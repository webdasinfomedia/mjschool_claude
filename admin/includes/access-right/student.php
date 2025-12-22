<?php
/**
 * Access Rights Management for Student Role.
 *
 * Handles saving and retrieving access rights for the Student role
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

// Check nonce for student access rights tab.
if ( isset( $_GET['tab'] ) ) {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_access_rights_tab' ) ) {
       wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
    }
}
/**
 * Handles saving and updating access rights for the Student role.
 *
 * This code retrieves the existing access rights for Student from the options table,
 * checks if the form has been submitted, and then updates the role access rights based
 * on the form input ($_REQUEST). Each menu has its own permissions like add, edit, view, delete.
 *
 * @since 1.0.0
 */
$result = get_option( 'mjschool_access_right_student' );
if ( isset( $_POST['save_access_right'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
	// Check save parent access rights nonce.
	if ( wp_verify_nonce( $nonce, 'mjschool_save_student_access_right_nonce' ) ) {
		$role_access_right = array();
		$result = get_option( 'mjschool_access_right_student' );
		$role_access_right['student'] = array(
			'teacher'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-teacher.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-student.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-parents.png' ) ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_delete'] ) ) : 0,
			),
			'supportstaff'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-support-staff.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-support-staff.png' ) ),
				'menu_title' => 'Supportstaff',
				'page_link'  => 'supportstaff',
				'own_data'   => isset( $_REQUEST['supportstaff_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['supportstaff_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['supportstaff_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['supportstaff_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['supportstaff_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_delete'] ) ) : 0,
			),
			'subject'           => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-subject.png' ) ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_delete'] ) ) : 0,
			),
			'schedule'          => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-class-route.png' ) ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_delete'] ) ) : 0,
			),
			'virtual_classroom' => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-virtual-class.png' ) ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_delete'] ) ) : 0,
			),
			'attendance'        => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-attandance.png' ) ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_delete'] ) ) : 0,
			),
			'notification'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_delete'] ) ) : 0,
			),
			'exam'              => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/exam.png' ) ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_delete'] ) ) : 0,
			),
			'class_room'      => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-class.png' ) ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				"own_data"   => isset( $_REQUEST['class_room_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_own_data'] ) ) : 0,
				"add"	     => isset( $_REQUEST['class_room_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_add'] ) ) : 0,
				"edit"	     =>isset( $_REQUEST['class_room_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_edit'] ) ) : 0,
				"view"	     =>isset( $_REQUEST['class_room_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_view'] ) ) : 0,
				"delete"	 =>isset( $_REQUEST['class_room_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_delete'] ) ) : 0
			),
			'grade'             =>array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-grade.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-grade.png' ) ),
				'menu_title' => 'Grade',
				'page_link'  => 'grade',
				'own_data'   => isset( $_REQUEST['grade_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['grade_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['grade_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['grade_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['grade_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_delete'] ) ) : 0,
			),
			'hostel'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-hostel.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-homework.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-mark-manage.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-fee-payment.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-payment.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-transport.png' ) ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_delete'] ) ) : 0,
			),
			'leave'             => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-transport.png' ) ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_delete'] ) ) : 0,
			),
			'notice'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-notice.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-message.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-holiday.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-library.png' ) ),
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
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-account.png' ) ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_delete'] ) ) : 0,
			),
			'report'            => array(
				'menu_icone' => esc_url(plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ) ),
				'app_icone'  => esc_url(plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-report.png' ) ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_delete'] ) ) : 0,
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
		$result = update_option( 'mjschool_access_right_student', $role_access_right );
		$nonce = wp_create_nonce( 'mjschool_access_rights_tab' );
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_access_right&tab=Student&_wpnonce='.rawurlencode( $nonce ).'&message=1' ) );
		die();
	}
	else{
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$access_right = get_option( 'mjschool_access_right_student' );
/**
 * Display success messages for Student role access rights.
 *
 * This code retrieves the saved access rights for the Student role
 * and shows a success notice if the access rights were updated.
 *
 * @since 1.0.0
 */
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Student Access Right Updated Successfully.', 'mjschool' );
		break;
}
if ( $message ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
		<p><?php echo esc_html( $message_string ); ?></p>
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
			<span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span>
		</button>
	</div>
	<?php
}
?>
<div><!--- PANEL WHITE DIV START. -->
	<div class="header">
		<h3 class="mjschool-first-header"><?php esc_html_e( 'Student Access Right', 'mjschool' ); ?></h3>
	</div>
	<div class="mjschool-panel-body" id="mjschool-rs-access-pl-15px"> <!--- PANEL BODY DIV START. -->
		<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-access-right-form">
			<div class="row">
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
								<input type="checkbox" <?php echo checked( $access_right['student']['schedule']['own_data'], 1 ); ?> value="1" name="schedule_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['schedule']['view'], 1 ); ?> value="1" name="schedule_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['schedule']['add'], 1 ); ?> value="1" name="schedule_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['schedule']['edit'], 1 ); ?> value="1" name="schedule_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['schedule']['delete'], 1 ); ?> value="1" name="schedule_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['virtual_classroom']['own_data'], 1 ); ?> value="1" name="virtual_classroom_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['virtual_classroom']['view'], 1 ); ?> value="1" name="virtual_classroom_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['virtual_classroom']['add'], 1 ); ?> value="1" name="virtual_classroom_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['virtual_classroom']['edit'], 1 ); ?> value="1" name="virtual_classroom_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['virtual_classroom']['delete'], 1 ); ?> value="1" name="virtual_classroom_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['subject']['own_data'], 1 ); ?> value="1" name="subject_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['subject']['view'], 1 ); ?> value="1" name="subject_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['subject']['add'], 1 ); ?> value="1" name="subject_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['subject']['edit'], 1 ); ?> value="1" name="subject_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['subject']['delete'], 1 ); ?> value="1" name="subject_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Subject module code end. -->
				<!-- Student module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Student', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['student']['own_data'], 1 ); ?> value="1" name="student_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['student']['view'], 1 ); ?> value="1" name="student_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['student']['add'], 1 ); ?> value="1" name="student_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['student']['edit'], 1 ); ?> value="1" name="student_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['student']['delete'], 1 ); ?> value="1" name="student_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Student module code.  -->
				<!-- Teacher module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label mjschool-menu-left-6">
							<?php esc_html_e( 'Teacher', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['teacher']['own_data'], 1 ); ?> value="1" name="teacher_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['teacher']['view'], 1 ); ?> value="1" name="teacher_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['teacher']['add'], 1 ); ?> value="1" name="teacher_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['teacher']['edit'], 1 ); ?> value="1" name="teacher_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['teacher']['delete'], 1 ); ?> value="1" name="teacher_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Teacher module code end. -->
				<!-- Support staff module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label mjschool-Supportstaff-menu-label">
							<?php esc_html_e( 'Supportstaff', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['supportstaff']['own_data'], 1 ); ?> value="1" name="supportstaff_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['supportstaff']['view'], 1 ); ?> value="1" name="supportstaff_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['supportstaff']['add'], 1 ); ?> value="1" name="supportstaff_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['supportstaff']['edit'], 1 ); ?> value="1" name="supportstaff_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['supportstaff']['delete'], 1 ); ?> value="1" name="supportstaff_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Support staff module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['student']['parent']['own_data'], 1 ); ?> value="1" name="parent_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['parent']['view'], 1 ); ?> value="1" name="parent_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['parent']['add'], 1 ); ?> value="1" name="parent_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['parent']['edit'], 1 ); ?> value="1" name="parent_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['parent']['delete'], 1 ); ?> value="1" name="parent_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['exam']['own_data'], 1 ); ?> value="1" name="exam_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['exam']['view'], 1 ); ?> value="1" name="exam_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['exam']['add'], 1 ); ?> value="1" name="exam_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['exam']['edit'], 1 ); ?> value="1" name="exam_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['exam']['delete'], 1 ); ?> value="1" name="exam_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Exam module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['student']['class_room']['own_data'], 1 ); ?> value="1" name="class_room_own_data">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['student']['class_room']['view'],1);?> value="1" name="class_room_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['student']['class_room']['add'],1);?> value="1" name="class_room_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['student']['class_room']['edit'],1);?> value="1" name="class_room_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['student']['class_room']['delete'],1);?> value="1" name="class_room_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Class Room module code end. -->
				<!-- Manage Marks module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Manage Marks', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['manage_marks']['own_data'], 1 ); ?> value="1" name="manage_marks_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['manage_marks']['view'], 1 ); ?> value="1" name="manage_marks_view" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['manage_marks']['add'], 1 ); ?> value="1" name="manage_marks_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['manage_marks']['edit'], 1 ); ?> value="1" name="manage_marks_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['manage_marks']['delete'], 1 ); ?> value="1" name="manage_marks_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Manage Marks module code end. -->
				<!-- Grade Hall module code. -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Grade', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['grade']['own_data'], 1 ); ?> value="1" name="grade_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['grade']['view'], 1 ); ?> value="1" name="grade_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['grade']['add'], 1 ); ?> value="1" name="grade_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['grade']['edit'], 1 ); ?> value="1" name="grade_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['grade']['delete'], 1 ); ?> value="1" name="grade_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Grade Hall module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['student']['homework']['own_data'], 1 ); ?> value="1" name="homework_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['homework']['view'], 1 ); ?> value="1" name="homework_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['homework']['add'], 1 ); ?> value="1" name="homework_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['homework']['edit'], 1 ); ?> value="1" name="homework_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['homework']['delete'], 1 ); ?> value="1" name="homework_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Home Work module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['student']['attendance']['own_data'], 1 ); ?> value="1" name="attendance_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['attendance']['view'], 1 ); ?> value="1" name="attendance_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['attendance']['add'], 1 ); ?> value="1" name="attendance_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['attendance']['edit'], 1 ); ?> value="1" name="attendance_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['attendance']['delete'], 1 ); ?> value="1" name="attendance_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['document']['own_data'], 1 ); ?> value="1" name="document_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['document']['view'], 1 ); ?> value="1" name="document_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['document']['add'], 1 ); ?> value="1" name="document_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['document']['edit'], 1 ); ?> value="1" name="document_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['document']['delete'], 1 ); ?> value="1" name="document_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['leave']['own_data'], 1 ); ?> value="1" name="leave_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['leave']['view'], 1 ); ?> value="1" name="leave_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['leave']['add'], 1 ); ?> value="1" name="leave_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['leave']['edit'], 1 ); ?> value="1" name="leave_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['leave']['delete'], 1 ); ?> value="1" name="leave_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['feepayment']['own_data'], 1 ); ?> value="1" name="feepayment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['feepayment']['view'], 1 ); ?> value="1" name="feepayment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['feepayment']['add'], 1 ); ?> value="1" name="feepayment_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['feepayment']['edit'], 1 ); ?> value="1" name="feepayment_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['feepayment']['delete'], 1 ); ?> value="1" name="feepayment_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['payment']['own_data'], 1 ); ?> value="1" name="payment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['payment']['view'], 1 ); ?> value="1" name="payment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['payment']['add'], 1 ); ?> value="1" name="payment_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['payment']['edit'], 1 ); ?> value="1" name="payment_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['payment']['delete'], 1 ); ?> value="1" name="payment_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['library']['own_data'], 1 ); ?> value="1" name="library_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['library']['view'], 1 ); ?> value="1" name="library_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['library']['add'], 1 ); ?> value="1" name="library_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['library']['edit'], 1 ); ?> value="1" name="library_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['library']['delete'], 1 ); ?> value="1" name="library_delete" disabled>
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
								<input type="checkbox" <?php if ( isset( $access_right['student']['certificate']['own_data'] ) ) { echo checked( $access_right['student']['certificate']['own_data'], 1 ); } ?> value="1" name="certificate_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['student']['certificate']['view'] ) ) { echo checked( $access_right['student']['certificate']['view'], 1 ); } ?> value="1" name="certificate_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['student']['certificate']['add'] ) ) { echo checked( $access_right['student']['certificate']['add'], 1 ); } ?> value="1" name="certificate_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['student']['certificate']['edit'] ) ) { echo checked( $access_right['student']['certificate']['edit'], 1 ); } ?> value="1" name="certificate_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['student']['certificate']['delete'] ) ) { echo checked( $access_right['student']['certificate']['delete'], 1 ); } ?> value="1" name="certificate_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['hostel']['own_data'], 1 ); ?> value="1" name="hostel_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['hostel']['view'], 1 ); ?> value="1" name="hostel_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['hostel']['add'], 1 ); ?> value="1" name="hostel_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['hostel']['edit'], 1 ); ?> value="1" name="hostel_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['hostel']['delete'], 1 ); ?> value="1" name="hostel_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['transport']['own_data'], 0 ); ?> value="1" name="transport_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['transport']['view'], 1 ); ?> value="1" name="transport_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['transport']['add'], 1 ); ?> value="1" name="transport_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['transport']['edit'], 1 ); ?> value="1" name="transport_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['transport']['delete'], 1 ); ?> value="1" name="transport_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['notice']['own_data'], 1 ); ?> value="1" name="notice_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notice']['view'], 1 ); ?> value="1" name="notice_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notice']['add'], 1 ); ?> value="1" name="notice_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notice']['edit'], 1 ); ?> value="1" name="notice_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notice']['delete'], 1 ); ?> value="1" name="notice_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['event']['own_data'], 1 ); ?> value="1" name="event_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['event']['view'], 1 ); ?> value="1" name="event_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['event']['add'], 1 ); ?> value="1" name="event_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['event']['edit'], 1 ); ?> value="1" name="event_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['event']['delete'], 1 ); ?> value="1" name="event_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['message']['own_data'], 1 ); ?> value="1" name="message_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['message']['view'], 1 ); ?> value="1" name="message_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['message']['add'], 1 ); ?> value="1" name="message_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['message']['edit'], 1 ); ?> value="1" name="message_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['message']['delete'], 1 ); ?> value="1" name="message_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['student']['notification']['own_data'], 1 ); ?> value="1" name="notification_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notification']['view'], 1 ); ?> value="1" name="notification_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notification']['add'], 1 ); ?> value="1" name="notification_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notification']['edit'], 1 ); ?> value="1" name="notification_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['notification']['delete'], 1 ); ?> value="1" name="notification_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['holiday']['own_data'], 1 ); ?> value="1" name="holiday_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['holiday']['view'], 1 ); ?> value="1" name="holiday_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['holiday']['add'], 1 ); ?> value="1" name="holiday_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['holiday']['edit'], 1 ); ?> value="1" name="holiday_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['holiday']['delete'], 1 ); ?> value="1" name="holiday_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['account']['own_data'], 1 ); ?> value="1" name="account_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['account']['view'], 1 ); ?> value="1" name="account_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['account']['add'], 1 ); ?> value="1" name="account_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['account']['edit'], 1 ); ?> value="1" name="account_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['account']['delete'], 1 ); ?> value="1" name="account_delete" disabled>
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
								<input type="checkbox" <?php echo checked( $access_right['student']['report']['own_data'], 1 ); ?> value="1" name="report_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['report']['view'], 1 ); ?> value="1" name="report_view" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['report']['add'], 1 ); ?> value="1" name="report_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['report']['edit'], 1 ); ?> value="1" name="report_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['student']['report']['delete'], 1 ); ?> value="1" name="report_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Report module code end. -->
			</div>
			<?php wp_nonce_field( 'mjschool_save_student_access_right_nonce' ); ?>
			<div class="col-sm-6 mjschool-row-bottom mjschool-rtl-access-save-btn">
				<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_access_right" class="btn btn-success mjschool-save-btn" />
			</div>
		</form>
	</div><!---END PANEL BODY DIV. -->
</div> <!--- END PANEL WHITE DIV. -->