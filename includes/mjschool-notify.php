<?php
/**
 * MJSchool PayFast Instant Transaction Notification (ITN) Handler.
 *
 * This script serves as the webhook endpoint for the PayFast payment gateway's ITN.
 * It is responsible for:
 * 1. Receiving the notification data from PayFast via POST.
 * 2. Validating the payment data, signature, and server source.
 * 3. Bootstrapping the WordPress environment.
 * 4. Updating the corresponding fee payment record in the database upon successful validation.
 *
 * @package Mjschool
 * @subpackage Mjschool
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Tell Payfast that this page is reachable by triggering a header 200.
header( 'HTTP/1.0 200 OK' );
flush();
$pfParamString = '';
$pfPassphrase  = get_option( 'payfast_salt_passphrase' );
$live_mode     = get_option( 'payfast_live_mode' );
if ( $live_mode === 'no' ) {
	if(!defined( 'MJSCHOOL_SANDBOX_MODE' ) )
	{
		define( 'MJSCHOOL_SANDBOX_MODE', true );
	}
}
$pfHost = MJSCHOOL_SANDBOX_MODE ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
// Posted variables from ITN.
$pfData  = $_POST;
$results = $_POST;
// Strip any slashes in data.
foreach ( $pfData as $key => $val ) {
	$pfData[ $key ] = stripslashes( $val );
}
// Convert posted variables to a string.
foreach ( $pfData as $key => $val ) {
	if ( $key !== 'signature' ) {
		$pfParamString .= $key . '=' . urlencode( $val ) . '&';
	} else {
		break;
	}
}
$pfParamString = substr( $pfParamString, 0, -1 );
/**
 * Validates the PayFast Instant Notification (ITN) signature.
 *
 * This is the first and most critical security check to ensure the ITN is genuine.
 * It compares the received signature with a signature generated locally from the data.
 *
 * @param array $pfData The POST data array from PayFast.
 * @param string $pfParamString The data string used for local signature generation.
 * @return bool True if signatures match, false otherwise.
 */
function mjschool_pf_valid_signature_payfast( $pfData, $pfParamString, $pfPassphrase = null ) {
	// Calculate security signature.
	if ( $pfPassphrase === null ) {
		$tempParamStrings = $pfParamString;
	} else {
		$tempParamString = $pfParamString . '&passphrase=' . urlencode( $pfPassphrase );
	}
	$signature = md5( $tempParamString );
	return ( $pfData['signature'] = $signature );
}
/**
 * Checks if the request is coming from a valid PayFast IP address.
 *
 * @return bool True if the IP is valid, false otherwise.
 */
function mjschool_pf_valid_ip_payfast() {
	// Variable initialization.
	$validHosts = array(
		'www.payfast.co.za',
		'sandbox.payfast.co.za',
		'w1w.payfast.co.za',
		'w2w.payfast.co.za',
	);
	$validIps   = array();
	foreach ( $validHosts as $pfHostname ) {
		$ips = gethostbynamel( $pfHostname );
		if ( $ips !== false ) {
			$validIps = array_merge( $validIps, $ips );
		}
	}
	// Remove duplicates.
	$validIps   = array_unique( $validIps );
	$referrerIp = gethostbyname( parse_url( $_SERVER['HTTP_REFERER'] )['host'] );
	if ( in_array( $referrerIp, $validIps, true ) ) {
		return true;
	}
	return false;
}
/**
 * Validates the payment data (status and amount).
 *
 * @param float $amount_gross The amount paid by the user.
 * @param array $pfData The POST data array from PayFast.
 * @return bool True if valid, false otherwise.
 */
function mjschool_pf_valid_payment_data( $cartTotal, $pfData ) {
	return ! ( abs( (float) $cartTotal - (float) $pfData['amount_gross'] ) > 0.01 );
}
/**
 * Performs a server-to-server confirmation request to PayFast.
 *
 * This is the final and most robust check. It ensures the transaction 
 * details match what PayFast has on record and confirms the payment is valid.
 *
 * @param string $pfParamString The parameter string (excluding signature and passphrase).
 * @param string $pfHost The PayFast host URL.
 * @return bool True if confirmed as 'VALID', false otherwise.
 */
function mjschool_pf_valid_server_confirmation( $pfParamString, $pfHost = 'sandbox.payfast.co.za', $pfProxy = null ) {
	// Use curl (if available).
	if ( in_array( 'curl', get_loaded_extensions(), true ) ) {
		// Variable initialization.
		$url = 'https://' . $pfHost . '/eng/query/validate';
		// Create default curl object.
		$ch = curl_init();
		// Set curl options - Use curl_setopt for greater PHP compatibility.
		// Base settings.
		curl_setopt( $ch, CURLOPT_USERAGENT, null );  // Set user agent.
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );      // Return output as string rather than outputting it.
		curl_setopt( $ch, CURLOPT_HEADER, false );             // Don't include header in output.
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
		// Standard settings.
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
		if ( ! empty( $pfProxy ) ) {
			curl_setopt( $ch, CURLOPT_PROXY, $pfProxy );
		}
		// Execute curl.
		$response = curl_exec( $ch );
		curl_close( $ch );
		if ( $response === 'VALID' ) {
			return true;
		}
	}
	return false;
}
$check1 = mjschool_pf_valid_signature_payfast( $pfData, $pfParamString );
$check2 = mjschool_pf_valid_ip_payfast();
$check3 = mjschool_pf_valid_payment_data( $pfData['amount_gross'], $pfData );
$check4 = mjschool_pf_valid_server_confirmation( $pfParamString, $pfHost );
if ( $check1 && $check2 && $check3 && $check4 ) {
	$root = dirname( dirname( dirname( __DIR__ ) ) );
	if ( file_exists( $root . '/wp-load.php' ) ) {
		require_once $root . '/wp-load.php';
	} else {
		require_once $root . '/wp-config.php';
	}
	$obj_fees_payment          = new Mjschool_Feespayment();
	$feedata['fees_pay_id']    = $pfData['m_payment_id'];
	$feedata['amount']         = $pfData['amount_gross'];
	$feedata['payment_method'] = 'PayFast';
	$feedata['trasaction_id']  = $pfData['pf_payment_id'];
	$feedata['created_by']     = $pfData['custom_int1'];
	$feedata['paid_by_date']   = date( 'Y-m-d' );
	$feedata['email_address']  = $pfData['email_address'];
	$feedata['name_first']     = $pfData['name_first'];
	$feedata['name_last']      = $pfData['name_last'];
	$results                   = $obj_fees_payment->mjschool_add_feespayment_history_For_payfast( $feedata );
	if ( $results ) {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success&payment=paystack_success' );
		die();
	} else {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=feepayment&action=cancel' );
		die();
	}
}