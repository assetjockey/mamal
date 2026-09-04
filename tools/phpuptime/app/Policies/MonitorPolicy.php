<?php

namespace App\Policies;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MonitorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Monitor $monitor, ?string $token = null): bool
    {
        if ($user && $user->id == $monitor->user_id) {
            return true;
        }

        if ($user && $user->isAdmin()) {
            return true;
        }

        if ($monitor->token == $token) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->monitors == -1) {
            return true;
        } elseif ($user->active_plan->features->monitors > 0) {
            if ($user->monitors()->count() < $user->active_plan->features->monitors) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Monitor $monitor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Monitor $monitor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Monitor $monitor): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Monitor $monitor): bool
    {
        return false;
    }
}
