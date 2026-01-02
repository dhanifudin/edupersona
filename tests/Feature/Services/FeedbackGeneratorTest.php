<?php

use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\FeedbackGenerator;
use App\Services\GeminiAiService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->geminiService = Mockery::mock(GeminiAiService::class);
    $this->generator = new FeedbackGenerator($this->geminiService);
});

test('generateForStudent returns null when no learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);

    $result = $this->generator->generateForStudent($student);

    expect($result)->toBeNull();
});

test('generateForStudent creates feedback using AI when available', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $this->geminiService->shouldReceive('generateFeedback')
        ->once()
        ->andReturn('Great progress! Keep learning.');

    $result = $this->generator->generateForStudent($student);

    expect($result)->not->toBeNull();
    expect($result->feedback_text)->toBe('Great progress! Keep learning.');
    expect($result->user_id)->toBe($student->id);
});

test('generateForStudent uses fallback when AI fails', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $this->geminiService->shouldReceive('generateFeedback')
        ->once()
        ->andReturn(null);

    $result = $this->generator->generateForStudent($student);

    expect($result)->not->toBeNull();
    expect($result->feedback_text)->toContain('visual');
});

test('generateForActivity creates feedback for completed activity', function () {
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

    $this->geminiService->shouldReceive('generateFeedback')
        ->once()
        ->andReturn('Well done completing this material!');

    $result = $this->generator->generateForActivity($student, $activity->id);

    expect($result)->not->toBeNull();
    expect($result->context_type)->toBe('activity_completion');
    expect($result->context_id)->toBe($activity->id);
});

test('generateForActivity returns null for non-existent activity', function () {
    $student = User::factory()->create(['role' => 'student']);

    $result = $this->generator->generateForActivity($student, 99999);

    expect($result)->toBeNull();
});

test('generateWeeklySummary creates feedback for active student', function () {
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

    $this->geminiService->shouldReceive('generateFeedback')
        ->once()
        ->andReturn('Great week of learning!');

    $result = $this->generator->generateWeeklySummary($student);

    expect($result)->not->toBeNull();
    expect($result->context_type)->toBe('weekly_summary');
});

test('generateWeeklySummary generates inactivity reminder for inactive student', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $result = $this->generator->generateWeeklySummary($student);

    expect($result)->not->toBeNull();
    expect($result->context_type)->toBe('inactivity_reminder');
    expect($result->feedback_type)->toBe('warning');
});

test('generateInactivityReminder creates warning feedback', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'John']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $result = $this->generator->generateInactivityReminder($student);

    expect($result)->not->toBeNull();
    expect($result->feedback_type)->toBe('warning');
    expect($result->context_type)->toBe('inactivity_reminder');
    expect($result->feedback_text)->toContain('John');
    expect($result->feedback_text)->toContain('video');
});

test('generateInactivityReminder uses appropriate message for auditory learner', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'Jane']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 40,
        'auditory_score' => 80,
        'kinesthetic_score' => 60,
        'dominant_style' => 'auditory',
        'analyzed_at' => now(),
    ]);

    $result = $this->generator->generateInactivityReminder($student);

    expect($result->feedback_text)->toContain('podcast');
    expect($result->feedback_text)->toContain('audio');
});

test('generateInactivityReminder uses appropriate message for kinesthetic learner', function () {
    $student = User::factory()->create(['role' => 'student', 'name' => 'Bob']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 40,
        'auditory_score' => 60,
        'kinesthetic_score' => 80,
        'dominant_style' => 'kinesthetic',
        'analyzed_at' => now(),
    ]);

    $result = $this->generator->generateInactivityReminder($student);

    expect($result->feedback_text)->toContain('Simulasi');
    expect($result->feedback_text)->toContain('interaktif');
});
