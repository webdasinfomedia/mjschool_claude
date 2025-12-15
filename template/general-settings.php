<?php
/**
 * Loads the General Settings administration page.
 *
 * Checks if the current admin request is specifically for the 'general-settings'
 * page (typically via $_REQUEST['page']). If the condition is met, it loads
 * the core logic and view for the General Settings from the plugin's admin
 * includes directory.
 * 
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === 'general-settings' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/general-settings.php';
}