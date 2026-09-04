<?php

namespace Modules\AppQRCodes\Support;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class GradientRoundedQrRenderer
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

        $start = $this->hex((string) ($options['foreground_color'] ?? '#b91c1c'), '#b91c1c');
        $end = $this->hex((string) ($options['accent_color'] ?? '#f97316'), '#f97316');
        $finder = $this->hex((string) ($options['finder_color'] ?? '#0f766e'), '#0f766e');
        $finderDot = $this->hex((string) ($options['finder_dot_color'] ?? '#264653'), '#264653');
        $background = $this->hex((string) ($options['background_color'] ?? '#ffffff'), '#ffffff');
        $logoUrl = trim((string) ($options['logo_url'] ?? ''));

        $svg = [];
        $svg[] = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$canvasSize.' '.$canvasSize.'" width="'.$canvasSize.'" height="'.$canvasSize.'" role="img" aria-label="QR code">';
        $svg[] = '<defs><linearGradient id="qr-rounded-gradient" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="'.$start.'"/><stop offset="100%" stop-color="'.$end.'"/></linearGradient></defs>';
        $svg[] = '<rect width="100%" height="100%" fill="'.$background.'"/>';

        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if (! $matrix->get($x, $y) || $this->isFinder($x, $y, $matrixSize)) {
                    continue;
                }

                $px = ($x + $margin) * $unit;
                $py = ($y + $margin) * $unit;
                $radius = $unit * 0.45;
                $svg[] = '<rect x="'.$px.'" y="'.$py.'" width="'.($unit * 0.92).'" height="'.($unit * 0.92).'" rx="'.$radius.'" fill="url(#qr-rounded-gradient)"/>';
            }
        }

        $this->finder($svg, $margin, $unit, 0, 0, $finder, $finderDot, $background);
        $this->finder($svg, $margin, $unit, $matrixSize - 7, 0, $finder, $finderDot, $background);
        $this->finder($svg, $margin, $unit, 0, $matrixSize - 7, $finder, $finderDot, $background);

        if ($logoUrl !== '' && filter_var($logoUrl, FILTER_VALIDATE_URL)) {
            $this->logo($svg, $canvasSize, $logoUrl, $background);
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    protected function finder(array &$svg, int $margin, int $unit, int $x, int $y, string $outer, string $dot, string $background): void
    {
        $px = ($x + $margin) * $unit;
        $py = ($y + $margin) * $unit;
        $size = $unit * 7;

        $svg[] = '<rect x="'.$px.'" y="'.$py.'" width="'.$size.'" height="'.$size.'" rx="'.($unit * 0.8).'" fill="'.$outer.'"/>';
        $svg[] = '<rect x="'.($px + $unit * 1.1).'" y="'.($py + $unit * 1.1).'" width="'.($unit * 4.8).'" height="'.($unit * 4.8).'" rx="'.($unit * 0.45).'" fill="'.$background.'"/>';
        $svg[] = '<rect x="'.($px + $unit * 2.05).'" y="'.($py + $unit * 2.05).'" width="'.($unit * 2.9).'" height="'.($unit * 2.9).'" rx="'.($unit * 0.35).'" fill="'.$dot.'"/>';
    }

    protected function isFinder(int $x, int $y, int $size): bool
    {
        return ($x <= 6 && $y <= 6)
            || ($x >= $size - 7 && $y <= 6)
            || ($x <= 6 && $y >= $size - 7);
    }

    protected function logo(array &$svg, int $canvasSize, string $logoUrl, string $background): void
    {
        $frameSize = (int) round($canvasSize * 0.19);
        $imageSize = (int) round($canvasSize * 0.145);
        $frameX = (int) round(($canvasSize - $frameSize) / 2);
        $imageX = (int) round(($canvasSize - $imageSize) / 2);
        $radius = (int) round($frameSize * 0.24);

        $svg[] = '<rect x="'.$frameX.'" y="'.$frameX.'" width="'.$frameSize.'" height="'.$frameSize.'" rx="'.$radius.'" fill="'.$background.'"/>';
        $svg[] = '<image href="'.e($logoUrl).'" x="'.$imageX.'" y="'.$imageX.'" width="'.$imageSize.'" height="'.$imageSize.'" preserveAspectRatio="xMidYMid slice" clip-path="inset(0 round '.(int) round($imageSize * 0.22).'px)"/>';
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
