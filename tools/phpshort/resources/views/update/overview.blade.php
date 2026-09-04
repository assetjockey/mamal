@extends('layouts.minimal')

@section('site_title', formatTitle([__('Update'), config('info.software.name')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container py-16 d-flex align-items-center justify-content-center">
            <div class="max-w-136 w-full">
                @include('update.partials.menu')

                <form action="{{ route('update.overview') }}" method="post">
                    @csrf

                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body text-center py-12">
                            @include('shared.message')

                            <div class="my-16">
                                <p class="text-muted fs-lg">{{ __('Updates pending') }}</p>

                                <div class="fs-3xl fw-semibold tracking-tight m-0">{{ $updates }}</div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-block btn-primary d-inline-flex align-items-center mt-4 py-2 position-relative" type="submit" data-button-loader>
                        <span class="position-absolute top-0 end-0 bottom-0 start-0 d-flex align-items-center justify-content-center">
                            <span class="d-none spinner-border spinner-border-sm w-4 h-4" role="status"></span>
                        </span>
                        <span class="spinner-text d-inline-flex align-items-center mx-auto">{{ __('Next') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])</span>&#8203;
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
