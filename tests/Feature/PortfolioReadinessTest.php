<?php

use App\Models\User;
use Database\Seeders\DemoSeeder;

$portfolioRoutes = [
    'dashboard' => fn () => route('dashboard'),
    'transactions index' => fn () => route('transactions.index'),
    'transactions create' => fn () => route('transactions.create'),
    'categories' => fn () => route('categories.index'),
    'budgets' => fn () => route('budgets.index'),
    'savings goals' => fn () => route('savings-goals.index'),
    'mood tracker' => fn () => route('mood-tracker.index'),
    'achievements' => fn () => route('achievements.index'),
    'analytics' => fn () => route('analytics.index'),
    'settings profile' => fn () => route('settings.profile'),
];

test('landing page renders with portfolio branding', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Lifestyle Financial Tracker')
        ->assertSee('Track your money, understand your lifestyle.');
});

test('main authenticated pages render safely with empty user data', function (Closure $route) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get($route())
        ->assertSuccessful();
})->with($portfolioRoutes);

test('guests are redirected away from app pages', function (Closure $route) {
    $this->get($route())
        ->assertRedirect('/login');
})->with($portfolioRoutes);

test('optional demo seeder creates portfolio demo data without being part of default seeding', function () {
    $this->seed();

    expect(User::query()->where('email', 'demo@example.com')->exists())->toBeFalse();

    $this->seed(DemoSeeder::class);

    $demoUser = User::query()->where('email', 'demo@example.com')->firstOrFail();

    expect($demoUser->transactions()->count())->toBeGreaterThan(0)
        ->and($demoUser->budgets()->count())->toBeGreaterThan(0)
        ->and($demoUser->savingsGoals()->count())->toBeGreaterThan(0)
        ->and($demoUser->moodLogs()->count())->toBeGreaterThan(0)
        ->and($demoUser->xpLogs()->count())->toBeGreaterThan(0)
        ->and($demoUser->achievements()->count())->toBeGreaterThan(0);
});
