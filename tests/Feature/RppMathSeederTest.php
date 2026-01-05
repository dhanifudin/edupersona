<?php

use App\Models\LearningMaterial;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\RppMathSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create the required subject
    Subject::factory()->create([
        'code' => 'MTK',
        'name' => 'Matematika',
    ]);

    // Create the required teacher
    User::factory()->create([
        'email' => 'guru@edupersona.test',
        'role' => 'teacher',
    ]);
});

it('creates learning materials from RPP', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    // Should have created materials
    expect(LearningMaterial::count())->toBeGreaterThan(0);
});

it('creates materials for all three BABs', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    // BAB 1: Komposisi Fungsi dan Fungsi Invers
    $bab1Topics = ['Konsep Dasar Fungsi', 'Komposisi Fungsi', 'Fungsi Invers', 'Aplikasi Komposisi dan Invers'];
    foreach ($bab1Topics as $topic) {
        expect(LearningMaterial::where('topic', $topic)->exists())->toBeTrue(
            "Topic '{$topic}' tidak ditemukan"
        );
    }

    // BAB 2: Lingkaran
    $bab2Topics = ['Persamaan Lingkaran', 'Garis Singgung Lingkaran', 'Tali Busur Lingkaran', 'Aplikasi Lingkaran'];
    foreach ($bab2Topics as $topic) {
        expect(LearningMaterial::where('topic', $topic)->exists())->toBeTrue(
            "Topic '{$topic}' tidak ditemukan"
        );
    }

    // BAB 3: Statistika
    $bab3Topics = ['Diagram Pencar', 'Regresi Linear', 'Analisis Korelasi'];
    foreach ($bab3Topics as $topic) {
        expect(LearningMaterial::where('topic', $topic)->exists())->toBeTrue(
            "Topic '{$topic}' tidak ditemukan"
        );
    }
});

it('creates materials for all learning styles', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    $styles = ['visual', 'auditory', 'kinesthetic', 'all'];

    foreach ($styles as $style) {
        expect(LearningMaterial::where('learning_style', $style)->exists())->toBeTrue(
            "Learning style '{$style}' tidak ditemukan"
        );
    }
});

it('creates materials with all difficulty levels', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    $levels = ['beginner', 'intermediate', 'advanced'];

    foreach ($levels as $level) {
        expect(LearningMaterial::where('difficulty_level', $level)->exists())->toBeTrue(
            "Difficulty level '{$level}' tidak ditemukan"
        );
    }
});

it('creates materials with various types', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    $types = ['video', 'audio', 'document', 'infographic', 'simulation'];

    foreach ($types as $type) {
        expect(LearningMaterial::where('type', $type)->exists())->toBeTrue(
            "Material type '{$type}' tidak ditemukan"
        );
    }
});

it('assigns materials to the correct subject and teacher', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    $subject = Subject::where('code', 'MTK')->first();
    $teacher = User::where('email', 'guru@edupersona.test')->first();

    $materials = LearningMaterial::all();

    foreach ($materials as $material) {
        expect($material->subject_id)->toBe($subject->id);
        expect($material->teacher_id)->toBe($teacher->id);
    }
});

it('does not create duplicates when run twice', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);
    $firstCount = LearningMaterial::count();

    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);
    $secondCount = LearningMaterial::count();

    expect($firstCount)->toBe($secondCount);
});

it('skips seeding when subject MTK is not found', function () {
    // Delete the subject
    Subject::where('code', 'MTK')->delete();

    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    expect(LearningMaterial::count())->toBe(0);
});

it('skips seeding when teacher is not found', function () {
    // Delete the teacher
    User::where('email', 'guru@edupersona.test')->delete();

    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    expect(LearningMaterial::count())->toBe(0);
});

it('creates expected number of materials', function () {
    $this->artisan('db:seed', ['--class' => RppMathSeeder::class]);

    // Based on seeder:
    // BAB 1: 22 materials (Konsep Dasar: 6, Komposisi: 6, Invers: 6, Aplikasi: 4)
    // BAB 2: 22 materials (Persamaan: 6, Garis Singgung: 6, Tali Busur: 6, Aplikasi: 4)
    // BAB 3: 19 materials (Diagram Pencar: 6, Regresi: 6, Korelasi: 7)
    expect(LearningMaterial::count())->toBe(63);
});
