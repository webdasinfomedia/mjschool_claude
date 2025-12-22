<?php
/**
 * Admin Fees and Payment Management Interface.
 *
 * This file manages the backend operations for creating, editing, deleting, and viewing fee types 
 * and fee payment transactions within the MJSchool plugin. It ensures secure handling of financial 
 * data, supports custom fields, and provides a dynamic and user-friendly interface for administrators.
 *
 * Key Features:
 * - Implements role-based access control for CRUD (Create, Read, Update, Delete) operations.
 * - Supports management of fee types, payment history, and recurring payments.
 * - Integrates DataTables for responsive, searchable, and sortable data listing.
 * - Includes bulk deletion functionality with secure nonce validation.
 * - Uses jQuery validation engine, datepickers, and Bootstrap multiselect.
 * - Supports dynamic custom fields (text, date, file, etc.) for fees and payments.
 * - Displays success and error messages dynamically via admin alerts.
 * - Handles secure file downloads for uploaded payment documents.
 * - Fully translation-ready and compatible with WordPress admin UI.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- CHECK BROWSER JAVASCRIPT. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'feepayment' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( isset( $_REQUEST['action'] ) && ! empty( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
			$sanitized_action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			if ( 'feepayment' === $user_access['page_link'] && ( $sanitized_action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'feepayment' === $user_access['page_link'] && ( $sanitized_action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'feepayment' === $user_access['page_link'] && ( $sanitized_action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'fee_pay';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$mjschool_obj_fees        = new Mjschool_Fees();
$mjschool_obj_feespayment = new Mjschool_Feespayment();
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {

		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		if ( isset( $_REQUEST['fees_id'] ) ) {
			$result = $mjschool_obj_fees->mjschool_delete_feetype_data( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['fees_id'] ) ) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feeslist&_wpnonce=' . rawurlencode( $nonce ) . '&message=feetype_del' ) );
				die();
			}
		}
		if ( isset( $_REQUEST['fees_pay_id'] ) ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_fee_payment_data( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['fees_pay_id'] ) ) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=fee_del' ) );
				die();
			}
		}
		if ( isset( $_REQUEST['recurring_fees_id'] ) ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_recurring_fees( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['recurring_fees_id'] ) ) ) );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=fee_del' ) );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected_feetype'] ) ) {
	if ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
		$sanitized_ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $sanitized_ids as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_feetype_data( $id );
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'Fees Type Deleted Successfully.', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
			</button>
		</div>
		<?php
	}
}
if ( isset( $_REQUEST['delete_selected_feelist'] ) ) {
	if ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
		$sanitized_ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $sanitized_ids as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_fee_payment_data( $id );
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'Fee Deleted Successfully.', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
			</button>
		</div>
		<?php
	}
}
if ( isset( $_REQUEST['delete_selected_recurring_feelist'] ) ) {
	if ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
		$sanitized_ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $sanitized_ids as $id ) {
			$result = $mjschool_obj_feespayment->mjschool_delete_recurring_fees( $id );
		}
	}
	if ( $result ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'Fee Deleted Successfully.', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
			</button>
		</div>
		<?php
	}
}
if ( isset( $_POST['save_feetype'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_fees_type_admin_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		$sanitized_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( $sanitized_action === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
				$fees_id                   = isset( $_REQUEST['fees_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fees_id'] ) ) : '';
				$result                    = $mjschool_obj_fees->mjschool_add_fees( wp_unslash( $_POST ) );
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'fee_pay';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $fees_id );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feeslist&_wpnonce=' . rawurlencode( $nonce ) . '&message=fee_edit' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} elseif ( ! $mjschool_obj_fees->mjschool_is_duplicat_fees( sanitize_text_field( wp_unslash( $_POST['fees_title_id'] ) ), sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) ) ) {
			$result                    = $mjschool_obj_fees->mjschool_add_fees( wp_unslash( $_POST ) );
			$module                    = 'fee_pay';
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feeslist&_wpnonce=' . rawurlencode( $nonce ) . '&message=feetype_add' ) );
				die();
			}
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feeslist&_wpnonce=' . rawurlencode( $nonce ) . '&message=fee_dub' ) );
			die();
		}
	}
}
if ( isset( $_POST['add_feetype_payment'] ) ) {
	$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
	$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment_history( wp_unslash( $_POST ) );
	$module                    = 'fee_transaction';
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=1' ) );
		die();
	}
}
//Update Recurring Invoice Data.
if ( isset( $_POST['save_recurring_feetype_payment'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_payment_fees_admin_nonce' ) ) {

		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) ) );
		if ( $start_date <= $end_date ) {
			$sanitized_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			if ( $sanitized_action === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
					$result = $mjschool_obj_feespayment->mjschool_add_recurring_feespayment( wp_unslash( $_POST ) );
					if ( $result ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=recurring_feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=recurring_feetype_edit' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			}
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html__( 'End Date should be greater than Start Date.', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
				</button>
			</div>
			<?php
		}
	}
}
if ( isset( $_POST['save_feetype_payment'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_payment_fees_admin_nonce' ) ) {
		$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
		if ( isset( $_REQUEST['mjschool_enable_feesalert_mail'] ) ) {
			update_option( 'mjschool_enable_feesalert_mail', 1 );
		} else {
			update_option( 'mjschool_enable_feesalert_mail', 0 );
		}
		$start_date = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['start_year'] ) ) ) );
		$end_date   = date( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['end_year'] ) ) ) );
		if ( $start_date <= $end_date ) {
			$sanitized_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			if ( $sanitized_action === 'edit' ) {
				if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
					$fees_pay_id               = isset( $_REQUEST['fees_pay_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['fees_pay_id'] ) ) : '';
					$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment( wp_unslash( $_POST ) );
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'fee_list';
					$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $fees_pay_id );
					if ( $result ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=feetype_edit' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$result                    = $mjschool_obj_feespayment->mjschool_add_feespayment( wp_unslash( $_POST ) );
				$module                    = 'fee_list';
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=fee_add' ) );
					die();
				}
			}
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html__( 'End Date should be greater than Start Date.', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
					<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
				</button>
			</div>
			<?php
		}
	}
}
// Fees Reminder for Student and Parent.
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'reminder' && isset( $_REQUEST['fees_pay_id'] ) ) {

	$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
	$fees_id      = sanitize_text_field( wp_unslash( $_REQUEST['fees_pay_id'] ) );
	$data         = $mjschool_obj_feespayment->mjschool_get_single_fee_payment( $fees_id );
	$student_id   = $data->student_id;
	$studentinfo  = get_userdata( $student_id );
	$student_mail = $studentinfo->user_email;
	$student_name = $studentinfo->display_name;
	$parent_id    = get_user_meta( $student_id, 'parent_id', true );
	foreach ( $parent_id as $id ) {
		$parentinfo = get_userdata( $id );
	}
	$parent_mail = $parentinfo->user_email;
	$parent_name = $parentinfo->display_name;
	$to          = $parent_mail;
	$Due_amt     = $data->total_amount - $data->fees_paid_amount;
	$due_amount  = number_format( $Due_amt, 2, '.', '' );
	// SMS Notification.
	$current_mjschool_service = get_option( 'mjschool_service' );
	if ( ! empty( $parent_id ) ) {
		foreach ( $parent_id as $user_id ) {
			$SMSArr                     = array();
			$parentinfo                 = get_userdata( $user_id );
			$parent_name                = $parentinfo->display_name;
			$SMSCon                     = get_option( 'mjschool_fees_payment_reminder_mjschool_content' );
			$SMSArr['{{parent_name}}']  = $parent_name;
			$SMSArr['{{student_name}}'] = $student_name;
			$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
			$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
			$type                       = 'Feeslist';
			mjschool_send_mjschool_notification( $user_id, $type, $message_content );
		}
	}
	// Mail Notification For Student.
	$student_mail              = $studentinfo->user_email;
	$student_name              = $studentinfo->display_name;
	$Due_amt                   = $data->total_amount - $data->fees_paid_amount;
	$due_amount                = number_format( $Due_amt, 2, '.', '' );
	$total_amount              = number_format( $data->total_amount, 2, '.', '' );
	$subject                   = get_option( 'mjschool_fee_payment_reminder_title_for_student' );
	$Seach['{{student_name}}'] = $student_name;
	$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
	$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
	$Seach['{{class_name}}']   = mjschool_get_class_name( $data->class_id );
	$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
	$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent_for_student' ) );
	if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
		$send = mjschool_send_mail_paid_invoice_pdf( $student_mail, $subject, $MsgContent, $fees_id );
		$send = 1;
	}
	// Mail Notification For Parent.
	if ( is_array( $parent_id ) || is_object( $parent_id ) ) {
		foreach ( $parent_id as $id ) {
			$parentinfo                = get_userdata( $id );
			$parent_mail               = $parentinfo->user_email;
			$parent_name               = $parentinfo->display_name;
			$Due_amt                   = $data->total_amount - $data->fees_paid_amount;
			$due_amount                = number_format( $Due_amt, 2, '.', '' );
			$total_amount              = number_format( $data->total_amount, 2, '.', '' );
			$subject                   = get_option( 'mjschool_fee_payment_reminder_title' );
			$Seach['{{student_name}}'] = $student_name;
			$Seach['{{parent_name}}']  = $parent_name;
			$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
			$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
			$Seach['{{class_name}}']   = mjschool_get_class_name( $data->class_id );
			$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
			$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent' ) );
			if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
				$send = mjschool_send_mail_paid_invoice_pdf( $parent_mail, $subject, $MsgContent, $fees_id );
			}
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=mail_success' ) );
	die();
}
if ( isset( $_REQUEST['fees_reminder_feeslist'] ) ) {

	$nonce = wp_create_nonce( 'mjschool_feespayment_tab' );
	if ( isset( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) {
		$sanitized_ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $sanitized_ids as $id ) {
			$fees_id     = $id;
			$data        = $mjschool_obj_feespayment->mjschool_get_single_fee_mjschool_payment( $fees_id );
			$student_id  = $data->student_id;
			$studentinfo = get_userdata( $student_id );
			$parent_id   = get_user_meta( $student_id, 'parent_id', true );
			// Mail Notification For Student.
			$student_mail              = $studentinfo->user_email;
			$student_name              = $studentinfo->display_name;
			$Due_amt                   = $data->total_amount - $data->fees_paid_amount;
			$due_amount                = number_format( $Due_amt, 2, '.', '' );
			$total_amount              = number_format( $data->total_amount, 2, '.', '' );
			$subject                   = get_option( 'mjschool_fee_payment_reminder_title_for_student' );
			$Seach['{{student_name}}'] = $student_name;
			$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
			$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
			$Seach['{{class_name}}']   = mjschool_get_class_name( $data->class_id );
			$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
			$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent_for_student' ) );
			if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
				$mail_send = mjschool_send_mail_paid_invoice_pdf( $student_mail, $subject, $MsgContent, $fees_id );
				$mail_send = 1;
			}
			// Mail Notification For Parent.
			if ( is_array( $parent_id ) || is_object( $parent_id ) ) {
				$device_token = array();
				foreach ( $parent_id as $id ) {
					$parentinfo                = get_userdata( $id );
					$device_token[]            = get_user_meta( $data->student_id, 'token_id', true );
					$parent_mail               = $parentinfo->user_email;
					$parent_name               = $parentinfo->display_name;
					$to                        = $parent_mail;
					$Due_amt                   = $data->total_amount - $data->fees_paid_amount;
					$due_amount                = number_format( $Due_amt, 2, '.', '' );
					$total_amount              = number_format( $data->total_amount, 2, '.', '' );
					$subject                   = get_option( 'mjschool_fee_payment_reminder_title' );
					$Seach['{{student_name}}'] = $student_name;
					$Seach['{{parent_name}}']  = $parent_name;
					$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
					$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
					$Seach['{{class_name}}']   = mjschool_get_class_name( $data->class_id );
					$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
					$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent' ) );
					$from                      = get_option( 'mjschool_name' );
					$fromemail                 = get_option( 'mjschool_email' );
					$headers                   = "MIME-Version: 1.0\r\n";
					$headers                  .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						$mail_send = mjschool_send_mail_paid_invoice_pdf( $to, $subject, $MsgContent, $fees_id );
						$mail_send = 1;
					}
				}
			}
			if ( $mail_send ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=mail_success' ) );
				die();
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . rawurlencode( $nonce ) . '&message=mail_faild' ) );
				die();
			}
			// Send Push Notification.
			$title = esc_attr__( 'New Notification For Fees Payment', 'mjschool' );
			$text  = esc_attr__( 'A Reminder of an Unpaid Fee Payment', 'mjschool' );
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'notification',
				),
			);
			$json    = json_encode( $notification_data );
			$message = mjschool_send_push_notification( $json );
			// Send Push Notification.
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'feeslist';
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-fees-type-add-height">
		<div class="modal-content mjschool-fees-type-model-height">
			<div class=" invoice_data"></div>
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-page-inner">
	<div class="payment_list mjschool-main-list-margin-5px mjschool-tab-margin-top-40px">
		<?php
		$message_string = '';
		if ( isset( $_REQUEST['message'] ) ) {
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '';
			switch ( $message ) {
				case 'feetype_del':
					$message_string = esc_html__( 'Fees Type Deleted Successfully.', 'mjschool' );
					break;
				case 'fee_del':
					$message_string = esc_html__( 'Fees Payment Deleted Successfully.', 'mjschool' );
					break;
				case 'fee_edit':
					$message_string = esc_html__( 'Fees Type Updated Successfully.', 'mjschool' );
					break;
				case 'fee_add':
					$message_string = esc_html__( 'Fees Payment Added Successfully.', 'mjschool' );
					break;
				case 'fee_dub':
					$message_string = esc_html__( 'Duplicate Fees.', 'mjschool' );
					break;
				case 'feetype_edit':
					$message_string = esc_html__( 'Fees Payment Updated Successfully.', 'mjschool' );
					break;
				case 'feetype_add':
					$message_string = esc_html__( 'Fees Type Added Successfully.', 'mjschool' );
					break;
				case 'mail_success':
					$message_string = esc_html__( 'Fees Payment Reminder Sent Successfully.', 'mjschool' );
					break;
				case 'mail_faild':
					$message_string = esc_html__( 'We Can Not Send Mail Reminders.', 'mjschool' );
					break;
				case 'recurring_feetype_edit':
					$message_string = esc_html__( 'Recurring Invoice Updated Successfully.', 'mjschool' );
					break;
				default:
					$message_string = esc_html__( 'Payment Added Successfully.', 'mjschool' );
			}
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
					<span class="screen-reader-text"><?php esc_attr_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
				</button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php
				if ( $active_tab != 'view_fesspayment' ) {
					$mjschool_action = '';
					if ( ! empty( $_REQUEST['action'] ) ) {
						$mjschool_action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
					}
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_feespayment_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'feeslist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=feeslist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'feeslist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Fees Type List', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab === 'addfeetype' && $mjschool_action === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'addfeetype' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addfeetype' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addfeetype' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Edit Fees Type', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} elseif ( $active_tab === 'addfeetype' ) {
							?>
							<li class="<?php if ( $active_tab === 'addfeetype' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addfeetype' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addfeetype' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Add Fees Type', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
						<li class="<?php if ( $active_tab === 'feespaymentlist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=feespaymentlist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'feespaymentlist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Fees Payment List', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab === 'addpaymentfee' && $mjschool_action === 'edit' ) {
							?>
							<li class="<?php if ( $active_tab === 'addpaymentfee' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addpaymentfee' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addpaymentfee' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Edit Payment Fees', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} elseif ( $active_tab === 'addpaymentfee' ) {
							?>
							<li class="<?php if ( $active_tab === 'addpaymentfee' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addpaymentfee' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addpaymentfee' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Add Fees Payment', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						$recurring_option = get_option( 'mjschool_enable_recurring_invoices' );
						if ( $recurring_option === 'yes' ) {
							?>
							<li class="<?php if ( $active_tab === 'recurring_feespaymentlist' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=recurring_feespaymentlist&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'recurring_feespaymentlist' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Recurring Fees Payment List', 'mjschool' ); ?>
								</a>
							</li>
							<?php
							if ( $active_tab === 'addrecurringpayment' && $mjschool_action === 'edit' ) {
								?>
								<li class="<?php if ( $active_tab === 'addrecurringpayment' ) { ?>active<?php } ?>">
									<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addrecurringpayment' ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addrecurringpayment' ? 'active' : ''; ?>">
										<?php esc_html_e( 'Edit Recurring Fees Payment', 'mjschool' ); ?>
									</a>
								</li>
								<?php
							}
						}
						if ( $active_tab === 'view_fessreceipt' ) {
							?>
							<li class="<?php if ( $active_tab === 'view_fessreceipt' ) { ?>active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'view_fessreceipt' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Payment History', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
				}
				if ( $active_tab === 'feeslist' ) {

					// Check nonce for fees list tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_feespayment_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					$retrieve_class_data = $mjschool_obj_fees->mjschool_get_all_fees();
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="feetype_list" class="display mjschool-admin-feestype-datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
												<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?> </th>
												<th><?php esc_html_e( 'Fees Amount', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
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
												if ( $i === 10 ) {
													$i = 0;
												}
												if ( $i === 0 ) {
													$color_class_css = 'mjschool-class-color0';
												} elseif ( $i === 1 ) {
													$color_class_css = 'mjschool-class-color1';
												} elseif ( $i === 2 ) {
													$color_class_css = 'mjschool-class-color2';
												} elseif ( $i === 3 ) {
													$color_class_css = 'mjschool-class-color3';
												} elseif ( $i === 4 ) {
													$color_class_css = 'mjschool-class-color4';
												} elseif ( $i === 5 ) {
													$color_class_css = 'mjschool-class-color5';
												} elseif ( $i === 6 ) {
													$color_class_css = 'mjschool-class-color6';
												} elseif ( $i === 7 ) {
													$color_class_css = 'mjschool-class-color7';
												} elseif ( $i === 8 ) {
													$color_class_css = 'mjschool-class-color8';
												} elseif ( $i === 9 ) {
													$color_class_css = 'mjschool-class-color9';
												}
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->fees_id ); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
															
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png' ); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
															
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
														<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->fees_amount, 2, '.', '' ) ) ); ?>
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
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_attr_e( 'Description', 'mjschool' ); } ?>"></i>
													</td>
													<?php
													// Custom Field Values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
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
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
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
																		<?php
																		if ( $user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addfeetype&action=edit&fees_id=' . mjschool_encrypt_id( $retrieved_data->fees_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px">
																					<i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=feeslist&action=delete&fees_id=' . mjschool_encrypt_id( $retrieved_data->fees_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjschool-para-margin" value="<?php echo esc_attr( $retrieved_data->fees_id ); ?>" >
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php
										if ( $user_access_delete === '1' ) {
											 ?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_feetype" class="delete_selected">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>">
											</button>
											<?php 
										}
										?>
									</div>
								</form>
							</div>
						</div>
						<?php
					} elseif ( $user_access_add === '1' ) {
						?>
						<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addfeetype' ) ); ?>">
								
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								
							</a>
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							
							<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							
						</div>
						<?php
					}
				}
				if ( $active_tab === 'addfeetype' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/add-feetype.php';
				}
				if ( $active_tab === 'recurring_feespaymentlist' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/fees-payment-recurring-list.php';
				}
				if ( $active_tab === 'feespaymentlist' ) {
					// Check nonce for feespayment list tab.
					if ( isset( $_GET['tab'] ) ) {
						if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_feespayment_tab' ) ) {
							wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
						}
					}
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'fee_list';
					$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
					$retrieve_class_data            = $mjschool_obj_feespayment->mjschool_get_all_fees();
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="fee_paymnt" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Fees Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?> </th>
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
												if ( $i === 10 ) {
													$i = 0;
												}
												if ( $i === 0 ) {
													$color_class_css = 'mjschool-class-color0';
												} elseif ( $i === 1 ) {
													$color_class_css = 'mjschool-class-color1';
												} elseif ( $i === 2 ) {
													$color_class_css = 'mjschool-class-color2';
												} elseif ( $i === 3 ) {
													$color_class_css = 'mjschool-class-color3';
												} elseif ( $i === 4 ) {
													$color_class_css = 'mjschool-class-color4';
												} elseif ( $i === 5 ) {
													$color_class_css = 'mjschool-class-color5';
												} elseif ( $i === 6 ) {
													$color_class_css = 'mjschool-class-color6';
												} elseif ( $i === 7 ) {
													$color_class_css = 'mjschool-class-color7';
												} elseif ( $i === 8 ) {
													$color_class_css = 'mjschool-class-color8';
												} elseif ( $i === 9 ) {
													$color_class_css = 'mjschool-class-color9';
												}
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>">
													</td>
													<td class="mjschool-user-image mjschool-width-50px-td">
														<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=view_fesspayment&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&view_type=view_payment' ); ?>">
															<?php
															$uid       = $retrieved_data->student_id;
															$umetadata = mjschool_get_user_image( $uid );
															
															if ( empty( $umetadata ) ) {
																echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
															} else {
																echo '<img src=' . esc_url( $umetadata ) . ' class="img-circle" />';
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
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->total_amount, 2, '.', '' ) ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->fees_paid_amount, 2, '.', '' ) ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Paid Amount', 'mjschool' ); ?>"></i>
													</td>
													<?php
													$Due_amt    = $retrieved_data->total_amount - $retrieved_data->fees_paid_amount;
													$due_amount = number_format( $Due_amt, 2, '.', '' );
													?>
													<td>
														<?php echo esc_html( mjschool_currency_symbol_position_language_wise( $due_amount ) ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_year ) ) . ' ' . esc_html__( 'To', 'mjschool' ) . ' ' . esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_year ) ); ?>
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
																			<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile">
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
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																		
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn"
																		aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=view_fesspayment&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&view_type=view_payment&_wpnonce_action=' . mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-float-left-width-100px">
																				<i class="fa fa-eye"></i><?php esc_html_e( 'View Invoice', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																		if ( ! empty( $retrieved_data->fees_paid_amount ) ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=view_fessreceipt&idtest=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-float-left-width-100px">
																					<i class="fa fa-eye"></i><?php esc_html_e( 'Payment History', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( ( $retrieved_data->fees_paid_amount < $retrieved_data->total_amount || $retrieved_data->fees_paid_amount === 0 ) && $retrieved_data->total_amount > 0 ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="#" class="mjschool-float-left-width-100px show-payment-popup" idtest="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" view_type="payment" due_amount="<?php echo esc_attr( $due_amount ); ?>">
																					<i class="fa fa-credit-card" aria-hidden="true"></i>
																					<?php esc_html_e( 'Pay', 'mjschool' ); ?>
																				</a>
																			</li>
																			<li class="mjschool-float-left-width-100px">
																				
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=feespaymentlist&action=reminder&fees_pay_id=' . $retrieved_data->fees_pay_id ); ?>" class="mjschool-float-left-width-100px " name="fees_reminder" id="fees_reminder_single">
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-payment-reminder-table.png' ); ?>" class="mjschool_height_15px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Reminder', 'mjschool' ); ?>
																				</a>
																				
																			</li>
																			<?php
																		}
																		?>
																		<?php
																		if ( $user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=addpaymentfee&action=edit&fees_pay_id=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit">
																					</i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?page=mjschool_fees_payment&tab=feespaymentlist&action=delete&fees_pay_id=' . mjschool_encrypt_id( $retrieved_data->fees_pay_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjschool-para-margin" value="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" >
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php
										if ( $user_access_delete === '1' ) {
											 ?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_feelist" class="delete_selected">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>">
											</button>
											<?php 
										}
										 ?>
										<button data-toggle="tooltip" id="fees_reminder" title="<?php esc_attr_e( 'Fees Payment Remainder', 'mjschool' ); ?>" name="fees_reminder_feeslist" class="delete_selected select_reminder_background fees_reminder">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-payment-reminder.png' ); ?>">
										</button>
										
									</div>
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
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							
							<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							
						</div>
						<?php
					}
				}
				if ( $active_tab === 'addpaymentfee' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/add-paymentfee.php';
				}
				if ( $active_tab === 'addrecurringpayment' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/add-recurring-paymentfee.php';
				} elseif ( $active_tab === 'view_fesspayment' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/fees-payment-invoice.php';
				} elseif ( $active_tab === 'view_fessreceipt' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/fees-receipt.php';
				}
				?>
			</div>
		</div>
	</div>
</div>