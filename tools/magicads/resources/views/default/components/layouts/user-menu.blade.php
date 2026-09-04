@props([
    'position' => 'bottom',
    'align' => 'start',
])

@php
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Facades\Route;
    use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

    $user = auth()->user();
    if (! $user) return;

    $avatar = $user->avatar ? URL::asset($user->avatar) : null;
    $initials = method_exists($user, 'initials') ? $user->initials() : strtoupper(mb_substr($user->name, 0, 2));

    // Plan label from active subscription
    $sub = $user->activeSubscription()->with('plan')->first();
    $planLabel = $sub?->plan?->name ?? __('Free');
    $planRenewalDate = $sub?->active_until?->format('M j, Y');

    $hasTwoFactor = Route::has('two-factor.show');
    $hasPassword = Route::has('user-password.edit');

    // Locale switcher data — driven by mcamara/laravel-localization, but
    // gated by `general_settings.languages` so the dropdown only shows the
    // locales an admin has explicitly enabled in the language manager.
    //
    // Selecting a language navigates to the same path under the new locale
    // prefix; the `localeSessionRedirect` middleware (mounted on every
    // dashboard route group) then persists that choice into the session,
    // so the user's pick survives across requests automatically.
    $supportedLocales = LaravelLocalization::getSupportedLocales();
    $currentLocale    = LaravelLocalization::getCurrentLocale();
    $generalSettings  = \Illuminate\Support\Facades\Schema::hasTable('general_settings')
        ? \App\Models\GeneralSetting::query()->select('languages')->first()
        : null;
    $enabledLocaleCodes = collect(explode(',', (string) ($generalSettings->languages ?? '')))
        ->map(fn ($code) => trim($code))
        ->filter()
        ->all();
    // Always keep the active locale visible even if it somehow isn't in the
    // enabled set — otherwise the user would be stuck without a way to
    // switch back.
    if (! in_array($currentLocale, $enabledLocaleCodes, true)) {
        $enabledLocaleCodes[] = $currentLocale;
    }
    $localeOptions = collect($supportedLocales)
        ->only($enabledLocaleCodes)
        ->all();
    $hasLocaleSwitch  = count($localeOptions) > 1;
    $currentLocaleNative = ucfirst($supportedLocales[$currentLocale]['native'] ?? $currentLocale);
@endphp

