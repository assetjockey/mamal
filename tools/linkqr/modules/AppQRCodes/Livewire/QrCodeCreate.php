<?php

namespace Modules\AppQRCodes\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Create QR Code')]
class QrCodeCreate extends Component
{
    public string $selectedType = 'dynamic_url';

    public string $search = '';

    public string $category = 'all';

    public string $bioPageSearch = '';

    public ?int $selectedBioPageId = null;

    public function selectType(string $type): void
    {
        if (collect(AppQrCode::typeCatalog())->contains('key', $type)) {
            $this->selectedType = $type;

            if ($type !== 'bio_links') {
                $this->selectedBioPageId = null;
            }
        }
    }

    public function selectCategory(string $category): void
    {
        if (array_key_exists($category, $this->categories())) {
            $this->category = $category;
        }
    }

    public function selectBioPage(int $pageId): void
    {
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $page = LinkBioPage::query()
            ->ownedBy($ownerUserId)
            ->find($pageId);

        if (! $page) {
            $this->selectedBioPageId = null;

            return;
        }

        $this->selectedBioPageId = (int) $page->id;
        $this->bioPageSearch = (string) $page->title;
    }

    public function createSelectedType(): void
    {
        abort_unless($this->canUseQrCodes(), 403);

        if ($this->hasReachedQrCodeLimit()) {
            $this->dispatch('app-toast', type: 'warning', message: __('QR campaign limit reached. Upgrade your plan to create more QR codes.'));

            return;
        }

        $type = collect(AppQrCode::typeCatalog())->firstWhere('key', $this->selectedType)
            ?: collect(AppQrCode::typeCatalog())->first();
        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $bioPage = $this->selectedType === 'bio_links'
            ? $this->selectedBioPage($ownerUserId)
            : null;

        if ($this->selectedType === 'bio_links' && ! $bioPage) {
            $this->dispatch('app-toast', type: 'error', message: __('Choose a Link Bio page first.'));

            return;
        }

        $bioUrl = $bioPage ? route('link-bio.public.show', ['slug' => $bioPage->slug]) : url('/');
        $brandOps = app(BrandOperations::class);
        $defaultUtmPresetId = $brandOps->utmPreset(null, $ownerUserId)?->id;
        $defaultPixelIds = $brandOps->defaultPixelIds($ownerUserId);
        $name = $bioPage
            ? __('Bio QR - :title', ['title' => $bioPage->title])
            : (string) $type['label'].' #'.now()->format('His');

        $qrCode = AppQrCode::query()->create([
            'owner_user_id' => $ownerUserId,
            'team_id' => $team?->id,
            'type' => (string) $type['key'],
            'name' => $name,
            'status' => $type['kind'] === __('dynamic') ? 'active' : 'draft',
            'destination_url' => $this->selectedType === 'bio_links' ? $bioUrl : url('/'),
            'short_code' => $type['kind'] === __('dynamic') ? AppQrCode::uniqueShortCode($name) : null,
            'foreground_color' => '#0f172a',
            'background_color' => '#ffffff',
            'pattern' => 'square',
            'settings' => [
                'type_label' => (string) $type['label'],
                'kind' => (string) $type['kind'],
                'utm_preset_id' => $defaultUtmPresetId ? (int) $defaultUtmPresetId : null,
                'tracking_pixel_ids' => $defaultPixelIds,
                'type_content' => $this->selectedType === 'bio_links' ? [
                    'title' => $bioPage?->title ?: __('Bio Links'),
                    'url' => $bioUrl,
                    'link_bio_page_id' => $bioPage?->id,
                ] : [],
            ],
        ]);

        if ($this->selectedType !== 'bio_links' && $this->canUseQrTypeBuilders() && Route::has('portal.qr-codes.type-content')) {
            $this->redirectRoute('portal.qr-codes.type-content', ['qrCode' => $qrCode->id], navigate: true);

            return;
        }

        $this->redirectRoute('portal.qr-codes.edit', ['qrCode' => $qrCode->id], navigate: true);
    }

