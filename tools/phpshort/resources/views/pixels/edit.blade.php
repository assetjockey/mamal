@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Pixel'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.pixels') : route('pixels'), 'title' => __('Pixels')],
                        ['title' => __('Edit')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                        </div>
                        <div class="col-auto px-2">
                            <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                            @include('pixels.partials.context-menu')
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Pixel') }}</div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ request()->is('admin/*') ? route('admin.pixels.edit', $pixel->id) : route('pixels.edit', $pixel->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @if(request()->is('admin/*'))
                                    <input type="hidden" name="user_id" value="{{ $pixel->user->id }}">
                                @endif

                                <div class="form-group">
                                    <label for="i-name">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') ?? $pixel->name }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-type">{{ __('Type') }}</label>
                                    <select name="type" id="i-type" class="custom-select{{ $errors->has('type') ? ' is-invalid' : '' }}">
                                        @foreach(config('pixels') as $key => $value)
                                            <option value="{{ $key }}" @if((old('type') !== null && old('type') == $key) || ($pixel->type == $key && old('type') == null)) selected @endif>{{ $value['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('type'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('type') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-value">{{ __('Value') }}</label>
                                    <input type="text" name="value" class="form-control{{ $errors->has('value') ? ' is-invalid' : '' }}" id="i-value" value="{{ old('value') ?? $pixel->value }}">
                                    @if ($errors->has('value'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('value') }}</strong>
                                        </span>
                                    @endif

                                    <small class="form-text text-muted">{{ __('The pixel ID value.') }}</small>
                                </div>

                                <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            </form>
                        </div>
                    </div>

                    @if(request()->is('admin/*'))
                        <div class="row m-n2 pt-4">
                            <div class="col-12 col-md-6 col-lg-4 p-2">
                                <div class="card border-0 shadow-sm h-full">
                                    <div class="card-body d-flex align-items-center text-truncate">
                                        <img src="{{ $pixel->user->avatar_url }}" class="w-8 h-8 rounded-circle" alt="">

                                        <a href="{{ route('admin.users.edit', ['id' => $pixel->user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link mx-4">{{ $pixel->user->name }}</a>

                                        @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                    </div>
                                </div>
                            </div>

                            @if($pixel->links_count)
                                <div class="col-12 col-md-6 col-lg-4 p-2">
                                    <div class="card border-0 shadow-sm h-full">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                                @include('icons.link', ['class' => 'fill-current w-4 h-4'])
                                            </div>

                                            <a href="{{ route('admin.links', ['pixel_id' => $pixel->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Links') }}</a>

                                            <span class="badge badge-primary me-4">{{ number_format($pixel->links_count, 0, __('.'), __(',')) }}</span>

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
