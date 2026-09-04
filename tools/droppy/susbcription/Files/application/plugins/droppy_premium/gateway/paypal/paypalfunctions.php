<?php

class Paypal
{
	public $PROXY_HOST, $PROXY_PORT, $API_UserName, $API_Password, $API_Signature, $sBNCode, $paymentAmount, $API_Endpoint, $PAYPAL_URL, $USE_PROXY, $version, $sandbox, $billing_freq, $billing_time, $max_fails, $currency;

	function __construct($config, $settings, $plan_details = false)
    {
        $this->PROXY_HOST = '127.0.0.1';
        $this->PROXY_PORT = '808';

        if($plan_details !== false) {
            $this->billing_freq = $plan_details['plan_freq'];
            $this->billing_time = $plan_details['plan_time'];
        }

        $this->API_UserName = $settings['username_api'];
        $this->API_Password = $settings['password_api'];
        $this->API_Signature = $settings['signature_api'];

        $this->max_fails = $settings['max_fails'];
        $this->currency = $settings['currency'];

        $this->sBNCode = "";

        if(isset($_SESSION['Payment_Amount']))
        {
            $this->paymentAmount = $_SESSION['Payment_Amount'];
        }
        /*
        ' Define the PayPal Redirect URLs.
        ' 	This is the URL that the buyer is first sent to do authorize payment with their paypal account
        ' 	change the URL depending if you are testing on the sandbox or the live PayPal site
        '
        ' For the sandbox, the URL is       https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
        ' For the live site, the URL is        https://www.paypal.com/webscr&cmd=_express-checkout&token=
        */

        $this->sandbox = $config['sandbox'];

        if ($config['sandbox'] == true)
        {
            $this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
        }
        else
        {
            $this->API_Endpoint = "https://api-3t.paypal.com/nvp";
            $this->PAYPAL_URL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=";
        }

        $this->USE_PROXY = false;
        $this->version = "124";
    }

