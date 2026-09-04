<?php

namespace App\Services\AiStudio;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Burns user-typed headline and CTA text into a generated video clip
 * using the bundled FFmpeg static binary at vendor/ffmpeg/.
 *
 * The strategy is "drawtext over the existing video stream":
 *   1. Read the source MP4 from Storage::disk('results').
 *   2. Compose a single drawtext filter chain — one pass for the
 *      headline (top band, semi-opaque dark plate, brand-secondary tint)
 *      and one pass for the CTA (bottom pill, brand-primary fill,
 *      optional fade-in over the last second).
 *   3. Re-encode to H.264 / AAC (universally compatible) and copy
 *      audio if the input has any (Veo's native audio survives the pass).
 *   4. Replace the original file_path on the asset with the burned
 *      version. Old file is removed.
 *
 * Why FFmpeg's drawtext over libass / overlay PNGs:
 *   - drawtext is the simplest filter and ships with every recent
 *     FFmpeg build (we verified libfreetype + libharfbuzz in the
 *     bundled binary).
 *   - We avoid having to render an intermediate PNG with Imagick.
 *   - Brand colors and timing become a single string per overlay.
 *
 * Failure mode is graceful: if the overlay fails for any reason
 * (missing font, exec disabled, FFmpeg crash) the asset keeps its
 * original AI-rendered file_path and an error is logged. The user
 * never sees a "generation failed" because of an overlay issue.
 */
class VideoOverlayService
{
    public function __construct() {}

    /**
     * Burn the overlay onto the given asset's video.
     *
     * @param  string  $relativePath  Path on the `results` disk (e.g. videos/runway/abc.mp4).
     * @param  ?string  $headline  Headline / overlayText (top band). Null/empty = skipped.
     * @param  ?string  $cta  CTA pill (bottom band). Null/empty = skipped.
     * @param  ?string  $primaryColor  Hex; falls back to config default. Used for CTA fill.
     * @param  ?string  $secondaryColor  Hex; falls back to config default. Used for headline plate.
     * @return string|null The new path on `results` (likely the same as input). Null on failure.
     */
    public function burn(
        string $relativePath,
        ?string $headline,
        ?string $cta,
        ?string $primaryColor = null,
        ?string $secondaryColor = null,
    ): ?string {
        if (! config('ai-studio.overlay.enabled', true)) {
            return $relativePath;
        }

        $headline = trim((string) $headline);
        $cta = trim((string) $cta);

        if ($headline === '' && $cta === '') {
            // Nothing to burn — return the input untouched.
            return $relativePath;
        }

        $disk = Storage::disk('results');

        if (! $disk->exists($relativePath)) {
            Log::warning('VideoOverlayService: input file not found', ['path' => $relativePath]);

            return null;
        }

        $absoluteIn = $this->absolutePath($disk, $relativePath);
        $tempOut = $this->makeTempPath($relativePath);
        $absoluteOut = $this->absolutePath($disk, $tempOut);

        // Resolve binary + font.
        $ffmpeg = (string) config('laravel-ffmpeg.ffmpeg.binaries', 'ffmpeg');
        $defaultFontPath = (string) config('ai-studio.overlay.font_path');

        if (! file_exists($defaultFontPath)) {
            Log::warning('VideoOverlayService: default font not found, overlay skipped', ['font' => $defaultFontPath]);

            return $relativePath;
        }

        // Build the filter graph.
        $filterChain = $this->buildFilterChain(
            headline: $headline,
            cta: $cta,
            primaryColor: $primaryColor ?: (config('ai-studio.overlay.cta.background') ?? '#4F46E5'),
            secondaryColor: $secondaryColor ?: (config('ai-studio.overlay.headline.box_color') ?? '#0F172A'),
            defaultFontPath: $defaultFontPath,
        );

        if ($filterChain === '') {
            return $relativePath;
        }

        // Build the FFmpeg command.
        $args = [
            $ffmpeg,
            '-y',
            '-loglevel', 'error',
            '-i', $absoluteIn,
            '-vf', $filterChain,
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '20',
            '-pix_fmt', 'yuv420p',
            // Copy audio if present, otherwise -c:a copy is a no-op for
            // streams without audio. Veo's native audio survives this.
            '-c:a', 'copy',
            '-movflags', '+faststart',
            $absoluteOut,
        ];

        $process = new Process(
            command: $args,
            timeout: (int) config('laravel-ffmpeg.timeout', 600),
        );

        try {
            $process->mustRun();
        } catch (\Throwable $e) {
            Log::error('VideoOverlayService: FFmpeg failed', [
                'path' => $relativePath,
                'message' => $e->getMessage(),
                'stderr' => $process->getErrorOutput(),
            ]);

            // Best-effort cleanup of partial output.
            if ($disk->exists($tempOut)) {
                $disk->delete($tempOut);
            }

            // Don't fail the whole generation — fall back to the original.
            return $relativePath;
        }

        // Atomic-ish swap: replace the original with the burned version.
        $disk->delete($relativePath);
        $disk->move($tempOut, $relativePath);

        return $relativePath;
    }

    /**
     * Build the FFmpeg `-vf` filter chain string for the configured overlays.
     */
    protected function buildFilterChain(
        string $headline,
        string $cta,
        string $primaryColor,
        string $secondaryColor,
        string $defaultFontPath,
    ): string {
        $cfg = (array) config('ai-studio.overlay', []);
        $hCfg = (array) ($cfg['headline'] ?? []);
        $cCfg = (array) ($cfg['cta'] ?? []);

        // Per-element font with graceful fallback to the global default.
        $headlineFont = $this->resolveFont($hCfg['font_path'] ?? null, $defaultFontPath);
        $ctaFont = $this->resolveFont($cCfg['font_path'] ?? null, $defaultFontPath);

        $filters = [];

        // ---------- Headline ----------
        if ($headline !== '') {
            $sizeRatio = (float) ($hCfg['size_ratio'] ?? 0.06);
            $color = $this->normalizeColor($hCfg['color'] ?? '#FFFFFF');
            $boxColor = $this->normalizeColor($secondaryColor);
            $boxOpacity = (float) ($hCfg['box_opacity'] ?? 0.55);
            $boxPadding = (int) ($hCfg['box_padding'] ?? 18);
            $topMargin = (float) ($hCfg['top_margin_ratio'] ?? 0.06);

            $filters[] = sprintf(
                "drawtext=fontfile='%s':text='%s':fontsize=h*%s:fontcolor=%s:box=1:boxcolor=%s@%.2f:boxborderw=%d:x=(w-text_w)/2:y=h*%s",
                $this->escapeFontPath($headlineFont),
                $this->escapeText($headline),
                $this->formatRatio($sizeRatio),
                $color,
                $boxColor,
                $boxOpacity,
                $boxPadding,
                $topMargin,
            );
        }

        // ---------- CTA ----------
        if ($cta !== '') {
            $sizeRatio = (float) ($cCfg['size_ratio'] ?? 0.045);
            $color = $this->normalizeColor($cCfg['color'] ?? '#FFFFFF');
            $bgColor = $this->normalizeColor($primaryColor);
            $bgOpacity = (float) ($cCfg['background_opacity'] ?? 0.92);
            $padX = (int) ($cCfg['padding_x'] ?? 22);
            $padY = (int) ($cCfg['padding_y'] ?? 10);
            $bottomMargin = (float) ($cCfg['bottom_margin_ratio'] ?? 0.06);
            $fadeIn = $cCfg['fade_in_seconds'] ?? null;

            $expr = sprintf(
                "drawtext=fontfile='%s':text='%s':fontsize=h*%s:fontcolor=%s:box=1:boxcolor=%s@%.2f:boxborderw=%d:x=(w-text_w)/2:y=h-text_h-h*%s-%d",
                $this->escapeFontPath($ctaFont),
                $this->escapeText($cta),
                $this->formatRatio($sizeRatio),
                $color,
                $bgColor,
                $bgOpacity,
                max($padX, $padY),
                $this->formatRatio($bottomMargin),
                $padY,
            );

            // Fade-in: alpha ramps from 0→1 over `fade_in_seconds` from t=0.
            if ($fadeIn !== null && (float) $fadeIn > 0) {
                $fadeIn = (float) $fadeIn;
                $expr .= sprintf(":alpha='if(lt(t,%.2f), t/%.2f, 1)'", $fadeIn, $fadeIn);
            }

            $filters[] = $expr;
        }

        return implode(',', $filters);
    }

    /**
     * Pick a per-element font, falling back to the default if the configured
     * file doesn't exist. Lets each overlay element use a different weight
     * (e.g. Headline → Inter Black, CTA → Inter ExtraBold) without breaking
     * if the user only has one font on disk.
     */
    protected function resolveFont(?string $configured, string $default): string
    {
        if ($configured && file_exists($configured)) {
            return $configured;
        }

        return $default;
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Resolve a Storage-relative path to an absolute filesystem path.
     * Works for any local/disk filesystem (the `results` disk in this app
     * is local). For remote disks (S3 etc.) you'd download → process →
     * re-upload, which is out of scope here.
     */
    protected function absolutePath($disk, string $relativePath): string
    {
        return $disk->path($relativePath);
    }

    protected function makeTempPath(string $relativePath): string
    {
        $dir = dirname($relativePath);
        $name = pathinfo($relativePath, PATHINFO_FILENAME);
        $ext = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'mp4';

        return ($dir === '.' ? '' : $dir.'/').$name.'__overlay_'.Str::random(6).'.'.$ext;
    }

    /**
     * Escape text for FFmpeg's drawtext filter:
     *   - backslash, single-quote, colon, percent need escaping
     *   - the surrounding single-quotes are already in the format string
     */
    protected function escapeText(string $text): string
    {
        $text = preg_replace('/[\r\n]+/', ' ', $text) ?? '';
        $text = trim($text);

        return strtr($text, [
            '\\' => '\\\\\\\\',
            "'" => "\\\\\\'",
            ':' => '\\\\:',
            '%' => '\\\\%',
        ]);
    }

    /**
     * Escape a font path for the drawtext `fontfile=` argument.
     * Backslashes need doubling for Windows paths; colons in C:\ paths
     * need to be escaped because FFmpeg uses `:` as the filter separator.
     */
    protected function escapeFontPath(string $path): string
    {
        // Use forward slashes everywhere (FFmpeg accepts them on Windows too).
        $path = str_replace('\\', '/', $path);

        return strtr($path, [
            ':' => '\\:',
            "'" => "\\'",
        ]);
    }

    /** Normalize a hex color into FFmpeg's #RRGGBB form. */
    protected function normalizeColor(?string $color): string
    {
        if (! $color) {
            return '#000000';
        }

        $color = ltrim(trim($color), '#');

        if (strlen($color) === 3) {
            $color = preg_replace('/(.)/', '\\1\\1', $color);
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
            return '#000000';
        }

        return '#'.strtoupper($color);
    }

    /** Format a float ratio for FFmpeg expression syntax (no exponent). */
    protected function formatRatio(float $ratio): string
    {
        return rtrim(rtrim(sprintf('%.4F', $ratio), '0'), '.');
    }
}
