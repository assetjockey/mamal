{{-- Platforms — clean row, inline SVG monograms, indigo accents. --}}
@php
    $imagePresets = config('ai-studio.presets.image', []);
    $videoPresets = config('ai-studio.presets.video', []);

    $imageCount = collect($imagePresets)->flatten(1)->count();
    $videoCount = is_array($videoPresets) ? count($videoPresets) : 0;
    $presetCount = max(20, $imageCount + $videoCount);

    $platforms = [
        ['name' => __('Instagram'),      'mono' => 'IG'],
        ['name' => __('Facebook'),       'mono' => 'f'],
        ['name' => __('TikTok'),         'mono' => 'TT'],
        ['name' => __('YouTube'),        'mono' => 'YT'],
        ['name' => __('LinkedIn'),       'mono' => 'in'],
        ['name' => __('Google Display'), 'mono' => 'Gd'],
    ];
@endphp

<section id="platforms" class="relative py-16 sm:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="l-card l-noise relative overflow-hidden p-10 sm:p-14">
            {{-- Indigo accent corner --}}
            <div aria-hidden="true"
                 class="absolute -right-16 -top-16 h-56 w-56 rounded-full"
                 style="background: radial-gradient(circle, rgba(79,70,229,0.18), transparent 70%);"></div>

            <div class="relative grid gap-10 lg:grid-cols-[1fr_1.3fr] lg:items-center">
                <div>
                    <span class="l-chip l-chip--indigo">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]"></span>
                        {{ __('Platforms') }}
                    </span>
                    <h2 class="l-display mt-4 text-3xl font-extrabold leading-[1.05] tracking-[-0.025em] text-black sm:text-4xl">
                        {{ __('Every major channel,') }} <br>
                        <span class="l-accent">{{ __('covered.') }}</span>
                    </h2>
                    <p class="mt-4 max-w-md text-[14px] text-black/60">
                        {{ __(':count canvas presets across every major channel', ['count' => $presetCount]) }}. {{ __('One brief, every size.') }}
                    </p>
                    <div class="mt-6 flex items-baseline gap-3">
                        <span class="l-display text-5xl font-extrabold tracking-tight text-black">{{ $presetCount }}+</span>
                        <span class="l-mono text-[11px] uppercase tracking-wider text-black/50">{{ __('presets') }}</span>
                    </div>
                </div>

                <ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($platforms as $p)
                        <li class="group flex items-center gap-3 rounded-2xl border border-[var(--l-hairline)] bg-white px-4 py-3.5 transition-all hover:-translate-y-0.5 hover:border-[#4F46E5] hover:shadow-[0_12px_28px_-10px_rgba(79,70,229,0.3)]">
                            <span aria-hidden="true" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-black text-[13px] font-bold text-white transition-colors group-hover:bg-[#4F46E5]">
                                {{ $p['mono'] }}
                            </span>
                            <span class="text-[13px] font-semibold text-black">{{ $p['name'] }}</span>
                            <span class="sr-only">{{ $p['name'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
