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
	 *
	 * This function sets default access rights for all user roles (student, teacher, parent, support staff, etc.)
	 * and defines default plugin options such as school name, logo, contact info, and system settings.
	 *
	 * @since 1.0.0
	 */
	function mjschool_option() {
		$role_access_right_student            = array();
		// Access rights for 'student' role.
		$role_access_right_student['student'] = array(
			'teacher'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-teacher.png' ),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset( $_REQUEST['teacher_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_own_data'])) : 1,
				'add'        => isset( $_REQUEST['teacher_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_add'])) : 0,
				'edit'       => isset( $_REQUEST['teacher_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_edit'])) : 0,
				'view'       => isset( $_REQUEST['teacher_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_view'])) : 1,
				'delete'     => isset( $_REQUEST['teacher_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_delete'])) : 0,
			),
			'student'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-student-icon.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-student.png' ),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset( $_REQUEST['student_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_own_data'])) : 1,
				'add'        => isset( $_REQUEST['student_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_add'])) : 0,
				'edit'       => isset( $_REQUEST['student_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_edit'])) : 0,
				'view'       => isset( $_REQUEST['student_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_view'])) : 1,
				'delete'     => isset( $_REQUEST['student_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_delete'])) : 0,
			),
			'parent'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-parents.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-parents.png' ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_own_data'])) : 1,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_add'])) : 0,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_edit'])) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_view'])) : 1,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_delete'])) : 0,
			),
			'supportstaff'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-support-staff.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-support-staff.png' ),
				'menu_title' => 'Supportstaff',
				'page_link'  => 'supportstaff',
				'own_data'   => isset( $_REQUEST['supportstaff_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_own_data'])) : 0,
				'add'        => isset( $_REQUEST['supportstaff_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_add'])) : 0,
				'edit'       => isset( $_REQUEST['supportstaff_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_edit'])) : 0,
				'view'       => isset( $_REQUEST['supportstaff_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_view'])) : 1,
				'delete'     => isset( $_REQUEST['supportstaff_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_delete'])) : 0,
			),
			'subject'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-subject.png' ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_own_data'])) : 1,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_add'])) : 0,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_edit'])) : 0,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_view'])) : 1,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_delete'])) : 0,
			),
			'schedule'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-class-route.png' ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_own_data'])) : 1,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_add'])) : 0,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_edit'])) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_view'])) : 1,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_delete'])) : 0,
			),
			'virtual_classroom' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-virtual-class.png' ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_own_data'])) : 1,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_add'])) : 0,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_edit'])) : 0,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_view'])) : 1,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_delete'])) : 0,
			),
			'attendance'        => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-attandance.png' ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_own_data'])) : 1,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_add'])) : 0,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_edit'])) : 0,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_view'])) : 1,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_delete'])) : 0,
			),
			'notification'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_own_data'])) : 1,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_add'])) : 0,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_edit'])) : 0,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_view'])) : 1,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_delete'])) : 0,
			),
			'exam'              => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/exam.png' ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_own_data'])) : 1,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_add'])) : 0,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_edit'])) : 0,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_view'])) : 1,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_delete'])) : 0,
			),
			'class_room'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/exam.png' ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				"own_data"	 => isset($_REQUEST['class_room_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_own_data'])) : 1,
				"add"		 => isset($_REQUEST['class_room_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_add'])) : 0,
				"edit"		 => isset($_REQUEST['class_room_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_edit'])) : 0,
				"view"		 => isset($_REQUEST['class_room_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_view'])) : 1,
				"delete"	 => isset($_REQUEST['class_room_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_delete'])) : 0
			),
			'grade'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-grade.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-grade.png' ),
				'menu_title' => 'Grade',
				'page_link'  => 'grade',
				'own_data'   => isset( $_REQUEST['grade_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['grade_own_data'])) : 0,
				'add'        => isset( $_REQUEST['grade_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['grade_add'])) : 0,
				'edit'       => isset( $_REQUEST['grade_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['grade_edit'])) : 0,
				'view'       => isset( $_REQUEST['grade_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['grade_view'])) : 1,
				'delete'     => isset( $_REQUEST['grade_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['grade_delete'])) : 0,
			),
			'hostel'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-hostel.png' ),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset( $_REQUEST['hostel_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_own_data'])) : 1,
				'add'        => isset( $_REQUEST['hostel_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_add'])) : 0,
				'edit'       => isset( $_REQUEST['hostel_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_edit'])) : 0,
				'view'       => isset( $_REQUEST['hostel_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_view'])) : 1,
				'delete'     => isset( $_REQUEST['hostel_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_delete'])) : 0,
			),
			'document'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-hostel.png' ),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset( $_REQUEST['document_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_own_data'])) : 1,
				'add'        => isset( $_REQUEST['document_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_add'])) : 0,
				'edit'       => isset( $_REQUEST['document_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_edit'])) : 0,
				'view'       => isset( $_REQUEST['document_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_view'])) : 1,
				'delete'     => isset( $_REQUEST['document_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_delete'])) : 0,
			),
			'leave'             => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_own_data'])) : 1,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_add'])) : 1,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_edit'])) : 0,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_view'])) : 1,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_delete'])) : 0,
			),
			'homework'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-homework.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-homework.png' ),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset( $_REQUEST['homework_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_own_data'])) : 1,
				'add'        => isset( $_REQUEST['homework_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_add'])) : 0,
				'edit'       => isset( $_REQUEST['homework_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_edit'])) : 0,
				'view'       => isset( $_REQUEST['homework_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_view'])) : 1,
				'delete'     => isset( $_REQUEST['homework_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_delete'])) : 0,
			),
			'manage_marks'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-mark-manage.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-mark-manage.png' ),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage-marks',
				'own_data'   => isset( $_REQUEST['manage_marks_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_own_data'])) : 0,
				'add'        => isset( $_REQUEST['manage_marks_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_add'])) : 0,
				'edit'       => isset( $_REQUEST['manage_marks_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_edit'])) : 0,
				'view'       => isset( $_REQUEST['manage_marks_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_view'])) : 0,
				'delete'     => isset( $_REQUEST['manage_marks_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_delete'])) : 0,
			),
			'feepayment'        => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-fee-payment.png' ),
				'menu_title' => 'Fees Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset( $_REQUEST['feepayment_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_own_data'])) : 1,
				'add'        => isset( $_REQUEST['feepayment_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_add'])) : 0,
				'edit'       => isset( $_REQUEST['feepayment_edit'] ) ? sanitize_text_field(wp_unslash(_REQUEST['feepayment_edit'])) : 0,
				'view'       => isset( $_REQUEST['feepayment_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_view'])) : 1,
				'delete'     => isset( $_REQUEST['feepayment_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_delete'])) : 0,
			),
			'payment'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-payment.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-payment.png' ),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset( $_REQUEST['payment_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_own_data'])) : 1,
				'add'        => isset( $_REQUEST['payment_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_add'])) : 0,
				'edit'       => isset( $_REQUEST['payment_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_edit'])) : 0,
				'view'       => isset( $_REQUEST['payment_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_view'])) : 1,
				'delete'     => isset( $_REQUEST['payment_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_delete'])) : 0,
			),
			'transport'         => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-transport.png' ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_own_data'])) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_add'])) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_edit'])) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_view'])) : 1,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_delete'])) : 0,
			),
			'notice'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-notice.png' ),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset( $_REQUEST['notice_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_own_data'])) : 1,
				'add'        => isset( $_REQUEST['notice_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_add'])) : 0,
				'edit'       => isset( $_REQUEST['notice_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_edit'])) : 0,
				'view'       => isset( $_REQUEST['notice_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_view'])) : 1,
				'delete'     => isset( $_REQUEST['notice_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_delete'])) : 0,
			),
			'message'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-message.png' ),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset( $_REQUEST['message_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_own_data'])) : 1,
				'add'        => isset( $_REQUEST['message_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_add'])) : 1,
				'edit'       => isset( $_REQUEST['message_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_edit'])) : 0,
				'view'       => isset( $_REQUEST['message_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_view'])) : 1,
				'delete'     => isset( $_REQUEST['message_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_delete'])) : 1,
			),
			'holiday'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-holiday.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-holiday.png' ),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset( $_REQUEST['holiday_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_own_data'])) : 0,
				'add'        => isset( $_REQUEST['holiday_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_add'])) : 0,
				'edit'       => isset( $_REQUEST['holiday_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_edit'])) : 0,
				'view'       => isset( $_REQUEST['holiday_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_view'])) : 1,
				'delete'     => isset( $_REQUEST['holiday_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_delete'])) : 0,
			),
			'library'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-library.png' ),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset( $_REQUEST['library_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_own_data'])) : 1,
				'add'        => isset( $_REQUEST['library_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_add'])) : 0,
				'edit'       => isset( $_REQUEST['library_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_edit'])) : 0,
				'view'       => isset( $_REQUEST['library_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_view'])) : 1,
				'delete'     => isset( $_REQUEST['library_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_delete'])) : 0,
			),
			'certificate'       => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-library.png' ),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset( $_REQUEST['certificate_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_own_data'])) : 1,
				'add'        => isset( $_REQUEST['certificate_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_add'])) : 0,
				'edit'       => isset( $_REQUEST['certificate_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_edit'])) : 0,
				'view'       => isset( $_REQUEST['certificate_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_view'])) : 1,
				'delete'     => isset( $_REQUEST['certificate_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_delete'])) : 0,
			),
			'account'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-account.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-account.png' ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_own_data'])) : 1,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_add'])) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_edit'])) : 1,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_view'])) : 1,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_delete'])) : 0,
			),
			'report'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/app-icon/mjschool-report.png' ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_own_data'])) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_add'])) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_edit'])) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_view'])) : 0,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_delete'])) : 0,
			),
			'event'             => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset( $_REQUEST['event_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_own_data'])) : 0,
				'add'        => isset( $_REQUEST['event_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_add'])) : 0,
				'edit'       => isset( $_REQUEST['event_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_edit'])) : 0,
				'view'       => isset( $_REQUEST['event_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_view'])) : 1,
				'delete'     => isset( $_REQUEST['event_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_delete'])) : 0,
			),
		);
		// Access rights for 'teacher' role.
		$role_access_right_teacher            = array();
		$role_access_right_teacher['teacher'] = array(
			'admission'         => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-admission.png' ),
				'menu_title' => 'Admission',
				'page_link'  => 'admission',
				'own_data'   => isset( $_REQUEST['admission_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['admission_own_data'])) : 0,
				'add'        => isset( $_REQUEST['admission_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['admission_add'])) : 1,
				'edit'       => isset( $_REQUEST['admission_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['admission_edit'])) : 1,
				'view'       => isset( $_REQUEST['admission_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['admission_view'])) : 1,
				'delete'     => isset( $_REQUEST['admission_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['admission_delete'])) : 0,
			),
			'teacher'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset( $_REQUEST['teacher_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_own_data'])) : 1,
				'add'        => isset( $_REQUEST['teacher_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_add'])) : 0,
				'edit'       => isset( $_REQUEST['teacher_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_edit'])) : 0,
				'view'       => isset( $_REQUEST['teacher_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_view'])) : 1,
				'delete'     => isset( $_REQUEST['teacher_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_delete'])) : 0,
			),
			'student'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-student-icon.png' ),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset( $_REQUEST['student_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_own_data'])) : 1,
				'add'        => isset( $_REQUEST['student_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_add'])) : 1,
				'edit'       => isset( $_REQUEST['student_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_edit'])) : 0,
				'view'       => isset( $_REQUEST['student_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_view'])) : 1,
				'delete'     => isset( $_REQUEST['student_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['student_delete'])) : 0,
			),
			'parent'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-parents.png' ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_own_data'])) : 1,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_add'])) : 1,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_edit'])) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_view'])) : 1,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['parent_delete'])) : 0,
			),
			'subject'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_own_data'])) : 1,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_add'])) : 1,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_edit'])) : 1,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_view'])) : 1,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['subject_delete'])) : 0,
			),
			'class'             => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-class.png' ),
				'menu_title' => 'Class',
				'page_link'  => 'class',
				'own_data'   => isset( $_REQUEST['class_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_own_data'])) : 1,
				'add'        => isset( $_REQUEST['class_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_add'])) : 0,
				'edit'       => isset( $_REQUEST['class_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_edit'])) : 0,
				'view'       => isset( $_REQUEST['class_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_view'])) : 1,
				'delete'     => isset( $_REQUEST['class_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['class_delete'])) : 0,
			),
			'virtual_classroom' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_own_data'])) : 0,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_add'])) : 1,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_edit'])) : 1,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_view'])) : 1,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_delete'])) : 0,
			),
			'schedule'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_own_data'])) : 1,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_add'])) : 1,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_edit'])) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_view'])) : 1,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_delete'])) : 0,
			),
			'attendance'        => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_own_data'])) : 1,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_add'])) : 1,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_edit'])) : 1,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_view'])) : 1,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_delete'])) : 0,
			),
			'notification'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_own_data'])) : 1,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_add'])) : 1,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_edit'])) : 1,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_view'])) : 1,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notification_delete'])) : 1,
			),
			'exam'              => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_own_data'])) : 1,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_add'])) : 1,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_edit'])) : 1,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_view'])) : 1,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_delete'])) : 0,
			),
			'class_room'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				"own_data" 	 => isset($_REQUEST['class_room_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_own_data'])) : 1,
				"add" 		 => isset($_REQUEST['class_room_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_add'])) : 1,
				"edit"		 => isset($_REQUEST['class_room_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_edit'])) : 1,
				"view"		 => isset($_REQUEST['class_room_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_view'])) : 1,
				"delete"	 => isset($_REQUEST['class_room_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_delete'])) : 0
			),
			'exam_hall'         => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam_hall.png' ),
				'menu_title' => 'Exam Hall',
				'page_link'  => 'exam_hall',
				'own_data'   => isset( $_REQUEST['exam_hall_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_own_data'])) : 1,
				'add'        => isset( $_REQUEST['exam_hall_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_add'])) : 1,
				'edit'       => isset( $_REQUEST['exam_hall_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_edit'])) : 1,
				'view'       => isset( $_REQUEST['exam_hall_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_view'])) : 1,
				'delete'     => isset( $_REQUEST['exam_hall_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_delete'])) : 0,
			),
			'hostel'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset( $_REQUEST['hostel_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_own_data'])) : 0,
				'add'        => isset( $_REQUEST['hostel_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_add'])) : 0,
				'edit'       => isset( $_REQUEST['hostel_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_edit'])) : 0,
				'view'       => isset( $_REQUEST['hostel_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_view'])) : 1,
				'delete'     => isset( $_REQUEST['hostel_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_delete'])) : 0,
			),
			'homework'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-homework.png' ),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset( $_REQUEST['homework_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_own_data'])) : 1,
				'add'        => isset( $_REQUEST['homework_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_add'])) : 1,
				'edit'       => isset( $_REQUEST['homework_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_edit'])) : 1,
				'view'       => isset( $_REQUEST['homework_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_view'])) : 1,
				'delete'     => isset( $_REQUEST['homework_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['homework_delete'])) : 0,
			),
			'manage_marks'      => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-mark-manage.png' ),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage-marks',
				'own_data'   => isset( $_REQUEST['manage_marks_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_own_data'])) : 1,
				'add'        => isset( $_REQUEST['manage_marks_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_add'])) : 1,
				'edit'       => isset( $_REQUEST['manage_marks_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_edit'])) : 1,
				'view'       => isset( $_REQUEST['manage_marks_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_view'])) : 1,
				'delete'     => isset( $_REQUEST['manage_marks_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_delete'])) : 0,
			),
			'feepayment'        => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ),
				'menu_title' => 'Fee Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset( $_REQUEST['feepayment_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_own_data'])) : 1,
				'add'        => isset( $_REQUEST['feepayment_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_add'])) : 1,
				'edit'       => isset( $_REQUEST['feepayment_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_edit'])) : 1,
				'view'       => isset( $_REQUEST['feepayment_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_view'])) : 1,
				'delete'     => isset( $_REQUEST['feepayment_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_delete'])) : 0,
			),
			'payment'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-payment.png' ),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset( $_REQUEST['payment_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_own_data'])) : 0,
				'add'        => isset( $_REQUEST['payment_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_add'])) : 0,
				'edit'       => isset( $_REQUEST['payment_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_edit'])) : 0,
				'view'       => isset( $_REQUEST['payment_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_view'])) : 0,
				'delete'     => isset( $_REQUEST['payment_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['payment_delete'])) : 0,
			),
			'transport'         => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_own_data'])) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_add'])) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_edit'])) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_view'])) : 1,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['transport_delete'])) : 0,
			),
			'document'          => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset( $_REQUEST['document_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_own_data'])) : 1,
				'add'        => isset( $_REQUEST['document_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_add'])) : 1,
				'edit'       => isset( $_REQUEST['document_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_edit'])) : 1,
				'view'       => isset( $_REQUEST['document_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_view'])) : 1,
				'delete'     => isset( $_REQUEST['document_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['document_delete'])) : 1,
			),
			'leave'             => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_own_data'])) : 0,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_add'])) : 1,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_edit'])) : 1,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_view'])) : 1,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['leave_delete'])) : 1,
			),
			'notice'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset( $_REQUEST['notice_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_own_data'])) : 1,
				'add'        => isset( $_REQUEST['notice_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_add'])) : 1,
				'edit'       => isset( $_REQUEST['notice_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_edit'])) : 1,
				'view'       => isset( $_REQUEST['notice_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_view'])) : 1,
				'delete'     => isset( $_REQUEST['notice_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['notice_delete'])) : 0,
			),
			'message'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset( $_REQUEST['message_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_own_data'])) : 1,
				'add'        => isset( $_REQUEST['message_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_add'])) : 1,
				'edit'       => isset( $_REQUEST['message_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_edit'])) : 0,
				'view'       => isset( $_REQUEST['message_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_view'])) : 1,
				'delete'     => isset( $_REQUEST['message_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message_delete'])) : 0,
			),
			// Migration //
			'migration'         => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ),
				'menu_title' => 'Migration',
				'page_link'  => 'migration',
				'own_data'   => isset( $_REQUEST['migration_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['migration_own_data'])) : 0,
				'add'        => isset( $_REQUEST['migration_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['migration_add'])) : 1,
				'edit'       => isset( $_REQUEST['migration_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['migration_edit'])) : 0,
				'view'       => isset( $_REQUEST['migration_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['migration_view'])) : 1,
				'delete'     => isset( $_REQUEST['migration_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['migration_delete'])) : 0,
			),
			'holiday'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-holiday.png' ),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset( $_REQUEST['holiday_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_own_data'])) : 0,
				'add'        => isset( $_REQUEST['holiday_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_add'])) : 1,
				'edit'       => isset( $_REQUEST['holiday_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_edit'])) : 1,
				'view'       => isset( $_REQUEST['holiday_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_view'])) : 1,
				'delete'     => isset( $_REQUEST['holiday_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_delete'])) : 0,
			),
			'library'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset( $_REQUEST['library_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_own_data'])) : 1,
				'add'        => isset( $_REQUEST['library_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_add'])) : 1,
				'edit'       => isset( $_REQUEST['library_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_edit'])) : 0,
				'view'       => isset( $_REQUEST['library_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_view'])) : 1,
				'delete'     => isset( $_REQUEST['library_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['library_delete'])) : 0,
			),
			'certificate'       => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset( $_REQUEST['certificate_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_own_data'])) : 1,
				'add'        => isset( $_REQUEST['certificate_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_add'])) : 0,
				'edit'       => isset( $_REQUEST['certificate_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_edit'])) : 0,
				'view'       => isset( $_REQUEST['certificate_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_view'])) : 0,
				'delete'     => isset( $_REQUEST['certificate_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_delete'])) : 0,
			),
			'account'           => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-account.png' ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_own_data'])) : 1,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_add'])) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_edit'])) : 1,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_view'])) : 1,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['account_delete'])) : 0,
			),
			'report'            => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_own_data'])) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_add'])) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_edit'])) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_view'])) : 1,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['report_delete'])) : 0,
			),
			'event'             => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset( $_REQUEST['event_own_data'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_own_data'])) : 0,
				'add'        => isset( $_REQUEST['event_add'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_add'])) : 1,
				'edit'       => isset( $_REQUEST['event_edit'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_edit'])) : 1,
				'view'       => isset( $_REQUEST['event_view'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_view'])) : 1,
				'delete'     => isset( $_REQUEST['event_delete'] ) ? sanitize_text_field(wp_unslash($_REQUEST['event_delete'])) : 1,
			),
		);
		// Access rights for 'parent' role.
		$role_access_right_parent            = array();
		$role_access_right_parent['parent'] = array(
			'teacher' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-teacher.png' ),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset( $_REQUEST['teacher_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['teacher_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['teacher_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['teacher_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['teacher_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_delete'] ) ) : 0,
			),
			'student' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-student-icon.png' ),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset( $_REQUEST['student_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['student_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['student_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['student_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['student_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_delete'] ) ) : 0,
			),
			'parent' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-parents.png' ),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset( $_REQUEST['parent_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['parent_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['parent_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['parent_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['parent_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['parent_delete'] ) ) : 0,
			),
			'subject' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-subject.png' ),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset( $_REQUEST['subject_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['subject_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['subject_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['subject_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['subject_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['subject_delete'] ) ) : 0,
			),
			'schedule' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-class-route.png' ),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset( $_REQUEST['schedule_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['schedule_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['schedule_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['schedule_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['schedule_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['schedule_delete'] ) ) : 0,
			),
			'virtual_classroom' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-virtual-classroom.png' ),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset( $_REQUEST['virtual_classroom_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['virtual_classroom_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['virtual_classroom_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['virtual_classroom_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['virtual_classroom_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['virtual_classroom_delete'] ) ) : 0,
			),
			'attendance' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset( $_REQUEST['attendance_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['attendance_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['attendance_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['attendance_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['attendance_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['attendance_delete'] ) ) : 0,
			),
			'exam' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset( $_REQUEST['exam_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['exam_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['exam_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['exam_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['exam_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['exam_delete'] ) ) : 0,
			),
			'class_room' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-exam.png' ),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				'own_data'   => isset( $_REQUEST['class_room_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['class_room_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['class_room_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['class_room_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['class_room_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_room_delete'] ) ) : 0,
			),
			'hostel' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset( $_REQUEST['hostel_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['hostel_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['hostel_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['hostel_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['hostel_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_delete'] ) ) : 0,
			),
			'notification' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-attandance.png' ),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset( $_REQUEST['notification_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['notification_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notification_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notification_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['notification_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notification_delete'] ) ) : 0,
			),
			'homework' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-homework.png' ),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset( $_REQUEST['homework_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['homework_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['homework_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['homework_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['homework_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['homework_delete'] ) ) : 0,
			),
			'manage_marks' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-mark-manage.png' ),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage-marks',
				'own_data'   => isset( $_REQUEST['manage_marks_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['manage_marks_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['manage_marks_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['manage_marks_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['manage_marks_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['manage_marks_delete'] ) ) : 0,
			),
			'feepayment' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-fee.png' ),
				'menu_title' => 'Fee Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset( $_REQUEST['feepayment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['feepayment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['feepayment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['feepayment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['feepayment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['feepayment_delete'] ) ) : 0,
			),
			'document' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-hostel.png' ),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset( $_REQUEST['document_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['document_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['document_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['document_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['document_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['document_delete'] ) ) : 0,
			),
			'leave' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'app_icone'  => plugins_url( 'mjschool/assets/images/icons/mjschool-notification_new.png' ),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset( $_REQUEST['leave_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['leave_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['leave_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['leave_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['leave_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['leave_delete'] ) ) : 0,
			),
			'payment' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-payment.png' ),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset( $_REQUEST['payment_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['payment_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['payment_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['payment_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['payment_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_delete'] ) ) : 0,
			),
			'transport' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-transport.png' ),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset( $_REQUEST['transport_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['transport_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['transport_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['transport_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['transport_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['transport_delete'] ) ) : 0,
			),
			'notice' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-notice.png' ),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset( $_REQUEST['notice_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['notice_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['notice_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['notice_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['notice_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_delete'] ) ) : 0,
			),
			'message' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-message.png' ),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset( $_REQUEST['message_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['message_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_add'] ) ) : 1,
				'edit'       => isset( $_REQUEST['message_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['message_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['message_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message_delete'] ) ) : 1,
			),
			'holiday' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-holiday.png' ),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset( $_REQUEST['holiday_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['holiday_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['holiday_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['holiday_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['holiday_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['holiday_delete'] ) ) : 0,
			),
			'library' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset( $_REQUEST['library_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['library_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['library_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['library_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['library_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['library_delete'] ) ) : 0,
			),
			'certificate' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-library.png' ),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset( $_REQUEST['certificate_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['certificate_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['certificate_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['certificate_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['certificate_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['certificate_delete'] ) ) : 0,
			),
			'account' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-account.png' ),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset( $_REQUEST['account_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_own_data'] ) ) : 1,
				'add'        => isset( $_REQUEST['account_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['account_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_edit'] ) ) : 1,
				'view'       => isset( $_REQUEST['account_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['account_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['account_delete'] ) ) : 0,
			),
			'report' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset( $_REQUEST['report_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['report_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_add'] ) ) : 0,
				'edit'       => isset( $_REQUEST['report_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_edit'] ) ) : 0,
				'view'       => isset( $_REQUEST['report_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_view'] ) ) : 0,
				'delete'     => isset( $_REQUEST['report_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['report_delete'] ) ) : 0,
			),
			'event' => array(
				'menu_icone' => plugins_url( 'mjschool/assets/images/icons/mjschool-report.png' ),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset( $_REQUEST['event_own_data'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_own_data'] ) ) : 0,
				'add'        => isset( $_REQUEST['event_add'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_add'] ) ) : 1,
				'edit'       => isset( $_REQUEST['event_edit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_edit'] ) ) : 1,
				'view'       => isset( $_REQUEST['event_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_view'] ) ) : 1,
				'delete'     => isset( $_REQUEST['event_delete'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_delete'] ) ) : 1,
			),
		);
		// Access rights for 'supportstaff' role.
		$role_access_right_support_staff                 = array();
		$role_access_right_support_staff['supportstaff'] = array(
			// Admission
			'admission' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-admission.png'),
				'menu_title' => 'Admission',
				'page_link'  => 'admission',
				'own_data'   => isset($_REQUEST['admission_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_own_data'])) : 0,
				'add'        => isset($_REQUEST['admission_add']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_add'])) : 1,
				'edit'       => isset($_REQUEST['admission_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_edit'])) : 1,
				'view'       => isset($_REQUEST['admission_view']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_view'])) : 1,
				'delete'     => isset($_REQUEST['admission_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_delete'])) : 0,
			),
			// Student
			'student' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-student-icon.png'),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset($_REQUEST['student_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['student_own_data'])) : 0,
				'add'        => isset($_REQUEST['student_add']) ? sanitize_text_field(wp_unslash($_REQUEST['student_add'])) : 1,
				'edit'       => isset($_REQUEST['student_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['student_edit'])) : 1,
				'view'       => isset($_REQUEST['student_view']) ? sanitize_text_field(wp_unslash($_REQUEST['student_view'])) : 1,
				'delete'     => isset($_REQUEST['student_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['student_delete'])) : 1,
			),
			// Teacher
			'teacher' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset($_REQUEST['teacher_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_own_data'])) : 0,
				'add'        => isset($_REQUEST['teacher_add']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_add'])) : 1,
				'edit'       => isset($_REQUEST['teacher_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_edit'])) : 1,
				'view'       => isset($_REQUEST['teacher_view']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_view'])) : 1,
				'delete'     => isset($_REQUEST['teacher_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_delete'])) : 1,
			),
			// Support Staff
			'supportstaff' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-support-staff.png'),
				'menu_title' => 'Supportstaff',
				'page_link'  => 'supportstaff',
				'own_data'   => isset($_REQUEST['supportstaff_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_own_data'])) : 1,
				'add'        => isset($_REQUEST['supportstaff_add']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_add'])) : 0,
				'edit'       => isset($_REQUEST['supportstaff_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_edit'])) : 0,
				'view'       => isset($_REQUEST['supportstaff_view']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_view'])) : 1,
				'delete'     => isset($_REQUEST['supportstaff_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_delete'])) : 0,
			),
			// Parent
			'parent' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-parents.png'),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset($_REQUEST['parent_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_own_data'])) : 0,
				'add'        => isset($_REQUEST['parent_add']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_add'])) : 1,
				'edit'       => isset($_REQUEST['parent_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_edit'])) : 1,
				'view'       => isset($_REQUEST['parent_view']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_view'])) : 1,
				'delete'     => isset($_REQUEST['parent_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_delete'])) : 1,
			),
			// Subject
			'subject' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-subject.png'),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset($_REQUEST['subject_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_own_data'])) : 0,
				'add'        => isset($_REQUEST['subject_add']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_add'])) : 1,
				'edit'       => isset($_REQUEST['subject_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_edit'])) : 1,
				'view'       => isset($_REQUEST['subject_view']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_view'])) : 1,
				'delete'     => isset($_REQUEST['subject_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_delete'])) : 1,
			),
			// Class
			'class' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-class.png'),
				'menu_title' => 'Class',
				'page_link'  => 'class',
				'own_data'   => isset($_REQUEST['class_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_own_data'])) : 0,
				'add'        => isset($_REQUEST['class_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_add'])) : 1,
				'edit'       => isset($_REQUEST['class_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_edit'])) : 1,
				'view'       => isset($_REQUEST['class_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_view'])) : 1,
				'delete'     => isset($_REQUEST['class_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_delete'])) : 1,
			),
			// Class Routine
			'schedule' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-class-route.png'),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset($_REQUEST['schedule_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_own_data'])) : 0,
				'add'        => isset($_REQUEST['schedule_add']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_add'])) : 1,
				'edit'       => isset($_REQUEST['schedule_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_edit'])) : 1,
				'view'       => isset($_REQUEST['schedule_view']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_view'])) : 1,
				'delete'     => isset($_REQUEST['schedule_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_delete'])) : 1,
			),
			// Virtual Classroom
			'virtual_classroom' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-virtual-classroom.png'),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset($_REQUEST['virtual_classroom_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_own_data'])) : 0,
				'add'        => isset($_REQUEST['virtual_classroom_add']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_add'])) : 1,
				'edit'       => isset($_REQUEST['virtual_classroom_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_edit'])) : 1,
				'view'       => isset($_REQUEST['virtual_classroom_view']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_view'])) : 1,
				'delete'     => isset($_REQUEST['virtual_classroom_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_delete'])) : 1,
			),
			// Attendance
			'attendance' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-attandance.png'),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset($_REQUEST['attendance_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_own_data'])) : 0,
				'add'        => isset($_REQUEST['attendance_add']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_add'])) : 1,
				'edit'       => isset($_REQUEST['attendance_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_edit'])) : 1,
				'view'       => isset($_REQUEST['attendance_view']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_view'])) : 1,
				'delete'     => isset($_REQUEST['attendance_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_delete'])) : 1,
			),
			// Exam
			'exam' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-exam.png'),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset($_REQUEST['exam_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_own_data'])) : 0,
				'add'        => isset($_REQUEST['exam_add']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_add'])) : 1,
				'edit'       => isset($_REQUEST['exam_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_edit'])) : 1,
				'view'       => isset($_REQUEST['exam_view']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_view'])) : 1,
				'delete'     => isset($_REQUEST['exam_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_delete'])) : 1,
			),
			// Class Room
			'class_room' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-exam.png'),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				'own_data'   => isset($_REQUEST['class_room_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_own_data'])) : 1,
				'add'        => isset($_REQUEST['class_room_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_add'])) : 1,
				'edit'       => isset($_REQUEST['class_room_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_edit'])) : 1,
				'view'       => isset($_REQUEST['class_room_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_view'])) : 1,
				'delete'     => isset($_REQUEST['class_room_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_delete'])) : 1,
			),
			// Notification
			'notification' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-attandance.png'),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset($_REQUEST['notification_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_own_data'])) : 1,
				'add'        => isset($_REQUEST['notification_add']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_add'])) : 1,
				'edit'       => isset($_REQUEST['notification_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_edit'])) : 1,
				'view'       => isset($_REQUEST['notification_view']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_view'])) : 1,
				'delete'     => isset($_REQUEST['notification_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_delete'])) : 1,
			),
			// Exam Hall
			'exam_hall' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-exam_hall.png'),
				'menu_title' => 'Exam Hall',
				'page_link'  => 'exam_hall',
				'own_data'   => isset($_REQUEST['exam_hall_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_own_data'])) : 0,
				'add'        => isset($_REQUEST['exam_hall_add']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_add'])) : 1,
				'edit'       => isset($_REQUEST['exam_hall_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_edit'])) : 1,
				'view'       => isset($_REQUEST['exam_hall_view']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_view'])) : 1,
				'delete'     => isset($_REQUEST['exam_hall_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_delete'])) : 1,
			),
			// Grade
			'grade' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-grade.png'),
				'menu_title' => 'Grade',
				'page_link'  => 'grade',
				'own_data'   => isset($_REQUEST['grade_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_own_data'])) : 0,
				'add'        => isset($_REQUEST['grade_add']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_add'])) : 1,
				'edit'       => isset($_REQUEST['grade_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_edit'])) : 1,
				'view'       => isset($_REQUEST['grade_view']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_view'])) : 1,
				'delete'     => isset($_REQUEST['grade_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_delete'])) : 1,
			),
			// Manage Marks
			'manage_marks' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-mark-manage.png'),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage-marks',
				'own_data'   => isset($_REQUEST['manage_marks_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_own_data'])) : 0,
				'add'        => isset($_REQUEST['manage_marks_add']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_add'])) : 1,
				'edit'       => isset($_REQUEST['manage_marks_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_edit'])) : 1,
				'view'       => isset($_REQUEST['manage_marks_view']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_view'])) : 1,
				'delete'     => isset($_REQUEST['manage_marks_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_delete'])) : 0,
			),
			// Homework
			'homework' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-homework.png'),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset($_REQUEST['homework_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_own_data'])) : 0,
				'add'        => isset($_REQUEST['homework_add']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_add'])) : 1,
				'edit'       => isset($_REQUEST['homework_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_edit'])) : 1,
				'view'       => isset($_REQUEST['homework_view']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_view'])) : 1,
				'delete'     => isset($_REQUEST['homework_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_delete'])) : 1,
			),
			// Hostel
			'hostel' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-hostel.png'),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset($_REQUEST['hostel_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_own_data'])) : 0,
				'add'        => isset($_REQUEST['hostel_add']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_add'])) : 1,
				'edit'       => isset($_REQUEST['hostel_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_edit'])) : 1,
				'view'       => isset($_REQUEST['hostel_view']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_view'])) : 1,
				'delete'     => isset($_REQUEST['hostel_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_delete'])) : 1,
			),
			// Document
			'document' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-hostel.png'),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset($_REQUEST['document_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['document_own_data'])) : 1,
				'add'        => isset($_REQUEST['document_add']) ? sanitize_text_field(wp_unslash($_REQUEST['document_add'])) : 1,
				'edit'       => isset($_REQUEST['document_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['document_edit'])) : 1,
				'view'       => isset($_REQUEST['document_view']) ? sanitize_text_field(wp_unslash($_REQUEST['document_view'])) : 1,
				'delete'     => isset($_REQUEST['document_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['document_delete'])) : 1,
			),
			// Leave
			'leave' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-notification_new.png'),
				'app_icone'  => plugins_url('mjschool/assets/images/icons/mjschool-notification_new.png'),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset($_REQUEST['leave_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_own_data'])) : 0,
				'add'        => isset($_REQUEST['leave_add']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_add'])) : 1,
				'edit'       => isset($_REQUEST['leave_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_edit'])) : 1,
				'view'       => isset($_REQUEST['leave_view']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_view'])) : 1,
				'delete'     => isset($_REQUEST['leave_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_delete'])) : 1,
			),
			// Transport
			'transport' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-transport.png'),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset($_REQUEST['transport_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_own_data'])) : 0,
				'add'        => isset($_REQUEST['transport_add']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_add'])) : 1,
				'edit'       => isset($_REQUEST['transport_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_edit'])) : 1,
				'view'       => isset($_REQUEST['transport_view']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_view'])) : 1,
				'delete'     => isset($_REQUEST['transport_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_delete'])) : 1,
			),
			// Notice Board
			'notice' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-notice.png'),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset($_REQUEST['notice_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_own_data'])) : 0,
				'add'        => isset($_REQUEST['notice_add']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_add'])) : 1,
				'edit'       => isset($_REQUEST['notice_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_edit'])) : 1,
				'view'       => isset($_REQUEST['notice_view']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_view'])) : 1,
				'delete'     => isset($_REQUEST['notice_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_delete'])) : 1,
			),
			// Message
			'message' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-message.png'),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset($_REQUEST['message_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['message_own_data'])) : 0,
				'add'        => isset($_REQUEST['message_add']) ? sanitize_text_field(wp_unslash($_REQUEST['message_add'])) : 1,
				'edit'       => isset($_REQUEST['message_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['message_edit'])) : 0,
				'view'       => isset($_REQUEST['message_view']) ? sanitize_text_field(wp_unslash($_REQUEST['message_view'])) : 1,
				'delete'     => isset($_REQUEST['message_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['message_delete'])) : 1,
			),
			// Migration
			'migration' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-message.png'),
				'menu_title' => 'Migration',
				'page_link'  => 'migration',
				'own_data'   => isset($_REQUEST['migration_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_own_data'])) : 0,
				'add'        => isset($_REQUEST['migration_add']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_add'])) : 1,
				'edit'       => isset($_REQUEST['migration_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_edit'])) : 0,
				'view'       => isset($_REQUEST['migration_view']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_view'])) : 1,
				'delete'     => isset($_REQUEST['migration_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_delete'])) : 0,
			),
			// Tax
			'tax' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-fee.png'),
				'menu_title' => 'Tax',
				'page_link'  => 'tax',
				'own_data'   => isset($_REQUEST['tax_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_own_data'])) : 0,
				'add'        => isset($_REQUEST['tax_add']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_add'])) : 0,
				'edit'       => isset($_REQUEST['tax_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_edit'])) : 0,
				'view'       => isset($_REQUEST['tax_view']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_view'])) : 1,
				'delete'     => isset($_REQUEST['tax_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_delete'])) : 0,
			),
			// Fee Payment
			'feepayment' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-fee.png'),
				'menu_title' => 'Fee Payment',
				'page_link'  => 'feepayment',
				'own_data'   => isset($_REQUEST['feepayment_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_own_data'])) : 0,
				'add'        => isset($_REQUEST['feepayment_add']) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_add'])) : 1,
				'edit'       => isset($_REQUEST['feepayment_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_edit'])) : 1,
				'view'       => isset($_REQUEST['feepayment_view']) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_view'])) : 1,
				'delete'     => isset($_REQUEST['feepayment_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['feepayment_delete'])) : 1,
			),
			// Payment
			'payment' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-payment.png'),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset($_REQUEST['payment_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_own_data'])) : 0,
				'add'        => isset($_REQUEST['payment_add']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_add'])) : 1,
				'edit'       => isset($_REQUEST['payment_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_edit'])) : 1,
				'view'       => isset($_REQUEST['payment_view']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_view'])) : 1,
				'delete'     => isset($_REQUEST['payment_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_delete'])) : 1,
			),
			// Holiday
			'holiday' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-holiday.png'),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset($_REQUEST['holiday_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_own_data'])) : 0,
				'add'        => isset($_REQUEST['holiday_add']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_add'])) : 1,
				'edit'       => isset($_REQUEST['holiday_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_edit'])) : 1,
				'view'       => isset($_REQUEST['holiday_view']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_view'])) : 1,
				'delete'     => isset($_REQUEST['holiday_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_delete'])) : 1,
			),
			// Library
			'library' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-library.png'),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset($_REQUEST['library_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['library_own_data'])) : 0,
				'add'        => isset($_REQUEST['library_add']) ? sanitize_text_field(wp_unslash($_REQUEST['library_add'])) : 1,
				'edit'       => isset($_REQUEST['library_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['library_edit'])) : 1,
				'view'       => isset($_REQUEST['library_view']) ? sanitize_text_field(wp_unslash($_REQUEST['library_view'])) : 1,
				'delete'     => isset($_REQUEST['library_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['library_delete'])) : 1,
			),
			// Certificate
			'certificate' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-library.png'),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset($_REQUEST['certificate_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_own_data'])) : 0,
				'add'        => isset($_REQUEST['certificate_add']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_add'])) : 0,
				'edit'       => isset($_REQUEST['certificate_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_edit'])) : 0,
				'view'       => isset($_REQUEST['certificate_view']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_view'])) : 0,
				'delete'     => isset($_REQUEST['certificate_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_delete'])) : 0,
			),
			// Custom Field
			'custom_field' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-custom.png'),
				'menu_title' => 'Custom Field',
				'page_link'  => 'custom_field',
				'own_data'   => isset($_REQUEST['custom_field_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_own_data'])) : 0,
				'add'        => isset($_REQUEST['custom_field_add']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_add'])) : 1,
				'edit'       => isset($_REQUEST['custom_field_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_edit'])) : 1,
				'view'       => isset($_REQUEST['custom_field_view']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_view'])) : 1,
				'delete'     => isset($_REQUEST['custom_field_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_delete'])) : 1,
			),
			// Report
			'report' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-report.png'),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset($_REQUEST['report_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['report_own_data'])) : 0,
				'add'        => isset($_REQUEST['report_add']) ? sanitize_text_field(wp_unslash($_REQUEST['report_add'])) : 0,
				'edit'       => isset($_REQUEST['report_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['report_edit'])) : 0,
				'view'       => isset($_REQUEST['report_view']) ? sanitize_text_field(wp_unslash($_REQUEST['report_view'])) : 1,
				'delete'     => isset($_REQUEST['report_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['report_delete'])) : 0,
			),
			// SMS Setting
			'mjschool_setting' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool_setting.png'),
				'menu_title' => 'SMS Setting',
				'page_link'  => 'mjschool_setting',
				'own_data'   => isset($_REQUEST['mjschool_setting_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_own_data'])) : 0,
				'add'        => isset($_REQUEST['mjschool_setting_add']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_add'])) : 1,
				'edit'       => isset($_REQUEST['mjschool_setting_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_edit'])) : 1,
				'view'       => isset($_REQUEST['mjschool_setting_view']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_view'])) : 1,
				'delete'     => isset($_REQUEST['mjschool_setting_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_delete'])) : 0,
			),
			// Email Template
			'email_template' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-email-template.png'),
				'menu_title' => 'Email Template',
				'page_link'  => 'email_template',
				'own_data'   => isset($_REQUEST['email_template_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_own_data'])) : 0,
				'add'        => isset($_REQUEST['email_template_add']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_add'])) : 1,
				'edit'       => isset($_REQUEST['email_template_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_edit'])) : 1,
				'view'       => isset($_REQUEST['email_template_view']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_view'])) : 1,
				'delete'     => isset($_REQUEST['email_template_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_delete'])) : 0,
			),
			// Email Template (duplicate module)
			'mjschool_template' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-email-template.png'),
				'menu_title' => 'Email Template',
				'page_link'  => 'mjschool_template',
				'own_data'   => isset($_REQUEST['mjschool_template_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_own_data'])) : 0,
				'add'        => isset($_REQUEST['mjschool_template_add']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_add'])) : 1,
				'edit'       => isset($_REQUEST['mjschool_template_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_edit'])) : 1,
				'view'       => isset($_REQUEST['mjschool_template_view']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_view'])) : 1,
				'delete'     => isset($_REQUEST['mjschool_template_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_delete'])) : 0,
			),
			// General Settings
			'general_settings' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-general-settings.png'),
				'menu_title' => 'General Settings',
				'page_link'  => 'general_settings',
				'own_data'   => isset($_REQUEST['general_settings_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['general_settings_own_data'])) : 0,
				'add'        => isset($_REQUEST['general_settings_add']) ? sanitize_text_field(wp_unslash($_REQUEST['general_settings_add'])) : 0,
				'edit'       => isset($_REQUEST['general_settings_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['general_settings_edit'])) : 0,
				'view'       => isset($_REQUEST['general_settings_view']) ? sanitize_text_field(wp_unslash($_REQUEST['general_settings_view'])) : 0,
				'delete'     => isset($_REQUEST['general_settings_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['general_settings_delete'])) : 0,
			),
			// Account
			'account' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-account.png'),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset($_REQUEST['account_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['account_own_data'])) : 1,
				'add'        => isset($_REQUEST['account_add']) ? sanitize_text_field(wp_unslash($_REQUEST['account_add'])) : 0,
				'edit'       => isset($_REQUEST['account_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['account_edit'])) : 1,
				'view'       => isset($_REQUEST['account_view']) ? sanitize_text_field(wp_unslash($_REQUEST['account_view'])) : 1,
				'delete'     => isset($_REQUEST['account_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['account_delete'])) : 0,
			),
			// Event
			'event' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-report.png'),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset($_REQUEST['event_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['event_own_data'])) : 0,
				'add'        => isset($_REQUEST['event_add']) ? sanitize_text_field(wp_unslash($_REQUEST['event_add'])) : 1,
				'edit'       => isset($_REQUEST['event_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['event_edit'])) : 1,
				'view'       => isset($_REQUEST['event_view']) ? sanitize_text_field(wp_unslash($_REQUEST['event_view'])) : 1,
				'delete'     => isset($_REQUEST['event_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['event_delete'])) : 1,
			),
		);
		// Access rights for 'management' role.
		$role_access_right_management               = array();
		$role_access_right_management['management'] = array(
			// NEw Menu Addded. //
			'admission' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Admission',
				'page_link'  => 'admission',
				'own_data'   => isset($_REQUEST['admission_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_own_data'])) : 0,
				'add'        => isset($_REQUEST['admission_add']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_add'])) : 1,
				'edit'       => isset($_REQUEST['admission_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_edit'])) : 1,
				'view'       => isset($_REQUEST['admission_view']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_view'])) : 1,
				'delete'     => isset($_REQUEST['admission_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['admission_delete'])) : 1,
			),
			'supportstaff' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Supportstaff',
				'page_link'  => 'supportstaff',
				'own_data'   => isset($_REQUEST['supportstaff_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_own_data'])) : 0,
				'add'        => isset($_REQUEST['supportstaff_add']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_add'])) : 1,
				'edit'       => isset($_REQUEST['supportstaff_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_edit'])) : 1,
				'view'       => isset($_REQUEST['supportstaff_view']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_view'])) : 1,
				'delete'     => isset($_REQUEST['supportstaff_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['supportstaff_delete'])) : 1,
			),
			'exam_hall' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Exam Hall',
				'page_link'  => 'exam_hall',
				'own_data'   => isset($_REQUEST['exam_hall_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_own_data'])) : 0,
				'add'        => isset($_REQUEST['exam_hall_add']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_add'])) : 1,
				'edit'       => isset($_REQUEST['exam_hall_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_edit'])) : 1,
				'view'       => isset($_REQUEST['exam_hall_view']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_view'])) : 1,
				'delete'     => isset($_REQUEST['exam_hall_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_hall_delete'])) : 1,
			),
			'grade' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Grade',
				'page_link'  => 'grade',
				'own_data'   => isset($_REQUEST['grade_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_own_data'])) : 0,
				'add'        => isset($_REQUEST['grade_add']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_add'])) : 1,
				'edit'       => isset($_REQUEST['grade_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_edit'])) : 1,
				'view'       => isset($_REQUEST['grade_view']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_view'])) : 1,
				'delete'     => isset($_REQUEST['grade_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['grade_delete'])) : 1,
			),
			'notification' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Notification',
				'page_link'  => 'notification',
				'own_data'   => isset($_REQUEST['notification_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_own_data'])) : 0,
				'add'        => isset($_REQUEST['notification_add']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_add'])) : 1,
				'edit'       => isset($_REQUEST['notification_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_edit'])) : 1,
				'view'       => isset($_REQUEST['notification_view']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_view'])) : 1,
				'delete'     => isset($_REQUEST['notification_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['notification_delete'])) : 1,
			),
			'custom_field' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Custom Field',
				'page_link'  => 'custom_field',
				'own_data'   => isset($_REQUEST['custom_field_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_own_data'])) : 0,
				'add'        => isset($_REQUEST['custom_field_add']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_add'])) : 1,
				'edit'       => isset($_REQUEST['custom_field_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_edit'])) : 1,
				'view'       => isset($_REQUEST['custom_field_view']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_view'])) : 1,
				'delete'     => isset($_REQUEST['custom_field_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['custom_field_delete'])) : 1,
			),
			'migration' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Migration',
				'page_link'  => 'migration',
				'own_data'   => isset($_REQUEST['migration_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_own_data'])) : 0,
				'add'        => isset($_REQUEST['migration_add']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_add'])) : 0,
				'edit'       => isset($_REQUEST['migration_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_edit'])) : 0,
				'view'       => isset($_REQUEST['migration_view']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_view'])) : 1,
				'delete'     => isset($_REQUEST['migration_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['migration_delete'])) : 0,
			),
			'mjschool_setting' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'SMS Setting',
				'page_link'  => 'mjschool_setting',
				'own_data'   => isset($_REQUEST['mjschool_setting_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_own_data'])) : 0,
				'add'        => isset($_REQUEST['mjschool_setting_add']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_add'])) : 0,
				'edit'       => isset($_REQUEST['mjschool_setting_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_edit'])) : 1,
				'view'       => isset($_REQUEST['mjschool_setting_view']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_view'])) : 1,
				'delete'     => isset($_REQUEST['mjschool_setting_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_setting_delete'])) : 0,
			),
			// TAX. //
			'tax' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-fee.png'),
				'menu_title' => 'Tax',
				'page_link'  => 'tax',
				'own_data'   => isset($_REQUEST['tax_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_own_data'])) : 0,
				'add'        => isset($_REQUEST['tax_add']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_add'])) : 1,
				'edit'       => isset($_REQUEST['tax_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_edit'])) : 1,
				'view'       => isset($_REQUEST['tax_view']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_view'])) : 1,
				'delete'     => isset($_REQUEST['tax_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['tax_delete'])) : 1,
			),
			'email_template' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Email Template',
				'page_link'  => 'email_template',
				'own_data'   => isset($_REQUEST['email_template_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_own_data'])) : 0,
				'add'        => isset($_REQUEST['email_template_add']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_add'])) : 1,
				'edit'       => isset($_REQUEST['email_template_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_edit'])) : 1,
				'view'       => isset($_REQUEST['email_template_view']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_view'])) : 1,
				'delete'     => isset($_REQUEST['email_template_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['email_template_delete'])) : 1,
			),
			'mjschool_template' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-email-template.png'),
				'menu_title' => 'Email Template',
				'page_link'  => 'mjschool_template',
				'own_data'   => isset($_REQUEST['mjschool_template_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_own_data'])) : 0,
				'add'        => isset($_REQUEST['mjschool_template_add']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_add'])) : 1,
				'edit'       => isset($_REQUEST['mjschool_template_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_edit'])) : 1,
				'view'       => isset($_REQUEST['mjschool_template_view']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_view'])) : 1,
				'delete'     => isset($_REQUEST['mjschool_template_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['mjschool_template_delete'])) : 0,
			),
			'access_right' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Access Right',
				'page_link'  => 'access_right',
				'own_data'   => isset($_REQUEST['access_right_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['access_right_own_data'])) : 0,
				'add'        => isset($_REQUEST['access_right_add']) ? sanitize_text_field(wp_unslash($_REQUEST['access_right_add'])) : 0,
				'edit'       => isset($_REQUEST['access_right_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['access_right_edit'])) : 1,
				'view'       => isset($_REQUEST['access_right_view']) ? sanitize_text_field(wp_unslash($_REQUEST['access_right_view'])) : 1,
				'delete'     => isset($_REQUEST['access_right_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['access_right_delete'])) : 0,
			),
			// teacher.
			'teacher' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-teacher.png'),
				'menu_title' => 'Teacher',
				'page_link'  => 'teacher',
				'own_data'   => isset($_REQUEST['teacher_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_own_data'])) : 0,
				'add'        => isset($_REQUEST['teacher_add']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_add'])) : 1,
				'edit'       => isset($_REQUEST['teacher_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_edit'])) : 1,
				'view'       => isset($_REQUEST['teacher_view']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_view'])) : 1,
				'delete'     => isset($_REQUEST['teacher_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['teacher_delete'])) : 1,
			),
			// student.
			'student' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-student-icon.png'),
				'menu_title' => 'Student',
				'page_link'  => 'student',
				'own_data'   => isset($_REQUEST['student_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['student_own_data'])) : 0,
				'add'        => isset($_REQUEST['student_add']) ? sanitize_text_field(wp_unslash($_REQUEST['student_add'])) : 1,
				'edit'       => isset($_REQUEST['student_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['student_edit'])) : 1,
				'view'       => isset($_REQUEST['student_view']) ? sanitize_text_field(wp_unslash($_REQUEST['student_view'])) : 1,
				'delete'     => isset($_REQUEST['student_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['student_delete'])) : 1,
			),
			// parent.
			'parent' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-parents.png'),
				'menu_title' => 'Parent',
				'page_link'  => 'parent',
				'own_data'   => isset($_REQUEST['parent_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_own_data'])) : 0,
				'add'        => isset($_REQUEST['parent_add']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_add'])) : 1,
				'edit'       => isset($_REQUEST['parent_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_edit'])) : 1,
				'view'       => isset($_REQUEST['parent_view']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_view'])) : 1,
				'delete'     => isset($_REQUEST['parent_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['parent_delete'])) : 1,
			),
			// subject.
			'subject' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-subject.png'),
				'menu_title' => 'Subject',
				'page_link'  => 'subject',
				'own_data'   => isset($_REQUEST['subject_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_own_data'])) : 0,
				'add'        => isset($_REQUEST['subject_add']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_add'])) : 1,
				'edit'       => isset($_REQUEST['subject_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_edit'])) : 1,
				'view'       => isset($_REQUEST['subject_view']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_view'])) : 1,
				'delete'     => isset($_REQUEST['subject_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['subject_delete'])) : 1,
			),
			// class.
			'class' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-class.png'),
				'menu_title' => 'Class',
				'page_link'  => 'class',
				'own_data'   => isset($_REQUEST['class_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_own_data'])) : 0,
				'add'        => isset($_REQUEST['class_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_add'])) : 1,
				'edit'       => isset($_REQUEST['class_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_edit'])) : 1,
				'view'       => isset($_REQUEST['class_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_view'])) : 1,
				'delete'     => isset($_REQUEST['class_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_delete'])) : 1,
			),
			// virtual classroom.
			'virtual_classroom' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-virtual-classroom.png'),
				'menu_title' => 'virtual_classroom',
				'page_link'  => 'virtual_classroom',
				'own_data'   => isset($_REQUEST['virtual_classroom_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_own_data'])) : 0,
				'add'        => isset($_REQUEST['virtual_classroom_add']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_add'])) : 1,
				'edit'       => isset($_REQUEST['virtual_classroom_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_edit'])) : 1,
				'view'       => isset($_REQUEST['virtual_classroom_view']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_view'])) : 1,
				'delete'     => isset($_REQUEST['virtual_classroom_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['virtual_classroom_delete'])) : 1,
			),
			// schedule.
			'schedule' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-class-route.png'),
				'menu_title' => 'Class Routine',
				'page_link'  => 'schedule',
				'own_data'   => isset($_REQUEST['schedule_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_own_data'])) : 0,
				'add'        => isset($_REQUEST['schedule_add']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_add'])) : 1,
				'edit'       => isset($_REQUEST['schedule_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_edit'])) : 1,
				'view'       => isset($_REQUEST['schedule_view']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_view'])) : 1,
				'delete'     => isset($_REQUEST['schedule_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['schedule_delete'])) : 1,
			),
			// attendance.
			'attendance' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-attandance.png'),
				'menu_title' => 'Attendance',
				'page_link'  => 'attendance',
				'own_data'   => isset($_REQUEST['attendance_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_own_data'])) : 0,
				'add'        => isset($_REQUEST['attendance_add']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_add'])) : 1,
				'edit'       => isset($_REQUEST['attendance_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_edit'])) : 1,
				'view'       => isset($_REQUEST['attendance_view']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_view'])) : 1,
				'delete'     => isset($_REQUEST['attendance_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['attendance_delete'])) : 1,
			),
			// exam.
			'exam' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-exam.png'),
				'menu_title' => 'Exam',
				'page_link'  => 'exam',
				'own_data'   => isset($_REQUEST['exam_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_own_data'])) : 0,
				'add'        => isset($_REQUEST['exam_add']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_add'])) : 1,
				'edit'       => isset($_REQUEST['exam_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_edit'])) : 1,
				'view'       => isset($_REQUEST['exam_view']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_view'])) : 1,
				'delete'     => isset($_REQUEST['exam_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['exam_delete'])) : 1,
			),
			// class_room.
			'class_room' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-exam.png'),
				'menu_title' => 'Class Room',
				'page_link'  => 'class_room',
				'own_data'   => isset($_REQUEST['class_room_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_own_data'])) : 0,
				'add'        => isset($_REQUEST['class_room_add']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_add'])) : 1,
				'edit'       => isset($_REQUEST['class_room_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_edit'])) : 1,
				'view'       => isset($_REQUEST['class_room_view']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_view'])) : 1,
				'delete'     => isset($_REQUEST['class_room_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['class_room_delete'])) : 1,
			),
			// hostel.
			'hostel' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-hostel.png'),
				'menu_title' => 'Hostel',
				'page_link'  => 'hostel',
				'own_data'   => isset($_REQUEST['hostel_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_own_data'])) : 0,
				'add'        => isset($_REQUEST['hostel_add']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_add'])) : 1,
				'edit'       => isset($_REQUEST['hostel_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_edit'])) : 1,
				'view'       => isset($_REQUEST['hostel_view']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_view'])) : 1,
				'delete'     => isset($_REQUEST['hostel_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['hostel_delete'])) : 1,
			),
			// homework.
			'homework' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-homework.png'),
				'menu_title' => 'Home Work',
				'page_link'  => 'homework',
				'own_data'   => isset($_REQUEST['homework_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_own_data'])) : 0,
				'add'        => isset($_REQUEST['homework_add']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_add'])) : 1,
				'edit'       => isset($_REQUEST['homework_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_edit'])) : 1,
				'view'       => isset($_REQUEST['homework_view']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_view'])) : 1,
				'delete'     => isset($_REQUEST['homework_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['homework_delete'])) : 1,
			),
			// manage marks.
			'manage_marks' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-mark-manage.png'),
				'menu_title' => 'Mark Manage',
				'page_link'  => 'manage-marks',
				'own_data'   => isset($_REQUEST['manage_marks_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_own_data'])) : 0,
				'add'        => isset($_REQUEST['manage_marks_add']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_add'])) : 1,
				'edit'       => isset($_REQUEST['manage_marks_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_edit'])) : 1,
				'view'       => isset($_REQUEST['manage_marks_view']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_view'])) : 1,
				'delete'     => isset($_REQUEST['manage_marks_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['manage_marks_delete'])) : 1,
			),
			// payment.
			'payment' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-payment.png'),
				'menu_title' => 'Payment',
				'page_link'  => 'payment',
				'own_data'   => isset($_REQUEST['payment_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_own_data'])) : 0,
				'add'        => isset($_REQUEST['payment_add']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_add'])) : 1,
				'edit'       => isset($_REQUEST['payment_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_edit'])) : 1,
				'view'       => isset($_REQUEST['payment_view']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_view'])) : 1,
				'delete'     => isset($_REQUEST['payment_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['payment_delete'])) : 1,
			),
			// transport.
			'transport' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-transport.png'),
				'menu_title' => 'Transport',
				'page_link'  => 'transport',
				'own_data'   => isset($_REQUEST['transport_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_own_data'])) : 0,
				'add'        => isset($_REQUEST['transport_add']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_add'])) : 1,
				'edit'       => isset($_REQUEST['transport_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_edit'])) : 1,
				'view'       => isset($_REQUEST['transport_view']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_view'])) : 1,
				'delete'     => isset($_REQUEST['transport_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['transport_delete'])) : 1,
			),
			// document.
			'document' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-hostel.png'),
				'menu_title' => 'Document',
				'page_link'  => 'document',
				'own_data'   => isset($_REQUEST['document_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['document_own_data'])) : 0,
				'add'        => isset($_REQUEST['document_add']) ? sanitize_text_field(wp_unslash($_REQUEST['document_add'])) : 1,
				'edit'       => isset($_REQUEST['document_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['document_edit'])) : 1,
				'view'       => isset($_REQUEST['document_view']) ? sanitize_text_field(wp_unslash($_REQUEST['document_view'])) : 1,
				'delete'     => isset($_REQUEST['document_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['document_delete'])) : 1,
			),
			// leave.
			'leave' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-notification_new.png'),
				'app_icone'  => plugins_url('mjschool/assets/images/icons/mjschool-notification_new.png'),
				'menu_title' => 'Leave',
				'page_link'  => 'leave',
				'own_data'   => isset($_REQUEST['leave_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_own_data'])) : 1,
				'add'        => isset($_REQUEST['leave_add']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_add'])) : 1,
				'edit'       => isset($_REQUEST['leave_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_edit'])) : 1,
				'view'       => isset($_REQUEST['leave_view']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_view'])) : 1,
				'delete'     => isset($_REQUEST['leave_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['leave_delete'])) : 1,
			),
			// notice.
			'notice' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-notice.png'),
				'menu_title' => 'Notice Board',
				'page_link'  => 'notice',
				'own_data'   => isset($_REQUEST['notice_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_own_data'])) : 0,
				'add'        => isset($_REQUEST['notice_add']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_add'])) : 1,
				'edit'       => isset($_REQUEST['notice_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_edit'])) : 1,
				'view'       => isset($_REQUEST['notice_view']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_view'])) : 1,
				'delete'     => isset($_REQUEST['notice_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['notice_delete'])) : 1,
			),
			// message.
			'message' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-message.png'),
				'menu_title' => 'Message',
				'page_link'  => 'message',
				'own_data'   => isset($_REQUEST['message_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['message_own_data'])) : 0,
				'add'        => isset($_REQUEST['message_add']) ? sanitize_text_field(wp_unslash($_REQUEST['message_add'])) : 1,
				'edit'       => isset($_REQUEST['message_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['message_edit'])) : 1,
				'view'       => isset($_REQUEST['message_view']) ? sanitize_text_field(wp_unslash($_REQUEST['message_view'])) : 1,
				'delete'     => isset($_REQUEST['message_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['message_delete'])) : 1,
			),
			// holiday.
			'holiday' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-holiday.png'),
				'menu_title' => 'Holiday',
				'page_link'  => 'holiday',
				'own_data'   => isset($_REQUEST['holiday_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_own_data'])) : 0,
				'add'        => isset($_REQUEST['holiday_add']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_add'])) : 1,
				'edit'       => isset($_REQUEST['holiday_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_edit'])) : 1,
				'view'       => isset($_REQUEST['holiday_view']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_view'])) : 1,
				'delete'     => isset($_REQUEST['holiday_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['holiday_delete'])) : 1,
			),
			// library.
			'library' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-library.png'),
				'menu_title' => 'Library',
				'page_link'  => 'library',
				'own_data'   => isset($_REQUEST['library_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['library_own_data'])) : 0,
				'add'        => isset($_REQUEST['library_add']) ? sanitize_text_field(wp_unslash($_REQUEST['library_add'])) : 1,
				'edit'       => isset($_REQUEST['library_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['library_edit'])) : 1,
				'view'       => isset($_REQUEST['library_view']) ? sanitize_text_field(wp_unslash($_REQUEST['library_view'])) : 1,
				'delete'     => isset($_REQUEST['library_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['library_delete'])) : 1,
			),
			// certificate.
			'certificate' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-library.png'),
				'menu_title' => 'Certificate',
				'page_link'  => 'certificate',
				'own_data'   => isset($_REQUEST['certificate_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_own_data'])) : 0,
				'add'        => isset($_REQUEST['certificate_add']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_add'])) : 1,
				'edit'       => isset($_REQUEST['certificate_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_edit'])) : 1,
				'view'       => isset($_REQUEST['certificate_view']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_view'])) : 1,
				'delete'     => isset($_REQUEST['certificate_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['certificate_delete'])) : 1,
			),
			// account.
			'account' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-account.png'),
				'menu_title' => 'Account',
				'page_link'  => 'account',
				'own_data'   => isset($_REQUEST['account_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['account_own_data'])) : 0,
				'add'        => isset($_REQUEST['account_add']) ? sanitize_text_field(wp_unslash($_REQUEST['account_add'])) : 0,
				'edit'       => isset($_REQUEST['account_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['account_edit'])) : 1,
				'view'       => isset($_REQUEST['account_view']) ? sanitize_text_field(wp_unslash($_REQUEST['account_view'])) : 1,
				'delete'     => isset($_REQUEST['account_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['account_delete'])) : 0,
			),
			// report.
			'report' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-report.png'),
				'menu_title' => 'Report',
				'page_link'  => 'report',
				'own_data'   => isset($_REQUEST['report_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['report_own_data'])) : 0,
				'add'        => isset($_REQUEST['report_add']) ? sanitize_text_field(wp_unslash($_REQUEST['report_add'])) : 0,
				'edit'       => isset($_REQUEST['report_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['report_edit'])) : 0,
				'view'       => isset($_REQUEST['report_view']) ? sanitize_text_field(wp_unslash($_REQUEST['report_view'])) : 1,
				'delete'     => isset($_REQUEST['report_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['report_delete'])) : 0,
			),
			// event.
			'event' => array(
				'menu_icone' => plugins_url('mjschool/assets/images/icons/mjschool-report.png'),
				'menu_title' => 'Event',
				'page_link'  => 'event',
				'own_data'   => isset($_REQUEST['event_own_data']) ? sanitize_text_field(wp_unslash($_REQUEST['event_own_data'])) : 0,
				'add'        => isset($_REQUEST['event_add']) ? sanitize_text_field(wp_unslash($_REQUEST['event_add'])) : 1,
				'edit'       => isset($_REQUEST['event_edit']) ? sanitize_text_field(wp_unslash($_REQUEST['event_edit'])) : 1,
				'view'       => isset($_REQUEST['event_view']) ? sanitize_text_field(wp_unslash($_REQUEST['event_view'])) : 1,
				'delete'     => isset($_REQUEST['event_delete']) ? sanitize_text_field(wp_unslash($_REQUEST['event_delete'])) : 1,
			),
		);
		$dashboard_card_access_for_student       = array();
		$dashboard_card_access_for_student       = array(
			'mjschool_payment_status_chart' => isset( $_REQUEST['payment_status_chart_enable_student'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['payment_status_chart_enable_student'])) ) : 'yes',
			'mjschool_user_chart'           => isset( $_REQUEST['user_chart_enable_student'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['user_chart_enable_student'])) ) : 'yes',
			'mjschool_invoice_chart'        => isset( $_REQUEST['invoice_enable'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['invoice_enable'])) ) : 'yes',
		);
		$dashboard_card_access_for_support_staff = array();
		$dashboard_card_access_for_support_staff = array(
			'mjschool_student_status_chart' => isset( $_REQUEST['student_status_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['student_status_staff'])) ) : 'yes',
			'mjschool_attendance_chart'     => isset( $_REQUEST['attendance_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['attendance_staff'])) ) : 'yes',
			'mjschool_payment_status_chart' => isset( $_REQUEST['payment_status_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['payment_status_staff'])) ) : 'yes',
			'mjschool_payment_report'       => isset( $_REQUEST['payment_report_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['payment_report_staff'])) ) : 'yes',
			'mjschool_invoice_chart'        => isset( $_REQUEST['invoice_enable_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['invoice_enable_staff'])) ) : 'yes',
			'mjschool_user_chart'           => isset( $_REQUEST['users_chart_staff'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['users_chart_staff'])) ) : 'yes',
		);
		$dashboard_card_access_for_teacher       = array();
		$dashboard_card_access_for_teacher       = array(
			'mjschool_student_status_chart' => isset( $_REQUEST['student_status_enable_teacher'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['student_status_enable_teacher'])) ) : 'yes',
			'mjschool_attendance_chart'     => isset( $_REQUEST['attendance_chart_enable_teacher'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['attendance_chart_enable_teacher'])) ) : 'yes',
			'mjschool_user_chart'           => isset( $_REQUEST['user_chart_enable_teacher'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['user_chart_enable_teacher'])) ) : 'yes',
		);
		$dashboard_card_access_for_parent        = array();
		$dashboard_card_access_for_parent        = array(
			'mjschool_user_chart'           => isset( $_REQUEST['user_chart_parent'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['user_chart_parent'])) ) : 'yes',
			'mjschool_invoice_chart'        => isset( $_REQUEST['invoice_enable_parent'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['invoice_enable_parent'])) ) : 'yes',
			'mjschool_payment_status_chart' => isset( $_REQUEST['payment_status_parent'] ) ? esc_attr( sanitize_text_field(wp_unslash($_REQUEST['payment_status_parent'])) ) : 'yes',
		);
		/* Setup Wizard Options. */
		$mjschool_setup_wizard_step = array(
			'step1_general_setting'  => 'no',
			'step2_class'            => 'no',
			'step3_teacher'          => 'no',
			'step4_subject'          => 'no',
			'step5_class_time_table' => 'no',
			'step6_student'          => 'no',
			'step7_email_temp'       => 'no',
		);
		/* Setup Wizard Step */
		$wizard_option = get_option( 'mjschool_setup_wizard_step' );
		if ( empty( $wizard_option ) ) {
			$wizard_option = add_option( 'mjschool_setup_wizard_step', $mjschool_setup_wizard_step );
		}
		/* Setup Wizard Status */
		$mjschool_setup_wizard_status = 'no';
		
		// Define other system options like school name, logo, and contact info.
		$options = array(
			'mjschool_name'                         => esc_attr__( 'School Management System', 'mjschool' ),
			'mjschool_staring_year'                        => '2025',
			'mjschool_address'		                      => '',
			'mjschool_contact_number'                      => '',
			'mjschool_combine'                             => 0,
			'mjschool_contry'                              => 'United States',
			'mjschool_city'                                => 'Los Angeles',
			"mjschool_class_room"	 					  => 0,
			"mjschool_custom_class" 					  => "school",
			"mjschool_custom_class_display" 			  => 0,
			'mjschool_past_pay'                           => 'no',
			'mjschool_prefix'                              => 'S-',
			'mjschool_email'                               => 'admin@gmail.com',
			'mjschool_datepicker_format'                   => 'yy/mm/dd',
			'mjschool_app_logo'		                       => plugins_url( 'mjschool/assets/images/mjschool-mobile-app-default.png' ),
			'mjschool_logo'          		               => plugins_url( 'mjschool/assets/images/mjschool-final-logo.png' ),
			'mjschool_system_logo'                         => plugins_url( 'mjschool/assets/images/mjschool-logo-white.png' ),
			'mjschool_background_image'  		           => plugins_url( 'mjschool/assets/images/school_life.jpg' ),
			'mjschool_student_thumb'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
			'mjschool_mjschool-no-data-img'                => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-plus-icon.png' ),
			'mjschool_parent_thumb'                        => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
			'mjschool_teacher_thumb'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
			'mjschool_supportstaff_thumb'                  => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
			'mjschool_driver_thumb'                        => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
			'mjschool_principal_signature'                 => plugins_url( 'mjschool/assets/images/mjschool-signature-stamp.png' ),
			'mjschool_student_thumb_new'                   => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
			'mjschool_parent_thumb_new'                    => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
			'mjschool_teacher_thumb_new'                   => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
			'mjschool_supportstaff_thumb_new'              => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
			'mjschool_driver_thumb_new'                    => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
			'mjschool_footer_description'                  => 'Copyright ©' . date( 'Y' ) . ' Mojoomla. All rights reserved.',
			'mjschool_access_right_student'                => $role_access_right_student,
			'mjschool_access_right_teacher'                => $role_access_right_teacher,
			'mjschool_access_right_parent'                 => $role_access_right_parent,
			'mjschool_access_right_supportstaff'           => $role_access_right_support_staff,
			'mjschool_access_right_management'             => $role_access_right_management,
			'mjschool_dashboard_card_for_student'          => $dashboard_card_access_for_student,
			'mjschool_dashboard_card_for_teacher'          => $dashboard_card_access_for_teacher,
			'mjschool_dashboard_card_for_support_staff'    => $dashboard_card_access_for_support_staff,
			'mjschool_dashboard_card_for_parent'           => $dashboard_card_access_for_parent,
			'mjschool_setup_wizard_status'                 => $mjschool_setup_wizard_status,
			'mjschool_service'                    => 'msg91',
			// Mobile APP Settings Option.//
			'mjschool_app_domain_name'                         => '',
			'mjschool_app_licence_key'                         => '',
			'mjschool_app_setup_email'                         => '',
			// PAY MASTER OPTION.//
			'mjschool_paymaster_pack'                      => 'no',
			'mjschool_invoice_option'                      => 1,
			'mjschool_mail_notification'                   => 1,
			'mjschool_notification_fcm_key'                => '',
			'mjschool_service_enable'             => 0,
			'mjschool_student_approval'                    => 1,
			'mjschool_sms_template'                            => 'Hello [mjschool_USER_NAME] ',
			'mjschool_clickatell_mjschool_service'         => array(),
			'mjschool_twillo_mjschool_service'             => array(),
			'mjschool_parent_send_message'                 => 1,
			'mjschool_enable_total_student'                => 1,
			'mjschool_enable_total_teacher'                => 1,
			'mjschool_enable_total_parent'                 => 1,
			'mjschool_enable_homework_mail'                => 0,
			'mjschool_enable_total_attendance'             => 1,
			'mjschool_enable_sandbox'                      => 'yes',
			'mjschool_virtual_classroom_account_id'        => '',
			'mjschool_virtual_classroom_client_id'         => '',
			'mjschool_virtual_classroom_client_secret_id'  => '',
			'mjschool_virtual_classroom_access_token'      => '',
			'mjschool_enable_virtual_classroom'            => 'no',
			'mjschool_return_option'                       => 'yes',
			'mjschool_return_period'                       => 3,
			'mjschool_system_payment_reminder_day'         => 3,
			'mjschool_system_payment_reminder_enable'      => 'no',
			'mjschool_paypal_email'                        => '',
			'razorpay__key'                                => '',
			'razorpay_secret_mid'                          => '',
			'mjschool_currency_code'                       => 'USD',
			'mjschool_teacher_manage_allsubjects_marks'    => 'yes',
			'mjschool_enable_video_popup_show'             => 'yes',
			'mjschool_registration_title'                  => 'Student Registration',
			'mjschool_student_activation_title'            => 'Student Approved',
			'mjschool_fee_payment_title'                   => 'Fees Alert',
			'mjschool_fee_payment_title_for_parent'        => 'Fees Alert',
			'mjschool_teacher_show_access'                 => 'own_class',
			'mjschool_admissiion_title'                    => 'Request For Admission',
			'mjschool_exam_receipt_subject'                => 'Exam Receipt Generate',
			'mjschool_bed_subject'                         => 'Hostel Bed Assigned',
			'mjschool_add_approve_admisson_mail_subject'   => 'Admission Approved',
			'mjschool_admissiion_approve_subject_for_parent_subject' => 'Student Admission Approved',
			'mjschool_student_assign_teacher_mail_subject' => 'New Student has been assigned to you.',
			'mjschool_enable_virtual_classroom_reminder'   => 'yes',
			'mjschool_enable_mjschool_virtual_classroom_reminder' => 'yes',
			'mjschool_virtual_classroom_reminder_before_time' => '30',
			'mjschool_heder_enable'                        => 'yes',
			'mjschool_admission_fees'                      => 'no',
			'mjschool_enable_recurring_invoices'           => 'no',
			'mjschool_admission_amount'                    => '',
			'mjschool_system_color_code'                   => '#5840bb',
			'mjschool_registration_fees'                   => 'no',
			'mjschool_registration_amount'                 => '',
			'mjschool_invoice_notice'                      => 'If You Paid Your Payment than Invoice are automatically Generated.',
			'mjschool_attendence_migration_status'         => 'no',
			'mjschool_add_leave_emails'                    => '',
			'mjschool_leave_approveemails'                 => '',
			'mjschool_add_leave_subject'                   => 'Request For Leave',
			'mjschool_add_leave_subject_of_admin'          => 'Request For Leave',
			'mjschool_add_leave_subject_for_student'       => 'Request For Leave',
			'mjschool_add_leave_subject_for_parent'        => 'Request For Leave',
			'mjschool_leave_approve_subject'               => 'Your leave has been Approved Successfully',
			'mjschool_leave_reject_subject'                => 'Your leave has been Rejected',
			'mjschool_add_exam_mail_title'                 => 'New exam has been assigned to you.',
			'mjschool_upload_document_type'                => 'pdf, doc, docx, ppt, pptx, gif, png, jpg, jpeg, webp',
			'mjschool_upload_profile_extention'            => 'gif, png, jpg, jpeg, webp',
			'mjschool_upload_document_size'                => '30',
			'mjschool_upload_profile_size'                 => '10',
			// ------------- SMS Template Start. --------------- //
			'mjschool_attendance_mjschool_content'         => 'Dear {{parent_name}}, your child {{student_name}} is absent on {{current_date}} at {{school_name}}.',
			'mjschool_fees_payment_mjschool_content_for_student' => 'Dear {{student_name}}, A new fees payment invoice has been generated for you at {{school_name}}.',
			'mjschool_fees_payment_mjschool_content_for_parent' => 'Dear {{parent_name}}, A new fees payment invoice has been generated for your {{student_name}} at {{school_name}}.',
			'mjschool_fees_payment_reminder_mjschool_content' => 'Dear {{parent_name}}, we just wanted to send you a reminder that the tuition fee has not been paid against your child {{student_name}} at {{school_name}}.',
			'mjschool_student_approve_mjschool_content'    => 'Dear {{student_name}}, your account with {{school_name}} is approved.',
			'mjschool_student_admission_approve_mjschool_content' => 'Dear {{student_name}}, your admission has been successfully approved with {{school_name}}.',
			'mjschool_holiday_mjschool_content'            => 'Dear {{user_name}}, New Holiday {{title}} Announced at {{school_name}}.',
			'mjschool_leave_student_mjschool_content'      => 'Dear {{student_name}}, your Leave for {{date}} are Added Successfully at {{school_name}}.',
			'mjschool_leave_parent_mjschool_content'       => 'Dear {{parent_name}}, your child {{student_name}}, has been added leave of {{date}} at {{school_name}}.',
			'mjschool_event_mjschool_content'              => 'Dear {{student_name}}, we are inform you about an exciting new event {{event_title}} at {{school_name}}.',
			'mjschool_exam_student_mjschool_content'       => 'This is a reminder that your upcoming exam {{exam_name}} is scheduled for {{date}} At {{school_name}}.',
			'mjschool_exam_parent_mjschool_content'        => 'We would like to inform you that your child {{student_name}} will have an important exam {{exam_name}} on {{date}} At {{school_name}}.',
			'mjschool_homework_student_mjschool_content'   => 'Dear {{student_name}}, your new homework {{title}} is posted. Please check and submit it by the submission date {{date}} At {{school_name}}.',
			'mjschool_homework_parent_mjschool_content'    => 'Dear {{parent_name}}, your child has a new homework {{title}} assignment. Please review it with them and provide any necessary support and submit at {{school_name}}.',
			// ------------- SMS Template End. --------------- //
			'mjschool_student_assign_teacher_mail_content' => 'Dear {{teacher_name}},
         New Student {{student_name}} has been assigned to you.
		Regards From {{school_name}}.',
					'mjschool_generate_invoice_mail_subject'       => 'Generate Invoice',
					'mjschool_generate_invoice_mail_content'       => 'Dear {{student_name}},
				Your have a new invoice.  You can check the invoice attached here.
		Regards From {{school_name}}.',
					// ------------ ADD USER. ---------------//
					'mjschool_add_user_mail_subject'               => 'Your have been assigned role of {{role}} in {{school_name}}.',
					'mjschool_add_user_mail_content'               => 'Dear {{user_name}},
				You are Added by admin in {{school_name}} . Your have been assigned role of {{role}} in {{school_name}}.  You can sign in using this link. {{login_link}}
		UserName : {{username}}
		Password : {{Password}}
		Regards From {{school_name}}.',
					// ------- Registration Successfully. ----------//
					'mjschool_registration_mailtemplate'           => 'Hello {{student_name}} ,
		Your registration has been successful with {{school_name}}.
		Class Name : {{class_name}}
		Email ID : {{email_id}}
		Password : {{password}}
		Regards From {{school_name}}.',
					// ------- Request for  Admission. ----------//
					'mjschool_admission_mailtemplate_content'      => 'Hello {{student_name}} ,
		Your admission request has been successful with {{school_name}}. You will be able to access your account after school admin approves it and we will send username and password shortly.
		Student Name : {{user_name}}
		Email : {{email}}
		Regards From {{school_name}}.',
					// ------- Exam Receipt GENERATE.----------//
					'mjschool_exam_receipt_content'                => 'Hello {{student_name}} ,
				your exam hall receipt has been generated.
		Regards From {{school_name}}.',
					// ------- Hostel Bed Assigned. ----------//
					'mjschool_bed_content'                         => 'Hello {{student_name}} ,
				You have been assigned new hostel bed in {{school_name}}.
		Hostel Name : {{hostel_name}}
		Room Number : {{room_id}}
		Bed Number : {{bed_id}}
		Bed Charge : {{bed_charge}}
		Regards From {{school_name}}.',
					// ------- Approved mail perents. ----------//
					'mjschool_admission_mailtemplate_content_for_parent' => 'Hello {{student_name}},
			Your admission has been successfully approved with {{school_name}}.
			You can sign in using this link: {{login_link}}
			Class Name : {{class_name}}
			Roll No    : {{roll_no}}
			Email      : {{email}}
			Password   : {{password}}
			Regards,
			{{school_name}}',
					// ------- Approved Admission. ----------//
					'mjschool_add_approve_admission_mail_content'  => 'Hello {{user_name}} ,
				Your admission has been successful approved with {{school_name}}.
				You can signin using this link. {{login_link}}
				Class Name : {{class_name}}
				Roll No : {{roll_no}}
				Email : {{email}}
				Password : {{Password}}
				Regards From {{school_name}}.',
					// ----------- Student Activation. --------------//
					'mjschool_student_activation_mailcontent'      => 'Hello {{student_name}},
						Your account with {{school_name}} is approved. You can access student account using your login details. Your other details are given bellow.
		User Name : {{user_name}}
		Class Name : {{class_name}}
		Email : {{email}}
		Regards From {{school_name}}.',
					// --------- student Appoved Leave.   --------------//
					'mjschool_addleave_email_template'             =>
					'Hello,
		Date : {{date}}
		Leave Type : {{leave_type}}
		Leave Duration : {{leave_duration}}
		Reason : {{reason}}
		Thank you
		{{employee_name}}
		',
					'mjschool_leave_approve_email_template'        =>
					'Hello,
		Leave of {{user_name}} is approved successfully.
		Date     :  {{date}}
		Comment  : {{comment}}
		Regards From {{system_name}}.
		Thank you
		{{system_name}}
		',
					// -------------------- LEAVE REQUEST MAIL TEMPLATE FOR STUDENT. ---------------------//
					'mjschool_addleave_email_template_student'     =>
					'Hello {{student_name}},
		Your Leave are Added Successfully.
		Date : {{date}},
		Leave Type : {{leave_type}},
		Leave Duration : {{leave_duration}},
		Reason : {{reason}},
		Thank you
		{{school_name}}.
		',
					// -------------------- LEAVE REQUEST MAIL TEMPLATE FOR PARENT. ---------------------//
					'mjschool_addleave_email_template_parent'      =>
					'Hello {{parent_name}},
		Your child {{student_name}}, has been added leave of {{date}}.
		Leave Type : {{leave_type}},
		Leave Duration : {{leave_duration}},
		Reason : {{reason}},
		Thank you
		{{school_name}}.
		',
					// -------------------- LEAVE REQUEST MAIL TEMPLATE FOR ADMIN. ---------------------//
					'mjschool_addleave_email_template_of_admin'    =>
					'Dear Admin,
		{{student_name}} are Add Leave of {{date}}.
		Leave Type : {{leave_type}},
		Leave Duration : {{leave_duration}},
		Reason : {{reason}},
		Thank you
		{{school_name}}.
		',
					// --------------- LEAVE REJECT MAIL TEMPLATE. ----------------//
					'mjschool_leave_reject_email_template'         =>
					'Hello {{student_name}} ,
		Leave of {{student_name}} is Rejected.
		Date     :  {{date}}
		Comment  : {{comment}}
		Regards From {{school_name}}
		Thank you',
					// --------------- ADD EXAM MAIL TEMPLATE. ----------------//
					'mjschool_add_exam_mailcontent'                =>
					'Dear {{user_name}},
		A new exam {{exam_name}} has been assigned to you.
		Exam Details:
		Exam Name : {{exam_name}}
		Exam Start To End Date : {{exam_start_end_date}}
		Exam Comment : {{exam_comment}}
		Regards From
		{{school_name}}',
					// --------------- FEES PAYMENT FOR STUDENT. --------------//
					'mjschool_fee_payment_mailcontent'             => 'Dear {{student_name}},
				You have a new invoice.  You can check the invoice attached here.
			Date : {{date}}
			Amount : {{amount}}
		Regards From {{school_name}}
		Thank you',
					// --------------- FEES PAYMENT FOR PARENT. --------------//
					'mjschool_fee_payment_mailcontent_for_parent'  => 'Dear {{parent_name}},
			You have a new invoice for your child {{child_name}}. You can check the invoice attached here.
			Date : {{date}}
			Amount : {{amount}}
			Regards From {{school_name}}
		Thank you',
					// ------------------ MESSAGE RECEIVED. ---------------//
					'mjschool_message_received_mailcontent'        => 'Dear {{receiver_name}},
				You have received new message {{message_content}}.
		Regards From {{school_name}}.',
					'mjschool_message_received_mailsubject'        => 'You have received new message from {{from_mail}} at {{school_name}}',
					// ------------------ CHILD ABSENT. -------------------//
					'mjschool_absent_mail_notification_subject'    => 'Your Child {{child_name}} is absent today',
					'mjschool_absent_mail_notification_content'    => 'Your Child {{child_name}} is absent today.
		Regards From {{school_name}}.',
					// ----------------- ASSIGNED TEACHER. ------------------//
					'mjschoool_student_assign_to_teacher_subject'  => 'You have been Assigned {{teacher_name}} at {{school_name}}',
					'mjschool_student_assign_to_teacher_content'   => 'Dear {{student_name}},
				You are assigned to  {{teacher_name}}. {{teacher_name}} belongs to {{class_name}}.
		Regards From {{school_name}}.',
					'mjschool_payment_recived_mailsubject'         => 'Payment Received against Invoice',
					'mjschool_payment_recived_mailcontent'         => 'Dear {{student_name}},
				Your have successfully paid your invoice {{invoice_no}}. You can check the invoice attached here.
		Regards From {{school_name}}.',
					'mjschool_notice_mailsubject'                  => 'New Notice For You',
					'mjschool_notice_mailcontent'                  => 'New Notice For You.
		Notice Title : {{notice_title}}
		Notice Date  : {{notice_date}}
		Notice Comment :  {{notice_comment}}
		Regards From {{school_name}}
		',
					/* ----------------- Event Mail Template. ------------------- */
					'mjschool_event_mailsubject'                   => 'Exciting New Event at {{school_name}}.',
					'mjschool_event_mailcontent'                   => 'Dear {{user_name}},
					We are delighted to announce an exciting new event at {{school_name}} that promises to be a memorable experience for all attendees!
		Event Details:
		Event Name: {{event_title}}
		Date: {{event_date}}
		Time: {{event_time}}
		Description: {{description}}
		Regards From {{school_name}}.',
					/*   -------Parent mail notification template.------- */
					'mjschool_parent_homework_mail_subject'        => 'New Homework Assigned',
					'mjschool_parent_homework_mail_content'        => 'Dear {{parent_name}},
		New homework has been assign to your child.
		Student name : {{student_name}}
		Homework Title : {{title}}
		Subject : {{subject}}
		Homework Date : {{homework_date}}
		Submission Date : {{submition_date}}
		Regards From {{school_name}}
		',
					/*   -------student mail notification template.------- */
					'mjschool_homework_title'                      => 'New Homework Assigned',
					'mjschool_homework_mailcontent'                => 'Dear {{student_name}},
		New homework has been assign to you.
		Homework Title : {{title}}
		Subject : {{subject}}
		Homework Date : {{homework_date}}
		Submission Date : {{submition_date}}
		Regards From {{school_name}}
		',
					// -------------- HOLIDAY MAILTEMPLATE. -----------//
					'mjschool_holiday_mailsubject'                 => 'Holiday Announcement',
					'mjschool_holiday_mailcontent'                 => 'Holiday Announcement
		Holiday Title : {{holiday_title}}
		Holiday Date : {{holiday_date}}
		Regards From {{school_name}}
		',
					// ----------------------- VIRTUAL CLASSROOM TEACHER INVITE MAIL. ------//
					'mjschool_virtual_class_invite_teacher_mail_subject' => 'Inviting you to a scheduled Zoom meeting',
					'mjschool_virtual_class_invite_teacher_mail_content' => 'Inviting you to a scheduled Zoom meeting
			Class Name : {{class_name}}
			Time : {{time}}
			Virtual Class ID : {{virtual_class_id}}
			Password : {{password}}
			Join Zoom Virtual Class : {{join_zoom_virtual_class}}
			Start Zoom Virtual Class : {{start_zoom_virtual_class}}
			Regards From {{school_name}}
		',
					// ----------------------- VIRTUAL CLASSROOM TEACHER REMINDER MAIL. ------//
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
			Regards From {{school_name}}
		',
					// ----------------------- VIRTUAL CLASSROOM STUDENT REMINDER MAIL. ------//
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
			Regards From {{school_name}}
		',
					// ----------------- Fee Payment Reminder Mail. ---------------------//
					'mjschool_fee_payment_reminder_title'          => 'Fees Payment Reminder',
					'mjschool_fee_payment_reminder_mailcontent'    => '
		Dear {{parent_name}},
		We just wanted to send you a reminder that the tuition fee has not been paid against your son/daughter {{student_name}} of class {{class_name}} .the total amount is {{total_amount}} and the due amount is {{due_amount}}.
		Regards From
		{{school_name}}',
					// ----------------- Fee Payment Reminder Mail For Student.---------------------//
					'mjschool_fee_payment_reminder_title_for_student' => 'Fees Payment Reminder',
					'mjschool_fee_payment_reminder_mailcontent_for_student' => '
		Dear {{student_name}},
		We just wanted to send you a reminder that the tuition fee has not been paid against you. the total amount is {{total_amount}} and the due amount is {{due_amount}}.
		Regards From
		{{school_name}}',
					// ----------------- Assign Subject Mail. ---------------------//
					'mjschool_assign_subject_title'                => 'New subject has been assigned to you.',
					'mjschool_assign_subject_mailcontent'          => '
		Dear {{teacher_name}},
		New subject {{subject_name}} has been assigned to you.
		Regards From
		{{school_name}}',
					// PROMOTION LETTER TEMPLATE START.
					'mjschool_transfer_certificate_title'          => 'Transfer Certificate',
					// lol.
					'mjschool_transfer_certificate_template'       => '
		<div class="container_table">
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
			<tr><td style="border: 1px solid black;text-align: center;"><b>1.</b></td><td style="border: 1px solid black;padding-left: 6px;">Student’s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{student_name}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>2.</b></td><td style="border: 1px solid black;padding-left: 6px;">Father’s/Guardian’s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{father_name}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>3.</b></td><td style="border: 1px solid black;padding-left: 6px;">Mother’s Name</td><td style="border: 1px solid black;padding-left: 6px;">{{mother_name}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>4.</b></td><td style="border: 1px solid black;padding-left: 6px;">Date of Birth (DD-MM-YYYY)</td><td style="border: 1px solid black;padding-left: 6px;">{{birth_date}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>5.</b></td><td style="border: 1px solid black;padding-left: 6px;">Date of Birth (in Words)</td><td style="border: 1px solid black;padding-left: 6px;">{{birth_date_words}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>6.</b></td><td style="border: 1px solid black;padding-left: 6px;">Nationality</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>7.</b></td><td style="border: 1px solid black;padding-left: 6px;">Category (SC/ST/OBC)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>8.</b></td><td style="border: 1px solid black;padding-left: 6px;">First Admission Date & Class</td><td style="border: 1px solid black;padding-left: 6px;">{{admission_date}} & {{class_name}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>9.</b></td><td style="border: 1px solid black;padding-left: 6px;">Last Class Studied</td><td style="border: 1px solid black;padding-left: 6px;">{{last_class}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>10.</b></td><td style="border: 1px solid black;padding-left: 6px;">Last Examination with Result</td><td style="border: 1px solid black;padding-left: 6px;">{{last_exam_name}} {{last_result}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>11.</b></td><td style="border: 1px solid black;padding-left: 6px;">Failed (if yes, once/twice)</td><td style="border: 1px solid black;padding-left: 6px;">{{fails}}</td></tr>
			<tr>
				<td style="border: 1px solid black;text-align: center;"><b>12.</b></td>
				<td style="border: 1px solid black;padding-left: 6px;">Subjects Studied</td>
				<td style="border: 1px solid black;padding-left: 6px;"> {{subject}}</td>
			</tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>13.</b></td><td style="border: 1px solid black;padding-left: 6px;">Qualified for Higher Class</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>14.</b></td><td style="border: 1px solid black;padding-left: 6px;">Fee Paid Up To</td><td style="border: 1px solid black;padding-left: 6px;">{{fees_pay}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>15.</b></td><td style="border: 1px solid black;padding-left: 6px;">Fee Concession (if any)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>16.</b></td><td style="border: 1px solid black;padding-left: 6px;">Working Days in Session</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>17.</b></td><td style="border: 1px solid black;padding-left: 6px;">Days Present</td><td style="border: 1px solid black;padding-left: 6px;">{{total_present}}</td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>18.</b></td><td style="border: 1px solid black;padding-left: 6px;">NCC/Scout/Guide (details)</td><td style="border: 1px solid black;padding-left: 6px;"></td></tr>
			<tr><td style="border: 1px solid black;text-align: center;"><b>19.</b></td><td style="border: 1px solid black;padding-left: 6px;">Extracurricular Activities & Achievements</td><td style="padding-left: 6px;"></td></tr>
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
		</div>
		',
					// ----------------- Issue Book  Mail. ---------------------//
					'mjschool_issue_book_title'                    => 'New book has been issue to you.',
					'mjschool_issue_book_mailcontent'              => '
		Dear {{student_name}},
		New book {{book_name}} has been issue to you.
		Issue Date : {{issue_date}}
		Return Date : {{return_date}}
		Regards From
		{{school_name}}',
		);
		
		return $options;
	}
	add_action( 'admin_init', 'mjschool_general_setting' );
	/**
	 *
	 * This function retrieves default plugin options from `mjschool_option()`
	 * and adds them to the WordPress database using `add_option()` during admin initialization.
	 *
	 * @since 1.0.0
	 */
	function mjschool_general_setting() {
		$options = mjschool_option();
		foreach ( $options as $key => $val ) {
			add_option( $key, $val );
		}
	}
	/**
	 *
	 * This function defines all the admin page slugs used in the MJ School Management plugin.
	 * These slugs can be used to enqueue scripts, styles, or perform page-specific logic.
	 *
	 * @since 1.0.0
	 *
	 * @return array List of all MJ School plugin page slugs.
	 */
	function mjschool_call_script_page() {
		// Define all admin page slugs.
		$page_array = array(
			'mjschool',
			'mjschool_admission',
			'mjschool_setup',
			'mjschool_student',
			'mjschool_student_homewrok',
			'mjschool_teacher',
			'mjschool_parent',
			'mjschool_Subject',
			'mjschool_class',
			'mjschool_route',
			'mjschool_custom_class',
			'mjschool_class_room',
			'mjschool_attendence',
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
		return $page_array;
	}
	/**
	 * Enqueues all required CSS and JS files for the MJ School admin pages.
	 *
	 * This function checks the current admin page and conditionally loads
	 * scripts and styles required for that page. It supports multiple
	 * plugins features like data tables, full calendar, chart JS, time pickers,
	 * validation, RTL support, media uploads, and more.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current admin page hook.
	 */
	function mjschool_change_adminbar_css( $hook ) {
		$school_type = get_option( 'mjschool_custom_class' );
		$current_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
		$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : '';
		$page_array   = mjschool_call_script_page();
		if ( in_array( $current_page, $page_array ) ) {
			if ( $current_page === 'mjschool' ) {
				
				wp_enqueue_style( 'fullcalendar', plugins_url( '/assets/css/third-party-css/fullcalendar.min.css', __FILE__ ) );
			}
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
    		wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_media();
			
			wp_enqueue_style( 'datatable', plugins_url( '/assets/css/third-party-css/third-party-css/dataTables.min.css', __FILE__), array( 'bootstrap-main' ), '', 'all' );
			wp_enqueue_style( 'jquery-datatable', plugins_url( '/assets/css/third-party-css/jquery.dataTables.min.css', __FILE__ ) );
			// wp_enqueue_style( 'mjschool-dynamic', plugins_url( '/assets/css/mjschool-dynamic-css.php', __FILE__ ) );
			wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-newversion', plugins_url( '/assets/css/mjschool-new-version.css', __FILE__ ) );
			if (is_rtl( ) ) {
				// Load RTL CSS.
				wp_enqueue_style( 'mjschool-rtl', plugins_url( '/assets/css/mjschool-new-design-rtl.css', __FILE__ ) );
				wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ) );
			}
			wp_enqueue_style( 'mjschool-dashboard', plugins_url( '/assets/css/mjschool-dashboard.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-popup', plugins_url( '/assets/css/mjschool-popup.css', __FILE__ ) );
			wp_enqueue_style( 'dataTables-responsive', plugins_url( '/assets/css/third-party-css/dataTables.responsive.css', __FILE__ ) );
			wp_enqueue_style( 'bootstrap-multiselect', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap-multiselect.css', __FILE__ ) );
			wp_enqueue_style( 'bootstrap-timepicker', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap-timepicker.min.css', __FILE__ ) );
			wp_enqueue_style( 'sweetalert2-css', plugins_url( '/lib/sweetalert2/sweetalert2.min.css', __FILE__ ) );
			// poppins font family css.
			wp_enqueue_style( 'mjschool-poppins-font-family', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
			// End  poppins font family css.
			// new design css. //
			wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-responsive-new-design', plugins_url( '/assets/css/mjschool-responsive-new-design.css', __FILE__ ) );
			wp_enqueue_style( 'roboto-fontfamily', plugins_url( '/assets/css/mjschool-roboto-font.css', __FILE__ ) );
			// End new design css. //
			// chart js & css.
			wp_enqueue_style( 'chart', plugins_url( '/assets/css/third-party-css/chart.min.css', __FILE__ ) );
			wp_enqueue_script( 'chart-loder', plugins_url( '/assets/js/third-party-js/chart-loder.js', __FILE__), [], '', true);
			wp_enqueue_script( 'loader', plugins_url( '/assets/js/third-party-js/loader.min.js', __FILE__ ) );
			wp_enqueue_script( 'html5-qrcode', plugins_url( '/lib/html5-qrcode/html5-qrcode.min.js', __FILE__ ) );
			wp_enqueue_script( 'sweetalert2-js', plugins_url( '/lib/sweetalert2/sweetalert2.all.min.js', __FILE__ ) );
			// chart js & css.
			wp_enqueue_script( 'jquery-timeago', plugins_url( '/assets/js/third-party-js/jquery.timeago.min.js', __FILE__ ) );
			wp_enqueue_style( 'mjschool-open-sans-fonts', plugins_url( '/assets/css/mjschool-open-sans-fonts.css', __FILE__ ) );
			wp_enqueue_script( 'datatable-editor', plugins_url( '/assets/js/third-party-js/dataTables.editor.min.js', __FILE__ ) );
			wp_enqueue_script( 'mjschool-customfield', plugins_url( 'assets/js/mjschool-customfield.js', __FILE__), array(), '1.0.0', true);
			wp_enqueue_script( 'moment' ); 
			/*--------Full calendar multilanguage.---------*/
			$lancode = get_locale();
			$code = substr( $lancode, 0, 2 );
			if ( isset( $_REQUEST['tab'] ) ) {
				if ( $_REQUEST['tab'] !== 'view_all_message' && $_REQUEST['tab'] !== 'view_all_message_reply' ) {
					wp_enqueue_script( 'datatables', plugins_url( '/assets/js/third-party-js/datatables.min.js', __FILE__), array( 'jquery' ), true);
					wp_enqueue_script( 'jquery-datatable', plugins_url( '/assets/js/third-party-js/jquery.dataTables.min.js', __FILE__), array( 'jquery' ), true);
				}
			} else {
				wp_enqueue_script( 'jquery-datatable', plugins_url( '/assets/js/third-party-js/jquery.dataTables.min.js', __FILE__), array( 'jquery' ), true);
			}
			if ( isset( $_REQUEST['tab'] ) ) {
				if ( $current_tab === 'student_information_report' || $current_tab === 'student_attendance_report' || $current_tab === 'finance_report' ) {
					wp_enqueue_style( 'searchBuilder-dataTables', plugins_url( '/assets/css/third-party-css/searchBuilder.dataTables.min.css', __FILE__ ) );
					wp_enqueue_script( 'dataTables-searchBuilder', plugins_url( '/assets/js/third-party-js/dataTables.searchBuilder.min.js', __FILE__ ) );
					wp_enqueue_style( 'searchBuilder-bootstrap4', plugins_url( '/assets/css/third-party-css/searchBuilder.bootstrap4.min.css', __FILE__ ) );
				}
			}

			$lancode = get_locale();
			$code = substr( $lancode, 0, 2 );
			if ( $current_page === 'mjschool' )
			{
				wp_enqueue_script( 'fullcalendar', plugins_url( '/assets/js/third-party-js/fullcalendar.min.js', __FILE__), array( 'jquery' ), '', true);
				wp_enqueue_script( 'calendar-lang', plugins_url( '/assets/js/calendar-lang/' . $code . '.js', __FILE__), array( 'jquery' ), '', true);
			}
			wp_enqueue_script( 'datatable-buttons', plugins_url( '/assets/js/third-party-js/dataTables.buttons.min.js', __FILE__), '', true);
			wp_enqueue_script( 'datatable-button-html', plugins_url( '/assets/js/third-party-js/buttons.html5.min.js', __FILE__ ) );
			wp_enqueue_script( 'datatable-button-print', plugins_url( 'assets/js/third-party-js/buttons.print.min.js', __FILE__), array( 'jquery' ), '', true );
			wp_enqueue_script( 'pdfmake', plugins_url( '/assets/js/third-party-js/pdfmake.min.js', __FILE__), array( 'jquery' ), '', true);
			wp_enqueue_script( 'buttons-colVis', plugins_url( '/assets/js/third-party-js/buttons.colVis.min.js', __FILE__), array( 'jquery' ), '', true);
			wp_enqueue_script( 'icheckjs', plugins_url( '/assets/js/third-party-js/icheck.min.js', __FILE__), array( 'jquery' ), '', true);
			wp_enqueue_script( 'popper', plugins_url( '/assets/js/third-party-js/popper.min.js', __FILE__ ) );
			if ( $current_page === 'mjschool_result' )
			{
				wp_enqueue_script( 'mjschool-customfield', plugins_url( '/assets/js/pages/marks.js', __FILE__ ) );
			}
			wp_enqueue_script( 'bootstrap-multiselect', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap-multiselect.min.js', __FILE__), array( 'jquery' ), '', true);
			// Print and PDF.
			wp_enqueue_script( 'datatables-buttons-print', plugins_url( '/assets/js/third-party-js/datatables-buttons-print.min.js', __FILE__), array( 'jquery' ), '', true);
			wp_enqueue_script( 'mjschool-popup', plugins_url( '/assets/js/mjschool-popup.js', __FILE__ ) );
			wp_enqueue_style( 'mjschool-frontend-calendar', plugins_url( '/assets/css/mjschool-frontend-calendar.css', __FILE__ ) );
			wp_localize_script( 'mjschool-popup', 'mjschool', array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mjschool_ajax_nonce' ),
			) );
			//Enqueue js files-wise.
			$document_option = get_option( 'mjschool_upload_document_type' );
			$document_type = explode( ', ', $document_option );
			$document_type_json = $document_type;
			$document_size = get_option( 'mjschool_upload_document_size' );
			$datatable_nonce = wp_create_nonce('mjschool_student_list_nonce');
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = mjschool_get_module_name_for_custom_field($current_page);
			$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
			$custom_columns = array();
			if ( ! empty( $user_custom_field ) ) {
				foreach ( $user_custom_field as $custom_field ) {
					if ( $custom_field->show_in_table === '1' ) {
						$custom_columns[] = true; // sortable
					}
				}
			}
			$class_name      = mjschool_get_all_class_array();
			$class_name_list = array_map(
				function ( $s ) {
					return trim( $s->class_name ); // Trim each class name.
				},
				$class_name
			);
			if(isset($_REQUEST['exam_id'])){
				$exam_data = mjschool_get_exam_by_id( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) ) );
			}
			$extra_student_data = array();
			$sb_id = null;
			
			$mix_data = array(
				'select_days'                    => esc_html__('Select Days', 'mjschool'),
				'select_teacher'                 => esc_html__('Select Teacher', 'mjschool'),
				'select_all'                     => esc_html__('Select all', 'mjschool'),
				'select_class'                   => esc_html__('Select Class', 'mjschool'),
				'select_user'                    => esc_html__('Select Users', 'mjschool'),
				'select_tax'                     => esc_html__('Select Tax', 'mjschool'),
				'select_book'                    => esc_html__('Select Book', 'mjschool'),
				'all_selected'                   => esc_html__('All Selected', 'mjschool'),
				'csv_text'                       => esc_html__('CSV', 'mjschool'),
				'print_text'                     => esc_html__('PRINT', 'mjschool'),
				'admission_report_text'          => esc_html__('Admission Report', 'mjschool'),
				'attendance_report_text'         => esc_html__('Attendance Report', 'mjschool'),
				'fees_payment_report_text'   	 => esc_html__('Fees Payment Report', 'mjschool'),
				'leave_report_text'              => esc_html__('Leave Report', 'mjschool'),
				'guardian_report_text'           => esc_html__('Guardian Report', 'mjschool'),
				'student_report_text'            => esc_html__('Student Report', 'mjschool'),
				'class_name_list'                => $class_name_list,
				'select_fees_type'               => esc_html__('Select Fees Type', 'mjschool'),
				'expense_amount_label'           => esc_html__( "Expense Amount", "mjschool" ),
    			'expense_entry_label'            => esc_html__( "Expense Entry Label", "mjschool" ),
				'income_amount_label'            => esc_html__( "Income Amount", "mjschool" ),
    			'income_entry_label'             => esc_html__( "Income Entry Label", "mjschool" ),
    			'audit_trail_report_text'        => esc_html__( "Audit Trail Report", "mjschool" ),
    			'class_section_report_text'      => esc_html__( "Class & Section Report", "mjschool" ),
    			'sibling_report_text'            => esc_html__( "Sibling Report", "mjschool" ),
    			'income_report_text'             => esc_html__( "Income Report", "mjschool" ),
    			'expense_report_text'            => esc_html__( "Expense Report", "mjschool" ),
    			'income_expense_report_text'     => esc_html__( "Income Expense Report", "mjschool" ),
    			'student_attendance_report_text' => esc_html__( "Student Attendance Report", "mjschool" ),
    			'subject_text'                   => esc_html__( "Subject", "mjschool" ),
    			'end_time_must_greater_text'     => esc_html__( "End time must be greater than start time", "mjschool" ),
    			'reply_user_alert'               => esc_html__( 'Please select at least one user to reply', 'mjschool' ),
				'delete_icon'                    => esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png" ),
				'datatable_language'             => mjschool_datatable_multi_language(),
				'search_placeholder'             => esc_html__( 'Search...', 'mjschool' ),
				'attachment_text'      			 => esc_html__( 'Attachment', 'mjschool' ),
				'is_school'                      => ($school_type === 'school'),
				'is_university'                  => ($school_type === 'university'),
				'is_add_access'                  => 1,
				'is_edit_access'                 => 1,
				'is_delete_access'               => 1,
				'is_view_access'                 => 1,
				// 'sibling_index'                 => esc_js($sb_id),
				'select_student'                 => esc_html__( 'Select Student', 'mjschool' ),
				'document_type_json'             => $document_type_json,
				'document_size'                  => $document_size,
				'date_format'                    => get_option('mjschool_datepicker_format'),
				'date_format_for_sorting'        => mjschool_return_date_format_for_shorting(),
				'datatable_nonce'       		 => $datatable_nonce,
				'module_columns'        		 => $custom_columns,
				'exam_data_id'        		     => isset($exam_data)?esc_js($exam_data->exam_id):null,
				'calendar_language'              => mjschool_calender_laungage(),
				'inactive_student_text'          => esc_html__( 'Inactive Students', 'mjschool' ),
				'student_text'                   => esc_html__( 'Students', 'mjschool' ),
				'active_student_text'       	 => esc_html__( 'Active Students', 'mjschool' ),
				'parent_text'    			     => esc_html__( 'Parents', 'mjschool' ),
				'teacher_text'    			     => esc_html__( 'Teachers', 'mjschool' ),
				'support_staff_text' 	         => esc_html__( 'Support Staff', 'mjschool' ),
				'paid_text'      				 => esc_html__( 'Paid', 'mjschool' ),
				'unpaid_text'        			 => esc_html__( 'Unpaid', 'mjschool' ),
				'present_text'        			 => esc_html__( 'Present', 'mjschool' ),
				'absent_text'     			     => esc_html__( 'Absent', 'mjschool' ),
				'late_text'       			     => esc_html__( 'Late', 'mjschool' ),
				'cash_text'       			     => esc_html__( 'Cash', 'mjschool' ),
				'cheque_text'     			     => esc_html__( 'Cheque', 'mjschool' ),
				'half_day_text'    			     => esc_html__( 'Half Day', 'mjschool' ),
				'bank_transfer_text'       		 => esc_html__( 'Bank Transfer', 'mjschool' ),
				'paypal_text'    			     => esc_html__( 'PayPal', 'mjschool' ),
				'stripe_text'    			     => esc_html__( 'Stripe', 'mjschool' ),
				'select_document_type_text'      => esc_html__( 'Select Document Type', 'mjschool' ),
				'one_document_alert_text'        => esc_html__( 'Please select at least one document extension.', 'mjschool' ),
				'profile_alert_text'             => esc_html__( 'Please select at least one profile extension.', 'mjschool' ),
				'select_child_alert_text'          => esc_html__( 'This child is already selected. Please choose a different child.', 'mjschool' ),
				'csv_file_alert_text'             => esc_html__( 'Only CSV format is allowed.', 'mjschool' ),
				'permission_alert_text'             => esc_html__( 'You do not have permission to perform this operation.', 'mjschool' ),
				'start_end_date_alert_text'          => esc_html__( 'End Date should be greater than the Start Date.', 'mjschool' ),
			);
			if(isset($_REQUEST['student_id'])) {
				$student_id                = intval(mjschool_decrypt_id(sanitize_text_field(wp_unslash($_REQUEST['student_id']))));
				$class_id                  = get_user_meta( $student_id, 'class_name', true );
				$section_name              = get_user_meta( $student_id, 'class_section', true );
				$extra_student_data = array(
					'student_id'   => esc_js($student_id),
					'class_id'     => esc_js($class_id),
					'section_name' => esc_js($section_name),
				);
				$mix_data = array_merge($mix_data, $extra_student_data);
			}
			if ( $current_page === 'mjschool_class' || $current_page === 'mjschool_route' || $current_page === 'mjschool_Subject' || $current_page === 'mjschool_virtual_classroom' || $current_page === 'mjschool_class_room'){
				wp_enqueue_script( 'mjschool-class', plugins_url( '/assets/js/public-js/mjschool-class.js', __FILE__ ) );
				wp_localize_script('mjschool-class','mjschool_class_data',$mix_data);
			}
			if ( $current_page === 'mjschool_student' || $current_page === 'mjschool_teacher' || $current_page === 'mjschool_supportstaff' || $current_page === 'mjschool_parent'){
				wp_enqueue_script( 'mjschool-users', plugins_url( '/assets/js/public-js/mjschool-users.js', __FILE__ ) );
				wp_localize_script('mjschool-users','mjschool_users_data',$mix_data);
			}
			if ( $current_page === 'mjschool_student_homewrok'){
				wp_enqueue_script( 'mjschool-homework', plugins_url( '/assets/js/public-js/mjschool-homework.js', __FILE__ ) );
				wp_localize_script('mjschool-homework','mjschool_homework_data',$mix_data);
			}
			if ( $current_page === 'mjschool_document'){
				wp_enqueue_script( 'mjschool-document', plugins_url( '/assets/js/public-js/mjschool-document.js', __FILE__ ) );
				wp_localize_script('mjschool-document','mjschool_document_data',$mix_data);
			}
			if ( $current_page === 'mjschool_leave'){
				wp_enqueue_script( 'mjschool-leave', plugins_url( '/assets/js/public-js/mjschool-leave.js', __FILE__ ) );
				wp_localize_script('mjschool-leave','mjschool_leave_data',$mix_data);
			}
			if ( $current_page === 'mjschool_fees_payment' || $current_page === 'mjschool_payment' || $current_page === 'mjschool_tax'){
				wp_enqueue_script( 'mjschool-payment', plugins_url( '/assets/js/public-js/mjschool-payment.js', __FILE__ ) );
				wp_localize_script('mjschool-payment','mjschool_payment_data',$mix_data);
			}
			if ( $current_page === 'mjschool_library'){
				wp_enqueue_script( 'mjschool-library', plugins_url( '/assets/js/public-js/mjschool-library.js', __FILE__ ) );
				wp_localize_script('mjschool-library','mjschool_library_data',$mix_data);
			}
			if ( $current_page === 'mjschool_hostel'){
				wp_enqueue_script( 'mjschool-hostel', plugins_url( '/assets/js/public-js/mjschool-hostel.js', __FILE__ ) );
				wp_localize_script('mjschool-hostel','mjschool_hostel_data',$mix_data);
			}
			if ( $current_page === 'mjschool_transport'){
				wp_enqueue_script( 'mjschool-transport', plugins_url( '/assets/js/public-js/mjschool-transport.js', __FILE__ ) );
				wp_localize_script('mjschool-transport','mjschool_transport_data',$mix_data);
			}
			if ( $current_page === 'mjschool_certificate'){
				wp_enqueue_script( 'mjschool-certificate', plugins_url( '/assets/js/public-js/mjschool-certificate.js', __FILE__ ) );
				wp_localize_script('mjschool-certificate','mjschool_certificate_data',$mix_data);
			}
			if ( $current_page === 'mjschool_advance_report'){
				wp_enqueue_script( 'mjschool-advance-report', plugins_url( '/assets/js/admin-js/mjschool-advance-report.js', __FILE__ ) );
				wp_localize_script('mjschool-advance-report','mjschool_advance_report_data',$mix_data);
			}
			if ( $current_page === 'mjschool_notice' || $current_page === 'mjschool_message' || $current_page === 'mjschool_notification' || $current_page === 'mjschool_event' || $current_page === 'mjschool_holiday'){
				wp_enqueue_script( 'mjschool-notification', plugins_url( '/assets/js/public-js/mjschool-notification.js', __FILE__ ) );
				wp_localize_script('mjschool-notification','mjschool_notification_data',$mix_data);
			}
			if ( $current_page === 'mjschool_report'){
				wp_enqueue_script( 'mjschool-report', plugins_url( '/assets/js/admin-js/mjschool-report.js', __FILE__ ) );
				wp_localize_script('mjschool-report','mjschool_report_data',$mix_data);
			}
			if ( $current_page === 'mjschool_admission'){
				wp_enqueue_script( 'mjschool-admission', plugins_url( '/assets/js/public-js/mjschool-admission.js', __FILE__ ) );
				wp_localize_script('mjschool-admission','mjschool_admission_data',$mix_data);
			}
			if ( $current_page === 'mjschool_exam' || $current_page === 'mjschool_hall' || $current_page === 'mjschool_result' || $current_page === 'mjschool_grade' || $current_page === 'mjschool_Migration'){
				wp_enqueue_script( 'mjschool-student-evaluation', plugins_url( '/assets/js/public-js/mjschool-student-evaluation.js', __FILE__ ) );
				wp_localize_script('mjschool-student-evaluation','mjschool_student_evaluation_data',$mix_data);
			}
			if ( $current_page === 'mjschool_attendence'){
				wp_enqueue_script( 'mjschool-attendance', plugins_url( '/assets/js/public-js/mjschool-attendance.js', __FILE__ ) );
				wp_localize_script('mjschool-attendance','mjschool_attendance_data',$mix_data);
			}
			if ( $current_page === 'mjschool_custom_field' || $current_page === 'mjschool_email_template' || $current_page === 'mjschool_sms_setting' || $current_page === 'mjschool_sms_template' || $current_page === 'mjschool_general_settings'){
				wp_enqueue_script( 'mjschool-general-setting', plugins_url( '/assets/js/public-js/mjschool-general-setting.js', __FILE__ ) );
				wp_localize_script('mjschool-general-setting','mjschool_general_setting_data',$mix_data);
			}
			if ( $current_page === 'mjschool'){
				wp_enqueue_script( 'mjschool-admin-dashboard', plugins_url( '/assets/js/public-js/mjschool-admin-dashboard.js', __FILE__ ) );
				wp_localize_script('mjschool-admin-dashboard','mjschool_dashboard_data',$mix_data);
			}

			wp_enqueue_script( 'mjschool-common', plugins_url( '/assets/js/mjschool-common.js', __FILE__ ) );	
			wp_localize_script('mjschool-common','mjschool_common_data',$mix_data);
			wp_enqueue_script( 'mjschool-ajax-function', plugins_url( '/assets/js/mjschool-ajax-function.js', __FILE__ ) );	
			wp_localize_script('mjschool-ajax-function','mjschool_ajax_function_data',$mix_data);
			wp_enqueue_script( 'mjschool-function', plugins_url( '/assets/js/mjschool-function-file.js', __FILE__ ) );	
			wp_localize_script('mjschool-function','mjschool_function_data',$mix_data);
			//Enqueue js files-wise end.
			wp_enqueue_script( 'mjschool-image-upload', plugins_url( '/assets/js/mjschool-image-upload.js', __FILE__), array( 'jquery' ), '', true);			
			//image upload file alert msg languages translation.
			wp_localize_script(
				'mjschool-image-upload',
				'language_translate1',
				array(
					'allow_file_alert' => esc_attr__( 'Only jpg,jpeg,png File allowed', 'mjschool' ),
				)
			);
			wp_localize_script(
				'mjschool-popup',
				'language_translate2',
				array(
					'edit_record_alert' => esc_attr__( 'Are you sure want to edit this record?', 'mjschool' ),
					'category_alert' => esc_attr__( 'You must fill out the field', 'mjschool' ),
					'class_limit_alert' => esc_attr__( 'Class Limit Is Full.', 'mjschool' ),
					'enter_room_alert' => esc_attr__( 'Please Enter Room Category Name.', 'mjschool' ),
					'enter_value_alert' => esc_attr__( 'Please Enter Value.', 'mjschool' ),
					'delete_record_alert' => esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ),
					'select_hall_alert' => esc_attr__( 'Please Select Exam Hall', 'mjschool' ),
					'one_record_alert' => esc_attr__( 'Please Select Atleast One Student', 'mjschool' ),
					'select_member_alert' => esc_attr__( 'Please select Student', 'mjschool' ),
					'one_record_select_alert' => esc_attr__( 'Please select atleast one record', 'mjschool' ),
					'one_class_select_alert' => esc_attr__( 'Please select atleast one class', 'mjschool' ),
					'one_select_Validation_alert' => esc_attr__( 'Please select atleast one Validation', 'mjschool' ),
					'lower_starting_year_alert' => esc_attr__( 'You can not select year lower then starting year', 'mjschool' ),
					'do_delete_record' => esc_attr__( 'Do you really want to delete this ?', 'mjschool' ),
					'select_one_book_alert' => esc_attr__( 'Please select atleast one book', 'mjschool' ),
					'select_different_student_alert' => esc_attr__( 'Please Select Different Student', 'mjschool' ),
					'select_user_label' => esc_attr__( 'Select Users', 'mjschool' ),
					'select_all_label' => esc_attr__( 'Select all', 'mjschool' ),
					'same_email_alert' => esc_attr__( 'you have used the same email', 'mjschool' ),
					'image_forame_alert' => esc_attr__( "Only '.jpeg','.jpg', '.png', '.bmp' formats are allowed.", "mjschool" ),
					'more_then_exam_date_time' => esc_attr__( "Fail! More than one subject exam date & time same.", "mjschool" ),
					'single_entry_alert' => esc_attr__( "There is only single entry,You can not remove it.", "mjschool" ),
					'one_teacher_alert' => esc_attr__( "Please select atleast one teacher", "mjschool" ),
					'one_assign_room_alert' => esc_attr__( "Please select Student", "mjschool" ),
					'one_message_alert' => esc_attr__( "Please select atleast one message", "mjschool" ),
					'large_file_size_alert' => esc_attr__( "Too large file Size. Only file smaller than 10MB can be uploaded.", "mjschool" ),
					'pdf_alert' => esc_attr__( "Only pdf formate are allowed.", "mjschool" ),
					'starting_year_alert' => esc_attr__( "You Can Not Select Ending Year Lower Than Starting Year", "mjschool" ),
					'one_user_replys_alert' => esc_attr__( "Please select atleast one users to replys", "mjschool" ),
					'csv_alert' => esc_attr__( "Problems with user: we are going to skip", "mjschool" ),
					'select_user' => esc_attr__( "Select Users", "mjschool" ),
					'select_all' => esc_attr__( "Select all", "mjschool" ),
					'mail_reminder' => esc_attr__( "Are you sure you want to send a mail reminder?", "mjschool" ),
					'account_alert_1' => esc_attr__( "Only jpeg,jpg,png and bmp formate are allowed.", "mjschool" ),
					'account_alert_2' => esc_attr__( "formate are not allowed.", "mjschool" ),
					'exam_hallCapacity_1' => esc_attr__( "Exam Hall Capacity", "mjschool" ),
					'exam_hallCapacity_2' => esc_attr__( "Out Of", "mjschool" ),
					'exam_hallCapacity_3' => esc_attr__( "Students.", "mjschool" )
				)
			);
			wp_enqueue_style( 'mjschool-white', plugins_url( '/assets/css/mjschool-white.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-schoolmgt', plugins_url( '/assets/css/mjschool-school-mgt.min.css', __FILE__ ) );
			wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ) );
			wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
			if (is_rtl( ) ) {
				wp_enqueue_style( 'bootstrap-rtl', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css', __FILE__ ) );
				wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ) );
				wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
			}
			wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ) );
			wp_enqueue_style( 'buttons-dataTables', plugins_url( '/assets/css/third-party-css/buttons.dataTables.min.css', __FILE__ ) );
			wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ) );
			//metrial design js.
			wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ) );
			//End metrial design js.
			wp_enqueue_script( 'modernizr', plugins_url( '/assets/js/third-party-js/modernizr.min.js', __FILE__ ) );
			wp_enqueue_script( 'jquery-waypoints', plugins_url( '/assets/js/third-party-js/jquery.waypoints.min.js', __FILE__ ) );
			wp_enqueue_script( 'jquery-counterup', plugins_url( '/assets/js/third-party-js/jquery.counterup.min.js', __FILE__ ) );
			//validation style And Script.
			//validation lib.
			wp_enqueue_style( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ) );
			wp_enqueue_script( 'font-awesome-all', plugins_url( '/assets/js/third-party-js/font-awesome.all.min.js', __FILE__ ) );
			//------- time picker js. -------//
			wp_enqueue_script( 'mdtimepicker', plugins_url( '/assets/js/third-party-js/mdtimepicker.min.js', __FILE__ ) );
			wp_enqueue_style( 'mdtimepicker', plugins_url( '/assets/css/third-party-css/mdtimepicker.min.css', __FILE__ ) );
			//------- time picker js. -------//
			wp_register_script( 'jquery-validationEngine-' . $code . '', plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $code . '.js', __FILE__), array( 'jquery' ) );
			wp_enqueue_script( 'jquery-validationEngine-' . $code . '' );
			wp_register_script( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__), array( 'jquery' ), '', true );
			wp_enqueue_script( 'jquery-validationEngine' );
			//------ MULTIPLE SELECT ITEM JS. -------//
			wp_enqueue_script( 'select2' );
    		wp_enqueue_style( 'select2' );
			//------ END MULTIPLE SELECT ITEM JS. ------//
			if ( $current_page === 'mjschool_report' || $current_page === 'mjschool' ) {
				wp_enqueue_script( 'chart-umd', plugins_url( '/assets/js/third-party-js/chart.umd.min.js', __FILE__), array( 'jquery' ), '', true);
			}
			wp_enqueue_script( 'mjschool-custom-obj', plugins_url( '/assets/js/mjschool-custom-confilict-obj.js', __FILE__), array( 'jquery' ), '', false);
		}
	}
	if ( isset( $_REQUEST['page'] ) ) {
		add_action( 'admin_enqueue_scripts', 'mjschool_change_adminbar_css' );
	}

/**
 *
 * This function hooks into the WordPress gettext filter when
 * on the MJ School plugin page. It allows you to customize
 * the text of the "Insert into Post" button in Thickbox/Media Uploader.
 *
 * @since 1.0.0
 */
function mjschool_upload_image() {
	global $mjschool_pagenow;
	$current_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $current_page === 'mjschool' ) {
			// Now we'll replace the 'Insert into Post Button' inside Thickbox.
			add_filter( 'gettext', 'mjschool_replace_thickbox_text', 1, 3 );
		}
	}
}
add_action( 'admin_init', 'mjschool_upload_image' );
/**
 * Replaces the default "Insert into Post" button text in Thickbox/Media Uploader.
 *
 * This function hooks into the 'gettext' filter to change the text
 * of the "Insert into Post" button to "Upload Image" when the
 * user is on the MJ School settings page.
 *
 * @since 1.0.0
 *
 * @param string $translated_text The translated text (may have been translated by other filters).
 * @param string $text            The original text.
 * @param string $domain          The text domain.
 * @return string Modified text if conditions match; otherwise original translated text.
 */
function mjschool_replace_thickbox_text( $translated_text, $text, $domain ) {
	// Check if the current translatable text is exactly "Insert into Post".
	if ( 'Insert into Post' === $text ) {
		$referer = strpos( wp_get_referer(), 'wptuts-settings' );
		if ( $referer != '' ) {
			return esc_html__( 'Upload Image', 'mjschool' );
		}
	}
	return $translated_text;
}
/**
 * Loads the plugin's translation files for localization.
 *
 * This function enables the plugin to support multiple languages
 * by loading the text domain 'mjschool' from the /languages/ directory.
 *
 * @since 1.0.0
 */
function mjschool_domain_load() {
	load_plugin_textdomain( 'mjschool', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
/**
 * Creates a Login Page post with the [smgt_login] shortcode.
 *
 * This function checks if the plugin option 'mjschool_login_page' exists.
 * If it does not exist, it creates a new WordPress page titled
 * "School Management Login Page" with the [smgt_login] shortcode,
 * and stores the page ID in the option for later use.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'mjschool_domain_load' );
function mjschool_install_login_page() {
	if ( ! get_option( 'mjschool_login_page' ) ) {
		$curr_page    = array(
			'post_title'     => esc_attr__( 'School Management Login Page', 'mjschool' ),
			'post_content'   => '[smgt_login]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_category'  => array(1),
			'post_parent'    => 0,
		);
		$curr_created = wp_insert_post( $curr_page );
		update_option( 'mjschool_login_page', $curr_created );
	}
}
/**
 * Creates a Student Registration Page post with the [smgt_student_registration] shortcode.
 *
 * This function checks if the plugin option 'mjschool_install_student_registration_page' exists.
 * If it does not exist, it creates a new WordPress page titled
 * "Student Registration" with the [smgt_student_registration] shortcode,
 * and stores the page ID in the option for later use.
 *
 * @since 1.0.0
 */
function mjschool_install_student_registration_page() {
	if ( ! get_option( 'mjschool_install_student_registration_page' ) ) {
		$curr_page    = array(
			'post_title'     => esc_attr__( 'Student Registration', 'mjschool' ),
			'post_content'   => '[smgt_student_registration]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_category'  => array(1),
			'post_parent'    => 0,
		);
		$curr_created = wp_insert_post( $curr_page );
		update_option( 'mjschool_install_student_registration_page', $curr_created );
	}
}
/**
 * Loads the frontend dashboard template or hooks into login authentication.
 *
 * This function checks for specific request parameters to determine
 * whether to load the frontend dashboard template or modify the login process.
 *
 * @since 1.0.0
 */
function mjschool_user_dashboard() {
	if ( isset( $_REQUEST['dashboard'] ) ) {
		require_once MJSCHOOL_INCLUDES_DIR . '/mjschool-frontend-template.php';
		die();
	}
	if ( isset( $_REQUEST['mjschool_login'] ) ) {
		add_action( 'authenticate', 'mjschool_pu_blank_login' );
	}
}
/**
 * Enqueues all required frontend scripts and styles for the MJ School User dashboard.
 *
 * This function only loads assets if the request parameter 'dashboard' equals 'mjschool_user'.
 *
 * @since 1.0.0
 */
function mjschool_enqueue_front_assets() {
	$current_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
	$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : '';
	if ( isset( $_REQUEST['dashboard'] ) && sanitize_text_field(wp_unslash($_REQUEST['dashboard'])) === 'mjschool_user' ) 
	{
		wp_register_script( 'popup-front', plugins_url( 'assets/js/mjschool-popup.js', __FILE__), array( 'jquery' ) );
		wp_enqueue_script( 'popup-front' );
		wp_localize_script( 'popup-front', 'mjschool', array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mjschool_ajax_nonce' ),
		) );
		wp_localize_script(
			'popup-front',
			'language_translate2',
			array(
				'edit_record_alert'              => esc_attr__( 'Are you sure want to edit this record?', 'mjschool' ),
				'category_alert'                 => esc_attr__( 'You must fill out the field!', 'mjschool' ),
				'class_limit_alert'              => esc_attr__( 'Class Limit Is Full.', 'mjschool' ),
				'enter_room_alert'               => esc_attr__( 'Please Enter Room Category Name.', 'mjschool' ),
				'enter_value_alert'              => esc_attr__( 'Please Enter Value.', 'mjschool' ),
				'delete_record_alert'            => esc_attr__( 'Are you sure you want to delete this record?', 'mjschool' ),
				'select_hall_alert'              => esc_attr__( 'Please Select Exam Hall', 'mjschool' ),
				'one_record_alert'               => esc_attr__( 'Please Checked Atleast One Student', 'mjschool' ),
				'select_member_alert'            => esc_attr__( 'Please select Student', 'mjschool' ),
				// New Updated alert message. //
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
				'one_assign_room_alert'         => esc_attr__( 'Please select Student', 'mjschool' ),
				'one_message_alert'              => esc_attr__( 'Please select atleast one message', 'mjschool' ),
				'large_file_size_alert'          => esc_attr__( 'Too large file Size. Only file smaller than 10MB can be uploaded.', 'mjschool' ),
				'pdf_alert'                      => esc_attr__( 'Only pdf formate are allowed.', 'mjschool' ),
				'starting_year_alert'            => esc_attr__( 'You can not select year lower then starting year', 'mjschool' ),
				'one_user_replys_alert'          => esc_attr__( 'Please select atleast one users to replys', 'mjschool' ),
				'csv_alert'                      => esc_attr__( 'Problems with user: we are going to skip', 'mjschool' ),
				'mail_reminder'                  => esc_attr__( 'Are you sure you want to send a mail reminder?', 'mjschool' ),
				'account_alert_1'                => esc_attr__( 'Only jpeg,jpg,png and bmp formate are allowed.', 'mjschool' ),
				'account_alert_2'                => esc_attr__( 'formate are not allowed.', 'mjschool' ),
				'exam_hallCapacity_1'            => esc_attr__( 'Exam Hall Capacity', 'mjschool' ),
				'exam_hallCapacity_2'            => esc_attr__( 'Out Of', 'mjschool' ),
				'exam_hallCapacity_3'            => esc_attr__( 'Students.', 'mjschool' ),
			)
		);
		
		// STYLES.	
		wp_enqueue_style( 'datatable',plugins_url( '/assets/css/third-party-css/dataTables.min.css', __FILE__),array(),'','all' );
		wp_enqueue_style( 'jquery-datatable',plugins_url( '/assets/css/third-party-css/jquery.dataTables.min.css', __FILE__),array(),'','all' );
		wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap-multiselect', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap-multiselect.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-popup', plugins_url( '/assets/css/mjschool-popup.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-newversion', plugins_url( '/assets/css/mjschool-new-version.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap-timepicker', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap-timepicker.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-dashboard', plugins_url( '/assets/css/mjschool-dashboard.css', __FILE__ ) );
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-white', plugins_url( '/assets/css/mjschool-white.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-schoolmgt', plugins_url( '/assets/css/mjschool-school-mgt.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-poppins-font-family', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-responsive-new-design', plugins_url( '/assets/css/mjschool-responsive-new-design.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-frontend-calendar', plugins_url( '/assets/css/mjschool-frontend-calendar.css', __FILE__ ) );
		wp_enqueue_style( 'font-awesome', plugins_url( '/assets/css/third-party-css/font-awesome.min.css', __FILE__ ) );
		if ( is_rtl() ) 
		{
			wp_enqueue_style( 'bootstrap-rtl', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
			wp_enqueue_style( 'mjschool-rtl', plugins_url( '/assets/css/mjschool-new-design-rtl.css', __FILE__ ) );
		}
		wp_enqueue_style( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ) );
		wp_enqueue_style( 'dataTables-responsive', plugins_url( '/assets/css/third-party-css/dataTables.responsive.css', __FILE__ ) );
		wp_enqueue_script( 'select2' );
    	wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'mdtimepicker', plugins_url( '/assets/css/third-party-css/mdtimepicker.min.css', __FILE__ ) );
		wp_enqueue_style( 'chart', plugins_url( '/assets/css/third-party-css/chart.min.css', __FILE__ ) );
		// SCRIPTS
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_script( 'datatables', plugins_url( '/assets/js/third-party-js/datatables.min.js', __FILE__), array( 'jquery' ), true);
		wp_enqueue_script( 'datatable-editor', plugins_url( '/assets/js/third-party-js/dataTables.editor.min.js', __FILE__ ) );
		wp_enqueue_script( 'jquery-datatable', plugins_url( '/assets/js/third-party-js/jquery.dataTables.min.js', __FILE__), array( 'jquery' ), true);
		wp_enqueue_script( 'datatable-button', plugins_url( '/assets/js/third-party-js/dataTables.buttons.min.js', __FILE__), '', true);
		wp_enqueue_script( 'datatable-button-print', plugins_url( 'assets/js/third-party-js/buttons.print.min.js', __FILE__), array( 'jquery' ), '', true );
		wp_enqueue_script( 'icheckjs', plugins_url( '/assets/js/third-party-js/icheck.min.js', __FILE__), array( 'jquery' ), '', true);
		$lancode = get_locale();
		$code    = substr( $lancode, 0, 2 );
		wp_enqueue_script( 'jquery-validationEngine-' . $code . '', plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $code . '.js', __FILE__), array( 'jquery' ) );
		wp_enqueue_script( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__), array( 'jquery' ) );
		wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ) );
		wp_enqueue_script( 'chart-custom', plugins_url( '/assets/js/third-party-js/chart.umd.min.js', __FILE__), array( 'jquery' ), '', true);
		wp_enqueue_script( 'loader', plugins_url( '/assets/js/third-party-js/loader.min.js', __FILE__ ) );
		wp_enqueue_script( 'popper', plugins_url( '/assets/js/third-party-js/popper.min.js', __FILE__ ) );
		wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ) );
		wp_enqueue_script( 'multiselect', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap-multiselect.min.js', __FILE__), array( 'jquery' ), '', true);
		wp_enqueue_script( 'timeago', plugins_url( '/assets/js/third-party-js/jquery.timeago.min.js', __FILE__ ) );
		wp_enqueue_script( 'moment' ); 
		// wp_enqueue_script( 'mjschool-admission', plugins_url( '/assets/js/admin-js/mjschool-admission.js', __FILE__ ) );
		wp_enqueue_script( 'fullcalendar', plugins_url( '/assets/js/third-party-js/fullcalendar.min.js', __FILE__), array( 'jquery' ), '', true);
		wp_enqueue_script( 'mdtimepicker', plugins_url( '/assets/js/third-party-js/mdtimepicker.min.js', __FILE__ ) );
		wp_enqueue_script( 'calendar-lang', plugins_url( '/assets/js/calendar-lang/' . $code . '.js', __FILE__), array( 'jquery' ), '', true);
		wp_enqueue_script( 'datatables-buttons-print', plugins_url( '/assets/js/third-party-js/datatables-buttons-print.min.js', __FILE__), array( 'jquery' ), '', true);
		wp_enqueue_script( 'html5-qrcode', plugins_url( '/lib/html5-qrcode/html5-qrcode.min.js', __FILE__ ) );
		$document_option = get_option( 'mjschool_upload_document_type' );
		$school_type = get_option( 'mjschool_custom_class' );
		$cust_class_room = get_option( 'mjschool_class_room' );
		$document_type = explode( ', ', $document_option );
		$document_type_json = $document_type;
		$document_size = get_option( 'mjschool_upload_document_size' );
		$datatable_nonce = wp_create_nonce('mjschool_student_list_nonce');
		$user_access       = mjschool_get_user_role_wise_access_right_array();
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = mjschool_get_module_name_for_custom_field($current_page);
		$mjschool_role_name = mjschool_get_user_role( get_current_user_id() );
		$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		$custom_columns = array();
		if ( ! empty( $user_custom_field ) ) {
			foreach ( $user_custom_field as $custom_field ) {
				if ( $custom_field->show_in_table == '1' ) {
					$custom_columns[] = true; // sortable.
				}
			}
		}
		$class_name      = mjschool_get_all_class_array();
		$class_name_list = array_map(
			function ( $s ) {
				return trim( $s->class_name ); // Trim each class name.
			},
			$class_name
		);
		if(isset($_REQUEST['exam_id'])){
			$exam_data = mjschool_get_exam_by_id( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) ) );
		}
		$current_user_id 		  = get_current_user_id();
		$student_id                = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
		$class_id                  = get_user_meta( $student_id, 'class_name', true );
		$section_name              = get_user_meta( $student_id, 'class_section', true );
		$mix_data = array(
			'select_days'                    => esc_html__('Select Days', 'mjschool'),
			'select_teacher'                 => esc_html__('Select Teacher', 'mjschool'),
			'select_all'                     => esc_html__('Select all', 'mjschool'),
			'select_class'                   => esc_html__('Select Class', 'mjschool'),
			'select_subject'                   => esc_html__('Select Subject', 'mjschool'),
			'select_user'                    => esc_html__('Select Users', 'mjschool'),
			'select_tax'                     => esc_html__('Select Tax', 'mjschool'),
			'select_book'                    => esc_html__('Select Book', 'mjschool'),
			'all_selected'                   => esc_html__('All Selected', 'mjschool'),
			'csv_text'                       => esc_html__('CSV', 'mjschool'),
			'print_text'                     => esc_html__('PRINT', 'mjschool'),
			'admission_report_text'          => esc_html__('Admission Report', 'mjschool'),
			'attendance_report_text'         => esc_html__('Attendance Report', 'mjschool'),
			'fees_payment_report_text'   	 => esc_html__('Fees Payment Report', 'mjschool'),
			'leave_report_text'              => esc_html__('Leave Report', 'mjschool'),
			'guardian_report_text'           => esc_html__('Guardian Report', 'mjschool'),
			'student_report_text'            => esc_html__('Student Report', 'mjschool'),
			'class_name_list'                => $class_name_list,
			'select_fees_type'               => esc_html__('Select Fees Type', 'mjschool'),
			'expense_amount_label'           => esc_html__( "Expense Amount", "mjschool" ),
			'expense_entry_label'            => esc_html__( "Expense Entry Label", "mjschool" ),
			'income_amount_label'            => esc_html__( "Income Amount", "mjschool" ),
			'income_entry_label'             => esc_html__( "Income Entry Label", "mjschool" ),
			'audit_trail_report_text'        => esc_html__( "Audit Trail Report", "mjschool" ),
			'class_section_report_text'      => esc_html__( "Class & Section Report", "mjschool" ),
			'sibling_report_text'            => esc_html__( "Sibling Report", "mjschool" ),
			'income_report_text'             => esc_html__( "Income Report", "mjschool" ),
			'expense_report_text'            => esc_html__( "Expense Report", "mjschool" ),
			'income_expense_report_text'     => esc_html__( "Income Expense Report", "mjschool" ),
			'student_attendance_report_text' => esc_html__( "Student Attendance Report", "mjschool" ),
			'subject_text'                   => esc_html__( "Subject", "mjschool" ),
			'end_time_must_greater_text'     => esc_html__( "End time must be greater than start time", "mjschool" ),
			'reply_user_alert'               => esc_html__( 'Please select at least one user to reply', 'mjschool' ),
			'delete_icon'                    => esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png" ),
			'datatable_language'             => mjschool_datatable_multi_language(),
			'search_placeholder'             => esc_html__( 'Search...', 'mjschool' ),
			'attachment_text'      			 => esc_html__( 'Attachment', 'mjschool' ),
			'is_school'                      => ($school_type === 'school'),
			'is_supportstaff'                => ($mjschool_role_name === 'supportstaff'),
			'is_teacher'                     => ($mjschool_role_name === 'teacher'),
			'is_student'                     => ($mjschool_role_name === 'student'),
			'is_parent'                      => ($mjschool_role_name === 'parent'),
			'is_university'                  => ($school_type === 'university'),
			'is_cust_class_room'             => ($cust_class_room === 1),
			'is_add_access'                  => ($user_access['add'] === '1'),
			'is_edit_access'                 => ($user_access['edit'] === '1'),
			'is_delete_access'               => ($user_access['delete'] === '1'),
			'is_view_access'                 => ($user_access['view'] === '1'),
			'select_student'                 => esc_html__( 'Select Student', 'mjschool' ),
			'document_type_json'             => $document_type_json,
			'document_size'                  => $document_size,
			'date_format'                    => get_option('mjschool_datepicker_format'),
			'date_format_for_sorting'        => mjschool_return_date_format_for_shorting(),
			'datatable_nonce'       		 => $datatable_nonce,
			'module_columns'        		 => $custom_columns,
			'current_user_id'        	     => esc_js($current_user_id),
			'exam_data_id'        		     => isset($exam_data)?esc_js($exam_data->exam_id):null,
			'calendar_language'              => mjschool_calender_laungage(),
			'inactive_student_text'          => esc_html__( 'Inactive Students', 'mjschool' ),
			'student_text'                   => esc_html__( 'Students', 'mjschool' ),
			'active_student_text'       	 => esc_html__( 'Active Students', 'mjschool' ),
			'parent_text'    			     => esc_html__( 'Parents', 'mjschool' ),
			'teacher_text'    			     => esc_html__( 'Teachers', 'mjschool' ),
			'support_staff_text' 	         => esc_html__( 'Support Staff', 'mjschool' ),
			'paid_text'      				 => esc_html__( 'Paid', 'mjschool' ),
			'unpaid_text'        			 => esc_html__( 'Unpaid', 'mjschool' ),
			'present_text'        			 => esc_html__( 'Present', 'mjschool' ),
			'absent_text'     			     => esc_html__( 'Absent', 'mjschool' ),
			'late_text'       			     => esc_html__( 'Late', 'mjschool' ),
			'cash_text'       			     => esc_html__( 'Cash', 'mjschool' ),
			'cheque_text'     			     => esc_html__( 'Cheque', 'mjschool' ),
			'half_day_text'    			     => esc_html__( 'Half Day', 'mjschool' ),
			'bank_transfer_text'       		 => esc_html__( 'Bank Transfer', 'mjschool' ),
			'paypal_text'    			     => esc_html__( 'PayPal', 'mjschool' ),
			'stripe_text'    			     => esc_html__( 'Stripe', 'mjschool' ),
			'select_document_type_text'      => esc_html__( 'Select Document Type', 'mjschool' ),
			'one_document_alert_text'        => esc_html__( 'Please select at least one document extension.', 'mjschool' ),
			'profile_alert_text'             => esc_html__( 'Please select at least one profile extension.', 'mjschool' ),
			'subject_file_alert_text'        => esc_html__( 'Only pdf,doc,docx,xls,xlsx,ppt,pptx,gif,png,jpg,jpeg formate are allowed.', 'mjschool' ),
			'not_format_alert_text'          => esc_html__( 'format is not allowed.', 'mjschool' ),
			'front_doc_alert_text'          => esc_html__( 'Sorry, only JPG, pdf, docs., JPEG, PNG And GIF files are allowed.', 'mjschool' ),
			'select_child_alert_text'          => esc_html__( 'This child is already selected. Please choose a different child.', 'mjschool' ),
			'start_end_date_alert_text'          => esc_html__( 'End Date should be greater than the Start Date.', 'mjschool' ),
		);
		if(isset($_REQUEST['student_id'])) {
			$student_id                = intval(mjschool_decrypt_id(sanitize_text_field(wp_unslash($_REQUEST['student_id']))));
			$class_id                  = get_user_meta( $student_id, 'class_name', true );
			$section_name              = get_user_meta( $student_id, 'class_section', true );
			$extra_student_data = array(
				'student_id'   => esc_js($student_id),
				'class_id'     => esc_js($class_id),
				'section_name' => esc_js($section_name),
			);
			$mix_data = array_merge($mix_data, $extra_student_data);
		}
		// Enqueue js file module-wise.
		if($current_page === 'account') {
			wp_enqueue_script('mjschool-account', plugins_url('assets/js/public-js/mjschool-account.js',__FILE__));
		}
		if ( $current_page === 'transport'){
			wp_enqueue_script( 'mjschool-transport', plugins_url( '/assets/js/public-js/mjschool-transport.js', __FILE__ ) );
			wp_localize_script('mjschool-transport','mjschool_transport_data',$mix_data);
		}
		if ( $current_page === 'tax' || $current_page === 'feepayment' || $current_page === 'payment'){
			wp_enqueue_script( 'mjschool-payment', plugins_url( '/assets/js/public-js/mjschool-payment.js', __FILE__ ) );
			wp_localize_script('mjschool-payment','mjschool_payment_data',$mix_data);
		}
		if ( $current_page === 'subject' || $current_page === 'schedule' || $current_page === 'class' || $current_page === 'class_room'){
			wp_enqueue_script( 'mjschool-class', plugins_url( '/assets/js/public-js/mjschool-class.js', __FILE__ ) );
			wp_localize_script('mjschool-class','mjschool_class_data',$mix_data);
		}
		if ( $current_page === 'sms_setting' || $current_page === 'custom_field'){
			wp_enqueue_script( 'mjschool-general-setting', plugins_url( '/assets/js/public-js/mjschool-general-setting.js', __FILE__ ) );
			wp_localize_script('mjschool-general-setting','mjschool_general_setting_data',$mix_data);
		}
		if ( $current_page === 'message' || $current_page === 'notification' || $current_page === 'holiday' || $current_page === 'event'){
			wp_enqueue_script( 'mjschool-notification', plugins_url( '/assets/js/public-js/mjschool-notification.js', __FILE__ ) );
			wp_localize_script('mjschool-notification','mjschool_notification_data',$mix_data);
		}
		if ( $current_page === 'exam' || $current_page === 'exam_hall' || $current_page === 'result' || $current_page === 'grade' || $current_page === 'migration'){
			wp_enqueue_script( 'mjschool-student-evaluation', plugins_url( '/assets/js/public-js/mjschool-student-evaluation.js', __FILE__ ) );
			wp_localize_script('mjschool-student-evaluation','mjschool_student_evaluation_data',$mix_data);
		}
		if ( $current_page === 'library'){
			wp_enqueue_script( 'mjschool-library', plugins_url( '/assets/js/public-js/mjschool-library.js', __FILE__ ) );
			wp_localize_script('mjschool-library','mjschool_library_data',$mix_data);
		}
		if ( $current_page === 'leave'){
			wp_enqueue_script( 'mjschool-leave', plugins_url( '/assets/js/public-js/mjschool-leave.js', __FILE__ ) );
			wp_localize_script('mjschool-leave','mjschool_leave_data',$mix_data);
		}
		if ( $current_page === 'hostel'){
			wp_enqueue_script( 'mjschool-hostel', plugins_url( '/assets/js/public-js/mjschool-hostel.js', __FILE__ ) );
			wp_localize_script('mjschool-hostel','mjschool_hostel_data',$mix_data);
		}
		if ( $current_page === 'homework'){
			wp_enqueue_script('mjschool-homework', plugins_url('/assets/js/public-js/mjschool-homework.js', __FILE__ ));
			wp_localize_script('mjschool-homework','mjschool_homework_data',$mix_data);
		}
		if ( $current_page === 'document'){
			wp_enqueue_script( 'mjschool-document', plugins_url( '/assets/js/public-js/mjschool-document.js', __FILE__ ) );
			wp_localize_script('mjschool-document','mjschool_document_data',$mix_data);
		}
		if ( $current_page === 'certificate'){
			wp_enqueue_script( 'mjschool-certificate', plugins_url( '/assets/js/public-js/mjschool-certificate.js', __FILE__ ) );
			wp_localize_script('mjschool-certificate','mjschool_certificate_data',$mix_data);
		}
		if ( $current_page === 'attendance'){
			wp_enqueue_script( 'mjschool-attendance', plugins_url( '/assets/js/public-js/mjschool-attendance.js', __FILE__ ) );
			wp_localize_script('mjschool-attendance','mjschool_attendance_data',$mix_data);
		}
		if ( $current_page === 'admission'){
			wp_enqueue_script( 'mjschool-admission', plugins_url( '/assets/js/public-js/mjschool-admission.js', __FILE__ ) );
			wp_localize_script('mjschool-admission','mjschool_admission_data',$mix_data);
		}
		if ( $current_page === 'student' || $current_page === 'teacher' || $current_page === 'supportstaff' || $current_page === 'parent'){
			wp_enqueue_script( 'mjschool-users', plugins_url( '/assets/js/public-js/mjschool-users.js', __FILE__ ) );
			wp_localize_script('mjschool-users','mjschool_users_data',$mix_data);
		}
		if ( isset( $_REQUEST['dashboard']) && (sanitize_text_field(wp_unslash($_REQUEST['dashboard'])) === 'mjschool_user')){
			wp_enqueue_script( 'mjschool-admin-dashboard', plugins_url( '/assets/js/public-js/mjschool-admin-dashboard.js', __FILE__ ) );
			wp_localize_script('mjschool-admin-dashboard','mjschool_dashboard_data',$mix_data);
		}
		wp_enqueue_script( 'mjschool-common', plugins_url( '/assets/js/mjschool-common.js', __FILE__ ));	
		wp_localize_script('mjschool-common','mjschool_common_data',$mix_data);
		
	}
}
/**
 * Displays the frontend student registration form and enqueues necessary assets.
 *
 * This function:
 * 1. Enqueues CSS and JavaScript files required for material design, validation, datepickers, and form styling.
 * 2. Loads media uploader and Thickbox scripts for avatar upload.
 * 3. Sets up client-side validation using jQuery Validation Engine.
 * 4. Initializes datepicker fields for birth date and custom fields with restrictions.
 * 5. Generates the HTML form markup for student registration including class selection, personal info, and registration fees.
 *
 * @param string $class_name               Selected class.
 * @param string $first_name               Student's first name.
 * @param string $middle_name              Student's middle name.
 * @param string $last_name                Student's last name.
 * @param string $gender                   Student's gender.
 * @param string $birth_date               Student's birth date.
 * @param string $address                  Address.
 * @param string $city_name                City.
 * @param string $state_name               State.
 * @param string $zip_code                 ZIP/Postal code.
 * @param string $mobile_number            Mobile phone number.
 * @param string $alternet_mobile_number   Alternate mobile number.
 * @param string $phone                    Landline phone number.
 * @param string $email                    Email address.
 * @param string $username                 Username (optional if using email as login).
 * @param string $password                 Password.
 * @param string $smgt_user_avatar         Uploaded avatar file.
 *
 * @since 1.0.0
 */
function mjschool_registration_form( $class_name, $first_name, $middle_name, $last_name, $gender, $birth_date, $address, $city_name, $state_name, $zip_code, $mobile_number, $alternet_mobile_number, $phone, $email, $username, $password, $smgt_user_avatar ) {
	
	wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ) );
	wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ) );
	//-------------- MATERIAL DESIGN ---------------//
	wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ) );
	$lancode = get_locale();
	$code = substr( $lancode, 0, 2 );
	wp_enqueue_style( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ) );
	wp_register_script( 'jquery-validationEngine-' . $code . '', plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $code . '.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_media();
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-validationEngine-' . $code . '' );
	wp_register_script( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_script( 'jquery-validationEngine' );
	wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ) );
	if (is_rtl( ) ) {;
		wp_enqueue_style( 'mjschool-custome_rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ) );
	}
	wp_enqueue_style( 'font-awesome', plugins_url( '/assets/css/third-party-css/font-awesome.min.css', __FILE__ ) );
	wp_register_script( 'font-awesome-all', plugins_url( '/assets/js/third-party-js/font-awesome.all.min.js', __FILE__ ) );
	wp_enqueue_script( 'mjschool-customobj', plugins_url( '/assets/js/mjschool-custom-confilict-obj.js', __FILE__), array( 'jquery' ), '', false);
	wp_enqueue_style( 'mjschool-register', plugins_url( '/assets/css/settings/mjschool-register.css', __FILE__ ) );
	wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ) );
	wp_enqueue_script( 'mjschool-popup', plugins_url( '/assets/js/mjschool-popup.js', __FILE__ ) );
	
	wp_localize_script( 'mjschool-popup', 'mjschool', array(
		'ajax' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'mjschool_ajax_nonce' ),
	) );
	// Registration form js.
	$document_option = get_option( 'mjschool_upload_document_type' );
	$document_type = explode( ', ', $document_option );
	$document_type_json = $document_type;
	$document_size = get_option( 'mjschool_upload_document_size' );
	// wp_enqueue_script( 'mjschool-common-js', plugins_url( '/assets/js/mjschool-common.js', __FILE__ ) );
	// wp_localize_script('mjschool-common-js','mjschool_common_data',array(
	// 	'date_format'                    => get_option('mjschool_datepicker_format'),
		// 'document_type_json' => $document_type_json,
		// 'document_size' > $document_size,
	// ));
	wp_enqueue_script( 'mjschool-registration-js', plugins_url( '/assets/js/mjschool-registration.js', __FILE__ ) );
	wp_localize_script('mjschool-registration-js','mjschool_registration_data',array(
		'date_format'                    => get_option('mjschool_datepicker_format'),
		'document_type_json' => $document_type_json,
		'document_size' => $document_size,
		'document_delete_alert' => esc_html__('Are you sure you want to delete this record?','mjschool'),
	));

	?>
	<?php
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'success_1' ) {
		?>
		<div class="col-lg-12 col-md-12 mjschool-admission-successfully-message">
			<?php
			esc_attr_e( 'Registration complete.Your account active after admin can approve.', 'mjschool' );
			?>
		</div>
		<?php
	}
	$edit       = 0;
	if ( is_rtl() ) {
		wp_enqueue_style( 'mjschool-rtl-registration-css', plugins_url( '/assets/css/mjschool-rtl-registration-form.css', __FILE__ ) );
	}
	$document_option    = get_option( 'mjschool_upload_document_type' );
	$document_type      = explode( ', ', $document_option );
	$document_type_json = $document_type;
	$document_size      = get_option( 'mjschool_upload_document_size' );
	?>

	<div class="mjschool-student-registration-form">
		<form id="mjschool-registration-form" action="<?php echo esc_url( sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) ); ?>" method="post" enctype="multipart/form-data">
			<div class="form-body mjschool-user-form"> <!------  Form Body. -------->
				<div class="row">
					<div class="col-md-6 input mjschool-error-msg-left-margin mjschool-responsive-bottom-15">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span></label>
						<select name="class_name" class="mjschool-line-height-27px-registration-form form-control validate[required] mjschool-width-100px" id="class_name">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							$tablename      = 'mjschool_class';
							$retrieve_class_data = mjschool_get_all_data( $tablename );
							foreach ( $retrieve_class_data as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata->class_id ); ?>" <?php selected( $classval, $classdata->class_id ); ?>><?php echo esc_html( $classdata->class_name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php
					if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
						$fees_id  = get_option( 'mjschool_registration_amount' );
						$obj_fees = new Mjschool_Fees();
						$amount   = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
						if ( $amount ) {
							$fees = $amount;
						} else {
							$fees = 0;
						}
						?>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input class="mjschool-line-height-29px-registration-from form-control text-input" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() ) . ' ' . esc_attr( $fees ); ?>">
									<label for="userinput1" class="active"><?php esc_html_e( 'Registration Fees', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
						<input id="registration_fees" class="form-control" type="hidden" name="registration_fees" value="<?php echo esc_attr( get_option( 'mjschool_registration_amount' ) ); ?>">
						<?php
					}
					?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="first_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) );} ?>" name="first_name">
								<label for="userinput1" class="active"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) );} ?>" name="middle_name">
								<label for="userinput1" class="active"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="last_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) ); } ?>" name="last_name">
								<label for="userinput1" class="active"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-res-margin-bottom-20px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mb-0">
									<div class="input-group mb-0">
										<label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span></label>
										<div class="d-inline-block mb-1">
											<?php
											$genderval = 'male';
											if ( $edit ) {
												$genderval = $user_info->gender;
											} elseif ( isset( $_POST['gender'] ) ) {
												$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
											}
											?>
											<input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> />
											<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
											&nbsp;&nbsp;
											<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> />
											<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
											&nbsp;&nbsp;
											<input type="radio" value="other" class="tog validate[required]" name="gender" <?php checked( 'other', $genderval ); ?> />
											<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="birth_date" class="mjschool-line-height-29px-registration-from validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_html( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_html( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); } else { echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); }?>" readonly>
								<label for="userinput1" class="active"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="address" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" name="address" value="<?php if ( $edit ) { echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_textarea_field(wp_unslash($_POST['address'])) ); } ?>">
								<label for="userinput1" class="active"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="city_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); } ?>">
								<label for="userinput1" class="active"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); }?>">
								<label for="userinput1" class="active"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="zip_code" class="form-control mjschool-line-height-29px-registration-from validate[required,custom[zipcode]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); }?>">
								<label for="userinput1" class="active"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-mobile-error-massage-left-margin">
						<div class="form-group input mjschool-margin-bottom-0">
							<div class="col-md-12 form-control mjschool-mobile-input">
								<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
								<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="mjschool-line-height-29px-registration-from country_code_res" name="phonecode">
								<input id="mobile_number" class="mjschool-line-height-29px-registration-from form-control text-input validate[required,custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mobile_number" maxlength="10" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); }?>">
								<label for="userinput6 " class="label_mobile_number mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-mobile-error-massage-left-margin">
						<div class="form-group input mjschool-margin-bottom-0">
							<div class="col-md-12 form-control mjschool-mobile-input">
								<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
								<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="mjschool-line-height-29px-registration-from" name="alter_mobile_number">
								<input id="alternet_mobile_number" class="mjschool-line-height-29px-registration-from form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['alternet_mobile_number'])) ); }?>">
								<label for="userinput6" class="mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="email" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[email]] text-input " maxlength="100" type="text" name="email" value="<?php if ( $edit ) { echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_email(wp_unslash($_POST['email'])) ); }?>">
								<label for="userinput1" class="label_email active"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="password" class="mjschool-line-height-29px-registration-from form-control <?php if (!$edit) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" value="">
								<label for="userinput1" class="active"><?php esc_html_e( 'Password', 'mjschool' ); ?><?php if (!$edit) { ?><span class="required">*</span><?php } ?></label>
								<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
							</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mb-0" style="padding:0px;padding-left:10px;">
								<div class="col-sm-12 mjschool-display-flex mb-0">
									<input type="file" style="border:0px;margin-bottom:0px;" class="form-control" onchange="mjschool_file_check(this);" name="smgt_user_avatar">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Document Details', 'mjschool' ); ?></h3>
			</div>
			<div class="mjschool-more-document">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="document_title" class="form-control  text-input" maxlength="50" type="text" value="" name="document_title[]">
									<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-5 col-sm-1">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
									<label for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></label>
									<div class="col-sm-12 mjschool-display-flex">
										<input id="upload_user_avatar_button" name="document_file[]" type="file" class="form-control file mjschool-file-validation" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>"  />
									</div>
								</div>
							</div>
						</div>
						
						<div class="col-md-1 col-sm-1 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
						</div>
						
					</div>
				</div>
			</div>
			<?php
			// --------- Get Module Wise Custom Field Data. --------------//
			$custom_field_obj = new Mjschool_Custome_Field();
			$module           = 'student';
			$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
			wp_nonce_field( 'save_student_frontend_shortcode_nonce' ); ?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php esc_html_e( 'Registration', 'mjschool' ); ?>" name="save_student_front" class="btn btn-success btn_style mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
}
/**
 * Handles the complete frontend student registration process.
 *
 * This function performs several tasks:
 * 1. Validates the form nonce for security.
 * 2. Checks for any registration errors.
 * 3. Creates a new WordPress user with the provided data.
 * 4. Assigns roles (student and subscriber) to the new user.
 * 5. Uploads the user's avatar and multiple documents.
 * 6. Stores all student metadata (personal info, class, contact, documents, etc.).
 * 7. Inserts custom field data for the student module.
 * 8. Generates a registration fee invoice if applicable.
 * 9. Sends email notifications to the student and assigned teachers.
 * 10. Redirects to the appropriate success page depending on the approval settings.
 *
 * @param int|string $class_name             Class ID or name.
 * @param string     $first_name             Student's first name.
 * @param string     $middle_name            Student's middle name.
 * @param string     $last_name              Student's last name.
 * @param string     $gender                 Student's gender.
 * @param string     $birth_date             Birth date of the student.
 * @param string     $address                Student's address.
 * @param string     $city_name              City name.
 * @param string     $state_name             State name.
 * @param string     $zip_code               Postal/ZIP code.
 * @param string     $mobile_number          Mobile phone number.
 * @param string     $alternet_mobile_number Alternate mobile number.
 * @param string     $phone                  Landline phone number.
 * @param string     $email                  Email address (used as username/login).
 * @param string     $username               Username (optional if using email as login).
 * @param string     $password               User password.
 * @param string     $smgt_user_avatar       Uploaded avatar file.
 * @param array      $document_title         Array of document titles.
 * @param array      $document_file          Array of document files uploaded.
 * @param string     $wp_nonce               Nonce for security verification.
 *
 * @return int|void Returns the new user ID on success or terminates on failure.
 *
 * @since 1.0.0
 */
function mjschool_complete_registration( $class_name, $first_name, $middle_name, $last_name, $gender, $birth_date, $address, $city_name, $state_name, $zip_code, $mobile_number, $alternet_mobile_number, $phone, $email, $username, $password, $smgt_user_avatar, $document_title, $document_file, $wp_nonce ) {
	global $mjschool_reg_errors;
	$custom_field_obj = new Mjschool_Custome_Field();
	if ( wp_verify_nonce( $wp_nonce, 'save_student_frontend_shortcode_nonce' ) ) {
		if ( 1 > count( $mjschool_reg_errors->get_error_messages() ) ) {
			$userdata = array(
				'user_login' => $email,
				'user_email' => $email,
				'user_pass'  => $password,
				'user_url'   => null,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'nickname'   => null,
			);
			$user_id  = wp_insert_user( $userdata );
			if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
				$registration_fees_id = get_option( 'mjschool_registration_amount' );
			} else {
				$registration_fees_id = '';
			}
			if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
				$obj_fees            = new Mjschool_Fees();
				$registration_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $registration_fees_id );
				$generated           = mjschool_generate_admission_fees_invoice( $registration_amount, $user_id, $registration_fees_id, $class_name, 0, 'Registration Fees' );
			}
			// CUSTOM FIELD INSERT START. //
			$module           = 'student';
			$add_custom_field = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $user_id );
			// CUSTOM FIELD INSERT END. //
			$user = new WP_User( $user_id );
			$user->set_role( 'student' );
			$user->add_role( 'subscriber' );
			$smgt_avatar = '';
			if ( $_FILES['mjschool_user_avatar']['size'] > 0 ) {
				$smgt_avatar_image = mjschool_user_avatar_image_upload( 'mjschool_user_avatar' );
				$smgt_avatar       = content_url() . '/uploads/school_assets/' . $smgt_avatar_image;
			} else {
				$smgt_avatar = '';
			}
			$document_content = array();
			if ( ! empty( $document_file['name'] ) ) {
				$count_array = count( $document_file['name'] );
				for ( $a = 0; $a < $count_array; $a++ ) {
					if ( ( $document_file['size'][ $a ] > 0 ) && ( ! empty( $document_title[ $a ] ) ) ) {
						$document_title_final = $document_title[ $a ];
						$final_document_file = mjschool_upload_document_user_multiple( $document_file, $a, $document_title[ $a ] );
					}
					if ( ! empty( $final_document_file ) && ! empty( $document_title_final ) ) {
						$document_content[] = array(
							'document_title' => $document_title_final,
							'document_file'  => $final_document_file,
						);
					}
				}
			}
			if ( ! empty( $document_content ) ) {
				$final_document = json_encode( $document_content );
			} else {
				$final_document = '';
			}
			// DOCUMENT UPLOAD FILE CODE END.
			$usermetadata = array(
				'roll_id'                => '',
				'middle_name'            => $middle_name,
				'gender'                 => $gender,
				'birth_date'             => $birth_date,
				'address'                => $address,
				'city'                   => $city_name,
				'state'                  => $state_name,
				'zip_code'               => $zip_code,
				'class_name'             => $class_name,
				'phone'                  => $phone,
				'mobile_number'          => $mobile_number,
				'user_document'          => $final_document,
				'alternet_mobile_number' => $alternet_mobile_number,
				'mjschool_user_avatar'       => $smgt_avatar,
			);
			foreach ( $usermetadata as $key => $val ) {
				$result = update_user_meta( $user_id, $key, $val );
			}
			if ( get_option( 'mjschool_student_approval' ) === '1' ) {
				$hash      = md5( rand( 0, 1000 ) );
				$result123 = update_user_meta( $user_id, 'hash', $hash );
			}
			$class_name = get_user_meta( $user_id, 'class_name', true );
			$user_info  = get_userdata( $user_id );
			$to         = $user_info->user_email;
			$subject    = get_option( 'mjschool_registration_title' );
			$search     = array( '{{student_name}}', '{{email_id}}', '{{class_name}}', '{{password}}', '{{school_name}}' );
			$replace    = array( $user_info->display_name, $to, mjschool_get_class_name( $class_name ), $password, get_option( 'mjschool_name' ) );
			$message    = str_replace( $search, $replace, get_option( 'mjschool_registration_mailtemplate' ) );
			$school     = get_option( 'mjschool_name' );
			$headers    = '';
			$headers   .= 'From: ' . $school . ' <noreplay@gmail.com>' . "\r\n";
			$headers   .= "MIME-Version: 1.0\r\n";
			$headers   .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			// MAIL CONTEMNT WITH TEMPLATE DESIGN.
			$email_template = mjschool_get_mail_content_with_template_design( $message );
			if ( $result ) {
				if ( get_option( 'mjschool_student_approval' ) === '1' ) {
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						wp_mail( $to, $subject, $email_template, $headers );
					}
					$page_id      = get_option( 'mjschool_install_student_registration_page' );
					$referrer_ipn = array(
						'action' => 'success_1',
					);
					$referrer_ipn = add_query_arg( $referrer_ipn, home_url() . '/student-registration/' );
					wp_redirect( $referrer_ipn );
					die();
				} else {
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						wp_mail( $to, $subject, $email_template, $headers );
					}
					// ----------- STUDENT ASSIGNED TEACHER MAIL. ------------//
					$TeacherIDs                 = mjschool_check_class_exits_in_teacher_class( $class_name );
					$TeacherEmail               = array();
					$string['{{school_name}}']  = get_option( 'mjschool_name' );
					$string['{{student_name}}'] = $user_info->display_name;
					$subject                    = get_option( 'mjschool_student_assign_teacher_mail_subject' );
					$MessageContent             = get_option( 'mjschool_student_assign_teacher_mail_content' );
					foreach ( $TeacherIDs as $teacher ) {
						$TeacherData = get_userdata( $teacher );
						$string['{{teacher_name}}'] = mjschool_get_display_name( $TeacherData->ID );
						$message                    = mjschool_string_replacement( $string, $MessageContent );
						mjschool_send_mail( $TeacherData->user_email, $subject, $message );
					}
					$page_id      = get_option( 'mjschool_install_student_registration_page' );
					$referrer_ipn = array(
						'page_id' => $page_id,
						'action'  => 'success_2',
					);
					$referrer_ipn = add_query_arg( $referrer_ipn, home_url() );
					wp_redirect( $referrer_ipn );
					die();
				}
				return $user_id;
			}
		}
	} else {
		wp_die( esc_html( 'Security check failed! Invalid security token.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
}
/**
 * Validates frontend student registration form fields.
 *
 * This function checks for required fields, validates the username length,
 * ensures the username and email are unique, and verifies that the email format is valid.
 * Any validation errors are stored in a global WP_Error object and displayed inline.
 *
 * @param string $class_name               Selected class.
 * @param string $first_name               Student's first name.
 * @param string $middle_name              Student's middle name.
 * @param string $last_name                Student's last name.
 * @param string $gender                   Student's gender.
 * @param string $birth_date               Student's birth date.
 * @param string $address                  Address.
 * @param string $city_name                City.
 * @param string $state_name               State.
 * @param string $zip_code                 ZIP/Postal code.
 * @param string $mobile_number            Mobile phone number.
 * @param string $alternet_mobile_number   Alternate mobile number.
 * @param string $phone                    Landline phone number.
 * @param string $email                    Email address.
 * @param string $username                 Username.
 * @param string $password                 Password.
 * @param string $smgt_user_avatar         Uploaded avatar file.
 *
 * @global WP_Error $mjschool_reg_errors  Stores validation error messages.
 *
 * @since 1.0.0
 */
function mjschool_registration_validation( $class_name, $first_name, $middle_name, $last_name, $gender, $birth_date, $address, $city_name, $state_name, $zip_code, $mobile_number, $alternet_mobile_number, $phone, $email, $username, $password, $smgt_user_avatar ) {
	global $mjschool_reg_errors;
	$mjschool_reg_errors = new WP_Error();
	if ( empty( $class_name ) || empty( $first_name ) || empty( $last_name ) || empty( $birth_date ) || empty( $address ) || empty( $city_name ) || empty( $zip_code ) || empty( $mobile_number ) || empty( $email ) || empty( $username ) || empty( $password ) ) {
		$mjschool_reg_errors->add( 'field', 'Required form field is missing' );
	}
	if ( 4 > strlen( $username ) ) {
		$mjschool_reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
	}
	if ( username_exists( $username ) ) {
		$mjschool_reg_errors->add( 'user_name', 'Sorry, that username already exists!' );
	}
	if ( ! is_email( $email ) ) {
		$mjschool_reg_errors->add( 'email_invalid', 'Email is not valid' );
	}
	if ( email_exists( $email ) ) {
		$mjschool_reg_errors->add( 'email', 'Email Already in use' );
	}
	if ( is_wp_error( $mjschool_reg_errors ) ) {
		foreach ( $mjschool_reg_errors->get_error_messages() as $error ) {
			echo '<div class="mjschool-student-reg-error">';
			echo '<strong> ' . esc_attr__( 'ERROR', 'mjschool' ) . '</strong> : ';
			echo '<span class="error"> ' . esc_html( $error ) . ' </span><br/>';
			echo '</div>';
		}
	}
}
/**
 *
 * This function performs the following:
 * 1. Checks for form submission and verifies the security nonce.
 * 2. Validates all submitted student registration fields.
 * 3. Sanitizes and stores user input in global variables.
 * 4. Calls `mjschool_complete_registration` to create the student user, upload documents, assign roles,
 *    store metadata, and send notification emails if validation passes.
 * 5. Displays the student registration form with previously entered values.
 *
 * @global string $mjschool_class_name
 * @global string $mjschool_first_name
 * @global string $mjschool_middle_name
 * @global string $mjschool_last_name
 * @global string $mjschool_gender
 * @global string $mjschool_birth_date
 * @global string $mjschool_address
 * @global string $mjschool_city_name
 * @global string $mjschool_state_name
 * @global string $mjschool_zip_code
 * @global string $mjschool_mobile_number
 * @global string $mjschool_alternet_mobile_number
 * @global string $mjschool_phone
 * @global string $mjschool_email
 * @global string $mjschool_username
 * @global string $mjschool_password
 * @global string $mjschool_user_avatar
 * @global array  $mjschool_document_title
 * @global array  $mjschool_document_file
 *
 * @since 1.0.0
 */
function mjschool_student_registration_function() {
	global $mjschool_class_name, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_gender, $mjschool_birth_date, $mjschool_address, $mjschool_city_name, $mjschool_state_name, $mjschool_zip_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_phone, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_user_avatar, $mjschool_document_title, $mjschool_document_file;
	$mjschool_class_name = isset( $_POST['class_name'] ) ? sanitize_text_field(wp_unslash($_POST['class_name'])) : '';
	if ( isset( $_POST['save_student_front'] ) ) {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_student_frontend_shortcode_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		mjschool_registration_validation(
			sanitize_text_field( wp_unslash( $_POST['class_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['first_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['middle_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['last_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['gender'] ) ),
			sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ),
			sanitize_text_field( wp_unslash( $_POST['address'] ) ),
			sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
			sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ),
			sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ),
			sanitize_text_field( wp_unslash( $_POST['alternet_mobile_number'] ) ),
			sanitize_text_field( wp_unslash( $_POST['phone'] ) ),
			sanitize_email( wp_unslash( $_POST['email'] ) ),
			sanitize_email( wp_unslash( $_POST['email'] ) ),
			sanitize_text_field( wp_unslash( $_POST['password'] ) ),
			isset( $_FILE['mjschool_user_avatar'] )
		);
		// sanitize user form input.
		global $mjschool_class_name, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_gender, $mjschool_birth_date, $mjschool_address, $mjschool_city_name, $mjschool_state_name, $mjschool_zip_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_phone, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_user_avatar, $mjschool_document_title, $mjschool_document_file;
		if ( isset( $_POST['class_name'] ) ) {
			$mjschool_class_name = sanitize_text_field(wp_unslash($_POST['class_name']));
		} else {
			echo esc_html( $mjschool_class_name = '' );
		}
		$mjschool_first_name             = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['first_name'] ) );
		$mjschool_middle_name            = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['middle_name'] ) );
		$mjschool_last_name              = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['last_name'] ) );
		$mjschool_gender                 = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['gender'] ) );
		$mjschool_birth_date             = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['birth_date'] ) );
		$mjschool_address                = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['address'] ) );
		$mjschool_city_name              = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['city_name'] ) );
		$mjschool_state_name             = mjschool_strip_tags_and_stripslashes( sanitize_text_field( $_POST['state_name'] ) );
		$mjschool_zip_code               = mjschool_strip_tags_and_stripslashes( $_POST['zip_code'] );
		$mjschool_mobile_number          = mjschool_strip_tags_and_stripslashes( $_POST['mobile_number'] );
		$mjschool_alternet_mobile_number = mjschool_strip_tags_and_stripslashes( $_POST['alternet_mobile_number'] );
		$mjschool_phone                  = mjschool_strip_tags_and_stripslashes( $_POST['phone'] );
		$mjschool_username               = sanitize_email(wp_unslash($_POST['email']));
		$mjschool_password               = sanitize_text_field(wp_unslash( $_POST['password'] ) );
		$mjschool_email                  = sanitize_email(wp_unslash($_POST['email']));
		$mjschool_document_title         = sanitize_text_field(wp_unslash($_POST['document_title']));
		$mjschool_document_file          = $_FILES['document_file'];
		$wp_nonce                        = $_POST['_wpnonce'];
		// call @function complete_registration to create the user.
		// only when no WP_error is found.
		mjschool_complete_registration(
			$mjschool_class_name,
			$mjschool_first_name,
			$middle_name,
			$mjschool_last_name,
			$mjschool_gender,
			$mjschool_birth_date,
			$mjschool_address,
			$mjschool_city_name,
			$mjschool_state_name,
			$mjschool_zip_code,
			$mjschool_mobile_number,
			$mjschool_alternet_mobile_number,
			$mjschool_phone,
			$mjschool_email,
			$mjschool_username,
			$mjschool_password,
			$mjschool_user_avatar,
			$mjschool_document_title,
			$mjschool_document_file,
			$wp_nonce
		);
	}
	mjschool_registration_form(
		$mjschool_class_name,
		$mjschool_first_name,
		$mjschool_middle_name,
		$mjschool_last_name,
		$mjschool_gender,
		$mjschool_birth_date,
		$mjschool_address,
		$mjschool_city_name,
		$mjschool_state_name,
		$mjschool_zip_code,
		$mjschool_mobile_number,
		$mjschool_alternet_mobile_number,
		$mjschool_phone,
		$mjschool_email,
		$mjschool_username,
		$mjschool_password,
		$mjschool_user_avatar
	);
}
/**
 *
 * This function checks for 'haskey' and 'id' parameters in the request,
 * verifies the activation hash for the user, and activates the account
 * if the hash matches. It then redirects the user to the login page with
 * a success or failure status. If the hash is missing or invalid, the user
 * is redirected to the home page.
 *
 * @global wpdb $wpdb WordPress database object.
 *
 * @since 1.0.0
 */
function mjschool_activate_mail_link() {
	if ( isset( $_REQUEST['haskey'] ) && isset( $_REQUEST['id'] ) ) {
		$user_id     = $user->ID; // prints the id of the user.
		if ( get_user_meta( $user_id, 'hash', true ) ) {
			if ( get_user_meta( $user_id, 'hash', true ) === sanitize_text_field(wp_unslash($_REQUEST['haskey'])) ) {
				delete_user_meta( $user_id, 'hash' );
				$curr_args = array(
					'page_id'       => get_option( 'mjschool_login_page' ),
					'mjschool_activate' => 1,
				);
				$referrer_faild = add_query_arg( $curr_args, get_permalink( get_option( 'mjschool_login_page' ) ) );
				wp_redirect( $referrer_faild );
				die();
			} else {
				$curr_args = array(
					'page_id'       => get_option( 'mjschool_login_page' ),
					'mjschool_activate' => 2,
				);
				$referrer_faild = add_query_arg( $curr_args, get_permalink( get_option( 'mjschool_login_page' ) ) );
				wp_redirect( $referrer_faild );
				die();
			}
		}
		wp_redirect( home_url( '/' ) );
		die();
	}
}
/**
 * Prevents login for users whose email accounts are not yet activated.
 *
 * This filter checks if the user has a 'hash' meta key, which indicates
 * the account is pending email activation. If the hash exists, the user
 * is redirected to the login page with an activation notice and login
 * is blocked.
 *
 * @param WP_User|WP_Error $user The user object or WP_Error.
 * @return WP_User|WP_Error Returns the user object if activated, or halts
 *                           the login via redirect if not.
 *
 * @since 1.0.0
 */
add_filter(
	'wp_authenticate_user',
	function ( $user ) {
		$havemeta = get_user_meta( $user->ID, 'hash', true );
		if ( $havemeta ) {
			$WP_Error       = new WP_Error();
			$referrer       = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$curr_args      = array(
				'page_id'       => get_option( 'mjschool_login_page' ),
				'mjschool_activate' => 'mjschool_activate',
			);
			$referrer_faild = add_query_arg( $curr_args, get_permalink( get_option( 'mjschool_login_page' ) ) );
			wp_redirect( $referrer_faild );
			die();
		}
		return $user;
	},
	10,
	2
);
add_action( 'wp_enqueue_scripts', 'mjschool_enqueue_front_assets' );
add_action( 'init', 'mjschool_install_login_page' );
add_action( 'init', 'mjschool_install_student_registration_page' );
add_action( 'init', 'mjschool_install_student_admission_page' );
add_action( 'init', 'mjschool_install_combine_admission_page' ); // new
add_action( 'wp_head', 'mjschool_user_dashboard' );
add_shortcode( 'smgt_login', 'mjschool_login_link' );
add_action( 'wp_login', 'mjschool_student_login', 10, 2 );
add_action( 'init', 'mjschool_output_ob_start' );
// Register a new shortcode.
add_shortcode( 'smgt_student_registration', 'mjschool_custom_registration_shortcode' );
add_shortcode( 'smgt_student_admission', 'mjschool_custom_admission_shortcode' );
add_shortcode( 'smgt_student_combine_admission', 'mjschool_custom_combine_admission_shortcode' ); // new
/**
 * Handles the [book] shortcode output for MJ School student registration.
 *
 * This function captures the output of the frontend student registration
 * form and returns it as a string, so it can be displayed wherever the
 * [book] shortcode is used.
 *
 * @return string HTML content of the student registration form.
 *
 * @since 1.0.0
 */
function mjschool_custom_registration_shortcode() {
	ob_start();
	mjschool_student_registration_function();
	return ob_get_clean();
}
/**
 * Handles the [admission] shortcode output for MJ School student admission.
 *
 * This function captures the output of the frontend student admission
 * form and returns it as a string, allowing the form to be displayed
 * wherever the [admission] shortcode is used.
 *
 * @return string HTML content of the student admission form.
 *
 * @since 1.0.0
 */
function mjschool_custom_admission_shortcode() {
	ob_start();
	mjschool_student_admission_function();
	return ob_get_clean();
}
/**
 * Handles the [combine_admission] shortcode output for MJ School.
 *
 * This function captures the output of the frontend combined student
 * admission form and returns it as a string, allowing the form to be
 * displayed wherever the [combine_admission] shortcode is used.
 *
 * @return string HTML content of the combined student admission form.
 *
 * @since 1.0.0
 */
function mjschool_custom_combine_admission_shortcode() {
	ob_start();
	mjschool_student_admission_function();
	return ob_get_clean();
}
/**
 * Starts PHP output buffering.
 *
 * This function initializes output buffering so that any output generated
 * can be captured and manipulated before sending it to the browser.
 *
 * @since 1.0.0
 */
function mjschool_output_ob_start() {
	ob_start();
}
/**
 * Generates PDF Function.
 *
 * This function triggers on the 'init' action hook. It checks for the
 * 'print' request parameter and a valid student ID,type,certificate_id etc. It
 * then fetches data, prepares the HTML layout,
 * and outputs the content for PDF rendering.
 *
 * @since 1.0.0
 */
add_action( 'init', 'mjschool_generate_pdf' );
function mjschool_generate_pdf() {
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'group_result_pdf' && isset( $_REQUEST['student'] ) ) {
		ob_start();
		$uid               = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student'])) ) );
		$merge_id          = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['merge_id'])) ) );
		$obj_mark          = new Mjschool_Marks_Manage();
		$exam_obj          = new Mjschool_exam();
		$merge_data        = $exam_obj->mjschool_get_single_merge_exam_setting( $merge_id );
		$merge_name        = $merge_data->merge_name;
		$merge_config_data = json_decode( $merge_data->merge_config );
		$totalObjects      = ! empty( $merge_config_data ) ? count( $merge_config_data ) : 0;
		$class_id          = isset( $_REQUEST['class_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['class_id'])) ) ) : 0;
		$section_id        = isset( $_REQUEST['section_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['section_id'])) ) ) : 0;
		$user              = get_userdata( $uid );
		$user_meta         = get_user_meta( $uid );
		$subject           = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
		$total_subject     = count( $subject );
		$umetadata         = mjschool_get_user_image( $uid );
		if( isset( $_REQUEST['teacher_id'] ) ){
			$metadata          = get_user_meta( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
			$signature_path    = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
			$signature_url     = $signature_path ? content_url( $signature_path ) : '';
		}
		?>
		<!-- HTML CONTENT START. -->
		<div class="container" style="margin-bottom:12px;">
			<div style="border: 2px solid;">
				<div style="padding:20px;">
					<div class="mjschool_float_left_width_100">
						<div style="float:left;width:30%;">
							<div class="mjschool-custom-logo-class" style="float:left;border-radius:50px;">
								<div style="background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
							</div>
						</div>
						<div style="float:left; width:70%;font-size:24px;padding-top:25px;">
							<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
							<p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
							<div class="mjschool_fees_center_margin_0px"><p class="mjschool_fees_width_fit_content_inline"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?></p></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="border: 2px solid; margin-bottom:12px;">
			<div class="mjschool_float_left_width_100">
				<div class="mjschool_padding_10px">
					<div style="float:left; width:50%;">
						<b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>:
						<?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $uid, 'last_name', true ) ); ?>
					</div>
					<div style="float:left; width:50%;">
						<b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
						<?php echo esc_html( $merge_name ); ?>
					</div>
					<div style="clear:both;"></div>
					<!-- Row 2: Roll No & Class + Section. -->
					<div style="float:left; width:50%;">
						<b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
						<?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?>
					</div>
					<div style="width: 50%; float: left; margin-bottom: 5px;">
						<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
						<?php
						$section_id = get_user_meta( $uid, 'class_section', true );
						$class_name = mjschool_get_class_name( $class_id );
						if ( ! empty( $section_id ) ) {
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
		<table style="float:left;width:100%;border:1px solid #000;margin-bottom:12px;" cellpadding="10" cellspacing="0">
			<thead>
				<tr style="background-color:#b8daff;">
					<th rowspan="2" style="border:1px solid #000;"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
					<?php
					if ( ! empty( $merge_config_data ) ) {
						foreach ( $merge_config_data as $item ) {
							$exam_id   = $item->exam_id;
							$exam_name = mjschool_get_exam_name_id( $exam_id );
							if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
								$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
								$contributions_data_array = json_decode( $exam_data->contributions_data, true );
								echo '<th colspan="' . ( count( $contributions_data_array ) ) . '" style="border:1px solid #000;">' . esc_html( $exam_name ) . '</th>';
							} else {
								echo '<th style="border:1px solid #000;">' . esc_html( $exam_name ) . '</th>';
							}
						}
					}
					?>
					<th colspan="2" style="border:1px solid #000;"><?php echo esc_attr( mjschool_print_weightage_data_pdf( $merge_data->merge_config ) ); ?></th>
				</tr>
				<tr style="background-color:#b8daff;">
					<?php
					if ( ! empty( $merge_config_data ) ) {
						foreach ( $merge_config_data as $item ) {
							$exam_id = $item->exam_id;
							if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
								$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
								$contributions_data_array = json_decode( $exam_data->contributions_data, true );
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									echo '<th style="border:1px solid #000;">' . esc_html( $con_value['label'] ) . ' ( ' . esc_html( $con_value['mark'] ) . ' )</th>';
								}
							} else {
								$exam_data = $exam_obj->mjschool_exam_data( $exam_id );
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
				$total_obtained     = 0;
				$total_max_possible = 0;
				$any_subject_failed = false;
				foreach ( $subject as $sub ) {
					echo '<tr>';
					echo '<td style="border:1px solid #000;">' . esc_html( $sub->sub_name ) . '</td>';
					$subject_total_weighted = 0;
					foreach ( $merge_config_data as $item ) {
						$exam_id        = $item->exam_id;
						$exam_weightage = $item->weightage;
						$marks          = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( mjschool_check_contribution( $exam_id ) === 'yes' ) {
							$exam_data                = $exam_obj->mjschool_exam_data( $exam_id );
							$contributions_data_array = json_decode( $exam_data->contributions_data, true );
							$subject_total            = 0;
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$mark_value     = isset( $marks[ $con_id ] ) ? floatval( $marks[ $con_id ] ) : 0;
								$subject_total += $mark_value;
								echo '<td style="border:1px solid #000;font-size:18px;">' . esc_html( $mark_value ) . '</td>';
							}
							$weighted_marks = ( $subject_total * $exam_weightage ) / 100;
							if ( $subject_total < $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$any_subject_failed = true;
							}
						} else {
							$marks = floatval( $marks );
							echo '<td style="border:1px solid #000;font-size:18px;">' . esc_html( $marks ) . '</td>';
							$weighted_marks = ( $marks * $exam_weightage ) / 100;
							if ( $marks < $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$any_subject_failed = true;
							}
						}
						$subject_total_weighted += $weighted_marks;
						$grade                   = $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid );
						$comment                 = $obj_mark->mjschool_get_grade_comment( $exam_id, $class_id, $sub->subid, $uid );
					}
					$subject_grade = $obj_mark->mjschool_get_grade_base_on_grand_total( $subject_total_weighted );
					echo '<td style="border:1px solid #000;">' . esc_html( round( $subject_total_weighted, 2 ) ) . '</td>';
					echo '<td style="border:1px solid #000;">' . esc_html( $subject_grade ) . '</td>';
					echo '</tr>';
					$total_obtained     += $subject_total_weighted;
					$total_max_possible += 100;
				}
				$percentage   = ( $total_obtained / $total_max_possible ) * 100;
				$final_grade  = $obj_mark->mjschool_get_grade_base_on_grand_total( $percentage );
				$final_result = ( $any_subject_failed || $percentage < 33 ) ? esc_html__( 'Fail', 'mjschool' ) : esc_html__( 'Pass', 'mjschool' );
				?>
			</tbody>
		</table>
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
					<td style="border:1px solid #000;"><?php echo esc_html( round( $total_obtained, 2 ) ) . ' / ' . esc_html( $total_max_possible ); ?></td>
					<td style="border:1px solid #000;"><?php echo number_format( $percentage, 2 ) . '%'; ?></td>
					<td style="border:1px solid #000;"><?php echo esc_html( $final_grade ); ?></td>
					<td style="border:1px solid #000;"><?php echo esc_html( $final_result ); ?></td>
				</tr>
			</tbody>
		</table>
		<div  style="border: 2px solid; width:96.6%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
			<!-- Teacher's Comment (Left Side). -->
			<div style="float: left; width: 33.33%;">
				<div style="margin-left: 20px;">
					<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong>
					<p><?php echo esc_html( sanitize_textarea_field(wp_unslash($_REQUEST['comment'])) ); ?></p>
				</div>
			</div>
			<!-- Teacher Signature (Middle) -->
			<div style="float: left; width: 33.33%; text-align: center; padding-top: 0px;">
				<?php
				if ( ! empty( $signature_url ) ) {
					 ?>
					<div>
						<img src="<?php echo esc_url($signature_url); ?>" style="width:100px;height:45px;" />
					</div>
					<?php
				}
				else
				{
					?>
					<div>
						<div style="width:100px;height:45px;"></div>
					</div>
				<?php } ?>
				<div class="mjschool_fees_width_150px"></div>
				<div class="mjschool_margin_top_5px">
					<?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?>
				</div>
			</div>
			<!-- Principal Signature (Right Side). -->
			<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
				<div>
					<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
				</div>
				
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
				<div style="margin-right:10px; margin-bottom:10px;">
					<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
				</div>
			</div>
		</div>
		<?php
		$out_put = ob_get_clean();
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$stylesheet1 = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css' );
		$mpdf        = new \Mpdf\Mpdf(
			array(
				'mode'          => 'utf-8',
				'format'        => 'A4',
				'orientation'   => 'P',
				'margin_left'   => 8,   // default is 15.
				'margin_right'  => 8,  // default is 15.
				'margin_top'    => 10,
				'margin_bottom' => 10,
			)
		);
		$mpdf->SetTitle( 'Result' );
		$mpdf->SetDisplayMode( 'fullwidth' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		if ( is_rtl() ) {
			$mpdf->SetDirectionality( 'rtl' );
		}
		$mpdf->WriteHTML( $stylesheet1, 1 );
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		die();
	}
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' && isset( $_REQUEST['student'] ) ) {
		ob_start();
		$school_type = get_option( 'mjschool_custom_class' );
		$uid      = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student'])) ) );
		$exam_id  = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
		$obj_mark = new Mjschool_Marks_Manage();
		$exam_obj = new Mjschool_exam();
		$user            = get_userdata( $uid );
		$user_meta       = get_user_meta( $uid );
		$class_id        = isset( $_REQUEST['class_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['class_id'])) ) ) : 0;
		$section_id      = isset( $_REQUEST['section_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['section_id'])) ) ) : 0;
		$exam_data       = $exam_obj->mjschool_exam_data( $exam_id );
		$class_id        = $exam_data->class_id;
		$exam_section_id = $exam_data->section_id;
		if( isset( $_REQUEST['teacher_id'] ) ){
			$metadata        = get_user_meta( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) );
			$signature_path  = isset( $metadata['signature'][0] ) ? $metadata['signature'][0] : '';
			$signature_url   = $signature_path ? content_url( $signature_path ) : '';
		}
		if ( $exam_section_id === 0 ) {
			$subject = mjschool_get_subject_by_class_id($class_id);
		} else {
			$subject = mjschool_get_subjects_by_class_and_section($class_id, $exam_section_id );
		}
		$total_subject = count( $subject );
		$total       = 0;
		$grade_point = 0;
		$umetadata   = mjschool_get_user_image( $uid );
		error_reporting( 1 );
		if ( is_rtl() ) {
			?>
			<div class="container" style="margin-bottom:8px;">
				<div style="border: 2px solid;">
					<div style="padding:20px;">
						<div style="float:right;width:100%;">
							<div style="float:right;width:25%;">
								<div class="mjschool-custom-logo-class" style="float:right;border-radius:50px;">
									<div style="width: 150px;background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
								</div>
							</div>
							<div style="float:right; width:74%;font-size:24px;padding-top:50px;">
								<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
								<p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
								<div class="mjschool_fees_center_margin_0px"><p class="mjschool_fees_width_fit_content_inline"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?></p></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div style="border: 2px solid;margin-bottom:8px;">
				<div style="float:right;width:100%;">
					<div  class="mjschool_padding_10px">
						<div style="float:right;width:50%;"><?php esc_html_e( 'Student Name', 'mjschool' ); ?>: <b><?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?></div>
						<div style="float:right;width:50%;"><?php esc_html_e( 'Roll Number', 'mjschool' ); ?>:
							<b><?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?> </b>
						</div>
					</div>
				</div>
				<div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:97%;padding:20px;margin-top:10px;">
					<div class="mjschool_float_left_width_100">
						<div  class="mjschool_padding_10px">
							<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>: <?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?></div>
							<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
								<?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?>
							</div>
						</div>
					</div>
					<div class="mjschool_float_width_css" >
						<div  class="mjschool_padding_10px">
							<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
								<?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?>
							</div>
						</div>
					</div>
					<div style="float:right;width:50%;">
						<div  style="padding-top:10px;">
							<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
							<?php
							$classname    = mjschool_get_class_name( $class_id );
							$section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
							echo esc_html( $classname ) . ' - ' . esc_html( $section_name );
							?>
						</div>
					</div>
				</div>
			</div>
			<table style="float:right;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
				<?php
				$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
				$exam_marks    = $exam_data->total_mark;
				$contributions = $exam_data->contributions;
				if ( $contributions === 'yes' ) {
					$contributions_data       = $exam_data->contributions_data;
					$contributions_data_array = json_decode( $contributions_data, true );
				}
				?>
				<thead>
					<tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
						<?php
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								?>
								<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php echo esc_html( $con_value['label'] ) . ' ( ' . esc_html( $con_value['mark'] ) . ' )'; ?></th>
								<?php
							}
							?>
							<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?></th>
							<?php
						} else {
							?>
							<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?></th>
							<?php
						}
						?>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Remarks', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i               = 1;
					$total_pass_mark = 0;
					$total_max_mark  = 0;
					foreach ( $subject as $sub ) {
						$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
						?>
						<tr style="border-bottom: 1px solid #000;">
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $sub->sub_name ); ?></td>
							<?php
							$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
							if ( $contributions === 'yes' ) {
								$subject_total = 0;
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
									$subject_total += $mark_value;
									?>
									<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $mark_value ); ?> </td>
									<?php
								}
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $subject_total ); ?> </td>
								<?php
							} else {
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obtain_marks ); ?> </td>
								<?php
							}
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_grade_comment( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
						</tr>
						<?php
						++$i;
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
							}
						} else {
							$total_marks += $obtain_marks;
						}
						$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
					}
					$total         += $total_marks;
					$total_max_mark = $exam_marks * $total_subject;
					$GPA            = $grade_point / $total_subject;
					$percentage     = $total / $total_max_mark * 100;
					?>
				</tbody>
				<tfoot>
					<tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
						<th><?php esc_html_e( 'TOTAL MARKS', 'mjschool' ); ?></th>
						<th>
							<?php
							if ( ! empty( $total_max_mark ) ) {
								echo esc_html( $total_max_mark );
							} else {
								echo '-';
							}
							?>
						</th>
						<th>
							<?php
							if ( ! empty( $total_pass_mark ) ) {
								echo esc_html( $total_pass_mark );
							} else {
								echo '-';
							}
							?>
						</th>
						<th>
							<?php
							if ( ! empty( $total ) ) {
								echo esc_html( $total );
							} else {
								echo '-';
							}
							?>
						</th>
						<th></th>
					</tr>
				</tfoot>
			</table>
			<table style="float:right;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
				<thead>
					<tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:right;border-right: 1px solid #000;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr style="border-bottom: 1px solid #000;">
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $total_max_mark ); ?></td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $total ); ?></td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php
							if ( ! empty( $percentage ) ) {
								echo number_format( $percentage, 2, '.', '' );
							} else {
								echo '-';
							}
							?>
						</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( round( $GPA, 2 ) ); ?></td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php
							$result  = array();
							$result1 = array();
							foreach ( $subject as $sub ) {
								$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
								if ( $contributions === 'yes' ) {
									$subject_total = 0;
									foreach ( $contributions_data_array as $con_id => $con_value ) {
										$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
										$subject_total += $mark_value;
									}
									$marks_total = $subject_total;
								} else {
									$marks_total = $obtain_marks;
								}
								if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
									$result[] = 'pass';
								} else {
									$result1[] = 'fail';
								}
							}
							if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
								esc_html_e( 'Pass', 'mjschool' );
							} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
								esc_html_e( 'Fail', 'mjschool' );
							} else {
								echo '-';
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<div style="border: 2px solid #8b8b8b;background-color:#eacf80;width:100%;float: right;margin-bottom:8px;">
				<div class="row">
					<div style="float:right;width: 60%;margin: 10px;">
						<b  style="text-align: right"><?php esc_html_e( 'Percentage', 'mjschool' ); ?> : </b>
						<?php
						$percentage = $total / $total_max_mark * 100;
						if ( ! empty( $percentage ) ) {
							echo number_format( $percentage, 2 );
						} else {
							echo '-';
						}
						?>
					</div>
					<div style="float:right;width: 20%;margin: 0px;">
						<b style="text-align: right;"><?php esc_html_e( 'Result', 'mjschool' ); ?> : </b>
						<?php
						$result  = array();
						$result1 = array();
						foreach ( $subject as $sub ) {
							if ( $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
								$result[] = 'pass';
							} else {
								$result1[] = 'fail';
							}
						}
						if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
							esc_html_e( 'Fail', 'mjschool' );
						} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
							esc_html_e( 'Pass', 'mjschool' );
						} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
							esc_html_e( 'Fail', 'mjschool' );
						} else {
							echo '-';
						}
						?>
					</div>
				</div>
			</div>
			<hr>
			<div  style="border: 2px solid; width:96.6%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
				<!-- Teacher's Comment (Left Side). -->
				<div style="float: left; width: 33.33%;">
					<div style="margin-left: 20px;">
						<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong>
						<p><?php echo esc_html( sanitize_textarea_field(wp_unslash($_REQUEST['comment'])) ); ?></p>
					</div>
				</div>
				<!-- Teacher Signature (Middle). -->
				<div style="float: left; width: 33.33%; text-align: center; padding-top: 0px;">
					<?php
					if ( ! empty( $signature_url ) ) {
						 ?>
						<div>
							<img src="<?php echo esc_url($signature_url); ?>" style="width:100px;height:45px;" />
						</div>
						<?php
					}
					else
					{
						?>
						<div>
							<div style="width:100px;height:45px;"></div>
						</div>
						<?php 
					} ?>
					<div class="mjschool_fees_width_150px"></div>
					<div class="mjschool_margin_top_5px">
						<?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?>
					</div>
				</div>
				<!-- Principal Signature (Right Side). -->
				<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
					<div>
						<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
					</div>
					
					<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
					<div style="margin-right:10px; margin-bottom:10px;">
						<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
					</div>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="container" style="margin-bottom:8px;">
				<div style="border: 2px solid;">
					<div style="padding:20px;">
						<div class="mjschool_float_left_width_100">
							<div style="float:left;width:30%;">
								<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
									<div style="background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
								</div>
							</div>
							<div style="float:left; width:70%;padding-top:30px;">
								<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
								<p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
								<div class="mjschool_fees_center_margin_0px"><p class="mjschool_fees_width_fit_content_inline"><?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?></p></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:97%;padding:20px;margin-top:10px;">
				<div class="mjschool_float_left_width_100">
					<div  class="mjschool_padding_10px">
						<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Student Name', 'mjschool' ); ?></b>: <?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?></div>
						<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></b>:
							<?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?>
						</div>
					</div>
				</div>
				<div class="mjschool_float_width_css" >
					<div  class="mjschool_padding_10px">
						<div class="mjschool_float_width_css" ><b><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></b>:
							<?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?>
						</div>
					</div>
				</div>
				<div style="float:right;width:50%;">
					<?php if ( $school_type === 'school' ) { ?>
						<div  style="padding-top:10px;">
							<b><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></b>:
							<?php
							$classname    = mjschool_get_class_name( $class_id );
							$section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
							echo esc_html( $classname ) . ' - ' . esc_html( $section_name );
							?>
						</div>
					<?php } ?>
					<?php if ( $school_type === 'university' ) { ?>
						<div  style="padding-top:10px;">
							<b><?php esc_html_e( 'Class Name', 'mjschool' ); ?></b>:
							<?php
							$classname    = mjschool_get_class_name( $class_id );
							// $section_name = ! empty( $section_id ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
							echo esc_html( $classname );
							?>
						</div>
					<?php } ?>
				</div>
			</div>
			<table style="float:left;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
				<thead>
					<?php
					$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
					$exam_marks    = $exam_data->total_mark;
					$contributions = $exam_data->contributions;
					if ( $contributions === 'yes' ) {
						$contributions_data       = $exam_data->contributions_data;
						$contributions_data_array = json_decode( $contributions_data, true );
					}
					?>
					<tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Subjects', 'mjschool' ); ?></th>
						<?php
						if ( $contributions === 'yes' ) {
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								?>
								<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php echo esc_html( $con_value['label'] ) . ' ( ' . esc_html( $con_value['mark'] ) . ' )'; ?></th>
								<?php
							}
							?>
							<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?></th>
							<?php
						} else {
							if ( $school_type === 'school' )
							{
								?>
								<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?></th>
								<?php
							}elseif ( $school_type === 'university' )
							{
								?>
								<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
								<?php
							}
						}
						?>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( $school_type === 'school' ) {
						$i               = 1;
						$total_pass_mark = 0;
						$total_max_mark  = 0;
						foreach ( $subject as $sub ) {
							$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
							?>
							<tr style="border-bottom: 1px solid #000;">
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $sub->sub_name ); ?></td>
								<?php
								$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
								if ( $contributions === 'yes' ) {
									$subject_total = 0;
									foreach ( $contributions_data_array as $con_id => $con_value ) {
										$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
										$subject_total += $mark_value;
										?>
										<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $mark_value ); ?> </td>
										<?php
									}
									?>
									<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $subject_total ); ?> </td>
									<?php
								} else {
									?>
									<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obtain_marks ); ?> </td>
									<?php
								}
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
							</tr>
							<?php
							++$i;
							if ( $contributions === 'yes' ) {
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
								}
							} else {
								$total_marks += $obtain_marks;
							}
							$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
						}
						$total += $total_marks;
						$total_max_mark = $exam_marks * $total_subject;
						$GPA            = $grade_point / $total_subject;
						if( ! empty( $total) && !empty($total_max_mark ) )
						{
							$percentage = $total / $total_max_mark * 100;
						}
					} elseif ( $school_type === 'university' ) {
						$i               = 1;
						$total_pass_mark = 0;
						$total_max_mark  = 0;
						$exam_subject_data = json_decode($exam_data->subject_data,true);
						$exam_subject_lookup = [];
						foreach ($exam_subject_data as $exam_sub) {
							$exam_subject_lookup[$exam_sub['subject_id']] = $exam_sub;
						}
						foreach ( $subject as $sub ) {
							$total_pass_mark += $obj_mark->mjschool_get_pass_marks( $exam_id );
							$max_marks = isset($exam_subject_lookup[$sub->subid]) ? $exam_subject_lookup[$sub->subid]['max_marks'] : 'N/A';

							//filter students for the current subject.
							$assigned_student_ids = array_map( 'intval', explode( ',', $sub->selected_students ) );
							$current_student_id   = (int) $user->ID;
							
							if (!in_array($current_student_id, $assigned_student_ids, true ) ) {
								continue; // Skip students not assigned to this subject.
							}
							//if subject is not in exam.
							if ( $sub->subid != $exam_subject_lookup[$sub->subid]['subject_id'] )
							{
								continue;
							}
							?>
							<tr style="border-bottom: 1px solid #000;">
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $sub->sub_name ); ?></td>
								<?php
								$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
								if ( $contributions === 'yes' ) {
									$subject_total = 0;
									foreach ( $contributions_data_array as $con_id => $con_value ) {
										$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
										$subject_total += $mark_value;
										?>
										<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $mark_value ); ?> </td>
										<?php
									}
									?>
									<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $subject_total ); ?> </td>
									<?php
								} else {
									?>
									<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obtain_marks) ." / ". esc_html( $max_marks); ?> </td>
									<?php
									$total_max_mark +=$max_marks;
								}
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
							</tr>
							<?php
							++$i;
							if ( $contributions === 'yes' ) {
								foreach ( $contributions_data_array as $con_id => $con_value ) {
									$total_marks += is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
								}
							} else {
								$total_marks += $obtain_marks;
							}
							$grade_point += $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
						}
						$total += $total_marks;
						$GPA            = $grade_point / $total_subject;
						if( ! empty( $total) && !empty($total_max_mark ) )
						{
							$percentage = $total / $total_max_mark * 100;
						}
					}
					?>
				</tbody>
			</table>
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
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php
							if ( ! empty( $percentage ) ) {
								echo number_format( $percentage, 2, '.', '' );
							} else {
								echo '-';
							}
							?>
						</td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( round( $GPA, 2 ) ); ?></td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;">
							<?php
							if ( $school_type != 'university' ){
								$result  = array();
								$result1 = array();
								foreach ( $subject as $sub ) {
									$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
									if ( $contributions === 'yes' ) {
										$subject_total = 0;
										foreach ( $contributions_data_array as $con_id => $con_value ) {
											$mark_value = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
											$subject_total += $mark_value;
										}
										$marks_total = $subject_total;
									} else {
										$marks_total = $obtain_marks;
									}
									if ( $marks_total >= $obj_mark->mjschool_get_pass_marks( $exam_id ) ) {
										$result[] = 'pass';
									} else {
										$result1[] = 'fail';
									}
								}
								if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
									esc_html_e( 'Fail', 'mjschool' );
								} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
									esc_html_e( 'Pass', 'mjschool' );
								} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
									esc_html_e( 'Fail', 'mjschool' );
								} else {
									echo '-';
								}
							}elseif ( $school_type === 'university' ){
								$result = array();
								$rest1  = array();
								foreach ( $subject as $sub ) {
									$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ?? 0;
									if ( $obtain_marks >= $exam_subject_lookup[$sub->subid]['passing_marks'] ) {
										$result[] = 'pass';
									} else {
										$result1[] = 'fail';
									}
								}
								if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
									esc_html_e( 'Fail', 'mjschool' );
								} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
									esc_html_e( 'Pass', 'mjschool' );
								} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
									esc_html_e( 'Fail', 'mjschool' );
								} else {
									echo '-';
								}
							}
							?>
						</td>
					</tr>
				</tbody>
			</table>
			<div  style="border: 2px solid; width:96.6%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;">
				<!-- Teacher's Comment (Left Side). -->
				<div style="float: left; width: 33.33%;">
					<div style="margin-left: 20px;">
						<strong><?php esc_html_e( "Teacher's Comment", 'mjschool' ); ?>:</strong>
						<p><?php echo esc_html( sanitize_textarea_field(wp_unslash($_REQUEST['comment'])) ); ?></p>
					</div>
				</div>
				<!-- Teacher Signature (Middle). -->
				<div style="float: left; width: 33.33%; text-align: center; padding-top: 0px;">
					<?php
					if ( ! empty( $signature_url ) ) {
						 ?>
						<div>
							<img src="<?php echo esc_url($signature_url); ?>" style="width:100px;height:45px;" />
						</div>
						<?php
					}
					else
					{
						?>
						<div>
							<div style="width:100px;height:45px;"></div>
						</div>
						<?php
				 	} ?>
					<div class="mjschool_fees_width_150px"></div>
					<div class="mjschool_margin_top_5px">
						<?php esc_html_e( 'Teacher Signature', 'mjschool' ); ?>
					</div>
				</div>
				<!-- Principal Signature (Right Side). -->
				<div style="float: left; width: 30%; text-align: right; padding-right: 20px;">
					<div>
						<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" />
					</div>
					
					<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 5px auto;"></div>
					<div style="margin-right:10px; margin-bottom:10px;">
						<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
					</div>
				</div>
			</div>
			<?php
		}
		$out_put = ob_get_contents();
		
		wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ) );
		
		ob_clean();
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="result"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$stylesheet1 = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css' ); // Get css content
		$mpdf        = new \Mpdf\Mpdf(
			array(
				'mode'          => 'utf-8',
				'format'        => 'A4',
				'orientation'   => 'P',
				'margin_left'   => 8,   // default is 15.
				'margin_right'  => 8,  // default is 15.
				'margin_top'    => 10,
				'margin_bottom' => 10,
			)
		);
		$mpdf->SetTitle( 'Result' );
		$mpdf->SetDisplayMode( 'fullwidth' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		if ( is_rtl() ) {
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			$mpdf->SetDirectionality( 'rtl' );
		}
		$mpdf->WriteHTML( $stylesheet1, 1 ); // Writing style to pdf.
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		unset( $out_put );
		unset( $mpdf );
		die();
	}
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' && isset( $_REQUEST['invoice_type'] ) ) {
		mjschool_student_invoice_pdf( sanitize_text_field(wp_unslash($_REQUEST['invoice_id'])), sanitize_text_field(wp_unslash($_REQUEST['invoice_type'])) );
		$out_put = ob_get_contents();
		ob_clean();
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="result"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf = new Mpdf\Mpdf();
		$mpdf->SetTitle( 'Payment' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		if ( is_rtl() ) {
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			$mpdf->SetDirectionality( 'rtl' );
		}
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		unset( $out_put );
		unset( $mpdf );
		die();
	}
	// lol.
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) ==='pdf' && isset( $_REQUEST['certificate_id'] ) ) {
		$certificate_id    = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['certificate_id'])) ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = mjschool_get_certificate_by_id($certificate_id );
		if ( $result && ! empty( $result->certificate_content ) ) {
			require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
			$mpdf = new \Mpdf\Mpdf();
			$mpdf->SetTitle( 'Transfer Certificate' );
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			if ( is_rtl() ) {
				$mpdf->SetDirectionality( 'rtl' );
			}
			// Get certificate content.
			$certificate_html = stripslashes( $result->certificate_content );
			// If checkbox is checked, prepend the header HTML.
			if ( isset( $_REQUEST['certificate_header'] ) && sanitize_text_field(wp_unslash($_REQUEST['certificate_header'])) === '1' ) {
				ob_start();
				?>
				<div class="container" style="margin-bottom:12px;">
					<div style="border: 2px solid;">
						<div style="padding:20px;">
							<div class="mjschool_float_left_width_100">
								<div class="mjschool_float_left_width_25">
									<div style="float:left;border-radius:50px;">
										<div style="background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 120px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;"></div>
									</div>
								</div>
								<div style="float:left; width:74%;font-size:24px;">
									<p class="mjschool_fees_widht_100_fonts_24px"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
									<p class="mjschool_fees_center_fonts_17px"><?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
									<div class="mjschool_fees_center_margin_0px">
										<p style="margin: 0px;width: fit-content;font-size: 16px;display: inline-block;">
											<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;
											<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$header_html      = ob_get_clean();
				$certificate_html = $header_html . $certificate_html;
			}
			// Output PDF
			$mpdf->WriteHTML( $certificate_html );
			$mpdf->Output( 'transfer_certificate.pdf', 'I' );
			die();
		}
	}
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' && isset( $_REQUEST['fee_paymenthistory'] ) ) {
		?>

		<?php
		mjschool_student_payment_history_pdf( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) );
		$out_put = ob_get_contents();
		ob_clean();
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="feepaymenthistory"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf = new Mpdf\Mpdf();
		$mpdf->SetTitle( 'Fees Payment' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		if ( is_rtl() ) {
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			$mpdf->SetDirectionality( 'rtl' );
		}
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		unset( $out_put );
		unset( $mpdf );
		die();
	}
	if ( isset( $_REQUEST['print'] ) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' && isset( $_REQUEST['fee_receipthistory'] ) ) {
		?>
	
		<?php
		mjschool_student_receipt_history_pdf( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])), sanitize_text_field(wp_unslash($_REQUEST['receipt_id'])) );
		$out_put = ob_get_contents();
		ob_clean();
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="receiptpayment"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf = new Mpdf\Mpdf();
		$mpdf->SetTitle( 'Receipt Payment' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		if ( is_rtl() ) {
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			$mpdf->SetDirectionality( 'rtl' );
		}
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		unset( $out_put );
		unset( $mpdf );
		die();
	}
	if ( isset( $_REQUEST['student_exam_receipt_pdf'] ) && sanitize_text_field(wp_unslash($_REQUEST['student_exam_receipt_pdf'])) === 'student_exam_receipt_pdf' ) {
		mjschool_student_exam_receipt_pdf( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ), mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) ) );
		$out_put = ob_get_contents();
		ob_clean();
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: inline; filename="examreceipt"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf = new Mpdf\Mpdf();
		$mpdf->SetTitle( 'Hall Ticket' );
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		$mpdf->WriteHTML( $out_put );
		$mpdf->Output();
		unset( $out_put );
		unset( $mpdf );
		die();
	}
}
/**
 * Authenticate a user, confirming the username and password are valid.
 *
 * @since 2.8.0
 *
 * @param WP_User|WP_Error|null $user     WP_User or WP_Error object from a previous callback. Default null.
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
			$error->add( 'empty_username', esc_html( '<strong>ERROR</strong>: The username field is empty.' ) );
		}
		if ( empty( $password ) ) {
			$error->add( 'empty_password', esc_html( '<strong>ERROR</strong>: The password field is empty.' ) );
		}
		return $error;
	}
	$user = get_user_by( 'login', $username );
	/**
	 * Filters whether the given user can be authenticated with the provided $password.
	 *
	 * @since 2.5.0
	 *
	 * @param WP_User|WP_Error $user     WP_User or WP_Error object if a previous
	 *                                   callback failed authentication.
	 * @param string           $password Password to check against the user.
	 */
	$user = apply_filters( 'wp_authenticate_user', $user, $password );
	if ( is_wp_error( $user ) ) {
		return $user;
	}
	return $user;
}
/**
 * Extends the WordPress login cookie expiration time.
 *
 * This function sets the authentication cookie duration to 2 hours
 * instead of the default WordPress duration, effectively keeping
 * users logged in longer.
 *
 * @param int $expirein Original cookie expiration time in seconds.
 * @return int Modified cookie expiration time in seconds (7200 = 2 hours).
 *
 * @since 1.0.0
 */
