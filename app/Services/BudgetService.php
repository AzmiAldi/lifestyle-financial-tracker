<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BudgetService
{
    public function __construct(private readonly GamificationService $gamificationService) {}

    /**
     * @param array{
     *     category_id?:int|string|null,
     *     amount:numeric-string|float|int,
     *     month:string
     * } $data
     */
    public function createBudget(User $user, array $data): Budget
    {
        $category = $this->resolveBudgetCategory($user, $data['category_id'] ?? null);
        $month = $this->normalizeMonth($data['month']);

        $this->ensureUniqueBudget($user, $month, $category?->id);

        $budget = DB::transaction(function () use ($user, $data, $month, $category): Budget {
            return Budget::query()->create([
                'user_id' => $user->id,
                'category_id' => $category?->id,
                'amount' => $data['amount'],
                'month' => $month,
            ]);
        });

        $this->gamificationService->awardXp($user, 10, 'create_budget', Budget::class, $budget->id);
        $this->gamificationService->checkAchievements($user);

        return $budget;
    }

    /**
     * @param array{
     *     category_id?:int|string|null,
     *     amount:numeric-string|float|int,
     *     month:string
     * } $data
     */
    public function updateBudget(User $user, Budget $budget, array $data): Budget
    {
        $this->ensureOwner($user, $budget);

        $category = $this->resolveBudgetCategory($user, $data['category_id'] ?? null);
        $month = $this->normalizeMonth($data['month']);

        $this->ensureUniqueBudget($user, $month, $category?->id, $budget);

        $budget->update([
            'category_id' => $category?->id,
            'amount' => $data['amount'],
            'month' => $month,
        ]);

        return $budget->refresh();
    }

    public function deleteBudget(User $user, Budget $budget): void
    {
        $this->ensureOwner($user, $budget);
        $budget->delete();
    }

    /**
     * @return array{
     *     month:string,
     *     totalBudget:float,
     *     totalUsed:float,
     *     totalRemaining:float,
     *     usagePercentage:float,
     *     budgets:array<int,array{budget:Budget,used:float,remaining:float,usagePercentage:float,barPercentage:float}>
     * }
     */
    public function getBudgetOverviewForUser(User $user, ?string $month = null): array
    {
        $normalizedMonth = $this->normalizeMonth($month ?? now()->format('Y-m'));

        $budgets = Budget::query()
            ->where('user_id', $user->id)
            ->where('month', $normalizedMonth)
            ->with('category')
            ->get()
            ->sort(function (Budget $firstBudget, Budget $secondBudget): int {
                $categoryComparison = ($firstBudget->category_id === null ? 0 : 1) <=> ($secondBudget->category_id === null ? 0 : 1);

                if ($categoryComparison !== 0) {
                    return $categoryComparison;
                }

                return $secondBudget->id <=> $firstBudget->id;
            })
            ->values();

        $rows = $budgets
            ->map(fn (Budget $budget): array => $this->summarizeBudget($user, $budget))
            ->values()
            ->all();

        $globalBudget = $budgets->firstWhere('category_id', null);
        $totalBudget = (float) $budgets->sum('amount');
        $totalUsed = $globalBudget
            ? $this->calculateUsedAmount($user, $normalizedMonth, null)
            : collect($rows)->sum('used');

        return [
            'month' => $normalizedMonth,
            'totalBudget' => $totalBudget,
            'totalUsed' => (float) $totalUsed,
            'totalRemaining' => $this->calculateRemainingAmount($totalBudget, (float) $totalUsed),
            'usagePercentage' => $this->calculateUsagePercentage($totalBudget, (float) $totalUsed),
            'budgets' => $rows,
        ];
    }

    public function calculateUsedAmount(User $user, string $month, ?int $categoryId): float
    {
        $period = Carbon::createFromFormat('Y-m-d', $this->normalizeMonth($month).'-01');

        return (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Expense->value)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->whereBetween('transaction_date', [
                $period->copy()->startOfMonth()->toDateString(),
                $period->copy()->endOfMonth()->toDateString(),
            ])
            ->sum('amount');
    }

    public function calculateRemainingAmount(float $amount, float $usedAmount): float
    {
        return $amount - $usedAmount;
    }

    public function calculateUsagePercentage(float $amount, float $usedAmount): float
    {
        if ($amount <= 0) {
            return 0;
        }

        return round(($usedAmount / $amount) * 100, 1);
    }

    private function summarizeBudget(User $user, Budget $budget): array
    {
        $usedAmount = $this->calculateUsedAmount($user, $budget->month, $budget->category_id);
        $usagePercentage = $this->calculateUsagePercentage((float) $budget->amount, $usedAmount);

        return [
            'budget' => $budget,
            'used' => $usedAmount,
            'remaining' => $this->calculateRemainingAmount((float) $budget->amount, $usedAmount),
            'usagePercentage' => $usagePercentage,
            'barPercentage' => min(100, $usagePercentage),
        ];
    }

    private function resolveBudgetCategory(User $user, int|string|null $categoryId): ?Category
    {
        if ($categoryId === null || $categoryId === '') {
            return null;
        }

        $category = Category::query()
            ->where('id', (int) $categoryId)
            ->where('type', TransactionType::Expense->value)
            ->visibleForUser($user)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'The selected category is invalid.',
            ]);
        }

        return $category;
    }

    private function normalizeMonth(string $month): string
    {
        return Carbon::createFromFormat('Y-m-d', $month.'-01')->format('Y-m');
    }

    private function ensureUniqueBudget(User $user, string $month, ?int $categoryId, ?Budget $ignoreBudget = null): void
    {
        $exists = Budget::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->when($categoryId === null, fn ($query) => $query->whereNull('category_id'))
            ->when($categoryId !== null, fn ($query) => $query->where('category_id', $categoryId))
            ->when($ignoreBudget, fn ($query) => $query->whereKeyNot($ignoreBudget->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'category_id' => 'A budget already exists for this category and month.',
            ]);
        }
    }

    private function ensureOwner(User $user, Budget $budget): void
    {
        if ($budget->user_id !== $user->id) {
            abort(403);
        }
    }
}
