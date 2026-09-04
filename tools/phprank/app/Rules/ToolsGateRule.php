<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ToolsGateRule implements ValidationRule
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
        if ($this->user) {
            if ($this->user->cannot('tools', ['App\Models\User'])) {
                $fail(__('You don\'t have access to this feature.'));
            }
        }
    }
}
