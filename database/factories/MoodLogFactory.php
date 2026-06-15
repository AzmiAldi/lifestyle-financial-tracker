<?php

namespace Database\Factories;

use App\Enums\Mood;
use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MoodLog>
 */
class MoodLogFactory extends Factory
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
            'mood' => fake()->randomElement(Mood::cases()),
            'note' => fake()->optional()->sentence(),
            'logged_date' => fake()->date(),
        ];
    }
}
