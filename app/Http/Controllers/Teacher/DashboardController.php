<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearningActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get teacher's classes (where they teach subjects or are homeroom teacher)
        $teachingClasses = $user->classSubjects()
            ->with(['classRoom:id,name,grade_level,major', 'subject:id,name,code'])
            ->get()
            ->groupBy('class_id');

        $homeroomClasses = $user->homeroomClasses()
            ->with('activeStudents:id,name')
            ->where('is_active', true)
            ->get();

        // Get total students count from homeroom classes
        $totalStudents = $homeroomClasses->sum(fn ($class) => $class->activeStudents->count());

        // Get teacher's uploaded materials
        $totalMaterials = $user->uploadedMaterials()->count();
        $activeMaterials = $user->uploadedMaterials()->where('is_active', true)->count();

        // Get recent materials
        $recentMaterials = $user->uploadedMaterials()
            ->with('subject:id,name,code')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Get student activity stats (for teacher's materials)
        $materialIds = $user->uploadedMaterials()->pluck('id');
        $recentActivities = LearningActivity::query()
            ->whereIn('material_id', $materialIds)
            ->with(['user:id,name', 'material:id,title,type'])
            ->orderByDesc('started_at')
            ->limit(10)
            ->get();

        $totalViews = LearningActivity::whereIn('material_id', $materialIds)->count();
        $totalCompletions = LearningActivity::whereIn('material_id', $materialIds)
            ->whereNotNull('completed_at')
            ->count();

        // Learning style distribution of students
        $learningStyleDistribution = [];
        foreach ($homeroomClasses as $class) {
            foreach ($class->activeStudents as $student) {
                $profile = $student->learningStyleProfile;
                if ($profile) {
                    $style = $profile->dominant_style;
                    $learningStyleDistribution[$style] = ($learningStyleDistribution[$style] ?? 0) + 1;
                }
            }
        }

        return Inertia::render('teacher/Dashboard', [
            'stats' => [
                'totalStudents' => $totalStudents,
                'totalMaterials' => $totalMaterials,
                'activeMaterials' => $activeMaterials,
                'totalViews' => $totalViews,
                'totalCompletions' => $totalCompletions,
                'completionRate' => $totalViews > 0 ? round(($totalCompletions / $totalViews) * 100) : 0,
            ],
            'teachingClasses' => $teachingClasses->map(fn ($subjects) => [
                'class' => $subjects->first()->classRoom,
                'subjects' => $subjects->pluck('subject'),
            ])->values(),
            'homeroomClasses' => $homeroomClasses,
            'recentMaterials' => $recentMaterials,
            'recentActivities' => $recentActivities,
            'learningStyleDistribution' => $learningStyleDistribution,
        ]);
    }
}
