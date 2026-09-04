<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateTargetKeyRule implements ValidationRule
{
    /**
     * The type of target to validate.
     */
    private ?string $targetType;

    /**
     * Create a new rule instance.
     */
    public function __construct(?string $targetType)
    {
        $this->targetType = $targetType;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!in_array($value, array_keys(config('continents'))) && $this->targetType == 'continents') {
            $fail(__('The :attribute field is invalid.'));
        }

        if (!in_array($value, array_keys(config('countries'))) && $this->targetType == 'countries') {
            $fail(__('The :attribute field is invalid.'));
        }

        if (!in_array($value, config('operating_systems')) && $this->targetType == 'operating_systems') {
            $fail(__('The :attribute field is invalid.'));
        }

        if (!in_array($value, config('browsers')) && $this->targetType == 'browsers') {
            $fail(__('The :attribute field is invalid.'));
        }

        if (!in_array($value, array_keys(config('languages'))) && $this->targetType == 'languages') {
            $fail(__('The :attribute field is invalid.'));
        }

        if (!in_array($value, array_keys(config('devices'))) && $this->targetType == 'devices') {
            $fail(__('The :attribute field is invalid.'));
        }
    }
}
