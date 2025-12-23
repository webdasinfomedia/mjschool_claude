<?php
/**
 * Template for the Fee Payment Management page.
 *
 * This file displays the main interface for managing student fee payments. 
 * It performs user role and access right checks, initializes the necessary
 * fee management classes, and determines the correct view/tab to display
 * (e.g., fees list, payment history, or receipt view).
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$school_type = get_option( "mjschool_custom_class");
$mjschool_role_name       = mjschool_get_user_role( get_current_user_id() );
$access                   = mjschool_page_access_role_wise_and_access_right();
$tablename                = 'mjschool_payment';
$mjschool_obj_fees        = new Mjschool_Fees();
$mjschool_obj_feespayment = new Mjschool_Feespayment();
if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
	$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'feeslist';
} else {
	$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'feepaymentlist';
}
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
}
// ------------- Save feestype payment method. --------------//
if ( isset( $_POST['add_feetype_payment'] ) ) {
	// POP-UP data save in payment history.
	$payment_method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : '';
	if ( $payment_method === 'PayPal' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/paypal/paypal_process.php';
	} elseif ( $payment_method === 'Stripe' ) {
		require_once PM_PLUGIN_DIR . '/lib/stripe/index.php';
	} elseif ( $payment_method === 'Skrill' ) {
		require_once PM_PLUGIN_DIR . '/lib/skrill/skrill.php';
	} elseif ( $payment_method === 'Instamojo' ) {
		require_once PM_PLUGIN_DIR . '/lib/instamojo/instamojo.php';
	} elseif ( $payment_method === 'PayUMony' ) {
		require_once PM_PLUGIN_DIR . '/lib/OpenPayU/index.php';
	} elseif ( isset($_REQUEST['payment_method']) && sanitize_text_field(wp_unslash($_REQUEST['payment_method'])) === '2CheckOut' ) {
		require_once PM_PLUGIN_DIR . '/lib/2checkout/index.php';
	} elseif ( $payment_method === 'iDeal' ) {
		require_once PM_PLUGIN_DIR . '/lib/ideal/ideal.php';
	} elseif ( $payment_method === 'Paystack' ) {
		require_once PM_PLUGIN_DIR . '/lib/paystack/paystack.php';
	} elseif ( $payment_method === 'paytm' ) {
		require_once PM_PLUGIN_DIR . '/lib/PaytmKit/index.php';
	} elseif ( $payment_method === 'razorpay' ) {
		require_once PM_PLUGIN_DIR . '/lib/razorpay/index.php';
	} elseif ( $payment_method === 'Payfast' ) {
		require_once PM_PLUGIN_DIR . '/lib/payfast/payfast_process.php';
	} else {
		$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment_history( wp_unslash($_POST) );
		$module                    = 'fee_transaction';
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=1') );
			die();
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'success' ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
			
		</button>
		<?php esc_html_e( 'Payment Added successfully', 'mjschool' ); ?>
	</div>
	<?php
}
// Paytm success.//
// ----------------- Paystack Complete. --------------//
$reference = '';
$reference = isset( $_GET['reference'] ) ? sanitize_text_field(wp_unslash($_GET['reference'])) : '';
if ( $reference ) {
	$paystack_secret_key = get_option( 'paymaster_paystack_secret_key' );
	$curl                = curl_init();
	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL            => 'https://api.paystack.co/transaction/verify/' . rawurlencode( $reference ),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER     => array(
				'accept: application/json',
				"authorization: Bearer $paystack_secret_key",
				'cache-control: no-cache',
			),
		)
	);
	$response = curl_exec( $curl );
	$err      = curl_error( $curl );
	if ( $err ) {
		// There was an error contacting the Paystack API,
		wp_die( 'Curl returned error: ' . esc_html( $err ) );
	}
	$tranx = json_decode( $response );
	if ( ! $tranx->status ) {
		// There was an error from the API.
		wp_die( 'API returned error: ' . esc_html( $tranx->message ) );
	}
	if ( 'success' === $tranx->data->status ) {
		$trasaction_id             = $tranx->data->reference;
		$feedata['fees_pay_id']    = $tranx->data->metadata->custom_fields->fees_pay_id;
		$feedata['amount']         = $tranx->data->amount / 100;
		$feedata['payment_method'] = 'Paystack';
		$feedata['trasaction_id']  = $trasaction_id;
		$PaymentSucces             = $mjschool_obj_feespayment->mjschool_add_feespayment_history( $feedata );
		if ( $PaymentSucces ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success') );
			die();
		}
	}
}
// Payment history payfast.
if ( isset( $_REQUEST['payment'] ) && sanitize_text_field(wp_unslash($_REQUEST['payment'])) === 'paystack_success' ) {
	$trasaction_id             = '';
	$feedata['fees_pay_id']    = sanitize_text_field(wp_unslash($_REQUEST['pay_id']));
	$feedata['amount']         = sanitize_text_field(wp_unslash($_REQUEST['amt']));
	$feedata['payment_method'] = 'Payfast';
	$feedata['trasaction_id']  = $trasaction_id;
	$feedata['paid_by_date']   = date( 'Y-m-d' );
	$feedata['created_by']     = get_current_user_id();
	$PaymentSucces             = $mjschool_obj_feespayment->mjschool_add_feespayment_history( $feedata );
	if ( $PaymentSucces ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success') );
		die();
	}
}
// Payment History entry for skrill.//
if ( isset( $_REQUEST['pay_id'] ) && isset( $_REQUEST['amt'] ) ) {
	$mjschool_obj_fees_payment = new Mjschool_Feespayment();
	$feedata['fees_pay_id']    = sanitize_text_field(wp_unslash($_REQUEST['pay_id']));
	$feedata['amount']         = sanitize_text_field(wp_unslash($_REQUEST['amt']));
	$feedata['payment_method'] = 'Skrill';
	$feedata['created_by']     = get_current_user_id();
	$feedata['paid_by_date']   = date( 'Y-m-d' );
	$result                    = $mjschool_obj_fees_payment->mjschool_add_feespayment_history( $feedata );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success' ));
		die();
	}
}
// Payment History entry for instamojo.//
if ( isset( $_REQUEST['payment_id'] ) && isset( $_REQUEST['payment_request_id'] ) ) {
	$mjschool_obj_fees_payment = new Mjschool_Feespayment();
	$feedata['fees_pay_id']    = sanitize_text_field(wp_unslash($_REQUEST['pay_id']));
	$feedata['amount']         = sanitize_text_field(wp_unslash($_REQUEST['amount']));
	$feedata['payment_method'] = 'Instamojo';
	$feedata['trasaction_id']  = sanitize_text_field(wp_unslash($_REQUEST['payment_id']));
	$feedata['created_by']     = get_current_user_id();
	$feedata['paid_by_date']   = date( 'Y-m-d' );
	$result                    = $mjschool_obj_fees_payment->mjschool_add_feespayment_history( $feedata );
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&action=success' ));
		die();
	}
}
// ----------------- Payment cancel. --------------//
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'cancel' ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			
		</button>
		<?php esc_html_e( 'Payment Cancel', 'mjschool' ); ?>
	</div>
	<?php
}
// ----------------- Save fee type. -------------------//
if ( isset( $_POST['save_feetype'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_fees_type_front_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		if (isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
				$fees_id                   = sanitize_text_field(wp_unslash($_REQUEST['fees_id']));
				$result                    = $mjschool_obj_fees->mjschool_add_fees( wp_unslash($_POST) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'fee_pay';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $fees_id );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce='.esc_attr( $nonce ).'&message=5' ));
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} elseif ( ! $mjschool_obj_fees->mjschool_is_duplicat_fees( sanitize_text_field(wp_unslash($_POST['fees_title_id'])), sanitize_text_field(wp_unslash($_POST['class_id'])) ) ) {
			$result                    = $mjschool_obj_fees->mjschool_add_fees( wp_unslash($_POST) );
			$module                    = 'fee_pay';
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce='.esc_attr( $nonce ).'&message=4' ));
				die();
			}
		} else {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce='.esc_attr( $nonce ).'&message=6' ));
			die();
		}
	}
}
/* Update recurring invoice data. */
if ( isset( $_POST['save_recurring_feetype_payment'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_payment_fees_admin_nonce' ) ) {
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) ) );
		if ( $start_date <= $end_date ) {
			if ( $_REQUEST['action'] === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					$result = $mjschool_obj_feespayment->mjschool_add_recurring_feespayment( wp_unslash($_POST) );
					if ( $result ) {
						$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=recurring_feespaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=recurring_feetype_edit' ));
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			}
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert updated mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html__( 'End Date should be greater than Start Date.', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
}
// ------------------ Save payment. ---------------//
if ( isset( $_POST['save_feetype_payment'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_payment_fees_front_nonce' ) ) {
		if ( isset( $_REQUEST['mjschool_enable_feesalert_mail'] ) ) {
			update_option( 'mjschool_enable_feesalert_mail', 1 );
		} else {
			update_option( 'mjschool_enable_feesalert_mail', 0 );
		}
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['start_year'])) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['end_year'])) ) );
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		if ( $start_date <= $end_date ) {
			if ( $_REQUEST['action'] === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
					$fees_pay_id               = sanitize_text_field(wp_unslash($_REQUEST['fees_pay_id']));
					$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment( wp_unslash($_POST) );
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'fee_list';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $fees_pay_id );
					if ( $result ) {
						wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=2' ));
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment( wp_unslash($_POST) );
				$module                    = 'fee_list';
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=1' ));
					die();
				}
			}
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
				
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
					<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
					
				</button>
				<?php esc_html_e( 'End Date should be greater than Start Date.', 'mjschool' ); ?>
			</div>
			<?php
		}
	}
}
if ( isset( $_REQUEST['delete_selected_recurring_feelist'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_recurring_fees( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert updated mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'Fee Deleted Successfully.', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	}
}
// ----------------- Delete fees type. -----------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		if ( isset( $_REQUEST['fees_id'] ) ) {
			$result = $mjschool_obj_fees->mjschool_delete_feetype_data( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['fees_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce='.esc_attr( $nonce ).'&message=7') );
				die();
			}
		}
		if ( isset( $_REQUEST['fees_pay_id'] ) ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_fee_payment_data( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['fees_pay_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=3') );
				die();
			}
		}
		if ( isset( $_REQUEST['recurring_fees_id'] ) ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_recurring_fees( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['recurring_fees_id'])) ) );
			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=recurring_feespaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=fee_del') );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ----------------- Multiple delete fees type. ----------------------//
