# Personalized Learning Platform - Gemini AI Integration

## Overview

Dokumen ini menjelaskan logika integrasi Gemini AI untuk platform pembelajaran personal EduPersona.ai. Platform menggunakan profil gaya belajar VAK (Visual, Auditori, Kinestetik) untuk memberikan pengalaman belajar yang dipersonalisasi.

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Frontend (Vue + Inertia)                      │
├─────────────────────────────────────────────────────────────────────┤
│  Dashboard  │  Subject Learning  │  Topic Detail  │  Questionnaire  │
└──────┬──────────────┬──────────────────┬─────────────────┬──────────┘
       │              │                  │                 │
       ▼              ▼                  ▼                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Laravel Controllers                           │
├─────────────────────────────────────────────────────────────────────┤
│ DashboardController │ SubjectLearningController │ TopicDetailController │
└──────┬──────────────────────────┬───────────────────────────────────┘
       │                          │
       ▼                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         AI Services Layer                            │
├─────────────────────────────────────────────────────────────────────┤
│   GeminiAiService   │   RecommendationEngine   │   FeedbackGenerator │
└──────────┬──────────────────────┬───────────────────────┬───────────┘
           │                      │                       │
           ▼                      ▼                       ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Background Jobs                               │
├─────────────────────────────────────────────────────────────────────┤
│ GenerateRecommendationsJob │ GenerateFeedbackJob │ WeeklyFeedbackJob │
└─────────────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     Google Gemini API                                │
│                   (gemini-1.5-pro model)                             │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 1. Learning Flow & AI Integration Points

### 1.1 Alur Pembelajaran Siswa

```
[Siswa Baru] ──► [Kuesioner VAK] ──► [Analisis Gaya Belajar]
                                              │
                                              ▼
                                    [Generate Rekomendasi AI] ◄── Trigger 1
                                              │
                                              ▼
[Dashboard] ◄─── [Lihat Rekomendasi & Feedback]
     │
     ▼
[Pilih Mata Pelajaran] ──► [Subject Learning Page]
     │                              │
     │                              ▼
     │                    [Lihat Topik & Rekomendasi Materi]
     │                              │
     │                              ▼
     │                    [Mulai Materi] ──► [Track Activity]
     │                              │
     │                              ▼
     │                    [Selesai Materi] ──► [Generate Feedback AI] ◄── Trigger 2
     │                              │
     │                              ▼
     │                    [Update Progress] ──► [Refresh Rekomendasi]
     │
     ▼
[Weekly Summary] ◄── Scheduled Job ◄── Trigger 3
```

### 1.2 AI Trigger Points

| Trigger | Event | AI Action | Priority |
|---------|-------|-----------|----------|
| 1 | Selesai kuesioner VAK | Generate initial recommendations | High |
| 2 | Selesai materi pembelajaran | Generate activity feedback | Medium |
| 3 | Jadwal mingguan | Generate weekly summary | Low |
| 4 | 3+ hari tidak aktif | Generate inactivity reminder | Low |
| 5 | Refresh manual oleh user | Regenerate recommendations | Medium |
| 6 | Perubahan learning profile | Regenerate recommendations | High |

---

## 2. AI Services Detail

### 2.1 GeminiAiService

**File:** `app/Services/GeminiAiService.php`

**Metode Utama:**

| Method | Purpose | Input | Output |
|--------|---------|-------|--------|
| `generateContent()` | Base text generation | prompt, systemInstruction | string |
| `generateRecommendations()` | Material recommendations | profile, materials | array (JSON) |
| `generateFeedback()` | Personalized feedback | profile, progress, activities | string |
| `generateExplanation()` | Topic explanation | topic, style, difficulty | string |

**Prompt Engineering:**

```php
// System instruction untuk rekomendasi
'Kamu adalah asisten AI untuk platform pembelajaran EduPersona.ai.
Tugasmu adalah merekomendasikan materi pembelajaran yang paling sesuai dengan gaya belajar siswa.
Berikan respons dalam format JSON yang valid.'

// System instruction untuk feedback
'Kamu adalah mentor pembelajaran yang ramah dan suportif di platform EduPersona.ai.
Berikan umpan balik yang personal, memotivasi, dan actionable dalam Bahasa Indonesia.
Gunakan bahasa yang sesuai untuk siswa SMA.'
```

