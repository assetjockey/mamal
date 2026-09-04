<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

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
    public function view(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user has access to the Data Export plan feature.
     */
    public function dataExport(User $user): bool
    {
        return $user->active_plan->features->data_export == 1;
    }

    /**
     * Determine whether the user has access to the API plan feature.
     */
    public function api(User $user): bool
    {
        return $user->active_plan->features->api == 1;
    }

    /**
     * Determine whether the user has access to the Spaces plan feature.
     */
    public function spaces(User $user): bool
    {
        return $user->active_plan->features->spaces != 0;
    }

    /**
     * Determine whether the user has access to the Domains plan feature.
     */
    public function domains(User $user): bool
    {
        return $user->active_plan->features->domains != 0;
    }

    /**
     * Determine whether the user has access to the Pixels plan feature.
     */
    public function pixels(User $user): bool
    {
        return $user->active_plan->features->pixels != 0;
    }

    /**
     * Determine whether the user has access to the Link Stats plan feature.
     */
    public function linkStats(?User $user): bool
    {
        return !$user || $user->active_plan->features->link_stats == 1;
    }

    /**
     * Determine whether the user has access to the Link Targeting plan feature.
     */
    public function linkTargeting(User $user): bool
    {
        return $user->active_plan->features->link_targeting == 1;
    }

    /**
     * Determine whether the user has access to the Link UTM Builder plan feature.
     */
    public function linkUtmBuilder(User $user): bool
    {
        return $user->active_plan->features->link_utm == 1;
    }

    /**
     * Determine whether the user has access to the Link Alias plan feature.
     */
    public function linkAlias(User $user): bool
    {
        return $user->active_plan->features->link_alias == 1;
    }

    /**
     * Determine whether the user has access to the Link Password plan feature.
     */
    public function linkPassword(User $user): bool
    {
        return $user->active_plan->features->link_password == 1;
    }

    /**
     * Determine whether the user has access to the Link Expiration plan feature.
     */
    public function linkExpiration(User $user): bool
    {
        return $user->active_plan->features->link_expiration == 1;
    }

    /**
     * Determine whether the user has access to the Additional Domains plan feature.
     */
    public function additionalDomains(User $user): bool
    {
        return $user->active_plan->features->additional_domains == 1;
    }

    /**
     * Determine whether the user has access to the Deep Linking plan feature.
     */
    public function deepLinking(User $user): bool
    {
        return $user->active_plan->features->link_deep == 1;
    }
}
