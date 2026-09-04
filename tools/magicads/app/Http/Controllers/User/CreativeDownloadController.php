<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdCreative;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreativeDownloadController extends Controller
{
    /**
     * Force-download a generated ad creative.
     *
     * Streams the asset through the app with a Content-Disposition: attachment
     * header so the browser saves it immediately instead of opening it in a new
     * tab. The HTML `download` attribute alone is unreliable (it is ignored for
     * cross-origin URLs and by several browsers/extensions), which is why the
     * preview "Download" button must route through here.
     */
    public function __invoke(AdCreative $creative): StreamedResponse
    {
        // Ownership guard — users may only download their own creatives.
        abort_unless($creative->user_id === auth()->id(), 403);

        abort_if(! $creative->file_path, 404);

        $extension = pathinfo($creative->file_path, PATHINFO_EXTENSION) ?: ($creative->type === 'video' ? 'mp4' : 'png');
        $filename = 'magicads-' . $creative->type . '-' . $creative->id . '.' . $extension;

        // Offloaded to a cloud storage provider — stream it back from there.
        if ($creative->storage_disk && $creative->storage_disk !== 'local') {
            $provider = app(\App\Services\Storage\StorageManager::class)->provider($creative->storage_disk);

            if ($provider) {
                $stream = $provider->readStream($creative->file_path);

                if ($stream !== null) {
                    $mime = $creative->mime_type ?: 'application/octet-stream';

                    return response()->streamDownload(function () use ($stream) {
                        fpassthru($stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }, $filename, ['Content-Type' => $mime]);
                }
            }
            // Fall through to local disk if the object can't be read (e.g. the
            // local copy was kept and the bucket is temporarily unreachable).
        }

        $disk = str_starts_with($creative->file_path, 'ai-studio/')
            ? Storage::disk('public')
            : Storage::disk('results');

        abort_unless($disk->exists($creative->file_path), 404);

        return $disk->download($creative->file_path, $filename);
    }
}
