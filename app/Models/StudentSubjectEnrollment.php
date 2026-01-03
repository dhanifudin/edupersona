<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubjectEnrollment extends Model
{
    /** @use HasFactory<\Database\Factories\StudentSubjectEnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'enrollment_type',
        'enrolled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAssigned($query)
    {
        return $query->where('enrollment_type', 'assigned');
    }

    public function scopeElective($query)
    {
        return $query->where('enrollment_type', 'elective');
    }
}
