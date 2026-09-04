@section('site_title', formatTitle([__('Advanced'), __('Settings'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('admin.dashboard'), 'title' => __('Admin')],
    ['title' => __('Settings')],
]])

<h1 class="h2 mb-3 d-inline-block">{{ __('Advanced') }}</h1>

<div class="card border-0 shadow-sm">
    <div class="card-header"><div class="font-weight-medium py-1">{{ __('Advanced') }}</div></div>
    <div class="card-body">

        <ul class="nav nav-pills d-flex flex-fill flex-column flex-md-row mb-3" id="pills-tab" role="tablist">
            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link active" id="pills-general-tab" data-toggle="pill" href="#pills-general" role="tab" aria-controls="pills-general" aria-selected="true">{{ __('General') }}</a>
            </li>
            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link" id="pills-analytics-tab" data-toggle="pill" href="#pills-analytics" role="tab" aria-controls="pills-analytics" aria-selected="false">{{ __('Analytics') }}</a>
            </li>
        </ul>

        @include('shared.message')

        <form action="{{ route('admin.settings', 'advanced') }}" method="post" enctype="multipart/form-data">

            @csrf

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-general" role="tabpanel" aria-labelledby="pills-general-tab">
                    <div class="form-group">
                        <label for="i-demo-url">{{ __('Demo URL') }}</label>
                        <input type="text" dir="ltr" name="demo_url" id="i-demo-url" class="form-control{{ $errors->has('demo_url') ? ' is-invalid' : '' }}" value="{{ old('settings.demo_url') ?? config('settings.demo_url') }}">
                        @if ($errors->has('demo_url'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('demo_url') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-bad-words">{{ __('Bad words') }}</label>
                        <textarea name="bad_words" id="i-bad-words" class="form-control{{ $errors->has('bad_words') ? ' is-invalid' : '' }}" rows="3">{{ config('settings.bad_words') }}</textarea>
                        @if ($errors->has('bad_words'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('bad_words') }}</strong>
                            </span>
                        @endif
                        <small class="form-text text-muted">{{ __('One per line.') }}</small>
                    </div>

                    <div class="form-group">
                        <label for="i-email-reports-period">{{ __('Email reports') }}</label>
                        <select name="email_reports_period" id="i-email-reports-period" class="custom-select{{ $errors->has('email_reports_period') ? ' is-invalid' : '' }}">
                            @foreach(['monthly' => __('Monthly'), 'weekly' => __('Weekly')] as $key => $value)
                                <option value="{{ $key }}" @if ((old('email_reports_period') !== null && old('email_reports_period') == $key) || (config('settings.email_reports_period') == $key && old('email_reports_period') == null)) selected @endif>{{ $value }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('email_reports_period'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email_reports_period') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="pills-analytics" role="tabpanel" aria-labelledby="pills-analytics-tab">
                    <div class="form-group">
                        <label for="i-cdn-url" class="d-flex align-items-center">{{ __('CDN URL') }} <span data-tooltip="true" title="{{ __('The CDN URL where the :name file is hosted.', ['name' => 'script.js']) }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.info', ['class' => 'fill-current text-muted width-4 height-4'])</span></label>
                        <input type="text" dir="ltr" name="cdn_url" id="i-cdn-url" class="form-control{{ $errors->has('cdn_url') ? ' is-invalid' : '' }}" value="{{ old('settings.cdn_url') ?? config('settings.cdn_url') }}">
                        @if ($errors->has('cdn_url'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('cdn_url') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="i-do-not-track" class="d-inline-flex align-items-center"><span class="{{ (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2') }}">{{ __('Do Not Track') }}</span><span class="badge badge-secondary">{{ __('Default') }}</span></label>
                            <select name="do_not_track" id="i-do-not-track" class="custom-select{{ $errors->has('do_not_track') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('do_not_track') !== null && old('do_not_track') == $key) || (config('settings.do_not_track') == $key && old('do_not_track') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('do_not_track'))
                                <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('do_not_track') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </form>

    </div>
</div>
