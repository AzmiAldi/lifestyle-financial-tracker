<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(private readonly GamificationService $gamificationService) {}

    /**
     * @param array{
     *     category_id:int|string,
     *     type:TransactionType|string,
     *     amount:numeric-string|float|int,
     *     transaction_date:string,
     *     description?:string|null,
     *     behavior_note?:string|null
     * } $data
     */
    public function createTransaction(User $user, array $data): Transaction
    {
        $category = $this->resolveUserCategory($user, (int) $data['category_id']);

        $transaction = DB::transaction(function () use ($user, $data, $category): Transaction {
            return Transaction::query()->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'type' => $data['type'],
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'behavior_note' => $data['behavior_note'] ?? null,
            ]);
        });

        $this->gamificationService->awardXp($user, 5, 'create_transaction', Transaction::class, $transaction->id);
        $this->gamificationService->awardDailyTrackingXp($user, $transaction->transaction_date);
        $this->gamificationService->checkAchievements($user);

        return $transaction;
    }

    /**
     * @param array{
     *     category_id:int|string,
     *     type:TransactionType|string,
     *     amount:numeric-string|float|int,
     *     transaction_date:string,
     *     description?:string|null,
     *     behavior_note?:string|null
     * } $data
     */
    public function updateTransaction(User $user, Transaction $transaction, array $data): Transaction
    {
        $this->ensureOwner($user, $transaction);
        $category = $this->resolveUserCategory($user, (int) $data['category_id']);

        $transaction->update([
            'category_id' => $category->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'] ?? null,
            'behavior_note' => $data['behavior_note'] ?? null,
        ]);

        return $transaction->refresh();
    }

    public function deleteTransaction(User $user, Transaction $transaction): void
    {
        $this->ensureOwner($user, $transaction);
        $transaction->delete();
    }

    /**
     * @return array{
     *     totalIncome:float,
     *     totalExpense:float,
     *     balance:float,
     *     recentTransactions:Collection<int,Transaction>
     * }
     */
    public function getDashboardSummaryForUser(User $user, ?Carbon $month = null): array
    {
        $period = $month?->copy() ?? now();
        $startOfMonth = $period->copy()->startOfMonth()->toDateString();
        $endOfMonth = $period->copy()->endOfMonth()->toDateString();

        $baseQuery = Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);

        $totalIncome = (clone $baseQuery)
            ->where('type', TransactionType::Income->value)
            ->sum('amount');

        $totalExpense = (clone $baseQuery)
            ->where('type', TransactionType::Expense->value)
            ->sum('amount');

        $recentTransactions = (clone $baseQuery)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'totalIncome' => (float) $totalIncome,
            'totalExpense' => (float) $totalExpense,
            'balance' => (float) $totalIncome - (float) $totalExpense,
            'recentTransactions' => $recentTransactions,
        ];
    }

    public function resolveUserCategory(User $user, int $categoryId): Category
    {
        $category = Category::query()
            ->where('id', $categoryId)
            ->visibleForUser($user)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'The selected category is invalid.',
            ]);
        }

        return $category;
    }

    private function ensureOwner(User $user, Transaction $transaction): void
    {
        if ($transaction->user_id !== $user->id) {
            abort(403);
        }
    }
}
