<?php

use App\Models\ClassRoom;
use App\Models\LearningMaterial;
use App\Models\StudentProgress;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\User;

test('guests are redirected to login from subject learning page', function () {
    $subject = Subject::factory()->create();

    $response = $this->get("/student/subjects/{$subject->id}/learn");

    $response->assertRedirect('/login');
});

test('non-students cannot access subject learning page', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($teacher)->get("/student/subjects/{$subject->id}/learn");

    $response->assertForbidden();
});

test('students cannot access learning page for unenrolled subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($student)->get("/student/subjects/{$subject->id}/learn");

    $response->assertForbidden();
});

test('students can access learning page for enrolled subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
        'topic' => 'Introduction',
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get("/student/subjects/{$subject->id}/learn");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/SubjectLearning')
        ->has('subject')
        ->has('enrollment')
        ->has('topics')
    );
});

test('students can view topic list with progress', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();
    $classRoom = ClassRoom::factory()->create();

    $student->classes()->attach($classRoom->id, [
        'enrolled_at' => now(),
        'status' => 'active',
    ]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
        'topic' => 'Topic 1',
        'is_active' => true,
    ]);

    LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
        'topic' => 'Topic 2',
        'is_active' => true,
    ]);

    StudentProgress::create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'class_id' => $classRoom->id,
        'topic' => 'Topic 1',
        'status' => 'completed',
    ]);

    $response = $this->actingAs($student)->get("/student/subjects/{$subject->id}/topics");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/SubjectTopics')
        ->has('topics', 2)
    );
});

test('students can view topic detail with materials', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    LearningMaterial::factory()->count(3)->create([
        'subject_id' => $subject->id,
        'topic' => 'Introduction',
        'is_active' => true,
    ]);

    $response = $this->actingAs($student)->get("/student/subjects/{$subject->id}/topics/Introduction");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/TopicDetail')
        ->has('subject')
        ->where('topic', 'Introduction')
        ->has('materials', 3)
    );
});

test('students can start a topic', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();
    $classRoom = ClassRoom::factory()->create();

    $student->classes()->attach($classRoom->id, [
        'enrolled_at' => now(),
        'status' => 'active',
    ]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/topics/Introduction/start");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_progress', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'class_id' => $classRoom->id,
        'topic' => 'Introduction',
        'status' => 'in_progress',
    ]);
});

test('starting topic updates existing progress', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();
    $classRoom = ClassRoom::factory()->create();

    $student->classes()->attach($classRoom->id, [
        'enrolled_at' => now(),
        'status' => 'active',
    ]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    StudentProgress::create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'class_id' => $classRoom->id,
        'topic' => 'Introduction',
        'status' => 'not_started',
    ]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/topics/Introduction/start");

    $response->assertRedirect();

    $this->assertDatabaseHas('student_progress', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'topic' => 'Introduction',
        'status' => 'in_progress',
    ]);
});

test('students can complete a topic', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();
    $classRoom = ClassRoom::factory()->create();

    $student->classes()->attach($classRoom->id, [
        'enrolled_at' => now(),
        'status' => 'active',
    ]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    StudentProgress::create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'class_id' => $classRoom->id,
        'topic' => 'Introduction',
        'status' => 'in_progress',
    ]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/topics/Introduction/complete");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_progress', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'topic' => 'Introduction',
        'status' => 'completed',
    ]);
});
