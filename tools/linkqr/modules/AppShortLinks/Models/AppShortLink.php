<?php

namespace Modules\AppShortLinks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\AppShortLinkAccess\Support\ShortLinkAccess;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;

class AppShortLink extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'custom_domain_id',
        'utm_preset_id',
        'tracking_pixel_ids',
        'name',
        'folder',
        'campaign',
        'tags',
        'destination_url',
        'short_code',
        'status',
        'expires_at',
        'click_limit',
        'password_hash',
        'clicks_count',
        'last_clicked_at',
        'og_title',
        'og_description',
        'og_image',
        'settings',
        'redirect_rules',
        'moderation_status',
        'moderation_note',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'custom_domain_id' => 'integer',
            'utm_preset_id' => 'integer',
            'tracking_pixel_ids' => 'array',
            'tags' => 'array',
            'expires_at' => 'datetime',
            'click_limit' => 'integer',
            'clicks_count' => 'integer',
            'last_clicked_at' => 'datetime',
            'settings' => 'array',
            'redirect_rules' => 'array',
        ];
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(AppShortLinkClick::class);
    }

    public function shortUrl(): string
    {
        return app(ShortLinkBranding::class)->shortUrl($this);
    }

    public function isExpired(): bool
    {
        return app(ShortLinkAccess::class)->isExpired($this);
    }

    public function isClickLimitReached(): bool
    {
        return app(ShortLinkAccess::class)->isClickLimitReached($this);
    }

    public function canRedirect(): bool
    {
        return app(ShortLinkAccess::class)->canRedirect($this);
    }

    public static function uniqueShortCode(string $seed = ''): string
    {
        $alphabet = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $code = collect(range(1, 6))
                ->map(fn (): string => $alphabet[random_int(0, strlen($alphabet) - 1)])
                ->implode('');
        } while (self::query()->where('short_code', $code)->exists());

        return $code;
    }
}
