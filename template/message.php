<?php
/**
 * Messaging System View/Controller.
 *
 * This file provides the core interface for the internal messaging system of the
 * 'mjschool' plugin, allowing users to compose, send, and view messages. It acts as
 * a controller for various message-related actions (compose, inbox, sentbox, view).
 *
 * Key features include:
 * - **View Switching:** Uses the 'tab' GET parameter to switch between 'inbox', 'sentbox', 'compose', and 'view_message'.
 * - **Validation:** Initializes jQuery Validation Engine for client-side validation of the compose form.
 * - **User/Class Selection:** Uses a jQuery Multiselect widget for selecting recipients by individual user or by class.
 * - **Nonce Verification:** Uses `wp_create_nonce` to secure tab links.
 * - **Unread Count:** Displays the count of unread messages in the inbox tab.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
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
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
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
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : 'inbox';
?>
<div class="row mailbox-header mjschool-frontend-list-margin-30px-res">
	<?php
	$tab_name = '';
	if ( ! empty( $_REQUEST['tab'] ) ) {
		$tab_name = sanitize_text_field(wp_unslash($_REQUEST['tab']));
	}
	?>
	<div class="col-md-12"><!--Col-md-12 mjschool-custom-padding-0.-->
        <?php $nonce = wp_create_nonce( 'mjschool_message_tab' ); ?>
		<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per list-unstyled mjschool-mailbox-nav">
			<li <?php if ( ! isset( $tab_name ) || ( $tab_name === 'inbox' ) ) { ?> class="active"<?php } ?>>
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=message&tab=inbox&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-inbox-tab"><i class="fas fa-inbox"></i> <?php esc_html_e( 'Inbox', 'mjschool' ); ?><span class="mjschool-inbox-count-number badge badge-success  pull-right ms-1 mjschool_border_redius_15px" ><?php echo esc_html( mjschool_count_unread_message( get_current_user_id() ) ); ?></span></a>
			</li>
			<li <?php if ( isset( $_REQUEST['page'] ) && $tab_name === 'sentbox' ) { ?> class="active" <?php } ?>>
				<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=message&tab=sentbox&_wpnonce=' . esc_attr( $nonce ) ); ?>" class="mjschool-padding-left-0 tab"><?php esc_html_e( 'Sent', 'mjschool' ); ?></a>
			</li>
			<li class="active">
				<?php
				if ( isset( $_REQUEST['page'] ) && $tab_name === 'compose' ) {
					?>
					<a href="#" class="mjschool-padding-left-0 tab"><?php esc_html_e( 'Compose', 'mjschool' ); ?></a>
					<?php
				}
				?>
			</li>
			<li class="active">
				<?php
				if ( isset( $_REQUEST['page'] ) && $tab_name === 'view_message' ) {
					?>
					<a href="#" class="mjschool-padding-left-0 tab"><?php esc_html_e( 'View Message', 'mjschool' ); ?></a>
					<?php
				}
				?>
			</li>
		</ul>
	</div><!--Col-md-12 mjschool-custom-padding-0.-->
	<?php
	if ( $active_tab === 'sentbox' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/template/sendbox.php';
	}
	if ( $active_tab === 'inbox' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/template/inbox.php';
	}
	if ( $active_tab === 'compose' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/template/compose-email.php';
	}
	if ( $active_tab === 'view_message' ) {
		require_once MJSCHOOL_PLUGIN_DIR . '/template/view-message.php';
	}
	?>
</div>