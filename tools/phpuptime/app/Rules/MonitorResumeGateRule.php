<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MonitorResumeGateRule implements ValidationRule
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
        // If the request is to resume the monitor
        if (!$value) {
            if (!$this->user->can('resumeMonitors', [User::class])) {
                $fail(__('You have too many active monitors.'));
            }
        }
    }
}
