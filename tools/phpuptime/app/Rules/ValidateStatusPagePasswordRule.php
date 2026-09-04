<?php

namespace App\Rules;

use App\Models\StatusPage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateStatusPagePasswordRule implements ValidationRule
{
    /**
     * The status page instance.
     */
    private StatusPage $statusPage;

    /**
     * Create a new rule instance.
     */
    public function __construct(StatusPage $statusPage)
    {
        $this->statusPage = $statusPage;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value != $this->statusPage->password) {
            $fail('The entered password is not correct.');
        }
    }
}
