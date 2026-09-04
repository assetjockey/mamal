<?php

namespace App\Http\Requests;

use App\Models\Space;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpaceRequest extends FormRequest
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
            Space::where([['id', '=', $this->route('id')], ['user_id', '=', $this->input('user_id')]])->firstOrFail();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'min:1', 'max:32', 'unique:spaces,name,' . $this->route('id') . ',id,user_id,' . ($this->input('user_id') ?? $this->user()->id)],
            'color' => ['sometimes', 'integer', 'between:1,6']
        ];
    }
}
