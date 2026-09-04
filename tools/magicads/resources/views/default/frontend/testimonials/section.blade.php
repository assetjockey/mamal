{{-- Testimonials — horizontal scroll-snap rail with mixed ink/white cards. --}}
@php
    // Admin-managed testimonials (injected by the `welcome` view composer).
    // `featured` maps to the full-black "ink" card so highlighted reviews
    // stand out. When none are configured we fall back to a curated set so
    // the section never renders empty.
    $testimonials = (isset($testimonials) && count($testimonials))
        ? collect($testimonials)->map(fn ($t) => [
            'variant'  => $t->featured ? 'ink' : 'white',
            'quote'    => $t->testimonial,
            'name'     => $t->name,
            'role'     => collect([$t->role, $t->company])->filter()->implode(', '),
            'initials' => $t->initials,
            'avatar'   => $t->avatar_url,
            'stars'    => (int) $t->stars,
        ])->all()
        : [
        ['variant' => 'white', 'quote' => __('AI Ad Studio replaced three contractors. We brief a campaign in the morning and ship twelve on-brand assets before lunch — without ever opening a design tool.'), 'name' => __('Jordan Alvarez'),  'role' => __('Growth lead, Lumen Labs'),         'initials' => 'JA', 'avatar' => null, 'stars' => 5],
        ['variant' => 'ink',   'quote' => __('The canvas presets alone paid for the subscription in week one. Our team stopped hand-resizing hero creative overnight.'),                                                   'name' => __('Priya Menon'),    'role' => __('Head of brand, Northwind'),       'initials' => 'PM', 'avatar' => null, 'stars' => 5],
        ['variant' => 'white', 'quote' => __('We cut our copy review cycles in half. The studio generates three headline variants that already match our voice.'),                                                         'name' => __('Daniel Okafor'),  'role' => __('Marketing manager, Harborlight'), 'initials' => 'DO', 'avatar' => null, 'stars' => 5],
        ['variant' => 'white', 'quote' => __('Brand kits make this the first AI tool our design lead actually trusts. Fonts, palette, and logo lockups stay consistent.'),                                                 'name' => __('Maya Rothstein'), 'role' => __('Creative director, Tidemark'),    'initials' => 'MR', 'avatar' => null, 'stars' => 5],
        ['variant' => 'ink',   'quote' => __('Our paid social calendar used to have holes. Now one marketer can generate a full week of platform-ready ads before Monday standup ends.'),                                 'name' => __('Chen Huang'),     'role' => __('Paid social lead, Verdant & Co.'),'initials' => 'CH', 'avatar' => null, 'stars' => 5],
        ['variant' => 'white', 'quote' => __('The asset gallery keeps every generation searchable. When a campaign spikes we remix winners in minutes and relaunch across every channel.'),                                 'name' => __('Sofia Nakamura'), 'role' => __('Performance, Brightrail'),        'initials' => 'SN', 'avatar' => null, 'stars' => 5],
    ];
@endphp

