<?php

use App\Enums\Mood;
use App\Services\MoodService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $mood = 'neutral';
    public ?string $note = null;
    public string $logged_date = '';

    public function mount(MoodService $moodService): void
    {
        $this->logged_date = now()->toDateString();

        $todayMood = $moodService->getTodayMood(Auth::user());

        if ($todayMood) {
            $this->mood = $todayMood->mood->value;
            $this->note = $todayMood->note;
        }
    }

    #[Computed]
    public function moodOptions(): array
    {
        return app(MoodService::class)->moodOptions();
    }

    #[Computed]
    public function recentMoodLogs()
    {
        return app(MoodService::class)->getRecentMoodLogs(Auth::user(), 10);
    }

    #[Computed]
    public function moodSummary(): array
    {
        return app(MoodService::class)->getMoodSummaryForCurrentMonth(Auth::user());
    }

    public function selectMood(string $mood): void
    {
        $this->mood = $mood;
    }

    public function save(MoodService $moodService): void
    {
        $validated = $this->validate([
            'mood' => ['required', new Enum(Mood::class)],
            'note' => ['nullable', 'string'],
            'logged_date' => ['required', 'date'],
        ]);

        $moodService->createOrUpdateDailyMood(Auth::user(), $validated);
    }
}; ?>

<div class="space-y-8">
    <x-ui.app-card class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <x-ui.section-header
            title="Mood Tracker"
            eyebrow="Behavioral Layer"
            description="Notice how your mood and spending move together. No pressure, just awareness."
        />

        <div class="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm text-zinc-400">
            {{ $this->moodSummary['total'] }} reflections this month
        </div>
    </x-ui.app-card>

    <div class="grid gap-5 lg:grid-cols-[0.95fr_1.05fr]">
        <form wire:submit="save" class="rounded-2xl border border-white/10 bg-white/[0.035] px-5 py-5 shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur md:px-6">
            <x-ui.section-header
                title="A small reflection for today."
                description="Choose the mood that feels closest. This stays lightweight and personal."
            />

            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                @foreach ($this->moodOptions as $option)
                    <button
                        type="button"
                        wire:click="selectMood('{{ $option['value'] }}')"
                        class="rounded-2xl border px-4 py-4 text-left transition duration-200 hover:bg-white/[0.06] {{ $mood === $option['value'] ? 'border-cyan-300/30 bg-cyan-300/[0.10] shadow-[0_0_34px_rgba(103,232,249,0.08)]' : 'border-white/10 bg-white/[0.025]' }}"
                    >
                        <p class="font-semibold text-white">{{ $option['label'] }}</p>
                        <p class="mt-2 text-xs leading-5 text-zinc-500">{{ $option['reflection'] }}</p>
                    </button>
                @endforeach
            </div>

            <div class="mt-5 grid gap-4">
                <flux:input wire:model="logged_date" label="Date" type="date" required />
                <flux:textarea wire:model="note" label="Note (optional)" rows="4" placeholder="What might have shaped your mood or spending today?" />
            </div>

            <div class="mt-5">
                <x-ui.button type="submit">Save reflection</x-ui.button>
            </div>
        </form>

        <x-ui.dashboard-widget title="Recent mood history" description="A soft timeline of recent reflections.">
            <div class="space-y-2 px-5 pb-5 pt-4 md:px-6">
                @forelse ($this->recentMoodLogs as $log)
                    <div class="rounded-xl px-3 py-3 transition duration-200 hover:bg-white/[0.04]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-white">{{ $log->mood->label() }}</p>
                                <p class="mt-1 text-sm text-zinc-500">{{ $log->logged_date->format('d M Y') }}</p>
                                @if ($log->note)
                                    <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $log->note }}</p>
                                @endif
                            </div>
                            <span class="rounded-full border border-cyan-300/15 bg-cyan-300/[0.08] px-3 py-1 text-xs font-medium text-cyan-200">
                                Reflection
                            </span>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        title="No mood reflections yet."
                        description="Start with today's mood. Small reflections can make spending patterns easier to notice."
                    />
                @endforelse
            </div>
        </x-ui.dashboard-widget>
    </div>
</div>
