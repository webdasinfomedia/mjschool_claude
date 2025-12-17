<?php
/**
 * Plugin Name:       MJ School
 * Plugin URI:        https://mojoomla.com/wordpress-plugins/mj-school
 * Description:       Streamline your school's operations with the School Management System Plugin for WordPress. From student enrollment and attendance to schedules, assignments, exams, and fees — this all-in-one plugin simplifies school management. Enhance communication between staff, students, and parents, ensuring smooth and efficient operations(16-12-2025).
 * Version:           2.0.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Tested up to:      6.7
 * Author:            Mojoomla
 * Author URI:        https://codecanyon.net/user/mojoomla
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mjschool
 * Domain Path:       /languages
 * Network:           false
 * 
 * @package           MJSchool
 * @since             1.0.0
 * @copyright         2020-2025 Mojoomla
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Check minimum requirements before loading plugin
 */

// Check PHP version
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	add_action( 'admin_notices', 'mjschool_php_version_notice' );
	return; // Stop plugin execution
}

// Check WordPress version  
if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
	add_action( 'admin_notices', 'mjschool_wp_version_notice' );
	return; // Stop plugin execution
}

/**
 * Display PHP version notice
 * 
 * @since 2.0.1
 */
function mjschool_php_version_notice() {
	printf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'MJ School:', 'mjschool' ),
		sprintf(
			/* translators: 1: Current PHP version, 2: Required PHP version */
			esc_html__( 'This plugin requires PHP version %2$s or higher. You are running version %1$s. Please contact your hosting provider to upgrade PHP.', 'mjschool' ),
			PHP_VERSION,
			'7.4'
		)
	);
}

/**
 * Display WordPress version notice
 * 
 * @since 2.0.1
 */
function mjschool_wp_version_notice() {
	printf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'MJ School:', 'mjschool' ),
		sprintf(
			/* translators: 1: Current WP version, 2: Required WP version */
			esc_html__( 'This plugin requires WordPress version %2$s or higher. You are running version %1$s. Please upgrade WordPress.', 'mjschool' ),
			get_bloginfo( 'version' ),
			'5.8'
		)
	);
}

/**
 * Define plugin constants
 */

// Plugin version
if ( ! defined( 'MJSCHOOL_VERSION' ) ) {
	define( 'MJSCHOOL_VERSION', '2.0.1' );
}

