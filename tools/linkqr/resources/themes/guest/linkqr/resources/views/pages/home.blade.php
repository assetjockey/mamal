@extends(theme_view('layouts.marketing', 'guest'))

@section('content')
    @php
        $signupEnabled = auth_signup_enabled();
        $featureCards = [
            ['icon' => 'fa-light fa-link', 'title' => __('Link Bio pages'), 'body' => __('Build branded mobile pages for profiles, products, menus, files, lead forms, and review collection.'), 'metric' => __('Blocks, buttons, templates'), 'visual' => 'bio'],
            ['icon' => 'fa-light fa-link-simple', 'title' => __('Branded short links'), 'body' => __('Create short codes, attach QR previews, protect destinations, route traffic, and measure every click.'), 'metric' => __('Codes, QR, clicks'), 'visual' => 'domain'],
            ['icon' => 'fa-light fa-qrcode', 'title' => __('Dynamic QR campaigns'), 'body' => __('Edit QR destinations after printing and route visitors by country, device, active hours, or campaign dates.'), 'metric' => __('Rules, design, redirect'), 'visual' => 'qr'],
            ['icon' => 'fa-light fa-chart-line', 'title' => __('Campaign analytics'), 'body' => __('Track scans, short-link clicks, CTA events, top campaigns, devices, traffic sources, and unusual spikes.'), 'metric' => __('Events and alerts'), 'visual' => 'analytics'],
            ['icon' => 'fa-light fa-globe', 'title' => __('Custom domains'), 'body' => __('Guide customers through CNAME/TXT setup, DNS checks, SSL status, and branded short links.'), 'metric' => __('DNS and SSL checks'), 'visual' => 'domain'],
            ['icon' => 'fa-light fa-tags', 'title' => __('UTM presets'), 'body' => __('Create brand and campaign presets, then auto-append UTM attribution across Bio links and QR campaigns.'), 'metric' => __('Brand attribution'), 'visual' => 'utm'],
            ['icon' => 'fa-light fa-users-gear', 'title' => __('Agency workspace'), 'body' => __('Manage clients, team permissions, activity logs, and collaboration workflows from one dashboard.'), 'metric' => __('Roles and clients'), 'visual' => 'team'],
            ['icon' => 'fa-light fa-file-csv', 'title' => __('Bulk short links'), 'body' => __('Import CSV rows, preview mapped destinations, apply brand defaults, and create campaigns faster.'), 'metric' => __('CSV import'), 'visual' => 'workspace'],
            ['icon' => 'fa-light fa-code', 'title' => __('API and webhooks'), 'body' => __('Issue API keys, create links from external systems, and send created or clicked events to your stack.'), 'metric' => __('Developer access'), 'visual' => 'analytics'],
        ];
        $workflow = [
            ['label' => __('Create'), 'title' => __('Launch Bio pages, QR campaigns, and short links'), 'body' => __('Start with a Link Bio page, QR type, branded short code, template, or dynamic redirect rule.')],
            ['label' => __('Route'), 'title' => __('Send visitors to the right destination'), 'body' => __('Target by country, device, hour, date range, or active campaign schedule.')],
            ['label' => __('Measure'), 'title' => __('Improve campaigns with clean signals'), 'body' => __('Read scan volume, click intent, CTA events, broken links, SSL status, and campaign health alerts.')],
        ];
        $segments = [
            ['label' => __('Retail'), 'value' => '38%', 'color' => '#2454E8'],
            ['label' => __('Restaurants'), 'value' => '24%', 'color' => '#0F766E'],
            ['label' => __('Events'), 'value' => '21%', 'color' => '#D97706'],
            ['label' => __('Creators'), 'value' => '17%', 'color' => '#7C3AED'],
        ];
        $useCases = [
            ['title' => __('Retail checkout'), 'label' => __('Scan to product, promo, or loyalty page'), 'visual' => 'retail', 'icon' => 'fa-light fa-link'],
            ['title' => __('Campaign analytics'), 'label' => __('Know which code, channel, and CTA performs'), 'visual' => 'analytics', 'icon' => 'fa-light fa-chart-line'],
            ['title' => __('Agency workflow'), 'label' => __('Manage brands, clients, approvals, and reports'), 'visual' => 'workspace', 'icon' => 'fa-light fa-users-gear'],
        ];
        $operationCards = [
            ['icon' => 'fa-light fa-route', 'title' => __('Dynamic redirect rules'), 'body' => __('Build country, device, active-hour, and campaign schedule rules without editing raw JSON.'), 'visual' => 'rules', 'label' => __('Routing logic')],
            ['icon' => 'fa-light fa-bell', 'title' => __('Analytics alerts'), 'body' => __('Notify teams when scans spike, links die, custom domains fail, or SSL status needs attention.'), 'visual' => 'alerts', 'label' => __('Health signals')],
            ['icon' => 'fa-light fa-layer-group', 'title' => __('Client-ready workspaces'), 'body' => __('Separate clients, brands, domains, UTM presets, team permissions, and audit activity cleanly.'), 'visual' => 'workspace', 'label' => __('Agency control')],
        ];
        $linkBioToolkit = [
            ['icon' => 'fa-light fa-link', 'title' => __('Pages'), 'body' => __('Publish mobile-first Bio pages for products, profiles, menus, files, reviews, and lead flows.'), 'meta' => __('Mobile page builder'), 'metric' => '12', 'metricLabel' => __('blocks')],
            ['icon' => 'fa-light fa-swatchbook', 'title' => __('Templates'), 'body' => __('Start from branded layouts for creators, agencies, restaurants, stores, and campaign pages.'), 'meta' => __('Reusable layouts'), 'metric' => '30+', 'metricLabel' => __('layouts')],
            ['icon' => 'fa-light fa-sparkles', 'title' => __('AI Bio Assistant'), 'body' => __('Draft page copy, button labels, sections, and campaign ideas faster from your business context.'), 'meta' => __('AI assisted setup'), 'metric' => 'AI', 'metricLabel' => __('drafts')],
            ['icon' => 'fa-light fa-flask', 'title' => __('A/B Tests'), 'body' => __('Test page variants and learn which profile, offer, or CTA gets stronger click intent.'), 'meta' => __('Variant testing'), 'metric' => 'A/B', 'metricLabel' => __('variants')],
            ['icon' => 'fa-light fa-chart-line', 'title' => __('Analytics'), 'body' => __('Track Bio views, clicks, CTA events, source signals, devices, and connected QR performance.'), 'meta' => __('Readable reports'), 'metric' => 'Live', 'metricLabel' => __('events')],
        ];
        $fallbackFaqs = [
            ['title' => __('Can I edit a QR code after it is printed?'), 'content' => __('Yes. Dynamic QR campaigns keep the printed code stable while you update destinations, schedules, device rules, and campaign URLs from the dashboard.')],
            ['title' => __('Do Bio pages and QR campaigns share analytics?'), 'content' => __('They are connected. You can read scans, clicks, CTA events, traffic source, devices, and attribution signals without splitting reports across tools.')],
            ['title' => __('Can agencies manage multiple client brands?'), 'content' => __('Yes. Workspaces, role permissions, custom domains, UTM presets, activity logs, and client-ready campaign records are designed for agency operations.')],
            ['title' => __('Do custom domains include DNS and SSL checks?'), 'content' => __('The product guides CNAME/TXT setup and gives customers clear DNS and SSL status so branded short links are easier to launch.')],
        ];
        $fallbackBlogs = [
            ['title' => __('How to plan a QR campaign that stays editable after print'), 'excerpt' => __('A practical workflow for destinations, routing rules, UTM presets, and scan reports before the first sticker goes live.'), 'visual' => 'rules', 'label' => __('Campaign guide')],
            ['title' => __('What brands should track beyond raw QR scans'), 'excerpt' => __('Turn scans into useful signals with device mix, traffic source, CTA clicks, broken-link checks, and unusual spike alerts.'), 'visual' => 'analytics', 'label' => __('Analytics')],
            ['title' => __('A cleaner client workspace for Link Bio and QR operations'), 'excerpt' => __('How agencies can separate brands, roles, domains, UTM presets, and approval activity without adding more tools.'), 'visual' => 'workspace', 'label' => __('Agency ops')],
        ];
        $qrTypeCatalog = class_exists(\Modules\AppQRCodes\Models\AppQrCode::class)
            ? collect(\Modules\AppQRCodes\Models\AppQrCode::typeCatalog())
            : collect();
        $qrTypeColors = ['#2563EB', '#0F766E', '#64748B'];
        $qrTypeFeatured = $qrTypeCatalog->take(6)->values();
    @endphp

    <section class="linkqr-shell pb-2 pt-6 lg:pb-4 lg:pt-8">
        <div class="linkqr-hero-stage linkqr-main-hero rounded-[2rem] px-5 py-7 sm:px-7 lg:rounded-[2.4rem] lg:px-8 lg:py-8 xl:px-10">
        <div class="grid gap-8 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,0.98fr)] lg:items-center xl:gap-10">
            <div class="max-w-[46rem]">
                <span data-reveal class="inline-flex items-center gap-2 rounded-full border bg-white/88 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-700 shadow-sm backdrop-blur" style="border-color: rgba(var(--theme-border-color-rgb),0.82);">
                    <i class="fa-light fa-sparkles"></i>
                    {{ __('Bio links, QR codes, short links, and analytics') }}
                </span>

                <h1 data-reveal class="mt-6 max-w-[44rem] pb-2 text-4xl font-extrabold leading-[1.08] tracking-[-0.035em] text-slate-950 sm:text-5xl lg:text-[4.15rem] xl:text-[4.55rem]">
                    {{ __('Turn short links') }}
                    <span class="block linkqr-gradient-text">{{ __('into measurable growth.') }}</span>
                </h1>

                <p data-reveal class="mt-6 max-w-xl text-base leading-8 text-slate-600 sm:text-lg">
                    {{ __('Launch Bio pages, dynamic QR campaigns, branded short links, custom domains, UTM attribution, and campaign analytics from one clean workspace built for brands and agencies.') }}
                </p>

                <div data-reveal class="mt-6 flex flex-wrap gap-2">
                    @foreach ([['fa-light fa-link', __('Bio pages')], ['fa-light fa-qrcode', __('Dynamic QR')], ['fa-light fa-link-simple', __('Short links')], ['fa-light fa-route', __('Smart routing')], ['fa-light fa-chart-line', __('Analytics')]] as $pill)
                        <span class="inline-flex items-center gap-2 rounded-full border bg-white/70 px-3 py-2 text-xs font-extrabold text-slate-700 shadow-sm backdrop-blur" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                            <i class="{{ $pill[0] }} text-blue-600"></i>
                            {{ $pill[1] }}
                        </span>
                    @endforeach
                </div>

                <div data-reveal class="mt-7 flex flex-wrap gap-3">
                    @if ($signupEnabled)
                        <a href="{{ route('register') }}" class="linkqr-button-primary linkqr-sheen inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-6 py-4 text-sm font-bold">
                            {{ __('Start free') }}
                            <i class="fa-light fa-arrow-right ml-2"></i>
                        </a>
                    @endif
                    <a href="#features" class="linkqr-button-secondary linkqr-sheen inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-6 py-4 text-sm font-bold">
                        <i class="fa-light fa-grid-2 mr-2"></i>
                        {{ __('Explore features') }}
                    </a>
                </div>

                <div data-reveal class="mt-8 grid max-w-2xl grid-cols-3 gap-3">
                    @foreach ([['42K+', __('links tracked'), '#2454E8'], ['99.9%', __('redirect uptime'), '#0F766E'], ['10+', __('growth tools'), '#D97706']] as $stat)
                        <div class="linkqr-hero-stat linkqr-hover-lift rounded-[1rem] px-4 py-4" style="--hero-accent: {{ $stat[2] }}; --hero-delay: {{ $loop->index * 140 }}ms;">
                            <p class="text-2xl font-extrabold tracking-[-0.05em] text-slate-950">{{ $stat[0] }}</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $stat[1] }}</p>
                        </div>
                    @endforeach
                </div>

                <div data-reveal class="mt-5 grid max-w-2xl gap-2 sm:grid-cols-2">
                    @foreach ([['fa-light fa-link-simple', __('Short links'), __('Custom codes, QR, clicks'), '#2454E8'], ['fa-light fa-route', __('Dynamic routing'), __('Country, device, A/B'), '#0F766E'], ['fa-light fa-globe', __('Branded domains'), __('DNS and SSL checks'), '#D97706'], ['fa-light fa-tags', __('UTM presets'), __('Auto append attribution'), '#7C3AED']] as $item)
                        <div class="linkqr-hero-chip linkqr-hover-lift rounded-[1rem] px-3 py-3" style="--hero-accent: {{ $item[3] }}; --hero-delay: {{ $loop->index * 120 }}ms;">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.8rem] bg-white text-slate-800 shadow-sm">
                                    <i class="{{ $item[0] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-slate-950">{{ $item[1] }}</p>
                                    <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">{{ $item[2] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div data-reveal class="relative mx-auto w-full max-w-[42rem] lg:mr-0 xl:max-w-[45rem]">
                <div class="linkqr-hero-float-card absolute left-4 top-16 z-30 hidden rounded-[1.1rem] border bg-white/95 px-4 py-3 shadow-2xl backdrop-blur xl:block" style="border-color: rgba(var(--theme-border-color-rgb),0.82); --hero-delay: 180ms;">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700">
                            <i class="fa-light fa-qrcode"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ __('Today clicks') }}</p>
                            <p class="text-2xl font-extrabold tracking-[-0.05em] text-slate-950">18,420</p>
                        </div>
                    </div>
                </div>
                <div class="linkqr-hero-float-card absolute bottom-10 right-4 z-30 hidden rounded-[1.1rem] border bg-white/95 px-4 py-3 shadow-2xl backdrop-blur xl:block" style="border-color: rgba(var(--theme-border-color-rgb),0.82); --hero-delay: 520ms;">
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-emerald-600">{{ __('SSL verified') }}</p>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <p class="text-sm font-extrabold text-slate-700">go.brand.com</p>
                    </div>
                </div>

                <div class="linkqr-hero-dashboard relative mx-auto max-w-full rounded-[2rem] border bg-white/80 p-3 shadow-[0_38px_120px_-60px_rgba(15,23,42,0.58)] backdrop-blur" style="border-color: rgba(var(--theme-border-color-rgb),0.85);">
                    <div class="absolute -inset-3 -z-10 rounded-[2.4rem] bg-gradient-to-br from-blue-500/18 via-cyan-400/14 to-amber-300/18 blur-xl"></div>
                    <div class="overflow-hidden rounded-[1.7rem] bg-slate-950 text-white ring-1 ring-white/10">
                        <div class="flex items-center justify-between border-b border-white/8 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-300"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            </div>
                            <div class="hidden items-center gap-2 rounded-full bg-white/6 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-white/50 sm:flex">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                {{ __('Live link OS') }}
                            </div>
                        </div>

                        <div class="grid min-h-[26.5rem] grid-cols-[4.2rem_minmax(0,1fr)]">
                            <aside class="border-r border-white/8 bg-white/[0.025] px-3 py-4">
                                <div class="grid gap-3">
                                    @foreach (['fa-light fa-house', 'fa-light fa-link', 'fa-light fa-qrcode', 'fa-light fa-chart-line', 'fa-light fa-gear'] as $icon)
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-[0.9rem] {{ $loop->index === 2 ? 'bg-blue-500 text-white' : 'bg-white/[0.05] text-white/38' }}">
                                            <i class="{{ $icon }}"></i>
                                        </span>
                                    @endforeach
                                </div>
                            </aside>

                            <div class="p-4">
                                <div class="grid gap-4 xl:grid-cols-[1.08fr_0.92fr]">
                                    <section class="rounded-[1.25rem] border border-white/8 bg-white/[0.045] p-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-blue-200/70">{{ __('Campaign') }}</p>
                                                <h3 class="mt-1 text-lg font-extrabold tracking-[-0.035em] xl:text-xl">{{ __('Short Link Launch') }}</h3>
                                            </div>
                                            <span class="rounded-full bg-emerald-400/12 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-emerald-200">{{ __('Active') }}</span>
                                        </div>

                                        <div class="mt-5 grid gap-4 sm:grid-cols-[8.8rem_minmax(0,1fr)]">
                                            <div class="relative rounded-[1.1rem] border border-blue-300/20 bg-[#0f1b30] p-4 shadow-[0_18px_45px_-34px_rgba(37,99,235,0.74)]">
                                                <span class="absolute -left-3 -top-3 inline-flex h-11 w-11 items-center justify-center rounded-[0.9rem] border border-blue-300/14 bg-[#152847] text-blue-400 shadow-lg">
                                                    <i class="fa-light fa-qrcode text-sm"></i>
                                                </span>
                                                <div class="grid aspect-square grid-cols-7 grid-rows-7 gap-1 rounded-[0.65rem]">
                                                    @foreach ([1,1,1,0,1,1,1,1,0,1,1,0,0,1,1,1,0,1,0,1,1,0,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1] as $bit)
                                                        <span class="{{ $bit ? 'linkqr-hero-qr-dot bg-slate-200' : 'bg-[#17243a]' }} rounded-[0.18rem]" style="--hero-delay: {{ $loop->index * 22 }}ms;"></span>
                                                    @endforeach
                                                </div>
                                                <span class="absolute -right-3 bottom-3 rounded-full border border-slate-200/70 bg-white px-3 py-1.5 text-xs font-extrabold text-slate-950 shadow-lg">17:00</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach ([['Short URL', '/s/summer'], ['Rule', 'Mobile + VN'], ['UTM', 'launch_2026']] as $row)
                                                    <div class="rounded-[0.95rem] bg-white/[0.055] px-3 py-2.5">
                                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-white/34">{{ $row[0] }}</p>
                                                        <p class="mt-1 break-words text-xs font-extrabold leading-5 text-white/82 xl:text-sm">{{ $row[1] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </section>

                                    <section class="rounded-[1.25rem] border border-white/8 bg-white/[0.045] p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/38">{{ __('Click analytics') }}</p>
                                            <span class="text-xs font-extrabold text-cyan-200">+18.6%</span>
                                        </div>
                                        <div class="mt-4 grid grid-cols-2 gap-3">
                                            @foreach ([['Clicks', '8,214'], ['Scans', '2,907'], ['CTA', '684'], ['CTR', '35%']] as $metric)
                                                <div class="rounded-[1rem] bg-white/[0.055] p-3">
                                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-white/34">{{ $metric[0] }}</p>
                                                    <p class="mt-2 text-2xl font-extrabold tracking-[-0.04em]">{{ $metric[1] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="mt-5 flex h-24 items-end gap-2">
                                            @foreach ([46, 72, 54, 82, 66, 90, 74] as $bar)
                                                <span class="linkqr-visual-bar" style="height: {{ $bar }}%; animation-delay: {{ $loop->index * 110 }}ms;"></span>
                                            @endforeach
                                        </div>
                                    </section>
                                </div>

                                <div class="mt-4 grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                                    <section class="rounded-[1.25rem] border border-white/8 bg-white/[0.045] p-4">
                                        <div class="flex items-center justify-between text-xs font-bold uppercase tracking-[0.14em] text-white/40">
                                            <span>{{ __('Routing rules') }}</span>
                                            <span class="text-emerald-300">{{ __('3 active') }}</span>
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            @foreach ([__('VN mobile -> /vn-offer'), __('Desktop after 17:00 -> /booking'), __('A/B split -> /variant-b')] as $rule)
                                                <div class="rounded-[0.8rem] bg-white/[0.055] px-3 py-2 text-sm font-semibold text-white/72">{{ $rule }}</div>
                                            @endforeach
                                        </div>
                                    </section>

                                    <section class="rounded-[1.25rem] border border-white/8 bg-white/[0.045] p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-white/38">{{ __('Health monitor') }}</p>
                                            <span class="rounded-full bg-emerald-400/12 px-3 py-1 text-[10px] font-extrabold text-emerald-200">{{ __('Clean') }}</span>
                                        </div>
                                        <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                            @foreach ([['fa-light fa-link', __('Short links OK')], ['fa-light fa-lock', __('SSL OK')], ['fa-light fa-bullseye-pointer', __('Pixels ON')]] as $check)
                                                <div class="rounded-[0.9rem] bg-white/[0.055] p-3">
                                                    <i class="{{ $check[0] }} text-cyan-200"></i>
                                                    <p class="mt-2 text-xs font-extrabold text-white/72">{{ $check[1] }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    <section class="linkqr-workflow-section pb-12 pt-0">
        @php
            $marqueeItems = [
                ['fa-light fa-link-simple', __('Short links'), __('Custom codes and QR')],
                ['fa-light fa-route', __('Dynamic routing'), __('Country, device, schedule')],
                ['fa-light fa-globe', __('Custom domains'), __('DNS and SSL checks')],
                ['fa-light fa-tags', __('UTM presets'), __('Auto attribution')],
                ['fa-light fa-bullseye-pointer', __('Tracking pixels'), __('Retargeting signals')],
                ['fa-light fa-chart-line', __('Campaign analytics'), __('Clicks and scans')],
                ['fa-light fa-bell', __('Health alerts'), __('Spikes, SSL, dead links')],
                ['fa-light fa-users-gear', __('Agency workspace'), __('Roles and clients')],
                ['fa-light fa-link', __('Bio pages'), __('Blocks and buttons')],
            ];
        @endphp
        <div data-reveal>
            <div class="mx-auto max-w-3xl px-5 text-center">
                <span class="inline-flex rounded-full border bg-white/78 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.18em] text-blue-700 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.78);">{{ __('Connected workflow') }}</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-[-0.05em] text-slate-950 sm:text-4xl">
                    {{ __('Everything moves with your Bio, QR, and short-link campaigns.') }}
                </h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('Create, shorten, route, brand, attribute, monitor, and collaborate without sending customers through disconnected tools.') }}</p>
            </div>

            <div class="linkqr-marquee mt-6">
                <div class="linkqr-marquee-track">
                    @foreach (array_merge($marqueeItems, $marqueeItems) as $item)
                        <div class="linkqr-marquee-card rounded-[1rem] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700">
                                    <i class="{{ $item[0] }}"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-950">{{ $item[1] }}</p>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $item[2] }}</p>
                                </div>
                                <span class="ml-auto h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_5px_rgba(52,211,153,0.08)]"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="linkqr-marquee mt-4">
                <div class="linkqr-marquee-track is-reverse">
                    @foreach (array_merge(array_reverse($marqueeItems), array_reverse($marqueeItems)) as $item)
                        <div class="linkqr-marquee-card rounded-[1rem] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] bg-amber-50 text-amber-700">
                                    <i class="{{ $item[0] }}"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-950">{{ $item[1] }}</p>
                                    <p class="mt-0.5 text-xs font-semibold text-slate-500">{{ $item[2] }}</p>
                                </div>
                                <span class="ml-auto h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_0_5px_rgba(103,232,249,0.08)]"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if ($qrTypeCatalog->isNotEmpty())
        <section class="linkqr-shell linkqr-section pt-0">
            <div data-reveal class="linkqr-card overflow-hidden rounded-[1.35rem]">
                <div class="grid gap-0 lg:grid-cols-[0.92fr_1.08fr]">
                    <div class="relative isolate overflow-hidden border-b p-6 sm:p-8 lg:border-b-0 lg:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.68); background: linear-gradient(180deg, rgba(var(--theme-surface-bg-rgb),0.94), rgba(var(--theme-surface-bg-rgb),0.76));">

                        <span class="inline-flex rounded-full border px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em]" style="border-color: rgba(var(--theme-border-color-rgb),0.74); background: rgba(var(--theme-border-color-rgb),0.07); color: var(--theme-muted-text-color);">
                            {{ __('QR type library') }}
                        </span>
                        <h2 class="mt-5 max-w-xl text-4xl font-extrabold tracking-[-0.055em] text-slate-950 sm:text-5xl">
                            {{ __('Start from the right QR purpose.') }}
                        </h2>
                        <p class="mt-4 max-w-lg text-sm leading-7 text-slate-600">
                            {{ __('Dynamic campaigns, static contact payloads, payments, messaging, menus, files, reviews, and booking flows are ready from the first screen.') }}
                        </p>

                        <div class="mt-7 grid grid-cols-2 gap-3">
                            @foreach ($qrTypeFeatured as $type)
                                @php $color = $qrTypeColors[$loop->index % count($qrTypeColors)]; @endphp
                                <div class="rounded-[0.9rem] border p-3" style="--qr-type-accent: {{ $color }}; border-color: rgba(var(--theme-border-color-rgb),0.7); background: rgba(var(--theme-surface-bg-rgb),0.62);">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.75rem]" style="background: color-mix(in srgb, var(--qr-type-accent) 10%, transparent); color: var(--qr-type-accent);">
                                            <i class="fa-light {{ $type['icon'] }}"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-extrabold text-slate-950">{{ $type['label'] }}</p>
                                            <p class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.14em]" style="color: var(--theme-muted-text-color);">{{ $type['kind'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-7 flex flex-wrap gap-3">
                            @auth
                                <a href="{{ route('portal.qr-codes.create') }}" class="linkqr-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                                    {{ __('Create QR') }}
                                    <i class="fa-light fa-arrow-right ml-2"></i>
                                </a>
                            @else
                                @if ($signupEnabled)
                                    <a href="{{ route('register') }}" class="linkqr-button-primary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                                        {{ __('Start free') }}
                                        <i class="fa-light fa-arrow-right ml-2"></i>
                                    </a>
                                @endif
                            @endauth
                            <a href="#pricing" class="linkqr-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                                {{ __('View plans') }}
                            </a>
                        </div>
                    </div>

                    <div class="relative overflow-hidden p-4 sm:p-5 lg:p-6" style="background: rgba(var(--theme-border-color-rgb),0.025);">
                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($qrTypeCatalog as $type)
                                @php $color = $qrTypeColors[$loop->index % count($qrTypeColors)]; @endphp
                                <article
                                    data-reveal
                                    class="group relative min-h-[4.85rem] overflow-hidden rounded-[0.85rem] border p-3 transition hover:-translate-y-0.5"
                                    style="border-color: rgba(var(--theme-border-color-rgb),0.68); background: rgba(var(--theme-surface-bg-rgb),0.78);"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.75rem] transition group-hover:scale-105" style="background: {{ $color }}12; color: {{ $color }};">
                                            <i class="fa-light {{ $type['icon'] }}"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-sm font-extrabold tracking-[-0.02em] text-slate-950">{{ $type['label'] }}</h3>
                                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-[0.12em]" style="background: rgba(var(--theme-border-color-rgb),0.09); color: var(--theme-muted-text-color);">
                                                {{ $type['kind'] }}
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="linkqr-shell pb-20 pt-0">
        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($useCases as $case)
                @php
                    $caseAccent = ['#2454E8', '#D97706', '#7C3AED'][$loop->index] ?? '#2454E8';
                    $caseMetrics = [
                        [['8.2K', __('scans')], ['684', __('cta')]],
                        [['35%', __('ctr')], ['2.9K', __('clicks')]],
                        [['12', __('clients')], ['4', __('roles')]],
                    ][$loop->index] ?? [['8.2K', __('scans')], ['684', __('cta')]];
                @endphp
                <article data-reveal class="linkqr-case-tile linkqr-hover-lift flex min-h-[26.75rem] flex-col rounded-[1.55rem] p-4" style="--case-accent: {{ $caseAccent }};">
                    <div class="flex items-start justify-between gap-4">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.95rem] bg-white/88 text-blue-700 shadow-sm">
                            <i class="{{ $case['icon'] }}"></i>
                        </span>
                        <span class="rounded-full bg-white/78 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-600 shadow-sm">{{ $case['label'] }}</span>
                    </div>

                    <div class="linkqr-case-window mt-7 flex h-40 items-center rounded-[1.25rem] p-4">
                        @if ($loop->index === 1)
                            <div class="flex h-24 w-full items-end gap-2">
                                @foreach ([44, 72, 48, 86, 60, 78, 54] as $bar)
                                    <span class="linkqr-visual-bar" style="height: {{ $bar }}%; animation-delay: {{ $loop->index * 120 }}ms;"></span>
                                @endforeach
                            </div>
                        @elseif ($loop->index === 2)
                            <div class="grid w-full gap-2">
                                @foreach ([__('Client workspace'), __('Approval queue'), __('Shared report')] as $row)
                                    <div class="flex items-center gap-3 rounded-[0.8rem] bg-white/82 px-3 py-2">
                                        <span class="h-7 w-7 rounded-full bg-gradient-to-br from-blue-600 to-teal-500"></span>
                                        <span class="h-2.5 flex-1 rounded-full bg-slate-200"></span>
                                        <span class="text-[10px] font-extrabold text-violet-700">{{ $loop->first ? __('Owner') : __('Live') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="grid w-full grid-cols-[1fr_7rem] gap-4">
                                <div>
                                    <div class="h-3 w-16 rounded-full bg-slate-200"></div>
                                    <div class="mt-5 h-12 w-12 rounded-full bg-gradient-to-br from-blue-600 to-teal-500"></div>
                                    <div class="mt-4 h-2.5 w-4/5 rounded-full bg-slate-200"></div>
                                    <div class="mt-2 h-2.5 w-3/5 rounded-full bg-slate-200"></div>
                                </div>
                                <div class="grid aspect-square grid-cols-7 grid-rows-7 gap-1 rounded-[0.95rem] bg-white p-3">
                                    @foreach ([1,1,1,0,1,1,1,1,0,1,1,0,0,1,1,1,0,1,0,1,1,0,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1] as $bit)
                                        <span class="{{ $bit ? 'linkqr-hero-qr-dot bg-slate-900' : 'bg-transparent' }} rounded-[0.14rem]" style="--hero-delay: {{ $loop->index * 18 }}ms;"></span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-auto pt-5">
                        <h3 class="text-2xl font-extrabold tracking-[-0.045em] text-slate-950">{{ $case['title'] }}</h3>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach ($caseMetrics as $metric)
                                <div class="linkqr-case-metric rounded-[0.95rem] px-3 py-3">
                                    <p class="text-xl font-extrabold tracking-[-0.04em] text-slate-950">{{ $metric[0] }}</p>
                                    <p class="mt-0.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">{{ $metric[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section id="features" class="linkqr-shell linkqr-section scroll-mt-44 pt-20 lg:pt-24">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl">
                <p data-reveal class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Core platform') }}</p>
                <h2 data-reveal class="mt-3 text-4xl font-extrabold tracking-[-0.055em] text-slate-950 md:text-5xl">{{ __('Everything a modern link and QR business needs.') }}</h2>
            </div>
            <p data-reveal class="max-w-xl text-sm leading-7 text-slate-600">{{ __('Keep the product focused: Bio pages, short-link management, QR campaigns, attribution, and customer-ready operations.') }}</p>
        </div>

        <div data-reveal class="mt-10 grid gap-5 lg:grid-cols-[1.12fr_0.88fr]">
            <div class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.65rem]">
                <div class="relative h-[24rem]">
                    @include(theme_view('partials.visual-card', 'guest'), [
                        'type' => 'workspace',
                        'icon' => 'fa-light fa-shield-check',
                    ])
                    <div class="linkqr-visual-copy absolute bottom-6 left-6 right-6 z-10 max-w-xl text-white">
                        <span class="linkqr-glass-badge inline-flex items-center gap-2 rounded-full px-4 py-2 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-700">
                            <i class="fa-light fa-shield-check text-blue-700"></i>
                            {{ __('Campaign operations') }}
                        </span>
                        <h3 class="mt-4 text-4xl font-extrabold leading-tight tracking-[-0.055em]">{{ __('One workspace for destinations, attribution, and scan health.') }}</h3>
                    </div>
                </div>
            </div>

            <div class="grid gap-4">
                @foreach ([
                    ['icon' => 'fa-light fa-link', 'title' => __('Bio pages stay connected'), 'body' => __('Each Bio page can ship with its own branded QR, short link, and measurable buttons.'), 'tone' => 'from-blue-50 to-cyan-50', 'iconBg' => 'bg-blue-100 text-blue-700'],
                    ['icon' => 'fa-light fa-link-simple', 'title' => __('Short links stay flexible'), 'body' => __('Change destinations, passwords, limits, QR previews, and routing rules after publishing.'), 'tone' => 'from-violet-50 to-blue-50', 'iconBg' => 'bg-violet-100 text-violet-700'],
                    ['icon' => 'fa-light fa-chart-mixed', 'title' => __('Reports stay readable'), 'body' => __('Scans, clicks, traffic source, CTA events, and alerts live beside the campaign.'), 'tone' => 'from-amber-50 to-emerald-50', 'iconBg' => 'bg-amber-100 text-amber-700'],
                ] as $item)
                    <article class="linkqr-card linkqr-hover-lift rounded-[1.2rem] bg-gradient-to-br {{ $item['tone'] }} p-5">
                        <div class="flex gap-4">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[0.9rem] {{ $item['iconBg'] }}">
                                <i class="{{ $item['icon'] }}"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-extrabold tracking-[-0.025em] text-slate-950">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['body'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <div data-reveal class="mt-10 overflow-hidden rounded-[1.45rem] border" style="border-color: rgba(var(--theme-border-color-rgb),0.72); background: linear-gradient(135deg, rgba(var(--theme-surface-bg-rgb),0.92), rgba(var(--theme-border-color-rgb),0.045));">
            <div class="grid gap-0 lg:grid-cols-[0.82fr_1.18fr]">
                <div class="relative isolate overflow-hidden border-b p-6 sm:p-8 lg:border-b-0 lg:border-r" style="border-color: rgba(var(--theme-border-color-rgb),0.68);">
                    <div class="absolute inset-0 -z-10 opacity-60" style="background-image: linear-gradient(rgba(var(--theme-border-color-rgb),0.08) 1px, transparent 1px), linear-gradient(90deg, rgba(var(--theme-border-color-rgb),0.08) 1px, transparent 1px); background-size: 28px 28px;"></div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Link Bio toolkit') }}</p>
                    <h3 class="mt-3 text-3xl font-extrabold tracking-[-0.05em] text-slate-950">{{ __('Build, test, and measure every Bio page.') }}</h3>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600">{{ __('The Bio module is more than a profile link: pages, templates, AI setup help, experiments, and analytics live together.') }}</p>

                    <div class="mt-7 grid gap-4 sm:grid-cols-[12rem_minmax(0,1fr)] sm:items-center">
                        <div class="mx-auto w-full max-w-[12rem] rounded-[1.45rem] border bg-white/84 p-3 shadow-[0_28px_76px_-58px_rgba(15,23,42,0.46)]" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                            <div class="rounded-[1.15rem] bg-slate-950 p-3 text-white">
                                <div class="mx-auto h-1.5 w-10 rounded-full bg-white/20"></div>
                                <div class="mt-4 flex flex-col items-center text-center">
                                    <span class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-600 to-teal-500"></span>
                                    <p class="mt-3 text-sm font-extrabold">{{ __('Cafe Studio') }}</p>
                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white/42">{{ __('Bio page') }}</p>
                                </div>
                                <div class="mt-4 space-y-2">
                                    @foreach ([__('Menu'), __('Book table'), __('Reviews')] as $button)
                                        <div class="rounded-[0.75rem] bg-white/9 px-3 py-2 text-center text-[11px] font-bold text-white/76">{{ $button }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @foreach ([['fa-light fa-wand-magic-sparkles', __('AI draft ready'), __('Headline, buttons, and sections')], ['fa-light fa-flask', __('Variant B live'), __('CTA test running')], ['fa-light fa-chart-line', __('Click rate +18%'), __('Analytics connected')]] as $signal)
                                <div class="flex items-center gap-3 rounded-[0.95rem] border bg-white/72 px-3 py-3" style="border-color: rgba(var(--theme-border-color-rgb),0.64);">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[0.75rem] bg-blue-50 text-blue-700">
                                        <i class="{{ $signal[0] }}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-slate-950">{{ $signal[1] }}</p>
                                        <p class="mt-0.5 truncate text-xs font-semibold text-slate-500">{{ $signal[2] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($linkBioToolkit as $item)
                        <article class="group rounded-[1rem] border p-4 transition hover:-translate-y-1 hover:shadow-[0_26px_70px_-54px_rgba(15,23,42,0.42)]" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background: rgba(var(--theme-surface-bg-rgb),0.72);">
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700 transition group-hover:scale-105">
                                    <i class="{{ $item['icon'] }}"></i>
                                </span>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.12em]" style="background: rgba(var(--theme-border-color-rgb),0.09); color: var(--theme-muted-text-color);">{{ $item['metric'] }}</span>
                            </div>
                            <h4 class="mt-5 text-lg font-extrabold tracking-[-0.03em] text-slate-950">{{ $item['title'] }}</h4>
                            <p class="mt-2 min-h-[4.5rem] text-sm leading-6 text-slate-600">{{ $item['body'] }}</p>
                            <div class="mt-4 flex items-center justify-between gap-3 border-t pt-3" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">{{ $item['meta'] }}</p>
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-blue-700">{{ $item['metricLabel'] }}</p>
                            </div>
                        </article>
                    @endforeach
                    <article class="hidden rounded-[1rem] border p-4 xl:block" style="border-color: rgba(var(--theme-border-color-rgb),0.66); background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(20,184,166,0.06));">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-700">{{ __('Connected output') }}</p>
                        <h4 class="mt-4 text-lg font-extrabold tracking-[-0.03em] text-slate-950">{{ __('Every Bio page can ship with a QR campaign and branded short link.') }}</h4>
                        <div class="mt-5 grid grid-cols-7 grid-rows-7 gap-1 rounded-[0.95rem] bg-white p-3">
                            @foreach ([1,1,1,0,1,1,1,1,0,1,1,0,0,1,1,1,0,1,0,1,1,0,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1,0,1,0,1,0,1,0,1,1,1,1,1] as $bit)
                                <span class="{{ $bit ? 'bg-slate-900' : 'bg-transparent' }} rounded-[0.12rem]"></span>
                            @endforeach
                        </div>
                    </article>
                </div>
            </div>
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($featureCards as $card)
                <article data-reveal class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.35rem]">
                    <div class="relative h-40">
                        @include(theme_view('partials.visual-card', 'guest'), [
                            'type' => $card['visual'],
                            'icon' => $card['icon'],
                        ])
                        <span class="linkqr-glass-badge absolute bottom-4 right-4 z-10 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-600">{{ $card['metric'] }}</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('Feature') }}</span>
                        </div>
                        <h3 class="mt-5 text-xl font-extrabold tracking-[-0.035em] text-slate-950">{{ $card['title'] }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $card['body'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="linkqr-shell linkqr-section pt-0">
        <div data-reveal class="linkqr-hero-stage rounded-[1.8rem] p-5 sm:p-7">
            <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-blue-700">{{ __('Campaign cockpit') }}</p>
                    <h2 class="mt-4 text-4xl font-extrabold tracking-[-0.055em] text-slate-950">{{ __('A cleaner operating layer for links, QR, and Bio campaigns.') }}</h2>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600">{{ __('Operators see short URLs, destinations, scan pressure, click volume, attribution tags, domain status, and health alerts without jumping between disconnected screens.') }}</p>

                    <div class="mt-7 grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                        @foreach ([['fa-light fa-check', __('Broken links'), __('Passed'), '#059669'], ['fa-light fa-lock', __('SSL status'), __('Renewed'), '#2454E8'], ['fa-light fa-bell', __('Traffic spike'), __('Alert ready'), '#D97706']] as $status)
                            <div class="flex items-center gap-3 rounded-[1rem] border bg-white/82 px-4 py-3 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.85rem] bg-white shadow-sm" style="color: {{ $status[3] }};">
                                    <i class="{{ $status[0] }} text-sm"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-950">{{ $status[1] }}</p>
                                    <p class="text-xs font-semibold text-slate-500">{{ $status[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[1.5rem] border bg-white/82 p-4 shadow-[0_28px_90px_-62px_rgba(15,23,42,0.48)] backdrop-blur" style="border-color: rgba(var(--theme-border-color-rgb),0.78);">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b pb-4" style="border-color: rgba(var(--theme-border-color-rgb),0.72);">
                        <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-400">{{ __('Live link OS') }}</p>
                            <h3 class="mt-1 text-xl font-extrabold tracking-[-0.04em] text-slate-950">{{ __('Short link launch board') }}</h3>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-emerald-700">{{ __('Clean') }}</span>
                    </div>

                    <div class="mt-4 grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
                        <div class="rounded-[1.15rem] bg-slate-950 p-4 text-white">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-white/44">{{ __('Audience mix') }}</p>
                                <span class="text-xs font-extrabold text-cyan-200">+18.6%</span>
                            </div>
                            <div class="mt-5 space-y-4">
                                @foreach ($segments as $segment)
                                    <div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-semibold text-white/76">{{ $segment['label'] }}</span>
                                            <span class="font-extrabold">{{ $segment['value'] }}</span>
                                        </div>
                                        <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-white/8">
                                            <div class="h-full rounded-full transition-all" style="width: {{ $segment['value'] }}; background: {{ $segment['color'] }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @foreach ($workflow as $index => $step)
                                <article class="relative rounded-[1rem] border bg-white px-4 py-4 pl-14 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.74);">
                                    <span class="absolute left-4 top-4 inline-flex h-8 w-8 items-center justify-center rounded-[0.8rem] bg-blue-50 text-xs font-extrabold text-blue-700">{{ $index + 1 }}</span>
                                    <p class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-400">{{ $step['label'] }}</p>
                                    <h3 class="mt-1 text-sm font-extrabold text-slate-950">{{ $step['title'] }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $step['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="linkqr-shell linkqr-section pt-0">
        <div data-reveal class="linkqr-card overflow-hidden rounded-[1.65rem]">
            <div class="grid lg:grid-cols-[0.42fr_0.58fr]">
                <div class="relative overflow-hidden bg-slate-950 p-7 text-white sm:p-8">
                    <div class="absolute inset-0 opacity-40" style="background: linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 28px 28px;"></div>
                    <div class="relative">
                        <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-cyan-200">{{ __('Operations layer') }}</p>
                        <h2 class="mt-4 text-4xl font-extrabold tracking-[-0.055em]">{{ __('Run smarter short-link and QR campaigns after launch.') }}</h2>
                        <p class="mt-4 max-w-md text-sm leading-7 text-white/62">{{ __('Route traffic, watch click and scan health, and keep client workspaces organized from the same operating view.') }}</p>

                        <div class="mt-7 grid gap-3">
                            @foreach ([['fa-light fa-link-simple', __('5 branded short links live')], ['fa-light fa-route', __('3 routing rules active')], ['fa-light fa-shield-check', __('SSL and link health clean')]] as $signal)
                                <div class="flex items-center gap-3 rounded-[1rem] border border-white/10 bg-white/[0.055] px-4 py-3">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-[0.85rem] bg-white/10 text-cyan-200">
                                        <i class="{{ $signal[0] }}"></i>
                                    </span>
                                    <span class="text-sm font-extrabold text-white/82">{{ $signal[1] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 p-3 sm:p-4">
                    @foreach ($operationCards as $card)
                        <article class="linkqr-operation-row group grid gap-4 rounded-[1.2rem] border p-4 transition sm:grid-cols-[4.2rem_minmax(0,1fr)_auto] sm:items-center" style="border-color: rgba(var(--theme-border-color-rgb),0.58);">
                            <div class="inline-flex h-14 w-14 items-center justify-center rounded-[1.05rem] border bg-white text-lg shadow-sm transition group-hover:scale-105" style="border-color: rgba(var(--theme-border-color-rgb),0.78); color: {{ $loop->index === 0 ? '#2454E8' : ($loop->index === 1 ? '#D97706' : '#7C3AED') }};">
                                <i class="{{ $card['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</span>
                                    <span class="text-[10px] font-extrabold uppercase tracking-[0.14em] text-blue-600">{{ __('Live control') }}</span>
                                </div>
                                <h3 class="mt-2 text-xl font-extrabold tracking-[-0.04em] text-slate-950">{{ $card['title'] }}</h3>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $card['body'] }}</p>
                            </div>
                            <span class="hidden h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-700 transition group-hover:translate-x-1 sm:inline-flex">
                                <i class="fa-light fa-arrow-right"></i>
                            </span>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if (($homePlanTypes ?? collect())->isNotEmpty())
        <section id="pricing" class="linkqr-shell linkqr-section pt-0" x-data="{ type: {{ (int) ($homeDefaultType ?? 1) }} }">
            <div class="mb-10 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div class="max-w-3xl">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Pricing') }}</p>
                        <h2 class="mt-3 text-4xl font-extrabold tracking-[-0.055em] text-slate-950">{{ __('Plans for creators, brands, and agencies.') }}</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">{{ __('Pick the operating level you need today. Every plan keeps Link Bio, QR design, short-link controls, dynamic destination routing, and campaign analytics in the same workspace.') }}</p>
                    </div>
                </div>
                <div class="inline-flex rounded-full border bg-white p-1.5 shadow-sm" style="border-color: rgba(var(--theme-border-color-rgb),0.88);">
                    @foreach ($homePlanTypes as $typeKey => $typeLabel)
                        <button
                            type="button"
                            x-on:click="type = {{ $typeKey }}"
                            class="rounded-full px-5 py-2.5 text-sm font-bold transition"
                            x-bind:class="type === {{ $typeKey }} ? 'bg-blue-600 text-white shadow-[0_12px_24px_-18px_rgba(36,84,232,0.72)]' : 'text-slate-500 hover:bg-blue-50 hover:text-blue-700'"
                        >
                            {{ $typeLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="overflow-hidden rounded-[1.7rem] border bg-white/92 shadow-[0_34px_90px_-60px_rgba(15,23,42,0.34)]" style="border-color: rgba(var(--theme-border-color-rgb),0.9);">
                <div class="linkqr-pricing-grid flex flex-wrap">
                    @foreach ($homePlanTypes as $typeKey => $typeLabel)
                        @php
                            $plansForType = collect($homePricing[$typeKey] ?? []);
                            $planCount = $plansForType->count();
                        @endphp

                        @foreach ($plansForType as $plan)
                            @php
                                $isFreePlan = (bool) ($plan['free_plan'] ?? false);
                                $planTarget = $plan['model']->slug ?? $plan['id'];
                            @endphp

                            <div class="w-full lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" x-transition style="display: none; z-index: {{ 10 - $loop->index }};">
                            <article class="linkqr-pricing-card relative flex h-full flex-col px-6 py-8 sm:px-8 {{ $plan['featured'] ? 'is-featured bg-gradient-to-b from-blue-50 via-white to-teal-50/70' : 'bg-white' }}">
                                    @if ($plan['featured'])
                                        <div class="absolute right-0 top-0 h-40 w-40 overflow-hidden">
                                            <div class="absolute top-6 -right-10 rotate-45">
                                                <span class="bg-blue-600 px-12 py-1 text-xs font-extrabold uppercase tracking-[0.12em] text-white shadow-md">
                                                    {{ __('Featured') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="relative z-10">
                                        <span class="mb-3 inline-block text-sm font-extrabold uppercase tracking-[0.16em] leading-snug {{ $plan['featured'] ? 'text-blue-700' : 'text-slate-500' }}">
                                            {{ $plan['name'] ?? '-' }}
                                        </span>

                                        @if (!$isFreePlan && (int) ($plan['trial_day'] ?? 0) > 0)
                                            <div class="mb-3">
                                                <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.14em] text-amber-700">
                                                    {{ __(':days-day trial', ['days' => (int) $plan['trial_day']]) }}
                                                </span>
                                            </div>
                                        @endif

                                        <p class="mb-6 min-h-[4.5rem] text-sm font-medium leading-7 text-slate-600">
                                            {{ $plan['desc'] ?: __('A practical plan for Link Bio, Dynamic QR, Short Links, smart routing, branded domains, and analytics.') }}
                                        </p>

                                        <h3 class="mb-1 text-5xl font-extrabold leading-tight tracking-[-0.07em] text-slate-950">
                                            {{ $isFreePlan ? ($plan['currency_symbol'] ?: '$').'0' : ($plan['currency_symbol'] ?: '$').rtrim(rtrim(number_format((float) ($plan['price'] ?? 0), 2), '0'), '.') }}
                                            <span class="text-base font-bold tracking-normal text-slate-400">/{{ strtolower($typeLabel) }}</span>
                                        </h3>
                                        <p class="mb-8 text-sm font-semibold leading-relaxed text-slate-500">
                                            {{ match((int) ($plan['type'] ?? 1)) { 2 => __('Billed yearly'), 3 => __('Pay once, use forever'), default => __('Billed monthly') } }}
                                        </p>

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
                                                                <span class="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">
                                                                    {{ $feature['display'] }}
                                                                </span>
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
                                    </div>
                                </article>
                            </div>
                        @endforeach

                        @for ($i = $planCount; $i < 3; $i++)
                            <div class="hidden lg:block lg:w-1/3 lg:flex-1" x-show="type === {{ $typeKey }}" style="display: none;"></div>
                        @endfor
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="faqs" class="linkqr-shell linkqr-section pt-0">
        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:items-start">
            <div data-reveal class="linkqr-card linkqr-premium rounded-[1.6rem] p-6 sm:p-8">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('FAQs') }}</p>
                <h2 class="mt-3 text-4xl font-extrabold tracking-[-0.055em] text-slate-950">{{ __('Answers before customers commit.') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ __('Handle the questions buyers ask after they understand the product: editable QR codes, analytics, domains, teams, and agency workflows.') }}</p>
                <a href="{{ route('guest.faqs') }}" class="linkqr-button-secondary mt-7 inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                    {{ __('View all FAQs') }}
                    <i class="fa-light fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div x-data="{ openFaq: 0 }" class="space-y-3">
                @forelse (collect($featuredFaqs ?? [])->take(4) as $faq)
                    <article data-reveal class="linkqr-card rounded-[1.15rem] p-5">
                        <button type="button" x-on:click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex w-full items-start justify-between gap-4 text-left">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Question') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                <h3 class="mt-2 text-lg font-extrabold tracking-[-0.025em] text-slate-950">{{ $faq->titleForLocale() }}</h3>
                            </div>
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700 transition" x-bind:class="openFaq === {{ $loop->index }} ? 'rotate-45' : ''">
                                <i class="fa-light fa-plus"></i>
                            </span>
                        </button>
                        <div class="grid overflow-hidden transition-all duration-300" x-bind:style="openFaq === {{ $loop->index }} ? 'grid-template-rows: 1fr; opacity: 1; margin-top: 1rem;' : 'grid-template-rows: 0fr; opacity: 0; margin-top: 0;'">
                            <p class="min-h-0 overflow-hidden text-sm leading-7 text-slate-600">{{ $faq->contentPreview(220) !== '' ? $faq->contentPreview(220) : $faq->contentForLocale() }}</p>
                        </div>
                    </article>
                @empty
                    @foreach ($fallbackFaqs as $faq)
                        <article data-reveal class="linkqr-card rounded-[1.15rem] p-5">
                            <button type="button" x-on:click="openFaq = openFaq === {{ $loop->index }} ? -1 : {{ $loop->index }}" class="flex w-full items-start justify-between gap-4 text-left">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Question') }} {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                                    <h3 class="mt-2 text-lg font-extrabold tracking-[-0.025em] text-slate-950">{{ $faq['title'] }}</h3>
                                </div>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[0.85rem] bg-blue-50 text-blue-700 transition" x-bind:class="openFaq === {{ $loop->index }} ? 'rotate-45' : ''">
                                    <i class="fa-light fa-plus"></i>
                                </span>
                            </button>
                            <div class="grid overflow-hidden transition-all duration-300" x-bind:style="openFaq === {{ $loop->index }} ? 'grid-template-rows: 1fr; opacity: 1; margin-top: 1rem;' : 'grid-template-rows: 0fr; opacity: 0; margin-top: 0;'">
                                <p class="min-h-0 overflow-hidden text-sm leading-7 text-slate-600">{{ $faq['content'] }}</p>
                            </div>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section id="blog" class="linkqr-shell linkqr-section pt-0">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="max-w-3xl">
                <p data-reveal class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">{{ __('Blog') }}</p>
                <h2 data-reveal class="mt-3 text-4xl font-extrabold tracking-[-0.055em] text-slate-950 md:text-5xl">{{ __('Practical playbooks for Bio, QR, and short-link campaigns.') }}</h2>
            </div>
            <a data-reveal href="{{ route('guest.blogs') }}" class="linkqr-button-secondary inline-flex items-center justify-center rounded-[var(--theme-button-radius)] px-5 py-3 text-sm font-bold">
                {{ __('Read all posts') }}
                <i class="fa-light fa-arrow-right ml-2"></i>
            </a>
        </div>

        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            @forelse (collect($latestBlogs ?? [])->take(3) as $blog)
                <article data-reveal class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.35rem]">
                    <a href="{{ route('guest.blog-show', $blog->slug) }}" class="block h-52">
                        @if ($blog->thumbnailUrl())
                            <span class="linkqr-image-frame block h-full">
                                <img src="{{ $blog->thumbnailUrl() }}" alt="{{ $blog->titleForLocale() }}" loading="lazy" onerror="this.remove();">
                            </span>
                        @else
                            @include(theme_view('partials.visual-card', 'guest'), [
                                'type' => 'analytics',
                                'icon' => 'fa-light fa-newspaper',
                                'label' => $blog->category?->nameForLocale() ?: __('Article'),
                            ])
                        @endif
                    </a>
                    <div class="p-6">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $blog->publishedAtFormatted('M d, Y') ?: $blog->createdAtFormatted('M d, Y') }}</p>
                        <h3 class="mt-3 text-xl font-extrabold tracking-[-0.035em] text-slate-950">
                            <a href="{{ route('guest.blog-show', $blog->slug) }}">{{ $blog->titleForLocale() }}</a>
                        </h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $blog->contentPreview(150) }}</p>
                    </div>
                </article>
            @empty
                @foreach ($fallbackBlogs as $blog)
                    <article data-reveal class="linkqr-card linkqr-hover-lift overflow-hidden rounded-[1.35rem]">
                        <div class="h-52">
                            @include(theme_view('partials.visual-card', 'guest'), [
                                'type' => $blog['visual'],
                                'icon' => 'fa-light fa-newspaper',
                                'label' => $blog['label'],
                            ])
                        </div>
                        <div class="p-6">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('LinkQR guide') }}</p>
                            <h3 class="mt-3 text-xl font-extrabold tracking-[-0.035em] text-slate-950">{{ $blog['title'] }}</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $blog['excerpt'] }}</p>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
    </section>

    <section class="linkqr-shell pb-12">
        <div data-reveal class="rounded-[1.6rem] bg-slate-950 p-8 text-white sm:p-10">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-200">{{ __('Ready to launch') }}</p>
                    <h2 class="mt-3 text-4xl font-extrabold tracking-[-0.055em]">{{ __('Turn every printed code and profile link into a measurable campaign.') }}</h2>
                    <p class="mt-4 text-sm leading-7 text-white/60">{{ __('Create branded experiences, update destinations later, and know what is working before the next campaign goes live.') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if ($signupEnabled)
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-[var(--theme-button-radius)] bg-white px-6 py-4 text-sm font-bold text-slate-950">{{ __('Create account') }}</a>
                    @endif
                    <a href="{{ route('guest.contact') }}" class="inline-flex items-center justify-center rounded-[var(--theme-button-radius)] border border-white/12 px-6 py-4 text-sm font-bold text-white">{{ __('Contact sales') }}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
