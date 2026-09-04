@extends('backend.layouts.grid')
@section('title', __('Storage Providers'))
@section('section', __('Settings'))
@section('container', 'container-max-lg')
@section('back', route('admin.settings.index'))
@section('modal', __('Settings'))
@section('content')
    <div class="card">
        <table id="datatable" class="table w-100">
            <thead>
                <tr>
                    <th class="tb-w-1x">{{ __('#') }}</th>
                    <th class="tb-w-3x">{{ __('Logo') }}</th>
                    <th class="tb-w-3x">{{ __('name') }}</th>
                    <th class="tb-w-7x">{{ __('Status') }}</th>
                    <th class="tb-w-7x">{{ __('Last Update') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($storageProviders as $storageProvider)
                    <tr class="item">
                        <td>{{ $storageProvider->id }}</td>
                        <td><img src="{{ asset($storageProvider->logo) }}" height="40" width="40"></td>
                        <td>{{ $storageProvider->name }} @if (env('FILESYSTEM_DRIVER') == $storageProvider->symbol)
                                ({{ __('Default') }})
                            @endif
                        </td>
                        <td>
                            @if ($storageProvider->status)
                                <span class="badge bg-success">{{ __('Enabled') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Disabled') }}</span>
                            @endif
                        </td>
                        <td>{{ vDate($storageProvider->updated_at) }}</td>
                        <td>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                    aria-expanded="true">
                                    <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                    @if ($storageProvider->symbol != 'local')
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('admin.settings.storage.edit', $storageProvider->id) }}"><i
                                                    class="fa fa-edit me-2"></i>{{ __('Edit') }}</a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider" />
                                        </li>
                                    @endif
                                    <li>
                                        <form
                                            action="{{ route('admin.settings.storage.default', $storageProvider->id) }}"
                                            method="POST">
                                            @csrf
                                            <button class="vironeer-form-confirm dropdown-item"><i
                                                    class="fas fa-thumbtack me-2"></i>{{ __('Set As Default') }}</button>
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
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">{{ __('Storage settings') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="storageSettingsForm" action="{{ route('admin.settings.storage.updateSettings') }}"
                        method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ __('Unaccepted file types') }} :
                            </label>
                            <input id="tagsInput" type="text" name="unaccepted_file_types" class="form-control"
                                placeholder="Enter the extension" value="{{ $settings['unaccepted_file_types'] }}">
                        </div>
                        <div class="alert alert-warning mb-0">
                            {{ __('Enter the extension name without the point or any symbol.') }}
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button form="storageSettingsForm" class="btn btn-primary">{{ __('Save changes') }}</button>
                </div>
            </div>
        </div>
    </div>
    @push('styles_libs')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/tags-input/bootstrap-tagsinput.css') }}">
    @endpush
    @push('scripts_libs')
        <script src="{{ asset('assets/vendor/libs/tags-input/bootstrap-tagsinput.min.js') }}"></script>
    @endpush
    @push('scripts')
        <script>
            "use strict";
            $(function() {
                let tagsInput = $('#tagsInput');
                tagsInput.tagsinput({
                    cancelConfirmKeysOnEmpty: false
                });
                tagsInput.on('beforeItemAdd', function(event) {
                    if (!/^[a-zA-Z,]+$/.test(event.item)) {
                        event.cancel = true;
                        toastr.error('Enter name without any symbol');
                    }
                });
            });
        </script>
    @endpush
@endsection
