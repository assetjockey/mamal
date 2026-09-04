<?php

namespace App\Rules;

use App\Models\Incident;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateIncidentStartDateRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private User $user;

    /**
     * The monitor ID to validate the end date against.
     */
    private ?string $monitorId;

    /**
     * The incident ID to exclude from validation.
     */
    private ?string $incidentId;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $user, ?string $monitorId = null, ?string $incidentId = null)
    {
        $this->user = $user;
        $this->monitorId = $monitorId;
        $this->incidentId = $incidentId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            $date = Carbon::parse($value, $this->user->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));

            if ($date->isFuture()) {
                $fail(__('The :attribute field cannot be in the future.'));
            }

            $lastIncident = Incident::where([['user_id', '=', $this->user->id], ['monitor_id', '=', $this->monitorId]])->orderBy('id', 'desc')->first();

            if ($lastIncident && $lastIncident->ended_at) {
                if ($date->lessThanOrEqualTo($lastIncident->ended_at)) {
                    $fail(__('The :attribute field must be higher than :date', ['date' => $lastIncident->ended_at->tz($this->user->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]));
                }
            }
        }
    }
}
