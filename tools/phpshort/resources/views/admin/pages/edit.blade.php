@extends('layouts.app')

@section('site_title', formatTitle([__('Edit'), __('Page'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['url' => route('admin.pages'), 'title' => __('Pages')],
                ['title' => __('Edit')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Edit') }}</h1>
                </div>
                <div class="col-auto px-2">
                    <a href="#" class="btn btn-ghost-secondary d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                    @include('admin.pages.partials.context-menu')
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Page') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <form action="{{ route('admin.pages.edit', $page->id) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-name">{{ __('Name') }}</label>
                            <input type="text" name="name" id="i-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ $page->name }}">
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
                                    <span class="input-group-text d-block align-items-center text-truncate max-w-52 max-w-md-full">{{ str_replace(['http://', 'https://'], '', route('pages.show', ['id' => '/'])) }}/</span>
                                </div>
                                <input type="text" name="slug" id="i-slug" class="form-control{{ $errors->has('slug') ? ' is-invalid' : '' }}" value="{{ old('slug') ?? $page->slug }}">
                            </div>
                            @if ($errors->has('slug'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('slug') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-visibility">{{ __('Visibility') }}</label>
                            <select name="visibility" id="i-visibility" class="custom-select{{ $errors->has('visibility') ? ' is-invalid' : '' }}">
                                @foreach([0 => __('Unlisted'), 1 => __('Footer')] as $key => $value)
                                    <option value="{{ $key }}" @if ((old('visibility') !== null && old('visibility') == $key) || ($page->visibility == $key && old('visibility') == null)) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('visibility'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('visibility') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-language">{{ __('Language') }}</label>
                            <select name="language" id="i-language" class="custom-select{{ $errors->has('language') ? ' is-invalid' : '' }}">
                                @foreach(config('app.locales') as $key => $value)
                                    <option value="{{ $key }}" @if ((old('language') !== null && old('language') == $key) || ($page->language == $key && old('language') == null)) selected @endif>{{ $value['name'] }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('language'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('language') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="i-content">{{ __('Content') }}</label>
                            <textarea name="content" id="i-content" class="form-control{{ $errors->has('content') ? ' is-invalid' : '' }} field-sizing-content min-h-22" rows="3">{{ $page->content }}</textarea>
                            @if ($errors->has('content'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('content') }}</strong>
                                </span>
                            @endif
                            <small class="form-text text-muted">
                                {{ __('Supports HTML.') }}
                            </small>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
