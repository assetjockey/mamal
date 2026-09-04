<?php

namespace App\Rules;

use App\Models\Link;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LinkLimitGateRule implements ValidationRule
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
        if ($this->user->cannot('create', [Link::class])) {
            $fail(__('You shortened too many links.'));
        }
    }
}
