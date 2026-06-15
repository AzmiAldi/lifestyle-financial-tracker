<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'icon' => null,
            'color' => null,
            'type' => fake()->randomElement([TransactionType::Income, TransactionType::Expense]),
        ];
    }

    public function global(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
        ]);
    }
}
