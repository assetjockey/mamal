<?php

namespace App\Livewire\User\Brands;

use App\Models\Brand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

#[Title('Brand Setup')]
class BrandEditor extends Component
{
    use WithFileUploads;

    public ?Brand $brand = null;
    public ?int $brandId = null;

    // Step 1 — identity
    public string $name = '';
    public string $industry = '';
    public string $website_url = '';
    public string $tagline = '';
    public string $description = '';

    // Step 2 — logo
    public ?string $logo_path = null;
    public $logo;

    // Step 3 — colors
    public ?string $primary_color = '#6366f1';
    public ?string $secondary_color = '#ec4899';
    public ?string $accent_color = '#f59e0b';

    // Step 4 — advanced
    public ?string $font_family = null;
    public ?string $tone_of_voice = null;
    public ?string $target_audience = null;
    public array $brand_values = [];
    public array $social_handles = [
        'instagram' => '',
        'tiktok' => '',
        'linkedin' => '',
        'facebook' => '',
        'x' => '',
        'youtube' => '',
    ];
    public array $ad_platforms = [];

    public bool $is_default = false;
    public bool $is_active = true;

    public bool $importing = false;
    public string $importState = 'idle'; // idle | loading | done | error

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->brand = Brand::where('user_id', auth()->id())->findOrFail($id);
            $this->brandId = $this->brand->id;
            $this->fillFromBrand($this->brand);
        }
    }

    protected function fillFromBrand(Brand $brand): void
    {
        $this->name = $brand->name ?? '';
        $this->industry = $brand->industry ?? '';
        $this->website_url = $brand->website_url ?? '';
        $this->tagline = $brand->tagline ?? '';
        $this->description = $brand->description ?? '';
        $this->logo_path = $brand->logo_path;
        $this->primary_color = $brand->primary_color ?? '#6366f1';
        $this->secondary_color = $brand->secondary_color ?? '#ec4899';
        $this->accent_color = $brand->accent_color ?? '#f59e0b';
        $this->font_family = $brand->font_family;
        $this->tone_of_voice = $brand->tone_of_voice;
        $this->target_audience = $brand->target_audience ?? '';
        $this->brand_values = $brand->brand_values ?? [];
        $this->social_handles = array_merge($this->social_handles, $brand->social_handles ?? []);
        $this->ad_platforms = $brand->ad_platforms ?? [];
        $this->is_default = (bool) $brand->is_default;
        $this->is_active = (bool) $brand->is_active;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'industry' => 'nullable|string|max:60',
            'website_url' => 'nullable|url|max:255',
            'tagline' => 'nullable|string|max:180',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|max:4096',
            'primary_color' => 'nullable|regex:/^#([a-fA-F0-9]{6})$/',
            'secondary_color' => 'nullable|regex:/^#([a-fA-F0-9]{6})$/',
            'accent_color' => 'nullable|regex:/^#([a-fA-F0-9]{6})$/',
            'font_family' => 'nullable|string|max:60',
            'tone_of_voice' => 'nullable|string|max:40',
            'target_audience' => 'nullable|string|max:500',
        ];
    }

    public function importFromWebsite(): void
    {
        $this->validate(['website_url' => 'required|url']);

        $this->importState = 'loading';

        try {
            $response = Http::timeout(8)->get($this->website_url);
            $html = (string) $response->body();

            if (preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $m)) {
                $title = trim(html_entity_decode($m[1]));
                if (empty($this->name)) {
                    $this->name = mb_substr($title, 0, 120);
                }
            }
            if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                $desc = trim(html_entity_decode($m[1]));
                if (empty($this->description)) {
                    $this->description = mb_substr($desc, 0, 2000);
                }
            }
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
                // Best-effort; we intentionally don't download remote images here.
            }

            $this->importState = 'done';
            Toaster::success(__('Imported available details from website.'));
        } catch (\Throwable $e) {
            $this->importState = 'error';
            Toaster::warning(__('Could not import from website. Please fill the details manually.'));
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->logo) {
            $path = $this->logo->store('brands/' . auth()->id(), 'public');

            if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
                Storage::disk('public')->delete($this->logo_path);
            }
            $this->logo_path = $path;
            $this->logo = null;
        }

        $payload = [
            'user_id' => auth()->id(),
            'name' => $this->name,
            'industry' => $this->industry ?: null,
            'website_url' => $this->website_url ?: null,
            'tagline' => $this->tagline ?: null,
            'description' => $this->description ?: null,
            'logo_path' => $this->logo_path,
            'primary_color' => $this->primary_color ?: null,
            'secondary_color' => $this->secondary_color ?: null,
            'accent_color' => $this->accent_color ?: null,
            'font_family' => $this->font_family ?: null,
            'tone_of_voice' => $this->tone_of_voice ?: null,
            'target_audience' => $this->target_audience ?: null,
            'brand_values' => $this->brand_values,
            'social_handles' => array_filter($this->social_handles, fn ($v) => filled($v)),
            'ad_platforms' => $this->ad_platforms,
            'is_active' => $this->is_active,
        ];

        if ($this->brand) {
            $this->brand->update($payload);
        } else {
            $this->brand = Brand::create($payload);
            $this->brandId = $this->brand->id;
        }

        if ($this->is_default) {
            Brand::where('user_id', auth()->id())
                ->where('id', '!=', $this->brand->id)
                ->update(['is_default' => false]);
            $this->brand->update(['is_default' => true]);
        } else {
            $this->brand->update(['is_default' => false]);
        }

        Toaster::success(__('Brand saved successfully.'));
        $this->redirectRoute('user.brands.index', navigate: true);
    }

    public function removeLogo(): void
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            Storage::disk('public')->delete($this->logo_path);
        }
        $this->logo_path = null;
        Toaster::success(__('Logo removed.'));
    }

    public function toggleBrandValue(string $value): void
    {
        if (in_array($value, $this->brand_values)) {
            $this->brand_values = array_values(array_diff($this->brand_values, [$value]));
        } else {
            $this->brand_values[] = $value;
        }
    }

    public function toggleAdPlatform(string $platform): void
    {
        if (in_array($platform, $this->ad_platforms)) {
            $this->ad_platforms = array_values(array_diff($this->ad_platforms, [$platform]));
        } else {
            $this->ad_platforms[] = $platform;
        }
    }

    public function getCompletionScoreProperty(): int
    {
        $steps = [
            // Step 1 — Brand Name
            filled($this->name),
            // Step 2 — Logo
            filled($this->logo_path),
            // Step 3 — Colors (all three set)
            filled($this->primary_color) && filled($this->secondary_color) && filled($this->accent_color),
            // Step 4 — Advanced (any meaningful field filled)
            filled($this->tone_of_voice)
                || filled($this->target_audience)
                || count($this->brand_values) > 0
                || count($this->ad_platforms) > 0,
        ];

        $done = count(array_filter($steps));
        return (int) round(($done / count($steps)) * 100);
    }

    public function render()
    {
        return view('livewire.user.brands.brand-editor');
    }
}
