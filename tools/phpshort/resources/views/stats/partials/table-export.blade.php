<a href="{{ route($export, ['id' => $link->id] + Request::query()) }}" data-toggle="modal" data-target="#export-modal" class="btn btn-sm btn-outline-primary d-flex align-items-center" data-tooltip="true" title="{{ __('Export') }}">@include('icons.file-download', ['class' => 'fill-current w-4 h-4'])&#8203;</a>

<div class="modal fade" id="export-modal" tabindex="-1" role="dialog" aria-labelledby="export-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title" id="export-modal-label">{{ __('Export') }}</h6>
                <button type="button" class="close d-flex align-items-center justify-content-center w-12 h-14" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current w-4 h-4'])</span>
                </button>
            </div>
            <div class="modal-body">
                @if($link->user->can('dataExport', [App\Models\User::class]))
                    {{ __('Are you sure you want to export this table?') }}
                @else
                    @if(enabledPaymentProcessors())
                        @if(Auth::check() && $link->user->id == Auth::user()->id)
                            @include('shared.features.locked')
                        @else
                            @include('shared.features.unavailable')
                        @endif
                    @else
                        @include('shared.features.unavailable')
                    @endif
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                @if($link->user->can('dataExport', [App\Models\User::class]))
                    <a href="{{ route($export, ['id' => $link->id] + Request::query()) }}" target="_self" class="btn btn-primary" id="exportButton" rel="nofollow">{{ __('Export') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    'use strict';

    window.addEventListener('DOMContentLoaded', function () {
        jQuery('#exportButton').on('click', function () {
            jQuery('#export-modal').modal('hide');
        });
    });
</script>