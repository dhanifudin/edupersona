<?php

use App\Models\LearningStyleProfile;
use App\Models\LearningStyleQuestion;
use App\Models\User;
use Database\Seeders\LearningStyleQuestionSeeder;

beforeEach(function () {
    $this->seed(LearningStyleQuestionSeeder::class);
});

test('students can view questionnaire page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/questionnaire');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Questionnaire')
        ->has('questions', 15)
    );
});

test('students with completed questionnaire are redirected to learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/student/questionnaire');

    $response->assertRedirect('/student/learning-profile');
});

test('students can submit questionnaire responses', function () {
    $student = User::factory()->create(['role' => 'student']);
    $questions = LearningStyleQuestion::active()->get();

    $responses = $questions->map(fn ($q) => [
        'question_id' => $q->id,
        'score' => fake()->numberBetween(1, 5),
    ])->toArray();

    $response = $this->actingAs($student)
        ->post('/student/questionnaire', ['responses' => $responses]);

    $response->assertRedirect('/student/learning-profile');
    $this->assertDatabaseHas('learning_style_profiles', [
        'user_id' => $student->id,
    ]);
});

test('questionnaire requires all questions to be answered', function () {
    $student = User::factory()->create(['role' => 'student']);

    // Only answer some questions
    $responses = [
        ['question_id' => 1, 'score' => 4],
        ['question_id' => 2, 'score' => 3],
    ];

    $response = $this->actingAs($student)
        ->post('/student/questionnaire', ['responses' => $responses]);

    $response->assertSessionHasErrors('responses');
});