    public function render(): View
    {
        abort_unless($this->canUseQrCodes(), 403);

        $types = collect(AppQrCode::typeCatalog());
        $filteredTypes = $types
            ->filter(fn (array $type): bool => $this->search === ''
                || str_contains(strtolower((string) $type['label']), strtolower($this->search))
                || str_contains(strtolower((string) $type['kind']), strtolower($this->search)))
            ->filter(fn (array $type): bool => $this->category === 'all' || $this->typeCategory((string) $type['key']) === $this->category)
            ->values()
            ->all();

        $bioPagesQuery = LinkBioPage::query()
            ->ownedBy(TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->orderBy('title');

        $filteredBioPages = (clone $bioPagesQuery)
            ->when($this->bioPageSearch !== '', function ($query): void {
                $term = '%'.$this->bioPageSearch.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('title', 'like', $term)
                        ->orWhere('slug', 'like', $term);
                });
            })
            ->limit(8)
            ->get(['id', 'title', 'slug', 'is_published']);

        return view('appqrcodes::create', [
            'types' => $filteredTypes,
            'selectedTypeMeta' => $types->firstWhere('key', $this->selectedType) ?: $types->first(),
            'bioPages' => $filteredBioPages,
            'hasBioPages' => (clone $bioPagesQuery)->exists(),
            'categories' => $this->categories(),
            'categoryCounts' => $this->categoryCounts($types),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Create QR Code'),
        ]);
    }

    protected function categories(): array
    {
        return [
            'all' => ['label' => __('All types'), 'icon' => 'fa-grid-2'],
            'campaign' => ['label' => __('Campaigns'), 'icon' => 'fa-bullhorn'],
            'business' => ['label' => __('Business'), 'icon' => 'fa-briefcase'],
            'commerce' => ['label' => __('Commerce'), 'icon' => 'fa-bag-shopping'],
            'communication' => ['label' => __('Communication'), 'icon' => 'fa-comments'],
            'payments' => ['label' => __('Payments'), 'icon' => 'fa-credit-card'],
            'static' => ['label' => __('Static data'), 'icon' => 'fa-database'],
        ];
    }

    protected function typeCategory(string $type): string
    {
        return match ($type) {
            'dynamic_url', 'website_builder', 'event', 'booking', 'app_download', 'resume_qr_code', 'file_upload' => 'campaign',
            'bio_links', 'business_profile', 'business_review', 'google_review', 'vcard_plus', 'vcard', 'lead_form' => 'business',
            'restaurant_menu', 'product_catalogue' => 'commerce',
            'email', 'email_dynamic', 'sms', 'sms_dynamic', 'call', 'whatsapp', 'facetime', 'telegram', 'messenger', 'viber', 'zoom' => 'communication',
            'donation', 'paypal', 'upi_static', 'upi_dynamic', 'crypto', 'brazilian_pix' => 'payments',
            default => 'static',
        };
    }

    protected function categoryCounts($types): array
    {
        $counts = ['all' => $types->count()];

        foreach ($types as $type) {
            $category = $this->typeCategory((string) $type['key']);
            $counts[$category] = ($counts[$category] ?? 0) + 1;
        }

        return $counts;
    }

    protected function canUseQrCodes(): bool
    {
        $user = auth()->user();

        return ! $user?->plan
            || ($user?->canUsePlanFeature('qr_codes') ?? false)
            || ($user?->canUsePlanFeature('link_bio_qr_codes') ?? false);
    }

    protected function canUseQrTypeBuilders(): bool
    {
        $user = auth()->user();

        return ! $user?->plan || ($user?->canUsePlanFeature('qr_type_builders') ?? false);
    }

    protected function hasReachedQrCodeLimit(): bool
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
        $limit = $planOwner?->planLimit('max_qr_codes', -1);

        if (! $planOwner?->plan || ! is_numeric($limit) || (int) $limit < 0) {
            return false;
        }

        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId($user))
            ->count() >= (int) $limit;
    }

    protected function selectedBioPage(int $ownerUserId): ?LinkBioPage
    {
        if (! $this->selectedBioPageId) {
            return null;
        }

        return LinkBioPage::query()
            ->ownedBy($ownerUserId)
            ->find($this->selectedBioPageId);
    }
}
