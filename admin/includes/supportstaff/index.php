<?php
/**
 * MjSchool Support Staff Management Admin File.
 *
 * Handles CRUD operations (Create, Read, Update, Delete) for support staff in the Mjschool plugin.
 * Includes user access checks, validation, csv upload handling, and database operations.
 * 
 * Key Features:
 * - Permission validation (view, add, edit, delete)
 * - Support staff form submission
 * - CSV import/export
 * - CRUD operations for support staff
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/supportstaff
 * @since      1.0
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
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'supportstaff' );
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
			if ( 'supportstaff' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'supportstaff' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'supportstaff' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'supportstaff';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
$mjschool_role = 'supportstaff';
if ( isset( $_POST['save_supportstaff'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_supportstaff_admin_nonce' ) ) {
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
		// Document upload file code start.
		$document_content = array();
		if ( ! empty( $_FILES['document_file']['name'] ) ) {
			$count_array = count( $_FILES['document_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				if ( ( $_FILES['document_file']['size'][ $a ] > 0 ) && ( ! empty( $_POST['document_title'][ $a ] ) ) ) {
					$document_title = sanitize_text_field(wp_unslash($_POST['document_title'][ $a ]));
					$document_file  = mjschool_upload_document_user_multiple( $_FILES['document_file'], $a, $_POST['document_title'][ $a ] );
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
			'birth_date'             => sanitize_text_field(wp_unslash($_POST['birth_date'])),
			'address'                => sanitize_textarea_field( wp_unslash($_POST['address']) ),
			'city'                   => sanitize_text_field( wp_unslash($_POST['city_name']) ),
			'state'                  => sanitize_text_field( wp_unslash($_POST['state_name']) ),
			'zip_code'               => sanitize_text_field( wp_unslash($_POST['zip_code']) ),
			'phone'                  => sanitize_text_field( wp_unslash($_POST['phone']) ),
			'mobile_number'          => sanitize_text_field( wp_unslash($_POST['mobile_number']) ),
			'user_document'          => $final_document,
			'alternet_mobile_number' => sanitize_text_field( wp_unslash($_POST['alternet_mobile_number']) ),
			'working_hour'           => sanitize_text_field( wp_unslash($_POST['working_hour']) ),
			'possition'              => sanitize_textarea_field( wp_unslash($_POST['possition']) ),
			'mjschool_user_avatar'       => $photo,
		);
		if ( $action === 'edit' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'edit_action' ) ) {
				$userdata['ID'] = mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) );
				$result         = mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
				// Update custom field data.
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'supportstaff';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=2' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} elseif ( ! email_exists( $_POST['email'] ) && ! username_exists( mjschool_strip_tags_and_stripslashes( $_POST['username'] ) ) ) {
			$result = mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role );
			// Add custom field data.
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'supportstaff';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=1' ) );
				die();
			}
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=3' ) );
			die();
		}
	}
}
if ( $action === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_action' ) ) {
		$result = mjschool_delete_usedata( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=4' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_usedata( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=4' ) );
		die();
	}
}
// -------------- Export staff data. ---------------//
if ( isset( $_POST['staff_csv_selected'] ) ) {
	if ( isset( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $s_id ) {
			$staff_list[] = get_userdata( $s_id );
		}
		if ( ! empty( $staff_list ) ) {
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
			$filename = 'export/mjschool-export-staff.csv';
			$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
			fputcsv( $fh, $header );
			foreach ( $staff_list as $retrive_data ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->ID );
				$row[]     = $user_info->user_login;
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
				$row[]     = get_user_meta( $retrive_data->ID, 'mobile_number', true );
				$row[]     = get_user_meta( $retrive_data->ID, 'alternet_mobile_number', true );
				fputcsv( $fh, $row );
			}
			fclose( $fh );
			// Download csv file.
			ob_clean();
			$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-staff.csv'; // File location.
			$mime = 'text/plain';
			header( 'Content-Type:application/force-download' );
			header( 'Pragma: public' );       // Required.
			header( 'Expires: 0' );           // No cache.
			header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
			header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
			header( 'Cache-Control: private', false );
			header( 'Content-Type: ' . $mime );
			header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Connection: close' );
			readfile( $file );
			die();
		} else {
			echo "<div class='parent-error'>Records not found.</div>";
		}
	}
}
// ------------------ Import staff member. --------------------//
if ( isset( $_REQUEST['upload_staff_csv_file'] ) ) {
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
				$err      = esc_attr__( 'this file not allowed, please choose a CSV file.', 'mjschool' );
				$errors[] = $err;
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=6' ) );
				die();
			}
			if ( $file_size > 2097152 ) {
				$module      = 'supportstaff';
				$status      = 'file type error';
				$log_message = 'supportstaff import fail due to invalid file type';
				mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
				$errors[] = 'File size limit 2 MB';
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=7' ) );
				die();
			}
			if ( empty( $errors ) === true ) {
				$rows         = array_map( 'str_getcsv', file( $file_tmp ) );
				$header       = array_map( 'trim', array_map( 'strtolower', array_shift( $rows ) ) );
				$csv          = array();
				$user_created = false;
				foreach ( $rows as $row ) {
					$csv      = array_combine( $header, $row );
					$username = sanitize_user( $csv['username'], true );
					$email    = sanitize_email( $csv['email'] );
					$user_id  = 0;
					if ( isset( $csv['password'] ) ) {
						$password = sanitize_text_field( $csv['password'] );
					} else {
						$password = rand();
					}
					$problematic_row = false;
					if ( username_exists( $username ) ) {
						$user_object = get_user_by( 'login', $username );
						$user_id     = $user_object->ID;
						$mjschool_role_name   = mjschool_get_user_role( $user_id );
						if ( $mjschool_role_name != 'administrator' ) {
							if ( ! empty( $password ) ) {
								wp_set_password( $password, $user_id );
							}
						}
					} elseif ( email_exists( $email ) ) {
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
						if ( empty( $password ) ) {
							$password = wp_generate_password();
						}
						$user_id = wp_create_user( $username, $password, $email );
						if ( $user_id ) {
							$user_created = true;
						}
					}
					if ( is_wp_error( $user_id ) ) {
						$module      = 'supportstaff';
						$emails      = $email;
						$status      = 'Fail';
						$log_message = "Support staff import fail for: $emails";
						mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						echo '<script type="text/javascript"> alert(language_translate2.csv_alert); </script>';
						continue;
					}
					if ( $mjschool_role_name != 'administrator' ) {
						wp_update_user(
							array(
								'ID'   => $user_id,
								'role' => 'supportstaff',
							)
						);
						$mjschool_user = new WP_User( $user_id );
						$mjschool_user->add_role( 'author' );
					}
					$user_id1 = wp_update_user(
						array(
							'ID'           => $user_id,
							'display_name' => sanitize_text_field( $csv['first name'] ) . ' ' . sanitize_text_field( $csv['middle name'] ) . ' ' . sanitize_text_field( $csv['last name'] ),
						)
					);
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
					if ( $user_created ) {
						if ( isset($_REQUEST['mjschool_import_staff_mail']) && sanitize_text_field(wp_unslash($_REQUEST['mjschool_import_staff_mail'])) === '1' ) {
							if ( $user_id ) {
								$userdata                  = get_userdata( $user_id );
								$string                    = array();
								$string['{{user_name}}']   = $userdata->display_name;
								$string['{{school_name}}'] = get_option( 'mjschool_name' );
								$string['{{role}}']        = 'supportstaff';
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
						$module      = 'supportstaff';
						$emails      = isset( $email ) ? $email : ''; // Or collect all emails.
						$status      = 'Success';
						$log_message = "Import CSV Successful: {$emails}";
						mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
					}
				}
			} else {
				foreach ( $errors as &$error ) {
					echo esc_html( $error );
				}
			}
			if ( isset( $success ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list&message=5' ) );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'supportstaff_list';
?>
<!-- POP-UP code start-->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- POP-UP code end. -->
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Support Staff Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Support Staff Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Username Or Email-id Already Exist.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Support Staff Deleted Successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Support Staff CSV Uploaded Successfully .', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '7':
				$message_string = esc_html__( 'File size limit 2 MB.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_7px_10px" >
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
					if ( $active_tab === 'supportstaff_list' ) {
						$teacherdata = mjschool_get_users_data( 'supportstaff' );
						if ( ! empty( $teacherdata ) ) {
							?>
							<form name="wcwm_report" action="" method="post">
								<div class="mjschool-panel-body">
									<div class="table-responsive">
										<form name="mjschool-common-form" action="" method="post">
											<table id="supportstaff_list" class="display mjschool-admin-supportstaff-datatable" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
														<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Support Staff Name & Email', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
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
														foreach ( mjschool_get_users_data( 'supportstaff' ) as $retrieved_data ) {
															$uid      = $retrieved_data->ID;
															$staff_id = mjschool_encrypt_id( $retrieved_data->ID );
															?>
															<tr>
																<td class="mjschool-checkbox-width-10px">
																	<input type="checkbox" class="mjschool-sub-chk selected_staff select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
																</td>
																<td class="mjschool-user-image mjschool-width-50px-td">
																	<a class="mjschool-color-black" href=<?php echo esc_url("?page=mjschool_supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=".esc_attr($staff_id)."&_wpnonce=".esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>>
																		<?php
																		$uid = $retrieved_data->ID;
																		$umetadata = mjschool_get_user_image($uid);
																		if (empty($umetadata ) ) {
																			echo '<img src=' . esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																		} else {
																			echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
																		}
																		?>
																	</a>
																</td>
																<td class="name">
																	<a class="mjschool-color-black" href=<?php echo esc_url("?page=mjschool_supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=".esc_attr( $staff_id )."&_wpnonce=".esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>>
																		<?php echo esc_html( $retrieved_data->display_name ); ?>
																	</a>
																	<br>
																	<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
																</td>
																<td >
																	+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php echo esc_html( ucfirst( get_user_meta( $uid, 'gender', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
																</td>
																<td >
																	<?php
																	$birthdate = get_user_meta( $uid, 'birth_date', true );
																	echo esc_html( mjschool_get_date_in_input_box( $birthdate ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
																</td>
																<?php
																// Custom field values.
																if ( ! empty( $user_custom_field ) ) {
																	foreach ( $user_custom_field as $custom_field ) {
																		if ( $custom_field->show_in_table === '1' ) {
																			$module             = 'supportstaff';
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
																						<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . sanitize_file_name( $custom_field_value ) ); ?>" download="CustomFieldfile">
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
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<li class="mjschool-float-left-width-100px">
																						<a href=<?php echo esc_url("?page=mjschool_supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=" . esc_attr( $staff_id ) . "&_wpnonce=". esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?> class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																					</li>
																					<?php
																					if ( $user_access_edit === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																							<a href=<?php echo esc_url("?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=" . esc_attr( $staff_id ) . "&_wpnonce=". esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?> class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																						</li>
																						<?php
																					}
																					?>
																					<?php
																					if ( $user_access_delete === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href=<?php echo esc_url("?page=mjschool_supportstaff&tab=supportstaff_list&action=delete&supportstaff_id=" . esc_attr( $staff_id ) . "&_wpnonce=". esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?> class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"> </i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
													<input type="checkbox" id="select_all" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
													<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
												</button>
												<?php  
												if ($user_access_delete === '1' ) {
													?>
													<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
													<?php
												} ?>
												<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="button" class="view_import_support_staff_csv_popup mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
												<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="staff_csv_selected" class="staff_csv_selected mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
												<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="supportstaff"> <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"> </button>
											</div>
										</form>
									</div>
								</div>
							</form>
							<?php
						} else {
							if ($user_access_add === '1' ) {
								?>
								<div class="mjschool-no-data-list-div row">
									<div class="offset-md-2 col-md-4">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff' ) ); ?>">
											<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
										</div>
									</div>
									<div class="col-md-4">
										<a data-toggle="tooltip" name="import_csv" type="button" class="view_import_support_staff_csv_popup">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-Import-list.png"); ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to import CSV.', 'mjschool' ); ?></label>
										</div>
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
					if ( $active_tab === 'addsupportstaff' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/supportstaff/add-staff.php';
					}
					if ( $active_tab === 'view_supportstaff' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/supportstaff/view-supportstaff.php';
					}
					?>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->