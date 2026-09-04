<?php

namespace Modules\AppShortLinkApi\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppShortLinkAccess\Support\ShortLinkPlanLimits;
use Modules\AppShortLinkApi\Models\AppShortLinkApiKey;
use Modules\AppShortLinkApi\Models\AppShortLinkWebhook;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

#[Title('Short Link API')]
class ShortLinkApiSettings extends Component
{
    public string $keyName = '';
    public ?string $plainToken = null;
    public array $webhook = ['name' => '', 'url' => '', 'events' => ['short_link.created', 'short_link.clicked']];

    public function createKey(): void
    {
        $limits = app(ShortLinkPlanLimits::class);

        if (! $limits->canUseFeature(auth()->user(), 'short_link_api_access')) {
            $this->dispatch('app-toast', type: 'warning', message: __('API access is not included in your plan.'));

            return;
        }

        if (! $limits->canCreateApiKey(auth()->user(), $this->ownerId())) {
            $this->dispatch('app-toast', type: 'warning', message: __('API key limit reached for your plan.'));

            return;
        }

        $validated = $this->validate(['keyName' => ['required', 'string', 'max:120']]);
        $token = 'sl_'.Str::random(48);

        AppShortLinkApiKey::query()->create([
            'owner_user_id' => $this->ownerId(),
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
            'name' => $validated['keyName'],
            'token_hash' => hash('sha256', $token),
            'is_active' => true,
        ]);

        $this->keyName = '';
        $this->plainToken = $token;
        $this->dispatch('app-toast', type: 'success', message: __('API key created.'));
    }

    public function revokeKey(int $keyId): void
    {
        AppShortLinkApiKey::query()->where('owner_user_id', $this->ownerId())->whereKey($keyId)->update(['is_active' => false]);
    }

    public function addWebhook(): void
    {
        $limits = app(ShortLinkPlanLimits::class);

        if (! $limits->canUseFeature(auth()->user(), 'short_link_api_access')) {
            $this->dispatch('app-toast', type: 'warning', message: __('Webhook access is not included in your plan.'));

            return;
        }

        if (! $limits->canCreateWebhook(auth()->user(), $this->ownerId())) {
            $this->dispatch('app-toast', type: 'warning', message: __('Webhook limit reached for your plan.'));

            return;
        }

        $validated = validator($this->webhook, [
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:2048'],
            'events' => ['array'],
            'events.*' => ['in:short_link.created,short_link.clicked'],
        ])->validate();

        AppShortLinkWebhook::query()->create([
            'owner_user_id' => $this->ownerId(),
            'team_id' => TeamWorkspaceAccess::activeTeam(auth()->user())?->id,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $validated['events'] ?: ['short_link.created', 'short_link.clicked'],
            'secret' => Str::random(48),
            'is_active' => true,
        ]);

        $this->webhook = ['name' => '', 'url' => '', 'events' => ['short_link.created', 'short_link.clicked']];
        $this->dispatch('app-toast', type: 'success', message: __('Webhook added.'));
    }

    public function toggleWebhook(int $webhookId): void
    {
        $webhook = AppShortLinkWebhook::query()->where('owner_user_id', $this->ownerId())->findOrFail($webhookId);
        $webhook->update(['is_active' => ! $webhook->is_active]);
    }

    public function deleteWebhook(int $webhookId): void
    {
        AppShortLinkWebhook::query()->where('owner_user_id', $this->ownerId())->whereKey($webhookId)->delete();
    }

    public function render(): View
    {
        $limits = app(ShortLinkPlanLimits::class);

        return view('appshortlinkapi::index', [
            'keys' => AppShortLinkApiKey::query()->where('owner_user_id', $this->ownerId())->latest()->get(),
            'webhooks' => AppShortLinkWebhook::query()->where('owner_user_id', $this->ownerId())->latest()->get(),
            'apiAccessEnabled' => $limits->canUseFeature(auth()->user(), 'short_link_api_access'),
            'apiKeyLimit' => $limits->apiKeysLimit(auth()->user()),
            'webhookLimit' => $limits->webhooksLimit(auth()->user()),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Short Link API')]);
    }

    protected function ownerId(): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId(auth()->user());
    }
}
