<?php

namespace App\Services\AiStudio\Drivers;

use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Driver for Runway Gen-4 Turbo — Runway's 2026 production model with
 * the strongest character/product consistency across multi-shot
 * sequences and a deep VFX/editing toolset.
 *
 * Runway's dev API is async: POST returns a task id, then we poll
 * `/v1/tasks/{id}` until status === SUCCEEDED. The driver exposes that
 * directly via submitVideo()/pollVideo() so the cron poller never blocks.
 *
 * Reference: https://docs.dev.runwayml.com/api/
 */
class RunwayDriver implements AiProviderInterface, AsyncVideoProviderInterface
{
    use DownloadsVideoResult;

    /** Pinned API version header — Runway requires this for stability. */
    protected const API_VERSION = '2024-11-06';

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Runway does not support image generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'video';
    }

    // ------------------------------------------------------------------
    // Async pipeline
    // ------------------------------------------------------------------

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $aspectRatio = $this->resolveAspectRatio($request->width, $request->height);
            $duration = max(5, min((int) ($request->duration ?? 5), 10));

            $hasReference = ! empty($request->referenceImagePath);
            $endpoint = $hasReference
                ? 'https://api.dev.runwayml.com/v1/image_to_video'
                : 'https://api.dev.runwayml.com/v1/text_to_video';

            $payload = [
                'model' => 'gen4_turbo',
                'promptText' => $request->prompt,
                'duration' => $duration,
                'ratio' => $aspectRatio,
            ];

            if ($hasReference) {
                $bytes = Storage::disk('public')->get($request->referenceImagePath);
                $mime = $this->guessImageMime($request->referenceImagePath);
                if ($bytes !== null) {
                    $payload['promptImage'] = 'data:'.$mime.';base64,'.base64_encode($bytes);
                }
            }

            $start = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'X-Runway-Version' => self::API_VERSION,
            ])
                ->timeout(60)
                ->post($endpoint, $payload);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Runway API error: {$start->status()} - {$start->body()}");
            }

            $taskId = $start->json('id');

            if (! $taskId) {
                return GenerationResult::failure('Runway API returned no task id to poll.');
            }

            return GenerationResult::submitted($taskId);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Runway submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $poll = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'X-Runway-Version' => self::API_VERSION,
            ])
                ->timeout(30)
                ->get("https://api.dev.runwayml.com/v1/tasks/{$operationId}");

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Runway poll error: {$poll->status()} - {$poll->body()}");
            }

            $status = $poll->json('status');

            if (in_array($status, ['FAILED', 'CANCELLED'], true)) {
                return GenerationResult::failure(
                    'Runway task '.strtolower((string) $status).': '.($poll->json('failure') ?? 'unknown')
                );
            }

            if ($status !== 'SUCCEEDED') {
                // PENDING / RUNNING / THROTTLED → keep waiting.
                return GenerationResult::stillPending();
            }

            $videoUrl = $poll->json('output.0');

            if (! $videoUrl) {
                return GenerationResult::failure('Runway response missing video URL.');
            }

            return $this->downloadAndStore(
                $request,
                fn () => Http::timeout(120)->get($videoUrl),
                'Runway',
            );
        } catch (\Throwable $e) {
            return GenerationResult::failure("Runway poll failed: {$e->getMessage()}");
        }
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        return $this->blockingGenerate($request, 'Runway', pollInterval: 6, timeout: 240);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    protected function resolveAspectRatio(int $width, int $height): string
    {
        $ratio = $width / max($height, 1);

        return match (true) {
            abs($ratio - 16 / 9) < 0.01 => '1280:720',
            abs($ratio - 9 / 16) < 0.01 => '720:1280',
            abs($ratio - 1) < 0.01 => '960:960',
            abs($ratio - 4 / 3) < 0.01 => '1104:832',
            abs($ratio - 3 / 4) < 0.01 => '832:1104',
            abs($ratio - 21 / 9) < 0.01 => '1584:672',
            default => '1280:720',
        };
    }

    protected function guessImageMime(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
