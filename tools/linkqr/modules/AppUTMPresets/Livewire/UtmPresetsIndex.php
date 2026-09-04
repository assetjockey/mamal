<?php

namespace Modules\AppUTMPresets\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Modules\AppUTMPresets\Models\AppUtmPreset;

#[Title('UTM Presets')]
class UtmPresetsIndex extends Component
{
    public array $form = [
        'name' => '',
        'campaign_key' => '',
        'source' => '',
        'medium' => '',
        'campaign' => '',
        'term' => '',
        'content' => '',
        'auto_apply' => false,
        'is_default' => false,
    ];

    public function addPreset(): void
    {
        $validated = validator($this->form, [
            'name' => ['required', 'string', 'max:120'],
            'campaign_key' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:120'],
            'medium' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:120'],
            'term' => ['nullable', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:120'],
            'auto_apply' => ['boolean'],
            'is_default' => ['boolean'],
        ])->validate();

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        if ((bool) ($validated['is_default'] ?? false)) {
            AppUtmPreset::query()->where('owner_user_id', $ownerId)->update(['is_default' => false]);
        }

        AppUtmPreset::query()->create($validated + [
            'owner_user_id' => $ownerId,
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
        ]);

        $this->form = ['name' => '', 'campaign_key' => '', 'source' => '', 'medium' => '', 'campaign' => '', 'term' => '', 'content' => '', 'auto_apply' => false, 'is_default' => false];
        $this->dispatch('app-toast', type: 'success', message: __('UTM preset added.'));
    }

    public function setDefault(int $presetId): void
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        AppUtmPreset::query()->where('owner_user_id', $ownerId)->update(['is_default' => false]);
        AppUtmPreset::query()->where('owner_user_id', $ownerId)->whereKey($presetId)->update(['is_default' => true]);
    }

    public function clearDefault(int $presetId): void
    {
        AppUtmPreset::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereKey($presetId)
            ->update(['is_default' => false]);
    }

    public function deletePreset(int $presetId): void
    {
        AppUtmPreset::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->whereKey($presetId)
            ->delete();

        $this->dispatch('app-toast', type: 'success', message: __('UTM preset deleted.'));
    }

    public function render(): View
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        return view('apputmpresets::index', [
            'presets' => AppUtmPreset::query()
                ->where('owner_user_id', $ownerId)
                ->orderByDesc('is_default')
                ->latest()
                ->get(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('UTM Presets')]);
    }
}
