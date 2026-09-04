<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PreventUserDemotionRule implements ValidationRule
{
    /**
     * The user ID whose role is being updated.
     */
    private string $userId;

    /**
     * The current user instance.
     */
    private User $currentUser;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $userId, User $authenticatedUser) {
        $this->userId = $userId;
        $this->currentUser = $authenticatedUser;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->currentUser->isAdmin() && $this->currentUser->id == $this->userId && $value == 0) {
            $fail(__('You cannot demote yourself.'));
        }
    }
}
