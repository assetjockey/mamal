@extends('layouts.status-page')

@section('site_title', formatTitle(($statusPage->meta_title ? e($statusPage->meta_title) : [$statusPage->name, __('Status page')])))

@section('head_content')
    @if ($statusPage->noindex)
        <meta name="robots" content="noindex">
    @endif

    @if ($statusPage->favicon)
        <link rel="icon" type="image/png" href="{{ $statusPage->faviconUrl }}">
    @endif

    <meta property="og:title"
          content="{{ formatTitle(($statusPage->meta_title ? e($statusPage->meta_title) : [$statusPage->name, __('Status page')])) }}">

    <meta name="description"
          content="{{ $statusPage->meta_description ?: __('Welcome to :name status page for real-time and historical website uptime data.', ['name' => $statusPage->name]) }}">
    <meta property="og:description"
          content="{{ $statusPage->meta_description ?: __('Welcome to :name status page for real-time and historical website uptime data.', ['name' => $statusPage->name]) }}">

    <meta http-equiv="refresh" content="{{ config('settings.status_page_refresh_interval') }}">

    <meta property="og:url" content="{{ route('status_pages.show', ['id' => $statusPage->id]) }}">
    <meta property="og:type" content="website">
    <meta property="og:image"
          content="{{ asset('img/status-page-' . (count($incidents) == 0 ? 'success' : (count($incidents) == count($monitors) ? 'danger' : 'warning')) .'.png') }}">

    @if ($statusPage->domain)
        @if ($statusPage->user->can('statusPageCustomization', [App\Models\User::class]))
            <style>
                @if ($statusPage->custom_css)
                    {!! $statusPage->custom_css !!}
                @endif
            </style>
        @endif

        @if ($statusPage->user->can('statusPageCustomization', [App\Models\User::class]))
            @if ($statusPage->custom_js)
                {!! $statusPage->custom_js !!}
            @endif
        @endif
    @endif
@endsection

