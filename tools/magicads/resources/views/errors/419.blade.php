{{-- 419 — Page Expired. CSRF token mismatch / session timed out. --}}
@extends('errors.layout')

@section('title', __('Page expired'))
@section('label', __('Error 419'))
@section('code', '419')

@section('message')
    {{ __('Your session expired for security reasons, usually because the page sat idle for too long. Refresh the page and try again.') }}
@endsection

@section('actions')
    <a href="{{ url()->current() }}" class="err-btn err-btn--primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/>
        </svg>
        {{ __('Refresh page') }}
    </a>
    <a href="{{ url('/') }}" class="err-btn err-btn--dark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M9 21v-6h6v6"/>
        </svg>
        {{ __('Back to home') }}
    </a>
@endsection

@section('hint')
    {{ __('Still stuck after refreshing?') }} <a href="{{ route('contact') }}">{{ __('Contact support') }}</a>
@endsection
