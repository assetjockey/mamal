/* Helpers */
const get_dynamic_var = string => {
    return `__${pixel_key}_${string}`;
};

const get_random_id = () => {
    return crypto.randomUUID().replace(/-/g, "");
};

const clear_optout_storage = () => {
    let pixel_key_prefix = `__${pixel_key}_`;
    let pixel_optout_key = get_dynamic_var('pixel_optout');

    /* Remove old tracking keys */
    for(let index = localStorage.length, key; index--;) {
        key = localStorage.key(index);

        if(key && key.startsWith(pixel_key_prefix) && key != pixel_optout_key) {
            localStorage.removeItem(key);
        }
    }
};

const is_optout = () => {
    let pixel_optout = (new URL(document.location)).searchParams.get('pixel_optout');

    /* Update opt out status */
    if(pixel_optout !== null) {
        localStorage.setItem(get_dynamic_var('pixel_optout'), pixel_optout == 'true');
    }

    pixel_optout = localStorage.getItem(get_dynamic_var('pixel_optout')) == 'true';

    if(pixel_optout) {
        clear_optout_storage();
    }

    return pixel_optout;
};

const get_device_type = () => {

    let android = /(?:phone|windows\s+phone|ipod|blackberry|(?:android|bb\d+|meego|silk|googlebot) .+? mobile|palm|windows\s+ce|opera mini|avantgo|mobilesafari|docomo)/gi;

    let tablet = /(?:ipad|playbook|(?:android|bb\d+|meego|silk)(?! .+? mobile))/gi;

    return android.test(navigator.userAgent) ? 'mobile' : tablet.test(navigator.userAgent) ? 'tablet' : 'desktop';
};

const get_url_no_scheme = () => {
    return window.location.href.replace(/^(https?:\/\/)?(www\.)?/i, '')
};

/* Sending data via ES6 Fetch */
const send_data_fetch = async data => {
    try {
        data['url'] = window.location.href;

        let form_data = new FormData();
        form_data.append('data', JSON.stringify(data));

        let request = await fetch(`${pixel_url_base}pixel-track/${pixel_key}`, {
            method: 'POST',
            body: form_data
        })

        let response = await request.text()

        return response == '' ? response : JSON.parse(response);

    } catch (error) {
        console.log(`Analytics pixel:`, error);
    }
}

/* Sending data without expecting return answer */
const send_data_beacon = data => {
    try {
        data['url'] = window.location.href;

        let form_data = new FormData();
        form_data.append('data', JSON.stringify(data));

        navigator.sendBeacon(`${pixel_url_base}pixel-track/${pixel_key}`, form_data);
    } catch (error) {
        console.log(`Analytics pixel:`, error);
    }
};
