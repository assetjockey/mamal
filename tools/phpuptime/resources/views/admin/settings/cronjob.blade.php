@extends('layouts.app')

@section('site_title', formatTitle([__('Cron job'), __('Settings'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Settings')],
            ]])

            <h1 class="h2 mb-3 d-inline-block">{{ __('Cron job') }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="row">
                        <div class="col"><div class="font-weight-medium py-1">{{ __('Cron job') }}</div></div>
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col">
                                {{ __('Cron job automates background processes by running scheduled tasks at regular intervals.') }}
                            </div>
                            <div class="col-auto">
                                <a href="{{ config('info.software.url') }}/{{ mb_strtolower(config('info.software.name')) }}/documentation#cron-job" class="alert-link font-weight-medium" target="_blank">{{ __('Learn more') }}</a>
                            </div>
                        </div>
                    </div>

                    @if(config('settings.cronjob_executed_at'))
                        <div class="alert alert-success">
                            {{ __('Last cron job command executed at :date.', ['date' => Carbon\Carbon::createFromTimestamp(config('settings.cronjob_executed_at'))->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d') . ' H:i:s')]) }}
                        </div>
                    @else
                        <div class="alert alert-danger">
                            {{ __('Configure your server to run the cron job.') }}
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="i-cronjob">{{ __('Command') }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <code class="input-group-text">* * * * *</code>
                            </div>
                            <input type="text" dir="ltr" name="cronjob" id="i-cronjob" class="form-control" value="wget -q -O /dev/null {{ route('cronjob', ['key' => config('settings.cronjob_key')]) }}" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-cronjob">{{ __('Copy') }}</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('The cron job command must be set to run every minute.') }}
                        </small>
                    </div>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal" data-button-name="cronjob_key" data-action="{{ route('admin.settings', 'cronjob') }}" data-button-class="btn btn-danger position-relative" data-title="{{ __('Regenerate') }}" data-text="{{ __('If you regenerate the cron job key, you will need to update the cron job task with the new command.') }}" data-sub-text="{{ __('Are you sure you want to regenerate the :name key?', ['name' => mb_strtolower(__('Cron job'))]) }}">{{ __('Regenerate') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
