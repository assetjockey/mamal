<?php

namespace App\Http\Requests;

use App\Models\StatusPage;
use App\Rules\ValidateStatusPagePasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class ValidateStatusPagePasswordRequest extends FormRequest
{
    /**
     * The status page instance.
     */
    private StatusPage $statusPage;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->statusPage = StatusPage::where('id', $this->route('id'))->firstOrFail();

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password' => ['required', new ValidateStatusPagePasswordRule($this->statusPage)]
        ];
    }
}
