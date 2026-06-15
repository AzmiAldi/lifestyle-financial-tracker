<?php

use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    #[Computed]
    public function progress(): array
    {
        return app(GamificationService::class)->getUserProgress(Auth::user());
    }
}; ?>

<div class="space-y-8">
    @php
        $unlockedIds = $this->progress['unlockedAchievements']->pluck('id')->all();
    @endphp

    <x-ui.app-card class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-cyan-300/10 blur-3xl"></div>
        <div class="relative flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <x-ui.section-header
                title="Achievements"
                eyebrow="Subtle Progress"
                description="Small milestones for consistency, awareness, and calmer money habits. No pressure, just a visible trail."
            />
            <div class="rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-3 shadow-[0_18px_60px_rgba(0,0,0,0.24)]">
                <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Current level</p>
                <p class="mt-1 text-2xl font-semibold tracking-tight text-white">Level {{ $this->progress['currentLevel'] }}</p>
            </div>
        </div>
    </x-ui.app-card>

    <section class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
        <x-ui.app-card>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">XP Progress</p>
            <div class="mt-4 flex items-end justify-between gap-4">
                <div>
                    <p class="text-5xl font-semibold tracking-[-0.045em] text-white">{{ $this->progress['totalXp'] }} XP</p>
                    <p class="mt-2 text-sm text-zinc-400">{{ $this->progress['xpToNextLevel'] }} XP to next level</p>
                </div>
                <span class="rounded-full border border-cyan-300/15 bg-cyan-300/[0.08] px-3 py-1 text-xs font-medium text-cyan-200">
                    {{ $this->progress['levelProgressPercentage'] }}%
                </span>
            </div>
            <div class="mt-6 space-y-2">
                <x-ui.progress-bar :value="$this->progress['levelProgressPercentage']" tone="accent" height="h-3" />
                <p class="text-xs leading-5 text-zinc-500">Progress comes from useful actions: tracking transactions, setting budgets, creating goals, and reflecting on mood.</p>
            </div>
        </x-ui.app-card>

        <x-ui.app-card>
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">Daily Rhythm</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                    <p class="text-sm text-zinc-400">Tracking streak</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-white">{{ $this->progress['dailyTrackingStreak'] }} day{{ $this->progress['dailyTrackingStreak'] === 1 ? '' : 's' }}</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4">
                    <p class="text-sm text-zinc-400">Unlocked</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-white">{{ $this->progress['unlockedAchievements']->count() }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm leading-6 text-zinc-400">A transaction or mood reflection today keeps the rhythm alive. Tiny inputs, clearer awareness.</p>
        </x-ui.app-card>
    </section>

    @if ($this->progress['unlockedAchievements']->isEmpty())
        <x-ui.empty-state
            title="No achievements unlocked yet."
            description="Create your first transaction, budget, savings goal, or mood reflection to start a calm progress trail."
            action-label="Add transaction"
            :action-url="route('transactions.create')"
        />
    @endif

    <section>
        <x-ui.dashboard-widget
            title="Milestone Library"
            description="Unlocked milestones are personal to your account. Locked ones stay quiet until the habit appears."
        >
            <div class="grid gap-4 px-5 pb-5 pt-4 md:grid-cols-2 xl:grid-cols-3 md:px-6">
                @foreach ($this->progress['allAchievements'] as $achievement)
                    @php
                        $isUnlocked = in_array($achievement->id, $unlockedIds, true);
                        $unlockedAchievement = $this->progress['unlockedAchievements']->firstWhere('id', $achievement->id);
                    @endphp

                    <div class="rounded-2xl border p-5 transition duration-200 {{ $isUnlocked ? 'border-cyan-300/20 bg-cyan-300/[0.07] shadow-[0_0_50px_rgba(103,232,249,0.08)]' : 'border-white/10 bg-white/[0.025] hover:bg-white/[0.05]' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full border border-white/10 bg-white/[0.05] text-sm font-semibold {{ $isUnlocked ? 'text-cyan-100' : 'text-zinc-500' }}">
                                {{ strtoupper(substr($achievement->title, 0, 1)) }}
                            </div>
                            <span class="rounded-full border px-2.5 py-1 text-[11px] font-medium {{ $isUnlocked ? 'border-cyan-300/15 bg-cyan-300/[0.08] text-cyan-200' : 'border-white/10 bg-white/[0.04] text-zinc-500' }}">
                                {{ $isUnlocked ? 'Unlocked' : 'Locked' }}
                            </span>
                        </div>

                        <p class="mt-5 text-lg font-semibold tracking-tight text-white">{{ $achievement->title }}</p>
                        <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $achievement->description }}</p>

                        @if ($isUnlocked)
                            <p class="mt-4 text-xs text-cyan-200/80">Unlocked {{ \Carbon\Carbon::parse($unlockedAchievement->pivot->unlocked_at)->format('d M Y') }}</p>
                        @else
                            <p class="mt-4 text-xs text-zinc-500">Keep building the habit gently.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-ui.dashboard-widget>
    </section>
</div>
