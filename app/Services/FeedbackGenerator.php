<?php

namespace App\Services;

use App\Models\AiFeedback;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class FeedbackGenerator
{
    public function __construct(
        private GeminiAiService $geminiService
    ) {}

    /**
     * Generate personalized feedback for a student.
     */
    public function generateForStudent(User $user, string $contextType = 'general'): ?AiFeedback
    {
        $learningProfile = $user->learningStyleProfile;

        if (! $learningProfile) {
            Log::info('Cannot generate feedback: no learning profile', ['user_id' => $user->id]);

            return null;
        }

        // Prepare learning profile data
        $profileData = [
            'visual_score' => $learningProfile->visual_score,
            'auditory_score' => $learningProfile->auditory_score,
            'kinesthetic_score' => $learningProfile->kinesthetic_score,
            'dominant_style' => $learningProfile->dominant_style,
        ];

        // Get recent activities
        $recentActivities = $user->learningActivities()
            ->with('material:id,title,type')
            ->latest('started_at')
            ->take(10)
            ->get()
            ->map(fn ($a) => [
                'material_title' => $a->material?->title ?? 'Unknown',
                'material_type' => $a->material?->type ?? 'Unknown',
                'duration_minutes' => round($a->duration_seconds / 60, 1),
                'completed' => $a->completed_at !== null,
            ])->toArray();

        // Get progress data
        $progressData = $user->studentProgress()
            ->with('subject:id,name')
            ->get()
            ->map(fn ($p) => [
                'subject' => $p->subject?->name ?? 'Unknown',
                'topic' => $p->topic,
                'score' => $p->score,
                'attempts' => $p->attempts,
                'status' => $p->status,
            ])->toArray();

        // Generate feedback using AI
        $feedbackText = $this->geminiService->generateFeedback(
            $profileData,
            ['progress' => $progressData],
            $recentActivities
        );

        if (! $feedbackText) {
            // Use fallback feedback
            $feedbackText = $this->generateFallbackFeedback($profileData, $recentActivities, $progressData);
        }

        // Determine feedback type based on context
        $feedbackType = $this->determineFeedbackType($recentActivities, $progressData, $contextType);

        // Save feedback to database
        return AiFeedback::create([
            'user_id' => $user->id,
            'context_type' => $contextType,
            'context_id' => null,
            'feedback_type' => $feedbackType,
            'feedback_text' => $feedbackText,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate feedback for a specific learning activity completion.
     */
    public function generateForActivity(User $user, int $activityId): ?AiFeedback
    {
        $activity = $user->learningActivities()
            ->with('material.subject')
            ->find($activityId);

        if (! $activity) {
            return null;
        }

        $learningProfile = $user->learningStyleProfile;

        if (! $learningProfile) {
            return null;
        }

        $profileData = [
            'visual_score' => $learningProfile->visual_score,
            'auditory_score' => $learningProfile->auditory_score,
            'kinesthetic_score' => $learningProfile->kinesthetic_score,
            'dominant_style' => $learningProfile->dominant_style,
        ];

        $activityData = [
            [
                'material_title' => $activity->material?->title ?? 'Materi',
                'material_type' => $activity->material?->type ?? 'unknown',
                'duration_minutes' => round($activity->duration_seconds / 60, 1),
                'completed' => $activity->completed_at !== null,
            ],
        ];

        $feedbackText = $this->geminiService->generateFeedback(
            $profileData,
            ['activity_completed' => true],
            $activityData
        );

        if (! $feedbackText) {
            $feedbackText = $this->generateActivityCompletionFeedback($activity);
        }

        return AiFeedback::create([
            'user_id' => $user->id,
            'context_type' => 'activity_completion',
            'context_id' => $activityId,
            'feedback_type' => 'encouragement',
            'feedback_text' => $feedbackText,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate weekly summary feedback.
     */
    public function generateWeeklySummary(User $user): ?AiFeedback
    {
        $learningProfile = $user->learningStyleProfile;

        if (! $learningProfile) {
            return null;
        }

        // Get activities from the past week
        $weeklyActivities = $user->learningActivities()
            ->with('material:id,title,type')
            ->where('started_at', '>=', now()->subWeek())
            ->get();

        if ($weeklyActivities->isEmpty()) {
            // Generate inactivity reminder
            return $this->generateInactivityReminder($user);
        }

        $profileData = [
            'visual_score' => $learningProfile->visual_score,
            'auditory_score' => $learningProfile->auditory_score,
            'kinesthetic_score' => $learningProfile->kinesthetic_score,
            'dominant_style' => $learningProfile->dominant_style,
        ];

        $activitiesData = $weeklyActivities->map(fn ($a) => [
            'material_title' => $a->material?->title ?? 'Unknown',
            'material_type' => $a->material?->type ?? 'Unknown',
            'duration_minutes' => round($a->duration_seconds / 60, 1),
            'completed' => $a->completed_at !== null,
        ])->toArray();

        $progressData = [
            'total_activities' => $weeklyActivities->count(),
            'completed_activities' => $weeklyActivities->whereNotNull('completed_at')->count(),
            'total_minutes' => round($weeklyActivities->sum('duration_seconds') / 60, 1),
        ];

        $feedbackText = $this->geminiService->generateFeedback(
            $profileData,
            $progressData,
            $activitiesData
        );

        if (! $feedbackText) {
            $feedbackText = $this->generateWeeklyFallbackFeedback($progressData, $learningProfile->dominant_style);
        }

        return AiFeedback::create([
            'user_id' => $user->id,
            'context_type' => 'weekly_summary',
            'context_id' => null,
            'feedback_type' => 'suggestion',
            'feedback_text' => $feedbackText,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate inactivity reminder for inactive students.
     */
    public function generateInactivityReminder(User $user): ?AiFeedback
    {
        $learningProfile = $user->learningStyleProfile;
        $dominantStyle = $learningProfile?->dominant_style ?? 'visual';

        $styleMessages = [
            'visual' => 'Kami punya materi video dan infografis baru yang menarik untukmu! Ayo kembali belajar dan eksplorasi konten visual yang sudah disiapkan.',
            'auditory' => 'Ada podcast dan materi audio baru yang menunggumu! Dengarkan sambil istirahat dan tingkatkan pemahamanmu.',
            'kinesthetic' => 'Simulasi interaktif baru sudah tersedia! Ayo praktikkan langsung dan perkuat pemahamanmu dengan hands-on learning.',
        ];

        $feedbackText = "Hai {$user->name}! Kami merindukanmu di EduPersona. ".
            ($styleMessages[$dominantStyle] ?? $styleMessages['visual']).
            ' Ingat, belajar sedikit setiap hari lebih efektif daripada belajar banyak sekaligus. Yuk mulai lagi! 💪';

        return AiFeedback::create([
            'user_id' => $user->id,
            'context_type' => 'inactivity_reminder',
            'context_id' => null,
            'feedback_type' => 'warning',
            'feedback_text' => $feedbackText,
            'generated_at' => now(),
        ]);
    }

    /**
     * Determine feedback type based on context.
     *
     * @param  array<int, array<string, mixed>>  $activities
     * @param  array<int, array<string, mixed>>  $progress
     */
    private function determineFeedbackType(array $activities, array $progress, string $contextType): string
    {
        if ($contextType === 'inactivity_reminder') {
            return 'warning';
        }

        // Check for good progress
        $completedCount = collect($activities)->where('completed', true)->count();
        if ($completedCount > 0 && $completedCount >= count($activities) * 0.7) {
            return 'encouragement';
        }

        // Default to suggestion
        return 'suggestion';
    }

    /**
     * Generate fallback feedback when AI is unavailable.
     *
     * @param  array<string, mixed>  $profile
     * @param  array<int, array<string, mixed>>  $activities
     * @param  array<int, array<string, mixed>>  $progress
     */
    private function generateFallbackFeedback(array $profile, array $activities, array $progress): string
    {
        $dominantStyle = $profile['dominant_style'] ?? 'visual';
        $activityCount = count($activities);
        $completedCount = collect($activities)->where('completed', true)->count();

        $styleLabels = [
            'visual' => 'visual',
            'auditory' => 'auditori',
            'kinesthetic' => 'kinestetik',
        ];

        $styleLabel = $styleLabels[$dominantStyle] ?? 'visual';

        if ($activityCount === 0) {
            return "Selamat datang di EduPersona! Sebagai pelajar {$styleLabel}, kami sudah menyiapkan materi yang cocok untukmu. Mulailah dengan mengeksplorasi rekomendasi yang ada.";
        }

        if ($completedCount >= $activityCount * 0.7) {
            return "Luar biasa! Kamu sudah menyelesaikan {$completedCount} dari {$activityCount} aktivitas. Konsistensimu sangat bagus! Terus semangat dan jangan ragu mencoba materi baru yang sesuai gaya belajar {$styleLabel}mu.";
        }

        return "Kamu sudah memulai {$activityCount} aktivitas belajar. Tips untuk pelajar {$styleLabel}: fokus pada satu materi sampai selesai sebelum pindah ke yang lain. Ayo selesaikan aktivitas yang sudah dimulai!";
    }

    /**
     * Generate activity completion feedback fallback.
     */
    private function generateActivityCompletionFeedback(mixed $activity): string
    {
        $durationMinutes = round($activity->duration_seconds / 60, 1);
        $materialTitle = $activity->material?->title ?? 'materi';

        return "Selamat! Kamu telah menyelesaikan {$materialTitle} dalam {$durationMinutes} menit. ".
            'Terus semangat belajar dan eksplorasi materi lainnya yang sudah direkomendasikan untukmu!';
    }

    /**
     * Generate weekly summary fallback feedback.
     *
     * @param  array<string, mixed>  $progressData
     */
    private function generateWeeklyFallbackFeedback(array $progressData, string $dominantStyle): string
    {
        $totalActivities = $progressData['total_activities'] ?? 0;
        $completedActivities = $progressData['completed_activities'] ?? 0;
        $totalMinutes = $progressData['total_minutes'] ?? 0;

        return "Ringkasan minggu ini: Kamu sudah belajar selama {$totalMinutes} menit, ".
            "menyelesaikan {$completedActivities} dari {$totalActivities} aktivitas. ".
            'Terus tingkatkan konsistensimu minggu depan!';
    }
}
