<?php

namespace App\Livewire\Admin\Frontend;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\FrontendSetting;

#[Title('Frontend Settings')]
class Settings extends Component
{
    public $frontend_page = true;
    public $custom_url_enabled = false;
    public $custom_url = '';
    public $twitter = '';
    public $facebook = '';
    public $linkedin = '';
    public $instagram = '';
    public $youtube = '';
    public $tiktok = '';

    public function mount()
    {
        $settings = FrontendSetting::first();
        
        if ($settings) {
            $this->frontend_page = (bool) $settings->frontend_page;
            $this->custom_url_enabled = (bool) $settings->custom_url_enabled;
            $this->custom_url = $settings->custom_url ?? '';
            $this->twitter = $settings->twitter ?? '';
            $this->facebook = $settings->facebook ?? '';
            $this->linkedin = $settings->linkedin ?? '';
            $this->instagram = $settings->instagram ?? '';
            $this->youtube = $settings->youtube ?? '';
            $this->tiktok = $settings->tiktok ?? '';
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'frontend_page' => 'boolean',
            'custom_url_enabled' => 'boolean',
            'custom_url' => $this->custom_url_enabled ? 'required|url' : 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'instagram' => 'nullable|url',
            'youtube' => 'nullable|url',
            'tiktok' => 'nullable|url',
        ]);

        FrontendSetting::updateOrCreate(
            ['id' => 1],
            $validated
        );

        toaster()->success( __('Frontend Settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.admin.frontend.settings');
    }
}
