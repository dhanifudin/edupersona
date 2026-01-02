<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateRecommendationsJob;
use App\Models\AiRecommendation;
use App\Models\Subject;
use App\Services\RecommendationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    public function __construct(
        private RecommendationEngine $recommendationEngine
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $hasLearningProfile = $user->learningStyleProfile !== null;

        // Get recommendations with filters
        $query = AiRecommendation::query()
            ->where('user_id', $user->id)
            ->where('is_viewed', false)
            ->with('material:id,title,description,type,learning_style,difficulty_level,subject_id,content_url', 'material.subject:id,name')
            ->orderByDesc('relevance_score');

        // Filter by subject
        if ($request->has('subject') && $request->query('subject')) {
            $query->whereHas('material', function ($q) use ($request) {
                $q->where('subject_id', $request->query('subject'));
            });
        }

        // Filter by material type
        if ($request->has('type') && $request->query('type')) {
            $query->whereHas('material', function ($q) use ($request) {
                $q->where('type', $request->query('type'));
            });
        }

        // Filter by learning style
        if ($request->has('style') && $request->query('style')) {
            $query->whereHas('material', function ($q) use ($request) {
                $q->where('learning_style', $request->query('style'));
            });
        }

        $recommendations = $query->paginate(12)->withQueryString();

        // Get viewed recommendations (history)
        $viewedRecommendations = AiRecommendation::query()
            ->where('user_id', $user->id)
            ->where('is_viewed', true)
            ->with('material:id,title,type,subject_id', 'material.subject:id,name')
            ->orderByDesc('viewed_at')
            ->limit(10)
            ->get();

        // Get subjects for filter
        $subjects = Subject::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('student/Recommendations', [
            'recommendations' => $recommendations,
            'viewedRecommendations' => $viewedRecommendations,
            'hasLearningProfile' => $hasLearningProfile,
            'learningProfile' => $user->learningStyleProfile,
            'subjects' => $subjects,
            'filters' => [
                'subject' => $request->query('subject'),
                'type' => $request->query('type'),
                'style' => $request->query('style'),
            ],
        ]);
    }

    public function markViewed(Request $request, AiRecommendation $recommendation): RedirectResponse
    {
        // Ensure the recommendation belongs to the current user
        if ($recommendation->user_id !== $request->user()->id) {
            abort(403);
        }

        $recommendation->update([
            'is_viewed' => true,
            'viewed_at' => now(),
        ]);

        return back()->with('success', 'Rekomendasi ditandai sudah dilihat');
    }

    public function refresh(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->learningStyleProfile) {
            return back()->with('error', 'Silakan lengkapi kuesioner gaya belajar terlebih dahulu');
        }

        // Dispatch job to generate new recommendations
        GenerateRecommendationsJob::dispatch($user);

        return back()->with('success', 'Rekomendasi sedang diperbarui. Silakan cek kembali dalam beberapa saat.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->learningStyleProfile) {
            return back()->with('error', 'Silakan lengkapi kuesioner gaya belajar terlebih dahulu');
        }

        // Generate recommendations synchronously for immediate feedback
        $subjectId = $request->input('subject_id');
        $recommendations = $this->recommendationEngine->generateForStudent($user, $subjectId);

        if ($recommendations->isEmpty()) {
            return back()->with('info', 'Tidak ada materi baru untuk direkomendasikan saat ini');
        }

        return back()->with('success', "Berhasil membuat {$recommendations->count()} rekomendasi baru!");
    }
}
