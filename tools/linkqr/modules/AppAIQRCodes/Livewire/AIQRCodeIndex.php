<?php

namespace Modules\AppAIQRCodes\Livewire;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppAIQRCodes\Support\AIQRCodeImageService;
use Modules\AppFiles\Models\AppFile;
use Modules\AppQRCodes\Models\AppQrCode;
use Modules\AppQRCodes\Support\BrandMosaicQrRenderer;
use Modules\AppQRCodes\Support\GradientRoundedQrRenderer;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Throwable;

#[Title('AI QR Codes')]
class AIQRCodeIndex extends Component
{
    use WithPagination;

    public string $name = '';

    public string $prompt = '';

    public string $textUrl = 'https://example.com/';

    public string $style = 'cinematic';

    public string $qrStyle = 'clean_square';

    public ?string $previewSvg = null;

    public ?int $backgroundFileId = null;

    public ?string $backgroundUrl = null;

    public bool $previewStale = false;

    public array $generated = [];

    public bool $isCreatePage = false;

    public string $search = '';

    public function mount(): void
    {
        abort_unless($this->canUseAIQRCodes(), 403);

        $this->isCreatePage = request()->routeIs('portal.qr-codes.ai.create');
        $this->name = __('AI QR Code').' '.now()->format('His');
        $this->prompt = __('Nature scenery at sunset, waterfall, warm sunlight');
    }

    public function generate(): void
    {
        $this->generatePreview();
    }

    public function save(): void
    {
        abort_unless($this->canUseAIQRCodes(), 403);

        $validated = $this->validateInput();

        if (! $this->previewSvg) {
            $this->addError('prompt', __('Generate an AI QR code first.'));

            return;
        }

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $name = trim((string) $validated['name']);

        $qrCode = AppQrCode::query()->create([
            'owner_user_id' => $ownerId,
            'team_id' => $team?->id,
            'type' => 'dynamic_url',
            'name' => $name,
            'status' => 'active',
            'destination_url' => trim((string) $validated['textUrl']),
            'short_code' => AppQrCode::uniqueShortCode($name),
            'foreground_color' => '#0f172a',
            'background_color' => '#ffffff',
            'pattern' => $this->qrPattern(),
            'settings' => [
                'source' => 'ai_qr_codes',
                'style_template' => $this->builderStyleTemplate(),
                'public_template' => 'split_showcase',
                'ai_qr' => [
                    'prompt' => trim((string) $validated['prompt']),
                    'style' => $this->style,
                    'template' => 'clean_overlay',
                    'qr_style' => $this->qrStyle,
                    'background_file_id' => $this->backgroundFileId,
                    'background_url' => $this->backgroundUrl,
                    'provider' => $this->generated['provider'] ?? null,
                    'model' => $this->generated['model'] ?? null,
                    'complete_qr' => (bool) ($this->generated['complete_qr'] ?? false),
                    'preview_svg' => $this->previewSvg,
                ],
            ],
        ]);

        $this->dispatch('app-toast', type: 'success', message: __('AI QR code saved.'));
        $this->redirectRoute('portal.qr-codes.ai', navigate: true);
    }

