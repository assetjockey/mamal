<?php

namespace App\Livewire\Admin\Plugins;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use App\Models\GeneralSetting;
use App\Services\Package\InstallPackageService;
use App\Services\Package\UninstallPackageService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

/**
 * Plugin detail + checkout screen.
 *
 * Mirrors {@see \App\Livewire\Admin\Themes\ThemeCheckout}. Depending on the
 * buyer's relationship to the plugin it exposes one of several actions: Buy,
 * Install, Update, or Uninstall. Buying stashes the purchase context in the
 * session and forwards to the Stripe gateway screen; the install/update/
 * uninstall actions resolve server-side through the package services.
 */
#[Title('Plugin Details')]
class PluginCheckout extends Component
{
    public string $slug = '';

    /** Marketplace metadata for the plugin. */
    public array $plugin = [];

    /** Parsed feature tags shown as a checklist. */
    public array $tags = [];

    /** Local install/purchase record, if any. */
    public ?Extension $extension = null;

    /** Whether the buyer's license tier is sufficient for this plugin. */
    public bool $approved = true;

    /** Minimum app version required, surfaced when the buyer is out of date. */
    public ?string $approvedVersion = null;

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
        $this->tags = collect(explode(',', (string) ($this->plugin['tags'] ?? '')))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        // App-version gate: the marketplace tells us the minimum version this
        // plugin supports; compare it against the running app version.
        $this->approvedVersion = $this->plugin['minimum_app_version'] ?? null;
        $current = explode('v', (string) config('app.version'));
        $currentVersion = (float) ($current[1] ?? $current[0] ?? 0);
        $this->approved = (float) ($this->approvedVersion ?? 0) <= $currentVersion;
    }

    /** True when the local version trails the marketplace version. */
    public function getUpgradableProperty(): bool
    {
        if (! $this->extension) {
            return false;
        }

        return (float) $this->extension->version < (float) ($this->plugin['version'] ?? 0);
    }

    public function getPurchasedProperty(): bool
    {
        return (bool) ($this->extension?->purchased) || (bool) ($this->plugin['is_free'] ?? false);
    }

    public function getInstalledProperty(): bool
    {
        return (bool) ($this->extension?->installed);
    }

    public function getOnlyForExtendedProperty(): bool
    {
        return (bool) ($this->plugin['only_for_extended'] ?? false);
    }

    /**
     * True when this paid plugin is free to install for the buyer because they
     * hold an Extended License and the plugin is flagged free_for_extended.
     */
    public function getExtendedInstallProperty(): bool
    {
        if ((bool) ($this->plugin['is_free'] ?? false)) {
            return false;
        }

        if (! (bool) ($this->plugin['free_for_extended'] ?? false)) {
            return false;
        }

        return $this->hasExtendedLicense();
    }

    /**
     * Detect whether the activated license is an Extended License.
     */
    private function hasExtendedLicense(): bool
    {
        $type = (string) (GeneralSetting::query()->value('license_type') ?? '');

        return str_contains(strtolower($type), 'extended');
    }

    /**
     * Stash the purchase context and hand off to the gateway loading screen,
     * which kicks off the Stripe Checkout Session.
     */
    public function buy(): void
    {
        if (empty($this->plugin) || ! $this->approved || $this->onlyForExtended) {
            return;
        }

        if (($this->plugin['is_free'] ?? false) || $this->extendedInstall) {
            $this->install();

            return;
        }

        session()->put('name', $this->slug);
        session()->put('type', 'extension');
        session()->put('amount', $this->plugin['price'] ?? 0);
        session()->put('extension_name', $this->plugin['name'] ?? $this->slug);

        $this->redirectRoute('admin.plugins.gateway');
    }

    /**
     * Download, extract and wire up the plugin (also used for updates).
     */
    public function install(): void
    {
        // Refuse to install a paid plugin the buyer neither owns nor qualifies
        // for via Extended License (free_for_extended).
        if (! $this->purchased && ! $this->installed && ! $this->extendedInstall) {
            Toaster::error(__('This plugin requires a purchase before it can be installed.'));

            return;
        }

        $service = new InstallPackageService;
        $response = $service->install($this->slug);

        $this->extension = Extension::where('slug', $this->slug)->first();

        if (! empty($response['status'])) {
            Toaster::success($response['message'] ?? __('Plugin installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Plugin installation failed.'));
        }
    }

    /**
     * Remove an installed plugin.
     */
    public function uninstall(): void
    {
        $service = new UninstallPackageService;
        $response = $service->uninstall($this->slug);

        $this->extension = Extension::where('slug', $this->slug)->first();

        if (! empty($response['status'])) {
            Toaster::success($response['message'] ?? __('Plugin uninstalled successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Plugin uninstall failed.'));
        }
    }

    public function render()
    {
        return view('livewire.admin.plugins.checkout');
    }
}
