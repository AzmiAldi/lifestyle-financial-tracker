<?php

use App\Models\SavingsGoal;
use App\Services\SavingsGoalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public ?int $editingGoalId = null;
    public string $title = '';
    public string $target_amount = '';
    public string $current_amount = '0';
    public ?string $deadline = null;
    public ?string $description = null;

    #[Computed]
    public function goals(): array
    {
        return app(SavingsGoalService::class)->getGoalsOverviewForUser(Auth::user(), 25);
    }

    public function save(SavingsGoalService $savingsGoalService): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'current_amount' => ['nullable', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->editingGoalId) {
            $goal = SavingsGoal::query()->findOrFail($this->editingGoalId);
            $savingsGoalService->updateGoal(Auth::user(), $goal, $validated);
        } else {
            $savingsGoalService->createGoal(Auth::user(), $validated);
        }

        $this->resetForm();
    }

    public function edit(int $goalId): void
    {
        $goal = SavingsGoal::query()->where('user_id', Auth::id())->findOrFail($goalId);

        $this->editingGoalId = $goal->id;
        $this->title = $goal->title;
        $this->target_amount = (string) $goal->target_amount;
        $this->current_amount = (string) $goal->current_amount;
        $this->deadline = $goal->deadline?->toDateString();
        $this->description = $goal->description;
    }

    public function delete(int $goalId, SavingsGoalService $savingsGoalService): void
    {
        $goal = SavingsGoal::query()->findOrFail($goalId);
        $savingsGoalService->deleteGoal(Auth::user(), $goal);

        if ($this->editingGoalId === $goalId) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->editingGoalId = null;
        $this->title = '';
        $this->target_amount = '';
        $this->current_amount = '0';
        $this->deadline = null;
        $this->description = null;
    }
}; ?>

<div class="space-y-8">
    <x-ui.app-card>
        <x-ui.section-header
            title="Savings Goals"
            eyebrow="Personal Progress"
            description="Track progress toward personal financial goals without clutter or pressure."
        />
    </x-ui.app-card>

    <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
        <form wire:submit="save" class="rounded-2xl border border-white/10 bg-white/[0.035] px-5 py-5 shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur md:px-6">
            <h2 class="text-xl font-semibold tracking-tight text-white">{{ $editingGoalId ? 'Edit Goal' : 'Create Goal' }}</h2>
            <p class="mt-2 text-sm text-zinc-400">Make one goal visible enough to return to every week.</p>

            <div class="mt-4 space-y-4">
                <flux:input wire:model="title" label="Title" required />
                <flux:input wire:model="target_amount" label="Target Amount" type="number" step="0.01" min="0.01" required />
                <flux:input wire:model="current_amount" label="Current Amount" type="number" step="0.01" min="0" />
                <div class="grid gap-1 rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-zinc-400">
                    <p>Target: {{ rupiah($target_amount ?: 0) }}</p>
                    <p>Current: {{ rupiah($current_amount ?: 0) }}</p>
                </div>
                <flux:input wire:model="deadline" label="Deadline (optional)" type="date" />
                <flux:textarea wire:model="description" label="Description (optional)" rows="3" />
            </div>

            <div class="mt-5 flex gap-2">
                <x-ui.button type="submit">{{ $editingGoalId ? 'Update Goal' : 'Save Goal' }}</x-ui.button>
                @if ($editingGoalId)
                    <x-ui.button variant="secondary" type="button" wire:click="resetForm">Cancel</x-ui.button>
                @endif
            </div>
        </form>

        <x-ui.dashboard-widget title="Goal Progress" description="Clear progress, remaining amount, and a calm sense of direction.">
            <div class="space-y-3 px-5 pb-5 pt-4 md:px-6">
                @forelse ($this->goals as $row)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4 transition duration-200 hover:bg-white/[0.05]">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-white">{{ $row['goal']->title }}</p>
                                    <span class="rounded-full border border-cyan-300/15 bg-cyan-300/[0.08] px-2 py-0.5 text-[11px] font-medium text-cyan-200">{{ $row['progressPercentage'] }}%</span>
                                </div>
                                <p class="mt-1 text-sm text-zinc-400">{{ rupiah($row['goal']->current_amount) }} of {{ rupiah($row['goal']->target_amount) }}</p>
                                @if ($row['estimatedCompletion'])
                                    <p class="mt-1 text-xs text-zinc-500">Target feel: {{ $row['estimatedCompletion'] }}</p>
                                @else
                                    <p class="mt-1 text-xs text-zinc-500">Set a deadline later when the pace feels clearer.</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="secondary" wire:click="edit({{ $row['goal']->id }})" class="px-3 py-2">Edit</x-ui.button>
                                <x-ui.button variant="danger" wire:click="delete({{ $row['goal']->id }})" wire:confirm="Delete this goal?" class="px-3 py-2">Delete</x-ui.button>
                            </div>
                        </div>

                        <x-ui.progress-bar class="mt-4" :value="$row['barPercentage']" tone="accent" />

                        <div class="mt-2 flex flex-col justify-between gap-1 text-xs text-zinc-500 sm:flex-row">
                            <p>{{ $row['progressPercentage'] }}% saved</p>
                            <p>{{ rupiah($row['remaining']) }} remaining</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        title="No savings goals yet."
                        description="Create a goal to make progress visible and give your money a more personal direction."
                        action-label="Create goal"
                        :action-url="route('savings-goals.index')"
                    />
                @endforelse
            </div>
        </x-ui.dashboard-widget>
    </div>
</div>
