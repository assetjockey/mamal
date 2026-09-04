let send_data_beacon = data => {
    try {
        let form_data = new FormData();
        form_data.append('data', JSON.stringify(data));

        navigator.sendBeacon(`${pixel_url_base}pixel-track/${pixel_key}`, form_data);
    } catch (error) {
        console.log(`Analytics pixel:`, error);
    }
};

class AltumCodeEvents {

    /* Create and initiate the class with the proper parameters */
    constructor() {

        /* Data */
        let url_params = new URLSearchParams(window.location.search);
        let query_parameters = new URL(document.location.toString()).searchParams.toString();

        let data = {
            path: window.location.pathname + (pixel_query_parameters_tracking_is_enabled && query_parameters.length ? '?' + query_parameters : ''),
            referrer: document.referrer.includes(`${location.protocol}//${location.host}${location.pathname}`) ? null : document.referrer,
            utm: {
                source: url_params.get('utm_source'),
                medium: url_params.get('utm_medium'),
                campaign: url_params.get('utm_campaign'),
            },
            resolution: {
                width: window.screen.width,
                height: window.screen.height
            },
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            theme: window?.matchMedia?.('(prefers-color-scheme:dark)')?.matches ? 'dark' : 'light'
        };

        /* Detect if unique or not */
        let referrer_url = document.createElement('a');
        referrer_url.href = document.referrer;
        let current_url = document.createElement('a');
        current_url.href = window.location.href;

        let type = document.referrer.trim() == '' || referrer_url.hostname != current_url.hostname ? 'landing_page' : 'pageview';

        /* Send the data to the server */
        send_data_beacon({
            type,
            url: window.location.href,
            data
        });

    }

}
