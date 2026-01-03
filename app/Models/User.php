<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id_number',
        'teacher_id_number',
        'phone',
        'learning_interests',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'learning_interests' => 'array',
        ];
    }

    // Role helpers
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Student relationships
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'class_student', 'student_id', 'class_id')
            ->withPivot('enrolled_at', 'status')
            ->withTimestamps();
    }

    public function currentClass(): ?ClassRoom
    {
        return $this->classes()->wherePivot('status', 'active')->first();
    }

    public function learningStyleProfile(): HasOne
    {
        return $this->hasOne(LearningStyleProfile::class);
    }

    public function learningStyleResponses(): HasMany
    {
        return $this->hasMany(LearningStyleResponse::class);
    }

    public function learningActivities(): HasMany
    {
        return $this->hasMany(LearningActivity::class);
    }

    public function aiRecommendations(): HasMany
    {
        return $this->hasMany(AiRecommendation::class);
    }

    public function aiFeedback(): HasMany
    {
        return $this->hasMany(AiFeedback::class);
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function subjectEnrollments(): HasMany
    {
        return $this->hasMany(StudentSubjectEnrollment::class);
    }

    public function enrolledSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject_enrollments')
            ->withPivot('enrollment_type', 'enrolled_at', 'status')
            ->withTimestamps();
    }

    // Teacher relationships
    public function teacherSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function classSubjects(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'teacher_id');
    }

    public function uploadedMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class, 'teacher_id');
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'homeroom_teacher_id');
    }
}
