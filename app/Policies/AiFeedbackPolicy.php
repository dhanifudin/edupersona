<?php

namespace App\Policies;

use App\Models\AiFeedback;
use App\Models\User;

class AiFeedbackPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStudent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AiFeedback $aiFeedback): bool
    {
        return $user->id === $aiFeedback->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() && $user->learningStyleProfile !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AiFeedback $aiFeedback): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AiFeedback $aiFeedback): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AiFeedback $aiFeedback): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AiFeedback $aiFeedback): bool
    {
        return false;
    }
}
