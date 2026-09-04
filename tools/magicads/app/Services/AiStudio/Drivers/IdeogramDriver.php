<?php

namespace App\Services\AiStudio\Drivers;

use App\Services\AiStudio\Concerns\ConfigurableTimeout;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Driver for Ideogram 3.0 — the typography specialist.
 *
 * Ideogram is the only major image model with a dedicated typography
 * module, scoring ~90% text accuracy on legibility benchmarks vs. ~30%
 * on Midjourney. This is the engine to pick when the user has filled
 * out the Headline / CTA fields and wants the AI to render that exact
 * copy in the image (rather than burning it in via post-processing).
 *
 * The Ideogram API is sync — the response body returns image URLs
 * directly, no polling required.
 *
 * Endpoint:
 *   POST  https://api.ideogram.ai/v1/ideogram-v3/generate
 *
 * Auth header: `Api-Key`.
 *
 * Reference: https://developer.ideogram.ai/api-reference
 */
class IdeogramDriver implements AiProviderInterface
{
    use ConfigurableTimeout;

    /**
     * Aspect-ratio whitelist exposed by the v3 endpoint. We map every
     * preset width/height to the closest of these.
     */
    protected const ASPECT_RATIOS = [
        '1x1' => 1.0,
        '4x3' => 4 / 3,
        '3x4' => 3 / 4,
        '16x9' => 16 / 9,
        '9x16' => 9 / 16,
        '3x2' => 3 / 2,
        '2x3' => 2 / 3,
        '4x5' => 4 / 5,
        '5x4' => 5 / 4,
        '16x10' => 16 / 10,
        '10x16' => 10 / 16,
    ];

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        try {
            $aspectRatio = $this->resolveAspectRatio($request->width, $request->height);

            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->httpTimeout(120))
                ->post('https://api.ideogram.ai/v1/ideogram-v3/generate', [
                    'prompt' => $request->prompt,
                    'aspect_ratio' => $aspectRatio,
                    'rendering_speed' => 'DEFAULT',
                    'num_images' => 1,
                    'magic_prompt' => 'AUTO',
                    'style_type' => 'AUTO',
                ]);

            if ($response->status() === 429) {
                return new GenerationResult(
                    success: false,
                    rateLimited: true,
                    retryAfter: (int) ($response->header('Retry-After') ?? 60),
                );
            }

            if ($response->failed()) {
                return new GenerationResult(
                    success: false,
                    error: "Ideogram API error: {$response->status()} - {$response->body()}",
                );
            }

            $imageUrl = $response->json('data.0.url');

            if (! $imageUrl) {
                return new GenerationResult(
                    success: false,
                    error: 'Ideogram API returned no image URL.',
                );
            }

            $imageContent = Http::timeout($this->httpTimeout(60))->get($imageUrl)->body();
            $assetId = Str::uuid()->toString();
            $path = "images/{$request->provider}/{$assetId}.png";

            Storage::disk('results')->put($path, $imageContent);

            return new GenerationResult(
                success: true,
                filePath: $path,
                mimeType: 'image/png',
                fileSize: strlen($imageContent),
            );
        } catch (\Throwable $e) {
            if ($deferred = $this->deferIfTimedOut($e)) {
                return $deferred;
            }

            return new GenerationResult(
                success: false,
                error: "Ideogram generation failed: {$e->getMessage()}",
            );
        }
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Ideogram does not support video generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    protected function resolveAspectRatio(int $width, int $height): string
    {
        if ($height <= 0) {
            return '1x1';
        }

        $target = $width / $height;
        $best = '1x1';
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
}
