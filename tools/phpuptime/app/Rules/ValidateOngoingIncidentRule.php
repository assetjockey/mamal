<?php

namespace App\Rules;

use App\Models\Incident;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateOngoingIncidentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Incident::where([['monitor_id', '=', $value], ['ended_at', '=', null]])->exists()) {
            $fail(__('There\'s already an ongoing incident for this monitor.'));
        }
    }
}
