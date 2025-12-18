<?php

/**
 * MJSchool Setup & License Management Template.
 *
 * This file handles the setup and license verification interface for
 * the MJSchool plugin.
 * It provides functionality to register, verify, and reset the plugin license within
 * the WordPress admin dashboard.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/setupform
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'setup';
?>
<div id="mjschool-img-spinner"></div>
<div class="mjschool_ajax-ani"></div>
<div class="mjschool-page-inner mjschool_min_height_1088px">
<?php
	if ( isset( $_REQUEST['varify_key'] ) ) {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_license_registration_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		// Sanitize POST data before passing.
		$post_data     = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$verify_result = mjschool_submit_setup_form( $post_data );
		if ( isset( $verify_result['mjschool_verify'] ) && $verify_result['mjschool_verify'] === '0' ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible successMessage">
				<p><?php echo esc_html( $verify_result['message'] ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo isset( $verify_result['message'] ) ? esc_html( $verify_result['message'] ) : ''; ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
	if ( isset( $_REQUEST['reset_key'] ) ) {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_license_reset_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		// Sanitize POST data before passing.
		$post_data  = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$reset_form = mjschool_reset_key_form( $post_data );
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php echo esc_html( $reset_form ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php if ( $reset_form === 'OTP sent to your email' ) : ?>
			<?php
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Session data is internally managed.
			$session_licence_key = isset( $_SESSION['mjschool_licence_key'] ) ? sanitize_text_field( wp_unslash( $_SESSION['mjschool_licence_key'] ) ) : '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Session data is internally managed.
			$session_email = isset( $_SESSION['enter_email'] ) ? sanitize_email( wp_unslash( $_SESSION['enter_email'] ) ) : '';
			?>
			<div id="countdown-timer" class="mjschool_color_red_font_weight_bold">
				OTP will expire in <span id="timer">02:00</span>.
			</div>
			<!-- Always visible resend button (disabled initially). -->
			<div id="resend-otp-wrapper" class="mjschool-margin-top-10px">
				<form method="post">
					<?php wp_nonce_field( 'mjschool_license_reset_nonce', 'security' ); ?>
					<input type="hidden" name="licence_key" value="<?php echo esc_attr( $session_licence_key ); ?>">
					<input type="hidden" name="enter_email" value="<?php echo esc_attr( $session_email ); ?>">
					<button type="submit" name="reset_key" class="btn btn-secondary mjschool_setup_form_no_cursor" id="resend-btn" disabled >
						Resend OTP
					</button>
				</form>
			</div>
			<script type="text/javascript">
				(() => {
					"use strict";
					const durationStart = 120; // 2 minutes in seconds.
					let duration = durationStart;
					const timerDisplay = document.getElementById( 'timer' );
					const resendBtn = document.getElementById( 'resend-btn' );
					const countdownContainer = document.getElementById( 'countdown-timer' );
					if (!timerDisplay || !resendBtn || !countdownContainer) {
						console.warn( 'Timer elements missing in DOM' );
						return;
					}
					resendBtn.disabled = true;
					resendBtn.classList.add( 'btn-secondary' );
					resendBtn.classList.remove( 'btn-primary' );
					resendBtn.style.cursor = 'not-allowed';
					const interval = setInterval(() => {
						let minutes = Math.floor(duration / 60);
						let seconds = duration % 60;
						minutes = minutes < 10 ? '0' + minutes : minutes;
						seconds = seconds < 10 ? '0' + seconds : seconds;
						timerDisplay.textContent = `${minutes}:${seconds}`;
						if (--duration < 0) {
							clearInterval(interval);
							countdownContainer.textContent = "OTP expired.";
							// Enable the resend button.
							resendBtn.disabled = false;
							resendBtn.classList.remove( 'btn-secondary' );
							resendBtn.classList.add( 'btn-primary' );
							resendBtn.style.backgroundColor = ''; // Reset to default if needed.
							resendBtn.style.cursor = 'pointer';
						}
					}, 1000);
				})();
			</script>
		<?php endif; ?>
		<?php
	}
	if ( isset( $_REQUEST['verify_reset_otp'] ) ) {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_verify_otp_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		// Sanitize POST data before passing.
		$post_data  = array_map( 'sanitize_text_field', wp_unslash( $_POST ) );
		$reset_form = mjschool_reset_key_otp_verify_form( $post_data );
		if ( $reset_form === 'License has been reset successfully' ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_setup&reset=success' ) );
			die();
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $reset_form ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
	?>
	<script type="text/javascript">
		(function(jQuery){
			"use strict";
			// Fade out success message and redirect after 3 seconds.
			jQuery(document).ready(function() {
				setTimeout(function() {
					jQuery( '.successMessage' ).fadeOut( 'fast', function() {
						window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=mjschool' ) ); ?>';
					});
				}, 3000);
			});
			// Initialize validation engines.
			jQuery(document).ready(function() {
				jQuery( '#verification_form, #reset_form, #verify_otp_form' ).validationEngine({
					promptPosition: "bottomLeft",
					maxErrorsPerField: 1
				});
			});
		})(jQuery);
	</script>
	<?php
	if ( isset( $_REQUEST['reset'] ) && ( sanitize_text_field( wp_unslash( $_REQUEST['reset'] ) ) === 'success' ) ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
			<p><?php esc_html_e( 'License has been reset successfully', 'mjschool' ); ?></p>
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
		</div>
		<?php
	}
	?>
	<div class="mjschool-panel-body mjschool-main-list-margin-15px"><!------------------ Panel body. ------------------->
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
			<li class="<?php if ( $active_tab === 'setup' ) { ?>active<?php } ?>">
				<a href="?page=mjschool_setup&tab=setup" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'setup' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Register License', 'mjschool' ); ?>
				</a>
			</li>
			<li class="<?php if ( $active_tab === 'reset' ) { ?>active<?php } ?>">
				<a href="?page=mjschool_setup&tab=reset" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab ) === 'reset' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Reset License', 'mjschool' ); ?>
				</a>
			</li>
		</ul>
		<?php
		if ( $active_tab === 'setup' ) {
			$check_varification = '';
			$licence_key        = get_option( 'mjschool_licence_key' );
			$email              = get_option( 'mjschool_setup_email' );
			if ( $licence_key && $email ) {
				$domain_name        = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
				$check_varification = mjschool_check_product_key( $domain_name, $licence_key, $email );
			}
			if ( ! empty( $licence_key ) && ! empty( $email ) && $check_varification === '0' ) {
				?>
				<div>
					<label class="mjschool_cursor_default_font_17px"><?php esc_html_e( 'License Verification Completed Successfully', 'mjschool' ); ?></label>
					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-checked.gif' ); ?>" class="calender_logo_image mjschool-textarea-height-60px">
				</div>
				<div class="mt-3">
					<label class="mjschool_color_setup_form"><i class="fas fa-exclamation-triangle text-black"></i> <?php esc_html_e( 'Important', 'mjschool' ); ?> : </label> <label class="mjschool_display_contents"><?php esc_html_e( 'If you want to transfer your license to a new domain, please reset this license. This action will deactivate your current license on this domain and allow you to register a new domain.', 'mjschool' ); ?>&nbsp;<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_setup&tab=reset' ) ); ?>"><?php esc_html_e( 'Reset now.', 'mjschool' ); ?></a></label>
				</div>
				<div class="mt-3">
					<label class="mjschool_color_setup_form"><i class="fas fa-exclamation-triangle text-black"></i> <?php esc_html_e( 'Important', 'mjschool' ); ?> : </label> <label class="mjschool_display_contents"><?php esc_html_e( 'If you want to transfer your license to a new domain, please reset this license. This action will deactivate your current license on this domain and allow you to register a new domain.', 'mjschool' ); ?></label>
				</div>
				<?php
			} else {
				$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
				?>
				<!------------------ License verification form. ---------------------->
				<form name="verification_form" action="" method="post" class="mjschool-form-horizontal" id="verification_form">
					<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_license_registration_nonce' ) ); ?>">
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'License Key Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!---------------- Form body. ------------------>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="server_name" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $server_name ); ?>" name="domain_name" readonly>
										<label for="server_name"><?php esc_html_e( 'Domain', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control licence_key">
										<input id="licence_key" class="form-control validate[required]" type="text" value="" name="licence_key">
										<label for="licence_key"><?php esc_html_e( 'Envato License key', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="enter_email" class="form-control validate[required,custom[email]]" type="text" value="" name="enter_email">
										<label for="enter_email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div><!---------------- Form body. ------------------>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="<?php esc_attr_e( 'Submit', 'mjschool' ); ?>" name="varify_key" id="varify_key_new" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form><!------------------ License verification form. ---------------------->
				<?php
			}
		}
		if ( $active_tab === 'reset' ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Session data is internally managed.
			$send_otp = isset( $_SESSION['send_otp'] ) ? sanitize_text_field( wp_unslash( $_SESSION['send_otp'] ) ) : '';
			if ( $send_otp === '1' ) {
				?>
				<form name="verify_otp_form" action="" method="post" class="mjschool-form-horizontal" id="verify_otp_form">
					<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_verify_otp_nonce' ) ); ?>">
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Verify OTP', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!---------------- Form body. ------------------>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="verify_otp" class="form-control validate[required]" type="text" value="" name="verify_otp">
										<label for="userinput1"><?php esc_html_e( 'Enter OTP', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<input type="submit" value="<?php esc_attr_e( 'Submit', 'mjschool' ); ?>" name="verify_reset_otp" id="verify_reset_otp" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form><!------------------ License verification form. ---------------------->
				<?php
			} else {
				?>
				<form name="reset_form" action="" method="post" class="mjschool-form-horizontal" id="reset_form">
					<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_license_reset_nonce' ) ); ?>">
					<div class="mt-3">
						<label class="mjschool_color_setup_form"><i class="fa fa-exclamation-triangle text-black"></i> <?php esc_html_e( 'Important', 'mjschool' ); ?> : </label> <label class="mjschool_display_contents"><?php esc_html_e( 'If you want to transfer your license to a new domain, please reset this license. This action will deactivate your current license on this domain and allow you to register a new domain.', 'mjschool' ); ?></label>
					</div>
					<div class="header">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'License Reset Information', 'mjschool' ); ?></h3>
					</div>
					<div class="form-body mjschool-user-form"><!---------------- Form body. ------------------>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control licence_key">
										<input id="licence_key" class="form-control validate[required]" type="text" value="" name="licence_key">
										<label for="userinput1"><?php esc_html_e( 'Enter Purchase Key', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="enter_email" class="form-control validate[required,custom[email]]" type="text" value="" name="enter_email">
										<label for="userinput1"><?php esc_html_e( 'Enter Registered Email', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div><!---------------- Form body. ------------------>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6">
								<input type="submit" value="<?php esc_attr_e( 'Submit', 'mjschool' ); ?>" name="reset_key" id="reset_key" class="btn btn-success mjschool-save-btn" />
							</div>
						</div>
					</div>
				</form><!------------------ License verification form. ---------------------->
				<?php
			}
		}
		?>
	</div><!------------------ Panel body. ------------------->
</div>