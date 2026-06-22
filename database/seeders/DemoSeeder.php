<?php

namespace Database\Seeders;

use App\Enums\Mood;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\GamificationService;
use App\Services\MoodService;
use App\Services\SavingsGoalService;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seed optional demo data for portfolio presentations.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            AchievementSeeder::class,
        ]);

        $user = User::query()->updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $incomeCategory = Category::query()
            ->whereNull('user_id')
            ->where('type', TransactionType::Income->value)
            ->where('name', 'Salary')
            ->firstOrFail();

        $foodCategory = Category::query()
            ->whereNull('user_id')
            ->where('type', TransactionType::Expense->value)
            ->where('name', 'Food')
            ->firstOrFail();

        $transportCategory = Category::query()
            ->whereNull('user_id')
            ->where('type', TransactionType::Expense->value)
            ->where('name', 'Transport')
            ->firstOrFail();

        $shoppingCategory = Category::query()
            ->whereNull('user_id')
            ->where('type', TransactionType::Expense->value)
            ->where('name', 'Shopping')
            ->firstOrFail();

        if ($user->transactions()->doesntExist()) {
            $transactionService = app(TransactionService::class);

            foreach ($this->transactions($incomeCategory->id, $foodCategory->id, $transportCategory->id, $shoppingCategory->id) as $transaction) {
                $transactionService->createTransaction($user, $transaction);
            }
        }

        if ($user->budgets()->doesntExist()) {
            app(BudgetService::class)->createBudget($user, [
                'category_id' => null,
                'amount' => 4500000,
                'month' => now()->format('Y-m'),
            ]);

            app(BudgetService::class)->createBudget($user, [
                'category_id' => $foodCategory->id,
                'amount' => 1600000,
                'month' => now()->format('Y-m'),
            ]);
        }

        if ($user->savingsGoals()->doesntExist()) {
            app(SavingsGoalService::class)->createGoal($user, [
                'title' => 'Emergency Fund',
                'target_amount' => 15000000,
                'current_amount' => 6500000,
                'deadline' => now()->addMonths(6)->toDateString(),
                'description' => 'A calm safety buffer for future plans.',
            ]);

            app(SavingsGoalService::class)->createGoal($user, [
                'title' => 'Creative Workspace',
                'target_amount' => 8000000,
                'current_amount' => 2500000,
                'deadline' => now()->addMonths(9)->toDateString(),
                'description' => 'Upgrade tools without rushing the process.',
            ]);
        }

        $moodService = app(MoodService::class);

        foreach ($this->moodLogs() as $moodLog) {
            $moodService->createOrUpdateDailyMood($user, $moodLog);
        }

        app(GamificationService::class)->checkAchievements($user);
    }

    /**
     * @return array<int,array{category_id:int,type:string,amount:int,transaction_date:string,description:string,behavior_note?:string}>
     */
    private function transactions(int $incomeCategoryId, int $foodCategoryId, int $transportCategoryId, int $shoppingCategoryId): array
    {
        return [
            [
                'category_id' => $incomeCategoryId,
                'type' => TransactionType::Income->value,
                'amount' => 8500000,
                'transaction_date' => now()->startOfMonth()->addDays(1)->toDateString(),
                'description' => 'Monthly salary',
            ],
            [
                'category_id' => $foodCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 185000,
                'transaction_date' => now()->subDays(1)->toDateString(),
                'description' => 'Dinner with friends',
                'behavior_note' => 'Reward after a dense workday, still within the food budget.',
            ],
            [
                'category_id' => $transportCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 75000,
                'transaction_date' => now()->subDays(2)->toDateString(),
                'description' => 'Ride hailing',
                'behavior_note' => 'Chose convenience because the day was packed.',
            ],
            [
                'category_id' => $foodCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 420000,
                'transaction_date' => now()->subDays(4)->toDateString(),
                'description' => 'Groceries',
            ],
            [
                'category_id' => $shoppingCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 350000,
                'transaction_date' => now()->subDays(6)->toDateString(),
                'description' => 'Desk accessories',
                'behavior_note' => 'Useful purchase for the workspace, not impulse-driven.',
            ],
            [
                'category_id' => $transportCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 120000,
                'transaction_date' => now()->subDays(8)->toDateString(),
                'description' => 'Weekly transport',
            ],
            [
                'category_id' => $foodCategoryId,
                'type' => TransactionType::Expense->value,
                'amount' => 215000,
                'transaction_date' => now()->subDays(10)->toDateString(),
                'description' => 'Cafe work session',
                'behavior_note' => 'A focused environment helped, worth watching frequency.',
            ],
        ];
    }

    /**
     * @return array<int,array{mood:string,logged_date:string,note:string}>
     */
    private function moodLogs(): array
    {
        return [
            [
                'mood' => Mood::Calm->value,
                'logged_date' => now()->toDateString(),
                'note' => 'Feeling organized after reviewing the month.',
            ],
            [
                'mood' => Mood::Productive->value,
                'logged_date' => now()->subDays(1)->toDateString(),
                'note' => 'Good day for intentional choices.',
            ],
            [
                'mood' => Mood::Stressed->value,
                'logged_date' => now()->subDays(2)->toDateString(),
                'note' => 'A packed schedule made convenience spending more likely.',
            ],
            [
                'mood' => Mood::Neutral->value,
                'logged_date' => now()->subDays(4)->toDateString(),
                'note' => 'Regular spending day.',
            ],
        ];
    }
}
