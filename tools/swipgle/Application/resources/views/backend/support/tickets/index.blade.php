@extends('backend.layouts.grid')
@section('title', $status . __(' tickets'))
@section('link', route('tickets.create'))
@section('content')
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vironeer-counter-box bg-primary">
                <h3 class="vironeer-counter-box-title">{{ __('Opened') }}</h3>
                <p class="vironeer-counter-box-number">{{ countTicketsByStatus(0) }}</p>
                <span class="vironeer-counter-box-icon">
                    <i class="fas fa-hourglass-half"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vironeer-counter-box bg-success">
                <h3 class="vironeer-counter-box-title">{{ __('Answered') }}</h3>
                <p class="vironeer-counter-box-number">{{ countTicketsByStatus(1) }}</p>
                <span class="vironeer-counter-box-icon">
                    <i class="far fa-comment-dots"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vironeer-counter-box bg-girl">
                <h3 class="vironeer-counter-box-title">{{ __('Replied') }}</h3>
                <p class="vironeer-counter-box-number">{{ countTicketsByStatus(2) }}</p>
                <span class="vironeer-counter-box-icon">
                    <i class="fas fa-reply-all"></i>
                </span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xxl-3">
            <div class="vironeer-counter-box bg-fire">
                <h3 class="vironeer-counter-box-title">{{ __('Closed') }}</h3>
                <p class="vironeer-counter-box-number">{{ countTicketsByStatus(3) }}</p>
                <span class="vironeer-counter-box-icon">
                    <i class="far fa-times-circle"></i>
                </span>
            </div>
        </div>
    </div>
    <div class="custom-card card">
        <div class="card-header p-3 border-bottom-small">
            <form action="{{ request()->url() }}" method="GET">
                <div class="input-group vironeer-custom-input-group">
                    <input type="text" name="search" class="form-control" placeholder="{{ __('Search on tickets...') }}"
                        value="{{ request()->input('search') ?? '' }}" required>
                    <button class="btn btn-secondary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">{{ __('Sort by') }}</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('tickets.index') }}">{{ __('All') }}</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('tickets.status', 'opened') }}">{{ __('Opened') }}</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('tickets.status', 'answered') }}">{{ __('Answered') }}</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('tickets.status', 'replied') }}">{{ __('Replied') }}</a></li>
                        <li><a class="dropdown-item"
                                href="{{ route('tickets.status', 'closed') }}">{{ __('Closed') }}</a></li>
                    </ul>
                </div>
            </form>
        </div>
        @if ($tickets->count() > 0)
            <div class="table-responsive">
                <table class="vironeer-normal-table table w-100">
                    <thead>
                        <tr>
                            <th class="tb-w-2x">{{ __('#') }}</th>
                            <th class="tb-w-3x">{{ __('Number') }}</th>
                            <th class="tb-w-20x">{{ __('Subject') }}</th>
                            <th class="tb-w-4x">{{ __('Priority') }}</th>
                            <th class="tb-w-4x">{{ __('Status') }}</th>
                            <th class="tb-w-4x">{{ __('Opened date') }}</th>
                            <th class="text-end"><i class="fas fa-sliders-h me-1"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td><a href="{{ route('tickets.show', $ticket->ticket_number) }}"><i
                                            class="fas fa-ticket-alt me-2"></i>{{ __('Ticket') }}#{{ $ticket->ticket_number }}</a>
                                </td>
                                <td><a class="text-dark"
                                        href="{{ route('tickets.show', $ticket->ticket_number) }}">{{ shortertext($ticket->subject, 60) }}</a>
                                </td>
                                <td>{!! ticketPriority($ticket->priority) !!}</td>
                                <td>{!! ticketStatus($ticket->status) !!}</td>
                                <td>{{ vDate($ticket->created_at) }}</td>
                                <td>
                                    <div class="text-end">
                                        <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                            aria-expanded="true">
                                            <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('tickets.show', $ticket->ticket_number) }}"><i
                                                        class="fa fa-eye me-2"></i>{{ __('View') }}</a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.users.edit', $ticket->user->id) }}"
                                                    target="_blank"><i
                                                        class="fa fa-user me-2"></i>{{ __('User details') }}</a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST">
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
        @else
            @include('backend.includes.empty')
        @endif
    </div>
    @if (!request()->input('search'))
        {{ $tickets->links() }}
    @endif
@endsection
