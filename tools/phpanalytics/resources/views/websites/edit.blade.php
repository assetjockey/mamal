@section('site_title', formatTitle([__('Edit'), __('Website'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
    ['title' => __('Edit')],
]])

<div class="d-flex">
    <h1 class="h2 mb-3 text-break">{{ __('Edit') }}</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header align-items-center">
        <div class="row">
            <div class="col">
                <div class="font-weight-medium py-1">{{ __('Website') }}</div>
            </div>
            <div class="col-auto">
                <div class="form-row">
                    <div class="col">
                        @include('websites.partials.menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @include('shared.message')

        <form action="{{ request()->is('admin/*') ? route('admin.websites.edit', $website->id) : route('websites.edit', $website->id) }}" method="post" enctype="multipart/form-data" id="form-website">
            @csrf

            @if(request()->is('admin/*'))
                <input type="hidden" name="user_id" value="{{ $website->user->id }}">
            @endif

            <div class="form-group">
                <label for="i-domain">{{ __('Domain') }}</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><img src="{{ favicon($website->domain) }}" rel="noreferrer" class="width-4 height-4"></span>
                    </div>
                    <input type="text" dir="ltr" name="domain" class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}" id="i-domain" value="{{ $website->domain }}" placeholder="https://example.com" disabled>
                    @if ($errors->has('domain'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('domain') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="form-group">
                <label>{{ __('Privacy') }}</label>
                <div class="form-group mb-0">
                    <div class="row mx-n2">
                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if($website->privacy == 1 && old('privacy') == null || old('privacy') == 1) checked @endif>
                                <label class="custom-control-label w-100 d-flex flex-column" for="i-privacy1">
                                    <span>{{ __('Private') }}</span>
                                    <span class="small text-muted">{{ __('Stats accessible only by you.') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if($website->privacy == 0 && old('privacy') == null || old('privacy') == 0 && old('privacy') != null) checked @endif>
                                <label class="custom-control-label w-100 d-flex flex-column" for="i-privacy0">
                                    <span>{{ __('Public') }}</span>
                                    <span class="small text-muted">{{ __('Stats accessible by anyone.') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if($website->privacy == 2 && old('privacy') == null || old('privacy') == 2) checked @endif>
                                <label class="custom-control-label w-100 d-flex flex-column" for="i-privacy2">
                                    <span>{{ __('Password') }}</span>
                                    <span class="small text-muted">{{ __('Stats accessible by password.') }}</span>
                                </label>

                                <div id="input-password" class="{{ (((old('privacy') == 2) || (old('privacy') == null && $website->privacy == 2)) ? '' : 'd-none')}}">
                                    <div class="input-group mt-2">
                                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') ?? $website->password }}" autocomplete="new-password">
                                        <div class="input-group-append">
                                            <div class="input-group-text cursor-pointer" data-tooltip="true" data-title="{{ __('Show password') }}" data-password="i-password" data-password-show="{{ __('Show password') }}" data-password-hide="{{ __('Hide password') }}">@include('icons.visibility_off', ['class' => 'width-4 height-4 fill-current text-muted'])@include('icons.visibility', ['class' => 'width-4 height-4 fill-current text-muted d-none'])</div>
                                        </div>
                                    </div>
                                    @if ($errors->has('password'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($errors->has('privacy'))
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $errors->first('privacy') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="form-group">
                <div class="row mx-n2">
                    <div class="col-12 col-lg-4 px-2">
                        <div class="form-group mb-0">
                            <div class="row">
                                <div class="col"><label>{{ __('Notifications') }}</label></div>
                                <div class="col-auto">
                                    @cannot('emailReports', ['App\Models\User'])
                                        @if(paymentProcessors())
                                            <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                        @endif
                                    @endcannot
                                </div>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="hidden" name="email" value="0">
                                <input type="checkbox" name="email" value="1" class="custom-control-input {{ $errors->has('email') ? ' is-invalid' : '' }}" id="i-email" @if($website->email && old('email') == null || old('email')) checked @endif @cannot('emailReports', ['App\Models\User']) disabled @endcannot>
                                <label class="custom-control-label user-select-none" for="i-email">
                                    <div>{{ __('Email') }}</div>
                                    <div class="small text-muted">{{ __('Periodic email reports.') }}</div>
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row mx-n2">
                <div class="col-12 col-md-6 px-2">
                    <div class="form-group">
                        <label for="i-exclude-ips" class="d-flex align-items-center">{{ __('Exclude IPs') }} <span data-tooltip="true" title="{{ __('To block entire IP classes, use the CIDR notation.') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.info', ['class' => 'fill-current text-muted width-4 height-4'])</span></label>
                        <textarea name="exclude_ips" id="i-exclude-ips" class="form-control{{ $errors->has('exclude_ips') ? ' is-invalid' : '' }}">{{ old('exclude_ips') ?? $website->exclude_ips }}</textarea>
                        @if ($errors->has('exclude_ips'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('exclude_ips') }}</strong>
                            </span>
                        @endif
                        <small class="form-text text-muted">{{ __('One per line.') }}</small>
                    </div>
                </div>

                <div class="col-12 col-md-6 px-2">
                    <div class="form-group">
                        <label for="i-exclude-params">{{ __('Exclude URL query parameters') }}</label>
                        <textarea name="exclude_params" id="i-exclude-params" class="form-control{{ $errors->has('exclude_params') ? ' is-invalid' : '' }}">{{ old('exclude_params') ?? $website->exclude_params }}</textarea>
                        @if ($errors->has('exclude_params'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('exclude_params') }}</strong>
                            </span>
                        @endif
                        <small class="form-text text-muted">{{ __('One per line.') }}</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="hidden" name="exclude_bots" value="0">
                    <input type="checkbox" name="exclude_bots" value="1" class="custom-control-input {{ $errors->has('exclude_bots') ? ' is-invalid' : '' }}" id="i-exclude-bots" @if($website->exclude_bots && old('exclude_bots') == null || old('exclude_bots')) checked @endif>
                    <label class="custom-control-label user-select-none" for="i-exclude-bots">
                        <div>{{ __('Exclude bots') }}</div>
                        <div class="small text-muted">{{ __('Exclude common bots from being tracked.') }}</div>
                        @if ($errors->has('exclude_bots'))
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $errors->first('exclude_bots') }}</strong>
                            </span>
                        @endif
                    </label>
                </div>
            </div>

            <hr>

            <div class="form-group">
                @include('shared.tracking-code')
            </div>

            <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </form>
    </div>
</div>

@if(request()->is('admin/*'))
    <div class="row m-n2 pt-3">
        <div class="col-12 col-md-6 col-lg-4 p-2">
            <a href="{{ route('admin.users.edit', ['id' => $website->user->id]) }}" class="text-decoration-none text-dark">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center text-truncate">
                        <img src="{{ $website->user->avatar_url }}" alt="{{ $website->user->name }}" class="width-8 height-8 rounded-circle">

                        <span class="font-weight-medium text-decoration-none text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-3 mr-2' : 'mr-2 ml-3') }}">{{ $website->user->name }}</span>

                        @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif
