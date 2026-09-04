@section('site_title', formatTitle([__('Edit'), __('Report'), config('settings.title')]))

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
                <div class="font-weight-medium py-1">{{ __('Report') }}</div>
            </div>
            <div class="col-auto d-flex">
                <div class="form-row">
                    <div class="col">
                        @include('reports.partials.menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        @include('shared.message')

        <form action="{{ request()->is('admin/*') ? route('admin.reports.edit', $report->id) : route('reports.edit', $report->id) }}" method="post" enctype="multipart/form-data">
            @csrf

            @if(request()->is('admin/*'))
                <input type="hidden" name="user_id" value="{{ isset($report->user) ? $report->user->id : '0' }}">
            @endif

            <div class="form-group">
                <label for="i-url">{{ __('URL') }}</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><img src="{{ favicon($report->url) }}" rel="noreferrer" class="width-4 height-4"></span>
                    </div>
                    <input type="text" dir="ltr" name="url" class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}" id="i-url" value="{{ $report->url }}" disabled>
                    @if ($errors->has('url'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('url') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('Privacy') }}</label>
                <div class="form-group mb-0">
                    <div class="row mx-n2">
                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if($report->privacy == 1 && old('privacy') == null || old('privacy') == 1) checked @endif>
                                <label for="i-privacy1" class="custom-control-label w-100 d-flex flex-column">
                                    <span>{{ __('Private') }}</span>
                                    <span class="small text-muted">{{ __('Status page accessible only by you.') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if($report->privacy == 0 && old('privacy') == null || old('privacy') == 0 && old('privacy') != null) checked @endif>
                                <label for="i-privacy0" class="custom-control-label w-100 d-flex flex-column">
                                    <span>{{ __('Public') }}</span>
                                    <span class="small text-muted">{{ __('Status page accessible by anyone.') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 px-2">
                            <div class="custom-control custom-radio">
                                <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if($report->privacy == 2 && old('privacy') == null || old('privacy') == 2) checked @endif>
                                <label for="i-privacy2" class="custom-control-label w-100 d-flex flex-column">
                                    <span>{{ __('Password') }}</span>
                                    <span class="small text-muted">{{ __('Status page accessible by password.') }}</span>
                                </label>

                                <div id="input-password" class="{{ (((old('privacy') == 2) || (old('privacy') == null && $report->privacy == 2)) ? '' : 'd-none')}}">
                                    <div class="input-group mt-2">
                                        <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') ?? $report->password }}" autocomplete="new-password">
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

            <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </form>
    </div>
</div>

@if(request()->is('admin/*'))
    <div class="row m-n2 pt-3">
        <div class="col-12 col-md-6 col-lg-4 p-2">
            <a href="{{ route('admin.users.edit', ['id' => $report->user->id]) }}" class="text-decoration-none text-dark">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center text-truncate">
                        <img src="{{ $report->user->avatar_url }}" alt="{{ $report->user->name }}" class="width-8 height-8 rounded-circle">

                        <span class="font-weight-medium text-decoration-none text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-3 mr-2' : 'mr-2 ml-3') }}">{{ $report->user->name }}</span>

                        @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                    </div>
                </div>
            </a>
        </div>
    </div>
@endif
