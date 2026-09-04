<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Monitor HTTP check intervals
    |--------------------------------------------------------------------------
    |
    | These are the possible interval values (in seconds) a monitor can be
    | checked at.
    |
    */

    'http' => [60, 180, 300, 600, 900, 1800, 3600],

    /*
    |--------------------------------------------------------------------------
    | Monitor SSL check intervals
    |--------------------------------------------------------------------------
    |
    | These are the possible interval values (in days) an SSL certificate can
    | be checked at.
    |
    */

    'ssl' => [0, 1, 2, 3, 7, 14, 30, 60],

    /*
    |--------------------------------------------------------------------------
    | Monitor domain name check intervals
    |--------------------------------------------------------------------------
    |
    | These are the possible interval values (in days) a domain name can be
    | checked at.
    |
    */

    'domain' => [0, 1, 2, 3, 7, 14, 30, 60, 90, 180],
];
