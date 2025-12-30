<?php

namespace App\Services;

use App\Models\LearningStyleProfile;
use App\Models\LearningStyleQuestion;
use App\Models\LearningStyleResponse;
use App\Models\User;

class LearningStyleAnalyzer
{
    /**
     * Analyze a user's learning style responses and create/update their profile.
     */
    public function analyze(User $user): LearningStyleProfile
    {
        $responses = LearningStyleResponse::where('user_id', $user->id)
            ->with('question:id,style_type')
            ->get();

        $scores = $this->calculateScores($responses);
        $percentages = $this->calculatePercentages($scores);
        $dominantStyle = $this->determineDominantStyle($percentages);

        return LearningStyleProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'visual_score' => $percentages['visual'],
                'auditory_score' => $percentages['auditory'],
                'kinesthetic_score' => $percentages['kinesthetic'],
                'dominant_style' => $dominantStyle,
                'analyzed_at' => now(),
            ]
        );
    }

    /**
     * Calculate raw scores for each learning style.
     *
     * @param  \Illuminate\Support\Collection  $responses
     * @return array<string, int>
     */
    private function calculateScores($responses): array
    {
        $scores = [
            'visual' => 0,
            'auditory' => 0,
            'kinesthetic' => 0,
        ];

        foreach ($responses as $response) {
            $styleType = $response->question->style_type;
            if (isset($scores[$styleType])) {
                $scores[$styleType] += $response->score;
            }
        }

        return $scores;
    }

    /**
     * Convert raw scores to percentages.
     *
     * @param  array<string, int>  $scores
     * @return array<string, float>
     */
    private function calculatePercentages(array $scores): array
    {
        // Get question count per style (should be 5 each)
        $questionCounts = LearningStyleQuestion::active()
            ->selectRaw('style_type, COUNT(*) as count')
            ->groupBy('style_type')
            ->pluck('count', 'style_type')
            ->toArray();

        $percentages = [];

        foreach ($scores as $style => $score) {
            $questionCount = $questionCounts[$style] ?? 5;
            // Max score per style = questions * 5 (max Likert)
            $maxScore = $questionCount * 5;
            // Calculate percentage
            $percentages[$style] = $maxScore > 0
                ? round(($score / $maxScore) * 100, 1)
                : 0;
        }

        return $percentages;
    }

    /**
     * Determine the dominant learning style.
     *
     * @param  array<string, float>  $percentages
     */
    private function determineDominantStyle(array $percentages): string
    {
        $maxScore = max($percentages);
        $dominantStyles = array_keys(array_filter(
            $percentages,
            fn ($score) => $score === $maxScore
        ));

        // If there's a tie, return the first one (visual > auditory > kinesthetic)
        $priority = ['visual', 'auditory', 'kinesthetic'];

        foreach ($priority as $style) {
            if (in_array($style, $dominantStyles)) {
                return $style;
            }
        }

        return 'visual';
    }

    /**
     * Get learning style recommendations based on dominant style.
     *
     * @return array<string, mixed>
     */
    public function getRecommendations(string $dominantStyle): array
    {
        $recommendations = [
            'visual' => [
                'title' => 'Pelajar Visual',
                'description' => 'Kamu belajar paling baik melalui gambar, diagram, dan visualisasi.',
                'tips' => [
                    'Gunakan mind map dan diagram untuk memahami konsep',
                    'Catat dengan warna-warna berbeda',
                    'Tonton video pembelajaran',
                    'Buat flashcard bergambar',
                    'Gunakan highlighter saat membaca',
                ],
                'materials' => ['video', 'infographic', 'document'],
            ],
            'auditory' => [
                'title' => 'Pelajar Auditori',
                'description' => 'Kamu belajar paling baik melalui mendengarkan dan diskusi.',
                'tips' => [
                    'Dengarkan penjelasan guru dengan seksama',
                    'Rekam materi dan putar ulang',
                    'Diskusikan materi dengan teman',
                    'Baca materi dengan suara keras',
                    'Gunakan podcast atau audio book',
                ],
                'materials' => ['audio', 'video'],
            ],
            'kinesthetic' => [
                'title' => 'Pelajar Kinestetik',
                'description' => 'Kamu belajar paling baik melalui praktik langsung dan gerakan.',
                'tips' => [
                    'Lakukan eksperimen atau praktik langsung',
                    'Berjalan-jalan sambil menghafal',
                    'Gunakan simulasi atau permainan edukatif',
                    'Buat model atau prototipe',
                    'Ikuti role-play atau simulasi',
                ],
                'materials' => ['simulation', 'video'],
            ],
        ];

        return $recommendations[$dominantStyle] ?? $recommendations['visual'];
    }
}
