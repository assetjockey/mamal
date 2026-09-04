<?php

namespace App\Http\Requests;

use App\Rules\ValidateDnsRule;
use App\Rules\ValidateDomainNameRule;
use App\Rules\DomainLimitGateRule;
use App\Rules\ValidateExternalUrlRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('name')) {
            $this->merge(['name' => str_replace(['https://', 'http://'], '', mb_strtolower($this->input('name')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['bail', 'required', 'max:255', new ValidateDomainNameRule(), new DomainLimitGateRule($this, $this->user()), 'unique:domains,name', new ValidateDnsRule()],
            'homepage_url' => ['nullable', 'url', 'max:255', new ValidateExternalUrlRule()],
            'not_found_url' => ['nullable', 'url', 'max:255', new ValidateExternalUrlRule()],
            'user_id' => $this->is('admin/*') ? ['required', 'integer', 'in:0'] : ['prohibited'],
        ];
    }
}
