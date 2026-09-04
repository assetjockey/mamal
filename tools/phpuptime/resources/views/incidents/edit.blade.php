@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Incident'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.incidents') : route('incidents'), 'title' => __('Incidents')],
                        ['title' => __('Edit')],
                    ]])

                    <div class="d-flex">
                        <h1 class="h2 mb-3 text-break">{{ __('Edit') }}</h1>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Incident') }}</div>
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
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ request()->is('admin/*') ? route('admin.incidents.edit', $incident->id) : route('incidents.edit', $incident->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @if(request()->is('admin/*'))
                                    <input type="hidden" name="user_id" value="{{ $incident->user->id }}">
                                @endif

                                <div class="form-group">
                                    <div class="d-flex align-items-center mb-2">
                                        <label for="i-monitor-id" class="d-flex align-items-center mb-0">
                                            {{ __('Monitor') }}
                                        </label>
                                    </div>
                                    <input id="i-monitor-id" class="form-control" value="{{ $incident->monitor->name }} ({{ $incident->monitor->displayUrl }})" disabled>
                                    @if ($errors->has('monitor_id'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('monitor_id') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-row mb-3">
                                    <div class="col-12"><label for="i-started-at">{{ __('Period') }}</label></div>
                                    <div class="col-12 col-lg-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <label for="i-started-at" class="input-group-text">{{ __('Started at') }}</label>
                                                </div>
                                                <input type="datetime-local" step="1" dir="ltr" name="started_at" class="form-control{{ $errors->has('started_at') ? ' is-invalid' : '' }}" id="i-started-at" value="{{ old('started_at') ?? ($incident->started_at ? $incident->started_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') : '') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}" disabled>
                                            </div>
                                            @if ($errors->has('started_at'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('started_at') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <label for="i-ended-at" class="input-group-text">{{ __('Ended at') }}</label>
                                                </div>
                                                <input type="datetime-local" step="1" dir="ltr" name="ended_at" class="form-control{{ $errors->has('ended_at') ? ' is-invalid' : '' }}" id="i-ended-at" value="{{ old('ended_at') ?? ($incident->ended_at ? $incident->ended_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') : '') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}" @if($incident->ended_at) disabled @endif>
                                            </div>
                                            @if ($errors->has('ended_at'))
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $errors->first('ended_at') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 mt-n3">
                                        <small class="form-text text-muted">{{ __('Leave the end date empty if the incident is ongoing.') }}</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="i-cause">{{ __('Cause') }}</label>
                                    <textarea name="cause" id="i-cause" class="form-control{{ $errors->has('cause') ? ' is-invalid' : '' }}">{{ $incident->cause }}</textarea>
                                    @if ($errors->has('cause'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cause') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-comment">{{ __('Comment') }}</label>
                                    <textarea name="comment" id="i-comment" class="form-control{{ $errors->has('comment') ? ' is-invalid' : '' }}">{{ $incident->comment }}</textarea>
                                    @if ($errors->has('comment'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('comment') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>

                    @if(request()->is('admin/*'))
                        <div class="row m-n2 pt-3">
                            <div class="col-12 col-md-6 col-lg-4 p-2">
                                <a href="{{ route('admin.users.edit', ['id' => $incident->user->id]) }}" class="text-decoration-none text-dark">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <img src="{{ $incident->user->avatar_url }}" alt="{{ $incident->user->name }}" class="width-8 height-8 rounded-circle">

                                            <span class="font-weight-medium text-decoration-none text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-3 mr-2' : 'mr-2 ml-3') }}">{{ $incident->user->name }}</span>

                                            @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 p-2">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body d-flex align-items-center text-truncate">
                                        <div class="d-flex position-relative text-primary width-8 height-8 align-items-center justify-content-center flex-shrink-0">
                                            <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-lg"></div>
                                            @include('icons.monitor-heart', ['class' => 'fill-current width-4 height-4'])
                                        </div>

                                        <a href="{{ route('admin.monitors.edit', ['id' => $incident->monitor_id]) }}" class="text-decoration-none font-weight-medium text-dark text-truncate stretched-link mx-3">{{ __('Monitor') }}</a>

                                        @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if(request()->is('admin/*'))
    @include('shared.sidebars.admin')
@else
    @include('shared.sidebars.user')
@endif
