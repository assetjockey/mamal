<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateGuestDomainRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If there's no default domain set
        if (empty(config('settings.short_domain_id'))) {
            $fail(__('No default domain.'));
        }

        // If the domain is not the same with the default domain
        if ($value != config('settings.short_domain_id')) {
            $fail(__('You don\'t have access to this feature.'));
        }
    }
}
