<?php

namespace App\Livewire\Admin\Frontend;

use App\Models\GoogleAdsense as GoogleAdsenseModel;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Google AdSense')]
class GoogleAdsense extends Component
{
    public bool $enabled = false;
    public ?string $publisher_id = null;
    public bool $auto_ads = false;

    public ?string $slot_home_top = null;
    public ?string $slot_home_bottom = null;
    public ?string $slot_blog_top = null;
    public ?string $slot_blog_article = null;
    public ?string $slot_blog_bottom = null;

    public function mount(): void
    {
        $settings = GoogleAdsenseModel::first();

        if ($settings) {
            $this->enabled = (bool) $settings->enabled;
            $this->publisher_id = $settings->publisher_id;
            $this->auto_ads = (bool) $settings->auto_ads;
            $this->slot_home_top = $settings->slot_home_top;
            $this->slot_home_bottom = $settings->slot_home_bottom;
            $this->slot_blog_top = $settings->slot_blog_top;
            $this->slot_blog_article = $settings->slot_blog_article;
            $this->slot_blog_bottom = $settings->slot_blog_bottom;
        }
    }

    protected function rules(): array
    {
        return [
            'enabled' => 'boolean',
            // ca-pub- followed by 16 digits is the standard AdSense publisher ID.
            'publisher_id' => ['nullable', 'string', 'regex:/^ca-pub-\d{16}$/'],
            'auto_ads' => 'boolean',
            'slot_home_top' => 'nullable|string|max:30',
            'slot_home_bottom' => 'nullable|string|max:30',
            'slot_blog_top' => 'nullable|string|max:30',
            'slot_blog_article' => 'nullable|string|max:30',
            'slot_blog_bottom' => 'nullable|string|max:30',
        ];
    }

    protected function messages(): array
    {
        return [
            'publisher_id.regex' => __('The publisher ID must look like ca-pub-XXXXXXXXXXXXXXXX (16 digits).'),
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Can't switch ads on without a publisher ID — guide the admin instead
        // of silently saving an inert "enabled" state.
        if ($this->enabled && blank($this->publisher_id)) {
            $this->addError('publisher_id', __('Add your AdSense publisher ID before enabling ads.'));

            return;
        }

        GoogleAdsenseModel::updateOrCreate(['id' => 1], $validated);

        toaster()->success(__('Google AdSense settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.admin.frontend.google-adsense', [
            'placements' => GoogleAdsenseModel::PLACEMENTS,
        ]);
    }
}
