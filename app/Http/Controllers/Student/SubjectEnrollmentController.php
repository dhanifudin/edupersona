<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubjectEnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $currentClass = $user->currentClass();

        $assignedSubjectIds = $currentClass
            ? ClassSubject::where('class_id', $currentClass->id)->pluck('subject_id')
            : collect();

        $enrollments = $user->subjectEnrollments()
            ->with(['subject' => fn ($q) => $q->withCount('materials')])
            ->active()
            ->get()
            ->map(function ($enrollment) use ($user) {
                $totalTopics = $enrollment->subject->materials()
                    ->whereNotNull('topic')
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

        return Inertia::render('student/Subjects', [
            'enrollments' => $enrollments,
            'currentClass' => $currentClass,
        ]);
    }

    public function available(Request $request): Response
    {
        $user = $request->user();
        $currentClass = $user->currentClass();

        $enrolledSubjectIds = $user->subjectEnrollments()
            ->active()
            ->pluck('subject_id');

        $assignedSubjectIds = $currentClass
            ? ClassSubject::where('class_id', $currentClass->id)->pluck('subject_id')
            : collect();

        $availableSubjects = Subject::query()
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledSubjectIds)
            ->whereNotIn('id', $assignedSubjectIds)
            ->withCount('materials')
            ->get()
            ->map(function ($subject) {
                $topicCount = $subject->materials()
                    ->whereNotNull('topic')
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

        return Inertia::render('student/SubjectsAvailable', [
            'availableSubjects' => $availableSubjects,
        ]);
    }

    public function enroll(Request $request, Subject $subject): RedirectResponse
    {
        $user = $request->user();

        $existingEnrollment = $user->subjectEnrollments()
            ->where('subject_id', $subject->id)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'dropped') {
                $existingEnrollment->update([
                    'status' => 'active',
                    'enrolled_at' => now(),
                ]);

                return back()->with('success', 'Berhasil mendaftar ulang ke '.$subject->name);
            }

            return back()->with('error', 'Kamu sudah terdaftar di mata pelajaran ini');
        }

        StudentSubjectEnrollment::create([
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'enrollment_type' => 'elective',
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        return back()->with('success', 'Berhasil mendaftar ke '.$subject->name);
    }

    public function unenroll(Request $request, Subject $subject): RedirectResponse
    {
        $user = $request->user();

        $enrollment = $user->subjectEnrollments()
            ->where('subject_id', $subject->id)
            ->active()
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Kamu tidak terdaftar di mata pelajaran ini');
        }

        if ($enrollment->enrollment_type === 'assigned') {
            return back()->with('error', 'Tidak dapat membatalkan mata pelajaran wajib');
        }

        $enrollment->update(['status' => 'dropped']);

        return back()->with('success', 'Berhasil membatalkan pendaftaran dari '.$subject->name);
    }
}
