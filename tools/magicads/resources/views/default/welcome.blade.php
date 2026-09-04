{{--
    AI Ad Studio — public marketing landing page.

    Thin composer: this file extends the shared `layouts.frontend` master
    layout and includes one self-contained section partial per vertical
    slice. Each partial lives at `frontend/<name>/section.blade.php` inside
    the active theme so the theme loader can override any slice per-theme
    without touching this file.

    The data the partials need (Plan collection + frontend settings) is
    injected by two View::composer closures registered in
    AppServiceProvider.
--}}
@extends('layouts.frontend')

@section('menu')
    @include('frontend.menu.section')
@endsection

@section('content')
    @include('frontend.hero.section')
    @include('frontend.ads.slot', ['placement' => 'home_top'])
    @include('frontend.features.section')
    @include('frontend.showcase.section')
    @include('frontend.how-it-works.section')
    @if (\App\Services\HelperService::extensionSaaS())
        @include('frontend.pricing.section')
    @endif
    @include('frontend.testimonials.section')
    @include('frontend.platforms.section')
    @include('frontend.blog.section')
    @include('frontend.faq.section')
    @include('frontend.ads.slot', ['placement' => 'home_bottom'])
    @include('frontend.cta.section')
@endsection

@section('footer')
    @include('frontend.footer.section')
@endsection
