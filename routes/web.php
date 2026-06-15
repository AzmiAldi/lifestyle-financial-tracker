<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('transactions', 'transactions.index')->middleware('verified')->name('transactions.index');
    Volt::route('transactions/create', 'transactions.create')->middleware('verified')->name('transactions.create');
    Volt::route('transactions/{transaction}/edit', 'transactions.edit')->middleware('verified')->name('transactions.edit');
    Volt::route('budgets', 'budgets.index')->middleware('verified')->name('budgets.index');
    Volt::route('savings-goals', 'savings-goals.index')->middleware('verified')->name('savings-goals.index');
    Volt::route('mood-tracker', 'mood-tracker.index')->middleware('verified')->name('mood-tracker.index');
    Volt::route('achievements', 'achievements.index')->middleware('verified')->name('achievements.index');
    Volt::route('analytics', 'analytics.index')->middleware('verified')->name('analytics.index');

    Route::get('categories', [CategoryController::class, 'index'])->middleware('verified')->name('categories.index');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('verified')->name('categories.store');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
