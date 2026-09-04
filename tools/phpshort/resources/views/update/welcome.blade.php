@extends('layouts.minimal')

@section('site_title', formatTitle([__('Update'), config('info.software.name')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container py-16 d-flex align-items-center justify-content-center">
            <div class="max-w-136 w-full">
                @include('update.partials.menu')

                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-12">
                        <div class="h-full d-flex flex-column justify-content-center align-items-center my-16">
                            <div class="position-relative w-32 h-32 d-flex align-items-center justify-content-center">
                                <div class="position-absolute top-0 end-0 bottom-0 start-0 bg-primary opacity-10 rounded-circle"></div>

                                @include('icons.update', ['class' => 'text-primary fill-current w-16 h-16'])

                                @include('icons.pending-filled', ['class' => 'position-absolute end-0 bottom-0 text-secondary fill-current w-8 h-8'])
                            </div>

                            <div>
                                <h1 class="fs-xl fw-medium mb-2 mt-6 text-center">{{ __('Update') }}</h1>
                                <p class="text-center text-muted mb-0">{!! __(':name update wizard.', ['name' => '<span class="fw-medium">'.(config('settings.title') ?? config('info.software.name')).'</span>']) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('update.overview') }}" class="btn btn-block btn-primary d-inline-flex align-items-center mt-4 py-2">
                    <span class="d-inline-flex align-items-center mx-auto">
                        {{ __('Start') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])
                    </span>
                </a>
            </div>
        </div>
    </div>
@endsection
