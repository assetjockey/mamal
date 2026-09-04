<?php

namespace App\Services\AiStudio;

use App\Models\AdCreative;
use App\Models\MediaModel;
use App\Models\User;
use App\Services\AiStudio\Concerns\ConfigurableTimeout;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;

/**
 * Hybrid image pipeline:
 *
 *   - Option A (priority) — generateNow(): run the provider call inline in
 *     the web request with a short time budget (default 60s). Images are a
 *     single synchronous API call, so most finish well inside that window
 *     and the user sees the result instantly.
 *
 *   - Option B (fallback) — when the budget is exceeded the driver reports
 *     the generation as still pending (it does NOT fail). The asset is left
 *     `pending` and the `ai-studio:process-generations` cron run finishes it
 *     with the driver's full timeout. The user sees a friendly "almost
 *     ready" message and the result appears on the next auto-refresh.
 *
 * Either way, completion (persist file + deduct credits + mark completed)
 * goes through the same finalize() path so billing is identical.
 */
class ImageGenerationService
{
    /** Inline (sync) budget in seconds before deferring to the cron path. */
    public const SYNC_BUDGET_SECONDS = 60;

    public function __construct(
        protected ProviderManager $manager,
        protected CreditServiceInterface $credits,
    ) {}

    /**
     * Option A: attempt the generation synchronously within the budget.
     * Returns the resulting status: 'completed', 'pending' (deferred to
     * cron) or 'failed'.
     */
    public function generateNow(AdCreative $asset): string
    {
        return $this->run($asset, self::SYNC_BUDGET_SECONDS);
    }

    /**
     * Option B: finish a deferred image from the cron command, using the
     * driver's full timeout (no short budget).
     */
    public function process(AdCreative $asset): string
    {
        return $this->run($asset, null);
    }

    /**
     * Mark an image failed (used by the cron reaper for ones stuck far too
     * long).
     */
    public function reap(AdCreative $asset, string $reason): void
    {
        $asset->update(['status' => 'failed', 'error_message' => $reason]);
    }

    /**
     * Run one generation attempt. $budget = null means "no cap" (cron path);
     * an integer caps the driver's HTTP timeout (sync path).
     */
    protected function run(AdCreative $asset, ?int $budget): string
    {
        $request = $this->request($asset);

        // Mark in-flight so the UI shows the working state immediately.
        if ($asset->status === 'pending') {
            $asset->update(['status' => 'processing']);
        }

        $driver = $this->manager->driver($request->provider);

        // Apply the sync time budget when the driver supports it.
        if ($budget !== null && in_array(ConfigurableTimeout::class, class_uses_recursive($driver), true)) {
            $driver->withTimeout($budget);
        }

        try {
            $result = $driver->generateImage($request);
        } catch (\Throwable $e) {
            $asset->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return 'failed';
        }

        // Took longer than the budget (or hit a transient rate limit):
        // leave it pending so the cron completion path picks it up.
        if ($result->pending || $result->rateLimited) {
            $asset->update(['status' => 'pending']);

            return 'pending';
        }

        if (! $result->success) {
            $asset->update(['status' => 'failed', 'error_message' => $result->error]);

            return 'failed';
        }

        $this->finalize($asset, $request, $result);

        return 'completed';
    }

    protected function finalize(AdCreative $asset, GenerationRequest $request, GenerationResult $result): void
    {
        $asset->update([
            'status' => 'completed',
            'file_path' => $result->filePath,
            'mime_type' => $result->mimeType,
            'file_size' => $result->fileSize,
            'model_id' => MediaModel::findByVendor($request->provider)?->model_id ?? $request->provider,
            'credits' => $this->credits->getCost('image', $request->provider, 1),
            'completed_at' => now(),
        ]);

        if ($user = User::find($asset->user_id)) {
            $this->credits->deduct($user, 'image', $request->provider, 1);
        }

        // Let optional storage plugins (e.g. Amazon S3) mirror the result.
        \App\Events\CreativeStored::dispatch($asset->refresh());
    }

    protected function request(AdCreative $asset): GenerationRequest
    {
        $meta = $asset->generation_meta;

        if (is_array($meta) && ! empty($meta['provider'])) {
            return GenerationRequest::fromArray($meta);
        }

        return new GenerationRequest(
            prompt: (string) $asset->prompt,
            type: 'image',
            provider: (string) $asset->provider,
            presetSlug: (string) $asset->preset_slug,
            width: (int) $asset->width,
            height: (int) $asset->height,
        );
    }
}
