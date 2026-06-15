<?php

use App\Enums\TransactionType;
use App\Models\Category;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $type = 'expense';
    public string $amount = '';
    public string $transaction_date = '';
    public ?string $description = null;
    public ?string $behavior_note = null;
    public string $category_id = '';

    public function mount(): void
    {
        $this->transaction_date = now()->toDateString();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->visibleForUser(Auth::user())
            ->where('type', $this->type)
            ->orderBy('name')
            ->get();
    }

    public function save(TransactionService $transactionService): void
    {
        $validated = $this->validate([
            'type' => ['required', new Enum(TransactionType::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'category_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'behavior_note' => ['nullable', 'string'],
        ]);

        $transactionService->createTransaction(Auth::user(), $validated);

        $this->redirectRoute('transactions.index', navigate: true);
    }
}; ?>

<x-ui.app-card>
    <x-ui.section-header
        title="Add Transaction"
        eyebrow="Quick Capture"
        description="Log the movement while it is fresh, then let the dashboard do the work."
    />

    <form wire:submit="save" class="mt-4 grid gap-4 md:grid-cols-2">
        <flux:select wire:model.live="type" label="Type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </flux:select>

        <flux:select wire:model="category_id" label="Category" required>
            <option value="">Select category</option>
            @foreach ($this->categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>

        <flux:input wire:model="amount" label="Amount" type="number" step="0.01" min="0.01" required />
        <p class="-mt-2 rounded-2xl border border-white/10 bg-white/[0.035] px-4 py-3 text-sm md:col-span-2 {{ $type === 'income' ? 'text-emerald-300' : 'text-rose-300' }}">
            Preview: {{ $type === 'income' ? '+ ' : '- ' }}{{ rupiah($amount ?: 0) }}
        </p>
        <flux:input wire:model="transaction_date" label="Transaction Date" type="date" required />
        <div class="md:col-span-2">
            <flux:textarea wire:model="description" label="Description (optional)" rows="3" />
        </div>
        <div class="md:col-span-2">
            <flux:textarea wire:model="behavior_note" label="Behavior note (optional)" rows="3" placeholder="Example: impulse purchase, after overtime, reward after a productive day" />
            <p class="mt-2 text-xs text-zinc-500">A small reflection for context. No pressure, just awareness.</p>
        </div>

        <div class="md:col-span-2">
            <x-ui.button type="submit">Save Transaction</x-ui.button>
        </div>
    </form>
</x-ui.app-card>
