<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Models\User;
use App\Services\MonitorAlertService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Iodev\Whois\Factory as WhoisFactory;
use Pdp\Rules as PdpRules;
use Pdp\Domain as PdpDomain;

class CheckMonitorsDomainCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cron:check-monitors-domain';

    /**
     * The console command description.
     */
    protected $description = 'Check the monitor domain names';

    /**
     * Execute the console command.
     */
    public function handle(MonitorAlertService $monitorAlertService): int
    {
        $publicSuffixList = PdpRules::fromPath(storage_path('app/domains/list.dat'));

        Monitor::with('user.plan')
            ->where([['status', '!=', 'paused'], ['domain_alert_days', '>', 0]])
            ->where(function ($query) {
                $query->whereNull('maintenance_start_at')
                    ->orWhereNull('maintenance_end_at')
                    ->orWhere(function ($nestedQuery) {
                        $nestedQuery->where('maintenance_start_at', '>', Carbon::now())
                            ->orWhere('maintenance_end_at', '<', Carbon::now());
                    });
            })
            ->chunk(config('settings.request_simultaneous_requests'), function ($monitors) use ($monitorAlertService, $publicSuffixList)
            {
                $domainAlerts = [];

                foreach ($monitors as $monitor) {
                    $now = Carbon::now();

                    if ($monitor->user->can('domainMonitoring', [User::class]) && parse_url($monitor->url, PHP_URL_SCHEME) == 'https') {
                        try {
                            $whoisFactory = WhoisFactory::get()->createWhois();

                            $domain = PdpDomain::fromIDNA2008(parse_url($monitor->url, PHP_URL_HOST));

                            $whoisDomainInfo = $whoisFactory->loadDomainInfo($publicSuffixList->resolve($domain)->registrableDomain()->toString());

                            if ($whoisDomainInfo->creationDate && $whoisDomainInfo->expirationDate) {
                                if ($monitor->domain_created_at && $monitor->domain_ends_at) {
                                    if (!$monitor->domain_created_at->equalTo(Carbon::createFromTimestamp($whoisDomainInfo->creationDate))
                                        || !$monitor->domain_ends_at->equalTo(Carbon::createFromTimestamp($whoisDomainInfo->expirationDate))) {
                                        $monitor->domain_alerted_at = null;
                                    }
                                }

                                $monitor->domain_information = [
                                    'domain_name' => $whoisDomainInfo->domainName ?? null,
                                    'owner' => $whoisDomainInfo->owner ?? null,
                                    'registrar' => $whoisDomainInfo->registrar ?? null,
                                    'update_date' => $whoisDomainInfo->updateDate ?? null
                                ];
                                $monitor->domain_created_at = Carbon::createFromTimestamp($whoisDomainInfo->creationDate);
                                $monitor->domain_ends_at = Carbon::createFromTimestamp($whoisDomainInfo->expirationDate);

                                if ($now->between($monitor->domain_created_at, $monitor->domain_ends_at) && $now->diffInDays($monitor->domain_ends_at, false) > 0 && $now->diffInDays($monitor->domain_ends_at, false) <= $monitor->domain_alert_days) {
                                    if (!$monitor->domain_alerted_at || $monitor->domain_alerted_at->diffInDays($monitor->domain_ends_at) > $monitor->domain_alert_days) {
                                        $monitor->token = Str::random(16);
                                        $monitor->domain_alerted_at = $now;

                                        $domainAlerts[$monitor->id] = [
                                            'id' => $monitor->id,
                                            'name' => $monitor->name,
                                            'url' => $monitor->url,
                                            'alerts' => $monitorAlertService->getMonitorAlertsUserPlanLimited($monitor),
                                            'domain_checked_at' => $now,
                                            'domain_alerted_at' => $monitor->domain_alerted_at,
                                            'domain_created_at' => $monitor->domain_created_at,
                                            'domain_ends_at' => $monitor->domain_ends_at,
                                            'token' => $monitor->token,
                                            'user' => $monitor->user,
                                            'alert' => (object) [
                                                'error' => true,
                                                'type' => 'domain'
                                            ]
                                        ];
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $monitor->domain_information = [
                                'error_message' => $e->getMessage()
                            ];
                            $monitor->domain_alerted_at = null;
                            $monitor->domain_created_at = null;
                            $monitor->domain_ends_at = null;
                        }

                        $monitor->domain_checked_at = $now;
                        $monitor->save();
                    }
                }

                $monitorAlertService->process($domainAlerts);
            });

        return 0;
    }
}
