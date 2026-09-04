{{--
    Pricing — public landing page.

    Mirrors the structure of the authenticated billing page
    (resources/views/default/livewire/user/billing/index.blade.php) so
    visitors and signed-in users see the same plan layout:

      • Plans are bucketed by plan_type: prepaid / monthly / yearly / lifetime
      • A tab is rendered only when its bucket has ≥ 1 active plan
      • Only the active tab's cards are visible (vanilla WAI-ARIA tabs pattern,
        wired up by resources/js/landing.js — no Alpine on this layout)
      • Untyped legacy rows fall under "monthly" so old seed data still surfaces

    The $plans collection is injected by AppServiceProvider's `welcome` View::composer
    and is already filtered to status = 'active' and ordered by price.
--}}
@php
    $plans = $plans ?? collect();

    /** Canonical order — drives both bucket creation and tab order. */
    $intervals = ['prepaid', 'monthly', 'yearly', 'lifetime'];

    $intervalLabels = [
        'prepaid'  => __('Prepaid'),
        'monthly'  => __('Monthly'),
        'yearly'   => __('Yearly'),
        'lifetime' => __('Lifetime'),
    ];

    $registrationEnabled = class_exists(\Laravel\Fortify\Features::class)
        && \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration());

    $currencySymbol = function ($code) {
        $map = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$'];
        $code = strtoupper((string) $code);
        return $map[$code] ?? ($code ? $code . ' ' : '$');
    };

    $formatFeatures = function ($plan) {
        $raw = $plan->features ?? null;
        if (blank($raw)) return [];
        $decoded = is_string($raw) ? json_decode($raw, true) : (is_array($raw) ? $raw : null);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('trim', array_filter($decoded, 'is_string')), fn ($v) => filled($v)));
        }
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $raw)), fn ($v) => filled($v)));
    };

    $periodLabel = function ($planType) {
        return match ($planType) {
            'monthly'  => __('month'),
            'yearly'   => __('year'),
            'prepaid'  => __('one-time'),
            'lifetime' => null,
            default    => null,
        };
    };

    /** Fallback dataset used only when no active plans exist (fresh installs). */
    $fallbackPlans = collect([
        (object) [
            'plan_id' => 'starter', 'name' => 'Starter', 'price' => 0, 'currency' => 'USD',
            'plan_type' => 'monthly', 'featured' => false,
            'tagline' => __('For solo marketers and side projects'),
            'description' => __('Everything you need to ship your first campaign.'),
            'features' => json_encode([
                __('50 image generations / mo'),
                __('10 video generations / mo'),
                __('Unlimited ad copy'),
                __('1 brand kit'),
                __('All canvas presets'),
            ]),
        ],
        (object) [
            'plan_id' => 'studio', 'name' => 'Studio', 'price' => 49, 'currency' => 'USD',
            'plan_type' => 'monthly', 'featured' => true,
            'tagline' => __('For growing marketing teams'),
            'description' => __('For marketing teams running paid everywhere.'),
            'features' => json_encode([
                __('1,000 image generations / mo'),
                __('200 video generations / mo'),
                __('Unlimited ad copy + A/B variants'),
                __('5 brand kits'),
                __('Priority rendering queue'),
                __('Shared asset gallery'),
            ]),
        ],
        (object) [
            'plan_id' => 'scale', 'name' => 'Scale', 'price' => 199, 'currency' => 'USD',
            'plan_type' => 'monthly', 'featured' => false,
            'tagline' => __('For agencies and enterprise'),
            'description' => __('Agencies and in-house teams shipping at volume.'),
            'features' => json_encode([
                __('Unlimited image + video generations'),
                __('Unlimited brand kits'),
                __('Custom canvas presets'),
                __('API access'),
                __('Dedicated success manager'),
            ]),
        ],
    ]);

    $sourcePlans = $plans->isNotEmpty() ? $plans : $fallbackPlans;

    /** Bucket plans by plan_type, with legacy untyped rows landing in "monthly". */
    $plansByInterval = [];
    foreach ($intervals as $key) {
        $plansByInterval[$key] = collect();
    }
    foreach ($sourcePlans as $plan) {
        $type = $plan->plan_type ?? null;
        if (! $type || ! isset($plansByInterval[$type])) {
            $type = 'monthly';
        }
        $plansByInterval[$type]->push($plan);
    }

    /** Only render tabs for intervals with at least one plan, preserving canonical order. */
    $availableIntervals = [];
    foreach ($intervals as $key) {
        if ($plansByInterval[$key]->isNotEmpty()) {
            $availableIntervals[] = $key;
        }
    }

    $defaultInterval = in_array('monthly', $availableIntervals, true)
        ? 'monthly'
        : ($availableIntervals[0] ?? null);

    $showToggle = count($availableIntervals) > 1;

    /** Yearly savings badge — only when monthly + yearly both have paid plans. */
    $yearlySavingsPercent = null;
    if (in_array('monthly', $availableIntervals, true) && in_array('yearly', $availableIntervals, true)) {
        $cheapestMonthly = (float) $plansByInterval['monthly']
            ->filter(fn ($p) => (float) ($p->price ?? 0) > 0)
            ->min('price');
        $cheapestYearly = (float) $plansByInterval['yearly']
            ->filter(fn ($p) => (float) ($p->price ?? 0) > 0)
            ->min('price');
        $cheapestYearlyPerMonth = $cheapestYearly > 0 ? $cheapestYearly / 12 : 0;
        if ($cheapestMonthly > 0 && $cheapestYearlyPerMonth > 0 && $cheapestYearlyPerMonth < $cheapestMonthly) {
            $yearlySavingsPercent = (int) round((1 - ($cheapestYearlyPerMonth / $cheapestMonthly)) * 100);
        }
    }
