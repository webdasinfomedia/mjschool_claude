<?php
/**
 * Admin Attendance Management index page.
 *
 * This file handles the backend logic for managing student and teacher attendance.
 * It includes functionality for saving, listing, and exporting attendance records,
 * as well as sending notifications (email/SMS) to parents when students are absent.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript.. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_view = '1';
} else {
	$user_access      = mjschool_get_user_role_wise_filter_access_right_array( 'attendance' );
	$user_access_view = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
	}
}
?>
<?php
$mjschool_obj_attend = new Mjschool_Attendence_Manage();
$class_id            = 0;
$current_date        = date( 'y-m-d' );
$active_tab          = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'student_attendance';
if ( $active_tab === 'teacher_attendance' ) {
	$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'teacher_attendences_list';
}
if ( $active_tab === 'student_attendance' ) {
	$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'attendence_list';
}
$MailCon = get_option( 'mjschool_absent_mail_notification_content' );
$Mailsub = get_option( 'mjschool_absent_mail_notification_subject' );
/* Save Attendance. */
if ( isset( $_REQUEST['save_attendence'] ) ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'mjschool_save_attendance_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	$class_id  = sanitize_text_field(wp_unslash($_POST['class_id']));
	$attend_by = get_current_user_id();
	$nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
	if ( isset( $_POST['class_section']) && intval( wp_unslash( $_POST['class_section'] ) ) != 0)
	{
		$students = mjschool_get_student_name_with_class_and_section($class_id, intval( wp_unslash( $_POST['class_section'] ) ));
	} else {
		$students = mjschool_get_student_name_with_class($class_id);
	}
	
	$parent_list = array();
	foreach ( $students as $stud ) {
		if ( isset( $_POST[ 'attendanace_' . $stud->ID ] ) ) {
			if ( isset( $_POST['mjschool_service_enable'] ) || isset( $_POST['mjschool_mail_service_enable'] ) ) {
				$current_mjschool_service = get_option( 'mjschool_service' );
				if ( sanitize_text_field(wp_unslash($_POST[ 'attendanace_' . $stud->ID ])) === 'Absent' ) {
					$parent_list = mjschool_get_student_parent_id( $stud->ID );
					if ( ! empty( $parent_list ) ) {
						// SEND SMS NOTIFICATION.
						if ( isset( $_POST['mjschool_service_enable'] ) ) {
							foreach ( $parent_list as $user_id ) {
								$message_content = 'Your Child ' . mjschool_get_user_name_by_id( $stud->ID ) . ' is absent on ' . sanitize_text_field(wp_unslash($_POST['curr_date']));
								$type            = 'Attendance';
								mjschool_send_mjschool_notification( $user_id, $type, $message_content );
							}
						}
						if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
							if ( ! empty( $parent_list ) ) {
								foreach ( $parent_list as $parent_user_id ) {
									$parent_data = get_userdata( $parent_user_id );
									if ( $parent_data === true ) {
										$MailArr['{{parent_name}}'] = mjschool_get_display_name( $parent_user_id );
										$MailArr['{{child_name}}']  = mjschool_get_display_name( $stud->ID );
										$MailArr['{{school_name}}'] = get_option( 'mjschool_name' );
										$Mail_content               = mjschool_string_replacement( $MailArr, $MailCon );
										$subject                    = mjschool_string_replacement( $MailArr, $Mailsub );
										$attendance_mail            = mjschool_send_mail( $parent_data->user_email, $subject, $Mail_content );
									}
								}
							}
						}
					}
				}
			}
			$attendence_type = 'Web';
			$savedata        = $mjschool_obj_attend->mjschool_insert_student_attendance( sanitize_text_field(wp_unslash($_POST['curr_date'])), $class_id, $stud->ID, $attend_by, sanitize_text_field(wp_unslash($_POST[ 'attendanace_' . $stud->ID ])), sanitize_text_field(wp_unslash($_POST[ 'attendanace_comment_' . $stud->ID ])), $attendence_type );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&_wpnonce=' . $nonce . '&message=1' ) );
	die();
}
/* Subject Wise Attendance. */
if ( isset( $_REQUEST['save_sub_attendence'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_subject_attendance_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
	$class_id    = sanitize_text_field(wp_unslash($_POST['class_id']));
	$parent_list = mjschool_get_user_notice( 'parent', $class_id );
	$attend_by   = get_current_user_id();
	$students = mjschool_get_student_name_with_class($class_id);
	
	foreach ( $students as $stud ) {
		if ( isset( $_POST[ 'attendanace_' . $stud->ID ] ) ) {
			if ( isset( $_POST['mjschool_service_enable'] ) || isset( $_POST['mjschool_subject_mail_service_enable'] ) ) {
				$current_mjschool_service = get_option( 'mjschool_service' );
				if ( sanitize_text_field(wp_unslash($_POST[ 'attendanace_' . $stud->ID ])) === 'Absent' ) {
					$parent_list = mjschool_get_student_parent_id( $stud->ID );
					if ( ! empty( $parent_list ) ) {
						foreach ( $parent_list as $user_id ) {
							$parent_data = get_userdata( $user_id );
							if ( isset( $_POST['mjschool_service_enable'] ) ) {
								$SMSCon                     = get_option( 'mjschool_attendance_mjschool_content' );
								$SMSArr['{{parent_name}}']  = $parent_data->display_name;
								$SMSArr['{{student_name}}'] = mjschool_get_display_name( $stud->ID );
								$SMSArr['{{current_date}}'] = sanitize_text_field(wp_unslash($_POST['curr_date']));
								$SMSArr['{{school_name}}']  = get_option( 'mjschool_name' );
								$message_content            = mjschool_string_replacement( $SMSArr, $SMSCon );
								$type                       = 'Attendance';
								mjschool_send_mjschool_notification( $user_id, $type, $message_content );
							}
							if ( isset( $_POST['mjschool_subject_mail_service_enable'] ) ) {
								$MailArr['{{child_name}}']  = mjschool_get_display_name( $stud->ID );
								$MailArr['{{school_name}}'] = get_option( 'mjschool_name' );
								$Mail                       = mjschool_string_replacement( $MailArr, $MailCon );
								$MailSub                    = mjschool_string_replacement( $MailArr, $Mailsub );
								mjschool_send_mail( $parent_data->user_email, $MailSub, $Mail );
							}
						}
					}
				}
			}
			$savedata = $mjschool_obj_attend->mjschool_insert_subject_wise_attendance( sanitize_text_field(wp_unslash($_POST['curr_date'])), $class_id, $stud->ID, $attend_by, sanitize_text_field(wp_unslash($_POST[ 'attendanace_' . $stud->ID ])), sanitize_text_field(wp_unslash($_POST['sub_id'])), sanitize_text_field(wp_unslash($_POST[ 'attendanace_comment_' . $stud->ID ])), 'Web', sanitize_text_field(wp_unslash($_POST['class_section'])) );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&_wpnonce=' . $nonce . '&message=1' ) );
	die();
}
/* Teacher attendence. */
if ( isset( $_REQUEST['save_teach_attendence'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_save_teacher_attendance_form')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$attend_by = get_current_user_id();
	$teacher   = get_users( array( 'role' => 'teacher' ) );
	$nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );
	foreach ( $teacher as $stud ) {
		if ( isset( $_POST[ 'attendanace_' . $stud->ID ] ) ) {
			$savedata = $mjschool_obj_attend->mjschool_insert_teacher_attendance( sanitize_text_field(wp_unslash($_POST['tcurr_date'])), $stud->ID, $attend_by, sanitize_text_field(wp_unslash($_POST[ 'attendanace_' . $stud->ID ])), sanitize_text_field(wp_unslash($_POST[ 'attendanace_comment_' . $stud->ID ])) );
		}
	}
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&_wpnonce=' . $nonce . '&message=1' ) );
	die();
}
/* Export Teacher Attendance. */
if ( isset( $_POST['export_attendance_in_csv'] ) ) {
	if ( empty( sanitize_text_field( wp_unslash( $_POST['filtered_date_type'] ) ) ) && empty( sanitize_text_field( wp_unslash( $_POST['filtered_class_id'] ) ) ) ) {
		$class_id                = '';
		$date_type               = '';
		$start_date              = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date                = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$student_attendance_list = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
	} else {
		$date_type               = sanitize_text_field(wp_unslash($_POST['filtered_date_type']));
		$class_id                = sanitize_text_field(wp_unslash($_REQUEST['filtered_class_id']));
		$student_attendance_list = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
	}
	if ( ! empty( $student_attendance_list ) ) {

		
		$header   = array();
		$header[] = 'Roll No';
		$header[] = 'Student Name';
		$header[] = 'Student Email';
		$header[] = 'Class Name';
		$header[] = 'Section Name';
		$header[] = 'Subject Name';
		$header[] = 'Attend_by_name';
		$header[] = 'Attendence_date';
		$header[] = 'Status';
		$header[] = 'Comment';
		$filename = 'export/mjschool-export-attendance.csv';
		$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
		fputcsv( $fh, $header );
		$nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
		foreach ( $student_attendance_list as $retrive_data ) {
			if ( $retrive_data->role_name === 'student' ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->user_id );
				$roll_no   = get_user_meta( $retrive_data->user_id, 'roll_id', true );
				if ( ! empty( $roll_no ) ) {
					$roll_no = $roll_no;
				} else {
					$roll_no = '-';
				}
				$row[]     = $roll_no;
				$row[]     = $user_info->display_name;
				$row[]     = $user_info->user_email;
				$class_id  = $retrive_data->class_id;
				$classname = mjschool_get_class_name( $class_id );
				if ( ! empty( $classname ) ) {
					$classname = $classname;
				} else {
					$classname = '';
				}
				$row[]   = $classname;
				$section = mjschool_get_section_name( $retrive_data->section_id );
				if ( ! empty( $section ) ) {
					$section = $section;
				} else {
					$section = '';
				}
				$row[]   = $section;
				$subject = mjschool_get_single_subject_name( $retrive_data->sub_id );
				if ( ! empty( $subject ) ) {
					$subject = $subject;
				} else {
					$subject = '';
				}
				$row[]     = $subject;
				$attend_by = get_userdata( $retrive_data->attend_by );
				$row[]     = $attend_by->display_name;
				$row[]     = $retrive_data->attendance_date;
				$row[]     = $retrive_data->status;
				$row[]     = $retrive_data->comment;
				fputcsv( $fh, $row );
			}
		}
		fclose( $fh );
		// download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-attendance.csv'; // file location.
		$mime = 'text/plain';
		header( 'Content-Type:application/force-download' );
		header( 'Pragma: public' );       // required.
		header( 'Expires: 0' );           // no cache.
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
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&_wpnonce=' . $nonce . '&message=3' ) );
		die();
	}
}
/* Upload Student Attendance. */
if ( isset( $_REQUEST['upload_attendance_csv_file'] ) ) {
	if ( isset( $_FILES['csv_file'] ) ) {
		$errors     = array();
		$file_name  = sanitize_file_name( wp_unslash($_FILES['csv_file']['name']));
		$file_size  = intval($_FILES['csv_file']['size']);
		$file_tmp   = sanitize_file_name( wp_unslash($_FILES['csv_file']['tmp_name']));
		$file_type  = wp_check_filetype($_FILES['csv_file']['type']);
		$value      = explode( '.', $_FILES['csv_file']['name'] );
		$file_ext   = strtolower( array_pop( $value ) );
		$extensions = array( 'csv' );
		$nonce = wp_create_nonce( 'mjschool_student_attendance_tab' );
		$upload_dir = wp_upload_dir();
		if ( in_array( $file_ext, $extensions ) === false ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=import_attendence&_wpnonce=' . $nonce . '&message=5' ) );
			die();
			$errors[] = $err;
		}
		if ( $file_size > 2097152 ) {
			$errors[] = 'File size limit 2 MB';
		}
		if ( empty( $errors ) === true ) {
			$rows   = array_map( 'str_getcsv', file( $file_tmp ) );
			$header = array_map( 'strtolower', array_shift( $rows ) );
			$csv    = array();
			foreach ( $rows as $row ) {
				// Skip completely empty rows.
				if (empty($row) || count(array_filter($row ) ) === 0) {
					continue;
				}
				// Skip rows with wrong column count.
				if (count($header) !== count($row ) ) {
					error_log( "Skipping bad row at line " . ($index + 2 ) );
					continue;
				}
				$csv      = array_combine( $header, $row );
				$class_id = mjschool_get_class_id_by_name( $csv['class name'] );
				if ( ! empty( $csv['section name'] ) ) {
					$section_name = $csv['section name'];
					$section_data = mjschool_get_section_id_by_section_name( $section_name, $class_id );
					$section_id   = $section_data[0]->id;
				}
				$curr_date = date( 'Y-m-d', strtotime( $csv['attendence_date'] ) );
				$mjschool_user      = get_user_by( 'email', $csv['student email'] );
				$userId    = $mjschool_user->ID;
				$attend_by = 1;
				$status    = $csv['status'];
				$sub_name  = $csv['subject name'];
				if ( ! empty( $sub_name ) ) {
					$sub_id = mjschool_get_subject_id_by_subject_name( $sub_name, $class_id, $section_id );
				}
				$comment         = $csv['comment'];
				$attendence_type = 'Web';
				$savedata        = $mjschool_obj_attend->mjschool_insert_subject_wise_attendance( $curr_date, $class_id, $userId, $attend_by, $status, $sub_id, $comment, $attendence_type, $section_id );
				$success         = 1;
			}
		} else {
			foreach ( $errors as &$error ) {
				echo esc_html( $error );
			}
		}
		if ( isset( $success ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&_wpnonce=' . $nonce . '&message=4' ) );
			die();
		}
	}
}
/* Export Teacher Attendance. */
if ( isset( $_POST['export_teacher_attendance_in_csv'] ) ) {

	$nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );
	if ( empty( sanitize_text_field( wp_unslash( $_POST['filtered_date_type'] ) ) ) && empty( sanitize_text_field( wp_unslash( $_POST['filtered_member_id'] ) ) ) ) {
		$start_date              = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date                = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$date_type               = '';
		$member_id               = '';
		$type                    = 'teacher';
		$teacher_attendance_list = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
	} else {
		$result     = mjschool_all_date_type_value( sanitize_text_field(wp_unslash($_POST['filtered_date_type'])) );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
		if ( ! empty( $_POST['filtered_member_id'] ) && sanitize_text_field(wp_unslash($_POST['filtered_member_id'])) !== 'all_teacher' ) {
			$member_id               = sanitize_text_field(wp_unslash($_REQUEST['filtered_member_id']));
			$teacher_attendance_list = mjschool_get_member_attendence_beetween_satrt_date_to_enddate_for_admin( $start_date, $end_date, $member_id );
		} else {
			$type                    = 'teacher';
			$teacher_attendance_list = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
		}
	}
	if ( ! empty( $teacher_attendance_list ) ) {
		$header   = array();
		$header[] = 'Teacher Name';
		$header[] = 'User_id';
		$header[] = 'Attend_by_name';
		$header[] = 'Attend_by';
		$header[] = 'Attendence_date';
		$header[] = 'Status';
		$header[] = 'Role_name';
		$header[] = 'Comment';
		$filename = 'export/mjschool-export-teacher-attendance.csv';
		$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
		fputcsv( $fh, $header );
		foreach ( $teacher_attendance_list as $retrive_data ) {
			if ( $retrive_data->role_name === 'teacher' ) {
				$row       = array();
				$user_info = get_userdata( $retrive_data->user_id );
				$row[]     = $user_info->display_name;
				$row[]     = $retrive_data->user_id;
				$attend_by = get_userdata( $retrive_data->attend_by );
				$row[]     = $attend_by->display_name;
				$row[]     = $retrive_data->attend_by;
				$row[]     = $retrive_data->attendence_date;
				$row[]     = $retrive_data->status;
				$row[]     = $retrive_data->role_name;
				$row[]     = $retrive_data->comment;
				fputcsv( $fh, $row );
			}
		}
		fclose( $fh );
		// download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-teacher-attendance.csv'; // file location.
		$mime = 'text/plain';
		header( 'Content-Type:application/force-download' );
		header( 'Pragma: public' );       // required.
		header( 'Expires: 0' );           // no cache.
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
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&_wpnonce=' . rawurlencode( $nonce ) . '&message=3' ) );
		exit;
	}
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<?php
if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
	?>
	<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/TaO7Xh4SmXY?si=v4zQa-CmiEE0h151' ); ?>" title="<?php esc_attr_e( 'Student Attendance', 'mjschool' ); ?>">
		
		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_html_e( 'YouTube', 'mjschool' ); ?>">
		
	</a>
	<?php
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class=" attendance_list mjschool-main-list-margin-5px"> <!-- attendance_list. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Attendance saved successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Record Deleted Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Attendance records not found.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Attendance records imported successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'This file not allowed, please choose a CSV file.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_attr( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div>
			<div class="mjschool-panel-body"> <!-- mjschool-panel-body. -->
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per mb-4" role="tablist">
					<?php
					if ( $active_tab === 'teacher_attendance' ) {
						?>
						<?php $nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' ); ?>
						<li class="<?php if ( $active_tab1 === 'teacher_attendences_list' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_attendence&tab=teacher_attendance&tab1=teacher_attendences_list&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'teacher_attendences_list' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Teacher Attendance List', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'teacher_attendences' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_attendence&tab=teacher_attendance&tab1=teacher_attendences&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'teacher_attendences' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Teacher Attendance', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					if ( $active_tab === 'student_attendance' ) {
						?>
						<?php $nonce = wp_create_nonce( 'mjschool_student_attendance_tab' ); ?>
						<li class="<?php if ( $active_tab1 === 'attendence_list' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_attendence&tab=student_attendance&tab1=attendence_list&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendence_list' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Attendance List', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'subject_attendence' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_attendence&tab=student_attendance&tab1=subject_attendence&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'subject_attendence' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Attendance', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'attendence_with_qr' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( '?page=mjschool_attendence&tab=student_attendance&tab1=attendence_with_qr&_wpnonce=' . $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendence_with_qr' ? 'nav-tab-active' : ''; ?>">
								<?php echo esc_attr__( 'Attendance With QR Code', 'mjschool' ); ?>
							</a>
						</li>
						<?php
					}
					?>
				</ul>
				<?php
				// attendence list.
				if ( isset( $active_tab1 ) && $active_tab1 === 'attendence' ) {
					?>
					<div class="mjschool-panel-body">
						<form method="post" id="student_attendance">
							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="curr_date_for_index" class="form-control" type="text" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); }?>" name="curr_date" readonly> 
												<label  for="curr_date_for_index"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-3 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="class_id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<?php
										if ( isset( $_REQUEST['class_id'] ) ) {
											$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
										}
										?>
										<select name="class_id" id="mjschool-class-list" class="form-control validate[required]">
											<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="col-md-3 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="class_id"><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></label>
										<?php
										$class_section = '';
										if ( isset( $_REQUEST['class_section'] ) ) {
											$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
										}
										?>
										<select name="class_section" class="form-control" id="class_section">
											<option value=""><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></option>
											<?php
											if ( isset( $_REQUEST['class_section'] ) ) {
												$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
												foreach ( mjschool_get_class_sections( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) as $sectiondata ) {
													?>
													<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
													<?php
												}
											}
											?>
										</select>
									</div>
									<div class="col-md-3 mb-3">
										<input type="submit" value="<?php esc_html_e( 'Take Attendance', 'mjschool' ); ?>" name="attendence" class="mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="clearfix"> </div>
					<?php
					if ( isset( $_REQUEST['attendence'] ) || isset( $_REQUEST['save_attendence'] ) ) {
						$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
						
						$mjschool_user = count(get_users(array(
							'meta_key' => 'class_name',
							'meta_value' => $class_id
						 ) ) );
						
						$attendanace_date = sanitize_text_field(wp_unslash($_REQUEST['curr_date']));
						$holiday_dates    = mjschool_get_all_date_of_holidays();
						if ( in_array( $attendanace_date, $holiday_dates ) ) {
							?>
							<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
								<p><?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?></p>
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Please Select Class', 'mjschool' ); ?></span></button>
							</div>
							<?php
						} elseif ( 0 < $mjschool_user ) {
							if ( isset( $_REQUEST['class_id'] ) && sanitize_text_field(wp_unslash($_REQUEST['class_id'])) != '' ) {
								$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
							} else {
								$class_id = 0;
							}
							if ( $class_id === 0 ) {
								?>
								<div class="mjschool-panel-heading">
									<h4 class="mjschool-panel-title"><?php esc_html_e( 'Please Select Class', 'mjschool' ); ?></h4>
								</div>
								<?php
							} else {
								
								$class_section = 0;
								if ( isset( $_REQUEST['class_section']) && sanitize_text_field(wp_unslash($_REQUEST['class_section'])) != 0) {
									$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
									$exlude_id = mjschool_approve_student_list();
									$student = get_users(array( 'meta_key' => 'class_section', 'meta_value' => sanitize_text_field(wp_unslash($_REQUEST['class_section'])), 'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ), 'role' => 'student', 'exclude' => $exlude_id, 'orderby' => 'display_name', 'order' => 'ASC' ) );
				
								} else {
									$exlude_id = mjschool_approve_student_list();
									$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id, 'orderby' => 'display_name', 'order' => 'ASC' ) );
									
								}
								?>
								<div class="mjschool-panel-body">
									<form method="post" class="mjschool-form-horizontal">
										<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
										<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
										<input type="hidden" name="curr_date" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" />
										<div class="mjschool-panel-heading">
											<h4 class="mjschool-panel-title"> <?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_attr( mjschool_get_class_name( $class_id ) ); ?> , <?php esc_html_e( 'Date', 'mjschool' ); ?> : <?php echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); ?> </h4>
										</div>
										<div class="col-md-12 mjschool-padding-payment mjschool_att_tbl_list">
											<div class="table-responsive">
												<table class="table">
													<tr>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Srno', 'mjschool' ); ?></th>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
														<th class="mjschool-multiple-subject-mark"><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
													</tr>
													<?php
													$date = sanitize_text_field(wp_unslash($_POST['curr_date']));
													$i    = 1;
													foreach ( $student as $mjschool_user ) {
														$date             = sanitize_text_field(wp_unslash($_POST['curr_date']));
														$check_attendance = $mjschool_obj_attend->mjschool_check_attendence( $mjschool_user->ID, $class_id, $date );
														$attendanc_status = 'Present';
														if ( ! empty( $check_attendance ) ) {
															$attendanc_status = $check_attendance->status;
														}
														echo '<tr>';
														echo '<td>' . esc_html( $i ) . '</td>';
														echo '<td><span>' . esc_html( get_user_meta( $mjschool_user->ID, 'roll_id', true ) ) . '</span></td>';
														echo '<td><span>' . esc_html( $mjschool_user->first_name ) . ' ' . esc_html( $mjschool_user->last_name ) . '</span></td>';
														?>
														<td>
															<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>> <?php esc_html_e( 'Present', 'mjschool' ); ?></label>
															<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>> <?php esc_html_e( 'Absent', 'mjschool' ); ?></label>
															<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>> <?php esc_html_e( 'Late', 'mjschool' ); ?></label>
															<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>> <?php esc_html_e( 'Half Day', 'mjschool' ); ?></label>
														</td>
														<td class="padding_left_right_0">
															<div class="form-group input mjschool-margin-bottom-0px">
																<div class="col-md-12 form-control">
																	<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control" value="<?php if ( ! empty( $check_attendance ) ) { echo esc_html( $check_attendance->comment );} ?>">
																</div>
															</div>
														</td>
														<?php
														echo '</tr>';
														++$i;
													}
													?>
												</table>
											</div>
											<div class="d-flex mt-2">
												<div class="form-group row mb-3">
													<label class="col-sm-8 control-label " for="enable"><?php esc_html_e( 'If student absent then Send Email to his/her parents', 'mjschool' ); ?></label>
													<div class="col-sm-2 ps-0">
														<div class="mjschool-checkbox">
															<label>
																<input class="mjschool_check_box" id="mjschool_mail_service_enable" type="checkbox"  <?php $mjschool_mail_service_enable = 0; if ( $mjschool_mail_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_mail_service_enable">
															</label>
														</div>
													</div>
												</div>
												<div class="form-group row mb-3">
													<label class="col-sm-10 control-label" for="enable"><?php esc_html_e( 'If student absent then Send  SMS to his/her parents', 'mjschool' ); ?></label>
													<div class="col-sm-2 ps-0">
														<div class="mjschool-checkbox">
															<label>
																<input class="mjschool_check_box" id="chk_mjschool_sent1" type="checkbox" <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_service_enable">
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-md-6 mjschool-rtl-res-att-save">
											<input type="submit" value="<?php esc_html_e( 'Save  Attendance', 'mjschool' ); ?>" name="save_attendence" class="col-sm-6 mjschool-save-btn" />
										</div>
									</form>
								</div>
								<?php
							}
						} else {
							?>
							<div>
								<h4 class="mjschool-panel-title"><?php esc_html_e( 'No Any Student In This Class', 'mjschool' ); ?></h4>
							</div>
							<?php
						}
					}
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'attendence_list' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/attendence/student-attendence-list.php';
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'teacher_attendences_list' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/attendence/teacher-attendences-list.php';
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'teacher_attendences' ) {
					?>
					<form method="post" id="teacher_attendance">
						<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_teacher_attendance_take_nonce' ) ); ?>">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-5 col-md-5 col-lg-5 col-xl-5">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="curr_date_teacher" class="form-control" type="text" value="<?php if ( isset( $_POST['tcurr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['tcurr_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="tcurr_date" readonly>
											<label  for="curr_date_teacher"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<input type="submit" value="<?php esc_html_e( 'Take Attendance', 'mjschool' ); ?>" name="teacher_attendence" class="mjschool-save-btn" />
								</div>
							</div>
						</div>
					</form>
					<?php
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'export_teacher_attendences' ) {
					?>
					<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
						<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
							<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
							<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-6 mjschool-error-msg-left-margin input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Teacher', 'mjschool' ); ?><span class="required">*</span></label>
										<?php
										if ( isset( $_POST['teacher_name'] ) ) {
											$workrval = sanitize_text_field(wp_unslash($_POST['teacher_name']));
										} else {
											$workrval = '';
										}
										?>
										<select name="teacher_name" class="form-control validate[required] mjschool-width-100px class_by_teacher" id="teacher_name">
											<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
											<?php
											$teacherdata_array = mjschool_get_users_data( 'teacher' );
											foreach ( $teacherdata_array as $techer_data ) {
												?>
												<option value="<?php echo esc_attr( $techer_data->ID ); ?>" <?php selected( $techer_data->ID ); ?>>
													<?php echo esc_html( $techer_data->display_name ); ?>
												</option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="col-sm-3">
										<input type="submit" value="<?php esc_html_e( 'Export Teacher Attendance', 'mjschool' ); ?>" name="export_teacher_attendance_in_csv" class="mjschool-save-attr-btn" />
									</div>
								</div>
							</div>
						</form>
					</div>
					<?php
				}
				?>
				<div class="clearfix"> </div>
				<?php
				if ( isset( $_REQUEST['teacher_attendence'] )) {
					if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_teacher_attendance_take_nonce')) {
						wp_die(esc_html__('Security check failed.', 'mjschool'));
					}
					$attendanace_date = sanitize_text_field(wp_unslash($_REQUEST['tcurr_date']));
					$holiday_dates    = mjschool_get_all_date_of_holidays();
					if ( in_array( $attendanace_date, $holiday_dates ) ) {
						?>
						<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
							<p><?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?></p>
							<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-panel-body"> <!-- mjschool-panel-body. -->
							<form method="post">
								<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_save_teacher_attendance_form' ) ); ?>">
								<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
								<input type="hidden" name="tcurr_date" value="<?php echo esc_attr( sanitize_text_field(wp_unslash($_POST['tcurr_date'])) ); ?>" />
								<div class="mjschool-panel-heading">
									<h4 class="mjschool-panel-title"><?php esc_html_e( 'Teacher Attendance', 'mjschool' ); ?> ,<?php esc_html_e( 'Date', 'mjschool' ); ?> : <?php echo esc_attr( sanitize_text_field(wp_unslash($_POST['tcurr_date'])) ); ?> </h4>
								</div>
								<div class="col-md-12 mjschool-padding-payment mjschool_att_tbl_list">
									<div class="table-responsive">
										<table class="table">
											<tr>
												<th class="mjchool_margin_none" ><?php esc_html_e( 'Srno', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Teacher', 'mjschool' ); ?></th>
												<th class="mjschool_widht_250px" ><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
											</tr>
											<?php
											$date    = sanitize_text_field(wp_unslash($_POST['tcurr_date']));
											$i       = 1;
											$teacher = get_users( array( 'role' => 'teacher' ) );
											foreach ( $teacher as $mjschool_user ) {
												$class_id         = 0;
												$check_attendance = $mjschool_obj_attend->mjschool_check_attendence( $mjschool_user->ID, $class_id, $date );
												$attendanc_status = 'Present';
												if ( ! empty( $check_attendance ) ) {
													$attendanc_status = $check_attendance->status;
												}
												echo '<tr>';
												echo '<tr>';
												echo '<td>' . esc_html( $i ) . '</td>';
												echo '<td class="mjschool_padding_left_0px"><span>' . esc_html( $mjschool_user->first_name ) . ' ' . esc_html( $mjschool_user->last_name ) . '</span></td>';
												?>
												<td class="mjschool_padding_left_0px">
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>><?php esc_html_e( 'Present', 'mjschool' ); ?></label>
													<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>><?php esc_html_e( 'Absent', 'mjschool' ); ?></label><br>
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>><?php esc_html_e( 'Late', 'mjschool' ); ?></label>
													<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>><?php esc_html_e( 'Half Day', 'mjschool' ); ?></label>
												</td>
												<td >
													<div class="form-group input mjschool-margin-bottom-0px">
														<div class="col-md-12 form-control">
															<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control" value="<?php if ( ! empty( $check_attendance ) ) { echo esc_attr( $check_attendance->comment );} ?>">
														</div>
													</div>
												</td>
												<?php
												echo '</tr>';
												++$i;
											}
											?>
										</table>
									</div>
								</div>
								<div class="cleatrfix"></div>
								<div class="col-sm-12 padding_top_10px mjschool-rtl-res-att-save">
									<input type="submit" value="<?php esc_html_e( 'Save Attendance', 'mjschool' ); ?>" name="save_teach_attendence" id="mjschool-res-rtl-width-100px mjschool-res-rtl-width-100px" class="col-sm-6 mjschool-save-attr-btn " />
								</div>
							</form>
						</div><!-- mjschool-panel-body. -->
						<?php
					}
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'subject_attendence' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/attendence/subject-attendence.php';
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'import_attendence' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/attendence/import-attendence.php';
				}
				if ( isset( $active_tab1 ) && $active_tab1 === 'attendence_with_qr' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/attendence/attendence-qr.php';
				}
				?>
			</div><!-- mjschool-panel-body. -->
		</div>
	</div><!-- attendance_list. -->
</div><!-- mjschool-page-inner. -->