add_filter( 'auth_cookie_expiration', 'mjschool_keep_me_logged_in_60_minutes' );
function mjschool_keep_me_logged_in_60_minutes( $expirein ) {
	return 7200; // 2 hours.
}
/**
 * Disables autocomplete on WordPress login page fields.
 *
 * This action modifies the login form HTML to add `autocomplete="off"` 
 * to both the username and password input fields. It is executed with a
 * very high priority to ensure it runs after the default login form is generated.
 *
 * @since 1.0.0
 */
add_action(
	'login_form',
	function ( $args ) {
		$login = ob_get_contents();
		ob_clean();
		$login = str_replace( 'id="user_pass"', 'id="user_pass" autocomplete="off"', $login );
		$login = str_replace( 'id="user_login"', 'id="user_login" autocomplete="off"', $login );
		echo $login; //phpcs:ignore
	},
	9999
);
/**
 * Creates a "Student Admission" page if it doesn't already exist.
 *
 * This function checks if the option 'mjschool_student_admission_page' is set.
 * If not, it creates a new WordPress page with the title "Student Admission"
 * and inserts the [smgt_student_admission] shortcode as its content. The page ID
 * is then saved in the WordPress options table for future reference.
 *
 * @since 1.0.0
 */
function mjschool_install_student_admission_page() {
	if ( ! get_option( 'mjschool_student_admission_page' ) ) {
		$curr_page    = array(
			'post_title'     => esc_attr__( 'Student Admission', 'mjschool' ),
			'post_content'   => '[smgt_student_admission]',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_category'  => array(1),
			'post_parent'    => 0,
		);
		$curr_created = wp_insert_post( $curr_page );
		update_option( 'mjschool_student_admission_page', $curr_created );
	}
}
/**
 * Creates a "Student Registration Form" page if it doesn't already exist.
 *
 * This function checks if the option 'mjschool_student_combine_admission_page' exists.
 * If not, it creates a new WordPress page titled "Student Registration Form"
 * and inserts the [smgt_student_combine_admission] shortcode as its content.
 * The page ID is then saved in the WordPress options table for future reference.
 *
 * @since 1.0.0
 */
