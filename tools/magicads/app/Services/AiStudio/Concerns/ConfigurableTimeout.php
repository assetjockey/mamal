<?php

namespace App\Services\AiStudio\Concerns;

/**
 * Lets a driver's HTTP timeout be capped per-call so the same driver can be
 * used two ways:
 *
 *   - Synchronous "fast path" (inline in the web request): cap at a short
 *     budget (e.g. 60s) so a slow render never holds the request open. If
 *     the cap is hit the driver reports the generation as still pending
 *     (GenerationResult::stillPending()) rather than failed, so the cron
 *     fallback can finish it.
 *   - Cron "completion path": no cap → the driver uses its full default
 *     timeout to give a genuinely slow render time to finish.
 *
 * Each driver is constructed fresh per resolve (ProviderManager does
 * `new $driverClass($apiKey)`), so mutating `$this` here carries no
 * cross-request state risk.
 */
trait ConfigurableTimeout
{
    /** When set, caps every HTTP timeout in the driver to at most this many seconds. */
    protected ?int $timeoutBudget = null;

    /**
     * Cap this driver's HTTP timeouts at $seconds. Pass null to clear the
     * cap and fall back to the driver's full default timeouts.
     */
    public function withTimeout(?int $seconds): static
    {
        $this->timeoutBudget = $seconds !== null ? max(1, $seconds) : null;

        return $this;
    }

    /** Whether a short sync budget is currently in effect. */
    protected function hasTimeoutBudget(): bool
    {
        return $this->timeoutBudget !== null;
    }

    /**
     * Resolve the effective timeout for a call whose natural default is
     * $default seconds. With a budget set, never exceed it.
     */
    protected function httpTimeout(int $default): int
    {
        if ($this->timeoutBudget === null) {
            return $default;
        }

        return min($default, $this->timeoutBudget);
    }

    /**
     * If a thrown error is a network/timeout while we're running under a
     * short sync budget, treat it as "not finished yet" so the caller can
     * defer the generation to the cron completion path instead of failing
     * it. Returns a pending GenerationResult in that case, otherwise null
     * (caller should surface a real failure).
     */
    protected function deferIfTimedOut(\Throwable $e): ?\App\Services\AiStudio\DTOs\GenerationResult
    {
        if (! $this->hasTimeoutBudget()) {
            return null;
        }

        $isTimeout = $e instanceof \Illuminate\Http\Client\ConnectionException
            || stripos($e->getMessage(), 'timed out') !== false
            || stripos($e->getMessage(), 'timeout') !== false;

        return $isTimeout ? \App\Services\AiStudio\DTOs\GenerationResult::stillPending() : null;
    }
}
