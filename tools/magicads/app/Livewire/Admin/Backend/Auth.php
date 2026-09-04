<?php

namespace App\Livewire\Admin\Backend;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\SocialMediaSetting;

#[Title('Auth Settings')]
class Auth extends Component
{
    public $socialMedia = false;
    public $facebook = false;
    public $facebook_api_key;
    public $facebook_api_secret;
    public $facebook_url;
    public $twitter = false;
    public $twitter_api_key;
    public $twitter_api_secret;
    public $twitter_url;
    public $google = false;
    public $google_api_key;
    public $google_api_secret;
    public $google_url;
    public $linkedin = false;
    public $linkedin_api_key;
    public $linkedin_api_secret;
    public $linkedin_url;

    public function mount()
    {
        $settings = SocialMediaSetting::first();

        if ($settings) {
            $this->socialMedia = (bool) $settings->social_media;
            $this->facebook = (bool) $settings->facebook;
            $this->facebook_api_key = $settings->facebook_api_key;
            $this->facebook_api_secret = $settings->facebook_api_secret;
            $this->facebook_url = $settings->facebook_url;
            $this->twitter = (bool) $settings->twitter;
            $this->twitter_api_key = $settings->twitter_api_key;
            $this->twitter_api_secret = $settings->twitter_api_secret;
            $this->twitter_url = $settings->twitter_url;
            $this->google = (bool) $settings->google;
            $this->google_api_key = $settings->google_api_key;
            $this->google_api_secret = $settings->google_api_secret;
            $this->google_url = $settings->google_url;
            $this->linkedin = (bool) $settings->linkedin;
            $this->linkedin_api_key = $settings->linkedin_api_key;
            $this->linkedin_api_secret = $settings->linkedin_api_secret;
            $this->linkedin_url = $settings->linkedin_url;
        }
        
    }

    public function updatedSocialMedia()
    {
        $this->validate([
            'socialMedia' => 'required|boolean',
        ]);

        $settings = SocialMediaSetting::firstOrCreate([]);

        $settings->update(['social_media' => $this->socialMedia]);

        if ($this->socialMedia) {
            toaster()->success(__('Social Media Login is enabled'));
        } else {
            toaster()->success(__('Social Media Login is disabled'));
        }      
    }

    public function save()
    {
        $this->validate([
            'facebook_api_key' => 'required_if:facebook,true',
            'facebook_api_secret' => 'required_if:facebook,true',
            'facebook_url' => 'required_if:facebook,true',
            'twitter_api_key' => 'required_if:twitter,true',
            'twitter_api_secret' => 'required_if:twitter,true',
            'twitter_url' => 'required_if:twitter,true',
            'google_api_key' => 'required_if:google,true',
            'google_api_secret' => 'required_if:google,true',
            'google_url' => 'required_if:google,true',
            'linkedin_api_key' => 'required_if:linkedin,true',
            'linkedin_api_secret' => 'required_if:linkedin,true',
            'linkedin_url' => 'required_if:linkedin,true',
        ]);

        $settings = SocialMediaSetting::firstOrCreate([]);

        $settings->update([
            'facebook' => $this->facebook,
            'facebook_api_key' => $this->facebook_api_key,
            'facebook_api_secret' => $this->facebook_api_secret,
            'facebook_url' => $this->facebook_url,
            'twitter' => $this->twitter,
            'twitter_api_key' => $this->twitter_api_key,
            'twitter_api_secret' => $this->twitter_api_secret,
            'twitter_url' => $this->twitter_url,
            'google' => $this->google,
            'google_api_key' => $this->google_api_key,
            'google_api_secret' => $this->google_api_secret,
            'google_url' => $this->google_url,
            'linkedin' => $this->linkedin,
            'linkedin_api_key' => $this->linkedin_api_key,
            'linkedin_api_secret' => $this->linkedin_api_secret,
            'linkedin_url' => $this->linkedin_url,
        ]);

        toaster()->success(__('Settings were saved successfully'));
    }


    public function render()
    {
        return view('livewire.admin.backend.auth');
    }

}

