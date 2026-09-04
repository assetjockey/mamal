<?php

namespace App\Livewire\Admin\Frontend;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\SeoSetting;

#[Title('SEO Manager')]
class Seo extends Component
{
    public $home_title;
    public $home_description;
    public $home_keywords;
    public $home_author;
    public $home_url;

    public $login_title;
    public $login_description;
    public $login_keywords;
    public $login_author;
    public $login_url;

    public $registration_title;
    public $registration_description;
    public $registration_keywords;
    public $registration_author;
    public $registration_url;

    public function mount()
    {
        $settings = SeoSetting::first();
        
        if ($settings) {
            $this->home_title = $settings->home_title;
            $this->home_description = $settings->home_description;
            $this->home_keywords = $settings->home_keywords;
            $this->home_author = $settings->home_author;
            $this->home_url = $settings->home_url;

            $this->login_title = $settings->login_title;
            $this->login_description = $settings->login_description;
            $this->login_keywords = $settings->login_keywords;
            $this->login_author = $settings->login_author;
            $this->login_url = $settings->login_url;

            $this->registration_title = $settings->register_title;
            $this->registration_description = $settings->register_description;
            $this->registration_keywords = $settings->register_keywords;
            $this->registration_author = $settings->register_author;
            $this->registration_url = $settings->register_url;
        }
    }

    protected function rules()
    {
        return [
            'home_title' => 'nullable|string|max:255',
            'home_description' => 'nullable|string',
            'home_keywords' => 'nullable|string',
            'home_author' => 'nullable|string|max:255',
            'home_url' => 'nullable|url|max:255',
            'login_title' => 'nullable|string|max:255',
            'login_description' => 'nullable|string',
            'login_keywords' => 'nullable|string',
            'login_author' => 'nullable|string|max:255',
            'login_url' => 'nullable|url|max:255',
            'registration_title' => 'nullable|string|max:255',
            'registration_description' => 'nullable|string',
            'registration_keywords' => 'nullable|string',
            'registration_author' => 'nullable|string|max:255',
            'registration_url' => 'nullable|url|max:255',
        ];
    }

    public function save()
    {
        $this->validate();

        SeoSetting::updateOrCreate(
            ['id' => 1],
            [
                'home_title' => $this->home_title,
                'home_description' => $this->home_description,
                'home_keywords' => $this->home_keywords,
                'home_author' => $this->home_author,
                'home_url' => $this->home_url,
                'login_title' => $this->login_title,
                'login_description' => $this->login_description,
                'login_keywords' => $this->login_keywords,
                'login_author' => $this->login_author,
                'login_url' => $this->login_url,
                'register_title' => $this->registration_title,
                'register_description' => $this->registration_description,
                'register_keywords' => $this->registration_keywords,
                'register_author' => $this->registration_author,
                'register_url' => $this->registration_url,
            ]
        );

        toaster()->success(__('SEO settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.admin.frontend.seo');
    }
}
