<?php

namespace App\Http\Requests\API;

use App\Http\Requests\StoreStatusPageRequest as BaseStoreStatusPageRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreStatusPageRequest extends BaseStoreStatusPageRequest
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

