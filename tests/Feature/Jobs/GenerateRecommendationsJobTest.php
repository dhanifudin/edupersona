<?php

use App\Jobs\GenerateRecommendationsJob;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\GeminiAiService;
use App\Services\RecommendationEngine;
use Illuminate\Support\Facades\Queue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('job can be dispatched', function () {
    Queue::fake();

    $student = User::factory()->create(['role' => 'student']);

    GenerateRecommendationsJob::dispatch($student);

    Queue::assertPushed(GenerateRecommendationsJob::class, function ($job) use ($student) {
        return $job->user->id === $student->id;
    });
});

test('job can be dispatched with subject id', function () {
    Queue::fake();

    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();

    GenerateRecommendationsJob::dispatch($student, $subject->id);

    Queue::assertPushed(GenerateRecommendationsJob::class, function ($job) use ($student, $subject) {
        return $job->user->id === $student->id && $job->subjectId === $subject->id;
    });
});

test('job generates recommendations for student', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $subject = Subject::factory()->create();
    LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
        'is_active' => true,
        'type' => 'video',
        'learning_style' => 'visual',
    ]);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateRecommendations')->andReturn(null);
    });

    $job = new GenerateRecommendationsJob($student);
    $job->handle(app(RecommendationEngine::class));

    $this->assertDatabaseHas('ai_recommendations', [
        'user_id' => $student->id,
    ]);
});

test('job has correct tags', function () {
    $student = User::factory()->create(['role' => 'student']);

    $job = new GenerateRecommendationsJob($student);

    expect($job->tags())->toContain('recommendations')
        ->and($job->tags())->toContain('user:'.$student->id);
});
