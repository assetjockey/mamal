@php
    $selectedTemplateKey = (string) data_get($form, 'style_template', 'clean_card');
    $selectedTemplate = $qrTemplates[$selectedTemplateKey] ?? $qrTemplates['clean_card'];
    $templateAccent = $selectedTemplate['accent'] ?? '#2563eb';
    $templateSurface = $selectedTemplate['surface'] ?? '#ffffff';
    $logoUrl = trim((string) data_get($form, 'logo_url', ''));
    $aiPreviewSvg = trim((string) data_get($qrCode->settings, 'ai_qr.preview_svg', ''));
    $aiArtworkUrl = trim((string) ($resolvedAiArtworkUrl ?? data_get($qrCode->settings, 'ai_qr.background_url', '')));
    $aiArtworkModel = trim((string) data_get($qrCode->settings, 'ai_qr.model', ''));
    $aiArtworkIsGeneratedImage = $aiArtworkUrl !== '' && str_contains($aiArtworkModel, 'qr_code_controlnet');
    $hasAiArtwork = $aiPreviewSvg !== '' || $aiArtworkIsGeneratedImage;
    $isAiQrCode = (string) data_get($qrCode->settings, 'source', '') === 'ai_qr_codes';
    $designMode = (string) data_get($form, 'design_mode', 'basic');
    $logoSize = max(8, min(28, (int) data_get($form, 'logo_size', 18)));
    $previewLogoClass = $logoSize >= 24 ? 'h-14 w-14' : ($logoSize >= 20 ? 'h-12 w-12' : ($logoSize >= 16 ? 'h-10 w-10' : 'h-8 w-8'));
    $visibleTemplates = collect($qrTemplates)
        ->filter(fn ($template) => match ($designMode) {
            'mosaic' => ($template['pattern'] ?? null) === 'mosaic',
            'roundness' => ($template['pattern'] ?? null) === 'gradient_rounded',
            default => ! in_array(($template['pattern'] ?? null), ['mosaic', 'gradient_rounded'], true),
        })
        ->all();
@endphp

