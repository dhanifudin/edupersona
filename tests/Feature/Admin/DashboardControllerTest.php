<?php

use App\Models\ClassRoom;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;

test('admins can view dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->has('userStats')
        ->has('classStats')
        ->has('learningStats')
        ->has('learningStyleDistribution')
        ->has('questionnaireStats')
        ->has('activityTrend')
        ->has('topMaterials')
        ->has('recentUsers')
        ->has('feedbackStats')
    );
});

test('dashboard shows correct user stats', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(5)->create(['role' => 'student']);
    User::factory()->count(3)->create(['role' => 'teacher']);

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->where('userStats.totalStudents', 5)
        ->where('userStats.totalTeachers', 3)
        ->where('userStats.totalAdmins', 1)
    );
});

test('dashboard shows correct class and subject stats', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ClassRoom::factory()->count(4)->create(['is_active' => true]);
    ClassRoom::factory()->count(2)->create(['is_active' => false]);
    Subject::factory()->count(5)->create();

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->where('classStats.totalClasses', 6)
        ->where('classStats.activeClasses', 4)
        ->where('classStats.totalSubjects', 5)
    );
});

test('dashboard shows learning style distribution', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);

    LearningStyleProfile::create([
        'user_id' => $student1->id,
        'visual_score' => 80,
        'auditory_score' => 40,
        'kinesthetic_score' => 30,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    LearningStyleProfile::create([
        'user_id' => $student2->id,
        'visual_score' => 30,
        'auditory_score' => 80,
        'kinesthetic_score' => 40,
        'dominant_style' => 'auditory',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->where('learningStyleDistribution.visual', 1)
        ->where('learningStyleDistribution.auditory', 1)
    );
});

test('dashboard shows recent users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(15)->create();

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->has('recentUsers', 10)
    );
});

test('dashboard shows top materials', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    LearningMaterial::factory()->count(10)->create();

    $response = $this->actingAs($admin)->get('/dashboard/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->has('topMaterials', 5)
    );
});

test('guests cannot access admin dashboard', function () {
    $response = $this->get('/dashboard/admin');

    $response->assertRedirect('/login');
});

test('students cannot access admin dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/dashboard/admin');

    $response->assertForbidden();
});

test('teachers cannot access admin dashboard', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/dashboard/admin');

    $response->assertForbidden();
});
