<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FieldMatchOrMinimumRule implements ValidationRule
{
    /**
     * The exact value to match.
     */
    private string $match;

    /**
     * The minimum value.
     */
    private int $min;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $match, int $min)
    {
        $this->match = $match;
        $this->min = $min;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value != $this->match && $value < $this->min) {
            $fail(__('The :attribute must be :match or at least :min.', ['match' => $this->match, 'min' => $this->min]));
        }
    }
}
