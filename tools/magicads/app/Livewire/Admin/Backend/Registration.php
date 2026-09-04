<?php

namespace App\Livewire\Admin\Backend;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Config;
use App\Models\GeneralSetting;

#[Title('Registration Settings')]
class Registration extends Component
{
    public $newUser = true;
    public $emailVerification = false;
    public $subscribeDuringRegistration = false;

    public function mount()
    {
        $settings = GeneralSetting::first();

        if ($settings) {
            $this->newUser = ($settings->user_registration) ? true : false;
            $this->emailVerification = ($settings->email_verification) ? true : false;
            $this->subscribeDuringRegistration = ($settings->user_registration_subscription) ? true : false;
        }
        
    }

    public function updatedNewUser()
    {
        $this->validate([
            'newUser' => 'required|boolean',
        ]);

        $settings = GeneralSetting::first();
        
        if ($settings) {
            $settings->update(['user_registration' => $this->newUser]);
        }

        if ($this->newUser) {
            toaster()->success(__('User Registration is enabled'));
        } else {
            toaster()->success(__('User Registration is disabled'));
        }      
    }

    public function updatedSubscribeDuringRegistration()
    {
        $this->validate([
            'subscribeDuringRegistration' => 'required|boolean',
        ]);

        $settings = GeneralSetting::first();
        
        if ($settings) {
            $settings->update(['user_registration_subscription' => $this->subscribeDuringRegistration]);
        }

        if ($this->subscribeDuringRegistration) {
            toaster()->success(__('Subscription at Registration is enabled'));
        } else {
            toaster()->success(__('Subscription at Registration is disabled'));
        }      
    }

    public function updatedEmailVerification()
    {
        $this->validate([
            'emailVerification' => 'required|boolean',
        ]);

        $settings = GeneralSetting::first();
        
        if ($settings) {
            $settings->update(['email_verification' => $this->emailVerification]);
        }

        if ($this->emailVerification) {
            toaster()->success(__('Email Verification is enabled'));
        } else {
            toaster()->success(__('Email Verification is disabled'));
        }      
    }

    public function render()
    {
        return view('livewire.admin.backend.registration');
    }

}

