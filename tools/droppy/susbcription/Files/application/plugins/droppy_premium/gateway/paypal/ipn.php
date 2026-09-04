<?php
require_once dirname(__FILE__) . '/../../autoloader.php';
require_once dirname(__FILE__) . '/paypalfunctions.php';

// Load premium settings from DB
$clsSettings = new PremiumSettings();
$premium_settings = $clsSettings->getSettings();

$premiumJsonConfig = file_get_contents(dirname(__FILE__) . '/../../config.json');
$premium_config = json_decode($premiumJsonConfig, true)['premium'];

$droppy_settings = $clsSettings->getDroppySettings();

$clsPaypal = new Paypal($premium_config, $premium_settings);

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}

$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}

if($premium_config['sandbox'] == true) {
  $ch = curl_init('https://www.sandbox.paypal.com/cgi-bin/webscr');
}
else
{
  $ch = curl_init('https://www.paypal.com/cgi-bin/webscr');
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

if( !($res = curl_exec($ch)) ) {
    curl_close($ch);
    exit;
}
curl_close($ch);

$time = time();

if (strcmp ($res, "VERIFIED") == 0) {
    $clsUser = new PremiumUser();
    $clsSubs = new PremiumSubs();

    //Getting some information from the IPN post
    $txn_type = $_POST['txn_type'];
    $paypal_id = $_POST['recurring_payment_id'];

    //When payment has been reversed
    if(isset($_POST['payment_status'])) {
        if($_POST['payment_status'] == 'Reversed')
        {
            $info = $clsSubs->getByPaypalID($paypal_id);
            if($info !== false)
            {
                $subid = $info['sub_id'];
                $user_email = $info['email'];

                $clsUser->updateBySubID(array('status' => 'suspended_reversal'), $subid);
                $clsSubs->updateBySubID(array('status' => 'canceled'), $subid);
            }
        }

        // When reversal has been canceled
        if($_POST['payment_status'] == 'Canceled_Reversal')
        {
            $info = $clsSubs->getByPaypalID($paypal_id);
            if($info !== false)
            {
                $subid = $info['sub_id'];
                $user_email = $info['email'];

                $clsUser->updateBySubID(array('status' => 'ready'), $subid);
                $clsSubs->updateBySubID(array('status' => 'active'), $subid);
            }
        }
    }
    if($txn_type == 'recurring_payment') {
        if($_POST['payment_status'] == 'Completed')
        {
            $resArray = $clsPaypal->GetRecurringPaymentsProfileDetails($paypal_id);
            $ack = strtoupper($resArray["ACK"]);

            if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )
            {
                // Getting the time in LA (California) (Paypal Timezone)
                date_default_timezone_set('America/Los_Angeles');

                if($premium_settings['recur_time'] == 'Day') {
                    $add_time = '86400';
                }
                if($premium_settings['recur_time'] == 'Week') {
                    $add_time = '604800';
                }
                if($premium_settings['recur_time'] == 'Month') {
                    $add_time = '2629743';
                }
                $nextbillingdate = time() + $add_time;
                $lastbillingdate = time();

                $info = $clsSubs->getByPaypalID($paypal_id);
                if($info !== false)
                {
                    $subid = $info['sub_id'];

                    $clsUser->updateBySubID(array('status' => 'ready'), $subid);
                    $clsSubs->updateBySubID(array('status' => 'active', 'last_date' => $lastbillingdate, 'next_date' => $nextbillingdate), $subid);
                }
            }
            else
            {
                //Display a user friendly Error on the page using any of the following error information returned by PayPal
                $ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
                $ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
            }
        }
    }
    //When subscription has been canceled
    if($txn_type == 'recurring_payment_profile_cancel') {
        $info = $clsSubs->getByPaypalID($paypal_id);

        if($info !== false) {
            $subid = $info['sub_id'];

            if($info['status'] != 'canceled_end') {
                $clsSubs->updateBySubID(array('status' => 'canceled_end'), $subid);

                $tokens = array(
                    'next_date'     => date("Y-m-d", $info['next_date']),
                    'paypal_id'     => $info['paypal_id'],
                    'last_date'     => date("Y-m-d", $info['last_date']),
                    'name'          => $info['name'],
                    'status'        => $info['status'],
                    'company'       => $info['company'],
                    'sub_id'        => $info['sub_id'],
                    'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account'
                );

                $pattern = '{%s}';

                $map = array();
                foreach($tokens as $var => $value)
                {
                    $map[sprintf($pattern, $var)] = $value;
                }

                $email_message = strtr($premium_settings['sub_cancel_e_email'], $map);

                $this->email->sendEmailClean($email_message, $premium_settings['sub_cancel_e_subject'], array($info['email']));
            }
        }
    }

    //When the subscription is missing payments
    if($txn_type == 'recurring_payment_skipped') {
        $info = $clsSubs->getByPaypalID($paypal_id);
        if($info !== false) {
            $subid = $info['sub_id'];

            $tokens = array(
                'next_date'     => date("Y-m-d", $info['next_date']),
                'paypal_id'     => $info['paypal_id'],
                'last_date'     => date("Y-m-d", $info['last_date']),
                'name'          => $info['name'],
                'status'        => $info['status'],
                'company'       => $info['company'],
                'sub_id'        => $info['sub_id'],
                'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account'
            );

            $pattern = '{%s}';

            $map = array();
            foreach($tokens as $var => $value)
            {
                $map[sprintf($pattern, $var)] = $value;
            }

            $email_message = strtr($premium_settings['payment_failed_email'], $map);
            $this->email->sendEmailClean($email_message, $premium_settings['payment_failed_subject'], array($info['email']));
        }
    }

    //When the subscription is missing payments
    if($txn_type == 'recurring_payment_failed') {
        $info = $clsSubs->getByPaypalID($paypal_id);
        if($info !== false) {
            $subid = $info['sub_id'];

            $tokens = array(
                'next_date'     => date("Y-m-d", $info['next_date']),
                'paypal_id'     => $info['paypal_id'],
                'last_date'     => date("Y-m-d", $info['last_date']),
                'name'          => $info['name'],
                'status'        => $info['status'],
                'company'       => $info['company'],
                'sub_id'        => $info['sub_id'],
                'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account'
            );

            $pattern = '{%s}';

            $map = array();
            foreach($tokens as $var => $value)
            {
                $map[sprintf($pattern, $var)] = $value;
            }

            $email_message = strtr($premium_settings['payment_failed_email'], $map);

            $this->email->sendEmailClean($email_message, $premium_settings['payment_failed_subject'], array($info['email']));
        }
    }

    // When the subscription is missing payments
    if($txn_type == 'recurring_payment_suspended_due_to_max_failed_payment') {
        $info = $clsSubs->getByPaypalID($paypal_id);
        if($info !== false) {
            $subid = $info['sub_id'];

            $clsUser->updateBySubID(array('status' => 'suspended', 'time' => $time), $subid);
            $clsSubs->updateBySubID(array('status' => 'suspended', 'time' => $time), $subid);

            $tokens = array(
                'next_date'     => date("Y-m-d", $info['next_date']),
                'paypal_id'     => $info['paypal_id'],
                'last_date'     => date("Y-m-d", $info['last_date']),
                'name'          => $info['name'],
                'status'        => $info['status'],
                'company'       => $info['company'],
                'sub_id'        => $info['sub_id'],
                'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account'
            );

            $pattern = '{%s}';

            $map = array();
            foreach($tokens as $var => $value)
            {
                $map[sprintf($pattern, $var)] = $value;
            }

            $email_message = strtr($premium_settings['suspended_email'], $map);

            $this->email->sendEmailClean($email_message, $premium_settings['suspended_email_subject'], $info['email']);
        }
    }
    if($txn_type == 'recurring_payment_suspended') {
        $info = $clsSubs->getByPaypalID($paypal_id);
        if($info !== false) {
            $subid = $info['sub_id'];

            $clsUser->updateBySubID(array('status' => 'suspended', 'time' => $time), $subid);
            $clsSubs->updateBySubID(array('status' => 'suspended', 'time' => $time), $subid);

            $tokens = array(
                'next_date'     => date("Y-m-d", $info['next_date']),
                'paypal_id'     => $info['paypal_id'],
                'last_date'     => date("Y-m-d", $info['last_date']),
                'name'          => $info['name'],
                'status'        => $info['status'],
                'company'       => $info['company'],
                'sub_id'        => $info['sub_id'],
                'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account'
            );

            $pattern = '{%s}';

            $map = array();
            foreach($tokens as $var => $value)
            {
                $map[sprintf($pattern, $var)] = $value;
            }

            $email_message = strtr($premium_settings['suspended_email'], $map);

            $this->email->sendEmailClean($email_message, $premium_settings['suspended_email_subject'], array($info['email']));
        }
    }
} else if (strcmp ($res, "INVALID") == 0) {
    // IPN invalid, log for manual investigation
    echo 'IPN returned invalid response, please check your credentials.';
}