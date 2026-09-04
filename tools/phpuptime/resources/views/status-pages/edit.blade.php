@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Status page'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.status_pages') : route('status_pages'), 'title' => __('Status pages')],
                        ['title' => __('Edit')],
                    ]])

                    <div class="d-flex">
                        <h1 class="h2 mb-3 text-break">{{ __('Edit') }}</h1>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Status page') }}</div>
                                </div>
                                <div class="col-auto">
                                    <div class="form-row">
                                        <div class="col">
                                            @include('status-pages.partials.context-menu')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ request()->is('admin/*') ? route('admin.status_pages.edit', $statusPage->id) : route('status_pages.edit', $statusPage->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @if(request()->is('admin/*'))
                                    <input type="hidden" name="user_id" value="{{ $statusPage->user->id }}">
                                @endif

                                <div class="form-group">
                                    <label for="i-name" class="d-flex align-items-center">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') ?? $statusPage->name }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-slug">{{ __('Slug') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text d-block align-items-center text-truncate max-width-52 max-width-md-full">{{ str_replace(['http://', 'https://'], '', route('status_pages.show', ['id' => '/'])) }}/</span>
                                        </div>
                                        <input type="text" name="slug" id="i-slug" class="form-control{{ $errors->has('slug') ? ' is-invalid' : '' }}" value="{{ old('slug') ?? $statusPage->slug }}">
                                    </div>
                                    @if ($errors->has('slug'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('slug') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The URL slug of the status page.') }}</small>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col">
                                            <label for="i-monitor-ids-target" class="d-flex align-items-center">{{ __('Monitors') }}</label>
                                        </div>
                                    </div>

                                    <div id="monitor-ids-container" data-order-multi-select="monitor-ids">
                                        @if (old('monitor_ids') !== null && is_array(old('monitor_ids')))
                                            @foreach (old('monitor_ids') as $monitorId)
                                                <input type="hidden" name="monitor_ids[]" value="{{ $monitorId }}" multiple>
                                            @endforeach
                                        @else
                                            @foreach ($statusPage->monitors->pluck('id')->toArray() as $monitorId)
                                                <input type="hidden" name="monitor_ids[]" value="{{ $monitorId }}" multiple>
                                            @endforeach
                                        @endif
                                    </div>

                                    <input type="hidden" name="monitor_ids_visible[]" value="" multiple>
                                    <select name="monitor_ids_visible[]" id="i-monitor-ids-target" class="custom-select{{ $errors->has('monitor_ids') ? ' is-invalid' : '' }}" size="{{ (count($monitors) == 0 ? 1 : 5) }}" multiple>
                                        @foreach($monitors as $monitor)
                                            <option value="{{ $monitor->id }}" @if((old('monitor_ids') !== null && is_array(old('monitor_ids')) && in_array($monitor->id, old('monitor_ids'))) || ($statusPage->monitors->contains('id', $monitor->id) && old('monitor_ids') == null)) selected @endif>{{ $monitor->name }} ({{ $monitor->displayUrl }})</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('monitor_ids'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('monitor_ids') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __('Hold :ctrl or :cmd to select or deselect multiple items.', ['ctrl' => '<span class="font-weight-medium">CTRL</span>', 'cmd' => '<span class="font-weight-medium">CMD</span>']) !!}</small>
                                </div>

                                <div class="form-row form-group">
                                    <div class="col-12"><label for="i-logo" class="d-flex align-items-center">{{ __('Logo') }}</label></div>
                                    <div class="col">
                                        <div class="input-group">
                                            @if ($statusPage->logo)
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text py-1 px-2"><img src="{{ $statusPage->logoUrl }}" class="max-height-6.5"></span>
                                                </div>
                                            @endif
                                            <div class="custom-file">
                                                <input type="file" name="logo" id="i-logo" class="custom-file-input{{ $errors->has('logo') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.status_page_logo_format') }}">
                                                <label for="i-logo" class="custom-file-label" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                            </div>
                                        </div>
                                        @if ($errors->has('logo'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('logo') }}</strong>
                                            </span>
                                        @endif
                                        @if ($errors->has('remove_logo'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('remove_logo') }}</strong>
                                            </span>
                                        @endif
                                    </div>

                                    @if ($statusPage->logo)
                                        <div class="col-auto">
                                            <div class="btn-group-toggle input-group-prepend" data-toggle="buttons">
                                                <label class="btn btn-outline-danger">
                                                    <input type="checkbox" name="remove_logo" value="1" data-disable-input="i-logo"> {{ __('Remove') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-row form-group">
                                    <div class="col-12"><label for="i-favicon" class="d-flex align-items-center">{{ __('Favicon') }}</label></div>
                                    <div class="col">
                                        <div class="input-group">
                                            @if ($statusPage->favicon)
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text py-1 px-2"><img src="{{ $statusPage->faviconUrl }}" class="max-height-6.5"></span>
                                                </div>
                                            @endif
                                            <div class="custom-file">
                                                <input type="file" name="favicon" id="i-favicon" class="custom-file-input{{ $errors->has('favicon') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.status_page_favicon_format') }}">
                                                <label for="i-favicon" class="custom-file-label" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
                                            </div>
                                        </div>
                                        @if ($errors->has('favicon'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('favicon') }}</strong>
                                            </span>
                                        @endif
                                        @if ($errors->has('remove_favicon'))
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $errors->first('remove_favicon') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                    @if ($statusPage->favicon)
                                        <div class="col-auto">
                                            <div class="btn-group-toggle input-group-prepend" data-toggle="buttons">
                                                <label class="btn btn-outline-danger">
                                                    <input type="checkbox" name="remove_favicon" value="1" data-disable-input="i-favicon"> {{ __('Remove') }}
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-website-url">{{ __('Website URL') }}</label>
                                    <input type="text" dir="ltr" name="website_url" class="form-control{{ $errors->has('website_url') ? ' is-invalid' : '' }}" id="i-website-url" value="{{ old('website_url') ?? $statusPage->website_url }}" placeholder="https://example.com">
                                    @if ($errors->has('website_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('website_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The primary website URL.') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-contact-url">{{ __('Contact URL') }}</label>
                                    <input type="text" dir="ltr" name="contact_url" class="form-control{{ $errors->has('contact_url') ? ' is-invalid' : '' }}" id="i-contact-url" value="{{ old('contact_url') ?? $statusPage->contact_url }}" placeholder="https://example.com/contact">
                                    @if ($errors->has('contact_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('contact_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The contact URL.') }} {!! __('Supports :mailto and :tel.', ['mailto' => '<code>mailto:contact@example.com</code>', 'tel' => '<code>tel:+0123456789</code>']) !!}</small>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Privacy') }}</label>
                                    <div class="form-group mb-0">
                                        <div class="row mx-n2">
                                            <div class="col-12 col-lg-4 px-2">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if($statusPage->privacy == 1 && old('privacy') == null || old('privacy') == 1) checked @endif>
                                                    <label for="i-privacy1" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Private') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible only by you.') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4 px-2 pt-2 pt-lg-0">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if($statusPage->privacy == 0 && old('privacy') == null || old('privacy') == 0 && old('privacy') != null) checked @endif>
                                                    <label for="i-privacy0" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Public') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible by anyone.') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4 px-2 pt-2 pt-lg-0">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if($statusPage->privacy == 2 && old('privacy') == null || old('privacy') == 2) checked @endif>
                                                    <label for="i-privacy2" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Password') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible by password.') }}</span>
                                                    </label>

                                                    <div id="input-password" class="{{ (((old('privacy') == 2) || (old('privacy') == null && $statusPage->privacy == 2)) ? '' : 'd-none')}}">
                                                        <div class="input-group mt-2">
                                                            <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') ?? $statusPage->password }}" autocomplete="new-password">
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

                                <div class="pb-3">
                                    <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseSeo" aria-expanded="{{ ($errors->has('noindex') || $errors->has('meta_title') || $errors->has('meta_description') ? 'true' : 'false') }}" aria-controls="collapseSeo">
                                        @include('icons.search', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('SEO') }}
                                    </button>

                                    <div class="collapse {{ ($errors->has('noindex') || $errors->has('meta_title') || $errors->has('meta_description') ? 'show' : '') }}" id="collapseSeo">
                                        <div class="form-group mt-3 mb-0">
                                            <label for="i-meta-title" class="d-flex align-items-center">{{ __('Meta title') }}</label>
                                            <input type="text" name="meta_title" class="form-control{{ $errors->has('meta_title') ? ' is-invalid' : '' }}" id="i-meta-title" value="{{ old('meta_title') ?? $statusPage->meta_title }}">
                                            @if ($errors->has('meta_title'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('meta_title') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <label for="i-meta-description" class="d-flex align-items-center">{{ __('Meta description') }}</label>
                                            <input type="text" name="meta_description" class="form-control{{ $errors->has('meta_description') ? ' is-invalid' : '' }}" id="i-meta-description" value="{{ old('meta_description') ?? $statusPage->meta_description }}">
                                            @if ($errors->has('meta_description'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('meta_description') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="noindex" value="0">
                                                <input type="checkbox" name="noindex" value="1" class="custom-control-input {{ $errors->has('noindex') ? ' is-invalid' : '' }}" id="i-noindex" @if($statusPage->noindex && old('noindex') == null || old('noindex')) checked @endif>
                                                <label for="i-noindex" class="custom-control-label">
                                                    <div>{{ __('Noindex') }}</div>
                                                    <div class="small text-muted">{{ __('Exclude the status page from search engines.') }}</div>
                                                    @if ($errors->has('noindex'))
                                                        <span class="invalid-feedback d-block" role="alert">
                                                            <strong>{{ $errors->first('noindex') }}</strong>
                                                        </span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="pb-3">
                                    <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseBranding" aria-expanded="{{ ($errors->has('domain') || $errors->has('custom_css') || $errors->has('custom_js') ? 'true' : 'false') }}" aria-controls="collapseBranding">
                                        @include('icons.design-services', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Branding') }}
                                    </button>

                                    <div class="collapse {{ ($errors->has('domain') || $errors->has('custom_css') || $errors->has('custom_js') ? 'show' : '') }}" id="collapseBranding">
                                        <div class="form-group mt-3 mb-0">
                                            <div class="d-flex align-items-center mb-2">
                                                <label for="i-domain" class="d-flex align-items-center mb-0">
                                                    {{ __('Custom domain') }}
                                                </label>
                                                @cannot('statusPageCustomization', [App\Models\User::class])
                                                    @if(enabledPaymentProcessors())
                                                        <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                                    @endif
                                                @endcannot
                                            </div>
                                            <input type="text" dir="ltr" name="domain" class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}" id="i-domain" value="{{ old('domain') ?? $statusPage->domain }}" placeholder="example.com" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>
                                            @if ($errors->has('domain'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('domain') }}</strong>
                                                </span>
                                            @endif
                                            <small class="form-text text-muted">{!! __('The DNS of the domain must include an A record pointing to :ip, or a CNAME record pointing to :domain.', ['ip' => '<strong>' . getHostIp() . '</strong>', 'domain' => '<strong>' . parse_url(config('app.url'), PHP_URL_HOST) . '</strong>']) !!}</small>
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <div class="d-flex align-items-center mb-2">
                                                <label for="i-custom-css" class="d-flex align-items-center mb-0">
                                                    {{ __('Custom CSS') }}
                                                </label>
                                                @cannot('statusPageCustomization', [App\Models\User::class])
                                                    @if(enabledPaymentProcessors())
                                                        <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                                    @endif
                                                @endcannot
                                            </div>
                                            <textarea name="custom_css" id="i-custom-css" class="form-control{{ $errors->has('custom_css') ? ' is-invalid' : '' }}" rows="4" placeholder="body #services-container {
                        background: black;
                        color: white;
                    }" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>{{ old('custom_css') ?? $statusPage->custom_css }}</textarea>
                                            @if ($errors->has('custom_css'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('custom_css') }}</strong>
                                                </span>
                                            @endif
                                            <small class="form-text text-muted">{{ __('Only works when a custom domain is set.') }}</small>
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <div class="d-flex align-items-center mb-2">
                                                <label for="i-custom-js" class="d-flex align-items-center mb-0">
                                                    {{ __('Custom JS') }}
                                                </label>
                                                @cannot('statusPageCustomization', [App\Models\User::class])
                                                    @if(enabledPaymentProcessors())
                                                        <a href="{{ route('pricing') }}" data-tooltip="true" title="{{ __('Unlock feature') }}" class="d-flex align-items-center {{ (__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2') }}">@include('icons.lock-open', ['class' => 'fill-current text-primary width-4 height-4'])</a>
                                                    @endif
                                                @endcannot
                                            </div>
                                            <textarea name="custom_js" id="i-custom-js" class="form-control{{ $errors->has('custom_js') ? ' is-invalid' : '' }}" rows="4" placeholder="<script>
                        alert('Hello World');
                    </script>" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>{{ old('custom_js') ?? $statusPage->custom_js }}</textarea>
                                            @if ($errors->has('custom_js'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('custom_js') }}</strong>
                                                </span>
                                            @endif
                                            <small class="form-text text-muted">{{ __('Only works when a custom domain is set.') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>

                    @if(request()->is('admin/*'))
                        <div class="row m-n2 pt-3">
                            <div class="col-12 col-md-6 col-lg-4 p-2">
                                <a href="{{ route('admin.users.edit', ['id' => $statusPage->user->id]) }}" class="text-decoration-none text-dark">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <img src="{{ $statusPage->user->avatar_url }}" alt="{{ $statusPage->user->name }}" class="width-8 height-8 rounded-circle">

                                            <span class="font-weight-medium text-decoration-none text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-3 mr-2' : 'mr-2 ml-3') }}">{{ $statusPage->user->name }}</span>

                                            @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                                        </div>
                                    </div>
                                </a>
                            </div>

                            @if($statusPage->monitors_count)
                                <div class="col-12 col-md-6 col-lg-4 p-2">
                                    <a href="{{ route('admin.monitors', ['status_page_id' => $statusPage->id]) }}" class="text-decoration-none text-dark">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body d-flex align-items-center text-truncate">
                                                <div class="d-flex position-relative text-primary width-8 height-8 align-items-center justify-content-center flex-shrink-0">
                                                    <div class="position-absolute bg-primary opacity-10 top-0 right-0 bottom-0 left-0 border-radius-lg"></div>
                                                    @include('icons.monitor-heart', ['class' => 'fill-current width-4 height-4'])
                                                </div>

                                                <span class="font-weight-medium text-decoration-none text-truncate {{ (__('lang_dir') == 'rtl' ? 'ml-3 mr-2' : 'mr-2 ml-3') }}">{{ __('Monitors') }}</span>

                                                <span class="badge badge-primary">{{ number_format($statusPage->monitors_count, 0, __('.'), __(',')) }}</span>

                                                @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 width-3 height-3 fill-current ' . (__('lang_dir') == 'rtl' ? 'mr-auto' : 'ml-auto')])
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if(request()->is('admin/*'))
    @include('shared.sidebars.admin')
@else
    @include('shared.sidebars.user')
@endif
