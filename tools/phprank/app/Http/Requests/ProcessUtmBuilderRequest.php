<?php

namespace App\Http\Requests;

use App\Rules\ToolsGateRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessUtmBuilderRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'url' => ['required', 'url', 'min:1', 'max:2048', new ToolsGateRule($this->user())],
            'utm_source' => ['required', 'min:1', 'max:128', 'alpha_dash'],
            'utm_medium' => ['nullable', 'max:128', 'alpha_dash'],
            'utm_campaign' => ['nullable', 'max:128', 'alpha_dash'],
            'utm_term' => ['nullable', 'max:128', 'alpha_dash'],
            'utm_content' => ['nullable', 'max:128', 'alpha_dash']
        ];
    }
}