if ( isset( $_REQUEST['delete_selected_feetype'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_feetype_data( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce='.esc_attr( $nonce ).'&message=3') );
			die();
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2">
			<p><?php esc_html_e( 'Fees Type Deleted Successfully.', 'mjschool' ); ?></p>
		</div>
		<?php
	}
}
// --------------------- Multiple fees Payment delete. --------------------//
if ( isset( $_REQUEST['delete_selected_feelist'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_books' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	if ( ! empty( $_REQUEST['id'] ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_fee_payment_data( intval( sanitize_text_field( wp_unslash( $id ) ) ) );
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce='.esc_attr( $nonce ).'&message=3') );
			die();
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert updated mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'Fee Deleted Successfully.', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'fee_list';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
$module1                   = 'fee_pay';
$user_custom_field1        = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module1 );
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-40px-res">
	<?php
	// ---------------- Message. -----------//
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Payment added Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Fees Updated Successfully.', 'mjschool' );
			break;
		case '3':
			$message_string = esc_html__( 'Fees Type Deleted Successfully.', 'mjschool' );
			break;
		case '4':
			$message_string = esc_html__( 'Fees Type added Successfully.', 'mjschool' );
			break;
		case '5':
			$message_string = esc_html__( 'Fees Type updated Successfully.', 'mjschool' );
			break;
		case '6':
			$message_string = esc_html__( 'Duplicate Fee.', 'mjschool' );
			break;
		case '7':
			$message_string = esc_html__( 'Fees Type Deleted Successfully.', 'mjschool' );
			break;
		case 'recurring_feetype_edit':
			$message_string = esc_html__( 'Recurring Invoice Updated Successfully.', 'mjschool' );
			break;
		case 'fee_del':
			$message_string = esc_html__( 'Fees Deleted Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			</button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<div class="mjschool-panel-body mjschool-panel-white">
		<?php
		if ( $active_tab != 'view_fesspayment' ) {
			$page_action = '';
			if ( ! empty( $_REQUEST['action'] ) ) {
				$page_action = sanitize_text_field(wp_unslash($_REQUEST['action']));
			}
			?>
			<?php $nonce = wp_create_nonce( 'mjschool_feespayment_tab' ); ?>
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
				<?php
				if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
					?>
					<li class="<?php if ( $active_tab === 'feeslist' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'feeslist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Type List', 'mjschool' ); ?></a>
					</li>
					<?php
				}
				if ( $active_tab === 'addfeetype' && $page_action === 'edit' ) {
					?>
					<li class="<?php if ( $active_tab === 'addfeetype' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype&action=edit&fees_id=' . sanitize_text_field( wp_unslash( $_REQUEST['fees_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addfeetype' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Fees Type', 'mjschool' ); ?></a>
					</li>
					<?php
				} elseif ( $active_tab === 'addfeetype' ) {
					if ( $user_access['add'] === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'addfeetype' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addfeetype' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Fees Type', 'mjschool' ); ?></a>
						</li>
						<?php
					}
				}
				?>
				<li class="<?php if ( $active_tab === 'feepaymentlist' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=feepaymentlist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'feepaymentlist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></a>
				</li>
				<?php
				if ( $active_tab === 'addpaymentfee' && $page_action === 'edit' ) {
					?>
					<li class="<?php if ( $active_tab === 'addpaymentfee' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee&action=edit&fees_pay_id=' . sanitize_text_field( wp_unslash( $_REQUEST['fees_pay_id'] ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addpaymentfee' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Fees Payment', 'mjschool' ); ?></a>
					</li>
					<?php
				} elseif ( $active_tab === 'addpaymentfee' ) {
					if ( $user_access['add'] === '1' ) {
						?>
						<li class="<?php if ( $active_tab === 'addpaymentfee' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addpaymentfee' ? 'active' : ''; ?>"> <?php esc_html_e( 'Add Fees Payment', 'mjschool' ); ?></a>
						</li>
						<?php
					}
				}
				if ( $mjschool_role_name === 'teacher' || $mjschool_role_name === 'supportstaff' ) {
					$recurring_option = get_option( 'mjschool_enable_recurring_invoices' );
					if ( $recurring_option === 'yes' ) {
						?>
						<li class="<?php if ( $active_tab === 'recurring_feespaymentlist' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=recurring_feespaymentlist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'recurring_feespaymentlist' ? 'active' : ''; ?>"> <?php esc_html_e( 'Recurring Fees Payment List', 'mjschool' ); ?></a>
						</li>
						<?php
					}
					if ( $active_tab === 'addrecurringpayment' && $mjschool_action === 'edit' ) {
						?>
						<li class="<?php if ( $active_tab === 'addrecurringpayment' ) { ?> active<?php } ?>">
							<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addrecurringpayment' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'addrecurringpayment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Edit Recurring Fees Payment', 'mjschool' ); ?></a>
						</li>
						<?php
					}
				}
				if ( $active_tab === 'view_fessreceipt' ) {
					?>
					<li class="<?php if ( $active_tab === 'view_fessreceipt' ) { ?> active<?php } ?>">
						<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'view_fessreceipt' ? 'active' : ''; ?>"> <?php esc_html_e( 'Payment History', 'mjschool' ); ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		?>
		<div>
			<?php
			if ( $active_tab === 'feeslist' ) {
				// Check nonce for fees list tab.
				if ( isset( $_GET['tab'] ) ) {
					if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_feespayment_tab' ) ) {
						wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
					}
				}
				$user_id = get_current_user_id();
				// ------- Exam data for student. ---------//
				if ( $school_obj->role === 'student' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_own_fees( $user_id );
					} else {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					}
				}
				// ------- Exam data for teacher. ---------//
				elseif ( $school_obj->role === 'teacher' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_own_fees( $user_id );
					} else {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					}
				}
				// ------- Exam data for parent. ---------//
				elseif ( $school_obj->role === 'parent' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_own_fees( $user_id );
					} else {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					}
				}
				// ------- Exam data for support staff. ---------//
				else {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_own_fees( $user_id );
					} else {
						$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					}
				}
				if ( ! empty( $retrieve_class_data ) ) {
					?>
					<div class="mjschool-panel-body"><!---------------- Panel body. ----------------->
						<div class="table-responsive"><!--------------- Table responsive. -------------->
							<!-------------- Feestype list form. -------------->
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<?php wp_nonce_field( 'bulk_delete_books' ); ?>
								<table id="frontend_feetype_list" class="display mjschool-admin-feestype-datatable" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Fees Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
											<?php
											if ( ! empty( $user_custom_field1 ) ) {
												foreach ( $user_custom_field1 as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														?>
														<th><?php echo esc_html( $custom_field->field_label ); ?></th>
														<?php
													}
												}
											}
											?>
											<?php
											if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
												?>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
											<?php } ?>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $retrieve_class_data as $retrieved_data ) {
											$color_class_css = mjschool_table_list_background_color( $i );
											?>
											<tr>
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->fees_id ); ?>">
													</td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
														
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
														
													</p>
												</td>
												<td>
													<?php echo esc_html( get_the_title( $retrieved_data->fees_title_id ) ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Title', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													if ( ! empty( $retrieved_data->class_id ) ) {
														if ( $retrieved_data->class_id === 'all_class' ) {
															esc_html_e( 'All Class', 'mjschool' );
														} else {
															echo esc_html( mjschool_get_class_name( $retrieved_data->class_id ) );
														}
													} else {
														esc_html_e( 'N/A', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													if ( $retrieved_data->section_id != 0 ) {
														echo esc_html( mjschool_get_section_name( $retrieved_data->section_id ) );
													} else {
														esc_html_e( 'No Section', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo '<span>' . esc_html( mjschool_get_currency_symbol() ) . '</span> ' . number_format( $retrieved_data->fees_amount, 2, '.', '' ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Amount', 'mjschool' ); ?>"></i>
												</td>
												<?php
												$comment     = $retrieved_data->description;
												$comment     = ltrim( $comment, ' ' );
												$description = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
												?>
												<td>
													<?php
													if ( ! empty( $comment ) ) {
														echo esc_html( $description );
													} else {
														esc_html_e( 'N/A', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_html( $comment ); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
												</td>
												<?php
												// Custom Field Values.
												if ( ! empty( $user_custom_field1 ) ) {
													foreach ( $user_custom_field1 as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'fee_pay';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->fees_id;
															$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
															if ( $custom_field->field_type === 'date' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} elseif ( $custom_field->field_type === 'file' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		?>
																		<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile">
																			<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																		</a>
																		<?php
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} else {
																?>
																<td> 
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( $custom_field_value );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															}
														}
													}
												}
												?>
												<?php
												if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
													?>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																		
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		if ( $user_access['edit'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype&action=edit&fees_id=' . mjschool_encrypt_id( $retrieved_data->fees_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px">
																					<i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $user_access['delete'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=feeslist&action=delete&fees_id=' . mjschool_encrypt_id( $retrieved_data->fees_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fas fa-trash"></i>
																					<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
												<?php } ?>
											</tr>
											<?php
											++$i;
										}
										?>
									</tbody>
								</table>
								<?php
								if ( $mjschool_role_name === 'supportstaff' ) {
									?>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" name="id[]" id="select_all" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php
										if ( $user_access['delete'] === '1' ) {
											?>
											
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_feetype" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
											<?php
										}
										?>
									</div>
									<?php
								}
								?>
							</form><!-------------- Feestype list form. -------------->
						</div><!--------------- Table responsive. -------------->
					</div><!---------------- Panel body. ----------------->
					<?php
				} else {
					if ($user_access['add'] === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
							<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=addfeetype' )); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								
							</a>
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label">
									<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
								</label>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							
							<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
							
						</div>
						<?php
					}
				}
			}
			if ( $active_tab === 'addfeetype' ) {
				$fees_id = 0;
				if ( isset( $_REQUEST['fees_id'] ) ) {
					$fees_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['fees_id'])) ) );
				}
				$edit = 0;
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
					$edit   = 1;
					$result = $mjschool_obj_fees->mjschool_get_single_feetype_data( $fees_id );
				}
				?>
				<div class="mjschool-panel-body"><!---------------- Panel body. ---------------->
					<!------------------- Add fees type form. --------------->
					<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
						<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
						<input type="hidden" name="fees_id" value="<?php echo esc_attr( $fees_id ); ?>">
						<input type="hidden" name="invoice_type" value="expense">
						<div class="header">
							<h3 class="mjschool-first-header">
								<?php esc_html_e( 'Fess Type Information', 'mjschool' ); ?>
							</h3>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-4 input">
									<label class="ml-1 mjschool-custom-top-label top" for="category_data"> <?php esc_html_e( 'Fee Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span> </label>
									<select class="mjschool-line-height-30px form-control validate[required] smgt_feetype mjschool-max-width-100px" name="fees_title_id" id="category_data">
										<option value="">
											<?php esc_html_e( 'Select Fee Type', 'mjschool' ); ?>
										</option>
										<?php
										$activity_category = mjschool_get_all_category( 'smgt_feetype' );
										if ( ! empty( $activity_category ) ) {
											if ( $edit ) {
												$fees_val = $result->fees_title_id;
											} else {
												$fees_val = '';
											}
											foreach ( $activity_category as $retrive_data ) {
												?>
												<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $fees_val ); ?>>
													<?php echo esc_html( $retrive_data->post_title ); ?>
												</option>
												<?php
											}
										}
										?>
									</select>
								</div>
								<div class="col-sm-2 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px">
									<button id="mjschool-addremove-cat" class="btn btn-info mjschool-add-btn" model="smgt_feetype">
										<?php esc_html_e( 'Add', 'mjschool' ); ?>
									</button>
								</div>
								<div class="col-md-6 input mjschool-error-msg-left-margin">
									<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list">
										<?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
									</label>
									<?php
									$classval = 0;
									if ( $edit ) {
										$classval = $result->class_id;
									}
									?>
									<select name="class_id" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px" id="mjschool-class-list">
										<option value=""> <?php esc_html_e( 'Select Class', 'mjschool' ); ?> </option>
										<option value="all_class" <?php selected( $classval, 'all_class' ); ?>> <?php esc_html_e( 'All Class', 'mjschool' ); ?> </option>
										<?php
										foreach ( mjschool_get_all_class() as $classdata ) {
											?>
											<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>>
												<?php echo esc_html( $classdata['class_name'] ); ?>
											</option>
										<?php } ?>
									</select>
								</div>
								<?php wp_nonce_field( 'save_fees_type_front_nonce' ); ?>
								<?php if ( $school_type === 'school' ){ ?>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="class_section"> <?php esc_html_e( 'Class Section', 'mjschool' ); ?> </label>
										<?php
										if ( $edit ) {
											$sectionval = $result->section_id;
										} elseif ( isset( $_POST['class_section'] ) ) {
											$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
										} else {
											$sectionval = '';
										}
										?>
										<select name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="class_section">
											<option value=""> <?php esc_html_e( 'All Section', 'mjschool' ); ?> </option>
											<?php
											if ( $edit ) {
												foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
													?>
													<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
														<?php echo esc_html( $sectiondata->section_name ); ?>
													</option>
													<?php
												}
											}
											?>
										</select>
									</div>
								<?php }?>
								<div class="col-md-6 mjschool-error-msg-left-margin">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->fees_amount ); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['fees_amount'])) ); } ?>" name="fees_amount">
											<label for="fees_amount">
												<?php esc_html_e( 'Fees Amount', 'mjschool' ); ?>( <?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span>
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mjschool-note-text-notice">
									<div class="form-group input">
										<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
											<div class="form-field">
												<textarea id="mjschool-description" name="description" class="mjschool-textarea-height-47px form-control" maxlength="150"> <?php if ( $edit ) { echo esc_textarea( $result->description ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_text_field(wp_unslash($_POST['description'])) ); } ?> </textarea>
												<span class="mjschool-txt-title-label"></span>
												<label for="mjschool-description" class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
						// --------- Get module-wise custom field data. --------------//
						$mjschool_custom_field_obj = new Mjschool_Custome_Field();
						$module                    = 'fee_pay';
						$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6">
									<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Fees Type', 'mjschool' ); } else { esc_html_e( 'Create Fees Type', 'mjschool' ); } ?>" name="save_feetype" class="btn btn-success mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
				</div>
				<?php
			}
			if ( $active_tab === 'feepaymentlist' ) {
				// Check nonce for fees list tab.
				if ( isset( $_GET['tab'] ) ) {
					if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_feespayment_tab' ) ) {
						wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
					}
				}
				$user_id = get_current_user_id();
				// ------- Payment data for student. ---------//
				if ( $school_obj->role === 'student' ) {
					$data          = $school_obj->feepayment;
					$filtered_data = array_filter(
						$data,
						function ( $item ) {
							return empty( $item->invoice_status ) || $item->invoice_status !== 'draft';
						}
					);
					$data = $filtered_data;
				}
				// ------- Payment data for teacher. ---------//
				elseif ( $school_obj->role === 'teacher' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$data = mjschool_get_fees_payment_by_class_ids($class_id);
					} else {
						$data          = $school_obj->feepayment;
						$filtered_data = array_filter(
							$data,
							function ( $item ) {
								return empty( $item->invoice_status ) || $item->invoice_status !== 'draft';
							}
						);
						$data = $filtered_data;
					}
				}
				// ------- Payment data for parent. ---------//
				elseif ( $school_obj->role === 'parent' ) {
					$data          = $school_obj->feepayment;
					$filtered_data = array_filter(
						$data,
						function ( $item ) {
							return empty( $item->invoice_status ) || $item->invoice_status !== 'draft';
						}
					);
					$data          = $filtered_data;
				} elseif ( $school_obj->role === 'supportstaff' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$data = $mjschool_obj_feespayment->mjschool_get_all_fees_own();
					} else {
						$data = $mjschool_obj_feespayment->mjschool_get_all_fees();
					}
				}
				// ------- Payment data for support staff ---------//
				else {
					$data          = $school_obj->feepayment;
					$filtered_data = array_filter(
						$data,
						function ( $item ) {
							return empty( $item->invoice_status ) || $item->invoice_status !== 'draft';
						}
					);
					$data = $filtered_data;
				}
				if ( ! empty( $data ) ) {
					?>
					<div class="mjschool-panel-body"><!------------- Panel body. ------------------>
						<div class="table-responsive"><!------------- Table responsive. ------------------>
							<!------------- FEES PAYMENT LIST FORM. ----------------->
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<?php wp_nonce_field( 'bulk_delete_books' ); ?>
								<table id="paymentt_list_receipt" class="display dataTable mjschool-feespayment-datatable" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<th class="mjschool-custom-padding-0"><input type="checkbox" class="mjschool-sub-chk select_all" name="select_all"></th>
												<?php
											}
											?>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Paid Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></th>
											<?php
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														?>
														<th>
															<?php echo esc_html( $custom_field->field_label ); ?>
														</th>
														<?php
													}
												}
											}
											?>
											<th class="mjschool-text-align-end"> <?php esc_html_e( 'Action', 'mjschool' ); ?> </th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $data as $retrieved_data ) {
											$color_class_css = mjschool_table_list_background_color( $i );
											?>
											<tr>
												<?php
												if ( $mjschool_role_name === 'supportstaff' ) {
													?>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>"> </td>
													<?php
												}
												?>
												<td class="mjschool-user-image mjschool-width-50px-td">
													
													<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&view_type=view_payment' ); ?>">
														<?php
														$uid = $retrieved_data->student_id;
														$umetadata = mjschool_get_user_image($uid);
														if (empty($umetadata ) ) {
															echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
														} else {
															echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
														}
														?>
													</a>
													
												</td>
												<td>
													<?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													$fees_id   = explode( ',', $retrieved_data->fees_id );
													$fees_type = array();
													foreach ( $fees_id as $id ) {
														$fees_type[] = mjschool_get_fees_term_name( $id );
													}
													echo esc_html( implode( ' , ', $fees_type ) );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Title', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													if ( $retrieved_data->class_id === '0' ) {
														esc_html_e( 'All Class', 'mjschool' );
													} else {
														echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													$mjschool_get_payment_status = mjschool_get_payment_status( $retrieved_data->fees_pay_id );
													if ( $mjschool_get_payment_status === 'Not Paid' ) {
														echo "<span class='mjschool-red-color'>";
													} elseif ( $mjschool_get_payment_status === 'Partially Paid' ) {
														echo "<span class='mjschool-purpal-color'>";
													} else {
														echo "<span class='mjschool-green-color'>";
													}
													echo esc_html( $mjschool_get_payment_status );
													echo '</span>';
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->total_amount, 2, '.', '' ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->fees_paid_amount, 2, '.', '' ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Paid Amount', 'mjschool' ); ?>"></i>
												</td>
												<?php
												$Due_amt    = $retrieved_data->total_amount - $retrieved_data->fees_paid_amount;
												$due_amount = number_format( $Due_amt, 2, '.', '' );
												?>
												<td>
													<?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . esc_html( $due_amount ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( $retrieved_data->start_year ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $retrieved_data->end_year ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date To End Date', 'mjschool' ); ?>"></i>
												</td>
												<?php
												// Custom Field Values.
												if ( ! empty( $user_custom_field ) ) {
													foreach ( $user_custom_field as $custom_field ) {
														if ( $custom_field->show_in_table === '1' ) {
															$module             = 'fee_list';
															$custom_field_id    = $custom_field->id;
															$module_record_id   = $retrieved_data->fees_pay_id;
															$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
															if ( $custom_field->field_type === 'date' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} elseif ( $custom_field->field_type === 'file' ) {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		?>
																		<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile">
																			<button class="btn btn-default view_document" type="button">
																				<i class="fas fa-download"></i>
																				<?php esc_html_e( 'Download', 'mjschool' ); ?>
																			</button>
																		</a>
																		<?php
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															} else {
																?>
																<td>
																	<?php
																	if ( ! empty( $custom_field_value ) ) {
																		echo esc_html( $custom_field_value );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																</td>
																<?php
															}
														}
													}
												}
												?>
												<td class="action">
													<div class="mjschool-user-dropdown">
														<ul  class="mjschool_ul_style">
															<li >
																<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																	
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	
																</a>
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<li class="mjschool-float-left-width-100px">
																		<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&view_type=view_payment' ); ?>" class="mjschool-float-left-width-100px">
																			<i class="fa fa-eye"></i>
																			<?php esc_html_e( 'View Invoice', 'mjschool' ); ?>
																		</a>
																	</li>
																	<?php
																	if ( ! empty( $retrieved_data->fees_paid_amount ) ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fessreceipt&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-float-left-width-100px">
																				<i class="fas fa-eye"></i>
																				<?php esc_html_e( 'Payment History', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	}
																	if ( $school_obj->role === 'supportstaff' || $school_obj->role === 'parent' || $school_obj->role === 'student' ) {
																		if ( ( $retrieved_data->fees_paid_amount < $retrieved_data->total_amount || $retrieved_data->fees_paid_amount === 0 ) && $retrieved_data->total_amount > 0 ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="#" class="mjschool-float-left-width-100px show-payment-popup" idtest="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" view_type="payment" due_amount="<?php echo esc_attr( $due_amount ); ?>">
																					<i class="fa fa-credit-card" aria-hidden="true"></i>
																					<?php esc_html_e( 'Pay', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																	}
																	if ( $user_access['edit'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee&action=edit&fees_pay_id=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px">
																				<i class="fa fa-edit"> </i>
																				<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	}
																	if ( $user_access['delete'] === '1' ) {
																		?>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=examlist&action=delete&fees_pay_id=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																				<i class="fas fa-trash"></i>
																				<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	}
																	?>
																</ul>
															</li>
														</ul>
													</div>
												</td>
											</tr>
											<?php
											++$i;
										}
										?>
									</tbody>
								</table>
								<?php
								if ( $mjschool_role_name === 'supportstaff' ) {
									?>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all1" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											<label for="select_all1" class="mjschool-margin-right-5px">
												<?php esc_html_e( 'Select All', 'mjschool' ); ?>
											</label>
										</button>
										<?php
										if ( $user_access['delete'] === '1' ) {
											 ?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_feelist" class="delete_selected">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>">
											</button>
											<?php 
										}
										?>
									</div>
									<?php
								}
								?>
							</form><!------------- FEES PAYMENT LIST FORM. ----------------->
						</div><!------------- TABLE RESPOSNIVE. ------------------>
					</div><!------------- Panel body. ------------------>
					<?php
				} elseif ( $user_access['add'] === '1' ) {
					?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						
						<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=feepayment&tab=addpaymentfee' )); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="mjschool-calendar-event-new">
						<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
						
					</div>
					<?php
				}
			}
			if ( $active_tab === 'addpaymentfee' ) {
				$fees_pay_id = 0;
				if ( isset( $_REQUEST['fees_pay_id'] ) ) {
					// $fees_pay_id = $_REQUEST['fees_pay_id'];
					$fees_pay_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['fees_pay_id'])) ) );
				}
				$edit = 0;
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
					$edit   = 1;
					$result = $mjschool_obj_feespayment->mjschool_get_single_fee_mjschool_payment( $fees_pay_id );
				}
				?>
				<div class="mjschool-panel-body"><!---------------- Panel body. ----------------->
					<!-------------- Fees payment form. ------------------>
					<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
						<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
						<input type="hidden" name="fees_pay_id" value="<?php echo esc_attr( $fees_pay_id ); ?>">
						<input type="hidden" name="recurrence_type" value="one_time">
						<input type="hidden" name="invoice_type" value="expense">
						<div class="header">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Fees Payment Information', 'mjschool' ); ?></h3>
						</div>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<?php
								if ( ! $edit ) {
									$recurring_option = get_option( 'mjschool_enable_recurring_invoices' );
									if ( $recurring_option === 'yes' ) {
										?>
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox">
											<div class="form-group">
												<div class="col-md-12 form-control">
													<div class="row mjschool-padding-radio">
														<div class="input-group">
															<label class="mjschool-custom-top-label" for="classis_limit"><?php esc_html_e( 'Recurrence Type', 'mjschool' ); ?></label>
															<div class="d-inline-block mjschool-gender-line-height-24px">
																<?php
																$recurrence_type = 'one_time';
																if ( $edit ) {
																	$recurrence_type = $result->recurrence_type;
																} elseif ( isset( $_POST['recurrence_type'] ) ) {
																	$recurrence_type = sanitize_text_field( wp_unslash($_POST['recurrence_type'] ) );
																}
																?>
																<label class="radio-inline">
																	<input type="radio" value="one_time" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'one_time', $recurrence_type ); ?> /><?php esc_html_e( 'One Time', 'mjschool' ); ?>
																</label>
																<label class="radio-inline">
																	<input type="radio" value="monthly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'monthly', $recurrence_type ); ?> /><?php esc_html_e( 'Monthly', 'mjschool' ); ?>
																</label>
																<label class="radio-inline">
																	<input type="radio" value="quarterly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'Quarterly', $recurrence_type ); ?> /><?php esc_html_e( 'Quarterly', 'mjschool' ); ?>
																</label>
																<label class="radio-inline">
																	<input type="radio" value="half_yearly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'half_yearly', $recurrence_type ); ?> /><?php esc_html_e( 'Half- Yearly', 'mjschool' ); ?>
																</label>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6 input">
										</div>
										<?php
									}
								}
								if ( $edit ) {
									?>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<?php
										if ( $edit ) {
											$classval = esc_html( $result->class_id );
										} else {
											$classval = '';
										}
										?>
										<select name="class_id" id="mjschool-class-list" class="form-control validate[required] load_fees_drop mjschool-max-width-100px mjschool-color-picker-div-height" >
											<?php
											if ( $addparent ) {
												$classdata = mjschool_get_class_by_id( $student->class_name );
												?>
												<option value="<?php echo esc_attr( $student->class_name ); ?>"> <?php echo esc_html( $classdata->class_name ); ?></option>
												<?php
											}
											?>
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?> </option>
												<?php
											}
											?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ){ ?>
										<div class="col-md-6 input mjschool-class-section-hide">
											<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<?php
											if ( $edit ) {
												$sectionval = $result->section_id;
											} elseif ( isset( $_POST['class_section'] ) ) {
												$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
											} else {
												$sectionval = '';
											}
											?>
											<select name="class_section" class="form-control mjschool-max-width-100px mjschool-color-picker-div-height" id="class_section" >
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												if ( $edit ) {
													foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
														?>
														<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
															<?php echo esc_html( $sectiondata->section_name ); ?>
														</option>
														<?php
													}
												}
												?>
											</select>
										</div>
									<?php }?>
									<div class="col-md-6 input mjschool-class-section-hide">
										<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
										<?php
										if ( $edit ) {
											$classval = $result->class_id;
										} else {
											$classval = '';
										}
										?>
										<select name="student_id" id="student_list" class="form-control validate[required] mjschool-max-width-100px mjschool-color-picker-div-height">
											<option value=""><?php esc_html_e( 'Select student', 'mjschool' ); ?> </option>
											<?php
											if ( $edit ) {
												echo '<option value="' . esc_attr( $result->student_id ) . '" ' . selected( $result->student_id, $result->student_id ) . '>' . esc_html( mjschool_student_display_name_with_roll( $result->student_id ) ) . '</option>';
											}
											?>
										</select>
									</div>
									<?php
								} else {
									?>
									<div id="mjschool-smgt-select-class" class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-single-class-div">
										<label class="ml-1 mjschool-custom-top-label top" for="fees_class_list_id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="class_id" id="fees_class_list_id" class="form-control load_fees_front mjschool-min-width-100px validate[required] mjschool-color-picker-div-height">
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?> </option>
											<option value="all_class"><?php esc_html_e( 'All Class', 'mjschool' ); ?> </option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"> <?php echo esc_html( $classdata['class_name'] ); ?></option>
											<?php } ?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ){ ?>
										<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_section_id">
											<label class="ml-1 mjschool-custom-top-label top" for="fees_class_section_id"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<?php
											if ( isset( $_POST['class_section'] ) ) {
												$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
											} else {
												$sectionval = '';
											}
											?>
											<select name="class_section" class="form-control mjschool-min-width-100px mjschool-color-picker-div-height" id="fees_class_section_id" >
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												if ( $edit ) {
													foreach ( mjschool_get_class_sections( $user_info->class_name ) as $sectiondata ) {
														?>
														<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
															<?php echo esc_html( $sectiondata->section_name ); ?>
														</option>
														<?php
													}
												}
												?>
											</select>
										</div>
									<?php }?>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
										<div id="messahe_test"></div>
										<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<span class="user_display_block">
												<select name="selected_users[]" id="selected_users" class="form-control mjschool-min-width-250px validate[required]" multiple="multiple">
													<?php
													$student_list = mjschool_get_all_student_list();
													foreach ( $student_list as $retrive_data ) {
														echo '<option value="' . esc_attr( $retrive_data->ID ) . '">' . esc_html( $retrive_data->display_name ) . '</option>';
													}
													?>
												</select>
											</span>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top mjschool_margin_left_5px" for="selected_users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
											</span>
										</div>
									</div>
									<?php
								}
								?>
								<?php wp_nonce_field( 'save_payment_fees_front_nonce' ); ?>
								<div class="col-md-6 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px">
									<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
										<select name="fees_id[]" multiple="multiple" id="fees_data" class="mjschool-line-height-30px form-control validate[required] mjschool-max-width-100px">
											<?php
											if ( $edit ) {
												$fees_id = explode( ',', $result->fees_id );
												foreach ( $fees_id as $id ) {
													if ( mjschool_get_fees_term_name( $id ) !== ' ' ) {
														echo '<option value="' . esc_attr( $id ) . '" ' . selected( $id, $id ) . '>' . esc_html( mjschool_get_fees_term_name( $id ) ) . '</option>';
													}
												}
											}
											?>
										</select>
										<span class="mjschool-multiselect-label">
											<label class="ml-1 mjschool-custom-top-label top" for="fees_data"><?php esc_html_e( 'Select Fees Type', 'mjschool' ); ?><span class="required">*</span></label>
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->fees_amount ); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['fees_amount'])) ); } else { echo '0'; } ?>" name="fees_amount" readonly>
											<label for="fees_amount"><?php esc_html_e( 'Amount', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span></label>
										</div>
									</div>
								</div>
								<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
									<select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
										<?php
										if ( $edit ) {
											if ( $result->tax !== null ) {
												$tax_id = explode( ',', $result->tax );
											} else {
												$tax_id[] = '';
											}
										} else {
											$tax_id[] = '';
										}
										$mjschool_obj_tax = new Mjschool_Tax_Manage();
										$smgt_taxs        = $mjschool_obj_tax->mjschool_get_all_tax();
										if ( ! empty( $smgt_taxs ) ) {
											foreach ( $smgt_taxs as $data ) {
												$selected = '';
												if ( in_array( $data->tax_id, $tax_id ) ) {
													$selected = 'selected';
												}
												?>
												<option value="<?php echo esc_attr( $data->tax_id ); ?>" <?php echo esc_html( $selected ); ?>>
													<?php echo esc_html( $data->tax_title ); ?> - <?php echo esc_html( $data->tax_value ); ?>
												</option>
												<?php
											}
										}
										?>
									</select>
									<span class="mjschool-multiselect-label">
										<label class="ml-1 mjschool-custom-top-label top" for="tax_id"><?php esc_html_e( 'Select Tax', 'mjschool' ); ?></label>
									</span>
								</div>
								<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="discount" class="form-control text-input" type="number" min="0" onkeypress="if(this.value.length==8) return false;" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->discount ); } ?>" name="discount" placeholder="">
											<label  for="discount"><?php esc_html_e( 'Discount', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
									<select class="form-control mjschool-max-width-100px mjschool-color-picker-div-height" name="discount_type" id="discount_type" >
										<option value="%" <?php if ( $edit ) { if ( isset( $result->discount_type ) ) { selected( $result->discount_type, '%' ); } } ?> >%</option>
										<option value="amount" <?php if ( $edit ) { if ( isset( $result->discount_type ) ) { selected( $result->discount_type, 'amount' );} } ?> >
											<?php echo esc_html__( 'Amount', 'mjschool' ) . '( ' . esc_html( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) . ' )'; ?>
										</option>
									</select>
								</div>
								<div class="col-md-3 input">
									<div class="form-group">
										<div class="col-md-12 form-control">
											<input id="start_date_event" class="form-control date_picker validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_year" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_year ) ) ) ); } elseif ( isset( $_POST['start_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
											<label class="active date_label" for="start_date_event"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-3 input">
									<div class="form-group">
										<div class="col-md-12 form-control">
											<input id="end_date_event" class="form-control date_picker validate[required] start_date datepicker2" type="text" name="end_year" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->end_year ) ) ) ); } elseif ( isset( $_POST['end_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['end_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
											<label class="date_label" for="end_date_event"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mjschool-note-text-notice">
									<div class="form-group input">
										<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
											<div class="form-field">
												<textarea id="mjschool-description" name="description" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"> <?php if ( $edit ) { echo esc_textarea( $result->description ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_text_field(wp_unslash($_POST['description'])) ); } ?> </textarea>
												<span class="mjschool-txt-title-label"></span>
												<label for="mjschool-description" class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px">
									<div class="form-group">
										<div class="col-md-12 form-control mjschool-input-height-50px">
											<div class="row mjschool-padding-radio">
												<div class="input-group mjschool-input-checkbox">
													<label class="mjschool-custom-top-label" for="mjschool_enable_feesalert_mail"><?php esc_html_e( 'Send Email To Students & Parents', 'mjschool' ); ?></label>
													<div class="checkbox mjschool-checkbox-label-padding-8px">
														<label>
															<input id="mjschool_enable_feesalert_mail" type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mail" value="1" <?php echo checked( get_option( 'mjschool_enable_feesalert_mail' ), 'yes' ); ?> />
														</label>
													</div>
													&nbsp;&nbsp;<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-3 mjschool-rtl-margin-top-15px">
									<div class="form-group">
										<div class="col-md-12 form-control mjschool-input-height-50px">
											<div class="row mjschool-padding-radio">
												<div class="input-group mjschool-input-checkbox">
													<label class="mjschool-custom-top-label" for="mjschool_enable_feesalert_mjschool_student"><?php esc_html_e( 'Send SMS To Students', 'mjschool' ); ?></label>
													<div class="checkbox mjschool-checkbox-label-padding-8px">
														<label>
															<input id="mjschool_enable_feesalert_mjschool_student"  type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mjschool_student" value="1" <?php echo checked( get_option( 'mjschool_enable_feesalert_sms' ), 'yes' ); ?> />
														</label>
													</div>
													&nbsp;&nbsp;<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-3 mjschool-rtl-margin-top-15px">
									<div class="form-group">
										<div class="col-md-12 form-control mjschool-input-height-50px">
											<div class="row mjschool-padding-radio">
												<div class="input-group mjschool-input-checkbox">
													<label class="mjschool-custom-top-label" for="mjschool_enable_feesalert_mjschool_student"><?php esc_html_e( 'Send SMS To Parents', 'mjschool' ); ?></label>
													<div class="checkbox mjschool-checkbox-label-padding-8px">
														<label>
															<input type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mjschool_parent" value="1" <?php echo checked( get_option( 'mjschool_enable_feesalert_sms' ), 'yes' ); ?> />
														</label>
													</div>
													&nbsp;&nbsp;<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
					
							</div>
						</div>
						<?php
						// --------- Get module-wise custom field data. --------------//
						$mjschool_custom_field_obj = new Mjschool_Custome_Field();
						$module                    = 'fee_list';
						$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
						?>
						<div class="form-body mjschool-user-form mjschool-margin-top-20px mjschool-padding-top-15px-res">
							<div class="row">
								<div class="col-sm-6">
									<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Invoice', 'mjschool' ); } else { esc_html_e( 'Create Invoice', 'mjschool' ); } ?>" name="save_feetype_payment" class="btn btn-success mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
				</div>
				<?php
			} elseif ( $active_tab === 'view_fesspayment' ) {
				$fees_pay_id                = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['idtest'])) ) );
				$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
				$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
				$mjschool_obj_feespayment   = new Mjschool_Feespayment();
				$format                     = get_option( 'mjschool_invoice_option' );
				$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
				?>
				<?php if ( is_rtl() ) { 
					wp_enqueue_style( 'mjschool-invoice-rtl-style', plugins_url( '/assets/css/mjschool-invoice-rtl.css', __FILE__ ) );
				}
				if ( isset($_REQUEST['view_type']) && sanitize_text_field(wp_unslash($_REQUEST['view_type'])) === 'view_payment' ) {
					?>
					<div class="penal-body"><!----- Panel body. --------->
						<div id="Fees_invoice"><!----- Fees invoice. --------->
							<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-25px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res">
								<?php if ( $format === 0 ) { ?>
									<img class="mjschool-rtl-image-set-invoice mjschool-invoice-image mjschool-float-left mjschool-image-width-98px mjschool-invoice-image-model" src="<?php echo esc_url( plugins_url( '/mjschool/assets/images/listpage-icon/mjschool-invoice.png' ) ); ?>" width="100%">
								<?php } ?>
								<div id="mjschool-invoice-print" class="mjschool-main-div1 mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div1">
									<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
										<div class="row mjschool-margin-top-20px">
											<?php if ( $format === 1 ) { ?>
												<div id="rtl_heads_logo" class="mjschool-width-print mjschool-rtl-heads rtl_heads_logo mjschool_fees_style">
													<div class="mjschool_float_left_width_100">
														<div class="mjschool_float_left_width_25">
															<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
																<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool-system-logo1 mjschool_main_logo_class mjschool_fees_border_half_height_130px" />
															</div>
														</div>
														<div class="mjschool_float_left_padding_width_75">
															<p class="mjschool_fees_widht_100_fonts_24px">
																<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
															</p>
															<p class="mjschool_fees_center_fonts_17px">
																<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
															</p>
															<div class="mjschool_fees_center_margin_0px">
																<p class="mjschool_fees_width_fit_content_inline">
																	<?php esc_html_e( 'E-mail', 'mjschool' ); ?> :
																	<?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
																</p>
																<p class="mjschool_fees_width_fit_content_inline">
																	&nbsp;&nbsp;
																	<?php esc_html_e( 'Phone', 'mjschool' ); ?> :
																	<?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
																</p>
															</div>
														</div>
													</div>
												</div>
												<?php
											} else {
												?>
												<h3 class="mjschool-school-name-for-invoice-view">
													<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
												</h3>
												<div class="col-md-1 col-sm-2 col-xs-3">
													<div class="width_1 mjschool-rtl-width-80px">
														<img class="system_logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
													</div>
												</div>
												<div class="col-md-11 col-sm-10 col-xs-9 mjschool-invoice-address mjschool-invoice-address-css">
													<div class="row">
														<div class="col-md-12 col-sm-12 col-xs-12 mjschool-invoice-padding-bottom-15px mjschool-padding-right-0">
															<label class="mjschool-popup-label-heading">
																<?php esc_html_e( 'Address', 'mjschool' ); ?>
															</label><br>
															<label class="mjschool-label-value mjschool-word-break-all">
																<?php
																$address         = get_option( 'mjschool_address' );
																$escaped_address = esc_html( $address );
																// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
																echo nl2br( chunk_split( $escaped_address, 100, "\n" ) );
																// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
																?>
															</label>
														</div>
														<div class="row col-md-12 mjschool-invoice-padding-bottom-15px">
															<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-email-width-auto">
																<label class="mjschool-popup-label-heading">
																	<?php esc_html_e( 'Email', 'mjschool' ); ?>
																</label><br>
																<label class="mjschool-label-value mjschool-word-break-all">
																	<?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?>
																</label>
															</div>
															<div class="col-md-6 col-sm-6 col-xs-6 mjschool-address-css mjschool-padding-right-0 mjschool-padding-left-30px">
																<label class="mjschool-popup-label-heading">
																	<?php esc_html_e( 'Phone', 'mjschool' ); ?>
																</label><br>
																<label class="mjschool-label-value">
																	<?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?>
																</label>
															</div>
														</div>
														<div align="right" class="mjschool-width-24px"></div>
													</div>
												</div>
											<?php } ?>
										</div>
										<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
											<?php if ( $format === 1 ) { ?>
												<div class="mjschool-width-print mjschool_fees_padding_border_2px">
													<div class="mjschool_float_left_width_100">
														<?php
														$student_id = $fees_detail_result->student_id;
														$patient    = get_userdata( $student_id );
														if ( $patient ) {
															$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
															$escaped_display_name = esc_html( ucwords( $display_name ) );
															$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<div  class="mjschool_padding_10px">
															<div class="mjschool_float_left_width_65">
																<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b><?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
															</div>
															<div class="mjschool_float_right_width_35">
																<b><?php esc_html_e( 'Invoice Number', 'mjschool' ); ?>:</b><?php echo esc_html( $invoice_number ); ?>
															</div>
														</div>
													</div>
													<div class="mjschool_float_left_width_65">
														<?php
														$student_id = $fees_detail_result->student_id;
														$patient    = get_userdata( $student_id );
														if ( $patient ) {
															$address = esc_html( get_user_meta( $student_id, 'address', true ) );
															$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
															$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
															?>
															<div class="mjschool_padding_10px">
																<div>
																	<b> <?php esc_html_e( 'Address', 'mjschool' ); ?>: </b><?php echo esc_html( $address ); ?>
																</div>
																<div>
																	<?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?>
																</div>
															</div>
														<?php } ?>
													</div>
													<div class="mjschool_float_right_width_35">
														<?php
														$issue_date = 'DD-MM-YYYY';
														$issue_date = $fees_detail_result->paid_by_date;
														if ( ! empty( $income_data ) ) {
															$issue_date = $income_data->income_create_date;
														} elseif ( ! empty( $invoice_data ) ) {
															$issue_date = $invoice_data->date;
														} elseif ( ! empty( $expense_data ) ) {
															$issue_date = $expense_data->income_create_date;
														}
														?>
														<div class="mjschool_fees_padding_10px">
															<div class="mjschool_float_left_width_100">
																<b> <?php esc_html_e( 'Issue Date', 'mjschool' ); ?>: </b>
																<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
															</div>
														</div>
													</div>
													<div class="mjschool_float_right_width_35">
														<div class="mjschool_fees_padding_10px">
															<b> <?php esc_html_e( 'Status', 'mjschool' ); ?>: </b>
															<?php
															$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
															if ( $payment_status === 'Fully Paid' ) {
																echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
															}
															if ( $payment_status === 'Partially Paid' ) {
																echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
															}
															if ( $payment_status === 'Not Paid' ) {
																echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
															}
															?>
														</div>
													</div>
												</div>
												<?php
											} else {
												?>
												<div class="row">
													<div class="mjschool-width-50px mjschool-float-left-width-100px">
														<div class="col-md-8 col-sm-8 col-xs-5 mjschool-custom-padding-0 mjschool-float-left mjschool-display-grid mjschool-display-inherit-res mjschool-margin-bottom-20px">
															<div class="mjschool-billed-to mjschool-display-flex mjschool-display-inherit-res mjschool-invoice-address-heading">
																<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px">
																	<?php esc_html_e( 'Bill To', 'mjschool' ); ?> :
																</h3>
																<?php
																$student_id = $fees_detail_result->student_id;
																$patient    = get_userdata( $student_id );
																if ( $patient ) {
																	$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
																	$escaped_display_name = esc_html( ucwords( $display_name ) );
																	$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
																	echo "<h3 class='display_name mjschool-invoice-width-100px'>" . esc_html( mjschool_student_display_name_with_roll( $student_id ) ) . '</h3>';
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
															</div>
															<div class="mjschool-width-60px mjschool-address-information-invoice">
																<?php
																$student_id = $fees_detail_result->student_id;
																$patient    = get_userdata( $student_id );
																if ( $patient ) {
																	$address         = get_user_meta( $student_id, 'address', true );
																	$escaped_address = esc_html( $address );
																	$split_address   = chunk_split( $escaped_address, 30, '<br>' );
																	echo wp_kses_post( $split_address );
																	echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
																	echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
																}
																?>
															</div>
														</div>
														<div class="col-md-3 col-sm-4 col-xs-7 mjschool-float-left">
															<div class="mjschool-width-50px">
																<div class="mjschool-width-20px" align="center">
																	<h5 class="mjschool-align-left"> 
																		<label class="mjschool-popup-label-heading text-transfer-upercase">
																			<?php echo esc_html__( 'Invoice Number :', 'mjschool' ); ?>
																		</label>&nbsp;
																		<label class="mjschool-invoice-model-value">
																			<?php echo esc_html( $invoice_number ); ?>
																		</label>
																	</h5>
																	<?php
																	$issue_date     = 'DD-MM-YYYY';
																	$issue_date     = $fees_detail_result->paid_by_date;
																	$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
																	?>
																	<h5 class="mjschool-align-left"> 
																		<label class="mjschool-popup-label-heading text-transfer-upercase">
																			<?php echo esc_html__( 'Date :', 'mjschool' ); ?>
																		</label>&nbsp; 
																		<label class="mjschool-invoice-model-value">
																			<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
																		</label>
																	</h5>
																	<h5 class="mjschool-align-left">
																		<label class="mjschool-popup-label-heading text-transfer-upercase"> <?php echo esc_html__( 'Status :', 'mjschool' ); ?> </label> &nbsp;
																		<label class="mjschool-invoice-model-value">
																			<?php
																			if ( $payment_status === 'Fully Paid' ) {
																				echo '<span class="mjschool-green-color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
																			}
																			if ( $payment_status === 'Partially Paid' ) {
																				echo '<span class="mjschool-purpal-color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
																			}
																			if ( $payment_status === 'Not Paid' ) {
																				echo '<span class="mjschool-red-color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
																			}
																			?>
																		</label>
																	</h5>
																</div>
															</div>
														</div>
													</div>
												</div>
											<?php } ?>
										</div>
										<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-3">
											<tbody>
												<tr>
													<td>
														<h3 class="display_name">
															<?php esc_html_e( 'Invoice Entries', 'mjschool' ); ?>
														</h3>
													<td>
												</tr>
											</tbody>
										</table>
										<div class="table-responsive mjschool-padding-bottom-15px mjschool-rtl-padding-left-40px">
											<?php if ( $format === 1 ) { ?>
												<div class="table-responsive">
													<table class="table table-bordered mjschool-model-invoice-table mjschool_border_black_2px">
														<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_border_color_2px" >
															<tr>
																<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_15px"> Number</th>
																<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_20">
																	<?php esc_html_e( 'Date', 'mjschool' ); ?>
																</th>
																<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px" >
																	<?php esc_html_e( 'Fees Type', 'mjschool' ); ?>
																</th>
																<th class="mjschool-entry-table-heading mjschool-align-left mjschool_tables_width_15px">
																	<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
																</th>
															</tr>
														</thead>
														<tbody>
															<?php
															$fees_id = explode( ',', $fees_detail_result->fees_id );
															$x       = 1;
															$amounts = 0;
															foreach ( $fees_id as $id ) {
																?>
																<tr>
																	<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																		<?php echo esc_html( $x ); ?>
																	</td>
																	<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																		<?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?>
																	</td>
																	<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																		<?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?>
																	</td>
																	<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																		<?php
																		$amount   = $mjschool_obj_feespayment->mjschool_feetype_amount_data( $id );
																		$amounts += $amount;
																		echo esc_html( number_format( $amount, 2, '.', '' ) );
																		?>
																	</td>
																</tr>
																<?php
																++$x;
															}
															$sub_total = $amounts;
															if ( ! empty( $fees_detail_result->tax ) ) {
																$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
															} else {
																$tax_name = '';
															}
															if ( $fees_detail_result->discount ) {
																$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
															} else {
																$discount_name = '';
															}
															?>
														</tbody>
													</table>
												</div>
												<?php
											} else {
												?>
												<table class="table mjschool-model-invoice-table">
													<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading">
														<tr>
															<th class="mjschool-entry-table-heading mjschool-align-left">#</th>
															<th class="mjschool-entry-table-heading mjschool-align-left"> <?php esc_html_e( 'Date', 'mjschool' ); ?></th>
															<th class="mjschool-entry-table-heading mjschool-align-left"> <?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
															<th class="mjschool-entry-table-heading mjschool-align-left"> <?php esc_html_e( 'Total', 'mjschool' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<?php
														$fees_id = explode( ',', $fees_detail_result->fees_id );
														$x       = 1;
														$amounts = 0;
														foreach ( $fees_id as $id ) {
															?>
															<tr>
																<td class="mjschool-align-left mjschool-invoice-table-data">
																	<?php echo esc_html( $x ); ?>
																</td>
																<td class="mjschool-align-left mjschool-invoice-table-data">
																	<?php echo esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ); ?>
																</td>
																<td class="mjschool-align-left mjschool-invoice-table-data">
																	<?php echo esc_html( mjschool_get_fees_term_name( $id ) ); ?>
																</td>
																<td class="mjschool-align-left mjschool-invoice-table-data">
																	<?php
																	$amount   = $mjschool_obj_feespayment->mjschool_feetype_amount_data( $id );
																	$amounts += $amount;
																	echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $amount, 2, '.', '' ) ) );
																	?>
																</td>
															</tr>
															<?php
															++$x;
														}
														$sub_total = $amounts;
														if ( ! empty( $fees_detail_result->tax ) ) {
															$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
														} else {
															$tax_name = '';
														}
														if ( $fees_detail_result->discount ) {
															$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
														} else {
															$discount_name = '';
														}
														?>
													</tbody>
												</table>
											<?php } ?>
										</div>
										<?php
										if ( $format === 1 ) {
											?>
											<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
												<table class="table table-bordered mjschool_fees_collapse_width_100" >
													<tbody>
														<tr>
															<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row">
																<?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?>
															</th>
															<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;">
																<?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?>
															</td>
														</tr>
														<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
															<tr>
																<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row">
																	<?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?>
																</th>
																<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;"> <?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?> </td>
															</tr>
														<?php } ?>
														<?php if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) { ?>
															<tr>
																<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row"> <?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?> </th>
																<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;"> <?php echo '+' . esc_html( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ); ?> </td>
															</tr>
														<?php } ?>
														<tr>
															<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row"> <?php echo esc_html__( 'Payment Made :', 'mjschool' ); ?> </th>
															<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;"> <?php echo esc_html( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ); ?> </td>
														</tr>
														<tr>
															<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 2px solid black;" scope="row"> <?php echo esc_html__( 'Due Amount :', 'mjschool' ); ?> </th>
															<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
															<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 2px solid black;"> <?php echo esc_html( number_format( $Due_amount, 2, '.', '' ) ); ?> </td>
														</tr>
													</tbody>
												</table>
											</div>
											<?php
										} else {
											?>
											<div class="table-responsive  mjschool-rtl-float-left-width-100px">
												<table width="100%" border="0">
													<tbody>
														<tr >
															<td align="right" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading">
																<?php esc_html_e( 'Sub Total :', 'mjschool' ); ?>
															</td>
															<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
																<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?>
															</td>
														</tr>
														<?php if ( isset( $fees_detail_result->discount_amount ) && ( $fees_detail_result->discount_amount ) != 0 ) { ?>
															<tr>
																<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
																	<?php echo esc_html__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )' . '  :'; ?>
																</td>
																<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
																	<?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ) ); ?>
																</td>
															</tr>
														<?php } ?>
														<?php
														if ( isset( $fees_detail_result->tax_amount ) && ( $fees_detail_result->tax_amount ) != 0 ) {
															?>
															<tr>
																<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
																	<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?>
																</td>
																<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
																	<?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->tax_amount, 2, '.', '' ) ) ); ?>
																</td>
															</tr>
															<?php
														}
														?>
														<tr>
															<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
																<?php esc_html_e( 'Payment Made :', 'mjschool' ); ?>
															</td>
															<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
																<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ); ?>
															</td>
														</tr>
														<tr>
															<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" align="right">
																<?php esc_html_e( 'Due Amount :', 'mjschool' ); ?>
															</td>
															<?php $Due_amount = $fees_detail_result->total_amount - $fees_detail_result->fees_paid_amount; ?>
															<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-rtl-text-align-left mjschool-total-value">
																<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ); ?>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										<?php } ?>
										<?php
										$subtotal    = $fees_detail_result->total_amount;
										$paid_amount = $fees_detail_result->fees_paid_amount;
										$grand_total = $subtotal - $paid_amount;
										?>
										<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-float-left row mjschool-margin-top-10px-res col-md-4 col-sm-4 col-xs-4 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_margin_right_0px" >
											<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
												<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right">
													<?php esc_html_e( 'Grand Total', 'mjschool' ); ?>
												</h3>
											</div>
											<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
												<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value">
													<?php
													$formatted_amount = number_format( $subtotal, 2, '.', '' );
													$currency         = mjschool_get_currency_symbol();
													echo esc_html( "($currency)$formatted_amount" );
													?>
												</h3>
											</div>
										</div>
										<?php
										if ( ! empty( $fees_history_detail_result ) ) {
											?>
											<table class="mjschool-width-100px mjschool-margin-top-10px-res">
												<tbody>
													<tr>
														<td>
															<h3 class="display_name mjschool-res-pay-his-mt-10px">
																<?php esc_html_e( 'Payment History', 'mjschool' ); ?>
															</h3>
														<td>
													</tr>
												</tbody>
											</table>
											<div class="table-responsive mjschool-rtl-padding-left-40px">
												<table class="table table-bordered mjschool-model-invoice-table">
													<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_border_color_2px" >
														<tr>
															<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px"> <?php esc_html_e( 'Date', 'mjschool' ); ?> </th>
															<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px" > <?php esc_html_e( 'Method', 'mjschool' ); ?> </th>
															<th class="mjschool-entry-table-heading mjschool-align-left mjschool_black_solid_border_2px"> <?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?> </th>
														</tr>
													</thead>
													<tbody>
														<?php
														foreach ( $fees_history_detail_result as $retrive_date ) {
															$payment_id = mjschool_encrypt_id( $retrive_date->payment_history_id );
															?>
															<tr>
																<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																	<?php echo esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ); ?>
																</td>
																<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																	<?php
																	$data = $retrive_date->payment_method;
																	echo esc_html( $data );
																	?>
																</td>
																<td class="mjschool-align-left mjschool-invoice-table-data mjschool_border_black_2px">
																	<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
																	<a href="<?php echo esc_url( home_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=' . esc_attr(sanitize_text_field(wp_unslash($_REQUEST['idtest']))) . '&payment_id=' . urlencode( $payment_id ) . '&view_type=view_receipt&_wpnonce_action=1e4d916199' ) ); ?>" class="btn btn-primary btn-sm mjschool_margin_left_10px"> View Receipt </a>
																</td>
															</tr>
															<?php
														}
														?>
													</tbody>
												</table>
											</div>
											<?php
											$total_payment = 0;
											foreach ( $fees_history_detail_result as $retrive_date ) {
												$total_payment += floatval( $retrive_date->amount );
											}
											$subtotal    = $subtotal = $total_payment;
											$grand_total = $subtotal - $paid_amount;
											?>
											<div id="mjschool-res-rtl-width-100px" class="mjschool-res-rtl-width-100px mjschool-rtl-float-left row mjschool-margin-top-10px-res col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable-css mjschool-inovice-width-100px-rs mjschool-float-left mjschool-grand-total-div mjschool-invoice-table-grand-total mjschool_float_margin_right_0px">
												<div class="mjschool-width-50-res mjschool-align-right col-md-5 col-sm-5 col-xs-5 mjschool-view-invoice-lable mjschool-padding-11 mjschool-padding-right-0-left-0 mjschool-float-left mjschool-grand-total-label-div mjschool-invoice-model-height mjschool-line-height-15 mjschool-padding-left-0px">
													<h3  class="padding mjschool-color-white margin mjschool-invoice-total-label mjschool_float_right">
														<?php esc_html_e( 'Total Payment', 'mjschool' ); ?>
													</h3>
												</div>
												<div class="mjschool-width-50-res mjschool-align-right col-md-7 col-sm-7 col-xs-7 mjschool-view-invoice-lable  padding_right_5_left_5 mjschool-padding-11 mjschool-float-left mjschool-grand-total-amount-div">
													<h3 class="padding margin text-right mjschool-color-white mjschool-invoice-total-value">
														<?php
														$formatted_amount = number_format( $subtotal, 2, '.', '' );
														$currency         = mjschool_get_currency_symbol();
														echo esc_html( "($currency)$formatted_amount" );
														?>
													</h3>
												</div>
											</div>
											<?php
										}
										?>
										<div class="rtl-signs mjschool_fees_border_2px_margin_20px" >
											<!-- Teacher Signature (Middle). -->
											<div class="mjschool_fees_center_width_33">
												<div>
													<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
												</div>
												<div class="mjschool_fees_width_150px">
												</div>
												<div class="mjschool_margin_top_5px">
													<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
												</div>
											</div>
										</div>
										<?php
										if ( ! empty( $fees_history_detail_result ) ) {
											?>
											<div class="row">
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3">
													<div class="form-group">
														<div class="col-md-12 form-control mjschool-rtl-relative-position">
															<div class="row mjschool-padding-radio">
																<div>
																	<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="smgt_enable_holiday_mail"><?php esc_html_e( 'Print With Payment History', 'mjschool' ); ?></label>
																	<input type="checkbox" class="mjschool-check-box-input-margin" name="certificate_header" id="certificate_header" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										<?php } ?>
										<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
											<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-6 col-sm-6 col-xs-6 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
												<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res">
													<a href="<?php echo esc_url( '?page=mjschool_fees_payment&print=print&payment_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['idtest'] ) ) ) . '&fee_paymenthistory=fee_paymenthistory' ); ?>" id="exprience_latter" target="_blank" class="btn btn mjschool-save-btn mjschool-invoice-btn-div"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>"> </a>
												</div>
												<?php
												if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
													if ( isset( $_POST['download_app_pdf'] ) ) {
														$file_path = esc_url(content_url( '/uploads/invoice_pdf/fees_payment/' . mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['idtest'])) ) . '.pdf'));
														if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
															unlink( $file_path ); // Delete the file.
														}
														$generate_pdf = mjschool_fees_payment_pdf_for_mobile_app( sanitize_text_field(wp_unslash($_REQUEST['idtest'])) );
														wp_safe_redirect( $file_path );
														die();
													}
													?>
													<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
														<form name="app_pdf" action="" method="post">
															<div class="form-body mjschool-user-form mjschool-margin-top-40px">
																<div class="row mjschool-invoice-print-pdf-btn">
																	<div class="col-md-1 mjschool-print-btn-rs">
																		<button data-toggle="tooltip" id="download_pdf" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>">
																		</button>
																	</div>
																</div>
															</div>
														</form>
													</div>
													<?php
												} else {
													?>
													<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
														<a href="<?php echo esc_url( '?page=mjschool_fees_payment&print=pdf&payment_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['idtest'] ) ) ) . '&fee_paymenthistory=fee_paymenthistory' ); ?>" id="download_pdf" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></a>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<div class="mjschool-margin-top-20px"></div>
									</div>
								</div>
							</div>
						</div><!----- Fees invoice. --------->
					</div><!----- Panel body. --------->
					<?php
				} else {
					$mjschool_obj_feespayment = new Mjschool_Feespayment();
					$fee_pay_id   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) ) );
					$fees_history = $$mjschool_obj_feespayment->mjschool_get_single_payment_history( $fee_pay_id );
					?>
					<div class="penal-body"><!----- Panel body. --------->
						<div id="Fees_invoice"><!----- Fees invoice. --------->
							<div class="modal-body mjschool-border-invoice-page mjschool-margin-top-25px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res mjschool_height_1350px">
								<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
									<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
										<div class="row mjschool-margin-top-20px">
											<div id="rtl_heads_logo" class="mjschool-width-print mjschool-rtl-heads rtl_heads_logo mjschool_fees_style" >
												<div class="mjschool_float_left_width_100">
													<div class="mjschool_float_left_width_25">
														<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
															<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool-system-logo1 mjschool_main_logo_class mjschool_fees_border_half_height_130px" />
														</div>
													</div>
													<div class="mjschool_float_left_padding_width_75">
														<p class="mjschool_fees_widht_100_fonts_24px">
															<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
														</p>
														<p class="mjschool_fees_center_fonts_17px">
															<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
														</p>
														<div class="mjschool_fees_center_margin_0px">
															<p class="mjschool_fees_width_fit_content_inline">
																<?php esc_html_e( 'E-mail', 'mjschool' ); ?> :
																<?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
															</p>
															<p class="mjschool_fees_width_fit_content_inline">
																&nbsp;&nbsp;
																<?php esc_html_e( 'Phone', 'mjschool' ); ?> :
																<?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
															</p>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-20px">
											<div class="mjschool-width-print mjschool_fees_padding_border_2px">
												<div class="mjschool_float_left_width_100">
													<?php
													$student_id = $fees_detail_result->student_id;
													$patient    = get_userdata( $student_id );
													if ( $patient ) {
														$display_name         = isset( $patient->display_name ) ? $patient->display_name : '';
														$escaped_display_name = esc_html( ucwords( $display_name ) );
														$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
													} else {
														esc_html_e( 'N/A', 'mjschool' );
													}
													?>
													<div  class="mjschool_padding_10px">
														<div class="mjschool_float_left_width_65">
															<b> <?php esc_html_e( 'Bill To', 'mjschool' ); ?>: </b> <?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
														</div>
														<div class="mjschool_float_right_width_35">
															<b> <?php esc_html_e( 'Receipt Number', 'mjschool' ); ?>: </b> <?php echo esc_html( mjschool_generate_receipt_number( $fee_pay_id ) ); ?>
														</div>
													</div>
												</div>
												<div class="mjschool_float_left_width_65">
													<?php
													$student_id = $fees_detail_result->student_id;
													$patient    = get_userdata( $student_id );
													if ( $patient ) {
														$address = esc_html( get_user_meta( $student_id, 'address', true ) );
														$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
														$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
														?>
														<div class="mjschool_padding_10px">
															<div>
																<b> <?php esc_html_e( 'Address', 'mjschool' ); ?>: </b> <?php echo esc_html( $address ); ?>
															</div>
															<div>
																<?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?>
															</div>
														</div>
													<?php } ?>
												</div>
												<div class="mjschool_float_right_width_35">
													<?php
													$issue_date = 'DD-MM-YYYY';
													$issue_date = isset( $fees_history[0] ) ? $fees_history[0]->paid_by_date : '';
													?>
													<div class="mjschool_fees_padding_10px">
														<div class="mjschool_float_left_width_100">
															<b> <?php esc_html_e( 'Issue Date', 'mjschool' ); ?>: </b> <?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
														</div>
													</div>
												</div>
												<div class="mjschool_float_right_width_35">
													<div class="mjschool_fees_padding_10px">
														<div class="mjschool_float_left_width_100">
															<b> <?php esc_html_e( 'Payment Method', 'mjschool' ); ?>: </b> <?php echo esc_html( $fees_history[0]->payment_method ); ?>
														</div>
													</div>
												</div>
												<div class="mjschool_float_right_width_35">
													<div class="mjschool_fees_padding_10px">
														<div class="mjschool_float_left_width_100">
															<b> <?php esc_html_e( 'Invoice Refrence', 'mjschool' ); ?>: </b> <?php echo esc_html( $invoice_number ); ?>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php
										if ( ! empty( $fees_history ) ) {
											?>
											<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-2">
												<tbody>
													<tr>
														<td>
															<h3 class="display_name mjschool-res-pay-his-mt-10px mjschool_fees_center_font_24px">
																<?php esc_html_e( 'Payment Receipt', 'mjschool' ); ?>
															</h3>
														<td>
													</tr>
												</tbody>
											</table>
											<div class="mjschool_fees_padding_10px" class="mb-3">
												<div class="mjschool_float_left_width_100">
													<b><?php esc_html_e( 'Transaction Id', 'mjschool' ); ?>:</b> <?php echo esc_html( $fees_history[0]->trasaction_id ); ?>
												</div>
											</div>
											<?php
											$mjschool_custom_field_obj = new Mjschool_Custome_Field();
											$module                    = 'fee_transaction';
											$mjschool_custom_field_obj->mjschool_show_inserted_customfield_receipt( $module );
											?>
											<div class="table-responsive mjschool-rtl-padding-left-40px">
												<table class="table table-bordered mjschool-model-invoice-table mjschool_fees_collapse_width_100">
													<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_fees_color_border_2px">
														<tr>
															<th class="mjschool-entry-table-heading mjschool-align-left mjschool_width_heading_70">
																<?php esc_html_e( 'Description', 'mjschool' ); ?>
															</th>
															<th class="mjschool-entry-table-heading mjschool-align-left mjschool_fees_center_width_30_border_black">
																<?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
															</th>
														</tr>
													</thead>
													<tbody>
														<?php
														foreach ( $fees_history as $retrive_date ) {
															?>
															<tr class="mjschool_height_230px">
																<td class="mjschool_fees_vertical_align_width_70">
																	<?php
																	$data = $retrive_date->payment_note;
																	echo esc_html( $data );
																	?>
																</td>
																<td class="mjschool_fees_vertical_align_width_30">
																	<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
																</td>
															</tr>
															<?php
														}
														?>
														<tr>
															<th class="mjschool_fees_border_2px_width_70">
																<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
															</th>
															<th class="mjschool_fees_border_2px_width_30">
																<?php echo esc_html( number_format( $retrive_date->amount, 2, '.', '' ) ); ?>
															</th>
														</tr>
													</tbody>
												</table>
												<p class="mt-2 mjschool_width_700_font_16px" >
													<?php echo esc_html( ucfirst( mjschool_convert_number_to_words( $retrive_date->amount ) ) . ' Only' ); ?>
												</p>
											</div>
											<div class="rtl-signs mjschool_fees_padding_width_overflow_hidden">
												<!-- Teacher Signature (Middle). -->
												<div class="mjschool_fees_center_width_33">
													<div>
														<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
													</div>
													<div
														class="mjschool_fees_width_150px">
													</div>
													<div class="mjschool_margin_top_5px">
														<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
													</div>
												</div>
											</div>
											<?php
										}
										?>
										<div class="col-md-12 grand_total_main_div total_mjschool-padding-15px mjschool-rtl-float-none">
											<div class="row mjschool-margin-top-10px-res mjschool-width-50-res col-md-6 col-sm-6 col-xs-6 mjschool-print-button pull-left mjschool-invoice-print-pdf-btn">
												<div class="col-md-2 mjschool-print-btn-rs mjschool-width-50-res">
													<a href="<?php echo esc_url( '?page=mjschool_fees_receipt&print=print&payment_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['idtest'] ) ) ) . '&receipt_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['payment_id'] ) ) ) . '&fee_paymenthistory=fee_paymenthistory' ); ?>" target="_blank" class="btn btn mjschool-save-btn mjschool-invoice-btn-div">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-print.png' ); ?>">
													</a>
												</div>
												<?php
												// check this.
												if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
													if ( isset( $_POST['download_app_pdf'] ) ) {
														$file_path = content_url( '/uploads/invoice_pdf/fees_payment/' . mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['idtest'])) ) . '.pdf');
														if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
															unlink( $file_path ); // Delete the file.
														}
														$generate_pdf = mjschool_fees_receipt_pdf_for_mobile_app( sanitize_text_field(wp_unslash($_REQUEST['idtest'])), sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) );
														wp_safe_redirect( $file_path );
														die();
													}
													?>
													<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
														<form name="app_pdf2" action="" method="post">
															<div class="form-body mjschool-user-form mjschool-margin-top-40px">
																<div class="row mjschool-invoice-print-pdf-btn">
																	<div class="col-md-1 mjschool-print-btn-rs">
																		<button data-toggle="tooltip" name="download_app_pdf" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></button>
																	</div>
																</div>
															</div>
														</form>
													</div>
													<?php
												} else {
													?>
													<div class="col-md-3 mjschool-pdf-btn-rs mjschool-width-50-res">
														<a href="<?php echo esc_url( '?page=mjschool_fees_receipt&print=pdf&payment_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['idtest'] ) ) ) . '&receipt_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['payment_id'] ) ) ) . '&fee_receipthistory=fee_receipthistory' ); ?>" target="_blank" class="btn mjschool-color-white mjschool-invoice-btn-div btn mjschool-save-btn"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-pdf.png' ); ?>"></a>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<div class="mjschool-margin-top-20px"></div>
									</div>
								</div>
							</div>
						</div><!----- Fees Invoice. --------->
					</div><!----- Panel body.. --------->
					<?php
				}
			}
			if ( $active_tab === 'recurring_feespaymentlist' ) {
				// Check nonce for fees list tab.
				if ( isset( $_GET['tab'] ) ) {
					if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_feespayment_tab' ) ) {
						wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
					}
				}
				$user_id = get_current_user_id();
				if ( $school_obj->role === 'teacher' ) {
					$own_data = $user_access['own_data'];
					if ( $own_data === '1' ) {
						$retrieve_class_data = mjschool_get_fees_payment_by_class_ids($class_id);
					} else {
						$retrieve_class_data = $mjschool_obj_feespayment->mjschool_get_all_recurring_fees();
					}
				} else {
					$retrieve_class_data = $mjschool_obj_feespayment->mjschool_get_all_recurring_fees();
				}
				if ( ! empty( $retrieve_class_data ) ) {
					?>
					<div class="mjschool-panel-body">
						<div class="table-responsive">
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<table id="frontend_recurring_fees_paymnt_list" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Recurring Type', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></th>
											<?php
											if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
												?>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
												<?php
											}
											?>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $retrieve_class_data as $retrieved_data ) {
											$color_class_css = mjschool_table_list_background_color( $i );
											?>
											<tr>
												<td>
													<?php
													$fees_id   = explode( ',', $retrieved_data->fees_id );
													$fees_type = array();
													foreach ( $fees_id as $id ) {
														$fees_type[] = mjschool_get_fees_term_name( $id );
													}
													echo esc_html( implode( ' , ', $fees_type ) );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Title', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php
													if ( $retrieved_data->recurring_type === 'monthly' ) {
														esc_html_e( 'Monthly', 'mjschool' );
													} elseif ( $retrieved_data->recurring_type === 'quarterly' ) {
														esc_html_e( 'Quarterly', 'mjschool' );
													} elseif ( $retrieved_data->recurring_type === 'half_yearly' ) {
														esc_html_e( 'Half- Yearly', 'mjschool' );
													} else {
														esc_html_e( 'One Time', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Recurring Type', 'mjschool' ); ?>"></i>
												</td>
												<?php
												$student_id_array = explode( ',', $retrieved_data->student_id );
												$student_data     = array();
												foreach ( $student_id_array as $student_id ) {
													$student_data[] = mjschool_student_display_name_with_roll( $student_id );
												}
												?>
												<td>
													<?php echo wp_kses_post( implode( ',<br>', array_map( 'esc_html', $student_data ) ) ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( implode( ', ', array_map( 'esc_html', $student_data ) ) ); ?>"></i>
												</td>
												<td>
													<?php
													if ( $retrieved_data->class_id === '0' ) {
														esc_html_e( 'All Class', 'mjschool' );
													} else {
														echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( $retrieved_data->status ); ?>
												</td>
												<td>
													<?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . number_format( $retrieved_data->total_amount, 2, '.', '' ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i>
												</td>
												<td>
													<?php echo esc_html( $retrieved_data->start_year ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( $retrieved_data->end_year ); ?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date To End Date', 'mjschool' ); ?>"></i>
												</td>
												<?php
												if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
													?>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<?php
																		if ( $user_access['edit'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=addrecurringpayment&action=edit&recurring_fees_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->recurring_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px">
																					<i class="fa fa-edit"> </i>
																					<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $user_access['delete'] === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=feespaymentlist&action=delete&recurring_fees_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->recurring_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																					<i class="fas fa-trash"></i>
																					<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		?>
																	</ul>
																</li>
															</ul>
														</div>
													</td>
													<?php
												}
												?>
											</tr>
											<?php
											++$i;
										}
										?>
									</tbody>
								</table>
							</form>
						</div>
					</div>
					<?php
				} elseif ( $user_access_add === '1' ) {
					 ?>
					<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addpaymentfee' ) ); ?>">
							<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
						</a>
						<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
							<label class="mjschool-no-data-list-label">
								<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
							</label>
						</div>
					</div>
					<?php
				} else {
					?>
					<div class="mjschool-calendar-event-new">
						<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
					</div>
					<?php 
				}
			}
			if ( $active_tab === 'addrecurringpayment' ) {
				$recurring_fees_id = 0;
				if ( isset( $_REQUEST['recurring_fees_id'] ) ) {
					$recurring_fees_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['recurring_fees_id'])) ) );
				}
				$edit = 0;
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
					$edit   = 1;
					$result = $mjschool_obj_feespayment->mjschool_get_single_recurring_fees( $recurring_fees_id );
				}
				?>
				<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!----- Panel body. --------->
					<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form">
						<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
						<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
						<input type="hidden" name="recurring_fees_id" value="<?php echo esc_attr( $recurring_fees_id ); ?>">
						<input type="hidden" name="last_recurrence_date" value="<?php echo esc_attr( $result->recurring_enddate ); ?>">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<?php
								if ( $edit ) {
									?>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox">
										<div class="form-group">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label" for="classis_limit"> <?php esc_html_e( 'Recurrence Type', 'mjschool' ); ?> </label>
														<div class="d-inline-block mjschool-gender-line-height-24px">
															<?php
															$recurrence_type = 'one_time';
															if ( $edit ) {
																$recurrence_type = $result->recurring_type;
															} elseif ( isset( $_POST['recurrence_type'] ) ) {
																$recurrence_type = sanitize_text_field( wp_unslash($_POST['recurrence_type'] ) );
															}
															?>
															<label class="radio-inline">
																<input type="radio" value="monthly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'monthly', $recurrence_type ); ?> />
																<?php esc_html_e( 'Monthly', 'mjschool' ); ?>
															</label>
															<label class="radio-inline">
																<input type="radio" value="quarterly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'quarterly', $recurrence_type ); ?> />
																<?php esc_html_e( 'Quarterly', 'mjschool' ); ?>
															</label>
															<label class="radio-inline">
																<input type="radio" value="half_yearly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'half_yearly', $recurrence_type ); ?> />
																<?php esc_html_e( 'Half- Yearly', 'mjschool' ); ?>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6 input">
									</div>
									<?php
								}
								if ( $edit ) {
									?>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="fees_class_list_id">
											<?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
										</label>
										<?php $classval = ( $edit && ! empty( $result->class_id ) ) ? $result->class_id : ''; ?>
										<select name="class_id" id="fees_class_list_id" class="form-control validate[required] load_fees_drop mjschool-max-width-100px mjschool_heights_47px">
											<option value="all class" <?php selected( $classval, '' ); ?>> <?php esc_html_e( 'All Class', 'mjschool' ); ?> </option>
											<?php if ( ! empty( $classval ) ) : ?>
												<option value="<?php echo esc_attr( $classval ); ?>" selected> <?php echo esc_html( mjschool_get_class_name_by_id( $classval ) ); ?> </option>
											<?php endif; ?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ){ ?>
										<div class="col-md-6 input mjschool-class-section-hide">
											<label class="ml-1 mjschool-custom-top-label top" for="fees_class_section_id"> <?php esc_html_e( 'Class Section', 'mjschool' ); ?> </label>
											<?php
											if ( $edit ) {
												$sectionval = $result->section_id;
											} elseif ( isset( $_POST['class_section'] ) ) {
												$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
											} else {
												$sectionval = '';
											}
											?>
											<select name="class_section" class="form-control mjschool-max-width-100px mjschool-color-picker-div-height" id="fees_class_section_id" >
												<option value=""> <?php esc_html_e( 'All Section', 'mjschool' ); ?> </option>
												<?php
												if ( $edit ) {
													foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
														?>
														<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
															<?php echo esc_html( $sectiondata->section_name ); ?>
														</option>
														<?php
													}
												}
												?>
											</select>
										</div>
									<?php }?>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
										<div id="messahe_test"></div>
										<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
											<span class="user_display_block">
												<select name="selected_users[]" id="selected_users" class="form-control mjschool-min-width-250px validate[required]" multiple="multiple">
													<?php
													$class_id = ! empty( $result->class_id ) ? $result->class_id : null;
													if ( $class_id === '' ) {
														$student_list = get_users(
															array(
																'role' => 'student',
															)
														);
													} else {
														$student_list = mjschool_get_student_by_class_id_and_section( $result->class_id, $result->section_id );
													}
													if ( ! empty( $student_list ) ) {
														$student_data = explode( ',', $result->student_id );
														foreach ( $student_list as $student_id ) {
															$selected = '';
															if ( in_array( $student_id->ID, $student_data ) ) {
																$selected = 'selected';
															}
															?>
															<option value="<?php echo esc_attr( $student_id->ID ); ?>" <?php echo esc_attr( $selected ); ?>>
																<?php echo esc_html( mjschool_student_display_name_with_roll( $student_id->ID ) ); ?>
															</option>
															<?php
														}
													}
													?>
												</select>
											</span>
											<span class="mjschool-multiselect-label">
												<label class="ml-1 mjschool-custom-top-label top" for="selected_users">
													<?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span>
												</label>
											</span>
										</div>
									</div>
									<?php
								}
								?>
								<?php wp_nonce_field( 'save_payment_fees_admin_nonce' ); ?>
								<div class="col-md-6 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px">
									<div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
										<select name="fees_id[]" multiple="multiple" id="fees_data" class="form-control validate[required] mjschool-max-width-100px">
											<?php
											if ( $edit ) {
												$fees_data = mjschool_get_fees_by_class_id( $result->class_id );
												if ( ! empty( $fees_data ) ) {
													$fees_id = explode( ',', $result->fees_id );
													foreach ( $fees_data as $id ) {
														if ( mjschool_get_fees_term_name( $id->fees_id ) !== ' ' ) {
															$selected = '';
															if ( in_array( $id->fees_id, $fees_id ) ) {
																$selected = 'selected';
															}
															?>
															<option value="<?php echo esc_attr( $id->fees_id ); ?>" <?php echo esc_attr( $selected ); ?>>
																<?php echo esc_html( mjschool_get_fees_term_name( $id->fees_id ) ); ?>
															</option>
															<?php
														}
													}
												}
											}
											?>
										</select>
										<span class="mjschool-multiselect-label">
											<label class="ml-1 mjschool-custom-top-label top" for="fees_data">
												<?php esc_html_e( 'Select Fees Type', 'mjschool' ); ?><span class="required">*</span>
											</label>
										</span>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->fees_amount ); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['fees_amount'])) ); } else { echo '0'; } ?>" name="fees_amount" readonly>
											<label for="fees_amount">
												<?php esc_html_e( 'Amount', 'mjschool' ); ?>( <?php echo esc_attr( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span>
											</label>
										</div>
									</div>
								</div>
								<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
									<select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
										<?php
										if ( $edit ) {
											if ( $result->tax !== null ) {
												$tax_id = explode( ',', $result->tax );
											} else {
												$tax_id[] = '';
											}
										} else {
											$tax_id[] = '';
										}
										$mjschool_obj_tax = new Mjschool_Tax_Manage();
										$smgt_taxs        = $mjschool_obj_tax->mjschool_get_all_tax();
										if ( ! empty( $smgt_taxs ) ) {
											foreach ( $smgt_taxs as $data ) {
												$selected = '';
												if ( in_array( $data->tax_id, $tax_id ) ) {
													$selected = 'selected';
												}
												?>
												<option value="<?php echo esc_attr( $data->tax_id ); ?>" <?php echo esc_html( $selected ); ?>>
													<?php echo esc_html( $data->tax_title ); ?> - <?php echo esc_html( $data->tax_value ); ?>
												</option>
												<?php
											}
										}
										?>
									</select>
									<span class="mjschool-multiselect-label">
										<span class="ml-1 mjschool-custom-top-label top" for="staff_name"> <?php esc_html_e( 'Select Tax', 'mjschool' ); ?> </span>
									</span>
								</div>
								<div class="col-md-6 mjschool-note-text-notice">
									<div class="form-group input">
										<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
											<div class="form-field">
												<textarea name="description" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"> <?php if ( $edit ) { echo esc_textarea( $result->description ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_text_field(wp_unslash($_POST['description'])) ); } ?> </textarea>
												<span class="mjschool-txt-title-label"></span>
												<label class="text-area address active"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 input mb-0">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="start_date_event" class="form-control date_picker validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_year" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_year ) ) ) ); } elseif ( isset( $_POST['start_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['start_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
											<label class="active date_label" for="start_date_event">
												<?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
											</label>
										</div>
									</div>
								</div>
								<div class="col-md-6 input mb-0">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="end_date_event" class="form-control date_picker validate[required] start_date datepicker2" type="text" name="end_year" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->end_year ) ) ) ); } elseif ( isset( $_POST['end_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['end_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
											<label class="date_label" for="end_date_event">
												<?php esc_html_e( 'End Date', 'mjschool' ); ?>
												<span class="mjschool-require-field">*</span>
											</label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox mjschool-margin-15px-rtl mb-0">
									<div class="form-group">
										<div class="col-md-12 form-control">
											<div class="row mjschool-padding-radio">
												<div class="input-group">
													<label class="mjschool-custom-top-label" for="classis_limit">
														<?php esc_html_e( 'Status', 'mjschool' ); ?>
													</label>
													<div class="d-inline-block mjschool-gender-line-height-24px">
														<?php
														$status = 'no';
														if ( $edit ) {
															$recurrence_type = $result->status;
														} elseif ( isset( $_POST['status'] ) ) {
															$recurrence_type = sanitize_text_field( wp_unslash($_POST['status'] ) );
														}
														?>
														<label class="radio-inline">
															<input type="radio" value="yes" class="recurrence_type validate[required]" name="status" <?php checked( 'yes', esc_html( $recurrence_type ) ); ?> />
															<?php esc_html_e( 'Yes', 'mjschool' ); ?>
														</label>
														<label class="radio-inline"> 
															<input type="radio" value="no" class="recurrence_type validate[required]" name="status" <?php checked( 'no', esc_html( $recurrence_type ) ); ?> />
															<?php esc_html_e( 'No', 'mjschool' ); ?>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="form-body mjschool-user-form mjschool-margin-top-20px mjschool-padding-top-15px-res">
							<div class="row">
								<div class="col-sm-6">
									<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Recurring Invoice', 'mjschool' ); } else { esc_html_e( 'Create Invoice', 'mjschool' ); } ?>" name="save_recurring_feetype_payment" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to edit this record? This data change in next recurring invoice details.', 'mjschool' ); ?>' );" class="btn btn-success mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
				</div><!----- Panel body. --------->
				<?php
			}
			?>
		</div>
	</div>
	<?php
	if ( $active_tab === 'view_fessreceipt' ) {
		$fees_pay_id                = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['idtest'] ) ) ) );
		$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
		$fees_history_detail_result = mjschool_get_payment_history_by_feespayid( $fees_pay_id );
		$mjschool_obj_feespayment   = new Mjschool_Feespayment();
		$format                     = get_option( 'mjschool_invoice_option' );
		$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
		$mjschool_custom_field_obj  = new Mjschool_Custome_Field();
		$module                     = 'fee_transaction';
		$user_custom_field          = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="penal-body"><!----- Panel body. --------->
			<?php
			$retrieve_class_data = $mjschool_obj_feespayment->mjschool_get_all_fees_payments( $fees_pay_id );
			if ( ! empty( $retrieve_class_data ) ) {
				?>
				<div class="mjschool-panel-body">
					<div class="table-responsive">
						<form id="mjschool-common-form" name="mjschool-common-form" method="post">
							<table id="feetype_list_receipt" class="display mjschool-admin-feestype-datatable" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
										<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Payment Method', 'mjschool' ); ?></th>
										<th><?php esc_attr_e( 'Payment Date', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Transaction id', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Note', 'mjschool' ); ?></th>
										<?php
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													?>
													<th><?php echo esc_html( $custom_field->field_label ); ?></th>
													<?php
												}
											}
										}
										?>
										<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									$i = 0;
									foreach ( $retrieve_class_data as $retrieved_data ) {
										$color_class_css = mjschool_table_list_background_color( $i );
										?>
										<tr>
											<td class="mjschool-checkbox-width-10px">
												<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->fees_id ); ?>">
											</td>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png' ); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
												</p>
											</td>
											<td>
												<?php echo esc_html( $retrieved_data->amount ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Amount', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( $retrieved_data->payment_method ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Method', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->paid_by_date ) ); ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Date', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo ! empty( $retrieved_data->trasaction_id ) ? esc_html( $retrieved_data->trasaction_id ) : 'N/A'; ?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Transaction Id', 'mjschool' ); ?>"></i>
											</td>
											<?php
											$comment     = $retrieved_data->payment_note;
											$comment     = ltrim( $comment, ' ' );
											$description = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
											?>
											<td>
												<?php
												if ( ! empty( $comment ) ) {
													echo esc_html( $description );
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_html( $comment ); } else { esc_html_e( 'Description', 'mjschool' ); } ?>"></i>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module             = 'fee_transaction';
														$custom_field_id    = $custom_field->id;
														$module_record_id   = $retrieved_data->payment_history_id;
														$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
														if ( $custom_field->field_type === 'date' ) {
															?>
															<td>
																<?php
																if ( ! empty( $custom_field_value ) ) {
																	echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
															</td>
															<?php
														} elseif ( $custom_field->field_type == 'file' ) {
															?>
															<td>
																<?php
																if ( ! empty( $custom_field_value ) ) {
																	?>
																	<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile">
																		<button class="btn btn-default view_document" type="button">
																			<i class="fas fa-download"></i>
																			<?php esc_html_e( 'Download', 'mjschool' ); ?>
																		</button>
																	</a>
																	<?php
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
															</td>
															<?php
														} else {
															?>
															<td>
																<?php
																if ( ! empty( $custom_field_value ) ) {
																	echo esc_html( $custom_field_value );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
															</td>
															<?php
														}
													}
												}
											}
											?>
											<td class="action">
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<li class="mjschool-float-left-width-100px">
																	<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=' . esc_attr( mjschool_encrypt_id( $retrieved_data->fees_pay_id ) ) . '&payment_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->payment_history_id ) ) . '&view_type=view_receipt&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px">
																		<i class="fas fa-eye"></i>
																		<?php esc_html_e( 'View Receipt', 'mjschool' ); ?>
																	</a>
																</li>
															</ul>
														</li>
													</ul>
												</div>
											</td>
										</tr>
										<?php
										++$i;
									}
									?>
								</tbody>
							</table>
						</form>
					</div>
				</div>
				<?php
			}
			?>
		</div><!----- Panel body. --------->
		<?php
	}
	?>
</div>