    public function render(): View
    {
        abort_unless($this->canUseAIQRCodes(), 403);

        return view('appaiqrcodes::index', [
            'creditCost' => $this->creditCost(),
            'creditSummary' => function_exists('credit_summary') ? credit_summary($this->creditUser()) : null,
            'savedAiQrCodes' => $this->savedAiQrCodes(),
            'savedAiQrCodesTotal' => $this->savedAiQrCodesTotal(),
            'qrStyleOptions' => [
                ['key' => 'clean_square', 'label' => __('Clean square'), 'description' => __('Recommended. Classic high contrast QR for maximum reliability.'), 'icon' => 'fa-qrcode'],
                ['key' => 'premium_round', 'label' => __('Premium round'), 'description' => __('Rounded gradient modules with branded finder corners.'), 'icon' => 'fa-circle-dot'],
                ['key' => 'blue_mosaic', 'label' => __('Blue mosaic'), 'description' => __('Soft dot mosaic with scan-safe accent pattern.'), 'icon' => 'fa-grid-2'],
                ['key' => 'gold_luxury', 'label' => __('Gold luxury'), 'description' => __('Black and gold QR for premium campaigns.'), 'icon' => 'fa-gem'],
                ['key' => 'neon_cyan', 'label' => __('Neon cyan'), 'description' => __('Bright cyan rounded QR for dark posters.'), 'icon' => 'fa-bolt'],
                ['key' => 'art_fusion', 'label' => __('Art fusion'), 'description' => __('Experimental. AI-image texture blended into QR modules; always scan-test carefully.'), 'icon' => 'fa-wand-magic-sparkles'],
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('AI QR Codes')]);
    }

    public function deleteSaved(int $qrCodeId): void
    {
        abort_unless($this->canUseAIQRCodes(), 403);

        $qrCode = AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('settings->source', 'ai_qr_codes')
            ->findOrFail($qrCodeId);

        $qrCode->delete();

        $this->dispatch('app-toast', type: 'success', message: __('AI QR code deleted.'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedQrStyle(): void
    {
        $this->refreshExistingPreview();
    }

    public function updatedPrompt(): void
    {
        $this->markPreviewStale();
    }

    public function updatedTextUrl(): void
    {
        $this->markPreviewStale();
    }

    protected function generatePreview(): void
    {
        abort_unless($this->canUseAIQRCodes(), 403);

        $validated = $this->validateInput();

        try {
            $this->resetErrorBag();
            $this->previewStale = false;

            $result = app(AIQRCodeImageService::class)->generateForUser(
                auth()->user(),
                trim((string) $validated['prompt']),
                $this->style,
                trim((string) $validated['textUrl'])
            );

            $file = $result['file'];
            $this->backgroundFileId = $file->id;
            $this->backgroundUrl = URL::signedRoute('portal.files.publish-preview', ['file' => $file]);

            $this->previewSvg = ($result['complete_qr'] ?? false)
                ? $this->renderGeneratedQrSvg($this->backgroundUrl)
                : $this->renderAiQrSvg(
                    trim((string) $validated['textUrl']),
                    trim((string) $validated['prompt']),
                    $this->backgroundUrl
                );
            $this->generated = [
                'prompt' => trim((string) $validated['prompt']),
                'style' => $this->style,
                'template' => 'clean_overlay',
                'qr_style' => $this->qrStyle,
                'provider' => $result['provider'] ?? null,
                'model' => $result['model'] ?? null,
                'complete_qr' => $result['complete_qr'] ?? false,
                'generated_at' => now()->toDateTimeString(),
            ];

            $this->dispatch('app-toast', type: 'success', message: __('AI QR code generated.'));
        } catch (Throwable $exception) {
            $this->previewStale = false;
            $message = $exception->getMessage() ?: __('Could not generate AI QR code.');
            $this->addError('prompt', $message);
            $this->dispatch('app-toast', type: 'error', message: $message);
        }
    }

    protected function validateInput(): array
    {
        return $this->validate([
            'name' => ['required', 'string', 'max:160'],
            'prompt' => ['required', 'string', 'max:800'],
            'textUrl' => ['required', 'string', 'max:2000'],
            'style' => ['required', 'in:cinematic,nature,city,product,minimal'],
            'qrStyle' => ['required', 'in:art_fusion,premium_round,blue_mosaic,gold_luxury,neon_cyan,clean_square'],
        ]);
    }

    protected function refreshExistingPreview(): void
    {
        if (! $this->backgroundUrl || ! $this->previewSvg || $this->previewStale) {
            return;
        }

        if (($this->generated['complete_qr'] ?? false) === true) {
            return;
        }

        $this->previewSvg = $this->renderAiQrSvg(
            trim($this->textUrl),
            trim($this->prompt),
            $this->backgroundUrl
        );
    }

    protected function markPreviewStale(): void
    {
        if (! $this->previewSvg) {
            return;
        }

        $this->previewStale = true;
    }

    protected function resetGeneratedState(): void
    {
        $this->name = __('AI QR Code').' '.now()->format('His');
        $this->previewSvg = null;
        $this->backgroundFileId = null;
        $this->backgroundUrl = null;
        $this->previewStale = false;
        $this->generated = [];
    }

    protected function savedAiQrCodes()
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $paginator = AppQrCode::query()
            ->where('owner_user_id', $ownerId)
            ->where('settings->source', 'ai_qr_codes')
            ->when(trim($this->search) !== '', function ($query): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)).'%';

                $query->where(function ($builder) use ($term): void {
                    $builder
                        ->where('name', 'like', $term)
                        ->orWhere('destination_url', 'like', $term)
                        ->orWhere('short_code', 'like', $term)
                        ->orWhere('settings->ai_qr->prompt', 'like', $term);
                });
            })
            ->latest()
            ->paginate(10);
        $items = $paginator->getCollection();

        $fileIds = $items
            ->map(fn (AppQrCode $qrCode): int => (int) data_get($qrCode->settings, 'ai_qr.background_file_id'))
            ->filter()
            ->unique()
            ->values();
        $files = AppFile::query()
            ->whereIn('id', $fileIds)
            ->get()
            ->keyBy('id');

        $paginator->setCollection($items->map(function (AppQrCode $qrCode) use ($files): array {
            $fileId = (int) data_get($qrCode->settings, 'ai_qr.background_file_id');
            $file = $files->get($fileId);
            $imageUrl = $file ? URL::signedRoute('portal.files.publish-preview', ['file' => $file]) : (string) data_get($qrCode->settings, 'ai_qr.background_url', '');

            return [
                'id' => $qrCode->id,
                'name' => $qrCode->name,
                'destination_url' => $qrCode->destination_url,
                'short_url' => $qrCode->shortUrl(),
                'image_url' => $imageUrl,
                'edit_url' => route('portal.qr-codes.edit', $qrCode),
                'open_url' => $qrCode->shortUrl() ?: $qrCode->destination_url,
                'download_url' => $file ? route('portal.files.download', $file) : null,
                'created_label' => optional($qrCode->created_at)->format('M d, Y'),
            ];
        }));

        return $paginator;
    }

