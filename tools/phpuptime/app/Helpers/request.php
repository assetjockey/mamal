<?php

/**
 * Get and cache the proxy.
 */
function getRequestProxy(): ?string
{
    static $proxy = null;

    if ($proxy !== null) {
        return $proxy;
    }

    if (empty(config('settings.request_proxy'))) {
        return null;
    }

    $proxies = preg_split('/[\r\n]+/', config('settings.request_proxy'), -1, PREG_SPLIT_NO_EMPTY);
    $proxy = $proxies[array_rand($proxies)];

    return $proxy;
}

/**
 * Get the IP used to make the request.
 */
function getRequestIp(): string
{
    if (getRequestProxy()) {
        return parse_url(getRequestProxy(), PHP_URL_HOST);
    }

    return getHostIp();
}
