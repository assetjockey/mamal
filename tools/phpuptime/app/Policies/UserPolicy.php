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
     * Determine whether the user has access to the Status Page Customization plan feature.
     */
    public function statusPageCustomization(User $user): bool
    {
        return $user->active_plan->features->status_page_customization == 1;
    }

    /**
     * Determine whether the user has access to the Webhook Alerts plan feature.
     */
    public function webhookAlerts(User $user): bool
    {
        return $user->active_plan->features->webhook_alerts != 0;
    }

    /**
     * Determine whether the user has access to the SMS Alerts plan feature.
     */
    public function smsAlerts(User $user): bool
    {
        return $user->active_plan->features->sms_alerts != 0;
    }

    /**
     * Determine whether the user has access to Email Alerts plan feature.
     */
    public function emailAlerts(User $user): bool
    {
        return $user->active_plan->features->email_alerts != 0;
    }

    /**
     * Determine whether the user has access to the SSL Monitoring plan feature.
     */
    public function sslMonitoring(User $user): bool
    {
        return $user->active_plan->features->ssl_monitoring == 1;
    }

    /**
     * Determine whether the user has access to the Domain Monitoring plan feature.
     */
    public function domainMonitoring(User $user): bool
    {
        return $user->active_plan->features->domain_monitoring == 1;
    }

    /**
     * Determine whether the user can resume monitors.
     */
    public function resumeMonitors(User $user): bool
    {
        $activeMonitorsCount = $user->monitors()->where('status', '!=', 'paused')->count();

        return $user->active_plan->features->monitors == -1 || $activeMonitorsCount < $user->active_plan->features->monitors;
    }
}
