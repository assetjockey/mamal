<?php

namespace App\Livewire\Admin\Themes;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use App\Models\GeneralSetting;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

#[Title('Themes')]
class Themes extends Component
{
    /** Marketplace theme catalog, fetched once on mount. */
    public array $themes = [];

    public function mount(): void
    {
        $this->loadThemes();
    }

    /**
     * Pull the theme catalog from the marketplace once. Tab + search filtering
     * happens entirely client-side (Alpine) so it stays instant — no server
     * round-trip per keystroke or tab switch.
     */
    private function loadThemes(): void
    {
        $extensionController = new ExtensionController;
        $this->themes = $extensionController->themes();
    }

    /**
     * Activate a theme by slug (handles payment check and setting the active theme).
     */
    public function activateTheme(string $slug): void
    {
        $extensions = new ExtensionController;
        $extensions->checkPayment($slug);

        Toaster::success(__('Theme activated successfully.'));
    }

    /**
     * Install a theme by slug (downloads, extracts, and sets as active).
     */
    public function installTheme(string $slug): void
    {
        $extensions = new ExtensionController;
        $response = $extensions->installTheme($slug);

        if (! empty($response['status'])) {
            Toaster::success($response['message'] ?? __('Theme installed successfully.'));
        } else {
            Toaster::error($response['message'] ?? __('Theme installation failed.'));
        }
    }

    public function render()
    {
        $extensions = Extension::get();
        $settings = GeneralSetting::first();

        // Lightweight metadata used by the client-side (Alpine) filter so it can
        // resolve tab/search matches by slug without touching the server.
        $themeMeta = collect($this->themes)
            ->map(fn ($theme) => [
                'slug' => $theme['slug'] ?? '',
                'name' => strtolower((string) ($theme['name'] ?? '')),
                'type' => strtolower((string) ($theme['type'] ?? '')),
            ])
            ->values()
            ->all();

        return view('livewire.admin.themes.index', [
            'themes' => $this->themes,
            'themeMeta' => $themeMeta,
            'extensions' => $extensions,
            'settings' => $settings,
        ]);
    }
}
