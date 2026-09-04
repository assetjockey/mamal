@php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = collect(\Plan::getTypes())->filter(fn ($label, $key) => ! empty($pricing[$key] ?? []));
    $defaultType = (int) ($planTypes->keys()->first() ?? 1);
    $signupEnabled = auth_signup_enabled();
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <section class="linkqr-shell linkqr-section pt-10" x-data="{ type: {{ $defaultType }} }">
        <div class="mb-10 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border bg-white px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <i class="fa-light fa-credit-card"></i>
                    {{ __('Pricing') }}
                </span>
                <h1 class="mt-6 text-5xl font-extrabold leading-[1.02] tracking-[-0.07em] text-slate-950 md:text-6xl">
                    {{ __('Plans for creators, brands, and agencies.') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                    {{ __('Choose the workspace depth you need for Bio pages, dynamic QR campaigns, branded short links, custom domains, attribution, and analytics.') }}
                </p>
            </div>

            @if ($planTypes->isNotEmpty())
                <div class="inline-flex rounded-full border bg-white p-1.5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.88);">
                    @foreach ($planTypes as $typeKey => $typeLabel)
                        <button type="button" x-on:click="type = {{ $typeKey }}" class="rounded-full px-5 py-2.5 text-sm font-bold transition" x-bind:class="type === {{ $typeKey }} ? 'bg-blue-600 text-white shadow-[0_12px_24px_-18px_rgba(36,84,232,0.72)]' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-700'">
                            {{ $typeLabel }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-[1.7rem] border bg-white/92 shadow-[0_34px_90px_-60px_rgba(15,23,42,0.34)]" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">
            <div class="linkqr-pricing-grid flex flex-wrap">
                @foreach ($planTypes as $typeKey => $typeLabel)
                    @php
                        $groupPlans = collect($pricing[$typeKey] ?? []);
                        $planCount = $groupPlans->count();
                    @endphp

                    @foreach ($groupPlans as $plan)
                        @php
                            $isFreePlan = (bool) ($plan['free_plan'] ?? false);
                            $planTarget = $plan['model']->slug ?? $plan['id'];
                        @endphp
                        <div class="w-full lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" x-transition style="display: none; z-index: {{ 10 - $loop->index }};">
                            <article class="linkqr-pricing-card relative flex h-full flex-col px-6 py-8 sm:px-8 {{ $plan['featured'] ? 'is-featured bg-gradient-to-b from-blue-50 via-white to-teal-50/70' : 'bg-white' }}">
                                @if ($plan['featured'])
                                    <div class="absolute right-0 top-0 h-40 w-40 overflow-hidden">
                                        <div class="absolute top-6 -right-10 rotate-45">
                                            <span class="bg-blue-600 px-12 py-1 text-xs font-extrabold uppercase tracking-[0.12em] text-white shadow-md">{{ __('Featured') }}</span>
                                        </div>
                                    </div>
                                @endif

                                <span class="mb-3 inline-block text-sm font-extrabold uppercase tracking-[0.16em] {{ $plan['featured'] ? 'text-blue-700' : 'text-slate-500' }}">{{ $plan['name'] ?? '-' }}</span>
                                @if (!$isFreePlan && (int) ($plan['trial_day'] ?? 0) > 0)
                                    <span class="mb-3 inline-flex w-max items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-amber-700">{{ __(':days-day trial', ['days' => (int) $plan['trial_day']]) }}</span>
                                @endif
                                <p class="mb-6 min-h-[4.5rem] text-sm font-medium leading-7 text-slate-600">{{ $plan['desc'] ?: __('A practical plan for Link Bio, Dynamic QR, Short Links, smart routing, branded domains, and analytics.') }}</p>
                                <h2 class="mb-1 text-5xl font-extrabold leading-tight tracking-[-0.07em] text-slate-950">
                                    {{ $isFreePlan ? ($plan['currency_symbol'] ?: '$').'0' : ($plan['currency_symbol'] ?: '$').rtrim(rtrim(number_format((float) ($plan['price'] ?? 0), 2), '0'), '.') }}
                                    <span class="text-base font-bold tracking-normal text-slate-400">/{{ strtolower($typeLabel) }}</span>
                                </h2>
                                <p class="mb-8 text-sm font-semibold text-slate-500">{{ match((int) ($plan['type'] ?? 1)) { 2 => __('Billed yearly'), 3 => __('Pay once, use forever'), default => __('Billed monthly') } }}</p>
                                <a href="{{ $isFreePlan ? (auth()->check() ? route('portal.dashboard') : ($signupEnabled ? route('register') : route('login'))) : route('payment.index', $planTarget) }}" class="mb-9 block w-full rounded-[var(--theme-button-radius)] px-6 py-4 text-center text-sm font-extrabold transition {{ $plan['featured'] ? 'linkqr-button-primary' : 'border border-blue-200 bg-white text-blue-700 hover:bg-blue-50' }}">
                                    {{ $isFreePlan ? __('Start for Free') : __('Choose Plan') }}
                                </a>

                                @php
                                    $outerFeatureLabels = collect($plan['features'] ?? [])
                                        ->map(fn ($item) => strtolower(trim((string) ($item['label'] ?? $item))))
                                        ->filter()
                                        ->all();
                                    $promotedSubFeatureLabels = [
                                        'max. bio pages',
                                        'remove link bio branding',
                                        'credits',
                                        'max qr codes',
                                        'monthly qr scans',
                                        'dynamic rules per qr',
                                        'qr bulk generation',
                                        'max bulk rows',
                                        'short link quota',
                                        'monthly clicks',
                                        'bulk import',
                                        'bulk rows per import',
                                        'analytics retention',
                                        'custom short codes',
                                        'password links',
                                        'expiration and click caps',
                                        'advanced routing',
                                        'a/b destination testing',
                                        'api and webhooks',
                                        'api keys',
                                        'webhooks',
                                    ];
                                    $promotedSubFeatures = collect($plan['features'] ?? [])
                                        ->flatMap(fn ($item) => collect($item['subfeature'] ?? [])->flatMap(fn ($group) => $group['items'] ?? []))
                                        ->filter(function ($sub) use ($outerFeatureLabels, $promotedSubFeatureLabels) {
                                            $label = strtolower(trim((string) ($sub['label'] ?? '')));
                                            return in_array($label, $promotedSubFeatureLabels, true) && ! in_array($label, $outerFeatureLabels, true);
                                        })
                                        ->values();
                                    $visibleFeatureLabels = array_values(array_unique(array_merge(
                                        $outerFeatureLabels,
                                        $promotedSubFeatures->map(fn ($item) => strtolower(trim((string) ($item['label'] ?? ''))))->all()
                                    )));
                                    $visibleSubFeatureCount = function (array $item) use (&$visibleFeatureLabels): int {
                                        return collect($item['subfeature'] ?? [])
                                            ->flatMap(fn ($group) => $group['items'] ?? [])
                                            ->filter(function ($sub) use ($visibleFeatureLabels) {
                                                $label = strtolower(trim((string) ($sub['label'] ?? '')));

                                                return $label !== ''
                                                    && ! in_array($label, $visibleFeatureLabels, true)
                                                    && stripos($label, 'approval') === false
                                                    && stripos($label, 'approvals') === false;
                                            })
                                            ->count();
                                    };
                                    $featureOrder = [
                                        'access features' => 10,
                                        'link bio' => 20,
                                        'max. bio pages' => 21,
                                        'link bio templates' => 22,
                                        'ai bio assistant' => 23,
                                        'link bio analytics' => 24,
                                        'link bio a/b testing' => 25,
                                        'remove link bio branding' => 26,
                                        'advanced qr codes' => 30,
                                        'max qr codes' => 31,
                                        'monthly qr scans' => 32,
                                        'dynamic rules per qr' => 33,
                                        'qr bulk generation' => 34,
                                        'max bulk rows' => 35,
                                        'pre-printed qr' => 36,
                                        'ai qr codes' => 37,
                                        'credits per ai qr request' => 38,
                                        'short links' => 40,
                                        'short link quota' => 41,
                                        'monthly clicks' => 42,
                                        'advanced routing' => 43,
                                        'a/b destination testing' => 44,
                                        'bulk import' => 45,
                                        'bulk rows per import' => 46,
                                        'api and webhooks' => 47,
                                        'brand kit' => 50,
                                        'custom domains' => 51,
                                        'tracking pixels' => 52,
                                        'utm presets' => 53,
                                        'ai studio' => 60,
                                        'url shortener' => 61,
                                        'team member' => 70,
                                        'credits' => 80,
                                        'premium support' => 90,
                                    ];
                                    $orderedPlanFeatures = collect($plan['features'] ?? [])
                                        ->merge($promotedSubFeatures)
                                        ->filter(function ($item) use ($visibleSubFeatureCount) {
                                            $label = strtolower(trim((string) ($item['label'] ?? $item)));
                                            if ($label === '' || stripos($label, 'approval') !== false || stripos($label, 'approvals') !== false) {
                                                return false;
                                            }

                                            if ($label === 'url shortener') {
                                                return false;
                                            }

                                            if (($item['key'] ?? null) === 'access_feature' && $visibleSubFeatureCount($item) === 0) {
                                                return false;
                                            }

                                            if (($item['type'] ?? null) === 'group' && $visibleSubFeatureCount($item) === 0) {
                                                return false;
                                            }

                                            return true;
                                        })
                                        ->sortBy(fn ($item) => $featureOrder[strtolower(trim((string) ($item['label'] ?? $item)))] ?? 80)
                                        ->values();
                                @endphp
                                <ul class="space-y-4">
                                    @foreach ($orderedPlanFeatures as $feature)
                                        @php $featureLabel = $feature['label'] ?? $feature; @endphp
                                        <li class="flex items-start gap-3">
                                            <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ ($feature['check'] ?? true) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                                <i class="fa-regular {{ ($feature['check'] ?? true) ? 'fa-check' : 'fa-minus' }} text-xs"></i>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex min-w-0 items-center justify-between gap-2">
                                                    <p class="min-w-0 text-sm font-bold leading-6 text-slate-800">{{ __($featureLabel) }}</p>
                                                    @if (($feature['display'] ?? null) !== null && ($feature['display'] ?? '') !== '')
                                                        <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">{{ $feature['display'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if (!empty($feature['subfeature']))
                                                <div x-data="{ open: false, timer: null }" class="relative ml-1">
                                                    <button type="button" x-on:mouseenter="clearTimeout(timer); open = true" x-on:mouseleave="timer = setTimeout(() => open = false, 120)" class="relative z-20 flex h-6 w-6 items-center justify-center rounded-full bg-blue-50 text-xs text-blue-700 transition hover:bg-blue-100">
                                                        <i class="fa-light fa-info"></i>
                                                    </button>
                                                    <div x-show="open" x-on:mouseenter="clearTimeout(timer); open = true" x-on:mouseleave="timer = setTimeout(() => open = false, 120)" x-transition class="absolute right-0 top-full z-30 mt-3 max-h-[400px] w-[320px] max-w-[calc(100vw-2rem)] overflow-y-auto rounded-xl border border-slate-200 bg-white p-4 text-slate-800 shadow-xl" style="display: none;">
                                                        @foreach (($feature['subfeature'] ?? []) as $tabGroup)
                                                            @php
                                                                $visibleSubItems = collect($tabGroup['items'] ?? [])
                                                                    ->filter(function ($sub) use ($visibleFeatureLabels) {
                                                                        $label = strtolower(trim((string) ($sub['label'] ?? '')));
                                                                        return $label !== ''
                                                                            && ! in_array($label, $visibleFeatureLabels, true)
                                                                            && stripos($label, 'approval') === false
                                                                            && stripos($label, 'approvals') === false;
                                                                    });
                                                            @endphp
                                                            @continue($visibleSubItems->isEmpty())
                                                            <div class="mb-5 last:mb-0">
                                                                <div class="mb-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ __($tabGroup['tab_name']) }}</div>
                                                                <ul class="space-y-2 text-left text-sm">
                                                                    @foreach ($visibleSubItems as $sub)
                                                                        <li class="flex min-w-[260px] items-start justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2">
                                                                            <div class="flex min-w-0 flex-1 items-start gap-2">
                                                                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ ($sub['check'] ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                                                    <i class="fa-solid {{ ($sub['check'] ?? true) ? 'fa-check' : 'fa-xmark' }} text-[10px]"></i>
                                                                                </span>
                                                                                <span class="min-w-0 leading-5 text-slate-800">{{ __($sub['label']) }}</span>
                                                                            </div>
                                                                            @if (($sub['display'] ?? null) !== null && ($sub['display'] ?? '') !== '')
                                                                                <span class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-bold text-blue-700">{{ $sub['display'] }}</span>
                                                                            @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        </div>
                    @endforeach
                    @for ($i = $planCount; $i < 3; $i++)
                        <div class="hidden lg:block lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" style="display: none;"></div>
                    @endfor
                @endforeach
            </div>
        </div>

        @if ($featuredFaqs->isNotEmpty())
            <div class="mt-10 grid gap-4 lg:grid-cols-3">
                @foreach ($featuredFaqs->take(3) as $faq)
                    <article class="linkqr-card rounded-[1.2rem] p-5">
                        <p class="text-sm font-extrabold text-slate-950">{{ $faq->titleForLocale() }}</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $faq->contentPreview(150) }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endcomponent
