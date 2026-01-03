<?php

use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\User;

test('guests are redirected to login from subjects page', function () {
    $response = $this->get('/student/subjects');

    $response->assertRedirect('/login');
});

test('non-students cannot access subjects page', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/student/subjects');

    $response->assertForbidden();
});

test('students can view enrolled subjects', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'enrollment_type' => 'assigned',
        'status' => 'active',
    ]);

    $response = $this->actingAs($student)->get('/student/subjects');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Subjects')
        ->has('enrollments', 1)
    );
});

test('students can view available elective subjects', function () {
    $student = User::factory()->create(['role' => 'student']);
    Subject::factory()->count(3)->create(['is_active' => true]);

    $response = $this->actingAs($student)->get('/student/subjects/available');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/SubjectsAvailable')
        ->has('availableSubjects', 3)
    );
});

test('students can enroll in elective subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/enroll");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_subject_enrollments', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'enrollment_type' => 'elective',
        'status' => 'active',
    ]);
});

test('students cannot enroll in already enrolled subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/enroll");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('students can re-enroll in dropped subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'dropped',
    ]);

    $response = $this->actingAs($student)->post("/student/subjects/{$subject->id}/enroll");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_subject_enrollments', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);
});

test('students can unenroll from elective subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'enrollment_type' => 'elective',
        'status' => 'active',
    ]);

    $response = $this->actingAs($student)->delete("/student/subjects/{$subject->id}/unenroll");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('student_subject_enrollments', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'dropped',
    ]);
});

test('students cannot unenroll from assigned subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    StudentSubjectEnrollment::factory()->create([
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'enrollment_type' => 'assigned',
        'status' => 'active',
    ]);

    $response = $this->actingAs($student)->delete("/student/subjects/{$subject->id}/unenroll");

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('student_subject_enrollments', [
        'user_id' => $student->id,
        'subject_id' => $subject->id,
        'status' => 'active',
    ]);
});

test('students cannot unenroll from subject they are not enrolled in', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['is_active' => true]);

    $response = $this->actingAs($student)->delete("/student/subjects/{$subject->id}/unenroll");

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
