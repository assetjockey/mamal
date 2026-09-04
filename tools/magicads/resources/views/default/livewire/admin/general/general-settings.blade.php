<div>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <div class="flex justify-center">
        <div class="w-full xl:w-10/12"
             x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'general' }"
             x-init="$watch('tab', value => {
                const url = new URL(window.location);
                url.searchParams.set('tab', value);
                window.history.replaceState({}, '', url);
             })">

            {{-- Breadcrumbs --}}
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}" separator="slash" class="text-xs">{{ __('Admin') }}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{ __('General Settings') }}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-8">
                <h1 class="font-bold md:text-2xl">{{ __('General Settings') }}</h1>
                <flux:subheading size="sm" class="mb-6 md:text-sm">{{ __('Manage general configuration and plugin behavior') }}</flux:subheading>
            </div>

            {{-- ============================================================
                 Tabs (client-side switching — no server round-trip)
                 ============================================================ --}}
            <div class="mb-6 border-b border-zinc-200 dark:border-white/8">
                <nav class="-mb-px flex gap-1" aria-label="{{ __('General Settings tabs') }}">
                    <button type="button" x-on:click="tab = 'general'"
                            x-bind:class="tab === 'general'
                                ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors cursor-pointer">
                        <flux:icon.cog-6-tooth class="size-4" />
                        {{ __('General') }}
                    </button>

                    <button type="button" x-on:click="tab = 'plugins'"
                            x-bind:class="tab === 'plugins'
                                ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors cursor-pointer">
                        <flux:icon.squares-plus class="size-4" />
                        {{ __('Plugins') }}
                    </button>

                    <button type="button" x-on:click="tab = 'prompts'"
                            x-bind:class="tab === 'prompts'
                                ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                                : 'border-transparent text-zinc-500 hover:text-zinc-800 hover:border-zinc-300 dark:text-zinc-400 dark:hover:text-zinc-200'"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors cursor-pointer">
                        <flux:icon.book-open class="size-4" />
                        {{ __('Prompts') }}
                    </button>
                </nav>
            </div>

            {{-- ============================================================
                 General tab
                 ============================================================ --}}
            <div x-show="tab === 'general'" x-cloak>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 md:p-10 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:icon.cog-6-tooth class="size-4 text-zinc-400" />
                        <h2 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('General') }}</h2>
                    </div>
                    <p class="text-[11px] text-zinc-400 mb-6">{{ __('General configuration options') }}</p>

                    <div class="md:border border-(--default-border-color) md:p-5 rounded-xl dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <flux:field>
                            <flux:label>{{ __('Default Registration Credits') }}</flux:label>
                            <flux:description><small>{{ __('Number of credits granted to a user automatically when they register for the first time') }}</small></flux:description>
                            <flux:input type="number" min="0" step="1" wire:model="default_credits" class="mt-2 md:w-1/3" />
                            <flux:error name="default_credits" />
                        </flux:field>
                    </div>

                    <div class="md:border border-(--default-border-color) md:p-5 rounded-xl mt-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <flux:field>
                            <flux:label>{{ __('Default Storage') }}</flux:label>
                            <flux:description><small>{{ __('Where new Image Studio and Video Studio results are stored. "Local server" keeps files on this machine. Enabled cloud storage plugins (Amazon S3, Wasabi, …) appear here once configured.') }}</small></flux:description>
                            <flux:select wire:model="default_storage" class="mt-2 md:w-1/3">
                                @foreach ($storageOptions as $value => $label)
                                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="default_storage" />
                            @if (count($storageOptions) === 1)
                                <flux:description class="mt-2"><small>{{ __('No cloud storage plugins are enabled yet. Install and enable a storage plugin (e.g. Amazon S3 or Wasabi) under the Plugins tab to add more options here.') }}</small></flux:description>
                            @endif
                        </flux:field>
                    </div>

                    <div class="md:border border-(--default-border-color) md:p-5 rounded-xl mt-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <flux:field>
                            <flux:label>{{ __('Free-Tier Project Limit') }}</flux:label>
                            <flux:description><small>{{ __('Maximum number of projects a user without an active subscription may create. Set to 0 to block project creation for free-tier users.') }}</small></flux:description>
                            <flux:input type="number" min="0" max="1000" step="1" wire:model="free_tier_project_limit" class="mt-2 md:w-1/3" />
                            <flux:error name="free_tier_project_limit" />
                        </flux:field>
                    </div>

                    @if (\App\Services\HelperService::extensionCheckTeam())
                        <div class="md:border border-(--default-border-color) md:p-5 rounded-xl mt-4 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                            <flux:field>
                                <flux:label>{{ __('Free-Tier Team Members') }}</flux:label>
                                <flux:description><small>{{ __('Maximum members (excluding the owner) a free-tier user can invite to their team. Set to 0 to block team creation for free-tier users. Subscribers are capped by their plan.') }}</small></flux:description>
                                <flux:input type="number" min="0" max="1000" step="1" wire:model="free_tier_team_members" class="mt-2 md:w-1/3" />
                                <flux:error name="free_tier_team_members" />
                            </flux:field>
                        </div>
                    @endif

                    <div class="flex w-full justify-center mt-8">
                        <flux:button wire:click="save" variant="primary" class="md:w-1/2 w-full hover:bg-(--default-primary-color) py-6 rounded-xl cursor-pointer">{{ __('Save') }}</flux:button>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 Plugins tab
                 ============================================================ --}}
            <div x-show="tab === 'plugins'" x-cloak>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 md:p-10 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                    <div class="flex items-center gap-2 mb-1">
                        <flux:icon.squares-plus class="size-4 text-zinc-400" />
                        <h2 class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ __('Plugins') }}</h2>
                    </div>
                    <p class="text-[11px] text-zinc-400 mb-6">{{ __('Configure plugin behavior') }}</p>

                    @php
                        $hasPlugins = \App\Services\HelperService::extensionCheckSaaS()
                            || \App\Services\HelperService::extensionCheckFashionStudio()
                            || \App\Services\HelperService::extensionCheckProductPhotoshoot()
                            || \App\Services\HelperService::extensionCheckAvatarStudio()
                            || \App\Services\HelperService::extensionCheckUgcFactory()
                            || \App\Services\HelperService::extensionCheckSocialMediaStudio()
                            || \App\Services\HelperService::extensionCheckTeam()
                            || \App\Services\HelperService::extensionCheckAmazonS3()
                            || \App\Services\HelperService::extensionCheckWasabi()
                            || \App\Services\HelperService::extensionCheckCloudflareR2()
                            || \App\Services\HelperService::extensionCheckGoogleCloudStorage()
                            || \App\Services\HelperService::extensionCheckGiftCards()
                            || \App\Services\HelperService::extensionCheckCoupons();
                    @endphp

                    @if ($hasPlugins)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                            {{-- SaaS Business plugin --}}
                            @if (\App\Services\HelperService::extensionCheckSaaS())
                                <a href="{{ route('admin.general.plugins.saas') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.building-storefront class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('SaaS Business') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('SaaS Feature Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Prompt Marketplace plugin --}}
                            @if (\App\Services\HelperService::extensionCheckPromptMarketplace())
                                <a href="{{ route('admin.general.plugins.prompt-marketplace') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.shopping-bag class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Prompt Marketplace') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Enable selling & set platform commission') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Gift Cards plugin --}}
                            @if (\App\Services\HelperService::extensionCheckGiftCards())
                                <a href="{{ route('admin.general.plugins.giftcards') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.gift class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Gift Cards') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Gift Cards Feature Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Coupons plugin --}}
                            @if (\App\Services\HelperService::extensionCheckCoupons())
                                <a href="{{ route('admin.general.plugins.coupons') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.ticket class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Coupons') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Coupons Feature Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Fashion Studio plugin --}}
                            @if (\App\Services\HelperService::extensionCheckFashionStudio())
                                <a href="{{ route('admin.general.plugins.fashion-studio') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.shirt class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Fashion Studio') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('AI Fashion Studio Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Product Photoshoot plugin --}}
                            @if (\App\Services\HelperService::extensionCheckProductPhotoshoot())
                                <a href="{{ route('admin.general.plugins.product-photoshoot') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.camera class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Product Photoshoot') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('AI Product Photoshoot Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Avatar Studio plugin --}}
                            @if (\App\Services\HelperService::extensionCheckAvatarStudio())
                                <a href="{{ route('admin.general.plugins.avatar-studio') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.user-circle class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Avatar Studio') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('HeyGen Avatar Studio Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- UGC Factory plugin --}}
                            @if (\App\Services\HelperService::extensionCheckUgcFactory())
                                <a href="{{ route('admin.general.plugins.ugc-factory') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.video-camera class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('UGC Factory') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('fal.ai UGC Factory Configuration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Social Media Studio plugin --}}
                            @if (\App\Services\HelperService::extensionCheckSocialMediaStudio())
                                <a href="{{ route('admin.general.plugins.social-media-studio') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.share class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Social Media Studio') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Connections, platforms & publishing') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Channel Broadcast plugin --}}
                            @if (\App\Services\HelperService::extensionCheckChannelBroadcast())
                                <a href="{{ route('admin.general.plugins.channel-broadcast') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.megaphone class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Channel Broadcast') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('WhatsApp, Telegram, Slack, Messenger & Email') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Ad Performance Analytics plugin --}}
                            @if (\App\Services\HelperService::extensionCheckAdAnalytics())
                                <a href="{{ route('admin.general.plugins.ad-analytics') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.chart-bar class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Ad Performance Analytics') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Meta, Google Ads & TikTok ROAS, creative attribution & AI insights') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Team plugin --}}
                            @if (\App\Services\HelperService::extensionCheckTeam())
                                <a href="{{ route('admin.general.plugins.team') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.users class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Teams') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Team seats, credit sharing & collaboration') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Amazon S3 Storage plugin --}}
                            @if (\App\Services\HelperService::extensionCheckAmazonS3())
                                <a href="{{ route('admin.general.plugins.amazon-s3') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.cloud-arrow-up class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Amazon S3 Storage') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Offload studio results to your bucket') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Wasabi Storage plugin --}}
                            @if (\App\Services\HelperService::extensionCheckWasabi())
                                <a href="{{ route('admin.general.plugins.wasabi') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.cloud class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Wasabi Storage') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Offload studio results to your bucket') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Cloudflare R2 Storage plugin --}}
                            @if (\App\Services\HelperService::extensionCheckCloudflareR2())
                                <a href="{{ route('admin.general.plugins.cloudflare-r2') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.cloud-arrow-up class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Cloudflare R2 Storage') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Offload studio results to your bucket') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                            {{-- Google Cloud Storage plugin --}}
                            @if (\App\Services\HelperService::extensionCheckGoogleCloudStorage())
                                <a href="{{ route('admin.general.plugins.google-cloud-storage') }}" wire:navigate
                                   class="group flex items-center gap-4 rounded-2xl border border-zinc-200 bg-white p-5 transition-all hover:shadow-md
                                          dark:border-white/8 dark:bg-(--default-element-light-bg-color) hover:border-indigo-300 dark:hover:border-indigo-700/50">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-zinc-100 text-zinc-700 dark:bg-white/10 dark:text-zinc-200">
                                        <flux:icon.cloud class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h6 class="text-[15px] font-bold text-zinc-900 dark:text-zinc-100 truncate">{{ __('Google Cloud Storage') }}</h6>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate">{{ __('Offload studio results to your bucket') }}</p>
                                    </div>
                                    <flux:icon.chevron-right class="size-4 text-zinc-300 ml-auto shrink-0 group-hover:text-indigo-500 transition-colors" />
                                </a>
                            @endif

                        </div>
                    @else
                        <flux:callout icon="information-circle" class="text-sm">
                            {{ __('No plugins installed yet.') }}
                        </flux:callout>
                    @endif
                </div>
            </div>

            {{-- ============================================================
                 Prompts tab
                 ============================================================ --}}
            <div x-show="tab === 'prompts'" x-cloak>
                <livewire:admin.general.prompts-manager />
            </div>

        </div>
    </div>
</div>
