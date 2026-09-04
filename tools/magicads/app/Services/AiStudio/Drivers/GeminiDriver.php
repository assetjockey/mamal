<?php

namespace App\Services\AiStudio\Drivers;

use App\Models\MediaModel;
use App\Services\AiStudio\Concerns\ConfigurableTimeout;
use App\Services\AiStudio\Concerns\ResolvesImageInputs;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Driver for Google's Nano Banana 2 model
 * (Gemini 3.1 Flash Image — `gemini-3.1-flash-image-preview`).
 *
 * Released February 2026 as the successor to Nano Banana
 * (`gemini-2.5-flash-image`). Notable upgrades over the prior Gemini
 * 2.0 Flash Experimental endpoint we used to call:
 *   - Genuine production image generation with no "exp" caveats.
 *   - SynthID-watermarked outputs by default.
 *   - Aspect ratio + image size are first-class fields on `imageConfig`
 *     (the legacy `imageDimensions` field was never accepted by the
 *     image-generation models — the old call worked silently with
 *     a 1:1 fallback).
 *   - Authenticated via the `x-goog-api-key` header rather than `?key=`.
 *   - The concrete model id is read from the `media_models` row (single
 *     source of truth) like VeoDriver, not hardcoded. The GA id is
 *     `gemini-3.1-flash-image`; the earlier `-preview` alias was retired
 *     and now 404s.
 *
 * Reference: https://ai.google.dev/gemini-api/docs/image-generation
 */
class GeminiDriver implements AiProviderInterface
{
    use ConfigurableTimeout;
    use ResolvesImageInputs;

    protected const BASE = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Fallback model id if the media_models row has no model_id. Mirrors the
     * VeoDriver pattern: the concrete model id is read from the database
     * (single source of truth) so it never drifts from the seeder / admin UI.
     *
     * Note the GA id is `gemini-3.1-flash-image` (no `-preview` suffix) —
     * the old `-preview` alias was retired and now 404s, which is what made
     * "Generate" silently fail and bounce back to the wizard.
     */
    protected const FALLBACK_MODEL = 'gemini-3.1-flash-image';

    /**
     * Officially supported aspect ratios on Nano Banana 2.
     * Each entry is a normalised float ratio for matching.
     */
    protected const ASPECT_RATIOS = [
        '1:1' => 1.0,
        '4:3' => 4 / 3,
        '3:4' => 3 / 4,
        '16:9' => 16 / 9,
        '9:16' => 9 / 16,
        '3:2' => 3 / 2,
        '2:3' => 2 / 3,
        '21:9' => 21 / 9,
        '5:4' => 5 / 4,
        '4:5' => 4 / 5,
    ];

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        try {
            $modelId = $this->resolveModelId($request->provider);
            $aspectRatio = $this->resolveAspectRatio($request->width, $request->height);
            $imageSize = $this->resolveImageSize($request->width, $request->height);

            // Build the multimodal parts. The text brief leads; any brand
            // logo and/or user reference image is attached as inline image
            // parts so Nano Banana 2 composites the REAL assets into the ad
            // instead of inventing a generic logo.
            $parts = [['text' => $request->prompt]];

            foreach ($this->imageInputs($request) as $input) {
                $parts[] = ['inlineData' => [
                    'mimeType' => $input['mime'],
                    'data' => $input['data'],
                ]];
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])
                ->timeout($this->httpTimeout(120))
                ->post(self::BASE."/models/{$modelId}:generateContent", [
                    'contents' => [[
                        'parts' => $parts,
                    ]],
                    'generationConfig' => [
                        'responseModalities' => ['IMAGE'],
                        'imageConfig' => [
                            'aspectRatio' => $aspectRatio,
                            'imageSize' => $imageSize,
                        ],
                    ],
                ]);

            if ($response->status() === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?? 60);

                return new GenerationResult(
                    success: false,
                    rateLimited: true,
                    retryAfter: $retryAfter,
                );
            }

            if ($response->failed()) {
                return new GenerationResult(
                    success: false,
                    error: "Gemini API error: {$response->status()} - {$response->body()}",
                );
            }

            $data = $response->json();

            // Gemini 3 image models are "thinking" models: the response can
            // include interim "thought" image parts (flagged "thought": true)
            // before the final render. We must skip those and take the last
            // non-thought inline image — grabbing the first inlineData blindly
            // can return a low-fidelity thought image instead of the result.
            $imageData = null;
            $mimeType = 'image/png';
            foreach ($data['candidates'][0]['content']['parts'] ?? [] as $part) {
                if (($part['thought'] ?? false) === true) {
                    continue;
                }
                if (isset($part['inlineData']['data'])) {
                    $imageData = $part['inlineData']['data'];
                    $mimeType = $part['inlineData']['mimeType'] ?? $mimeType;
                }
            }

            if (! $imageData) {
                return new GenerationResult(
                    success: false,
                    error: 'Gemini API returned no image data.',
                );
            }

            $imageBytes = base64_decode($imageData);
            $ext = $this->extensionFromMime($mimeType);
            $assetId = Str::uuid()->toString();
            $path = "images/{$request->provider}/{$assetId}.{$ext}";

            Storage::disk('results')->put($path, $imageBytes);

            return new GenerationResult(
                success: true,
                filePath: $path,
                mimeType: $mimeType,
                fileSize: strlen($imageBytes),
            );
        } catch (\Throwable $e) {
            if ($deferred = $this->deferIfTimedOut($e)) {
                return $deferred;
            }

            return new GenerationResult(
                success: false,
                error: "Gemini generation failed: {$e->getMessage()}",
            );
        }
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Gemini does not support video generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    /**
     * Resolve the concrete model id from the media_models row (single source
     * of truth), falling back to a known-good GA default. Mirrors the
     * VeoDriver fix: the hardcoded slug no longer drifts from the seeded
     * model_id, so a retired `-preview` alias can't silently 404 the call.
     */
    protected function resolveModelId(string $providerKey): string
    {
        return MediaModel::findByVendor($providerKey)?->model_id ?: self::FALLBACK_MODEL;
    }

    /**
     * Pick the supported aspect ratio whose float value is closest to
     * the requested width/height ratio.
     */
    protected function resolveAspectRatio(int $width, int $height): string
    {
        if ($height <= 0) {
            return '1:1';
        }

        $target = $width / $height;
        $best = '1:1';
        $bestDiff = PHP_FLOAT_MAX;

        foreach (self::ASPECT_RATIOS as $label => $ratio) {
            $diff = abs($ratio - $target);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $label;
            }
        }

        return $best;
    }

    /**
     * Map the longest requested side to Nano Banana 2's image size buckets.
     *  - 1K  → up to ~1024px on the long side (default, fastest, cheapest)
     *  - 2K  → up to ~2048px on the long side (high quality)
     *  - 4K  → up to ~4096px on the long side (Pro tier — billed higher)
     */
    protected function resolveImageSize(int $width, int $height): string
    {
        $longSide = max($width, $height);

        return match (true) {
            $longSide >= 3000 => '4K',
            $longSide >= 1500 => '2K',
            default => '1K',
        };
    }

    protected function extensionFromMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/webp' => 'webp',
            default => 'png',
        };
    }
}
