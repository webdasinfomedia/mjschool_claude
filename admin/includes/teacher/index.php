<?php
/**
 * Teacher Management - Admin Dashboard.
 *
 * Handles CRUD operations (Create, Read, Update, Delete) for teachers within the MJSchool plugin.  
 * Provides a complete interface for administrators and authorized users to view, add, edit, delete,  
 * and manage teacher information including profile details, class assignments, and subjects.
 *
 * Key Features:
 * - Implements role-based access control for add, edit, delete, and view operations.
 * - Displays a searchable and sortable teacher list using DataTables integration.
 * - Includes multiselect dropdowns for class assignment and AJAX-based data updates.
 * - Supports bulk deletion of teacher records with validation and confirmation prompts.
 * - Provides success/error notifications (e.g., Add, Update, Delete, CSV Upload, etc.).
 * - Integrates with custom fields for module-specific dynamic data display.
 * - Implements jQuery Validation Engine for client-side input validation.
 * - Offers localized text and multi-language compatibility using WordPress translation functions.
 * - Ensures compliance with WordPress security standards (nonces, escaping, sanitization).
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/teacher
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'teacher' );
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
			if ( 'teacher' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'teacher' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'teacher' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'teacher';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$teacher_obj = new Mjschool_Teacher();
$mjschool_role        = 'teacher';
if ( isset( $_POST['save_teacher'] ) ) {
	if ( isset( $_FILES['signature'] ) && ! empty( $_FILES['signature']['name'] ) ) {
		if ( $_FILES['signature']['size'] > 0 ) {
			$signature = mjschool_upload_teacher_signature( $_FILES['signature'] );
		}
	} else {
		// Always fallback to existing signature if no new file is uploaded.
		$signature = isset( $_POST['signaturehidden'] ) ? sanitize_text_field(wp_unslash($_POST['signaturehidden'])) : '';
	}
	$firstname  = sanitize_text_field( wp_unslash($_POST['first_name']) );
	$middlename = sanitize_text_field( wp_unslash($_POST['middle_name']) );
	$lastname   = sanitize_text_field( wp_unslash($_POST['last_name']) );
	$userdata   = array(
		'user_login'    => sanitize_email( wp_unslash($_POST['email']) ),
		'user_nicename' => null,
		'user_email'    => sanitize_email( wp_unslash($_POST['email']) ),
		'user_url'      => null,
		'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
	);
	if ( $_POST['password'] != '' ) {
		$userdata['user_pass'] = mjschool_password_validation( wp_unslash($_POST['password']) );
	}
	if ( isset( $_POST['mjschool_user_avatar'] ) && $_POST['mjschool_user_avatar'] != '' ) {
		$photo = sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar']));
	} else {
		$photo = '';
	}
	$attechment = '';
	if ( ! empty( $_POST['attachment'] ) ) {
		$attechment = implode( ',', sanitize_text_field(wp_unslash($_POST['attachment'])) );
	}
	if ( ! empty( $_POST['phone'] ) ) {
		$phone = sanitize_text_field( wp_unslash($_POST['phone']) );
	} else {
		$phone = '';
	}
	if ( ! empty( $_POST['phone'] ) ) {
		$phone = sanitize_text_field( wp_unslash($_POST['phone']) );
	} else {
		$phone = '';
	}
	// Document upload file code start.
	$document_content = array();
	if ( ! empty( $_FILES['document_file']['name'] ) ) {
		$count_array = count( $_FILES['document_file']['name'] );
		for ( $a = 0; $a < $count_array; $a++ ) {
			if ( ( $_FILES['document_file']['size'][ $a ] > 0 ) && ( ! empty( $_POST['document_title'][ $a ] ) ) ) {
				$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
				$document_file  = mjschool_upload_document_user_multiple( $_FILES['document_file'], $a, sanitize_text_field(wp_unslash($_POST['document_title'][ $a ])) );
			} elseif ( ! empty( $_POST['user_hidden_docs'][ $a ] ) && ! empty( $_POST['document_title'][ $a ] ) ) {
				$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
				$document_file  = sanitize_text_field(wp_unslash($_POST['user_hidden_docs'][ $a ]));
			}
			if ( ! empty( $document_file ) && ! empty( $document_title ) ) {
				$document_content[] = array(
					'document_title' => $document_title,
					'document_file'  => $document_file,
				);
			}
		}
	}
	if ( ! empty( $document_content ) ) {
		$final_document = json_encode( $document_content );
	} else {
		$final_document = '';
	}
	// Document upload file code end.
	$usermetadata = array(
		'middle_name'            => sanitize_text_field( wp_unslash($_POST['middle_name']) ),
		'gender'                 => sanitize_text_field( wp_unslash($_POST['gender']) ),
		'birth_date'             => sanitize_text_field( wp_unslash($_POST['birth_date']) ),
		'address'                => sanitize_textarea_field( wp_unslash($_POST['address']) ),
		'city'                   => sanitize_text_field( wp_unslash($_POST['city_name']) ),
		'state'                  => sanitize_text_field( wp_unslash($_POST['state_name']) ),
		'zip_code'               => sanitize_text_field( wp_unslash($_POST['zip_code']) ),
		'designation'            => sanitize_text_field( wp_unslash($_POST['designation']) ),
		'class_name'             => sanitize_text_field(wp_unslash($_POST['class_name'])),
		'signature'              => $signature,
		'phone'                  => sanitize_text_field( wp_unslash($_POST['phone']) ),
		'mobile_number'          => sanitize_text_field( wp_unslash($_POST['mobile_number']) ),
		'user_document'          => $final_document,
		'alternet_mobile_number' => sanitize_text_field( wp_unslash($_POST['alternet_mobile_number']) ),
		'working_hour'           => sanitize_text_field( wp_unslash($_POST['working_hour']) ),
		'possition'              => sanitize_textarea_field( wp_unslash($_POST['possition']) ),
		'mjschool_user_avatar'       => $photo,
		'attachment'             => $attechment,
		'created_by'             => get_current_user_id(),
		'nickname'				 => sanitize_email( wp_unslash($_POST['email']) ),
	);
	if ( $action === 'edit' ) {
		$userdata['ID'] = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
		$result         = mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
		// Update custom field data.
		$custom_field_obj    = new Mjschool_Custome_Field();
		$module              = 'teacher';
		$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
		$result1             = $teacher_obj->mjschool_update_multi_class( sanitize_text_field(wp_unslash($_POST['class_name'])), mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) );
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=2' ) );
		die();
	} else {
		/* Setup wizard. */
		$wizard = mjschool_setup_wizard_steps_updates( 'step3_teacher' );
		if ( ! email_exists( $_POST['email'] ) ) {
			$result = mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
			// Add custom field data.
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'teacher';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			$result1            = $teacher_obj->mjschool_add_multi_class( sanitize_text_field(wp_unslash($_POST['class_name'])), mjschool_strip_tags_and_stripslashes( wp_unslash($_POST['email']) ) );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=1' ) );
			die();
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert updated_top mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php esc_html_e( 'Username Or Emailid All Ready Exist.', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
}
if ( $action === 'delete' ) {
	$teacher_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) );
	$result     = mjschool_delete_usedata( $teacher_id );
	if ( $result ) {
		$result = mjschool_delete_teacher_class_assignments($teacher_id);
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=5' ) );
		die();
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_usedata( $id );
			if ( $result ) {
				$result = mjschool_delete_teacher_class_assignments($id);
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=5' ) );
				die();
			}
		}
	}
}
// -------------- Export teacher data. ---------------//
if ( isset( $_POST['teacher_csv_selected'] ) ) {
	$teacher_list = array();
	if ( isset( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $p_id ) {
			$mjschool_user = get_userdata( $p_id );
			if ( $mjschool_user ) { // Only add valid WP_User objects.
				$teacher_list[] = $mjschool_user;
			}
		}
		if ( ! empty( $teacher_list ) ) {
			$header   = array();
			$header[] = 'Username';
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
			$header[] = 'Mobile Number';
			$header[] = 'Alternate Mobile Number';
			$header[] = 'Class Name';
			$filename = 'export/mjschool-export-teacher.csv';
			$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
			fputcsv( $fh, $header );
			foreach ( $teacher_list as $retrive_data ) {
				$row             = array();
				$class_name_data = array();
				$user_info       = get_userdata( $retrive_data->ID );
				$teacher_obj     = new Mjschool_Teacher();
				$teacher_class   = $teacher_obj->mjschool_get_teacher_class( $retrive_data->ID );
				foreach ( $teacher_class as $class_id ) {
					$class_name_data[] = mjschool_get_class_name_by_id( $class_id );
				}
				$class_name = implode( ',', $class_name_data );
				$row[]      = $user_info->user_login;
				$row[]      = $user_info->user_email;
				$row[]      = get_user_meta( $retrive_data->ID, 'first_name', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'middle_name', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'last_name', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'gender', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'birth_date', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'address', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'city', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'state', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'zip_code', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'mobile_number', true );
				$row[]      = get_user_meta( $retrive_data->ID, 'alternet_mobile_number', true );
				$row[]      = $class_name;
				fputcsv( $fh, $row );
			}
			fclose( $fh );
			// Download csv file.
			ob_clean();
			$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-teacher.csv'; // File location.
			$mime = 'text/plain';
			header( 'Content-Type:application/force-download' );
			header( 'Pragma: public' );       // Required.
			header( 'Expires: 0' );           // No cache.
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Last-Modified: ' . date( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
			header( 'Cache-Control: private', false );
			header( 'Content-Type: ' . $mime );
			header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );
			readfile( $file );
			die();
		} else {
			echo "<div style=' background: none repeat scroll 0 0 red; border: 1px solid; color: white; float: left; font-size: 17px; margin-top: 10px; padding: 10px; width: 98%;'>Records not found.</div>";
		}
	}
}
// ------------------ Import teacher. --------------------//
if ( isset( $_REQUEST['upload_teacher_csv_file'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'upload_csv_nonce' ) ) {
		if ( isset( $_FILES['csv_file'] ) ) {
			$errors     = array();
			$file_name  = sanitize_file_name( $_FILES['csv_file']['name'] );
			$file_size  = $_FILES['csv_file']['size'];
			$file_tmp   = $_FILES['csv_file']['tmp_name'];
			$file_type  = $_FILES['csv_file']['type'];
			$value      = explode( '.', $_FILES['csv_file']['name'] );
			$file_ext   = strtolower( array_pop( $value ) );
			$extensions = array( 'csv' );
			$upload_dir = wp_upload_dir();
			if ( in_array( $file_ext, $extensions ) === false ) {
				$module      = 'teacher';
				$status      = 'file type error';
				$log_message = 'Teacher import fail due to invalid file type';
				mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
				$err      = esc_attr__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				$errors[] = $err;
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=8' ) );
				die();
			}
			if ( $file_size > 2097152 ) {
				$errors[] = 'File size limit 2 MB';
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=9' ) );
				die();
			}
			if ( empty( $errors ) === true ) {
				$rows   = array_map( 'str_getcsv', file( $file_tmp ) );
				$header = array_map( 'trim', array_map( 'strtolower', array_shift( $rows ) ) );
				$csv    = array();
				foreach ( $rows as $row ) {
					$csv      = array_combine( $header, $row );
					$username = sanitize_user( $csv['username'], true );
					$email    = sanitize_email( $csv['email'] );
					$user_id  = 0;
					if ( isset( $csv['password'] ) ) {
						$password = sanitize_email( $csv['password'] );
					} else {
						$password = wp_generate_password();
					}
					$problematic_row = false;
					if ( username_exists( $username ) ) { // If user exists, we take his ID by login.
						$user_object = get_user_by( 'login', $username );
						$user_id     = $user_object->ID;
						$mjschool_role_name   = mjschool_get_user_role( $user_id );
						if ( $mjschool_role_name != 'administrator' ) {
							if ( ! empty( $password ) ) {
								wp_set_password( $password, $user_id );
							}
						}
					} elseif ( email_exists( $email ) ) { // If the email is registered, we take the user from this.
						$user_object     = get_user_by( 'email', $email );
						$user_id         = $user_object->ID;
						$problematic_row = true;
						$mjschool_role_name       = mjschool_get_user_role( $user_id );
						if ( $mjschool_role_name != 'administrator' ) {
							if ( ! empty( $password ) ) {
								wp_set_password( $password, $user_id );
							}
						}
					} else {
						$user_id = wp_create_user( $username, $password, $email );
					}
					if ( is_wp_error( $user_id ) ) { // In case the user is generating errors after this checks.
						$module      = 'teacher';
						$emails      = $email;
						$status      = 'Fail';
						$log_message = "Teacher import fail for: $emails";
						mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						// Set a JS trigger flag
						echo '<input type="hidden" id="mjschool_csv_error" value="1">';
						
						continue;
					}
					if ( $mjschool_role_name != 'administrator' ) {
						wp_update_user(
							array(
								'ID'   => $user_id,
								'role' => 'teacher',
							)
						);
						$mjschool_user = new WP_User( $user_id );
						$mjschool_user->add_role( 'author' );
					}
					$user_id1    = wp_update_user(
						array(
							'ID'           => $user_id,
							'display_name' => $csv['first name'] . ' ' . $csv['middle name'] . ' ' . $csv['last name'],
						)
					);
					$class_array = explode( ',', $csv['class name'] );
					$teacher_obj = new Mjschool_Teacher();
					$result1     = $teacher_obj->mjschool_add_multi_class_import( $class_array, $username );
					if ( isset( $csv['first name'] ) ) {
						update_user_meta( $user_id, 'first_name', sanitize_text_field( $csv['first name'] ) );
					}
					if ( isset( $csv['last name'] ) ) {
						update_user_meta( $user_id, 'last_name', sanitize_text_field( $csv['last name'] ) );
					}
					if ( isset( $csv['middle name'] ) ) {
						update_user_meta( $user_id, 'middle_name', sanitize_text_field( $csv['middle name'] ) );
					}
					if ( isset( $csv['gender'] ) ) {
						$gender = strtolower( trim( $csv['gender'] ) );
						// Optionally validate allowed values.
						if ( in_array( $gender, array( 'male', 'female' ) ) ) {
							update_user_meta( $user_id, 'gender', $gender );
						} else {
							update_user_meta( $user_id, 'gender', '' ); // Or skip, or set default.
						}
					}
					if ( isset( $csv['birth date'] ) ) {
						update_user_meta( $user_id, 'birth_date', sanitize_text_field( $csv['birth date'] ) );
					}
					if ( isset( $csv['address'] ) ) {
						update_user_meta( $user_id, 'address', sanitize_text_field( $csv['address'] ) );
					}
					if ( isset( $csv['city name'] ) ) {
						update_user_meta( $user_id, 'city', sanitize_text_field( $csv['city name'] ) );
					}
					if ( isset( $csv['state name'] ) ) {
						update_user_meta( $user_id, 'state', sanitize_text_field( $csv['state name'] ) );
					}
					if ( isset( $csv['zip code'] ) ) {
						update_user_meta( $user_id, 'zip_code', sanitize_text_field( $csv['zip code'] ) );
					}
					if ( isset( $csv['mobile number'] ) ) {
						update_user_meta( $user_id, 'mobile_number', sanitize_text_field( $csv['mobile number'] ) );
					}
					if ( isset( $csv['alternate mobile number'] ) ) {
						update_user_meta( $user_id, 'alternet_mobile_number', sanitize_text_field( $csv['alternate mobile number'] ) );
					}
					if ( isset( $csv['phone number'] ) ) {
						update_user_meta( $user_id, 'phone', sanitize_text_field( $csv['phone number'] ) );
					}
					$success = 1;
					if ( isset( $success ) ) {
						if ( isset($_REQUEST['mjschool_import_teacher_mail']) && sanitize_text_field(wp_unslash($_REQUEST['mjschool_import_teacher_mail'])) === '1' ) {
							if ( $user_id ) {
								$userdata                  = get_userdata( $user_id );
								$string                    = array();
								$string['{{user_name}}']   = $userdata->display_name;
								$string['{{school_name}}'] = get_option( 'mjschool_name' );
								$string['{{role}}']        = 'teacher';
								$string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
								$string['{{username}}']    = $userdata->user_email;
								$string['{{Password}}']    = $password;
								$MsgContent                = get_option( 'mjschool_add_user_mail_content' );
								$MsgSubject                = get_option( 'mjschool_add_user_mail_subject' );
								$message                   = mjschool_string_replacement( $string, $MsgContent );
								$MsgSubject                = mjschool_string_replacement( $string, $MsgSubject );
								$email                     = $userdata->user_email;
								mjschool_send_mail( $email, $MsgSubject, $message );
							}
						}
						$module      = 'teacher';
						$emails      = isset( $email ) ? $email : ''; // Or collect all emails.
						$status      = 'Success';
						$log_message = "Import CSV Successful: {$emails}";
						mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=6' ) );
						exit;
					}
				}
			} else {
				foreach ( $errors as $error ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert updated_top mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php echo esc_html( $error ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
			}
			if ( isset( $success ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&message=6' ) );
				exit;
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
?>
<!-- POP-UP code start. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- POP-UP code end. -->
<?php
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'teacherlist';
?>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Teacher Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Teacher Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Roll No Already Exist.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Teacher Username Or Emailid Already Exist.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Teacher Deleted Successfully.', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'Teacher CSV Uploaded Successfully.', 'mjschool' );
				break;
			case '7':
				$message_string = esc_html__( 'Student Activated Successfully.', 'mjschool' );
				break;
			case '8':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '9':
				$message_string = esc_html__( 'File size limit 2 MB', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_7px_10px">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="row"><!-- Row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-main-list-page"><!-- Mjschool-main-list-page. -->
					<?php
					if ( $active_tab === 'teacherlist' ) {
						$teacherdata = mjschool_get_users_data( 'teacher' );
						if ( ! empty( $teacherdata ) ) {
							?>
							<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
								<div class="table-responsive">
									<form name="mjschool-common-form" action="" method="post">
										<table id="teacher_list" class="display mjschool-admin-taecher-datatable" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Teacher Name & Email', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
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
												if ( ! empty( $teacherdata ) ) {
													foreach ( mjschool_get_users_data( 'teacher' ) as $retrieved_data ) {
														$teacher_id    = mjschool_encrypt_id( $retrieved_data->ID );
														$teacher_group = array();
														$teacher_ids   = mjschool_teacher_by_subject( $retrieved_data );
														foreach ( $teacher_ids as $teacher_id ) {
															$teacher_group[] = mjschool_get_teacher( $teacher_id );
														}
														$teachers = implode( ',', $teacher_group );
														$obj_subject = new Mjschool_Subject();
														?>
														<tr>
															<td class="mjschool-checkbox-width-10px">
																<input type="checkbox" class="mjschool-sub-chk selected_teacher" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
															</td>
															<td class="mjschool-user-image mjschool-width-50px-td">
																<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teacher&teacher_id=' . rawurlencode( $teacher_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>">
																	<?php $uid = $retrieved_data->ID;
																	$umetadata = mjschool_get_user_image($uid);
																	if (empty($umetadata ) ) {
																		echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																	} else {
																		echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
																	}
																	?>
																</a>
															</td>
															<td class="name">
																<a class="mjschool-color-black" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teacher&teacher_id=' . rawurlencode( $teacher_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' )  ) ) ); ?>">
																	<?php echo esc_html( $retrieved_data->display_name ); ?>
																</a>
																<br>
																<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
															</td>
															<td >
																<?php
																$classes   = '';
																$classes   = $teacher_obj->mjschool_get_class_by_teacher( $retrieved_data->ID );
																$classname = '';
																foreach ( $classes as $class ) {
																	$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
																}
																$classname_rtrim = rtrim( $classname, ', ' );
																$classname_ltrim = ltrim( $classname_rtrim, ', ' );
																if ( ! empty( $classname_ltrim ) ) {
																	echo esc_html( $classname_ltrim );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php
																$subjectname = $obj_subject->mjschool_get_subject_name_by_teacher( $uid );
																if ( ! empty( $subjectname ) ) {
																	echo esc_html( rtrim( $subjectname, ', ' ) );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php
																$uid = $retrieved_data->ID;
																?>
																+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
															</td>
															<?php
															// Custom field values.
															if ( ! empty( $user_custom_field ) ) {
																foreach ( $user_custom_field as $custom_field ) {
																	if ( $custom_field->show_in_table === '1' ) {
																		$module             = 'teacher';
																		$custom_field_id    = $custom_field->id;
																		$module_record_id   = $retrieved_data->ID;
																		$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
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
																					<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button">
																						<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teacher&teacher_id='.rawurlencode( $teacher_id ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																				</li>
																				<?php
																				if ( $user_access_edit === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . rawurlencode($teacher_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				if ( $user_access_delete === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist&action=delete&teacher_id=' . rawurlencode( $teacher_id ) . '&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
											<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload mjchool_margin_bottom_5px" >
												<input type="checkbox" id="select_all" name="id[]" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												?>
												<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php
											} ?>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="button" class="view_import_teacher_csv_popup mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="teacher_csv_selected" class="teacher_csv_export_alert mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="teacher"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
										</div>
									</form>
								</div>
							</div><!-- Mjschool-panel-body. -->
							<?php
						} else {
							if ($user_access_add === '1' ) {
								?>
								<div class="mjschool-no-data-list-div row">
									<div class="offset-md-2 col-md-4">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher' ) ); ?>">
											<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
										</div>
									</div>
									<div class="col-md-4">
										<a data-toggle="tooltip" name="import_csv" type="button" class="view_import_teacher_csv_popup">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-Import-list.png"); ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to import CSV.', 'mjschool' ); ?> </label>
										</div>
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
					}
					if ( $active_tab === 'addteacher' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/teacher/add-new-teacher.php';
					}
					if ( $active_tab === 'view_teacher' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/teacher/view-teacher.php';
					}
					if ( $active_tab === 'uploadteacher' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/teacher/upload_teacher.php';
					}
					?>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->
