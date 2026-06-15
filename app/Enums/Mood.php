<?php

namespace App\Enums;

enum Mood: string
{
    case Happy = 'happy';
    case Calm = 'calm';
    case Stressed = 'stressed';
    case Tired = 'tired';
    case Productive = 'productive';
    case Anxious = 'anxious';
    case Neutral = 'neutral';

    public function label(): string
    {
        return match ($this) {
            self::Happy => 'Happy',
            self::Calm => 'Calm',
            self::Stressed => 'Stressed',
            self::Tired => 'Tired',
            self::Productive => 'Productive',
            self::Anxious => 'Anxious',
            self::Neutral => 'Neutral',
        };
    }

    public function reflection(): string
    {
        return match ($this) {
            self::Happy => 'Notice what supports this lighter rhythm today.',
            self::Calm => 'A calm day is a good moment to make intentional choices.',
            self::Stressed => 'No pressure, just awareness around spending and stress.',
            self::Tired => 'Keep decisions simple and kind to your energy today.',
            self::Productive => 'A focused mood can help you make clearer money choices.',
            self::Anxious => 'Take it slowly. Awareness is already a useful step.',
            self::Neutral => 'A neutral day still gives useful context for your patterns.',
        };
    }
}
