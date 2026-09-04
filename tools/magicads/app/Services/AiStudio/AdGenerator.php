<?php

namespace App\Services\AiStudio;

use App\Models\AdCreative;
use App\Models\MediaModel;
use App\Models\Project;
use App\Models\User;
use App\Services\AiStudio\DTOs\GenerationRequest;

class AdGenerator
{
    public function dispatch(User $user, GenerationRequest $request): AdCreative
    {
        $asset = $this->persistPending($user, $request);

        if ($request->type === 'video') {
            // Async/cron pipeline: the row stays `pending`, and the
            // `ai-studio:process-generations` scheduled command submits it
            // to the provider and polls to completion. No queue worker, no
            // long-lived process — works on shared hosting with just cron.
            return $asset;
        }

        // Images — hybrid pipeline (no queue worker required):
        //   Option A (priority): run inline now within a short time budget.
        //   Option B (fallback): if it exceeds the budget the service leaves
        //   the asset `pending` and the cron command finishes it.
        app(ImageGenerationService::class)->generateNow($asset);

        return $asset;
    }

    /**
     * Persist a fresh `pending` AdCreative for the request WITHOUT running
     * the provider call. Used by the image wizard so the first request can
     * return immediately and render the full-page "AI is creating your ad"
     * loading screen; a follow-up request then runs the generation (see
     * ImageStudio::processQueuedImage). The cron fallback only adopts rows
     * left untouched past its grace window, so there is no double-run race.
     */
    public function persistPending(User $user, GenerationRequest $request): AdCreative
    {
        $brandKitSnapshot = null;

        if ($request->brandKitId) {
            $brandKit = $user->brandKit;
            if ($brandKit) {
                $brandKitSnapshot = [
                    'logo_path' => $brandKit->logo_path,
                    'primary_color' => $brandKit->primary_color,
                    'secondary_color' => $brandKit->secondary_color,
                    'tagline' => $brandKit->tagline,
                ];
            }
        }

        // The provider slug maps 1:1 to a media_models row; capture its
        // model_id so analytics (and any downstream consumer) can attribute
        // the generation to a specific model rather than just the vendor.
        $modelId = MediaModel::findByVendor($request->provider)?->model_id;

        // Ownership-safe project association: only attach to a Project the user
        // owns when the creative is generated within a project's context.
        $projectId = null;
        if ($request->projectId) {
            $projectId = Project::where('user_id', $user->id)
                ->whereKey($request->projectId)
                ->value('id');
        }

        $asset = AdCreative::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'type' => $request->type,
            'status' => 'pending',
            'provider' => $request->provider,
            'model_id' => $modelId,
            'prompt' => $request->prompt,
            'preset_slug' => $request->presetSlug,
            'width' => $request->width,
            'height' => $request->height,
            'duration' => $request->duration,
            'brand_kit_snapshot' => $brandKitSnapshot,
            // Persist the full request so the stateless cron poller can
            // resume a video generation without the originating web request.
            'generation_meta' => $request->toArray(),
        ]);

        return $asset;
    }
}