function mjschool_install_combine_admission_page() {
	if ( ! get_option( 'mjschool_student_combine_admission_page' ) ) {
		$curr_page    = array(
			'post_title'     => esc_attr__( 'Student Registration Form', 'mjschool' ),
			'post_content'   => '[smgt_student_combine_admission]', // ⬅ Your new shortcode
			'post_name'      => 'student-registration-form',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_category'  => array(1),
			'post_parent'    => 0,
		);
		$curr_created = wp_insert_post( $curr_page );
		update_option( 'mjschool_student_combine_admission_page', $curr_created );
	}
}
/**
 * Handles the frontend student admission process.
 *
 * This function manages the entire student admission workflow on the frontend, including:
 * 1. Form submission detection and nonce verification for security.
 * 2. Validation of student and parent email addresses.
 * 3. File uploads for parent documents and user avatar.
 * 4. Sanitization of all user input to ensure data integrity.
 * 5. Construction of sibling and document arrays for storage.
 * 6. Password generation or validation.
 * 7. Calls `mjschool_complete_admission` to insert the student and parent data into the system.
 * 8. Renders the admission form with previously entered values for user convenience.
 *
 * @since 1.0.0
 */
function mjschool_student_admission_function() {
	global $mjschool_admission_no, $mjschool_class, $mjschool_admission_date, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_birth_date, $mjschool_gender, $mjschool_address, $mjschool_state_name, $mjschool_city_name, $mjschool_zip_code, $mjschool_phone_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_preschool_name, $mjschool_user_avatar, $mjschool_sibling_information, $mjschool_p_status, $mjschool_fathersalutation, $mjschool_father_first_name, $mjschool_father_middle_name, $mjschool_father_last_name, $mjschool_fathe_gender, $mjschool_father_birth_date, $mjschool_father_address, $mjschool_father_city_name, $mjschool_father_state_name, $mjschool_father_zip_code, $mjschool_father_email, $mjschool_father_mobile, $mjschool_father_school, $mjschool_father_medium, $mjschool_father_education, $mjschool_fathe_income, $mjschool_father_occuption, $mjschool_father_doc, $mjschool_mothersalutation, $mjschool_mother_first_name, $mjschool_mother_middle_name, $mjschool_mother_last_name, $mjschool_mother_gender, $mjschool_mother_birth_date, $mjschool_mother_address, $mjschool_mother_city_name, $mjschool_mother_state_name, $mjschool_mother_zip_code, $mjschool_mother_email, $mjschool_mother_mobile, $mjschool_mother_school, $mjschool_mother_medium, $mjschool_mother_education, $mjschool_mother_income, $mjschool_mother_occuption, $mjschool_mother_doc, $mjschool_admission_fees;
	if ( isset( $_POST['save_student_front_admission'] ) ) {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'save_student_frontend_admission_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'mjschool' ) );
		}
		mjschool_admission_validation(
			sanitize_email(wp_unslash($_POST['email'])),
			sanitize_email(wp_unslash($_POST['email'])),
			sanitize_email(wp_unslash($_POST['father_email'])),
			sanitize_email(wp_unslash($_POST['mother_email']))
		);
		// sanitize user form input
		global $mjschool_admission_no, $mjschool_class, $mjschool_admission_date, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_birth_date, $mjschool_gender, $mjschool_address, $mjschool_state_name, $mjschool_city_name, $mjschool_zip_code, $mjschool_phone_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_preschool_name, $mjschool_user_avatar, $mjschool_sibling_information, $mjschool_p_status, $mjschool_fathersalutation, $mjschool_father_first_name, $mjschool_father_middle_name, $mjschool_father_last_name, $mjschool_fathe_gender, $mjschool_father_birth_date, $mjschool_father_address, $mjschool_father_city_name, $mjschool_father_state_name, $mjschool_father_zip_code, $mjschool_father_email, $mjschool_father_mobile, $mjschool_father_school, $mjschool_father_medium, $mjschool_father_education, $mjschool_fathe_income, $mjschool_father_occuption, $mjschool_father_doc, $mjschool_mothersalutation, $mjschool_mother_first_name, $mjschool_mother_middle_name, $mjschool_mother_last_name, $mjschool_mother_gender, $mjschool_mother_birth_date, $mjschool_mother_address, $mjschool_mother_city_name, $mjschool_mother_state_name, $mjschool_mother_zip_code, $mjschool_mother_email, $mjschool_mother_mobile, $mjschool_mother_school, $mjschool_mother_medium, $mjschool_mother_education, $mjschool_mother_income, $mjschool_mother_occuption, $mjschool_mother_doc, $mjschool_admission_fees;
		$sibling_value = array();
		if ( isset( $_FILES['father_doc'] ) && ! empty( $_FILES['father_doc'] ) && $_FILES['father_doc']['size'] != 0 ) {
			if ( $_FILES['father_doc']['size'] > 0 ) {
				$mjschool_upload_docs = mjschool_load_documets_new( $_FILES['father_doc'], $_FILES['father_doc'], sanitize_text_field(wp_unslash($_POST['father_document_name'])) );
			}
		} else {
			$mjschool_upload_docs = '';
		}
		$mjschool_father_document_data = array();
		if ( ! empty( $mjschool_upload_docs ) ) {
			$mjschool_father_document_data[] = array(
				'title' => sanitize_text_field(wp_unslash($_POST['father_document_name'])),
				'value' => $mjschool_upload_docs,
			);
		} else {
			$mjschool_father_document_data[] = '';
		}
		if ( isset( $_FILES['mother_doc'] ) && ! empty( $_FILES['mother_doc'] ) && $_FILES['mother_doc']['size'] != 0 ) {
			if ( $_FILES['mother_doc']['size'] > 0 ) {
				$mjschool_upload_docs1 = mjschool_load_documets_new( $_FILES['mother_doc'], $_FILES['mother_doc'], 'mother_doc' );
			}
		} else {
			$mjschool_upload_docs1 = '';
		}
		$mjschool_mother_document_data = array();
		if ( ! empty( $mjschool_upload_docs1 ) ) {
			$mjschool_mother_document_data[] = array(
				'title' => sanitize_text_field(wp_unslash($_POST['mother_document_name'])),
				'value' => $mjschool_upload_docs1,
			);
		} else {
			$mjschool_mother_document_data[] = '';
		}
		if ( isset( $_POST['mjschool_user_avatar'] ) && $_POST['mjschool_user_avatar'] != '' ) {
			$mjschool_photo = sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar']));
		} else {
			$mjschool_photo = '';
		}
		if ( $_POST['password'] != '' ) {
			$mjschool_user_pass = mjschool_password_validation( sanitize_text_field(wp_unslash($_POST['password'])) );
		} else {
			$mjschool_user_pass = wp_generate_password();
		}
		$mjschool_sibling_value = array();
		if ( ! empty( $_POST['siblingsclass'] ) ) {
			foreach ( $_POST['siblingsclass'] as $key => $value ) {
				$mjschool_sibling_value[] = array(
					'siblingsclass'   => sanitize_text_field( $value ),
					'siblingssection' => sanitize_text_field( wp_unslash($_POST['siblingssection'][ $key ]) ),
					'siblingsstudent' => sanitize_text_field( wp_unslash($_POST['siblingsstudent'][ $key ]) ),
				);
			}
		}
		$mjschool_admission_no   = sanitize_text_field( wp_unslash($_POST['admission_no']) );
		$mjschool_class          = sanitize_text_field( wp_unslash($_POST['class_name']) );
		$mjschool_admission_date = sanitize_text_field( wp_unslash($_POST['admission_date']) );
		$mjschool_first_name     = sanitize_text_field( wp_unslash($_POST['first_name']) );
		$mjschool_middle_name    = sanitize_text_field( wp_unslash($_POST['middle_name']) );
		$mjschool_last_name      = sanitize_text_field( wp_unslash($_POST['last_name']) );
		$mjschool_birth_date     = sanitize_text_field( wp_unslash($_POST['birth_date']) );
		$mjschool_gender         = sanitize_text_field( wp_unslash($_POST['gender']) );
		$mjschool_address        = sanitize_textarea_field( wp_unslash($_POST['address']) );
		$mjschool_state_name     = sanitize_text_field( wp_unslash($_POST['state_name']) );
		$mjschool_city_name      = sanitize_text_field( wp_unslash($_POST['city_name']) );
		$mjschool_zip_code       = sanitize_text_field( wp_unslash($_POST['zip_code']) );
		$mjschool_phone_code     = wp_unslash($_POST['phone_code']);
		$mjschool_mobile_number  = sanitize_text_field( wp_unslash($_POST['mobile_number']) );
		$mjschool_email               = sanitize_email( wp_unslash($_POST['email']) );
		$mjschool_username            = sanitize_email( wp_unslash($_POST['email']) );
		$mjschool_password            = $user_pass;
		$mjschool_preschool_name      = sanitize_text_field(wp_unslash($_POST['preschool_name']));
		$mjschool_user_avatar    = $photo;
		$mjschool_sibling_information = $sibling_value;
		$mjschool_p_status            = sanitize_text_field(wp_unslash($_POST['pstatus']));
		$mjschool_fathersalutation    = sanitize_text_field( wp_unslash($_POST['fathersalutation']) );
		$mjschool_father_first_name   = sanitize_text_field( wp_unslash($_POST['father_first_name']) );
		$mjschool_father_middle_name  = sanitize_text_field( wp_unslash($_POST['father_middle_name']) );
		$mjschool_father_last_name    = sanitize_text_field( wp_unslash($_POST['father_last_name']) );
		$mjschool_fathe_gender        = sanitize_text_field(wp_unslash($_POST['fathe_gender']));
		$mjschool_father_birth_date   = sanitize_text_field(wp_unslash($_POST['father_birth_date']));
		$mjschool_father_address      = sanitize_textarea_field( wp_unslash($_POST['father_address']) );
		$mjschool_father_city_name    = sanitize_text_field( wp_unslash($_POST['father_city_name']) );
		$mjschool_father_state_name   = sanitize_text_field( wp_unslash($_POST['father_state_name']) );
		$mjschool_father_zip_code     = sanitize_text_field( wp_unslash($_POST['father_zip_code']) );
		$mjschool_father_email        = sanitize_email( wp_unslash($_POST['father_email']) );
		$mjschool_father_mobile       = sanitize_text_field( wp_unslash($_POST['father_mobile']) );
		$mjschool_father_school       = sanitize_text_field( wp_unslash($_POST['father_school']) );
		$mjschool_father_medium       = sanitize_text_field(wp_unslash($_POST['father_medium']));
		$mjschool_father_education    = sanitize_text_field(wp_unslash($_POST['father_education']));
		$mjschool_fathe_income        = sanitize_text_field(wp_unslash($_POST['fathe_income']));
		$mjschool_father_occuption    = sanitize_text_field(wp_unslash($_POST['father_occuption']));
		$mjschool_father_doc          = json_encode( $father_document_data );
		$mjschool_mothersalutation    = sanitize_text_field( wp_unslash($_POST['mothersalutation']) );
		$mjschool_mother_first_name   = sanitize_text_field( wp_unslash($_POST['mother_first_name']) );
		$mjschool_mother_middle_name  = sanitize_text_field( wp_unslash($_POST['mother_middle_name']) );
		$mjschool_mother_last_name    = sanitize_text_field( wp_unslash($_POST['mother_last_name']) );
		$mjschool_mother_gender       = sanitize_text_field( wp_unslash($_POST['mother_gender']) );
		$mjschool_mother_birth_date   = sanitize_text_field( wp_unslash($_POST['mother_birth_date']) );
		$mjschool_mother_address      = sanitize_textarea_field( wp_unslash($_POST['mother_address']) );
		$mjschool_mother_city_name    = sanitize_text_field( wp_unslash($_POST['mother_city_name']) );
		$mjschool_mother_state_name   = sanitize_text_field( wp_unslash($_POST['mother_state_name']) );
		$mjschool_mother_zip_code     = sanitize_text_field( wp_unslash($_POST['mother_zip_code']) );
		$mjschool_mother_email        = sanitize_email( wp_unslash($_POST['mother_email']) );
		$mjschool_mother_mobile       = sanitize_text_field( wp_unslash($_POST['mother_mobile']) );
		$mjschool_mother_school       = sanitize_text_field( wp_unslash($_POST['mother_school']) );
		$mjschool_mother_medium       = sanitize_text_field(wp_unslash($_POST['mother_medium']));
		$mjschool_mother_education    = sanitize_text_field(wp_unslash($_POST['mother_education']));
		$mjschool_mother_income       = sanitize_text_field(wp_unslash($_POST['mother_income']));
		$mjschool_mother_occuption    = sanitize_text_field(wp_unslash($_POST['mother_occuption']));
		$mjschool_mother_doc          = json_encode( $mother_document_data );
		$wp_nonce                     = $_POST['_wpnonce'];
		$mjschool_admission_fees      = wp_unslash($_POST['admission_fees']);
		$mjschool_register_fees       = wp_unslash($_POST['registration_fees']);
		// call @function smgt_complete_admission to create the user.
		// only when no WP_error is found.
		mjschool_complete_admission( $mjschool_admission_no, $mjschool_class, $mjschool_admission_date, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_birth_date, $mjschool_gender, $mjschool_address, $mjschool_state_name, $mjschool_city_name, $mjschool_zip_code, $mjschool_phone_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_preschool_name, $mjschool_user_avatar, $mjschool_sibling_information, $mjschool_p_status, $mjschool_fathersalutation, $mjschool_father_first_name, $mjschool_father_middle_name, $mjschool_father_last_name, $mjschool_fathe_gender, $mjschool_father_birth_date, $mjschool_father_address, $mjschool_father_city_name, $mjschool_father_state_name, $mjschool_father_zip_code, $mjschool_father_email, $mjschool_father_mobile, $mjschool_father_school, $mjschool_father_medium, $mjschool_father_education, $mjschool_fathe_income, $mjschool_father_occuption, $mjschool_father_doc, $mjschool_mothersalutation, $mjschool_mother_first_name, $mjschool_mother_middle_name, $mjschool_mother_last_name, $mjschool_mother_gender, $mjschool_mother_birth_date, $mjschool_mother_address, $mjschool_mother_city_name, $mjschool_mother_state_name, $mjschool_mother_zip_code, $mjschool_mother_email, $mjschool_mother_mobile, $mjschool_mother_school, $mjschool_mother_medium, $mjschool_mother_education, $mjschool_mother_income, $mjschool_mother_occuption, $mjschool_mother_doc, $wp_nonce, $mjschool_admission_fees, $mjschool_register_fees );
	}
	mjschool_admission_form( $mjschool_admission_no, $mjschool_class, $mjschool_admission_date, $mjschool_first_name, $mjschool_middle_name, $mjschool_last_name, $mjschool_birth_date, $mjschool_gender, $mjschool_address, $mjschool_state_name, $mjschool_city_name, $mjschool_zip_code, $mjschool_phone_code, $mjschool_mobile_number, $mjschool_alternet_mobile_number, $mjschool_email, $mjschool_username, $mjschool_password, $mjschool_preschool_name, $mjschool_user_avatar, $mjschool_sibling_information, $mjschool_p_status, $mjschool_fathersalutation, $mjschool_father_first_name, $mjschool_father_middle_name, $mjschool_father_last_name, $mjschool_fathe_gender, $mjschool_father_birth_date, $mjschool_father_address, $mjschool_father_city_name, $mjschool_father_state_name, $mjschool_father_zip_code, $mjschool_father_email, $mjschool_father_mobile, $mjschool_father_school, $mjschool_father_medium, $mjschool_father_education, $mjschool_fathe_income, $mjschool_father_occuption, $mjschool_father_doc, $mjschool_mothersalutation, $mjschool_mother_first_name, $mjschool_mother_middle_name, $mjschool_mother_last_name, $mjschool_mother_gender, $mjschool_mother_birth_date, $mjschool_mother_address, $mjschool_mother_city_name, $mjschool_mother_state_name, $mjschool_mother_zip_code, $mjschool_mother_email, $mjschool_mother_mobile, $mjschool_mother_school, $mjschool_mother_medium, $mjschool_mother_education, $mjschool_mother_income, $mjschool_mother_occuption, $mjschool_mother_doc, $mjschool_admission_fees, $mjschool_register_fees );
}
/**
 * Renders the frontend student admission form.
 *
 * This function outputs the complete HTML form for student admissions on the frontend,
 * including student information, parent/guardian details, sibling info, document uploads,
 * and dynamically enqueued scripts and styles for validation, datepickers, and UI enhancements.
 * 
 * The form also includes theme-specific adjustments for popular WordPress themes like Divi and TwentyTwenty.
 * The form uses accordion sections and applies input validation using jQuery ValidationEngine.
 *
 * @param string $admission_no           The admission number for the student.
 * @param string $class                  The class the student is applying for.
 * @param string $admission_date         The admission date.
 * @param string $first_name             Student's first name.
 * @param string $middle_name            Student's middle name.
 * @param string $last_name              Student's last name.
 * @param string $birth_date             Student's date of birth.
 * @param string $gender                 Student's gender.
 * @param string $address                Student's address.
 * @param string $state_name             State name of the student.
 * @param string $city_name              City name of the student.
 * @param string $zip_code               Zip/postal code of the student.
 * @param string $phone_code             Phone country/area code.
 * @param string $mobile_number          Student's mobile number.
 * @param string $alternet_mobile_number Alternate mobile number.
 * @param string $email                  Student's email address.
 * @param string $username               Username for the student account.
 * @param string $password               Password for the student account.
 * @param string $preschool_name         Name of the preschool (if any).
 * @param string $smgt_user_avatar       User avatar image URL or file.
 * @param array  $sibling_information    Sibling information array.
 * @param string $p_status               Parent/guardian status.
 * @param string $fathersalutation       Father's salutation (Mr., Dr., etc.).
 * @param string $father_first_name      Father's first name.
 * @param string $father_middle_name     Father's middle name.
 * @param string $father_last_name       Father's last name.
 * @param string $fathe_gender           Father's gender.
 * @param string $father_birth_date      Father's date of birth.
 * @param string $father_address         Father's address.
 * @param string $father_city_name       Father's city name.
 * @param string $father_state_name      Father's state name.
 * @param string $father_zip_code        Father's zip/postal code.
 * @param string $father_email           Father's email address.
 * @param string $father_mobile          Father's mobile number.
 * @param string $father_school          Father's school name.
 * @param string $father_medium          Father's medium of education.
 * @param string $father_education       Father's education qualification.
 * @param string $fathe_income           Father's income.
 * @param string $father_occuption       Father's occupation.
 * @param string $father_doc             Father's uploaded document(s), JSON encoded.
 * @param string $mothersalutation       Mother's salutation (Mrs., Ms., etc.).
 * @param string $mother_first_name      Mother's first name.
 * @param string $mother_middle_name     Mother's middle name.
 * @param string $mother_last_name       Mother's last name.
 * @param string $mother_gender          Mother's gender.
 * @param string $mother_birth_date      Mother's date of birth.
 * @param string $mother_address         Mother's address.
 * @param string $mother_city_name       Mother's city name.
 * @param string $mother_state_name      Mother's state name.
 * @param string $mother_zip_code        Mother's zip/postal code.
 * @param string $mother_email           Mother's email address.
 * @param string $mother_mobile          Mother's mobile number.
 * @param string $mother_school          Mother's school name.
 * @param string $mother_medium          Mother's medium of education.
 * @param string $mother_education       Mother's education qualification.
 * @param string $mother_income          Mother's income.
 * @param string $mother_occuption       Mother's occupation.
 * @param string $mother_doc             Mother's uploaded document(s), JSON encoded.
 * @param string $admission_fees         Admission fees.
 * @param string $register_fees          Registration fees.
 *
 * @return void
 *
 * @since 1.0.0
 */