// Plugin base name
if ( ! defined( 'MJSCHOOL_PLUGIN_BASENAME' ) ) {
	define( 'MJSCHOOL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

// Plugin directory path
if ( ! defined( 'MJSCHOOL_PLUGIN_DIR' ) ) {
	define( 'MJSCHOOL_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Plugin URL
if ( ! defined( 'MJSCHOOL_PLUGIN_URL' ) ) {
	define( 'MJSCHOOL_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
}

// Content URL
if ( ! defined( 'MJSCHOOL_CONTENT_URL' ) ) {
	define( 'MJSCHOOL_CONTENT_URL', content_url() );
}

// Includes directory
if ( ! defined( 'MJSCHOOL_INCLUDES_DIR' ) ) {
	define( 'MJSCHOOL_INCLUDES_DIR', MJSCHOOL_PLUGIN_DIR . '/includes' );
}

// Admin directory
if ( ! defined( 'MJSCHOOL_ADMIN_DIR' ) ) {
	define( 'MJSCHOOL_ADMIN_DIR', MJSCHOOL_PLUGIN_DIR . '/admin/includes' );
}

// Assets URL
if ( ! defined( 'MJSCHOOL_ASSETS_URL' ) ) {
	define( 'MJSCHOOL_ASSETS_URL', MJSCHOOL_PLUGIN_URL . '/assets' );
}

// Template directory
if ( ! defined( 'MJSCHOOL_TEMPLATE_DIR' ) ) {
	define( 'MJSCHOOL_TEMPLATE_DIR', MJSCHOOL_PLUGIN_DIR . '/template' );
}

// No data image
if ( ! defined( 'MJSCHOOL_NODATA_IMG' ) ) {
	define( 'MJSCHOOL_NODATA_IMG', MJSCHOOL_ASSETS_URL . '/images/dashboard-icon/mjschool-nodata.png' );
}

// Database table prefix
if ( ! defined( 'MJSCHOOL_DB_PREFIX' ) ) {
	global $wpdb;
	define( 'MJSCHOOL_DB_PREFIX', $wpdb->prefix . 'mjschool_' );
}

// Debug mode
if ( ! defined( 'MJSCHOOL_DEBUG' ) ) {
	define( 'MJSCHOOL_DEBUG', defined( 'WP_DEBUG' ) && WP_DEBUG );
}

/**
 * Load critical plugin files with error handling
 * 
 * @since 2.0.1
 * @return bool True if all files loaded successfully, false otherwise
 */
function mjschool_load_core_files() {
	$critical_files = array(
		MJSCHOOL_PLUGIN_DIR . '/mjschool-settings.php',
		MJSCHOOL_PLUGIN_DIR . '/api/school-api-files.php',
	);

	foreach ( $critical_files as $file ) {
		if ( ! file_exists( $file ) ) {
			add_action( 'admin_notices', function() use ( $file ) {
				printf(
					'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
					esc_html__( 'MJ School:', 'mjschool' ),
					sprintf(
						/* translators: %s: file name */
						esc_html__( 'Critical file missing: %s. Please reinstall the plugin.', 'mjschool' ),
						esc_html( basename( $file ) )
					)
				);
			} );
			
			// Log the error
			mjschool_log( 'Critical file missing: ' . $file, 'error' );
			
			return false;
		}
		require_once $file;
	}
	return true;
}

// Load core files
if ( ! mjschool_load_core_files() ) {
	return; // Stop plugin execution if critical files are missing
}

/**
 * Autoload classes
 * 
 * @since 2.0.1
 * @param string $class Class name
 */
spl_autoload_register( function( $class ) {
    // Only handle MJSchool classes
    if ( stripos( $class, 'mjschool' ) !== 0 ) {
        return;
    }
    
    // Convert class name to filename
    // Example: Mjschool_Attendence_Manage → class-mjschool-attendence-manage.php
    $class_lower = strtolower( $class );
    $filename = 'class-' . str_replace( '_', '-', $class_lower ) . '.php';
    
    // Try multiple possible directories
    $possible_paths = array(
        MJSCHOOL_INCLUDES_DIR . '/' . $filename,
        MJSCHOOL_ADMIN_DIR . '/' . $filename,
        MJSCHOOL_PLUGIN_DIR . '/lib/' . $filename,
    );
    
    // Try to load from each path
    foreach ( $possible_paths as $path ) {
        if ( file_exists( $path ) ) {
            require_once $path;
            
            // Verify the class actually exists after loading
            if ( class_exists( $class, false ) ) {
                // Success!
                return;
            } else {
                // File loaded but class not found - possible class name mismatch
                if ( defined( 'MJSCHOOL_DEBUG' ) && MJSCHOOL_DEBUG ) {
                    error_log( sprintf(
                        '[MJSchool] WARNING: File loaded but class "%s" not found in %s',
                        $class,
                        $path
                    ) );
                }
            }
        }
    }
    
    // Class file not found anywhere
    if ( defined( 'MJSCHOOL_DEBUG' ) && MJSCHOOL_DEBUG ) {
        error_log( sprintf(
            '[MJSchool] ERROR: Class file not found for "%s". Expected filename: %s',
            $class,
            $filename
        ) );
        error_log( '[MJSchool] Searched in: ' . print_r( $possible_paths, true ) );
    }
} );

/**
 * Initialize plugin
 * 
 * This runs after all plugins are loaded to ensure compatibility
 * 
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'mjschool_init_plugin', 10 );
function mjschool_init_plugin() {
	// Load text domain for translations
	load_plugin_textdomain( 
		'mjschool', 
		false, 
		dirname( MJSCHOOL_PLUGIN_BASENAME ) . '/languages' 
	);
	
	// Fire init action for other components to hook into
	do_action( 'mjschool_init' );
	
	// Log successful initialization in debug mode
	mjschool_log( 'Plugin initialized successfully', 'info' );
}

/**
 * Plugin activation hook
 * 
 * IMPORTANT: This MUST be in the main plugin file (mjschool.php)
 * Do NOT move this to mjschool-settings.php - it won't work!
 * 
 * The activation hook must be registered in the main plugin file because:
 * 1. WordPress only recognizes hooks from the primary plugin file
 * 2. The __FILE__ constant must point to the main plugin file
 * 3. Moving it elsewhere will prevent proper activation
 * 
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'mjschool_activate_plugin' );
function mjschool_activate_plugin() {
	// Double-check requirements on activation
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( MJSCHOOL_PLUGIN_BASENAME );
		wp_die( 
			esc_html__( 'MJ School requires PHP 7.4 or higher. Please upgrade PHP before activating this plugin.', 'mjschool' ),
			esc_html__( 'Plugin Activation Error', 'mjschool' ),
			array( 'back_link' => true )
		);
	}
	
	if ( version_compare( get_bloginfo( 'version' ), '5.8', '<' ) ) {
		deactivate_plugins( MJSCHOOL_PLUGIN_BASENAME );
		wp_die( 
			esc_html__( 'MJ School requires WordPress 5.8 or higher. Please upgrade WordPress before activating this plugin.', 'mjschool' ),
			esc_html__( 'Plugin Activation Error', 'mjschool' ),
			array( 'back_link' => true )
		);
	}
	
	// Call setup function from settings file if it exists
	// This function should handle:
	// - Database table creation
	// - Default options setup
	// - Directory creation
	// - Scheduled tasks setup
	if ( function_exists( 'mjschool_activate' ) ) {
		mjschool_activate();
	}
	
	// Store plugin version for future reference (useful for updates)
	update_option( 'mjschool_version', MJSCHOOL_VERSION );
	
	// Store activation timestamp (useful for analytics/tracking)
	if ( ! get_option( 'mjschool_activated_time' ) ) {
		update_option( 'mjschool_activated_time', time() );
	}
	
	// Flush rewrite rules to ensure custom post types/taxonomies work
	flush_rewrite_rules();
	
	// Log activation
	mjschool_log( 'Plugin activated - Version: ' . MJSCHOOL_VERSION, 'info' );
}

/**
 * Plugin deactivation hook
 * 
 * IMPORTANT: This MUST be in the main plugin file (mjschool.php)
 * 
 * Performs cleanup when plugin is deactivated.
 * Note: This should NOT delete user data or database tables.
 * Data deletion should only happen on uninstall (via uninstall.php)
 * 
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'mjschool_deactivate_plugin' );
function mjschool_deactivate_plugin() {
	// Call cleanup function from settings file if it exists
	// This function should handle:
	// - Clearing scheduled cron jobs
	// - Deleting transients
	// - Clearing caches
	// - Other temporary cleanup
	// BUT NOT deleting user data or database tables!
	if ( function_exists( 'mjschool_deactivate' ) ) {
		mjschool_deactivate();
	}
	
	// Flush rewrite rules
	flush_rewrite_rules();
	
	// Log deactivation
	mjschool_log( 'Plugin deactivated', 'info' );
}

/**
 * Load admin-specific functionality
 * 
 * Only loads admin files when in admin context to improve frontend performance
 * 
 * @since 1.0.0
 */
if ( is_admin() ) {
	add_action( 'admin_init', 'mjschool_load_admin_pages', 10 );
}

/**
 * Load admin pages based on current page
 * 
 * Only loads files when necessary, with proper security checks.
 * This prevents unnecessary file loading and potential security issues.
 * 
 * Special pages like 'mjschool-callback' and 'mjschool-notify' are loaded here
 * with proper capability and nonce checks.
 * 
 * @since 2.0.1
 */
function mjschool_load_admin_pages() {
	// Check if user has minimum permission to access admin
	if ( ! current_user_can( 'read' ) ) {
		return;
	}
	
	// Get current admin page
	$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
	
	// Only load specific callback/notify pages if user has proper permissions
	if ( ! empty( $page ) ) {
		switch ( $page ) {
			case 'mjschool-callback':
				// Verify user can manage options (typically admin only)
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 
						esc_html__( 'You do not have permission to access this page.', 'mjschool' ),
						esc_html__( 'Unauthorized Access', 'mjschool' ),
						array( 'response' => 403 )
					);
				}
				
				// Verify nonce if there's an action
				if ( isset( $_GET['action'] ) ) {
					if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'mjschool_callback_action' ) ) {
						wp_die( 
							esc_html__( 'Security check failed. Please try again.', 'mjschool' ),
							esc_html__( 'Security Error', 'mjschool' ),
							array( 'response' => 403 )
						);
					}
				}
				
				$callback_file = MJSCHOOL_INCLUDES_DIR . '/mjschool-callback.php';
				if ( file_exists( $callback_file ) ) {
					require_once $callback_file;
				} else {
					mjschool_log( 'Callback file not found: ' . $callback_file, 'error' );
					wp_die( 
						esc_html__( 'Required file is missing. Please reinstall the plugin.', 'mjschool' ),
						esc_html__( 'File Not Found', 'mjschool' ),
						array( 'response' => 500 )
					);
				}
				break;
				
			case 'mjschool-notify':
				// Verify user can manage options (typically admin only)
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_die( 
						esc_html__( 'You do not have permission to access this page.', 'mjschool' ),
						esc_html__( 'Unauthorized Access', 'mjschool' ),
						array( 'response' => 403 )
					);
				}
				
				$notify_file = MJSCHOOL_INCLUDES_DIR . '/mjschool-notify.php';
				if ( file_exists( $notify_file ) ) {
					require_once $notify_file;
				} else {
					mjschool_log( 'Notify file not found: ' . $notify_file, 'error' );
					wp_die( 
						esc_html__( 'Required file is missing. Please reinstall the plugin.', 'mjschool' ),
						esc_html__( 'File Not Found', 'mjschool' ),
						array( 'response' => 500 )
					);
				}
				break;
		}
	}
}

