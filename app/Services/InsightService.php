<?php

namespace App\Services;

use App\Enums\Mood;
use App\Enums\TransactionType;
use App\Models\MoodLog;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class InsightService
{
    public function __construct(
        private readonly BudgetService $budgetService,
    ) {}

    /**
     * @return array<int,string>
     */
    public function getSimpleInsightsForUser(User $user): array
    {
        $currentMonth = now();
        $startOfMonth = $currentMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $currentMonth->copy()->endOfMonth()->toDateString();

        $transactionCount = Transaction::query()
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->count();

        if ($transactionCount === 0) {
            return [
                'Mulai catat transaksi agar insight personal bisa muncul.',
            ];
        }

        $insights = [];
        $budgetOverview = $this->budgetService->getBudgetOverviewForUser($user);

        if ($budgetOverview['totalBudget'] > 0 && $budgetOverview['usagePercentage'] >= 75) {
            $insights[] = 'Pengeluaran bulan ini mulai mendekati batas. Coba cek kategori yang paling sering dipakai.';
        }

        $stressedMoodDates = MoodLog::query()
            ->where('user_id', $user->id)
            ->where('mood', Mood::Stressed->value)
            ->whereBetween('logged_date', [$startOfMonth, $endOfMonth])
            ->pluck('logged_date')
            ->map(fn ($date): string => $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString())
            ->all();

        if ($stressedMoodDates !== []) {
            $stressExpenseCount = Transaction::query()
                ->where('user_id', $user->id)
                ->where('type', TransactionType::Expense->value)
                ->whereIn('transaction_date', $stressedMoodDates)
                ->count();

            if ($stressExpenseCount > 0) {
                $insights[] = 'Ada beberapa pengeluaran yang terjadi saat mood stressed. Ini bisa jadi pola yang menarik untuk diperhatikan.';
            }
        }

        if ($insights === []) {
            $insights[] = 'Notice how your mood and spending move together. No pressure, just awareness.';
        }

        return $insights;
    }
}