### 2.2 RecommendationEngine

**File:** `app/Services/RecommendationEngine.php`

**Logika Rekomendasi:**

1. **AI-Powered (Primary)**
   - Kirim profil VAK + daftar materi ke Gemini
   - Parse JSON response
   - Simpan ke `ai_recommendations` table

2. **Rule-Based (Fallback)**
   - Scoring berdasarkan matching type dan style
   - Visual → video, infographic, document (+0.3)
   - Auditory → audio, video (+0.3)
   - Kinesthetic → simulation, video (+0.3)
   - Style match → +0.2

**Caching Strategy:**

```php
// Cache duration: 6 jam
Cache::remember("recommendations:{$userId}:subject:{$subjectId}", 6 hours, ...)

// Cache invalidation triggers:
// - Profile update
// - Manual refresh
// - Materi baru ditambahkan
```

### 2.3 FeedbackGenerator

**File:** `app/Services/FeedbackGenerator.php`

**Tipe Feedback:**

| Type | Context | Use Case |
|------|---------|----------|
| `encouragement` | Progress bagus | ≥70% aktivitas selesai |
| `suggestion` | Area improvement | Default untuk summary |
| `warning` | Peringatan lembut | Inaktivitas, progress menurun |
| `learning_progress` | Update kemajuan | Setelah milestone |

---

## 3. Database Schema (Existing)

### 3.1 AI Tables

```sql
-- Menyimpan rekomendasi dari AI
ai_recommendations:
  - user_id
  - material_id
  - reason (text)
  - relevance_score (decimal)
  - is_viewed (boolean)
  - viewed_at (timestamp)

-- Menyimpan feedback dari AI
ai_feedback:
  - user_id
  - context_type (general|activity_completion|weekly_summary|inactivity_reminder)
  - context_id (nullable)
  - feedback_type (encouragement|suggestion|warning|learning_progress)
  - feedback_text (text)
  - is_read (boolean)
  - read_at (timestamp)
  - generated_at (timestamp)
```

### 3.2 Learning Profile

```sql
learning_style_profiles:
  - user_id
  - visual_score (decimal 0-100)
  - auditory_score (decimal 0-100)
  - kinesthetic_score (decimal 0-100)
  - dominant_style (enum: visual|auditory|kinesthetic)
  - analyzed_at (timestamp)
```

---

## 4. Integration Implementation

### 4.1 After Questionnaire Completion

**Controller:** `QuestionnaireController@complete`

```php
public function complete(Request $request): RedirectResponse
{
    // 1. Calculate VAK scores
    $scores = $this->analyzer->analyze($request->answers);

    // 2. Create/update learning profile
    $profile = LearningStyleProfile::updateOrCreate(
        ['user_id' => auth()->id()],
        $scores
    );

    // 3. Generate initial recommendations (async)
    GenerateRecommendationsJob::dispatch(auth()->user());

    // 4. Generate welcome feedback (async)
    GenerateFeedbackJob::dispatch(auth()->user(), 'welcome');

    return redirect()->route('student.dashboard');
}
```

### 4.2 Subject Learning Page

**Controller:** `SubjectLearningController@show`

```php
public function show(Subject $subject): Response
{
    $user = auth()->user();

    // Get personalized recommendations for this subject
    $recommendations = $this->recommendationEngine
        ->getCachedRecommendations($user, $subject->id)
        ->take(5);

    // Get latest feedback
    $feedback = $user->aiFeedback()
        ->where('is_read', false)
        ->latest('generated_at')
        ->first();

    // Get topics with progress
    $topics = $this->getTopicsWithProgress($user, $subject);

    return Inertia::render('Student/SubjectLearning', [
        'subject' => $subject,
        'recommendations' => $recommendations,
        'feedback' => $feedback,
        'topics' => $topics,
        'learningProfile' => $user->learningStyleProfile,
    ]);
}
```

### 4.3 Activity Completion

**Controller:** `LearningActivityController@complete`

