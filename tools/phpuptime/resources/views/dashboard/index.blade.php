@extends('layouts.app')

@section('site_title', formatTitle([__('Dashboard'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="bg-base-0">
            <div class="container py-5">
                <div class="row m-n2">
                    <div class="d-flex col-12 col-md p-2">
                        <div class="flex-shrink-1">
                            <a href="{{ route('account') }}" class="d-block"><img src="{{ Auth::user()->avatarUrl }}" class="rounded-circle width-16 height-16"></a>
                        </div>
                        <div class="flex-grow-1 d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-3' : 'ml-3') }}">
                            <div>
                                <h4 class="font-weight-medium mb-0">{{ Auth::user()->name }}</h4>

                                <div class="d-flex flex-wrap">
                                    @if(enabledPaymentProcessors())
                                        <div class="d-inline-block mt-2 {{ (__('lang_dir') == 'rtl' ? 'ml-4' : 'mr-4') }}">
                                            <div class="d-flex">
                                                <div class="d-inline-flex align-items-center">
                                                    @include('icons.package', ['class' => 'text-muted fill-current width-4 height-4'])
                                                </div>

                                                <div class="d-inline-block {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                    <a href="{{ route('account.plan') }}" class="text-dark text-decoration-none">{{ Auth::user()->active_plan->name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-inline-block mt-2 {{ (__('lang_dir') == 'rtl' ? 'ml-4' : 'mr-4') }}">
                                            <div class="d-flex">
                                                <div class="d-inline-flex align-items-center">
                                                    @include('icons.email', ['class' => 'text-muted fill-current width-4 height-4'])
                                                </div>

                                                <div class="d-inline-block {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">
                                                    {{ Auth::user()->email }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-auto d-md-flex align-items-center p-2">
                        <div class="row m-n2">
                            @if(enabledPaymentProcessors())
                                <div class="col-12 col-md-auto d-flex align-items-center p-2">
                                    @if(Auth::user()->planIsDefault())
                                        <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.unarchive', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Upgrade') }}</a>
                                    @else
                                        <a href="{{ route('pricing') }}" class="btn btn-outline-primary btn-block d-flex justify-content-center align-items-center">@include('icons.package', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('Plans') }}</a>
                                    @endif
                                </div>
                            @endif

                            <div class="col-12 col-md-auto d-flex align-items-center p-2">
                                <a href="{{ route('monitors.new') }}" class="btn btn-primary btn-block d-flex justify-content-center align-items-center">@include('icons.add', ['class' => 'width-4 height-4 fill-current '.(__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]){{ __('New monitor') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-base-1">
            <div class="container pt-5 pb-6">
                <h4 class="mb-3">{{ __('Overview') }}</h4>

                <div class="row m-n2">
                    @php
                        $cards = [
                            [
                                'title' => 'Monitors',
                                'value' => Auth::user()->monitorsCount,
                                'route' => 'monitors',
                                'icon' => 'monitor-heart'
                            ],
                            [
                                'title' => 'Status pages',
                                'value' => Auth::user()->statusPages()->count(),
                                'route' => 'status_pages',
                                'icon' => 'podcasts'
                            ],
                            [
                                'title' => 'Incidents',
                                'value' => Auth::user()->incidents()->count(),
                                'route' => 'incidents',
                                'icon' => 'error'
                            ]
                        ];
                    @endphp

                    @foreach($cards as $card)
                        <div class="col-12 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                                <div class="card-body d-flex">
                                    <div class="d-flex position-relative text-primary width-10 height-10 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                        @include('icons.' . $card['icon'], ['class' => 'fill-current width-5 height-5'])
                                    </div>

                                    <div class="flex-grow-1"></div>

                                    <div class="d-flex align-items-center h2 font-weight-bold mb-0 text-truncate">
                                        {{ number_format($card['value'], 0, __('.'), __(',')) }}
                                    </div>
                                </div>
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route($card['route']) }}" class="text-muted font-weight-medium d-inline-flex align-items-baseline w-100">{{ __($card['title']) }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <h4 class="mb-3 mt-5">{{ __('Activity') }}</h4>

                <div class="row m-n2">
                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header align-items-center">
                                <div class="row">
                                    <div class="col">
                                        <div class="font-weight-medium py-1">{{ __('Latest monitors') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @if(count($latestMonitors) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n3">
                                        @foreach($latestMonitors as $monitor)
                                            <div class="list-group-item px-0">
                                                <div class="row align-items-center">
                                                    <div class="col d-flex text-truncate">
                                                        <div class="text-truncate">
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.http-status-tooltip')'>
                                                                    @include('icons.status', ['class' => 'width-4 height-4 fill-current text-' . monitorStatusColor($monitor->status)])&#8203;
                                                                </div>

                                                                <a href="{{ route('monitors.overview', ['id' => $monitor->id]) }}" class="text-truncate">{{ $monitor->name }}</a>

                                                                @if ($monitor->ssl_alert_days && parse_url($monitor->url, PHP_URL_SCHEME) == 'https' && Auth::user()->can('sslMonitoring', [App\Models\User::class]))
                                                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.ssl-status-tooltip')'>
                                                                        @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-' . monitorSslStatusColor($monitor)])&#8203;
                                                                    </div>
                                                                @endif

                                                                @if ($monitor->domain_alert_days && Auth::user()->can('domainMonitoring', [App\Models\User::class]))
                                                                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}" data-tooltip="true" data-html="true" title='@include('monitors.partials.domain-status-tooltip')'>
                                                                        @include('icons.website', ['class' => 'fill-current width-4 height-4 text-' . monitorDomainStatusColor($monitor)])&#8203;
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="d-flex align-items-center">
                                                                <div class="width-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}"></div>
                                                                <div class="text-muted text-truncate small cursor-help" data-toggle="tooltip-url" title="{{ $monitor->url }}">
                                                                    <span dir="ltr">{{ $monitor->displayUrl }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="form-row">
                                                            <div class="col">
                                                                @include('monitors.partials.context-menu')
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($latestMonitors) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('monitors') }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 col-lg-6 p-2">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header align-items-center">
                                <div class="row">
                                    <div class="col">
                                        <div class="font-weight-medium py-1">{{ __('Latest incidents') }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @if(count($latestIncidents) == 0)
                                    {{ __('No data') }}.
                                @else
                                    <div class="list-group list-group-flush my-n3">
                                        @foreach($latestIncidents as $incident)
                                            <div class="list-group-item px-0">
                                                <div class="row align-items-center">
                                                    <div class="col d-flex text-truncate">
                                                        <div class="text-truncate">
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}" data-tooltip="true" data-html="true" title='@include('incidents.partials.tooltip')'>
                                                                    @include('icons.' . ($incident->status == 'resolved' ? 'check-circle-filled' : ($incident->status == 'unresolved' ? 'error-filled' : 'offline-bolt-filled')), ['class' => 'width-4 height-4 fill-current ' . ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning'))])&#8203;
                                                                </div>

                                                                <div class="text-truncate">
                                                                    <a href="{{ route('incidents.show', ['id' => $incident->id]) }}">{{ $incident->monitor->name }}</a>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center text-truncate">
                                                                <div class="width-4 flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}"></div>

                                                                <div class="text-muted text-truncate small d-flex align-items-center">
                                                                    {{ ($incident->cause ? __($incident->cause) : __('No data')) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="form-row">
                                                            <div class="col">
                                                                @include('incidents.partials.context-menu')
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(count($latestIncidents) > 0)
                                <div class="card-footer bg-base-2 border-0">
                                    <a href="{{ route('incidents') }}" class="text-muted font-weight-medium d-flex align-items-center justify-content-center">{{ __('View all') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{--<h4 class="mb-3 mt-5">{{ __('More') }}</h4>

                <div class="row m-n2">
                    <div class="col-12 col-xl-4 p-2">
                        <div class="card border-0 h-100 shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary width-12 height-12 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.monitor-heart', ['class' => 'fill-current width-6 height-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ route('monitors') }}" class="text-dark font-weight-medium text-decoration-none stretched-link">{{ __('Monitors') }}</a>

                                    <div class="text-muted">
                                        {{ __('Manage the monitors.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4 p-2">
                        <div class="card border-0 h-100 shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary width-12 height-12 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.podcasts', ['class' => 'fill-current width-6 height-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ route('status_pages') }}" class="text-dark font-weight-medium text-decoration-none stretched-link">{{ __('Status pages') }}</a>

                                    <div class="text-muted">
                                        {{ __('Manage the status pages.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4 p-2">
                        <div class="card border-0 h-100 shadow-sm">
                            <div class="card-body d-flex">
                                <div class="d-flex position-relative text-primary width-12 height-12 align-items-center justify-content-center flex-shrink-0 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-xl"></div>
                                    @include('icons.error', ['class' => 'fill-current width-6 height-6'])
                                </div>
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="{{ route('incidents') }}" class="text-dark font-weight-medium text-decoration-none stretched-link">{{ __('Incidents') }}</a>

                                    <div class="text-muted">
                                        {{ __('Manage the incidents.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>--}}
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
