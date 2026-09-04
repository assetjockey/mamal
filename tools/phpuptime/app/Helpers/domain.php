<?php

/**
 * Get the local host IP.
 */
function getHostIp(): string
{
    if (config('settings.custom_server_addr')) {
        return config('settings.custom_server_addr');
    }

    $hostName = parse_url(config('app.url'), PHP_URL_HOST);

    $hostIp = gethostbyname($hostName);

    if ($hostIp !== $hostName) {
        return $hostIp;
    }

    return !empty(request()->server('SERVER_ADDR')) ? request()->server('SERVER_ADDR') : '127.0.0.1';
}
