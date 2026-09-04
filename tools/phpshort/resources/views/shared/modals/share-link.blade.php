<div class="modal fade" id="share-modal" tabindex="-1" role="dialog" aria-labelledby="share-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h6 class="modal-title" id="share-modal-label">{{ __('Share') }}</h6>
                <button type="button" class="close d-flex align-items-center justify-content-center w-12 h-14" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="d-flex align-items-center">@include('icons.close', ['class' => 'fill-current w-4 h-4'])</span>
                </button>
            </div>
            <div class="modal-body d-flex flex-wrap">
                <div class="row m-n2">
                    <div class="col-auto p-2 d-flex align-items-center justify-content-center">
                        <img id="share-qr-code" class="h-auto max-w-full w-auto h-auto w-sm-26 h-sm-26 rounded-xl border" alt="{{ __('QR code') }}" src="">
                    </div>
                    <div class="col p-2 d-flex align-items-center">
                        <div class="row m-n2">
                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(123, 96, 251); background: rgba(123, 96, 251, 0.1);" data-tooltip="true" title="Device" id="share-native-button">@include('icons.share-alt', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(23, 162, 184); background: rgba(23, 162, 184, 0.1);" data-url-template="mailto:?subject=__text_placeholder__&body=__text_placeholder__ - __url_placeholder__" target="_self" data-tooltip="true" title="Email" data-share-button>@include('icons.email', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(40, 167, 69); background: rgba(40, 167, 69, 0.1);" data-url-template="sms:?body=__text_placeholder__ - __url_placeholder__" target="_self" data-tooltip="true" title="SMS" data-share-button>@include('icons.sms', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="background: rgba(0, 0, 0, 0.1);" data-url-template="https://x.com/intent/tweet?text=__text_placeholder__&url=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="X" data-share-button>@include('icons.x', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(8, 102, 255); background: rgba(8, 102, 255, 0.1);" data-url-template="https://www.facebook.com/sharer/sharer.php?u=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Facebook" data-share-button>@include('icons.facebook', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(37, 211, 102); background: rgba(37, 211, 102, 0.1);" data-url-template="https://wa.me/?text=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="WhatsApp" data-share-button>@include('icons.whatsapp', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(8, 102, 255); background: rgba(8, 102, 255, 0.1);" data-url-template="https://www.facebook.com/dialog/send?link=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Messenger" data-share-button>@include('icons.messenger', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="background: rgba(0, 0, 0, 0.1);" data-url-template="https://www.threads.net/intent/post?text=__text_placeholder__&url=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Threads" data-share-button>@include('icons.threads', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(0, 119, 255); background: rgba(0, 119, 255, 0.1);" data-url-template="https://vk.com/share.php?url=__url_placeholder__&title=__text_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="VK" data-share-button>@include('icons.vk', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(40, 167, 232); background: rgba(40, 167, 232, 0.1);" data-url-template="https://t.me/share/url?text=__text_placeholder__&url=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Telegram" data-share-button>@include('icons.telegram', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(255, 69, 0); background: rgba(255, 69, 0, 0.1);" data-url-template="https://www.reddit.com/submit?url=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Reddit" data-share-button>@include('icons.reddit', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(230, 0, 35); background: rgba(230, 0, 35, 0.1);" data-url-template="https://pinterest.com/pin/create/button/?url=__url_placeholder__&description=__text_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Pinterest" data-share-button>@include('icons.pinterest', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="color: rgb(10, 102, 194); background: rgba(10, 102, 194, 0.1);" data-url-template="https://www.linkedin.com/sharing/share-offsite/?url=__url_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="LinkedIn" data-share-button>@include('icons.linkedin', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>

                            <div class="col-auto p-2">
                                <a href="#" class="btn d-flex align-items-center justify-content-center w-9 h-9 p-0 rounded" style="background: rgba(0, 0, 0, 0.1);" data-url-template="https://www.tumblr.com/widgets/share/tool/preview?posttype=link&canonicalUrl=__url_placeholder__&title=__text_placeholder__" target="_blank" rel="nofollow noreferrer noopener" data-tooltip="true" title="Tumblr" data-share-button>@include('icons.tumblr', ['class' => 'w-5 h-5 fill-current'])</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 p-2">
                        <div class="input-group mb-0">
                            <input type="text" dir="ltr" name="share_link" id="i-share-link" class="form-control" value="" readonly>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-inverse" data-tooltip-copy="true" data-tooltip="true" title="{{ __('Copy') }}" data-text-copy="{{ __('Copy') }}" data-text-copied="{{ __('Copied') }}" data-clipboard="true" data-clipboard-target="#i-share-link">
                                    {{ __('Copy') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    'use strict';

    window.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-share-link]').forEach(function (element) {
            element.addEventListener('click', function (e) {
                e.preventDefault();
                let url = this.dataset.url;
                let text = this.dataset.text;

                document.querySelectorAll('[data-share-button]').forEach(function (button) {
                    let href = button.dataset.urlTemplate.replace(/__url_placeholder__/g, encodeURIComponent(url)).replace(/__text_placeholder__/g, encodeURIComponent(text));

                    button.setAttribute('href', href);
                });

                document.querySelector('#i-share-link').setAttribute('value', url);
                document.querySelector('#share-qr-code').setAttribute('src', this.dataset.qr);
                
                document.querySelector('#share-native-button').setAttribute('data-url', url);
                document.querySelector('#share-native-button').setAttribute('data-text', text);
            });
        });

        document.querySelector('#share-native-button').addEventListener('click', function () {
            let url = document.querySelector('#share-native-button').dataset.url;
            let text = document.querySelector('#share-native-button').dataset.text;

            console.log(url);
            console.log(text);

            navigator.share({
                url: url,
                title: text,
                text: text
            });
        });
    });
</script>