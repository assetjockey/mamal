<?php

namespace Modules\AppQRCodes\Livewire;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBrandKit\Support\BrandOperations;
use Modules\AppFiles\Models\AppFile;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Models\AppQrScanEvent;
use Modules\AppQRCodes\Support\BrandMosaicQrRenderer;
use Modules\AppQRCodes\Support\GradientRoundedQrRenderer;
use Modules\AppQRCodes\Support\QrPlanLimits;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('QR Codes')]
class QrCodeWorkspace extends Component
{
    use WithPagination;

    public string $selectedType = 'dynamic_url';

    public string $search = '';

    public string $statusFilter = 'all';

    public string $typeFilter = 'all';

    public ?int $editingQrId = null;

    public ?int $selectedBioPageId = null;

    public array $selectedQrIds = [];

    public array $form = [
        'name' => '',
        'destination_url' => '',
        'status' => 'active',
        'foreground_color' => '#0f172a',
        'background_color' => '#ffffff',
        'pattern' => 'square',
        'logo_url' => '',
    ];

    public function selectType(string $type): void
    {
        if (! collect(AppQrCode::typeCatalog())->contains('key', $type)) {
            return;
        }

        $this->selectedType = $type;

        if ($type !== 'bio_links') {
            $this->selectedBioPageId = null;
        }
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

        if ($this->selectedType === 'bio_links') {
            $this->redirectRoute('portal.qr-codes.edit', ['qrCode' => $qrCode->id], navigate: true);

            return;
        }

        $this->editQrCode((int) $qrCode->id);
        $this->dispatch('app-toast', type: 'success', message: __('QR code created.'));
    }

    public function editQrCode(int $qrCodeId): void
    {
        $qrCode = $this->ownedQrCode($qrCodeId);

        $this->editingQrId = (int) $qrCode->id;
        $this->form = [
            'name' => (string) $qrCode->name,
            'destination_url' => (string) ($qrCode->destination_url ?: ''),
            'status' => (string) $qrCode->status,
            'foreground_color' => (string) $qrCode->foreground_color,
            'background_color' => (string) $qrCode->background_color,
            'pattern' => (string) $qrCode->pattern,
            'logo_url' => (string) ($qrCode->logo_url ?: ''),
        ];
    }

    public function saveQrCode(): void
    {
        abort_unless($this->canUseQrCodes(), 403);
        abort_unless($this->editingQrId, 404);

        $qrCode = $this->ownedQrCode((int) $this->editingQrId);
        $validator = validator($this->form, [
            'name' => ['required', 'string', 'max:160'],
            'destination_url' => [
                'nullable',
                'string',
                'max:2000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->isAllowedQrDestination((string) $value)) {
                        $fail(__('Enter a valid URL or QR payload such as sms:, mailto:, tel:, geo:, or WIFI:.'));
                    }
                },
            ],
            'status' => ['required', 'in:active,draft'],
            'foreground_color' => ['nullable', 'string', 'max:24'],
            'background_color' => ['nullable', 'string', 'max:24'],
            'pattern' => ['required', 'in:square,rounded,dots,mosaic,sticker,outlined,gradient_rounded'],
            'logo_url' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
            $this->dispatch('app-toast', type: 'error', message: $validator->errors()->first());

            return;
        }

        $validated = $validator->validated();

