<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Exception;

#[Title('Update Software')]
class Update extends Component
{
    public $current_version;
    public $latest_version;
    public $version_metadata;
    public $is_update_available = false;
    public $is_upgrading = false;

    protected function getApi(): LicenseController
    {
        return new LicenseController();
    }

    public function mount()
    {
        $api = $this->getApi();

        $this->current_version = $api->get_current_version();
        $latest_version = $api->check_update();

        if (isset($latest_version['status']) && $latest_version['status'] == false) {
            $this->latest_version = [
                'update_id' => $this->current_version,
                'version' => $this->current_version,
            ];
            $this->is_update_available = false;
        } else {
            $this->latest_version = $latest_version;
            $this->is_update_available = isset($latest_version['version'])
                && $latest_version['version'] !== $this->current_version;
        }

        $this->version_metadata = $api->version_metadata();
    }

    public function render()
    {
        return view('livewire.admin.update');
    }

    /**
     * Start upgrade process (called via wire:click="save" in the Blade view)
     */
    public function save()
    {
        $api = $this->getApi();

        $latest_version = $api->get_latest_version();

        if ($this->current_version == ($latest_version['latest_version'] ?? null)) {
            toaster()->success(__('You are already using the latest version ') . ($latest_version['latest_version'] ?? ''));
            return;
        }

        $this->is_upgrading = true;

        try {
            $update_id = $this->latest_version['update_id'] ?? null;
            $version = $this->latest_version['version'] ?? null;

            $response = $api->download_update($update_id, false, $version);
        } catch (Exception $e) {
            $this->is_upgrading = false;
            toaster()->error(__('There was an error during software update. ') . $e->getMessage());
            return;
        }

        if ($response) {
            $this->storeConfiguration('APP_VERSION', $latest_version['latest_version'] ?? $version);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Log::info('Migration completed...');
        } catch (Exception $e) {
            Log::info('Migration or Seed error: ' . $e->getMessage());
        }

        $this->is_upgrading = false;

        if ($response) {
            toaster()->success(__('Software successfully was upgraded to version ') . ($version ?? ''));
            $this->current_version = $latest_version['latest_version'] ?? $version;
            $this->is_update_available = false;
        } else {
            toaster()->error(__('Software was not updated correctly. Please try again or contact support'));
        }
    }

    /**
     * Record in .env file
     */
    private function storeConfiguration(string $key, string $value): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                "{$key}=" . env($key),
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }
}
