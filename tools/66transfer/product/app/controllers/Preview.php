<?php
namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Uploads;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

defined('ALTUMCODE') || die();

class Preview extends Controller {

    public function index() {
        $file_uuid = isset($this->params[0]) ? hex2bin($this->params[0]) : null;

        /* Get the file */
        if(!$file = db()->where('file_uuid', $file_uuid)->getOne('files')) {
            throw_404();
        }

        /* Get transfer details */
        if($file->transfer_id) {
            if(!$transfer = db()->where('transfer_id', $file->transfer_id)->getOne('transfers')) {
                throw_404();
            }
            $transfer->settings = json_decode($transfer->settings ?? '');

            /* Make sure the current user has access */
            if(
                !$transfer->settings->file_preview_is_enabled
                && ($transfer->uploader_id != md5(get_ip()))
                && (!$transfer->user_id || $transfer->user_id != $this->user->user_id)
            ) {
                throw_404();
            }

            /* Check for expiration */
            if($transfer->downloads_limit && $transfer->downloads >= $transfer->downloads_limit) {
                Alerts::add_info(l('t_transfer.expired.info_message'));
                throw_404();
            }

            if($transfer->expiration_datetime && (new \DateTime()) >= (new \DateTime($transfer->expiration_datetime))) {
                Alerts::add_info(l('t_transfer.expired.info_message'));
                throw_404();
            }

            /* Check against password access */
            $has_access =
                !$transfer->settings->password ||
                (
                    $transfer->settings->password
                    && isset($_COOKIE['transfer_password_' . $transfer->transfer_id])
                    && $_COOKIE['transfer_password_' . $transfer->transfer_id] == $transfer->settings->password
                    && session_has('transfer_password_' . $transfer->transfer_id)
                );

            if(!$has_access) {
                redirect('transfer-redirect/' . $transfer->transfer_id);
            }
        }

        /* Get the transfer request details */
        if($file->transfer_request_id) {
            if(!$transfer_request = db()->where('transfer_request_id', $file->transfer_request_id)->getOne('transfer_requests')) {
                throw_404();
            }

            /* Make sure the current user has access */
            if(
                ($transfer_request->uploader_id != md5(get_ip()))
                && (!$transfer_request->user_id || $transfer_request->user_id != $this->user->user_id)
            ) {
                throw_404();
            }

            /* Check for expiration */
            if($transfer_request->expiration_datetime && (new \DateTime()) >= (new \DateTime($transfer_request->expiration_datetime))) {
                Alerts::add_info(l('r_transfer_request.expired.info_message'));
                throw_404();
            }
        }

        /* Make sure the file is previewable */
        $file_extension = mb_strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
        if(!in_array($file_extension, explode(',', settings()->transfers->preview_file_extensions))) {
            throw_404();
        }

        /* Disable OG Image */
        if(\Altum\Plugin::is_active('dynamic-og-images') && settings()->dynamic_og_images->is_enabled) {
            \Altum\Plugin\DynamicOgImages::$should_process = false;
        }

        /* Determine file path and source */
        $is_offloaded = \Altum\Plugin::is_active('offload') && settings()->offload->uploads_url;
        $file_path = UPLOADS_PATH . 'files/' . $file->name;
        $file_size = 0;
        $mime_type = 'application/octet-stream';

        /* If not offloaded, handle local files */
        if(!$is_offloaded) {
            /* Local storage */
            if(!file_exists($file_path)) {
                throw_404();
            }

            $file_size = filesize($file_path);
            $mime_type = mime_content_type($file_path) ?: 'application/octet-stream';

            /* Handle HTTP Range */
            $start = 0;
            $end = $file_size - 1;
            $http_status = 200;

            if(isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
                $start = intval($matches[1]);
                if(isset($matches[2]) && $matches[2] !== '') {
                    $end = intval($matches[2]);
                }
                if($end > ($file_size - 1)) {
                    $end = $file_size - 1;
                }
                $http_status = 206;
            }

            $content_length = ($end - $start) + 1;

            /* Clean any previous output */
            if(ob_get_level()) {
                ob_end_clean();
            }

            /* Send headers */
            if($http_status === 206) {
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size);
            }

            header('Content-Type: ' . $mime_type);
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . $content_length);
            header('Content-Disposition: inline; filename="' . $file->original_name . '"');
            header('Cache-Control: public, max-age=3600');

            /* Open the file and seek to the correct position */
            $file_source = fopen($file_path, 'rb');
            fseek($file_source, $start);

            /* Stream the file in chunks */
            $bytes_sent = 0;
            $chunk_size = 65536;

            while(!feof($file_source) && $bytes_sent < $content_length) {
                $remaining_bytes = $content_length - $bytes_sent;
                $read_length = ($remaining_bytes > $chunk_size) ? $chunk_size : $remaining_bytes;

                $data = fread($file_source, $read_length);
                if($data === false) {
                    break;
                }

                echo $data;
                flush();
                $bytes_sent += strlen($data);
            }

            fclose($file_source);
            die();
        }

        /* S3 Handling */
        else {
            try {
                $s3 = new S3Client(get_aws_s3_config());

                /* Build the correct S3 key */
                $s3_key = UPLOADS_URL_PATH . Uploads::get_path('files') . $file->name;

                /* Detect range request from browser */
                $range_header = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : null;

                /* Prepare S3 request */
                $get_object_params = [
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => $s3_key
                ];

                if($range_header) {
                    $get_object_params['Range'] = $range_header;
                }

                /* Fetch the file from S3 */
                $result = $s3->getObject($get_object_params);

                /* Extract metadata */
                $file_size = $result['ContentLength'];
                $mime_type = $result['ContentType'] ?? '';

                if(!$mime_type || $mime_type == 'application/octet-stream') {
                    $mime_type = match($file_extension) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'bmp' => 'image/bmp',
                        'svg' => 'image/svg+xml',
                        'webp' => 'image/webp',

                        'mp4' => 'video/mp4',
                        'webm' => 'video/webm',
                        'ogv' => 'video/ogg',

                        'mp3' => 'audio/mpeg',
                        'ogg' => 'audio/ogg',
                        'wav' => 'audio/wav',

                        'pdf' => 'application/pdf',
                        'txt' => 'text/plain',
                        'csv' => 'text/csv',
                        'md' => 'text/markdown',

                        default => 'application/octet-stream',
                    };
                }

                /* Clean any previous output */
                if(ob_get_level()) {
                    ob_end_clean();
                }

                /* Send headers */
                if($range_header && isset($result['ContentRange'])) {
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Range: ' . $result['ContentRange']);
                }

                header('Content-Type: ' . $mime_type);
                header('Accept-Ranges: bytes');
                header('Content-Length: ' . $file_size);
                header('Content-Disposition: inline; filename="' . $file->original_name . '"');
                header('Cache-Control: public, max-age=3600');

                /* Output the file body directly */
                while(!$result['Body']->eof()) {
                    echo $result['Body']->read(65536);
                    flush();
                }

                die();

            } catch (AwsException $exception) {
                error_log('AWS S3 error: ' . $exception->getAwsErrorMessage());
                throw_404();
            }
        }
    }

}
