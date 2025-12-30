# Gemini AI Integration Specification

## Overview

Integrasi dengan Google Gemini API untuk fitur rekomendasi materi dan feedback personalisasi.

## Environment Configuration

```env
# .env
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-2.0-flash
```

```php
// config/services.php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
],
```

## Service Class

```php
// app/Services/GeminiAiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model');
        $this->baseUrl = config('services.gemini.base_url');
    }

    public function generateContent(string $prompt): ?string
    {
        try {
            $response = Http::post(
                "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                ]
            );

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Gemini API error', ['response' => $response->json()]);
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
```

## AI Features

### 1. Material Recommendations

**Purpose:** Merekomendasikan materi pembelajaran berdasarkan gaya belajar siswa.

**Input:**
- Learning style profile (visual, auditory, kinesthetic scores)
- Dominant style
- Subject (Matematika)
- Current topic (optional)
- Available materials list

**Prompt Template:**

```php
// app/Services/RecommendationEngine.php

public function generateRecommendationPrompt(User $user, Collection $materials): string
{
    $profile = $user->learningStyleProfile;

    $materialList = $materials->map(function ($m) {
        return "- ID:{$m->id} | {$m->title} | Tipe:{$m->type} | Gaya:{$m->learning_style} | Level:{$m->difficulty_level}";
    })->join("\n");

    return <<<PROMPT
Kamu adalah asisten pembelajaran AI untuk platform EduPersona.ai.

Profil gaya belajar siswa:
- Visual: {$profile->visual_score}%
- Auditori: {$profile->auditory_score}%
- Kinestetik: {$profile->kinesthetic_score}%
- Gaya dominan: {$profile->dominant_style}

Mata pelajaran: Matematika

Daftar materi yang tersedia:
{$materialList}

Tugas:
1. Pilih 5 materi yang paling sesuai dengan gaya belajar siswa
2. Urutkan dari yang paling relevan
3. Berikan alasan singkat mengapa setiap materi cocok untuk siswa ini

Format output (JSON):
{
    "recommendations": [
        {
            "material_id": 1,
            "relevance_score": 0.95,
            "reason": "Materi video ini sangat cocok untuk gaya belajar visual Anda..."
        }
    ]
}

Berikan respons dalam Bahasa Indonesia.
PROMPT;
}
```

**Output Processing:**

```php
public function processRecommendations(User $user, string $aiResponse): void
{
    $data = json_decode($aiResponse, true);

    if (!isset($data['recommendations'])) {
        return;
    }

    foreach ($data['recommendations'] as $rec) {
        AiRecommendation::updateOrCreate(
            [
                'user_id' => $user->id,
                'material_id' => $rec['material_id'],
            ],
            [
                'reason' => $rec['reason'],
                'relevance_score' => $rec['relevance_score'],
            ]
        );
    }
}
```

### 2. Learning Feedback

**Purpose:** Memberikan feedback personalisasi berdasarkan progress dan aktivitas siswa.

**Input:**
- Learning style profile
- Recent activities
- Progress data
- Completed materials

**Prompt Template:**