function mjschool_admission_form( $admission_no, $class, $admission_date, $first_name, $middle_name, $last_name, $birth_date, $gender, $address, $state_name, $city_name, $zip_code, $phone_code, $mobile_number, $alternet_mobile_number, $email, $username, $password, $preschool_name, $smgt_user_avatar, $sibling_information, $p_status, $fathersalutation, $father_first_name, $father_middle_name, $father_last_name, $fathe_gender, $father_birth_date, $father_address, $father_city_name, $father_state_name, $father_zip_code, $father_email, $father_mobile, $father_school, $father_medium, $father_education, $fathe_income, $father_occuption, $father_doc, $mothersalutation, $mother_first_name, $mother_middle_name, $mother_last_name, $mother_gender, $mother_birth_date, $mother_address, $mother_city_name, $mother_state_name, $mother_zip_code, $mother_email, $mother_mobile, $mother_school, $mother_medium, $mother_education, $mother_income, $mother_occuption, $mother_doc, $admission_fees, $register_fees ) {
	
	wp_enqueue_style( 'mjschool-inputs', plugins_url( '/assets/css/mjschool-inputs.css', __FILE__ ) );
	wp_enqueue_media();
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_script( 'jquery-validationEngine', plugins_url( '/lib/validationEngine/js/jquery.validationEngine.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_script( 'jquery-validationEngine' );
	$lancode = get_locale();
	$code = substr( $lancode, 0, 2 );
	
	wp_enqueue_style( 'validationEngine-jquery', plugins_url( '/lib/validationEngine/css/validationEngine.jquery.css', __FILE__ ) );
	wp_register_script( 'jquery-validationEngine-' . $code . '', plugins_url( '/lib/validationEngine/js/languages/jquery.validationEngine-' . $code . '.js', __FILE__), array( 'jquery' ) );
	wp_enqueue_script( 'jquery-validationEngine-' . $code . '' );
	//wp_enqueue_style( 'mjschool-dynamic', plugins_url( '/assets/css/mjschool-dynamic-css.php', __FILE__ ) );
	wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_script( 'material', plugins_url( '/assets/js/third-party-js/material.min.js', __FILE__ ) );
	wp_enqueue_script( 'bootstrap', plugins_url( '/assets/js/third-party-js/bootstrap/bootstrap.min.js', __FILE__ ) );
	wp_enqueue_style( 'mjschool-responsive', plugins_url( '/assets/css/mjschool-school-responsive.css', __FILE__ ) );
	if (is_rtl( ) ) {
		wp_enqueue_style( 'mjschool-custome_rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-rtl-css', plugins_url( '/assets/css/theme/mjschool-rtl.css', __FILE__ ) );
	}
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_script( 'fontawesome', plugins_url( '/assets/js/fontawesome.min.js', __FILE__ ) );
	wp_enqueue_script( 'mjschool-custom_obj', plugins_url( '/assets/js/mjschool-custom-confilict-obj.js', __FILE__), array( 'jquery' ), '', false);
	wp_enqueue_style( 'mjschool-admission', plugins_url( '/assets/css/settings/mjschool-admission.css', __FILE__ ) );
	wp_enqueue_style( 'jquery-ui', plugins_url( '/assets/css/third-party-css/jquery-ui.min.css', __FILE__ ) );
	$document_option = get_option( 'mjschool_upload_document_type' );
	$document_type = explode( ', ', $document_option );
	$document_type_json = $document_type;
	$document_size = get_option( 'mjschool_upload_document_size' );
	$mix_data = array(
		'date_format'           => get_option('mjschool_datepicker_format'),
		'document_type_json' 	=> $document_type_json,
		'document_size'			=> $document_size,
		'document_delete_alert' => esc_html__('Are you sure you want to delete this record?','mjschool'),
		'admission_doc_alert' => esc_html__('Only pdf, doc, docx, xls, xlsx, ppt, pptx, gif, png, jpg, jpeg formats are allowed','mjschool'),
		'format_alert' => esc_html__('format is not allowed.','mjschool'),
	);
	wp_enqueue_script( 'mjschool-admission', plugins_url( '/assets/js/public-js/mjschool-admission.js', __FILE__ ) );
	wp_localize_script('mjschool-admission', 'mjschool_admission_data', $mix_data);
	wp_enqueue_script( 'mjschool-registration', plugins_url( '/assets/js/mjschool-registration.js', __FILE__ ) );
	wp_localize_script('mjschool-registration', 'mjschool_registration_data', $mix_data);
	$role = 'student_temp';
	$theme_name = get_template();

	//If active theme is twentytwentyfive, this style will apply.
	if ($theme_name === 'twentytwentyfive' ) {
		wp_enqueue_style( 'mjschool-admission-twenty-twenty-five', plugins_url( '/assets/css/theme/mjschool-admission-twenty-twenty-five-fix.css', __FILE__ ) );
	}
	wp_enqueue_style( 'mjschool-admission-new-style', plugins_url( '/assets/css/theme/mjschool-admission.css', __FILE__ ) );
	if ( is_rtl() ) {
		wp_enqueue_style( 'mjschool-admission-rtl', plugins_url( '/assets/css/theme/mjschool-admission-rtl.css', __FILE__ ) );
	}
	wp_enqueue_script( 'mjschool-popup', plugins_url( '/assets/js/mjschool-popup.js', __FILE__ ) );
	wp_localize_script( 'mjschool-popup', 'mjschool', array(
		'ajax' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'mjschool_ajax_nonce' ),
	) );
	
	?>
	<div class="<?php echo esc_attr( $theme_name ); ?>">
		<form id="mjschool-admission-form" class="mjschool-admission-form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>" />
			<input type="hidden"  name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
			<!--- Hidden User and password. --------->
			<input id="username" type="hidden" name="username">
			<input id="password" type="hidden" name="password">
			<div>
				<div>
					<div class="accordion admission_label" id="myAccordion">
						<div class="accordion-item mjschool-class-border-div">
							<h2 class="accordion-header mjschool-accordion-header-custom-css" id="headingOne">
								<button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne" style="font-weight:800;"><?php esc_html_e( 'Student Information', 'mjschool' ); ?></button>
							</h2>
							<div id="collapseOne" class="accordion-collapse collapse mjschool-theme-page-addmission-form-padding show" data-bs-parent="#myAccordion">
								<div class="card-body_1">
									<div class="form-body mjschool-user-form mjschool-padding-20px-child-theme mjschool-margin-top-15px"> <!------  Form Body -------->
										<div class="row">
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="admission_no" class="mjschool-line-height-29px-registration-from form-control validate[required] text-input" type="text" value="<?php echo esc_attr( mjschool_generate_admission_number() ); ?>" name="admission_no" readonly>
														<label for="userinput1"><?php esc_html_e( 'Admission Number', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<?php if ( get_option( 'mjschool_combine' ) === '1' ) { ?>
												<div class="col-md-12 input mjschool-error-msg-left-margin mjschool-responsive-bottom-15">
													<div class="form-group input">
														<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span></label>
														<select name="class_name" class="mjschool-line-height-27px-registration-form form-control validate[required]" id="class_name">
															<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
															<?php
															$tablename      = 'mjschool_class';
															$retrieve_class_data = mjschool_get_all_data( $tablename );
															foreach ( $retrieve_class_data as $classdata ) {
																?>
																<option value="<?php echo esc_attr( $classdata->class_id ); ?>" <?php selected( $classval, $classdata->class_id ); ?>><?php echo esc_html( $classdata->class_name ); ?></option>
																<?php
															}
															?>
														</select>
													</div>
												</div>
											<?php } ?>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="admission_date" class="mjschool-line-height-29px-registration-from form-control validate[required]" type="text" name="admission_date" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" readonly>
														<label for="userinput1"><?php esc_html_e( 'Admission Date', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<?php
											$fees      = 0;
											$fees_id   = '';
											$fee_label = '';
											$obj_fees = new Mjschool_Fees();
											if ( get_option( 'mjschool_combine' ) === '1' ) {
												// Combine mode: Use Registration Fees.
												if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
													$fees_id   = get_option( 'mjschool_registration_amount' );
													$fee_label = esc_html__( 'Registration Fees', 'mjschool' );
													$amount = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
													$fees   = $amount ? $amount : 0;
													?>
													<div class="col-md-12 mjschool-error-msg-left-margin mb-3">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="registration_fees" class="form-control" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() ) . ' ' . esc_attr( $fees ); ?>">
																<label for="registration_fees"><?php echo esc_html( $fee_label ); ?><span class="required">*</span></label>
															</div>
														</div>
													</div>
													<input class="form-control" type="hidden" name="registration_fees" value="<?php echo esc_attr( get_option( 'mjschool_registration_amount' ) ); ?>">
													<?php
												}
											} else {
												// Normal mode: Use Admission Fees.
												if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
													$fees_id   = get_option( 'mjschool_admission_amount' );
													$fee_label = esc_html__( 'Admission Fees', 'mjschool' );
													$amount = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
													$fees   = $amount ? $amount : 0;
													?>
													<div class="col-md-12 mjschool-error-msg-left-margin mb-3">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="admission_fees" class="form-control" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() ) . ' ' . esc_attr( $fees ); ?>">
																<label for="admission_fees"><?php echo esc_html( $fee_label ); ?><span class="required">*</span></label>
															</div>
														</div>
													</div>
													<input class="form-control" type="hidden" name="admission_fees" value="<?php echo esc_attr( $fees_id ); ?>">
													<?php
												}
											}
											?>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="first_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="first_name">
														<label for="userinput1"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" name="middle_name">
														<label for="userinput1"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="last_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="last_name">
														<label for="userinput1"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="birth_date" class="mjschool-line-height-29px-registration-from form-control validate[required] birth_date" type="text" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>" name="birth_date" readonly>
														<label for="userinput1"><?php esc_html_e( 'Date Of Birth', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<?php
											$genderval = 'male';
											?>
											<div class="col-md-12 mb-3">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div class="input-group">
																<label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="top:-7px !important;"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span></label>
																<div class="d-inline-block mjschool-line-height-29px-registration-from">
																	<input type="radio"  value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> checked />
																	<label style="" class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
																	&nbsp;&nbsp;<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> />
																	<label style="" class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
																	&nbsp;&nbsp;<input type="radio" value="other" class="tog validate[required]" name="gender" <?php checked( 'other', $genderval ); ?> />
																	<label style="" class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="address" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[address_description_validation]]" maxlength="150" type="text" name="address">
														<label for="userinput1"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name">
														<label for="userinput1"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="city_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name">
														<label for="userinput1"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="zip_code" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[zipcode]]" maxlength="15" type="text" name="zip_code">
														<label for="userinput1"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3 mjschool-mobile-error-massage-left-margin">
												<div class="form-group input mjschool-margin-bottom-0">
													<div class="col-md-12 form-control mjschool-mobile-input">
														<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
														<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="mjschool-line-height-29px-registration-from form-control" name="phonecode">
														<input id="phone" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number">
														<label for="userinput6" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="email" email_tpye="student_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control validate[required,custom[email]] text-input email" maxlength="100" type="text" name="email">
														<label for="userinput1"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="preschool_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="preschool_name">
														<label for="userinput1"><?php esc_html_e( 'Previous School', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="accordion-item mjschool-class-border-div">
							<h2 class="accordion-header" id="headingTwo">
								<button type="button" class="accordion-button collapsed" style="font-weight:800;" data-bs-toggle="collapse" data-bs-target="#collapseTwo"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></button>
							</h2>
							<div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#myAccordion">
								<div class="card-body_1">
									<div class="mjschool-panel-body mjschool-padding-20px-child-theme">
										<div class="form-group">
											<div class="col-md-12 col-sm-12 col-xs-12" style="display: inline-flex;" id="relationid">
												<input type="checkbox" id="chkIsTeamLead" style="margin-top:4px;" />&nbsp;&nbsp;<h4 class="admintion_page_checkbox_span front"><?php esc_html_e( 'In case of any sibling ? click here', 'mjschool' ); ?></span>
											</div>
										</div>
										<div id="mjschool-sibling-div" class="mjschool-sibling-div-none mjschool-sibling-div_clss">
											<div class="form-body mjschool-user-form">
												<div class="row">
													<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select mb-3">
														<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														<select name="siblingsclass[]" class="form-control validate[required]  mjschool-class-in-student mjschool-max-width-100px mjschool_45px"  id="mjschool-sibling-class-change">
															<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
															<?php
															$tablename      = 'mjschool_class';
															$retrieve_class_data = mjschool_get_all_data( $tablename );
															foreach ( $retrieve_class_data as $classdata ) {
																?>
																<option value="<?php echo esc_attr( $classdata->class_id ); ?>"><?php echo esc_html( $classdata->class_name ); ?></option>
																<?php
															}
															?>
														</select>
													</div>
													<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select mb-3">
														<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_name"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
														<select name="siblingssection[]" class="form-control mjschool-max-width-100px mjschool_45px" id="sibling_class_section">
															<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
														</select>
													</div>
													<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide mb-3">
														<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
														<select name="siblingsstudent[]" id="sibling_student_list" class="mjschool_45px form-control mjschool-max-width-100px validate[required1]">
															<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
														</select>
													</div>
													<input type="hidden" class="click_value" name="" value="1">
													<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
														
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
														
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="accordion-item mjschool-class-border-div">
							<h2 class="accordion-header" id="headingThree">
								<button type="button" class="accordion-button collapsed" style="font-weight:800;" data-bs-toggle="collapse" data-bs-target="#collapseThree"><?php esc_html_e( 'Family Information', 'mjschool' ); ?></a></button>
							</h2>
							<div id="collapseThree" class="accordion-collapse collapse mjschool-margin-top-10pxpx" data-bs-parent="#myAccordion">
								<div class="card-body_1 admission_parent_information_div">
									<div class="form-body mjschool-user-form mjschool-padding-20px-child-theme">
										<div class="row">
											<div class="col-md-12">
												<div class="form-group">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio">
															<div class="input-group">
																<label class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Parental Status', 'mjschool' ); ?></label>
																<div class="d-inline-block mjschool-family-information">
																	<?php $pstatus = 'Both'; ?>
																	<input type="radio" name="pstatus" class="tog" value="Father" id="sinfather" <?php checked( 'Father', $pstatus ); ?>>
																	<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="Father"><?php esc_html_e( 'Father', 'mjschool' ); ?></label>
																	&nbsp;&nbsp; <input type="radio" name="pstatus" class="tog " id="sinmother" value="Mother" <?php checked( 'Mother', $pstatus ); ?>>
																	<label class="mjschool-custom-control-label" for="Mother"><?php esc_html_e( 'Mother', 'mjschool' ); ?></label>
																	&nbsp;&nbsp;<input type="radio" name="pstatus" class="tog" id="boths" value="Both" <?php checked( 'Both', $pstatus ); ?>>
																	<label class="mjschool-custom-control-label" for="Both"><?php esc_html_e( 'Both', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="mjschool-panel-body">
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 father_div">
											<div class="header" id="fatid" style="margin-left:10px;">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Father Information', 'mjschool' ); ?></h3>
											</div>
											<div id="fatid1" class="col-md-12 mb-3">
												<div class="form-group input">
													<select class="mjschool-line-height-29px-registration-from form-control validate[required]" name="fathersalutation" id="fathersalutation">
														<option value="Mr"><?php esc_html_e( 'Mr', 'mjschool' ); ?></option>
													</select>
												</div>
											</div>
											<div id="fatid2" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_first_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_first_name">
														<label for="userinput1"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid3" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_middle_name">
														<label for="userinput1"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid4" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_last_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_last_name">
														<label for="userinput1"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid13" class="col-md-12 mb-3">
												<div class="form-group mjschool-radio-button-bottom-margin-rs mjschool-margin-top-15px_child_theme">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio mjschool-line-height-29px-registration-from">
															<div class="input-group">
																<label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="left: 0px;top:-11px !important;"><?php esc_html_e( 'Gender', 'mjschool' ); ?></label>
																<div class="d-inline-block">
																	<?php $father_gender = 'male'; ?>
																	<input type="radio" value="male" class="tog" name="fathe_gender" <?php checked( 'male', $father_gender ); ?> />
																	<label style="" class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>&nbsp;&nbsp;
																	<input type="radio" value="female" class="tog" name="fathe_gender" <?php checked( 'female', $father_gender ); ?> />
																	<label style="" class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>&nbsp;&nbsp;
																	<input  type="radio" value="other" class="tog" name="fathe_gender" <?php checked( 'other', $father_gender ); ?> />
																	<label style="" class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>&nbsp;&nbsp;
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="fatid14" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_birth_date" class="mjschool-line-height-29px-registration-from form-control birth_date" type="text" name="father_birth_date" readonly>
														<label for="userinput1"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid15" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_address" class="mjschool-line-height-29px-registration-from form-control validate[custom[address_description_validation]]" maxlength="150" type="text" name="father_address">
														<label for="userinput1"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid16" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_state_name">
														<label for="userinput1"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid17" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_city_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_city_name">
														<label for="userinput1"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid18" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_zip_code" class="mjschool-line-height-29px-registration-from form-control  validate[custom[zipcode]]" maxlength="15" type="text" name="father_zip_code">
														<label for="userinput1"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid5" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_email" email_tpye="father_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control validate[custom[email]] text-input father_email" maxlength="100" type="text" name="father_email">
														<label for="userinput1"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid6" class="col-md-12 mb-3">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group input mjschool-margin-bottom-0">
															<div class="col-md-12 form-control mjschool-mobile-input">
																<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
																<input id="father_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]] mjschool-line-height-29px-registration-from" type="text" name="father_mobile">
																<label for="userinput6" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="fatid7" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]] mjschool-line-height-29px-registration-from" type="text" name="father_mobile">
														<input id="father_school" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input mjschool-line-height-29px-registration-from" maxlength="50" type="text" name="father_school">
														<label for="userinput1"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid8" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_medium" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_medium">
														<label for="userinput1"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid9" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_education" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_education">
														<label for="userinput1"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid10" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="fathe_income" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="fathe_income">
														<label for="userinput1"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="fatid9" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="father_occuption" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_occuption">
														<label for="userinput1"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div class="col-md-12 mb-3" id="mjschool-fatid12">
												<div class="form-group input mjschool-margin-top-15px_child_theme">
													<div class="col-md-12 form-control">
														<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" style="left: 20px;top:9px !important"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label>
														<div class="col-sm-12">
															<input type="file" name="father_doc" class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file mjschool-father_doc">
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mother_div">
											<div class="header" id="motid" style="margin-left:10px;">
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Mother Information', 'mjschool' ); ?></h3>
											</div>
											<div id="motid1" class="col-md-12 mb-3">
												<div class="form-group input">
													<select class="form-control validate[required]" name="mothersalutation" id="mothersalutation">
														<option value="Ms"><?php esc_html_e( 'Ms', 'mjschool' ); ?></option>
														<option value="Mrs"><?php esc_html_e( 'Mrs', 'mjschool' ); ?></option>
														<option value="Miss"><?php esc_html_e( 'Miss', 'mjschool' ); ?></option>
													</select>
												</div>
											</div>
											<div id="motid2" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_first_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_first_name">
														<label for="userinput1"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid3" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_middle_name">
														<label for="userinput1"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid4" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_last_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_last_name">
														<label for="userinput1"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid13" class="col-md-12 mb-3">
												<?php $mother_gender = 'female'; ?>
												<div class="form-group mjschool-radio-button-bottom-margin-rs mjschool-margin-top-15px_child_theme">
													<div class="col-md-12 form-control">
														<div class="row mjschool-padding-radio mjschool-line-height-29px-registration-from">
															<div class="input-group">
																<label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="left: 0px;top:-11px !important"><?php esc_html_e( 'Gender', 'mjschool' ); ?></label>
																<div class="d-inline-block">
																	<input type="radio" value="male" class="tog" name="mother_gender" <?php checked( 'male', $mother_gender ); ?> />
																	<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>&nbsp;&nbsp;
																	<input type="radio" value="female" class="tog" name="mother_gender" <?php checked( 'female', $mother_gender ); ?> />
																	<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>&nbsp;&nbsp;
																	<input type="radio" value="other" class="tog" name="mother_gender" <?php checked( 'other', $mother_gender ); ?> />
																	<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>&nbsp;&nbsp;
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id="motid14" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_birth_date" class="mjschool-line-height-29px-registration-from form-control birth_date" type="text" name="mother_birth_date" readonly>
														<label for="userinput1"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid15" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_address" class="mjschool-line-height-29px-registration-from form-control validate[custom[address_description_validation]]" maxlength="150" type="text" name="mother_address">
														<label for="userinput1"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid16" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_state_name">
														<label for="userinput1"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid17" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_city_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_city_name">
														<label for="userinput1"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid18" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_zip_code" class="mjschool-line-height-29px-registration-from form-control  validate[custom[zipcode]]" maxlength="15" type="text" name="mother_zip_code">
														<label for="userinput1"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid5" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_email" email_tpye="mother_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control  validate[custom[email]]  text-input mother_email" maxlength="100" type="text" name="mother_email">
														<label for="userinput1"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid6" class="col-md-12 mb-3">
												<div class="row">
													<div class="col-md-12">
														<div class="form-group input mjschool-margin-bottom-0">
															<div class="col-md-12 form-control mjschool-mobile-input">
																<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
																<input id="mother_mobile" class="mjschool-line-height-29px-registration-from form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mother_mobile">
																<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="mjschool-line-height-29px-registration-from form-control" name="phone_code">
																<label for="userinput6" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div id=" motid7" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_school" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_school">
														<label for="userinput1"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid8" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_medium" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_medium">
														<label for="userinput1"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid9" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_education" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_education">
														<label for="userinput1"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid10" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_income" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="mother_income">
														<label for="userinput1"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="motid9" class="col-md-12 mb-3">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="mother_occuption" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_occuption">
														<label for="userinput1"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
													</div>
												</div>
											</div>
											<div id="mjschool-motid12" class="col-md-12 mb-3">
												<div class="form-group input mjschool-margin-top-15px_child_theme">
													<div class="col-md-12 form-control">
														<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" style="left: 20px;top:9px !important"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label>
														<div class="col-sm-12">
															<input type="file" name="mother_doc" class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file mjschool-father_doc">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
						// --------- Get Module Wise Custom Field Data. --------------//
						$custom_field_obj     = new Mjschool_Custome_Field();
						$module               = 'admission';
						$compact_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
						if ( ! empty( $compact_custom_field ) ) {
							?>
							<div class="accordion-item mjschool-class-border-div">
								<h2 id="headingFour" class="accordion-header">
									<button type="button" class="accordion-button collapsed mjschool_weight_800" data-bs-toggle="collapse" data-bs-target="#collapseFour"><?php esc_html_e( 'Custom Field Information', 'mjschool' ); ?></a></button>
								</h2>
								<div id="collapseFour" class="accordion-collapse admission_custom collapse mjschool-margin-top-10pxpx" data-bs-parent="#myAccordion">
									<div class="card-body_1 admission_parent_information_div">
										<?php
										$custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
										?>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'save_student_frontend_admission_nonce' ); ?>
			<div class="col-sm-6 mjschool-admission-button mjschool_width_100px" >
				<input type="submit" value="<?php esc_html_e( 'New Admission', 'mjschool' ); ?>" name="save_student_front_admission" class="btn btn-success btn_style mjschool-save-btn" />
			</div>
		</form>
	</div>
	<?php
}
/**
 * Completes the student admission process.
 *
 * This function handles the entire admission workflow:
 * - Verifies the nonce for security.
 * - Creates a new WordPress user with the role `student_temp`.
 * - Stores all student, parent, and sibling information as user meta.
 * - Handles file/document uploads for parents.
 * - Generates admission and registration fee invoices if applicable.
 * - Inserts custom field data associated with the admission module.
 * - Sends confirmation email to the student with admission details.
 * - Handles redirection to payment processing if registration/admission fees are enabled.
 *
 * @param string $admission_no           Admission number of the student.
 * @param string $class                  Class name/ID of the student.
 * @param string $admission_date         Admission date.
 * @param string $first_name             Student's first name.
 * @param string $middle_name            Student's middle name.
 * @param string $last_name              Student's last name.
 * @param string $birth_date             Student's date of birth.
 * @param string $gender                 Student's gender.
 * @param string $address                Student's address.
 * @param string $state_name             State of the student.
 * @param string $city_name              City of the student.
 * @param string $zip_code               ZIP/postal code of the student.
 * @param string $phone_code             Phone code of the student.
 * @param string $mobile_number          Student's mobile number.
 * @param string $alternet_mobile_number Alternate mobile number.
 * @param string $email                  Student's email address.
 * @param string $username               Username for the WordPress account (usually email).
 * @param string $password               Password for the WordPress account.
 * @param string $preschool_name         Name of the preschool (if any).
 * @param string $smgt_user_avatar       Student's avatar/image URL or uploaded file.
 * @param array  $sibling_information    Array of sibling information.
 * @param string $p_status               Parent/guardian status.
 * @param string $fathersalutation       Father's salutation (Mr., Dr., etc.).
 * @param string $father_first_name      Father's first name.
 * @param string $father_middle_name     Father's middle name.
 * @param string $father_last_name       Father's last name.
 * @param string $fathe_gender           Father's gender.
 * @param string $father_birth_date      Father's date of birth.
 * @param string $father_address         Father's address.
 * @param string $father_city_name       Father's city.
 * @param string $father_state_name      Father's state.
 * @param string $father_zip_code        Father's ZIP/postal code.
 * @param string $father_email           Father's email address.
 * @param string $father_mobile          Father's mobile number.
 * @param string $father_school          Father's school.
 * @param string $father_medium          Father's medium of education.
 * @param string $father_education       Father's education level.
 * @param string $fathe_income           Father's income.
 * @param string $father_occuption       Father's occupation.
 * @param string $father_doc             Father's uploaded documents (JSON encoded).
 * @param string $mothersalutation       Mother's salutation (Mrs., Ms., etc.).
 * @param string $mother_first_name      Mother's first name.
 * @param string $mother_middle_name     Mother's middle name.
 * @param string $mother_last_name       Mother's last name.
 * @param string $mother_gender          Mother's gender.
 * @param string $mother_birth_date      Mother's date of birth.
 * @param string $mother_address         Mother's address.
 * @param string $mother_city_name       Mother's city.
 * @param string $mother_state_name      Mother's state.
 * @param string $mother_zip_code        Mother's ZIP/postal code.
 * @param string $mother_email           Mother's email address.
 * @param string $mother_mobile          Mother's mobile number.
 * @param string $mother_school          Mother's school.
 * @param string $mother_medium          Mother's medium of education.
 * @param string $mother_education       Mother's education level.
 * @param string $mother_income          Mother's income.
 * @param string $mother_occuption       Mother's occupation.
 * @param string $mother_doc             Mother's uploaded documents (JSON encoded).
 * @param string $wp_nonce               Security nonce for form verification.
 * @param float  $admission_fees         Admission fees amount.
 *
 * @return int|WP_Error User ID on success, or WP_Error object on failure.
 *
 * @throws wp_die() If the security nonce is invalid.
 *
 * @since 1.0.0
 */
