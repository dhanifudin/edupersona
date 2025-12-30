<?php

namespace App\Services;

use App\Models\AiRecommendation;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RecommendationEngine
{
    public function __construct(
        private GeminiAiService $geminiService
    ) {}

    /**
     * Generate personalized recommendations for a student.
     *
     * @return Collection<int, AiRecommendation>
     */
    public function generateForStudent(User $user, ?int $subjectId = null): Collection
    {
        $learningProfile = $user->learningStyleProfile;

        if (! $learningProfile) {
            Log::info('Cannot generate recommendations: no learning profile', ['user_id' => $user->id]);

            return collect();
        }

        // Get available materials
        $materialsQuery = LearningMaterial::query()
            ->with('subject:id,name')
            ->where('is_active', true);

        if ($subjectId) {
            $materialsQuery->where('subject_id', $subjectId);
        }

        $materials = $materialsQuery->get();

        if ($materials->isEmpty()) {
            return collect();
        }

        // Prepare data for AI
        $profileData = [
            'visual_score' => $learningProfile->visual_score,
            'auditory_score' => $learningProfile->auditory_score,
            'kinesthetic_score' => $learningProfile->kinesthetic_score,
            'dominant_style' => $learningProfile->dominant_style,
        ];

        $materialsData = $materials->map(fn ($m) => [
            'id' => $m->id,
            'title' => $m->title,
            'type' => $m->type,
            'learning_style' => $m->learning_style,
            'difficulty_level' => $m->difficulty_level,
            'description' => $m->description,
        ])->toArray();

        // Try AI recommendations first
        $aiRecommendations = $this->geminiService->generateRecommendations(
            $profileData,
            $materialsData
        );

        if ($aiRecommendations && isset($aiRecommendations['recommendations'])) {
            return $this->saveAiRecommendations($user, $aiRecommendations['recommendations']);
        }

        // Fallback to rule-based recommendations
        return $this->generateRuleBasedRecommendations($user, $materials, $learningProfile->dominant_style);
    }

    /**
     * Save AI-generated recommendations to database.
     *
     * @param  array<int, array<string, mixed>>  $recommendations
     * @return Collection<int, AiRecommendation>
     */
    private function saveAiRecommendations(User $user, array $recommendations): Collection
    {
        $saved = collect();

        foreach ($recommendations as $rec) {
            // Verify material exists
            $material = LearningMaterial::find($rec['material_id'] ?? 0);
            if (! $material) {
                continue;
            }

            // Check if already recommended
            $existing = AiRecommendation::where('user_id', $user->id)
                ->where('material_id', $material->id)
                ->where('is_viewed', false)
                ->first();

            if ($existing) {
                $saved->push($existing);

                continue;
            }

            $recommendation = AiRecommendation::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'reason' => $rec['reason'] ?? 'Direkomendasikan berdasarkan gaya belajarmu',
                'relevance_score' => $rec['relevance_score'] ?? 0.5,
                'is_viewed' => false,
            ]);

            $saved->push($recommendation);
        }

        return $saved;
    }

    /**
     * Generate rule-based recommendations when AI is unavailable.
     *
     * @param  Collection<int, LearningMaterial>  $materials
     * @return Collection<int, AiRecommendation>
     */
    private function generateRuleBasedRecommendations(
        User $user,
        Collection $materials,
        string $dominantStyle
    ): Collection {
        // Style to material type mapping
        $stylePreferences = [
            'visual' => ['video', 'infographic', 'document'],
            'auditory' => ['audio', 'video'],
            'kinesthetic' => ['simulation', 'video'],
        ];

        $preferredTypes = $stylePreferences[$dominantStyle] ?? ['video', 'document'];

        // Score and sort materials
        $scored = $materials->map(function ($material) use ($preferredTypes, $dominantStyle) {
            $score = 0.5; // Base score

            // Type match bonus
            if (in_array($material->type, $preferredTypes)) {
                $score += 0.3;
            }

            // Learning style match bonus
            if ($material->learning_style === $dominantStyle || $material->learning_style === 'all') {
                $score += 0.2;
            }

            return [
                'material' => $material,
                'score' => min($score, 1.0),
            ];
        })
            ->sortByDesc('score')
            ->take(5);

        $saved = collect();

        foreach ($scored as $item) {
            $material = $item['material'];

            // Check if already recommended
            $existing = AiRecommendation::where('user_id', $user->id)
                ->where('material_id', $material->id)
                ->where('is_viewed', false)
                ->first();

            if ($existing) {
                $saved->push($existing);

                continue;
            }

            $reason = $this->generateRuleBasedReason($material, $dominantStyle);

            $recommendation = AiRecommendation::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'reason' => $reason,
                'relevance_score' => $item['score'],
                'is_viewed' => false,
            ]);

            $saved->push($recommendation);
        }

        return $saved;
    }

    /**
     * Generate a rule-based reason for recommendation.
     */
    private function generateRuleBasedReason(LearningMaterial $material, string $dominantStyle): string
    {
        $styleLabels = [
            'visual' => 'visual',
            'auditory' => 'auditori',
            'kinesthetic' => 'kinestetik',
        ];

        $typeLabels = [
            'video' => 'video',
            'document' => 'dokumen',
            'infographic' => 'infografis',
            'audio' => 'audio',
            'simulation' => 'simulasi interaktif',
        ];

        $styleLabel = $styleLabels[$dominantStyle] ?? $dominantStyle;
        $typeLabel = $typeLabels[$material->type] ?? $material->type;

        if ($material->learning_style === $dominantStyle) {
            return "Materi {$typeLabel} ini dirancang khusus untuk pelajar {$styleLabel} sepertimu.";
        }

        if ($material->learning_style === 'all') {
            return "Materi {$typeLabel} ini cocok untuk semua gaya belajar termasuk {$styleLabel}.";
        }

        return "Materi {$typeLabel} ini dapat membantumu memahami topik dengan cara berbeda.";
    }

    /**
     * Refresh recommendations for a student (clear old and generate new).
     *
     * @return Collection<int, AiRecommendation>
     */
    public function refreshForStudent(User $user, ?int $subjectId = null): Collection
    {
        // Mark old unviewed recommendations as viewed
        AiRecommendation::where('user_id', $user->id)
            ->where('is_viewed', false)
            ->update(['is_viewed' => true, 'viewed_at' => now()]);

        return $this->generateForStudent($user, $subjectId);
    }
}
