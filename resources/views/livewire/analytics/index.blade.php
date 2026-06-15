<?php

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    #[Computed]
    public function monthlySummary(): array
    {
        return app(AnalyticsService::class)->getMonthlySummary(Auth::user());
    }

    #[Computed]
    public function categorySpending(): array
    {
        return app(AnalyticsService::class)->getCategorySpending(Auth::user());
    }

    #[Computed]
    public function budgetPerformance(): array
    {
        return app(AnalyticsService::class)->getBudgetPerformance(Auth::user());
    }

    #[Computed]
    public function savingsProgress(): array
    {
        return app(AnalyticsService::class)->getSavingsProgress(Auth::user());
    }

    #[Computed]
    public function moodSpendingCorrelation(): array
    {
        return app(AnalyticsService::class)->getMoodSpendingCorrelation(Auth::user());
    }

    #[Computed]
    public function monthlyReview(): array
    {
        return app(AnalyticsService::class)->getMonthlyReview(Auth::user());
    }
}; ?>

<div class="space-y-8">
    @php
        $budgetTone = match ($this->budgetPerformance['status']) {
            'exceeded' => 'danger',
            'warning' => 'warning',
            default => 'safe',
        };

        $budgetLabel = match ($this->budgetPerformance['status']) {
            'exceeded' => 'Exceeded',
            'warning' => 'Watch closely',
            default => 'Safe',
        };
    @endphp

    <x-ui.app-card class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-cyan-300/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-10 h-72 w-72 rounded-full bg-emerald-300/[0.06] blur-3xl"></div>

        <div class="relative flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
            <x-ui.section-header
                title="Analytics"
                eyebrow="Monthly Reflection"
                description="Here's what your money pattern looks like this month. A calm review, not an accounting report."
            />

            <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 text-sm text-zinc-400 shadow-[0_18px_60px_rgba(0,0,0,0.24)]">
                {{ now()->format('F Y') }}
            </div>
        </div>

        <div class="relative mt-7 rounded-2xl border border-white/10 bg-[#050507]/35 p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)]">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Monthly Review</p>
            <p class="mt-3 text-xl font-semibold leading-8 text-white">{{ $this->monthlyReview['sentence'] }}</p>

            @unless ($this->monthlyReview['hasData'])
                <p class="mt-2 text-sm text-zinc-400">Mulai dari satu transaksi kecil. Pola akan makin jelas seiring data bertambah.</p>
            @endunless
        </div>
    </x-ui.app-card>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.metric-card label="Monthly Income" value="{{ rupiah($this->monthlySummary['totalIncome']) }}" description="Total income tracked this month." tone="positive" />
        <x-ui.metric-card label="Monthly Expense" value="{{ rupiah($this->monthlySummary['totalExpense']) }}" description="Total expenses recorded this month." tone="negative" />
        <x-ui.metric-card label="Net Balance" value="{{ rupiah($this->monthlySummary['balance']) }}" description="Income minus expense for the month." tone="accent" />
        <x-ui.metric-card label="Transactions" value="{{ $this->monthlySummary['transactionCount'] }}" description="Tracked movements this month." tone="neutral" />
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.05fr_0.95fr]">
        <x-ui.dashboard-widget
            title="Category Spending"
            description="Highest expense categories first, shown as a simple progress list."
        >
            <div class="space-y-3 px-5 pb-5 pt-4 md:px-6">
                @forelse ($this->categorySpending as $category)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4 transition duration-200 hover:bg-white/[0.05]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-white">{{ $category['name'] }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ $category['transactionCount'] }} transaction{{ $category['transactionCount'] === 1 ? '' : 's' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-rose-300">{{ rupiah($category['total']) }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $category['percentage'] }}%</p>
                            </div>
                        </div>
                        <x-ui.progress-bar class="mt-3" :value="$category['percentage']" tone="danger" />
                    </div>
                @empty
                    <x-ui.empty-state
                        title="Belum ada expense bulan ini."
                        description="Category breakdown akan muncul setelah ada transaksi expense."
                        action-label="Add transaction"
                        :action-url="route('transactions.create')"
                    />
                @endforelse
            </div>
        </x-ui.dashboard-widget>

        <x-ui.dashboard-widget
            title="Budget Performance"
            description="A quick read on how much breathing room remains."
        >
            <div class="px-5 pb-5 pt-4 md:px-6">
                @if ($this->budgetPerformance['totalBudget'] > 0)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">{{ $budgetLabel }}</p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight text-white">{{ $this->budgetPerformance['usagePercentage'] }}%</p>
                            </div>
                            <span class="rounded-full border px-3 py-1 text-xs font-medium {{ $budgetTone === 'danger' ? 'border-rose-300/15 bg-rose-300/[0.08] text-rose-200' : ($budgetTone === 'warning' ? 'border-amber-300/15 bg-amber-300/[0.08] text-amber-200' : 'border-emerald-300/15 bg-emerald-300/[0.08] text-emerald-200') }}">
                                {{ $budgetLabel }}
                            </span>
                        </div>
                        <x-ui.progress-bar class="mt-5" :value="$this->budgetPerformance['usagePercentage']" :tone="$budgetTone" height="h-3" />
                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                            <div>
                                <p class="text-xs text-zinc-500">Budget</p>
                                <p class="mt-1 font-semibold text-white">{{ rupiah($this->budgetPerformance['totalBudget']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Used</p>
                                <p class="mt-1 font-semibold text-rose-300">{{ rupiah($this->budgetPerformance['totalUsed']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Remaining</p>
                                <p class="mt-1 font-semibold text-cyan-100">{{ rupiah($this->budgetPerformance['totalRemaining']) }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <x-ui.empty-state
                        title="No budget data yet."
                        description="Set a simple monthly budget so analytics can show usage and remaining room."
                        action-label="Set budget"
                        :action-url="route('budgets.index')"
                    />
                @endif
            </div>
        </x-ui.dashboard-widget>
    </section>

    <section class="grid gap-5 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.dashboard-widget
            title="Savings Progress"
            description="A calm summary of active goals and closest progress."
        >
            <div class="px-5 pb-5 pt-4 md:px-6">
                @if ($this->savingsProgress['activeGoalsCount'] > 0)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Overall Progress</p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight text-white">{{ $this->savingsProgress['overallProgressPercentage'] }}%</p>
                            </div>
                            <span class="rounded-full border border-cyan-300/15 bg-cyan-300/[0.08] px-3 py-1 text-xs font-medium text-cyan-200">
                                {{ $this->savingsProgress['activeGoalsCount'] }} goal{{ $this->savingsProgress['activeGoalsCount'] === 1 ? '' : 's' }}
                            </span>
                        </div>
                        <x-ui.progress-bar class="mt-5" :value="$this->savingsProgress['overallProgressPercentage']" tone="accent" height="h-3" />
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-zinc-500">Saved</p>
                                <p class="mt-1 font-semibold text-cyan-100">{{ rupiah($this->savingsProgress['totalSavedAmount']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-500">Target</p>
                                <p class="mt-1 font-semibold text-white">{{ rupiah($this->savingsProgress['totalTargetAmount']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-3">
                        @foreach ($this->savingsProgress['closestGoals'] as $goal)
                            <x-ui.progress-card
                                :title="$goal['goal']->title"
                                :meta="rupiah($goal['remaining']).' remaining'"
                                :value="$goal['barPercentage']"
                                tone="accent"
                                :caption="$goal['estimatedCompletion'] ? 'Target feel: '.$goal['estimatedCompletion'] : 'Progress kecil tetap berarti.'"
                            />
                        @endforeach
                    </div>
                @else
                    <x-ui.empty-state
                        title="No savings goals yet."
                        description="Create one personal target so progress can become visible here."
                        action-label="Create goal"
                        :action-url="route('savings-goals.index')"
                    />
                @endif
            </div>
        </x-ui.dashboard-widget>

        <x-ui.dashboard-widget
            title="Mood & Spending Pattern"
            description="Light correlation only. Reflective, not diagnostic."
        >
            <div class="space-y-4 px-5 pb-5 pt-4 md:px-6">
                <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5">
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Simple Reflection</p>
                    <p class="mt-3 text-lg font-semibold leading-7 text-white">{{ $this->moodSpendingCorrelation['sentence'] }}</p>
                    @if ($this->moodSpendingCorrelation['mostCommonMood'])
                        <p class="mt-2 text-sm text-zinc-400">Most common mood: {{ $this->moodSpendingCorrelation['mostCommonMood'] }}</p>
                    @endif
                </div>

                @forelse ($this->moodSpendingCorrelation['rows'] as $row)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4 transition duration-200 hover:bg-white/[0.05]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-white">{{ $row['mood'] }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ $row['count'] }} logged day{{ $row['count'] === 1 ? '' : 's' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-rose-300">{{ rupiah($row['totalExpense']) }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $row['percentage'] }}%</p>
                            </div>
                        </div>
                        <x-ui.progress-bar class="mt-3" :value="$row['percentage']" tone="warning" />
                    </div>
                @empty
                    <x-ui.empty-state
                        title="Mood pattern is still forming."
                        description="Catat mood dan transaksi beberapa hari lagi agar pola mulai terlihat."
                        action-label="Open Mood Tracker"
                        :action-url="route('mood-tracker.index')"
                    />
                @endforelse
            </div>
        </x-ui.dashboard-widget>
    </section>

    @if ($this->monthlyReview['behaviorNotes'] !== [])
        <section>
            <x-ui.dashboard-widget
                title="Recent Behavior Notes"
                description="Small context from your own transaction reflections."
            >
                <div class="space-y-2 px-5 pb-5 pt-4 md:px-6">
                    @foreach ($this->monthlyReview['behaviorNotes'] as $note)
                        <div class="flex flex-col gap-3 rounded-xl px-3 py-3 transition duration-200 hover:bg-white/[0.04] md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="font-medium text-white">{{ $note['category'] }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ $note['date'] }} | {{ strtoupper($note['type']) }}</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $note['note'] }}</p>
                            </div>
                            <p class="font-semibold {{ $note['type'] === 'income' ? 'text-emerald-300' : 'text-rose-300' }}">
                                {{ $note['type'] === 'income' ? '+ ' : '- ' }}{{ rupiah($note['amount']) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </x-ui.dashboard-widget>
        </section>
    @endif
</div>
