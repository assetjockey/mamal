<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidatePlatformKeyRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, config('operating_systems'))) {
            $fail(__('The :attribute field is invalid.'));
        }
    }
}
