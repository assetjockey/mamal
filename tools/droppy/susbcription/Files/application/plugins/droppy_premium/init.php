<?php

require_once dirname(__FILE__) . '/autoloader.php';

if(isset($_SESSION['droppy_premium'])) {
    $clsSettings = new PremiumSettings();
    $clsSubs = new PremiumSubs();
    $clsUsers = new PremiumUser();
    $clsPlans = new PremiumPlans();
    $clsBackgrounds = new PremiumBackgrounds();

    $usr = $clsUsers->getByID($_SESSION['droppy_premium']);
    if($usr !== false)
    {
        $sub = $clsSubs->getBySubID($usr['sub_id']);
        if ($sub !== false && ($sub['status'] == 'active' || $sub['status'] == 'canceled_end') && $clsSettings->checkSettings())
        {
            $premium_settings = $clsSettings->getSettings();

            // Get specific subscription plan settings
            $plan_details = $clsPlans->getByID($sub['sub_plan']);

            // Premium upload settings
            $max_storage = $plan_details['plan_max_storage'];
            $this->CI->config->config['pm_max_storage'] = $max_storage;

            if(!empty($max_storage) && $max_storage > 0) {
                $total_storage = $clsUsers->getTotalStorage($_SESSION['droppy_premium']) / 1024 / 1024;
                $max_upload_size = $plan_details['plan_max_size'];

                if(($max_storage - $total_storage) < $max_upload_size) {
                    $max_upload_size = ($max_storage - $total_storage);
                }

                $this->CI->config->config['max_size'] = $max_upload_size;
            } else {
                $this->CI->config->config['max_size'] = $plan_details['plan_max_size'];
            }

            $this->CI->config->config['pm_pass_enabled'] = $plan_details['plan_password_enabled'];
            $this->CI->config->config['expire'] = $plan_details['plan_expire_time'];
            $this->CI->config->config['ad_enabled'] = $plan_details['plan_ad_enabled'];
            if ($this->CI->config->config['ad_enabled'] == 'false')
            {
                $this->CI->config->config['ad_1_enabled'] = 'false';
                $this->CI->config->config['ad_2_enabled'] = 'false';
            }

            $this->CI->config->config['custom_backgrounds_enabled'] = $plan_details['plan_backgrounds'];
            if($plan_details['plan_backgrounds'] == 'true') {
                $user_id = (!empty($usr['parent_id']) ? $usr['parent_id'] : $usr['id']);

                $this->CI->config->config['custom_backgrounds'] = $clsBackgrounds->getByUserID($user_id);
            }
        }
    }
}

