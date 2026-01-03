<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\LearningStyleProfile;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoStudentSeeder extends Seeder
{
    /**
     * Demo students dengan profil gaya belajar berbeda
     */
    public function run(): void
    {
        $subject = Subject::where('code', 'MTK')->first();
        $classRoom = ClassRoom::where('name', 'X IPA 1')->first();

        if (! $subject) {
            $this->command->warn('Subject MTK tidak ditemukan. Jalankan SubjectSeeder terlebih dahulu.');

            return;
        }

        $students = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@demo.test',
                'student_id_number' => 'DEMO001',
                'profile' => [
                    'visual_score' => 85.00,
                    'auditory_score' => 45.00,
                    'kinesthetic_score' => 35.00,
                    'dominant_style' => 'visual',
                ],
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@demo.test',
                'student_id_number' => 'DEMO002',
                'profile' => [
                    'visual_score' => 40.00,
                    'auditory_score' => 88.00,
                    'kinesthetic_score' => 42.00,
                    'dominant_style' => 'auditory',
                ],
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@demo.test',
                'student_id_number' => 'DEMO003',
                'profile' => [
                    'visual_score' => 35.00,
                    'auditory_score' => 40.00,
                    'kinesthetic_score' => 90.00,
                    'dominant_style' => 'kinesthetic',
                ],
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@demo.test',
                'student_id_number' => 'DEMO004',
                'profile' => [
                    'visual_score' => 72.00,
                    'auditory_score' => 70.00,
                    'kinesthetic_score' => 68.00,
                    'dominant_style' => 'mixed',
                ],
            ],
        ];

        foreach ($students as $studentData) {
            // Create user
            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => Hash::make('demo1234'),
                    'role' => 'student',
                    'student_id_number' => $studentData['student_id_number'],
                    'email_verified_at' => now(),
                ]
            );

            // Create learning style profile
            LearningStyleProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'visual_score' => $studentData['profile']['visual_score'],
                    'auditory_score' => $studentData['profile']['auditory_score'],
                    'kinesthetic_score' => $studentData['profile']['kinesthetic_score'],
                    'dominant_style' => $studentData['profile']['dominant_style'],
                    'analyzed_at' => now()->subDays(7),
                ]
            );

            // Enroll in Matematika subject
            StudentSubjectEnrollment::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'enrollment_type' => 'assigned',
                    'enrolled_at' => now()->subDays(14),
                    'status' => 'active',
                ]
            );

            // Assign to class if exists
            if ($classRoom) {
                $user->classes()->syncWithoutDetaching([
                    $classRoom->id => [
                        'enrolled_at' => now()->subDays(14),
                        'status' => 'active',
                    ],
                ]);
            }

            $this->command->info("Created demo student: {$studentData['name']} ({$studentData['profile']['dominant_style']})");
        }
    }
}
