<?php

namespace Modules\AppShortLinkAccess\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkAccess
{
    public function canRedirect(AppShortLink $shortLink): bool
    {
        return $shortLink->status === 'active'
            && ($shortLink->moderation_status ?? 'approved') === 'approved'
            && ! $this->isExpired($shortLink)
            && ! $this->isClickLimitReached($shortLink);
    }

    public function isExpired(AppShortLink $shortLink): bool
    {
        return $shortLink->expires_at !== null && $shortLink->expires_at->isPast();
    }

    public function isClickLimitReached(AppShortLink $shortLink): bool
    {
        return $shortLink->click_limit !== null
            && $shortLink->click_limit > 0
            && $shortLink->clicks_count >= $shortLink->click_limit;
    }

    public function isUnlocked(Request $request, AppShortLink $shortLink): bool
    {
        return ! $shortLink->password_hash || $request->session()->has($this->passwordSessionKey($shortLink));
    }

    public function unlock(Request $request, AppShortLink $shortLink, string $password): bool
    {
        if (! Hash::check($password, (string) $shortLink->password_hash)) {
            return false;
        }

        $request->session()->put($this->passwordSessionKey($shortLink), true);

        return true;
    }

    public function passwordSessionKey(AppShortLink $shortLink): string
    {
        return 'short_link_unlocked_'.$shortLink->id;
    }
}
