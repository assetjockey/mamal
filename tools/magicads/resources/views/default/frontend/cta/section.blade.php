{{-- Final CTA — large dark ink card with indigo spotlight + rotating ring. --}}
@php
    $registrationEnabled = class_exists(\Laravel\Fortify\Features::class)
        && \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration());

    $ctaHref = auth()->check()
        ? route('user.dashboard')
        : ($registrationEnabled ? route('register') : route('login'));

    $ctaLabel = auth()->check() ? __('Open dashboard') : __('Start free');
@endphp

<section class="relative py-16 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="l-card l-card--ink relative overflow-hidden p-10 sm:p-16">
            {{-- Indigo spotlight --}}
            <div aria-hidden="true"
                 class="absolute inset-0"
                 style="background: radial-gradient(circle at 30% 110%, rgba(79, 70, 229, 0.55), transparent 50%);"></div>

            {{-- Dot grid overlay --}}
            <div aria-hidden="true"
                 class="absolute inset-0 opacity-[0.08]"
                 style="background-image: radial-gradient(circle, #FFFFFF 1px, transparent 1px); background-size: 22px 22px;"></div>

            {{-- Orbital rings on the right --}}
            <div aria-hidden="true" class="absolute right-[-200px] top-1/2 hidden h-[600px] w-[600px] -translate-y-1/2 rounded-full border border-white/10 lg:block"></div>
            <div aria-hidden="true" class="l-rotate absolute right-[-150px] top-1/2 hidden h-[440px] w-[440px] -translate-y-1/2 rounded-full border border-dashed border-white/15 lg:block"></div>
            <div aria-hidden="true" class="absolute right-[-100px] top-1/2 hidden h-[280px] w-[280px] -translate-y-1/2 rounded-full border border-white/10 lg:block"></div>

            <div class="relative max-w-2xl">
                <span class="l-chip l-chip--indigo !bg-[#4F46E5]/20 !text-[#E0DEFF] !border-[#4F46E5]/40">
                    <span class="l-pulse inline-block h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                    {{ __('Ready when you are') }}
                </span>
                <h2 class="l-display mt-6 text-4xl font-extrabold leading-[1.02] tracking-[-0.025em] text-white sm:text-6xl">
                    {{ __('Ship your next campaign') }} <br>
                    <span class="l-accent" style="color: #A5A0FF;">{{ __('before the week is out.') }}</span>
                </h2>
                <p class="mt-6 max-w-lg text-[15px] text-white/60">
                    {{ __("Join the marketing teams producing image ads, video ads, and ad copy in minutes, not days. Start free and upgrade when you're ready to scale.") }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ $ctaHref }}" class="l-btn-indigo !px-6 !py-3.5">
                        <span>{{ $ctaLabel }}</span>
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 10h10m0 0-4-4m4 4-4 4"/>
                        </svg>
                    </a>
                    <a href="#showcase"
                       class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-transparent px-6 py-3 text-sm font-semibold text-white transition-colors hover:border-white/40 hover:bg-white/10">
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-white/40">
                            <svg class="h-2.5 w-2.5 translate-x-[1px]" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
                                <path d="M3 1.5v9l8-4.5z"/>
                            </svg>
                        </span>
                        {{ __('Watch 90s tour') }}
                    </a>
                </div>

                <ul role="list" class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 text-[12px] text-white/50">
                    @foreach ([__('No credit card'), __('Generate in <30s'), __('Cancel anytime')] as $i => $point)
                        @if ($i > 0)
                            <li aria-hidden="true" class="h-1 w-1 rounded-full bg-white/20"></li>
                        @endif
                        <li class="inline-flex items-center gap-1.5">
                            <svg class="h-3 w-3 text-[#4F46E5]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7 7a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L9 11.6l6.3-6.3a1 1 0 0 1 1.4 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
