@extends('frontend.layouts.front')
@section('title', $SeoConfiguration->title ?? '')
@section('content')
    <div class="header-content text-center">
        <div class="container-lg">
            <div id="dropzone-wrapper" class="dropzone-wrapper">
                <div class="dropzone-index">
                    <div class="header-content-icon" data-aos="fade" data-aos-duration="1000" data-dz-click>
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <p class="h2 mb-4 text-white" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="250">
                        {{ lang('Transfer your files, easy and secure', 'home page') }}
                    </p>
                    <div class="col-lg-6 mx-auto mb-5" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="500">
                        <p class="text-white lead mb-0">
                            {{ lang('Transfer your files Up to 20GB* per transfer and have them travel around the world for free, easily and securely.', 'home page') }}
                        </p>
                    </div>
                    <div data-aos="fade-up" data-aos-duration="1000" data-aos-delay="750">
                        @if (subscription()->is_subscribed)
                            <button class="btn btn-secondary btn-md" data-dz-click>
                                <i class="fa-solid fa-paper-plane me-2"></i>
                                {{ lang('Start Transfer', 'home page') }}
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-secondary btn-md">
                                <i class="fas fa-sign-in-alt me-2"></i>{{ lang('Get Started', 'home page') }}
                            </a>
                        @endif
                    </div>
                </div>
                @if (subscription()->is_subscribed)
                    <div class="dropzone-drag" data-dz-click>
                        <div class="dropzone-drag-inner">
                            <div class="dropzone-drag-icon">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="dropzone-drag-title">
                                {{ lang('Drag and Drop Your Files to Start Transfer', 'upload zone') }}</p>
                            <p class="dropzone-drag-text">{{ lang('Or click here', 'upload zone') }}</p>
                        </div>
                    </div>
                    <div class="dropzone-uploadbox">
                        <div class="dropzone-uploadbox-upper">
                            <div class="dropzone-uploadbox-header">
                                <div class="dropzone-more" data-dz-click>
                                    <i class="fa fa-plus"></i>
                                    <div class="ms-3">
                                        <p class="dropzone-more-title">{{ lang('Add More', 'upload zone') }}</p>
                                        <p class="dropzone-more-text">
                                            {{ lang('Total Files', 'upload zone') }} <span data-dz-length></span>, <span
                                                data-dz-fullsize></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="dropzone-reset" data-dz-reset>
                                    <i class="fa-solid fa-rotate-right"></i>
                                    <p class="dropzone-reset-text">{{ lang('Reset', 'upload zone') }}</p>
                                </div>
                            </div>
                            <div class="dropzone-uploadbox-files" data-simplebar>
                                <div id="dropzone" class="dropzone"></div>
                            </div>
                            <div class="dropzone-forms">
                                @if (subscription()->plan->transfer_link)
                                    <div class="dropzone-form">
                                        <div class="dropzone-form-header">
                                            <p class="dropzone-form-title">
                                                <i class="fa fa-link"></i>
                                                {{ lang('Link', 'upload zone') }}
                                            </p>
                                            @if (subscription()->plan->transfer_password ||
                                                subscription()->plan->transfer_notify ||
                                                subscription()->plan->transfer_expiry)
                                                <div class="drop-down dropzone-form-edit" data-dropdown
                                                    data-dropdown-position="top">
                                                    <div class="drop-down-btn">
                                                        <i class="fa fa-cog"></i>
                                                    </div>
                                                    <div class="drop-down-menu">
                                                        @if (subscription()->plan->transfer_password)
                                                            <a class="drop-down-item dropzone-form-edit-item"
                                                                data-form-input="password">
                                                                <i
                                                                    class="fa-solid fa-lock"></i>{{ lang('Password', 'upload zone') }}
                                                            </a>
                                                        @endif
                                                        @if (subscription()->plan->transfer_notify)
                                                            <a class="drop-down-item dropzone-form-edit-item"
                                                                data-form-input="notifications">
                                                                <i
                                                                    class="fa-solid fa-bell"></i>{{ lang('Notifications', 'upload zone') }}
                                                            </a>
                                                        @endif
                                                        @if (subscription()->plan->transfer_expiry)
                                                            <a class="drop-down-item dropzone-form-edit-item"
                                                                data-form-input="date">
                                                                <i
                                                                    class="fa-solid fa-calendar-days"></i>{{ lang('Expiry Date', 'upload zone') }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="dropzone-form-body">
                                            <form id="generateLinkForm" data-action="{{ route('transfer.createlink') }}"
                                                method="GET" class="dropzone-form-inner-item">
                                                <div>
                                                    <label
                                                        class="form-label">{{ lang('Your Email Address', 'upload zone') }}
                                                        : <span class="red">*</span></label>
                                                    <input type="email" name="sender_email"
                                                        class="form-control form-control-md"
                                                        placeholder="{{ lang('Your Email Address', 'upload zone') }}">
                                                </div>
                                                <div class="mt-3">
                                                    <label
                                                        class="form-label">{{ lang('Subject (optional)', 'upload zone') }}
                                                        :</label>
                                                    <input type="text" name="subject"
                                                        class="form-control form-control-md"
                                                        placeholder="{{ lang('Subject (optional)', 'upload zone') }}">
                                                </div>
                                                <div class="mt-3">
                                                    <label
                                                        class="form-label">{{ lang('Custom link (optional)', 'upload zone') }}
                                                        :</label>
                                                    <input type="text" name="custom_link"
                                                        class="form-control form-control-md"
                                                        placeholder="{{ lang('Custom link (optional)', 'upload zone') }}">
                                                </div>
                                                @if (subscription()->plan->transfer_password)
                                                    <div class="password d-none mt-3">
                                                        <input type="checkbox" name="password_checkbox"
                                                            class="passwordCheck d-none" />
                                                        <label class="form-label">{{ lang('Password', 'upload zone') }}
                                                            :</label>
                                                        <div class="input-group input-icon input-password mb-3">
                                                            <input type="password" name="password"
                                                                class="form-control form-control-md"
                                                                placeholder="{{ lang('Password', 'upload zone') }}">
                                                            <button>
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (subscription()->plan->transfer_notify)
                                                    <div
                                                        class="notifications d-none form-check form-switch form-switch-lg mt-3">
                                                        <input type="checkbox" name="notification_checkbox"
                                                            class="notificationsCheck d-none" />
                                                        <div class="row row-cols-1 g-2 w-100">
                                                            <div class="col d-flex align-items-center">
                                                                <input id="linkNoti1" type="checkbox"
                                                                    name="download_notify"
                                                                    class="download-notify-input form-check-input">
                                                                <label class="form-check-label"
                                                                    for="linkNoti1">{{ lang('Notify me when downloaded', 'upload zone') }}</label>
                                                            </div>
                                                            <div class="col d-flex align-items-center">
                                                                <input id="linkNoti2" type="checkbox"
                                                                    name="expiry_notify"
                                                                    class="download-notify-input form-check-input">
                                                                <label class="form-check-label"
                                                                    for="linkNoti2">{{ lang('Notify me when expired', 'upload zone') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (subscription()->plan->transfer_expiry)
                                                    <div class="date mt-3 d-none">
                                                        <input type="checkbox" name="expiry_checkbox"
                                                            class="dateCheck d-none" />
                                                        <label class="mb-2">{{ lang('Set expiry date', 'upload zone') }}
                                                            :
                                                        </label>
                                                        <input type="datetime-local" name="expiry_at"
                                                            class="form-control form-control-md file-expiry transfer-expiry-date">
                                                    </div>
                                                @endif
                                            </form>
                                        </div>
                                    </div>
                                @endif
                                <div class="dropzone-form">
                                    <div class="dropzone-form-header">
                                        <p class="dropzone-form-title">
                                            <i class="fa fa-envelope"></i>
                                            {{ lang('Email', 'upload zone') }}
                                        </p>
                                        @if (subscription()->plan->transfer_password ||
                                            subscription()->plan->transfer_notify ||
                                            subscription()->plan->transfer_expiry)
                                            <div class="drop-down dropzone-form-edit" data-dropdown
                                                data-dropdown-position="top">
                                                <div class="drop-down-btn">
                                                    <i class="fa fa-cog"></i>
                                                </div>
                                                <div class="drop-down-menu">
                                                    @if (subscription()->plan->transfer_password)
                                                        <a class="drop-down-item dropzone-form-edit-item"
                                                            data-form-input="password">
                                                            <i
                                                                class="fa-solid fa-lock"></i>{{ lang('Password', 'upload zone') }}
                                                        </a>
                                                    @endif
                                                    @if (subscription()->plan->transfer_notify)
                                                        <a class="drop-down-item dropzone-form-edit-item"
                                                            data-form-input="notifications">
                                                            <i
                                                                class="fa-solid fa-bell"></i>{{ lang('Notifications', 'upload zone') }}
                                                        </a>
                                                    @endif
                                                    @if (subscription()->plan->transfer_expiry)
                                                        <a class="drop-down-item dropzone-form-edit-item"
                                                            data-form-input="date">
                                                            <i
                                                                class="fa-solid fa-calendar-days"></i>{{ lang('Expiry Date', 'upload zone') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="dropzone-form-body">
                                        <form id="transferByEmailForm" data-action="{{ route('transfer.sendfiles') }}"
                                            method="POST" class="dropzone-form-inner-item">
                                            <div>
                                                <label class="form-label">{{ lang('Your Email Address', 'upload zone') }}
                                                    : <span class="red">*</span></label>
                                                <input type="email" name="sender_email"
                                                    class="form-control form-control-md"
                                                    placeholder="{{ lang('Your Email Address', 'upload zone') }}">
                                            </div>
                                            <div class="mt-3">
                                                <label
                                                    class="form-label">{{ lang('Sender name (optional)', 'upload zone') }}
                                                    : <span class="red">*</span></label>
                                                <input type="text" name="sender_name"
                                                    class="form-control form-control-md"
                                                    placeholder="{{ lang('Sender name (optional)', 'upload zone') }}">
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">{{ lang('Send to', 'upload zone') }} : <span
                                                        class="red">*</span></label>
                                                <input type="email" name="send_to" class="form-control form-control-md"
                                                    id="input-tags" placeholder="{{ lang('Send to', 'upload zone') }}" />
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">{{ lang('Subject (optional)', 'upload zone') }}
                                                    :</label>
                                                <input type="text" name="subject" class="form-control form-control-md"
                                                    placeholder="{{ lang('Subject (optional)', 'upload zone') }}">
                                            </div>
                                            <div class="mt-3">
                                                <label
                                                    class="form-label">{{ lang('Your message (optional)', 'upload zone') }}
                                                    :</label>
                                                <textarea name="message" class="form-control form-control-md transfer-textarea"
                                                    placeholder="{{ lang('Your message (optional)', 'upload zone') }}" autosize></textarea>
                                            </div>
                                            @if (subscription()->plan->transfer_password)
                                                <div class="password d-none mt-3">
                                                    <input type="checkbox" name="password_checkbox"
                                                        class="passwordCheck d-none" />
                                                    <label class="form-label">{{ lang('Password', 'upload zone') }}
                                                        :</label>
                                                    <div class="input-group input-icon input-password mb-3">
                                                        <input type="password" name="password"
                                                            class="form-control form-control-md"
                                                            placeholder="{{ lang('Password', 'upload zone') }}">
                                                        <button>
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (subscription()->plan->transfer_notify)
                                                <div
                                                    class="notifications d-none form-check form-switch form-switch-lg mt-3">
                                                    <input type="checkbox" name="notification_checkbox"
                                                        class="notificationsCheck d-none" />
                                                    <div class="row row-cols-1 g-2 w-100">
                                                        <div class="col d-flex align-items-center">
                                                            <input id="emailNoti1" type="checkbox" name="download_notify"
                                                                class="download-notify-input form-check-input">
                                                            <label class="form-check-label"
                                                                for="emailNoti1">{{ lang('Notify me when downloaded', 'upload zone') }}</label>
                                                        </div>
                                                        <div class="col d-flex align-items-center">
                                                            <input id="emailNoti2" type="checkbox" name="expiry_notify"
                                                                class="download-notify-input form-check-input">
                                                            <label class="form-check-label"
                                                                for="emailNoti2">{{ lang('Notify me when expired', 'upload zone') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if (subscription()->plan->transfer_expiry)
                                                <div class="date mt-3 d-none">
                                                    <input type="checkbox" name="expiry_checkbox"
                                                        class="dateCheck d-none" />
                                                    <label class="mb-2">{{ lang('Set expiry date', 'upload zone') }} :
                                                    </label>
                                                    <input type="datetime-local" name="expiry_at"
                                                        class="form-control form-control-md file-expiry transfer-expiry-date">
                                                </div>
                                            @endif
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div id="upload-previews">
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-fileicon">
                                        <span class="vi vi-file" dz-file-extension></span>
                                    </div>
                                    <div class="dz-preview-content">
                                        <div class="dz-details">
                                            <div class="dz-details-info">
                                                <div class="dz-filename">
                                                    <div class="dz-success-mark">
                                                        <span><i class="far fa-check-circle"></i></span>
                                                    </div>
                                                    <div class="dz-error-mark">
                                                        <span><i class="far fa-times-circle"></i></span>
                                                    </div>
                                                    <span data-dz-name></span>
                                                </div>
                                                <div class="dz-meta">
                                                    <div class="dz-size" data-dz-size></div>,
                                                    <div class="dz-percent ms-1" data-dz-percent></div>
                                                </div>
                                            </div>
                                            <a class="dz-remove" data-dz-remove>
                                                <i class="fas fa-times fa-lg"></i>
                                            </a>
                                        </div>
                                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span>
                                        </div>
                                        <div class="dz-error-message"><span data-dz-errormessage></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dropzone-uploadbox-lower">
                            <div class="dropzone-form-actions">
                                @if (subscription()->plan->transfer_link)
                                    <div class="dropzone-form-action">
                                        <i class="fa fa-link"></i>
                                        {{ lang('Link', 'upload zone') }}
                                    </div>
                                @endif
                                <div class="dropzone-form-action">
                                    <i class="fa fa-envelope"></i>
                                    {{ lang('Email', 'upload zone') }}
                                </div>
                            </div>
                            <div class="dropzone-uploadbox-submit">
                                <button id="transferFiles" class="btn btn-secondary btn-md"
                                    disabled>{{ lang('Transfer', 'upload zone') }}</button>
                            </div>
                            <div class="dropzone-form-submit">
                                <button
                                    class="btn btn-outline-danger btn-md dropzone-form-cancel">{{ lang('Cancel', 'upload zone') }}</button>
                                <button
                                    class="btn btn-secondary btn-md dropzone-form-validate">{{ lang('Submit', 'upload zone') }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="dz-dragbox">
                        <div class="dz-dragbox-inner">
                            <i class="fa-solid fa-arrow-up-from-bracket fa-4x mb-4"></i>
                            <h3 class="mb-3">{{ lang('Drop File Here', 'upload zone') }}</h3>
                            <h4 class="fw-light">
                                {{ lang('Upload your files by drag-and-dropping them on this window', 'upload zone') }}
                            </h4>
                        </div>
                    </div>
                @endif
            </div>
            @if (subscription()->is_subscribed)
                <div class="transfer-completed-card file-container animation-zoomIn d-none">
                    <div class="card-v">
                        <div class="card-v-body">
                            <div class="upload-complete">
                                <div class="upload-complete-icon">
                                    <i class="fas fa-circle-check"></i>
                                </div>
                                <p class="upload-complete-title">{{ lang('Transfer Completed', 'upload zone') }}</p>
                                <p class="upload-complete-text">
                                    {{ lang('Your files have been transferred successfully, here is your download link', 'upload zone') }}.
                                </p>
                                <div class="mt-3">
                                    <div class="form-button">
                                        <input id="linkInput" type="text"
                                            class="transfer-link form-control form-control-md" value="" readonly>
                                        <button class="btn-copy" data-clipboard-target="#linkInput">
                                            <i class="fa-regular fa-clone"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button
                                        class="btn btn-secondary btn-md w-100 new-transfer-btn">{{ lang('New Transfer', 'upload zone') }}</button>
                                    <a href=""
                                        class="btn btn-outline-primary btn-md w-100 mt-3 view-transfer-btn">{{ lang('View Transfer', 'upload zone') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if (subscription()->is_subscribed)
        @push('config')
            @php
                $maxTransferSizeError = str_replace('{maxTransferSize}', subscription()->plan->transfer_size ?? 0, lang('Max size per transfer : {maxTransferSize}.', 'upload zone'));
                $userRemainingStorageSpace = subscription()->is_subscribed ? subscription()->storage->remaining_space_number : 0;
                $maxTransferSize = subscription()->plan->transfer_size_number;
                $subscribed = subscription()->is_subscribed ? 1 : 0;
                $subscriptionExpired = subscription()->is_expired ? 1 : 0;
                $subscriptionCanceled = subscription()->is_canceled ? 1 : 0;
                $unsubscribedError = !is_null(subscription()->plan->id) ? lang('You have no subscription or your subscription has been expired', 'alerts') : lang('Login or create account to start transferring files', 'alerts');
                $subscriptionCanceledError = lang('Your subscription has been canceled, please contact us for more information', 'alerts');
                $transferPassword = subscription()->plan->transfer_password ? 1 : 0;
                $transferNotify = subscription()->plan->transfer_notify ? 1 : 0;
                $transferExpiry = subscription()->plan->transfer_expiry ? 1 : 0;
            @endphp
            <script>
                "use strict";
                const uploadConfig = {
                    sizesTranslation: ["{{ lang('bytes') }}", "{{ lang('KB') }}", "{{ lang('MB') }}",
                        "{{ lang('GB') }}", "{{ lang('TB') }}"
                    ],
                    sendToTranslation: "{{ lang('Send to', 'upload zone') }}",
                    subscribed: "{{ $subscribed }}",
                    subscriptionExpired: "{{ $subscriptionExpired }}",
                    subscriptionCanceled: "{{ $subscriptionCanceled }}",
                    subscriptionCanceledError: "{{ $subscriptionCanceledError }}",
                    unsubscribedError: "{{ $unsubscribedError }}",
                    userRemainingStorageSpace: "{{ $userRemainingStorageSpace }}",
                    insufficientStorageSpaceError: "{{ lang('Insufficient storage space, please check your space or upgrade your plan', 'alerts') }}",
                    maxTransferSize: "{{ $maxTransferSize }}",
                    maxTransferSizeError: "{{ $maxTransferSizeError }}",
                    transferPassword: "{{ $transferPassword }}",
                    transferNotify: "{{ $transferNotify }}",
                    transferExpiry: "{{ $transferExpiry }}",
                };
                let stringifyUploadConfig = JSON.stringify(uploadConfig),
                    getUploadConfig = JSON.parse(stringifyUploadConfig);
            </script>
            @include('frontend.includes.dropzone-options')
        @endpush
        @push('scripts_libs')
            <script src="{{ asset('assets/vendor/libs/dropzone/dropzone.min.js') }}"></script>
            <script src="{{ asset('assets/vendor/libs/tags-input/tags-input.min.js') }}"></script>
            <script src="{{ asset('assets/vendor/libs/autosize/autosize.min.js') }}"></script>
        @endpush
        @push('scripts')
            <script src="{{ asset('assets/js/handler.js') }}"></script>
        @endpush
    @endif
@endsection
