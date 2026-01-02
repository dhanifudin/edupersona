<?php

use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;

test('guests are redirected to login from progress page', function () {
    $response = $this->get('/student/progress');

    $response->assertRedirect('/login');
});

test('non-students cannot access progress page', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/student/progress');

    $response->assertForbidden();
});

test('students can access progress page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('stats')
        ->has('weeklyActivity')
        ->has('progressBySubject')
        ->has('recentCompleted')
        ->has('streak')
        ->has('activityByType')
    );
});

test('progress page shows correct stats for student with activities', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create();
    $material = LearningMaterial::factory()->create(['subject_id' => $subject->id]);

    // Create some learning activities
    LearningActivity::factory()->count(3)->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 1800, // 30 minutes each
        'started_at' => now(),
    ]);

    LearningActivity::factory()->completed()->count(2)->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 1800,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->where('stats.totalActivities', 5)
        ->where('stats.completedActivities', 2)
    );
});

test('progress page shows empty stats for new student', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->where('stats.totalActivities', 0)
        ->where('stats.completedActivities', 0)
        ->where('stats.totalMinutes', 0)
        ->where('stats.completionRate', 0)
    );
});

test('progress page shows weekly activity data', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('weeklyActivity', 4) // 4 weeks of data
    );
});

test('progress page shows progress by subject', function () {
    $student = User::factory()->create(['role' => 'student']);
    $subject = Subject::factory()->create(['name' => 'Matematika']);
    $material = LearningMaterial::factory()->create(['subject_id' => $subject->id]);

    LearningActivity::factory()->completed()->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 600,
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('progressBySubject', 1)
    );
});

test('progress page shows streak data', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('streak.current')
        ->has('streak.longest')
    );
});

test('progress page shows activity by type', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create(['type' => 'video']);

    LearningActivity::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('activityByType')
    );
});

test('progress page shows recently completed activities', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    LearningActivity::factory()->completed()->count(3)->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('recentCompleted', 3)
    );
});

test('progress page limits recent completed to 10', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    LearningActivity::factory()->completed()->count(15)->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->has('recentCompleted', 10)
    );
});

test('student cannot see other students progress data', function () {
    $student1 = User::factory()->create(['role' => 'student']);
    $student2 = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    // Create activities for student2
    LearningActivity::factory()->count(5)->create([
        'user_id' => $student2->id,
        'material_id' => $material->id,
    ]);

    // Student1 should only see their own data (empty)
    $response = $this->actingAs($student1)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->where('stats.totalActivities', 0)
    );
});

test('streak is calculated correctly for consecutive days', function () {
    $student = User::factory()->create(['role' => 'student']);
    $material = LearningMaterial::factory()->create();

    // Create activities for today and yesterday
    LearningActivity::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'started_at' => now(),
    ]);

    LearningActivity::factory()->create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'started_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($student)->get('/student/progress');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('student/Progress')
        ->where('streak.current', 2)
    );
});
