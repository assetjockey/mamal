<?php

namespace App\Services\AiStudio\Drivers\Kling;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Kling v3 via fal.ai — the queue-based hosted route.
 *
 * On fal.ai the resolution tier is chosen by the MODEL SLUG rather than a
 * request field:
 *   720p  → fal-ai/kling-video/v3/standard/{endpoint}
 *   1080p → fal-ai/kling-video/v3/pro/{endpoint}
 *   4k    → fal-ai/kling-video/v3/{endpoint}          (the 4k variant)
 *
 * fal.ai queue API:
 *   POST  https://queue.fal.run/{slug}/{text-to-video|image-to-video}
 *   GET   {status_url} → poll until COMPLETED, then fetch {response_url}
 *
 * Auth: `Authorization: Key {key}`. Success exposes the video at `video.url`.
 */
class KlingFalClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    /** Base slug; the quality segment + endpoint are appended per request. */
    protected const FALLBACK_MODEL = 'fal-ai/kling-video/v3';

    protected const QUEUE_BASE = 'https://queue.fal.run';

    /** Tiers fal.ai can produce for Kling v3. */
    protected const SUPPORTED_RESOLUTIONS = ['720p', '1080p', '4k'];

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $isImageToVideo = ! empty($request->referenceImagePath);
            $tier = $this->resolveResolutionTier($request->resolution, $request->width, $request->height, self::SUPPORTED_RESOLUTIONS);
            $slug = $this->slugForTier($tier);
            $endpoint = $slug.'/'.($isImageToVideo ? 'image-to-video' : 'text-to-video');

            $input = [
                'prompt' => $request->prompt,
                'duration' => (string) $this->resolveDuration($request->duration),
                'aspect_ratio' => $this->resolveAspectRatioBasic($request->width, $request->height),
                'generate_audio' => true,
            ];

            if ($isImageToVideo) {
                $dataUri = $this->referenceImageDataUri($request->referenceImagePath);
                if ($dataUri === null) {
                    return GenerationResult::failure('Kling could not read the reference image for image-to-video.');
                }
                // v3 image-to-video uses start_image_url (first frame).
                $input['start_image_url'] = $dataUri;
            }

            $start = Http::withHeaders([
                'Authorization' => "Key {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(self::QUEUE_BASE."/{$endpoint}", $input);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Kling (fal.ai) API error: {$start->status()} - {$start->body()}");
            }

            $statusUrl = $start->json('status_url');
            $requestId = $start->json('request_id');

            if (! $statusUrl && $requestId) {
                $statusUrl = self::QUEUE_BASE."/{$slug}/requests/{$requestId}/status";
            }

            if (! $statusUrl) {
                return GenerationResult::failure('Kling (fal.ai) returned no request id to poll.');
            }

            return GenerationResult::submitted($statusUrl);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Kling submit failed: {$e->getMessage()}");
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
                return GenerationResult::failure("Kling (fal.ai) poll error: {$poll->status()} - {$poll->body()}");
            }

            if ($poll->json('status') !== 'COMPLETED') {
                return GenerationResult::stillPending();
            }

            $responseUrl = $poll->json('response_url') ?: preg_replace('#/status(\?.*)?$#', '', $statusUrl);

            $result = Http::withHeaders(['Authorization' => "Key {$this->apiKey}"])
                ->timeout(30)->get($responseUrl);

            if ($result->failed()) {
                return GenerationResult::failure("Kling (fal.ai) result error: {$result->status()} - {$result->body()}");
            }

            if ($error = $result->json('error')) {
                $message = is_array($error) ? ($error['message'] ?? json_encode($error)) : (string) $error;

                return GenerationResult::failure("Kling generation failed: {$message}");
            }

            $videoUrl = $result->json('video.url');

            if (! $videoUrl) {
                return GenerationResult::failure('Kling (fal.ai) response missing video URL.');
            }

            return $this->downloadAndStore(
                $request,
                fn () => Http::timeout(120)->get($videoUrl),
                'Kling',
            );
        } catch (\Throwable $e) {
            return GenerationResult::failure("Kling poll failed: {$e->getMessage()}");
        }
    }

    /**
     * Build the fal.ai model slug for a tier. 720p → /standard, 1080p → /pro,
     * 4k → the base slug (no quality segment).
     */
    protected function slugForTier(string $tier): string
    {
        $base = rtrim($this->modelId ?: self::FALLBACK_MODEL, '/');

        return match ($tier) {
            '720p'  => $base.'/standard',
            '4k'    => $base,
            default => $base.'/pro',
        };
    }

    /** Kling v3 accepts whole-second durations from 3 to 15. */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        return max(3, min($requested, 15));
    }
}
