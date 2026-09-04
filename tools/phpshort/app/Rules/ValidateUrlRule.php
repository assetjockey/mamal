<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidateUrlRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail(__('validation.url'));
        }

        $urls = preg_split('/[\r\n]+/', $value, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($urls as $url) {
            if (!Str::isUrl($url)) {
                $fail(__('validation.url'));
            }
        }
    }
}
