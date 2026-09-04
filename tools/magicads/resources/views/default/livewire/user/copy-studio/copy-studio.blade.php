@php
    // Static engine/model metadata, shaped for the client. Engine + model
    // switching is done entirely in Alpine (see x-data below) so it's instant —
    // no Livewire round-trip is needed just to repaint the model list.
    $tierMeta = [
        'premium'  => ['label' => __('Premium'),  'tone' => 'text-amber-600 dark:text-amber-400'],
        'standard' => ['label' => __('Standard'), 'tone' => 'text-emerald-600 dark:text-emerald-400'],
        'fast'     => ['label' => __('Fast'),     'tone' => 'text-sky-600 dark:text-sky-400'],
    ];
    $enginesForJs = [];
    foreach ($this->enginesAvailable as $eKey => $eMeta) {
        $jsModels = [];
        foreach ($eMeta['enabled_models'] as $mId => $mMeta) {
            $jsModels[$mId] = [
                'id'          => $mId,
                'label'       => $mMeta['label'] ?? $mId,
                'description' => $mMeta['description'] ?? '',
                'tier'        => $mMeta['tier'] ?? 'standard',
                'credit_cost' => (int) ($mMeta['credit_cost'] ?? 1),
            ];
        }
        $enginesForJs[$eKey] = [
            'label'   => $eMeta['label'],
            'default' => (string) array_key_first($jsModels),
            'models'  => $jsModels,
        ];
    }
@endphp

