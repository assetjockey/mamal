<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidatePixelValueRule implements ValidationRule
{
    /**
     * The pixel type to validate.
     */
    private string $pixelType;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $pixelType)
    {
        $this->pixelType = $pixelType;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->pixelType == 'adroll') {
            if (mb_strpos($value, '-') == false) {
                $fail(__('The :attribute must be in :format format.', ['format' => 'ADVID-PIXID']));
            }
        }
    }
}
