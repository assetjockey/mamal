@php
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Facades\Schema;

    // Favicon driven by general_settings.logo_favicon.
    //
    // $generalSettings is shared on frontend views via a view composer, but the
    // dashboard / auth / install layouts don't receive it — fall back to a
    // guarded lookup there. Defaults to the bundled /favicon.ico when nothing
    // is configured or the database isn't available yet (fresh installs).
    $faviconSetting = ($generalSettings ?? null)?->logo_favicon;

    if (blank($faviconSetting)) {
        try {
            if (Schema::hasTable('general_settings')) {
                $faviconSetting = \App\Models\GeneralSetting::query()->value('logo_favicon');
            }
        } catch (\Throwable $e) {
            $faviconSetting = null;
        }
    }

    $faviconUrl = filled($faviconSetting)
        ? (str_starts_with((string) $faviconSetting, 'http') ? $faviconSetting : URL::asset($faviconSetting))
        : asset('favicon.ico');

    // Pick a sensible MIME type from the extension so browsers don't have to sniff.
    $faviconExt = strtolower(pathinfo(parse_url((string) $faviconUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
    $faviconType = match ($faviconExt) {
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'ico' => 'image/x-icon',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        default => null,
    };
@endphp

<link rel="icon" href="{{ $faviconUrl }}" sizes="any"@if ($faviconType) type="{{ $faviconType }}"@endif>
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
