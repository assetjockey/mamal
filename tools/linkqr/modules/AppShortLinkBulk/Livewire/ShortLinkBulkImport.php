<?php

namespace Modules\AppShortLinkBulk\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Bulk Import Short Links')]
class ShortLinkBulkImport extends Component
{
    use WithFileUploads;

    public string $csv = "name,destination_url,custom_code\nSpring launch,https://example.com/spring,spring-launch\nSupport,https://example.com/support,";

    public string $custom_domain_id = '';

    public string $utm_preset_id = '';

    public array $tracking_pixel_ids = [];

    public int $createdCount = 0;

    public array $errorsPreview = [];

    public ?TemporaryUploadedFile $uploadedCsv = null;

    public array $lastCreatedIds = [];

    public function mount(): void
    {
        $branding = app(ShortLinkBranding::class);
        $defaultUtmPresetId = $branding->defaultUtmPreset($this->ownerId())?->id;
        $this->utm_preset_id = $defaultUtmPresetId ? (string) $defaultUtmPresetId : '';
        $this->tracking_pixel_ids = $branding->defaultPixelIds($this->ownerId());
    }

    public function loadUploadedCsv(): void
    {
        $this->validate([
            'uploadedCsv' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $this->csv = trim((string) file_get_contents($this->uploadedCsv->getRealPath()));
        $this->uploadedCsv = null;
        $this->createdCount = 0;
        $this->errorsPreview = [];
        $this->lastCreatedIds = [];
        $this->dispatch('app-toast', type: 'success', message: __('CSV loaded. Review the preview before importing.'));
    }

    public function import(): void
    {
        $rows = $this->parseRows();

        if ($rows === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No valid CSV rows found.'));

            return;
        }

        $limits = app(ShortLinkPlanLimits::class);

        if (! $limits->canUseFeature(auth()->user(), 'short_link_bulk_import')) {
            $this->dispatch('app-toast', type: 'warning', message: __('Bulk import is not included in your plan.'));

            return;
        }

        if (! $limits->canCreateBulkRows(auth()->user(), count($rows))) {
            $this->dispatch('app-toast', type: 'warning', message: __('Bulk import row limit reached for your plan.'));

            return;
        }

        $this->validate([
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
        ]);

        $created = 0;
        $errors = [];
        $createdIds = [];

        foreach ($rows as $index => $row) {
            if (! $limits->canCreateLink(auth()->user(), $this->ownerId())) {
                $errors[] = __('Row :row skipped: short link limit reached.', ['row' => $index + 2]);
                break;
            }

            $validator = validator($row, [
                'name' => ['required', 'string', 'max:160'],
                'destination_url' => ['required', 'url', 'max:2048'],
                'custom_code' => ['nullable', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')],
                'password' => ['nullable', 'string', 'min:4', 'max:255'],
            ]);

            if ($validator->fails()) {
                $errors[] = __('Row :row skipped: :error', ['row' => $index + 2, 'error' => $validator->errors()->first()]);
                continue;
            }

            $validated = $validator->validated();
            $code = trim((string) ($validated['custom_code'] ?? ''));

            $shortLink = AppShortLink::query()->create([
                'owner_user_id' => $this->ownerId(),
                'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
                'custom_domain_id' => $this->custom_domain_id ?: null,
                'utm_preset_id' => $this->utm_preset_id ?: null,
                'tracking_pixel_ids' => $this->cleanIds($this->tracking_pixel_ids),
                'name' => $validated['name'],
                'destination_url' => $validated['destination_url'],
                'short_code' => $code !== '' ? str($code)->lower()->value() : AppShortLink::uniqueShortCode($validated['name']),
                'status' => 'active',
                'password_hash' => filled($validated['password'] ?? null) ? Hash::make((string) $validated['password']) : null,
            ]);

            $created++;
            $createdIds[] = (int) $shortLink->id;
        }

        $this->createdCount = $created;
        $this->lastCreatedIds = $createdIds;
        $this->errorsPreview = array_slice($errors, 0, 8);
        $this->dispatch('app-toast', type: $created > 0 ? 'success' : 'warning', message: __('Created :count short links.', ['count' => $created]));
    }

    public function rollbackLastImport(): void
    {
        if ($this->lastCreatedIds === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No recent import batch to roll back.'));

            return;
        }

        $deleted = AppShortLink::query()
            ->where('owner_user_id', $this->ownerId())
            ->whereIn('id', $this->lastCreatedIds)
            ->delete();

        $this->lastCreatedIds = [];
        $this->createdCount = 0;
        $this->dispatch('app-toast', type: 'success', message: __('Rolled back :count imported links.', ['count' => $deleted]));
    }

    public function render(): View
    {
        $branding = app(ShortLinkBranding::class);
        $rows = $this->parseRows();

        return view('appshortlinkbulk::index', [
            'domains' => $branding->domains($this->ownerId()),
            'utmPresets' => $branding->utmPresets($this->ownerId()),
            'pixels' => $branding->pixels($this->ownerId()),
            'rowCount' => count($rows),
            'previewRows' => array_slice($rows, 0, 8),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Bulk Import Short Links')]);
    }

    protected function parseRows(): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($this->csv)) ?: [];

        if (count($lines) < 2) {
            return [];
        }

        $headers = array_map(fn ($header): string => str((string) $header)->trim()->lower()->value(), str_getcsv(array_shift($lines)));
        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line);
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            $rows[] = [
                'name' => $row['name'] ?? '',
                'destination_url' => $row['destination_url'] ?? '',
                'custom_code' => $row['custom_code'] ?? '',
                'password' => $row['password'] ?? '',
            ];
        }

        return $rows;
    }

    protected function ownerId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function cleanIds(array $ids): array
    {
        return collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
    }
}
