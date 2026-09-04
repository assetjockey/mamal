{{--
    Google Analytics (gtag.js) — frontend snippet.

    Rendered only when:
      1. The admin has enabled "Google Analytics for Homepage" in
         Admin → Backend → Global Settings (general_settings.google_analytics_homepage = true)
      2. A valid tracking ID exists in admin_keys.google_analytics_tracking_id

    The tracking ID is pushed into runtime config by
    AppServiceProvider::configureGoogleServices() on every boot.

    $generalSettings is shared by the `layouts.frontend` View::composer.
--}}
@if (($generalSettings->google_analytics_homepage ?? false) && config('services.google.analytics.tracking_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics.tracking_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google.analytics.tracking_id') }}');
    </script>
@endif
