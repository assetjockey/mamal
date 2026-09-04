@if(config('settings.auth_google') || config('settings.auth_microsoft') || config('settings.auth_apple'))
    <div class="row mt-4">
        <div class="col d-flex align-items-center">
            <hr class="my-0 w-full">
        </div>

        <div class="col-auto d-flex align-items-center">
            <div class="text-muted">{{ mb_strtolower(__('Or')) }}</div>
        </div>

        <div class="col d-flex align-items-center">
            <hr class="my-0 w-full">
        </div>
    </div>

    <div class="row mx-n2 mt-2">
        @if(config('settings.auth_google'))
            <div class="col-12 p-2">
                <a href="{{ Socialite::with('google')->stateless()->redirect()->getTargetUrl() }}" class="btn btn-inverse d-flex align-items-center justify-content-center" rel="nofollow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-2" viewBox="0 0 21.56 22"><path d="m21.56,11.25c0-.78-.07-1.53-.2-2.25h-10.36v4.26h5.92c-.26,1.37-1.04,2.53-2.21,3.31v2.77h3.57c2.08-1.92,3.28-4.74,3.28-8.09Z" fill="#4285f4" stroke-width="0"/><path d="m11,22c2.97,0,5.46-.98,7.28-2.66l-3.57-2.77c-.98.66-2.23,1.06-3.71,1.06-2.86,0-5.29-1.93-6.16-4.53H1.18v2.84c1.81,3.59,5.52,6.06,9.82,6.06Z" fill="#34a853" stroke-width="0"/><path d="m4.84,13.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09v-2.84H1.18c-.75,1.48-1.18,3.15-1.18,4.93s.43,3.45,1.18,4.93l2.85-2.22s.81-.62.81-.62Z" fill="#fbbc05" stroke-width="0"/><path d="m11,4.38c1.62,0,3.06.56,4.21,1.64l3.15-3.15c-1.91-1.78-4.39-2.87-7.36-2.87C6.7,0,2.99,2.47,1.18,6.07l3.66,2.84c.87-2.6,3.3-4.53,6.16-4.53Z" fill="#ea4335" stroke-width="0"/></svg>

                    {{ __('Continue with :name', ['name' => 'Google']) }}
                </a>
            </div>
        @endif

        @if(config('settings.auth_microsoft'))
            <div class="col-12 p-2">
                <a href="{{ Socialite::with('azure')->stateless()->redirect()->getTargetUrl() }}" class="btn btn-inverse d-flex align-items-center justify-content-center" rel="nofollow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 me-2" viewBox="0 0 19 19"><rect width="9" height="9" fill="#f25022" stroke-width="0"/><rect y="10" width="9" height="9" fill="#00a4ef" stroke-width="0"/><rect x="10" width="9" height="9" fill="#7fba00" stroke-width="0"/><rect x="10" y="10" width="9" height="9" fill="#ffb900" stroke-width="0"/></svg>

                    {{ __('Continue with :name', ['name' => 'Microsoft']) }}
                </a>
            </div>
        @endif

        @if(config('settings.auth_apple'))
            <div class="col-12 p-2">
                <a href="{{ Socialite::with('apple')->stateless()->redirect()->getTargetUrl() }}" class="btn btn-inverse d-flex align-items-center justify-content-center" rel="nofollow">
                    @include('icons.apple', ['class' => 'fill-current w-4 h-4 me-2'])
                    {{ __('Continue with :name', ['name' => 'Apple']) }}
                </a>
            </div>
        @endif
    </div>
@endif
