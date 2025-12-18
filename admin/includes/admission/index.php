<?php
/**
 * Mjschool admin admission index page.
 * 
 * The admin-specific functionality for the Admission module.
 *
 * Handles student admission management, activation, approval, email/SMS notifications,
 * and access right verification for the Mjschool plugin.
 *
 * @since      1.0.0
 * @since      2.0.1 Security hardening - Added nonce verification, file validation, path traversal protection
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/admission
 */
defined( 'ABSPATH' ) || exit;

// -------- Check browser javascript.. ----------//
mjschool_browser_javascript_check();

$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
}
else
{
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'admission' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'admission' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'admission' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'admission' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}

$mjschool_obj_admission    = new Mjschool_admission();
$mjschool_custom_field_obj = new mjschool_custome_field();
$module                    = 'admission';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );

// ------------ ACTIVE ADMISSION. ------------//
if ( isset( $_POST['active_user_admission'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'save_active_student_admission_nonce' ) ) {
		wp_die( esc_attr__( 'Security check failed.', 'mjschool' ) );
	}
	
	$userbyroll_no = get_users(
		array(
			'meta_query' =>
				array(
					'relation' => 'AND',
					array( 'key' => 'class_name', 'value' => sanitize_text_field(wp_unslash($_POST['class_name'])) ),
					array( 'key' => 'roll_id', 'value' => mjschool_strip_tags_and_stripslashes(sanitize_text_field(wp_unslash($_POST['roll_id']))) )
				),
			'role' => 'student'
		)
	);
	$is_rollno = count($userbyroll_no);
	
	if ( $is_rollno ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list&message=6' ) );
		exit;
	}
	else
	{
		$active_user_id = intval( wp_unslash($_REQUEST['act_user_id'] ));
		update_user_meta( $active_user_id, 'roll_id', sanitize_text_field(wp_unslash($_REQUEST['roll_id'] )) );
		update_user_meta( $active_user_id, 'class_name', intval( wp_unslash($_REQUEST['class_name'] )) );
		update_user_meta( $active_user_id, 'class_section', intval( wp_unslash($_REQUEST['class_section'] )) );
		update_user_meta( $active_user_id, 'admission_fees', intval( wp_unslash($_REQUEST['admission_fees'] )) );
		$class_ids = sanitize_text_field(wp_unslash($_REQUEST['class_name']));
		if ( email_exists( sanitize_email( wp_unslash( $_REQUEST['email'] ) ) ) ) { // if the email is registered, we take the user from this.
			if ( ! empty( $_REQUEST['password'] ) ) {
				wp_set_password( sanitize_text_field( wp_unslash( $_REQUEST['password'] ) ), $active_user_id );
			}
		}
		if ( get_option( 'mjschool_combine' ) === 1 ) {
			if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
				$admission_fees_id     = sanitize_text_field(wp_unslash($_REQUEST['admission_fees']));
				$obj_fees              = new mjschool_fees();
				$admission_fees_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $admission_fees_id );
			}
		}
		if ( get_option( 'mjschool_combine' ) === 1 ) {
			if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
				$generated = mjschool_generate_admission_fees_invoice( $admission_fees_amount, $active_user_id, $admission_fees_id, $class_ids, 0, 'Admission Fees' );
			}
		}
		$user_info = get_userdata( intval( wp_unslash($_POST['act_user_id']) ) );
		if ( ! empty( $user_info ) ) {
			// --------- SEND STUDENT MAIL ACTIVE ACCOUNT. -----------//
			if ( isset( $_POST['student_approve_mail'] ) && ( sanitize_text_field(wp_unslash($_POST['student_approve_mail'])) === 1 ) ) {
				// STUDENT APPROVE MAIL FOR STUDENT
				$string                    = array();
				$string['{{user_name}}']   = $user_info->display_name;
				$string['{{school_name}}'] = get_option( 'mjschool_name' );
				$string['{{role}}']        = 'student';
				$string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
				$string['{{username}}']    = $user_info->user_login;
				$string['{{class_name}}']  = mjschool_get_class_section_name_wise( sanitize_text_field(wp_unslash($_REQUEST['class_name'])), sanitize_text_field(wp_unslash($_REQUEST['class_section'] )));
				$string['{{roll_no}}']     = sanitize_text_field(wp_unslash($_REQUEST['roll_id']));
				$string['{{email}}']       = $user_info->user_email;
				$string['{{Password}}']    = sanitize_text_field(wp_unslash($_REQUEST['password']));
				$MsgContent                = get_option( 'mjschool_add_approve_admission_mail_content' );
				$MsgSubject                = get_option( 'mjschool_add_approve_admisson_mail_subject' );
				$message                   = mjschool_string_replacement( $string, $MsgContent );
				$MsgSubject                = mjschool_string_replacement( $string, $MsgSubject );
				$email                     = $user_info->user_email;
				if ( get_option( 'mjschool_combine' ) === '1' && get_option( 'mjschool_admission_fees' ) === 'yes' ) {
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						mjschool_send_mail_paid_invoice_pdf( $email, get_option( 'mjschool_fee_payment_title' ), $message, $generated );
					}
				} else {
					mjschool_send_mail( $email, $MsgSubject, $message );
				}
				// STUDENT APPROVE MAIL FOR PARENT.
				if ( ( ! empty( $user_info->father_email ) ) && ( ! empty( $user_info->father_first_name ) ) ) {
					$string_parent                     = array();
					$string_parent['{{parent_name}}']  = $user_info->father_first_name . ' ' . $user_info->father_middle_name . ' ' . $user_info->father_last_name;
					$string_parent['{{student_name}}'] = $user_info->display_name;
					$string_parent['{{school_name}}']  = get_option( 'mjschool_name' );
					$string_parent['{{role}}']         = 'student';
					$string_parent['{{login_link}}']   = site_url() . '/index.php/mjschool-login-page';
					$string_parent['{{username}}']     = $user_info->user_login;
					$string_parent['{{class_name}}']   = mjschool_get_class_section_name_wise( sanitize_text_field(wp_unslash($_REQUEST['class_name'])), sanitize_text_field(wp_unslash($_REQUEST['class_section'] )));
					$string_parent['{{roll_no}}']      = sanitize_text_field(wp_unslash($_REQUEST['roll_id']));
					$string_parent['{{email}}']        = $user_info->user_email;
					$string_parent['{{Password}}']     = sanitize_text_field(wp_unslash($_REQUEST['password']));
					$MsgContent_parent                 = get_option( 'mjschool_admission_mailtemplate_content_for_parent' );
					$MsgSubject_parent                 = get_option( 'mjschool_admissiion_approve_subject_for_parent' );
					$message                           = mjschool_string_replacement( $string_parent, $MsgContent_parent );
					$MsgSubject                        = mjschool_string_replacement( $string_parent, $MsgSubject_parent );
					$email_parent                      = $user_info->father_email;
					if ( get_option( 'mjschool_combine' ) === '1' && get_option( 'mjschool_admission_fees' ) == 'yes' ) {
						if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
							mjschool_send_mail_paid_invoice_pdf( $email_parent, get_option( 'mjschool_fee_payment_title' ), $message, $generated );
						}
					} else {
						mjschool_send_mail( $email_parent, $MsgSubject, $message );
					}
				}
				if ( ( ! empty( $user_info->mother_email ) ) and ( ! empty( $user_info->mother_first_name ) ) ) {
					$string_parent                     = array();
					$string_parent['{{parent_name}}']  = $user_info->mother_first_name . ' ' . $user_info->mother_middle_name . ' ' . $user_info->mother_last_name;
					$string_parent['{{student_name}}'] = $user_info->display_name;
					$string_parent['{{school_name}}']  = get_option( 'mjschool_name' );
					$string_parent['{{role}}']         = 'student';
					$string_parent['{{login_link}}']   = site_url() . '/index.php/mjschool-login-page';
					$string_parent['{{username}}']     = $user_info->user_login;
					$string_parent['{{class_name}}']   = mjschool_get_class_section_name_wise( sanitize_text_field(wp_unslash($_REQUEST['class_name'])), sanitize_text_field(wp_unslash($_REQUEST['class_section'] )));
					$string_parent['{{roll_no}}']      = sanitize_text_field(wp_unslash($_REQUEST['roll_id']));
					$string_parent['{{email}}']        = $user_info->user_email;
					$string_parent['{{Password}}']     = sanitize_text_field(wp_unslash($_REQUEST['password']));
					$MsgContent_parent                 = get_option( 'mjschool_admission_mailtemplate_content_for_parent' );
					$MsgSubject_parent                 = get_option( 'admissiion_approve_subject_for_parent' );
					$message                           = mjschool_string_replacement( $string_parent, $MsgContent_parent );
					$MsgSubject                        = mjschool_string_replacement( $string_parent, $MsgSubject_parent );
					$email_parent                      = $user_info->mother_email;
					if ( get_option( 'mjschool_combine' ) === '1' && get_option( 'mjschool_admission_fees' ) === 'yes' ) {
						if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
							mjschool_send_mail_paid_invoice_pdf( $email_parent, get_option( 'mjschool_fee_payment_title' ), $message, $generated );
						}
					} else {
						mjschool_send_mail( $email_parent, $MsgSubject, $message );
					}
				}
			}
			// --------- SEND APPROVE SMS NOTIFICATION.  -----------//
			if ( isset( $_POST['student_approve_sms'] ) && ( sanitize_text_field(wp_unslash($_POST['student_approve_sms'])) === 1 ) ) {
				$SMSCon                     = get_option( 'mjschool_student_admission_approve_mjschool_content' );
				$SMSArr['{{student_name}}'] = $user_info->display_name;
				$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
				$type                       = 'Approved';
				$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
				mjschool_send_mjschool_notification( $user_info->ID, $type, $message_content );
			}
		}
		$role_update = 'student';
		$status      = 'Approved';
		$result      = new WP_User( $active_user_id );
		$result->set_role( $role_update );
		$result       = update_user_meta( $active_user_id, 'role', $role_update );
		$result       = update_user_meta( $active_user_id, 'status', $status );
		$sibling_data = $user_info->sibling_information;
		$result       = update_user_meta( $active_user_id, 'sibling_information', $sibling_data );
		if ( ! empty( $sibling_data ) ) {
			$sibling_data_array = json_decode( $sibling_data, true );
			if ( is_array( $sibling_data_array ) ) {
				foreach ( $sibling_data_array as $sibling_entry ) {
					$sibling_id = intval( $sibling_entry['siblingsstudent'] );
					if ( $sibling_id > 0 && $sibling_id !== $active_user_id ) {
						$existing_sibling_info  = get_user_meta( $sibling_id, 'sibling_information', true );
						$existing_sibling_array = ! empty( $existing_sibling_info ) ? json_decode( $existing_sibling_info, true ) : array();
						$already_exists         = false;
						foreach ( $existing_sibling_array as $info ) {
							if ( isset( $info['siblingsstudent'] ) && $info['siblingsstudent'] === $active_user_id ) {
								$already_exists = true;
								break;
							}
						}
						if ( ! $already_exists ) {
							$existing_sibling_array[] = array(
								'siblingsclass'   => sanitize_text_field(wp_unslash($_REQUEST['class_name'])),
								'siblingssection' => sanitize_text_field(wp_unslash($_REQUEST['class_section'])),
								'siblingsstudent' => $active_user_id,
							);
							update_user_meta( $sibling_id, 'sibling_information', json_encode( $existing_sibling_array ) );
						}
					}
				}
			}
		}
		$role_parents = 'parent';
		// ---------- ADD PARENTS. -------------------//
		$patents_add = $mjschool_obj_admission->mjschool_add_parent( $active_user_id, $role_parents );
		if ( get_user_meta( $active_user_id, 'hash', true ) ) {
			delete_user_meta( $active_user_id, 'hash' );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=7' ) );
		exit;
	}
	$active_user_id        = sanitize_text_field(wp_unslash($_REQUEST['act_user_id']));
	$user_info             = get_user_meta( $active_user_id );
	$admission_fees_amount = $user_info['admission_fees'][0];
	$admission_fees_id     = get_option( 'mjschool_admission_amount' );
	$class                 = $user_info['class_name'][0];
	$section               = $user_info['class_section'][0];
}

