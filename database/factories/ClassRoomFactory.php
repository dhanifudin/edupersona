<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gradeLevel = fake()->randomElement(['X', 'XI', 'XII']);
        $major = fake()->randomElement(['IPA', 'IPS', 'Bahasa']);
        $classNumber = fake()->unique()->numberBetween(1, 100);

        return [
            'name' => $gradeLevel.' '.$major.' '.$classNumber,
            'grade_level' => $gradeLevel,
            'major' => $major,
            'academic_year' => '2024/2025',
            'is_active' => true,
        ];
    }
}
