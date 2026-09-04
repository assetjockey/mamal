@section('site_title', formatTitle([__('Preferences'), config('settings.title')]))

@include('shared.breadcrumbs', ['breadcrumbs' => [
    ['url' => route('dashboard'), 'title' => __('Home')],
    ['url' => route('account'), 'title' => __('Account')],
    ['title' => __('Preferences')]
]])

<div class="d-flex"><h1 class="h2 mb-3 text-break">{{ __('Preferences') }}</h1></div>

<div class="card border-0 shadow-sm">
    <div class="card-header">
        <div class="font-weight-medium py-1">
            {{ __('Preferences') }}
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-pills d-flex flex-fill flex-column flex-md-row mb-3" id="pills-tab" role="tablist">
            <li class="nav-item flex-grow-1 text-center">
                <a class="nav-link active" id="pills-notifications-tab" data-toggle="pill" href="#pills-notifications" role="tab" aria-controls="pills-notifications" aria-selected="true">{{ __('Notifications') }}</a>
            </li>
        </ul>

        @include('shared.message')

        <form action="{{ route('account.preferences') }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-notifications" role="tabpanel" aria-labelledby="pills-notifications-tab">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="email_account_limit" value="1" class="custom-control-input {{ $errors->has('email_account_limit') ? ' is-invalid' : '' }}" id="i-email-account-limit" @if(Auth::user()->email_account_limit && old('email_account_limit') == null || old('email_account_limit')) checked @endif>
                            <label class="custom-control-label user-select-none" for="i-email-account-limit">
                                <div>{{ __('Account limit') }}</div>
                                <div class="small text-muted">{{ __('Email when account limit is exceeded.') }}</div>
                                @if ($errors->has('email_account_limit'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('email_account_limit') }}</strong>
                                    </span>
                                @endif
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </form>
    </div>
</div>
