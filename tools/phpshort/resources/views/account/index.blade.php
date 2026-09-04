@extends('layouts.app')

@section('site_title', formatTitle([__('Account'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('dashboard'), 'title' => __('Home')],
                ['title' => __('Account')]
            ]])

            <div class="row mx-n2">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Account') }}</h1>
                </div>
            </div>

            @php
                $settings[] = [
                    'icon' => 'account-box',
                    'title' => __('Profile'),
                    'description' => __('Update your profile information'),
                    'route' => route('account.profile')
                ];
                $settings[] = [
                    'icon' => 'lock',
                    'title' => __('Security'),
                    'description' => __('Change your security information'),
                    'route' => route('account.security')
                ];
                $settings[] = [
                    'icon' => 'package',
                    'title' => __('Plan'),
                    'description' => __('View your plan details'),
                    'route' => route('account.plan')
                ];
                if (enabledPaymentProcessors()) {
                    $settings[] = [
                        'icon' => 'credit-card',
                        'title' => __('Payments'),
                        'description' => (config('settings.invoicing') ? __('View your payments and invoices') : __('View your payments')),
                        'route' => route('account.payments')
                    ];
                }
                $settings[] = [
                    'icon' => 'code',
                    'title' => __('API'),
                    'description' => __('View and change your developer key'),
                    'route' => route('account.api')
                ];
                $settings[] = [
                    'icon' => 'delete',
                    'title' => __('Delete'),
                    'description' => __('Delete your account and associated data'),
                    'route' => route('account.delete')
                ];
            @endphp

            <div class="row">
                @foreach($settings as $setting)
                    <div class="col-12 mt-4">
                        <div class="card border-0 h-full shadow-sm">
                            <div class="card-body d-flex align-items-center text-truncate">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.' . $setting['icon'], ['class' => 'fill-current w-4 h-4'])
                                </div>

                                <a href="{{ $setting['route'] }}" class="text-inverse fw-medium stretched-link text-decoration-none text-truncate mx-4">{{ $setting['title'] }}</a>

                                <div class="text-muted d-flex align-items-center text-truncate ms-auto">
                                    <span class="text-truncate d-none d-md-inline-block">{{ $setting['description'] }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current mx-2'])
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
