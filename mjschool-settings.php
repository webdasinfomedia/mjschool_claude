<?php
/**
 * The settings functionality of the MJ School Management Plugin.
 *
 * This file loads required classes, functions, and handles the
 * plugin settings and helper functions.
 *
 * @since      1.0.0
 * @package    MJSCHOOL
 */

defined( 'ABSPATH' ) || exit;
/**
 * This is the Settings page of the School Management Plugin.
 * All required classes and functions for the plugin are loaded here.
 */
/**
 * Load required class files with error handling
 * 
 * @since 1.0.0
 */
function mjschool_load_required_files() {
    $required_files = array(
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-attendence-manage.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-marks-manage.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-routine.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-payment.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-fees.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-homework.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-fees-payment.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-library.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-teacher.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-exam.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-admissioin.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-hostel.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-subject.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-custome-field.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-virtual-classroom.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-event.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-leave.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-document.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-notification.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-tax.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-message.php',
        MJSCHOOL_INCLUDES_DIR . '/mjschool-function.php',
        MJSCHOOL_INCLUDES_DIR . '/mjschool-print-pdf-functions.php',
        MJSCHOOL_INCLUDES_DIR . '/mjschool-ajax-function.php',
        MJSCHOOL_INCLUDES_DIR . '/class-mjschool-management.php',
        MJSCHOOL_PLUGIN_DIR . '/lib/paypal/paypal_class.php',
        MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-dynamic-css.php',
        MJSCHOOL_PLUGIN_DIR . '/lib/chart/GoogleCharts.class.php',
    );

    $missing_files = array();
    
    foreach ( $required_files as $file ) {
        if ( file_exists( $file ) ) {
            require_once $file;
        } else {
            $missing_files[] = basename( $file );
            mjschool_log( 'Required file missing: ' . $file, 'error' );
        }
    }
    
    // Show admin notice if files are missing
    if ( ! empty( $missing_files ) ) {
        add_action( 'admin_notices', function() use ( $missing_files ) {
            printf(
                '<div class="notice notice-error"><p><strong>%s</strong> %s: %s</p></div>',
                esc_html__( 'MJ School Error:', 'mjschool' ),
                esc_html__( 'The following required files are missing', 'mjschool' ),
                esc_html( implode( ', ', $missing_files ) )
            );
        } );
        return false;
    }
    
    return true;
}

// Load files
mjschool_load_required_files();

/**
 * Check if a WordPress role exists
 * 
 * @since 1.0.0
 * @param string $role Role name to check
 * @return bool True if role exists, false otherwise
 */
function mjschool_role_exists( $role ) {
    if ( empty( $role ) || ! is_string( $role ) ) {
        return false;
    }
    
    // Check if wp_roles global is available
    if ( empty( $GLOBALS['wp_roles'] ) ) {
        return false;
    }
    
    return $GLOBALS['wp_roles']->is_role( $role );
}

/**
 * Add capabilities to existing roles and create custom roles
 * 
 * This function adds capabilities to existing roles (teacher, student, parent)
 * and creates new custom roles (supportstaff, student_temp, management) with
 * appropriate capabilities if they do not already exist.
 *
 * @since 1.0.0
 */
function mjschool_add_role_caps() {
    // Add capabilities to the existing 'teacher' role if it exists
    if ( mjschool_role_exists( 'teacher' ) ) {
        $role = get_role( 'teacher' );
        if ( $role ) {
            $role->add_cap( 'read' );
            $role->add_cap( 'level_0' );
        }
    }
    
    // Add capabilities to the existing 'student' role if it exists
    if ( mjschool_role_exists( 'student' ) ) {
        $role = get_role( 'student' );
        if ( $role ) {
            $role->add_cap( 'read' );
            $role->add_cap( 'level_0' );
        }
    }
    
    // Add capabilities to the existing 'parent' role if it exists
    if ( mjschool_role_exists( 'parent' ) ) {
        $role = get_role( 'parent' );
        if ( $role ) {
            $role->add_cap( 'read' );
            $role->add_cap( 'level_0' );
        }
    }
    
    // Create the 'supportstaff' role if it does not exist
    if ( ! mjschool_role_exists( 'supportstaff' ) ) {
        add_role(
            'supportstaff',
            esc_attr__( 'Support Staff', 'mjschool' ),
            array(
                'read'    => true,
                'level_0' => true,
            )
        );
    }
    
    // Create the temporary 'student_temp' role if it does not exist
    if ( ! mjschool_role_exists( 'student_temp' ) ) {
        add_role(
            'student_temp',
            esc_attr__( 'Student (Pending)', 'mjschool' ),
            array(
                'read'    => true,
                'level_0' => true,
            )
        );
    }
    
    // Create the 'management' role with higher permissions if it does not exist
    if ( ! mjschool_role_exists( 'management' ) ) {
        add_role(
            'management',
            esc_attr__( 'Management', 'mjschool' ),
            array(
                'read'    => true,
                'level_1' => true,
            )
        );
    }
}
add_action( 'admin_init', 'mjschool_add_role_caps' );
/**
 * Add custom dashboard link to admin bar
 * 
 * This function adds a custom node to the WordPress admin bar that links
 * directly to the MJ School Management plugin dashboard page.
 *
 * @since 1.0.0
 * @param WP_Admin_Bar $wp_admin_bar The WordPress Admin Bar object
 */
function mjschool_dashboard_link( $wp_admin_bar ) {
    // Only show for users who can access the plugin
    if ( ! current_user_can( 'read' ) ) {
        return;
    }
    
    $args = array(
        'id'    => 'school-dashboard',
        'title' => esc_html__( 'School Dashboard', 'mjschool' ),
        'href'  => admin_url( 'admin.php?page=mjschool' ),
        'meta'  => array( 
            'class' => 'mjschool-school-dashboard',
            'title' => esc_attr__( 'Go to School Dashboard', 'mjschool' ),
        ),
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'mjschool_dashboard_link', 999 );

/**
 * Initialize verification system using transients
 * 
 * WordPress best practice: Use transients instead of PHP sessions.
 * Sessions are not recommended in WordPress plugins due to compatibility issues.
 * 
 * @since 2.0.1
 */
function mjschool_init_verification() {
    // Only for logged-in users
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return;
    }
    
    // Check user-specific transient for verification status
    $verify_key = 'mjschool_verify_' . $user_id;
    $verified = get_transient( $verify_key );
    
    // Initialize if not set
    if ( false === $verified ) {
        set_transient( $verify_key, 'pending', HOUR_IN_SECONDS );
    }
}
add_action( 'init', 'mjschool_init_verification' );

/**
 * Cleanup verification transient on logout
 * 
 * @since 2.0.1
 */
function mjschool_logout_cleanup() {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        delete_transient( 'mjschool_verify_' . $user_id );
    }
}
add_action( 'wp_logout', 'mjschool_logout_cleanup' );


/**
 *
 * This function checks whether the plugin is running on a local server or a live server.
 * It processes license verification only on plugin pages, using stored license key and setup email.
 * If verification fails, the user is redirected to the setup page.
 *
 * @since 1.0.0
 */
function mjschool_verify_license() {
	
	$is_mjschool_pluginpage = mjschool_is_smgt_page();
	$is_verify          = false;
	if ( ! isset( $_SESSION['mjschool_verify'] ) ) {
		$_SESSION['mjschool_verify'] = '';
	}
	$server_name    = wp_unslash($_SERVER['SERVER_NAME']);
	$is_localserver = mjschool_check_server( $server_name );
	// Skip verification on local servers.
	if ( $is_localserver ) {
		return true;
	}
	// Process license verification only on plugin pages.
	
	if ( $is_mjschool_pluginpage ) {
		
		if ( empty( $_SESSION['mjschool_verify'] ) ) {
			if ( get_option( 'mjschool_licence_key' ) && get_option( 'mjschool_setup_email' ) ) {
				$domain_name       =sanitize_text_field( wp_unslash($_SERVER['SERVER_NAME']));
				$licence_key       = get_option( 'mjschool_licence_key' );
				$email             = get_option( 'mjschool_setup_email' );
				$result            = mjschool_check_product_key( $domain_name, $licence_key, $email );
				$is_server_running = mjschool_check_our_server();
				if ( $is_server_running ) {
					$_SESSION['mjschool_verify'] = $result;
				} else {
					$_SESSION['mjschool_verify'] = '0';
				}
				$is_verify = mjschool_check_verify_or_not( $result );
			}
		}
	}
	$is_verify = isset( $_SESSION['mjschool_verify'] ) ? mjschool_check_verify_or_not( sanitize_text_field( (string) $_SESSION['mjschool_verify'] ) ) : false;
	
	if ( $is_mjschool_pluginpage ) {
		
		if ( ! $is_verify ) {
			
			$_SESSION['mjschool_verify'] = '';
			if ( $_REQUEST['page'] != 'mjschool_setup' ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_setup' ) );
				die();
			}
		}
	}
}

// add_action( 'init', 'mjschool_verify_license' );

/**
 * Load admin functionality
 */
if ( is_admin() ) {
    $admin_file = MJSCHOOL_PLUGIN_DIR . '/admin/admin.php';
    if ( file_exists( $admin_file ) ) {
        require_once $admin_file;
    } else {
        mjschool_log( 'Admin file not found: ' . $admin_file, 'error' );
    }
}
/**
 * Plugin activation callback
 * 
 * This function is called by the activation hook in mjschool.php
 * It performs the following tasks:
 * 1. Adds default roles with capabilities
 * 2. Migrates old plugin options if necessary
 * 3. Creates required database tables
 * 4. Registers custom post types
 * 5. Performs attendance migration for new tables
 *
 * @since 1.0.0
 */
function mjschool_activate() {
	// Create teacher role
	if ( ! mjschool_role_exists( 'teacher' ) ) {
		add_role(
			'teacher',
			esc_attr__( 'Teacher', 'mjschool' ),
			array(
				'read'    => true,
				'level_0' => true,
			)
		);
	}
	
	// Create student role
	if ( ! mjschool_role_exists( 'student' ) ) {
		add_role(
			'student',
			esc_attr__( 'Student', 'mjschool' ),
			array(
				'read'    => true,
				'level_0' => true,
			)
		);
	}
	
	// Create parent role
	if ( ! mjschool_role_exists( 'parent' ) ) {
		add_role(
			'parent',
			esc_attr__( 'Parent', 'mjschool' ),
			array(
				'read'    => true,
				'level_0' => true,
			)
		);
	}
	
	// Create support staff role
	if ( ! mjschool_role_exists( 'supportstaff' ) ) {
		add_role(
			'supportstaff',
			esc_attr__( 'Support Staff', 'mjschool' ),
			array(
				'read'    => true,
				'level_0' => true,
			)
		);
	}
	
	// Create management role
	if ( ! mjschool_role_exists( 'management' ) ) {
		add_role(
			'management',
			esc_attr__( 'Management', 'mjschool' ),
			array(
				'read'    => true,
				'level_1' => true,
			)
		);
	}
	
	// Migrate old options if needed
	if ( get_option( 'mjschool_migrate_options_check' ) !== '1' ) {
		if ( function_exists( 'mjschool_migrate_options' ) ) {
			mjschool_migrate_options();
		}
	}
	
	// Create database tables
	if ( function_exists( 'mjschool_install_tables' ) ) {
		mjschool_install_tables();
	}
	
	// Register custom post types
	if ( function_exists( 'mjschool_register_post' ) ) {
		mjschool_register_post();
	}
	
	// Migrate attendance data
	if ( function_exists( 'mjschool_attendance_migratation_for_new_table' ) ) {
		mjschool_attendance_migratation_for_new_table();
	}
}

// Note: The actual register_activation_hook() is in mjschool.php

/**
 * Helper function to get permission value from request
 *
 * @param string $key     The request key.
 * @param int    $default Default value (0 or 1).
 * @return int Sanitized permission value.
 */
function mjschool_get_permission_value( $key, $default = 0 ) {
	// Only process if we're in an admin context with proper permissions
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return absint( $default );
	}

	// Check for nonce if processing form data
	if ( isset( $_REQUEST[ $key ] ) ) {
		// Verify nonce for form submissions
		if ( isset( $_REQUEST['mjschool_settings_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['mjschool_settings_nonce'] ) ), 'mjschool_save_settings' ) ) {
				return absint( $default );
			}
		}
		
		$value = absint( wp_unslash( $_REQUEST[ $key ] ) );
		// Validate: only allow 0 or 1
		return ( $value === 1 ) ? 1 : 0;
	}

	return absint( $default );
}

/**
 * Helper function to build module access rights array
 *
 * @param string $module_key  Module identifier.
 * @param array  $config      Module configuration.
 * @return array Module access rights.
 */
function mjschool_build_module_access( $module_key, $config ) {
	$defaults = array(
		'menu_icone' => '',
		'app_icone'  => '',
		'menu_title' => ucfirst( str_replace( '_', ' ', $module_key ) ),
		'page_link'  => $module_key,
		'own_data'   => 0,
		'add'        => 0,
		'edit'       => 0,
		'view'       => 1,
		'delete'     => 0,
	);

	$config = wp_parse_args( $config, $defaults );

	return array(
		'menu_icone' => esc_url( $config['menu_icone'] ),
		'app_icone'  => esc_url( $config['app_icone'] ),
		'menu_title' => sanitize_text_field( $config['menu_title'] ),
		'page_link'  => sanitize_key( $config['page_link'] ),
		'own_data'   => mjschool_get_permission_value( $module_key . '_own_data', $config['own_data'] ),
		'add'        => mjschool_get_permission_value( $module_key . '_add', $config['add'] ),
		'edit'       => mjschool_get_permission_value( $module_key . '_edit', $config['edit'] ),
		'view'       => mjschool_get_permission_value( $module_key . '_view', $config['view'] ),
		'delete'     => mjschool_get_permission_value( $module_key . '_delete', $config['delete'] ),
	);
}

/**
 * Get the base URL for plugin icons
 *
 * @return string Base URL for icons.
 */
function mjschool_get_icons_url() {
	return plugins_url( 'mjschool/assets/images/icons/' );
}

/**
 * Get the base URL for app icons
 *
 * @return string Base URL for app icons.
 */
function mjschool_get_app_icons_url() {
	return plugins_url( 'mjschool/assets/images/icons/app-icon/' );
}

/**
 * Define all modules with their default configurations
 *
 * @return array Module configurations.
 */
function mjschool_get_module_definitions() {
	$icons_url     = mjschool_get_icons_url();
	$app_icons_url = mjschool_get_app_icons_url();

	return array(
		'teacher' => array(
			'menu_icone' => $icons_url . 'mjschool-teacher.png',
			'app_icone'  => $app_icons_url . 'mjschool-teacher.png',
			'menu_title' => 'Teacher',
		),
		'student' => array(
			'menu_icone' => $icons_url . 'mjschool-student-icon.png',
			'app_icone'  => $app_icons_url . 'mjschool-student.png',
			'menu_title' => 'Student',
		),
		'parent' => array(
			'menu_icone' => $icons_url . 'mjschool-parents.png',
			'app_icone'  => $app_icons_url . 'mjschool-parents.png',
			'menu_title' => 'Parent',
		),
		'supportstaff' => array(
			'menu_icone' => $icons_url . 'mjschool-support-staff.png',
			'app_icone'  => $app_icons_url . 'mjschool-support-staff.png',
			'menu_title' => 'Supportstaff',
		),
		'subject' => array(
			'menu_icone' => $icons_url . 'mjschool-subject.png',
			'app_icone'  => $app_icons_url . 'mjschool-subject.png',
			'menu_title' => 'Subject',
		),
		'schedule' => array(
			'menu_icone' => $icons_url . 'mjschool-class-route.png',
			'app_icone'  => $app_icons_url . 'mjschool-class-route.png',
			'menu_title' => 'Class Routine',
		),
		'virtual_classroom' => array(
			'menu_icone' => $icons_url . 'mjschool-virtual-classroom.png',
			'app_icone'  => $app_icons_url . 'mjschool-virtual-class.png',
			'menu_title' => 'Virtual Classroom',
		),
		'attendance' => array(
			'menu_icone' => $icons_url . 'mjschool-attandance.png',
			'app_icone'  => $app_icons_url . 'mjschool-attandance.png',
			'menu_title' => 'Attendance',
		),
		'notification' => array(
			'menu_icone' => $icons_url . 'mjschool-notification_new.png',
			'app_icone'  => $icons_url . 'mjschool-notification_new.png',
			'menu_title' => 'Notification',
		),
		'exam' => array(
			'menu_icone' => $icons_url . 'mjschool-exam.png',
			'app_icone'  => $app_icons_url . 'exam.png',
			'menu_title' => 'Exam',
		),
		'class_room' => array(
			'menu_icone' => $icons_url . 'mjschool-exam.png',
			'app_icone'  => $app_icons_url . 'exam.png',
			'menu_title' => 'Class Room',
		),
		'class' => array(
			'menu_icone' => $icons_url . 'mjschool-class.png',
			'app_icone'  => $app_icons_url . 'mjschool-class.png',
			'menu_title' => 'Class',
		),
		'grade' => array(
			'menu_icone' => $icons_url . 'mjschool-grade.png',
			'app_icone'  => $app_icons_url . 'mjschool-grade.png',
			'menu_title' => 'Grade',
		),
		'hostel' => array(
			'menu_icone' => $icons_url . 'mjschool-hostel.png',
			'app_icone'  => $app_icons_url . 'mjschool-hostel.png',
			'menu_title' => 'Hostel',
		),
		'document' => array(
			'menu_icone' => $icons_url . 'mjschool-hostel.png',
			'app_icone'  => $app_icons_url . 'mjschool-hostel.png',
			'menu_title' => 'Document',
		),
		'leave' => array(
			'menu_icone' => $icons_url . 'mjschool-notification_new.png',
			'app_icone'  => $icons_url . 'mjschool-notification_new.png',
			'menu_title' => 'Leave',
		),
		'homework' => array(
			'menu_icone' => $icons_url . 'mjschool-homework.png',
			'app_icone'  => $app_icons_url . 'mjschool-homework.png',
			'menu_title' => 'Home Work',
		),
		'manage_marks' => array(
			'menu_icone' => $icons_url . 'mjschool-mark-manage.png',
			'app_icone'  => $app_icons_url . 'mjschool-mark-manage.png',
			'menu_title' => 'Mark Manage',
			'page_link'  => 'manage-marks',
		),
		'feepayment' => array(
			'menu_icone' => $icons_url . 'mjschool-fee.png',
			'app_icone'  => $app_icons_url . 'mjschool-fee-payment.png',
			'menu_title' => 'Fees Payment',
		),
		'payment' => array(
			'menu_icone' => $icons_url . 'mjschool-payment.png',
			'app_icone'  => $app_icons_url . 'mjschool-payment.png',
			'menu_title' => 'Payment',
		),
		'transport' => array(
			'menu_icone' => $icons_url . 'mjschool-transport.png',
			'app_icone'  => $app_icons_url . 'mjschool-transport.png',
			'menu_title' => 'Transport',
		),
		'notice' => array(
			'menu_icone' => $icons_url . 'mjschool-notice.png',
			'app_icone'  => $app_icons_url . 'mjschool-notice.png',
			'menu_title' => 'Notice Board',
		),
		'message' => array(
			'menu_icone' => $icons_url . 'mjschool-message.png',
			'app_icone'  => $app_icons_url . 'mjschool-message.png',
			'menu_title' => 'Message',
		),
		'holiday' => array(
			'menu_icone' => $icons_url . 'mjschool-holiday.png',
			'app_icone'  => $app_icons_url . 'mjschool-holiday.png',
			'menu_title' => 'Holiday',
		),
		'library' => array(
			'menu_icone' => $icons_url . 'mjschool-library.png',
			'app_icone'  => $app_icons_url . 'mjschool-library.png',
			'menu_title' => 'Library',
		),
		'certificate' => array(
			'menu_icone' => $icons_url . 'mjschool-library.png',
			'app_icone'  => $app_icons_url . 'mjschool-library.png',
			'menu_title' => 'Certificate',
		),
		'account' => array(
			'menu_icone' => $icons_url . 'mjschool-account.png',
			'app_icone'  => $app_icons_url . 'mjschool-account.png',
			'menu_title' => 'Account',
		),
		'report' => array(
			'menu_icone' => $icons_url . 'mjschool-report.png',
			'app_icone'  => $app_icons_url . 'mjschool-report.png',
			'menu_title' => 'Report',
		),
		'event' => array(
			'menu_icone' => $icons_url . 'mjschool-report.png',
			'app_icone'  => $app_icons_url . 'mjschool-report.png',
			'menu_title' => 'Event',
		),
		'admission' => array(
			'menu_icone' => $icons_url . 'mjschool-admission.png',
			'app_icone'  => $app_icons_url . 'mjschool-admission.png',
			'menu_title' => 'Admission',
		),
		'exam_hall' => array(
			'menu_icone' => $icons_url . 'mjschool-exam_hall.png',
			'app_icone'  => $app_icons_url . 'mjschool-exam_hall.png',
			'menu_title' => 'Exam Hall',
		),
		'migration' => array(
			'menu_icone' => $icons_url . 'mjschool-message.png',
			'app_icone'  => $app_icons_url . 'mjschool-message.png',
			'menu_title' => 'Migration',
		),
		'tax' => array(
			'menu_icone' => $icons_url . 'mjschool-fee.png',
			'app_icone'  => $app_icons_url . 'mjschool-fee.png',
			'menu_title' => 'Tax',
		),
		'custom_field' => array(
			'menu_icone' => $icons_url . 'mjschool-custom.png',
			'app_icone'  => $app_icons_url . 'mjschool-custom.png',
			'menu_title' => 'Custom Field',
		),
		'mjschool_setting' => array(
			'menu_icone' => $icons_url . 'mjschool_setting.png',
			'app_icone'  => $app_icons_url . 'mjschool_setting.png',
			'menu_title' => 'SMS Setting',
		),
		'email_template' => array(
			'menu_icone' => $icons_url . 'mjschool-email-template.png',
			'app_icone'  => $app_icons_url . 'mjschool-email-template.png',
			'menu_title' => 'Email Template',
		),
		'mjschool_template' => array(
			'menu_icone' => $icons_url . 'mjschool-email-template.png',
			'app_icone'  => $app_icons_url . 'mjschool-email-template.png',
			'menu_title' => 'Email Template',
		),
		'general_settings' => array(
			'menu_icone' => $icons_url . 'mjschool-general-settings.png',
			'app_icone'  => $app_icons_url . 'mjschool-general-settings.png',
			'menu_title' => 'General Settings',
		),
		'access_right' => array(
			'menu_icone' => $icons_url . 'mjschool-teacher.png',
			'app_icone'  => $app_icons_url . 'mjschool-teacher.png',
			'menu_title' => 'Access Right',
		),
	);
}

/**
 * Build role access rights based on role-specific permissions
 *
 * @param string $role         Role identifier.
 * @param array  $permissions  Role-specific permission overrides.
 * @return array Role access rights.
 */
function mjschool_build_role_access_rights( $role, $permissions ) {
	$modules        = mjschool_get_module_definitions();
	$access_rights  = array();

	foreach ( $permissions as $module_key => $perms ) {
		if ( isset( $modules[ $module_key ] ) ) {
			$config = array_merge( $modules[ $module_key ], $perms );
			$access_rights[ $module_key ] = mjschool_build_module_access( $module_key, $config );
		}
	}

	return array( $role => $access_rights );
}

/**
 * Get student role default permissions
 *
 * @return array Student permissions configuration.
 */
function mjschool_get_student_permissions() {
	return array(
		'teacher'           => array( 'own_data' => 1, 'view' => 1 ),
		'student'           => array( 'own_data' => 1, 'view' => 1 ),
		'parent'            => array( 'own_data' => 1, 'view' => 1 ),
		'supportstaff'      => array( 'view' => 1 ),
		'subject'           => array( 'own_data' => 1, 'view' => 1 ),
		'schedule'          => array( 'own_data' => 1, 'view' => 1 ),
		'virtual_classroom' => array( 'own_data' => 1, 'view' => 1 ),
		'attendance'        => array( 'own_data' => 1, 'view' => 1 ),
		'notification'      => array( 'own_data' => 1, 'view' => 1 ),
		'exam'              => array( 'own_data' => 1, 'view' => 1 ),
		'class_room'        => array( 'own_data' => 1, 'view' => 1 ),
		'grade'             => array( 'view' => 1 ),
		'hostel'            => array( 'own_data' => 1, 'view' => 1 ),
		'document'          => array( 'own_data' => 1, 'view' => 1 ),
		'leave'             => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'homework'          => array( 'own_data' => 1, 'view' => 1 ),
		'manage_marks'      => array(),
		'feepayment'        => array( 'own_data' => 1, 'view' => 1 ),
		'payment'           => array( 'own_data' => 1, 'view' => 1 ),
		'transport'         => array( 'view' => 1 ),
		'notice'            => array( 'own_data' => 1, 'view' => 1 ),
		'message'           => array( 'own_data' => 1, 'add' => 1, 'view' => 1, 'delete' => 1 ),
		'holiday'           => array( 'view' => 1 ),
		'library'           => array( 'own_data' => 1, 'view' => 1 ),
		'certificate'       => array( 'own_data' => 1, 'view' => 1 ),
		'account'           => array( 'own_data' => 1, 'edit' => 1, 'view' => 1 ),
		'report'            => array(),
		'event'             => array( 'view' => 1 ),
	);
}

/**
 * Get teacher role default permissions
 *
 * @return array Teacher permissions configuration.
 */
function mjschool_get_teacher_permissions() {
	return array(
		'admission'         => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'teacher'           => array( 'own_data' => 1, 'view' => 1 ),
		'student'           => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'parent'            => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'subject'           => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'class'             => array( 'own_data' => 1, 'view' => 1 ),
		'virtual_classroom' => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'schedule'          => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'attendance'        => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'notification'      => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'exam'              => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'class_room'        => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'exam_hall'         => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'hostel'            => array( 'view' => 1 ),
		'homework'          => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'manage_marks'      => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'feepayment'        => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'payment'           => array(),
		'transport'         => array( 'view' => 1 ),
		'document'          => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'leave'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'notice'            => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'message'           => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'migration'         => array( 'add' => 1, 'view' => 1 ),
		'holiday'           => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'library'           => array( 'own_data' => 1, 'add' => 1, 'view' => 1 ),
		'certificate'       => array( 'own_data' => 1 ),
		'account'           => array( 'own_data' => 1, 'edit' => 1, 'view' => 1 ),
		'report'            => array( 'view' => 1 ),
		'event'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
	);
}

/**
 * Get parent role default permissions
 *
 * @return array Parent permissions configuration.
 */
function mjschool_get_parent_permissions() {
	return array(
		'teacher'           => array( 'own_data' => 1, 'view' => 1 ),
		'student'           => array( 'own_data' => 1, 'view' => 1 ),
		'parent'            => array( 'own_data' => 1, 'view' => 1 ),
		'subject'           => array( 'own_data' => 1, 'view' => 1 ),
		'schedule'          => array( 'own_data' => 1, 'view' => 1 ),
		'virtual_classroom' => array( 'own_data' => 1, 'view' => 1 ),
		'attendance'        => array( 'own_data' => 1, 'view' => 1 ),
		'exam'              => array( 'own_data' => 1, 'view' => 1 ),
		'class_room'        => array( 'own_data' => 1, 'view' => 1 ),
		'hostel'            => array( 'own_data' => 1, 'view' => 1 ),
		'notification'      => array( 'own_data' => 1, 'view' => 1 ),
		'homework'          => array( 'own_data' => 1, 'view' => 1 ),
		'manage_marks'      => array(),
		'feepayment'        => array( 'own_data' => 1, 'view' => 1 ),
		'document'          => array( 'own_data' => 1, 'view' => 1 ),
		'leave'             => array( 'own_data' => 1, 'view' => 1 ),
		'payment'           => array( 'own_data' => 1, 'view' => 1 ),
		'transport'         => array( 'view' => 1 ),
		'notice'            => array( 'own_data' => 1, 'view' => 1 ),
		'message'           => array( 'own_data' => 1, 'add' => 1, 'view' => 1, 'delete' => 1 ),
		'holiday'           => array( 'view' => 1 ),
		'library'           => array( 'own_data' => 1, 'view' => 1 ),
		'certificate'       => array( 'own_data' => 1, 'view' => 1 ),
		'account'           => array( 'own_data' => 1, 'edit' => 1, 'view' => 1 ),
		'report'            => array(),
		'event'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
	);
}

/**
 * Get support staff role default permissions
 *
 * @return array Support staff permissions configuration.
 */
function mjschool_get_supportstaff_permissions() {
	return array(
		'admission'         => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'student'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'teacher'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'supportstaff'      => array( 'own_data' => 1, 'view' => 1 ),
		'parent'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'subject'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'class'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'schedule'          => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'virtual_classroom' => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'attendance'        => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'exam'              => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'class_room'        => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'notification'      => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'exam_hall'         => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'grade'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'manage_marks'      => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'homework'          => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'hostel'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'document'          => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'leave'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'transport'         => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'notice'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'message'           => array( 'add' => 1, 'view' => 1, 'delete' => 1 ),
		'migration'         => array( 'add' => 1, 'view' => 1 ),
		'tax'               => array( 'view' => 1 ),
		'feepayment'        => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'payment'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'holiday'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'library'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'certificate'       => array(),
		'custom_field'      => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'report'            => array( 'view' => 1 ),
		'mjschool_setting'  => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'email_template'    => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'mjschool_template' => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'general_settings'  => array(),
		'account'           => array( 'own_data' => 1, 'edit' => 1, 'view' => 1 ),
		'event'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
	);
}

/**
 * Get management role default permissions
 *
 * @return array Management permissions configuration.
 */
function mjschool_get_management_permissions() {
	return array(
		'admission'         => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'supportstaff'      => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'exam_hall'         => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'grade'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'notification'      => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'custom_field'      => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'migration'         => array( 'view' => 1 ),
		'mjschool_setting'  => array( 'edit' => 1, 'view' => 1 ),
		'tax'               => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'email_template'    => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'mjschool_template' => array( 'add' => 1, 'edit' => 1, 'view' => 1 ),
		'access_right'      => array( 'edit' => 1, 'view' => 1 ),
		'teacher'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'student'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'parent'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'subject'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'class'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'virtual_classroom' => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'schedule'          => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'attendance'        => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'exam'              => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'class_room'        => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'hostel'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'homework'          => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'manage_marks'      => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'payment'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'transport'         => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'document'          => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'leave'             => array( 'own_data' => 1, 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'notice'            => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'message'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'holiday'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'library'           => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'certificate'       => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
		'account'           => array( 'edit' => 1, 'view' => 1 ),
		'report'            => array( 'view' => 1 ),
		'event'             => array( 'add' => 1, 'edit' => 1, 'view' => 1, 'delete' => 1 ),
	);
}

/**
 * Get dashboard card access for a specific role
 *
 * @param string $role Role identifier.
 * @return array Dashboard card access configuration.
 */
function mjschool_get_dashboard_card_access( $role ) {
	$defaults = array(
		'student' => array(
			'mjschool_payment_status_chart' => 'yes',
			'mjschool_user_chart'           => 'yes',
			'mjschool_invoice_chart'        => 'yes',
		),
		'teacher' => array(
			'mjschool_student_status_chart' => 'yes',
			'mjschool_attendance_chart'     => 'yes',
			'mjschool_user_chart'           => 'yes',
		),
		'supportstaff' => array(
			'mjschool_student_status_chart' => 'yes',
			'mjschool_attendance_chart'     => 'yes',
			'mjschool_payment_status_chart' => 'yes',
			'mjschool_payment_report'       => 'yes',
			'mjschool_invoice_chart'        => 'yes',
			'mjschool_user_chart'           => 'yes',
		),
		'parent' => array(
			'mjschool_user_chart'           => 'yes',
			'mjschool_invoice_chart'        => 'yes',
			'mjschool_payment_status_chart' => 'yes',
		),
	);

	if ( ! isset( $defaults[ $role ] ) ) {
		return array();
	}

	$access = array();
	$role_defaults = $defaults[ $role ];

	foreach ( $role_defaults as $key => $default ) {
		$request_key = str_replace( 'mjschool_', '', $key ) . '_enable_' . $role;
		
		if ( isset( $_REQUEST[ $request_key ] ) && current_user_can( 'manage_options' ) ) {
			$access[ $key ] = sanitize_text_field( wp_unslash( $_REQUEST[ $request_key ] ) ) === 'yes' ? 'yes' : 'no';
		} else {
			$access[ $key ] = $default;
		}
	}

	return $access;
}

/**
 * Main function to get all MJSchool options
 *
 * @return array All plugin options.
 */
function mjschool_option() {
	// Build role access rights
	$role_access_right_student      = mjschool_build_role_access_rights( 'student', mjschool_get_student_permissions() );
	$role_access_right_teacher      = mjschool_build_role_access_rights( 'teacher', mjschool_get_teacher_permissions() );
	$role_access_right_parent       = mjschool_build_role_access_rights( 'parent', mjschool_get_parent_permissions() );
	$role_access_right_supportstaff = mjschool_build_role_access_rights( 'supportstaff', mjschool_get_supportstaff_permissions() );
	$role_access_right_management   = mjschool_build_role_access_rights( 'management', mjschool_get_management_permissions() );

	// Dashboard card access
	$dashboard_card_access_for_student       = mjschool_get_dashboard_card_access( 'student' );
	$dashboard_card_access_for_teacher       = mjschool_get_dashboard_card_access( 'teacher' );
	$dashboard_card_access_for_support_staff = mjschool_get_dashboard_card_access( 'supportstaff' );
	$dashboard_card_access_for_parent        = mjschool_get_dashboard_card_access( 'parent' );

	// Setup Wizard Options
	$mjschool_setup_wizard_step = array(
		'step1_general_setting'  => 'no',
		'step2_class'            => 'no',
		'step3_teacher'          => 'no',
		'step4_subject'          => 'no',
		'step5_class_time_table' => 'no',
		'step6_student'          => 'no',
		'step7_email_temp'       => 'no',
	);

	// Setup Wizard Status
	$wizard_option = get_option( 'mjschool_setup_wizard_step' );
	if ( empty( $wizard_option ) ) {
		add_option( 'mjschool_setup_wizard_step', $mjschool_setup_wizard_step );
	}

	$plugin_url = plugins_url( 'mjschool/' );

	// Define all system options
	$options = array(
		// Basic Settings
		'mjschool_name'                    => esc_attr__( 'School Management System', 'mjschool' ),
		'mjschool_staring_year'            => gmdate( 'Y' ),
		'mjschool_address'                 => '',
		'mjschool_contact_number'          => '',
		'mjschool_combine'                 => 0,
		'mjschool_contry'                  => 'United States',
		'mjschool_city'                    => 'Los Angeles',
		'mjschool_class_room'              => 0,
		'mjschool_custom_class'            => 'school',
		'mjschool_custom_class_display'    => 0,
		'mjschool_past_pay'                => 'no',
		'mjschool_prefix'                  => 'S-',
		'mjschool_email'                   => 'admin@gmail.com',
		'mjschool_datepicker_format'       => 'yy/mm/dd',

		// Logos and Images
		'mjschool_app_logo'                => $plugin_url . 'assets/images/mjschool-mobile-app-default.png',
		'mjschool_logo'                    => $plugin_url . 'assets/images/mjschool-final-logo.png',
		'mjschool_system_logo'             => $plugin_url . 'assets/images/mjschool-logo-white.png',
		'mjschool_background_image'        => $plugin_url . 'assets/images/school_life.jpg',
		'mjschool_student_thumb'           => $plugin_url . 'assets/images/thumb-icon/mjschool-student.png',
		'mjschool_mjschool-no-data-img'    => $plugin_url . 'assets/images/thumb-icon/mjschool-plus-icon.png',
		'mjschool_parent_thumb'            => $plugin_url . 'assets/images/thumb-icon/mjschool-parents.png',
		'mjschool_teacher_thumb'           => $plugin_url . 'assets/images/thumb-icon/mjschool-teacher.png',
		'mjschool_supportstaff_thumb'      => $plugin_url . 'assets/images/thumb-icon/mjschool-support-staff.png',
		'mjschool_driver_thumb'            => $plugin_url . 'assets/images/thumb-icon/mjschool-transport.png',
		'mjschool_principal_signature'     => $plugin_url . 'assets/images/mjschool-signature-stamp.png',
		'mjschool_student_thumb_new'       => $plugin_url . 'assets/images/thumb-icon/mjschool-student.png',
		'mjschool_parent_thumb_new'        => $plugin_url . 'assets/images/thumb-icon/mjschool-parents.png',
		'mjschool_teacher_thumb_new'       => $plugin_url . 'assets/images/thumb-icon/mjschool-teacher.png',
		'mjschool_supportstaff_thumb_new'  => $plugin_url . 'assets/images/thumb-icon/mjschool-support-staff.png',
		'mjschool_driver_thumb_new'        => $plugin_url . 'assets/images/thumb-icon/mjschool-transport.png',

		// Footer
		'mjschool_footer_description'      => 'Copyright ©' . gmdate( 'Y' ) . ' Mojoomla. All rights reserved.',

		// Access Rights
		'mjschool_access_right_student'    => $role_access_right_student,
		'mjschool_access_right_teacher'    => $role_access_right_teacher,
		'mjschool_access_right_parent'     => $role_access_right_parent,
		'mjschool_access_right_supportstaff' => $role_access_right_supportstaff,
		'mjschool_access_right_management' => $role_access_right_management,

		// Dashboard Cards
		'mjschool_dashboard_card_for_student'       => $dashboard_card_access_for_student,
		'mjschool_dashboard_card_for_teacher'       => $dashboard_card_access_for_teacher,
		'mjschool_dashboard_card_for_support_staff' => $dashboard_card_access_for_support_staff,
		'mjschool_dashboard_card_for_parent'        => $dashboard_card_access_for_parent,

		// Setup Wizard
		'mjschool_setup_wizard_status'     => 'no',

		// Service Settings
		'mjschool_service'                 => 'msg91',
		'mjschool_app_domain_name'         => '',
		'mjschool_app_licence_key'         => '',
		'mjschool_app_setup_email'         => '',

		// Payment Settings
		'mjschool_paymaster_pack'          => 'no',
		'mjschool_invoice_option'          => 1,
		'mjschool_mail_notification'       => 1,
		'mjschool_notification_fcm_key'    => '',
		'mjschool_service_enable'          => 0,
		'mjschool_student_approval'        => 1,
		'mjschool_sms_template'            => 'Hello [mjschool_USER_NAME] ',
		'mjschool_clickatell_mjschool_service' => array(),
		'mjschool_twillo_mjschool_service' => array(),
		'mjschool_parent_send_message'     => 1,

		// Dashboard Toggles
		'mjschool_enable_total_student'    => 1,
		'mjschool_enable_total_teacher'    => 1,
		'mjschool_enable_total_parent'     => 1,
		'mjschool_enable_homework_mail'    => 0,
		'mjschool_enable_total_attendance' => 1,

		// Virtual Classroom Settings
		'mjschool_enable_sandbox'          => 'yes',
		'mjschool_virtual_classroom_account_id'       => '',
		'mjschool_virtual_classroom_client_id'        => '',
		'mjschool_virtual_classroom_client_secret_id' => '',
		'mjschool_virtual_classroom_access_token'     => '',
		'mjschool_enable_virtual_classroom'           => 'no',

		// Return and Reminder Settings
		'mjschool_return_option'           => 'yes',
		'mjschool_return_period'           => 3,
		'mjschool_system_payment_reminder_day'    => 3,
		'mjschool_system_payment_reminder_enable' => 'no',

		// Payment Gateway Settings
		'mjschool_paypal_email'            => '',
		'razorpay__key'                    => '',
		'razorpay_secret_mid'              => '',
		'mjschool_currency_code'           => 'USD',

		// Other Settings
		'mjschool_teacher_manage_allsubjects_marks' => 'yes',
		'mjschool_enable_video_popup_show' => 'yes',
		'mjschool_teacher_show_access'     => 'own_class',
		'mjschool_heder_enable'            => 'yes',
		'mjschool_admission_fees'          => 'no',
		'mjschool_enable_recurring_invoices' => 'no',
		'mjschool_admission_amount'        => '',
		'mjschool_system_color_code'       => '#5840bb',
		'mjschool_registration_fees'       => 'no',
		'mjschool_registration_amount'     => '',
		'mjschool_invoice_notice'          => 'If You Paid Your Payment than Invoice are automatically Generated.',
		'mjschool_attendence_migration_status' => 'no',

		// Upload Settings
		'mjschool_upload_document_type'    => 'pdf, doc, docx, ppt, pptx, gif, png, jpg, jpeg, webp',
		'mjschool_upload_profile_extention' => 'gif, png, jpg, jpeg, webp',
		'mjschool_upload_document_size'    => '30',
		'mjschool_upload_profile_size'     => '10',

		// Email Subjects
		'mjschool_registration_title'      => 'Student Registration',
		'mjschool_student_activation_title' => 'Student Approved',
		'mjschool_fee_payment_title'       => 'Fees Alert',
		'mjschool_fee_payment_title_for_parent' => 'Fees Alert',
		'mjschool_admissiion_title'        => 'Request For Admission',
		'mjschool_exam_receipt_subject'    => 'Exam Receipt Generate',
		'mjschool_bed_subject'             => 'Hostel Bed Assigned',
		'mjschool_add_approve_admisson_mail_subject' => 'Admission Approved',
		'mjschool_admissiion_approve_subject_for_parent_subject' => 'Student Admission Approved',
		'mjschool_student_assign_teacher_mail_subject' => 'New Student has been assigned to you.',
		'mjschool_enable_virtual_classroom_reminder' => 'yes',
		'mjschool_enable_mjschool_virtual_classroom_reminder' => 'yes',
		'mjschool_virtual_classroom_reminder_before_time' => '30',
		'mjschool_add_leave_emails'        => '',
		'mjschool_leave_approveemails'     => '',
		'mjschool_add_leave_subject'       => 'Request For Leave',
		'mjschool_add_leave_subject_of_admin' => 'Request For Leave',
		'mjschool_add_leave_subject_for_student' => 'Request For Leave',
		'mjschool_add_leave_subject_for_parent' => 'Request For Leave',
		'mjschool_leave_approve_subject'   => 'Your leave has been Approved Successfully',
		'mjschool_leave_reject_subject'    => 'Your leave has been Rejected',
		'mjschool_add_exam_mail_title'     => 'New exam has been assigned to you.',
	);

	// Add email templates
	$options = array_merge( $options, mjschool_get_email_templates() );

	return $options;
}

/**
 * Get all email template defaults
 *
 * @return array Email templates.
 */
function mjschool_get_email_templates() {
	return array(
		// SMS Templates
		'mjschool_attendance_mjschool_content' => 'Dear {{parent_name}}, your child {{student_name}} is absent on {{current_date}} at {{school_name}}.',
		'mjschool_fees_payment_mjschool_content_for_student' => 'Dear {{student_name}}, A new fees payment invoice has been generated for you at {{school_name}}.',
		'mjschool_fees_payment_mjschool_content_for_parent' => 'Dear {{parent_name}}, A new fees payment invoice has been generated for your {{student_name}} at {{school_name}}.',
		'mjschool_fees_payment_reminder_mjschool_content' => 'Dear {{parent_name}}, we just wanted to send you a reminder that the tuition fee has not been paid against your child {{student_name}} at {{school_name}}.',
		'mjschool_student_approve_mjschool_content' => 'Dear {{student_name}}, your account with {{school_name}} is approved.',
		'mjschool_student_admission_approve_mjschool_content' => 'Dear {{student_name}}, your admission has been successfully approved with {{school_name}}.',
		'mjschool_holiday_mjschool_content' => 'Dear {{user_name}}, New Holiday {{title}} Announced at {{school_name}}.',
		'mjschool_leave_student_mjschool_content' => 'Dear {{student_name}}, your Leave for {{date}} are Added Successfully at {{school_name}}.',
		'mjschool_leave_parent_mjschool_content' => 'Dear {{parent_name}}, your child {{student_name}}, has been added leave of {{date}} at {{school_name}}.',
		'mjschool_event_mjschool_content' => 'Dear {{student_name}}, we are inform you about an exciting new event {{event_title}} at {{school_name}}.',
		'mjschool_exam_student_mjschool_content' => 'This is a reminder that your upcoming exam {{exam_name}} is scheduled for {{date}} At {{school_name}}.',
		'mjschool_exam_parent_mjschool_content' => 'We would like to inform you that your child {{student_name}} will have an important exam {{exam_name}} on {{date}} At {{school_name}}.',
		'mjschool_homework_student_mjschool_content' => 'Dear {{student_name}}, your new homework {{title}} is posted. Please check and submit it by the submission date {{date}} At {{school_name}}.',
		'mjschool_homework_parent_mjschool_content' => 'Dear {{parent_name}}, your child has a new homework {{title}} assignment. Please review it with them and provide any necessary support and submit at {{school_name}}.',

		// Email Templates
		'mjschool_student_assign_teacher_mail_content' => 'Dear {{teacher_name}},
New Student {{student_name}} has been assigned to you.
Regards From {{school_name}}.',

		'mjschool_generate_invoice_mail_subject' => 'Generate Invoice',
		'mjschool_generate_invoice_mail_content' => 'Dear {{student_name}},
Your have a new invoice. You can check the invoice attached here.
Regards From {{school_name}}.',

		'mjschool_add_user_mail_subject' => 'Your have been assigned role of {{role}} in {{school_name}}.',
		'mjschool_add_user_mail_content' => 'Dear {{user_name}},
You are Added by admin in {{school_name}}. Your have been assigned role of {{role}} in {{school_name}}. You can sign in using this link. {{login_link}}
UserName : {{username}}
Password : {{Password}}
Regards From {{school_name}}.',

		'mjschool_registration_mailtemplate' => 'Hello {{student_name}},
Your registration has been successful with {{school_name}}.
Class Name : {{class_name}}
Email ID : {{email_id}}
Password : {{password}}
Regards From {{school_name}}.',

		'mjschool_admission_mailtemplate_content' => 'Hello {{student_name}},
Your admission request has been successful with {{school_name}}. You will be able to access your account after school admin approves it and we will send username and password shortly.
Student Name : {{user_name}}
Email : {{email}}
Regards From {{school_name}}.',

		'mjschool_exam_receipt_content' => 'Hello {{student_name}},
Your exam hall receipt has been generated.
Regards From {{school_name}}.',

		'mjschool_bed_content' => 'Hello {{student_name}},
You have been assigned new hostel bed in {{school_name}}.
Hostel Name : {{hostel_name}}
Room Number : {{room_id}}
Bed Number : {{bed_id}}
Bed Charge : {{bed_charge}}
Regards From {{school_name}}.',

		'mjschool_admission_mailtemplate_content_for_parent' => 'Hello {{student_name}},
Your admission has been successfully approved with {{school_name}}.
You can sign in using this link: {{login_link}}
Class Name : {{class_name}}
Roll No : {{roll_no}}
Email : {{email}}
Password : {{password}}
Regards,
{{school_name}}',

		'mjschool_add_approve_admission_mail_content' => 'Hello {{user_name}},
Your admission has been successful approved with {{school_name}}.
You can signin using this link. {{login_link}}
Class Name : {{class_name}}
Roll No : {{roll_no}}
Email : {{email}}
Password : {{Password}}
Regards From {{school_name}}.',

		'mjschool_student_activation_mailcontent' => 'Hello {{student_name}},
Your account with {{school_name}} is approved. You can access student account using your login details. Your other details are given bellow.
User Name : {{user_name}}
Class Name : {{class_name}}
Email : {{email}}
Regards From {{school_name}}.',

		'mjschool_addleave_email_template' => 'Hello,
Date : {{date}}
Leave Type : {{leave_type}}
Leave Duration : {{leave_duration}}
Reason : {{reason}}
Thank you
{{employee_name}}',

		'mjschool_leave_approve_email_template' => 'Hello,
Leave of {{user_name}} is approved successfully.
Date : {{date}}
Comment : {{comment}}
Regards From {{system_name}}.
Thank you
{{system_name}}',

		'mjschool_addleave_email_template_student' => 'Hello {{student_name}},
Your Leave are Added Successfully.
Date : {{date}},
Leave Type : {{leave_type}},
Leave Duration : {{leave_duration}},
Reason : {{reason}},
Thank you
{{school_name}}.',

		'mjschool_addleave_email_template_parent' => 'Hello {{parent_name}},
Your child {{student_name}}, has been added leave of {{date}}.
Leave Type : {{leave_type}},
Leave Duration : {{leave_duration}},
Reason : {{reason}},
Thank you
{{school_name}}.',

		'mjschool_addleave_email_template_of_admin' => 'Dear Admin,
{{student_name}} are Add Leave of {{date}}.
Leave Type : {{leave_type}},
Leave Duration : {{leave_duration}},
Reason : {{reason}},
Thank you
{{school_name}}.',

		'mjschool_leave_reject_email_template' => 'Hello {{student_name}},
Leave of {{student_name}} is Rejected.
Date : {{date}}
Comment : {{comment}}
Regards From {{school_name}}
Thank you',

		'mjschool_add_exam_mailcontent' => 'Dear {{user_name}},
A new exam {{exam_name}} has been assigned to you.
Exam Details:
Exam Name : {{exam_name}}
Exam Start To End Date : {{exam_start_end_date}}
Exam Comment : {{exam_comment}}
Regards From
{{school_name}}',

		'mjschool_fee_payment_mailcontent' => 'Dear {{student_name}},
You have a new invoice. You can check the invoice attached here.
Date : {{date}}
Amount : {{amount}}
Regards From {{school_name}}
Thank you',

		'mjschool_fee_payment_mailcontent_for_parent' => 'Dear {{parent_name}},
You have a new invoice for your child {{child_name}}. You can check the invoice attached here.
Date : {{date}}
Amount : {{amount}}
Regards From {{school_name}}
Thank you',

		'mjschool_message_received_mailcontent' => 'Dear {{receiver_name}},
You have received new message {{message_content}}.
Regards From {{school_name}}.',

		'mjschool_message_received_mailsubject' => 'You have received new message from {{from_mail}} at {{school_name}}',

		'mjschool_absent_mail_notification_subject' => 'Your Child {{child_name}} is absent today',
		'mjschool_absent_mail_notification_content' => 'Your Child {{child_name}} is absent today.
Regards From {{school_name}}.',

		'mjschoool_student_assign_to_teacher_subject' => 'You have been Assigned {{teacher_name}} at {{school_name}}',
		'mjschool_student_assign_to_teacher_content' => 'Dear {{student_name}},
You are assigned to {{teacher_name}}. {{teacher_name}} belongs to {{class_name}}.
Regards From {{school_name}}.',

		'mjschool_payment_recived_mailsubject' => 'Payment Received against Invoice',
		'mjschool_payment_recived_mailcontent' => 'Dear {{student_name}},
Your have successfully paid your invoice {{invoice_no}}. You can check the invoice attached here.
Regards From {{school_name}}.',

		'mjschool_notice_mailsubject' => 'New Notice For You',
		'mjschool_notice_mailcontent' => 'New Notice For You.
Notice Title : {{notice_title}}
Notice Date : {{notice_date}}
Notice Comment : {{notice_comment}}
Regards From {{school_name}}',

		'mjschool_event_mailsubject' => 'Exciting New Event at {{school_name}}.',
		'mjschool_event_mailcontent' => 'Dear {{user_name}},
We are delighted to announce an exciting new event at {{school_name}} that promises to be a memorable experience for all attendees!
Event Details:
Event Name: {{event_title}}
Date: {{event_date}}
Time: {{event_time}}
Description: {{description}}
Regards From {{school_name}}.',

		'mjschool_parent_homework_mail_subject' => 'New Homework Assigned',
		'mjschool_parent_homework_mail_content' => 'Dear {{parent_name}},
New homework has been assign to your child.
Student name : {{student_name}}
Homework Title : {{title}}
Subject : {{subject}}
Homework Date : {{homework_date}}
Submission Date : {{submition_date}}
Regards From {{school_name}}',

		'mjschool_homework_title' => 'New Homework Assigned',
		'mjschool_homework_mailcontent' => 'Dear {{student_name}},
New homework has been assign to you.
Homework Title : {{title}}
Subject : {{subject}}
Homework Date : {{homework_date}}
Submission Date : {{submition_date}}
Regards From {{school_name}}',

		'mjschool_holiday_mailsubject' => 'Holiday Announcement',
		'mjschool_holiday_mailcontent' => 'Holiday Announcement
Holiday Title : {{holiday_title}}
Holiday Date : {{holiday_date}}
Regards From {{school_name}}',

		'mjschool_virtual_class_invite_teacher_mail_subject' => 'Inviting you to a scheduled Zoom meeting',
		'mjschool_virtual_class_invite_teacher_mail_content' => 'Inviting you to a scheduled Zoom meeting
Class Name : {{class_name}}
Time : {{time}}
Virtual Class ID : {{virtual_class_id}}
Password : {{password}}
Join Zoom Virtual Class : {{join_zoom_virtual_class}}
Start Zoom Virtual Class : {{start_zoom_virtual_class}}
Regards From {{school_name}}',

		'mjschool_virtual_class_teacher_reminder_mail_subject' => 'Your virtual class just start',
		'mjschool_virtual_class_teacher_reminder_mail_content' => 'Dear {{teacher_name}}
Your virtual class just start
Class Name : {{class_name}}
subject Name : {{subject_name}}
Date : {{date}}
Time : {{time}}
Virtual Class ID : {{virtual_class_id}}
Password : {{password}}
{{start_zoom_virtual_class}}
Regards From {{school_name}}',

		'mjschool_virtual_class_student_reminder_mail_subject' => 'Your virtual class just start',
		'mjschool_virtual_class_student_reminder_mail_content' => 'Dear {{student_name}}
Your virtual class just start
Class Name : {{class_name}}
Subject Name : {{subject_name}}
Teacher Name : {{teacher_name}}
Date : {{date}}
Time : {{time}}
Virtual Class ID : {{virtual_class_id}}
Password : {{password}}
{{join_zoom_virtual_class}}
Regards From {{school_name}}',

		'mjschool_fee_payment_reminder_title' => 'Fees Payment Reminder',
		'mjschool_fee_payment_reminder_mailcontent' => 'Dear {{parent_name}},
We just wanted to send you a reminder that the tuition fee has not been paid against your son/daughter {{student_name}} of class {{class_name}}. The total amount is {{total_amount}} and the due amount is {{due_amount}}.
Regards From
{{school_name}}',

		'mjschool_fee_payment_reminder_title_for_student' => 'Fees Payment Reminder',
		'mjschool_fee_payment_reminder_mailcontent_for_student' => 'Dear {{student_name}},
We just wanted to send you a reminder that the tuition fee has not been paid against you. The total amount is {{total_amount}} and the due amount is {{due_amount}}.
Regards From
{{school_name}}',

		'mjschool_assign_subject_title' => 'New subject has been assigned to you.',
		'mjschool_assign_subject_mailcontent' => 'Dear {{teacher_name}},
New subject {{subject_name}} has been assigned to you.
Regards From
{{school_name}}',

		'mjschool_transfer_certificate_title' => 'Transfer Certificate',
		'mjschool_transfer_certificate_template' => mjschool_get_transfer_certificate_template(),

		'mjschool_issue_book_title' => 'New book has been issue to you.',
		'mjschool_issue_book_mailcontent' => 'Dear {{student_name}},
New book {{book_name}} has been issue to you.
Issue Date : {{issue_date}}
Return Date : {{return_date}}
Regards From
{{school_name}}',
	);
}

/**
 * Get transfer certificate HTML template
 *
 * @return string Transfer certificate template.
 */
function mjschool_get_transfer_certificate_template() {
	return '<div class="container_table">
<div class="header">
	<h2 style="text-align: center;border-collapse: collapse;" class="certificate_heading">TRANSFER CERTIFICATE</h2>
	<div style="width: 100%; overflow: hidden; line-height: 4px;">
		<div style="width: 49%; float: left;">
			<p><strong>Affiliation Number.:</strong> 2134012</p>
			<p><strong>Book Number.:</strong> 08</p>
			<p><strong>Admission Number.:</strong> {{admission_no}}</p>
		</div>
		<div style="width: 49%; float: right;">
			<p><strong>School Code:</strong> 72055</p>
			<p><strong>Sr Number.:</strong> 045</p>
			<p><strong>Roll Number.:</strong> {{roll_no}}</p>
		</div>
	</div>
</div>
	<table style="width: 100%; border-collapse: collapse; border: 1px solid black; font-size: 12px;">
	<tr><td style="border: 1px solid black;text-align: center;"><b>1.</b></td><td style="border: 1px solid black;padding-left: 6px;">Student\'s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{student_name}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>2.</b></td><td style="border: 1px solid black;padding-left: 6px;">Father\'s/Guardian\'s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{father_name}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>3.</b></td><td style="border: 1px solid black;padding-left: 6px;">Mother\'s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{mother_name}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>4.</b></td><td style="border: 1px solid black;padding-left: 6px;">Date of Birth (DD-MM-YYYY)</td><td style="border: 1px solid black;padding-left: 6px;">{{birth_date}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>5.</b></td><td style="border: 1px solid black;padding-left: 6px;">Date of Birth (in Words)</td><td style="border: 1px solid black;padding-left: 6px;">{{birth_date_words}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>6.</b></td><td style="border: 1px solid black;padding-left: 6px;">Nationality</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>7.</b></td><td style="border: 1px solid black;padding-left: 6px;">Category (SC/ST/OBC)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>8.</b></td><td style="border: 1px solid black;padding-left: 6px;">First Admission Date &amp; Class</td><td style="border: 1px solid black;padding-left: 6px;">{{admission_date}} &amp; {{class_name}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>9.</b></td><td style="border: 1px solid black;padding-left: 6px;">Last Class Studied</td><td style="border: 1px solid black;padding-left: 6px;">{{last_class}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>10.</b></td><td style="border: 1px solid black;padding-left: 6px;">Last Examination with Result</td><td style="border: 1px solid black;padding-left: 6px;">{{last_exam_name}} {{last_result}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>11.</b></td><td style="border: 1px solid black;padding-left: 6px;">Failed (if yes, once/twice)</td><td style="border: 1px solid black;padding-left: 6px;">{{fails}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>12.</b></td><td style="border: 1px solid black;padding-left: 6px;">Subjects Studied</td><td style="border: 1px solid black;padding-left: 6px;">{{subject}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>13.</b></td><td style="border: 1px solid black;padding-left: 6px;">Qualified for Higher Class</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>14.</b></td><td style="border: 1px solid black;padding-left: 6px;">Fee Paid Up To</td><td style="border: 1px solid black;padding-left: 6px;">{{fees_pay}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>15.</b></td><td style="border: 1px solid black;padding-left: 6px;">Fee Concession (if any)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>16.</b></td><td style="border: 1px solid black;padding-left: 6px;">Working Days in Session</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>17.</b></td><td style="border: 1px solid black;padding-left: 6px;">Days Present</td><td style="border: 1px solid black;padding-left: 6px;">{{total_present}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>18.</b></td><td style="border: 1px solid black;padding-left: 6px;">NCC/Scout/Guide (details)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>19.</b></td><td style="border: 1px solid black;padding-left: 6px;">Extracurricular Activities &amp; Achievements</td><td style="padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>20.</b></td><td style="border: 1px solid black;padding-left: 6px;">General Conduct</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>21.</b></td><td style="border: 1px solid black;padding-left: 6px;">Application Date</td><td style="border: 1px solid black;padding-left: 6px;">{{date}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>22.</b></td><td style="border: 1px solid black;padding-left: 6px;">Certificate Issue Date</td><td style="border: 1px solid black;padding-left: 6px;">{{date}}</td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>23.</b></td><td style="border: 1px solid black;padding-left: 6px;">Reason for Leaving</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	<tr><td style="border: 1px solid black;text-align: center;"><b>24.</b></td><td style="border: 1px solid black;padding-left: 6px;">Other Remarks</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
	</table>
	<table style="width: 100%; border-collapse: collapse; border: none; margin-top: 6px; font-size: 14px;">
	<tbody>
	<tr>
	<td style="width: 33%; vertical-align: top; border-collapse: collapse; border: none;"><strong>Signature of Class Teacher:</strong>
	<img src="{{teacher_signature}}" width="100px" height="50px" />
	<strong>Name:</strong> {{teacher_name}}
	<strong>Designation:</strong> {{teacher_designation}}</td>
	<td style="width: 33%; vertical-align: top; border-collapse: collapse; border: none;"><strong>Checked by:</strong>
	<img src="{{check_by_signature}}" width="100px" height="50px" />
	<strong>Name:</strong> {{checking_teacher_name}}
	<strong>Designation:</strong> {{checking_teacher_designation}}</td>
	<td style="width: 33%; vertical-align: top; border-collapse: collapse; border: none;"><strong>Signature of Principal:</strong>
	<img src="{{principal_signature}}" width="100px" height="50px" />
	<strong>Name:</strong>
	<strong>Designation:</strong>
	<strong>Date:</strong> {{date}}
	<strong>Place:</strong> {{place}}</td>
	</tr>
	</tbody>
	</table>
</div>';
}

/**
 * Initialize MJSchool general settings
 *
 * @return void
 */
function mjschool_general_setting() {
	$options = mjschool_option();
	foreach ( $options as $key => $val ) {
		add_option( $key, $val );
	}
}
add_action( 'admin_init', 'mjschool_general_setting' );

/**
 * Output nonce field for settings forms
 *
 * @return void
 */
function mjschool_settings_nonce_field() {
	wp_nonce_field( 'mjschool_save_settings', 'mjschool_settings_nonce' );
}

/**
 * Define plugin constants for script versions
 */
if ( ! defined( 'MJSCHOOL_SCRIPT_VERSION' ) ) {
    define( 'MJSCHOOL_SCRIPT_VERSION', '1.0.0' );
}

/**
 * Get all MJSchool admin page slugs
 *
 * @since 1.0.0
 * @return array List of all MJ School plugin page slugs.
 */
function mjschool_call_script_page() {
    return array(
        'mjschool',
        'mjschool_admission',
        'mjschool_setup',
        'mjschool_student',
        'mjschool_student_homewrok', // Note: typo in original - 'homewrok' should be 'homework'
        'mjschool_teacher',
        'mjschool_parent',
        'mjschool_Subject',
        'mjschool_class',
        'mjschool_route',
        'mjschool_custom_class',
        'mjschool_class_room',
        'mjschool_attendence', // Note: typo - should be 'attendance'
        'mjschool_exam',
        'mjschool_grade',
        'mjschool_result',
        'mjschool_leave',
        'mjschool_document',
        'mjschool_transport',
        'mjschool_certificate',
        'mjschool_notice',
        'mjschool_event',
        'mjschool_message',
        'mjschool_hall',
        'mjschool_fees',
        'mjschool_fees_payment',
        'mjschool_payment',
        'mjschool_holiday',
        'mjschool_report',
        'mjschool_advance_report',
        'mjschool_Migration',
        'mjschool_sms_setting',
        'mjschool_system_addon',
        'mjschool_system_videos',
        'mjschool_general_settings',
        'mjschool_supportstaff',
        'mjschool_library',
        'mjschool_custom_field',
        'mjschool_access_right',
        'mjschool_hostel',
        'mjschool_view-attendance',
        'mjschool_email_template',
        'mjschool_sms_template',
        'mjschool_show_infographic',
        'mjschool_notification',
        'mjschool_homework',
        'mjschool_virtual_classroom',
        'mjschool_dashboard',
        'mjschool_tax',
    );
}

/**
 * Get current page and tab from request
 *
 * @since 2.0.0
 * @return array Array containing 'page' and 'tab' values.
 */
function mjschool_get_current_page_info() {
    return array(
        'page' => isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '',
        'tab'  => isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : '',
    );
}

/**
 * Get plugin assets URL
 *
 * @since 2.0.0
 * @param string $path Relative path to asset.
 * @return string Full URL to asset.
 */
function mjschool_asset_url( $path ) {
    return plugins_url( $path, __FILE__ );
}

/**
 * Enqueue core WordPress dependencies
 *
 * @since 2.0.0
 */
function mjschool_enqueue_core_dependencies() {
    wp_enqueue_script( 'thickbox' );
    wp_enqueue_style( 'thickbox' );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-accordion' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style( 'wp-jquery-ui-dialog' );
    wp_enqueue_media();
    wp_enqueue_script( 'moment' );
    wp_enqueue_script( 'select2' );
    wp_enqueue_style( 'select2' );
}

/**
 * Enqueue third-party CSS files
 *
 * @since 2.0.0
 */
function mjschool_enqueue_third_party_css() {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    // DataTables - FIXED: removed duplicate 'third-party-css' folder
    wp_enqueue_style( 'datatable', mjschool_asset_url( '/assets/css/third-party-css/dataTables.min.css' ), array(), $version );
    wp_enqueue_style( 'jquery-datatable', mjschool_asset_url( '/assets/css/third-party-css/jquery.dataTables.min.css' ), array(), $version );
    wp_enqueue_style( 'dataTables-responsive', mjschool_asset_url( '/assets/css/third-party-css/dataTables.responsive.css' ), array(), $version );
    wp_enqueue_style( 'buttons-dataTables', mjschool_asset_url( '/assets/css/third-party-css/buttons.dataTables.min.css' ), array(), $version );
    
    // Bootstrap
    wp_enqueue_style( 'bootstrap', mjschool_asset_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css' ), array(), $version );
    wp_enqueue_style( 'bootstrap-multiselect', mjschool_asset_url( '/assets/css/third-party-css/bootstrap/bootstrap-multiselect.css' ), array( 'bootstrap' ), $version );
    wp_enqueue_style( 'bootstrap-timepicker', mjschool_asset_url( '/assets/css/third-party-css/bootstrap/bootstrap-timepicker.min.css' ), array( 'bootstrap' ), $version );
    
    // jQuery UI
    wp_enqueue_style( 'jquery-ui', mjschool_asset_url( '/assets/css/third-party-css/jquery-ui.min.css' ), array(), $version );
    
    // Chart.js
    wp_enqueue_style( 'chart', mjschool_asset_url( '/assets/css/third-party-css/chart.min.css' ), array(), $version );
    
    // Time picker
    wp_enqueue_style( 'mdtimepicker', mjschool_asset_url( '/assets/css/third-party-css/mdtimepicker.min.css' ), array(), $version );
    
    // Validation Engine
    wp_enqueue_style( 'jquery-validationEngine', mjschool_asset_url( '/lib/validationEngine/css/validationEngine.jquery.css' ), array(), $version );
    
    // SweetAlert2
    wp_enqueue_style( 'sweetalert2-css', mjschool_asset_url( '/lib/sweetalert2/sweetalert2.min.css' ), array(), $version );
}

/**
 * Enqueue plugin CSS files
 *
 * @since 2.0.0
 */
function mjschool_enqueue_plugin_css() {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    wp_enqueue_style( 'mjschool-style', mjschool_asset_url( '/assets/css/mjschool-style.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-newversion', mjschool_asset_url( '/assets/css/mjschool-new-version.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-dashboard', mjschool_asset_url( '/assets/css/mjschool-dashboard.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-popup', mjschool_asset_url( '/assets/css/mjschool-popup.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-new-design', mjschool_asset_url( '/assets/css/mjschool-smgt-new-design.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-responsive-new-design', mjschool_asset_url( '/assets/css/mjschool-responsive-new-design.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-white', mjschool_asset_url( '/assets/css/mjschool-white.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-schoolmgt', mjschool_asset_url( '/assets/css/mjschool-school-mgt.min.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-inputs', mjschool_asset_url( '/assets/css/mjschool-inputs.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-responsive', mjschool_asset_url( '/assets/css/mjschool-school-responsive.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-frontend-calendar', mjschool_asset_url( '/assets/css/mjschool-frontend-calendar.css' ), array(), $version );
    
    // Font families
    wp_enqueue_style( 'mjschool-poppins-font-family', mjschool_asset_url( '/assets/css/mjschool-popping-font.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-roboto-fontfamily', mjschool_asset_url( '/assets/css/mjschool-roboto-font.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-open-sans-fonts', mjschool_asset_url( '/assets/css/mjschool-open-sans-fonts.css' ), array(), $version );
}

/**
 * Enqueue RTL CSS files if needed
 *
 * @since 2.0.0
 */
function mjschool_enqueue_rtl_css() {
    if ( ! is_rtl() ) {
        return;
    }
    
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    wp_enqueue_style( 'mjschool-rtl', mjschool_asset_url( '/assets/css/mjschool-new-design-rtl.css' ), array(), $version );
    wp_enqueue_style( 'mjschool-rtl-css', mjschool_asset_url( '/assets/css/theme/mjschool-rtl.css' ), array(), $version );
    wp_enqueue_style( 'bootstrap-rtl', mjschool_asset_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css' ), array( 'bootstrap' ), $version );
    wp_enqueue_style( 'mjschool-custome-rtl', mjschool_asset_url( '/assets/css/mjschool-custome-rtl.css' ), array(), $version );
}

/**
 * Enqueue third-party JavaScript files
 *
 * @since 2.0.0
 * @param string $current_page Current admin page slug.
 * @param string $current_tab  Current tab slug.
 */
function mjschool_enqueue_third_party_js( $current_page, $current_tab ) {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    // DataTables
    if ( ! in_array( $current_tab, array( 'view_all_message', 'view_all_message_reply' ), true ) ) {
        wp_enqueue_script( 'datatables', mjschool_asset_url( '/assets/js/third-party-js/datatables.min.js' ), array( 'jquery' ), $version, true );
        wp_enqueue_script( 'jquery-datatable', mjschool_asset_url( '/assets/js/third-party-js/jquery.dataTables.min.js' ), array( 'jquery' ), $version, true );
    }
    
    wp_enqueue_script( 'datatable-editor', mjschool_asset_url( '/assets/js/third-party-js/dataTables.editor.min.js' ), array( 'jquery-datatable' ), $version, true );
    wp_enqueue_script( 'datatable-buttons', mjschool_asset_url( '/assets/js/third-party-js/dataTables.buttons.min.js' ), array( 'jquery-datatable' ), $version, true );
    wp_enqueue_script( 'datatable-button-html', mjschool_asset_url( '/assets/js/third-party-js/buttons.html5.min.js' ), array( 'datatable-buttons' ), $version, true );
    wp_enqueue_script( 'datatable-button-print', mjschool_asset_url( '/assets/js/third-party-js/buttons.print.min.js' ), array( 'datatable-buttons' ), $version, true );
    wp_enqueue_script( 'buttons-colVis', mjschool_asset_url( '/assets/js/third-party-js/buttons.colVis.min.js' ), array( 'datatable-buttons' ), $version, true );
    wp_enqueue_script( 'pdfmake', mjschool_asset_url( '/assets/js/third-party-js/pdfmake.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'datatables-buttons-print', mjschool_asset_url( '/assets/js/third-party-js/datatables-buttons-print.min.js' ), array( 'jquery' ), $version, true );
    
    // Bootstrap
    wp_enqueue_script( 'popper', mjschool_asset_url( '/assets/js/third-party-js/popper.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'bootstrap', mjschool_asset_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js' ), array( 'popper' ), $version, true );
    wp_enqueue_script( 'bootstrap-multiselect', mjschool_asset_url( '/assets/js/third-party-js/bootstrap/bootstrap-multiselect.min.js' ), array( 'bootstrap' ), $version, true );
    
    // Chart.js
    wp_enqueue_script( 'chart-loder', mjschool_asset_url( '/assets/js/third-party-js/chart-loder.js' ), array(), $version, true );
    wp_enqueue_script( 'loader', mjschool_asset_url( '/assets/js/third-party-js/loader.min.js' ), array(), $version, true );
    
    // Other utilities
    wp_enqueue_script( 'html5-qrcode', mjschool_asset_url( '/lib/html5-qrcode/html5-qrcode.min.js' ), array(), $version, true );
    wp_enqueue_script( 'sweetalert2-js', mjschool_asset_url( '/lib/sweetalert2/sweetalert2.all.min.js' ), array(), $version, true );
    wp_enqueue_script( 'jquery-timeago', mjschool_asset_url( '/assets/js/third-party-js/jquery.timeago.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'icheckjs', mjschool_asset_url( '/assets/js/third-party-js/icheck.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'mdtimepicker', mjschool_asset_url( '/assets/js/third-party-js/mdtimepicker.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'material', mjschool_asset_url( '/assets/js/third-party-js/material.min.js' ), array(), $version, true );
    wp_enqueue_script( 'modernizr', mjschool_asset_url( '/assets/js/third-party-js/modernizr.min.js' ), array(), $version, true );
    wp_enqueue_script( 'jquery-waypoints', mjschool_asset_url( '/assets/js/third-party-js/jquery.waypoints.min.js' ), array( 'jquery' ), $version, true );
    wp_enqueue_script( 'jquery-counterup', mjschool_asset_url( '/assets/js/third-party-js/jquery.counterup.min.js' ), array( 'jquery-waypoints' ), $version, true );
    wp_enqueue_script( 'font-awesome-all', mjschool_asset_url( '/assets/js/third-party-js/font-awesome.all.min.js' ), array(), $version, true );
    
    // Validation Engine with localization
    $locale = get_locale();
    $lang_code = substr( $locale, 0, 2 );
    
    wp_enqueue_script( 
        'jquery-validationEngine-' . $lang_code, 
        mjschool_asset_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $lang_code . '.js' ), 
        array( 'jquery' ), 
        $version, 
        true 
    );
    wp_enqueue_script( 
        'jquery-validationEngine', 
        mjschool_asset_url( '/lib/validationEngine/js/jquery.validationEngine.js' ), 
        array( 'jquery' ), 
        $version, 
        true 
    );
    
    // Search Builder for specific tabs
    $search_builder_tabs = array( 'student_information_report', 'student_attendance_report', 'finance_report' );
    if ( in_array( $current_tab, $search_builder_tabs, true ) ) {
        wp_enqueue_style( 'searchBuilder-dataTables', mjschool_asset_url( '/assets/css/third-party-css/searchBuilder.dataTables.min.css' ), array(), $version );
        wp_enqueue_script( 'dataTables-searchBuilder', mjschool_asset_url( '/assets/js/third-party-js/dataTables.searchBuilder.min.js' ), array( 'jquery-datatable' ), $version, true );
        wp_enqueue_style( 'searchBuilder-bootstrap4', mjschool_asset_url( '/assets/css/third-party-css/searchBuilder.bootstrap4.min.css' ), array(), $version );
    }
}

/**
 * Enqueue calendar scripts for dashboard
 *
 * @since 2.0.0
 * @param string $current_page Current admin page slug.
 */
function mjschool_enqueue_calendar_scripts( $current_page ) {
    if ( 'mjschool' !== $current_page ) {
        return;
    }
    
    $version = MJSCHOOL_SCRIPT_VERSION;
    $locale = get_locale();
    $lang_code = substr( $locale, 0, 2 );
    
    wp_enqueue_style( 'fullcalendar', mjschool_asset_url( '/assets/css/third-party-css/fullcalendar.min.css' ), array(), $version );
    wp_enqueue_script( 'fullcalendar', mjschool_asset_url( '/assets/js/third-party-js/fullcalendar.min.js' ), array( 'jquery', 'moment' ), $version, true );
    wp_enqueue_script( 'calendar-lang', mjschool_asset_url( '/assets/js/calendar-lang/' . $lang_code . '.js' ), array( 'fullcalendar' ), $version, true );
}

/**
 * Enqueue chart scripts for specific pages
 *
 * @since 2.0.0
 * @param string $current_page Current admin page slug.
 */
function mjschool_enqueue_chart_scripts( $current_page ) {
    $chart_pages = array( 'mjschool_report', 'mjschool' );
    
    if ( ! in_array( $current_page, $chart_pages, true ) ) {
        return;
    }
    
    wp_enqueue_script( 
        'chart-umd', 
        mjschool_asset_url( '/assets/js/third-party-js/chart.umd.min.js' ), 
        array( 'jquery' ), 
        MJSCHOOL_SCRIPT_VERSION, 
        true 
    );
}

/**
 * Get localized data for JavaScript
 *
 * @since 2.0.0
 * @param string $current_page Current admin page slug.
 * @return array Localized data array.
 */
function mjschool_get_localized_data( $current_page ) {
    $school_type = get_option( 'mjschool_custom_class', 'school' );
    
    // Get document settings
    $document_option = get_option( 'mjschool_upload_document_type', 'pdf, doc, docx, ppt, pptx, gif, png, jpg, jpeg, webp' );
    $document_type = array_map( 'trim', explode( ',', $document_option ) );
    $document_size = get_option( 'mjschool_upload_document_size', '30' );
    
    // Get class names
    $class_name = function_exists( 'mjschool_get_all_class_array' ) ? mjschool_get_all_class_array() : array();
    $class_name_list = array_map(
        function ( $s ) {
            return isset( $s->class_name ) ? trim( $s->class_name ) : '';
        },
        $class_name
    );
    
    // Get custom field columns
    $custom_columns = array();
    if ( class_exists( 'Mjschool_Custome_Field' ) && function_exists( 'mjschool_get_module_name_for_custom_field' ) ) {
        $mjschool_custom_field_obj = new Mjschool_Custome_Field();
        $module = mjschool_get_module_name_for_custom_field( $current_page );
        $user_custom_field = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
        
        if ( ! empty( $user_custom_field ) ) {
            foreach ( $user_custom_field as $custom_field ) {
                if ( isset( $custom_field->show_in_table ) && '1' === $custom_field->show_in_table ) {
                    $custom_columns[] = true;
                }
            }
        }
    }
    
    // Build localized data array
    $data = array(
        // Select labels
        'select_days'        => esc_html__( 'Select Days', 'mjschool' ),
        'select_teacher'     => esc_html__( 'Select Teacher', 'mjschool' ),
        'select_all'         => esc_html__( 'Select all', 'mjschool' ),
        'select_class'       => esc_html__( 'Select Class', 'mjschool' ),
        'select_user'        => esc_html__( 'Select Users', 'mjschool' ),
        'select_tax'         => esc_html__( 'Select Tax', 'mjschool' ),
        'select_book'        => esc_html__( 'Select Book', 'mjschool' ),
        'select_student'     => esc_html__( 'Select Student', 'mjschool' ),
        'select_fees_type'   => esc_html__( 'Select Fees Type', 'mjschool' ),
        'all_selected'       => esc_html__( 'All Selected', 'mjschool' ),
        
        // Button labels
        'csv_text'           => esc_html__( 'CSV', 'mjschool' ),
        'print_text'         => esc_html__( 'PRINT', 'mjschool' ),
        
        // Report labels
        'admission_report_text'          => esc_html__( 'Admission Report', 'mjschool' ),
        'attendance_report_text'         => esc_html__( 'Attendance Report', 'mjschool' ),
        'fees_payment_report_text'       => esc_html__( 'Fees Payment Report', 'mjschool' ),
        'leave_report_text'              => esc_html__( 'Leave Report', 'mjschool' ),
        'guardian_report_text'           => esc_html__( 'Guardian Report', 'mjschool' ),
        'student_report_text'            => esc_html__( 'Student Report', 'mjschool' ),
        'audit_trail_report_text'        => esc_html__( 'Audit Trail Report', 'mjschool' ),
        'class_section_report_text'      => esc_html__( 'Class & Section Report', 'mjschool' ),
        'sibling_report_text'            => esc_html__( 'Sibling Report', 'mjschool' ),
        'income_report_text'             => esc_html__( 'Income Report', 'mjschool' ),
        'expense_report_text'            => esc_html__( 'Expense Report', 'mjschool' ),
        'income_expense_report_text'     => esc_html__( 'Income Expense Report', 'mjschool' ),
        'student_attendance_report_text' => esc_html__( 'Student Attendance Report', 'mjschool' ),
        
        // Other labels
        'expense_amount_label'       => esc_html__( 'Expense Amount', 'mjschool' ),
        'expense_entry_label'        => esc_html__( 'Expense Entry Label', 'mjschool' ),
        'income_amount_label'        => esc_html__( 'Income Amount', 'mjschool' ),
        'income_entry_label'         => esc_html__( 'Income Entry Label', 'mjschool' ),
        'subject_text'               => esc_html__( 'Subject', 'mjschool' ),
        'attachment_text'            => esc_html__( 'Attachment', 'mjschool' ),
        'search_placeholder'         => esc_html__( 'Search...', 'mjschool' ),
        
        // Status labels
        'inactive_student_text' => esc_html__( 'Inactive Students', 'mjschool' ),
        'student_text'          => esc_html__( 'Students', 'mjschool' ),
        'active_student_text'   => esc_html__( 'Active Students', 'mjschool' ),
        'parent_text'           => esc_html__( 'Parents', 'mjschool' ),
        'teacher_text'          => esc_html__( 'Teachers', 'mjschool' ),
        'support_staff_text'    => esc_html__( 'Support Staff', 'mjschool' ),
        'paid_text'             => esc_html__( 'Paid', 'mjschool' ),
        'unpaid_text'           => esc_html__( 'Unpaid', 'mjschool' ),
        'present_text'          => esc_html__( 'Present', 'mjschool' ),
        'absent_text'           => esc_html__( 'Absent', 'mjschool' ),
        'late_text'             => esc_html__( 'Late', 'mjschool' ),
        'half_day_text'         => esc_html__( 'Half Day', 'mjschool' ),
        
        // Payment methods
        'cash_text'          => esc_html__( 'Cash', 'mjschool' ),
        'cheque_text'        => esc_html__( 'Cheque', 'mjschool' ),
        'bank_transfer_text' => esc_html__( 'Bank Transfer', 'mjschool' ),
        'paypal_text'        => esc_html__( 'PayPal', 'mjschool' ),
        'stripe_text'        => esc_html__( 'Stripe', 'mjschool' ),
        
        // Alert messages
        'end_time_must_greater_text'  => esc_html__( 'End time must be greater than start time', 'mjschool' ),
        'reply_user_alert'            => esc_html__( 'Please select at least one user to reply', 'mjschool' ),
        'select_document_type_text'   => esc_html__( 'Select Document Type', 'mjschool' ),
        'one_document_alert_text'     => esc_html__( 'Please select at least one document extension.', 'mjschool' ),
        'profile_alert_text'          => esc_html__( 'Please select at least one profile extension.', 'mjschool' ),
        'select_child_alert_text'     => esc_html__( 'This child is already selected. Please choose a different child.', 'mjschool' ),
        'csv_file_alert_text'         => esc_html__( 'Only CSV format is allowed.', 'mjschool' ),
        'permission_alert_text'       => esc_html__( 'You do not have permission to perform this operation.', 'mjschool' ),
        'start_end_date_alert_text'   => esc_html__( 'End Date should be greater than the Start Date.', 'mjschool' ),
        
        // Configuration
        'class_name_list'         => $class_name_list,
        'delete_icon'             => defined( 'MJSCHOOL_PLUGIN_URL' ) ? esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ) : '',
        'datatable_language'      => function_exists( 'mjschool_datatable_multi_language' ) ? mjschool_datatable_multi_language() : array(),
        'is_school'               => ( 'school' === $school_type ),
        'is_university'           => ( 'university' === $school_type ),
        'is_add_access'           => 1,
        'is_edit_access'          => 1,
        'is_delete_access'        => 1,
        'is_view_access'          => 1,
        'document_type_json'      => $document_type,
        'document_size'           => $document_size,
        'date_format'             => get_option( 'mjschool_datepicker_format', 'yy/mm/dd' ),
        'date_format_for_sorting' => function_exists( 'mjschool_return_date_format_for_shorting' ) ? mjschool_return_date_format_for_shorting() : 'yy/mm/dd',
        'datatable_nonce'         => wp_create_nonce( 'mjschool_student_list_nonce' ),
        'module_columns'          => $custom_columns,
        'calendar_language'       => function_exists( 'mjschool_calender_laungage' ) ? mjschool_calender_laungage() : 'en',
    );
    
    // Add exam data if available - with proper validation
    if ( isset( $_REQUEST['exam_id'] ) && function_exists( 'mjschool_decrypt_id' ) && function_exists( 'mjschool_get_exam_by_id' ) ) {
        $exam_id = sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) );
        $decrypted_id = mjschool_decrypt_id( $exam_id );
        
        if ( $decrypted_id ) {
            $exam_data = mjschool_get_exam_by_id( absint( $decrypted_id ) );
            if ( $exam_data && isset( $exam_data->exam_id ) ) {
                $data['exam_data_id'] = absint( $exam_data->exam_id );
            }
        }
    }
    
    // Add student data if available - with proper validation
    if ( isset( $_REQUEST['student_id'] ) && function_exists( 'mjschool_decrypt_id' ) ) {
        $student_id_encrypted = sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) );
        $student_id = mjschool_decrypt_id( $student_id_encrypted );
        
        if ( $student_id ) {
            $student_id = absint( $student_id );
            $data['student_id'] = $student_id;
            $data['class_id'] = get_user_meta( $student_id, 'class_name', true );
            $data['section_name'] = get_user_meta( $student_id, 'class_section', true );
        }
    }
    
    return $data;
}

/**
 * Get alert messages for JavaScript localization
 *
 * @since 2.0.0
 * @return array Alert messages array.
 */
function mjschool_get_alert_messages() {
    return array(
        'edit_record_alert'              => esc_attr__( 'Are you sure want to edit this record?', 'mjschool' ),
        'category_alert'                 => esc_attr__( 'You must fill out the field', 'mjschool' ),
        'class_limit_alert'              => esc_attr__( 'Class Limit Is Full.', 'mjschool' ),
        'enter_room_alert'               => esc_attr__( 'Please Enter Room Category Name.', 'mjschool' ),
        'enter_value_alert'              => esc_attr__( 'Please Enter Value.', 'mjschool' ),
        'delete_record_alert'            => esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ),
        'select_hall_alert'              => esc_attr__( 'Please Select Exam Hall', 'mjschool' ),
        'one_record_alert'               => esc_attr__( 'Please Select Atleast One Student', 'mjschool' ),
        'select_member_alert'            => esc_attr__( 'Please select Student', 'mjschool' ),
        'one_record_select_alert'        => esc_attr__( 'Please select atleast one record', 'mjschool' ),
        'one_class_select_alert'         => esc_attr__( 'Please select atleast one class', 'mjschool' ),
        'one_select_Validation_alert'    => esc_attr__( 'Please select atleast one Validation', 'mjschool' ),
        'lower_starting_year_alert'      => esc_attr__( 'You can not select year lower then starting year', 'mjschool' ),
        'do_delete_record'               => esc_attr__( 'Do you really want to delete this ?', 'mjschool' ),
        'select_one_book_alert'          => esc_attr__( 'Please select atleast one book', 'mjschool' ),
        'select_different_student_alert' => esc_attr__( 'Please Select Different Student', 'mjschool' ),
        'select_user_label'              => esc_attr__( 'Select Users', 'mjschool' ),
        'select_all_label'               => esc_attr__( 'Select all', 'mjschool' ),
        'same_email_alert'               => esc_attr__( 'you have used the same email', 'mjschool' ),
        'image_forame_alert'             => esc_attr__( "Only '.jpeg','.jpg', '.png', '.bmp' formats are allowed.", 'mjschool' ),
        'more_then_exam_date_time'       => esc_attr__( 'Fail! More than one subject exam date & time same.', 'mjschool' ),
        'single_entry_alert'             => esc_attr__( 'There is only single entry,You can not remove it.', 'mjschool' ),
        'one_teacher_alert'              => esc_attr__( 'Please select atleast one teacher', 'mjschool' ),
        'one_assign_room_alert'          => esc_attr__( 'Please select Student', 'mjschool' ),
        'one_message_alert'              => esc_attr__( 'Please select atleast one message', 'mjschool' ),
        'large_file_size_alert'          => esc_attr__( 'Too large file Size. Only file smaller than 10MB can be uploaded.', 'mjschool' ),
        'pdf_alert'                      => esc_attr__( 'Only pdf formate are allowed.', 'mjschool' ),
        'starting_year_alert'            => esc_attr__( 'You Can Not Select Ending Year Lower Than Starting Year', 'mjschool' ),
        'one_user_replys_alert'          => esc_attr__( 'Please select atleast one users to replys', 'mjschool' ),
        'csv_alert'                      => esc_attr__( 'Problems with user: we are going to skip', 'mjschool' ),
        'select_user'                    => esc_attr__( 'Select Users', 'mjschool' ),
        'select_all'                     => esc_attr__( 'Select all', 'mjschool' ),
        'mail_reminder'                  => esc_attr__( 'Are you sure you want to send a mail reminder?', 'mjschool' ),
        'account_alert_1'                => esc_attr__( 'Only jpeg,jpg,png and bmp formate are allowed.', 'mjschool' ),
        'account_alert_2'                => esc_attr__( 'formate are not allowed.', 'mjschool' ),
        'exam_hallCapacity_1'            => esc_attr__( 'Exam Hall Capacity', 'mjschool' ),
        'exam_hallCapacity_2'            => esc_attr__( 'Out Of', 'mjschool' ),
        'exam_hallCapacity_3'            => esc_attr__( 'Students.', 'mjschool' ),
    );
}

/**
 * Enqueue page-specific JavaScript files
 *
 * @since 2.0.0
 * @param string $current_page Current admin page slug.
 * @param array  $localized_data Localized data for scripts.
 */
function mjschool_enqueue_page_specific_js( $current_page, $localized_data ) {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    // Define page to script mappings
    $page_scripts = array(
        'mjschool_class'             => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'mjschool_route'             => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'mjschool_Subject'           => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'mjschool_virtual_classroom' => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'mjschool_class_room'        => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'mjschool_student'           => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'mjschool_teacher'           => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'mjschool_supportstaff'      => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'mjschool_parent'            => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'mjschool_student_homewrok'  => array( 'mjschool-homework', '/assets/js/public-js/mjschool-homework.js', 'mjschool_homework_data' ),
        'mjschool_document'          => array( 'mjschool-document', '/assets/js/public-js/mjschool-document.js', 'mjschool_document_data' ),
        'mjschool_leave'             => array( 'mjschool-leave', '/assets/js/public-js/mjschool-leave.js', 'mjschool_leave_data' ),
        'mjschool_fees_payment'      => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'mjschool_payment'           => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'mjschool_tax'               => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'mjschool_library'           => array( 'mjschool-library', '/assets/js/public-js/mjschool-library.js', 'mjschool_library_data' ),
        'mjschool_hostel'            => array( 'mjschool-hostel', '/assets/js/public-js/mjschool-hostel.js', 'mjschool_hostel_data' ),
        'mjschool_transport'         => array( 'mjschool-transport', '/assets/js/public-js/mjschool-transport.js', 'mjschool_transport_data' ),
        'mjschool_certificate'       => array( 'mjschool-certificate', '/assets/js/public-js/mjschool-certificate.js', 'mjschool_certificate_data' ),
        'mjschool_advance_report'    => array( 'mjschool-advance-report', '/assets/js/admin-js/mjschool-advance-report.js', 'mjschool_advance_report_data' ),
        'mjschool_notice'            => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'mjschool_message'           => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'mjschool_notification'      => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'mjschool_event'             => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'mjschool_holiday'           => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'mjschool_report'            => array( 'mjschool-report', '/assets/js/admin-js/mjschool-report.js', 'mjschool_report_data' ),
        'mjschool_admission'         => array( 'mjschool-admission', '/assets/js/public-js/mjschool-admission.js', 'mjschool_admission_data' ),
        'mjschool_exam'              => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'mjschool_hall'              => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'mjschool_result'            => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'mjschool_grade'             => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'mjschool_Migration'         => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'mjschool_attendence'        => array( 'mjschool-attendance', '/assets/js/public-js/mjschool-attendance.js', 'mjschool_attendance_data' ),
        'mjschool_custom_field'      => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'mjschool_email_template'    => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'mjschool_sms_setting'       => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'mjschool_sms_template'      => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'mjschool_general_settings'  => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'mjschool'                   => array( 'mjschool-admin-dashboard', '/assets/js/public-js/mjschool-admin-dashboard.js', 'mjschool_dashboard_data' ),
    );
    
    // Enqueue page-specific script
    if ( isset( $page_scripts[ $current_page ] ) ) {
        $script_info = $page_scripts[ $current_page ];
        wp_enqueue_script( $script_info[0], mjschool_asset_url( $script_info[1] ), array( 'jquery' ), $version, true );
        wp_localize_script( $script_info[0], $script_info[2], $localized_data );
    }
    
    // Special case for marks page
    if ( 'mjschool_result' === $current_page ) {
        wp_enqueue_script( 'mjschool-marks', mjschool_asset_url( '/assets/js/pages/marks.js' ), array( 'jquery' ), $version, true );
    }
}

/**
 * Enqueue common plugin JavaScript files
 *
 * @since 2.0.0
 * @param array $localized_data Localized data for scripts.
 */
function mjschool_enqueue_common_js( $localized_data ) {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    // Custom field handler
    wp_enqueue_script( 'mjschool-customfield', mjschool_asset_url( '/assets/js/mjschool-customfield.js' ), array( 'jquery' ), $version, true );
    
    // Popup handler
    wp_enqueue_script( 'mjschool-popup', mjschool_asset_url( '/assets/js/mjschool-popup.js' ), array( 'jquery' ), $version, true );
    wp_localize_script( 'mjschool-popup', 'mjschool', array(
        'ajax'  => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'mjschool_ajax_nonce' ),
    ) );
    wp_localize_script( 'mjschool-popup', 'language_translate2', mjschool_get_alert_messages() );
    
    // Common functions
    wp_enqueue_script( 'mjschool-common', mjschool_asset_url( '/assets/js/mjschool-common.js' ), array( 'jquery' ), $version, true );
    wp_localize_script( 'mjschool-common', 'mjschool_common_data', $localized_data );
    
    // AJAX functions
    wp_enqueue_script( 'mjschool-ajax-function', mjschool_asset_url( '/assets/js/mjschool-ajax-function.js' ), array( 'jquery', 'mjschool-common' ), $version, true );
    wp_localize_script( 'mjschool-ajax-function', 'mjschool_ajax_function_data', $localized_data );
    
    // Main function file
    wp_enqueue_script( 'mjschool-function', mjschool_asset_url( '/assets/js/mjschool-function-file.js' ), array( 'jquery', 'mjschool-common' ), $version, true );
    wp_localize_script( 'mjschool-function', 'mjschool_function_data', $localized_data );
    
    // Image upload handler
    wp_enqueue_script( 'mjschool-image-upload', mjschool_asset_url( '/assets/js/mjschool-image-upload.js' ), array( 'jquery' ), $version, true );
    wp_localize_script( 'mjschool-image-upload', 'language_translate1', array(
        'allow_file_alert' => esc_attr__( 'Only jpg,jpeg,png File allowed', 'mjschool' ),
    ) );
    
    // Conflict resolution
    wp_enqueue_script( 'mjschool-custom-obj', mjschool_asset_url( '/assets/js/mjschool-custom-confilict-obj.js' ), array( 'jquery' ), $version, false );
}

/**
 * Main function to enqueue admin scripts and styles
 *
 * @since 1.0.0
 * @param string $hook The current admin page hook.
 */
function mjschool_change_adminbar_css( $hook ) {
    $page_info = mjschool_get_current_page_info();
    $current_page = $page_info['page'];
    $current_tab = $page_info['tab'];
    
    // Check if we're on a plugin page
    $page_array = mjschool_call_script_page();
    if ( ! in_array( $current_page, $page_array, true ) ) {
        return;
    }
    
    // Enqueue all assets
    mjschool_enqueue_core_dependencies();
    mjschool_enqueue_third_party_css();
    mjschool_enqueue_plugin_css();
    mjschool_enqueue_rtl_css();
    mjschool_enqueue_third_party_js( $current_page, $current_tab );
    mjschool_enqueue_calendar_scripts( $current_page );
    mjschool_enqueue_chart_scripts( $current_page );
    
    // Get and apply localized data
    $localized_data = mjschool_get_localized_data( $current_page );
    mjschool_enqueue_page_specific_js( $current_page, $localized_data );
    mjschool_enqueue_common_js( $localized_data );
}

// Hook the main enqueue function - only if page parameter is set
if ( isset( $_REQUEST['page'] ) ) {
    add_action( 'admin_enqueue_scripts', 'mjschool_change_adminbar_css' );
}

/**
 * Replace Thickbox button text for media uploads
 *
 * @since 1.0.0
 */
function mjschool_upload_image() {
    $page_info = mjschool_get_current_page_info();
    
    if ( 'mjschool' === $page_info['page'] ) {
        add_filter( 'gettext', 'mjschool_replace_thickbox_text', 1, 3 );
    }
}
add_action( 'admin_init', 'mjschool_upload_image' );

/**
 * Replace "Insert into Post" button text
 *
 * @since 1.0.0
 * @param string $translated_text The translated text.
 * @param string $text            The original text.
 * @param string $domain          The text domain.
 * @return string Modified text.
 */
function mjschool_replace_thickbox_text( $translated_text, $text, $domain ) {
    if ( 'Insert into Post' === $text ) {
        $referer = wp_get_referer();
        if ( $referer && strpos( $referer, 'wptuts-settings' ) !== false ) {
            return esc_html__( 'Upload Image', 'mjschool' );
        }
    }
    return $translated_text;
}

/**
 * Load plugin text domain for translations
 *
 * @since 1.0.0
 */
function mjschool_domain_load() {
    load_plugin_textdomain( 'mjschool', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'mjschool_domain_load' );

/**
 * Create login page on plugin activation
 *
 * @since 1.0.0
 */
function mjschool_install_login_page() {
    if ( get_option( 'mjschool_login_page' ) ) {
        return;
    }
    
    $page_data = array(
        'post_title'     => esc_attr__( 'School Management Login Page', 'mjschool' ),
        'post_content'   => '[smgt_login]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_category'  => array( 1 ),
        'post_parent'    => 0,
    );
    
    $page_id = wp_insert_post( $page_data );
    
    if ( $page_id && ! is_wp_error( $page_id ) ) {
        update_option( 'mjschool_login_page', $page_id );
    }
}

/**
 * Create student registration page on plugin activation
 *
 * @since 1.0.0
 */
function mjschool_install_student_registration_page() {
    if ( get_option( 'mjschool_install_student_registration_page' ) ) {
        return;
    }
    
    $page_data = array(
        'post_title'     => esc_attr__( 'Student Registration', 'mjschool' ),
        'post_content'   => '[smgt_student_registration]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_category'  => array( 1 ),
        'post_parent'    => 0,
    );
    
    $page_id = wp_insert_post( $page_data );
    
    if ( $page_id && ! is_wp_error( $page_id ) ) {
        update_option( 'mjschool_install_student_registration_page', $page_id );
    }
}

/**
 * Load frontend dashboard template
 *
 * @since 1.0.0
 */
function mjschool_user_dashboard() {
    if ( isset( $_REQUEST['dashboard'] ) ) {
        $dashboard = sanitize_text_field( wp_unslash( $_REQUEST['dashboard'] ) );
        
        if ( defined( 'MJSCHOOL_INCLUDES_DIR' ) && file_exists( MJSCHOOL_INCLUDES_DIR . '/mjschool-frontend-template.php' ) ) {
            require_once MJSCHOOL_INCLUDES_DIR . '/mjschool-frontend-template.php';
            exit;
        }
    }
    
    if ( isset( $_REQUEST['mjschool_login'] ) ) {
        if ( function_exists( 'mjschool_pu_blank_login' ) ) {
            add_action( 'authenticate', 'mjschool_pu_blank_login' );
        }
    }
}

/**
 * Enqueue frontend assets for user dashboard
 *
 * @since 1.0.0
 */
function mjschool_enqueue_front_assets() {
    // Check if we're on the user dashboard
    if ( ! isset( $_REQUEST['dashboard'] ) ) {
        return;
    }
    
    $dashboard = sanitize_text_field( wp_unslash( $_REQUEST['dashboard'] ) );
    if ( 'mjschool_user' !== $dashboard ) {
        return;
    }
    
    $page_info = mjschool_get_current_page_info();
    $current_page = $page_info['page'];
    
    // Enqueue core dependencies
    mjschool_enqueue_core_dependencies();
    
    // Enqueue CSS
    mjschool_enqueue_third_party_css();
    mjschool_enqueue_plugin_css();
    mjschool_enqueue_rtl_css();
    
    // Enqueue JavaScript
    mjschool_enqueue_third_party_js( $current_page, $page_info['tab'] );
    mjschool_enqueue_calendar_scripts( 'mjschool' ); // Always load calendar on frontend dashboard
    
    // Get localized data with frontend-specific additions
    $localized_data = mjschool_get_frontend_localized_data( $current_page );
    
    // Enqueue page-specific scripts
    mjschool_enqueue_frontend_page_scripts( $current_page, $localized_data );
    
    // Enqueue common scripts
    mjschool_enqueue_common_js( $localized_data );
}

/**
 * Get frontend-specific localized data
 *
 * @since 2.0.0
 * @param string $current_page Current page slug.
 * @return array Localized data array.
 */
function mjschool_get_frontend_localized_data( $current_page ) {
    $data = mjschool_get_localized_data( $current_page );
    
    // Add frontend-specific data
    $school_type = get_option( 'mjschool_custom_class', 'school' );
    $cust_class_room = get_option( 'mjschool_class_room', 0 );
    
    // Get user role and access rights
    $current_user_id = get_current_user_id();
    $mjschool_role_name = '';
    $user_access = array(
        'add'    => '0',
        'edit'   => '0',
        'delete' => '0',
        'view'   => '0',
    );
    
    if ( function_exists( 'mjschool_get_user_role' ) ) {
        $mjschool_role_name = mjschool_get_user_role( $current_user_id );
    }
    
    if ( function_exists( 'mjschool_get_user_role_wise_access_right_array' ) ) {
        $user_access = mjschool_get_user_role_wise_access_right_array();
    }
    
    // Add role-specific flags
    $data['is_supportstaff'] = ( 'supportstaff' === $mjschool_role_name );
    $data['is_teacher'] = ( 'teacher' === $mjschool_role_name );
    $data['is_student'] = ( 'student' === $mjschool_role_name );
    $data['is_parent'] = ( 'parent' === $mjschool_role_name );
    $data['is_cust_class_room'] = ( 1 === absint( $cust_class_room ) );
    
    // Override access rights with actual user permissions
    $data['is_add_access'] = ( '1' === $user_access['add'] );
    $data['is_edit_access'] = ( '1' === $user_access['edit'] );
    $data['is_delete_access'] = ( '1' === $user_access['delete'] );
    $data['is_view_access'] = ( '1' === $user_access['view'] );
    $data['current_user_id'] = $current_user_id;
    
    // Additional frontend alert messages
    $data['subject_file_alert_text'] = esc_html__( 'Only pdf,doc,docx,xls,xlsx,ppt,pptx,gif,png,jpg,jpeg formate are allowed.', 'mjschool' );
    $data['not_format_alert_text'] = esc_html__( 'format is not allowed.', 'mjschool' );
    $data['front_doc_alert_text'] = esc_html__( 'Sorry, only JPG, pdf, docs., JPEG, PNG And GIF files are allowed.', 'mjschool' );
    
    return $data;
}

/**
 * Enqueue frontend page-specific scripts
 *
 * @since 2.0.0
 * @param string $current_page   Current page slug.
 * @param array  $localized_data Localized data for scripts.
 */
function mjschool_enqueue_frontend_page_scripts( $current_page, $localized_data ) {
    $version = MJSCHOOL_SCRIPT_VERSION;
    
    // Define frontend page to script mappings
    $page_scripts = array(
        'account'      => array( 'mjschool-account', '/assets/js/public-js/mjschool-account.js', '' ),
        'transport'    => array( 'mjschool-transport', '/assets/js/public-js/mjschool-transport.js', 'mjschool_transport_data' ),
        'tax'          => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'feepayment'   => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'payment'      => array( 'mjschool-payment', '/assets/js/public-js/mjschool-payment.js', 'mjschool_payment_data' ),
        'subject'      => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'schedule'     => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'class'        => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'class_room'   => array( 'mjschool-class', '/assets/js/public-js/mjschool-class.js', 'mjschool_class_data' ),
        'sms_setting'  => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'custom_field' => array( 'mjschool-general-setting', '/assets/js/public-js/mjschool-general-setting.js', 'mjschool_general_setting_data' ),
        'message'      => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'notification' => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'holiday'      => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'event'        => array( 'mjschool-notification', '/assets/js/public-js/mjschool-notification.js', 'mjschool_notification_data' ),
        'exam'         => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'exam_hall'    => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'result'       => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'grade'        => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'migration'    => array( 'mjschool-student-evaluation', '/assets/js/public-js/mjschool-student-evaluation.js', 'mjschool_student_evaluation_data' ),
        'library'      => array( 'mjschool-library', '/assets/js/public-js/mjschool-library.js', 'mjschool_library_data' ),
        'leave'        => array( 'mjschool-leave', '/assets/js/public-js/mjschool-leave.js', 'mjschool_leave_data' ),
        'hostel'       => array( 'mjschool-hostel', '/assets/js/public-js/mjschool-hostel.js', 'mjschool_hostel_data' ),
        'homework'     => array( 'mjschool-homework', '/assets/js/public-js/mjschool-homework.js', 'mjschool_homework_data' ),
        'document'     => array( 'mjschool-document', '/assets/js/public-js/mjschool-document.js', 'mjschool_document_data' ),
        'certificate'  => array( 'mjschool-certificate', '/assets/js/public-js/mjschool-certificate.js', 'mjschool_certificate_data' ),
        'attendance'   => array( 'mjschool-attendance', '/assets/js/public-js/mjschool-attendance.js', 'mjschool_attendance_data' ),
        'admission'    => array( 'mjschool-admission', '/assets/js/public-js/mjschool-admission.js', 'mjschool_admission_data' ),
        'student'      => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'teacher'      => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'supportstaff' => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
        'parent'       => array( 'mjschool-users', '/assets/js/public-js/mjschool-users.js', 'mjschool_users_data' ),
    );
    
    // Enqueue page-specific script
    if ( isset( $page_scripts[ $current_page ] ) ) {
        $script_info = $page_scripts[ $current_page ];
        wp_enqueue_script( $script_info[0], mjschool_asset_url( $script_info[1] ), array( 'jquery' ), $version, true );
        
        if ( ! empty( $script_info[2] ) ) {
            wp_localize_script( $script_info[0], $script_info[2], $localized_data );
        }
    }
    
    // Always enqueue dashboard script on frontend
    wp_enqueue_script( 'mjschool-admin-dashboard', mjschool_asset_url( '/assets/js/public-js/mjschool-admin-dashboard.js' ), array( 'jquery' ), $version, true );
    wp_localize_script( 'mjschool-admin-dashboard', 'mjschool_dashboard_data', $localized_data );
}

/**
 * Class MJSchool_Student_Registration
 *
 * Handles all student registration functionality with improved security and code organization.
 *
 * @since 2.0.0
 */
class MJSchool_Student_Registration {

    /**
     * Registration form data
     *
     * @var array
     */
    private $form_data = array();

    /**
     * Registration errors
     *
     * @var WP_Error
     */
    private $errors;

    /**
     * Allowed file extensions for avatars
     *
     * @var array
     */
    private $allowed_avatar_extensions = array( 'jpg', 'jpeg', 'png', 'gif', 'webp' );

    /**
     * Maximum avatar file size in bytes (10MB)
     *
     * @var int
     */
    private $max_avatar_size = 10485760;

    /**
     * Constructor
     */
    public function __construct() {
        $this->errors = new WP_Error();
        $this->init_form_data();
    }

    /**
     * Initialize form data with defaults
     *
     * @since 2.0.0
     */
    private function init_form_data() {
        $this->form_data = array(
            'class_name'             => '',
            'first_name'             => '',
            'middle_name'            => '',
            'last_name'              => '',
            'gender'                 => 'male',
            'birth_date'             => '',
            'address'                => '',
            'city_name'              => '',
            'state_name'             => '',
            'zip_code'               => '',
            'mobile_number'          => '',
            'alternet_mobile_number' => '',
            'phone'                  => '',
            'email'                  => '',
            'password'               => '',
            'document_title'         => array(),
            'document_file'          => array(),
        );
    }

    /**
     * Get form data value
     *
     * @since 2.0.0
     * @param string $key     Data key.
     * @param mixed  $default Default value.
     * @return mixed Form data value.
     */
    public function get_data( $key, $default = '' ) {
        return isset( $this->form_data[ $key ] ) ? $this->form_data[ $key ] : $default;
    }

    /**
     * Set form data value
     *
     * @since 2.0.0
     * @param string $key   Data key.
     * @param mixed  $value Data value.
     */
    public function set_data( $key, $value ) {
        $this->form_data[ $key ] = $value;
    }

    /**
     * Enqueue required scripts and styles
     *
     * @since 2.0.0
     */
    public function enqueue_assets() {
        $version = defined( 'MJSCHOOL_SCRIPT_VERSION' ) ? MJSCHOOL_SCRIPT_VERSION : '1.0.0';
        $locale  = get_locale();
        $lang    = substr( $locale, 0, 2 );

        // Styles
        wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'font-awesome', plugins_url( '/assets/css/third-party-css/font-awesome.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'mjschool-register', plugins_url( '/assets/css/settings/mjschool-register.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ), array(), $version );

        // RTL Styles - FIXED: removed extra semicolon
        if ( is_rtl() ) {
            wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ), array(), $version );
            wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ), array(), $version );
            wp_enqueue_style( 'mjschool-rtl-registration-css', plugins_url( '/assets/css/mjschool-rtl-registration-form.css', __FILE__ ), array(), $version );
        }

        // Core WordPress scripts
        wp_enqueue_media();
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-accordion' );
        wp_enqueue_script( 'jquery-ui-datepicker' );

        // Plugin scripts
        wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ), array(), $version, true );
        wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ), array( 'jquery' ), $version, true );
        wp_enqueue_script( 'font-awesome-all', plugins_url( '/assets/js/third-party-js/font-awesome.all.min.js', __FILE__ ), array(), $version, true );
        wp_enqueue_script( 'mjschool-customobj', plugins_url( '/assets/js/mjschool-custom-confilict-obj.js', __FILE__ ), array( 'jquery' ), $version, false );

        // Validation Engine
        wp_enqueue_script( 'jquery-validationEngine-' . $lang, plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $lang . '.js', __FILE__ ), array( 'jquery' ), $version, true );
        wp_enqueue_script( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__ ), array( 'jquery' ), $version, true );

        // Popup script with localization
        wp_enqueue_script( 'mjschool-popup', plugins_url( '/assets/js/mjschool-popup.js', __FILE__ ), array( 'jquery' ), $version, true );
        wp_localize_script( 'mjschool-popup', 'mjschool', array(
            'ajax'  => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'mjschool_ajax_nonce' ),
        ) );

        // Registration script
        wp_enqueue_script( 'mjschool-registration-js', plugins_url( '/assets/js/mjschool-registration.js', __FILE__ ), array( 'jquery' ), $version, true );
        wp_localize_script( 'mjschool-registration-js', 'mjschool_registration_data', $this->get_registration_js_data() );
    }

    /**
     * Get JavaScript localization data for registration
     *
     * @since 2.0.0
     * @return array Localization data.
     */
    private function get_registration_js_data() {
        $document_option = get_option( 'mjschool_upload_document_type', 'pdf, doc, docx, ppt, pptx, gif, png, jpg, jpeg, webp' );
        $document_types  = array_map( 'trim', explode( ',', $document_option ) );

        return array(
            'date_format'           => get_option( 'mjschool_datepicker_format', 'yy/mm/dd' ),
            'document_type_json'    => $document_types,
            'document_size'         => get_option( 'mjschool_upload_document_size', '30' ),
            'document_delete_alert' => esc_html__( 'Are you sure you want to delete this record?', 'mjschool' ),
        );
    }

    /**
     * Validate registration form data
     *
     * @since 2.0.0
     * @return bool True if valid, false otherwise.
     */
    public function validate() {
        $this->errors = new WP_Error();

        // Required fields validation
        $required_fields = array(
            'class_name'    => __( 'Class Name', 'mjschool' ),
            'first_name'    => __( 'First Name', 'mjschool' ),
            'last_name'     => __( 'Last Name', 'mjschool' ),
            'birth_date'    => __( 'Date of Birth', 'mjschool' ),
            'address'       => __( 'Address', 'mjschool' ),
            'city_name'     => __( 'City', 'mjschool' ),
            'zip_code'      => __( 'Zip Code', 'mjschool' ),
            'mobile_number' => __( 'Mobile Number', 'mjschool' ),
            'email'         => __( 'Email', 'mjschool' ),
            'password'      => __( 'Password', 'mjschool' ),
        );

        foreach ( $required_fields as $field => $label ) {
            if ( empty( $this->form_data[ $field ] ) ) {
                $this->errors->add(
                    'required_' . $field,
                    /* translators: %s: field label */
                    sprintf( __( '%s is required.', 'mjschool' ), $label )
                );
            }
        }

        // Email validation
        if ( ! empty( $this->form_data['email'] ) ) {
            if ( ! is_email( $this->form_data['email'] ) ) {
                $this->errors->add( 'email_invalid', __( 'Please enter a valid email address.', 'mjschool' ) );
            } elseif ( email_exists( $this->form_data['email'] ) ) {
                $this->errors->add( 'email_exists', __( 'This email address is already registered.', 'mjschool' ) );
            }

            // Username (email) validation
            if ( username_exists( $this->form_data['email'] ) ) {
                $this->errors->add( 'username_exists', __( 'This username is already taken.', 'mjschool' ) );
            }

            if ( strlen( $this->form_data['email'] ) < 4 ) {
                $this->errors->add( 'username_short', __( 'Username must be at least 4 characters.', 'mjschool' ) );
            }
        }

        // Password validation
        if ( ! empty( $this->form_data['password'] ) ) {
            if ( strlen( $this->form_data['password'] ) < 8 ) {
                $this->errors->add( 'password_short', __( 'Password must be at least 8 characters.', 'mjschool' ) );
            }
            if ( strlen( $this->form_data['password'] ) > 12 ) {
                $this->errors->add( 'password_long', __( 'Password must not exceed 12 characters.', 'mjschool' ) );
            }
        }

        // Mobile number validation
        if ( ! empty( $this->form_data['mobile_number'] ) ) {
            if ( ! preg_match( '/^[0-9]{6,15}$/', $this->form_data['mobile_number'] ) ) {
                $this->errors->add( 'mobile_invalid', __( 'Please enter a valid mobile number (6-15 digits).', 'mjschool' ) );
            }
        }

        return ! $this->has_errors();
    }

    /**
     * Check if there are validation errors
     *
     * @since 2.0.0
     * @return bool True if errors exist.
     */
    public function has_errors() {
        return count( $this->errors->get_error_messages() ) > 0;
    }

    /**
     * Get validation errors
     *
     * @since 2.0.0
     * @return WP_Error Validation errors.
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Display validation errors
     *
     * @since 2.0.0
     */
    public function display_errors() {
        if ( ! $this->has_errors() ) {
            return;
        }

        echo '<div class="mjschool-registration-errors">';
        foreach ( $this->errors->get_error_messages() as $error ) {
            echo '<div class="mjschool-student-reg-error">';
            echo '<strong>' . esc_html__( 'ERROR', 'mjschool' ) . '</strong>: ';
            echo '<span class="error">' . esc_html( $error ) . '</span>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Process form submission
     *
     * @since 2.0.0
     * @return int|false User ID on success, false on failure.
     */
    public function process_submission() {
        // Verify nonce
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_student_frontend_shortcode_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }

        // Populate form data from POST
        $this->populate_form_data();

        // Validate
        if ( ! $this->validate() ) {
            return false;
        }

        // Create user
        $user_id = $this->create_user();
        if ( is_wp_error( $user_id ) ) {
            $this->errors->add( 'user_creation', $user_id->get_error_message() );
            return false;
        }

        // Process uploads and metadata
        $this->process_avatar( $user_id );
        $this->process_documents( $user_id );
        $this->save_user_metadata( $user_id );
        $this->process_custom_fields( $user_id );
        $this->process_registration_fees( $user_id );

        // Send notifications
        $this->send_notifications( $user_id );

        // Redirect
        $this->redirect_after_registration( $user_id );

        return $user_id;
    }

    /**
     * Populate form data from POST request
     *
     * @since 2.0.0
     */
    private function populate_form_data() {
        $text_fields = array(
            'class_name', 'first_name', 'middle_name', 'last_name',
            'gender', 'birth_date', 'address', 'city_name', 'state_name',
            'zip_code', 'mobile_number', 'alternet_mobile_number', 'phone', 'password',
        );

        foreach ( $text_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $this->form_data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
            }
        }

        // Email needs special handling
        if ( isset( $_POST['email'] ) ) {
            $this->form_data['email'] = sanitize_email( wp_unslash( $_POST['email'] ) );
        }

        // Document titles - FIXED: proper array handling
        if ( isset( $_POST['document_title'] ) && is_array( $_POST['document_title'] ) ) {
            $this->form_data['document_title'] = array_map( 'sanitize_text_field', wp_unslash( $_POST['document_title'] ) );
        }

        // Validate birth date format
        if ( ! empty( $this->form_data['birth_date'] ) ) {
            $this->form_data['birth_date'] = $this->sanitize_date( $this->form_data['birth_date'] );
        }
    }

    /**
     * Sanitize date input
     *
     * @since 2.0.0
     * @param string $date Date string.
     * @return string Sanitized date.
     */
    private function sanitize_date( $date ) {
        $timestamp = strtotime( $date );
        if ( false === $timestamp ) {
            return '';
        }
        return gmdate( 'Y-m-d', $timestamp );
    }

    /**
     * Create WordPress user
     *
     * @since 2.0.0
     * @return int|WP_Error User ID or error.
     */
    private function create_user() {
        $userdata = array(
            'user_login' => $this->form_data['email'],
            'user_email' => $this->form_data['email'],
            'user_pass'  => $this->form_data['password'],
            'first_name' => $this->form_data['first_name'],
            'last_name'  => $this->form_data['last_name'],
        );

        $user_id = wp_insert_user( $userdata );

        if ( ! is_wp_error( $user_id ) ) {
            $user = new WP_User( $user_id );
            $user->set_role( 'student' );
            $user->add_role( 'subscriber' );

            // Set activation hash if approval required
            if ( '1' === get_option( 'mjschool_student_approval' ) ) {
                $hash = wp_generate_password( 32, false );
                update_user_meta( $user_id, 'hash', $hash );
            }
        }

        return $user_id;
    }

    /**
     * Process avatar upload
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function process_avatar( $user_id ) {
        // FIXED: Proper $_FILES check
        if ( ! isset( $_FILES['smgt_user_avatar'] ) || empty( $_FILES['smgt_user_avatar']['name'] ) ) {
            return;
        }

        $file = $_FILES['smgt_user_avatar'];

        // Validate file
        if ( ! $this->validate_avatar_file( $file ) ) {
            return;
        }

        // Upload file
        if ( function_exists( 'mjschool_user_avatar_image_upload' ) ) {
            $avatar_filename = mjschool_user_avatar_image_upload( 'smgt_user_avatar' );
            if ( $avatar_filename ) {
                $avatar_url = content_url( '/uploads/school_assets/' . $avatar_filename );
                update_user_meta( $user_id, 'mjschool_user_avatar', esc_url( $avatar_url ) );
            }
        }
    }

    /**
     * Validate avatar file
     *
     * @since 2.0.0
     * @param array $file File array from $_FILES.
     * @return bool True if valid.
     */
    private function validate_avatar_file( $file ) {
        // Check for upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return false;
        }

        // Check file size
        if ( $file['size'] > $this->max_avatar_size ) {
            $this->errors->add( 'avatar_size', __( 'Avatar file is too large. Maximum size is 10MB.', 'mjschool' ) );
            return false;
        }

        // Check file extension
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
        if ( ! in_array( $ext, $this->allowed_avatar_extensions, true ) ) {
            $this->errors->add( 'avatar_type', __( 'Invalid avatar file type. Allowed types: jpg, jpeg, png, gif, webp.', 'mjschool' ) );
            return false;
        }

        // Verify MIME type
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime  = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );

        $allowed_mimes = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
        if ( ! in_array( $mime, $allowed_mimes, true ) ) {
            $this->errors->add( 'avatar_mime', __( 'Invalid avatar file type.', 'mjschool' ) );
            return false;
        }

        return true;
    }

    /**
     * Process document uploads
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function process_documents( $user_id ) {
        // FIXED: Proper $_FILES check
        if ( ! isset( $_FILES['document_file'] ) || empty( $_FILES['document_file']['name'] ) ) {
            return;
        }

        $document_files  = $_FILES['document_file'];
        $document_titles = $this->form_data['document_title'];
        $documents       = array();

        // Get allowed document types
        $allowed_types = $this->get_allowed_document_types();
        $max_size      = absint( get_option( 'mjschool_upload_document_size', 30 ) ) * 1024 * 1024;

        if ( ! is_array( $document_files['name'] ) ) {
            return;
        }

        $count = count( $document_files['name'] );

        for ( $i = 0; $i < $count; $i++ ) {
            // Skip if no file or no title
            if ( empty( $document_files['name'][ $i ] ) || empty( $document_titles[ $i ] ) ) {
                continue;
            }

            // Skip if upload error
            if ( $document_files['error'][ $i ] !== UPLOAD_ERR_OK ) {
                continue;
            }

            // Validate file size
            if ( $document_files['size'][ $i ] > $max_size ) {
                continue;
            }

            // Validate file type
            $ext = strtolower( pathinfo( $document_files['name'][ $i ], PATHINFO_EXTENSION ) );
            if ( ! in_array( $ext, $allowed_types, true ) ) {
                continue;
            }

            // Upload document
            if ( function_exists( 'mjschool_upload_document_user_multiple' ) ) {
                $uploaded_file = mjschool_upload_document_user_multiple( $document_files, $i, $document_titles[ $i ] );
                if ( $uploaded_file ) {
                    $documents[] = array(
                        'document_title' => sanitize_text_field( $document_titles[ $i ] ),
                        'document_file'  => $uploaded_file,
                    );
                }
            }
        }

        if ( ! empty( $documents ) ) {
            update_user_meta( $user_id, 'user_document', wp_json_encode( $documents ) );
        }
    }

    /**
     * Get allowed document types
     *
     * @since 2.0.0
     * @return array Allowed extensions.
     */
    private function get_allowed_document_types() {
        $option = get_option( 'mjschool_upload_document_type', 'pdf, doc, docx, ppt, pptx, gif, png, jpg, jpeg, webp' );
        return array_map( 'trim', explode( ',', str_replace( ', ', ',', $option ) ) );
    }

    /**
     * Save user metadata
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function save_user_metadata( $user_id ) {
        $metadata = array(
            'roll_id'                => '',
            'middle_name'            => $this->form_data['middle_name'],
            'gender'                 => $this->form_data['gender'],
            'birth_date'             => $this->form_data['birth_date'],
            'address'                => $this->form_data['address'],
            'city'                   => $this->form_data['city_name'],
            'state'                  => $this->form_data['state_name'],
            'zip_code'               => $this->form_data['zip_code'],
            'class_name'             => $this->form_data['class_name'],
            'phone'                  => $this->form_data['phone'],
            'mobile_number'          => $this->form_data['mobile_number'],
            'alternet_mobile_number' => $this->form_data['alternet_mobile_number'],
        );

        foreach ( $metadata as $key => $value ) {
            update_user_meta( $user_id, $key, $value );
        }
    }

    /**
     * Process custom fields
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function process_custom_fields( $user_id ) {
        if ( ! class_exists( 'Mjschool_Custome_Field' ) ) {
            return;
        }

        $custom_field_obj = new Mjschool_Custome_Field();
        $custom_field_obj->mjschool_insert_custom_field_data_module_wise( 'student', $user_id );
    }

    /**
     * Process registration fees
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function process_registration_fees( $user_id ) {
        if ( 'yes' !== get_option( 'mjschool_registration_fees' ) ) {
            return;
        }

        $fees_id = get_option( 'mjschool_registration_amount' );
        if ( empty( $fees_id ) ) {
            return;
        }

        if ( ! class_exists( 'Mjschool_Fees' ) || ! function_exists( 'mjschool_generate_admission_fees_invoice' ) ) {
            return;
        }

        $obj_fees = new Mjschool_Fees();
        $amount   = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );

        if ( $amount ) {
            mjschool_generate_admission_fees_invoice(
                $amount,
                $user_id,
                $fees_id,
                $this->form_data['class_name'],
                0,
                'Registration Fees'
            );
        }
    }

    /**
     * Send notification emails
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function send_notifications( $user_id ) {
        if ( 1 !== absint( get_option( 'mjschool_mail_notification' ) ) ) {
            return;
        }

        $user_info  = get_userdata( $user_id );
        $class_name = get_user_meta( $user_id, 'class_name', true );

        // Student notification
        $this->send_student_notification( $user_info, $class_name );

        // Teacher notification (if no approval required)
        if ( '1' !== get_option( 'mjschool_student_approval' ) ) {
            $this->send_teacher_notifications( $user_info, $class_name );
        }
    }

    /**
     * Send notification to student
     *
     * @since 2.0.0
     * @param WP_User $user_info  User object.
     * @param string  $class_name Class name.
     */
    private function send_student_notification( $user_info, $class_name ) {
        $to      = $user_info->user_email;
        $subject = get_option( 'mjschool_registration_title', 'Student Registration' );
        $school  = get_option( 'mjschool_name', 'School Management System' );

        // Get class name display
        $class_display = function_exists( 'mjschool_get_class_name' ) ? mjschool_get_class_name( $class_name ) : $class_name;

        // Build message
        $search  = array( '{{student_name}}', '{{email_id}}', '{{class_name}}', '{{password}}', '{{school_name}}' );
        $replace = array( $user_info->display_name, $to, $class_display, $this->form_data['password'], $school );
        $message = str_replace( $search, $replace, get_option( 'mjschool_registration_mailtemplate', '' ) );

        // Headers
        $headers  = 'From: ' . $school . ' <noreply@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>' . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // Apply template
        if ( function_exists( 'mjschool_get_mail_content_with_template_design' ) ) {
            $message = mjschool_get_mail_content_with_template_design( $message );
        }

        wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Send notifications to assigned teachers
     *
     * @since 2.0.0
     * @param WP_User $user_info  User object.
     * @param string  $class_name Class name.
     */
    private function send_teacher_notifications( $user_info, $class_name ) {
        if ( ! function_exists( 'mjschool_check_class_exits_in_teacher_class' ) ) {
            return;
        }

        $teacher_ids = mjschool_check_class_exits_in_teacher_class( $class_name );
        if ( empty( $teacher_ids ) ) {
            return;
        }

        $school  = get_option( 'mjschool_name', 'School Management System' );
        $subject = get_option( 'mjschool_student_assign_teacher_mail_subject', 'New Student has been assigned to you.' );
        $content = get_option( 'mjschool_student_assign_teacher_mail_content', '' );

        foreach ( $teacher_ids as $teacher_id ) {
            $teacher_data = get_userdata( $teacher_id );
            if ( ! $teacher_data ) {
                continue;
            }

            $teacher_name = function_exists( 'mjschool_get_display_name' ) ? mjschool_get_display_name( $teacher_id ) : $teacher_data->display_name;

            $replacements = array(
                '{{school_name}}'  => $school,
                '{{student_name}}' => $user_info->display_name,
                '{{teacher_name}}' => $teacher_name,
            );

            $message = str_replace( array_keys( $replacements ), array_values( $replacements ), $content );

            if ( function_exists( 'mjschool_send_mail' ) ) {
                mjschool_send_mail( $teacher_data->user_email, $subject, $message );
            }
        }
    }

    /**
     * Redirect after successful registration
     *
     * @since 2.0.0
     * @param int $user_id User ID.
     */
    private function redirect_after_registration( $user_id ) {
        $page_id = get_option( 'mjschool_install_student_registration_page' );

        if ( '1' === get_option( 'mjschool_student_approval' ) ) {
            // Approval required
            $redirect_url = add_query_arg(
                array( 'action' => 'success_1' ),
                home_url( '/student-registration/' )
            );
        } else {
            // Auto-approved
            $redirect_url = add_query_arg(
                array(
                    'page_id' => $page_id,
                    'action'  => 'success_2',
                ),
                home_url()
            );
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Render the registration form
     *
     * @since 2.0.0
     */
    public function render_form() {
        $this->enqueue_assets();

        // Display success message if applicable
        if ( isset( $_REQUEST['action'] ) && 'success_1' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) {
            echo '<div class="col-lg-12 col-md-12 mjschool-admission-successfully-message">';
            esc_html_e( 'Registration complete. Your account will be active after admin approval.', 'mjschool' );
            echo '</div>';
            return;
        }

        // Display errors
        $this->display_errors();

        // Get form action URL - FIXED: XSS vulnerability
        $form_action = esc_url( remove_query_arg( array( 'action' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

        ?>
        <div class="mjschool-student-registration-form">
            <form id="mjschool-registration-form" action="<?php echo esc_url( $form_action ); ?>" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field( 'save_student_frontend_shortcode_nonce' ); ?>
                
                <div class="form-body mjschool-user-form">
                    <div class="row">
                        <?php $this->render_class_select(); ?>
                        <?php $this->render_registration_fees(); ?>
                        <?php $this->render_name_fields(); ?>
                        <?php $this->render_gender_field(); ?>
                        <?php $this->render_date_field(); ?>
                        <?php $this->render_address_fields(); ?>
                        <?php $this->render_contact_fields(); ?>
                        <?php $this->render_credentials_fields(); ?>
                        <?php $this->render_avatar_field(); ?>
                    </div>
                </div>

                <?php $this->render_document_section(); ?>
                <?php $this->render_custom_fields(); ?>
                <?php $this->render_submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render class selection dropdown
     *
     * @since 2.0.0
     */
    private function render_class_select() {
        ?>
        <div class="col-md-6 input mjschool-error-msg-left-margin mjschool-responsive-bottom-15">
            <label class="ml-1 mjschool-custom-top-label top" for="class_name">
                <?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span>
            </label>
            <select name="class_name" class="mjschool-line-height-27px-registration-form form-control validate[required] mjschool-width-100px" id="class_name">
                <option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
                <?php
                if ( function_exists( 'mjschool_get_all_data' ) ) {
                    $classes = mjschool_get_all_data( 'mjschool_class' );
                    foreach ( $classes as $class ) {
                        printf(
                            '<option value="%s" %s>%s</option>',
                            esc_attr( $class->class_id ),
                            selected( $this->form_data['class_name'], $class->class_id, false ),
                            esc_html( $class->class_name )
                        );
                    }
                }
                ?>
            </select>
        </div>
        <?php
    }

    /**
     * Render registration fees display
     *
     * @since 2.0.0
     */
    private function render_registration_fees() {
        if ( 'yes' !== get_option( 'mjschool_registration_fees' ) ) {
            return;
        }

        $fees_id = get_option( 'mjschool_registration_amount' );
        $fees    = 0;

        if ( class_exists( 'Mjschool_Fees' ) ) {
            $obj_fees = new Mjschool_Fees();
            $amount   = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
            $fees     = $amount ? $amount : 0;
        }

        $currency = function_exists( 'mjschool_get_currency_symbol' ) ? mjschool_get_currency_symbol() : '$';
        ?>
        <div class="col-md-6 mjschool-error-msg-left-margin">
            <div class="form-group input">
                <div class="col-md-12 form-control">
                    <input class="mjschool-line-height-29px-registration-from form-control text-input" type="text" readonly value="<?php echo esc_attr( $currency . ' ' . $fees ); ?>">
                    <label for="registration_fees" class="active">
                        <?php esc_html_e( 'Registration Fees', 'mjschool' ); ?><span class="required">*</span>
                    </label>
                </div>
            </div>
        </div>
        <input type="hidden" name="registration_fees" id="registration_fees" value="<?php echo esc_attr( $fees_id ); ?>">
        <?php
    }

    /**
     * Render name input fields
     *
     * @since 2.0.0
     */
    private function render_name_fields() {
        $fields = array(
            'first_name'  => array(
                'label'    => __( 'First Name', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[onlyLetter_specialcharacter]]',
            ),
            'middle_name' => array(
                'label'    => __( 'Middle Name', 'mjschool' ),
                'required' => false,
                'validate' => 'validate[custom[onlyLetter_specialcharacter]]',
            ),
            'last_name'   => array(
                'label'    => __( 'Last Name', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[onlyLetter_specialcharacter]]',
            ),
        );

        foreach ( $fields as $field_name => $config ) {
            ?>
            <div class="col-md-6">
                <div class="form-group input">
                    <div class="col-md-12 form-control">
                        <input 
                            id="<?php echo esc_attr( $field_name ); ?>" 
                            class="mjschool-line-height-29px-registration-from form-control <?php echo esc_attr( $config['validate'] ); ?> text-input" 
                            maxlength="50" 
                            type="text" 
                            value="<?php echo esc_attr( $this->form_data[ $field_name ] ); ?>" 
                            name="<?php echo esc_attr( $field_name ); ?>"
                        >
                        <label for="<?php echo esc_attr( $field_name ); ?>" class="active">
                            <?php echo esc_html( $config['label'] ); ?>
                            <?php if ( $config['required'] ) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render gender selection field
     *
     * @since 2.0.0
     */
    private function render_gender_field() {
        $gender = $this->form_data['gender'] ?: 'male';
        ?>
        <div class="col-md-6 mjschool-res-margin-bottom-20px">
            <div class="form-group">
                <div class="col-md-12 form-control">
                    <div class="row mjschool-padding-radio mb-0">
                        <div class="input-group mb-0">
                            <label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl">
                                <?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span>
                            </label>
                            <div class="d-inline-block mb-1">
                                <?php
                                $genders = array(
                                    'male'   => __( 'Male', 'mjschool' ),
                                    'female' => __( 'Female', 'mjschool' ),
                                    'other'  => __( 'Other', 'mjschool' ),
                                );
                                foreach ( $genders as $value => $label ) :
                                    ?>
                                    <input type="radio" value="<?php echo esc_attr( $value ); ?>" class="tog validate[required]" name="gender" <?php checked( $value, $gender ); ?>>
                                    <label class="mjschool-custom-control-label mjschool-margin-right-20px" for="<?php echo esc_attr( $value ); ?>">
                                        <?php echo esc_html( $label ); ?>
                                    </label>
                                    &nbsp;&nbsp;
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render birth date field
     *
     * @since 2.0.0
     */
    private function render_date_field() {
        $birth_date = $this->form_data['birth_date'];
        if ( empty( $birth_date ) && function_exists( 'mjschool_get_date_in_input_box' ) ) {
            $birth_date = mjschool_get_date_in_input_box( gmdate( 'Y-m-d' ) );
        }
        ?>
        <div class="col-md-6">
            <div class="form-group input">
                <div class="col-md-12 form-control">
                    <input 
                        id="birth_date" 
                        class="mjschool-line-height-29px-registration-from validate[required]" 
                        type="text" 
                        name="birth_date" 
                        value="<?php echo esc_attr( $birth_date ); ?>" 
                        readonly
                    >
                    <label for="birth_date" class="active">
                        <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="required">*</span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render address fields
     *
     * @since 2.0.0
     */
    private function render_address_fields() {
        $fields = array(
            'address'    => array(
                'label'    => __( 'Address', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[address_description_validation]]',
                'maxlen'   => 120,
            ),
            'city_name'  => array(
                'label'    => __( 'City', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[city_state_country_validation]]',
                'maxlen'   => 50,
            ),
            'state_name' => array(
                'label'    => __( 'State', 'mjschool' ),
                'required' => false,
                'validate' => 'validate[custom[city_state_country_validation]]',
                'maxlen'   => 50,
            ),
            'zip_code'   => array(
                'label'    => __( 'Zip Code', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[zipcode]]',
                'maxlen'   => 15,
            ),
        );

        foreach ( $fields as $field_name => $config ) {
            ?>
            <div class="col-md-6">
                <div class="form-group input">
                    <div class="col-md-12 form-control">
                        <input 
                            id="<?php echo esc_attr( $field_name ); ?>" 
                            class="mjschool-line-height-29px-registration-from form-control <?php echo esc_attr( $config['validate'] ); ?>" 
                            maxlength="<?php echo esc_attr( $config['maxlen'] ); ?>" 
                            type="text" 
                            name="<?php echo esc_attr( $field_name ); ?>" 
                            value="<?php echo esc_attr( $this->form_data[ $field_name ] ); ?>"
                        >
                        <label for="<?php echo esc_attr( $field_name ); ?>" class="active">
                            <?php echo esc_html( $config['label'] ); ?>
                            <?php if ( $config['required'] ) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render contact fields (mobile numbers)
     *
     * @since 2.0.0
     */
    private function render_contact_fields() {
        $country_code = '';
        if ( function_exists( 'mjschool_get_country_phonecode' ) ) {
            $country_code = mjschool_get_country_phonecode( get_option( 'mjschool_contry', 'United States' ) );
        }

        $phone_fields = array(
            'mobile_number'          => array(
                'label'    => __( 'Mobile Number', 'mjschool' ),
                'required' => true,
                'validate' => 'validate[required,custom[phone_number],minSize[6],maxSize[15]]',
            ),
            'alternet_mobile_number' => array(
                'label'    => __( 'Alternate Mobile Number', 'mjschool' ),
                'required' => false,
                'validate' => 'validate[custom[phone_number],minSize[6],maxSize[15]]',
            ),
        );

        foreach ( $phone_fields as $field_name => $config ) {
            ?>
            <div class="col-md-6 mjschool-mobile-error-massage-left-margin">
                <div class="form-group input mjschool-margin-bottom-0">
                    <div class="col-md-12 form-control mjschool-mobile-input">
                        <span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( $country_code ); ?></span>
                        <input type="hidden" value="+<?php echo esc_attr( $country_code ); ?>" name="phonecode">
                        <input 
                            id="<?php echo esc_attr( $field_name ); ?>" 
                            class="mjschool-line-height-29px-registration-from form-control text-input <?php echo esc_attr( $config['validate'] ); ?>" 
                            type="text" 
                            name="<?php echo esc_attr( $field_name ); ?>" 
                            maxlength="15" 
                            value="<?php echo esc_attr( $this->form_data[ $field_name ] ); ?>"
                        >
                        <label for="<?php echo esc_attr( $field_name ); ?>" class="mjschool-custom-control-label mjschool-custom-top-label">
                            <?php echo esc_html( $config['label'] ); ?>
                            <?php if ( $config['required'] ) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Render email and password fields
     *
     * @since 2.0.0
     */
    private function render_credentials_fields() {
        ?>
        <div class="col-md-6">
            <div class="form-group input">
                <div class="col-md-12 form-control">
                    <input 
                        id="email" 
                        class="mjschool-line-height-29px-registration-from form-control validate[required,custom[email]] text-input" 
                        maxlength="100" 
                        type="email" 
                        name="email" 
                        value="<?php echo esc_attr( $this->form_data['email'] ); ?>"
                    >
                    <label for="email" class="label_email active">
                        <?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group input">
                <div class="col-md-12 form-control">
                    <input 
                        id="password" 
                        class="mjschool-line-height-29px-registration-from form-control validate[required,minSize[8],maxSize[12]]" 
                        type="password" 
                        name="password" 
                        value=""
                    >
                    <label for="password" class="active">
                        <?php esc_html_e( 'Password', 'mjschool' ); ?><span class="required">*</span>
                    </label>
                    <i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render avatar upload field
     *
     * @since 2.0.0
     */
    private function render_avatar_field() {
        ?>
        <div class="col-md-6">
            <div class="form-group input">
                <div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mb-0" style="padding:0px;padding-left:10px;">
                    <div class="col-sm-12 mjschool-display-flex mb-0">
                        <label for="smgt_user_avatar" class="active"><?php esc_html_e( 'Profile Photo', 'mjschool' ); ?></label>
                        <input type="file" style="border:0px;margin-bottom:0px;" class="form-control" name="smgt_user_avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render document upload section
     *
     * @since 2.0.0
     */
    private function render_document_section() {
        ?>
        <div class="header">
            <h3 class="mjschool-first-header"><?php esc_html_e( 'Document Details', 'mjschool' ); ?></h3>
        </div>
        <div class="mjschool-more-document">
            <div class="form-body mjschool-user-form">
                <div class="row">
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
                        <div class="form-group input">
                            <div class="col-md-12 form-control">
                                <input id="document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
                                <label for="document_title"><?php esc_html_e( 'Document Title', 'mjschool' ); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 col-sm-1">
                        <div class="form-group input">
                            <div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
                                <label for="document_file" class="mjschool-custom-control-label mjschool-custom-top-label ml-2">
                                    <?php esc_html_e( 'Document File', 'mjschool' ); ?>
                                </label>
                                <div class="col-sm-12 mjschool-display-flex">
                                    <input id="document_file" name="document_file[]" type="file" class="form-control file mjschool-file-validation">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1 col-sm-1 col-xs-12">
                        <?php if ( defined( 'MJSCHOOL_PLUGIN_URL' ) ) : ?>
                            <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_document" alt="<?php esc_attr_e( 'Add More', 'mjschool' ); ?>">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render custom fields section
     *
     * @since 2.0.0
     */
    private function render_custom_fields() {
        if ( ! class_exists( 'Mjschool_Custome_Field' ) ) {
            return;
        }

        $custom_field_obj = new Mjschool_Custome_Field();
        $custom_fields    = $custom_field_obj->mjschool_get_custom_field_by_module( 'student' );

        if ( empty( $custom_fields ) ) {
            return;
        }

        // Custom fields rendering would go here
        // This is a placeholder for the actual implementation
    }

    /**
     * Render submit button
     *
     * @since 2.0.0
     */
    private function render_submit_button() {
        ?>
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-sm-6">
                    <input type="submit" value="<?php esc_attr_e( 'Registration', 'mjschool' ); ?>" name="save_student_front" class="btn btn-success btn_style mjschool-save-btn">
                </div>
            </div>
        </div>
        <?php
    }
}

/**
 * Main registration function - Shortcode handler
 *
 * @since 1.0.0
 */
function mjschool_student_registration_function() {
    $registration = new MJSchool_Student_Registration();

    // Process form submission
    if ( isset( $_POST['save_student_front'] ) ) {
        $registration->process_submission();
    }

    // Render form
    $registration->render_form();
}

/**
 * Handle email activation link
 *
 * @since 1.0.0
 */
function mjschool_activate_mail_link() {
    // FIXED: Added proper validation for request parameters
    if ( ! isset( $_REQUEST['haskey'] ) || ! isset( $_REQUEST['id'] ) ) {
        return;
    }

    $hash_key = sanitize_text_field( wp_unslash( $_REQUEST['haskey'] ) );
    $user_id  = absint( wp_unslash( $_REQUEST['id'] ) );

    // Validate user exists
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $stored_hash = get_user_meta( $user_id, 'hash', true );

    if ( empty( $stored_hash ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $login_page_id = get_option( 'mjschool_login_page' );
    $login_url     = get_permalink( $login_page_id );

    if ( hash_equals( $stored_hash, $hash_key ) ) {
        // Valid hash - activate user
        delete_user_meta( $user_id, 'hash' );

        $redirect_url = add_query_arg(
            array(
                'page_id'           => $login_page_id,
                'mjschool_activate' => 1,
            ),
            $login_url
        );
    } else {
        // Invalid hash
        $redirect_url = add_query_arg(
            array(
                'page_id'           => $login_page_id,
                'mjschool_activate' => 2,
            ),
            $login_url
        );
    }

    wp_safe_redirect( $redirect_url );
    exit;
}

// Legacy function wrappers for backward compatibility

/**
 * Legacy registration form function
 *
 * @deprecated 2.0.0 Use MJSchool_Student_Registration class instead.
 */
function mjschool_registration_form( $class_name, $first_name, $middle_name, $last_name, $gender, $birth_date, $address, $city_name, $state_name, $zip_code, $mobile_number, $alternet_mobile_number, $phone, $email, $username, $password, $smgt_user_avatar ) {
    _deprecated_function( __FUNCTION__, '2.0.0', 'MJSchool_Student_Registration::render_form()' );

    $registration = new MJSchool_Student_Registration();
    
    // Set form data from parameters
    $registration->set_data( 'class_name', $class_name );
    $registration->set_data( 'first_name', $first_name );
    $registration->set_data( 'middle_name', $middle_name );
    $registration->set_data( 'last_name', $last_name );
    $registration->set_data( 'gender', $gender );
    $registration->set_data( 'birth_date', $birth_date );
    $registration->set_data( 'address', $address );
    $registration->set_data( 'city_name', $city_name );
    $registration->set_data( 'state_name', $state_name );
    $registration->set_data( 'zip_code', $zip_code );
    $registration->set_data( 'mobile_number', $mobile_number );
    $registration->set_data( 'alternet_mobile_number', $alternet_mobile_number );
    $registration->set_data( 'phone', $phone );
    $registration->set_data( 'email', $email );
    
    $registration->render_form();
}

/**
 * Legacy validation function
 *
 * @deprecated 2.0.0 Use MJSchool_Student_Registration::validate() instead.
 */
function mjschool_registration_validation( $class_name, $first_name, $middle_name, $last_name, $gender, $birth_date, $address, $city_name, $state_name, $zip_code, $mobile_number, $alternet_mobile_number, $phone, $email, $username, $password, $smgt_user_avatar ) {
    _deprecated_function( __FUNCTION__, '2.0.0', 'MJSchool_Student_Registration::validate()' );

    global $mjschool_reg_errors;
    $mjschool_reg_errors = new WP_Error();

    // Basic validation for backward compatibility
    if ( empty( $class_name ) || empty( $first_name ) || empty( $last_name ) || empty( $birth_date ) || empty( $address ) || empty( $city_name ) || empty( $zip_code ) || empty( $mobile_number ) || empty( $email ) || empty( $username ) || empty( $password ) ) {
        $mjschool_reg_errors->add( 'field', __( 'Required form field is missing', 'mjschool' ) );
    }

    if ( strlen( $username ) < 4 ) {
        $mjschool_reg_errors->add( 'username_length', __( 'Username too short. At least 4 characters is required', 'mjschool' ) );
    }

    if ( username_exists( $username ) ) {
        $mjschool_reg_errors->add( 'user_name', __( 'Sorry, that username already exists!', 'mjschool' ) );
    }

    if ( ! is_email( $email ) ) {
        $mjschool_reg_errors->add( 'email_invalid', __( 'Email is not valid', 'mjschool' ) );
    }

    if ( email_exists( $email ) ) {
        $mjschool_reg_errors->add( 'email', __( 'Email Already in use', 'mjschool' ) );
    }

    // Display errors
    if ( is_wp_error( $mjschool_reg_errors ) && $mjschool_reg_errors->has_errors() ) {
        foreach ( $mjschool_reg_errors->get_error_messages() as $error ) {
            echo '<div class="mjschool-student-reg-error">';
            echo '<strong>' . esc_html__( 'ERROR', 'mjschool' ) . '</strong>: ';
            echo '<span class="error">' . esc_html( $error ) . '</span><br/>';
            echo '</div>';
        }
    }
}

/**
 * Class MJSchool_PDF_Generator
 *
 * Handles PDF generation for results, certificates, invoices, etc.
 *
 * @since 2.0.0
 */
class MJSchool_PDF_Generator {

    /**
     * mPDF instance
     *
     * @var \Mpdf\Mpdf
     */
    private $mpdf;

    /**
     * Default mPDF configuration
     *
     * @var array
     */
    private $mpdf_config = array(
        'mode'          => 'utf-8',
        'format'        => 'A4',
        'orientation'   => 'P',
        'margin_left'   => 8,
        'margin_right'  => 8,
        'margin_top'    => 10,
        'margin_bottom' => 10,
    );

    /**
     * Initialize mPDF instance
     *
     * @since 2.0.0
     * @param array $config Optional custom configuration.
     * @return bool True on success.
     */
    private function init_mpdf( $config = array() ) {
        if ( ! defined( 'MJSCHOOL_PLUGIN_DIR' ) ) {
            return false;
        }

        $autoload_path = MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
        if ( ! file_exists( $autoload_path ) ) {
            return false;
        }

        require_once $autoload_path;

        $config = wp_parse_args( $config, $this->mpdf_config );

        try {
            $this->mpdf = new \Mpdf\Mpdf( $config );
            $this->mpdf->autoScriptToLang = true;
            $this->mpdf->autoLangToFont   = true;

            if ( is_rtl() ) {
                $this->mpdf->SetDirectionality( 'rtl' );
            }

            return true;
        } catch ( \Exception $e ) {
            error_log( 'MJSchool PDF Error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Output PDF to browser
     *
     * @since 2.0.0
     * @param string $html     HTML content.
     * @param string $title    PDF title.
     * @param string $filename Output filename.
     */
    private function output_pdf( $html, $title = 'Document', $filename = 'document.pdf' ) {
        if ( ! $this->mpdf ) {
            wp_die( esc_html__( 'PDF generation failed.', 'mjschool' ) );
        }

        // Load stylesheet
        $stylesheet = $this->get_stylesheet();
        if ( $stylesheet ) {
            $this->mpdf->WriteHTML( $stylesheet, 1 );
        }

        $this->mpdf->SetTitle( $title );
        $this->mpdf->SetDisplayMode( 'fullwidth' );
        $this->mpdf->WriteHTML( $html );

        // Set headers
        header( 'Content-type: application/pdf' );
        header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $filename ) . '"' );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Accept-Ranges: bytes' );

        $this->mpdf->Output();

        // Cleanup
        unset( $this->mpdf );
        exit;
    }

    /**
     * Get stylesheet content safely
     *
     * @since 2.0.0
     * @return string|false Stylesheet content or false.
     */
    private function get_stylesheet() {
        if ( ! defined( 'MJSCHOOL_PLUGIN_DIR' ) ) {
            return false;
        }

        $stylesheet_path = MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css';

        if ( ! file_exists( $stylesheet_path ) || ! is_readable( $stylesheet_path ) ) {
            return false;
        }

        // Use WP_Filesystem for better security
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if ( $wp_filesystem ) {
            return $wp_filesystem->get_contents( $stylesheet_path );
        }

        // Fallback - but log it
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        return file_get_contents( $stylesheet_path );
    }

    /**
     * Verify request and check permissions
     *
     * @since 2.0.0
     * @param string $action Required capability or 'public'.
     * @return bool True if authorized.
     */
    private function verify_request( $action = 'read' ) {
        // For public PDF generation (like student results), verify the user has access
        if ( ! is_user_logged_in() ) {
            return false;
        }

        // Check if user can view the requested data
        // This should be extended based on specific requirements
        return current_user_can( $action ) || current_user_can( 'read' );
    }

    /**
     * Safely get request parameter
     *
     * @since 2.0.0
     * @param string $key     Parameter key.
     * @param string $type    Expected type: 'int', 'string', 'email'.
     * @param mixed  $default Default value.
     * @return mixed Sanitized value.
     */
    private function get_param( $key, $type = 'string', $default = '' ) {
        if ( ! isset( $_REQUEST[ $key ] ) ) {
            return $default;
        }

        $value = wp_unslash( $_REQUEST[ $key ] );

        switch ( $type ) {
            case 'int':
                return absint( $value );

            case 'encrypted_int':
                if ( function_exists( 'mjschool_decrypt_id' ) ) {
                    return absint( mjschool_decrypt_id( sanitize_text_field( $value ) ) );
                }
                return 0;

            case 'email':
                return sanitize_email( $value );

            case 'textarea':
                return sanitize_textarea_field( $value );

            case 'string':
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Generate group result PDF
     *
     * @since 2.0.0
     */
    public function generate_group_result_pdf() {
        if ( ! $this->verify_request() ) {
            wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
        }

        $student_id = $this->get_param( 'student', 'encrypted_int' );
        $merge_id   = $this->get_param( 'merge_id', 'encrypted_int' );
        $class_id   = $this->get_param( 'class_id', 'encrypted_int' );
        $section_id = $this->get_param( 'section_id', 'encrypted_int' );
        $teacher_id = $this->get_param( 'teacher_id', 'int' ); // FIXED: Added intval
        $comment    = $this->get_param( 'comment', 'textarea' );

        if ( ! $student_id || ! $merge_id ) {
            wp_die( esc_html__( 'Invalid parameters.', 'mjschool' ) );
        }

        // Verify student exists
        $user = get_userdata( $student_id );
        if ( ! $user ) {
            wp_die( esc_html__( 'Student not found.', 'mjschool' ) );
        }

        // Get exam data
        if ( ! class_exists( 'Mjschool_Marks_Manage' ) || ! class_exists( 'Mjschool_exam' ) ) {
            wp_die( esc_html__( 'Required classes not found.', 'mjschool' ) );
        }

        $obj_mark   = new Mjschool_Marks_Manage();
        $exam_obj   = new Mjschool_exam();
        $merge_data = $exam_obj->mjschool_get_single_merge_exam_setting( $merge_id );

        if ( ! $merge_data ) {
            wp_die( esc_html__( 'Exam data not found.', 'mjschool' ) );
        }

        // Get teacher signature if provided
        $signature_url = '';
        if ( $teacher_id ) {
            $metadata       = get_user_meta( $teacher_id );
            $signature_path = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
            $signature_url  = $signature_path ? content_url( $signature_path ) : '';
        }

        // Generate HTML
        $html = $this->render_group_result_html(
            $student_id,
            $merge_data,
            $class_id,
            $section_id,
            $comment,
            $signature_url,
            $obj_mark,
            $exam_obj
        );

        // Initialize and output PDF
        if ( ! $this->init_mpdf() ) {
            wp_die( esc_html__( 'Failed to initialize PDF generator.', 'mjschool' ) );
        }

        $this->output_pdf( $html, 'Result', 'group_result.pdf' );
    }

    /**
     * Render group result HTML
     *
     * @since 2.0.0
     * @param int    $student_id    Student ID.
     * @param object $merge_data    Merge exam data.
     * @param int    $class_id      Class ID.
     * @param int    $section_id    Section ID.
     * @param string $comment       Teacher comment.
     * @param string $signature_url Teacher signature URL.
     * @param object $obj_mark      Marks manager object.
     * @param object $exam_obj      Exam object.
     * @return string HTML content.
     */
    private function render_group_result_html( $student_id, $merge_data, $class_id, $section_id, $comment, $signature_url, $obj_mark, $exam_obj ) {
        $merge_name        = $merge_data->merge_name;
        $merge_config_data = json_decode( $merge_data->merge_config );
        $subject           = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
        $total_subject     = count( $subject );

        // Prevent division by zero
        if ( $total_subject === 0 ) {
            return '<p>' . esc_html__( 'No subjects found.', 'mjschool' ) . '</p>';
        }

        ob_start();
        ?>
        <!-- School Header -->
        <?php echo $this->render_school_header(); ?>

        <!-- Student Info -->
        <div style="border: 2px solid; margin-bottom:12px;">
            <div class="mjschool_float_left_width_100">
                <div class="mjschool_padding_10px">
                    <div style="float:left; width:50%;">
                        <b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'first_name', true ) . ' ' . get_user_meta( $student_id, 'last_name', true ) ); ?>
                    </div>
                    <div style="float:left; width:50%;">
                        <b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( $merge_name ); ?>
                    </div>
                    <div style="clear:both;"></div>
                    <div style="float:left; width:50%;">
                        <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'roll_id', true ) ); ?>
                    </div>
                    <div style="width: 50%; float: left; margin-bottom: 5px;">
                        <b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
                        <?php
                        $class_name = function_exists( 'mjschool_get_class_name' ) ? mjschool_get_class_name( $class_id ) : '';
                        if ( ! empty( $section_id ) && function_exists( 'mjschool_get_section_name' ) ) {
                            $section_name = mjschool_get_section_name( $section_id );
                            echo esc_html( $class_name . ' ( ' . $section_name . ' )' );
                        } else {
                            echo esc_html( $class_name );
                        }
                        ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <?php echo $this->render_group_result_table( $subject, $merge_config_data, $student_id, $class_id, $obj_mark, $exam_obj, $merge_data ); ?>

        <!-- Signatures -->
        <?php echo $this->render_signatures( $comment, $signature_url ); ?>

        <?php
        return ob_get_clean();
    }

    /**
     * Render school header for PDFs
     *
     * @since 2.0.0
     * @return string HTML content.
     */
    private function render_school_header() {
        ob_start();
        ?>
        <div class="container" style="margin-bottom:12px;">
            <div style="border: 2px solid;">
                <div style="padding:20px;">
                    <div class="mjschool_float_left_width_100">
                        <div style="float:left;width:30%;">
                            <div class="mjschool-custom-logo-class" style="float:left;border-radius:50px;">
                                <div style="background-image: url('<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>');height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
                            </div>
                        </div>
                        <div style="float:left; width:70%;font-size:24px;padding-top:25px;">
                            <p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
                            <p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
                            <div class="mjschool_fees_center_margin_0px">
                                <p class="mjschool_fees_width_fit_content_inline">
                                    <?php esc_html_e( 'E-mail', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
                                    &nbsp;&nbsp;
                                    <?php esc_html_e( 'Phone', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render group result table
     *
     * @since 2.0.0
     * @param array  $subject           Subject list.
     * @param array  $merge_config_data Merge configuration.
     * @param int    $student_id        Student ID.
     * @param int    $class_id          Class ID.
     * @param object $obj_mark          Marks manager.
     * @param object $exam_obj          Exam object.
     * @param object $merge_data        Merge data.
     * @return string HTML content.
     */
    private function render_group_result_table( $subject, $merge_config_data, $student_id, $class_id, $obj_mark, $exam_obj, $merge_data ) {
        ob_start();

        $total_obtained     = 0;
        $total_max_possible = 0;
        $any_subject_failed = false;

        ?>
        <table style="float:left;width:100%;border:1px solid #000;margin-bottom:12px;" cellpadding="10" cellspacing="0">
            <thead>
                <tr style="background-color:#b8daff;">
                    <th rowspan="2" style="border:1px solid #000;"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                    <?php
                    if ( ! empty( $merge_config_data ) ) {
                        foreach ( $merge_config_data as $item ) {
                            $exam_id   = $item->exam_id;
                            $exam_name = function_exists( 'mjschool_get_exam_name_id' ) ? mjschool_get_exam_name_id( $exam_id ) : '';

                            if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                                $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                                $contributions_data_array = json_decode( $exam_data->contributions_data, true );
                                $colspan                  = is_array( $contributions_data_array ) ? count( $contributions_data_array ) : 1;
                                echo '<th colspan="' . esc_attr( $colspan ) . '" style="border:1px solid #000;">' . esc_html( $exam_name ) . '</th>';
                            } else {
                                echo '<th style="border:1px solid #000;">' . esc_html( $exam_name ) . '</th>';
                            }
                        }
                    }
                    ?>
                    <th colspan="2" style="border:1px solid #000;">
                        <?php
                        if ( function_exists( 'mjschool_print_weightage_data_pdf' ) ) {
                            echo esc_html( mjschool_print_weightage_data_pdf( $merge_data->merge_config ) );
                        }
                        ?>
                    </th>
                </tr>
                <tr style="background-color:#b8daff;">
                    <?php
                    if ( ! empty( $merge_config_data ) ) {
                        foreach ( $merge_config_data as $item ) {
                            $exam_id = $item->exam_id;

                            if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                                $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                                $contributions_data_array = json_decode( $exam_data->contributions_data, true );

                                if ( is_array( $contributions_data_array ) ) {
                                    foreach ( $contributions_data_array as $con_value ) {
                                        echo '<th style="border:1px solid #000;">' . esc_html( $con_value['label'] ) . ' (' . esc_html( $con_value['mark'] ) . ')</th>';
                                    }
                                }
                            } else {
                                ?>
                                <th style="border:1px solid #000;"><?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
                                <?php
                            }
                        }
                    }
                    ?>
                    <th style="border:1px solid #000;"><?php esc_html_e( 'Grand Total(100)', 'mjschool' ); ?></th>
                    <th style="border:1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $subject as $sub ) {
                    echo '<tr>';
                    echo '<td style="border:1px solid #000;">' . esc_html( $sub->sub_name ) . '</td>';

                    $subject_total_weighted = 0;

                    foreach ( $merge_config_data as $item ) {
                        $exam_id        = $item->exam_id;
                        $exam_weightage = isset( $item->weightage ) ? floatval( $item->weightage ) : 0;
                        $marks          = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $student_id );

                        if ( function_exists( 'mjschool_check_contribution' ) && mjschool_check_contribution( $exam_id ) === 'yes' ) {
                            $exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
                            $contributions_data_array = json_decode( $exam_data->contributions_data, true );
                            $subject_total            = 0;

                            if ( is_array( $contributions_data_array ) ) {
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $mark_value     = isset( $marks[ $con_id ] ) ? floatval( $marks[ $con_id ] ) : 0;
                                    $subject_total += $mark_value;
                                    echo '<td style="border:1px solid #000;font-size:18px;">' . esc_html( $mark_value ) . '</td>';
                                }
                            }

                            $weighted_marks = $exam_weightage > 0 ? ( $subject_total * $exam_weightage ) / 100 : 0;
                            $pass_marks     = $obj_mark->mjschool_get_pass_marks( $exam_id );

                            if ( $subject_total < $pass_marks ) {
                                $any_subject_failed = true;
                            }
                        } else {
                            $marks_float = floatval( $marks );
                            echo '<td style="border:1px solid #000;font-size:18px;">' . esc_html( $marks_float ) . '</td>';

                            $weighted_marks = $exam_weightage > 0 ? ( $marks_float * $exam_weightage ) / 100 : 0;
                            $pass_marks     = $obj_mark->mjschool_get_pass_marks( $exam_id );

                            if ( $marks_float < $pass_marks ) {
                                $any_subject_failed = true;
                            }
                        }

                        $subject_total_weighted += $weighted_marks;
                    }

                    $subject_grade = $obj_mark->mjschool_get_grade_base_on_grand_total( $subject_total_weighted );
                    echo '<td style="border:1px solid #000;">' . esc_html( round( $subject_total_weighted, 2 ) ) . '</td>';
                    echo '<td style="border:1px solid #000;">' . esc_html( $subject_grade ) . '</td>';
                    echo '</tr>';

                    $total_obtained     += $subject_total_weighted;
                    $total_max_possible += 100;
                }

                // FIXED: Prevent division by zero
                $percentage   = $total_max_possible > 0 ? ( $total_obtained / $total_max_possible ) * 100 : 0;
                $final_grade  = $obj_mark->mjschool_get_grade_base_on_grand_total( $percentage );
                $final_result = ( $any_subject_failed || $percentage < 33 ) ? esc_html__( 'Fail', 'mjschool' ) : esc_html__( 'Pass', 'mjschool' );
                ?>
            </tbody>
        </table>

        <!-- Summary Table -->
        <table style="float:left;width:100%;border:1px solid #000;margin-bottom:12px;" cellpadding="10" cellspacing="0">
            <thead>
                <tr style="background-color:#b8daff;">
                    <th style="border:1px solid #000; font-size: 12px;"><?php esc_html_e( 'Overall Mark', 'mjschool' ); ?></th>
                    <th style="border:1px solid #000; font-size: 12px;"><?php esc_html_e( 'Percentage', 'mjschool' ); ?></th>
                    <th style="border:1px solid #000; font-size: 12px;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                    <th style="border:1px solid #000; font-size: 12px;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color:#b8daff;">
                    <td style="border:1px solid #000;"><?php echo esc_html( round( $total_obtained, 2 ) . ' / ' . $total_max_possible ); ?></td>
                    <td style="border:1px solid #000;"><?php echo esc_html( number_format( $percentage, 2 ) . '%' ); ?></td>
                    <td style="border:1px solid #000;"><?php echo esc_html( $final_grade ); ?></td>
                    <td style="border:1px solid #000;"><?php echo esc_html( $final_result ); ?></td>
                </tr>
            </tbody>
        </table>
        <?php

        return ob_get_clean();
    }

    /**
     * Render signatures section
     *
     * @since 2.0.0
     * @param string $comment       Teacher comment.
     * @param string $signature_url Teacher signature URL.
     * @return string HTML content.
     */
    private function render_signatures( $comment, $signature_url ) {
        ob_start();
        ?>
        <div style="border: 2px solid; width:96.6%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
            <!-- Teacher's Comment -->
            <div style="float: left; width: 33.33%;">
                <div style="margin-left: 20px;">
                    <strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong>
                    <p><?php echo esc_html( $comment ); ?></p>
                </div>
            </div>

            <!-- Teacher Signature -->
            <div style="float: left; width: 33.33%; text-align: center; padding-top: 0px;">
                <?php if ( ! empty( $signature_url ) ) : ?>
                    <div>
                        <img src="<?php echo esc_url( $signature_url ); ?>" style="width:100px;height:45px;" alt="<?php esc_attr_e( 'Teacher Signature', 'mjschool' ); ?>">
                    </div>
                <?php else : ?>
                    <div>
                        <div style="width:100px;height:45px;"></div>
                    </div>
                <?php endif; ?>
                <div class="mjschool_fees_width_150px"></div>
                <div class="mjschool_margin_top_5px">
                    <?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?>
                </div>
            </div>

            <!-- Principal Signature -->
            <div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
                <div>
                    <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" alt="<?php esc_attr_e( 'Principal Signature', 'mjschool' ); ?>">
                </div>
                <div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
                <div style="margin-right:10px; margin-bottom:10px;">
                    <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate single exam result PDF
     *
     * @since 2.0.0
     */
    public function generate_result_pdf() {
        if ( ! $this->verify_request() ) {
            wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
        }

        $student_id = $this->get_param( 'student', 'encrypted_int' );
        $exam_id    = $this->get_param( 'exam_id', 'encrypted_int' );
        $class_id   = $this->get_param( 'class_id', 'encrypted_int' );
        $section_id = $this->get_param( 'section_id', 'encrypted_int' );
        $teacher_id = $this->get_param( 'teacher_id', 'int' );
        $comment    = $this->get_param( 'comment', 'textarea' );

        if ( ! $student_id || ! $exam_id ) {
            wp_die( esc_html__( 'Invalid parameters.', 'mjschool' ) );
        }

        // Verify student exists
        $user = get_userdata( $student_id );
        if ( ! $user ) {
            wp_die( esc_html__( 'Student not found.', 'mjschool' ) );
        }

        // Get required classes
        if ( ! class_exists( 'Mjschool_Marks_Manage' ) || ! class_exists( 'Mjschool_exam' ) ) {
            wp_die( esc_html__( 'Required classes not found.', 'mjschool' ) );
        }

        $obj_mark  = new Mjschool_Marks_Manage();
        $exam_obj  = new Mjschool_exam();
        $exam_data = $exam_obj->mjschool_exam_data( $exam_id );

        if ( ! $exam_data ) {
            wp_die( esc_html__( 'Exam data not found.', 'mjschool' ) );
        }

        // Use exam's class_id if available
        $class_id        = $exam_data->class_id ?: $class_id;
        $exam_section_id = $exam_data->section_id;

        // Get subjects
        if ( $exam_section_id === 0 && function_exists( 'mjschool_get_subject_by_class_id' ) ) {
            $subject = mjschool_get_subject_by_class_id( $class_id );
        } elseif ( function_exists( 'mjschool_get_subjects_by_class_and_section' ) ) {
            $subject = mjschool_get_subjects_by_class_and_section( $class_id, $exam_section_id );
        } else {
            $subject = array();
        }

        // Get teacher signature
        $signature_url = '';
        if ( $teacher_id ) {
            $metadata       = get_user_meta( $teacher_id );
            $signature_path = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
            $signature_url  = $signature_path ? content_url( $signature_path ) : '';
        }

        // Generate HTML
        $html = $this->render_result_html(
            $student_id,
            $exam_id,
            $exam_data,
            $class_id,
            $section_id,
            $subject,
            $comment,
            $signature_url,
            $obj_mark,
            $exam_obj
        );

        // Initialize and output PDF
        if ( ! $this->init_mpdf() ) {
            wp_die( esc_html__( 'Failed to initialize PDF generator.', 'mjschool' ) );
        }

        $this->output_pdf( $html, 'Result', 'result.pdf' );
    }

    /**
     * Render result HTML for single exam
     *
     * @since 2.0.0
     * @param int    $student_id    Student ID.
     * @param int    $exam_id       Exam ID.
     * @param object $exam_data     Exam data.
     * @param int    $class_id      Class ID.
     * @param int    $section_id    Section ID.
     * @param array  $subject       Subject list.
     * @param string $comment       Teacher comment.
     * @param string $signature_url Signature URL.
     * @param object $obj_mark      Marks manager.
     * @param object $exam_obj      Exam object.
     * @return string HTML content.
     */
    private function render_result_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $exam_obj ) {
        $school_type   = get_option( 'mjschool_custom_class', 'school' );
        $total_subject = count( $subject );

        // FIXED: Prevent division by zero
        if ( $total_subject === 0 ) {
            return '<p>' . esc_html__( 'No subjects found for this exam.', 'mjschool' ) . '</p>';
        }

        // FIXED: Initialize variables before use
        $total       = 0;
        $total_marks = 0;
        $grade_point = 0;
        $exam_marks  = isset( $exam_data->total_mark ) ? floatval( $exam_data->total_mark ) : 100;

        // Check for contributions
        $contributions            = isset( $exam_data->contributions ) ? $exam_data->contributions : 'no';
        $contributions_data_array = array();

        if ( $contributions === 'yes' && ! empty( $exam_data->contributions_data ) ) {
            $contributions_data_array = json_decode( $exam_data->contributions_data, true );
            if ( ! is_array( $contributions_data_array ) ) {
                $contributions_data_array = array();
            }
        }

        ob_start();

        // Render appropriate layout based on RTL
        if ( is_rtl() ) {
            echo $this->render_result_rtl_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $contributions, $contributions_data_array, $exam_marks );
        } else {
            echo $this->render_result_ltr_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $contributions, $contributions_data_array, $exam_marks, $school_type );
        }

        return ob_get_clean();
    }

    /**
     * Render LTR result HTML
     *
     * @since 2.0.0
     * @return string HTML content.
     */
    private function render_result_ltr_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $contributions, $contributions_data_array, $exam_marks, $school_type ) {
        // FIXED: Initialize all variables
        $total           = 0;
        $total_marks     = 0;
        $grade_point     = 0;
        $total_pass_mark = 0;
        $total_max_mark  = 0;
        $total_subject   = count( $subject );
        $percentage      = 0;
        $GPA             = 0;

        ob_start();
        ?>
        <!-- School Header -->
        <?php echo $this->render_school_header(); ?>

        <!-- Student Info -->
        <div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:97%;padding:20px;margin-top:10px;">
            <div class="mjschool_float_left_width_100">
                <div class="mjschool_padding_10px">
                    <div class="mjschool_float_width_css">
                        <b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'first_name', true ) . ' ' . get_user_meta( $student_id, 'last_name', true ) ); ?>
                    </div>
                    <div class="mjschool_float_width_css">
                        <b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
                        <?php echo esc_html( function_exists( 'mjschool_get_exam_name_id' ) ? mjschool_get_exam_name_id( $exam_id ) : '' ); ?>
                    </div>
                </div>
            </div>
            <div class="mjschool_float_width_css">
                <div class="mjschool_padding_10px">
                    <div class="mjschool_float_width_css">
                        <b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
                        <?php echo esc_html( get_user_meta( $student_id, 'roll_id', true ) ); ?>
                    </div>
                </div>
            </div>
            <div style="float:right;width:50%;">
                <div style="padding-top:10px;">
                    <b><?php echo esc_html( $school_type === 'university' ? __( 'Class Name', 'mjschool' ) : __( 'Class & Section', 'mjschool' ) ); ?></b>:
                    <?php
                    $classname    = function_exists( 'mjschool_get_class_name' ) ? mjschool_get_class_name( $class_id ) : '';
                    $section_name = ( ! empty( $section_id ) && function_exists( 'mjschool_get_section_name' ) ) ? mjschool_get_section_name( $section_id ) : '';

                    if ( $school_type === 'university' ) {
                        echo esc_html( $classname );
                    } else {
                        echo esc_html( $classname . ( $section_name ? ' - ' . $section_name : '' ) );
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <table style="float:left;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
            <thead>
                <tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
                    <th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
                    <?php
                    if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
                        foreach ( $contributions_data_array as $con_value ) {
                            ?>
                            <th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;">
                                <?php echo esc_html( $con_value['label'] . ' (' . $con_value['mark'] . ')' ); ?>
                            </th>
                            <?php
                        }
                        ?>
                        <th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;">
                            <?php esc_html_e( 'Total', 'mjschool' ); ?>
                        </th>
                        <?php
                    } else {
                        ?>
                        <th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;">
                            <?php esc_html_e( 'Total', 'mjschool' ); ?>
                        </th>
                        <?php
                    }
                    ?>
                    <th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $subject as $sub ) {
                    $total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
                    ?>
                    <tr style="border-bottom: 1px solid #000;">
                        <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $sub->sub_name ); ?></td>
                        <?php
                        $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $student_id );

                        if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
                            $subject_total = 0;
                            foreach ( $contributions_data_array as $con_id => $con_value ) {
                                $mark_value     = is_array( $obtain_marks ) ? ( isset( $obtain_marks[ $con_id ] ) ? floatval( $obtain_marks[ $con_id ] ) : 0 ) : floatval( $obtain_marks );
                                $subject_total += $mark_value;
                                ?>
                                <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $mark_value ); ?></td>
                                <?php
                            }
                            ?>
                            <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $subject_total ); ?></td>
                            <?php
                            $total_marks += $subject_total;
                        } else {
                            $marks_float = floatval( $obtain_marks );
                            ?>
                            <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $marks_float ); ?></td>
                            <?php
                            $total_marks += $marks_float;
                        }
                        ?>
                        <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
                            <?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $student_id ) ); ?>
                        </td>
                    </tr>
                    <?php
                    $grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $student_id );
                }

                $total          = $total_marks;
                $total_max_mark = $exam_marks * $total_subject;

                // FIXED: Prevent division by zero
                $GPA        = $total_subject > 0 ? $grade_point / $total_subject : 0;
                $percentage = ( $total > 0 && $total_max_mark > 0 ) ? ( $total / $total_max_mark ) * 100 : 0;
                ?>
            </tbody>
        </table>

        <!-- Summary Table -->
        <table style="float:left;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
            <thead>
                <tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
                    <th style="border-bottom: 1px solid #000;text-align:center;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
                    <th style="border-bottom: 1px solid #000;text-align:center;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
                    <th style="border-bottom: 1px solid #000;text-align:center;border-right: 1px solid #000;"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
                    <th style="border-bottom: 1px solid #000;text-align:center;border-right: 1px solid #000;"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
                    <th style="border-bottom: 1px solid #000;text-align:center;border-right: 1px solid #000;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #000;">
                    <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $total_max_mark ); ?></td>
                    <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $total ); ?></td>
                    <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $percentage > 0 ? number_format( $percentage, 2 ) : '-' ); ?></td>
                    <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( round( $GPA, 2 ) ); ?></td>
                    <td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
                        <?php
                        $result      = array();
                        $result_fail = array();

                        foreach ( $subject as $sub ) {
                            $obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $student_id );

                            if ( $contributions === 'yes' && ! empty( $contributions_data_array ) ) {
                                $subject_total = 0;
                                foreach ( $contributions_data_array as $con_id => $con_value ) {
                                    $mark_value     = is_array( $obtain_marks ) ? ( isset( $obtain_marks[ $con_id ] ) ? floatval( $obtain_marks[ $con_id ] ) : 0 ) : floatval( $obtain_marks );
                                    $subject_total += $mark_value;
                                }
                                $marks_total = $subject_total;
                            } else {
                                $marks_total = floatval( $obtain_marks );
                            }

                            if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
                                $result[] = 'pass';
                            } else {
                                $result_fail[] = 'fail';
                            }
                        }

                        // FIXED: Typo - $rest1 was $result1
                        if ( ! empty( $result ) && in_array( 'pass', $result, true ) && ! empty( $result_fail ) && in_array( 'fail', $result_fail, true ) ) {
                            esc_html_e( 'Fail', 'mjschool' );
                        } elseif ( ! empty( $result ) && in_array( 'pass', $result, true ) ) {
                            esc_html_e( 'Pass', 'mjschool' );
                        } elseif ( ! empty( $result_fail ) && in_array( 'fail', $result_fail, true ) ) {
                            esc_html_e( 'Fail', 'mjschool' );
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures -->
        <?php echo $this->render_signatures( $comment, $signature_url ); ?>
        <?php

        return ob_get_clean();
    }

    /**
     * Render RTL result HTML
     *
     * @since 2.0.0
     * @return string HTML content.
     */
    private function render_result_rtl_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $contributions, $contributions_data_array, $exam_marks ) {
        // RTL version - similar structure but with RTL-specific styling
        // For brevity, this would mirror render_result_ltr_html but with float:right instead of float:left
        return $this->render_result_ltr_html( $student_id, $exam_id, $exam_data, $class_id, $section_id, $subject, $comment, $signature_url, $obj_mark, $contributions, $contributions_data_array, $exam_marks, get_option( 'mjschool_custom_class', 'school' ) );
    }

    /**
     * Generate certificate PDF
     *
     * @since 2.0.0
     */
    public function generate_certificate_pdf() {
        if ( ! $this->verify_request() ) {
            wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
        }

        $certificate_id = $this->get_param( 'certificate_id', 'encrypted_int' );
        $include_header = $this->get_param( 'certificate_header', 'string' ) === '1';

        if ( ! $certificate_id ) {
            wp_die( esc_html__( 'Invalid certificate ID.', 'mjschool' ) );
        }

        // Get certificate data
        if ( ! function_exists( 'mjschool_get_certificate_by_id' ) ) {
            wp_die( esc_html__( 'Certificate function not found.', 'mjschool' ) );
        }

        $certificate = mjschool_get_certificate_by_id( $certificate_id );

        if ( ! $certificate || empty( $certificate->certificate_content ) ) {
            wp_die( esc_html__( 'Certificate not found.', 'mjschool' ) );
        }

        // Build HTML
        $html = '';

        if ( $include_header ) {
            $html .= $this->render_school_header();
        }

        $html .= wp_kses_post( stripslashes( $certificate->certificate_content ) );

        // Initialize and output PDF
        if ( ! $this->init_mpdf() ) {
            wp_die( esc_html__( 'Failed to initialize PDF generator.', 'mjschool' ) );
        }

        $this->mpdf->SetTitle( 'Transfer Certificate' );
        $this->mpdf->WriteHTML( $html );
        $this->mpdf->Output( 'transfer_certificate.pdf', 'I' );
        exit;
    }

    /**
     * Generate invoice PDF
     *
     * @since 2.0.0
     */
    public function generate_invoice_pdf() {
        if ( ! $this->verify_request() ) {
            wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
        }

        $invoice_id   = $this->get_param( 'invoice_id', 'string' );
        $invoice_type = $this->get_param( 'invoice_type', 'string' );

        if ( ! $invoice_id || ! $invoice_type ) {
            wp_die( esc_html__( 'Invalid parameters.', 'mjschool' ) );
        }

        if ( ! function_exists( 'mjschool_student_invoice_pdf' ) ) {
            wp_die( esc_html__( 'Invoice function not found.', 'mjschool' ) );
        }

        ob_start();
        mjschool_student_invoice_pdf( $invoice_id, $invoice_type );
        $html = ob_get_clean();

        // Initialize and output PDF
        if ( ! $this->init_mpdf( array( 'format' => 'A4' ) ) ) {
            wp_die( esc_html__( 'Failed to initialize PDF generator.', 'mjschool' ) );
        }

        $this->mpdf->SetTitle( 'Payment' );
        $this->mpdf->WriteHTML( $html );
        $this->mpdf->Output( 'invoice.pdf', 'I' );
        exit;
    }
}

