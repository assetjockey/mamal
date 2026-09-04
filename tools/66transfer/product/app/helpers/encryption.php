<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();

function encrypt_file($original_file_location, $new_file_location, $key) {
    $key = substr(hash('sha256', $key, true), 0, 32);

    $cipher = 'AES-256-CBC';
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $original_file = fopen($original_file_location, 'rb');
    $new_file = fopen($new_file_location, 'w');

    fwrite($new_file, $iv);

    while($plaintext = fread($original_file, $iv_length * 5000)) {
        $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $iv = substr($ciphertext, 0, $iv_length);
        fwrite($new_file, $ciphertext);
    }

    fclose($original_file);
    fclose($new_file);
}

function decrypt_and_output($file_stream, $key, $temp_file = null) {
    $key = substr(hash('sha256', $key, true), 0, 32);
    $cipher = 'AES-256-CBC';
    $iv_length = openssl_cipher_iv_length($cipher);

    $iv = fread($file_stream, $iv_length);

    $chunk_size = (5000 + 1) * $iv_length;
    $buffer = '';

    while(!feof($file_stream)) {
        /* Fill buffer until we have a full chunk or reach EOF */
        while(strlen($buffer) < $chunk_size && !feof($file_stream)) {
            $read = fread($file_stream, $chunk_size - strlen($buffer));
            if($read === false || $read === '') break;
            $buffer .= $read;
        }

        if(strlen($buffer) == 0) {
            break;
        }

        $plaintext = openssl_decrypt($buffer, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $iv = substr($buffer, 0, $iv_length);

        if($temp_file) {
            fwrite($temp_file, $plaintext);
        } else {
            echo $plaintext;
        }

        $buffer = '';
    }
}

function decrypt_file($original_file_location, $new_file_location, $key) {
    $key = substr(hash('sha256', $key, true), 0, 32);

    $cipher = 'AES-256-CBC';
    $iv_length = openssl_cipher_iv_length($cipher);

    $original_file = fopen($original_file_location, 'rb');
    $new_file = fopen($new_file_location, 'w');

    $iv = fread($original_file, $iv_length);

    while($ciphertext = fread($original_file, $iv_length * (5000 + 1))) {
        $plaintext = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $iv = substr($ciphertext, 0, $iv_length);
        fwrite($new_file, $plaintext);
    }

    fclose($original_file);
    fclose($new_file);
}

/* decrypt helper keeps everything in memory until 2 MiB, then spills to /tmp */
function decrypt_to_temp($encrypted_stream, $password) {
    $temp = fopen('php://temp/maxmemory:2097152', 'wb+');

    /* callback fires every time decrypt_and_output() echoes */
    ob_start(function ($chunk) use ($temp) {
        fwrite($temp, $chunk);
        return '';
    }, 4096);

    decrypt_and_output($encrypted_stream, $password);

    ob_end_flush();
    rewind($temp);
    return $temp;
}
