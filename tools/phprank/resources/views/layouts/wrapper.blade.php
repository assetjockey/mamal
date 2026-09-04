<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100 scroll-behavior-smooth {{ (config('settings.dark_mode') == 1 ? 'dark' : '') }}" dir="{{ (__('lang_dir') == 'rtl' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('site_title')</title>

    <link href="{{ asset('uploads/brand/' . config('settings.favicon')) }}" rel="icon">

    <!-- Scripts -->
    <script src="{{ asset('js/app.js?v=' . config('info.software.version')) }}" defer></script>

    <!-- Styles -->
    <link href="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . (config('settings.dark_mode') == 1 ? '.dark' : '').'.css?v=' . config('info.software.version')) }}" rel="stylesheet" data-theme-light="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . '.css?v=' . config('info.software.version')) }}" data-theme-dark="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . '.dark.css?v=' . config('info.software.version')) }}" data-theme-target="href">

    @yield('head_content')

    {!! config('settings.custom_js') !!}

    @if(config('settings.custom_css'))
        <style>
            {!! config('settings.custom_css') !!}
        </style>
    @endif
</head>
@yield('body')
</html>
