<?php

namespace Modules\AppQRBulk\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Bulk QR Generation')]
class BulkIndex extends Component
{
    public string $csv = "Landing Page,dynamic_url,https://example.com\nMenu QR,restaurant_menu,https://example.com/menu";

    public int $createdCount = 0;

    public function generate(): void
    {
        abort_unless($this->canUseBulkGeneration(), 403);

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $types = collect(AppQrCode::typeCatalog())->keyBy('key');
        $rows = $this->csvRows();
        $bulkLimit = $this->bulkRowLimit();

        if ($bulkLimit >= 0 && count($rows) > $bulkLimit) {
            $this->dispatch('app-toast', type: 'warning', message: __('Bulk row limit reached. Reduce the CSV rows or upgrade your plan.'));

            return;
        }

        $validRows = collect($rows)
            ->map(function (string $line) use ($types): ?array {
                $columns = str_getcsv($line);
                $name = trim((string) ($columns[0] ?? ''));
                $type = trim((string) ($columns[1] ?? 'dynamic_url'));
                $url = trim((string) ($columns[2] ?? ''));

                if ($name === '' || ! $types->has($type) || ! filter_var($url, FILTER_VALIDATE_URL)) {
                    return null;
                }

                return compact('name', 'type', 'url');
            })
            ->filter()
            ->values();

        if ($validRows->isEmpty()) {
            $this->dispatch('app-toast', type: 'warning', message: __('No valid QR rows found. Check name, type key, and URL columns.'));

            return;
        }

        if (! $this->canCreateQrCount($ownerId, $validRows->count())) {
            $this->dispatch('app-toast', type: 'warning', message: __('QR campaign limit reached. Upgrade your plan to create more QR codes.'));

            return;
        }

        $created = 0;

        foreach ($validRows as $row) {
            $kind = (string) $types->get($row['type'])['kind'];
            AppQrCode::query()->create([
                'owner_user_id' => $ownerId,
                'team_id' => $team?->id,
                'type' => $row['type'],
                'name' => $row['name'],
                'status' => 'active',
                'destination_url' => $row['url'],
                'short_code' => $kind === __('dynamic') ? AppQrCode::uniqueShortCode($row['name']) : null,
                'foreground_color' => '#0f172a',
                'background_color' => '#ffffff',
                'pattern' => 'square',
                'settings' => ['kind' => $kind],
            ]);
            $created++;
        }

        $this->createdCount = $created;
        $this->dispatch('app-toast', type: 'success', message: __('Bulk QR generation completed.'));
    }

    public function render(): View
    {
        abort_unless($this->canUseBulkGeneration(), 403);

        return view('appqrbulk::index', [
            'types' => AppQrCode::typeCatalog(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Bulk QR Generation')]);
    }

    protected function canUseBulkGeneration(): bool
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;

        return ! $planOwner?->plan || ($planOwner?->canUsePlanFeature('qr_bulk_generation') ?? false);
    }

    protected function csvRows(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', trim($this->csv)) ?: [])
            ->map(fn ($line): string => trim((string) $line))
            ->filter()
            ->values()
            ->all();
    }

    protected function bulkRowLimit(): int
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
        $limit = $planOwner?->planLimit('max_qr_bulk_rows', -1);

        if (! $planOwner?->plan || ! is_numeric($limit)) {
            return -1;
        }

        return (int) $limit;
    }

    protected function canCreateQrCount(int $ownerId, int $incomingCount): bool
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
        $limit = $planOwner?->planLimit('max_qr_codes', -1);

        if (! $planOwner?->plan || ! is_numeric($limit) || (int) $limit < 0) {
            return true;
        }

        $currentCount = AppQrCode::query()
            ->where('owner_user_id', $ownerId)
            ->count();

        return $currentCount + $incomingCount <= (int) $limit;
    }
}
