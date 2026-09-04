<?php

namespace App\Services;

use App\Models\SocialMediaSetting;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;

/**
 * Bridges the admin-managed `social_media_settings` row to Laravel Socialite.
 *
 * The site owner toggles social login and stores per-provider credentials on
 * the admin "Auth Settings" page. This service reads that row, decides which
 * providers are usable, and configures Socialite at runtime so no .env edits
 * are required.
 *
 * The admin UI exposes four providers — facebook, twitter, google, linkedin —
 * which map onto Socialite drivers as follows:
 *   google   -> google
 *   facebook -> facebook
 *   twitter  -> twitter   (OAuth 2.0)
 *   linkedin -> linkedin-openid (LinkedIn's current OpenID Connect flow)
 */
class SocialAuthService
{
    /**
     * App provider slug => Socialite driver name.
     *
     * @var array<string, string>
     */
    public const DRIVERS = [
        'facebook' => 'facebook',
        'twitter' => 'twitter',
        'google' => 'google',
        'linkedin' => 'linkedin-openid',
    ];

    /**
     * Human-friendly labels for each provider slug.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'google' => 'Google',
        'linkedin' => 'LinkedIn',
    ];

    /**
     * Determine whether social login is enabled at all (master switch).
     */
    public function loginEnabled(): bool
    {
        $settings = $this->settings();

        return (bool) ($settings?->social_media);
    }

    /**
     * Return the provider slugs that are ready to use: master switch on, the
     * individual provider enabled, and both API key + secret present.
     *
     * @return array<int, string>
     */
    public function enabledProviders(): array
    {
        $settings = $this->settings();

        if (! $settings || ! $settings->social_media) {
            return [];
        }

        $enabled = [];

        foreach (array_keys(self::DRIVERS) as $provider) {
            $hasCredentials = filled($settings->{$provider.'_api_key'})
                && filled($settings->{$provider.'_api_secret'});

            if ($settings->{$provider} && $hasCredentials) {
                $enabled[] = $provider;
            }
        }

        return $enabled;
    }

    /**
     * Whether a single provider slug is enabled and ready.
     */
    public function isProviderEnabled(string $provider): bool
    {
        return in_array($provider, $this->enabledProviders(), true);
    }

    /**
     * Build a configured Socialite driver for the given app provider slug.
     *
     * Injects the DB-stored credentials into the matching `services.*` config
     * key just-in-time so Socialite's manager picks them up. Returns the
     * stateless-capable provider instance.
     */
    public function driver(string $provider): Provider
    {
        $settings = $this->settings();
        $driver = self::DRIVERS[$provider];

        $redirect = filled($settings->{$provider.'_url'})
            ? $settings->{$provider.'_url'}
            : route('social.callback', ['provider' => $provider]);

        // Socialite reads each driver's credentials from a dedicated services
        // key. Map the admin columns onto the correct key for that driver.
        $configKey = match ($driver) {
            'google' => 'services.google',
            'facebook' => 'services.facebook',
            'twitter' => 'services.twitter',
            'linkedin-openid' => 'services.linkedin-openid',
        };

        $config = [
            'client_id' => $settings->{$provider.'_api_key'},
            'client_secret' => $settings->{$provider.'_api_secret'},
            'redirect' => $redirect,
        ];

        // Force the modern OAuth 2.0 flow for Twitter (the default driver still
        // resolves to the legacy OAuth 1.0a server otherwise).
        if ($driver === 'twitter') {
            $config['oauth'] = 2;
        }

        Config::set($configKey, $config);

        return Socialite::driver($driver);
    }

    /**
     * Map an app provider slug to its display label.
     */
    public function label(string $provider): string
    {
        return self::LABELS[$provider] ?? ucfirst($provider);
    }

    /**
     * Fetch the single settings row (or null on fresh installs).
     */
    protected function settings(): ?SocialMediaSetting
    {
        return SocialMediaSetting::query()->first();
    }
}
