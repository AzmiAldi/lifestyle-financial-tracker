<?php

namespace App\Services;

use App\Enums\Mood;
use App\Models\MoodLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MoodService
{
    public function __construct(private readonly GamificationService $gamificationService) {}

    /**
     * @param array{
     *     mood:Mood|string,
     *     note?:string|null,
     *     logged_date?:string|null
     * } $data
     */
    public function createOrUpdateDailyMood(User $user, array $data): MoodLog
    {
        $loggedDate = isset($data['logged_date']) && $data['logged_date']
            ? Carbon::parse($data['logged_date'])->toDateString()
            : now()->toDateString();

        $moodLog = MoodLog::query()
            ->where('user_id', $user->id)
            ->whereDate('logged_date', $loggedDate)
            ->first();

        if (! $moodLog) {
            $moodLog = new MoodLog([
                'user_id' => $user->id,
                'logged_date' => $loggedDate,
            ]);
        }

        $moodLog->fill([
            'mood' => $data['mood'],
            'note' => $data['note'] ?? null,
        ]);
        $moodLog->save();

        if ($moodLog->wasRecentlyCreated) {
            $this->gamificationService->awardXp($user, 3, 'create_mood_log', MoodLog::class, $moodLog->id);
        }

        $this->gamificationService->awardDailyTrackingXp($user, $moodLog->logged_date);
        $this->gamificationService->checkAchievements($user);

        return $moodLog;
    }

    public function getTodayMood(User $user): ?MoodLog
    {
        return MoodLog::query()
            ->where('user_id', $user->id)
            ->whereDate('logged_date', now()->toDateString())
            ->first();
    }

    /**
     * @return Collection<int,MoodLog>
     */
    public function getRecentMoodLogs(User $user, int $limit = 10): Collection
    {
        return MoodLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('logged_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{
     *     month:string,
     *     total:int,
     *     mostCommonMood:?Mood,
     *     moodCounts:array<string,int>,
     *     recentLogs:Collection<int,MoodLog>
     * }
     */
    public function getMoodSummaryForCurrentMonth(User $user, ?Carbon $month = null): array
    {
        $period = $month?->copy() ?? now();

        $logs = MoodLog::query()
            ->where('user_id', $user->id)
            ->whereBetween('logged_date', [
                $period->copy()->startOfMonth()->toDateString(),
                $period->copy()->endOfMonth()->toDateString(),
            ])
            ->orderByDesc('logged_date')
            ->orderByDesc('id')
            ->get();

        $moodCounts = $logs
            ->groupBy(fn (MoodLog $log): string => $log->mood->value)
            ->map(fn (Collection $group): int => $group->count())
            ->all();

        arsort($moodCounts);
        $mostCommonMood = array_key_first($moodCounts);

        return [
            'month' => $period->format('Y-m'),
            'total' => $logs->count(),
            'mostCommonMood' => $mostCommonMood ? Mood::from($mostCommonMood) : null,
            'moodCounts' => $moodCounts,
            'recentLogs' => $logs->take(5)->values(),
        ];
    }

    /**
     * @return array<int,array{value:string,label:string,reflection:string}>
     */
    public function moodOptions(): array
    {
        return collect(Mood::cases())
            ->map(fn (Mood $mood): array => [
                'value' => $mood->value,
                'label' => $mood->label(),
                'reflection' => $mood->reflection(),
            ])
            ->all();
    }
}
