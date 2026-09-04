<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Link;
use Illuminate\Auth\Access\HandlesAuthorization;

class LinkPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any links.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Link $link): bool
    {
        if ($link->isPublic()) {
            return true;
        }

        if ($user && ($link->user_id == $user->id || $user->isAdmin())) {
            return true;
        }

        if ($link->isPasswordProtected() && session()->has($link->passwordSessionKey())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create links.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->links == -1) {
            return true;
        } elseif ($user->active_plan->features->links > 0) {
            if ($user->links()->count() < $user->active_plan->features->links) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the link.
     */
    public function update(User $user, Link $link): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the link.
     */
    public function delete(User $user, Link $link): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the link.
     */
    public function restore(User $user, Link $link): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the link.
     */
    public function forceDelete(User $user, Link $link): bool
    {
        return false;
    }
}
