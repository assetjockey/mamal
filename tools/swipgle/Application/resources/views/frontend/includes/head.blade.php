@include('frontend.configurations.metaTags')
<title>{{ $settings['website_name'] }} @hasSection('title')— @yield('title') @endif</title>
<meta name="application-name" content="{{ $settings['website_name'] }}"/>
<meta name="msapplication-TileImage" content="{{ asset($settings['website_favicon']) }}"/>
<meta name="msapplication-TileColor" content="{{ asset($settings['website_primary_color']) }}"/>
<meta name="msapplication-square70x70logo" content="{{ asset($settings['website_favicon']) }}"/>
<meta name="msapplication-square150x150logo" content="{{ asset($settings['website_favicon']) }}"/>
<meta name="msapplication-wide310x150logo" content="{{ asset($settings['website_favicon']) }}"/>
<meta name="msapplication-square310x310logo" content="{{ asset($settings['website_favicon']) }}"/>
<link rel="apple-touch-icon-precomposed" href="{{ asset($settings['website_favicon']) }}"/>
<link rel="icon" href="{{ asset($settings['website_favicon']) }}" sizes="16x16 32x32 48x48 64x64"type="image/vnd.microsoft.icon"/>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700&display=swap">
