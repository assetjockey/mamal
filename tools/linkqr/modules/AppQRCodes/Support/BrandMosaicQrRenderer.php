<?php

namespace Modules\AppQRCodes\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class BrandMosaicQrRenderer
{
    public function render(string $value, array $options = []): string
    {
        $ecc = $this->errorCorrectionLevel((string) ($options['ecc'] ?? 'high'));
        $matrix = Encoder::encode($value, $ecc, 'UTF-8', null, false)->getMatrix();
        $matrixSize = $matrix->getWidth();
        $margin = 4;
        $unit = 8;
        $canvasModules = $matrixSize + ($margin * 2);
        $canvasSize = $canvasModules * $unit;

        $foreground = $this->hex((string) ($options['foreground_color'] ?? '#0f172a'), '#0f172a');
        $accent = $this->hex((string) ($options['accent_color'] ?? $foreground), $foreground);
        $background = $this->hex((string) ($options['background_color'] ?? '#ffffff'), '#ffffff');
        $ghostColor = $this->hex((string) ($options['ghost_color'] ?? '#94a3b8'), '#94a3b8');
        $shape = (string) ($options['shape'] ?? 'circle');
        $ghostStrength = max(0, min(100, (int) ($options['ghost_strength'] ?? 80))) / 100;
        $ghostThreshold = max(0, min(100, (int) ($options['ghost_threshold'] ?? 8))) / 100;
        $darkMin = max(0, min(100, (int) ($options['dark_min'] ?? 47))) / 100;
        $darkMax = max(1, min(140, (int) ($options['dark_max'] ?? 105))) / 100;
        $isFacebook = str_contains(strtolower($value), 'facebook.com');

        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">';
        $svg[] = '<rect width="100%" height="100%" rx="'.($unit * 2).'" fill="'.$background.'"/>';

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                $cx = ($x + $margin + 0.5) * $unit;
                $cy = ($y + $margin + 0.5) * $unit;
                $finder = $this->isFinder($x, $y, $matrixSize);
                $ghost = $this->inGhostMask($x, $y, $matrixSize, $isFacebook);
                $dark = (bool) $matrix->get($x, $y);

                if ($dark) {
                    $scale = $finder ? 0.94 : ($ghost ? max(0.54, min(0.98, $darkMax)) : max(0.32, min(0.78, $darkMin + 0.18)));
                    $color = $finder || $ghost ? $accent : $foreground;
                    $svg[] = $this->dot($cx, $cy, $unit * $scale, $shape, $color, 1);
                    continue;
                }

                if (! $finder && $ghost) {
                    $scale = 0.12 + ($ghostThreshold * 0.22);
                    $opacity = max(0.1, min(0.56, $ghostStrength * 0.52));
                    $svg[] = $this->dot($cx, $cy, $unit * $scale, 'circle', $ghostColor, $opacity);
                }
            }
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function dot(float $cx, float $cy, float $size, string $shape, string $color, float $opacity): string
    {
        $half = $size / 2;
        $opacityAttribute = $opacity < 1 ? ' opacity="'.number_format($opacity, 2, '.', '').'"' : '';

        return match ($shape) {
            'square' => '<rect x="'.($cx - $half).'" y="'.($cy - $half).'" width="'.$size.'" height="'.$size.'" fill="'.$color.'"'.$opacityAttribute.'/>',
            'rounded' => '<rect x="'.($cx - $half).'" y="'.($cy - $half).'" width="'.$size.'" height="'.$size.'" rx="'.($size * 0.28).'" fill="'.$color.'"'.$opacityAttribute.'/>',
            'diamond' => '<path d="M '.$cx.' '.($cy - $half).' L '.($cx + $half).' '.$cy.' L '.$cx.' '.($cy + $half).' L '.($cx - $half).' '.$cy.' Z" fill="'.$color.'"'.$opacityAttribute.'/>',
            default => '<circle cx="'.$cx.'" cy="'.$cy.'" r="'.($size / 2).'" fill="'.$color.'"'.$opacityAttribute.'/>',
        };
    }

    protected function inGhostMask(int $x, int $y, int $size, bool $facebook): bool
    {
        $nx = (($x + 0.5) / $size * 2) - 1;
        $ny = (($y + 0.5) / $size * 2) - 1;

        if ($facebook) {
            $stem = $nx > -0.12 && $nx < 0.1 && $ny > -0.62 && $ny < 0.72;
            $top = $nx > -0.1 && $nx < 0.42 && $ny > -0.62 && $ny < -0.38;
            $bar = $nx > -0.35 && $nx < 0.28 && $ny > -0.18 && $ny < 0.04;
            $roundCut = (($nx - 0.38) ** 2 + (($ny + 0.48) * 1.4) ** 2) < 0.16;

            return ($stem || $top || $bar) && ! $roundCut;
        }

        $body = (($nx * 0.92) ** 2 + (($ny + 0.02) * 1.08) ** 2) < 0.5;
        $inner = (($nx * 1.7) ** 2 + (($ny + 0.02) * 1.9) ** 2) < 0.16;
        $stroke = abs($nx) < 0.12 && $ny > -0.45 && $ny < 0.48;

        return ($body && ! $inner) || $stroke;
    }

    protected function isFinder(int $x, int $y, int $size): bool
    {
        $inTopLeft = $x <= 6 && $y <= 6;
        $inTopRight = $x >= $size - 7 && $y <= 6;
        $inBottomLeft = $x <= 6 && $y >= $size - 7;

        return $inTopLeft || $inTopRight || $inBottomLeft;
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

    protected function hex(string $hex, string $fallback): string
    {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#'.$hex;

        return preg_match('/^#[0-9a-f]{6}$/', $hex) ? $hex : $fallback;
    }
}
