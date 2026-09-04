<?php

require dirname(__FILE__) . '/../external/aws/aws-autoloader.php';
require_once dirname(__FILE__) . '/SettingsLibrary.php';

use Aws\Credentials\CredentialProvider;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Aws\S3\Crypto;

class HandlerLibrary
{
    private $_settings, $_s3, $CI;

    function __construct()
    {
        $settings = new SettingsLibrary();
        $this->CI =& get_instance();

        $this->_settings    = $settings->_settings;

        $s3_settings = [
            'region'        => $this->_settings['s3']['region'],
            'version'       => 'latest',
            'credentials'   => [
                'key'       => $this->_settings['aws']['key'],
                'secret'    => $this->_settings['aws']['secret']
            ],
            'use_path_style_endpoint' => $this->_settings['s3']['path_style'] ?? false
        ];

        if(!empty($this->_settings['aws']['endpoint'])) {
            $s3_settings['endpoint'] = $this->_settings['aws']['endpoint'];
        }

        try {
            $this->_s3 = new Aws\S3\S3Client($s3_settings);
        } catch (Exception $e) {
            $this->CI->logging->log('[S3][ERROR] ' . $e);
        }
    }

    /**
     * Upload file to S3 bucket

     * @param $upload_id
     * @param $file_path
     * @param $file_name
     * @param int $encrypt
     * @return bool
     */
    public function upload($upload_id, $file_path, $file_name, $encrypt = 0)
    {
        // Define the parameters that are being passed to the uploader
        $params = [];
        if($encrypt == 1) {
            $params['ServerSideEncryption'] = 'AES256';
        }

        $this->CI->logging->log('[S3][UPLOAD] Uploading to S3 started');

        if(isset($this->_settings['s3']['part_size']) && !empty($this->_settings['s3']['part_size']) && $this->_settings['s3']['part_size'] != 0) {
            $part_size = $this->_settings['s3']['part_size'];
        } else {
            $part_size = 5242880;
        }

        // Upload the file in pieces
        try {
            $uploader = new MultipartUploader($this->_s3, $file_path, [
                'bucket' => $this->_settings['s3']['bucket'],
                'key'    => $this->_settings['s3']['path'] . $file_name,
                'acl'    => 'private',
                'params' => $params,
                'part_size' => $part_size
            ]);
        } catch (Exception $e) {
            $this->CI->logging->log('[S3][ERROR] ' . $e);
            return false;
        }

        // Check if the upload has been finished
        try {
            $result = $uploader->upload();
            $this->CI->logging->log('[S3][UPLOAD] Upload to S3 finished');
            return true;
        } catch (MultipartUploadException $e) {
            $error = $e->getMessage();
            $this->CI->logging->log('[S3][ERROR] ' . $error);
            error_log($error);
            return false;
        }
    }

    /**
     * Download file to client directly
     *
     * @param $upload_id
     * @param $file_name
     * @param $file_path
     * @param $encrypt
     * @param $size
     */
    public function download($upload_id, $file_name, $file_path, $encrypt = 0, $size = 0) {
        // Get info of the "object" in the bucket
        $cmd = $this->_s3->getCommand('GetObject', [
            'Bucket' => $this->_settings['s3']['bucket'],
            'Key'    => $this->_settings['s3']['path'] . $file_path,
            'ResponseContentDisposition' => 'attachment; filename="'.urlencode($file_name).'"',
            'ResponseContentType' => 'application/octet-stream'
        ]);

        // Create an unique URL
        $request = $this->_s3->createPresignedRequest($cmd, '+15 minutes');

        // Retrieve the URL
        $download_url = (string) $request->getUri();

        // Don't keep the browser waiting
        session_write_close();

        // Start download
        header('Location: '.$download_url);
    }

    /**
     * Delete file from bucket
     *
     * @param $file_path
     * @return bool
     */
    public function delete($file_path) {
        $delete = $this->_s3->deleteObject([
            'Bucket' => $this->_settings['s3']['bucket'],
            'Key'    => $this->_settings['s3']['path'] . $file_path
        ]);

        if($delete)
            return true;
        return false;
    }
}