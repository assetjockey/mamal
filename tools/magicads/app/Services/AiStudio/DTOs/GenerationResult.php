<?php

namespace App\Services\AiStudio\DTOs;

class GenerationResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $filePath = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $fileSize = null,
        public readonly ?string $error = null,
        public readonly bool $rateLimited = false,
        public readonly ?int $retryAfter = null,

        // Async video pipeline fields.
        //
        // `pending` is true when a poll found the provider job still
        // running — it is neither a success nor a failure, just "check
        // again later". The cron poller leaves the AdCreative in
        // `processing` and tries again on the next tick.
        public readonly bool $pending = false,

        // The provider-side operation / task identifier returned by a
        // submit call (Veo operation name, Runway/Seedance task id,
        // Kling data.task_id). Persisted on the AdCreative so a later,
        // stateless cron run can poll it.
        public readonly ?string $operationId = null,
    ) {}

    /** A "still running on the provider" poll outcome. */
    public static function stillPending(): self
    {
        return new self(success: false, pending: true);
    }

    /** A successful submit that returns an operation/task id to poll. */
    public static function submitted(string $operationId): self
    {
        return new self(success: true, operationId: $operationId);
    }

    /** A failed outcome carrying a human-readable error. */
    public static function failure(string $error): self
    {
        return new self(success: false, error: $error);
    }

    /** A rate-limited outcome (HTTP 429) with an optional retry delay. */
    public static function throttled(?int $retryAfter = null): self
    {
        return new self(success: false, rateLimited: true, retryAfter: $retryAfter);
    }
}
