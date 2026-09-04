@extends('backend.layouts.grid')
@section('title', __('Transfers | Users | #') . $transfer->unique_id)
@section('back', route('admin.transfers.users.index'))
@section('content')
    @if (isExpiry($transfer->expiry_at) && $transfer->status)
        <div class="alert alert-danger">
            <strong>{{ __('Transfer has been expired') }}</strong>
        </div>
    @endif
    @if (!$transfer->status)
        <div class="alert alert-danger">
            <p class="mb-0"><strong>{{ __('Transfer has been canceled') }}</strong></p>
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
                    <div class="card-header bg-primary text-white">{{ __('Transfer details') }}</div>
                    <ul class="custom-list-group list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Transfer number') }}</strong></span>
                            <span>#{{ $transfer->unique_id }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('User') }}</strong></span>
                            <span>
                                <a href="{{ route('admin.users.edit', $transfer->user->id) }}" class="text-success">
                                    <i class="fa fa-user me-1"></i>
                                    {{ $transfer->user->firstname . ' ' . $transfer->user->lastname }}
                                </a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Sender email') }}</strong></span>
                            <span>{{ $transfer->sender_email }}</span>
                        </li>
                        @if ($transfer->sender_name)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <span><strong>{{ __('Sender name') }}</strong></span>
                                <span>{{ $transfer->sender_name }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Transfer method') }}</strong></span>
                            <span>
                                @if ($transfer->type == 1)
                                    <span><i class="fa fa-envelope me-2"></i>{{ __('Transferred by email') }}</span>
                                @elseif($transfer->type == 2)
                                    <span><i class="fa fa-link me-2"></i>{{ __('Transferred by link') }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Status') }}</strong></span>
                            <span>
                                @if ($transfer->status)
                                    <span class="badge bg-success">{{ __('Transferred') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ __('Canceled') }}</span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Notify when downloaded') }}</strong></span>
                            <span>
                                @if ($transfer->download_notify)
                                    <span class="text-success">
                                        <i class="fa fa-check-circle"></i>
                                    </span>
                                @else
                                    <span class="text-danger">
                                        <i class="fa fa-times-circle"></i>
                                    </span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Notify when expiry') }}</strong></span>
                            <span>
                                @if ($transfer->expiry_notify)
                                    <span class="text-success">
                                        <i class="fa fa-check-circle"></i>
                                    </span>
                                @else
                                    <span class="text-danger">
                                        <i class="fa fa-times-circle"></i>
                                    </span>
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Transferred at') }}</strong></span>
                            <span>{{ vDate($transfer->created_at) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Last update') }}</strong></span>
                            <span>{{ vDate($transfer->updated_at) }}</span>
                        </li>
                        @if ($transfer->downloaded_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <span><strong>{{ __('Downloaded at') }}</strong></span>
                                <span>{{ vDate($transfer->downloaded_at) }}</span>
                            </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><strong>{{ __('Expiring at') }}</strong></span>
                            <span class="{{ $transfer->expiry_at ? expiry($transfer->expiry_at) : 'no-expiry-date' }}">
                                {!! $transfer->expiry_at ? vDate($transfer->expiry_at) : '<i>' . __('Unlimited time') . '</i>' !!}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @if (!isExpiry($transfer->expiry_at) && $transfer->status)
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-primary text-white">
                            <span><i class="fa fa-link me-2"></i>{{ __('Transfer link') }}
                                {{ $transfer->password ? '(Protected by password)' : '' }}</span>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input id="input-link" type="text" class="form-control form-control-lg"
                                    value="{{ route('transfer.download.index', $transfer->link) }}" readonly>
                                <button id="copy-btn" class="btn btn-primary" data-clipboard-target="#input-link"><i
                                        class="far fa-clone me-2"></i>{{ __('Copy') }}</button>
                            </div>
                            <hr>
                            <a href="{{ route('transfer.download.index', $transfer->link) }}" target="_blank"
                                class="btn btn-secondary btn-lg w-100"><i
                                    class="fas fa-external-link-alt me-2"></i>{{ __('View Transfer') }}</a>
                        </div>
                    </div>
                </div>
            @endif
            @if (!isExpiry($transfer->expiry_at) && $transfer->status && count($transfer->transferFiles) > 0)
                <div class="{{ is_null($transfer->emails) ? 'col-lg-12' : 'col-lg-6' }}">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white border-bottom-0">
                            <span><i class="fas fa-folder-open me-2"></i>{{ __('Transferred files') }}
                                ({{ count($transfer->transferFiles) }}) /
                                ({{ formatBytes($transfer->transferFiles->sum('size')) }})</span>
                        </div>
                        <div class="card-body">
                            <div class="list">
                                @foreach ($transfer->transferFiles as $transferFile)
                                    <div class="list-item d-flex justify-content-between">
                                        <span class="d-none d-xxl-inline">
                                            <a href="{{ route('admin.transfers.users.download', [$transfer->unique_id, hashid($transferFile->id)]) }}"
                                                class="text-dark">
                                                <i
                                                    class="fas fa-file-alt me-2"></i>{{ shortertext($transferFile->name, 40) }}
                                            </a>
                                            <span class="text-muted">({{ formatBytes($transferFile->size) }})</span>
                                        </span>
                                        <span class="d-block d-xxl-none">
                                            <a href="{{ route('admin.transfers.users.download', [$transfer->unique_id, hashid($transferFile->id)]) }}"
                                                class="text-dark">
                                                <i
                                                    class="fas fa-file-alt me-2"></i>{{ shortertext($transferFile->name, 25) }}
                                            </a>
                                        </span>
                                        <span>
                                            <span class="me-3"><a
                                                    href="{{ route('admin.transfers.users.download', [$transfer->unique_id, hashid($transferFile->id)]) }}"><i
                                                        class="fas fa-download me-2"></i></a><span
                                                    class="text-muted">({{ $transferFile->downloads }})</span></span>
                                            @if (!isExpiry($transfer->expiry_at) && count($transfer->transferFiles) > 1)
                                                <form class="d-inline"
                                                    action="{{ route('admin.transfers.users.deleteFile', [$transfer->unique_id, hashid($transferFile->id)]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="vironeer-form-confirm btn btn-link p-0 text-danger"><i
                                                            class="fas fa-trash-alt"></i></button>
                                                </form>
                                            @else
                                                <button class="btn btn-link p-0 text-danger" disabled><i
                                                        class="fas fa-trash-alt"></i></button>
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
                        <div class="card-header bg-success text-white border-bottom-0">
                            <span><i class="fas fa-at me-2"></i>{{ __('Emails') }}</span>
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
    @if ($transfer->status && !isExpiry($transfer->expiry_at))
        <div class="modal fade" id="cancelTransfer" tabindex="-1" aria-labelledby="cancelTransferLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelTransferLabel">{{ __('Cancel Transfer') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>{{ __('Note ! :') }}</strong> {{ __('All transferred files will be deleted') }}
                        </div>
                        <form action="{{ route('admin.transfers.users.update', $transfer->unique_id) }}" method="POST">
                            @csrf
                            <div class="col-lg-4 mb-3">
                                <label class="form-label">{{ __('Notify Sender') }} : <span
                                        class="red">*</span></label>
                                <input type="checkbox" name="notify_sender" data-toggle="toggle" data-off="No"
                                    data-on="Yes">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">{{ __('Cancellation Reason') }} : <span
                                        class="red">*</span></label>
                                <textarea name="cancellation_reason" rows="6" class="form-control" placeholder="Max 150 character"
                                    required></textarea>
                            </div>
                            <button class="vironeer-form-confirm btn btn-danger">{{ __('Cancel') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/clipboard/clipboard.min.js') }}"></script>
    @endpush
@endsection
