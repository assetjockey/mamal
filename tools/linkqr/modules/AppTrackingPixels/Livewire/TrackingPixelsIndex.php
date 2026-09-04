<?php

namespace Modules\AppTrackingPixels\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppTeams\Support\TeamWorkspaceAccess;
use Modules\AppTrackingPixels\Models\AppTrackingPixel;

#[Title('Tracking Pixels')]
class TrackingPixelsIndex extends Component
{
    public array $form = [
        'name' => '',
        'provider' => 'meta',
        'pixel_id' => '',
        'status' => 'active',
        'is_default' => false,
        'script' => '',
    ];

    public function addPixel(): void
    {
        $validated = validator($this->form, [
            'name' => ['required', 'string', 'max:120'],
            'provider' => ['required', 'in:meta,google,tiktok,linkedin,custom'],
            'pixel_id' => ['required', 'string', 'max:190'],
            'status' => ['required', 'in:active,paused'],
            'is_default' => ['boolean'],
            'script' => ['nullable', 'required_if:provider,custom', 'string', 'max:20000'],
        ])->validate();

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        if ((bool) ($validated['is_default'] ?? false)) {
            AppTrackingPixel::query()->where('owner_user_id', $ownerId)->update(['is_default' => false]);
        }

        AppTrackingPixel::query()->create([
            'owner_user_id' => $ownerId,
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'pixel_id' => $validated['pixel_id'],
            'status' => $validated['status'],
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'settings' => [
                'script' => trim((string) ($validated['script'] ?? '')),
            ],
        ]);

        $this->form = ['name' => '', 'provider' => 'meta', 'pixel_id' => '', 'status' => 'active', 'is_default' => false, 'script' => ''];
        $this->dispatch('app-toast', type: 'success', message: __('Tracking pixel added.'));
    }

    public function setDefault(int $pixelId): void
    {
        $pixel = $this->ownedPixel($pixelId);
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        AppTrackingPixel::query()->where('owner_user_id', $ownerId)->update(['is_default' => false]);
        $pixel->forceFill(['is_default' => true, 'status' => 'active'])->save();

        $this->dispatch('app-toast', type: 'success', message: __('Default tracking pixel updated.'));
    }

    public function unsetDefault(int $pixelId): void
    {
        $pixel = $this->ownedPixel($pixelId);
        $pixel->forceFill(['is_default' => false])->save();

        $this->dispatch('app-toast', type: 'success', message: __('Default tracking pixel removed.'));
    }

    public function toggleStatus(int $pixelId): void
    {
        $pixel = $this->ownedPixel($pixelId);
        $pixel->update(['status' => $pixel->status === 'active' ? 'paused' : 'active']);
    }

    public function deletePixel(int $pixelId): void
    {
        $this->ownedPixel($pixelId)->delete();
        $this->dispatch('app-toast', type: 'success', message: __('Tracking pixel deleted.'));
    }

    public function render(): View
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());

        return view('apptrackingpixels::index', [
            'pixels' => AppTrackingPixel::query()
                ->where('owner_user_id', $ownerId)
                ->orderByDesc('is_default')
                ->latest()
                ->get(),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Tracking Pixels')]);
    }

    protected function ownedPixel(int $pixelId): AppTrackingPixel
    {
        return AppTrackingPixel::query()
            ->where('owner_user_id', TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user()))
            ->findOrFail($pixelId);
    }
}
