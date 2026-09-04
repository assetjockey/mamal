<?php

namespace App\Services\Package;

use App\Http\Controllers\Admin\ExtensionController;
use App\Models\Extension;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

/**
 * Downloads a plugin/extension archive from the Berkine marketplace and unpacks
 * it into the application.
 *
 * The flow mirrors the legacy DaVinci package installer but is rebuilt on PHP's
 * native {@see \ZipArchive} (no third-party zip dependency) and Laravel's File
 * facade so it fits the magicads stack.
 *
 * Each plugin archive ships an `index.json` manifest describing what to copy and
 * where:
 *
 *   {
 *     "version": "1.0",
 *     "migration": true,
 *     "routes": "routes/extensions/my-plugin.php",
 *     "controllers": ["app/Http/Controllers/Extensions/MyController.php"],
 *     "views": ["resources/views/extensions/my-plugin/index.blade.php"],
 *     "livewire": ["app/Livewire/Extensions/MyComponent.php"],
 *     "services": ["app/Services/Extensions/MyService.php"],
 *     "events": ["app/Events/Extensions/MyEvent.php"],
 *     "listeners": ["app/Listeners/Extensions/MyListener.php"],
 *     "notifications": ["app/Notifications/Extensions/MyNotification.php"],
 *     "commands": ["app/Console/Commands/Extensions/MyCommand.php"],
 *     "migrations": { "uninstall": [ { "path": "drop_table.sql" } ] }
 *   }
 *
 * A copy of the manifest (plus any uninstall SQL) is preserved under
 * resources/extensions/{slug}/ so {@see UninstallPackageService} can cleanly
 * reverse the install later.
 */
class InstallPackageService
{
    private string $slug = '';

    /** Absolute path to the temp dir the archive is extracted into. */
    private string $tempPath = '';

    /** Absolute path to the extracted archive root (the dir holding index.json). */
    private string $extractPath = '';

    /** Decoded index.json manifest. */
    private array $manifest = [];

