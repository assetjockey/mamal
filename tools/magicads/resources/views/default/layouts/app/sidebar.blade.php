<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ in_array(app()->getLocale(), config('app.rtl_locales', ['ar', 'he', 'fa', 'ur'])) ? 'rtl' : 'ltr' }}"
    class="dark"
>
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-linear-to-br from-indigo-600/10 to-transparent to-20% dark:bg-(--default-body-bg-color) dark:bg-none" data-theme="{{ \Igaster\LaravelTheme\Facades\Theme::current()->name }}">

        @php
            use Illuminate\Support\Facades\URL;
            use Illuminate\Support\Facades\Route;

            $settings = \App\Models\GeneralSetting::first();

            // Dashboard logo (what the sidebar shows). Gracefully falls back across:
            //   1. configured dashboard-* paths from GeneralSetting
            //   2. the frontend logos (still saner than a stock demo asset)
            //   3. the app name initials rendered by the brand slot below
            $logoLight       = $settings?->logo_dashboard_light       ? URL::asset($settings->logo_dashboard_light)       : null;
            $logoDark        = $settings?->logo_dashboard_dark        ? URL::asset($settings->logo_dashboard_dark)        : null;
            $logoCollapsed   = $settings?->logo_dashboard_collapsed_light ? URL::asset($settings->logo_dashboard_collapsed_light) : null;

            $authUser = auth()->user();
            $brandHref = $authUser?->hasRole('admin')
                ? route('admin.dashboard')
                : (Route::has('user.dashboard') ? route('user.dashboard') : url('/'));

            $appName = config('app.name', 'App');
            $appInitials = strtoupper(mb_substr($appName, 0, 1) . (str_contains($appName, ' ') ? mb_substr(explode(' ', $appName)[1] ?? '', 0, 1) : ''));
        @endphp

        <flux:sidebar sticky collapsible class="border-transparent dark:border-white/5 dark:bg-(--default-sidebar-bg-color) sidebar-thin-scrollbar max-lg:bg-white max-lg:dark:bg-(--default-sidebar-bg-color)">

            <flux:sidebar.header class="sidebar-header-blur">
                @if($logoLight || $logoDark)
                    <flux:sidebar.brand
                        :href="$brandHref"
                        :logo="$logoLight ?: $logoDark"
                        :logo:dark="$logoDark ?: $logoLight"
                        :alt="$appName"
                        :name="$appName"
                        wire:navigate
                    />
                @else
                    {{-- No logos configured — render a gradient-initial brand chip that matches the app palette --}}
                    <flux:sidebar.brand :href="$brandHref" :name="$appName" wire:navigate>
                        <span class="flex size-6 items-center justify-center rounded-md text-[10px] font-black text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A 60%, #F59E0B);">
                            {{ $appInitials }}
                        </span>
                    </flux:sidebar.brand>
                @endif

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="overflow-y-auto sidebar-thin-scrollbar">

                @role('user|subscriber|admin')

                    <flux:sidebar.item icon="layout-dashboard" tooltip="{{ __('Home') }}" :href="route('user.dashboard')" :current="request()->routeIs('user.dashboard')" wire:navigate>{{ __('Home') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="sparkles" tooltip="{{ __('Brand Kit') }}" :href="route('user.brands.index')" :current="request()->routeIs('user.brands.*')" wire:navigate>{{ __('Brand Kit') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="images" tooltip="{{ __('Ad Creatives') }}" :href="route('user.studio.gallery')" :current="request()->routeIs('user.studio.gallery')" wire:navigate>{{ __('Ad Creatives') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="book-open" tooltip="{{ __('Copy Library') }}" :href="route('user.copy.library')" :current="request()->routeIs('user.copy.library')" wire:navigate>{{ __('Copy Library') }}</flux:sidebar.item>

                    <x-layouts.sidebar-section :label="__('Creative Tools')">
                        {{-- Copy Studio: show as a live item when accessible, or as a
                             locked "upgrade to unlock" entry when it's offered on the
                             platform but not on the user's plan. Hidden only when the
                             feature is disabled platform-wide. --}}
                        @if (\App\Services\HelperService::accessCopyStudio())
                            <flux:sidebar.item icon="pencil" tooltip="{{ __('Copy Studio') }}" :href="route('user.copy.studio')" :current="request()->routeIs('user.copy.studio')" wire:navigate>{{ __('Copy Studio') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::studioLockedCopyStudio())
                            <flux:sidebar.item icon="pencil" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Copy Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Copy Studio') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::accessImageStudio())
                            <flux:sidebar.item icon="image-plus" tooltip="{{ __('Image Studio') }}" :href="route('user.studio.images')" :current="request()->routeIs('user.studio.images')" wire:navigate>{{ __('Image Studio') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::studioLockedImageStudio())
                            <flux:sidebar.item icon="image-plus" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Image Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Image Studio') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::accessVideoStudio())
                            <flux:sidebar.item icon="film" tooltip="{{ __('Video Studio') }}" :href="route('user.studio.videos')" :current="request()->routeIs('user.studio.videos')" wire:navigate>{{ __('Video Studio') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::studioLockedVideoStudio())
                            <flux:sidebar.item icon="film" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Video Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Video Studio') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::extensionFashionStudio())
                            <flux:sidebar.item icon="shirt" tooltip="{{ __('Fashion Studio') }}" :href="route('user.extension.fashion.studio')" :current="request()->routeIs('user.extension.fashion.studio*')" wire:navigate>{{ __('Fashion Studio') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::fashionStudioLocked())
                            <flux:sidebar.item icon="shirt" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Fashion Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Fashion Studio') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::extensionProductPhotoshoot())
                            <flux:sidebar.item icon="images" tooltip="{{ __('Product Photoshoot') }}" :href="route('user.extension.product.photoshoot')" :current="request()->routeIs('user.extension.product.photoshoot')" wire:navigate>{{ __('Product Photoshoot') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::productPhotoshootLocked())
                            <flux:sidebar.item icon="images" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Product Photoshoot — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Product Photoshoot') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::extensionAvatarStudio())
                            <flux:sidebar.item icon="user-circle" tooltip="{{ __('Avatar Studio') }}" :href="route('user.extension.avatar.studio')" :current="request()->routeIs('user.extension.avatar.studio*')" wire:navigate>{{ __('Avatar Studio') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::avatarStudioLocked())
                            <flux:sidebar.item icon="user-circle" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Avatar Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Avatar Studio') }}</flux:sidebar.item>
                        @endif
                        @if (\App\Services\HelperService::extensionUgcFactory())
                            <flux:sidebar.item icon="video-camera" tooltip="{{ __('UGC Factory') }}" :href="route('user.extension.ugc.factory')" :current="request()->routeIs('user.extension.ugc.factory*')" wire:navigate>{{ __('UGC Factory') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::ugcFactoryLocked())
                            <flux:sidebar.item icon="video-camera" icon:trailing="lock-closed" :current="false" tooltip="{{ __('UGC Factory — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('UGC Factory') }}</flux:sidebar.item>
                        @endif
                    </x-layouts.sidebar-section>

                    @if (\App\Services\HelperService::extensionSocialMediaStudio() || \App\Services\HelperService::socialMediaStudioLocked() || \App\Services\HelperService::extensionChannelBroadcast() || \App\Services\HelperService::channelBroadcastLocked())
                        <x-layouts.sidebar-section :label="__('Publishing')">
                            @if (\App\Services\HelperService::extensionSocialMediaStudio())
                                <flux:sidebar.item icon="share" tooltip="{{ __('Social Media Studio') }}" :href="route('user.extension.social.media.studio')" :current="request()->routeIs('user.extension.social.media.studio*')" wire:navigate>{{ __('Social Media Studio') }}</flux:sidebar.item>
                            @elseif (\App\Services\HelperService::socialMediaStudioLocked())
                                <flux:sidebar.item icon="share" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Social Media Studio — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Social Media Studio') }}</flux:sidebar.item>
                            @endif

                            @if (\App\Services\HelperService::extensionChannelBroadcast())
                                <flux:sidebar.item icon="megaphone" tooltip="{{ __('Channel Broadcast') }}" :href="route('user.extension.channel.broadcast')" :current="request()->routeIs('user.extension.channel.broadcast*')" wire:navigate>{{ __('Channel Broadcast') }}</flux:sidebar.item>
                            @elseif (\App\Services\HelperService::channelBroadcastLocked())
                                <flux:sidebar.item icon="megaphone" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Channel Broadcast — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Channel Broadcast') }}</flux:sidebar.item>
                            @endif
                        </x-layouts.sidebar-section>
                    @endif

                    @if (\App\Services\HelperService::extensionAdAnalytics() || \App\Services\HelperService::adAnalyticsLocked())
                        <x-layouts.sidebar-section :label="__('Analytics')">
                            @if (\App\Services\HelperService::extensionAdAnalytics())
                                <flux:sidebar.item icon="chart-bar" tooltip="{{ __('Ad Analytics') }}" :href="route('user.extension.ad.analytics')" :current="request()->routeIs('user.extension.ad.analytics*')" wire:navigate>{{ __('Ad Analytics') }}</flux:sidebar.item>
                            @elseif (\App\Services\HelperService::adAnalyticsLocked())
                                <flux:sidebar.item icon="chart-bar" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Ad Analytics — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Ad Analytics') }}</flux:sidebar.item>
                            @endif
                        </x-layouts.sidebar-section>
                    @endif

                    <x-layouts.sidebar-section :label="__('Workspace')">
                        <flux:sidebar.item icon="folder" tooltip="{{ __('Projects') }}" :href="route('user.projects.index')" :current="request()->routeIs('user.projects.*')" wire:navigate>{{ __('Projects') }}</flux:sidebar.item>
                        @if (\App\Services\HelperService::extensionTeam())
                            <flux:sidebar.item icon="users" tooltip="{{ __('Teams') }}" :href="route('user.team')" :current="request()->routeIs('user.team')" wire:navigate>{{ __('Teams') }}</flux:sidebar.item>
                        @elseif (\App\Services\HelperService::teamLocked())
                            <flux:sidebar.item icon="users" icon:trailing="lock-closed" :current="false" tooltip="{{ __('Teams — upgrade to unlock') }}" :href="\App\Services\HelperService::studioUpgradeUrl()" wire:navigate>{{ __('Teams') }}</flux:sidebar.item>
                        @endif
                    </x-layouts.sidebar-section>
                    

                    @if (\App\Services\HelperService::extensionSaaS())
                        <x-layouts.sidebar-section :label="__('Plans')">
                            <flux:sidebar.item icon="credit-card" tooltip="{{ __('Billing') }}" :href="route('user.billing')" :current="request()->routeIs('user.billing')" wire:navigate>{{ __('Billing') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="share-2" tooltip="{{ __('Referrals') }}" :href="route('user.affiliate')" :current="request()->routeIs('user.affiliate')" wire:navigate>{{ __('Referrals') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    @if (\App\Services\HelperService::extensionPromptMarketplace())
                        <x-layouts.sidebar-section :label="__('Marketplace')">
                            <flux:sidebar.item icon="store" tooltip="{{ __('Prompt Marketplace') }}" :href="route('user.marketplace')" :current="request()->routeIs('user.marketplace*')" wire:navigate>{{ __('Prompt Marketplace') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    <x-layouts.sidebar-section :label="__('Help')">
                        <flux:sidebar.item icon="message-circle-question-mark" tooltip="{{ __('Support') }}" :href="route('user.support')" :current="request()->routeIs('user.support')" wire:navigate>{{ __('Support') }}</flux:sidebar.item>
                    </x-layouts.sidebar-section>

                @endrole

                @role('admin')

                    <flux:sidebar.item icon="layout-dashboard" tooltip="{{ __('Admin Dashboard') }}" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>

                    @if (\App\Services\HelperService::extensionSaaS())
                        <x-layouts.sidebar-section :label="__('Finance')">
                            <flux:sidebar.item icon="presentation-chart-bar" tooltip="{{ __('Finance Dashboard') }}" :href="route('admin.finance.dashboard')" :current="request()->routeIs('admin.finance.dashboard')" wire:navigate>{{ __('Finance Dashboard') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="package" tooltip="{{ __('Pricing Plans') }}" :href="route('admin.finance.plans')" :current="request()->routeIs('admin.finance.plans')" wire:navigate>{{ __('Pricing Plans') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="badge-dollar-sign" tooltip="{{ __('Orders') }}" :href="route('admin.finance.orders')" :current="request()->routeIs('admin.finance.orders')" wire:navigate>{{ __('Orders') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="refresh-cw" tooltip="{{ __('Subscriptions') }}" :href="route('admin.finance.subscriptions')" :current="request()->routeIs('admin.finance.subscriptions')" wire:navigate>{{ __('Subscriptions') }}</flux:sidebar.item>
                            @if (\App\Services\HelperService::extensionCheckCoupons())
                                <flux:sidebar.item icon="ticket" tooltip="{{ __('Coupons') }}" :href="route('admin.coupons.index')" :current="request()->routeIs('admin.coupons.*')" wire:navigate>{{ __('Coupons') }}</flux:sidebar.item>
                            @endif
                            <flux:sidebar.item icon="receipt-text" tooltip="{{ __('Invoice Settings') }}" :href="route('admin.finance.invoices')" :current="request()->routeIs('admin.finance.invoices')" wire:navigate>{{ __('Invoice Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="banknotes" tooltip="{{ __('Payment Gateways') }}" :href="route('admin.finance.gateways')" :current="request()->routeIs('admin.finance.gateways')" wire:navigate>{{ __('Payment Gateways') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="adjustments-horizontal" tooltip="{{ __('Finance Settings') }}" :href="route('admin.finance.settings')" :current="request()->routeIs('admin.finance.settings')" wire:navigate>{{ __('Finance Settings') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    @if (\App\Services\HelperService::extensionCheckGiftCards())
                        <x-layouts.sidebar-section :label="__('Gift Cards')">
                            <flux:sidebar.item icon="gift" tooltip="{{ __('Gift Cards') }}" :href="route('admin.giftcards.index')" :current="request()->routeIs('admin.giftcards.*')" wire:navigate>{{ __('Gift Cards') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    @if (\App\Services\HelperService::extensionSaaS())
                        <x-layouts.sidebar-section :label="__('Affiliates')">
                            <flux:sidebar.item icon="share-2" tooltip="{{ __('Affiliate Accounts') }}" :href="route('admin.affiliate.accounts')" :current="request()->routeIs('admin.affiliate.accounts')" wire:navigate>{{ __('Accounts') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="banknote-arrow-up" tooltip="{{ __('Earnings') }}" :href="route('admin.affiliate.earnings')" :current="request()->routeIs('admin.affiliate.earnings')" wire:navigate>{{ __('Earnings') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="banknote-arrow-down" tooltip="{{ __('Payouts') }}" :href="route('admin.affiliate.payouts')" :current="request()->routeIs('admin.affiliate.payouts')" wire:navigate>{{ __('Payouts') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    @if (\App\Services\HelperService::extensionPromptMarketplace())
                        <x-layouts.sidebar-section :label="__('Marketplace')">
                            <flux:sidebar.item icon="store" tooltip="{{ __('Marketplace Dashboard') }}" :href="route('admin.marketplace.dashboard')" :current="request()->routeIs('admin.marketplace.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="tag" tooltip="{{ __('Listings') }}" :href="route('admin.marketplace.listings')" :current="request()->routeIs('admin.marketplace.listings')" wire:navigate>{{ __('Listings') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="receipt-text" tooltip="{{ __('Transactions') }}" :href="route('admin.marketplace.transactions')" :current="request()->routeIs('admin.marketplace.transactions')" wire:navigate>{{ __('Transactions') }}</flux:sidebar.item>
                            <flux:sidebar.item icon="banknote-arrow-down" tooltip="{{ __('Withdrawals') }}" :href="route('admin.marketplace.withdrawals')" :current="request()->routeIs('admin.marketplace.withdrawals')" wire:navigate>{{ __('Withdrawals') }}</flux:sidebar.item>
                        </x-layouts.sidebar-section>
                    @endif

                    <x-layouts.sidebar-section :label="__('Accounts')">
                        <flux:sidebar.item icon="user-group" tooltip="{{ __('Accounts Dashboard') }}" :href="route('admin.accounts.dashboard')" :current="request()->routeIs('admin.accounts.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="users" tooltip="{{ __('Users') }}" :href="route('admin.accounts.list')" :current="request()->routeIs('admin.accounts.list')" wire:navigate>{{ __('Users') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="square-activity" tooltip="{{ __('Activity Monitoring') }}" :href="route('admin.accounts.activity')" :current="request()->routeIs('admin.accounts.activity')" wire:navigate>{{ __('Activity Monitoring') }}</flux:sidebar.item>
                    </x-layouts.sidebar-section>

                    <x-layouts.sidebar-section :label="__('Support')">
                        <flux:sidebar.item icon="clipboard-document-check" tooltip="{{ __('Tickets') }}" :href="route('admin.support.tickets')" :current="request()->routeIs('admin.support.tickets')" wire:navigate>{{ __('Tickets') }}</flux:sidebar.item>
                    </x-layouts.sidebar-section>

                    <x-layouts.sidebar-section :label="__('Notifications')">
                        <flux:sidebar.item icon="bell-alert" tooltip="{{ __('System Notification') }}" :href="route('admin.notifications.system')" :current="request()->routeIs('admin.notifications.system')" wire:navigate>{{ __('System Notification') }}</flux:sidebar.item>
                    </x-layouts.sidebar-section>

                    <x-layouts.sidebar-section :label="__('Platform')">
                        <flux:sidebar.item icon="squares-plus" tooltip="{{ __('Plugins') }}" :href="route('admin.plugins')" :current="request()->routeIs('admin.plugins')" wire:navigate>{{ __('Plugins') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="palette" tooltip="{{ __('Themes') }}" :href="route('admin.themes')" :current="request()->routeIs('admin.themes')" wire:navigate>{{ __('Themes') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="brain-circuit" tooltip="{{ __('AI Settings') }}" :href="route('admin.ai')" :current="request()->routeIs('admin.ai')" wire:navigate>{{ __('AI Settings') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="cog-6-tooth" tooltip="{{ __('General Settings') }}" :href="route('admin.general')" :current="request()->routeIs('admin.general')" wire:navigate>{{ __('General Settings') }}</flux:sidebar.item>
                        <flux:sidebar.group expandable expanded="false" icon="square-3-stack-3d" heading="{{ __('Backend Settings') }}" class="grid">
                            <flux:sidebar.item :href="route('admin.backend.global')" :current="request()->routeIs('admin.backend.global')" wire:navigate>{{ __('Global Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.backend.auth')" :current="request()->routeIs('admin.backend.auth')" wire:navigate>{{ __('Auth Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.backend.registration')" :current="request()->routeIs('admin.backend.registration')" wire:navigate>{{ __('Registration Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.backend.smtp')" :current="request()->routeIs('admin.backend.smtp')" wire:navigate>{{ __('SMTP Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.backend.gdpr')" :current="request()->routeIs('admin.backend.gdpr')" wire:navigate>{{ __('GDPR Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('elseyyid.translations.home2')" :current="request()->routeIs('elseyyid.translations.home2')" wire:navigate>{{ __('Language Manager') }}</flux:sidebar.item>
                        </flux:sidebar.group>
                        <flux:sidebar.group expandable expanded="false" icon="rectangle-stack" heading="{{ __('Frontend Settings') }}" class="grid">
                            <flux:sidebar.item :href="route('admin.frontend.settings')" :current="request()->routeIs('admin.frontend.settings')" wire:navigate>{{ __('Frontend Settings') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.seo')" :current="request()->routeIs('admin.frontend.seo')" wire:navigate>{{ __('SEO Manager') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.logos')" :current="request()->routeIs('admin.frontend.logos')" wire:navigate>{{ __('Logos') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.pages')" :current="request()->routeIs('admin.frontend.pages')" wire:navigate>{{ __('Pages') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.faqs')" :current="request()->routeIs('admin.frontend.faqs')" wire:navigate>{{ __('FAQs Manager') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.testimonials')" :current="request()->routeIs('admin.frontend.testimonials')" wire:navigate>{{ __('Testimonials') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.blogs')" :current="request()->routeIs('admin.frontend.blogs') || request()->routeIs('admin.frontend.blogs.*') || request()->routeIs('admin.frontend.blog-comments')" wire:navigate>{{ __('Blogs Manager') }}</flux:sidebar.item>
                            <flux:sidebar.item :href="route('admin.frontend.adsense')" :current="request()->routeIs('admin.frontend.adsense')" wire:navigate>{{ __('Google Adsense') }}</flux:sidebar.item>
                        </flux:sidebar.group>
                        {{-- <flux:sidebar.item icon="server-stack" tooltip="{{ __('System Status') }}" :href="route('admin.status')" :current="request()->routeIs('admin.status')" wire:navigate>{{ __('System Status') }}</flux:sidebar.item> --}}
                        <flux:sidebar.item icon="key" tooltip="{{ __('Activation') }}" :href="route('admin.activation')" :current="request()->routeIs('admin.activation')" wire:navigate>{{ __('Activation') }}</flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-path" tooltip="{{ __('Update') }}" :href="route('admin.update')" :current="request()->routeIs('admin.update')" wire:navigate>{{ __('Update') }}</flux:sidebar.item>
                    </x-layouts.sidebar-section>

                @endrole

            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            {{-- Plan / credits card — sits just above the profile footer --}}
            @auth
                <x-layouts.plan-card />
            @endauth

            {{-- Desktop profile footer --}}
            @auth
                <div class="hidden lg:block">
                    <x-layouts.user-menu position="top" align="start" />
                </div>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        @auth
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <x-layouts.user-menu position="top" align="end" />
        </flux:header>
        @endauth

        {{ $slot }}

        <x-toaster-hub />

        @filamentScripts
        @fluxScripts
        @stack('scripts')
    </body>
</html>
