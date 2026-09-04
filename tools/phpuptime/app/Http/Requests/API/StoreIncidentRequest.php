<?php

namespace App\Http\Requests\API;

use App\Http\Requests\StoreIncidentRequest as BaseStoreIncidentRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreIncidentRequest extends BaseStoreIncidentRequest
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

