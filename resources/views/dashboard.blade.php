<x-layouts.app>
    @php
        $budgetTone = match (true) {
            $budgetOverview['usagePercentage'] >= 100 => 'danger',
            $budgetOverview['usagePercentage'] >= 75 => 'warning',
            default => 'safe',
        };

        $budgetState = match ($budgetTone) {
            'danger' => 'Over budget',
            'warning' => 'Watch closely',
            default => 'Healthy pace',
        };

        $budgetToneClasses = [
            'danger' => 'border-rose-300/15 bg-rose-300/[0.08] text-rose-200',
            'warning' => 'border-amber-300/15 bg-amber-300/[0.08] text-amber-200',
            'safe' => 'border-emerald-300/15 bg-emerald-300/[0.08] text-emerald-200',
        ][$budgetTone];
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-3">
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">{{ now()->format('F Y') }}</p>
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-white md:text-4xl">Financial cockpit</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">A calm monthly command center for money, habits, and progress.</p>
                </div>
                <div class="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm text-zinc-400 shadow-[0_16px_50px_rgba(0,0,0,0.24)]">
                    {{ $summary['recentTransactions']->count() }} tracked movements
                </div>
            </div>
        </div>

        <section class="grid gap-5 xl:grid-cols-[1.45fr_0.75fr]">
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br from-white/[0.085] via-cyan-300/[0.055] to-white/[0.025] p-6 shadow-[0_28px_110px_rgba(0,0,0,0.46)] backdrop-blur md:p-8">
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-cyan-300/15 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 left-8 h-72 w-72 rounded-full bg-emerald-300/[0.08] blur-3xl"></div>

                <div class="relative">
                    <div class="flex flex-col justify-between gap-6 md:flex-row md:items-start">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Balance Overview</p>
                            <h2 class="mt-4 text-sm font-medium text-zinc-400">Current balance</h2>
                            <p class="mt-3 text-5xl font-semibold tracking-[-0.045em] text-white md:text-6xl">{{ rupiah($summary['balance']) }}</p>
                            <p class="mt-4 max-w-xl text-sm leading-6 text-zinc-400">Keep the trend visible without turning your day into a spreadsheet.</p>
                        </div>

                        <div class="w-full rounded-2xl border border-white/10 bg-[#050507]/35 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] md:w-64">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-zinc-500">Monthly context</p>
                            <div class="mt-4 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-zinc-400">Income</span>
                                    <span class="font-semibold text-emerald-300">+ {{ rupiah($summary['totalIncome']) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm text-zinc-400">Expense</span>
                                    <span class="font-semibold text-rose-300">- {{ rupiah($summary['totalExpense']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Income</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-emerald-300">{{ rupiah($summary['totalIncome']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Expense</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-rose-300">{{ rupiah($summary['totalExpense']) }}</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Balance feel</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-cyan-100">{{ $budgetState }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-5">
                <x-ui.app-card>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Budget Status</p>
                            <p class="mt-3 text-2xl font-semibold tracking-tight text-white">Budget Overview</p>
                        </div>
                        <span class="rounded-full border px-3 py-1 text-xs font-medium {{ $budgetToneClasses }}">{{ $budgetState }}</span>
                    </div>

                    @if ($budgetOverview['totalBudget'] > 0)
                        <div class="mt-6 space-y-4">
                            <x-ui.progress-bar :value="$budgetOverview['usagePercentage']" :tone="$budgetTone" height="h-3" />
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-zinc-500">Used</p>
                                    <p class="mt-1 font-semibold text-rose-300">{{ rupiah($budgetOverview['totalUsed']) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-zinc-500">Remaining</p>
                                    <p class="mt-1 font-semibold text-cyan-100">{{ rupiah($budgetOverview['totalRemaining']) }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-zinc-500">{{ $budgetOverview['usagePercentage'] }}% used from {{ rupiah($budgetOverview['totalBudget']) }}.</p>
                        </div>
                    @else
                        <div class="mt-5">
                            <x-ui.empty-state
                                title="No budget set for this month."
                                description="Set one simple limit so the dashboard can show how much breathing room you still have."
                                action-label="Set budget"
                                :action-url="route('budgets.index')"
                            />
                        </div>
                    @endif
                </x-ui.app-card>

                <x-ui.app-card>
                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Savings Goals</p>
                    <p class="mt-3 text-2xl font-semibold tracking-tight text-white">Savings Goals Progress</p>
                    <p class="mt-2 text-sm leading-6 text-zinc-400">Personal targets, visible without pressure.</p>
                </x-ui.app-card>
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-[0.95fr_1.05fr]">
            <x-ui.dashboard-widget
                title="Savings Goals"
                description="Progress that feels personal and easy to return to."
            >
                <div class="space-y-3 px-5 pb-5 pt-4 md:px-6">
                    @forelse ($savingsGoalsOverview as $row)
                        <x-ui.progress-card
                            :title="$row['goal']->title"
                            :meta="rupiah($row['remaining']).' remaining'"
                            :value="$row['barPercentage']"
                            tone="accent"
                            :caption="$row['estimatedCompletion'] ? 'Target feel: '.$row['estimatedCompletion'] : 'Keep the next small deposit visible.'"
                        />
                    @empty
                        <x-ui.empty-state
                            title="No savings goals yet."
                            description="Create one goal so progress feels personal, visible, and easier to return to."
                            action-label="Create goal"
                            :action-url="route('savings-goals.index')"
                        />
                    @endforelse
                </div>
            </x-ui.dashboard-widget>

            <x-ui.dashboard-widget
                title="Behavioral Reflection"
                description="A simple, supportive layer for mood and spending awareness."
            >
                <div class="grid gap-4 px-5 pb-5 pt-4 md:px-6">
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Today's Mood</p>
                        @if ($todayMood)
                            <p class="mt-3 text-2xl font-semibold tracking-tight text-white">{{ $todayMood->mood->label() }}</p>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $todayMood->mood->reflection() }}</p>
                            @if ($todayMood->note)
                                <p class="mt-3 rounded-xl border border-cyan-300/10 bg-cyan-300/[0.05] px-3 py-2 text-sm text-cyan-100">{{ $todayMood->note }}</p>
                            @endif
                        @else
                            <p class="mt-3 text-2xl font-semibold tracking-tight text-white">No mood yet</p>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">A small reflection for today can add context to your money patterns.</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Simple Insight</p>
                        <div class="mt-3 space-y-2">
                            @foreach ($behaviorInsights as $insight)
                                <p class="text-sm leading-6 text-zinc-300">{{ $insight }}</p>
                            @endforeach
                        </div>
                    </div>

                    <x-ui.button variant="secondary" :href="route('mood-tracker.index')" class="w-fit">
                        Open Mood Tracker
                    </x-ui.button>
                </div>
            </x-ui.dashboard-widget>
        </section>

        <section>
            <x-ui.dashboard-widget
                title="Growth Progress"
                description="A light, supportive layer for consistency without pressure."
            >
                <div class="grid gap-4 px-5 pb-5 pt-4 md:grid-cols-[0.9fr_1.1fr] md:px-6">
                    <div class="rounded-2xl border border-cyan-300/10 bg-gradient-to-br from-cyan-300/[0.10] via-white/[0.035] to-white/[0.015] p-5 shadow-[0_24px_90px_rgba(0,0,0,0.30)]">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Current Level</p>
                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div>
                                <p class="text-5xl font-semibold tracking-[-0.045em] text-white">Level {{ $growthProgress['currentLevel'] }}</p>
                                <p class="mt-2 text-sm text-zinc-400">{{ $growthProgress['totalXp'] }} XP total</p>
                            </div>
                            <div class="rounded-full border border-white/10 bg-white/[0.05] px-3 py-1 text-xs font-medium text-cyan-100">
                                {{ $growthProgress['xpToNextLevel'] }} XP next
                            </div>
                        </div>
                        <div class="mt-5 space-y-2">
                            <x-ui.progress-bar :value="$growthProgress['levelProgressPercentage']" tone="accent" height="h-3" />
                            <p class="text-xs text-zinc-500">{{ $growthProgress['levelProgressPercentage'] }}% toward the next level.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5 transition duration-200 hover:bg-white/[0.05]">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-zinc-500">Daily Streak</p>
                            <p class="mt-4 text-3xl font-semibold tracking-tight text-white">{{ $growthProgress['dailyTrackingStreak'] }} day{{ $growthProgress['dailyTrackingStreak'] === 1 ? '' : 's' }}</p>
                            <p class="mt-2 text-sm leading-6 text-zinc-400">Based on a transaction or mood reflection today.</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-5 transition duration-200 hover:bg-white/[0.05]">
                            <p class="text-xs font-medium uppercase tracking-[0.2em] text-zinc-500">Latest Achievement</p>
                            @if ($growthProgress['latestAchievement'])
                                <p class="mt-4 text-xl font-semibold tracking-tight text-white">{{ $growthProgress['latestAchievement']->title }}</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $growthProgress['latestAchievement']->description }}</p>
                            @else
                                <p class="mt-4 text-xl font-semibold tracking-tight text-white">No achievement yet</p>
                                <p class="mt-2 text-sm leading-6 text-zinc-400">Log one small action to start your progress gently.</p>
                            @endif
                            <x-ui.button variant="secondary" :href="route('achievements.index')" class="mt-4 w-fit">
                                View achievements
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.dashboard-widget>
        </section>

        <section>
            <x-ui.dashboard-widget
                title="Recent Transactions"
                description="Modern activity rows without heavy separators."
            >
                <div class="space-y-2 px-5 pb-5 pt-4 md:px-6">
                    @forelse ($summary['recentTransactions'] as $transaction)
                        <div class="flex flex-col gap-3 rounded-xl px-3 py-3 transition duration-200 hover:bg-white/[0.04] md:flex-row md:items-center md:justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/[0.05] text-sm font-semibold text-zinc-300">
                                    {{ strtoupper(substr($transaction->category?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-white">{{ $transaction->category?->name ?? 'Uncategorized' }}</p>
                                    <p class="mt-1 text-sm text-zinc-500">{{ $transaction->transaction_date->format('d M Y') }} | {{ strtoupper($transaction->type->value) }}</p>
                                    @if ($transaction->description)
                                        <p class="mt-1 text-sm text-zinc-400">{{ $transaction->description }}</p>
                                    @endif
                                    @if ($transaction->behavior_note)
                                        <p class="mt-2 rounded-xl border border-cyan-300/10 bg-cyan-300/[0.05] px-3 py-2 text-xs text-cyan-100">Reflection: {{ $transaction->behavior_note }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="{{ $transaction->type->value === 'income' ? 'text-emerald-300' : 'text-rose-300' }} text-base font-semibold md:text-right">
                                {{ $transaction->type->value === 'income' ? '+ ' : '- ' }}{{ rupiah($transaction->amount) }}
                            </p>
                        </div>
                    @empty
                        <x-ui.empty-state
                            title="Belum ada transaksi bulan ini."
                            description="Mulai dari transaksi pertama untuk melihat ringkasan keuangan Anda dengan lebih jelas."
                            action-label="Add transaction"
                            :action-url="route('transactions.create')"
                        />
                    @endforelse
                </div>
            </x-ui.dashboard-widget>
        </section>
    </div>
</x-layouts.app>
