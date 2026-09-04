<?php

namespace App\Services\AiStudio\Concerns;

use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared helpers for the video drivers' async pipeline:
 *   - downloadAndStore(): fetch the finished MP4 and write it to the
 *     `results` disk, returning a success GenerationResult.
 *   - blockingGenerate(): the legacy submit-then-sleep-poll behaviour,
 *     rebuilt on top of submitVideo()/pollVideo() so AiProviderInterface's
 *     generateVideo() still works (e.g. for the synchronous "run now"
 *     fallback) without duplicating HTTP logic.
 */
trait DownloadsVideoResult
{
    /**
     * Download the finished video via the given fetcher and persist it.
     *
     * @param  callable():Response  $fetch  Returns the HTTP response holding the MP4 bytes.
     */
    protected function downloadAndStore(GenerationRequest $request, callable $fetch, string $label): GenerationResult
    {
        $download = $fetch();

        if ($download->failed()) {
            return GenerationResult::failure("{$label} download error: {$download->status()}");
        }

        $videoContent = $download->body();

        if ($videoContent === '' || $videoContent === null) {
            return GenerationResult::failure("{$label} returned an empty video file.");
        }

        $assetId = Str::uuid()->toString();
        $path = "videos/{$request->provider}/{$assetId}.mp4";

        Storage::disk('results')->put($path, $videoContent);

        return new GenerationResult(
            success: true,
            filePath: $path,
            mimeType: 'video/mp4',
            fileSize: strlen($videoContent),
        );
    }

    /**
     * Legacy blocking generate: submit, then sleep-poll until done or
     * timeout. Built on the async methods so there's a single source of
     * truth for the provider HTTP shape.
     *
     * @param  int  $pollInterval  Seconds between polls.
     * @param  int  $timeout  Max total wait in seconds.
     */
    protected function blockingGenerate(GenerationRequest $request, string $label, int $pollInterval, int $timeout): GenerationResult
    {
        $submit = $this->submitVideo($request);

        if (! $submit->success || ! $submit->operationId) {
            // Carry through rate-limit / error as-is.
            return $submit->rateLimited
                ? $submit
                : GenerationResult::failure($submit->error ?? "{$label} submit failed.");
        }

        $deadline = time() + $timeout;

        while (time() < $deadline) {
            sleep($pollInterval);

            $poll = $this->pollVideo($submit->operationId, $request);

            if ($poll->pending) {
                continue;
            }

            // Either success (with filePath) or a hard failure.
            return $poll;
        }

        return GenerationResult::failure("{$label} generation timed out after {$timeout}s.");
    }
}