// ------------- SAVE STUDENT ADMISSION FORM. ------------------//
if ( isset( $_POST['student_admission'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_mjschool-admission-form' ) ) {
		$role_update = 'student';
		$mjschool_role = sanitize_text_field(wp_unslash($_POST['role']));
		if ( isset( $_FILES['father_doc'] ) && ! empty( $_FILES['father_doc'] ) ) {
			if ( $_FILES['father_doc']['size'] > 0 ) {
				$upload_docs = mjschool_load_documets_new( $_FILES['father_doc'], $_FILES['father_doc'], sanitize_text_field(wp_unslash($_POST['father_document_name'])) );
			} elseif ( isset( $_POST['father_doc_hidden'] ) ) {
				$upload_docs = sanitize_text_field(wp_unslash($_POST['father_doc_hidden']));
			} else {
				$upload_docs = '';
			}
		} else {
			$upload_docs = '';
		}
		$father_document_data = array();
		if ( ! empty( $upload_docs ) ) {
			$father_document_data[] = array(
				'title' => sanitize_text_field(wp_unslash($_POST['father_document_name'])),
				'value' => $upload_docs,
			);
		} else {
			$father_document_data[] = '';
		}
		if ( isset( $_FILES['mother_doc'] ) && ! empty( $_FILES['mother_doc'] ) ) {
			if ( $_FILES['mother_doc']['size'] > 0 ) {
				$upload_docs1 = mjschool_load_documets_new( $_FILES['mother_doc'], $_FILES['mother_doc'], $_POST['mother_document_name'] );
			} elseif ( isset( $_POST['mother_doc_hidden'] ) ) {
				$upload_docs = sanitize_text_field(wp_unslash($_POST['mother_doc_hidden']));
			} else {
				$upload_docs = '';
			}
		} else {
			$upload_docs1 = '';
		}
		$mother_document_data = array();
		if ( ! empty( $upload_docs1 ) ) {
			$mother_document_data[] = array(
				'title' => sanitize_text_field(wp_unslash($_POST['mother_document_name'])),
				'value' => $upload_docs1,
			);
		} else {
			$mother_document_data[] = '';
		}
		if ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'edit_action' ) ) {
				// ----------EDIT.-------------//
				$result = $mjschool_obj_admission->mjschool_add_admission( wp_unslash($_POST), $father_document_data, $mother_document_data, $role_update );
				// Custom Field File Update. //
				$mjschool_custom_field_obj = new mjschool_custome_field();
				$module                    = 'admission';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list&message=9' ) );
					exit;
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			// -------- Email Check --------//
			if ( email_exists( $_POST['email'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&message=2' ) );
				exit;
			} elseif ( email_exists( $_POST['father_email'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&message=3' ) );
				exit;
			} elseif ( email_exists( $_POST['mother_email'] ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&message=4' ) );
				exit;
			} else {
				// ----------ADD.-------------//
				$result                    = $mjschool_obj_admission->mjschool_add_admission( wp_unslash($_POST), $father_document_data, $mother_document_data, $mjschool_role );
				$mjschool_custom_field_obj = new mjschool_custome_field();
				$module                    = 'admission';
				$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list&message=1' ) );
					exit;
				}
			}
		}
	}
}

// ------------- DELETE ADMISSION.  ------------------//
// SECURITY FIX: Added nonce verification and array validation
if ( isset( $_REQUEST['delete_selected'] ) ) {
	// SECURITY FIX: Verify nonce before bulk delete
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_mjschool-admission-form' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	// SECURITY FIX: Validate array before iteration
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$deleted_count = 0;
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_usedata( intval( $id ) );
			if ( $result ) {
				$deleted_count++;
			}
		}
		
		if ( $deleted_count > 0 ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list&message=8' ) );
			exit;
		}
	}
}

// SECURITY FIX: Enhanced CSV export with proper validation
if ( isset( $_POST['admission_export_csv_selected'] ) ) {
	
	// Nonce verification (already present - GOOD!)
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mjschool-admission-export-nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	
	// SECURITY FIX: Validate array before processing
	if ( isset( $_POST['id'] ) && is_array( $_POST['id'] ) ) {
		$admission_list = array();
		
		foreach ( $_POST['id'] as $s_id ) {
			$user_data = get_userdata( intval( $s_id ) );
			if ( $user_data ) {
				$admission_list[] = $user_data;
			}
		}
		
		if ( ! empty( $admission_list ) ) {
			$header   = array();
			$header[] = 'Email';
			$header[] = 'First Name';
			$header[] = 'Middle Name';
			$header[] = 'Last Name';
			$header[] = 'Gender';
			$header[] = 'Birth Date';
			$header[] = 'Address';
			$header[] = 'City Name';
			$header[] = 'State Name';
			$header[] = 'Zip Code';
			$header[] = 'Previous School';
			$header[] = 'Mobile Number';
			$header[] = 'Alternate Mobile Number';
			$header[] = 'Father First Name';
			$header[] = 'Father middle Name';
			$header[] = 'Father Last Name';
			$header[] = 'Father Email';
			$header[] = 'Father Gender';
			$header[] = 'Father DOB';
			$header[] = 'Father Mobile';
			$header[] = 'Father Address';
			$header[] = 'Mother First Name';
			$header[] = 'Mother middle Name';
			$header[] = 'Mother Last Name';
			$header[] = 'Mother Email';
			$header[] = 'Mother Gender';
			$header[] = 'Mother DOB';
			$header[] = 'Mother Mobile';
			$header[] = 'Mother Address';
			
			$filename = 'export/mjschool-export-admission.csv';
			$file_path = MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename;
			$export_dir = dirname( $file_path );
			
			// SECURITY FIX: Ensure directory exists
			if ( ! file_exists( $export_dir ) ) {
				wp_mkdir_p( $export_dir );
			}
			
			// SECURITY FIX: Safe file handle with error checking
			$fh = fopen( $file_path, 'w' );
			if ( false === $fh ) {
				wp_die( esc_html__( 'Unable to create export file. Please check directory permissions.', 'mjschool' ) );
			}
			
			fputcsv( $fh, $header );
			
			foreach ( $admission_list as $retrive_data ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->ID );
				$row[]     = $user_info->user_email;
				$row[]     = get_user_meta( $retrive_data->ID, 'first_name', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'middle_name', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'last_name', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'gender', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'birth_date', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'address', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'city', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'state', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'zip_code', true );
				$row[]     = $user_info->preschool_name;
				$row[]     = get_user_meta( $retrive_data->ID, 'mobile_number', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'alternet_mobile_number', true );
				$row[]     = $user_info->father_first_name;
				$row[]     = $user_info->father_middle_name;
				$row[]     = $user_info->father_last_name;
				$row[]     = $user_info->father_email;
				$row[]     = $user_info->fathe_gender;
				$row[]     = mjschool_get_date_in_input_box( $user_info->father_birth_date );
				$row[]     = $user_info->father_mobile;
				$row[]     = $user_info->father_address;
				$row[]     = $user_info->mother_first_name;
				$row[]     = $user_info->mother_middle_name;
				$row[]     = $user_info->mother_last_name;
				$row[]     = $user_info->mother_email;
				$row[]     = $user_info->mother_gender;
				$row[]     = mjschool_get_date_in_input_box( $user_info->mother_birth_date );
				$row[]     = $user_info->mother_mobile;
				$row[]     = $user_info->mother_address;
				fputcsv( $fh, $row );
			}
			fclose( $fh );
			
			// SECURITY FIX: Secure file download
			ob_clean();
			
			$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-admission.csv';
			
			// SECURITY FIX: Validate file exists
			if ( ! file_exists( $file ) ) {
				wp_die( esc_html__( 'Export file not found.', 'mjschool' ) );
			}
			
			// SECURITY FIX: Prevent directory traversal
			$file_real = realpath( $file );
			$allowed_dir = realpath( MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/' );
			
			if ( false === $file_real || strpos( $file_real, $allowed_dir ) !== 0 ) {
				wp_die( esc_html__( 'Invalid file path.', 'mjschool' ) );
			}
			
			// SECURITY FIX: Validate file is readable
			if ( ! is_readable( $file_real ) ) {
				wp_die( esc_html__( 'File is not readable.', 'mjschool' ) );
			}
			
			// Set secure headers
			$mime = 'text/csv';
			header( 'Content-Type: application/force-download' );
			header( 'Pragma: public' );
			header( 'Expires: 0' );
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $file_real ) ) . ' GMT' );
			header( 'Cache-Control: private', false );
			header( 'Content-Type: ' . $mime );
			header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( basename( $file_real ) ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . filesize( $file_real ) );
			header( 'Connection: close' );
			
			readfile( $file_real );
			exit; // Use exit instead of die() for better WordPress practices
		}
	}
}

