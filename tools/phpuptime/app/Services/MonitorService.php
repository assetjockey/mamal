<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Monitor;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Iodev\Whois\Factory as WhoisFactory;
use Pdp\Domain as PdpDomain;
use Pdp\Rules as PdpRules;
use Spatie\SslCertificate\SslCertificate;

class MonitorService
{
    /**
     * Store the monitor.
     */
    public function store(array $data): Monitor
    {
        $now = Carbon::now();

        $monitor = new Monitor;

        $monitor->user_id = Auth::user()->id;
        $monitor->name = $data['name'];
        $monitor->url = $data['url'];
        $monitor->interval = $data['interval'];
        $monitor->alert_condition = $data['alert_condition'];
        $monitor->request_method = $data['request_method'];

        if (array_key_exists('maintenance_start_at', $data) && $data['maintenance_start_at']) {
            $monitor->maintenance_start_at = Carbon::parse($data['maintenance_start_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'))->toDateTimeString();
        } else {
            $monitor->maintenance_start_at = null;
        }

        if (array_key_exists('maintenance_end_at', $data) && $data['maintenance_end_at']) {
            $monitor->maintenance_end_at = Carbon::parse($data['maintenance_end_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'))->toDateTimeString();
        } else {
            $monitor->maintenance_end_at = null;
        }

        if (array_key_exists('alert_text_lookup', $data)) {
            $monitor->alert_text_lookup = $data['alert_text_lookup'];
        } else {
            $monitor->alert_text_lookup = null;
        }

        if (array_key_exists('request_auth_username', $data)) {
            $monitor->request_auth_username = $data['request_auth_username'];
        } else {
            $monitor->request_auth_username = null;
        }

        if (array_key_exists('request_auth_password', $data)) {
            $monitor->request_auth_password = $data['request_auth_password'];
        } else {
            $monitor->request_auth_password = null;
        }

        if (array_key_exists('request_headers', $data)) {
            $monitor->request_headers = array_filter(is_array($data['request_headers']) ? $data['request_headers'] : [], function ($item) { return isset($item['key'], $item['value']); });
        } else {
            $monitor->request_headers = null;
        }

        if (array_key_exists('cache_buster', $data)) {
            $monitor->cache_buster = $data['cache_buster'];
        } else {
            $monitor->cache_buster = 0;
        }

        if (array_key_exists('alerts', $data)) {
            $monitor->alerts = array_filter(is_array($data['alerts']) ? $data['alerts'] : [], function ($item) { return isset($item['key'], $item['value']); });
        } else {
            $monitor->alerts = null;
        }

        if (array_key_exists('ssl_alert_days', $data)) {
            $monitor->ssl_alert_days = $data['ssl_alert_days'];
        } else {
            $monitor->ssl_alert_days = 0;
        }

        if (array_key_exists('domain_alert_days', $data)) {
            $monitor->domain_alert_days = $data['domain_alert_days'];
        } else {
            $monitor->domain_alert_days = 0;
        }

        $this->updateSslInformation($monitor, $now);
        $this->updateDomainInformation($monitor, $now);

        $monitor->started_at = $now;

        $monitor->save();

        return $monitor;
    }

