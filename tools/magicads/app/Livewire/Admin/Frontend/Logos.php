<?php

namespace App\Livewire\Admin\Frontend;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use App\Models\GeneralSetting;

#[Title('Logos')]
class Logos extends Component
{
    use WithFileUploads;

    public $logo_frontend;
    public $logo_frontend_collapsed;
    public $logo_favicon;
    public $logo_dashboard_light;
    public $logo_dashboard_dark;
    public $logo_dashboard_collapsed_light;
    public $logo_dashboard_collapsed_dark;

    public $logo_frontend_path;
    public $logo_frontend_collapsed_path;
    public $logo_favicon_path;
    public $logo_dashboard_light_path;
    public $logo_dashboard_dark_path;
    public $logo_dashboard_collapsed_light_path;
    public $logo_dashboard_collapsed_dark_path;

    protected $rules = [
        'logo_frontend' => 'nullable|image|max:5048',
        'logo_frontend_collapsed' => 'nullable|image|max:5048',
        'logo_favicon' => 'nullable|mimes:ico|max:1024',
        'logo_dashboard_light' => 'nullable|image|max:5048',
        'logo_dashboard_dark' => 'nullable|image|max:5048',
        'logo_dashboard_collapsed_light' => 'nullable|image|max:5048',
        'logo_dashboard_collapsed_dark' => 'nullable|image|max:5048',
    ];

    public function mount()
    {
        $settings = GeneralSetting::first();
        
        if ($settings) {
            $this->logo_frontend_path = $settings->logo_frontend;
            $this->logo_frontend_collapsed_path = $settings->logo_frontend_collapsed;
            $this->logo_favicon_path = $settings->logo_favicon;
            $this->logo_dashboard_light_path = $settings->logo_dashboard_light;
            $this->logo_dashboard_dark_path = $settings->logo_dashboard_dark;
            $this->logo_dashboard_collapsed_light_path = $settings->logo_dashboard_collapsed_light;
            $this->logo_dashboard_collapsed_dark_path = $settings->logo_dashboard_collapsed_dark;
        }
    }

    public function updatedLogoFrontend()
    {
        $this->validate(['logo_frontend' => 'image|max:5048']);
    }

    public function updatedLogoFrontendCollapsed()
    {
        $this->validate(['logo_frontend_collapsed' => 'image|max:5048']);
    }

    public function updatedLogoFavicon()
    {
        $this->validate(['logo_favicon' => 'mimes:ico|max:1024']);
    }

    public function updatedLogoDashboardLight()
    {
        $this->validate(['logo_dashboard_light' => 'image|max:5048']);
    }

    public function updatedLogoDashboardDark()
    {
        $this->validate(['logo_dashboard_dark' => 'image|max:5048']);
    }

    public function updatedLogoDashboardCollapsedLight()
    {
        $this->validate(['logo_dashboard_collapsed_light' => 'image|max:5048']);
    }

    public function updatedLogoDashboardCollapsedDark()
    {
        $this->validate(['logo_dashboard_collapsed_dark' => 'image|max:5048']);
    }

    public function save()
    {
        $this->validate();

        $settings = GeneralSetting::firstOrCreate([]);
        $data = [];

        if ($this->logo_frontend) {
            $data['logo_frontend'] = $this->logo_frontend->storeAs('uploads/logo', 'logo_frontend.' . $this->logo_frontend->extension(), 'public');
        }

        if ($this->logo_frontend_collapsed) {
            $data['logo_frontend_collapsed'] = $this->logo_frontend_collapsed->storeAs('uploads/logo', 'logo_frontend_collapsed.' . $this->logo_frontend_collapsed->extension(), 'public');
        }

        if ($this->logo_favicon) {
            $data['logo_favicon'] = $this->logo_favicon->storeAs('uploads/logo', 'logo_favicon.' . $this->logo_favicon->extension(), 'public');
        }

        if ($this->logo_dashboard_light) {
            $data['logo_dashboard_light'] = $this->logo_dashboard_light->storeAs('uploads/logo', 'logo_dashboard_light.' . $this->logo_dashboard_light->extension(), 'public');
        }

        if ($this->logo_dashboard_dark) {
            $data['logo_dashboard_dark'] = $this->logo_dashboard_dark->storeAs('uploads/logo', 'logo_dashboard_dark.' . $this->logo_dashboard_dark->extension(), 'public');
        }

        if ($this->logo_dashboard_collapsed_light) {
            $data['logo_dashboard_collapsed_light'] = $this->logo_dashboard_collapsed_light->storeAs('uploads/logo', 'logo_dashboard_collapsed_light.' . $this->logo_dashboard_collapsed_light->extension(), 'public');
        }

        if ($this->logo_dashboard_collapsed_dark) {
            $data['logo_dashboard_collapsed_dark'] = $this->logo_dashboard_collapsed_dark->storeAs('uploads/logo', 'logo_dashboard_collapsed_dark.' . $this->logo_dashboard_collapsed_dark->extension(), 'public');
        }

        $settings->update($data);

        $this->reset(['logo_frontend', 'logo_frontend_collapsed', 'logo_favicon',
                      'logo_dashboard_light', 'logo_dashboard_dark', 'logo_dashboard_collapsed_light', 'logo_dashboard_collapsed_dark']);
        
        $this->mount();

        toaster()->success(__('Logos updated successfully'));
    }

    public function render()
    {
        return view('livewire.admin.frontend.logos');
    }
}
