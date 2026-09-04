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
 * Plugin marketplace catalog.
 *
 * Mirrors {@see \App\Livewire\Admin\Themes\Themes}: the catalog is fetched once
 * on mount and tab/search filtering happens client-side (Alpine) for an instant
 * feel. Free plugins install/uninstall straight from the card; paid plugins
 * route to the checkout screen.
 */
#[Title('Plugins')]
class Plugins extends Component
{
    /** Marketplace plugin catalog, fetched once on mount. */
    public array $extensions = [];

    /** Slugs of plugins installed locally. */
    public array $installedSlugs = [];

    /** Slugs of plugins the buyer owns (purchased or free). */
    public array $purchasedSlugs = [];

    /** Local version per slug, used to surface "update available". */
    public array $detailVersions = [];

    /** Whether the activated license is an Envato Extended License. */
    public bool $isExtendedLicense = false;

    public function mount(): void
    {
        $this->isExtendedLicense = $this->resolveExtendedLicense();
        $this->loadPlugins();
    }

    /**
     * Detect whether the activated license is an Extended License. The license
     * tier label is stashed on the general settings at activation time.
     */
    private function resolveExtendedLicense(): bool
    {
        $type = (string) (GeneralSetting::query()->value('license_type') ?? '');

        return str_contains(strtolower($type), 'extended');
    }

    /**
     * Pull the plugin catalog from the marketplace and snapshot the local
     * install/purchase state used by the client-side filter and the card CTAs.
     */
    private function loadPlugins(): void
    {
        $extensionController = new ExtensionController;
        $this->extensions = $extensionController->extensions();

        $this->refreshState();
    }

    /**
     * Refresh the install/purchase/version snapshots from the database.
     */
    private function refreshState(): void
    {
        $details = Extension::query()->where('is_theme', false)->get();

        $this->installedSlugs = $details->where('installed', true)->pluck('slug')->values()->all();
        $this->purchasedSlugs = $details->filter(fn ($e) => $e->purchased || $e->is_free)->pluck('slug')->values()->all();
        $this->detailVersions = $details->pluck('version', 'slug')->all();
    }

    /**
     * Install (or update) a plugin by slug. Downloads the archive, unpacks it and
     * wires the plugin into the app.
     */
    public function install(string $slug): void
    {
        // A paid plugin flagged free_for_extended is only installable without
        // purchase by Extended License holders. Everyone else must buy it.
        $extension = collect($this->extensions)->firstWhere('slug', $slug);
        $isFree = (bool) ($extension['is_free'] ?? false);
        $freeForExtended = (bool) ($extension['free_for_extended'] ?? false);
        $alreadyOwned = in_array($slug, $this->purchasedSlugs, true);
        $alreadyInstalled = in_array($slug, $this->installedSlugs, true);

        if (! $isFree && ! $alreadyOwned && ! $alreadyInstalled
            && $freeForExtended && ! $this->isExtendedLicense) {
            Toaster::error(__('This plugin is free only for Extended License holders.'));

            return;
        }

        $service = new InstallPackageService;
        $response = $service->install($slug);

        $this->refreshState();

        if (! empty($response['status'])) {
            Toaster::success($response['message'] ?? __('Plugin installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Plugin installation failed.'));
        }
    }

    /**
     * Uninstall a plugin by slug. Removes the files it laid down and flips the
     * local record back to "not installed".
     */
    public function uninstall(string $slug): void
    {
        $service = new UninstallPackageService;
        $response = $service->uninstall($slug);

        $this->refreshState();

        if (! empty($response['status'])) {
            Toaster::success($response['message'] ?? __('Plugin uninstalled successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Plugin uninstall failed.'));
        }
    }

    public function render()
    {
        // Lightweight metadata for the client-side (Alpine) filter so it can
        // resolve tab/search matches by slug without touching the server.
        $pluginMeta = collect($this->extensions)
            ->map(fn ($ext) => [
                'slug' => $ext['slug'] ?? '',
                'name' => strtolower((string) ($ext['name'] ?? '')),
                'desc' => strtolower((string) ($ext['short_description'] ?? '')),
                'tags' => strtolower((string) ($ext['tags'] ?? '')),
                'free' => (bool) ($ext['is_free'] ?? false),
            ])
            ->values()
            ->all();

        return view('livewire.admin.plugins.index', [
            'pluginMeta' => $pluginMeta,
        ]);
    }
}
