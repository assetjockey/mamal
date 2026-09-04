<?php

namespace App\Services\Package;

use App\Models\Extension;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reverses an {@see InstallPackageService} install.
 *
 * It reads the manifest copy that the installer preserved under
 * resources/extensions/{slug}/index.json and deletes every file the install
 * laid down — views, routes, controllers — then drops the preserved resource
 * folder and flips the local Extension record back to "not installed".
 */
class UninstallPackageService
{
    private string $slug = '';

    /** Absolute path to resources/extensions/{slug} (holds the preserved manifest). */
    private string $resourcePath = '';

    /** Decoded index.json manifest. */
    private array $manifest = [];

    /**
     * Uninstall the plugin identified by $slug.
     *
     * @return array{status: bool, message: string}
     */
    public function uninstall(string $slug): array
    {
        try {
            $this->slug = $slug;
            $this->resourcePath = resource_path('extensions'.DIRECTORY_SEPARATOR.$slug);

            $this->readManifest();

            if (empty($this->manifest)) {
                return [
                    'status' => false,
                    'message' => __('The plugin manifest (index.json) was not found.'),
                ];
            }

            $this->deleteViews();
            $this->deleteRoutes();
            $this->deleteControllers();
            $this->deleteCategory('livewire');
            $this->deleteCategory('services');
            $this->deleteCategory('events');
            $this->deleteCategory('listeners');
            $this->deleteCategory('notifications');
            $this->deleteCategory('commands');
            $this->deleteCategory('models');
            $this->deleteConfigs();
            $this->deleteInstallMigrations();
            $this->deleteResources();

            Extension::query()->where('slug', $slug)->update(['installed' => 0]);

            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Plugin uninstalled successfully.'),
            ];
        } catch (Throwable $e) {
            Log::error('Plugin uninstall failed ['.$slug.']: '.$e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function readManifest(): void
    {
        $path = $this->resourcePath.DIRECTORY_SEPARATOR.'index.json';

        if (! File::exists($path)) {
            $this->manifest = [];

            return;
        }

        $this->manifest = json_decode((string) File::get($path), true) ?: [];
    }

    private function deleteViews(): void
    {
        $views = data_get($this->manifest, 'views', []);

        if (! is_array($views)) {
            return;
        }

        foreach ($views as $key => $view) {
            $target = is_numeric($key) ? $view : $view;
            $path = base_path($target);

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    private function deleteRoutes(): void
    {
        // Accept both the singular legacy key and the installer's plural key.
        $route = data_get($this->manifest, 'routes', data_get($this->manifest, 'route'));

        if (! $route) {
            return;
        }

        $path = base_path('routes/extensions/'.basename($route));

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    private function deleteControllers(): void
    {
        $controllers = data_get($this->manifest, 'controllers', []);

        if (! is_array($controllers)) {
            return;
        }

        foreach ($controllers as $controller) {
            $path = base_path($controller);

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    /**
     * Delete every file declared under the given manifest key. Entries are
     * app-root-relative paths, mirroring {@see InstallPackageService::copyCategory}.
     */
    private function deleteCategory(string $manifestKey): void
    {
        $entries = data_get($this->manifest, $manifestKey, []);

        if (! is_array($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }

            $path = base_path($entry);

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    private function deleteResources(): void
    {
        if (File::isDirectory($this->resourcePath)) {
            File::deleteDirectory($this->resourcePath);
        }
    }

    /**
     * Delete plugin config files declared under the manifest "config" key.
     */
    private function deleteConfigs(): void
    {
        $configs = data_get($this->manifest, 'config', []);

        if (! is_array($configs)) {
            return;
        }

        foreach ($configs as $config) {
            if (! is_string($config) || $config === '') {
                continue;
            }

            $path = base_path($config);

            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    /**
     * Remove the install migration files so a future re-install runs cleanly.
     *
     * NB: this only deletes the migration *files* — it deliberately does NOT
     * drop any tables. Dropping user data on uninstall is destructive; the
     * preserved migrations/uninstall SQL is available for an admin to run
     * manually if they truly want to purge the schema.
     */
    private function deleteInstallMigrations(): void
    {
        $migrations = data_get($this->manifest, 'migrations.install', []);

        if (! is_array($migrations)) {
            return;
        }

        foreach ($migrations as $entry) {
            $path = data_get($entry, 'path');

            if (! $path) {
                continue;
            }

            $file = database_path('migrations'.DIRECTORY_SEPARATOR.$path);

            if (File::exists($file)) {
                File::delete($file);
            }
        }
    }
}
