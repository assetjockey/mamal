<?php

namespace App\Livewire\User\Studio;

use App\Models\AdCreative;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Title('Gallery')]
class Gallery extends Component
{
    use WithPagination;

    public string $typeFilter = 'all';

    public string $sortOrder = 'newest';

    /** Asset payload to auto-open in the lightbox on load (via ?open={id}). */
    public ?array $openAsset = null;

    public function mount(): void
    {
        $openId = request()->integer('open');

        if ($openId) {
            $asset = AdCreative::where('user_id', auth()->id())->completed()->find($openId);

            if ($asset) {
                $this->openAsset = [
                    'id' => $asset->id,
                    'type' => $asset->type,
                    'prompt' => $asset->prompt,
                    'provider' => $asset->provider,
                    'preset_slug' => $asset->preset_slug,
                    'width' => $asset->width,
                    'height' => $asset->height,
                    'duration' => $asset->duration,
                    'file_path' => $asset->fileUrl(),
                    'mime_type' => $asset->mime_type,
                    'file_size' => $asset->file_size,
                    'created_at' => $asset->created_at->format('M d, Y · h:i A'),
                    'overlay_text' => $asset->generation_meta['overlayText'] ?? null,
                    'cta_text' => $asset->generation_meta['ctaText'] ?? null,
                    'brand_primary' => $asset->generation_meta['brandPrimaryColor'] ?? null,
                    'brand_secondary' => $asset->generation_meta['brandSecondaryColor'] ?? null,
                    'force_overlay' => $asset->generation_meta['forceOverlay'] ?? null,
                    'reference_image' => $asset->generation_meta['referenceImagePath'] ?? null,
                    'breakdown' => $asset->promptBreakdown(),
                ];
            }
        }
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortOrder(): void
    {
        $this->resetPage();
    }

    public function delete(int $assetId): void
    {
        $asset = AdCreative::where('user_id', auth()->id())->findOrFail($assetId);

        if ($asset->file_path) {
            // Remove the cloud copy first when the result was offloaded
            // (resolved via the StorageManager; no-op if the provider is gone).
            if ($asset->storage_disk && $asset->storage_disk !== 'local') {
                $provider = app(\App\Services\Storage\StorageManager::class)->provider($asset->storage_disk);
                $provider?->delete($asset->file_path);
            }

            if (Storage::disk('results')->exists($asset->file_path)) {
                Storage::disk('results')->delete($asset->file_path);
            }
        }

        $asset->delete();

        Toaster::success(__('Asset deleted successfully.'));
    }

    public function download(int $assetId)
    {
        $asset = AdCreative::where('user_id', auth()->id())->findOrFail($assetId);

        // Offloaded to a cloud bucket — hand off to the shared, storage-aware
        // download route.
        if ($asset->storage_disk && $asset->storage_disk !== 'local') {
            return redirect()->route('user.studio.download', $asset->id);
        }

        if (! $asset->file_path || ! Storage::disk('results')->exists($asset->file_path)) {
            Toaster::error(__('File not found.'));

            return;
        }

        return Storage::disk('results')->download($asset->file_path);
    }

    public function render()
    {
        $query = AdCreative::where('user_id', auth()->id())->completed();

        if ($this->typeFilter === 'image') {
            $query->images();
        } elseif ($this->typeFilter === 'video') {
            $query->videos();
        }

        $query->orderBy('created_at', $this->sortOrder === 'newest' ? 'desc' : 'asc');

        return view('livewire.user.studio.gallery', [
            'assets' => $query->paginate(20),
        ]);
    }
}
