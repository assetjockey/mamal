<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateReportPasswordRule implements ValidationRule
{
    /**
     * @var
     */
    private $report;

    /**
     * Create a new rule instance.
     *
     * @param $report
     * @return void
     */
    public function __construct($report)
    {
        $this->report = $report;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value != $this->report->password) {
            $fail(__('The entered password is not correct.'));
        }
    }
}
