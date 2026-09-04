<?php

namespace App\Listeners;

use App\Events\CreativeStored;
use App\Services\Storage\StorageManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Mirrors a finalized studio result to the admin's chosen cloud storage.
 *
 * This single core listener replaces the per-plugin listeners: it asks the
 * {@see StorageManager} which provider is *active* (general_settings.default_storage)
 * and uploads there. Because there is exactly one active provider, the old
 * "both plugins race on the same event" ambiguity is gone — the admin's
 * selection is authoritative.
 *
 * It is a no-op whenever storage is local or the active provider is
 * missing/disabled, so generation never breaks because of storage config.
 */
class OffloadCreativeToCloud
{
    public function __construct(protected StorageManager $manager) {}

    public function handle(CreativeStored $event): void
    {
        $provider = $this->manager->active();

        // Storage is local (or the configured provider is gone/disabled) →
        // leave the file on the local `results` disk.
        if ($provider === null) {
            return;
        }

        $creative = $event->creative;
        $key = (string) $creative->file_path;

        if ($key === '') {
            return;
        }

        // Already offloaded somewhere other than local — nothing to do.
        if (! empty($creative->storage_disk) && $creative->storage_disk !== StorageManager::LOCAL) {
            return;
        }

        $local = Storage::disk('results');

        if (! $local->exists($key)) {
            return;
        }

        try {
            $uploaded = $provider->putFile($key, $local->path($key), $creative->mime_type);

            if (! $uploaded) {
                Log::warning("Cloud offload failed for creative {$creative->id} to [{$provider->key()}] (key: {$key})");

                return;
            }

            // Record which backend now holds the bytes so reads/downloads/deletes
            // resolve through the right provider.
            $creative->forceFill(['storage_disk' => $provider->key()])->save();

            // Optionally reclaim local disk space now the bytes are safe.
            if ($provider->shouldDeleteLocal() && $local->exists($key)) {
                $local->delete($key);
            }
        } catch (Throwable $e) {
            // Never let a storage hiccup fail the generation; the file is still
            // on local disk and will simply serve from there.
            Log::error("Cloud offload error for creative {$creative->id}: " . $e->getMessage());
        }
    }
}
