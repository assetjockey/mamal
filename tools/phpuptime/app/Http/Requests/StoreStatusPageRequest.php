<?php

namespace App\Http\Requests;

use App\Rules\ProhibitedUnlessFieldValueRule;
use App\Rules\StatusPageCustomizationGateRule;
use App\Rules\StatusPageLimitGateRule;
use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateDnsRule;
use App\Rules\ValidateDomainNameRule;
use App\Rules\ValidateEmailOrUrlRule;
use App\Rules\ValidateMonitorsOwnershipRule;
use App\Rules\ValidatePrivacyFieldRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStatusPageRequest extends FormRequest
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
        if ($this->input('domain')) {
            $this->merge(['domain' => str_replace(['https://', 'http://'], '', mb_strtolower($this->input('domain')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:128', 'unique:status_pages,name,null,id,user_id,'.$this->user()->id, new ValidateBannedWordsRule(), new StatusPageLimitGateRule($this->user())],
            'slug' => ['required', 'max:64', 'alpha_dash', 'unique:status_pages,slug', new ValidateBannedWordsRule()],
            'monitor_ids' => ['nullable', 'array', 'max:' . config('settings.status_pages_max_monitors'), new ValidateMonitorsOwnershipRule($this, $this->input('user_id') ?? $this->user()->id)],
            'logo' => ['nullable', 'prohibited_if:remove_logo,on', 'file', 'mimes:' . config('settings.status_page_logo_format'), 'min:1', 'max:' . (1024 * config('settings.status_page_logo_filesize'))],
            'remove_logo' => ['nullable', 'boolean'],
            'favicon' => ['nullable', 'prohibited_if:remove_favicon,on', 'file', 'mimes:' . config('settings.status_page_favicon_format'), 'min:1', 'max:' . (1024 * config('settings.status_page_favicon_filesize'))],
            'remove_favicon' => ['nullable', 'boolean'],
            'privacy' => ['nullable', 'integer', 'between:0,2', new ValidatePrivacyFieldRule($this, 'domain')],
            'password' => [Rule::requiredIf($this->input('privacy') == 2), 'nullable', 'string', 'min:1', 'max:128'],
            'website_url' => ['nullable', 'url', 'max:512', new ValidateBannedWordsRule()],
            'contact_url' => ['nullable', new ValidateEmailOrUrlRule(), 'max:512', new ValidateBannedWordsRule()],
            'domain' => ['bail', 'nullable', 'max:255', 'unique:status_pages,domain', new ValidateBannedWordsRule(), new ValidateDomainNameRule(), new ValidateDnsRule(), new StatusPageCustomizationGateRule($this->user())],
            'custom_css' => ['nullable', 'string', 'max:20480', new ProhibitedUnlessFieldValueRule($this, 'domain'), new StatusPageCustomizationGateRule($this->user())],
            'custom_js' => ['nullable', 'string', 'max:20480', new ProhibitedUnlessFieldValueRule($this, 'domain'), new StatusPageCustomizationGateRule($this->user())],
            'noindex' => ['nullable', 'integer'],
            'meta_title' => ['nullable', 'string', 'max:128', new ValidateBannedWordsRule()],
            'meta_description' => ['nullable', 'string', 'max:512', new ValidateBannedWordsRule()],
        ];
    }
}
