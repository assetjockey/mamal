<?php

namespace Modules\AppShortLinkQr\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Modules\AppQRCodes\Support\GradientRoundedQrRenderer;
use Modules\AppQRCodes\Support\BrandMosaicQrRenderer;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinks\Models\AppShortLink;

class ShortLinkQrRenderer
{
    public function svg(AppShortLink $shortLink): string
    {
        return app(GradientRoundedQrRenderer::class)->render(
            $shortLink->shortUrl(),
            app(ShortLinkBranding::class)->qrOptions((int) $shortLink->owner_user_id)
        );
    }

    public function styledSvg(AppShortLink $shortLink, array $template): string
    {
        $pattern = (string) ($template['pattern'] ?? 'square');
        $background = (string) ($template['background'] ?? '#ffffff');
        $foreground = (string) ($template['foreground'] ?? '#0f172a');
        $fallback = (string) ($template['fallback'] ?? '#0f172a');
        $accent = (string) ($template['accent'] ?? '#2563eb');
        $brandingOptions = app(ShortLinkBranding::class)->qrOptions((int) $shortLink->owner_user_id);

        if ($pattern === 'mosaic') {
            return app(BrandMosaicQrRenderer::class)->render($shortLink->shortUrl(), [
                'foreground_color' => $fallback,
                'accent_color' => $foreground,
                'background_color' => $background,
                'ghost_color' => $foreground,
                'shape' => (string) ($template['shape'] ?? 'circle'),
                'ecc' => 'high',
                'dark_min' => 47,
                'dark_max' => 105,
                'ghost_strength' => 80,
                'ghost_threshold' => 8,
            ]);
        }

        if ($pattern === 'square') {
            return $this->squareSvg($shortLink->shortUrl(), $foreground, $background);
        }

        return app(GradientRoundedQrRenderer::class)->render($shortLink->shortUrl(), [
            'foreground_color' => $foreground,
            'accent_color' => $pattern === 'gradient_rounded' ? $fallback : $foreground,
            'finder_color' => $accent,
            'finder_dot_color' => $fallback,
            'background_color' => $background,
            'logo_url' => (string) ($brandingOptions['logo_url'] ?? ''),
            'ecc' => 'high',
        ]);
    }

    protected function squareSvg(string $value, string $foreground, string $background): string
    {
        [$foregroundR, $foregroundG, $foregroundB] = $this->hexToRgb($foreground, [15, 23, 42]);
        [$backgroundR, $backgroundG, $backgroundB] = $this->hexToRgb($background, [255, 255, 255]);

        $renderer = new ImageRenderer(
            new RendererStyle(
                180,
                4,
                null,
                null,
                Fill::uniformColor(
                    new Rgb($backgroundR, $backgroundG, $backgroundB),
                    new Rgb($foregroundR, $foregroundG, $foregroundB)
                )
            ),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($value, ecLevel: ErrorCorrectionLevel::H());
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
