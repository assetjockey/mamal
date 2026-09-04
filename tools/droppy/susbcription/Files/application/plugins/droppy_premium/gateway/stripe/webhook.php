<?php
require_once dirname(__FILE__) . '/../../autoloader.php';
require_once dirname(__FILE__). '/init.php';

$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(
        json_decode($payload, true)
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
}

$clsSettings = new PremiumSettings();
$premium_settings = $clsSettings->getSettings();

$premiumJsonConfig = file_get_contents(dirname(__FILE__) . '/../../config.json');
$premium_config = json_decode($premiumJsonConfig, true)['premium'];

$droppy_settings = $clsSettings->getDroppySettings();

// Init some classes
$clsUser = new PremiumUser();
$clsSubs = new PremiumSubs();

// Handle the event
switch ($event->type) {
    // First payment checkout is completed
    case 'checkout.session.completed':
        $paymentIntent = $event->data->object;
        $client_reference = $paymentIntent->client_reference_id;

        if(!empty($client_reference)) {
            $sub = $clsSubs->getBySubID($client_reference);

            if($sub['status'] == 'validating') {
                $clsSubs->updateBySubID(array(
                    'status' => 'active',
                    'stripe_id' => $paymentIntent->subscription
                ), $client_reference);
            }
        }
    break;
    case 'charge.failed':

    break;
    case 'customer.subscription.deleted':
        $paymentIntent = $event->data->object;

        $clsSubs->updateByStripeID(array('status' => 'canceled_end'), $paymentIntent->id);

        $info = $clsSubs->getByStripeID($paymentIntent->id);

        if(!empty($info)) {
            $tokens = array(
                'next_date' => $paymentIntent->current_period_end,
                'paypal_id' => $paymentIntent->id,
                'last_date' => $paymentIntent->current_period_start,
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
    case 'customer.subscription.updated':
        $sub_ID = $event->data->object->id;

        if(!empty($sub_ID)) {
            $sub = $clsSubs->getByStripeID($sub_ID);

            $clsSubs->updateByStripeID(array(
                'last_date' => $event->data->object->current_period_start,
                'next_date' => $event->data->object->current_period_end
            ), $sub_ID);

            if($event->data->object->current_period_end > time()) {
                $clsSubs->updateByStripeID(array(
                    'status' => 'active'
                ), $sub_ID);
            }
        }
    break;

    case 'customer.subscription.created':
        $sub_ID = $event->data->object->id;

        if(!empty($sub_ID)) {
            $paymentIntent = $event->data->object;

            $info = $clsSubs->getByStripeID($sub_ID);

            $tokens = array(
                'next_date' => $paymentIntent->current_period_end,
                'paypal_id' => $paymentIntent->id,
                'last_date' => $paymentIntent->current_period_start,
                'name' => $info['name'],
                'status' => $info['status'],
                'company' => $info['company'],
                'sub_id' => $info['sub_id'],
                'manage_page' => $droppy_settings['site_url'] . '?goto=custom_account',
                'email' 		=> $info['email'],
                'payment' 		=> $info['payment']
            );

            $pattern = '{%s}';

            $map = array();
            foreach ($tokens as $var => $value) {
                $map[sprintf($pattern, $var)] = $value;
            }

            $email_message = strtr($premium_settings['new_sub_email'], $map);

            $this->email->sendEmailClean($email_message, $premium_settings['new_sub_subject'], array($info['email']));
        }
    break;

    default:
        echo 'Received unknown event type ' . $event->type;
}

http_response_code(200);
exit;
