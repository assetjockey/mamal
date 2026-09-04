<?php

namespace App\Rules;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateIncidentEndDateRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private User $user;

    /**
     * The start date to validate against.
     */
    private Carbon $startDate;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $user, Carbon $startDate)
    {
        $this->user = $user;
        $this->startDate = $startDate;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            $endDate = Carbon::parse($value, $this->user->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));

            if ($endDate->isFuture()) {
                $fail(__('The :attribute field cannot be in the future.'));
            }

            if ($endDate->lessThanOrEqualTo($this->startDate)) {
                $fail(__('The :attribute field must be higher than :date', ['date' => $this->startDate->tz($this->user->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]));
            }
        }
    }
}
