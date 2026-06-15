<?php

namespace App\Services;

use App\Enums\Mood;
use App\Enums\TransactionType;
use App\Models\MoodLog;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function __construct(
        private readonly BudgetService $budgetService,
        private readonly SavingsGoalService $savingsGoalService,
    ) {}

    /**
     * @return array{
     *     month:string,
     *     totalIncome:float,
     *     totalExpense:float,
     *     balance:float,
     *     transactionCount:int,
     *     averageDailyExpense:float,
     *     biggestExpenseCategory:?array{name:string,total:float,percentage:float}
     * }
     */
    public function getMonthlySummary(User $user, ?Carbon $month = null): array
    {
        $period = $this->period($month);
        $transactions = $this->transactionsForMonth($user, $period)->get();
        $expenseTransactions = $transactions->where('type', TransactionType::Expense);
        $totalIncome = (float) $transactions->where('type', TransactionType::Income)->sum('amount');
        $totalExpense = (float) $expenseTransactions->sum('amount');

        return [
            'month' => $period->format('Y-m'),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'transactionCount' => $transactions->count(),
            'averageDailyExpense' => round($totalExpense / max(1, $period->daysInMonth), 2),
            'biggestExpenseCategory' => $this->getCategorySpending($user, $period)[0] ?? null,
        ];
    }

    /**
     * @return array<int,array{name:string,total:float,percentage:float,transactionCount:int}>
     */
    public function getCategorySpending(User $user, ?Carbon $month = null): array
    {
        $period = $this->period($month);
        $expenses = $this->transactionsForMonth($user, $period)
            ->where('type', TransactionType::Expense->value)
            ->with('category')
            ->get();

        $totalExpense = (float) $expenses->sum('amount');

        if ($totalExpense <= 0) {
            return [];
        }

        return $expenses
            ->groupBy(fn (Transaction $transaction): string => (string) ($transaction->category?->name ?? 'Uncategorized'))
            ->map(fn (Collection $transactions, string $categoryName): array => [
                'name' => $categoryName,
                'total' => (float) $transactions->sum('amount'),
                'percentage' => round(((float) $transactions->sum('amount') / $totalExpense) * 100, 1),
                'transactionCount' => $transactions->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     month:string,
     *     totalBudget:float,
     *     totalUsed:float,
     *     totalRemaining:float,
     *     usagePercentage:float,
     *     status:string,
     *     budgets:array<int,array{budget:mixed,used:float,remaining:float,usagePercentage:float,barPercentage:float}>
     * }
     */
    public function getBudgetPerformance(User $user, ?Carbon $month = null): array
    {
        $period = $this->period($month);
        $overview = $this->budgetService->getBudgetOverviewForUser($user, $period->format('Y-m'));
        $usagePercentage = (float) $overview['usagePercentage'];

        return [
            ...$overview,
            'status' => match (true) {
                $usagePercentage > 100 => 'exceeded',
                $usagePercentage >= 75 => 'warning',
                default => 'safe',
            },
        ];
    }

    /**
     * @return array{
     *     activeGoalsCount:int,
     *     totalTargetAmount:float,
     *     totalSavedAmount:float,
     *     overallProgressPercentage:float,
     *     closestGoals:array<int,array{goal:SavingsGoal,progressPercentage:float,barPercentage:float,remaining:float,estimatedCompletion:?string}>
     * }
     */
    public function getSavingsProgress(User $user): array
    {
        $goals = SavingsGoal::query()
            ->where('user_id', $user->id)
            ->get();

        $totalTarget = (float) $goals->sum('target_amount');
        $totalSaved = (float) $goals->sum('current_amount');
        $overview = $this->savingsGoalService->getGoalsOverviewForUser($user, 50);
        $closestGoals = collect($overview)
            ->sortByDesc('progressPercentage')
            ->take(3)
            ->values()
            ->all();

        return [
            'activeGoalsCount' => $goals->count(),
            'totalTargetAmount' => $totalTarget,
            'totalSavedAmount' => $totalSaved,
            'overallProgressPercentage' => $totalTarget > 0 ? round(($totalSaved / $totalTarget) * 100, 1) : 0.0,
            'closestGoals' => $closestGoals,
        ];
    }

    /**
     * @return array{
     *     hasEnoughData:bool,
     *     mostCommonMood:?string,
     *     moodWithHighestExpense:?string,
     *     totalExpenseOnMoodDays:float,
     *     rows:array<int,array{mood:string,count:int,totalExpense:float,percentage:float}>,
     *     sentence:string
     * }
     */
    public function getMoodSpendingCorrelation(User $user, ?Carbon $month = null): array
    {
        $period = $this->period($month);
        $moodLogs = MoodLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('logged_date', [$period->copy()->startOfMonth()->toDateString(), $period->copy()->endOfMonth()->toDateString()])
            ->get();

        if ($moodLogs->isEmpty()) {
            return $this->emptyMoodCorrelation();
        }

        $moodDates = $moodLogs
            ->map(fn (MoodLog $moodLog): string => $moodLog->logged_date->toDateString())
            ->unique()
            ->values()
            ->all();

        $moodDateLookup = collect($moodDates)->flip();

        $expenses = $this->transactionsForMonth($user, $period)
            ->where('type', TransactionType::Expense->value)
            ->get();
        $expenses = $expenses
            ->filter(fn (Transaction $transaction): bool => $moodDateLookup->has($transaction->transaction_date->toDateString()))
            ->values();

        if ($expenses->isEmpty()) {
            return $this->emptyMoodCorrelation($this->mostCommonMoodLabel($moodLogs));
        }

        $expensesByDate = $expenses->groupBy(fn (Transaction $transaction): string => $transaction->transaction_date->toDateString());
        $totalExpense = (float) $expenses->sum('amount');

        $rows = $moodLogs
            ->groupBy(fn (MoodLog $moodLog): string => $moodLog->mood->value)
            ->map(function (Collection $logs, string $moodValue) use ($expensesByDate, $totalExpense): array {
                $moodExpense = $logs->sum(function (MoodLog $moodLog) use ($expensesByDate): float {
                    return (float) ($expensesByDate->get($moodLog->logged_date->toDateString())?->sum('amount') ?? 0);
                });

                return [
                    'mood' => Mood::from($moodValue)->label(),
                    'count' => $logs->count(),
                    'totalExpense' => (float) $moodExpense,
                    'percentage' => $totalExpense > 0 ? round(((float) $moodExpense / $totalExpense) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('totalExpense')
            ->values();

        $highestExpenseMood = $rows->first();
        $mostCommonMood = $this->mostCommonMoodLabel($moodLogs);

        return [
            'hasEnoughData' => true,
            'mostCommonMood' => $mostCommonMood,
            'moodWithHighestExpense' => $highestExpenseMood['mood'] ?? null,
            'totalExpenseOnMoodDays' => $totalExpense,
            'rows' => $rows->all(),
            'sentence' => $highestExpenseMood
                ? 'Pengeluaran paling banyak tercatat pada hari dengan mood '.$highestExpenseMood['mood'].'. Anggap ini sebagai bahan refleksi ringan, bukan kesimpulan mutlak.'
                : 'Catat mood dan transaksi beberapa hari lagi agar pola mulai terlihat.',
        ];
    }

    /**
     * @return array{
     *     month:string,
     *     hasData:bool,
     *     totalIncome:float,
     *     totalExpense:float,
     *     balance:float,
     *     transactionCount:int,
     *     biggestExpenseCategory:?array{name:string,total:float,percentage:float},
     *     budgetStatus:string,
     *     sentence:string,
     *     behaviorNotes:array<int,array{date:string,amount:float,type:string,category:string,note:string}>
     * }
     */
    public function getMonthlyReview(User $user, ?Carbon $month = null): array
    {
        $period = $this->period($month);
        $summary = $this->getMonthlySummary($user, $period);
        $budgetPerformance = $this->getBudgetPerformance($user, $period);
        $biggestCategory = $summary['biggestExpenseCategory'];

        if ($summary['transactionCount'] === 0) {
            $sentence = 'Belum ada cukup data untuk membuat review bulan ini.';
        } elseif ($biggestCategory) {
            $sentence = 'Bulan ini kamu mencatat '.$summary['transactionCount'].' transaksi dengan pengeluaran terbesar di kategori '.$biggestCategory['name'].'. No pressure, just awareness.';
        } else {
            $sentence = 'Bulan ini kamu mencatat '.$summary['transactionCount'].' transaksi. Pola akan makin jelas seiring data bertambah.';
        }

        return [
            'month' => $summary['month'],
            'hasData' => $summary['transactionCount'] > 0,
            'totalIncome' => $summary['totalIncome'],
            'totalExpense' => $summary['totalExpense'],
            'balance' => $summary['balance'],
            'transactionCount' => $summary['transactionCount'],
            'biggestExpenseCategory' => $biggestCategory,
            'budgetStatus' => $budgetPerformance['status'],
            'sentence' => $sentence,
            'behaviorNotes' => $this->getRecentBehaviorNotes($user, $period),
        ];
    }

    /**
     * @return array<int,array{date:string,amount:float,type:string,category:string,note:string}>
     */
    public function getRecentBehaviorNotes(User $user, ?Carbon $month = null, int $limit = 5): array
    {
        $period = $this->period($month);

        return $this->transactionsForMonth($user, $period)
            ->whereNotNull('behavior_note')
            ->where('behavior_note', '!=', '')
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Transaction $transaction): array => [
                'date' => $transaction->transaction_date->format('d M Y'),
                'amount' => (float) $transaction->amount,
                'type' => $transaction->type->value,
                'category' => $transaction->category?->name ?? 'Uncategorized',
                'note' => (string) $transaction->behavior_note,
            ])
            ->all();
    }

    private function transactionsForMonth(User $user, Carbon $period): Builder
    {
        return Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [
                $period->copy()->startOfMonth()->toDateString(),
                $period->copy()->endOfMonth()->toDateString(),
            ]);
    }

    private function period(?Carbon $month = null): Carbon
    {
        return ($month?->copy() ?? now())->startOfMonth();
    }

    private function mostCommonMoodLabel(Collection $moodLogs): ?string
    {
        $moodValue = $moodLogs
            ->groupBy(fn (MoodLog $moodLog): string => $moodLog->mood->value)
            ->sortByDesc(fn (Collection $logs): int => $logs->count())
            ->keys()
            ->first();

        return $moodValue ? Mood::from($moodValue)->label() : null;
    }

    /**
     * @return array{hasEnoughData:bool,mostCommonMood:?string,moodWithHighestExpense:?string,totalExpenseOnMoodDays:float,rows:array<int,array{mood:string,count:int,totalExpense:float,percentage:float}>,sentence:string}
     */
    private function emptyMoodCorrelation(?string $mostCommonMood = null): array
    {
        return [
            'hasEnoughData' => false,
            'mostCommonMood' => $mostCommonMood,
            'moodWithHighestExpense' => null,
            'totalExpenseOnMoodDays' => 0.0,
            'rows' => [],
            'sentence' => 'Catat mood dan transaksi beberapa hari lagi agar pola mulai terlihat.',
        ];
    }
}
