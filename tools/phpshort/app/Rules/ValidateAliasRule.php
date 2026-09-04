<?php

namespace App\Rules;

use App\Models\Link;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class ValidateAliasRule implements ValidationRule
{
    /**
     * The request instance.
     */
    private Request $request;

    /**
     * Create a new rule instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $conditions = [];

        $conditions[] = ['alias', '=', $value];

        // If the query is for a specific link
        if ($this->request->route('id')) {
            // Exclude the link when validating the alias
            $conditions[] = ['id', '!=', $this->request->route('id')];

            $link = Link::findOrFail($this->request->route('id'));
            $conditions[] = ['domain_id', '=', $link->domain->id ?? null];
        } else {
            // If the request has a link under a domain
            if ($this->request->input('domain_id')) {
                $conditions[] = ['domain_id', '=', $this->request->input('domain_id')];
            }
            // Check for links that are not under a domain
            else {
                $conditions[] = ['domain_id', '=', null];
            }
        }

        if (Link::where($conditions)->exists()) {
            $fail(__('validation.unique'));
        }
    }
}
