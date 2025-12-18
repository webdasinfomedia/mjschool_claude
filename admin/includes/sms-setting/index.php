<?php
/**
 * SMS Settings Management Page.
 *
 * This file provides the administrative interface for managing SMS gateway configurations
 * within the MJSchool plugin. It allows administrators to select and configure supported
 * SMS services such as Clickatell and MSG91 for sending notifications to users.
 *
 * Key Features:
 * - Displays a settings form to configure API credentials for SMS providers.
 * - Supports multiple gateways (Clickatell, MSG91) with dynamic field rendering.
 * - Implements WordPress nonces and access control to secure settings operations.
 * - Validates required input fields using the jQuery Validation Engine.
 * - Saves configuration data securely using WordPress `update_option()` API.
 * - Displays confirmation messages upon successful updates.
 * - Enforces role-based permissions (Add/Edit) for settings access.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/sms-setting
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'mjschool_setting' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
}
$current_mjschool_service_active = get_option( 'mjschool_service' );

if ( isset( $_REQUEST['save_mjschool_setting'] ) ) {
	// Verify nonce.
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mjschool_sms_settings_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
	}

	// Sanitize selected service.
	$selected_service = isset( $_REQUEST['select_serveice'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['select_serveice'] ) ) : '';
	if ( $selected_service === 'clickatell' ) {
		$custm_mjschool_service              = array();
		$custm_mjschool_service['username']  = isset( $_REQUEST['username'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['username'] ) ) : '';
		$custm_mjschool_service['password']  = isset( $_REQUEST['password'] ) ? trim( wp_unslash( $_REQUEST['password'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Password field, no sanitize_text_field.
		$custm_mjschool_service['api_key']   = isset( $_REQUEST['api_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['api_key'] ) ) : '';
		$custm_mjschool_service['sender_id'] = isset( $_REQUEST['sender_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sender_id'] ) ) : '';
		update_option( 'mjschool_clickatell_mjschool_service', $custm_mjschool_service );
	}
	if ( $selected_service === 'twillo' ) {
		$custm_mjschool_service                = array();
		$custm_mjschool_service['account_sid'] = isset( $_REQUEST['account_sid'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_sid'] ) ) : '';
		$custm_mjschool_service['auth_token']  = isset( $_REQUEST['auth_token'] ) ? trim( wp_unslash( $_REQUEST['auth_token'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Auth token field, no sanitize_text_field.
		$custm_mjschool_service['from_number'] = isset( $_REQUEST['from_number'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['from_number'] ) ) : '';
		update_option( 'mjschool_twillo_mjschool_service', $custm_mjschool_service );
	}
	if ( $selected_service === 'msg91' ) {
		$custm_mjschool_service                        = array();
		$custm_mjschool_service['msg91_senderID']      = isset( $_REQUEST['msg91_senderID'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['msg91_senderID'] ) ) : '';
		$custm_mjschool_service['mjschool_auth_key']   = isset( $_REQUEST['mjschool_auth_key'] ) ? trim( wp_unslash( $_REQUEST['mjschool_auth_key'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Auth key field, no sanitize_text_field.
		$custm_mjschool_service['wpnc_mjschool_route'] = isset( $_REQUEST['wpnc_mjschool_route'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['wpnc_mjschool_route'] ) ) : '';
		update_option( 'mjschool_msg91_mjschool_service', $custm_mjschool_service );
	}
	// Save selected service.
	update_option( 'mjschool_service', $selected_service );

	wp_safe_redirect( admin_url( 'admin.php?page=mjschool_sms_setting&message=1' ) );
	die();
}
?>
<!-- Mjschool-page-inner. -->
<div class="mjschool-page-inner">
	<!-- Mjschool-main-list-margin-15px. -->
	<div class="mjschool-main-list-margin-15px mjschool-marks-list">
		<?php
		$message        = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		$message_string = '';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'SMS Settings Updated Successfully.', 'mjschool' );
				break;
		}
		if ( $message && $message !== '0' ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
		<?php } ?>
		<div class="row"><!-- Row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-main-list-page"><!-- Mjschool-main-list-page. -->
					<div class="mjschool-panel-body">
						<form action="" method="post" class="mjschool-form-horizontal" id="mjschool_setting_form">
							<?php wp_nonce_field( 'mjschool_sms_settings_nonce' ); ?>
							<div class="header">
								<h3 class="mjschool-first-header"><?php esc_html_e( 'SMS Setting Information', 'mjschool' ); ?></h3>
							</div>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
										<div class="form-group">
											<div class="col-md-12 form-control">
												<div class="row mjschool-padding-radio">
													<div class="input-group">
														<label class="mjschool-custom-top-label" for="enable"><?php esc_html_e( 'Select Message Service', 'mjschool' ); ?></label>
														<div class="d-inline-block mjschool-select-message-service">
															<label class="radio-inline custom_radio">
																<input id="checkbox" type="radio" <?php checked( $current_mjschool_service_active, 'clickatell' ); ?> name="select_serveice" class="label_set" value="clickatell"> <?php esc_html_e( 'Clickatell ', 'mjschool' ); ?>
															</label>
															<label class="radio-inline custom_radio">
																<input id="checkbox" type="radio" <?php checked( $current_mjschool_service_active, 'msg91' ); ?> name="select_serveice" class="label_set" value="msg91"> <?php esc_html_e( 'MSG91 ', 'mjschool' ); ?>
															</label>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="mt-3" id="mjschool_setting_block">
								<?php
								if ( $current_mjschool_service_active === 'clickatell' ) {
									$clickatell = get_option( 'mjschool_clickatell_mjschool_service' );
									$api_key    = isset( $clickatell['api_key'] ) ? $clickatell['api_key'] : '';
									?>
									<div class="form-body mjschool-user-form mt-3">
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="api_key" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $api_key ); ?>" name="api_key">
														<label for="api_key"><?php esc_html_e( 'API Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								if ( $current_mjschool_service_active === 'msg91' ) {
									$msg91              = get_option( 'mjschool_msg91_mjschool_service' );
									$mjschool_auth_key  = isset( $msg91['mjschool_auth_key'] ) ? $msg91['mjschool_auth_key'] : '';
									$msg91_senderID     = isset( $msg91['msg91_senderID'] ) ? $msg91['msg91_senderID'] : '';
									$wpnc_mjschool_route = isset( $msg91['wpnc_mjschool_route'] ) ? $msg91['wpnc_mjschool_route'] : '';
									?>
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mjschool_auth_key" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $mjschool_auth_key ); ?>" name="mjschool_auth_key">
														<label class="active" for="mjschool_auth_key"><?php esc_html_e( 'Authentication Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="msg91_senderID" class="form-control validate[required] text-input" type="text" name="msg91_senderID" value="<?php echo esc_attr( $msg91_senderID ); ?>">
														<label class="active" for="msg91_senderID"><?php esc_html_e( 'SenderID ', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="wpnc_mjschool_route" class="form-control validate[required] text-input" type="text" name="wpnc_mjschool_route" value="<?php echo esc_attr( $wpnc_mjschool_route ); ?>">
														<label class="active" for="wpnc_mjschool_route"><?php esc_html_e( 'Route', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
								}
								?>
							</div>
							<?php
							if ( $user_access_add === '1' || $user_access_edit === '1' ) {
								?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-6">
											<input type="submit" value="<?php esc_attr_e( 'Save', 'mjschool' ); ?>" name="save_mjschool_setting" class="btn btn-success mjschool-save-btn" />
										</div>
									</div>
								</div>
								<?php
							}
							?>
						</form>
					</div>
					<div class="clearfix"> </div>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->