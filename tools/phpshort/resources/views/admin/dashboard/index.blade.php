@extends('layouts.app')

@section('site_title', formatTitle([__('Dashboard'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="bg-base-0">
            <div class="container py-12">
                <div class="d-flex">
                    <div class="row no-gutters w-full">
                        <div class="d-flex col-12 col-md">
                            <div class="flex-grow-1 d-flex align-items-center">
                                <div>
                                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ config('settings.title') }}</h1>

                                    <div class="d-flex flex-wrap">
                                        <div class="d-inline-block mt-2 me-6">
                                            <div class="d-flex">
                                                <div class="d-inline-flex align-items-center">
                                                    @include('icons.info', ['class' => 'text-muted fill-current w-4 h-4'])
                                                </div>

                                                <div class="d-inline-block ms-2">
                                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/changelog" class="text-inverse text-decoration-none d-flex align-items-center" target="_blank">{{ __('Version') }} <span class="badge badge-primary ms-2">{{ config('info.software.version') }}</span></a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-inline-block mt-2 me-6">
                                            <div class="d-flex">
                                                <div class="d-inline-flex align-items-center">
                                                    @include('icons.vpn-key', ['class' => 'text-muted fill-current w-4 h-4'])
                                                </div>

                                                <div class="d-inline-block ms-2">
                                                    <a href="{{ route('admin.settings', 'license') }}" class="text-inverse text-decoration-none d-flex align-items-center">{{ __('License') }} <span class="badge badge-primary ms-2">{{ config('settings.license_type') ? 'Extended' : 'Regular' }}</span></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-auto d-flex flex-row-reverse align-items-center"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-1">
            <div class="container pt-12 pb-16">
                <h4 class="fs-2xl fw-medium tracking-tight mb-4">{{ __('Overview') }}</h4>

                <div class="row m-n2">
                    @php
                        $cards = [
                            'users' =>
                            [
                                'title' => 'Users',
                                'value' => $stats['users'],
                                'route' => 'admin.users',
                                'icon' => 'people-alt'
                            ],
                            [
                                'title' => 'Pages',
                                'value' => $stats['pages'],
                                'route' => 'admin.pages',
                                'icon' => 'menu-book'
                            ],
                            [
                                'title' => 'Payments',
                                'value' => $stats['payments'],
                                'route' => 'admin.payments',
                                'icon' => 'credit-card'
                            ],
                            [
                                'title' => 'Plans',
                                'value' => $stats['plans'],
                                'route' => 'admin.plans',
                                'icon' => 'package'
                            ],
                        ];
                    @endphp

                    @foreach($cards as $card)
                        <div class="col-12 col-md-6 col-xl-3 p-2">
                            <div class="card border-0 shadow-sm h-full overflow-hidden">
                                <div class="card-body d-flex">
                                    <div class="d-flex position-relative text-primary w-10 h-10 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                        @include('icons.' . $card['icon'], ['class' => 'fill-current w-5 h-5'])
                                    </div>

                                    <div class="flex-grow-1"></div>

                                    <div class="d-flex align-items-center h2 fw-bold mb-0 text-truncate">
                                        {{ number_format($card['value'], 0, __('.'), __(',')) }}
                                    </div>
                                </div>
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route($card['route']) }}" class="text-secondary fw-medium d-inline-flex align-items-baseline w-full">{{ __($card['title']) }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h4 class="fs-2xl fw-medium tracking-tight mb-4 mt-12">{{ __('Activity') }}</h4>

                <div class="row m-n2">
                    <div class="col-12 col-xl-6 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-header align-items-center">
                                <div class="fw-medium py-1">{{ __('Latest users') }}</div>
                            </div>
                            <div class="card-body">
                                @if(count($latestUsers) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n4">
                                        @foreach($latestUsers as $user)
                                            <div class="list-group-item px-0">
                                                <div class="row align-items-center">
                                                    <div class="col text-truncate">
                                                        <div class="text-truncate">
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $user->avatarUrl }}" class="rounded-circle w-4 h-4 me-4" alt="">

                                                                <div class="text-truncate">
                                                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-truncate">{{ $user->name }}</a>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="w-4 flex-shrink-0 me-4"></div>
                                                                <div class="text-muted text-truncate small">
                                                                    {{ $user->email }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                        @include('admin.users.partials.context-menu', ['user' => $user])
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($latestUsers) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('admin.users') }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if(enabledPaymentProcessors())
                        <div class="col-12 col-lg-6 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-header align-items-center">
                                    <div class="fw-medium py-1">{{ __('Latest payments') }}</div>
                                </div>
                                <div class="card-body">
                                    @if(count($latestPayments) == 0)
                                        {{ __('No data') }}.
                                    @else
                                        <div class="list-group list-group-flush my-n4">
                                            @foreach($latestPayments as $payment)
                                                <div class="list-group-item px-0">
                                                    <div class="row align-items-center">
                                                        <div class="col text-truncate">
                                                            <div class="text-truncate">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ asset('img/icons/payments/' . $payment->processor . '.svg') }}" class="w-4 rounded-sm me-4" alt="">

                                                                    <div class="text-truncate d-flex align-items-center">
                                                                        <div class="text-truncate me-2">
                                                                            <a href="{{ route('admin.payments.edit', $payment->id) }}">{{ formatMoney($payment->amount, $payment->currency) }}</a> <span class="text-muted">{{ $payment->currency }}</span>
                                                                        </div>

                                                                        @if($payment->status == 'completed')
                                                                            <span class="badge badge-success text-truncate">{{ __('Completed') }}</span>
                                                                        @elseif($payment->status == 'pending')
                                                                            <span class="badge badge-secondary text-truncate">{{ __('Pending') }}</span>
                                                                        @else
                                                                            <span class="badge badge-danger text-truncate">{{ __('Cancelled') }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                                    <div class="text-muted text-truncate small">
                                                                        {{ $payment->plan->name }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                            @include('account.payments.partials.context-menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if(count($latestPayments) > 0)
                                    <div class="card-footer bg-base-2 border-0">
                                        <a href="{{ route('admin.payments') }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="col-12 col-lg-6 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-header align-items-center">
                                    <div class="fw-medium py-1">{{ __('Latest links') }}</div>
                                </div>
                                <div class="card-body">
                                    @if(count($latestLinks) == 0)
                                        {{ __('No data') }}.
                                    @else
                                        <div class="list-group list-group-flush my-n4">
                                            @foreach($latestLinks as $link)
                                                <div class="list-group-item px-0">
                                                    <div class="row align-items-center">
                                                        <div class="col d-flex text-truncate">
                                                            <div class="text-truncate">
                                                                <div class="d-flex align-items-center">
                                                                    <img src="{{ faviconUrl($link->url) }}" rel="noreferrer" class="w-4 h-4 me-4" alt="">

                                                                    <div class="text-truncate">
                                                                        <a href="{{ route('stats.overview', $link->id) }}" dir="ltr">{{ $link->displayShortUrl }}</a>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-center">
                                                                    <div class="w-4 flex-shrink-0 me-4"></div>
                                                                    <div class="text-muted text-truncate small cursor-help" data-tooltip="true" data-html="true" title='@include('links.partials.tooltip')'>
                                                                        <span dir="ltr">{{ $link->displayUrl }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto">
                                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                                            @include('links.partials.context-menu')
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if(count($latestLinks) > 0)
                                    <div class="card-footer bg-base-2 border-0">
                                        <a href="{{ route('admin.links') }}" class="text-secondary fw-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <h4 class="fs-2xl fw-medium tracking-tight mb-4 mt-12">{{ __('More') }}</h4>

                <div class="row m-n2">
                    <div class="col-12 col-lg-4 p-2">
                        <div class="card border-0 h-full shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.website', ['class' => 'fill-current w-6 h-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Website') }}</a>

                                    <div class="text-muted">
                                        {{ __('Visit the official website.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-2">
                        <div class="card border-0 h-full shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.book', ['class' => 'fill-current w-6 h-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Documentation') }}</a>

                                    <div class="text-muted">
                                        {{ __('Read the documentation.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4 p-2">
                        <div class="card border-0 h-full shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary w-12 h-12 align-items-center justify-content-center flex-shrink-0 me-4">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-xl"></div>
                                    @include('icons.history', ['class' => 'fill-current w-6 h-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/changelog" class="text-inverse fw-medium text-decoration-none stretched-link">{{ __('Changelog') }}</a>

                                    <div class="text-muted">
                                        {{ __('See what\'s new.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('shared.modals.share-link')
@endsection

@include('shared.sidebars.admin')