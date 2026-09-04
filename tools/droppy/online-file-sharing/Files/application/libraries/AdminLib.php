<?php

/**
 * Class AdminLib
 */
class AdminLib
{
    /**
     * @var CI_Controller
     */
    protected $CI;

    /**
     * AdminLib constructor.
     */
    public function __construct() {
        $this->CI =& get_instance();

        $this->CI->load->model('uploads');
    }

    /**
     * Returns the total disk usage used in MB
     *
     * @return float
     */
    public function getDiskUsage() {
        $total = $this->CI->uploads->getTotalStorageUsed();

        return $total;
    }

    public function getDiskStats() {
        $total = $this->CI->uploads->getTotalStorageUsed();

        return $total;
    }

    /**
     * Authenticate the user
     * @param $email
     * @param $password
     * @return bool
     */
    public function authenticate($email, $password) {
        $this->CI->load->model('accounts');

        // If login correct
        $check = $this->CI->accounts->login($email);
        if(is_array($check)) {
            if(password_verify($password, $check['password']))
            {
                // Start creating a session
                $this->CI->load->library('session');

                $this->CI->session->sess_regenerate();

                // Session data to be set
                $session_data = array(
                    'admin' => TRUE,
                    'admin_id' => $check['id'],
                    'admin_email' => $email
                );

                $this->CI->session->set_userdata($session_data);

                // Session created, return data.
                return true;
            }
        }
        return false;
    }

    public function forgotPassword($email) {
        $this->CI->load->model('accounts');

        // If login correct
        $check = $this->CI->accounts->getByEmail($email);
        if(is_array($check)) {
            // Generate long unique reset ID
            $reset_id = md5(uniqid(rand(), true)) . md5(uniqid(rand(), true));

            $this->CI->accounts->updateByID(array('reset_id' => $reset_id), $check['id']);

            $this->CI->load->library('email');

            $this->CI->email->from($this->CI->config->item('email_from'), $this->CI->config->item('email_from_name'));
            $this->CI->email->to($check['email']);

            $this->CI->email->subject('Password reset');
            $this->CI->email->message('You have requested a password reset, click the link below to reset your password. <br><br>' . $this->CI->config->item('site_url') . $this->CI->config->item('admin_route') . '/reset/' . $reset_id);

            $this->CI->email->send();

            return true;
        }
        return false;
    }

    public function resetPassword($reset_id, $password) {
        $this->CI->load->model('accounts');

        // If login correct
        $check = $this->CI->accounts->getByResetID($reset_id);
        if(is_array($check)) {
            $this->CI->accounts->updateByID(array('password' => password_hash($password, PASSWORD_DEFAULT), 'reset_id' => ''), $check['id']);

            return true;
        }
        return false;
    }

    /**
     * Save entered settings
     *
     * @param $page
     * @param $data
     */
    public function save($page, $data) {
        unset($data['save']);

        switch($page) {
            case 'upload':
                if(empty($data['expire'])) {
                    $data['expire'] = 0;
                }
                elseif(count($data['expire']) > 1) {
                    $data['expire'] = implode(',', $data['expire']);
                } else {
                    $data['expire'] = $data['expire'][0];
                }

                $this->CI->load->model('settings');
                $this->CI->settings->save($data);
            break;
            case 'general':
            case 'termsabout':
            case 'advertising':
            case 'contact':
                $this->CI->load->model('settings');
                $this->CI->settings->save($data);
            break;
            case 'mail':
                if(empty($data['smtp_password'])) {
                    unset($data['smtp_password']);
                }

                $this->CI->load->model('settings');
                $this->CI->settings->save($data);
            break;
            case 'social':
                $this->CI->load->model('socials');
                $this->CI->socials->save($data);
            break;
            case 'mailtemplates':
                if(isset($data['action']) && $data['action'] == 'disable_templates') {
                    if(!empty($data['disableTemplates'])) {
                        $data['disabled_email_templates'] = implode(',', $data['disableTemplates']);
                        unset($data['disableTemplates']);
                    } else {
                        $data['disabled_email_templates'] = '';
                    }

                    unset($data['action']);

                    $this->CI->load->model('settings');
                    $this->CI->settings->save($data);
                } else {
                    $this->CI->load->model('templates');
                    $this->CI->templates->save($data);
                }
            break;
            case 'language':
                $this->CI->load->model('Language');

                if(isset($data['id'])) {
                    $this->CI->language->updateById($data);
                } else {
                    $this->CI->language->save($data);
                }
            break;
        }
    }

    public function pageAction() {

    }

