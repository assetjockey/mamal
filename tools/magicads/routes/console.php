<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| AI Studio generation (cron-driven)
|--------------------------------------------------------------------------
|
| All generation is cron-driven — no `queue:work` daemon required, so it
| runs on shared hosting and VPS alike. The host only needs Laravel's
| single scheduler cron entry:
|
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
| Each minute this advances every in-flight generation one step:
|   - Videos: submit new ones to the provider, poll those still rendering,
|     finalize the finished ones (overlay + credits), fail any that overran.
|   - Images: finish any that exceeded the synchronous in-request budget and
|     were deferred to the background (the common fast case completes inline
|     during the web request and never reaches the cron).
|
| `withoutOverlapping` stops two runs from touching the same job at once;
| `runInBackground` keeps the scheduler from blocking on it.
|
*/
Schedule::command('ai-studio:process-generations')
    ->everyMinute()
    ->withoutOverlapping(10)
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Avatar Studio generation (cron-driven)
|--------------------------------------------------------------------------
|
| Same cron-driven model as AI Studio: each minute this advances every
| in-flight HeyGen render one step (submit → poll → finalize).
|
| The command class ships INSIDE the "magicads-avatar-studio" plugin, so it
| only exists once the plugin is installed. We guard the schedule registration
| on the class being present — otherwise `schedule:run` would spawn a
| background `artisan avatar-studio:process` every minute that fails with
| "Command not defined". When the plugin is absent the schedule simply isn't
| registered; when it's installed it self-activates with no extra wiring.
|
*/
if (class_exists(\App\Console\Commands\AvatarStudio\ProcessAvatarStudioCommand::class)) {
    Schedule::command('avatar-studio:process')
        ->everyMinute()
        ->withoutOverlapping(10)
        ->runInBackground();
}

/*
|--------------------------------------------------------------------------
| UGC Factory generation (cron-driven)
|--------------------------------------------------------------------------
|
| Same cron-driven model as Avatar Studio: each minute this advances every
| in-flight fal.ai (VEED Fabric 1.0) render one step (submit → poll →
| finalize).
|
| The command class ships INSIDE the "magicads-ugc-factory" plugin, so it only
| exists once the plugin is installed. Guarding on the class being present
| avoids `schedule:run` spawning a failing background command every minute when
| the plugin is absent.
|
*/
if (class_exists(\App\Console\Commands\UgcFactory\ProcessUgcFactoryCommand::class)) {
    Schedule::command('ugc-factory:process')
        ->everyMinute()
        ->withoutOverlapping(10)
        ->runInBackground();
}

/*
|--------------------------------------------------------------------------
| Social Media Studio publishing (cron-driven)
|--------------------------------------------------------------------------
|
| Each minute this publishes scheduled posts that are due and advances the
| auto-repost cadence (the worker re-arms each target's next run after a
| successful publish). Immediate posts are published inline during the web
| request and never reach the cron.
|
| The command ships INSIDE the "magicads-social-media-studio" plugin, so we
| guard on the class existing — when the plugin is absent the schedule simply
| isn't registered; when installed it self-activates with no extra wiring.
|
*/
if (class_exists(\App\Console\Commands\SocialMediaStudio\PublishDueCommand::class)) {
    Schedule::command('social-media-studio:publish-due')
        ->everyMinute()
        ->withoutOverlapping(10)
        ->runInBackground();
}

/*
|--------------------------------------------------------------------------
| Channel Broadcast delivery (cron-driven)
|--------------------------------------------------------------------------
|
| Each minute this delivers scheduled broadcasts that are due and advances the
| recurring cadence (the worker re-arms each target's next run after a
| successful send). Immediate broadcasts are delivered inline during the web
| request and never reach the cron.
|
| Ships INSIDE the "magicads-channel-broadcast" plugin, so we guard on the
| class existing — absent when the plugin isn't installed, self-activating when
| it is.
|
*/
if (class_exists(\App\Console\Commands\ChannelBroadcast\BroadcastDueCommand::class)) {
    Schedule::command('channel-broadcast:send-due')
        ->everyMinute()
        ->withoutOverlapping(10)
        ->runInBackground();
}

/*
|--------------------------------------------------------------------------
| Ad Performance Analytics sync (cron-driven)
|--------------------------------------------------------------------------
|
| Hourly, this queues a metrics sync for every active ad account whose
| per-account throttle window has passed (Meta, Google Ads, TikTok). Daily, it
| prunes normalized rows older than the retention window. Syncing is idempotent
| (rows are upserted on their natural grain), so a missed run self-heals on the
| next tick.
|
| The command ships INSIDE the "magicads-ad-performance-analytics" plugin, so
| we guard on the class existing — absent when the plugin isn't installed,
| self-activating when it is. Relies on the host cron:
|   * * * * * php artisan schedule:run
|
*/
if (class_exists(\App\Console\Commands\AdAnalytics\SyncAdAnalyticsCommand::class)) {
    Schedule::command('ad-analytics:sync')
        ->hourly()
        ->withoutOverlapping(30)
        ->runInBackground();

    Schedule::command('ad-analytics:sync --prune')
        ->dailyAt('03:30')
        ->withoutOverlapping(30)
        ->runInBackground();
}
