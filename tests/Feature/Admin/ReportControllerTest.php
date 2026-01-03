<?php

use App\Models\ClassRoom;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\User;

test('admins can view reports index page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/reports');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Reports/Index')
        ->has('reportTypes', 4)
    );
});

test('guests cannot access reports page', function () {
    $response = $this->get('/reports');

    $response->assertRedirect('/login');
});

test('students cannot access reports page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/reports');

    $response->assertForbidden();
});

test('teachers cannot access reports page', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/reports');

    $response->assertForbidden();
});

test('admins can generate learning styles pdf report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Create some learning style profiles
    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'learning_styles',
        'format' => 'pdf',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admins can generate learning styles csv report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $student = User::factory()->create(['role' => 'student']);
    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'learning_styles',
        'format' => 'csv',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
});

test('admins can generate student progress pdf report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();
    LearningActivity::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
    ]);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'student_progress',
        'format' => 'pdf',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admins can generate student progress csv report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'student_progress',
        'format' => 'csv',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
});

test('admins can generate material usage pdf report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $material = LearningMaterial::factory()->create();

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'material_usage',
        'format' => 'pdf',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admins can generate material usage csv report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'material_usage',
        'format' => 'csv',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
});

test('admins can generate class analytics pdf report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    ClassRoom::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'class_analytics',
        'format' => 'pdf',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('admins can generate class analytics csv report', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'class_analytics',
        'format' => 'csv',
    ]);

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=utf-8');
});

test('report generation validates type parameter', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'invalid_type',
        'format' => 'pdf',
    ]);

    $response->assertSessionHasErrors('type');
});

test('report generation validates format parameter', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'learning_styles',
        'format' => 'docx',
    ]);

    $response->assertSessionHasErrors('format');
});

test('report generation accepts date range filters', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'student_progress',
        'format' => 'csv',
        'date_from' => '2024-01-01',
        'date_to' => '2024-12-31',
    ]);

    $response->assertOk();
});

test('report generation validates date_to is after date_from', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/reports/generate', [
        'type' => 'student_progress',
        'format' => 'csv',
        'date_from' => '2024-12-31',
        'date_to' => '2024-01-01',
    ]);

    $response->assertSessionHasErrors('date_to');
});

test('guests cannot generate reports', function () {
    $response = $this->post('/reports/generate', [
        'type' => 'learning_styles',
        'format' => 'pdf',
    ]);

    $response->assertRedirect('/login');
});

test('students cannot generate reports', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post('/reports/generate', [
        'type' => 'learning_styles',
        'format' => 'pdf',
    ]);

    $response->assertForbidden();
});
