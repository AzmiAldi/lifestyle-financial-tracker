<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Achievement;
use App\Models\Budget;
use App\Models\MoodLog;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\XpLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GamificationService
{
    /**
     * @var array<int,int>
     */
    private const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 1000,
    ];

    /**
     * @return array<string,array{title:string,description:string,icon:string,xp_reward:int}>
     */
    public function defaultAchievements(): array
    {
        return [
            'first_transaction' => [
                'title' => 'First Step',
                'description' => 'Catat transaksi pertamamu.',
                'icon' => 'banknotes',
                'xp_reward' => 0,
            ],
            'first_budget' => [
                'title' => 'Budget Starter',
                'description' => 'Buat budget pertamamu.',
                'icon' => 'chart-bar',
                'xp_reward' => 0,
            ],
            'first_savings_goal' => [
                'title' => 'Goal Builder',
                'description' => 'Buat target tabungan pertamamu.',
                'icon' => 'sparkles',
                'xp_reward' => 0,
            ],
            'first_mood_log' => [
                'title' => 'Self Aware',
                'description' => 'Catat mood pertamamu.',
                'icon' => 'face-smile',
                'xp_reward' => 0,
            ],
            'seven_transactions' => [
                'title' => 'Consistent Tracker',
                'description' => 'Catat 7 transaksi.',
                'icon' => 'calendar-days',
                'xp_reward' => 0,
            ],
            'positive_balance_month' => [
                'title' => 'Balanced Month',
                'description' => 'Jaga balance tetap positif bulan ini.',
                'icon' => 'scale',
                'xp_reward' => 0,
            ],
        ];
    }

    public function ensureDefaultAchievements(): void
    {
        foreach ($this->defaultAchievements() as $key => $achievement) {
            Achievement::query()->updateOrCreate(
                ['key' => $key],
                [
                    'title' => $achievement['title'],
                    'description' => $achievement['description'],
                    'icon' => $achievement['icon'],
                    'xp_reward' => $achievement['xp_reward'],
                ],
            );
        }
    }

    public function awardXp(User $user, int $amount, string $reason, ?string $sourceType = null, ?int $sourceId = null): XpLog
    {
        if ($sourceType !== null && $sourceId !== null) {
            $existingLog = XpLog::query()
                ->where('user_id', $user->id)
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->first();

            if ($existingLog) {
                return $existingLog;
            }
        }

        return XpLog::query()->create([
            'user_id' => $user->id,
            'amount' => $amount,
            'reason' => $reason,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }

    public function awardDailyTrackingXp(User $user, Carbon|string|null $date = null): ?XpLog
    {
        $trackedDate = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        if ($trackedDate !== now()->toDateString()) {
            return null;
        }

        return $this->awardXp(
            user: $user,
            amount: 10,
            reason: 'complete_daily_tracking',
            sourceType: 'daily_tracking',
            sourceId: (int) Carbon::parse($trackedDate)->format('Ymd'),
        );
    }

    public function getTotalXp(User $user): int
    {
        return (int) XpLog::query()
            ->where('user_id', $user->id)
            ->sum('amount');
    }

    public function getCurrentLevel(User $user): int
    {
        $totalXp = $this->getTotalXp($user);
        $level = 1;

        foreach (self::LEVEL_THRESHOLDS as $thresholdLevel => $thresholdXp) {
            if ($totalXp >= $thresholdXp) {
                $level = $thresholdLevel;
            }
        }

        return $level;
    }

    public function getXpToNextLevel(User $user): int
    {
        $totalXp = $this->getTotalXp($user);
        $currentLevel = $this->getCurrentLevel($user);
        $nextThreshold = self::LEVEL_THRESHOLDS[$currentLevel + 1] ?? null;

        if ($nextThreshold === null) {
            return 0;
        }

        return max(0, $nextThreshold - $totalXp);
    }

    public function getLevelProgressPercentage(User $user): float
    {
        $totalXp = $this->getTotalXp($user);
        $currentLevel = $this->getCurrentLevel($user);
        $currentThreshold = self::LEVEL_THRESHOLDS[$currentLevel];
        $nextThreshold = self::LEVEL_THRESHOLDS[$currentLevel + 1] ?? null;

        if ($nextThreshold === null) {
            return 100.0;
        }

        return round((($totalXp - $currentThreshold) / ($nextThreshold - $currentThreshold)) * 100, 1);
    }

    /**
     * @return array{
     *     totalXp:int,
     *     currentLevel:int,
     *     xpToNextLevel:int,
     *     levelProgressPercentage:float,
     *     dailyTrackingStreak:int,
     *     latestAchievement:?Achievement,
     *     unlockedAchievements:Collection<int,Achievement>,
     *     lockedAchievements:Collection<int,Achievement>,
     *     allAchievements:Collection<int,Achievement>
     * }
     */
    public function getUserProgress(User $user): array
    {
        $this->ensureDefaultAchievements();

        $unlockedAchievements = $this->getUnlockedAchievements($user);
        $unlockedAchievementIds = $unlockedAchievements->pluck('id')->all();
        $allAchievements = Achievement::query()->orderBy('id')->get();

        return [
            'totalXp' => $this->getTotalXp($user),
            'currentLevel' => $this->getCurrentLevel($user),
            'xpToNextLevel' => $this->getXpToNextLevel($user),
            'levelProgressPercentage' => $this->getLevelProgressPercentage($user),
            'dailyTrackingStreak' => $this->getDailyTrackingStreak($user),
            'latestAchievement' => $this->getLatestAchievement($user),
            'unlockedAchievements' => $unlockedAchievements,
            'lockedAchievements' => $allAchievements->reject(fn (Achievement $achievement): bool => in_array($achievement->id, $unlockedAchievementIds, true))->values(),
            'allAchievements' => $allAchievements,
        ];
    }

    /**
     * @return Collection<int,Achievement>
     */
    public function checkAchievements(User $user): Collection
    {
        $this->ensureDefaultAchievements();

        $unlocked = new Collection;

        foreach ($this->achievementConditions($user) as $achievementKey => $isUnlocked) {
            if (! $isUnlocked) {
                continue;
            }

            $achievement = Achievement::query()->where('key', $achievementKey)->first();

            if ($achievement && $this->unlockAchievement($user, $achievement)) {
                $unlocked->push($achievement);
            }
        }

        return $unlocked;
    }

    public function getDailyTrackingStreak(User $user): int
    {
        $trackedDates = Transaction::query()
            ->where('user_id', $user->id)
            ->pluck('transaction_date')
            ->merge(
                MoodLog::query()
                    ->where('user_id', $user->id)
                    ->pluck('logged_date'),
            )
            ->map(fn ($date): string => Carbon::parse($date)->toDateString())
            ->unique()
            ->flip();

        $streak = 0;
        $cursor = now()->startOfDay();

        while ($trackedDates->has($cursor->toDateString())) {
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }

    /**
     * @return array<string,bool>
     */
    private function achievementConditions(User $user): array
    {
        return [
            'first_transaction' => Transaction::query()->where('user_id', $user->id)->exists(),
            'first_budget' => Budget::query()->where('user_id', $user->id)->exists(),
            'first_savings_goal' => SavingsGoal::query()->where('user_id', $user->id)->exists(),
            'first_mood_log' => MoodLog::query()->where('user_id', $user->id)->exists(),
            'seven_transactions' => Transaction::query()->where('user_id', $user->id)->count() >= 7,
            'positive_balance_month' => $this->hasPositiveBalanceThisMonth($user),
        ];
    }

    private function hasPositiveBalanceThisMonth(User $user): bool
    {
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $income = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Income->value)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $expense = (float) Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return ($income - $expense) > 0;
    }

    private function unlockAchievement(User $user, Achievement $achievement): bool
    {
        $alreadyUnlocked = $user->achievements()
            ->whereKey($achievement->id)
            ->exists();

        if ($alreadyUnlocked) {
            return false;
        }

        $user->achievements()->attach($achievement->id, [
            'unlocked_at' => now(),
        ]);

        if ($achievement->xp_reward > 0) {
            $this->awardXp(
                user: $user,
                amount: $achievement->xp_reward,
                reason: 'unlock_achievement',
                sourceType: Achievement::class,
                sourceId: $achievement->id,
            );
        }

        return true;
    }

    /**
     * @return Collection<int,Achievement>
     */
    private function getUnlockedAchievements(User $user): Collection
    {
        return $user->achievements()
            ->orderByPivot('unlocked_at', 'desc')
            ->get();
    }

    private function getLatestAchievement(User $user): ?Achievement
    {
        return $user->achievements()
            ->orderByPivot('unlocked_at', 'desc')
            ->first();
    }
}
