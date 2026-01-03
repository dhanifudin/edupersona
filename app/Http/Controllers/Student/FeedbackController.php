<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use App\Services\GeminiAiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function __construct(
        private GeminiAiService $geminiService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $feedback = $user->aiFeedback()
            ->orderByDesc('generated_at')
            ->paginate(10);

        return Inertia::render('student/Feedback/Index', [
            'feedback' => $feedback,
            'hasLearningProfile' => (bool) $user->learningStyleProfile,
        ]);
    }

    public function show(Request $request, AiFeedback $feedback): Response
    {
        Gate::authorize('view', $feedback);

        // Mark as read
        if (! $feedback->is_read) {
            $feedback->markAsRead();
        }

        return Inertia::render('student/Feedback/Show', [
            'feedback' => $feedback,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $learningProfile = $user->learningStyleProfile;

        if (! $learningProfile) {
            return back()->with('error', 'Silakan isi kuesioner gaya belajar terlebih dahulu.');
        }

        // Get recent activities
        $recentActivities = $user->learningActivities()
            ->with('material:id,title,type,topic')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get()
            ->map(fn ($activity) => [
                'material_title' => $activity->material?->title,
                'material_type' => $activity->material?->type,
                'topic' => $activity->material?->topic,
                'duration_minutes' => round($activity->duration_seconds / 60, 1),
                'completed' => $activity->isCompleted(),
                'date' => $activity->started_at?->format('Y-m-d'),
            ])
            ->toArray();

        // Calculate progress data
        $totalActivities = $user->learningActivities()->count();
        $completedActivities = $user->learningActivities()->whereNotNull('completed_at')->count();
        $totalMinutes = round($user->learningActivities()->sum('duration_seconds') / 60);

        $progressData = [
            'total_activities' => $totalActivities,
            'completed_activities' => $completedActivities,
            'completion_rate' => $totalActivities > 0 ? round(($completedActivities / $totalActivities) * 100) : 0,
            'total_learning_time_minutes' => $totalMinutes,
        ];

        $profileData = [
            'dominant_style' => $learningProfile->dominant_style,
            'visual_score' => $learningProfile->visual_score,
            'auditory_score' => $learningProfile->auditory_score,
            'kinesthetic_score' => $learningProfile->kinesthetic_score,
        ];

        // Generate AI feedback
        $feedbackText = $this->geminiService->generateFeedback(
            $profileData,
            $progressData,
            $recentActivities
        );

        if (! $feedbackText) {
            // Generate fallback feedback
            $feedbackText = $this->generateFallbackFeedback($learningProfile->dominant_style, $progressData);
        }

        // Save feedback
        $feedback = AiFeedback::create([
            'user_id' => $user->id,
            'context_type' => 'progress',
            'context_id' => null,
            'feedback_text' => $feedbackText,
            'feedback_type' => 'learning_progress',
            'is_read' => false,
            'generated_at' => now(),
        ]);

        return redirect()->route('feedback.show', $feedback)
            ->with('success', 'Umpan balik berhasil dibuat!');
    }

    private function generateFallbackFeedback(string $dominantStyle, array $progressData): string
    {
        $styleLabels = [
            'visual' => 'visual',
            'auditory' => 'auditori',
            'kinesthetic' => 'kinestetik',
        ];
        $styleLabel = $styleLabels[$dominantStyle] ?? 'visual';

        $completionRate = $progressData['completion_rate'] ?? 0;
        $totalMinutes = $progressData['total_learning_time_minutes'] ?? 0;

        $encouragement = $completionRate >= 70
            ? 'Bagus sekali! Kamu sudah menunjukkan konsistensi yang baik dalam belajar.'
            : 'Terus semangat! Setiap langkah kecil membawa kamu lebih dekat ke tujuan.';

        $suggestion = match ($dominantStyle) {
            'visual' => 'Coba fokus pada materi video dan infografis untuk memaksimalkan potensi belajar visualmu.',
            'auditory' => 'Manfaatkan materi audio dan video dengan penjelasan verbal untuk memperkuat pemahamanmu.',
            'kinesthetic' => 'Praktikkan langsung apa yang kamu pelajari melalui simulasi dan latihan interaktif.',
            default => 'Eksplorasi berbagai tipe materi untuk menemukan cara belajar terbaikmu.',
        };

        return <<<FEEDBACK
## Umpan Balik Pembelajaran 📚

Hai! Berikut adalah evaluasi kemajuan belajarmu:

### Statistik Belajar
- **Total waktu belajar**: {$totalMinutes} menit
- **Tingkat penyelesaian**: {$completionRate}%
- **Gaya belajar dominan**: {$styleLabel}

### Catatan
{$encouragement}

### Saran untuk Minggu Ini
{$suggestion}

Ingat, belajar adalah perjalanan, bukan lomba. Terus eksplorasi dan temukan cara belajar yang paling nyaman untukmu! 🚀
FEEDBACK;
    }
}
