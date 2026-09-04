<?php

namespace App\Http\Requests;

use App\Rules\ValidateWebsitePasswordRule;
use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;

class ValidateWebsitePasswordRequest extends FormRequest
{
    /**
     * @var
     */
    var $website;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->website = Website::where('domain', $this->route('id'))->firstOrFail();

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
            'password' => ['required', new ValidateWebsitePasswordRule($this->website)]
        ];
    }
}
