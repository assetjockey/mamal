@extends('layouts.app')

@section('site_title', formatTitle([__('Payments'), config('settings.title')]))

@section('content')
    <div class="bg-base-1 flex-fill">
        <div class="container pt-4 mt-4 pb-16">
            @include('shared.breadcrumbs', ['breadcrumbs' => [
                ['url' => route('admin.dashboard'), 'title' => __('Admin')],
                ['title' => __('Payments')],
            ]])

            <div class="row mx-n2 mb-4">
                <div class="col px-2">
                    <h1 class="fs-3xl fw-medium tracking-tight m-0">{{ __('Payments') }}</h1>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header align-items-center">
                    <div class="row mx-n2">
                        <div class="col px-2">
                            <div class="fw-medium py-1">{{ __('Payments') }}</div>
                        </div>
                        <div class="col-auto px-2">
                            <form method="GET" action="{{ route('admin.payments') }}" class="d-md-flex">
                                <div class="input-group input-group-sm">
                                    <input class="form-control max-w-32 max-w-sm-full" name="search" placeholder="{{ __('Search') }}" value="{{ request()->input('search') }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary d-flex align-items-center dropdown-toggle dropdown-toggle-split reset-after" data-tooltip="true" title="{{ __('Filters') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.filter', ['class' => 'fill-current w-4 h-4'])&#8203;</button>
                                        <div class="dropdown-menu {{ (__('lang_dir') == 'rtl' ? 'dropdown-menu' : 'dropdown-menu-right') }} border-0 shadow w-64 p-0" id="search-filters">
                                            <div class="dropdown-header py-4">
                                                <div class="row">
                                                    <div class="col"><div class="fw-medium m-0 text-body">{{ __('Filters') }}</div></div>
                                                    <div class="col-auto"><a href="{{ route('admin.payments') }}" class="text-secondary">{{ __('Reset') }}</a></div>
                                                </div>
                                            </div>

                                            <div class="dropdown-divider my-0"></div>

                                            <div class="max-h-96 overflow-auto pt-4">
                                                <div class="form-group px-6">
                                                    <div class="text-truncate d-block">
                                                        <label for="i-user-id" class="small">{{ __('User') }}</label>
                                                        @if ($user) <a href="{{ route('admin.users.edit', ['id' => $user->id]) }}" class="small text-truncate">{{ $user->name }}</a> @endif
                                                    </div>
                                                    <input type="text" name="user_id" class="form-control form-control-sm" id="i-user-id" value="{{ request()->input('user_id') }}" placeholder="{{ __('ID') }}">
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-search-by" class="small">{{ __('Search by') }}</label>
                                                    <select name="search_by" id="i-search-by" class="custom-select custom-select-sm rounded-sm">
                                                        @foreach(['payment_id' => __('Payment ID'), 'invoice_id' => __('Invoice ID')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('search_by') == $key || !request()->input('search_by') && $key == 'name') selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-plan-id" class="small">{{ __('Plan') }}</label>
                                                    <select id="i-plan-id" name="plan_id" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach($plans as $plan)
                                                            <option value="{{ $plan->id }}" @if(request()->input('plan_id') == $plan->id) selected @endif>{{ $plan->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-interval" class="small">{{ __('Interval') }}</label>
                                                    <select name="interval" id="i-interval" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach(['month' => __('Monthly'), 'year' => __('Yearly')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('interval') == $key && request()->input('interval') !== null) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-processor" class="small">{{ __('Processor') }}</label>
                                                    <select name="processor" id="i-processor" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach(config('payment.processors') as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('processor') == $key && request()->input('processor') !== null) selected @endif>{{ __($value['name']) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-status" class="small">{{ __('Status') }}</label>
                                                    <select name="status" id="i-status" class="custom-select custom-select-sm rounded-sm">
                                                        <option value="">{{ __('All') }}</option>
                                                        @foreach(['completed' => __('Completed'), 'pending' => __('Pending'), 'cancelled' => __('Cancelled')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('status') == $key && request()->input('status') !== null) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-sort-by" class="small">{{ __('Sort by') }}</label>
                                                    <select name="sort_by" id="i-sort-by" class="custom-select custom-select-sm rounded-sm">
                                                        @foreach(['id' => __('Date created')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('sort_by') == $key) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-sort" class="small">{{ __('Sort') }}</label>
                                                    <select name="sort" id="i-sort" class="custom-select custom-select-sm rounded-sm">
                                                        @foreach(['desc' => __('Descending'), 'asc' => __('Ascending')] as $key => $value)
                                                            <option value="{{ $key }}" @if(request()->input('sort') == $key) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group px-6">
                                                    <label for="i-per-page" class="small">{{ __('Results per page') }}</label>
                                                    <select name="per_page" id="i-per-page" class="custom-select custom-select-sm rounded-sm">
                                                        @foreach([10, 25, 50, 100] as $value)
                                                            <option value="{{ $value }}" @if(request()->input('per_page') == $value || request()->input('per_page') == null && $value == config('settings.paginate')) selected @endif>{{ $value }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="dropdown-divider my-0"></div>

                                            <div class="px-6 py-4">
                                                <button type="submit" class="btn btn-primary btn-sm btn-block">{{ __('Search') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('shared.message')

                    @if(count($payments) == 0)
                        {{ __('No results found.') }}
                    @else
                        <div class="list-group list-group-flush my-n4">
                            <div class="list-group-item px-0 text-muted">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <div class="row align-items-center">
                                            <div class="col-12 col-lg-5 d-flex">
                                                {{ __('Amount') }}
                                            </div>

                                            <div class="col-12 col-lg-5 d-flex">
                                                {{ __('User') }}
                                            </div>

                                            <div class="col-12 col-lg-2 d-flex">
                                                {{ __('Status') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="invisible btn btn-ghost-primary btn-sm d-flex align-items-center">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</div>
                                    </div>
                                </div>
                            </div>
                            @foreach($payments as $payment)
                                <div class="list-group-item px-0">
                                    <div class="row align-items-center">
                                        <div class="col text-truncate">
                                            <div class="row align-items-center">
                                                <div class="col-12 col-lg-5 d-flex">
                                                    <div class="text-truncate">
                                                        <div class="d-flex align-items-center">
                                                            <img src="{{ asset('img/icons/payments/' . $payment->processor . '.svg') }}" class="w-6 rounded-sm" alt="">

                                                            <div class="text-truncate ms-4 me-2">
                                                                <a href="{{ route('admin.payments.edit', $payment->id) }}">{{ formatMoney($payment->amount, $payment->currency) }}</a> <span class="text-muted">{{ $payment->currency }}</span>
                                                            </div>

                                                            @if(config('settings.invoicing'))
                                                                @if(in_array($payment->status, ['completed', 'cancelled']))
                                                                    <a href="{{ route('admin.invoices.show', $payment->id) }}" class="badge badge-secondary text-truncate" data-tooltip="true" title="{{ __('Invoice') }}">{{ $payment->invoice_id }}</a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-lg-5 d-flex align-items-center">
                                                    @if($payment->user)
                                                        <div class="d-inline-block me-4 text-truncate">
                                                            <img src="{{$payment->user->avatarUrl }}" class="rounded-circle w-6 h-6" alt="">
                                                        </div>
                                                        <a href="{{ route('admin.users.edit', $payment->user->id) }}">{{ $payment->user->name }}</a>
                                                    @else
                                                        <div class="d-inline-block me-4">
                                                            <img src="{{ asset('img/user.png') }}" class="rounded-circle w-6 h-6" alt="{{ __('User') }}">
                                                        </div>
                                                        <div class="text-muted">{{ __('Deleted') }}</div>
                                                    @endif
                                                </div>

                                                <div class="col-12 col-lg-2 d-flex">
                                                    @if($payment->status == 'completed')
                                                        <span class="badge badge-success text-truncate">{{ __('Completed') }}</span>
                                                    @elseif($payment->status == 'pending')
                                                        <span class="badge badge-secondary text-truncate">{{ __('Pending') }}</span>
                                                    @else
                                                        <span class="badge badge-danger text-truncate">{{ __('Cancelled') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#" class="btn btn-ghost-primary btn-sm d-flex align-items-center dropdown-toggle reset-after" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@include('icons.more-horiz', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

                                            @include('account.payments.partials.context-menu')
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mt-4 align-items-center">
                                <div class="row">
                                    <div class="col">
                                        <div class="mt-2 mb-4">{{ __('Showing :from-:to of :total', ['from' => $payments->firstItem(), 'to' => $payments->lastItem(), 'total' => $payments->total()]) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        {{ $payments->onEachSide(1)->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@include('shared.sidebars.admin')
