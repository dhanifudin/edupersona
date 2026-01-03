<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use App\Models\StudentProgress;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectLearningController extends Controller
{
    public function show(Request $request, Subject $subject): Response
    {
        $user = $request->user();

        $enrollment = $user->subjectEnrollments()
            ->where('subject_id', $subject->id)
            ->active()
            ->first();

        if (! $enrollment) {
            abort(403, 'Kamu tidak terdaftar di mata pelajaran ini');
        }

        $topics = $this->getTopicsWithProgress($user, $subject);
        $currentTopic = $this->getCurrentTopic($user, $subject, $topics);

        return Inertia::render('student/SubjectLearning', [
            'subject' => $subject,
            'enrollment' => $enrollment,
            'topics' => $topics,
            'currentTopic' => $currentTopic,
        ]);
    }

    public function topics(Request $request, Subject $subject): Response
    {
        $user = $request->user();
        $topics = $this->getTopicsWithProgress($user, $subject);

        return Inertia::render('student/SubjectTopics', [
            'subject' => $subject,
            'topics' => $topics,
        ]);
    }

    public function topic(Request $request, Subject $subject, string $topic): Response
    {
        $user = $request->user();
        $learningProfile = $user->learningStyleProfile;

        $dominantStyle = $learningProfile?->dominant_style ?? 'general';

        $materials = LearningMaterial::query()
            ->where('subject_id', $subject->id)
            ->where('topic', $topic)
            ->where('is_active', true)
            ->orderByRaw("CASE
                WHEN learning_style = ? THEN 0
                WHEN learning_style = 'general' THEN 1
                ELSE 2
            END", [$dominantStyle])
            ->get();

        $progress = $user->studentProgress()
            ->where('subject_id', $subject->id)
            ->where('topic', $topic)
            ->first();

        $recommendations = $user->aiRecommendations()
            ->with('material:id,title,type,learning_style,description')
            ->whereHas('material', fn ($q) => $q->where('subject_id', $subject->id)->where('topic', $topic))
            ->unviewed()
            ->orderByDesc('relevance_score')
            ->limit(3)
            ->get();

        return Inertia::render('student/TopicDetail', [
            'subject' => $subject,
            'topic' => $topic,
            'materials' => $materials,
            'progress' => $progress,
            'recommendations' => $recommendations,
            'learningProfile' => $learningProfile,
        ]);
    }

    public function startTopic(Request $request, Subject $subject, string $topic): RedirectResponse
    {
        $user = $request->user();
        $currentClass = $user->currentClass();

        $existingProgress = $user->studentProgress()
            ->where('subject_id', $subject->id)
            ->where('topic', $topic)
            ->first();

        if ($existingProgress) {
            $existingProgress->update([
                'status' => 'in_progress',
                'last_activity_at' => now(),
            ]);
        } else {
            StudentProgress::create([
                'user_id' => $user->id,
                'subject_id' => $subject->id,
                'class_id' => $currentClass?->id,
                'topic' => $topic,
                'status' => 'in_progress',
                'attempts' => 1,
                'last_activity_at' => now(),
            ]);
        }

        return back()->with('success', 'Mulai belajar topik: '.$topic);
    }

    public function completeTopic(Request $request, Subject $subject, string $topic): RedirectResponse
    {
        $user = $request->user();

        $progress = $user->studentProgress()
            ->where('subject_id', $subject->id)
            ->where('topic', $topic)
            ->first();

        if ($progress) {
            $progress->update([
                'status' => 'completed',
                'last_activity_at' => now(),
            ]);
        }

        return back()->with('success', 'Selamat! Kamu telah menyelesaikan topik: '.$topic);
    }

    private function getTopicsWithProgress($user, Subject $subject): array
    {
        $topics = LearningMaterial::query()
            ->where('subject_id', $subject->id)
            ->whereNotNull('topic')
            ->where('is_active', true)
            ->distinct()
            ->pluck('topic')
            ->toArray();

        $progressMap = $user->studentProgress()
            ->where('subject_id', $subject->id)
            ->pluck('status', 'topic')
            ->toArray();

        return array_map(function ($topic) use ($progressMap, $subject) {
            $materialsCount = LearningMaterial::where('subject_id', $subject->id)
                ->where('topic', $topic)
                ->where('is_active', true)
                ->count();

            return [
                'name' => $topic,
                'status' => $progressMap[$topic] ?? 'not_started',
                'materialsCount' => $materialsCount,
            ];
        }, $topics);
    }

    private function getCurrentTopic($user, Subject $subject, array $topics): ?array
    {
        $inProgress = collect($topics)->firstWhere('status', 'in_progress');
        if ($inProgress) {
            return $inProgress;
        }

        return collect($topics)->firstWhere('status', 'not_started');
    }
}
