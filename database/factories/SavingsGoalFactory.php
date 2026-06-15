<?php

namespace Database\Factories;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsGoal>
 */
class SavingsGoalFactory extends Factory
{
    protected $model = SavingsGoal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'target_amount' => fake()->randomFloat(2, 1000000, 20000000),
            'current_amount' => fake()->randomFloat(2, 0, 1000000),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
