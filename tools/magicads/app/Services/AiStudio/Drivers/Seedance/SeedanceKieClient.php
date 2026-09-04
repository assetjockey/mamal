<?php

namespace App\Services\AiStudio\Drivers\Seedance;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Seedance via kie.ai — the unified Market "jobs" API.
 *
 *   POST https://api.kie.ai/api/v1/jobs/createTask                → data.taskId
 *   GET  https://api.kie.ai/api/v1/jobs/recordInfo?taskId={id}    → data.state / data.resultJson
 *
 * Auth: `Authorization: Bearer {key}`. States:
 *   waiting → queuing → generating → success | fail
 * On success `resultJson` is a JSON string carrying `{ "resultUrls": [ ... ] }`.
 * kie.ai serves 480p / 720p / 1080p AND 4k. Image-to-video takes a public
 * `first_frame_url` (not base64), so we hand it the reference image's public URL.
 */
class SeedanceKieClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    protected const FALLBACK_MODEL = 'bytedance/seedance-2';

    protected const BASE_URL = 'https://api.kie.ai/api/v1';

    protected const SUPPORTED_RESOLUTIONS = ['480p', '720p', '1080p', '4k'];

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $input = [
                'prompt' => $request->prompt,
                'resolution' => $this->resolveResolutionTier(
                    $request->resolution,
                    $request->width,
                    $request->height,
                    self::SUPPORTED_RESOLUTIONS,
                ),
                'aspect_ratio' => $this->resolveAspectRatio($request->width, $request->height),
                'duration' => $this->resolveDuration($request->duration),
                'generate_audio' => true,
            ];

            if (! empty($request->referenceImagePath)) {
                // kie.ai expects a reachable URL for the first frame.
                $input['first_frame_url'] = $this->referenceImagePublicUrl($request->referenceImagePath);
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
                return GenerationResult::failure("Seedance (kie.ai) API error: {$start->status()} - {$start->body()}");
            }

            $code = (int) $start->json('code');
            if ($code !== 200) {
                return GenerationResult::failure("Seedance (kie.ai) API error: {$code} - ".(string) $start->json('msg'));
            }

            $taskId = $start->json('data.taskId');

            if (! $taskId) {
                return GenerationResult::failure('Seedance (kie.ai) returned no task id to poll.');
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
            ])->timeout(30)->get(self::BASE_URL.'/jobs/recordInfo', ['taskId' => $operationId]);

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Seedance (kie.ai) poll error: {$poll->status()} - {$poll->body()}");
            }

            $state = (string) $poll->json('data.state');

            if (in_array($state, ['waiting', 'queuing', 'generating'], true)) {
                return GenerationResult::stillPending();
            }

            if ($state !== 'success') {
                $message = (string) ($poll->json('data.failMsg') ?: $poll->json('msg') ?: $state);

                return GenerationResult::failure("Seedance generation {$state}: {$message}");
            }

            // resultJson is a JSON *string* → { "resultUrls": [ ... ] }.
            $resultJson = $poll->json('data.resultJson');
            $decoded = is_string($resultJson) ? json_decode($resultJson, true) : (is_array($resultJson) ? $resultJson : null);
            $videoUrl = $decoded['resultUrls'][0] ?? null;

            if (! $videoUrl) {
                return GenerationResult::failure('Seedance (kie.ai) response missing video URL.');
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

    /** kie.ai accepts whole-second durations from 4 to 15. */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        return max(4, min($requested, 15));
    }
}
