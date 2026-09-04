<?php

namespace App\Services\Storage;

use App\Contracts\StorageProvider;
use App\Models\GeneralSetting;

/**
 * Central registry + resolver for studio result storage.
 *
 * Storage plugins register their provider here at boot (in their service
 * provider or, for these bundled plugins, in AppServiceProvider, guarded by
 * class_exists so core never hard-depends on a plugin):
 *
 *     app(StorageManager::class)->register(app(AmazonS3Service::class));
 *
 * The manager then answers three questions for the rest of the app:
 *
 *   - available()  → enabled providers, for the admin default-storage selector.
 *   - active()     → the provider results should be written to right now
 *                    (general_settings.default_storage), or null for local.
 *   - provider($k) → look up a provider by its key, for reads/deletes of an
 *                    already-offloaded creative.
 *
 * "local" is the implicit default: when default_storage is empty/"local" or the
 * selected provider is no longer enabled, results stay on the local `results`
 * disk exactly like the original setup.
 */
class StorageManager
{
    /** Local (on-server) storage sentinel — the default when nothing else is active. */
    public const LOCAL = 'local';

    /** @var array<string, StorageProvider> Registered providers keyed by key(). */
    protected array $providers = [];

    /** Register a storage provider (idempotent per key). */
    public function register(StorageProvider $provider): void
    {
        $this->providers[$provider->key()] = $provider;
    }

    /** All registered providers, regardless of enabled state. */
    public function all(): array
    {
        return $this->providers;
    }

    /** Look up a registered provider by key, or null. */
    public function provider(?string $key): ?StorageProvider
    {
        if ($key === null || $key === '' || $key === self::LOCAL) {
            return null;
        }

        return $this->providers[$key] ?? null;
    }

    /**
     * Enabled providers (installed + switched on + configured), keyed by key().
     * These are the choices offered in the admin default-storage selector.
     *
     * @return array<string, StorageProvider>
     */
    public function available(): array
    {
        return array_filter($this->providers, fn (StorageProvider $p) => $this->safeEnabled($p));
    }

    /**
     * Options for the admin selector: ['local' => 'Local server', 's3' => 'Amazon S3', …].
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        $options = [self::LOCAL => __('Local server (this machine)')];

        foreach ($this->available() as $key => $provider) {
            $options[$key] = $provider->label();
        }

        return $options;
    }

    /** The admin's configured default storage key (defaults to local). */
    public function defaultKey(): string
    {
        $key = (string) (GeneralSetting::query()->value('default_storage') ?: self::LOCAL);

        return $key !== '' ? $key : self::LOCAL;
    }

    /**
     * The provider new results should be written to right now, or null when
     * storage is local (or the configured provider is missing/disabled, in
     * which case we safely fall back to local).
     */
    public function active(): ?StorageProvider
    {
        $provider = $this->provider($this->defaultKey());

        return ($provider && $this->safeEnabled($provider)) ? $provider : null;
    }

    /** Whether a cloud provider is currently the active write target. */
    public function usingCloud(): bool
    {
        return $this->active() !== null;
    }

    /** enabled() guarded so one misconfigured plugin can't break resolution. */
    protected function safeEnabled(StorageProvider $provider): bool
    {
        try {
            return $provider->enabled();
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
