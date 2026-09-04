<?php

namespace Modules\AppShortLinkAccess\Support;

use Carbon\CarbonImmutable;
use Modules\AdminUser\Models\User;
use Modules\AppShortLinkApi\Models\AppShortLinkApiKey;
use Modules\AppShortLinkApi\Models\AppShortLinkWebhook;
use Modules\AppShortLinkAnalytics\Models\AppShortLinkClick;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class ShortLinkPlanLimits
{
    public function usageSummary(mixed $user, int $ownerUserId): array
    {
        $linksUsed = AppShortLink::query()->where('owner_user_id', $ownerUserId)->count();
        $clicksUsed = AppShortLinkClick::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('created_at', '>=', CarbonImmutable::now()->startOfMonth())
            ->count();

        return [
            'links_used' => $linksUsed,
            'links_limit' => $this->limit($user, 'max_short_links', -1),
            'monthly_clicks_used' => $clicksUsed,
            'monthly_clicks_limit' => $this->limit($user, 'max_short_link_clicks_monthly', -1),
            'bulk_rows_limit' => $this->limit($user, 'max_short_link_bulk_rows', 100),
            'api_keys_limit' => $this->limit($user, 'max_short_link_api_keys', 2),
            'webhooks_limit' => $this->limit($user, 'max_short_link_webhooks', 2),
        ];
    }

    public function canUseFeature(mixed $user, string $key): bool
    {
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;

        return ! $planOwner?->plan || ($planOwner?->canUsePlanFeature($key) ?? false);
    }

    public function canCreateLink(mixed $user, int $ownerUserId): bool
    {
        $limit = $this->limit($user, 'max_short_links', -1);

        if ($limit === null) {
            return true;
        }

        return AppShortLink::query()->where('owner_user_id', $ownerUserId)->count() < $limit;
    }

    public function canCreateBulkRows(mixed $user, int $rows): bool
    {
        $limit = $this->limit($user, 'max_short_link_bulk_rows', -1);

        return $limit === null || $rows <= $limit;
    }

    public function canRecordClick(mixed $user, int $ownerUserId): bool
    {
        $limit = $this->limit($user, 'max_short_link_clicks_monthly', -1);

        if ($limit === null) {
            return true;
        }

        return AppShortLinkClick::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('created_at', '>=', CarbonImmutable::now()->startOfMonth())
            ->count() < $limit;
    }

    public function apiKeysLimit(mixed $user): ?int
    {
        return $this->limit($user, 'max_short_link_api_keys', 2);
    }

    public function webhooksLimit(mixed $user): ?int
    {
        return $this->limit($user, 'max_short_link_webhooks', 2);
    }

    public function canCreateApiKey(mixed $user, int $ownerUserId): bool
    {
        $limit = $this->apiKeysLimit($user);

        if ($limit === null) {
            return true;
        }

        return AppShortLinkApiKey::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('is_active', true)
            ->count() < $limit;
    }

    public function canCreateWebhook(mixed $user, int $ownerUserId): bool
    {
        $limit = $this->webhooksLimit($user);

        if ($limit === null) {
            return true;
        }

        return AppShortLinkWebhook::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('is_active', true)
            ->count() < $limit;
    }

    public function canUseFeatureForOwner(int $ownerUserId, string $key): bool
    {
        return $this->canUseFeature(User::query()->find($ownerUserId), $key);
    }

    public function canCreateLinkForOwner(int $ownerUserId): bool
    {
        return $this->canCreateLink(User::query()->find($ownerUserId), $ownerUserId);
    }

    protected function limit(mixed $user, string $key, int $default): ?int
    {
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
        $limit = $planOwner?->planLimit($key, $default);

        if (! $planOwner?->plan || ! is_numeric($limit) || (int) $limit < 0) {
            return null;
        }

        return (int) $limit;
    }
}
