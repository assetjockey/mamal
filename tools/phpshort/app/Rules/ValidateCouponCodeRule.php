<?php

namespace App\Rules;

use Closure;
use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateCouponCodeRule implements ValidationRule
{
    /**
     * The plan ID to validate the coupon against.
     */
    private string $planId;

    /**
     * Create a new rule instance.
     */
    public function __construct(string $planId)
    {
        $this->planId = $planId;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $coupon = Coupon::where('code', '=', $value)->first();

        // If the coupon exists
        if ($coupon) {
            // If the coupon quantity is unlimited, or higher than the number of redeems
            if ($coupon->quantity == -1 || $coupon->quantity > $coupon->redeems) {
                $plan = Plan::where('id', '=', $this->planId)->notDefault()->firstOrFail();

                // If the coupon is not under the selected plan
                if ($plan->coupons && !in_array($coupon->id, $plan->coupons ?? [])) {
                    $fail(__('The coupon code could not be found.'));
                }
            } else {
                $fail(__('The coupon code has expired.'));
            }
        } else {
            $fail(__('The coupon code could not be found.'));
        }
    }
}