// -----------Delete Code.--------
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'delete_action' ) ) {
		$admission_id = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['admission_id'])) );
		$result       = mjschool_delete_usedata( $admission_id );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_admission&tab=admission_list&message=8' ) );
			exit;
		}// Proceed with the action.
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'admission_list'; {
	?>
	<!-- POP up code. -->
	<div class="mjschool-popup-bg">
		<div class="mjschool-overlay-content mjschool-admission-popup">
			<div class="modal-content">
				<div class="result mjschool-admission-approval-popup-rs"></div>
				<div class="mjschool-category-list"></div>
			</div>
		</div>
	</div>
	<div class="mjschool-page-inner"><!--------- page inner. -------->
		<div class="mjschool-main-list-margin-15px"><!----- mjschool-main-list-margin-15px.--------->
			<?php
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Admission Added Successfully.', 'mjschool' );
					break;
				case '2':
					$message_string = esc_html__( 'Student Email-id Already Exist.', 'mjschool' );
					break;
				case '3':
					$message_string = esc_html__( 'Father Email-id Already Exist.', 'mjschool' );
					break;
				case '4':
					$message_string = esc_html__( 'Mother Email-id Already Exist.', 'mjschool' );
					break;
				case '5':
					$message_string = esc_html__( 'Admision Added Successfully.', 'mjschool' );
					break;
				case '6':
					$message_string = esc_html__( 'Student Roll No. Already Exist.', 'mjschool' );
					break;
				case '7':
					$message_string = esc_html__( 'Student Record Approved Successfully.', 'mjschool' );
					break;
				case '8':
					$message_string = esc_html__( 'Admission Deleted Successfully.', 'mjschool' );
					break;
				case '9':
					$message_string = esc_html__( 'Admission Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible responsive_alert_message mjschool_margin_5px_10">
					<p>
						<?php echo esc_html( $message_string ); ?>
					</p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
			?>
			<div class="row"> <!------- Row Div. --------->
				<div class="col-md-12 mjschool-custom-padding-0"><!------- col-md-12 Div. --------->
					<div class="mjschool-main-list-page">
						<?php
						if ( $active_tab === 'admission_list' ) {
							if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
								?>
								<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url('https://www.youtube.com/embed/Qz-hbpQkJXY?si=migIY_WmRJha3Zqh'); ?>" title="<?php esc_attr_e( 'Student Admission Form: Step-by-Step Guide', 'mjschool' ); ?>">
									
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
									
								</a>
								<?php
							}
							$studentdata = get_users(
								array(
									'role'    => 'student_temp',
									'orderby' => 'user_registered',
									'order'   => 'DESC',
								)
							);
							if ( ! empty( $studentdata ) ) 
							{
								?>
								<div class="mjschool-panel-body">
									<div class="table-responsive">
										<form id="mjschool-common-form" name="mjschool-common-form" method="post">
											<table id="admission_list" class="display admin_student_datatable display responsive " width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
														<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Name & Email', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Admission No.', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
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
													if ( ! empty( $studentdata ) ) {
														foreach ( $studentdata as $retrieved_data ) {
															$admission_id = mjschool_encrypt_id( $retrieved_data->ID );
															$user_info    = get_userdata( $retrieved_data->ID );
															if( ! empty( $user_info->birth_date ) ) {
																$birth_date = mjschool_get_date_in_input_box( $user_info->birth_date );
															} else {
																$birth_date = "N/A";
															}
															?>
															<tr>
																<td class="mjschool-checkbox-width-10px"><input type="checkbox" name="id[]" class="mjschool-sub-chk selected_admission select-checkbox" value="<?php echo esc_attr( $retrieved_data->ID ); ?>"> </td>
																<td class="mjschool-user-image mjschool-width-50px-td">
																	<a href="<?php echo esc_url( '?page=mjschool_admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>">
																		<?php
																		$uid       = $retrieved_data->ID;
																		$umetadata = mjschool_get_user_image( $uid );
																		if ( empty( $umetadata ) ) {
																			
																			echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
																			
																		} else {
																			
																			echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
																			
																		}
																		?>
																	</a>
																</td>
																<td class="name">
																	<a class="mjschool-color-black" href="<?php echo esc_url( '?page=mjschool_admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>">
																		<?php echo esc_attr( $retrieved_data->display_name ); ?>
																	</a>
																	<br>
																	<span class="mjschool-list-page-email">
																		<?php echo esc_attr( $retrieved_data->user_email ); ?>
																	</span>
																</td>
																<td >+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>
																	<?php echo esc_attr( $user_info->mobile_number ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php echo esc_html( $user_info->admission_no ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Admission No.', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php echo esc_html( mjschool_get_date_in_input_box( $user_info->admission_date ) ); ?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Admission Date', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php echo esc_html( ucfirst( $user_info->gender ) ); ?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php echo esc_html( $birth_date); ?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<span class="mjschool-not-approved">
																		<?php
																		if ( ! empty( $user_info->status ) ) {
																			echo esc_html( $user_info->status );
																		} else {
																			esc_html_e( 'Not Approved', 'mjschool' );
																		}
																		?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
																	</span>
																</td>
																<?php
																// Custom Field Values.
																if ( ! empty( $user_custom_field ) ) {
																	foreach ( $user_custom_field as $custom_field ) {
																		if ( $custom_field->show_in_table === '1' ) {
																			$module             = 'admission';
																			$custom_field_id    = $custom_field->id;
																			$module_record_id   = $retrieved_data->ID;
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
																						$safe_custom_file = sanitize_file_name( $custom_field_value ); ?>
																						<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $safe_custom_file ); ?>" download="CustomFieldfile">
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
																				<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																					
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<li class="mjschool-float-left-width-100px">
																						<a href="<?php echo esc_url( '?page=mjschool_admission&tab=view_admission&action=view_admission&id=' . esc_attr( $admission_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-eye"> </i>
																							<?php esc_html_e( 'View', 'mjschool' ); ?>
																						</a>
																					</li>
																					<?php
																					if ( $user_info->role === 'student_temp' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							
																							<a href="<?php echo esc_url( '?page=mjschool_admission&tab=admission_list&action=approve&id=' . esc_attr( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px show-admission-popup " student_id="<?php echo esc_attr( $retrieved_data->ID ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-admission-approve.png"); ?>" class="mjschool_height_15px">&nbsp;&nbsp;&nbsp;<?php esc_html_e( 'Approve', 'mjschool' ); ?></a>
																							
																						</li>
																						<?php
																					}
																					if ( $user_access_edit === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																							<a href="<?php echo esc_url( '?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( $admission_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i>
																								<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																							</a>
																						</li>
																						<?php
																					}
																					if ( $user_access_delete === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( '?page=mjschool_admission&tab=studentlist&action=delete&admission_id=' . esc_attr( $admission_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
														}
													}
													?>
												</tbody>
											</table>
											<div class="mjschool-print-button pull-left">
												<button class="mjschool-btn-sms-color mjschool-button-reload">
													<input type="checkbox" id="mjschool-sub-chk" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
													<label for="mjschool-sub-chk" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
												</button>
												<?php if ( $user_access_delete === '1' ) { ?>
													<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php } ?>
												<?php wp_nonce_field( 'mjschool-admission-export-nonce' ); ?>
												<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="admission_export_csv_selected" class="admission_csv_selected mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
											</div>
										</form>
									</div>
								</div>
								<?php
							} elseif ( $user_access_add === '1' ) {
								?>
								<div class="mjschool-no-data-list-div">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form' ) ); ?>">
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
									
									<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
									
								</div>
								<?php
							}
						}
						if ( $active_tab === 'mjschool-admission-form' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/admission/admission-form.php';
						}
						if ( $active_tab === 'view_admission' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/admission/view-admission.php';
						}
						?>
					</div>
				</div><!------- col-md-12 Div. --------->
			</div><!------- Row Div. --------->
		</div><!----- mjschool-main-list-margin-15px.--------->
	</div><!--------- page inner. -------->
<?php
}
?>