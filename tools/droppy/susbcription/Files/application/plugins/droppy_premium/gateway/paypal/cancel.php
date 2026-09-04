<?php
function change_subscription_status( $profile_id, $action ) {
    require_once 'paypalfunctions.php';

    $api_request = 'USER=' . urlencode($API_UserName)
                .  '&PWD=' . urlencode($API_Password)
                .  '&SIGNATURE=' . urlencode($API_Signature)
                .  '&VERSION=76.0'
                .  '&METHOD=ManageRecurringPaymentsProfileStatus'
                .  '&PROFILEID=' . urlencode( $profile_id )
                .  '&ACTION=' . urlencode( $action )
                .  '&NOTE=' . urlencode( 'Profile cancelled at store' );
 
    $ch = curl_init();
    if($SandboxFlag == true) {
        curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.sandbox.paypal.com/nvp' );
    }
    else
    {
        curl_setopt( $ch, CURLOPT_URL, 'https://api-3t.paypal.com/nvp' );
    }
    curl_setopt( $ch, CURLOPT_VERBOSE, 1 );
 
    // Uncomment these to turn off server and peer verification
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
    // curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
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

if(isset($_GET['action']) == 'Cancel') {
    change_subscription_status( $_GET['id'], $_GET['action'] );
}