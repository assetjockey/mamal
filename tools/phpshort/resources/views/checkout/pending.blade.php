@extends('layouts.app')

@section('site_title', formatTitle([__('Payment completed'), config('settings.title')]))

@section('content')
<div class="bg-base-1 d-flex align-items-center flex-fill">
    <div class="container">
        <div class="h-full d-flex flex-column justify-content-center align-items-center my-12">
            <div class="position-relative w-32 h-32 d-flex align-items-center justify-content-center">
                <div class="position-absolute top-0 end-0 bottom-0 start-0 bg-primary opacity-10 rounded-circle"></div>

                @include('icons.credit-card', ['class' => 'text-primary fill-current w-16 h-16'])

                @include('icons.pending-filled', ['class' => 'position-absolute end-0 bottom-0 text-secondary fill-current w-8 h-8'])
            </div>

            <div>
                <h1 class="fs-xl fw-medium mb-2 mt-6 text-center">{{ __('Payment pending') }}</h1>
                <p class="text-center text-muted mb-0">{{ __('The payment is pending approval.') }}</p>

                <div class="text-center mt-12">
                    <a href="{{ route('home') }}" class="btn btn-primary">{{ __('Dashboard') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')