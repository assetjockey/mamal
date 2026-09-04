<?php

namespace App\Http\Requests;

use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWebsiteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // If the request is to edit a website as a specific user
        // And the user is not an admin
        if ($this->has('user_id') && $this->user()->role == 0) {
            return false;
        }

        // Check if the website to be edited exists under that user
        if ($this->has('user_id')) {
            Website::where([['id', '=', $this->route('id')], ['user_id', '=', $this->input('user_id')]])->firstOrFail();
        }

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
            'privacy' => ['sometimes', 'required', 'integer', 'between:0,2'],
            'password' => [(in_array($this->input('privacy'), [0, 1]) ? 'nullable' : 'sometimes'), 'string', 'min:1', 'max:128'],
            'exclude_bots' => ['sometimes', 'integer', 'between:0,1'],
            'exclude_ips' => ['sometimes', 'nullable', 'string'],
            'exclude_params' => ['sometimes', 'nullable', 'string'],
            'email' => ['sometimes', 'nullable', 'integer']
        ];
    }
}