<div x-data="{
    copiedKey: null,

    {{-- Alpine-entangled state for instant UI.
         platform / framework / tone are LIVE so the field chips under the
         picker, the live preview panel, and any contextual info refresh on
         every click. The rest stays deferred for typing performance.
         engine / model are deferred and switched client-side — generate()
         re-validates them server-side, so no round-trip is needed to pick. --}}
    platform:  @entangle('platform').live,
    objective: @entangle('objective'),
    framework: @entangle('framework').live,
    tone:      @entangle('tone').live,
    engine:    @entangle('engine'),
    model:     @entangle('model'),
    cta:       @entangle('cta'),
    variants:  @entangle('variantsCount'),
    brandId:   @entangle('brandId'),

    {{-- Static config pushed to the client once, at render. --}}
    engines:  @js($enginesForJs),
    tierMeta: @js($tierMeta),

    get currentModels()    { return (this.engines[this.engine] && this.engines[this.engine].models) || {}; },
    get currentModelList() { return Object.values(this.currentModels); },
    get currentModelMeta() { return this.currentModels[this.model] || null; },
    get currentEngineLabel() { return (this.engines[this.engine] && this.engines[this.engine].label) || this.engine; },
    tierOf(id) { let m = this.currentModels[id]; return this.tierMeta[(m && m.tier) || 'standard'] || this.tierMeta.standard; },

    selectEngine(key) {
        this.engine = key;
        {{-- Snap to the engine's default when the current model isn't on it. --}}
        if (! this.currentModels[this.model]) {
            this.model = (this.engines[key] && this.engines[key].default) || '';
        }
    },
    selectModel(id) { this.model = id; },

    copyToClipboard(txt, key) {
        navigator.clipboard.writeText(txt).then(() => {
            this.copiedKey = key;
            setTimeout(() => this.copiedKey = null, 1800);
        });
    }
}">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Creative Tools') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('Copy Studio') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('user.copy.library') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200
                     transition dark:text-zinc-300 dark:hover:text-white dark:bg-(--default-element-bg-color) dark:hover:bg-white/5 dark:border-white/8">
                        <flux:icon.book-open class="size-3.5" /> {{ __('Library') }}
                    </a>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Hero Command Deck                           --}}
            {{-- ========================================== --}}
            <div class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40
                        bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(99,102,241,0.26),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(139,92,246,0.22),transparent),radial-gradient(ellipse_60%_40%_at_50%_50%,rgba(56,189,248,0.10),transparent)]">
                
                <div class="relative px-6 md:px-8 py-10 flex flex-col xl:flex-row gap-6 items-start xl:items-center justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.pencil class="size-6 text-indigo-300" />
                                </div>
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl md:text-2xl font-extrabold text-white leading-tight tracking-tight">{{ __('Write ads that actually convert.') }}</h1>
                            <p class="text-xs text-zinc-400 mt-1 max-w-lg">
                                {{ __('Pick a platform, describe your offer, and get three polished copy variants — formatted to every character limit, using proven frameworks like AIDA and PAS.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Stat chips --}}
                    <div class="grid grid-cols-1 gap-2 w-full xl:w-auto">
                        <div class="px-3 py-2.5 rounded-xl bg-white/4 backdrop-blur-sm border border-white/10">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-lg bg-linear-to-br from-amber-400/20 to-orange-500/20 border border-amber-400/30 flex items-center justify-center">
                                    <flux:icon.bolt class="size-3.5 text-amber-400" />
                                </div>
                                <span class="text-[9px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Credits') }}</span>
                            </div>
                            <div class="text-base font-bold text-white leading-none tabular-nums text-right">{{ number_format($creditBalance) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Main two-column layout                       --}}
            {{-- ========================================== --}}
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-8">

                {{-- ===== MAIN FORM (left) ===== --}}
                <div class="space-y-4">

                    {{-- Platform picker --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-6 mb-10">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-indigo-500/20 shadow-sm shadow-neutral-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.rectangle-group class="size-4 text-indigo-400" />
                                </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Choose Platform') }}</h3>
                            <span class="ml-auto text-[10px] text-zinc-500 font-mono">{{ count(config('ad-copy.platforms')) }} {{ __('available') }}</span>
                        </div>

                        @foreach($this->platformsByGroup as $groupKey => $groupData)
                            @if(empty($groupData['platforms'])) @continue @endif
                            <div class="mb-4 last:mb-0">
                                <div class="text-[10px] uppercase tracking-widest text-zinc-500 mb-2 flex items-center gap-1.5">
                                    <flux:icon name="{{ $groupData['meta']['icon'] }}" class="size-3" variant="mini" />
                                    {{ __($groupData['meta']['label']) }}
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                    @foreach($groupData['platforms'] as $slug => $p)
                                        <button type="button" @click="platform = '{{ $slug }}'"
                                                class="group relative text-left p-3 rounded-xl border transition-all"
                                                :class="platform === '{{ $slug }}' ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-950/30 ring-1 ring-indigo-400/30' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/40'">
                                            <div class="flex items-start gap-2">
                                                <span class="relative w-7 h-7 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-{{ $p['tint'] }}-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                                    @include('livewire.user.copy-studio.partials._platform-icon', [
                                                        'slug'     => $slug,
                                                        'fallback' => $p['icon'],
                                                        'class'    => 'size-3.5 text-'.$p['tint'].'-400',
                                                    ])
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="text-[11px] font-bold truncate" :class="platform === '{{ $slug }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'">{{ __($p['label']) }}</div>
                                                    <div class="text-[9px] text-zinc-500 truncate">{{ count($p['fields']) }} {{ __('fields') }}</div>
                                                </div>
                                                <flux:icon.check-circle class="size-4 text-indigo-500 shrink-0" x-show="platform === '{{ $slug }}'" x-cloak />
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Contextual info for selected platform (updates server-side, debounced via entangle defer) --}}
                        @php $selected = config("ad-copy.platforms.{$platform}"); @endphp
                        @if($selected)
                            <div class="mt-4 p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 dark:bg-indigo-950/30 dark:border-indigo-900/40">
                                <div class="text-[11px] text-indigo-900 dark:text-indigo-200 leading-relaxed">{{ __($selected['description']) }}</div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($selected['fields'] as $slug => $f)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-white/80 border border-indigo-200 text-indigo-700 dark:bg-neutral-900/80 dark:border-indigo-900/50 dark:text-indigo-300">
                                            {{ __($f['label']) }}
                                            <span class="text-indigo-400 font-mono">{{ $f['limit'] }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Campaign Brief --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-6 mb-10">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 shadow-sm shadow-emerald-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.clipboard-document-list class="size-4 text-emerald-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Campaign Brief') }}</h3>
                        </div>

                        <div class="space-y-6">
                            <flux:field>
                                <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Product / Offer Description') }} <span class="text-red-500">*</span></flux:label>
                                <flux:textarea wire:model.blur="productDescription" rows="3" maxlength="2000" placeholder="{{ __('e.g. A project management SaaS for remote teams that reduces meeting time by 60%.') }}" />
                                <flux:error name="productDescription" />
                            </flux:field>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Target Audience') }}</flux:label>
                                    <flux:textarea wire:model.blur="targetAudience" rows="2" maxlength="500" placeholder="{{ __('e.g. Startup founders aged 25-45 managing distributed teams') }}" />
                                </flux:field>
                                <flux:field>
                                    <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Key Benefits') }}</flux:label>
                                    <flux:textarea wire:model.blur="keyBenefits" rows="2" maxlength="500" placeholder="{{ __('e.g. Save 10 hours/week, never miss a deadline, integrates with Slack') }}" />
                                </flux:field>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                <flux:field class="self-start">
                                    <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Keywords to Include') }}</flux:label>
                                    <flux:input wire:model.blur="keywords" maxlength="200" placeholder="{{ __('project management, remote teams, productivity') }}" />
                                </flux:field>
                                <flux:field class="self-start">
                                    <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Preferred CTA') }}</flux:label>
                                    <flux:input wire:model.blur="cta" maxlength="40" placeholder="{{ __('Start Free Trial') }}" />
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach(config('ad-copy.cta_library') as $item)
                                            <button type="button" @click="cta = @js(__($item))"
                                                    class="text-[10px] px-2 py-0.5 rounded-full border transition"
                                                    :class="cta === @js(__($item)) ? 'bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-950/40 dark:border-indigo-700/40 dark:text-indigo-300' : 'bg-white border-zinc-200 text-zinc-500 hover:border-indigo-200 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400'">{{ __($item) }}</button>
                                        @endforeach
                                    </div>
                                </flux:field>
                            </div>
                        </div>
                    </div>

                    {{-- Voice & Framework --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-6 mb-10">
                        <div class="flex items-center gap-2 mb-5">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-violet-500/20 shadow-lg shadow-violet-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.swatch class="size-4 text-violet-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Voice & Framework') }}</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                            <flux:field>
                                <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Objective') }}</flux:label>
                                <flux:select wire:model="objective">
                                    @foreach(config('ad-copy.objectives') as $slug => $obj)
                                        <option value="{{ $slug }}">{{ __($obj['label']) }} — {{ __($obj['hint']) }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <flux:field>
                                <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Tone of Voice') }}</flux:label>
                                <flux:select wire:model="tone">
                                    @foreach(config('ad-copy.tones') as $slug => $label)
                                        <option value="{{ $slug }}">{{ __($label) }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        </div>

                        <div class="mb-6">
                            <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2 block">{{ __('Copywriting Framework') }}</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                @foreach(config('ad-copy.frameworks') as $slug => $fw)
                                    <button type="button" @click="framework = '{{ $slug }}'"
                                            class="text-left p-3 rounded-xl border transition"
                                            :class="framework === '{{ $slug }}' ? 'border-violet-400 bg-violet-50/50 dark:bg-violet-950/30 ring-1 ring-violet-400/30' : 'border-zinc-200 bg-white hover:border-violet-200 dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-violet-700/40'">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="text-[11px] font-bold" :class="framework === '{{ $slug }}' ? 'text-violet-700 dark:text-violet-300' : 'text-zinc-800 dark:text-zinc-200'">{{ __($fw['label']) }}</span>
                                            <flux:icon.check-circle class="size-3.5 text-violet-500" x-show="framework === '{{ $slug }}'" x-cloak />
                                        </div>
                                        <div class="text-[9px] text-zinc-500 line-clamp-1 mb-0.5">{{ __($fw['full']) }}</div>
                                        <div class="text-[9px] text-violet-500 italic">{{ __($fw['best_for']) }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4">
                            <flux:field>
                                <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Extra Instructions') }}</flux:label>
                                <flux:textarea wire:model.blur="extraInstructions" rows="2" maxlength="500" placeholder="{{ __('Avoid superlatives. Include a 20% discount mention. End with a rhetorical question.') }}" />
                            </flux:field>
                        </div>
                    </div>

                    {{-- ========================================== --}}
                    {{-- Launch (Engine + Variants + Brand + Generate) --}}
                    {{-- ========================================== --}}
                    @php
                        $canAfford       = $creditBalance >= $copyCost;
                        $selectedBrand   = collect($availableBrands)->firstWhere('id', $brandId);
                        $enginesConfig   = $this->enginesAvailable;
                    @endphp

                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-6 mb-8">
                        {{-- Section header (matches other sections) --}}
                        <div class="flex items-center gap-2 mb-5">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-indigo-500/20 shadow-lg shadow-indigo-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.rocket-launch class="size-4 text-indigo-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Launch') }}</h3>
                            <span class="ml-auto text-[10px] text-zinc-500 font-mono uppercase tracking-widest">{{ __('Final step') }}</span>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-7">

                            {{-- AI Engine + Model ---------------------------------------- --}}
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 dark:border-white/8 dark:bg-neutral-900/40 p-3">
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <flux:icon.cpu-chip class="size-3 text-zinc-400" />
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ __('AI Engine') }}</span>
                                </div>

                                {{-- Engine picker --}}
                                <div class="grid grid-cols-1 gap-1.5">
                                    @foreach($enginesConfig as $key => $meta)
                                        <button type="button" @click="selectEngine('{{ $key }}')"
                                                class="group/engine relative flex items-center gap-2.5 px-2.5 py-2 rounded-lg border text-left transition-all"
                                                :class="engine === '{{ $key }}' ? 'border-indigo-400 bg-white ring-1 ring-indigo-400/30 shadow-sm dark:bg-neutral-950' : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/40'">
                                            <span class="relative w-7 h-7 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-{{ $meta['tint'] ?? 'indigo' }}-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                                @include('livewire.user.copy-studio.partials._engine-icon', [
                                                    'slug'     => $key,
                                                    'fallback' => $meta['icon'] ?? 'cpu-chip',
                                                    'class'    => 'size-3.5 text-'.($meta['tint'] ?? 'indigo').'-400',
                                                ])
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-[11px] font-bold truncate" :class="engine === '{{ $key }}' ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'">{{ $meta['label'] }}</span>
                                                <span class="block text-[9px] text-zinc-500 truncate">{{ count($meta['enabled_models']) }} {{ __('models') }}</span>
                                            </span>
                                            <flux:icon.check-circle class="size-4 text-indigo-500 shrink-0" x-show="engine === '{{ $key }}'" x-cloak />
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Model row — compact "current model + Change" trigger.
                                     Rendered client-side from Alpine so switching engine
                                     repaints the model instantly (no Livewire round-trip).
                                     The full model list lives in a flux:modal at the bottom
                                     of the component, opened with a `modal-show` event. --}}
                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-white/8">
                                    <template x-if="currentModelList.length > 0">
                                        <div>
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <flux:icon.sparkles class="size-3 text-zinc-400" />
                                                <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Model') }}</span>
                                                <button type="button"
                                                        x-on:click="$dispatch('modal-show', { name: 'copy-studio-model-picker' })"
                                                        class="ml-auto inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 transition">
                                                    {{ __('Change') }}
                                                    <flux:icon.chevron-right class="size-3" />
                                                </button>
                                            </div>

                                            <template x-if="currentModelMeta">
                                                <button type="button"
                                                        x-on:click="$dispatch('modal-show', { name: 'copy-studio-model-picker' })"
                                                        class="w-full text-left px-2.5 py-2 rounded-lg border border-indigo-400 bg-white ring-1 ring-indigo-400/30 dark:bg-neutral-950 hover:bg-indigo-50/40 dark:hover:bg-indigo-950/20 transition">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                        <span class="text-[11px] font-bold truncate flex-1 text-indigo-700 dark:text-indigo-300" x-text="currentModelMeta.label"></span>
                                                        <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 dark:text-amber-400" :title="'{{ __('Credits per 1,000 words') }}'">
                                                            <flux:icon.bolt class="size-2.5" />
                                                            <span x-text="currentModelMeta.credit_cost"></span>
                                                            <span class="font-normal opacity-70">/ 1k</span>
                                                        </span>
                                                        <span class="text-[9px] font-bold uppercase tracking-wider" :class="tierOf(model).tone" x-text="tierOf(model).label"></span>
                                                    </div>
                                                    <div class="mt-0.5 text-[9px] text-zinc-500 leading-snug line-clamp-2" x-show="currentModelMeta.description" x-text="currentModelMeta.description"></div>
                                                    <div class="mt-0.5 text-[9px] font-mono text-zinc-400 truncate" x-text="model"></div>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="currentModelList.length === 0">
                                        <div class="text-[10px] text-rose-600 dark:text-rose-400 leading-relaxed">
                                            {{ __('No models are currently enabled for this engine. Ask an admin to enable one in config/ad-copy.php.') }}
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Variants ------------------------------------------------ --}}
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 dark:border-white/8 dark:bg-neutral-900/40 p-3">
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <flux:icon.squares-plus class="size-3 text-zinc-400" />
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Variants') }}</span>
                                    <span class="ml-auto inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                        <span class="tabular-nums" x-text="variants"></span>
                                        <span class="text-zinc-400 font-normal">× {{ __('per run') }}</span>
                                    </span>
                                </div>
                                <div class="grid grid-cols-6 gap-1">
                                    @foreach([1,2,3,4,5,6] as $n)
                                        <button type="button" @click="variants = {{ $n }}"
                                                class="relative h-9 rounded-lg text-xs font-bold transition-all tabular-nums"
                                                :class="variants === {{ $n }} ? 'text-white shadow-md shadow-indigo-500/30 scale-[1.04]' : 'bg-white text-zinc-500 border border-zinc-200 hover:border-indigo-300 hover:text-indigo-600 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:border-indigo-700/50'"
                                                x-bind:style="variants === {{ $n }} ? 'background: linear-gradient(120deg, #4F46E5, #0F172A);' : ''">{{ $n }}</button>
                                    @endforeach
                                </div>
                                <div class="mt-2 text-[10px] text-zinc-500 leading-relaxed">
                                    {{ __('More variants → more angles to A/B test.') }}
                                </div>
                            </div>

                            {{-- Brand --------------------------------------------------- --}}
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 dark:border-white/8 dark:bg-neutral-900/40 p-3">
                                <div class="flex items-center gap-1.5 mb-2.5">
                                    <flux:icon.swatch class="size-3 text-zinc-400" />
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Brand Context') }}</span>
                                </div>

                                @if(count($availableBrands) > 0)
                                    <div class="relative">
                                        <select wire:model.live="brandId"
                                                class="w-full appearance-none pl-3 pr-9 py-2 rounded-lg text-[12px] font-semibold border border-zinc-200 bg-white text-zinc-800 hover:border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none transition dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200 dark:hover:border-indigo-700/40">
                                            <option value="">{{ __('No brand context') }}</option>
                                            @foreach($availableBrands as $b)
                                                <option value="{{ $b['id'] }}">{{ $b['name'] }}{{ $b['is_default'] ? ' ★' : '' }}{{ $b['industry'] ? ' — '.$b['industry'] : '' }}</option>
                                            @endforeach
                                        </select>
                                        <flux:icon.chevron-down class="size-4 text-zinc-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                                    </div>
                                    <div class="mt-2 flex items-start gap-1.5 text-[10px] text-zinc-500 leading-relaxed">
                                        <flux:icon.information-circle class="size-3 mt-px shrink-0" />
                                        <span>{{ $selectedBrand ? __('Copy will match :brand voice & tone.', ['brand' => $selectedBrand['name']]) : __('Optional — tailors voice to your brand.') }}</span>
                                    </div>
                                @else
                                    <a href="{{ route('user.brands.create') }}" wire:navigate class="group/brand flex items-center gap-2 px-2.5 py-2 rounded-lg border border-dashed border-zinc-300 dark:border-white/8 hover:border-indigo-300 dark:hover:border-indigo-700/50 transition">
                                        <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                                            <flux:icon.plus class="size-3.5 text-indigo-500" />
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-[11px] font-bold text-zinc-800 dark:text-zinc-200">{{ __('Add a brand') }}</span>
                                            <span class="block text-[9px] text-zinc-500">{{ __('Optional — boosts on-brand copy') }}</span>
                                        </span>
                                        <flux:icon.arrow-right class="size-3.5 text-zinc-400 group-hover/brand:translate-x-0.5 group-hover/brand:text-indigo-500 transition" />
                                    </a>
                                @endif

                                {{-- Project — drop the generated copy into a project (optional) --}}
                                <div class="mt-3 pt-3 border-t border-zinc-200/70 dark:border-white/8">
                                    <div class="flex items-center gap-1.5 mb-2.5">
                                        <flux:icon.folder class="size-3 text-zinc-400" />
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">{{ __('Project') }}</span>
                                    </div>

                                    @if($availableProjects->isNotEmpty())
                                        <div class="relative">
                                            <select wire:model.live="projectId"
                                                    class="w-full appearance-none pl-3 pr-9 py-2 rounded-lg text-[12px] font-semibold border border-zinc-200 bg-white text-zinc-800 hover:border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 focus:outline-none transition dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200 dark:hover:border-indigo-700/40">
                                                <option value="">{{ __('No project') }}</option>
                                                @foreach($availableProjects as $p)
                                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                            <flux:icon.chevron-down class="size-4 text-zinc-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                                        </div>
                                        <div class="mt-2 flex items-start gap-1.5 text-[10px] text-zinc-500 leading-relaxed">
                                            <flux:icon.information-circle class="size-3 mt-px shrink-0" />
                                            <span>{{ __('Optional — the generated copy is added to the selected project.') }}</span>
                                        </div>
                                    @else
                                        <a href="{{ route('user.projects.index') }}" wire:navigate class="group/proj flex items-center gap-2 px-2.5 py-2 rounded-lg border border-dashed border-zinc-300 dark:border-white/8 hover:border-indigo-300 dark:hover:border-indigo-700/50 transition">
                                            <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                                                <flux:icon.plus class="size-3.5 text-indigo-500" />
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-[11px] font-bold text-zinc-800 dark:text-zinc-200">{{ __('Create a project') }}</span>
                                                <span class="block text-[9px] text-zinc-500">{{ __('Optional — organize related creatives') }}</span>
                                            </span>
                                            <flux:icon.arrow-right class="size-3.5 text-zinc-400 group-hover/proj:translate-x-0.5 group-hover/proj:text-indigo-500 transition" />
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Low-balance warning ------------------------------------ --}}
                        @if(!$canAfford)
                            <div class="mb-3 flex items-start gap-2 px-3 py-2 rounded-lg bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20">
                                <flux:icon.exclamation-triangle class="size-4 text-rose-500 shrink-0 mt-0.5" />
                                <div class="flex-1 text-[11px] text-rose-700 dark:text-rose-300 leading-relaxed">
                                    {{ __('This model costs :need credits per 1,000 words and you only have :have.', ['need' => $copyCost, 'have' => number_format($creditBalance)]) }}
                                    @if (\App\Services\HelperService::extensionSaaS())
                                        <a href="{{ route('user.billing') }}" wire:navigate class="font-bold underline hover:no-underline">{{ __('Top up') }}</a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Generate button (brand cool two-stop — see brand-palette.md) --}}
                        <button type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                                @disabled(!$canAfford)
                                class="group/cta relative w-full flex items-center justify-center gap-3 px-6 py-4 rounded-xl font-bold text-base text-white shadow-xs shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.995] transition-all overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                                style="background: linear-gradient(120deg, #4F46E5, #0F172A);">

                            {{-- Brighter hover layer --}}
                            <span class="pointer-events-none absolute inset-0 opacity-0 group-hover/cta:opacity-100 transition-opacity duration-300"
                                  style="background: linear-gradient(120deg, #6366F1, #1E293B);"></span>

                            {{-- Default label --}}
                            <span class="relative flex items-center gap-2.5" wire:loading.remove.flex wire:target="generate">
                                <span class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm flex items-center justify-center">
                                    <flux:icon.sparkles class="size-4" />
                                </span>
                                <span class="inline-flex items-baseline gap-1.5">
                                    <span>{{ __('Generate') }}</span>
                                </span>
                            </span>

                            {{-- Loading label --}}
                            <span class="relative flex items-center gap-2.5" wire:loading.flex wire:target="generate">
                                <svg class="size-5 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                    <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                                <span class="whitespace-nowrap">{{ __('Writing your copy') }}</span>
                                <span class="inline-flex items-center gap-1 shrink-0">
                                    <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                                    <span class="w-1 h-1 rounded-full bg-white animate-pulse" style="animation-delay: 150ms"></span>
                                    <span class="w-1 h-1 rounded-full bg-white animate-pulse" style="animation-delay: 300ms"></span>
                                </span>
                            </span>
                        </button>

                        {{-- Fine print --}}
                        <div class="mt-3 flex items-center justify-center gap-1.5 text-[10px] text-zinc-500">
                            <flux:icon.lock-closed class="size-3" />
                            {{ __('Your brief stays private. Only you see the output.') }}
                        </div>
                    </div>

                    {{-- ========================= --}}
                    {{-- RESULTS                     --}}
                    {{-- ========================= --}}
                    @if($this->latestCopy && $this->latestCopy->status === 'completed' && is_array($this->latestCopy->variants))
                        @php $latest = $this->latestCopy; $latestPlatform = config("ad-copy.platforms.{$latest->platform}"); @endphp
                        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-900/40 bg-linear-to-br from-emerald-50/40 to-white dark:from-emerald-950/20 dark:to-neutral-800 p-5">
                            <div class="flex items-start justify-between gap-3 mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-10 h-10 rounded-xl bg-linear-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                        <flux:icon.check class="size-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ __('Your copy is ready') }}</h3>
                                        <p class="text-[11px] text-zinc-500">{{ count($latest->variants) }} {{ __('variants for :p', ['p' => isset($latestPlatform['label']) ? __($latestPlatform['label']) : $latest->platform]) }} · {{ $latest->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="toggleFavorite({{ $latest->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition {{ $latest->is_favorite ? 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300' : 'bg-white border-zinc-200 text-zinc-500 hover:border-amber-200 dark:bg-(--default-element-bg-color) dark:border-white/8' }}">
                                    <flux:icon.star class="size-3.5 {{ $latest->is_favorite ? 'fill-amber-400 text-amber-400' : '' }}" />
                                    {{ $latest->is_favorite ? __('Saved') : __('Save') }}
                                </button>
                            </div>

                            <div class="space-y-3">
                                @foreach($latest->variants as $idx => $variant)
                                    <div class="rounded-xl border border-zinc-200 dark:border-white/8 bg-white dark:bg-(--default-element-bg-color) overflow-hidden">
                                        <div class="flex items-center justify-between px-4 py-2.5 bg-linear-to-r from-indigo-50/60 to-violet-50/30 border-b border-zinc-100 dark:border-white/6 dark:from-indigo-950/30 dark:to-violet-950/20">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-black text-white bg-linear-to-br from-indigo-500 to-violet-500 shadow-sm">{{ $idx + 1 }}</span>
                                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ __('Variant :n', ['n' => $idx + 1]) }}</span>
                                            </div>
                                            @php
                                                $allText = collect($variant)->map(fn ($v) => trim((string)$v))->filter()->implode("\n\n");
                                            @endphp
                                            <button type="button"
                                                    x-on:click="copyToClipboard(@js($allText), 'variant-{{ $latest->id }}-{{ $idx }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold text-zinc-500 hover:text-indigo-600 bg-white hover:bg-indigo-50 border border-zinc-200 hover:border-indigo-200 transition dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400 dark:hover:text-indigo-300 dark:hover:bg-indigo-950/30">
                                                <template x-if="copiedKey === 'variant-{{ $latest->id }}-{{ $idx }}'">
                                                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400"><flux:icon.check class="size-3" /> {{ __('Copied') }}</span>
                                                </template>
                                                <template x-if="copiedKey !== 'variant-{{ $latest->id }}-{{ $idx }}'">
                                                    <span class="inline-flex items-center gap-1"><flux:icon.document-duplicate class="size-3" /> {{ __('Copy all') }}</span>
                                                </template>
                                            </button>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            @foreach(($latestPlatform['fields'] ?? []) as $fieldSlug => $fieldMeta)
                                                @php
                                                    $value = $variant[$fieldSlug] ?? '';
                                                    $len = mb_strlen((string)$value);
                                                    $over = $len > $fieldMeta['limit'];
                                                @endphp
                                                <div>
                                                    <div class="flex items-center justify-between mb-1">
                                                        <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">{{ __($fieldMeta['label']) }}</label>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[10px] tabular-nums font-mono {{ $over ? 'text-red-500' : 'text-zinc-400' }}">{{ $len }}/{{ $fieldMeta['limit'] }}</span>
                                                            <button type="button"
                                                                    x-on:click="copyToClipboard(@js((string)$value), 'field-{{ $latest->id }}-{{ $idx }}-{{ $fieldSlug }}')"
                                                                    class="text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition" aria-label="{{ __('Copy') }}">
                                                                <template x-if="copiedKey === 'field-{{ $latest->id }}-{{ $idx }}-{{ $fieldSlug }}'">
                                                                    <flux:icon.check class="size-3.5 text-emerald-500" />
                                                                </template>
                                                                <template x-if="copiedKey !== 'field-{{ $latest->id }}-{{ $idx }}-{{ $fieldSlug }}'">
                                                                    <flux:icon.document-duplicate class="size-3.5" />
                                                                </template>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="text-[13px] leading-relaxed text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap break-words p-3 rounded-lg bg-zinc-50 border border-zinc-100 dark:bg-neutral-950/50 dark:border-white/6">{{ $value ?: __('—') }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ===== RIGHT RAIL ===== --}}
                <aside class="space-y-8">
                    {{-- Live preview --}}
                    @php $activePlatform = config("ad-copy.platforms.{$platform}"); @endphp
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) overflow-hidden">
                        <div class="px-5 py-3 border-b border-zinc-100 dark:border-white/8 bg-zinc-50/50 dark:bg-neutral-900/50">
                            <h3 class="text-xs font-bold text-zinc-800 dark:text-zinc-100 flex items-center gap-1.5">{{ __('Preview') }}</h3>
                        </div>
                        <div class="p-5">
                            <div class="text-[10px] uppercase tracking-widest font-bold text-zinc-400 mb-1">{{ __('Platform') }}</div>
                            <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100 mb-3">{{ isset($activePlatform['label']) ? __($activePlatform['label']) : '—' }}</div>

                            <div class="space-y-2">
                                @foreach(($activePlatform['fields'] ?? []) as $slug => $f)
                                    <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-zinc-50 border border-zinc-100 dark:bg-(--default-element-bg-color) dark:border-white/6">
                                        <span class="text-[11px] font-semibold text-zinc-700 dark:text-zinc-300 truncate">{{ __($f['label']) }}</span>
                                        <span class="text-[10px] font-mono text-zinc-500">{{ $f['limit'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-white/8 grid grid-cols-2 gap-2 text-[11px]">
                                <div class="p-2 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)">
                                    <div class="text-[9px] uppercase tracking-widest font-bold text-zinc-400">{{ __('Framework') }}</div>
                                    <div class="font-semibold text-zinc-700 dark:text-zinc-300">{{ ($fwLabel = config("ad-copy.frameworks.{$framework}.label")) ? __($fwLabel) : '—' }}</div>
                                </div>
                                <div class="p-2 rounded-lg bg-zinc-50 dark:bg-(--default-element-bg-color)">
                                    <div class="text-[9px] uppercase tracking-widest font-bold text-zinc-400">{{ __('Tone') }}</div>
                                    <div class="font-semibold text-zinc-700 dark:text-zinc-300">{{ ($toneLabel = config("ad-copy.tones.{$tone}")) ? __($toneLabel) : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tips card --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="relative w-8 h-8 rounded-xl bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 shadow-lg shadow-amber-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.light-bulb class="size-4 text-amber-400" />
                                <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.9)] animate-pulse"></span>
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Pro tips') }}</h3>
                        </div>
                        <ul class="space-y-2 text-[11px] text-zinc-600 dark:text-zinc-400">
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Be specific: "saves 10 hours/week" beats "saves time".') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Describe your audience with concrete traits — age, job, pain.') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Pick a framework that matches your funnel stage.') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Run 3 variants, A/B test the top 2.') }}</li>
                        </ul>
                    </div>

                    {{-- Recent runs --}}
                    @if($this->recentCopies->isNotEmpty())
                        <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-bold text-zinc-800 dark:text-zinc-100 flex items-center gap-1.5"><flux:icon.clock class="size-3.5 text-indigo-500" /> {{ __('Recent') }}</h3>
                                <a href="{{ route('user.copy.library') }}" wire:navigate class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-300 hover:underline">{{ __('View all') }}</a>
                            </div>
                            <div class="space-y-2">
                                @foreach($this->recentCopies as $rc)
                                    <button type="button" wire:click="reusePrevious({{ $rc->id }})" class="w-full text-left p-2.5 rounded-lg border border-zinc-100 hover:border-indigo-200 bg-zinc-50/30 hover:bg-indigo-50/30 transition dark:border-white/6 dark:hover:border-indigo-700/40 dark:bg-neutral-900/30 dark:hover:bg-indigo-950/20">
                                        <div class="flex items-center gap-2">
                                            <span class="relative w-6 h-6 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-{{ $rc->platformTint() }}-500/20 flex items-center justify-center shrink-0 dark:bg-neutral-950 dark:border-white/8">
                                                @include('livewire.user.copy-studio.partials._platform-icon', [
                                                    'slug'     => $rc->platform,
                                                    'fallback' => $rc->platformIcon(),
                                                    'class'    => 'size-3 text-'.$rc->platformTint().'-400',
                                                ])
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200 truncate">{{ $rc->title ?: __($rc->platformLabel()) }}</div>
                                                <div class="text-[9px] text-zinc-500">{{ __($rc->platformLabel()) }} · {{ $rc->created_at->diffForHumans() }}</div>
                                            </div>
                                            @if($rc->is_favorite)
                                                <flux:icon.star class="size-3 fill-amber-400 text-amber-400" />
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </div>

    {{-- ============================================================
         Model picker modal — opened from the engine card "Change" button.
         Rendered client-side from Alpine so it reflects the selected engine
         instantly and selecting a model has no Livewire round-trip. The
         value is entangled, so generate() still validates it server-side.
         ============================================================ --}}
    <flux:modal name="copy-studio-model-picker" class="max-w-lg">
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ __('Choose a model') }}</h3>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                    <span x-text="currentModelList.length"></span>
                    {{ __('models available for') }}
                    <span class="font-semibold" x-text="currentEngineLabel"></span>.
                    {{ __('Pick the one that fits your run.') }}
                </p>
            </div>

            <template x-if="currentModelList.length > 0">
                <div class="space-y-1.5 max-h-[60vh] overflow-y-auto pr-1">
                    <template x-for="m in currentModelList" :key="m.id">
                        <button type="button"
                                x-on:click="selectModel(m.id); $dispatch('modal-close', { name: 'copy-studio-model-picker' })"
                                class="w-full text-left px-3 py-2.5 rounded-lg border transition-all"
                                :class="model === m.id
                                    ? 'border-indigo-400 bg-white ring-1 ring-indigo-400/30 dark:bg-neutral-950'
                                    : 'border-zinc-200 bg-white hover:border-indigo-200 dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/40'">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full"
                                      :class="model === m.id ? 'bg-indigo-500' : 'bg-zinc-300 dark:bg-neutral-600'"></span>
                                <span class="text-[12px] font-bold truncate flex-1"
                                      :class="model === m.id ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200'"
                                      x-text="m.label"></span>
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-600 dark:text-amber-400" :title="'{{ __('Credits per 1,000 words') }}'">
                                    <flux:icon.bolt class="size-3" />
                                    <span x-text="m.credit_cost"></span>
                                    <span class="font-normal opacity-70">/ 1k {{ __('words') }}</span>
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-wider"
                                      :class="(tierMeta[m.tier] || tierMeta.standard).tone"
                                      x-text="(tierMeta[m.tier] || tierMeta.standard).label"></span>
                                <flux:icon.check-circle class="size-4 text-indigo-500 shrink-0" x-show="model === m.id" x-cloak />
                            </div>
                            <div class="mt-0.5 text-[10px] text-zinc-500 leading-snug" x-show="m.description" x-text="m.description"></div>
                            <div class="mt-0.5 text-[9px] font-mono text-zinc-400 truncate" x-text="m.id"></div>
                        </button>
                    </template>
                </div>
            </template>

            <template x-if="currentModelList.length === 0">
                <div class="text-xs text-rose-600 dark:text-rose-400">
                    {{ __('No models are currently enabled for this engine. Ask an admin to enable one in config/ad-copy.php.') }}
                </div>
            </template>

            <div class="flex justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
