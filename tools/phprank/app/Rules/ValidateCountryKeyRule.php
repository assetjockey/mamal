<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateCountryKeyRule implements ValidationRule
{
    /**
     * The list of country codes to be excluded.
     *
     * @var
     */
    var $excludedCountries;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($excludedCountries = null)
    {
        $this->excludedCountries = array_flip($excludedCountries);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!array_key_exists($value, array_diff_key(config('countries'), $this->excludedCountries))) {
            $fail(__('Invalid country.'));
        }
    }
}
