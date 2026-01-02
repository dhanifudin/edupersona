<?php

use App\Jobs\RefreshRecommendationsJob;
use App\Models\AiRecommendation;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\GeminiAiService;
use App\Services\RecommendationEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('job can be dispatched', function () {
    Queue::fake();

    RefreshRecommendationsJob::dispatch();

    Queue::assertPushed(RefreshRecommendationsJob::class);
});

test('job refreshes recommendations for active students', function () {
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
        'is_active' => true,
        'type' => 'video',
        'learning_style' => 'visual',
    ]);

    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subDays(2),
    ]);

    // Create old recommendation
    AiRecommendation::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'reason' => 'Old recommendation',
        'relevance_score' => 0.8,
        'is_viewed' => false,
    ]);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateRecommendations')->andReturn(null);
    });

    $job = new RefreshRecommendationsJob;
    $job->handle(app(RecommendationEngine::class));

    // Old recommendation should be marked as viewed
    $this->assertDatabaseHas('ai_recommendations', [
        'user_id' => $student->id,
        'is_viewed' => true,
    ]);
});

test('job clears cache for active students', function () {
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
        'is_active' => true,
    ]);

    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subDays(2),
    ]);

    Cache::put("recommendations:{$student->id}", collect(['test']), 3600);

    $this->mock(GeminiAiService::class, function ($mock) {
        $mock->shouldReceive('generateRecommendations')->andReturn(null);
    });

    $job = new RefreshRecommendationsJob;
    $job->handle(app(RecommendationEngine::class));

    expect(Cache::has("recommendations:{$student->id}"))->toBeFalse();
});

test('job skips students without recent activity', function () {
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
        'is_active' => true,
    ]);

    // Activity older than 7 days
    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subDays(10),
    ]);

    $job = new RefreshRecommendationsJob;
    $job->handle(app(RecommendationEngine::class));

    // No new recommendations should be created
    expect(AiRecommendation::where('user_id', $student->id)->count())->toBe(0);
});

test('job skips students without learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    // No learning profile

    $subject = Subject::factory()->create();
    $material = LearningMaterial::factory()->create([
        'subject_id' => $subject->id,
        'is_active' => true,
    ]);

    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
        'started_at' => now()->subDays(2),
    ]);

    $job = new RefreshRecommendationsJob;
    $job->handle(app(RecommendationEngine::class));

    $this->assertDatabaseMissing('ai_recommendations', [
        'user_id' => $student->id,
    ]);
});

test('job has correct tags', function () {
    $job = new RefreshRecommendationsJob;

    expect($job->tags())->toContain('recommendations')
        ->and($job->tags())->toContain('daily')
        ->and($job->tags())->toContain('batch');
});
