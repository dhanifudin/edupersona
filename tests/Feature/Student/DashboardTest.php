<?php

use App\Models\User;

test('guests are redirected to login from student dashboard', function () {
    $response = $this->get('/dashboard/student');

    $response->assertRedirect('/login');
});

test('non-students cannot access student dashboard', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/dashboard/student');

    $response->assertForbidden();
});

test('students can access student dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/dashboard/student');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Dashboard')
        ->has('hasCompletedQuestionnaire')
    );
});

test('student dashboard shows questionnaire prompt when not completed', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/dashboard/student');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Dashboard')
        ->where('hasCompletedQuestionnaire', false)
    );
});
