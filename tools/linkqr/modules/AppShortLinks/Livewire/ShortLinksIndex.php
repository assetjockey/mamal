<?php

namespace Modules\AppShortLinks\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppQRCodes\Support\GradientRoundedQrRenderer;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkApi\Support\ShortLinkWebhookDispatcher;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinkQr\Support\ShortLinkQrRenderer;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('URL Shortener')]
class ShortLinksIndex extends Component
{
    use WithPagination;

    public array $form = [
        'name' => '',
        'folder' => '',
        'campaign' => '',
        'tags' => '',
        'destination_url' => '',
        'custom_code' => '',
        'custom_domain_id' => '',
        'utm_preset_id' => '',
        'tracking_pixel_ids' => [],
        'expires_at' => '',
        'click_limit' => '',
        'password' => '',
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'redirect_rules' => '',
    ];

    public array $editForm = [
        'name' => '',
        'folder' => '',
        'campaign' => '',
        'tags' => '',
        'destination_url' => '',
        'custom_code' => '',
        'custom_domain_id' => '',
        'utm_preset_id' => '',
        'tracking_pixel_ids' => [],
        'status' => 'active',
        'expires_at' => '',
        'click_limit' => '',
        'password' => '',
        'clear_password' => false,
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'redirect_rules' => '',
    ];

    public array $previewForm = [
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
    ];

    public ?int $editingId = null;

    public ?int $previewEditingId = null;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $sort = 'latest';

    #[Url(as: 'view', history: true, keep: true)]
    public string $viewMode = 'list';

    public ?int $selectedLinkId = null;

    public ?int $qrModalLinkId = null;

    public array $selectedLinkIds = [];

    public bool $copyAfterCreate = true;

    public ?string $createdUrl = null;

    public int $perPage = 25;

    public function mount(): void
    {
        $storedViewMode = request()->query('view') ?: session('app_short_links.view_mode');

        if (is_string($storedViewMode) && in_array($storedViewMode, ['list', 'detail', 'grid'], true)) {
            $this->viewMode = $storedViewMode;
        }

        $this->resetCreateForm();
    }

