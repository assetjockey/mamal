<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

namespace Altum\Controllers;

use Altum\Uploads;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

defined('ALTUMCODE') || die();

class View extends Controller {

    public function index() {

        $keys_map = [
            'payment-proof' => 'offline_payment_proofs',

            /* Per product */
            'payment-processors-offline-payment-proof' => 'payment_processors_offline_payment_proofs',
        ];

        $type = isset($this->params[0]) ? input_clean($this->params[0]) : null;
        $file_name = isset($this->params[1]) ? basename(input_clean($this->params[1])) : null;
        $force_download = isset($_GET['download']) && $_GET['download'] == 1;

        if(!$file_name || !$type || !array_key_exists($type, $keys_map)) {
            throw_404();
        }

        /* Uploads key */
        $uploads_key = $keys_map[$type];

        /* Make sure the file extension is allowed */
        $file_extension = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if(!in_array($file_extension, Uploads::get_whitelisted_file_extensions($uploads_key))) {
            throw_404();
        }

        /* Verify access properly if needed */
        switch ($uploads_key) {
            case 'offline_payment_proofs':

                if(!is_logged_in()) {
                    throw_404();
                }

                /* Admins have access */
                if($this->user->type != 1) {

                    /* Verify payment user access */
                    if(!db()->where('user_id', $this->user->user_id)->where('payment_proof', $file_name)->has('payments')) {
                        throw_404();
                    }

                }

                break;

            case 'payment_processors_offline_payment_proofs':

                if(!is_logged_in()) {
                    throw_404();
                }

                /* Admins have access */
                if($this->user->type != 1) {

                    /* Verify payment user access */
                    if(!db()->where('user_id', $this->user->user_id)->where("JSON_CONTAINS(`data`, '" . json_encode($file_name) . "', '$.payment_proof')")->has('guests_payments')) {
                        throw_404();
                    }

                }

                break;
        }

        /* File path */
        $file_path = Uploads::get_full_path($uploads_key) . $file_name;

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Download or not */
        $disposition = $force_download ? 'attachment' : 'inline';
        header('Content-Disposition: ' . $disposition . '; filename="' . $file_name . '"');

        /* No caching */
        header('Cache-Control: no-store');

        /* No index */
        header('X-Robots-Tag: noindex, noarchive, nosnippet');

        /* If not offloaded, handle local files */
        if(!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {
            /* Local storage */
            if(!file_exists($file_path)) {
                throw_404();
            }

            $file_size = filesize($file_path);
            $mime_type = mime_content_type($file_path) ?: 'application/octet-stream';

            /* Clean any previous output */
            if(ob_get_level()) {
                ob_end_clean();
            }

            /* Send headers */
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . $file_size);

            /* Output file */
            readfile($file_path);
            die();
        }

        /* S3 Handling */
        else {
            try {
                $s3 = new S3Client(get_aws_s3_config());

                /* Build the correct S3 key */
                $s3_key = Uploads::get_path($uploads_key) . $file_name;

                /* Prepare S3 request */
                $get_object_params = [
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => $s3_key
                ];

                /* Fetch the file from S3 */
                $result = $s3->getObject($get_object_params);

                /* Extract metadata */
                $file_size = $result['ContentLength'];
                $mime_type = $result['ContentType'] ?? 'application/octet-stream';

                /* Clean any previous output */
                if(ob_get_level()) {
                    ob_end_clean();
                }

                /* Send headers */
                header('Content-Type: ' . $mime_type);
                header('Content-Length: ' . $file_size);

                /* Output the file body directly */
                echo $result['Body'];
                die();

            } catch (AwsException $exception) {
                error_log('AWS S3 error: ' . $exception->getAwsErrorMessage());
                throw_404();
            }
        }

    }

}