    /**
     * Add new background
     *
     * @param $file
     * @param $data
     * @return bool
     */
    public function addBackground($file, $data) {
        $this->CI->load->model('backgrounds');

        if(move_uploaded_file($file['tmp_name'], FCPATH . 'assets/backgrounds/' . $file['name'])) {
            $data = array(
                'src' => 'assets/backgrounds/' . $file['name'],
                'url' => $data['url'],
                'duration' => $data['duration']
            );

            if($this->CI->backgrounds->add($data)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Delete existing background
     *
     * @param $id
     */
    public function deleteBackground($id) {
        $this->CI->load->model('backgrounds');

        $background = $this->CI->backgrounds->getByID($id);
        if(!empty($background)) {
            if(file_exists(FCPATH . $background->src))
            {
                unlink(FCPATH . $background->src);
            }
            $this->CI->backgrounds->delete($id);
        }
    }

    /**
     * Add new theme
     *
     * @param $data
     * @return bool
     */
    public function addTheme($data) {
        $this->CI->load->model('themes');

        $data['status'] = 'stopped';

        if($this->CI->themes->add($data)) {
            return true;
        }
        return false;
    }

    /**
     * Perform theme actions based on action
     *
     * @param string $action
     * @param string $value
     * @return bool
     */
    public function themeAction($action, string $value = '') {
        $this->CI->load->model('themes');

        switch ($action) {
            case 'activate':
                $this->CI->themes->updateAll(array('status' => 'stopped'));
                $this->CI->themes->updateByID(array('status' => 'ready'), $value);
            break;
            case 'suspend':
                $this->CI->themes->updateByID(array('status' => 'stopped'), $value);
            break;
            case 'delete':
                $this->CI->themes->deleteByID($value);
            break;
            case 'color':
                $this->CI->load->model('settings');
                $this->CI->settings->save(array(
                    'theme_color' => $_POST['theme_color'],
                    'theme_color_secondary' => $_POST['theme_color_secondary'],
                    'theme_color_text' => $_POST['theme_color_text']
                ));
            break;
        }

        return true;
    }

    /**
     * Check if the user is authenticated
     *
     * @return bool
     */
    public function authenticated() {
        $this->CI->load->model('settings');

        // If isn't authenticated
        if(!$this->CI->session->userdata('admin')) {
            // Load custom admin route if set
            $adminRoute = 'admin';
            foreach ($this->CI->router->routes as $key => $value) {
                if ($value === 'admin') {
                    $adminRoute = $key;
                }
            }

            $redirect = $this->CI->settings->getAll()[0]['site_url'] . $adminRoute . '/login';
            header('Location: '.$redirect);
            die();
        }
        return false;
    }

    /**
     * Logout the admin user
     */
    public function logout() {
        $unset = array('admin', 'admin_email');

        $this->CI->session->unset_userdata($unset);
    }

    /**
     * Send data to the Proxibolt API
     *
     * @param $type
     * @param array $data
     * @return bool|string
     */
    public function callProxibolt($type, $data = array()) {
        $fields = '';
        foreach ($data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        rtrim($fields, '&');

        $ch = curl_init('https://api.proxibolt.com/droppy/' . $type);

        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10000);

        $content = curl_exec( $ch );
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close( $ch );

        if ($content === false || $http_code != 200) {
            return false;
        }

        return $content;
    }

    /**
     * Downloads the update file from the Proxibolt server
     *
     * @param $file_url
     * @return string
     */
    private function downloadUpdateFile($file_url) {
        $file_path = FCPATH . 'droppy_' . json_decode($this->callProxibolt('version'))->version . '.zip';
        $file = fopen($file_path, 'w');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file_url);
        // cURL options
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Set file handler option
        curl_setopt($ch, CURLOPT_FILE, $file);
        // Execute cURL
        curl_exec($ch);
        // Close cURL
        curl_close($ch);
        // Close file
        fclose($file);

        return $file_path;
    }

    /**
     * Runs the update
     *
     * @param $data
     * @return bool
     */
    public function runUpdate($data) {
        // Check if the system is out of date
        if(!$this->isUpToDate())
        {
            $data = array(
                "purchase_code" => $data['purchase_code'],
                "ip" => $_SERVER['SERVER_ADDR']
            );

            $return = json_decode($this->callProxibolt('update', $data));

            // Check if update request was successful
            if ($return->type == 'success')
            {
                // Download the update file from the Proxibolt server
                $update_file = $this->downloadUpdateFile($return->url);

                $zip = new ZipArchive;

                // Extract the update file
                if ($zip->open($update_file) === TRUE)
                {
                    $zip->extractTo(FCPATH);
                    $zip->close();
                }

                // Remove the downloaded zip file
                unlink($update_file);

                // Run the update files (If there are any)
                $this->runUpdateFiles();

                return TRUE;
            }
        }
        return false;
    }

    public function runUpdateFiles() {
        // Check if update provided any SQL file to run
        if (file_exists(FCPATH . 'update.sql'))
        {
            $sql = file_get_contents(FCPATH . 'update.sql');

            $query_array = explode(";", $sql);
            foreach ($query_array as $query)
            {
                if ( ! empty($query))
                {
                    $query = utf8_encode($query);
                    $this->CI->db->query($query);
                }
            }

            unlink(FCPATH . 'update.sql');
        }

        // Check if update library exists
        if(file_exists(APPPATH . 'libraries/UpdateLib.php')) {
            $this->CI->load->library('UpdateLib');
            $this->CI->updatelib->run();
        }
    }

    /**
     * Checks if Droppy is using the latest update
     *
     * @return bool
     */
    public function isUpToDate() {
        $this->CI->load->model('settings');

        $cur_version = $this->CI->config->item('version');
        $api = json_decode($this->callProxibolt('version', array('cur_version' => $cur_version, 'url' => $this->CI->settings->getAll()[0]['site_url'], 'purchase_code' => $this->CI->settings->getAll()[0]['purchase_code'])));

        if(isset($api->version) && ($api->version > $cur_version)) {
            return false;
        }
        return true;
    }

    /**
     * Checks htaccess file for new security
     *
     * @return false|int
     */
    public function checkHtaccess() {
        if(!file_exists(FCPATH . 'application/.htaccess')) {
            return false;
        }
        return true;
    }

    public function checkTermsPageExists() {
        // Query the Pages model to see if the terms page exists with type terms_page
        // the setting accept_terms should be set to yes, if it's set to no then return true by default
        $this->CI->load->model('pages');
        $this->CI->load->model('settings');

        $terms_page = $this->CI->pages->getByType('terms_page');
        $settings = $this->CI->settings->getAll()[0];

        if(empty($terms_page) && $settings['accept_terms'] == 'yes') {
           return false;
        }
        return true;
    }
}