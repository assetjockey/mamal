<?php

namespace App\Console\Commands;

use App\Models\Monitor;
use App\Services\MonitorAlertService;
use Carbon\Carbon;
use Exception;
use GeoIp2\Database\Reader as GeoIP;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckMonitorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:check-monitors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the monitor statuses';

    /**
     * Execute the console command.
     */
    public function handle(MonitorAlertService $monitorAlertService): int
    {
        $cronStartTimestamp = Carbon::now()->startOfMinute()->timestamp;

        $requestIp = getRequestIp();

        try {
            $geoip = (new GeoIP(storage_path('app/geoip/GeoLite2-City.mmdb')))->city($requestIp);

            $country = $geoip->country->isoCode.':'.$geoip->country->name;
            $city = $geoip->country->isoCode.':'. $geoip->city->name . (isset($geoip->mostSpecificSubdivision->isoCode) ? ', '.$geoip->mostSpecificSubdivision->isoCode : '');
        } catch (Exception) {
            $country = null;
            $city = null;
        }

        Monitor::with('user.plan')
            ->where('status', '!=', 'paused')
            ->where(function ($query) {
                $query->whereNull('maintenance_start_at')
                    ->orWhereNull('maintenance_end_at')
                    ->orWhere(function ($nestedQuery) {
                        $nestedQuery->where('maintenance_start_at', '>', Carbon::now())
                            ->orWhere('maintenance_end_at', '<', Carbon::now());
                    });
            })
            ->chunk(config('settings.request_simultaneous_requests'), function ($monitors) use ($monitorAlertService, $country, $city, $cronStartTimestamp, $requestIp)
            {
                $monitorRequestClient = new GuzzleClient();
                $monitorRequestStats = [];
                $checkInsertions = [];
                $monitorUpdates = [];
                $incidentInsertions = [];
                $incidentUpdates = [];
                $offlineMonitors = [];

                $monitorRequestPromises = $this->buildAsyncRequests($monitorRequestClient, $monitors, $monitorRequestStats, $cronStartTimestamp);
                $monitorResponses = Utils::settle($monitorRequestPromises)->wait();

                $this->processMonitorResponses($monitorResponses, $monitors, $monitorRequestStats, $monitorAlertService, $country, $city, $requestIp, $monitorUpdates, $checkInsertions, $incidentInsertions, $incidentUpdates, $offlineMonitors, false);

                if (count($offlineMonitors)) {
                    sleep(config('settings.monitors_double_check_delay_seconds'));

                    $monitorRequestPromises = $this->buildAsyncRequests($monitorRequestClient, collect($offlineMonitors), $monitorRequestStats, $cronStartTimestamp);
                    $monitorResponses = Utils::settle($monitorRequestPromises)->wait();

                    $this->processMonitorResponses($monitorResponses, $monitors, $monitorRequestStats, $monitorAlertService, $country, $city, $requestIp, $monitorUpdates, $checkInsertions, $incidentInsertions, $incidentUpdates, $offlineMonitors, true);
                }

                // If there are monitors to be updated
                if (!empty($monitorUpdates)) {
                    $updateMonitorsQuery = 'UPDATE `monitors` SET `status` = CASE `id` ' . implode(' ', array_map(function ($id) { return 'WHEN ' . $id . ' THEN ?'; }, array_keys($monitorUpdates))) . ' END, `checked_at` = CASE `id` ' . implode(' ', array_map(function ($id) { return 'WHEN ' . $id . ' THEN ?'; }, array_keys($monitorUpdates))) . ' END';

                    $bindings = array_merge(
                        array_column($monitorUpdates, 'status'),
                        array_column($monitorUpdates, 'checked_at')
                    );

                    $startedAtUpdates = array_filter($monitorUpdates, function ($update) { return isset($update['started_at']); });
                    if (!empty($startedAtUpdates)) {
                        $updateMonitorsQuery .= ', `started_at` = CASE `id` ' . implode(' ', array_map(function ($id) { return 'WHEN ' . $id . ' THEN ?'; }, array_keys($startedAtUpdates))) . ' END';
                        $bindings = array_merge($bindings, array_column($startedAtUpdates, 'started_at'));
                    }

                    $monitorTokenUpdates = array_filter($monitorUpdates, function ($update) { return isset($update['token']); });
                    if (!empty($monitorTokenUpdates)) {
                        $updateMonitorsQuery .= ', `token` = CASE `id` ' . implode(' ', array_map(function ($id) { return 'WHEN ' . $id . ' THEN ?'; }, array_keys($monitorTokenUpdates))) . ' END';
                        $bindings = array_merge($bindings, array_column($monitorTokenUpdates, 'token'));
                    }

                    $updateMonitorsQuery .= " WHERE `id` IN (" . implode(',', array_keys($monitorUpdates)) . ")";

                    DB::update($updateMonitorsQuery, $bindings);
                }

                // If there are incidents to be added
                if (!empty($incidentInsertions)) {
                    DB::table('incidents')->insert($incidentInsertions);
                }

                // If there are incidents to be updated
                if (!empty($incidentUpdates)) {
                    $updateIncidentsQuery = 'UPDATE `incidents` SET `ended_at` = CASE `monitor_id` ' . implode(' ', array_map(function ($id) { return 'WHEN ' . $id . ' THEN ?'; }, array_keys($incidentUpdates))) . ' END WHERE `monitor_id` IN (' . implode(',', array_keys($incidentUpdates)) . ') AND `ended_at` IS NULL';

                    $bindings = array_merge(
                        array_column($incidentUpdates, 'ended_at'),
                    );

                    DB::update($updateIncidentsQuery, $bindings);
                }

                if (!empty($checkInsertions)) {
                    DB::table('checks')->insert($checkInsertions);
                }

                $monitorAlertService->process($monitorUpdates);
            });

        return 0;
    }

    /**
     * Process monitor responses and update the relevant data structures.
     */
    private function processMonitorResponses(array $monitorResponses, Collection $monitors, array &$monitorRequestStats, MonitorAlertService $monitorAlertService, ?string $country, ?string $city, ?string $requestIp, array &$monitorUpdates, array &$checkInsertions, array &$incidentInsertions, array &$incidentUpdates, array &$offlineMonitors, bool $isDoubleCheck = false): void
    {
        foreach ($monitorResponses as $id => $monitorResponse) {
            $monitor = $monitors->find($id);

            [$monitorResponseStatusCode, $monitorResponseTotalTime, $monitorResponseErrorMessage, $monitorStatus] = $this->processMonitorResponse($monitor, $monitorResponse, $monitorRequestStats);

            $oldMonitor = (clone $monitor);
            $now = Carbon::now();

            $checkInsertions[] = $this->buildCheckInsertPayload($monitor, $monitorResponseTotalTime, $monitorResponseStatusCode, $country, $city, $now);

            if ($monitorStatus === 'offline' && $oldMonitor->status !== 'offline' && config('settings.monitors_double_check') && !$isDoubleCheck) {
                $offlineMonitors[] = $monitor;
                continue;
            }

            $monitorMustSendAlerts = null;

            // If the monitor becomes online from an offline state, or if the monitor becomes offline from an online state
            if (($monitorStatus === 'online' && $oldMonitor->status !== 'online') || ($monitorStatus === 'offline' && $oldMonitor->status !== 'offline')) {
                $monitorStartedAt = $now;
                $monitorToken = Str::random(16);
                $monitorMustSendAlerts = true;
            } else {
                $monitorStartedAt = $oldMonitor->started_at;
                $monitorToken = $oldMonitor->token;
            }

            $monitorAlerts = new Collection();
            if ($monitorMustSendAlerts) {
                $monitorAlerts = $monitorAlertService->getMonitorAlertsUserPlanLimited($monitor);
            }

            $monitorUpdates[$monitor->id] = $this->updateMonitorUpdatePayload($monitor, $monitorAlerts, $monitorToken, $monitorStatus, $now, $monitorStartedAt);

            if ($monitorStatus === 'offline' && in_array($oldMonitor->status, ['pending', 'online'])) {
                $incidentInsertions[] = $this->buildIncidentInsertPayload($monitor, $monitorAlerts, $monitorToken, $monitorResponseErrorMessage, $monitorResponseStatusCode, $country, $city, $requestIp, $now);
            }

            if ($monitorStatus === 'online' && in_array($oldMonitor->status, ['offline', 'pending'])) {
                $incidentUpdates[$monitor->id] = ['ended_at' => $now];
            }
        }
    }

    /**
     * Process the HTTP response of a monitor and determine its status.
     */
    private function processMonitorResponse(Monitor $monitor, array $monitorResponse, array &$monitorRequestStats): array
    {
        $monitorResponseStatusCode = 0;
        $monitorResponseTotalTime = 0;
        $monitorResponseErrorMessage = null;
        $monitorStatus = 'offline';

        if ($monitorResponse['state'] == 'fulfilled') {
            $monitorResponseStatusCode = $monitorResponse['value']->getStatusCode();
            $monitorResponseTotalTime = $monitorRequestStats[$monitor->id];

            if (in_array($monitor->alert_condition, ['url_text', 'url_no_text'])) {
                $monitorRequestBody = $monitorResponse['value']->getBody()->getContents() ?? null;

                // If the monitor is set to alert when a given text is present in the response
                if ($monitor->alert_condition == 'url_text') {
                    if (!Str::contains($monitorRequestBody, $monitor->alert_text_lookup)) {
                        $monitorStatus = 'online';
                    } else {
                        $monitorResponseStatusCode = 403;
                        $monitorResponseErrorMessage = __('The expected text was present in the response body.');
                    }
                }
                // If the monitor is set to alert when a given text is not present in the response
                else {
                    if (Str::contains($monitorRequestBody, $monitor->alert_text_lookup)) {
                        $monitorStatus = 'online';
                    } else {
                        $monitorResponseStatusCode = 404;
                        $monitorResponseErrorMessage = __('The expected text was not present in the response body.');
                    }
                }
            } else {
                if ($monitorResponseStatusCode >= 200 && $monitorResponseStatusCode <= 299) {
                    $monitorStatus = 'online';
                }
            }
        } elseif ($monitorResponse['state'] == 'rejected') {
            // Handle connection failures (DNS issues, timeouts)
            if ($monitorResponse['reason'] instanceof ConnectException) {
                $monitorResponseErrorMessage = $monitorResponse['reason']->getMessage();
            }
            // Handle other HTTP request failures
            elseif ($monitorResponse['reason'] instanceof RequestException) {
                $monitorResponseStatusCode = $monitorResponse['reason']->getResponse()?->getStatusCode() ?? 0;
                $monitorResponseErrorMessage = $monitorResponse['reason']->getMessage();
            }
        }

        return [
            $monitorResponseStatusCode,
            $monitorResponseTotalTime,
            $monitorResponseErrorMessage,
            $monitorStatus,
        ];
    }

    /**
     * Build the check insert payload data of a monitor check.
     */
    private function buildCheckInsertPayload(Monitor $monitor, int $monitorResponseTotalTime, int $monitorResponseStatusCode, ?string $country, ?string $city, Carbon $now): array
    {
        return [
            'monitor_id' => $monitor->id,
            'request_method' => $monitor->request_method,
            'response_time' => $monitorResponseTotalTime,
            'response_status_code'=> $monitorResponseStatusCode,
            'country' => $country,
            'city' => $city,
            'checked_at' => $now,
        ];
    }

    /**
     * Build the monitor payload data to be updated when the monitor status changes.
     */
    private function updateMonitorUpdatePayload(Monitor $monitor, Collection $monitorAlerts, ?string $monitorToken, string $monitorStatus, Carbon $now, Carbon $monitorStartedAt): array
    {
        $monitorUpdates = [
            'token' => $monitorToken,
            'status' => $monitorStatus,
            'checked_at' => $now,
            'started_at' => $monitorStartedAt,
        ];

        if (count($monitorAlerts) > 0) {
            $monitorUpdates = array_merge($monitorUpdates, [
                'id' => $monitor->id,
                'name' => $monitor->name,
                'url' => $monitor->url,
                'alerts' => $monitorAlerts,
                'user' => $monitor->user,
                'alert' => (object) [
                    'error' => $monitorStatus == 'offline',
                    'type' => 'http'
                ]
            ]);
        }

        return $monitorUpdates;
    }

    /**
     * Build the incident payload data to be inserted when a monitor transitions to an offline state.
     */
    private function buildIncidentInsertPayload(Monitor $monitor, Collection $monitorAlerts, ?string $monitorToken, ?string $monitorResponseErrorMessage, ?string $monitorResponseStatusCode, ?string $country, ?string $city, ?string $requestIp, Carbon $now): array
    {
        return [
            'monitor_id' => $monitor->id,
            'user_id' => $monitor->user_id,
            'url' => $monitor->url,
            'cause' => $monitorResponseErrorMessage ? mb_substr($monitorResponseErrorMessage, 0, 255) : (isset(config('requests.status.codes')[$monitorResponseStatusCode]) ? $monitorResponseStatusCode . ' ' . config('requests.status.codes')[$monitorResponseStatusCode] : ''),
            'alerted' => json_encode($monitorAlerts->toArray()),
            'country' => $country,
            'city' => $city,
            'check_ip' => $requestIp,
            'token' => Str::random(16),
            'monitor_token'=> $monitorToken,
            'started_at' => $now,
        ];
    }

    /**
     * Build the asynchronous HTTP requests used to check the monitor availability.
     */
    private function buildAsyncRequests(GuzzleClient $monitorRequestClient, Collection $monitors, array &$monitorRequestStats, $cronStartTimestamp): array {
        $monitorRequestPromises = [];

        foreach ($monitors as $monitor) {
            // Skip the monitor if the cron start timestamp is not even divisible with the interval
            if ($cronStartTimestamp % $monitor->interval !== 0) {
                continue;
            }

            $requestHeaders = [];
            $requestHeaders['User-Agent'] = config('settings.request_user_agent');
            $requestHeaders['Accept'] = 'text/html,application/xhtml+xml,application/xml,application/json;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8';
            $requestHeaders['Accept-Encoding'] = 'gzip, deflate';

            if ($monitor->request_headers) {
                foreach ($monitor->request_headers as $header) {
                    $requestHeaders[mb_strtolower($header->key)] = $header->value;
                }
            }

            if ($monitor->request_auth_username && $monitor->request_auth_password) {
                $requestAuth = [$monitor->request_auth_username, $monitor->request_auth_password];
            } else {
                $requestAuth = null;
            }

            $monitorRequestPromises[$monitor->id] = $monitorRequestClient->requestAsync($monitor->request_method, ($monitor->cache_buster ? $this->appendUrlParams($monitor->url, [Str::random(16) => true]) : $monitor->url), [
                'http_errors' => false,
                'timeout' => config('settings.request_timeout'),
                'proxy' => [
                    'http' => getRequestProxy(),
                    'https' => getRequestProxy()
                ],
                'auth' => $requestAuth,
                'allow_redirects' => [
                    'max' => 6,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['http', 'https'],
                    'track_redirects' => true
                ],
                'headers' => $requestHeaders,
                'verify' => false,
                'on_stats' => function (TransferStats $stats) use (&$monitorRequestPromises, &$monitorRequestStats, $monitor) {
                    $monitorRequestStats[$monitor->id] = $stats->getHandlerStats()['total_time'] ? $stats->getHandlerStats()['total_time'] * 1000000 : 0;
                }
            ]);
        }

        return $monitorRequestPromises;
    }

    /**
     * Append URL parameters to a URL string.
     */
    function appendUrlParams(string $url, array $newParams = []): string
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $params = [];
        if (!is_null($query)) {
            parse_str($query, $params);
        }

        $params = array_merge($params, $newParams);
        $queryString = http_build_query($params);

        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $user = parse_url($url, PHP_URL_USER);
        $pass = parse_url($url, PHP_URL_PASS);
        $path = parse_url($url, PHP_URL_PATH);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        $schemePart = $scheme ? $scheme . '://' : '';
        $authPart   = $user ? $user . ($pass ? ':' . $pass : '') . '@' : '';
        $portPart   = $port ? ':' . $port : '';
        $fragmentPart = $fragment ? '#' . $fragment : '';

        return $schemePart . $authPart . $host . $portPart . $path . ($queryString ? '?' . $queryString : '') . $fragmentPart;
    }
}
