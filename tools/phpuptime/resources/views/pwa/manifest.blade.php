{
    "name": "{{ config('settings.title') }}",
    "short_name": "{{ config('settings.title') }}",
    "start_url": "{{ config('app.url') }}",
    "scope": "{{ config('app.url') }}",
    "display": "{{ config('settings.pwa_display') }}",
    "background_color": "{{ config('settings.pwa_background_color') }}",
    "theme_color": "{{ config('settings.pwa_theme_color') }}",
    "orientation": "{{ 'portrait' }}",
    "icons": [
        {
            "src": "{{ asset('uploads/brand/' . config('settings.pwa_logo')) }}",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "any"
        },
        {
            "src": "{{ asset('uploads/brand/' . config('settings.pwa_logo_maskable')) }}",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "maskable"
        },
        {
            "src": "{{ asset('uploads/brand/' . config('settings.pwa_logo_monochrome')) }}",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "monochrome"
        }
    ]
}
