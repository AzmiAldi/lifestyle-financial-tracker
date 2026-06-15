<?php

namespace App\Http\Controllers;

use App\Services\BudgetService;
use App\Services\GamificationService;
use App\Services\InsightService;
use App\Services\MoodService;
use App\Services\SavingsGoalService;
use App\Services\TransactionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        TransactionService $transactionService,
        BudgetService $budgetService,
        SavingsGoalService $savingsGoalService,
        MoodService $moodService,
        InsightService $insightService,
        GamificationService $gamificationService,
    ): View {
        $user = $request->user();

        return view('dashboard', [
            'summary' => $transactionService->getDashboardSummaryForUser($user),
            'budgetOverview' => $budgetService->getBudgetOverviewForUser($user),
            'savingsGoalsOverview' => $savingsGoalService->getGoalsOverviewForUser($user, 3),
            'todayMood' => $moodService->getTodayMood($user),
            'moodSummary' => $moodService->getMoodSummaryForCurrentMonth($user),
            'behaviorInsights' => $insightService->getSimpleInsightsForUser($user),
            'growthProgress' => $gamificationService->getUserProgress($user),
        ]);
    }
}
