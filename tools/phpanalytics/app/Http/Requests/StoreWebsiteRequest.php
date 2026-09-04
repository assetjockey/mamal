<?php

namespace App\Http\Requests;

use App\Rules\ValidateBadWordsRule;
use App\Rules\ValidateDomainNameRule;
use App\Rules\WebsiteLimitGateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Remove the URL protocol from the name input
        $domain = str_replace(['https://', 'http://'], '', mb_strtolower($this->input('domain')));

        $this->merge(['domain' => str_starts_with($domain, 'www.') ? str_replace('www.', '', $domain) : $domain]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'domain' => ['required', 'max:255', new ValidateDomainNameRule(), 'unique:websites,domain', new ValidateBadWordsRule(), new WebsiteLimitGateRule($this->user())],
            'privacy' => ['nullable', 'integer', 'between:0,2'],
            'password' => [Rule::requiredIf($this->input('privacy') == 2), 'nullable', 'string', 'min:1', 'max:128'],
            'exclude_bots' => ['nullable', 'integer', 'between:0,1'],
            'exclude_params' => ['nullable', 'string'],
            'exclude_ips' => ['nullable', 'string'],
            'email' => ['nullable', 'integer']
        ];
    }
}
