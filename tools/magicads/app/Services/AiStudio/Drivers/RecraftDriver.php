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
 * Driver for Recraft V3 — vector / brand-asset specialist.
 *
 * The only major model trained on logo, icon, and poster data with native
 * vector (SVG) output and brand-style consistency across a session. Best
 * pick for flat design, icon work, logo concepts, and layout-heavy ads.
 *
 * Recraft's API is sync — returns the asset URL inline in the response.
 *
 * Endpoint:
 *   POST  https://external.api.recraft.ai/v1/images/generations
 *
 * Auth: standard `Authorization: Bearer <key>` header.
 *
 * Reference: https://www.recraft.ai/docs
 */
class RecraftDriver implements AiProviderInterface
{
    use ConfigurableTimeout;

    /**
     * Recraft V3 accepts a fixed list of `size` strings rather than free
     * width/height. We map every preset to the closest of these.
     */
    protected const SIZES = [
        '1024x1024' => 1.0,
        '1365x1024' => 1365 / 1024,
        '1024x1365' => 1024 / 1365,
        '1536x1024' => 1536 / 1024,
        '1024x1536' => 1024 / 1536,
        '1820x1024' => 1820 / 1024,
        '1024x1820' => 1024 / 1820,
        '2048x1024' => 2.0,
        '1024x2048' => 0.5,
        '1707x1024' => 1707 / 1024,
        '1024x1707' => 1024 / 1707,
    ];

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        try {
            $size = $this->resolveSize($request->width, $request->height);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->httpTimeout(120))
                ->post('https://external.api.recraft.ai/v1/images/generations', [
                    'prompt' => $request->prompt,
                    'model' => 'recraftv3',
                    'style' => 'realistic_image',
                    'size' => $size,
                    'response_format' => 'url',
                    'n' => 1,
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
                    error: "Recraft API error: {$response->status()} - {$response->body()}",
                );
            }

            $imageUrl = $response->json('data.0.url');

            if (! $imageUrl) {
                return new GenerationResult(
                    success: false,
                    error: 'Recraft API returned no image URL.',
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
                error: "Recraft generation failed: {$e->getMessage()}",
            );
        }
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Recraft does not support video generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    protected function resolveSize(int $width, int $height): string
    {
        if ($height <= 0) {
            return '1024x1024';
        }

        $target = $width / $height;
        $best = '1024x1024';
        $bestDiff = PHP_FLOAT_MAX;

        foreach (self::SIZES as $label => $ratio) {
            $diff = abs($ratio - $target);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $label;
            }
        }

        return $best;
    }
}
