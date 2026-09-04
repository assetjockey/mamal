@extends('layouts.app')

@section('site_title', __('Report protected'))

@section('content')
    <div class="bg-base-1 d-flex align-items-center flex-fill">
        <div class="container py-6">
            <div class="row h-100 justify-content-center align-items-center py-3">
                <div class="col-lg-6">
                    <form action="{{ route('reports.password', ['id' => $report->id]) }}" method="post">
                        @csrf

                        <h1 class="h2 mb-3 text-center">{{ __('Report protected') }}</h1>
                        <p class="mb-5 text-center text-muted">{{ __('This report is password protected.') }}</p>

                        <div class="form-row">
                            <div class="col-12 col-sm">
                                <input id="i-password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="col-12 col-sm-auto mt-3 mt-sm-0">
                                <button type="submit" class="btn btn-primary btn-block">{{ __('Validate') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
