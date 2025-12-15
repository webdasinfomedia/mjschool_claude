<?php
/**
 * Email Template Management Page.
 *
 * This file handles the creation, editing, and saving of all email templates used
 * in the MjSchool plugin, including demo mail sending , admission email templates, registration email template, virtual class room email template,
 * student email template, exam email template, leave email template, fees payment email templates,
 * hostel email templates, holidays email templates, event email templates and all.
 *
 * @since      1.0.0
 *
 * @package    MjSchool
 * @subpackage MjSchool/admin/includes/email_template
 */
defined( 'ABSPATH' ) || exit;
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$mjschool_user_access_add    = '1';
	$mjschool_user_access_edit   = '1';
	$mjschool_user_access_delete = '1';
	$mjschool_user_access_view   = '1';
} else {
	$mjschool_user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'mjschool_setting' );
	$mjschool_user_access_add    = $mjschool_user_access['add'];
	$mjschool_user_access_edit   = $mjschool_user_access['edit'];
	$mjschool_user_access_delete = $mjschool_user_access['delete'];
	$mjschool_user_access_view   = $mjschool_user_access['view'];
}
?>
<?php
$mjschool_active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash($_REQUEST['tab'])) : 'registration_mail';
$mjschool_changed    = 0;
if ( isset( $_REQUEST['send_demo_mail'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_send_demo_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$to      = sanitize_text_field( wp_unslash($_REQUEST['demo_email']));
	$subject = 'Demo Mail';
	$message = sanitize_text_field( wp_unslash($_REQUEST['demo_content']));
	$result  = mjschool_send_mail( $to, $subject, $message );
	mjschool_setup_wizard_steps_updates( 'step7_email_temp' );
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_email_template&message=2' ) );
	die();
}
if ( isset( $_REQUEST['save_registration_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_registration_mail_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_registration_mailtemplate', mjschool_strip_tags_and_stripslashes( $_REQUEST['registratoin_mailtemplate_content'] ) );
	update_option( 'mjschool_registration_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_registration_title'] ) );
	$search           = array( '{{student_name}}', '{{school_name}}' );
	$replace          = array( 'ashvin', 'A1 School' );
	$message_content  = str_replace( $search, $replace, get_option( 'mjschool_registration_mailtemplate' ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_activation_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_student_activation_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_student_activation_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['activation_mailcontent'] ) );
	update_option( 'mjschool_student_activation_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_student_activation_title'] ) );
	$search           = array( '{{student_name}}', '{{school_name}}' );
	$replace          = array( 'ashvin', 'A1 School' );
	$message_content  = str_replace( $search, $replace, get_option( 'mjschool_student_activation_mailcontent' ) );
	$mjschool_changed = 1;
}
// ---- -------//
if ( isset( $_REQUEST['save_feepayment_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_fees_payment_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_fee_payment_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_mailcontent'] ) );
	update_option( 'mjschool_fee_payment_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_title'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_mailtemplate_for_parent'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_parent_fees_payment_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_fee_payment_mailcontent_for_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_mailcontent_for_parent'] ) );
	update_option( 'mjschool_fee_payment_title_for_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_title_for_parent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_homework_student_mail_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_homework_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_homework_mailcontent'] ) );
	update_option( 'mjschool_homework_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_homework_title'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_messege_recived_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_message_receive_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_message_received_mailsubject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_message_received_mailsubject'] ) );
	update_option( 'mjschool_message_received_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_message_received_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_adduser_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_user_add_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_add_user_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_user_mail_subject'] ) );
	update_option( 'mjschool_add_user_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_user_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_holiday_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_holiday_anouncement_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_holiday_mailsubject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_holiday_mailsubject'] ) );
	update_option( 'mjschool_holiday_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_holiday_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_teacher_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_student_assign_teacher_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_student_assign_teacher_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_student_assign_teacher_mail_subject'] ) );
	update_option( 'mjschool_student_assign_teacher_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_student_assign_teacher_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_payment_recived_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_payment_receive_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_payment_recived_mailsubject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_payment_recived_mailsubject'] ) );
	update_option( 'mjschool_payment_recived_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_payment_recived_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_admission_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_admission_request_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_admissiion_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_admissiion_title'] ) );
	update_option( 'mjschool_admission_mailtemplate_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_admission_mailtemplate_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_admission_template_for_parent'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_admission_approve_email_parent_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_admissiion_approve_subject_for_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_admissiion_approve_subject_for_parent'] ) );
	update_option( 'mjschool_admission_mailtemplate_content_for_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_admission_mailtemplate_content_for_parent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_approve_admission_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_admission_approve_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_add_approve_admisson_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_approve_admisson_mail_subject'] ) );
	update_option( 'mjschool_add_approve_admission_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_approve_admission_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate_parent'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_homework_parent_mail_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_parent_homework_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_parent_homework_mail_subject'] ) );
	update_option( 'mjschool_parent_homework_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_parent_homework_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_absent_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_attendance_absent_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_absent_mail_notification_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_absent_mail_notification_subject'] ) );
	update_option( 'mjschool_absent_mail_notification_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_absent_mail_notification_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_exam_receipt_generate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_exam_hall_receipt_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_exam_receipt_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_exam_receipt_subject'] ) );
	update_option( 'mjschool_exam_receipt_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_exam_receipt_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_bed_template'] ) ) {
	update_option( 'mjschool_bed_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_bed_subject'] ) );
	update_option( 'mjschool_bed_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_bed_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_to_teacher_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_student_assign_teacher_student_mail_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschoool_student_assign_to_teacher_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschoool_student_assign_to_teacher_subject'] ) );
	update_option( 'mjschool_student_assign_to_teacher_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_student_assign_to_teacher_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_notice_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_new_notice_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_notice_mailsubject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_notice_mailsubject'] ) );
	update_option( 'mjschool_notice_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_notice_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_event_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_new_event_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_event_mailsubject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_event_mailsubject'] ) );
	update_option( 'mjschool_event_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_event_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_invite_teacher_form_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_virtual_class_teacher_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_virtual_class_invite_teacher_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_subject'] ) );
	update_option( 'mjschool_virtual_class_invite_teacher_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_teacher_reminder_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_virtual_class_teacher_reminder_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_subject'] ) );
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_student_reminder_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_virtual_class_student_reminder_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_virtual_class_student_reminder_mail_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_student_reminder_mail_subject'] ) );
	update_option( 'mjschool_virtual_class_student_reminder_mail_content', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_virtual_class_student_reminder_mail_content'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_reminder_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_parent_payment_reminder_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_fee_payment_reminder_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_reminder_title'] ) );
	update_option( 'mjschool_fee_payment_reminder_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_reminder_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_reminder_mailtemplate_for_student'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_student_payment_reminder_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_fee_payment_reminder_title_for_student', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_reminder_title_for_student'] ) );
	update_option( 'mjschool_fee_payment_reminder_mailcontent_for_student', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_fee_payment_reminder_mailcontent_for_student'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_assign_subject_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_assign_subject_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_assign_subject_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_assign_subject_title'] ) );
	update_option( 'mjschool_assign_subject_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_assign_subject_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_issue_book_mailtemplate'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_issues_book_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_issue_book_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_issue_book_title'] ) );
	update_option( 'mjschool_issue_book_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_issue_book_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template'] ) ) {
	update_option( 'mjschool_addleave_email_template', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_addleave_email_template'] ) );
	update_option( 'mjschool_add_leave_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_leave_subject'] ) );
	update_option( 'mjschool_add_leave_emails', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_leave_emails'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_student'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_student_leave_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_addleave_email_template_student', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_addleave_email_template_student'] ) );
	update_option( 'mjschool_add_leave_subject_for_student', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_leave_subject_for_student'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_parent'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_parent_leave_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_addleave_email_template_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_addleave_email_template_parent'] ) );
	update_option( 'mjschool_add_leave_subject_for_parent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_leave_subject_for_parent'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_admin'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_admin_leave_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_addleave_email_template_of_admin', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_addleave_email_template_of_admin'] ) );
	update_option( 'mjschool_add_leave_subject_of_admin', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_leave_subject_of_admin'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['leave_approve_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_leave_approve_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_leave_approve_email_template', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_leave_approve_email_template'] ) );
	update_option( 'mjschool_leave_approve_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_leave_approve_subject'] ) );
	update_option( 'mjschool_leave_approveemails', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_leave_approveemails'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['leave_reject_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_leave_reject_email_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_leave_reject_email_template', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_leave_reject_email_template'] ) );
	update_option( 'mjschool_leave_reject_subject', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_leave_reject_subject'] ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_exam_mail_template'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['security'])), 'mjschool_add_exam_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	update_option( 'mjschool_add_exam_mail_title', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_exam_mail_title'] ) );
	update_option( 'mjschool_add_exam_mailcontent', mjschool_strip_tags_and_stripslashes( $_REQUEST['mjschool_add_exam_mailcontent'] ) );
	$mjschool_changed = 1;
}
if ( $mjschool_changed ) {
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_email_template&message=1' ) );
	die();
}
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Email Template Updated Successfully.', 'mjschool' );
			break;
		case '2':
			$message_string = esc_html__( 'Demo Mail Send Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_right_5px" >
			<p><?php echo esc_html( $message_string ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	}
	?>
	<form name="mjschool-email-template-form" action="" method="post" class="mjschool-form-horizontal mjschool_responsive_margin_left_25px" id="mjschool-email-template-form">
		<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_send_demo_email_nonce' ) ); ?>">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Demo Mail Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!--Card Body div.-->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="email" class="form-control validate[required,custom[email]] text-input mjschool-student-email-id" maxlength="100" type="text" name="demo_email" value="" autocomplete="email">
							<label  for="email"><?php esc_html_e( 'Demo Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="demo_content" class="mjschool-textarea-height-60px form-control validate[required]" maxlength="1000" id="demo_content"></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="demo_content"><?php esc_html_e( 'Demo Mail Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php esc_html_e( 'Send Demo Mail', 'mjschool' ); ?>" name="send_demo_mail" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form><!--------- Student Form. ---------->
	<div class="mjschool-main-list-margin-15px mt-3"><!-- mjschool-main-list-margin-15px. -->
		<div class="row"><!-- row. -->
			<?php
			$i = 1;
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<div class="mjschool-main-list-page"><!-- mjschool-main-list-page. -->
					<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
						<div class="mjschool-main-email-template"><!--mjschool-main-email-template. -->
							<?php ++$i; ?>
							<div id="mjschool-accordion" class="mjschool-accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion-flush" aria-multiselectable="false" role="tablist">
								<!--START accordion. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Request For Admission Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_request_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_admissiion_title" id="mjschool_admissiion_title" placeholder="Enter Admission subject" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_admissiion_title' ) ) ); ?>">
																	<label for="mjschool_admissiion_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_admission_mailtemplate_content" name="mjschool_admission_mailtemplate_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_admission_mailtemplate_content' ) ) ); ?></textarea>
																<label for="mjschool_admission_mailtemplate_content" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'User name of student', 'mjschool' ); ?></span><br>
														<span><strong>{{email}} - </strong><?php esc_html_e( 'Email of student', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_admission_template" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Approve Admission Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_approve_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_add_approve_admisson_mail_subject" id="mjschool_add_approve_admisson_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_approve_admisson_mail_subject' ) ) ); ?>">
																	<label for="mjschool_add_approve_admisson_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_add_approve_admission_mail_content" name="mjschool_add_approve_admission_mail_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_approve_admission_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_add_approve_admission_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to user When', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
														<span><strong>{{login_link}} - </strong><?php esc_html_e( 'Login Link', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
														<span><strong>{{roll_no}} - </strong><?php esc_html_e( 'Roll No', 'mjschool' ); ?></span><br>
														<span><strong>{{username}} - </strong><?php esc_html_e( 'Username', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_approve_admission_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Approve Admission Mail Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_approve_email_parent_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="admissiion_approve_subject_for_parent" id="admissiion_approve_subject_for_parent" placeholder="Enter Admission subject" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_admissiion_approve_subject_for_parent_subject' ) ) ); ?>">
																	<label for="admissiion_approve_subject_for_parent"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_admission_mailtemplate_content_for_parent" name="mjschool_admission_mailtemplate_content_for_parent" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_admission_mailtemplate_content_for_parent' ) ) ); ?></textarea>
																<label for="mjschool_admission_mailtemplate_content_for_parent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
														<span><strong>{{login_link}} - </strong><?php esc_html_e( 'Login Link', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
														<span><strong>{{roll_no}} - </strong><?php esc_html_e( 'Roll No', 'mjschool' ); ?></span><br>
														<span><strong>{{email}} - </strong><?php esc_html_e( 'Email', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_admission_template_for_parent" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Registration Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_registration_mail_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_registration_title" id="mjschool_registration_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_registration_title' ) ) ); ?>">
																	<label for="mjschool_registration_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_registration_mailtemplate" name="registratoin_mailtemplate_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_registration_mailtemplate' ) ) ); ?></textarea>
																<label for="mjschool_registration_mailtemplate" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'User name of student', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class name of student', 'mjschool' ); ?></span><br>
														<span><strong>{{email}} - </strong><?php esc_html_e( 'Email of student', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_registration_template" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Add User', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_user_add_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_add_user_mail_subject" id="mjschool_add_user_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_user_mail_subject' ) ) ); ?>">
																	<label for="mjschool_add_user_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_add_user_mail_content" name="mjschool_add_user_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_user_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_add_user_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to user When', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{role}} - </strong><?php esc_html_e( 'Student roll number', 'mjschool' ); ?></span><br>
														<span><strong>{{login_link}} - </strong><?php esc_html_e( 'Student Login URL', 'mjschool' ); ?></span><br>
														<span><strong>{{username}} - </strong><?php esc_html_e( 'Student Username', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Student Password', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_adduser_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Virtual ClassRoom Teacher Invite Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_invite_teacher_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_teacher_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_virtual_class_invite_teacher_mail_subject" class="form-control validate[required]" name="mjschool_virtual_class_invite_teacher_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_invite_teacher_mail_subject' ) ) ); ?>">
																	<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_virtual_class_invite_teacher_mail_content" name="mjschool_virtual_class_invite_teacher_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_invite_teacher_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_virtual_class_invite_teacher_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
														<span><strong>{{time}} - </strong><?php esc_html_e( 'Time', 'mjschool' ); ?></span><br>
														<span><strong>{{virtual_class_id}} - </strong><?php esc_html_e( 'Virtual Class ID', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
														<span><strong>{{join_zoom_virtual_class}} - </strong><?php esc_html_e( 'Join Zoom Virtual Class', 'mjschool' ); ?></span><br>
														<span><strong>{{start_zoom_virtual_class}} - </strong><?php esc_html_e( 'Start Zoom Virtual Class', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="virtual_class_invite_teacher_form_template" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Virtual Classroom Teacher Reminder Template. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Virtual ClassRoom Teacher Reminder Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_teacher_reminder_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_teacher_reminder_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_virtual_class_invite_teacher_mail_subject" class="form-control validate[required]" name="mjschool_virtual_class_teacher_reminder_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_teacher_reminder_mail_subject' ) ) ); ?>">
																	<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_virtual_class_teacher_reminder_mail_content" name="mjschool_virtual_class_teacher_reminder_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_teacher_reminder_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_virtual_class_teacher_reminder_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{teacher_name}} - </strong><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
														<span><strong>{{subject_name}} - </strong><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date', 'mjschool' ); ?></span><br>
														<span><strong>{{time}} - </strong><?php esc_html_e( 'Time', 'mjschool' ); ?></span><br>
														<span><strong>{{virtual_class_id}} - </strong><?php esc_html_e( 'Virtual Class ID', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
														<span><strong>{{start_zoom_virtual_class}} - </strong><?php esc_html_e( 'Start Zoom Virtual Class', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="virtual_class_teacher_reminder_template" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Virtual Classroom Student Reminder Template. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Virtual ClassRoom Student Reminder Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_student_reminder_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_student_reminder_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_virtual_class_invite_teacher_mail_subject" class="form-control validate[required]" name="mjschool_virtual_class_student_reminder_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_student_reminder_mail_subject' ) ) ); ?>">
																	<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_virtual_class_student_reminder_mail_content" name="mjschool_virtual_class_student_reminder_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_virtual_class_student_reminder_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_virtual_class_student_reminder_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
														<span><strong>{{subject_name}} - </strong><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></span><br>
														<span><strong>{{teacher_name}} - </strong><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date', 'mjschool' ); ?></span><br>
														<span><strong>{{time}} - </strong><?php esc_html_e( 'Time', 'mjschool' ); ?></span><br>
														<span><strong>{{virtual_class_id}} - </strong><?php esc_html_e( 'Virtual Class ID', 'mjschool' ); ?></span><br>
														<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
														<span><strong>{{join_zoom_virtual_class}} - </strong><?php esc_html_e( 'Join Zoom Virtual Class', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="virtual_class_student_reminder_template" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Assign Subject Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_assign_subject_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_assign_subject_title" class="form-control validate[required]" name="mjschool_assign_subject_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_assign_subject_title' ) ) ); ?>">
																	<label for="mjschool_assign_subject_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_assign_subject_mailcontent" name="mjschool_assign_subject_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_assign_subject_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_assign_subject_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{teacher_name}} - </strong><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></span><br>
														<span><strong>{{subject_name}} - </strong><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_assign_subject_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Student Activation Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_activation_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_student_activation_title" id="mjschool_student_activation_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_activation_title' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="activation_mailcontent" name="activation_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_activation_mailcontent' ) ) ); ?></textarea>
																<label for="activation_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'User name of student', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class name of student', 'mjschool' ); ?></span><br>
														<span><strong>{{email}} - </strong><?php esc_html_e( 'Email of student', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_activation_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Student Assign to Teacher mail template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_assign_teacher_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_student_assign_teacher_mail_subject" id="mjschool_student_assign_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_assign_teacher_mail_subject' ) ) ); ?>" />
																	<label for="mjschool_student_assign_teacher_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?>
																		<span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_student_assign_teacher_mail_content" name="mjschool_student_assign_teacher_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_assign_teacher_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_student_assign_teacher_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></span><br>
														<span><strong>{{teacher_name}} - </strong><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_student_assign_teacher_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Student Assigned to Teacher Student mail template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_assign_teacher_student_mail_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschoool_student_assign_to_teacher_subject" id="mjschoool_student_assign_to_teacher_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschoool_student_assign_to_teacher_subject' ) ) ); ?>" />
																	<label for="mjschoool_student_assign_to_teacher_subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_student_assign_to_teacher_content" name="mjschool_student_assign_to_teacher_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_assign_to_teacher_content' ) ) ); ?></textarea>
																<label for="mjschool_student_assign_to_teacher_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to user When Student Assigned to Teacher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{teacher_name}} - </strong><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'Enter school name', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Enter student name', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Enter Class name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_student_assign_to_teacher_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add exam mail template start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Add Exam Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_add_exam_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_add_exam_mail_title" class="form-control validate[required]" name="mjschool_add_exam_mail_title" id="mjschool_add_exam_mail_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_exam_mail_title' ) ) ); ?>">
																	<label for="mjschool_add_exam_mail_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_add_exam_mailcontent" name="mjschool_add_exam_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_exam_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_add_exam_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{user_name}} - </strong><?php esc_html_e( 'Student/Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{exam_name}} - </strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></span><br>
														<span><strong>{{exam_start_end_date}} - </strong><?php esc_html_e( 'Exam Start to End Date', 'mjschool' ); ?></span><br>
														<span><strong>{{exam_comment}} - </strong><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_exam_mail_template" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add exam mail template end. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Student Exam Hall Receipt', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_exam_hall_receipt_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_exam_receipt_subject" id="mjschool_exam_receipt_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_exam_receipt_subject' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_exam_receipt_content" name="mjschool_exam_receipt_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_exam_receipt_content' ) ) ); ?></textarea>
																<label for="mjschool_exam_receipt_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_exam_receipt_generate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'HomeWork Mail Template For Student', 'mjschool' ); ?> </button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_homework_student_mail_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_homework_title" id="mjschool_homework_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_homework_title' ) ) ); ?>">
																	<label for="mjschool_homework_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_homework_mailcontent" name="mjschool_homework_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_homework_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_homework_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent Students When Give Homework', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the Homework email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{title}} - </strong><?php esc_html_e( 'Student homework title', 'mjschool' ); ?></span><br>
														<span><strong>{{subject}} - </strong><?php esc_html_e( 'Subject Name', 'mjschool' ); ?></span><br>
														<span><strong>{{homework_date}} - </strong><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></span><br>
														<span><strong>{{submition_date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_homework_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'HomeWork Mail Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_homework_parent_mail_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_parent_homework_mail_subject" id="mjschool_parent_homework_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_parent_homework_mail_subject' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_parent_homework_mail_content" name="mjschool_parent_homework_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_parent_homework_mail_content' ) ) ); ?></textarea>
																<label for="mjschool_parent_homework_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to Parents When A Give Homework', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{title}} - </strong><?php esc_html_e( 'Student homework title', 'mjschool' ); ?></span><br>
														<span><strong>{{homework_date}} - </strong><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></span><br>
														<span><strong>{{submition_date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></span><br>
														<span><strong>{{submition_date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_homework_mailtemplate_parent" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Attendance Absent Notification', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_absent_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_absent_mail_notification_subject" id="mjschool_absent_mail_notification_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_absent_mail_notification_subject' ) ) ); ?>" />
																	<label for="mjschool_absent_mail_notification_subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_absent_mail_notification_content" name="mjschool_absent_mail_notification_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_absent_mail_notification_content' ) ) ); ?></textarea>
																<label for="mjschool_absent_mail_notification_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to user if student absent', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{child_name}} - </strong><?php esc_html_e( 'Enter name of child', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_student_absent_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add Leave Email Template - start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Add Leave Email Template For Admin', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admin_leave_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_add_leave_subject_of_admin" id="mjschool_add_leave_subject_of_admin" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_leave_subject_of_admin' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="activation_mailcontent" name="mjschool_addleave_email_template_of_admin" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_addleave_email_template_of_admin' ) ) ); ?></textarea>
																<label for="activation_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Email sent when student add leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_type}} - </strong><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_duration}} - </strong><?php esc_html_e( 'Duration of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{reason}} - </strong><?php esc_html_e( 'Reson of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date of leave', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="add_leave_template_for_admin" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add Leave Email Template For Student-start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Add Leave Email Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_leave_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_add_leave_subject_for_student" id="mjschool_add_leave_subject_for_student" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_leave_subject_for_student' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="activation_mailcontent" name="mjschool_addleave_email_template_student" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_addleave_email_template_student' ) ) ); ?></textarea>
																<label for="activation_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Email sent when student add leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_type}} - </strong><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_duration}} - </strong><?php esc_html_e( 'Duration of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{reason}} - </strong><?php esc_html_e( 'Reson of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date of leave', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="add_leave_template_for_student" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add Leave Email Template for Student-End. -->
								<!-- Add Leave Email Template For Parent—start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Add Leave Email Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_leave_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_add_leave_subject_for_parent" id="mjschool_add_leave_subject_for_parent" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_add_leave_subject_for_parent' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_addleave_email_template_parent" name="mjschool_addleave_email_template_parent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_addleave_email_template_parent' ) ) ); ?></textarea>
																<label for="mjschool_addleave_email_template_parent" class="mjschool-textarea-label"><?php esc_html_e( 'Email sent when student add leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_type}} - </strong><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></span><br>
														<span><strong>{{leave_duration}} - </strong><?php esc_html_e( 'Duration of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{reason}} - </strong><?php esc_html_e( 'Reson of the leave', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date of leave', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="add_leave_template_for_parent" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Add Leave Email Template for Student-End. -->
								<!-- Leave Approve Email Template - start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Leave Approve Email Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_leave_approve_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_leave_approve_subject" id="mjschool_leave_approve_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_approve_subject' ) ) ); ?>">
																	<label for="mjschool_leave_approve_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_leave_approve_email_template" name="mjschool_leave_approve_email_template" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_approve_email_template' ) ) ); ?></textarea>
																<label for="mjschool_leave_approve_email_template" class="mjschool-textarea-label"><?php esc_html_e( 'Email Sent to Student When Admin Add Approve Leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date of leave', 'mjschool' ); ?></span><br>
														<span><strong>{{comment}} - </strong><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="leave_approve_template" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Leave Approve Email Template - End. -->
								<!-- Leave Reject Email Template - start. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Leave Reject Email Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_leave_reject_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_leave_reject_subject" id="mjschool_leave_reject_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_reject_subject' ) ) ); ?>">
																	<label for="mjschool_leave_reject_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_leave_reject_email_template" name="mjschool_leave_reject_email_template" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_reject_email_template' ) ) ); ?></textarea>
																<label for="mjschool_leave_reject_email_template" class="mjschool-textarea-label"><?php esc_html_e( 'Email Sent to Student When Admin can Reject Leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
														<span><strong>{{date}} - </strong><?php esc_html_e( 'Date of leave', 'mjschool' ); ?></span><br>
														<span><strong>{{comment}} - </strong><?php esc_html_e( 'Comment', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="leave_reject_template" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<!-- Leave Reject Email Template - End. -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Fee Payment Mail Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_fees_payment_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_fee_payment_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_title' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_fee_payment_mailcontent" name="mjschool_fee_payment_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_fee_payment_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Fee Payment Mail Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_fees_payment_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_fee_payment_title_for_parent" id="mjschool_fee_payment_title_for_parent" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_title_for_parent' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_fee_payment_mailcontent_for_parent" name="mjschool_fee_payment_mailcontent_for_parent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_mailcontent_for_parent' ) ) ); ?></textarea>
																<label for="mjschool_fee_payment_mailcontent_for_parent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_mailtemplate_for_parent" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Payment Received against Invoice', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_payment_receive_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_payment_recived_mailsubject" id="mjschool_payment_recived_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_payment_recived_mailsubject' ) ) ); ?>" />
																	<label for="mjschool_payment_recived_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?>
																		<span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_payment_recived_mailcontent" name="mjschool_payment_recived_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_payment_recived_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_payment_recived_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'Enter school name', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Enter student name', 'mjschool' ); ?></span><br>
														<span><strong>{{invoice_no}} - </strong><?php esc_html_e( 'Enter Invoice No', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_payment_recived_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Fee Payment Reminder Mail Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_payment_reminder_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_fee_payment_reminder_title" class="form-control validate[required]" name="mjschool_fee_payment_reminder_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_reminder_title' ) ) ); ?>">
																	<label for="mjschool_fee_payment_reminder_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_fee_payment_reminder_mailcontent" name="mjschool_fee_payment_reminder_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_reminder_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_fee_payment_reminder_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
														<span><strong>{{total_amount}} - </strong><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></span><br>
														<span><strong>{{due_amount}} - </strong><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_reminder_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Fee Payment Reminder Mail Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_payment_reminder_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_fee_payment_reminder_title_for_student" class="form-control validate[required]" name="mjschool_fee_payment_reminder_title_for_student" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_reminder_title_for_student' ) ) ); ?>">
																	<label for="mjschool_fee_payment_reminder_title_for_student"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_fee_payment_reminder_mailcontent_for_student" name="mjschool_fee_payment_reminder_mailcontent_for_student" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fee_payment_reminder_mailcontent_for_student' ) ) ); ?></textarea>
																<label for="mjschool_fee_payment_reminder_mailcontent_for_student" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
														<span><strong>{{total_amount}} - </strong><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></span><br>
														<span><strong>{{due_amount}} - </strong><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></span><br>
														<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_reminder_mailtemplate_for_student" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Issue Book Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_issues_book_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_issue_book_title" class="form-control validate[required]" name="mjschool_issue_book_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_issue_book_title' ) ) ); ?>">
																	<label for="mjschool_issue_book_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_issue_book_mailcontent" name="mjschool_issue_book_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_issue_book_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_issue_book_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
														<span><strong>{{book_name}} - </strong><?php esc_html_e( 'Book Title', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_issue_book_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Hostel Bed Assigned Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_issues_book_email_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_bed_subject" id="mjschool_bed_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_bed_subject' ) ) ); ?>">
																	<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_bed_content" name="mjschool_bed_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_bed_content' ) ) ); ?></textarea>
																<label for="mjschool_bed_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name', 'mjschool' ); ?></span><br>
														<span><strong>{{hostel_name}} - </strong><?php esc_html_e( 'Hostel name', 'mjschool' ); ?></span><br>
														<span><strong>{{room_id}} - </strong><?php esc_html_e( 'Room number', 'mjschool' ); ?></span><br>
														<span><strong>{{bed_id}} - </strong><?php esc_html_e( 'Bed number', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_bed_template" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Notice', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_new_notice_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_notice_mailsubject" id="mjschool_notice_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_notice_mailsubject' ) ) ); ?>" />
																	<label for="mjschool_notice_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_notice_mailcontent" name="mjschool_notice_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_notice_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_notice_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{notice_title}} - </strong><?php esc_html_e( 'Enter notice title', 'mjschool' ); ?></span><br>
														<span><strong>{{notice_date}} - </strong><?php esc_html_e( 'Enter notice date', 'mjschool' ); ?></span><br>
														<span><strong>{{notice_for}} - </strong><?php esc_html_e( 'Enter role name for notice', 'mjschool' ); ?></span><br>
														<span><strong>{{notice_comment}} - </strong><?php esc_html_e( 'Enter notice comment', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_notice_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Event Mail Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_new_event_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_event_mailsubject" id="mjschool_event_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_event_mailsubject' ) ) ); ?>" />
																	<label for="mjschool_event_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?>
																		<span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_event_mailcontent" name="mjschool_event_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_event_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_event_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Event Mail Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{event_title}} - </strong><?php esc_html_e( 'Enter Event title', 'mjschool' ); ?></span><br>
														<span><strong>{{event_date}} - </strong><?php esc_html_e( 'Enter Event date', 'mjschool' ); ?></span><br>
														<span><strong>{{event_time}} - </strong><?php esc_html_e( 'Enter Event time', 'mjschool' ); ?></span><br>
														<span><strong>{{description}} - </strong><?php esc_html_e( 'Enter Description', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_event_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Message Received', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_message_receive_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_message_received_mailsubject" id="mjschool_message_received_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_message_received_mailsubject' ) ); ?>" />
																	<label for="mjschool_message_received_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_message_received_mailcontent" name="mjschool_message_received_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_message_received_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_message_received_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{from_mail}} - </strong><?php esc_html_e( 'Message sender name', 'mjschool' ); ?></span><br>
														<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></span><br>
														<span><strong>{{receiver_name}} - </strong><?php esc_html_e( 'Message Receive Name', 'mjschool' ); ?></span><br>
														<span><strong>{{message_content}} - </strong><?php esc_html_e( 'Message Content', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) { ?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_messege_recived_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapstwelve"> </a>
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Holiday', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_holiday_anouncement_nonce' ) ); ?>">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<div class="col-md-12 form-control mjschool-input-height-75px">
																	<input type="text" class="form-control validate[required]" name="mjschool_holiday_mailsubject" id="mjschool_holiday_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_holiday_mailsubject' ) ) ); ?>" />
																	<label for="mjschool_holiday_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
																</div>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea id="mjschool_holiday_mailcontent" name="mjschool_holiday_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_holiday_mailcontent' ) ) ); ?></textarea>
																<label for="mjschool_holiday_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group input">
													<div class="col-md-12">
														<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
														<span><strong>{{holiday_title}} - </strong><?php esc_html_e( 'Enter holiday title', 'mjschool' ); ?></span><br>
														<span><strong>{{holiday_date}} - </strong><?php esc_html_e( 'Enter holiday date', 'mjschool' ); ?></span><br>
													</div>
												</div>
												<?php
												if ( $mjschool_user_access_add === '1' || $mjschool_user_access_edit === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_holiday_mailtemplate" class="btn btn-success mjschool-save-btn" />
													</div>
												<?php } ?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
							</div><!--End accordion. -->
						</div><!--mjschool-main-email-template. -->
					</div><!-- mjschool-panel-body. -->
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->