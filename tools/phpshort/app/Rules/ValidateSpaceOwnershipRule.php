<?php

namespace App\Rules;

use Closure;
use App\Models\Space;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateSpaceOwnershipRule implements ValidationRule
{
    /**
     * The ID of the user who must own the space.
     */
    private string $userId;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!empty($value)) {
            if (!Space::where([['id', '=', $value], ['user_id', '=', $this->userId]])->exists()) {
                $fail(__('The :attribute field is invalid.'));
            }
        }
    }
}
