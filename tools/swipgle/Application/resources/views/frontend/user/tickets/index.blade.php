@extends('frontend.user.layouts.dash')
@section('title', lang('My Tickets', 'user'))
@section('link', route('user.tickets.create'))
@section('status_dropdown', true)
@section('search', true)
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-primary">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Opened', 'tickets') }}</p>
                        <p class="vr__counter__text mb-0">{{ countTicketsByStatus(0) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-success">
                        <i class="far fa-comment-dots"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Answered', 'tickets') }}</p>
                        <p class="vr__counter__text mb-0">{{ countTicketsByStatus(1) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-girl">
                        <i class="fas fa-reply-all"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Replied', 'tickets') }}</p>
                        <p class="vr__counter__text mb-0">{{ countTicketsByStatus(2) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vr__card">
                <div class="vr__counter">
                    <div class="vr__counter__icon bg-fire">
                        <i class="far fa-times-circle"></i>
                    </div>
                    <div class="vr__counter__meta">
                        <p class="vr__counter__title mb-0">{{ lang('Closed', 'tickets') }}</p>
                        <p class="vr__counter__text mb-0">{{ countTicketsByStatus(3) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($tickets->count() > 0)
        <div class="vr__dash__table">
            <div class="vr__table">
                <table>
                    <thead>
                        <th>{{ lang('Ticket number', 'tickets') }}</th>
                        <th>{{ lang('Subject', 'tickets') }}</th>
                        <th class="text-center">{{ lang('Priority', 'tickets') }}</th>
                        <th class="text-center">{{ lang('Status', 'tickets') }}</th>
                        <th class="text-center">{{ lang('Opened date', 'tickets') }}</th>
                        <th class="text-center">{{ lang('Action', 'tickets') }}</th>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td><a class="text-primary"
                                        href="{{ route('user.tickets.view', $ticket->ticket_number) }}"><i
                                            class="fas fa-ticket-alt me-2"></i>{{ lang('Ticket', 'tickets') }}#{{ $ticket->ticket_number }}</a>
                                </td>
                                <td><a class="text-dark" href="{{ route('user.tickets.view', $ticket->ticket_number) }}">
                                        {{ shortertext($ticket->subject, 60) }}</a>
                                </td>
                                <td class="text-center">{!! ticketPriority($ticket->priority) !!}</td>
                                <td class="text-center">{!! ticketStatus($ticket->status) !!}</td>
                                <td class="text-center">{{ vDate($ticket->created_at) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('user.tickets.view', $ticket->ticket_number) }}"
                                        class="btn btn-blue btn-sm"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $tickets->links() }}
        </div>
    @else
        @include('frontend.user.includes.empty')
    @endif
@endsection
