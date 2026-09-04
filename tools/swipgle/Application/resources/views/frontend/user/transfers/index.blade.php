@extends('frontend.user.layouts.dash')
@section('title', lang('My Transfers', 'user'))
@section('search', true)
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4 col-xxl-4">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-success">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Active transfers', 'user') }}</p>
                        <p class="vr__counter__text mb-0">{{ $activeTransfersCount }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-xxl-4">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-lg-7">
                        <i class="far fa-comment-dots"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Expired transfers', 'user') }}</p>
                        <p class="vr__counter__text mb-0">{{ $expiredTransfersCount }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 col-xxl-4">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-danger">
                        <i class="far fa-times-circle"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Canceled transfers', 'user') }}</p>
                        <p class="vr__counter__text mb-0">{{ $canceledTransfersCount }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('frontend.user.includes.transfers')
@endsection
