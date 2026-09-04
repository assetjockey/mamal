<?php

namespace Modules\AppQRPrePrinted\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Pre-Printed QR')]
class PrePrintedIndex extends Component
{
    public ?int $qrCodeId = null;

    public ?int $templateQrCodeId = null;

    public string $selectedType = 'dynamic_url';

    public int $count = 12;

    public string $batchName = 'Pre-Printed QR Batch';

    public string $sticker = 'classic_scan';

    public string $qrStyleTemplate = 'clean_card';

    public string $publicTemplate = 'split_showcase';

    public string $format = 'sticker_square';

    public string $material = 'matte_sticker';

    public int $quantity = 24;

    public bool $includeLogo = true;

    public bool $includeShortUrl = true;

    public int $generatedCount = 0;

    public ?string $lastBatchId = null;

    public ?string $lastBatchName = null;

    public function mount(): void
    {
        abort_unless($this->canUsePrePrinted(), 403);

        $this->qrCodeId = $this->qrCodes()->first()?->id;
        $this->templateQrCodeId = $this->qrCodeId;
    }

    public function generate(): void
    {
        abort_unless($this->canUsePrePrinted(), 403);

        $validated = validator([
            'selectedType' => $this->selectedType,
            'count' => $this->count,
            'batchName' => $this->batchName,
            'qrStyleTemplate' => $this->qrStyleTemplate,
            'publicTemplate' => $this->publicTemplate,
        ], [
            'selectedType' => ['required', 'string'],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'batchName' => ['required', 'string', 'max:120'],
            'qrStyleTemplate' => ['required', 'string'],
            'publicTemplate' => ['required', 'string'],
        ])->validate();

        $types = $this->dynamicTypes()->keyBy('key');
        $qrStyleTemplates = $this->qrStyleTemplates();
        $publicTemplates = $this->publicTemplates();

        if (! $types->has($validated['selectedType'])) {
            $this->dispatch('app-toast', type: 'warning', message: __('Choose a dynamic QR type before generating.'));

            return;
        }

        if (! $qrStyleTemplates->has($validated['qrStyleTemplate']) || ! $publicTemplates->has($validated['publicTemplate'])) {
            $this->dispatch('app-toast', type: 'warning', message: __('Choose a valid QR style and page template before generating.'));

            return;
        }

        $qrStyle = $qrStyleTemplates->get($validated['qrStyleTemplate']);
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $batchId = 'pp-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(4));
        $template = $this->templateQrCodeId
            ? AppQrCode::query()->where('owner_user_id', $ownerId)->whereKey($this->templateQrCodeId)->first()
            : null;

        $created = 0;

        for ($index = 1; $index <= (int) $validated['count']; $index++) {
            $name = trim((string) $validated['batchName']).' #'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $type = $template?->type ?: (string) $validated['selectedType'];
            $settings = array_merge((array) ($template?->settings ?? []), [
                'source' => 'pre_printed',
                'pre_printed' => [
                    'batch_id' => $batchId,
                    'batch_name' => trim((string) $validated['batchName']),
                    'qr_style_template' => $validated['qrStyleTemplate'],
                    'public_template' => $validated['publicTemplate'],
                    'format' => $this->format,
                    'material' => $this->material,
                    'include_logo' => $this->includeLogo,
                    'include_short_url' => $this->includeShortUrl,
                ],
                'style_template' => $validated['qrStyleTemplate'],
                'public_template' => $validated['publicTemplate'],
                'design_mode' => match ((string) ($qrStyle['pattern'] ?? 'square')) {
                    'mosaic' => 'mosaic',
                    'gradient_rounded' => 'roundness',
                    default => 'basic',
                },
                'qr_shape' => (string) ($qrStyle['shape'] ?? 'square'),
                'fallback_dark' => (string) ($qrStyle['fallback'] ?? '#0f172a'),
            ]);

            AppQrCode::query()->create([
                'owner_user_id' => $ownerId,
                'team_id' => $team?->id,
                'type' => $type,
                'name' => $name,
                'status' => filled($template?->destination_url) ? 'active' : 'draft',
                'destination_url' => $template?->destination_url ?: null,
                'short_code' => AppQrCode::uniqueShortCode($name),
                'short_domain' => $template?->short_domain,
                'foreground_color' => (string) ($qrStyle['foreground'] ?? $template?->foreground_color ?: '#0f172a'),
                'background_color' => (string) ($qrStyle['background'] ?? $template?->background_color ?: '#ffffff'),
                'pattern' => (string) ($qrStyle['pattern'] ?? $template?->pattern ?: 'square'),
                'logo_url' => $this->includeLogo ? $template?->logo_url : null,
                'settings' => $settings,
            ]);

            $created++;
        }

        $this->generatedCount = $created;
        $this->lastBatchId = $batchId;
        $this->lastBatchName = trim((string) $validated['batchName']);
        $this->dispatch('app-toast', type: 'success', message: __('Pre-printed QR batch generated.'));
    }

