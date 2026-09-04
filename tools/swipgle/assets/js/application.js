(function($) {
    "use strict";
    document.querySelectorAll('[data-year]').forEach(function(el) {
        el.textContent = new Date().getFullYear();
    });
    if (window.AOS) {
        AOS.init({ once: true, disable: 'mobile', });
    }
    $(document).on({
        ajaxStart: function() { JsLoadingOverlay.show(); },
        ajaxStop: function() { JsLoadingOverlay.hide(); },
    });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var dropdown = document.querySelectorAll('[data-dropdown]');
    if (dropdown != null) {
        dropdown.forEach(function(el) {
            let dropdownMenu = el.querySelector(".drop-down-menu");

            function dropdownOP() {
                if (el.getBoundingClientRect().top + dropdownMenu.offsetHeight > window.innerHeight - 60 && el.getAttribute("data-dropdown-position") !== "top") {
                    dropdownMenu.style.top = "auto";
                    dropdownMenu.style.bottom = "40px";
                } else {
                    dropdownMenu.style.top = "40px";
                    dropdownMenu.style.bottom = "auto";
                }
            }
            window.addEventListener("click", function(e) {
                if (el.contains(e.target)) {
                    el.classList.toggle('active');
                    setTimeout(function() {
                        el.classList.toggle('animated');
                    }, 0);
                } else {
                    el.classList.remove('active');
                    el.classList.remove('animated');
                }
                dropdownOP();
            });
            window.addEventListener("resize", dropdownOP);
            window.addEventListener("scroll", dropdownOP);
        });
    }
    let navBar = document.querySelector('.nav-bar');
    if (navBar) {
        let navBarOP = () => {
            if (window.scrollY > 50) {
                navBar.classList.add('active');
            } else {
                navBar.classList.remove('active');
            }
        };

        window.addEventListener('load', navBarOP);
        window.addEventListener('scroll', navBarOP);
    }
    let navBarMenu = document.querySelector('.nav-bar-menu'),
        navBarIcon = document.querySelector('.nav-bar-menu-icon');
    let navBarCloseFunc = () => {
        navBarMenu.classList.remove('active');
        document.body.classList.remove("overflow-hidden");
    };
    if (navBarMenu) {
        navBarIcon.onclick = () => {
            navBarMenu.classList.add('active');
            document.body.classList.add("overflow-hidden");
        };

        navBarMenu.querySelector('.btn-close').onclick = () => {
            navBarCloseFunc();
        };
        navBarMenu.querySelector(".overlay").onclick = () => {
            navBarCloseFunc();
        };

        navBarMenu.querySelectorAll('.nav-bar-link').forEach((el) => {
            el.onclick = () => {
                navBarCloseFunc();
            };
        });
    }
    let links = document.querySelectorAll('[data-link]');
    if (links) {
        links.forEach((el) => {
            el.onclick = (e) => {
                e.preventDefault();
                var scrollTarget = document.querySelector(el.getAttribute('data-link')).offsetTop - 65;
                window.scrollTo('0', scrollTarget);
                navBarMenu.classList.remove("active");
                document.body.classList.remove("overflow-hidden");
            };
        });
    }
    let password = document.querySelectorAll(".input-password");
    if (password) {
        password.forEach((el) => {
            let passwordBtn = el.querySelector("button"),
                passwordInput = el.querySelector("input");
            passwordBtn.onclick = (e) => {
                e.preventDefault();
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    passwordBtn.innerHTML = `<i class="fas fa-eye-slash"></i>`;
                } else {
                    passwordInput.type = "password";
                    passwordBtn.innerHTML = `<i class="fas fa-eye"></i>`;
                }
            };
        });
    }
    let clipboardBtn = document.querySelectorAll(".btn-copy");
    if (clipboardBtn) {
        clipboardBtn.forEach((el) => {
            let clipboard = new ClipboardJS(el);
            clipboard.on("success", () => {
                toastr.success(getConfig.copiedToClipboardSuccess);
            });
        });
    }
    let counter = document.querySelector("#counter");
    if (counter) {
        function counterFunc() {
            document.querySelectorAll(".odometer").forEach((el) => {
                if (counter.offsetTop + el.offsetTop < window.scrollY + window.innerHeight) {
                    setTimeout(function() {
                        el.innerHTML = el.getAttribute("data-number");
                    }, 200);
                }
            });
        }
        window.addEventListener("load", counterFunc);
        window.addEventListener("scroll", counterFunc);
    }
    let plans = document.querySelectorAll(".plans .plans-item"),
        planSwitcher = document.querySelector(".plan-switcher");
    if (planSwitcher) {
        planSwitcher.querySelectorAll(".plan-switcher-item").forEach((el, id) => {
            el.onclick = () => {
                planSwitcher.querySelectorAll(".plan-switcher-item").forEach((ele) => {
                    ele.classList.remove("active");
                });
                el.classList.add("active");
                plans.forEach((el) => {
                    el.classList.remove("active");
                });
                plans[id].classList.add("active");
            };
        });
    }
    let confirmFormBtn = $('.vr__confirm__action__form');
    confirmFormBtn.on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: getConfig.alertActionTitle,
            text: getConfig.alertActionText,
            icon: 'question',
            showCancelButton: true,
            allowOutsideClick: false,
            focusConfirm: true,
            confirmButtonText: getConfig.alertActionConfirmButton,
            confirmButtonColor: getConfig.primaryColor,
            cancelButtonText: getConfig.alertActionCancelButton,
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).parents('form')[0].submit();
            }
        })
    });
    let swiperVideos = document.querySelectorAll(".swiper-video"),
        swiperEle = document.querySelector(".swiper");
    if (swiperEle) {
        const swiper = new Swiper('.swiper', {
            autoplay: true,
            allowTouchMove: false,
            effect: 'fade',
            fadeEffect: {
                crossFade: false
            },
            on: {
                init: function() {
                    if (swiperVideos) {
                        if (this.slides[this.realIndex].classList.contains("swiper-video")) {
                            this.slides[this.realIndex].querySelector("video").play();
                        }
                    }
                },
                slideChange: function() {
                    if (swiperVideos) {
                        swiperVideos.forEach((el) => {
                            let video = el.querySelector("video");
                            video.load();
                        });
                    }
                },
                slideChangeTransitionStart: function() {
                    if (this.slides[this.realIndex].classList.contains("swiper-video")) {
                        this.slides[this.realIndex].querySelector("video").play();
                    }
                }
            }
        });
    }
    let contactForm = $('#contactForm'),
        sendMessageBtn = $('#sendMessage');
    sendMessageBtn.on('click', function(e) {
        e.preventDefault();
        let formData = contactForm.serializeArray(),
            sendUrl = getConfig.baseURL + '/' + getConfig.lang + '/contact/send';
        sendMessageBtn.prop('disabled', true);
        $.ajax({
            url: sendUrl,
            type: "POST",
            data: formData,
            dataType: 'json',
            success: function(response) {
                sendMessageBtn.prop('disabled', false);
                if ($.isEmptyObject(response.error)) {
                    contactForm.trigger("reset");
                    if (window.grecaptcha) {
                        grecaptcha.reset();
                    }
                    toastr.success(response.success);
                } else {
                    toastr.error(response.error);
                }
            }
        });
    });
    let downloadBtn = $('.download-btn'),
        downloadAllBtn = $('.download-all-btn');
    downloadBtn.on('click', function(e) {
        e.preventDefault();
        let id = $(this).data('id'),
            requestUrl = getConfig.baseURL + '/' + getConfig.lang + '/d/' + getDownloadConfig.transferIdentifier + '/single/request';
        downloadBtn.prop('disabled', true);
        $.ajax({
            url: requestUrl,
            type: "POST",
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                downloadBtn.prop('disabled', false);
                if ($.isEmptyObject(response.error)) {
                    window.location = response.download_link;
                } else {
                    toastr.error(response.error);
                }
            }
        });
    });
    downloadAllBtn.on('click', function(e) {
        e.preventDefault();
        let requestUrl = getConfig.baseURL + '/' + getConfig.lang + '/d/' + getDownloadConfig.transferIdentifier + '/all/request';
        downloadAllBtn.prop('disabled', true);
        $.ajax({
            url: requestUrl,
            type: "GET",
            dataType: 'json',
            success: function(response) {
                downloadAllBtn.prop('disabled', false);
                if ($.isEmptyObject(response.error)) {
                    window.location = response.download_link;
                } else {
                    toastr.error(response.error);
                }
            }
        });
    });
})(jQuery);