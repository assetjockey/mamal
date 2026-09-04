@extends('layouts.app')

@section('site_title', formatTitle([__('Account'), __('Developers'), config('settings.title')]))

@section('head_content')

@endsection

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container h-full py-4 my-4">

            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('home'), 'title' => __('Home')],
                ['url' => route('developers'), 'title' => __('Developers')],
                ['title' => __('Account')]
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Account') }}</h1>
                </div>
            </div>

            @include('developers.partials.authentication')

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header align-items-center">
                    <div class="fw-medium py-1">{{ __('Show') }}</div>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        {{ __('API endpoint') }}
                    </p>
                    <div class="bg-light text-inverse p-4 rounded d-flex align-items-center mb-4" dir="ltr">
                        <span class="badge badge-success px-2 py-1 me-4">GET</span>
<pre class="m-0">{{ route('api.account.index') }}</pre>
                    </div>

                    <p class="mb-2">
                        {{ __('Request example') }}
                    </p>
<pre class="bg-light text-inverse p-4 mb-0 rounded text-left" dir="ltr">
curl --location --request GET '{{ route('api.account.index') }}' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer <span class="text-primary">{api_key}</span>'
</pre>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.user')