<section id="testimonials" class="relative overflow-hidden py-24 sm:py-32">
    {{-- Abstract decorative lines — same family as FAQ, mirrored layout:
         curves run right-to-left, arcs sit on the LEFT, dot grid on the RIGHT,
         diagonals swapped so the eye flow doesn't repeat the FAQ section. --}}
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        {{-- Large sweeping curves — flowing right-to-left with a different cadence --}}
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 1200 800" preserveAspectRatio="none" fill="none">
            <path d="M1300,150 C 1000,320 700,80 500,260 S 100,120 -150,300" stroke="#4F46E5" stroke-width="1.5" opacity="0.25"/>
            <path d="M1280,420 C 980,260 680,540 380,360 S 80,580 -120,420" stroke="#4F46E5" stroke-width="1" opacity="0.18"/>
            <path d="M1320,620 C 1020,470 720,720 420,560 S 120,760 -150,600" stroke="#000000" stroke-width="1" opacity="0.12"/>
        </svg>

        {{-- Diagonal accent line — top-LEFT (mirror of FAQ's top-right) --}}
        <div class="absolute -left-20 top-10 h-[1.5px] w-[45%] origin-left rotate-[18deg]"
             style="background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.35) 40%, rgba(79, 70, 229, 0.5) 70%, transparent);"></div>

        {{-- Diagonal accent line — bottom-RIGHT (mirror of FAQ's bottom-left) --}}
        <div class="absolute -right-10 bottom-20 h-[1.5px] w-[35%] origin-right -rotate-[12deg]"
             style="background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.15) 30%, rgba(79, 70, 229, 0.3) 60%, transparent);"></div>

        {{-- Concentric arcs — LEFT side (FAQ has them on the right) --}}
        <svg class="absolute -left-32 top-1/2 h-[500px] w-[500px] -translate-y-1/2" viewBox="0 0 400 400" fill="none">
            <circle cx="200" cy="200" r="80" stroke="#4F46E5" stroke-width="1" opacity="0.18"/>
            <circle cx="200" cy="200" r="130" stroke="#4F46E5" stroke-width="0.8" opacity="0.14"/>
            <circle cx="200" cy="200" r="180" stroke="#4F46E5" stroke-width="0.6" opacity="0.10"/>
            <circle cx="200" cy="200" r="230" stroke="#4F46E5" stroke-width="0.5" opacity="0.07"/>
        </svg>

        {{-- Dot grid — RIGHT side (FAQ has it on the left), staggered and slightly larger --}}
        <svg class="absolute right-8 top-1/4 h-36 w-36" viewBox="0 0 100 100" fill="#4F46E5" opacity="0.3">
            <circle cx="10" cy="10" r="1.8"/><circle cx="30" cy="10" r="1.8"/><circle cx="50" cy="10" r="1.8"/><circle cx="70" cy="10" r="1.8"/><circle cx="90" cy="10" r="1.8"/>
            <circle cx="20" cy="30" r="1.8"/><circle cx="40" cy="30" r="1.8"/><circle cx="60" cy="30" r="1.8"/><circle cx="80" cy="30" r="1.8"/>
            <circle cx="10" cy="50" r="1.8"/><circle cx="30" cy="50" r="1.8"/><circle cx="50" cy="50" r="1.8"/><circle cx="70" cy="50" r="1.8"/><circle cx="90" cy="50" r="1.8"/>
            <circle cx="20" cy="70" r="1.8"/><circle cx="40" cy="70" r="1.8"/><circle cx="60" cy="70" r="1.8"/><circle cx="80" cy="70" r="1.8"/>
            <circle cx="10" cy="90" r="1.8"/><circle cx="30" cy="90" r="1.8"/><circle cx="50" cy="90" r="1.8"/><circle cx="70" cy="90" r="1.8"/><circle cx="90" cy="90" r="1.8"/>
        </svg>
    </div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
            <div>
                <span class="l-chip l-chip--indigo">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                    {{ __('Testimonials') }}
                </span>
                <h2 class="l-display mt-5 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-black sm:text-5xl">
                    {{ __('Loved by teams') }} <br>
                    {{ __('who') }} <span class="l-accent">{{ __('ship fast.') }}</span>
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2">
                    <div class="flex items-center gap-0.5" aria-hidden="true">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4 text-[#4F46E5]" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8l-5.2 2.8 1-5.8L1.5 7.7l5.9-.9z"/></svg>
                        @endfor
                    </div>
                    <span class="l-display text-2xl font-bold text-black">4.9</span>
                </div>
                <span class="l-vdash h-8"></span>
                <span class="text-[12px] text-black/55">{{ __('from 1,247 reviews') }}</span>
            </div>
        </div>

        {{-- Infinite, drag/scrollable testimonial rail. Cards are triplicated
             so the rail can wrap seamlessly in both directions; JS keeps the
             scroll position centered on the middle copy. --}}
        <div class="mt-12 l-rail l-rail--loop" id="testimonial-rail" tabindex="0"
             role="region" aria-label="{{ __('Customer testimonials') }}">
            @for ($pass = 0; $pass < 3; $pass++)
                @foreach ($testimonials as $t)
                    @php $isInk = $t['variant'] === 'ink'; @endphp
                    <figure @class([
                        'flex w-80 flex-col p-6 select-none',
                        'l-card' => !$isInk,
                        'l-card l-card--ink' => $isInk,
                    ]) @if ($pass !== 1) aria-hidden="true" @endif>
                        <div class="flex items-center gap-1" aria-hidden="true">
                            @for ($i = 0; $i < ($t['stars'] ?? 5); $i++)
                                <svg @class(['h-4 w-4', 'text-[#4F46E5]' => !$isInk, 'text-white' => $isInk]) viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.8l-5.2 2.8 1-5.8L1.5 7.7l5.9-.9z"/>
                                </svg>
                            @endfor
                            <span class="sr-only">{{ ($t['stars'] ?? 5) }} {{ __('out of 5') }}</span>
                        </div>
                        <blockquote @class(['mt-4 flex-1 text-[14px] leading-relaxed', 'text-black/85' => !$isInk, 'text-white/85' => $isInk])>
                            <p>“{{ $t['quote'] }}”</p>
                        </blockquote>
                        <figcaption @class(['mt-6 flex items-center gap-3 border-t pt-4', 'border-[var(--l-hairline)]' => !$isInk, 'border-white/10' => $isInk])>
                            @if (!empty($t['avatar']))
                                <img src="{{ $t['avatar'] }}" alt="{{ $t['name'] }}"
                                     class="h-10 w-10 shrink-0 rounded-full object-cover" loading="lazy" decoding="async" width="40" height="40" draggable="false">
                            @else
                                <span aria-hidden="true"
                                      @class([
                                          'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                                          'bg-[#4F46E5] text-white' => $isInk,
                                          'bg-black text-white'     => !$isInk,
                                      ])>{{ $t['initials'] }}</span>
                            @endif
                            <span>
                                <span @class(['block text-[13px] font-semibold', 'text-black' => !$isInk, 'text-white' => $isInk])>{{ $t['name'] }}</span>
                                <span @class(['block text-[11px]', 'text-black/55' => !$isInk, 'text-white/55' => $isInk])>{{ $t['role'] }}</span>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            @endfor
        </div>
    </div>

    {{-- Infinite scroll controller: seamless wrap + idle auto-advance,
         supports wheel, trackpad, drag, and keyboard. --}}
    <script>
        (function () {
            var rail = document.getElementById('testimonial-rail');
            if (!rail) return;

            var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // The rail holds three identical copies of the testimonial set.
            // One "segment" is a third of the total scroll width. We keep the
            // scroll position within the middle segment and snap by ±1 segment
            // whenever the user crosses into a side copy — invisible because
            // the copies are identical.
            function segment() { return rail.scrollWidth / 3; }

            function recenter() {
                var seg = segment();
                if (seg <= 0) return;
                if (rail.scrollLeft < seg * 0.5) {
                    rail.scrollLeft += seg;
                } else if (rail.scrollLeft > seg * 1.5) {
                    rail.scrollLeft -= seg;
                }
            }

            // Start centered on the middle copy.
            function init() { rail.scrollLeft = segment(); }
            if (document.readyState === 'complete') init();
            else window.addEventListener('load', init);
            window.addEventListener('resize', init);

            rail.addEventListener('scroll', function () {
                window.requestAnimationFrame(recenter);
            }, { passive: true });

            // ---- Pointer drag (mouse / touch / pen) ----
            var isDown = false, startX = 0, startScroll = 0, moved = false;
            rail.addEventListener('pointerdown', function (e) {
                isDown = true; moved = false;
                startX = e.clientX;
                startScroll = rail.scrollLeft;
                rail.setPointerCapture && rail.setPointerCapture(e.pointerId);
            });
            rail.addEventListener('pointermove', function (e) {
                if (!isDown) return;
                var dx = e.clientX - startX;
                if (Math.abs(dx) > 3) moved = true;
                rail.scrollLeft = startScroll - dx;
            });
            function endDrag() { isDown = false; }
            rail.addEventListener('pointerup', endDrag);
            rail.addEventListener('pointercancel', endDrag);
            rail.addEventListener('pointerleave', endDrag);
            // Prevent click navigation right after a drag.
            rail.addEventListener('click', function (e) {
                if (moved) { e.preventDefault(); e.stopPropagation(); }
            }, true);

            // ---- Keyboard ----
            rail.addEventListener('keydown', function (e) {
                var card = rail.querySelector('figure');
                var stepBy = card ? card.getBoundingClientRect().width + 20 : 320;
                if (e.key === 'ArrowRight') { e.preventDefault(); rail.scrollBy({ left:  stepBy, behavior: 'smooth' }); }
                if (e.key === 'ArrowLeft')  { e.preventDefault(); rail.scrollBy({ left: -stepBy, behavior: 'smooth' }); }
            });

            // ---- Idle auto-advance ----
            if (!prefersReduced) {
                var paused = false;
                rail.addEventListener('pointerenter', function () { paused = true; });
                rail.addEventListener('pointerleave', function () { paused = false; });
                rail.addEventListener('focusin', function () { paused = true; });
                rail.addEventListener('focusout', function () { paused = false; });

                var last = null;
                function tick(now) {
                    if (last === null) last = now;
                    var dt = now - last;
                    last = now;
                    if (!paused && !isDown) {
                        // ~40px per second drift to the right.
                        rail.scrollLeft += (dt / 1000) * 40;
                        recenter();
                    }
                    window.requestAnimationFrame(tick);
                }
                window.requestAnimationFrame(tick);
            }
        })();
    </script>
</section>
