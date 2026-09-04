<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class ExtensionController extends Controller
{
    public const API_URL = 'https://marketplace.berkine.net/api/magicads/';

    private $root_path;

    public function extensions()
    {
        return $this->get();
    }

    public function themes()
    {
        return $this->get(true);
    }

    public function search($slug)
    {
        $response = $this->request('post', "extension/{$slug}");

        if ($response->ok()) {

            $data = $response->json('data');

            $extension = Extension::where('slug', $slug)->first();

            return array_merge($data, [
                'latest_version' => $extension?->version,
                'installed' => (bool) $extension?->installed,
                'upgradable' => $extension?->version !== $data['version'],
            ]);
        }

        return [];
    }

    public function verify($slug, $payment)
    {
        $response = $this->request('post', "extension/purchase/{$slug}/verify/{$payment}");

        if ($response->ok()) {

            $status = $response->json('status');
            $data = $response->json('data');

            if ($slug == 'premier') {
                return $status == 'succeeded' ? $data : [];
            } elseif ($slug == 'support') {
                return $status == 'active' ? $data : [];
            } else {
                $extension = Extension::where('slug', $slug)->first();

                if ($status != 'succeeded') {
                    return [];
                }

                $extension->purchased = true;
                $extension->save();

                return array_merge($data, [
                    'latest_version' => $extension?->version,
                    'installed' => (bool) $extension?->installed,
                    'upgradable' => $extension?->version !== $data['version'],
                    'purchased' => true,
                ]);
            }

        }

        return [];
    }

    public function checkPayment($slug)
    {
        if ($slug != 'default') {
            $response = $this->request('post', "extension/purchase/check/{$slug}");

            if ($response->ok()) {

                $data = $response->json('data');

                $extension = Extension::where('slug', $slug)->first();
                $extension->purchased = true;
                $extension->save();

                $setting = GeneralSetting::first();

                if (strtolower($data['type']) == 'dashboard') {
                    $setting->dashboard_theme = $extension->slug;
                } elseif (strtolower($data['type']) == 'frontend') {
                    $setting->frontend_theme = $extension->slug;
                } else {
                    $setting->dashboard_theme = $extension->slug;
                    $setting->frontend_theme = $extension->slug;
                }

                $setting->save();

            }
        } else {
            $setting = GeneralSetting::first();

            $setting->dashboard_theme = 'default';
            $setting->frontend_theme = 'default';
            $setting->save();
        }

        return [];
    }

    public function get(bool $is_theme = false)
    {
        $appVersion = env('APP_VERSION');

        $response = $this->request('post', 'extension', [
            'is_theme' => $is_theme,
            'app_version' => $appVersion,
        ]);
        
        if ($response->ok()) {

            $data = $response->json('data');

            $this->update($data);

            $purchases = $this->request('post', 'extension/user/purchases');

            if ($purchases->ok()) {

                $purchase_data = $purchases->json('data');

                $this->updatePurchases($purchase_data);
            }

            return $this->merge($data);
        }

        return [];
    }

    public function installTheme(string $slug)
    {
        $this->root_path = base_path();
        $appVersion = env('APP_VERSION');
        $setting = GeneralSetting::first();

        if ($slug == 'default') {

            $setting->dashboard_theme = $slug;
            $setting->frontend_theme = $slug;
            $setting->save();

            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Theme installation completed successfully'),
            ];
        }

        $extension = $this->search($slug);

        $response = $this->request('post', 'extension/version/install', [
            'slug' => $slug,
            'app_version' => $appVersion,
        ]);

        if ($response->ok()) {

            $content = $response->body();
            $destination = $this->root_path.DIRECTORY_SEPARATOR.'destination.zip';

            // Persist the downloaded archive to the project root.
            if (file_put_contents($destination, $content) === false) {
                return [
                    'status' => false,
                    'message' => __('Unable to write the downloaded theme archive to disk.'),
                ];
            }

            // Extract using PHP's native ZipArchive (no third-party zip dependency).
            $zip = new \ZipArchive;
            $opened = $zip->open($destination);

            if ($opened !== true) {
                @unlink($destination);

                return [
                    'status' => false,
                    'message' => __('The downloaded theme archive is invalid or corrupted.'),
                ];
            }

            // Read the theme's canonical name from the bundled theme.json while the
            // archive is still open. The distributable package folder may be named
            // differently from the theme itself, so the value stored in
            // frontend_theme/dashboard_theme must come from theme.json's "name"
            // field — that is what the theme loader matches against — not the
            // package slug.
            $themeName = $this->resolveThemeNameFromArchive($zip);

            $extracted = $zip->extractTo($this->root_path.DIRECTORY_SEPARATOR);
            $zip->close();
            @unlink($destination);

            if (! $extracted) {
                return [
                    'status' => false,
                    'message' => __('Failed to extract the theme files.'),
                ];
            }

            // Fall back to the package slug only when theme.json did not declare a name.
            $themeName = $themeName ?: $extension['slug'];

            Extension::query()->where('slug', $slug)->update(['installed' => 1, 'version' => $extension['version']]);

            if (strtolower($extension['type']) == 'dashboard') {
                $setting->dashboard_theme = $themeName;
            } elseif (strtolower($extension['type']) == 'frontend') {
                $setting->frontend_theme = $themeName;
            } else {
                $setting->dashboard_theme = $themeName;
                $setting->frontend_theme = $themeName;
            }

            $setting->save();

            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Theme installation completed successfully'),
            ];

        } else {
            return [
                'status' => false,
                'message' => $response->json('message'),
            ];
        }
    }

    /**
     * Resolve a theme's declared name from the `theme.json` bundled in its
     * installation archive.
     *
     * A theme package is laid out as `resources/views/<folder>/theme.json`, and
     * the `<folder>` (the package/distributable name) may differ from the theme's
     * own identity. The theme loader keys themes by the `name` value inside
     * `theme.json`, so that is the value we persist to
     * `general_settings.frontend_theme` / `dashboard_theme`.
     *
     * Returns null when no readable `theme.json` with a non-empty `name` is found,
     * so the caller can fall back to the package slug.
     */
    private function resolveThemeNameFromArchive(\ZipArchive $zip): ?string
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false) {
                continue;
            }

            // Normalise separators so Windows-built archives match too.
            $normalized = str_replace('\\', '/', $entry);

            if (! preg_match('#(^|/)resources/views/[^/]+/theme\.json$#', $normalized)) {
                continue;
            }

            $contents = $zip->getFromIndex($i);

            if ($contents === false || $contents === '') {
                continue;
            }

            $data = json_decode($contents, true);

            if (is_array($data) && ! empty($data['name'])) {
                return trim((string) $data['name']);
            }
        }

        return null;
    }

    public function installExtension(string $slug)
    {
        $appVersion = env('APP_VERSION');

        $response = $this->request('post', 'extension/version/install', [
            'slug' => $slug,
            'app_version' => $appVersion,
        ]);

        return $response;
    }

    public function request(string $method, string $route, array $body = [], $url = null)
    {
        $user_data = $this->userInfo();
        $url = $url ?? self::API_URL.$route;

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-domain' => request()->getHost(),
            'x-username' => $user_data['username'],
            'x-activation-code' => $user_data['license'],
        ])->when($method === 'post', function ($http) use ($url, $body) {
            return $http->post($url, $body);
        }, function ($http) use ($url, $body) {
            return $http->get($url, $body);
        });
    }

    public function merge(array $data): array
    {
        $extensions = Extension::query()->get();

        return collect($data)->map(function ($extension) use ($extensions) {
            $value = $extensions->firstWhere('slug', $extension['slug']);

            return array_merge($extension, [
                'latest_version' => $value?->version,
                'installed' => (bool) $value?->installed,
                'upgradable' => $value?->version !== $extension['version'],
            ]);
        })->toArray();
    }

    private function update(array $data): void
    {
        foreach ($data as $extension) {
            Extension::query()->firstOrCreate([
                'slug' => $extension['slug'],
                'is_theme' => $extension['is_theme'],
            ], [
                'version' => $extension['version'],
                'is_free' => $extension['is_free'],
            ]);
        }
    }

    private function updatePurchases(array $data): void
    {

        foreach ($data as $extension) {
            if ($extension['status'] == 'succeeded') {
                $ext = Extension::where('slug', $extension['slug'])->first();
                if ($ext) {
                    $ext->purchased = true;
                    $ext->save();
                }
            }

        }
    }

    public function userInfo()
    {
        $settings = GeneralSetting::first();

        return [
            'license' => $settings->license ?? '',
            'username' => $settings->username ?? '',
        ];
    }

    public function checkDownloadLicense($license)
    {
        $response = $this->request('post', 'license/check', [
            'license' => $license,
        ]);

        if ($response->ok()) {

            $data = $response->json('data');

            return $data;
        } else {
            return false;
        }
    }

    public function sak()
    {
        $response = $this->request('post', 'extension/sak');

        if ($response->ok()) {

            $data = $response->json('data');

            return $data;
        }

    }

    public function get_metadata()
    {
        $response = $this->request('post', 'extension/version/metadata');

        if ($response->ok()) {

            $data = $response->json('metadata');

            return $data;
        }

        return false;
    }

    public function get_version($update)
    {
        $response = $this->request('post', 'extension/version/update');

        if ($response->ok()) {

            $data = $response->json('version');

            if ($data) {
                return $update;
            } else {
                return false;
            }
        }

        return false;
    }
}
