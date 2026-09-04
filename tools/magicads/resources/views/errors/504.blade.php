{{--
    504 — Gateway Timeout.

    In this app a 504 most often surfaces during long-running image / video
    generation: the HTTP request times out at the proxy while the job keeps
    running on the backend. So this page is intentionally reassuring rather
    than alarming — the work isn't lost, it's still processing, and the
    finished creative will land in the gallery.
--}}
@extends('errors.layout')

@section('title', __('Still working on it'))
@section('label', __('Error 504'))
@section('code', '504')

@section('message')
    {{ __("Your generation is taking a little longer than usual, so we handed it off to finish in the background. You don't need to wait here — feel free to keep working, and your finished creative will appear automatically.") }}
@endsection

@section('extra')
    <div class="err__status" role="status" aria-live="polite">
        <span class="spinner" aria-hidden="true"></span>
        <span class="txt">
            <b>{{ __('Generating in the background') }}</b>
            <span>{{ __('Your creative will show up in the gallery once it’s ready.') }}</span>
        </span>
    </div>
@endsection

@section('actions')
    <a href="{{ route('user.studio.gallery') }}" class="err-btn err-btn--primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.5-3.5a2 2 0 0 0-2.8 0L5 21"/>
        </svg>
        {{ __('Go to my creatives') }}
    </a>
    <a href="{{ route('user.dashboard') }}" class="err-btn err-btn--dark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>
        </svg>
        {{ __('Back to dashboard') }}
    </a>
@endsection

@section('hint')
    {{ __('Processing continues even if you close this page.') }}
@endsection
