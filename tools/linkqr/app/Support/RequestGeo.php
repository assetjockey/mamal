<?php

namespace App\Support;

use Illuminate\Http\Request;

class RequestGeo
{
    public static function country(Request $request): string
    {
        return strtoupper(self::firstHeader($request, [
            'cf-ipcountry',
            'x-vercel-ip-country',
            'cloudfront-viewer-country',
            'x-country-code',
            'x-appengine-country',
        ]));
    }

    public static function city(Request $request): string
    {
        return self::firstHeader($request, [
            'cf-ipcity',
            'x-vercel-ip-city',
            'cloudfront-viewer-city',
            'x-appengine-city',
            'x-city',
        ]);
    }

    public static function region(Request $request): string
    {
        return self::firstHeader($request, [
            'cf-region',
            'x-vercel-ip-country-region',
            'cloudfront-viewer-country-region',
            'x-appengine-region',
            'x-region',
        ]);
    }

    public static function timezone(Request $request): string
    {
        return self::firstHeader($request, [
            'cf-timezone',
            'x-vercel-ip-timezone',
            'cloudfront-viewer-time-zone',
        ]);
    }

    protected static function firstHeader(Request $request, array $headers): string
    {
        foreach ($headers as $header) {
            $value = trim((string) $request->headers->get($header, ''));

            if ($value !== '') {
                return substr(rawurldecode($value), 0, 120);
            }
        }

        return '';
    }
}
