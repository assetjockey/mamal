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
 * Driver for Black Forest Labs FLUX.2 Pro.
 *
 * The successor to Stable Diffusion in this stack — same lineage (BFL is
 * the team that originally built Stable Diffusion), much stronger text
 * rendering, multi-reference conditioning (up to 8 images), and
 * deterministic 4-megapixel output.
 *
 * BFL's API is async: POST returns a request id, GET polls a result URL.
 *   POST  https://api.bfl.ml/v1/flux-2-pro
 *   GET   https://api.bfl.ml/v1/get_result?id={id}
 *
 * Auth is via the `x-key` header (NOT Bearer).
 *
 * Reference: https://docs.bfl.ai/quick_start/get_started
 */
class FluxDriver implements AiProviderInterface
{
    use ConfigurableTimeout;
    use ResolvesImageInputs;

    /** Max wait for a single render in seconds (90 seconds is plenty for FLUX). */
    protected const POLL_TIMEOUT_SECONDS = 90;

    /** Polling interval in seconds. */
    protected const POLL_INTERVAL_SECONDS = 2;

    public function __construct(protected string $apiKey) {}

    public function generateImage(GenerationRequest $request): GenerationResult
    {
        try {
            // FLUX.2 Pro accepts width/height directly in 32-pixel multiples
            // and caps each side at 2048. Snap silently to keep prompts working
            // for any preset the user picked.
            $width = $this->snapTo32(min($request->width, 2048));
            $height = $this->snapTo32(min($request->height, 2048));

            $payload = [
                'prompt' => $request->prompt,
                'width' => $width,
                'height' => $height,
                'output_format' => 'png',
                'safety_tolerance' => 2,
            ];

            // FLUX.2 Pro accepts multiple reference images via image_prompts[]
            // (up to 8). We attach the brand logo first (so the real mark is
            // composited, not invented) followed by any user reference image.
            // Falls back to the single image_prompt field for one input.
            $imageInputs = $this->imageInputs($request);
            if ($imageInputs !== []) {
                $encoded = array_map(fn ($i) => $i['data'], $imageInputs);

                if (count($encoded) === 1) {
                    $payload['image_prompt'] = $encoded[0];
                } else {
                    $payload['image_prompts'] = $encoded;
                }
                $payload['image_prompt_strength'] = 0.6;
            }

            $start = Http::withHeaders([
                'x-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->httpTimeout(60))
                ->post('https://api.bfl.ml/v1/flux-2-pro', $payload);

            if ($start->status() === 429) {
                return new GenerationResult(
                    success: false,
                    rateLimited: true,
                    retryAfter: (int) ($start->header('Retry-After') ?? 60),
                );
            }

            if ($start->failed()) {
                return new GenerationResult(
                    success: false,
                    error: "FLUX API error: {$start->status()} - {$start->body()}",
                );
            }

            $requestId = $start->json('id');

            if (! $requestId) {
                return new GenerationResult(
                    success: false,
                    error: 'FLUX API returned no request id to poll.',
                );
            }

            $imageUrl = $this->pollUntilReady($requestId);

            if ($imageUrl === null) {
                // Hit the poll deadline. Under a short sync budget this just
                // means "not done yet" — defer to the cron completion path
                // instead of failing the user's generation.
                if ($this->hasTimeoutBudget()) {
                    return GenerationResult::stillPending();
                }

                return new GenerationResult(
                    success: false,
                    error: 'FLUX generation timed out after '.self::POLL_TIMEOUT_SECONDS.'s.',
                );
            }

            if (str_starts_with($imageUrl, 'error:')) {
                return new GenerationResult(
                    success: false,
                    error: substr($imageUrl, 6),
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
                error: "FLUX generation failed: {$e->getMessage()}",
            );
        }
    }

    public function generateVideo(GenerationRequest $request): GenerationResult
    {
        throw new \RuntimeException('FLUX does not support video generation.');
    }

    public function supports(string $type): bool
    {
        return $type === 'image';
    }

    /**
     * Polls /v1/get_result until status === Ready / Failed / timeout.
     * Returns the image URL on success, `error:<msg>` on failure, null on timeout.
     *
     * The deadline is capped to the active sync budget (if any) so the fast
     * path never blocks the web request past its allowance — the cron path
     * resumes polling with the full deadline.
     */
    protected function pollUntilReady(string $requestId): ?string
    {
        $deadline = time() + $this->httpTimeout(self::POLL_TIMEOUT_SECONDS);

        while (time() < $deadline) {
            sleep(self::POLL_INTERVAL_SECONDS);

            $poll = Http::withHeaders([
                'x-key' => $this->apiKey,
            ])
                ->timeout(15)
                ->get('https://api.bfl.ml/v1/get_result', ['id' => $requestId]);

            if ($poll->failed()) {
                return 'error:FLUX poll error: '.$poll->status().' - '.$poll->body();
            }

            $status = $poll->json('status');

            if ($status === 'Ready') {
                return $poll->json('result.sample')
                    ?? 'error:FLUX response missing image URL.';
            }

            if (in_array($status, ['Error', 'Content Moderated', 'Request Moderated'], true)) {
                return 'error:FLUX task '.strtolower((string) $status).': '
                    .($poll->json('result.error') ?? 'unknown');
            }
        }

        return null;
    }

    /** FLUX requires width/height to be multiples of 32. */
    protected function snapTo32(int $value): int
    {
        return max(256, (int) (round($value / 32) * 32));
    }
}
