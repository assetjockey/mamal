<?php

namespace App\Concerns;

use App\Models\Referral;
use App\Models\ReferralConfiguration;
use App\Models\User;

/**
 * Shared referral-attribution logic used by every signup path (standard
 * Fortify registration and social/OAuth registration).
 *
 * Keys mirror the admin affiliate views:
 *   - referrals.referrer_id / referred_id hold the numeric users.id
 *   - referred_by on the user row holds the referrer's public referral_id code
 */
trait ResolvesReferrals
{
    /**
     * Resolve the referring user from a referral code, but only when the
     * affiliate program is enabled. Unknown codes resolve to null so signup
     * proceeds without attribution.
     */
    protected function resolveReferrer(?string $code): ?User
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        $config = ReferralConfiguration::query()->first();

        if (! $config || ! $config->enabled) {
            return null;
        }

        return User::query()
            ->where('referral_id', $code)
            ->first();
    }

    /**
     * Persist a pending referral row linking referrer → newly registered user.
     * Commission/payment stay zero until the referred user makes a purchase.
     * Guards against self-referral and duplicate rows.
     */
    protected function recordReferral(User $referrer, User $referred): void
    {
        if ($referrer->id === $referred->id) {
            return;
        }

        $exists = Referral::query()
            ->where('referred_id', $referred->id)
            ->exists();

        if ($exists) {
            return;
        }

        $rate = (int) round((float) (ReferralConfiguration::query()->value('payment_commission') ?? 0));

        Referral::create([
            'referrer_id'    => $referrer->id,
            'referrer_email' => $referrer->email,
            'referred_id'    => $referred->id,
            'referred_email' => $referred->email,
            'rate'           => $rate,
            'commission'     => 0,
            'payment'        => 0,
            'status'         => 'pending',
        ]);
    }
}
