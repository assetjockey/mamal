<?php

namespace App\Services\Projects;

use App\Models\GeneralSetting;
use App\Models\User;

/**
 * Resolves the effective Project_Limit for a user from subscription state and
 * the configured limit sources.
 *
 * Rules (Requirement 9):
 *  - Active subscription            -> the subscribed Plan's `features['project_limit']`.
 *  - Active subscription, no limit  -> fall back to the Free_Tier_Limit (Requirement 8.3).
 *  - No active subscription         -> the Free_Tier_Limit (Requirement 9.2).
 *  - Chosen value null/unset/negative -> 0; block new creation, existing Projects stay
 *    accessible (Requirement 9.3).
 *
 * "Active subscription" reuses the existing platform definition
 * (`status = 'active' AND active_until > now()`) modelled by
 * {@see User::activeSubscription()} and {@see \App\Models\Subscriber::scopeActive()}.
 */
final class ProjectLimitResolver
{
    /**
     * Pure core, isolated for property testing. No DB access.
     *
     * @param  int|null  $planLimit      `features['project_limit']` of the active plan, or null when unset.
     * @param  int|null  $freeTierLimit  `general_settings.free_tier_project_limit`, or null when unset.
     * @param  bool      $hasActiveSub   Whether the user has an Active_Subscription.
     * @return int                       The effective Project_Limit; always a non-negative integer.
     */
    public static function effectiveLimit(?int $planLimit, ?int $freeTierLimit, bool $hasActiveSub): int
    {
        $chosen = $hasActiveSub ? $planLimit : $freeTierLimit;

        // When a subscribed plan does not define a limit, fall back to the free-tier value (8.3).
        if ($hasActiveSub && $planLimit === null) {
            $chosen = $freeTierLimit;
        }

        // null/unset or negative -> 0 (9.3).
        return ($chosen === null || $chosen < 0) ? 0 : $chosen;
    }

    /**
     * Resolve the effective Project_Limit for a user from the database.
     *
     * Reads the user's Active_Subscription and its plan's `features['project_limit']`
     * (via {@see \App\Models\Plan::projectLimit()}) and the
     * `general_settings.free_tier_project_limit`, then delegates to
     * {@see self::effectiveLimit()}.
     */
    public function resolve(User $user): int
    {
        $activeSubscription = $user->activeSubscription()->with('plan')->first();
        $hasActiveSub = $activeSubscription !== null;

        $planLimit = $hasActiveSub && $activeSubscription->plan !== null
            ? $activeSubscription->plan->projectLimit()
            : null;

        $freeTierLimit = $this->freeTierLimit();

        return self::effectiveLimit($planLimit, $freeTierLimit, $hasActiveSub);
    }

    /**
     * Read the configured Free_Tier_Limit from general settings, or null when unset.
     */
    private function freeTierLimit(): ?int
    {
        $settings = GeneralSetting::query()->first();

        if ($settings === null) {
            return null;
        }

        $value = $settings->free_tier_project_limit;

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