    public function render(): View
    {
        abort_unless($this->canUsePrePrinted(), 403);

        $qrCodes = $this->qrCodes();
        $selectedQr = $qrCodes->firstWhere('id', $this->qrCodeId);

        return view('appqrpreprinted::index', [
            'qrCodes' => $qrCodes,
            'selectedQr' => $selectedQr,
            'formats' => $this->formats(),
            'materials' => $this->materials(),
            'dynamicTypes' => $this->dynamicTypes(),
            'qrStyleTemplates' => $this->qrStyleTemplates(),
            'publicTemplates' => $this->publicTemplates(),
            'selectedFormat' => $this->formats()->firstWhere('key', $this->format) ?? $this->formats()->first(),
            'selectedMaterial' => $this->materials()->firstWhere('key', $this->material) ?? $this->materials()->first(),
            'checklist' => $this->checklist($selectedQr),
            'useCases' => $this->useCases(),
            'recentBatches' => $this->recentBatches(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Pre-Printed QR')]);
    }

    protected function qrCodes(): Collection
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->latest()
            ->limit(80)
            ->get();
    }

    protected function dynamicTypes(): Collection
    {
        return collect(AppQrCode::typeCatalog())
            ->filter(fn (array $type): bool => (string) ($type['kind'] ?? '') === __('dynamic'))
            ->values();
    }

    protected function formats(): Collection
    {
        return collect([
            ['key' => 'sticker_square', 'label' => __('Square sticker'), 'size' => '50 x 50 mm', 'bleed' => '2 mm', 'safe' => '4 mm', 'grid' => '4 x 6'],
            ['key' => 'table_tent', 'label' => __('Table tent'), 'size' => '100 x 150 mm', 'bleed' => '3 mm', 'safe' => '6 mm', 'grid' => '1 x 2'],
            ['key' => 'business_card', 'label' => __('Business card'), 'size' => '90 x 54 mm', 'bleed' => '3 mm', 'safe' => '5 mm', 'grid' => '2 x 5'],
            ['key' => 'poster_a4', 'label' => __('A4 poster'), 'size' => '210 x 297 mm', 'bleed' => '3 mm', 'safe' => '8 mm', 'grid' => '1 x 1'],
            ['key' => 'product_label', 'label' => __('Product label'), 'size' => '70 x 35 mm', 'bleed' => '2 mm', 'safe' => '4 mm', 'grid' => '3 x 8'],
        ]);
    }

    protected function materials(): Collection
    {
        return collect([
            ['key' => 'matte_sticker', 'label' => __('Matte sticker'), 'finish' => __('Indoor labels and packaging')],
            ['key' => 'vinyl_sticker', 'label' => __('Vinyl sticker'), 'finish' => __('Water-resistant product and window labels')],
            ['key' => 'card_stock', 'label' => __('Card stock'), 'finish' => __('Business cards, inserts, and handouts')],
            ['key' => 'poster_paper', 'label' => __('Poster paper'), 'finish' => __('Wall posters, signage, and event displays')],
        ]);
    }

    protected function qrStyleTemplates(): Collection
    {
        return collect([
            'clean_card' => ['label' => __('Default QR'), 'description' => __('Plain scan-safe QR.'), 'pattern' => 'square', 'foreground' => '#0f172a', 'background' => '#ffffff', 'fallback' => '#0f172a', 'shape' => 'square', 'accent' => '#2563eb', 'surface' => '#ffffff'],
            'rounded_gradient' => ['label' => __('Rounded gradient'), 'description' => __('Rounded modules with gradient color.'), 'pattern' => 'gradient_rounded', 'foreground' => '#b02a2a', 'background' => '#ffffff', 'fallback' => '#264653', 'shape' => 'rounded', 'accent' => '#2a9d8f', 'surface' => '#ffffff'],
            'navy_label' => ['label' => __('Navy square'), 'description' => __('Navy framed square for print.'), 'pattern' => 'square', 'foreground' => '#0f4f87', 'background' => '#ffffff', 'fallback' => '#0b2447', 'shape' => 'square', 'accent' => '#f97316', 'surface' => '#0b2447'],
            'emerald_ring' => ['label' => __('Emerald square'), 'description' => __('Green storefront frame.'), 'pattern' => 'square', 'foreground' => '#0f7a32', 'background' => '#ffffff', 'fallback' => '#064e3b', 'shape' => 'square', 'accent' => '#16a34a', 'surface' => '#ecfdf5'],
            'gold_orbit' => ['label' => __('Gold square'), 'description' => __('Premium gold frame.'), 'pattern' => 'square', 'foreground' => '#171717', 'background' => '#fffaf0', 'fallback' => '#92400e', 'shape' => 'square', 'accent' => '#f59e0b', 'surface' => '#fffbeb'],
            'roundness_ocean' => ['label' => __('Ocean round'), 'description' => __('Blue to teal rounded QR.'), 'pattern' => 'gradient_rounded', 'foreground' => '#1d4ed8', 'background' => '#eff6ff', 'fallback' => '#06b6d4', 'shape' => 'rounded', 'accent' => '#0e7490', 'surface' => '#ecfeff'],
            'roundness_carbon' => ['label' => __('Carbon round'), 'description' => __('Dark graphite rounded QR.'), 'pattern' => 'gradient_rounded', 'foreground' => '#111827', 'background' => '#f8fafc', 'fallback' => '#64748b', 'shape' => 'rounded', 'accent' => '#334155', 'surface' => '#e2e8f0'],
        ])->map(fn (array $template, string $key): array => ['key' => $key, ...$template]);
    }

    protected function publicTemplates(): Collection
    {
        return collect([
            'split_showcase' => ['label' => __('Split showcase'), 'description' => __('Large copy on the left with details and media on the right.'), 'icon' => 'fa-columns-3'],
            'mobile_stack' => ['label' => __('Mobile stack'), 'description' => __('Compact app-like page for menus, profiles, and quick actions.'), 'icon' => 'fa-mobile-screen'],
            'catalog_grid' => ['label' => __('Catalog grid'), 'description' => __('Product-first storefront with collection cards and clear CTAs.'), 'icon' => 'fa-grid-2'],
            'editorial' => ['label' => __('Editorial'), 'description' => __('Clean article-style page for events, resumes, files, and reviews.'), 'icon' => 'fa-newspaper'],
            'compact_banner' => ['label' => __('Compact banner'), 'description' => __('Short branded hero with action buttons above the fold.'), 'icon' => 'fa-rectangle-wide'],
            'sidebar_profile' => ['label' => __('Sidebar profile'), 'description' => __('Profile-first layout with contact facts beside the content.'), 'icon' => 'fa-sidebar'],
            'menu_board' => ['label' => __('Menu board'), 'description' => __('Restaurant-style board with item groups and strong prices.'), 'icon' => 'fa-utensils'],
            'lead_capture' => ['label' => __('Lead capture'), 'description' => __('Conversion-focused page for forms, quotes, and bookings.'), 'icon' => 'fa-inbox-in'],
            'event_pass' => ['label' => __('Event pass'), 'description' => __('Ticket-like design for events, RSVPs, and appointments.'), 'icon' => 'fa-ticket'],
            'file_card' => ['label' => __('File card'), 'description' => __('Document-style view for files, resumes, and downloads.'), 'icon' => 'fa-file-lines'],
            'review_spotlight' => ['label' => __('Review spotlight'), 'description' => __('Trust-first layout for reviews and social proof.'), 'icon' => 'fa-star'],
            'resume_sheet' => ['label' => __('Resume sheet'), 'description' => __('Professional profile sheet with clear contact options.'), 'icon' => 'fa-id-card'],
            'booking_card' => ['label' => __('Booking card'), 'description' => __('Appointment card with date, location, and booking CTA.'), 'icon' => 'fa-calendar-check'],
            'app_download' => ['label' => __('App download'), 'description' => __('App-store style page for downloads and product launches.'), 'icon' => 'fa-mobile-button'],
            'payment_card' => ['label' => __('Payment card'), 'description' => __('Payment-forward layout for PayPal, UPI, PIX, and crypto.'), 'icon' => 'fa-credit-card'],
            'link_hub' => ['label' => __('Link hub'), 'description' => __('Simple hub for multiple links and campaign destinations.'), 'icon' => 'fa-link'],
        ])->map(fn (array $template, string $key): array => ['key' => $key, ...$template]);
    }

    protected function recentBatches(): Collection
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('settings->source', 'pre_printed')
            ->latest()
            ->limit(300)
            ->get()
            ->groupBy(fn (AppQrCode $qrCode): string => (string) (data_get($qrCode->settings, 'pre_printed.batch_id') ?: data_get($qrCode->settings, 'pre_printed.batch_name') ?: 'unknown'))
            ->map(function (Collection $items, string $batchId): array {
                $first = $items->first();

                return [
                    'id' => $batchId,
                    'name' => (string) data_get($first?->settings, 'pre_printed.batch_name', __('Untitled batch')),
                    'count' => $items->count(),
                    'created_at' => $items->max('created_at'),
                    'style' => (string) data_get($first?->settings, 'pre_printed.qr_style_template', data_get($first?->settings, 'style_template', 'clean_card')),
                    'view' => (string) data_get($first?->settings, 'pre_printed.public_template', data_get($first?->settings, 'public_template', 'split_showcase')),
                ];
            })
            ->sortByDesc('created_at')
            ->take(6)
            ->values();
    }

    protected function useCases(): array
    {
        return [
            ['label' => __('Restaurant menus'), 'icon' => 'fa-utensils'],
            ['label' => __('Product packaging'), 'icon' => 'fa-box-open'],
            ['label' => __('Event badges'), 'icon' => 'fa-id-badge'],
            ['label' => __('Retail posters'), 'icon' => 'fa-sign-hanging'],
            ['label' => __('Business cards'), 'icon' => 'fa-address-card'],
            ['label' => __('Review cards'), 'icon' => 'fa-star'],
        ];
    }

    protected function checklist(?AppQrCode $qrCode): array
    {
        return [
            ['label' => __('Dynamic short link'), 'done' => filled($qrCode?->short_code), 'hint' => __('Required if destination may change after printing.')],
            ['label' => __('Active campaign'), 'done' => (string) ($qrCode?->status ?? '') === 'active', 'hint' => __('Scans should resolve before artwork is sent to print.')],
            ['label' => __('Destination URL'), 'done' => filled($qrCode?->destination_url), 'hint' => __('Confirm the final landing URL or dynamic redirect target.')],
            ['label' => __('Scan-safe contrast'), 'done' => true, 'hint' => __('Keep dark QR foreground on a light quiet zone.')],
        ];
    }

    protected function canUsePrePrinted(): bool
    {
        $user = auth()->user();
        $planOwner = TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;

        return ! $planOwner?->plan
            || ($planOwner?->canUsePlanFeature('qr_pre_printed') ?? false)
            || ($planOwner?->canUsePlanFeature('qr_codes') ?? false)
            || ($planOwner?->canUsePlanFeature('link_bio_qr_codes') ?? false);
    }
}
