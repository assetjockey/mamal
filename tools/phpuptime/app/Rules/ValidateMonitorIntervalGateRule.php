<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateMonitorIntervalGateRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, $this->user->active_plan->features->monitor_intervals)) {
            $fail(__('You don\'t have access to this feature.'));
        }
    }
}
