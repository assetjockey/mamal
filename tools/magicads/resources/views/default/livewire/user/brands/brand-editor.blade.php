<div x-data="{
    step: 1,
    totalSteps: 4,
    open: { 1: true, 2: false, 3: false, 4: false },
    toggle(n) { this.open[n] = !this.open[n]; if (this.open[n]) this.step = n; },
    goTo(n) { this.step = n; this.open[n] = true; },
}">
    <div class="flex justify-center">
        <div class="w-full xl:w-11/12">

            {{-- Top toolbar --}}
            <div class="mb-6 flex items-center justify-between">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('user.dashboard') }}" separator="slash" class="text-xs">{{ __('Home') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="{{ route('user.brands.index') }}" separator="slash" class="text-xs">{{ __('Brand Kit') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ $brand ? __('Edit') : __('Create') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
                <a href="{{ route('user.brands.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 bg-white hover:bg-zinc-50 border border-zinc-200 shadow-sm transition dark:text-zinc-300 dark:hover:text-white dark:bg-(--default-element-bg-color) dark:hover:bg-white/5 dark:border-white/8">
                    <flux:icon.arrow-left class="size-3.5" /> {{ __('Back to brands') }}
                </a>
            </div>

            {{-- ========================================== --}}
            {{-- Dark hero with step tracker                  --}}
            {{-- ========================================== --}}
            <div class="relative mb-10 overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40 bg-zinc-950 bg-[radial-gradient(ellipse_80%_50%_at_10%_-10%,rgba(79,70,229,0.22),transparent),radial-gradient(ellipse_80%_50%_at_110%_110%,rgba(245,158,11,0.14),transparent)]">              
                <div class="absolute -top-24 -right-16 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-16 w-96 h-96 rounded-full bg-violet-500/10 blur-[120px] pointer-events-none"></div>
                <div class="absolute top-0 inset-x-0 h-px bg-linear-to-r from-transparent via-indigo-400/60 to-transparent"></div>

                <div class="relative p-6 md:p-8">

                    {{-- Header --}}
                    <div class="flex flex-wrap items-start gap-4 mb-6">
                        <div class="relative shrink-0">
                            <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 via-violet-500 to-indigo-600 p-px shadow-xl shadow-indigo-500/30">
                                <div class="w-full h-full rounded-[15px] bg-zinc-950 flex items-center justify-center">
                                    <flux:icon.bookmark class="size-5 text-indigo-300" />
                                </div>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($brand && $is_default)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-400/30 text-[9px] font-bold uppercase tracking-[0.18em] text-amber-300">
                                        <flux:icon.star class="size-2.5" /> {{ __('Default') }}
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-lg md:text-xl font-extrabold text-white tracking-tight">{{ __('Brand setup') }}</h1>
                            <p class="text-xs text-zinc-400 mt-0.5 max-w-lg">{{ __('Provide your brand details once, and let our AI generate on-brand ad assets.') }}</p>
                        </div>

                    </div>

                    {{-- Step tracker (4 steps) --}}
                    @php
                        $steps = [
                            1 => __('Brand Name'),
                            2 => __('Select Logo'),
                            3 => __('Select Colors'),
                            4 => __('Advanced Setup'),
                        ];
                    @endphp
                    <div class="flex items-center justify-between gap-2 px-2">
                        @foreach($steps as $n => $label)
                            <button type="button" @click="goTo({{ $n }})" class="relative flex-1 flex flex-col items-center group focus:outline-hidden">
                                <div class="w-9 h-9 rounded-full border flex items-center justify-center text-[11px] font-bold transition-all duration-300"
                                     :class="step === {{ $n }} ? 'bg-linear-to-br from-indigo-500 to-violet-600 border-indigo-400/50 text-white shadow-lg shadow-indigo-500/40 scale-110' : step > {{ $n }} ? 'bg-emerald-500/15 border-emerald-400/40 text-emerald-300' : 'bg-white/5 border-white/10 text-zinc-500 group-hover:border-white/20'">
                                    <span x-show="step <= {{ $n }}" x-text="{{ $n }}"></span>
                                    <flux:icon.check x-show="step > {{ $n }}" x-cloak class="size-4" />
                                </div>
                                <span class="mt-2 text-[10px] font-semibold uppercase tracking-wider text-center"
                                      :class="step === {{ $n }} ? 'text-white' : step > {{ $n }} ? 'text-emerald-400/80' : 'text-zinc-500'">{{ $label }}</span>
                            </button>
                            @if($n < 4)
                                <div class="flex-1 h-[2px] rounded-full bg-white/5 overflow-hidden -mt-5">
                                    <div class="h-full rounded-full transition-all duration-500" :class="step > {{ $n }} ? 'w-full bg-linear-to-r from-emerald-400 to-indigo-400' : step === {{ $n }} ? 'w-1/2 bg-linear-to-r from-indigo-400 to-indigo-400/0' : 'w-0'"></div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- Light content area                           --}}
            {{-- ========================================== --}}
            <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-7">

                {{-- MAIN FORM --}}
                <div class="space-y-8">

                    {{-- Import from website --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-5">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-indigo-500/20 shadow-sm shadow-indigo-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.globe-alt class="size-4 text-indigo-400" />
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Import from website') }}</h3>
                            <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900/40">
                                <flux:icon.sparkles class="size-2.5" /> {{ __('AI') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-3 ml-10">{{ __('Automatically import your brand from your website, or input the details manually.') }}</p>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1">
                                <flux:icon.link class="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-400" />
                                <input type="url" wire:model="website_url" placeholder="https://yourbrand.com" class="w-full pl-9 pr-3 py-2 rounded-lg bg-white border border-zinc-200 text-xs text-zinc-700 placeholder:text-zinc-400 focus:outline-hidden focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200 dark:placeholder:text-zinc-500" />
                            </div>
                            <button type="button" wire:click="importFromWebsite" wire:loading.attr="disabled" wire:target="importFromWebsite" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-semibold text-white shadow-sm shadow-indigo-500/25 hover:shadow-lg transition disabled:opacity-50" style="background: linear-gradient(120deg, #4F46E5, #0F172A) 30%;">
                                <flux:icon.sparkles class="size-3.5" wire:loading.remove wire:target="importFromWebsite" />
                                <svg wire:loading wire:target="importFromWebsite" class="size-3.5 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                <span wire:loading.remove wire:target="importFromWebsite">{{ __('Recommended') }}</span>
                                <span wire:loading wire:target="importFromWebsite">{{ __('Importing...') }}</span>
                            </button>
                            @if($importState === 'done')
                                <span class="inline-flex items-center justify-center gap-1 px-3 py-2 rounded-lg text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 border border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/40">
                                    <flux:icon.check class="size-3" /> {{ __('Imported') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- =========================================== --}}
                    {{-- STEP 1: Name + Description                   --}}
                    {{-- =========================================== --}}
                    <div class="rounded-2xl border overflow-hidden transition-all"
                         :class="open[1] ? 'border-indigo-300 bg-white dark:border-indigo-700/40 dark:bg-(--default-element-light-bg-color) shadow-sm shadow-indigo-500/10' : 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-200 dark:hover:border-indigo-700/30'">
                        <button type="button" @click="toggle(1)" class="w-full flex items-center gap-3 p-5 text-left">
                            <span class="relative w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-indigo-500/20 shadow-lg shadow-indigo-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8 shrink-0">
                                <flux:icon.identification class="size-4 text-indigo-400" />                                
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Write Brand Name & Description') }}</div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('Add your brand name and a clear description to define your identity.') }}</div>
                            </div>
                            @if(filled($name))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold text-indigo-700 bg-indigo-50 border border-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-900/40 max-w-[140px] truncate">{{ $name }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-red-600 bg-red-50 border border-red-100 dark:bg-red-950/40 dark:text-red-300 dark:border-red-900/40">{{ __('Required') }}</span>
                            @endif
                            <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform" x-bind:class="open[1] && 'rotate-180'" />
                        </button>
                        <div x-show="open[1]" x-collapse>
                            <div class="px-5 pb-5 border-t border-zinc-100 dark:border-white/8 pt-4 space-y-6">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Brand Name') }}</label>
                                        <span class="text-[10px] text-zinc-400 tabular-nums">{{ strlen($name) }}/120</span>
                                    </div>
                                    <flux:input wire:model.live.debounce.300ms="name" maxlength="120" placeholder="{{ __('e.g. Netomi, Nike, Acme Co.') }}" />
                                    <flux:error name="name" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:field>
                                        <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Industry') }}</flux:label>
                                        <flux:select wire:model="industry">
                                            <option value="">{{ __('Select industry') }}</option>
                                            @foreach(['E-Commerce','SaaS / Tech','Food & Beverage','Fashion','Health & Fitness','Real Estate','Education','Travel','Finance','Beauty','Automotive','Entertainment','Agency / Services','Manufacturing','Other'] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Tagline') }}</flux:label>
                                        <flux:input wire:model="tagline" maxlength="180" placeholder="{{ __('A short one-liner') }}" />
                                    </flux:field>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Product / Service Description') }}</label>
                                        <span class="text-[10px] text-zinc-400 tabular-nums">{{ __('Characters :n', ['n' => strlen($description)]) }}</span>
                                    </div>
                                    <flux:textarea wire:model.live.debounce.500ms="description" rows="4" maxlength="2000" placeholder="{{ __('We provide professional UI/UX design services, specializing in websites, web apps and mobile apps with user-centered, scalable, and visually impactful design solutions.') }}" />
                                </div>

                                <div class="flex justify-end">
                                    <button type="button" @click="open[1] = false; step = 2; open[2] = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white shadow-md shadow-indigo-500/25 hover:shadow-lg transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A) 30%;">
                                        {{ __('Save and Continue') }} <flux:icon.arrow-right class="size-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =========================================== --}}
                    {{-- STEP 2: Logo                                 --}}
                    {{-- =========================================== --}}
                    <div class="rounded-2xl border overflow-hidden transition-all"
                         :class="open[2] ? 'border-sky-300 bg-white dark:border-sky-700/40 dark:bg-(--default-element-light-bg-color) shadow-sm shadow-sky-500/10' : 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-sky-200 dark:hover:border-sky-700/30'">
                        <button type="button" @click="toggle(2)" class="w-full flex items-center gap-3 p-5 text-left">
                            <span class="relative w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-sky-500/20 shadow-lg shadow-sky-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8 shrink-0">
                                <flux:icon.photo class="size-4 text-sky-400" />                                
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Select Brand Logo') }}</div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('Upload a clear logo to visually represent and strengthen your brand identity.') }}</div>
                            </div>
                            @if($logo_path)
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-zinc-50 border border-zinc-200 dark:bg-(--default-element-bg-color) dark:border-white/8">
                                    <img src="{{ URL::asset($logo_path) }}" class="w-4 h-4 rounded object-contain" />
                                    <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-300 truncate max-w-[80px]">{{ $name ?: __('Logo') }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-amber-600 bg-amber-50 border border-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/40">{{ __('Optional') }}</span>
                            @endif
                            <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform" x-bind:class="open[2] && 'rotate-180'" />
                        </button>
                        <div x-show="open[2]" x-collapse>
                            <div class="px-5 pb-5 border-t border-zinc-100 dark:border-white/8 pt-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <label class="cursor-pointer">
                                        <div class="flex flex-col items-center justify-center h-40 border-2 border-dashed border-zinc-300 rounded-xl bg-zinc-50 hover:border-sky-400 hover:bg-sky-50/30 transition dark:border-neutral-600 dark:bg-(--default-element-bg-color) dark:hover:bg-sky-950/20">
                                            <flux:icon.cloud-arrow-up class="size-8 text-zinc-400 mb-2" />
                                            <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ __('Click to upload') }}</p>
                                            <p class="text-[10px] text-zinc-500 mt-0.5">{{ __('PNG, JPG or SVG — max 4MB') }}</p>
                                        </div>
                                        <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                                    </label>

                                    <div class="flex flex-col items-center justify-center h-40 rounded-xl border border-zinc-200 bg-linear-to-br from-zinc-50 to-zinc-100 overflow-hidden relative dark:border-white/8 dark:from-neutral-900 dark:to-neutral-950">
                                        @if($logo)
                                            <div class="absolute top-2 left-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 border border-amber-200 text-[9px] font-bold uppercase tracking-widest text-amber-700 dark:bg-amber-950/40 dark:border-amber-900/40 dark:text-amber-300">
                                                <flux:icon.arrow-up-tray class="size-2.5" /> {{ __('Pending save') }}
                                            </div>
                                            <img src="{{ $logo->temporaryUrl() }}" alt="preview" class="max-w-[75%] max-h-[75%] object-contain drop-shadow-md" />
                                        @elseif($logo_path)
                                            <img src="{{ URL::asset($logo_path) }}" alt="{{ $name }}" class="max-w-[75%] max-h-[75%] object-contain drop-shadow-md" />
                                            <button type="button" wire:click="removeLogo" class="absolute top-2 right-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 transition dark:bg-red-950/40 dark:text-red-300 dark:border-red-900/40">
                                                <flux:icon.trash class="size-2.5" /> {{ __('Remove') }}
                                            </button>
                                        @else
                                            <flux:icon.photo class="size-10 text-zinc-300 dark:text-neutral-600" />
                                            <p class="text-[10px] text-zinc-400 mt-2 uppercase tracking-widest font-semibold">{{ __('Preview') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button type="button" @click="open[2] = false; step = 3; open[3] = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white shadow-md shadow-indigo-500/25 hover:shadow-lg transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A) 30%;">
                                        {{ __('Save and Continue') }} <flux:icon.arrow-right class="size-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =========================================== --}}
                    {{-- STEP 3: Colors                                --}}
                    {{-- =========================================== --}}
                    <div class="rounded-2xl border overflow-hidden transition-all"
                         :class="open[3] ? 'border-violet-300 bg-white dark:border-violet-700/40 dark:bg-(--default-element-light-bg-color) shadow-sm shadow-violet-500/10' : 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-violet-200 dark:hover:border-violet-700/30'">
                        <button type="button" @click="toggle(3)" class="w-full flex items-center gap-3 p-5 text-left">
                            <span class="relative w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-violet-500/20 shadow-lg shadow-violet-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8 shrink-0">
                                <flux:icon.swatch class="size-4 text-violet-400" />                                
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Select Brand Colors') }}</div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __("Pick colors that define your brand's mood, style, and overall presence.") }}</div>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="w-5 h-5 rounded-full border border-zinc-200 shadow-sm" style="background: {{ $primary_color ?: '#6366f1' }};"></span>
                                <span class="w-5 h-5 rounded-full border border-zinc-200 shadow-sm" style="background: {{ $secondary_color ?: '#8b5cf6' }};"></span>
                                <span class="w-5 h-5 rounded-full border border-zinc-200 shadow-sm" style="background: {{ $accent_color ?: '#38bdf8' }};"></span>
                            </div>
                            <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform" x-bind:class="open[3] && 'rotate-180'" />
                        </button>
                        <div x-show="open[3]" x-collapse>
                            <div class="px-5 pb-5 border-t border-zinc-100 dark:border-white/8 pt-4 space-y-7">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @foreach([
                                        ['label' => __('Primary'), 'model' => 'primary_color'],
                                        ['label' => __('Secondary'), 'model' => 'secondary_color'],
                                        ['label' => __('Accent'), 'model' => 'accent_color'],
                                    ] as $c)
                                        <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-3 dark:border-white/8 dark:bg-neutral-900/50">
                                            <div class="text-[10px] uppercase tracking-widest font-bold text-zinc-500 dark:text-zinc-400 mb-2">{{ $c['label'] }}</div>
                                            <div class="flex items-center gap-2">
                                                <input type="color" wire:model.live="{{ $c['model'] }}" class="w-10 h-10 rounded-lg border border-zinc-200 bg-white cursor-pointer dark:border-white/8 dark:bg-(--default-element-bg-color)" />
                                                <input type="text" wire:model.live.debounce.300ms="{{ $c['model'] }}" maxlength="7" class="flex-1 px-2.5 py-2 rounded-lg bg-white border border-zinc-200 text-xs font-mono text-zinc-700 focus:outline-hidden focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/20 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-200" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Palette preset picker --}}
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest font-bold text-zinc-500 dark:text-zinc-400 mb-2">{{ __('Quick palettes') }}</div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                        @foreach([
                                            ['Indigo',    '#4f46e5','#7c3aed','#38bdf8'],
                                            ['Ocean',     '#0ea5e9','#0284c7','#22d3ee'],
                                            ['Emerald',   '#059669','#10b981','#84cc16'],
                                            ['Slate',     '#0f172a','#334155','#94a3b8'],
                                            ['Midnight',  '#1e293b','#4f46e5','#64748b'],
                                            ['Amber',     '#b45309','#d97706','#f59e0b'],
                                            ['Royal',     '#1e40af','#3b82f6','#93c5fd'],
                                            ['Mono',      '#18181b','#52525b','#a1a1aa'],
                                        ] as $p)
                                            <button type="button" wire:click="$set('primary_color', '{{ $p[1] }}')" @click="$wire.set('secondary_color', '{{ $p[2] }}'); $wire.set('accent_color', '{{ $p[3] }}')" class="group flex items-center gap-2 px-2.5 py-2 rounded-lg border border-zinc-200 bg-white hover:border-indigo-300 hover:bg-indigo-50/30 transition dark:border-white/8 dark:bg-(--default-element-bg-color) dark:hover:border-indigo-700/40 dark:hover:bg-indigo-950/20">
                                                <div class="flex -space-x-1">
                                                    <span class="w-4 h-4 rounded-full border border-white dark:border-neutral-900 shadow-sm" style="background: {{ $p[1] }};"></span>
                                                    <span class="w-4 h-4 rounded-full border border-white dark:border-neutral-900 shadow-sm" style="background: {{ $p[2] }};"></span>
                                                    <span class="w-4 h-4 rounded-full border border-white dark:border-neutral-900 shadow-sm" style="background: {{ $p[3] }};"></span>
                                                </div>
                                                <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-300">{{ $p[0] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="button" @click="open[3] = false; step = 4; open[4] = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white shadow-md shadow-indigo-500/25 hover:shadow-lg transition" style="background: linear-gradient(120deg, #4F46E5, #0F172A) 30%;">
                                        {{ __('Save and Continue') }} <flux:icon.arrow-right class="size-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- =========================================== --}}
                    {{-- STEP 4: Advanced                              --}}
                    {{-- =========================================== --}}
                    <div class="rounded-2xl border overflow-hidden transition-all"
                         :class="open[4] ? 'border-emerald-300 bg-white dark:border-emerald-700/40 dark:bg-(--default-element-light-bg-color) shadow-sm shadow-emerald-500/10' : 'border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-emerald-200 dark:hover:border-emerald-700/30'">
                        <button type="button" @click="toggle(4)" class="w-full flex items-center gap-3 p-5 text-left">
                            <span class="relative w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-emerald-500/20 shadow-lg shadow-emerald-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8 shrink-0">
                                <flux:icon.adjustments-horizontal class="size-4 text-emerald-400" />                                
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Advanced Setup') }}</div>
                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ __('Access advanced tools to customize your brand settings with more control.') }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest text-amber-600 bg-amber-50 border border-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/40">{{ __('Optional') }}</span>
                            <flux:icon.chevron-down class="size-4 text-zinc-400 transition-transform" x-bind:class="open[4] && 'rotate-180'" />
                        </button>
                        <div x-show="open[4]" x-collapse>
                            <div class="px-5 pb-5 border-t border-zinc-100 dark:border-white/8 pt-4 space-y-7">

                                {{-- Tone + Font --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:field>
                                        <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Tone of Voice') }}</flux:label>
                                        <flux:select wire:model="tone_of_voice">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach(['Professional','Friendly','Bold','Playful','Luxurious','Minimal','Inspirational','Witty','Urgent','Authoritative'] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                    <flux:field>
                                        <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Change Font') }}</flux:label>
                                        <flux:select wire:model="font_family">
                                            <option value="">{{ __('Default') }}</option>
                                            @foreach(['Inter','Poppins','Roboto','Montserrat','Playfair Display','Space Grotesk','Manrope','DM Serif','Lora'] as $opt)
                                                <option value="{{ $opt }}">{{ $opt }}</option>
                                            @endforeach
                                        </flux:select>
                                    </flux:field>
                                </div>

                                {{-- Target audience --}}
                                <flux:field>
                                    <flux:label class="text-[11px] font-semibold uppercase tracking-wider">{{ __('Target Audience') }}</flux:label>
                                    <flux:textarea wire:model="target_audience" rows="2" maxlength="500" placeholder="{{ __('Who is this brand for? Age, interests, pain points...') }}" />
                                </flux:field>

                                {{-- Brand values pills --}}
                                <div>
                                    <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2 block">{{ __('Brand Values') }}</label>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(['Trust','Innovation','Quality','Sustainability','Simplicity','Boldness','Care','Craft','Fun','Speed','Authenticity','Community','Luxury','Value'] as $val)
                                            @php $active = in_array($val, $brand_values); @endphp
                                            <button type="button" wire:click="toggleBrandValue('{{ $val }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border transition {{ $active ? 'bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-950/40 dark:border-indigo-700/40 dark:text-indigo-300' : 'bg-white border-zinc-200 text-zinc-500 hover:border-zinc-300 dark:bg-(--default-element-bg-color) dark:border-white/8 dark:text-zinc-400' }}">
                                                @if($active) <flux:icon.check class="size-3" /> @endif
                                                {{ $val }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Social handles --}}
                                <div>
                                    <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2 block">{{ __('Social Handles') }}</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach([
                                            'instagram' => 'Instagram',
                                            'tiktok' => 'TikTok',
                                            'linkedin' => 'LinkedIn',
                                            'facebook' => 'Facebook',
                                            'x' => 'X / Twitter',
                                            'youtube' => 'YouTube',
                                        ] as $handle => $label)
                                            <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-white border border-zinc-200 dark:bg-(--default-element-bg-color) dark:border-white/8">
                                                <span class="text-[11px] font-bold text-zinc-500 dark:text-zinc-400 w-16 shrink-0">{{ $label }}</span>
                                                <input type="text" wire:model="social_handles.{{ $handle }}" placeholder="@handle" class="flex-1 min-w-0 bg-transparent text-xs text-zinc-700 placeholder:text-zinc-400 focus:outline-hidden dark:text-zinc-200 dark:placeholder:text-zinc-600" />
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Ad platforms (Connect cards like mockup) --}}
                                <div>
                                    <label class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2 block">{{ __('Preferred Ad Platforms') }}</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach([
                                            'meta' => 'Meta Ads',
                                            'google' => 'Google Ads',
                                            'linkedin' => 'LinkedIn Ads',
                                            'tiktok' => 'TikTok Ads',
                                            'snapchat' => 'Snapchat Ads',
                                            'facebook' => 'Facebook Ads',
                                        ] as $key => $label)
                                            @php $active = in_array($key, $ad_platforms); @endphp
                                            <button type="button" wire:click="toggleAdPlatform('{{ $key }}')" class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg border transition text-left {{ $active ? 'bg-indigo-50 border-indigo-300 dark:bg-indigo-950/30 dark:border-indigo-700/40' : 'bg-white border-zinc-200 hover:border-zinc-300 dark:bg-(--default-element-bg-color) dark:border-white/8' }}">
                                                <div>
                                                    <div class="text-xs font-bold {{ $active ? 'text-indigo-700 dark:text-indigo-300' : 'text-zinc-800 dark:text-zinc-200' }}">{{ $label }}</div>
                                                    <div class="text-[10px] text-zinc-500">{{ $active ? __('Added to your mix') : __('Not yet in your mix') }}</div>
                                                </div>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $active ? 'text-emerald-700 bg-emerald-50 border border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900/40' : 'text-zinc-500 bg-zinc-50 border border-zinc-200 dark:bg-(--default-element-light-bg-color) dark:border-white/8' }}">
                                                    @if($active) <flux:icon.check class="size-2.5" /> {{ __('Added') }} @else <flux:icon.plus class="size-2.5" /> {{ __('Add') }} @endif
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Default / Active toggles --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                    <label class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg bg-white border border-zinc-200 hover:border-indigo-300 cursor-pointer transition dark:bg-(--default-element-bg-color) dark:border-white/8">
                                        <div>
                                            <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ __('Set as default brand') }}</div>
                                            <div class="text-[10px] text-zinc-500">{{ __('Used automatically for new generations') }}</div>
                                        </div>
                                        <input type="checkbox" wire:model="is_default" class="sr-only peer" />
                                        <div class="w-9 h-5 bg-zinc-200 rounded-full relative peer-checked:bg-linear-to-r peer-checked:from-indigo-500 peer-checked:to-violet-600 transition dark:bg-neutral-700">
                                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                                        </div>
                                    </label>
                                    <label class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg bg-white border border-zinc-200 hover:border-emerald-300 cursor-pointer transition dark:bg-(--default-element-bg-color) dark:border-white/8">
                                        <div>
                                            <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ __('Active') }}</div>
                                            <div class="text-[10px] text-zinc-500">{{ __('Available in studio brand picker') }}</div>
                                        </div>
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer" />
                                        <div class="w-9 h-5 bg-zinc-200 rounded-full relative peer-checked:bg-emerald-500 transition dark:bg-neutral-700">
                                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition peer-checked:translate-x-4"></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer CTAs --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-2">
                            <flux:icon.information-circle class="size-3.5 text-zinc-400" />
                            {{ __('All changes save together. You can come back and edit anytime.') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('user.brands.index') }}" wire:navigate class="text-xs text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-200 px-4 py-2 rounded-lg border border-zinc-200 hover:border-zinc-300 bg-white dark:bg-(--default-element-bg-color) dark:border-white/8 transition">{{ __('Cancel') }}</a>
                            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="group relative inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-sm text-white shadow-sm shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 transition overflow-hidden disabled:opacity-60" style="background: linear-gradient(120deg, #4F46E5, #0F172A) 30%;">
                                <span class="relative flex items-center gap-2" wire:loading.remove wire:target="save">
                                    <flux:icon.check-circle class="size-4" /> {{ $brand ? __('Update Brand') : __('Create Brand') }}
                                </span>
                                <span class="relative flex items-center gap-2" wire:loading wire:target="save">
                                    <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    {{ __('Saving...') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============== RIGHT RAIL PREVIEW ============== --}}
                <aside class="space-y-7">
                    {{-- Live preview card — uses the user's own brand colors --}}
                    <div class="rounded-2xl overflow-hidden border border-zinc-200 bg-white shadow-sm dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="relative h-56 overflow-hidden" style="background: linear-gradient(135deg, {{ $primary_color ?: '#6366f1' }}, {{ $secondary_color ?: '#8b5cf6' }});">
                            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.25), transparent 45%), radial-gradient(circle at 75% 70%, rgba(0,0,0,0.3), transparent 40%);"></div>
                            <div class="absolute inset-0" style="background-image: linear-gradient(rgba(255,255,255,0.07) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 30px 30px;"></div>
                            <div class="relative h-full flex flex-col items-center justify-center p-6 text-center">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" class="max-h-14 max-w-[70%] object-contain mb-3 drop-shadow-xl" />
                                @elseif($logo_path)
                                    <img src="{{ URL::asset($logo_path) }}" class="max-h-14 max-w-[70%] object-contain mb-3 drop-shadow-xl" />
                                @else
                                    <div class="inline-flex w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 items-center justify-center mb-3">
                                        <span class="text-xl font-extrabold text-white">{{ mb_strtoupper(mb_substr($name ?: 'B', 0, 1)) }}</span>
                                    </div>
                                @endif
                                <h3 class="text-lg font-extrabold text-white leading-tight" style="font-family: {{ $font_family ?: 'inherit' }}">{{ $name ?: __('Your brand') }}</h3>
                                @if($tagline)
                                    <p class="text-[11px] text-white/80 mt-1 max-w-[85%]">{{ $tagline }}</p>
                                @endif
                                @if($accent_color)
                                    <button type="button" class="mt-3 px-3 py-1.5 rounded-lg text-[11px] font-bold shadow-lg" style="background: {{ $accent_color }}; color: {{ $primary_color ?: '#fff' }};">{{ __('Get Started') }}</button>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 border-t border-zinc-100 dark:border-white/8">
                            @php $stepsDone = (int) round($this->completionScore / 25); @endphp
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] uppercase tracking-widest font-bold text-zinc-500 dark:text-zinc-400">{{ __('Brand setup') }}</span>
                                <span class="text-xs font-bold tabular-nums {{ $stepsDone === 4 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-300' }}">
                                    {{ $stepsDone }}<span class="text-zinc-400 font-medium">/4</span>
                                    <span class="text-[10px] text-zinc-500 ml-0.5 font-semibold">{{ __('steps') }}</span>
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                @foreach(['Name','Logo','Colors','Advanced'] as $idx => $label)
                                    @php $active = ($idx + 1) <= $stepsDone; @endphp
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <span class="w-full h-1.5 rounded-full transition-all {{ $active ? 'bg-linear-to-r from-indigo-500 to-violet-500' : 'bg-zinc-100 dark:bg-neutral-700' }}"></span>
                                        <span class="text-[9px] font-semibold uppercase tracking-wider {{ $active ? 'text-indigo-600 dark:text-indigo-300' : 'text-zinc-400' }}">{{ __($label) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-[10px] text-zinc-500 mt-3 leading-relaxed">{{ __('A complete brand gives the AI more context and produces dramatically better ads.') }}</p>
                        </div>
                    </div>

                    {{-- Tips --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-light-bg-color) p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="relative w-8 h-8 rounded-lg bg-zinc-900 border border-zinc-800 ring-1 ring-amber-500/20 shadow-lg shadow-amber-500/10 flex items-center justify-center dark:bg-neutral-950 dark:border-white/8">
                                <flux:icon.light-bulb class="size-4 text-amber-400" />                                
                            </span>
                            <h3 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Tips for better ads') }}</h3>
                        </div>
                        <ul class="space-y-2 text-[11px] text-zinc-600 dark:text-zinc-400">
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Describe your product in plain language — no buzzwords.') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Set a clear tone of voice — the AI mirrors it in copy.') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Pick a distinctive palette — signature colors drive recall.') }}</li>
                            <li class="flex items-start gap-2"><flux:icon.check-circle class="size-3.5 text-emerald-500 mt-0.5 shrink-0" /> {{ __('Name your audience specifically — "busy moms" beats "everyone".') }}</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>
