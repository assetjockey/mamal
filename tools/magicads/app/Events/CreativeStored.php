<?php

namespace App\Events;

use App\Models\AdCreative;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired the moment a studio result (image or video) has been finalized: the
 * bytes are written to the local `results` disk, the AdCreative row is marked
 * `completed` and `file_path` is set.
 *
 * Core does nothing with this on its own — it's a hook point so optional
 * storage plugins (e.g. "magicads-amazon-s3") can mirror the freshly generated
 * file to external storage without core having to depend on the plugin.
 */
class CreativeStored
{
    use Dispatchable;

    public function __construct(public AdCreative $creative) {}
}
