<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\AdminKey;

#[Title('System Status')]
class Status extends Component
{
    public $license;
    public $username;

    public function mount()
    {
        $keys = AdminKey::first();

        if ($keys) {
            $this->license = $keys->license;
            $this->username = $keys->username;
        }

    }

    public function save()
    {
        $this->validate([
            'license' => 'required|string',
            'username' => 'required|string',
        ]);

        $settings = AdminKey::first();
        
        try {
            if ($settings) {
                $settings->license = $this->license;
                $settings->username = $this->username;
                $settings->save();

                toaster()->success('License activated successfully.');
            }
        } catch(\Exception $e) {
            toaster()->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.status');
    }
}
