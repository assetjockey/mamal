<?php

$sub_id = $_SESSION['subscription_id'];

$sub_info = $clsSubs->getBySubID($sub_id);
$plan_details = $clsPlans->getByID($sub_info['sub_plan']);

$PaymentOption = "PayPal";
if ( $PaymentOption == "PayPal" )
{
	/*
	'------------------------------------
	' The paymentAmount is the total value of 
	' the shopping cart, that was set 
	' earlier in a session variable 
	' by the shopping cart page
	'------------------------------------
	*/
	
	$finalPaymentAmount =  $_SESSION["Payment_Amount"];
	
	/*
	'------------------------------------
	' Calls the DoExpressCheckoutPayment API call
	'
	' The ConfirmPayment function is defined in the file PayPalFunctions.jsp,
	' that is included at the top of this file.
	'-------------------------------------------------
	*/

	//$resArray = ConfirmPayment ( $finalPaymentAmount ); Remove comment with ontime payment.


	$resArray = $clsPaypal->CreateRecurringPaymentsProfile($plan_details['plan_desc']);
	$ack = strtoupper($resArray["ACK"]);

	if( $ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING" )
	{
		/*
		'********************************************************************************************************************
		'
		' THE PARTNER SHOULD SAVE THE KEY TRANSACTION RELATED INFORMATION LIKE 
		'                    transactionId & orderTime 
		'  IN THEIR OWN  DATABASE
		' AND THE REST OF THE INFORMATION CAN BE USED TO UNDERSTAND THE STATUS OF THE PAYMENT 
		'
		'********************************************************************************************************************
		*/

		//$transactionId		= $resArray["TRANSACTIONID"]; // ' Unique transaction ID of the payment. Note:  If the PaymentAction of the request was Authorization or Order, this value is your AuthorizationID for use with the Authorization & Capture APIs.
		//$transactionType 	= $resArray["TRANSACTIONTYPE"]; //' The type of transaction Possible values: l  cart l  express-checkout
		//$paymentType		= $resArray["PAYMENTTYPE"];  //' Indicates whether the payment is instant or delayed. Possible values: l  none l  echeck l  instant
		//$orderTime 			= $resArray["ORDERTIME"];  //' Time/date stamp of payment
		//$amt				= $resArray["AMT"];  //' The final amount charged, including any shipping and taxes from your Merchant Profile.
		//$currencyCode		= $resArray["CURRENCYCODE"];  //' A three-character currency code for one of the currencies listed in PayPay-Supported Transactional Currencies. Default: USD.
		//$feeAmt				= $resArray["FEEAMT"];  //' PayPal fee amount charged for the transaction
		//$settleAmt			= $resArray["SETTLEAMT"];  //' Amount deposited in your PayPal account after a currency conversion.
		//$taxAmt				= $resArray["TAXAMT"];  //' Tax charged on the transaction.
		//$exchangeRate		= $resArray["EXCHANGERATE"];  //' Exchange rate if a currency conversion occurred. Relevant only if your are billing in their non-primary currency. If the customer chooses to pay with a currency other than the non-primary currency, the conversion occurs in the customer’s account.
		$profileID			= $resArray["PROFILEID"];
		$accountstatus		= $resArray["PROFILESTATUS"];
		
		/*
		' Status of the payment: 
				'Completed: The payment has been completed, and the funds have been added successfully to your account balance.
				'Pending: The payment is pending. See the PendingReason element for more information. 
		*/
		
		//$paymentStatus	= $resArray["PAYMENTSTATUS"];

		/*
		'The reason the payment is pending:
		'  none: No pending reason 
		'  address: The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences section of your Profile. 
		'  echeck: The payment is pending because it was made by an eCheck that has not yet cleared. 
		'  intl: The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview. 		
		'  multi-currency: You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment. 
		'  verify: The payment is pending because you are not yet verified. You must verify your account before you can accept this payment. 
		'  other: The payment is pending for a reason other than those listed above. For more information, contact PayPal customer service. 
		*/
		
		//$pendingReason	= $resArray["PENDINGREASON"];

		/*
		'The reason for a reversal if TransactionType is reversal:
		'  none: No reason code 
		'  chargeback: A reversal has occurred on this transaction due to a chargeback by your customer. 
		'  guarantee: A reversal has occurred on this transaction due to your customer triggering a money-back guarantee. 
		'  buyer-complaint: A reversal has occurred on this transaction due to a complaint about the transaction from your customer. 
		'  refund: A reversal has occurred on this transaction because you have given the customer a refund. 
		'  other: A reversal has occurred on this transaction due to a reason not listed above. 
		*/
		
		if($accountstatus == 'PendingProfile') {
			$clsUser->updateBySubID(array('status' => 'pending'), $sub_id);

			header('Location: ' . $_SESSION['original_url'] . '&payment=pending');
		}
		elseif(isset($resArray["REASONCODE"]))
		{
            $clsUser->updateBySubID(array('status' => 'suspended'), $sub_id);

			header('Location: ' . $_SESSION['original_url'] . '&payment=reverse');
		}
		else
		{
            $clsSubs->updateBySubID(array('status' => 'active', 'paypal_id' => $profileID), $sub_id);
            $clsUser->updateBySubID(array('status' => 'ready'), $sub_id);

			$resArray2 = $clsPaypal->GetRecurringPaymentsProfileDetails($profileID);
	      	$ack2 = strtoupper($resArray2["ACK"]);

	      	if( $ack2 == "SUCCESS" || $ack2 == "SUCCESSWITHWARNING" )
		    {
		        date_default_timezone_set('America/Los_Angeles');

		        $add_time = 0;
		        if($plan_details['plan_time'] == 'Day') {
		          $add_time = 86400;
		        }
		        if($plan_details['plan_time'] == 'Week') {
		          $add_time = 604800;
		        }
		        if($plan_details['plan_time'] == 'Month') {
		          $add_time = 2629743;
		        }
		        $nextbillingdate = time() + $add_time;
		        $lastbillingdate = time();

		        $info = $clsSubs->getByPaypalID($profileID);
		        if($info !== false) {
					$subid = $info['sub_id'];

					$clsSubs->updateBySubID(array('status' => 'active', 'last_date' => $lastbillingdate, 'next_date' => $nextbillingdate), $subid);

					$map = array();

					// Email shortcodes replacements
					$info_sub = $clsSubs->getByPaypalID($profileID);
					if($info_sub !== false) {
						$tokens = array(
						    'next_date'     => date("Y-m-d", $info_sub['next_date']),
						    'paypal_id'     => $info_sub['paypal_id'],
						    'last_date'     => date("Y-m-d", $info_sub['last_date']),
						    'name'          => $info_sub['name'],
						    'status'        => $info_sub['status'],
						    'company'       => $info_sub['company'],
						    'sub_id'        => $info_sub['sub_id'],
						    'manage_page'   => $droppy_settings['site_url'] . '?goto=custom_account',
						    'email' 		=> $info_sub['email'],
						    'payment' 		=> $info_sub['payment'],
						    'amount' 		=> $finalPaymentAmount
						);

                        $pattern = '{%s}';

                        foreach($tokens as $var => $value)
                        {
                            $map[sprintf($pattern, $var)] = $value;
                        }
					}

					$email_message = strtr($premium_settings['new_sub_email'], $map);

					$this->email->sendEmailClean($email_message, $premium_settings['new_sub_subject'], array($info['email']));
		        }
		    }
		    else
		    {
		    	// Display a user friendly Error on the page using any of the following error information returned by PayPal
				$ErrorCode = urldecode($resArray2["L_ERRORCODE0"]);
				$ErrorLongMsg = urldecode($resArray2["L_LONGMESSAGE0"]);

                var_dump($ErrorCode);
                var_dump($ErrorLongMsg);
		    }
			header('Location: ' . $_SESSION['success_url']);
		}
	}
	else  
	{
		// Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);

        var_dump($ErrorCode);
        var_dump($ErrorLongMsg);
	}
}
