<?php

use App\Jobs\GenerateFeedbackJob;
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

    $student = User::factory()->create(['role' => 'student']);

    GenerateFeedbackJob::dispatch($student);

    Queue::assertPushed(GenerateFeedbackJob::class, function ($job) use ($student) {
        return $job->user->id === $student->id;
    });
});

test('job can be dispatched with context type', function () {
    Queue::fake();

    $student = User::factory()->create(['role' => 'student']);

    GenerateFeedbackJob::dispatch($student, 'weekly_summary');

    Queue::assertPushed(GenerateFeedbackJob::class, function ($job) use ($student) {
        return $job->user->id === $student->id && $job->contextType === 'weekly_summary';
    });
});

test('job generates general feedback', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateFeedback')->andReturn('Test feedback');
    });

    $job = new GenerateFeedbackJob($student, 'general');
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'general',
    ]);
});

test('job generates activity completion feedback', function () {
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

    $activity = LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subMinutes(10),
        'completed_at' => now(),
    ]);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateFeedback')->andReturn('Great job!');
    });

    $job = new GenerateFeedbackJob($student, 'activity_completion', $activity->id);
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'activity_completion',
        'context_id' => $activity->id,
    ]);
});

test('job generates weekly summary', function () {
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
        $mock->shouldReceive('generateFeedback')->andReturn('Great week!');
    });

    $job = new GenerateFeedbackJob($student, 'weekly_summary');
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'weekly_summary',
    ]);
});

test('job generates inactivity reminder', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $job = new GenerateFeedbackJob($student, 'inactivity_reminder');
    $job->handle(app(FeedbackGenerator::class));

    $this->assertDatabaseHas('ai_feedback', [
        'user_id' => $student->id,
        'context_type' => 'inactivity_reminder',
        'feedback_type' => 'warning',
    ]);
});

test('job has correct tags', function () {
    $student = User::factory()->create(['role' => 'student']);

    $job = new GenerateFeedbackJob($student, 'weekly_summary');

    expect($job->tags())->toContain('feedback')
        ->and($job->tags())->toContain('user:'.$student->id)
        ->and($job->tags())->toContain('context:weekly_summary');
});