<div class="space-y-5 px-4 pb-8 pt-4 sm:px-5 xl:px-6">
    <section class="overflow-hidden rounded-[1.25rem] border shadow-[0_18px_52px_-44px_rgba(15,23,42,0.45)]" style="border-color: rgba(var(--theme-border-color-rgb),0.7); background: linear-gradient(135deg, var(--theme-surface-base), color-mix(in srgb, var(--theme-surface-soft) 72%, transparent));">
        <div class="flex flex-col gap-4 px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <a href="{{ route('portal.qr-codes.index') }}" class="mt-1 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] border transition hover:-translate-x-0.5" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background-color: var(--theme-surface-base); color: var(--theme-muted-text-color);" title="{{ __('Back to QR Codes') }}" aria-label="{{ __('Back to QR Codes') }}">
                    <i class="fa-light fa-arrow-left"></i>
                </a>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                            <i class="fa-light {{ $typeMeta['icon'] ?? 'fa-qrcode' }}"></i>
                        </span>
                        <x-ui.badge variant="neutral">{{ $typeMeta['label'] }}</x-ui.badge>
                        <x-ui.badge :variant="$qrCode->status === 'active' ? 'success' : 'neutral'">{{ ucfirst($qrCode->status) }}</x-ui.badge>
                        <span class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $typeMeta['kind'] }}</span>
                    </div>
                    <h1 class="mt-2 truncate text-[1.65rem] font-semibold tracking-[-0.035em]" style="color: var(--theme-header-text-color);">{{ $qrCode->name }}</h1>
                    <p class="mt-1 max-w-2xl truncate text-sm" style="color: var(--theme-muted-text-color);">{{ $displayShortUrl ?: data_get($form, 'destination_url') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 lg:justify-end">
                @if ($qrCode->shortUrl())
                    <x-ui.button href="{{ $qrCode->shortUrl() }}" target="_blank" variant="outline">
                        <i class="fa-light fa-arrow-up-right-from-square"></i>
                        {{ __('Open') }}
                    </x-ui.button>
                @endif
                @if (($canUseQrTypeBuilders ?? true) && $qrCode->type !== 'bio_links' && Route::has('portal.qr-codes.type-content'))
                    <x-ui.button href="{{ route('portal.qr-codes.type-content', ['qrCode' => $qrCode->id]) }}" variant="outline">
                        <i class="fa-light fa-list-check"></i>
                        {{ __('Edit content') }}
                    </x-ui.button>
                @endif
                <x-ui.button type="button" wire:click="saveQrCode">
                    <i class="fa-light fa-floppy-disk"></i>
                    {{ __('Save QR') }}
                </x-ui.button>
            </div>
        </div>
    </section>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_25rem]">
        <x-ui.card class="space-y-6">
            <div>
                <h2 class="text-lg font-semibold" style="color: var(--theme-header-text-color);">{{ __('Configure QR') }}</h2>
                <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Set destination, publishing status, and scan-safe brand design.') }}</p>
            </div>

            @if ($errors->any())
                <div class="rounded-[1rem] border px-4 py-3 text-sm" style="border-color: rgba(239,68,68,0.28); background-color: rgba(239,68,68,0.08); color: #991b1b;">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-[0.7rem]" style="background-color: rgba(239,68,68,0.12); color: #dc2626;">
                            <i class="fa-light fa-triangle-exclamation"></i>
                        </span>
                        <div>
                            <p class="font-semibold">{{ __('Could not save this QR code.') }}</p>
                            <p class="mt-1 leading-5">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid gap-3 lg:grid-cols-[minmax(0,1.1fr)_11rem]">
                <x-ui.input wire:model.defer="form.name" :label="__('QR name')" />
                <x-ui.select wire:model.live="form.status" :label="__('Status')">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="draft">{{ __('Draft') }}</option>
                </x-ui.select>
                <div class="lg:col-span-2">
                    <x-ui.input wire:model.defer="form.destination_url" :label="__('Destination URL')" :placeholder="__('https://your-domain.com/landing-page')" />
                </div>
            </div>

            @unless ($isAiQrCode)
            <details class="group rounded-[1rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-image"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand image') }}</span>
                            <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">{{ __('Optional logo for QR center and Mosaic direction.') }}</span>
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold" style="color: var(--theme-accent);">
                        {{ filled(data_get($form, 'logo_url')) ? __('Selected') : __('Choose') }}
                        <i class="fa-light fa-chevron-down transition group-open:rotate-180"></i>
                    </span>
                </summary>
                <div class="border-t p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                    <x-ui.image-picker
                        wire:model.live="form.logo_url"
                        :value="data_get($form, 'logo_url', '')"
                        :preview="data_get($form, 'logo_url', '')"
                        :label="__('Brand image source')"
                        context="portal"
                        value-field="url"
                        layout="compact"
                        :button-label="__('Choose image')"
                        :dialog-title="__('Choose QR brand image')"
                        :dialog-description="__('Select a logo, icon, or artwork from Files. Mosaic mode can use it as the visual direction while preserving a scan-safe QR base.')"
                    />
                </div>
            </details>
            @endunless

            <details @if($isAiQrCode) open @endif class="group rounded-[1rem] border" style="border-color: rgba(var(--theme-accent-rgb),0.20); background: linear-gradient(135deg, rgba(var(--theme-accent-rgb),0.055), rgba(20,184,166,0.05));">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3">
                    <span class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.8rem]" style="background-color: rgba(var(--theme-accent-rgb),0.1); color: var(--theme-accent);">
                            <i class="fa-light fa-route"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Brand routing and tracking') }}</span>
                            <span class="block truncate text-xs" style="color: var(--theme-muted-text-color);">
                                {{ __('Brand Kit, domain, UTM, pixels, and dynamic rules.') }}
                            </span>
                        </span>
                    </span>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold" style="color: var(--theme-accent);">
                        {{ count((array) data_get($form, 'dynamic_rules', [])) }} {{ __('rules') }}
                        <i class="fa-light fa-chevron-down transition group-open:rotate-180"></i>
                    </span>
                </summary>

                <div class="space-y-4 border-t p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                    <div class="grid gap-3 lg:grid-cols-3">
                        @if ($canUseBrandKit ?? true)
                            <x-ui.select wire:model.live="form.brand_kit_id" :label="__('Brand Kit')">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($brandKits as $brandKit)
                                    <option value="{{ $brandKit->id }}">{{ $brandKit->brand_name }}</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                        @if ($canUseCustomDomains ?? true)
                            <x-ui.select wire:model.live="form.custom_domain_id" :label="__('Custom Domain')">
                                <option value="">{{ __('Default domain') }}</option>
                                @foreach ($customDomains as $domain)
                                    <option value="{{ $domain->id }}">{{ $domain->domain }} @if($domain->is_default)({{ __('default') }})@elseif($domain->status !== 'verified')({{ $domain->status }})@endif</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                        @if ($canUseUtmPresets ?? true)
                            <x-ui.select wire:model.live="form.utm_preset_id" :label="__('UTM Preset')">
                                <option value="">{{ __('Default UTM preset') }}</option>
                                @foreach ($utmPresets as $preset)
                                    <option value="{{ $preset->id }}">{{ $preset->name }} @if($preset->is_default)({{ __('default') }})@endif</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                    </div>

                    @if ($canUseTrackingPixels ?? true)
                        <div>
                            <p class="mb-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Tracking Pixels') }}</p>
                            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                                @forelse ($trackingPixels as $pixel)
                                    <div class="flex items-center gap-3 rounded-[0.85rem] border px-3 py-2.5 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                                        @php
                                            $pixelChecked = in_array((int) $pixel->id, array_map('intval', (array) data_get($form, 'tracking_pixel_ids', [])), true);
                                        @endphp
                                        <x-ui.checkbox
                                            wire:model.live="form.tracking_pixel_ids"
                                            value="{{ $pixel->id }}"
                                            :checked="$pixelChecked"
                                            minimal
                                        />
                                        <span class="min-w-0">
                                            <span class="block truncate font-semibold" style="color: var(--theme-header-text-color);">{{ $pixel->name }}</span>
                                            <span class="block text-xs" style="color: var(--theme-muted-text-color);">{{ $pixel->provider }} / {{ $pixel->status }}</span>
                                        </span>
                                    </div>
                                @empty
                                    <div class="rounded-[0.85rem] border border-dashed px-3 py-4 text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72); color: var(--theme-muted-text-color);">
                                        {{ __('No tracking pixels yet.') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <div class="rounded-[0.95rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-base);">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Dynamic redirect rules') }}</p>
                                <p class="mt-1 text-sm leading-6" style="color: var(--theme-muted-text-color);">{{ __('Send visitors to different URLs by country, device, active hours, or campaign dates.') }}</p>
                            </div>
                            <x-ui.button type="button" variant="outline" size="sm" wire:click="addDynamicRule">
                                <i class="fa-light fa-plus"></i>
                                {{ __('Add rule') }}
                            </x-ui.button>
                        </div>

                    <div class="mt-4 space-y-3">
                        @forelse ((array) $form['dynamic_rules'] as $ruleIndex => $rule)
                            <div class="rounded-[0.95rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: color-mix(in srgb, var(--theme-surface-overlay) 96%, transparent);">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <x-ui.checkbox
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.enabled"
                                        :label="__('Enabled')"
                                        minimal
                                        label-class="font-semibold"
                                    />
                                    <div class="flex flex-wrap gap-2">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="duplicateDynamicRule({{ $ruleIndex }})">
                                            <i class="fa-light fa-copy"></i>
                                            {{ __('Duplicate') }}
                                        </x-ui.button>
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="removeDynamicRule({{ $ruleIndex }})">
                                            <i class="fa-light fa-trash"></i>
                                            {{ __('Delete') }}
                                        </x-ui.button>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1.3fr)_8rem]">
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.name"
                                        :label="__('Rule name')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.name')"
                                        placeholder="Vietnam mobile"
                                    />
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.priority"
                                        type="number"
                                        :label="__('Priority')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.priority')"
                                    />
                                </div>

                                <div class="mt-3">
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.url"
                                        type="url"
                                        :label="__('Destination URL')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.url')"
                                        placeholder="https://example.com/vn"
                                    />
                                </div>

                                <div class="mt-3 grid gap-3 lg:grid-cols-2">
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.countries"
                                        :label="__('Countries')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.countries')"
                                        :help="__('Use country codes separated by commas, for example VN, US, SG.')"
                                    />

                                    <div>
                                        <p class="mb-2 text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Devices') }}</p>
                                        <div class="grid gap-2 sm:grid-cols-3">
                                            @foreach (['MOBILE' => __('Mobile'), 'DESKTOP' => __('Desktop'), 'TABLET' => __('Tablet')] as $deviceKey => $deviceLabel)
                                                <div class="rounded-[0.8rem] border px-3 py-2" style="border-color: rgba(var(--theme-border-color-rgb),0.54); background-color: var(--theme-surface-base);">
                                                    <x-ui.checkbox
                                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.devices"
                                                        value="{{ $deviceKey }}"
                                                        :label="$deviceLabel"
                                                        minimal
                                                    />
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('form.dynamic_rules.'.$ruleIndex.'.devices')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mt-3 grid gap-3 lg:grid-cols-4">
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.hours_from"
                                        type="number"
                                        min="0"
                                        max="23"
                                        :label="__('From hour')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.hours_from')"
                                        placeholder="8"
                                    />
                                    <x-ui.input
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.hours_to"
                                        type="number"
                                        min="0"
                                        max="23"
                                        :label="__('To hour')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.hours_to')"
                                        placeholder="17"
                                    />
                                    <x-ui.datetime-picker
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.start_at"
                                        :label="__('Starts')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.start_at')"
                                        :placeholder="__('Select start time')"
                                        picker-align="right"
                                        picker-position="auto"
                                    />
                                    <x-ui.datetime-picker
                                        wire:model.defer="form.dynamic_rules.{{ $ruleIndex }}.end_at"
                                        :label="__('Ends')"
                                        :error="$errors->first('form.dynamic_rules.'.$ruleIndex.'.end_at')"
                                        :placeholder="__('Select end time')"
                                        picker-align="right"
                                        picker-position="auto"
                                    />
                                </div>

                                <div class="mt-3 rounded-[0.8rem] border px-3 py-2 text-xs leading-5" style="border-color: rgba(var(--theme-accent-rgb),0.18); background-color: rgba(var(--theme-accent-rgb),0.06); color: var(--theme-muted-text-color);">
                                    {{ __('Preview') }}:
                                    {{ __('If visitor matches this rule, redirect to') }}
                                    <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ filled($rule['url'] ?? null) ? $rule['url'] : __('destination URL') }}</span>.
                                </div>
                            </div>
                        @empty
                            <div class="rounded-[0.9rem] border border-dashed px-4 py-6 text-center text-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.7); color: var(--theme-muted-text-color);">
                                <i class="fa-light fa-route mb-2 text-lg"></i>
                                <p class="font-semibold" style="color: var(--theme-header-text-color);">{{ __('No redirect rules yet') }}</p>
                                <p class="mt-1">{{ __('The QR will use the default destination URL until you add a rule.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </details>

            @unless ($isAiQrCode)
            <div>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Design mode') }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Choose Basic for reliable campaign QR designs, or Mosaic for image-driven brand QR.') }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 lg:grid-cols-3">
                    <label class="cursor-pointer rounded-[1rem] border p-4 transition" style="{{ $designMode === 'basic' ? 'border-color: var(--theme-accent); background: color-mix(in srgb, var(--theme-surface-soft) 84%, var(--theme-accent) 16%); box-shadow: 0 16px 34px rgba(var(--theme-accent-rgb),0.13);' : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);' }}">
                        <input type="radio" class="sr-only" wire:model.live="form.design_mode" value="basic">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.9rem]" style="{{ $designMode === 'basic' ? 'background-color: rgba(var(--theme-accent-rgb),0.14); color: var(--theme-accent);' : 'background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);' }}">
                            <i class="fa-light fa-qrcode"></i>
                        </span>
                        <span class="mt-3 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Basic') }}</span>
                        <span class="mt-1 block text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Sticker, coupon, ring, and card designs that keep QR contrast simple.') }}</span>
                    </label>
                    <label class="cursor-pointer rounded-[1rem] border p-4 transition" style="{{ $designMode === 'roundness' ? 'border-color: var(--theme-accent); background: color-mix(in srgb, var(--theme-surface-soft) 84%, var(--theme-accent) 16%); box-shadow: 0 16px 34px rgba(var(--theme-accent-rgb),0.13);' : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);' }}">
                        <input type="radio" class="sr-only" wire:model.live="form.design_mode" value="roundness">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.9rem]" style="{{ $designMode === 'roundness' ? 'background-color: rgba(var(--theme-accent-rgb),0.14); color: var(--theme-accent);' : 'background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);' }}">
                            <i class="fa-light fa-border-all"></i>
                        </span>
                        <span class="mt-3 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Roundness') }}</span>
                        <span class="mt-1 block text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Rounded QR modules with gradient colors and custom finder corners.') }}</span>
                    </label>
                    <label class="cursor-pointer rounded-[1rem] border p-4 transition" style="{{ $designMode === 'mosaic' ? 'border-color: var(--theme-accent); background: color-mix(in srgb, var(--theme-surface-soft) 84%, var(--theme-accent) 16%); box-shadow: 0 16px 34px rgba(var(--theme-accent-rgb),0.13);' : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);' }}">
                        <input type="radio" class="sr-only" wire:model.live="form.design_mode" value="mosaic">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.9rem]" style="{{ $designMode === 'mosaic' ? 'background-color: rgba(var(--theme-accent-rgb),0.14); color: var(--theme-accent);' : 'background-color: rgba(var(--theme-border-color-rgb),0.12); color: var(--theme-muted-text-color);' }}">
                            <i class="fa-light fa-grid-2-plus"></i>
                        </span>
                        <span class="mt-3 block font-semibold" style="color: var(--theme-header-text-color);">{{ __('Mosaic') }}</span>
                        <span class="mt-1 block text-sm leading-5" style="color: var(--theme-muted-text-color);">{{ __('Brand-image inspired dots with advanced scan-safe controls.') }}</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('QR style templates') }}</h3>
                        <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $designMode === 'mosaic' ? __('Mosaic templates use the brand image and dot settings below.') : ($designMode === 'roundness' ? __('Roundness templates use rounded modules, gradient fills, and custom finder corners.') : __('Scan-safe sticker, ring, coupon, and card presets. Choose one, then tune colors if needed.')) }}</p>
                    </div>
                    <x-ui.badge variant="info">{{ count($visibleTemplates) }} {{ __('templates') }}</x-ui.badge>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($visibleTemplates as $templateKey => $template)
                        @php
                            $templatePattern = $template['pattern'] ?? null;
                            $templatePreviewKind = 'default';

                            if ($templateKey === 'clean_card') {
                                $templatePreviewKind = 'clean';
                            } elseif ($templatePattern === 'gradient_rounded') {
                                $templatePreviewKind = 'roundness';
                            } elseif ($templatePattern === 'mosaic') {
                                $templatePreviewKind = 'mosaic';
                            }
                        @endphp
                        <label class="group cursor-pointer rounded-[1rem] border p-3 transition hover:-translate-y-[1px]" style="{{ $selectedTemplateKey === $templateKey ? 'border-color: var(--theme-accent); background: color-mix(in srgb, var(--theme-surface-soft) 86%, var(--theme-accent) 14%); box-shadow: 0 18px 36px rgba(var(--theme-accent-rgb),0.15);' : 'border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);' }}">
                            <input type="radio" class="sr-only" wire:model.live="form.style_template" value="{{ $templateKey }}">
                            <div class="flex h-28 items-center justify-center rounded-[0.85rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.38); background: color-mix(in srgb, {{ $template['surface'] }} 42%, var(--theme-surface-soft));">
                                @if ($templatePreviewKind === 'clean')
                                    <div class="grid h-16 w-16 grid-cols-5 gap-[2px] rounded-[0.45rem] p-1 shadow-sm" style="background-color: var(--theme-surface-base);">
                                        @foreach (range(0, 24) as $i)
                                            <span class="rounded-[1px]" style="background-color: {{ in_array($i, [0,1,5,6,18,19,23,24], true) ? $template['foreground'] : ($i % 2 === 0 ? $template['accent'] : '#eef2ff') }};"></span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($templatePreviewKind === 'roundness')
                                    <svg class="h-16 w-16 overflow-visible rounded-[0.7rem] shadow-sm" viewBox="0 0 72 72" role="img" aria-hidden="true">
                                        <defs>
                                            <linearGradient id="roundness-thumb-{{ $templateKey }}" x1="0" y1="0" x2="1" y2="1">
                                                <stop offset="0%" stop-color="{{ $template['foreground'] }}" />
                                                <stop offset="100%" stop-color="{{ $template['fallback'] }}" />
                                            </linearGradient>
                                        </defs>
                                        <rect x="6" y="6" width="60" height="60" rx="9" fill="#ffffff" />
                                        @php
                                            $roundnessDots = [];

                                            for ($row = 0; $row < 11; $row++) {
                                                for ($col = 0; $col < 11; $col++) {
                                                    $inFinder = ($row <= 3 && $col <= 3) || ($row <= 3 && $col >= 7) || ($row >= 7 && $col <= 3);
                                                    $visibleDot = (($row * 7 + $col * 5 + strlen($templateKey)) % 4) !== 1;

                                                    if (! $inFinder && $visibleDot) {
                                                        $roundnessDots[] = [
                                                            'x' => 10 + ($col * 5),
                                                            'y' => 10 + ($row * 5),
                                                            'opacity' => ($row + $col) % 3 === 0 ? '1' : '0.82',
                                                        ];
                                                    }
                                                }
                                            }
                                        @endphp
                                        @foreach ($roundnessDots as $dot)
                                            <rect x="{{ $dot['x'] }}" y="{{ $dot['y'] }}" width="3.8" height="3.8" rx="1.9" fill="url(#roundness-thumb-{{ $templateKey }})" opacity="{{ $dot['opacity'] }}" />
                                        @endforeach
                                        @foreach ([[8, 8], [46, 8], [8, 46]] as $finder)
                                            <rect x="{{ $finder[0] }}" y="{{ $finder[1] }}" width="18" height="18" rx="5" fill="{{ $template['accent'] }}" />
                                            <rect x="{{ $finder[0] + 5 }}" y="{{ $finder[1] + 5 }}" width="8" height="8" rx="2.5" fill="#264653" />
                                        @endforeach
                                    </svg>
                                @endif

                                @if ($templatePreviewKind === 'mosaic')
                                    <svg class="h-16 w-16 rounded-[0.8rem] shadow-sm" viewBox="0 0 72 72" role="img" aria-hidden="true">
                                        <rect x="5" y="5" width="62" height="62" rx="9" fill="#ffffff" />
                                        @php
                                            $mosaicDots = [];

                                            for ($row = 0; $row < 11; $row++) {
                                                for ($col = 0; $col < 11; $col++) {
                                                    $inFinder = ($row <= 3 && $col <= 3) || ($row <= 3 && $col >= 7) || ($row >= 7 && $col <= 3);
                                                    $visibleDot = (($row * 5 + $col * 3 + strlen($templateKey)) % 5) !== 2;
                                                    $dotColor = ($row + $col) % 4 === 0 ? $template['accent'] : (($row + $col) % 3 === 0 ? $template['foreground'] : '#94a3b8');

                                                    if (! $inFinder && $visibleDot) {
                                                        $mosaicDots[] = [
                                                            'x' => 10 + ($col * 5),
                                                            'y' => 10 + ($row * 5),
                                                            'fill' => $dotColor,
                                                            'opacity' => ($row + $col) % 3 === 0 ? '1' : '0.78',
                                                        ];
                                                    }
                                                }
                                            }

                                            $mosaicRadius = in_array($template['shape'], ['circle', 'rounded'], true) ? '1.8' : '0.8';
                                        @endphp
                                        @foreach ($mosaicDots as $dot)
                                            <rect x="{{ $dot['x'] }}" y="{{ $dot['y'] }}" width="3.6" height="3.6" rx="{{ $mosaicRadius }}" fill="{{ $dot['fill'] }}" opacity="{{ $dot['opacity'] }}" />
                                        @endforeach
                                        @foreach ([[8, 8], [46, 8], [8, 46]] as $finder)
                                            <rect x="{{ $finder[0] }}" y="{{ $finder[1] }}" width="18" height="18" rx="3.5" fill="{{ $template['accent'] }}" />
                                            <rect x="{{ $finder[0] + 4 }}" y="{{ $finder[1] + 4 }}" width="10" height="10" rx="2.5" fill="#ffffff" />
                                            <rect x="{{ $finder[0] + 7 }}" y="{{ $finder[1] + 7 }}" width="4.5" height="4.5" rx="1.4" fill="{{ $template['foreground'] }}" />
                                        @endforeach
                                    </svg>
                                @endif

                                @if ($templatePreviewKind === 'default')
                                    <div class="rounded-[0.85rem] p-2 shadow-sm" style="background-color: var(--theme-surface-base); border: 5px solid {{ $template['accent'] }};">
                                        <div class="grid h-12 w-12 grid-cols-5 gap-[2px] rounded" style="background-color: var(--theme-surface-base);">
                                            @foreach (range(0, 24) as $i)
                                                <span class="rounded-[1px]" style="background-color: {{ in_array($i, [0,1,5,6,18,19,23,24], true) ? $template['accent'] : ($i % 2 === 0 ? $template['foreground'] : '#eef2ff') }};"></span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <span class="mt-3 block text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $template['label'] }}</span>
                            <span class="mt-1 block text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ $template['description'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            @if (in_array($designMode, ['basic', 'roundness'], true))
                <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $designMode === 'roundness' ? __('Advanced roundness settings') : __('Advanced basic settings') }}</h3>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ $designMode === 'roundness' ? __('Tune gradient colors, finder corners, export quality, and center logo size.') : __('Tune QR colors, export quality, background, correction level, and center logo size.') }}</p>
                        </div>
                        <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-sliders"></i>
                            {{ __('Advanced') }}
                        </span>
                    </div>

                    <div class="mt-5">
                        <h4 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ $designMode === 'roundness' ? __('Roundness colors') : __('Basic colors') }}</h4>
                        <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ $designMode === 'roundness' ? __('Control the gradient start, gradient end, finder corner, and QR background.') : __('Control the QR modules, frame accent, and QR background.') }}</p>
                        <div class="mt-3 grid gap-3 lg:grid-cols-3">
                            <div wire:key="basic-color-foreground-{{ $selectedTemplateKey }}">
                                <x-ui.color-picker wire:model.live="form.foreground_color" :label="$designMode === 'roundness' ? __('Gradient start') : __('QR dots')" :value="data_get($form, 'foreground_color', '#0f172a')" />
                            </div>
                            <div wire:key="basic-color-fallback-{{ $selectedTemplateKey }}">
                                <x-ui.color-picker wire:model.live="form.fallback_dark" :label="$designMode === 'roundness' ? __('Gradient end') : __('Frame / accent')" :value="data_get($form, 'fallback_dark', '#0f172a')" />
                            </div>
                            <div wire:key="basic-color-background-{{ $selectedTemplateKey }}">
                                <x-ui.color-picker wire:model.live="form.background_color" :label="__('Background')" :value="data_get($form, 'background_color', '#ffffff')" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 lg:grid-cols-3">
                        <x-ui.select wire:model.live="form.ecc" :label="__('ECC')">
                            <option value="high">{{ __('High') }}</option>
                            <option value="quartile">{{ __('Quartile') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                        </x-ui.select>
                        <x-ui.select wire:model.live="form.output_px" :label="__('Output px')">
                            <option value="512">512</option>
                            <option value="768">768</option>
                            <option value="1024">1024</option>
                            <option value="1536">1536</option>
                            <option value="2048">2048</option>
                        </x-ui.select>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span style="color: var(--theme-muted-text-color);">{{ __('Center logo size') }}</span>
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ $logoSize }}%</span>
                        </div>
                        <input type="range" min="8" max="28" wire:model.live.debounce.300ms="form.logo_size" class="mt-2 h-2 w-full cursor-pointer accent-[var(--theme-accent)]">
                    </div>
                </div>
            @endif

            @if (data_get($form, 'pattern') === 'mosaic')
                <div class="rounded-[1.15rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background-color: var(--theme-surface-soft);">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold" style="color: var(--theme-header-text-color);">{{ __('Advanced mosaic settings') }}</h3>
                            <p class="mt-1 text-sm" style="color: var(--theme-muted-text-color);">{{ __('Control dot shape, correction level, ghost layer, and image color behavior for scan-safe artistic QR renders.') }}</p>
                        </div>
                        <span class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold" style="background-color: rgba(var(--theme-accent-rgb),0.08); color: var(--theme-accent);">
                            <i class="fa-light fa-sliders"></i>
                            {{ __('Advanced') }}
                        </span>
                    </div>

                <div class="mt-5 grid gap-3 lg:grid-cols-3">
                    <x-ui.select wire:model.live="form.qr_shape" :label="__('Shape')">
                        <option value="circle">{{ __('Circle') }}</option>
                        <option value="square">{{ __('Square') }}</option>
                        <option value="rounded">{{ __('Rounded') }}</option>
                        <option value="diamond">{{ __('Diamond') }}</option>
                    </x-ui.select>
                    <x-ui.select wire:model.live="form.ecc" :label="__('ECC')">
                        <option value="high">{{ __('High') }}</option>
                        <option value="quartile">{{ __('Quartile') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="low">{{ __('Low') }}</option>
                    </x-ui.select>
                    <x-ui.select wire:model.live="form.output_px" :label="__('Output px')">
                        <option value="512">512</option>
                        <option value="768">768</option>
                        <option value="1024">1024</option>
                        <option value="1536">1536</option>
                        <option value="2048">2048</option>
                    </x-ui.select>
                </div>

                <div class="mt-5 space-y-5">
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span style="color: var(--theme-muted-text-color);">{{ __('Dark dot range') }}</span>
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($form, 'dark_min', 47) }}% - {{ data_get($form, 'dark_max', 105) }}%</span>
                        </div>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <input type="range" min="0" max="100" wire:model.live.debounce.500ms="form.dark_min" class="h-2 w-full cursor-pointer accent-[var(--theme-accent)]">
                            <input type="range" min="1" max="140" wire:model.live.debounce.500ms="form.dark_max" class="h-2 w-full cursor-pointer accent-[var(--theme-accent)]">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span style="color: var(--theme-muted-text-color);">{{ __('Ghost strength') }}</span>
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($form, 'ghost_strength', 80) }}%</span>
                        </div>
                        <input type="range" min="0" max="100" wire:model.live.debounce.500ms="form.ghost_strength" class="mt-2 h-2 w-full cursor-pointer accent-[var(--theme-accent)]">
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span style="color: var(--theme-muted-text-color);">{{ __('Ghost threshold') }}</span>
                            <span class="font-semibold" style="color: var(--theme-header-text-color);">{{ data_get($form, 'ghost_threshold', 8) }}%</span>
                        </div>
                        <input type="range" min="0" max="100" wire:model.live.debounce.500ms="form.ghost_threshold" class="mt-2 h-2 w-full cursor-pointer accent-[var(--theme-accent)]">
                    </div>
                </div>

                <div class="mt-5">
                    <h4 class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Mosaic colors') }}</h4>
                    <p class="mt-1 text-xs" style="color: var(--theme-muted-text-color);">{{ __('Control the visible QR dots, brand accent dots, finder blocks, and background.') }}</p>
                    <div class="mt-3 grid gap-3 lg:grid-cols-3">
                        <div wire:key="mosaic-color-main-{{ $selectedTemplateKey }}">
                            <x-ui.color-picker wire:model.live="form.fallback_dark" :label="__('Main dots')" :value="data_get($form, 'fallback_dark', '#0f172a')" />
                        </div>
                        <div wire:key="mosaic-color-accent-{{ $selectedTemplateKey }}">
                            <x-ui.color-picker wire:model.live="form.foreground_color" :label="__('Accent dots')" :value="data_get($form, 'foreground_color', '#2563eb')" />
                        </div>
                        <div wire:key="mosaic-color-background-{{ $selectedTemplateKey }}">
                            <x-ui.color-picker wire:model.live="form.background_color" :label="__('Background')" :value="data_get($form, 'background_color', '#ffffff')" />
                        </div>
                    </div>
                </div>

                <label class="mt-5 flex cursor-pointer items-center gap-3 rounded-[0.95rem] border px-4 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58); background-color: var(--theme-surface-soft);">
                    <input type="checkbox" wire:model.live="form.use_image_colors" class="h-4 w-4 rounded border-slate-300 accent-[var(--theme-accent)]">
                    <span class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Use image colors') }}</span>
                    <span class="text-xs" style="color: var(--theme-muted-text-color);">{{ __('Sample brand image colors for mosaic exports.') }}</span>
                </label>
                </div>
            @endif
            @endunless

        </x-ui.card>

        <x-ui.card class="space-y-4 xl:sticky xl:top-24 xl:self-start">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em]" style="color: var(--theme-muted-text-color);">{{ $hasAiArtwork ? __('AI QR') : __('Live QR') }}</p>
                <h2 class="mt-1 text-base font-semibold" style="color: var(--theme-header-text-color);">{{ $hasAiArtwork ? __('AI QR artwork') : (data_get($form, 'pattern') === 'mosaic' ? __('Mosaic preview') : __('Preview')) }}</h2>
            </div>

            @if ($hasAiArtwork)
            <div
                x-data="aiArtworkDownloads({
                    filename: @js(\Illuminate\Support\Str::slug($qrCode->name ?: 'ai-qr-code')),
                    svg: @js($aiPreviewSvg),
                    imageUrl: @js($aiArtworkIsGeneratedImage ? $aiArtworkUrl : '')
                })"
                class="rounded-[1.1rem] border p-4 text-center"
                style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: color-mix(in srgb, var(--theme-surface-soft) 88%, transparent);"
            >
                <div x-ref="preview" class="mx-auto aspect-square w-full max-w-[19rem] overflow-hidden rounded-[1rem] [&>svg]:h-full [&>svg]:w-full">
                    @if ($aiArtworkIsGeneratedImage)
                        <img src="{{ $aiArtworkUrl }}" alt="{{ __('AI QR artwork') }}" class="h-full w-full object-cover" onerror="this.classList.add('hidden'); const fallback = this.nextElementSibling; if (fallback) { fallback.classList.remove('hidden'); fallback.style.display = 'grid'; }">
                        <div class="hidden h-full w-full place-items-center text-center" style="color: var(--theme-muted-text-color); background-color: var(--theme-surface-soft);">
                            <span>
                                <i class="fa-light fa-image-slash mb-2 text-xl"></i>
                                <span class="block text-xs font-semibold">{{ __('Artwork unavailable') }}</span>
                            </span>
                        </div>
                    @else
                    {!! $aiPreviewSvg !!}
                    @endif
                </div>
                <p class="mt-3 break-all text-xs" style="color: var(--theme-muted-text-color);">{{ $displayShortUrl ?: data_get($form, 'destination_url') }}</p>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="print()">
                        <i class="fa-light fa-print"></i>
                        {{ __('Print') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="downloadSvg()">
                        <i class="fa-light fa-code"></i>
                        {{ __('SVG') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="downloadPng()">
                        <i class="fa-light fa-download"></i>
                        {{ __('PNG') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Saved AI QR artwork') }}</p>
                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('This campaign keeps the generated AI QR artwork while the destination remains editable and trackable.') }}</p>
            </div>
            @else
            <div
                x-data="qrCodeDownloads({
                    filename: @js(\Illuminate\Support\Str::slug($qrCode->name ?: 'qr-code')),
                    template: @js($selectedTemplateKey),
                    accent: @js($templateAccent),
                    surface: @js($templateSurface),
                    foreground: @js($selectedTemplate['foreground'] ?? '#0f172a'),
                    background: @js($selectedTemplate['background'] ?? '#ffffff'),
                    logoUrl: @js($logoUrl),
                    logoSize: @js($logoSize)
                })"
                class="rounded-[1.1rem] border p-4 text-center"
                style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: color-mix(in srgb, {{ $templateSurface }} 46%, var(--theme-surface-soft));"
            >
                <div x-ref="preview">
                    @if (in_array($selectedTemplateKey, ['clean_card', 'default_mosaic'], true))
                    <div class="mx-auto flex w-full max-w-[16rem] items-center justify-center rounded-[0.9rem] p-2 [&_svg]:h-auto [&_svg]:max-h-[15rem] [&_svg]:w-full [&_svg]:max-w-full" style="background-color: var(--theme-surface-base);">
                        {!! $qrSvg !!}
                    </div>
                @elseif ($selectedTemplateKey === 'black_premium')
                    <div class="mx-auto w-full max-w-[18rem] rounded-[1rem] p-4 shadow-sm" style="background-color: {{ $selectedTemplate['foreground'] }};">
                        <div class="relative rounded-[0.85rem] p-5 [&_svg]:h-auto [&_svg]:max-h-[12.5rem] [&_svg]:w-full [&_svg]:max-w-full" style="background-color: var(--theme-surface-base);">
                            {!! $qrSvg !!}
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="" class="absolute left-1/2 top-1/2 {{ $previewLogoClass }} -translate-x-1/2 -translate-y-1/2 rounded-xl border-[6px] border-white object-cover shadow-sm">
                            @endif
                        </div>
                        <div class="mt-4 pb-1 text-center text-xs font-bold uppercase tracking-[0.22em]" style="color: {{ $templateAccent }};">{{ __('Premium scan') }}</div>
                    </div>
                @else
                    <div class="mx-auto flex aspect-square w-full max-w-[18rem] items-center justify-center rounded-[1.15rem] p-5 shadow-sm" style="background-color: {{ $templateSurface }};">
                        <div class="relative flex aspect-square w-full items-center justify-center rounded-[1rem] p-4 shadow-sm [&_svg]:h-auto [&_svg]:max-h-[13.5rem] [&_svg]:w-full [&_svg]:max-w-full" style="background-color: var(--theme-surface-base); border: 5px solid {{ $templateAccent }};">
                            {!! $qrSvg !!}
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="" class="absolute left-1/2 top-1/2 {{ $previewLogoClass }} -translate-x-1/2 -translate-y-1/2 rounded-xl border-[6px] border-white object-cover shadow-sm">
                            @endif
                        </div>
                    </div>
                @endif
                </div>
                <p class="mt-3 break-all text-xs" style="color: var(--theme-muted-text-color);">{{ $displayShortUrl ?: data_get($form, 'destination_url') }}</p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="downloadPng()">
                        <i class="fa-light fa-download"></i>
                        {{ __('Download PNG') }}
                    </x-ui.button>
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="downloadSvg()">
                        <i class="fa-light fa-code"></i>
                        {{ __('Download SVG') }}
                    </x-ui.button>
                </div>
            </div>
            <div class="rounded-[1rem] border p-4" style="border-color: rgba(var(--theme-border-color-rgb),0.62); background-color: var(--theme-surface-soft);">
                <p class="text-sm font-semibold" style="color: var(--theme-header-text-color);">{{ __('Need a poster?') }}</p>
                <p class="mt-1 text-xs leading-5" style="color: var(--theme-muted-text-color);">{{ __('AI-looking QR images often break scanner contrast. This builder keeps the QR payload intact first, then applies brand styling around a reliable base.') }}</p>
            </div>
            @endif
            <x-ui.button type="button" wire:click="saveQrCode" block>
                <i class="fa-light fa-floppy-disk"></i>
                {{ __('Save QR') }}
            </x-ui.button>
        </x-ui.card>
    </div>
</div>

@once
    <script>
        window.aiArtworkDownloads = function (payload) {
            return {
                ...payload,
                currentSvg() {
                    const rendered = this.$refs.preview?.querySelector('svg');

                    if (rendered) {
                        return rendered.outerHTML;
                    }

                    if (this.imageUrl) {
                        return this.imageSvg(this.imageUrl);
                    }

                    return this.svg || '';
                },
                async downloadSvg() {
                    const source = await this.inlineSvgImages(this.currentSvg());

                    if (!source) {
                        return;
                    }

                    this.downloadBlob(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }), `${this.filename}.svg`);
                },
                async downloadPng() {
                    if (this.imageUrl) {
                        const blob = await this.fetchImageBlob(this.imageUrl);

                        if (blob) {
                            this.downloadBlob(blob, `${this.filename}.png`);
                        }

                        return;
                    }

                    const blob = await this.svgToPngBlob(this.currentSvg());

                    if (blob) {
                        this.downloadBlob(blob, `${this.filename}.png`);
                    }
                },
                async print() {
                    if (this.imageUrl) {
                        this.printImageUrl(this.imageUrl);

                        return;
                    }

                    const blob = await this.svgToPngBlob(this.currentSvg());

                    if (!blob) {
                        return;
                    }

                    const imageUrl = URL.createObjectURL(blob);
                    const win = window.open('', '_blank');

                    if (!win) {
                        URL.revokeObjectURL(imageUrl);
                        return;
                    }

                    win.document.write(`
                        <html>
                            <head>
                                <title>AI QR Code</title>
                                <style>
                                    @page { size: auto; margin: 12mm; }
                                    html, body { margin: 0; min-height: 100%; background: #fff; }
                                    body { display: grid; place-items: center; }
                                    img { display: block; width: min(92vw, 180mm); height: auto; }
                                </style>
                            </head>
                            <body>
                                <img src='${imageUrl}' alt='AI QR Code'>
                            </body>
                        </html>
                    `);
                    win.document.close();

                    const printable = win.document.querySelector('img');
                    printable.onload = () => {
                        win.focus();
                        setTimeout(() => {
                            win.print();
                            URL.revokeObjectURL(imageUrl);
                        }, 150);
                    };
                },
                imageSvg(url) {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="1024" height="1024" viewBox="0 0 1024 1024" role="img" aria-label="AI QR code"><rect width="1024" height="1024" rx="48" fill="#ffffff"/><image href="${this.escapeXml(url)}" width="1024" height="1024" preserveAspectRatio="xMidYMid slice"/></svg>`;
                },
                async fetchImageBlob(url) {
                    try {
                        const response = await fetch(url, {
                            cache: 'no-store',
                            credentials: 'same-origin',
                        });

                        return response.ok ? await response.blob() : null;
                    } catch (error) {
                        return null;
                    }
                },
                printImageUrl(url) {
                    const win = window.open('', '_blank');

                    if (!win) {
                        return;
                    }

                    win.document.write(`
                        <html>
                            <head>
                                <title>AI QR Code</title>
                                <style>
                                    @page { size: auto; margin: 12mm; }
                                    html, body { margin: 0; min-height: 100%; background: #fff; }
                                    body { display: grid; place-items: center; }
                                    img { display: block; width: min(92vw, 180mm); height: auto; }
                                </style>
                            </head>
                            <body>
                                <img src='${this.escapeAttribute(url)}' alt='AI QR Code'>
                            </body>
                        </html>
                    `);
                    win.document.close();

                    const printable = win.document.querySelector('img');
                    printable.onload = () => {
                        win.focus();
                        setTimeout(() => win.print(), 150);
                    };
                },
                async svgToPngBlob(svg) {
                    const source = await this.inlineSvgImages(svg);

                    if (!source) {
                        return null;
                    }

                    return new Promise((resolve) => {
                        const img = new Image();
                        const url = URL.createObjectURL(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }));

                        img.onload = () => {
                            const canvas = document.createElement('canvas');
                            const context = canvas.getContext('2d');

                            canvas.width = 1024;
                            canvas.height = 1024;
                            context.fillStyle = '#ffffff';
                            context.fillRect(0, 0, 1024, 1024);
                            context.drawImage(img, 0, 0, 1024, 1024);
                            URL.revokeObjectURL(url);
                            canvas.toBlob((blob) => resolve(blob), 'image/png');
                        };

                        img.onerror = () => {
                            URL.revokeObjectURL(url);
                            resolve(null);
                        };

                        img.src = url;
                    });
                },
                async inlineSvgImages(svg) {
                    if (!svg) {
                        return '';
                    }

                    const doc = new DOMParser().parseFromString(svg, 'image/svg+xml');
                    const images = Array.from(doc.querySelectorAll('image'));

                    await Promise.all(images.map(async (image) => {
                        const href = image.getAttribute('href') || image.getAttribute('xlink:href');

                        if (!href || href.startsWith('data:')) {
                            return;
                        }

                        try {
                            const response = await fetch(href, {
                                cache: 'no-store',
                                credentials: 'same-origin',
                            });

                            if (!response.ok) {
                                return;
                            }

                            const blob = await response.blob();
                            const dataUrl = await new Promise((resolve) => {
                                const reader = new FileReader();
                                reader.onloadend = () => resolve(reader.result || '');
                                reader.onerror = () => resolve('');
                                reader.readAsDataURL(blob);
                            });

                            if (dataUrl) {
                                image.setAttribute('href', dataUrl);
                            }
                        } catch (error) {
                            return;
                        }
                    }));

                    return new XMLSerializer().serializeToString(doc.documentElement);
                },
                downloadBlob(blob, name) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    link.href = url;
                    link.download = name;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 300);
                },
                escapeXml(value) {
                    return String(value).replace(/[&<>"']/g, (char) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        '\'': '&apos;',
                    }[char]));
                },
                escapeAttribute(value) {
                    return String(value).replace(/[&<>"']/g, (char) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        '\'': '&#039;',
                    }[char]));
                },
            };
        };

        window.qrCodeDownloads = function (payload) {
            return {
                ...payload,
                logoDataUrl: null,
                async downloadSvg() {
                    const source = await this.exportSvg();

                    if (!source) {
                        return;
                    }

                    this.downloadBlob(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }), `${this.filename}.svg`);
                },
                async downloadPng() {
                    const source = await this.exportSvg();

                    if (!source) {
                        return;
                    }

                    const url = URL.createObjectURL(new Blob([source], { type: 'image/svg+xml;charset=utf-8' }));
                    const image = new Image();

                    image.onload = () => {
                        const canvas = document.createElement('canvas');
                        const size = Math.max(image.naturalWidth || image.width || 1400, image.naturalHeight || image.height || 1400);
                        const context = canvas.getContext('2d');

                        canvas.width = size;
                        canvas.height = size;
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, size, size);
                        context.drawImage(image, 0, 0, size, size);
                        URL.revokeObjectURL(url);
                        canvas.toBlob((blob) => {
                            if (blob) {
                                this.downloadBlob(blob, `${this.filename}.png`);
                            }
                        }, 'image/png');
                    };

                    image.src = url;
                },
                async exportSvg() {
                    const svg = this.$refs.preview?.querySelector('svg');

                    if (!svg) {
                        return '';
                    }

                    const clone = svg.cloneNode(true);
                    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

                    const qrSource = new XMLSerializer().serializeToString(clone);
                    const qrHref = `data:image/svg+xml;base64,${this.toBase64(qrSource)}`;
                    const surface = this.surface || '#ffffff';
                    const accent = this.accent || '#2563eb';
                    const foreground = this.foreground || '#0f172a';
                    const background = this.background || '#ffffff';
                    const logoHref = await this.embeddedLogoUrl();
                    const exportLogoSize = Math.round(620 * Math.max(8, Math.min(28, Number(this.logoSize || 18))) / 100);
                    const logo = (cx, cy, size = exportLogoSize) => logoHref
                        ? `<rect x="${cx - ((size + 36) / 2)}" y="${cy - ((size + 36) / 2)}" width="${size + 36}" height="${size + 36}" rx="32" fill="#ffffff"/><image href="${this.escapeXml(logoHref)}" x="${cx - (size / 2)}" y="${cy - (size / 2)}" width="${size}" height="${size}" preserveAspectRatio="xMidYMid slice"/>`
                        : '';

                    if (['clean_card', 'default_mosaic'].includes(this.template)) {
                        return qrSource;
                    }

                    if (this.template === 'black_premium') {
                        return `<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="1000" viewBox="0 0 1000 1000">
                            <rect width="1000" height="1000" rx="56" fill="${foreground}"/>
                            <rect x="135" y="115" width="730" height="730" rx="42" fill="${background}"/>
                            <image href="${qrHref}" x="225" y="205" width="550" height="550"/>
                            ${logo(500, 480, Math.round(exportLogoSize * 0.86))}
                            <text x="500" y="920" text-anchor="middle" fill="${accent}" font-size="34" font-family="Inter, Arial, sans-serif" font-weight="800" letter-spacing="12">PREMIUM SCAN</text>
                        </svg>`;
                    }

                    return `<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="1400" viewBox="0 0 1400 1400">
                        <rect width="1400" height="1400" rx="110" fill="${surface}"/>
                        <rect x="235" y="235" width="930" height="930" rx="70" fill="#ffffff"/>
                        <rect x="270" y="270" width="860" height="860" rx="52" fill="${background}" stroke="${accent}" stroke-width="34"/>
                        <image href="${qrHref}" x="380" y="380" width="640" height="640"/>
                        ${logo(700, 700)}
                    </svg>`;
                },
                toBase64(value) {
                    return btoa(unescape(encodeURIComponent(value)));
                },
                async embeddedLogoUrl() {
                    if (!this.logoUrl) {
                        return '';
                    }

                    if (String(this.logoUrl).startsWith('data:')) {
                        return this.logoUrl;
                    }

                    if (this.logoDataUrl) {
                        return this.logoDataUrl;
                    }

                    try {
                        const absoluteUrl = new URL(this.logoUrl, window.location.origin).href;
                        const response = await fetch(absoluteUrl, { credentials: 'same-origin' });

                        if (!response.ok) {
                            return '';
                        }

                        const blob = await response.blob();
                        this.logoDataUrl = await new Promise((resolve) => {
                            const reader = new FileReader();
                            reader.onloadend = () => resolve(reader.result || '');
                            reader.onerror = () => resolve('');
                            reader.readAsDataURL(blob);
                        });

                        return this.logoDataUrl || '';
                    } catch (error) {
                        return '';
                    }
                },
                escapeXml(value) {
                    return String(value).replace(/[&<>"']/g, (char) => ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        '\'': '&apos;',
                    }[char]));
                },
                downloadBlob(blob, name) {
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');

                    link.href = url;
                    link.download = name;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(url), 300);
                },
            };
        };
    </script>
@endonce
