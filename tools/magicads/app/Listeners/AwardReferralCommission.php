<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Models\Referral;
use App\Models\ReferralConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Converts a completed order placed by a referred user into a commission for
 * the referrer.
 *
 * Fires off {@see OrderCompleted}, which is dispatched for both online
 * payments and admin-approved offline orders — so commissions accrue the
 * moment an order actually completes, never on a pending bank transfer.
 *
 * Data model (mirrors the admin affiliate views):
 *   - A pending `referrals` row is created at signup (commission/payment = 0,
 *     order_id = null) by {@see \App\Concerns\ResolvesReferrals}.
 *   - The FIRST qualifying order fills that open row in place.
 *   - Under the "all orders" policy, every subsequent order adds a new row.
 *   - `referrer_id` / `referred_id` hold the numeric users.id.
 *
 * Commission policy is read live from {@see ReferralConfiguration}:
 *   - payment_policy   : "first" (first paid order only) | "all" (every order)
 *   - payment_commission: percentage of the order price
 *   - payment_credits  : optional bonus credits granted to the referrer
 *
 * The handler is idempotent per order_id, so webhook retries that re-dispatch
 * OrderCompleted never double-award.
 */
class AwardReferralCommission
{
    public function handle(OrderCompleted $event): void
    {
        try {
            $this->award($event->order);
        } catch (\Throwable $e) {
            // Never let affiliate bookkeeping break the payment flow.
            Log::error('Failed to award referral commission for order '
                . ($event->order->order_id ?? '?') . ': ' . $e->getMessage());
        }
    }

    protected function award(\App\Models\Order $order): void
    {
        // Only completed, actually-paid orders qualify.
        if ($order->status !== 'completed' || (float) $order->price <= 0) {
            return;
        }

        $config = ReferralConfiguration::query()->first();

        if (! $config || ! $config->enabled) {
            return;
        }

        // The buyer must have been referred by someone.
        $buyer = $order->user ?: User::find($order->user_id);

        if (! $buyer || blank($buyer->referred_by)) {
            return;
        }

        $referrer = User::query()
            ->where('referral_id', $buyer->referred_by)
            ->first();

        if (! $referrer || $referrer->id === $buyer->id) {
            return;
        }

        // Idempotency: this exact order has already been credited.
        $alreadyForOrder = Referral::query()
            ->where('referred_id', $buyer->id)
            ->where('order_id', $order->order_id)
            ->exists();

        if ($alreadyForOrder) {
            return;
        }

        $policy = $config->payment_policy === 'all' ? 'all' : 'first';

        // "First order only": bail if this referred user has already produced
        // a commissioned (order-attached) referral row.
        if ($policy === 'first') {
            $hasPaidReferral = Referral::query()
                ->where('referred_id', $buyer->id)
                ->whereNotNull('order_id')
                ->exists();

            if ($hasPaidReferral) {
                return;
            }
        }

        $rate       = (float) ($config->payment_commission ?? 0);
        $payment    = (float) $order->price;
        $commission = round($payment * $rate / 100, 2);

        $attributes = [
            'referrer_id'    => $referrer->id,
            'referrer_email' => $referrer->email,
            'referred_id'    => $buyer->id,
            'referred_email' => $buyer->email,
            'rate'           => (int) round($rate),
            'order_id'       => $order->order_id,
            'payment'        => $payment,
            'commission'     => $commission,
            'status'         => 'approved',
            'gateway'        => $order->gateway,
            'order_date'     => $order->created_at,
        ];

        // Reuse the open signup row for the first conversion; create a fresh
        // row for additional orders under the "all" policy.
        $openRow = Referral::query()
            ->where('referred_id', $buyer->id)
            ->whereNull('order_id')
            ->first();

        if ($openRow) {
            $openRow->update($attributes);
        } else {
            Referral::create($attributes);
        }

        // Optional one-off bonus credits granted to the referrer on conversion.
        $bonusCredits = (int) ($config->payment_credits ?? 0);

        if ($bonusCredits > 0) {
            $referrer->forceFill([
                'credits' => (int) ($referrer->credits ?? 0) + $bonusCredits,
            ])->save();
        }
    }
}
