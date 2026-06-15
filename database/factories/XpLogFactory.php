<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\XpLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<XpLog>
 */
class XpLogFactory extends Factory
{
    protected $model = XpLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(1, 25),
            'reason' => fake()->randomElement(['create_transaction', 'create_budget', 'create_savings_goal', 'create_mood_log']),
            'source_type' => null,
            'source_id' => null,
        ];
    }
}
