<?php

namespace App\Services\AiStudio\Drivers\Seedance;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Seedance via ByteDance's own API (Volcengine Ark / BytePlus ModelArk) —
 * the original first-party vendor.
 *
 * Async task API:
 *   POST {base}/contents/generations/tasks        → { "id": "cgt-..." }
 *   GET  {base}/contents/generations/tasks/{id}    → { status, content:{ video_url } }
 *
 * Auth: `Authorization: Bearer {key}`. State machine:
 *   queued → running → succeeded | failed | expired
 * The signed `content.video_url` must be downloaded WITHOUT the auth header
 * and expires in ~24h. ByteDance's direct route serves 480p / 720p / 1080p.
 *
 * The base URL defaults to Volcengine Ark and can be overridden via
 * config('ai-studio.seedance.bytedance_base_url') for BytePlus / regional hosts.
 */
class SeedanceBytedanceClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    protected const FALLBACK_MODEL = 'doubao-seedance-2-0-260128';

    protected const DEFAULT_BASE_URL = 'https://ark.cn-beijing.volces.com/api/v3';

    protected const SUPPORTED_RESOLUTIONS = ['480p', '720p', '1080p'];

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    protected function baseUrl(): string
    {
        return rtrim((string) config('ai-studio.seedance.bytedance_base_url', self::DEFAULT_BASE_URL), '/');
    }

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $isImageToVideo = ! empty($request->referenceImagePath);

            $content = [
                ['type' => 'text', 'text' => $request->prompt],
            ];

            if ($isImageToVideo) {
                $dataUri = $this->referenceImageDataUri($request->referenceImagePath);
                if ($dataUri === null) {
                    return GenerationResult::failure('Seedance could not read the reference image for image-to-video.');
                }
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $dataUri],
                    'role' => 'first_frame',
                ];
            }

            $payload = [
                'model' => $this->modelId ?: self::FALLBACK_MODEL,
                'content' => $content,
                'resolution' => $this->resolveResolutionTier(
                    $request->resolution,
                    $request->width,
                    $request->height,
                    self::SUPPORTED_RESOLUTIONS,
                ),
                'ratio' => $this->resolveAspectRatio($request->width, $request->height),
                'duration' => $this->resolveDuration($request->duration),
                'generate_audio' => true,
            ];

            $start = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl().'/contents/generations/tasks', $payload);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Seedance (ByteDance) API error: {$start->status()} - {$start->body()}");
            }

            $taskId = $start->json('id');

            if (! $taskId) {
                return GenerationResult::failure('Seedance (ByteDance) returned no task id to poll.');
            }

            return GenerationResult::submitted((string) $taskId);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Seedance submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $poll = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept-Encoding' => 'identity',
            ])->timeout(30)->get($this->baseUrl().'/contents/generations/tasks/'.$operationId);

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Seedance (ByteDance) poll error: {$poll->status()} - {$poll->body()}");
            }

            $status = (string) $poll->json('status');

            if (in_array($status, ['queued', 'running'], true)) {
                return GenerationResult::stillPending();
            }

            if ($status !== 'succeeded') {
                // failed | expired | anything unexpected.
                $error = $poll->json('error');
                $message = is_array($error) ? ($error['message'] ?? json_encode($error)) : ($error ?: $status);

                return GenerationResult::failure("Seedance generation {$status}: {$message}");
            }

            $videoUrl = $poll->json('content.video_url');

            if (! $videoUrl) {
                return GenerationResult::failure('Seedance (ByteDance) response missing video URL.');
            }

            // Signed URL — download WITHOUT the Authorization header.
            return $this->downloadAndStore(
                $request,
                fn () => Http::timeout(120)->get($videoUrl),
                'Seedance',
            );
        } catch (\Throwable $e) {
            return GenerationResult::failure("Seedance poll failed: {$e->getMessage()}");
        }
    }

    /** ByteDance accepts whole-second durations from 4 to 15. */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        return max(4, min($requested, 15));
    }
}
