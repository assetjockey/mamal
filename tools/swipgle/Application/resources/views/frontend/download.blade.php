@extends('frontend.layouts.front')
@section('title', lang('Download', 'download page'))
@section('content')
    <div class="header-content text-center">
        <div class="container-lg">
            {!! ads_download_page_top() !!}
            <div class="file-container">
                @isset($password)
                    <div class="card-v">
                        <div class="card-v-body">
                            <div class="file-password">
                                <div class="file-password-icon">
                                    <i class="fa fa-lock"></i>
                                </div>
                                <p class="file-password-title">{{ lang('Password Protection', 'password page') }}</p>
                                <p class="file-password-text">
                                    {{ lang('Enter the Password to Unlock the Files', 'password page') }}</p>
                                <form action="{{ route('transfer.download.password.unlock', $transfer->link) }}" method="POST">
                                    @csrf
                                    <div class="password mt-3">
                                        <div class="input-group input-icon input-password">
                                            <input type="password" name="password" class="form-control form-control-md"
                                                placeholder="Enter Password">
                                            <button id="input-group-button-right">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit"
                                            class="btn btn-secondary btn-md w-100">{{ lang('Unlock Files', 'password page') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-v v3">
                        <div class="card-v-body">
                            <div class="upload-complete">
                                <div class="upload-complete-icon">
                                    <i class="fa-solid fa-download"></i>
                                </div>
                                <p class="upload-complete-title">
                                    {{ $transfer->subject ? $transfer->subject : lang('Download', 'download page') }}
                                </p>
                                <p class="upload-complete-text">
                                    {{ lang('Transferred files are ready for download', 'download page') }}
                                    @if ($transfer->expiry_at)
                                        <p class="mb-0"> <span
                                                class="text-muted">{{ lang('Expires on', 'download page') }}</span>
                                            {{ vDate($transfer->expiry_at) }}</p>
                                    @endif
                                </p>
                                <div class="files mt-3" data-simplebar>
                                    <div>
                                        @foreach ($transferFiles as $transferFile)
                                            <div class="file">
                                                <div class="file-icon">
                                                    {!! fileIcon($transferFile->extension, 'vi-sm') !!}
                                                </div>
                                                <div class="file-info">
                                                    <p class="file-title">{{ $transferFile->name }}</p>
                                                    <p class="file-text">{{ vDate($transferFile->created_at) }}</p>
                                                </div>
                                                <div class="file-download ms-auto">
                                                    <a href="#" class="download-btn"
                                                        data-id="{{ hashid($transferFile->id) }}">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                @if ($transfer->storageProvider->symbol == 'local')
                                    <div class="mt-3">
                                        <button
                                            class="download-all-btn btn btn-secondary btn-md w-100">{{ $transferFiles->count() > 1 ? lang('Download all', 'download page') : lang('Download file', 'download page') }}</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endisset
            </div>
            {!! ads_download_page_bottom() !!}
        </div>
    </div>
    @push('config')
        <script>
            "use strict";
            const downloadConfig = {
                transferIdentifier: "{{ $transfer->link }}",
            };
            let downloadObjects = JSON.stringify(downloadConfig),
                getDownloadConfig = JSON.parse(downloadObjects);
        </script>
    @endpush
@endsection
