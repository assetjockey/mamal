<?php

namespace App\Console\Commands\AiStudio;

use App\Models\AdCreative;
use App\Services\AiStudio\ImageGenerationService;
use App\Services\AiStudio\VideoGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Cron-driven engine for AI Studio generation. Replaces the need for a
 * long-lived `queue:work` daemon — schedule this every minute and it will
 * advance every in-flight generation one step:
 *
 *   php artisan ai-studio:process-generations
 *
 * Videos (async submit + poll):
 *   1. SUBMITS newly-created pending videos to their provider (gets a job id).
 *   2. POLLS processing videos whose providers are still rendering.
 *   3. REAPS videos that have run too long, so a user never sees an
 *      infinite spinner.
 *
 * Images (hybrid fallback):
 *   4. FINISHES images that exceeded the synchronous in-request budget and
 *      were deferred here (Option B), plus reaps any that overran.
 *
 * Every step is stateless and bounded — no step blocks waiting for a render,
 * so this is safe on shared hosting where cron processes are short-lived.
 * Designed to be overlap-safe (`withoutOverlapping` in the scheduler) and to
 * respect a wall-clock budget so a single invocation never runs long.
 */
class ProcessVideoGenerationsCommand extends Command
{
    protected $signature = 'ai-studio:process-generations
                            {--max-seconds=50 : Wall-clock budget for this run before it stops cleanly.}
                            {--stuck-after=900 : Mark a generation failed if it has been processing this many seconds.}
                            {--max-polls=240 : Mark a video failed after this many poll attempts.}
                            {--poll-interval=10 : Minimum seconds between polls of the same video.}
                            {--image-defer-grace=45 : Wait this many seconds after a deferred image was last touched before the cron picks it up, so it does not race the in-request attempt.}';

    protected $description = 'Submit, poll and finalize async video + deferred image generations (cron-driven; no queue worker needed).';

    public function handle(VideoGenerationService $videos, ImageGenerationService $images): int
    {
        $deadline = time() + (int) $this->option('max-seconds');
        $stuckAfter = (int) $this->option('stuck-after');
        $maxPolls = (int) $this->option('max-polls');
        $pollInterval = (int) $this->option('poll-interval');
        $imageGrace = (int) $this->option('image-defer-grace');

        $submitted = 0;
        $polled = 0;
        $completed = 0;
        $failed = 0;
        $reaped = 0;

        // 1. Submit freshly-created pending videos.
        $pending = AdCreative::query()
            ->where('type', 'video')
            ->where('status', 'pending')
            ->whereNull('provider_job_id')
            ->orderBy('id')
            ->limit(50)
            ->get();

        foreach ($pending as $asset) {
            if (time() >= $deadline) {
                break;
            }

            $status = $videos->submit($asset);
            $submitted++;
            if ($status === 'failed') {
                $failed++;
            }
        }

        // 2. Poll videos that are mid-render.
        $processing = AdCreative::query()
            ->where('type', 'video')
            ->where('status', 'processing')
            ->whereNotNull('provider_job_id')
            ->orderBy('last_polled_at')
            ->limit(100)
            ->get();

        foreach ($processing as $asset) {
            if (time() >= $deadline) {
                break;
            }

            // Reaper — give up on jobs that have run too long or been polled
            // too many times, so the UI shows a real error instead of hanging.
            $startedAt = $asset->submitted_at ?? $asset->created_at;
            $age = $startedAt ? (int) abs(Carbon::now()->diffInSeconds($startedAt)) : 0;

            if ($age >= $stuckAfter || $asset->poll_count >= $maxPolls) {
                $videos->reap($asset, __('Video generation timed out. Please try again.'));
                $reaped++;
                $failed++;

                continue;
            }

            // Respect a minimum interval between polls of the same job so we
            // don't hammer the provider when cron fires frequently.
            if ($asset->last_polled_at && abs(Carbon::now()->diffInSeconds($asset->last_polled_at)) < $pollInterval) {
                continue;
            }

            $status = $videos->poll($asset);
            $polled++;

            if ($status === 'completed') {
                $completed++;
            } elseif ($status === 'failed') {
                $failed++;
            }
        }

        $imgCompleted = 0;
        $imgFailed = 0;
        $imgReaped = 0;

        // 3. Finish images deferred from the synchronous in-request attempt
        //    (Option B). Only touch rows that have sat untouched for the
        //    grace window so we never race the request still handling them.
        $deferredImages = AdCreative::query()
            ->where('type', 'image')
            ->whereIn('status', ['pending', 'processing'])
            ->where('updated_at', '<=', Carbon::now()->subSeconds($imageGrace))
            ->orderBy('id')
            ->limit(25)
            ->get();

        foreach ($deferredImages as $asset) {
            if (time() >= $deadline) {
                break;
            }

            // Reaper for images that have been stuck far too long.
            $startedAt = $asset->created_at;
            $age = $startedAt ? (int) abs(Carbon::now()->diffInSeconds($startedAt)) : 0;

            if ($age >= $stuckAfter) {
                $images->reap($asset, __('Image generation timed out. Please try again.'));
                $imgReaped++;
                $imgFailed++;

                continue;
            }

            $status = $images->process($asset);

            if ($status === 'completed') {
                $imgCompleted++;
            } elseif ($status === 'failed') {
                $imgFailed++;
            }
        }

        $this->info("Videos — submitted: {$submitted}, polled: {$polled}, completed: {$completed}, failed: {$failed}, reaped: {$reaped}");
        $this->info("Images — completed: {$imgCompleted}, failed: {$imgFailed}, reaped: {$imgReaped}");

        return self::SUCCESS;
    }
}
