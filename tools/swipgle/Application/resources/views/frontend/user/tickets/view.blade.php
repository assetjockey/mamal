@extends('frontend.user.layouts.dash')
@section('title', lang('Ticket', 'tickets') . ' #' . $ticket->ticket_number)
@section('back', route('user.tickets'))
@section('content')
    @if ($ticket->status == 3)
        <div class="note note-warning">
            <strong>{{ lang('Note', 'tickets') }} : </strong>
            {{ lang('Ticket has been closed you can sent a reply to reopen it', 'tickets') }}
        </div>
    @endif
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
        <div id="vr__chat__card" class="vr__chat__card bg-light p-3">
            <ul class="list-unstyled">
                @foreach ($replies as $reply)
                    @if ($reply->admin_id == null)
                        <li class="vr__chat__message d-flex justify-content-between">
                            <div class="custom-card card bg-white text-dark w-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <p class="text-muted small"><i
                                                class="far fa-clock me-1"></i>{{ $reply->created_at->diffForHumans() }}
                                        </p>
                                        <p class="fw-bold">
                                            {{ $ticket->user->firstname . ' ' . $ticket->user->lastname }}
                                        </p>
                                    </div>
                                    <p class="mb-0">
                                        {!! allowBr($reply->message) !!}
                                    </p>
                                    @if ($reply->supportAttachments->count() > 0)
                                        <div class="attachments mt-3">
                                            @foreach ($reply->supportAttachments as $attachment)
                                                <a href="{{ route('user.tickets.download', [$ticket->ticket_number, encrypt($attachment->id)]) }}"
                                                    class="btn btn-dark btn-sm mb-2 me-1"><i
                                                        class="fa fa-download me-1"></i>{{ lang('Attachment', 'tickets') }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <img src="{{ asset($ticket->user->avatar) }}" alt="avatar"
                                class="rounded-circle d-flex align-self-start ms-3 border" width="60">
                        </li>
                    @else
                        <li class="vr__chat__message d-flex justify-content-between">
                            <img src="{{ asset($reply->admin->avatar) }}" alt="avatar"
                                class="rounded-circle d-flex align-self-start me-3 border" width="60">
                            <div class="custom-card card w-100 bg-blue text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-bold">
                                            {{ $reply->admin->firstname . ' ' . $reply->admin->lastname }}
                                            <span class="vr__check__icon ms-1"><i class="far fa-check-circle"></i></span>
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
                                                <a href="{{ route('user.tickets.download', [$ticket->ticket_number, encrypt($attachment->id)]) }}"
                                                    class="btn btn-secondary btn-sm mb-2 me-1"><i
                                                        class="fa fa-download me-1"></i>{{ lang('Attachment', 'tickets') }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card custom-card dash__forms">
        <div class="card-body">
            <form action="{{ route('user.tickets.reply', $ticket->ticket_number) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">{{ lang('Reply message', 'tickets') }} : <span
                            class="red">*</span></label>
                    <textarea name="message" rows="8" class="form-control" required></textarea>
                </div>
                <div class="vr__dash__nice__button mb-3">
                    <label class="form-label">{{ lang('Files', 'tickets') }} :
                        <small>(<strong>{{ lang('Supported types', 'tickets') }} :</strong>
                            {{ __('JPG, JPEG, PNG, PDF') }})</small></label>
                    <div class="input-group">
                        <input type="file" name="attachments[]" class="form-control"
                            accept="image/png, image/jpeg, image/jpg, application/pdf">
                        <button class="btn btn-dark" type="button" id="vr__addfiles__btn"><i
                                class="fa fa-plus"></i></button>
                    </div>
                    <div id="vr__showFiles__input"></div>
                </div>
                <div class="vr__dash__nice__button">
                    <button class="btn btn-secondary" type="submit"><i
                            class="far fa-paper-plane me-2"></i>{{ lang('Send', 'tickets') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
