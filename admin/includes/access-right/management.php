<?php
/**
 * Access Rights Management for Management Role.
 *
 * Handles saving and retrieving access rights for the Management role
 * within the MJSchool plugin. Allows admins to define CRUD (Create, Read,
 * Update, Delete) permissions for different menu items.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/access-rights
 * @since      1.0.0
 * 
 */
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
// Check nonce for management access rights tab.
if ( isset( $_GET['tab'] ) ) {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce(sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_access_rights_tab') ) {
       wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
    }
}

/**
 * Handles saving and updating access rights for the Management role.
 *
 * This code retrieves the existing access rights for Management from the options table,
 * checks if the form has been submitted, and then updates the role access rights based
 * on the form input ($_REQUEST). Each menu has its own permissions like add, edit, view, delete.
 *
 * @since 1.0.0
 */
$result = get_option( 'mjschool_access_right_management' );
if ( isset( $_POST['save_access_right'] ) ) {
	$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
	// Check save management access rights nonce.
	if ( wp_verify_nonce( $nonce, 'mjschool_save_management_access_right_nonce' ) ) {
		$role_access_right = array();
		$result = get_option( 'mjschool_access_right_management' );
		$role_access_right['management'] = array(
			'admission'         => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Admission',
				'page_link'  => 'admission',
				'own_data'   => isset( $_REQUEST['admission_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['admission_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['admission_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['admission_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['admission_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['admission_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['admission_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['admission_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['admission_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['admission_delete'] ) ) : 0,
			),
			'supportstaff'      => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Supportstaff',
				'page_link'  => 'supportstaff',
				'own_data'   => isset( $_REQUEST['supportstaff_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['supportstaff_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['supportstaff_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['supportstaff_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['supportstaff_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_delete'] ) ) : 0,
			),
			'exam_hall'         => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Exam Hall',
				'page_link'  => 'exam_hall',
				'own_data'   => isset( $_REQUEST['exam_hall_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_hall_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['exam_hall_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_hall_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['exam_hall_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_hall_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['exam_hall_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_hall_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['exam_hall_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_hall_delete'] ) ) : 0,
			),
			'grade'             => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Grade',
				'page_link'  => 'grade',
				'own_data'   => isset( $_REQUEST['grade_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['grade_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['grade_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['grade_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['grade_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['grade_delete'] ) ) : 0,
			),
			'notification'      => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_delete'] ) ) : 0,
			),
			'custom_field'      => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Custom Field',
				'page_link'  => 'custom_field',
				'own_data'   => isset( $_REQUEST['custom_field_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['custom_field_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['custom_field_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['custom_field_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['custom_field_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['custom_field_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['custom_field_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['custom_field_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['custom_field_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['custom_field_delete'] ) ) : 0,
			),
			'migration'         => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Migration',
				'page_link'  => 'migration',
				'own_data'   => isset( $_REQUEST['migration_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['migration_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['migration_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['migration_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['migration_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['migration_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['migration_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['migration_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['migration_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['migration_delete'] ) ) : 0,
			),
			'mjschool_setting'  => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png') ),
				'menu_title' => 'SMS Setting',
				'page_link'  => 'mjschool_setting',
				'own_data'   => isset( $_REQUEST['mjschool_setting_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_setting_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['mjschool_setting_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_setting_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['mjschool_setting_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_setting_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['mjschool_setting_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_setting_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['mjschool_setting_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_setting_delete'] ) ) : 0,
			),
			'email_template'    => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Email Template',
				'page_link'  => 'email_template',
				'own_data'   => isset( $_REQUEST['email_template_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_template_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['email_template_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_template_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['email_template_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_template_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['email_template_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_template_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['email_template_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['email_template_delete'] ) ) : 0,
			),
			'mjschool_template' => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'SMS Template',
				'page_link'  => 'mjschool_template',
				'own_data'   => isset( $_REQUEST['mjschool_template_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_template_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['mjschool_template_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_template_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['mjschool_template_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_template_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['mjschool_template_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_template_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['mjschool_template_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['mjschool_template_delete'] ) ) : 0,
			),
			'access_right'      => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Access Right',
				'page_link'  => 'access_right',
				'own_data'   => isset( $_REQUEST['access_right_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_right_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['access_right_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_right_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['access_right_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_right_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['access_right_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_right_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['access_right_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['access_right_delete'] ) ) : 0,
			),
			'teacher'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ) ),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset( $_REQUEST['teacher_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['teacher_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['teacher_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['teacher_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['teacher_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_delete'] ) ) : 0,
			),
			'student'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-student-icon.png' ) ),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset( $_REQUEST['student_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['student_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['student_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['student_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['student_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_delete'] ) ) : 0,
			),
			'parent'            => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-parents.png' ) ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_delete'] ) ) : 0,
			),
			'subject'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ) ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_delete'] ) ) : 0,
			),
			'class'             => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-class.png' ) ),
				'menu_title' => 'Class',
				'page_link'  => 'class',
				'own_data'   => isset( $_REQUEST['class_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['class_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['class_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['class_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['class_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_delete'] ) ) : 0,
			),
			'class_room'     => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-class.png' ) ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				"own_data"   => isset( $_REQUEST['class_room_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_own_data'] ) ) : 0,
				"add"        => isset( $_REQUEST['class_room_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_add'] ) ) : 0,
				"edit"       => isset( $_REQUEST['class_room_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_edit'] ) ) : 0,
				"view"       => isset( $_REQUEST['class_room_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_view'] ) ) : 0,
				"delete"     => isset( $_REQUEST['class_room_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_delete'] ) ) : 0
			),
			'virtual_classroom' => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ) ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_delete'] ) ) : 0,
			),
			'schedule'          => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ) ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_delete'] ) ) : 0,
			),
			'attendance'        => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ) ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_delete'] ) ) : 0,
			),
			'exam'              => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ) ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_delete'] ) ) : 0,
			),
			'hostel'            => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ) ),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset( $_REQUEST['hostel_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['hostel_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['hostel_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['hostel_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['hostel_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_delete'] ) ) : 0,
			),
			'document'          => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ) ),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset( $_REQUEST['document_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['document_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['document_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['document_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['document_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_delete'] ) ) : 0,
			),
			'leave'             => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'app_icone'  => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ) ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_delete'] ) ) : 0,
			),
			'homework'          => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-homework.png' ) ),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset( $_REQUEST['homework_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['homework_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['homework_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['homework_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['homework_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_delete'] ) ) : 0,
			),
			'manage_marks'      => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-mark-manage.png' ) ),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage_marks',
				'own_data'   => isset( $_REQUEST['manage_marks_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['manage_marks_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['manage_marks_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['manage_marks_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['manage_marks_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_delete'] ) ) : 0,
			),
			'tax'               => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ) ),
				'menu_title' => 'Tax',
				'page_link'  => 'tax',
				'own_data'   => isset( $_REQUEST['tax_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tax_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['tax_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tax_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['tax_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tax_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['tax_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tax_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['tax_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tax_delete'] ) ) : 0,
			),
			'feepayment'        => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ) ),
				'menu_title' => 'Fee Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset( $_REQUEST['feepayment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['feepayment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['feepayment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['feepayment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['feepayment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_delete'] ) ) : 0,
			),
			'payment'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-payment.png' ) ),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset( $_REQUEST['payment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['payment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['payment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['payment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['payment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_delete'] ) ) : 0,
			),
			'transport'         => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ) ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_delete'] ) ) : 0,
			),
			'notice'            => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ) ),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset( $_REQUEST['notice_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['notice_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notice_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notice_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['notice_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_delete'] ) ) : 0,
			),
			'message'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ) ),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset( $_REQUEST['message_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['message_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['message_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['message_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['message_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_delete'] ) ) : 0,
			),
			'holiday'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-holiday.png' ) ),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset( $_REQUEST['holiday_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['holiday_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['holiday_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['holiday_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['holiday_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_delete'] ) ) : 0,
			),
			'library'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ) ),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset( $_REQUEST['library_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['library_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['library_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['library_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['library_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_delete'] ) ) : 0,
			),
			'certificate'       => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ) ),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset( $_REQUEST['certificate_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['certificate_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['certificate_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['certificate_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['certificate_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_delete'] ) ) : 0,
			),
			'account'           => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-account.png' ) ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_delete'] ) ) : 0,
			),
			'report'            => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ) ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_delete'] ) ) : 0,
			),
			'event'             => array(
				'menu_icone' => esc_url( plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ) ),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset( $_REQUEST['event_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['event_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['event_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['event_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['event_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_delete'] ) ) : 0,
			),
		);
		$result = update_option( 'mjschool_access_right_management', $role_access_right );
		$nonce = wp_create_nonce( 'mjschool_access_rights_tab' );
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_access_right&tab=Management&_wpnonce=' . rawurlencode( $nonce ) . '&message=1' ) );
		exit; 
	}
	else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$access_right = get_option( 'mjschool_access_right_management' );
