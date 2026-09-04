{{--
    Google AdSense loader — injected into <head> of the frontend layout.

    Loads the AdSense library exactly once (and only when ads are enabled with
    a publisher ID). When Auto Ads is on, enables page-level auto ads. Manual
    ad units are rendered separately via frontend.ads.slot.

    $googleAdsense is shared by the `layouts.frontend` View::composer.
--}}
@if (isset($googleAdsense) && $googleAdsense && $googleAdsense->isActive())
    <script async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $googleAdsense->publisher_id }}"
            crossorigin="anonymous"></script>

    @if ($googleAdsense->auto_ads)
        <script>
            (window.adsbygoogle = window.adsbygoogle || []).push({
                google_ad_client: "{{ $googleAdsense->publisher_id }}",
                enable_page_level_ads: true
            });
        </script>
    @endif
@endif