    public function updatedViewMode(string $value): void
    {
        if (! in_array($value, ['list', 'detail', 'grid'], true)) {
            $this->viewMode = 'list';
        }

        session(['app_short_links.view_mode' => $this->viewMode]);
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['list', 'detail', 'grid'], true)) {
            return;
        }

        $this->viewMode = $mode;
        session(['app_short_links.view_mode' => $mode]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 25, 50, 100], true) ? $this->perPage : 25;
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSelectedLinkIds(): void
    {
        $this->selectedLinkIds = $this->normalizedSelectedLinkIds();
    }

    public function fetchCreateMetadata(): void
    {
        $this->fillMetadata('form');
    }

    public function fetchEditMetadata(): void
    {
        $this->fillMetadata('editForm');
    }

    public function generateCreateCode(): void
    {
        $this->form['custom_code'] = $this->suggestShortCode(
            (string) ($this->form['name'] ?? ''),
            (string) ($this->form['destination_url'] ?? ''),
        );
    }

    public function generateEditCode(): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->editForm['custom_code'] = $this->suggestShortCode(
            (string) ($this->editForm['name'] ?? ''),
            (string) ($this->editForm['destination_url'] ?? ''),
            $this->editingId,
        );
    }

    public function addLink(): void
    {
        if (! app(ShortLinkPlanLimits::class)->canCreateLink(auth()->user(), $this->ownerId())) {
            $this->dispatch('app-toast', type: 'warning', message: __('Short link limit reached. Upgrade your plan to create more links.'));

            return;
        }

        $validated = validator($this->form, [
            'name' => ['nullable', 'string', 'max:160'],
            'folder' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:160'],
            'tags' => ['nullable', 'string', 'max:500'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_code' => ['nullable', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')],
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'click_limit' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'redirect_rules' => ['nullable', 'json'],
        ])->validate();

        $code = trim((string) ($validated['custom_code'] ?? ''));
        $name = trim((string) ($validated['name'] ?? ''));

        if ($name === '') {
            $host = parse_url((string) $validated['destination_url'], PHP_URL_HOST);
            $name = $host ? __('Link :host', ['host' => $host]) : __('Untitled link');
        }

        $shortLink = AppShortLink::query()->create([
            'owner_user_id' => $this->ownerId(),
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
            'custom_domain_id' => $validated['custom_domain_id'] ?: null,
            'utm_preset_id' => $validated['utm_preset_id'] ?: null,
            'tracking_pixel_ids' => $this->cleanIds($validated['tracking_pixel_ids'] ?? []),
            'name' => $name,
            'folder' => $validated['folder'] ?: null,
            'campaign' => $validated['campaign'] ?: null,
            'tags' => $this->parseTags((string) ($validated['tags'] ?? '')),
            'destination_url' => $validated['destination_url'],
            'short_code' => $code !== '' ? str($code)->lower()->value() : AppShortLink::uniqueShortCode($name),
            'status' => 'active',
            'expires_at' => $validated['expires_at'] ?: null,
            'click_limit' => $validated['click_limit'] ?: null,
            'password_hash' => filled($validated['password'] ?? null) ? Hash::make((string) $validated['password']) : null,
            'og_title' => $validated['og_title'] ?: null,
            'og_description' => $validated['og_description'] ?: null,
            'og_image' => $validated['og_image'] ?: null,
            'redirect_rules' => filled($validated['redirect_rules'] ?? null) ? json_decode((string) $validated['redirect_rules'], true) : null,
        ]);

        $this->createdUrl = $shortLink->shortUrl();
        $this->resetCreateForm();
        app(ShortLinkWebhookDispatcher::class)->dispatch($this->ownerId(), 'short_link.created', $shortLink);
        $this->dispatch('short-link-created', url: $this->createdUrl, copy: $this->copyAfterCreate);
        $this->dispatch('app-toast', type: 'success', message: __('Short link created.'));
    }

    public function duplicateLink(int $linkId): void
    {
        if (! app(ShortLinkPlanLimits::class)->canCreateLink(auth()->user(), $this->ownerId())) {
            $this->dispatch('app-toast', type: 'warning', message: __('Short link limit reached. Upgrade your plan to duplicate links.'));

            return;
        }

        $link = $this->ownedLink($linkId);
        $copy = $link->replicate([
            'short_code',
            'clicks_count',
            'last_clicked_at',
            'created_at',
            'updated_at',
        ]);
        $copy->name = __('Copy of :name', ['name' => $link->name]);
        $copy->short_code = AppShortLink::uniqueShortCode((string) $copy->name);
        $copy->clicks_count = 0;
        $copy->last_clicked_at = null;
        $copy->save();

        $this->dispatch('app-toast', type: 'success', message: __('Short link duplicated.'));
    }

    public function selectLink(int $linkId): void
    {
        $this->selectedLinkId = $this->ownedLink($linkId)->id;
        $this->viewMode = 'detail';
    }

    public function openQrModal(int $linkId): void
    {
        $this->qrModalLinkId = $this->ownedLink($linkId)->id;
        $this->dispatch('short-link-qr-modal-open');
    }

    public function closeQrModal(): void
    {
        $this->qrModalLinkId = null;
    }

    public function beginEdit(int $linkId): void
    {
        $link = $this->ownedLink($linkId);
        $this->editingId = (int) $link->id;
        $this->editForm = [
            'name' => (string) $link->name,
            'folder' => (string) $link->folder,
            'campaign' => (string) $link->campaign,
            'tags' => implode(', ', (array) $link->tags),
            'destination_url' => (string) $link->destination_url,
            'custom_code' => (string) $link->short_code,
            'custom_domain_id' => (string) ($link->custom_domain_id ?: ''),
            'utm_preset_id' => (string) ($link->utm_preset_id ?: ''),
            'tracking_pixel_ids' => (array) ($link->tracking_pixel_ids ?: []),
            'status' => (string) $link->status,
            'expires_at' => $link->expires_at?->format('Y-m-d\TH:i') ?: '',
            'click_limit' => $link->click_limit ? (string) $link->click_limit : '',
            'password' => '',
            'clear_password' => false,
            'og_title' => (string) $link->og_title,
            'og_description' => (string) $link->og_description,
            'og_image' => (string) $link->og_image,
            'redirect_rules' => $link->redirect_rules ? json_encode($link->redirect_rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '',
        ];
    }

    public function beginPreviewEdit(int $linkId): void
    {
        $link = $this->ownedLink($linkId);
        $this->previewEditingId = (int) $link->id;
        $this->previewForm = [
            'og_title' => (string) $link->og_title,
            'og_description' => (string) $link->og_description,
            'og_image' => (string) $link->og_image,
        ];
    }

    public function savePreviewEdit(): void
    {
        if (! $this->previewEditingId) {
            return;
        }

        $validated = validator($this->previewForm, [
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
        ])->validate();

        $this->ownedLink($this->previewEditingId)->update([
            'og_title' => $validated['og_title'] ?: null,
            'og_description' => $validated['og_description'] ?: null,
            'og_image' => $validated['og_image'] ?: null,
        ]);

        $this->previewEditingId = null;
        $this->dispatch('app-toast', type: 'success', message: __('Link preview updated.'));
    }

    public function cancelPreviewEdit(): void
    {
        $this->previewEditingId = null;
        $this->previewForm = [
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
        ];
    }

    public function fetchSelectedPreviewMetadata(): void
    {
        if (! $this->previewEditingId) {
            return;
        }

        $link = $this->ownedLink($this->previewEditingId);
        $metadata = $this->fetchMetadata((string) $link->destination_url);

        if ($metadata === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No metadata found for this URL.'));

            return;
        }

        foreach (['og_title' => 'title', 'og_description' => 'description', 'og_image' => 'image'] as $field => $key) {
            if (filled($metadata[$key] ?? null)) {
                $this->previewForm[$field] = $metadata[$key];
            }
        }

        $this->dispatch('app-toast', type: 'success', message: __('Link preview metadata fetched.'));
    }

    public function saveEdit(): void
    {
        if (! $this->editingId) {
            return;
        }

        $link = $this->ownedLink($this->editingId);
        $validated = validator($this->editForm, [
            'name' => ['required', 'string', 'max:160'],
            'folder' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:160'],
            'tags' => ['nullable', 'string', 'max:500'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_code' => ['required', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')->ignore($link->id)],
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
            'status' => ['required', 'in:active,paused'],
            'expires_at' => ['nullable', 'date'],
            'click_limit' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'clear_password' => ['boolean'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'redirect_rules' => ['nullable', 'json'],
        ])->validate();

        $payload = [
            'name' => $validated['name'],
            'folder' => $validated['folder'] ?: null,
            'campaign' => $validated['campaign'] ?: null,
            'tags' => $this->parseTags((string) ($validated['tags'] ?? '')),
            'destination_url' => $validated['destination_url'],
            'short_code' => str((string) $validated['custom_code'])->lower()->value(),
            'custom_domain_id' => $validated['custom_domain_id'] ?: null,
            'utm_preset_id' => $validated['utm_preset_id'] ?: null,
            'tracking_pixel_ids' => $this->cleanIds($validated['tracking_pixel_ids'] ?? []),
            'status' => $validated['status'],
            'expires_at' => $validated['expires_at'] ?: null,
            'click_limit' => $validated['click_limit'] ?: null,
            'og_title' => $validated['og_title'] ?: null,
            'og_description' => $validated['og_description'] ?: null,
            'og_image' => $validated['og_image'] ?: null,
            'redirect_rules' => filled($validated['redirect_rules'] ?? null) ? json_decode((string) $validated['redirect_rules'], true) : null,
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password_hash'] = Hash::make((string) $validated['password']);
        } elseif ((bool) ($validated['clear_password'] ?? false)) {
            $payload['password_hash'] = null;
        }

        $link->update($payload);

        $this->cancelEdit();
        $this->dispatch('app-toast', type: 'success', message: __('Short link updated.'));
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editForm = [
            'name' => '',
            'folder' => '',
            'campaign' => '',
            'tags' => '',
            'destination_url' => '',
            'custom_code' => '',
            'custom_domain_id' => '',
            'utm_preset_id' => '',
            'tracking_pixel_ids' => [],
            'status' => 'active',
            'expires_at' => '',
            'click_limit' => '',
            'password' => '',
            'clear_password' => false,
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'redirect_rules' => '',
        ];
    }

    public function toggleStatus(int $linkId): void
    {
        $link = $this->ownedLink($linkId);
        $link->update(['status' => $link->status === 'active' ? 'paused' : 'active']);
    }

    public function applySelectedCustomDomain(string $domainId): void
    {
        if (! $this->selectedLinkId) {
            return;
        }

        $validated = validator(['custom_domain_id' => $domainId ?: null], [
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
        ])->validate();

        $this->ownedLink($this->selectedLinkId)->update([
            'custom_domain_id' => $validated['custom_domain_id'] ?: null,
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('Brand domain applied.'));
    }

    public function applySelectedUtmPreset(string $presetId): void
    {
        if (! $this->selectedLinkId) {
            return;
        }

        $validated = validator(['utm_preset_id' => $presetId ?: null], [
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
        ])->validate();

        $this->ownedLink($this->selectedLinkId)->update([
            'utm_preset_id' => $validated['utm_preset_id'] ?: null,
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('UTM preset applied.'));
    }

    public function toggleSelectedTrackingPixel(int $pixelId): void
    {
        if (! $this->selectedLinkId) {
            return;
        }

        validator(['pixel_id' => $pixelId], [
            'pixel_id' => ['required', 'integer', Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
        ])->validate();

        $link = $this->ownedLink($this->selectedLinkId);
        $ids = collect((array) $link->tracking_pixel_ids)->map(fn ($id): int => (int) $id)->filter();
        $ids = $ids->contains($pixelId) ? $ids->reject(fn (int $id): bool => $id === $pixelId) : $ids->push($pixelId);

        $link->update(['tracking_pixel_ids' => $ids->unique()->values()->all()]);

        $this->dispatch('app-toast', type: 'success', message: __('Tracking pixels updated.'));
    }

    public function applySelectedRedirectTemplate(string $type): void
    {
        if (! $this->selectedLinkId) {
            return;
        }

        $link = $this->ownedLink($this->selectedLinkId);
        $destination = (string) $link->destination_url;
        $variantUrl = str_contains($destination, '?') ? $destination.'&variant=b' : $destination.'?variant=b';

        $rules = match ($type) {
            'rotator' => [
                ['type' => 'ab', 'variant_key' => 'A', 'name' => __('Variant A'), 'url' => $destination, 'weight' => 70, 'status' => 'active', 'winner' => false],
                ['type' => 'ab', 'variant_key' => 'B', 'name' => __('Variant B'), 'url' => $variantUrl, 'weight' => 30, 'status' => 'active', 'winner' => false],
            ],
            'geo' => [
                ['url' => $destination, 'countries' => ['US', 'CA']],
                ['url' => $variantUrl, 'countries' => ['VN']],
            ],
            'device' => [
                ['url' => $destination, 'devices' => ['desktop']],
                ['url' => $variantUrl, 'devices' => ['mobile']],
            ],
            'time' => [
                ['url' => $destination, 'starts_at' => now()->startOfDay()->toIso8601String(), 'ends_at' => now()->endOfDay()->toIso8601String()],
                ['url' => $variantUrl, 'starts_at' => now()->addDay()->startOfDay()->toIso8601String()],
            ],
            default => null,
        };

        if ($rules === null) {
            return;
        }

        $link->update(['redirect_rules' => $rules]);

        $this->dispatch('app-toast', type: 'success', message: __('Traffic routing template applied.'));
    }

    public function clearSelectedRedirectRules(): void
    {
        if (! $this->selectedLinkId) {
            return;
        }

        $this->ownedLink($this->selectedLinkId)->update(['redirect_rules' => null]);

        $this->dispatch('app-toast', type: 'success', message: __('Traffic routing cleared.'));
    }

    public function deleteLink(int $linkId): void
    {
        $this->ownedLink($linkId)->delete();

        if ($this->editingId === $linkId) {
            $this->cancelEdit();
        }

        if ($this->selectedLinkId === $linkId) {
            $this->selectedLinkId = null;
        }

        $this->selectedLinkIds = array_values(array_diff($this->normalizedSelectedLinkIds(), [$linkId]));

        $this->dispatch('app-toast', type: 'success', message: __('Short link deleted.'));
    }

    public function toggleCurrentPageSelection(): void
    {
        $pageIds = $this->currentPageLinkIds();

        if ($pageIds === []) {
            return;
        }

        $selected = $this->normalizedSelectedLinkIds();
        $allSelected = count(array_intersect($pageIds, $selected)) === count($pageIds);

        $this->selectedLinkIds = $allSelected
            ? array_values(array_diff($selected, $pageIds))
            : array_values(array_unique(array_merge($selected, $pageIds)));
    }

    public function selectAllFiltered(): void
    {
        $this->selectedLinkIds = $this->filteredLinksQuery()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        $this->dispatch('app-toast', type: 'success', message: __('All matching short links selected.'));
    }

    public function clearSelection(): void
    {
        $this->selectedLinkIds = [];
    }

    public function deleteSelectedLinks(): void
    {
        $ids = $this->normalizedSelectedLinkIds();

        if ($ids === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('Select at least one short link first.'));

            return;
        }

        $ownedIds = AppShortLink::query()
            ->where('owner_user_id', $this->ownerId())
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($ownedIds === []) {
            $this->clearSelection();

            return;
        }

        AppShortLink::query()
            ->where('owner_user_id', $this->ownerId())
            ->whereIn('id', $ownedIds)
            ->delete();

        if ($this->editingId && in_array($this->editingId, $ownedIds, true)) {
            $this->cancelEdit();
        }

        if ($this->selectedLinkId && in_array($this->selectedLinkId, $ownedIds, true)) {
            $this->selectedLinkId = null;
        }

        $deletedCount = count($ownedIds);
        $this->clearSelection();
        $this->resetPage();
        $this->dispatch('app-toast', type: 'success', message: trans_choice(':count short link deleted.|:count short links deleted.', $deletedCount, ['count' => $deletedCount]));
    }

    public function deleteAllFilteredLinks(): void
    {
        $ids = $this->filteredLinksQuery()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if ($ids === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No short links match the current filters.'));

            return;
        }

        AppShortLink::query()
            ->where('owner_user_id', $this->ownerId())
            ->whereIn('id', $ids)
            ->delete();

        if ($this->editingId && in_array($this->editingId, $ids, true)) {
            $this->cancelEdit();
        }

        if ($this->selectedLinkId && in_array($this->selectedLinkId, $ids, true)) {
            $this->selectedLinkId = null;
        }

        $deletedCount = count($ids);
        $this->clearSelection();
        $this->resetPage();
        $this->dispatch('app-toast', type: 'success', message: trans_choice(':count short link deleted.|:count short links deleted.', $deletedCount, ['count' => $deletedCount]));
    }

    public function render(): View
    {
        $query = $this->filteredLinksQuery();

        /** @var LengthAwarePaginator $links */
        $links = $query->paginate($this->perPage);
        $allLinksQuery = AppShortLink::query()->where('owner_user_id', $this->ownerId());
        $branding = app(ShortLinkBranding::class);
        $pageLinks = collect($links->items());
        $pageLinkIds = $pageLinks->pluck('id')->map(fn ($id): int => (int) $id)->values()->all();
        $selectedIds = $this->normalizedSelectedLinkIds();
        $selectedOnPageCount = count(array_intersect($pageLinkIds, $selectedIds));
        $selectedLink = $pageLinks->firstWhere('id', $this->selectedLinkId) ?: $pageLinks->first();
        $qrModalLink = $this->qrModalLinkId
            ? AppShortLink::query()
                ->where('owner_user_id', $this->ownerId())
                ->whereKey($this->qrModalLinkId)
                ->first()
            : null;

        if ($selectedLink && $this->selectedLinkId !== (int) $selectedLink->id) {
            $this->selectedLinkId = (int) $selectedLink->id;
        }

        return view('appshortlinks::index', [
            'links' => $links,
            'selectedLink' => $selectedLink,
            'qrModalLink' => $qrModalLink,
            'totalLinks' => (clone $allLinksQuery)->count(),
            'totalClicks' => (int) (clone $allLinksQuery)->sum('clicks_count'),
            'activeLinks' => (int) (clone $allLinksQuery)->where('status', 'active')->count(),
            'blockedLinks' => (int) (clone $allLinksQuery)->where('moderation_status', 'blocked')->count(),
            'selectedLinkIds' => $selectedIds,
            'selectedLinksCount' => count($selectedIds),
            'pageLinkIds' => $pageLinkIds,
            'pageAllSelected' => $pageLinkIds !== [] && $selectedOnPageCount === count($pageLinkIds),
            'filteredLinksCount' => (clone $this->filteredLinksQuery())->count(),
            'planUsage' => app(ShortLinkPlanLimits::class)->usageSummary(auth()->user(), $this->ownerId()),
            'domains' => $branding->domains($this->ownerId()),
            'utmPresets' => $branding->utmPresets($this->ownerId()),
            'pixels' => $branding->pixels($this->ownerId()),
            'brandVisual' => $branding->visualSettings($this->ownerId()),
            'previewShortUrl' => $this->previewShortUrl($branding),
            'previewQrSvg' => $this->previewQrSvg($branding),
            'qrRenderer' => app(ShortLinkQrRenderer::class),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('URL Shortener')]);
    }

    protected function previewShortUrl(ShortLinkBranding $branding): string
    {
        $code = trim((string) ($this->form['custom_code'] ?? ''));

        if ($code === '') {
            $seed = trim((string) (($this->form['name'] ?? '') ?: ($this->form['destination_url'] ?? '')));
            $code = $seed !== '' ? substr(md5($seed), 0, 6) : 'a8K3xQ';
        }

        $domainId = (int) ($this->form['custom_domain_id'] ?? 0);
        $domain = $branding->domains($this->ownerId())->firstWhere('id', $domainId);
        $fallback = route('short-links.public.redirect', ['code' => $code]);

        if ($domain) {
            return 'https://'.trim((string) $domain->domain, '/').'/s/'.$code;
        }

        return $fallback;
    }

    protected function suggestShortCode(string $name = '', string $url = '', ?int $ignoreId = null): string
    {
        $source = trim($name);

        if ($source === '') {
            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
            $source = $path !== '' ? basename($path) : (string) parse_url($url, PHP_URL_HOST);
        }

        $base = str($source)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->limit(32, '')
            ->value();

        if ($base === '' || strlen($base) < 3) {
            $base = AppShortLink::uniqueShortCode($source ?: $url);
        }

        $base = substr($base, 0, 40);
        $candidate = $base;
        $suffix = 2;

        while (AppShortLink::query()
            ->where('short_code', $candidate)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $tail = '-'.$suffix++;
            $candidate = substr($base, 0, 48 - strlen($tail)).$tail;
        }

        return $candidate;
    }

    protected function previewQrSvg(ShortLinkBranding $branding): ?string
    {
        if (trim((string) ($this->form['destination_url'] ?? '')) === '') {
            return null;
        }

        return app(GradientRoundedQrRenderer::class)->render(
            $this->previewShortUrl($branding),
            $branding->qrOptions($this->ownerId())
        );
    }

    protected function ownedLink(int $linkId): AppShortLink
    {
        return AppShortLink::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->findOrFail($linkId);
    }

    protected function filteredLinksQuery(): Builder
    {
        $query = AppShortLink::query()->where('owner_user_id', $this->ownerId());

        if (trim($this->search) !== '') {
            $search = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)).'%';
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', $search)
                    ->orWhere('short_code', 'like', $search)
                    ->orWhere('destination_url', 'like', $search)
                    ->orWhere('campaign', 'like', $search)
                    ->orWhere('folder', 'like', $search);
            });
        }

        if ($this->statusFilter !== 'all') {
            if ($this->statusFilter === 'blocked') {
                $query->where('moderation_status', 'blocked');
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        match ($this->sort) {
            'clicks' => $query->orderByDesc('clicks_count')->latest(),
            'oldest' => $query->oldest(),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return $query;
    }

    protected function currentPageLinkIds(): array
    {
        return $this->filteredLinksQuery()
            ->paginate($this->perPage, ['id'], 'page', $this->getPage())
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    protected function normalizedSelectedLinkIds(): array
    {
        return collect($this->selectedLinkIds)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function ownerId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function cleanIds(array $ids): array
    {
        return collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
    }

    protected function fillMetadata(string $formKey): void
    {
        $url = trim((string) data_get($this->{$formKey}, 'destination_url'));

        if ($url === '') {
            return;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->dispatch('app-toast', type: 'warning', message: __('Enter a valid destination URL first.'));

            return;
        }

        $metadata = $this->fetchMetadata($url);

        if ($metadata === []) {
            $this->dispatch('app-toast', type: 'warning', message: __('No metadata found for this URL.'));

            return;
        }

        $form = $this->{$formKey};

        foreach (['og_title' => 'title', 'og_description' => 'description', 'og_image' => 'image'] as $field => $key) {
            if (filled($metadata[$key] ?? null) && blank(data_get($form, $field))) {
                data_set($form, $field, $metadata[$key]);
            }
        }

        if (blank(data_get($form, 'name')) && filled($metadata['title'] ?? null)) {
            data_set($form, 'name', $metadata['title']);
        }

        $this->{$formKey} = $form;

        $this->dispatch('app-toast', type: 'success', message: __('Link preview metadata fetched.'));
    }

    protected function fetchMetadata(string $url): array
    {
        try {
            $response = Http::timeout(8)
                ->connectTimeout(4)
                ->withHeaders(['User-Agent' => 'LinkQRPro metadata crawler'])
                ->get($url);
        } catch (\Throwable) {
            return [];
        }

        if (! $response->ok()) {
            return [];
        }

        $html = $this->normalizeFetchedHtml((string) $response->body(), (string) $response->header('Content-Type'));

        if ($html === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($dom);

        $meta = function (string $selector) use ($xpath): string {
            $node = $xpath->query($selector)->item(0);

            return trim((string) $node?->attributes?->getNamedItem('content')?->nodeValue);
        };

        $titleNode = $xpath->query('//title')->item(0);
        $title = $meta('//meta[@property="og:title"]')
            ?: $meta('//meta[@name="twitter:title"]')
            ?: trim((string) $titleNode?->textContent);
        $description = $meta('//meta[@property="og:description"]')
            ?: $meta('//meta[@name="description"]')
            ?: $meta('//meta[@name="twitter:description"]');
        $image = $meta('//meta[@property="og:image"]')
            ?: $meta('//meta[@name="twitter:image"]');

        if ($image && ! str_starts_with($image, 'http')) {
            if (str_starts_with($image, '//')) {
                $image = (string) parse_url($url, PHP_URL_SCHEME).':'.$image;
            } else {
                $image = rtrim((string) parse_url($url, PHP_URL_SCHEME), ':').'://'.parse_url($url, PHP_URL_HOST).'/'.ltrim($image, '/');
            }
        }

        return array_filter([
            'title' => str($title)->limit(160, '')->value(),
            'description' => str($description)->limit(500, '')->value(),
            'image' => filter_var($image, FILTER_VALIDATE_URL) ? $image : null,
        ]);
    }

    protected function normalizeFetchedHtml(string $html, string $contentType = ''): string
    {
        if ($html === '') {
            return '';
        }

        $encoding = null;

        if (preg_match('/charset\s*=\s*["\']?([a-zA-Z0-9._-]+)/i', $contentType, $match)) {
            $encoding = strtoupper($match[1]);
        }

        if (! $encoding && preg_match('/<meta[^>]+charset\s*=\s*["\']?\s*([a-zA-Z0-9._-]+)/i', $html, $match)) {
            $encoding = strtoupper($match[1]);
        }

        if (! $encoding && preg_match('/<meta[^>]+content\s*=\s*["\'][^"\']*charset\s*=\s*([a-zA-Z0-9._-]+)/i', $html, $match)) {
            $encoding = strtoupper($match[1]);
        }

        $aliases = [
            'GB2312' => 'GB18030',
            'GBK' => 'GB18030',
            'CP936' => 'GB18030',
            'BIG5' => 'BIG5',
            'BIG-5' => 'BIG5',
            'CP950' => 'BIG5',
        ];
        $encoding = $aliases[$encoding] ?? $encoding;

        if (! $encoding) {
            $encoding = mb_detect_encoding($html, ['UTF-8', 'GB18030', 'BIG5', 'CP950', 'Shift_JIS', 'EUC-JP', 'ISO-8859-1'], true) ?: null;
        }

        if ($encoding && strtoupper($encoding) !== 'UTF-8') {
            $converted = @mb_convert_encoding($html, 'UTF-8', $encoding);

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        if (! mb_check_encoding($html, 'UTF-8')) {
            $converted = @mb_convert_encoding($html, 'UTF-8', 'GB18030');

            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return $html;
    }

    public function resetCreateForm(): void
    {
        $branding = app(ShortLinkBranding::class);
        $defaultDomainId = $branding->domains($this->ownerId())->firstWhere('is_default', true)?->id;
        $defaultUtmPresetId = $branding->defaultUtmPreset($this->ownerId())?->id;
        $defaultPixelIds = $branding->defaultPixelIds($this->ownerId());

        $this->form = [
            'name' => '',
            'folder' => '',
            'campaign' => '',
            'tags' => '',
            'destination_url' => '',
            'custom_code' => '',
            'custom_domain_id' => $defaultDomainId ? (string) $defaultDomainId : '',
            'utm_preset_id' => $defaultUtmPresetId ? (string) $defaultUtmPresetId : '',
            'tracking_pixel_ids' => $defaultPixelIds,
            'expires_at' => '',
            'click_limit' => '',
            'password' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'redirect_rules' => '',
        ];
    }

    protected function parseTags(string $tags): array
    {
        return str($tags)->explode(',')
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
