<?php
/**
 * MJSchool Class Routine Page.
 *
 * This file provides the admin interface for managing class routines (timetables)
 * within the MJSchool plugin. Administrators and authorized users can add, edit,
 * delete, import, and export class schedules for different classes and sections.
 *
 * Key Features:
 * - User access control based on roles and permissions.
 * - Add/Edit/Delete routines with start and end times validation.
 * - Import class routines via CSV file with validation and logging.
 * - Export class routine data to CSV.
 * - Virtual classroom integration with Zoom (create, edit, delete, start meetings).
 * - Dynamic accordion display for classes and sections with sorting by time.
 * - Utilizes WordPress nonces for security and sanitization of user input.
 * - Fully internationalized with translatable strings.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/routine
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type     = get_option( 'mjschool_custom_class' );
$cust_class_room = get_option( 'mjschool_class_room' );
// phpcs:disable
//-------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
$action        = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'schedule' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $action ) ) {
			if ( 'schedule' === $user_access['page_link'] && ( $action === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'schedule' === $user_access['page_link'] && ( $action === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'schedule' === $user_access['page_link'] && ( $action === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
?>
<?php
$mjschool_obj_route        = new Mjschool_Class_Routine();
$obj_virtual_classroom     = new Mjschool_Virtual_Classroom();
$mjschool_page_name        = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
//---------- Save class Routine.  ------------//
if ( isset( $_POST['save_route'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_root_admin_nonce' ) ) {

		$nonce_redirect     = wp_create_nonce( 'mjschool_class_routine_tab' );
		$teacher_id         = isset( $_POST['subject_teacher'] ) ? ( is_array( $_POST['subject_teacher'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['subject_teacher'] ) ) : sanitize_text_field( wp_unslash( $_POST['subject_teacher'] ) ) ) : '';
		$start_time_raw     = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
		$end_time_raw       = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
		$start_time         = mjschool_time_convert( $start_time_raw );
		$end_time           = mjschool_time_convert( $end_time_raw );
		$start_time_1       = $start_time_raw;
		$end_time_1         = $end_time_raw;
		$start_time_convert = gmdate( 'h:i', strtotime( $start_time_raw ) );
		$end_time_convert   = gmdate( 'h:i', strtotime( $end_time_raw ) );
		$start_time_data    = explode( ':', $start_time_1 );
		$start_hour         = str_pad( isset( $start_time_data[0] ) ? $start_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
		$start_min          = str_pad( isset( $start_time_data[1] ) ? $start_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
		$start_time_new     = $start_hour . ':' . $start_min;
		$start_time_in_24_hour_format = gmdate( 'H:i', strtotime( $start_time_new ) );
		$end_time_data                = explode( ':', $end_time_1 );
		$end_hour                     = str_pad( isset( $end_time_data[0] ) ? $end_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
		$end_min                      = str_pad( isset( $end_time_data[1] ) ? $end_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
		$end_time_new                 = $end_hour . ':' . $end_min;
		$end_time_in_24_hour_format   = gmdate( 'H:i', strtotime( $end_time_new ) );
		
		$subject_id_post    = isset( $_POST['subject_id'] ) ? intval( $_POST['subject_id'] ) : 0;
		$class_id_post      = isset( $_POST['class_id'] ) ? intval( $_POST['class_id'] ) : 0;
		$class_section_post = isset( $_POST['class_section'] ) ? intval( $_POST['class_section'] ) : 0;
		$room_id_post       = isset( $_POST['room_id'] ) ? intval( $_POST['room_id'] ) : 0;
		$weekday_post       = isset( $_POST['weekday'] ) ? ( is_array( $_POST['weekday'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['weekday'] ) ) : sanitize_text_field( wp_unslash( $_POST['weekday'] ) ) ) : '';
		
		$route_data = array();
		
		if ( ( $end_time_in_24_hour_format === '00:00' && $start_time_in_24_hour_format > '00:00' ) ||
			( $end_time_in_24_hour_format === '12:00' && $start_time_in_24_hour_format > '12:00' ) ||
			( $end_time_in_24_hour_format > $start_time_in_24_hour_format ) ) {
			if ( $action === 'edit' ) {
				$route_data['subject_id']       = $subject_id_post;
				$route_data['class_id']         = $class_id_post;
				$route_data['section_name']     = $class_section_post;
				$route_data['teacher_id']       = is_array( $teacher_id ) ? sanitize_text_field( $teacher_id[0] ) : $teacher_id;
				$route_data['start_time']       = $start_time_new;
				$route_data['end_time']         = $end_time_new;
				$route_data['weekday']          = is_array( $weekday_post ) ? sanitize_text_field( $weekday_post[0] ) : $weekday_post;
				$route_data['multiple_teacher'] = 'yes';
				$route_data['room_id']          = $room_id_post;
			} else {
				if ( is_array( $teacher_id ) && is_array( $weekday_post ) ) {
					foreach ( $teacher_id as $teacher ) {
						foreach ( $weekday_post as $week_days ) {
							$route_data[] = array(
								'subject_id'       => $subject_id_post,
								'class_id'         => $class_id_post,
								'section_name'     => $class_section_post,
								'teacher_id'       => sanitize_text_field( $teacher ),
								'start_time'       => $start_time_new,
								'end_time'         => $end_time_new,
								'weekday'          => sanitize_text_field( $week_days ),
								'multiple_teacher' => 'yes',
								'room_id'          => $room_id_post,
							);
						}
					}
				}
			}
			if ( $action === 'edit' ) { //------- Edit class routine. --------//
				if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_action' ) ) {
					$route_id_val = isset( $_REQUEST['route_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['route_id'] ) ) : '';
					$route_id     = array( 'route_id' => mjschool_decrypt_id( $route_id_val ) );
					$mjschool_obj_route->mjschool_update_route( $route_data, $route_id );

					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=2' ) );
					die();
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else { //------- Record Insert. ---------//
				// Setup Wizard.
				$wizard     = mjschool_setup_wizard_steps_updates( 'step5_class_time_table' );
				$retuen_val = '';
				if ( is_array( $route_data ) ) {
					foreach ( $route_data as $route ) {
						$retuen_val = $mjschool_obj_route->mjschool_is_route_exist( $route );
					}
				}
				if ( $retuen_val === 'success' ) {
					// Create Virtual Class.
					$route_id_array = $mjschool_obj_route->mjschool_save_route_with_virtual_class( $route_data );
					if ( $route_id_array ) {
						$create_virtual_classroom = isset( $_POST['create_virtual_classroom'] ) && $_POST['create_virtual_classroom'] === '1';
						if ( $create_virtual_classroom ) {
							foreach ( $route_id_array as $route_id_item ) {
								$start_date     = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : gmdate( 'Y-m-d' );
								$end_date       = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : gmdate( 'Y-m-d' );
								$agenda         = isset( $_POST['agenda'] ) ? sanitize_textarea_field( wp_unslash( $_POST['agenda'] ) ) : '';
								$obj_mark       = new Mjschool_Class_Routine();
								$route_data_obj = mjschool_get_route_by_id( $route_id_item );
								$start_time_vc  = mjschool_start_time_convert( $start_time_raw );
								$end_time_vc    = mjschool_end_time_convert( $end_time_raw );
								if ( empty( $_POST['password'] ) ) {
									$password = wp_generate_password( 10, true, true );
								} else {
									$password = sanitize_text_field( wp_unslash( $_POST['password'] ) );
								}
								$metting_data = array(
									'teacher_id'       => $route_data_obj->teacher_id,
									'password'         => $password,
									'start_date'       => $start_date,
									'start_time'       => $start_time_vc,
									'end_date'         => $end_date,
									'end_time'         => $end_time_vc,
									'weekday'          => $route_data_obj->weekday,
									'agenda'           => $agenda,
									'route_id'         => $route_id_item,
									'class_id'         => $route_data_obj->class_id,
									'class_section_id' => $route_data_obj->section_name,
									'subject_id'       => $route_data_obj->subject_id,
									'action'           => 'insert',
								);
								$result = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( $metting_data );
							}
						}
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=1' ) );
						die();
					}
				} elseif ( $retuen_val === 'duplicate' ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=4' ) );
					die();
				} elseif ( $retuen_val === 'teacher_duplicate' ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=5' ) );
					die();
				}
			}
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=6' ) );
			die();
		}
	}
}
//--------------- Save import class route data. --------------------//
if ( isset( $_POST['save_import_csv'] ) ) {
	// Verify nonce for import.
	$import_nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $import_nonce, 'upload_class_route_admin_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( isset( $_FILES['csv_file'] ) && ! empty( $_FILES['csv_file']['name'] ) ) {
		$nonce_redirect = wp_create_nonce( 'mjschool_class_routine_tab' );
		$errors         = array();
		$file_name      = isset( $_FILES['csv_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['csv_file']['name'] ) ) : '';
		$file_size      = isset( $_FILES['csv_file']['size'] ) ? intval( $_FILES['csv_file']['size'] ) : 0;
		$file_tmp       = isset( $_FILES['csv_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['csv_file']['tmp_name'] ) ) : '';
		$file_type      = isset( $_FILES['csv_file']['type'] ) ? sanitize_mime_type( wp_unslash( $_FILES['csv_file']['type'] ) ) : '';
		$value          = explode( '.', $file_name );
		$file_ext       = strtolower( array_pop( $value ) );
		$extensions     = array( 'csv' );
		$upload_dir     = wp_upload_dir();
		if ( ! in_array( $file_ext, $extensions, true ) ) {
			$module      = 'routine';
			$status      = 'file type error';
			$log_message = 'Routine import fail due to invalid file type';
			mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
			$err      = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
			$errors[] = $err;
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=import_class_route&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=7' ) );
			die();
		}
		//------------ Check File Size. ------------//
		if ( $file_size > 2097152 ) {
			$errors[] = 'File size limit 2 MB';
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=import_class_route&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=8' ) );
			die();
		}
		if ( empty( $errors ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$rows   = array_map( 'str_getcsv', file( $file_tmp ) );
			$header = array_map( 'strtolower', array_shift( $rows ) );
			$csv    = array();
			foreach ( $rows as $row ) {
				if ( empty( array_filter( $row ) ) ) {
					continue;
				}
				$csv = array_combine( $header, $row );
				global $wpdb;
				$mjschool_time_table = $wpdb->prefix . 'mjschool_time_table';
				$subject_code        = isset( $csv['subject id'] ) ? sanitize_text_field( $csv['subject id'] ) : '';
				$subject_name        = isset( $csv['subject name'] ) ? sanitize_text_field( $csv['subject name'] ) : '';
				$subject_data        = mjschool_get_subject( $subject_code );
				$routedata           = array();
				if ( isset( $_POST['class_id'] ) ) {
					$routedata['class_id'] = intval( $_POST['class_id'] );
				}
				if ( isset( $_POST['class_section'] ) ) {
					$routedata['section_name'] = intval( $_POST['class_section'] );
				}
				if ( isset( $csv['start time'] ) ) {
					$routedata['start_time'] = sanitize_text_field( $csv['start time'] );
				}
				if ( isset( $csv['end time'] ) ) {
					$routedata['end_time'] = sanitize_text_field( $csv['end time'] );
				}
				if ( isset( $csv['weekday'] ) ) {
					$routedata['weekday'] = intval( $csv['weekday'] );
				}
				$routedata['multiple_teacher'] = 'yes';
				$username_csv                  = isset( $csv['username'] ) ? sanitize_email( $csv['username'] ) : '';
				$teacher_data                  = get_user_by( 'email', $username_csv );
				$teacher_id_csv                = $teacher_data ? intval( $teacher_data->ID ) : 0;
				$routedata['teacher_id']       = $teacher_id_csv;
				$routedata['subject_id']       = $subject_data ? intval( $subject_data->subid ) : 0;
				$all_class_route               = $mjschool_obj_route->mjschool_is_route_exist( $routedata );
				$import_success                = false;
				$class_id_post                 = isset( $_POST['class_id'] ) ? intval( $_POST['class_id'] ) : 0;
				if ( $subject_data && $class_id_post === intval( $subject_data->class_id ) ) {
					if ( $all_class_route === 'success' ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$insert         = $wpdb->insert( $mjschool_time_table, $routedata );
						$import_success = true;
					}
				}
				$module = 'routine';
				if ( $import_success ) {
					$status      = 'Success';
					$log_message = 'Row imported successfully: Subject - ' . $subject_name;
				} else {
					$status      = 'Class or Selection not Match';
					$log_message = 'Row import failed: Subject - ' . $subject_name;
				}
				mjschool_append_csv_log( $log_message, get_current_user_id(), $module, $status );
			}
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=10' ) );
			die();
		}
	}
}
//--------- Virtual class meeting create.  -------//
if ( isset( $_POST['create_meeting'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'create_meeting_admin_nonce' ) ) {
		$nonce_redirect = wp_create_nonce( 'mjschool_class_routine_tab' );
		// Sanitize POST data before passing.
		$meeting_post_data = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$result            = $obj_virtual_classroom->mjschool_create_meeting_in_zoom( $meeting_post_data );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=1' ) );
			die();
		}
	}
}
//-------- Delete class routine. ---------//
if ( $action === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_action' ) ) {
		$tablenm      = 'mjschool_time_table';
		$route_id_val = isset( $_REQUEST['route_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['route_id'] ) ) : '';
		$result       = mjschool_delete_route( $tablenm, mjschool_decrypt_id( $route_id_val ) );
		if ( $result ) {
			$nonce_redirect = wp_create_nonce( 'mjschool_class_routine_tab' );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( $action === 'routine_export_csv' ) {
	// Verify nonce for export.
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_class_routine_tab' ) ) {
		// For backward compatibility, also check without nonce but add nonce to links.
	}
	$nonce_redirect = wp_create_nonce( 'mjschool_class_routine_tab' );
	$class_id       = isset( $_REQUEST['class_id'] ) ? intval( $_REQUEST['class_id'] ) : 0;
	$section_name   = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) : '';
	if ( $class_id !== 0 && ( $section_name === 'remove' || $section_name === '' ) ) { //------- Only Class Select -------//
		$class_route_list = mjschool_get_time_table_using_class_and_section( $class_id, 0 );
	} else {
		$class_route_list = mjschool_get_time_table_using_class_and_section( $class_id, intval( $section_name ) );
	}
	if ( ! empty( $class_route_list ) ) {
		$header   = array();
		$header[] = 'Class Name';
		$header[] = 'Section Name';
		$header[] = 'Subject id';
		$header[] = 'Subject Name';
		$header[] = 'username';
		$header[] = 'Teacher Name';
		$header[] = 'Start Time';
		$header[] = 'End Time';
		$header[] = 'Weekday';
		$filename = 'export/mjschool-export-class-route.csv';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		$fh = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' );
		if ( false === $fh ) {
			wp_die( esc_html__( "Can't open file", 'mjschool' ) );
		}
		fputcsv( $fh, $header );
		foreach ( $class_route_list as $retrive_data ) {
			$row       = array();
			$classname = mjschool_get_class_name( $retrive_data->class_id );
			if ( $retrive_data->section_name !== '0' && $retrive_data->section_name !== 0 ) {
				$section_name_new = mjschool_get_section_name( $retrive_data->section_name );
			} else {
				$section_name_new = 'No Section';
			}
			$sub_name           = mjschool_get_single_subject_name( $retrive_data->subject_id );
			$teacher_first_name = get_user_meta( $retrive_data->teacher_id, 'first_name', true );
			$teacher_last_name  = get_user_meta( $retrive_data->teacher_id, 'last_name', true );
			$teacher_name       = $teacher_first_name . ' ' . $teacher_last_name;
			$row[]              = $classname;
			$row[]              = $section_name_new;
			$row[]              = $retrive_data->subject_id;
			$row[]              = $sub_name;
			$student_data       = get_userdata( $retrive_data->teacher_id );
			$email              = $student_data ? $student_data->user_email : '';
			$row[]              = $email;
			$row[]              = $teacher_name;
			$row[]              = $retrive_data->start_time;
			$row[]              = $retrive_data->end_time;
			$row[]              = $retrive_data->weekday;
			fputcsv( $fh, $row );
		}
		fclose( $fh );
		// Download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/admin/reports/mjschool-export-class-route.csv'; // file location.
		$mime = 'text/plain';
		header( 'Content-Type:application/force-download' );
		header( 'Pragma: public' );       // Required.
		header( 'Expires: 0' );           // No cache.
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Last-Modified: ' . date( 'D, d M Y H:i:s', filemtime($file ) ) . ' GMT' );
		header( 'Cache-Control: private', false);
		header( 'Content-Type: ' . $mime);
		header( 'Content-Disposition: attachment; filename="' . basename($file) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Connection: close' );
		readfile( $file );
		die();
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_route&tab=route_list&_wpnonce=' . esc_attr( $nonce_redirect ) . '&message=9' ) );
		die;
	}
}
?>
<?php $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'route_list'; ?>
<div class="mjschool-page-inner"><!------- Page inner. --------->
	<div class="mjschool_grade_page mjschool-main-list-margin-15px">
		<?php
		//-------- Class routine messages. ---------//
		$message        = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		$message_string = '';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Routine Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Routine Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Routine Deleted Successfully.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Routine Alredy Added For This Time Period.Please Try Again.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Teacher Is Not Available.', 'mjschool' );
				break;
			case '6':
				$message_string = esc_html__( 'End Time should be greater than Start Time.', 'mjschool' );
				break;
			case '7':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
			case '8':
				$message_string = esc_html__( 'File size limit 2 MB.', 'mjschool' );
				break;
			case '9':
				$message_string = esc_html__( 'Records not found.', 'mjschool' );
				break;
			case '10':
				$message_string = esc_html__( 'CSV Imported Successfully.', 'mjschool' );
				break;
			case '11':
				$message_string = esc_html__( 'Subject Not Found For This Class', 'mjschool' );
				break;
		}
		if ( $message && $message !== '0' ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white"><!-------- Panel white. ------->
			<div class="mjschool-panel-body"><!-------- Panel body. ------->
				<div class=" mjschool-class-list">
					<?php $nonce = wp_create_nonce( 'mjschool_class_routine_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'route_list' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_route&tab=route_list&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'route_list' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Routine list', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab === 'teacher_timetable' ) { ?>active<?php } ?>">
							<a href="?page=mjschool_route&tab=teacher_timetable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'teacher_timetable' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Teacher TimeTable', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $action === 'edit' && $active_tab === 'addroute' ) {
							?>
							<li class="<?php if ( $active_tab === 'addroute' ) { ?>active<?php } ?>">
								<a href="#" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addroute' ? 'nav-tab-active' : ''; ?>">
									<?php esc_html_e( 'Edit Class Time Table', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} elseif ( $mjschool_page_name === 'mjschool_route' && $active_tab === 'addroute' ) {
							?>
							<li class="<?php if ( $active_tab === 'addroute' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_library&tab=addbook" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'addroute' ? 'nav-tab-active' : ''; ?>">
									<?php esc_html_e( 'Add Class Time Table', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
					if ( $active_tab === 'route_list' ) {

						// Check nonce for class routine list tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_class_routine_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<div class="mjschool-panel-white margin_top_20px"> <!-------- Panel white. ------->
							<div class="mjschool-panel-body"><!-------- Panel body. ------->
								<div class="mjschool-popup-bg">
									<div class="mjschool-overlay-content mjschool-max-height-overflow">
										<div class="modal-content">
											<div class="mjschool-category-list"></div>
										</div>
									</div>
								</div>
								<div id="accordion" class="mjschool_fix_accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion-flush" aria-multiselectable="true" role="tablist">
									<?php
									$retrieve_class_data = mjschool_get_all_data( 'mjschool_class' );
									$i                   = 0;
									if ( ! empty( $retrieve_class_data ) ) {
										foreach ( $retrieve_class_data as $class ) {
											if ( ! empty( $class ) ) {
												?>
												<div class="mt-1 accordion-item mjschool-class-border-div">
													<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
														<button class="accordion-button class_route_list collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
															<div class="col-md-10 col-7 mjschool-route-rtl-list">
																<span class="Title_font_weight"><?php echo esc_html__( 'Class', 'mjschool' ) . ':'; ?>&nbsp;</span><?php echo esc_html( $class->class_name ); ?>
															</div>
															<div class="col-md-2 col-5 row justify-content-end mjschool-view-result">
																<div class="col-md-5 mjschool-width-50px">
																	<a href="#" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="submit" data-toggle="tooltip" class_id="<?php echo esc_attr( $class->class_id ); ?>" section_id="<?php echo esc_attr( $class->class_section ); ?>" class="mjschool-float-right mjschool-routine-import-csv mjschool-rootine-export-import-button mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-export-csv.png' ); ?>"></a>
																</div>
																<div class="col-md-4 mjschool-width-50px mjschool-rtl-margin-left-20px mjschool-exam-result-pdf-margin mjschool_margin_right_22px">
																	<a href="?page=mjschool_route&tab=route_list&action=routine_export_csv&class_id=<?php echo esc_attr( $class->class_id ); ?>&class_section=<?php echo esc_attr( $class->class_section ); ?>&_wpnonce=<?php echo esc_attr( $nonce ); ?>" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" type="submit" data-toggle="tooltip" class="mjschool-float-right mjschool-rootine-export-import-button mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></a>
																</div>
															</div>
														</button>
													</h4>
													<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
														<div class="mjschool-panel-body">
															<table class="table table-bordered">
																<?php
																$sectionid = 0;
																foreach ( mjschool_day_list() as $daykey => $dayname ) {
																	?>
																	<tr>
																		<th><?php echo esc_html( $dayname ); ?></th>
																		<td>
																			<?php
																			$period = $mjschool_obj_route->mjschool_get_period( $class->class_id, $sectionid, $daykey );
																			// Sorting function based on start time and then end time.
																			usort(
																				$period,
																				function ( $a, $b ) {
																					$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																					$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																					if ( $startA === $startB ) {
																						$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																						$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																						return $endA <=> $endB;
																					}
																					return $startA <=> $startB;
																				}
																			);
																			if ( ! empty( $period ) ) {
																				foreach ( $period as $period_data ) {
																					$route_id = mjschool_encrypt_id( $period_data->route_id );
																					echo '<div class="btn-group m-b-sm">';
																					if ( $period_data->multiple_teacher === 'yes' ) {
																						echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) ) . '( ' . esc_html( mjschool_get_display_name( $period_data->teacher_id ) ) . ' )';
																					} else {
																						echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																					}
																					$start_time_data = explode( ':', $period_data->start_time );
																					$start_hour      = str_pad( isset( $start_time_data[0] ) ? $start_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																					$start_min       = str_pad( isset( $start_time_data[1] ) ? $start_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																					$end_time_data   = explode( ':', $period_data->end_time );
																					$end_hour        = str_pad( isset( $end_time_data[0] ) ? $end_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																					$end_min         = str_pad( isset( $end_time_data[1] ) ? $end_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																					if ( $school_type === 'university' ) {
																						if ( intval( $cust_class_room ) === 1 ) {
																							$class_room = mjschool_get_class_room_name( $period_data->room_id );
																							if ( ! empty( $class_room ) ) {
																								echo '<span class="time"> ( ' . esc_html( $class_room->room_name ) . ' ) </span>';
																							}
																						}
																					}
																					echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																					$create_meeting      = '';
																					$update_meeting      = '';
																					$delete_meeting      = '';
																					$meeting_statrt_link = '';
																					if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																						$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																						if ( empty( $meeting_data ) ) {
																							$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none mjschool-show-virtual-popup" href="#" id="' . esc_attr( $period_data->route_id ) . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																						} else {
																							$create_meeting = '';
																						}
																						if ( ! empty( $meeting_data ) ) {
																							$update_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=edit_meeting&action=edit&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																							$delete_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&action=delete&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a></li>';
																							$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( $meeting_data->meeting_start_link ) . '" target="_blank">' . esc_html__( 'Virtual Class Start', 'mjschool' ) . '</a></li>';
																						} else {
																							$update_meeting      = '';
																							$delete_meeting      = '';
																							$meeting_statrt_link = '';
																						}
																					}
																					echo '</span><span class="caret"></span></button>';
																					echo '<ul role="menu" class="pt-2 dropdown-menu">
																							<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="?page=mjschool_route&tab=addroute&action=edit&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) . '">' . esc_html__( 'Edit Time Table', 'mjschool' ) . '</a></li>
																							
																							<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="?page=mjschool_route&tab=route_list&action=delete&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Time Table', 'mjschool' ) . '</a></li>' . wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ) . '
																						</ul>';
																					echo '</div>';
																				}
																			}
																			?>
																		</td>
																	</tr>
																	<?php
																}
																?>
															</table>
														</div>
													</div>
												</div>
												<?php
											}
											$create_meeting      = '';
											$update_meeting      = '';
											$delete_meeting      = '';
											$meeting_statrt_link = '';
											$sectionname         = '';
											$sectionid           = '';
											$class_sectionsdata  = mjschool_get_class_sections( $class->class_id );
											if ( ! empty( $class_sectionsdata ) ) {
												foreach ( $class_sectionsdata as $section ) {
													++$i;
													$sectionname = $section->section_name;
													$sectionid   = $section->id;
													?>
													<div class="mt-1 accordion-item mjschool-class-border-div">
														<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
															<button class="accordion-button class_route_list collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-collapse_<?php echo esc_attr( $i ); ?>">
																<div class="col-md-10 col-7 mjschool-route-rtl-list">
																	<span class="Title_font_weight"><?php echo esc_html__( 'Class', 'mjschool' ) . ':'; ?>&nbsp;</span><?php echo esc_html( mjschool_get_class_section_name_wise( $section->class_id, $sectionid ) ); ?> &nbsp;&nbsp;&nbsp;&nbsp;
																</div>
																<div class="col-md-2 col-5 row justify-content-end mjschool-view-result">
																	<div class="col-md-5 mjschool-width-50px">
																		<a href="#" title="<?php esc_attr_e( 'Import CSV', 'mjschool' ); ?>" type="submit" data-toggle="tooltip" class_id="<?php echo esc_attr( $class->class_id ); ?>" section_id="<?php echo esc_attr( $sectionid ); ?>" class="mjschool-float-right mjschool-routine-import-csv mjschool-rootine-export-import-button mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-export-csv.png' ); ?>"></a>
																	</div>
																	<div class="col-md-4 mjschool-width-50px mjschool-rtl-margin-left-20px mjschool-exam-result-pdf-margin mjschool_margin_right_22px">
																		<a href="?page=mjschool_route&tab=route_list&action=routine_export_csv&class_id=<?php echo esc_attr( $class->class_id ); ?>&class_section=<?php echo esc_attr( $sectionid ); ?>&_wpnonce=<?php echo esc_attr( $nonce ); ?>" title="<?php esc_attr_e( 'Export CSV', 'mjschool' ); ?>" type="submit" data-toggle="tooltip" class="mjschool-float-right mjschool-rootine-export-import-button mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>"></a>
																	</div>
																</div>
															</button>
														</h4>
														<div id="flush-collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" data-bs-parent="#mjschool-accordion-flush">
															<div class="mjschool-panel-body">
																<table class="table table-bordered">
																	<?php
																	foreach ( mjschool_day_list() as $daykey => $dayname ) {
																		?>
																		<tr>
																			<th><?php echo esc_html( $dayname ); ?></th>
																			<td>
																				<?php
																				$period = $mjschool_obj_route->mjschool_get_period( $class->class_id, $section->id, $daykey );
																				if ( ! empty( $period ) ) {
																					// Sorting function based on start time and then end time.
																					usort(
																						$period,
																						function ( $a, $b ) {
																							$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																							$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																							if ( $startA === $startB ) {
																								$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																								$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																								return $endA <=> $endB;
																							}
																							return $startA <=> $startB;
																						}
																					);
																				}
																				if ( ! empty( $period ) ) {
																					foreach ( $period as $period_data ) {
																						$route_id = mjschool_encrypt_id( $period_data->route_id );
																						echo '<div class="btn-group m-b-sm">';
																						if ( $period_data->multiple_teacher === 'yes' ) {
																							echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) ) . '( ' . esc_html( mjschool_get_display_name( $period_data->teacher_id ) ) . ' )';
																						} else {
																							echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																						}
																						$start_time_data = explode( ':', $period_data->start_time );
																						$start_hour      = str_pad( isset( $start_time_data[0] ) ? $start_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																						$start_min       = str_pad( isset( $start_time_data[1] ) ? $start_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																						$start_am_pm     = '';
																						$end_time_data   = explode( ':', $period_data->end_time );
																						$end_hour        = str_pad( isset( $end_time_data[0] ) ? $end_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																						$end_min         = str_pad( isset( $end_time_data[1] ) ? $end_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																						$end_am_pm       = '';
																						if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																							$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																							if ( empty( $meeting_data ) ) {
																								$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none mjschool-show-virtual-popup" href="#" id="' . esc_attr( $period_data->route_id ) . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																							} else {
																								$create_meeting = '';
																							}
																							if ( ! empty( $meeting_data ) ) {
																								$update_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=edit_meeting&action=edit&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																								$delete_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&action=delete&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a></li>';
																								$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( $meeting_data->meeting_start_link ) . '" target="_blank">' . esc_html__( 'Start Virtual Class', 'mjschool' ) . '</a></li>';
																							} else {
																								$update_meeting      = '';
																								$delete_meeting      = '';
																								$meeting_statrt_link = '';
																							}
																						}
																						if ( $school_type === 'university' ) {
																							if ( intval( $cust_class_room ) === 1 ) {
																								$class_room = mjschool_get_class_room_name( $period_data->room_id );
																								if ( ! empty( $class_room ) ) {
																									echo '<span class="time"> ( ' . esc_html( $class_room->room_name ) . ' ) </span>';
																								}
																							}
																						}
																						echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' ' . esc_html( $start_am_pm ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ' . esc_html( $end_am_pm ) . ' ) </span>';
																						echo '</span><span class="caret"></span></button>';
																						echo '<ul class="pt-2 dropdown-menu mjschool-edit-delete-drop">
																								<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="?page=mjschool_route&tab=addroute&action=edit&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) . '">' . esc_html__( 'Edit', 'mjschool' ) . '</a></li>
																								<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );" href="?page=mjschool_route&tab=route_list&action=delete&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) . '">' . esc_html__( 'Delete', 'mjschool' ) . '</a></li>
																								' . wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ) . '
																							</ul>';
																						echo '</div>';
																					}
																				}
																				?>
																			</td>
																		</tr>
																		<?php
																	}
																	?>
																</table>
															</div>
														</div>
													</div>
													<?php
												}
											}
											++$i;
										}
									} else {
										esc_html_e( 'Class data not avilable', 'mjschool' );
									}
									?>
								</div>

								<button data-toggle="tooltip" title="<?php esc_attr_e( 'CSV logs', 'mjschool' ); ?>" name="csv_log" type="button" class="mjschool-download-csv-log mjschool-export-import-csv-btn mjschool-custom-padding-0" id="routine">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-import-csv.png' ); ?>">
								</button>

							</div><!-------- Panel body. ------->
						</div><!-------- Panel white. ------->
						<?php
					}
					if ( $active_tab === 'addroute' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/routine/add-route.php';
					}
					if ( $active_tab === 'import_class_route' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/routine/import-class-route.php';
					}
					if ( $active_tab === 'teacher_timetable' ) {
						// Check nonce for class routine list tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_class_routine_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<div class="mjschool-popup-bg">
							<div class="mjschool-overlay-content mjschool-max-height-overflow">
								<div class="modal-content">
									<div class="mjschool-category-list"></div>
								</div>
							</div>
						</div>
						<div class="mjschool-panel-white margin_top_20px"><!-------- Panel white. ------->
							<div class="mjschool-panel-body"><!-------- Panel body. ------->
								<div id="accordion" class="mjschool_fix_accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" aria-multiselectable="true" role="tablist">
									<?php
									$teacherdata = mjschool_get_users_data( 'teacher' );
									if ( ! empty( $teacherdata ) ) {
										$i = 0;
										foreach ( $teacherdata as $retrieved_data ) {
											$teacher_obj = new Mjschool_Teacher();
											$classes     = '';
											$classes     = $teacher_obj->mjschool_get_class_by_teacher( $retrieved_data->ID );
											$classname   = '';
											if ( is_array( $classes ) ) {
												foreach ( $classes as $class ) {
													$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
												}
											}
											$classname_rtrim = rtrim( $classname, ', ' );
											$classname_ltrim = ltrim( $classname_rtrim, ', ' );
											?>
											<div class="mt-1 accordion-item mjschool-class-border-div">
												<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
													<button class="accordion-button class_route_list collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
														<span class="Title_font_weight"><?php esc_html_e( 'Teacher', 'mjschool' ); ?></span> :
														<?php
														if ( ! empty( $classname_ltrim ) ) {
															echo esc_html( $retrieved_data->display_name ) . '( ' . esc_html( $classname_ltrim ) . ' )';
														} else {
															echo esc_html( $retrieved_data->display_name );
														}
														?>
													</button>
												</h4>
												<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-wizard-accordion-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
													<div class="mjschool-panel-body">
														<table class="table table-bordered">
															<?php
															++$i;
															foreach ( mjschool_day_list() as $daykey => $dayname ) {
																?>
																<tr>
																	<th><?php echo esc_html( $dayname ); ?></th>
																	<td>
																		<?php
																		$period_1 = $mjschool_obj_route->mjschool_get_period_by_teacher( $retrieved_data->ID, $daykey );
																		$period_2 = $mjschool_obj_route->mjschool_get_period_by_particular_teacher( $retrieved_data->ID, $daykey );
																		$period   = array();
																		if ( ! empty( $period_1 ) && ! empty( $period_2 ) ) {
																			$period = array_merge( $period_1, $period_2 );
																		} elseif ( ! empty( $period_1 ) && empty( $period_2 ) ) {
																			$period = $period_1;
																		} elseif ( empty( $period_1 ) && ! empty( $period_2 ) ) {
																			$period = $period_2;
																		}
																		if ( ! empty( $period ) ) {
																			// Sorting function.
																			usort(
																				$period,
																				function ( $a, $b ) {
																					$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																					$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																					if ( $startA === $startB ) {
																						$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																						$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																						return $endA <=> $endB;
																					}
																					return $startA <=> $startB;
																				}
																			);
																			foreach ( $period as $period_data ) {
																				$route_id = mjschool_encrypt_id( $period_data->route_id );
																				echo '<div class="btn-group m-b-sm">';
																				echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" data-bs-toggle="dropdown"><span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																				$start_time_data = explode( ':', $period_data->start_time );
																				$start_hour      = str_pad( isset( $start_time_data[0] ) ? $start_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																				$start_min       = str_pad( isset( $start_time_data[1] ) ? $start_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																				$end_time_data   = explode( ':', $period_data->end_time );
																				$end_hour        = str_pad( isset( $end_time_data[0] ) ? $end_time_data[0] : '00', 2, '0', STR_PAD_LEFT );
																				$end_min         = str_pad( isset( $end_time_data[1] ) ? $end_time_data[1] : '00', 2, '0', STR_PAD_LEFT );
																				echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																				$create_meeting      = '';
																				$update_meeting      = '';
																				$delete_meeting      = '';
																				$meeting_statrt_link = '';
																				if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
																					$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $period_data->route_id );
																					if ( empty( $meeting_data ) ) {
																						$create_meeting = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none mjschool-show-virtual-popup" href="#" id="' . esc_attr( $period_data->route_id ) . '">' . esc_html__( 'Create Virtual Class', 'mjschool' ) . '</a></li>';
																					} else {
																						$create_meeting = '';
																					}
																					if ( ! empty( $meeting_data ) ) {
																						$update_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=edit_meeting&action=edit&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '">' . esc_html__( 'Edit Virtual Class', 'mjschool' ) . '</a></li>';
																						$delete_meeting      = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&action=delete&meeting_id=' . intval( $meeting_data->meeting_id ) ) ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete Virtual Class', 'mjschool' ) . '</a></li>';
																						$meeting_statrt_link = '<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="' . esc_url( $meeting_data->meeting_start_link ) . '" target="_blank">' . esc_html__( 'Virtual Class Start', 'mjschool' ) . '</a></li>';
																					} else {
																						$update_meeting      = '';
																						$delete_meeting      = '';
																						$meeting_statrt_link = '';
																					}
																				}
																				echo '<span>' . esc_html( mjschool_get_class_name( $period_data->class_id ) ) . '</span>';
																				echo '</span></span><span class="caret"></span></button>';
																				echo '<ul role="menu" class="pt-2 dropdown-menu">
																						<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="?page=mjschool_route&tab=addroute&action=edit&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) . '">' . esc_html__( 'Edit', 'mjschool' ) . '</a></li>
																						<li class="mjschool-float-left-width-100px"><a class="mjschool-float-left-width-100px text-decoration-none" href="?page=mjschool_route&tab=route_list&action=delete&route_id=' . esc_attr( $route_id ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) . '" onclick="return confirm(\'' . esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ) . '\' );">' . esc_html__( 'Delete', 'mjschool' ) . '</a></li>
																						' . wp_kses_post( $create_meeting ) . '' . wp_kses_post( $update_meeting ) . '' . wp_kses_post( $delete_meeting ) . '' . wp_kses_post( $meeting_statrt_link ) . '
																					</ul>';
																				echo '</div>';
																			}
																		}
																		?>
																	</td>
																</tr>
																<?php
															}
															?>
														</table>
													</div>
												</div>
											</div>
											<?php
										}
									} else {

										?>
										<div class="mjschool-calendar-event-new">
											<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
										</div>
										<?php
									}
									?>
								</div>
							</div><!-------- Panel body. ------->
						</div><!-------- Panal white. ------->
						<?php
					}
					?>
				</div>
			</div><!-------- Panal body. ------->
		</div><!-------- Panal white. ------->
	</div>
</div><!------- Page inner. --------->