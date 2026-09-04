@section('site_title', formatTitle([$page['name'], config('settings.title')]))

@extends('layouts.app')

@section('head_content')

@endsection

@section('content')
<div class="bg-base-1 flex-fill">
    <div class="container py-16">
        <div class="text-center">
            <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ $page->name }}</h1>
            <div class="mx-auto mt-4">
                <p class="text-muted fw-normal fs-lg mb-0">{{ __('Updated at') }}: {{ $page->updated_at->tz(Auth::user()->timezone ?? config('settings.timezone'))->format(__('Y-m-d')) }}.</p>
            </div>
        </div>

        <div class="h-full justify-content-center align-items-center mt-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    {!! __($page->content) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.sidebars.user')