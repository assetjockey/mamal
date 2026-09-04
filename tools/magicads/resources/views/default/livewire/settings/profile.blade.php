@php
    use Illuminate\Support\Str;

    $user = auth()->user();

    $sub      = $this->subscription;
    $metrics  = $this->metrics;
    $countries = config('countries', []);

    $firstName = Str::before($user->name, ' ') ?: $user->name;

    // Status pill styling for the subscription state (semantic only — no brand palette).
    $statusStyles = [
        'active'    => ['label' => __('Subscribed'), 'class' => 'text-emerald-700 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-500/10 ring-emerald-600/20'],
        'pending'   => ['label' => __('Pending'),    'class' => 'text-amber-700 bg-amber-50 dark:text-amber-300 dark:bg-amber-500/10 ring-amber-600/20'],
        'cancelled' => ['label' => __('Cancelled'),  'class' => 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-500/10 ring-rose-600/20'],
        'ended'     => ['label' => __('Ended'),      'class' => 'text-zinc-600 bg-zinc-100 dark:text-zinc-300 dark:bg-white/5 ring-zinc-500/20'],
        'deactive'  => ['label' => __('Inactive'),   'class' => 'text-zinc-600 bg-zinc-100 dark:text-zinc-300 dark:bg-white/5 ring-zinc-500/20'],
        'free'      => ['label' => __('Free plan'),  'class' => 'text-zinc-600 bg-zinc-100 dark:text-zinc-300 dark:bg-white/5 ring-zinc-500/20'],
    ];
    $statusPill = $statusStyles[$sub['status']] ?? $statusStyles['free'];
@endphp

<section class="w-full" data-profile-page>
    {{-- Studio-style dark gradient + neutral chrome. No brand-palette gradients. --}}
    <style>
        [data-profile-page] [x-cloak] { display: none !important; }

        @keyframes pfFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pfBlob   { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(26px,-16px) scale(1.06); } }

        [data-profile-page] .pf-fade { animation: pfFadeUp .55s cubic-bezier(.16,1,.3,1) both; }
        [data-profile-page] .pf-d-1 { animation-delay: 60ms; }
        [data-profile-page] .pf-d-2 { animation-delay: 130ms; }
        [data-profile-page] .pf-d-3 { animation-delay: 210ms; }
        [data-profile-page] .pf-d-4 { animation-delay: 300ms; }
        [data-profile-page] .pf-blob { animation: pfBlob 18s ease-in-out infinite; }

        /* The exact dark radial gradient used by Image/Video Studio heroes. */
        [data-profile-page] .pf-darkgrad {
            background-color: #09090b; /* zinc-950 */
            background-image:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(79,70,229,0.26), transparent),
                radial-gradient(ellipse 80% 50% at 110% 110%, rgba(245,158,11,0.16), transparent),
                radial-gradient(ellipse 60% 40% at 50% 50%, rgba(79,70,229,0.10), transparent);
        }

        @media (prefers-reduced-motion: reduce) {
            [data-profile-page] .pf-fade, [data-profile-page] .pf-blob { animation: none !important; }
        }
    </style>

    {{-- ===================================================================== --}}
    {{-- HERO — studio dark gradient, bordered                                 --}}
    {{-- ===================================================================== --}}
    <div class="pf-darkgrad relative overflow-hidden rounded-3xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40 pf-fade">
        {{-- top shimmer line (same treatment as the studios) --}}
        <div class="absolute inset-x-0 top-0 h-px"
             style="background: linear-gradient(90deg, transparent, rgba(79,70,229,0.60), transparent);"></div>
        {{-- soft floating glow for depth --}}
        <div class="pointer-events-none absolute -top-20 right-16 h-64 w-64 rounded-full blur-3xl opacity-30 pf-blob"
             style="background: radial-gradient(circle, rgba(99,102,241,0.45), transparent 60%);"></div>

        <div class="relative p-6 sm:p-8">
            {{-- Top row --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/8 px-2 py-0.5 text-[9px] font-bold uppercase tracking-[0.18em] text-white">
                        <flux:icon.user-circle class="size-5" /> {{ __('My Profile') }}
                    </span>
                </div>

                {{-- Avatar save/cancel — only while a new photo upload is pending --}}
                @if($avatar)
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" wire:click="saveAvatar" wire:loading.attr="disabled" wire:target="saveAvatar"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-zinc-900 transition hover:bg-white/90">
                            <flux:icon.check class="size-4" /> {{ __('Save photo') }}
                        </button>
                        <button type="button" wire:click="$set('avatar', null)"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-white/15 bg-white/8 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-white/15">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Identity row --}}
            <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex items-end gap-4">
                    {{-- Avatar + uploader --}}
                    <div class="relative shrink-0"
                         x-data="{
                            uploading: false,
                            choose() { $refs.avatarInput.click(); }
                         }"
                         x-on:livewire-upload-start="uploading = true"
                         x-on:livewire-upload-finish="uploading = false"
                         x-on:livewire-upload-error="uploading = false">

                        {{-- Background glow — same indigo bloom the studio hero icon casts --}}
                        <div class="pointer-events-none absolute -inset-3 rounded-[28px] bg-indigo-500/30 blur-2xl"></div>

                        <div class="relative h-24 w-24 overflow-hidden rounded-2xl border border-white/15 ring-2 ring-black/20 shadow-xl shadow-indigo-500/30 sm:h-28 sm:w-28">
                            @if($this->avatarUrl)
                                <img src="{{ $this->avatarUrl }}" alt="{{ $user->name }}" class="h-full w-full object-cover" />
                            @else
                                <div class="pf-darkgrad flex h-full w-full items-center justify-center text-2xl font-black text-white sm:text-3xl">
                                    {{ $this->initials }}
                                </div>
                            @endif

                            {{-- Hover overlay --}}
                            <button type="button" @click="choose()"
                                    class="absolute inset-0 flex items-center justify-center bg-zinc-950/55 opacity-0 transition hover:opacity-100"
                                    aria-label="{{ __('Change profile photo') }}">
                                <flux:icon.camera class="size-6 text-white" />
                            </button>

                            {{-- Uploading spinner --}}
                            <div x-show="uploading" x-cloak class="absolute inset-0 flex items-center justify-center bg-zinc-950/65">
                                <flux:icon.arrow-path class="size-6 animate-spin text-white" />
                            </div>
                        </div>

                        {{-- Edit badge — neutral dark, bordered --}}
                        <button type="button" @click="choose()"
                                class="absolute -bottom-1.5 -right-1.5 flex size-8 items-center justify-center rounded-full border border-white/20 bg-zinc-900 text-white shadow-lg transition hover:scale-105 hover:bg-zinc-800"
                                aria-label="{{ __('Upload new photo') }}">
                            <flux:icon.pencil class="size-3.5" />
                        </button>

                        <input type="file" x-ref="avatarInput" wire:model="avatar"
                               accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" />
                    </div>

                    {{-- Name + email + meta --}}
                    <div class="pb-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-black tracking-tight text-white sm:text-2xl">{{ $user->name }}</h2>
                            @if(! $this->hasUnverifiedEmail)
                                <flux:icon.check-badge class="size-5 text-indigo-300" title="{{ __('Verified') }}" />
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-zinc-300">{{ $user->email }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[12px] text-zinc-400">
                            @if($user->company)
                                <span class="inline-flex items-center gap-1"><flux:icon.building-office-2 class="size-3.5" /> {{ $user->company }}</span>
                            @endif
                            @if($user->city || $user->country)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.map-pin class="size-3.5" />
                                    {{ collect([$user->city, ($countries[$user->country]['name'] ?? null)])->filter()->join(', ') }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1">
                                <flux:icon.calendar class="size-3.5" />
                                {{ __('Joined') }} {{ optional($metrics['memberSince'])->format('M Y') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Stat tiles — Credits, Generations, Total spent (studio chip style) --}}
                @php
                    $heroStats = [
                        [
                            'icon'  => 'bolt',
                            'tint'  => 'amber',
                            'label' => __('Credits'),
                            'value' => number_format($metrics['creditsTotal']),
                            'sub'   => $metrics['creditsPrepaid'] > 0 ? __('incl. :n prepaid', ['n' => number_format($metrics['creditsPrepaid'])]) : __('available'),
                        ],
                        [
                            'icon'  => 'sparkles',
                            'tint'  => 'indigo',
                            'label' => __('Generations'),
                            'value' => number_format($metrics['generations']),
                            'sub'   => $metrics['images'] . ' ' . __('img') . ' · ' . $metrics['videos'] . ' ' . __('vid') . ' · ' . $metrics['copies'] . ' ' . __('copy'),
                        ],
                        [
                            'icon'  => 'banknote-arrow-up',
                            'tint'  => 'emerald',
                            'label' => __('Total spent'),
                            'value' => $sub['symbol'] . number_format($metrics['totalSpent'], 2),
                            'sub'   => $metrics['orderCount'] . ' ' . __('orders'),
                        ],
                    ];

                    // Total spent + Wallet are part of the SaaS billing feature.
                    // Drop the Total spent tile and skip the Wallet tile when the
                    // magicads-saas extension/feature is inactive.
                    if (! \App\Services\HelperService::extensionSaaS()) {
                        $heroStats = array_values(array_filter($heroStats, fn ($s) => $s['label'] !== __('Total spent')));
                    } else {
                        $walletSymbol = config('payment.default_system_currency_symbol') ?: '$';
                        $heroStats[] = [
                            'icon'  => 'wallet',
                            'tint'  => 'sky',
                            'label' => __('Wallet'),
                            'value' => $walletSymbol . number_format((float) ($user->wallet ?? 0), 2),
                            'sub'   => __('balance'),
                        ];
                    }
                    $heroTint = [
                        'amber'   => 'from-amber-400/20 to-orange-500/20 border-amber-400/30 text-amber-400',
                        'indigo'  => 'from-indigo-400/20 to-indigo-600/20 border-indigo-400/30 text-indigo-300',
                        'emerald' => 'from-emerald-400/20 to-emerald-600/20 border-emerald-400/30 text-emerald-300',
                        'sky'     => 'from-sky-400/20 to-sky-600/20 border-sky-400/30 text-sky-300',
                    ];
                    $heroCols = match (count($heroStats)) {
                        2       => 'grid-cols-2',
                        4       => 'grid-cols-2 sm:grid-cols-4',
                        default => 'grid-cols-3',
                    };
                @endphp
                <div class="grid w-full {{ $heroCols }} gap-2 sm:w-auto sm:shrink-0">
                    @foreach($heroStats as $stat)
                        <div class="rounded-xl border border-white/10 bg-white/4 px-3 py-2.5 backdrop-blur-sm sm:min-w-[120px]">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex size-6 items-center justify-center rounded-lg border bg-linear-to-br {{ $heroTint[$stat['tint']] }}">
                                    <flux:icon :name="$stat['icon']" class="size-3.5" />
                                </div>
                                <span class="truncate text-[9px] font-bold uppercase tracking-widest text-zinc-500">{{ $stat['label'] }}</span>
                            </div>
                            <div class="truncate text-base font-bold leading-none tabular-nums text-white text-right">{{ $stat['value'] }}</div>
                            <div class="mt-1 truncate text-[10px] text-zinc-400 text-right">{{ $stat['sub'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @error('avatar')
                <p class="mt-3 text-xs font-medium text-rose-300">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- MAIN GRID                                                             --}}
    {{-- ===================================================================== --}}
    <div class="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-3">

        {{-- ============================== LEFT COLUMN ============================== --}}
        <div class="space-y-8 lg:col-span-1">

            {{-- Subscription / plan card — studio dark gradient, bordered --}}
            {{-- Part of the SaaS billing feature — hidden when magicads-saas is inactive. --}}
            @if (\App\Services\HelperService::extensionSaaS())
            <div class="pf-darkgrad relative overflow-hidden rounded-2xl border border-zinc-800/90 shadow-sm shadow-neutral-950/40 p-5 text-zinc-100 pf-fade pf-d-2">
                <div class="absolute inset-x-0 top-0 h-px"
                     style="background: linear-gradient(90deg, transparent, rgba(99,102,241,0.55), transparent);"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400">{{ __('Current plan') }}</div>
                        <div class="mt-1 text-2xl font-black tracking-tight text-white">{{ $sub['planName'] }}</div>
                        @if($sub['planType'])
                            <div class="mt-0.5 text-[11px] font-medium capitalize text-zinc-400">{{ __($sub['planType']) }}</div>
                        @endif
                    </div>
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-white/15 bg-white/8 backdrop-blur-sm">
                        <flux:icon.gem class="size-5 text-indigo-300" />
                    </span>
                </div>

                @if($sub['active'])
                    <div class="relative mt-4 grid grid-cols-2 gap-3 border-t border-white/10 pt-4">
                        <div>
                            <div class="text-[9px] font-semibold uppercase tracking-widest text-zinc-500">{{ __('Billing') }}</div>
                            <div class="mt-0.5 text-sm font-bold tabular-nums text-white">{{ $sub['symbol'] }}{{ number_format($sub['amount'], 2) }}</div>
                        </div>
                        <div>
                            <div class="text-[9px] font-semibold uppercase tracking-widest text-zinc-500">{{ $sub['isLifetime'] ? __('Access') : __('Renews in') }}</div>
                            <div class="mt-0.5 text-sm font-bold tabular-nums text-white">
                                @if($sub['isLifetime'])
                                    {{ __('Lifetime') }}
                                @elseif($sub['daysLeft'] !== null)
                                    {{ $sub['daysLeft'] }} {{ __('days') }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($sub['activeUntil'] && ! $sub['isLifetime'])
                        <div class="relative mt-3 text-[11px] text-zinc-400">
                            {{ __('Next billing on :date', ['date' => $sub['activeUntil']->format('M j, Y')]) }}
                        </div>
                    @endif
                @else
                    <p class="relative mt-4 border-t border-white/10 pt-4 text-[12px] text-zinc-400">
                        {{ __("You're on the free plan. Upgrade to unlock more credits and features.") }}
                    </p>
                @endif

                <a href="{{ route('user.billing') }}" wire:navigate
                   class="relative mt-4 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-zinc-900 transition hover:bg-white/90">
                    <flux:icon.arrow-up-circle class="size-4" />
                    {{ $sub['active'] ? __('Manage subscription') : __('View plans') }}
                </a>
            </div>
            @endif

            {{-- Account snapshot — bordered --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-white/8 dark:bg-(--default-element-bg-color) pf-fade pf-d-3">
                <h3 class="mb-4 text-[11px] font-bold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ __('Account') }}</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.hashtag class="size-4 text-zinc-400" /> {{ __('User ID') }}
                        </dt>
                        <dd class="font-mono text-[12px] font-semibold text-zinc-700 dark:text-zinc-200">{{ $user->user_id }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.hashtag class="size-4 text-zinc-400" /> {{ __('Referral ID') }}
                        </dt>
                        <dd class="font-mono text-[12px] font-semibold text-zinc-700 dark:text-zinc-200">{{ $user->referral_id }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.envelope class="size-4 text-zinc-400" /> {{ __('Email status') }}
                        </dt>
                        <dd>
                            @if($this->hasUnverifiedEmail)
                                <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-amber-600 dark:text-amber-400">
                                    <flux:icon.exclamation-circle class="size-3.5" /> {{ __('Unverified') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600 dark:text-emerald-400">
                                    <flux:icon.check-circle class="size-3.5" /> {{ __('Verified') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.shield-check class="size-4 text-zinc-400" /> {{ __('Two-factor') }}
                        </dt>
                        <dd>
                            @if($user->google2fa_enabled || $user->two_factor_secret)
                                <span class="text-[12px] font-semibold text-emerald-600 dark:text-emerald-400">{{ __('On') }}</span>
                            @else
                                <a href="{{ route('two-factor.show') }}" wire:navigate class="text-[12px] font-semibold text-zinc-700 underline-offset-2 hover:underline dark:text-zinc-200">{{ __('Enable') }}</a>
                            @endif
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.clock class="size-4 text-zinc-400" /> {{ __('Member since') }}
                        </dt>
                        <dd class="text-[12px] font-semibold text-zinc-700 dark:text-zinc-200">{{ optional($metrics['memberSince'])->format('M j, Y') ?? '—' }}</dd>
                    </div>
                </dl>

                <flux:separator variant="subtle" class="my-4" />

                <div class="flex flex-wrap gap-2">
                    <flux:button :href="route('user-password.edit')" wire:navigate size="xs" variant="ghost">
                        <flux:icon.lock-closed class="size-3.5" /> {{ __('Password') }}
                    </flux:button>
                    @if(\Illuminate\Support\Facades\Route::has('two-factor.show'))
                        <flux:button :href="route('two-factor.show')" wire:navigate size="xs" variant="ghost">
                            <flux:icon.shield-check class="size-3.5" /> {{ __('Two-factor auth') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================== RIGHT COLUMN ============================== --}}
        <div class="space-y-8 lg:col-span-2">

            {{-- Editable profile form — bordered card --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-bg-color) pf-fade pf-d-1">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/8">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-white/8 dark:bg-white/5 dark:text-zinc-200">
                            <flux:icon.user class="size-4" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-50">{{ __('Personal information') }}</h3>
                            <p class="text-[12px] text-zinc-500 dark:text-zinc-400">{{ __('Update your contact details and address') }}</p>
                        </div>
                    </div>
                    <x-action-message class="text-[12px] font-semibold text-emerald-600 dark:text-emerald-400" on="profile-updated">
                        <span class="inline-flex items-center gap-1"><flux:icon.check-circle class="size-4" /> {{ __('Saved') }}</span>
                    </x-action-message>
                </div>

                <form wire:submit="updateProfileInformation" class="space-y-6 p-5">

                    {{-- Identity --}}
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-white/8">
                        <h4 class="mb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ __('Identity') }}</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:input wire:model="name" :label="__('Full name')" type="text" required autocomplete="name" icon="user" />

                            <div>
                                <flux:input wire:model="email" :label="__('Email address')" type="email" required autocomplete="email" icon="envelope" />
                                @if ($this->hasUnverifiedEmail)
                                    <div class="mt-1.5">
                                        <flux:text class="text-[12px]">
                                            {{ __('Your email address is unverified.') }}
                                            <flux:link class="cursor-pointer text-[12px]" wire:click.prevent="resendVerificationNotification">
                                                {{ __('Re-send verification email.') }}
                                            </flux:link>
                                        </flux:text>
                                        @if (session('status') === 'verification-link-sent')
                                            <flux:text class="mt-1 text-[12px] font-medium !text-emerald-600 dark:!text-emerald-400">
                                                {{ __('A new verification link has been sent to your email address.') }}
                                            </flux:text>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Company / contact --}}
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-white/8">
                        <h4 class="mb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ __('Company & contact') }}</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <flux:input wire:model="company" :label="__('Company')" type="text" autocomplete="organization" icon="building-office-2" :placeholder="__('Acme Inc.')" />
                            <flux:input wire:model="phone_number" :label="__('Phone number')" type="tel" autocomplete="tel" icon="phone" :placeholder="__('+1 555 000 0000')" />
                            <div class="sm:col-span-2">
                                <flux:input wire:model="website" :label="__('Website')" type="url" autocomplete="url" icon="globe-alt" placeholder="https://example.com" />
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-white/8">
                        <h4 class="mb-3 text-[10px] font-bold uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">{{ __('Address') }}</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <flux:input wire:model="address" :label="__('Street address')" type="text" autocomplete="street-address" icon="map-pin" :placeholder="__('123 Main St.')" />
                            </div>
                            <flux:input wire:model="city" :label="__('City')" type="text" autocomplete="address-level2" />
                            <flux:input wire:model="postal_code" :label="__('Postal code')" type="text" autocomplete="postal-code" />
                            <div class="sm:col-span-2">
                                <flux:select wire:model="country" :label="__('Country')" searchable :placeholder="__('Select a country')">
                                    @foreach($countries as $code => $meta)
                                        <flux:select.option value="{{ $code }}">{{ $meta['flagEmoji'] ?? '' }} {{ $meta['name'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-5">
                        <x-action-message class="text-[12px] font-semibold text-emerald-600 dark:text-emerald-400" on="profile-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                        <flux:button variant="primary" type="submit">
                            <span wire:loading.remove wire:target="updateProfileInformation">{{ __('Save changes') }}</span>
                            <span wire:loading wire:target="updateProfileInformation" class="inline-flex items-center gap-2">
                                <flux:icon.arrow-path class="size-4 animate-spin" /> {{ __('Saving…') }}
                            </span>
                        </flux:button>
                    </div>
                </form>
            </div>

            {{-- Billing history — bordered card --}}
            {{-- Part of the SaaS billing feature — hidden when magicads-saas is inactive. --}}
            @if (\App\Services\HelperService::extensionSaaS())
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-(--default-element-bg-color) pf-fade pf-d-2">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-white/8">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-white/8 dark:bg-white/5 dark:text-zinc-200">
                            <flux:icon.receipt-text class="size-4" />
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-50">{{ __('Recent billing') }}</h3>
                            <p class="text-[12px] text-zinc-500 dark:text-zinc-400">{{ __('Your latest orders and invoices') }}</p>
                        </div>
                    </div>
                    <flux:button :href="route('user.billing')" wire:navigate size="xs" variant="ghost">{{ __('View all') }}</flux:button>
                </div>

                @php
                    $orderStatusTone = [
                        'completed' => 'text-emerald-700 bg-emerald-50 dark:text-emerald-300 dark:bg-emerald-500/10',
                        'pending'   => 'text-amber-700 bg-amber-50 dark:text-amber-300 dark:bg-amber-500/10',
                        'cancelled' => 'text-zinc-600 bg-zinc-100 dark:text-zinc-300 dark:bg-white/5',
                        'failed'    => 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-500/10',
                        'declined'  => 'text-rose-700 bg-rose-50 dark:text-rose-300 dark:bg-rose-500/10',
                    ];
                @endphp

                @if($this->recentOrders->isEmpty())
                    <div class="px-5 py-12 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-2xl border border-zinc-200 bg-zinc-100 text-zinc-400 dark:border-white/8 dark:bg-white/5">
                            <flux:icon.receipt-text class="size-6" />
                        </span>
                        <p class="mt-3 text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ __('No billing history yet') }}</p>
                        <p class="mt-1 text-[12px] text-zinc-500 dark:text-zinc-400">{{ __('Your orders will appear here once you subscribe.') }}</p>
                        <flux:button :href="route('user.billing')" wire:navigate size="sm" variant="primary" class="mt-4">
                            {{ __('Explore plans') }}
                        </flux:button>
                    </div>
                @else
                    <div class="divide-y divide-zinc-200 dark:divide-white/6">
                        @foreach($this->recentOrders as $order)
                            @php
                                $currencies = config('currencies', []);
                                $oSymbol = $currencies[strtoupper($order->currency ?: 'USD')]['symbol'] ?? '$';
                                $tone = $orderStatusTone[$order->status] ?? $orderStatusTone['cancelled'];
                            @endphp
                            <div class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-zinc-50 dark:hover:bg-white/5">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-zinc-200 bg-zinc-100 text-zinc-600 dark:border-white/8 dark:bg-white/5 dark:text-zinc-300">
                                    <flux:icon.receipt-text class="size-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="truncate text-[13px] font-semibold text-zinc-800 dark:text-zinc-100">
                                        {{ $order->plan_name ?? __('Order') }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] text-zinc-400 dark:text-zinc-500">
                                        <span class="font-mono">#{{ $order->order_id }}</span>
                                        <span>·</span>
                                        <span>{{ $order->created_at?->format('M j, Y') }}</span>
                                    </div>
                                    @if (is_array($order->coupon) && ! empty($order->coupon['code']))
                                        <div class="mt-1 inline-flex items-center gap-1 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                            <flux:icon.ticket class="size-3" />
                                            {{ __('Coupon :code (−:percent%)', ['code' => $order->coupon['code'], 'percent' => (int) ($order->coupon['percentage'] ?? 0)]) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-[13px] font-bold tabular-nums text-zinc-800 dark:text-zinc-100">
                                        {{ $oSymbol }}{{ number_format((float) $order->price, 2) }}
                                    </div>
                                    <span class="mt-0.5 inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $tone }}">
                                        {{ __(ucfirst($order->status)) }}
                                    </span>
                                </div>
                                @if ($order->status === 'completed')
                                    <a href="{{ route('user.invoices.download', $order->order_id) }}"
                                       title="{{ __('Download invoice') }}"
                                       aria-label="{{ __('Download invoice') }}"
                                       class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-800 dark:border-white/8 dark:text-zinc-400 dark:hover:bg-white/5 dark:hover:text-zinc-100">
                                        <flux:icon.arrow-down-tray class="size-4" />
                                    </a>
                                @elseif ($order->gateway === 'banktransfer' && $order->status === 'pending')
                                    <button type="button"
                                            wire:click="openProofModal('{{ $order->order_id }}')"
                                            title="{{ $order->payment_proof_path ? __('View / replace proof of payment') : __('Upload proof of payment') }}"
                                            aria-label="{{ $order->payment_proof_path ? __('View / replace proof of payment') : __('Upload proof of payment') }}"
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg border transition {{ $order->payment_proof_path ? 'border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-500/30 dark:text-emerald-300 dark:hover:bg-emerald-500/10' : 'border-amber-300 text-amber-600 hover:bg-amber-50 dark:border-amber-500/30 dark:text-amber-300 dark:hover:bg-amber-500/10' }}">
                                        <flux:icon.paper-clip class="size-4" />
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- Gift cards — redeem & transfer. Only shown when the Gift Cards
                 plugin is installed AND activated by the admin. --}}
            @if (\App\Services\HelperService::extensionGiftCards())
                <div class="pf-fade pf-d-3">
                    @livewire(\App\Livewire\User\GiftCards\GiftCardsUser::class)
                </div>
            @endif

            {{-- Coupons — shared codes & redemption history. Only shown when the
                 Coupons plugin is installed AND activated by the admin. --}}
            @if (\App\Services\HelperService::extensionCoupons())
                <div class="pf-fade pf-d-3">
                    @livewire(\App\Livewire\User\Coupons\Coupons::class)
                </div>
            @endif

            {{-- Danger zone — bordered (rose) --}}
            @if ($this->showDeleteUser)
                <div class="overflow-hidden rounded-2xl border border-rose-200 bg-rose-50/40 dark:border-rose-500/25 dark:bg-rose-500/5 pf-fade pf-d-3">
                    <div class="p-5">
                        <livewire:settings.delete-user-form />
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- Bank transfer — proof of payment upload modal                         --}}
    {{-- ===================================================================== --}}
    @php
        $proofOrder = $proofOrderId
            ? $this->recentOrders->firstWhere('order_id', $proofOrderId)
            : null;
    @endphp
    <flux:modal wire:model="showProofModal" name="upload-payment-proof" class="max-w-lg w-full">
        <form wire:submit.prevent="uploadProof" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Proof of payment') }}</flux:heading>
                <flux:subheading>
                    {{ __('Upload your wire transfer receipt or confirmation for order :ref. PDF or image, up to 8 MB.', ['ref' => $proofOrderId]) }}
                </flux:subheading>
            </div>

            @if ($proofOrder && $proofOrder->payment_proof_path)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-3 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                    <div class="flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                        <flux:icon.check-circle class="size-4" />
                        <span>
                            {{ __('Proof already uploaded') }}
                            @if ($proofOrder->payment_proof_uploaded_at)
                                · {{ $proofOrder->payment_proof_uploaded_at->format('M j, Y H:i') }}
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('user.payments.proof', $proofOrder->order_id) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-semibold text-zinc-700 transition-colors hover:bg-white dark:border-white/10 dark:text-zinc-200 dark:hover:bg-white/5">
                        <flux:icon.eye class="size-3.5" />
                        {{ __('View current') }}
                    </a>
                </div>
            @endif

            <div>
                <input type="file" wire:model="proof" accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="block w-full text-xs text-zinc-600 file:mr-3 file:rounded-full file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-300 dark:file:bg-white/10 dark:file:text-zinc-200" />
                <flux:error name="proof" />
                <div wire:loading wire:target="proof" class="mt-2 text-xs text-zinc-500">{{ __('Uploading…') }}</div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-white/6">
                <flux:modal.close>
                    <flux:button variant="ghost" type="button">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="arrow-up-tray"
                             wire:loading.attr="disabled" wire:target="uploadProof,proof">
                    {{ $proofOrder && $proofOrder->payment_proof_path ? __('Replace proof') : __('Upload proof') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>

