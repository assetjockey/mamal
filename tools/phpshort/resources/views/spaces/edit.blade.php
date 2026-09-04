@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Space'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.spaces') : route('spaces'), 'title' => __('Spaces')],
                        ['title' => __('Edit')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                        </div>
                        <div class="col-auto px-2">
                            <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                            @include('spaces.partials.context-menu')
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Space') }}</div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ request()->is('admin/*') ? route('admin.spaces.edit', $space->id) : route('spaces.edit', $space->id) }}" method="post" enctype="multipart/form-data">
                                @csrf

                                @if(request()->is('admin/*'))
                                    <input type="hidden" name="user_id" value="{{ $space->user->id }}">
                                @endif

                                <div class="form-group">
                                    <label for="i-name">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') ?? $space->name }}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="i-color1">{{ __('Color') }}</label>
                                    <div class="row mx-n2">
                                        @foreach(spaceColors() as $key => $value)
                                            <div class="col-4 col-sm px-2">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="i-color{{ $key }}" name="color" class="custom-control-input{{ $errors->has('color') ? ' is-invalid' : '' }}" value="{{ $key }}" @if($key == $space->color) checked @endif>
                                                    <label class="custom-control-label d-flex align-items-center" for="i-color{{ $key }}"><span class="badge badge-{{ $value }}">{{ __('Example') }}</span></label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($errors->has('color'))
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $errors->first('color') }}</strong>
                                        </span>
                                    @endif
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
                                        <img src="{{ $space->user->avatar_url }}" alt="" class="w-8 h-8 rounded-circle" alt="">

                                        <a href="{{ route('admin.users.edit', ['id' => $space->user->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link mx-4">{{ $space->user->name }}</a>

                                        @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'flex-shrink-0 w-3 h-3 fill-current ms-auto'])
                                    </div>
                                </div>
                            </div>

                            @if($space->links_count)
                                <div class="col-12 col-md-6 col-lg-4 p-2">
                                    <div class="card border-0 shadow-sm h-full">
                                        <div class="card-body d-flex align-items-center text-truncate">
                                            <div class="d-flex position-relative text-primary w-8 h-8 align-items-center justify-content-center flex-shrink-0">
                                                <div class="position-absolute bg-primary opacity-10 top-0 end-0 bottom-0 start-0 rounded-lg"></div>
                                                @include('icons.link', ['class' => 'fill-current w-4 h-4'])
                                            </div>

                                            <a href="{{ route('admin.links', ['space_id' => $space->id]) }}" class="text-decoration-none fw-medium text-inverse text-truncate stretched-link me-2 ms-4">{{ __('Links') }}</a>

                                            <span class="badge badge-primary me-4">{{ number_format($space->links_count, 0, __('.'), __(',')) }}</span>

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
