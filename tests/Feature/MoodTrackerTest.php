<?php

use App\Enums\Mood;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\InsightService;
use Livewire\Volt\Volt;

test('user can create daily mood', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Volt::test('mood-tracker.index')
        ->set('mood', Mood::Calm->value)
        ->set('logged_date', now()->toDateString())
        ->set('note', 'A small reflection for today.')
        ->call('save');

    $moodLog = MoodLog::query()->where('user_id', $user->id)->firstOrFail();

    expect($moodLog->mood)->toBe(Mood::Calm)
        ->and($moodLog->note)->toBe('A small reflection for today.')
        ->and($moodLog->logged_date->toDateString())->toBe(now()->toDateString());
});

test('user updates mood on the same date instead of creating duplicate', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Volt::test('mood-tracker.index')
        ->set('mood', Mood::Stressed->value)
        ->set('logged_date', now()->toDateString())
        ->set('note', 'Heavy day')
        ->call('save');

    Volt::test('mood-tracker.index')
        ->set('mood', Mood::Productive->value)
        ->set('logged_date', now()->toDateString())
        ->set('note', 'Recovered focus')
        ->call('save');

    expect(MoodLog::query()->where('user_id', $user->id)->whereDate('logged_date', now()->toDateString())->count())->toBe(1);

    $this->assertDatabaseHas('mood_logs', [
        'user_id' => $user->id,
        'mood' => Mood::Productive->value,
        'note' => 'Recovered focus',
    ]);
});

test('user only sees own mood logs', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    MoodLog::factory()->create([
        'user_id' => $user->id,
        'mood' => Mood::Happy,
        'note' => 'Visible reflection',
        'logged_date' => now()->toDateString(),
    ]);
    MoodLog::factory()->create([
        'user_id' => $otherUser->id,
        'mood' => Mood::Anxious,
        'note' => 'Hidden reflection',
        'logged_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $this->get(route('mood-tracker.index'))
        ->assertSuccessful()
        ->assertSee('Visible reflection')
        ->assertDontSee('Hidden reflection');
});

test('dashboard shows today mood when available', function () {
    $user = User::factory()->create();

    MoodLog::factory()->create([
        'user_id' => $user->id,
        'mood' => Mood::Calm,
        'note' => 'No pressure, just awareness.',
        'logged_date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee("Today's Mood", false)
        ->assertSee('Calm')
        ->assertSee('No pressure, just awareness.');
});

test('dashboard remains safe without mood', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('No mood yet');
});

test('insight service returns fallback insight when data is empty', function () {
    $user = User::factory()->create();

    $insights = app(InsightService::class)->getSimpleInsightsForUser($user);

    expect($insights)->toContain('Mulai catat transaksi agar insight personal bisa muncul.');
});
