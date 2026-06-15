<?php

use App\Enums\Mood;
use App\Enums\TransactionType;
use App\Models\Achievement;
use App\Models\Category;
use App\Models\MoodLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\XpLog;
use App\Services\GamificationService;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Volt;

function createExpenseCategory(): Category
{
    return Category::factory()->global()->create([
        'type' => TransactionType::Expense,
        'name' => 'Food',
    ]);
}

test('creating a transaction awards xp and unlocks first transaction achievement', function () {
    $user = User::factory()->create();
    $category = createExpenseCategory();

    $this->actingAs($user);

    Volt::test('transactions.create')
        ->set('type', TransactionType::Expense->value)
        ->set('category_id', (string) $category->id)
        ->set('amount', '50000')
        ->set('transaction_date', now()->toDateString())
        ->call('save');

    $transaction = Transaction::query()->where('user_id', $user->id)->firstOrFail();

    $this->assertDatabaseHas('xp_logs', [
        'user_id' => $user->id,
        'amount' => 5,
        'reason' => 'create_transaction',
        'source_type' => Transaction::class,
        'source_id' => $transaction->id,
    ]);

    expect($user->achievements()->where('key', 'first_transaction')->exists())->toBeTrue();
});

test('xp is not awarded twice for the same source', function () {
    $user = User::factory()->create();
    $service = app(GamificationService::class);

    $service->awardXp($user, 5, 'create_transaction', Transaction::class, 123);
    $service->awardXp($user, 5, 'create_transaction', Transaction::class, 123);

    expect(XpLog::query()->where('user_id', $user->id)->count())->toBe(1)
        ->and($service->getTotalXp($user))->toBe(5);
});

test('level calculation uses configured thresholds', function () {
    $user = User::factory()->create();
    XpLog::factory()->create([
        'user_id' => $user->id,
        'amount' => 260,
        'reason' => 'manual_test_progress',
    ]);

    $service = app(GamificationService::class);

    expect($service->getTotalXp($user))->toBe(260)
        ->and($service->getCurrentLevel($user))->toBe(3)
        ->and($service->getXpToNextLevel($user))->toBe(240)
        ->and($service->getLevelProgressPercentage($user))->toBe(4.0);
});

test('achievement cannot be unlocked twice for the same user', function () {
    $user = User::factory()->create();
    $category = createExpenseCategory();
    $service = app(GamificationService::class);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'transaction_date' => now()->toDateString(),
    ]);

    $service->checkAchievements($user);
    $service->checkAchievements($user);

    $achievement = Achievement::query()->where('key', 'first_transaction')->firstOrFail();

    expect(DB::table('user_achievements')
        ->where('user_id', $user->id)
        ->where('achievement_id', $achievement->id)
        ->count())->toBe(1);
});

test('user does not see another users achievement as unlocked', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    app(GamificationService::class)->ensureDefaultAchievements();

    $achievement = Achievement::query()->where('key', 'first_budget')->firstOrFail();
    $otherUser->achievements()->attach($achievement->id, [
        'unlocked_at' => now(),
    ]);

    $this->actingAs($user);

    $this->get(route('achievements.index'))
        ->assertSuccessful()
        ->assertSee('Budget Starter')
        ->assertSee('Locked')
        ->assertDontSee('Unlocked '.now()->format('d M Y'));
});

test('dashboard is safe without xp or achievements', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Growth Progress')
        ->assertSee('Level 1')
        ->assertSee('No achievement yet');
});

test('daily tracking streak increases with activity today', function () {
    $user = User::factory()->create();
    $category = createExpenseCategory();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'transaction_date' => now()->subDay()->toDateString(),
    ]);

    MoodLog::factory()->create([
        'user_id' => $user->id,
        'mood' => Mood::Calm,
        'logged_date' => now()->toDateString(),
    ]);

    expect(app(GamificationService::class)->getDailyTrackingStreak($user))->toBe(2);
});

test('creating mood budget and savings goal unlocks their first milestones', function () {
    $user = User::factory()->create();
    $category = createExpenseCategory();

    $this->actingAs($user);

    Volt::test('mood-tracker.index')
        ->set('mood', Mood::Calm->value)
        ->set('logged_date', now()->toDateString())
        ->call('save');

    Volt::test('budgets.index')
        ->set('month', now()->format('Y-m'))
        ->set('category_id', (string) $category->id)
        ->set('amount', '1000000')
        ->call('save');

    Volt::test('savings-goals.index')
        ->set('title', 'Emergency Fund')
        ->set('target_amount', '10000000')
        ->set('current_amount', '1000000')
        ->call('save');

    expect($user->achievements()->where('key', 'first_mood_log')->exists())->toBeTrue()
        ->and($user->achievements()->where('key', 'first_budget')->exists())->toBeTrue()
        ->and($user->achievements()->where('key', 'first_savings_goal')->exists())->toBeTrue();
});