    private ExtensionController $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionController;
    }

    /**
     * Install (or update) the plugin identified by $slug.
     *
     * @return array{status: bool, message: string}
     */
    public function install(string $slug): array
    {
        $this->slug = $slug;

        // 1. Pull the signed archive from the marketplace (license is verified
        //    server-side, so free plugins download without a purchase record).
        $response = $this->extensions->installExtension($slug);

        if ($response->failed()) {
            return [
                'status' => false,
                'message' => $response->json('message') ?: __('The plugin could not be downloaded from the marketplace.'),
            ];
        }

        $tmpZip = storage_path('app/plugin-'.$slug.'-'.uniqid().'.zip');
        $this->tempPath = storage_path('app/plugin-install-'.$slug.'-'.uniqid());
        $this->extractPath = $this->tempPath;

        if (file_put_contents($tmpZip, $response->body()) === false) {
            return [
                'status' => false,
                'message' => __('Unable to write the downloaded plugin archive to disk.'),
            ];
        }

        // 2. Extract the archive with the native Zip extension.
        $zip = new ZipArchive;

        if ($zip->open($tmpZip) !== true) {
            @unlink($tmpZip);

            return [
                'status' => false,
                'message' => __('The downloaded plugin archive is invalid or corrupted.'),
            ];
        }

        File::ensureDirectoryExists($this->extractPath);
        $zip->extractTo($this->extractPath.DIRECTORY_SEPARATOR);
        $zip->close();
        @unlink($tmpZip);

        // 3. Walk the manifest and copy everything into place.
        try {
            $this->locateManifestRoot();
            $this->readManifest();

            if (empty($this->manifest)) {
                $this->cleanup();

                return [
                    'status' => false,
                    'message' => __('The plugin manifest (index.json) was not found.'),
                ];
            }

            $this->prepareDirectories();
            $this->copyManifest();
            $this->copyRoutes();
            $this->copyControllers();
            $this->copyViews();

            // Additional PHP class categories. Each is sourced from a folder of
            // the same name in the archive and copied to the path declared in
            // the manifest, preserving the app's directory structure.
            $this->copyCategory('livewire', 'livewire');
            $this->copyCategory('services', 'services');
            $this->copyCategory('events', 'events');
            $this->copyCategory('listeners', 'listeners');
            $this->copyCategory('notifications', 'notifications');
            $this->copyCategory('commands', 'commands');

            // Additive categories (manifest-key-guarded — older plugins that don't
            // declare them are unaffected): Eloquent models, config files and the
            // install migrations that the migrate step below will then run.
            $this->copyCategory('models', 'models');
            $this->copyConfigs();
            $this->copyInstallMigrations();

            if (data_get($this->manifest, 'migration')) {
                Artisan::call('migrate', ['--force' => true]);
            }

            Extension::query()->where('slug', $slug)->update([
                'installed' => 1,
                'version' => data_get($this->manifest, 'version', '1.0'),
            ]);

            $this->cleanup();
            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Plugin installed successfully.'),
            ];
        } catch (Throwable $e) {
            Log::error('Plugin install failed ['.$slug.']: '.$e->getMessage());
            $this->cleanup();

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Some archives wrap their payload in a single top-level folder. Resolve the
     * real manifest root so the rest of the pipeline can assume index.json lives
     * directly in {@see $extractPath}.
     */
    private function locateManifestRoot(): void
    {
        if (File::exists($this->extractPath.DIRECTORY_SEPARATOR.'index.json')) {
            return;
        }

        foreach (File::directories($this->extractPath) as $directory) {
            if (File::exists($directory.DIRECTORY_SEPARATOR.'index.json')) {
                $this->extractPath = $directory;

                return;
            }
        }
    }

    private function readManifest(): void
    {
        $path = $this->extractPath.DIRECTORY_SEPARATOR.'index.json';

        if (! File::exists($path)) {
            $this->manifest = [];

            return;
        }

        $this->manifest = json_decode((string) File::get($path), true) ?: [];
    }

    /**
     * Ensure the destination folders the installer writes into all exist.
     */
    private function prepareDirectories(): void
    {
        File::ensureDirectoryExists(resource_path("extensions/{$this->slug}"));
        File::ensureDirectoryExists(resource_path("extensions/{$this->slug}/migrations/uninstall"));
        File::ensureDirectoryExists(base_path('routes/extensions'));
    }

    /**
     * Preserve the manifest (and any uninstall SQL) under resources/extensions so
     * the plugin can be cleanly removed later.
     */
    private function copyManifest(): void
    {
        File::copy(
            $this->extractPath.DIRECTORY_SEPARATOR.'index.json',
            resource_path("extensions/{$this->slug}/index.json")
        );

        $uninstall = data_get($this->manifest, 'migrations.uninstall');

        if (! is_array($uninstall)) {
            return;
        }

        foreach ($uninstall as $entry) {
            $path = data_get($entry, 'path');

            if (! $path) {
                continue;
            }

            $source = $this->extractPath.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.$path;

            if (File::exists($source)) {
                File::copy($source, resource_path("extensions/{$this->slug}/migrations/{$path}"));
            }
        }
    }

    /**
     * Copy the plugin's route file into routes/extensions/ where it is
     * auto-loaded by the application.
     */
    private function copyRoutes(): void
    {
        $route = data_get($this->manifest, 'routes');

        if (! $route) {
            return;
        }

        // The archive ships the route file flat in its category folder
        // ("routes/<file>"), exactly like controllers live under "controllers/".
        // The manifest value is the *destination* path, so resolve the source by
        // basename inside the archive's routes/ folder.
        $source = $this->extractPath.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.basename($route);

        if (! File::exists($source)) {
            // Fall back to a structure-preserving archive that keeps the file at
            // its full destination-relative path (e.g. "routes/extensions/x.php").
            $source = $this->extractPath.DIRECTORY_SEPARATOR.str_replace('\\', '/', $route);
        }

        $destination = base_path($route);

        if (File::exists($source)) {
            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);
        }
    }

    private function copyControllers(): void
    {
        $controllers = data_get($this->manifest, 'controllers');

        if (! is_array($controllers)) {
            return;
        }

        foreach ($controllers as $controller) {
            $source = $this->extractPath.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.basename($controller);
            $destination = base_path($controller);

            if (! File::exists($source)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);
        }
    }

    private function copyViews(): void
    {
        $views = data_get($this->manifest, 'views');

        if (! is_array($views)) {
            return;
        }

        foreach ($views as $key => $view) {
            $fileName = is_numeric($key) ? basename($view) : $key;
            $source = $this->extractPath.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$fileName;
            $destination = base_path($view);

            if (! File::exists($source)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);
        }
    }

    /**
     * Copy a list of PHP class files declared under the given manifest key.
     *
     * Each entry is the file's final path relative to the app root (e.g.
     * "app/Livewire/User/Billing/Billing.php"). The archive ships the files in a
     * folder named after the category ($sourceDir). To stay robust against
     * filename collisions across nested folders (e.g. several index.blade-style
     * classes), the source is resolved by preserving the path *below* the app's
     * top-level dir first, falling back to the flat basename for archives that
     * ship a flat folder.
     */
    private function copyCategory(string $manifestKey, string $sourceDir): void
    {
        $entries = data_get($this->manifest, $manifestKey);

        if (! is_array($entries)) {
            return;
        }

        $base = $this->extractPath.DIRECTORY_SEPARATOR.$sourceDir.DIRECTORY_SEPARATOR;

        foreach ($entries as $entry) {
            if (! is_string($entry) || $entry === '') {
                continue;
            }

            // Structure-preserving candidate: strip the leading "app/" segment so
            // "app/Livewire/User/Billing/Billing.php" looks for
            // "<archive>/livewire/Livewire/User/Billing/Billing.php".
            $relative = preg_replace('#^app/#', '', str_replace('\\', '/', $entry));

            $source = $base.$relative;

            if (! File::exists($source)) {
                // Fall back to a flat archive layout keyed by basename.
                $source = $base.basename($entry);
            }

            if (! File::exists($source)) {
                continue;
            }

            $destination = base_path($entry);
            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);
        }
    }

    private function cleanup(): void
    {
        if ($this->tempPath && File::isDirectory($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }
    }

    /**
     * Copy plugin config files declared under the manifest "config" key. Each
     * entry is the destination path (e.g. "config/social-media-studio.php");
     * the archive ships the file flat under its "config/" folder by basename.
     */
    private function copyConfigs(): void
    {
        $configs = data_get($this->manifest, 'config');

        if (! is_array($configs)) {
            return;
        }

        foreach ($configs as $config) {
            if (! is_string($config) || $config === '') {
                continue;
            }

            $source = $this->extractPath.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.basename($config);
            $destination = base_path($config);

            if (! File::exists($source)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($destination));
            File::copy($source, $destination);
        }
    }

    /**
     * Copy fresh install migrations into database/migrations so the subsequent
     * `migrate --force` runs them. Existing files are never overwritten, so a
     * re-install is safe. Declared under manifest "migrations.install" as a list
     * of { "path": "2026_..._create_x_table.php" } entries shipped flat under the
     * archive's "migrations/" folder.
     */
    private function copyInstallMigrations(): void
    {
        $migrations = data_get($this->manifest, 'migrations.install');

        if (! is_array($migrations)) {
            return;
        }

        File::ensureDirectoryExists(database_path('migrations'));

        foreach ($migrations as $entry) {
            $path = data_get($entry, 'path');

            if (! $path) {
                continue;
            }

            $source = $this->extractPath.DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.$path;
            $destination = database_path('migrations'.DIRECTORY_SEPARATOR.$path);

            if (File::exists($source) && ! File::exists($destination)) {
                File::copy($source, $destination);
            }
        }
    }
}
