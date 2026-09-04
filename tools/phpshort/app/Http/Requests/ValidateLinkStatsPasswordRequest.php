<?php

namespace App\Http\Requests;

use App\Models\Link;
use App\Rules\ValidateLinkStatsPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class ValidateLinkStatsPasswordRequest extends FormRequest
{
    /**
     * The link instance.
     */
    private Link $link;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->link = Link::where('id', '=', $this->route('id'))->firstOrFail();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password' => ['required', new ValidateLinkStatsPasswordRule($this->link)]
        ];
    }
}
