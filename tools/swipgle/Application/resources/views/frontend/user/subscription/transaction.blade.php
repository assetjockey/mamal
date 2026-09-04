@extends('frontend.user.layouts.dash')
@section('title', lang('Transaction details', 'user') . ' #' . $transaction->transaction_id)
@section('back', route('user.subscription'))
@section('content')
    @if ($transaction->status == 3)
        <div class="alert alert-danger">
            <p class="mb-0"><strong>{{ lang('Transaction has been canceled', 'user') }}</strong></p>
            @if ($transaction->cancellation_reason)
                <p class="mb-0 mt-1"><i
                        class="fas fa-quote-left me-2"></i><i>{{ $transaction->cancellation_reason }}</i></p>
            @endif
        </div>
    @endif
    <div class="custom-list card mb-3">
        <div class="card-header border-bottom-0">
            {{ lang('Transaction details', 'user') }}
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Transaction Number', 'user') }}</strong></span>
                <span>#{{ $transaction->transaction_id }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Plan (Interval)', 'user') }}</strong></span>
                <span class="text-capitalize">{{ $transaction->plan->name }}
                    @if ($transaction->plan->interval == 0)
                        ({{ lang('Monthly', 'plans') }})
                    @elseif($transaction->plan->interval == 1)
                        ({{ lang('Yearly', 'plans') }})
                    @elseif($transaction->plan->interval == 2)
                        ({{ lang('lifetime', 'plans') }})
                    @endif
                </span>
            </li>
            @if ($transaction->coupon_id)
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <span><strong>{{ lang('Coupon Code', 'user') }}</strong></span>
                    <span><i class="fas fa-ticket-alt me-2"></i>{{ $transaction->coupon->code }}</span>
                </li>
            @endif
            @if ($transaction->gateway)
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <span><strong>{{ lang('Payment method', 'user') }}</strong></span>
                    <span>{{ $transaction->gateway->name }}</span>
                </li>
            @endif
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Transaction Type', 'user') }}</strong></span>
                <span>
                    @if ($transaction->type == 0)
                        <strong>{{ lang('Subscribe', 'user') }}</strong>
                    @elseif($transaction->type == 1)
                        <strong>{{ lang('Renew', 'user') }}</strong>
                    @elseif($transaction->type == 2)
                        <strong>{{ lang('Upgrade', 'user') }}</strong>
                    @endif
                </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Transaction Status', 'user') }}</strong></span>
                <span>
                    @if ($transaction->plan_price != 0)
                        @if ($transaction->status == 2)
                            <span class="badge bg-success">{{ lang('Paid', 'user') }}</span>
                        @elseif($transaction->status == 3)
                            <span class="badge bg-danger">{{ lang('Canceled', 'user') }}</span>
                        @endif
                    @else
                        @if ($transaction->status == 2)
                            <span class="badge bg-secondary">{{ lang('Done', 'user') }}</span>
                        @elseif($transaction->status == 3)
                            <span class="badge bg-danger">{{ lang('Canceled', 'user') }}</span>
                        @endif
                    @endif
                </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Transaction date', 'user') }}</strong></span>
                <span><strong>{{ vDate($transaction->created_at) }}</strong></span>
            </li>
        </ul>
    </div>
    <div class="custom-list card mb-3">
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Plan Price', 'user') }}</strong></span>
                <span><strong>{{ priceSymbol($transaction->details_before_discount->plan_price) }}</strong></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Taxes', 'user') }}</strong></span>
                <span><strong>+{{ priceSymbol($transaction->details_before_discount->tax_price) }}</strong></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span><strong>{{ lang('Subtotal', 'user') }}</strong></span>
                <span><strong>{{ priceSymbol($transaction->details_before_discount->total_price) }}</strong></span>
            </li>
            @if ($transaction->coupon_id)
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <span><strong>{{ lang('Discount', 'user') }}</strong>
                        <span class="text-muted">({{ $transaction->coupon->percentage }}%)</span></span>
                    <span
                        class="text-danger"><strong>-{{ priceSymbol($transaction->details_before_discount->total_price - $transaction->details_after_discount->total_price) }}</strong></span>
                </li>
            @endif
            @if ($transaction->gateway)
                <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                    <span><strong>{{ lang('Gateway Fees', 'user') }}</strong></span>
                    <span><strong>+{{ priceSymbol($transaction->fees_price) }}</strong></span>
                </li>
            @endif
            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                <span>
                    <h5 class="mb-0"><strong>{{ lang('Total', 'user') }}</strong></h5>
                </span>
                <span>
                    <h5 class="mb-0"><strong>{{ priceSymbol($transaction->total_price) }}</strong></h5>
                </span>
            </li>
        </ul>
    </div>
@endsection
