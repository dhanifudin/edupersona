<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use App\Models\ClassRoom;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // User statistics
        $userStats = [
            'totalUsers' => User::count(),
            'totalStudents' => User::where('role', 'student')->count(),
            'totalTeachers' => User::where('role', 'teacher')->count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
            'newUsersThisMonth' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        // Class statistics
        $classStats = [
            'totalClasses' => ClassRoom::count(),
            'activeClasses' => ClassRoom::where('is_active', true)->count(),
            'totalSubjects' => Subject::count(),
        ];

        // Learning statistics
        $learningStats = [
            'totalMaterials' => LearningMaterial::count(),
            'activeMaterials' => LearningMaterial::where('is_active', true)->count(),
            'totalActivities' => LearningActivity::count(),
            'completedActivities' => LearningActivity::whereNotNull('completed_at')->count(),
            'totalLearningHours' => round(LearningActivity::sum('duration_seconds') / 3600, 1),
        ];

        // Learning style distribution (all students)
        $learningStyleDistribution = LearningStyleProfile::selectRaw('dominant_style, count(*) as count')
            ->groupBy('dominant_style')
            ->pluck('count', 'dominant_style')
            ->toArray();

        // Students who completed questionnaire
        $questionnaireStats = [
            'completed' => LearningStyleProfile::count(),
            'pending' => User::where('role', 'student')
                ->whereDoesntHave('learningStyleProfile')
                ->count(),
        ];

        // Recent activities (last 30 days)
        $activityTrend = LearningActivity::selectRaw('DATE(started_at) as date, count(*) as count')
            ->where('started_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Top materials by views
        $topMaterials = LearningMaterial::withCount('activities')
            ->orderByDesc('activities_count')
            ->limit(5)
            ->get(['id', 'title', 'type']);

        // Recent users
        $recentUsers = User::orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        // AI feedback statistics
        $feedbackStats = [
            'totalFeedback' => AiFeedback::count(),
            'thisMonth' => AiFeedback::whereMonth('generated_at', now()->month)
                ->whereYear('generated_at', now()->year)
                ->count(),
        ];

        return Inertia::render('admin/Dashboard', [
            'userStats' => $userStats,
            'classStats' => $classStats,
            'learningStats' => $learningStats,
            'learningStyleDistribution' => $learningStyleDistribution,
            'questionnaireStats' => $questionnaireStats,
            'activityTrend' => $activityTrend,
            'topMaterials' => $topMaterials,
            'recentUsers' => $recentUsers,
            'feedbackStats' => $feedbackStats,
        ]);
    }
}