    protected function savedAiQrCodesTotal(): int
    {
        return AppQrCode::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->where('settings->source', 'ai_qr_codes')
            ->count();
    }

    protected function renderAiQrSvg(string $value, string $prompt, string $backgroundUrl): string
    {
        $qrSvg = $this->qrStyle === 'art_fusion'
            ? $this->artFusionQrSvg($value, $backgroundUrl)
            : $this->qrSvg($value, '#0f172a', '#ffffff');
        $qrData = 'data:image/svg+xml;base64,'.base64_encode($qrSvg);

        return $this->renderCleanOverlaySvg($qrData, $backgroundUrl);
    }

    protected function renderGeneratedQrSvg(string $imageUrl): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1024" viewBox="0 0 1024 1024" role="img" aria-label="AI QR code">'
            .'<rect width="1024" height="1024" rx="48" fill="#ffffff"/>'
            .'<image href="'.e($imageUrl).'" width="1024" height="1024" preserveAspectRatio="xMidYMid slice"/>'
            .'</svg>';
    }

    protected function renderCleanOverlaySvg(string $qrData, string $backgroundUrl): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1024" viewBox="0 0 1024 1024" role="img" aria-label="AI QR code">'
            .'<defs><filter id="shadow" x="-22%" y="-22%" width="144%" height="144%"><feDropShadow dx="0" dy="24" stdDeviation="22" flood-color="#020617" flood-opacity="0.3"/></filter><radialGradient id="soft-vignette" cx="50%" cy="48%" r="72%"><stop offset="0%" stop-color="#ffffff" stop-opacity="0"/><stop offset="100%" stop-color="#020617" stop-opacity="0.22"/></radialGradient></defs>'
            .'<rect width="1024" height="1024" rx="48" fill="#0f172a"/>'
            .'<image href="'.e($backgroundUrl).'" width="1024" height="1024" preserveAspectRatio="xMidYMid slice"/>'
            .'<rect width="1024" height="1024" rx="48" fill="url(#soft-vignette)"/>'
            .($this->qrStyle === 'art_fusion'
                ? '<g filter="url(#shadow)"><rect x="156" y="156" width="712" height="712" rx="76" fill="#ffffff" opacity="0.97"/><image href="'.$qrData.'" x="196" y="196" width="632" height="632" preserveAspectRatio="xMidYMid meet"/></g>'
                : '<g filter="url(#shadow)"><rect x="196" y="196" width="632" height="632" rx="56" fill="#ffffff" opacity="0.97"/><image href="'.$qrData.'" x="236" y="236" width="552" height="552" preserveAspectRatio="xMidYMid meet"/></g>')
            .'</svg>';
    }

    protected function artFusionQrSvg(string $value, string $backgroundUrl): string
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::H(), 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 10;
        $canvasModules = $matrixSize + ($margin * 2);
        $canvasSize = $canvasModules * $unit;
        $modules = [];

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y) || $this->isFinderModule($x, $y, $matrixSize)) {
                    continue;
                }

                $px = ($x + $margin) * $unit;
                $py = ($y + $margin) * $unit;
                $modules[] = '<rect x="'.$px.'" y="'.$py.'" width="'.($unit * 0.88).'" height="'.($unit * 0.88).'" rx="'.($unit * 0.28).'"/>';
            }
        }

        $finders = implode('', [
            $this->artFusionFinder($margin, $unit, 0, 0),
            $this->artFusionFinder($margin, $unit, $matrixSize - 7, 0),
            $this->artFusionFinder($margin, $unit, 0, $matrixSize - 7),
        ]);

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="AI QR code">'
            .'<defs>'
            .'<clipPath id="ai-qr-modules">'.implode('', $modules).'</clipPath>'
            .'<filter id="module-shadow"><feDropShadow dx="0" dy="0" stdDeviation="0.25" flood-color="#020617" flood-opacity="0.35"/></filter>'
            .'<linearGradient id="module-tone" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0f172a" stop-opacity="0.96"/><stop offset="58%" stop-color="#2563eb" stop-opacity="0.9"/><stop offset="100%" stop-color="#020617" stop-opacity="0.98"/></linearGradient>'
            .'</defs>'
            .'<g clip-path="url(#ai-qr-modules)">'
            .'<image href="'.e($backgroundUrl).'" x="0" y="0" width="'.$canvasSize.'" height="'.$canvasSize.'" preserveAspectRatio="xMidYMid slice" opacity="0.28"/>'
            .'<rect width="100%" height="100%" fill="url(#module-tone)" opacity="0.92"/>'
            .'</g>'
            .'<g fill="#020617" opacity="0.72" filter="url(#module-shadow)">'.implode('', $modules).'</g>'
            .$finders
            .'</svg>';
    }

    protected function artFusionFinder(int $margin, int $unit, int $x, int $y): string
    {
        $px = ($x + $margin) * $unit;
        $py = ($y + $margin) * $unit;
        $size = $unit * 7;

        return '<rect x="'.$px.'" y="'.$py.'" width="'.$size.'" height="'.$size.'" rx="'.($unit * 1.2).'" fill="#0f172a"/>'
            .'<rect x="'.($px + $unit * 1.1).'" y="'.($py + $unit * 1.1).'" width="'.($unit * 4.8).'" height="'.($unit * 4.8).'" rx="'.($unit * 0.75).'" fill="#ffffff"/>'
            .'<rect x="'.($px + $unit * 2.05).'" y="'.($py + $unit * 2.05).'" width="'.($unit * 2.9).'" height="'.($unit * 2.9).'" rx="'.($unit * 0.55).'" fill="#38bdf8"/>';
    }

    protected function isFinderModule(int $x, int $y, int $size): bool
    {
        return ($x <= 6 && $y <= 6)
            || ($x >= $size - 7 && $y <= 6)
            || ($x <= 6 && $y >= $size - 7);
    }

    protected function qrSvg(string $value, string $foreground, string $background): string
    {
        $style = $this->qrStyleOptions()[$this->qrStyle] ?? $this->qrStyleOptions()['premium_round'];

        if (($style['pattern'] ?? null) === 'gradient_rounded') {
            return app(GradientRoundedQrRenderer::class)->render($value, [
                'foreground_color' => $style['foreground'],
                'accent_color' => $style['accent'],
                'finder_color' => $style['finder'],
                'finder_dot_color' => $style['finder_dot'],
                'background_color' => $background,
                'ecc' => 'high',
            ]);
        }

        if (($style['pattern'] ?? null) === 'mosaic') {
            return app(BrandMosaicQrRenderer::class)->render($value, [
                'foreground_color' => $style['foreground'],
                'accent_color' => $style['accent'],
                'background_color' => $background,
                'ghost_color' => $style['ghost'],
                'shape' => $style['shape'],
                'ecc' => 'high',
                'dark_min' => $style['dark_min'],
                'dark_max' => $style['dark_max'],
                'ghost_strength' => $style['ghost_strength'],
                'ghost_threshold' => $style['ghost_threshold'],
            ]);
        }

        [$fr, $fg, $fb] = $this->hexToRgb($foreground, [15, 23, 42]);
        [$br, $bg, $bb] = $this->hexToRgb($background, [255, 255, 255]);
        $renderer = new ImageRenderer(
            new RendererStyle(
                620,
                4,
                null,
                null,
                Fill::uniformColor(new Rgb($br, $bg, $bb), new Rgb($fr, $fg, $fb))
            ),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($value, ecLevel: ErrorCorrectionLevel::H());
    }

    protected function qrPattern(): string
    {
        return (string) ($this->qrStyleOptions()[$this->qrStyle]['pattern'] ?? 'square');
    }

    protected function builderStyleTemplate(): string
    {
        return match ($this->qrPattern()) {
            'gradient_rounded' => 'rounded_gradient',
            'mosaic' => 'default_mosaic',
            default => 'clean_card',
        };
    }

    protected function qrStyleOptions(): array
    {
        return [
            'art_fusion' => [
                'pattern' => 'mosaic',
            ],
            'premium_round' => [
                'pattern' => 'gradient_rounded',
                'foreground' => '#2563eb',
                'accent' => '#7c3aed',
                'finder' => '#0f172a',
                'finder_dot' => '#38bdf8',
            ],
            'blue_mosaic' => [
                'pattern' => 'mosaic',
                'foreground' => '#0f172a',
                'accent' => '#2563eb',
                'ghost' => '#60a5fa',
                'shape' => 'circle',
                'dark_min' => 54,
                'dark_max' => 112,
                'ghost_strength' => 70,
                'ghost_threshold' => 12,
            ],
            'gold_luxury' => [
                'pattern' => 'gradient_rounded',
                'foreground' => '#111827',
                'accent' => '#d97706',
                'finder' => '#ca8a04',
                'finder_dot' => '#111827',
            ],
            'neon_cyan' => [
                'pattern' => 'mosaic',
                'foreground' => '#083344',
                'accent' => '#06b6d4',
                'ghost' => '#67e8f9',
                'shape' => 'rounded',
                'dark_min' => 58,
                'dark_max' => 118,
                'ghost_strength' => 78,
                'ghost_threshold' => 10,
            ],
            'clean_square' => [
                'pattern' => 'square',
            ],
        ];
    }

    protected function hexToRgb(string $hex, array $fallback): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return $fallback;
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    protected function canUseAIQRCodes(): bool
    {
        $planOwner = $this->creditUser();

        return ! $planOwner?->plan || ($planOwner?->canUsePlanFeature('ai_qr_codes') ?? false);
    }

    protected function creditUser()
    {
        $user = auth()->user();

        return TeamWorkspaceAccess::activeTeam($user)?->owner ?: $user;
    }

    protected function creditCost(): int
    {
        return function_exists('credit_service')
            ? credit_service()->costFor($this->creditUser(), 'ai_qr_generate', 5)
            : 5;
    }
}
