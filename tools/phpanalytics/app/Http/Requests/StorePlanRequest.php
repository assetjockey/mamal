<?php

namespace App\Http\Requests;

use App\Rules\ValidateExtendedLicenseRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
            'features.pageviews' => ['required', 'integer', 'min:-1', 'max:9999999999'],
            'features.websites' => ['required', 'integer', 'min:-1', 'max:9999999999'],
            'features.email_reports' => ['required', 'integer', 'between:0,1'],
            'features.data_export' => ['required', 'integer', 'between:0,1'],
            'features.api' => ['required', 'integer', 'between:0,1']
        ];
    }
}
