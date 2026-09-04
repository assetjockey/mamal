@extends('layouts.app')

@section('site_title', formatTitle([__('Pricing'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="flex-fill">
        <div class="bg-base-1">
            <div class="container py-16">
                @include('shared.message')

                <div class="text-center">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Pricing') }}</h1>
                    <div class="mx-auto mt-4">
                        <p class="text-muted fw-normal fs-lg">{{ __('Simple pricing plans for everyone and every budget.') }}</p>
                    </div>
                </div>

                @include('shared.pricing')
            </div>
        </div>
        <div class="bg-base-0">
            <div class="container py-16">
                <div class="text-center">
                    <h3 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Frequently asked questions') }}</h3>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6 mt-12 h-full">
                        <div class="fs-lg fw-medium mb-2">{{ __('What payment methods do you accept?') }}</div>
                        <div class="text-muted">{{ __('We support the following payment methods: :list.', ['list' => implode(', ', array_unique(array_map(function ($payment) { return __($payment['type']); }, enabledPaymentProcessors())))]) }}</div>
                    </div>

                    <div class="col-12 col-md-6 mt-12 h-full">
                        <div class="fs-lg fw-medium mb-2">{{ __('Can I change plans?') }}</div>
                        <div class="text-muted">{{ __('Yes, you can change your plan at any time.') }} {{ __('Upon switching plans, your current subscription will be cancelled immediately.') }}</div>
                    </div>

                    <div class="col-12 col-md-6 mt-12 h-full">
                        <div class="fs-lg fw-medium mb-2">{{ __('Can I cancel my subscription?') }}</div>
                        <div class="text-muted">{{ __('Yes, you can cancel your subscription at any time.') }} {{ __('You\'ll continue to have access to your plan features until the end of your billing cycle.') }}</div>
                    </div>

                    <div class="col-12 col-md-6 mt-12 h-full">
                        <div class="fs-lg fw-medium mb-2">{{ __('What happens when my subscription expires?') }}</div>
                        <div class="text-muted">{{ __('Once your subscription expires, you\'ll lose access to all the subscription features.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-base-1">
            <div class="container py-16 text-center">
                <div>
                    <h3 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Still have questions?') }}</h3>
                </div>

                <a href="{{ route('contact') }}" class="btn btn-primary btn-lg fs-lg mt-12">{{ __('Contact us') }}</a>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
