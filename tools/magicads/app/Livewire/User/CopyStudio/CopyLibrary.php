<?php

namespace App\Livewire\User\CopyStudio;

use App\Models\AdCopy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Title('Ad Text Library')]
class CopyLibrary extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'p')]
    public string $platformFilter = '';

    #[Url(as: 'fav')]
    public bool $favoritesOnly = false;

    // Deep-link target: when the dashboard (or anywhere) links here with
    // ?focus={id}, the matching card scrolls into view and pulses once.
    #[Url(as: 'focus')]
    public ?int $focus = null;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPlatformFilter() { $this->resetPage(); }
    public function updatingFavoritesOnly() { $this->resetPage(); }

    public function toggleFavorite(int $id): void
    {
        $copy = AdCopy::where('user_id', auth()->id())->find($id);
        if (! $copy) return;
        $copy->update(['is_favorite' => ! $copy->is_favorite]);
    }

    public function delete(int $id): void
    {
        $copy = AdCopy::where('user_id', auth()->id())->find($id);
        if (! $copy) return;
        $copy->delete();
        Toaster::success(__('Ad text deleted.'));
    }

    public function render()
    {
        $query = AdCopy::where('user_id', auth()->id())->completed();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('product_description', 'like', "%{$this->search}%");
            });
        }
        if ($this->platformFilter) {
            $query->where('platform', $this->platformFilter);
        }
        if ($this->favoritesOnly) {
            $query->where('is_favorite', true);
        }

        return view('livewire.user.copy-studio.copy-library', [
            'copies' => $query->latest()->paginate(12),
            'platforms' => config('ad-copy.platforms', []),
        ]);
    }
}
