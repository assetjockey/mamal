(function($, Dropzone) {
    "use strict";
    let UploadUrl = getConfig.baseURL + '/' + getConfig.lang + '/upload',
        deleteUrl = getConfig.baseURL + '/' + getConfig.lang + '/uploads/delete',
        generateLinkForm = $('#generateLinkForm'),
        transferByEmailForm = $('#transferByEmailForm'),
        uploadbox = document.querySelector(".dropzone-uploadbox");
    var uploadedDocumentMap = {}
    window.totalSize = 0;
    let previewNode = document.querySelector('#upload-previews');
    previewNode.id = "";
    let previewTemplate = previewNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);
    var dropzoneConfig = {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: UploadUrl,
            method: 'POST',
            paramName: 'file',
            filesizeBase: 1024,
            maxFilesize: null,
            maxFiles: null,
            previewTemplate: previewTemplate,
            previewsContainer: "#dropzone",
            clickable: "[data-dz-click]",
            parallelUploads: 10,
            timeout: 0,
            chunking: true,
            forceChunking: true,
            chunkSize: 10485760,
            retryChunks: true,
        },
        dropzoneConfig = Object.assign({}, dropzoneConfig, dropzoneOptions);
    Dropzone.autoDiscover = false;
    var dropzone = new Dropzone("#dropzone-wrapper", dropzoneConfig);
    let autosizeOptions = document.querySelectorAll("[autosize]");
    if (autosizeOptions) {
        autosize(autosizeOptions);
    }
    let uploadForms = document.querySelector(".dropzone-forms"),
        uploadFormActions = document.querySelector(".dropzone-form-actions"),
        uploadForm = uploadForms.querySelectorAll(".dropzone-form"),
        uploadFormSubmit = uploadbox.querySelector(".dropzone-form-submit");
    function resetForms() {
        uploadFormActions.querySelectorAll(".dropzone-form-action").forEach((action) => {
            action.classList.remove("selected");
        });
        submitBtn.prop("disabled", true);
        uploadForms.querySelectorAll("input[type='checkbox']").forEach((el) => {
            if (el.classList.contains("static-input") === false) {
                el.checked = false;
            }
        });
        uploadForms.querySelectorAll("input:not([type='checkbox'])").forEach((el) => {
            if (el.classList.contains("static-input") === false) {
                el.value = "";
            }
        });
        uploadForms.querySelectorAll(".dropzone-form").forEach((el) => {
            el.querySelectorAll(".dropzone-form-edit-item").forEach((option) => {
                option.classList.remove("active");
                el.querySelector(`.${option.getAttribute("data-form-input")}`).classList.add("d-none");
            });
        });
        uploadbox.classList.remove("show-forms");
        uploadForm.forEach((form) => {
            form.classList.remove("show");
            form.classList.remove("animation");
            form.classList.remove("selected");
        });
    }
    if (uploadForms) {
        uploadForms.querySelectorAll(".dropzone-form").forEach((el) => {
            el.querySelectorAll(".dropzone-form-edit-item").forEach((option) => {
                option.onclick = () => {
                    option.classList.toggle("active");
                    el.querySelector(`.${option.getAttribute("data-form-input")}`).classList.toggle("d-none");
                    el.querySelector(`.${option.getAttribute("data-form-input")}Check`).click();
                };
            });
        });
        uploadFormActions.querySelectorAll(".dropzone-form-action").forEach((el, id) => {
            el.onclick = () => {
                uploadFormActions.querySelectorAll(".dropzone-form-action").forEach((action) => {
                    action.classList.remove("selected");
                });
                el.classList.add("selected");
                uploadbox.classList.add("show-forms");
                uploadForm.forEach((form) => {
                    form.classList.remove("show");
                    form.classList.remove("animation");
                    form.classList.remove("selected");
                });
                uploadForm[id].classList.add("show");
                setTimeout(() => {
                    uploadForm[id].classList.add("animation");
                    uploadForm[id].classList.add("selected");
                }, 0);
            };
            uploadFormSubmit.querySelector(".dropzone-form-validate").onclick = () => {
                submitBtn.prop("disabled", false);
                uploadbox.classList.remove("show-forms");
                uploadForm.forEach((form) => {
                    form.classList.remove("show");
                    form.classList.remove("animation");
                });
            };
            uploadFormSubmit.querySelector(".dropzone-form-cancel").onclick = () => {
                resetForms();
            };
        });
    }
    let DeleteAllTags = () => {
        let tagCloseBtn = document.querySelectorAll(".tags-input-wrapper .tag a"),
            tagsContainer = document.querySelector(".tags-input-wrapper input");
        if (tagCloseBtn) {
            tagCloseBtn.forEach((el) => {
                el.click();
            });
            tagsContainer.blur();
        }
    };
    let dropzoneWrapper = $('.dropzone-wrapper'),
        transferCompletedCard = $('.transfer-completed-card'),
        transferLink = $('.transfer-link'),
        newTransferBtn = $('.new-transfer-btn'),
        viewTransferBtn = $('.view-transfer-btn'),
        transferCompletedAudio = new Audio(getConfig.baseURL + "/assets/media/transfer-completed.mp3");
    let transferDone = (response) => {
        dropzoneWrapper.addClass('animation-zoomIn');
        transferLink.val(response.transfer_download_link);
        if (response.view_transfer_link != null) {
            viewTransferBtn.attr('href', response.view_transfer_link);
            viewTransferBtn.removeClass('d-none');
        } else {
            viewTransferBtn.addClass('d-none');
        }
        dropzoneWrapper.addClass('d-none');
        transferCompletedCard.removeClass('d-none');
        document.querySelector("[autosize]").style.height = "45px";
        transferCompletedAudio.play();
    };
    let submitBtn = $("#transferFiles"),
        jqUploadBox = $('.dropzone-uploadbox');
    if (submitBtn.length) {
        submitBtn.on('click', function(e) {
            e.preventDefault();
            let submitedForm = jqUploadBox.find(".dropzone-form.selected form");
            if (submitedForm != null) {
                let transferUrl = submitedForm.data('action');
                $.ajax({
                    url: transferUrl,
                    type: "POST",
                    data: submitedForm.serializeArray(),
                    dataType: 'json',
                    success: function(response) {
                        submitBtn.prop('disabled', false);
                        if ($.isEmptyObject(response.error)) {
                            submitedForm.trigger("reset");
                            DeleteAllTags();
                            generateLinkForm.find('input[name="files[]"]').remove();
                            transferByEmailForm.find('input[name="files[]"]').remove();
                            uploadedDocumentMap = {};
                            dropzone.removeAllFiles(true);
                            resetForms();
                            transferDone(response);
                        } else {
                            toastr.error(response.error);
                        }
                    }
                });
            }
        });
    }
    newTransferBtn.on('click', function(e) {
        e.preventDefault();
        dropzoneWrapper.removeClass('d-none');
        transferCompletedCard.addClass('d-none');
    });
    let resetUpload = document.querySelectorAll("[data-dz-reset]");
    if (resetUpload) {
        resetUpload.forEach((el) => {
            el.onclick = () => {
                dropzone.removeAllFiles(true);
            };
        });
    }
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return "0 " + getUploadConfig.sizesTranslation[0];
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = getUploadConfig.sizesTranslation;
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    function uploadBoxDrag() {
        if (dropzone.files.length === 0) {
            document.querySelector(".dropzone-wrapper").classList.remove("animation");
            document.querySelector(".dropzone-wrapper").classList.remove("show-uploadbox");
            document.querySelector(".dropzone-wrapper").classList.add("show-drag");
            setTimeout(() => {
                document.querySelector(".dropzone-wrapper").classList.add("animation");
            }, 0);
            document.querySelector("[data-dz-fullsize]").textContent = 0;
        } else {
            document.querySelector(".dropzone-wrapper").classList.remove("animation");
            document.querySelector(".dropzone-wrapper").classList.remove("show-drag");
            document.querySelector(".dropzone-wrapper").classList.add("show-uploadbox");
            setTimeout(() => {
                document.querySelector(".dropzone-wrapper").classList.add("animation");
            }, 0);
            let filesSize = [];
            dropzone.files.forEach((el) => {
                filesSize.push(el.size);
                let fullSize = filesSize.reduce((a, b) => a + b, 0);
                document.querySelector("[data-dz-fullsize]").textContent = formatBytes(fullSize);
            });
        }
        document.querySelector("[data-dz-length]").textContent = dropzone.files.length; // Files Length
    }
    function onAddFile(file) {
        if (getUploadConfig.subscribed != 0 && getUploadConfig.subscriptionExpired != 1 && getUploadConfig.subscriptionCanceled != 1) {
            if (this.files.length) {
                var _i, _len;
                for (_i = 0, _len = this.files.length; _i < _len - 1; _i++) {
                    if (this.files[_i].name === file.name) {
                        this.removeFile(file);
                    }
                }
            }
            if (file.size == 0) {
                this.removeFile(file);
            }
            window.totalSize += file.size;
            if (getUploadConfig.userRemainingStorageSpace != "") {
                if (window.totalSize > getUploadConfig.userRemainingStorageSpace) {
                    toastr.error(getUploadConfig.insufficientStorageSpaceError);
                    this.removeFile(file);
                }
            }
            if (getUploadConfig.maxTransferSize != "") {
                if (window.totalSize > getUploadConfig.maxTransferSize) {
                    toastr.error(getUploadConfig.maxTransferSizeError);
                    this.removeFile(file);
                }
            }
            uploadBoxDrag();
            let fileExt = document.querySelectorAll("[dz-file-extension]");
            fileExt.forEach((el, id) => {
                if (dropzone.files[id].name.split('.').pop().length > 4) {
                    el.setAttribute("data-type", dropzone.files[id].name.split('.').pop().slice(0, 3) + "..");
                } else {
                    el.setAttribute("data-type", dropzone.files[id].name.split('.').pop());
                }
            });
        } else {
            if (getUploadConfig.subscriptionCanceled == 1) {
                toastr.error(getUploadConfig.subscriptionCanceledError);
            } else {
                toastr.error(getUploadConfig.unsubscribedError);
            }
            this.removeFile(file);
        }
    }
    function onSending(file, xhr, formData) {
        formData.append('total_size', window.totalSize);
    }
    function onUploadprogress(file, progress) {
        file.previewElement.querySelector("[data-dz-percent]").textContent = progress.toFixed(0) + "%";
    }
    function onFileError(file = null, message = null) {
        const preview = $(file.previewElement);
        preview.removeClass('dz-success');
        preview.addClass('dz-error');
        toastr.error(message + ' (' + file.name + ')');
    }
    function onRemovedfile(file) {
        uploadBoxDrag();
        if (window.totalSize >= file.size) {
            window.totalSize -= file.size;
        }
        let fileId = uploadedDocumentMap[file.upload.uuid];
        if (fileId) {
            generateLinkForm.find('input[name="files[]"][value="' + fileId + '"]').remove();
            transferByEmailForm.find('input[name="files[]"][value="' + fileId + '"]').remove();
            $.ajax({
                url: deleteUrl,
                type: "POST",
                data: { id: fileId },
                success: function(response) {
                    if (response.error) {
                        toastr.error(response.error);
                    }
                }
            });
        }
    }
    function onUploadComplete(file) {
        if (file.status == "success") {
            const preview = $(file.previewElement);
            const response = JSON.parse(file.xhr.response);
            if (response.type == 'success') {
                generateLinkForm.append('<input type="hidden" name="files[]" value="' + response.id + '" class="static-input">');
                transferByEmailForm.append('<input type="hidden" name="files[]" value="' + response.id + '" class="static-input">');
                uploadedDocumentMap[file.upload.uuid] = response.id;
            } else {
                preview.removeClass('dz-success');
                preview.addClass('dz-error');
                toastr.error(response.msg);
            }
        }
    }
    function onDragover() {
        document.querySelector(".dz-dragbox").classList.add("show");
    }
    function onDragleave() {
        document.querySelector(".dz-dragbox").classList.remove("show");
    }
    dropzone.on("addedfile", onAddFile);
    dropzone.on('sending', onSending);
    dropzone.on('uploadprogress', onUploadprogress);
    dropzone.on("removedfile", onRemovedfile);
    dropzone.on('dragover', onDragover);
    dropzone.on('dragleave', onDragleave);
    dropzone.on('drop', onDragleave);
    dropzone.on('error', onFileError);
    dropzone.on('complete', onUploadComplete);
})(jQuery, Dropzone);