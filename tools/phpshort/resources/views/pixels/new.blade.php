@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Pixel'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => request()->is('admin/*') ? route('admin.dashboard') : route('dashboard'), 'title' => request()->is('admin/*') ? __('Admin') : __('Home')],
                        ['url' => request()->is('admin/*') ? route('admin.pixels') : route('pixels'), 'title' => __('Pixels')],
                        ['title' => __('New')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('New') }}</h1>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Pixel') }}</div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ route('pixels.new') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="i-name">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" id="i-name" value="{{ old('name') }}">
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
                                            <option value="{{ $key }}" @if(old('type') == $key) selected @endif>{{ $value['name'] }}</option>
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
                                    <input type="text" name="value" class="form-control{{ $errors->has('value') ? ' is-invalid' : '' }}" id="i-value" value="{{ old('value') }}">
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
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
