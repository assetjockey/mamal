<?php

namespace App\Http\Requests;

use App\Rules\FieldNotPresentRule;
use App\Rules\ReportLimitGateRule;
use App\Rules\ValidateBadWordsRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreReportRequest extends FormRequest
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
        // If the user is logged-in
        if (Auth::check()) {
            $rules = [
                'url' => ['bail', 'required', 'max:2048', 'url', new ValidateBadWordsRule(), new ReportLimitGateRule($this->user())],
                'sitemap' => ['nullable', 'integer', 'between:0,1'],
                'privacy' => ['nullable', 'integer', 'between:0,2'],
                'password' => [Rule::requiredIf($this->input('privacy') == 2), 'nullable', 'string', 'min:1', 'max:128'],
            ];
        } else {
            $rules = [
                'url' => ['bail', 'required', 'max:2048', 'url', new ValidateBadWordsRule()],
                'sitemap' => [new FieldNotPresentRule()],
                'privacy' => ['nullable', 'integer', 'between:0,2'],
                'password' => [new FieldNotPresentRule()],
                formatCaptchaFieldName() => config('settings.captcha_driver') ? ['required', 'captcha'] : [],
            ];
        }

        return $rules;
    }
}
