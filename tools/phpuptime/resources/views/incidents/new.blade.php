@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Incident'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.incidents') : route('incidents'), 'title' => __('Incidents')],
                        ['title' => __('New')],
                    ]])

                    <div class="d-flex">
                        <h1 class="h2 mb-3 text-break">{{ __('New') }}</h1>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Incident') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ route('incidents.new') }}" method="post" enctype="multipart/form-data" id="form-incident">
                                @csrf

                                <div class="form-group">
                                    <label for="i-monitor-id">{{ __('Monitor') }}</label>
                                    <select name="monitor_id" id="i-monitor-id" class="custom-select{{ $errors->has('monitor_id') ? ' is-invalid' : '' }}" @if(count($monitors) == 0) disabled @endif>
                                        @foreach($monitors as $monitor)
                                            <option value="{{ $monitor->id }}" @if(old('monitor_id') == $monitor->id) selected @endif>{{ $monitor->name }} ({{ $monitor->displayUrl }})</option>
                                        @endforeach
                                    </select>
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
                                                <input type="datetime-local" step="1" dir="ltr" name="started_at" class="form-control{{ $errors->has('started_at') ? ' is-invalid' : '' }}" id="i-started-at" value="{{ old('started_at') ?? Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
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
                                                <input type="datetime-local" step="1" dir="ltr" name="ended_at" class="form-control{{ $errors->has('ended_at') ? ' is-invalid' : '' }}" id="i-ended-at" value="{{ old('ended_at') }}" placeholder="{{ Carbon\Carbon::now()->tz(Auth::user()->timezone ?? config('settings.timezone'))->format('Y-m-d\TH:i:s') }}">
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
                                    <textarea name="cause" id="i-cause" class="form-control{{ $errors->has('cause') ? ' is-invalid' : '' }}">{{ old('cause') ?? '' }}</textarea>
                                    @if ($errors->has('cause'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('cause') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-comment">{{ __('Comment') }}</label>
                                    <textarea name="comment" id="i-comment" class="form-control{{ $errors->has('comment') ? ' is-invalid' : '' }}">{{ old('comment') ?? '' }}</textarea>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
