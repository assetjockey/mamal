<?php

namespace App\Rules;

use App\Models\Link;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateLinkRedirectPasswordRule implements ValidationRule
{
    /**
     * The link instance.
     */
    private Link $link;

    /**
     * Create a new rule instance.
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value != $this->link->redirect_password) {
            $fail(__('The entered password is not correct.'));
        }
    }
}
