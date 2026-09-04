@php
    $activeKeys = $keys->where('is_active', true)->count();
    $activeWebhooks = $webhooks->where('is_active', true)->count();
    $eventsCount = $webhooks->flatMap(fn ($hook) => (array) $hook->events)->unique()->count();
    $apiAccessEnabled = $apiAccessEnabled ?? true;
    $apiKeyLimit = $apiKeyLimit ?? null;
    $webhookLimit = $webhookLimit ?? null;
    $apiKeyLimitLabel = $apiKeyLimit === null ? __('Unlimited') : number_format((int) $apiKeyLimit);
    $webhookLimitLabel = $webhookLimit === null ? __('Unlimited') : number_format((int) $webhookLimit);
@endphp

<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-5">
        <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_24rem]">
                <div class="p-5 sm:p-7">
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-code"></i>
                        {{ __('Developer access') }}
                    </span>
                    <h1 class="mt-5 text-[2rem] font-semibold leading-tight" style="color: var(--theme-header-text-color);">{{ __('API & Webhooks') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Create API credentials, subscribe to short-link events, and connect your workspace to external systems.') }}</p>
                </div>

                <div class="border-t p-4 lg:border-l lg:border-t-0 sm:p-5" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(16,185,129,0.10));">
                    <div class="grid h-full gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        @foreach ([
                            ['label' => __('Active keys'), 'value' => $activeKeys.' / '.$apiKeyLimitLabel, 'icon' => 'fa-key', 'color' => '#2563eb'],
                            ['label' => __('Webhooks'), 'value' => $activeWebhooks.' / '.$webhookLimitLabel, 'icon' => 'fa-webhook', 'color' => '#059669'],
                            ['label' => __('Events'), 'value' => $eventsCount, 'icon' => 'fa-bolt', 'color' => '#7c3aed'],
                        ] as $stat)
                            <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.55); background-color: color-mix(in srgb, var(--theme-surface-base) 82%, transparent);">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                        <i class="fa-light {{ $stat['icon'] }}"></i>
                                    </span>
                                </div>
                                <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="rounded-[1.15rem] border p-5 shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('API keys') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Bearer tokens for creating and reading short links.') }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-key"></i>
                    </span>
                </div>

                @unless ($apiAccessEnabled)
                    <div class="mt-5 rounded-[0.95rem] border px-4 py-3 text-sm font-medium" style="border-color: rgba(245,158,11,0.28); background-color: rgba(245,158,11,0.08); color: #92400e;">
                        <i class="fa-light fa-lock mr-2"></i>
                        {{ __('API access is not included in your current plan.') }}
                    </div>
                @endunless

                <div class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_8rem]">
                    <x-ui.input wire:model.defer="keyName" :placeholder="__('Production key')" :error="$errors->first('keyName')" />
                    <button type="button" wire:click="createKey" @disabled(! $apiAccessEnabled) class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] px-4 text-sm font-semibold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-45" style="background-color: var(--theme-accent);">
                        <i class="fa-light fa-plus"></i>
                        {{ __('Create') }}
                    </button>
                </div>

                @if ($plainToken)
                    <div class="mt-4 rounded-[1rem] border p-4" style="border-color: rgba(16,185,129,0.28); background-color: rgba(16,185,129,0.08);">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">{{ __('Copy now') }}</p>
                            <button type="button" class="inline-flex h-8 items-center gap-2 rounded-[0.6rem] border px-3 text-xs font-semibold" style="border-color: rgba(16,185,129,0.28); background-color: var(--theme-surface-base); color: #047857;" x-data x-on:click="navigator.clipboard.writeText(@js($plainToken))">
                                <i class="fa-light fa-copy"></i>
                                {{ __('Copy') }}
                            </button>
                        </div>
                        <p class="mt-2 break-all font-mono text-xs" style="color: var(--theme-header-text-color);">{{ $plainToken }}</p>
                    </div>
                @endif

                <div class="mt-5 divide-y overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.62); --tw-divide-color: rgba(var(--theme-border-color-rgb),0.52);">
                    @forelse ($keys as $key)
                        <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $key->name }}</p>
                                    <x-ui.badge :variant="$key->is_active ? 'success' : 'neutral'">{{ $key->is_active ? __('Active') : __('Revoked') }}</x-ui.badge>
                                </div>
                                <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $key->last_used_at ? __('Last used').' '.$key->last_used_at->diffForHumans() : __('Never used') }}</p>
                            </div>
                            @if ($key->is_active)
                                <button type="button" wire:click="revokeKey({{ $key->id }})" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.65rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                    <i class="fa-light fa-ban"></i>
                                    {{ __('Revoke') }}
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="flex min-h-36 flex-col items-center justify-center p-6 text-center">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.85rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);"><i class="fa-light fa-key"></i></span>
                            <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No API keys yet') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Create a key to access the short-link API.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.15rem] border p-5 shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Webhooks') }}</h2>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Send created and clicked events to your own endpoint.') }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] bg-emerald-50 text-emerald-700">
                        <i class="fa-light fa-webhook"></i>
                    </span>
                </div>

                <div class="mt-5 grid gap-3">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input wire:model.defer="webhook.name" :label="__('Name')" :placeholder="__('CRM sync')" :error="$errors->first('webhook.name')" />
                        <x-ui.input wire:model.defer="webhook.url" :label="__('Endpoint URL')" :placeholder="__('https://example.com/webhooks/short-links')" :error="$errors->first('webhook.url')" />
                    </div>
                    <div class="grid gap-2 rounded-[0.9rem] border p-3 sm:grid-cols-2" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                        <x-ui.checkbox wire:model.defer="webhook.events" value="short_link.created" :label="__('Link created')" />
                        <x-ui.checkbox wire:model.defer="webhook.events" value="short_link.clicked" :label="__('Link clicked')" />
                    </div>
                    <button type="button" wire:click="addWebhook" @disabled(! $apiAccessEnabled) class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-[0.8rem] px-4 text-sm font-semibold text-white shadow-sm disabled:cursor-not-allowed disabled:opacity-45" style="background-color: var(--theme-accent);">
                        <i class="fa-light fa-plus"></i>
                        {{ __('Add webhook') }}
                    </button>
                </div>

                <div class="mt-5 divide-y overflow-hidden rounded-[0.95rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.62); --tw-divide-color: rgba(var(--theme-border-color-rgb),0.52);">
                    @forelse ($webhooks as $hook)
                        <div class="grid gap-3 p-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $hook->name }}</p>
                                    <x-ui.badge :variant="$hook->is_active ? 'success' : 'neutral'">{{ $hook->is_active ? __('Active') : __('Paused') }}</x-ui.badge>
                                </div>
                                <p class="mt-1 truncate font-mono text-xs" style="color: var(--theme-muted-text-color);">{{ $hook->url }}</p>
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ((array) $hook->events as $event)
                                        <span class="rounded-full px-2 py-1 text-[10px] font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">{{ str($event)->after('short_link.')->replace('_', ' ')->title() }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex gap-2 lg:justify-end">
                                <button type="button" wire:click="toggleWebhook({{ $hook->id }})" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.65rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                    <i class="fa-light {{ $hook->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    {{ $hook->is_active ? __('Pause') : __('Activate') }}
                                </button>
                                <button type="button" wire:click="deleteWebhook({{ $hook->id }})" class="inline-flex h-9 items-center justify-center rounded-[0.65rem] border px-3 text-xs font-semibold text-red-600" style="border-color: rgba(239,68,68,0.35);">
                                    <i class="fa-light fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex min-h-36 flex-col items-center justify-center p-6 text-center">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.85rem] bg-emerald-50 text-emerald-700"><i class="fa-light fa-webhook"></i></span>
                            <p class="mt-3 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('No webhooks yet') }}</p>
                            <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Add an endpoint to receive short-link events.') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="rounded-[1.15rem] border p-5 shadow-[0_24px_70px_-58px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('API docs') }}</p>
                    <h2 class="mt-1 text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Short Links API') }}</h2>
                    <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Use bearer tokens for API access. Webhooks are signed with HMAC SHA-256.') }}</p>
                </div>
                <x-ui.badge variant="primary">v1</x-ui.badge>
            </div>

            <div class="mt-5 grid gap-3 lg:grid-cols-3">
                @foreach ([
                    ['method' => 'POST', 'path' => '/api/short-links', 'label' => __('Create short link')],
                    ['method' => 'GET', 'path' => '/api/short-links', 'label' => __('List short links')],
                    ['method' => 'POST', 'path' => '/api/short-links/{id}/webhooks/test', 'label' => __('Test webhook')],
                ] as $endpoint)
                    <div class="rounded-[0.95rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $endpoint['label'] }}</p>
                        <p class="mt-3 truncate font-mono text-sm" style="color: var(--theme-header-text-color);"><span class="font-semibold">{{ $endpoint['method'] }}</span> {{ $endpoint['path'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 space-y-5">
                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Create short link') }}</p>
                            <h3 class="mt-1 font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">POST /api/short-links</h3>
                        </div>
                        <x-ui.badge variant="success">201</x-ui.badge>
                    </div>
                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Request') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>curl -X POST {{ url('/api/short-links') }} \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"destination_url":"https://example.com","name":"Example","custom_code":"example"}'</code></pre>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Response') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>{
  "data": {
    "id": 124,
    "name": "Example",
    "short_code": "example",
    "short_url": "{{ url('/s/example') }}",
    "destination_url": "https://example.com",
    "status": "active",
    "clicks_count": 0,
    "created_at": "2026-05-08T10:15:00Z"
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('List short links') }}</p>
                            <h3 class="mt-1 font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">GET /api/short-links</h3>
                        </div>
                        <x-ui.badge variant="neutral">200</x-ui.badge>
                    </div>
                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Request') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>curl -X GET "{{ url('/api/short-links') }}?per_page=25" \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Response') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>{
  "data": [
    {
      "id": 124,
      "name": "Example",
      "short_code": "example",
      "short_url": "{{ url('/s/example') }}",
      "destination_url": "https://example.com",
      "status": "active",
      "clicks_count": 18
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 1
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Test webhook') }}</p>
                            <h3 class="mt-1 font-mono text-sm font-semibold" style="color: var(--theme-header-text-color);">POST /api/short-links/{id}/webhooks/test</h3>
                        </div>
                        <x-ui.badge variant="primary">{{ __('signed') }}</x-ui.badge>
                    </div>
                    <div class="mt-4 grid gap-4 xl:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Request') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>curl -X POST {{ url('/api/short-links/124/webhooks/test') }} \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>

                            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Signature header') }}</p>
                            <pre class="mt-2 overflow-x-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>X-ShortLink-Signature: sha256=&lt;hmac&gt;
hmac = hash_hmac('sha256', raw_body, webhook_secret)</code></pre>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Event payload') }}</p>
                            <pre class="mt-2 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>{
  "event": "short_link.clicked",
  "created_at": "2026-05-08T10:20:00Z",
  "data": {
    "short_link": {
      "id": 124,
      "short_code": "example",
      "short_url": "{{ url('/s/example') }}",
      "destination_url": "https://example.com"
    },
    "click": {
      "country": "VN",
      "browser": "Chrome",
      "device": "Mobile",
      "referer": "https://instagram.com"
    }
  }
}</code></pre>
                            <div class="mt-3 rounded-[0.85rem] border px-3 py-3 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-shield-check mr-2" style="color: var(--theme-accent);"></i>
                                {{ __('Verify the signature before trusting incoming events.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Validation error') }}</p>
                            <h3 class="mt-1 font-semibold" style="color: var(--theme-header-text-color);">{{ __('Error response') }}</h3>
                        </div>
                        <x-ui.badge variant="danger">422</x-ui.badge>
                    </div>
                    <pre class="mt-4 overflow-auto rounded-[0.85rem] p-4 text-xs leading-6" style="background-color: #0f172a; color: #e2e8f0;"><code>{
  "message": "The destination url field is required.",
  "errors": {
    "destination_url": [
      "The destination url field is required."
    ]
  }
}</code></pre>
                </div>
            </div>
        </section>
    </div>
</div>
