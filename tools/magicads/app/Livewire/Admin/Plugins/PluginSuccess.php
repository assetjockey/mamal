<?php

namespace App\Livewire\Admin\Plugins;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use App\Services\Package\InstallPackageService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

/**
 * Post-payment "thank you" + one-click install screen for an individual plugin.
 *
 * Mirrors {@see \App\Livewire\Admin\Themes\ThemeSuccess}. Reached after Stripe
 * verifies the payment and the marketplace flags the plugin as purchased.
 */
#[Title('Purchase Complete')]
class PluginSuccess extends Component
{
    public string $slug = '';

    public array $plugin = [];

    public ?Extension $extension = null;

    public bool $installed = false;

    public function mount(string $slug): void
    {
        $extensions = new ExtensionController;
        $this->slug = $slug;
        $this->plugin = $extensions->search($slug);

        if (empty($this->plugin)) {
            Toaster::error(__('That plugin could not be found.'));
            $this->redirectRoute('admin.plugins', navigate: true);

            return;
        }

        $this->extension = Extension::where('slug', $slug)->first();
        $this->installed = (bool) ($this->extension?->installed);
    }

    public function install(): void
    {
        $service = new InstallPackageService;
        $response = $service->install($this->slug);

        if (! empty($response['status'])) {
            $this->installed = true;
            $this->extension = Extension::where('slug', $this->slug)->first();
            Toaster::success($response['message'] ?? __('Plugin installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Plugin installation failed.'));
        }
    }

    public function render()
    {
        return view('livewire.admin.plugins.success');
    }
}
