<?php

namespace Modules\AppLinkBioShareKit\Livewire;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Common\ErrorCorrectionLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppLinkBio\Models\LinkBioShortLink;
use Modules\AppLinkBio\Support\LinkBioAccess;
use Modules\AppLinkBioShareKit\Models\LinkBioQrDesign;

#[Title('Bio QR & Share')]
class ShareKitIndex extends Component
{
    public array $designState = [];

    public function fontOptions(): array
    {
        return [
            'Inter',
            'Roboto',
            'Open Sans',
            'Montserrat',
            'Poppins',
            'Lato',
            'Nunito',
            'Playfair Display',
            'Merriweather',
            'Oswald',
            'Raleway',
            'Noto Sans',
            'Noto Serif',
            'Noto Sans Arabic',
            'Noto Sans JP',
            'Noto Sans SC',
            'Noto Sans Devanagari',
        ];
    }

    public function saveDesign(int $pageId, LinkBioAccess $access): void
    {
        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseQrCodes(auth()->user()), 403);

        $page = LinkBioPage::query()
            ->ownedBy($access->workspaceOwnerUserId(auth()->user()))
            ->findOrFail($pageId);
        $state = (array) ($this->designState[$pageId] ?? []);

        $validated = validator($state, [
            'foreground_color' => ['nullable', 'string', 'max:24'],
            'background_color' => ['nullable', 'string', 'max:24'],
            'gradient_type' => ['nullable', 'string', 'max:30'],
            'pattern' => ['nullable', 'string', 'max:60'],
            'sticker_text' => ['nullable', 'string', 'max:160'],
            'sticker_font' => ['nullable', 'string', 'max:120'],
            'sticker_shape' => ['nullable', Rule::in(['none', 'label', 'rounded', 'ticket'])],
            'logo_url' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        LinkBioQrDesign::query()->updateOrCreate(
            ['link_bio_page_id' => (int) $page->id],
            [
                'owner_user_id' => (int) $page->owner_user_id,
                'name' => 'Default',
                'foreground_color' => $validated['foreground_color'] ?: '#0f172a',
                'background_color' => $validated['background_color'] ?: '#ffffff',
                'gradient_type' => $validated['gradient_type'] ?: null,
                'pattern' => $validated['pattern'] ?: 'square',
                'sticker_text' => trim((string) ($validated['sticker_text'] ?? '')),
                'sticker_font' => trim((string) ($validated['sticker_font'] ?? 'Inter')) ?: 'Inter',
                'sticker_shape' => $validated['sticker_shape'] ?: 'none',
                'logo_url' => trim((string) ($validated['logo_url'] ?? '')) ?: null,
            ]
        );

        $this->dispatch('app-toast', type: 'success', message: __('QR code design saved.'));
    }

    public function applyPalette(int $pageId, string $foreground, string $background, ?string $gradientType = null): void
    {
        $this->designState[$pageId]['foreground_color'] = $this->normalizeHex($foreground, '#0f172a');
        $this->designState[$pageId]['background_color'] = $this->normalizeHex($background, '#ffffff');
        $this->designState[$pageId]['gradient_type'] = $gradientType ?: null;
    }

    public function applyPattern(int $pageId, string $pattern): void
    {
        $allowed = ['square', 'dots', 'rounded', 'mosaic', 'outlined', 'sticker'];
        $this->designState[$pageId]['pattern'] = in_array($pattern, $allowed, true) ? $pattern : 'square';
    }

    public function render(LinkBioAccess $access): View
    {
        abort_unless($access->enabled(auth()->user()), 404);
        abort_unless($access->canUseQrCodes(auth()->user()), 403);

        $pages = LinkBioPage::query()
            ->ownedBy($access->workspaceOwnerUserId(auth()->user()))
            ->orderByDesc('updated_at')
            ->get();
        $shortLinks = $pages
            ->mapWithKeys(fn (LinkBioPage $page) => [(int) $page->id => LinkBioShortLink::forPage($page)])
            ->all();
        $designs = LinkBioQrDesign::query()
            ->whereIn('link_bio_page_id', $pages->pluck('id')->all())
            ->get()
            ->keyBy('link_bio_page_id');

        foreach ($pages as $page) {
            $design = $designs->get((int) $page->id);
            $this->designState[(int) $page->id] ??= [
                'foreground_color' => $design?->foreground_color ?: '#0f172a',
                'background_color' => $design?->background_color ?: '#ffffff',
                'gradient_type' => $design?->gradient_type ?: null,
                'pattern' => $design?->pattern ?: 'square',
                'sticker_text' => $design?->sticker_text ?: __('Scan me'),
                'sticker_font' => $design?->sticker_font ?: 'Inter',
                'sticker_shape' => $design?->sticker_shape ?: 'label',
                'logo_url' => $design?->logo_url ?: '',
            ];
        }

        return view('applinkbiosharekit::index', [
            'pages' => $pages,
            'shortLinks' => $shortLinks,
            'designs' => $designs,
            'fontOptions' => $this->fontOptions(),
            'qrCodes' => $pages
                ->mapWithKeys(function (LinkBioPage $page) use ($shortLinks): array {
                    $shortLink = $shortLinks[(int) $page->id] ?? null;
                    $state = (array) ($this->designState[(int) $page->id] ?? []);

                    return [
                        (int) $page->id => $this->makeQrCodeSvg(
                            $shortLink ? route('link-bio.short.show', ['code' => $shortLink->code]) : route('link-bio.public.show', ['slug' => $page->slug]),
                            (string) ($state['foreground_color'] ?? '#0f172a'),
                            (string) ($state['background_color'] ?? '#ffffff')
                        ),
                    ];
                })
                ->all(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Bio QR & Share'),
        ]);
    }

    protected function makeQrCodeSvg(string $value, string $foreground = '#0f172a', string $background = '#ffffff'): string
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
            new SvgImageBackEnd()
        );

        return (new Writer($renderer))->writeString($value, ecLevel: ErrorCorrectionLevel::H());
    }

    protected function normalizeHex(string $hex, string $fallback): string
    {
        $hex = strtolower(trim($hex));

        if ($hex !== '' && $hex[0] !== '#') {
            $hex = '#'.$hex;
        }

        if (preg_match('/^#[0-9a-f]{3}$/', $hex)) {
            $hex = '#'.$hex[1].$hex[1].$hex[2].$hex[2].$hex[3].$hex[3];
        }

        return preg_match('/^#[0-9a-f]{6}$/', $hex) ? $hex : $fallback;
    }

    protected function hexToRgb(string $hex, array $fallback): array
    {
        $hex = $this->normalizeHex($hex, sprintf('#%02x%02x%02x', $fallback[0], $fallback[1], $fallback[2]));

        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }
}
