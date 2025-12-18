<?php
/**
 * 
 * MJSchool Zoom OAuth Callback Handler.
 * 
 * This script handles the OAuth callback from Zoom for virtual classroom integration. 
 * It retrieves or refreshes the access token using the Zoom API and updates WordPress options.
 * 
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
require_once MJSCHOOL_PLUGIN_DIR . '/lib/vendor/autoload.php';
$client        = new GuzzleHttp\Client( array( 'base_uri' => 'https://zoom.us' ) );
$CLIENT_ID     = get_option( 'mjschool_virtual_classroom_client_id' );
$CLIENT_SECRET = get_option( 'mjschool_virtual_classroom_client_secret_id' );
$REDIRECT_URI  = esc_url_raw(site_url() . '/?page=mjschoolcallback');
// The following conditional block handles both the initial token request and token refresh.
if ( empty( get_option( 'mjschool_virtual_classroom_access_token' ) ) or get_option( 'mjschool_virtual_classroom_access_token' ) ) {
	$response = $client->request(
		'POST',
		'/oauth/token',
		array(
			'headers'     => array(
				'Authorization' => 'Basic ' . base64_encode( $CLIENT_ID . ':' . $CLIENT_SECRET ),
			),
			'form_params' => array(
				'grant_type'   => 'authorization_code',
				'code'         => isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '',
				'redirect_uri' => $REDIRECT_URI,
			),
		)
	);
	$token    = $response->getBody()->getContents();
	update_option( 'mjschool_virtual_classroom_access_token', $token );
	$site_url = esc_url_raw(site_url() . '/wp-admin/admin.php?page=mjschool_virtual_classroom&tab=meeting_list&message=4');
	wp_safe_redirect( $site_url );
	exit;
} else {
	$get_token     = get_option( 'mjschool_virtual_classroom_access_token' );
	$token_decode  = json_decode( $get_token );
	$refresh_token = $token_decode->refresh_token;
	$response = $client->request(
		'POST',
		'/oauth/token',
		array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $CLIENT_ID . ':' . $CLIENT_SECRET ),
			),
			'query'   => array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $refresh_token,
			),
		)
	);
	$token    = $response->getBody()->getContents();
	update_option( 'mjschool_virtual_classroom_access_token', $token );
}