@section('site_title', formatTitle([__('WHOIS lookup'), __('Tool'), config('settings.title')]))

@section('head_content')
    <meta name="description" content="{{ __($tool->description) }}">
@endsection

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['url' => route('tools'), 'title' => __('Tools')],
    ['title' => __('Tool')],
]])

<div class="d-flex">
    <h1 class="h2 mb-3 text-break">{{ __('WHOIS lookup') }}</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">{{ __('WHOIS lookup') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body position-relative">
        @include('shared.message')

        <form action="{{ route('tools.whois_lookup') }}" method="post" enctype="multipart/form-data" id="whois-lookup-form" @cannot('tools', ['App\Models\User']) class="position-relative opacity-20 min-height-80" @endcannot>
            @cannot('tools', ['App\Models\User'])
                <div class="position-absolute top-0 right-0 bottom-0 left-0 z-1 bg-fade-0"></div>
            @endcannot

            @csrf

            <div class="form-group">
                <label for="i-domain">{{ __('Domain') }}</label>
                <input type="text" dir="ltr" name="domain" id="i-domain" class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}" value="{{ $domain ?? (old('domain') ?? '') }}">

                @if ($errors->has('domain'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('domain') }}</strong>
                    </span>
                @endif
            </div>

            <div class="row mx-n2">
                <div class="col px-2">
                    @if(config('settings.captcha_driver'))
                        <x-captcha-js lang="{{ __('lang_code') }}"></x-captcha-js>

                        @include('shared.captcha', ['id' => 'whois-lookup-form'])

                        <x-captcha-button data-callback="{{ (config('settings.captcha_driver') == 'turnstile' ? '' : 'captchaFormSubmit') }}" form-id="whois-lookup-form" class="btn {{ $errors->has(formatCaptchaFieldName()) ? 'btn-danger' : 'btn-primary' }}" data-sitekey="{{ config('settings.captcha_site_key') }}" data-theme="{{ (config('settings.dark_mode') == 1 ? 'dark' : 'light') }}">{{ __('Search') }}</x-captcha-button>

                        @if ($errors->has(formatCaptchaFieldName()))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ __($errors->first(formatCaptchaFieldName())) }}</strong>
                            </span>
                        @endif
                    @else
                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Search') }}</button>
                    @endif
                </div>
                <div class="col-auto px-2">
                    <a href="{{ route('tools.whois_lookup') }}" class="btn btn-outline-secondary {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">{{ __('Reset') }}</a>
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

        <div class="card-body">
            @if(empty($result))
                {{ __('No results found.') }}
            @else
                <div class="list-group list-group-flush my-n3">
                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Domain') }}</div>
                            <div class="col-12 col-lg-8 text-break d-flex align-items-center">
                                <img src="{{ favicon($result->domainName) }}" rel="noreferrer" class="width-4 height-4 {{ (__('lang_dir') == 'rtl' ? 'ml-3' : 'mr-3') }}">
                                <span dir="ltr">{{ $result->domainName }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Registrar') }}</div>
                            <div class="col-12 col-lg-8 text-break">{{ $result->registrar }}</div>
                        </div>
                    </div>

                    @if($result->owner)
                        <div class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-4 text-break text-muted">{{ __('Registrant') }}</div>
                                <div class="col-12 col-lg-8 text-break">{{ $result->owner }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Created date') }}</div>
                            <div class="col-12 col-lg-8 text-break">
                                {{ __(':date at :time (UTC :offset)', ['date' => \Carbon\Carbon::createFromTimestamp($result->creationDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => \Carbon\Carbon::createFromTimestamp($result->creationDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('H:i:s')), 'offset' => \Carbon\CarbonTimeZone::create((Auth::user()->timezone ?? config('settings.timezone')))->toOffsetName()]) }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Updated date') }}</div>
                            <div class="col-12 col-lg-8 text-break">
                                {{ __(':date at :time (UTC :offset)', ['date' => \Carbon\Carbon::createFromTimestamp($result->updatedDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => \Carbon\Carbon::createFromTimestamp($result->updatedDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('H:i:s')), 'offset' => \Carbon\CarbonTimeZone::create((Auth::user()->timezone ?? config('settings.timezone')))->toOffsetName()]) }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Expiration date') }}</div>
                            <div class="col-12 col-lg-8 text-break">
                                {{ __(':date at :time (UTC :offset)', ['date' => \Carbon\Carbon::createFromTimestamp($result->expirationDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')), 'time' => \Carbon\Carbon::createFromTimestamp($result->expirationDate)->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('H:i:s')), 'offset' => \Carbon\CarbonTimeZone::create((Auth::user()->timezone ?? config('settings.timezone')))->toOffsetName()]) }}
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('Name servers') }}</div>
                            <div class="col-12 col-lg-8 text-break">
                                @foreach($result->nameServers as $nameServer)
                                    <div class="text-break {{ !$loop->first ? 'mt-1' : '' }}">
                                        {{ $nameServer }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item px-0">
                        <div class="row align-items-center">
                            <div class="col-12 col-lg-4 text-break text-muted">{{ __('States') }}</div>
                            <div class="col-12 col-lg-8 text-break">
                                @foreach($result->states as $state)
                                    <div class="text-break {{ !$loop->first ? 'mt-1' : '' }}">
                                        {{ $state }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($result->whoisServer)
                        <div class="list-group-item px-0">
                            <div class="row align-items-center">
                                <div class="col-12 col-lg-4 text-break text-muted">{{ __('WHOIS server') }}</div>
                                <div class="col-12 col-lg-8 text-break">{{ $result->whoisServer }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
