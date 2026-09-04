/* Outbound link tracking for analytics */
let track_outbound_links = (extra_parameters = {}) => {
    /* Parse the pixel URL */
    let pixel_url_object = new URL(pixel_url_base);
    let pixel_hostname = pixel_url_object.hostname;
    let pixel_path = pixel_url_object.pathname.replace(/\/$/, '');

    /* Get all anchor elements */
    let anchor_elements = document.querySelectorAll('a');

    anchor_elements.forEach(anchor_element => {
        let link_url = anchor_element.getAttribute('href');
        let link_title = anchor_element.innerText.trim() || anchor_element.getAttribute('title') || anchor_element.getAttribute('data-original-title') || anchor_element.getAttribute('aria-label');
        if (!link_url || link_url == '#') {
            return;
        }

        /* Parse the link URL, handle relative and absolute URLs */
        let temp_url_object;
        try {
            temp_url_object = new URL(link_url, window.location.origin);
        } catch (error) {
            return;
        }

        /* Check if the link is outbound */
        let is_different_domain = temp_url_object.hostname !== pixel_hostname;
        let is_same_domain_but_outside_path = (
            temp_url_object.hostname === pixel_hostname &&
            !temp_url_object.pathname.startsWith(pixel_path)
        );

        if (is_different_domain || is_same_domain_but_outside_path) {
            anchor_element.addEventListener('click', event => {
                /* Prepare outbound click data */
                let outbound_click_data = {
                    type: 'outbound_click',
                    outbound_url: temp_url_object.href,
                    outbound_title: link_title,
                    url: window.location.href,
                    ...extra_parameters,
                };
                /* Send outbound click event */
                send_data_beacon(outbound_click_data);
            });
        }
    });
};
