<div class="card border-0 shadow-sm mt-4">
    <div class="card-header align-items-center">
        <div class="fw-medium py-1">{{ __('Authentication') }}</div>
    </div>

    <div class="card-body">
        {{ __('The API key should be sent as a Bearer token in the Authorization header of the request.') }} <a href="{{ route('account.api') }}">{{ __('Get your API key') }}</a>.
    </div>
</div>
