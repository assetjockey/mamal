<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pixel;
use Illuminate\Auth\Access\HandlesAuthorization;

class PixelPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any pixels.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the pixel.
     */
    public function view(User $user, Pixel $pixel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create pixels.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->pixels == -1) {
            return true;
        } elseif ($user->active_plan->features->pixels > 0) {
            if ($user->pixels()->count() < $user->active_plan->features->pixels) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the pixel.
     */
    public function update(User $user, Pixel $pixel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the pixel.
     */
    public function delete(User $user, Pixel $pixel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the pixel.
     */
    public function restore(User $user, Pixel $pixel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the pixel.
     */
    public function forceDelete(User $user, Pixel $pixel): bool
    {
        return false;
    }
}
