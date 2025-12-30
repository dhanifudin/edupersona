<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiRecommendation>
 */
class AiRecommendationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'material_id' => LearningMaterial::factory(),
            'reason' => fake()->sentence(),
            'relevance_score' => fake()->randomFloat(2, 0.5, 1.0),
            'is_viewed' => false,
            'viewed_at' => null,
        ];
    }

    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_viewed' => true,
            'viewed_at' => now(),
        ]);
    }
}
