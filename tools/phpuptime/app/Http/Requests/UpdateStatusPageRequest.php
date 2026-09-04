<?php

namespace App\Http\Requests;

use App\Models\StatusPage;
use App\Rules\ProhibitedUnlessFieldValueRule;
use App\Rules\StatusPageCustomizationGateRule;
use App\Rules\ValidateBannedWordsRule;
use App\Rules\ValidateDnsRule;
use App\Rules\ValidateDomainNameRule;
use App\Rules\ValidateEmailOrUrlRule;
use App\Rules\ValidateMonitorsOwnershipRule;
use App\Rules\ValidatePrivacyFieldRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->has('user_id') && !$this->user()->isAdmin()) {
            return false;
        }

        if ($this->has('user_id')) {
            StatusPage::where([['id', '=', $this->route('id')], ['user_id', '=', $this->input('user_id')]])->firstOrFail();
        }

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
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:128', 'unique:status_pages,name,'.$this->route('id').',id,user_id,'.($this->input('user_id') ?? $this->user()->id), new ValidateBannedWordsRule()],
            'slug' => ['sometimes', 'required', 'max:64', 'alpha_dash', 'unique:status_pages,slug,'.$this->route('id').',id', new ValidateBannedWordsRule()],
            'monitor_ids' => ['sometimes', 'nullable', 'array', 'max:' . config('settings.status_pages_max_monitors'), new ValidateMonitorsOwnershipRule($this, $this->input('user_id') ?? $this->user()->id)],
            'logo' => ['sometimes', 'nullable', 'prohibited_if:remove_logo,on', 'file', 'mimes:' . config('settings.status_page_logo_format'), 'min:1', 'max:' . (1024 * config('settings.status_page_logo_filesize'))],
            'remove_logo' => ['sometimes', 'nullable', 'boolean'],
            'favicon' => ['sometimes', 'nullable', 'prohibited_if:remove_favicon,on', 'file', 'mimes:' . config('settings.status_page_favicon_format'), 'min:1', 'max:' . (1024 * config('settings.status_page_favicon_filesize'))],
            'remove_favicon' => ['sometimes', 'nullable', 'boolean'],
            'privacy' => ['sometimes', 'required', 'integer', 'between:0,2', new ValidatePrivacyFieldRule($this, 'domain')],
            'password' => [(in_array($this->input('privacy'), [0, 1]) ? 'nullable' : 'sometimes'), 'string', 'min:1', 'max:128'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:512', new ValidateBannedWordsRule()],
            'contact_url' => ['sometimes', 'nullable', new ValidateEmailOrUrlRule(), 'max:512', new ValidateBannedWordsRule()],
            'domain' => ['bail', 'sometimes', 'nullable', 'max:255', 'unique:status_pages,domain,'.$this->route('id').',id', new ValidateBannedWordsRule(), new ValidateDomainNameRule(), new ValidateDnsRule(), new StatusPageCustomizationGateRule($this->user())],
            'custom_css' => ['sometimes', 'nullable', 'string', 'max:20480', new ProhibitedUnlessFieldValueRule($this, 'domain'), new StatusPageCustomizationGateRule($this->user())],
            'custom_js' => ['sometimes', 'nullable', 'string', 'max:20480', new ProhibitedUnlessFieldValueRule($this, 'domain'), new StatusPageCustomizationGateRule($this->user())],
            'noindex' => ['sometimes', 'nullable', 'integer'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:128', new ValidateBannedWordsRule()],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:512', new ValidateBannedWordsRule()],
        ];
    }
}
