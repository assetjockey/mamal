<?php

require_once dirname(__FILE__) . '/SettingsLibrary.php';

class HandlerLibrary
{
    protected $CI;

    private $_settings, $_config, $_ftp;

    function __construct()
    {
        $this->CI =& get_instance();

        $settings = new SettingsLibrary();
        $this->_settings = (object) $settings->_settings['ftp'];

        $this->_config = array(
            'hostname' => $this->_settings->host,
            'username' => $this->_settings->username,
            'password' => $this->_settings->password,
            'port'     => $this->_settings->port,
            'debug'    => TRUE
        );

        if($this->_settings->type == 'ftp')
        {
            $this->CI->load->library('ftp');

            $this->_ftp = $this->CI->ftp;
        }
        elseif($this->_settings->type == 'sftp')
        {
            require_once dirname(__FILE__) . '/SftpLibrary.php';
            $this->CI->load->library('SftpLib');

            $this->_ftp = $this->CI->sftplib;

            $this->_config['key']        = $this->_settings->key;
            $this->_config['method']     = $this->_settings->method;
            $this->_config['passphrase'] = $this->_settings->passphrase;
        }

        $this->_ftp->connect($this->_config);
    }

    /**
     * Upload file to ftp storage
     *
     * @param $upload_id
     * @param $file_path
     * @param $file_name
     * @param int $encrypt
     * @return bool
     */
    public function upload($upload_id, $file_path, $file_name, $encrypt = 0)
    {
        $this->CI->load->library('CompleteHandler');

        $dirpath = dirname($file_name);

        $list_files = $this->_ftp->list_files($dirpath);
        if($list_files === false || count($list_files) === 0) {
            $this->_ftp->mkdir($this->_settings->remote_path . $dirpath);
        }

        if($encrypt) {
            $encryptKey = $this->CI->completehandler->encryptFile($file_path);

            $this->CI->uploads->updateEncrypt($encryptKey, $upload_id);
        }

        if($this->_ftp->upload($file_path, $this->_settings->remote_path . $file_name, 'auto', 0775)) {
            $this->_ftp->close();

            return true;
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
        $upload      = $this->CI->uploads->getByUploadID($upload_id);
        $remote_path = $this->_settings->remote_path . $file_path;

        // Don't keep the browser waiting
        session_write_close();

        if ($this->_settings->type == 'sftp') {
            $size = $this->_ftp->getSize($remote_path);
        }

        // Send download headers
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . $size);

        if ($encrypt) {
            $temp_path = $this->CI->config->item('upload_dir') . 'temp/' . random_string('alnum', 32) . '_' . $file_name;
            $decrypt_temp_path = $this->CI->config->item('upload_dir') . 'temp/decrypt_' . random_string('alnum', 32) . '_' . $file_name;

            if($this->_ftp->download($remote_path, $temp_path)) {
                $this->CI->load->library('CompleteHandler');
                $this->CI->completehandler->decryptFile($temp_path, $decrypt_temp_path, $upload['encrypt']);

                unlink($temp_path);

                $file = fopen($decrypt_temp_path, "r");

                while (!feof($file)) {
                    print(@fread($file, 1024 * 8));
                    ob_flush();
                    flush();
                }

                unlink($decrypt_temp_path);
            }
        }
        else
        {
            while (ob_get_level()) ob_end_clean();
            if ($this->_settings->type == 'ftp')
            {
                readfile('ftp://' . $this->_settings->username . ':' . $this->_settings->password . '@' . $this->_settings->host . '/' . $remote_path);
            }
            elseif ($this->_settings->type == 'sftp')
            {
                $this->_ftp->directDownload($remote_path);
            }
        }
    }

    /**
     * Delete file from ftp storage
     *
     * @param $file_name
     * @return bool
     */
    public function delete($file_name) {
        if (!pathinfo($file_name, PATHINFO_EXTENSION)) {
            return $this->_ftp->delete_dir($this->_settings->remote_path . $file_name);
        } else {
            return $this->_ftp->delete_file($this->_settings->remote_path . $file_name);
        }
    }
}