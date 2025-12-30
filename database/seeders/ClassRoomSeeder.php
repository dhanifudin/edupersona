<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use Illuminate\Database\Seeder;

class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = '2024/2025';

        $classes = [
            // Kelas X IPA
            ['name' => 'X IPA 1', 'grade_level' => 'X', 'major' => 'IPA'],
            ['name' => 'X IPA 2', 'grade_level' => 'X', 'major' => 'IPA'],
            // Kelas X IPS
            ['name' => 'X IPS 1', 'grade_level' => 'X', 'major' => 'IPS'],
            // Kelas XI IPA
            ['name' => 'XI IPA 1', 'grade_level' => 'XI', 'major' => 'IPA'],
            ['name' => 'XI IPA 2', 'grade_level' => 'XI', 'major' => 'IPA'],
            // Kelas XI IPS
            ['name' => 'XI IPS 1', 'grade_level' => 'XI', 'major' => 'IPS'],
            // Kelas XII IPA
            ['name' => 'XII IPA 1', 'grade_level' => 'XII', 'major' => 'IPA'],
            ['name' => 'XII IPA 2', 'grade_level' => 'XII', 'major' => 'IPA'],
            // Kelas XII IPS
            ['name' => 'XII IPS 1', 'grade_level' => 'XII', 'major' => 'IPS'],
        ];

        foreach ($classes as $class) {
            ClassRoom::firstOrCreate(
                [
                    'name' => $class['name'],
                    'academic_year' => $academicYear,
                ],
                [
                    'grade_level' => $class['grade_level'],
                    'major' => $class['major'],
                    'academic_year' => $academicYear,
                    'is_active' => true,
                ]
            );
        }
    }
}
