<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Services\RecommendationEngine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function __construct(
        private RecommendationEngine $recommendationEngine
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $learningProfile = $user->learningStyleProfile;

        // Get filter parameters
        $subjectId = $request->query('subject');
        $type = $request->query('type');
        $style = $request->query('style');

        // Build materials query
        $materialsQuery = LearningMaterial::query()
            ->with('subject:id,name,code')
            ->active();

        if ($subjectId) {
            $materialsQuery->where('subject_id', $subjectId);
        }

        if ($type) {
            $materialsQuery->where('type', $type);
        }

        if ($style) {
            $materialsQuery->forLearningStyle($style);
        } elseif ($learningProfile) {
            // Default to student's dominant style
            $materialsQuery->forLearningStyle($learningProfile->dominant_style);
        }

        $materials = $materialsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        // Get subjects for filter
        $subjects = Subject::orderBy('name')->get(['id', 'name', 'code']);

        // Get recommendations
        $recommendations = [];
        if ($learningProfile) {
            $recommendations = $user->aiRecommendations()
                ->with('material:id,title,type,learning_style,description')
                ->unviewed()
                ->orderByDesc('relevance_score')
                ->limit(5)
                ->get();
        }

        return Inertia::render('student/Materials/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
            'recommendations' => $recommendations,
            'learningProfile' => $learningProfile,
            'filters' => [
                'subject' => $subjectId,
                'type' => $type,
                'style' => $style,
            ],
        ]);
    }

    public function show(Request $request, LearningMaterial $material): Response
    {
        $user = $request->user();

        // Load relationships
        $material->load(['subject:id,name,code', 'teacher:id,name']);

        // Start tracking activity
        $activity = LearningActivity::create([
            'user_id' => $user->id,
            'material_id' => $material->id,
            'duration_seconds' => 0,
            'started_at' => now(),
        ]);

        // Mark recommendation as viewed if exists
        $user->aiRecommendations()
            ->where('material_id', $material->id)
            ->unviewed()
            ->update(['is_viewed' => true, 'viewed_at' => now()]);

        // Get related materials
        $relatedMaterials = LearningMaterial::query()
            ->where('id', '!=', $material->id)
            ->where('subject_id', $material->subject_id)
            ->active()
            ->limit(4)
            ->get(['id', 'title', 'type', 'learning_style']);

        return Inertia::render('student/Materials/Show', [
            'material' => $material,
            'activity' => $activity,
            'relatedMaterials' => $relatedMaterials,
        ]);
    }

    public function updateActivity(Request $request, LearningActivity $activity)
    {
        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $activity->update([
            'duration_seconds' => $validated['duration_seconds'],
            'completed_at' => ($validated['completed'] ?? false) ? now() : null,
        ]);

        return response()->json(['success' => true]);
    }

    public function refreshRecommendations(Request $request)
    {
        $user = $request->user();

        if (! $user->learningStyleProfile) {
            return back()->with('error', 'Silakan isi kuesioner gaya belajar terlebih dahulu.');
        }

        $this->recommendationEngine->refreshForStudent($user);

        return back()->with('success', 'Rekomendasi berhasil diperbarui!');
    }
}
