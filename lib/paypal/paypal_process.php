<?php
require_once dirname( __DIR__, 5 ) . '/wp-load.php';
$obj_feespayment = new Mjschool_Feespayment();
$p               = new Smgt_paypal_class(); // paypal class
// $p->admin_mail    = GMS_MJSCHOOL_EMAIL_ADD; // set notification email
// $action       = $_REQUEST["fees_pay_id"];

$feepaydata  = $obj_feespayment->mjschool_get_single_fee_payment( $_REQUEST['fees_pay_id'] );
$user_id     = $feepaydata->student_id;
$user_info   = get_userdata( $feepaydata->student_id );
$this_script = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$p->add_field( 'business', get_option( 'mjschool_paypal_email' ) ); // Call the facilitator eaccount
$p->add_field( 'cmd', '_cart' ); // cmd should be _cart for cart checkout
$p->add_field( 'upload', '1' );
if ( $_REQUEST['action'] == 'mjschool_student_add_payment' ) {
	$p->add_field( 'return', home_url() . '/?dashboard=mjschool_user&action=paypal_payment' );
	$p->add_field( 'cancel_return', home_url() . '/?dashboard=mjschool_user&page=feepayment&action=cancel' ); // cancel URL if the trasaction was cancelled
} else {
	$p->add_field( 'return', home_url() . '/?dashboard=mjschool_user&action=paypal_payment_form' ); // return URL after the transaction got over
	$p->add_field( 'cancel_return', home_url( '/student-registration-form/?status=cancel' ) );
}
$payment_date = isset( $_REQUEST['payment_date'] ) ? sanitize_text_field( $_REQUEST['payment_date'] ) : '';
$p->add_field( 'notify_url', home_url() . '/?dashboard=mjschool_user&page=feepayment&action=ipn' ); // Notify URL which received IPN (Instant Payment Notification)
$p->add_field( 'currency_code', get_option( 'mjschool_currency_code' ) );
$p->add_field( 'invoice', date( 'His' ) . rand( 1234, 9632 ) );
$p->add_field( 'item_name_1', mjschool_get_fees_term_name( $feepaydata->fees_id ) );
$p->add_field( 'item_number_1', 4 );
$p->add_field( 'quantity_1', 1 );
// $p->add_field( 'amount_1', get_membership_price(get_user_meta($user_id,'membership_id',true ) ) );
$p->add_field( 'amount_1', isset( $_REQUEST['amount'] ) ? $_REQUEST['amount'] : 0 );
// $p->add_field( 'amount_1', 1);//Test purpose
$p->add_field( 'first_name', $user_info->first_name );
$p->add_field( 'last_name', $user_info->last_name );
$p->add_field( 'address1', $user_info->address );
$p->add_field( 'city', $user_info->city );
$custom_fields = array();
if ( ! empty( $_POST['custom'] ) && is_array( $_POST['custom'] ) ) {
	foreach ( $_POST['custom'] as $id => $value ) {
		$custom_fields[ $id ] = sanitize_text_field( $value );
	}
}
if ( ! empty( $_POST['payment_note'] ) ) {
	$custom_fields['payment_note'] = sanitize_text_field( $_POST['payment_note'] );
}
$custom_data_array = array(
	$user_id,
	$_REQUEST['fees_pay_id'],
	$payment_date,
	http_build_query( $custom_fields ), // serialize custom fields
);
$custom_data       = implode( '|', $custom_data_array );
$p->add_field( 'custom', $custom_data );
$p->add_field( 'rm', 2 );
$p->add_field( 'state', get_user_meta( $user_id, 'state', true ) );
$p->add_field( 'country', get_option( 'mjschool_contry' ) );
$p->add_field( 'zip', get_user_meta( $user_id, 'zip_code', true ) );
$p->add_field( 'email', $user_info->user_email );
$p->submit_paypal_post(); // POST it to paypal
// $p->dump_fields(); // Show the posted values for a reference, comment this line before app goes live