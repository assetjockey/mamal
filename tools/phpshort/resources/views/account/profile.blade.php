@extends('layouts.app')

@if(request()->is('admin/*'))
    @section('site_title', formatTitle([__('Edit'), __('User'), config('settings.title')]))
@else
    @section('site_title', formatTitle([__('Profile'), config('settings.title')]))
@endif

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                ['url' => request()->is('admin/*') ? route('admin.users') : route('account'), 'title' => request()->is('admin/*') ? __('Users') : __('Account')],
                ['title' => request()->is('admin/*') ? __('Edit') : __('Profile')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ (request()->is('admin/*') ? __('Edit') : __('Profile')) }}</h1>
                </div>
                @if(request()->is('admin/*'))
                    <div class="col-auto px-2">
                        <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                        @include('admin.users.partials.context-menu')
                    </div>
                @endif
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">
                        @if(request()->is('admin/*'))
                            {{ __('User') }}
                        @else
                            {{ __('Profile') }}
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if($user->trashed())
                        <div class="alert alert-danger">
                            {{ __('This :resource is disabled.', ['resource' => mb_strtolower(__('User'))]) }}
                        </div>
                    @endif

                    @if(!$user->email_verified_at)
                        <div class="alert alert-secondary d-flex" role="alert">
                            {{ __('This user has not confirmed his email address.') }}
                        </div>
                    @endif

                    @if($user->getPendingEmail() && request()->is('admin/*') == false)
                        <div class="alert alert-info d-flex" role="alert">
                            <div class="row">
                                <div class="col">
                                    <form class="d-inline" method="POST" action="{{ route('account.profile.resend') }}" id="resend-form">
                                        @csrf
                                        {{ __(':address email address is pending confirmation.', ['address' => $user->getPendingEmail()]) }} {{ __('Didn\'t receive the email?') }} <a href="#" class="alert-link fw-medium" onclick="event.preventDefault(); document.getElementById('resend-form').submit();">{{ __('Resend') }}</a>
                                    </form>
                                </div>
                                <div class="col-auto">
                                    <form class="d-inline" method="POST" action="{{ route('account.profile.cancel') }}" id="cancel-form">
                                        @csrf
                                        <a href="#" class="alert-link fw-medium" onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">{{ __('Cancel') }}</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ (request()->is('admin/*') ? route('admin.users.edit', $user->id) : route('account.profile.update')) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="i-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') ?? $user->name }}">
                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-email">{{ __('Email') }}</label>
                            <input type="text" name="email" id="i-email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') ?? $user->email }}">
                            @if ($errors->has('email'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="row mx-n1 form-group">
                            <div class="col-12 px-1"><label for="i-avatar">{{ __('Avatar') }}</label></div>
                            <div class="col px-1">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text py-1 px-2"><img src="{{ $user->avatarUrl }}" class="max-h-6.5" alt=""></span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" name="avatar" id="i-avatar" class="custom-file-input{{ $errors->has('avatar') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.user_avatar_extensions') }}">
                                        <label class="custom-file-label" for="i-avatar" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                    </div>
                                </div>
                            </div>
                            @if ($user->avatar)
                                <div class="col-auto px-1">
                                    <div class="btn-group-toggle input-group-prepend" data-toggle="buttons">
                                        <label class="btn btn-outline-danger">
                                            <input type="checkbox" name="remove_avatar" value="1" data-disable-input="i-avatar"> {{ __('Remove') }}
                                        </label>
                                    </div>
                                </div>
                            @endif

                            @if ($errors->has('avatar'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('avatar') }}</strong>
                                </span>
                            @endif
                            @if ($errors->has('remove_avatar'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('remove_avatar') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-timezone">{{ __('Timezone') }}</label>
                            <select name="timezone" id="i-timezone" class="custom-select{{ $errors->has('timezone') ? ' is-invalid' : '' }}">
                                @foreach(timezone_identifiers_list() as $value)
                                    <option value="{{ $value }}" @if ($value == $user->timezone) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('timezone'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('timezone') }}</strong>
                                </span>
                            @endif
                        </div>

                        @if(request()->is('admin/*'))
                            <div class="row mx-n2 mb-4">
                                <div class="col-auto fw-bold px-2">
                                    <span class="badge badge-secondary text-uppercase">
                                        {{ __('Status') }}
                                    </span>
                                </div>
                                <div class="col d-flex align-items-center px-2">
                                    <hr class="my-0 w-full">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="i-mark-email-as-verified">{{ __('Verified') }}</label>
                                <select name="mark_email_as_verified" id="i-mark-email-as-verified" class="custom-select{{ $errors->has('mark_email_as_verified') ? ' is-invalid' : '' }}">
                                    <option value="0" @if (empty($user->email_verified_at)) selected @endif>{{ __('No') }}</option>
                                    <option value="1" @if ($user->email_verified_at) selected @endif>{{ __('Yes') }}</option>
                                </select>
                                @if ($errors->has('mark_email_as_verified'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mark_email_as_verified') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="i-role">{{ __('Role') }}</label>
                                <select name="role" id="i-role" class="custom-select{{ $errors->has('role') ? ' is-invalid' : '' }}">
                                    @foreach([0 => __('User'), 1 => __('Admin')] as $key => $value)
                                        <option value="{{ $key }}" @if ($key == $user->role) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('role'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('role') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="row mx-n2 mb-4">
                                <div class="col-auto fw-bold px-2">
                                    <span class="badge badge-secondary text-uppercase">
                                        {{ __('Password') }}
                                    </span>
                                </div>
                                <div class="col d-flex align-items-center px-2">
                                    <hr class="my-0 w-full">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="i-password">{{ __('New password') }} <span class="text-muted">({{ mb_strtolower(__('Leave empty if you don\'t want to change it')) }})</span></label>
                                <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label for="i-password-confirmation">{{ __('Confirm new password') }}</label>
                                <input type="password" name="password_confirmation" id="i-password-confirmation" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}">
                                @if ($errors->has('password_confirmation'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="row mx-n2 mb-4">
                                <div class="col-auto fw-bold px-2">
                                    <span class="badge badge-secondary text-uppercase">
                                        {{ __('Two-factor authentication') }}
                                    </span>
                                </div>
                                <div class="col d-flex align-items-center px-2">
                                    <hr class="my-0 w-full">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="i-tfa">{{ __('Two-factor authentication') }}</label>
                                <select name="tfa" id="i-tfa" class="custom-select{{ $errors->has('tfa') ? ' is-invalid' : '' }}">
                                    @foreach([0 => __('Disabled'), 1 => __('Enabled')] as $key => $value)
                                        <option value="{{ $key }}" @if ((old('tfa') !== null && old('tfa') == $key) || ($user->tfa == $key && old('tfa') == null)) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('tfa'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('tfa') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="row mx-n2 mb-4">
                                <div class="col-auto fw-bold px-2">
                                    <span class="badge badge-secondary text-uppercase">
                                        {{ __('Plan') }}
                                    </span>
                                </div>
                                <div class="col d-flex align-items-center px-2">
                                    <hr class="my-0 w-full">
                                </div>
                            </div>

                            <div class="row mx-n2">
                                <div class="col-12 col-lg-4 px-2">
                                    <div class="form-group">
                                        <label for="i-plan-id">{{ __('Name') }}</label>
                                        <select id="i-plan-id" name="plan_id" class="custom-select{{ $errors->has('plan_id') ? ' is-invalid' : '' }}">
                                            @foreach($plans as $plan)
                                                <option value="{{ $plan->id }}" @if($user->plan_id == $plan->id) selected @endif>{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('plan_id'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('plan_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4 px-2">
                                    <div class="form-group">
                                        <label for="i-plan-ends-at" class="d-flex align-items-center"><span class="me-2">{{ __('Ends at') }}</span> @if ($user->plan_id != $user->active_plan->id) <span class="badge badge-danger">{{ __('Expired') }}</span> @endif</label>
                                        <input type="date" name="plan_ends_at" class="form-control{{ $errors->has('plan_ends_at') ? ' is-invalid' : '' }}" id="i-plan-ends-at" placeholder="Y-m-d" value="{{ old('plan_ends_at') ?? ($user->plan_ends_at ? $user->plan_ends_at->tz($user->timezone ?? config('app.timezone'))->format('Y-m-d') : '') }}">
                                        @if ($errors->has('plan_ends_at'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('plan_ends_at') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4 px-2">
                                    <div class="form-group">
                                        <label for="i-plan-payment-processor">{{ __('Processor') }}</label>
                                        <input type="text" class="form-control" id="i-plan-payment-processor" value="{{ config('payment.processors.' . $user->plan_payment_processor)['name'] ?? __('None') }}" readonly>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>

            @if(request()->is('admin/*'))
                <div class="row m-n2 pt-4">
                    <div class="col-12 col-md-6 col-lg-4 p-2">
                        <div class="card border-0 shadow-sm h-full">
                            <div class="card-body d-flex align-items-center text-truncate">
                                <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                    <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                    @include('icons.package', ['class' => 'fill-current w-4 h-4'])
                                </div>

                                <a href="{{ route('admin.plans.edit', ['id' => $user->plan_id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link mx-4">{{ __('Plan') }}</a>

                                @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                            </div>
                        </div>
                    </div>

                    @if($user->payments_count)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                        @include('icons.credit-card', ['class' => 'fill-current w-4 h-4'])
                                    </div>

                                    <a href="{{ route('admin.payments', ['user_id' => $user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Payments') }}</a>

                                    <span class="badge badge-primary me-4">{{ number_format($user->payments_count, 0, __('.'), __(',')) }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->links_count)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                        @include('icons.link', ['class' => 'fill-current w-4 h-4'])
                                    </div>

                                    <a href="{{ route('admin.links', ['user_id' => $user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Links') }}</a>

                                    <span class="badge badge-primary me-4">{{ number_format($user->links_count, 0, __('.'), __(',')) }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->spaces_count)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                        @include('icons.workspaces', ['class' => 'fill-current w-4 h-4'])
                                    </div>

                                    <a href="{{ route('admin.spaces', ['user_id' => $user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Spaces') }}</a>

                                    <span class="badge badge-primary me-4">{{ number_format($user->spaces_count, 0, __('.'), __(',')) }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->domains_count)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                        @include('icons.website', ['class' => 'fill-current w-4 h-4'])
                                    </div>

                                    <a href="{{ route('admin.domains', ['user_id' => $user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Domains') }}</a>

                                    <span class="badge badge-primary me-4">{{ number_format($user->domains_count, 0, __('.'), __(',')) }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($user->pixels_count)
                        <div class="col-12 col-md-6 col-lg-4 p-2">
                            <div class="card border-0 shadow-sm h-full">
                                <div class="card-body d-flex align-items-center text-truncate">
                                    <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                        <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                        @include('icons.filter-center-focus', ['class' => 'fill-current w-4 h-4'])
                                    </div>

                                    <a href="{{ route('admin.pixels', ['user_id' => $user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Pixels') }}</a>

                                    <span class="badge badge-primary me-4">{{ number_format($user->pixels_count, 0, __('.'), __(',')) }}</span>

                                    @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@if(request()->is('admin/*'))
    @include('shared.sidebars.admin')
@else
    @include('shared.sidebars.user')
@endif
