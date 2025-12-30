<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningActivity extends Model
{
    /** @use HasFactory<\Database\Factories\LearningActivityFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'material_id',
        'duration_seconds',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'material_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function getDurationInMinutes(): float
    {
        return round($this->duration_seconds / 60, 1);
    }
}
