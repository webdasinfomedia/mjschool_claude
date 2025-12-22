<?php

/**
 * Email and SMS Template Management Page.
 *
 * This file serves as the administrative view and controller for managing all
 * **Email and SMS notification templates** used throughout the Mjschool system.
 * It allows administrators to customize the subject, body content, and variables
 * for various automated communications, such as registration, admission approval,
 * fees payment, and library book issuance.
 *
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Performing necessary browser/JavaScript checks and implementing
 * **role-based access control** to ensure only authorized users can 'view' and
 * 'edit' (or 'add') these sensitive templates.
 * 2. **Form Submission**: Handling the form submission logic for saving changes to
 * the various template options (`mjschool_registration_mailcontent`, etc.) using
 * WordPress's `update_option()` function.
 * 3. **Tabular Interface**: Displaying a user interface, likely structured with tabs
 * (e.g., General Settings, Registration, Fees, Library), to separate and manage
 * the different template types.
 * 4. **WYSIWYG Integration**: Providing an advanced editor (implied by the `<textarea>`
 * structure for body content) to facilitate rich text editing for email bodies.
 * 5. **Shortcode/Variable Documentation**: Clearly listing the available dynamic
 * variables (e.g., `{{student_name}}`, `{{school_name}}`) that can be used
 * within the templates.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( isset( $user_access['view'] ) && $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
			if ( isset( $user_access['edit'] ) && $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) ) {
			if ( isset( $user_access['add'] ) && $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$changed = 0;
if ( isset( $_REQUEST['save_registration_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_registration_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_registration_mailtemplate', wp_kses_post( wp_unslash( $_REQUEST['registratoin_mailtemplate_content'] ) ) );
	update_option( 'mjschool_registration_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_registration_title'] ) ) );
	$changed         = 1;
}
if ( isset( $_REQUEST['save_activation_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_activation_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_student_activation_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['activation_mailcontent'] ) ) );
	update_option( 'mjschool_student_activation_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_student_activation_title'] ) ) );
	$changed         = 1;
}
if ( isset( $_REQUEST['save_feepayment_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_fees_payment_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_fee_payment_mailcontent'] ) ) );
	update_option( 'mjschool_fee_payment_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_fee_payment_title'] ) ) );
	$changed         = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_homework_student_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_homework_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_homework_mailcontent'] ) ) );
	update_option( 'mjschool_homework_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_homework_title'] ) ) );
	$changed         = 1;
}
if ( isset( $_REQUEST['save_messege_recived_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_message_received_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_message_received_mailsubject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_message_received_mailsubject'] ) ) );
	update_option( 'mjschool_message_received_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_message_received_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_adduser_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_add_user_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_add_user_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_add_user_mail_subject'] ) ) );
	update_option( 'mjschool_add_user_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_add_user_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_holiday_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_holiday_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_holiday_mailsubject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_holiday_mailsubject'] ) ) );
	update_option( 'mjschool_holiday_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_holiday_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_teacher_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_assign_teacher_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_student_assign_teacher_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_student_assign_teacher_mail_subject'] ) ) );
	update_option( 'mjschool_student_assign_teacher_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_student_assign_teacher_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_payment_recived_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_payment_receive_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_payment_recived_mailsubject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_payment_recived_mailsubject'] ) ) );
	update_option( 'mjschool_payment_recived_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_payment_recived_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_admission_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admission_request_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_admissiion_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_admissiion_title'] ) ) );
	update_option( 'mjschool_admission_mailtemplate_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_admission_mailtemplate_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_approve_admission_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_admission_approve_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_add_approve_admisson_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_add_approve_admisson_mail_subject'] ) ) );
	update_option( 'mjschool_add_approve_admission_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_add_approve_admission_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_homework_mailtemplate_parent'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_homework_parent_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_parent_homework_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_parent_homework_mail_subject'] ) ) );
	update_option( 'mjschool_parent_homework_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_parent_homework_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_student_absent_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_attendance_absent_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_absent_mail_notification_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_absent_mail_notification_subject'] ) ) );
	update_option( 'mjschool_absent_mail_notification_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_absent_mail_notification_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_exam_receipt_generate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_student_exam_hall_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_exam_receipt_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_exam_receipt_subject'] ) ) );
	update_option( 'mjschool_exam_receipt_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_exam_receipt_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_bed_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_hostel_bed_assign_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_bed_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_bed_subject'] ) ) );
	update_option( 'mjschool_bed_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_bed_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_student_assign_to_teacher_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_teacher_assign_student_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschoool_student_assign_to_teacher_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschoool_student_assign_to_teacher_subject'] ) ) );
	update_option( 'mjschoool_student_assign_to_teacher_content', wp_kses_post( wp_unslash( $_REQUEST['mjschoool_student_assign_to_teacher_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_notice_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_notice_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_notice_mailsubject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_notice_mailsubject'] ) ) );
	update_option( 'mjschool_notice_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_notice_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['virtual_class_invite_teacher_form_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_teacher_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_invite_teacher_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_invite_teacher_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_virtual_class_invite_teacher_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['virtual_class_teacher_reminder_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_teacher_reminder_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_teacher_reminder_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_virtual_class_teacher_reminder_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['virtual_class_student_reminder_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_virtual_class_student_reminder_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_virtual_class_student_reminder_mail_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_virtual_class_student_reminder_mail_subject'] ) ) );
	update_option( 'mjschool_virtual_class_student_reminder_mail_content', wp_kses_post( wp_unslash( $_REQUEST['mjschool_virtual_class_student_reminder_mail_content'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_feepayment_reminder_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_fee_payment_reminder_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_fee_payment_reminder_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_title'] ) ) );
	update_option( 'mjschool_fee_payment_reminder_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_fee_payment_reminder_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_assign_subject_mailtemplate'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_assign_subject_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_assign_subject_title', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_assign_subject_title'] ) ) );
	update_option( 'mjschool_assign_subject_mailcontent', wp_kses_post( wp_unslash( $_REQUEST['mjschool_assign_subject_mailcontent'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['add_leave_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_add_leave_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_addleave_email_template', wp_kses_post( wp_unslash( $_REQUEST['mjschool_addleave_email_template'] ) ) );
	update_option( 'mjschool_add_leave_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_add_leave_subject'] ) ) );
	update_option( 'mjschool_add_leave_emails', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_add_leave_emails'] ) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['leave_approve_template'] ) ) {
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_approve_leave_mail_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}
	update_option( 'mjschool_leave_approve_email_template', wp_kses_post( wp_unslash( $_REQUEST['mjschool_leave_approve_email_template'] ) ) );
	update_option( 'mjschool_leave_approve_subject', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_leave_approve_subject'] ) ) );
	update_option( 'mjschool_leave_approveemails', sanitize_text_field( wp_unslash( $_REQUEST['mjschool_leave_approveemails'] ) ) );
	$changed = 1;
}
if ( $changed ) {
	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=email-template&message=1' ) );
	die();
}
$i = 1;
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'Email Template Updated successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
				<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span>
			</button>
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel mjschool-panel-white mjschool-frontend-list-margin-30px-res mjschool_box_shadow_none" >
				<div class="mjschool-main-email-template"><!--Mjschool-main-email-template. -->
					<?php ++$i; ?>
					<div id="accordion" class="mjschool_fix_accordion mjschool-accordion panel-group accordion accordion-flush frontend_email_bg_color" id="mjschool-accordion-flush" aria-multiselectable="true" role="tablist">
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Request For Admission Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_request_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_admissiion_title" type="text" class="form-control validate[required]" name="mjschool_admissiion_title" placeholder="Enter Admission subject" value="<?php echo esc_attr( get_option( 'mjschool_admissiion_title' ) ); ?>">
															<label for="mjschool_admissiion_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_admission_mailtemplate_content" name="mjschool_admission_mailtemplate_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_admission_mailtemplate_content' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_admission_template" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Approve Admission Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_approve_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_add_approve_admisson_mail_subject" type="text" class="form-control validate[required]" name="mjschool_add_approve_admisson_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_add_approve_admisson_mail_subject' ) ); ?>">
															<label for="mjschool_add_approve_admisson_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_add_approve_admission_mail_content" name="mjschool_add_approve_admission_mail_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_add_approve_admission_mail_content' ) ); ?></textarea>
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
												<span><strong>{{role}} - </strong><?php esc_html_e( 'Role', 'mjschool' ); ?></span><br>
												<span><strong>{{login_link}} - </strong><?php esc_html_e( 'Login Link', 'mjschool' ); ?></span><br>
												<span><strong>{{username}} - </strong><?php esc_html_e( 'Username', 'mjschool' ); ?></span><br>
												<span><strong>{{password}} - </strong><?php esc_html_e( 'Password', 'mjschool' ); ?></span><br>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_approve_admission_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Registration Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_registration_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_registration_title" type="text" class="form-control validate[required]" name="mjschool_registration_title"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_registration_title' ) ); ?>">
															<label for="mjschool_registration_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_registratoin_mailtemplate_content" name="registratoin_mailtemplate_content" class="form-control min_height_200 validate[required] h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_registration_mailtemplate' ) ); ?></textarea>
														<label for="mjschool_registratoin_mailtemplate_content" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_registration_template" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Student Activation Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_activation_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_student_activation_title" type="text" class="form-control validate[required]" name="mjschool_student_activation_title" id="mjschool_student_activation_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_student_activation_title' ) ); ?>">
															<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_activation_mailcontent" name="activation_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_student_activation_mailcontent' ) ); ?></textarea>
														<label for="mjschool_activation_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_activation_mailtemplate" class="btn btn-success mjschool-save-btn" />
											</div>
											<?php
										}
										?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
						<!-- Add leave email template - start. -->
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Add Leave Email Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_add_leave_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_add_leave_subject" type="text" class="form-control validate[required]" name="mjschool_add_leave_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_add_leave_subject' ) ); ?>">
															<label for="mjschool_add_leave_subject"><?php esc_html_e( 'Email Subjec', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_activation_mailcontent" name="mjschool_addleave_email_template" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_addleave_email_template' ) ); ?></textarea>
														<label for="mjschool_activation_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Email sent when student add leave', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
										</div>
										<div class="form-group input">
											<div class="col-md-12">
												<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
												<span><strong>{{student_name}} - </strong><?php esc_html_e( 'The student full name or login name (whatever is available)', 'mjschool' ); ?></span><br>
												<span><strong>{{user_name}} - </strong><?php esc_html_e( 'User name of student', 'mjschool' ); ?></span><br>
												<span><strong>{{leave_type}} - </strong><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></span><br>
												<span><strong>{{leave_duration}} - </strong><?php esc_html_e( 'Duration of the leave', 'mjschool' ); ?></span><br>
												<span><strong>{{reason}} - </strong><?php esc_html_e( 'Reson of the leave', 'mjschool' ); ?></span><br>
												<span><strong>{{start_date}} - </strong><?php esc_html_e( 'Date of leave start', 'mjschool' ); ?></span><br>
												<span><strong>{{end_date}} - </strong><?php esc_html_e( 'Date of leave end', 'mjschool' ); ?></span><br>
												<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="add_leave_template" class="btn btn-success mjschool-save-btn" />
											</div>
											<?php
										}
										?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
						<!-- Add leave email template - end. -->
						<!-- Leave approve email template - start. -->
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Leave Approve Email Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_approve_leave_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_leave_approve_subject" type="text" class="form-control validate[required]" name="mjschool_leave_approve_subject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_leave_approve_subject' ) ); ?>">
															<label for="mjschool_leave_approve_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_leave_approve_email_template" name="mjschool_leave_approve_email_template" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_leave_approve_email_template' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="leave_approve_template" class="btn btn-success mjschool-save-btn" />
											</div>
											<?php
										}
										?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
						<!-- Leave approve email template - end. -->
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Fee Payment Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_fees_payment_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_student_activation_title" type="text" class="form-control validate[required]" name="mjschool_fee_payment_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_fee_payment_title' ) ); ?>">
															<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_fee_payment_mailcontent" name="mjschool_fee_payment_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_fee_payment_mailcontent' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'Add User', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_add_user_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_add_user_mail_subject" type="text" class="form-control validate[required]" name="mjschool_add_user_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_add_user_mail_subject' ) ); ?>">
															<label for="mjschool_add_user_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_add_user_mail_content" name="mjschool_add_user_mail_content" class="form-control validate[required] min_height_200  h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_add_user_mail_content' ) ); ?></textarea>
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
												<span><strong>{{login_link}} - </strong><?php esc_html_e( 'Student roll number', 'mjschool' ); ?></span><br>
												<span><strong>{{username}} - </strong><?php esc_html_e( 'Student roll number', 'mjschool' ); ?></span><br>
												<span><strong>{{password}} - </strong><?php esc_html_e( 'Student roll number', 'mjschool' ); ?></span><br>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_adduser_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Student Assign to Teacher mail template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_assign_teacher_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_student_assign_teacher_mail_subject" type="text" class="form-control validate[required]" name="mjschool_student_assign_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_student_assign_teacher_mail_subject' ) ); ?>" />
															<label for="mjschool_student_assign_teacher_mail_subject"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_student_assign_teacher_mail_content" name="mjschool_student_assign_teacher_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_student_assign_teacher_mail_content' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_student_assign_teacher_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Message Received', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_message_received_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_message_received_mailsubject" type="text" class="form-control validate[required]" name="mjschool_message_received_mailsubject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_message_received_mailsubject' ) ); ?>" />
															<label for="mjschool_message_received_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_message_received_mailcontent" name="mjschool_message_received_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_message_received_mailcontent' ) ); ?></textarea>
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
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_messege_recived_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Attendance Absent Notification', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_absent_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_absent_mail_notification_subject" type="text" class="form-control validate[required]" name="mjschool_absent_mail_notification_subject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_absent_mail_notification_subject' ) ); ?>" />
															<label for="mjschool_absent_mail_notification_subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_absent_mail_notification_content" name="mjschool_absent_mail_notification_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_absent_mail_notification_content' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_student_absent_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Student Assigned to Teacher Student mail template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_teacher_assign_student_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschoool_student_assign_to_teacher_subject" type="text" class="form-control validate[required]" name="mjschoool_student_assign_to_teacher_subject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschoool_student_assign_to_teacher_subject' ) ); ?>" />
															<label for="mjschoool_student_assign_to_teacher_subject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_student_assign_teacher_mail_content" name="mjschool_student_assign_teacher_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_student_assign_teacher_mail_content' ) ); ?></textarea>
														<label for="mjschool_student_assign_teacher_mail_content" class="mjschool-textarea-label"><?php esc_html_e( 'Emails Sent to user When Student Assigned to Teacher', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_student_assign_to_teacher_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Payment Received against Invoice', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_payment_receive_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_payment_recived_mailsubject" type="text" class="form-control validate[required]" name="mjschool_payment_recived_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_payment_recived_mailsubject' ) ); ?>" />
															<label for="mjschool_payment_recived_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_payment_recived_mailcontent" name="mjschool_payment_recived_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_payment_recived_mailcontent' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_payment_recived_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Notice', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_notice_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_notice_mailsubject" type="text" class="form-control validate[required]" name="mjschool_notice_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_notice_mailsubject' ) ); ?>" />
															<label for="mjschool_notice_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_notice_mailcontent" name="mjschool_notice_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_notice_mailcontent' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_notice_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapstwelve"> </a>
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Holiday', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_holiday_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_holiday_mailsubject" type="text" class="form-control validate[required]" name="mjschool_holiday_mailsubject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_holiday_mailsubject' ) ); ?>" />
															<label for="mjschool_holiday_mailsubject"><?php esc_html_e( 'Subject', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_holiday_mailcontent" name="mjschool_holiday_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_holiday_mailcontent' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_holiday_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'HomeWork Mail Template For Student', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_homework_student_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_homework_title" type="text" class="form-control validate[required]" name="mjschool_homework_title"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_homework_title' ) ); ?>">
															<label for="mjschool_homework_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_homework_mailcontent" name="mjschool_homework_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_homework_mailcontent' ) ); ?></textarea>
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
												<span><strong>{{submition_date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></span><br>
												<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_homework_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'HomeWork Mail Template For Parent', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_homework_parent_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_parent_homework_mail_subject" type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_parent_homework_mail_subject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_parent_homework_mail_subject' ) ); ?>">
															<label for="mjschool_parent_homework_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_parent_homework_mail_content" name="mjschool_parent_homework_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_parent_homework_mail_content' ) ); ?></textarea>
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
												<span><strong>{{submition_date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></span><br>
												<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_homework_mailtemplate_parent" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Student Exam Hall Receipt', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_exam_hall_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_exam_receipt_subject" type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_exam_receipt_subject"  placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_exam_receipt_subject' ) ); ?>">
															<label for="mjschool_exam_receipt_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_exam_receipt_content" name="mjschool_exam_receipt_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_exam_receipt_content' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_exam_receipt_generate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Hostel Bed Assigned Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_hostel_bed_assign_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input type="text" id="mjschool_student_activation_title" class="form-control validate[required]" name="mjschool_bed_subject" id="mjschool_bed_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_bed_subject' ) ); ?>">
															<label for="mjschool_student_activation_title"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_bed_content" name="mjschool_bed_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_bed_content' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_bed_template" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Virtual ClassRoom Teacher Invite Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_invite_teacher_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_teacher_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_virtual_class_invite_teacher_mail_subject" type="text" class="form-control validate[required]" name="mjschool_virtual_class_invite_teacher_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_virtual_class_invite_teacher_mail_subject' ) ); ?>">
															<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_bed_content" name="mjschool_virtual_class_invite_teacher_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_virtual_class_invite_teacher_mail_content' ) ); ?></textarea>
														<label for="mjschool_bed_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="virtual_class_invite_teacher_form_template" class="btn btn-success mjschool-save-btn" />
											</div>
										<?php } ?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
						<!-- Virtual classroom teacher reminder template. -->
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Virtual ClassRoom Teacher Reminder Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_teacher_reminder_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_teacher_reminder_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_virtual_class_invite_teacher_mail_subject" type="text" class="form-control validate[required]" name="mjschool_virtual_class_teacher_reminder_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_virtual_class_teacher_reminder_mail_subject' ) ); ?>">
															<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_bed_content" name="mjschool_virtual_class_teacher_reminder_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_virtual_class_teacher_reminder_mail_content' ) ); ?></textarea>
														<label for="mjschool_bed_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="virtual_class_teacher_reminder_template" class="btn btn-success mjschool-save-btn" />
											</div>
											<?php
										}
										?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
						<!-- Virtual classroom student reminder template. -->
						<div class="mt-1 accordion-item">
							<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Virtual ClassRoom Student Reminder Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="virtual_class_student_reminder_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_virtual_class_student_reminder_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input id="mjschool_virtual_class_invite_teacher_mail_subject" type="text" class="form-control validate[required]" name="mjschool_virtual_class_student_reminder_mail_subject" id="mjschool_virtual_class_invite_teacher_mail_subject" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_virtual_class_student_reminder_mail_subject' ) ); ?>">
															<label for="mjschool_virtual_class_invite_teacher_mail_subject"><?php esc_html_e( 'Email Subject ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_bed_content" name="mjschool_virtual_class_student_reminder_mail_content" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_virtual_class_student_reminder_mail_content' ) ); ?></textarea>
														<label for="mjschool_bed_content" class="mjschool-textarea-label"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="virtual_class_student_reminder_template" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'Fee Payment Reminder Mail Template', 'mjschool' ); ?>
								</button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_fee_payment_reminder_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input type="text" id="mjschool_fee_payment_reminder_title" class="form-control validate[required]" name="mjschool_fee_payment_reminder_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_fee_payment_reminder_title' ) ); ?>">
															<label for="mjschool_fee_payment_reminder_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_fee_payment_reminder_mailcontent" name="mjschool_fee_payment_reminder_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_fee_payment_reminder_mailcontent' ) ); ?></textarea>
														<label for="mjschool_fee_payment_reminder_mailcontent" class="mjschool-textarea-label"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
													<span><?php esc_html_e( 'You can use following variables in the email template:', 'mjschool' ); ?></span><br>
													<span><strong>{{parent_name}} - </strong><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></span><br>
													<span><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></span><br>
													<span><strong>{{school_name}} - </strong><?php esc_html_e( 'School name', 'mjschool' ); ?></span><br>
													<span><strong>{{total_amount}} - </strong><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></span><br>
													<span><strong>{{due_amount}} - </strong><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></span><br>
													<span><strong>{{class_name}} - </strong><?php esc_html_e( 'Class Name', 'mjschool' ); ?></span><br>
												</div>
											</div>
										</div>
										<?php
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_feepayment_reminder_mailtemplate" class="btn btn-success mjschool-save-btn" />
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
								<button class="accordion-button collapsed " type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>"> <?php esc_html_e( 'Assign Subject Mail Template', 'mjschool' ); ?> </button>
							</h4>
							<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion-flush">
								<div class="m-auto mjschool-panel-body">
									<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
										<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_assign_subject_mail_nonce' ) ); ?>">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12">
														<div class="col-md-12 form-control mjschool-input-height-75px">
															<input type="text" id="mjschool_assign_subject_title" class="form-control validate[required]" name="mjschool_assign_subject_title" id="mjschool_fee_payment_title" placeholder="<?php esc_html_e( 'Enter Email Subject', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_assign_subject_title' ) ); ?>">
															<label for="mjschool_assign_subject_title"><?php esc_html_e( 'Email Subject', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group input">
													<div class="col-md-12 form-control mjschool-texarea-padding-15px">
														<textarea id="mjschool_assign_subject_mailcontent" name="mjschool_assign_subject_mailcontent" class="form-control validate[required] min_height_200 h-200-px mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_assign_subject_mailcontent' ) ); ?></textarea>
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
										if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
											?>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
												<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_assign_subject_mailtemplate" class="btn btn-success mjschool-save-btn" />
											</div>
											<?php
										}
										?>
									</form>
								</div>
							</div>
						</div>
						<?php ++$i; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>