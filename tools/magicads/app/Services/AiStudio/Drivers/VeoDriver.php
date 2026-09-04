<?php

namespace App\Services\AiStudio\Drivers;

use App\Models\MediaModel;
use App\Services\AiStudio\Concerns\DownloadsVideoResult;
use App\Services\AiStudio\Contracts\AiProviderInterface;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Http;

/**
 * Driver for Google Veo 3.1 — Google's film-grade video model with
 * native synchronised audio (dialogue, ambience, sound effects) in
 * a single pass.
 *
 * Two SKUs are surfaced through this driver, switched by the
 * `provider` key on the GenerationRequest:
 *   - `veo`       → Standard (1080p, premium tier)
 *   - `veo-lite`  → Fast (lower fidelity, budget tier)
 *
 * The exact model identifier is read from the `media_models` row for the
 * provider (single source of truth), so it stays in sync with the seeder
 * and the admin UI instead of being hardcoded here.
 *
 * Authenticates with the same `gemini_key` Admin Key column already used
 * for Nano Banana 2 image generation. One key, two media types.
 *
 * Endpoint shape — Gemini API long-running operation (async):
 *   POST  /v1beta/models/{model}:predictLongRunning   → returns operation name
 *   GET   /v1beta/{operationName}                       → poll until done
 *   GET   {video.uri}                                   → fetch the MP4
 *
 * Reference: https://ai.google.dev/gemini-api/docs/video
 */
class VeoDriver implements AiProviderInterface, AsyncVideoProviderInterface
{
    use DownloadsVideoResult;

    protected const BASE = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Veo accepts only these discrete clip lengths — there are no values in
     * between. A requested duration is snapped to the nearest of these before
     * submit, so e.g. a 4s selection is sent as 4 (not clamped up to 5, which
     * the API rejects with "durationSeconds is out of bound").
     */
    protected const ALLOWED_DURATIONS = [4, 6, 8];

    /** Fallback model ids if the media_models row has no model_id. */
    protected const FALLBACK_MODELS = [
        'veo-lite' => 'veo-3.1-fast-generate-preview',
        'veo' => 'veo-3.1-generate-preview',
    ];

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('Veo does not support image generation. Use Nano Banana 2 (gemini) instead.');
    }

    public function supports(string $type): bool
    {
        return $type === 'video';
    }

    // ------------------------------------------------------------------
    // Async pipeline (used by the cron poller)
    // ------------------------------------------------------------------

    public function submitVideo(GenerationRequest $request): GenerationResult
    {
        try {
            $modelId = $this->resolveModelId($request->provider);
            $aspectRatio = $this->resolveAspectRatio($request->width, $request->height);
            $duration = $this->resolveDuration($request->duration);

            $start = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey,
            ])
                ->timeout(60)
                ->post(self::BASE."/models/{$modelId}:predictLongRunning", [
                    'instances' => [[
                        'prompt' => $request->prompt,
                    ]],
                    'parameters' => [
                        'aspectRatio' => $aspectRatio,
                        'durationSeconds' => $duration,
                        'sampleCount' => 1,
                    ],
                ]);

            if ($start->status() === 429) {
                return GenerationResult::throttled((int) ($start->header('Retry-After') ?? 60));
            }

            if ($start->failed()) {
                return GenerationResult::failure("Veo API error: {$start->status()} - {$start->body()}");
            }

            $operationName = $start->json('name');

            if (! $operationName) {
                return GenerationResult::failure('Veo API returned no operation name to poll.');
            }

            return GenerationResult::submitted($operationName);
        } catch (\Throwable $e) {
            return GenerationResult::failure("Veo submit failed: {$e->getMessage()}");
        }
    }

    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult
    {
        try {
            $poll = Http::withHeaders([
                'x-goog-api-key' => $this->apiKey,
            ])
                ->timeout(30)
                ->get(self::BASE."/{$operationId}");

            if ($poll->status() === 429) {
                return GenerationResult::stillPending();
            }

            if ($poll->failed()) {
                return GenerationResult::failure("Veo poll error: {$poll->status()} - {$poll->body()}");
            }

            $operation = $poll->json();

            if (($operation['done'] ?? false) !== true) {
                return GenerationResult::stillPending();
            }

            if (isset($operation['error'])) {
                return GenerationResult::failure(
                    'Veo generation failed: '.($operation['error']['message'] ?? json_encode($operation['error']))
                );
            }

            $videoUri = $operation['response']['generateVideoResponse']['generatedSamples'][0]['video']['uri']
                ?? $operation['response']['videos'][0]['video']['uri']
                ?? null;

            if (! $videoUri) {
                return GenerationResult::failure('Veo response did not include a downloadable video URI.');
            }

            // Veo's download endpoint needs the API key header.
            return $this->downloadAndStore(
                $request,
                fn () => Http::withHeaders(['x-goog-api-key' => $this->apiKey])->timeout(120)->get($videoUri),
                'Veo',
            );
        } catch (\Throwable $e) {
            return GenerationResult::failure("Veo poll failed: {$e->getMessage()}");
        }
    }

    // ------------------------------------------------------------------
    // Blocking wrapper — kept for AiProviderInterface compatibility.
    // The async submit/poll path above is what the cron poller uses.
    // ------------------------------------------------------------------

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        return $this->blockingGenerate($request, 'Veo', pollInterval: 8, timeout: 240);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Veo only ships portrait or landscape — square/vertical presets fall
     * back to the closest of the two by ratio so the request still succeeds.
     */
    protected function resolveAspectRatio(int $width, int $height): string
    {
        if ($height <= 0) {
            return '16:9';
        }

        return ($width / $height) >= 1 ? '16:9' : '9:16';
    }

    /**
     * Snap the requested duration to the nearest Veo-supported clip length
     * (4, 6 or 8s). Veo rejects any value not in that discrete set, so a
     * straight numeric clamp is not enough — 5 and 7 are invalid. On a tie
     * the lower value wins (cheaper, faster render). Defaults to 8 when no
     * duration was requested.
     */
    protected function resolveDuration(?int $requested): int
    {
        if ($requested === null) {
            return 8;
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

    /**
     * Resolve the concrete model id from the media_models row (single
     * source of truth), falling back to known-good defaults. This is what
     * fixes the "models/veo-3.1-fast is not found" 404 — the hardcoded slug
     * no longer drifts from the seeded model_id.
     */
    protected function resolveModelId(string $providerKey): string
    {
        $modelId = MediaModel::findByVendor($providerKey)?->model_id;

        if ($modelId) {
            return $modelId;
        }

        return self::FALLBACK_MODELS[$providerKey] ?? self::FALLBACK_MODELS['veo'];
    }
}