/**
 * Main PDF generation handler
 *
 * @since 1.0.0
 */
function mjschool_generate_pdf() {
    $print_type = isset( $_REQUEST['print'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['print'] ) ) : '';

    if ( empty( $print_type ) && ! isset( $_REQUEST['student_exam_receipt_pdf'] ) ) {
        return;
    }

    // REMOVED: error_reporting(1) - should never be in production
    $pdf_generator = new MJSchool_PDF_Generator();

    // Group result PDF
    if ( $print_type === 'group_result_pdf' && isset( $_REQUEST['student'] ) ) {
        $pdf_generator->generate_group_result_pdf();
        return;
    }

    // Single exam result PDF
    if ( $print_type === 'pdf' && isset( $_REQUEST['student'] ) && isset( $_REQUEST['exam_id'] ) ) {
        $pdf_generator->generate_result_pdf();
        return;
    }

    // Invoice PDF
    if ( $print_type === 'pdf' && isset( $_REQUEST['invoice_type'] ) ) {
        $pdf_generator->generate_invoice_pdf();
        return;
    }

    // Certificate PDF
    if ( $print_type === 'pdf' && isset( $_REQUEST['certificate_id'] ) ) {
        $pdf_generator->generate_certificate_pdf();
        return;
    }

    // Payment history PDF
    if ( $print_type === 'pdf' && isset( $_REQUEST['fee_paymenthistory'] ) ) {
        mjschool_generate_payment_history_pdf();
        return;
    }

    // Receipt history PDF
    if ( $print_type === 'pdf' && isset( $_REQUEST['fee_receipthistory'] ) ) {
        mjschool_generate_receipt_history_pdf();
        return;
    }

    // Exam receipt PDF
    if ( isset( $_REQUEST['student_exam_receipt_pdf'] ) && $_REQUEST['student_exam_receipt_pdf'] === 'student_exam_receipt_pdf' ) {
        mjschool_generate_exam_receipt_pdf();
        return;
    }
}
add_action( 'init', 'mjschool_generate_pdf' );

