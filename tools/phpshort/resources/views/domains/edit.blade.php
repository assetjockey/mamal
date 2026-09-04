@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Domain'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.domains') : route('domains'), 'title' => __('Domains')],
                        ['title' => __('Edit')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                        </div>
                        <div class="col-auto px-2">
                            <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                            @include('domains.partials.context-menu')
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Domain') }}</div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            @if(request()->is('admin/domains/new'))
                                <div class="alert alert-warning">{{ __('This domain will be available as a plan feature.') }}</div>
                            @endif

                            <form action="{{ request()->is('admin/*') ? route('admin.domains.edit', $domain->id) : route('domains.edit', $domain->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="i-name">{{ __('Domain') }}</label>
                                    <input type="text" dir="ltr" name="name" class="form-control" id="i-name" value="{{ old('name') ?? $domain->name }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="i-title">{{ __('Title') }}</label>
                                    <input type="text" dir="ltr" name="title" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" id="i-title" value="{{ old('title') ?? $domain->title }}">
                                    @if ($errors->has('title'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('title') }}</strong>
                                        </span>
                                    @endif
                                    <small class="form-text text-muted">{{ __('The website title, displayed in the browser tab, email subjects, and various other contexts.') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-homepage-url">{{ __('Homepage URL') }}</label>
                                    <input type="text" dir="ltr" name="homepage_url" id="i-homepage-url" class="form-control{{ $errors->has('homepage_url') ? ' is-invalid' : '' }}" value="{{ (old('homepage_url') ?? $domain->homepage_url) }}">
                                    @if ($errors->has('homepage_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('homepage_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="text-muted">{{ __('The URL to which visitors are redirected when accessing the website\'s homepage.') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="i-not-found-url">{{ __('404 Not Found URL') }}</label>
                                    <input type="text" dir="ltr" name="not_found_url" id="i-not-found-url" class="form-control{{ $errors->has('not_found_url') ? ' is-invalid' : '' }}" value="{{ (old('not_found_url') ?? $domain->not_found_url) }}">
                                    @if ($errors->has('not_found_url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('not_found_url') }}</strong>
                                        </span>
                                    @endif
                                    <small class="text-muted">{{ __('The URL to which visitors are redirected when a link does not exist.') }}</small>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>

                    @if(request()->is('admin/*'))
                        <div class="row m-n2 pt-4">
                            @if ($domain->user)
                                <div class="col-12 col-md-6 col-lg-4 p-2">
                                    <div class="card border-0 shadow-sm h-full">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <img src="{{ $domain->user->avatar_url }}" class="w-8 h-8 rounded-circle" alt="">

                                            <a href="{{ route('admin.users.edit', ['id' => $domain->user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link mx-4">{{ $domain->user->name }}</a>

                                            @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($domain->links_count)
                                <div class="col-12 col-md-6 col-lg-4 p-2">
                                    <div class="card border-0 shadow-sm h-full">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                                @include('icons.link', ['class' => 'fill-current w-4 h-4'])
                                            </div>

                                            <a href="{{ route('admin.links', ['domain_id' => $domain->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Links') }}</a>

                                            <span class="badge badge-primary me-4">{{ number_format($domain->links_count, 0, __('.'), __(',')) }}</span>

                                            @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                        </div>
                                    </div>
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
