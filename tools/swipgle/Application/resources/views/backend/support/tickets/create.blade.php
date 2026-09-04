@extends('backend.layouts.form')
@section('title', __('Create new ticket'))
@section('back', route('tickets.index'))
@section('content')
    <form id="vironeer-submited-form" action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div id="vironeer-preview-card" class="custom-card card mb-3 d-none">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img class="border rounded-circle border-2" src="#" width="60" height="60">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="mb-1"></h5>
                        <p class="mb-0"></p>
                    </div>
                    <div class="flex-grow-2 ms-3">
                        <span class="badge bg-dark"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-card card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('User') }} : <span
                                    class="red">*</span></label>
                            <select id="vironeer-select-user" name="user" class="form-select select2" required>
                                <option></option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @if (old('user') == $user->id) selected @endif>
                                        {{ $user->firstname . ' ' . $user->lastname }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Notify by email') }} : </label>
                            <input type="checkbox" name="sendMail" data-toggle="toggle" data-on="{{ __('Yes') }}"
                                data-off="{{ __('No') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="mb-2">
                            <label class="form-label">{{ __('Subject') }} : <span
                                    class="red">*</span></label>
                            <input type="subject" name="subject" class="form-control" required
                                value="{{ old('subject') }}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Priority') }} : <span
                                    class="red">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="0" @if (old('priority') == 0) selected @endif>{{ __('Normal') }}</option>
                                <option value="1" @if (old('priority') == 1) selected @endif>{{ __('Low') }}</option>
                                <option value="2" @if (old('priority') == 2) selected @endif>{{ __('High') }}</option>
                                <option value="3" @if (old('priority') == 3) selected @endif>{{ __('Urgent') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Message') }} : <span class="red">*</span></label>
                    <textarea name="message" class="form-control" rows="10" required>{{ old('message') }}</textarea>
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
                </div>
                <div class="alert alert-warning mb-0">
                    <strong>{{ __('Supported types :') }}</strong> {{ __('JPG, JPEG, PNG, PDF') }}
                </div>
            </div>
        </div>
    </form>
@endsection