/**
 * Display success messages for Management role access rights.
 *
 * This code retrieves the saved access rights for the Management role
 * and shows a success notice if the access rights were updated.
 *
 * @since 1.0.0
 */
$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
switch ( $message ) {
	case '1':
		$message_string = esc_html__( 'Management Access Right Updated Successfully.', 'mjschool' );
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
		<h3 class="mjschool-first-header"><?php esc_html_e( 'Management Access Right', 'mjschool' ); ?></h3>
	</div>
	<div class="mjschool-panel-body" id="mjschool-rs-access-pl-15px"> <!--- PANEL BODY DIV START. -->
		<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal mjschool-rs-access-pl-15px" id="mjschool-access-right-form">
			<div class="row mjschool-access-right-header">
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Menu', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2"><?php esc_html_e( 'OwnData', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2"><?php esc_html_e( 'View', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Add', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Edit', 'mjschool' ); ?></div>
				<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15"><?php esc_html_e( 'Delete ', 'mjschool' ); ?></div>
			</div>
			<div class="mjschool-access-right-menu row mjschool-border-bottom-0">
				<!-- Admission module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Admission', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['admission']['own_data'], 1 ); ?>value="1" name="admission_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['admission']['view'], 1 ); ?> value="1" name="admission_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['admission']['add'], 1 ); ?> value="1" name="admission_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['admission']['edit'], 1 ); ?> value="1" name="admission_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['admission']['delete'], 1 ); ?> value="1" name="admission_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Admission module code end. -->
				<!-- Class module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Class', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['class']['own_data'], 1 ); ?> value="1" name="class_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['class']['view'], 1 ); ?> value="1" name="class_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['class']['add'], 1 ); ?> value="1" name="class_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['class']['edit'], 1 ); ?> value="1" name="class_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['class']['delete'], 1 ); ?> value="1" name="class_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Class module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['class_room']['own_data'], 1 ); ?> value="1" name="class_room_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['management']['class_room']['view'],1);?> value="1" name="class_room_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['management']['class_room']['add'],1);?> value="1" name="class_room_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['management']['class_room']['edit'],1);?> value="1" name="class_room_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked($access_right['management']['class_room']['delete'],1);?> value="1" name="class_room_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Class Room module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['schedule']['own_data'], 1 ); ?> value="1" name="schedule_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['schedule']['view'], 1 ); ?> value="1" name="schedule_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['schedule']['add'], 1 ); ?> value="1" name="schedule_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['schedule']['edit'], 1 ); ?> value="1" name="schedule_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['schedule']['delete'], 1 ); ?> value="1" name="schedule_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['virtual_classroom']['own_data'], 1 ); ?> value="1" name="virtual_classroom_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['virtual_classroom']['view'], 1 ); ?> value="1" name="virtual_classroom_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['virtual_classroom']['add'], 1 ); ?> value="1" name="virtual_classroom_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['virtual_classroom']['edit'], 1 ); ?> value="1" name="virtual_classroom_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['virtual_classroom']['delete'], 1 ); ?> value="1" name="virtual_classroom_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['subject']['own_data'], 1 ); ?> value="1" name="subject_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['subject']['view'], 1 ); ?> value="1" name="subject_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['subject']['add'], 1 ); ?> value="1" name="subject_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['subject']['edit'], 1 ); ?> value="1" name="subject_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['subject']['delete'], 1 ); ?> value="1" name="subject_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['student']['own_data'], 1 ); ?> value="1" name="student_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['student']['view'], 1 ); ?> value="1" name="student_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['student']['add'], 1 ); ?> value="1" name="student_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['student']['edit'], 1 ); ?> value="1" name="student_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['student']['delete'], 1 ); ?> value="1" name="student_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['teacher']['own_data'], 1 ); ?> value="1" name="teacher_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['teacher']['view'], 1 ); ?> value="1" name="teacher_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['teacher']['add'], 1 ); ?> value="1" name="teacher_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['teacher']['edit'], 1 ); ?> value="1" name="teacher_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['teacher']['delete'], 1 ); ?> value="1" name="teacher_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['supportstaff']['own_data'], 1 ); ?> value="1" name="supportstaff_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['supportstaff']['view'], 1 ); ?> value="1" name="supportstaff_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['supportstaff']['add'], 1 ); ?> value="1" name="supportstaff_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['supportstaff']['edit'], 1 ); ?> value="1" name="supportstaff_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['supportstaff']['delete'], 1 ); ?> value="1" name="supportstaff_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['parent']['own_data'], 1 ); ?> value="1" name="parent_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['parent']['view'], 1 ); ?> value="1" name="parent_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['parent']['add'], 1 ); ?> value="1" name="parent_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['parent']['edit'], 1 ); ?> value="1" name="parent_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['parent']['delete'], 1 ); ?> value="1" name="parent_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['exam']['own_data'], 1 ); ?> value="1" name="exam_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam']['view'], 1 ); ?> value="1" name="exam_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam']['add'], 1 ); ?> value="1" name="exam_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam']['edit'], 1 ); ?> value="1" name="exam_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam']['delete'], 1 ); ?> value="1" name="exam_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Exam module code end. -->
				<!-- Exam Hall module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Exam Hall', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam_hall']['own_data'], 1 ); ?> value="1" name="exam_hall_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam_hall']['view'], 1 ); ?> value="1" name="exam_hall_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam_hall']['add'], 1 ); ?> value="1" name="exam_hall_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam_hall']['edit'], 1 ); ?> value="1" name="exam_hall_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['exam_hall']['delete'], 1 ); ?> value="1" name="exam_hall_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Exam Hall module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['manage_marks']['own_data'], 1 ); ?> value="1" name="manage_marks_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['manage_marks']['view'], 1 ); ?> value="1" name="manage_marks_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['manage_marks']['add'], 1 ); ?> value="1" name="manage_marks_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['manage_marks']['edit'], 1 ); ?> value="1" name="manage_marks_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['manage_marks']['delete'], 1 ); ?> value="1" name="manage_marks_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Manage Marks module code end. -->
				<!-- Grade module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Grade', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['grade']['own_data'], 1 ); ?> value="1" name="grade_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['grade']['view'], 1 ); ?> value="1" name="grade_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['grade']['add'], 1 ); ?> value="1" name="grade_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['grade']['edit'], 1 ); ?> value="1" name="grade_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['grade']['delete'], 1 ); ?> value="1" name="grade_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Grade Hall module code end. -->
				<!-- Migration module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Migration', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['migration']['own_data'], 1 ); ?> value="1" name="migration_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['migration']['view'], 1 ); ?> value="1" name="migration_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['migration']['add'], 1 ); ?> value="1" name="migration_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['migration']['edit'], 1 ); ?> value="1" name="migration_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['migration']['delete'], 1 ); ?> value="1" name="migration_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- migration module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['homework']['own_data'], 1 ); ?> value="1" name="homework_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['homework']['view'], 1 ); ?> value="1" name="homework_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['homework']['add'], 1 ); ?> value="1" name="homework_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['homework']['edit'], 1 ); ?> value="1" name="homework_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['homework']['delete'], 1 ); ?> value="1" name="homework_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['attendance']['own_data'], 1 ); ?> value="1" name="attendance_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['attendance']['view'], 1 ); ?> value="1" name="attendance_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['attendance']['add'], 1 ); ?> value="1" name="attendance_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['attendance']['edit'], 1 ); ?> value="1" name="attendance_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['attendance']['delete'], 1 ); ?> value="1" name="attendance_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Attendance module code end. -->
				<!-- document module code code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Document', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['document']['own_data'], 1 ); ?> value="1" name="document_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['document']['view'], 1 ); ?> value="1" name="document_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['document']['add'], 1 ); ?> value="1" name="document_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['document']['edit'], 1 ); ?> value="1" name="document_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['document']['delete'], 1 ); ?> value="1" name="document_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- document module code end. -->
				<!-- Leave module code start. -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Leave', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['leave']['own_data'], 1 ); ?> value="1" name="leave_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['leave']['view'], 1 ); ?> value="1" name="leave_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['leave']['add'], 1 ); ?> value="1" name="leave_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['leave']['edit'], 1 ); ?> value="1" name="leave_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['leave']['delete'], 1 ); ?> value="1" name="leave_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Leave module code end. -->
				<!-- Fee Payment module code. -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Tax', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['tax']['own_data'], 1 ); ?> value="1" name="tax_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['tax']['view'], 1 ); ?> value="1" name="tax_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['tax']['add'], 1 ); ?> value="1" name="tax_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['tax']['edit'], 1 ); ?> value="1" name="tax_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['tax']['delete'], 1 ); ?> value="1" name="tax_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Fee Payment module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['feepayment']['own_data'], 1 ); ?> value="1" name="feepayment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['feepayment']['view'], 1 ); ?> value="1" name="feepayment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['feepayment']['add'], 1 ); ?> value="1" name="feepayment_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['feepayment']['edit'], 1 ); ?> value="1" name="feepayment_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['feepayment']['delete'], 1 ); ?> value="1" name="feepayment_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['payment']['own_data'], 1 ); ?> value="1" name="payment_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['payment']['view'], 1 ); ?> value="1" name="payment_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['payment']['add'], 1 ); ?> value="1" name="payment_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['payment']['edit'], 1 ); ?> value="1" name="payment_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['payment']['delete'], 1 ); ?> value="1" name="payment_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['library']['own_data'], 1 ); ?> value="1" name="library_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['library']['view'], 1 ); ?> value="1" name="library_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['library']['add'], 1 ); ?> value="1" name="library_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['library']['edit'], 1 ); ?> value="1" name="library_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['library']['delete'], 1 ); ?> value="1" name="library_delete">
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
								<input type="checkbox" <?php if ( isset( $access_right['management']['certificate']['own_data'] ) ) { echo checked( $access_right['management']['certificate']['own_data'], 1 ); } ?> value="1" name="certificate_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['management']['certificate']['own_data'] ) ) { echo checked( $access_right['management']['certificate']['view'], 1 ); } ?> value="1" name="certificate_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php if ( isset( $access_right['management']['certificate']['add'] ) ) { echo checked( $access_right['management']['certificate']['add'], 1 ); } ?> value="1" name="certificate_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox"  <?php if ( isset( $access_right['management']['certificate']['edit'] ) ) { echo checked( $access_right['management']['certificate']['edit'], 1 ); } ?> value="1" name="certificate_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox"  <?php if ( isset( $access_right['management']['certificate']['delete'] ) ) { echo checked( $access_right['management']['certificate']['delete'], 1 ); } ?> value="1" name="certificate_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['hostel']['own_data'], 1 ); ?> value="1" name="hostel_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['hostel']['view'], 1 ); ?> value="1" name="hostel_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['hostel']['add'], 1 ); ?> value="1" name="hostel_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['hostel']['edit'], 1 ); ?> value="1" name="hostel_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['hostel']['delete'], 1 ); ?> value="1" name="hostel_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['transport']['own_data'], 1 ); ?> value="1" name="transport_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['transport']['view'], 1 ); ?> value="1" name="transport_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['transport']['add'], 1 ); ?> value="1" name="transport_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['transport']['edit'], 1 ); ?> value="1" name="transport_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['transport']['delete'], 1 ); ?> value="1" name="transport_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Transport module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['report']['own_data'], 1 ); ?> value="1" name="report_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['report']['view'], 1 ); ?> value="1" name="report_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['report']['add'], 1 ); ?> value="1" name="report_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['report']['edit'], 1 ); ?> value="1" name="report_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['report']['delete'], 1 ); ?> value="1" name="report_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Report module code end. -->
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
								<input type="checkbox" <?php echo checked( $access_right['management']['notice']['own_data'], 1 ); ?> value="1" name="notice_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notice']['view'], 1 ); ?> value="1" name="notice_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notice']['add'], 1 ); ?> value="1" name="notice_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notice']['edit'], 1 ); ?> value="1" name="notice_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notice']['delete'], 1 ); ?> value="1" name="notice_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['event']['own_data'], 1 ); ?> value="1" name="event_own_data">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['event']['view'], 1 ); ?> value="1" name="event_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['event']['add'], 1 ); ?> value="1" name="event_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['event']['edit'], 1 ); ?> value="1" name="event_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['event']['delete'], 1 ); ?> value="1" name="event_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['message']['own_data'], 1 ); ?> value="1" name="message_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['message']['view'], 1 ); ?> value="1" name="message_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['message']['add'], 1 ); ?> value="1" name="message_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['message']['edit'], 1 ); ?> value="1" name="message_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['message']['delete'], 1 ); ?> value="1" name="message_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['notification']['own_data'], 1 ); ?> value="1" name="notification_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notification']['view'], 1 ); ?> value="1" name="notification_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notification']['add'], 1 ); ?> value="1" name="notification_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notification']['edit'], 1 ); ?> value="1" name="notification_edit" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['notification']['delete'], 1 ); ?> value="1" name="notification_delete">
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
								<input type="checkbox" <?php echo checked( $access_right['management']['holiday']['own_data'], 1 ); ?> value="1" name="holiday_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['holiday']['view'], 1 ); ?> value="1" name="holiday_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['holiday']['add'], 1 ); ?> value="1" name="holiday_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['holiday']['edit'], 1 ); ?> value="1" name="holiday_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['holiday']['delete'], 1 ); ?> value="1" name="holiday_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Holiday module code end. -->
				<!-- Custom module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Custom Field', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['custom_field']['own_data'], 1 ); ?> value="1" name="custom_field_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['custom_field']['view'], 1 ); ?> value="1" name="custom_field_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['custom_field']['add'], 1 ); ?> value="1" name="custom_field_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['custom_field']['edit'], 1 ); ?> value="1" name="custom_field_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['custom_field']['delete'], 1 ); ?> value="1" name="custom_field_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Report module code end. -->
				<!-- SMS Setting module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'SMS Setting', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_setting']['own_data'], 1 ); ?> value="1" name="mjschool_setting_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_setting']['view'], 1 ); ?> value="1" name="mjschool_setting_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_setting']['add'], 1 ); ?> value="1" disabled name="mjschool_setting_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_setting']['edit'], 1 ); ?> value="1" name="mjschool_setting_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_setting']['delete'], 1 ); ?> value="1" disabled name="mjschool_setting_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Report module code end. -->
				<!-- Email Template module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Email Template', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['email_template']['own_data'], 1 ); ?> value="1" name="email_template_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['email_template']['view'], 1 ); ?> value="1" name="email_template_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['email_template']['add'], 1 ); ?> value="1" disabled name="email_template_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['email_template']['edit'], 1 ); ?> value="1" name="email_template_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['email_template']['delete'], 1 ); ?> value="1" disabled name="email_template_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- Email Template module code end. -->
				<!-- SMS Template module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'SMS Template', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_template']['own_data'], 1 ); ?> value="1" name="mjschool_template_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_template']['view'], 1 ); ?> value="1" name="mjschool_template_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_template']['add'], 1 ); ?> value="1" disabled name="mjschool_template_add">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_template']['edit'], 1 ); ?> value="1" name="mjschool_template_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['mjschool_template']['delete'], 1 ); ?> value="1" disabled name="mjschool_template_delete">
							</label>
						</div>
					</div>
				</div>
				<!-- SMS Template module code end. -->
				<!-- Access Right module code.  -->
				<div class="row">
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<span class="mjschool-menu-label">
							<?php esc_html_e( 'Access Right', 'mjschool' ); ?>
						</span>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['access_right']['own_data'], 1 ); ?> value="1" name="access_right_own_data" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['access_right']['view'], 1 ); ?> value="1" name="access_right_view">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-10">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['access_right']['add'], 1 ); ?> value="1" name="access_right_add" disabled>
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['access_right']['edit'], 1 ); ?> value="1" name="access_right_edit">
							</label>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 mjschool-menu-left-15">
						<div class="mjschool-checkbox">
							<label>
								<input type="checkbox" <?php echo checked( $access_right['management']['access_right']['delete'], 1 ); ?> value="1" name="access_right_delete" disabled>
							</label>
						</div>
					</div>
				</div>
				<!-- Email Template module code end. -->
			</div>
			<?php wp_nonce_field( 'mjschool_save_management_access_right_nonce' ); ?>
			<div class="col-sm-6 mjschool-row-bottom mjschool-rtl-access-save-btn">
				<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_access_right" class="btn btn-success mjschool-save-btn" />
			</div>
		</form>
	</div><!--- END PANEL BODY DIV.-->
</div> <!--- END PANEL WHITE DIV. -->