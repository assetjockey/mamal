<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\GeneralSetting;
use App\Http\Controllers\Admin\LicenseController;
use Exception;

#[Title('License Activation')]
class Activation extends Component
{
    public ?string $license = null;
    public ?string $username = null;
    public ?string $licenseType = null;
    public bool $isActivated = false;

    /**
     * Resolve a fresh LicenseController instance (mirrors the pattern used in Update.php).
     */
    protected function getApi(): LicenseController
    {
        return new LicenseController();
    }

    public function mount(): void
    {
        $this->loadLicenseInfo();
    }

    /**
     * Hydrate the component state from the stored general settings and the local
     * license file. The presence of a non-empty .lic file is the canonical marker
     * that the application is activated.
     */
    protected function loadLicenseInfo(): void
    {
        $settings = GeneralSetting::query()->first();

        if ($settings) {
            $this->license = $settings->license;
            $this->username = $settings->username;
            $this->licenseType = $settings->license_type;
        }

        $this->isActivated = $this->hasLocalLicense();
    }

    /**
     * Whether the local license file exists and is not empty.
     */
    protected function hasLocalLicense(): bool
    {
        $licenseFile = base_path('.lic');

        return file_exists($licenseFile) && filesize($licenseFile) > 0;
    }

    /**
     * Human readable license type used by the view.
     */
    public function getLicenseTypeLabelProperty(): string
    {
        return filled($this->licenseType)
            ? $this->licenseType
            : __('No Valid License');
    }

    /**
     * Activate the application license.
     */
    public function save(): void
    {
        $this->validate([
            'license' => 'required|string',
            'username' => 'required|string',
        ]);

        try {
            $status = $this->getApi()->activate_license($this->license, $this->username);

            if (! empty($status['status'])) {
                GeneralSetting::query()->firstOrCreate([])->update([
                    'license' => $this->license,
                    'username' => $this->username,
                    'license_type' => $status['data'] ?? null,
                ]);

                $this->loadLicenseInfo();

                toaster()->success(__('Application license was successfully activated'));
            } else {

                toaster()->error(__('There was an error while activating your application, please contact support team'));
            }
        } catch (Exception $e) {
            toaster()->error($e->getMessage());
        }
    }

    /**
     * Deactivate the application license.
     */
    public function deactivate(): void
    {
        $settings = GeneralSetting::query()->first();

        $license = $settings->license ?? null;
        $username = $settings->username ?? null;

        if (blank($license) || blank($username)) {
            toaster()->error(__('No active license was found to deactivate'));

            return;
        }

        try {
            $verify = $this->getApi()->deactivate_license($license, $username);

            if (! empty($verify['status'])) {
                GeneralSetting::query()->firstOrCreate([])->update([
                    'license' => '',
                    'username' => '',
                    'license_type' => '',
                ]);

                $this->reset(['license', 'username', 'licenseType']);
                $this->isActivated = false;

                toaster()->success(__('Application license was successfully deactivated'));
            } else {
                if(!empty($verify['message'])) {
                    toaster()->error($verify['message']);
                } else {
                    toaster()->error(__('There was an error while deactivating your application, please contact support team'));
                }
                
            }
        } catch (Exception $e) {
            toaster()->error($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.activation');
    }
}
