<?php

namespace App\Livewire\Admin\Themes;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

/**
 * Post-payment "thank you" + one-click install screen.
 *
 * Reached after Stripe verifies the payment and the marketplace flags the
 * theme as purchased. The buyer installs the freshly-purchased theme here.
 */
#[Title('Purchase Complete')]
class ThemeSuccess extends Component
{
    public string $slug = '';

    public array $theme = [];

    public ?Extension $extension = null;

    public bool $processing = false;

    public bool $installed = false;

    public function mount(string $slug): void
    {
        $extensions = new ExtensionController;
        $this->slug = $slug;
        $this->theme = $extensions->search($slug);

        if (empty($this->theme)) {
            Toaster::error(__('That theme could not be found.'));
            $this->redirectRoute('admin.themes', navigate: true);

            return;
        }

        $this->extension = Extension::where('slug', $slug)->first();
        $this->installed = (bool) ($this->extension?->installed);
    }

    public function install(): void
    {
        $this->processing = true;

        $extensions = new ExtensionController;
        $response = $extensions->installTheme($this->slug);

        $this->processing = false;

        if (! empty($response['status'])) {
            $this->installed = true;
            $this->extension = Extension::where('slug', $this->slug)->first();
            Toaster::success($response['message'] ?? __('Theme installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Theme installation failed.'));
        }
    }

    public function render()
    {
        return view('livewire.admin.themes.success');
    }
}
