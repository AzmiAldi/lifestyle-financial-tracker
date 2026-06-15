<?php

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BudgetService;
use Livewire\Volt\Volt;

test('user can create update and delete a budget', function () {
    $user = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    $this->actingAs($user);

    Volt::test('budgets.index')
        ->set('month', '2026-05')
        ->set('category_id', (string) $category->id)
        ->set('amount', '1000000')
        ->call('save');

    $budget = Budget::query()->where('user_id', $user->id)->firstOrFail();

    Volt::test('budgets.index')
        ->call('edit', $budget->id)
        ->set('amount', '1500000')
        ->call('save');

    expect((string) $budget->refresh()->amount)->toBe('1500000.00');

    Volt::test('budgets.index')
        ->call('delete', $budget->id);

    $this->assertDatabaseMissing('budgets', [
        'id' => $budget->id,
    ]);
});

test('budget calculations use expense transactions for the scoped month and category', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 1000000,
        'month' => '2026-05',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 250000,
        'transaction_date' => '2026-05-10',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 500000,
        'transaction_date' => '2026-06-10',
    ]);
    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 999000,
        'transaction_date' => '2026-05-10',
    ]);

    $row = app(BudgetService::class)->getBudgetOverviewForUser($user, '2026-05')['budgets'][0];

    expect($row['budget']->id)->toBe($budget->id)
        ->and($row['used'])->toBe(250000.0)
        ->and($row['remaining'])->toBe(750000.0)
        ->and($row['usagePercentage'])->toBe(25.0);
});

test('user cannot manage another users budget', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($otherUser);

    Volt::test('budgets.index')
        ->call('delete', $budget->id)
        ->assertForbidden();
});

test('one budget per category per month is enforced', function () {
    $user = User::factory()->create();
    $category = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'month' => '2026-05',
    ]);

    $this->actingAs($user);

    Volt::test('budgets.index')
        ->set('month', '2026-05')
        ->set('category_id', (string) $category->id)
        ->set('amount', '1000000')
        ->call('save')
        ->assertHasErrors(['category_id']);
});

test('global monthly budget calculates all monthly expense transactions', function () {
    $user = User::factory()->create();
    $food = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);
    $transport = Category::factory()->global()->create([
        'type' => TransactionType::Expense,
    ]);

    Budget::factory()->global()->create([
        'user_id' => $user->id,
        'amount' => 1500000,
        'month' => '2026-05',
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'type' => TransactionType::Expense,
        'amount' => 250000,
        'transaction_date' => '2026-05-10',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $transport->id,
        'type' => TransactionType::Expense,
        'amount' => 150000,
        'transaction_date' => '2026-05-12',
    ]);

    $overview = app(BudgetService::class)->getBudgetOverviewForUser($user, '2026-05');

    expect($overview['totalUsed'])->toBe(400000.0)
        ->and($overview['totalRemaining'])->toBe(1100000.0);
});