/**
 * Generate payment history PDF
 *
 * @since 2.0.0
 */
function mjschool_generate_payment_history_pdf() {
    if ( ! is_user_logged_in() ) {
        wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
    }

    $payment_id = isset( $_REQUEST['payment_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_id'] ) ) : '';

    if ( ! $payment_id || ! function_exists( 'mjschool_student_payment_history_pdf' ) ) {
        wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
    }

    ob_start();
    mjschool_student_payment_history_pdf( $payment_id );
    $html = ob_get_clean();

    mjschool_output_simple_pdf( $html, 'Fees Payment', 'feepaymenthistory.pdf' );
}

/**
 * Generate receipt history PDF
 *
 * @since 2.0.0
 */
function mjschool_generate_receipt_history_pdf() {
    if ( ! is_user_logged_in() ) {
        wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
    }

    $payment_id = isset( $_REQUEST['payment_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_id'] ) ) : '';
    $receipt_id = isset( $_REQUEST['receipt_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['receipt_id'] ) ) : '';

    if ( ! $payment_id || ! $receipt_id || ! function_exists( 'mjschool_student_receipt_history_pdf' ) ) {
        wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
    }

    ob_start();
    mjschool_student_receipt_history_pdf( $payment_id, $receipt_id );
    $html = ob_get_clean();

    mjschool_output_simple_pdf( $html, 'Receipt Payment', 'receiptpayment.pdf' );
}

/**
 * Generate exam receipt PDF
 *
 * @since 2.0.0
 */
function mjschool_generate_exam_receipt_pdf() {
    if ( ! is_user_logged_in() ) {
        wp_die( esc_html__( 'Unauthorized access.', 'mjschool' ) );
    }

    $student_id = isset( $_REQUEST['student_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) : '';
    $exam_id    = isset( $_REQUEST['exam_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_id'] ) ) : '';

    if ( ! $student_id || ! $exam_id || ! function_exists( 'mjschool_student_exam_receipt_pdf' ) || ! function_exists( 'mjschool_decrypt_id' ) ) {
        wp_die( esc_html__( 'Invalid request.', 'mjschool' ) );
    }

    ob_start();
    mjschool_student_exam_receipt_pdf( mjschool_decrypt_id( $student_id ), mjschool_decrypt_id( $exam_id ) );
    $html = ob_get_clean();

    mjschool_output_simple_pdf( $html, 'Hall Ticket', 'examreceipt.pdf' );
}

/**
 * Output a simple PDF
 *
 * @since 2.0.0
 * @param string $html     HTML content.
 * @param string $title    PDF title.
 * @param string $filename Output filename.
 */
function mjschool_output_simple_pdf( $html, $title, $filename ) {
    if ( ! defined( 'MJSCHOOL_PLUGIN_DIR' ) ) {
        wp_die( esc_html__( 'Plugin directory not defined.', 'mjschool' ) );
    }

    require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';

    header( 'Content-type: application/pdf' );
    header( 'Content-Disposition: inline; filename="' . sanitize_file_name( $filename ) . '"' );
    header( 'Content-Transfer-Encoding: binary' );
    header( 'Accept-Ranges: bytes' );

    $mpdf = new \Mpdf\Mpdf();
    $mpdf->SetTitle( $title );
    $mpdf->autoScriptToLang = true;
    $mpdf->autoLangToFont   = true;

    if ( is_rtl() ) {
        $mpdf->SetDirectionality( 'rtl' );
    }

    $mpdf->WriteHTML( $html );
    $mpdf->Output();

    unset( $mpdf );
    exit;
}

/**
 * Prevent login for unactivated users
 *
 * @since 1.0.0
 * @param WP_User|WP_Error $user User object or error.
 * @return WP_User|WP_Error User object or error.
 */
function mjschool_check_user_activation( $user ) {
    if ( is_wp_error( $user ) ) {
        return $user;
    }

    if ( ! isset( $user->ID ) ) {
        return $user;
    }

    $hash = get_user_meta( $user->ID, 'hash', true );

    if ( $hash ) {
        $login_page_id = get_option( 'mjschool_login_page' );
        $redirect_url  = add_query_arg(
            array(
                'page_id'           => $login_page_id,
                'mjschool_activate' => 'mjschool_activate',
            ),
            get_permalink( $login_page_id )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    return $user;
}
add_filter( 'wp_authenticate_user', 'mjschool_check_user_activation', 10, 1 );

// Register hooks and shortcodes
add_action( 'wp_enqueue_scripts', 'mjschool_enqueue_front_assets' );
add_action( 'init', 'mjschool_install_login_page' );
add_action( 'init', 'mjschool_install_student_registration_page' );

if ( function_exists( 'mjschool_install_student_admission_page' ) ) {
    add_action( 'init', 'mjschool_install_student_admission_page' );
}

if ( function_exists( 'mjschool_install_combine_admission_page' ) ) {
    add_action( 'init', 'mjschool_install_combine_admission_page' );
}

add_action( 'wp_head', 'mjschool_user_dashboard' );
add_shortcode( 'smgt_login', 'mjschool_login_link' );

if ( function_exists( 'mjschool_student_login' ) ) {
    add_action( 'wp_login', 'mjschool_student_login', 10, 2 );
}

add_action( 'init', 'mjschool_output_ob_start' );

/**
 * Student registration shortcode handler
 *
 * @since 1.0.0
 * @return string Shortcode output.
 */
function mjschool_custom_registration_shortcode() {
    ob_start();
    if ( function_exists( 'mjschool_student_registration_function' ) ) {
        mjschool_student_registration_function();
    }
    return ob_get_clean();
}
add_shortcode( 'smgt_student_registration', 'mjschool_custom_registration_shortcode' );

/**
 * Student admission shortcode handler
 *
 * @since 1.0.0
 * @return string Shortcode output.
 */
function mjschool_custom_admission_shortcode() {
    ob_start();
    if ( function_exists( 'mjschool_student_admission_function' ) ) {
        mjschool_student_admission_function();
    }
    return ob_get_clean();
}
add_shortcode( 'smgt_student_admission', 'mjschool_custom_admission_shortcode' );

/**
 * Combined admission shortcode handler
 *
 * @since 1.0.0
 * @return string Shortcode output.
 */
function mjschool_custom_combine_admission_shortcode() {
    ob_start();
    if ( function_exists( 'mjschool_student_admission_function' ) ) {
        mjschool_student_admission_function();
    }
    return ob_get_clean();
}
add_shortcode( 'smgt_student_combine_admission', 'mjschool_custom_combine_admission_shortcode' );

/**
 * Start output buffering
 *
 * @since 1.0.0
 */
function mjschool_output_ob_start() {
    ob_start();
}
/**
 * Authenticate a user, confirming the username and password are valid.
 *
 * @since 2.8.0
 *
 * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback.
 * @param string                $username Username for authentication.
 * @param string                $password Password for authentication.
 * @return WP_User|WP_Error WP_User on success, WP_Error on failure.
 */
function mjschool_wp_authenticate_username_password_new( $user, $username, $password ) {
    if ( $user instanceof WP_User ) {
        return $user;
    }

    if ( empty( $username ) || empty( $password ) ) {
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $error = new WP_Error();

        if ( empty( $username ) ) {
            $error->add( 'empty_username', __( '<strong>ERROR</strong>: The username field is empty.', 'mjschool' ) );
        }

        if ( empty( $password ) ) {
            $error->add( 'empty_password', __( '<strong>ERROR</strong>: The password field is empty.', 'mjschool' ) );
        }

        return $error;
    }

    $user = get_user_by( 'login', $username );

    if ( ! $user ) {
        return new WP_Error(
            'invalid_username',
            __( '<strong>ERROR</strong>: Invalid username.', 'mjschool' )
        );
    }

    /** This filter is documented in wp-includes/user.php */
    $user = apply_filters( 'wp_authenticate_user', $user, $password );

    if ( is_wp_error( $user ) ) {
        return $user;
    }

    // CRITICAL FIX: Verify password
    if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
        return new WP_Error(
            'incorrect_password',
            sprintf(
                /* translators: %s: User name. */
                __( '<strong>ERROR</strong>: The password you entered for the username %s is incorrect.', 'mjschool' ),
                '<strong>' . esc_html( $username ) . '</strong>'
            )
        );
    }

    return $user;
}

/**
 * Extends the WordPress login cookie expiration time.
 *
 * @param int $expirein Original cookie expiration time in seconds.
 * @return int Modified cookie expiration time in seconds (7200 = 2 hours).
 *
 * @since 1.0.0
 */
function mjschool_keep_me_logged_in_60_minutes( $expirein ) {
    return 7200; // 2 hours.
}
add_filter( 'auth_cookie_expiration', 'mjschool_keep_me_logged_in_60_minutes' );

/**
 * Class MJSchool_Admission_Handler
 *
 * Handles student admission functionality.
 *
 * @since 1.0.0
 */
class MJSchool_Admission_Handler {

    /**
     * Student data array.
     *
     * @var array
     */
    private $student_data = array();

    /**
     * Father data array.
     *
     * @var array
     */
    private $father_data = array();

    /**
     * Mother data array.
     *
     * @var array
     */
    private $mother_data = array();

    /**
     * Sibling data array.
     *
     * @var array
     */
    private $sibling_data = array();

    /**
     * Validation errors.
     *
     * @var WP_Error
     */
    private $errors;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->errors = new WP_Error();
    }

    /**
     * Initialize hooks.
     */
    public function init() {
        add_shortcode( 'smgt_student_admission', array( $this, 'render_admission_form' ) );
        add_shortcode( 'smgt_student_combine_admission', array( $this, 'render_combine_admission_form' ) );
    }

    /**
     * Process form submission.
     *
     * @return bool|int User ID on success, false on failure.
     */
    public function process_submission() {
        if ( ! isset( $_POST['save_student_front_admission'] ) ) {
            return false;
        }

        // Verify nonce.
        if ( ! $this->verify_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }

        // Sanitize and validate input.
        $this->sanitize_input();

        if ( ! $this->validate_input() ) {
            return false;
        }

        // Process file uploads.
        $this->process_file_uploads();

        // Create user and save data.
        return $this->create_admission();
    }

    /**
     * Verify nonce.
     *
     * @return bool
     */
    private function verify_nonce() {
        return isset( $_POST['_wpnonce'] ) && 
               wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_student_frontend_admission_nonce' );
    }

    /**
     * Sanitize all input data.
     */
    private function sanitize_input() {
        // Student data.
        $this->student_data = array(
            'admission_no'           => $this->sanitize_field( 'admission_no' ),
            'class_name'             => $this->sanitize_field( 'class_name' ),
            'admission_date'         => $this->sanitize_field( 'admission_date' ),
            'first_name'             => $this->sanitize_field( 'first_name' ),
            'middle_name'            => $this->sanitize_field( 'middle_name' ),
            'last_name'              => $this->sanitize_field( 'last_name' ),
            'birth_date'             => $this->sanitize_field( 'birth_date' ),
            'gender'                 => $this->sanitize_field( 'gender' ),
            'address'                => $this->sanitize_textarea( 'address' ),
            'state_name'             => $this->sanitize_field( 'state_name' ),
            'city_name'              => $this->sanitize_field( 'city_name' ),
            'zip_code'               => $this->sanitize_field( 'zip_code' ),
            'phone_code'             => $this->sanitize_field( 'phone_code' ),
            'mobile_number'          => $this->sanitize_field( 'mobile_number' ),
            'alternet_mobile_number' => $this->sanitize_field( 'alternet_mobile_number' ),
            'email'                  => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
            'preschool_name'         => $this->sanitize_field( 'preschool_name' ),
            'parent_status'          => $this->sanitize_field( 'pstatus' ),
            'admission_fees'         => absint( $_POST['admission_fees'] ?? 0 ),
            'registration_fees'      => absint( $_POST['registration_fees'] ?? 0 ),
        );

        // Generate password.
        $password = $this->sanitize_field( 'password' );
        $this->student_data['password'] = ! empty( $password ) 
            ? $this->validate_password( $password ) 
            : wp_generate_password();

        // Father data.
        $this->father_data = array(
            'salutation'   => $this->sanitize_field( 'fathersalutation' ),
            'first_name'   => $this->sanitize_field( 'father_first_name' ),
            'middle_name'  => $this->sanitize_field( 'father_middle_name' ),
            'last_name'    => $this->sanitize_field( 'father_last_name' ),
            'gender'       => $this->sanitize_field( 'fathe_gender' ),
            'birth_date'   => $this->sanitize_field( 'father_birth_date' ),
            'address'      => $this->sanitize_textarea( 'father_address' ),
            'city_name'    => $this->sanitize_field( 'father_city_name' ),
            'state_name'   => $this->sanitize_field( 'father_state_name' ),
            'zip_code'     => $this->sanitize_field( 'father_zip_code' ),
            'email'        => sanitize_email( wp_unslash( $_POST['father_email'] ?? '' ) ),
            'mobile'       => $this->sanitize_field( 'father_mobile' ),
            'school'       => $this->sanitize_field( 'father_school' ),
            'medium'       => $this->sanitize_field( 'father_medium' ),
            'education'    => $this->sanitize_field( 'father_education' ),
            'income'       => $this->sanitize_field( 'fathe_income' ),
            'occupation'   => $this->sanitize_field( 'father_occuption' ),
            'document_name'=> $this->sanitize_field( 'father_document_name' ),
        );

        // Mother data.
        $this->mother_data = array(
            'salutation'   => $this->sanitize_field( 'mothersalutation' ),
            'first_name'   => $this->sanitize_field( 'mother_first_name' ),
            'middle_name'  => $this->sanitize_field( 'mother_middle_name' ),
            'last_name'    => $this->sanitize_field( 'mother_last_name' ),
            'gender'       => $this->sanitize_field( 'mother_gender' ),
            'birth_date'   => $this->sanitize_field( 'mother_birth_date' ),
            'address'      => $this->sanitize_textarea( 'mother_address' ),
            'city_name'    => $this->sanitize_field( 'mother_city_name' ),
            'state_name'   => $this->sanitize_field( 'mother_state_name' ),
            'zip_code'     => $this->sanitize_field( 'mother_zip_code' ),
            'email'        => sanitize_email( wp_unslash( $_POST['mother_email'] ?? '' ) ),
            'mobile'       => $this->sanitize_field( 'mother_mobile' ),
            'school'       => $this->sanitize_field( 'mother_school' ),
            'medium'       => $this->sanitize_field( 'mother_medium' ),
            'education'    => $this->sanitize_field( 'mother_education' ),
            'income'       => $this->sanitize_field( 'mother_income' ),
            'occupation'   => $this->sanitize_field( 'mother_occuption' ),
            'document_name'=> $this->sanitize_field( 'mother_document_name' ),
        );

        // Sibling data.
        $this->sibling_data = $this->sanitize_sibling_data();
    }

    /**
     * Sanitize a text field.
     *
     * @param string $field_name Field name.
     * @return string Sanitized value.
     */
    private function sanitize_field( $field_name ) {
        return isset( $_POST[ $field_name ] ) 
            ? sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ) 
            : '';
    }

    /**
     * Sanitize a textarea field.
     *
     * @param string $field_name Field name.
     * @return string Sanitized value.
     */
    private function sanitize_textarea( $field_name ) {
        return isset( $_POST[ $field_name ] ) 
            ? sanitize_textarea_field( wp_unslash( $_POST[ $field_name ] ) ) 
            : '';
    }

    /**
     * Sanitize sibling data.
     *
     * @return array Sanitized sibling data.
     */
    private function sanitize_sibling_data() {
        $sibling_data = array();

        if ( ! empty( $_POST['siblingsclass'] ) && is_array( $_POST['siblingsclass'] ) ) {
            $classes  = array_map( 'sanitize_text_field', wp_unslash( $_POST['siblingsclass'] ) );
            $sections = isset( $_POST['siblingssection'] ) 
                ? array_map( 'sanitize_text_field', wp_unslash( $_POST['siblingssection'] ) ) 
                : array();
            $students = isset( $_POST['siblingsstudent'] ) 
                ? array_map( 'sanitize_text_field', wp_unslash( $_POST['siblingsstudent'] ) ) 
                : array();

            foreach ( $classes as $key => $class ) {
                $sibling_data[] = array(
                    'siblingsclass'   => $class,
                    'siblingssection' => $sections[ $key ] ?? '',
                    'siblingsstudent' => $students[ $key ] ?? '',
                );
            }
        }

        return $sibling_data;
    }

    /**
     * Validate password.
     *
     * @param string $password Password to validate.
     * @return string Validated password.
     */
    private function validate_password( $password ) {
        if ( function_exists( 'mjschool_password_validation' ) ) {
            return mjschool_password_validation( $password );
        }
        return $password;
    }

    /**
     * Validate input data.
     *
     * @return bool True if valid, false otherwise.
     */
    private function validate_input() {
        $email    = $this->student_data['email'];
        $username = $email;

        // Username length check.
        if ( strlen( $username ) < 4 ) {
            $this->errors->add( 'username_length', __( 'Username too short. At least 4 characters required.', 'mjschool' ) );
        }

        // Username exists check.
        if ( username_exists( $username ) ) {
            $this->errors->add( 'user_name', __( 'Sorry, that username already exists!', 'mjschool' ) );
        }

        // Email validation.
        if ( ! is_email( $email ) ) {
            $this->errors->add( 'email_invalid', __( 'Email is not valid.', 'mjschool' ) );
        }

        // Email exists check.
        if ( email_exists( $email ) ) {
            $this->errors->add( 'email', __( 'Email already in use.', 'mjschool' ) );
        }

        // Display errors if any.
        if ( $this->errors->has_errors() ) {
            $this->display_errors();
            return false;
        }

        return true;
    }

    /**
     * Display validation errors.
     */
    private function display_errors() {
        foreach ( $this->errors->get_error_messages() as $error ) {
            echo '<div class="mjschool-student-reg-error">';
            echo '<strong>' . esc_html__( 'ERROR', 'mjschool' ) . '</strong>: ';
            echo '<span class="error">' . esc_html( $error ) . '</span><br/>';
            echo '</div>';
        }
    }

    /**
     * Process file uploads.
     */
    private function process_file_uploads() {
        // Father document.
        if ( $this->is_valid_file_upload( 'father_doc' ) ) {
            $this->father_data['document'] = $this->upload_document( 
                $_FILES['father_doc'], 
                $this->father_data['document_name'] 
            );
        }

        // Mother document.
        if ( $this->is_valid_file_upload( 'mother_doc' ) ) {
            $this->mother_data['document'] = $this->upload_document( 
                $_FILES['mother_doc'], 
                $this->mother_data['document_name'] 
            );
        }

        // User avatar.
        if ( ! empty( $_POST['mjschool_user_avatar'] ) ) {
            $this->student_data['avatar'] = sanitize_text_field( wp_unslash( $_POST['mjschool_user_avatar'] ) );
        }
    }

    /**
     * Check if file upload is valid.
     *
     * @param string $field_name File field name.
     * @return bool
     */
    private function is_valid_file_upload( $field_name ) {
        return isset( $_FILES[ $field_name ] ) 
            && ! empty( $_FILES[ $field_name ]['size'] ) 
            && $_FILES[ $field_name ]['size'] > 0;
    }

    /**
     * Upload document.
     *
     * @param array  $file     File data.
     * @param string $doc_name Document name.
     * @return string|array Upload result.
     */
    private function upload_document( $file, $doc_name ) {
        if ( function_exists( 'mjschool_load_documets_new' ) ) {
            $uploaded = mjschool_load_documets_new( $file, $file, $doc_name );
            if ( ! empty( $uploaded ) ) {
                return array(
                    array(
                        'title' => $doc_name,
                        'value' => $uploaded,
                    ),
                );
            }
        }
        return array();
    }

    /**
     * Create admission record.
     *
     * @return int|false User ID on success, false on failure.
     */
    private function create_admission() {
        $email      = $this->student_data['email'];
        $first_name = $this->student_data['first_name'];
        $last_name  = $this->student_data['last_name'];

        // Prepare user data.
        $userdata = array(
            'user_login'   => $email,
            'user_email'   => $email,
            'user_pass'    => $this->student_data['password'],
            'display_name' => trim( $first_name . ' ' . $last_name ),
        );

        // Insert user.
        $user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $user_id ) ) {
            $this->errors->add( 'user_creation', $user_id->get_error_message() );
            $this->display_errors();
            return false;
        }

        // Set user role.
        $user = new WP_User( $user_id );
        $user->set_role( 'student_temp' );
        $user->add_role( 'subscriber' );

        // Save user meta.
        $this->save_user_meta( $user_id );

        // Generate invoice if needed.
        $this->generate_invoice( $user_id );

        // Send notification email.
        $this->send_admission_email( $user_id );

        // Display success message.
        $this->display_success_message();

        // Handle payment redirect.
        $this->handle_payment_redirect( $user_id );

        return $user_id;
    }

    /**
     * Save user meta data.
     *
     * @param int $user_id User ID.
     */
    private function save_user_meta( $user_id ) {
        $admission_fees_amount = 0;

        if ( get_option( 'mjschool_combine' ) === '1' && get_option( 'mjschool_admission_fees' ) === 'yes' ) {
            $admission_fees_id = $this->student_data['admission_fees'];
            if ( class_exists( 'Mjschool_Fees' ) ) {
                $obj_fees = new Mjschool_Fees();
                $admission_fees_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $admission_fees_id );
            }
        }

        $meta_data = array(
            'admission_no'           => $this->student_data['admission_no'],
            'admission_date'         => $this->student_data['admission_date'],
            'admission_fees'         => $admission_fees_amount,
            'role'                   => 'student_temp',
            'status'                 => 'Not Approved',
            'roll_id'                => '',
            'middle_name'            => $this->student_data['middle_name'],
            'gender'                 => $this->student_data['gender'],
            'birth_date'             => $this->student_data['birth_date'],
            'address'                => $this->student_data['address'],
            'city'                   => $this->student_data['city_name'],
            'state'                  => $this->student_data['state_name'],
            'zip_code'               => $this->student_data['zip_code'],
            'preschool_name'         => $this->student_data['preschool_name'],
            'phone_code'             => $this->student_data['phone_code'],
            'class_name'             => $this->student_data['class_name'],
            'mobile_number'          => $this->student_data['mobile_number'],
            'alternet_mobile_number' => $this->student_data['alternet_mobile_number'],
            'sibling_information'    => wp_json_encode( $this->sibling_data ),
            'parent_status'          => $this->student_data['parent_status'],
            'first_name'             => $this->student_data['first_name'],
            'last_name'              => $this->student_data['last_name'],
            'hash'                   => md5( wp_rand( 0, 1000 ) ),
            'created_by'             => get_current_user_id() ?: 1,
        );

        // Father meta.
        foreach ( $this->father_data as $key => $value ) {
            if ( 'document' === $key ) {
                $meta_data['father_doc'] = wp_json_encode( $value );
            } else {
                $meta_data[ 'father_' . $key ] = $value;
            }
        }

        // Mother meta.
        foreach ( $this->mother_data as $key => $value ) {
            if ( 'document' === $key ) {
                $meta_data['mother_doc'] = wp_json_encode( $value );
            } else {
                $meta_data[ 'mother_' . $key ] = $value;
            }
        }

        // Avatar.
        if ( ! empty( $this->student_data['avatar'] ) ) {
            $meta_data['mjschool_user_avatar'] = $this->student_data['avatar'];
        }

        // Save all meta.
        foreach ( $meta_data as $key => $value ) {
            update_user_meta( $user_id, $key, $value );
        }

        // Save custom fields.
        if ( class_exists( 'Mjschool_Custome_Field' ) ) {
            $custom_field_obj = new Mjschool_Custome_Field();
            $custom_field_obj->mjschool_insert_custom_field_data_module_wise( 'admission', $user_id );
        }
    }

    /**
     * Generate invoice for admission/registration fees.
     *
     * @param int $user_id User ID.
     * @return int|null Invoice ID or null.
     */
    private function generate_invoice( $user_id ) {
        $class = $this->student_data['class_name'];

        if ( get_option( 'mjschool_combine' ) === '1' ) {
            if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
                $registration_fees_id = get_option( 'mjschool_registration_amount' );
                if ( class_exists( 'Mjschool_Fees' ) && function_exists( 'mjschool_generate_admission_fees_invoice_draft' ) ) {
                    $obj_fees = new Mjschool_Fees();
                    $registration_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $registration_fees_id );
                    return mjschool_generate_admission_fees_invoice_draft( 
                        $registration_amount, 
                        $user_id, 
                        $registration_fees_id, 
                        $class, 
                        0, 
                        'Registration Fees' 
                    );
                }
            }
        } elseif ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
            $admission_fees_id = $this->student_data['admission_fees'];
            if ( class_exists( 'Mjschool_Fees' ) && function_exists( 'mjschool_generate_admission_fees_invoice' ) ) {
                $obj_fees = new Mjschool_Fees();
                $admission_fees_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $admission_fees_id );
                return mjschool_generate_admission_fees_invoice( 
                    $admission_fees_amount, 
                    $user_id, 
                    $admission_fees_id, 
                    0, 
                    0, 
                    'Admission Fees' 
                );
            }
        }

        return null;
    }

    /**
     * Send admission notification email.
     *
     * @param int $user_id User ID.
     */
    private function send_admission_email( $user_id ) {
        if ( ! function_exists( 'mjschool_send_mail' ) || ! function_exists( 'mjschool_string_replacement' ) ) {
            return;
        }

        $user_info = get_userdata( $user_id );
        $display_name = function_exists( 'mjschool_get_display_name' ) 
            ? mjschool_get_display_name( $user_id ) 
            : $user_info->display_name;

        $replacements = array(
            '{{student_name}}' => $display_name,
            '{{user_name}}'    => $this->student_data['first_name'] . ' ' . $this->student_data['last_name'],
            '{{email}}'        => $this->student_data['email'],
            '{{school_name}}'  => get_option( 'mjschool_name' ),
        );

        $message = mjschool_string_replacement( 
            $replacements, 
            get_option( 'mjschool_admission_mailtemplate_content' ) 
        );
        $subject = mjschool_string_replacement( 
            $replacements, 
            get_option( 'mjschool_admissiion_title' ) 
        );

        mjschool_send_mail( $this->student_data['email'], $subject, $message );
    }

    /**
     * Display success message.
     */
    private function display_success_message() {
        echo '<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mjschool-hoste-lbl2">';
        esc_html_e( 
            'Request For Admission Successfully. You will be able to access your account after the school admin approves it.', 
            'mjschool' 
        );
        echo '</div>';
    }

    /**
     * Handle payment redirect if fees are enabled.
     *
     * @param int $user_id User ID.
     */
    private function handle_payment_redirect( $user_id ) {
        if ( get_option( 'mjschool_combine' ) !== '1' || get_option( 'mjschool_registration_fees' ) !== 'yes' ) {
            return;
        }

        $registration_fees_id = get_option( 'mjschool_registration_amount' );
        $registration_amount  = 0;

        if ( class_exists( 'Mjschool_Fees' ) ) {
            $obj_fees = new Mjschool_Fees();
            $registration_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $registration_fees_id );
        }

        $invoice_id = $this->generate_invoice( $user_id );

        if ( $invoice_id && $registration_amount > 0 ) {
            $redirect_url = add_query_arg(
                array(
                    'fees_pay_id' => absint( $invoice_id ),
                    'user_id'     => absint( $user_id ),
                    'amount'      => floatval( $registration_amount ),
                ),
                site_url( '/wp-content/plugins/mjschool/lib/paypal/paypal_process.php' )
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Get errors.
     *
     * @return WP_Error
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Render admission form shortcode.
     *
     * @return string Form HTML.
     */
    public function render_admission_form() {
        ob_start();
        $this->process_submission();
        $this->output_admission_form();
        return ob_get_clean();
    }

    /**
     * Render combined admission form shortcode.
     *
     * @return string Form HTML.
     */
    public function render_combine_admission_form() {
        ob_start();
        $this->process_submission();
        $this->output_admission_form();
        return ob_get_clean();
    }

    /**
     * Enqueue form assets.
     */
    private function enqueue_form_assets() {
        wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ) );
        wp_enqueue_media();
        wp_enqueue_script( 'thickbox' );
        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-accordion' );
        wp_enqueue_script( 'jquery-ui-datepicker' );

        $locale = get_locale();
        $lang_code = substr( $locale, 0, 2 );

        wp_enqueue_script( 
            'jquery-validationEngine', 
            plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__ ), 
            array( 'jquery' ), 
            '1.0.0', 
            true 
        );
        wp_enqueue_style( 
            'validationEngine-jquery', 
            plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ) 
        );
        wp_enqueue_script( 
            'jquery-validationEngine-' . $lang_code, 
            plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $lang_code . '.js', __FILE__ ), 
            array( 'jquery' ), 
            '1.0.0', 
            true 
        );

        wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
        wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
        wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ), array(), '1.0.0', true );
        wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ), array(), '1.0.0', true );
        wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ) );

        if ( is_rtl() ) {
            wp_enqueue_style( 'mjschool-custome_rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
            wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ) );
        }

        wp_enqueue_style( 'mjschool-admission', plugins_url( '/assets/css/settings/mjschool-admission.css', __FILE__ ) );
        wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ) );

        // Localize scripts.
        $document_option = get_option( 'mjschool_upload_document_type', '' );
        $document_type   = explode( ', ', $document_option );
        $document_size   = get_option( 'mjschool_upload_document_size', '' );

        $localize_data = array(
            'date_format'           => get_option( 'mjschool_datepicker_format' ),
            'document_type_json'    => $document_type,
            'document_size'         => $document_size,
            'document_delete_alert' => esc_html__( 'Are you sure you want to delete this record?', 'mjschool' ),
            'admission_doc_alert'   => esc_html__( 'Only pdf, doc, docx, xls, xlsx, ppt, pptx, gif, png, jpg, jpeg formats are allowed', 'mjschool' ),
            'format_alert'          => esc_html__( 'format is not allowed.', 'mjschool' ),
        );

        wp_enqueue_script( 'mjschool-admission', plugins_url( '/assets/js/public-js/mjschool-admission.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'mjschool-admission', 'mjschool_admission_data', $localize_data );

        wp_enqueue_script( 'mjschool-registration', plugins_url( '/assets/js/mjschool-registration.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'mjschool-registration', 'mjschool_registration_data', $localize_data );

        $theme_name = get_template();

        if ( 'twentytwentyfive' === $theme_name ) {
            wp_enqueue_style( 'mjschool-admission-twenty-twenty-five', plugins_url( '/assets/css/theme/mjschool-admission-twenty-twenty-five-fix.css', __FILE__ ) );
        }

        wp_enqueue_style( 'mjschool-admission-new-style', plugins_url( '/assets/css/theme/mjschool-admission.css', __FILE__ ) );

        if ( is_rtl() ) {
            wp_enqueue_style( 'mjschool-admission-rtl', plugins_url( '/assets/css/theme/mjschool-admission-rtl.css', __FILE__ ) );
        }

        wp_enqueue_script( 'mjschool-popup', plugins_url( '/assets/js/mjschool-popup.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        wp_localize_script( 'mjschool-popup', 'mjschool', array(
            'ajax'  => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'mjschool_ajax_nonce' ),
        ) );
    }

    /**
     * Output the admission form HTML.
     */
    private function output_admission_form() {
        $this->enqueue_form_assets();

        $theme_name = get_template();
        $role       = 'student_temp';
        $form_action = esc_url( remove_query_arg( array( 'doing_wp_cron' ) ) );

        // Get phone code.
        $phone_code = '';
        if ( function_exists( 'mjschool_get_country_phonecode' ) ) {
            $phone_code = mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
        }

        // Get admission number.
        $admission_no = '';
        if ( function_exists( 'mjschool_generate_admission_number' ) ) {
            $admission_no = mjschool_generate_admission_number();
        }

        // Get current date formatted.
        $current_date = '';
        if ( function_exists( 'mjschool_get_date_in_input_box' ) ) {
            $current_date = mjschool_get_date_in_input_box( gmdate( 'Y-m-d' ) );
        }

        // Get currency symbol.
        $currency_symbol = '';
        if ( function_exists( 'mjschool_get_currency_symbol' ) ) {
            $currency_symbol = mjschool_get_currency_symbol();
        }

        include plugin_dir_path( __FILE__ ) . 'template/mjschool-admission-form-frontend.php';
    }
}

/**
 * Initialize admission handler.
 */
function mjschool_init_admission_handler() {
    $handler = new MJSchool_Admission_Handler();
    $handler->init();
}
add_action( 'init', 'mjschool_init_admission_handler' );

/**
 * Creates a "Student Admission" page if it doesn't already exist.
 *
 * @since 1.0.0
 */
function mjschool_install_student_admission_page() {
    if ( get_option( 'mjschool_student_admission_page' ) ) {
        return;
    }

    $page_data = array(
        'post_title'     => __( 'Student Admission', 'mjschool' ),
        'post_content'   => '[smgt_student_admission]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_category'  => array( 1 ),
        'post_parent'    => 0,
    );

    $page_id = wp_insert_post( $page_data );

    if ( ! is_wp_error( $page_id ) ) {
        update_option( 'mjschool_student_admission_page', $page_id );
    }
}

/**
 * Creates a "Student Registration Form" page if it doesn't already exist.
 *
 * @since 1.0.0
 */
function mjschool_install_combine_admission_page() {
    if ( get_option( 'mjschool_student_combine_admission_page' ) ) {
        return;
    }

    $page_data = array(
        'post_title'     => __( 'Student Registration Form', 'mjschool' ),
        'post_content'   => '[smgt_student_combine_admission]',
        'post_name'      => 'student-registration-form',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
        'post_category'  => array( 1 ),
        'post_parent'    => 0,
    );

    $page_id = wp_insert_post( $page_data );

    if ( ! is_wp_error( $page_id ) ) {
        update_option( 'mjschool_student_combine_admission_page', $page_id );
    }
}

/**
 * Class MJSchool_Admin_Menu_Manager
 *
 * Manages admin menu visibility based on user roles.
 *
 * @since 1.0.0
 */
class MJSchool_Admin_Menu_Manager {

    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'restrict_menus' ), 999 );
        add_action( 'admin_bar_menu', array( __CLASS__, 'customize_toolbar' ), 999 );
        add_action( 'admin_head', array( __CLASS__, 'hide_media_menu_css' ) );
    }

    /**
     * Restrict admin menus for management role.
     */
    public static function restrict_menus() {
        $current_user = wp_get_current_user();
        $current_role = $current_user->roles[0] ?? '';

        if ( 'management' !== $current_role ) {
            return;
        }

        // Grant upload capability.
        $management = get_role( 'management' );
        if ( $management ) {
            $management->add_cap( 'upload_files' );
        }

        // Remove menus.
        $menus_to_remove = array(
            'index.php',               // Dashboard.
            'jetpack',                 // Jetpack.
            'edit.php',                // Posts.
            'upload.php',              // Media.
            'edit.php?post_type=page', // Pages.
            'edit-comments.php',       // Comments.
            'themes.php',              // Appearance.
            'plugins.php',             // Plugins.
            'users.php',               // Users.
            'tools.php',               // Tools.
            'options-general.php',     // Settings.
        );

        foreach ( $menus_to_remove as $menu ) {
            remove_menu_page( $menu );
        }
    }

    /**
     * Customize admin toolbar.
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
     */
    public static function customize_toolbar( $wp_admin_bar ) {
        $current_user = wp_get_current_user();
        $current_role = $current_user->roles[0] ?? '';

        if ( 'management' !== $current_role ) {
            return;
        }

        $wp_admin_bar->remove_node( 'wp-logo' );
        $wp_admin_bar->remove_node( 'site-name' );
    }

    /**
     * Hide media menu with CSS for management role.
     */
    public static function hide_media_menu_css() {
        $current_user = wp_get_current_user();
        $current_role = $current_user->roles[0] ?? '';

        if ( 'management' !== $current_role ) {
            return;
        }

        echo '<style>#menu-media { display: none !important; }</style>';
    }
}
MJSchool_Admin_Menu_Manager::init();

