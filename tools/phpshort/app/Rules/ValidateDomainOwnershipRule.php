<?php

namespace App\Rules;

use App\Models\Domain;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateDomainOwnershipRule implements ValidationRule
{
    /**
     * The user instance.
     */
    private User $user;

    /**
     * The domain instance.
     */
    private Domain $domain;

    /**
     * Create a new rule instance.
     */
    public function __construct(User $user, Domain $domain)
    {
        $this->user = $user;
        $this->domain = $domain;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the domain has a user id, and it is not the same with the user's id
        // or if the domain is a global domain, but not the default one, and the user does not have access to global domains
        if (($this->domain->user_id && $this->domain->user_id != $this->user->id) || (!$this->domain->user_id && config('settings.short_domain_id') && config('settings.short_domain_id') != $value && $this->user->cannot('additionalDomains', [User::class]))) {
            $fail(__('The :attribute field is invalid.'));
        }
    }
}
