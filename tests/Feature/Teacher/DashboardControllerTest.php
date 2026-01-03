<?php

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;

test('teachers can view dashboard', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)->get('/dashboard/teacher');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Dashboard')
        ->has('stats')
        ->has('teachingClasses')
        ->has('homeroomClasses')
        ->has('recentMaterials')
        ->has('recentActivities')
        ->has('learningStyleDistribution')
    );
});

test('dashboard shows correct stats', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    ClassSubject::create([
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    LearningMaterial::factory()->count(3)->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get('/dashboard/teacher');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Dashboard')
        ->where('stats.totalStudents', 1)
        ->where('stats.totalMaterials', 3)
    );
});

test('dashboard shows recent materials', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    LearningMaterial::factory()->count(5)->create(['teacher_id' => $teacher->id]);

    $response = $this->actingAs($teacher)->get('/dashboard/teacher');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Dashboard')
        ->has('recentMaterials', 5)
    );
});

test('dashboard shows learning style distribution for homeroom students', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $visualStudent = User::factory()->create(['role' => 'student']);
    $visualStudent->classes()->attach($class->id, ['enrolled_at' => now()]);
    LearningStyleProfile::create([
        'user_id' => $visualStudent->id,
        'visual_score' => 80,
        'auditory_score' => 40,
        'kinesthetic_score' => 30,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $auditoryStudent = User::factory()->create(['role' => 'student']);
    $auditoryStudent->classes()->attach($class->id, ['enrolled_at' => now()]);
    LearningStyleProfile::create([
        'user_id' => $auditoryStudent->id,
        'visual_score' => 40,
        'auditory_score' => 80,
        'kinesthetic_score' => 30,
        'dominant_style' => 'auditory',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->get('/dashboard/teacher');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Dashboard')
        ->where('learningStyleDistribution.visual', 1)
        ->where('learningStyleDistribution.auditory', 1)
    );
});

test('guests cannot access teacher dashboard', function () {
    $response = $this->get('/dashboard/teacher');

    $response->assertRedirect('/login');
});

test('students cannot access teacher dashboard', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/dashboard/teacher');

    $response->assertForbidden();
});
