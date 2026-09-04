<?php

namespace App\Livewire\User\Brands;

use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

#[Title('Brands')]
class BrandsIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'recent'; // recent | name | completion

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setDefault(int $brandId): void
    {
        $user = auth()->user();
        $brand = Brand::where('user_id', $user->id)->findOrFail($brandId);

        Brand::where('user_id', $user->id)->update(['is_default' => false]);
        $brand->update(['is_default' => true]);

        Toaster::success(__(':name is now your default brand.', ['name' => $brand->name]));
    }

    public function duplicate(int $brandId): void
    {
        $brand = Brand::where('user_id', auth()->id())->findOrFail($brandId);

        $copy = $brand->replicate();
        $copy->name = $brand->name . ' ' . __('(Copy)');
        $copy->is_default = false;
        $copy->slug = null;
        $copy->save();

        Toaster::success(__('Brand duplicated.'));
    }

    public function delete(int $brandId): void
    {
        $brand = Brand::where('user_id', auth()->id())->findOrFail($brandId);

        if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
            Storage::disk('public')->delete($brand->logo_path);
        }
        if ($brand->cover_path && Storage::disk('public')->exists($brand->cover_path)) {
            Storage::disk('public')->delete($brand->cover_path);
        }

        $brand->delete();
        Toaster::success(__('Brand deleted.'));
    }

    public function render()
    {
        $query = Brand::where('user_id', auth()->id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('industry', 'like', "%{$this->search}%")
                  ->orWhere('tagline', 'like', "%{$this->search}%");
            });
        }

        match ($this->sortBy) {
            'name'       => $query->orderBy('name'),
            'completion' => $query->orderByDesc('updated_at'),
            default      => $query->orderByDesc('is_default')->orderByDesc('updated_at'),
        };

        return view('livewire.user.brands.brands-index', [
            'brands' => $query->paginate(12),
            'totalBrands' => Brand::where('user_id', auth()->id())->count(),
            'defaultBrand' => Brand::where('user_id', auth()->id())->where('is_default', true)->first(),
        ]);
    }
}