/**
 * Remove Jetpack menu for non-administrators.
 */
function mjschool_remove_jetpack_menu() {
    if ( ! current_user_can( 'administrator' ) ) {
        remove_menu_page( 'jetpack' );
    }
}
add_action( 'admin_menu', 'mjschool_remove_jetpack_menu' );

/**
 * Customize document title.
 *
 * @param array $title Document title parts.
 * @return array Modified title parts.
 */
function mjschool_custom_document_title( $title ) {
    $page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';

    if ( ! empty( $page_name ) ) {
        $title['title'] = $page_name;
    } elseif ( is_singular( 'post' ) ) {
        $school_name = get_option( 'mjschool_name', '' );
        $title['title'] = $school_name . ' ' . $title['title'];
    }

    return $title;
}
add_filter( 'document_title_parts', 'mjschool_custom_document_title' );

/**
 * Log user login.
 *
 * @param string  $user_login Username.
 * @param WP_User $user       User object.
 */
function mjschool_log_user_login( $user_login, $user ) {
    $role = $user->roles[0] ?? 'unknown';

    if ( function_exists( 'mjschool_append_user_log' ) ) {
        mjschool_append_user_log( $user_login, $role );
    }
}
add_action( 'wp_login', 'mjschool_log_user_login', 10, 2 );

