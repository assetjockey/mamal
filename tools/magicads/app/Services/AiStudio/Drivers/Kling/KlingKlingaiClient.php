<?php

namespace App\Services\AiStudio\Drivers\Kling;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Concerns\ResolvesVideoInputs;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Kling v3 via Kling AI's own API (Kuaishou, https://kling.ai) — the original
 * first-party vendor.
 *
 * Async task API:
 *   POST {base}/v1/videos/text2video   → data.task_id
 *   POST {base}/v1/videos/image2video  → data.task_id
 *   GET  {base}/v1/videos/{type}/{id}  → data.task_status / data.task_result.videos[].url
 *
 * Auth is a short-lived JWT (HS256) generated from an Access Key + Secret Key.
 * The admin stores both in admin_keys.kling_key as "accessKey:secretKey"; if
 * only a single token is stored (no colon) it is sent verbatim as the bearer.
 *
 * task_status: submitted → processing → succeed | failed. The finished video
 * URL is a signed CDN link (download without the auth header). Kling's classic
 * API exposes std / pro modes (720p / 1080p); a 4k request snaps to pro.
 * Durations are limited to 5s or 10s.
 *
 * Base URL defaults to the Singapore (international) host and can be overridden
 * via config('ai-studio.kling.base_url').
 */
class KlingKlingaiClient implements AsyncVideoProviderInterface
{
    use DownloadsVideoResult;
    use ResolvesVideoInputs;

    protected const FALLBACK_MODEL = 'kling-v3';

    protected const DEFAULT_BASE_URL = 'https://api-singapore.klingai.com';

    protected const SUPPORTED_RESOLUTIONS = ['720p', '1080p'];

    /** Separator packing the task type into the operation id for polling. */
    protected const OP_SEPARATOR = '|';

    public function __construct(protected string $apiKey, protected ?string $modelId = null) {}

    protected function baseUrl(): string
    {
        return rtrim((string) config('ai-studio.kling.base_url', self::DEFAULT_BASE_URL), '/');
    }

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $token = $this->bearerToken();
            if ($token === null) {
                return GenerationResult::failure('Kling AI key is invalid. Expected "accessKey:secretKey".');
            }

            $isImageToVideo = ! empty($request->referenceImagePath);
            $type = $isImageToVideo ? 'image2video' : 'text2video';
            $tier = $this->resolveResolutionTier($request->resolution, $request->width, $request->height, self::SUPPORTED_RESOLUTIONS);

            $payload = [
                'model_name' => $this->modelId ?: self::FALLBACK_MODEL,
                'prompt' => $request->prompt,
                'mode' => $this->modeForTier($tier),
                'duration' => (string) $this->resolveDuration($request->duration),
                'cfg_scale' => 0.5,
            ];

            if ($isImageToVideo) {
                $base64 = $this->referenceImageBase64($request->referenceImagePath);
                if ($base64 === null) {
                    return GenerationResult::failure('Kling could not read the reference image for image-to-video.');
                }
                // Kling's `image` field takes bare base64 (no data: prefix).
                $payload['image'] = $base64;
            } else {
                // Aspect ratio only applies to text-to-video; image mode
                // adopts the source image's ratio.
                $payload['aspect_ratio'] = $this->resolveAspectRatioBasic($request->width, $request->height);
            }

            $start = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl()."/v1/videos/{$type}", $payload);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Kling (Kling AI) API error: {$start->status()} - {$start->body()}");
            }

            if ((int) $start->json('code') !== 0) {
                return GenerationResult::failure('Kling (Kling AI) API error: '.(string) $start->json('message'));
            }

            $taskId = $start->json('data.task_id');

            if (! $taskId) {
                return GenerationResult::failure('Kling (Kling AI) returned no task id to poll.');
            }

            // Pack the task type so the poll hits the matching query endpoint.
            return GenerationResult::submitted($type.self::OP_SEPARATOR.$taskId);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Kling submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $token = $this->bearerToken();
            if ($token === null) {
                return GenerationResult::failure('Kling AI key is invalid. Expected "accessKey:secretKey".');
            }

            [$type, $taskId] = $this->parseOperation($operationId);

            $poll = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->timeout(30)->get($this->baseUrl()."/v1/videos/{$type}/{$taskId}");

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Kling (Kling AI) poll error: {$poll->status()} - {$poll->body()}");
            }

            $status = (string) $poll->json('data.task_status');

            if (in_array($status, ['submitted', 'processing'], true)) {
                return GenerationResult::stillPending();
            }

            if ($status !== 'succeed') {
                $message = (string) ($poll->json('data.task_status_msg') ?: $poll->json('message') ?: $status);

                return GenerationResult::failure("Kling generation {$status}: {$message}");
            }

            $videoUrl = $poll->json('data.task_result.videos.0.url');

            if (! $videoUrl) {
                return GenerationResult::failure('Kling (Kling AI) response missing video URL.');
            }

            // Signed CDN URL — download WITHOUT the auth header.
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
     * @return array{0:string, 1:string}  [taskType, taskId]
     */
    protected function parseOperation(string $operationId): array
    {
        if (str_contains($operationId, self::OP_SEPARATOR)) {
            [$type, $id] = explode(self::OP_SEPARATOR, $operationId, 2);
            $type = in_array($type, ['text2video', 'image2video'], true) ? $type : 'text2video';

            return [$type, $id];
        }

        return ['text2video', $operationId];
    }

    /**
     * Build the bearer token. The stored key is "accessKey:secretKey" → sign a
     * short-lived HS256 JWT (Kling's scheme). A single token (no colon) is used
     * as-is for proxies that accept a static bearer.
     */
    protected function bearerToken(): ?string
    {
        $key = trim($this->apiKey);

        if ($key === '') {
            return null;
        }

        if (! str_contains($key, ':')) {
            return $key;
        }

        [$accessKey, $secretKey] = explode(':', $key, 2);
        $accessKey = trim($accessKey);
        $secretKey = trim($secretKey);

        if ($accessKey === '' || $secretKey === '') {
            return null;
        }

        return $this->signJwt($accessKey, $secretKey);
    }

    /** Sign a Kling-style HS256 JWT: {iss, exp: +30m, nbf: -5s}. */
    protected function signJwt(string $accessKey, string $secretKey): string
    {
        $now = time();

        $header = $this->base64UrlEncode((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode((string) json_encode([
            'iss' => $accessKey,
            'exp' => $now + 1800,
            'nbf' => $now - 5,
        ]));

        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', $header.'.'.$payload, $secretKey, true)
        );

        return $header.'.'.$payload.'.'.$signature;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** Kling classic modes: std (≈720p) / pro (≈1080p). 4k snaps to pro. */
    protected function modeForTier(string $tier): string
    {
        return $tier === '720p' ? 'std' : 'pro';
    }

    /** Kling's classic API only accepts 5s or 10s clips. */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 5;
        }

        return $requested <= 7 ? 5 : 10;
    }
}
