<?php

namespace Modules\AppShortLinks\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkApi\Support\ShortLinkWebhookDispatcher;
use Modules\AppShortLinkBranding\Support\ShortLinkBranding;
use Modules\AppShortLinks\Models\AppShortLink;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Create Short Link')]
class ShortLinkCreate extends Component
{
    public array $form = [
        'name' => '',
        'folder' => '',
        'campaign' => '',
        'tags' => '',
        'destination_url' => '',
        'custom_code' => '',
        'custom_domain_id' => '',
        'utm_preset_id' => '',
        'tracking_pixel_ids' => [],
        'expires_at' => '',
        'click_limit' => '',
        'password' => '',
        'og_title' => '',
        'og_description' => '',
        'og_image' => '',
        'redirect_rules' => '',
    ];

    public ?string $createdUrl = null;

    public function mount(): void
    {
        $this->resetForm();
    }

    public function create(): void
    {
        if (! app(ShortLinkPlanLimits::class)->canCreateLink(auth()->user(), $this->ownerId())) {
            $this->dispatch('app-toast', type: 'warning', message: __('Short link limit reached. Upgrade your plan to create more links.'));

            return;
        }

        $validated = validator($this->form, [
            'name' => ['required', 'string', 'max:160'],
            'folder' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:160'],
            'tags' => ['nullable', 'string', 'max:500'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'custom_code' => ['nullable', 'alpha_dash:ascii', 'min:3', 'max:48', Rule::unique('app_short_links', 'short_code')],
            'custom_domain_id' => ['nullable', 'integer', Rule::exists('custom_domains', 'id')->where('owner_user_id', $this->ownerId())->where('status', 'verified')],
            'utm_preset_id' => ['nullable', 'integer', Rule::exists('app_utm_presets', 'id')->where('owner_user_id', $this->ownerId())],
            'tracking_pixel_ids' => ['array'],
            'tracking_pixel_ids.*' => [Rule::exists('app_tracking_pixels', 'id')->where('owner_user_id', $this->ownerId())],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'click_limit' => ['nullable', 'integer', 'min:1', 'max:999999999'],
            'password' => ['nullable', 'string', 'min:4', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:160'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'url', 'max:2048'],
            'redirect_rules' => ['nullable', 'json'],
        ])->validate();

        $code = trim((string) ($validated['custom_code'] ?? ''));
        $shortLink = AppShortLink::query()->create([
            'owner_user_id' => $this->ownerId(),
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
            'custom_domain_id' => $validated['custom_domain_id'] ?: null,
            'utm_preset_id' => $validated['utm_preset_id'] ?: null,
            'tracking_pixel_ids' => $this->cleanIds($validated['tracking_pixel_ids'] ?? []),
            'name' => $validated['name'],
            'folder' => $validated['folder'] ?: null,
            'campaign' => $validated['campaign'] ?: null,
            'tags' => $this->parseTags((string) ($validated['tags'] ?? '')),
            'destination_url' => $validated['destination_url'],
            'short_code' => $code !== '' ? str($code)->lower()->value() : AppShortLink::uniqueShortCode($validated['name']),
            'status' => 'active',
            'expires_at' => $validated['expires_at'] ?: null,
            'click_limit' => $validated['click_limit'] ?: null,
            'password_hash' => filled($validated['password'] ?? null) ? Hash::make((string) $validated['password']) : null,
            'og_title' => $validated['og_title'] ?: null,
            'og_description' => $validated['og_description'] ?: null,
            'og_image' => $validated['og_image'] ?: null,
            'redirect_rules' => filled($validated['redirect_rules'] ?? null) ? json_decode((string) $validated['redirect_rules'], true) : null,
        ]);

        $this->createdUrl = $shortLink->shortUrl();
        app(ShortLinkWebhookDispatcher::class)->dispatch($this->ownerId(), 'short_link.created', $shortLink);
        $this->resetForm();
        $this->dispatch('app-toast', type: 'success', message: __('Short link created.'));
    }

    public function render(): View
    {
        $branding = app(ShortLinkBranding::class);

        return view('appshortlinks::create', [
            'domains' => $branding->domains($this->ownerId()),
            'utmPresets' => $branding->utmPresets($this->ownerId()),
            'pixels' => $branding->pixels($this->ownerId()),
            'brandVisual' => $branding->visualSettings($this->ownerId()),
            'previewCode' => $this->form['custom_code'] ?: 'a8K3xQ',
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Create Short Link')]);
    }

    protected function ownerId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }

    protected function cleanIds(array $ids): array
    {
        return collect($ids)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
    }

    protected function resetForm(): void
    {
        $branding = app(ShortLinkBranding::class);
        $defaultDomainId = $branding->domains($this->ownerId())->firstWhere('is_default', true)?->id;
        $defaultUtmPresetId = $branding->defaultUtmPreset($this->ownerId())?->id;
        $defaultPixelIds = $branding->defaultPixelIds($this->ownerId());

        $this->form = [
            'name' => '',
            'folder' => '',
            'campaign' => '',
            'tags' => '',
            'destination_url' => '',
            'custom_code' => '',
            'custom_domain_id' => $defaultDomainId ? (string) $defaultDomainId : '',
            'utm_preset_id' => $defaultUtmPresetId ? (string) $defaultUtmPresetId : '',
            'tracking_pixel_ids' => $defaultPixelIds,
            'expires_at' => '',
            'click_limit' => '',
            'password' => '',
            'og_title' => '',
            'og_description' => '',
            'og_image' => '',
            'redirect_rules' => '',
        ];
    }

    protected function parseTags(string $tags): array
    {
        return str($tags)->explode(',')
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
