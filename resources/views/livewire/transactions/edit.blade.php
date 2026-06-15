<?php

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public int $transactionId;
    public string $type = '';
    public string $amount = '';
    public string $transaction_date = '';
    public ?string $description = null;
    public ?string $behavior_note = null;
    public string $category_id = '';

    public function mount(Transaction $transaction): void
    {
        abort_unless($transaction->user_id === Auth::id(), 403);

        $this->transactionId = $transaction->id;
        $this->type = $transaction->type->value;
        $this->amount = (string) $transaction->amount;
        $this->transaction_date = $transaction->transaction_date->toDateString();
        $this->description = $transaction->description;
        $this->behavior_note = $transaction->behavior_note;
        $this->category_id = (string) $transaction->category_id;
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

        $transaction = Transaction::query()->findOrFail($this->transactionId);
        $transactionService->updateTransaction(Auth::user(), $transaction, $validated);

        $this->redirectRoute('transactions.index', navigate: true);
    }
}; ?>

<x-ui.app-card>
    <x-ui.section-header
        title="Edit Transaction"
        eyebrow="Refine Record"
        description="Keep the record accurate so summaries stay trustworthy."
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

        <div class="md:col-span-2 flex gap-2">
            <x-ui.button type="submit">Update Transaction</x-ui.button>
            <x-ui.button variant="secondary" :href="route('transactions.index')">Cancel</x-ui.button>
        </div>
    </form>
</x-ui.app-card>
