<?php

use App\Models\AiRecommendation;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\GeminiAiService;
use App\Services\RecommendationEngine;
use Illuminate\Support\Facades\Cache;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->geminiService = Mockery::mock(GeminiAiService::class);
    $this->engine = new RecommendationEngine($this->geminiService);
});

test('generateForStudent returns empty collection when no learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);

    $result = $this->engine->generateForStudent($student);

    expect($result)->toBeEmpty();
});

test('generateForStudent returns empty collection when no materials available', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $result = $this->engine->generateForStudent($student);

    expect($result)->toBeEmpty();
});

test('generateForStudent uses AI recommendations when available', function () {
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

    $this->geminiService->shouldReceive('generateRecommendations')
        ->once()
        ->andReturn([
            'recommendations' => [
                [
                    'material_id' => $material->id,
                    'relevance_score' => 0.95,
                    'reason' => 'Great for visual learners',
                ],
            ],
        ]);

    $result = $this->engine->generateForStudent($student);

    expect($result)->toHaveCount(1);
    $this->assertDatabaseHas('ai_recommendations', [
        'user_id' => $student->id,
        'material_id' => $material->id,
    ]);
});

test('generateForStudent uses rule-based fallback when AI fails', function () {
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

    $this->geminiService->shouldReceive('generateRecommendations')
        ->once()
        ->andReturn(null);

    $result = $this->engine->generateForStudent($student);

    expect($result)->toHaveCount(1);
});

test('generateForStudent filters by subject when provided', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $subject1 = Subject::factory()->create();
    $subject2 = Subject::factory()->create();
    LearningMaterial::factory()->create([
        'subject_id' => $subject1->id,
        'is_active' => true,
    ]);
    LearningMaterial::factory()->create([
        'subject_id' => $subject2->id,
        'is_active' => true,
    ]);

    $this->geminiService->shouldReceive('generateRecommendations')
        ->once()
        ->andReturn(null);

    $result = $this->engine->generateForStudent($student, $subject1->id);

    expect($result)->toHaveCount(1);
});

test('refreshForStudent marks old recommendations as viewed', function () {
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

    $oldRecommendation = AiRecommendation::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'reason' => 'Old recommendation',
        'relevance_score' => 0.8,
        'is_viewed' => false,
    ]);

    $this->geminiService->shouldReceive('generateRecommendations')
        ->once()
        ->andReturn(null);

    $this->engine->refreshForStudent($student);

    $oldRecommendation->refresh();
    expect($oldRecommendation->is_viewed)->toBeTrue();
});

test('getCachedRecommendations returns cached recommendations', function () {
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

    AiRecommendation::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'reason' => 'Test recommendation',
        'relevance_score' => 0.9,
        'is_viewed' => false,
    ]);

    // First call - should hit database
    $result1 = $this->engine->getCachedRecommendations($student);

    // Second call - should use cache
    $result2 = $this->engine->getCachedRecommendations($student);

    expect($result1)->toHaveCount(1);
    expect($result2)->toHaveCount(1);
});

test('clearUserCache removes user cache', function () {
    $student = User::factory()->create(['role' => 'student']);

    Cache::put("recommendations:{$student->id}", collect(['test']), 3600);

    expect(Cache::has("recommendations:{$student->id}"))->toBeTrue();

    $this->engine->clearUserCache($student);

    expect(Cache::has("recommendations:{$student->id}"))->toBeFalse();
});
