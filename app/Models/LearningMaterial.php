<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningMaterial extends Model
{
    /** @use HasFactory<\Database\Factories\LearningMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'class_id',
        'title',
        'description',
        'topic',
        'type',
        'learning_style',
        'difficulty_level',
        'content_url',
        'file_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LearningActivity::class, 'material_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class, 'material_id');
    }

    public function scopeForLearningStyle($query, string $style)
    {
        return $query->whereIn('learning_style', [$style, 'all']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
