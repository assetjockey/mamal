@extends('layouts.app')

@section('site_title', formatTitle([Str::ucfirst(mb_strtolower(__('Verify Account'))), config('settings.title')]))

@section('content')
<div class="bg-base-1 d-flex align-items-center flex-fill">
    <div class="container">
        <div class="h-full d-flex flex-column justify-content-center align-items-center my-12">
            @if (request()->session()->get('resent'))
                <div class="alert alert-success mb-12" role="alert">
                    {{ __('A new verification link has been sent to your email address.') }}
                </div>
            @endif

            <div class="position-relative w-32 h-32 d-flex align-items-center justify-content-center">
                <div class="position-absolute top-0 end-0 bottom-0 start-0 bg-primary opacity-10 rounded-circle"></div>

                @include('icons.email', ['class' => 'text-primary fill-current w-16 h-16'])

                @include('icons.pending-filled', ['class' => 'position-absolute end-0 bottom-0 text-secondary fill-current w-8 h-8'])
            </div>

            <div>
                <h1 class="fs-xl fw-medium mb-2 mt-6 text-center">{{ Str::ucfirst(mb_strtolower(__('Verify Account'))) }}</h1>
                <p class="text-center text-muted mb-0">{{ __('Verify your account by accessing the link sent through email.') }}</p>

                <div class="text-center mt-12">
                    <div class="text-center text-muted">
                        <form class="d-inline" method="POST" action="{{ route('verification.resend') }}" id="resend-form">
                            @csrf

                            {{ __('Didn\'t receive the email?') }} <a href="{{ route('verification.resend') }}" onclick="event.preventDefault(); document.getElementById('resend-form').submit();">{{ __('Resend') }}</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')
