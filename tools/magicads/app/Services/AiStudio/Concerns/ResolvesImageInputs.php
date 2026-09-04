<?php

namespace App\Services\AiStudio\Concerns;

use App\Services\AiStudio\DTOs\GenerationRequest;
use Illuminate\Support\Facades\Storage;

/**
 * Shared helper for image drivers that can ingest reference images
 * (brand logo + user-supplied style/composition reference) alongside the
 * text prompt. Reads the files off the 'public' disk — the same disk the
 * Brand editor and the studio reference uploads write to — and returns them
 * as base64 blobs ready to attach to a multimodal request.
 */
trait ResolvesImageInputs
{
    /**
     * Collect the image inputs for a generation, in priority order:
     *   1. Brand logo (so the real mark is composited, not invented).
     *   2. User reference image (style / composition guide).
     *
     * Each entry: ['data' => base64 string, 'mime' => 'image/png', 'raw' => bytes].
     *
     * @return array<int, array{data: string, mime: string, raw: string}>
     */
    protected function imageInputs(GenerationRequest $request): array
    {
        $inputs = [];

        foreach ([$request->brandLogoPath, $request->referenceImagePath] as $path) {
            if (! $path) {
                continue;
            }

            $blob = $this->readPublicImage($path);
            if ($blob !== null) {
                $inputs[] = $blob;
            }
        }

        return $inputs;
    }

    /**
     * Read an image off the public disk and return it as a base64 blob with
     * its detected mime type. Returns null when the file is missing/unreadable
     * so a stale path never breaks the whole generation.
     *
     * @return array{data: string, mime: string, raw: string}|null
     */
    protected function readPublicImage(string $path): ?array
    {
        try {
            if (! Storage::disk('public')->exists($path)) {
                return null;
            }

            $bytes = Storage::disk('public')->get($path);
            if ($bytes === null || $bytes === '') {
                return null;
            }

            return [
                'data' => base64_encode($bytes),
                'mime' => $this->guessMime($path),
                'raw' => $bytes,
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    protected function guessMime(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }
}
