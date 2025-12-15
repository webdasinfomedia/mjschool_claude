<?php
/**
 * Access Rights Management for Parent Role.
 *
 * Handles saving and retrieving access rights for the Parent role
 * within the MJSchool plugin. Allows admins to define CRUD (Create, Read,
 * Update, Delete) permissions for different menu items.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/access-rights
 * @since      1.0.0
 * 
 */
if (!defined( 'ABSPATH' ) ) {
	die();
}

// Check nonce for parent access rights tab.
if ( isset( $_GET['tab'] ) ) {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_access_rights_tab' ) ) {
       wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
    }
}
/**
 * Handles saving and updating access rights for the Parent role.
 *
 * This code retrieves the existing access rights for Parent from the options table,
 * checks if the form has been submitted, and then updates the role access rights based
 * on the form input ($_REQUEST). Each menu has its own permissions like add, edit, view, delete.
 *
 * @since 1.0.0
 */
$result = get_option( 'mjschool_access_right_parent' );
if ( isset( $_POST['save_access_right'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
	// Check save parent access rights nonce.
	if ( wp_verify_nonce( $nonce, 'mjschool_save_parent_access_right_nonce' ) ) {
		$role_access_right = array();
		$result = get_option( 'mjschool_access_right_parent' );
		$role_access_right['parent'] = array(
			'teacher'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset( $_REQUEST['teacher_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['teacher_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['teacher_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['teacher_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['teacher_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_delete'] ) ) : 0,
			),
			'student'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-student-icon.png' ) ),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset( $_REQUEST['student_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['student_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['student_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['student_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['student_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_delete'] ) ) : 0,
			),
			'parent'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-parents.png' ) ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_delete'] ) ) : 0,
			),
			'notification'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ) ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_delete'] ) ) : 0,
			),
			'subject'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ) ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_delete'] ) ) : 0,
			),
			'leave'             => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_delete'] ) ) : 0,
			),
			'schedule'          => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ) ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_delete'] ) ) : 0,
			),
			'attendance'        => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ) ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_delete'] ) ) : 0,
			),
			'exam'              => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ) ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_delete'] ) ) : 0,
			),
			'hostel'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ) ),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset( $_REQUEST['hostel_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['hostel_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['hostel_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['hostel_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['hostel_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_delete'] ) ) : 0,
			),
			'document'          => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ) ),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset( $_REQUEST['document_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['document_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['document_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['document_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['document_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_delete'] ) ) : 0,
			),
			'homework'          => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-homework.png' ) ),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset( $_REQUEST['homework_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['homework_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['homework_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['homework_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['homework_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_delete'] ) ) : 0,
			),
			'manage_marks'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-mark-manage.png' ) ),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage_marks',
				'own_data'   => isset( $_REQUEST['manage_marks_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['manage_marks_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['manage_marks_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['manage_marks_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['manage_marks_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_delete'] ) ) : 0,
			),
			'feepayment'        => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ) ),
				'menu_title' => 'Fee Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset( $_REQUEST['feepayment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['feepayment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['feepayment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['feepayment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['feepayment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_delete'] ) ) : 0,
			),
			'payment'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-payment.png' ) ),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset( $_REQUEST['payment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['payment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['payment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['payment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['payment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_delete'] ) ) : 0,
			),
			'transport'         => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ) ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_delete'] ) ) : 0,
			),
			'notice'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ) ),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset( $_REQUEST['notice_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['notice_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notice_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notice_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['notice_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_delete'] ) ) : 0,
			),
			'message'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ) ),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset( $_REQUEST['message_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['message_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['message_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['message_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['message_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_delete'] ) ) : 0,
			),
			'holiday'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-holiday.png' ) ),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset( $_REQUEST['holiday_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['holiday_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['holiday_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['holiday_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['holiday_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_delete'] ) ) : 0,
			),
			'library'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ) ),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset( $_REQUEST['library_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['library_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['library_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['library_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['library_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_delete'] ) ) : 0,
			),
			'certificate'       => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ) ),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset( $_REQUEST['certificate_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['certificate_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['certificate_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['certificate_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['certificate_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_delete'] ) ) : 0,
			),
			'account'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-account.png' ) ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_delete'] ) ) : 0,
			),
			'class_room'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-class.png' ) ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				"own_data"   => isset( $_REQUEST['class_room_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_own_data'] ) ) : 0,
				"add"        => isset( $_REQUEST['class_room_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_add'] ) ) : 0,
				"edit"       => isset( $_REQUEST['class_room_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_edit'] ) ) : 0,
				"view"       => isset( $_REQUEST['class_room_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_view'] ) ) : 0,
				"delete"     => isset( $_REQUEST['class_room_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_delete'] ) ) : 0
			),
			'report'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ) ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_delete'] ) ) : 0,
			),
			'virtual_classroom' => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ) ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_delete'] ) ) : 0,
			),
			'event'             => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ) ),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset( $_REQUEST['event_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['event_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['event_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['event_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['event_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_delete'] ) ) : 0,
			),
		);
		$result = update_option( 'mjschool_access_right_parent', $role_access_right );
		$nonce = wp_create_nonce( 'mjschool_access_rights_tab' );
		wp_redirect( admin_url() . 'admin.php?page=mjschool_access_right&tab=Parent&_wpnonce='.esc_attr( $nonce ).'&message=1' );
		die();
	}
	else{
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$access_right = get_option( 'mjschool_access_right_parent' );
/**
 * Display success messages for Parent role access rights.
 *
 * This code retrieves the saved access rights for the Parent role
 * and shows a success notice if the access rights were updated.
 *
 * @since 1.0.0
 */
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Parent Access Right Updated Successfully.', 'mjschool' );
		break;
}
if ( $message ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
		<p> <?php echo esc_html( $message_string ); ?> </p>
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
		</button>
	</div>
	<?php
}
?>
<div><!--- PANEL WHITE DIV START. -->
	<div class="header">
		<h3 class="mjschool-first-header"><?php echo esc_html( esc_attr__( 'Parent Access Right', 'mjschool' ) ); ?></h3>
	</div>
	<div class="mjschool-panel-body" id="mjschool-rs-access-pl-15px"> <!--- PANEL BODY DIV START. -->
		<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-access-right-form">
			<div class="row mjschool-access-right-header">
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Menu', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2"><?php esc_html_e( 'OwnData', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2"><?php esc_html_e( 'View', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Add', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Edit', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Delete ', 'mjschool' ); ?></div>
			</div>
			<div class="mjschool-access-right-menu row mjschool-border-bottom-0">
				<!-- Class Routine module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Class Routine', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['schedule']['own_data'], 1 ); ?> value="1" name="schedule_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['schedule']['view'], 1 ); ?> value="1" name="schedule_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['schedule']['add'], 1 ); ?> value="1" name="schedule_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['schedule']['edit'], 1 ); ?> value="1" name="schedule_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['schedule']['delete'], 1 ); ?> value="1" name="schedule_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Class Routine module code end. -->
				<!-- Virtual Classroom module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Virtual Classroom', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['virtual_classroom']['own_data'], 1 ); ?> value="1" name="virtual_classroom_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['virtual_classroom']['view'], 1 ); ?> value="1" name="virtual_classroom_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['virtual_classroom']['add'], 1 ); ?> value="1" name="virtual_classroom_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['virtual_classroom']['edit'], 1 ); ?> value="1" name="virtual_classroom_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['virtual_classroom']['delete'], 1 ); ?> value="1" name="virtual_classroom_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Virtual Classroom module code end. -->
				<!-- Subject module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Subject', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['subject']['own_data'], 1 ); ?> value="1" name="subject_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['subject']['view'], 1 ); ?> value="1" name="subject_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['subject']['add'], 1 ); ?> value="1" name="subject_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['subject']['edit'], 1 ); ?> value="1" name="subject_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['subject']['delete'], 1 ); ?> value="1" name="subject_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Subject module code end. -->
				<!-- Student module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Child', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['student']['own_data'], 1 ); ?> value="1" name="student_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['student']['view'], 1 ); ?> value="1" name="student_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['student']['add'], 1 ); ?> value="1" name="student_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['student']['edit'], 1 ); ?> value="1" name="student_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['student']['delete'], 1 ); ?> value="1" name="student_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Student module code.  -->
				<!-- Teacher module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Teacher', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['teacher']['own_data'], 1 ); ?> value="1" name="teacher_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['teacher']['view'], 1 ); ?> value="1" name="teacher_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['teacher']['add'], 1 ); ?> value="1" name="teacher_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['teacher']['edit'], 1 ); ?> value="1" name="teacher_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['teacher']['delete'], 1 ); ?> value="1" name="teacher_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Teacher module code end. -->
				<!-- Parent module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Parent', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['parent']['own_data'], 1 ); ?> value="1" name="parent_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['parent']['view'], 1 ); ?> value="1" name="parent_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['parent']['add'], 1 ); ?> value="1" name="parent_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['parent']['edit'], 1 ); ?> value="1" name="parent_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['parent']['delete'], 1 ); ?> value="1" name="parent_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Parent module code end. -->
				<!-- Exam module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Exam', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['exam']['own_data'], 1 ); ?> value="1" name="exam_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['exam']['view'], 1 ); ?> value="1" name="exam_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['exam']['add'], 1 ); ?> value="1" name="exam_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['exam']['edit'], 1 ); ?> value="1" name="exam_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['exam']['delete'], 1 ); ?> value="1" name="exam_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Exam module code end. -->
				<!-- Manage Marks module code. -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Manage Marks', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['manage_marks']['own_data'], 1 ); ?> value="1" name="manage_marks_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['manage_marks']['view'], 1 ); ?> value="1" name="manage_marks_view" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['manage_marks']['add'], 1 ); ?> value="1" name="manage_marks_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['manage_marks']['edit'], 1 ); ?> value="1" name="manage_marks_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['manage_marks']['delete'], 1 ); ?> value="1" name="manage_marks_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Manage Marks module code end. -->
				<!-- Home Work module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Home Work', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['homework']['own_data'], 1 ); ?> value="1" name="homework_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['homework']['view'], 1 ); ?> value="1" name="homework_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['homework']['add'], 1 ); ?> value="1" name="homework_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['homework']['edit'], 1 ); ?> value="1" name="homework_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['homework']['delete'], 1 ); ?> value="1" name="homework_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Home Work module code end. -->
				 <!-- Class Room module code. -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Class Room', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['class_room']['own_data'], 1 ); ?> value="1" name="class_room_own_data">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['parent']['class_room']['view'],1);?> value="1" name="class_room_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['parent']['class_room']['add'],1);?> value="1" name="class_room_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['parent']['class_room']['edit'],1);?> value="1" name="class_room_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['parent']['class_room']['delete'],1);?> value="1" name="class_room_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Class Room module code end. -->
				<!-- Attendance module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Attendance', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['attendance']['own_data'], 1 ); ?> value="1" name="attendance_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['attendance']['view'], 1 ); ?> value="1" name="attendance_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['attendance']['add'], 1 ); ?> value="1" name="attendance_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['attendance']['edit'], 1 ); ?> value="1" name="attendance_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['attendance']['delete'], 1 ); ?> value="1" name="attendance_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Attendance module code end. -->
				<!-- document module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Document', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['document']['own_data'], 1 ); ?> value="1" name="document_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['document']['view'], 1 ); ?> value="1" name="document_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['document']['add'], 1 ); ?> value="1" name="document_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['document']['edit'], 1 ); ?> value="1" name="document_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['document']['delete'], 1 ); ?> value="1" name="document_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- document module code end. -->
				<!-- Leave module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Leave', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['leave']['own_data'], 1 ); ?> value="1" name="leave_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['leave']['view'], 1 ); ?> value="1" name="leave_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['leave']['add'], 1 ); ?> value="1" name="leave_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['leave']['edit'], 1 ); ?> value="1" name="leave_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['leave']['delete'], 1 ); ?> value="1" name="leave_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Leave module code end. -->
				<!-- Fee Payment module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Fees Payment', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['feepayment']['own_data'], 1 ); ?> value="1" name="feepayment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['feepayment']['view'], 1 ); ?> value="1" name="feepayment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['feepayment']['add'], 1 ); ?> value="1" name="feepayment_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['feepayment']['edit'], 1 ); ?> value="1" name="feepayment_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['feepayment']['delete'], 1 ); ?> value="1" name="feepayment_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Fee Payment module code end. -->
				<!-- Payment module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Payment', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['payment']['own_data'], 1 ); ?> value="1" name="payment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['payment']['view'], 1 ); ?> value="1" name="payment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['payment']['add'], 1 ); ?> value="1" name="payment_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['payment']['edit'], 1 ); ?> value="1" name="payment_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['payment']['delete'], 1 ); ?> value="1" name="payment_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Payment module code end. -->
				<!-- Library module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Library', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['library']['own_data'], 1 ); ?> value="1" name="library_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['library']['view'], 1 ); ?> value="1" name="library_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['library']['add'], 1 ); ?> value="1" name="library_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['library']['edit'], 1 ); ?> value="1" name="library_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['library']['delete'], 1 ); ?> value="1" name="library_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Library module code end. -->
				<!-- Library module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Certificate', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['parent']['certificate']['own_data'] ) ) { echo checked( $access_right['parent']['certificate']['own_data'], 1 ); } ?> value="1" name="certificate_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['parent']['certificate']['view'] ) ) { echo checked( $access_right['parent']['certificate']['view'], 1 ); } ?> value="1" name="certificate_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['parent']['certificate']['add'] ) ) { echo checked( $access_right['parent']['certificate']['add'], 1 ); } ?> value="1" name="certificate_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['parent']['certificate']['edit'] ) ) { echo checked( $access_right['parent']['certificate']['edit'], 1 ); } ?> value="1" name="certificate_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['parent']['certificate']['delete'] ) ) { echo checked( $access_right['parent']['certificate']['delete'], 1 ); } ?> value="1" name="certificate_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Library module code end. -->
				<!-- Hostel module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Hostel', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['hostel']['own_data'], 1 ); ?> value="1" name="hostel_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['hostel']['view'], 1 ); ?> value="1" name="hostel_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['hostel']['add'], 1 ); ?> value="1" name="hostel_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['hostel']['edit'], 1 ); ?> value="1" name="hostel_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['hostel']['delete'], 1 ); ?> value="1" name="hostel_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Hostel module code end. -->
				<!-- Transport module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Transport', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['transport']['own_data'], 1 ); ?> value="1" name="transport_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['transport']['view'], 1 ); ?> value="1" name="transport_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['transport']['add'], 1 ); ?> value="1" name="transport_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['transport']['edit'], 1 ); ?> value="1" name="transport_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['transport']['delete'], 1 ); ?> value="1" name="transport_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Transport module code end. -->
				<!-- Notice Board module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Notice', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notice']['own_data'], 1 ); ?> value="1" name="notice_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notice']['view'], 1 ); ?> value="1" name="notice_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notice']['add'], 1 ); ?> value="1" name="notice_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notice']['edit'], 1 ); ?> value="1" name="notice_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notice']['delete'], 1 ); ?> value="1" name="notice_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Notice Board module code end. -->
				<!-- Event module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Event', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['event']['own_data'], 0 ); ?> value="1" name="event_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['event']['view'], 1 ); ?> value="1" name="event_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['event']['add'], 1 ); ?> value="1" name="event_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['event']['edit'], 1 ); ?> value="1" name="event_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['event']['delete'], 1 ); ?> value="1" name="event_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Event module code end. -->
				<!-- Message module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Message', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['message']['own_data'], 1 ); ?> value="1" name="message_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['message']['view'], 1 ); ?> value="1" name="message_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['message']['add'], 1 ); ?> value="1" name="message_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['message']['edit'], 1 ); ?> value="1" name="message_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['message']['delete'], 1 ); ?> value="1" name="message_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Message module code end. -->
				<!-- Notification module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Notification', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notification']['own_data'], 1 ); ?> value="1" name="notification_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notification']['view'], 1 ); ?> value="1" name="notification_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notification']['add'], 1 ); ?> value="1" name="notification_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notification']['edit'], 1 ); ?> value="1" name="notification_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['notification']['delete'], 1 ); ?> value="1" name="notification_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Notification module code end. -->
				<!-- Holiday module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Holiday', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['holiday']['own_data'], 1 ); ?> value="1" name="holiday_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['holiday']['view'], 1 ); ?> value="1" name="holiday_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['holiday']['add'], 1 ); ?> value="1" name="holiday_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['holiday']['edit'], 1 ); ?> value="1" name="holiday_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['holiday']['delete'], 1 ); ?> value="1" name="holiday_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Holiday module code end. -->
				<!-- Account module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Account', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['account']['own_data'], 1 ); ?> value="1" name="account_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['account']['view'], 1 ); ?> value="1" name="account_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['account']['add'], 1 ); ?> value="1" name="account_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['account']['edit'], 1 ); ?> value="1" name="account_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['account']['delete'], 1 ); ?> value="1" name="account_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Account module code end. -->
				<!-- Report module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Report', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['report']['own_data'], 1 ); ?> value="1" name="report_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['report']['view'], 1 ); ?> value="1" name="report_view" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['report']['add'], 1 ); ?> value="1" name="report_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['report']['edit'], 1 ); ?> value="1" name="report_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['parent']['report']['delete'], 1 ); ?> value="1" name="report_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Report module code end. -->
			</div>
			<?php wp_nonce_field( 'mjschool_save_parent_access_right_nonce' ); ?>
			<div class="col-sm-6 mjschool-row-bottom mjschool-rtl-access-save-btn">
				<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_access_right" class="btn btn-success mjschool-save-btn" />
			</div>
		</form>
	</div><!---END PANEL BODY DIV. -->
</div> <!--- END PANEL WHITE DIV. -->