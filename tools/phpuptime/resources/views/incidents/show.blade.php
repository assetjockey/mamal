@extends('layouts.app')

@section('site_title', formatTitle([$incident->monitor->name, __('Incident'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.incidents') : route('incidents'), 'title' => __('Incidents')],
                        ['title' => __('Incident')],
                    ]])

                    <div class="d-flex align-items-end mb-3">
                        <h1 class="h2 mb-0 flex-grow-1 text-truncate">{{ __('Incident') }}</h1>

                        <div class="d-flex align-items-center flex-grow-0">
                            <div class="form-row flex-nowrap">
                                <div class="col">
                                    <a href="#" class="btn text-secondary d-flex align-items-center" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current width-4 height-4'])&#8203;</a>

                                    @include('incidents.partials.context-menu')
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row m-n2">
                        <div class="col-12 col-lg-4 p-2 order-2 order-lg-1">
                            <div class="card border-0 rounded-top shadow-sm overflow-hidden">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col">
                                            <div class="font-weight-medium py-1">{{ __('Summary') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row m-n2">
                                        <div class="col-12 col-lg-12 p-2 text-truncate">
                                            <div class="text-muted small">
                                                {{ __('Status') }}
                                            </div>
                                            <div class="font-weight-medium">
                                                <span class="{{ ($incident->status == 'resolved' ? 'text-success' : ($incident->status == 'unresolved' ? 'text-danger' : 'text-warning')) }}">{{ __(Str::ucfirst(__($incident->status))) }}</span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Cause') }}
                                            </div>
                                            <div class="font-weight-medium">
                                                {{ ($incident->cause ? __($incident->cause) : __('No data')) }}
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Location') }}
                                            </div>
                                            <div class="d-flex align-items-center font-weight-medium text-truncate">
                                                <div class="d-inline-flex align-items-center">
                                                    <div class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">
                                                        <img src="{{ asset('img/icons/countries/' . flagIcon($incident->country)) }}.svg" class="width-4 height-4">
                                                    </div>
                                                    <span class="text-truncate" data-tooltip="true" title="@if(!empty(explode(':', $incident->country)[1])) {{ __(explode(':', $incident->country)[1]) }}@if(!empty(explode(':', $incident->city)[1])), {{ __(explode(':', $incident->city)[1]) }}@endif @else {{ __('Unknown') }} @endif @if(Auth::check() && Auth::user()->isAdmin() && $incident->check_ip) ({{ $incident->check_ip }}) @endif">
                                                        @if(!empty(explode(':', $incident->country)[1]))
                                                            {{ explode(':', $incident->country)[1] }}@if(!empty(explode(':', $incident->city)[1]))
                                                                , {{ explode(':', $incident->city)[1] }}
                                                            @endif
                                                        @else
                                                            {{ __('Unknown') }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                <label for="i-comment" class="mb-0">{{ __('Comment') }}</label>
                                            </div>
                                            <div class="font-weight-medium">
                                                @if (Auth::check() && $incident->user_id == Auth::user()->id)
                                                    <form action="{{ route('incidents.edit', $incident->id) }}" method="post" enctype="multipart/form-data">

                                                        @csrf

                                                        <div class="row m-n1 pt-2 {{ $errors->has('comment') || old('comment') ? '' : 'd-none' }}" data-show-container="comment">
                                                            <div class="col-12 p-1">
                                                                <div class="form-group mb-0">
                                                                    <textarea name="comment" id="i-comment" class="form-control{{ $errors->has('comment') ? ' is-invalid' : '' }}" data-show-input  {{ $errors->has('comment') || old('comment') ? '' : ' disabled' }}>{{ old('comment') ?? $incident->comment }}</textarea>
                                                                    @if ($errors->has('comment'))
                                                                        <span class="invalid-feedback" role="alert">
                                                                            <strong>{{ $errors->first('comment') }}</strong>
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="col-auto p-1">
                                                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Save') }}</button>
                                                            </div>

                                                            <div class="col-auto p-1">
                                                                <a href="#" class="btn btn-sm btn-secondary" data-show-hide-action>{{ __('Cancel') }}</a>
                                                            </div>
                                                        </div>

                                                        <span data-show-content="comment" class="{{ $errors->has('comment') || old('comment') ? 'd-none' : '' }}">{{ $incident->comment }}</span>

                                                        <span class="{{ $errors->has('comment') || old('comment') ? 'd-none' : '' }}" data-show="comment">
                                                             <a href="#">{{ ($incident->comment ? __('Edit') : __('Add')) }}</a>
                                                        </span>
                                                    </form>
                                                @endif

                                                <script>
                                                    'use strict';

                                                    window.addEventListener('DOMContentLoaded', function () {
                                                        // Handle the hiding of the displayed form containers
                                                        document.querySelectorAll('[data-show-container]').forEach(function (containerElement) {
                                                            containerElement.querySelectorAll('[data-show-hide-action]').forEach(function (hideActionElement) {
                                                                hideActionElement.addEventListener('click', function (e) {
                                                                    e.preventDefault();

                                                                    // Get all the show action elements
                                                                    document.querySelectorAll('[data-show="' + containerElement.dataset.showContainer + '"]').forEach(function (showActionElement) {
                                                                        // Display the show action elements
                                                                        showActionElement.classList.remove('d-none');
                                                                    });

                                                                    // Get all the content elements
                                                                    document.querySelectorAll('[data-show-content="' + containerElement.dataset.showContainer + '"]').forEach(function (showActionElement) {
                                                                        // Display the content elements
                                                                        showActionElement.classList.remove('d-none');
                                                                    });

                                                                    // Get all the displayed containers
                                                                    document.querySelectorAll('[data-show-container="' + containerElement.dataset.showContainer + '"]').forEach(function (containerElement) {
                                                                        // Display the show action elements
                                                                        containerElement.classList.add('d-none');
                                                                    });

                                                                    // Disable the displayed form inputs
                                                                    containerElement.querySelectorAll('[data-show-input]').forEach(function (containerInput) {
                                                                        containerInput.setAttribute('disabled', 'disabled');
                                                                    });
                                                                });
                                                            });
                                                        });

                                                        // Handle the showing of the form container to be shown
                                                        document.querySelectorAll('[data-show]').forEach(function (element) {
                                                            element.addEventListener('click', function (e) {
                                                                e.preventDefault();

                                                                // Get all the show action elements
                                                                document.querySelectorAll('[data-show="' + element.dataset.show + '"]').forEach(function (showActionElement) {
                                                                    // Hide the show action element
                                                                    showActionElement.classList.add('d-none');
                                                                });

                                                                // Get all the content elements
                                                                document.querySelectorAll('[data-show-content="' + element.dataset.show + '"]').forEach(function (showActionElement) {
                                                                    // Hide the content element
                                                                    showActionElement.classList.add('d-none');
                                                                });

                                                                // Get all the containers
                                                                document.querySelectorAll('[data-show-container="' + element.dataset.show + '"]').forEach(function (elementContainer) {
                                                                    // Show the containers
                                                                    elementContainer.classList.remove('d-none');

                                                                    // Activate the inputs
                                                                    elementContainer.querySelectorAll('[data-show-input]').forEach(function (containerInput) {
                                                                        containerInput.removeAttribute('disabled');
                                                                    });
                                                                });
                                                            });
                                                        });
                                                    });
                                                </script>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Duration') }}
                                            </div>
                                            <div class="d-flex align-items-center font-weight-medium text-truncate">
                                                @php
                                                    Carbon\Carbon::disableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                @endphp
                                                {{ $incident->started_at->diffForHumans($incident->ended_at ?? Carbon\Carbon::now(), ['syntax' => true, 'parts' => 3, 'join' => true]) }}
                                                @php
                                                    Carbon\Carbon::enableHumanDiffOption(Carbon\Carbon::NO_ZERO_DIFF);
                                                @endphp
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Started at') }}
                                            </div>
                                            <div class="font-weight-medium">
                                                <div data-tooltip="true" title="{{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}">{{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Ended at') }}
                                            </div>
                                            <div class="font-weight-medium">
                                                @if ($incident->ended_at)
                                                    <span data-tooltip="true" title="{{ $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}">{{ $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s') }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ __('Ongoing') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body border-top">
                                    <div class="row m-n2">
                                        <div class="col-12 col-lg-12 p-2">
                                            <div class="text-muted small">
                                                {{ __('Monitor') }}
                                            </div>
                                            <div class="font-weight-medium">
                                                <div class="d-flex text-truncate align-items-center">
                                                    <a href="{{ route('monitors.overview', ['id' => $incident->monitor->id, 'token' => (Auth::check() && Auth::user()->isAdmin() || Auth::check() && $incident->monitor->user_id == Auth::user()->id ? null : $incident->monitor->monitor_token)]) }}" class="text-truncate">{{ $incident->monitor->name }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 small text-muted d-lg-none">
                                <div class="col">
                                    {{ __('Report generated on :date at :time (UTC :offset).', ['date' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('H:i:s'), 'offset' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->getOffsetString()]) }}
                                    <a href="{{ Request::fullUrl() }}" class="text-dark">{{ __('Refresh report') }}</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-8 p-2 order-1 order-lg-2">
                            <div class="card border-0 rounded-top shadow-sm overflow-hidden">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col">
                                            <div class="font-weight-medium py-1">{{ __('Timeline') }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body position-relative">
                                    <div class="position-absolute width-2 d-flex align-items-center justify-content-center flex-row top-8 bottom-8 {{ (__('lang_dir') == 'rtl' ? 'right-8' : 'left-8') }}">
                                        <div class="bg-secondary flex-grow-0 opacity-10 height-full width-px"></div>
                                        <div class="bg-secondary flex-grow-0 opacity-10 height-full width-px"></div>
                                    </div>

                                    <div class="row m-n3">
                                        @if($incident->ended_at)
                                            <div class="col-12 p-3">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1 position-relative">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-success rounded-circle opacity-10"></div>
                                                            @include('icons.check-circle-filled', ['class' => 'width-4 height-4 fill-current text-success position-absolute'])
                                                        </div>
                                                    </div>

                                                    <div class="col mt-1 p-1">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                {{ __('Incident resolved.') }}
                                                            </div>

                                                            <div class="col-12 small text-muted">
                                                                {{ $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($incident->acknowledged_at)
                                            <div class="col-12 p-3">
                                                <div class="row m-n1">
                                                    <div class="col-auto p-1 position-relative">
                                                        <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                            <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                            <div class="width-6 height-6 bg-warning rounded-circle opacity-10"></div>
                                                            @include('icons.offline-bolt-filled', ['class' => 'width-4 height-4 fill-current text-warning position-absolute'])
                                                        </div>
                                                    </div>

                                                    <div class="col mt-1 p-1">
                                                        <div class="row">
                                                            <div class="col-12">
                                                                {{ __('Incident acknowledged.') }}
                                                            </div>

                                                            <div class="col-12 small text-muted">
                                                                {{ $incident->acknowledged_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($incident->alerted)
                                            @foreach($incident->alerted as $alert)
                                                @if($alert->key == 'email')
                                                    <div class="col-12 p-3">
                                                        <div class="row m-n1">
                                                            <div class="col-auto p-1 position-relative">
                                                                <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                                    <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                                    <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                                    @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                                </div>
                                                            </div>

                                                            <div class="col mt-1 p-1">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        {!! __('Sent out :service alert to :value.', ['service' => view('icons.email-filled', ['class' => 'width-4 height-4 fill-current text-info vertical-align-n0.5 mx-1']) .'<span class="font-weight-medium" data-tooltip="true" title="' . $alert->value . '">' . config('alert.channels')[$alert->key] . '</span>', 'value' => '<a href="mailto:' . $alert->value. '" class="font-weight-medium" data-tooltip="true" title="' . $alert->value . '" dir="ltr">' . $alert->value . '</a>']) !!}
                                                                    </div>

                                                                    <div class="col-12 small text-muted">
                                                                        {{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if ($incident->alerted)
                                            @foreach($incident->alerted as $alert)
                                                @if($alert->key == 'sms')
                                                    <div class="col-12 p-3">
                                                        <div class="row m-n1">
                                                            <div class="col-auto p-1 position-relative">
                                                                <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                                    <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                                    <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                                    @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                                </div>
                                                            </div>

                                                            <div class="col mt-1 p-1">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        {!! __('Sent out :service alert to :value.', ['service' => view('icons.sms-filled', ['class' => 'width-4 height-4 fill-current text-success vertical-align-n0.5 mx-1']) . '<span class="font-weight-medium" data-tooltip="true" title="' . $alert->value . '">' . config('alert.channels')[$alert->key] . '</span>', 'value' => '<a href="tel:' . $alert->value. '" class="font-weight-medium" data-tooltip="true" title="' . $alert->value . '" dir="ltr">' . $alert->value . '</a>']) !!}
                                                                    </div>

                                                                    <div class="col-12 small text-muted">
                                                                        {{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if ($incident->alerted)
                                            @foreach($incident->alerted as $alert)
                                                @if (in_array($alert->key, ['slack', 'teams', 'discord', 'flock', 'telegram', 'webhook']))
                                                    <div class="col-12 p-3">
                                                        <div class="row m-n1">
                                                            <div class="col-auto p-1 position-relative">
                                                                <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                                    <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                                    <div class="width-6 height-6 bg-secondary rounded-circle opacity-10"></div>
                                                                    @include('icons.notifications-circle-filled', ['class' => 'width-4 height-4 fill-current text-secondary position-absolute'])
                                                                </div>
                                                            </div>

                                                            <div class="col mt-1 p-1">
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        {!! __('Sent out :service alert.', ['service' => '<img src="' . asset('img/icons/brands/' . md5($alert->key) . '.svg') . '" class="width-4 height-4 vertical-align-n0.5 mx-1"><span class="font-weight-medium" data-tooltip="true" title="' . $alert->value . '" alt="' . config('alert.channels')[$alert->key] . '">' . config('alert.channels')[$alert->key] . '</span>']) !!}
                                                                    </div>

                                                                    <div class="col-12 small text-muted">
                                                                        {{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        <div class="col-12 p-3">
                                            <div class="row m-n1">
                                                <div class="col-auto p-1 position-relative">
                                                    <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                        <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                        <div class="width-6 height-6 bg-danger rounded-circle opacity-10"></div>
                                                        @include('icons.error-filled', ['class' => 'width-4 height-4 fill-current text-danger position-absolute'])
                                                    </div>
                                                </div>

                                                <div class="col mt-1 p-1">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            {{ __('Incident started.') }}
                                                        </div>

                                                        <div class="col-12 small text-muted">
                                                            {{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 p-3">
                                            <div class="row m-n1">
                                                <div class="col-auto p-1 position-relative">
                                                    <div class="width-8 height-8 d-flex align-items-center justify-content-center position-relative flex-shrink-0">
                                                        <div class="width-12 height-12 bg-base-0 rounded-circle position-absolute"></div>
                                                        <div class="width-6 height-6 bg-danger rounded-circle opacity-10"></div>
                                                        @include('icons.error-filled', ['class' => 'width-4 height-4 fill-current text-danger position-absolute'])
                                                    </div>
                                                </div>

                                                <div class="col mt-1 p-1">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            {!! __('Received :cause response from :location on :url.', ['cause' => '<span class="font-weight-medium">' . ($incident->cause ? e(__($incident->cause)) : __('No data')) . '</span>', 'url' => '<a href="' . $incident->url . '" target="_blank" rel="nofollow noreferrer noopener" class="font-weight-medium" data-tooltip="true" title="' . $incident->url . '" dir="ltr">' . $incident->displayUrl .'</a>',
                                                             'location' => '<img src="' . asset('img/icons/countries/' . flagIcon($incident->country)) . '.svg" class="width-4 height-4 vertical-align-n0.5 mx-1"><span class="font-weight-medium" data-tooltip="true" title="' . ((!empty(explode(':', $incident->country)[1])) ? __(explode(':', $incident->country)[1]) . ((!empty(explode(':', $incident->city)[1])) ? ', ' . __(explode(':', $incident->city)[1]) : '') : __('Unknown')) . (Auth::check() && Auth::user()->isAdmin() && $incident->check_ip ? ' (' . $incident->check_ip .')' : '' ) .'">' . ((!empty(explode(':', $incident->country)[1])) ? explode(':', $incident->country)[1] . ((!empty(explode(':', $incident->city)[1])) ? ', ' . explode(':', $incident->city)[1] : '') : __('Unknown')) .'</span>']) !!}
                                                        </div>

                                                        <div class="col-12 small text-muted">
                                                            {{ $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone')) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 small text-muted d-none d-lg-flex">
                                <div class="col">
                                    {{ __('Report generated on :date at :time (UTC :offset).', ['date' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('H:i:s'), 'offset' => (clone $now)->tz(Auth::user()->timezone ?? config('settings.timezone'))->getOffsetString()]) }}
                                    <a href="{{ Request::fullUrl() }}" class="text-dark">{{ __('Refresh report') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
