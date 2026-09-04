@extends('backend.layouts.grid')
@section('title', 'Ticket #' . $ticket->ticket_number)
@section('back', route('tickets.index'))
@section('content')
    @if ($ticket->status == 3)
        <div class="alert alert-primary">
            <strong>{{ __('Note') }} : </strong>
            {{ __('Ticket has been closed you can sent a reply to reopen it') }}
        </div>
    @endif
    <div class="card custom-card mb-2">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('admin.users.edit', $ticket->user->id) }}">
                        <img class="border rounded-circle border-2" src="{{ asset($ticket->user->avatar) }}" width="60"
                            height="60">
                    </a>
                </div>
                <div class="flex-grow-1 ms-3">
                    <a href="{{ route('admin.users.edit', $ticket->user->id) }}" class="text-dark">
                        <h5 class="mb-1">
                            {{ $ticket->user->firstname . ' ' . $ticket->user->lastname }}
                        </h5>
                        <p class="mb-0 text-muted">{{ $ticket->user->email }}</p>
                    </a>
                </div>
                <div class="flex-grow-3 ms-3">
                    @if ($ticket->status != 3)
                        <a href="{{ route('tickets.close', $ticket->ticket_number) }}"
                            class="vironeer-link-confirm btn btn-danger">{{ __('Close ticket') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card custom-card mb-3">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h5 class="m-0 text-muted">{{ $ticket->subject }}</h5>
                <span>
                    <span class="me-2">{!! ticketPriority($ticket->priority) !!}</span>
                    {!! ticketStatus($ticket->status) !!}
                </span>
            </div>
        </div>
        <div id="vironeer-chat-card" class="vironeer-chat-card bg-light p-3">
            <ul class="list-unstyled">
                @foreach ($replies as $reply)
                    @if ($reply->admin_id == null)
                        <li class="vironeer-chat-message d-flex justify-content-between">
                            <img src="{{ asset($ticket->user->avatar) }}" alt="avatar"
                                class="rounded-circle d-flex align-self-start me-3 border" width="60">
                            <div class="card bg-digitalocean text-white w-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-bold">
                                            {{ $ticket->user->firstname . ' ' . $ticket->user->lastname }}
                                        </p>
                                        <p class="text-light small"><i
                                                class="far fa-clock me-1"></i>{{ $reply->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <p class="mb-0">
                                        {!! allowBr($reply->message) !!}
                                    </p>
                                    @if ($reply->supportAttachments->count() > 0)
                                        <div class="attachments mt-3">
                                            @foreach ($reply->supportAttachments as $attachment)
                                                <a href="{{ route('tickets.download', [$ticket->ticket_number, encrypt($attachment->id)]) }}"
                                                    class="btn btn-secondary btn-sm mb-2 me-1"><i
                                                        class="fa fa-download me-1"></i>{{ __('Attachment') }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @else
                        <li class="vironeer-chat-message d-flex justify-content-between">
                            <div class="card w-100 bg-white text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted small"><i
                                                class="far fa-clock me-1"></i>{{ $reply->created_at->diffForHumans() }}
                                        </p>
                                        <p class="fw-bold">
                                            {{ $reply->admin->firstname . ' ' . $reply->admin->lastname }}
                                        </p>
                                    </div>
                                    <p class="mb-0">
                                        {!! allowBr($reply->message) !!}
                                    </p>
                                    @if ($reply->supportAttachments->count() > 0)
                                        <div class="attachments mt-3">
                                            @foreach ($reply->supportAttachments as $attachment)
                                                <a href="{{ route('tickets.download', [$ticket->ticket_number, encrypt($attachment->id)]) }}"
                                                    class="btn btn-dark btn-sm mb-2 me-1"><i
                                                        class="fa fa-download me-1"></i>{{ __('Attachment') }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <img src="{{ asset($reply->admin->avatar) }}" alt="avatar"
                                class="rounded-circle d-flex align-self-start ms-3 border" width="60">
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card custom-card">
        <div class="card-body">
            <form action="{{ route('tickets.reply', $ticket->ticket_number) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ __('Reply message') }} : <span
                            class="red">*</span></label>
                    <textarea name="message" rows="8" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Files') }} : </label>
                    <div class="input-group">
                        <input type="file" name="attachments[]" class="form-control"
                            accept="image/png, image/jpeg, image/jpg, application/pdf">
                        <button class="btn btn-success" type="button" id="vironeer-addfiles-btn"><i
                                class="fa fa-plus"></i></button>
                    </div>
                    <div id="vironeer-showFiles-input"></div>
                    <div class="alert alert-warning mt-3">
                        <strong>{{ __('Supported types :') }}</strong> {{ __('JPG, JPEG, PNG, PDF') }}
                    </div>
                </div>
                <button class="btn btn-primary">{{ __('Send') }}</button>
            </form>
        </div>
    </div>
@endsection
