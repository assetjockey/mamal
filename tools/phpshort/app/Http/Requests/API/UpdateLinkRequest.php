<?php

namespace App\Http\Requests\API;

use App\Http\Requests\UpdateLinkRequest as BaseUpdateLinkRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateLinkRequest extends BaseUpdateLinkRequest
{
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $validator->errors(),
                'status' => 422
            ], 422));
    }
}

