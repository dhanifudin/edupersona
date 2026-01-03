<?php

use App\Models\AiRecommendation;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;

test('students can view materials index page', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningMaterial::factory()->count(3)->create();

    $response = $this->actingAs($student)->get('/materials');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->has('materials')
        ->has('subjects')
        ->has('filters')
    );
});

test('students can filter materials by subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MAT']);
    $material = LearningMaterial::factory()->create(['subject_id' => $subject->id]);
    LearningMaterial::factory()->create(); // Different subject

    $response = $this->actingAs($student)->get('/materials?subject='.$subject->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->where('filters.subject', (string) $subject->id)
    );
});

test('students can filter materials by type', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningMaterial::factory()->video()->create();
    LearningMaterial::factory()->document()->create();

    $response = $this->actingAs($student)->get('/materials?type=video');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->where('filters.type', 'video')
    );
});

test('students can filter materials by learning style', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningMaterial::factory()->visual()->create();
    LearningMaterial::factory()->auditory()->create();

    $response = $this->actingAs($student)->get('/materials?style=visual');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->where('filters.style', 'visual')
    );
});

test('students with learning profile see materials matching their dominant style by default', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    LearningMaterial::factory()->visual()->create();
    LearningMaterial::factory()->auditory()->create();

    $response = $this->actingAs($student)->get('/materials');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->has('learningProfile')
    );
});

test('students can view a single material', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    $response = $this->actingAs($student)->get('/materials/'.$material->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Show')
        ->has('material')
        ->has('activity')
        ->has('relatedMaterials')
    );
});

test('viewing a material creates a learning activity', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    $this->actingAs($student)->get('/materials/'.$material->id);

    $this->assertDatabaseHas('learning_activities', [
        'user_id' => $student->id,
        'material_id' => $material->id,
    ]);
});

test('viewing a material marks recommendation as viewed', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    AiRecommendation::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'reason' => 'Test recommendation',
        'relevance_score' => 0.8,
        'is_viewed' => false,
    ]);

    $this->actingAs($student)->get('/materials/'.$material->id);

    $this->assertDatabaseHas('ai_recommendations', [
        'user_id' => $student->id,
        'material_id' => $material->id,
        'is_viewed' => true,
    ]);
});

test('students can update learning activity duration', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();
    $activity = LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 0,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($student)
        ->patchJson('/activities/'.$activity->id, [
            'duration_seconds' => 120,
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('learning_activities', [
        'id' => $activity->id,
        'duration_seconds' => 120,
    ]);
});

test('students can mark learning activity as completed', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();
    $activity = LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 0,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($student)
        ->patchJson('/activities/'.$activity->id, [
            'duration_seconds' => 300,
            'completed' => true,
        ]);

    $response->assertOk();
    $activity->refresh();
    expect($activity->completed_at)->not->toBeNull();
});

test('guests cannot access materials', function () {
    $response = $this->get('/materials');

    $response->assertRedirect('/login');
});

test('non-students cannot access student materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/materials');

    $response->assertForbidden();
});

test('related materials are shown on material page', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MAT']);
    $material = LearningMaterial::factory()->create(['subject_id' => $subject->id]);
    LearningMaterial::factory()->count(3)->create(['subject_id' => $subject->id]);

    $response = $this->actingAs($student)->get('/materials/'.$material->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Show')
        ->has('relatedMaterials', 3)
    );
});

test('only active materials are shown', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningMaterial::factory()->create(['is_active' => true]);
    LearningMaterial::factory()->inactive()->create();

    $response = $this->actingAs($student)->get('/materials');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Materials/Index')
        ->has('materials.data', 1)
    );
});
