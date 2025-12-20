<?php
/**
 * Admin Dashboard.
 *
 * This file manages and displays the main admin dashboard interface of the MJSchool plugin.
 * It aggregates and presents real-time data including student attendance, events, notices,
 * fees, and other school statistics to administrators and authorized users. The dashboard
 * serves as a centralized overview for school operations and integrates multiple modules.
 *
 * Key Features:
 * - Displays summarized information such as total students, teachers, and classes.
 * - Integrates attendance statistics with graphical representation.
 * - Fetches and lists school notices, upcoming events, and assignments.
 * - Provides quick access links to major modules (e.g., Students, Teachers, Fees, Exams).
 * - Supports WordPress roles and capabilities for secure data access.
 * - Uses AJAX for real-time updates and dynamic content loading.
 * - Ensures data sanitization, escaping, and nonce validation for security.
 * - Fully compatible with WordPress coding standards and translation functions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// This is the dashboard on the admin side.
$obj_attend             = new Mjschool_Attendence_Manage();
$obj_event              = new Mjschool_Event_Manage();;
$all_notice             = '';
$args['post_type']      = 'notice';
$args['posts_per_page'] = -1;
$args['post_status']    = 'public';
$q                      = new WP_Query();
$all_notice             = $q->query( $args );
$notive_array           = array();
$request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
$request_invoice_type = isset( $_REQUEST['invoice_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['invoice_type'] ) ) : '';
if ( ! empty( $all_notice ) ) {
	foreach ( $all_notice as $notice ) {
		$notice_start_date = get_post_meta( $notice->ID, 'start_date', true );
		$notice_end_date   = get_post_meta( $notice->ID, 'end_date', true );
		$notice_comment    = $notice->post_content;
		if ( ! empty( $notice->post_content ) ) {
			$notice_comment = $notice->post_content;
		} else {
			$notice_comment = 'N/A';
		}
		$start_date = $notice->start_date;
		$end_date   = $notice->end_date;
		$notice_for = ucfirst( get_post_meta( $notice->ID, 'notice_for', true ) );
		$i          = 1;
		if ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' && get_post_meta( $notice->ID, 'smgt_class_id', true ) === 'all' ) {
			$class_name = esc_html__( 'All', 'mjschool' );
		} elseif ( get_post_meta( $notice->ID, 'smgt_class_id', true ) != '' ) {
			$class_name = mjschool_get_class_name( get_post_meta( $notice->ID, 'smgt_class_id', true ) );
		} else {
			$class_name = '';
		}
		$to                = esc_html__( 'To', 'mjschool' );
		$start_to_end_date = mjschool_get_date_in_input_box( $start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $end_date );
		$notice_title      = $notice->post_title;
		$notive_array[]    = array(
			'event_title'       => esc_html__( 'Notice Details', 'mjschool' ),
			'notice_title'      => $notice_title,
			'title'             => $notice->post_title,
			'description'       => 'notice',
			'notice_comment'    => $notice_comment,
			'notice_for'        => $notice_for,
			'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
			'class_name'        => $class_name,
			'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
			'color'             => '#ffd000',
			'start_to_end_date' => $start_to_end_date,
		);
	}
}
$holiday_list = mjschool_get_all_data( 'mjschool_holiday' );
if ( ! empty( $holiday_list ) ) {
	foreach ( $holiday_list as $holiday ) {
		if ( $holiday->status === 0 ) {
			$notice_start_date = $holiday->date;
			$notice_end_date   = $holiday->end_date;
			$i                 = 1;
			$holiday_title     = $holiday->holiday_title;
			$holiday_comment   = $holiday->description;
			if ( ! empty( $holiday->description ) ) {
				$holiday_comment = $holiday->description;
			} else {
				$holiday_comment = 'N/A';
			}
			$to                = esc_html__( 'To', 'mjschool' );
			$start_to_end_date = mjschool_get_date_in_input_box( $notice_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $notice_end_date );
			$notive_array[]    = array(
				'event_title'       => esc_html__( 'Holiday Details', 'mjschool' ),
				'title'             => $holiday->holiday_title,
				'description'       => 'holiday',
				'start'             => mysql2date( 'Y-m-d', $notice_start_date ),
				'end'               => date( 'Y-m-d', strtotime( $notice_end_date . ' +' . $i . ' days' ) ),
				'color'             => '#3c8dbc',
				'holiday_title'     => $holiday_title,
				'holiday_comment'   => $holiday_comment,
				'start_to_end_date' => $start_to_end_date,
				'status'            => esc_html__( 'Approve', 'mjschool' ),
			);
		}
	}
}
// ----------- Event for calendar. -------------//
$event_list = mjschool_get_all_data( 'mjschool_event' );
if ( ! empty( $event_list ) ) {
	foreach ( $event_list as $event ) {
		$event_start_date = $event->start_date;
		$event_end_date   = $event->end_date;
		$i                = 1;
		$notive_array[]   = array(
			'event_title'      => esc_html__( 'Event Details', 'mjschool' ),
			'title'            => $event->event_title,
			'description'      => 'event',
			'start'            => mysql2date( 'Y-m-d', $event_start_date ),
			'end'              => date( 'Y-m-d', strtotime( $event_end_date . ' +' . $i . ' days' ) ),
			'color'            => '#36A8EB',
			'event_heading'    => $event->event_title,
			'event_comment'    => $event->description,
			'event_start_time' => mjschool_time_remove_colon_before_am_pm( $event->start_time ),
			'event_end_time'   => mjschool_time_remove_colon_before_am_pm( $event->end_time ),
			'event_start_date' => $event->start_date,
			'event_end_date'   => $event->end_date,
		);
	}
}
$exam_list = mjschool_get_all_data( 'mjschool_exam' );
if ( ! empty( $exam_list ) ) {
	foreach ( $exam_list as $exam ) {
		$exam_start_date = mjschool_get_date_in_input_box( $exam->exam_start_date );
		$exam_end_date   = mjschool_get_date_in_input_box( $exam->exam_end_date );
		$i               = 1;
		$exam_title      = $exam->exam_name;
		$exam_term       = get_the_title( $exam->exam_term );
		if ( ! empty( $exam->section_id ) ) {
			$section_name = mjschool_get_section_name( $exam->section_id );
		} else {
			$section_name = 'N/A';
		}
		$class_name = mjschool_get_class_section_name_wise( $exam->class_id, $exam->section_id );
		if ( ! empty( $exam->exam_comment ) ) {
			$comment = $exam->exam_comment;
		} else {
			$comment = 'N/A';
		}
		$to                = esc_html__( 'To', 'mjschool' );
		$start_to_end_date = mjschool_get_date_in_input_box( $exam_start_date ) . ' ' . $to . ' ' . mjschool_get_date_in_input_box( $exam_end_date );
		$total_mark        = $exam->total_mark;
		$passing_mark      = $exam->passing_mark;
		$notive_array[]    = array(
			'exam_title'   => $exam_title,
			'exam_term'    => $exam_term,
			'class_name'   => $class_name,
			'total_mark'   => $total_mark,
			'passing_mark' => $passing_mark,
			'comment'      => $comment,
			'start_date'   => $start_to_end_date,
			'event_title'  => esc_html__( 'Exam Details', 'mjschool' ),
			'title'        => $exam->exam_name,
			'description'  => 'exam',
			'start'        => mysql2date( 'Y-m-d', $exam_start_date ),
			'end'          => date( 'Y-m-d', strtotime( $exam_end_date . ' +' . $i . ' days' ) ),
			'color'        => '#5840bb',
		);
	}
}
?>

<!--------------- Notice calendar popup. ---------------->
<div id="mjschool-event-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
	<div class="penal-body">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span ><br>
				<span class="mjschool-label-value" id="notice_title"></span >
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="start_to_end_date"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Notice For', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="notice_for"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="class_name_111"></span>
			</div>
			<div class="col-md-12 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value " id="discription"> </span>
			</div>
		</div>
	</div>
</div>
<!--------------- Holiday calendar popup. ---------------->
<div id="mjschool-holiday-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
	<div class="penal-body">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="holiday_title"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date To End Date', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="start_to_end_date"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Status', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value mjschool_green_color" id="status" ></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="holiday_comment"></span>
			</div>
		</div>
	</div>
</div>
<!--------------- Exam calendar popup. ---------------->
<div id="mjschool-exam-booked-popup" class="modal-body mjchool_display_none" ><!--Modal body div start.-->
	<div class="penal-body">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="exam_title"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Term', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="exam_term"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Class', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="class_name_123"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start To End Date', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="start_date"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Total Marks', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="total_mark"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Passing Marks', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="passing_mark"></span>
			</div>
			<div class="col-md-12 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="comment"></span>
			</div>
		</div>
	</div>
</div>
<!--------------- Event calendar popup. ---------------->
<div id="mjschool-event-list-booked-popup" class="modal-body mjchool_display_none"><!--Modal body div start.-->
	<div class="penal-body">
		<div class="row">
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Title', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_heading"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_start_date_calender"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'End Date', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_end_date_calender"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Start Time', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_start_time_calender"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'End Time', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_end_time_calender"></span>
			</div>
			<div class="col-md-6 mjschool-popup-padding-15px">
				<span class="mjschool-popup-label-heading"><?php esc_html_e( 'Description', 'mjschool' ); ?></span><br>
				<span class="mjschool-label-value" id="event_comment_calender"></span>
			</div>
		</div>
	</div>
</div>
<!DOCTYPE html>
<html lang="en"><!-- Html start. -->
	<head>
	</head>
	<?php
	if ( $request_page === 'mjschool' ) {
		?>
		<div id="mjschool_calendar_trigger" data-language="<?php echo esc_attr( mjschool_calender_laungage() ); ?>" data-events="<?php echo esc_attr( wp_json_encode( $notive_array ) ); ?>"></div>
		<?php
	}
	?>
	<!-- Body part start.  -->
	<body>
		<!--Task-event POPUP code. -->
		<?php
		if ( is_rtl() ) {
			$rtl_left_icon_class_css = 'fa-chevron-left';
		} else {
			$rtl_left_icon_class_css = 'fa-chevron-right';
		}
		$mjschool_role = mjschool_get_user_role( get_current_user_id() );
		if ( $mjschool_role === 'management' ) {
			$admission_page             = 'admission';
			$admission_access           = mjschool_get_user_role_wise_filter_access_right_array( $admission_page );
			$student_page               = 'student';
			$student_access             = mjschool_get_user_role_wise_filter_access_right_array( $student_page );
			$teacher_page               = 'teacher';
			$teacher_access             = mjschool_get_user_role_wise_filter_access_right_array( $teacher_page );
			$supportstaff_page          = 'supportstaff';
			$supportstaff_access        = mjschool_get_user_role_wise_filter_access_right_array( $supportstaff_page );
			$parent_page                = 'parent';
			$parent_access              = mjschool_get_user_role_wise_filter_access_right_array( $parent_page );
			$class_page                 = 'class';
			$class_access               = mjschool_get_user_role_wise_filter_access_right_array( $class_page );
			$schedule_page              = 'schedule';
			$schedule_access            = mjschool_get_user_role_wise_filter_access_right_array( $schedule_page );
			$virtual_classroom_page     = 'virtual_classroom';
			$virtual_classroom_access   = mjschool_get_user_role_wise_filter_access_right_array( $virtual_classroom_page );
			$subject_page               = 'subject';
			$subject_access             = mjschool_get_user_role_wise_filter_access_right_array( $subject_page );
			$exam_page                  = 'exam';
			$exam_access                = mjschool_get_user_role_wise_filter_access_right_array( $exam_page );
			$class_room_page 		    = 'class_room';
			$class_room_access		    = mjschool_get_user_role_wise_filter_access_right_array( $class_room_page );
			$exam_hall_page             = 'exam_hall';
			$exam_hall_access           = mjschool_get_user_role_wise_filter_access_right_array( $exam_hall_page );
			$manage_marks_page          = 'manage_marks';
			$manage_marks_access        = mjschool_get_user_role_wise_filter_access_right_array( $manage_marks_page );
			$mjschool_grade_page        = 'grade';
			$grade_access               = mjschool_get_user_role_wise_filter_access_right_array( $mjschool_grade_page );
			$homework_page              = 'homework';
			$homework_access            = mjschool_get_user_role_wise_filter_access_right_array( $homework_page );
			$attendance_page            = 'attendance';
			$attendance_access          = mjschool_get_user_role_wise_filter_access_right_array( $attendance_page );
			$document_page              = 'document';
			$document_access            = mjschool_get_user_role_wise_filter_access_right_array( $document_page );
			$tax_page                   = 'tax';
			$tax_access                 = mjschool_get_user_role_wise_filter_access_right_array( $tax_page );
			$feepayment_page            = 'feepayment';
			$feepayment_access          = mjschool_get_user_role_wise_filter_access_right_array( $feepayment_page );
			$payment_page               = 'payment';
			$payment_access             = mjschool_get_user_role_wise_filter_access_right_array( $payment_page );
			$library_page               = 'library';
			$library_access             = mjschool_get_user_role_wise_filter_access_right_array( $library_page );
			$hostel_page                = 'hostel';
			$hostel_access              = mjschool_get_user_role_wise_filter_access_right_array( $hostel_page );
			$mjschool_access_right      = 'access_right';
			$access_right_access        = mjschool_get_user_role_wise_filter_access_right_array( $mjschool_access_right );
			$leave_page                 = 'leave';
			$leave_access               = mjschool_get_user_role_wise_filter_access_right_array( $leave_page );
			$transport_page             = 'transport';
			$transport_access           = mjschool_get_user_role_wise_filter_access_right_array( $transport_page );
			$certificate_page           = 'certificate';
			$certificate_access         = mjschool_get_user_role_wise_filter_access_right_array( $certificate_page );
			$report_page                = 'report';
			$report_access              = mjschool_get_user_role_wise_filter_access_right_array( $report_page );
			$advance_report             = 'advance_report';
			$advance_report_access      = mjschool_get_user_role_wise_filter_access_right_array( $advance_report );
			$notice_page                = 'notice';
			$notice_access              = mjschool_get_user_role_wise_filter_access_right_array( $notice_page);
			$message_page               = 'message';
			$message_access             = mjschool_get_user_role_wise_filter_access_right_array( $message_page );
			$holiday_page               = 'holiday';
			$holiday_access             = mjschool_get_user_role_wise_filter_access_right_array( $holiday_page );
			$notification_page          = 'notification';
			$notification_access        = mjschool_get_user_role_wise_filter_access_right_array( $notification_page );
			$event_page                 = 'event';
			$event_access               = mjschool_get_user_role_wise_filter_access_right_array( $event_page );
			$custom_field_page          = 'custom_field';
			$custom_field_access        = mjschool_get_user_role_wise_filter_access_right_array( $custom_field_page );
			$sms_setting_page           = 'sms_setting';
			$sms_setting_access         = mjschool_get_user_role_wise_filter_access_right_array( $sms_setting_page );
			$general_settings_page      = 'general_settings';
			$general_settings_access    = mjschool_get_user_role_wise_filter_access_right_array( $general_settings_page );
			$email_template_page        = 'email_template';
			$email_template_access      = mjschool_get_user_role_wise_filter_access_right_array( $email_template_page );
			$mjschool_template_page     = 'mjschool_template';
			$mjschool_template_access   = mjschool_get_user_role_wise_filter_access_right_array( $mjschool_template_page );
			$migration_page             = 'migration';
			$migration_access           = mjschool_get_user_role_wise_filter_access_right_array( $migration_page );
			$student_view_access        = $student_access['view'];
			$student_add_access         = $student_access['add'];
			$admission_view_access      = $admission_access['view'];
			$admission_add_access       = $admission_access['add'];
			$staff_view_access          = $supportstaff_access['view'];
			$staff_add_access           = $supportstaff_access['add'];
			$teacher_view_access        = $teacher_access['view'];
			$teacher_add_access         = $teacher_access['add'];
			$parent_view_access         = $parent_access['view'];
			$parent_add_access          = $parent_access['add'];
			$exam_view_access           = $exam_access['view'];
			$exam_add_access            = $exam_access['add'];
			$hall_view_access           = $exam_hall_access['view'];
			$hall_add_access            = $exam_hall_access['add'];
			$mark_view_access           = $manage_marks_access['view'];
			$mark_add_access            = $manage_marks_access['add'];
			$grade_view_access          = $grade_access['view'];
			$grade_add_access           = $grade_access['add'];
			$homework_view_access       = $homework_access['view'];
			$homework_add_access        = $homework_access['add'];
			$attendance_view_access     = $attendance_access['view'];
			$attendance_add_access      = $attendance_access['add'];
			$document_view_access       = $document_access['view'];
			$document_add_access        = $document_access['add'];
			$fees_view_access           = $feepayment_access['view'];
			$fees_add_access            = $feepayment_access['add'];
			$tax_view_access            = $tax_access['view'];
			$tax_add_access             = $tax_access['add'];
			$payment_view_access        = $payment_access['view'];
			$payment_add_access         = $payment_access['add'];
			$library_view_access        = $library_access['view'];
			$library_add_access         = $library_access['add'];
			$access_right_view_access   = $access_right_access['view'];
			$access_right_add_access    = $access_right_access['add'];
			$leave_view_access          = $leave_access['view'];
			$leave_add_access           = $leave_access['add'];
			$hostel_view_access         = $hostel_access['view'];
			$hostel_add_access          = $hostel_access['add'];
			$certificate_view_access    = $certificate_access['view'];
			$certificate_add_access     = $certificate_access['add'];
			$transport_view_access      = $transport_access['view'];
			$transport_add_access       = $transport_access['add'];
			$report_view_access         = $report_access['view'];
			$report_add_access          = $report_access['add'];
			$advance_report_view_access = $advance_report_access['view'];
			$notice_view_access            = $notice_access['view'];
			$notice_add_access             = $notice_access['add'];
			$message_view_access           = $message_access['view'];
			$message_add_access            = $message_access['add'];
			$holiday_view_access           = $holiday_access['view'];
			$holiday_add_access            = $holiday_access['add'];
			$notification_view_access      = $notification_access['view'];
			$notification_add_access       = $notification_access['add'];
			$event_view_access             = $event_access['view'];
			$event_add_access              = $event_access['add'];
			$field_view_access             = $custom_field_access['view'];
			$field_add_access              = $custom_field_access['add'];
			$sms_view_access               = $sms_setting_access['view'];
			$sms_add_access                = $sms_setting_access['add'];
			$mail_view_access              = $email_template_access['view'];
			$mail_add_access               = $email_template_access['add'];
			$mjschool_template_view_access = $mjschool_template_access['view'];
			$mjschool_template_add_access  = $mjschool_template_access['add'];
			$class_view_access             = $class_access['view'];
			$class_add_access              = $class_access['add'];
			$schedule_view_access          = $schedule_access['view'];
			$schedule_add_access           = $schedule_access['add'];
			$virtual_class_view_access     = $virtual_classroom_access['view'];
			$virtual_class_add_access      = $virtual_classroom_access['add'];
			$subject_view_access           = $subject_access['view'];
			$subject_add_access            = $subject_access['add'];
			$class_room_view_access 	   = $class_room_access['view'];
			$class_room_add_access 		   = $class_room_access['add'];
			$migration_view_access         = $migration_access['view'];
			$migration_add_access          = $migration_access['add'];
		} else {
			$student_view_access           = '1';
			$student_add_access            = '1';
			$admission_view_access         = '1';
			$admission_add_access          = '1';
			$staff_view_access             = '1';
			$staff_add_access              = '1';
			$teacher_view_access           = '1';
			$teacher_add_access            = '1';
			$parent_view_access            = '1';
			$parent_add_access             = '1';
			$exam_view_access              = '1';
			$exam_add_access               = '1';
			$hall_view_access              = '1';
			$hall_add_access               = '1';
			$mark_view_access              = '1';
			$mark_add_access               = '1';
			$grade_view_access             = '1';
			$grade_add_access              = '1';
			$homework_view_access          = '1';
			$homework_add_access           = '1';
			$attendance_view_access        = '1';
			$attendance_add_access         = '1';
			$document_view_access          = '1';
			$document_add_access           = '1';
			$fees_view_access              = '1';
			$fees_add_access               = '1';
			$tax_view_access               = '1';
			$tax_add_access                = '1';
			$payment_view_access           = '1';
			$payment_add_access            = '1';
			$library_view_access           = '1';
			$library_add_access            = '1';
			$leave_view_access             = '1';
			$access_right_view_access      = '1';
			$access_right_add_access       = '1';
			$leave_add_access              = '1';
			$hostel_view_access            = '1';
			$hostel_add_access             = '1';
			$transport_view_access         = '1';
			$transport_add_access          = '1';
			$certificate_view_access       = '1';
			$certificate_add_access        = '1';
			$report_view_access            = '1';
			$report_add_access             = '1';
			$advance_report_view_access    = '1';
			$advance_report_add_access     = '1';
			$notice_view_access            = '1';
			$notice_add_access             = '1';
			$message_view_access           = '1';
			$message_add_access            = '1';
			$holiday_view_access           = '1';
			$holiday_add_access            = '1';
			$notification_view_access      = '1';
			$notification_add_access       = '1';
			$event_view_access             = '1';
			$event_add_access              = '1';
			$field_view_access             = '1';
			$field_add_access              = '1';
			$sms_view_access               = '1';
			$sms_add_access                = '1';
			$mail_view_access              = '1';
			$mail_add_access               = '1';
			$mjschool_template_view_access = '1';
			$mjschool_template_add_access  = '1';
			$class_view_access             = '1';
			$class_add_access              = '1';
			$class_room_view_access		   = '1';
			$class_room_add_access 		   = '1';
			$schedule_view_access          = '1';
			$schedule_add_access           = '1';
			$virtual_class_view_access     = '1';
			$virtual_class_add_access      = '1';
			$subject_view_access           = '1';
			$subject_add_access            = '1';
			$migration_view_access         = '1';
			$migration_add_access          = '1';
		}	
		
		if ( $request_page === 'mjschool' ) {
			
			?>
			<div class="mjschool-popup-bg">
				<div class="mjschool-overlay-content mjschool-content-width">
					<div class="modal-content d-modal-style">
						<div class="mjschool-task-event-list"></div>
						<div class="mjschool-category-list"></div>
					</div>
				</div>
			</div>
			<?php
			if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
				?>
				<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url('https://www.youtube.com/embed/H2oDKfMVN-I?si=1kWparkE0ekoLYm3'); ?>" title="<?php esc_attr_e( 'School Overview', 'mjschool' ); ?>">
					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-youtube-icon.png"); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">
					
				</a>
				<?php
			}
		}
		?>
		<div class="row mjschool-header mjschool-plugin-code-start mjschool-admin-dashboard-main-div mjchool_margin_none" >
			<!--Header part in set logo & title start.-->
			<div class="col-sm-12 col-md-12 col-lg-2 col-xl-2 mjschool-custom-padding-0">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool' ) ); ?>" class='mjschool-logo'>
					<img src="<?php echo esc_url( get_option( 'mjschool_system_logo' ) ); ?>" class="mjschool-system-logo-height-width">
				</a>
				<!-- Toggle button & design start. -->
				<button type="button" id="sidebarCollapse" class="navbar-btn">
					<span></span>
					<span></span>
					<span></span>
				</button>
				<!--  Toggle button & design end. -->
			</div>
			<div class="col-sm-12 col-md-12 col-lg-10 col-xl-10 mjschool-right-heder">
				<div class="row">
					<div class="col-sm-8 col-md-8 col-lg-8 col-xl-8 mjschool-name-and-icon-dashboard mjschool-align-items-unset-res mjschool-header-width">
						<div class="mjschool-title-add-btn">
							<!-- Page name. -->
							<h3 class="mjschool-addform-header-title mjschool-rtl-menu-backarrow-float">
								<?php
								$school_obj         = new MJSchool_Management( get_current_user_id() );
								$mjschool_page_name = '';
								$active_tab         = '';
								$mjschool_action   = '';
								if ( ! empty( $_REQUEST['page'] ) ) {
									$mjschool_page_name = $request_page;
								}
								if ( ! empty( $_REQUEST['tab'] ) ) {
									$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '';
								}
								if ( ! empty( $_REQUEST['action'] ) ) {
									$mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
								}
								$mjschool_role = $school_obj->role;
								if ( $request_page === 'mjschool' ) {
									esc_html_e( 'Welcome to Dashboard', 'mjschool' ) . ', ';
									if ( $mjschool_role === 'management' ) {
										esc_html_e( 'Management', 'mjschool' );
									} else {
										esc_html_e( 'Admin', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_student' ) {
									if ( $active_tab === 'addstudent' || $active_tab === 'view_student' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=studentlist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Student', 'mjschool' );
										} elseif ( $mjschool_action === 'view_student' ) {
											esc_html_e( 'View Student', 'mjschool' );
										} else {
											esc_html_e( 'Add Student', 'mjschool' );
										}
									} else {
										esc_html_e( 'Student', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_teacher' ) {
									if ( $active_tab === 'addteacher' || $active_tab === 'view_teacher' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=teacherlist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Teacher', 'mjschool' );
										} elseif ( $active_tab === 'view_teacher' ) {
											esc_html_e( 'View Teacher', 'mjschool' );
										} else {
											esc_html_e( 'Add Teacher', 'mjschool' );
										}
									} else {
										esc_html_e( 'Teacher', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_parent' ) {
									if ( $active_tab === 'addparent' || $active_tab === 'view_parent' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent&tab=parentlist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Parent', 'mjschool' );
										} elseif ( $mjschool_action === 'view_parent' ) {
											esc_html_e( 'View Parent', 'mjschool' );
										} else {
											esc_html_e( 'Add Parent', 'mjschool' );
										}
									} else {
										esc_html_e( 'Parent', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_supportstaff' ) {
									if ( $active_tab === 'addsupportstaff' || $active_tab === 'view_supportstaff' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_supportstaff&tab=supportstaff_list' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Support Staff', 'mjschool' );
										} elseif ( $mjschool_action === 'view_supportstaff' ) {
											esc_html_e( 'View Support Staff', 'mjschool' );
										} else {
											esc_html_e( 'Add Support Staff', 'mjschool' );
										}
									} else {
										esc_html_e( 'Support Staff', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_student_homewrok' ) {
									$nonce = wp_create_nonce( 'mjschool_homework_tab' );
									if ( $active_tab === 'addhomework' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Homework', 'mjschool' );
										} else {
											esc_html_e( 'Add Homework', 'mjschool' );
										}
									} elseif ( $active_tab === 'view_homework' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										esc_html_e( 'Homework Details', 'mjschool' );
									} elseif ( $active_tab === 'view_stud_detail' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=homeworklist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										esc_html_e( 'View Submission', 'mjschool' );
									} else {
										esc_html_e( 'Homework', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_library' ) {
									$nonce = wp_create_nonce( 'mjschool_library_tab' );
									if ( $active_tab === 'booklist' || $active_tab === 'addbook' ) {
										esc_html_e( 'Book', 'mjschool' );
									} elseif ( $active_tab === 'issuelist' ) {
										esc_html_e( 'Issue & Return', 'mjschool' );
									} elseif ( $active_tab === 'issue_return' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=issuelist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										esc_html_e( 'Issue & Return', 'mjschool' );
									} elseif ( $active_tab === 'view_book' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										esc_html_e( 'Book Details', 'mjschool' );
									} else {
										esc_html_e( 'Library', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_class' ) {
									if ( $active_tab === 'addclass' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=classlist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Class', 'mjschool' );
										} else {
											esc_html_e( 'Add Class', 'mjschool' );
										}
									} elseif ( $active_tab === 'class_details' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=classlist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										echo esc_html__( 'Class Details', 'mjschool' );
									} else {
										esc_html_e( 'Class', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_class_room' ){
									if ($active_tab === 'add_class_room' )
									{
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room&tab=class_room_list' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ($mjschool_action === 'edit' ) 
										{
											esc_html_e( 'Edit Class Room', 'mjschool' );
										} 
										else 
										{
											esc_html_e( 'Add Class Room', 'mjschool' );
										}
									}	
									else
									{
										esc_html_e( 'Class Room', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_admission' ) {
									if ( $active_tab === 'mjschool-admission-form' || $active_tab === 'view_admission' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Admission', 'mjschool' );
										} elseif ( $mjschool_action === 'view_admission' ) {
											esc_html_e( 'View Admission', 'mjschool' );
										} else {
											esc_html_e( 'Add Admission', 'mjschool' );
										}
									} else {
										esc_html_e( 'Admission', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_route' ) {
									if ( $active_tab === 'addroute' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Class Time Table', 'mjschool' );
										} else {
											esc_html_e( 'Class Time Table', 'mjschool' );
										}
									} else {
										esc_html_e( 'Class Time Table', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_virtual_classroom' ) {
									if ( $active_tab === 'edit_meeting' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Virtual Classroom', 'mjschool' );
										} else {
											esc_html_e( 'Add Virtual Classroom', 'mjschool' );
										}
									} elseif ( $active_tab === 'view_past_participle_list' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										esc_html_e( 'Participant List', 'mjschool' );
									} else {
										esc_html_e( 'Virtual Classroom', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_exam' ) {
									if ( $active_tab === 'addexam' || $active_tab === 'exam_time_table' ) {
										?>
										<?php $nonce = wp_create_nonce( 'mjschool_exam_module_tab' ); ?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=examlist&_wpnonce=' . $nonce ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Exam', 'mjschool' );
										} 
										elseif ( $active_tab === 'exam_time_table' ){
											esc_html_e( 'Exam Time Table', 'mjschool' );
										}else {
											esc_html_e( 'Exam', 'mjschool' );
										}
									} else {
										esc_html_e( 'Exam', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_Subject' ) {
									if ( $active_tab === 'addsubject' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_Subject&tab=Subject' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Subject', 'mjschool' );
										} else {
											esc_html_e( 'Add Subject', 'mjschool' );
										}
									} else {
										esc_html_e( 'Subject', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_hall' ) {
									if ( $active_tab === 'addhall' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Exam Hall', 'mjschool' );
										} else {
											esc_html_e( 'Exam Hall', 'mjschool' );
										}
									} else {
										esc_html_e( 'Exam Hall', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_grade' ) {
									if ( $active_tab === 'addgrade' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade&tab=gradelist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Grade', 'mjschool' );
										} else {
											esc_html_e( 'Add Grade', 'mjschool' );
										}
									} else {
										esc_html_e( 'Grade', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_result' ) {
									if ( $active_tab === 'result' ) {
										esc_html_e( 'Manage Marks', 'mjschool' );
									} elseif ( $active_tab === 'export_marks' ) {
										esc_html_e( 'Export Marks', 'mjschool' );
									} elseif ( $active_tab === 'multiple_subject_marks' ) {
										esc_html_e( 'Multiple Subject Marks', 'mjschool' );
									} else {
										esc_html_e( 'Manage Marks', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_attendence' ) {
									if ( $active_tab === 'student_attendance' ) {
										esc_html_e( 'Student Attendance', 'mjschool' );
									} else {
										esc_html_e( 'Teacher Attendance', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_library' ) {
									esc_html_e( 'Library', 'mjschool' );
								}
								// --- Leave module start. ---//
								elseif ( $mjschool_page_name === 'mjschool_leave' ) {
									if ( $active_tab === 'add_leave' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=leave_list' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Leave', 'mjschool' );
										} else {
											esc_html_e( 'Add Leave', 'mjschool' );
										}
									} else {
										esc_html_e( 'Leave', 'mjschool' );
									}
								}
								// --- Leave module end. ---//
								// Hostel module start.
								elseif ( $mjschool_page_name === 'mjschool_hostel' ) {
									if ( $mjschool_page_name === 'mjschool_hostel' && $active_tab === 'hostel_list' ) {
										esc_html_e( 'Hostel', 'mjschool' );
									} elseif ( $mjschool_page_name === 'mjschool_hostel' && $active_tab === 'hostel_details' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										esc_html_e( 'Hostel Details', 'mjschool' );
									} elseif ($mjschool_page_name === 'mjschool_hostel' && $active_tab === 'add_hostel' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php 
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Hostel', 'mjschool' );
										} else {
											esc_html_e( 'Add Hostel', 'mjschool' );
										}
									} else {
										esc_html_e( 'Hostel', 'mjschool' );
									}
								}
								// Hostel module end.
								elseif ( $mjschool_page_name === 'mjschool_notice' ) {
									if ( $active_tab === 'addnotice' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Notice', 'mjschool' );
										} else {
											esc_html_e( 'Add Notice', 'mjschool' );
										}
									} else {
										esc_html_e( 'Notice', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_certificate' ) {
									$nonce = wp_create_nonce( 'mjschool_certificate_tab' );
									if ( $active_tab === 'add_certificate' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Certificate', 'mjschool' );
										} else {
											esc_html_e( 'Add Certificate', 'mjschool' );
										}
									} elseif ( $active_tab === 'certificatelist' ) {
										esc_html_e( 'Certificates', 'mjschool' );
									}
									if ( $active_tab === 'assign_certificate' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_list&_wpnonce=' . $nonce ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Assign Certificate', 'mjschool' );
										} else {
											esc_html_e( 'Assign Certificate', 'mjschool' );
										}
									} elseif ( $active_tab === 'assign_list' ) {
										esc_html_e( 'Student Certificate', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_event' ) {
									if ( $active_tab === 'add_event' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=eventlist' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Event', 'mjschool' );
										} else {
											esc_html_e( 'Add Event', 'mjschool' );
										}
									} else {
										esc_html_e( 'Event', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_notification' ) {
									if ( $active_tab === 'addnotification' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification&tab=notificationlist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										esc_html_e( 'Add Notification', 'mjschool' );
									} else {
										esc_html_e( 'Notification', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_holiday' ) {
									if ( $active_tab === 'addholiday' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=holidaylist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Holiday', 'mjschool' );
										} else {
											esc_html_e( 'Add Holiday', 'mjschool' );
										}
									} else {
										esc_html_e( 'Holiday', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_message' ) {
									esc_html_e( 'Message', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_Migration' ) {
									esc_html_e( 'Migration', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_payment' ) {
									if ( $active_tab === 'payment' ) {
										esc_html_e( 'Other Payment', 'mjschool' );
									} elseif ( $active_tab === 'incomelist' ) {
										esc_html_e( 'Income', 'mjschool' );
									} elseif ( $active_tab === 'expenselist' ) {
										esc_html_e( 'Expense', 'mjschool' );
									}
									if ( $active_tab === 'addincome' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Income', 'mjschool' );
										} else {
											esc_html_e( 'Income', 'mjschool' );
										}
									} elseif ( $active_tab === 'addexpense' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Expense', 'mjschool' );
										} else {
											esc_html_e( 'Expense', 'mjschool' );
										}
									} elseif ( $active_tab === 'view_invoice' ) {
										if ( $request_invoice_type === 'income' || $request_invoice_type === 'invoice' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist' ) ); ?>'>
												
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
												
											</a>
											<?php
										} elseif ( $request_invoice_type === 'expense' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=expenselist' ) ); ?>'>
												
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
												
											</a>
											<?php
										}
										esc_html_e( 'View Invoice', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_fees_payment' ) {
									if ( $active_tab === 'feeslist' ) {
										esc_html_e( 'Fees Type', 'mjschool' );
									} elseif ( $active_tab === 'feespaymentlist' ) {
										esc_html_e( 'Fees Payment', 'mjschool' );
									} elseif ( $active_tab === 'recurring_feespaymentlist' ) {
										esc_html_e( 'Recurring Fees Payment', 'mjschool' );
									} elseif ( $active_tab === 'view_fessreceipt' ) {
										esc_html_e( 'Payment History', 'mjschool' );
									}
									if ( $active_tab === 'addfeetype' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Fees Type', 'mjschool' );
										} else {
											esc_html_e( 'Fees Type', 'mjschool' );
										}
									} elseif ( $active_tab === 'addpaymentfee' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Fees Payment', 'mjschool' );
										} else {
											esc_html_e( 'Fees Payment', 'mjschool' );
										}
									} elseif ( $active_tab === 'addrecurringpayment' ) {
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Recurring Fees Payment', 'mjschool' );
										} else {
											esc_html_e( 'Recurring Fees Payment', 'mjschool' );
										}
									} elseif ( $active_tab === 'view_fesspayment' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										esc_html_e( 'View Fees Payment Invoice', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_tax' ) {
									if ( $active_tab === 'add_tax' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_tax&tab=tax' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Tax', 'mjschool' );
										} else {
											esc_html_e( 'Add Tax', 'mjschool' );
										}
									} else {
										esc_html_e( 'Tax', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_transport' ) {
									if ( $active_tab === 'addtransport' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_transport&tab=transport' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Transport', 'mjschool' );
										} else {
											esc_html_e( 'Add Transport', 'mjschool' );
										}
									} else {
										esc_html_e( 'Transport', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_report' ) {
									esc_html_e( 'Reports', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_advance_report' ) {
									esc_html_e( 'Advance Reports', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_setup' ) {
									esc_html_e( 'License settings', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_custom_field' ) {
									if ( $active_tab === 'add_custome_field' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field&tab=custome_field_list' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Custom Field', 'mjschool' );
										} else {
											esc_html_e( 'Add Custom Field', 'mjschool' );
										}
									} else {
										esc_html_e( 'Custom Fields', 'mjschool' );
									}
								} elseif ( $mjschool_page_name === 'mjschool_sms_setting' ) {
									esc_html_e( 'SMS Settings', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_email_template' ) {
									esc_html_e( 'Email Template', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_sms_template' ) {
									esc_html_e( 'SMS Template', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_access_right' ) {
									esc_html_e( 'Access Right', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_system_videos' ) {
									esc_html_e( 'How To Videos', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_system_addon' ) {
									esc_html_e( 'Addons', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_general_settings' ) {
									esc_html_e( 'General Settings', 'mjschool' );
								} elseif ( $mjschool_page_name === 'mjschool_document' ) {
									if ( $active_tab === 'add_document' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document&tab=documentlist' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-back-arrow.png"); ?>">
											
										</a>
										<?php
										if ( $mjschool_action === 'edit' ) {
											esc_html_e( 'Edit Document', 'mjschool' );
										} else {
											esc_html_e( 'Add Document', 'mjschool' );
										}
									} else {
										esc_html_e( 'Documents', 'mjschool' );
									}
								} else {
									echo esc_html( $mjschool_page_name );
								}
								?>
							</h3>
							<div class="mjschool-add-btn1"><!-------- Plus button div. -------->
								<?php
								if ( $mjschool_page_name === 'mjschool_student' && $active_tab != 'addstudent' && $mjschool_action != 'view_student' ) {
									
									if ($student_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=addstudent' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_admission" && $active_tab != 'mjschool-admission-form' && $active_tab != 'view_admission' ) {
									if ($admission_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_class" && $active_tab != 'class_details' && $active_tab != 'addclass' && $active_tab != 'class_wise_student_list' ) {
									if ($class_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=addclass' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_class_room" && $active_tab != 'add_class_room' ) {
									if ($class_room_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room&tab=add_class_room' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_route" && $active_tab != 'addroute' ) {
									if ($schedule_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=addroute' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_teacher" && $active_tab != 'addteacher' && $active_tab != 'view_teacher' && $mjschool_action != 'view_teacher' ) {
									if ($teacher_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_parent" && $active_tab != 'addparent' && $mjschool_action != 'view_parent' ) {
									if ($parent_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent&tab=addparent' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ($mjschool_page_name === "mjschool_supportstaff" && $active_tab != 'addsupportstaff' && $mjschool_action != 'view_supportstaff' ) {
									if ($staff_add_access === '1') {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php 
									}
								} elseif ( $mjschool_page_name === 'mjschool_student_homewrok' && $active_tab != 'addhomework' && $active_tab != 'view_stud_detail' && $active_tab != 'view_homework' ) {
									if ( $homework_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework' ) ); ?>'>
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_virtual_classroom' && $active_tab != 'edit_meeting' && $active_tab != 'view_past_participle_list' ) {
									if ( $virtual_class_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=addroute' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_Subject' && $active_tab != 'addsubject' ) {
									if ( $subject_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_Subject&tab=addsubject' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_exam' && $active_tab != 'addexam' && $active_tab != 'exam_time_table' ) {
									if ( $exam_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=addexam' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_hall' && $active_tab != 'addhall' && $active_tab != 'exam_hall_receipt' ) {
									if ( $hall_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=addhall' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_library' && $active_tab === 'booklist' ) {
									if ( $library_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=addbook' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_grade' && $active_tab != 'addgrade' ) {
									if ( $grade_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade&tab=addgrade' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_hostel' && $active_tab === 'hostel_list' ) {
									if ( $hostel_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=add_hostel' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_tax' && $active_tab === 'tax' ) {
									if ( $tax_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_tax&tab=add_tax' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_payment' ) {
									if ( $active_tab === 'payment' ) {
										if ( $payment_add_access === '1' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=addpayment' ) ); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
											</a>
											<?php
										}
									} elseif ( $active_tab === 'incomelist' ) {
										if ( $payment_add_access === '1' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=addincome' ) ); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
											</a>
											<?php
										}
									} elseif ( $active_tab === 'expenselist' ) {
										if ( $payment_add_access === '1' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=addexpense' ) ); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
											</a>
											<?php
										}
									}
								} elseif ( $mjschool_page_name === 'mjschool_fees_payment' ) {
									if ( $active_tab === 'feeslist' ) {
										if ( $fees_add_access === '1' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addfeetype' ) ); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
											</a>
											<?php
										}
									} elseif ( $active_tab === 'feespaymentlist' || $active_tab === 'recurring_feespaymentlist' ) {
										if ( $fees_add_access === '1' ) {
											?>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addpaymentfee' ) ); ?>'>
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
											</a>
											<?php
										}
									}
								} elseif ( $mjschool_page_name === 'mjschool_transport' && $active_tab != 'addtransport' ) {
									if ( $hostel_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_transport&tab=addtransport' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_leave' && $active_tab != 'add_leave' ) {
									if ( $leave_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=add_leave' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								}
								elseif ( $mjschool_page_name === 'mjschool_notice' && $active_tab != 'addnotice' ) {
									if ( $notice_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=addnotice' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_event' && $active_tab != 'add_event' ) {
									if ( $event_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=add_event' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_certificate' && $active_tab === 'assign_list' ) {
									if ( $certificate_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=assign_certificate&action=new' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_notification' && $active_tab != 'addnotification' ) {
									if ( $notification_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification&tab=addnotification' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_holiday' && $active_tab != 'addholiday' ) {
									if ( $holiday_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=addholiday' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_message' ) {
									if ( $message_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=compose' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_custom_field' && $active_tab != 'add_custome_field' ) {
									if ( $field_add_access === '1' ) {
										?>
										<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field&tab=add_custome_field' ) ); ?>'>
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
										</a>
										<?php
									}
								} elseif ( $mjschool_page_name === 'mjschool_document' && $active_tab != 'add_document' ) {
									?>
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document&tab=add_document' ) ); ?>'>
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-add-new-button.png"); ?>">
									</a>
									<?php
								}
								?>
							</div><!-------- Plus button div end.-------->
							<!-- End Page Name  .-->
						</div>
					</div>
					<!-- Right Header. -->
					<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4">
						<div class="mjschool-setting-notification">
							<div class="mjschool-user-dropdown mjschool-setting-notification-bg mjschool-setting-dropdown-responsive mjschool-dashboard-header-setting-rtl mjschool_margin_right_15px">
								<ul >
									<!-- Begin user login dropdown. -->
									<li >
										<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-settings.png"); ?>" class="mjschool-dropdown-userimg">
										</a>
										<ul class="dropdown-menu extended mjschool-action-dropdawn mjschool-logout-dropdown-menu logout mjschool-header-dropdown-menu mjschool-setting-dropdown-menu" aria-labelledby="dropdownMenuLink">
											<li class="mjschool-float-left-width-100px">
												<?php $nonce = wp_create_nonce( 'mjschool_general_setting_tab' ); ?>
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_general_settings&_wpnonce='.esc_attr( $nonce ) ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-general-setting.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'General Settings', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-custom-fields.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'Custom Fields', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_sms_setting' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-sms-settings.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'SMS Settings', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_email_template' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-email-template.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'Email Template', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_sms_template' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-email-template.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'SMS Template', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_access_right' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-access-rights.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'Access Right', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_system_videos' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-how-to-tutorial.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'How To Videos', 'mjschool' ); ?>
													</p>
												</a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_system_addon' ) ); ?>"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/mjschool-system-addons.png"); ?>" class="mjschool-dashboard-popup-icon">
													<p class="mjschool-dashboard-setting-dropdow">
														<?php esc_html_e( 'Addons', 'mjschool' ); ?>
													</p>
												</a>
											</li>
										</ul>
									</li>
									<!-- End user login dropdown. -->
								</ul>
							</div>
							<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice' ) ); ?>' class="mjschool-setting-notification-bg">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-bell-notification.png"); ?>" class="mjschool-right-heder-list-link">
								<spna class="mjschool-between-border mjschool-right-heder-list-link"> </span>
							</a>
							<a href='<?php echo esc_url( wp_logout_url( home_url() ) ); ?>' class="mjschool-setting-notification-bg">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-logout.png"); ?>" class="mjschool-right-heder-list-link">
								<spna class="mjschool-between-border mjschool-right-heder-list-link"> </span>
							</a>
							<div class="mjschool-user-dropdown">
								<ul >
									<!-- Begin user login dropdown. -->
									<li >
										<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-avatar.png"); ?>" class="mjschool-dropdown-userimg">
										</a>
										<ul class="dropdown-menu extended mjschool-action-dropdawn mjschool-logout-dropdown-menu logout mjschool-header-dropdown-menu" aria-labelledby="dropdownMenuLink">
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-back-wp mjschool-float-left-width-100px" href="<?php echo esc_url( admin_url() ); ?>"><i class="fa fa-user"></i> <?php esc_html_e( 'Back to wp-admin', 'mjschool' ); ?></a>
											</li>
											<li class="mjschool-float-left-width-100px">
												<a class="dropdown-item mjschool-float-left-width-100px" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><i class="fa fa-sign-out"></i><?php esc_html_e( 'Log Out', 'mjschool' ); ?></a>
											</li>
										</ul>
									</li>
									<!-- End user login dropdown. -->
								</ul>
							</div>
						</div>
					</div>
					<!-- Right Header. -->
				</div>
			</div>
		</div>
		<div class="row main_page mjschool-plugin-code-start mjschool-admin-dashboard-menu-rs mjchool_margin_none">
			<div class="col-sm-12 col-md-12 col-lg-2 col-xl-2 mjschool-custom-padding-0 mjschool-main-sidebar-bgcolor_class" id="mjschool-main-sidebar-bgcolor">
				<!-- Menu sidebar main div start. -->
				<div class="mjschool-main-sidebar">
					<nav class="sidebar_dashboard" id="sidebar">
						<ul class='mjschool-navigation navbar-collapse mjschool-rs-side-menu-bgcolor' id="navbarNav">
							<li class="card-icon">
								<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_setup' ) ); ?>' class="<?php if ( $request_page === 'mjschool_setup' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
									<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-license.png"); ?>">
									<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-license.png"); ?>">
									<span><?php esc_html_e( 'License settings', 'mjschool' ); ?></span>
								</a>
							</li>
							<li class="card-icon">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool' ) ); ?>" class="<?php if ( $request_page === 'mjschool' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
									<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-dashboards.png"); ?>">
									<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-dashboards.png"); ?>">
									<span><?php esc_html_e( 'Dashboard', 'mjschool' ); ?></span>
								</a>
							</li>
							<?php
							if ( $admission_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_admission' ) ); ?>' class="<?php if ( $request_page === 'mjschool_admission' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-admission.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-admission.png"); ?>">
										<span><?php esc_html_e( 'Admission', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $class_view_access === '1' || $schedule_view_access === '1' || $virtual_class_view_access === '1' || $subject_view_access === '1' || $class_room_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="<?php if ( $request_page === 'mjschool_class' || $request_page === 'mjschool_route' || $request_page === 'mjschool_virtual_classroom' ||$request_page === 'mjschool_Subject' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-class.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-class.png"); ?>">
										<span><?php esc_html_e( 'Class', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										if ( $class_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class' ) ); ?>' class="<?php if ( $request_page === 'mjschool_class' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
													<span><?php esc_html_e( 'Class', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$school_type = get_option( 'mjschool_custom_class' );
										if ( $school_type === 'university' )
										{
											if(get_option( 'mjschool_class_room' ) === 1)
											{
												if ( $class_room_view_access === '1' ) {
													?>
													<li class=''>
														<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class_room' ) ); ?>' class="<?php if ( $request_page === 'mjschool_class_room' ) { esc_html_e( 'active', 'mjschool' );} ?>">
															<span><?php esc_html_e( 'Class Room', 'mjschool' ); ?></span>
														</a>
													</li>
													<?php
												}
											}
										}
										if ( $schedule_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route' ) ); ?>' class="<?php if ( $request_page === 'mjschool_route' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Class Routine', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $virtual_class_view_access === '1' ) {
											if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
												?>
												<li class=''>
													<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_virtual_classroom' ) ); ?>' class="<?php if ( $request_page === 'mjschool_virtual_classroom' ) { esc_html_e( 'active', 'mjschool' );} ?>">
														<span><?php esc_html_e( 'Virtual Classroom', 'mjschool' ); ?></span>
													</a>
												</li>
												<?php
											}
										}
										if ( $subject_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_Subject' ) ); ?>' class="<?php if ( $request_page === 'mjschool_Subject' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Subject', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							if ( $student_view_access === '1' || $staff_view_access === '1' || $teacher_view_access === '1' || $parent_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="<?php if ( $request_page === 'mjschool_student' || $request_page === 'mjschool_teacher' || $request_page === 'mjschool_supportstaff' || $request_page === 'mjschool_parent' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										
										<img class="icon img-top mjschool-responsive-iphone-icon mjschool-margin-left-3px" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-user.png"); ?>">
										<img class="icon mjschool-margin-left-3px" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-user-white.png"); ?>">
										
										<span class="mjschool-margin-left-12px"><?php esc_html_e( 'Users', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										if ( $student_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student' ) ); ?>' class="<?php if ( $request_page === 'mjschool_student' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Student', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $teacher_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher' ) ); ?>' class="<?php if ( $request_page === 'mjschool_teacher' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Teacher', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $staff_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_supportstaff' ) ); ?>' class="<?php if ( $request_page === 'mjschool_supportstaff' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $parent_view_access === '1' ) {
											?>
											<li >
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent' ) ); ?>' class="<?php if ( $request_page === 'mjschool_parent' ) { esc_html_e( 'active', 'mjschool' ); } ?>">
													<span><?php esc_html_e( 'Parent', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							if ( $exam_view_access === '1' || $hall_view_access === '1' || $mark_view_access === '1' || $grade_view_access === '1' || $migration_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="<?php if ( $request_page === 'mjschool_exam' || $request_page === 'mjschool_hall' || $request_page === 'mjschool_result' || $request_page === 'mjschool_grade' || $request_page === 'mjschool_Migration' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-exam.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam.png"); ?>">
										
										<span ><?php esc_html_e( 'Student Evaluation', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										if ( $exam_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam' ) ); ?>' class="<?php if ( $request_page === 'mjschool_exam' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Exam', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $hall_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall' ) ); ?>' class="<?php if ( $request_page === 'mjschool_hall' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Exam Hall', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $mark_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_result' ) ); ?>' class="<?php if ( $request_page === 'mjschool_result' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Manage Marks', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $grade_view_access === '1' ) {
											?>
											<li >
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_grade' ) ); ?>' class="<?php if ( $request_page === 'mjschool_grade' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Grade', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $migration_view_access === '1' ) {
											?>
											<li >
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_Migration' ) ); ?>' class="<?php if ( $request_page === 'mjschool_Migration' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Migration', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							if ( $homework_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok' ) ); ?>' class="<?php if ( $request_page === 'mjschool_student_homewrok' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-homework.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>">
										<span><?php esc_html_e( 'Homework', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $attendance_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class='<?php if ( $request_page === 'mjschool_attendence' ) { esc_html_e( 'active', 'mjschool' );} ?>'>
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-attendance.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>">
										<span><?php esc_html_e( 'Attendance', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php $nonce = wp_create_nonce( 'mjschool_student_attendance_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&_wpnonce='.esc_attr( $nonce ) ) ); ?>' >
												<span><?php esc_html_e( 'Student Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&_wpnonce='.esc_attr( $nonce ) ) ); ?>' >
												<span><?php esc_html_e( 'Teacher Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<?php
							}
							// --  Start ADD document side menu page name and link.  --//
							if ( $document_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_document' ) ); ?>' class="<?php if ( $request_page === 'mjschool_document' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-document.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-document.png"); ?>">
										<span><?php esc_html_e( 'Documents', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							// -- End ADD document side menu page name and link.  --//
							// --  Start ADD leave side menu page name and link.  --//
							if ( $leave_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave' ) ); ?>' class="<?php if ( $request_page === 'mjschool_leave' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-leave.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png"); ?>">
										<span><?php esc_html_e( 'Leave', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							// -- End ADD leave side menu page name and link.  --//
							if ( $tax_view_access === '1' || $fees_view_access === '1' || $payment_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class=" <?php if ( $request_page === 'mjschool_fees_payment' || $request_page === 'mjschool_payment' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-payment.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>">
										<span><?php esc_html_e( 'Payment', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php
										if ( $fees_view_access === '1' ) {
											?>
											<?php $nonce = wp_create_nonce( 'mjschool_feespayment_tab' ); ?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feeslist&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_fees_payment' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Fees payment', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $payment_view_access === '1' ) {
											?>
											<?php $nonce = wp_create_nonce( 'mjschool_payment_tab' ); ?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_payment&tab=incomelist&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_payment' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Other Payment', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $tax_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_tax&tab=tax' ) ); ?>' class="<?php if ( $request_page === 'mjschool_tax' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Tax', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							if ( $library_view_access === '1' ) {
								$nonce = wp_create_nonce( 'mjschool_library_tab' );
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=booklist&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_library' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-library.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-library.png"); ?>">
										<span><?php esc_html_e( 'Library', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $hostel_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list' ) ); ?>' class="<?php if ( $request_page === 'mjschool_hostel' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-hostel.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-hostel.png"); ?>">
										<span><?php esc_html_e( 'Hostel', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $transport_view_access === '1' ) {
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_transport' ) ); ?>' class="<?php if ( $request_page === 'mjschool_transport' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-transportation.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-transportation.png"); ?>">
										<span><?php esc_html_e( 'Transport', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $certificate_view_access === '1' ) {
								$nonce = wp_create_nonce( 'mjschool_certificate_tab' );
								?>
								<li class="card-icon">
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_certificate&tab=certificatelist&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_certificate' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-certificate-icon-dark.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-certificate-icon-light.png"); ?>">
										<span><?php esc_html_e( 'Certificate', 'mjschool' ); ?></span>
									</a>
								</li>
								<?php
							}
							if ( $report_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon report">
									<a href='#' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-report.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-report.png"); ?>">
										
										<span><?php esc_html_e( 'Reports', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php $nonce = wp_create_nonce( 'mjschool_student_infomation_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=student_information_report&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Student Information', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce1 = wp_create_nonce( 'mjschool_finance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=finance_report&_wpnonce='.esc_attr( $nonce1 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Finance/Payment', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce2 = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=attendance_report&_wpnonce='.esc_attr( $nonce2 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce3 = wp_create_nonce( 'mjschool_examination_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=examinations_report&_wpnonce='.esc_attr( $nonce3 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Examinations', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce4 = wp_create_nonce( 'mjschool_library_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=library_report&_wpnonce='.esc_attr( $nonce4 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Library', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce5 = wp_create_nonce( 'mjschool_hostel_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=hostel_report&_wpnonce='.esc_attr( $nonce5 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Hostel', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce6 = wp_create_nonce( 'mjschool_user_log_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=user_log_report&_wpnonce='.esc_attr( $nonce6 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'User Log', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce7 = wp_create_nonce( 'mjschool_audit_trail_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=audit_log_report&_wpnonce='.esc_attr( $nonce7 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Audit Trail Report', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce8 = wp_create_nonce( 'mjschool_migration_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_report&tab=migration_report&_wpnonce='.esc_attr( $nonce8 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Migration Report', 'mjschool' ); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<?php
							}
							if ( $advance_report_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon">
									<a href='#' class="<?php if ( $request_page === 'mjschool_advance_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-report.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-report.png"); ?>">
										<span><?php esc_html_e( 'Advance Reports', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu dropdown-menu'>
										<?php $nonce = wp_create_nonce( 'mjschool_advance_student_infomation_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_information_report&_wpnonce='.esc_attr( $nonce ) ) ); ?>' class=" <?php if ( $request_page === 'mjschool_advance_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Student Information', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce2 = wp_create_nonce( 'mjschool_advance_finance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=finance_report&_wpnonce='.esc_attr( $nonce2 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_advance_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Finance/Payment', 'mjschool' ); ?></span>
											</a>
										</li>
										<?php $nonce3 = wp_create_nonce( 'mjschool_advance_attendance_report_tab' ); ?>
										<li class=''>
											<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_attendance_report&_wpnonce='.esc_attr( $nonce3 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_advance_report' ) { esc_html_e( 'active', 'mjschool' );} ?>">
												<span><?php esc_html_e( 'Attendance', 'mjschool' ); ?></span>
											</a>
										</li>
									</ul>
								</li>
								<?php
							}
							if ( $notice_view_access === '1' || $message_view_access === '1' || $holiday_view_access === '1' || $notification_view_access === '1' || $event_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon message_menu mjschool-general-setting-menu-for-notification">
									<a href='#' class=" <?php if ( $request_page === 'mjschool_notice' || $request_page === 'mjschool_message' || $request_page === 'mjschool_event' || $request_page === 'mjschool_notification' || $request_page === 'mjschool_holiday' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-notifications.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notifications.png"); ?>">
										<span><?php esc_html_e( 'Notification', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu mjschool-admin-submenu-css dropdown-menu'>
										<?php
										if ( $notice_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice' ) ); ?>' class="<?php if ( $request_page === 'mjschool_notice' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Notice', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $message_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message' ) ); ?>' class="<?php if ( $request_page === 'mjschool_message' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Message', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $notification_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification' ) ); ?>' class="<?php if ( $request_page === 'mjschool_notification' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Notification', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $event_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event' ) ); ?>' class="<?php if ( $request_page === 'mjschool_event' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Event', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $holiday_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday' ) ); ?>' class="<?php if ( $request_page === 'mjschool_holiday' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Holiday', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							if ( $field_view_access === '1' || $sms_view_access === '1' || $mail_view_access === '1' || $mjschool_template_view_access === '1' ) {
								?>
								<li class="has-submenu nav-item card-icon <?php if ( $mjschool_role != 'management' ) { ?> mjschool-general-setting-menu <?php } ?>">
									<a href='#' class=" <?php if ( $request_page === 'custom_field' || $request_page === 'mjschool_sms_setting' || $request_page === 'mjschool_email_template' || $request_page === 'mjschool_access_right' || $request_page === 'mjschool_general_settings' ) { esc_html_e( 'active', 'mjschool' );} ?>">
										<img class="icon img-top mjschool-responsive-iphone-icon" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/mjschool-setting.png"); ?>">
										<img class="icon " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-setting.png"); ?>">
										<span><?php esc_html_e( 'System Settings', 'mjschool' ); ?></span>
										<i class="fa <?php echo esc_attr( $rtl_left_icon_class_css ); ?> mjschool-dropdown-right-icon icon" aria-hidden="true"></i>
										<i class="fa fa-chevron-down icon mjschool-dropdown-down-icon" aria-hidden="true"></i>
									</a>
									<ul class='submenu mjschool-admin-submenu-css dropdown-menu'>
										<?php
										if ( $field_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field' ) ); ?>' class="<?php if ( $request_page === 'custom_field' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Custom Fields', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $sms_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_sms_setting' ) ); ?>' class="<?php if ( $request_page === 'mjschool_sms_setting' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'SMS Settings', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $mail_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_email_template' ) ); ?>' class="<?php if ( $request_page === 'mjschool_email_template' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Email Template', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										if ( $mjschool_template_view_access === '1' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_sms_template' ) ); ?>' class="<?php if ( $request_page === 'mjschool_sms_template' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'SMS Template', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										$mjschool_role = $school_obj->role;
										if ( $mjschool_role === 'administrator' ) {
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_access_right' ) ); ?>' class="<?php if ( $request_page === 'mjschool_access_right' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Access Right', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
											if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
												?>
												<li class=''>
													<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_system_videos' ) ); ?>' class="<?php if ( $request_page === 'mjschool_system_videos' ) { esc_html_e( 'active', 'mjschool' );} ?>">
														<span><?php esc_html_e( 'How To Videos', 'mjschool' ); ?></span>
													</a>
												</li>
												<?php
											}
											?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_system_addon' ) ); ?>' class="<?php if ( $request_page === 'mjschool_system_addon' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'Addons', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php $nonce = wp_create_nonce( 'mjschool_general_setting_tab' ); ?>
											<li class=''>
												<a href='<?php echo esc_url( admin_url( 'admin.php?page=mjschool_general_settings&_wpnonce='.esc_attr( $nonce1 ) ) ); ?>' class="<?php if ( $request_page === 'mjschool_general_settings' ) { esc_html_e( 'active', 'mjschool' );} ?>">
													<span><?php esc_html_e( 'General Settings', 'mjschool' ); ?></span>
												</a>
											</li>
											<?php
										}
										?>
									</ul>
								</li>
								<?php
							}
							?>
						</ul>
					</nav>
				</div>
				<!-- End menu sidebar main div. -->
			</div>
			<!-- Dashboard content div start. -->
			<div class="col col-sm-12 col-md-12 col-lg-10 col-xl-10 mjschool-dashboard-margin mjschool-padding-left-0 mjschool-padding-right-0">
				<div class="mjschool-page-inner mjschool-min-height-1088 mjschool-admin-homepage-padding-top">
					<!-- Main-wrapper div start.-->
					<div id="main-wrapper" class="main-wrapper-div mjschool-label-margin-top-15px mjschool-admin-dashboard mjschool_new_main_warpper">
						<?php
						$mjschool_page_name = $request_page;
						if ( $request_page === 'mjschool_student' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/student/index.php';
						} elseif ( $request_page === 'mjschool_teacher' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/teacher/index.php';
						} elseif ( $request_page === 'mjschool_supportstaff' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/supportstaff/index.php';
						} elseif ( $request_page === 'mjschool_parent' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/parent/index.php';
						} elseif ( $request_page === 'mjschool_class' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/class/index.php';
						}elseif ( $request_page === 'mjschool_class_room' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/class-room/index.php';
						} elseif ( $request_page === 'mjschool_route' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/routine/index.php';
						} elseif ( $request_page === 'mjschool_admission' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/admission/index.php';
						} elseif ( $request_page === 'mjschool_virtual_classroom' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/virtual-classroom/index.php';
						} elseif ( $request_page === 'mjschool_Subject' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/subject/index.php';
						} elseif ( $request_page === 'mjschool_exam' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/exam/index.php';
						} elseif ( $request_page === 'mjschool_hall' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/hall/index.php';
						} elseif ( $request_page === 'mjschool_result' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/mark/index.php';
						} elseif ( $request_page === 'mjschool_grade' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/grade/index.php';
						} elseif ( $request_page === 'mjschool_Migration' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/migration/index.php';
						} elseif ( $request_page === 'mjschool_student_homewrok' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/student-homework/index.php';
						} elseif ( $request_page === 'mjschool_attendence' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/attendence/index.php';
						} elseif ( $request_page === 'mjschool_tax' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/tax/index.php';
						} elseif ( $request_page === 'mjschool_fees_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/fees-payment/index.php';
						} elseif ( $request_page === 'mjschool_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/payment/index.php';
						} elseif ( $request_page === 'mjschool_library' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/library/index.php';
						} elseif ( $request_page === 'mjschool_hostel' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/hostel/index.php';
						} elseif ( $request_page === 'mjschool_leave' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/leave/index.php';
						} elseif ( $request_page === 'mjschool_transport' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/transport/index.php';
						} elseif ( $request_page === 'mjschool_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/index.php';
						} elseif ( $request_page === 'mjschool_advance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/index.php';
						} elseif ( $request_page === 'mjschool_notice' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/notice/index.php';
						} elseif ( $request_page === 'mjschool_certificate' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/certificate/index.php';
						} elseif ( $request_page === 'mjschool_event' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/event/index.php';
						} elseif ( $request_page === 'mjschool_notification' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/notification/index.php';
						} elseif ( $request_page === 'mjschool_message' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/message/index.php';
						} elseif ( $request_page === 'mjschool_holiday' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/holiday/index.php';
						} elseif ( $request_page === 'mjschool_custom_field' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/custome-field/index.php';
						} elseif ( $request_page === 'mjschool_sms_setting' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/sms-setting/index.php';
						} elseif ( $request_page === 'mjschool_email_template' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/email-template/index.php';
						} elseif ( $request_page === 'mjschool_sms_template' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/sms-template/index.php';
						} elseif ( $request_page === 'mjschool_access_right' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/access-right/index.php';
						} elseif ( $request_page === 'mjschool_system_videos' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/system-video.php';
						} elseif ( $request_page === 'mjschool_system_addon' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/system-addons.php';
						} elseif ( $request_page === 'mjschool_general_settings' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/general-settings.php';
						} elseif ( $request_page === 'mjschool_setup' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/setupform/index.php';
						} elseif ( $request_page === 'mjschool_document' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/ducuments/index.php';
						}
						?>
						<?php
						if ( $request_page === 'mjschool' ) {
							$wizard_option = get_option( 'mjschool_setup_wizard_step' );
							$wizard_status = get_option( 'mjschool_setup_wizard_status' );
							$setup_i       = 1;
							?>
							<!-- Setup wizard start. -->
							<div class="mjschool-setup-wizard-dashboard">
								<div class="mjschool-accordion-wizzard accordion">
									<h4 class="accordion-header mjschool-wizard-heading" id="flush-heading<?php echo esc_attr( $setup_i ); ?>">
										<button class="accordion-button mjschool-wizzard-button  collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $setup_i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $setup_i ); ?>">
											<?php esc_html_e( 'Setup Wizard', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $setup_i ); ?>" class="accordion-collapse collapse mjschool-wizard-accordion-rtl <?php if ( $wizard_status != 'yes' ) { echo 'show'; }?>" aria-labelledby="flush-heading<?php echo esc_attr( $setup_i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-wizard">
											<div class="mjschool-wizard-main">
												<div class="steps clearfix">
													<ul role="tablist">
														<li role="tab" class="first mjschool-wizard-responsive disabled <?php if ( $wizard_option['step1_general_setting'] === 'yes' ) { echo 'done';} ?>" aria-disabled="false" aria-selected="true">
															<a id="form-total-t-0" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_general_settings'));?>" aria-controls="form-total-p-0">
																<span class="current-info audible"> </span>
																<div class="title mjschool-wizard-title">
																	<span class="mjschool-step-icon">
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-setting.png"); ?>">
																		<?php
																		if ( $wizard_option['step1_general_setting'] === 'yes' ) {	
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php  
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'General Settings', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-external-padding <?php if ( $wizard_option['step2_class'] === 'yes' ) { echo 'done';} ?>" aria-disabled="true">
															<a id="form-total-t-1" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_class&tab=addclass'));?>" aria-controls="form-total-p-1">
																<div class="title mjschool-wizard-title">
																	<span class="mjschool-step-icon">
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-class.png"); ?>">
																		<?php  
																		if ( $wizard_option['step2_class'] === 'yes' ) {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php  
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php  
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Add Class', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-external-padding mjschool-wizard-title <?php if ( $wizard_option['step3_teacher'] === 'yes' ) { echo 'done';} ?>" aria-disabled="true">
															<a id="form-total-t-2" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_teacher&tab=addteacher'));?>" aria-controls="form-total-p-2">
																<div class="title">
																	<span class="mjschool-step-icon">
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-teacher.png"); ?>">
																		<?php 
																		if ( $wizard_option['step3_teacher'] === 'yes' ) {
																			 ?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php  
																		} else {
																			 ?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php  
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Add Teacher', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-wizard-title <?php if ( $wizard_option['step4_subject'] === 'yes' ) { echo 'done'; } ?>" aria-disabled="true">
															<a id="form-total-t-2" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_Subject&tab=addsubject'));?>" aria-controls="form-total-p-2">
																<div class="title">
																	<span class="mjschool-step-icon">
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-subject.png"); ?>">
																		<?php  
																		if ( $wizard_option['step4_subject'] === 'yes' ) {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php  
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php  
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Add Subject', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-wizard-title last <?php if ( $wizard_option['step5_class_time_table'] === 'yes' ) { echo 'done';} ?>" aria-disabled="true">
															<a id="form-total-t-2" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_route&tab=addroute'));?>" aria-controls="form-total-p-2">
																<div class="title">
																	<span class="mjschool-step-icon">
																		
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-timetable.png"); ?>">
																		<?php
																		if ( $wizard_option['step5_class_time_table'] === 'yes' ) {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php 		
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php  
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Add Class Time Table', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-wizard-title last <?php if ( $wizard_option['step6_student'] === 'yes' ) { echo 'done';} ?>" aria-disabled="true">
															<a id="form-total-t-2" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=addstudent')); ?>" aria-controls="form-total-p-2">
																<div class="title">
																	<span class="mjschool-step-icon">
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/wizard/mjschool-wizard-student.png' ); ?>">
																		<?php
																		if ( $wizard_option['step6_student'] === 'yes' ) {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/wizard/mjschool-wizard-vector.png' ); ?>">
																			<?php
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/wizard/mjschool-wizard-hour-glass.png' ); ?>">
																		<?php } ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Add Student', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
														<li role="tab" class="disabled mjschool-wizard-responsive mjschool-wizard-title last last_child <?php if ( isset( $wizard_option['step7_email_temp'] ) && $wizard_option['step7_email_temp'] === 'yes' ) { echo 'done'; } ?>" aria-disabled="true">
															<a id="form-total-t-2" href="<?php echo esc_url( admin_url('admin.php?page=mjschool_email_template')); ?>" aria-controls="form-total-p-2">
																<div class="title">
																	<span class="mjschool-step-icon">
																		
																		<img class="center mjschool-wizard-settinge" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-student.png"); ?>">
																		<?php  
																		if ( $wizard_option['step7_email_temp'] === 'yes' ) {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-vector.png"); ?>">
																			<?php  
																		} else {
																			?>
																			<img class="mjschool-status-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/wizard/mjschool-wizard-hour-glass.png"); ?>">
																			<?php 
																		} ?>
																	</span>
																	<span class="step-number"><?php esc_html_e( 'Email Template', 'mjschool' ); ?></span>
																</div>
															</a>
														</li>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div><!-- Setup wizard end. -->
							<div class="row mjschool-menu-row mjschool-dashboard-content-rs mjschool-first-row-padding-top">
								<!-- User report card start. -->
								<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
									<div class="panel mjschool-panel-white mjschool-line-chat">
										<div class="mjschool-panel-heading mjschool-line-chat-p" id="mjschool-line-chat-p">
											<h3 class="mjschool-panel-title mjschool_float_left" ><?php esc_html_e( 'Users', 'mjschool' ); ?></h3>
										</div>
										<div class="mjschool-member-chart">
											<div class="outer">
												<p class="percent">
													<?php
													$user_query           = new WP_User_Query( array( 'role' => 'parent' ) );
													$parent_count         = (int) $user_query->get_total();
													$user_query_1         = new WP_User_Query( array( 'role' => 'student' ) );
													$student_count        = (int) $user_query_1->get_total();
													$user_query_2         = new WP_User_Query( array( 'role' => 'teacher' ) );
													$teacher_count        = (int) $user_query_2->get_total();
													$user_query_3         = new WP_User_Query( array( 'role' => 'supportstaff' ) );
													$staff_count          = (int) $user_query_3->get_total();
													$total_student_parent = $parent_count + $student_count + $teacher_count + $staff_count;
													echo (int) $total_student_parent;
													?>
												</p>
												<p class="percent_report"> <?php esc_html_e( 'Users', 'mjschool' ); ?> </p>
												<canvas id="userContainer" width="300" height="250" data-student-count = '<?php echo esc_js($student_count); ?>' data-parent-count = '<?php echo esc_js($parent_count); ?>' data-teacher-count = '<?php echo esc_js($teacher_count); ?>' data-staff-count = '<?php echo esc_js($staff_count); ?>'></canvas>
											</div>
										</div>
										<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_bule_color" ></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Students', 'mjschool' ); ?></p>
											</div>
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_green_color" ></p>
												<p class="mjschool-user-report-label "><?php esc_html_e( 'Parents', 'mjschool' ); ?></p>
											</div>
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_dark_orange_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Teachers', 'mjschool' ); ?></p>
											</div>
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_yellow_color" ></p>
												<p class="mjschool-user-report-label "><?php esc_html_e( 'Support Staff', 'mjschool' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<!-- User report card end. -->
								<!-- Student status report card start. -->
								<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
									<div class="panel mjschool-panel-white mjschool-line-chat">
										<div class="mjschool-panel-heading mjschool-line-chat-p" id="mjschool-line-chat-p">
											<h3 class="mjschool-panel-title mjschool_float_left" ><?php esc_html_e( 'Student Status', 'mjschool' ); ?></h3>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
										</div>
										<div class="mjschool-member-chart">
											<div class="outer">
												<?php
													$user_query = mjschool_approve_student_list();
													$inactive   = 0;
													if ( ! empty( $user_query ) ) {
														$inactive = count( $user_query );
													}
													$approve_student = mjschool_get_all_student_list();
													$approve         = 0;
													if ( ! empty( $approve_student ) ) {
														$approve = count( $approve_student );
													}
												?>
												<canvas id="studentContainer" width="300" height="250" data-inactive="<?php echo esc_js($inactive); ?>" data-active="<?php echo esc_js($approve); ?>"></canvas>
												<p class="percent">
													<?php
													$total_student = $inactive + $approve;
													echo (int) $total_student;
													?>
												</p>
												<p class="percent_report"> <?php esc_html_e( 'Student Status', 'mjschool' ); ?> </p>
											</div>
										</div>
										<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_dark_orange_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Inactive Students', 'mjschool' ); ?></p>
											</div>
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_lime_color" ></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Active Students', 'mjschool' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<!-- Student status report card end. -->
								<!-- Payment status report card Start. -->
								<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard">
									<div class="panel mjschool-panel-white mjschool-line-chat">
										<div class="mjschool-panel-heading mjschool-line-chat-p" id="mjschool-line-chat-p">
											<h3 class="mjschool-panel-title mjschool_float_left" ><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></h3>
											<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
										</div>
										<div class="mjschool-member-chart">
											<div class="outer">
												<?php
													$total            = mjschool_get_payment_amout_by_payment_status( 'total' );
													$paid             = mjschool_get_payment_amout_by_payment_status( 'Fully Paid' );
													$unpaid           = $total - $paid;
													$currency_symbol  = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
													$formatted_amount = number_format( $total, 2, '.', '' );
												?>
												<canvas id="paymentstatusContainer" width="300" height="250" data-paid="<?php echo esc_attr( number_format( $paid, 2, '.', '' ) ); ?>" data-unpaid="<?php echo esc_attr( number_format( $unpaid, 2, '.', '' ) ); ?>" data-symbol="<?php echo esc_attr( html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) ) ); ?>"></canvas>
												<p class="percent">
													<?php
													$currency_output  = mjschool_currency_symbol_position_language_wise( $formatted_amount );
													echo esc_html( $currency_output );
													?>
												</p>
												<p class="percent_report"> <?php esc_html_e( 'Payment Status', 'mjschool' ); ?> </p>
											</div>
										</div>
										<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_paid_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Paid', 'mjschool' ); ?></p>
											</div>
											<div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6  mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_unpaid_color" ></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<!-- Payment status report card end. -->
								<!-- Attendance report card start. -->
								<div class="col-lg-4 col-md-4 col-xl-4 col-sm-4 mjschool-responsive-div-dashboard">
									<div class="panel mjschool-panel-white mjschool-line-chat">
										<div class="row mb-3 mjschool-dashboard-height-card">
											<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-attendance-report-title">
												<h3 class="mjschool-panel-title mjschool_font_20px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></h3>
											</div>
											<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-padding-right-25 mjschool-rtl-padding-dropdown">
												<select class="form-control mjschool-attendance-report-filter mjschool-dash-report-filter" name="date_type" autocomplete="off">
													<option value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
													<option value="this_week"><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
													<option value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
													<option value="this_month" selected><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
													<option value="last_month"><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
													<option value="last_3_month"><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
													<option value="last_6_month"><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
													<option value="last_12_month"><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
													<option value="this_year"><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
													<option value="last_year"><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
												</select>
											</div>
										</div>
										<div class="mjschool-member-chart">
											<div class="outer mjschool-attendance-report-load">
												<?php
													$result     = mjschool_all_date_type_value( 'this_month' );
													$response   = json_decode( $result );
													$start_date = $response[0];
													$end_date   = $response[1];
													$present    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Present' );
													$absent     = mjschool_attendance_data_by_status( $start_date, $end_date, 'Absent' );
													$late       = mjschool_attendance_data_by_status( $start_date, $end_date, 'Late' );
													$halfday    = mjschool_attendance_data_by_status( $start_date, $end_date, 'Half Day' );
												?>
												<canvas id="chartJSContainerattendance" width="300" height="250" data-present="<?php echo esc_js($present); ?>" data-absent="<?php echo esc_js($absent); ?>" data-late="<?php echo esc_js($late); ?>" data-halfday="<?php echo esc_js($halfday); ?>"></canvas>
												<p class="percent">
													<?php
													$attendance = $present + $absent + $late + $halfday;
													echo esc_html( $attendance );
													?>
												</p>
												<p class="percent_report"> <?php esc_html_e( 'Attendance', 'mjschool' ); ?> </p>
											</div>
										</div>
										<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
											<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_green_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Present', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_unpaid_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Absent', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_yellow_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Late', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-6 col-xl-6 col-xs-6 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_bule_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Half Day', 'mjschool' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<!-- Attendance report card end. -->
								<!-- Attendance report card start. -->
								<div class="col-lg-4 col-md-4 col-xl-4 col-sm-4 mjschool-responsive-div-dashboard">
									<div class="panel mjschool-panel-white mjschool-line-chat">
										<div class="row mb-3 mjschool-dashboard-height-card">
											<div class="col-6 col-lg-6 col-md-8 col-xl-6 mjschool-attendance-report-title">
												<h3 class="mjschool-panel-title mjschool_font_20px"><?php esc_html_e( 'Payment', 'mjschool' ); ?></h3>
											</div>
											<div class="col-6 col-lg-6 col-md-6 col-xl-6 mjschool-padding-right-25 mjschool-rtl-padding-dropdown">
												<select class="form-control payment_report_filter mjschool-dash-report-filter" name="date_type" autocomplete="off">
													<option value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
													<option value="this_week"><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
													<option value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
													<option value="this_month" selected><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
													<option value="last_month"><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
													<option value="last_3_month"><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
													<option value="last_6_month"><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
													<option value="last_12_month"><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
													<option value="this_year"><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
													<option value="last_year"><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
												</select>
											</div>
										</div>
										<div class="mjschool-member-chart">
											<div class="outer mjschool-payment-report-load">
												<?php
													$result       = mjschool_all_date_type_value( 'this_month' );
													$response     = json_decode( $result );
													$start_date   = $response[0];
													$end_date     = $response[1];
													$cash_payment = mjschool_get_payment_paid_data_by_date_method( 'Cash', $start_date, $end_date );
													if ( ! empty( $cash_payment ) ) {
														$cashAmount = 0;
														foreach ( $cash_payment as $cash ) {
															$cashAmount += $cash->amount;
														}
													} else {
														$cashAmount = 0;
													}
													$Cheque_payment = mjschool_get_payment_paid_data_by_date_method( 'Cheque', $start_date, $end_date );
													if ( ! empty( $Cheque_payment ) ) {
														$chequeAmount = 0;
														foreach ( $Cheque_payment as $cheque ) {
															$chequeAmount += $cheque->amount;
														}
													} else {
														$chequeAmount = 0;
													}
													$bank_payment = mjschool_get_payment_paid_data_by_date_method( 'Bank Transfer', $start_date, $end_date );
													if ( ! empty( $bank_payment ) ) {
														$bankAmount = 0;
														foreach ( $bank_payment as $bank ) {
															$bankAmount += $bank->amount;
														}
													} else {
														$bankAmount = 0;
													}
													$paypal_payment = mjschool_get_payment_paid_data_by_date_method( 'PayPal', $start_date, $end_date );
													if ( ! empty( $paypal_payment ) ) {
														$paypalAmount = 0;
														foreach ( $paypal_payment as $paypal ) {
															$paypalAmount += $paypal->amount;
														}
													} else {
														$paypalAmount = 0;
													}
													$stripe_payment = mjschool_get_payment_paid_data_by_date_method( 'Stripe', $start_date, $end_date );
													if ( ! empty( $stripe_payment ) ) {
														$stripeAmount = 0;
														foreach ( $stripe_payment as $stripe ) {
															$stripeAmount += $stripe->amount;
														}
													} else {
														$stripeAmount = 0;
													}
													$Total_amount     = $cashAmount + $chequeAmount + $bankAmount + $paypalAmount + $stripeAmount;
													$currency_symbol  = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
												?>
												<canvas id="chartJSContainerpayment" width="300" height="250" data-cash="<?php echo esc_js($cashAmount); ?>" data-cheque="<?php echo esc_js($chequeAmount); ?>" data-bank="<?php echo esc_js($bankAmount); ?>" data-paypal="<?php echo esc_js($paypalAmount); ?>" data-stripe="<?php echo esc_js($stripeAmount); ?>" data-symbol="<?php echo esc_js(html_entity_decode(mjschool_get_currency_symbol(get_option('mjschool_currency_code')))); ?>"></canvas>
												<p class="percent">
													<?php
													$formatted_amount = number_format( $Total_amount, 2, '.', '' );
													$currency_output  = mjschool_currency_symbol_position_language_wise( $formatted_amount );
													echo esc_html( $currency_output );
													?>
												</p>
												<p class="percent_report"> <?php esc_html_e( 'Payment Report', 'mjschool' ); ?> </p>
											</div>
										</div>
										<div class="row ps-3 mjschool-padding-top-10px mjschool-users-label-div mt-4 mjschool-rtl-dashboard-label-setup">
											<div class="col-4 col-sm-4 col-md-6 col-lg-4 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_gray_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'PayPal', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-4 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_purple_color" ></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Stripe', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-4 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_light_orange_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Cash', 'mjschool' ); ?></p>
											</div>
											<div class="col-4 col-sm-4 col-md-6 col-lg-4 col-xl-4 col-xs-4 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_sky_color" ></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Cheque', 'mjschool' ); ?></p>
											</div>
											<div class="col-8 col-sm-4 col-md-6 col-lg-8 col-xl-8 col-xs-8 mjschool-users-report-label ps-2">
												<p class="mjschool-users-report-dot-color mjschool_yellow_color"></p>
												<p class="mjschool-user-report-label"><?php esc_html_e( 'Bank Transfer', 'mjschool' ); ?></p>
											</div>
										</div>
									</div>
								</div>
								<!-- Attendance report card end. -->
								<!-- Fees payment details report start.-->
								<div class="col-lg-4 col-md-4 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white mjschool-admmision-div mjschool-line-chat">
										<div class="mjschool-panel-heading mjschool-line-chat-p" id="mjschool-line-chat-p">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Fees Payment Details', 'mjschool' ); ?></h3>
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=feespaymentlist' ) ); ?>">
												<img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>">
											</a>
										</div>
										<div class="mjschool-panel-body">
											<div class="events1">
												<?php
												$obj_feespayment  = new Mjschool_Feespayment();
												$i                = 0;
												$feespayment_data = $obj_feespayment->mjschool_get_five_fees();
												if ( ! empty( $feespayment_data ) ) {
													foreach ( $feespayment_data as $retrieved_data ) {
														if ( $i === 0 ) {
															$color_class_css = 'mjschool-assign-bed-color0';
														} elseif ( $i === 1 ) {
															$color_class_css = 'mjschool-assign-bed-color1';
														} elseif ( $i === 2 ) {
															$color_class_css = 'mjschool-assign-bed-color2';
														} elseif ( $i === 3 ) {
															$color_class_css = 'mjschool-assign-bed-color3';
														} elseif ( $i === 4 ) {
															$color_class_css = 'mjschool-assign-bed-color4';
														}
														?>
														<div class="mjschool-fees-payment-height calendar-event">
															<p class="mjschool-fees-payment-padding-top-0 mjschool-remainder-title Bold viewbedlist mjschool-show-task-event mjschool-date-font-size" id="<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>" model="Feespayment Details">
																<label class="mjschool-date-assign-bed-label">
																	<?php
																	$formatted_amount = number_format( $retrieved_data->total_amount, 2, '.', '' );
																	$currency_output  = mjschool_currency_symbol_position_language_wise( $formatted_amount );
																	echo esc_html( $currency_output );
																	?>
																</label>
																<span class=" <?php echo esc_attr( $color_class_css ); ?>"></span>
															</p>
															<p class="mjschool-remainder-date mjschool-assign-bed-name mjschool-assign-bed-name-size">
																<?php
																$student_data = get_userdata( $retrieved_data->student_id );
																if ( ! empty( $student_data ) ) {
																	echo esc_html( $student_data->display_name );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
															</p>
															<p class="mjschool-remainder-date mjschool-assign-bed-date mjschool-assign-bed-name-size"> <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> </p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														<div class="col-md-12 mjschool-dashboard-btn">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addpaymentfee' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<!-- Fees payment details report end. -->
							</div>
							<!-- Chart and Fees Payment Row Div.  -->
							<!-- Calendar And Chart Row.  -->
							<div class="row calander-chart-div">
								<div class="col-lg-12 col-md-12 col-xs-12 col-sm-12">
									<div class="mjschool-calendar panel">
										<div class="row mjschool-panel-heading activities">
											<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
												<h3 class="mjschool-panel-title mjschool-calander-heading-title-width"><?php esc_html_e( 'Calendar', 'mjschool' ); ?></h3>
											</div>
											<div class="mjschool-cal-py col-sm-12 col-md-8 col-lg-8 col-xl-8 mjschool-celender-dot-div">
												<div class="mjschool-card-head">
													<ul class="mjschool-cards-indicators mjschool-right">
														<!--Set calendar-header event-List Start. -->
														<li><span class="mjschool-indic mjschool-blue-indic"></span> <?php esc_html_e( 'Holiday', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-yellow-indic"></span> <?php esc_html_e( 'Notice', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-perple-indic"></span> <?php esc_html_e( 'Exam', 'mjschool' ); ?></li>
														<li><span class="mjschool-indic mjschool-light-blue-indic"></span> <?php esc_html_e( 'Event', 'mjschool' ); ?></li>
														<!--Set calendar-header event-List End. -->
													</ul>
												</div>
											</div>
										</div>
										<div class="mjschool-cal-py mjschool-calender-margin-top">
											<div id="calendar"></div>
										</div>
									</div>
								</div>
							</div>
							<!-- Calendar And Chart Row.  -->
							<!-- Income expence report start. -->
							<div class="row mjschool-menu-row mjschool-dashboard-content-rs"><!-- Row Div Start. -->
								<div class="col-lg-12 col-md-12 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white mjschool-income-expence-chart">
										<div class="row">
											<div class="col-md-8 input">
												<div class="mjschool-panel-heading">
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'Income-Expense Report', 'mjschool' ); ?></h3>
												</div>
											</div>
											<div class="col-md-2 mb-3 col-6 input mjschool-margin-top-20px mjschool-margin-left-20px mjschool-margin-rtl-30px mjschool-responsive-months mjschool-dashboard-payment-report-padding-left">
												<select id="month" name="month" class="mjschool-line-height-30px form-control dash_month_load mjschool_height_35px" >
													<option value="all_month"><?php esc_html_e( 'All Month', 'mjschool' ); ?></option>
													<?php
													$month = array(
														'1'  => esc_html__( 'January', 'mjschool' ),
														'2'  => esc_html__( 'February', 'mjschool' ),
														'3'  => esc_html__( 'March', 'mjschool' ),
														'4'  => esc_html__( 'April', 'mjschool' ),
														'5'  => esc_html__( 'May', 'mjschool' ),
														'6'  => esc_html__( 'June', 'mjschool' ),
														'7'  => esc_html__( 'July', 'mjschool' ),
														'8'  => esc_html__( 'August', 'mjschool' ),
														'9'  => esc_html__( 'September', 'mjschool' ),
														'10' => esc_html__( 'October', 'mjschool' ),
														'11' => esc_html__( 'November', 'mjschool' ),
														'12' => esc_html__( 'December', 'mjschool' ),
													);
													foreach ( $month as $key => $value ) {
														$selected = ( date( 'm' ) === $key ? ' selected' : '' );
														echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>' . "\n";
													}
													?>
												</select>
											</div>
											<div class="col-md-2 mb-3 col-6 input mjschool-margin-top-20px mjschool-margin-left-20px mjschool-responsive-months mjschool-dashboard-payment-report-padding">
												<select name="year" class="mjschool-line-height-30px form-control mjschool-dash-year-load mjschool_height_35px" >
													<?php
													$current_year = date( 'Y' );
													$min_year     = $current_year - 10;
													for ( $i = $current_year; $i >= $min_year; $i-- ) {
														$year_array[ $i ] = $i;
														$selected         = ( $current_year === $i ? ' selected' : '' );
														echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>' . "\n";
													}
													?>
												</select>
											</div>
										</div>
										<div class="mjschool-panel-body class_padding">
											<div class="events1" id="income_expence_report_append">
												<?php
												$month         = date( 'm' );
												$current_month = date( 'm' );
												$current_year  = date( 'Y' );
												$dataPoints_2  = array();
												if ( $month === '2' ) {
													$max_d = '29';
												} elseif ( $month === '4' || $month === '6' || $month === '9' || $month === '11' ) {
													$max_d = '30';
												} else {
													$max_d = '31';
												}
												for ( $d = 1; $d <= $max_d; $d++ ) {
													$time = mktime( 12, 0, 0, $month, $d, $current_year );
													if ( date( 'm', $time ) === $month ) {
														$date_list[] = date( 'Y-m-d', $time );
													}
													$day_date[]       = date( 'd', $time );
													$month_first_date = min( $date_list );
													$month_last_date  = max( $date_list );
												}
												$month = array();
												$i     = 1;
												foreach ( $day_date as $value ) {
													$month[ $i ] = $value;
													++$i;
												}
												array_push( $dataPoints_2, array( esc_html__( 'Day', 'mjschool' ), esc_html__( 'Income', 'mjschool' ), esc_html__( 'Expense', 'mjschool' ), esc_html__( 'Net Profit', 'mjschool' ) ) );
												$expense_array   = array();
												$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
												foreach ( $month as $key => $value ) {
													$result = mjschool_get_income_expense_by_date($current_year, $current_month, $value, 'income');
													$result1 = mjschool_get_income_expense_by_date($current_year, $current_month, $value, 'expense');
													$expense_yearly_amount = 0;
													foreach ( $result1 as $expense_entry ) {
														$all_entry = json_decode( $expense_entry->entry );
														$amount    = 0;
														foreach ( $all_entry as $entry ) {
															$amount += $entry->amount;
														}
														$expense_yearly_amount += $amount;
													}
													$expense_amount       = $expense_yearly_amount;
													$income_yearly_amount = 0;
													foreach ( $result as $income_entry ) {
														$all_entry = json_decode( $income_entry->entry );
														$amount    = 0;
														foreach ( $all_entry as $entry ) {
															$amount += $entry->amount;
														}
														$income_yearly_amount += $amount;
													}
													$income_amount    = $income_yearly_amount;
													$expense_array[]  = $expense_amount;
													$income_array[]   = $income_amount;
													$net_profit_array = $income_amount - $expense_amount;
													array_push( $dataPoints_2, array( $value, $income_amount, $expense_amount, $net_profit_array ) );
												}
												$income_filtered  = array_filter( $income_array );
												$expense_filtered = array_filter( $expense_array );
												$new_array        = $dataPoints_2;
												if ( ! empty( $income_filtered ) || ! empty( $expense_filtered ) ) {
													$new_currency_symbol = html_entity_decode( $currency_symbol );
													$labels       = array();
													$income_data  = array();
													$expense_data = array();
													$profit_data  = array();
													foreach ( $new_array as $index => $row ) {
														if ( $index === 0 ) {
															continue; // Skip header row.
														}
														$labels[]       = $row[0];
														$income_data[]  = $row[1];
														$expense_data[] = $row[2];
														$profit_data[]  = $row[3];
													}
													$chart_data = [
														'labels'       => $labels,
														'income'       => $income_data,
														'expense'      => $expense_data,
														'profit'       => $profit_data,
														'currency'     => $currency_symbol
													];
													?>
													<canvas id="mjschool-barchart-material" class="mjschool-barchart-material mjschool_chart_430pxmjschool_chart_430px" data-chart='<?php echo wp_json_encode( $chart_data ); ?>'></canvas>
													<?php
												} else {
													
													?>
													<div class="mjschool-calendar-event-new">
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
													</div>
													<?php
													
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-12 col-md-12 col-xs-12 col-sm-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white mjschool-income-expence-chart">
										<div class="row">
											<div class="col-md-8 input">
												<div class="mjschool-panel-heading">
													<h3 class="mjschool-panel-title"><?php esc_html_e( 'Fees Payment Report', 'mjschool' ); ?></h3>
												</div>
											</div>
											<div class="col-md-2 mb-3 col-6 input mjschool-margin-top-20px mjschool-margin-left-20px mjschool-margin-rtl-30px mjschool-responsive-months mjschool-dashboard-payment-report-padding-left">
												<select id="month" name="month" class="mjschool-line-height-30px form-control fees_month_load mjschool_height_35px" >
													<option value="all_month"><?php esc_html_e( 'All Month', 'mjschool' ); ?></option>
													<?php
													$month = array(
														'1'  => esc_html__( 'January', 'mjschool' ),
														'2'  => esc_html__( 'February', 'mjschool' ),
														'3'  => esc_html__( 'March', 'mjschool' ),
														'4'  => esc_html__( 'April', 'mjschool' ),
														'5'  => esc_html__( 'May', 'mjschool' ),
														'6'  => esc_html__( 'June', 'mjschool' ),
														'7'  => esc_html__( 'July', 'mjschool' ),
														'8'  => esc_html__( 'August', 'mjschool' ),
														'9'  => esc_html__( 'September', 'mjschool' ),
														'10' => esc_html__( 'October', 'mjschool' ),
														'11' => esc_html__( 'November', 'mjschool' ),
														'12' => esc_html__( 'December', 'mjschool' ),
													);
													foreach ( $month as $key => $value ) {
														$selected = ( date( 'm' ) === $key ? ' selected' : '' );
														echo '<option value="' . esc_attr( $key ) . '"' . esc_attr( $selected ) . '>' . esc_html( $value ) . '</option>' . "\n";
													}
													?>
												</select>
											</div>
											<div class="col-md-2 mb-3 col-6 input mjschool-margin-top-20px mjschool-margin-left-20px mjschool-responsive-months mjschool-dashboard-payment-report-padding">
												<select name="year" class="mjschool-line-height-30px form-control fees_year_load mjschool_height_35px">
													<?php
													$current_year = date( 'Y' );
													$min_year     = $current_year - 10;
													for ( $i = $current_year; $i >= $min_year; $i-- ) {
														$year_array[ $i ] = $i;
														$selected         = ( $current_year === $i ? ' selected' : '' );
														echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>' . "\n";
													}
													?>
												</select>
											</div>
										</div>
										<div class="mjschool-panel-body class_padding">
											<div class="events1" id="fees_report_append">
												<?php
												$month              = date( 'm' );
												$current_month      = date( 'm' );
												$current_year       = date( 'Y' );
												$dataPoints_payment = array();
												if ( $month === '2' ) {
													$max_d = '29';
												} elseif ( $month === '4' || $month === '6' || $month === '9' || $month === '11' ) {
													$max_d = '30';
												} else {
													$max_d = '31';
												}
												for ( $d = 1; $d <= $max_d; $d++ ) {
													$time = mktime( 12, 0, 0, $month, $d, $current_year );
													if ( date( 'm', $time ) === $month ) {
														$date_list[] = date( 'Y-m-d', $time );
													}
													$day_date_1[]     = date( 'd', $time );
													$month_first_date = min( $date_list );
													$month_last_date  = max( $date_list );
												}
												$month = array();
												$i     = 1;
												foreach ( $day_date_1 as $value ) {
													$month[ $i ] = $value;
													++$i;
												}
												array_push( $dataPoints_payment, array( esc_html__( 'Day', 'mjschool' ), esc_html__( 'Payment', 'mjschool' ) ) );
												$payment_array   = array();
												$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
												foreach ( $month as $key => $value ) {
													$result = mjschool_get_fee_payment_history_by_date($current_year, $current_month, $value);
													$amount = 0;
													foreach ( $result as $payment_entry ) {
														$amount += $payment_entry->amount;
													}
													$payment_amount  = $amount;
													$payment_array[] = $payment_amount;
													array_push( $dataPoints_payment, array( $value, $payment_amount ) );
												}
												$payment_filtered = array_filter( $payment_array );
												$new_array        = $dataPoints_payment;
												if ( ! empty( $payment_filtered ) ) :
													$labels = array_column( $new_array, 0 );
													$values = array_column( $new_array, 1 );

													// Remove header
													array_shift( $labels );
													array_shift( $values );
													?>
													<canvas id="mjschool-payment-bar-material" class="mjschool-payment-bar-material mjschool_chart_430pxmjschool_chart_430px" data-labels='<?php echo json_encode( $labels ); ?>' data-values='<?php echo json_encode( $values ); ?>' data-currency="<?php echo esc_attr( $currency_symbol ); ?>" data-color="<?php echo esc_js( get_option( 'mjschool_system_color_code' ) ); ?>"></canvas>
												<?php else : ?>
													<div class="mjschool-calendar-event-new">
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								</div>
							</div><!-- Row Div Start.  -->
							<!-- Class and Exam List Row.  -->
							<div class="row">
								<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event priscription">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Class', 'mjschool' ); ?></h3>
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
										</div>
										<div class="mjschool-panel-body class_padding">
											<div class="events1">
												<?php
												$class_data = mjschool_class_dashboard();
												$i          = 0;
												if ( ! empty( $class_data ) ) {
													foreach ( $class_data as $retrieved_data ) {
														$class_id = $retrieved_data->class_id;
														$mjschool_user = count(get_users(array(
															'meta_key' => 'class_name',
															'meta_value' => $class_id
														 ) ) );
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
														}
														?>
														<div class="row mjschool-group-list-record mjschool-profile-image-class mjschool-class-record-height">
															<div class="mjschool-cursor-pointer col-sm-2 col-md-2 col-lg-2 col-xl-2 <?php echo esc_attr( $color_class_css ); ?> mjschool-remainder-title mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment mjschool-class-color0" id="<?php echo esc_attr( $retrieved_data->class_id ); ?>" model="Class Details">
																<img class="mjschool-class-image-1 " src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-class.png"); ?>">
															</div>
															<div class="d-flex align-items-center col-sm-7 col-md-7 col-lg-7 col-xl-7 mjschool-group-list-record-col-img">
																<div class="mjschool-cursor-pointer mjschool-class-font-color mjschool-group-list-group-name mjschool-remainder-title-pr Bold viewdetail mjschool-show-task-event" id="<?php echo esc_attr( $retrieved_data->class_id ); ?>" model="Class Details">
																	<span><?php echo esc_attr( $retrieved_data->class_name ); ?></span>
																</div>
															</div>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 justify-content-end d-flex align-items-center mjschool-group-list-record-col-count">
																<div class="mjschool-group-list-total-group">
																	<?php
																	echo esc_html( $mjschool_user ) . ' ';
																	esc_html_e( 'Out Of', 'mjschool' );
																	echo ' ' . esc_html( $retrieved_data->class_capacity );
																	?>
																</div>
															</div>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=addclass' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Class', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event operation">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Exam List', 'mjschool' ); ?></h3>
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
										</div>
										<div class="mjschool-panel-body">
											<div class="events">
												<?php
												$exam     = new Mjschool_exam();
												$examdata = $exam->mjschool_exam_list_for_dashboard();
												$i        = 0;
												if ( ! empty( $examdata ) ) {
													foreach ( $examdata as $retrieved_data ) {
														$cid = $retrieved_data->class_id;
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
														}
														?>
														<div class="mjschool-calendar-event-p calendar-event view-complaint">
															<p class="mjschool-cursor-pointer mjschool-exam-list-img mjschool-show-task-event <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" model="Exam Details">
																<img class="mjschool-class-image-1 mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>">
															</p>
															<p class="mjschool-cursor-pointer mjschool-exam-remainder-title-pr mjschool-remainder-title-pr Bold mjschool-view-priscription mjschool-show-task-event" id="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" model="Exam Details">
																<?php echo esc_html( $retrieved_data->exam_name ); ?>&nbsp;&nbsp;
																<span class="smgt_exam_start_date"> <?php echo esc_html( get_the_title( $retrieved_data->exam_term ) ); ?>&nbsp;|&nbsp;<?php echo esc_html( mjschool_get_class_name( $cid ) ); ?></span>
															</p>
															<p class="mjschool-exam-remainder-title-pr mjschool-description-line">
																<span class="smgt_activity_date" id="smgt_start_date_end_date"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_start_date ) ); ?>&nbsp;|&nbsp;<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->exam_end_date ) ); ?></span>
															</p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_exam&tab=addexam' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Exam', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Notice', 'mjschool' ); ?></h3>
											
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											
										</div>
										<div class="mjschool-panel-body">
											<div class="events">
												<?php
												$args['post_type']      = 'notice';
												$args['posts_per_page'] = 4;
												$args['post_status']    = 'public';
												$q                      = new WP_Query();
												$retrieve_class_data         = $q->query( $args );
												$format                 = get_option( 'date_format' );
												$i                      = 0;
												if ( ! empty( $retrieve_class_data ) ) {
													foreach ( $retrieve_class_data as $retrieved_data ) {
														if ( $i === 0 ) {
															$color_class_css = 'mjschool-notice-color0';
														} elseif ( $i === 1 ) {
															$color_class_css = 'mjschool-notice-color1';
														} elseif ( $i === 2 ) {
															$color_class_css = 'mjschool-notice-color2';
														} elseif ( $i === 3 ) {
															$color_class_css = 'mjschool-notice-color3';
														} elseif ( $i === 4 ) {
															$color_class_css = 'mjschool_notice_color4';
														}
														?>
														<div class="calendar-event mjschool-notice-div <?php echo esc_attr( $color_class_css ); ?>">
															<div class="mjschool-notice-div-contant mjschool-profile-image-prescription">
																<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 notice_description_div">
																	<p class="mjschool-cursor-pointer mjschool-remainder-title Bold viewdetail mjschool-notice-descriptions mjschool-show-task-event notice_heading mjschool-notice-content-rs mjschool-width-100px" id="<?php echo esc_attr( $retrieved_data->ID ); ?>" model="Noticeboard Details" >
																		<label class="mjschool-cursor-pointer notice_heading_label notice_heading">
																			<?php echo esc_html( $retrieved_data->post_title ); ?>
																		</label>
																		<a href="#" class="notice_date_div">
																			<?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'start_date', true ) ) ); ?> &nbsp;|&nbsp; <?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'end_date', true ) ) ); ?>
																		</a>
																	</p>
																	<p class="mjschool-cursor-pointer mjschool-remainder-title viewdetail mjschool-notice-descriptions mjschool-width-100px" ><?php echo esc_html( $retrieved_data->post_content ); ?></p>
																</div>
															</div>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=addnotice' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Notice', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white massage">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Event List', 'mjschool' ); ?></h3>
											
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=eventlist' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											
										</div>
										<div class="mjschool-panel-body">
											<div class="events mjschool-notice-content-div">
												<?php
												$event_data = $obj_event->mjschool_get_all_event_for_dashboard();
												$i          = 0;
												if ( ! empty( $event_data ) ) {
													foreach ( $event_data as $retrieved_data ) {
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
														}
														?>
														<div class="calendar-event mjschool-profile-image-class">
															<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" model="Event Details">
																
																<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png"); ?>">
																
															</p>
															<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool_color_dark" id="<?php echo esc_attr( $retrieved_data->event_id ); ?>" model="Event Details">
																<?php echo esc_html( $retrieved_data->event_title ); ?>
															</p>
															<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <label class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?></label> </p>
															<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																<?php
																$strlength = strlen( $retrieved_data->description );
																if ( $strlength > 90 ) {
																	echo esc_html( substr( $retrieved_data->description, 10, 90 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->description );
																}
																?>
															</p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG);?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_event&tab=add_event' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Add Event', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event priscription">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Notification', 'mjschool' ); ?></h3>
											
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											
										</div>
										<div class="mjschool-panel-body mjschool-message-rtl-css">
											<div class="events1">
												<?php
												$notification_data = mjschool_notification_dashboard();
												$i                 = 0;
												if ( ! empty( $notification_data ) ) {
													foreach ( $notification_data as $retrieved_data ) {
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
														}
														?>
														<div class="calendar-event mjschool-profile-image-class">
															<p class="mjschool-cursor-pointer mjschool-remainder-title-pr Bold mjschool-view-priscription mjschool-show-task-event mjschool-class-tag <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->notification_id ); ?>" model="Notification Details">
																
																<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notification.png"); ?>">
																
															</p>
															<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-card-content-width mjschool-remainder-title-pr mjschool-view-priscription mjschool-show-task-event mjschool-class-width mjschool-padding-top-card-content mjschool_color_dark" id="<?php echo esc_attr( $retrieved_data->notification_id ); ?>" model="Notification Details" >
																<?php echo esc_html( $retrieved_data->title ); ?>
															</p>
															<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <label class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?></label> </p>
															<p class="mjschool-remainder-title-pr mjschool-card-content-width mjschool-view-priscription mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top"> <?php echo esc_html( $retrieved_data->message ); ?> </p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notification&tab=addnotification' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Notification', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event operation">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Holiday List', 'mjschool' ); ?></h3>
											
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											
										</div>
										<div class="mjschool-panel-body">
											<div class="events mjschool-rtl-notice-css">
												<?php
												$holidaydata = mjschool_holiday_dashboard();
												$i           = 0;
												if ( ! empty( $holidaydata ) ) {
													foreach ( $holidaydata as $retrieved_data ) {
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
														}
														if ( $retrieved_data->status === 0 ) {
															?>
															<div class="calendar-event mjschool-profile-image-class">
																<p class="mjschool-cursor-pointer mjschool-remainder-title mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->holiday_id ); ?>" model="holiday Details">
																	
																	<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-holiday.png"); ?>">
																	
																</p>
																<p class="mjschool-cursor-pointer mjschool-holiday-list-description-res mjschool-remainder-title-pr mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-holiday-width mjschool_color_dark" id="<?php echo esc_attr( $retrieved_data->holiday_id ); ?>" model="holiday Details">
																	<?php echo esc_html( $retrieved_data->holiday_title ); ?> <span class="date_div_color"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?> | <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?></span>
																</p>
																<p class="mjschool-remainder-title-pr mjschool-holiday-list-description-res mjschool-view-priscription mjschool-holiday-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																	<?php echo esc_html( $retrieved_data->description ); ?>
																</p>
															</div>
															<?php
														}
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_holiday&tab=addholiday' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Holiday', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-sm-12 col-md-6 col-lg-6 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white massage">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Message', 'mjschool' ); ?></h3>
											
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
											
										</div>
										<div class="mjschool-panel-body">
											<div class="events mjschool-notice-content-div">
												<?php
												$max = 5;
												if ( isset( $_GET['pg'] ) ) {
													$p = absint(wp_unslash($_GET['pg']));
												} else {
													$p = 1;
												}
												$limit        = ( $p - 1 ) * $max;
												$post_id      = 0;
												$message_data = mjschool_get_inbox_message( get_current_user_id(), $limit, $max );
												$i            = 0;
												if ( ! empty( $message_data ) ) {
													foreach ( $message_data as $retrieved_data ) {
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
														}
														?>
														<div class="calendar-event mjschool-profile-image-class">
															<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->message_id ); ?>" model="Message Details">
																
																<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png"); ?>">
																
															</p>
															<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool_color_dark" id="<?php echo esc_attr( $retrieved_data->message_id ); ?>" model="Message Details">
																<?php echo esc_html( $retrieved_data->subject ); ?>
															</p>
															<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width"> <label class="mjschool-label-for-date"><?php echo esc_attr( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?></label> </p>
															<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top">
																<?php
																$strlength = strlen( $retrieved_data->message_body );
																if ( $strlength > 90 ) {
																	echo esc_html( substr( $retrieved_data->message_body, 10, 90 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->message_body );
																}
																?>
															</p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=compose' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'ADD Message', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 mjschool-responsive-div-dashboard mjschool-precription-padding-left">
									<div class="panel mjschool-panel-white event operation">
										<div class="mjschool-panel-heading">
											<h3 class="mjschool-panel-title"><?php esc_html_e( 'Homework List', 'mjschool' ); ?></h3>
											<a class="mjschool-page-link" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok' ) ); ?>"><img class="mjschool-vertical-align-unset" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-redirect.png"); ?>"></a>
										</div>
										<div class="mjschool-panel-body">
											<div class="events mjschool-rtl-notice-css">
												<?php
												$homework_data = mjschool_get_homework_data_for_dashboard();
												$i             = 0;
												if ( ! empty( $homework_data ) ) {
													foreach ( $homework_data as $retrieved_data ) {
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
														}
														?>
														<div class="calendar-event mjschool-profile-image-class">
															<p class="mjschool-cursor-pointer mjschool-class-tag Bold save1 mjschool-show-task-event mjschool-show-task-event-list mjschool-profile-image-appointment <?php echo esc_attr( $color_class_css ); ?>" id="<?php echo esc_attr( $retrieved_data->homework_id ); ?>" model="homework Details">
																<img class="mjschool-class-image mjschool_dashboard_cards_fix" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>">
															</p>
															<p class="mjschool-cursor-pointer mjschool-padding-top-5px-res mjschool-remainder-title-pr mjschool-card-content-width mjschool-show-task-event mjschool-padding-top-card-content mjschool-view-priscription mjschool-class-width mjschool-homework-dashboard-rtl mjschool_color_dark" id="<?php echo esc_attr( $retrieved_data->homework_id ); ?>" model="homework Details">
																<?php echo esc_html( $retrieved_data->title ); ?>
															</p>
															<p class="mjschool-remainder-date-pr mjschool-date-background mjschool-class-width mjschool-homework-date-rtl"> <label class="mjschool-label-for-date"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?></label> </p>
															<p class="mjschool-remainder-title-pr mjschool-view-priscription mjschool-card-content-width mjschool-class-width mjschool-assign-bed-name1 mjschool-card-margin-top mjschool-homework-dashboard-rtl">
																<?php echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_name, $retrieved_data->section_id ) ); ?>
															</p>
														</div>
														<?php
														++$i;
													}
												} else {
													?>
													<div class="mjschool-calendar-event-new">
														
														<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
														
														<div class="col-md-12 mjschool-dashboard-btn mjschool-padding-top-30px">
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework' ) ); ?>" class="btn mjschool-save-btn mjschool-event-for-alert mjschool-line-height-31px"><?php esc_html_e( 'Add Homework', 'mjschool' ); ?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<!-- End dashboard content div. -->
		</div>
		<!-- Footer Part Start. -->
		<footer class='mjschool-footer'>
			<p> <?php echo esc_html( get_option( 'mjschool_footer_description' ) ); ?> </p>
		</footer>
		<!-- Footer Part End. -->
	</body>
	<!-- Body part end. -->
</html>