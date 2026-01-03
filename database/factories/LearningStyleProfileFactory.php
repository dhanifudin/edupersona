<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningStyleProfile>
 */
class LearningStyleProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $visual = fake()->randomFloat(2, 30, 90);
        $auditory = fake()->randomFloat(2, 30, 90);
        $kinesthetic = fake()->randomFloat(2, 30, 90);

        $dominant = match (true) {
            $visual > $auditory && $visual > $kinesthetic => 'visual',
            $auditory > $visual && $auditory > $kinesthetic => 'auditory',
            $kinesthetic > $visual && $kinesthetic > $auditory => 'kinesthetic',
            default => 'mixed',
        };

        return [
            'user_id' => User::factory(),
            'visual_score' => $visual,
            'auditory_score' => $auditory,
            'kinesthetic_score' => $kinesthetic,
            'dominant_style' => $dominant,
            'analyzed_at' => now(),
        ];
    }

    public function visual(): static
    {
        return $this->state(fn (array $attributes) => [
            'visual_score' => 85.00,
            'auditory_score' => 45.00,
            'kinesthetic_score' => 35.00,
            'dominant_style' => 'visual',
        ]);
    }

    public function auditory(): static
    {
        return $this->state(fn (array $attributes) => [
            'visual_score' => 40.00,
            'auditory_score' => 88.00,
            'kinesthetic_score' => 42.00,
            'dominant_style' => 'auditory',
        ]);
    }

    public function kinesthetic(): static
    {
        return $this->state(fn (array $attributes) => [
            'visual_score' => 35.00,
            'auditory_score' => 40.00,
            'kinesthetic_score' => 90.00,
            'dominant_style' => 'kinesthetic',
        ]);
    }

    public function mixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'visual_score' => 72.00,
            'auditory_score' => 70.00,
            'kinesthetic_score' => 68.00,
            'dominant_style' => 'mixed',
        ]);
    }
}