<flux:dropdown position="{{ $position }}" align="{{ $align }}">
    {{-- Trigger: shared compact profile block --}}
    <button
        type="button"
        class="group relative flex w-full items-center gap-3 rounded-lg px-3 py-2 text-start transition hover:bg-zinc-100 dark:hover:bg-white/5 in-data-flux-sidebar-collapsed-desktop:justify-center in-data-flux-sidebar-collapsed-desktop:gap-0 in-data-flux-sidebar-collapsed-desktop:px-0"
        data-test="sidebar-menu-button"
    >
        {{-- Avatar with online dot --}}
        <span class="relative shrink-0">
            @if($avatar)
                <img src="{{ $avatar }}" alt="{{ $user->name }}" class="size-9 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-white/10" />
            @else
                <span class="flex size-9 items-center justify-center rounded-lg text-xs font-bold text-white ring-1 ring-white/10"
                      style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                    {{ $initials }}
                </span>
            @endif
        </span>

        {{-- Name --}}
        <span class="grid flex-1 min-w-0 text-start leading-tight in-data-flux-sidebar-collapsed-desktop:hidden">
            <span class="truncate text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ $user->name }}</span>
           <span class="truncate text-[10px] text-zinc-400">{{ $user->email }}</span>
        </span>

        <flux:icon.chevrons-up-down class="size-3.5 text-zinc-400 shrink-0 group-hover:text-zinc-600 dark:group-hover:text-zinc-200 transition in-data-flux-sidebar-collapsed-desktop:hidden" />
    </button>

    {{-- Dropdown menu --}}
    <flux:menu class="w-[260px]">
        {{-- Header row: avatar + name + plan --}}
        <div class="p-2">
            <div class="flex items-center gap-3">
                <span class="relative shrink-0">
                    @if($avatar)
                        <img src="{{ $avatar }}" alt="{{ $user->name }}" class="size-10 rounded-lg object-cover ring-1 ring-zinc-200 dark:ring-white/10" />
                    @else
                        <span class="flex size-10 items-center justify-center rounded-lg text-sm font-bold text-white"
                              style="background: linear-gradient(120deg, #4F46E5, #0F172A);">
                            {{ $initials }}
                        </span>
                    @endif
                </span>
                <div class="flex-1 min-w-0">
                    <div class="truncate text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">{{ $user->name }}</div>
                    <div class="truncate text-[11px] text-zinc-500">{{ $user->email }}</div>
                </div>
            </div>

            {{-- Plan chip --}}
            <div class="mt-2 flex items-center justify-between gap-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-500/10 px-2.5 py-1.5">
                <div class="flex items-center gap-1.5 min-w-0">
                    <flux:icon.sparkles class="size-3.5 text-indigo-600 dark:text-indigo-400 shrink-0" />
                    <span class="truncate text-[11px] font-semibold text-indigo-700 dark:text-indigo-300">{{ $planLabel }} {{ __('plan') }}</span>
                </div>
                @if($planRenewalDate)
                    <span class="text-[9px] text-indigo-600/70 dark:text-indigo-400/80 shrink-0">{{ __('renews') }} {{ $planRenewalDate }}</span>
                @endif
            </div>
        </div>

        <flux:menu.separator variant="subtle" />

        {{-- Appearance — inline icon-only segmented toggle, sits directly under
             the plan chip. Icon-only keeps all three options inside the narrow
             260px menu without truncation; each carries an aria-label. --}}
        <div class="px-2.5 py-2" x-data>
            <div class="mb-1.5 flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                <flux:icon.sun class="size-3.5" /> {{ __('Appearance') }}
            </div>
            <flux:radio.group variant="segmented" size="sm" x-model="$flux.appearance" class="w-full *:flex-1">
                <flux:radio value="light" icon="sun" :aria-label="__('Light')" />
                <flux:radio value="dark" icon="moon" :aria-label="__('Dark')" />
                <flux:radio value="system" icon="computer-desktop" :aria-label="__('System')" />
            </flux:radio.group>
        </div>

        <flux:menu.separator variant="subtle" />

        {{-- Language switcher — renders as a Flux submenu listing every locale
             configured via mcamara/laravel-localization. Each entry links to the
             same page on the chosen locale (LaravelLocalization::getLocalizedURL
             keeps route params and query string intact). --}}
        @if($hasLocaleSwitch)
            <flux:menu.submenu icon="language" :heading="__('Language') . ': ' . $currentLocaleNative">
                @foreach($localeOptions as $localeCode => $properties)
                    <flux:menu.item
                        :href="LaravelLocalization::getLocalizedURL($localeCode, null, [], true)"
                        :icon="$localeCode === $currentLocale ? 'check' : null"
                    >
                        {{ ucfirst($properties['native'] ?? $properties['name'] ?? $localeCode) }}
                        <span class="ms-2 text-[10px] uppercase tracking-wide text-zinc-400">{{ $localeCode }}</span>
                    </flux:menu.item>
                @endforeach
            </flux:menu.submenu>

            <flux:menu.separator variant="subtle" />
        @endif

        {{-- Settings group --}}
        <flux:menu.item :href="route('profile.edit')" icon="user-circle" wire:navigate>{{ __('Profile') }}</flux:menu.item>

        {{-- Security — Password + Two-factor live together behind one entry.
             Both are dedicated, security-sensitive routes (2FA sits behind the
             password-confirm gate), so we group rather than fold them into the
             profile hub. --}}
        @if($hasPassword || $hasTwoFactor)
            <flux:menu.submenu icon="shield-check" :heading="__('Security')">
                @if($hasPassword)
                    <flux:menu.item :href="route('user-password.edit')" icon="lock-closed" wire:navigate>{{ __('Password') }}</flux:menu.item>
                @endif
                @if($hasTwoFactor)
                    <flux:menu.item :href="route('two-factor.show')" icon="key" wire:navigate>{{ __('Two-factor auth') }}</flux:menu.item>
                @endif
            </flux:menu.submenu>
        @endif

        <flux:menu.separator variant="subtle" />

        {{-- Log out — native form submit, with a low-friction confirm on desktop only --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full" onsubmit="return confirm('{{ __('Log out of your account?') }}')">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer text-rose-600 dark:text-rose-400"
                data-test="logout-button"
            >
                {{ __('Log Out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
