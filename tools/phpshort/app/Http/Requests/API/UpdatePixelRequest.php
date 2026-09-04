<?php

namespace App\Http\Requests\API;

use App\Http\Requests\UpdatePixelRequest as BaseUpdatePixelRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdatePixelRequest extends BaseUpdatePixelRequest
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