```php
public function complete(LearningActivity $activity): JsonResponse
{
    $activity->update([
        'completed_at' => now(),
        'duration_seconds' => /* calculated */,
    ]);

    // Update progress
    $this->progressTracker->updateProgress($activity);

    // Generate completion feedback (async)
    GenerateFeedbackJob::dispatch(
        auth()->user(),
        'activity_completion',
        $activity->id
    );

    // Refresh recommendations if needed
    if ($this->shouldRefreshRecommendations($activity)) {
        RefreshRecommendationsJob::dispatch(auth()->user());
    }

    return response()->json(['success' => true]);
}
```

### 4.4 Weekly Summary (Scheduled)

**Job:** `WeeklyFeedbackJob`

```php
// routes/console.php
Schedule::job(new WeeklyFeedbackJob)->weekly()->sundays()->at('09:00');

// Job implementation
public function handle(FeedbackGenerator $generator): void
{
    $activeStudents = User::where('role', 'student')
        ->whereHas('learningStyleProfile')
        ->get();

    foreach ($activeStudents as $student) {
        $generator->generateWeeklySummary($student);
    }
}
```

---

## 5. Personalization Logic

### 5.1 Material Type Mapping

| Gaya Belajar | Tipe Materi Prioritas | Alasan |
|--------------|----------------------|--------|
| Visual | video, infographic, document | Konten yang bisa dilihat dan divisualisasikan |
| Auditory | audio, video (narration), podcast | Konten yang bisa didengarkan |
| Kinesthetic | simulation, interactive, hands-on | Konten yang bisa dipraktikkan langsung |

### 5.2 Relevance Score Calculation

**AI-Based:**
```
Gemini mengevaluasi:
- Match antara tipe materi dan gaya belajar (40%)
- Relevansi topik dengan progress siswa (30%)
- Tingkat kesulitan yang sesuai (20%)
- Variasi pembelajaran (10%)
```

**Rule-Based Fallback:**
```
base_score = 0.5
if material.type in preferred_types[dominant_style]:
    score += 0.3
if material.learning_style == dominant_style or 'all':
    score += 0.2
final_score = min(score, 1.0)
```

### 5.3 Feedback Personalization

**Context-Aware Messages:**

1. **Welcome (First Login)**
   - Introduce platform based on learning style
   - Suggest first steps

2. **Activity Completion**
   - Celebrate achievement
   - Suggest next material

3. **Weekly Summary**
   - Recap progress
   - Identify patterns
   - Suggest improvements

4. **Inactivity Reminder**
   - Gentle nudge
   - Highlight new content matching style

---

## 6. Error Handling & Fallbacks

### 6.1 API Failure Handling

```php
// GeminiAiService
public function generateContent(string $prompt, ?string $systemInstruction = null): ?string
{
    if (empty(config('gemini.api_key'))) {
        Log::warning('Gemini API key not configured');
        return null; // Trigger fallback
    }

    try {
        // API call
    } catch (\Exception $e) {
        Log::error('Gemini API error', ['message' => $e->getMessage()]);
        return null; // Trigger fallback
    }
}
```

### 6.2 Fallback Chain

```
1. Try Gemini AI → Success → Use AI response
                 ↓
              Failure
                 ↓
2. Use Rule-Based Logic → Generate recommendations/feedback
                 ↓
3. Use Template Messages → Generic but helpful response
```

### 6.3 Graceful Degradation

| Scenario | Behavior |
|----------|----------|
| API Key missing | Use rule-based + log warning |
| API timeout | Retry once, then fallback |
| Invalid JSON response | Log error, use fallback |
| No materials available | Show "coming soon" message |
| No learning profile | Redirect to questionnaire |

---

## 7. Performance Optimization

### 7.1 Caching Strategy

```php
// Recommendations: 6 hours cache
Cache::remember("recommendations:{$userId}:subject:{$subjectId}", 21600, ...)

// Learning profile: Until manually updated
// No cache - read from DB (single row)

// Feedback: No cache - Always fresh for user
```

### 7.2 Background Processing

```php
// Jobs that should run async
GenerateRecommendationsJob::dispatch($user)->onQueue('ai');
GenerateFeedbackJob::dispatch($user, $context)->onQueue('ai');
RefreshRecommendationsJob::dispatch($user)->onQueue('ai');

// Queue configuration
'ai' => [
    'driver' => 'database',
    'retry_after' => 90,
    'timeout' => 60,
]
```

