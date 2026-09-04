<?php

namespace App\Livewire\Admin\General;

use App\Concerns\ProjectValidationRules;
use App\Models\GeneralSetting;
use App\Services\Storage\StorageManager;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('General Settings')]
class GeneralSettings extends Component
{
    use ProjectValidationRules;

    /** Credits granted to a user when they register for the first time. */
    public int $default_credits = 0;

    /**
     * Where new Image/Video Studio results are stored: 'local' (this server)
     * or the key of an enabled storage provider ('s3', 'wasabi', …).
     */
    public string $default_storage = StorageManager::LOCAL;

    /**
     * Maximum number of Projects a user without an active subscription may
     * create (the Free_Tier_Limit). Whole number between 0 and 1000 inclusive.
     */
    public int $free_tier_project_limit = 0;

    /**
     * Maximum members (excluding the owner) a free-tier owner may invite. Only
     * meaningful when the Team plugin is installed.
     */
    public int $free_tier_team_members = 0;

    public function mount(): void
    {
        $settings = GeneralSetting::query()->first();

        $this->default_credits = (int) ($settings?->default_credits ?? 0);
        $this->default_storage = (string) ($settings?->default_storage ?: StorageManager::LOCAL);
        $this->free_tier_project_limit = (int) ($settings?->free_tier_project_limit ?? 0);
        $this->free_tier_team_members = (int) ($settings?->free_tier_team_members ?? 0);
    }

    public function save(StorageManager $storage): void
    {
        $this->validate([
            'default_credits' => 'required|integer|min:0',
            'default_storage' => 'required|string|max:40',
            'free_tier_project_limit' => $this->freeTierLimitRules(),
            'free_tier_team_members' => 'required|integer|min:0|max:1000',
        ]);

        // Only accept 'local' or a currently-enabled provider key. If the
        // chosen provider was disabled in the meantime, fall back to local so
        // we never point at an unusable backend.
        $allowed = array_keys($storage->options());

        if (! in_array($this->default_storage, $allowed, true)) {
            $this->default_storage = StorageManager::LOCAL;
        }

        $payload = [
            'default_credits' => $this->default_credits,
            'default_storage' => $this->default_storage,
            'free_tier_project_limit' => $this->free_tier_project_limit,
        ];

        // The team-seat column only exists when the Team plugin is installed.
        if (\Illuminate\Support\Facades\Schema::hasColumn('general_settings', 'free_tier_team_members')) {
            $payload['free_tier_team_members'] = $this->free_tier_team_members;
        }

        GeneralSetting::query()->firstOrCreate([])->update($payload);

        toaster()->success(__('Settings were saved successfully'));
    }

    public function render(StorageManager $storage)
    {
        return view('livewire.admin.general.general-settings', [
            // ['local' => 'Local server', 's3' => 'Amazon S3', …]
            'storageOptions' => $storage->options(),
        ]);
    }
}
