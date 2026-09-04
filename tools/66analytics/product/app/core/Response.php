<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
 *
 * This software is proprietary software owned and licensed by AltumCode.
 * A valid license is required to use, modify, or distribute this software.
 * Unauthorized use, reproduction, modification, or distribution is prohibited.
 *
 * 🌍 Explore all AltumCode projects: https://altumcode.com/
 * 📧 Support & general inquiries: https://altumcode.com/contact
 * 📤 Download the latest version: https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 */
namespace Altum;

defined('ALTUMCODE') || die();

class Response {

    public static function json($message, $status = 'success', $details = []) {
        if(!is_array($message) && $message) $message = [$message];

        echo json_encode(
            [
                'message' 	=> $message,
                'status' 	=> $status,
                'details'	=> $details,
            ]
        );


        die();
    }

    /* jsonapi.org */
    public static function jsonapi_success($data, $meta = null, $response_code = 200, $others = null) {
        http_response_code($response_code);

        $response = [
            'data' => $data
        ];

        if($meta) {
            $response['meta'] = $meta;
        };

        if($others) {
            $response = array_merge($response, $others);
        }

        echo json_encode($response);

        die();
    }

    public static function jsonapi_error($errors, $meta = null, $response_code = 400) {
        http_response_code($response_code);

        $response = [
            'errors' => $errors
        ];

        if($meta) {
            $response['meta'] = $meta;
        };

        echo json_encode($response);

        die();
    }

}
