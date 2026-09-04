<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateDeepLinkRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private ?User $user;

    /**
     * Create a new rule instance.
     */
    public function __construct(?User $user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (parse_url($value, PHP_URL_SCHEME) && !in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'])) {
            if (!$this->user || $this->user->cannot('deepLinking', [User::class])) {
                $fail(__('You don\'t have access to this feature.'));
            }
        }
    }
}
