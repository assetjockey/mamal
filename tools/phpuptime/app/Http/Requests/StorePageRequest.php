<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
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
            'slug' => ['required', 'max:64', 'alpha_dash', Rule::unique('pages', 'slug')->where(function ($query) { return $query->where('language', $this->input('language')); })],
            'visibility' => ['required', 'integer', 'between:0,1'],
            'language' => ['required', 'max:16'],
            'content' => ['required']
        ];
    }
}
