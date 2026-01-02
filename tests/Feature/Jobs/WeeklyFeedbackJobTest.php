<?php

use App\Jobs\WeeklyFeedbackJob;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\FeedbackGenerator;
use App\Services\GeminiAiService;
use Illuminate\Support\Facades\Queue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('job can be dispatched', function () {
    Queue::fake();

    WeeklyFeedbackJob::dispatch();

    Queue::assertPushed(WeeklyFeedbackJob::class);
});

test('job generates feedback for active students', function () {
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
    $material = LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
    ]);

    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subDays(2),
    ]);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateFeedback')->andReturn('Weekly summary');
    });

    $job = new WeeklyFeedbackJob;
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'weekly_summary',
    ]);
});

test('job generates inactivity reminder for inactive students', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    // No recent activities

    $job = new WeeklyFeedbackJob;
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'inactivity_reminder',
        'feedback_type' => 'warning',
    ]);
});

test('job skips students without learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    // No learning profile

    $job = new WeeklyFeedbackJob;
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseMissing('ai_feedback', [
        'user_id' => $student->id,
    ]);
});

test('job skips non-student users', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    LearningStyleProfile::create([
        'user_id' => $teacher->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $job = new WeeklyFeedbackJob;
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseMissing('ai_feedback', [
        'user_id' => $teacher->id,
    ]);
});

test('job has correct tags', function () {
    $job = new WeeklyFeedbackJob;

    expect($job->tags())->toContain('feedback')
        ->and($job->tags())->toContain('weekly')
        ->and($job->tags())->toContain('batch');
});
