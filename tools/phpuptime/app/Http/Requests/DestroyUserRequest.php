<?php

namespace App\Http\Requests;

use App\Rules\ValidateUserPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
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
            'current_password' => ['required', 'string', 'min:6', 'max:128', new ValidateUserPasswordRule($this->user()->password)]
        ];
    }
}
