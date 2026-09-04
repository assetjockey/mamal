{{--
    500 — Server Error. Something broke on our end.

    Kept maximally dependency-free: only url() helpers (no route() lookups),
    since this page may render while the app is in a degraded state.
--}}
@extends('errors.layout')

@section('title', __('Something went wrong'))
@section('label', __('Error 500'))
@section('code', '500')

@section('message')
    {{ __("An unexpected error occurred on our end. This isn't your fault — our team has been notified. Please try again in a moment.") }}
@endsection

@section('actions')
    <a href="{{ url()->current() }}" class="err-btn err-btn--primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/>
        </svg>
        {{ __('Try again') }}
    </a>
    <a href="{{ url('/') }}" class="err-btn err-btn--dark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>
        </svg>
        {{ __('Back to home') }}
    </a>
@endsection

@section('hint')
    {{ __('If the problem keeps happening, please') }} <a href="{{ url('/contact') }}">{{ __('let us know') }}</a>.
@endsection
