@php
    $previewUrl = route('short-links.public.redirect', ['code' => $previewCode ?: 'launch-link']);
@endphp

<div
    class="px-4 pb-8 pt-4 sm:px-5 xl:px-6"
    x-data="{
        passwordValue: $wire.entangle('form.password'),
        generatePassword() {
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
            const length = 14;
            const values = new Uint32Array(length);

            window.crypto.getRandomValues(values);
            this.passwordValue = Array.from(values, (value) => alphabet[value % alphabet.length]).join('');
        },
    }"
>
    <div class="mx-auto max-w-[94rem] space-y-5">
        <section class="overflow-hidden rounded-[1.35rem] border shadow-[0_30px_90px_-68px_rgba(15,23,42,0.55)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            <div class="grid lg:grid-cols-[minmax(0,0.58fr)_minmax(28rem,0.42fr)]">
                <div class="p-5 sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Create short link') }}
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700">
                            <i class="fa-light fa-bolt"></i>
                            {{ __('Branded redirect') }}
                        </span>
                    </div>

                    <h1 class="mt-5 max-w-3xl text-[2.45rem] font-semibold leading-[1.05]" style="color: var(--theme-header-text-color);">{{ __('Create a polished campaign short link') }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Build a trackable short URL with brand domains, UTM presets, pixels, access rules, and social preview metadata in one flow.') }}</p>

                    @if ($createdUrl)
                        <div class="mt-6 rounded-[1rem] border p-4" style="border-color: rgba(16,185,129,0.28); background-color: rgba(16,185,129,0.08);">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">{{ __('Created') }}</p>
                                    <p class="mt-1 break-all font-mono text-sm" style="color: var(--theme-header-text-color);">{{ $createdUrl }}</p>
                                </div>
                                <x-ui.button type="button" variant="outline" x-data x-on:click="navigator.clipboard.writeText(@js($createdUrl)); window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'success', message: @js(__('Short link copied.')) } }))">
                                    <i class="fa-light fa-copy"></i>
                                    {{ __('Copy') }}
                                </x-ui.button>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t p-5 lg:border-l lg:border-t-0 sm:p-8" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background: linear-gradient(135deg, color-mix(in srgb, {{ $brandVisual['primary_color'] ?? '#2563eb' }} 10%, transparent), color-mix(in srgb, {{ $brandVisual['accent_color'] ?? '#10b981' }} 12%, transparent));">
                    <div class="rounded-[1.2rem] border p-4 shadow-[0_28px_75px_-52px_rgba(15,23,42,0.52)]" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.9);">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Live preview') }}</p>
                                <p class="mt-1 truncate text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $form['name'] ?: __('Campaign link') }}</p>
                            </div>
                            <span class="inline-flex h-10 w-10 items-center justify-center overflow-hidden rounded-[0.95rem]" style="background-color: {{ $brandVisual['primary_color'] ?? '#2563eb' }}; color: white;">
                                @if (! empty($brandVisual['logo_url']))
                                    <img src="{{ $brandVisual['logo_url'] }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <i class="fa-light fa-link-simple"></i>
                                @endif
                            </span>
                        </div>
                        <div class="mt-4 rounded-[0.95rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-base);">
                            <p class="break-all font-mono text-xs leading-5" style="color: var(--theme-header-text-color);">{{ $previewUrl }}</p>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Domain') }}</p>
                                <p class="mt-1 truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $form['custom_domain_id'] ? __('Brand') : __('Default') }}</p>
                            </div>
                            <div class="rounded-[0.85rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: rgba(var(--theme-surface-base-rgb,255,255,255),0.72);">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.12em]" style="color: var(--theme-muted-text-color);">{{ __('Rules') }}</p>
                                <p class="mt-1 truncate text-xs font-semibold" style="color: var(--theme-header-text-color);">{{ $form['password'] || $form['expires_at'] || $form['click_limit'] ? __('Enabled') : __('Open') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <form wire:submit="create" class="grid gap-5 xl:grid-cols-[minmax(0,0.62fr)_minmax(26rem,0.38fr)]">
            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link details') }}</h2>
                        <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Name the campaign, choose the target URL, and set the public alias.') }}</p>
                    </div>
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                        <i class="fa-light fa-pen-line"></i>
                    </span>
                </div>

                <div class="mt-5 space-y-4">
                    <x-ui.input wire:model.live.debounce.250ms="form.name" :label="__('Name')" :placeholder="__('Spring launch offer')" :error="$errors->first('name')" />
                    <x-ui.input wire:model.live.debounce.250ms="form.destination_url" :label="__('Destination URL')" :placeholder="__('https://example.com/spring-launch')" :error="$errors->first('destination_url')" />
                    <div class="hidden grid gap-3 md:grid-cols-3">
                        <x-ui.input wire:model.defer="form.folder" :label="__('Folder')" :placeholder="__('Paid campaigns')" :error="$errors->first('folder')" />
                        <x-ui.input wire:model.defer="form.campaign" :label="__('Campaign')" :placeholder="__('Q4 launch')" :error="$errors->first('campaign')" />
                        <x-ui.input wire:model.defer="form.tags" :label="__('Tags')" :placeholder="__('facebook, sale')" :error="$errors->first('tags')" />
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <x-ui.input wire:model.live.debounce.250ms="form.custom_code" :label="__('Custom alias')" :placeholder="__('spring-launch')" :error="$errors->first('custom_code')" />
                        <x-ui.select wire:model.defer="form.custom_domain_id" :label="__('Brand domain')" :error="$errors->first('custom_domain_id')">
                            <option value="">{{ __('Default app domain') }}</option>
                            @foreach ($domains as $domain)
                                <option value="{{ $domain->id }}">{{ $domain->domain }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <h2 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Marketing') }}</h2>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Reuse Brand assets for analytics and retargeting.') }}</p>

                <div class="mt-5 space-y-4">
                    <x-ui.select wire:model.defer="form.utm_preset_id" :label="__('UTM preset')" :error="$errors->first('utm_preset_id')">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($utmPresets as $preset)
                            <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                        @endforeach
                    </x-ui.select>

                    <x-ui.field :label="__('Tracking pixels')" :error="$errors->first('tracking_pixel_ids')" :help="__('Hold Ctrl or Cmd to select multiple pixels.')">
                        <select wire:model.defer="form.tracking_pixel_ids" multiple class="min-h-28 w-full rounded-[0.8rem] border px-3 py-2 text-sm" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                            @foreach ($pixels as $pixel)
                                <option value="{{ $pixel->id }}">{{ $pixel->name }} - {{ str($pixel->provider)->title() }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field :label="__('Redirect rules JSON')" :error="$errors->first('redirect_rules')" :help="__('Optional rules support url, weight, countries, devices, starts_at, ends_at.')">
                        <textarea wire:model.defer="form.redirect_rules" rows="7" class="w-full rounded-[0.8rem] border px-3 py-2 font-mono text-xs leading-5" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);" placeholder='[{"url":"https://example.com/a","weight":70},{"url":"https://example.com/b","weight":30,"devices":["mobile"],"countries":["US"]}]'></textarea>
                    </x-ui.field>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <h2 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Access rules') }}</h2>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Add optional expiry, click caps, or password protection.') }}</p>
                <div class="mt-5 grid gap-3 md:grid-cols-3">
                    <x-ui.datetime-picker
                        wire:model.defer="form.expires_at"
                        :label="__('Expires at')"
                        :error="$errors->first('expires_at')"
                        :placeholder="__('Select expiry time')"
                        picker-align="left"
                        picker-position="auto"
                    />
                    <x-ui.input type="number" min="1" wire:model.defer="form.click_limit" :label="__('Click limit')" :placeholder="__('Unlimited')" :error="$errors->first('click_limit')" />
                    <x-ui.password-input
                        wire:model.defer="form.password"
                        x-model="passwordValue"
                        :label="__('Password')"
                        :error="$errors->first('password')"
                        :placeholder="__('Generated or custom password')"
                    >
                        <x-slot:action>
                            <x-ui.button type="button" variant="outline" size="sm" x-on:click="generatePassword()">
                                {{ __('Generate') }}
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.password-input>
                </div>
            </section>

            <section class="rounded-[1.25rem] border p-5 shadow-[0_24px_65px_-52px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <h2 class="text-xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Social preview') }}</h2>
                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Control how this link appears when shared.') }}</p>
                <div class="mt-5 space-y-3">
                    <x-ui.input wire:model.defer="form.og_title" :label="__('OG title')" :error="$errors->first('og_title')" />
                    <x-ui.input wire:model.defer="form.og_description" :label="__('OG description')" :error="$errors->first('og_description')" />
                    <x-ui.image-picker
                        wire:model.defer="form.og_image"
                        :value="$form['og_image'] ?? ''"
                        :label="__('OG image')"
                        :error="$errors->first('og_image')"
                        :help="__('Choose an image from your file library, upload a new image, or import from an image URL.')"
                        :button-label="__('Choose image')"
                        context="portal"
                        layout="compact"
                    />
                </div>
            </section>

            <div class="xl:col-span-2 flex flex-col gap-3 rounded-[1.15rem] border p-4 sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
                <div class="min-w-0">
                    <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Ready to publish') }}</p>
                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('The short link becomes active immediately after creation.') }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="{{ route('portal.short-links.index') }}" class="inline-flex items-center gap-2 rounded-[0.8rem] border px-4 py-2 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                        <i class="fa-light fa-list"></i>
                        {{ __('View links') }}
                    </a>
                    <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="create">
                        <i class="fa-light fa-plus" wire:loading.remove wire:target="create"></i>
                        <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="create"></i>
                        <span wire:loading.remove wire:target="create">{{ __('Create short link') }}</span>
                        <span wire:loading wire:target="create">{{ __('Creating...') }}</span>
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
</div>
