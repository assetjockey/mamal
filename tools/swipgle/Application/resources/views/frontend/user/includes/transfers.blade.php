@if ($transfers->count() > 0)
    <div class="vr__dash__table">
        <div class="vr__table">
            <table>
                <thead>
                    <th>{{ lang('Transfer number', 'user') }}</th>
                    <th class="text-center">{{ lang('Transferred at', 'user') }}</th>
                    <th class="text-center">{{ lang('Expiring at', 'user') }}</th>
                    <th class="text-center">{{ lang('Transfer method', 'user') }}</th>
                    <th class="text-center">{{ lang('Downloaded', 'user') }}</th>
                    <th class="text-center">{{ lang('Status', 'user') }}</th>
                    <th class="text-center">{{ lang('Action', 'user') }}</th>
                </thead>
                <tbody>
                    @foreach ($transfers as $transfer)
                        <tr>
                            <td><a
                                    href="{{ route('user.transfers.show', $transfer->unique_id) }}">#{{ $transfer->unique_id }}</a>
                            </td>
                            <td class="text-center">{{ vDate($transfer->created_at) }}</td>
                            <td class="text-center {{ $transfer->expiry_at ? expiry($transfer->expiry_at) : '' }}">
                                {!! $transfer->expiry_at ? vDate($transfer->expiry_at) : '<i>' . lang('Unlimited time', 'user') . '<i>' !!}</td>
                            <td class="text-center">
                                @if ($transfer->type == 1)
                                    <span><i
                                            class="fa fa-envelope me-2"></i>{{ lang('Transferred by email', 'user') }}</span>
                                @elseif($transfer->type == 2)
                                    <span><i
                                            class="fa fa-link me-2"></i>{{ lang('Transferred by link', 'user') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $transfer->downloaded_at ? lang('Yes', 'user') : lang('No', 'user') }}</td>
                            <td class="text-center">
                                @if ($transfer->status)
                                    <span class="badge bg-success">{{ lang('Transferred', 'user') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ lang('Canceled', 'user') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('user.transfers.show', $transfer->unique_id) }}"
                                    class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $transfers->links() }}
    </div>
@else
    @include('frontend.user.includes.empty')
@endif
