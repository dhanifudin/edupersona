<?php

use App\Models\ClassRoom;
use App\Models\ClassSubject;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\LearningStyleProfile;
use App\Models\Subject;
use App\Models\User;

test('teachers can view students index', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Index')
        ->has('students.data', 1)
        ->has('classes')
        ->has('filters')
    );
});

test('teachers only see students in their classes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);
    $otherClass = ClassRoom::factory()->create();

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    $otherStudent = User::factory()->create(['role' => 'student']);
    $otherStudent->classes()->attach($otherClass->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Index')
        ->has('students.data', 1)
    );
});

test('teachers can filter students by class', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class1 = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);
    $class2 = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student1 = User::factory()->create(['role' => 'student']);
    $student1->classes()->attach($class1->id, ['enrolled_at' => now()]);

    $student2 = User::factory()->create(['role' => 'student']);
    $student2->classes()->attach($class2->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students?class='.$class1->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Index')
        ->has('students.data', 1)
        ->where('filters.class', (string) $class1->id)
    );
});

test('teachers can filter students by learning style', function () {
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

    $response = $this->actingAs($teacher)->get('/students?style=visual');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Index')
        ->has('students.data', 1)
        ->where('filters.style', 'visual')
    );
});

test('teachers can search students by name', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student1 = User::factory()->create(['role' => 'student', 'name' => 'John Doe']);
    $student1->classes()->attach($class->id, ['enrolled_at' => now()]);

    $student2 = User::factory()->create(['role' => 'student', 'name' => 'Jane Smith']);
    $student2->classes()->attach($class->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students?search=John');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Index')
        ->has('students.data', 1)
    );
});

test('teachers can view student details', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students/'.$student->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Show')
        ->has('student')
        ->has('recentActivities')
        ->has('stats')
        ->has('recommendations')
        ->has('feedback')
    );
});

test('teachers can view student with learning profile', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    LearningStyleProfile::create([
        'user_id' => $student->id,
        'visual_score' => 80,
        'auditory_score' => 40,
        'kinesthetic_score' => 30,
        'dominant_style' => 'visual',
        'analyzed_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->get('/students/'.$student->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Show')
        ->has('student.learning_style_profile')
    );
});

test('teachers can view student activities', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $class = ClassRoom::factory()->create(['homeroom_teacher_id' => $teacher->id]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    $material = LearningMaterial::factory()->create();
    LearningActivity::create([
        'user_id' => $student->id,
        'material_id' => $material->id,
        'duration_seconds' => 300,
        'started_at' => now(),
    ]);

    $response = $this->actingAs($teacher)->get('/students/'.$student->id);

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('teacher/Students/Show')
        ->has('recentActivities', 1)
        ->where('stats.totalActivities', 1)
    );
});

test('teachers cannot view students outside their classes', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $otherClass = ClassRoom::factory()->create();

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($otherClass->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students/'.$student->id);

    $response->assertForbidden();
});

test('teachers teaching a class can view students', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $subject = Subject::factory()->create();
    $class = ClassRoom::factory()->create();

    ClassSubject::create([
        'class_id' => $class->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->id,
    ]);

    $student = User::factory()->create(['role' => 'student']);
    $student->classes()->attach($class->id, ['enrolled_at' => now()]);

    $response = $this->actingAs($teacher)->get('/students/'.$student->id);

    $response->assertOk();
});

test('guests cannot access teacher students page', function () {
    $response = $this->get('/students');

    $response->assertRedirect('/login');
});

test('students cannot access teacher students page', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get('/students');

    $response->assertForbidden();
});
