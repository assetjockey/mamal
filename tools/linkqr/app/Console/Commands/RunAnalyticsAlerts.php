<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class RunAnalyticsAlerts extends Command
{
    protected $signature = 'analytics:alerts';

    protected $description = 'Check QR scan spikes, dead links, and custom domain SSL alerts.';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('analytics_alert_rules')) {
            $this->warn('analytics_alert_rules table does not exist.');

            return self::SUCCESS;
        }

        $rules = DB::table('analytics_alert_rules')
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            match ((string) $rule->metric) {
                'dead_link' => $this->checkDeadLinks($rule),
                'domain_ssl' => $this->checkDomainSsl($rule),
                default => $this->checkScanSpike($rule),
            };
        }

        return self::SUCCESS;
    }

    protected function checkScanSpike(object $rule): void
    {
        if (! DB::getSchemaBuilder()->hasTable('app_qr_scan_events')) {
            return;
        }

        $recent = DB::table('app_qr_scan_events')
            ->where('owner_user_id', $rule->owner_user_id)
            ->where('created_at', '>=', now()->subHour())
            ->count();
        $baseline = max(1, (int) DB::table('app_qr_scan_events')
            ->where('owner_user_id', $rule->owner_user_id)
            ->whereBetween('created_at', [now()->subDays(8), now()->subDay()])
            ->count() / (24 * 7));

        if ($recent >= max((int) $rule->threshold, $baseline * 3)) {
            $this->notify($rule, 'QR scan spike detected', "Recent scans: {$recent}. Hourly baseline: {$baseline}.");
        }
    }

    protected function checkDeadLinks(object $rule): void
    {
        foreach ($this->candidateUrls((int) $rule->owner_user_id)->take(30) as $url) {
            try {
                $response = Http::timeout(8)->head($url);
                if ($response->status() >= 400) {
                    $this->notify($rule, 'Dead campaign link detected', "{$url} returned HTTP {$response->status()}.");
                }
            } catch (\Throwable) {
                $this->notify($rule, 'Dead campaign link detected', "{$url} could not be reached.");
            }
        }
    }

    protected function checkDomainSsl(object $rule): void
    {
        if (! DB::getSchemaBuilder()->hasTable('custom_domains')) {
            return;
        }

        DB::table('custom_domains')
            ->where('owner_user_id', $rule->owner_user_id)
            ->where('status', 'verified')
            ->pluck('domain')
            ->each(function (string $domain) use ($rule): void {
                $client = @stream_socket_client('ssl://'.$domain.':443', $errno, $errstr, 4);

                if (is_resource($client)) {
                    fclose($client);

                    return;
                }

                $this->notify($rule, 'Custom domain SSL issue', "{$domain} is not accepting HTTPS connections.");
            });
    }

    protected function candidateUrls(int $ownerId): Collection
    {
        $urls = collect();

        if (DB::getSchemaBuilder()->hasTable('app_qr_codes')) {
            $urls = $urls->merge(DB::table('app_qr_codes')
                ->where('owner_user_id', $ownerId)
                ->where('status', 'active')
                ->pluck('destination_url'));
        }

        if (DB::getSchemaBuilder()->hasTable('link_bio_pages')) {
            DB::table('link_bio_pages')
                ->where('owner_user_id', $ownerId)
                ->pluck('blocks')
                ->each(function (?string $blocks) use (&$urls): void {
                    $decoded = json_decode((string) $blocks, true);
                    collect((array) $decoded)
                        ->flatMap(fn (array $block): array => (array) data_get($block, 'items', []))
                        ->pluck('url')
                        ->filter()
                        ->each(fn (string $url) => $urls->push($url));
                });
        }

        return $urls
            ->map(fn ($url): string => trim((string) $url))
            ->filter(fn (string $url): bool => str_starts_with($url, 'http'))
            ->unique()
            ->values();
    }

    protected function notify(object $rule, string $subject, string $body): void
    {
        $recipients = collect(json_decode((string) $rule->recipients, true))
            ->filter(fn ($email): bool => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $recipient) {
            Mail::raw($body, fn ($message) => $message->to($recipient)->subject($subject));
        }

        DB::table('analytics_alert_rules')
            ->where('id', $rule->id)
            ->update(['last_triggered_at' => now(), 'updated_at' => now()]);
    }
}
