/* Enable the first step */
$('#welcome').fadeIn('slow').data('is-active', true);
$('#sidebar-ul a[href="#welcome"]').addClass('active').removeClass('disabled').attr('aria-current', 'step').attr('aria-disabled', 'false');
$('#sidebar-ul a.disabled').attr('aria-disabled', 'true');

let enable_sidebar_item = section_id => {
    $(`#sidebar-ul a[href="#${section_id}"]`).removeClass('disabled').attr('aria-disabled', 'false');
};

let activate_sidebar_item = section_id => {
    $('#sidebar-ul a').removeClass('active').removeAttr('aria-current');
    $(`#sidebar-ul a[href="#${section_id}"]`).addClass('active').attr('aria-current', 'step');
};

/* Sidebar links handling */
$('a[href*="#"][class*="navigator"]').on('click', event => {
    let section_id = $(event.currentTarget).attr('href').replace('#', '');
    let sidebar_a =  $(`#sidebar-ul a[href="#${section_id}"]`);

    if($(event.currentTarget).closest('#sidebar-ul').length && sidebar_a.hasClass('disabled')) {
        event.preventDefault();
        return;
    }

    enable_sidebar_item(section_id);

    /* Make sure the user didnt click on the same tab multiple times */
    let is_active = $(`#content section[id="${section_id}"]`).data('is-active');

    if(!is_active) {
        /* Hide all sections */
        $('#content section').hide();

        /* Disable the previous active section */
        $('#content section').data('is-active', false);

        /* Display the one that was clicked and activate it */
        $(`#content section[id="${section_id}"]`).fadeIn('slow').data('is-active', true);

        /* Display the sidebar item if not already */
        sidebar_a.fadeIn('slow');

        if(!sidebar_a.hasClass('active')) {
            /* Make the new link active */
            activate_sidebar_item(section_id);
        }
    }

    event.preventDefault();
});

/* Form handling for the update */
$('#setup_form').on('submit', event => {
    event.preventDefault();

    /* Disable submit button */
    let submit_button = $(event.currentTarget).find('button[type="submit"]');

    if(submit_button.prop('disabled')) {
        return;
    }

    submit_button.addClass('disabled').prop('disabled', true);
    let text = submit_button.text();
    let loader = '<div class="spinner-grow spinner-grow-sm"></div>';
    submit_button.html(loader);

    let data = $('#setup_form').serialize();

    $.ajax({
        type: 'POST',
        url: 'update.php',
        data: data,
        success: data => {

            /* Re enable submit button */
            submit_button.removeClass('disabled').prop('disabled', false).text(text);

            if(data.status == 'error') {
                alert(data.message);
            }

            else if(data.status == 'success') {
                enable_sidebar_item('finish');
                $('#sidebar-ul a[href="#finish"]').trigger('click');

                setTimeout(() => {
                    if(typeof confetti !== 'undefined') {
                        confetti({
                            particleCount: 150,
                            spread: 90,
                            origin: {
                                y: 0.5
                            },
                        });
                    }
                }, 100);
            }
        },
        error: error => {
            alert(`There was an issue with the update: ${error.status} - ${error.responseText}`);

            /* Re enable submit button */
            submit_button.removeClass('disabled').prop('disabled', false).text(text);
        },
        dataType: 'json'
    });
});
