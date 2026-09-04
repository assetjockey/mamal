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

use Altum\Alerts;
use Altum\Response;
use Altum\Traits\Apiable;
use Altum\Uploads;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

defined('ALTUMCODE') || die();

class Files extends Controller {
    use Apiable;

    public function index() {
        throw_404();
    }

	public function download() {
		\Altum\Authentication::guard();

		$file_id = isset($this->params[0]) ? (int) $this->params[0] : null;

		/* Get the file */
		if(!$file = db()->where('file_id', $file_id)->getOne('files')) {
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
				($transfer->uploader_id != md5(get_ip()))
				&& (!$transfer->user_id || $transfer->user_id != $this->user->user_id)
			) {
				throw_404();
			}

			/* Password for decryption if needed */
			$transfer_password = $transfer->settings->password ?? '';
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

			/* Password for decryption if needed */
			$transfer_password = '';
		}

		/* Prepare headers */
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file->original_name . '"');

		/* Local storage */
		if(!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {

			if(!file_exists(UPLOADS_PATH . 'files/' . $file->name)) {
				throw_404();
			}

			$file_source = @fopen(UPLOADS_PATH . 'files/' . $file->name, 'rb');

			if($file->is_encrypted) {
				decrypt_and_output($file_source, $transfer_password);
			} else {
				while($buffer = fread($file_source, 1024 * 1024)) {
					echo $buffer;
				}
			}

			fclose($file_source);
			die();
		}

		/* Offload storage */
		try {
			$s3 = new \Aws\S3\S3Client(get_aws_s3_config());
			$s3->registerStreamWrapper();
		} catch(\Exception $exception) {
			Alerts::add_error($exception->getMessage());
			throw_404();
		}

		$file_key = UPLOADS_URL_PATH . Uploads::get_path('files') . $file->name;
		$file_path = 's3://' . settings()->offload->storage_name . '/' . $file_key;

		if(!file_exists($file_path)) {
			throw_404();
		}

		/* Encrypted offload file must be streamed + decrypted */
		if($file->is_encrypted && !settings()->transfers->is_direct_offload_upload) {
			$file_source = @fopen($file_path, 'rb');

			decrypt_and_output($file_source, $transfer_password);

			fclose($file_source);
			die();
		}

		/* Non-encrypted / direct offload file can redirect to S3 presigned URL */
		$original_filename = $file->original_name;

		$ascii_filename = preg_replace('/[^\x20-\x7E]/', '', $original_filename);
		$ascii_filename = str_replace([' ', '"', "'"], ['_', '', ''], $ascii_filename);

		$utf8_filename = rawurlencode($original_filename);

		$command = $s3->getCommand('GetObject', [
			'Bucket' => settings()->offload->storage_name,
			'Key' => $file_key,
			'ResponseContentDisposition' => 'attachment; filename="' . $ascii_filename . '"; filename*=UTF-8\'\'' . $utf8_filename
		]);

		$request = $s3->createPresignedRequest($command, '+15 minutes');

		header('Location: ' . $request->getUri(), true, 302);
		die();
	}

    private function initiate_create_api() {
        set_time_limit(0);

        if(empty($_POST)) {
            throw_404();
        }

        /* Define the return content to be treated as JSON */
        header('Content-Type: application/json');

        /* Get potential API key */
        $api_key = \Altum\Authentication::get_authorization_bearer();

        /* Check for the plan limit */
        if(is_logged_in()) {
            // :)
        }

        /* API */
        elseif($api_key) {
            $this->user = db()->where('api_key', $api_key)->where('status', 1)->getOne('users');

            if(!$this->user) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }

            $this->user->plan_settings = json_decode($this->user->plan_settings);

            if(!$this->user->plan_settings->api_is_enabled) {
                $this->response_error(l('api.error_message.no_access'), 401);
            }
        }

        /* Guest */
        else {
            if($this->user->plan_settings->transfers_limit == 0) {
                $this->response_error(l('global.info_message.plan_feature_limit'), 401);
            }
        }

        if(!$api_key) {
            if(!\Altum\Csrf::check('global_token')) {
                $this->response_error(l('global.error_message.invalid_csrf_token'), 401);
            }
        }
    }

    public function create_api_offload() {

        $this->initiate_create_api();

        if(!settings()->transfers->is_direct_offload_upload || !\Altum\Plugin::is_active('offload') || !settings()->offload->uploads_url) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        /* Finishing external upload */
        if(isset($_POST['offload_id'])) {
            $uuid = hex2bin($_POST['uuid']);

            /* Check for required fields */
            $required_fields = ['uuid', 'uploaded_chunks'];

            foreach($required_fields as $field) {
                if(!isset($_POST[$field])) {
                    $this->response_error(l('global.error_message.empty_fields'), 401);
                    break 1;
                }
            }

            /* Get the file from the database */
            if(!$file = db()->where('file_uuid', $uuid)->getOne('files')) {
                $this->response_error(l('global.error_message.basic'), 401);
            }

            if($file->status != 'uploading') {
                $this->response_error(l('global.error_message.basic'), 401);
            }

            $uploaded_chunks = json_decode($_POST['uploaded_chunks'], true);

            try {
                $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

				/* Params */
				$complete_multipart_upload_params = [
					'Bucket' => settings()->offload->storage_name,
					'Key' => UPLOADS_URL_PATH . Uploads::get_path('files') . $file->name,
					'UploadId' => $file->offload_id,
					'MultipartUpload' => [
						'Parts' => $uploaded_chunks
					],
				];

				if(!empty($_POST['file_encryption_is_enabled'])) {
					$complete_multipart_upload_params['ServerSideEncryption'] = 'AES256';
				}

                /* Generate multipart upload */
                $multipart_upload = $s3->getCommand('CompleteMultipartUpload', $complete_multipart_upload_params);

				/* 🚀 */
                $result = $s3->execute($multipart_upload);

                /* Update the file entry */
                db()->where('file_id', $file->file_id)->update('files', [
                    'status' => 'uploaded'
                ]);

                Response::jsonapi_success([]);
            } catch (\Exception $exception) {
                $this->response_error($exception->getMessage(), 401);
            }
        }

        /* Initiate external upload */

        /* Check for required fields */
        $required_fields = ['uuid', 'file_name', 'total_chunks', 'chunk_index'];

        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        /* Filter some of the variables */
        $_POST['uuid'] = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['uuid'] ?? '');
        $_POST['uuid'] = hex2bin($_POST['uuid']);
        if(!$_POST['uuid']) {
            $_POST['uuid'] = str_replace('-', '', random_bytes(16));
        }
        $_POST['password'] = !empty($_POST['password']) && $this->user->plan_settings->password_protection_is_enabled ? $_POST['password'] : null;
        $_POST['file_encryption_is_enabled'] = $this->user->plan_settings->file_encryption_is_enabled ? (bool) ($_POST['file_encryption_is_enabled'] ?? false) : false;
        $_POST['total_file_size'] = (int) $_POST['total_file_size'] ?? null;

        /* Chunking */
        $_POST['total_chunks'] = (int) $_POST['total_chunks'];
        $_POST['chunk_index'] = (int) $_POST['chunk_index'];

        /* Uploaded file processing */
        $file_name = input_clean($_POST['file_name']);
        $file_extension = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        /* Check for any errors */
        if(!strpos($file_name, '.')) {
            $this->response_error(l('global.error_message.invalid_file_type'), 401);
        }

        $blacklisted_file_extensions = explode(',', settings()->transfers->blacklisted_file_extensions);
        if(in_array($file_extension, $blacklisted_file_extensions)) {
            $this->response_error(l('global.error_message.invalid_file_type'), 401);
        }

        /* Generate random file name */
        $new_file_name = generate_readable_uuid() . '.' . $file_extension;

        try {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

            /* Params */
            $create_multipart_upload_params = [
                'Bucket' => settings()->offload->storage_name,
                'Key' => UPLOADS_URL_PATH . Uploads::get_path('files') . $new_file_name,
            ];

            if(!empty($_POST['file_encryption_is_enabled'])) {
                $create_multipart_upload_params['ServerSideEncryption'] = 'AES256';
            }

            /* Generate multipart upload */
            $multipart_upload = $s3->getCommand('CreateMultipartUpload', $create_multipart_upload_params);
            $multipart_upload_result = $s3->execute($multipart_upload);
            $multipart_upload_id = $multipart_upload_result['UploadId'];

            /* Prepare array */
            $upload_urls = [];

            /* Generate pre signed URLs */
            for($i = 1; $i <= $_POST['total_chunks']; $i++) {
                $upload_request = $s3->getCommand('UploadPart', [
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => UPLOADS_URL_PATH . Uploads::get_path('files') . $new_file_name,
                    'UploadId' => $multipart_upload_id,
                    'PartNumber' => $i
                ]);

                $presigned_request = $s3->createPresignedRequest($upload_request, '+1 hours');

                $upload_urls[] = $presigned_request->getUri();
            }

        } catch (\Exception $exception) {
            $this->response_error($exception->getMessage(), 401);
        }

        /* Database query */
        $file_id = db()->insert('files', [
            'file_uuid' => $_POST['uuid'],
            'offload_id' => $multipart_upload_id,
            'uploader_id' => md5(get_ip()),
            'user_id' => $this->user->user_id ?? null,
            'name' => $new_file_name,
            'original_name' => $file_name,
            'size' => $_POST['total_file_size'],
            'status' => 'uploading',
            'is_encrypted' => (int) $_POST['file_encryption_is_enabled'],
            'datetime' => get_date(),
        ]);

        Response::jsonapi_success([
            'offload_id' => $multipart_upload_id,
            'upload_urls' => $upload_urls,
            'file_name' => $new_file_name,
        ]);
    }

    public function create_api() {

        /* Check for exceeded post_max_size */
        if($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
            header('HTTP/1.1 413 Payload Too Large');
            die();
        }

        $this->initiate_create_api();

        /* Check for required fields */
        $required_fields = ['uuid', 'file_name', 'total_chunks', 'chunk_index'];

        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        if(empty($_FILES['file']['name'])) {
            $this->response_error(l('global.error_message.empty_fields'), 401);
        }

        /* Filter some of the variables */
        $_POST['uuid'] = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['uuid'] ?? '');
        $_POST['uuid'] = hex2bin($_POST['uuid']);
        if(!$_POST['uuid']) {
            $_POST['uuid'] = str_replace('-', '', random_bytes(16));
        }
        $_POST['password'] = !empty($_POST['password']) && $this->user->plan_settings->password_protection_is_enabled ? $_POST['password'] : null;
        $_POST['file_encryption_is_enabled'] = $this->user->plan_settings->file_encryption_is_enabled ? (bool) ($_POST['file_encryption_is_enabled'] ?? false) : false;
        $_POST['total_file_size'] = (int) $_POST['total_file_size'] ?? null;

        /* Chunking */
        $_POST['total_chunks'] = (int) $_POST['total_chunks'];
        $_POST['chunk_index'] = (int) $_POST['chunk_index'];

        /* Uploaded file processing */
        $file_name = input_clean($_POST['file_name']);
        $file_extension = mb_strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_temp = $_FILES['file']['tmp_name'];

        /* Check for any errors */
        if(!strpos($file_name, '.')) {
            $this->response_error(l('global.error_message.invalid_file_type'), 401);
        }

        $blacklisted_file_extensions = explode(',', settings()->transfers->blacklisted_file_extensions);
        if(in_array($file_extension, $blacklisted_file_extensions)) {
            $this->response_error(l('global.error_message.invalid_file_type'), 401);
        }

        /* Check for any errors */
        if($_FILES['file']['error'] == UPLOAD_ERR_INI_SIZE) {
            $this->response_error(sprintf(l('global.error_message.file_size_limit'), get_max_upload()), 401);
        }

        if($_FILES['file']['error'] && $_FILES['file']['error'] != UPLOAD_ERR_INI_SIZE) {
            $this->response_error(l('global.error_message.file_upload'), 401);
        }

        if(!\Altum\Plugin::is_active('offload') || (\Altum\Plugin::is_active('offload') && !settings()->offload->uploads_url)) {
            if(!is_writable(UPLOADS_PATH . Uploads::get_path('files'))) {
                $this->response_error(sprintf(l('global.error_message.directory_not_writable'), UPLOADS_PATH . Uploads::get_path('files')), 401);
            }
        }

        if($_FILES['file']['size'] > settings()->transfers->chunk_size_limit * 1000000) {
            $this->response_error(sprintf(l('global.error_message.file_size_limit'), settings()->transfers->chunk_size_limit), 401);
        }

        /* Get file details if any */
        $file = db()->where('file_uuid', $_POST['uuid'])->getOne('files');

        /* Create the file entry */
        if(!$file) {
            /* Generate random file name */
            $new_file_name = generate_readable_uuid() . '.' . $file_extension . '.temp';

            /* TODO: Add if/else checks for fopen fails */
            $file_destination = @fopen(Uploads::get_full_path('files') . $new_file_name, 'wb');
            $file_temp_source = @fopen($file_temp, 'rb');

            /* Upload the file without encryption */
            while($buffer = fread($file_temp_source, 1024 * 1024)) {
                fwrite($file_destination, $buffer);
            }

            /* Close the file stream */
            fclose($file_destination);
            fclose($file_temp_source);

            $status = $_POST['total_chunks'] > 1 ? 'uploading' : 'uploaded';

            /* Database query */
            $file_id = db()->insert('files', [
                'file_uuid' => $_POST['uuid'],
                'uploader_id' => md5(get_ip()),
                'user_id' => $this->user->user_id ?? null,
                'name' => $new_file_name,
                'original_name' => $file_name,
                'size' => $_FILES['file']['size'],
                'status' => $_POST['total_chunks'] > 1 ? 'uploading' : 'uploaded',
                'is_encrypted' => (int) $_POST['file_encryption_is_enabled'],
                'datetime' => get_date(),
            ]);

        }

        else {
            /* Check if file is already finished */
            if($file->status == 'uploaded') {
                /* Prepare the data */
                $data = [
                    'id' => (int) $file->file_id,
                    'user_id' => (int) $file->user_id,
                    'transfer_id' => (int) $file->transfer_id,
                    'file_uuid' => bin2hex($file->file_uuid),
                    'uploader_id' => $file->uploader_id,
                    'name' => $file->name,
                    'original_name' => $file->original_name,
                    'size' => (int) $file->size,
                    'status' => $file->status,
                    'is_encrypted' => (bool) $file->is_encrypted,
                    'datetime' => $file->datetime,
                ];

                Response::jsonapi_success($data);
            }

            /* TODO: Add if/else checks for fopen fails */
            $file_destination = @fopen(Uploads::get_full_path('files') . $file->name, 'ab');
            $file_temp_source = @fopen($file_temp, 'rb');

            /* Upload the file without encryption */
            while($buffer = fread($file_temp_source, 1024 * 1024)) {
                fwrite($file_destination, $buffer);
            }

            /* Close the file stream */
            fclose($file_destination);
            fclose($file_temp_source);

            /* Check file size against limit */
            if(filesize(Uploads::get_full_path('files') . $file->name) > $this->user->plan_settings->transfer_size_limit * 1000 * 1000 && $this->user->plan_settings->transfer_size_limit != -1) {
                $this->response_error(sprintf(l('global.error_message.file_size_limit'), $this->user->plan_settings->transfer_size_limit), 401);
            }
        }

        if(($_POST['chunk_index']+1) == $_POST['total_chunks']) {
            $current_file_name = $file ? $file->name : $new_file_name;
            $new_file_name = str_replace('.temp', '', $current_file_name);
            $file_id = $file_id ?? $file->file_id;

            /* Check file size against limit */
            if(filesize(Uploads::get_full_path('files') . $current_file_name) > $this->user->plan_settings->transfer_size_limit * 1000 * 1000 && $this->user->plan_settings->transfer_size_limit != -1) {
                $this->response_error(sprintf(l('global.error_message.file_size_limit'), $this->user->plan_settings->transfer_size_limit), 401);
            }

            /* Encrypt file if needed */
            if($_POST['password'] && $_POST['file_encryption_is_enabled']) {
                /* Encrypt */
                encrypt_file(Uploads::get_full_path('files') . $current_file_name, Uploads::get_full_path('files') . $new_file_name, $_POST['password']);

                /* Delete old file */
                unlink(Uploads::get_full_path('files') . $current_file_name);
            } else {

                /* Rename file */
                rename(Uploads::get_full_path('files') . $current_file_name, Uploads::get_full_path('files') . $new_file_name);
            }

            /* Get full file size after upload */
            $file_size = filesize(Uploads::get_full_path('files') . $new_file_name);

            /* Upload to external storage if needed */
            if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
                try {
                    $s3 = new \Aws\S3\S3Client(get_aws_s3_config());

                    /* Upload */
                    $uploader = new \Aws\S3\MultipartUploader($s3, Uploads::get_full_path('files') . $new_file_name, [
                        'Bucket' => settings()->offload->storage_name,
                        'Key' => UPLOADS_URL_PATH . Uploads::get_path('files') . $new_file_name,
                        'part_size' => (settings()->transfers->offload_chunk_size_limit ?? 10) * 1024 * 1024,
                    ]);

                    /* Upload */
                    $uploader->upload();
                } catch (\Exception $exception) {
                    $this->response_error($exception->getMessage(), 401);
                }

                /* Delete the local file */
                unlink(Uploads::get_full_path('files') . $new_file_name);
            }

            $status = 'uploaded';

            /* Database query */
            db()->where('file_id', $file_id)->update('files', [
                'name' => $new_file_name,
                'status' => $status,
                'size' => $file_size,
            ]);

        }

        /* Prepare the data */
        $data = [
            'id' => (int) $file_id,
            'user_id' => $this->user->user_id ? (int) $this->user->user_id : null,
            'transfer_id' => null,
            'file_uuid' => bin2hex($_POST['uuid']),
            'uploader_id' => md5(get_ip()),
            'name' => $new_file_name,
            'original_name' => $file_name,
            'size' => (int) $_FILES['file']['size'] ?? $file_size,
            'status' => $status ?? $file->status,
            'is_encrypted' => (bool) (int) $_POST['file_encryption_is_enabled'],
            'datetime' => get_date(),
        ];

        Response::jsonapi_success($data);
    }

    public function delete_api() {

        if(empty($_POST)) {
            throw_404();
        }

        /* Check for required fields */
        $required_fields = ['uuid'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field])) {
                $this->response_error(l('global.error_message.empty_fields'), 401);
                break 1;
            }
        }

        $_POST['uuid'] = hex2bin($_POST['uuid']);

        if(!\Altum\Csrf::check('global_token')) {
            $this->response_error(l('global.error_message.invalid_csrf_token'), 401);
        }

        if(!$file = db()->where('file_uuid', $_POST['uuid'])->getOne('files')) {
            //$this->response_error(l('global.error_message.basic'), 401);
            // File was most likely not yet uploaded, skip */
            die();
        }

        if($file->transfer_id || $file->uploader_id != md5(get_ip())) {
            $this->response_error(l('global.error_message.basic'), 401);
        }

        /* Delete uploaded file */
        Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

        /* Delete the resource */
        db()->where('file_id', $file->file_id)->delete('files');

        die();

    }

    public function delete() {

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.transfers')) {
            Alerts::add_error(l('global.info_message.team_no_access'));
            redirect('transfers');
        }

        if (empty($_POST)) {
            throw_404();
        }

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('transfers');
        }

        $file_id = (int) $_POST['file_id'];

        /* Get file details */
        if(!$file = db()->where('file_id', $file_id)->getOne('files', ['file_id', 'user_id', 'uploader_id', 'transfer_id', 'transfer_request_id', 'name', 'original_name', 'offload_id'])) {
            throw_404();
        }

        /* Make sure the current user has access */
        if(($file->uploader_id != md5(get_ip()) && ($file->user_id && $file->user_id != $this->user->user_id))) {
            throw_404();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Delete uploaded file */
            Uploads::delete_uploaded_file_and_potential_residue($file->name, 'files', $file->offload_id);

            /* Delete the resource */
            db()->where('file_id', $file->file_id)->delete('files');

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $file->original_name . '</strong>'));

            /* Update the user */
            (new \Altum\Models\Files())->calculate_and_update_file_usage($file->user_id);

            /* Clear the cache */
			if($file->transfer_id) {
				cache()->deleteItem('files?transfer_id=' . $file->transfer_id);
				redirect('transfer/' . $file->transfer_id);
			}

			if($file->transfer_request_id) {
				cache()->deleteItem('files?transfer_request_id=' . $file->transfer_request_id);
				redirect('transfer-request/' . $file->transfer_request_id);
			}


        }

        redirect('dashboard');
    }

}
