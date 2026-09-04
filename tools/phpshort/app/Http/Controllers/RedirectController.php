<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Http\Requests\ValidateLinkRedirectPasswordRequest;
use App\Models\Link;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use GeoIp2\Database\Reader as GeoIP;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use WhichBrowser\Parser as UserAgent;

class RedirectController extends Controller
{
    /**
     * Handle the link redirect.
     */
    public function index(Request $request, string $id): RedirectResponse|View
    {
        $localHost = parse_url(config('app.url'), PHP_URL_HOST);
        $remoteHost = $request->getHost();

        $link = null;

        $domain = Domain::where('name', '=', $remoteHost)->first();

        if ($domain) {
            $link = Link::where([['alias', '=', $id], ['domain_id', '=', $domain->id]])->first();
        }

        if ($link) {
            if ($link->user_id != 0 && $link->user->trashed()) {
                abort(404, __('Link disabled'));
            }

            $bannedWords = preg_split('/[\r\n]+/', config('settings.banned_words'), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($bannedWords as $word) {
                if (mb_strpos(mb_strtolower($link->url), mb_strtolower($word)) !== false) {
                    abort(404, __('Link banned'));
                }
            }

            $referrer = parse_url($request->server('HTTP_REFERER'), PHP_URL_HOST) ?? null;

            if ($link->redirect_password && $request->session()->get('redirect_password_' . $link->id)) {
                $request->session()->put('redirect_referrer_' . $link->id, $referrer);
            } elseif ($link->redirect_password && $request->session()->get('redirect_password_' . $link->id)) {
                $referrer = $request->session()->get('redirect_referrer_' . $link->id);

                // If there's no additional consent required (no custom pixels)
                if (count($link->pixels) == 0) {
                    $request->session()->forget('redirect_referrer_' . $link->id);
                }
            }

            $now = Carbon::now();

            if ($link->active_period_start_at && $link->active_period_end_at && !$now->isBetween($link->active_period_start_at, $link->active_period_end_at)) {
                if ($link->expiration_url) {
                    return redirect()->to($link->expiration_url, 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
                }

                abort(404, __('Link expired'));
            }

            if ($link->clicks_limit && $link->clicks_count >= $link->clicks_limit) {
                if ($link->expiration_url) {
                    return redirect()->to($link->expiration_url, 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
                }

                abort(404, __('Link expired'));
            }

            try {
                $geoip = (new GeoIP(storage_path('app/geoip/GeoLite2-City.mmdb')))->city($request->ip());

                $continentCode = $geoip->continent->code;
                $countryCode = $geoip->country->isoCode;
                $country = $geoip->country->isoCode . ':' . $geoip->country->name;
                $city = $geoip->country->isoCode . ':' . $geoip->city->name . (isset($geoip->mostSpecificSubdivision->isoCode) ? ', ' . $geoip->mostSpecificSubdivision->isoCode : '');
            } catch (Exception) {
                $continentCode = null;
                $countryCode = null;
                $country = null;
                $city = null;
            }

            $ua = new UserAgent(getallheaders());

            $data['country'] = $country;
            $data['city'] = $city;
            $data['browser'] = $ua->browser->name ?? null;
            $data['operating_system'] = $ua->os->name ?? null;
            $data['device'] = $ua->device->type ?? null;
            $data['language'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

            $url = $this->resolveDestinationUrl($link, $data, $continentCode, $countryCode);

            if ($link->redirect_password && !$request->session()->get('redirect_password_' . $link->id)) {
                return view('redirect.password', ['link' => $link]);
            }

            if ($link->sensitive_content) {
                if (!$request->session()->get('redirect_sensitive_' . $link->id)) {
                    session(['redirect_referrer_' . $link->id => $referrer]);

                    return view('redirect.sensitive-consent', ['link' => $link, 'url' => $url]);
                } else {
                    $referrer = $request->session()->get('redirect_referrer_' . $link->id);

                    // If there's no additional consent required (no custom pixels)
                    if (count($link->pixels) == 0) {
                        $request->session()->forget('redirect_referrer_' . $link->id);
                    }
                }
            }

            if (config('settings.short_splash_redirect')) {
                if ($link->user_id == 0 || $link->user->active_plan->features->no_ads == 0) {
                    if (!$request->session()->get('redirect_splash_' . $link->id)) {
                        session(['redirect_referrer_' . $link->id => $referrer]);

                        return view('redirect.splash', ['link' => $link, 'url' => $url]);
                    } else {
                        $referrer = $request->session()->get('redirect_referrer_' . $link->id);

                        // If there's no additional consent required (no custom pixels)
                        if (count($link->pixels) == 0) {
                            $request->session()->forget('redirect_referrer_' . $link->id);
                        }
                    }
                }
            }

            if (count($link->pixels) > 0) {
                if (!$request->hasCookie('redirect_tracking_' . $link->id)) {
                    session(['redirect_referrer_' . $link->id => $referrer]);

                    return view('redirect.tracking-consent', ['link' => $link, 'url' => $url]);
                } else {
                    $referrer = $request->session()->get('redirect_referrer_' . $link->id);

                    $request->session()->forget('redirect_referrer_' . $link->id);
                }
            }

            $data['referrer'] = $referrer;

            if ($link->user_id) {
                DB::statement("INSERT INTO `stats` (`link_id`, `referrer`, `operating_system`, `browser`, `device`, `country`, `city`, `language`, `created_at`) VALUES (:link_id, :referrer, :operating_system, :browser, :device, :country, :city, :language, :created_at)", ['link_id' => $link->id, 'referrer' => $data['referrer'] ? mb_substr($data['referrer'], 0, 255) : null, 'operating_system' => $data['operating_system'] ? mb_substr($data['operating_system'], 0, 64) : null, 'browser' => $data['browser'] ? mb_substr($data['browser'], 0, 64) : null, 'device' => $data['device'] ? mb_substr($data['device'], 0, 64) : null, 'country' => $data['country'] ? mb_substr($data['country'], 0, 64) : null, 'city' => $data['city'] ? mb_substr($data['city'], 0, 128) : null, 'language' => $data['language'] ? mb_substr($data['language'], 0, 2) : null, 'created_at' => $now]);
            }

            Link::where('id', '=', $link->id)->increment('clicks_count', 1);

            if (count($link->pixels) > 0) {
                if ($request->cookie('redirect_tracking_' . $link->id) == 1) {
                    return view('redirect.pixel', ['link' => $link, 'url' => $url]);
                }
            }

            return redirect()->to($this->urlParamsForward($url, $request->query()), 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        if ($localHost != $remoteHost) {
            $domain = Domain::where('name', '=', $remoteHost)->first();

            if ($domain) {
                if ($domain->not_found_url) {
                    return redirect()->to($domain->not_found_url, 301)->header('Cache-Control', 'no-store, no-cache, must-revalidate');
                }
            }
        }

        abort(404);
    }

    /**
     * Validate the link's redirect password.
     */
    public function validateRedirectPassword(ValidateLinkRedirectPasswordRequest $request, string $id): RedirectResponse
    {
        session()->flash('redirect_password_' . $id);
        return redirect()->back();
    }

    /**
     * Validate the link's sensitive consent page.
     */
    public function validateSensitiveConsent(Request $request, string $id): RedirectResponse
    {
        session()->flash('redirect_sensitive_' . $id);
        return redirect()->back()->with([
            'redirect_password_' . $id => true
        ]);
    }

    /**
     * Validate the link's splash page.
     */
    public function validateSplash(Request $request, string $id): RedirectResponse
    {
        session()->flash('redirect_splash_' . $id);
        return redirect()->back()->with([
            'redirect_sensitive_' . $id => true,
            'redirect_password_' . $id => true
        ]);
    }

    /**
     * Validate the link's tracking consent page.
     */
    public function validateTrackingConsent(Request $request, string $id): RedirectResponse
    {
        return redirect()->back()->withCookie('redirect_tracking_' . $id, $request->input('consent') ? 1 : 0, (60 * 24 * 30))->with([
            'redirect_sensitive_' . $id => true,
            'redirect_splash_' . $id => true,
            'redirect_password_' . $id => true
        ]);
    }

    /**
     * Append or override query parameters in a URL.
     */
    private function urlParamsForward(string $url, array $forwardParams): string
    {
        if (empty($forwardParams)) {
            return $url;
        }

        $parts = parse_url($url);

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query = array_merge($query, $forwardParams);

        $queryString = http_build_query($query);

        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') . ($parts['user'] ?? '') . (isset($parts['pass']) ? ':' . $parts['pass'] : '') . (isset($parts['user']) ? '@' : '') . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '') . ($parts['path'] ?? '') . ($queryString !== '' ? '?' . $queryString : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * Resolve the destination URL.
     */
    public function resolveDestinationUrl(Link $link, array $data, ?string $continentCode, ?string $countryCode): string
    {
        $url = $link->url;

        if ($link->targets_type == 'continents' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $continent) {
                    if ($continentCode == $continent->key) {
                        $url = $continent->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'countries' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $country) {
                    if ($countryCode == $country->key) {
                        $url = $country->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'operating_systems' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $operatingSystem) {
                    if ($data['operating_system'] == $operatingSystem->key) {
                        $url = $operatingSystem->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'browsers' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $browser) {
                    if ($data['browser'] == $browser->key) {
                        $url = $browser->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'languages' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $language) {
                    if ($data['language'] == $language->key) {
                        $url = $language->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'devices' && $link->targets !== null) {
            if ($link->targets) {
                foreach ($link->targets as $device) {
                    if ($data['device'] == $device->key) {
                        $url = $device->value;
                    }
                }
            }
        }

        if ($link->targets_type == 'rotations' && $link->targets !== null) {
            $rotationLinks = collect($link->targets)->toArray();

            $totalRotations = count($rotationLinks);

            $lastRotation = 0;
            if ($totalRotations > 0 && $totalRotations > $link->last_rotation) {
                $lastRotation = $link->last_rotation + 1;
            }

            Link::where('id', '=', $link->id)->update(['last_rotation' => $lastRotation]);

            $currentRotationIndex = 1;
            foreach ($link->targets as $rotation) {
                if ($currentRotationIndex == $link->last_rotation) {
                    $url = $rotation->value;
                    break;
                }
                $currentRotationIndex++;
            }
        }

        return $url;
    }
}