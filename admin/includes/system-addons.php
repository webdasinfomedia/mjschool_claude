<?php
/**
 * Add-ons and Integration Page.
 *
 * Displays available premium add-ons and integrations for the MjSchool plugin,
 * such as mobile applications and payment/SMS gateways. Each add-on card
 * provides a link to its respective marketplace page.
 *
 * This file also implements enhanced session management for improved security:
 * - Ensures HTTPS-only cookies.
 * - Prevents JavaScript access with the HttpOnly flag.
 * - Uses the 'Strict' SameSite policy to reduce CSRF risks.
 * - Regenerates the session ID on initialization to prevent fixation attacks.
 *
 * @package    MjSchool
 * @subpackage MjSchool/admin/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Secure session configuration.
if ( session_status() === PHP_SESSION_NONE ) {
	session_set_cookie_params(
		array(
			'lifetime' => 0,         // Session expires when the browser is closed.
			'path'     => '/',
			'domain'   => '',          // Set the domain if required.
			'secure'   => true,        // Send cookies only over HTTPS.
			'httponly' => true,      // Prevent JavaScript access to cookies.
			'samesite' => 'Strict',  // Mitigate CSRF risks.
		)
	);
	session_start();
}
// Regenerate session ID to enhance security.
if ( ! isset( $_SESSION['initialized'] ) ) {
	session_regenerate_id( true );
	$_SESSION['initialized'] = true;
}
// Generate a secure URL with session ID appended as a fallback (optional).
$url_with_sid = session_name() . '=' . session_id();
?>
<div class="mjschool-page-inner mjschool-min-height-1631"><!--Page inner div start.-->
	<div class="mjschool-main-list"><!--Main wrapper div start.-->
		<div class="row"><!--Row div start.-->
			<div class="col-md-12"><!-- COL 12 div start.-->
				<div class="mjschool-float-left-width-100px"><!--Panel white div start.-->
					<div class="row mjschool-addon-reponsive mjschool-addon-mtop">
						<div class="col-md-4">
							
							<div class="card mjschool-addon-card">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-app-addon.png' ); ?>" class="card-img-top">
								<div class="card-body mjschool-addon-card-body">
									<h5 class="mjschool-addon-card-title"><?php esc_html_e( 'School Master Mobile App for Android', 'mjschool' ); ?></h5>
									<a href="https://codecanyon.net/item/school-master-mobile-app-for-android/20806118?<?php echo esc_url( $url_with_sid ); ?>" target="_blank" class="btn addon-button btn-primary"><?php echo esc_html__( 'Get It Now', 'mjschool' ); ?></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mjschool-addon-card">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-iphone.png' ); ?>" class="card-img-top">
								<div class="card-body mjschool-addon-card-body">
									<h5 class="mjschool-addon-card-title"><?php esc_html_e( 'School Master Mobile App for iphone', 'mjschool' ); ?></h5>
									<a href="https://codecanyon.net/item/school-master-mobile-app-for-iphone/20792912?<?php echo esc_url( $url_with_sid ); ?>" target="_blank" class="btn addon-button btn-primary"><?php echo esc_html__( 'Get It Now', 'mjschool' ); ?></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mjschool-addon-card">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-paymaster-image.png' ); ?>" class="card-img-top">
								<div class="card-body mjschool-addon-card-body">
									<h5 class="mjschool-addon-card-title"><?php esc_html_e( 'Paymaster - Multipurpose Payment Gateway', 'mjschool' ); ?></h5>
									<a href="https://codecanyon.net/item/paymaster-multipurpose-payment-gateway/19693579?<?php echo esc_url( $url_with_sid ); ?>" target="_blank" class="btn addon-button btn-primary"><?php echo esc_html__( 'Get It Now', 'mjschool' ); ?></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mjschool-addon-card">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-sms-master.png' ); ?>" class="card-img-top">
								<div class="card-body mjschool-addon-card-body">
									<h5 class="mjschool-addon-card-title"><?php esc_html_e( 'SMSmaster – Multipurpose SMS Gateway for WordPress', 'mjschool' ); ?></h5>
									<a href="https://codecanyon.net/item/smsmaster-multipurpose-sms-gateway-for-WordPress/20605853?<?php echo esc_url( $url_with_sid ); ?>" target="_blank" class="btn addon-button btn-primary"><?php echo esc_html__( 'Get It Now', 'mjschool' ); ?></a>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card mjschool-addon-card">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/system-video-preview/mjschool-lms-addon.png' ); ?>" class="card-img-top">
								<div class="card-body mjschool-addon-card-body">
									<h5 class="mjschool-addon-card-title"><?php esc_html_e( 'WPLMS - Learning Management System Intigreted with WP-School', 'mjschool' ); ?></h5>
									<a href="https://codecanyon.net/item/wplms-learning-management-system-for-WordPress/15485895?<?php echo esc_url( $url_with_sid ); ?>" target="_blank" class="btn addon-button btn-primary"><?php echo esc_html__( 'Get It Now', 'mjschool' ); ?></a>
								</div>
							</div>
						</div>
					</div>
				</div><!--Panel white div end.-->
			</div><!--COL 12 div end.-->
		</div><!--Row div end.-->
	</div><!--Main wrapper div end.-->
</div><!--Page inner div end.-->
