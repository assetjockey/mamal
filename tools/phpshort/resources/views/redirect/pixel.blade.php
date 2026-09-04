@extends('layouts.redirect-pixels')

@section('site_title', formatTitle([$link->title ?? $link->displayUrl]))

@section('head_content')
    <meta name="robots" content="noindex">

    @if($link->title)
        <meta property="og:title" content="{{ $link->title }}">
    @endif

    @if($link->description)
        <meta name="description" content="{{ $link->description }}">
        <meta property="og:description" content="{{ $link->description }}">
    @endif

    @if($link->image)
        <meta property="og:image" content="{{ $link->image }}">
    @endif

    <meta property="og:url" content="{{ $url }}">
@endsection

@section('content')
    @foreach($link->pixels as $pixel)
        @include('redirect.partials.pixels.' . $pixel->type)
    @endforeach

    <script>
        setTimeout(function () { window.location = '{!! htmlspecialchars($url) !!}'; }, 500);
    </script>
@endsection