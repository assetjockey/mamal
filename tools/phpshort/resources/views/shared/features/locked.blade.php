<div class="h-full d-flex flex-column justify-content-center align-items-center">
    <div class="position-relative w-32 h-32 d-flex align-items-center justify-content-center">
        <div class="position-absolute top-0 end-0 bottom-0 start-0 bg-primary opacity-10 rounded-circle"></div>

        @include('icons.lock', ['class' => 'text-primary fill-current w-16 h-16'])
    </div>

    <div>
        <h1 class="fs-xl fw-medium mb-2 mt-6 text-center">{{ __('Feature locked') }}</h1>
        <p class="text-center text-muted mb-0">{{ __('Upgrade your account to unlock this feature.') }}</p>

        <div class="text-center mt-12">
            <a href="{{ route('pricing') }}" class="btn btn-primary">{{ __('Upgrade') }}</a>
        </div>
    </div>
</div>