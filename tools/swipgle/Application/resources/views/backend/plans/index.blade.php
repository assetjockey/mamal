@extends('backend.layouts.grid')
@section('title', __('Pricing plans'))
@section('link', route('admin.plans.create'))
@section('content')
    <div class="card custom-card custom-tabs mb-3">
        <div class="card-body">
            <ul class="nav nav-pills" role="tablist">
                <li role="presentation">
                    <button class="nav-link active me-2" id="monthly-tab" data-bs-toggle="tab" data-bs-target="#monthly"
                        type="button" role="tab" aria-controls="monthly" aria-selected="true">{{ __('Monthly plans') }}
                        ({{ count($monthlyPlans) }})</button>
                </li>
                <li role="presentation">
                    <button class="nav-link me-2" id="yearly-tab" data-bs-toggle="tab" data-bs-target="#yearly"
                        type="button" role="tab" aria-controls="yearly" aria-selected="false">{{ __('Yearly plans') }}
                        ({{ count($yearlyPlans) }})</button>
                </li>
                <li role="presentation">
                    <button class="nav-link" id="lifetime-tab" data-bs-toggle="tab" data-bs-target="#lifetime"
                        type="button" role="tab" aria-controls="Lifetime" aria-selected="false">{{ __('Lifetime plans') }}
                        ({{ count($lifetimePlans) }})</button>
                </li>
            </ul>
        </div>
    </div>
    <div class="card custom-card">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
                <table class="datatable-50 table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Plan name') }}</th>
                            <th class="tb-w-3x">{{ __('Storage space') }}</th>
                            <th class="tb-w-3x">{{ __('Transfer size') }}</th>
                            <th class="tb-w-3x">{{ __('Files duration') }}</th>
                            <th class="tb-w-3x">{{ __('Plan price') }}</th>
                            <th class="tb-w-3x">{{ __('Plan Interval') }}</th>
                            <th class="tb-w-3x">{{ __('Added at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthlyPlans as $monthlyPlan)
                            <tr class="item">
                                <td>{{ $monthlyPlan->id }}</td>
                                <td>
                                    {{ $monthlyPlan->name }}
                                    {{ $monthlyPlan->featured_plan ? __('(Featured)') : '' }}
                                    {{ $monthlyPlan->price == 0 ? __('(Free)') : '' }}
                                </td>
                                <td>{{ $monthlyPlan->storage_space == null ? __('Unlimited') : formatBytes($monthlyPlan->storage_space) }}
                                </td>
                                <td>{{ $monthlyPlan->transfer_size == null ? __('Unlimited') : formatBytes($monthlyPlan->transfer_size) }}
                                </td>
                                <td>
                                    @if ($monthlyPlan->transfer_interval == 1)
                                        {{ $monthlyPlan->transfer_interval == null ? __('Unlimited time') : $monthlyPlan->transfer_interval . ' ' . __('Day') }}
                                    @else
                                        {{ $monthlyPlan->transfer_interval == null ? __('Unlimited time') : $monthlyPlan->transfer_interval . ' ' . __('Days') }}
                                    @endif
                                </td>
                                <td>
                                    <strong>
                                        @if ($monthlyPlan->price == 0)
                                            <span class="text-success">{{ __('Free') }}</span>
                                        @else
                                            <span class="text-dark">{{ priceSymbol($monthlyPlan->price) }}</span>
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    @if ($monthlyPlan->interval == 0)
                                        <span class="badge bg-secondary">{{ __('Monthly') }}</span>
                                    @elseif($monthlyPlan->interval == 1)
                                        <span class="badge bg-primary">{{ __('Yearly') }}</span>
                                    @else
                                        <span class="badge bg-primary">{{ __('Lifetime') }}</span>
                                    @endif
                                </td>
                                <td>{{ vDate($monthlyPlan->created_at) }}</td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.plans.edit', $monthlyPlan->id) }}"><i
                                                        class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.plans.destroy', $monthlyPlan->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button class="vironeer-able-to-delete dropdown-item text-danger"><i
                                                            class="far fa-trash-alt me-2"></i>{{ __('Delete') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="yearly" role="tabpanel" aria-labelledby="yearly-tab">
                <table class="datatable-50 table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Plan name') }}</th>
                            <th class="tb-w-3x">{{ __('Storage space') }}</th>
                            <th class="tb-w-3x">{{ __('Transfer size') }}</th>
                            <th class="tb-w-3x">{{ __('File duration') }}</th>
                            <th class="tb-w-3x">{{ __('Plan price') }}</th>
                            <th class="tb-w-3x">{{ __('Plan Interval') }}</th>
                            <th class="tb-w-3x">{{ __('Added at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($yearlyPlans as $yearlyPlan)
                            <tr class="item">
                                <td>{{ $yearlyPlan->id }}</td>
                                <td>
                                    {{ $yearlyPlan->name }}
                                    {{ $yearlyPlan->featured_plan ? __('(Featured)') : '' }}
                                    {{ $yearlyPlan->price == 0 ? __('(Free)') : '' }}
                                </td>
                                <td>{{ $yearlyPlan->storage_space == null ? __('Unlimited') : formatBytes($yearlyPlan->storage_space) }}
                                </td>
                                <td>{{ $yearlyPlan->transfer_size == null ? __('Unlimited') : formatBytes($yearlyPlan->transfer_size) }}
                                </td>
                                <td>
                                    @if ($yearlyPlan->transfer_interval == 1)
                                        {{ $yearlyPlan->transfer_interval == null ? __('Unlimited time') : $yearlyPlan->transfer_interval . ' ' . __('Day') }}
                                    @else
                                        {{ $yearlyPlan->transfer_interval == null ? __('Unlimited time') : $yearlyPlan->transfer_interval . ' ' . __('Days') }}
                                    @endif
                                </td>
                                <td>
                                    <strong>
                                        @if ($yearlyPlan->price == 0)
                                            <span class="text-success">{{ __('Free') }}</span>
                                        @else
                                            <span class="text-dark">{{ priceSymbol($yearlyPlan->price) }}</span>
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    @if ($yearlyPlan->interval == 0)
                                        <span class="badge bg-secondary">{{ __('Monthly') }}</span>
                                    @elseif($yearlyPlan->interval == 1)
                                        <span class="badge bg-primary">{{ __('Yearly') }}</span>
                                    @else
                                        <span class="badge bg-primary">{{ __('Lifetime') }}</span>
                                    @endif
                                </td>
                                <td>{{ vDate($yearlyPlan->created_at) }}</td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.plans.edit', $yearlyPlan->id) }}"><i
                                                        class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.plans.destroy', $yearlyPlan->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button class="vironeer-able-to-delete dropdown-item text-danger"><i
                                                            class="far fa-trash-alt me-2"></i>{{ __('Delete') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="lifetime" role="tabpanel" aria-labelledby="lifetime-tab">
                <table class="datatable-50 table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Plan name') }}</th>
                            <th class="tb-w-3x">{{ __('Storage space') }}</th>
                            <th class="tb-w-3x">{{ __('Transfer size') }}</th>
                            <th class="tb-w-3x">{{ __('File duration') }}</th>
                            <th class="tb-w-3x">{{ __('Plan price') }}</th>
                            <th class="tb-w-3x">{{ __('Plan Interval') }}</th>
                            <th class="tb-w-3x">{{ __('Added at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lifetimePlans as $lifetimePlan)
                            <tr class="item">
                                <td>{{ $lifetimePlan->id }}</td>
                                <td>
                                    {{ $lifetimePlan->name }}
                                    {{ $lifetimePlan->featured_plan ? __('(Featured)') : '' }}
                                    {{ $lifetimePlan->price == 0 ? __('(Free)') : '' }}
                                </td>
                                <td>{{ $lifetimePlan->storage_space == null ? __('Unlimited') : formatBytes($lifetimePlan->storage_space) }}
                                </td>
                                <td>{{ $lifetimePlan->transfer_size == null ? __('Unlimited') : formatBytes($lifetimePlan->transfer_size) }}
                                </td>
                                <td>
                                    @if ($lifetimePlan->transfer_interval == 1)
                                        {{ $lifetimePlan->transfer_interval == null ? __('Unlimited time') : $lifetimePlan->transfer_interval . ' ' . __('Day') }}
                                    @else
                                        {{ $lifetimePlan->transfer_interval == null ? __('Unlimited time') : $lifetimePlan->transfer_interval . ' ' . __('Days') }}
                                    @endif
                                </td>
                                <td>
                                    <strong>
                                        @if ($lifetimePlan->price == 0)
                                            <span class="text-success">{{ __('Free') }}</span>
                                        @else
                                            <span class="text-dark">{{ priceSymbol($lifetimePlan->price) }}</span>
                                        @endif
                                    </strong>
                                </td>
                                <td>
                                    @if ($lifetimePlan->interval == 0)
                                        <span class="badge bg-secondary">{{ __('Monthly') }}</span>
                                    @elseif($lifetimePlan->interval == 1)
                                        <span class="badge bg-primary">{{ __('Yearly') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('Lifetime') }}</span>
                                    @endif
                                </td>
                                <td>{{ vDate($lifetimePlan->created_at) }}</td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.plans.edit', $lifetimePlan->id) }}"><i
                                                        class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.plans.destroy', $lifetimePlan->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button class="vironeer-able-to-delete dropdown-item text-danger"><i
                                                            class="far fa-trash-alt me-2"></i>{{ __('Delete') }}</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
