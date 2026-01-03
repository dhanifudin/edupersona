<?php

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;

test('admins can view subjects index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Subject::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/manage/subjects');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Index')
        ->has('subjects.data', 5)
        ->has('filters')
    );
});

test('subjects can be searched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK']);
    Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS']);

    $response = $this->actingAs($admin)->get('/manage/subjects?search=Mat');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Index')
        ->has('subjects.data', 1)
    );
});

test('subjects can be searched by code', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK']);
    Subject::factory()->create(['name' => 'Fisika', 'code' => 'FIS']);

    $response = $this->actingAs($admin)->get('/manage/subjects?search=MTK');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Index')
        ->has('subjects.data', 1)
    );
});

test('admins can view create subject form', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/manage/subjects/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Create')
    );
});

test('admins can create a subject', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/manage/subjects', [
        'name' => 'Matematika',
        'code' => 'MTK',
        'description' => 'Mata pelajaran matematika',
    ]);

    $response->assertRedirect('/manage/subjects');
    $this->assertDatabaseHas('subjects', [
        'name' => 'Matematika',
        'code' => 'MTK',
    ]);
});

test('subject code is stored uppercase', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/manage/subjects', [
        'name' => 'Matematika',
        'code' => 'mtk',
    ]);

    $response->assertRedirect('/manage/subjects');
    $this->assertDatabaseHas('subjects', [
        'code' => 'MTK',
    ]);
});

test('subject creation prevents duplicate code', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Subject::factory()->create(['code' => 'MTK']);

    $response = $this->actingAs($admin)->post('/manage/subjects', [
        'name' => 'Matematika 2',
        'code' => 'MTK',
    ]);

    $response->assertSessionHasErrors(['code']);
});

test('subject creation validates required fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/manage/subjects', []);

    $response->assertSessionHasErrors(['name', 'code']);
});

test('admins can view a subject', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->get("/manage/subjects/{$subject->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Show')
        ->has('subject')
        ->has('materials')
        ->has('classes')
        ->has('stats')
    );
});

test('admins can view edit subject form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->get("/manage/subjects/{$subject->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Subjects/Edit')
        ->has('subject')
    );
});

test('admins can update a subject', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create(['name' => 'Matematika']);

    $response = $this->actingAs($admin)->put("/manage/subjects/{$subject->id}", [
        'name' => 'Matematika Wajib',
        'code' => $subject->code,
    ]);

    $response->assertRedirect('/manage/subjects');
    $this->assertDatabaseHas('subjects', [
        'id' => $subject->id,
        'name' => 'Matematika Wajib',
    ]);
});

test('admins can delete a subject', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create();

    $response = $this->actingAs($admin)->delete("/manage/subjects/{$subject->id}");

    $response->assertRedirect('/manage/subjects');
    $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
});

test('admins cannot delete subject with materials', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create();
    LearningMaterial::factory()->create(['subject_id' => $subject->id]);

    $response = $this->actingAs($admin)->delete("/manage/subjects/{$subject->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
});

test('admins cannot delete subject assigned to classes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $subject = Subject::factory()->create();
    $class = ClassRoom::factory()->create();
    $teacher = User::factory()->create(['role' => 'teacher']);

    ClassSubject::create([
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $response = $this->actingAs($admin)->delete("/manage/subjects/{$subject->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
});

test('guests cannot access subject management', function () {
    $response = $this->get('/manage/subjects');

    $response->assertRedirect('/login');
});

test('students cannot access subject management', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/manage/subjects');

    $response->assertForbidden();
});

test('teachers cannot access subject management', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/manage/subjects');

    $response->assertForbidden();
});
