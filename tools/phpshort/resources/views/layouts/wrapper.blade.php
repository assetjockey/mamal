<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth {{ (config('settings.dark_mode') == 1 ? 'dark' : '') }}" dir="{{ (__('lang_dir') == 'rtl' ? 'rtl' : 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('site_title')</title>

    @section('favicon')
        <link href="{{ asset('uploads/brand/' . config('settings.favicon')) }}" rel="icon">
    @show

    <script src="{{ asset('js/app.js?v=' . config('info.software.version')) }}" defer></script>

    <link href="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . (config('settings.dark_mode') == 1 ? '.dark' : '').'.css?v=' . config('info.software.version')) }}" rel="stylesheet" data-theme-light="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . '.css?v=' . config('info.software.version')) }}" data-theme-dark="{{ asset('css/app'. (__('lang_dir') == 'rtl' ? '.rtl' : '') . '.dark.css?v=' . config('info.software.version')) }}" data-theme-target="href">

    @if (config('settings.pwa'))
        <link rel="manifest" href="{{ route('pwa.manifest') }}">
    @endif

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
