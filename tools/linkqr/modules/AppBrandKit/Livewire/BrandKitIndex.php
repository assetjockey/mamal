<?php

namespace Modules\AppBrandKit\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBrandKit\Models\AppBrandKit;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Brand Kit')]
class BrandKitIndex extends Component
{
    public array $form = [
        'brand_name' => '',
        'logo_url' => '',
        'primary_color' => '#2563eb',
        'secondary_color' => '#0f172a',
        'accent_color' => '#10b981',
        'font_family' => 'Inter',
    ];

    public function mount(): void
    {
        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());
        $kit = AppBrandKit::query()
            ->where('owner_user_id', $ownerId)
            ->where('team_id', $team?->id)
            ->first();

        if (! $kit) {
            return;
        }

        $this->form = [
            'brand_name' => (string) ($kit->brand_name ?? ''),
            'logo_url' => (string) ($kit->logo_url ?? ''),
            'primary_color' => (string) $kit->primary_color,
            'secondary_color' => (string) $kit->secondary_color,
            'accent_color' => (string) $kit->accent_color,
            'font_family' => (string) $kit->font_family,
        ];
    }

    public function save(): void
    {
        $validated = validator($this->form, [
            'brand_name' => ['nullable', 'string', 'max:120'],
            'logo_url' => ['nullable', 'string', 'max:2000'],
            'primary_color' => ['required', 'string', 'max:24'],
            'secondary_color' => ['required', 'string', 'max:24'],
            'accent_color' => ['required', 'string', 'max:24'],
            'font_family' => ['required', 'string', 'max:120'],
        ])->validate();

        $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
        $team = TeamWorkspaceAccess::activeTeam(auth()->user());

        AppBrandKit::query()->updateOrCreate(
            ['owner_user_id' => $ownerId, 'team_id' => $team?->id],
            $validated + ['owner_user_id' => $ownerId, 'team_id' => $team?->id],
        );

        $this->dispatch('app-toast', type: 'success', message: __('Brand kit saved.'));
    }

    public function applyPalette(string $primary, string $secondary, string $accent): void
    {
        $this->form['primary_color'] = $primary;
        $this->form['secondary_color'] = $secondary;
        $this->form['accent_color'] = $accent;
    }

    public function render(): View
    {
        return view('appbrandkit::index')
            ->layout(theme_view('layouts.app', 'app'), ['title' => __('Brand Kit')]);
    }
}
