<?php

/**
 * Get the favicon URL.
 */
function faviconUrl(?string $url): string
{
    return 'https://icons.duckduckgo.com/ip3/' . (parse_url($url, PHP_URL_HOST) ?? parse_url('https://' . $url, PHP_URL_HOST)) . '.ico';
}
