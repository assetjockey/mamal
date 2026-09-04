<?php

namespace App\Livewire\Admin\Themes;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use App\Models\GeneralSetting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

/**
 * Theme detail + checkout screen.
 *
 * Mirrors the legacy DaVinci "checkout" blade, re-expressed as a full-page
 * Livewire component. Depending on the buyer's relationship to the theme it
 * exposes one of four actions: Buy, Install, Update or Activate. Buying stashes
 * the purchase context in the session and forwards to the Stripe gateway screen;
 * the other three resolve entirely server-side through the ExtensionController.
 */
#[Title('Theme Details')]
class ThemeCheckout extends Component
{
    public string $slug = '';

    /** Marketplace metadata for the theme. */
    public array $theme = [];

    /** Parsed feature tags shown as a checklist. */
    public array $tags = [];

    /** Local install/purchase record, if any. */
    public ?Extension $extension = null;

    /** Reflects the busy state of the install/update/activate buttons. */
    public bool $processing = false;

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
        $this->tags = collect(explode(',', (string) ($this->theme['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();
    }

    /** True when the local version trails the marketplace version. */
    public function getUpgradableProperty(): bool
    {
        if (! $this->extension) {
            return false;
        }

        return (float) $this->extension->version < (float) ($this->theme['version'] ?? 0);
    }

    public function getPurchasedProperty(): bool
    {
        return (bool) ($this->extension?->purchased);
    }

    public function getInstalledProperty(): bool
    {
        return (bool) ($this->extension?->installed);
    }

    /**
     * Stash the purchase context and hand off to the gateway loading screen,
     * which kicks off the Stripe Checkout Session.
     */
    public function buy(): void
    {
        if (empty($this->theme) || ($this->theme['slug'] ?? '') === 'default') {
            return;
        }

        session()->put('name', $this->slug);
        session()->put('type', 'theme');
        session()->put('amount', $this->theme['price'] ?? 0);

        $this->redirectRoute('admin.themes.gateway');
    }

    /**
     * Download, extract and activate the theme (also used for updates).
     */
    public function install(): void
    {
        $this->processing = true;

        $extensions = new ExtensionController;
        $response = $extensions->installTheme($this->slug);

        $this->processing = false;

        if (! empty($response['status'])) {
            $this->extension = Extension::where('slug', $this->slug)->first();
            Toaster::success($response['message'] ?? __('Theme installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Theme installation failed.'));
        }
    }

    /**
     * Set an already-purchased & installed theme as the active one.
     */
    public function activate(): void
    {
        $extensions = new ExtensionController;
        $extensions->checkPayment($this->slug);

        $this->extension = Extension::where('slug', $this->slug)->first();

        Toaster::success(__('Theme activated successfully.'));
    }

    public function render()
    {
        return view('livewire.admin.themes.checkout', [
            'settings' => GeneralSetting::first(),
        ]);
    }
}