@section('content')
    <div class="d-flex flex-column align-items-center justify-content-center">
        <div class="position-relative width-8 height-8 d-flex align-items-center justify-content-center mb-3">
            <div class="position-absolute top-0 right-0 bottom-0 left-0 opacity-10 rounded-circle {{ (count($monitors) == 0 ? 'bg-secondary' : (count($incidents) == 0 ? 'bg-success' : (count($incidents) == count($monitors) ? 'bg-danger' : 'bg-warning'))) }}"></div>
            @include('icons.' . (count($monitors) == 0 ? 'error-filled' : (count($incidents) == 0 ? 'check-circle-filled' : (count($incidents) == count($monitors) ? 'error-filled' : 'offline-bolt-filled'))), ['class' => 'fill-current width-6 height-6 ' . (count($monitors) == 0 ? 'text-secondary' : (count($incidents) == 0 ? 'text-success' : (count($incidents) == count($monitors) ? 'text-danger' : 'text-warning')))])
        </div>
        <h1 class="h2 pb-4 mb-3 d-inline-block font-weight-bold">{{ (count($monitors) == 0 ? __('No monitors.') : (count($incidents) == 0 ? __('All services are online') : (count($incidents) == count($monitors) ? __('All services are offline') : __('Some services are offline')))) }}</h1>
    </div>

    <div class="row m-n2">
        <div class="col-12 p-2">
            <div class="card border-0 shadow-sm border-radius-lg px-0" id="services-container">
                @if(count($monitors) == 0)
                    <div class="card-body">
                        {{ __('No data') }}.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($monitors as $monitor)
                            <div class="list-group-item px-0">
                                <div class="card-body py-3 service-container" id="service-{{ $monitor->id }}">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row">
                                                <div class="col d-flex align-items-center text-truncate">
                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}"
                                                         data-tooltip="true" data-html="true"
                                                         title='@include('monitors.partials.http-status-tooltip')'>
                                                        <div class="d-flex align-items-center">
                                                            @include('icons.status', ['class' => 'width-4 height-4 fill-current text-' . monitorStatusColor($monitor->status)])
                                                            &#8203;
                                                        </div>
                                                    </div>

                                                    <a href="{{ $monitor->url }}" rel="nofollow noreferrer noopener"
                                                       class="text-body text-truncate" data-tooltip="true"
                                                       title="{{ $monitor->url }}">{{ $monitor->name }}</a>

                                                    @if ($monitor->ssl_alert_days && parse_url($monitor->url, PHP_URL_SCHEME) == 'https' && $monitor->user->can('sslMonitoring', [App\Models\User::class]))
                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}"
                                                             data-tooltip="true" data-html="true"
                                                             title='@include('monitors.partials.ssl-status-tooltip')'>
                                                            @include('icons.lock', ['class' => 'fill-current width-4 height-4 text-' . monitorSslStatusColor($monitor)])
                                                            &#8203;
                                                        </div>
                                                    @endif

                                                    @if ($monitor->domain_alert_days && $monitor->user->can('domainMonitoring', [App\Models\User::class]))
                                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}"
                                                             data-tooltip="true" data-html="true"
                                                             title='@include('monitors.partials.domain-status-tooltip')'>
                                                            @include('icons.website', ['class' => 'fill-current width-4 height-4 text-' . monitorDomainStatusColor($monitor)])
                                                            &#8203;
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="col-auto text-muted">
                                                    @php
                                                        $fromNew = (Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->lt($monitor->created_at) ? $monitor->created_at : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay());

                                                        $toNew = (Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->isSameDay((clone $now)) || Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->gt((clone $now)) ? (clone $now) : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['to'], Auth::user()->timezone ?? config('settings.timezone'))->addDay()->startOfDay());
                                                    @endphp

                                                    {{ __(':value uptime', ['value' => formatUptimePercentageNumber((Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->lt($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))) ? $monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->diffInMicroseconds($toNew) : Carbon\Carbon::createFromFormat('Y-m-d', $dateRange['from'], Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()->diffInMicroseconds($toNew)), $monitor['totalIncidentsDuration'], 2, __('.'), __(',')) . '%']) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex justify-content-between mt-2 graph-container overflow-hidden rounded-lg">
                                                @foreach($rangeMap as $date => $value)
                                                    @php
                                                        $startDate = Carbon\Carbon::createFromFormat($dateRange['format'], $date, Auth::user()->timezone ?? config('settings.timezone'))->startOfDay();
                                                        $iteratedDate = $startDate;

                                                        $endDate = (clone $iteratedDate)->addDay()->startOfDay();

                                                        if ($iteratedDate->isSameDay($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))) && $iteratedDate->isSameDay((clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone')))) {
                                                            $startDate = $monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'));
                                                            $endDate = (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'));
                                                        } elseif ($iteratedDate->isSameDay($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone')))) {
                                                            $startDate = $monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'));
                                                        } elseif ($iteratedDate->isSameDay((clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone')))) {
                                                            $endDate = (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'));
                                                        }

                                                        $diffMicroseconds = $startDate->diffInMicroseconds($endDate);
                                                    @endphp

                                                    <div class="position-relative min-height-8 w-100 {{ ($loop->index > 59 ? '' : ($loop->index > 29 ? 'd-none d-md-block' : 'd-none d-lg-block')) }}">
                                                        <div class="position-absolute top-0 right-0 bottom-0 left-0 {{ ($monitor['incidentsMap'][$date] > 0 ? 'bg-danger' : (Carbon\Carbon::createFromFormat($dateRange['format'], $date, Auth::user()->timezone ?? config('settings.timezone'))->greaterThanOrEqualTo($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()) ? 'bg-success' : 'bg-base-1')) }}"
                                                             data-tooltip="true" data-html="true" title='
                                                        <div class="m-2 font-size-base {{ (__('lang_dir') == 'rtl' ? 'text-right' : 'text-left') }}">
                                                            <div class="d-flex flex-row">
                                                                <div>
                                                                {{ Carbon\Carbon::createFromFormat($dateRange['format'], $date, Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}
                                                                </div>
                                                            </div>
                                                            @if (Carbon\Carbon::createFromFormat($dateRange['format'], $date, Auth::user()->timezone ?? config('settings.timezone'))->greaterThanOrEqualTo($monitor->created_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->startOfDay()))
                                                                <div class="mt-1 font-size-sm d-flex align-items-center">
                                                                    @include('icons.analytics-filled', ['class' => 'width-4 height-4 fill-current text-light ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Uptime') }}: {{ number_format((-(($monitor['incidentsDurationMap'][$date] - $diffMicroseconds) / $diffMicroseconds) * 100), 2, __('.'), __(',')) . '%' }}
                                                                </div>
                                                                @if ($monitor['incidentsMap'][$date] > 0)
                                                                    <div class="mt-1 font-size-sm d-flex align-items-center">
                                                                        @include('icons.offline-bolt-filled', ['class' => 'width-4 height-4 fill-current text-danger ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Incidents') }}: {{ $monitor['incidentsMap'][$date] }} ({{ (clone $now)->diffForHumans((clone $now)->subMicroseconds($monitor['incidentsDurationMap'][$date]), ['syntax' => true, 'parts' => 2]) }})
                                                                    </div>
                                                                    @else
                                                                    <div class="mt-1 font-size-sm d-flex align-items-center">
                                                                        @include('icons.check-circle-filled', ['class' => 'width-4 height-4 fill-current text-success ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('No incidents') }}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <div class="mt-1 font-size-sm d-flex align-items-center">
                                                                    {{ __('No data') }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                        '></div>
                                                    </div>

                                                    @if (!$loop->last)
                                                        <div class="width-px flex-shrink-0 {{ ($loop->index > 59 ? '' : ($loop->index > 29 ? 'd-none d-md-block' : 'd-none d-lg-block')) }}"></div>
                                                        <div class="width-px flex-shrink-0 {{ ($loop->index > 59 ? '' : ($loop->index > 29 ? 'd-none d-md-block' : 'd-none d-lg-block')) }}"></div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="row text-muted small mt-2">
                                                <div class="col">
                                                    <span class="d-none d-lg-block">
                                                        {{ __('Last :days days', ['days' => 90]) }}
                                                    </span>
                                                    <span class="d-none d-md-block d-lg-none">
                                                        {{ __('Last :days days', ['days' => 60]) }}
                                                    </span>
                                                    <span class="d-block d-md-none">
                                                        {{ __('Last :days days', ['days' => 30]) }}
                                                    </span>
                                                </div>

                                                <div class="col-auto">
                                                    {{ __('Today') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(count($monitors) != 0)
        <div class="row mt-3 small text-muted">
            <div class="col">
                {{ __('Report generated on :date at :time (UTC :offset).', ['date' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('H:i:s'), 'offset' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->getOffsetString()]) }}
                <a href="{{ Request::fullUrl() }}" class="text-dark">{{ __('Refresh report') }}</a>
            </div>
        </div>
    @endif
@endsection