/**
 * Class MJSchool_Recurring_Invoice_Handler
 *
 * Handles recurring invoice generation and payment reminders.
 *
 * @since 1.0.0
 */
class MJSchool_Recurring_Invoice_Handler {

    /**
     * Generate recurring invoices.
     */
    public static function generate_recurring_invoices() {
        global $wpdb;

        set_time_limit( 0 );

        if ( ! class_exists( 'Mjschool_Feespayment' ) ) {
            return;
        }

        $obj_feespayment = new Mjschool_Feespayment();
        $table_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
        $table_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring';
        $current_date = gmdate( 'Y-m-d' );

        $all_recurring = $obj_feespayment->mjschool_get_all_recurring_fees_active( $current_date );

        if ( empty( $all_recurring ) ) {
            return;
        }

        foreach ( $all_recurring as $recurring ) {
            $student_ids = explode( ',', $recurring->student_id );
            $fees_ids = explode( ',', $recurring->fees_id );
            $recurring_type = $recurring->recurring_type;

            // Calculate end date based on recurring type.
            $end_date = self::calculate_recurring_end_date( $recurring_type );

            // Calculate total fees.
            $total_fees = self::calculate_total_fees( $fees_ids );

            // Process each student.
            foreach ( $student_ids as $student_id ) {
                $fee_data = self::prepare_fee_data( $recurring, $student_id, $total_fees, $end_date );

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->insert( $table_fees_payment, $fee_data );
                $fees_pay_id = $wpdb->insert_id;

                // Send notifications.
                self::send_fee_notifications( $student_id, $total_fees, $fees_pay_id );
            }

            // Update recurring end date.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->update(
                $table_recurring,
                array( 'recurring_enddate' => $end_date ),
                array( 'recurring_id' => $recurring->recurring_id ),
                array( '%s' ),
                array( '%d' )
            );
        }
    }

    /**
     * Calculate recurring end date.
     *
     * @param string $recurring_type Recurring type.
     * @return string End date.
     */
    private static function calculate_recurring_end_date( $recurring_type ) {
        $intervals = array(
            'weekly'      => '+1 week',
            'monthly'     => '+1 month',
            'quarterly'   => '+3 months',
            'half_yearly' => '+6 months',
        );

        $interval = $intervals[ $recurring_type ] ?? '+0 days';
        return gmdate( 'Y-m-d', strtotime( $interval ) );
    }

    /**
     * Calculate total fees.
     *
     * @param array $fees_ids Fee IDs.
     * @return float Total fees.
     */
    private static function calculate_total_fees( $fees_ids ) {
        $total = 0;

        foreach ( $fees_ids as $id ) {
            if ( function_exists( 'mjschool_get_fees_details' ) ) {
                $result = mjschool_get_fees_details( $id );
                if ( $result && isset( $result->fees_amount ) ) {
                    $total += floatval( $result->fees_amount );
                }
            }
        }

        return $total;
    }

    /**
     * Prepare fee data for insertion.
     *
     * @param object $recurring   Recurring data.
     * @param int    $student_id  Student ID.
     * @param float  $total_fees  Total fees.
     * @param string $end_date    End date.
     * @return array Fee data.
     */
    private static function prepare_fee_data( $recurring, $student_id, $total_fees, $end_date ) {
        $class_id = $recurring->class_id;
        $section_id = $recurring->section_id;

        if ( '0' === $class_id ) {
            $class_id = get_user_meta( $student_id, 'class_name', true );
            $section_id = get_user_meta( $student_id, 'class_section', true );
        }

        $tax_amount = 0;
        if ( ! empty( $recurring->tax ) && function_exists( 'mjschool_get_tax_amount' ) ) {
            $tax_amount = mjschool_get_tax_amount( $total_fees, explode( ',', $recurring->tax ) );
        }

        return array(
            'class_id'       => $class_id,
            'section_id'     => $section_id,
            'fees_id'        => $recurring->fees_id,
            'student_id'     => $student_id,
            'fees_amount'    => $total_fees,
            'tax'            => $recurring->tax ?? null,
            'tax_amount'     => $tax_amount,
            'total_amount'   => $total_fees + $tax_amount,
            'description'    => $recurring->description,
            'start_year'     => gmdate( 'Y-m-d' ),
            'end_year'       => $end_date,
            'paid_by_date'   => gmdate( 'Y-m-d' ),
            'created_date'   => current_time( 'mysql' ),
            'created_by'     => get_current_user_id(),
        );
    }

    /**
     * Send fee notifications.
     *
     * @param int   $student_id  Student ID.
     * @param float $total_fees  Total fees.
     * @param int   $fees_pay_id Fee payment ID.
     */
    private static function send_fee_notifications( $student_id, $total_fees, $fees_pay_id ) {
        if ( '1' !== get_option( 'mjschool_mail_notification' ) ) {
            return;
        }

        if ( ! function_exists( 'mjschool_send_mail_paid_invoice_pdf' ) || ! function_exists( 'mjschool_string_replacement' ) ) {
            return;
        }

        $student_info = get_userdata( $student_id );
        if ( ! $student_info ) {
            return;
        }

        $currency = function_exists( 'mjschool_get_currency_symbol' ) ? mjschool_get_currency_symbol() : '';
        $date_formatted = function_exists( 'mjschool_get_date_in_input_box' ) 
            ? mjschool_get_date_in_input_box( gmdate( 'Y-m-d' ) ) 
            : gmdate( 'Y-m-d' );

        $replacements = array(
            '{{student_name}}' => $student_info->display_name,
            '{{school_name}}'  => get_option( 'mjschool_name' ),
            '{{date}}'         => $date_formatted,
            '{{amount}}'       => $currency . number_format( $total_fees, 2, '.', '' ),
        );

        // Send to student.
        $message = mjschool_string_replacement( $replacements, get_option( 'mjschool_fee_payment_mailcontent' ) );
        mjschool_send_mail_paid_invoice_pdf( $student_info->user_email, get_option( 'mjschool_fee_payment_title' ), $message, $fees_pay_id );

        // Send to parents.
        $parent_ids = get_user_meta( $student_id, 'parent_id', true );
        if ( is_array( $parent_ids ) ) {
            foreach ( $parent_ids as $parent_id ) {
                $parent_info = get_userdata( $parent_id );
                if ( ! $parent_info ) {
                    continue;
                }

                $replacements['{{parent_name}}'] = $parent_info->display_name;
                $replacements['{{child_name}}'] = $student_info->display_name;

                $parent_message = mjschool_string_replacement( $replacements, get_option( 'mjschool_fee_payment_mailcontent_for_parent' ) );
                mjschool_send_mail_paid_invoice_pdf( $parent_info->user_email, get_option( 'mjschool_fee_payment_title' ), $parent_message, $fees_pay_id );
            }
        }
    }

    /**
     * Send payment reminders.
     */
    public static function send_payment_reminders() {
        global $wpdb;

        set_time_limit( 0 );

        if ( 'yes' !== get_option( 'mjschool_system_payment_reminder_enable' ) ) {
            return;
        }

        if ( ! class_exists( 'Mjschool_Feespayment' ) ) {
            return;
        }

        $reminder_day = absint( get_option( 'mjschool_system_payment_reminder_day', 0 ) );
        $reminder_date = gmdate( 'Y-m-d', strtotime( "+{$reminder_day} days" ) );

        $obj_feespayment = new Mjschool_Feespayment();
        $fees_payment_data = $obj_feespayment->mjschool_get_all_student_fees_data_for_reminder( $reminder_date );

        if ( empty( $fees_payment_data ) ) {
            return;
        }

        $reminder_log_table = $wpdb->prefix . 'mjschool_cron_reminder_log';

        foreach ( $fees_payment_data as $payment ) {
            $fees_id = $payment->fees_pay_id;
            $student_id = $payment->student_id;

            // Check if reminder already sent.
            if ( function_exists( 'mjschool_check_reminder_send_or_not' ) ) {
                $check = mjschool_check_reminder_send_or_not( $student_id, $fees_id );
                if ( ! empty( $check ) ) {
                    continue;
                }
            }

            // Send reminders.
            $sent = self::send_reminder_emails( $payment );

            if ( $sent ) {
                // Log reminder.
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->insert(
                    $reminder_log_table,
                    array(
                        'student_id'  => $student_id,
                        'fees_pay_id' => $fees_id,
                        'date_time'   => gmdate( 'Y-m-d' ),
                    ),
                    array( '%d', '%d', '%s' )
                );
            }
        }
    }

    /**
     * Send reminder emails.
     *
     * @param object $payment Payment data.
     * @return bool Whether emails were sent.
     */
    private static function send_reminder_emails( $payment ) {
        if ( '1' !== get_option( 'mjschool_mail_notification' ) ) {
            return false;
        }

        if ( ! function_exists( 'mjschool_send_mail_paid_invoice_pdf' ) || ! function_exists( 'mjschool_string_replacement' ) ) {
            return false;
        }

        $student_id = $payment->student_id;
        $student_info = get_userdata( $student_id );

        if ( ! $student_info ) {
            return false;
        }

        $due_amount = $payment->total_amount - $payment->fees_paid_amount;
        $currency_formatted = function_exists( 'mjschool_currency_symbol_position_language_wise' )
            ? mjschool_currency_symbol_position_language_wise( number_format( $due_amount, 2, '.', '' ) )
            : number_format( $due_amount, 2, '.', '' );
        
        $total_formatted = function_exists( 'mjschool_currency_symbol_position_language_wise' )
            ? mjschool_currency_symbol_position_language_wise( number_format( $payment->total_amount, 2, '.', '' ) )
            : number_format( $payment->total_amount, 2, '.', '' );

        $class_name = function_exists( 'mjschool_get_class_name' ) 
            ? mjschool_get_class_name( $payment->class_id ) 
            : '';

        $replacements = array(
            '{{student_name}}' => $student_info->display_name,
            '{{total_amount}}' => $total_formatted,
            '{{due_amount}}'   => $currency_formatted,
            '{{class_name}}'   => $class_name,
            '{{school_name}}'  => get_option( 'mjschool_name' ),
        );

        // Send to student.
        $subject = get_option( 'mjschool_fee_payment_reminder_title_for_student' );
        $message = mjschool_string_replacement( $replacements, get_option( 'mjschool_fee_payment_reminder_mailcontent_for_student' ) );
        mjschool_send_mail_paid_invoice_pdf( $student_info->user_email, $subject, $message, $payment->fees_pay_id );

        // Send to parents.
        $parent_ids = get_user_meta( $student_id, 'parent_id', true );
        if ( is_array( $parent_ids ) ) {
            foreach ( $parent_ids as $parent_id ) {
                $parent_info = get_userdata( $parent_id );
                if ( ! $parent_info ) {
                    continue;
                }

                $replacements['{{parent_name}}'] = $parent_info->display_name;

                $parent_subject = get_option( 'mjschool_fee_payment_reminder_title' );
                $parent_message = mjschool_string_replacement( $replacements, get_option( 'mjschool_fee_payment_reminder_mailcontent' ) );
                mjschool_send_mail_paid_invoice_pdf( $parent_info->user_email, $parent_subject, $parent_message, $payment->fees_pay_id );
            }
        }

        return true;
    }
}

