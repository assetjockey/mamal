<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateWebsitePasswordRule implements ValidationRule
{
    /**
     * @var
     */
    private $website;

    /**
     * Create a new rule instance.
     *
     * @param $website
     * @return void
     */
    public function __construct($website)
    {
        $this->website = $website;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value != $this->website->password) {
            $fail(__('The entered password is not correct.'));
        }
    }
}
