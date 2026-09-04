@extends('frontend.user.layouts.auth')
@section('title', lang('Pricing plans'))
@section('bg', 'bg-light')
@section('content')
    <div class="plans plans-page mb-5">
        <div class="plans-header text-center mt-5 mb-4">
            @if (request()->input('st') == 'subscribe')
                <i class="fas fa-check-circle"></i>
                <h2>{{ lang('Choose your plan to complete the subscription', 'user') }}</h2>
            @else
                <i class="far fa-gem"></i>
                <h2>{{ lang('Pricing plans') }}</h2>
            @endif
        </div>
        @if (count($lifetimePlans) > 0 || count($yearlyPlans) > 0)
            <div class="d-flex justify-content-center">
                <div class="plan-switcher">
                    <span class="plan-switcher-item active">{{ lang('Monthly', 'plans') }}</span>
                    <span class="plan-switcher-item">{{ lang('Yearly', 'plans') }}</span>
                    @if (count($lifetimePlans) > 0)
                        <span class="plan-switcher-item">{{ lang('Lifetime', 'plans') }}</span>
                    @endif
                </div>
            </div>
        @endif
        <div class="container mt-5">
            @include('frontend.includes.plans')
        </div>
    </div>
@endsection
