<?php

namespace App\Services\AiStudio\Drivers;

use App\Services\AiStudio\Concerns\ConfigurableTimeout;
use App\Services\AiStudio\Concerns\ResolvesImageInputs;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Driver for OpenAI's GPT Image 2 model (gpt-image-2).
 *
 * Successor to DALL-E 3 / GPT Image 1.5, released April 2026.
 * Notable differences from the legacy DALL-E driver:
 *   - Model id is `gpt-image-2` (not `dall-e-3`).
 *   - Only a fixed set of sizes is accepted by the API; we round to the
 *     closest supported aspect ratio and let downstream code crop if needed.
 *   - Supports a `quality` parameter (low / medium / high / auto).
 *   - Always returns base64 PNG data; no `response_format` flag is required.
 *
 * Reference: https://platform.openai.com/docs/guides/image-generation
 */
class GptImageDriver implements AiProviderInterface
{
    use ConfigurableTimeout;
    use ResolvesImageInputs;

    /**
     * GPT Image 2's accepted size matrix.
     * Mapped to the aspect ratio they best approximate.
     */
    protected const SUPPORTED_SIZES = [
        'square' => '1024x1024',
        'portrait' => '1024x1536',
        'landscape' => '1536x1024',
    ];

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        try {
            $size = $this->resolveSize($request->width, $request->height);

            // Pull the admin-configured quality tier for this engine from
            // media_models. Falls back to 'auto' (let OpenAI pick) when unset
            // or set to an unexpected value.
            $quality = $this->resolveQuality($request->provider);

            // When a brand logo and/or reference image is present, route to
            // the image-edit endpoint so GPT Image 2 composites the REAL
            // assets into the ad instead of inventing them. Otherwise use the
            // plain text-to-image generations endpoint.
            $imageInputs = $this->imageInputs($request);

            $response = $imageInputs !== []
                ? $this->requestWithImages($request, $size, $quality, $imageInputs)
                : $this->requestTextOnly($request, $size, $quality);

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
                    error: "GPT Image API error: {$response->status()} - {$response->body()}",
                );
            }

            $data = $response->json();
            $imageData = $data['data'][0]['b64_json'] ?? null;

            if (! $imageData) {
                return new GenerationResult(
                    success: false,
                    error: 'GPT Image API returned no image data.',
                );
            }

            $imageBytes = base64_decode($imageData);
            $assetId = Str::uuid()->toString();
            $path = "images/{$request->provider}/{$assetId}.png";

            Storage::disk('results')->put($path, $imageBytes);

            return new GenerationResult(
                success: true,
                filePath: $path,
                mimeType: 'image/png',
                fileSize: strlen($imageBytes),
            );
        } catch (\Throwable $e) {
            if ($deferred = $this->deferIfTimedOut($e)) {
                return $deferred;
            }

            return new GenerationResult(
                success: false,
                error: "GPT Image generation failed: {$e->getMessage()}",
            );
        }
    }

    /**
     * Plain text-to-image call (no reference inputs).
     */
    protected function requestTextOnly(GenerationRequest $request, string $size, string $quality)
    {
        // GPT Image 2 at high quality can take 90–180s on larger sizes.
        // Allow up to 5 minutes before timing out so legitimate slow
        // renders complete instead of erroring as a curl timeout.
        return Http::withToken($this->apiKey)
            ->timeout($this->httpTimeout(300))
            ->connectTimeout(20)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'gpt-image-2',
                'prompt' => $request->prompt,
                'n' => 1,
                'size' => $size,
                'quality' => $quality,
            ]);
    }

    /**
     * Image-edit call: attaches the brand logo + reference image(s) as
     * multipart `image[]` inputs so the model composites the real assets.
     */
    protected function requestWithImages(GenerationRequest $request, string $size, string $quality, array $imageInputs)
    {
        $http = Http::withToken($this->apiKey)
            ->timeout($this->httpTimeout(300))
            ->connectTimeout(20)
            ->asMultipart();

        foreach ($imageInputs as $i => $input) {
            $ext = match ($input['mime']) {
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default => 'png',
            };
            $http->attach("image[]", $input['raw'], "input-{$i}.{$ext}");
        }

        return $http->post('https://api.openai.com/v1/images/edits', [
            'model' => 'gpt-image-2',
            'prompt' => $request->prompt,
            'n' => '1',
            'size' => $size,
            'quality' => $quality,
        ]);
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('GPT Image does not support video generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    /**
     * Resolve the admin-configured image quality for this engine from
     * media_models. Returns one of low | medium | high | auto. Defaults to
     * 'auto' when the column is empty or holds an unexpected value.
     */
    protected function resolveQuality(string $provider): string
    {
        $allowed = ['auto', 'low', 'medium', 'high'];

        $quality = \App\Models\MediaModel::query()
            ->where('vendor', $provider)
            ->value('image_quality');

        return in_array($quality, $allowed, true) ? $quality : 'auto';
    }

    /**
     * Pick the supported size that best matches the requested aspect ratio.
     */
    protected function resolveSize(int $width, int $height): string
    {
        if ($height <= 0) {
            return self::SUPPORTED_SIZES['square'];
        }

        $ratio = $width / $height;

        return match (true) {
            $ratio > 1.15 => self::SUPPORTED_SIZES['landscape'],
            $ratio < 0.87 => self::SUPPORTED_SIZES['portrait'],
            default => self::SUPPORTED_SIZES['square'],
        };
    }
}
