<?php

namespace App\Services\AiStudio\Drivers\Seedance;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Seedance via fal.ai — the queue-based hosted route.
 *
 * fal.ai queue API (https://docs.fal.ai/model-apis/model-endpoints/queue):
 *   POST  https://queue.fal.run/{model_id}/{endpoint}            → request_id + status/response URLs
 *   GET   https://queue.fal.run/{model_id}/requests/{id}/status  → poll until COMPLETED
 *   GET   https://queue.fal.run/{model_id}/requests/{id}         → final result payload
 *
 * Auth: `Authorization: Key {key}`. Success exposes the video at `video.url`.
 * fal.ai serves 480p / 720p / 1080p (no 4k).
 */
class SeedanceFalClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    protected const FALLBACK_MODEL = 'bytedance/seedance-2.0';

    protected const QUEUE_BASE = 'https://queue.fal.run';

    /** Tiers fal.ai can produce for Seedance. */
    protected const SUPPORTED_RESOLUTIONS = ['480p', '720p', '1080p'];

    /** Seedance accepts only these discrete clip lengths via fal.ai. */
    protected const ALLOWED_DURATIONS = [4, 5, 6, 8, 10, 12, 15];

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $isImageToVideo = ! empty($request->referenceImagePath);
            $modelId = $this->modelId ?: self::FALLBACK_MODEL;
            $endpoint = $modelId.'/'.($isImageToVideo ? 'image-to-video' : 'text-to-video');

            $input = [
                'prompt' => $request->prompt,
                'resolution' => $this->resolveResolutionTier(
                    $request->resolution,
                    $request->width,
                    $request->height,
                    self::SUPPORTED_RESOLUTIONS,
                ),
                'duration' => (string) $this->resolveDuration($request->duration),
                'aspect_ratio' => $this->resolveAspectRatio($request->width, $request->height),
                'generate_audio' => true,
            ];

            if ($isImageToVideo) {
                $dataUri = $this->referenceImageDataUri($request->referenceImagePath);
                if ($dataUri === null) {
                    return GenerationResult::failure('Seedance could not read the reference image for image-to-video.');
                }
                $input['image_url'] = $dataUri;
            }

            $start = Http::withHeaders([
                'Authorization' => "Key {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(self::QUEUE_BASE."/{$endpoint}", $input);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Seedance (fal.ai) API error: {$start->status()} - {$start->body()}");
            }

            $statusUrl = $start->json('status_url');
            $requestId = $start->json('request_id');

            if (! $statusUrl && $requestId) {
                $baseApp = $this->baseAppId($modelId);
                $statusUrl = self::QUEUE_BASE."/{$baseApp}/requests/{$requestId}/status";
            }

            if (! $statusUrl) {
                return GenerationResult::failure('Seedance (fal.ai) returned no request id to poll.');
            }

            return GenerationResult::submitted($statusUrl);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Seedance submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $statusUrl = $operationId;

            $poll = Http::withHeaders(['Authorization' => "Key {$this->apiKey}"])
                ->timeout(30)->get($statusUrl);

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Seedance (fal.ai) poll error: {$poll->status()} - {$poll->body()}");
            }

            if ($poll->json('status') !== 'COMPLETED') {
                return GenerationResult::stillPending();
            }

            $responseUrl = $poll->json('response_url') ?: $this->responseUrlFromStatus($statusUrl);

            $result = Http::withHeaders(['Authorization' => "Key {$this->apiKey}"])
                ->timeout(30)->get($responseUrl);

            if ($result->failed()) {
                return GenerationResult::failure("Seedance (fal.ai) result error: {$result->status()} - {$result->body()}");
            }

            if ($error = $result->json('error')) {
                $message = is_array($error) ? ($error['message'] ?? json_encode($error)) : (string) $error;

                return GenerationResult::failure("Seedance generation failed: {$message}");
            }

            $videoUrl = $result->json('video.url');

            if (! $videoUrl) {
                return GenerationResult::failure('Seedance (fal.ai) response missing video URL.');
            }

            return $this->downloadAndStore(
                $request,
                fn () => Http::timeout(120)->get($videoUrl),
                'Seedance',
            );
        } catch (\Throwable $e) {
            return GenerationResult::failure("Seedance poll failed: {$e->getMessage()}");
        }
    }

    protected function baseAppId(string $modelId): string
    {
        $parts = explode('/', trim($modelId, '/'));

        return implode('/', array_slice($parts, 0, 2));
    }

    protected function responseUrlFromStatus(string $statusUrl): string
    {
        return preg_replace('#/status(\?.*)?$#', '', $statusUrl);
    }

    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        $best = self::ALLOWED_DURATIONS[0];
        $bestDiff = PHP_INT_MAX;

        foreach (self::ALLOWED_DURATIONS as $allowed) {
            $diff = abs($allowed - $requested);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $allowed;
            }
        }

        return $best;
    }
}
