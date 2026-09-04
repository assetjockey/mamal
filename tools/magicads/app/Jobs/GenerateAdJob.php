<?php

namespace App\Jobs;

use App\Models\AdCreative;
use App\Models\User;
use App\Services\AiStudio\CreditService;
use App\Services\AiStudio\DTOs\GenerationRequest;
use App\Services\AiStudio\ProviderManager;
use App\Services\AiStudio\VideoOverlayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAdJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Image generations can take 1–3 minutes at high quality. Retrying after
    // a curl/network timeout is rarely productive, so default to a single
    // attempt and let the user re-run from the UI if it actually fails.
    // Rate-limited responses (HTTP 429) still re-release with backoff via
    // the rateLimited path on GenerationResult.
    public int $tries = 1;

    public int $backoff = 30;

    public int $timeout = 600;

    public function __construct(
        public readonly int $adAssetId,
        public readonly GenerationRequest $request,
    ) {}

    public function handle(
        ProviderManager $manager,
        CreditService $credits,
        VideoOverlayService $overlay,
    ): void {
        $asset = AdCreative::findOrFail($this->adAssetId);
        $asset->update(['status' => 'processing']);

        $driver = $manager->driver($this->request->provider);
        $result = $this->request->type === 'image'
            ? $driver->generateImage($this->request)
            : $driver->generateVideo($this->request);

        if ($result->rateLimited) {
            $this->release($result->retryAfter ?? 60);

            return;
        }

        if (! $result->success) {
            $asset->update(['status' => 'failed', 'error_message' => $result->error]);

            return;
        }

        // Post-processing — burn the user-typed headline + CTA into video
        // clips. The decision is per-engine (and overrideable per-generation):
        //
        //   1. If the user explicitly set forceOverlay = false → skip.
        //   2. If the user explicitly set forceOverlay = true  → run.
        //   3. Otherwise look at the engine's `text_rendering` flag in
        //      config/ai-studio.php:
        //        - 'native' (Veo, Veo Lite) → skip; trust the model.
        //        - anything else            → run; FFmpeg owns the text.
        //
        // Image overlays are skipped entirely — Ideogram, GPT Image 2 and
        // Nano Banana 2 are all strong at in-image text, so post-processing
        // there would be more harmful than helpful.
        $finalPath = $result->filePath;
        $overlayApplied = false;

        if (
            $this->request->type === 'video'
            && $result->filePath
            && ($this->request->overlayText || $this->request->ctaText)
            && $this->shouldRunOverlay()
        ) {
            $burned = $overlay->burn(
                relativePath: $result->filePath,
                headline: $this->request->overlayText,
                cta: $this->request->ctaText,
                primaryColor: $this->request->brandPrimaryColor,
                secondaryColor: $this->request->brandSecondaryColor,
            );

            // burn() returns the same path on success (we replace in-place),
            // returns the original path if overlay was skipped, returns
            // null only if the source file was missing.
            if ($burned !== null) {
                $finalPath = $burned;
                $overlayApplied = $burned === $result->filePath;
            }
        }

        $asset->update([
            'status' => 'completed',
            'file_path' => $finalPath,
            'mime_type' => $result->mimeType,
            // file_size may shift slightly after re-encode; recompute when
            // the overlay actually ran so the UI shows the right number.
            'file_size' => $overlayApplied
                ? \Illuminate\Support\Facades\Storage::disk('results')->size($finalPath)
                : $result->fileSize,
            // The actual model identifier behind the chosen provider/vendor
            // (e.g. provider 'veo-lite' → model_id 'veo-3.0-fast'). Falls back
            // to the vendor slug when the row has no explicit model_id.
            'model_id' => \App\Models\MediaModel::findByVendor($this->request->provider)?->model_id
                ?? $this->request->provider,
            'credits' => $credits->getCost($this->request->type, $this->request->provider, $this->billingUnits(), $this->request->resolution),
            'completed_at' => now(),
        ]);

        $user = User::find($asset->user_id);
        $credits->deduct($user, $this->request->type, $this->request->provider, $this->billingUnits(), $this->request->resolution);
    }

    /**
     * Billable units for this generation:
     *  - video → clip duration in seconds (rate is credits/second)
     *  - image → 1 image (rate is credits/image)
     */
    protected function billingUnits(): int
    {
        return $this->request->type === 'video'
            ? max(1, (int) ($this->request->duration ?? 1))
            : 1;
    }

    public function failed(\Throwable $e): void
    {
        AdCreative::where('id', $this->adAssetId)
            ->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
    }

    /**
     * Decide whether the FFmpeg overlay step should run for this generation.
     *
     * Resolution order:
     *   1. The user's per-generation `forceOverlay` flag wins if set.
     *   2. Otherwise the engine's `text_rendering` config decides:
     *      - 'native' → skip (model handles text well)
     *      - anything else → run
     */
    protected function shouldRunOverlay(): bool
    {
        if ($this->request->forceOverlay === true) {
            return true;
        }
        if ($this->request->forceOverlay === false) {
            return false;
        }

        $textRendering = (string) (\App\Models\MediaModel::query()
            ->where('vendor', $this->request->provider)
            ->value('text_rendering') ?? 'weak');

        // 'native' engines render text well enough that the FFmpeg overlay
        // would actually look worse (flat, frame-aligned, ignores scene
        // geometry). Trust the model.
        return $textRendering !== 'native';
    }
}
