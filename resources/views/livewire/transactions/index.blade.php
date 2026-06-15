<?php

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    #[Computed]
    public function transactions()
    {
        return Transaction::query()
            ->where('user_id', Auth::id())
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function delete(int $transactionId, TransactionService $transactionService): void
    {
        $transaction = Transaction::query()->findOrFail($transactionId);
        $transactionService->deleteTransaction(Auth::user(), $transaction);
    }
}; ?>

<div class="space-y-8">
    <x-ui.app-card class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <x-ui.section-header
            title="Transactions"
            eyebrow="Money Movement"
            description="Track income and expenses with a clean monthly rhythm."
        />

        <x-ui.button :href="route('transactions.create')">Add Transaction</x-ui.button>
    </x-ui.app-card>

    <x-ui.dashboard-widget>
        <div class="space-y-2 p-5 md:p-6">
            @forelse ($this->transactions as $transaction)
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

                    <div class="flex flex-wrap items-center gap-3 md:justify-end">
                        <p class="{{ $transaction->type->value === 'income' ? 'text-emerald-300' : 'text-rose-300' }} font-semibold">
                            {{ $transaction->type->value === 'income' ? '+ ' : '- ' }}{{ rupiah($transaction->amount) }}
                        </p>
                        <x-ui.button variant="secondary" :href="route('transactions.edit', $transaction)" class="px-3 py-2">Edit</x-ui.button>
                        <x-ui.button variant="danger" wire:click="delete({{ $transaction->id }})" wire:confirm="Delete this transaction?" class="px-3 py-2">Delete</x-ui.button>
                    </div>
                </div>
            @empty
                <x-ui.empty-state
                    title="Belum ada transaksi."
                    description="Tambahkan income atau expense pertama Anda agar dashboard mulai terasa hidup dan berguna."
                    action-label="Add Transaction"
                    :action-url="route('transactions.create')"
                />
            @endforelse
        </div>

        <div class="border-t border-white/10 px-5 py-4 md:px-6">
            {{ $this->transactions->links() }}
        </div>
    </x-ui.dashboard-widget>
</div>
