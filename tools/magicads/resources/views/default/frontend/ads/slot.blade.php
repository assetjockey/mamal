{{--
    Reusable Google AdSense ad slot.

    Renders a single ad unit for a given placement key — but ONLY when:
      • AdSense is enabled and a publisher ID is set, and
      • the admin has configured a slot ID for this exact placement.

    Otherwise it outputs nothing, so spots stay invisible until set up.

    Usage:  @include('frontend.ads.slot', ['placement' => 'home_top'])

    Data:   $googleAdsense is shared globally by the `layouts.frontend`
            View::composer in AppServiceProvider.
--}}
@php
    $placement = $placement ?? null;
    $meta = $placement ? (\App\Models\GoogleAdsense::PLACEMENTS[$placement] ?? null) : null;
    $render = $placement
        && $meta
        && isset($googleAdsense)
        && $googleAdsense
        && $googleAdsense->hasPlacement($placement);
@endphp

@if ($render)
    @php
        $slotId = $googleAdsense->slotFor($placement);
        $format = $meta['format'] ?? 'auto';
        // Fluid (in-article) units use a specific layout key + format.
        $isFluid = $format === 'fluid';
    @endphp

    <div class="l-ad-slot mx-auto my-8 w-full max-w-5xl px-4 sm:px-6 lg:px-8" aria-hidden="true">
        <div class="flex flex-col items-center">
            <span class="mb-2 text-[10px] font-medium uppercase tracking-[0.18em] text-black/30">{{ __('Advertisement') }}</span>

            <ins class="adsbygoogle block w-full text-center"
                 style="display:block"
                 data-ad-client="{{ $googleAdsense->publisher_id }}"
                 data-ad-slot="{{ $slotId }}"
                 @if ($isFluid)
                     data-ad-layout="in-article"
                     data-ad-format="fluid"
                 @else
                     data-ad-format="{{ $format === 'auto' ? 'auto' : $format }}"
                     data-full-width-responsive="true"
                 @endif
            ></ins>
        </div>
    </div>

    <script>
        (window.adsbygoogle = window.adsbygoogle || []).push({});
    </script>
@endif
