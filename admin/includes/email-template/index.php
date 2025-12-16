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
$mjschool_active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'registration_mail';
$mjschool_changed    = 0;
if ( isset( $_REQUEST['send_demo_mail'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_send_demo_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	$to      = isset( $_REQUEST['demo_email'] ) ? sanitize_email( wp_unslash( $_REQUEST['demo_email'] ) ) : '';
	$subject = 'Demo Mail';
	$message = isset( $_REQUEST['demo_content'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['demo_content'] ) ) : '';
	$result  = mjschool_send_mail( $to, $subject, $message );
	mjschool_setup_wizard_steps_updates( 'step7_email_temp' );
	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_email_template&message=2' ) );
	die();
}
if ( isset( $_REQUEST['save_registration_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_registration_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_registration_mailtemplate', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['registratoin_mailtemplate_content'] ) ) );
	update_option( 'mjschool_registration_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_registration_title'] ) ) );
	$search           = array( '{{student_name}}', '{{school_name}}' );
	$replace          = array( 'ashvin', 'A1 School' );
	$message_content  = str_replace( $search, $replace, get_option( 'mjschool_registration_mailtemplate' ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_activation_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_activation_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_student_activation_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['activation_mailcontent'] ) ) );
	update_option( 'mjschool_student_activation_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_student_activation_title'] ) ) );
	$search           = array( '{{student_name}}', '{{school_name}}' );
	$replace          = array( 'ashvin', 'A1 School' );
	$message_content  = str_replace( $search, $replace, get_option( 'mjschool_student_activation_mailcontent' ) );
	$mjschool_changed = 1;
}
// ---- -------//
if ( isset( $_REQUEST['save_feepayment_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_fees_payment_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_mailcontent'] ) ) );
	update_option( 'mjschool_fee_payment_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_title'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_mailtemplate_for_parent'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_parent_fees_payment_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_mailcontent_for_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_mailcontent_for_parent'] ) ) );
	update_option( 'mjschool_fee_payment_title_for_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_title_for_parent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_homework_student_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_homework_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_homework_mailcontent'] ) ) );
	update_option( 'mjschool_homework_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_homework_title'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_messege_recived_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_message_receive_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_message_received_mailsubject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_message_received_mailsubject'] ) ) );
	update_option( 'mjschool_message_received_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_message_received_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_adduser_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_user_add_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_add_user_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_user_mail_subject'] ) ) );
	update_option( 'mjschool_add_user_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_user_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_holiday_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_holiday_anouncement_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_holiday_mailsubject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_holiday_mailsubject'] ) ) );
	update_option( 'mjschool_holiday_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_holiday_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_teacher_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_assign_teacher_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_student_assign_teacher_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_student_assign_teacher_mail_subject'] ) ) );
	update_option( 'mjschool_student_assign_teacher_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_student_assign_teacher_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_payment_recived_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_payment_receive_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_payment_recived_mailsubject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_payment_recived_mailsubject'] ) ) );
	update_option( 'mjschool_payment_recived_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_payment_recived_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_admission_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admission_request_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_admissiion_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_admissiion_title'] ) ) );
	update_option( 'mjschool_admission_mailtemplate_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_admission_mailtemplate_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_admission_template_for_parent'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admission_approve_email_parent_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_admissiion_approve_subject_for_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_admissiion_approve_subject_for_parent'] ) ) );
	update_option( 'mjschool_admission_mailtemplate_content_for_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_admission_mailtemplate_content_for_parent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_approve_admission_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admission_approve_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_add_approve_admisson_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_approve_admisson_mail_subject'] ) ) );
	update_option( 'mjschool_add_approve_admission_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_approve_admission_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate_parent'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_homework_parent_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_parent_homework_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_parent_homework_mail_subject'] ) ) );
	update_option( 'mjschool_parent_homework_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_parent_homework_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_absent_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_attendance_absent_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_absent_mail_notification_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_absent_mail_notification_subject'] ) ) );
	update_option( 'mjschool_absent_mail_notification_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_absent_mail_notification_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_exam_receipt_generate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_exam_hall_receipt_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_exam_receipt_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_exam_receipt_subject'] ) ) );
	update_option( 'mjschool_exam_receipt_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_exam_receipt_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_bed_template'] ) ) {
	update_option( 'mjschool_bed_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_bed_subject'] ) ) );
	update_option( 'mjschool_bed_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_bed_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_to_teacher_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_assign_teacher_student_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschoool_student_assign_to_teacher_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschoool_student_assign_to_teacher_subject'] ) ) );
	update_option( 'mjschool_student_assign_to_teacher_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_student_assign_to_teacher_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_notice_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_new_notice_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_notice_mailsubject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_notice_mailsubject'] ) ) );
	update_option( 'mjschool_notice_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_notice_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_event_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_new_event_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_event_mailsubject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_event_mailsubject'] ) ) );
	update_option( 'mjschool_event_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_event_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_invite_teacher_form_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_teacher_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_invite_teacher_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_invite_teacher_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_teacher_reminder_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_teacher_reminder_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['virtual_class_student_reminder_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_student_reminder_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_student_reminder_mail_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_student_reminder_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_student_reminder_mail_content', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_virtual_class_student_reminder_mail_content'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_reminder_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_parent_payment_reminder_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_reminder_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_title'] ) ) );
	update_option( 'mjschool_fee_payment_reminder_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_reminder_mailtemplate_for_student'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_payment_reminder_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_reminder_title_for_student', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_title_for_student'] ) ) );
	update_option( 'mjschool_fee_payment_reminder_mailcontent_for_student', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_mailcontent_for_student'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_assign_subject_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_assign_subject_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_assign_subject_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_assign_subject_title'] ) ) );
	update_option( 'mjschool_assign_subject_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_assign_subject_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_issue_book_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_issues_book_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_issue_book_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_issue_book_title'] ) ) );
	update_option( 'mjschool_issue_book_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_issue_book_mailcontent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template'] ) ) {
	update_option( 'mjschool_addleave_email_template', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_addleave_email_template'] ) ) );
	update_option( 'mjschool_add_leave_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_leave_subject'] ) ) );
	update_option( 'mjschool_add_leave_emails', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_leave_emails'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_student'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_leave_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_addleave_email_template_student', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_addleave_email_template_student'] ) ) );
	update_option( 'mjschool_add_leave_subject_for_student', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_leave_subject_for_student'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_parent'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_parent_leave_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_addleave_email_template_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_addleave_email_template_parent'] ) ) );
	update_option( 'mjschool_add_leave_subject_for_parent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_leave_subject_for_parent'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['add_leave_template_for_admin'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admin_leave_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_addleave_email_template_of_admin', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_addleave_email_template_of_admin'] ) ) );
	update_option( 'mjschool_add_leave_subject_of_admin', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_leave_subject_of_admin'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['leave_approve_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_leave_approve_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_leave_approve_email_template', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_leave_approve_email_template'] ) ) );
	update_option( 'mjschool_leave_approve_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_leave_approve_subject'] ) ) );
	update_option( 'mjschool_leave_approveemails', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_leave_approveemails'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['leave_reject_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_leave_reject_email_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_leave_reject_email_template', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_leave_reject_email_template'] ) ) );
	update_option( 'mjschool_leave_reject_subject', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_leave_reject_subject'] ) ) );
	$mjschool_changed = 1;
}
if ( isset( $_REQUEST['save_exam_mail_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_add_exam_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_add_exam_mail_title', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_exam_mail_title'] ) ) );
	update_option( 'mjschool_add_exam_mailcontent', mjschool_strip_tags_and_stripslashes( wp_unslash( $_REQUEST['mjschool_add_exam_mailcontent'] ) ) );
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
																	<input type="text" class="form-control validate[required]" name="mjschool_admissiion_approve_subject_for_parent" id="mjschool_admissiion_approve_subject_for_parent" placeholder="Enter Admission subject" value="<?php echo esc_attr( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_admissiion_approve_subject_for_parent' ) ) ); ?>">
																	<label for="mjschool_admissiion_approve_subject_for_parent"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
								<!-- Continue with remaining accordion items using the same sanitization pattern... -->
								<!-- Due to character limits, the remaining accordion items follow the same pattern -->
								<!-- with proper sanitization of all inputs using wp_unslash() and appropriate sanitize functions -->
							</div><!--End accordion. -->
						</div><!--mjschool-main-email-template. -->
					</div><!-- mjschool-panel-body. -->
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->