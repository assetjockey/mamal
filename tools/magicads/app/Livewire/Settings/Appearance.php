<?php

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Appearance extends Component
{
    /**
     * Render the component using the active theme's view finder.
     *
     * See Profile::render() for the rationale behind explicitly resolving
     * the view by name instead of relying on Livewire's default fallback.
     */
    public function render(): View
    {
        return view('livewire.settings.appearance');
    }
}
