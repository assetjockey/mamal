@extends('layouts.app')

@section('site_title', formatTitle([__('Delete'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('dashboard'), 'title' => __('Home')],
                ['url' => route('account'), 'title' => __('Account')],
                ['title' => __('Delete')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Delete') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Delete') }}</div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    <div class="alert alert-danger" role="alert">
                        {{ __('Deleting your account is permanent, and will remove all the data associated with it.') }}
                    </div>

                    <form action="{{ route('account.destroy') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="i-current-password">{{ __('Current password') }}</label>
                            <input type="password" name="current_password" id="i-current-password" class="form-control{{ $errors->has('current_password') ? ' is-invalid' : '' }}">
                            @if ($errors->has('current_password'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('current_password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <button type="submit" name="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
