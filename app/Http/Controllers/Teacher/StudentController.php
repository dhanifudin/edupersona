<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        $teacher = $request->user();

        // Get all classes where teacher is homeroom or teaches subjects
        $homeroomClassIds = $teacher->homeroomClasses()->pluck('id');
        $teachingClassIds = $teacher->classSubjects()->pluck('class_id');
        $classIds = $homeroomClassIds->merge($teachingClassIds)->unique();

        $query = User::query()
            ->where('role', 'student')
            ->whereHas('classes', fn ($q) => $q->whereIn('classes.id', $classIds))
            ->with(['learningStyleProfile', 'classes' => fn ($q) => $q->whereIn('classes.id', $classIds)])
            ->withCount([
                'learningActivities',
                'learningActivities as completed_activities_count' => fn ($q) => $q->whereNotNull('completed_at'),
            ]);

        // Filter by class
        if ($request->has('class') && $request->query('class')) {
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $request->query('class')));
        }

        // Filter by learning style
        if ($request->has('style') && $request->query('style')) {
            $query->whereHas('learningStyleProfile', fn ($q) => $q->where('dominant_style', $request->query('style')));
        }

        // Search by name
        if ($request->has('search') && $request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('student_id_number', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(20)->withQueryString();

        // Get classes for filter
        $classes = ClassRoom::whereIn('id', $classIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        return Inertia::render('teacher/Students/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'class' => $request->query('class'),
                'style' => $request->query('style'),
                'search' => $request->query('search'),
            ],
        ]);
    }

    public function show(Request $request, User $student): Response
    {
        $teacher = $request->user();

        // Verify teacher has access to this student
        $homeroomClassIds = $teacher->homeroomClasses()->pluck('id');
        $teachingClassIds = $teacher->classSubjects()->pluck('class_id');
        $classIds = $homeroomClassIds->merge($teachingClassIds)->unique();

        $hasAccess = $student->classes()->whereIn('classes.id', $classIds)->exists();
        abort_unless($hasAccess, 403);

        $student->load([
            'learningStyleProfile',
            'classes' => fn ($q) => $q->whereIn('classes.id', $classIds),
        ]);

        // Get recent activities
        $recentActivities = $student->learningActivities()
            ->with('material:id,title,type,topic')
            ->orderByDesc('started_at')
            ->limit(20)
            ->get();

        // Get activity stats
        $stats = [
            'totalActivities' => $student->learningActivities()->count(),
            'completedActivities' => $student->learningActivities()->whereNotNull('completed_at')->count(),
            'totalLearningTime' => round($student->learningActivities()->sum('duration_seconds') / 60),
            'averageSessionTime' => round($student->learningActivities()->avg('duration_seconds') / 60, 1),
        ];

        // Get AI recommendations for this student
        $recommendations = $student->aiRecommendations()
            ->with('material:id,title,type')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get AI feedback
        $feedback = $student->aiFeedback()
            ->orderByDesc('generated_at')
            ->limit(5)
            ->get();

        return Inertia::render('teacher/Students/Show', [
            'student' => $student,
            'recentActivities' => $recentActivities,
            'stats' => $stats,
            'recommendations' => $recommendations,
            'feedback' => $feedback,
        ]);
    }
}
