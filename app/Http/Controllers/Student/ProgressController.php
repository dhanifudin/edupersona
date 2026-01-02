<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProgressController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get overall statistics
        $stats = $this->getOverallStats($user);

        // Get weekly activity data for chart
        $weeklyActivity = $this->getWeeklyActivity($user);

        // Get progress by subject
        $progressBySubject = $this->getProgressBySubject($user);

        // Get recent completed activities
        $recentCompleted = $user->learningActivities()
            ->with('material:id,title,type,subject_id', 'material.subject:id,name')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(10)
            ->get();

        // Get learning streak (consecutive days)
        $streak = $this->calculateStreak($user);

        // Get activity by material type
        $activityByType = $this->getActivityByType($user);

        return Inertia::render('student/Progress', [
            'stats' => $stats,
            'weeklyActivity' => $weeklyActivity,
            'progressBySubject' => $progressBySubject,
            'recentCompleted' => $recentCompleted,
            'streak' => $streak,
            'activityByType' => $activityByType,
        ]);
    }

    /**
     * Get overall learning statistics.
     *
     * @return array<string, mixed>
     */
    private function getOverallStats(mixed $user): array
    {
        $activities = $user->learningActivities();

        $totalMinutes = round($activities->sum('duration_seconds') / 60);
        $totalActivities = $activities->count();
        $completedActivities = $activities->whereNotNull('completed_at')->count();
        $uniqueMaterials = $activities->distinct('material_id')->count('material_id');

        // Topics completed from StudentProgress
        $topicsCompleted = $user->studentProgress()->completed()->count();
        $topicsInProgress = $user->studentProgress()->inProgress()->count();

        return [
            'totalMinutes' => $totalMinutes,
            'totalHours' => round($totalMinutes / 60, 1),
            'totalActivities' => $totalActivities,
            'completedActivities' => $completedActivities,
            'uniqueMaterials' => $uniqueMaterials,
            'topicsCompleted' => $topicsCompleted,
            'topicsInProgress' => $topicsInProgress,
            'completionRate' => $totalActivities > 0
                ? round(($completedActivities / $totalActivities) * 100)
                : 0,
        ];
    }

    /**
     * Get weekly activity data for the last 4 weeks.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getWeeklyActivity(mixed $user): array
    {
        $weeks = [];
        $startOfWeek = now()->startOfWeek();

        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $startOfWeek->copy()->subWeeks($i);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $activities = $user->learningActivities()
                ->whereBetween('started_at', [$weekStart, $weekEnd])
                ->get();

            $weeks[] = [
                'week' => 'Minggu '.($i === 0 ? 'ini' : ($i === 1 ? 'lalu' : $i.' lalu')),
                'weekStart' => $weekStart->format('d M'),
                'weekEnd' => $weekEnd->format('d M'),
                'activities' => $activities->count(),
                'completed' => $activities->whereNotNull('completed_at')->count(),
                'minutes' => round($activities->sum('duration_seconds') / 60),
            ];
        }

        return $weeks;
    }

    /**
     * Get progress grouped by subject.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getProgressBySubject(mixed $user): array
    {
        // Get activity data grouped by subject
        $activityBySubject = LearningActivity::query()
            ->where('user_id', $user->id)
            ->join('learning_materials', 'learning_activities.material_id', '=', 'learning_materials.id')
            ->join('subjects', 'learning_materials.subject_id', '=', 'subjects.id')
            ->select(
                'subjects.id',
                'subjects.name',
                DB::raw('COUNT(learning_activities.id) as total_activities'),
                DB::raw('SUM(CASE WHEN learning_activities.completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(learning_activities.duration_seconds) as total_seconds')
            )
            ->groupBy('subjects.id', 'subjects.name')
            ->get();

        return $activityBySubject->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'totalActivities' => $item->total_activities,
                'completed' => $item->completed,
                'minutes' => round($item->total_seconds / 60),
                'completionRate' => $item->total_activities > 0
                    ? round(($item->completed / $item->total_activities) * 100)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Calculate learning streak (consecutive days of activity).
     *
     * @return array<string, mixed>
     */
    private function calculateStreak(mixed $user): array
    {
        $activeDates = $user->learningActivities()
            ->select(DB::raw('DATE(started_at) as activity_date'))
            ->distinct()
            ->orderByDesc('activity_date')
            ->pluck('activity_date')
            ->map(fn ($date) => $date instanceof \DateTime ? $date->format('Y-m-d') : $date);

        if ($activeDates->isEmpty()) {
            return [
                'current' => 0,
                'longest' => 0,
                'lastActivityDate' => null,
            ];
        }

        $currentStreak = 0;
        $longestStreak = 0;
        $tempStreak = 1;
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        // Check if active today or yesterday for current streak
        $firstDate = $activeDates->first();
        if ($firstDate === $today || $firstDate === $yesterday) {
            $currentStreak = 1;
            $checkDate = $firstDate === $today ? now() : now()->subDay();

            foreach ($activeDates->skip(1) as $date) {
                $checkDate = $checkDate->subDay();
                if ($date === $checkDate->format('Y-m-d')) {
                    $currentStreak++;
                } else {
                    break;
                }
            }
        }

        // Calculate longest streak
        $previousDate = null;
        foreach ($activeDates as $date) {
            if ($previousDate === null) {
                $tempStreak = 1;
            } else {
                $diff = (new \DateTime($previousDate))->diff(new \DateTime($date))->days;
                if ($diff === 1) {
                    $tempStreak++;
                } else {
                    $longestStreak = max($longestStreak, $tempStreak);
                    $tempStreak = 1;
                }
            }
            $previousDate = $date;
        }
        $longestStreak = max($longestStreak, $tempStreak);

        return [
            'current' => $currentStreak,
            'longest' => $longestStreak,
            'lastActivityDate' => $activeDates->first(),
        ];
    }

    /**
     * Get activity grouped by material type.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getActivityByType(mixed $user): array
    {
        $byType = LearningActivity::query()
            ->where('user_id', $user->id)
            ->join('learning_materials', 'learning_activities.material_id', '=', 'learning_materials.id')
            ->select(
                'learning_materials.type',
                DB::raw('COUNT(learning_activities.id) as count'),
                DB::raw('SUM(learning_activities.duration_seconds) as total_seconds')
            )
            ->groupBy('learning_materials.type')
            ->get();

        $typeLabels = [
            'video' => 'Video',
            'document' => 'Dokumen',
            'infographic' => 'Infografis',
            'audio' => 'Audio',
            'simulation' => 'Simulasi',
        ];

        return $byType->map(function ($item) use ($typeLabels) {
            return [
                'type' => $item->type,
                'label' => $typeLabels[$item->type] ?? $item->type,
                'count' => $item->count,
                'minutes' => round($item->total_seconds / 60),
            ];
        })->toArray();
    }
}
