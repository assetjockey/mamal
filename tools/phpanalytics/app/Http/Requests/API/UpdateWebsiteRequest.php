<?php

namespace App\Http\Requests\API;

use App\Http\Requests\UpdateWebsiteRequest as BaseUpdateWebsiteRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateWebsiteRequest extends BaseUpdateWebsiteRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $validator->errors(),
                'status' => 422
            ], 422));
    }
}

