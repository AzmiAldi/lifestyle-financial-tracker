<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => TransactionType::Income, 'color' => '#16a34a'],
            ['name' => 'Freelance', 'type' => TransactionType::Income, 'color' => '#22c55e'],
            ['name' => 'Allowance', 'type' => TransactionType::Income, 'color' => '#65a30d'],
            ['name' => 'Bonus', 'type' => TransactionType::Income, 'color' => '#4d7c0f'],
            ['name' => 'Side Hustle', 'type' => TransactionType::Income, 'color' => '#15803d'],
            ['name' => 'Food', 'type' => TransactionType::Expense, 'color' => '#f97316'],
            ['name' => 'Transport', 'type' => TransactionType::Expense, 'color' => '#eab308'],
            ['name' => 'Shopping', 'type' => TransactionType::Expense, 'color' => '#ef4444'],
            ['name' => 'Entertainment', 'type' => TransactionType::Expense, 'color' => '#ec4899'],
            ['name' => 'Health', 'type' => TransactionType::Expense, 'color' => '#8b5cf6'],
            ['name' => 'Bills', 'type' => TransactionType::Expense, 'color' => '#64748b'],
            ['name' => 'Education', 'type' => TransactionType::Expense, 'color' => '#3b82f6'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                [
                    'user_id' => null,
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'icon' => null,
                    'color' => $category['color'],
                ],
            );
        }
    }
}
