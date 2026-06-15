<?php

namespace Database\Factories;

use App\Models\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'key' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => Str::title($title),
            'description' => fake()->sentence(),
            'icon' => 'sparkles',
            'xp_reward' => 0,
        ];
    }
}
