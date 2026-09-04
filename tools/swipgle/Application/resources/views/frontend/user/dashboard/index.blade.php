@extends('frontend.user.layouts.dash')
@section('title', lang('Dashboard', 'user'))
@section('search', true)
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="vr__counter__box bg-primary h-100">
                <div class="bx mb-3">
                    <h3 class="vr__counter__box__title">{{ lang('Your storage space', 'user') }}</h3>
                    <p class="vr__counter__box__number">
                        {{ subscription()->storage->used_space }}
                        <span class="text-primary">{{ lang('of', 'user') }}</span>
                        {{ subscription()->plan->storage_space }}
                    </p>
                    @if (subscription()->storage->remaining_space)
                        <span>{{ lang('Remaining', 'user') }} ({{ subscription()->storage->remaining_space }})</span>
                    @endif
                    <span class="vr__counter__box__icon pb-2 pe-3">
                        <i class="fas fa-database"></i>
                    </span>
                </div>
                @if (!is_null(subscription()->plan->storage_space_number))
                    @php
                        if (subscription()->storage->used_percentage > 80) {
                            $bg = 'bg-danger';
                        } elseif (subscription()->storage->used_percentage > 50 && subscription()->storage->used_percentage < 80) {
                            $bg = 'bg-warning';
                        } else {
                            $bg = 'bg-success';
                        }
                    @endphp
                    <div class="progress">
                        <div class="progress-bar {{ $bg }}" role="progressbar"
                            style="width: {{ subscription()->storage->used_percentage }}%"
                            aria-valuenow="{{ subscription()->storage->used_percentage }}" aria-valuemin="0"
                            aria-valuemax="100"></div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="vr__counter__box bg-secondary h-100">
                <div class="bx mb-2">
                    <h3 class="vr__counter__box__title">{{ lang('Total transfers', 'user') }}</h3>
                    <p class="vr__counter__box__number">{{ shortNumber($totalTransfers) }}</p>
                    <span class="vr__counter__box__icon pb-2 pe-3">
                        <i class="fas fa-paper-plane"></i>
                    </span>
                </div>
                <a href="{{ url('/') }}" class="btn btn-primary"><i
                        class="far fa-paper-plane me-2"></i>{{ lang('Start Transfer', 'user') }}</a>
            </div>
        </div>
    </div>
    <h5 class="fs-5 mb-4">{{ lang('Your transfers', 'user') }}</h5>
    @include('frontend.user.includes.transfers')
@endsection
