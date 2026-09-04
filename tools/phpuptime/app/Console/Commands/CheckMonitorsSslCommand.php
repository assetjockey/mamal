<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Models\User;
use App\Services\MonitorAlertService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Spatie\SslCertificate\SslCertificate;

class CheckMonitorsSslCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:check-monitors-ssl';

    /**
     * The console command description.
     */
    protected $description = 'Check the monitor SSL certificates';

    /**
     * Execute the console command.
     */
    public function handle(MonitorAlertService $monitorAlertService): int
    {
        Monitor::with('user.plan')
            ->where([['status', '!=', 'paused'], ['ssl_alert_days', '>', 0]])
            ->where(function ($query) {
                $query->whereNull('maintenance_start_at')
                    ->orWhereNull('maintenance_end_at')
                    ->orWhere(function ($nestedQuery) {
                        $nestedQuery->where('maintenance_start_at', '>', Carbon::now())
                            ->orWhere('maintenance_end_at', '<', Carbon::now());
                    });
            })
            ->chunk(config('settings.request_simultaneous_requests'), function ($monitors) use ($monitorAlertService) {
                $sslAlerts = [];

                foreach ($monitors as $monitor) {
                    $now = Carbon::now();

                    if ($monitor->user->can('sslMonitoring', [User::class]) && parse_url($monitor->url, PHP_URL_SCHEME) == 'https') {
                        try {
                            $sslCertificate = SslCertificate::download()
                                ->setTimeout(config('settings.request_timeout'))
                                ->withVerifyPeerName(false)
                                ->withVerifyPeer(false)
                                ->forHost($monitor->url);

                            if ($sslCertificate->validFromDate() && $sslCertificate->expirationDate()) {
                                if ($monitor->ssl_created_at && $monitor->ssl_ends_at) {
                                    if (!$monitor->ssl_created_at->equalTo($sslCertificate->validFromDate())
                                        || !$monitor->ssl_ends_at->equalTo($sslCertificate->expirationDate())) {
                                        $monitor->ssl_alerted_at = null;
                                    }
                                }

                                $monitor->ssl_information = [
                                    'issuer' => $sslCertificate->getIssuer(),
                                    'organization' => $sslCertificate->getOrganization(),
                                    'signature_algorithm' => $sslCertificate->getSignatureAlgorithm(),
                                    'domain' => $sslCertificate->getDomain(),
                                ];
                                $monitor->ssl_created_at = $sslCertificate->validFromDate();
                                $monitor->ssl_ends_at = $sslCertificate->expirationDate();

                                if ($now->between($monitor->ssl_created_at, $monitor->ssl_ends_at) && $now->diffInDays($monitor->ssl_ends_at, false) > 0 && $now->diffInDays($monitor->ssl_ends_at, false) <= $monitor->ssl_alert_days) {
                                    if (!$monitor->ssl_alerted_at || $monitor->ssl_alerted_at->diffInDays($monitor->ssl_ends_at) > $monitor->ssl_alert_days) {
                                        $monitor->token = Str::random(16);
                                        $monitor->ssl_alerted_at = $now;

                                        $sslAlerts[$monitor->id] = [
                                            'id' => $monitor->id,
                                            'name' => $monitor->name,
                                            'url' => $monitor->url,
                                            'alerts' => $monitorAlertService->getMonitorAlertsUserPlanLimited($monitor),
                                            'ssl_checked_at' => $now,
                                            'ssl_alerted_at' => $monitor->ssl_alerted_at,
                                            'ssl_created_at' => $monitor->ssl_created_at,
                                            'ssl_ends_at' => $monitor->ssl_ends_at,
                                            'token' => $monitor->token,
                                            'user' => $monitor->user,
                                            'alert' => (object) [
                                                'error' => true,
                                                'type' => 'ssl'
                                            ]
                                        ];
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $monitor->ssl_information = [
                                'error_message' => $e->getMessage()
                            ];
                            $monitor->ssl_alerted_at = null;
                            $monitor->ssl_created_at = null;
                            $monitor->ssl_ends_at = null;
                        }

                        $monitor->ssl_checked_at = $now;
                        $monitor->save();
                    }
                }

                $monitorAlertService->process($sslAlerts);
            });

        return 0;
    }
}
