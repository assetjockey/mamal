@extends('layouts.app')

@section('site_title', __('Stats protected'))

@section('head_content')
    <meta name="robots" content="noindex">
@endsection

@section('content')
    <div class="bg-base-1 d-flex align-items-center flex-fill">
        <div class="container py-16 d-flex align-items-center justify-content-center">
            <div class="max-w-136 w-full">
                <form action="{{ route('stats.password', ['id' => $link->id]) }}" method="post">
                    @csrf

                    <div class="text-center">
                        <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Stats protected') }}</h1>
                        <p class="mb-12 mt-4 text-muted">{{ __('These stats are password protected.') }}</p>
                    </div>

                    <div class="row mx-n1">
                        <div class="col-12 col-sm px-1">
                            <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">
                            @if ($errors->has('password'))
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col-12 col-sm-auto mt-4 mt-sm-0 px-1">
                            <button type="submit" class="btn btn-primary btn-block">{{ __('Validate') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')