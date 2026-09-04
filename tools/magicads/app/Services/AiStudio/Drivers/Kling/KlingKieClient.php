<?php

namespace App\Services\AiStudio\Drivers\Kling;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Kling 3.0 via kie.ai — the unified Market "jobs" API.
 *
 *   POST https://api.kie.ai/api/v1/jobs/createTask                → data.taskId
 *   GET  https://api.kie.ai/api/v1/jobs/recordInfo?taskId={id}    → data.state / data.resultJson
 *
 * Auth: `Authorization: Bearer {key}`. The quality tier maps to kie.ai's
 * `mode`: 720p → std, 1080p → pro, 4k → 4K. Aspect ratio is limited to
 * 16:9 / 9:16 / 1:1. Image-to-video takes a public `image_urls[0]` (first
 * frame), so we hand it the reference image's public URL.
 */
class KlingKieClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    protected const FALLBACK_MODEL = 'kling-3.0/video';

    protected const BASE_URL = 'https://api.kie.ai/api/v1';

    protected const SUPPORTED_RESOLUTIONS = ['720p', '1080p', '4k'];

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $tier = $this->resolveResolutionTier($request->resolution, $request->width, $request->height, self::SUPPORTED_RESOLUTIONS);

            $input = [
                'prompt' => $request->prompt,
                'sound' => true,
                'duration' => (string) $this->resolveDuration($request->duration),
                'aspect_ratio' => $this->resolveAspectRatioBasic($request->width, $request->height),
                'mode' => $this->modeForTier($tier),
                'multi_shots' => false,
            ];

            if (! empty($request->referenceImagePath)) {
                // kie.ai expects a reachable URL for the first frame.
                $input['image_urls'] = [$this->referenceImagePublicUrl($request->referenceImagePath)];
            }

            $payload = [
                'model' => $this->modelId ?: self::FALLBACK_MODEL,
                'input' => $input,
            ];

            $start = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(self::BASE_URL.'/jobs/createTask', $payload);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Kling (kie.ai) API error: {$start->status()} - {$start->body()}");
            }

            $code = (int) $start->json('code');
            if ($code !== 200) {
                return GenerationResult::failure("Kling (kie.ai) API error: {$code} - ".(string) $start->json('msg'));
            }

            $taskId = $start->json('data.taskId');

            if (! $taskId) {
                return GenerationResult::failure('Kling (kie.ai) returned no task id to poll.');
            }

            return GenerationResult::submitted((string) $taskId);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Kling submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $poll = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])->timeout(30)->get(self::BASE_URL.'/jobs/recordInfo', ['taskId' => $operationId]);

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Kling (kie.ai) poll error: {$poll->status()} - {$poll->body()}");
            }

            $state = (string) $poll->json('data.state');

            if (in_array($state, ['waiting', 'queuing', 'generating'], true)) {
                return GenerationResult::stillPending();
            }

            if ($state !== 'success') {
                $message = (string) ($poll->json('data.failMsg') ?: $poll->json('msg') ?: $state);

                return GenerationResult::failure("Kling generation {$state}: {$message}");
            }

            $resultJson = $poll->json('data.resultJson');
            $decoded = is_string($resultJson) ? json_decode($resultJson, true) : (is_array($resultJson) ? $resultJson : null);
            $videoUrl = $decoded['resultUrls'][0] ?? null;

            if (! $videoUrl) {
                return GenerationResult::failure('Kling (kie.ai) response missing video URL.');
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

    /** Map a tier to kie.ai's generation mode. */
    protected function modeForTier(string $tier): string
    {
        return match ($tier) {
            '720p'  => 'std',
            '4k'    => '4K',
            default => 'pro',
        };
    }

    /** kie.ai Kling accepts whole-second durations from 3 to 15. */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        return max(3, min($requested, 15));
    }
}
