<?php
/**
 * SMS Gateway Setting Configuration File.
 *
 * This file handles the administrative interface for configuring and saving
 * the settings for various third-party SMS gateway services (e.g., Twilio, Msg91, ClickSend).
 * It includes user access control for editing settings and uses nonce verification for form submission security.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'mjschool_setting';
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
$current_mjschool_service_active = get_option( 'mjschool_service' );
if ( isset( $_REQUEST['save_mjschool_setting'] ) ) {
	if ( isset( $_REQUEST['select_serveice'] ) && sanitize_text_field(wp_unslash($_REQUEST['select_serveice'])) === 'clickatell' ) {
		$custm_mjschool_service              = array();
		$result                              = get_option( 'mjschool_clickatell_mjschool_service' );
		$custm_mjschool_service['username']  = trim( sanitize_text_field(wp_unslash($_REQUEST['username'])) );
		$custm_mjschool_service['password']  = sanitize_text_field(wp_unslash($_REQUEST['password']));
		$custm_mjschool_service['api_key']   = sanitize_text_field(wp_unslash($_REQUEST['api_key']));
		$custm_mjschool_service['sender_id'] = sanitize_text_field(wp_unslash($_REQUEST['sender_id']));
		$result                              = update_option( 'mjschool_clickatell_mjschool_service', $custm_mjschool_service );
	}
	if ( isset( $_REQUEST['select_serveice'] ) && sanitize_text_field(wp_unslash($_REQUEST['select_serveice'])) === 'twillo' ) {
		$custm_mjschool_service                = array();
		$result                                = get_option( 'mjschool_twillo_mjschool_service' );
		$custm_mjschool_service['account_sid'] = trim( sanitize_text_field(wp_unslash($_REQUEST['account_sid'])) );
		$custm_mjschool_service['auth_token']  = trim( sanitize_text_field(wp_unslash($_REQUEST['auth_token'])) );
		$custm_mjschool_service['from_number'] = sanitize_text_field(wp_unslash($_REQUEST['from_number']));
		$result                                = update_option( 'mjschool_twillo_mjschool_service', $custm_mjschool_service );
	}
	if ( isset( $_REQUEST['select_serveice'] ) && sanitize_text_field(wp_unslash($_REQUEST['select_serveice'])) === 'msg91' ) {
		$custm_mjschool_service                        = array();
		$result                                        = get_option( 'mjschool_msg91_mjschool_service' );
		$custm_mjschool_service['msg91_senderID']      = trim( sanitize_text_field(wp_unslash($_REQUEST['msg91_senderID'])) );
		$custm_mjschool_service['mjschool_auth_key']   = trim( sanitize_text_field(wp_unslash($_REQUEST['mjschool_auth_key'])) );
		$custm_mjschool_service['wpnc_mjschool_route'] = sanitize_text_field(wp_unslash($_REQUEST['wpnc_mjschool_route']));
		$result                                        = update_option( 'mjschool_msg91_mjschool_service', $custm_mjschool_service );
	}
	update_option( 'mjschool_service', sanitize_text_field(wp_unslash($_REQUEST['select_serveice'])) );
	wp_safe_redirect( home_url() . '?dashboard=mjschool_user&page=mjschool-setting&tab=mjschool_setting&message=1' );
	die();
}
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
	switch ( $message ) {
		case '1':
			$message_string = esc_html__( 'SMS Settings Updated Successfully.', 'mjschool' );
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
			
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	?>
	<!-- Tab panes. -->
	<?php
	if ( $active_tab === 'mjschool_setting' ) {
		?>
		<div class="mjschool-panel-body mjschool-margin-top-40">
			<form action="" method="post" class="mjschool-form-horizontal" id="mjschool_setting_form">
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'SMS Setting Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-res-margin-bottom-20px">
							<div class="form-group">
								<div class="col-md-12 form-control">
									<div class="row mjschool-padding-radio">
										<div class="input-group">
											<label class="mjschool-custom-top-label" for="enable"><?php esc_html_e( 'Select Message Service', 'mjschool' ); ?></label>
											<div class="d-inline-block mjschool-select-message-service">
												<label class="radio-inline custom_radio">
													<input id="checkbox" type="radio" <?php echo checked( $current_mjschool_service_active, 'clickatell' ); ?> name="select_serveice" class="label_set" value="clickatell"> <?php esc_html_e( 'Clickatell ', 'mjschool' ); ?>
												</label>&nbsp;&nbsp;&nbsp;&nbsp;
												<label class="radio-inline custom_radio">
													<input id="checkbox" type="radio" <?php echo checked( $current_mjschool_service_active, 'msg91' ); ?> name="select_serveice" class="label_set" value="msg91"> <?php esc_html_e( 'MSG91 ', 'mjschool' ); ?>
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
						?>
						<div class="form-body mjschool-user-form mt-3">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="username" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $clickatell['username'] ); ?>" name="username">
											<label  for="username"><?php esc_html_e( 'Username', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="password" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $clickatell['password'] ); ?>" name="password">
											<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="api_key" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $clickatell['api_key'] ); ?>" name="api_key">
											<label  for="api_key"><?php esc_html_e( 'API Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="sender_id" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $clickatell['sender_id'] ); ?>" name="sender_id">
											<label  for="sender_id"><?php esc_html_e( 'Sender Id', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					if ( $current_mjschool_service_active === 'msg91' ) {
						$msg91 = get_option( 'mjschool_msg91_mjschool_service' );
						?>
						<div class="form-body mjschool-user-form mt-3">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="mjschool_auth_key" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $msg91['mjschool_auth_key'] ); ?>" name="mjschool_auth_key">
											<label  for="mjschool_auth_key"><?php esc_html_e( 'Authentication Key', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="msg91_senderID" class="form-control validate[required] text-input" type="text" name="msg91_senderID" value="<?php echo esc_attr( $msg91['msg91_senderID'] ); ?>">
											<label  for="msg91_senderID"><?php esc_html_e( 'SenderID', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="wpnc_mjschool_route" class="form-control validate[required] text-input" type="text" name="wpnc_mjschool_route" value="<?php echo esc_attr( $msg91['wpnc_mjschool_route'] ); ?>">
											<label  for="wpnc_mjschool_route"><?php esc_html_e( 'Route', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
									<label class="col-sm-10 control-label col-form-label text-md-end " for="wpnc_mjschool_route"><b><?php esc_html_e( 'If your operator supports multiple routes then give one route name. Eg: route=1 for promotional, route=4 for transactional SMS.', 'mjschool' ); ?></b></label>
								</div>
							</div>
						</div>
						<?php
					}
					if ( $user_access['add'] === '1' || $user_access['edit'] === '1' ) {
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6">
									<input type="submit" value="<?php esc_html_e( 'Save', 'mjschool' ); ?>" name="save_mjschool_setting" class="btn btn-success mjschool-save-btn" />
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</form>
		</div>
		<?php
	}
	?>
</div>