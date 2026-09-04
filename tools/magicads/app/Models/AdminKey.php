<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminKey extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'openai_key',
        'gemini_key',
        'xai_key',
        'nova_key',
        'anthropic_key',
        'runway_key',
        'kling_key',
        'seedance_key',
        'fal_key',
        'kie_key',
        'flux_key',
        'ideogram_key',
        'recraft_key',
        'google_maps_api_key',
        'google_analytics_property_id',
        'google_analytics_service_credentials',
        'google_analytics_tracking_id',
        'google_recaptcha_site_key',
        'google_recaptcha_secret_key',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'openai_key' => 'encrypted',
        'gemini_key' => 'encrypted',
        'xai_key' => 'encrypted',
        'nova_key' => 'encrypted',
        'anthropic_key' => 'encrypted',
        'runway_key' => 'encrypted',
        'kling_key' => 'encrypted',
        'seedance_key' => 'encrypted',
        'fal_key' => 'encrypted',
        'kie_key' => 'encrypted',
        'flux_key' => 'encrypted',
        'ideogram_key' => 'encrypted',
        'recraft_key' => 'encrypted',
    ];

    /**
     * Read an API key column safely, regardless of whether the stored value is
     * encrypted (the normal path) or was inserted as plain text (legacy data,
     * manual SQL inserts, or rows from before the encrypted cast was added).
     *
     * Without this, reading a plain-text value through the `encrypted` cast
     * throws `DecryptException("The payload is invalid.")` and bubbles up to
     * the user as "Generation failed: The payload is invalid.".
     */
    public function apiKey(string $column): ?string
    {
        $raw = $this->getRawOriginal($column);

        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            // Value was stored unencrypted — return it as-is.
            return $raw;
        }
    }

    /**
     * Write an API key column safely.
     *
     * We deliberately bypass Eloquent's normal update/dirty-check path. With an
     * `encrypted` cast, saving a column runs `originalIsEquivalent()`, which
     * decrypts the *existing* stored value to see whether it changed. If that
     * existing value is plain text or was encrypted under a previous APP_KEY,
     * the comparison throws `DecryptException("The payload is invalid.")`
     * before the new value is ever written.
     *
     * Encrypting here and writing through the query builder avoids touching
     * (and therefore decrypting) the old value entirely.
     */
    public function setApiKey(string $column, ?string $value): void
    {
        $encrypted = ($value === null || $value === '')
            ? null
            : \Illuminate\Support\Facades\Crypt::encryptString($value);

        static::query()->whereKey($this->getKey())->update([$column => $encrypted]);

        // Keep the in-memory model consistent with what we just persisted,
        // syncing the original so future saves don't re-compare this column.
        $this->setRawAttributes(
            array_merge($this->getAttributes(), [$column => $encrypted]),
            true
        );
    }
}