/**
 * Log errors when debug mode is enabled
 * 
 * Provides consistent logging across the plugin.
 * Only logs when MJSCHOOL_DEBUG (or WP_DEBUG) is true.
 * 
 * This is used throughout the plugin for debugging purposes.
 * Example usage: mjschool_log( 'Error message here', 'error' );
 * 
 * @since 2.0.1
 * @param string $message Error message to log
 * @param string $level   Error level (error, warning, info, debug). Default 'error'
 */
function mjschool_log( $message, $level = 'error' ) {
	// Check if debug mode is enabled
	if ( ! MJSCHOOL_DEBUG ) {
		return;
	}
	
	// Check if WP_DEBUG_LOG is enabled
	if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
		return;
	}
	
	// Log the message with timestamp
	if ( function_exists( 'error_log' ) ) {
		$log_message = sprintf( 
			'[MJSchool %s] %s', 
			strtoupper( $level ), 
			$message 
		);
		error_log( $log_message );
	}
}

/**
 * Check if plugin is network activated
 * 
 * Useful for multisite installations to determine if plugin
 * is activated network-wide or per-site.
 * 
 * @since 2.0.1
 * @return bool True if network activated, false otherwise
 */
function mjschool_is_network_activated() {
	if ( ! is_multisite() ) {
		return false;
	}
	
	$plugins = get_site_option( 'active_sitewide_plugins' );
	return isset( $plugins[ MJSCHOOL_PLUGIN_BASENAME ] );
}