### 7.3 Rate Limiting

```php
// Limit AI calls per user
RateLimiter::for('ai-generation', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

---

## 8. Frontend Integration

### 8.1 Displaying Recommendations

```vue
<!-- SubjectLearning.vue -->
<section class="recommendations">
    <h3>Rekomendasi Untukmu</h3>
    <div v-for="rec in recommendations" :key="rec.id">
        <MaterialCard
            :material="rec.material"
            :reason="rec.reason"
            :relevance="rec.relevance_score"
        />
    </div>
</section>
```

### 8.2 Displaying Feedback

```vue
<!-- Dashboard.vue or SubjectLearning.vue -->
<AiFeedbackCard
    v-if="feedback"
    :feedback="feedback"
    :type="feedback.feedback_type"
    @dismiss="markAsRead"
/>
```

### 8.3 Learning Style Indicator

```vue
<!-- Show dominant style badge -->
<LearningStyleBadge
    :profile="learningProfile"
    :showDetails="true"
/>
```

---

## 9. Testing Strategy

### 9.1 Unit Tests

```php
// tests/Unit/GeminiAiServiceTest.php
it('parses JSON from markdown code blocks', function () {
    $service = new GeminiAiService();
    $response = '```json\n{"recommendations": []}\n```';

    expect($service->parseJsonResponse($response))
        ->toBeArray()
        ->toHaveKey('recommendations');
});

// tests/Unit/RecommendationEngineTest.php
it('generates rule-based recommendations when AI fails', function () {
    // Mock GeminiAiService to return null
    $this->mock(GeminiAiService::class)
        ->shouldReceive('generateRecommendations')
        ->andReturn(null);

    $engine = app(RecommendationEngine::class);
    $recommendations = $engine->generateForStudent($user);

    expect($recommendations)->not->toBeEmpty();
});
```

### 9.2 Feature Tests

```php
// tests/Feature/RecommendationTest.php
it('generates recommendations after questionnaire completion', function () {
    $user = User::factory()->student()->create();

    actingAs($user)
        ->post('/questionnaire/submit', ['answers' => [...]])
        ->assertRedirect('/dashboard');

    expect($user->aiRecommendations)->not->toBeEmpty();
});
```

### 9.3 Mock API Response

```php
// Mock Gemini response for testing
Http::fake([
    'generativelanguage.googleapis.com/*' => Http::response([
        'candidates' => [[
            'content' => [
                'parts' => [[
                    'text' => json_encode([
                        'recommendations' => [
                            ['material_id' => 1, 'relevance_score' => 0.95, 'reason' => 'Test']
                        ]
                    ])
                ]]
            ]
        ]]
    ])
]);
```

---

## 10. Future Enhancements

### 10.1 Adaptive Learning Path

- Track learning patterns over time
- Adjust difficulty based on performance
- Suggest optimal learning sequences

### 10.2 Multi-Subject Support

- Cross-subject recommendations
- Integrated learning dashboard
- Subject-specific AI prompts

### 10.3 Advanced Analytics

- Learning efficiency metrics
- Style adaptation tracking
- Predictive progress modeling

### 10.4 Conversational AI (Chatbot)

- Real-time Q&A with topic context
- Explanation tailored to learning style
- Interactive problem-solving

---

## 11. Configuration

### 11.1 Environment Variables

```env
# Google Gemini AI Configuration
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=text-embedding-004
GEMINI_MODEL_TEXT=gemini-1.5-pro
GEMINI_TIMEOUT=12
GEMINI_MAX_OUTPUT_TOKENS=512
```

### 11.2 App Config

```php
// config/app.php
'gemini_model' => env('GEMINI_MODEL_TEXT', 'gemini-1.5-flash'),
```

---

## Summary

Platform EduPersona.ai mengintegrasikan Gemini AI di beberapa titik kunci:

1. **Initial Profile** → Generate personalized recommendations
2. **Learning Activity** → Track & provide feedback
3. **Progress Update** → Refresh recommendations
4. **Scheduled Jobs** → Weekly summaries & reminders

Dengan fallback rule-based yang robust, platform tetap berfungsi meski API tidak tersedia.
