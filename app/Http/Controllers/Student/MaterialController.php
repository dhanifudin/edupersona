<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateFeedbackJob;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\StudentProgress;
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

        // Start/update topic progress if material has a topic
        $topicNavigation = null;
        if ($material->topic && $material->subject_id) {
            $currentClass = $user->currentClass();

            // Only create/update progress if user has a class or progress already exists
            $existingProgress = StudentProgress::where('user_id', $user->id)
                ->where('subject_id', $material->subject_id)
                ->where('topic', $material->topic)
                ->first();

            if ($existingProgress) {
                $existingProgress->update([
                    'status' => $existingProgress->status === 'completed' ? 'completed' : 'in_progress',
                    'last_activity_at' => now(),
                ]);
            } elseif ($currentClass) {
                StudentProgress::create([
                    'user_id' => $user->id,
                    'subject_id' => $material->subject_id,
                    'topic' => $material->topic,
                    'class_id' => $currentClass->id,
                    'status' => 'in_progress',
                    'last_activity_at' => now(),
                    'attempts' => 1,
                ]);
            }

            // Get all topics for this subject (ordered)
            $allTopics = LearningMaterial::query()
                ->where('subject_id', $material->subject_id)
                ->where('is_active', true)
                ->distinct()
                ->pluck('topic')
                ->filter()
                ->values();

            // Find current topic index and next topic
            $currentTopicIndex = $allTopics->search($material->topic);
            $nextTopic = $currentTopicIndex !== false && $currentTopicIndex < $allTopics->count() - 1
                ? $allTopics[$currentTopicIndex + 1]
                : null;

            // Get other materials in same topic with different learning styles
            $otherStyleMaterials = LearningMaterial::query()
                ->where('subject_id', $material->subject_id)
                ->where('topic', $material->topic)
                ->where('id', '!=', $material->id)
                ->where('is_active', true)
                ->get(['id', 'title', 'type', 'learning_style', 'difficulty_level']);

            $topicNavigation = [
                'subjectId' => $material->subject_id,
                'subjectName' => $material->subject?->name,
                'currentTopic' => $material->topic,
                'nextTopic' => $nextTopic,
                'otherStyleMaterials' => $otherStyleMaterials->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'type' => $m->type,
                    'learning_style' => $m->learning_style,
                    'difficulty_level' => $m->difficulty_level,
                ]),
            ];
        }

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
            'topicNavigation' => $topicNavigation,
        ]);
    }

    public function updateActivity(Request $request, LearningActivity $activity)
    {
        $validated = $request->validate([
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $wasCompleted = $activity->completed_at !== null;
        $isNowCompleted = $validated['completed'] ?? false;

        \Log::info('updateActivity called', [
            'activity_id' => $activity->id,
            'material_id' => $activity->material_id,
            'wasCompleted' => $wasCompleted,
            'isNowCompleted' => $isNowCompleted,
            'duration_seconds' => $validated['duration_seconds'],
        ]);

        $activity->update([
            'duration_seconds' => $validated['duration_seconds'],
            'completed_at' => $isNowCompleted ? now() : null,
        ]);

        // When activity is newly completed, mark the topic as completed
        if ($isNowCompleted && ! $wasCompleted) {
            $material = $activity->material;
            $user = $request->user();

            \Log::info('Activity newly completed, updating topic progress', [
                'material_id' => $material?->id,
                'topic' => $material?->topic,
                'subject_id' => $material?->subject_id,
            ]);

            // Mark topic as completed (create if not exists)
            if ($material && $material->topic) {
                $currentClass = $user->currentClass();

                $progress = StudentProgress::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'subject_id' => $material->subject_id,
                        'topic' => $material->topic,
                    ],
                    [
                        'class_id' => $currentClass?->id,
                        'status' => 'completed',
                        'last_activity_at' => now(),
                        'attempts' => 1,
                    ]
                );

                \Log::info('Topic progress updated', [
                    'progress_id' => $progress->id,
                    'status' => $progress->status,
                ]);
            }

            // Generate feedback
            GenerateFeedbackJob::dispatch(
                $user,
                'activity_completion',
                $activity->id
            );
        }

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
