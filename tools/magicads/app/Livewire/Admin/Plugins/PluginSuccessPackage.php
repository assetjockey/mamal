<?php

namespace App\Livewire\Admin\Plugins;

use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Post-payment "thank you" screen for the marketplace bundle (premier) and the
 * premium support subscription.
 *
 * Unlike an individual plugin, these products have nothing to install — the
 * bundle simply unlocks every paid plugin/theme and support is a service — so
 * this screen just confirms the purchase and points the buyer at support.
 */
#[Title('Purchase Complete')]
class PluginSuccessPackage extends Component
{
    public string $slug = '';

    /** Human label for the purchased product. */
    public string $name = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        $this->name = match ($slug) {
            'premier' => __('Premier Package Bundle'),
            'support' => __('Premium Support'),
            default => __('Marketplace Purchase'),
        };
    }

    public function render()
    {
        return view('livewire.admin.plugins.success-package');
    }
}
