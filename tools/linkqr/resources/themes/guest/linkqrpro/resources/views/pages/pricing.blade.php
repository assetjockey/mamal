@php
    $pricing = \Pricing::plansWithFeatures();
    $planTypes = collect(\Modules\AdminPlans\Facades\Plan::getTypes())->filter(fn ($label, $key) => ! empty($pricing[$key] ?? []));
    $defaultType = (int) ($planTypes->keys()->first() ?? 1);
    $minCol = 3;
@endphp

@component(theme_view('layouts.marketing', 'guest'), ['pageTitle' => $pageTitle])
    <style>
        .linkqrpro-serif { font-family: Georgia, "Times New Roman", serif; }
        .linkqrpro-paper {
            background-color: #fbfaf6;
            background-image:
                radial-gradient(circle at 15% 8%, rgba(24,23,20,.05) 0 1px, transparent 1px),
                radial-gradient(circle at 76% 18%, rgba(24,23,20,.045) 0 1px, transparent 1px);
            background-size: 120px 120px, 170px 170px;
        }
        .lq-pricing-table {
            background:
                linear-gradient(90deg, rgba(255,197,45,.12), transparent 24%, rgba(207,238,253,.18) 74%, transparent),
                rgba(255,253,248,.92);
        }
        .lq-pricing-card {
            background:
                radial-gradient(circle at 92% 8%, rgba(255,197,45,.12), transparent 25%),
                #fffdf8;
        }
        .lq-pricing-card.is-featured {
            background:
                radial-gradient(circle at 12% 10%, rgba(255,197,45,.38), transparent 27%),
                radial-gradient(circle at 88% 14%, rgba(207,238,253,.72), transparent 32%),
                linear-gradient(180deg, #fffdf8 0%, #f8fbf1 54%, #fff8dd 100%);
            box-shadow: none;
        }
        .lq-pricing-ribbon {
            background: #ffc52d;
            color: #181714;
        }
        .lq-pricing-summary-card {
            background:
                radial-gradient(circle at 88% 12%, var(--summary-accent, rgba(255,197,45,.24)), transparent 34%),
                #fffdf8;
            transition: transform .24s ease, border-color .24s ease, box-shadow .24s ease;
        }
        .lq-pricing-summary-card:hover {
            transform: translateY(-4px);
            border-color: rgba(24,23,20,.18);
            box-shadow: 0 24px 70px -58px rgba(24,23,20,.62);
        }
        .lq-pricing-faq-card {
            background:
                linear-gradient(180deg, rgba(255,253,248,.94), rgba(255,248,221,.62)),
                #fffdf8;
        }
        @media (max-width: 639px) {
            .lq-pricing-tabs {
                display: grid;
                width: 100%;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                border-radius: 1rem;
            }
            .lq-pricing-tabs button {
                min-width: 0;
                padding-left: .75rem;
                padding-right: .75rem;
            }
        }
    </style>

    <section class="linkqrpro-paper px-5 py-14 sm:py-20" x-data="{ type: {{ $defaultType }} }">
        <div class="mx-auto max-w-7xl">
            <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-end">
                <div>
                    <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/72 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff] shadow-[0_14px_35px_-30px_rgba(24,23,20,.5)]">{{ __('Pricing') }}</span>
                    <h1 class="linkqrpro-serif mt-6 text-5xl leading-[0.98] tracking-[-0.025em] text-[#181714] sm:text-6xl">{{ __('Plans for creators, brands, and agencies.') }}</h1>
                </div>
                <div>
                    <p class="max-w-2xl text-base leading-8 text-[#6d685f]">{{ __('Choose the operating level you need today. Every plan keeps Bio, QR design, short-link controls, dynamic destination routing, and campaign analytics in the same workspace.') }}</p>
                    @if ($planTypes->isNotEmpty())
                        <div class="lq-pricing-tabs mt-6 inline-flex rounded-full border border-[#d8d3c7] bg-[#fffdf8]/88 p-1.5 shadow-[0_16px_42px_-34px_rgba(24,23,20,.55)]">
                            @foreach ($planTypes as $typeKey => $typeLabel)
                                <button type="button" x-on:click="type = {{ $typeKey }}" class="rounded-full px-5 py-2.5 text-sm font-extrabold transition" x-bind:class="type === {{ $typeKey }} ? 'bg-[#181714] text-white shadow-[0_14px_28px_-20px_rgba(24,23,20,.72)]' : 'text-[#6d685f] hover:bg-[#fff3d8] hover:text-[#181714]'">
                                    {{ __($typeLabel) }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="lq-pricing-table mt-10 overflow-hidden rounded-[1.75rem] border border-[#ded7ca] shadow-[0_34px_90px_-60px_rgba(24,23,20,.38)]">
                <div class="flex flex-wrap">
                    @foreach ($planTypes as $typeKey => $typeLabel)
                        @php
                            $plans = collect($pricing[$typeKey] ?? []);
                            $planCount = $plans->count();
                        @endphp

                        @foreach ($plans as $index => $plan)
                            @php
                                $isFreePlan = (bool) ($plan['free_plan'] ?? false);
                                $planTarget = $plan['model']->slug ?? $plan['id'];
                            @endphp

                            <div class="w-full lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" x-transition style="display: none; z-index: {{ 10 - $index }};">
                                <article class="lq-pricing-card relative flex h-full flex-col px-6 py-8 sm:px-8 {{ !empty($plan['featured']) ? 'is-featured' : '' }} {{ $index > 0 ? 'lg:border-l lg:border-[#e5dfd2]' : '' }}">
                                    @if (!empty($plan['featured']))
                                        <div class="absolute right-0 top-0 h-40 w-40 overflow-hidden">
                                            <div class="absolute top-6 -right-10 rotate-45">
                                                <span class="lq-pricing-ribbon px-12 py-1 text-xs font-extrabold uppercase tracking-[0.12em] shadow-md">{{ __('Featured') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <span class="mb-3 inline-block text-sm font-extrabold uppercase tracking-[0.16em] {{ !empty($plan['featured']) ? 'text-[#181714]' : 'text-[#8a867d]' }}">{{ __($plan['name'] ?? '-') }}</span>
                                    @if (!($plan['free_plan'] ?? false) && (int) ($plan['trial_day'] ?? 0) > 0)
                                        <span class="mb-3 inline-flex w-max items-center rounded-full bg-[#fff0c2] px-3 py-1 text-xs font-extrabold uppercase tracking-[0.14em] text-[#8a5a00]">{{ __(':days-day trial', ['days' => (int) $plan['trial_day']]) }}</span>
                                    @endif
                                    <p class="mb-6 min-h-[4.5rem] text-sm font-medium leading-7 text-[#6d685f]">{{ __($plan['desc'] ?? '') }}</p>
                                    <h2 class="mb-1 text-5xl font-black leading-tight tracking-[-0.07em] text-[#181714]">
                                        {{ $isFreePlan ? ($plan['currency_symbol'] ?: '$').'0' : ($plan['currency_symbol'] ?: '$').rtrim(rtrim(number_format((float) ($plan['price'] ?? 0), 2), '0'), '.') }}
                                        <span class="text-base font-bold tracking-normal text-[#8a867d]">/{{ strtolower($typeLabel) }}</span>
                                    </h2>
                                    <p class="mb-8 text-sm font-semibold text-[#6d685f]">{{ __('Billed') }} {{ $typeLabel }}</p>

                                    <a href="{{ route('payment.index', $planTarget) }}" class="mb-9 block w-full rounded-xl px-6 py-4 text-center text-sm font-extrabold transition hover:-translate-y-0.5 {{ !empty($plan['featured']) ? 'bg-[#181714] text-white shadow-[0_20px_42px_-28px_rgba(24,23,20,.65)]' : 'border-2 border-[#181714] bg-[#fffdf8] text-[#181714] shadow-[6px_6px_0_rgba(255,197,45,.36)] hover:bg-[#fff3d8]' }}">
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
                                            @php
                                                $featureLabel = $feature['label'] ?? $feature;
                                            @endphp
                                            <li class="flex items-start gap-3">
                                                <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ ($feature['check'] ?? true) ? 'bg-[#cef8dc] text-emerald-800' : 'bg-[#eef2f7] text-[#8a867d]' }}">
                                                    <i class="fa-regular {{ ($feature['check'] ?? true) ? 'fa-check' : 'fa-minus' }} text-xs"></i>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex min-w-0 items-center justify-between gap-2">
                                                        <p class="min-w-0 text-sm font-bold leading-6 text-[#181714]">{{ __($featureLabel) }}</p>
                                                        @if (($feature['display'] ?? null) !== null && ($feature['display'] ?? '') !== '')
                                                        <span class="shrink-0 rounded-full bg-[#fff3d8] px-2.5 py-1 text-[11px] font-extrabold text-[#181714]">{{ $feature['display'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if (!empty($feature['subfeature']))
                                                    <div x-data="{ open: false, timer: null }" class="relative ml-1">
                                                        <button type="button" x-on:mouseenter="clearTimeout(timer); open = true" x-on:mouseleave="timer = setTimeout(() => open = false, 120)" class="relative z-20 flex h-6 w-6 items-center justify-center rounded-full bg-[#fff3d8] text-xs text-[#181714] transition hover:bg-[#ffc52d]/40">
                                                            <i class="fa-light fa-info"></i>
                                                        </button>
                                                        <div x-show="open" x-on:mouseenter="clearTimeout(timer); open = true" x-on:mouseleave="timer = setTimeout(() => open = false, 120)" x-transition class="absolute right-0 top-full z-30 mt-3 max-h-[400px] w-[320px] max-w-[calc(100vw-2rem)] overflow-y-auto rounded-xl border border-[#ded7ca] bg-white p-4 text-[#181714] shadow-xl" style="display: none;">
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
                                                                    <div class="mb-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#8a867d]">{{ __($tabGroup['tab_name']) }}</div>
                                                                    <ul class="space-y-2 text-left text-sm">
                                                                        @foreach ($visibleSubItems as $sub)
                                                                            <li class="flex min-w-[260px] items-start justify-between gap-3 rounded-xl border border-[#eee7da] bg-[#fbfaf6] px-3 py-2">
                                                                                <div class="flex min-w-0 flex-1 items-start gap-2">
                                                                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ ($sub['check'] ?? true) ? 'bg-[#cef8dc] text-[11px] font-bold text-emerald-800' : 'bg-rose-100 text-[11px] font-bold text-rose-700' }}">
                                                                                        <i class="fa-solid {{ ($sub['check'] ?? true) ? 'fa-check' : 'fa-xmark' }}"></i>
                                                                                    </span>
                                                                                    <span class="min-w-0 leading-5 text-[#57534b]">{{ __($sub['label']) }}</span>
                                                                                </div>
                                                                                @if (($sub['display'] ?? null) !== null && ($sub['display'] ?? '') !== '')
                                                                                    <span class="shrink-0 rounded-full bg-[#fff3d8] px-2.5 py-1 text-[11px] font-extrabold text-[#181714]">{{ $sub['display'] }}</span>
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

                        @for ($i = $planCount; $i < $minCol; $i++)
                            <div class="hidden lg:block lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" style="display: none;"></div>
                        @endfor
                    @endforeach
                </div>
            </div>

            <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([['fa-light fa-id-card-clip', __('Bio pages'), __('Create public profiles with social, shop, booking, and file links.'), '#cfeefd'], ['fa-light fa-link-simple', __('Short links'), __('Create branded short URLs for campaigns, messages, ads, and offline material.'), '#fff3d8'], ['fa-light fa-qrcode', __('Dynamic QR'), __('Use one QR destination and adjust routing without reprinting.'), '#cef8dc'], ['fa-light fa-chart-simple', __('Analytics'), __('Read countries, cities, devices, campaigns, clicks, and scans.'), '#f7d1e6']] as $item)
                    <div class="lq-pricing-summary-card relative overflow-hidden rounded-[1.25rem] border border-[#ded7ca] p-6" style="--summary-accent: {{ $item[3] }};">
                        <div class="flex items-start justify-between gap-4">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-[#181714] shadow-[0_14px_32px_-26px_rgba(24,23,20,.75)]" style="background: {{ $item[3] }};">
                                <i class="{{ $item[0] }} text-xl"></i>
                            </span>
                            <span class="rounded-full border border-[#ded7ca] bg-white/78 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a867d]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="linkqrpro-serif mt-5 text-2xl leading-tight text-[#181714]">{{ $item[1] }}</p>
                        <p class="mt-3 text-sm leading-7 text-[#6d685f]">{{ $item[2] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($featuredFaqs->isNotEmpty())
                <div class="mt-10 grid gap-4 lg:grid-cols-3">
                    @foreach ($featuredFaqs->take(3) as $faq)
                        <article class="lq-pricing-faq-card rounded-[1.2rem] border border-[#ded7ca] p-5 shadow-[0_18px_54px_-48px_rgba(24,23,20,.46)]">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#181714] text-xs font-black text-[#fffdf8]">?</span>
                                <p class="pt-1 text-sm font-extrabold leading-6 text-[#181714]">{{ $faq->titleForLocale() }}</p>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-[#6d685f]">{{ $faq->contentPreview(150) }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endcomponent