@endphp

<section id="pricing" class="relative overflow-hidden py-24 sm:py-32">
    {{-- Decorative grid behind the pricing cards --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 opacity-40"
         style="background-image:
                linear-gradient(#EAEAEA 1px, transparent 1px),
                linear-gradient(90deg, #EAEAEA 1px, transparent 1px);
                background-size: 40px 40px;
                mask-image: radial-gradient(ellipse 70% 70% at 50% 40%, black 20%, transparent 80%);
                -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 40%, black 20%, transparent 80%);"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <span class="l-chip l-chip--indigo">
                <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                {{ __('Pricing') }}
            </span>
            <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                {{ __('Simple plans.') }}
                <span class="l-accent">{{ __('Infinite ads.') }}</span>
            </h2>
            <p class="mt-4 text-[15px] text-black/60">
                {{ __("Start free. Scale when you're ready. Cancel anytime.") }}
            </p>
        </div>

        @if ($showToggle)
            {{--
                WAI-ARIA tabs pattern. landing.js initTablists() handles selection,
                arrow-key navigation, and panel hidden/aria-hidden swapping based on
                role="tablist" / role="tab" / aria-controls. No Alpine required.
            --}}
            <div role="tablist" aria-label="{{ __('Billing interval') }}"
                 class="mx-auto mt-10 flex items-center justify-center">
                <div class="inline-flex flex-wrap items-center justify-center gap-1 rounded-full border border-[var(--l-border)] bg-white p-1 text-sm">
                    @foreach ($availableIntervals as $intervalKey)
                        @php
                            $isDefault = $intervalKey === $defaultInterval;
                            $showYearlyBadge = $intervalKey === 'yearly' && $yearlySavingsPercent;
                        @endphp
                        <button type="button"
                                role="tab"
                                id="pricing-tab-{{ $intervalKey }}"
                                aria-controls="pricing-panel-{{ $intervalKey }}"
                                aria-selected="{{ $isDefault ? 'true' : 'false' }}"
                                tabindex="{{ $isDefault ? '0' : '-1' }}"
                                data-pricing-tab="{{ $intervalKey }}"
                                class="pricing-tab inline-flex items-center gap-2 rounded-full px-5 py-2 font-medium text-black/65 transition-colors hover:text-black aria-selected:bg-black aria-selected:text-white">
                            <span>{{ $intervalLabels[$intervalKey] ?? ucfirst($intervalKey) }}</span>
                            @if ($showYearlyBadge)
                                <span class="rounded-full bg-[#4F46E5]/15 px-1.5 py-0.5 text-[10px] font-semibold text-[#4F46E5]"
                                      title="{{ __('Save :percent% with yearly billing', ['percent' => $yearlySavingsPercent]) }}">
                                    −{{ $yearlySavingsPercent }}%
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach ($availableIntervals as $intervalKey)
            @php
                $intervalPlans = $plansByInterval[$intervalKey];
                $isDefault     = $intervalKey === $defaultInterval;
            @endphp
            <div id="pricing-panel-{{ $intervalKey }}"
                 role="tabpanel"
                 aria-labelledby="pricing-tab-{{ $intervalKey }}"
                 aria-hidden="{{ $isDefault ? 'false' : 'true' }}"
                 @if (! $isDefault) hidden @endif>
                <div class="mx-auto mt-14 grid max-w-5xl grid-cols-1 items-stretch gap-6 md:grid-cols-3">
                    @foreach ($intervalPlans as $idx => $plan)
                        @php
                            $isFeatured   = (bool) ($plan->featured ?? false);
                            $features     = $formatFeatures($plan);
                            $priceDisplay = rtrim(rtrim(number_format((float) ($plan->price ?? 0), 2, '.', ''), '0'), '.');
                            $isFree       = (bool) ($plan->free ?? false) || (float) ($plan->price ?? 0) === 0.0;
                            $tagline      = $plan->tagline ?? $plan->description ?? '';
                            $period       = $periodLabel($plan->plan_type ?? null);
                        @endphp

                        <article @class([
                            'relative flex h-full flex-col rounded-2xl p-7 sm:p-8',
                            'bg-[#0F172A] text-white md:-translate-y-4 z-10 shadow-[0_0_0_1px_rgba(79,70,229,0.35),0_40px_80px_-30px_rgba(79,70,229,0.4)]' => $isFeatured,
                            'bg-white border border-[var(--l-hairline)] text-black' => !$isFeatured,
                        ])>
                            {{-- Featured accent glow layer --}}
                            @if ($isFeatured)
                                <div aria-hidden="true" class="pointer-events-none absolute inset-0 rounded-2xl overflow-hidden">
                                    <div class="absolute inset-x-0 top-0 h-32"
                                         style="background: radial-gradient(ellipse 60% 100% at 50% 0%, rgba(79, 70, 229, 0.35), transparent 70%);"></div>
                                    <div class="absolute inset-x-0 top-0 h-px"
                                         style="background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.9) 50%, transparent);"></div>
                                </div>
                                <div class="absolute -top-3 left-1/2 z-10 -translate-x-1/2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-[#4F46E5] px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-white shadow-[0_10px_24px_-6px_rgba(79,70,229,0.7)]">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                            <path d="M12 2 9 9H2l5.5 4.5L5 21l7-5 7 5-2.5-7.5L22 9h-7z"/>
                                        </svg>
                                        {{ __('Most popular') }}
                                    </span>
                                </div>
                            @endif

                            {{-- Header: name + tagline --}}
                            <div class="relative">
                                <div class="flex items-center gap-2">
                                    <h3 @class(['text-sm font-semibold uppercase tracking-[0.08em]', 'text-white' => $isFeatured, 'text-black' => !$isFeatured])>
                                        {{ $plan->name }}
                                    </h3>
                                    @if ($isFree)
                                        <span class="l-mono rounded-full bg-emerald-500/10 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-emerald-700">{{ __('Free') }}</span>
                                    @endif
                                </div>
                                <p @class([
                                    'mt-2 min-h-[2.5em] text-[13px]',
                                    'text-white/60' => $isFeatured,
                                    'text-black/60' => !$isFeatured,
                                ])>
                                    {{ $tagline }}
                                </p>
                            </div>

                            {{-- Price --}}
                            <div class="relative mt-6 flex items-baseline gap-1.5">
                                @if ($isFree)
                                    <span @class(['l-display text-[56px] font-extrabold leading-none tracking-[-0.04em]', 'text-white' => $isFeatured, 'text-black' => !$isFeatured])>$0</span>
                                    <span @class(['text-[13px] font-medium', 'text-white/50' => $isFeatured, 'text-black/50' => !$isFeatured])>/ {{ __('forever') }}</span>
                                @else
                                    <span @class(['text-xl font-semibold', 'text-white/70' => $isFeatured, 'text-black/50' => !$isFeatured])>{{ $currencySymbol($plan->currency ?? 'USD') }}</span>
                                    <span @class(['l-display text-[56px] font-extrabold leading-none tracking-[-0.04em]', 'text-white' => $isFeatured, 'text-black' => !$isFeatured])>{{ $priceDisplay }}</span>
                                    @if ($period)
                                        <span @class(['text-[13px] font-medium', 'text-white/50' => $isFeatured, 'text-black/50' => !$isFeatured])>/ {{ $period }}</span>
                                    @endif
                                @endif
                            </div>

                            {{-- Credits hint, if applicable (matches backend card) --}}
                            @if ((int) ($plan->credits ?? 0) > 0)
                                <p @class([
                                    'relative mt-2 text-xs font-semibold',
                                    'text-white/80' => $isFeatured,
                                    'text-black/70' => !$isFeatured,
                                ])>
                                    {{ __(':count credits included', ['count' => number_format((int) $plan->credits)]) }}
                                </p>
                            @endif

                            {{-- CTA --}}
                            <a href="{{ ($plan->plan_id ?? null) ? route('plans.select', ['planId' => $plan->plan_id]) : ($registrationEnabled ? route('register') : route('login')) }}"
                               @class([
                                   'group relative mt-6 inline-flex items-center justify-center gap-1.5 rounded-full px-5 py-3 text-sm font-semibold transition-all',
                                   'bg-white text-black hover:scale-[1.02] hover:shadow-[0_20px_40px_-10px_rgba(255,255,255,0.3)]' => $isFeatured,
                                   'bg-black text-white hover:bg-[#1F1F1F] hover:scale-[1.01]' => !$isFeatured,
                               ])>
                                {{ $isFree ? __('Start free') : __('Choose plan') }}
                                <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                                </svg>
                            </a>

                            {{-- Divider + "Includes" label --}}
                            <div @class([
                                'relative mt-8 flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.15em]',
                                'text-white/50' => $isFeatured,
                                'text-black/40' => !$isFeatured,
                            ])>
                                <span>{{ __("What's included") }}</span>
                                <span @class(['h-px flex-1', 'bg-white/10' => $isFeatured, 'bg-[var(--l-hairline)]' => !$isFeatured])></span>
                            </div>

                            {{-- Feature list --}}
                            <div class="relative flex-1">
                                @if (! empty($features))
                                    <ul role="list" @class([
                                        'mt-5 space-y-3 text-[13px]',
                                        'text-white/85' => $isFeatured,
                                        'text-black/80' => !$isFeatured,
                                    ])>
                                        @foreach ($features as $feature)
                                            <li class="flex items-start gap-2.5">
                                                <span @class([
                                                    'mt-0.5 inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full',
                                                    'bg-[#4F46E5]' => $isFeatured,
                                                    'bg-black'     => !$isFeatured,
                                                ])>
                                                    <svg class="h-2.5 w-2.5 text-white" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="m2 5 2 2 4-4"/>
                                                    </svg>
                                                </span>
                                                <span>{{ $feature }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Empty-state guard: no active plans at all (rare — composer + fallback already cover most cases). --}}
        @if (empty($availableIntervals))
            <div class="mx-auto mt-14 max-w-2xl rounded-2xl border border-dashed border-[var(--l-hairline)] bg-white/60 p-12 text-center">
                <h3 class="text-base font-semibold text-black">{{ __('No plans available yet.') }}</h3>
                <p class="mt-1 text-sm text-black/60">{{ __('Check back shortly or contact support to learn more.') }}</p>
            </div>
        @endif

        {{-- Footnote --}}
        <div class="mt-12 flex flex-col items-center gap-2 text-[13px] text-black/55">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-[#4F46E5]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.3-11.5a1 1 0 0 1 1.4 0l3 3a1 1 0 0 1 0 1.4l-3 3a1 1 0 1 1-1.4-1.4L11.6 11H6a1 1 0 1 1 0-2h5.6L9.7 7.9a1 1 0 0 1 0-1.4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('All plans include unlimited users, every canvas preset, and priority support.') }}</span>
            </div>
        </div>
    </div>
</section>
