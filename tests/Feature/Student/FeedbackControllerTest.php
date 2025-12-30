<?php

use App\Models\AiFeedback;
use App\Models\LearningStyleProfile;
use App\Models\User;
use App\Services\GeminiAiService;

test('students can view feedback index page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/feedback');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Feedback/Index')
        ->has('feedback')
        ->has('hasLearningProfile')
    );
});

test('students can see their feedback in the list', function () {
    $student = User::factory()->create(['role' => 'student']);
    AiFeedback::factory()->count(3)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/student/feedback');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Feedback/Index')
        ->has('feedback.data', 3)
    );
});

test('students can view a single feedback', function () {
    $student = User::factory()->create(['role' => 'student']);
    $feedback = AiFeedback::factory()->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/student/feedback/'.$feedback->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Feedback/Show')
        ->has('feedback')
    );
});

test('viewing feedback marks it as read', function () {
    $student = User::factory()->create(['role' => 'student']);
    $feedback = AiFeedback::factory()->create([
        'user_id' => $student->id,
        'is_read' => false,
    ]);

    $this->actingAs($student)->get('/student/feedback/'.$feedback->id);

    $feedback->refresh();
    expect($feedback->is_read)->toBeTrue();
});

test('students cannot view other users feedback', function () {
    $student = User::factory()->create(['role' => 'student']);
    $otherStudent = User::factory()->create(['role' => 'student']);
    $feedback = AiFeedback::factory()->create(['user_id' => $otherStudent->id]);

    $response = $this->actingAs($student)->get('/student/feedback/'.$feedback->id);

    $response->assertForbidden();
});

test('students can generate feedback when they have a learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    // Mock the GeminiAiService to return null (fallback will be used)
    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateFeedback')->andReturn(null);
    });

    $response = $this->actingAs($student)->post('/student/feedback/generate');

    $response->assertRedirect();
    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'feedback_type' => 'learning_progress',
    ]);
});

test('students cannot generate feedback without learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post('/student/feedback/generate');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('guests cannot access feedback', function () {
    $response = $this->get('/student/feedback');

    $response->assertRedirect('/login');
});

test('non-students cannot access student feedback', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/student/feedback');

    $response->assertForbidden();
});

test('feedback index shows hasLearningProfile false when no profile exists', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/feedback');
    $response->assertInertia(fn ($page) => $page
        ->where('hasLearningProfile', false)
    );
});

test('feedback index shows hasLearningProfile true when profile exists', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/student/feedback');
    $response->assertInertia(fn ($page) => $page
        ->where('hasLearningProfile', true)
    );
});
