<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class ValidatePrivacyFieldRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * The name of the related field to check.
     */
    private string $fieldName;

    /**
     * Create a new rule instance.
     */
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
        if ($value == 1) {
            if ($this->request->input($this->fieldName)) {
                $fail(__('The :attribute field cannot be :value when the :field field is present.', ['attribute' => $attribute, 'value' => $value, 'field' => $this->fieldName]));
            }
        }
    }
}
