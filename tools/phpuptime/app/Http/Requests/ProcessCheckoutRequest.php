<?php

namespace App\Http\Requests;

use App\Rules\ValidateCouponCodeRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'payment_processor' => ['required', 'in:' . implode(',', array_keys(enabledPaymentProcessors()))],
            'interval' => ['required', 'in:month,year'],
            'name' => ['required'],
            'address' => ['required'],
            'city' => ['required'],
            'postal_code' => ['required'],
            'country' => ['required'],
            'coupon' => ['sometimes', 'min:1', new ValidateCouponCodeRule($this->route('id'))],
            'payment_id' => ['required_if:payment_processor,bank', 'alpha_num', 'min:1', 'max:128', 'unique:payments,payment_id']
        ];
    }
}
