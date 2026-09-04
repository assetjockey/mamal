<div>
    <div class="flex justify-center">
        <div class="w-full xl:w-10/12">

            {{-- Breadcrumbs --}}
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{ __('AI Settings') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

             <div class="mb-10">
                <h1 class="font-bold md:text-2xl">{{ __('AI Settings') }}</h1>
                <flux:subheading size="sm" class="mb-6 md:text-sm">{{ __('Connect AI vendors, manage their models across Image, Video and Copy studios, and control pricing') }}</flux:subheading>
            </div>

            {{-- ============================================================
                 Studio feature toggles
                 ============================================================ --}}
            @php
                $studioToggles = [
                    ['key' => 'image_studio_feature', 'free' => 'image_studio_free_tier', 'label' => __('Image Studio'), 'icon' => 'photo',         'desc' => __('AI image generation'), 'tint' => 'indigo'],
                    ['key' => 'video_studio_feature', 'free' => 'video_studio_free_tier', 'label' => __('Video Studio'), 'icon' => 'film',          'desc' => __('AI video generation'), 'tint' => 'violet'],
                    ['key' => 'copy_studio_feature',  'free' => 'copy_studio_free_tier',  'label' => __('Copy Studio'),  'icon' => 'pencil-square', 'desc' => __('AI ad-copy writing'),  'tint' => 'emerald'],
                ];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-10">
                @foreach($studioToggles as $t)
                    <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-9 h-9 rounded-xl bg-{{ $t['tint'] }}-50 dark:bg-{{ $t['tint'] }}-500/15 flex items-center justify-center shrink-0">
                                    <flux:icon :name="$t['icon']" class="size-4 text-{{ $t['tint'] }}-600 dark:text-{{ $t['tint'] }}-400" />
                                </span>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $t['label'] }}</div>
                                    <div class="text-[11px] text-zinc-500 truncate">{{ $t['desc'] }}</div>
                                </div>
                            </div>
                            <flux:switch wire:model.live="{{ $t['key'] }}" />
                        </div>

                        {{-- Free-tier access: only meaningful while the studio itself is
                             enabled. Off = non-subscribers can't use it (they're sent to
                             billing). Subscribers always defer to their plan column. --}}
                        <div @class([
                                'mt-3 pt-3 border-t border-zinc-100 dark:border-white/5 flex items-center justify-between gap-3',
                                'opacity-50 pointer-events-none' => ! $this->{$t['key']},
                            ])>
                            <div class="min-w-0">
                                <div class="text-[12px] font-semibold text-zinc-700 dark:text-zinc-300 truncate">{{ __('Free tier access') }}</div>
                                <div class="text-[11px] text-zinc-500 truncate">{{ __('Allow non-subscribers to use it') }}</div>
                            </div>
                            <flux:switch wire:model.live="{{ $t['free'] }}" :disabled="! $this->{$t['key']}" />
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ============================================================
                 Vendors
                 ============================================================ --}}
            <div class="mb-10">
                <div class="mb-4">
                    <div class="flex items-center gap-2">
                        <flux:icon.building-storefront class="size-4 text-zinc-400" />
                        <h2 class="text-sm font-bold text-zinc-800">{{ __('Vendors') }}</h2>
                    </div>
                    <p class="text-[11px] text-zinc-400 mt-1">{{ __('Click a vendor to configure its key & models') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @php
                        $capMeta = [
                            'image' => ['label' => __('Image Studio'),    'icon' => 'photo',         'tint' => 'indigo'],
                            'video' => ['label' => __('Video Studio'),    'icon' => 'film',          'tint' => 'violet'],
                            'copy'  => ['label' => __('Copy Studio'), 'icon' => 'pencil-square', 'tint' => 'emerald'],
                        ];
                    @endphp

                    @foreach($vendors as $vendor)
                        <button type="button" wire:click="configureVendor('{{ $vendor['key'] }}')"
                                class="group text-left rounded-2xl border border-zinc-200 dark:border-white/8 bg-white p-5 transition-all hover:shadow-md
                                       dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                            <div class="flex items-start gap-3.5">
                                {{-- Vendor logo --}}
                                <span class="relative w-12 h-12 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                    @include('livewire.admin.ai.partials._vendor-icon', ['brand' => $vendor['brand'], 'class' => 'size-6 text-white'])
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $vendor['name'] }}</h3>
                                        @if($vendor['has_key'])
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                <flux:icon.check-badge class="size-3.5" /> {{ __('Connected') }}
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold text-zinc-400">{{ __('Not connected') }}</span>
                                        @endif
                                    </div>

                                    {{-- Capability chips --}}
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @foreach($vendor['capabilities'] as $cap)
                                            @php $cm = $capMeta[$cap]; @endphp
                                            <span class="inline-flex items-center gap-1 rounded-md bg-{{ $cm['tint'] }}-50 dark:bg-{{ $cm['tint'] }}-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-{{ $cm['tint'] }}-700 dark:text-{{ $cm['tint'] }}-300">
                                                <flux:icon :name="$cm['icon']" class="size-3" />
                                                {{ $cm['label'] }}
                                                <span class="opacity-60">{{ $vendor['counts'][$cap] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Connection status dot (replaces the chevron) --}}
                                <span class="mt-1 w-3.5 h-3.5 rounded-full shrink-0 {{ $vendor['has_key'] ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-neutral-600' }}"></span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ============================================================
                 Multi-vendor video engines (e.g. Seedance)
                 One model, several interchangeable API vendors.
                 ============================================================ --}}
            @if(! empty($multiVendorModels))
                <div class="mb-10">
                    <div class="mb-4">
                        <div class="flex items-center gap-2">
                            <flux:icon.film class="size-4 text-zinc-400" />
                            <h2 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Multi-vendor Video Engines') }}</h2>
                        </div>
                        <p class="text-[11px] text-zinc-400 mt-1">{{ __('These models can be powered by one of several API vendors. Pick the active vendor, set its key, and price each resolution.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($multiVendorModels as $mvm)
                            @php
                                $activeProvider = collect($mvm['providers'])->firstWhere('key', $mvm['active_provider']);
                                $enabledTierCount = collect($mvm['resolutions'])->filter(fn($r) => $r['enabled'])->count();
                            @endphp
                            <button type="button" wire:click="configureModel('{{ $mvm['vendor'] }}')"
                                    class="group text-left rounded-2xl border border-zinc-200 dark:border-white/8 bg-white p-5 transition-all hover:shadow-md
                                           dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                <div class="flex items-start gap-3.5">
                                    {{-- Model icon (flat chip, no gradient) --}}
                                    <span class="relative w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                        @if(! empty($mvm['icon_svg']))
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $mvm['icon_svg'] !!}</svg>
                                        @else
                                            <flux:icon.film class="size-6" />
                                        @endif
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ $mvm['label'] }}</h3>
                                            @if($mvm['enabled'])
                                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">{{ __('Enabled') }}</span>
                                            @else
                                                <span class="text-[10px] font-bold text-zinc-400">{{ __('Disabled') }}</span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            {{-- Active vendor chip --}}
                                            <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 dark:bg-indigo-500/15 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 dark:text-indigo-300">
                                                {{ __('Vendor') }}: {{ $activeProvider['label'] ?? __('None') }}
                                            </span>
                                            {{-- Key status --}}
                                            @if($mvm['has_active_key'])
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                    <flux:icon.check-badge class="size-3.5" /> {{ __('Connected') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400">
                                                    <flux:icon.exclamation-triangle class="size-3.5" /> {{ __('No key') }}
                                                </span>
                                            @endif
                                            {{-- Resolution count --}}
                                            <span class="inline-flex items-center gap-1 rounded-md bg-zinc-100 dark:bg-white/10 px-1.5 py-0.5 text-[10px] font-semibold text-zinc-600 dark:text-zinc-300">
                                                {{ $enabledTierCount }} {{ trans_choice('resolution|resolutions', $enabledTierCount) }}
                                            </span>
                                        </div>
                                    </div>

                                    <span class="mt-1 w-3.5 h-3.5 rounded-full shrink-0 {{ $mvm['has_active_key'] && $mvm['enabled'] ? 'bg-emerald-500' : 'bg-zinc-300 dark:bg-neutral-600' }}"></span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ============================================================
                 Defaults
                 ============================================================ --}}
            <div class="mb-8">
                {{-- Default models --}}
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex items-center gap-2 mb-5">
                        <flux:icon.star class="size-4 text-amber-500" />
                        <h2 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Default Models') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:field>
                            <flux:label>{{ __('Default Image Model') }}</flux:label>
                            <flux:select wire:model="defaultImageModel">
                                @foreach($defaultOptions['image'] as $opt)
                                    <flux:select.option value="{{ $opt['value'] }}">{{ $opt['label'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Default Video Model') }}</flux:label>
                            <flux:select wire:model="defaultVideoModel">
                                @foreach($defaultOptions['video'] as $opt)
                                    <flux:select.option value="{{ $opt['value'] }}">{{ $opt['label'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('Default Copy Engine') }}</flux:label>
                            <flux:select wire:model="defaultCopyEngine">
                                @foreach($defaultOptions['copy'] as $opt)
                                    <flux:select.option value="{{ $opt['value'] }}">{{ $opt['label'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>
                    <p class="mt-4 text-[11px] text-zinc-500 leading-relaxed flex items-start gap-1.5">
                        <flux:icon.information-circle class="size-3.5 mt-px shrink-0" />
                        {{ __('Credit pricing is set per model on each vendor. Image models charge per image, video models per second of video, and copy models per 1,000 words generated.') }}
                    </p>
                </div>
            </div>

            {{-- Save global settings --}}
            <div class="flex justify-center mb-10">
                <flux:button wire:click="save" variant="primary"
                             class="md:w-1/2 w-full py-6 rounded-xl cursor-pointer">
                    {{ __('Save Settings') }}
                </flux:button>
            </div>

        </div>
    </div>

    {{-- ============================================================
         Vendor configuration modal
         ============================================================ --}}
    <flux:modal wire:model="showVendorModal" name="vendor-config" class="max-w-3xl w-full">
        @php $editing = collect($vendors)->firstWhere('key', $editingVendor); @endphp

        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-start gap-3.5">
                <span class="relative w-12 h-12 rounded-xl bg-zinc-900 border border-zinc-800 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                    @include('livewire.admin.ai.partials._vendor-icon', ['brand' => $editing['brand'] ?? '', 'class' => 'size-6 text-white'])
                </span>
                <div class="min-w-0">
                    <flux:heading size="lg">{{ $editing['name'] ?? __('Vendor') }}</flux:heading>
                    <flux:subheading>{{ __('Configure the API key and models for this vendor.') }}</flux:subheading>
                </div>
            </div>

            {{-- API key --}}
            <div class="rounded-xl border border-zinc-200 dark:border-white/8 p-4">
                <flux:field>
                    <flux:label>{{ __('API Key') }}</flux:label>
                    <flux:input type="password" wire:model.blur="apiKey"
                                placeholder="{{ $hasExistingKey ? __('•••••••••••• (saved — type to replace)') : __('Enter API key...') }}" />
                    <flux:description>
                        @if($hasExistingKey)
                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                <flux:icon.check-badge class="size-3.5" /> {{ __('A key is currently stored. Leave blank to keep it.') }}
                            </span>
                        @else
                            {{ __('No key stored yet. Models stay hidden from users until a key is added.') }}
                        @endif
                    </flux:description>
                </flux:field>
                @if($hasExistingKey)
                    <div class="mt-2">
                        <flux:button wire:click="clearApiKey" variant="ghost" size="sm" icon="trash"
                                     class="text-rose-600 dark:text-rose-400">
                            {{ __('Remove key') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            {{-- Models grouped by capability --}}
            @php
                $groupMeta = [
                    'image' => ['label' => __('Image Studio'), 'icon' => 'photo',         'tint' => 'indigo'],
                    'video' => ['label' => __('Video Studio'), 'icon' => 'film',          'tint' => 'violet'],
                    'copy'  => ['label' => __('Copy Studio'),  'icon' => 'pencil-square', 'tint' => 'emerald'],
                ];
                $unitHint = [
                    'image' => __('Credits are charged per image generated.'),
                    'video' => __('Credits are charged per second of video generated.'),
                    'copy'  => __('Credits are charged per 1,000 words generated.'),
                ];
                $unitShort = [
                    'image' => __('/ image'),
                    'video' => __('/ sec'),
                    'copy'  => __('/ 1k words'),
                ];
                $rowsByType = collect($modelRows)->groupBy('type');
                $tierTone = [
                    'premium'  => 'text-amber-600 dark:text-amber-400',
                    'standard' => 'text-emerald-600 dark:text-emerald-400',
                    'mid'      => 'text-emerald-600 dark:text-emerald-400',
                    'fast'     => 'text-sky-600 dark:text-sky-400',
                    'budget'   => 'text-sky-600 dark:text-sky-400',
                ];
            @endphp

            <div class="space-y-5 max-h-[50vh] overflow-y-auto pr-1">
                @foreach(['image','video','copy'] as $type)
                    @php $rows = $rowsByType->get($type); @endphp
                    @if($rows)
                        @php
                            $gm = $groupMeta[$type];
                            $allOn = $rows->every(fn($r) => $r['enabled']);
                        @endphp
                        <div>
                            <div class="flex items-center gap-1.5 mb-2">
                                <flux:icon :name="$gm['icon']" class="size-3.5 text-{{ $gm['tint'] }}-500" />
                                <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-500">{{ $gm['label'] }}</span>
                                <span class="text-[10px] text-zinc-400">{{ $rows->count() }} {{ trans_choice('model|models', $rows->count()) }}</span>
                                {{-- Per-capability master toggle: enable/disable this whole studio for the vendor --}}
                                <label class="ml-auto inline-flex items-center gap-1.5 cursor-pointer">
                                    <span class="text-[10px] font-semibold text-zinc-500">{{ __('All') }}</span>
                                    <flux:switch wire:click="toggleCapability('{{ $type }}', {{ $allOn ? 'false' : 'true' }})" :checked="$allOn" />
                                </label>
                            </div>

                            {{-- Unit pricing explanation for this studio --}}
                            <div class="mb-2 flex items-start gap-1.5 rounded-lg bg-{{ $gm['tint'] }}-50/60 dark:bg-{{ $gm['tint'] }}-500/10 px-2.5 py-1.5 text-[10px] text-{{ $gm['tint'] }}-700 dark:text-{{ $gm['tint'] }}-300 leading-relaxed">
                                <flux:icon.information-circle class="size-3.5 mt-px shrink-0" />
                                <span>{{ $unitHint[$type] }}</span>
                            </div>

                            <div class="space-y-2">
                                @foreach($rows as $i => $row)
                                    {{-- Resolve the absolute index in $modelRows for wire:model binding --}}
                                    @php $idx = collect($modelRows)->search(fn($r) => $r['id'] === $row['id'] && $r['table'] === $row['table']); @endphp
                                    <div class="rounded-xl border p-3 transition-colors
                                                {{ $modelRows[$idx]['enabled']
                                                    ? 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-bg-color)'
                                                    : 'border-dashed border-zinc-200 bg-zinc-50/50 dark:border-white/8 dark:bg-neutral-900/40 opacity-70' }}">
                                        <div class="flex items-center gap-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100 truncate">{{ $row['label'] }}</span>
                                                    @if($row['tier'])
                                                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $tierTone[$row['tier']] ?? 'text-zinc-400' }}">{{ $row['tier'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-[10px] font-mono text-zinc-400 truncate">{{ $row['model_id'] }}</div>
                                            </div>

                                            {{-- Image quality (only for engines whose driver supports it, e.g. OpenAI GPT Image 2) --}}
                                            @if(!empty($modelRows[$idx]['supports_quality']))
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <flux:icon.adjustments-horizontal class="size-3.5 text-zinc-400" />
                                                    <select wire:model="modelRows.{{ $idx }}.image_quality"
                                                            class="rounded-lg border border-zinc-200 dark:border-white/8 bg-white dark:bg-neutral-950 px-2 py-1 text-xs text-zinc-800 dark:text-zinc-200 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 focus:outline-none capitalize">
                                                        @foreach($modelRows[$idx]['quality_options'] as $opt)
                                                            <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif

                                            {{-- Credit cost (per the studio's pricing unit) --}}
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <flux:icon.bolt class="size-3.5 text-amber-500" />
                                                <input type="number" min="1" wire:model="modelRows.{{ $idx }}.credit_cost"
                                                       class="w-16 rounded-lg border border-zinc-200 dark:border-white/8 bg-white dark:bg-neutral-950 px-2 py-1 text-xs text-center tabular-nums text-zinc-800 dark:text-zinc-200 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 focus:outline-none" />
                                                <span class="text-[10px] font-medium text-zinc-400 whitespace-nowrap w-16">{{ $unitShort[$type] }}</span>
                                            </div>

                                            {{-- Enable toggle --}}
                                            <flux:switch wire:model.live="modelRows.{{ $idx }}.enabled" />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-white/6">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveVendor" variant="primary">
                    {{ __('Save Vendor') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================================
         Multi-vendor engine configuration modal (e.g. Seedance)
         ============================================================ --}}
    <flux:modal wire:model="showModelModal" name="model-config" class="max-w-3xl w-full">
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex items-start gap-3.5">
                <span class="relative w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <flux:icon.film class="size-6" />
                </span>
                <div class="min-w-0 flex-1">
                    <flux:heading size="lg">{{ $modelLabel ?: __('Video Engine') }}</flux:heading>
                    <flux:subheading>{{ __('Choose the API vendor, add its key, and price each resolution.') }}</flux:subheading>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="text-[11px] font-semibold text-zinc-500">{{ __('Enabled') }}</span>
                    <flux:switch wire:model.live="modelEnabled" />
                </label>
            </div>

            {{-- Active vendor selector + per-vendor API keys --}}
            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <flux:icon.server-stack class="size-3.5 text-indigo-500" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Powered by') }}</span>
                </div>
                <p class="mb-3 flex items-start gap-1.5 rounded-lg bg-indigo-50/60 dark:bg-indigo-500/10 px-2.5 py-1.5 text-[10px] text-indigo-700 dark:text-indigo-300 leading-relaxed">
                    <flux:icon.information-circle class="size-3.5 mt-px shrink-0" />
                    <span>{{ __('Only the selected vendor is used. It must have an API key saved before it can power the model.') }}</span>
                </p>

                <div class="space-y-2">
                    @foreach($providerRows as $key => $row)
                        <div class="rounded-xl border p-3 transition-colors
                                    {{ $modelActiveProvider === $key
                                        ? 'border-indigo-400 bg-indigo-50/50 dark:border-indigo-700/50 dark:bg-indigo-950/20'
                                        : 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-bg-color)' }}">
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-2 cursor-pointer shrink-0">
                                    <input type="radio" wire:model.live="modelActiveProvider" value="{{ $key }}"
                                           class="text-indigo-600 focus:ring-indigo-400/30" />
                                    <span class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100">{{ $row['label'] }}</span>
                                </label>

                                @if($row['has_key'])
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                        <flux:icon.check-badge class="size-3.5" /> {{ __('Key saved') }}
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-zinc-400">{{ __('No key') }}</span>
                                @endif

                                <span class="ml-auto text-[10px] font-mono text-zinc-400 truncate">{{ $row['model_id'] }}</span>
                            </div>

                            <div class="mt-2 flex items-center gap-2">
                                <flux:input type="password" size="sm" class="flex-1"
                                            wire:model="providerRows.{{ $key }}.api_key"
                                            placeholder="{{ $row['has_key'] ? __('•••••••••••• (saved — type to replace)') : __('Enter API key...') }}" />
                                @if($row['has_key'])
                                    <flux:button wire:click="clearProviderKey('{{ $key }}')" variant="ghost" size="sm" icon="trash"
                                                 class="text-rose-600 dark:text-rose-400 shrink-0">
                                        {{ __('Remove') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Per-resolution enable + pricing --}}
            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <flux:icon.sparkles class="size-3.5 text-indigo-500" />
                    <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Resolutions & Pricing') }}</span>
                </div>
                <div class="mb-2 flex items-start gap-1.5 rounded-lg bg-violet-50/60 dark:bg-violet-500/10 px-2.5 py-1.5 text-[10px] text-violet-700 dark:text-violet-300 leading-relaxed">
                    <flux:icon.information-circle class="size-3.5 mt-px shrink-0" />
                    <span>{{ __('Credits are charged per second of video at the selected resolution. Disabled resolutions are hidden from users.') }}</span>
                </div>

                @php
                    $tierLabels = [
                        '480p'  => __('480p · Draft'),
                        '720p'  => __('720p · HD'),
                        '1080p' => __('1080p · Full HD'),
                        '4k'    => __('4K · Ultra HD'),
                    ];
                @endphp
                <div class="space-y-2">
                    @foreach($tierLabels as $tier => $label)
                        @if(isset($resolutionRows[$tier]))
                            <div class="rounded-xl border p-3 transition-colors
                                        {{ $resolutionRows[$tier]['enabled']
                                            ? 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-bg-color)'
                                            : 'border-dashed border-zinc-200 bg-zinc-50/50 dark:border-white/8 dark:bg-neutral-900/40 opacity-70' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-[13px] font-bold text-zinc-800 dark:text-zinc-100 flex-1 min-w-0 truncate">{{ $label }}</span>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <flux:icon.bolt class="size-3.5 text-amber-500" />
                                        <input type="number" min="1" wire:model="resolutionRows.{{ $tier }}.credit_cost"
                                               class="w-16 rounded-lg border border-zinc-200 dark:border-white/8 bg-white dark:bg-neutral-950 px-2 py-1 text-xs text-center tabular-nums text-zinc-800 dark:text-zinc-200 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-400/30 focus:outline-none" />
                                        <span class="text-[10px] font-medium text-zinc-400 whitespace-nowrap w-14">{{ __('cr') }}/{{ __('sec') }}</span>
                                    </div>

                                    <flux:switch wire:model.live="resolutionRows.{{ $tier }}.enabled" />
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-white/6">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveModel" variant="primary">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>
