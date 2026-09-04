@extends('layouts.install')

@section('site_title', formatTitle([__('Update'), config('info.software.name')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container">
            <div class="row h-100 justify-content-center align-items-center py-5">
                <div class="col-lg-6">
                    @include('update.partials.menu')

                    <form action="{{ route('update.overview') }}" method="post">
                        @csrf

                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="card-body text-center py-5">
                                @include('shared.message')

                                <div class="my-6">
                                    <p class="text-muted font-size-lg">{{ __('Updates pending') }}</p>

                                    <div class="h1">{{ $updates }}</div>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-block btn-primary d-inline-flex align-items-center mt-3 py-2 position-relative" type="submit" data-button-loader>
                            <span class="position-absolute top-0 right-0 bottom-0 left-0 d-flex align-items-center justify-content-center">
                                <span class="d-none spinner-border spinner-border-sm width-4 height-4" role="status"></span>
                            </span>
                            <span class="spinner-text d-inline-flex align-items-center mx-auto">{{ __('Next') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'width-3 height-3 fill-current '.(__('lang_dir') == 'rtl' ? 'mr-2' : 'ml-2')])</span>&#8203;
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
