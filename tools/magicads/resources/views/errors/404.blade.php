{{-- 404 — Not Found. Resource doesn't exist or has moved. --}}
@extends('errors.layout')

@section('title', __('Page not found'))
@section('label', __('Error 404'))
@section('code', '404')

@section('message')
    {{ __("The page you're looking for doesn't exist, may have been moved, or the link is broken. Let's get you back on track.") }}
@endsection

@section('actions')
    <a href="{{ url('/') }}" class="err-btn err-btn--dark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>
        </svg>
        {{ __('Back to home') }}
    </a>
    <button type="button" onclick="if (document.referrer) { history.back() } else { window.location.href = '{{ url('/') }}' }" class="err-btn err-btn--outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
        </svg>
        {{ __('Go back') }}
    </button>
@endsection

@section('hint')
    {{ __('Think this is a mistake?') }} <a href="{{ route('contact') }}">{{ __('Contact support') }}</a>
@endsection
