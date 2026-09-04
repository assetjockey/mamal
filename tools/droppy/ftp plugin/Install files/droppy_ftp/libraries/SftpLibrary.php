<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

use phpseclib3\Net\SFTP;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * Class SftpLib
 */
class SftpLib {
    private $_config, $_conn;

    /**
     * SftpLib constructor.
     */
    function __construct()
    {
        $this->_conn = false;
    }

    /**
     * @param $config
     */
    function connect($config)
    {
        $this->_config = $config;

        $this->_conn = new phpseclib3\Net\SFTP($this->_config['hostname'] . ':' . $this->_config['port']);

        // Init connection to remote SFTP server.
        if($this->_config['method'] == 'password')
        {
            if ( ! $this->_conn->login($this->_config['username'], $this->_config['password']))
            {
                log_message('debug', 'SFTP login/connection failed');
            }
        }
        elseif($this->_config['method'] == 'key') {
            if(!empty($this->_config['passphrase'])) {
                $key = PublicKeyLoader::load(file_get_contents(dirname(__FILE__) . '/../keys/' . $this->_config['key']), $this->_config['passphrase']);
            } else {
                $key = PublicKeyLoader::load(file_get_contents(dirname(__FILE__) . '/../keys/' . $this->_config['key']));
            }

            if ( ! $this->_conn->login($this->_config['username'], $key))
            {
                log_message('debug', 'SFTP login/connection failed');
            }
        }
    }

    /**
     * Upload file to remote storage
     *
     * @param $local_path
     * @param $remote_path
     * @return bool
     */
    function upload($local_path, $remote_path)
    {
        if($this->_conn->put($remote_path, $local_path, SFTP::SOURCE_LOCAL_FILE)) {
            return true;
        }
        return false;
    }

    /**
     * Download file from remote storage and store in local path
     *
     * @param $local_path
     * @param $remote_path
     * @return bool
     */
    function download($remote_path, $local_path)
    {
        return $this->_conn->get($remote_path, $local_path);
    }

    /**
     * Output the contents from the remote file directly to the screen
     *
     * @param $remote_path
     */
    function directDownload($remote_path)
    {
        echo $this->_conn->get($remote_path);
    }

    /**
     * Creates new directory on remote path
     *
     * @param $remote_path
     * @return mixed
     */
    function mkdir($remote_path) {
        return $this->_conn->mkdir($remote_path);
    }

    /**
     * Delete file from remote storage
     *
     * @param $remote_path
     * @return bool
     */
    function delete_file($remote_path)
    {
        return $this->_conn->delete($remote_path);
    }

    /**
     * Delete directory from remote storage
     *
     * @param $remote_path
     * @return mixed
     */
    function delete_dir($remote_path) {
        return $this->_conn->rmdir($remote_path);
    }

    /**
     * Get size of remote file
     *
     * @param $remote_path
     * @return mixed
     */
    function getSize($remote_path)
    {
        return $this->_conn->filesize($remote_path);
    }

    /**
     * Closing connection
     *
     * @return bool
     */
    function close() {
        $this->_conn->disconnect();

        return true;
    }
}