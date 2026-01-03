<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\LearningActivity;
use App\Models\LearningMaterial;
use App\Models\StudentProgress;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoActivitySeeder extends Seeder
{
    /**
     * Demo learning activities dan progress untuk siswa demo
     */
    public function run(): void
    {
        $subject = Subject::where('code', 'MTK')->first();
        $classRoom = ClassRoom::where('name', 'X IPA 1')->first();

        if (! $subject) {
            $this->command->warn('Subject MTK tidak ditemukan.');

            return;
        }

        // Get demo students
        $budi = User::where('email', 'budi@demo.test')->first();
        $siti = User::where('email', 'siti@demo.test')->first();
        $andi = User::where('email', 'andi@demo.test')->first();

        if (! $budi || ! $siti || ! $andi) {
            $this->command->warn('Demo students tidak ditemukan. Jalankan DemoStudentSeeder terlebih dahulu.');

            return;
        }

        // === BUDI (Visual) Activities ===
        $this->createActivitiesForStudent($budi, $subject, $classRoom, [
            [
                'material_title' => 'Video: Konsep Bilangan Bulat',
                'duration_minutes' => 25,
                'days_ago' => 3,
                'completed' => true,
            ],
            [
                'material_title' => 'Infografis: Operasi Bilangan Bulat',
                'duration_minutes' => 15,
                'days_ago' => 2,
                'completed' => true,
            ],
            [
                'material_title' => 'Video: Pengenalan Pecahan',
                'duration_minutes' => 30,
                'days_ago' => 0,
                'completed' => false,
            ],
        ]);

        // Budi Progress
        $this->createProgress($budi, $subject, $classRoom, [
            ['topic' => 'Bilangan Bulat', 'score' => 85, 'status' => 'completed', 'attempts' => 2],
            ['topic' => 'Pecahan', 'score' => 60, 'status' => 'in_progress', 'attempts' => 1],
        ]);

        // === SITI (Auditory) Activities ===
        $this->createActivitiesForStudent($siti, $subject, $classRoom, [
            [
                'material_title' => 'Podcast: Memahami Bilangan Bulat',
                'duration_minutes' => 20,
                'days_ago' => 4,
                'completed' => true,
            ],
            [
                'material_title' => 'Audio: Lagu Rumus Pecahan',
                'duration_minutes' => 10,
                'days_ago' => 3,
                'completed' => true,
            ],
            [
                'material_title' => 'Video: Penjelasan Verbal Aljabar',
                'duration_minutes' => 35,
                'days_ago' => 2,
                'completed' => true,
            ],
            [
                'material_title' => 'Podcast: Cerita Geometri',
                'duration_minutes' => 18,
                'days_ago' => 0,
                'completed' => false,
            ],
        ]);

        // Siti Progress
        $this->createProgress($siti, $subject, $classRoom, [
            ['topic' => 'Bilangan Bulat', 'score' => 90, 'status' => 'completed', 'attempts' => 1],
            ['topic' => 'Pecahan', 'score' => 78, 'status' => 'completed', 'attempts' => 2],
            ['topic' => 'Aljabar Dasar', 'score' => 45, 'status' => 'in_progress', 'attempts' => 1],
        ]);

        // === ANDI (Kinesthetic) Activities ===
        $this->createActivitiesForStudent($andi, $subject, $classRoom, [
            [
                'material_title' => 'Simulasi: Garis Bilangan Interaktif',
                'duration_minutes' => 40,
                'days_ago' => 5,
                'completed' => true,
            ],
            [
                'material_title' => 'Simulasi: Kalkulator Pecahan',
                'duration_minutes' => 25,
                'days_ago' => 3,
                'completed' => true,
            ],
            [
                'material_title' => 'Simulasi: Puzzle Aljabar',
                'duration_minutes' => 45,
                'days_ago' => 0,
                'completed' => false,
            ],
        ]);

        // Andi Progress
        $this->createProgress($andi, $subject, $classRoom, [
            ['topic' => 'Bilangan Bulat', 'score' => 75, 'status' => 'completed', 'attempts' => 3],
            ['topic' => 'Pecahan', 'score' => 70, 'status' => 'completed', 'attempts' => 2],
            ['topic' => 'Aljabar Dasar', 'score' => 50, 'status' => 'in_progress', 'attempts' => 1],
        ]);

        $this->command->info('Created demo learning activities and progress.');
    }

    private function createActivitiesForStudent(User $student, Subject $subject, ?ClassRoom $classRoom, array $activities): void
    {
        foreach ($activities as $activityData) {
            $material = LearningMaterial::where('title', $activityData['material_title'])
                ->where('subject_id', $subject->id)
                ->first();

            if (! $material) {
                $this->command->warn("Material not found: {$activityData['material_title']}");

                continue;
            }

            $startedAt = now()->subDays($activityData['days_ago'])->subMinutes($activityData['duration_minutes']);

            LearningActivity::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'material_id' => $material->id,
                ],
                [
                    'duration_seconds' => $activityData['duration_minutes'] * 60,
                    'started_at' => $startedAt,
                    'completed_at' => $activityData['completed'] ? $startedAt->addMinutes($activityData['duration_minutes']) : null,
                ]
            );
        }
    }

    private function createProgress(User $student, Subject $subject, ?ClassRoom $classRoom, array $progressData): void
    {
        foreach ($progressData as $progress) {
            StudentProgress::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'subject_id' => $subject->id,
                    'topic' => $progress['topic'],
                ],
                [
                    'class_id' => $classRoom?->id,
                    'score' => $progress['score'],
                    'attempts' => $progress['attempts'],
                    'status' => $progress['status'],
                    'last_activity_at' => now()->subDays(rand(0, 3)),
                ]
            );
        }
    }
}
