<?php

namespace App\Rules;

use App\Models\Monitor;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MonitorLimitGateRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private User $user;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->cannot('create', [Monitor::class])) {
            $fail(__('You created too many monitors.'));
        }
    }
}
