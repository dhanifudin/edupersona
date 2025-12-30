<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LearningStyleAnalyzer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningProfileController extends Controller
{
    public function __construct(
        private LearningStyleAnalyzer $analyzer
    ) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $learningProfile = $user->learningStyleProfile;

        // Redirect to questionnaire if not completed
        if (! $learningProfile) {
            return redirect()->route('student.questionnaire.index')
                ->with('info', 'Silakan isi kuesioner terlebih dahulu untuk melihat profil gaya belajarmu.');
        }

        $recommendations = $this->analyzer->getRecommendations($learningProfile->dominant_style);

        return Inertia::render('student/LearningProfile', [
            'learningProfile' => $learningProfile,
            'recommendations' => $recommendations,
        ]);
    }
}
