@extends('layouts.minimal')

@section('site_title', formatTitle([__('Installation'), config('info.software.name')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container py-16 d-flex align-items-center justify-content-center">
            <div class="max-w-136 w-full">
                @include('install.partials.menu')

                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header">
                        <div class="fw-medium py-1">{{ __('Requirements') }}</div>
                    </div>

                    <div class="card-body">
                        @foreach($results['extensions'] as $type => $extension)
                            <div class="list-group list-group-flush {{ $loop->index == 0 ? 'mb-n4 mt-n4' : 'mt-4 mb-n4 pt-4' }}">
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <span class="fw-medium">{{ mb_strtoupper($type) }}</span>
                                            @if($type == 'php')
                                                {{ config('install.php_version') }}+
                                            @endif
                                        </div>

                                        <div class="col-auto d-flex align-items-center">
                                            @if($type == 'php')
                                                @if(version_compare(PHP_VERSION, config('install.php_version'), '>='))
                                                    @include('icons.checkmark', ['class' => 'text-success w-4 h-4 fill-current'])
                                                @else
                                                    @include('icons.close', ['class' => 'text-danger w-4 h-4 fill-current'])
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @foreach($extension as $name => $enabled)
                                    <div class="list-group-item px-0 text-muted">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                {{ $name }}
                                            </div>
                                            <div class="col-auto d-flex align-items-center">
                                                @if($enabled)
                                                    @include('icons.checkmark', ['class' => 'text-success w-4 h-4 fill-current'])
                                                @else
                                                    @include('icons.close', ['class' => 'text-danger w-4 h-4 fill-current'])
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(!isset($results['errors']))
                    <a href="{{ route('install.permissions') }}" class="btn btn-block btn-primary d-inline-flex align-items-center mt-4 py-2">
                        <span class="d-inline-flex align-items-center mx-auto">
                            {{ __('Next') }} @include((__('lang_dir') == 'rtl' ? 'icons.chevron-left' : 'icons.chevron-right'), ['class' => 'w-3 h-3 fill-current ms-2'])
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
