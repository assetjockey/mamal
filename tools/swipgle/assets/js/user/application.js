(function($) {
    "use strict";

    document.querySelectorAll("[data-year]").forEach((el) => {
        el.textContent = new Date().getFullYear();
    });
    let sidebarToggle = document.querySelector(".vr__sidebar__toggle"),
        sidebarMenu = document.querySelector(".vr__dash__sidebar"),
        dashBody = document.querySelector(".vr__dash__body"),
        expired = document.querySelector(".vr__alert");
    if (sidebarMenu) {
        sidebarToggle.onclick = () => {
            sidebarMenu.classList.toggle("toggle");
            dashBody.classList.toggle("toggle");
            if (expired) {
                expired.classList.toggle("toggle");
            }
        };
        sidebarMenu.querySelector(".vr__overlay").onclick = () => {
            sidebarMenu.classList.remove("toggle");
            dashBody.classList.remove("toggle");
            if (expired) {
                expired.classList.remove("toggle");
            }
        };
        window.addEventListener("resize", () => {
            sidebarMenu.classList.remove("toggle");
            dashBody.classList.remove("toggle");
            if (expired) {
                expired.classList.remove("toggle");
            }
        });
    }
    let searchBtn = document.querySelector(".vr__search__button");
    if (searchBtn) {
        searchBtn.onclick = () => {
            document.querySelector(".vr__dash__search").classList.toggle("show");
            document.querySelector(".vr__dash__body__content").classList.toggle("padding");
            window.addEventListener("click", (e) => {
                if (e.target.closest(".vr__dash__search") || e.target.closest(".vr__search__button")) {
                    return false;
                } else if (!e.target.closest(".vr__dash__search")) {
                    document.querySelector(".vr__dash__search").classList.remove("show");
                    document.querySelector(".vr__dash__body__content").classList.remove("padding");
                }
            });
        };
    }
    let sidebarLinkCounter = document.querySelectorAll(".vr__dash__sidebar__body .vr__counter");
    if (sidebarLinkCounter) {
        sidebarLinkCounter.forEach((el) => {
            if (el.innerHTML == 0) {
                el.classList.add("disabled");
            }
        });
    }
    let navbarLinkCounter = document.querySelectorAll(".noti__btn .noti__count");
    if (navbarLinkCounter) {
        navbarLinkCounter.forEach((el) => {
            if (el.innerHTML == 0) {
                el.classList.add("disabled");
            } else {
                el.classList.add("flashit");
            }
        });
    }
    let sideDropdown = document.querySelectorAll(".vr__dropdown");
    if (sideDropdown) {
        sideDropdown.forEach((el) => {
            let sideDropdownTitle = el.querySelector(".vr__dropdown__title"),
                sideDropdownMenu = el.querySelector(".vr__dropdown__menu");
            sideDropdownTitle.onclick = () => {
                sideDropdownTitle.classList.toggle("show");
                sideDropdownMenu.classList.toggle("show");
                const menuHeight = sideDropdownMenu.offsetHeight;
                sideDropdownMenu.style.height = "0px";
                setTimeout(() => {
                    sideDropdownMenu.style.height = `${menuHeight}px`;
                }, 0);
                setTimeout(() => {
                    sideDropdownMenu.removeAttribute("style");
                }, 300);
            };
        });
    }
    let plans = document.querySelector(".plans"),
        planSwitcher = document.querySelector(".plan-switcher");
    if (planSwitcher) {
        let planSwitcherItems = planSwitcher.querySelectorAll(".plan-switcher-item"),
            plansItems = plans.querySelectorAll(".plans-item");
        planSwitcherItems.forEach((el, id) => {
            el.onclick = () => {
                plansItems.forEach((e) => {
                    e.classList.remove("active");
                });
                planSwitcherItems.forEach((e) => {
                    e.classList.remove("active");
                });
                el.classList.add("active");
                plansItems[id].classList.add("active");
            };
        });
    }
    if (window.location.hash === "#_=_") {
        if (history.replaceState) {
            var cleanHref = window.location.href.split("#")[0];
            history.replaceState(null, null, cleanHref);
        } else {
            window.location.hash = "";
        }
    }
    let confirmActionLink = $('.vr__confirm__action');
    confirmActionLink.on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: getConfig.alertActionTitle,
            text: getConfig.alertActionText,
            icon: 'question',
            showCancelButton: true,
            allowOutsideClick: false,
            focusConfirm: false,
            confirmButtonText: getConfig.alertActionConfirmButton,
            confirmButtonColor: getConfig.primaryColor,
            cancelButtonText: getConfig.alertActionCancelButton,
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = $(this).attr('href');
            }
        })
    });

    let confirmFormBtn = $('.vr__confirm__action__form');
    confirmFormBtn.on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: getConfig.alertActionTitle,
            text: getConfig.alertActionText,
            icon: 'question',
            showCancelButton: true,
            allowOutsideClick: false,
            focusConfirm: false,
            confirmButtonText: getConfig.alertActionConfirmButton,
            confirmButtonColor: getConfig.primaryColor,
            cancelButtonText: getConfig.alertActionCancelButton,
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).parents('form')[0].submit();
            }
        })
    });


    let country = document.querySelector("#country"),
        mobile = $("#mobile"),
        mobileCode = document.querySelector("#mobile_code");

    if (country) {
        let countryOption = country.querySelector(`option[data-code="${getConfig.countryCode}"]`),
            mobileOption = mobileCode.querySelector(`option[data-code="${getConfig.countryCode}"]`);
        countryOption.selected = true;
        mobileOption.selected = true;


        country.addEventListener("change", () => {
            let mobileId = mobileCode.querySelector(`option[data-code="${country.options[country.selectedIndex].getAttribute("data-code")}"]`);
            mobileId.selected = true;
        });

        mobileCode.addEventListener("change", () => {
            let countryCode = country.querySelector(`option[data-code="${mobileCode.options[mobileCode.selectedIndex].getAttribute("data-code")}"]`);
            countryCode.selected = true;
        });

        mobile.on('propertychange input', function() {
            const input = $(this);
            input.val(input.val().replace(/[^\d]+/g, ''));
        });
    }
    let changeLanguage = $('.vr__change__language');
    changeLanguage.on('change', function() {
        let langURL = $(this).find(':selected').data('link');
        window.location.href = langURL;
    });
    let i = 1,
        maxInputs = 5,
        addFileButton = $('#vr__addfiles__btn'),
        showFileInput = $('#vr__showFiles__input');

    addFileButton.on("click", function(e) {
        e.preventDefault();
        if (i < maxInputs) {
            i++;
            showFileInput.append('<div class="input-group mt-3" id="form_plus' + i + '"> <input type="file" name="attachments[]" class="form-control" accept="image/png, image/jpeg, image/jpg, application/pdf"> <button class="btn_remove btn btn-danger" id="' + i + '" type="button"><i class="far fa-trash-alt"></i></button> </div>');
        } else {
            toastr.error(getConfig.ticketsMaxFilesError);
        }
    });

    $(document).on('click', '.btn_remove', function() {
        let buttonId = $(this).attr("id");
        i--;
        $('#form_plus' + buttonId).remove();
    });

    let chatCard = $('#vr__chat__card');
    if (chatCard.length) {
        chatCard.stop().animate({
            scrollTop: chatCard[0].scrollHeight
        });
    }

    let avatarInput = $('#change_avatar'),
        targetedImagePreview = $('#avatar_preview');
    avatarInput.on('change', function() {
        var file = true,
            readLogoURL;
        if (file) {
            readLogoURL = function(input_file) {
                if (input_file.files && input_file.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        targetedImagePreview.attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input_file.files[0]);
                }
            }
        }
        readLogoURL(this);
    });
    let otpCode = $('#vr__otp__code');
    otpCode.on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
    let clipboardBtn = document.querySelector("#copy-btn");
    if (clipboardBtn) {
        let clipboard = new ClipboardJS(clipboardBtn);
        clipboard.on('success', function(e) {
            toastr.success(getConfig.copiedToClipboardSuccess);
        });
    }
})(jQuery);