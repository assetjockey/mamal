<?php

namespace App\Services\AiStudio;

use App\Models\AdCreative;
use App\Models\MediaModel;
use App\Models\User;
use App\Services\AiStudio\Contracts\AsyncVideoProviderInterface;
use App\Services\AiStudio\Contracts\CreditServiceInterface;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\DTOs\GenerationResult;
use Illuminate\Support\Facades\Storage;

/**
 * Drives the cron-based async video pipeline. No queue worker required —
 * a once-a-minute `ai-studio:process-generations` run advances every
 * in-flight video through three stateless steps:
 *
 *   submit()   pending  + no job id   → POST to provider, store job id,
 *                                        move to processing.
 *   poll()     processing + job id    → one status check. Still rendering?
 *                                        leave it. Done? download + finalize.
 *   finalize() success                → FFmpeg overlay (if applicable),
 *                                        persist file, deduct credits,
 *                                        mark completed.
 *
 * Each AdCreative carries its full GenerationRequest in `generation_meta`
 * so any run can resume it without the originating web request.
 */
class VideoGenerationService
{
    public function __construct(
        protected ProviderManager $manager,
        protected CreditServiceInterface $credits,
        protected VideoOverlayService $overlay,
    ) {}

    /**
     * Submit a freshly-created (pending) video creative to its provider.
     * Returns the asset's resulting status for logging.
     */
    public function submit(AdCreative $asset): string
    {
        $request = $this->request($asset);
        $driver = $this->manager->driver($request->provider);

        if (! $driver instanceof AsyncVideoProviderInterface) {
            $asset->update([
                'status' => 'failed',
                'error_message' => "Provider [{$request->provider}] does not support async video generation.",
            ]);

            return 'failed';
        }

        try {
            $result = $driver->submitVideo($request);
        } catch (\Throwable $e) {
            $asset->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return 'failed';
        }

        if ($result->rateLimited) {
            // Leave it pending; a later cron tick will retry the submit.
            $asset->update(['last_polled_at' => now()]);

            return 'pending';
        }

        if (! $result->success || ! $result->operationId) {
            $asset->update([
                'status' => 'failed',
                'error_message' => $result->error ?? 'Provider returned no operation id.',
            ]);

            return 'failed';
        }

        $asset->update([
            'status' => 'processing',
            'provider_job_id' => $result->operationId,
            'submitted_at' => now(),
            'last_polled_at' => now(),
        ]);

        return 'processing';
    }

    /**
     * Poll a processing video creative exactly once and finalize it if the
     * provider has finished. Returns the resulting status.
     */
    public function poll(AdCreative $asset): string
    {
        $request = $this->request($asset);
        $driver = $this->manager->driver($request->provider);

        if (! $driver instanceof AsyncVideoProviderInterface) {
            $asset->update([
                'status' => 'failed',
                'error_message' => "Provider [{$request->provider}] does not support async polling.",
            ]);

            return 'failed';
        }

        try {
            $result = $driver->pollVideo((string) $asset->provider_job_id, $request);
        } catch (\Throwable $e) {
            $asset->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return 'failed';
        }

        $asset->increment('poll_count');
        $asset->update(['last_polled_at' => now()]);

        if ($result->pending || $result->rateLimited) {
            return 'processing';
        }

        if (! $result->success) {
            $asset->update(['status' => 'failed', 'error_message' => $result->error]);

            return 'failed';
        }

        $this->finalize($asset, $request, $result);

        return 'completed';
    }

    /**
     * Mark a creative failed because it has been running too long or
     * polled too many times. Called by the reaper in the command.
     */
    public function reap(AdCreative $asset, string $reason): void
    {
        $asset->update(['status' => 'failed', 'error_message' => $reason]);
    }

    /**
     * Burn the overlay (when applicable), persist the file, deduct credits
     * and mark the creative completed. Mirrors the old GenerateAdJob tail.
     */
    protected function finalize(AdCreative $asset, GenerationRequest $request, GenerationResult $result): void
    {
        $finalPath = $result->filePath;
        $overlayApplied = false;

        if (
            $result->filePath
            && ($request->overlayText || $request->ctaText)
            && $this->shouldRunOverlay($request)
        ) {
            $burned = $this->overlay->burn(
                relativePath: $result->filePath,
                headline: $request->overlayText,
                cta: $request->ctaText,
                primaryColor: $request->brandPrimaryColor,
                secondaryColor: $request->brandSecondaryColor,
            );

            if ($burned !== null) {
                $finalPath = $burned;
                $overlayApplied = $burned === $result->filePath;
            }
        }

        $units = max(1, (int) ($request->duration ?? 1));

        $asset->update([
            'status' => 'completed',
            'file_path' => $finalPath,
            'mime_type' => $result->mimeType,
            'file_size' => $overlayApplied
                ? Storage::disk('results')->size($finalPath)
                : $result->fileSize,
            'model_id' => MediaModel::findByVendor($request->provider)?->model_id ?? $request->provider,
            'credits' => $this->credits->getCost('video', $request->provider, $units, $request->resolution),
            'completed_at' => now(),
        ]);

        if ($user = User::find($asset->user_id)) {
            $this->credits->deduct($user, 'video', $request->provider, $units, $request->resolution);
        }

        // Let optional storage plugins (e.g. Amazon S3) mirror the result.
        \App\Events\CreativeStored::dispatch($asset->refresh());
    }

    /**
     * Resolution order for the FFmpeg overlay step:
     *   1. forceOverlay flag wins if set.
     *   2. else the engine's text_rendering decides ('native' → skip).
     */
    protected function shouldRunOverlay(GenerationRequest $request): bool
    {
        if ($request->forceOverlay === true) {
            return true;
        }
        if ($request->forceOverlay === false) {
            return false;
        }

        $textRendering = (string) (MediaModel::query()
            ->where('vendor', $request->provider)
            ->value('text_rendering') ?? 'weak');

        return $textRendering !== 'native';
    }

    /**
     * Rebuild the GenerationRequest persisted on the creative. Falls back to
     * reconstructing from columns for older rows written before generation_meta
     * carried the full request.
     */
    protected function request(AdCreative $asset): GenerationRequest
    {
        $meta = $asset->generation_meta;

        if (is_array($meta) && ! empty($meta['provider'])) {
            return GenerationRequest::fromArray($meta);
        }

        return new GenerationRequest(
            prompt: (string) $asset->prompt,
            type: 'video',
            provider: (string) $asset->provider,
            presetSlug: (string) $asset->preset_slug,
            width: (int) $asset->width,
            height: (int) $asset->height,
            duration: $asset->duration ? (int) $asset->duration : null,
        );
    }
}
