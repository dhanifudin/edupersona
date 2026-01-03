<?php

use App\Models\ClassRoom;
use App\Models\User;

test('admins can view classes index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/classes');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Index')
        ->has('classes.data', 5)
        ->has('academicYears')
        ->has('filters')
    );
});

test('classes can be filtered by grade level', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(3)->create(['grade_level' => 'X']);
    ClassRoom::factory()->count(2)->create(['grade_level' => 'XI']);

    $response = $this->actingAs($admin)->get('/classes?grade=X');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Index')
        ->has('classes.data', 3)
    );
});

test('classes can be filtered by academic year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(3)->create(['academic_year' => '2024/2025']);
    ClassRoom::factory()->count(2)->create(['academic_year' => '2023/2024']);

    $response = $this->actingAs($admin)->get('/classes?year=2024/2025');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Index')
        ->has('classes.data', 3)
    );
});

test('classes can be filtered by active status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(3)->create(['is_active' => true]);
    ClassRoom::factory()->count(2)->create(['is_active' => false]);

    $response = $this->actingAs($admin)->get('/classes?active=1');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Index')
        ->has('classes.data', 3)
    );
});

test('classes can be searched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->create(['name' => 'X IPA 1', 'major' => 'IPA']);
    ClassRoom::factory()->create(['name' => 'XI IPS 1', 'major' => 'IPS']);

    $response = $this->actingAs($admin)->get('/classes?search=IPA');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Index')
        ->has('classes.data', 1)
    );
});

test('admins can view create class form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(3)->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->get('/classes/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Create')
        ->has('teachers', 3)
    );
});

test('admins can create a class', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->post('/classes', [
        'name' => 'X IPA 1',
        'grade_level' => 'X',
        'major' => 'IPA',
        'academic_year' => '2024/2025',
        'homeroom_teacher_id' => $teacher->id,
        'is_active' => true,
    ]);

    $response->assertRedirect('/classes');
    $this->assertDatabaseHas('classes', [
        'name' => 'X IPA 1',
        'grade_level' => 'X',
        'major' => 'IPA',
    ]);
});

test('class creation prevents duplicate name in same academic year', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->create([
        'name' => 'X IPA 1',
        'academic_year' => '2024/2025',
    ]);

    $response = $this->actingAs($admin)->post('/classes', [
        'name' => 'X IPA 1',
        'grade_level' => 'X',
        'academic_year' => '2024/2025',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('class creation validates required fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/classes', []);

    $response->assertSessionHasErrors(['name', 'grade_level', 'academic_year']);
});

test('admins can view a class', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create();

    $response = $this->actingAs($admin)->get("/classes/{$class->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Show')
        ->has('class')
        ->has('stats')
        ->has('learningStyleDistribution')
    );
});

test('admins can view edit class form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create();

    $response = $this->actingAs($admin)->get("/classes/{$class->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Classes/Edit')
        ->has('class')
        ->has('teachers')
    );
});

test('admins can update a class', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create(['name' => 'X IPA 1']);

    $response = $this->actingAs($admin)->put("/classes/{$class->id}", [
        'name' => 'X IPA 2',
        'grade_level' => $class->grade_level,
        'academic_year' => $class->academic_year,
    ]);

    $response->assertRedirect('/classes');
    $this->assertDatabaseHas('classes', [
        'id' => $class->id,
        'name' => 'X IPA 2',
    ]);
});

test('admins can toggle class active status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patch("/classes/{$class->id}/toggle-active");

    $response->assertRedirect();
    $class->refresh();
    $this->assertFalse($class->is_active);
});

test('admins can delete a class', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create();

    $response = $this->actingAs($admin)->delete("/classes/{$class->id}");

    $response->assertRedirect('/classes');
    $this->assertDatabaseMissing('classes', ['id' => $class->id]);
});

test('admins cannot delete class with active students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create();
    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now(), 'status' => 'active']);

    $response = $this->actingAs($admin)->delete("/classes/{$class->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('classes', ['id' => $class->id]);
});

test('guests cannot access class management', function () {
    $response = $this->get('/classes');

    $response->assertRedirect('/login');
});

test('students cannot access class management', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/classes');

    $response->assertForbidden();
});

test('teachers cannot access class management', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/classes');

    $response->assertForbidden();
});