```php
// app/Services/FeedbackGenerator.php

public function generateFeedbackPrompt(User $user): string
{
    $profile = $user->learningStyleProfile;
    $activities = $user->learningActivities()
        ->with('material')
        ->latest()
        ->take(10)
        ->get();

    $progress = $user->studentProgress()
        ->with('subject')
        ->get();

    $activitySummary = $activities->map(function ($a) {
        $duration = round($a->duration_seconds / 60);
        return "- {$a->material->title}: {$duration} menit";
    })->join("\n");

    $progressSummary = $progress->map(function ($p) {
        return "- {$p->subject->name} ({$p->topic}): Skor {$p->score}, Status: {$p->status}";
    })->join("\n");

    return <<<PROMPT
Kamu adalah mentor pembelajaran AI yang ramah dan suportif untuk platform EduPersona.ai.

Profil siswa:
- Nama: {$user->name}
- Gaya belajar dominan: {$profile->dominant_style}
- Visual: {$profile->visual_score}%, Auditori: {$profile->auditory_score}%, Kinestetik: {$profile->kinesthetic_score}%

Aktivitas belajar terakhir:
{$activitySummary}

Progress pembelajaran:
{$progressSummary}

Tugas:
Berikan feedback pembelajaran yang personal dan memotivasi untuk siswa ini. Feedback harus:
1. Mengapresiasi usaha dan progress siswa
2. Memberikan saran spesifik sesuai gaya belajar dominan
3. Mengidentifikasi area yang perlu ditingkatkan
4. Memberikan tips praktis untuk meningkatkan pembelajaran

Format output (JSON):
{
    "feedback_type": "encouragement|suggestion|warning",
    "feedback_text": "Feedback dalam Bahasa Indonesia yang ramah dan memotivasi..."
}

Gunakan bahasa yang hangat, personal, dan sesuai untuk siswa SMA.
PROMPT;
}
```

**Feedback Types:**

| Type | Description | Use Case |
|------|-------------|----------|
| encouragement | Apresiasi dan motivasi | Progress bagus, konsisten belajar |
| suggestion | Saran untuk improvement | Area yang bisa ditingkatkan |
| warning | Peringatan lembut | Kurang aktivitas, progress menurun |

### 3. Error Handling

```php
// app/Services/GeminiAiService.php

public function generateContentWithFallback(string $prompt, string $fallback = null): string
{
    $result = $this->generateContent($prompt);

    if ($result === null) {
        // Return fallback or generic message
        return $fallback ?? 'Maaf, sistem sedang tidak dapat memberikan rekomendasi. Silakan coba lagi nanti.';
    }

    return $result;
}
```

### 4. Rate Limiting & Caching

```php
// Implement caching for recommendations
public function getRecommendations(User $user): Collection
{
    $cacheKey = "recommendations:{$user->id}";

    return Cache::remember($cacheKey, now()->addHours(6), function () use ($user) {
        return $this->generateAndStoreRecommendations($user);
    });
}

// Clear cache when profile changes
public function clearUserCache(User $user): void
{
    Cache::forget("recommendations:{$user->id}");
}
```

## Integration Points

### When to Generate Recommendations

1. **After questionnaire completion** - Generate initial recommendations
2. **After profile update** - Regenerate if learning style changes significantly
3. **Daily refresh** - Optional background job for active users
4. **On demand** - Manual refresh button

### When to Generate Feedback

1. **After completing material** - Immediate feedback on activity
2. **Weekly summary** - Scheduled job for weekly progress feedback
3. **Milestone achievements** - When reaching certain progress thresholds
4. **Inactivity** - Gentle reminder after 3+ days of no activity

## Jobs & Scheduling

```php
// app/Jobs/GenerateRecommendationsJob.php
class GenerateRecommendationsJob implements ShouldQueue
{
    public function __construct(public User $user) {}

    public function handle(RecommendationEngine $engine): void
    {
        $engine->generateForUser($this->user);
    }
}

// app/Jobs/GenerateFeedbackJob.php
class GenerateFeedbackJob implements ShouldQueue
{
    public function __construct(public User $user, public string $context) {}

    public function handle(FeedbackGenerator $generator): void
    {
        $generator->generateForUser($this->user, $this->context);
    }
}

// routes/console.php
Schedule::job(new WeeklyFeedbackJob)->weekly();
Schedule::job(new RefreshRecommendationsJob)->daily();
```

## Testing

```php
// tests/Unit/GeminiAiServiceTest.php
it('generates content from Gemini API', function () {
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            ['text' => '{"recommendations": []}']
                        ]
                    ]
                ]
            ]
        ])
    ]);

    $service = new GeminiAiService();
    $result = $service->generateContent('Test prompt');

    expect($result)->toContain('recommendations');
});

// tests/Unit/RecommendationEngineTest.php
it('processes AI recommendations correctly', function () {
    $user = User::factory()->create();
    // ... test implementation
});
```
