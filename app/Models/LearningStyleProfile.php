<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningStyleProfile extends Model
{
    /** @use HasFactory<\Database\Factories\LearningStyleProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'visual_score',
        'auditory_score',
        'kinesthetic_score',
        'dominant_style',
        'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'visual_score' => 'decimal:2',
            'auditory_score' => 'decimal:2',
            'kinesthetic_score' => 'decimal:2',
            'analyzed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getScoresAsArray(): array
    {
        return [
            'visual' => (float) $this->visual_score,
            'auditory' => (float) $this->auditory_score,
            'kinesthetic' => (float) $this->kinesthetic_score,
        ];
    }
}
