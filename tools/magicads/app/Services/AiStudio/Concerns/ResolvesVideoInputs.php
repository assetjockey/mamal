<?php

namespace App\Services\AiStudio\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Shared input-resolution helpers for video drivers/clients:
 *   - reading a reference image off the public disk as a base64 data URI,
 *   - mapping pixel dimensions to an aspect-ratio label,
 *   - picking a quality tier (480p/720p/1080p/4k) that both honours the
 *     user's selection and stays within what a given vendor can produce.
 */
trait ResolvesVideoInputs
{
    /** Canonical ascending order of the quality tiers we understand. */
    protected array $tierOrder = ['480p', '720p', '1080p', '4k'];

    /** Read a public-disk image as a base64 data URI (for image-to-video). */
    protected function referenceImageDataUri(string $path): ?string
    {
        $bytes = Storage::disk('public')->get($path);

        if ($bytes === null) {
            return null;
        }

        return 'data:'.$this->guessImageMime($path).';base64,'.base64_encode($bytes);
    }

    /**
     * Absolute public URL to a reference image on the public disk — for
     * vendors that ingest a URL rather than an inline data URI (e.g. kie.ai).
     */
    protected function referenceImagePublicUrl(string $path): string
    {
        $url = Storage::disk('public')->url($path);

        return \Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])
            ? $url
            : url($url);
    }

    /**
     * Raw base64 of a public-disk image WITHOUT the `data:` URI prefix — for
     * vendors (e.g. Kling AI direct) whose image field wants bare base64.
     */
    protected function referenceImageBase64(string $path): ?string
    {
        $bytes = Storage::disk('public')->get($path);

        return $bytes === null ? null : base64_encode($bytes);
    }

    protected function guessImageMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            default => 'image/jpeg',
        };
    }

    /** Map pixel dimensions to one of the discrete aspect-ratio labels. */
    protected function resolveAspectRatio(int $width, int $height): string
    {
        $ratio = $width / max($height, 1);

        return match (true) {
            abs($ratio - 21 / 9) < 0.02 => '21:9',
            abs($ratio - 16 / 9) < 0.02 => '16:9',
            abs($ratio - 4 / 3) < 0.02  => '4:3',
            abs($ratio - 1) < 0.02      => '1:1',
            abs($ratio - 3 / 4) < 0.02  => '3:4',
            abs($ratio - 9 / 16) < 0.02 => '9:16',
            default => 'adaptive',
        };
    }

    /**
     * Snap dimensions to the three aspect ratios Kling supports (16:9, 9:16,
     * 1:1) by orientation. Anything close to square → 1:1; landscape → 16:9;
     * portrait → 9:16.
     */
    protected function resolveAspectRatioBasic(int $width, int $height): string
    {
        $ratio = $width / max($height, 1);

        return match (true) {
            abs($ratio - 1) < 0.1 => '1:1',
            $ratio > 1            => '16:9',
            default               => '9:16',
        };
    }

    /** Derive a tier from the shorter side of the frame. */
    protected function tierFromDimensions(int $width, int $height): string
    {
        $short = min($width, $height);

        return match (true) {
            $short >= 2160 => '4k',
            $short >= 1080 => '1080p',
            $short >= 720  => '720p',
            default        => '480p',
        };
    }

    /**
     * Resolve the tier to actually request from a vendor.
     *
     * Prefers the user's explicitly-selected tier; falls back to deriving one
     * from the frame dimensions. The result is then clamped to `$supported`
     * (a vendor may not offer every tier — e.g. fal.ai has no 4k), stepping
     * DOWN to the closest supported tier so we never over-request.
     *
     * @param  array<int, string>  $supported  Tiers this vendor can produce.
     */
    protected function resolveResolutionTier(?string $requested, int $width, int $height, array $supported): string
    {
        $order = $this->tierOrder;
        $supported = array_values(array_intersect($order, $supported));

        if ($supported === []) {
            return '720p';
        }

        $target = ($requested && in_array($requested, $order, true))
            ? $requested
            : $this->tierFromDimensions($width, $height);

        if (in_array($target, $supported, true)) {
            return $target;
        }

        // Step down to the highest supported tier at or below the target.
        $targetIndex = array_search($target, $order, true);
        for ($i = $targetIndex; $i >= 0; $i--) {
            if (in_array($order[$i], $supported, true)) {
                return $order[$i];
            }
        }

        // Nothing lower — use the lowest supported tier.
        return $supported[0];
    }
}
