<?php

use App\Models\ClassRoom;
use App\Models\User;

test('admins can view users index', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Index')
        ->has('users.data', 6)
        ->has('filters')
    );
});

test('users can be filtered by role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(3)->create(['role' => 'student']);
    User::factory()->count(2)->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->get('/users?role=student');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Index')
        ->has('users.data', 3)
    );
});

test('users can be searched', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin)->get('/users?search=John');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Index')
        ->has('users.data', 1)
    );
});

test('admins can view create user form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(2)->create(['is_active' => true]);

    $response = $this->actingAs($admin)->get('/users/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Create')
        ->has('classes', 2)
    );
});

test('admins can create a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'role' => 'student',
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'student',
    ]);
});

test('admins can create a student with class', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $class = ClassRoom::factory()->create(['is_active' => true]);

    $response = $this->actingAs($admin)->post('/users', [
        'name' => 'Student Name',
        'email' => 'student@example.com',
        'password' => 'password123',
        'role' => 'student',
        'student_id_number' => 'STU001',
        'class_id' => $class->id,
    ]);

    $response->assertRedirect('/users');
    $student = User::where('email', 'student@example.com')->first();
    $this->assertTrue($student->classes->contains($class));
});

test('user creation validates required fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post('/users', []);

    $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
});

test('admins can view a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($admin)->get("/users/{$student->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Show')
        ->has('user')
        ->has('additionalData')
    );
});

test('admins can view edit user form', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->get("/users/{$user->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Users/Edit')
        ->has('user')
        ->has('classes')
    );
});

test('admins can update a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/users/{$user->id}", [
        'name' => 'New Name',
        'email' => $user->email,
        'role' => $user->role,
    ]);

    $response->assertRedirect('/users');
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
    ]);
});

test('admins can update user password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();
    $oldPassword = $user->password;

    $response = $this->actingAs($admin)->put("/users/{$user->id}", [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'password' => 'newpassword123',
    ]);

    $response->assertRedirect('/users');
    $user->refresh();
    $this->assertNotEquals($oldPassword, $user->password);
});

test('admins can delete a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/users/{$user->id}");

    $response->assertRedirect('/users');
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admins cannot delete themselves', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->delete("/users/{$admin->id}");

    $response->assertRedirect();
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('guests cannot access user management', function () {
    $response = $this->get('/users');

    $response->assertRedirect('/login');
});

test('students cannot access user management', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/users');

    $response->assertForbidden();
});

test('teachers cannot access user management', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/users');

    $response->assertForbidden();
});
