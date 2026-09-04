<?php

namespace Modules\AppQRCodes\Support;

use Carbon\CarbonImmutable;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class QrPlanLimits
{
    public function summary(mixed $user, int $ownerUserId): array
    {
        $qrCount = AppQrCode::query()->where('owner_user_id', $ownerUserId)->count();
        $monthlyScans = AppQrScanEvent::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('created_at', '>=', CarbonImmutable::now()->startOfMonth())
            ->count();
        $maxRulesUsed = AppQrCode::query()
            ->where('owner_user_id', $ownerUserId)
            ->get(['settings'])
            ->map(fn (AppQrCode $qrCode): int => count((array) data_get($qrCode->settings, 'dynamic_rules', [])))
            ->max() ?? 0;

        return [
            [
                'key' => 'max_qr_codes',
                'label' => __('QR codes'),
                'description' => __('Campaigns in this workspace'),
                'used' => $qrCount,
                'limit' => $this->limit($user, 'max_qr_codes', -1),
                'icon' => 'fa-light fa-qrcode',
                'color' => '#2563eb',
            ],
            [
                'key' => 'max_qr_scans_monthly',
                'label' => __('Monthly QR scans'),
                'description' => __('Recorded analytics this month'),
                'used' => $monthlyScans,
                'limit' => $this->limit($user, 'max_qr_scans_monthly', -1),
                'icon' => 'fa-light fa-chart-line',
                'color' => '#0f766e',
            ],
            [
                'key' => 'max_dynamic_rules_per_qr',
                'label' => __('Dynamic rules per QR'),
                'description' => __('Highest rules used on one QR'),
                'used' => $maxRulesUsed,
                'limit' => $this->limit($user, 'max_dynamic_rules_per_qr', 10),
                'icon' => 'fa-light fa-route',
                'color' => '#7c3aed',
            ],
            [
                'key' => 'max_qr_bulk_rows',
                'label' => __('Bulk rows per import'),
                'description' => __('Validated before QR creation'),
                'used' => null,
                'limit' => $this->limit($user, 'max_qr_bulk_rows', -1),
                'icon' => 'fa-light fa-table-list',
                'color' => '#d97706',
            ],
        ];
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
