<?php

namespace App\Http\Requests;

use App\Rules\ValidateExternalUrlRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDomainRequest extends FormRequest
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
            'homepage_url' => ['sometimes', 'nullable', 'url', 'max:255', new ValidateExternalUrlRule()],
            'not_found_url' => ['sometimes', 'nullable', 'url', 'max:255', new ValidateExternalUrlRule()],
        ];
    }
}
