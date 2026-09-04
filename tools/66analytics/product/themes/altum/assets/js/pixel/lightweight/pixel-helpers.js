/* Helpers */
const get_url_no_scheme = () => {
    return window.location.href.replace(/^(https?:\/\/)?(www\.)?/i, '')
};
