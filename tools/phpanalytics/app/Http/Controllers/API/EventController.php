<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GeoIp2\Database\Reader as GeoIP;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\IpUtils;
use WhichBrowser\Parser as UserAgent;

class EventController extends Controller
{
    /**
     * The tracking mechanism.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|void
     */
    public function index(Request $request)
    {
        $page = $this->parseUrl($request->input('page'));

        $website = DB::table('websites')
            ->select(['websites.id', 'websites.domain', 'websites.user_id', 'websites.exclude_bots', 'websites.exclude_ips', 'websites.exclude_params', 'users.can_track_websites'])
            ->join('users', 'users.id', '=', 'websites.user_id')
            ->where('websites.domain', '=', $page['non_www_host'] ?? null)
            ->first();

        // If the user's account exceeded the limit
        if (isset($website->can_track_websites) && !$website->can_track_websites) {
            return response(403);
        }

        // If the website does not exist
        if (isset($website->can_track_websites) == false) {
            return response(404);
        }

        // If the website has any excluded IPs
        if ($website->exclude_ips) {
            $excludedIps = preg_split('/\n|\r/', $website->exclude_ips, -1, PREG_SPLIT_NO_EMPTY);

            if (IpUtils::checkIp($request->ip(), $excludedIps)) {
                return response(403);
            }
        }

        $ua = new UserAgent(getallheaders());

        // If the website is excluding bots
        if ($website->exclude_bots) {
            if ($ua->device->type == 'bot') {
                return response(403);
            }
        }

        $now = Carbon::now();

        if ($request->input('event')) {
            $event = str_replace(':', ' ', $request->input('event'));

            if (isset($event['name']) && strlen($event['name']) <= 64) {
                $data['value'][] = $event['name'];
            } else {
                return;
            }

            if (isset($event['value']) && is_numeric($event['value']) && strlen($event['value']) <= 24) {
                $data['value'][] = trim($event['value']);
            }

            if (isset($event['unit']) && strlen($event['unit']) <= 32) {
                $data['value'][] = $event['unit'];
            }

            DB::statement("INSERT INTO `events` (`website_id`, `value`, `created_at`) VALUES (:website_id, :value, :created_at)", ['website_id' => $website->id, 'value' => implode(':', $data['value']), 'created_at' => $now]);
        } else {
            // If the request has a referrer that's not an internal page
            $referrer = $this->parseUrl($request->input('referrer'));

            // Parse the query data
            parse_str($page['query'] ?? null, $params);

            // If the website has any excluded query parameters
            if ($website->exclude_params) {
                $excludeQueries = preg_split('/\n|\r/', $website->exclude_params, -1, PREG_SPLIT_NO_EMPTY);

                // If a match all rule is set
                if (in_array('&', $excludeQueries)) {
                    // Remove all parameters
                    $page['query'] = null;
                } else {
                    foreach ($excludeQueries as $param) {
                        // If the excluded parameter exists
                        if (isset($params[$param])) {
                            // Remove the excluded parameter
                            unset($params[$param]);
                        }
                    }

                    // Rebuild the query parameters
                    $page['query'] = http_build_query($params);
                }
            }

            // Add the page
            $data['page'] = mb_substr((isset($page['query']) && !empty($page['query']) ? $page['path'].'?'.$page['query'] : $page['path'] ?? '/'), 0, 255);

            // Get the user's geolocation
            try {
                $geoip = (new GeoIP(storage_path('app/geoip/GeoLite2-City.mmdb')))->city($request->ip());

                $continent = $geoip->continent->code.':'.$geoip->continent->name;
                $country = $geoip->country->isoCode.':'.$geoip->country->name;
                $city = $geoip->country->isoCode.':'. $geoip->city->name .(isset($geoip->mostSpecificSubdivision->isoCode) ? ', '.$geoip->mostSpecificSubdivision->isoCode : '');
            } catch (\Exception $e) {
                $continent = $country = $city = null;
            }

            $browser = mb_substr($ua->browser->name ?? null, 0, 64);
            $os = mb_substr($ua->os->name ?? null, 0, 64);
            $device = mb_substr($ua->device->type ?? null, 0, 64);
            $language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $screenResolution = $request->input('screen_resolution') ?? null;

            $data['campaign'] = null;
            // Add the UTM campaign
            if (isset($params['utm_campaign']) && !empty($params['utm_campaign'])) {
                $data['campaign'] = $params['utm_campaign'];
            }

            // Add the continent
            $data['continent'] = $continent;

            // Add the country
            $data['country'] = $country;

            // Add the city
            $data['city'] = $city;

            // Add the browser
            $data['browser'] = $browser;

            // Add the OS
            $data['operating_system'] = $os;

            // Add the device
            $data['device'] = $device;

            // Add the language
            $data['language'] = $language;

            // Add the theme
            $data['theme'] = $request->input('theme');

            // Add the screen-resolution
            $data['screen_resolution'] = $screenResolution;

            // Add the referrer
            $data['referrer'] = $referrer['host'] ?? null;

            // Add the unique
            $data['unique'] = !isset($referrer['non_www_host']) || $referrer['non_www_host'] != $website->domain ? 1 : 0;

            DB::statement("INSERT INTO `stats` (`website_id`, `unique`, `referrer`, `page`, `operating_system`, `browser`, `screen_resolution`, `device`, `country`, `continent`, `city`, `language`, `campaign`, `created_at`) VALUES (:website_id, :unique, :referrer, :page, :operating_system, :browser, :screen_resolution, :device, :country, :continent, :city, :language, :campaign, :created_at)", ['website_id' => $website->id, 'unique' => $data['unique'],'referrer' => $data['referrer'] ? mb_substr($data['referrer'], 0, 255) : null, 'page' => $data['page'] ? mb_substr($data['page'], 0, 255) : null, 'operating_system' => $data['operating_system'] ? mb_substr($data['operating_system'], 0, 64) : null, 'browser' => $data['browser'] ? mb_substr($data['browser'], 0, 64) : null, 'screen_resolution' => $data['screen_resolution'] ? mb_substr($data['screen_resolution'], 0, 16) : null, 'device' => $data['device'] ? mb_substr($data['device'], 0, 64) : null, 'country' => $data['country'] ? mb_substr($data['country'], 0, 64) : null, 'continent' => $data['continent'] ? mb_substr($data['continent'], 0, 16) : null, 'city' => $data['city'] ? mb_substr($data['city'], 0, 128) : null, 'language' => $data['language'] ? mb_substr($data['language'], 0, 2) : null, 'campaign' => $data['campaign'] ? mb_substr($data['campaign'], 0, 64) : null, 'created_at' => $now]);
        }

        return response(200);
    }

    /**
     * Returns the parsed URL, including an always "non-www." version of the host.
     *
     * @param $url
     * @return mixed|null
     */
    private function parseUrl($url)
    {
        $url = parse_url($url);

        // If the URL has a host
        if (isset($url['host'])) {
            // If the URL starts with "www."
            if(substr($url['host'], 0, 4 ) == "www.") {
                $url['non_www_host'] = str_replace('www.', '', $url['host']);
            } else {
                $url['non_www_host'] = $url['host'];
            }
            return $url;
        } else {
            return null;
        }
    }
}
