<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentSubjectEnrollment>
 */
class StudentSubjectEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject_id' => Subject::factory(),
            'enrollment_type' => fake()->randomElement(['assigned', 'elective']),
            'enrolled_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'status' => 'active',
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_type' => 'assigned',
        ]);
    }

    public function elective(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_type' => 'elective',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dropped',
        ]);
    }
}
