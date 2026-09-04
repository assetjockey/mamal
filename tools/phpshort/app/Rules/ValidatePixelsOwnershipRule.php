<?php

namespace App\Rules;

use App\Models\Pixel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidatePixelsOwnershipRule implements ValidationRule
{
    /**
     * The ID of the user who must own the pixel.
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
        if (!is_array($value)) {
            $fail(__('The :attribute field is invalid.'));
        }

        $values = array_filter($value);

        $pixelsCount = Pixel::where('user_id', '=', $this->userId)->whereIn('id', $values)->count();

        if (count($values) !== $pixelsCount) {
            $fail(__('The :attribute field is invalid.'));
        }
    }
}