function mjschool_complete_admission( $admission_no, $class, $admission_date, $first_name, $middle_name, $last_name, $birth_date, $gender, $address, $state_name, $city_name, $zip_code, $phone_code, $mobile_number, $alternet_mobile_number, $email, $username, $password, $preschool_name, $smgt_user_avatar, $sibling_information, $p_status, $fathersalutation, $father_first_name, $father_middle_name, $father_last_name, $fathe_gender, $father_birth_date, $father_address, $father_city_name, $father_state_name, $father_zip_code, $father_email, $father_mobile, $father_school, $father_medium, $father_education, $fathe_income, $father_occuption, $father_doc, $mothersalutation, $mother_first_name, $mother_middle_name, $mother_last_name, $mother_gender, $mother_birth_date, $mother_address, $mother_city_name, $mother_state_name, $mother_zip_code, $mother_email, $mother_mobile, $mother_school, $mother_medium, $mother_education, $mother_income, $mother_occuption, $mother_doc, $wp_nonce, $admission_fees ) {
	global $mjschool_reg_errors;
	if ( wp_verify_nonce( $wp_nonce, 'save_student_frontend_admission_nonce' ) ) {
		if ( 1 > count( $mjschool_reg_errors->get_error_messages() ) ) {
			$userdata = array(
				'user_login'    => $email,
				'user_nicename' => null,
				'user_email'    => $email,
				'user_url'      => null,
				'display_name'  => $first_name . ' ' . $last_name,
			);
			if ( $password != '' ) {
				$userdata['user_pass'] = mjschool_password_validation( $password );
			} else {
				$userdata['user_pass'] = wp_generate_password();
			}
			$role   = 'student_temp';
			$status = 'Not Approved';
			if ( get_option( 'mjschool_combine' ) === '1' ) {
				if ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
					$admission_fees_id     = $admission_fees;
					$obj_fees              = new Mjschool_Fees();
					$admission_fees_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $admission_fees_id );
				}
			}
			// ADD USER META. //
			$usermetadata = array(
				'admission_no'           => $admission_no,
				'admission_date'         => $admission_date,
				'admission_fees'         => $admission_fees_amount,
				'role'                   => $role,
				'status'                 => $status,
				'roll_id'                => '',
				'middle_name'            => $middle_name,
				'gender'                 => $gender,
				'birth_date'             => $birth_date,
				'address'                => $address,
				'city'                   => $city_name,
				'state'                  => $state_name,
				'zip_code'               => $zip_code,
				'preschool_name'         => $preschool_name,
				'phone_code'             => $phone_code,
				'class_name'             => $class,
				'mobile_number'          => $mobile_number,
				'alternet_mobile_number' => $alternet_mobile_number,
				'sibling_information'    => json_encode( $sibling_information ),
				'parent_status'          => $p_status,
				'fathersalutation'       => $fathersalutation,
				'father_first_name'      => $father_first_name,
				'father_middle_name'     => $father_middle_name,
				'father_last_name'       => $father_last_name,
				'fathe_gender'           => $fathe_gender,
				'father_birth_date'      => $father_birth_date,
				'father_address'         => $father_address,
				'father_city_name'       => $father_city_name,
				'father_state_name'      => $father_state_name,
				'father_zip_code'        => $father_zip_code,
				'father_email'           => $father_email,
				'father_mobile'          => $father_mobile,
				'father_school'          => $father_school,
				'father_medium'          => $father_medium,
				'father_education'       => $father_education,
				'fathe_income'           => $fathe_income,
				'father_occuption'       => $father_occuption,
				'father_doc'             => json_encode( $father_doc ),
				'mothersalutation'       => $mothersalutation,
				'mother_first_name'      => $mother_first_name,
				'mother_middle_name'     => $mother_middle_name,
				'mother_last_name'       => $mother_last_name,
				'mother_gender'          => $mother_gender,
				'mother_birth_date'      => $mother_birth_date,
				'mother_address'         => $mother_address,
				'mother_city_name'       => $mother_city_name,
				'mother_state_name'      => $mother_state_name,
				'mother_zip_code'        => $mother_zip_code,
				'mother_email'           => $mother_email,
				'mother_mobile'          => $mother_mobile,
				'mother_school'          => $mother_school,
				'mother_medium'          => $mother_medium,
				'mother_education'       => $mother_education,
				'mother_income'          => $mother_income,
				'mother_occuption'       => $mother_occuption,
				'mother_doc'             => json_encode( $mother_doc ),
				'mjschool_user_avatar'       => $smgt_user_avatar,
				'created_by'             => 1,
			);
			$returnval;
			$user_id = wp_insert_user( $userdata );
			$user    = new WP_User( $user_id );
			$user->set_role( $role );
			$user->add_role( 'subscriber' );
			foreach ( $usermetadata as $key => $val ) {
				$returnans = add_user_meta( $user_id, $key, $val, true );
			}
			if ( get_option( 'mjschool_combine' ) === '1' ) {
				if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
					$registration_fees_id = get_option( 'mjschool_registration_amount' );
				} else {
					$registration_fees_id = '';
				}
				if ( get_option( 'mjschool_registration_fees' ) === 'yes' ) {
					$obj_fees            = new Mjschool_Fees();
					$registration_amount = $obj_fees->mjschool_get_single_feetype_data_amount( $registration_fees_id );
					$generated           = mjschool_generate_admission_fees_invoice_draft( $registration_amount, $user_id, $registration_fees_id, $class, 0, 'Registration Fees' );
				}
			} elseif ( get_option( 'mjschool_admission_fees' ) === 'yes' ) {
				$generated = mjschool_generate_admission_fees_invoice( $admission_fees_amount, $user_id, $admission_fees_id, 0, 0, 'Admission Fees' );
			}
			$returnval          = update_user_meta( $user_id, 'first_name', $first_name );
			$returnval          = update_user_meta( $user_id, 'last_name', $last_name );
			$hash               = md5( rand( 0, 1000 ) );
			$returnval          = update_user_meta( $user_id, 'hash', $hash );
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'admission';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $user_id );
			if ( $user_id ) {
				// ---------- ADMISSION REQUEST MAIL. ---------//
				$string                     = array();
				$string['{{student_name}}'] = mjschool_get_display_name( $user_id );
				$string['{{user_name}}']    = $first_name . ' ' . $last_name;
				$string['{{email}}']        = $userdata['user_email'];
				$string['{{school_name}}']  = get_option( 'mjschool_name' );
				$MsgContent                 = get_option( 'mjschool_admission_mailtemplate_content' );
				$MsgSubject                 = get_option( 'mjschool_admissiion_title' );
				$message                    = mjschool_string_replacement( $string, $MsgContent );
				$MsgSubject                 = mjschool_string_replacement( $string, $MsgSubject );
				$email                      = $email;
				mjschool_send_mail( $email, $MsgSubject, $message );
				?>
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mjschool-hoste-lbl2" >
					<?php
					esc_attr_e( 'Request For Admission Successfully. You will be able to access your account after the school admin approves it.', 'mjschool' );
					?>
				</div>
				<?php
			}
			if ( $user_id && get_option( 'mjschool_combine' ) === '1' && get_option( 'mjschool_registration_fees' ) === 'yes' ) {
				$amount     = isset( $registration_amount ) ? $registration_amount : ( isset( $admission_fees_amount ) ? $admission_fees_amount : 0 );
				$invoice_id = isset( $generated ) ? $generated : 0;
				$redirect_url = site_url( '/wp-content/plugins/mjschool/lib/paypal/paypal_process.php' ) . '?fees_pay_id=' . $invoice_id . '&user_id=' . $user_id . '&amount=' . $amount;
				wp_redirect( $redirect_url );
				die();
			}
			return $returnval;
		}
	} else {
		wp_die( esc_html( 'Security check failed! Invalid security token.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
}
/**
 * Validates the frontend student admission form inputs.
 *
 * This function checks:
 * - Username length (minimum 4 characters).
 * - Whether the username already exists in WordPress.
 * - Validity of the student email.
 * - Whether the student email is already registered.
 *
 * Validation errors are stored in a global WP_Error object ($mjschool_reg_errors)
 * and displayed immediately if any exist.
 *
 * @param string $email       Student's email address.
 * @param string $username    Student's username (usually email).
 * @param string $father_email Father's email address (currently not used).
 * @param string $mother_email Mother's email address (currently not used).
 *
 * @global WP_Error $mjschool_reg_errors Holds validation errors.
 *
 * @return void Outputs validation errors directly if any.
 *
 * @since 1.0.0
 */
function mjschool_admission_validation( $email, $username, $father_email, $mother_email ) {
	global $mjschool_reg_errors;
	$mjschool_reg_errors = new WP_Error();
	if ( 4 > strlen( $username ) ) {
		$mjschool_reg_errors->add( 'username_length', 'Username too short. At least 4 characters is required' );
	}
	if ( username_exists( $username ) ) {
		$mjschool_reg_errors->add( 'user_name', 'Sorry, that username already exists!' );
	}
	if ( ! is_email( $email ) ) {
		$mjschool_reg_errors->add( 'email_invalid', 'Email is not valid' );
	}
	if ( email_exists( $email ) ) {
		$mjschool_reg_errors->add( 'email', 'Email Already in use' );
	}
	if ( is_wp_error( $mjschool_reg_errors ) ) {
		foreach ( $mjschool_reg_errors->get_error_messages() as $error ) {
			echo '<div class="mjschool-student-reg-error">';
			echo '<strong>' . esc_attr__( 'ERROR', 'mjschool' ) . '</strong> : ';
			echo '<span class="error"> ' . esc_html( $error ) . ' </span><br/>';
			echo '</div>';
		}
	}
}
/**
 * Restricts admin menu and toolbar items for users with the 'management' role.
 * Grants them the 'upload_files' capability and hides the Media menu.
 *
 * Hooks:
 * - Removes specific admin menu items.
 * - Removes specific admin toolbar nodes.
 * - Hides the Media menu via CSS.
 *
 * @return void
 * 
 * @since 1.0.0
 */
function mjschool_remove_menus() {
	$mjschool_author = wp_get_current_user();
	if ( isset( $mjschool_author->roles[0] ) ) {
		$current_role = $mjschool_author->roles[0];
	} else {
		$current_role = 'management';
	}
	if ( $current_role === 'management' ) {
		add_action( 'admin_bar_menu', 'mjschool_shape_space_remove_toolbar_nodes', 999 );
		add_action( 'admin_menu', 'mjschool_remove_menus1', 999 );
		add_action( 'admin_menu', 'mjschool_docs_remove_menus', 999 );
		$management = get_role( 'management' );
		$management->add_cap( 'upload_files' );
		?>
		<style>
			#menu-media {
				display: none !important;
			}
		</style>
		<?php
	}
}
/**
 * Restricts admin menus and toolbar items based on user role.
 *
 * This function performs the following:
 * - Removes the Jetpack menu for all non-administrator users.
 * - For users with the 'management' role:
 *   - Removes core admin menus including Dashboard, Posts, Media, Pages, Comments, Appearance, Plugins, Users, Tools, and Settings.
 *   - Grants the 'upload_files' capability.
 *   - Optionally hides the Media menu via inline CSS.
 *
 * @global WP_User $current_user Current logged-in WordPress user.
 *
 * @return void Modifies admin menus and capabilities for specific users.
 *
 * @since 1.0.0
 */
function mjschool_docs_remove_menus() {
	remove_menu_page( 'index.php' );                  // Dashboard.
	remove_menu_page( 'jetpack' );                    // Jetpack*.
	remove_menu_page( 'edit.php' );                   // Posts.
	remove_menu_page( 'upload.php' );                 // Media.
	remove_menu_page( 'edit.php?post_type=page' );    // Pages.
	remove_menu_page( 'edit-comments.php' );          // Comments.
	remove_menu_page( 'themes.php' );                 // Appearance.
	remove_menu_page( 'plugins.php' );                // Plugins.
	remove_menu_page( 'users.php' );                  // Users.
	remove_menu_page( 'tools.php' );                  // Tools.
	remove_menu_page( 'options-general.php' );        // Settings.
}
/**
 * Removes the Jetpack admin menu for all non-administrator users.
 *
 * This function ensures that users without the 'administrator' capability
 * do not see the Jetpack menu in the WordPress admin dashboard.
 *
 * @global WP_User $current_user Current logged-in WordPress user.
 *
 * @return void Removes the Jetpack menu page for non-admin users.
 *
 * @since 1.0.0
 */
add_action( 'admin_menu', 'mjschool_remove_menus' );
function mjschool_remove_menus1() {
	if ( ! current_user_can( 'administrator' ) ) {
		remove_menu_page( 'jetpack' );
	}
}
/**
 * Removes the WordPress logo and site name from the admin toolbar.
 *
 * This function customizes the WordPress admin toolbar for specific users
 * by removing the default WordPress logo and the site name node.
 *
 * @param WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance to modify.
 *
 * @return void Modifies the admin toolbar by removing specified nodes.
 *
 * @since 1.0.0
 */
function mjschool_shape_space_remove_toolbar_nodes( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
	$wp_admin_bar->remove_node( 'site-name' );
}
/**
 * Customizes the WordPress page or post title dynamically.
 *
 * This function modifies the document title based on the context:
 * - If a 'page' parameter exists in the request, the title is set to that page name.
 * - For singular posts, the title is prefixed with the school's name from the
 *   'mjschool_name' option.
 *
 * @param array $title An associative array of document title parts.
 *                     Typical keys include 'title', 'tagline', 'site', etc.
 *
 * @return array Modified document title parts.
 *
 * @since 1.0.0
 */
add_filter( 'document_title_parts', 'mjschool_my_custom_title' );
function mjschool_my_custom_title( $title ) {
	$mjschool_page_name = '';
	$current_page = isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '';
	if ( ! empty( $_REQUEST['page'] ) ) {
		$mjschool_page_name = $current_page;
	}
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $current_page === $mjschool_page_name ) {
			$title['title'] = $mjschool_page_name;
		}
	} elseif ( is_singular( 'post' ) ) {
		$title['title'] = get_option( 'mjschool_name' ) . ' ' . $title['title'];
	}
	return $title;
}
/**
 * Logs a user's login along with their role.
 *
 * This function is triggered during the WordPress login process.
 * It retrieves the user's primary role and records the login event
 * for auditing or tracking purposes.
 *
 * @param string   $user_login Username used for login.
 * @param WP_User  $user       WP_User object of the logged-in user.
 *
 * @return void
 *
 * @since 1.0.0
 */
