<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Policies\BudgetPolicy;
use App\Policies\SavingsGoalPolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config()->set('livewire.layout', 'components.layouts.app');
        config()->set('livewire.component_layout', 'components.layouts.app');
        Gate::policy(Budget::class, BudgetPolicy::class);
        Gate::policy(SavingsGoal::class, SavingsGoalPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }
}
