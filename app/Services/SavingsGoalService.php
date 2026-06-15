<?php

namespace App\Services;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SavingsGoalService
{
    public function __construct(private readonly GamificationService $gamificationService) {}

    /**
     * @param array{
     *     title:string,
     *     target_amount:numeric-string|float|int,
     *     current_amount?:numeric-string|float|int|null,
     *     deadline?:string|null,
     *     description?:string|null
     * } $data
     */
    public function createGoal(User $user, array $data): SavingsGoal
    {
        $goal = DB::transaction(function () use ($user, $data): SavingsGoal {
            return SavingsGoal::query()->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'target_amount' => $data['target_amount'],
                'current_amount' => $data['current_amount'] ?? 0,
                'deadline' => $data['deadline'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });

        $this->gamificationService->awardXp($user, 10, 'create_savings_goal', SavingsGoal::class, $goal->id);
        $this->gamificationService->checkAchievements($user);

        return $goal;
    }

    /**
     * @param array{
     *     title:string,
     *     target_amount:numeric-string|float|int,
     *     current_amount?:numeric-string|float|int|null,
     *     deadline?:string|null,
     *     description?:string|null
     * } $data
     */
    public function updateGoal(User $user, SavingsGoal $goal, array $data): SavingsGoal
    {
        $this->ensureOwner($user, $goal);

        $goal->update([
            'title' => $data['title'],
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'deadline' => $data['deadline'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $goal->refresh();
    }

    public function deleteGoal(User $user, SavingsGoal $goal): void
    {
        $this->ensureOwner($user, $goal);
        $goal->delete();
    }

    /**
     * @return array<int,array{goal:SavingsGoal,progressPercentage:float,barPercentage:float,remaining:float,estimatedCompletion:?string}>
     */
    public function getGoalsOverviewForUser(User $user, int $limit = 5): array
    {
        return SavingsGoal::query()
            ->where('user_id', $user->id)
            ->get()
            ->sort(function (SavingsGoal $firstGoal, SavingsGoal $secondGoal): int {
                $deadlineComparison = ($firstGoal->deadline === null ? 1 : 0) <=> ($secondGoal->deadline === null ? 1 : 0);

                if ($deadlineComparison !== 0) {
                    return $deadlineComparison;
                }

                if ($firstGoal->deadline && $secondGoal->deadline) {
                    $dateComparison = $firstGoal->deadline->toDateString() <=> $secondGoal->deadline->toDateString();

                    if ($dateComparison !== 0) {
                        return $dateComparison;
                    }
                }

                return $secondGoal->id <=> $firstGoal->id;
            })
            ->take($limit)
            ->map(fn (SavingsGoal $goal): array => $this->summarizeGoal($goal))
            ->values()
            ->all();
    }

    public function calculateProgressPercentage(SavingsGoal $goal): float
    {
        if ((float) $goal->target_amount <= 0) {
            return 0;
        }

        return round(((float) $goal->current_amount / (float) $goal->target_amount) * 100, 1);
    }

    public function calculateRemainingTarget(SavingsGoal $goal): float
    {
        return max(0, (float) $goal->target_amount - (float) $goal->current_amount);
    }

    public function estimatedCompletion(SavingsGoal $goal): ?string
    {
        return $goal->deadline?->format('d M Y');
    }

    private function summarizeGoal(SavingsGoal $goal): array
    {
        $progressPercentage = $this->calculateProgressPercentage($goal);

        return [
            'goal' => $goal,
            'progressPercentage' => $progressPercentage,
            'barPercentage' => min(100, $progressPercentage),
            'remaining' => $this->calculateRemainingTarget($goal),
            'estimatedCompletion' => $this->estimatedCompletion($goal),
        ];
    }

    private function ensureOwner(User $user, SavingsGoal $goal): void
    {
        if ($goal->user_id !== $user->id) {
            abort(403);
        }
    }
}
