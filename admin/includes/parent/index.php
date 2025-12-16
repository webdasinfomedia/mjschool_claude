<?php
/**
 * Parent Management Page.
 *
 * This file manages the "Parent" section of the MJSchool plugin within the WordPress admin dashboard.
 * It provides administrators and authorized users the ability to add, edit, view, delete, import, and export
 * parent user records. The page dynamically integrates custom fields, enforces role-based access permissions,
 * and offers an intuitive DataTable interface for managing parent-related data.
 *
 * Key Features:
 * - Displays a searchable and sortable list of parents with pagination and custom field columns.
 * - Supports secure add/edit operations using nonces and WordPress sanitization/escaping functions.
 * - Enables CSV import/export for bulk parent data management.
 * - Implements role-based CRUD (Create, Read, Update, Delete) permissions and access control checks.
 * - Validates form inputs and supports document and photo uploads for parent profiles.
 * - Integrates AJAX-driven interactions for responsive UI components.
 * - Includes dynamic DataTables initialization, filtering, and multi-language support.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/parent
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'parent' );
	$user_access_add    = isset( $user_access['add'] ) ? $user_access['add'] : '0';
	$user_access_edit   = isset( $user_access['edit'] ) ? $user_access['edit'] : '0';
	$user_access_delete = isset( $user_access['delete'] ) ? $user_access['delete'] : '0';
	$user_access_view   = isset( $user_access['view'] ) ? $user_access['view'] : '0';
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'parent' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'parent' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( isset( $user_access['page_link'] ) && $user_access['page_link'] === 'parent' && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'parent';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$mjschool_role = 'parent';
if ( isset( $_POST['save_parent'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
	if ( wp_verify_nonce( $nonce, 'save_parent_admin_nonce' ) ) {
		$firstname  = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
		$middlename = sanitize_text_field( wp_unslash( $_POST['middle_name'] ) );
		$lastname   = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
		$userdata   = array(
			'user_login'    => sanitize_email( wp_unslash( $_POST['email'] ) ),
			'user_nicename' => null,
			'user_email'    => sanitize_email( wp_unslash( $_POST['email'] ) ),
			'user_url'      => null,
			'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
		);
		if ( isset( $_POST['password'] ) && $_POST['password'] !== '' ) {
			$userdata['user_pass'] = mjschool_password_validation( wp_unslash( $_POST['password'] ) );
		}
		if ( isset( $_POST['mjschool_user_avatar'] ) && $_POST['mjschool_user_avatar'] !== '' ) {
			$photo = sanitize_text_field( wp_unslash( $_POST['mjschool_user_avatar'] ) );
		} else {
			$photo = '';
		}
		// DOCUMENT UPLOAD
		$document_content = array();
		if ( isset( $_FILES['document_file']['name'] ) && ! empty( $_FILES['document_file']['name'] ) ) {
			$count_array = count( $_FILES['document_file']['name'] );
			for ( $a = 0; $a < $count_array; $a++ ) {
				if ( ( intval( $_FILES['document_file']['size'][ $a ] ) > 0 ) && ! empty( $_POST['document_title'][ $a ] ) ) {
					$document_title = sanitize_text_field( wp_unslash( $_POST['document_title'][ $a ] ) );
					$document_file = mjschool_upload_document_user_multiple(
						$_FILES['document_file'], 
						$a, 
						sanitize_text_field( wp_unslash( $_POST['document_title'][ $a ] ) )
					);
				} elseif ( ! empty( $_POST['user_hidden_docs'][ $a ] ) && ! empty( $_POST['document_title'][ $a ] ) ) {

					$document_title = sanitize_text_field( wp_unslash( $_POST['document_title'][ $a ] ) );
					$document_file  = sanitize_text_field( wp_unslash( $_POST['user_hidden_docs'][ $a ] ) );
				}
				if ( ! empty( $document_file ) && ! empty( $document_title ) ) {
					$document_content[] = array(
						'document_title' => $document_title,
						'document_file'  => $document_file,
					);
				}
			}
		}
		$final_document = ! empty( $document_content ) ? wp_json_encode( $document_content ) : '';
		$usermetadata = array(
			'middle_name'      => sanitize_text_field( wp_unslash( $_POST['middle_name'] ) ),
			'gender'           => sanitize_text_field( wp_unslash( $_POST['gender'] ) ),
			'birth_date'       => sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ),
			'address'          => sanitize_textarea_field( wp_unslash( $_POST['address'] ) ),
			'city'             => sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
			'state'            => sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
			'zip_code'         => sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ),
			'phone'            => sanitize_text_field( wp_unslash( $_POST['phone'] ) ),
			'mobile_number'    => sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ),
			'user_document'    => $final_document,
			'relation'         => sanitize_text_field( wp_unslash( $_POST['relation'] ) ),
			'mjschool_user_avatar' => $photo,
			'created_by'       => get_current_user_id(),
		);
		// UPDATE PARENT
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_action' ) ) {
				$userdata['ID'] = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['parent_id'] ) ) ) );
				$result = mjschool_update_user(
					$userdata,
					$usermetadata,
					$firstname,
					$middlename,
					$lastname,
					$mjschool_role
				);
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'parent';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $result );
				if ( $result ) {
					wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=1' );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		}
		// INSERT PARENT
		elseif ( ! email_exists( sanitize_email( wp_unslash( $_POST['email'] ) ) ) ) {
			$result = mjschool_add_new_user(
				$userdata,
				$usermetadata,
				$firstname,
				$middlename,
				$lastname,
				$mjschool_role
			);
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'parent';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=2' );
				die();
			}
		} else {
			wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=3' );
			die();
		}
	}
}
$addparent = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'addparent' ) {
	if ( isset( $_REQUEST['student_id'] ) ) {
		$student   = get_userdata( intval( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) );
		$addparent = 1;
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'parentlist';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	$parent_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['parent_id'] ) ) ) );
	$childs = get_user_meta( $parent_id, 'child', true );
	if ( ! empty( $childs ) ) {
		foreach ( $childs as $childvalue ) {
			$parents = get_user_meta( $childvalue, 'parent_id', true );
			if ( ! empty( $parents ) ) {
				if ( ( $key = array_search( $parent_id, $parents ) ) !== false ) {
					unset( $parents[ $key ] );
					update_user_meta( $childvalue, 'parent_id', $parents );
				}
			}
		}
	}
	$result = mjschool_delete_usedata( $parent_id );
	if ( $result ) {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=4' );
		die();
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$id = intval( sanitize_text_field( wp_unslash( $id ) ) );
			$childs = get_user_meta( $id, 'child', true );
			if ( ! empty( $childs ) ) {
				foreach ( $childs as $childvalue ) {
					$parents = get_user_meta( $childvalue, 'parent_id', true );
					if ( ! empty( $parents ) ) {
						if ( ( $key = array_search( $id, $parents ) ) !== false ) {
							unset( $parents[ $key ] );
							update_user_meta( $childvalue, 'parent_id', $parents );
						}
					}
				}
			}
			$result = mjschool_delete_usedata( $id );
		}
	}
	if ( isset( $result ) && $result ) {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=4' );
		die();
	}
}
// -------------- EXPORT Parent DATA. ---------------//
if ( isset( $_POST['parent_export_csv_selected'] ) ) {
	if ( isset( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $s_id ) {
			$staff_list[] = get_userdata( $s_id );
		}
		if ( ! empty( $staff_list ) ) {
			$header   = array();
			$header[] = 'Username';
			$header[] = 'Email';
			$header[] = 'Password';
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
			$header[] = 'child';
			$header[] = 'Relation';
			$filename = 'export/mjschool-export-parent.csv';
			$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
			fputcsv( $fh, $header );
			foreach ( $staff_list as $retrive_data ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->ID );
				$child_id  = get_user_meta( $retrive_data->ID, 'child', true );
				$childid   = array();
				foreach ( $child_id as $childsdata ) {
					$child     = get_userdata( $childsdata );
					$childid[] = $child->data->user_email;
				}
				$row[]           = $user_info->user_login;
				$row[]           = $user_info->user_email;
				$row[]           = $user_info->user_pass;
				$row[]           = get_user_meta( $retrive_data->ID, 'first_name', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'middle_name', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'last_name', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'gender', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'birth_date', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'address', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'city', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'state', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'zip_code', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'mobile_number', true );
				$row[]           = get_user_meta( $retrive_data->ID, 'alternet_mobile_number', true );
				$child_record_id = implode( ',', $childid );
				$row[]           = $child_record_id;
				$row[]           = get_user_meta( $retrive_data->ID, 'relation', true );
				fputcsv( $fh, $row );
			}
			fclose( $fh );
			// download csv file.
			ob_clean();
			$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-parent.csv'; // File location.
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
			echo "<div style=' background: none repeat scroll 0 0 red;border: 1px solid;color: white;float: left;font-size: 17px;margin-top: 10px;padding: 10px;width: 98%;'>Records not found.</div>";
		}
	}
}
// ------------------ Import parent member. --------------------------//
if ( isset( $_REQUEST['upload_parent_csv_file'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
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
				$module      = 'parent';
				$status      = 'file type error';
				$log_message = 'Parent import fail due to invalid file type';
				mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
				$err      = esc_attr__( 'this file not allowed, please choose a CSV file.', 'mjschool' );
				$errors[] = $err;
				wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=uploadparent&message=6' );
				die();
			}
			if ( $file_size > 2097152 ) {
				$errors[] = 'File size limit 2 MB';
				wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=uploadparent&message=7' );
				die();
			}
			if ( empty( $errors ) === true ) {
				$rows         = array_map( 'str_getcsv', file( $file_tmp ) );
				$header       = array_map( 'trim', array_map( 'strtolower', array_shift( $rows ) ) );
				$csv          = array();
				$user_created = false;
				foreach ( $rows as $row ) {
					$csv             = array_combine( $header, $row );
					$username        = sanitize_user( $csv['username'], true );
					$email           = sanitize_email( $csv['email'] );
					$user_id         = 0;
					$problematic_row = false;
					if ( username_exists( $username ) ) {
						if ( isset( $csv['password'] ) ) {
							$password = $csv['password'];
						}
						$user_object = get_user_by( 'login', $username );
						$user_id     = $user_object->ID;
						$mjschool_role_name   = mjschool_get_user_role( $user_id );
						if ( $mjschool_role_name != 'administrator' ) {
							if ( ! empty( $password ) ) {
								wp_set_password( $password, $user_id );
							}
						}
					} elseif ( email_exists( $email ) ) {
						if ( isset( $csv['password'] ) ) {
							$password = $csv['password'];
						}
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
						if ( ! empty( $csv['password'] ) ) {
							$password = sanitize_text_field( $csv['password'] );
						} else {
							$password = wp_generate_password();
						}
						if ( ! empty( $password ) ) {
							$user_id = wp_create_user( $username, $password, $email );
							if ( $user_id ) {
								$user_created = true;
							}
						}
					}
					if ( is_wp_error( $user_id ) ) {
						$module      = 'parent';
						$emails      = $email;
						$status      = 'Fail';
						$log_message = 'parent import fail';
						mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
						// In case the user is generating errors after this checks.
						echo '<script type="text/javascript">alert( "Problems with user: ' . esc_html( $username ) . ', we are going to skip");</script>';
						continue;
					}
					if ( $mjschool_role_name != 'administrator' ) {
						wp_update_user(
							array(
								'ID'   => $user_id,
								'role' => 'parent',
							)
						);
						$mjschool_user = new WP_User( $user_id );
						$mjschool_user->add_role( 'subscriber' );
					}
					$user_id1 = wp_update_user(
						array(
							'ID'           => $user_id,
							'display_name' => $csv['first name'] . ' ' . $csv['middle name'] . ' ' . $csv['last name'],
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
						$alt_number1 = mjschool_convert_scientific_to_number( $csv['mobile number'] );
					}
					update_user_meta( $user_id, 'mobile_number', sanitize_text_field( $alt_number1 ) );
					if ( isset( $csv['user_profile'] ) ) {
						$upload_dir = wp_upload_dir();
						$photo      = $upload_dir['baseurl'] . '/' . $csv['user_profile'];
						update_user_meta( $user_id, 'mjschool_user_avatar', $photo );
					}
					if ( isset( $csv['alternate mobile number'] ) ) {
						$alt_number = mjschool_convert_scientific_to_number( $csv['alternate mobile number'] );
					}
					update_user_meta( $user_id, 'phone', sanitize_text_field( $alt_number ) );
					if ( isset( $csv['phone number'] ) ) {
						update_user_meta( $user_id, 'phone', sanitize_text_field( $csv['phone number'] ) );
					}
					if ( isset( $csv['relation'] ) ) {
						update_user_meta( $user_id, 'relation', sanitize_text_field( $csv['relation'] ) );
					}
					if ( isset( $csv['child'] ) ) {
						$child_username = explode( ',', $csv['child'] );
						foreach ( $child_username as $child_id ) {
							$mjschool_user         = get_user_by( 'email', $child_id );
							$student_id   = $mjschool_user->data->ID;
							$student_data = get_user_meta( $student_id, 'parent_id', true );
							$parent_data  = get_user_meta( $user_id, 'child', true );
							if ( $student_data ) {
								if ( ! in_array( $user_id, $student_data ) ) {
									$update    = array_push( $student_data, $user_id );
									$returnans = update_user_meta( $student_id, 'parent_id', $student_data );
									if ( $returnans ) {
										$returnval = $returnans;
									}
								}
							} else {
								$parent_id = array( $user_id );
								$returnans = add_user_meta( $student_id, 'parent_id', $parent_id );
								if ( $returnans ) {
									$returnval = $returnans;
								}
							}
							if ( $parent_data ) {
								if ( ! in_array( $student_id, $parent_data ) ) {
									$update    = array_push( $parent_data, $student_id );
									$returnans = update_user_meta( $user_id, 'child', $parent_data );
									if ( $returnans ) {
										$returnval = $returnans;
									}
								}
							} elseif ( ! empty( $student_id ) ) {
									$child_id  = array( $student_id );
									$returnans = add_user_meta( $user_id, 'child', $child_id );
								if ( $returnans ) {
									$returnval = $returnans;
								}
							}
						}
					}
					$success = 1;
					if ( $user_created ) {
						if ( isset( $_REQUEST['mjschool_import_parent_mail'] ) && sanitize_text_field( wp_unslash( $_REQUEST['mjschool_import_parent_mail'] ) ) === '1' ) {
							if ( $user_id ) {
								$userdata                  = get_userdata( $user_id );
								$string                    = array();
								$string['{{user_name}}']   = $userdata->display_name;
								$string['{{school_name}}'] = get_option( 'mjschool_name' );
								$string['{{role}}']        = 'parent';
								$string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
								$string['{{username}}']    = $userdata->user_email;
								$string['{{Password}}']    = $csv['password'];
								$MsgContent                = get_option( 'mjschool_add_user_mail_content' );
								$MsgSubject                = get_option( 'mjschool_add_user_mail_subject' );
								$message                   = mjschool_string_replacement( $string, $MsgContent );
								$MsgSubject                = mjschool_string_replacement( $string, $MsgSubject );
								$email                     = $userdata->user_email;
								mjschool_send_mail( $email, $MsgSubject, $message );
							}
						}
						$module      = 'parent';
						$status      = 'Success';
						$emails      = isset( $email ) ? $email : ''; // Or collect all emails.
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
				wp_redirect( admin_url() . 'admin.php?page=mjschool_parent&tab=parentlist&message=5' );
				die();
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
?>
<!-- POP-UP code Start. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<!-- POP-UP code End. -->
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Parent Updated Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Parent Added Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Username Or Emailid Already Exist.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Parent Deleted Successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Parent CSV Uploaded Successfully .', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '7':
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
					if ( $active_tab === 'parentlist' ) {
						$parentdata = mjschool_get_users_data( 'parent' );
						if ( ! empty( $parentdata ) ) {
							?>
							<div>
								<div class="loader">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-school-app-loader.gif' ) ?>">
								</div>
								<div class="table-responsive mjchool_display_none">
									<form name="mjschool-common-form" action="" method="post">
										<table id="parent_list" class="display mjschool-admin-parent-datatable" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all" style="float:left;"></th>
													<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Parent Name & Email', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Relation', 'mjschool' ); ?></th>
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
												if ( $parentdata ) {
													foreach ( $parentdata as $retrieved_data ) {
														$parent_id = mjschool_encrypt_id( $retrieved_data->ID );
														$uid       = $retrieved_data->ID;
														?>
														<tr>
															<td class="mjschool-checkbox-width-10px">
																<input type="checkbox" class="mjschool-sub-chk mjschool-selected-parent select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->ID ); ?>">
															</td>
															<td class="mjschool-user-image mjschool-width-50px-td">
																<a class="mjschool-color-black" href="?page=mjschool_parent&tab=view_parent&action=view_parent&parent_id=<?php echo esc_attr( $parent_id ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>">
																	<?php
																	$uid       = $retrieved_data->ID;
																	$umetadata = mjschool_get_user_image( $uid );
																	if (empty($umetadata ) ) {
																		echo '<img src=' . esc_url( get_option( 'mjschool_parent_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																	} else {
																		echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
																	}
																	?>
																</a>
															</td>
															<td class="name">
																<a class="mjschool-color-black" href="?page=mjschool_parent&tab=view_parent&action=view_parent&parent_id=<?php echo esc_attr( $parent_id ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>">
																	<?php echo esc_html( mjschool_get_parent_name_by_id( $retrieved_data->ID ) ); ?>
																</a>
																<br>
																<span class="mjschool-list-page-email"><?php echo esc_html( $retrieved_data->user_email ); ?></span>
															</td>
															<td >
																+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php echo esc_html( ucfirst( get_user_meta( $uid, 'gender', true ) ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
															</td>
															<td >
																<?php echo esc_html( ucfirst( get_user_meta( $uid, 'relation', true ) ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Relation', 'mjschool' ); ?>"></i>
															</td>
															<?php // Custom Field Values.
															if ( ! empty( $user_custom_field ) ) {
																foreach ( $user_custom_field as $custom_field ) {
																	if ( $custom_field->show_in_table === '1' ) {
																		$module             = 'parent';
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
																					<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
																						<button class="btn btn-default view_document" type="button"><i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																					<a href="?page=mjschool_parent&tab=view_parent&action=view_parent&parent_id=<?php echo esc_attr( $parent_id ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																				</li>
																				<?php
																				if ( $user_access_edit === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																						<a href="?page=mjschool_parent&tab=addparent&action=edit&parent_id=<?php echo esc_attr( $parent_id ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				if ( $user_access_delete === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="?page=mjschool_parent&tab=parentlist&action=delete&parent_id=<?php echo esc_attr( $parent_id ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											if ($user_access_delete === '1' ) {
												?>
												<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php
											}
											?>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="button" class="mjschool-view-import-parent-csv-popup mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="parent_export_csv_selected" class="mjschool-parent-csv-selected mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
											<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="parent"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
										</div>
									</form>
								</div>
							</div>
							<?php
						} else {
							if ($user_access_add === '1' ) {
								?>
								<div class="mjschool-no-data-list-div row">
									<div class="offset-md-2 col-md-4">
										<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_parent&tab=addparent' ); ?>">
											<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
										</a>
										<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
											<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
										</div>
									</div>
									<div class="col-md-4">
										<a data-toggle="tooltip" name="import_csv" type="button" class="mjschool-view-import-parent-csv-popup">
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
					if ( $active_tab === 'addparent' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/parent/add-new-parent.php';
					}
					if ( $active_tab === 'view_parent' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/parent/view-parent.php';
					}
					if ( $active_tab === 'uploadparent' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/parent/upload_parent.php';
					}
					?>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->