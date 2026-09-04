<?php

namespace App\Http\Requests;

use App\Models\Pixel;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePixelRequest extends FormRequest
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
            Pixel::where([['id', '=', $this->route('id')], ['user_id', '=', $this->input('user_id')]])->firstOrFail();
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'min:1', 'max:32', 'unique:pixels,name,' . $this->route('id') . ',id,user_id,' . ($this->input('user_id') ?? $this->user()->id)],
            'type' => ['sometimes', 'in:' . implode(',', array_keys(config('pixels')))],
            'value' => ['sometimes', 'alpha_dash', 'max:255']
        ];
    }
}
