<?php

namespace App\Livewire\Admin\Backend;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;
use App\Models\AdminKey;
use App\Models\GeneralSetting;

#[Title('Global Settings')]
class GlobalSettings extends Component
{
    public $google_recaptcha = false;
    public $google_analytics_homepage = false;
    public $google_analytics_dashboard = false;
    public $google_maps = false;
    public $google_maps_api_key;
    public $google_analytics_property_id;
    public $google_analytics_service_credentials;
    public $google_analytics_tracking_id;
    public $google_recaptcha_site_key;
    public $google_recaptcha_secret_key;
    public $default_theme;
    public $website_name;
    public $website_url;
    public $time_zone;

    public function mount()
    {

        $keys = AdminKey::first();
        
        if ($keys) {
        
            $this->google_maps_api_key = $keys->google_maps_api_key;
            $this->google_analytics_property_id = $keys->google_analytics_property_id;
            $this->google_analytics_service_credentials = $keys->google_analytics_service_credentials;
            $this->google_analytics_tracking_id = $keys->google_analytics_tracking_id;
            $this->google_recaptcha_site_key = $keys->google_recaptcha_site_key;
            $this->google_recaptcha_secret_key = $keys->google_recaptcha_secret_key;
            
        }   

        $settings = GeneralSetting::first();

        if ($settings) {
            $this->google_recaptcha = (bool) $settings->google_recaptcha;
            $this->google_analytics_homepage = (bool) $settings->google_analytics_homepage;
            $this->google_analytics_dashboard = (bool) $settings->google_analytics_dashboard;
            $this->google_maps = (bool) $settings->google_maps;
            $this->default_theme = $settings->default_theme;
        }

        $this->website_name = config('app.name');
        $this->website_url = config('app.url');
        $this->time_zone = config('app.timezone');
    }

    public function save()
    {
        $this->validate([
            'google_maps_api_key' => 'required_if:google_maps,true',
            'google_analytics_property_id' => 'required_if:google_analytics_dashboard,true',
            'google_analytics_service_credentials' => 'required_if:google_analytics_dashboard,true',
            'google_analytics_tracking_id' => 'required_if:google_analytics_homepage,true',
            'google_recaptcha_site_key' => 'required_if:google_recaptcha,true',
            'google_recaptcha_secret_key' => 'required_if:google_recaptcha,true',
            'website_name' => 'required|string|max:255',
            'website_url' => 'required|string|max:100',
        ]);

        $keys = AdminKey::first();
        
        if ($keys) {
            $keys->update([
                'google_maps_api_key' => $this->google_maps_api_key,
                'google_analytics_property_id' => $this->google_analytics_property_id,
                'google_analytics_service_credentials' => $this->google_analytics_service_credentials,
                'google_analytics_tracking_id' => $this->google_analytics_tracking_id,
                'google_recaptcha_site_key' => $this->google_recaptcha_site_key,
                'google_recaptcha_secret_key' => $this->google_recaptcha_secret_key,
            ]);

        } else {
             toaster()->error(__('There was an error, please try again'));
        }  

        $settings = GeneralSetting::first();

        if ($settings) {
            $settings->update([
                'google_recaptcha' => $this->google_recaptcha, 
                'google_analytics_homepage' => $this->google_analytics_homepage, 
                'google_analytics_dashboard' => $this->google_analytics_dashboard, 
                'google_maps' => $this->google_maps, 
                'default_theme' => $this->default_theme,
            ]);

        } else {
             toaster()->error(__('There was an error, please try again'));
        }

        $this->updateEnvFile('APP_NAME', "'{$this->website_name}'");
        $this->updateEnvFile('APP_URL', $this->website_url);
        $this->updateEnvFile('APP_TIMEZONE', $this->time_zone);
        
        toaster()->success(__('Settings were saved successfully'));
    }

    private function updateEnvFile($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
            
            file_put_contents($path, $content);
        }
    }


    public function render()
    {
        return view('livewire.admin.backend.global-settings');
    }

}

