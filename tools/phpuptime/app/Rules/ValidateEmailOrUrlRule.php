<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateEmailOrUrlRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $protocols = ['http', 'https', 'mailto', 'tel'];

        if (!in_array(parse_url($value, PHP_URL_SCHEME), $protocols) || (empty(parse_url($value, PHP_URL_HOST)) && empty(parse_url($value, PHP_URL_PATH)))) {
            $fail('The :attribute field must be a valid email address or URL.');
        }
    }
}
