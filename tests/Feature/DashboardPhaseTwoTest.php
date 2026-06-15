<?php

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;

test('dashboard includes budget and savings summaries', function () {
    $user = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
        'name' => 'Food',
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 1000000,
        'month' => now()->format('Y-m'),
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 250000,
        'transaction_date' => now()->toDateString(),
    ]);
    SavingsGoal::factory()->create([
        'user_id' => $user->id,
        'title' => 'Emergency Fund',
        'target_amount' => 10000000,
        'current_amount' => 2500000,
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Budget Overview')
        ->assertSee('Savings Goals Progress')
        ->assertSee('Rp 250.000')
        ->assertSee('Emergency Fund')
        ->assertSee('25%');
});
