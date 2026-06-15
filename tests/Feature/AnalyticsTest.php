<?php

use App\Enums\Mood;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\MoodLog;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;

function analyticsCategory(TransactionType $type, string $name, ?User $user = null): Category
{
    return Category::factory()->create([
        'user_id' => $user?->id,
        'type' => $type,
        'name' => $name,
    ]);
}

test('guest cannot access analytics page', function () {
    $this->get(route('analytics.index'))
        ->assertRedirect('/login');
});

test('authenticated user can access analytics page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertSuccessful()
        ->assertSee('Analytics')
        ->assertSee('Monthly Review');
});

test('monthly summary calculates income expense balance and transaction count', function () {
    $user = User::factory()->create();
    $incomeCategory = analyticsCategory(TransactionType::Income, 'Salary');
    $expenseCategory = analyticsCategory(TransactionType::Expense, 'Food');
    $otherUser = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'type' => TransactionType::Income,
        'amount' => 2000000,
        'transaction_date' => '2026-05-03',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'type' => TransactionType::Expense,
        'amount' => 350000,
        'transaction_date' => '2026-05-04',
    ]);
    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $incomeCategory->id,
        'type' => TransactionType::Income,
        'amount' => 9999999,
        'transaction_date' => '2026-05-04',
    ]);

    $summary = app(AnalyticsService::class)->getMonthlySummary($user, Carbon::parse('2026-05-01'));

    expect($summary['totalIncome'])->toBe(2000000.0)
        ->and($summary['totalExpense'])->toBe(350000.0)
        ->and($summary['balance'])->toBe(1650000.0)
        ->and($summary['transactionCount'])->toBe(2)
        ->and($summary['biggestExpenseCategory']['name'])->toBe('Food');
});

test('category spending breakdown is sorted and scoped to user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $food = analyticsCategory(TransactionType::Expense, 'Food');
    $transport = analyticsCategory(TransactionType::Expense, 'Transport');

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $food->id,
        'type' => TransactionType::Expense,
        'amount' => 600000,
        'transaction_date' => '2026-05-04',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $transport->id,
        'type' => TransactionType::Expense,
        'amount' => 400000,
        'transaction_date' => '2026-05-05',
    ]);
    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $transport->id,
        'type' => TransactionType::Expense,
        'amount' => 999000,
        'transaction_date' => '2026-05-05',
    ]);

    $breakdown = app(AnalyticsService::class)->getCategorySpending($user, Carbon::parse('2026-05-01'));

    expect($breakdown)->toHaveCount(2)
        ->and($breakdown[0]['name'])->toBe('Food')
        ->and($breakdown[0]['total'])->toBe(600000.0)
        ->and($breakdown[0]['percentage'])->toBe(60.0)
        ->and($breakdown[1]['name'])->toBe('Transport')
        ->and($breakdown[1]['percentage'])->toBe(40.0);
});

test('budget performance returns usage and status', function () {
    $user = User::factory()->create();
    $category = analyticsCategory(TransactionType::Expense, 'Food');

    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 1000000,
        'month' => '2026-05',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 800000,
        'transaction_date' => '2026-05-10',
    ]);

    $budget = app(AnalyticsService::class)->getBudgetPerformance($user, Carbon::parse('2026-05-01'));

    expect($budget['totalBudget'])->toBe(1000000.0)
        ->and($budget['totalUsed'])->toBe(800000.0)
        ->and($budget['totalRemaining'])->toBe(200000.0)
        ->and($budget['usagePercentage'])->toBe(80.0)
        ->and($budget['status'])->toBe('warning');
});

test('savings progress summarizes active goals and closest completion', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    SavingsGoal::factory()->create([
        'user_id' => $user->id,
        'title' => 'Emergency Fund',
        'target_amount' => 10000000,
        'current_amount' => 5000000,
    ]);
    SavingsGoal::factory()->create([
        'user_id' => $user->id,
        'title' => 'New Laptop',
        'target_amount' => 5000000,
        'current_amount' => 4000000,
    ]);
    SavingsGoal::factory()->create([
        'user_id' => $otherUser->id,
        'target_amount' => 100000000,
        'current_amount' => 100000000,
    ]);

    $progress = app(AnalyticsService::class)->getSavingsProgress($user);

    expect($progress['activeGoalsCount'])->toBe(2)
        ->and($progress['totalTargetAmount'])->toBe(15000000.0)
        ->and($progress['totalSavedAmount'])->toBe(9000000.0)
        ->and($progress['overallProgressPercentage'])->toBe(60.0)
        ->and($progress['closestGoals'][0]['goal']->title)->toBe('New Laptop');
});

test('mood spending correlation is safe when enough data exists', function () {
    $user = User::factory()->create();
    $category = analyticsCategory(TransactionType::Expense, 'Food');

    MoodLog::factory()->create([
        'user_id' => $user->id,
        'mood' => Mood::Stressed,
        'logged_date' => '2026-05-05',
    ]);
    MoodLog::factory()->create([
        'user_id' => $user->id,
        'mood' => Mood::Calm,
        'logged_date' => '2026-05-06',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 300000,
        'transaction_date' => '2026-05-05',
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 100000,
        'transaction_date' => '2026-05-06',
    ]);

    $correlation = app(AnalyticsService::class)->getMoodSpendingCorrelation($user, Carbon::parse('2026-05-01'));

    expect($correlation['hasEnoughData'])->toBeTrue()
        ->and($correlation['moodWithHighestExpense'])->toBe('Stressed')
        ->and($correlation['totalExpenseOnMoodDays'])->toBe(400000.0)
        ->and($correlation['rows'][0]['totalExpense'])->toBe(300000.0);
});

test('analytics fallback is safe when data is empty', function () {
    $user = User::factory()->create();
    $service = app(AnalyticsService::class);

    $summary = $service->getMonthlySummary($user, Carbon::parse('2026-05-01'));
    $review = $service->getMonthlyReview($user, Carbon::parse('2026-05-01'));
    $correlation = $service->getMoodSpendingCorrelation($user, Carbon::parse('2026-05-01'));

    expect($summary['transactionCount'])->toBe(0)
        ->and($summary['totalIncome'])->toBe(0.0)
        ->and($summary['totalExpense'])->toBe(0.0)
        ->and($review['hasData'])->toBeFalse()
        ->and($review['sentence'])->toBe('Belum ada cukup data untuk membuat review bulan ini.')
        ->and($correlation['hasEnoughData'])->toBeFalse();
});

test('analytics page does not show another users data', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $category = analyticsCategory(TransactionType::Expense, 'Hidden Category');

    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 999000,
        'transaction_date' => now()->toDateString(),
        'behavior_note' => 'Hidden behavior note',
    ]);

    $this->actingAs($user)
        ->get(route('analytics.index'))
        ->assertSuccessful()
        ->assertDontSee('Hidden Category')
        ->assertDontSee('Hidden behavior note')
        ->assertDontSee('Rp 999.000');
});
