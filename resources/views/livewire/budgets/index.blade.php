<?php

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public ?int $editingBudgetId = null;
    public string $category_id = '';
    public string $amount = '';
    public string $month = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->visibleForUser(Auth::user())
            ->where('type', TransactionType::Expense->value)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function overview(): array
    {
        return app(BudgetService::class)->getBudgetOverviewForUser(Auth::user(), $this->month);
    }

    public function save(BudgetService $budgetService): void
    {
        $validated = $this->validate([
            'category_id' => ['nullable', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'month' => ['required', 'date_format:Y-m'],
        ]);

        if ($this->editingBudgetId) {
            $budget = Budget::query()->findOrFail($this->editingBudgetId);
            $budgetService->updateBudget(Auth::user(), $budget, $validated);
        } else {
            $budgetService->createBudget(Auth::user(), $validated);
        }

        $this->resetForm();
    }

    public function edit(int $budgetId): void
    {
        $budget = Budget::query()->where('user_id', Auth::id())->findOrFail($budgetId);

        $this->editingBudgetId = $budget->id;
        $this->category_id = $budget->category_id ? (string) $budget->category_id : '';
        $this->amount = (string) $budget->amount;
        $this->month = $budget->month;
    }

    public function delete(int $budgetId, BudgetService $budgetService): void
    {
        $budget = Budget::query()->findOrFail($budgetId);
        $budgetService->deleteBudget(Auth::user(), $budget);

        if ($this->editingBudgetId === $budgetId) {
            $this->resetForm();
        }
    }

    public function resetForm(): void
    {
        $this->editingBudgetId = null;
        $this->category_id = '';
        $this->amount = '';
        $this->month = now()->format('Y-m');
    }
}; ?>

<div class="space-y-8">
    <x-ui.app-card class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <x-ui.section-header
            title="Monthly Budgets"
            eyebrow="Budget System"
            description="Keep monthly spending limits visible, lightweight, and easy to adjust."
        />
        <div class="rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 text-sm text-zinc-400">{{ $this->overview['month'] }}</div>
    </x-ui.app-card>

    <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr]">
        <form wire:submit="save" class="rounded-2xl border border-white/10 bg-white/[0.035] px-5 py-5 shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur md:px-6">
            <h2 class="text-xl font-semibold tracking-tight text-white">{{ $editingBudgetId ? 'Edit Budget' : 'Create Budget' }}</h2>
            <p class="mt-2 text-sm text-zinc-400">One monthly guardrail is enough to make spending clearer.</p>

            <div class="mt-4 space-y-4">
                <flux:input wire:model="month" label="Month" type="month" required />

                <flux:select wire:model="category_id" label="Category">
                    <option value="">Global monthly budget</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="amount" label="Amount" type="number" step="0.01" min="0.01" required />
                <p class="-mt-2 rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-zinc-400">Preview: <span class="font-semibold text-cyan-100">{{ rupiah($amount ?: 0) }}</span></p>
            </div>

            <div class="mt-5 flex gap-2">
                <x-ui.button type="submit">{{ $editingBudgetId ? 'Update Budget' : 'Save Budget' }}</x-ui.button>
                @if ($editingBudgetId)
                    <x-ui.button variant="secondary" type="button" wire:click="resetForm">Cancel</x-ui.button>
                @endif
            </div>
        </form>

        <x-ui.dashboard-widget>
            <div class="grid gap-4 px-5 py-5 md:grid-cols-3">
                <x-ui.metric-card label="Budgeted" value="{{ rupiah($this->overview['totalBudget']) }}" tone="neutral" />
                <x-ui.metric-card label="Used" value="{{ rupiah($this->overview['totalUsed']) }}" tone="negative" />
                <x-ui.metric-card label="Remaining" value="{{ rupiah($this->overview['totalRemaining']) }}" tone="accent" />
            </div>

            <div class="space-y-3 px-5 pb-5 md:px-6">
                @forelse ($this->overview['budgets'] as $row)
                    @php
                        $budgetTone = match (true) {
                            $row['usagePercentage'] >= 100 => 'danger',
                            $row['usagePercentage'] >= 75 => 'warning',
                            default => 'safe',
                        };

                        $statusLabel = match ($budgetTone) {
                            'danger' => 'Exceeded',
                            'warning' => 'Warning',
                            default => 'Safe',
                        };
                    @endphp

                    <div class="rounded-2xl border border-white/10 bg-white/[0.025] p-4 transition duration-200 hover:bg-white/[0.05]">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-medium text-white">{{ $row['budget']->category?->name ?? 'Global monthly budget' }}</p>
                                    <span class="rounded-full border px-2 py-0.5 text-[11px] font-medium {{ $budgetTone === 'danger' ? 'border-rose-300/15 bg-rose-300/[0.08] text-rose-200' : ($budgetTone === 'warning' ? 'border-amber-300/15 bg-amber-300/[0.08] text-amber-200' : 'border-emerald-300/15 bg-emerald-300/[0.08] text-emerald-200') }}">{{ $statusLabel }}</span>
                                </div>
                                <p class="mt-1 text-sm text-zinc-400">Used {{ rupiah($row['used']) }} of {{ rupiah($row['budget']->amount) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.button variant="secondary" wire:click="edit({{ $row['budget']->id }})" class="px-3 py-2">Edit</x-ui.button>
                                <x-ui.button variant="danger" wire:click="delete({{ $row['budget']->id }})" wire:confirm="Delete this budget?" class="px-3 py-2">Delete</x-ui.button>
                            </div>
                        </div>

                        <x-ui.progress-bar class="mt-4" :value="$row['barPercentage']" :tone="$budgetTone" />

                        <div class="mt-2 flex flex-col justify-between gap-1 text-xs text-zinc-500 sm:flex-row">
                            <p>{{ $row['usagePercentage'] }}% used</p>
                            <p>{{ rupiah($row['remaining']) }} remaining</p>
                        </div>
                    </div>
                @empty
                    <x-ui.empty-state
                        title="No budgets for this month yet."
                        description="Create a global budget or a category budget so spending limits feel visible before the month gets noisy."
                        action-label="Create budget"
                        :action-url="route('budgets.index')"
                    />
                @endforelse
            </div>
        </x-ui.dashboard-widget>
    </div>
</div>
