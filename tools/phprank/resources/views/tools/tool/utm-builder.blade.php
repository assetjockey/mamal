@section('site_title', formatTitle([__('UTM builder'), __('Tool'), config('settings.title')]))

@section('head_content')
    <meta name="description" content="{{ __($tool->description) }}">
@endsection

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['url' => route('tools'), 'title' => __('Tools')],
    ['title' => __('Tool')],
]])

<div class="d-flex">
    <h1 class="h2 mb-3 text-break">{{ __('UTM builder') }}</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">{{ __('UTM builder') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body position-relative">
        @include('shared.message')

        <form action="{{ route('tools.utm_builder') }}" method="post" enctype="multipart/form-data" @cannot('tools', ['App\Models\User']) class="position-relative opacity-20 min-height-80" @endcannot>
            @cannot('tools', ['App\Models\User'])
                <div class="position-absolute top-0 right-0 bottom-0 left-0 z-1 bg-fade-0"></div>
            @endcannot

            @csrf

            <div class="form-group">
                <label for="i-url">{{ __('URL') }}</label>
                <input type="text" dir="ltr" name="url" id="i-url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" value="{{ old('url') ?? ($url ?? null) }}">
                @if ($errors->has('url'))
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $errors->first('url') }}</strong>
                    </span>
                @endif
            </div>

            <div class="form-group">
                <label for="i-utm-source">{{ __('Source') }}</label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><code class="code">utm_source</code></span>
                    </div>
                    <input type="text" name="utm_source" id="i-utm-source" class="form-control{{ $errors->has('utm_source') ? ' is-invalid' : '' }}" value="{{ old('utm_source') ?? ($utmSource ?? null) }}">
                    @if ($errors->has('utm_source'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('utm_source') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="i-utm-medium">{{ __('Medium') }}</label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><code class="code">utm_medium</code></span>
                    </div>
                    <input type="text" name="utm_medium" id="i-utm-medium" class="form-control{{ $errors->has('utm_medium') ? ' is-invalid' : '' }}" value="{{ old('utm_medium') ?? ($utmMedium ?? null) }}">
                    @if ($errors->has('utm_medium'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('utm_medium') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="i-utm-campaign">{{ __('Campaign') }}</label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><code class="code">utm_campaign</code></span>
                    </div>
                    <input type="text" name="utm_campaign" id="i-utm-campaign" class="form-control{{ $errors->has('utm_campaign') ? ' is-invalid' : '' }}" value="{{ old('utm_campaign') ?? ($utmCampaign ?? null) }}">
                    @if ($errors->has('utm_campaign'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('utm_campaign') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="i-utm-term">{{ __('Term') }}</label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><code class="code">utm_term</code></span>
                    </div>
                    <input type="text" name="utm_term" id="i-utm-term" class="form-control{{ $errors->has('utm_term') ? ' is-invalid' : '' }}" value="{{ old('utm_term') ?? ($utmTerm ?? null) }}">
                    @if ($errors->has('utm_term'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('utm_term') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label for="i-utm-content">{{ __('Content') }}</label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><code class="code">utm_content</code></span>
                    </div>
                    <input type="text" name="utm_content" id="i-utm-content" class="form-control{{ $errors->has('utm_content') ? ' is-invalid' : '' }}" value="{{ old('utm_content') ?? ($utmContent ?? null) }}">
                    @if ($errors->has('utm_content'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('utm_content') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="row mx-n2">
                <div class="col px-2">
                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Generate') }}</button>
                </div>
                <div class="col-auto px-2">
                    <a href="{{ route('tools.utm_builder') }}" class="btn btn-outline-secondary {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>

        @cannot('tools', ['App\Models\User'])
            <div class="position-absolute top-0 right-0 bottom-0 left-0">
                @if(paymentProcessors())
                    @include('shared.features.locked')
                @else
                    @include('shared.features.unavailable')
                @endif
            </div>
        @endcannot
    </div>
</div>

@if(isset($result))
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-header align-items-center">
            <div class="row">
                <div class="col">
                    <div class="font-weight-medium py-1">{{ __('Results') }}</div>
                </div>
            </div>
        </div>
        <div class="card-body mb-n3">
            <div class="form-group">
                <label for="i-result-content">{{ __('Content') }}</label>

                <div class="position-relative">
                    <textarea name="result-content" id="i-result-content" class="form-control" onclick="this.select();" readonly>{{ $result }}</textarea>

                    <div class="position-absolute top-0 right-0">
                        <div class="btn btn-sm btn-primary m-2" data-tooltip-copy="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-result-content">{{ __('Copy') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
