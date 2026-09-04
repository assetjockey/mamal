<?php

namespace App\Livewire\Admin\Backend;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\CookieSetting;
use Masmerise\Toaster\Toaster;

#[Title('GDPR Settings')]
class Gdpr extends Component
{
    public bool $enable_cookies = false;
    public bool $enable_dark_mode = false;
    public bool $disable_page_interaction = false;
    public bool $hide_from_bots = true;
    public $consent_modal_layout = 'box';
    public $consent_modal_position = 'bottom right';
    public $preferences_modal_layout = 'box';
    public $preferences_modal_position = 'right';
    public $cookie_valid_days = 7;

    public function mount()
    {
        $settings = CookieSetting::first();
        if ($settings) {
            $this->enable_cookies = (bool) $settings->enable_cookies;
            $this->enable_dark_mode = (bool) $settings->enable_dark_mode;
            $this->disable_page_interaction = (bool) $settings->disable_page_interaction;
            $this->hide_from_bots = (bool) $settings->hide_from_bots;
            $this->consent_modal_layout = $settings->consent_modal_layouts;
            $this->consent_modal_position = $settings->consent_modal_position;
            $this->preferences_modal_layout = $settings->preferences_modal_layout;
            $this->preferences_modal_position = $settings->preferences_modal_position;
            $this->cookie_valid_days = $settings->cookie_valid_days;
        }
    }

    public function save()
    {
        $this->validate([
            'cookie_valid_days' => 'required|integer|min:1|max:365',
            'consent_modal_layout' => 'required|string',
            'consent_modal_position' => 'required|string',
            'preferences_modal_layout' => 'required|string',
            'preferences_modal_position' => 'required|string',
        ]);

        CookieSetting::updateOrCreate(
            ['id' => 1],
            [
                'enable_cookies' => $this->enable_cookies,
                'enable_dark_mode' => $this->enable_dark_mode,
                'disable_page_interaction' => $this->disable_page_interaction,
                'hide_from_bots' => $this->hide_from_bots,
                'consent_modal_layouts' => $this->consent_modal_layout,
                'consent_modal_position' => $this->consent_modal_position,
                'preferences_modal_layout' => $this->preferences_modal_layout,
                'preferences_modal_position' => $this->preferences_modal_position,
                'cookie_valid_days' => $this->cookie_valid_days,
            ]
        );

        Toaster::success('GDPR settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.admin.backend.gdpr');
    }
}