function mjschool_student_login( $user_login, $user ) {
	$mjschool_role = $user->roles;
	$mjschool_role_name     = $mjschool_role[0];
	mjschool_append_user_log( $user_login, $mjschool_role_name );
}
/**
 * Generates recurring fee invoices based on active recurring fee settings.
 *
 * This function performs the following tasks:
 * - Fetches all active recurring fee records as of the current date.
 * - Calculates the recurring end date based on the recurring type (weekly, monthly, quarterly, half-yearly).
 * - Computes the total fee amount including applicable taxes.
 * - Inserts a new fee payment record for each student and fee combination.
 * - Sends email notifications to students and their parents with invoice details.
 * - Updates the recurring fee record's end date to reflect the next cycle.
 *
 * Notes:
 * - This function sets the PHP execution time to unlimited (`set_time_limit(0)`) due to potentially long processing.
 * - Uses direct database queries for fee and recurring data (considered safe in this context).
 * - Email notifications depend on the plugin options (`mjschool_mail_notification`).
 *
 * @global wpdb $wpdb WordPress database object.
 *
 * @return void
 *
 * @since 1.0.0
 */
function mjschool_generate_recurring_invoice() {
	set_time_limit( 0 );
	global $wpdb;
	$obj_feespayment                       = new Mjschool_Feespayment();
	$table_mjschool_fees                   = $wpdb->prefix . 'mjschool_fees';
	$table_mjschool_fees_payment_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring';
	$date                                  = date( 'Y-m-d' );
	$all_recurring_fees_data               = $obj_feespayment->mjschool_get_all_recurring_fees_active( $date );
	if ( ! empty( $all_recurring_fees_data ) ) {
		foreach ( $all_recurring_fees_data as $recurring_fees_data ) {
			$student_id_array = explode( ',', $recurring_fees_data->student_id );
			$fees_id_array    = explode( ',', $recurring_fees_data->fees_id );
			$recurring_type   = $recurring_fees_data->recurring_type;
			if ( $recurring_type === 'monthly' ) {
				$recurring_enddate = date( 'Y-m-d', strtotime( '+1 months', strtotime( date( 'Y-m-d' ) ) ) );
			} elseif ( $recurring_type === 'weekly' ) {
				$recurring_enddate = date( 'Y-m-d', strtotime( '+1 week', strtotime( date( 'Y-m-d' ) ) ) );
			} elseif ( $recurring_type === 'quarterly' ) {
				$recurring_enddate = date( 'Y-m-d', strtotime( '+3 months', strtotime( date( 'Y-m-d' ) ) ) );
			} elseif ( $recurring_type === 'half_yearly' ) {
				$recurring_enddate = date( 'Y-m-d', strtotime( '+6 months', strtotime( date( 'Y-m-d' ) ) ) );
			} else {
				$recurring_enddate = date( 'Y-m-d' );
			}
			$fees_amount = array();
			foreach ( $fees_id_array as $id ) {
				$result        = mjschool_get_fees_details($id);
				$fees_amount[] = $result->fees_amount;
			}
			$total_fees_amount = array_sum( $fees_amount );
			foreach ( $student_id_array as $student_id ) {
				global $wpdb;
				$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
				if ( $recurring_fees_data->class_id === '0' ) {
					$class_name            = get_user_meta( $student_id, 'class_name', true );
					$class_section         = get_user_meta( $student_id, 'class_section', true );
					$feedata['class_id']   = $class_name;
					$feedata['section_id'] = $class_section;
				} else {
					$feedata['class_id']   = $recurring_fees_data->class_id;
					$feedata['section_id'] = $recurring_fees_data->section_id;
				}
				$feedata['fees_id']     = $recurring_fees_data->fees_id;
				$feedata['student_id']  = $student_id;
				$feedata['fees_amount'] = $total_fees_amount;
				if ( isset( $recurring_fees_data->tax ) ) {
					$feedata['tax']        = $recurring_fees_data->tax;
					$feedata['tax_amount'] = mjschool_get_tax_amount( $total_fees_amount, explode( ',', $recurring_fees_data->tax ) );
				} else {
					$feedata['tax']        = null;
					$feedata['tax_amount'] = 0;
				}
				$feedata['total_amount'] = $feedata['fees_amount'] + $feedata['tax_amount'];
				$feedata['description']  = $recurring_fees_data->description;
				$feedata['start_year']   = date( 'Y-m-d' );
				$feedata['end_year']     = $recurring_enddate;
				$feedata['paid_by_date'] = date( 'Y-m-d' );
				$feedata['created_date'] = date( 'Y-m-d H:i:s' );
				$feedata['created_by']   = get_current_user_id();
				// Generate Recurring Invoice. //
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result = $wpdb->insert( $table_mjschool_fees_payment, $feedata );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$fees_pay_id = $wpdb->insert_id;
				// END Generate Recurring Invoice. //
				// Send Mail Notiifcation to student. //
				$student_info                  = get_userdata( $student_id );
				$Cont                          = get_option( 'mjschool_fee_payment_mailcontent' );
				$email                         = $student_info->user_email;
				$SearchArr['{{student_name}}'] = $student_info->display_name;
				$SearchArr['{{school_name}}']  = get_option( 'mjschool_name' );
				$SearchArr['{{date}}']         = mjschool_get_date_in_input_box( date( 'Y-m-d' ) );
				$SearchArr['{{amount}}']       = mjschool_get_currency_symbol() . '' . number_format( $total_fees_amount, 2, '.', '' );
				$MessageContent                = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_fee_payment_mailcontent' ) );
				if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
					mjschool_send_mail_paid_invoice_pdf( $email, get_option( 'mjschool_fee_payment_title' ), $MessageContent, $fees_pay_id );
				}
				// END Send Mail Notiifcation to student. //
				// Send Mail To Parant code start. //
				$parent = get_user_meta( $student_id, 'parent_id', true );
				if ( ! empty( $parent ) ) {
					foreach ( $parent as $parent_id ) {
						$parent_info                  = get_userdata( $parent_id );
						$Cont                         = get_option( 'mjschool_fee_payment_title_for_parent' );
						$email                        = $parent_info->user_email;
						$SearchArr['{{parent_name}}'] = $parent_info->display_name;
						$SearchArr['{{school_name}}'] = get_option( 'mjschool_name' );
						$SearchArr['{{date}}']        = mjschool_get_date_in_input_box( date( 'Y-m-d' ) );
						$SearchArr['{{amount}}']      = mjschool_get_currency_symbol() . '' . number_format( $total_fees_amount, 2, '.', '' );
						$SearchArr['{{child_name}}']  = $student_info->display_name;
						$MessageContent               = mjschool_string_replacement( $SearchArr, get_option( 'mjschool_fee_payment_mailcontent_for_parent' ) );
						if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
							mjschool_send_mail_paid_invoice_pdf( $email, get_option( 'mjschool_fee_payment_title' ), $MessageContent, $fees_pay_id );
						}
					}
				}
				// END Send Mail To Parant code start. //
			}
			// Update Recuring END DATE.//
			$recurring_fees_id['recurring_id']      = $recurring_fees_data->recurring_id;
			$recurring_feedata['recurring_enddate'] = $recurring_enddate;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->update( $table_mjschool_fees_payment_recurring, $recurring_feedata, $recurring_fees_id );
			// END Update Recuring END DATE.//
		}
	}
}
/**
 * Sends payment reminder emails to students and their parents for pending fees.
 *
 * This function performs the following tasks:
 * - Checks if the payment reminder system is enabled in plugin settings.
 * - Calculates the reminder date based on the configured number of days before due date.
 * - Retrieves all student fee records that match the reminder date.
 * - Checks whether a reminder has already been sent for each student and fee.
 * - Sends email notifications to the student and their parents with fee details.
 * - Logs each reminder sent to the database to prevent duplicate notifications.
 *
 * Notes:
 * - The function sets the PHP execution time to unlimited using set_time_limit(0)
 *   to handle a potentially large number of students.
 * - Uses WordPress email and database functions to send emails and log reminders.
 * - Supports email template replacement with placeholders like {{student_name}}, {{due_amount}}, {{total_amount}}, {{class_name}}, and {{school_name}}.
 *
 * @global wpdb $wpdb WordPress database object.
 *
 * @return void Outputs or processes reminders; no return value.
 *
 * @since 1.0.0
 */
