<?php

namespace App\Rules;

use App\Models\Monitor;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateMonitorOwnershipRule implements ValidationRule
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
        if (!empty($value)) {
            if (!Monitor::where([['id', '=', $value], ['user_id', '=', $this->user->id]])->exists()) {
                $fail(__('The :attribute field is invalid.'));
            }
        }
    }
}