/**
 * Safe redirect with fallback
 * 
 * Wrapper for wp_safe_redirect() with additional validation.
 * Use this instead of wp_redirect() for security.
 * 
 * Example usage:
 * mjschool_safe_redirect( admin_url( 'admin.php?page=mjschool' ) );
 * 
 * @since 2.0.1
 * @param string $location URL to redirect to
 * @param int    $status   HTTP status code (default 302 for temporary redirect)
 */
function mjschool_safe_redirect( $location, $status = 302 ) {
	// Ensure we have a valid URL
	if ( empty( $location ) ) {
		mjschool_log( 'Attempted redirect with empty location', 'warning' );
		return;
	}
	
	// Use WordPress safe redirect (validates URL)
	wp_safe_redirect( $location, $status );
	exit;
}

/**
 * Get plugin data
 * 
 * Retrieves plugin information from the plugin header.
 * Useful for displaying version, author, etc. in admin pages.
 * 
 * @since 2.0.1
 * @param string $field Optional. Specific field to retrieve (Name, Version, Author, etc.)
 * @return mixed Plugin data array or specific field value
 */
function mjschool_get_plugin_data( $field = '' ) {
	// Load plugin.php if not already loaded
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	
	$plugin_data = get_plugin_data( __FILE__ );
	
	// Return specific field if requested
	if ( ! empty( $field ) && isset( $plugin_data[ $field ] ) ) {
		return $plugin_data[ $field ];
	}
	
	// Return all data
	return $plugin_data;
}

/**
 * Check if current request is AJAX
 * 
 * @since 2.0.1
 * @return bool True if AJAX request, false otherwise
 */
function mjschool_is_ajax() {
	return defined( 'DOING_AJAX' ) && DOING_AJAX;
}

/**
 * Check if current request is REST API
 * 
 * @since 2.0.1
 * @return bool True if REST API request, false otherwise
 */
function mjschool_is_rest() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Check if current request is cron
 * 
 * @since 2.0.1
 * @return bool True if cron request, false otherwise
 */
function mjschool_is_cron() {
	return defined( 'DOING_CRON' ) && DOING_CRON;
}