function mjschool_send_payment_reminder() {
	global $wpdb;
	$obj_feespayment            = new Mjschool_Feespayment();
	$mjschool_cron_reminder_log = $wpdb->prefix . 'mjschool_cron_reminder_log';
	set_time_limit( 0 );
	$reminder_enable = get_option( 'mjschool_system_payment_reminder_enable' );
	$reminder_day    = get_option( 'mjschool_system_payment_reminder_day' );
	if ( $reminder_enable === 'yes' ) {
		$currentDate = new DateTime();
		$currentDate->modify( "+$reminder_day days" );
		$reminder_date     = $currentDate->format( 'Y-m-d' );
		$fees_payment_data = $obj_feespayment->mjschool_get_all_student_fees_data_for_reminder( $reminder_date );
		if ( ! empty( $fees_payment_data ) ) {
			$reminder_log_data = array();
			$fees_id           = 0;
			$student_id        = 0;
			foreach ( $fees_payment_data as $paymentdata ) {
				$fees_id    = $paymentdata->fees_pay_id;
				$student_id = $paymentdata->student_id;
				$check      = mjschool_check_reminder_send_or_not( $student_id, $fees_id );
				if ( empty( $check ) ) {
					$reminder_log_data['student_id']  = $student_id;
					$reminder_log_data['fees_pay_id'] = $fees_id;
					$reminder_log_data['date_time']   = date( 'Y-m-d' );
					$studentinfo                      = get_userdata( $student_id );
					$student_mail                     = $studentinfo->user_email;
					$student_name                     = $studentinfo->display_name;
					$parent_id                        = get_user_meta( $student_id, 'parent_id', true );
					foreach ( $parent_id as $id ) {
						$parentinfo = get_userdata( $id );
					}
					$parent_mail = $parentinfo->user_email;
					$parent_name = $parentinfo->display_name;
					$to          = $parent_mail;
					$Due_amt     = $paymentdata->total_amount - $paymentdata->fees_paid_amount;
					$due_amount  = number_format( $Due_amt, 2, '.', '' );
					/* Mail Notification For Student. */
					$student_mail              = $studentinfo->user_email;
					$student_name              = $studentinfo->display_name;
					$Due_amt                   = $paymentdata->total_amount - $paymentdata->fees_paid_amount;
					$due_amount                = number_format( $Due_amt, 2, '.', '' );
					$total_amount              = number_format( $paymentdata->total_amount, 2, '.', '' );
					$subject                   = get_option( 'mjschool_fee_payment_reminder_title_for_student' );
					$Seach['{{student_name}}'] = $student_name;
					$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
					$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
					$Seach['{{class_name}}']   = mjschool_get_class_name( $paymentdata->class_id );
					$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
					$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent_for_student' ) );
					if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
						$send = mjschool_send_mail_paid_invoice_pdf( $student_mail, $subject, $MsgContent, $fees_id );
						$send = 1;
						if ( $send ) {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$result = $wpdb->insert( $mjschool_cron_reminder_log, $reminder_log_data );
						}
					}
					/* Mail Notification For Parent. */
					if ( is_array( $parent_id ) || is_object( $parent_id ) ) {
						foreach ( $parent_id as $id ) {
							$parentinfo                = get_userdata( $id );
							$parent_mail               = $parentinfo->user_email;
							$parent_name               = $parentinfo->display_name;
							$Due_amt                   = $paymentdata->total_amount - $paymentdata->fees_paid_amount;
							$due_amount                = number_format( $Due_amt, 2, '.', '' );
							$total_amount              = number_format( $paymentdata->total_amount, 2, '.', '' );
							$subject                   = get_option( 'mjschool_fee_payment_reminder_title' );
							$Seach['{{student_name}}'] = $student_name;
							$Seach['{{parent_name}}']  = $parent_name;
							$Seach['{{total_amount}}'] = mjschool_currency_symbol_position_language_wise( $total_amount );
							$Seach['{{due_amount}}']   = mjschool_currency_symbol_position_language_wise( $due_amount );
							$Seach['{{class_name}}']   = mjschool_get_class_name( $paymentdata->class_id );
							$Seach['{{school_name}}']  = get_option( 'mjschool_name' );
							$MsgContent                = mjschool_string_replacement( $Seach, get_option( 'mjschool_fee_payment_reminder_mailcontent' ) );
							if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
								$send = mjschool_send_mail_paid_invoice_pdf( $parent_mail, $subject, $MsgContent, $fees_id );
							}
						}
					}
				}
			}
		}
	}
}
add_action( 'recurring_invoice_event', 'mjschool_send_payment_reminder' );
add_action( 'recurring_invoice_event', 'mjschool_generate_recurring_invoice' );
/**
 * Schedules a WordPress cron event to generate recurring invoices.
 *
 * This snippet checks whether the 'recurring_invoice_event' is already scheduled.
 * If not, it schedules the event to run every 30 minutes.
 *
 * Note: Ensure that the 'thirty_minutes' recurrence interval is registered with
 *       `add_filter( 'cron_schedules', ...)` elsewhere in your code.
 *
 * @since 1.0.0
 */
