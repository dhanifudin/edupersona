<?php

use App\Models\AiRecommendation;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;
use App\Services\RecommendationEngine;

test('guests are redirected to login from recommendations page', function () {
    $response = $this->get('/recommendations');

    $response->assertRedirect('/login');
});

test('non-students cannot access recommendations page', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/recommendations');

    $response->assertForbidden();
});

test('students can access recommendations page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Recommendations')
        ->has('recommendations')
        ->has('viewedRecommendations')
        ->has('hasLearningProfile')
        ->has('subjects')
        ->has('filters')
    );
});

test('recommendations page shows hasLearningProfile false when no profile exists', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('hasLearningProfile', false)
    );
});

test('recommendations page shows hasLearningProfile true when profile exists', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('hasLearningProfile', true)
        ->has('learningProfile')
    );
});

test('students can see their unviewed recommendations', function () {
    $student = User::factory()->create(['role' => 'student']);
    AiRecommendation::factory()->count(3)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 3)
    );
});

test('viewed recommendations are excluded from main list', function () {
    $student = User::factory()->create(['role' => 'student']);
    AiRecommendation::factory()->count(2)->create(['user_id' => $student->id]);
    AiRecommendation::factory()->viewed()->count(3)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 2)
        ->has('viewedRecommendations', 3)
    );
});

test('students can filter recommendations by subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject1 = Subject::factory()->create();
    $subject2 = Subject::factory()->create();
    $material1 = LearningMaterial::factory()->create(['subject_id' => $subject1->id]);
    $material2 = LearningMaterial::factory()->create(['subject_id' => $subject2->id]);

    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material1->id,
    ]);
    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material2->id,
    ]);

    $response = $this->actingAs($student)->get('/recommendations?subject='.$subject1->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 1)
    );
});

test('students can filter recommendations by material type', function () {
    $student = User::factory()->create(['role' => 'student']);
    $materialVideo = LearningMaterial::factory()->create(['type' => 'video']);
    $materialDoc = LearningMaterial::factory()->create(['type' => 'document']);

    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $materialVideo->id,
    ]);
    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $materialDoc->id,
    ]);

    $response = $this->actingAs($student)->get('/recommendations?type=video');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 1)
    );
});

test('students can filter recommendations by learning style', function () {
    $student = User::factory()->create(['role' => 'student']);
    $materialVisual = LearningMaterial::factory()->create(['learning_style' => 'visual']);
    $materialAuditory = LearningMaterial::factory()->create(['learning_style' => 'auditory']);

    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $materialVisual->id,
    ]);
    AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'material_id' => $materialAuditory->id,
    ]);

    $response = $this->actingAs($student)->get('/recommendations?style=visual');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 1)
    );
});

test('students can mark recommendation as viewed', function () {
    $student = User::factory()->create(['role' => 'student']);
    $recommendation = AiRecommendation::factory()->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->post('/recommendations/'.$recommendation->id.'/view');

    $response->assertRedirect();
    $recommendation->refresh();
    expect($recommendation->is_viewed)->toBeTrue();
    expect($recommendation->viewed_at)->not->toBeNull();
});

test('students cannot mark other students recommendations as viewed', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);
    $recommendation = AiRecommendation::factory()->create(['user_id' => $student2->id]);

    $response = $this->actingAs($student1)->post('/recommendations/'.$recommendation->id.'/view');

    $response->assertForbidden();
});

test('students can refresh recommendations when they have a learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($student)->post('/recommendations/refresh');

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('students cannot refresh recommendations without learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post('/recommendations/refresh');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('students can generate recommendations when they have a learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    // Mock the RecommendationEngine to return empty collection
    $this->mock(RecommendationEngine::class, function ($mock) {
        $mock->shouldReceive('generateForStudent')->andReturn(collect());
    });

    $response = $this->actingAs($student)->post('/recommendations/generate');

    $response->assertRedirect();
});

test('students cannot generate recommendations without learning profile', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post('/recommendations/generate');

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('recommendations are ordered by relevance score descending', function () {
    $student = User::factory()->create(['role' => 'student']);

    $lowScore = AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'relevance_score' => 0.5,
    ]);
    $highScore = AiRecommendation::factory()->create([
        'user_id' => $student->id,
        'relevance_score' => 0.95,
    ]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 2)
        ->where('recommendations.data.0.id', $highScore->id)
        ->where('recommendations.data.1.id', $lowScore->id)
    );
});

test('recommendations are paginated', function () {
    $student = User::factory()->create(['role' => 'student']);
    AiRecommendation::factory()->count(15)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 12) // 12 per page
        ->where('recommendations.last_page', 2)
    );
});

test('student cannot see other students recommendations', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);

    AiRecommendation::factory()->count(5)->create(['user_id' => $student2->id]);

    $response = $this->actingAs($student1)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('recommendations.data', 0)
    );
});

test('viewed recommendations history is limited to 10', function () {
    $student = User::factory()->create(['role' => 'student']);
    AiRecommendation::factory()->viewed()->count(15)->create(['user_id' => $student->id]);

    $response = $this->actingAs($student)->get('/recommendations');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('viewedRecommendations', 10)
    );
});

test('filters are preserved in query string', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/recommendations?subject=1&type=video&style=visual');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('filters.subject', '1')
        ->where('filters.type', 'video')
        ->where('filters.style', 'visual')
    );
});
