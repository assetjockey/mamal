<?php

namespace App\Services\AiStudio\Contracts;

use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;

/**
 * Implemented by every video engine driver. Splits the old blocking
 * `generateVideo()` into two stateless steps so the generation can be
 * driven by a cron poller instead of a long-lived queue worker:
 *
 *   1. submitVideo()  — kick off the provider job, return its operation/
 *                        task id immediately (a few seconds, no waiting).
 *   2. pollVideo()     — one status check against that id. Returns:
 *                          - pending  → still rendering, try again later
 *                          - success  → MP4 downloaded to the results disk
 *                          - failure  → provider reported an error
 *
 * No driver method blocks waiting for the render to finish, so neither a
 * web request nor a cron tick is ever held open for minutes.
 */
interface AsyncVideoProviderInterface
{
    /**
     * Start the provider-side generation. On success the returned
     * GenerationResult carries `operationId` (via GenerationResult::submitted()).
     */
    public function submitVideo(GenerationRequest $request): GenerationResult;

    /**
     * Check a previously-submitted job exactly once.
     *
     * @param  string  $operationId  The id returned by submitVideo().
     * @param  GenerationRequest  $request  The original request (for provider
     *                                      slug, dimensions, etc. needed to store the downloaded file).
     */
    public function pollVideo(string $operationId, GenerationRequest $request): GenerationResult;
}