if ( ! wp_next_scheduled( 'recurring_invoice_event' ) ) {
	wp_schedule_event( time(), 'thirty_minutes', 'recurring_invoice_event' );
}
/**
 * Adds a custom cron schedule interval of 30 minutes.
 *
 * WordPress has default intervals like hourly, twice daily, and daily.
 * This filter adds a custom interval named 'thirty_minutes' that runs every 30 minutes.
 *
 * @param array $schedules Existing cron schedules.
 * @return array Modified cron schedules including the 30-minute interval.
 *
 * @since 1.0.0
 */
add_filter(
	'cron_schedules',
	function ( $schedules ) {
		$schedules['thirty_minutes'] = array(
			'interval' => 1800, // 30 minutes
			'display'  => 'Every 30 Minutes',
		);
		return $schedules;
	}
);
/**
 * Adds a custom nonce field to the default WordPress login form for security.
 *
 * Nonces help protect against CSRF (Cross-Site Request Forgery) attacks
 * by ensuring that the request comes from a valid source.
 *
 * @since 1.0.0
 */
function mjschool_add_custom_nonce_to_login_form()
{
	// Add a nonce field for security.
	wp_nonce_field( 'custom_login_form_nonce', 'custom_login_form_nonce_field' );
}
/**
 * Adds a custom nonce field to the default WordPress login form.
 *
 * This nonce helps protect the login form from CSRF attacks.
 *
 * @since 1.0.0
 */
add_action( 'login_form', 'mjschool_add_custom_nonce_to_login_form' );
/**
 * Verifies the custom nonce during WordPress login.
 *
 * This function checks whether the nonce field from the login form is set
 * and valid. If verification fails, it prevents the login and returns
 * a WP_Error.
 *
 * @param WP_User|WP_Error|null $user     The user object if login is successful, or WP_Error.
 * @param string                $username The username entered.
 * @param string                $password The password entered.
 *
 * @return WP_User|WP_Error Returns the user object on success, or WP_Error on failure.
 *
 * @since 1.0.0
 */
function mjschool_verify_custom_login_nonce($user, $username, $password)
{
	// Check if the nonce is set and valid.
	if ( isset( $_POST['custom_login_form_nonce_field']) && !wp_verify_nonce($_POST['custom_login_form_nonce_field'], 'custom_login_form_nonce' ) ) {
		// If nonce verification fails, prevent login and return an error
		return new WP_Error( 'nonce_verification_failed', esc_html__( 'Nonce verification failed. Please try again.', 'mjschool' ) );
	}
	return $user;
}
add_filter( 'authenticate', 'mjschool_verify_custom_login_nonce', 30, 3);
/* FOR SECURITY CODE */ 
// CSP: Wildcard Directive
//  Missing Anti-clickjacking Header
// add_action( 'send_headers', function () {
//     header( "Content-Security-Policy: frame-ancestors 'self';"); // Replace 'self' with allowed domains if needed
// });
/**
 * Starts a PHP session with secure cookie parameters.
 *
 * Ensures that session cookies have the HttpOnly flag, the Secure flag
 * (if using HTTPS), and an optional SameSite attribute for added security.
 * This mitigates risks like XSS and CSRF attacks targeting session cookies.
 *
 * @since 1.0.0
 */
// add_action( 'init', function () {
//     if (session_status() === PHP_SESSION_NONE) {
//         session_set_cookie_params([
//             'httponly' => true,
//             'secure' => is_ssl(),
//             'samesite' => 'Strict', // Optional: Add SameSite attribute.
//         ]);
//         session_start();
//     }
// });
/**
 * Removes server and PHP version information from HTTP response headers.
 *
 * By default, many web servers and PHP send headers like "Server" and
 * "X-Powered-By" which can reveal the server software and PHP version.
 * Removing these headers helps reduce information disclosure and improves security.
 *
 * @since 1.0.0
 */
// function mjschool_remove_version_info() {
//     header_remove( 'Server' );
// 	header_remove( 'X-Powered-By' );
// }
// add_action( 'send_headers', 'mjschool_remove_version_info' );
/**
 * Customize the Server HTTP header to reduce information disclosure.
 *
 * By default, the "Server" header reveals the web server software (e.g., Apache, Nginx),
 * which could be exploited by attackers. This function removes the default server info
 * and optionally replaces it with a custom string (e.g., "WordPress").
 *
 * @since 1.0.0
 */
// add_action( 'send_headers', function() {
//     header_remove( 'Server' );
//     header( 'Server: WordPress' );
// });
/**
 * Add X-Content-Type-Options HTTP header to prevent MIME type sniffing.
 *
 * This header instructs browsers not to sniff the content type and to strictly
 * follow the declared Content-Type. This mitigates certain types of XSS attacks
 * when serving untrusted content.
 *
 * @since 1.0.0
 */
// function mjschool_add_nosniff_header() {
//     header( 'X-Content-Type-Options: nosniff' );
// }
// add_action( 'send_headers', 'mjschool_add_nosniff_header' );
/**
 * Output conditional CSS to hide specific menu links in the frontend.
 *
 * Depending on the "mjschool_combine" option, this function injects CSS into
 * the <head> section to hide certain student registration or admission links.
 *
 * @since 1.0.0
 */
add_action( 'wp_head', 'mjschool_conditional_menu_hide_css' );
function mjschool_conditional_menu_hide_css() {
	$combine = get_option( 'mjschool_combine' );
	?>
	<?php if ( $combine === '1' ) : ?>
		<style>
		a[href$="/student-registration/"],
		a[href$="/student-admission/"] {
			display: none !important;
		}
		</style>
	<?php else : ?>
		<style>
		a[href$="/student-registration-form/"] {
			display: none !important;
		}
		</style>
	<?php endif;
}
?>