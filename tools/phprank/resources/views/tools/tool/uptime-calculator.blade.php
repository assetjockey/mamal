@section('site_title', formatTitle([__('Uptime calculator'), __('Tool'), config('settings.title')]))

@section('head_content')
    <meta name="description" content="{{ __($tool->description) }}">
@endsection

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['url' => route('tools'), 'title' => __('Tools')],
    ['title' => __('Tool')],
]])

<div class="d-flex">
    <h1 class="h2 mb-3 text-break">{{ __('Uptime calculator') }}</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">{{ __('Uptime calculator') }}</div>
            </div>
        </div>
    </div>
    <div class="card-body position-relative">
        @include('shared.message')

        <form action="{{ route('tools.uptime_calculator') }}" method="post" enctype="multipart/form-data" @cannot('tools', ['App\Models\User']) class="position-relative opacity-20 min-height-80" @endcannot>
            @cannot('tools', ['App\Models\User'])
                <div class="position-absolute top-0 right-0 bottom-0 left-0 z-1 bg-fade-0"></div>
            @endcannot

            @csrf

            <div class="form-group">
                <label for="i-percentage">{{ __('Percentage') }}</label>
                <div class="input-group">
                    <input type="number" name="percentage" id="i-percentage" class="form-control{{ $errors->has('percentage') ? ' is-invalid' : '' }}" step="any" value="{{ $percentage ?? (old('percentage') ?? '99.99') }}">
                    <div class="input-group-append">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                @if ($errors->has('percentage'))
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $errors->first('percentage') }}</strong>
                    </span>
                @endif
            </div>

            <div class="row mx-n2">
                <div class="col px-2">
                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Calculate') }}</button>
                </div>
                <div class="col-auto px-2">
                    <a href="{{ route('tools.uptime_calculator') }}" class="btn btn-outline-secondary {{ (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto') }}">{{ __('Reset') }}</a>
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
            <div class="list-group list-group-flush my-n3">
                <div class="list-group-item px-0 text-muted">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    {{ __('Period') }}
                                </div>

                                <div class="col-12 col-md-4">
                                    {{ __('Uptime') }}
                                </div>

                                <div class="col-12 col-md-4">
                                    {{ __('Downtime') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach([__('Day') => 86400, __('Week') => 86400 * 7, __('Month') => 86400 * 30, __('Year') => 86400 * 365] as $key => $value)
                    <div class="list-group-item px-0">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                {{ $key }}
                            </div>
                            <div class="col-12 col-md-4 text-success">
                                {{ (clone $now)->diffForHumans((clone $now)->subSeconds(round(($value / 100) * $percentage)), ['syntax' => true, 'parts' => 3, 'join' => true, 'short' => true]) }}
                            </div>
                            <div class="col-12 col-md-4 text-danger">
                                {{ (clone $now)->diffForHumans((clone $now)->subSeconds(round(($value / 100) * (100-$percentage))), ['syntax' => true, 'parts' => 3, 'join' => true, 'short' => true]) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
