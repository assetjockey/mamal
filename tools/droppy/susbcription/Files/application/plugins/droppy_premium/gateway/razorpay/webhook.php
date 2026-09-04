<?php
/*require_once dirname(__FILE__) . '/../../autoloader.php';
require_once __DIR__ . '/Razorpay.php';

$clsSettings = new PremiumSettings();
$premium_settings = $clsSettings->getSettings();
$droppy_settings = $clsSettings->getDroppySettings();

$clsSubs = new PremiumSubs();

// Verify the webhook signature
$webhookSecret = $premium_settings['razorpay_webhook_secret']; // Replace with your actual webhook secret
$headers = getallheaders();
$signature = $headers['X-Razorpay-Signature'];

$requestBody = file_get_contents('php://input');

$api = new \Razorpay\Api\Api($premium_settings['razorpay_key'], $premium_settings['razorpay_secret']);

try {
    $api->utility->verifyWebhookSignature($requestBody, $signature, $webhookSecret);
    $event = json_decode($requestBody, true);

    // Handle different events
    switch ($event['event']) {
        case 'subscription.charged':
            // Payment successful, update your database or perform necessary actions
            $order_id = $event['payload']['subscription']['entity']['order_id'];
            $subscription_id = $event['payload']['subscription']['entity']['notes']['subscription_id'];

            if(!empty($subscription_id)) {
                $sub = $clsSubs->getBySubID($subscription_id);

                if($sub['status'] == 'validating') {
                    $clsSubs->updateBySubID(array(
                        'status' => 'active',
                        'razorpay_id' => $order_id
                    ), $subscription_id);
                }
            }
            break;

        case 'subscription.expired':
        case 'subscription.cancelled':
            // Payment successful, update your database or perform necessary actions
            $order_id = $event['payload']['subscription']['entity']['order_id'];
            $subscription_id = $event['payload']['subscription']['entity']['notes']['subscription_id'];

            $clsSubs->updateBySubID(array('status' => 'canceled_end'), $subscription_id);

            $info = $clsSubs->getBySubID($subscription_id);

            if(!empty($info)) {
                $tokens = array(
                    'next_date' => '',
                    'paypal_id' => '',
                    'last_date' => '',
                    'name' => $info['name'],
                    'status' => $info['status'],
                    'company' => $info['company'],
                    'sub_id' => $info['sub_id'],
                    'manage_page' => $droppy_settings['site_url'] . '?goto=custom_account'
                );

                $pattern = '{%s}';

                $map = array();
                foreach ($tokens as $var => $value) {
                    $map[sprintf($pattern, $var)] = $value;
                }

                $email_message = strtr($premium_settings['sub_cancel_e_email'], $map);

                $this->email->sendEmailClean($email_message, $premium_settings['sub_cancel_e_subject'], array($info['email']));
            }

            break;

        // Add more cases for other events you want to handle

        default:
            // Unknown event
            break;
    }

    http_response_code(200); // Acknowledge receipt of the webhook
} catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
    // Handle invalid signature
    http_response_code(400);
    die('Webhook signature verification failed.');
}*/
?>
