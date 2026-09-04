<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Domain;
use Illuminate\Auth\Access\HandlesAuthorization;

class DomainPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any domains.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the domain.
     */
    public function view(User $user, Domain $domain): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create domains.
     */
    public function create(User $user): bool
    {
        if ($user->active_plan->features->domains == -1) {
            return true;
        } elseif ($user->active_plan->features->domains > 0) {
            if ($user->domains()->count() < $user->active_plan->features->domains) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the domain.
     */
    public function update(User $user, Domain $domain): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the domain.
     */
    public function delete(User $user, Domain $domain): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the domain.
     */
    public function restore(User $user, Domain $domain): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the domain.
     */
    public function forceDelete(User $user, Domain $domain): bool
    {
        return false;
    }
}
