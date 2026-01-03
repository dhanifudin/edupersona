<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $learningProfile = $user->learningStyleProfile;
        $hasCompletedQuestionnaire = $learningProfile !== null;
        $currentClass = $user->currentClass();

        $recentActivities = $user->learningActivities()
            ->with('material:id,title,type,learning_style')
            ->latest('started_at')
            ->limit(5)
            ->get();

        $recommendations = $user->aiRecommendations()
            ->with('material:id,title,type,learning_style,description')
            ->unviewed()
            ->orderByDesc('relevance_score')
            ->limit(5)
            ->get();

        $enrollments = $user->subjectEnrollments()
            ->with(['subject' => fn ($q) => $q->withCount('materials')])
            ->active()
            ->get()
            ->map(function ($enrollment) use ($user) {
                $totalTopics = LearningMaterial::where('subject_id', $enrollment->subject_id)
                    ->whereNotNull('topic')
                    ->where('is_active', true)
                    ->distinct('topic')
                    ->count('topic');

                $completedTopics = $user->studentProgress()
                    ->where('subject_id', $enrollment->subject_id)
                    ->where('status', 'completed')
                    ->count();

                return [
                    'id' => $enrollment->id,
                    'subject' => $enrollment->subject,
                    'enrollment_type' => $enrollment->enrollment_type,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'status' => $enrollment->status,
                    'progress' => [
                        'completedTopics' => $completedTopics,
                        'totalTopics' => $totalTopics,
                        'percentage' => $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0,
                    ],
                ];
            });

        $availableSubjects = Subject::query()
            ->where('is_active', true)
            ->whereNotIn('id', $user->subjectEnrollments()->active()->pluck('subject_id'))
            ->withCount('materials')
            ->get()
            ->map(function ($subject) {
                $topicCount = LearningMaterial::where('subject_id', $subject->id)
                    ->whereNotNull('topic')
                    ->where('is_active', true)
                    ->distinct('topic')
                    ->count('topic');

                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'description' => $subject->description,
                    'materials_count' => $subject->materials_count,
                    'topic_count' => $topicCount,
                ];
            });

        $totalCompletedTopics = $user->studentProgress()
            ->where('status', 'completed')
            ->count();

        $totalTopics = 0;
        foreach ($enrollments as $enrollment) {
            $totalTopics += $enrollment['progress']['totalTopics'];
        }

        return Inertia::render('student/Dashboard', [
            'hasCompletedQuestionnaire' => $hasCompletedQuestionnaire,
            'learningProfile' => $learningProfile,
            'currentClass' => $currentClass,
            'recentActivities' => $recentActivities,
            'recommendations' => $recommendations,
            'enrollments' => $enrollments,
            'availableSubjects' => $availableSubjects,
            'stats' => [
                'completedTopics' => $totalCompletedTopics,
                'totalTopics' => $totalTopics,
            ],
        ]);
    }
}
