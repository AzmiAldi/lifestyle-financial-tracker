<?php

namespace App\Models;

use App\Enums\Mood;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodLog extends Model
{
    /** @use HasFactory<\Database\Factories\MoodLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mood',
        'note',
        'logged_date',
    ];

    protected function casts(): array
    {
        return [
            'mood' => Mood::class,
            'logged_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
