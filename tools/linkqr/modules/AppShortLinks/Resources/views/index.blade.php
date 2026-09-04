<div class="px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <div class="mx-auto max-w-[96rem] space-y-4">
        <section class="relative rounded-[1.1rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-base);">
            @php
                $linksLimit = $planUsage['links_limit'] ?? null;
                $linksUsed = (int) ($planUsage['links_used'] ?? $totalLinks);
                $clicksLimit = $planUsage['monthly_clicks_limit'] ?? null;
                $clicksUsed = (int) ($planUsage['monthly_clicks_used'] ?? 0);
                $linkPercent = $linksLimit ? min(100, (int) round(($linksUsed / max(1, (int) $linksLimit)) * 100)) : 0;
                $clickPercent = $clicksLimit ? min(100, (int) round(($clicksUsed / max(1, (int) $clicksLimit)) * 100)) : 0;
            @endphp
            <div class="border-b p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <div
                            x-data="{
                                mode: @js($viewMode),
                                setMode(value) {
                                    this.mode = value;
                                    localStorage.setItem('app-short-links-view-mode', value);
                                    $wire.setViewMode(value);
                                },
                            }"
                            x-init="
                                const savedMode = localStorage.getItem('app-short-links-view-mode');
                                if (['list', 'detail', 'grid'].includes(savedMode) && savedMode !== mode) {
                                    setMode(savedMode);
                                }
                            "
                            class="inline-flex h-10 items-center rounded-[0.8rem] border p-1"
                            style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);"
                        >
                            <button type="button" x-on:click="setMode('list')" class="inline-flex h-8 w-9 items-center justify-center rounded-[0.55rem] text-sm transition" x-bind:style="`background-color: ${mode === 'list' ? 'var(--theme-surface-base)' : 'transparent'}; color: var(--theme-header-text-color);`" title="{{ __('List view') }}">
                                <i class="fa-light fa-list"></i>
                            </button>
                            <button type="button" x-on:click="setMode('detail')" class="inline-flex h-8 w-9 items-center justify-center rounded-[0.55rem] text-sm transition" x-bind:style="`background-color: ${mode === 'detail' ? 'var(--theme-surface-base)' : 'transparent'}; color: var(--theme-header-text-color);`" title="{{ __('Detail view') }}">
                                <i class="fa-light fa-table-columns"></i>
                            </button>
                            <button type="button" x-on:click="setMode('grid')" class="inline-flex h-8 w-9 items-center justify-center rounded-[0.55rem] text-sm transition" x-bind:style="`background-color: ${mode === 'grid' ? 'var(--theme-surface-base)' : 'transparent'}; color: var(--theme-header-text-color);`" title="{{ __('Grid view') }}">
                                <i class="fa-light fa-grid-2"></i>
                            </button>
                        </div>

                        <x-ui.select wire:model.live="sort" class="h-10 min-w-36">
                            <option value="latest">{{ __('Latest') }}</option>
                            <option value="oldest">{{ __('Oldest') }}</option>
                            <option value="clicks">{{ __('Most clicks') }}</option>
                            <option value="name">{{ __('Name') }}</option>
                        </x-ui.select>

                        <x-ui.select wire:model.live="statusFilter" class="h-10 min-w-36">
                            <option value="all">{{ __('All statuses') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="paused">{{ __('Paused') }}</option>
                            <option value="blocked">{{ __('Blocked') }}</option>
                        </x-ui.select>

                        <x-ui.select wire:model.live="perPage" class="h-10 min-w-28">
                            <option value="10">10 / p</option>
                            <option value="25">25 / p</option>
                            <option value="50">50 / p</option>
                            <option value="100">100 / p</option>
                        </x-ui.select>

                        @if ($selectedLinksCount > 0)
                            <x-ui.dialog :title="__('Delete selected short links?')" :description="trans_choice('This will delete :count selected short link and its analytics.|This will delete :count selected short links and their analytics.', $selectedLinksCount, ['count' => number_format($selectedLinksCount)])" width="sm" dismissible>
                                <x-slot:trigger>
                                    <button type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-3 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-50" style="border-color: rgba(var(--theme-danger-rgb,239,68,68),0.32); color: var(--theme-danger-color);">
                                        <i class="fa-light fa-trash-can"></i>
                                        {{ __('Delete selected') }} ({{ number_format($selectedLinksCount) }})
                                    </button>
                                </x-slot:trigger>
                                <x-slot:footer>
                                    <div class="flex justify-end gap-3">
                                        <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                        <x-ui.button type="button" variant="danger" wire:click="deleteSelectedLinks" wire:loading.attr="disabled" wire:target="deleteSelectedLinks" x-on:click="open = false">
                                            <span wire:loading.remove wire:target="deleteSelectedLinks">{{ __('Delete selected') }}</span>
                                            <span wire:loading wire:target="deleteSelectedLinks">{{ __('Deleting...') }}</span>
                                        </x-ui.button>
                                    </div>
                                </x-slot:footer>
                            </x-ui.dialog>
                        @endif
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center lg:justify-end">
                        <div class="relative">
                            <i class="fa-light fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color: var(--theme-muted-text-color);"></i>
                            <input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Search links') }}" class="h-11 w-full rounded-[0.8rem] border pl-9 pr-3 text-sm outline-none sm:w-80" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                        </div>
                        <x-ui.dialog width="2xl" dismissible>
                            <x-slot:trigger>
                                <button id="short-link-create-trigger" type="button" wire:click="resetCreateForm" class="inline-flex h-11 items-center justify-center gap-2 whitespace-nowrap rounded-[0.8rem] bg-black px-5 text-sm font-semibold text-white shadow-sm">
                                    <i class="fa-light fa-plus"></i>
                                    {{ __('New link') }}
                                </button>
                            </x-slot:trigger>
                        <form
                            wire:submit="addLink"
                            x-data="{
                                protect: false,
                                previewEdit: false,
                                passwordValue: $wire.entangle('form.password'),
                                generatePassword() {
                                    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
                                    const length = 14;
                                    const values = new Uint32Array(length);

                                    window.crypto.getRandomValues(values);
                                    this.passwordValue = Array.from(values, (value) => alphabet[value % alphabet.length]).join('');
                                    this.protect = true;
                                },
                            }"
                            class="p-2"
                        >
                            <div class="grid gap-7 lg:grid-cols-[minmax(0,1fr)_24rem]">
                                <div class="space-y-5">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-[0.65rem]" style="background-color: {{ $brandVisual['primary_color'] ?? '#facc15' }}; color: white;">
                                            @if (! empty($brandVisual['logo_url']))
                                                <img src="{{ $brandVisual['logo_url'] }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <i class="fa-light fa-globe"></i>
                                            @endif
                                        </span>
                                        <div>
                                            <h2 class="text-lg font-semibold leading-tight sm:text-xl" style="color: var(--theme-header-text-color);">{{ __('Create branded link & QR code') }}</h2>
                                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Create without leaving this page.') }}</p>
                                        </div>
                                    </div>

                                    @if ($createdUrl)
                                        <div class="rounded-[0.95rem] border p-3" style="border-color: rgba(16,185,129,0.28); background-color: rgba(16,185,129,0.08);">
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-700">{{ __('Created') }}</p>
                                            <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="break-all font-mono text-xs" style="color: var(--theme-header-text-color);">{{ $createdUrl }}</p>
                                                <button type="button" class="inline-flex h-9 shrink-0 items-center justify-center gap-2 rounded-[0.65rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-on:click="navigator.clipboard.writeText(@js($createdUrl))">
                                                    <i class="fa-light fa-copy"></i>
                                                    {{ __('Copy') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Destination URL') }}</label>
                                        <textarea wire:model.live.debounce.250ms="form.destination_url" x-on:blur="$wire.fetchCreateMetadata()" rows="2" placeholder="{{ __('Type or paste a link (URL)') }}" class="w-full rounded-[0.8rem] border px-4 py-3 text-sm outline-none focus:border-black focus:ring-4 focus:ring-black/5" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"></textarea>
                                        @error('destination_url') <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                                        <button type="button" wire:click="fetchCreateMetadata" wire:loading.attr="disabled" wire:target="fetchCreateMetadata" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.7rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                            <i class="fa-light fa-wand-magic-sparkles"></i>
                                            <span wire:loading.remove wire:target="fetchCreateMetadata">{{ __('Fetch preview metadata') }}</span>
                                            <span wire:loading wire:target="fetchCreateMetadata">{{ __('Fetching...') }}</span>
                                        </button>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-[minmax(0,0.42fr)_minmax(0,0.58fr)]">
                                        <x-ui.input wire:model.live.debounce.250ms="form.name" :label="__('Internal name')" :placeholder="__('Optional')" :error="$errors->first('name')" />
                                        <div class="space-y-2.5">
                                            <x-ui.label>{{ __('Short code') }}</x-ui.label>
                                            <div class="flex">
                                                <input wire:model.live.debounce.250ms="form.custom_code" type="text" placeholder="{{ __('custom-code') }}" class="h-11 min-w-0 flex-1 rounded-l-[0.8rem] border px-4 text-sm outline-none focus:border-black focus:ring-4 focus:ring-black/5" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                                                <button type="button" wire:click="generateCreateCode" wire:loading.attr="disabled" wire:target="generateCreateCode" class="h-11 rounded-r-[0.8rem] bg-black px-5 text-sm font-semibold text-white">
                                                    <span wire:loading.remove wire:target="generateCreateCode">{{ __('Generate') }}</span>
                                                    <span wire:loading wire:target="generateCreateCode">{{ __('Generating...') }}</span>
                                                </button>
                                            </div>
                                            @error('custom_code') <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <section class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand and tracking') }}</h3>
                                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Reuse Brand domains, UTM presets, and retargeting pixels for this short link.') }}</p>
                                            </div>
                                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem]" style="background-color: rgba(var(--theme-accent-rgb),0.10); color: var(--theme-accent);">
                                                <i class="fa-light fa-bullseye-arrow"></i>
                                            </span>
                                        </div>

                                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                                            <x-ui.select wire:model.defer="form.custom_domain_id" :label="__('Brand domain')" :error="$errors->first('custom_domain_id')">
                                                <option value="">{{ __('Default short link domain') }}</option>
                                                @foreach ($domains as $domain)
                                                    <option value="{{ $domain->id }}">{{ $domain->domain }} @if($domain->is_default)({{ __('default') }})@endif</option>
                                                @endforeach
                                            </x-ui.select>

                                            <x-ui.select wire:model.defer="form.utm_preset_id" :label="__('UTM preset')" :error="$errors->first('utm_preset_id')">
                                                <option value="">{{ __('Default UTM preset') }}</option>
                                                @foreach ($utmPresets as $preset)
                                                    <option value="{{ $preset->id }}">{{ $preset->name }} @if($preset->is_default)({{ __('default') }})@endif</option>
                                                @endforeach
                                            </x-ui.select>
                                        </div>

                                        <div class="mt-4">
                                            <p class="mb-2 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Tracking pixels') }}</p>
                                            @if ($pixels->isNotEmpty())
                                                <div class="grid gap-2 md:grid-cols-2">
                                                    @foreach ($pixels as $pixel)
                                                        <div class="flex cursor-pointer items-center gap-3 rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                                                            @php
                                                                $pixelChecked = in_array((int) $pixel->id, array_map('intval', (array) ($form['tracking_pixel_ids'] ?? [])), true);
                                                            @endphp
                                                            <x-ui.checkbox
                                                                id="create-tracking-pixel-{{ $pixel->id }}"
                                                                wire:model.live="form.tracking_pixel_ids"
                                                                value="{{ $pixel->id }}"
                                                                :checked="$pixelChecked"
                                                                minimal
                                                            />
                                                            <span class="min-w-0">
                                                                <span class="block truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $pixel->name }}</span>
                                                                <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ str($pixel->provider)->title() }} @if($pixel->is_default)({{ __('default') }})@endif</span>
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="rounded-[0.85rem] border border-dashed px-3 py-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-muted-text-color);">
                                                    {{ __('No tracking pixels in Brand yet.') }}
                                                </div>
                                            @endif
                                        </div>
                                    </section>

                                    <div class="flex items-center gap-3">
                                        <button type="button" x-on:click="protect = ! protect" class="relative inline-flex h-6 w-12 items-center rounded-full transition" :class="protect ? 'bg-black' : 'bg-slate-300'">
                                            <span class="inline-flex h-5 w-5 rounded-full bg-white shadow transition" :class="protect ? 'translate-x-6' : 'translate-x-1'"></span>
                                        </button>
                                        <span class="text-sm" style="color: var(--theme-header-text-color);">{{ __('Password protect this link') }}</span>
                                        <button type="button" x-on:click="generatePassword()" class="ml-auto inline-flex h-9 items-center justify-center gap-2 rounded-[0.7rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base); color: var(--theme-header-text-color);">
                                            <i class="fa-light fa-wand-magic-sparkles"></i>
                                            {{ __('Generate') }}
                                        </button>
                                    </div>

                                    <div x-show="protect" x-cloak>
                                        <x-ui.password-input
                                            wire:model.defer="form.password"
                                            x-model="passwordValue"
                                            :label="__('Password')"
                                            :error="$errors->first('password')"
                                            :placeholder="__('Generated or custom password')"
                                        />
                                    </div>
                                </div>

                                <aside class="space-y-4">
                                    <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link preview') }}</h3>
                                                <x-ui.tooltip :text="__('Shows the title, description, and image used when this short link is shared. Fetch metadata to update it from the destination page.')" placement="top">
                                                    <x-slot:trigger>
                                                        <button type="button" class="inline-flex h-5 w-5 items-center justify-center rounded-full text-sm" style="color: var(--theme-muted-text-color);" aria-label="{{ __('Link preview info') }}">
                                                            <i class="fa-light fa-circle-info"></i>
                                                        </button>
                                                    </x-slot:trigger>
                                                </x-ui.tooltip>
                                            </div>
                                            <button type="button" x-on:click="previewEdit = ! previewEdit" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Edit preview') }}">
                                                <i class="fa-light" :class="previewEdit ? 'fa-check' : 'fa-pen'"></i>
                                            </button>
                                        </div>

                                        <div class="mt-4 overflow-hidden rounded-[0.9rem]" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                                            @if (filled($form['og_image']))
                                                <img src="{{ $form['og_image'] }}" alt="" class="h-40 w-full object-cover" onerror="this.closest('div').classList.add('is-invalid-image'); this.remove();">
                                            @else
                                                <div class="flex h-40 items-center justify-center">
                                                    <div class="rounded-[1rem] border-2 border-dashed p-7" style="border-color: rgba(var(--theme-accent-rgb),0.55); background-color: var(--theme-surface-base); color: var(--theme-accent);">
                                                        <i class="fa-light fa-plus text-2xl"></i>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <p class="mt-4 line-clamp-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $form['og_title'] ?: ($form['name'] ?: __('No title')) }}</p>
                                        <p class="mt-2 line-clamp-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $form['og_description'] ?: __('No description') }}</p>

                                        <div x-show="previewEdit" x-cloak class="mt-4 space-y-3 rounded-[0.9rem] border p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                                            <x-ui.input wire:model.live.debounce.250ms="form.og_title" :label="__('Preview title')" :placeholder="__('Title shown on social previews')" :error="$errors->first('og_title')" />
                                            <div class="space-y-2">
                                                <x-ui.label>{{ __('Preview description') }}</x-ui.label>
                                                <textarea wire:model.live.debounce.250ms="form.og_description" rows="3" placeholder="{{ __('Short description shown under the title') }}" class="w-full rounded-[0.8rem] border px-4 py-3 text-sm outline-none focus:border-black focus:ring-4 focus:ring-black/5" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"></textarea>
                                                @error('og_description') <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                                            </div>
                                            <x-ui.image-picker
                                                wire:model.live="form.og_image"
                                                :value="$form['og_image'] ?? ''"
                                                :label="__('Preview image')"
                                                :error="$errors->first('og_image')"
                                                :help="__('Choose an image from your file library, upload a new image, or import from an image URL.')"
                                                :button-label="__('Choose image')"
                                                context="portal"
                                                layout="compact"
                                            />
                                            <div class="grid gap-2 sm:grid-cols-2">
                                                <button type="button" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.7rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="$set('form.og_title', '')">
                                                    <i class="fa-light fa-heading"></i>
                                                    {{ __('Clear title') }}
                                                </button>
                                                <button type="button" class="inline-flex h-9 items-center justify-center gap-2 rounded-[0.7rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="$set('form.og_image', '')">
                                                    <i class="fa-light fa-image-slash"></i>
                                                    {{ __('Clear image') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <div class="flex items-center justify-between">
                                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR Code') }}</h3>
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                                                <i class="fa-light fa-download"></i>
                                            </span>
                                        </div>
                                        <div class="mt-4 flex min-h-32 items-center justify-center rounded-[0.85rem] border px-4 py-5 text-center text-sm leading-6" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);">
                                            @if ($previewQrSvg)
                                                <div class="flex flex-col items-center gap-3">
                                                    <div class="[&_svg]:h-36 [&_svg]:w-36">{!! $previewQrSvg !!}</div>
                                                    <span class="max-w-[16rem] break-all text-xs">{{ $previewShortUrl }}</span>
                                                </div>
                                            @else
                                                <span class="max-w-[15rem]">{{ __('QR code appears automatically after entering a destination URL.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </aside>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-end" style="border-color: var(--theme-border-color);">
                                <x-ui.checkbox wire:model.live="copyAfterCreate" :checked="$copyAfterCreate" :label="__('Copy link to clipboard')" />
                                <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="addLink" class="justify-center bg-black text-white hover:bg-black/90">
                                    <span wire:loading.remove wire:target="addLink">{{ __('Create link') }}</span>
                                    <span wire:loading wire:target="addLink">{{ __('Creating...') }}</span>
                                </x-ui.button>
                            </div>
                        </form>
                    </x-ui.dialog>
                </div>
            </div>

            </div>

            <div class="grid gap-3 border-b px-4 py-4 sm:grid-cols-2 xl:grid-cols-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                @foreach ([
                    [
                        'label' => __('Links'),
                        'value' => $totalLinks,
                        'icon' => 'fa-link-simple',
                        'color' => '#2563eb',
                        'meta' => number_format($linksUsed).' / '.($linksLimit === null ? __('Unlimited') : number_format((int) $linksLimit)).' '.__('quota'),
                        'percent' => $linksLimit === null ? 100 : $linkPercent,
                        'limited' => $linksLimit !== null,
                    ],
                    ['label' => __('Active'), 'value' => $activeLinks, 'icon' => 'fa-circle-check', 'color' => '#059669'],
                    ['label' => __('Blocked'), 'value' => $blockedLinks, 'icon' => 'fa-ban', 'color' => '#dc2626'],
                    [
                        'label' => __('Clicks'),
                        'value' => number_format($totalClicks),
                        'icon' => 'fa-arrow-pointer',
                        'color' => '#7c3aed',
                        'meta' => number_format($clicksUsed).' / '.($clicksLimit === null ? __('Unlimited') : number_format((int) $clicksLimit)).' '.__('monthly'),
                        'percent' => $clicksLimit === null ? 100 : $clickPercent,
                        'limited' => $clicksLimit !== null,
                    ],
                ] as $stat)
                    <div class="rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $stat['label'] }}</p>
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-[0.7rem]" style="background-color: {{ $stat['color'] }}14; color: {{ $stat['color'] }};">
                                <i class="fa-light {{ $stat['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ $stat['value'] }}</p>
                        @if (isset($stat['meta']))
                            <div class="mt-3">
                                <div class="flex items-center justify-between gap-3 text-xs font-semibold" style="color: var(--theme-muted-text-color);">
                                    <span>{{ $stat['meta'] }}</span>
                                    @if (($stat['percent'] ?? 0) > 0 && ($stat['percent'] ?? 0) < 100)
                                        <span>{{ $stat['percent'] }}%</span>
                                    @endif
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background-color: rgba(var(--theme-border-color-rgb),0.72);">
                                    <div class="h-full rounded-full" style="width: {{ $stat['percent'] ?? 0 }}%; background-color: {{ ! empty($stat['limited']) && ($stat['percent'] ?? 0) >= 90 ? 'var(--theme-danger-color)' : $stat['color'] }};"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($viewMode === 'detail')
                @if ($selectedLink)
                    @php
                        $brandQrForeground = $brandVisual['primary_color'] ?? '#2563eb';
                        $brandQrWatermark = ! empty($brandVisual['logo_url']) ? 5 : 0;
                        $selectedQrSvg = $qrRenderer->svg($selectedLink);
                        $selectedDownloadName = str($selectedLink->name ?: 'short-link')->slug('-')->value() ?: 'short-link';
                    @endphp
                    <div class="grid min-h-[42rem] lg:h-[calc(100vh-14rem)] lg:grid-cols-[27rem_minmax(0,1fr)] lg:overflow-hidden">
                        <aside class="h-full border-b lg:overflow-y-auto lg:border-b-0 lg:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                            <div class="divide-y" style="--tw-divide-color: rgba(var(--theme-border-color-rgb),0.5);">
                                @foreach ($links as $link)
                                    <button type="button" wire:click="selectLink({{ $link->id }})" wire:loading.attr="disabled" wire:target="selectLink({{ $link->id }})" class="grid w-full grid-cols-[minmax(0,1fr)_8rem] gap-4 border-l-4 px-5 py-5 text-left transition hover:bg-white/70 disabled:cursor-wait disabled:opacity-70 dark:hover:bg-slate-950/20" style="border-left-color: {{ (int) $selectedLink->id === (int) $link->id ? 'var(--theme-accent)' : 'transparent' }}; background-color: {{ (int) $selectedLink->id === (int) $link->id ? 'var(--theme-surface-base)' : 'transparent' }};">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-3">
                                        <i wire:loading.remove wire:target="selectLink({{ $link->id }})" class="fa-light {{ $link->moderation_status === 'blocked' ? 'fa-ban text-red-500' : 'fa-layer-group' }} shrink-0" style="color: {{ $link->moderation_status === 'blocked' ? '' : 'var(--theme-header-text-color)' }};"></i>
                                        <i wire:loading wire:target="selectLink({{ $link->id }})" class="fa-light fa-spinner-third fa-spin shrink-0" style="color: var(--theme-accent);"></i>
                                                <p class="truncate text-base font-semibold" style="color: {{ $link->moderation_status === 'blocked' ? 'var(--theme-danger-color)' : 'var(--theme-header-text-color)' }};">{{ $link->shortUrl() }}</p>
                                            </div>
                                            <div class="mt-3 pl-8">
                                                <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Name:') }}</p>
                                                <p class="truncate text-sm" style="color: var(--theme-header-text-color);">{{ $link->name }}</p>
                                            </div>
                                        </div>
                                        <div class="pt-8">
                                            <p class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Click received:') }}</p>
                                            <p class="text-sm" style="color: var(--theme-header-text-color);">{{ number_format((int) $link->clicks_count) }} {{ trans_choice('click|clicks', (int) $link->clicks_count) }}</p>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </aside>

                        <main class="min-h-0 p-5 lg:h-full lg:overflow-y-auto lg:p-7">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <i class="fa-light fa-layer-group text-xl" style="color: var(--theme-header-text-color);"></i>
                                        <p class="break-all text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedLink->shortUrl() }}</p>
                                        <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.6rem] border text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-data x-on:click="navigator.clipboard.writeText(@js($selectedLink->shortUrl()))" title="{{ __('Copy') }}"><i class="fa-light fa-copy"></i></button>
                                        @if ($selectedLink->password_hash)
                                            <i class="fa-light fa-lock" style="color: var(--theme-header-text-color);" title="{{ __('Password protected') }}"></i>
                                        @endif
                                        <i class="fa-light fa-star" style="color: var(--theme-header-text-color);"></i>
                                    </div>
                                    <div class="mt-5 flex min-w-0 items-center gap-3">
                                        <i class="fa-light fa-globe" style="color: var(--theme-header-text-color);"></i>
                                        <p class="truncate text-sm" style="color: var(--theme-header-text-color);">{{ $selectedLink->destination_url }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                    <a href="{{ route('portal.short-links.edit', $selectedLink) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></a>
                                    <a href="{{ route('portal.short-links.analytics', ['link' => $selectedLink->id]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Analytics') }}"><i class="fa-light fa-chart-line"></i></a>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm disabled:cursor-wait disabled:opacity-70" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="duplicateLink({{ $selectedLink->id }})" wire:loading.attr="disabled" wire:target="duplicateLink({{ $selectedLink->id }})" title="{{ __('Duplicate') }}"><i wire:loading.remove wire:target="duplicateLink({{ $selectedLink->id }})" class="fa-light fa-clone"></i><i wire:loading wire:target="duplicateLink({{ $selectedLink->id }})" class="fa-light fa-spinner-third fa-spin"></i></button>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm disabled:cursor-wait disabled:opacity-70" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="toggleStatus({{ $selectedLink->id }})" wire:loading.attr="disabled" wire:target="toggleStatus({{ $selectedLink->id }})" title="{{ $selectedLink->status === 'active' ? __('Pause') : __('Activate') }}"><i wire:loading.remove wire:target="toggleStatus({{ $selectedLink->id }})" class="fa-light {{ $selectedLink->status === 'active' ? 'fa-pause' : 'fa-play' }}"></i><i wire:loading wire:target="toggleStatus({{ $selectedLink->id }})" class="fa-light fa-spinner-third fa-spin"></i></button>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-data x-on:click="downloadShortLinkQr({{ $selectedLink->id }}, @js($selectedDownloadName))" title="{{ __('Download QR') }}"><i class="fa-light fa-qrcode"></i></button>
                                    <x-ui.dialog :title="__('Delete short link?')" :description="__('This removes the short URL and its future redirects.')" width="sm" dismissible>
                                        <x-slot:trigger>
                                            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.7rem] border text-sm hover:!border-red-200 hover:!text-red-600" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash-can"></i></button>
                                        </x-slot:trigger>
                                        <x-slot:footer>
                                            <div class="flex justify-end gap-3">
                                                <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                <x-ui.button type="button" variant="danger" wire:click="deleteLink({{ $selectedLink->id }})" wire:loading.attr="disabled" wire:target="deleteLink({{ $selectedLink->id }})" x-on:click="open = false">
                                                    <span wire:loading.remove wire:target="deleteLink({{ $selectedLink->id }})">{{ __('Delete') }}</span>
                                                    <span wire:loading wire:target="deleteLink({{ $selectedLink->id }})">{{ __('Deleting...') }}</span>
                                                </x-ui.button>
                                            </div>
                                        </x-slot:footer>
                                    </x-ui.dialog>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-4 text-sm" style="color: var(--theme-muted-text-color);">
                                <span class="inline-flex items-center gap-2"><i class="fa-light fa-shield-check"></i>{{ $selectedLink->name }}</span>
                                <span class="inline-flex items-center gap-2"><i class="fa-light fa-calendar"></i>{{ $selectedLink->created_at?->format('M j, Y') }}</span>
                                <x-ui.badge :variant="$selectedLink->status === 'active' && $selectedLink->moderation_status !== 'blocked' ? 'success' : 'neutral'">{{ $selectedLink->moderation_status === 'blocked' ? __('Blocked') : str($selectedLink->status)->title() }}</x-ui.badge>
                            </div>

                            <div class="mt-7 grid gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]">
                                <section class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link preview') }}</h3>
                                            <x-ui.tooltip :text="__('Shows the title, description, and image used when this short link is shared. Fetch metadata to update it from the destination page.')" placement="top">
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-5 w-5 items-center justify-center rounded-full text-sm" style="color: var(--theme-muted-text-color);" aria-label="{{ __('Link preview info') }}">
                                                        <i class="fa-light fa-circle-info"></i>
                                                    </button>
                                                </x-slot:trigger>
                                            </x-ui.tooltip>
                                        </div>
                                        <x-ui.dialog :title="__('Edit link preview')" :description="__('Update the title, description, and image shown when this short link is shared.')" width="lg" dismissible>
                                            <x-slot:trigger>
                                                <button type="button" wire:click="beginPreviewEdit({{ $selectedLink->id }})" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Edit preview') }}"><i class="fa-light fa-pen"></i></button>
                                            </x-slot:trigger>

                                            <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_18rem]">
                                                <div class="space-y-4">
                                                    <x-ui.input wire:model.live.debounce.250ms="previewForm.og_title" :label="__('Preview title')" :placeholder="__('Title shown on social previews')" :error="$errors->first('og_title')" />
                                                    <div class="space-y-2">
                                                        <x-ui.label>{{ __('Preview description') }}</x-ui.label>
                                                        <textarea wire:model.live.debounce.250ms="previewForm.og_description" rows="4" placeholder="{{ __('Short description shown under the title') }}" class="w-full rounded-[0.8rem] border px-4 py-3 text-sm outline-none focus:border-black focus:ring-4 focus:ring-black/5" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"></textarea>
                                                        @error('og_description') <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                                                    </div>
                                                    <x-ui.image-picker
                                                        wire:model.live="previewForm.og_image"
                                                        :value="$previewForm['og_image'] ?? ''"
                                                        :label="__('Preview image')"
                                                        :error="$errors->first('og_image')"
                                                        :help="__('Choose an image from your file library, upload a new image, or import from an image URL.')"
                                                        :button-label="__('Choose image')"
                                                        context="portal"
                                                        layout="compact"
                                                    />
                                                    <div class="flex flex-wrap gap-2">
                                                        <button type="button" wire:click="fetchSelectedPreviewMetadata" wire:loading.attr="disabled" wire:target="fetchSelectedPreviewMetadata" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                                            <i class="fa-light fa-wand-magic-sparkles"></i>
                                                            <span wire:loading.remove wire:target="fetchSelectedPreviewMetadata">{{ __('Fetch metadata') }}</span>
                                                            <span wire:loading wire:target="fetchSelectedPreviewMetadata">{{ __('Fetching...') }}</span>
                                                        </button>
                                                        <button type="button" wire:click="$set('previewForm.og_image', '')" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                                            <i class="fa-light fa-image-slash"></i>
                                                            {{ __('Clear image') }}
                                                        </button>
                                                    </div>
                                                </div>

                                                <aside class="self-start rounded-[1rem] border p-3 shadow-sm" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                                                    <div class="overflow-hidden rounded-[0.85rem]" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                                                        @if (filled($previewForm['og_image']))
                                                            <img src="{{ $previewForm['og_image'] }}" alt="" class="aspect-[1.91/1] w-full object-cover">
                                                        @else
                                                            <div class="flex aspect-[1.91/1] w-full items-center justify-center">
                                                                <div class="rounded-[1rem] border-2 border-dashed p-6" style="border-color: rgba(var(--theme-accent-rgb),0.55); background-color: var(--theme-surface-base); color: var(--theme-accent);">
                                                                    <i class="fa-light fa-plus text-2xl"></i>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <p class="mt-4 line-clamp-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $previewForm['og_title'] ?: __('No title') }}</p>
                                                    <p class="mt-2 line-clamp-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $previewForm['og_description'] ?: __('No description') }}</p>
                                                </aside>
                                            </div>

                                            <x-slot:footer>
                                                <div class="flex justify-end gap-2">
                                                    <x-ui.button type="button" variant="outline" wire:click="cancelPreviewEdit" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                                    <x-ui.button type="button" wire:click="savePreviewEdit" x-on:click="open = false">{{ __('Save preview') }}</x-ui.button>
                                                </div>
                                            </x-slot:footer>
                                        </x-ui.dialog>
                                    </div>
                                    <div class="mt-4 overflow-hidden rounded-[0.9rem]" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                                        @if ($selectedLink->og_image)
                                            <img src="{{ $selectedLink->og_image }}" alt="" class="h-64 w-full object-cover">
                                        @else
                                            <div class="flex h-64 items-center justify-center">
                                                <div class="rounded-[1rem] border-2 border-dashed p-8" style="border-color: rgba(var(--theme-accent-rgb),0.55); background-color: var(--theme-surface-base); color: var(--theme-accent);">
                                                    <i class="fa-light fa-plus text-3xl"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mt-5 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedLink->og_title ?: __('No title') }}</p>
                                    <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ $selectedLink->og_description ?: __('No description') }}</p>
                                </section>

                                <section class="rounded-[1rem] border p-4" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-base);">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR Code') }}</h3>
                                            <x-ui.tooltip :text="__('Download or customize this QR code for print, packaging, campaigns, or quick sharing.')" placement="top" max-width="16rem">
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-5 w-5 items-center justify-center rounded-full text-sm" style="color: var(--theme-muted-text-color);" aria-label="{{ __('QR code info') }}">
                                                        <i class="fa-light fa-circle-info"></i>
                                                    </button>
                                                </x-slot:trigger>
                                            </x-ui.tooltip>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" x-data x-on:click="downloadShortLinkQr({{ $selectedLink->id }}, @js($selectedDownloadName))" title="{{ __('Download QR') }}"><i class="fa-light fa-download"></i></button>
                                            <x-ui.dialog :title="__('Customize and download your QR code')" width="3xl" dismissible>
                                                <x-slot:trigger>
                                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] border" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);" title="{{ __('Customize QR') }}"><i class="fa-light fa-pen"></i></button>
                                                </x-slot:trigger>

                                                <div
                                                    class="grid max-h-[78vh] gap-6 overflow-y-auto pr-1 lg:grid-cols-[minmax(0,1fr)_18rem]"
                                                    x-data="{
                                                        bg: '#ffffff',
                                                        fg: @js($brandQrForeground),
                                                        margin: 0,
                                                        watermark: @js($brandQrWatermark),
                                                        size: '512',
                                                        format: 'png',
                                                        downloadQr() {
                                                            window.downloadCustomizedShortLinkQr({
                                                                id: {{ $selectedLink->id }},
                                                                filename: @js($selectedDownloadName),
                                                                bg: this.bg,
                                                                fg: this.fg,
                                                                preserveColors: this.fg === @js($brandQrForeground),
                                                                margin: this.margin,
                                                                watermark: this.watermark,
                                                                size: this.size,
                                                                format: this.format,
                                                            });
                                                        },
                                                    }"
                                                    x-init="$nextTick(() => setTimeout(() => window.paintShortLinkQrPreview && window.paintShortLinkQrPreview({ id: {{ $selectedLink->id }}, bg, fg, margin, watermark }), 80))"
                                                    x-effect="window.paintShortLinkQrPreview && window.paintShortLinkQrPreview({ id: {{ $selectedLink->id }}, bg, fg, margin, watermark })"
                                                >
                                                    <div class="space-y-5">
                                                        <div class="grid gap-4 md:grid-cols-2">
                                                            <x-ui.color-picker x-model="bg" :label="__('Background color')" value="#ffffff" />
                                                            <x-ui.color-picker x-model="fg" :label="__('Foreground color')" :value="$brandQrForeground" />
                                                        </div>

                                                        <x-ui.field :label="__('Margin')">
                                                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="margin"></span>
                                                                <input x-model.number="margin" type="range" min="0" max="8" step="1" class="w-full accent-teal-600">
                                                            </div>
                                                        </x-ui.field>

                                                        <x-ui.field :label="__('Watermark size')">
                                                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="Number(watermark).toFixed(1)"></span>
                                                                <input x-model.number="watermark" type="range" min="0" max="10" step="0.5" class="w-full accent-teal-600">
                                                            </div>
                                                        </x-ui.field>

                                                        <div class="grid gap-4 md:grid-cols-2">
                                                            <x-ui.select x-model="size" :label="__('Export size')">
                                                                <option value="128">128 px</option>
                                                                <option value="256">256 px</option>
                                                                <option value="512">512 px</option>
                                                                <option value="1024">1024 px</option>
                                                                <option value="2048">2048 px</option>
                                                            </x-ui.select>
                                                            <x-ui.select x-model="format" :label="__('Format')">
                                                                <option value="png">PNG</option>
                                                                <option value="svg">SVG</option>
                                                            </x-ui.select>
                                                        </div>

                                                        <x-ui.button type="button" x-on:click.prevent.stop="downloadQr()">
                                                            <i class="fa-light fa-download"></i>{{ __('Download') }}
                                                        </x-ui.button>
                                                    </div>

                                                    <aside class="rounded-[1rem] border bg-slate-50 p-5" style="border-color: var(--theme-border-color);">
                                                        <div class="flex h-56 items-center justify-center rounded-[1rem] bg-white p-6 [&_svg]:h-40 [&_svg]:w-40">
                                                            <div
                                                                id="short-link-qr-preview-{{ $selectedLink->id }}"
                                                                x-html="window.customizedShortLinkQrSvg ? window.customizedShortLinkQrSvg({ id: {{ $selectedLink->id }}, bg, fg, margin, watermark, preserveColors: fg === @js($brandQrForeground) }) : ''"
                                                            ></div>
                                                        </div>
                                                        <p class="mt-4 break-all text-center text-xs" style="color: var(--theme-muted-text-color);">{{ $selectedLink->shortUrl() }}</p>
                                                    </aside>
                                                </div>

                                                @if (false)
                                                <div
                                                    class="hidden max-h-[78vh] gap-6 overflow-y-auto pr-1 lg:grid-cols-[minmax(0,1fr)_22rem]"
                                                    x-ignore
                                                    x-data="{
                                                        bg: '#ffffff',
                                                        fg: '#0f172a',
                                                        frame: '#2563eb',
                                                        margin: 2,
                                                        watermark: 5,
                                                        size: '512',
                                                        ecc: 'high',
                                                        shape: 'circle',
                                                        darkMin: 47,
                                                        darkMax: 105,
                                                        ghostStrength: 80,
                                                        ghostThreshold: 8,
                                                        useImageColors: true,
                                                        mode: 'basic',
                                                        template: 'clean_card',
                                                        templateGroups: @js($shortLinkQrTemplateGroups),
                                                        visibleTemplates() {
                                                            return Object.values(this.templateGroups[this.mode] || this.templateGroups.basic);
                                                        },
                                                        templateDescription() {
                                                            if (this.mode === 'mosaic') return 'Mosaic templates use the brand image and dot settings below.';
                                                            if (this.mode === 'roundness') return 'Roundness templates use rounded modules, gradient fills, and custom finder corners.';
                                                            return 'Scan-safe sticker, ring, coupon, and card presets. Choose one, then tune colors if needed.';
                                                        },
                                                        advancedTitle() {
                                                            if (this.mode === 'mosaic') return 'Advanced mosaic settings';
                                                            if (this.mode === 'roundness') return 'Advanced roundness settings';
                                                            return 'Advanced basic settings';
                                                        },
                                                        advancedDescription() {
                                                            if (this.mode === 'mosaic') return 'Control dot shape, correction level, ghost layer, and image color behavior for scan-safe artistic QR renders.';
                                                            if (this.mode === 'roundness') return 'Tune gradient colors, finder corners, export quality, and center logo size.';
                                                            return 'Tune QR colors, export quality, background, correction level, and center logo size.';
                                                        },
                                                        colorSectionTitle() {
                                                            if (this.mode === 'mosaic') return 'Mosaic colors';
                                                            if (this.mode === 'roundness') return 'Roundness colors';
                                                            return 'Basic colors';
                                                        },
                                                        colorSectionDescription() {
                                                            if (this.mode === 'mosaic') return 'Control the visible QR dots, brand accent dots, finder blocks, and background.';
                                                            if (this.mode === 'roundness') return 'Control the gradient start, gradient end, finder corner, and QR background.';
                                                            return 'Control the QR modules, frame accent, and QR background.';
                                                        },
                                                        fgLabel() {
                                                            if (this.mode === 'mosaic') return 'Main dots';
                                                            if (this.mode === 'roundness') return 'Gradient start';
                                                            return 'QR dots';
                                                        },
                                                        frameLabel() {
                                                            if (this.mode === 'mosaic') return 'Accent dots';
                                                            if (this.mode === 'roundness') return 'Gradient end';
                                                            return 'Frame / accent';
                                                        },
                                                        setMode(value) {
                                                            this.mode = value;
                                                            this.apply(this.visibleTemplates()[0]);
                                                        },
                                                        svgTemplateId(item) { return `short-link-qr-template-{{ $selectedLink->id }}-${item.key}`; },
                                                        svgMarkup(item) {
                                                            const template = document.getElementById(this.svgTemplateId(item));
                                                            return template ? template.innerHTML.trim() : '';
                                                        },
                                                        apply(item) {
                                                            this.template = item.key;
                                                            this.bg = item.background;
                                                            this.fg = item.foreground;
                                                            this.frame = item.accent;
                                                            this.shape = item.shape || 'circle';
                                                        },
                                                        downloadQr(format = 'png') {
                                                            window.downloadCustomizedShortLinkQr({
                                                                id: {{ $selectedLink->id }},
                                                                template: this.template,
                                                                filename: @js($selectedDownloadName),
                                                                bg: this.bg,
                                                                fg: this.fg,
                                                                frame: this.frame,
                                                                size: this.size,
                                                                format,
                                                            });
                                                        }
                                                    }"
                                                    x-init="$nextTick(() => window.paintShortLinkQrPreview({ id: {{ $selectedLink->id }}, template, bg, fg, frame }))"
                                                    x-effect="window.paintShortLinkQrPreview({ id: {{ $selectedLink->id }}, template, bg, fg, frame })"
                                                >
                                                    <div class="space-y-6">
                                                        <section>
                                                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Design mode') }}</h3>
                                                            <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose Basic for reliable campaign QR designs, or Mosaic for image-driven brand QR.') }}</p>
                                                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                                                <button type="button" x-on:click.prevent="setMode('basic')" class="rounded-[1rem] border p-4 text-left" x-bind:class="mode === 'basic' ? 'bg-blue-50 ring-1 ring-blue-500' : 'bg-slate-50'" style="border-color: var(--theme-border-color);">
                                                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.8rem] bg-blue-100 text-blue-700"><i class="fa-light fa-qrcode"></i></span>
                                                                    <span class="mt-4 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Basic') }}</span>
                                                                    <span class="mt-2 block text-sm" style="color: var(--theme-muted-text-color);">{{ __('Sticker, coupon, ring, and card designs that keep QR contrast simple.') }}</span>
                                                                </button>
                                                                <button type="button" x-on:click.prevent="setMode('roundness')" class="rounded-[1rem] border p-4 text-left" x-bind:class="mode === 'roundness' ? 'bg-blue-50 ring-1 ring-blue-500' : 'bg-slate-50'" style="border-color: var(--theme-border-color);">
                                                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.8rem] bg-slate-100 text-slate-700"><i class="fa-light fa-border-all"></i></span>
                                                                    <span class="mt-4 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Roundness') }}</span>
                                                                    <span class="mt-2 block text-sm" style="color: var(--theme-muted-text-color);">{{ __('Rounded QR modules with gradient colors and custom finder corners.') }}</span>
                                                                </button>
                                                                <button type="button" x-on:click.prevent="setMode('mosaic')" class="rounded-[1rem] border p-4 text-left" x-bind:class="mode === 'mosaic' ? 'bg-blue-50 ring-1 ring-blue-500' : 'bg-slate-50'" style="border-color: var(--theme-border-color);">
                                                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.8rem] bg-slate-100 text-slate-700"><i class="fa-light fa-grid-2-plus"></i></span>
                                                                    <span class="mt-4 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Mosaic') }}</span>
                                                                    <span class="mt-2 block text-sm" style="color: var(--theme-muted-text-color);">{{ __('Brand-image inspired dots with advanced scan-safe controls.') }}</span>
                                                                </button>
                                                            </div>
                                                        </section>

                                                        <section>
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div>
                                                                    <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR style templates') }}</h3>
                                                                    <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);" x-text="templateDescription()"></p>
                                                                </div>
                                                                <span class="shrink-0 rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.16em]" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);"><span x-text="visibleTemplates().length"></span> {{ __('templates') }}</span>
                                                            </div>
                                                            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                                                <template x-for="item in visibleTemplates()" :key="item.key">
                                                                    <button type="button" x-on:click.prevent="apply(item)" class="rounded-[1rem] border p-3 text-left transition hover:-translate-y-0.5 hover:shadow-sm" x-bind:class="template === item.key ? 'bg-blue-50 ring-1 ring-blue-500' : 'bg-slate-50'" style="border-color: var(--theme-border-color);">
                                                                        <span class="flex h-28 items-center justify-center overflow-hidden rounded-[0.8rem]" x-bind:style="`background-color: ${item.surface}`">
                                                                            <span class="flex h-20 w-20 items-center justify-center rounded-[0.75rem] bg-white p-2 shadow-sm [&_svg]:h-full [&_svg]:w-full" x-html="svgMarkup(item)"></span>
                                                                        </span>
                                                                        <span class="mt-3 block font-semibold" style="color: var(--theme-header-text-color);" x-text="item.label"></span>
                                                                        <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);" x-text="item.description"></span>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </section>

                                                        <section class="rounded-[1rem] border p-5" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                                            <div class="flex items-start justify-between gap-3">
                                                                <div>
                                                                    <h3 class="font-semibold" style="color: var(--theme-header-text-color);" x-text="advancedTitle()"></h3>
                                                                    <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);" x-text="advancedDescription()"></p>
                                                                </div>
                                                                <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                                                                    <i class="fa-light fa-sliders"></i>{{ __('Advanced') }}
                                                                </span>
                                                            </div>

                                                            <div x-show="mode === 'mosaic'" class="mt-5 grid gap-4 md:grid-cols-3">
                                                                <x-ui.select x-model="shape" :label="__('Shape')">
                                                                    <option value="circle">{{ __('Circle') }}</option>
                                                                    <option value="rounded">{{ __('Rounded') }}</option>
                                                                    <option value="square">{{ __('Square') }}</option>
                                                                    <option value="diamond">{{ __('Diamond') }}</option>
                                                                </x-ui.select>
                                                                <x-ui.select x-model="ecc" :label="__('ECC')">
                                                                    <option value="high">{{ __('High') }}</option>
                                                                    <option value="quartile">{{ __('Quartile') }}</option>
                                                                    <option value="medium">{{ __('Medium') }}</option>
                                                                    <option value="low">{{ __('Low') }}</option>
                                                                </x-ui.select>
                                                                <x-ui.select x-model="size" :label="__('Output px')">
                                                                    <option value="512">512</option>
                                                                    <option value="1024">1024</option>
                                                                    <option value="2048">2048</option>
                                                                </x-ui.select>
                                                            </div>

                                                            <div x-show="mode === 'mosaic'" class="mt-5 space-y-4">
                                                                <div>
                                                                    <div class="mb-2 flex items-center justify-between gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                                                                        <span>{{ __('Dark dot range') }}</span>
                                                                        <span class="font-semibold" style="color: var(--theme-header-text-color);"><span x-text="darkMin"></span>% - <span x-text="darkMax"></span>%</span>
                                                                    </div>
                                                                    <div class="grid gap-3 md:grid-cols-2">
                                                                        <input x-model.number="darkMin" type="range" min="0" max="100" class="w-full accent-blue-600">
                                                                        <input x-model.number="darkMax" type="range" min="1" max="140" class="w-full accent-blue-600">
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <div class="mb-2 flex items-center justify-between gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                                                                        <span>{{ __('Ghost strength') }}</span>
                                                                        <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="`${ghostStrength}%`"></span>
                                                                    </div>
                                                                    <input x-model.number="ghostStrength" type="range" min="0" max="100" class="w-full accent-blue-600">
                                                                </div>
                                                                <div>
                                                                    <div class="mb-2 flex items-center justify-between gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                                                                        <span>{{ __('Ghost threshold') }}</span>
                                                                        <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="`${ghostThreshold}%`"></span>
                                                                    </div>
                                                                    <input x-model.number="ghostThreshold" type="range" min="0" max="100" class="w-full accent-blue-600">
                                                                </div>
                                                            </div>

                                                            <div x-show="mode !== 'mosaic'" class="mt-5 grid gap-4 md:grid-cols-2">
                                                                <x-ui.select x-model="ecc" :label="__('ECC')">
                                                                    <option value="high">{{ __('High') }}</option>
                                                                    <option value="quartile">{{ __('Quartile') }}</option>
                                                                    <option value="medium">{{ __('Medium') }}</option>
                                                                    <option value="low">{{ __('Low') }}</option>
                                                                </x-ui.select>
                                                                <x-ui.select x-model="size" :label="__('Output px')">
                                                                    <option value="512">512</option>
                                                                    <option value="1024">1024</option>
                                                                    <option value="2048">2048</option>
                                                                </x-ui.select>
                                                            </div>

                                                            <div class="mt-6">
                                                                <h4 class="font-semibold" style="color: var(--theme-header-text-color);" x-text="colorSectionTitle()"></h4>
                                                                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);" x-text="colorSectionDescription()"></p>
                                                                <template x-if="mode === 'basic'">
                                                                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                                                                        <x-ui.color-picker x-model="fg" :label="__('QR dots')" value="#0f172a" />
                                                                        <x-ui.color-picker x-model="frame" :label="__('Frame / accent')" value="#2563eb" />
                                                                        <x-ui.color-picker x-model="bg" :label="__('Background')" value="#ffffff" />
                                                                    </div>
                                                                </template>
                                                                <template x-if="mode === 'roundness'">
                                                                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                                                                        <x-ui.color-picker x-model="fg" :label="__('Gradient start')" value="#b02a2a" />
                                                                        <x-ui.color-picker x-model="frame" :label="__('Gradient end')" value="#264653" />
                                                                        <x-ui.color-picker x-model="bg" :label="__('Background')" value="#ffffff" />
                                                                    </div>
                                                                </template>
                                                                <template x-if="mode === 'mosaic'">
                                                                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                                                                        <x-ui.color-picker x-model="fg" :label="__('Main dots')" value="#0f172a" />
                                                                        <x-ui.color-picker x-model="frame" :label="__('Accent dots')" value="#2563eb" />
                                                                        <x-ui.color-picker x-model="bg" :label="__('Background')" value="#ffffff" />
                                                                    </div>
                                                                </template>
                                                            </div>

                                                            <div x-show="mode !== 'mosaic'" class="mt-5">
                                                                <div class="mb-2 flex items-center justify-between gap-3 text-sm" style="color: var(--theme-muted-text-color);">
                                                                    <span>{{ __('Center logo size') }}</span>
                                                                    <span class="font-semibold" style="color: var(--theme-header-text-color);" x-text="`${watermark}%`"></span>
                                                                </div>
                                                                <input x-model.number="watermark" type="range" min="0" max="24" class="w-full accent-blue-600">
                                                            </div>

                                                            <label x-show="mode === 'mosaic'" class="mt-5 flex items-center gap-3 rounded-[0.85rem] border bg-white px-4 py-3 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">
                                                                <input x-model="useImageColors" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600">
                                                                <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Use image colors') }}</span>
                                                                <span>{{ __('Sample brand image colors for mosaic exports.') }}</span>
                                                            </label>
                                                        </section>
                                                    </div>

                                                    <aside class="space-y-4 lg:sticky lg:top-0 lg:self-start">
                                                        <section class="rounded-[1rem] border bg-white p-5" style="border-color: var(--theme-border-color);">
                                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--theme-muted-text-color);">{{ __('Live QR') }}</p>
                                                            <h3 class="mt-2 font-semibold" style="color: var(--theme-header-text-color);" x-text="mode === 'mosaic' ? @js(__('Mosaic preview')) : @js(__('Preview'))"></h3>
                                                            <div class="mt-5 rounded-[1rem] p-5" x-bind:style="`background-color: ${bg}`">
                                                                <div class="rounded-[1rem] border-4 bg-white p-5 shadow-sm" x-bind:style="`border-color: ${frame}`">
                                                                    <div id="short-link-qr-preview-{{ $selectedLink->id }}" class="flex items-center justify-center [&_svg]:h-44 [&_svg]:w-44"></div>
                                                                </div>
                                                                <p class="mt-4 break-all text-center text-xs" style="color: var(--theme-muted-text-color);">{{ $selectedLink->shortUrl() }}</p>
                                                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                                                    <x-ui.button type="button" variant="outline" size="sm" x-on:click.prevent.stop="downloadQr('png')">
                                                                        <i class="fa-light fa-download"></i>{{ __('Download PNG') }}
                                                                    </x-ui.button>
                                                                    <x-ui.button type="button" variant="outline" size="sm" x-on:click.prevent.stop="downloadQr('svg')">
                                                                        <i class="fa-light fa-code"></i>{{ __('Download SVG') }}
                                                                    </x-ui.button>
                                                                </div>
                                                            </div>
                                                        </section>

                                                        <section class="rounded-[1rem] border bg-slate-50 p-4" style="border-color: var(--theme-border-color);">
                                                            <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Need a poster?') }}</h3>
                                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('AI-looking QR images often break scanner contrast. This builder keeps the QR payload intact first, then applies brand styling around a reliable base.') }}</p>
                                                        </section>

                                                        <x-ui.button type="button" block x-on:click.prevent.stop="downloadQr('png')">
                                                            <i class="fa-light fa-floppy-disk"></i>{{ __('Save QR') }}
                                                        </x-ui.button>
                                                    </aside>
                                                </div>
                                                @endif
                                            </x-ui.dialog>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex h-80 items-center justify-center rounded-[0.9rem] bg-white p-6 [&_svg]:h-48 [&_svg]:w-48" style="background-color: rgba(var(--theme-border-color-rgb),0.18);">
                                        {!! $selectedQrSvg !!}
                                    </div>
                                    <template id="short-link-qr-svg-{{ $selectedLink->id }}">{!! $selectedQrSvg !!}</template>
                                </section>
                            </div>

                            <div class="mt-6 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.65rem] bg-yellow-300 text-slate-950"><i class="fa-light fa-wrench"></i></span>
                                    <h2 class="text-2xl font-semibold" style="color: var(--theme-header-text-color);">{{ __('Optimize') }}</h2>
                                </div>
                                <div class="mt-5 space-y-3">
                                    <details open class="rounded-[0.85rem] border" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 font-semibold" style="color: var(--theme-header-text-color);">
                                            <span class="inline-flex items-center gap-3"><i class="fa-light fa-globe-pointer"></i>{{ __('Brand domain') }}</span>
                                            <i class="fa-light fa-chevron-down"></i>
                                        </summary>
                                        <div class="border-t px-4 py-4" style="border-color: var(--theme-border-color);">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                                                <select x-data class="h-11 min-w-0 rounded-[0.8rem] border bg-white px-3 text-sm outline-none lg:w-80" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-on:change="$wire.applySelectedCustomDomain($event.target.value)">
                                                    <option value="" @selected(! $selectedLink->custom_domain_id)>{{ __('Default short link domain') }}</option>
                                                    @foreach ($domains as $domain)
                                                        <option value="{{ $domain->id }}" @selected((int) $selectedLink->custom_domain_id === (int) $domain->id)>{{ $domain->domain }}</option>
                                                    @endforeach
                                                </select>
                                                <a href="{{ route('portal.brand.domains') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                                    <i class="fa-light fa-arrow-up-right-from-square"></i>{{ __('Manage domains') }}
                                                </a>
                                            </div>
                                            <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Uses verified Custom Domains from Brand. The public short URL updates immediately after saving.') }}</p>
                                        </div>
                                    </details>

                                    <details open class="rounded-[0.85rem] border" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 font-semibold" style="color: var(--theme-header-text-color);">
                                            <span class="inline-flex items-center gap-3"><i class="fa-light fa-wand-magic-sparkles"></i>{{ __('UTM Builder') }}</span>
                                            <i class="fa-light fa-chevron-down"></i>
                                        </summary>
                                        <div class="border-t px-4 py-4" style="border-color: var(--theme-border-color);">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                                                <select x-data class="h-11 min-w-0 rounded-[0.8rem] border bg-white px-3 text-sm outline-none lg:w-80" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-on:change="$wire.applySelectedUtmPreset($event.target.value)">
                                                    <option value="" @selected(! $selectedLink->utm_preset_id)>{{ __('No UTM preset') }}</option>
                                                    @foreach ($utmPresets as $preset)
                                                        <option value="{{ $preset->id }}" @selected((int) $selectedLink->utm_preset_id === (int) $preset->id)>{{ $preset->name }}</option>
                                                    @endforeach
                                                </select>
                                                <a href="{{ route('portal.brand.utm-presets') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-[0.8rem] border px-4 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                                    <i class="fa-light fa-arrow-up-right-from-square"></i>{{ __('Manage UTM presets') }}
                                                </a>
                                            </div>
                                            <p class="mt-3 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Uses Brand UTM Presets. The redirect destination is decorated through BrandOperations::applyUtm().') }}</p>
                                        </div>
                                    </details>

                                    <details class="rounded-[0.85rem] border" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 font-semibold" style="color: var(--theme-header-text-color);">
                                            <span class="inline-flex items-center gap-3"><i class="fa-light fa-route"></i>{{ __('Traffic routing') }}</span>
                                            <i class="fa-light fa-chevron-down"></i>
                                        </summary>
                                        <div class="border-t px-4 py-4" style="border-color: var(--theme-border-color);">
                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                <div class="min-w-0">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Traffic routing') }}</p>
                                                        @if ($selectedLink->redirect_rules)
                                                            <x-ui.badge variant="primary">{{ __('Rules active') }}</x-ui.badge>
                                                        @else
                                                            <x-ui.badge variant="neutral">{{ __('Not configured') }}</x-ui.badge>
                                                        @endif
                                                    </div>
                                                    <p class="mt-2 text-sm" style="color: var(--theme-muted-text-color);">{{ $selectedLink->redirect_rules ? __('Routing rules are active. Open Edit to update exact URLs, countries, devices, and schedules.') : __('Open Edit to configure A/B variants, rotator, geo, device, or time rules.') }}</p>
                                                </div>
                                                <x-ui.button href="{{ route('portal.short-links.edit', $selectedLink) }}" variant="outline" size="sm" wire:navigate>
                                                    <i class="fa-light fa-pen"></i>{{ __('Edit routing') }}
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </details>

                                    <details open class="rounded-[0.85rem] border" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 font-semibold" style="color: var(--theme-header-text-color);">
                                            <span class="inline-flex items-center gap-3"><i class="fa-light fa-bullseye-arrow"></i>{{ __('Retargeting scripts') }}</span>
                                            <i class="fa-light fa-chevron-down"></i>
                                        </summary>
                                        <div class="border-t px-4 py-4" style="border-color: var(--theme-border-color);">
                                            @if ($pixels->isNotEmpty())
                                                <div class="grid gap-2 md:grid-cols-2">
                                                    @foreach ($pixels as $pixel)
                                                        @php $pixelActive = in_array((int) $pixel->id, array_map('intval', (array) $selectedLink->tracking_pixel_ids), true); @endphp
                                                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-[0.8rem] border bg-white px-3 py-3 text-sm" style="border-color: var(--theme-border-color);">
                                                            <span class="min-w-0">
                                                                <span class="block truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $pixel->name }}</span>
                                                                <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ str($pixel->provider)->title() }}</span>
                                                            </span>
                                                            <x-ui.checkbox
                                                                :checked="$pixelActive"
                                                                wire:change="toggleSelectedTrackingPixel({{ $pixel->id }})"
                                                                minimal
                                                            />
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="rounded-[0.8rem] border border-dashed p-4 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No tracking pixels in Brand yet.') }}</div>
                                            @endif
                                            <div class="mt-3">
                                                <a href="{{ route('portal.brand.tracking-pixels') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border px-3 text-sm font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                                    <i class="fa-light fa-arrow-up-right-from-square"></i>{{ __('Manage tracking pixels') }}
                                                </a>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </div>

                            <div class="mt-6 border-t pt-6" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                                <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                                    <div class="flex items-start gap-3">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-[0.65rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);"><i class="fa-light fa-sparkles"></i></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <h3 class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('Best time to share') }}</h3>
                                                <x-ui.badge variant="neutral">{{ __('Analytics') }}</x-ui.badge>
                                            </div>
                                            <p class="mt-2 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Recommendations appear after this link receives enough clicks by time, device, and location.') }}</p>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-[0.85rem] border bg-white px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Optimal window') }}</p>
                                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $selectedLink->last_clicked_at ? __('Calculating') : __('Available after clicks') }}</p>
                                                </div>
                                                <a href="{{ route('portal.short-links.analytics', ['link' => $selectedLink->id]) }}" wire:navigate class="inline-flex min-h-14 items-center justify-center gap-2 rounded-[0.85rem] bg-black px-4 text-sm font-semibold text-white">
                                                    <i class="fa-light fa-chart-line"></i>
                                                    {{ __('View analytics') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </main>
                    </div>
                @else
                    @include('appshortlinks::partials.empty-state')
                @endif
            @elseif ($viewMode === 'grid')
                <div class="grid gap-4 p-4 lg:grid-cols-2 2xl:grid-cols-3">
                    @forelse ($links as $link)
                        @include('appshortlinks::partials.link-card', ['link' => $link, 'qrRenderer' => $qrRenderer])
                    @empty
                        @include('appshortlinks::partials.empty-state')
                    @endforelse
                </div>
            @else
                <div class="hidden grid-cols-[2.5rem_minmax(0,1fr)_9rem_10rem_18rem] border-b px-6 py-3 text-xs font-semibold uppercase tracking-[0.12em] lg:grid" style="border-color: rgba(var(--theme-border-color-rgb),0.62); color: var(--theme-muted-text-color);">
                    <div>
                        <label class="inline-flex cursor-pointer items-center" title="{{ __('Select page') }}">
                            <input type="checkbox" class="peer sr-only" wire:click="toggleCurrentPageSelection" wire:loading.attr="disabled" wire:target="toggleCurrentPageSelection" @checked($pageAllSelected)>
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-[0.4rem] border border-slate-300 bg-white text-white transition peer-checked:border-[color:var(--theme-accent)] peer-checked:bg-[color:var(--theme-accent)] peer-checked:[&_svg]:opacity-100">
                                <svg viewBox="0 0 16 16" aria-hidden="true" class="h-3.5 w-3.5 opacity-0 transition-opacity" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2">
                                    <path d="M3.5 8.5 6.5 11.5 12.5 4.5" />
                                </svg>
                            </span>
                        </label>
                    </div>
                    <div>{{ __('Branded link') }} ({{ number_format($links->count()) }} / {{ number_format($totalLinks) }})</div>
                    <div>{{ __('Clicks') }}</div>
                    <div>{{ __('Created') }}</div>
                    <div class="text-right">{{ __('Actions') }}</div>
                </div>

                <div class="divide-y" style="--tw-divide-color: rgba(var(--theme-border-color-rgb),0.52);">
                    @forelse ($links as $link)
                        <div class="group grid gap-4 px-4 py-5 transition hover:bg-slate-50/70 lg:grid-cols-[2.5rem_minmax(0,1fr)_9rem_10rem_18rem] lg:items-center lg:px-6 dark:hover:bg-slate-900/20">
                            <div class="flex items-center">
                                <x-ui.checkbox wire:model.live="selectedLinkIds" value="{{ $link->id }}" minimal />
                            </div>
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                                    <i class="fa-light {{ $link->moderation_status === 'blocked' ? 'fa-ban text-red-500' : ($link->redirect_rules ? 'fa-route' : 'fa-link-simple') }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $link->shortUrl() }}</p>
                                        <x-ui.badge :variant="$link->status === 'active' && $link->moderation_status !== 'blocked' ? 'success' : 'neutral'">{{ $link->moderation_status === 'blocked' ? __('Blocked') : str($link->status)->title() }}</x-ui.badge>
                                        @if ($link->password_hash)
                                            <x-ui.badge variant="neutral">{{ __('Protected') }}</x-ui.badge>
                                        @endif
                                        @if ($link->redirect_rules)
                                            <x-ui.badge variant="primary">{{ __('Rules') }}</x-ui.badge>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm" style="color: var(--theme-muted-text-color);">
                                        <span class="truncate">{{ $link->name }}</span>
                                        @if ($link->campaign)
                                            <span class="inline-flex items-center gap-1"><i class="fa-light fa-bullhorn"></i>{{ $link->campaign }}</span>
                                        @endif
                                        @if ($link->folder)
                                            <span class="inline-flex items-center gap-1"><i class="fa-light fa-folder"></i>{{ $link->folder }}</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ $link->destination_url }}</p>
                                </div>
                            </div>

                            <div class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) $link->clicks_count) }} {{ trans_choice('click|clicks', (int) $link->clicks_count) }}</div>
                            <div class="text-sm" style="color: var(--theme-muted-text-color);">{{ $link->created_at?->diffForHumans() }}</div>

                            <div class="flex flex-wrap justify-start gap-1.5 lg:justify-end">
                                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" x-data x-on:click="navigator.clipboard.writeText(@js($link->shortUrl())); window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'success', message: @js(__('Short link copied.')) } }))" title="{{ __('Copy') }}"><i class="fa-light fa-copy"></i></button>
                                <a href="{{ route('portal.short-links.analytics', ['link' => $link->id]) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Analytics') }}"><i class="fa-light fa-chart-line"></i></a>
                                <a href="{{ route('portal.short-links.edit', $link) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Edit') }}"><i class="fa-light fa-pen"></i></a>
                                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none disabled:cursor-not-allowed disabled:opacity-60" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="duplicateLink({{ $link->id }})" wire:loading.attr="disabled" wire:target="duplicateLink({{ $link->id }})" title="{{ __('Duplicate') }}">
                                    <i class="fa-light fa-clone" wire:loading.remove wire:target="duplicateLink({{ $link->id }})"></i>
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="duplicateLink({{ $link->id }})"></i>
                                </button>
                                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none disabled:cursor-not-allowed disabled:opacity-60" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="toggleStatus({{ $link->id }})" wire:loading.attr="disabled" wire:target="toggleStatus({{ $link->id }})" title="{{ $link->status === 'active' ? __('Pause') : __('Activate') }}">
                                    <i class="fa-light {{ $link->status === 'active' ? 'fa-pause' : 'fa-play' }}" wire:loading.remove wire:target="toggleStatus({{ $link->id }})"></i>
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="toggleStatus({{ $link->id }})"></i>
                                </button>
                                <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none disabled:cursor-not-allowed disabled:opacity-60" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" wire:click="openQrModal({{ $link->id }})" wire:loading.attr="disabled" wire:target="openQrModal({{ $link->id }})" title="{{ __('Open QR') }}">
                                    <i class="fa-light fa-qrcode" wire:loading.remove wire:target="openQrModal({{ $link->id }})"></i>
                                    <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="openQrModal({{ $link->id }})"></i>
                                </button>
                                <x-ui.dialog :title="__('Delete short link?')" :description="__('This removes the short URL and its future redirects.')" width="sm" dismissible>
                                    <x-slot:trigger>
                                        <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.65rem] border text-sm leading-none hover:!border-red-200 hover:!text-red-600" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" title="{{ __('Delete') }}"><i class="fa-light fa-trash-can"></i></button>
                                    </x-slot:trigger>
                                    <x-slot:footer>
                                        <div class="flex justify-end gap-3">
                                            <x-ui.button type="button" variant="outline" x-on:click="open = false">{{ __('Cancel') }}</x-ui.button>
                                            <x-ui.button type="button" variant="danger" wire:click="deleteLink({{ $link->id }})" wire:loading.attr="disabled" wire:target="deleteLink({{ $link->id }})" x-on:click="open = false">
                                                <span wire:loading.remove wire:target="deleteLink({{ $link->id }})">{{ __('Delete') }}</span>
                                                <span wire:loading wire:target="deleteLink({{ $link->id }})">{{ __('Deleting...') }}</span>
                                            </x-ui.button>
                                        </div>
                                    </x-slot:footer>
                                </x-ui.dialog>
                            </div>
                        </div>

                    @empty
                        @include('appshortlinks::partials.empty-state')
                    @endforelse
                </div>
            @endif

            @if (method_exists($links, 'links') && $links->hasPages())
                <div class="border-t px-4 py-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    {{ $links->links() }}
                </div>
            @endif
        </section>

        <x-ui.modal :title="__('Customize and download your QR code')" width="xl" dismissible open-event="short-link-qr-modal-open">
            @if ($qrModalLink)
                @php
                    $brandQrForeground = $brandVisual['primary_color'] ?? '#2563eb';
                    $brandQrWatermark = ! empty($brandVisual['logo_url']) ? 5 : 0;
                    $qrModalSvg = $qrRenderer->svg($qrModalLink);
                    $qrModalDownloadName = str($qrModalLink->name ?: 'short-link')->slug('-')->value() ?: 'short-link';
                @endphp

                <div
                    wire:key="short-link-qr-modal-{{ $qrModalLink->id }}"
                    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]"
                    x-data="{
                        bg: '#ffffff',
                        fg: @js($brandQrForeground),
                        margin: 0,
                        watermark: @js($brandQrWatermark),
                        size: '512',
                        format: 'png',
                        downloadQr() {
                            window.downloadCustomizedShortLinkQr({
                                id: {{ $qrModalLink->id }},
                                filename: @js($qrModalDownloadName),
                                bg: this.bg,
                                fg: this.fg,
                                preserveColors: this.fg === @js($brandQrForeground),
                                margin: this.margin,
                                watermark: this.watermark,
                                size: this.size,
                                format: this.format,
                            });
                        },
                    }"
                >
                    <div class="space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.color-picker x-model="bg" :label="__('Background color')" value="#ffffff" />
                            <x-ui.color-picker x-model="fg" :label="__('Foreground color')" :value="$brandQrForeground" />
                        </div>

                        <x-ui.field :label="__('Margin')">
                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="margin"></span>
                                <input x-model.number="margin" type="range" min="0" max="8" step="1" class="w-full accent-teal-600">
                            </div>
                        </x-ui.field>

                        <x-ui.field :label="__('Watermark size')">
                            <div class="grid grid-cols-[4.5rem_minmax(0,1fr)] items-center gap-3">
                                <span class="inline-flex h-10 items-center justify-center rounded-[0.75rem] bg-slate-400 text-sm font-bold text-white" x-text="Number(watermark).toFixed(1)"></span>
                                <input x-model.number="watermark" type="range" min="0" max="10" step="0.5" class="w-full accent-teal-600">
                            </div>
                        </x-ui.field>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-ui.select x-model="size" :label="__('Export size')">
                                <option value="128">128 px</option>
                                <option value="256">256 px</option>
                                <option value="512">512 px</option>
                                <option value="1024">1024 px</option>
                                <option value="2048">2048 px</option>
                            </x-ui.select>
                            <x-ui.select x-model="format" :label="__('Format')">
                                <option value="png">PNG</option>
                                <option value="svg">SVG</option>
                            </x-ui.select>
                        </div>

                        <x-ui.button type="button" x-on:click.prevent.stop="downloadQr()">
                            <i class="fa-light fa-download"></i>
                            {{ __('Download') }}
                        </x-ui.button>
                    </div>

                    <aside class="rounded-[1rem] border p-5" style="border-color: var(--theme-border-color); background-color: var(--theme-surface-soft);">
                        <div class="rounded-[1rem] p-5" x-bind:style="`background-color: ${bg}`">
                            <div class="flex min-h-56 items-center justify-center [&_svg]:h-44 [&_svg]:w-44" x-html="window.customizedShortLinkQrSvg ? window.customizedShortLinkQrSvg({ id: {{ $qrModalLink->id }}, bg, fg, margin, watermark, preserveColors: fg === @js($brandQrForeground) }) : ''"></div>
                        </div>
                        <p class="mt-4 break-all text-center text-xs" style="color: var(--theme-muted-text-color);">{{ $qrModalLink->shortUrl() }}</p>
                    </aside>
                </div>

                <template id="short-link-qr-modal-svg-{{ $qrModalLink->id }}">{!! $qrModalSvg !!}</template>
            @else
                <div class="flex min-h-56 items-center justify-center">
                    <span class="inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--theme-muted-text-color);">
                        <i class="fa-light fa-spinner-third fa-spin"></i>
                        {{ __('Loading QR...') }}
                    </span>
                </div>
            @endif
        </x-ui.modal>
    </div>
