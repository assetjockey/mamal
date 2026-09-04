<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallDatabaseRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:128', 'confirmed'],
            'timezone' => ['required', 'in:' . implode(',', timezone_identifiers_list())],
            'newsletter' => ['nullable']
        ];
    }
}
