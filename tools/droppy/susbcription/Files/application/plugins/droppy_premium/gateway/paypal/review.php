<?php

/*==================================================================
 PayPal Express Checkout Call
 ===================================================================
*/
// Check to see if the Request object contains a variable named 'token'	
$token = "";
if (isset($_GET['token']))
{
	$token = $_GET['token'];
}
// If the Request object contains the variable 'token' then it means that the user is coming from PayPal site.	
if ( $token != "" )
{
	$resArray = $clsPaypal->GetShippingDetails( $token );

	$ack = strtoupper($resArray["ACK"]);
	if( $ack == "SUCCESS" || $ack == "SUCESSWITHWARNING") 
	{
		/*
		' The information that is returned by the GetExpressCheckoutDetails call should be integrated by the partner into his Order Review 
		' page		
		*/
		$email 				= $resArray["EMAIL"]; // ' Email address of payer.
		$payerId 			= $resArray["PAYERID"]; // ' Unique PayPal customer account identification number.
		$payerStatus		= $resArray["PAYERSTATUS"]; // ' Status of payer. Character length and limitations: 10 single-byte alphabetic characters.
		$firstName			= $resArray["FIRSTNAME"]; // ' Payer's first name.
		$lastName			= $resArray["LASTNAME"]; // ' Payer's last name.
		$suffix				= (isset($resArray["SUFFIX"]) ? $resArray["SUFFIX"] : ''); // ' Payer's suffix.
		$cntryCode			= $resArray["COUNTRYCODE"]; // ' Payer's country of residence in the form of ISO standard 3166 two-character country codes.
		$shipToName			= $resArray["SHIPTONAME"]; // ' Person's name associated with this address.
		$shipToStreet		= $resArray["SHIPTOSTREET"]; // ' First street address.
		$shipToCity			= $resArray["SHIPTOCITY"]; // ' Name of city.
		$shipToState		= $resArray["SHIPTOSTATE"]; // ' State or province
		$shipToCntryCode	= $resArray["SHIPTOCOUNTRYCODE"]; // ' Country code. 
		$shipToZip			= $resArray["SHIPTOZIP"]; // ' U.S. Zip code or other country-specific postal code.
		$addressStatus 		= $resArray["ADDRESSSTATUS"]; // ' Status of street address on file with PayPal   
		$phonNumber			= (isset($resArray["PHONENUM"]) ? $resArray["PHONENUM"] : ''); // ' Payer's contact telephone number. Note:  PayPal returns a contact telephone number only if your Merchant account profile settings require that the buyer enter one.
		$timestamp 			= $resArray["TIMESTAMP"];

		$sub_id = $_SESSION['subscription_id'];

		$fullPayerName = $firstName . ' ' . $lastName;

		$update_data = array(
			'paypal_payerid' => $payerId,
			'paypal_email' => $email,
			'paypal_status' => $payerStatus,
			'paypal_name' => $fullPayerName,
			'paypal_country' => $cntryCode,
			'paypal_phone' => $phonNumber,
			'paypal_ordertime' => $timestamp
		);

		$clsSubs->updateBySubID($update_data, $sub_id);

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		header('Location: '.$url.'&action=payment_confirm');
	} 
	else  
	{
		//Display a user friendly Error on the page using any of the following error information returned by PayPal
		$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
		$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
	}
}
