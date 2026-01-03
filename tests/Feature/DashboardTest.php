<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected to role-specific dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('dashboard.student'));
});

test('teachers are redirected to teacher dashboard', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $this->actingAs($teacher);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('dashboard.teacher'));
});

test('admins are redirected to admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('dashboard.admin'));
});
