@extends('backend.layouts.grid')
@section('title', __('Transfers | guests'))
@section('content')
    <div class="card custom-card custom-tabs mb-3">
        <div class="card-body">
            <ul class="nav nav-pills" role="tablist">
                <li role="presentation">
                    <button class="nav-link active me-2" id="byemail-tab" data-bs-toggle="tab" data-bs-target="#byemail"
                        type="button" role="tab" aria-controls="byemail" aria-selected="true"><i
                            class="fa fa-envelope me-2"></i>{{ __('Transferred by email') }}
                        ({{ count($byEmailTransfers) }})</button>
                </li>
                <li role="presentation">
                    <button class="nav-link me-2" id="bylink-tab" data-bs-toggle="tab" data-bs-target="#bylink"
                        type="button" role="tab" aria-controls="bylink" aria-selected="false"><i
                            class="fa fa-link me-2"></i>{{ __('Transferred by link') }}
                        ({{ count($byLinkTransfers) }})</button>
                </li>
                @if (count($canceledTransfers) > 0)
                    <li role="presentation">
                        <button class="nav-link" id="canceled-tab" data-bs-toggle="tab" data-bs-target="#canceled"
                            type="button" role="tab" aria-controls="canceled" aria-selected="false"><i
                                class="far fa-times-circle me-2"></i>{{ __('Canceled Transfers') }}
                            ({{ count($canceledTransfers) }})</button>
                    </li>
                @endif
            </ul>
        </div>
    </div>
    <div class="card custom-card">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="byemail" role="tabpanel" aria-labelledby="byemail-tab">
                <table class="datatable-50 table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Transfer Number') }}</th>
                            <th class="tb-w-7x">{{ __('Guest Ip') }}</th>
                            <th class="tb-w-7x">{{ __('Transferred at') }}</th>
                            <th class="tb-w-7x">{{ __('Expiry at') }}</th>
                            <th class="tb-w-7x">{{ __('Storage') }}</th>
                            <th class="tb-w-3x">{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byEmailTransfers as $byEmailTransfer)
                            <tr>
                                <td>{{ $byEmailTransfer->id }}</td>
                                <td><a
                                        href="{{ route('admin.transfers.guests.edit', $byEmailTransfer->unique_id) }}">#{{ $byEmailTransfer->unique_id }}</a>
                                </td>
                                <td><a href="{{ route('admin.users.logsbyip', $byEmailTransfer->ip) }}"><i
                                            class="fas fa-map-marker-alt me-2"></i>{{ $byEmailTransfer->ip }}</a>
                                </td>
                                <td>{{ vDate($byEmailTransfer->created_at) }}</td>
                                <td>
                                    @if ($byEmailTransfer->expiry_at)
                                        <span
                                            class="{{ !isExpiry($byEmailTransfer->expiry_at) ? 'text-success' : 'text-danger' }}">{{ vDate($byEmailTransfer->expiry_at) }}</span>
                                    @else
                                        <span><i>{{ __('Unlimited time') }}</i></span>
                                    @endif
                                </td>
                                <td><i class="fas fa-database me-2"></i>{{ $byEmailTransfer->storageProvider->name }}
                                </td>
                                <td>
                                    @if ($byEmailTransfer->status)
                                        <span class="badge bg-success">{{ __('Transferred') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Canceled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            @if (!isExpiry($byEmailTransfer->expiry_at))
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('transfer.download.index', $byEmailTransfer->link) }}"
                                                        target="_blank"><i
                                                            class="fa fa-eye me-2"></i>{{ __('View') }}</a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.transfers.guests.edit', $byEmailTransfer->unique_id) }}"><i
                                                        class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form
                                                    action="{{ route('admin.transfers.guests.destroy', $byEmailTransfer->unique_id) }}"
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
            <div class="tab-pane fade" id="bylink" role="tabpanel" aria-labelledby="bylink-tab">
                <table class="datatable-50 table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Transfer Number') }}</th>
                            <th class="tb-w-7x">{{ __('Guest Ip') }}</th>
                            <th class="tb-w-7x">{{ __('Transferred at') }}</th>
                            <th class="tb-w-7x">{{ __('Expiry at') }}</th>
                            <th class="tb-w-7x">{{ __('Storage') }}</th>
                            <th class="tb-w-3x">{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($byLinkTransfers as $byLinkTransfer)
                            <tr>
                                <td>{{ $byLinkTransfer->id }}</td>
                                <td><a
                                        href="{{ route('admin.transfers.guests.edit', $byLinkTransfer->unique_id) }}">#{{ $byLinkTransfer->unique_id }}</a>
                                </td>
                                <td><a href="{{ route('admin.users.logsbyip', $byLinkTransfer->ip) }}"><i
                                            class="fas fa-map-marker-alt me-2"></i>{{ $byLinkTransfer->ip }}</a>
                                </td>
                                <td>{{ vDate($byLinkTransfer->created_at) }}</td>
                                <td>
                                    @if ($byLinkTransfer->expiry_at)
                                        <span
                                            class="{{ !isExpiry($byLinkTransfer->expiry_at) ? 'text-success' : 'text-danger' }}">{{ vDate($byLinkTransfer->expiry_at) }}</span>
                                    @else
                                        <span><i>{{ __('Unlimited time') }}</i></span>
                                    @endif
                                </td>
                                <td><i class="fas fa-database me-2"></i>{{ $byLinkTransfer->storageProvider->name }}
                                </td>
                                <td>
                                    @if ($byLinkTransfer->status)
                                        <span class="badge bg-success">{{ __('Transferred') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('Canceled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            @if (!isExpiry($byLinkTransfer->expiry_at))
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('transfer.download.index', $byLinkTransfer->link) }}"
                                                        target="_blank"><i
                                                            class="fa fa-eye me-2"></i>{{ __('View') }}</a>
                                                </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.transfers.guests.edit', $byLinkTransfer->unique_id) }}"><i
                                                        class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form
                                                    action="{{ route('admin.transfers.guests.destroy', $byLinkTransfer->unique_id) }}"
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
            @if (count($canceledTransfers) > 0)
                <div class="tab-pane fade" id="canceled" role="tabpanel" aria-labelledby="canceled-tab">
                    <table class="datatable-50 table w-100">
                        <thead>
                            <tr>
                                <th class="tb-w-2x">{{ __('#') }}</th>
                                <th class="tb-w-3x">{{ __('Transfer Number') }}</th>
                                <th class="tb-w-7x">{{ __('Guest Ip') }}</th>
                                <th class="tb-w-7x">{{ __('Transferred at') }}</th>
                                <th class="tb-w-7x">{{ __('Expiry at') }}</th>
                                <th class="tb-w-7x">{{ __('Storage') }}</th>
                                <th class="tb-w-3x">{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($canceledTransfers as $canceledTransfer)
                                <tr>
                                    <td>{{ $canceledTransfer->id }}</td>
                                    <td><a
                                            href="{{ route('admin.transfers.guests.edit', $canceledTransfer->unique_id) }}">#{{ $canceledTransfer->unique_id }}</a>
                                    </td>
                                    <td><a href="{{ route('admin.users.logsbyip', $canceledTransfer->ip) }}"><i
                                                class="fas fa-map-marker-alt me-2"></i>{{ $canceledTransfer->ip }}</a>
                                    </td>
                                    <td>{{ vDate($canceledTransfer->created_at) }}</td>
                                    <td>
                                        @if ($canceledTransfer->expiry_at)
                                            <span
                                                class="{{ !isExpiry($canceledTransfer->expiry_at) ? 'text-success' : 'text-danger' }}">{{ vDate($canceledTransfer->expiry_at) }}</span>
                                        @else
                                            <span><i>{{ __('Unlimited time') }}</i></span>
                                        @endif
                                    </td>
                                    <td><i
                                            class="fas fa-database me-2"></i>{{ $canceledTransfer->storageProvider->name }}
                                    </td>
                                    <td>
                                        @if ($canceledTransfer->status)
                                            <span class="badge bg-success">{{ __('Transferred') }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ __('Canceled') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                                aria-expanded="true">
                                                <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-sm-end"
                                                data-popper-placement="bottom-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('admin.transfers.guests.edit', $canceledTransfer->unique_id) }}"><i
                                                            class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider" />
                                                </li>
                                                <li>
                                                    <form
                                                        action="{{ route('admin.transfers.guests.destroy', $canceledTransfer->unique_id) }}"
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
            @endif
        </div>
    </div>
@endsection
