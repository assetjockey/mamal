@extends('layouts.app')

@section('site_title', formatTitle([__('New'), __('Space'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            <div class="row">
                <div class="col-12">
                    @include('shared.breadcrumbs', ['breadcrumbs' => [
                        ['url' => route('dashboard'), 'title' => __('Home')],
                        ['url' => route('spaces'), 'title' => __('Spaces')],
                        ['title' => __('New')],
                    ]])

                    <div class="row mx-n2 mb-4">
                        <div class="col px-2">
                            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('New') }}</h1>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header align-items-center">
                            <div class="fw-medium py-1">{{ __('Space') }}</div>
                        </div>
                        <div class="card-body">
                            @include('shared.message')

                            <form action="{{ route('spaces.new') }}" method="post" enctype="multipart/form-data">
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
                                    <label for="i-name">{{ __('Color') }}</label>
                                    <div class="row mx-n2">
                                        @foreach(spaceColors() as $key => $value)
                                            <div class="col-4 col-sm px-2">
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="i-color{{ $key }}" name="color" class="custom-control-input{{ $errors->has('color') ? ' is-invalid' : '' }}" value="{{ $key }}" @if($key == 1) checked @endif>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
