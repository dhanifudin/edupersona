<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRecommendation extends Model
{
    /** @use HasFactory<\Database\Factories\AiRecommendationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'material_id',
        'reason',
        'relevance_score',
        'is_viewed',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'relevance_score' => 'decimal:2',
            'is_viewed' => 'boolean',
            'viewed_at' => 'datetime',
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

    public function markAsViewed(): void
    {
        $this->update([
            'is_viewed' => true,
            'viewed_at' => now(),
        ]);
    }

    public function scopeUnviewed($query)
    {
        return $query->where('is_viewed', false);
    }
}
