<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateExtendedLicenseRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('settings.license_type')) {
            $fail(__('An Extended license is required to use this feature.'));
        }
    }
}
