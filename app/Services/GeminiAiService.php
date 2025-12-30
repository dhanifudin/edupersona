<?php

namespace App\Services;

use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class GeminiAiService
{
    private string $textModel;

    public function __construct()
    {
        $this->textModel = config('app.gemini_model', 'gemini-1.5-flash');
    }

    /**
     * Generate text content using Gemini API.
     */
    public function generateContent(string $prompt, ?string $systemInstruction = null): ?string
    {
        if (empty(config('gemini.api_key'))) {
            Log::warning('Gemini API key not configured');

            return null;
        }

        try {
            $model = Gemini::generativeModel($this->textModel);

            if ($systemInstruction) {
                $model = $model->withSystemInstruction(
                    Content::createTextContent($systemInstruction, Role::MODEL)
                );
            }

            $response = $model->generateContent($prompt);

            return $response->text();
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate learning material recommendations.
     *
     * @param  array<string, mixed>  $learningProfile
     * @param  array<int, array<string, mixed>>  $availableMaterials
     * @return array<string, mixed>|null
     */
    public function generateRecommendations(
        array $learningProfile,
        array $availableMaterials,
        string $subject = 'Matematika'
    ): ?array {
        $prompt = $this->buildRecommendationPrompt($learningProfile, $availableMaterials, $subject);

        $systemInstruction = <<<'SYSTEM'
Kamu adalah asisten AI untuk platform pembelajaran EduPersona.ai.
Tugasmu adalah merekomendasikan materi pembelajaran yang paling sesuai dengan gaya belajar siswa.
Berikan respons dalam format JSON yang valid dengan struktur:
{
  "recommendations": [
    {
      "material_id": <id>,
      "relevance_score": <0.0-1.0>,
      "reason": "<alasan dalam Bahasa Indonesia>"
    }
  ]
}
Maksimal 5 rekomendasi, urutkan dari yang paling relevan.
SYSTEM;

        $response = $this->generateContent($prompt, $systemInstruction);

        if (! $response) {
            return null;
        }

        return $this->parseJsonResponse($response);
    }

    /**
     * Generate personalized learning feedback.
     *
     * @param  array<string, mixed>  $learningProfile
     * @param  array<string, mixed>  $progressData
     * @param  array<int, array<string, mixed>>  $recentActivities
     */
    public function generateFeedback(
        array $learningProfile,
        array $progressData,
        array $recentActivities
    ): ?string {
        $prompt = $this->buildFeedbackPrompt($learningProfile, $progressData, $recentActivities);

        $systemInstruction = <<<'SYSTEM'
Kamu adalah mentor pembelajaran yang ramah dan suportif di platform EduPersona.ai.
Berikan umpan balik yang personal, memotivasi, dan actionable dalam Bahasa Indonesia.
Gunakan bahasa yang sesuai untuk siswa SMA.
Fokus pada:
1. Apresiasi pencapaian
2. Area yang perlu ditingkatkan
3. Saran konkret berdasarkan gaya belajar siswa
4. Motivasi untuk terus belajar
SYSTEM;

        return $this->generateContent($prompt, $systemInstruction);
    }

    /**
     * Generate explanation for a learning topic.
     */
    public function generateExplanation(
        string $topic,
        string $learningStyle,
        string $difficultyLevel = 'intermediate'
    ): ?string {
        $styleDescriptions = [
            'visual' => 'menggunakan analogi visual, diagram mental, dan deskripsi yang mudah divisualisasikan',
            'auditory' => 'menggunakan penjelasan verbal yang jelas, ritme, dan contoh percakapan',
            'kinesthetic' => 'menggunakan contoh praktis, langkah-langkah hands-on, dan simulasi aktivitas',
        ];

        $styleApproach = $styleDescriptions[$learningStyle] ?? $styleDescriptions['visual'];

        $prompt = <<<PROMPT
Jelaskan topik "{$topic}" untuk siswa dengan gaya belajar {$learningStyle}.
Tingkat kesulitan: {$difficultyLevel}

Gunakan pendekatan {$styleApproach}.
Berikan penjelasan dalam Bahasa Indonesia yang mudah dipahami siswa SMA.
PROMPT;

        $systemInstruction = <<<'SYSTEM'
Kamu adalah guru yang ahli dalam menjelaskan konsep sesuai gaya belajar siswa.
Berikan penjelasan yang engaging, mudah dipahami, dan sesuai dengan preferensi belajar siswa.
SYSTEM;

        return $this->generateContent($prompt, $systemInstruction);
    }

    /**
     * @param  array<string, mixed>  $learningProfile
     * @param  array<int, array<string, mixed>>  $availableMaterials
     */
    private function buildRecommendationPrompt(
        array $learningProfile,
        array $availableMaterials,
        string $subject
    ): string {
        $materialsJson = json_encode($availableMaterials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Berdasarkan profil gaya belajar siswa:
- Visual: {$learningProfile['visual_score']}%
- Auditori: {$learningProfile['auditory_score']}%
- Kinestetik: {$learningProfile['kinesthetic_score']}%
- Gaya dominan: {$learningProfile['dominant_style']}

Mata pelajaran: {$subject}

Daftar materi yang tersedia:
{$materialsJson}

Rekomendasikan materi yang paling sesuai dengan gaya belajar siswa.
Pertimbangkan:
1. Kesesuaian tipe materi dengan gaya belajar dominan
2. Tingkat kesulitan yang progresif
3. Variasi tipe materi untuk pembelajaran optimal
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $learningProfile
     * @param  array<string, mixed>  $progressData
     * @param  array<int, array<string, mixed>>  $recentActivities
     */
    private function buildFeedbackPrompt(
        array $learningProfile,
        array $progressData,
        array $recentActivities
    ): string {
        $activitiesJson = json_encode($recentActivities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $progressJson = json_encode($progressData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Profil siswa:
- Gaya belajar dominan: {$learningProfile['dominant_style']}
- Visual: {$learningProfile['visual_score']}%, Auditori: {$learningProfile['auditory_score']}%, Kinestetik: {$learningProfile['kinesthetic_score']}%

Kemajuan belajar:
{$progressJson}

Aktivitas terkini:
{$activitiesJson}

Berikan umpan balik personal untuk membantu siswa terus berkembang.
PROMPT;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function parseJsonResponse(string $response): ?array
    {
        // Extract JSON from markdown code blocks if present
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $matches)) {
            $response = $matches[1];
        }

        try {
            return json_decode(trim($response), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Failed to parse Gemini JSON response', [
                'response' => $response,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
