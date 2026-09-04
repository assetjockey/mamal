<?php

namespace App\Contracts;

/**
 * A pluggable storage backend for studio results (images / videos).
 *
 * Core ships only the local `results` disk. Optional plugins (Amazon S3,
 * Wasabi, and any future provider) implement this contract and register
 * themselves with {@see \App\Services\Storage\StorageManager}. The admin then
 * picks which registered provider is the active "default storage" under
 * General Settings → General; the {@see \App\Listeners\OffloadCreativeToCloud}
 * listener uploads new results to whichever provider is active.
 *
 * The object key passed to these methods is always the creative's relative
 * file_path (e.g. "images/gemini/uuid.png") — identical to the path on the
 * local `results` disk — so a single value works on every backend. A provider
 * may transparently prepend its own configured key prefix.
 */
interface StorageProvider
{
    /** Stable machine key, e.g. "s3" or "wasabi". Stored on ad_creatives.storage_disk. */
    public function key(): string;

    /** Human label shown in the admin default-storage selector, e.g. "Amazon S3". */
    public function label(): string;

    /**
     * Whether this provider is installed, switched on by the admin AND has
     * enough credentials to talk to its bucket. Only enabled providers appear
     * in the default-storage selector and are eligible to receive uploads.
     */
    public function enabled(): bool;

    /** Whether the local copy should be removed after a successful upload. */
    public function shouldDeleteLocal(): bool;

    /** Upload raw bytes. Returns true on success. */
    public function put(string $key, string $contents, ?string $mime = null): bool;

    /** Stream a local file up to the bucket without buffering it in memory. */
    public function putFile(string $key, string $absoluteLocalPath, ?string $mime = null): bool;

    /** Whether an object exists in the bucket. */
    public function exists(string $key): bool;

    /** Download an object's bytes, or null on failure. */
    public function get(string $key): ?string;

    /** A read stream for an object, or null on failure. */
    public function readStream(string $key);

    /** Delete an object. Returns true if it's gone afterwards. */
    public function delete(string $key): bool;

    /** Public URL for a stored object, or null on failure. */
    public function url(string $key): ?string;

    /** A signed, time-limited URL for private buckets, or null on failure. */
    public function temporaryUrl(string $key, int $minutes = 5): ?string;
}
