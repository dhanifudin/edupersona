<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed subjects first
        $this->call(SubjectSeeder::class);

        // Seed classes
        $this->call(ClassRoomSeeder::class);

        // Seed learning style questions
        $this->call(LearningStyleQuestionSeeder::class);

        // Create admin user
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@edupersona.test',
            'role' => 'admin',
        ]);

        // Create sample teacher
        User::factory()->create([
            'name' => 'Guru Matematika',
            'email' => 'guru@edupersona.test',
            'role' => 'teacher',
            'teacher_id_number' => '198501012010011001',
        ]);

        // Create sample student
        User::factory()->create([
            'name' => 'Siswa Demo',
            'email' => 'siswa@edupersona.test',
            'role' => 'student',
            'student_id_number' => '2024001',
        ]);
    }
}
