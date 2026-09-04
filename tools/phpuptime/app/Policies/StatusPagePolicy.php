<?php

namespace App\Policies;

use App\Models\StatusPage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StatusPagePolicy
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
    public function view(?User $user, StatusPage $statusPage): bool
    {
        if ($statusPage->isPublic()) {
            return true;
        }

        if ($user && ($statusPage->user_id == $user->id || $user->isAdmin())) {
            return true;
        }

        if ($statusPage->isPasswordProtected() && session()->has($statusPage->passwordSessionKey())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->status_pages == -1) {
            return true;
        } elseif ($user->active_plan->features->status_pages > 0) {
            if ($user->statusPages()->count() < $user->active_plan->features->status_pages) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StatusPage $statusPage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StatusPage $statusPage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StatusPage $statusPage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StatusPage $statusPage): bool
    {
        return false;
    }
}
