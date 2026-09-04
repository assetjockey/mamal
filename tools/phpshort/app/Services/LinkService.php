<?php

namespace App\Services;

use App\Models\Link;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Header as GuzzleHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class LinkService
{
    public function storeMultiple(array $data): array
    {
        $urls = preg_split('/[\r\n]+/', $data['urls'], -1, PREG_SPLIT_NO_EMPTY);
        $links = [];

        foreach ($urls as $url) {
            if (Auth::user()->can('create', [Link::class])) {
                $link = $this->store($data + ['url' => $url, 'alias' => null]);
                $links[] = $link->id;
            } else {
                break;
            }
        }

        return $links;
    }

    /**
     * Store a link.
     */
    public function store(array $data): Link
    {
        $link = new Link;

        $link->user_id = Auth::user()->id ?? 0;
        $link->url = $data['url'];
        $link->domain_id = $data['domain_id'];

        $metadata = config('settings.short_link_metadata_fetch') ? $this->getMetaTags($data['url']) : [];

        if (!empty($metadata['title'])) {
            $link->title = trim(Str::limit($metadata['title'], 128));
        } else {
            $link->title = null;
        }

        if (!empty($metadata['description'])) {
            $link->description = trim(Str::limit($metadata['description'], 512));
        } else {
            $link->description = null;
        }

        if (!empty($metadata['og:image'])) {
            $link->image = trim(Str::limit($metadata['og:image'], 1024));
        } else {
            $link->image = null;
        }

        if (array_key_exists('alias', $data) && $data['alias']) {
            $link->alias = $data['alias'];
        } else {
            $link->alias = $this->generateAlias();
        }

        if (array_key_exists('privacy', $data)) {
            $link->privacy = $data['privacy'];
        } else {
            $link->privacy = 0;
        }

        if (array_key_exists('password', $data)) {
            $link->password = $data['password'];
        } else {
            $link->password = null;
        }

        if (array_key_exists('redirect_password', $data)) {
            $link->redirect_password = $data['redirect_password'];
        } else {
            $link->redirect_password = null;
        }

        if (array_key_exists('space_id', $data)) {
            $link->space_id = $data['space_id'];
        } else {
            $link->space_id = null;
        }

        if (array_key_exists('expiration_url', $data)) {
            $link->expiration_url = $data['expiration_url'];
        } else {
            $link->expiration_url = null;
        }

        if (array_key_exists('active_period_start_at', $data) && $data['active_period_start_at']) {
            $link->active_period_start_at = Carbon::parse($data['active_period_start_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } else {
            $link->active_period_start_at = null;
        }

        if (array_key_exists('active_period_end_at', $data) && $data['active_period_end_at']) {
            $link->active_period_end_at = Carbon::parse($data['active_period_end_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } else {
            $link->active_period_end_at = null;
        }

        if (array_key_exists('clicks_limit', $data)) {
            $link->clicks_limit = $data['clicks_limit'];
        } else {
            $link->clicks_limit = 0;
        }

        if (array_key_exists('sensitive_content', $data)) {
            $link->sensitive_content = $data['sensitive_content'];
        } else {
            $link->sensitive_content = 0;
        }

        if (array_key_exists('targets_type', $data)) {
            $link->targets_type = $data['targets_type'];
        } else {
            $link->targets_type = null;
        }

        if (array_key_exists('targets', $data)) {
            $link->targets = array_filter(is_array($data['targets']) ? $data['targets'] : [], function ($item) { return isset($item['value']); });
        } else {
            $link->targets = null;
        }

        $link->save();

        if (array_key_exists('pixel_ids', $data)) {
            $link->pixels()->sync(array_filter($data['pixel_ids']) ?? []);
        }

        return $link;
    }

    /**
     * Update a link.
     */
    public function update(Link $link, array $data): Link
    {
        if (array_key_exists('url', $data)) {
            if ($link->url != $data['url']) {
                $metadata = config('settings.short_link_metadata_fetch') ? $this->getMetaTags($data['url']) : [];

                if (!empty($metadata['title'])) {
                    $link->title = trim(Str::limit($metadata['title'], 128));
                } else {
                    $link->title = null;
                }

                if (!empty($metadata['description'])) {
                    $link->description = trim(Str::limit($metadata['description'], 512));
                } else {
                    $link->description = null;
                }

                if (!empty($metadata['og:image'])) {
                    $link->image = trim(Str::limit($metadata['og:image'], 1024));
                } else {
                    $link->image = null;
                }
            }

            $link->url = $data['url'];
        }

        if (array_key_exists('alias', $data) && $data['alias']) {
            $link->alias = $data['alias'];
        }

        if (array_key_exists('privacy', $data)) {
            $link->privacy = $data['privacy'];
        }

        if (array_key_exists('password', $data)) {
            $link->password = $data['password'];
        }

        if (array_key_exists('redirect_password', $data)) {
            $link->redirect_password = $data['redirect_password'];
        }

        if (array_key_exists('space_id', $data)) {
            $link->space_id = $data['space_id'];
        }

        if (array_key_exists('expiration_url', $data)) {
            $link->expiration_url = $data['expiration_url'];
        }

        if (array_key_exists('active_period_start_at', $data) && $data['active_period_start_at']) {
            $link->active_period_start_at = Carbon::parse($data['active_period_start_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } elseif (array_key_exists('active_period_start_at', $data)) {
            $link->active_period_start_at = null;
        }

        if (array_key_exists('active_period_end_at', $data) && $data['active_period_end_at']) {
            $link->active_period_end_at = Carbon::parse($data['active_period_end_at'], Auth::user()->timezone ?? config('settings.timezone'))->tz(config('app.timezone'));
        } elseif (array_key_exists('active_period_end_at', $data)) {
            $link->active_period_end_at = null;
        }

        if (array_key_exists('clicks_limit', $data)) {
            $link->clicks_limit = $data['clicks_limit'];
        }

        if (array_key_exists('sensitive_content', $data)) {
            $link->sensitive_content = $data['sensitive_content'];
        }

        if (array_key_exists('targets_type', $data)) {
            $link->targets_type = $data['targets_type'];
        }

        if (array_key_exists('targets', $data)) {
            $link->targets = array_filter(is_array($data['targets']) ? $data['targets'] : [], function ($item) { return isset($item['value']); });
        }

        if (array_key_exists('pixel_ids', $data)) {
            $link->pixels()->sync(array_filter($data['pixel_ids']) ?? []);
        }

        $link->save();

        return $link;
    }

    /**
     * Generate a random unique alias.
     */
    private function generateAlias(): string
    {
        $fails = 0;

        while (true) {
            $alias = Str::random(config('settings.short_alias_length') + $fails);

            if (!Link::where('alias', '=', $alias)->exists()) {
                return $alias;
            }

            $fails++;
        }
    }

    /**
     * Fetch a URL.
     */
    private function fetchUrl(string $url): ?ResponseInterface
    {
        $httpClient = new GuzzleClient();

        try {
            return $httpClient->request('GET', $url, [
                'version' => config('settings.request_http_version'),
                'proxy' => [
                    'http' => getRequestProxy(),
                    'https' => getRequestProxy()
                ],
                'timeout' => config('settings.request_timeout'),
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => config('settings.request_user_agent'),
                    'Accept-Encoding' => 'gzip, deflate',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml,application/json;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8'
                ]
            ]);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Return the meta tags for a given URL.
     */
    public function getMetaTags(string $url): array
    {
        $urlResponse = $this->fetchUrl($url);

        if (!$urlResponse instanceof ResponseInterface) {
            return [];
        }

        $urlResponseHeader = $urlResponse->getHeader('content-type');
        $parsedHeaders = GuzzleHeader::parse($urlResponseHeader);

        $charset = $parsedHeaders[0]['charset'] ?? null;

        $content = mb_convert_encoding($urlResponse->getBody(), 'UTF-8', in_array($charset, mb_list_encodings()) ? $charset : ($charset == 'MS949' && in_array('UHC', mb_list_encodings()) ? 'CP949' : 'UTF-8'));

        $array = [];

        $pattern = '
            ~<\s*meta\s
        
            # using lookahead to capture type to $1
            (?=[^>]*?
            \b(?:name|property|http-equiv)\s*=\s*
            (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
            ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            )
        
            # capture content to $2
            [^>]*?\bcontent\s*=\s*
            (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
            ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
            [^>]*>
        
            ~ix';
        if (preg_match_all($pattern, $content, $out)) {
            $array = array_combine(array_map('strtolower', $out[1]), $out[2]);
        }

        preg_match("/<title[^>]*>(.*?)<\/title>/is", $content, $title);
        $array['title'] = $title[1] ?? null;

        return $array;
    }
}