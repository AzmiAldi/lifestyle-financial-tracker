<?php

use App\Models\SavingsGoal;
use App\Models\User;
use App\Services\SavingsGoalService;
use Livewire\Volt\Volt;

test('user can create update and delete a savings goal', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('savings-goals.index')
        ->set('title', 'Emergency Fund')
        ->set('target_amount', '10000000')
        ->set('current_amount', '2500000')
        ->set('deadline', '2026-12-31')
        ->set('description', 'Six month safety buffer')
        ->call('save');

    $goal = SavingsGoal::query()->where('user_id', $user->id)->firstOrFail();

    Volt::test('savings-goals.index')
        ->call('edit', $goal->id)
        ->set('current_amount', '3000000')
        ->call('save');

    expect((string) $goal->refresh()->current_amount)->toBe('3000000.00');

    Volt::test('savings-goals.index')
        ->call('delete', $goal->id);

    $this->assertDatabaseMissing('savings_goals', [
        'id' => $goal->id,
    ]);
});

test('savings goal progress and remaining target are calculated accurately', function () {
    $goal = SavingsGoal::factory()->create([
        'target_amount' => 10000000,
        'current_amount' => 2500000,
    ]);

    $service = app(SavingsGoalService::class);

    expect($service->calculateProgressPercentage($goal))->toBe(25.0)
        ->and($service->calculateRemainingTarget($goal))->toBe(7500000.0);
});

test('user cannot manage another users savings goal', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $goal = SavingsGoal::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($otherUser);

    Volt::test('savings-goals.index')
        ->call('delete', $goal->id)
        ->assertForbidden();
});
