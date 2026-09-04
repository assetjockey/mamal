<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class ProhibitedUnlessFieldValueRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * The name of the related field to check.
     */
    private string $fieldName;

    public function __construct(Request $request, string $fieldName)
    {
        $this->request = $request;
        $this->fieldName = $fieldName;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            if (!$this->request->input($this->fieldName)) {
                $fail(__('The :attribute field is prohibited unless :field field is set.', ['attribute' => $attribute, 'field' => $this->fieldName]));
            }
        }
    }
}
