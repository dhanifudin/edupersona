<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

        return Inertia::render('student/Dashboard', [
            'hasCompletedQuestionnaire' => $hasCompletedQuestionnaire,
            'learningProfile' => $learningProfile,
            'currentClass' => $currentClass,
            'recentActivities' => $recentActivities,
            'recommendations' => $recommendations,
        ]);
    }
}
