@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Payment'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                ['url' => request()->is('admin/*') ? route('admin.payments') : route('account'), 'title' => request()->is('admin/*') ? __('Payments') : __('Account')],
                !request()->is('admin/*') ? ['url' => route('account.payments'), 'title' => __('Payments')] : null,
                ['title' => __('Edit')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                </div>
                <div class="col-auto px-2">
                    <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                    @include('account.payments.partials.context-menu')
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Payment') }}</div>
                </div>
                <div class="card-body mb-n4">
                    @include('shared.message')

                    <form action="{{ route('admin.payments.edit', $payment->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Plan') }}</div>
                                @if (request()->is('admin/*'))
                                    <a href="{{ route('admin.plans.edit', ['id' => $payment->product->id]) }}">{{ $payment->product->name }}</a>
                                @else
                                    <div>{{ $payment->product->name }}</div>
                                @endif
                            </div>

                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Payment ID') }}</div>
                                <div>{{ $payment->payment_id }}</div>
                            </div>

                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Processor') }}</div>
                                <div>{{ config('payment.processors.' . $payment->processor)['name'] }}</div>
                            </div>

                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Amount') }}</div>
                                <div>{{ formatMoney($payment->amount, $payment->plan->currency) }} {{ $payment->plan->currency }} / <span class="text-lowercase">{{ $payment->interval == 'month' ? __('Month') : __('Year') }}</span></div>
                            </div>

                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Status') }}</div>
                                <div>
                                    @if($payment->status == 'completed')
                                        {{ __('Completed') }}
                                    @elseif($payment->status == 'pending')
                                        {{ __('Pending') }}
                                    @else
                                        {{ __('Cancelled') }}
                                    @endif
                                </div>
                            </div>

                            @if(config('settings.invoicing'))
                                @if((request()->is('admin/*') && in_array($payment->status, ['completed', 'cancelled'])) || $payment->status == 'completed')
                                    <div class="col-12 col-lg-6 mb-4">
                                        <div class="text-muted">{{ __('Invoice') }}</div>
                                        <div><a href="{{ (request()->is('admin/*') ? route('admin.invoices.show', $payment->id) : route('account.invoices.show', $payment->id)) }}">{{ $payment->invoice_id }}</a></div>
                                    </div>
                                @endif
                            @endif

                            <div class="col-12 col-lg-6 mb-4">
                                <div class="text-muted">{{ __('Created at') }}</div>
                                <div>{{ $payment->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(request()->is('admin/*'))
                <div class="row m-n2 pt-4">
                    @if ($payment->user)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <img src="{{ $payment->user->avatar_url }}" class="w-8 h-8 rounded-circle" alt="">

                                    <a href="{{ route('admin.users.edit', ['id' => $payment->user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link mx-4">{{ $payment->user->name }}</a>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full cursor-default">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <img src="{{ asset('img/user.png') }}" class="w-8 h-8 rounded-circle" alt="{{ __('User') }}">

                                    <span class="fw-medium text-decoration-none text-truncate me-2 ms-4">{{ __('Deleted') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@if(request()->is('admin/*'))
    @include('shared.sidebars.admin')
@else
    @include('shared.sidebars.user')
@endif
