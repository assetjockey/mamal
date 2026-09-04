<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Space;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpacePolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any spaces.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the space.
     */
    public function view(User $user, Space $space): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create spaces.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->spaces == -1) {
            return true;
        } elseif ($user->active_plan->features->spaces > 0) {
            if ($user->spaces()->count() < $user->active_plan->features->spaces) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the space.
     */
    public function update(User $user, Space $space): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the space.
     */
    public function delete(User $user, Space $space): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the space.
     */
    public function restore(User $user, Space $space): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the space.
     */
    public function forceDelete(User $user, Space $space): bool
    {
        return false;
    }
}