    /**
     * Update the monitor.
     */
    public function update(Monitor $monitor, array $data): Monitor
    {
        $now = Carbon::now();

        if (array_key_exists('name', $data)) {
            $monitor->name = $data['name'];
        }

        if (array_key_exists('interval', $data)) {
            $monitor->interval = $data['interval'];
        }

        if (array_key_exists('ssl_alert_days', $data)) {
            $monitor->ssl_alert_days = $data['ssl_alert_days'];
        }

        if (array_key_exists('domain_alert_days', $data)) {
            $monitor->domain_alert_days = $data['domain_alert_days'];
        }

        if (array_key_exists('url', $data)) {
            $monitor->url = $data['url'];

            $this->updateSslInformation($monitor, $now);
            $this->updateDomainInformation($monitor, $now);
        }

        if (array_key_exists('maintenance_start_at', $data)) {
            $monitor->maintenance_start_at = $data['maintenance_start_at'] ? Carbon::parse($data['maintenance_start_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'))->toDateTimeString() : null;
        }

        if (array_key_exists('maintenance_end_at', $data)) {
            $monitor->maintenance_end_at = $data['maintenance_end_at'] ? Carbon::parse($data['maintenance_end_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'))->toDateTimeString() : null;
        }

        if (array_key_exists('alert_condition', $data)) {
            $monitor->alert_condition = $data['alert_condition'];
        }

        if (array_key_exists('alert_text_lookup', $data)) {
            $monitor->alert_text_lookup = $data['alert_text_lookup'];
        }

        if (array_key_exists('request_method', $data)) {
            $monitor->request_method = $data['request_method'];
        }

        if (array_key_exists('request_auth_username', $data)) {
            $monitor->request_auth_username = $data['request_auth_username'];
        }

        if (array_key_exists('request_auth_password', $data)) {
            $monitor->request_auth_password = $data['request_auth_password'];
        }

        if (array_key_exists('request_headers', $data)) {
            $monitor->request_headers = array_filter(is_array($data['request_headers']) ? $data['request_headers'] : [], function ($item) { return isset($item['key'], $item['value']); });
        }

        if (array_key_exists('cache_buster', $data)) {
            $monitor->cache_buster = $data['cache_buster'];
        }

        if (array_key_exists('alerts', $data)) {
            $monitor->alerts = array_filter(is_array($data['alerts']) ? $data['alerts'] : [], function ($item) { return isset($item['key'], $item['value']); });
        }

        if (array_key_exists('cache_buster', $data)) {
            $monitor->cache_buster = $data['cache_buster'];
        }

        if (array_key_exists('pause', $data)) {
            $monitor->status = $data['pause'] ? 'paused' : (Incident::where('monitor_id', '=', $monitor->id)
                ->whereNull('ended_at')
                ->orderBy('id', 'desc')
                ->exists() ? 'offline' : 'online');
            $monitor->started_at = $now;
            $monitor->checked_at = $now;
        }

        $monitor->save();

        return $monitor;
    }

    /**
     * Update the monitor's domain information.
     */
    private function updateDomainInformation(Monitor $monitor, Carbon $now): void
    {
        if ($monitor->domain_alert_days > 0) {
            try {
                $whoisFactory = WhoisFactory::get()->createWhois();

                $publicSuffixList = PdpRules::fromPath(storage_path('app/domains/list.dat'));
                $domain = PdpDomain::fromIDNA2008(parse_url($monitor->url, PHP_URL_HOST));

                $whoisDomainInfo = $whoisFactory->loadDomainInfo($publicSuffixList->resolve($domain)->registrableDomain()->toString());

                if ($whoisDomainInfo == null) {
                    throw new Exception('We couldn\'t fetch the domain name information.');
                }

                $monitor->domain_information = [
                    'domain_name' => $whoisDomainInfo->domainName ?? null,
                    'owner' => $whoisDomainInfo->owner ?? null,
                    'registrar' => $whoisDomainInfo->registrar ?? null,
                    'update_date' => $whoisDomainInfo->updateDate ?? null
                ];
                $monitor->domain_alerted_at = null;
                $monitor->domain_created_at = $whoisDomainInfo->creationDate ? Carbon::createFromTimestamp($whoisDomainInfo->creationDate) : null;
                $monitor->domain_ends_at = $whoisDomainInfo->expirationDate ? Carbon::createFromTimestamp($whoisDomainInfo->expirationDate) : null;
            } catch (Exception $e) {
                $monitor->domain_information = [
                    'error_message' => $e->getMessage()
                ];
                $monitor->domain_alerted_at = null;
                $monitor->domain_created_at = null;
                $monitor->domain_ends_at = null;
            }
            $monitor->domain_checked_at = $now;
        }
    }

    /**
     * Update the monitor's ssl information.
     */
    private function updateSslInformation(Monitor $monitor, Carbon $now): void
    {
        if ($monitor->ssl_alert_days > 0) {
            try {
                $sslCertificate = SslCertificate::download()
                    ->setTimeout(config('settings.request_timeout'))
                    ->withVerifyPeerName(false)
                    ->withVerifyPeer(false)
                    ->forHost($monitor->url);

                $monitor->ssl_information = [
                    'issuer' => $sslCertificate->getIssuer(),
                    'organization' => $sslCertificate->getOrganization(),
                    'signature_algorithm' => $sslCertificate->getSignatureAlgorithm(),
                    'domain' => $sslCertificate->getDomain(),
                ];
                $monitor->ssl_alerted_at = null;
                $monitor->ssl_created_at = $sslCertificate->validFromDate();
                $monitor->ssl_ends_at = $sslCertificate->expirationDate();
            } catch (Exception $e) {
                $monitor->ssl_information = [
                    'error_message' => $e->getMessage()
                ];
                $monitor->ssl_alerted_at = null;
                $monitor->ssl_created_at = null;
                $monitor->ssl_ends_at = null;
            }
            $monitor->ssl_checked_at = $now;
        }
    }
}