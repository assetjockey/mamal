@section('site_title', formatTitle([__('Service unavailable'), config('settings.title')]))

@extends('layouts.error')

@section('content')
    <div class="bg-base-1 d-flex align-items-center flex-fill">
        <div class="container py-16 d-flex align-items-center justify-content-center">
            <div class="max-w-136 w-full">
                <h1 class="fs-4xl fw-black tracking-tight m-0 text-center">{{ 503 }}</h1>
                <p class="mb-0 mt-4 text-center text-muted">{{ __('Service unavailable') }}.</p>

                @if(url()->previous() != url()->current())
                    <div class="text-center mt-12">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">{{ __('Go back') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
