<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReportLimitGateRule implements ValidationRule
{
    /**
     * @var
     */
    private $user;

    /**
     * Create a new rule instance.
     *
     * @param $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->user->cannot('create', ['App\Models\Report'])) {
            $fail(__('You created too many reports.'));
        }
    }
}
