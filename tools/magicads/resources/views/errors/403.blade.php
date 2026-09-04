{{-- 403 — Forbidden. The user is authenticated but not allowed to access this resource. --}}
@extends('errors.layout')

@section('title', __('Access denied'))
@section('label', __('Error 403'))
@section('code', '403')

@section('message')
    {{ __("You don't have permission to access this page. If you believe you should have access, check that you're signed in with the right account.") }}
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
