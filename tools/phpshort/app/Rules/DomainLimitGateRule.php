<?php

namespace App\Rules;

use App\Models\Domain;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class DomainLimitGateRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * The user instance.
     */
    private User $user;

    /**
     * Create a new rule instance.
     */
    public function __construct(Request $request, User $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->request->is('admin/*')) {
            if ($this->user->cannot('create', [Domain::class])) {
                $fail(__('You added too many domains.'));
            }
        }
    }
}
