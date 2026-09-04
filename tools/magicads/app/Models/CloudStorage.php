<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-provider cloud storage configuration.
 *
 * One row per storage plugin (keyed by `provider`, e.g. "s3" / "wasabi").
 * Replaces the flat amazon_s3_* / wasabi_* columns that used to live on the
 * single extension_settings row, so any number of storage plugins can be added
 * in the future without schema churn.
 *
 * `enabled` is the plugin's own "offloading turned on + configured" flag —
 * an enabled provider shows up in the admin's default-storage selector under
 * General Settings. Which one is actually *used* is decided separately by
 * general_settings.default_storage.
 *
 * Provider-specific connection details (key, secret, region, bucket, endpoint,
 * url, path-style, prefix, delete-local) are stored as a JSON `config` blob so
 * the schema stays provider-agnostic. The secret is encrypted within the blob
 * by the owning plugin's config screen before it reaches here.
 */
class CloudStorage extends Model
{
    protected $table = 'cloud_storages';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'config'  => 'array',
    ];

    /** Fetch (or make a blank) row for a provider key. */
    public static function forProvider(string $provider): self
    {
        return static::firstOrNew(['provider' => $provider]);
    }

    /** Read a single key out of the JSON config blob. */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
