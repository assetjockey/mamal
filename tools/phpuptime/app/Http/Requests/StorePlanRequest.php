<?php

namespace App\Http\Requests;

use App\Rules\FieldMatchOrMinimumRule;
use App\Rules\ValidateExtendedLicenseRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:64'],
            'description' => ['required', 'max:256'],
            'amount_month' => ['sometimes', 'numeric', 'gt:0', 'max:9999999999', new ValidateExtendedLicenseRule()],
            'amount_year' => ['sometimes', 'numeric', 'gt:0', 'max:9999999999', new ValidateExtendedLicenseRule()],
            'currency' => ['sometimes', new ValidateExtendedLicenseRule()],
            'coupons' => ['sometimes', 'nullable'],
            'tax_rates' => ['sometimes', 'nullable'],
            'trial_days' => ['sometimes', 'integer', 'min:0', 'max:3650'],
            'visibility' => ['sometimes', 'integer', 'between:0,1'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:4294967295'],
            'features.monitors' => ['required', 'integer', 'min:-1', 'max:9999999999'],
            'features.monitor_intervals' => ['required', 'array', 'min:1'],
            'features.monitor_intervals.*' => ['required', 'in:' . implode(',', config('intervals.http'))],
            'features.ssl_monitoring' => ['required', 'integer', 'between:0,1'],
            'features.domain_monitoring' => ['required', 'integer', 'between:0,1'],
            'features.webhook_alerts' => ['required', 'integer', 'between:0,99'],
            'features.email_alerts' => ['required', 'integer', 'between:0,99'],
            'features.sms_alerts' => ['required', 'integer', 'between:0,99'],
            'features.status_pages' => ['required', 'integer', 'min:-1', 'max:9999999999'],
            'features.status_page_customization' => ['required', 'integer', 'between:0,1'],
            'features.data_retention' => ['required', 'string', new FieldMatchOrMinimumRule('-1', 30)],
            'features.data_export' => ['required', 'integer', 'between:0,1'],
            'features.api' => ['required', 'integer', 'between:0,1']
        ];
    }
}
