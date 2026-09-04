@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Status page'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-3 mt-3 pb-6">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => route('dashboard'), 'title' => __('Home')],
                        ['url' => route('status_pages'), 'title' => __('Status pages')],
                        ['title' => __('New')],
                    ]])

                    <h1 class="h2 mb-3 d-inline-block">{{ __('New') }}</h1>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header">
                            <div class="row">
                                <div class="col">
                                    <div class="font-weight-medium py-1">{{ __('Status page') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ route('status_pages.new') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="i-name" class="d-flex align-items-center">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') }}">
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
                                        <input type="text" name="slug" id="i-slug" class="form-control{{ $errors->has('slug') ? ' is-invalid' : '' }}" value="{{ old('slug') }}">
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
                                        @endif
                                    </div>

                                    <input type="hidden" name="monitor_ids_visible[]" value="" multiple>
                                    <select name="monitor_ids_visible[]" id="i-monitor-ids-target" class="custom-select{{ $errors->has('monitor_ids') ? ' is-invalid' : '' }}" size="{{ (count($monitors) == 0 ? 1 : 5) }}" multiple>
                                        @foreach($monitors as $monitor)
                                            <option value="{{ $monitor->id }}" @if(old('monitor_ids') !== null && is_array(old('monitor_ids')) && in_array($monitor->id, old('monitor_ids'))) selected @endif>{{ $monitor->name }} ({{ $monitor->displayUrl }})</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('monitor_ids'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('monitor_ids') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{!! __('Hold :ctrl or :cmd to select or deselect multiple items.', ['ctrl' => '<span class="font-weight-medium">CTRL</span>', 'cmd' => '<span class="font-weight-medium">CMD</span>']) !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-logo" class="d-flex align-items-center">{{ __('Logo') }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" id="i-logo" class="custom-file-input{{ $errors->has('logo') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.status_page_logo_format') }}">
                                        <label for="i-logo" class="custom-file-label" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
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

                                <div class="form-group">
                                    <label for="i-favicon" class="d-flex align-items-center">{{ __('Favicon') }}</label>
                                    <div class="custom-file">
                                        <input type="file" name="favicon" id="i-favicon" class="custom-file-input{{ $errors->has('favicon') ? ' is-invalid' : '' }} cursor-pointer" accept="{{ config('settings.status_page_favicon_format') }}">
                                        <label for="i-favicon" class="custom-file-label" data-browse="{{ __('Browse') }}">{{ __('Choose file') }}</label>
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

                                <div class="form-group">
                                    <label for="i-website-url">{{ __('Website URL') }}</label>
                                    <input type="text" dir="ltr" name="website_url" class="form-control{{ $errors->has('website_url') ? ' is-invalid' : '' }}" id="i-website-url" value="{{ old('website_url') }}" placeholder="https://example.com">
                                    @if ($errors->has('website_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('website_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The primary website URL.') }}</small>
                                </div>
                                <div class="form-group">
                                    <label for="i-contact-url">{{ __('Contact URL') }}</label>
                                    <input type="text" dir="ltr" name="contact_url" class="form-control{{ $errors->has('contact_url') ? ' is-invalid' : '' }}" id="i-contact-url" value="{{ old('contact_url') }}" placeholder="https://example.com/contact">
                                    @if ($errors->has('contact_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('contact_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The contact URL.') }} {!! __('Supports :mailto and :tel.', ['mailto' => '<code>mailto:contact@example.com</code>', 'tel' => '<code>tel:+0123456789</code>']) !!}</small>
                                </div>

                                <div class="form-group">
                                    <label class="d-flex align-items-center">{{ __('Privacy') }}</label>
                                    <div class="form-group mb-0">
                                        <div class="row mx-n2">
                                            <div class="col-12 col-lg-4 px-2">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy1" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="1" @if(old('privacy') == 1 && old('privacy') != null) checked @endif>
                                                    <label for="i-privacy1" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Private') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible only by you.') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4 px-2 pt-2 pt-lg-0">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy0" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="0" @if(old('privacy') == null || old('privacy') == 0) checked @endif>
                                                    <label for="i-privacy0" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Public') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible by anyone.') }}</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-12 col-lg-4 px-2 pt-2 pt-lg-0">
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" id="i-privacy2" name="privacy" class="custom-control-input{{ $errors->has('privacy') ? ' is-invalid' : '' }}" value="2" @if(old('privacy') == 2) checked @endif>
                                                    <label for="i-privacy2" class="custom-control-label w-100 d-flex flex-column">
                                                        <span>{{ __('Password') }}</span>
                                                        <span class="small text-muted">{{ __('Status page accessible by password.') }}</span>
                                                    </label>
                                                    <div id="input-password" class="{{ (old('privacy') != 2 ? 'd-none' : '') }}">
                                                        <div class="input-group mt-2">
                                                            <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" value="{{ old('password') }}" autocomplete="new-password">
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
                                            <input type="text" name="meta_title" class="form-control{{ $errors->has('meta_title') ? ' is-invalid' : '' }}" id="i-meta-title" value="{{ old('meta_title') }}">
                                            @if ($errors->has('meta_title'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('meta_title') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <label for="i-meta-description" class="d-flex align-items-center">{{ __('Meta description') }}</label>
                                            <input type="text" name="meta_description" class="form-control{{ $errors->has('meta_description') ? ' is-invalid' : '' }}" id="i-meta-description" value="{{ old('meta_description') }}">
                                            @if ($errors->has('meta_description'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('meta_description') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group mt-3 mb-0">
                                            <div class="custom-control custom-checkbox">
                                                <input type="hidden" name="noindex" value="0">
                                                <input type="checkbox" name="noindex" value="1" class="custom-control-input {{ $errors->has('noindex') ? ' is-invalid' : '' }}" id="i-noindex" @if(old('noindex')) checked @endif>
                                                <label for="i-noindex" class="custom-control-label">
                                                    {{ __('Noindex') }}
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
                                    <button class="btn btn-light d-block w-100 d-flex align-items-center justify-content-center" type="button" data-toggle="collapse" data-target="#collapseCustomization" aria-expanded="{{ ($errors->has('domain') || $errors->has('custom_css') || $errors->has('custom_js') ? 'true' : 'false') }}" aria-controls="collapseCustomization">
                                        @include('icons.design-services', ['class' => 'width-4 height-4 fill-current ' . (__('lang_dir') == 'rtl' ? 'ml-2' : 'mr-2')]) {{ __('Customization') }}
                                    </button>

                                    <div class="collapse {{ ($errors->has('domain') || $errors->has('custom_css') || $errors->has('custom_js') ? 'show' : '') }}" id="collapseCustomization">
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
                                            <input type="text" dir="ltr" name="domain" class="form-control{{ $errors->has('domain') ? ' is-invalid' : '' }}" id="i-domain" value="{{ old('domain') }}" placeholder="example.com" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>
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
                    }" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>{{ old('custom_css') }}</textarea>
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
                    </script>" @cannot('statusPageCustomization', [App\Models\User::class]) disabled @endcannot>{{ old('custom_js') }}</textarea>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
