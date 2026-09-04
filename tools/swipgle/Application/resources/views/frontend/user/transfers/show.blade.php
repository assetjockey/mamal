@extends('frontend.user.layouts.dash')
@section('title', lang('Transfer details', 'user') . ' #' . $transfer->unique_id)
@section('back', route('user.transfers.index'))
@section('content')
    @if (isExpiry($transfer->expiry_at) && $transfer->status)
        <div class="alert alert-danger">
            <strong>{{ lang('Transfer has been expired', 'user') }}</strong>
        </div>
    @endif
    @if (!$transfer->status)
        <div class="alert alert-danger">
            <p class="mb-0"><strong>{{ lang('Transfer has been canceled', 'user') }}</strong></p>
            @if ($transfer->cancellation_reason)
                <p class="mb-0 mt-1"><i class="fas fa-quote-left me-2"></i><i>{{ $transfer->cancellation_reason }}</i>
                </p>
            @endif
        </div>
    @endif
    <div class="transfer">
        @if (!is_null($transfer->subject) || !is_null($transfer->message))
            <div class="custom-list card shadow-sm border-0 mb-3">
                <ul class="list-group list-group-flush">
                    @if (!is_null($transfer->subject))
                        <li class="list-group-item">
                            <span><i class="fas fa-quote-left me-2"></i>{{ $transfer->subject }}</span>
                        </li>
                    @endif
                    @if (!is_null($transfer->message))
                        <li class="list-group-item">
                            <span>{{ $transfer->message }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
        <div class="row g-3">
            <div class="{{ isExpiry($transfer->expiry_at) || !$transfer->status ? 'col-lg-12' : 'col-lg-7' }}">
                <div class="custom-list card shadow-sm border-0 h-100">
                    <div class="card-header">{{ lang('Transfer details', 'user') }}</div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Transfer number', 'user') }}</strong></span>
                            <span>#{{ $transfer->unique_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Sender email', 'user') }}</strong></span>
                            <span>{{ $transfer->sender_email }}</span>
                        </li>
                        @if ($transfer->sender_name)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>{{ lang('Sender name', 'user') }}</strong></span>
                                <span>{{ $transfer->sender_name }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Transfer method', 'user') }}</strong></span>
                            <span>
                                @if ($transfer->type == 1)
                                    <span><i
                                            class="fa fa-envelope me-2"></i>{{ lang('Transferred by email', 'user') }}</span>
                                @elseif($transfer->type == 2)
                                    <span><i class="fa fa-link me-2"></i>{{ lang('Transferred by link', 'user') }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Status', 'user') }}</strong></span>
                            <span>
                                @if ($transfer->status)
                                    <span class="badge bg-success">{{ lang('Transferred', 'user') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ lang('Canceled', 'user') }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Transferred at', 'user') }}</strong></span>
                            <span>{{ vDate($transfer->created_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Last update', 'user') }}</strong></span>
                            <span>{{ vDate($transfer->updated_at) }}</span>
                        </li>
                        @if ($transfer->downloaded_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><strong>{{ lang('Downloaded at', 'user') }}</strong></span>
                                <span>{{ vDate($transfer->downloaded_at) }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><strong>{{ lang('Expiring at', 'user') }}</strong></span>
                            <span class="{{ $transfer->expiry_at ? expiry($transfer->expiry_at) : 'no-expiry-date' }}">
                                {!! $transfer->expiry_at ? vDate($transfer->expiry_at) : '<i>' . lang('Unlimited time', 'user') . '</i>' !!}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @if (!isExpiry($transfer->expiry_at) && $transfer->status)
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header">
                            <span><i class="fa fa-link me-2"></i>{{ lang('Transfer link', 'user') }}</span>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input id="input-link" type="text" class="form-control"
                                    value="{{ route('transfer.download.index', $transfer->link) }}" readonly>
                                <button id="copy-btn" class="btn btn-primary" data-clipboard-target="#input-link"><i
                                        class="far fa-clone me-2"></i>{{ lang('Copy', 'user') }}</button>
                            </div>
                            <hr>
                            <a href="{{ route('transfer.download.index', $transfer->link) }}" target="_blank"
                                class="btn btn-secondary btn-lg w-100"><i
                                    class="fas fa-external-link-alt me-2"></i>{{ lang('View Transfer', 'user') }}</a>
                        </div>
                    </div>
                </div>
            @endif
            @if (!isExpiry($transfer->expiry_at) && $transfer->status && count($transfer->transferFiles) > 0)
                <div class="{{ is_null($transfer->emails) ? 'col-lg-12' : 'col-lg-6' }}">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-secondary border-bottom-0">
                            <span><i class="fas fa-folder-open me-2"></i>{{ lang('Transferred files', 'user') }}
                                ({{ count($transfer->transferFiles) }})</span>
                        </div>
                        <div class="card-body">
                            <div class="list">
                                @foreach ($transfer->transferFiles as $transferFile)
                                    <div class="list-item d-flex justify-content-between">
                                        <span class="d-none d-xxl-inline">
                                            <a href="{{ route('user.transfers.downloadFiles', [$transfer->unique_id, hashid($transferFile->id)]) }}"
                                                class="text-dark"><i
                                                    class="fas fa-file-alt me-2"></i>{{ shortertext($transferFile->name, 40) }}</a>
                                            <span class="text-muted">({{ formatBytes($transferFile->size) }})</span>
                                        </span>
                                        <span class="d-block d-xxl-none">
                                            <i
                                                class="fas fa-file-alt me-2"></i>{{ shortertext($transferFile->name, 25) }}
                                        </span>
                                        <span>
                                            <span class="me-3 text-muted"><a
                                                    href="{{ route('user.transfers.downloadFiles', [$transfer->unique_id, hashid($transferFile->id)]) }}"><i
                                                        class="fas fa-download me-1"></i></a>({{ $transferFile->downloads }})</span>
                                            @if (!isExpiry($transfer->expiry_at) && $transfer->transfer_files_count > 1)
                                                <form class="d-inline"
                                                    action="{{ route('user.transfers.deletefiles', [$transfer->unique_id, hashid($transferFile->id)]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        class="vr__confirm__action__form btn btn-link p-0 text-danger"><i
                                                            class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @else
                                                <button class="vr__confirm__action__form btn btn-link p-0 text-danger"
                                                    disabled><i class="fas fa-trash-alt"></i></button>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (!is_null($transfer->emails))
                <div
                    class="{{ !isExpiry($transfer->expiry_at) && $transfer->status && count($transfer->transferFiles) > 0 ? 'col-lg-6' : 'col-lg-12' }}">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-secondary">
                            <span><i class="fas fa-at me-2"></i>{{ lang('Emails', 'user') }}</span>
                        </div>
                        <div class="card-body">
                            <div class="list">
                                @foreach ($transfer->emails as $emailKey => $emailValue)
                                    <div class="list-item d-flex justify-content-between">
                                        <span><i
                                                class="fa fa-envelope me-2"></i>{{ shortertext($emailValue, 50) }}</span>
                                        <span class="text-success"><i class="fa fa-check"></i></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if (!isExpiry($transfer->expiry_at) && $transfer->status)
        <div class="modal fade" id="transferSettingsModal" tabindex="-1" aria-labelledby="transferSettingsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="transferSettingsModalLabel">
                            <span class="me-1">{{ lang('Transfer settings', 'user') }}</span>
                            {!! !subscription()->plan->transfer_notify && !subscription()->plan->transfer_password ? '<a href="' . route('user.plans') . '" class="btn btn-blue btn-sm"><i class="far fa-gem me-2"></i>' . lang('Upgrade', 'user') . '</a>' : '' !!}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <form id="transferSettingsFprm"
                            action="{{ route('user.transfers.update', $transfer->unique_id) }}" method="POST">
                            @csrf
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item p-3">
                                    <div class="form-toggle">
                                        <input type="checkbox" name="download_notify"
                                            class="download-notify file-noti-download"
                                            {{ subscription()->plan->transfer_notify ? '' : 'disabled' }}
                                            {{ $transfer->download_notify ? 'checked' : '' }}>
                                        <div class="toggle-style order-2"></div>
                                        <label class="order-1"><span
                                                class="me-1">{{ lang('Notify me when downloaded', 'upload zone') }}</span>
                                            @if (!subscription()->plan->transfer_notify)
                                                <span class="badge"
                                                    style="background: {{ featureFirstPlanDetails('transfer_notify')->color }};">{{ featureFirstPlanDetails('transfer_notify')->name }}</span>
                                            @endif
                                        </label>
                                    </div>
                                </li>
                                <li class="list-group-item p-3">
                                    <div class="form-toggle">
                                        <input type="checkbox" name="expiry_notify" class="expiry-notify file-noti-expiry"
                                            {{ subscription()->plan->transfer_notify ? '' : 'disabled' }}
                                            {{ $transfer->expiry_notify ? 'checked' : '' }}>
                                        <div class="toggle-style order-2"></div>
                                        <label class="order-1"><span
                                                class="me-1">{{ lang('Notify me when expired', 'upload zone') }}</span>
                                            @if (!subscription()->plan->transfer_notify)
                                                <span class="badge"
                                                    style="background: {{ featureFirstPlanDetails('transfer_notify')->color }};">{{ featureFirstPlanDetails('transfer_notify')->name }}</span>
                                            @endif
                                        </label>
                                    </div>
                                </li>
                                <li class="list-group-item p-3">
                                    <label
                                        class="form-label {{ subscription()->plan->transfer_password ? '' : 'disabled' }}">
                                        <span class="me-1">
                                            <span>{{ lang('Transfer password', 'user') }} :</span>
                                            <span>
                                                {!! $transfer->password ? '<span class="badge bg-success">' . lang('On', 'user') . '<span>' : '<span class="badge bg-danger">' . lang('Off', 'user') . '<span>' !!}
                                            </span>
                                        </span>
                                        @if (!subscription()->plan->transfer_password)
                                            <span class="badge"
                                                style="background: {{ featureFirstPlanDetails('transfer_password')->color }};">{{ featureFirstPlanDetails('transfer_password')->name }}</span>
                                        @endif
                                    </label>
                                    <input type="password" name="transfer_password" class="form-control"
                                        placeholder="{{ lang('Transfer password', 'user') }}"
                                        {{ subscription()->plan->transfer_password ? '' : 'disabled' }}>
                                    <small
                                        class="text-muted">{{ lang('Leave it empty to remove password', 'user') }}</small>
                                </li>
                            </ul>
                        </form>
                    </div>
                    <div class="modal-footer p-2">
                        <button form="transferSettingsFprm" class="btn btn-primary"
                            {{ !subscription()->plan->transfer_notify && !subscription()->plan->transfer_password ? 'disabled' : '' }}>{{ lang('Save changes', 'user') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
    @endpush
@endsection
