<?php

namespace Database\Factories;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory()->state(['role' => 'teacher']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'topic' => fake()->word(),
            'type' => fake()->randomElement(['video', 'document', 'infographic', 'audio', 'simulation']),
            'learning_style' => fake()->randomElement(['visual', 'auditory', 'kinesthetic', 'all']),
            'difficulty_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'content_url' => fake()->url(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function visual(): static
    {
        return $this->state(fn (array $attributes) => [
            'learning_style' => 'visual',
        ]);
    }

    public function auditory(): static
    {
        return $this->state(fn (array $attributes) => [
            'learning_style' => 'auditory',
        ]);
    }

    public function kinesthetic(): static
    {
        return $this->state(fn (array $attributes) => [
            'learning_style' => 'kinesthetic',
        ]);
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'content_url' => 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9]{11}'),
        ]);
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
        ]);
    }
}