        try {
            $qrCode->update([
                'name' => $validated['name'],
                'destination_url' => $validated['destination_url'] ?: url('/'),
                'status' => $validated['status'],
                'foreground_color' => $validated['foreground_color'] ?: '#0f172a',
                'background_color' => $validated['background_color'] ?: '#ffffff',
                'pattern' => $validated['pattern'],
                'logo_url' => trim((string) ($validated['logo_url'] ?? '')) ?: null,
            ]);

            $this->editQrCode((int) $qrCode->id);
            $this->dispatch('app-toast', type: 'success', message: __('QR code saved.'));
        } catch (\Throwable $exception) {
            report($exception);

            $message = __('Could not save this QR code. Please check the data and try again.');
            $this->setErrorBag(['save' => $message]);
            $this->dispatch('app-toast', type: 'error', message: $message);
        }
    }

    public function deleteQrCode(int $qrCodeId): void
    {
        abort_unless($this->canUseQrCodes(), 403);

        $qrCode = $this->ownedQrCode($qrCodeId);
        $qrCode->delete();

        if ($this->editingQrId === $qrCodeId) {
            $this->editingQrId = null;
        }

        $this->dispatch('app-toast', type: 'success', message: __('QR campaign deleted.'));
    }

    public function deleteSelectedQrCodes(array $ids = []): void
    {
        abort_unless($this->canUseQrCodes(), 403);

        $ids = collect($ids !== [] ? $ids : $this->selectedQrIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $deleted = AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereIn('id', $ids)
            ->delete();

        if ($this->editingQrId && $ids->contains((int) $this->editingQrId)) {
            $this->editingQrId = null;
        }

        $this->selectedQrIds = [];
        $this->dispatch('app-toast', type: 'success', message: __('Deleted :count QR campaigns.', ['count' => $deleted]));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedQrIds = [];
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->selectedQrIds = [];
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
        $this->selectedQrIds = [];
    }

    public function render(): View
    {
        abort_unless($this->canUseQrCodes(), 403);

        $ownerUserId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $types = collect(AppQrCode::typeCatalog());
        $typeOptions = $types->pluck('label', 'key')->all();

        $query = $this->filteredQrCodeQuery($ownerUserId);

        $qrCodes = (clone $query)
            ->latest()
            ->paginate(12);
        $editingQrCode = $this->editingQrId
            ? AppQrCode::query()->where('owner_user_id', $ownerUserId)->find($this->editingQrId)
            : $qrCodes->getCollection()->first();

        return view('appqrcodes::workspace', [
            'types' => $typeOptions,
            'selectedTypeMeta' => $types->firstWhere('key', $this->selectedType) ?: $types->first(),
            'qrCodes' => $qrCodes,
            'selectedCount' => count($this->selectedQrIds),
            'visibleQrCodeIds' => $qrCodes->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'editingQrCode' => $editingQrCode,
            'editingQrSvg' => $editingQrCode ? $this->makeQrCodeSvg(
                $editingQrCode->shortUrl() ?: ($editingQrCode->destination_url ?: url('/')),
                (string) data_get($this->form, 'foreground_color', $editingQrCode->foreground_color),
                (string) data_get($this->form, 'background_color', $editingQrCode->background_color),
            ) : null,
            'stats' => [
                'total' => AppQrCode::query()->where('owner_user_id', $ownerUserId)->count(),
                'dynamic' => AppQrCode::query()->where('owner_user_id', $ownerUserId)->whereNotNull('short_code')->count(),
                'scans' => AppQrScanEvent::query()->where('owner_user_id', $ownerUserId)->count(),
                'active' => AppQrCode::query()->where('owner_user_id', $ownerUserId)->where('status', 'active')->count(),
            ],
            'qrLimits' => app(QrPlanLimits::class)->summary(auth()->user(), $ownerUserId),
            'canUseQrTypeBuilders' => $this->canUseQrTypeBuilders(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('QR Codes'),
        ]);
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

    protected function isAllowedQrDestination(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }

        if (! preg_match('/^([a-z][a-z0-9+.-]*):/i', $value, $matches)) {
            return false;
        }

        $scheme = strtolower($matches[1]);

        return ! in_array($scheme, ['javascript', 'data', 'vbscript'], true);
    }

    protected function ownedQrCode(int $qrCodeId): AppQrCode
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->findOrFail($qrCodeId);
    }

    protected function filteredQrCodeQuery(int $ownerUserId)
    {
        return AppQrCode::query()
            ->where('owner_user_id', $ownerUserId)
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->typeFilter !== 'all', fn ($query) => $query->where('type', $this->typeFilter))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('name', 'like', $term)
                        ->orWhere('type', 'like', $term)
                        ->orWhere('short_code', 'like', $term)
                        ->orWhere('destination_url', 'like', $term);
                });
            });
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

    public function downloadableQrSvg(AppQrCode $qrCode): string
    {
        $value = $qrCode->shortUrl() ?: ($qrCode->destination_url ?: url('/'));
        $foreground = (string) ($qrCode->foreground_color ?: '#0f172a');
        $background = (string) ($qrCode->background_color ?: '#ffffff');
        $settings = (array) ($qrCode->settings ?? []);
        $ecc = (string) data_get($settings, 'ecc', 'high');

        if ((string) $qrCode->pattern === 'gradient_rounded') {
            return app(GradientRoundedQrRenderer::class)->render($value, [
                'foreground_color' => $foreground,
                'accent_color' => (string) data_get($settings, 'fallback_dark', '#f97316'),
                'finder_color' => (string) data_get($settings, 'accent_color', '#0f766e'),
                'finder_dot_color' => (string) data_get($settings, 'finder_dot_color', '#264653'),
                'background_color' => $background,
                'ecc' => $ecc,
            ]);
        }

        if ((string) $qrCode->pattern === 'mosaic') {
            return app(BrandMosaicQrRenderer::class)->render($value, [
                'foreground_color' => (string) data_get($settings, 'fallback_dark', $foreground),
                'accent_color' => $foreground,
                'background_color' => $background,
                'ghost_color' => $foreground,
                'shape' => (string) data_get($settings, 'qr_shape', 'circle'),
                'ecc' => $ecc,
                'dark_min' => (int) data_get($settings, 'dark_min', 47),
                'dark_max' => (int) data_get($settings, 'dark_max', 105),
                'ghost_strength' => (int) data_get($settings, 'ghost_strength', 80),
                'ghost_threshold' => (int) data_get($settings, 'ghost_threshold', 8),
            ]);
        }

        return $this->makeQrCodeSvg($value, $foreground, $background, $ecc);
    }

    public function aiQrArtworkDataUri(AppQrCode $qrCode): string
    {
        if ((string) data_get($qrCode->settings, 'source', '') !== 'ai_qr_codes') {
            return '';
        }

        $fileId = (int) data_get($qrCode->settings, 'ai_qr.background_file_id');

        if ($fileId < 1) {
            return '';
        }

        $file = AppFile::query()->find($fileId);

        if (! $file) {
            return '';
        }

        $disk = (string) $file->disk;
        $path = (string) $file->path;

        if ($disk === '' || $path === '' || ! Storage::disk($disk)->exists($path)) {
            return '';
        }

        return 'data:'.($file->mime_type ?: 'image/png').';base64,'.base64_encode(Storage::disk($disk)->get($path));
    }

    protected function makeQrCodeSvg(string $value, string $foreground = '#0f172a', string $background = '#ffffff', string $ecc = 'high'): string
    {
        [$foregroundR, $foregroundG, $foregroundB] = $this->hexToRgb($foreground, [15, 23, 42]);
        [$backgroundR, $backgroundG, $backgroundB] = $this->hexToRgb($background, [255, 255, 255]);
        $renderer = new ImageRenderer(
            new RendererStyle(
                180,
                4,
                null,
                null,
                Fill::uniformColor(new Rgb($backgroundR, $backgroundG, $backgroundB), new Rgb($foregroundR, $foregroundG, $foregroundB))
            ),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($value, ecLevel: $this->errorCorrectionLevel($ecc));
    }

    protected function errorCorrectionLevel(string $ecc): ErrorCorrectionLevel
    {
        return match ($ecc) {
            'low' => ErrorCorrectionLevel::L(),
            'medium' => ErrorCorrectionLevel::M(),
            'quartile' => ErrorCorrectionLevel::Q(),
            default => ErrorCorrectionLevel::H(),
        };
    }

    protected function hexToRgb(string $hex, array $fallback): array
    {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#'.$hex;

        if (! preg_match('/^#[0-9a-f]{6}$/', $hex)) {
            return $fallback;
        }

        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }
}