</div>

@once
    <script>
        window.downloadShortLinkQr = function (id, filename) {
            const template = document.getElementById(`short-link-qr-modal-svg-${id}`)
                || document.getElementById(`short-link-qr-svg-${id}`);
            if (!template) return;

            const source = template.innerHTML.trim();
            const safeName = (filename || `short-link-${id}`).replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || `short-link-${id}`;
            const blob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${safeName}-qr.svg`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        };

        window.downloadShortLinkQrPng = function (id, filename, size = 768) {
            const template = document.getElementById(`short-link-qr-modal-svg-${id}`)
                || document.getElementById(`short-link-qr-svg-${id}`);
            if (!template) return;

            const source = template.innerHTML.trim();
            const safeName = (filename || `short-link-${id}`).replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || `short-link-${id}`;
            const svgBlob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);
            const image = new Image();

            image.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = size;
                canvas.height = size;
                const context = canvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, size, size);
                context.drawImage(image, 0, 0, size, size);
                URL.revokeObjectURL(url);

                canvas.toBlob(function (blob) {
                    if (!blob) return;

                    const pngUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = pngUrl;
                    link.download = `${safeName}-qr.png`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(pngUrl);
                }, 'image/png');
            };

            image.src = url;
        };

        window.customizedShortLinkQrSvg = function ({ id, template = '', bg = '#ffffff', fg = '#000000', frame = '', margin = 0, watermark = 5, preserveColors = false }) {
            let source = '';

            if (template) {
                const styledTemplate = document.getElementById(`short-link-qr-template-${id}-${template}`);

                if (styledTemplate) {
                    source = styledTemplate.innerHTML.trim();
                }
            }

            if (!source) {
                const baseTemplate = document.getElementById(`short-link-qr-modal-svg-${id}`)
                    || document.getElementById(`short-link-qr-svg-${id}`);
                if (!baseTemplate) return '';
                source = baseTemplate.innerHTML.trim();
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(source, 'image/svg+xml');
            const svg = doc.documentElement;
            const isLight = (value) => ['#fff', '#ffffff', 'white', 'rgb(255,255,255)', 'rgb(255, 255, 255)'].includes(String(value || '').trim().toLowerCase());
            const isAccent = (value) => {
                const color = String(value || '').trim().toLowerCase();
                return ['#2563eb', '#2a9d8f', '#14b8a6', '#0f766e', '#64748b', '#f97316', '#fb7185', '#16a34a', '#f59e0b', '#ec4899', '#a855f7', '#65a30d', '#7dd3fc', '#94a3b8'].includes(color);
            };

            if (!preserveColors) {
                svg.querySelectorAll('[fill]').forEach((node) => {
                    const fill = node.getAttribute('fill');
                    if (!fill || fill === 'none') return;
                    const isBackgroundRect = node.tagName.toLowerCase() === 'rect'
                        && node.getAttribute('width') === '100%'
                        && node.getAttribute('height') === '100%';

                    if (isBackgroundRect) {
                        node.setAttribute('fill', bg);
                        return;
                    }

                    if (fill.startsWith('url(')) {
                        node.setAttribute('fill', fg);
                        return;
                    }
                    node.setAttribute('fill', isLight(fill) ? bg : (frame && isAccent(fill) ? frame : fg));
                });

                svg.querySelectorAll('[stroke]').forEach((node) => {
                    const stroke = node.getAttribute('stroke');
                    if (!stroke || stroke === 'none') return;
                    if (stroke.startsWith('url(')) {
                        node.setAttribute('stroke', fg);
                        return;
                    }
                    node.setAttribute('stroke', isLight(stroke) ? bg : (frame && isAccent(stroke) ? frame : fg));
                });
            } else {
                svg.querySelectorAll('[fill]').forEach((node) => {
                    const fill = node.getAttribute('fill');
                    const isBackgroundRect = node.tagName.toLowerCase() === 'rect'
                        && node.getAttribute('width') === '100%'
                        && node.getAttribute('height') === '100%';

                    if (isBackgroundRect || isLight(fill)) {
                        node.setAttribute('fill', bg);
                    }
                });
            }

            svg.querySelectorAll('text').forEach((node) => {
                if (Number(watermark) <= 0) {
                    node.setAttribute('display', 'none');
                } else {
                    node.setAttribute('font-size', String(Number(watermark) * 3.2));
                    if (!preserveColors) {
                        node.setAttribute('fill', fg);
                    }
                }
            });

            const viewBox = (svg.getAttribute('viewBox') || '0 0 256 256').split(/\s+/).map(Number);
            const width = viewBox[2] || 256;
            const height = viewBox[3] || 256;
            const logoScale = Math.max(0, Math.min(10, Number(watermark) || 0)) / 5;

            svg.querySelectorAll('image').forEach((imageNode) => {
                if (logoScale <= 0) {
                    imageNode.setAttribute('display', 'none');
                    return;
                }

                const nextSize = Math.max(8, Math.round(width * 0.145 * logoScale));
                const nextX = Math.round((width - nextSize) / 2);
                imageNode.setAttribute('x', String(nextX));
                imageNode.setAttribute('y', String(nextX));
                imageNode.setAttribute('width', String(nextSize));
                imageNode.setAttribute('height', String(nextSize));
                imageNode.removeAttribute('display');

                const frameSize = Math.round(nextSize * 1.32);
                const frameX = Math.round((width - frameSize) / 2);
                const nearbyRects = Array.from(svg.querySelectorAll('rect')).filter((rect) => {
                    const rectWidth = Number(rect.getAttribute('width') || 0);
                    const rectHeight = Number(rect.getAttribute('height') || 0);
                    const rectX = Number(rect.getAttribute('x') || 0);
                    const rectY = Number(rect.getAttribute('y') || 0);

                    return rectWidth > width * 0.08
                        && rectWidth < width * 0.32
                        && Math.abs(rectWidth - rectHeight) < 2
                        && Math.abs((rectX + rectWidth / 2) - width / 2) < width * 0.08
                        && Math.abs((rectY + rectHeight / 2) - height / 2) < height * 0.08;
                });

                nearbyRects.forEach((rect) => {
                    rect.setAttribute('x', String(frameX));
                    rect.setAttribute('y', String(frameX));
                    rect.setAttribute('width', String(frameSize));
                    rect.setAttribute('height', String(frameSize));
                    rect.setAttribute('rx', String(Math.round(frameSize * 0.24)));
                    rect.removeAttribute('display');
                });
            });

            const padding = Math.max(0, Number(margin) || 0) * 8;
            const serialized = new XMLSerializer().serializeToString(svg);

            if (padding <= 0) {
                return serialized;
            }

            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width + padding * 2} ${height + padding * 2}"><rect width="100%" height="100%" fill="${bg}"/><svg x="${padding}" y="${padding}" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">${serialized.replace(/^<svg[^>]*>|<\/svg>$/g, '')}</svg></svg>`;
        };

        window.paintShortLinkQrPreview = function (options) {
            const preview = document.getElementById(`short-link-qr-preview-${options.id}`);
            if (!preview) return;

            preview.innerHTML = window.customizedShortLinkQrSvg(options);
        };

        window.downloadCustomizedShortLinkQr = function (options) {
            try {
                const svg = window.customizedShortLinkQrSvg(options);
                if (!svg) {
                    window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'warning', message: 'QR preview is not ready.' } }));
                    return;
                }

                const safeName = (options.filename || `short-link-${options.id}`).replace(/[^a-z0-9-_]+/gi, '-').replace(/^-+|-+$/g, '').toLowerCase() || `short-link-${options.id}`;
                const format = String(options.format || 'png').toLowerCase();

                const downloadSvg = function () {
                    const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `${safeName}-qr.svg`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(url);
                };

                if (format === 'svg') {
                    downloadSvg();
                    return;
                }

                const size = Math.max(128, Number(options.size) || 512);
                const image = new Image();
                const blob = new Blob([svg], { type: 'image/svg+xml;charset=utf-8' });
                const url = URL.createObjectURL(blob);

                image.onload = function () {
                    const canvas = document.createElement('canvas');
                    canvas.width = size;
                    canvas.height = size;
                    const context = canvas.getContext('2d');
                    context.fillStyle = options.bg || '#ffffff';
                    context.fillRect(0, 0, size, size);
                    context.drawImage(image, 0, 0, size, size);
                    URL.revokeObjectURL(url);

                    try {
                        canvas.toBlob((pngBlob) => {
                            if (!pngBlob) {
                                downloadSvg();
                                return;
                            }
                            const pngUrl = URL.createObjectURL(pngBlob);
                            const link = document.createElement('a');
                            link.href = pngUrl;
                            link.download = `${safeName}-qr.png`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            URL.revokeObjectURL(pngUrl);
                        }, 'image/png');
                    } catch (error) {
                        downloadSvg();
                    }
                };

                image.onerror = downloadSvg;

                image.src = url;
            } catch (error) {
                window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'danger', message: 'Unable to download QR.' } }));
            }
        };

        window.addEventListener('short-link-created', function (event) {
            const detail = event.detail || {};

            if (detail.copy && detail.url && navigator.clipboard) {
                navigator.clipboard.writeText(detail.url);
            }
        });
    </script>
@endonce