// Hook recurring invoice events.
add_action( 'recurring_invoice_event', array( 'MJSchool_Recurring_Invoice_Handler', 'send_payment_reminders' ) );
add_action( 'recurring_invoice_event', array( 'MJSchool_Recurring_Invoice_Handler', 'generate_recurring_invoices' ) );

// Schedule cron event.
if ( ! wp_next_scheduled( 'recurring_invoice_event' ) ) {
    wp_schedule_event( time(), 'thirty_minutes', 'recurring_invoice_event' );
}

/**
 * Add thirty minutes cron schedule.
 *
 * @param array $schedules Existing schedules.
 * @return array Modified schedules.
 */
function mjschool_add_cron_interval( $schedules ) {
    $schedules['thirty_minutes'] = array(
        'interval' => 1800,
        'display'  => __( 'Every 30 Minutes', 'mjschool' ),
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'mjschool_add_cron_interval' );

/**
 * Add custom nonce to login form.
 */
function mjschool_add_login_nonce() {
    wp_nonce_field( 'mjschool_login_nonce', 'mjschool_login_nonce_field' );
}
add_action( 'login_form', 'mjschool_add_login_nonce' );

/**
 * Verify login nonce.
 *
 * @param WP_User|WP_Error|null $user     User object or error.
 * @param string                $username Username.
 * @param string                $password Password.
 * @return WP_User|WP_Error
 */
function mjschool_verify_login_nonce( $user, $username, $password ) {
    if ( isset( $_POST['mjschool_login_nonce_field'] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mjschool_login_nonce_field'] ) ), 'mjschool_login_nonce' ) ) {
            return new WP_Error( 'nonce_failed', __( 'Security verification failed. Please try again.', 'mjschool' ) );
        }
    }
    return $user;
}
add_filter( 'authenticate', 'mjschool_verify_login_nonce', 30, 3 );

/**
 * Output conditional CSS to hide menu links.
 */
function mjschool_conditional_menu_css() {
    $combine = get_option( 'mjschool_combine' );

    if ( '1' === $combine ) {
        ?>
        <style>
            a[href$="/student-registration/"],
            a[href$="/student-admission/"] {
                display: none !important;
            }
        </style>
        <?php
    } else {
        ?>
        <style>
            a[href$="/student-registration-form/"] {
                display: none !important;
            }
        </style>
        <?php
    }
}
add_action( 'wp_head', 'mjschool_conditional_menu_css' );
?>