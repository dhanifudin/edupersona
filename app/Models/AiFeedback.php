<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    /** @use HasFactory<\Database\Factories\AiFeedbackFactory> */
    use HasFactory;

    protected $table = 'ai_feedback';

    protected $fillable = [
        'user_id',
        'context_type',
        'context_id',
        'feedback_text',
        'feedback_type',
        'is_read',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }
}