    /*
    '-------------------------------------------------------------------------------------------------------------------------------------------
    ' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
    ' Inputs:
    '		paymentAmount:  	Total value of the shopping cart
    '		currencyCodeType: 	Currency code value the PayPal API
    '		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
    '		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
    '		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
    '--------------------------------------------------------------------------------------------------------------------------------------------
    */
    function CallShortcutExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL, $cancelURL, $logoPath, $plan_details)
    {
        //------------------------------------------------------------------------------------------------------------------------------------
        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation

        $nvpstr = "&AMT=" . urlencode($paymentAmount);
        $nvpstr = $nvpstr . "&PAYMENTACTION=" . urlencode($paymentType);
        $nvpstr = $nvpstr . "&BILLINGAGREEMENTDESCRIPTION=" . urlencode($plan_details['plan_desc']);
        $nvpstr = $nvpstr . "&BILLINGTYPE=RecurringPayments";
        $nvpstr = $nvpstr . "&RETURNURL=" . urlencode($returnURL);
        $nvpstr = $nvpstr . "&CANCELURL=" . urlencode($cancelURL);
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_CURRENCYCODE=" . $currencyCodeType;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_AMT=" . urlencode($paymentAmount);
        $nvpstr = $nvpstr . "&LOGOIMG=" . urlencode($logoPath);

        $_SESSION["currencyCodeType"] = $currencyCodeType;
        $_SESSION["PaymentType"] = $paymentType;

        //'---------------------------------------------------------------------------------------------------------------
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------

        $resArray = $this->hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            $token = urldecode($resArray["TOKEN"]);
            $_SESSION['TOKEN'] = $token;
        }

        return $resArray;
    }

    /*
    '-------------------------------------------------------------------------------------------------------------------------------------------
    ' Purpose: 	Prepares the parameters for the SetExpressCheckout API Call.
    ' Inputs:
    '		paymentAmount:  	Total value of the shopping cart
    '		currencyCodeType: 	Currency code value the PayPal API
    '		paymentType: 		paymentType has to be one of the following values: Sale or Order or Authorization
    '		returnURL:			the page where buyers return to after they are done with the payment review on PayPal
    '		cancelURL:			the page where buyers return to when they cancel the payment review on PayPal
    '		shipToName:		the Ship to name entered on the merchant's site
    '		shipToStreet:		the Ship to Street entered on the merchant's site
    '		shipToCity:			the Ship to City entered on the merchant's site
    '		shipToState:		the Ship to State entered on the merchant's site
    '		shipToCountryCode:	the Code for Ship to Country entered on the merchant's site
    '		shipToZip:			the Ship to ZipCode entered on the merchant's site
    '		shipToStreet2:		the Ship to Street2 entered on the merchant's site
    '		phoneNum:			the phoneNum  entered on the merchant's site
    '--------------------------------------------------------------------------------------------------------------------------------------------
    */
    function CallMarkExpressCheckout($paymentAmount, $currencyCodeType, $paymentType, $returnURL,
                                     $cancelURL, $shipToName, $shipToStreet, $shipToCity, $shipToState,
                                     $shipToCountryCode, $shipToZip, $shipToStreet2, $phoneNum
    )
    {
        //------------------------------------------------------------------------------------------------------------------------------------
        // Construct the parameter string that describes the SetExpressCheckout API call in the shortcut implementation

        $nvpstr = "&PAYMENTREQUEST_0_AMT=" . $paymentAmount;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_PAYMENTACTION=" . $paymentType;
        $nvpstr = $nvpstr . "&RETURNURL=" . $returnURL;
        $nvpstr = $nvpstr . "&CANCELURL=" . $cancelURL;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_CURRENCYCODE=" . $currencyCodeType;
        $nvpstr = $nvpstr . "&ADDROVERRIDE=1";
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTONAME=" . $shipToName;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOSTREET=" . $shipToStreet;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOSTREET2=" . $shipToStreet2;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOCITY=" . $shipToCity;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOSTATE=" . $shipToState;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE=" . $shipToCountryCode;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOZIP=" . $shipToZip;
        $nvpstr = $nvpstr . "&PAYMENTREQUEST_0_SHIPTOPHONENUM=" . $phoneNum;

        $_SESSION["currencyCodeType"] = $currencyCodeType;
        $_SESSION["PaymentType"] = $paymentType;

        //'---------------------------------------------------------------------------------------------------------------
        //' Make the API call to PayPal
        //' If the API call succeded, then redirect the buyer to PayPal to begin to authorize payment.
        //' If an error occured, show the resulting errors
        //'---------------------------------------------------------------------------------------------------------------
        $resArray = $this->hash_call("SetExpressCheckout", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            $token = urldecode($resArray["TOKEN"]);
            $_SESSION['TOKEN'] = $token;
        }

        return $resArray;
    }

    /*
    '-------------------------------------------------------------------------------------------
    ' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
    '
    ' Inputs:
    '		None
    ' Returns:
    '		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
    '-------------------------------------------------------------------------------------------
    */
    function GetShippingDetails($token)
    {
        //'--------------------------------------------------------------
        //' At this point, the buyer has completed authorizing the payment
        //' at PayPal.  The function will call PayPal to obtain the details
        //' of the authorization, incuding any shipping information of the
        //' buyer.  Remember, the authorization is not a completed transaction
        //' at this state - the buyer still needs an additional step to finalize
        //' the transaction
        //'--------------------------------------------------------------

        //'---------------------------------------------------------------------------
        //' Build a second API request to PayPal, using the token as the
        //'  ID to get the details on the payment authorization
        //'---------------------------------------------------------------------------
        $nvpstr = "&TOKEN=" . $token;

        //'---------------------------------------------------------------------------
        //' Make the API call and store the results in an array.
        //'	If the call was a success, show the authorization details, aGetExpressnd provide
        //' 	an action to complete the payment.
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->hash_call("GetExpressCheckoutDetails", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);

        if ($ack == "SUCCESS" || $ack == "SUCCESSWITHWARNING") {
            $_SESSION['payer_id'] = $resArray['PAYERID'];
            $_SESSION['email'] = $resArray['EMAIL'];
            $_SESSION['firstName'] = $resArray["FIRSTNAME"];
            $_SESSION['lastName'] = $resArray["LASTNAME"];
            $_SESSION['shipToName'] = $resArray["SHIPTONAME"];
            $_SESSION['shipToStreet'] = $resArray["SHIPTOSTREET"];
            $_SESSION['shipToCity'] = $resArray["SHIPTOCITY"];
            $_SESSION['shipToState'] = $resArray["SHIPTOSTATE"];
            $_SESSION['shipToZip'] = $resArray["SHIPTOZIP"];
            $_SESSION['shipToCountry'] = $resArray["SHIPTOCOUNTRYCODE"];
        }
        return $resArray;
    }

    /*
    '-------------------------------------------------------------------------------------------------------------------------------------------
    ' Purpose: 	Prepares the parameters for the GetExpressCheckoutDetails API Call.
    '
    ' Inputs:
    '		sBNCode:	The BN code used by PayPal to track the transactions from a given shopping cart.
    ' Returns:
    '		The NVP Collection object of the GetExpressCheckoutDetails Call Response.
    '--------------------------------------------------------------------------------------------------------------------------------------------
    */
    function ConfirmPayment($FinalPaymentAmt)
    {
        /* Gather the information to make the final call to
           finalize the PayPal payment.  The variable nvpstr
           holds the name value pairs
           */


        //Format the other parameters that were stored in the session from the previous calls
        $token = urlencode($_SESSION['TOKEN']);
        $paymentType = urlencode($_SESSION['PaymentType']);
        $currencyCodeType = urlencode($_SESSION['currencyCodeType']);
        $payerID = urlencode($_SESSION['payer_id']);

        $serverName = urlencode($_SERVER['SERVER_NAME']);

        $nvpstr = '&TOKEN=' . $token . '&PAYERID=' . $payerID . '&PAYMENTACTION=' . $paymentType . '&AMT=' . $FinalPaymentAmt;
        $nvpstr .= '&CURRENCYCODE=' . $currencyCodeType . '&IPADDRESS=' . $serverName;

        /* Make the call to PayPal to finalize payment
           If an error occured, show the resulting errors
           */
        $resArray = $this->hash_call("DoExpressCheckoutPayment", $nvpstr);

        $_SESSION['billing_agreemenet_id'] = $resArray["BILLINGAGREEMENTID"];

        /* Display the API response back to the browser.
           If the response from PayPal was a success, display the response parameters'
           If the response was an error, display the errors received using APIError.php.
           */
        $ack = strtoupper($resArray["ACK"]);

        return $resArray;
    }

    function CreateRecurringPaymentsProfile($sub_desc)
    {
        //'--------------------------------------------------------------
        //' At this point, the buyer has completed authorizing the payment
        //' at PayPal.  The function will call PayPal to obtain the details
        //' of the authorization, incuding any shipping information of the
        //' buyer.  Remember, the authorization is not a completed transaction
        //' at this state - the buyer still needs an additional step to finalize
        //' the transaction
        //'--------------------------------------------------------------
        $token = urlencode($_SESSION['TOKEN']);
        $email = urlencode($_SESSION['email']);
        $shipToName = urlencode($_SESSION['shipToName']);
        $shipToStreet = urlencode($_SESSION['shipToStreet']);
        $shipToCity = urlencode($_SESSION['shipToCity']);
        $shipToState = urlencode($_SESSION['shipToState']);
        $shipToZip = urlencode($_SESSION['shipToZip']);
        $shipToCountry = urlencode($_SESSION['shipToCountry']);

        //'---------------------------------------------------------------------------
        //' Build a second API request to PayPal, using the token as the
        //'  ID to get the details on the payment authorization
        //'---------------------------------------------------------------------------
        $nvpstr = "&TOKEN=" . $token;
        #$nvpstr.="&EMAIL=".$email;
        $nvpstr .= "&SHIPTONAME=" . $shipToName;
        $nvpstr .= "&SHIPTOSTREET=" . $shipToStreet;
        $nvpstr .= "&SHIPTOCITY=" . $shipToCity;
        $nvpstr .= "&SHIPTOSTATE=" . $shipToState;
        $nvpstr .= "&SHIPTOZIP=" . $shipToZip;
        $nvpstr .= "&SHIPTOCOUNTRY=" . $shipToCountry;
        $nvpstr .= "&PROFILESTARTDATE=" . urlencode(gmdate("Y-m-d\TH:i:s\Z"));
        $nvpstr .= "&DESC=" . urlencode($sub_desc);
        $nvpstr .= "&BILLINGPERIOD=" . $this->billing_time;
        $nvpstr .= "&BILLINGFREQUENCY=" . $this->billing_freq;
        if ($this->max_fails != 0) {
            $nvpstr .= "&MAXFAILEDPAYMENTS=" . $this->max_fails;
        }
        $nvpstr .= "&AMT=" . $this->paymentAmount;
        $nvpstr .= "&CURRENCYCODE=" . $this->currency;
        $nvpstr .= "&IPADDRESS=" . $_SERVER['REMOTE_ADDR'];

        //'---------------------------------------------------------------------------
        //' Make the API call and store the results in an array.
        //'	If the call was a success, show the authorization details, and provide
        //' 	an action to complete the payment.
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->hash_call("CreateRecurringPaymentsProfile", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        return $resArray;
    }

    //Get subscription info
    function GetRecurringPaymentsProfileDetails($profileID)
    {
        //'---------------------------------------------------------------------------
        //' Getting the Profile information of the subscription
        //' using the profile ID that has been set when calling this function
        //'---------------------------------------------------------------------------
        $nvpstr = "&PROFILEID=" . $profileID;

        //'---------------------------------------------------------------------------
        //' Make the API call and store the results in an array.
        //'	If the call was a success, show the authorization details, and provide
        //' 	an action to complete the payment.
        //'	If failed, show the error
        //'---------------------------------------------------------------------------
        $resArray = $this->hash_call("GetRecurringPaymentsProfileDetails", $nvpstr);
        $ack = strtoupper($resArray["ACK"]);
        return $resArray;
    }

    /*
    '-------------------------------------------------------------------------------------------------------------------------------------------
    ' Purpose: 	This function makes a DoDirectPayment API call
    '
    ' Inputs:
    '		paymentType:		paymentType has to be one of the following values: Sale or Order or Authorization
    '		paymentAmount:  	total value of the shopping cart
    '		currencyCode:	 	currency code value the PayPal API
    '		firstName:			first name as it appears on credit card
    '		lastName:			last name as it appears on credit card
    '		street:				buyer's street address line as it appears on credit card
    '		city:				buyer's city
    '		state:				buyer's state
    '		countryCode:		buyer's country code
    '		zip:				buyer's zip
    '		creditCardType:		buyer's credit card type (i.e. Visa, MasterCard ... )
    '		creditCardNumber:	buyers credit card number without any spaces, dashes or any other characters
    '		expDate:			credit card expiration date
    '		cvv2:				Card Verification Value
    '
    '-------------------------------------------------------------------------------------------
    '
    ' Returns:
    '		The NVP Collection object of the DoDirectPayment Call Response.
    '--------------------------------------------------------------------------------------------------------------------------------------------
    */


    function DirectPayment($paymentType, $paymentAmount, $creditCardType, $creditCardNumber,
                           $expDate, $cvv2, $firstName, $lastName, $street, $city, $state, $zip,
                           $countryCode, $currencyCode)
    {
        //Construct the parameter string that describes DoDirectPayment
        $nvpstr = "&AMT=" . $paymentAmount;
        $nvpstr = $nvpstr . "&CURRENCYCODE=" . $currencyCode;
        $nvpstr = $nvpstr . "&PAYMENTACTION=" . $paymentType;
        $nvpstr = $nvpstr . "&CREDITCARDTYPE=" . $creditCardType;
        $nvpstr = $nvpstr . "&ACCT=" . $creditCardNumber;
        $nvpstr = $nvpstr . "&EXPDATE=" . $expDate;
        $nvpstr = $nvpstr . "&CVV2=" . $cvv2;
        $nvpstr = $nvpstr . "&FIRSTNAME=" . $firstName;
        $nvpstr = $nvpstr . "&LASTNAME=" . $lastName;
        $nvpstr = $nvpstr . "&STREET=" . $street;
        $nvpstr = $nvpstr . "&CITY=" . $city;
        $nvpstr = $nvpstr . "&STATE=" . $state;
        $nvpstr = $nvpstr . "&COUNTRYCODE=" . $countryCode;
        $nvpstr = $nvpstr . "&IPADDRESS=" . $_SERVER['REMOTE_ADDR'];

        $resArray = $this->hash_call("DoDirectPayment", $nvpstr);

        return $resArray;
    }


    /**
     * '-------------------------------------------------------------------------------------------------------------------------------------------
     * hash_call: Function to perform the API call to PayPal using API signature
     * @methodName is name of API  method.
     * @nvpStr is nvp string.
     * returns an associtive array containing the response from the server.
     * '-------------------------------------------------------------------------------------------------------------------------------------------
     */
    function hash_call($methodName, $nvpStr)
    {
        // setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        // Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php
        if ($this->USE_PROXY)
            curl_setopt($ch, CURLOPT_PROXY, $this->PROXY_HOST . ":" . $this->PROXY_PORT);

        // NVPRequest for submitting to server
        $nvpreq = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($this->version) . "&PWD=" . urlencode($this->API_Password) . "&USER=" . urlencode($this->API_UserName) . "&SIGNATURE=" . urlencode($this->API_Signature) . $nvpStr . "&BUTTONSOURCE=" . urlencode($this->sBNCode);

        // setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

        // getting response from server
        $response = curl_exec($ch);

        $output = array(
            'url' => $this->API_Endpoint,
            'sent' => $nvpreq,
            'received' => $response
        );

        error_log(json_encode($output));

        // convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);
        $nvpReqArray = $this->deformatNVP($nvpreq);
        $_SESSION['nvpReqArray'] = $nvpReqArray;

        if (curl_errno($ch)) {
            // moving to display page to display curl errors
            $_SESSION['curl_error_no'] = curl_errno($ch);
            $_SESSION['curl_error_msg'] = curl_error($ch);

            // Execute the Error handling module to display errors.
        } else {
            // closing the curl
            curl_close($ch);
        }

        return $nvpResArray;
    }

    /*'----------------------------------------------------------------------------------
     Purpose: Redirects to PayPal.com site.
     Inputs:  NVP string.
     Returns:
    ----------------------------------------------------------------------------------
    */
    function RedirectToPayPal($token)
    {
        // Redirect to paypal.com here
        $payPalURL = $this->PAYPAL_URL . $token;

        header("Location: " . $payPalURL);
    }

    function RedirectToPayPalReturn($token)
    {
        // Return URL of paypal.com here
        echo $this->PAYPAL_URL . $token;
    }

    /*'----------------------------------------------------------------------------------
     * This function will take NVPString and convert it to an Associative Array and it will decode the response.
      * It is usefull to search for a particular key and displaying arrays.
      * @nvpstr is NVPString.
      * @nvpArray is Associative Array.
       ----------------------------------------------------------------------------------
      */
    function deformatNVP($nvpstr)
    {
        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }
        return $nvpArray;
    }

    function change_subscription_status( $profile_id, $action ) {
        $api_request = 'USER=' . urlencode($this->API_UserName)
            .  '&PWD=' . urlencode($this->API_Password)
            .  '&SIGNATURE=' . urlencode($this->API_Signature)
            .  '&VERSION=76.0'
            .  '&METHOD=ManageRecurringPaymentsProfileStatus'
            .  '&PROFILEID=' . urlencode( $profile_id )
            .  '&ACTION=' . urlencode( $action )
            .  '&NOTE=' . urlencode( 'Profile cancelled at store' );

        $ch = curl_init();
        if($this->sandbox === true) {
            curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp' );
        }
        else
        {
            curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.paypal.com/nvp' );
        }
        curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

        // Uncomment these to turn off server and peer verification
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );

        // Set the API parameters for this transaction
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $api_request );

        // Request response from PayPal
        $response = curl_exec( $ch );

        // If no response was received from PayPal there is no point parsing the response
        if( ! $response )
            die( 'Calling PayPal to change_subscription_status failed: ' . curl_error( $ch ) . '(' . curl_errno( $ch ) . ')' );

        curl_close( $ch );

        // An associative array is more usable than a parameter string
        parse_str( $response, $parsed_response );

        return $parsed_response;
    }
}