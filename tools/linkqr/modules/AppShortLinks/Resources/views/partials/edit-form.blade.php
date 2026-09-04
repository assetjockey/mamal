<div
    class="space-y-8"
    x-data="{
        editPasswordValue: $wire.entangle('editForm.password'),
        generateEditPassword() {
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
            const length = 14;
            const values = new Uint32Array(length);

            window.crypto.getRandomValues(values);
            this.editPasswordValue = Array.from(values, (value) => alphabet[value % alphabet.length]).join('');
        },
    }"
>
    <section class="space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Link details') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Name the link, control the alias, and update the destination URL.') }}</p>
            </div>
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.09); color: var(--theme-accent);">
                <i class="fa-light fa-link-simple"></i>
            </span>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-ui.input wire:model.defer="editForm.name" :label="__('Name')" :error="$errors->first('name')" />
            <div class="space-y-2.5">
                <x-ui.label>{{ __('Short code') }}</x-ui.label>
                <div class="flex">
                    <input wire:model.defer="editForm.custom_code" type="text" class="h-11 min-w-0 flex-1 rounded-l-[0.8rem] border px-4 text-sm outline-none focus:border-black focus:ring-4 focus:ring-black/5" style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);">
                    <button type="button" wire:click="generateEditCode" wire:loading.attr="disabled" wire:target="generateEditCode" class="h-11 rounded-r-[0.8rem] bg-black px-5 text-sm font-semibold text-white">
                        <span wire:loading.remove wire:target="generateEditCode">{{ __('Generate') }}</span>
                        <span wire:loading wire:target="generateEditCode">{{ __('Generating...') }}</span>
                    </button>
                </div>
                @if ($errors->first('custom_code'))
                    <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $errors->first('custom_code') }}</p>
                @endif
            </div>
        </div>

        <div class="hidden grid gap-4 lg:grid-cols-3">
            <x-ui.input wire:model.defer="editForm.folder" :label="__('Folder')" :placeholder="__('Paid campaigns')" :error="$errors->first('folder')" />
            <x-ui.input wire:model.defer="editForm.campaign" :label="__('Campaign')" :placeholder="__('Q4 launch')" :error="$errors->first('campaign')" />
            <x-ui.input wire:model.defer="editForm.tags" :label="__('Tags')" :placeholder="__('facebook, sale')" :error="$errors->first('tags')" />
        </div>

        <div class="space-y-2">
            <x-ui.input wire:model.live.debounce.300ms="editForm.destination_url" x-on:blur="$wire.fetchEditMetadata()" :label="__('Destination URL')" :error="$errors->first('destination_url')" />
            <button type="button" wire:click="fetchEditMetadata" wire:loading.attr="disabled" wire:target="fetchEditMetadata" class="inline-flex h-10 items-center justify-center gap-2 rounded-[0.75rem] border bg-white px-3 text-xs font-semibold shadow-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                <i class="fa-light fa-wand-magic-sparkles" wire:loading.remove wire:target="fetchEditMetadata"></i>
                <i class="fa-light fa-spinner-third fa-spin" wire:loading wire:target="fetchEditMetadata"></i>
                <span wire:loading.remove wire:target="fetchEditMetadata">{{ __('Fetch preview metadata') }}</span>
                <span wire:loading wire:target="fetchEditMetadata">{{ __('Fetching...') }}</span>
            </button>
        </div>
    </section>

    <section class="space-y-4 border-t pt-7" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand and tracking') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Reuse Brand domains, UTM presets, and retargeting pixels for this short link.') }}</p>
            </div>
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem] bg-emerald-50 text-emerald-700">
                <i class="fa-light fa-bullseye-arrow"></i>
            </span>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-ui.select wire:model.defer="editForm.status" :label="__('Status')" :error="$errors->first('status')">
                <option value="active">{{ __('Active') }}</option>
                <option value="paused">{{ __('Paused') }}</option>
            </x-ui.select>
            <x-ui.select wire:model.defer="editForm.custom_domain_id" :label="__('Brand domain')" :error="$errors->first('custom_domain_id')">
                <option value="">{{ __('Default app domain') }}</option>
                @foreach ($domains as $domain)
                    <option value="{{ $domain->id }}">{{ $domain->domain }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.select wire:model.defer="editForm.utm_preset_id" :label="__('UTM preset')" :error="$errors->first('utm_preset_id')">
                <option value="">{{ __('None') }}</option>
                @foreach ($utmPresets as $preset)
                    <option value="{{ $preset->id }}">{{ $preset->name }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <x-ui.field :label="__('Tracking pixels')" :error="$errors->first('tracking_pixel_ids')">
            @if ($pixels->isNotEmpty())
                <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($pixels as $pixel)
                        <label class="flex cursor-pointer items-center gap-3 rounded-[0.85rem] border bg-white px-3 py-3 text-sm transition hover:shadow-sm" style="border-color: var(--theme-border-color);">
                            @php($pixelChecked = in_array((int) $pixel->id, array_map('intval', (array) ($editForm['tracking_pixel_ids'] ?? [])), true))
                            <x-ui.checkbox
                                wire:model.defer="editForm.tracking_pixel_ids"
                                value="{{ $pixel->id }}"
                                :checked="$pixelChecked"
                                minimal
                            />
                            <span class="min-w-0">
                                <span class="block truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $pixel->name }}</span>
                                <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ str($pixel->provider)->title() }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="rounded-[0.9rem] border border-dashed p-4 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-muted-text-color);">{{ __('No tracking pixels in Brand yet.') }}</div>
            @endif
        </x-ui.field>
    </section>

    <section class="space-y-4 border-t pt-7" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Traffic routing') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Run A/B destination tests, split traffic by weight, pause variants, or pin a winner.') }}</p>
            </div>
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem] bg-amber-50 text-amber-700">
                <i class="fa-light fa-route"></i>
            </span>
        </div>

        <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background-color: var(--theme-surface-soft);">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h4 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('A/B Test') }}</h4>
                        @if (! empty($abStats))
                            <x-ui.badge variant="primary">{{ count($abStats) }} {{ __('variants') }}</x-ui.badge>
                        @else
                            <x-ui.badge variant="neutral">{{ __('Not configured') }}</x-ui.badge>
                        @endif
                    </div>
                    <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('Variant weights become split percentages. Winner overrides the split until you clear it.') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if (empty($abStats))
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="createAbTest">
                            <i class="fa-light fa-flask"></i>
                            {{ __('Create A/B test') }}
                        </x-ui.button>
                    @else
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="addAbVariant">
                            <i class="fa-light fa-plus"></i>
                            {{ __('Add variant') }}
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="autoSelectAbWinner('clicks')">
                            <i class="fa-light fa-trophy"></i>
                            {{ __('Auto by clicks') }}
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="autoSelectAbWinner('ctr')">
                            <i class="fa-light fa-chart-line-up"></i>
                            {{ __('Auto by CTR') }}
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" size="sm" wire:click="clearAbWinner">
                            {{ __('Clear winner') }}
                        </x-ui.button>
                    @endif
                </div>
            </div>

            @if (! empty($abStats))
                @php($totalWeight = max(1, collect($abVariants)->sum(fn ($variant) => max(0, (int) ($variant['weight'] ?? 0)))))
                <div class="mt-4 overflow-hidden rounded-[0.85rem] border bg-white" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
                    <div class="flex h-3">
                        @foreach ($abVariants as $variant)
                            @php($width = round((max(0, (int) ($variant['weight'] ?? 0)) / $totalWeight) * 100, 2))
                            <span class="{{ ($variant['status'] ?? 'active') === 'paused' ? 'bg-slate-300' : (($variant['winner'] ?? false) ? 'bg-emerald-500' : 'bg-blue-600') }}" style="width: {{ $width }}%;"></span>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($abVariants as $index => $variant)
                        @php($stat = collect($abStats)->firstWhere('variant_key', $variant['variant_key'] ?? '') ?? [])
                        @php($weight = max(0, (int) ($variant['weight'] ?? 0)))
                        @php($split = round(($weight / $totalWeight) * 100, 1))
                        <div class="rounded-[0.95rem] border bg-white p-3" style="border-color: rgba(var(--theme-border-color-rgb),0.66);">
                            <div class="grid gap-3 xl:grid-cols-[5rem_minmax(8rem,0.9fr)_minmax(14rem,1.6fr)_8rem_auto] xl:items-end">
                                <div>
                                    <x-ui.input wire:model.defer="abVariants.{{ $index }}.variant_key" :label="__('Key')" />
                                </div>
                                <div>
                                    <x-ui.input wire:model.defer="abVariants.{{ $index }}.name" :label="__('Variant name')" />
                                </div>
                                <div>
                                    <x-ui.input wire:model.defer="abVariants.{{ $index }}.url" :label="__('Destination URL')" />
                                </div>
                                <div>
                                    <x-ui.input type="number" min="0" max="100" wire:model.defer="abVariants.{{ $index }}.weight" :label="__('Weight')" />
                                </div>
                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                    <button type="button" wire:click="toggleAbVariant({{ $index }})" class="inline-flex h-10 items-center gap-2 rounded-[0.75rem] border px-3 text-xs font-semibold" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                                        <i class="fa-light {{ ($variant['status'] ?? 'active') === 'paused' ? 'fa-play' : 'fa-pause' }}"></i>
                                        {{ ($variant['status'] ?? 'active') === 'paused' ? __('Resume') : __('Pause') }}
                                    </button>
                                    <button type="button" wire:click="markAbWinner({{ $index }})" class="inline-flex h-10 items-center gap-2 rounded-[0.75rem] border px-3 text-xs font-semibold" style="border-color: {{ ($variant['winner'] ?? false) ? 'rgba(16,185,129,0.55)' : 'var(--theme-border-color)' }}; color: {{ ($variant['winner'] ?? false) ? '#059669' : 'var(--theme-header-text-color)' }};">
                                        <i class="fa-light fa-trophy"></i>
                                        {{ ($variant['winner'] ?? false) ? __('Winner') : __('Set winner') }}
                                    </button>
                                    @if (count($abVariants) > 2)
                                        <button type="button" wire:click="removeAbVariant({{ $index }})" class="inline-flex h-10 w-10 items-center justify-center rounded-[0.75rem] border text-red-600" style="border-color: rgba(239,68,68,0.35);">
                                            <i class="fa-light fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                <div class="rounded-[0.75rem] px-3 py-2" style="background-color: var(--theme-surface-soft);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Split') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $split }}%</p>
                                </div>
                                <div class="rounded-[0.75rem] px-3 py-2" style="background-color: var(--theme-surface-soft);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Variant clicks') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ number_format((int) ($stat['clicks'] ?? 0)) }}</p>
                                </div>
                                <div class="rounded-[0.75rem] px-3 py-2" style="background-color: var(--theme-surface-soft);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('Observed share') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $stat['split'] ?? 0 }}%</p>
                                </div>
                                <div class="rounded-[0.75rem] px-3 py-2" style="background-color: var(--theme-surface-soft);">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ __('CTR index') }}</p>
                                    <p class="mt-1 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $stat['ctr_index'] ?? 0 }}%</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <x-ui.field :label="__('Redirect rules JSON')" :error="$errors->first('redirect_rules')" :help="__('Rules are saved as an ordered array. Each rule can include url, weight, countries, devices, starts_at, and ends_at.')">
            <div class="mb-3 flex flex-wrap gap-2">
                <x-ui.button type="button" wire:click="applyEditRedirectTemplate('ab')" variant="outline" size="sm">
                    <i class="fa-light fa-flask"></i>{{ __('A/B template') }}
                </x-ui.button>
                <x-ui.button type="button" wire:click="applyEditRedirectTemplate('geo')" variant="outline" size="sm">
                    <i class="fa-light fa-globe"></i>{{ __('Geo') }}
                </x-ui.button>
                <x-ui.button type="button" wire:click="applyEditRedirectTemplate('device')" variant="outline" size="sm">
                    <i class="fa-light fa-mobile-screen"></i>{{ __('Device') }}
                </x-ui.button>
                <x-ui.button type="button" wire:click="applyEditRedirectTemplate('time')" variant="outline" size="sm">
                    <i class="fa-light fa-clock"></i>{{ __('Time') }}
                </x-ui.button>
                <x-ui.button type="button" wire:click="applyEditRedirectTemplate('clear')" variant="danger" size="sm">
                    <i class="fa-light fa-xmark"></i>{{ __('Clear') }}
                </x-ui.button>
            </div>
            <textarea wire:model.defer="editForm.redirect_rules" rows="8" class="w-full rounded-[0.9rem] border bg-white px-3 py-3 font-mono text-xs leading-5 shadow-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);" placeholder='[{"url":"https://example.com/a","weight":70},{"url":"https://example.com/b","devices":["mobile"]}]'></textarea>
        </x-ui.field>
    </section>

    <section class="space-y-4 border-t pt-7" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Access control') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Protect this link with expiry, click caps, or a password.') }}</p>
            </div>
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem] bg-rose-50 text-rose-700">
                <i class="fa-light fa-shield-keyhole"></i>
            </span>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-ui.datetime-picker
                wire:model.defer="editForm.expires_at"
                :label="__('Expires at')"
                :error="$errors->first('expires_at')"
                :placeholder="__('Select expiry time')"
                picker-align="left"
                picker-position="auto"
            />
            <x-ui.input type="number" min="1" wire:model.defer="editForm.click_limit" :label="__('Click limit')" :placeholder="__('Unlimited')" :error="$errors->first('click_limit')" />
            <x-ui.password-input
                wire:model.defer="editForm.password"
                x-model="editPasswordValue"
                :label="__('New password')"
                :error="$errors->first('password')"
                :placeholder="__('Leave blank to keep current password')"
            >
                <x-slot:action>
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="generateEditPassword()">
                        {{ __('Generate') }}
                    </x-ui.button>
                </x-slot:action>
            </x-ui.password-input>
        </div>
        @if ($link->password_hash)
            <label class="inline-flex cursor-pointer items-center gap-3 rounded-[0.85rem] border bg-white px-3 py-3 text-sm" style="border-color: var(--theme-border-color); color: var(--theme-header-text-color);">
                <input type="checkbox" wire:model.defer="editForm.clear_password" class="h-4 w-4 rounded border-slate-300">
                <span>{{ __('Remove password protection') }}</span>
            </label>
        @endif
    </section>

    <section class="space-y-4 border-t pt-7" style="border-color: rgba(var(--theme-border-color-rgb),0.62);">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Social preview') }}</h3>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Control the title, description, and image shown when this link is shared.') }}</p>
            </div>
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.8rem] bg-sky-50 text-sky-700">
                <i class="fa-light fa-image"></i>
            </span>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-4">
                <x-ui.input wire:model.defer="editForm.og_title" :label="__('OG title')" :placeholder="__('Title shown on social previews')" :error="$errors->first('og_title')" />

                <div class="space-y-2">
                    <x-ui.label>{{ __('OG description') }}</x-ui.label>
                    <textarea
                        wire:model.defer="editForm.og_description"
                        rows="5"
                        placeholder="{{ __('Short description shown below the title') }}"
                        class="w-full resize-y rounded-[0.8rem] border px-4 py-3 text-sm leading-6 outline-none focus:border-black focus:ring-4 focus:ring-black/5"
                        style="border-color: var(--theme-border-color); background-color: var(--theme-input-surface); color: var(--theme-input-text);"
                    ></textarea>
                    @error('og_description') <p class="text-sm font-medium" style="color: var(--theme-danger-color);">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="min-w-0">
                <x-ui.image-picker
                    wire:model.defer="editForm.og_image"
                    :value="$editForm['og_image'] ?? ''"
                    :label="__('OG image')"
                    :error="$errors->first('og_image')"
                    :help="__('Choose an image from your file library, upload a new image, or import from an image URL.')"
                    :button-label="__('Choose image')"
                    context="portal"
                    layout="compact"
                />
            </div>
        </div>
    </section>

    <div class="sticky bottom-4 z-10 flex flex-col gap-3 rounded-[1rem] border bg-white/95 p-3 shadow-[0_20px_55px_-35px_rgba(15,23,42,0.45)] backdrop-blur sm:flex-row sm:items-center sm:justify-between" style="border-color: rgba(var(--theme-border-color-rgb),0.7);">
        <div class="min-w-0">
            <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Save link changes') }}</p>
            <p class="mt-1 truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Changes affect future redirects immediately after saving.') }}</p>
        </div>
        <div class="flex shrink-0 justify-end gap-2">
            <x-ui.button type="button" variant="outline" wire:click="cancelEdit">{{ __('Cancel') }}</x-ui.button>
            <x-ui.button type="button" wire:click="saveEdit">
                <i class="fa-light fa-floppy-disk"></i>
                {{ __('Save changes') }}
            </x-ui.button>
        </div>
    </div>
</div>
