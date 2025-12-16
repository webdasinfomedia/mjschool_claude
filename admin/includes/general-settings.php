<?php
/**
 * General Settings - Admin Panel.
 *
 * Handles the configuration and management of general system settings
 * in the MjSchool plugin. This includes dashboard widget preferences,
 * form validation, user role–based access control, and application
 * verification settings.
 *
 * Key Features:
 * - Implements custom jQuery validation for school name and other fields.
 * - Manages tab-based settings (General, Email, Payment, Dashboard, etc.).
 * - Provides role-based access control (Administrator, Teacher, Parent, etc.).
 * - Saves and updates dashboard card visibility preferences for different roles.
 * - Integrates with WordPress options API for persistent configuration storage.
 * - Ensures compliance with WordPress security standards using sanitization
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
$active_tab = isset($_GET['tab']) ? sanitize_text_field( wp_unslash($_GET['tab']) ) : 'general_setting';
$mjschool_role       = mjschool_get_user_role( get_current_user_id() );
$mjschool_role_array = explode( ',', $mjschool_role );
if ( in_array( 'administrator', $mjschool_role_array ) ) {
	$user_access_add    = 1;
	$user_access_edit   = 1;
	$user_access_delete = 1;
	$user_access_view   = 1;
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'general_settings' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
}
if ( isset( $_POST['save_dashboard_setting'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_dashboard_setting_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$dashboard_card_access = array();
	$dashboard_card_access = array(
		'mjschool_payment_status_chart' => isset($_REQUEST['payment_status_chart_enable_student']) ? sanitize_text_field( wp_unslash($_REQUEST['payment_status_chart_enable_student']) ) : 'no',
		'mjschool_user_chart'           => isset($_REQUEST['user_chart_enable_student']) ? sanitize_text_field( wp_unslash($_REQUEST['user_chart_enable_student']) ) : 'no',
		'mjschool_invoice_chart'        => isset($_REQUEST['invoice_enable']) ? sanitize_text_field( wp_unslash($_REQUEST['invoice_enable']) ) : 'no',
	);
	$dashboard_result      = update_option( 'mjschool_dashboard_card_for_student', $dashboard_card_access );
	// -------- Card option update for staffmemeber. ---------//
	$dashboard_result_1              = get_option( 'mjschool_dashboard_card_for_support_staff' );
	$dashboard_card_access_for_staff = array();
	$dashboard_card_access_for_staff = array(
		'mjschool_student_status_chart' => isset($_REQUEST['student_status_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['student_status_staff']) ) : 'no',
		'mjschool_attendance_chart'     => isset($_REQUEST['attendance_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['attendance_staff']) ) : 'no',
		'mjschool_payment_status_chart' => isset($_REQUEST['payment_status_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['payment_status_staff']) ) : 'no',
		'mjschool_payment_report'       => isset($_REQUEST['payment_report_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['payment_report_staff']) ) : 'no',
		'mjschool_invoice_chart'        => isset($_REQUEST['invoice_enable_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['invoice_enable_staff']) ) : 'no',
		'mjschool_user_chart'           => isset($_REQUEST['users_chart_staff']) ? sanitize_text_field( wp_unslash($_REQUEST['users_chart_staff']) ) : 'no',
	);
	$dashboard_result_1              = update_option( 'mjschool_dashboard_card_for_support_staff', $dashboard_card_access_for_staff );
	// -------- Card option update for teacher. ---------//
	$dashboard_result_2            = get_option( 'mjschool_dashboard_card_for_teacher' );
	$dashboard_card_access_teacher = array();
	$dashboard_card_access_teacher = array(
		'mjschool_student_status_chart' => isset($_REQUEST['student_status_enable_teacher']) ? sanitize_text_field( wp_unslash($_REQUEST['student_status_enable_teacher']) ) : 'no',
		'mjschool_attendance_chart'     => isset($_REQUEST['attendance_chart_enable_teacher']) ? sanitize_text_field( wp_unslash($_REQUEST['attendance_chart_enable_teacher']) ) : 'no',
		'mjschool_user_chart'           => isset($_REQUEST['user_chart_enable_teacher']) ? sanitize_text_field( wp_unslash($_REQUEST['user_chart_enable_teacher']) ) : 'no',
	);
	$dashboard_result_2            = update_option( 'mjschool_dashboard_card_for_teacher', $dashboard_card_access_teacher );
	// -------- Card option update for parent. ---------//
	$dashboard_result_3           = get_option( 'mjschool_dashboard_card_for_parent' );
	$dashboard_card_access_parent = array();
	$dashboard_card_access_parent = array(
		'mjschool_user_chart'           => isset($_REQUEST['user_chart_parent']) ? sanitize_text_field( wp_unslash($_REQUEST['user_chart_parent']) ) : 'no',
		'mjschool_invoice_chart'        => isset($_REQUEST['invoice_enable_parent']) ? sanitize_text_field( wp_unslash($_REQUEST['invoice_enable_parent']) ) : 'no',
		'mjschool_payment_status_chart' => isset($_REQUEST['payment_status_parent']) ? sanitize_text_field( wp_unslash($_REQUEST['payment_status_parent']) ) : 'no',
	);
	$dashboard_result_3 = update_option( 'mjschool_dashboard_card_for_parent', $dashboard_card_access_parent );
	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	if ( $school_obj->role === 'supportstaff' ) {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=general-settings&tab=dashboard_card_settings&_wpnonce='.esc_attr( $nonce ).'&message=1' );
		die();
	} else {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=dashboard_card_settings&_wpnonce='.esc_attr( $nonce ).'&message=1' );
		die();
	}
}
if ( isset( $_POST['save_mobile_app_settings'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_app_verification_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$optionval = mjschool_update_option();
	foreach ( $optionval as $key => $val ) {
		if ( isset( $_POST[ $key ] ) ) {
			$result = update_option($key, sanitize_text_field( wp_unslash($_POST[$key]) ));
		}
	}
	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	if ( $school_obj->role === 'supportstaff' ) {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=general-settings&tab=mobile_app_settings&tab1=icon_setting&_wpnonce='.esc_attr( $nonce ).'&message=3' );
		die();
	} else {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=mobile_app_settings&tab1=icon_setting&_wpnonce='.esc_attr( $nonce ).'&message=3' );
		die();
	}
}
if ( isset( $_POST['varify_app_key'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_license_setup_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$verify_result = mjschool_submit_setup_form_mobileapp( wp_unslash($_POST) );
	if ( $verify_result['mjschool_app_verify'] != '0' ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php echo esc_html( $verify_result['message'] ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	} else {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php echo esc_html( $verify_result['message'] ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	}
}
if ( isset( $_POST['save_student_onboard'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_student_onbording_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$optionval = mjschool_update_option();
	foreach ( $optionval as $key => $val ) {
		if ( isset( $_POST[ $key ] ) ) {
			$result = update_option($key, sanitize_text_field( wp_unslash($_POST[$key]) ));
		}
	}
	if ( isset( $_REQUEST['mjschool_combine'] ) ) {
		update_option( 'mjschool_combine', '1' );
	} else {
		update_option( 'mjschool_combine', '0' );
	}
	if ( isset( $_REQUEST['mjschool_admission_fees'] ) ) {
		update_option( 'mjschool_admission_fees', 'yes' );
	} else {
		update_option( 'mjschool_admission_fees', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_registration_fees'] ) ) {
		update_option( 'mjschool_registration_fees', 'yes' );
	} else {
		update_option( 'mjschool_registration_fees', 'no' );
	}
	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=student_onboarding&_wpnonce='.esc_attr( $nonce ).'&message=7' );
	die();
}
if ( isset( $_POST['save_class_room'] ) )
{
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_class_setting_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	if ( isset( $_REQUEST['mjschool_class_room'] ) ) {
		update_option( 'mjschool_class_room', '1' );
	} else {
		update_option( 'mjschool_class_room', '0' );
	}
	if ( isset( $_REQUEST['mjschool_custom_class'] ) ) {
		update_option( 'mjschool_custom_class', sanitize_text_field(wp_unslash($_REQUEST['mjschool_custom_class']) ) );
	}
	if ( isset( $_REQUEST['mjschool_custom_class_display'] ) ) {
		update_option( 'mjschool_custom_class_display', '1' );
	} else {
		update_option( 'mjschool_custom_class_display', '0' );
	}
	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=class_settings&_wpnonce='.esc_attr( $nonce ).'&message=8' );
	die();
}
if ( isset( $_POST['save_setting'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_general_setting_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$optionval = mjschool_update_option();
	foreach ( $optionval as $key => $val ) {
		if ( isset( $_POST[ $key ] ) ) {
			$result = update_option($key, sanitize_text_field( wp_unslash($_POST[$key]) ));
		}
	}
	if ( isset( $_REQUEST['mjschool_datepicker_format'] ) ) {
		update_option( 'mjschool_datepicker_format', sanitize_text_field( wp_unslash($_REQUEST['mjschool_datepicker_format'])) );
	}
	if ( isset( $_REQUEST['mjschool_system_color_code'] ) ) {
		update_option( 'mjschool_system_color_code', sanitize_text_field( wp_unslash($_REQUEST['mjschool_system_color_code'])) );
	}
	// Update General settings option.
	if ( isset( $_REQUEST['mjschool_paymaster_pack'] ) ) {
		update_option( 'mjschool_paymaster_pack', 'yes' );
	} else {
		update_option( 'mjschool_paymaster_pack', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_recurring_invoices'] ) ) {
		update_option( 'mjschool_enable_recurring_invoices', 'yes' );
	} else {
		update_option( 'mjschool_enable_recurring_invoices', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_system_payment_reminder_enable'] ) ) {
		update_option( 'mjschool_system_payment_reminder_enable', 'yes' );
	} else {
		update_option( 'mjschool_system_payment_reminder_enable', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_invoice_option'] ) ) {
		update_option( 'mjschool_invoice_option', '1' );
	} else {
		update_option( 'mjschool_invoice_option', '0' );
	}
	// Update General settings option.
	if ( isset( $_REQUEST['mjschool_mail_notification'] ) ) {
		update_option( 'mjschool_mail_notification', 1 );
	} else {
		update_option( 'mjschool_mail_notification', 0 );
	}
	if ( isset( $_REQUEST['mjschool_parent_send_message'] ) ) {
		update_option( 'mjschool_parent_send_message', 1 );
	} else {
		update_option( 'mjschool_parent_send_message', 0 );
	}
	if ( isset( $_REQUEST['mjschool_student_send_message'] ) ) {
		update_option( 'mjschool_student_send_message', 1 );
	} else {
		update_option( 'mjschool_student_send_message', 0 );
	}
	if ( isset( $_REQUEST['mjschool_student_approval'] ) ) {
		update_option( 'mjschool_student_approval', 1 );
	} else {
		update_option( 'mjschool_student_approval', 0 );
	}
	if ( isset( $_REQUEST['mjschool_past_pay'] ) ) {
		update_option( 'mjschool_past_pay', 'yes' );
	} else {
		update_option( 'mjschool_past_pay', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_sandbox'] ) ) {
		update_option( 'mjschool_enable_sandbox', 'yes' );
	} else {
		update_option( 'mjschool_enable_sandbox', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_virtual_classroom'] ) ) {
		update_option( 'mjschool_enable_virtual_classroom', 'yes' );
	} else {
		update_option( 'mjschool_enable_virtual_classroom', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_video_popup_show'] ) ) {
		update_option( 'mjschool_enable_video_popup_show', 'yes' );
	} else {
		update_option( 'mjschool_enable_video_popup_show', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_virtual_classroom_reminder'] ) ) {
		update_option( 'mjschool_enable_virtual_classroom_reminder', 'yes' );
	} else {
		update_option( 'mjschool_enable_virtual_classroom_reminder', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_enable_mjschool_virtual_classroom_reminder'] ) ) {
		update_option( 'mjschool_enable_mjschool_virtual_classroom_reminder', 'yes' );
	} else {
		update_option( 'mjschool_enable_mjschool_virtual_classroom_reminder', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_teacher_manage_allsubjects_marks'] ) ) {
		update_option( 'mjschool_teacher_manage_allsubjects_marks', 'yes' );
	} else {
		update_option( 'mjschool_teacher_manage_allsubjects_marks', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_heder_enable'] ) ) {
		update_option( 'mjschool_heder_enable', 'yes' );
	} else {
		update_option( 'mjschool_heder_enable', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_return_option'] ) ) {
		update_option( 'mjschool_return_option', 'yes' );
	} else {
		update_option( 'mjschool_return_option', 'no' );
	}
	if ( isset( $_REQUEST['mjschool_return_period'] ) ) {
		update_option( 'mjschool_return_period', esc_attr( sanitize_text_field( wp_unslash($_REQUEST['mjschool_return_period'])) ) );
	}
	// Principal Signature.
	if ( isset( $_REQUEST['mjschool_principal_signature'] ) ) {
		update_option( 'mjschool_principal_signature', sanitize_text_field( wp_unslash($_REQUEST['mjschool_principal_signature'])) );
	}
	// ------------ Wizard setup option. -----------//
	$wizard = mjschool_setup_wizard_steps_updates( 'step1_general_setting' );
	// -------- Card option update for student. ---------//
	$dashboard_result = get_option( 'mjschool_dashboard_card_for_student' );

	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	if ( $school_obj->role === 'supportstaff' ) {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=general-settings&_wpnonce='.esc_attr( $nonce ).'&message=1' );
		die();
	} else {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&_wpnonce='.esc_attr( $nonce ).'&message=1' );
		die();
	}
}
if ( isset( $_REQUEST['save_document_setting'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_document_setting_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$document_type     = implode( ', ', sanitize_text_field(wp_unslash($_REQUEST['document_type'])) );
	$profile_extension = implode( ', ', sanitize_text_field(wp_unslash($_REQUEST['profile_extension'])) );
	$document_size     = sanitize_text_field(wp_unslash($_REQUEST['document_size']));
	$profile_size      = sanitize_text_field(wp_unslash($_REQUEST['profile_size']));
	update_option( 'mjschool_upload_document_type', $document_type );
	update_option( 'mjschool_upload_profile_extention', $profile_extension );
	update_option( 'mjschool_upload_document_size', $document_size );
	update_option( 'mjschool_upload_profile_size', $profile_size );

	$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
	if ( $school_obj->role === 'supportstaff' ) {
		wp_redirect( home_url() . '?dashboard=mjschool_user&page=general-settings&tab=document_settings&_wpnonce='.esc_attr( $nonce ).'&message=2' );
		die();
	} else {
		wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=document_settings&_wpnonce='.esc_attr( $nonce ).'&message=2' );
		die();
	}
	?>
	<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
		<p><?php esc_html_e( 'Document setting updated successfully.', 'mjschool' ); ?></p>
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
	</div>
	<?php
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<?php
if ( $school_obj->role === 'administrator' ) {
	if ( get_option( 'mjschool_enable_video_popup_show' ) === 'yes' ) {
		?>
		<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/vCxdYKKX9es?si=DUUdlwfucUoScL-N' ); ?>" title="<?php esc_attr_e( 'General Settings', 'mjschool' ); ?>">

			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-youtube-icon.png' ); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">

		</a>
		<?php
	}
}
?>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner.-->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<div class="row"><!-- Row. -->
			<?php
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Settings Updated Successfully.', 'mjschool' );
					break;
				case '2':
					$message_string = esc_html__( 'Document setting updated successfully.', 'mjschool' );
					break;
				case '3':
					$message_string = esc_html__( 'Mobile App Setting Updated Successfully.', 'mjschool' );
					break;
				case '4':
					$message_string = esc_html__( 'Grpup Exam Result Setting Inserted Successfully.', 'mjschool' );
					break;
				case '5':
					$message_string = esc_html__( 'Grpup Exam Result Setting Updated Successfully.', 'mjschool' );
					break;
				case '6':
					$message_string = esc_html__( 'Grpup Exam Result Setting Deleted Successfully.', 'mjschool' );
					break;
				case '7':
					$message_string = esc_html__( 'Student Onboarding Setting Updated Successfully.', 'mjschool' );
					break;
				case '8':
					$message_string = esc_html__( 'Class Setting Updated Successfully.', 'mjschool' );
					break;
			}
			if ( $message ) {
				if ( $school_obj->role === 'supportstaff' ) {
					?>
					<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success mt-1 alert-dismissible " role="alert">

						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
						<?php
						echo esc_html( $message_string ); ?>
					</div>
					<?php
				} else {
					?>
					<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
						<p><?php echo esc_html( $message_string ); ?></p>
						<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
					</div>
					<?php
				}
			}
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-panel-body">
					<?php $nonce = wp_create_nonce( 'mjschool_general_setting_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per mb-4" role="tablist">
						<?php
						if ( $school_obj->role === 'supportstaff' ) {
							?>
							<li class="<?php if ( $active_tab === 'general_setting' ) { ?>active<?php } ?>">
								<a href="?dashboard=mjschool_user&page=general-settings&tab=general_setting&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'general_setting' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'General Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'document_settings' ) { ?>active<?php } ?>">
								<a href="?dashboard=mjschool_user&page=general-settings&tab=document_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'document_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Document Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'dashboard_card_settings' ) { ?>active<?php } ?>">
								<a href="?dashboard=mjschool_user&page=general-settings&tab=dashboard_card_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'dashboard_card_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Dashboard Card Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'mobile_app_settings' ) { ?>active<?php } ?>">
								<a href="?dashboard=mjschool_user&page=general-settings&tab=mobile_app_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'mobile_app_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Mobile APP Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'exam_merge_settings' ) { ?>active<?php } ?>">
								<a href="?dashboard=mjschool_user&page=general-settings&tab=exam_merge_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_merge_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Group Exam Result Settings', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						} else {
							?>
							<li class="<?php if ( $active_tab === 'general_setting' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=general_setting&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'general_setting' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'General Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'document_settings' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=document_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'document_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Document Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'dashboard_card_settings' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=dashboard_card_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'dashboard_card_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Dashboard Card Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'mobile_app_settings' ) { ?>active<?php } ?>">
								<a href="?&page=mjschool_general_settings&tab=mobile_app_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'mobile_app_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Mobile APP Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'exam_merge_settings' ) { ?>active<?php } ?>">
								<a href="?&page=mjschool_general_settings&tab=exam_merge_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_merge_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Group Exam Result Settings', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'student_onboarding' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=student_onboarding&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_onboarding' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'Student Onboarding', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab === 'class_settings' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=class_settings&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="padding_left_0 tab <?php echo esc_attr( $active_tab  ) === 'class_settings' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_attr__( 'class settings', 'mjschool' ); ?>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					<?php
					if ( $active_tab === 'exam_merge_settings' ) {
						// Check nonce for exam mearge settings tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'exam_merge_settings'; ?>
						<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
							<div class="mjschool-guardian-div">
								<?php
								$exam_obj = new Mjschool_exam();
								if ( isset( $_POST['save_merge_settings'] ) ) {
									$nonce = $_POST['_wpnonce'];
									if ( wp_verify_nonce( $nonce, 'save_merge_settings' ) ) {
										$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
										if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit_merge' ) {
											if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'edit_action' ) ) {
												$result = $exam_obj->mjschool_save_merge_exam_setting( wp_unslash($_POST) );
												if ( $result ) {
													wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=exam_merge_settings&_wpnonce='.esc_attr( $nonce ).'&message=5' );
													die();
												}
											} else {
												wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
											}
										} else {
											$result = $exam_obj->mjschool_save_merge_exam_setting( wp_unslash($_POST) );
											if ( $result ) {
												wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=exam_merge_settings&_wpnonce='.esc_attr( $nonce ).'&message=4' );
												die();
											}
										}
									} else {
										wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
									}
								}
								if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete_merge' ) ) {
									if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'delete_action' ) ) {
										$result = $exam_obj->mjschool_delete_exam_setting( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['merge_id'])) ) );
										if ( $result ) {
											$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
											wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=exam_merge_settings&_wpnonce='.esc_attr( $nonce ).'&message=6' );
											die();
										}
									} else {
										wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
									}
								}
								if ( isset( $_REQUEST['delete_selected_exam_setting'] ) ) {
									if ( ! empty( $_REQUEST['merge_id'] ) ) {
										mjschool_append_audit_log( '' . esc_html__( 'Group Exam Merge Setting Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_text_field( wp_unslash($_REQUEST['page']) ) );
										foreach ( $_REQUEST['merge_id'] as $id ) {
											$result = $exam_obj->mjschool_delete_exam_setting( $id );
										}
										$nonce = wp_create_nonce( 'mjschool_general_setting_tab' );
										wp_redirect( admin_url() . 'admin.php?page=mjschool_general_settings&tab=exam_merge_settings&_wpnonce='.esc_attr( $nonce ).'&message=6' );
										die();
									}
								}
								$edit = 0;
								if ( isset( $_REQUEST['action'] ) && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit_merge' ) ) {
									$edit     = 1;
									$merge_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['merge_id'])) ) );
									$result   = $exam_obj->mjschool_get_single_merge_exam_setting( $merge_id );
								}
								?>
								<form name="class_Section_form" action="" method="post" class="mjschool-form-horizontal" id="class_Section_form">
									<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
									<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
									<input type="hidden" name="merge_id" value="<?php echo esc_attr( $merge_id ); ?>">
									<div class="header">
										<h3 class="mjschool-first-header"><?php esc_html_e( 'Group Exam Result Settings', 'mjschool' ); ?></h3>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-md-4">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="subject_code" class="form-control validate[required,custom[address_description_validation]] text-input" type="text" maxlength="100" value="<?php if ( $edit ) { echo esc_attr( $result->merge_name ); } ?>" name="merge_name">
														<label for="subject_code"><?php esc_html_e( 'Group Exam Name', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-4 input">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
												<?php
												$class_id = 0;
												if ( $edit ) {
													$class_id = $result->class_id;
												}
												?>
												<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required] text-input">
													<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
													<?php foreach ( mjschool_get_all_class() as $classdata ) { ?>
														<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
													<?php } ?>
												</select>
											</div>
											<div class="col-md-4 input">
												<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Section Name', 'mjschool' ); ?></label>
												<?php
												if ( $edit ) {
													$sectionval = $result->section_id;
												} elseif ( isset( $_POST['class_section'] ) ) {
													$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
												} else {
													$sectionval = '';
												}
												?>
												<select name="class_section" class=" mjschool-line-height-30px form-control mjschool-width-100px mjschool-section-id-exam" id="class_section">
													<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
													<?php
													if ( $edit ) {
														foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
															?>
															<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
															<?php
														}
													}
													?>
												</select>
											</div>
											<?php
											if ( $edit && ! empty( $result->merge_config ) ) {
												?>
												<div id="mjschool-merge-settings-div">
													<?php
													$contributions_data = json_decode( $result->merge_config );
													foreach ( $contributions_data as $key => $value ) {
														?>
														<div class="form-body mjschool-user-form">
															<div class="row">
																<div class="col-md-4 input">
																	<span class="ml-1 mjschool-custom-top-label top" ><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
																	<select  name="exam_id[]" class="mjschool-line-height-30px form-control exam_list validate[required] text-input">
																		<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
																		<?php
																		if ( isset( $value->exam_id ) ) {
																			$exam_data = mjschool_get_all_exam_by_class_id_all( $class_id );
																			if ( ! empty( $exam_data ) ) {
																				foreach ( $exam_data as $retrieved_data ) {
																					?>
																					<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $value->exam_id, $retrieved_data->exam_id ); ?>><?php echo esc_attr( $retrieved_data->exam_name ); ?></option>
																					<?php
																				}
																			}
																		} else {
																			?>
																			<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
																			<?php
																		}
																		?>
																	</select>
																</div>
																<div class="col-md-4 col-10">
																	<div class="form-group input mjschool-error-msg-left-margin">
																		<div class="col-md-12 form-control">
																			<input id="weightage" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="<?php echo esc_attr( $value->weightage ); ?>" name="weightage[]">
																			<label for="weightage"><?php esc_html_e( 'Weightage of the exam(%)', 'mjschool' ); ?></label>
																		</div>
																	</div>
																</div>
																<?php
																if ( $key === 0 ) {
																	?>
																	<div class="col-md-1 col-2 col-sm-3 col-xs-12">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_merge_result()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
																	</div>
																	<?php
																} else {
																	?>
																	<div class="col-md-1 col-2 col-sm-3 col-xs-12">
																		<input type="image" onclick="mjschool_delete_parent_elementExamMergeSettings(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
																	</div>
																	<?php
																}
																?>
															</div>
														</div>
														<?php
													}
													?>
												</div>
												<?php
											} else {
												?>
												<div id="mjschool-merge-settings-div">
													<div class="form-body mjschool-user-form">
														<div class="row">
															<div class="col-md-4 input">
																<span class="ml-1 mjschool-custom-top-label top" ><?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
																<select name="exam_id[]" class="mjschool-line-height-30px form-control exam_list validate[required] text-input">
																	<option value=""><?php esc_html_e( 'Select Exam', 'mjschool' ); ?></option>
																</select>
															</div>
															<div class="col-md-4 col-10">
																<div class="form-group input mjschool-error-msg-left-margin">
																	<div class="col-md-12 form-control">
																		<input id="weightage1" class="form-control mjschool-onlyletter-number-space-validation text-input" type="number" value="" name="weightage[]">
																		<label for="weightage1" class="ms-2"><?php esc_html_e( 'Weightage of the exam(%)', 'mjschool' ); ?></label>
																	</div>
																</div>
															</div>
															<div class="col-md-1 col-2 col-sm-3 col-xs-12">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_merge_result()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
															</div>
														</div>
													</div>
												</div>
												<?php
											}
											?>
											<?php wp_nonce_field( 'save_merge_settings' ); ?>
											<div class="col-sm-3 col-md-3 col-lg-3 col-xs-12">
												<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save', 'mjschool' ); } else { esc_html_e( 'Save', 'mjschool' ); } ?>" name="save_merge_settings" class="mjschool-save-btn check_total_per " />
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
						<div class="header mt-4">
							<h3 class="mjschool-first-header"><?php esc_html_e( 'Group Exam Result List', 'mjschool' ); ?></h3>
						</div>
						<?php
						$all_merge_exam_setting_list = $exam_obj->mjschool_get_all_merge_exam_setting();
						if ( ! empty( $all_merge_exam_setting_list ) ) {
							?>
							<div class="mjschool-panel-body">
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="exam_merge_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
													<th><?php esc_html_e( 'Group Exam Result Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Group Exam Details', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Created By', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
													<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												foreach ( $all_merge_exam_setting_list as $retrieved_data ) {
													?>
													<tr>
														<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="merge_id[]" value="<?php echo esc_attr( $retrieved_data->id ); ?>"></td>
														<td><?php echo esc_html( $retrieved_data->merge_name ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Merge Name', 'mjschool' ); ?>"></i></td>
														<td class="name">
															<?php
															$classname = mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id );
															if ( ! empty( $classname ) ) {
																echo esc_html( $classname );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
														</td>
														<td><?php echo esc_html( mjschool_print_weightage_data( $retrieved_data->merge_config ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php echo esc_html( mjschool_print_weightage_data( $retrieved_data->merge_config ) ); ?>"></i></td>
														<td><?php echo esc_html( mjschool_get_display_name( $retrieved_data->created_by ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Created By', 'mjschool' ); ?>"></i></td>
														<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_at ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Created Date', 'mjschool' ); ?>"></i></td>
														<td><?php echo esc_html( $retrieved_data->status ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
														<td class="action">
															<div class="mjschool-user-dropdown">
																<ul  class="mjschool_ul_style">
																	<li >
																		<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																		</a>
																		<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																			<?php
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																					<a href="?&page=mjschool_general_settings&tab=exam_merge_settings&merge_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&action=edit_merge&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="?&page=mjschool_general_settings&tab=exam_merge_settings&action=delete_merge&merge_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			?>
																		</ul>
																	</li>
																</ul>
															</div>
														</td>
													</tr>
													<?php
													++$i;
												}
												?>
											</tbody>
										</table>
										<div class="mjschool-print-button pull-left">
											<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
												<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												?>
												<button id="mjschool-delete-selected-room" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_exam_setting" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
												<?php
											}
											?>
										</div>
									</form>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php
						}
					}
					if ( $active_tab === 'student_onboarding' ) {
						// Check nonce for student onboarding settings tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'student_onboarding';
						?>
						<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="setting_form">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_onbording_nonce' ) ); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Student onboard Settings', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_combine"><?php esc_html_e( 'Merge Student Admission Form with Registration form', 'mjschool' ); ?></label>
														<input id="mjschool_combine" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_combine" value="1" <?php echo checked( get_option( 'mjschool_combine' ), '1' ); ?> />
														<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
										<div class="form-group">
											<div class="col-md-12 form-control mjschool-input-height-48px">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label mjschool-margin-left-0" for="mjschool_admission_fees"><?php esc_html_e( 'Admission Fees', 'mjschool' ); ?></label>
														<div class="checkbox mjschool-checkbox-label-padding-8px">
															<label class="control-label form-label">
																<input id="mjschool_admission_fees" type="checkbox" class="mjschool_admission_fees" name="mjschool_admission_fees" value="1" <?php echo checked( get_option( 'mjschool_admission_fees' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Yes', 'mjschool' ); ?></span>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_admission_amount">
										<div class="form-group input">
											<?php
											$mjschool_obj_fees        = new Mjschool_Fees();
											$fees_data = $mjschool_obj_fees->mjschool_get_all_fees();
											?>
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool_admission_amount"><?php esc_html_e( 'Admission Fees Amount', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select id="mjschool_admission_amount" class="form-control validate[required] text-input mjschool-max-width-100px" name="mjschool_admission_amount">
												<option value=""><?php esc_html_e( 'Select Fees', 'mjschool' ); ?></option>
												<?php
												$options = get_option( 'mjschool_admission_amount' );
												foreach ( $fees_data as $fees ) {
													?>
													<option value="<?php echo esc_attr( $fees->fees_id ); ?>" <?php if ( $options === $fees->fees_id ) { echo 'selected="selected"';} ?> name='mjschool_admission_amount'>
														<?php echo esc_attr( get_the_title( $fees->fees_title_id ) ) . '( ' . esc_html( mjschool_get_currency_symbol() ) . ' ' . esc_html( $fees->fees_amount ) . ' )'; ?>
													</option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
										<div class="form-group">
											<div class="col-md-12 form-control mjschool-input-height-48px">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label mjschool-margin-left-0" for="mjschool_registration_fees"><?php esc_html_e( 'Registration Fees', 'mjschool' ); ?></label>
														<div class="checkbox mjschool-checkbox-label-padding-8px">
															<label class="control-label form-label">
																<input id="mjschool_registration_fees" type="checkbox" class="mjschool_registration_fees" name="mjschool_registration_fees" value="1" <?php echo checked( get_option( 'mjschool_registration_fees' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Yes', 'mjschool' ); ?></span>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_registration_amount">
										<div class="form-group input">
											<?php
											$mjschool_obj_fees        = new Mjschool_Fees();
											$fees_data = $mjschool_obj_fees->mjschool_get_all_fees();
											?>
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool_registration_amount"><?php esc_html_e( 'Registration Fees Amount', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="mjschool_registration_amount" id="mjschool_registration_amount" class='form-control validate[required] text-input mjschool-max-width-100px'>
												<option value=""><?php esc_html_e( 'Select Fees', 'mjschool' ); ?></option>
												<?php
												$options = get_option( 'mjschool_registration_amount' );
												foreach ( $fees_data as $fees ) {
													?>
													<option value="<?php echo esc_attr( $fees->fees_id ); ?>" <?php if ( $options === $fees->fees_id ) { echo 'selected="selected"';} ?> name='mjschool_registration_amount'>
														<?php echo esc_html( get_the_title( $fees->fees_title_id ) ) . '( ' . esc_html( mjschool_get_currency_symbol() ) . ' ' . esc_html( $fees->fees_amount ) . ' )'; ?>
													</option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6">
										<input class="form-control text-input" type="hidden" value="<?php echo esc_attr( get_option( 'mjschool_general_setting_option_update' ) ); ?>" name="mjschool_general_setting_option_update">
										<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_student_onboard" class="btn btn-success mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					if ($active_tab === 'class_settings' ){
						// Check nonce for class settings tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						$active_tab1 = isset($_GET['tab1'])?sanitize_text_field(wp_unslash($_GET['tab1'])):'class_settings';
						?>
						<form name="student_form" action="" method="post" class="mjschool-form-horizontal" id="setting_form">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_class_setting_nonce' ) ); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Settings', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 rtl_mjschool-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div class="">
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_class_room"><?php esc_attr_e( 'Enable Class Room', 'mjschool' ); ?></label>
														<input id="mjschool_class_room" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_class_room" value="1" <?php echo checked( get_option( 'mjschool_class_room' ), '1' ); ?> />
														<span><?php esc_attr_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-md-6 input">
										<label class="mjschool-custom-top-label top" for="mjschool_custom_class"><?php esc_attr_e( 'Type Of Organization', 'mjschool' ); ?></label>
										<select name="mjschool_custom_class" id="mjschool_custom_class" class="form-control">
											<option value="school" <?php selected(get_option( 'mjschool_custom_class' ), 'school' ); ?>><?php esc_html_e( 'School', 'mjschool' ); ?></option>
											<option value="university" <?php selected(get_option( 'mjschool_custom_class' ), 'university' ); ?>><?php esc_html_e( 'University', 'mjschool' ); ?></option>
											<option value="tuition" <?php selected(get_option( 'mjschool_custom_class' ), 'tuition' ); ?>><?php esc_html_e( 'Private Student Academy', 'mjschool' ); ?></option>
										</select>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 rtl_mjschool-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div class="">
														<label class="mjschool-custom-top-label" for="mjschool_custom_class_display"><?php esc_attr_e( 'Enable Custom Class Student card', 'mjschool' ); ?></label>
														<input id="mjschool_custom_class_display" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_custom_class_display" value="1" <?php echo checked( get_option( 'mjschool_custom_class_display' ), '1' ); ?> />
														<span><?php esc_attr_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6">
										<input class="form-control text-input" type="hidden" value="" name="general_setting_option_update">
										<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_class_room" class="btn btn-success mjschool-save-btn" />
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					if ( $active_tab === 'mobile_app_settings' ) {
						$active_tab1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'license_verification';
						?>
						<?php $nonce = wp_create_nonce( 'mjschool_general_setting_tab' ); ?>
						<ul class="nav nav-tabs mjschool-panel-tabs mjschool-margin-left-1per mjschool-flex-nowrap mjschool_margin_top_15px"  role="tablist"><!-- NAV TAB WRAPPER MENU START-->
							<li class="<?php if ( $active_tab1 === 'license_verification' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=mobile_app_settings&tab1=license_verification&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'license_verification' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_html__( 'License Verification', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab1 === 'icon_setting' ) { ?>active<?php } ?>">
								<a href="?page=mjschool_general_settings&tab=mobile_app_settings&tab1=icon_setting&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'icon_setting' ? 'nav-tab-active' : ''; ?>">
									<?php echo esc_html__( 'Icon Settings', 'mjschool' ); ?>
								</a>
							</li>
						</ul>
						<div class="mjschool-main-list"><!--Main wrapper div start.-->
							<div class="row"><!--Row div start.-->
								<div class="col-md-12 mjschool-custom-padding-0"><!-- Col 12 div start.-->
									<div><!--Panel white div start.-->
										<div class="mjschool-panel-body mjschool-margin-left-10px"><!--Panel body div start.-->
											<?php
											if ( $active_tab1 === 'license_verification' ) {
												// Check nonce for license verification settings tab.
												if ( isset( $_GET['tab'] ) ) {
													if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
														wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
													}
												}
												?>
												<div class="header">
													<h3 class="mjschool-first-header mb-0"><?php esc_html_e( 'License Verification', 'mjschool' ); ?></h3>
												</div>
												<?php
												$domain_name        = get_option( 'mjschool_app_domain_name' );
												$licence_key        = get_option( 'mjschool_app_licence_key' );
												$email              = get_option( 'mjschool_app_setup_email' );
												$check_varification = mjschool_check_product_key( $domain_name, $licence_key, $email );
												if ( ! empty( $licence_key ) && ! empty( $email ) && $check_varification === '0' ) {
													?>
													<div>
														<label class="mjschool_cursor_default_font_17px"><?php esc_html_e( 'Mobile App License Verification Completed successfully', 'mjschool' ); ?></label>
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-checked.gif' ); ?>"  class="calender_logo_image mjschool-textarea-height-60px">
													</div>
													<?php
												} else {
													?>
													<form name="app_verification_form" action="" method="post" class="mjschool-form-horizontal" id="app_verification_form"><!--Verification form start.-->
														<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_license_setup_nonce' ) ); ?>">
														<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start..-->
															<input id="server_name" class="form-control validate[required]" type="hidden" value="<?php echo esc_url( home_url() ); ?>" name="mjschool_app_domain_name" readonly>
															<div class="row"><!--Row div start.-->
																<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
																	<div class="form-group input">
																		<div class="col-md-12 form-control">
																			<input id="app_licence_key" class="form-control validate[required]" type="text" value="" name="mjschool_app_licence_key">
																			<label  for="app_licence_key"><?php esc_html_e( 'App License key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																		</div>
																	</div>
																</div>
																<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
																	<div class="form-group input">
																		<div class="col-md-12 form-control">
																			<input id="enter_app_email" class="form-control validate[required,custom[email]]" type="text" value="" name="mjschool_app_setup_email">
																			<label  for="enter_app_email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
															<div class="row"><!--Row div start.-->
																<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
																	<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="varify_app_key" id="varify_app_key" class="btn mjschool-save-btn" />
																</div>
															</div>
														</div>
													</form> <!--Verification form end. -->
													<?php
												}
											}
											if ( $active_tab1 === 'icon_setting' ) {
												// Check nonce for mobile app icon settings tab.
												if ( isset( $_GET['tab'] ) ) {
													if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
														wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
													}
												}
												?>
												<form name="app_verification_form" action="" method="post" class="mjschool-form-horizontal" id="app_verification_form"><!--VERIFICATION FORM START-->
													<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_app_verification_nonce' ) ); ?>">
													<div class="header">
														<h3 class="mjschool-first-header"><?php esc_html_e( 'Icon Settings', 'mjschool' ); ?></h3>
													</div>
													<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
														<div class="row"><!--Row div start.-->
															<div class="col-md-6">
																<div class="form-group input">
																	<div class="col-md-12 form-control mjschool-upload-profile-image-patient">
																		<label class="mjschool-label-margin-left-7px mjschool-custom-control-label mjschool-custom-top-label ml-2" for="mjschool_cover_image"><?php esc_html_e( 'App Logo', 'mjschool' ); ?></label>
																		<div class="col-sm-12 mjschool-display-flex">
																			<input type="text" id="smgt_app_logo_image_url" name="mjschool_app_logo" class="mjschool-image-path-dots form-control" readonly value="<?php echo esc_attr( get_option( 'mjschool_app_logo' ) ); ?>" />
																			<input id="app_upload_image_button" type="button" class="button upload_app_logo_button mjschool-upload-image-btn" value="<?php esc_html_e( 'Upload Cover Image', 'mjschool' ); ?>" />
																		</div>
																	</div>
																	<div class="clearfix"></div>
																	<div id="upload_mjschool_app_logo_preview" class="mjschool-min-height-100px mt-3 mjschool-margin-top-5">
																		<img class="mjschool-other-data-logo mjschool-other-data-logo-with-back" src="<?php echo esc_attr( get_option( 'mjschool_app_logo' ) ); ?>" />
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
														<div class="row"><!--Row div start.-->
															<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
																<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_mobile_app_settings" id="save_mobile_app_settings" class="btn mjschool-save-btn" />
															</div>
														</div>
													</div>
												</form> <!--Verification form end.-->
												<?php
											}
											?>
										</div><!--Panel body div end.-->
									</div><!--Panel white div end.-->
								</div><!-- Col 12 div end.-->
							</div><!--Row div end.-->
						</div><!--Main wrapper div end.-->
						<?php
					}
					if ( $active_tab === 'general_setting' ) {
						// Check nonce for general setting tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="setting_form">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_general_setting_nonce' ) ); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'General Settings', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_name" class="form-control validate[required,custom[custom_school_name_validation]]" type="text" maxlength="100" value="<?php echo esc_attr( get_option( 'mjschool_name' ) ); ?>" name="mjschool_name">
												<label  for="mjschool_name"><?php esc_html_e( 'School Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_staring_year" class="form-control validate[minSize[4],maxSize[4],min[0]]" min="1" step="1" type="number" value="<?php echo esc_attr( get_option( 'mjschool_staring_year' ) ); ?>" name="mjschool_staring_year">
												<label  for="mjschool_staring_year"><?php esc_html_e( 'Starting Year', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_address" class="form-control validate[required,custom[address_description_validation]]" maxlength="150" type="text" value="<?php echo esc_attr( get_option( 'mjschool_address' ) ); ?>" name="mjschool_address">
												<label  for="mjschool_address"><?php esc_html_e( 'School Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_contact_number" class="form-control  validate[required,custom[phone_number],minSize[6],maxSize[15]]" type="text" value="<?php echo esc_attr( get_option( 'mjschool_contact_number' ) ); ?>" name="mjschool_contact_number">
												<label class="mjschool-label-margin-left-7px" for="mjschool_contact_number"><?php esc_html_e( 'Official Phone Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" ><?php esc_html_e( 'Country', 'mjschool' ); ?></label>
										<?php
										$url = MJSCHOOL_PLUGIN_URL . "/assets/xml/mjschool-country-list.xml";
										$xml = simplexml_load_file( $url ) or wp_die( 'Error: Cannot create object' );
										?>
										<select name="mjschool_contry" class="form-control validate[required] mjschool-max-width-100px" id="mjschool_contry">
											<option value=""><?php esc_html_e( 'Select Country', 'mjschool' ); ?></option>
											<?php
											foreach ( $xml as $country ) {
												?>
												<option value="<?php echo esc_attr( $country->name ); ?>" <?php selected( get_option( 'mjschool_contry' ), $country->name ); ?>><?php echo esc_attr( $country->name ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_city" class="form-control validate[required,custom[city_state_country_validation]]" type="text" value="<?php echo esc_attr( get_option( 'mjschool_city' ) ); ?>" name="mjschool_city">
												<label class="mjschool-label-margin-left-7px" for="mjschool_city"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
												<label class="mjschool-custom-control-label label_margin_left_15px mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-label-right-position" for="mjschool_email"><?php esc_html_e( 'System Logo', 'mjschool' ); ?> (<?php esc_html_e( 'Size Must Be 150 x 150 px', 'mjschool' ); ?>)<span class="mjschool-require-field">*</span></label>
												<div class="col-sm-12 mjschool-display-flex">
													<input type="text" id="mjschool_system_logo_url" name="mjschool_system_logo" class="mjschool-image-path-dots form-control validate[required]" value="<?php echo esc_attr( get_option( 'mjschool_system_logo' ) ); ?>" readonly />
													<input id="upload_system_logo_button" type="button" class="button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
												</div>
											</div>
											<div class="clearfix"></div>
											<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 p-0 mjschool-margin-top-15px">
												<div id="upload_system_logo_preview" class="mjschool-general-setting-image-background">
													<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_system_logo' ) ); ?>" />
												</div>
											</div>
										</div>
										<p><?php esc_html_e( 'Note: logo Size must be 200 X 54 PX And Color Should Be White.', 'mjschool' ); ?></p>
									</div>
									<div class="col-md-6">
										<div class="form-group input">
											<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
												<span class="mjschool-label-margin-left-7px mjschool-custom-control-label mjschool-label-position-rtl mjschool-custom-top-label ml-2" for="smgt_cover_image"><?php esc_html_e( 'Other Logo(Invoice, Mail)', 'mjschool' ); ?></span>
												<div class="col-sm-12 mjschool-display-flex">
													<input type="text" id="mjschool_background_image" name="mjschool_logo" class="mjschool-image-path-dots form-control" value="<?php echo esc_attr( get_option( 'mjschool_logo' ) ); ?>" readonly />
													<input id="upload_image_button" type="button" class="button upload_user_cover_button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_html_e( 'Upload Cover Image', 'mjschool' ); ?>" />
												</div>
											</div>
											<div class="clearfix"></div>
											<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 p-0 mt-3">
												<div id="upload_school_cover_preview min-h-100-px mt-5-px">
													<img class="mjschool-other-data-logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" />
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_email" class="form-control validate[required,custom[email]] text-input" maxlength="100" type="text" value="<?php echo esc_attr( get_option( 'mjschool_email' ) ); ?>" name="mjschool_email">
												<label  for="mjschool_email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool_datepicker_format"><?php esc_html_e( 'Date Format', 'mjschool' ); ?> </label>
										<?php
										$date_format_array = mjschool_datepicker_date_format();
										if ( get_option( 'mjschool_datepicker_format' ) ) {
											$selected_format = get_option( 'mjschool_datepicker_format' );
										} else {
											$selected_format = 'Y-m-d';
										}
										?>
										<select id="mjschool_datepicker_format" class="form-control mjschool-max-width-100px" name="mjschool_datepicker_format">
											<?php
											foreach ( $date_format_array as $key => $value ) {
												echo '<option value="' . esc_attr( $value ) . '" ' . selected( $selected_format, $value ) . '>' . esc_attr( $value ) . '</option>';
											}
											?>
										</select>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
										<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 p-0">
											<div class="form-group input">
												<div class="col-md-12 form-control mjschool-color-picker-div-height">
													<label class="ml-1 mjschool-custom-top-label top mjschool-label-position-rtl" for="mjschool_datepicker_format"><?php esc_html_e( 'System Color', 'mjschool' ); ?></label>
													<input id="mjschool_notification_fcm_key" class="form-control text-input mjschool-color-picker-input" type="color" value="<?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>" name="mjschool_system_color_code">
													&nbsp;<label class="mjschool-color-picker-label" for="mjschool_notification_fcm_key"><?php esc_html_e( 'System Color Code : ', 'mjschool' ); ?><?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?></label>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="mjschool_prefix" class="form-control validate[required] text-input" type="text" value="<?php echo esc_attr( get_option( 'mjschool_prefix' ) ); ?>" name="mjschool_prefix">
												<label  for="mjschool_prefix"><?php esc_html_e( 'Student Prefix', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="header">
										<h3 class="mjschool-first-header"><?php esc_html_e( 'Book Return Settings', 'mjschool' ); ?></h3>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
										<div class="form-group">
											<div class="col-md-12 form-control mjschool-input-height-48px">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label mjschool-margin-left-0" for="mjschool_return_option"><?php esc_html_e( 'Enable Return Option', 'mjschool' ); ?></label>
														<div class="checkbox mjschool-checkbox-label-padding-8px">
															<label class="control-label form-label">
																<input id="mjschool_return_option" type="checkbox" class="mjschool_return_option" name="mjschool_return_option" value="1" <?php echo checked( get_option( 'mjschool_return_option' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Yes', 'mjschool' ); ?></span>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool_return_period_field">
										<div class="form-group input">
											<label class="ml-1 mjschool-custom-top-label top" for="mjschool_return_period"><?php esc_html_e( 'Book Return Period', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select id="mjschool_return_period" class="form-control validate[required] text-input mjschool-max-width-100px" name="mjschool_return_period">
												<option value=""><?php esc_html_e( 'Book Return Period', 'mjschool' ); ?></option>
												<?php
												$period_id     = get_option( 'mjschool_return_period' );
												$obj_lib       = new Mjschool_Library();
												$category_data = $obj_lib->mjschool_get_period_list();
												if ( ! empty( $category_data ) ) {
													foreach ( $category_data as $retrieved_data ) {
														echo '<option value="' . esc_attr( $retrieved_data->ID ) . '" ' . selected( $period_id, $retrieved_data->ID ) . '>' . esc_html( $retrieved_data->post_title ) . ' ' . esc_attr__( 'Days', 'mjschool' ) . '</option>';
													}
												}
												?>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="header">
										<h3 class="mjschool-first-header"><?php esc_html_e( 'Recurring Invoices Settings', 'mjschool' ); ?></h3>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_enable_recurring_invoices"><?php esc_html_e( 'Enable Recurring Invoices', 'mjschool' ); ?></label>
														<input id="mjschool_enable_recurring_invoices" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_enable_recurring_invoices" value="yes" <?php echo checked( get_option( 'mjschool_enable_recurring_invoices' ), 'yes' ); ?> />
														<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input mjschool_system_payment_reminder_day">
											<div class="col-md-12 form-control">
												<input id="mjschool_system_payment_reminder_day_more" class="form-control" min="0" type="number" onKeyPress="if(this.value.length==2 ) return false;" placeholder="<?php esc_html_e( '03 Days', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_system_payment_reminder_day' ) ); ?>" name="mjschool_system_payment_reminder_day">
												<label  for="mjschool_system_payment_reminder_day_more"><?php esc_html_e( 'Reminder Before Day', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="header">
										<h3 class="mjschool-first-header"><?php esc_html_e( 'Fees Payment Reminder Settings', 'mjschool' ); ?></h3>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_system_payment_reminder_enable"><?php esc_html_e( 'Fees Payment Reminder', 'mjschool' ); ?></label>
														<input id="mjschool_system_payment_reminder_enable" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_system_payment_reminder_enable" value="yes" <?php echo checked( get_option( 'mjschool_system_payment_reminder_enable' ), 'yes' ); ?> />
														<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_invoice_option"><?php esc_html_e( 'Invoice in Tabuler format', 'mjschool' ); ?></label>
														<input id="mjschool_invoice_option" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_invoice_option" value="1" <?php echo checked( get_option( 'mjschool_invoice_option' ), '1' ); ?> />
														<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group input mjschool_system_payment_reminder_day">
											<div class="col-md-12 form-control">
												<input id="mjschool_system_payment_reminder_day" class="form-control" min="0" type="number" onKeyPress="if(this.value.length==2 ) return false;" placeholder="<?php esc_html_e( '03 Days', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_system_payment_reminder_day' ) ); ?>" name="mjschool_system_payment_reminder_day">
												<label  for="mjschool_system_payment_reminder_day"><?php esc_html_e( 'Reminder Before Day', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="header">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Payment Setting', 'mjschool' ); ?></h3>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mjschool_paypal_email" class="form-control validate[required,custom[email]] text-input" maxlength="100" type="text" value="<?php echo esc_attr( get_option( 'mjschool_paypal_email' ) ); ?>" name="mjschool_paypal_email">
														<label  for="mjschool_paypal_email"><?php esc_html_e( 'PayPal Email Id', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_enable_sandbox"><?php esc_html_e( 'Enable Sandbox', 'mjschool' ); ?></label>
																<input id="mjschool_enable_sandbox" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_enable_sandbox" value="1" <?php echo checked( get_option( 'mjschool_enable_sandbox' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-6 input">
												<div class="row">
													<div class="col-md-11">
														<?php
														$url = MJSCHOOL_PLUGIN_URL . "/assets/xml/mjschool-currencies.xml";
														$xml = simplexml_load_file( $url ) or wp_die( 'Error: Cannot create object' );
														?>
														<label class="ml-1 mjschool-custom-top-label top" for="mjschool_currency_code"><?php esc_html_e( 'Select Currency', 'mjschool' ); ?></label>
														<select id="mjschool_currency_code" name="mjschool_currency_code" class="form-control text-input mjschool-max-width-100px">
															<option value=""> <?php esc_html_e( 'Select Currency', 'mjschool' ); ?></option>
															<?php
															foreach ( $xml as $currency ) {
																$selected = selected( get_option( 'mjschool_currency_code' ), $currency->code, false );
																echo '<option value="' . esc_attr( $currency['code'] ) . '" ' . esc_html( $selected ) . '>';
																echo esc_html( $currency->name . ' ( ' . $currency->code . ' ' . $currency->symbol . ' )' );
																echo '</option>';
															}
															?>
														</select>
													</div>
													<div class="col-md-1">
														<span class="mjschool-font-23-px"><?php echo esc_html( mjschool_get_currency_symbol() ); ?></span>
													</div>
												</div>
												<span class="description"><?php esc_html_e( 'Selected currency might not supported by paypal. Please check with paypal.', 'mjschool' ); ?></span>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<textarea id="mjschool_invoice_notice" name="mjschool_invoice_notice" class="form-control mjschool-texarea-custom-padding-0"><?php echo esc_textarea( get_option( 'mjschool_invoice_notice' ) ); ?></textarea>
														<label for="mjschool_invoice_notice" class="mjschool-custom-top-label top active"><?php esc_html_e( 'Invoice Notice', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_past_pay"><?php esc_html_e( 'Allow Past Date Payment', 'mjschool' ); ?></label>
																<input id="mjschool_past_pay" type="checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_past_pay" value="1" <?php echo checked( get_option( 'mjschool_past_pay' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<?php
											if ( $school_obj->role === 'administrator' ) {
												if ( is_plugin_active( 'paymaster/paymaster.php' ) ) {
													?>
													<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3">
														<div class="form-group">
															<div class="col-md-12 form-control">
																<div class="row mjschool-padding-radio">
																	<div>
																		<label for="mjschool_paymaster_pack" class="mjschool-label-margin-left-0px mjschool-custom-top-label"><?php esc_html_e( 'Use Paymaster Payment Gateways', 'mjschool' ); ?></label>
																		<input type="checkbox" class="mjschool-margin-right-checkbox-css" value="yes" <?php echo checked( get_option( 'mjschool_paymaster_pack' ), 'yes' ); ?> name="mjschool_paymaster_pack">
																		<label><?php esc_html_e( 'Enable', 'mjschool' ); ?></label>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<?php
												}
											}
											?>
										</div>
										<span class="description">
											<a href="<?php if ( $school_obj->role === 'supportstaff' ) { echo '?dashboard=mjschool_user&page=feepayment'; } else { echo '?page=mjschool_fees_payment&tab=feespaymentlist'; } ?>" target="_blank" class="mjschool_blue_decoration_none"> <?php esc_html_e( 'Click here to add or update Fees Amount.', 'mjschool' ); ?> </a>
										</span>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="header">
											<h3 class="mjschool-first-header"><?php esc_html_e( 'Virtual Classroom Setting(Zoom)', 'mjschool' ); ?></h3>
										</div>
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-rtl-margin-top-15px">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_enable_virtual_classroom"><?php esc_html_e( 'Virtual Classroom', 'mjschool' ); ?></label>
																<input id="mjschool_enable_virtual_classroom" type="checkbox" id="virual_class_checkbox" class="mjschool-margin-right-checkbox-css" name="mjschool_enable_virtual_classroom" value="1" <?php echo checked( get_option( 'mjschool_enable_virtual_classroom' ), 'yes' ); ?> />
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<?php
										if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
											?>
											<style>
												#virtual_class_div {
													display: block;
												}
											</style>
											<?php
										} else {
											?>
											<style>
												#virtual_class_div {
													display: none;
												}
											</style>
											<?php
										}
										?>
										<div id="virtual_class_div">
											<div class="row">
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="mjschool_virtual_classroom_account_id" class="form-control text-input virtual_classroom_input" type="text" value="<?php echo esc_attr( get_option( 'mjschool_virtual_classroom_account_id' ) ); ?>" name="mjschool_virtual_classroom_account_id">
															<label  for="mjschool_virtual_classroom_account_id"><?php esc_html_e( 'Account ID', 'mjschool' ); ?><span class="required">*</span></label>
														</div>
														<span class="description"><?php esc_html_e( 'That will be provided by zoom.', 'mjschool' ); ?></span>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="mjschool_virtual_classroom_client_id" class="form-control text-input virtual_classroom_input" type="text" value="<?php echo esc_attr( get_option( 'mjschool_virtual_classroom_client_id' ) ); ?>" name="mjschool_virtual_classroom_client_id">
															<label  for="mjschool_virtual_classroom_client_id"><?php esc_html_e( 'Client Id', 'mjschool' ); ?><span class="required">*</span></label>
														</div>
														<span class="description"><?php esc_html_e( 'That will be provided by zoom.', 'mjschool' ); ?></span>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="mjschool_virtual_classroom_client_secret_id" class="form-control text-input virtual_classroom_input" type="text" value="<?php echo esc_attr( get_option( 'mjschool_virtual_classroom_client_secret_id' ) ); ?>" name="mjschool_virtual_classroom_client_secret_id">
															<label  for="mjschool_virtual_classroom_client_secret_id"><?php esc_html_e( 'Client Secret Id', 'mjschool' ); ?><span class="required">*</span></label>
														</div>
														<span class="description"><?php esc_html_e( 'That will be provided by zoom.', 'mjschool' ); ?></span>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
													<div class="form-group mb-3">
														<div class="col-md-12 form-control">
															<div class="row mjschool-padding-radio">
																<div>
																	<label class="mjschool-custom-top-label" for="mjschool_enable_virtual_classroom_reminder"><?php esc_html_e( 'Mail Notification Virtual ClassRoom Reminder', 'mjschool' ); ?></label>
																	<input id="mjschool_enable_virtual_classroom_reminder" class="mjschool-margin-right-checkbox-css" type="checkbox" name="mjschool_enable_virtual_classroom_reminder" value="1" <?php echo checked( get_option( 'mjschool_enable_virtual_classroom_reminder' ), 'yes' ); ?> />
																	<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
													<div class="form-group mb-3">
														<div class="col-md-12 form-control">
															<div class="row mjschool-padding-radio">
																<div>
																	<label class="mjschool-custom-top-label" for="mjschool_enable_mjschool_virtual_classroom_reminder"><?php esc_html_e( 'SMS Notification Virtual Class Room Reminder', 'mjschool' ); ?></label>
																	<input id="mjschool_enable_mjschool_virtual_classroom_reminder" class="mjschool-margin-right-checkbox-css" type="checkbox" name="mjschool_enable_mjschool_virtual_classroom_reminder" value="1" <?php echo checked( get_option( 'mjschool_enable_mjschool_virtual_classroom_reminder' ), 'yes' ); ?> />
																	<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="zoomurl" class="form-control text-input" type="text" value="<?php echo esc_url( site_url() ) . '/?page=mjschoolcallback'; ?>" name="zoomurl" disabled>
															<label  for="zoomurl"><?php esc_html_e( 'Redirect URL', 'mjschool' ); ?></label>
														</div>
														<span class="description"><?php esc_html_e( 'Please copy this Redirect URL and add in your zoom account Redirect URL.', 'mjschool' ); ?></span>
													</div>
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="mjschool_virtual_classroom_reminder_before_time" class="form-control" min="0" type="number" onKeyPress="if(this.value.length==2 ) return false;" placeholder="<?php esc_html_e( '01 Minute', 'mjschool' ); ?>" value="<?php echo esc_attr( get_option( 'mjschool_virtual_classroom_reminder_before_time' ) ); ?>" name="mjschool_virtual_classroom_reminder_before_time">
															<label  for="mjschool_virtual_classroom_reminder_before_time"><?php esc_html_e( 'Reminder Before Time', 'mjschool' ); ?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="header">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Message Setting', 'mjschool' ); ?></h3>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label for="mjschool_parent_send_message" class="mjschool-custom-top-label"><?php esc_html_e( 'Parent can send message to class students', 'mjschool' ); ?></label>
																<input id="mjschool_parent_send_message" type="checkbox" class="mjschool-margin-right-checkbox-css" value="1" <?php echo checked( get_option( 'mjschool_parent_send_message' ), 1 ); ?> name="mjschool_parent_send_message">
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label for="mjschool_student_send_message" class="mjschool-custom-top-label"><?php esc_html_e( ' Student can send message to each other', 'mjschool' ); ?></label>
																<input id="mjschool_student_send_message" type="checkbox" class="mjschool-margin-right-checkbox-css" value="1" <?php echo checked( get_option( 'mjschool_student_send_message' ), 1 ); ?> name="mjschool_student_send_message">
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Student Approval setting', 'mjschool' ); ?></h3>
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label for="mjschool_student_approval" class="mjschool-label-margin-left-0px mjschool-custom-top-label"><?php esc_html_e( 'Student Approval', 'mjschool' ); ?></label>
																<input id="mjschool_student_approval" type="checkbox" class="mjschool-margin-right-checkbox-css" value="1" <?php echo checked( get_option( 'mjschool_student_approval' ), 1 ); ?> name="mjschool_student_approval"> <?php esc_html_e( 'Enable', 'mjschool' ); ?>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-margin-top-15px-rs mjschool-rtl-margin-top-15px">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Video Setting', 'mjschool' ); ?></h3>
												<div class="form-group mb-3">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div>
																<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for="mjschool_enable_video_popup_show"><?php esc_html_e( 'How to Videos Display?', 'mjschool' ); ?></label>
																<input id="mjschool_enable_video_popup_show" type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="mjschool_enable_video_popup_show" value="yes" <?php echo checked( get_option( 'mjschool_enable_video_popup_show' ), 'yes' ); ?> />
																<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="header">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Other setting', 'mjschool' ); ?></h3>
											</div>
											<div class="col-md-6">
												<div class="form-group input mjschool-rtl-margin-0px">
													<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
														<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="mjschool_email"><?php esc_html_e( 'Principal Signature', 'mjschool' ); ?></label>
														<div class="col-sm-12 mjschool-display-flex">
															<input type="text" id="mjschool_principal_signature" name="mjschool_principal_signature" class="mjschool-image-path-dots form-control" value="<?php echo esc_attr( get_option( 'mjschool_principal_signature' ) ); ?>" readonly />
															<input id="upload_principal_signature" type="button" class="button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
														</div>
													</div>
													<div class="clearfix"></div>
													<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
														<div id="upload_user_aprincipal_signature">

															<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" />

														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group mb-3">
													<div class="col-md-12 form-control mjschool_minheight_47px" >
														<div class="row mjschool-padding-radio">
															<div>
																<label for="mjschool_mail_notification" class="mjschool-label-margin-left-0px mjschool-custom-top-label"><?php esc_html_e( 'Mail Notification', 'mjschool' ); ?></label>
																<input id="mjschool_mail_notification" type="checkbox" class="mjschool-margin-right-checkbox-css" value="1" <?php echo checked( get_option( 'mjschool_mail_notification' ), 1 ); ?> name="mjschool_mail_notification">
																<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
										<div class="row"><!--Row div start.-->
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
												<div class="header">
													<h3 class="mjschool-first-header"><?php esc_html_e( 'Footer setting', 'mjschool' ); ?></h3>
												</div>
												<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 P-0 mjschool-rtl-custom-padding-0px">
													<div class="form-group input mjschool-rtl-margin-0px">
														<div class="col-md-12 form-control">
															<input id="mjschool_footer_description" class="form-control text-input" type="text" minlength="6" maxlength="100" value="<?php echo esc_attr( get_option( 'mjschool_footer_description' ) ); ?>" name="mjschool_footer_description">
															<label  for="mjschool_footer_description"><?php esc_html_e( 'Footer Description', 'mjschool' ); ?></label>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
												<div class="header">
													<h3 class="mjschool-first-header"><?php esc_html_e( 'Datatable Header Settings', 'mjschool' ); ?></h3>
												</div>
												<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
													<div class="form-group">
														<div class="col-md-12 form-control mjschool-input-height-48px">
															<div class="row mjschool-padding-radio">
																<div class="input-group">
																	<label class="mjschool-custom-top-label mjschool-margin-left-0" for="mjschool_heder_enable"><?php esc_html_e( 'Header', 'mjschool' ); ?></label>
																	<div class="checkbox mjschool-checkbox-label-padding-8px">
																		<label class="control-label form-label">
																			<input id="mjschool_heder_enable" type="checkbox" name="mjschool_heder_enable" value="1" <?php echo checked( get_option( 'mjschool_heder_enable' ), 'yes' ); ?> />
																			<span><?php esc_html_e( 'Enable', 'mjschool' ); ?></span>
																		</label>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3">
												<div class="header">
													<h3 class="mjschool-first-header"><?php esc_html_e( 'Push Notification setting', 'mjschool' ); ?></h3>
												</div>
												<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mjschool-rtl-custom-padding-0px">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="mjschool_notification_fcm_key" class="form-control text-input" type="text" value="<?php echo esc_attr( get_option( 'mjschool_notification_fcm_key' ) ); ?>" name="mjschool_notification_fcm_key">
															<label  for="mjschool_notification_fcm_key"><?php esc_html_e( 'Notification FCM Key', 'mjschool' ); ?></label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
									if ( $user_access_edit === '1' ) {
										?>
										<div class="form-body mjschool-user-form">
											<div class="row">
												<div class="col-sm-6">
													<input class="form-control text-input" type="hidden" value="<?php echo esc_attr( get_option( 'mjschool_general_setting_option_update' ) ); ?>" name="mjschool_general_setting_option_update">
													<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_setting" class="btn btn-success mjschool-save-btn" />
												</div>
											</div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</form>
						<?php
					}
					if ( $active_tab === 'dashboard_card_settings' ) {
						// Check nonce for dashboard card settings tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="setting_form">
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_dashboard_setting_nonce' ) ); ?>">
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Dashboard Card setting For Support-Staff', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
								<div class="row"><!--Row div start.-->
									<?php $dashboard_card_for_staff = get_option( 'mjschool_dashboard_card_for_support_staff' ); ?>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Users Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="users_chart_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_user_chart'] ) ? checked( $dashboard_card_for_staff['mjschool_user_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Student Status Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="student_status_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_student_status_chart'] ) ? checked( $dashboard_card_for_staff['mjschool_student_status_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Attendance Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="attendance_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_attendance_chart'] ) ? checked( $dashboard_card_for_staff['mjschool_attendance_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Payment Status Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="payment_status_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_payment_status_chart'] ) ? checked( $dashboard_card_for_staff['mjschool_payment_status_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Payment Report', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="payment_report_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_payment_report'] ) ? checked( $dashboard_card_for_staff['mjschool_payment_report'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Fees Payment Card', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="invoice_enable_staff" value="yes" <?php echo isset( $dashboard_card_for_staff['mjschool_invoice_chart'] ) ? checked( $dashboard_card_for_staff['mjschool_invoice_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Dashboard Card setting For Teacher', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
								<div class="row"><!--Row div start.-->
									<?php $dashboard_card_for_teacher = get_option( 'mjschool_dashboard_card_for_teacher' ); ?>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'User Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="user_chart_enable_teacher" value="yes" <?php echo isset( $dashboard_card_for_teacher['mjschool_user_chart'] ) ? checked( $dashboard_card_for_teacher['mjschool_user_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Student Status Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="student_status_enable_teacher" value="yes" <?php echo isset( $dashboard_card_for_teacher['mjschool_student_status_chart'] ) ? checked( $dashboard_card_for_teacher['mjschool_student_status_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Attendance Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="attendance_chart_enable_teacher" value="yes" <?php echo isset( $dashboard_card_for_teacher['mjschool_attendance_chart'] ) ? checked( $dashboard_card_for_teacher['mjschool_attendance_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Dashboard Card setting For Parent', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
								<div class="row"><!--Row div start.-->
									<?php $dashboard_card_for_parent = get_option( 'mjschool_dashboard_card_for_parent' ); ?>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3  mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'User Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="user_chart_parent" value="yes" <?php echo isset( $dashboard_card_for_parent['mjschool_user_chart'] ) ? checked( $dashboard_card_for_parent['mjschool_user_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Payement Status Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="payment_status_parent" value="yes" <?php echo isset( $dashboard_card_for_parent['mjschool_payment_status_chart'] ) ? checked( $dashboard_card_for_parent['mjschool_payment_status_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Fees Payment Card', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="invoice_enable_parent" value="yes" <?php echo isset( $dashboard_card_for_parent['mjschool_invoice_chart'] ) ? checked( $dashboard_card_for_parent['mjschool_invoice_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'Dashboard Card setting For Student', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
								<div class="row"><!--Row div start.-->
									<?php $dashboard_card = get_option( 'mjschool_dashboard_card_for_student' ); ?>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Users Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="user_chart_enable_student" value="yes" <?php echo isset( $dashboard_card['mjschool_user_chart'] ) ? checked( $dashboard_card['mjschool_user_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Payment Status Chart', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="payment_status_chart_enable_student" value="yes" <?php echo isset( $dashboard_card['mjschool_payment_status_chart'] ) ? checked( $dashboard_card['mjschool_payment_status_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px">
										<div class="form-group mb-3">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div>
														<label class="mjschool-label-margin-left-0px mjschool-custom-top-label" for=""><?php esc_html_e( 'Fees Payment Card', 'mjschool' ); ?></label>
														<input type="checkbox" class="mjschool-res-margin-top-5px mjschool-margin-right-checkbox-css" name="invoice_enable" value="yes" <?php echo isset( $dashboard_card['mjschool_invoice_chart'] ) ? checked( $dashboard_card['mjschool_invoice_chart'], 'yes', false ) : ''; ?> />
														<span class="mjschool-res-margin-top-5px"><?php esc_html_e( 'Show', 'mjschool' ); ?></span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
							if ( $user_access_edit === '1' ) {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-6">
											<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_dashboard_setting" class="btn btn-success mjschool-save-btn" />
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</form>
						<?php
					}
					if ( $active_tab === 'document_settings' ) {
						// Check nonce for document settings tab.
						if ( isset( $_GET['tab'] ) ) {
							if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_general_setting_tab' ) ) {
								wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
							}
						}
						?>
						<form name="document_setting_form" action="" method="post" class="mjschool-form-horizontal" id="document_setting_form"><!--VERIFICATION FORM START-->
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_document_setting_nonce' ) ); ?>">
							<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
								<div class="row"><!--Row div start.-->
									<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-res-margin-bottom-20px multiselect_validation_document mjschool-multiple-select mb-3">
										<?php
										$document_option = get_option( 'mjschool_upload_document_type' );
										$document_type   = explode( ', ', $document_option );
										?>
										<span class="mjschool-multiselect-label">
											<label class="ml-1 mjschool-custom-top-label top mjschool_margin_left_20px" for="mjschool_document_type"><?php esc_html_e( 'Document Extension', 'mjschool' ); ?></label>
										</span>
										<select id="mjschool_document_type" class="form-control document_type" name="document_type[]" multiple="multiple">
											<option value="pdf" <?php echo in_array( 'pdf', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'pdf', 'mjschool' ); ?></option>
											<option value="doc" <?php echo in_array( 'doc', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'doc', 'mjschool' ); ?></option>
											<option value="docx" <?php echo in_array( 'docx', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'docx', 'mjschool' ); ?></option>
											<option value="xls" <?php echo in_array( 'xls', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'xls', 'mjschool' ); ?></option>
											<option value="xlsx" <?php echo in_array( 'xlsx', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'xlsx', 'mjschool' ); ?></option>
											<option value="ppt" <?php echo in_array( 'ppt', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'ppt', 'mjschool' ); ?></option>
											<option value="pptx" <?php echo in_array( 'pptx', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'pptx', 'mjschool' ); ?></option>
											<option value="gif" <?php echo in_array( 'gif', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'gif', 'mjschool' ); ?></option>
											<option value="png" <?php echo in_array( 'png', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'png', 'mjschool' ); ?></option>
											<option value="jpg" <?php echo in_array( 'jpg', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'jpg', 'mjschool' ); ?></option>
											<option value="jpeg" <?php echo in_array( 'jpeg', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'jpeg', 'mjschool' ); ?></option>
											<option value="bmp" <?php echo in_array( 'bmp', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'bmp', 'mjschool' ); ?></option>
											<option value="webp" <?php echo in_array( 'webp', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'webp', 'mjschool' ); ?></option>
											<option value="svg" <?php echo in_array( 'svg', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'svg', 'mjschool' ); ?></option>
											<option value="csv" <?php echo in_array( 'csv', $document_type ) ? 'selected' : ''; ?>><?php esc_html_e( 'csv', 'mjschool' ); ?></option>
										</select>
									</div>
									<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-res-margin-bottom-20px mjschool-multiselect-validation-profile mjschool-multiple-select">
										<?php
										$profile_option    = get_option( 'mjschool_upload_profile_extention' );
										$profile_extention = explode( ', ', $profile_option );
										?>
										<select id="mjschool_profile_extention" class="form-control profile_extention" name="profile_extension[]" multiple="multiple">
											<option value="gif" <?php echo in_array( 'gif', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'gif', 'mjschool' ); ?></option>
											<option value="png" <?php echo in_array( 'png', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'png', 'mjschool' ); ?></option>
											<option value="jpg" <?php echo in_array( 'jpg', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'jpg', 'mjschool' ); ?></option>
											<option value="jpeg" <?php echo in_array( 'jpeg', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'jpeg', 'mjschool' ); ?></option>
											<option value="bmp" <?php echo in_array( 'bmp', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'bmp', 'mjschool' ); ?></option>
											<option value="webp" <?php echo in_array( 'webp', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'webp', 'mjschool' ); ?></option>
											<option value="svg" <?php echo in_array( 'svg', $profile_extention ) ? 'selected' : ''; ?>><?php esc_html_e( 'svg', 'mjschool' ); ?></option>
										</select>
										<span class="mjschool-multiselect-label">
											<label class="ml-1 mjschool-custom-top-label top mjschool_margin_left_20px"  for="mjschool_profile_extention"><?php esc_html_e( 'Profile Extension For Frontend', 'mjschool' ); ?></label>
										</span>
									</div>
									<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="document_size" class="form-control validate[required,custom[onlyNumberSp],max[40],min[1]]" type="text" value="<?php echo esc_attr( get_option( 'mjschool_upload_document_size' ) ); ?>" name="document_size">
												<label class="ms-1 mjschool-custom-top-label top" for="document_size"><?php esc_html_e( 'Document Size(MB)', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-6 col-lg-6 col-sm-12 col-xl-6">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="profile_size" class="form-control validate[required,custom[onlyNumberSp],max[20],min[1]]" type="text" value="<?php echo esc_attr( get_option( 'mjschool_upload_profile_size' ) ); ?>" name="profile_size">
												<label class="mjschool-custom-top-label top" for="profile_size"><?php esc_html_e( 'Profile Image Size(MB)', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
							if ( $user_access_edit === '1' ) {
								?>
								<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form start.-->
									<div class="row"><!--Row div start.-->
										<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
											<input type="submit" value="<?php esc_html_e( 'Submit', 'mjschool' ); ?>" name="save_document_setting" id="save_document_setting" class="btn mjschool-document-type-validation mjschool-save-btn" />
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</form> <!--Verification form end.-->
						<?php
					}
					?>
				</div><!-- Mjschool-panel-body.-->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner.-->