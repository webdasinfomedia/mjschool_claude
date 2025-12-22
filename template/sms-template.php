<?php
/**
 * SMS Template Management File.
 *
 * This file provides the administrative interface for viewing, editing, and saving
 * pre-defined SMS message templates for various system events (e.g., attendance,
 * fees payment, admissions, holidays). It handles form submissions to update WordPress
 * options and displays a list of available merge variables for each template.
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
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$changed = 0;
if ( isset( $_REQUEST['save_attendance_mjschool_template'] ) ) {
	update_option( 'mjschool_attendance_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_attendance_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_add_fees_mjschool_template_for_student'] ) ) {
	update_option( 'mjschool_fees_payment_mjschool_content_for_student', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_fees_payment_mjschool_content_for_student'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_add_fees_mjschool_template_for_parent'] ) ) {
	update_option( 'mjschool_fees_payment_mjschool_content_for_parent', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_fees_payment_mjschool_content_for_parent'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_add_fees_reminder_mjschool_template'] ) ) {
	update_option( 'mjschool_fees_payment_reminder_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_fees_payment_reminder_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_student_approve_mjschool_template'] ) ) {
	update_option( 'mjschool_student_approve_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_student_approve_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_student_admission_approve_mjschool_template'] ) ) {
	update_option( 'mjschool_student_admission_approve_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_student_admission_approve_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_holiday_mjschool_template'] ) ) {
	update_option( 'mjschool_holiday_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_holiday_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_event_mjschool_template'] ) ) {
	update_option( 'mjschool_event_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_event_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_leave_student_mjschool_template'] ) ) {
	update_option( 'mjschool_leave_student_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_leave_student_mjschool_content'])) ) );
	$changed = 1;
}
if ( isset( $_REQUEST['save_leave_parent_mjschool_template'] ) ) {
	update_option( 'mjschool_leave_parent_mjschool_content', mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_REQUEST['mjschool_leave_parent_mjschool_content'])) ) );
	$changed = 1;
}
if ( $changed ) {
	wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=mjschool_template&message=1') );
	die();
}
?>
</script>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px mt-2"><!-- Mjschool-main-list-margin-15px. -->
		<div class="row"><!-- Row. -->
			<?php
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'SMS Template Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span> </button>
					<?php echo esc_html( $message_string ); ?>
				</div>
				<?php
			}
			$i = 1;
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-main-list-page"><!-- Mjschool-main-list-page. -->
					<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
						<div class="mjschool-main-email-template"><!--Mjschool-main-email-template. -->
							<?php ++$i; ?>
							<div id="mjschool-accordion" class="mjschool-accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion-flush" aria-multiselectable="false" role="tablist"><!--START accordion -->
								<div class="mt-1 accordion-item">
									<h4 class="accordion-header" id="flush-heading<?php echo esc_attr( $i ); ?>">
										<button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" aria-controls="flush-heading<?php echo esc_attr( $i ); ?>">
											<?php esc_html_e( 'Student Admission Approve SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_student_admission_approve_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_admission_approve_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_student_admission_approve_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Student Approve SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_student_approve_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_student_approve_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_student_approve_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Exam SMS Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_exam_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_exam_student_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{exam_name}} - </strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></label><br>
																<label><strong>{{date}} - </strong><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></label><br>
																<label><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_exam_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Exam SMS Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_exam_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0 mjschool_70px" ><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_exam_parent_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{student_name}} - </strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
																<label><strong>{{exam_name}} - </strong><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></label><br>
																<label><strong>{{date}} - </strong><?php esc_html_e( 'Exam Date', 'mjschool' ); ?></label><br>
																<label><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_exam_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Homework SMS Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_homework_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_homework_student_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{title}} - </strong><?php esc_html_e( 'Homework Title', 'mjschool' ); ?></label><br>
																<label><strong>{{date}} - </strong><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_homework_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Homework SMS Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_homework_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_homework_parent_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{title}} - </strong><?php esc_html_e( 'Homework Title', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_homework_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Attendance SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_attendance_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_attendance_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{student_name}} - </strong><?php esc_html_e( 'Student name', 'mjschool' ); ?></label><br>
																<label><strong>{{current_date}} - </strong><?php esc_html_e( 'Today Date', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_attendance_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Leave SMS Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_leave_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_student_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{date}} - </strong><?php esc_html_e( 'Leave Date', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_leave_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Leave SMS Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_leave_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_leave_parent_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{student_name}} - </strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
																<label><strong>{{date}} - </strong><?php esc_html_e( 'Leave Date', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_leave_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Fees Payment SMS Template For Student', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_fees_payment_mjschool_content_for_student" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fees_payment_mjschool_content_for_student' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_add_fees_mjschool_template_for_student" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Fees Payment SMS Template For Parent', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_fees_payment_mjschool_content_for_parent" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fees_payment_mjschool_content_for_parent' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{student_name}} - </strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_add_fees_mjschool_template_for_parent" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Fees Payment Reminder SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_fees_payment_reminder_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_fees_payment_reminder_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{student_name}} - </strong><?php esc_html_e( 'Student Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_add_fees_reminder_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Event SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_event_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_event_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{event_title}} - </strong><?php esc_html_e( 'Event Title', 'mjschool' ); ?></label><br>
																<label><strong>{{school_name}} - </strong><?php esc_html_e( 'School Name', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_event_mjschool_template" class="btn btn-success mjschool-save-btn" />
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
											<?php esc_html_e( 'Holiday SMS Template', 'mjschool' ); ?>
										</button>
									</h4>
									<div id="flush-collapse_collapse_<?php echo esc_attr( $i ); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse" aria-labelledby="flush-heading<?php echo esc_attr( $i ); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
										<div class="m-auto mjschool-panel-body mjschool-margin-20px">
											<form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
												<div class="row">
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12 form-control mjschool-texarea-padding-15px">
																<textarea name="mjschool_holiday_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea( mjschool_strip_tags_and_stripslashes( get_option( 'mjschool_holiday_mjschool_content' ) ) ); ?></textarea>
																<label for="first_name" class="mjschool-textarea-label"><?php esc_html_e( 'Message Content', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group input">
															<div class="col-md-12">
																<label><?php esc_html_e( 'You can use following variables in the SMS template:', 'mjschool' ); ?></label><br>
																<label><strong>{{title}} - </strong><?php esc_html_e( 'Holiday Title', 'mjschool' ); ?></label><br>
															</div>
														</div>
													</div>
												</div>
												<?php
												if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
													?>
													<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
														<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_holiday_mjschool_template" class="btn btn-success mjschool-save-btn" />
													</div>
													<?php
												}
												?>
											</form>
										</div>
									</div>
								</div>
								<?php ++$i; ?>
							</div><!--End accordion. -->
						</div><!--Mjschool-main-email-template. -->
					</div><!-- Mjschool-panel-body. -->
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->