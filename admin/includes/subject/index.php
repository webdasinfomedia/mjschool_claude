<?php
/**
 * Subject Management - Admin Dashboard.
 *
 * Handles CRUD operations (Create, Read, Update, Delete) for subjects in the Mjschool plugin.
 * Includes user access checks, validation, file upload handling, and database operations.
 * 
 * Key Features:
 * - Implements role-based access control for subject add, edit, delete, and view actions.
 * - Displays list of subject with DataTables integration for dynamic search and sorting.
 * - Supports bulk deletion of subject records with confirmation prompts.
 * - Includes jQuery validation, DataTables, and localized JavaScript strings for multi-language support.
 * - Displays admin notices for success or error messages (Add, Update, Delete, etc.).
 * - Ensures compliance with WordPress coding standards and best practices.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/subject
 * @since      1.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
// -------- Check Browser Javascript. ----------//
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'subject' );
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
			if ( 'subject' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'subject' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'subject' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'subject';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$document_option    = get_option( 'mjschool_upload_document_type' );
$document_type      = explode( ', ', $document_option );
$document_type_json = $document_type;
$document_size      = get_option( 'mjschool_upload_document_size' );
// This is Dashboard at admin side.
// -------------- Delete code. -------------------------------
$teacher_obj = new Mjschool_Teacher();
$tablename   = 'mjschool_subject';
if ( $action === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_action' ) ) {
		$result = mjschool_delete_subject( $tablename, mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=4' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
/* Delete selected subject. */
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $subject_id ) {
			$result = mjschool_delete_subject( $tablename, $subject_id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=4' ) );
		die();
	}
}
// ------------------ Edit-Add code. ------------------------------
if ( isset( $_POST['subject'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_subject_admin_nonce' ) ) {
		$syllabus = '';
		if ( isset( $_FILES['subject_syllabus'] ) && ! empty( $_FILES['subject_syllabus']['name'] ) ) {
			if ( $_FILES['subject_syllabus']['size'] > 0 ) {
				$syllabus = mjschool_inventory_image_upload( $_FILES['subject_syllabus'] );
			} else {
				$syllabus = sanitize_text_field(wp_unslash($_POST['sylybushidden']));
			}
		}
		$student_ids_array = isset($_POST['student_id'][0]) ? sanitize_text_field(wp_unslash($_POST['student_id'][0])) : array();
		$student_ids_string = implode( ',', array_map( 'intval', $student_ids_array ) );
		// Update subject data code.
		if ( $action === 'edit' ) {
			$subject_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['subject_id'])) ) );
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'edit_action' ) ) {
				$subjects = array(
					'subject_code' => sanitize_text_field( wp_unslash($_POST['subject_code']) ),
					'sub_name'     => sanitize_textarea_field( wp_unslash( $_POST['subject_name'] ) ),
					'class_id'     => sanitize_text_field( wp_unslash($_POST['subject_class']) ),
					'section_id'   => sanitize_text_field( wp_unslash($_POST['class_section']) ),
					'teacher_id'   => $teacher_id,
					'selected_students' => $student_ids_string,
					'edition'      => sanitize_textarea_field( wp_unslash( $_POST['subject_edition'] ) ),
					'author_name'  => sanitize_text_field( wp_unslash($_POST['subject_author']) ),
					'syllabus'     => $syllabus,
					'created_by'   => get_current_user_id(),
					'subject_credit' => sanitize_text_field(wp_unslash($_POST['subject_credit'])),
				);
				if ( isset( $_FILES['subject_syllabus'] ) && empty( $_FILES['subject_syllabus']['name'] ) ) {
					unset( $subjects['syllabus'] );
				}
				$tablename         = 'mjschool_subject';
				$selected_teachers = isset( $_REQUEST['subject_teacher'] ) ? array_map( 'intval', (array) $_REQUEST['subject_teacher'] ) : array();
				// ------------ Subject code check. ------------//
				$subject_code = sanitize_text_field( wp_unslash($_POST['subject_code']) );
				$class_id     = sanitize_text_field( intval( wp_unslash($_POST['subject_class']) ) );
				$result_sub = mjschool_get_subject_by_class_and_code($class_id, $subject_code);
				if ( ! empty( $result_sub ) ) {
					if ( $result_sub->subid != $subject_id ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=addsubject&action=edit&subject_id=' . $subject_id . '&message=5' ) );
						die();
					}
				}
				global $wpdb;
				$table_mjschool_subject = $wpdb->prefix . 'mjschool_teacher_subject';
				$subid              = array( 'subid' => intval( $subject_id ) );
				$result             = mjschool_update_record( $tablename, $subjects, $subid );
				// Update custom field data.
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'subject';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $subject_id );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$wpdb->delete( $table_mjschool_subject, array( 'subject_id' => $subject_id ), array( '%s' ) );
				if ( ! empty( $selected_teachers ) ) {
					$teacher_subject = $wpdb->prefix . 'mjschool_teacher_subject';
					foreach ( $selected_teachers as $teacher_id ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$wpdb->insert(
							$teacher_subject,
							array(
								'teacher_id'   => $teacher_id,
								'subject_id'   => sanitize_text_field( $subject_id ),
								'created_date' => time(),
								'created_by'   => get_current_user_id(),
							)
						);
					}
				}
				/* Send assign subject mail. */
				if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
					foreach ( $_POST['subject_teacher'] as $teacher_id ) {
						$smgt_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_mail_service_enable']));
						if ( $smgt_mail_service_enable ) {
							$search['{{teacher_name}}'] = mjschool_get_teacher( $teacher_id );
							$search['{{subject_name}}'] = sanitize_text_field( wp_unslash($_POST['subject_name']) );
							$search['{{school_name}}']  = get_option( 'mjschool_name' );
							$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_assign_subject_mailcontent' ) );
							if ( ! empty( $syllabus ) ) {
								$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $syllabus;
							} else {
								$attechment = '';
							}
							if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
								mjschool_send_mail_for_homework( mjschool_get_email_id_by_user_id( $teacher_id ), get_option( 'mjschool_assign_subject_title' ), $message, $attechment );
							}
						}
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			// Insert subject dta.
			/* Setup Wizard. */
			$wizard = mjschool_setup_wizard_steps_updates( 'step4_subject' );
			if ( ! empty( $_POST['subject_class'] ) ) {
				foreach ( $_POST['subject_class'] as $key => $value ) {
					$subject_code = sanitize_text_field( wp_unslash($_POST['subject_code'][ $key ]) );
					$class_id     = sanitize_text_field( $value );
					$result_sub = mjschool_get_subject_by_class_and_code($class_id, $subject_code);
					if ( ! empty( $result_sub ) ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=addsubject&message=5' ) );
						die();
					} else {
						// Insert in subject table.
						$student_ids_array = isset($_POST['student_id'][0]) ? sanitize_text_field(wp_unslash($_POST['student_id'][0])) : array();
						$student_ids_string = implode( ',', array_map( 'intval', $student_ids_array ) );
						$subjects = array(
							'subject_code' => sanitize_text_field( wp_unslash($_POST['subject_code'][ $key ]) ),
							'sub_name'     => sanitize_textarea_field( wp_unslash( $_POST['subject_name'] ) ),
							'class_id'     => sanitize_text_field( $value ),
							'section_id'   => sanitize_text_field( wp_unslash($_POST['class_section'][ $key ]) ),
							'teacher_id'   => 0,
							'edition'      => sanitize_textarea_field( wp_unslash( $_POST['subject_edition'] ) ),
							'selected_students' => $student_ids_string,
							'author_name'  => sanitize_text_field( wp_unslash($_POST['subject_author']) ),
							'syllabus'     => $syllabus,
							'created_by'   => get_current_user_id(),
							'subject_credit' => sanitize_text_field(wp_unslash($_POST['subject_credit'])),
						);
						$result   = mjschool_insert_record( $tablename, $subjects );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$lastid             = $wpdb->insert_id;
						$custom_field_obj   = new Mjschool_Custome_Field();
						$module             = 'subject';
						$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $lastid );
						$selected_teachers  = isset( $_POST['subject_teacher'][ $key ] ) ? sanitize_text_field(wp_unslash($_POST['subject_teacher'][ $key ])) : array();
						if ( ! empty( $selected_teachers ) ) {
							$teacher_subject = $wpdb->prefix . 'mjschool_teacher_subject';
							$device_token    = array();
							foreach ( $selected_teachers as $teacher_id ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$wpdb->insert(
									$teacher_subject,
									array(
										'teacher_id'   => $teacher_id,
										'subject_id'   => $lastid,
										'created_date' => time(),
										'created_by'   => get_current_user_id(),
									)
								);
								$device_token[] = get_user_meta( $teacher_id, 'token_id', true );
							}
							/* Send push notification. */
							$title             = esc_attr__( 'New Notification For Assign Subject', 'mjschool' );
							$text              = esc_attr__( 'New subject', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash($_POST['subject_name']) ) . ' ' . esc_attr__( 'has been assigned to you.', 'mjschool' );
							$notification_data = array(
								'registration_ids' => $device_token,
								'data' => array(
									'title' => $title,
									'body'  => $text,
									'type'  => 'notification',
								),
							);
							$json              = json_encode( $notification_data );
							$message           = mjschool_send_push_notification( $json );
							/* Send push notification. */
						}
						if ( $result ) {
							/* Send assign subject mail. */
							if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
								foreach ( $selected_teachers as $teacher_id ) {
									$smgt_mail_service_enable = sanitize_text_field(wp_unslash($_POST['mjschool_mail_service_enable']));
									if ( $smgt_mail_service_enable ) {
										$search['{{teacher_name}}'] = mjschool_get_teacher( $teacher_id );
										$search['{{subject_name}}'] = sanitize_text_field( wp_unslash($_POST['subject_name']) );
										$search['{{school_name}}']  = get_option( 'mjschool_name' );
										$message                    = mjschool_string_replacement( $search, get_option( 'mjschool_assign_subject_mailcontent' ) );
										if ( ! empty( $syllabus ) ) {
											$attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $syllabus;
										} else {
											$attechment = '';
										}
										if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
											mjschool_send_mail_for_homework( mjschool_get_email_id_by_user_id( $teacher_id ), get_option( 'mjschool_assign_subject_title' ), $message, $attechment );
										}
									}
								}
							}
						}
					}
				}
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=1' ) );
				die();
			}
		}
	}
}
// -------------- Export subject data. ---------------//
if ( isset( $_POST['subject_export_csv_selected'] ) ) {
	if ( isset( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $s_id ) {
			$subject_list[] = mjschool_get_subject( $s_id );
		}
		if ( ! empty( $subject_list ) ) {
			$header   = array();
			$header[] = 'Subject Code';
			$header[] = 'Subject Name';
			$header[] = 'Teacher';
			$header[] = 'Class Name';
			$header[] = 'Section Name';
			$header[] = 'Author Name';
			$header[] = 'Edition';
			$header[] = 'Created By';
			$filename = 'export/mjschool-export-subject.csv';
			$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
			fputcsv( $fh, $header );
			foreach ( $subject_list as $retrive_data ) {
				$row           = array();
				$teacher_group = array();
				$teacher_ids   = mjschool_teacher_by_subject( $retrive_data );
				foreach ( $teacher_ids as $teacher_id ) {
					$teacher_group[] = mjschool_get_teacher( $teacher_id );
				}
				$teachers = implode( ',', $teacher_group );
				$cid      = $retrive_data->class_id;
				$clasname = mjschool_get_class_name( $cid );
				if ( $retrive_data->section_id != 0 ) {
					$section_name = mjschool_get_section_name( $retrive_data->section_id );
				} else {
					$section_name = esc_attr__( 'No Section', 'mjschool' );
				}
				$created_by = mjschool_get_user_name_by_id( $retrive_data->created_by );
				$row[]      = $retrive_data->subject_code;
				$row[]      = $retrive_data->sub_name;
				$row[]      = $teachers;
				$row[]      = $clasname;
				$row[]      = $section_name;
				$row[]      = $retrive_data->author_name;
				$row[]      = $retrive_data->edition;
				$row[]      = $created_by;
				fputcsv( $fh, $row );
			}
			fclose( $fh );
			// Download csv file.
			ob_clean();
			$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-subject.csv'; // File location.
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
			echo "<div class='mjschool_csv_record_not_found'>Records not found.</div>";
		}
	}
}
// -------------- Export subject data. ---------------//
// --------------  Import subject CSV data. --------------//
if ( isset( $_REQUEST['upload_csv_file'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'upload_subject_admin_nonce' ) ) {
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
				$err      = esc_attr__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				$errors[] = $err;
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=6' ) );
				die();
			}
			// ------------ Check file size. ------------//
			if ( $file_size > 2097152 ) {
				$errors[] = 'File size limit 2 MB';
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=7' ) );
				die();
			}
			if ( empty( $errors ) === true ) {
				$rows             = array_map( 'str_getcsv', file( $file_tmp ) );
				$header           = array_map( 'trim', array_map( 'strtolower', array_shift( $rows ) ) );
				$csv              = array();
				$subject_class_id = array();
				foreach ( $rows as $row ) {
					global $wpdb;
					$csv                = array_combine( $header, $row );
					$selected_teachers = isset( $_REQUEST['subject_teacher'] ) ? array_map( 'intval', (array) $_REQUEST['subject_teacher'] ) : array();
					$teacher_subject    = $wpdb->prefix . 'mjschool_teacher_subject';
					$table_mjschool_subject = $wpdb->prefix . 'mjschool_subject';
					if ( isset( $csv['subject name'] ) ) {
						$subjectdata['sub_name'] = sanitize_text_field( $csv['subject name'] );
					}
					if ( isset( $_POST['subject_teacher'] ) ) {
						$subjectdata['teacher_id'] = 0;
					}
					if ( isset( $_POST['class_name'] ) ) {
						$subjectdata['class_id'] = sanitize_text_field( wp_unslash($_POST['class_name']) );
					}
					if ( isset( $_REQUEST['class_section'] ) ) {
						$subjectdata['section_id'] = sanitize_text_field( wp_unslash($_REQUEST['class_section']) );
					}
					if ( isset( $csv['author name'] ) ) {
						$subjectdata['author_name'] = sanitize_text_field( $csv['author name'] );
					}
					if ( isset( $csv['edition'] ) ) {
						$subjectdata['edition'] = sanitize_text_field( $csv['edition'] );
					}
					if ( isset( $csv['subject code'] ) ) {
						$subjectdata['subject_code'] = sanitize_text_field( $csv['subject code'] );
					}
					$subjectdata['created_by'] = get_current_user_id();
					$sub_name                  = sanitize_text_field( $csv['subject name'] );
					$sub_code                  = sanitize_text_field( $csv['subject code'] );
					$class_id                  = intval( wp_unslash($_POST['class_name']) );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$existing_subject_data = $wpdb->get_row( "SELECT subid FROM $table_mjschool_subject where sub_name='$sub_name' AND subject_code='$sub_code' AND class_id=$class_id" );
					if ( $existing_subject_data ) {
						$id['subid'] = $existing_subject_data->subid;
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$wpdb->update( $table_mjschool_subject, $subjectdata, $id );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$wpdb->delete(
							$teacher_subject,      // Table name.
							array( 'subject_id' => $existing_subject_data->subid ),  // Where clause.
							array( '%s' )      // Where clause data type (string).
						);
						if ( ! empty( $selected_teachers ) ) {
							foreach ( $selected_teachers as $teacher_id ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$wpdb->insert(
									$teacher_subject,
									array(
										'teacher_id'   => $teacher_id,
										'subject_id'   => $existing_subject_data->subid,
										'created_date' => time(),
										'created_by'   => get_current_user_id(),
									)
								);
							}
						}
						$success = 1;
					} else {
						error_log( 'New Data' );
						error_log( $subjectdata['sub_name'] );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$wpdb->insert( $table_mjschool_subject, $subjectdata );
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$lastid = $wpdb->insert_id;
						if ( ! empty( $selected_teachers ) ) {
							foreach ( $selected_teachers as $teacher_id ) {
								// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
								$wpdb->insert(
									$teacher_subject,
									array(
										'teacher_id'   => $teacher_id,
										'subject_id'   => $lastid,
										'created_date' => time(),
										'created_by'   => get_current_user_id(),
									)
								);
							}
						}
						$success = 1;
					}
				}
				if ( $success === 1 ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject&message=8' ) );
					die();
				}
			}
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ------ Upload CSV code. -----------//
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'Subject';
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<div class="mjschool-page-inner">
	<div class="mjschool-main-list-margin-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Subject Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Subject Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'This File Type Is Not Allowed, Please Upload Only Pdf File.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Subject Deleted Successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Please Enter Unique Subject Code', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '7':
				$message_string = esc_html__( 'File size limit 2 MB.', 'mjschool' );
				break;
			case '8':
				$message_string = esc_html__( 'Subject CSV Imported Successfully.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
		<?php } ?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php
				if ( $active_tab === 'Subject' ) {
					$retrieve_subjects = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_subjects ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="mjschool-subject-list-admin" class="display datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject Id', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject Code', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
												<?php
												if ( $school_type === 'university' )
												{?>
													<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
													<?php
												}
												?>
												<th><?php esc_html_e( 'Author Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Edition', 'mjschool' ); ?></th>
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
											foreach ( $retrieve_subjects as $retrieved_data ) {
												$encrypt_subid   = mjschool_encrypt_id( $retrieved_data->subid );
												$teacher_group   = array();
												$teacher_display = array();
												$teacher_ids     = mjschool_teacher_by_subject( $retrieved_data );
												$ti              = 0;
												foreach ( $teacher_ids as $teacher_id ) {
													$teacher_group[] = mjschool_get_teacher( $teacher_id );
													if ( $ti < 3 ) {
														$teacher_display[] = mjschool_get_teacher( $teacher_id );
													}
													++$ti;
												}
												$teachers         = implode( ',', $teacher_group );
												$teacher_displays = implode( ',', $teacher_display );
												$student_group = array();
												$student_display = array();
												$student_ids = explode( ',', $retrieved_data->selected_students);
												$ti = 0;
												foreach ($student_ids as $student_id) {
													$student_group[] = mjschool_get_teacher($student_id);
													if ($ti < 3) {
														$student_display[] = mjschool_get_teacher($student_id);
													}
													$ti++;
												}
												$student = implode( ',', $student_group);
												$student_displays = implode( ',', $student_display);
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
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->subid ); ?>"> </td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
														<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->subid ); ?>" type="subject_view">		
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/wizard/mjschool-wizard-subject.png' ); ?>" class="mjschool-massage-image">
															</p>
															
														</a>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->subid ) ) {
															echo esc_html( $retrieved_data->subid );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Code', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->subject_code ) ) {
															echo esc_html( $retrieved_data->subject_code );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Code', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->subid ); ?>" type="subject_view">
															<?php echo esc_html( $retrieved_data->sub_name ); ?>
														</a>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $teacher_displays ) ) {
															echo esc_html( $teacher_displays );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo ! empty( $teachers ) ? esc_attr( $teachers ) : esc_attr__( 'N/A', 'mjschool' ); ?>"> </i>
													</td>
													<td>
														<?php
														$cid = $retrieved_data->class_id;
														if ( ! empty( $cid ) ) {
															$clasname = mjschool_get_class_section_name_wise( $cid, $retrieved_data->section_id );
															echo esc_html( $clasname );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<?php
													if ( $school_type === 'university' )
													{
														?>
														<td>
															<?php 
															if ( ! empty( $student_displays ) ) {
																echo esc_html( $student_displays);
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $student ) ) { echo esc_html( $student); } else { esc_html_e( 'N/A', 'mjschool' ); } ?>"> </i>
														</td>
														<?php
													}
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->author_name ) ) {
															// Truncate the author name to 30 characters.
															$author_name = mb_strimwidth( $retrieved_data->author_name, 0, 30, '...' );
															echo esc_html( $author_name );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->author_name ) ) { echo esc_html( $retrieved_data->author_name ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?>"> </i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->edition ) ) {
															// Truncate the edition to 30 characters.
															$edition_chunk = mb_strimwidth( $retrieved_data->edition, 0, 20, '...' );
															echo esc_html( $edition_chunk );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->edition ) ) { echo esc_attr( $retrieved_data->edition ); } else { echo esc_attr( 'N/A' ); } ?>"> </i>
													</td>
													<?php
													// Custom Field Values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'subject';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->subid;
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
																			<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_html( $retrieved_data->subid ); ?>" type="subject_view">
																				<i class="fa fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																		if ( $user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href=<?php echo esc_url("?page=mjschool_Subject&tab=addsubject&action=edit&subject_id=". esc_attr( $encrypt_subid ) ."&_wpnonce=".esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?> class="mjschool-float-left-width-100px">
																					<i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<?php
																		}
																		if ( $user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href=<?php echo esc_url("?page=mjschool_Subject&tab=Subject&action=delete&subject_id=". esc_attr( $encrypt_subid ) ."&_wpnonce=".esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?> class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
									<div class="mjschool-print-button pull-left mjschool-padding-top-25px-res">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="mjschool-sub-chk" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
											<label for="mjschool-sub-chk" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php
										if ( $user_access_delete === '1' ) {
											 ?>
											<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>">
											</button>
											<?php
										}
										?>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="button" class="view_import_subject_csv_popup mjschool-export-import-csv-btn mjschool-custom-padding-0">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>">
										</button>
										<button data-toggle="tooltip" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" name="subject_export_csv_selected" class="subject_csv_selected mjschool-export-import-csv-btn mjschool-custom-padding-0">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>">
										</button>
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
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_Subject&tab=addsubject' ) ); ?>">
										<img class="col-md-12 mjschool-no-img-width-100px rtl_float_remove" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
									</a>
									<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
										<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
									</div>
								</div>
								<div class="col-md-4">
									<a data-toggle="tooltip" name="import_csv" type="button" class="view_import_subject_csv_popup">
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
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				if ( $active_tab === 'addsubject' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/subject/add-new-subject.php';
				}
				?>
			</div>
		</div>
	</div>
</div>
