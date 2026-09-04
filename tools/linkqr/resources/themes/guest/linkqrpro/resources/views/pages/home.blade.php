@extends(theme_view('layouts.marketing', 'guest'))

@section('content')
    @php
        $signupEnabled = auth_signup_enabled();
        $displayBaseUrl = preg_replace('#^https?://#i', '', rtrim(url('/'), '/'));
        $featureHighlights = [
            ['icon' => 'fa-light fa-fingerprint', 'title' => __('Username-first signup'), 'body' => __('Let visitors claim a clean URL before signup. Reserved paths, users, bio pages, and short links are checked before they continue.'), 'color' => '#cfeefd'],
            ['icon' => 'fa-light fa-link', 'title' => __('Bio page builder'), 'body' => __('Publish links, socials, products, videos, files, lead forms, menus, reviews, and contact actions from one profile.'), 'color' => '#fff3d8'],
            ['icon' => 'fa-light fa-link-simple', 'title' => __('Short link builder'), 'body' => __('Create branded short URLs for campaigns, ads, messages, invoices, and offline material with click tracking built in.'), 'color' => '#ffc52d'],
            ['icon' => 'fa-light fa-qrcode', 'title' => __('Dynamic QR sharing'), 'body' => __('Turn packaging, posters, tables, print ads, and offline traffic into measurable visits to the same public profile.'), 'color' => '#cef8dc'],
            ['icon' => 'fa-light fa-chart-simple', 'title' => __('Click and scan analytics'), 'body' => __('Track views, clicks, CTR, devices, countries, cities, referrers, campaigns, and block-level performance.'), 'color' => '#f7d1e6'],
            ['icon' => 'fa-light fa-globe-pointer', 'title' => __('Custom domains'), 'body' => __('Use branded domains for QR and bio journeys so campaigns feel owned by your business, not by a generic link tool.'), 'color' => '#e4ddff'],
            ['icon' => 'fa-light fa-users-gear', 'title' => __('Team-ready workspace'), 'body' => __('Manage pages, short links, QR assets, presets, folders, and reports from a portal built for repeated work.'), 'color' => '#dff3ce'],
        ];
        $workflow = [
            ['step' => '01', 'icon' => 'fa-light fa-fingerprint', 'title' => __('Claim the URL'), 'body' => __('Start with the public username people will remember.'), 'tag' => __('Username')],
            ['step' => '02', 'icon' => 'fa-light fa-id-card-clip', 'title' => __('Create the page'), 'body' => __('After signup, open the LinkBio builder with that slug already filled in.'), 'tag' => __('Bio page')],
            ['step' => '03', 'icon' => 'fa-light fa-link-simple', 'title' => __('Add short links and QR'), 'body' => __('Attach socials, offers, files, forms, branded short URLs, and QR destinations.'), 'tag' => __('Short links')],
            ['step' => '04', 'icon' => 'fa-light fa-chart-line-up', 'title' => __('Measure what works'), 'body' => __('Use city-level click and scan reporting to improve campaigns.'), 'tag' => __('Analytics')],
        ];
        $audiences = [
            ['icon' => 'fa-light fa-sparkles', 'title' => __('Creators'), 'body' => __('Put every social, video, drop, booking, and media kit behind one memorable profile.'), 'accent' => '#cfeefd', 'links' => [__('Latest drop'), __('Media kit')]],
            ['icon' => 'fa-light fa-briefcase', 'title' => __('Agencies'), 'body' => __('Launch client pages, QR campaigns, short links, and reports without stitching tools together.'), 'accent' => '#ffc52d', 'links' => [__('Client report'), __('Campaign QR')]],
            ['icon' => 'fa-light fa-store', 'title' => __('Local businesses'), 'body' => __('Send table QR scans, posters, packaging, and foot traffic to the right offers and contact actions.'), 'accent' => '#cef8dc', 'links' => [__('Menu QR'), __('Book a table')]],
            ['icon' => 'fa-light fa-users-gear', 'title' => __('Teams'), 'body' => __('Keep Bio pages, branded short links, campaign sources, domains, analytics, and team workflows in one portal.'), 'accent' => '#f7d1e6', 'links' => [__('Brand links'), __('Team report')]],
        ];
        $reportRows = [
            [__('Instagram launch link'), '982'],
            [__('Restaurant table QR'), '640'],
            [__('Product packaging QR'), '417'],
            [__('Branded short link'), '286'],
        ];
    @endphp

    <style>
        .linkqrpro-serif { font-family: Georgia, "Times New Roman", serif; }
        .linkqrpro-paper {
            background-color: #fbfaf6;
            background-image:
                radial-gradient(circle at 15% 8%, rgba(24,23,20,.05) 0 1px, transparent 1px),
                radial-gradient(circle at 76% 18%, rgba(24,23,20,.045) 0 1px, transparent 1px);
            background-size: 120px 120px, 170px 170px;
        }
        .linkqrpro-paper,
        .linkqrpro-paper *,
        .linkqrpro-paper *::before,
        .linkqrpro-paper *::after {
            box-sizing: border-box;
        }
        .linkqrpro-blue-paper {
            background-color: #d9f1fb;
            background-image:
                repeating-linear-gradient(0deg, rgba(50,109,133,.18) 0 1px, transparent 1px 31px),
                repeating-linear-gradient(90deg, rgba(50,109,133,.08) 0 1px, transparent 1px 72px);
        }
        .linkqrpro-doodle {
            color: rgba(24,23,20,.18);
        }
        .lq-hero-copy { animation: lq-rise-in .72s cubic-bezier(.2,.8,.2,1) both; }
        .lq-hero-preview { animation: lq-rise-in .82s .12s cubic-bezier(.2,.8,.2,1) both, lq-float 7s 1.1s ease-in-out infinite; transform-origin: center; }
        .lq-username-card { transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease; }
        .lq-username-card:focus-within { border-color: #5f8dff; box-shadow: 0 22px 55px -34px rgba(24,23,20,.62), 0 0 0 4px rgba(95,141,255,.14); transform: translateY(-2px); }
        .lq-card { transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease; }
        .lq-card:hover { border-color: rgba(95,141,255,.58); box-shadow: 0 30px 80px -62px rgba(24,23,20,.62); transform: translateY(-5px); }
        .lq-feature-card {
            background:
                linear-gradient(135deg, var(--accent-soft, rgba(255,255,255,.7)) 0 32%, rgba(255,255,255,.78) 32% 100%);
        }
        .lq-feature-card::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 6px;
            background: var(--accent, #5f8dff);
            opacity: .86;
        }
        .lq-feature-card::after {
            content: "";
            position: absolute;
            right: -28px;
            top: -28px;
            width: 104px;
            height: 104px;
            border-radius: 999px;
            background: var(--accent, #5f8dff);
            opacity: .16;
        }
        .lq-feature-showcase {
            background:
                radial-gradient(circle at 16% 18%, rgba(255,197,45,.34), transparent 30%),
                radial-gradient(circle at 92% 12%, rgba(95,141,255,.16), transparent 28%),
                linear-gradient(180deg, rgba(255,253,248,.96), rgba(255,250,235,.82));
        }
        .lq-shortlink-window {
            background:
                linear-gradient(#edf3fb 1px, transparent 1px),
                linear-gradient(90deg, #edf3fb 1px, transparent 1px),
                #fffdf8;
            background-size: 26px 26px;
        }
        .lq-use-card {
            background:
                radial-gradient(circle at 86% 18%, var(--accent-soft, rgba(255,255,255,.4)), transparent 30%),
                linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,253,248,.96));
        }
        .lq-use-card .lq-use-icon,
        .lq-use-card .lq-use-link {
            transition: transform .28s ease, background-color .28s ease, border-color .28s ease;
        }
        .lq-use-card:hover .lq-use-icon {
            transform: translateY(-4px) rotate(-4deg);
        }
        .lq-use-card:hover .lq-use-link {
            transform: translateX(4px);
            border-color: rgba(24,23,20,.22);
        }
        .lq-workflow-panel {
            background:
                radial-gradient(circle at 18% 12%, rgba(255,255,255,.5), transparent 28%),
                radial-gradient(circle at 86% 24%, rgba(255,243,216,.62), transparent 32%),
                linear-gradient(180deg, #8db2ff 0%, #6f9cff 100%);
        }
        .lq-step-card {
            background:
                radial-gradient(circle at 92% 10%, rgba(95,141,255,.12), transparent 28%),
                #fffdf8;
        }
        .lq-step-card::before {
            content: "";
            position: absolute;
            left: 2rem;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(95,141,255,.22);
        }
        .lq-step-icon {
            transition: transform .28s ease, background-color .28s ease;
        }
        .lq-step-card:hover .lq-step-icon {
            transform: translateY(-3px) rotate(-5deg);
            background-color: #cef8dc;
        }
        .lq-cta-button {
            position: relative;
            overflow: hidden;
            transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease, background-color .24s ease;
        }
        .lq-cta-button::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 0 30%, rgba(255,255,255,.44) 45%, transparent 60% 100%);
            transform: translateX(-120%);
            transition: transform .55s ease;
        }
        .lq-cta-button:hover {
            transform: translateY(-3px);
            border-color: rgba(16,36,83,.55);
            background: rgba(255,255,255,.35);
            box-shadow: 0 18px 36px -26px rgba(16,36,83,.72);
        }
        .lq-cta-button:hover::before {
            transform: translateX(120%);
        }
        .lq-cta-button i {
            transition: transform .24s ease;
        }
        .lq-cta-button:hover i {
            transform: translateX(4px);
        }
        .lq-badge-icon {
            transition: transform .24s ease, background-color .24s ease;
        }
        .lq-reveal:hover .lq-badge-icon {
            transform: translateY(-2px) rotate(-5deg);
            background-color: #181714;
            color: #fffdf8;
        }
        .lq-link-row i { transition: transform .22s ease; }
        .lq-link-row:hover i { transform: translate(3px, -3px); }
        .lq-postit { animation: lq-paper-sway 6.5s ease-in-out infinite; }
        .lq-metric { animation: lq-soft-pop .62s cubic-bezier(.2,.8,.2,1) both; animation-delay: var(--delay, 0ms); }
        .lq-reveal { opacity: 0; transform: translateY(24px); transition: opacity .72s cubic-bezier(.2,.8,.2,1), transform .72s cubic-bezier(.2,.8,.2,1); transition-delay: var(--delay, 0ms); }
        .lq-reveal.is-visible { opacity: 1; transform: translateY(0); }
        .lq-draw svg path { stroke-dasharray: 900; stroke-dashoffset: 900; }
        .lq-draw.is-visible svg path { animation: lq-draw 1.45s ease forwards; }
        @keyframes lq-rise-in { from { opacity: 0; transform: translateY(26px) scale(.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes lq-float { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-10px) rotate(.4deg); } }
        @keyframes lq-paper-sway { 0%, 100% { transform: translateY(0) rotate(var(--rotate, 0deg)); } 50% { transform: translateY(-8px) rotate(calc(var(--rotate, 0deg) + 2deg)); } }
        @keyframes lq-soft-pop { from { opacity: 0; transform: translateY(10px) scale(.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes lq-draw { to { stroke-dashoffset: 0; } }
        @media (max-width: 639px) {
            .linkqrpro-paper {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .linkqrpro-paper > .mx-auto,
            .lq-hero-copy,
            .lq-hero-copy > *,
            .lq-hero-copy [x-data] {
                min-width: 0;
                max-width: 100%;
            }
            .lq-hero-copy h1 {
                font-size: clamp(2.35rem, 10.6vw, 2.75rem);
                line-height: 1.02;
                letter-spacing: 0;
            }
            .lq-hero-copy p {
                overflow-wrap: anywhere;
            }
            .lq-username-card {
                width: 100%;
                max-width: 100%;
            }
            .lq-username-card > div {
                width: 100%;
                max-width: 100%;
            }
            .lq-hero-preview {
                border-radius: 1.1rem;
                padding: .75rem;
            }
            .lq-hero-preview .linkqrpro-blue-paper {
                border-radius: .9rem;
                padding: .75rem;
            }
            .lq-feature-showcase {
                padding: 1rem;
            }
            .lq-shortlink-window {
                min-width: 0;
                max-width: 100%;
                padding: .75rem;
            }
            .lq-short-url-row {
                flex-wrap: nowrap;
            }
            .lq-short-url-row > span {
                min-width: 0;
            }
            .lq-scroll-chips {
                flex-wrap: nowrap;
                max-width: 100%;
                overflow-x: auto;
                padding-bottom: .25rem;
                scrollbar-width: none;
            }
            .lq-scroll-chips::-webkit-scrollbar {
                display: none;
            }
            .lq-scroll-chips > span {
                flex: 0 0 auto;
            }
            .lq-postit {
                display: none;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .lq-hero-copy, .lq-hero-preview, .lq-postit, .lq-metric, .lq-reveal, .lq-draw.is-visible svg path {
                animation: none !important; opacity: 1 !important; transform: none !important; transition: none !important; stroke-dashoffset: 0 !important;
            }
        }
    </style>

    <section class="linkqrpro-paper relative overflow-hidden px-5 pb-14 pt-10 sm:pt-14">
        <div class="mx-auto grid max-w-6xl gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div class="lq-hero-copy text-center lg:text-left">
                <span class="inline-flex rounded-full border border-[#d8d3c7] bg-white/72 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff] shadow-[0_14px_35px_-30px_rgba(24,23,20,.5)]">
                    {{ __('Bio pages, short links, and QR') }}
                </span>

                <h1 class="linkqrpro-serif mx-auto mt-6 max-w-3xl text-[44px] leading-[0.96] tracking-[-0.025em] text-[#181714] sm:text-6xl md:text-7xl lg:mx-0">
                    {{ __('Claim your name. Publish one useful profile.') }}
                </h1>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-[#6d685f] lg:mx-0">
                    {{ __('Let guests check a clean username, sign up with it, then build a public Bio page, branded short links, QR sharing, and city-level click analytics.') }}
                </p>

                <div
                    x-data="{
                    username: '',
                    loading: false,
                    checked: false,
                    available: false,
                    message: '',
                    profileUrl: '',
                    isAuthenticated: @js(auth()->check()),
                    registerUrl: @js(url('/register')),
                    createBioUrl: @js(route('portal.link-bio.create')),
                    signupUrl: @js(auth()->check() ? route('portal.link-bio.create') : url('/register')),
                    timer: null,
                    destinationUrl(username) {
                        const target = new URL(this.isAuthenticated ? this.createBioUrl : this.registerUrl, window.location.href);
                        target.searchParams.set(this.isAuthenticated ? 'slug' : 'username', username);
                        return target.toString();
                    },
                    async check() {
                        const value = this.username.trim();
                        this.checked = false;
                        this.message = '';
                        this.available = false;
                        this.profileUrl = '';
                        if (!value) return;
                        this.loading = true;
                        try {
                            const response = await fetch(@js(route('guest.username.check')) + '?username=' + encodeURIComponent(value), { headers: { 'Accept': 'application/json' } });
                            const data = await response.json();
                            this.username = data.username || value;
                            this.available = !!data.available;
                            this.message = data.message || '';
                            this.profileUrl = data.profile_url || '';
                            this.signupUrl = this.destinationUrl(this.username || value);
                            this.checked = true;
                        } catch (error) {
                            this.message = @js(__('Could not check this username. Please try again.'));
                            this.checked = true;
                        } finally {
                            this.loading = false;
                        }
                    },
                    schedule() {
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.check(), 360);
                    },
                    submit() {
                        if (this.available) {
                            window.location.href = this.signupUrl;
                            return;
                        }
                        this.check();
                    }
                    }"
                    class="mx-auto mt-8 max-w-2xl lg:mx-0"
                >
                    <form x-on:submit.prevent="submit" class="lq-username-card mx-auto flex max-w-xl flex-col items-stretch gap-2 rounded-xl border border-[#d8d3c7] bg-white p-2 shadow-[0_16px_40px_-32px_rgba(24,23,20,.55)] sm:flex-row lg:mx-0">
                        <label class="sr-only" for="homepage-username">{{ __('Username') }}</label>
                        <div class="flex min-h-12 min-w-0 flex-1 items-center rounded-lg bg-[#fbfaf6] px-4 text-left">
                            <span class="max-w-[56vw] shrink truncate text-sm font-semibold text-[#8a867d] sm:max-w-60">{{ $displayBaseUrl }}/</span>
                            <input
                                id="homepage-username"
                                type="text"
                                x-model="username"
                                x-on:input="schedule"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm font-semibold text-[#181714] outline-none ring-0 placeholder:text-[#b9b2a5] focus:ring-0"
                                placeholder="{{ __('your-username') }}"
                                autocomplete="username"
                            >
                            <i x-show="loading" class="fa-light fa-spinner-third fa-spin text-[#8a867d]"></i>
                            <i x-show="checked && available && !loading" class="fa-light fa-circle-check text-emerald-700"></i>
                            <i x-show="checked && !available && !loading" class="fa-light fa-circle-xmark text-rose-700"></i>
                        </div>
                        <button type="submit" class="min-h-12 shrink-0 whitespace-nowrap rounded-lg bg-[#181714] px-5 text-sm font-semibold text-white transition hover:bg-black sm:min-w-32">
                            <span x-text="available ? (isAuthenticated ? @js(__('Create Bio')) : @js(__('Try for free'))) : @js(__('Check'))"></span>
                        </button>
                    </form>
                    <div class="mt-3 min-h-6 text-center text-sm font-semibold lg:text-left">
                        <p x-show="message" x-text="message" x-bind:class="available ? 'text-emerald-700' : 'text-rose-700'"></p>
                        <p x-show="profileUrl && available" class="mt-1 font-mono text-xs text-[#8a867d]" x-text="profileUrl"></p>
                    </div>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    @foreach ([['fa-light fa-shield-check', __('No URL conflicts')], ['fa-light fa-wand-magic-sparkles', __('Auto-filled signup')], ['fa-light fa-link-simple', __('Root profile URL')]] as $badge)
                        <div class="lq-reveal flex items-center gap-2 rounded-xl border border-[#e5dfd2] bg-white/60 px-4 py-3 text-sm font-bold text-[#57534b]" style="--delay: {{ 180 + ($loop->index * 90) }}ms;">
                            <span class="lq-badge-icon inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#f4f1ea] text-[#181714]">
                                <i class="{{ $badge[0] }} text-xs"></i>
                            </span>
                            <span>{{ $badge[1] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl">
                <div class="lq-postit absolute -left-5 top-8 h-28 w-28 bg-[#ffc52d] opacity-90" style="--rotate: -8deg; transform: rotate(-8deg);"></div>
                <div class="lq-postit absolute -right-5 bottom-8 h-32 w-28 bg-[#cfeefd] opacity-90" style="--rotate: 10deg; transform: rotate(10deg); animation-delay: -2.3s;"></div>
                <div class="lq-hero-preview relative rounded-[1.75rem] border border-[#d8d3c7] bg-[#fffdf8] p-5 shadow-[0_34px_90px_-62px_rgba(24,23,20,.58)]">
                    <div class="linkqrpro-blue-paper rounded-[1.35rem] p-5">
                        <div class="rounded-[1.1rem] border-4 border-[#5f8dff] bg-[#fffdf8] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Public profile') }}</p>
                                    <p class="mt-1 max-w-[12rem] truncate font-mono text-sm font-bold text-[#181714] sm:max-w-none">{{ $displayBaseUrl }}/yourname</p>
                                </div>
                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#181714] text-white">
                                    <i class="fa-light fa-qrcode"></i>
                                </span>
                            </div>

                            <div class="mt-5 grid gap-3">
                                @foreach ([__('Book a call'), __('Short link campaign'), __('Shop latest offer')] as $link)
                                    <div class="lq-link-row flex items-center justify-between rounded-xl border border-[#e5dfd2] bg-white px-4 py-3 text-sm font-bold text-[#181714] transition hover:border-[#5f8dff]/50 hover:bg-[#fbfaf6]">
                                        <span>{{ $link }}</span>
                                        <i class="fa-light fa-arrow-up-right text-[#5f8dff]"></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3">
                            @foreach ([['3.8K', __('Clicks')], ['18', __('Cities')], ['42%', __('CTR')]] as $metric)
                                <div class="lq-metric rounded-xl bg-[#fffdf8] p-4 text-center" style="--delay: {{ 420 + ($loop->index * 110) }}ms;">
                                    <p class="text-xl font-extrabold text-[#181714]">{{ $metric[0] }}</p>
                                    <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.14em] text-[#8a867d]">{{ $metric[1] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="px-5 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="lq-reveal rounded-[1.75rem] border border-[#ded7ca] bg-[#fffdf8]/80 p-5 shadow-[0_28px_85px_-72px_rgba(24,23,20,.45)] sm:p-7 lg:p-9">
                <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
                    <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Highlights') }}</p>
                        <h2 class="linkqrpro-serif mt-4 max-w-2xl text-4xl leading-tight text-[#181714] sm:text-5xl">{{ __('Built for Bio pages, short links, QR campaigns, and measurable clicks.') }}</h2>
                    </div>
                    <div class="relative">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rotate-6 bg-[#ffc52d] opacity-80"></div>
                        <div class="absolute -bottom-4 left-8 h-20 w-28 -rotate-6 bg-[#cfeefd] opacity-90"></div>
                        <div class="relative rounded-[1.35rem] border-4 border-[#5f8dff] bg-[#fffdf8] p-5 shadow-[0_18px_0_rgba(93,141,255,.16)]">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Campaign snapshot') }}</p>
                                    <p class="mt-2 max-w-md text-sm leading-7 text-[#6d685f]">{{ __('Claim a username, publish the Bio page, create branded short links, then read every QR scan and click from the same workspace.') }}</p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 sm:min-w-[18rem]">
                                    @foreach ([['3.8K', __('Clicks')], ['18', __('Cities')], ['42%', __('CTR')]] as $metric)
                                        <div class="rounded-xl bg-[#f9f3df] p-3 text-center">
                                            <p class="text-xl font-extrabold text-[#181714]">{{ $metric[0] }}</p>
                                            <p class="mt-1 inline-flex items-center justify-center gap-1 text-[10px] font-bold uppercase tracking-[0.12em] text-[#8a867d]">
                                                <i class="fa-light {{ $loop->index === 0 ? 'fa-arrow-pointer' : ($loop->index === 1 ? 'fa-location-dot' : 'fa-gauge-high') }}"></i>
                                                <span>{{ $metric[1] }}</span>
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                @foreach ([['fa-light fa-store', __('Creators and shops')], ['fa-light fa-users-gear', __('Agencies and teams')], ['fa-light fa-link-simple', __('Short links and QR')]] as $label)
                                    <div class="lq-card rounded-xl border border-[#e5dfd2] bg-white px-4 py-3">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#cef8dc] text-xs font-black text-[#28553c]"><i class="{{ $label[0] }}"></i></span>
                                        <p class="mt-3 text-sm font-bold text-[#181714]">{{ $label[1] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($featureHighlights as $feature)
                    <article class="lq-card lq-feature-card lq-reveal relative min-h-[16rem] overflow-hidden rounded-[1.35rem] border border-[#ded7ca] p-6 shadow-[0_22px_70px_-58px_rgba(24,23,20,.48)]" style="--delay: {{ $loop->index * 80 }}ms; --accent: {{ $feature['color'] }}; --accent-soft: {{ $feature['color'] }}55;">
                        <div class="relative z-10 flex items-start justify-between gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-[#181714] shadow-[0_12px_30px_-22px_rgba(24,23,20,.8)]" style="background: {{ $feature['color'] }};">
                                <i class="{{ $feature['icon'] }} text-2xl"></i>
                            </div>
                            <span class="rounded-full border border-[#ded7ca] bg-[#fffdf8]/86 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#8a867d]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <i class="{{ $feature['icon'] }} pointer-events-none absolute bottom-5 right-5 text-6xl text-[#181714]/[0.045]"></i>
                        <div class="relative z-10">
                            <h3 class="linkqrpro-serif mt-6 text-2xl leading-tight text-[#181714]">{{ $feature['title'] }}</h3>
                            <p class="mt-4 text-sm leading-7 text-[#57534b]">{{ $feature['body'] }}</p>
                        </div>
                    </article>
                @endforeach

                <article class="lq-card lq-reveal lq-feature-showcase relative overflow-hidden rounded-[1.35rem] border border-[#ded7ca] p-6 shadow-[0_24px_80px_-58px_rgba(24,23,20,.52)] md:col-span-2 xl:col-span-2" style="--delay: 120ms;">
                    <div class="relative z-10 grid h-full gap-6">
                        <div class="max-w-4xl">
                            <span class="inline-flex rounded-full border border-[#d8d3c7] bg-[#fffdf8]/86 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em] text-[#8a867d]">{{ __('Short Link Lab') }}</span>
                            <div class="mt-5 grid gap-4 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
                                <h3 class="linkqrpro-serif text-3xl leading-tight text-[#181714] sm:text-[2.65rem]">{{ __('Short links that still feel like your brand.') }}</h3>
                                <p class="text-sm leading-7 text-[#57534b]">{{ __('Turn product pages, forms, WhatsApp links, booking URLs, and ad destinations into readable campaign URLs with tracking, rules, and QR reuse.') }}</p>
                            </div>
                            <div class="mt-5 flex flex-wrap gap-2">
                                @foreach ([__('Readable URL'), __('Click tracking'), __('QR ready')] as $chip)
                                    <span class="rounded-full border border-[#d8d3c7] bg-white/75 px-3 py-1 text-xs font-bold text-[#57534b]">{{ $chip }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="lq-shortlink-window rounded-[1.35rem] border border-[#ded7ca] p-4 shadow-[0_22px_54px_-42px_rgba(24,23,20,.5)]">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-[#ff6f61]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#ffc52d]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[#74d99f]"></span>
                            </div>

                            <div class="mt-4 rounded-xl border border-[#e5dfd2] bg-white/90 p-3">
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a867d]">{{ __('Long destination') }}</p>
                                <p class="mt-1 truncate font-mono text-xs font-bold text-[#181714] sm:text-sm">https://example.com/products/summer-launch?utm_campaign=poster</p>
                            </div>

                            <div class="lq-short-url-row mt-3 flex items-center gap-2 rounded-xl border border-[#5f8dff]/35 bg-white p-3">
                                <span class="max-w-[46%] truncate rounded-lg bg-[#181714] px-3 py-2 font-mono text-xs font-bold text-white sm:max-w-none">{{ $displayBaseUrl }}/go</span>
                                <span class="min-w-0 flex-1 truncate rounded-lg bg-[#fff3d8] px-3 py-2 font-mono text-sm font-bold text-[#181714]">summer-offer</span>
                                <i class="fa-light fa-arrow-up-right shrink-0 text-[#5f8dff]"></i>
                            </div>

                            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                @foreach ([[__('Rules'), '3'], [__('Clicks'), '1.2K'], [__('CTR'), '38%']] as $metric)
                                    <div class="rounded-xl border border-[#eadfc9] bg-[#fffdf8]/92 px-3 py-3 text-center">
                                        <p class="text-lg font-extrabold text-[#181714]">{{ $metric[1] }}</p>
                                        <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.12em] text-[#8a867d]">{{ $metric[0] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="lq-scroll-chips mt-4 flex flex-wrap gap-2">
                                @foreach ([__('UTM ready'), __('Password option'), __('Expiry date'), __('QR reuse')] as $chip)
                                    <span class="rounded-full border border-[#d8d3c7] bg-white/86 px-3 py-1 text-xs font-bold text-[#57534b]">{{ $chip }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <i class="fa-light fa-link-simple pointer-events-none absolute -bottom-8 -right-5 text-[9rem] text-[#181714]/[0.035]"></i>
                </article>
            </div>
        </div>
    </section>

    <section class="linkqrpro-blue-paper px-5 py-20">
        <div class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[0.46fr_0.54fr] lg:items-stretch">
            <div class="lq-reveal lq-workflow-panel relative overflow-hidden rounded-[1.6rem] p-8 text-[#102453] shadow-[18px_18px_0_rgba(111,156,255,.16)]">
                <span class="inline-flex rounded-full border border-white/55 bg-white/45 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.14em]">{{ __('Launch flow') }}</span>
                <h2 class="linkqrpro-serif mt-5 text-4xl leading-tight sm:text-5xl">{{ __('Start with the URL. Build every link around it.') }}</h2>
                <p class="mt-5 text-sm leading-7">{{ __('When a guest chooses a username, signup continues into the builder with the slug ready. From there they can publish the profile, create short links, attach QR codes, and measure every click.') }}</p>

                <div class="mt-7 rounded-[1.2rem] border border-white/45 bg-white/34 p-4 shadow-[0_14px_34px_-30px_rgba(16,36,83,.42)]">
                    <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-[0.14em]">
                        <span>{{ __('Setup progress') }}</span>
                        <span>4/4</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-white/55">
                        <div class="h-full w-full rounded-full bg-[#102453]"></div>
                    </div>
                </div>

                <div class="mt-7 grid gap-3">
                    @foreach ([__('username'), __('bio page'), __('short links'), __('analytics')] as $pill)
                        <div class="flex items-center gap-3 rounded-xl border border-white/42 bg-white/32 px-4 py-3 text-sm font-bold shadow-[0_12px_28px_-26px_rgba(16,36,83,.35)]">
                            <i class="fa-light fa-circle-check text-[#102453]"></i>
                            <span>{{ $pill }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($signupEnabled)
                    <a href="{{ url('/register') }}" class="lq-cta-button mt-8 inline-flex items-center gap-2 rounded-lg border border-[#102453]/30 px-4 py-3 text-sm font-bold focus:outline-none focus:ring-4 focus:ring-white/35">
                        <span class="relative z-10">{{ __('Create your first Bio page') }}</span>
                        <i class="fa-light fa-arrow-right relative z-10 text-xs"></i>
                    </a>
                @endif

                <i class="fa-light fa-route pointer-events-none absolute -bottom-8 -right-4 text-[10rem] text-white/18"></i>
            </div>

            <div class="grid gap-4">
                @foreach ($workflow as $item)
                    <article class="lq-card lq-step-card lq-reveal relative overflow-hidden rounded-[1.25rem] border-4 border-[#5f8dff] p-5 shadow-[0_14px_0_rgba(93,141,255,.14)]" style="--delay: {{ $loop->index * 90 }}ms;">
                        <div class="relative z-10 flex gap-5">
                            <span class="lq-step-icon inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#cfeefd] text-[#181714] shadow-[0_12px_28px_-22px_rgba(24,23,20,.75)]">
                                <i class="{{ $item['icon'] }} text-xl"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-[#5f8dff]">{{ $item['step'] }}</span>
                                    <span class="rounded-full border border-[#ded7ca] bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8a867d]">{{ $item['tag'] }}</span>
                                </div>
                                <h3 class="linkqrpro-serif mt-3 text-2xl leading-tight text-[#181714]">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#6d685f]">{{ $item['body'] }}</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-5 py-20">
        <div class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div class="lq-reveal">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#5f8dff]">{{ __('Reporting') }}</p>
                <h2 class="linkqrpro-serif mt-4 text-4xl leading-tight text-[#181714] sm:text-5xl">{{ __('Know which Bio links, short links, and QR scans work.') }}</h2>
                <p class="mt-5 max-w-xl text-sm leading-7 text-[#6d685f]">{{ __('Track profile views, short link clicks, individual Bio link clicks, QR scans, city-level location, countries, devices, referrers, and campaign source. Useful for real campaigns, not just vanity counts.') }}</p>
            </div>

            <div class="lq-card lq-reveal rounded-[1.4rem] border border-[#ded7ca] bg-[#fffdf8] p-5 shadow-[0_28px_85px_-64px_rgba(24,23,20,.48)]" style="--delay: 120ms;">
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([['3,842', __('Clicks')], ['18', __('Cities')], ['42%', __('CTR')]] as $metric)
                        <div class="lq-metric rounded-xl bg-[#f9f3df] p-5" style="--delay: {{ $loop->index * 120 }}ms;">
                            <p class="text-3xl font-extrabold text-[#181714]">{{ $metric[0] }}</p>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-[#8a867d]">{{ $metric[1] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 rounded-xl border border-[#e5dfd2] bg-white p-4">
                    @foreach ($reportRows as $row)
                        <div class="lq-reveal flex items-center justify-between gap-3 border-b border-[#eee8dc] py-3 last:border-b-0" style="--delay: {{ $loop->index * 75 }}ms;">
                            <span class="text-sm font-bold text-[#181714]">{{ $row[0] }}</span>
                            <span class="rounded-full bg-[#cef8dc] px-3 py-1 text-xs font-bold text-[#28553c]">{{ $row[1] }} {{ __('clicks') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section id="use-cases" class="px-5 py-20">
        <div class="mx-auto max-w-6xl">
            <div class="lq-reveal mx-auto max-w-3xl text-center">
                <h2 class="linkqrpro-serif text-4xl leading-tight text-[#181714] sm:text-5xl">{{ __('Built for the way different teams publish.') }}</h2>
                <p class="mt-5 text-sm leading-7 text-[#6d685f]">{{ __('Use one system for public profiles, branded short links, QR campaigns, and reporting. Keep each use case focused without creating another tool stack.') }}</p>
            </div>

            <div class="mt-12 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($audiences as $item)
                    <article class="lq-card lq-use-card lq-reveal relative min-h-[18rem] overflow-hidden rounded-[1.35rem] border border-[#ded7ca] p-6 shadow-[0_22px_70px_-60px_rgba(24,23,20,.48)]" style="--delay: {{ $loop->index * 90 }}ms; --accent-soft: {{ $item['accent'] }}99;">
                        <div class="relative z-10 flex items-start justify-between gap-4">
                            <span class="lq-use-icon inline-flex h-14 w-14 items-center justify-center rounded-2xl text-[#181714] shadow-[0_14px_34px_-24px_rgba(24,23,20,.7)]" style="background: {{ $item['accent'] }};">
                                <i class="{{ $item['icon'] }} text-xl"></i>
                            </span>
                            <span class="rounded-full border border-[#d8d3c7] bg-[#fffdf8]/86 px-3 py-1 font-mono text-[11px] font-bold text-[#8a867d]">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <h3 class="linkqrpro-serif relative z-10 mt-6 text-3xl leading-tight text-[#181714]">{{ $item['title'] }}</h3>
                        <p class="relative z-10 mt-4 text-sm leading-7 text-[#6d685f]">{{ $item['body'] }}</p>

                        <div class="relative z-10 mt-5 grid gap-2">
                            @foreach ($item['links'] as $link)
                                <div class="lq-use-link flex items-center justify-between rounded-xl border border-[#e5dfd2] bg-white/78 px-3 py-2 text-xs font-bold text-[#57534b]">
                                    <span>{{ $link }}</span>
                                    <i class="fa-light fa-arrow-up-right text-[#5f8dff]"></i>
                                </div>
                            @endforeach
                        </div>

                        <span class="pointer-events-none absolute -bottom-10 -right-8 h-32 w-32 rounded-full opacity-55" style="background: {{ $item['accent'] }};"></span>
                        <i class="{{ $item['icon'] }} pointer-events-none absolute bottom-5 right-5 text-6xl text-[#181714]/[0.045]"></i>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="px-5 py-10">
        <div class="lq-reveal lq-draw mx-auto grid max-w-4xl items-center gap-8 bg-[#ffc52d] px-8 py-12 sm:grid-cols-[0.9fr_1.1fr] sm:px-14">
            <div>
                <h2 class="linkqrpro-serif text-4xl leading-tight text-[#181714]">{{ __('Your links deserve better tools') }}</h2>
                <p class="mt-4 max-w-sm text-sm leading-6 text-[#4d3600]">{{ __('No matter what you share, keep Bio pages, short links, QR, and analytics together in one workspace.') }}</p>
                @if ($signupEnabled)
                    <a href="{{ url('/register') }}" class="mt-7 inline-flex rounded-lg bg-[#181714] px-5 py-3 text-sm font-semibold text-white">{{ __('Try for free') }}</a>
                @endif
            </div>
            <svg viewBox="0 0 430 220" class="mx-auto h-auto w-full max-w-md" fill="none" stroke="#181714" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <path d="M111 174c4-48 29-74 74-78 42-4 70 21 77 74M184 170c5-47 31-72 78-74 40-2 68 24 76 76"/>
                <path d="M70 171c4-46 28-72 71-76 37-3 65 20 75 71"/>
                <path d="M103 103c13-29 39-40 77-32 22 5 34 18 36 41M207 105c14-30 40-42 79-35 23 4 38 18 43 42"/>
                <path d="M68 110c16-31 42-43 78-36 23 4 38 17 44 40"/>
                <path d="M105 142c15 12 33 13 54 3M210 145c15 10 33 10 53-1M294 143c15 11 33 11 54-1"/>
                <path d="M119 119c0 5-2 8-6 8s-6-3-6-8M155 119c0 5-2 8-6 8s-6-3-6-8M224 121c0 5-2 8-6 8s-6-3-6-8M260 121c0 5-2 8-6 8s-6-3-6-8M308 119c0 5-2 8-6 8s-6-3-6-8M344 119c0 5-2 8-6 8s-6-3-6-8"/>
                <path d="M45 88l10 10M55 88 45 98M372 70l10 10M382 70l-10 10M23 138l10 10M33 138l-10 10"/>
                <path d="M394 103c7-4 13-3 17 3 4 7 1 13-7 18"/>
            </svg>
        </div>
    </section>

    <script>
        (() => {
        const initHomeAnimations = () => {
            const animatedItems = document.querySelectorAll('.lq-reveal, .lq-draw');

            if (!('IntersectionObserver' in window)) {
                animatedItems.forEach((item) => item.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.16,
                rootMargin: '0px 0px -8% 0px',
            });

            animatedItems.forEach((item) => observer.observe(item));
        };

        document.addEventListener('DOMContentLoaded', initHomeAnimations);
        document.addEventListener('livewire:navigated', initHomeAnimations);
        })();
    </script>
@endsection
