<?php

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('teachers can view materials index', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    LearningMaterial::factory()->count(3)->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get('/teacher/materials');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Materials/Index')
        ->has('materials.data', 3)
        ->has('subjects')
    );
});

test('teachers only see their own materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);

    LearningMaterial::factory()->count(2)->create(['teacher_id' => $teacher->id]);
    LearningMaterial::factory()->count(3)->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->get('/teacher/materials');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Materials/Index')
        ->has('materials.data', 2)
    );
});

test('teachers can view create material form', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();
    $class = ClassRoom::factory()->create();

    ClassSubject::create([
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $response = $this->actingAs($teacher)->get('/teacher/materials/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Materials/Create')
        ->has('subjects')
        ->has('classes')
        ->has('materialTypes')
        ->has('learningStyles')
        ->has('difficultyLevels')
    );
});

test('teachers can create a material', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($teacher)->post('/teacher/materials', [
        'subject_id' => $subject->id,
        'title' => 'Test Material',
        'description' => 'Test description',
        'type' => 'video',
        'learning_style' => 'visual',
        'difficulty_level' => 'beginner',
        'is_active' => true,
    ]);

    $response->assertRedirect('/teacher/materials');
    $this->assertDatabaseHas('learning_materials', [
        'title' => 'Test Material',
        'teacher_id' => $teacher->id,
        'subject_id' => $subject->id,
    ]);
});

test('teachers can create a material with file upload', function () {
    Storage::fake('public');

    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($teacher)->post('/teacher/materials', [
        'subject_id' => $subject->id,
        'title' => 'Test Material with File',
        'type' => 'document',
        'learning_style' => 'visual',
        'difficulty_level' => 'beginner',
        'file' => UploadedFile::fake()->create('document.pdf', 1000),
        'is_active' => true,
    ]);

    $response->assertRedirect('/teacher/materials');
    $this->assertDatabaseHas('learning_materials', [
        'title' => 'Test Material with File',
    ]);

    $material = LearningMaterial::where('title', 'Test Material with File')->first();
    Storage::disk('public')->assertExists($material->file_path);
});

test('teachers can view their own material', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get('/teacher/materials/'.$material->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Materials/Show')
        ->has('material')
        ->has('activities')
        ->has('stats')
    );
});

test('teachers cannot view other teachers materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->get('/teacher/materials/'.$material->id);

    $response->assertForbidden();
});

test('teachers can view edit form for their own material', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get('/teacher/materials/'.$material->id.'/edit');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Materials/Edit')
        ->has('material')
        ->has('subjects')
        ->has('classes')
    );
});

test('teachers can update their own material', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();
    $material = LearningMaterial::factory()->create([
        'teacher_id' => $teacher->id,
        'title' => 'Original Title',
    ]);

    $response = $this->actingAs($teacher)->put('/teacher/materials/'.$material->id, [
        'subject_id' => $subject->id,
        'title' => 'Updated Title',
        'type' => 'video',
        'learning_style' => 'visual',
        'difficulty_level' => 'beginner',
        'is_active' => true,
    ]);

    $response->assertRedirect('/teacher/materials');
    $this->assertDatabaseHas('learning_materials', [
        'id' => $material->id,
        'title' => 'Updated Title',
    ]);
});

test('teachers cannot update other teachers materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->put('/teacher/materials/'.$material->id, [
        'subject_id' => $material->subject_id,
        'title' => 'Updated Title',
        'type' => 'video',
        'learning_style' => 'visual',
        'difficulty_level' => 'beginner',
    ]);

    $response->assertForbidden();
});

test('teachers can delete their own material', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->delete('/teacher/materials/'.$material->id);

    $response->assertRedirect('/teacher/materials');
    $this->assertDatabaseMissing('learning_materials', [
        'id' => $material->id,
    ]);
});

test('teachers cannot delete other teachers materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherTeacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create(['teacher_id' => $otherTeacher->id]);

    $response = $this->actingAs($teacher)->delete('/teacher/materials/'.$material->id);

    $response->assertForbidden();
});

test('teachers can toggle material active status', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $material = LearningMaterial::factory()->create([
        'teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($teacher)->patch('/teacher/materials/'.$material->id.'/toggle-active');

    $response->assertRedirect();
    $this->assertDatabaseHas('learning_materials', [
        'id' => $material->id,
        'is_active' => false,
    ]);
});

test('guests cannot access teacher materials', function () {
    $response = $this->get('/teacher/materials');

    $response->assertRedirect('/login');
});

test('students cannot access teacher materials', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/teacher/materials');

    $response->assertForbidden();
});

test('material creation requires valid data', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->post('/teacher/materials', [
        'title' => '', // Required field empty
        'type' => 'invalid_type', // Invalid type
    ]);

    $response->assertSessionHasErrors(['title', 'subject_id', 'type', 'learning_style', 'difficulty_level']);
});
