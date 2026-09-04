<?php

/**
 * Get the favicon URL.
 */
function faviconUrl(?string $url, ?int $size = 48): string
{
    $host = parse_url($url, PHP_URL_HOST) ?? parse_url('https://' . $url, PHP_URL_HOST);

    return match (config('settings.favicon_driver')) {
        'google' => 'https://www.google.com/s2/favicons?sz=' . $size . '&domain_url=' . $host,
        'vemetric' => 'https://favicon.vemetric.com/' . $host . '?size=' . $size,
        'faviconso' => 'https://favicon.so/' . $host,
        'faviconkit' => 'https://ico.faviconkit.net/favicon/' . $host . '?sz=' . $size,
        'faviconsnap' => 'https://faviconsnap.com/api/favicon?size=' . $size . '&url=' . $host,
        default => 'https://icons.duckduckgo.com/ip3/' . $host . '.ico',
    };
}

/**
 * Calculate the growth between two values.
 */
function calcGrowth(int|float $current, int|float $previous): int|float
{
    if ($previous == 0 || $previous == null || $current == 0) {
        return 0;
    }

    return (($current - $previous) / $previous * 100);
}

/**
 * Calculate the percentage change between two numbers.
 */
function calcPercentageChange(int|float $old, int|float $new, ?int $precision = 1): int|float
{
    if ($old == 0) {
        $old++;
        $new++;
    }

    $change = (($new - $old) / $old) * 100;

    return round($change, $precision);
}
