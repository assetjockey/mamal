<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class ValidateUserPasswordRule implements ValidationRule
{
    /**
     * The hashed user password to be validated against.
     */
    private string $password;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!Hash::check($value, $this->password)) {
            $fail('The current password is not correct.');
        }
    }
}
