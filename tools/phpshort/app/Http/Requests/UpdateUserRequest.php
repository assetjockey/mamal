<?php

namespace App\Http\Requests;

use App\Rules\PreventUserDemotionRule;
use App\Rules\ValidateUserPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->route('id') && !$this->user()->isAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'min:1', 'max:255', 'unique:users,email,' . ($this->route('id') ?? $this->user()->id)],
            'tfa' => ['sometimes', 'integer', 'between:0,1'],
            'current_password' => $this->is('admin/*') ? ['prohibited'] : ['nullable', 'required_with:password', 'string', 'min:6', 'max:128', new ValidateUserPasswordRule($this->user()->password)],
            'password' => ['nullable', 'required_with:current_password', 'string', 'min:6', 'max:128', 'confirmed'],
            'avatar' => ['nullable', 'prohibited_if:remove_avatar,on', 'file', 'mimes:' . config('settings.user_avatar_extensions'), 'min:1', 'max:' . (1024 * config('settings.user_avatar_filesize'))],
            'remove_avatar' => ['nullable', 'boolean'],
            'timezone' => ['sometimes', 'in:' . implode(',', timezone_identifiers_list())],
            'api_token' => ['sometimes', 'boolean'],
            'mark_email_as_verified' => $this->is('admin/*') ? ['nullable', 'integer', 'between:0,1'] : ['prohibited'],
            'update_authed_at' => $this->is('admin/*') ? ['nullable', 'integer', 'between:0,1'] : ['prohibited'],
            'role'  => $this->is('admin/*') ? ['sometimes', 'integer', 'between:0,1', new PreventUserDemotionRule(($this->route('id') ?? $this->user()->id), $this->user())] : ['prohibited'],
            'plan_id' => $this->is('admin/*') ? ['sometimes'] : ['prohibited'],
            'plan_ends_at' => $this->is('admin/*') ? ['sometimes', 'nullable', 'date_format:Y-m-d'] : ['prohibited'],
        ];
    }
}
