<?php
/**
 * MJ School Functions File.
 *
 * This file contains a collection of utility functions used across the MJ School Management plugin.
 * These functions handle database operations, user/meta retrieval, message management,
 * class/section/subject CRUD operations, fee payment queries, and various helper lookups.
 *
 * Notes:
 * - Most queries intentionally use direct DB access with $wpdb for performance and flexibility.
 * - All IDs are sanitized using intval() before being passed into queries.
 * - Audit logs are appended for create/update/delete operations where applicable.
 * - Functions return 'N/A' or empty values when data is missing to avoid PHP notices.
 *
 * @package    MjSchool
 * @subpackage MjSchool
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'login_redirect', 'mjschool_login_redirect', 10, 3 );
/**
 * Redirects users after login based on their role.
 *
 * Students, teachers, parents, and support staff are redirected to
 * the MjSchool dashboard. All other roles go to the WordPress admin area.
 *
 * @since 1.0.0
 *
 * @param string   $redirect_to The original redirect URL.
 * @param string   $request     The requested redirect URL.
 * @param WP_User  $user        The logged-in user object.
 *
 * @return string  The modified redirect URL.
 */
function mjschool_login_redirect( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		$roles = array( 'student', 'teacher', 'parent', 'supportstaff' );
		foreach ( $roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				$redirect_to = esc_url_raw( home_url( '?dashboard=mjschool_user' ) );
				break;
			} else {
				$redirect_to = esc_url_raw( admin_url() );
			}
		}
	}
	return $redirect_to;
}
/**
 * Adds custom action links to the plugin row on the plugins page.
 *
 * Includes documentation, video guide, support, and addons links.
 *
 * @since 1.0.0
 *
 * @param array $links Existing plugin action links.
 *
 * @return array Modified action links.
 */
function mjschool_custom_plugin_links( $links ) {
	$addons_link  = admin_url( 'admin.php?page=mjschool_system_addon' );
	$plugin_links = array(
		'<a href="https://mojoomlasoftware.github.io/wp-school-documentation/" target="_blank">Documentation</a>',
		'<a href="https://youtu.be/34177nQsofw?si=idiHXGkywESeHLeS" target="_blank">Video Guide</a>',
		'<a href="https://mojoomla.com/contact/" target="_blank">Community Support</a>',
		'<a href="' . esc_url( $addons_link ) . '" target="_blank">Addons</a>',
	);
	return array_merge( $links, $plugin_links );
}
add_filter( 'plugin_action_links_' . MJSCHOOL_PLUGIN_BASENAME, 'mjschool_custom_plugin_links' );

/**
 * Adds custom metadata links under the plugin description.
 *
 * Displays documentation, video guides, addons, and support links.
 *
 * @since 1.0.0
 *
 * @param array  $links Existing plugin row meta links.
 * @param string $file  The plugin basename.
 *
 * @return array Modified plugin meta links.
 */
function mjschool_custom_plugin_row_meta( $links, $file ) {
	if ( $file === MJSCHOOL_PLUGIN_BASENAME ) {
		$addons_link = admin_url( 'admin.php?page=mjschool_system_addon' );
		$custom_links = array(
			'<a href="https://mojoomlasoftware.github.io/wp-school-documentation/" target="_blank" rel="noopener noreferrer">Documentation</a>',
			'<a href="https://youtu.be/34177nQsofw?si=idiHXGkywESeHLeS" target="_blank" rel="noopener noreferrer">Video Guide</a>',
			'<a href="https://mojoomla.com/contact/" target="_blank" rel="noopener noreferrer">Community Support</a>',
			'<a href="' . esc_url( $addons_link ) . '" target="_blank" rel="noopener noreferrer">Addons</a>',
		);
		$links        = array_merge( $links, $custom_links );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'mjschool_custom_plugin_row_meta', 10, 2 );
/**
 * Retrieves all notices for the student dashboard.
 *
 * Includes notices for all students and notices for the given class
 * and section.
 *
 * @since 1.0.0
 *
 * @param int|string $class_name    The class ID.
 * @param int|string $class_section The class section ID.
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_student_notice_dashboard( $class_name, $class_section ) {
	// Sanitize inputs to ensure they're treated as the correct type
	$class_name    = is_numeric( $class_name ) ? absint( $class_name ) : sanitize_text_field( $class_name );
	$class_section = is_numeric( $class_section ) ? absint( $class_section ) : sanitize_text_field( $class_section );
	
	$arr1              = array( 'all' );
	$arr2              = array( $class_name );
	$mjschool_class_id = array_merge( $arr1, $arr2 );

	$notice_list_student = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'notice_for',
					'value'   => 'all',
					'compare' => '=',
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
					array(
						'key'     => 'smgt_section_id',
						'value'   => $class_section,
						'compare' => '=',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'student',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
				),
			),
		)
	);
	
	return $notice_list_student;
}
/**
 * Retrieves limited notices for students based on access rights.
 *
 * Fetches up to four notices matching class and section filters.
 *
 * @since 1.0.0
 *
 * @param int|string $class_name    The class ID.
 * @param int|string $class_section The class section ID.
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_student_notice_dashboard_with_access_right( $class_name, $class_section ) {
	// Sanitize inputs to ensure they're treated as the correct type
	$class_name    = is_numeric( $class_name ) ? absint( $class_name ) : sanitize_text_field( $class_name );
	$class_section = is_numeric( $class_section ) ? absint( $class_section ) : sanitize_text_field( $class_section );
	
	$arr1              = array( 'all' );
	$arr2              = array( $class_name );
	$mjschool_class_id = array_merge( $arr1, $arr2 );

	$notice_list_student = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => 4,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'notice_for',
					'value'   => 'all',
					'compare' => '=',
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
					array(
						'key'     => 'smgt_section_id',
						'value'   => $class_section,
						'compare' => '=',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'student',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
				),
			),
		)
	);
	
	return $notice_list_student;
}
/**
 * Retrieves notice board items for teachers for all or matching classes.
 *
 * Fetches up to four relevant notices for teacher users.
 *
 * @since 1.0.0
 *
 * @param array|string $class_name Class IDs the teacher handles.
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_teacher_notice_board( $class_name ) {
	// Ensure $class_name is an array and sanitize values
	if ( ! is_array( $class_name ) ) {
		$class_name = array( $class_name );
	}
	$class_name = array_map( function( $value ) {
		return is_numeric( $value ) ? absint( $value ) : sanitize_text_field( $value );
	}, $class_name );
	
	$arr1              = array( 'all' );
	$arr2              = $class_name;
	$mjschool_class_id = array_merge( $arr1, $arr2 );

	$notice_list_teacher = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => 4,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'notice_for',
					'value'   => 'all',
					'compare' => '=',
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'teacher',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
				),
			),
		)
	);
	
	return $notice_list_teacher;
}
/**
 * Retrieves all teacher-specific notices based on their assigned class.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_teacher_notice_dashbord() {
	$class_name = get_user_meta( get_current_user_id(), 'class_name', true );
	
	// Ensure we have valid data
	if ( empty( $class_name ) ) {
		$class_name = array();
	}
	if ( ! is_array( $class_name ) ) {
		$class_name = array( $class_name );
	}
	
	// Get the first class or use 'all' as fallback
	$first_class = ! empty( $class_name[0] ) ? $class_name[0] : 'all';
	$first_class = is_numeric( $first_class ) ? absint( $first_class ) : sanitize_text_field( $first_class );
	
	$mjschool_class_id = array( 'all', $first_class );

	$notice_list_teacher = get_posts(
		array(
			'post_type'   => 'notice',
			'numberposts' => -1,
			'meta_query'  => array(
				'relation' => 'OR',
				array(
					'key'     => 'notice_for',
					'value'   => 'all',
					'compare' => '=',
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'teacher',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $mjschool_class_id,
						'compare' => 'IN',
					),
				),
			),
		)
	);
	
	return $notice_list_teacher;
}
/**
 * Retrieves notice board items for parents.
 *
 * Fetches up to three notices relevant to all parents or parent-specific.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_parent_notice_board() {

	$notice_list_parent = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => 3,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'notice_for',
						'value'   => 'parent',
						'compare' => '=',
					),
				),
			),
		)
	);
	
	return $notice_list_parent;
}
/**
 * Retrieves all parent dashboard notices based on their children's classes.
 *
 * Matches notices for 'all', 'parent', or specific child class groups.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_parent_notice_dashbord() {
	$parents_child_list = get_user_meta( get_current_user_id(), 'child', true );
	$class_array        = array();
	$unique             = array();
	
	if ( ! empty( $parents_child_list ) && is_array( $parents_child_list ) ) {
		foreach ( $parents_child_list as $user ) {
			$user_id   = absint( $user );
			$class_id  = get_user_meta( $user_id, 'class_name', true );
			if ( ! empty( $class_id ) ) {
				$class_id      = is_numeric( $class_id ) ? absint( $class_id ) : sanitize_text_field( $class_id );
				$class_array[] = $class_id;
			}
		}
		$unique = array_unique( $class_array );
	}

	$notice_list_parent = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				// Notice for all parent and all class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'smgt_class_id',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'notice_for',
						'value'   => 'parent',
						'compare' => '=',
					),
				),
				// Notice for all class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => 'all',
						'compare' => '=',
					),
				),
				// Notice for all own child class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $unique,
						'compare' => 'IN',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'student',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $unique,
						'compare' => 'IN',
					),
				),
			),
		)
	);
	
	return $notice_list_parent;
}
/**
 * Retrieves limited parent notices based on access rights.
 *
 * Fetches up to four notices relevant to the parent's children.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_parent_notice_dashboard_with_access_right() {
	$parents_child_list = get_user_meta( get_current_user_id(), 'child', true );
	$class_array        = array();
	$unique             = array();
	
	if ( ! empty( $parents_child_list ) && is_array( $parents_child_list ) ) {
		foreach ( $parents_child_list as $user ) {
			$user_id   = absint( $user );
			$class_id  = get_user_meta( $user_id, 'class_name', true );
			if ( ! empty( $class_id ) ) {
				$class_id      = is_numeric( $class_id ) ? absint( $class_id ) : sanitize_text_field( $class_id );
				$class_array[] = $class_id;
			}
		}
		$unique = array_unique( $class_array );
	}

	$notice_list_parent = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => 4,
			'meta_query'     => array(
				'relation' => 'OR',
				// Notice for all parent and all class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'smgt_class_id',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'notice_for',
						'value'   => 'parent',
						'compare' => '=',
					),
				),
				// Notice for all class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => 'all',
						'compare' => '=',
					),
				),
				// Notice for all own child class.
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $unique,
						'compare' => 'IN',
					),
				),
				array(
					'relation' => 'AND',
					array(
						'key'     => 'notice_for',
						'value'   => 'student',
						'compare' => '=',
					),
					array(
						'key'     => 'smgt_class_id',
						'value'   => $unique,
						'compare' => 'IN',
					),
				),
			),
		)
	);

	return $notice_list_parent;
}
/**
 * Retrieves notice board items for support staff.
 *
 * Fetches up to three notices applicable to all or support staff only.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_supportstaff_notice_board() {

	$notice_list_supportstaff = get_posts(
		array(
			'post_type'      => 'notice',
			'posts_per_page' => 3,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'notice_for',
						'value'   => 'supportstaff',
						'compare' => '=',
					),
				),
			),
		)
	);
	
	return $notice_list_supportstaff;
}
/**
 * Retrieves all support staff-related notices.
 *
 * Includes notices for all roles or support staff.
 *
 * @since 1.0.0
 *
 * @return WP_Post[] List of notice posts.
 */
function mjschool_supportstaff_notice_dashbord() {

	$notice_list_supportstaff = get_posts(
		array(
			'post_type'  => 'notice',
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => 'notice_for',
						'value'   => 'all',
						'compare' => '=',
					),
					array(
						'key'     => 'notice_for',
						'value'   => 'supportstaff',
						'compare' => '=',
					),
				),
			),
		)
	);
	
	return $notice_list_supportstaff;
}
/**
 * Checks whether the current user has access to a page based on role settings.
 *
 * Reads the MjSchool access rights settings and determines whether
 * the logged-in user's role allows the requested page.
 *
 * @since 1.0.0
 *
 * @return int 1 if access allowed, 0 otherwise.
 */
function mjschool_page_access_role_wise_and_accessright() {
	$menu = get_option( 'mjschool_access_right' );
	
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role  = array_shift( $user_roles );
	$flage      = 0;
	
	// Sanitize $_REQUEST['page'] before use
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	
	if ( ! empty( $menu ) && is_array( $menu ) ) {
		foreach ( $menu as $key => $value ) {
			if ( isset( $value['page_link'] ) && $value['page_link'] === $current_page ) {
				if ( isset( $value[ $user_role ] ) && $value[ $user_role ] === 0 ) {
					$flage = 0;
				} else {
					$flage = 1;
				}
			}
		}
	}
	return $flage;
}
/**
 * Checks connectivity to the licensing server.
 *
 * Attempts a socket connection to verify whether the remote API server is up.
 *
 * @since 1.0.0
 *
 * @return bool True if the server is reachable, false otherwise.
 */
function mjschool_check_our_server() {
	$api_server = 'license.3dlif.com';
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fsockopen -- External API check requires fsockopen
	$fp = @fsockopen( $api_server, 80, $errno, $errstr, 2 );
	if ( ! $fp ) {
		return false; /*server down.*/
	} else {
		fclose( $fp );
		return true; /*Server up.*/
	}
}
/**
 * Validates and registers a product license key with the remote licensing server.
 *
 * Attempts to connect to the licensing API, sends the domain, licence key, and email
 * for verification/registration, and returns a mapped response code.
 *
 * @since 1.0.0
 *
 * @param string $domain_name  The domain to register the license for.
 * @param string $licence_key  The Envato/License purchase key.
 * @param string $email        The email associated with the license.
 *
 * @return string              Status code ('0','1','2','3').
 */
function mjschool_check_product_key( $domain_name, $licence_key, $email ) {
	// Sanitize inputs at entry point
	$domain_name = esc_url_raw( $domain_name );
	$licence_key = sanitize_text_field( $licence_key );
	$email       = sanitize_email( $email );
	
	$api_server = 'license.3dlif.com';
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fsockopen -- External API check requires fsockopen
	$fp = @fsockopen( $api_server, 80, $errno, $errstr, 2 );
	
	if ( ! $fp ) {
		$server_rerror = 'Down';
	} else {
		$server_rerror = 'up';
		fclose( $fp );
	}
	
	if ( $server_rerror === 'up' ) {
		$response = wp_remote_post(
			'https://license.3dlif.com/admin/api/license/register',
			array(
				'body'    => array(
					'pkey'   => $licence_key,
					'email'  => $email,
					'domain' => $domain_name,
				),
				'timeout' => 30,
			)
		);
		
		if ( is_wp_error( $response ) ) {
			return '3';
		}
		
		$body = wp_remote_retrieve_body( $response );
		return mjschool_return_license_response( $body );
	} else {
		return '3';
	}
}
/**
 * Maps the API response from the license server into predefined status codes.
 *
 * @since 1.0.0
 *
 * @param string $response JSON encoded API response.
 *
 * @return string|null Status code ('0','1','2','3') or null if no match.
 */
function mjschool_return_license_response( $response ) {
	$response_data = json_decode( $response, true );
	
	if ( ! is_array( $response_data ) ) {
		return '3';
	}
	
	$error   = isset( $response_data['error'] ) ? $response_data['error'] : null;
	$message = isset( $response_data['message'] ) ? sanitize_text_field( $response_data['message'] ) : '';
	
	if ( $error === false && $message === 'License already registered' ) {
		return '2';
	} elseif ( $error === false && $message === 'Invalid license' ) {
		return '1';
	} elseif ( $error === false && $message === 'Failed to register license' ) {
		return '3';
	} elseif ( $error === true && $message === 'License registered successfully' ) {
		return '0';
	} elseif ( $error === false && $message === 'License already registered with the same domain' ) {
		return '0';
	}
	
	return '3'; // Default fallback
}
/**
 * Handles setup form submission for license verification and registration.
 *
 * Saves license information on success and sets session flags based on result.
 *
 * @since 1.0.0
 *
 * @param array $data Submitted form data including domain, key, and email.
 *
 * @return array Response array containing message and verification status.
 */
function mjschool_submit_setup_form( $data ) {
	// Sanitize inputs immediately
	$domain_name = isset( $data['domain_name'] ) ? esc_url_raw( $data['domain_name'] ) : '';
	$licence_key = isset( $data['licence_key'] ) ? sanitize_text_field( $data['licence_key'] ) : '';
	$email       = isset( $data['enter_email'] ) ? sanitize_email( $data['enter_email'] ) : '';
	
	$result = mjschool_check_product_key( $domain_name, $licence_key, $email );
	
	if ( $result === '1' ) {
		$message                     = esc_html__( 'Please provide correct Envato purchase key.', 'mjschool' );
		$_SESSION['mjschool_verify'] = '1';
	} elseif ( $result === '2' ) {
		$message                     = esc_html__( 'This purchase key is already registered with the different domain.please contact us at sales@mojoomla.com', 'mjschool' );
		$_SESSION['mjschool_verify'] = '2';
	} elseif ( $result === '3' ) {
		$message                     = esc_html__( 'There seems to be some problem please try after sometime or contact us on sales@mojoomla.com', 'mjschool' );
		$_SESSION['mjschool_verify'] = '3';
	} else {
		update_option( 'mjschool_domain_name', $domain_name, true );
		update_option( 'mjschool_licence_key', $licence_key, true );
		update_option( 'mjschool_setup_email', $email, true );
		$message                     = esc_html__( 'License key successfully registered.', 'mjschool' );
		$_SESSION['mjschool_verify'] = '0';
	}
	
	$result_array = array(
		'message'         => $message,
		'mjschool_verify' => isset( $_SESSION['mjschool_verify'] ) ? sanitize_text_field( $_SESSION['mjschool_verify'] ) : '',
	);
	
	return $result_array;
}
/**
 * Handles license reset form submission by requesting an OTP from server.
 *
 * Stores OTP session details when the API reports success.
 *
 * @since 1.0.0
 *
 * @param array $data Form data containing email and license key.
 *
 * @return string The API response message.
 */
function mjschool_reset_key_form( $data ) {
	// Sanitize inputs immediately
	$licence_key = isset( $data['licence_key'] ) ? sanitize_text_field( wp_unslash( $data['licence_key'] ) ) : '';
	$email       = isset( $data['enter_email'] ) ? sanitize_email( wp_unslash( $data['enter_email'] ) ) : '';
	
	$result        = mjschool_send_otp_for_license_reset( $licence_key, $email );
	$response_data = json_decode( $result, true );
	
	$message = isset( $response_data['message'] ) ? sanitize_text_field( $response_data['message'] ) : '';
	
	if ( $message === 'OTP sent to your email' ) {
		$_SESSION['licence_key'] = $licence_key;
		$_SESSION['enter_email'] = $email;
		$_SESSION['send_otp']    = '1';
	}
	
	return $message;
}
/**
 * Sends an OTP request for resetting a license key.
 *
 * @since 1.0.0
 *
 * @param string $licence_key The license key to reset.
 * @param string $email       User email for OTP.
 *
 * @return string JSON encoded response from API.
 */
function mjschool_send_otp_for_license_reset( $licence_key, $email ) {
	// Sanitize inputs at entry
	$licence_key = sanitize_text_field( $licence_key );
	$email       = sanitize_email( $email );
	
	$response = wp_remote_post(
		'https://license.3dlif.com/admin/api/license/send-otp',
		array(
			'body'    => array(
				'email' => $email,
				'pkey'  => $licence_key,
			),
			'timeout' => 30,
		)
	);
	
	if ( is_wp_error( $response ) ) {
		return wp_json_encode( array( 'message' => 'Error connecting to license server' ) );
	}
	
	return wp_remote_retrieve_body( $response );
}
/**
 * Verifies OTP for license key reset and clears stored license details on success.
 *
 * @since 1.0.0
 *
 * @param array $data Form data containing OTP value.
 *
 * @return string The API response message.
 */
function mjschool_reset_key_otp_verify_form( $data ) {
	// Sanitize session data when reading
	$licence_key = isset( $_SESSION['licence_key'] ) ? sanitize_text_field( $_SESSION['licence_key'] ) : '';
	$email       = isset( $_SESSION['enter_email'] ) ? sanitize_email( $_SESSION['enter_email'] ) : '';
	
	// Sanitize OTP input from the form
	$otp = isset( $data['verify_otp'] ) ? sanitize_text_field( wp_unslash( $data['verify_otp'] ) ) : '';
	
	$result        = mjschool_verify_otp_for_license_reset( $licence_key, $email, $otp );
	$response_data = json_decode( $result, true );
	
	$message = isset( $response_data['message'] ) ? sanitize_text_field( $response_data['message'] ) : '';
	
	if ( $message === 'License has been reset successfully' ) {
		unset( $_SESSION['licence_key'] );
		unset( $_SESSION['enter_email'] );
		unset( $_SESSION['send_otp'] );
		unset( $_SESSION['mjschool_verify'] );
		delete_option( 'mjschool_licence_key' );
		delete_option( 'mjschool_setup_email' );
	}
	
	return $message;
}
/**
 * Verifies the OTP for license reset with the remote server.
 *
 * @since 1.0.0
 *
 * @param string $licence_key License key being reset.
 * @param string $email       Registered email.
 * @param string $otp         OTP received by user.
 *
 * @return string JSON response from server.
 */
function mjschool_verify_otp_for_license_reset( $licence_key, $email, $otp ) {
	// Sanitize inputs at entry.
	$licence_key = sanitize_text_field( $licence_key );
	$email       = sanitize_email( $email );
	$otp         = sanitize_text_field( $otp );
	
	$response = wp_remote_post(
		'https://license.3dlif.com/admin/api/license/verify-otp',
		array(
			'body'    => array(
				'email' => $email,
				'otp'   => $otp,
				'pkey'  => $licence_key,
			),
			'timeout' => 30,
		)
	);
	
	if ( is_wp_error( $response ) ) {
		return wp_json_encode( array( 'message' => 'Error connecting to license server' ) );
	}
	
	return wp_remote_retrieve_body( $response );
}
/**
 * Handles setup form submission for Mobile App license verification.
 *
 * Saves license info for mobile app version in WordPress options.
 *
 * @since 1.0.0
 *
 * @param array $data Submitted form data for mobile app license.
 *
 * @return array Response array including message and verification status.
 */
function mjschool_submit_setup_form_mobileapp( $data ) {
	// Sanitize inputs immediately.
	$domain_name = isset( $data['mjschool_app_domain_name'] ) ? esc_url_raw( $data['mjschool_app_domain_name'] ) : '';
	$licence_key = isset( $data['mjschool_app_licence_key'] ) ? sanitize_text_field( $data['mjschool_app_licence_key'] ) : '';
	$email       = isset( $data['mjschool_app_setup_email'] ) ? sanitize_email( $data['mjschool_app_setup_email'] ) : '';
	
	$result = mjschool_check_product_key( $domain_name, $licence_key, $email );
	
	if ( $result === '1' ) {
		$message                         = esc_html__( 'Please provide correct Envato purchase key.', 'mjschool' );
		$_SESSION['mjschool_app_verify'] = '1';
	} elseif ( $result === '2' ) {
		$message                         = esc_html__( 'This purchase key is already registered with the different domain.please contact us at sales@mojoomla.com', 'mjschool' );
		$_SESSION['mjschool_app_verify'] = '2';
	} elseif ( $result === '3' ) {
		$message                         = esc_html__( 'There seems to be some problem please try after sometime or contact us on sales@mojoomla.com', 'mjschool' );
		$_SESSION['mjschool_app_verify'] = '3';
	} else {
		update_option( 'mjschool_app_domain_name', $domain_name, true );
		update_option( 'mjschool_app_licence_key', $licence_key, true );
		update_option( 'mjschool_app_setup_email', $email, true );
		$message                         = esc_html__( 'License key successfully registered.', 'mjschool' );
		$_SESSION['mjschool_app_verify'] = '0';
	}
	
	$result_array = array(
		'message'             => $message,
		'mjschool_app_verify' => isset( $_SESSION['mjschool_app_verify'] ) ? sanitize_text_field( $_SESSION['mjschool_app_verify'] ) : '',
	);
	
	return $result_array;
}
/**
 * Verifies whether the server is localhost.
 *
 * @since 1.0.0
 *
 * @param string $server_name Hostname to validate.
 *
 * @return bool True when server is localhost.
 */
function mjschool_check_server( $server_name ) {
	$server_name = sanitize_text_field( $server_name );
	if ( $server_name === 'localhost' ) {
		return true;
	}
	return false;
}
/**
 * Checks if plugin pages should be accessible depending on license verification.
 *
 * @since 1.0.0
 *
 * @param string $result License verification status code.
 *
 * @return bool True if page access is allowed.
 */
function mjschool_check_verify_or_not( $result ) {
	// Sanitize $_SERVER['SERVER_NAME']
	$server_name = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';
	
	// Sanitize $_REQUEST['page']
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	
	$pos = strrpos( $current_page, 'mjschool_' );
	
	if ( $pos !== false ) {
		if ( $server_name === 'localhost' ) {
			return true;
		} elseif ( $result === '0' || $result === '4' ) {
			return true;
		}
		return false;
	}
	
	return false;
}
/**
 * Determines whether the current page belongs to the MJ School plugin.
 *
 * @since 1.0.0
 *
 * @return bool True if current admin page is MJ School-related.
 */
function mjschool_is_smgt_page() {
	// Sanitize $_REQUEST['page']
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	$pos          = strrpos( $current_page, 'mjschool_' );
	
	if ( $pos !== false ) {
		return true;
	}
	
	return false;
}
/**
 * Returns translated DataTable language strings for localization support.
 *
 * @since 1.0.0
 *
 * @return array Language configuration array for DataTables.
 */
$obj_attend = new mjschool_Attendence_Manage();
function mjschool_datatable_multi_language() {
	$datatable_attr = array(
		'sEmptyTable'     => esc_html__( 'No data available in table', 'mjschool' ),
		'sInfo'           => esc_html__( 'Showing _START_ to _END_ of _TOTAL_ entries', 'mjschool' ),
		'sInfoEmpty'      => esc_html__( 'Showing 0 to 0 of 0 entries', 'mjschool' ),
		'sInfoFiltered'   => esc_html__( '(filtered from _MAX_ total entries)', 'mjschool' ),
		'sInfoPostFix'    => '',
		'sInfoThousands'  => ',',
		'sLengthMenu'     => esc_html__( ' _MENU_ ', 'mjschool' ),
		'sLoadingRecords' => esc_html__( 'Loading...', 'mjschool' ),
		'sProcessing'     => esc_html__( 'Processing...', 'mjschool' ),
		'sSearch'         => '',
		'sZeroRecords'    => esc_html__( 'No matching records found', 'mjschool' ),
		'Print'           => esc_html__( 'Print', 'mjschool' ),
		'oPaginate'       => array(
			'sFirst'    => esc_html__( 'First', 'mjschool' ),
			'sLast'     => esc_html__( 'Last', 'mjschool' ),
			'sNext'     => esc_html__( 'Next', 'mjschool' ),
			'sPrevious' => esc_html__( 'Previous', 'mjschool' ),
		),
		'searchBuilder'   => array(
			'add' => esc_html__( 'Add Filter', 'mjschool' ),
		),
		'oAria'           => array(
			'sSortAscending'  => esc_html__( ': activate to sort column ascending', 'mjschool' ),
			'sSortDescending' => esc_html__( ': activate to sort column descending', 'mjschool' ),
		),
	);
	
	return $datatable_attr;
}
/**
 * Returns translated menu title based on the given key and user role.
 *
 * @since 1.0.0
 *
 * @param string $key Menu key identifier.
 *
 * @return string Localized menu label.
 */
function mjschool_change_menu_title( $key ) {
	$key        = sanitize_key( $key );
	$school_obj = new MJSchool_Management( get_current_user_id() );
	$role       = $school_obj->role;
	
	if ( $role === 'parent' && $key === 'student' ) {
		$key = 'child';
	}
	
	$menu_titlearray = array(
		'general_settings'  => esc_html__( 'General Settings', 'mjschool' ),
		'email_template'    => esc_html__( 'Email Template', 'mjschool' ),
		'custom_field'      => esc_html__( 'Custom Field', 'mjschool' ),
		'mjschool_setting'  => esc_html__( 'SMS Setting', 'mjschool' ),
		'exam_hall'         => esc_html__( 'Exam Hall', 'mjschool' ),
		'grade'             => esc_html__( 'Grade', 'mjschool' ),
		'supportstaff'      => esc_html__( 'Supportstaff', 'mjschool' ),
		'admission'         => esc_html__( 'Admission', 'mjschool' ),
		'virtual_classroom' => esc_html__( 'Virtual Classroom', 'mjschool' ),
		'teacher'           => esc_html__( 'Teacher', 'mjschool' ),
		'student'           => esc_html__( 'Student', 'mjschool' ),
		'notification'      => esc_html__( 'Notification', 'mjschool' ),
		'child'             => esc_html__( 'Child', 'mjschool' ),
		'parent'            => esc_html__( 'Parent', 'mjschool' ),
		'subject'           => esc_html__( 'Subject', 'mjschool' ),
		'class'             => esc_html__( 'Class', 'mjschool' ),
		'schedule'          => esc_html__( 'Class Routine', 'mjschool' ),
		'attendance'        => esc_html__( 'Attendance', 'mjschool' ),
		'exam'              => esc_html__( 'Exam', 'mjschool' ),
		'manage_marks'      => esc_html__( 'Manage Marks', 'mjschool' ),
		'migration'         => esc_html__( 'Migration', 'mjschool' ),
		'feepayment'        => esc_html__( 'Fee Payment', 'mjschool' ),
		'payment'           => esc_html__( 'Payment', 'mjschool' ),
		'transport'         => esc_html__( 'Transport', 'mjschool' ),
		'hostel'            => esc_html__( 'Hostel', 'mjschool' ),
		'notice'            => esc_html__( 'Notice Board', 'mjschool' ),
		'event'             => esc_html__( 'Event', 'mjschool' ),
		'message'           => esc_html__( 'Message', 'mjschool' ),
		'holiday'           => esc_html__( 'Holiday', 'mjschool' ),
		'library'           => esc_html__( 'Library', 'mjschool' ),
		'account'           => esc_html__( 'Account', 'mjschool' ),
		'report'            => esc_html__( 'Report', 'mjschool' ),
		'homework'          => esc_html__( 'Homework', 'mjschool' ),
	);
	
	return isset( $menu_titlearray[ $key ] ) ? $menu_titlearray[ $key ] : '';
}
/**
 * Retrieves a list of inactive/approval-pending student user IDs.
 *
 * @since 1.0.0
 *
 * @return array List of student IDs pending approval.
 */
function mjschool_approve_student_list() {
	$studentdata = get_users(
		array(
			'meta_key' => 'hash',
			'role'     => 'student',
		)
	);

	$inactive_student_id = wp_list_pluck( $studentdata, 'ID' );
	return $inactive_student_id;
}
/**
 * Fetches a remote file using cURL.
 *
 * @since 1.0.0
 *
 * @param string $url     File URL.
 * @param int    $timeout Timeout in seconds.
 *
 * @return string|false File content on success, false on failure.
 */
function mjschool_get_remote_file( $url, $timeout = 30 ) {
	// Use WordPress HTTP API instead of curl
	$url     = esc_url_raw( $url );
	$timeout = absint( $timeout );
	
	$response = wp_remote_get(
		$url,
		array(
			'timeout' => $timeout,
		)
	);
	
	if ( is_wp_error( $response ) ) {
		return false;
	}
	
	return wp_remote_retrieve_body( $response );
}
/**
 * Marks a message as read by updating its status in the database.
 *
 * @since 1.0.0
 *
 * @param int $id Message ID.
 *
 * @return int|false Rows updated or false on failure.
 */
function mjschool_change_read_status( $id ) {
	global $wpdb;
	$table_name            = $wpdb->prefix . 'mjschool_message';
	$data['status']        = 1;
	$whereid['message_id'] = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_message_status = $wpdb->update( $table_name, $data, $whereid );
	
	return $retrieve_message_status;
}
/**
 * Marks message replies as read by the current user.
 *
 * @since 1.0.0
 *
 * @param int $id Message ID.
 *
 * @return int|false Rows updated or false on failure.
 */
function mjschool_change_read_status_reply( $id ) {
	global $wpdb;
	$mjschool_message_replies      = $wpdb->prefix . 'mjschool_message_replies';
	$data['status']                = 1;
	$whereid['message_id']         = absint( $id );
	$whereid['receiver_id']        = absint( get_current_user_id() );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_message_reply_status = $wpdb->update( $mjschool_message_replies, $data, $whereid );
	
	return $retrieve_message_reply_status;
}
/**
 * Retrieves the class ID for a given subject ID.
 *
 * @since 1.0.0
 *
 * @param int $subject_id Subject ID.
 *
 * @return int Class ID.
 */
function mjschool_get_subject_class( $subject_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$id         = absint( $subject_id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT class_id FROM $table_name WHERE subid=%d", $id ) );
	
	return isset( $result->class_id ) ? $result->class_id : 0;
}
/**
 * Gets all subjects assigned to a specific teacher.
 *
 * @since 1.0.0
 *
 * @param int $tid Teacher ID.
 *
 * @return array List of subject objects.
 */
function mjschool_get_teachers_subjects( $tid ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$teacher_id = absint( $tid );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id=%d", $teacher_id ) );
	
	return $result;
}
/**
 * Retrieves all active students, excluding unapproved ones.
 *
 * @since 1.0.0
 *
 * @return array List of student WP_User objects.
 */
function mjschool_get_all_student_list() {
	$exlude_id = mjschool_approve_student_list();
	$student   = get_users(
		array(
			'role'    => 'student',
			'exclude' => $exlude_id,
		)
	);
	return $student;
}
/**
 * Retrieves all students belonging to the same class as the specified student.
 *
 * @since 1.0.0
 *
 * @param int $id Student ID.
 *
 * @return array List of WP_User classmate objects.
 */
function mjschool_get_teacher_class_student( $id ) {
	$student_id = absint( $id );
	$meta_val   = get_user_meta( $student_id, 'class_name', true );

	$meta_query_result = get_users(
		array(
			'meta_key'   => 'class_name',
			'meta_value' => $meta_val,
		)
	);

	return $meta_query_result;
}
/**
 * Checks which teachers have the given class assigned to them.
 *
 * @since 1.0.0
 *
 * @param int $id Class ID.
 *
 * @return array List of teacher IDs.
 */
function mjschool_check_class_exits_in_teacher_class( $id ) {
	$id          = absint( $id );
	$TeacherData = get_users( array( 'role' => 'teacher' ) );
	$Teacher     = array();
	
	if ( ! empty( $TeacherData ) ) {
		foreach ( $TeacherData as $teacher ) {
			$TeacherClass = get_user_meta( $teacher->ID, 'class_name', true );
			if ( is_array( $TeacherClass ) ) {
				if ( in_array( $id, array_map( 'absint', $TeacherClass ), true ) ) {
					$Teacher[] = $teacher->ID;
				}
			}
		}
	}
	
	return $Teacher;
}
/**
 * Retrieves active students excluding those pending approval.
 *
 * (Same behaviour as mjschool_get_all_student_list)
 *
 * @since 1.0.0
 *
 * @return array List of student WP_User objects.
 */
function mjschool_get_all_student_list_class() {
	$exlude_id = mjschool_approve_student_list();
	$student   = get_users(
		array(
			'role'    => 'student',
			'exclude' => $exlude_id,
		)
	);
	return $student;
}
/**
 * Outputs HTML <option> lists of users available for messaging,
 * filtered based on user role and plugin settings.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML directly.
 */
function mjschool_get_all_user_in_message() {
	$school_obj         = new MJSchool_Management( get_current_user_id() );
	$teacher            = get_users( array( 'role' => 'teacher' ) );
	$parent             = get_users( array( 'role' => 'parent' ) );
	$exlude_id          = mjschool_approve_student_list();
	$student            = get_users(
		array(
			'role'    => 'student',
			'exclude' => $exlude_id,
		)
	);
	$supportstaff       = get_users( array( 'role' => 'supportstaff' ) );
	$parents_child_list = get_user_meta( get_current_user_id(), 'child', true );
	
	$all_user = array(
		'student'      => $student,
		'teacher'      => $teacher,
		'parent'       => $parent,
		'supportstaff' => $supportstaff,
	);
	
	if ( $school_obj->role === 'administrator' || $school_obj->role === 'teacher' ) {
		$all_user = array(
			'student'      => $student,
			'teacher'      => $teacher,
			'parent'       => $parent,
			'supportstaff' => $supportstaff,
		);
	}
	
	if ( $school_obj->role === 'parent' ) {
		if ( get_option( 'mjschool_parent_send_message' ) ) {
			if ( ! empty( $parents_child_list ) && is_array( $parents_child_list ) ) {
				$class_array = array();
				foreach ( $parents_child_list as $user ) {
					$user_id       = absint( $user );
					$class_id      = get_user_meta( $user_id, 'class_name', true );
					$class_array[] = $class_id;
				}
				$unique  = array_unique( $class_array );
				$student = array();
				
				if ( ! empty( $unique ) ) {
					foreach ( $unique as $class_id ) {
						$class_id  = absint( $class_id );
						$student[] = get_users(
							array(
								'role'       => 'student',
								'meta_key'   => 'class_name',
								'meta_value' => $class_id,
							)
						);
					}
				}
			}
			$all_user = array(
				'student'      => $student,
				'teacher'      => $teacher,
				'parent'       => $parent,
				'supportstaff' => $supportstaff,
			);
		} else {
			$all_user = array(
				'teacher'      => $teacher,
				'parent'       => $parent,
				'supportstaff' => $supportstaff,
			);
		}
	}
	
	if ( get_option( 'mjschool_student_send_message' ) ) {
		if ( $school_obj->role === 'student' ) {
			$school_obj->class_info = $school_obj->mjschool_get_user_class_id( get_current_user_id() );
			$student                = $school_obj->mjschool_get_student_list( $school_obj->class_info->class_id );
			$all_user               = array( 'student' => $student );
		}
	}
	
	$return_array = array();
	foreach ( $all_user as $key => $value ) {
		if ( ! empty( $value ) ) {
			echo '<optgroup label="' . esc_attr( $key ) . '" style="text-transform: capitalize;">';
			
			foreach ( $value as $user ) {
				if ( get_option( 'mjschool_parent_send_message' ) ) {
					if ( $key === 'student' && $school_obj->role === 'parent' ) {
						if ( is_array( $user ) ) {
							foreach ( $user as $student_class ) {
								if ( isset( $student_class->ID ) && isset( $student_class->display_name ) ) {
									echo '<option value="' . esc_attr( $student_class->ID ) . '">' . esc_html( $student_class->display_name ) . '</option>';
								}
							}
						}
					} else {
						if ( isset( $user->ID ) && isset( $user->display_name ) ) {
							echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . '</option>';
						}
					}
				} else {
					if ( isset( $user->ID ) && isset( $user->display_name ) ) {
						echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . '</option>';
					}
				}
			}
			echo '</optgroup>';
		}
	}
}
/**
 * Sends a reply message, uploads attachments, stores the message in the database,
 * and triggers email notifications if enabled.
 *
 * @since 1.0.0
 *
 * @param array $data Message data including receiver IDs and attachments.
 *
 * @return int|false Insert result or false.
 */
function mjschool_send_replay_message( $data ) {
	global $wpdb;
	$table_name        = $wpdb->prefix . 'mjschool_message_replies';
	$upload_docs_array = array();
	
	// Validate and sanitize file uploads
	if ( ! empty( $_FILES['message_attachment']['name'] ) && is_array( $_FILES['message_attachment']['name'] ) ) {
		$count_array = count( $_FILES['message_attachment']['name'] );
		
		for ( $a = 0; $a < $count_array; $a++ ) {
			// Validate file exists and has no error
			if ( isset( $_FILES['message_attachment']['error'][ $a ] ) && 
			     $_FILES['message_attachment']['error'][ $a ] === UPLOAD_ERR_OK ) {
				
				$document_array = array(
					'name'     => isset( $_FILES['message_attachment']['name'][ $a ] ) ? sanitize_file_name( $_FILES['message_attachment']['name'][ $a ] ) : '',
					'type'     => isset( $_FILES['message_attachment']['type'][ $a ] ) ? sanitize_mime_type( $_FILES['message_attachment']['type'][ $a ] ) : '',
					'tmp_name' => isset( $_FILES['message_attachment']['tmp_name'][ $a ] ) ? $_FILES['message_attachment']['tmp_name'][ $a ] : '',
					'error'    => isset( $_FILES['message_attachment']['error'][ $a ] ) ? absint( $_FILES['message_attachment']['error'][ $a ] ) : UPLOAD_ERR_NO_FILE,
					'size'     => isset( $_FILES['message_attachment']['size'][ $a ] ) ? absint( $_FILES['message_attachment']['size'][ $a ] ) : 0,
				);
				
				$get_file_name = $document_array['name'];
				
				if ( ! empty( $document_array['name'] ) ) {
					$upload_result = mjschool_load_documets_new( $document_array, $document_array, $get_file_name );
					if ( $upload_result ) {
						$upload_docs_array[] = $upload_result;
					}
				}
			}
		}
	}
	
	$upload_docs_array_filter = array_filter( $upload_docs_array );
	$attachment               = ! empty( $upload_docs_array_filter ) ? implode( ',', $upload_docs_array_filter ) : '';
	
	$result = '';
	
	if ( ! empty( $data['receiver_id'] ) && is_array( $data['receiver_id'] ) ) {
		foreach ( $data['receiver_id'] as $receiver_id ) {
			$receiver_id = absint( $receiver_id );
			
			$messagedata = array(
				'message_id'         => isset( $data['message_id'] ) ? absint( $data['message_id'] ) : 0,
				'sender_id'          => isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0,
				'receiver_id'        => $receiver_id,
				'message_comment'    => isset( $data['replay_message_body'] ) ? sanitize_textarea_field( wp_unslash( $data['replay_message_body'] ) ) : '',
				'message_attachment' => $attachment,
				'status'             => 0,
				'created_date'       => current_time( 'mysql' ),
			);
			
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_name, $messagedata );
			
			if ( $result ) {
				$mjschool_name             = sanitize_text_field( get_option( 'mjschool_name' ) );
				$SubArr['{{school_name}}'] = $mjschool_name;
				$SubArr['{{from_mail}}']   = mjschool_get_display_name( isset( $data['user_id'] ) ? absint( $data['user_id'] ) : 0 );
				$MailSub                   = mjschool_string_replacement( $SubArr, get_option( 'mjschool_message_received_mailsubject' ) );
				
				$user_info = get_userdata( $receiver_id );
				if ( $user_info ) {
					$to = sanitize_email( $user_info->user_email );
					
					$MailBody                      = get_option( 'mjschool_message_received_mailcontent' );
					$MesArr['{{receiver_name}}']   = mjschool_get_display_name( $receiver_id );
					$MesArr['{{message_content}}'] = isset( $data['replay_message_body'] ) ? sanitize_textarea_field( wp_unslash( $data['replay_message_body'] ) ) : '';
					$MesArr['{{school_name}}']     = $mjschool_name;
					$messg                         = mjschool_string_replacement( $MesArr, $MailBody );
					
					$headers  = '';
					$headers .= 'From: ' . $mjschool_name . ' <noreplay@gmail.com>' . "\r\n";
					$headers .= "MIME-Version: 1.0\r\n";
					$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
					
					// MAIL CONTENT WITH TEMPLATE DESIGN.
					$email_template = mjschool_get_mail_content_with_template_design( $messg );
					
					if ( absint( get_option( 'mjschool_mail_notification' ) ) === 1 ) {
						wp_mail( $to, $MailSub, $email_template, $headers );
					}
				}
			}
		}
	}
	
	return $result;
}
/**
 * Get phone code for a given country from XML file.
 *
 * @since 1.0.0
 * @param string $country_name Country name.
 * @return string|null Phone code if found, otherwise null.
 */
function mjschool_get_country_phonecode( $country_name ) {
	$country_name = sanitize_text_field( $country_name );
	$url = MJSCHOOL_PLUGIN_URL . "/assets/xml/mjschool-country-list.xml";
	$xml = simplexml_load_file( $url ) or wp_die( 'Error: Cannot create object' );
	foreach ( $xml as $country ) {
		if ( $country_name == $country->name ) {
			return $country->phoneCode;
		}
	}
}
/**
 * Get primary role of a user.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @return string|null User role slug.
 */
function mjschool_get_roles( $user_id ) {
	$user_id = absint( $user_id );
	$user    = new WP_User( $user_id );
	
	if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
		foreach ( $user->roles as $role ) {
			return sanitize_key( $role );
		}
	}	
	return '';
}
/**
 * Get parent IDs linked to a student.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Array of parent IDs.
 */
function mjschool_get_student_parent_id( $student_id ) {
	$id             = absint( $student_id );
	$parent         = get_user_meta( $id, 'parent_id' );
	$parent_idarray = array();
	
	if ( ! empty( $parent ) && is_array( $parent ) && isset( $parent[0] ) && is_array( $parent[0] ) ) {
		foreach ( $parent[0] as $parent_id ) {
			$parent_idarray[] = absint( $parent_id );
		}
	}
	
	return $parent_idarray;
}
/**
 * Get child IDs linked to a parent account.
 *
 * @since 1.0.0
 * @param int $id Parent ID.
 * @return array Array of child IDs.
 */
function mjschool_get_parents_child_id( $id ) {
	$parent_id      = absint( $id );
	$parent         = get_user_meta( $parent_id, 'child' );
	$parent_idarray = array();
	
	if ( ! empty( $parent ) && is_array( $parent ) && isset( $parent[0] ) && is_array( $parent[0] ) ) {
		foreach ( $parent[0] as $child_id ) {
			$parent_idarray[] = absint( $child_id );
		}
	}
	
	return $parent_idarray;
}
/**
 * Retrieve users for Notice module based on role, class, and section.
 *
 * @since 1.0.0
 * @param string $role User role (teacher|student|parent|administrator|all).
 * @param string|int $class_id Class ID or 'all'.
 * @param int $section_id Section ID.
 * @return array List of user objects.
 */
function mjschool_get_user_notice( $role, $class_id, $section_id = 0 ) {
	// Sanitize inputs
	$role       = sanitize_key( $role );
	$class_id   = is_numeric( $class_id ) ? absint( $class_id ) : sanitize_text_field( $class_id );
	$section_id = absint( $section_id );
	
	if ( $role === 'all' ) {
		$userdata = array();
		$roles    = array( 'teacher', 'student', 'parent', 'supportstaff' );
		
		foreach ( $roles as $user_role ) :
			$users_query = new WP_User_Query(
				array(
					'fields'  => 'all_with_meta',
					'role'    => $user_role,
					'orderby' => 'display_name',
				)
			);
			$results     = $users_query->get_results();
			
			if ( $results ) {
				$userdata = array_merge( $userdata, $results );
			}
		endforeach;
	} elseif ( $role === 'parent' ) {
		$new = array();
		
		if ( $class_id === 'all' ) {
			$userdata = get_users( array( 'role' => $role ) );
		} else {
			$userdata = get_users(
				array(
					'role'       => 'student',
					'meta_key'   => 'class_name',
					'meta_value' => $class_id,
				)
			);

			if ( ! empty( $userdata ) ) {
				foreach ( $userdata as $users ) {
					$parent = get_user_meta( $users->ID, 'parent_id', true );
					
					if ( ! empty( $parent ) && is_array( $parent ) ) {
						foreach ( $parent as $p ) {
							$new[] = array( 'ID' => absint( $p ) );
						}
					}
				}
			}
			$userdata = $new;
		}
	} elseif ( $role === 'administrator' ) {
		$userdata = get_users( array( 'role' => $role ) );
	} else {
		if ( $class_id === 'all' ) {
			$userdata = get_users( array( 'role' => $role ) );
		} elseif ( $section_id !== 0 ) {
			$userdata = get_users(
				array(
					'meta_key'   => 'class_section',
					'meta_value' => $section_id,
					'meta_query' => array(
						array(
							'key'     => 'class_name',
							'value'   => $class_id,
							'compare' => '=',
						),
					),
					'role'       => 'student',
				)
			);
		} else {
			$userdata = get_users(
				array(
					'role'       => $role,
					'meta_key'   => 'class_name',
					'meta_value' => $class_id,
				)
			);
		}
	}
	return $userdata;
}
/**
 * Insert a new record into a custom table.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name without prefix.
 * @param array $records Data to insert.
 * @return int Inserted record ID.
 */
function mjschool_insert_record( $mjschool_table_name, $records ) {
	global $wpdb;	
	// Sanitize table name.
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_name, $records );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$ids = $wpdb->insert_id;
	return $ids;
}
/**
 * Insert a new class section.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @param array $sectiondata Section data.
 * @return int|false Insert result.
 */
function mjschool_add_class_section( $mjschool_table_name, $sectiondata ) {
	global $wpdb;
	// Sanitize table name.
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_name, $sectiondata );	
	return $result;
}
/**
 * Get all sections under a class.
 *
 * @since 1.0.0
 * @param int|string $id Class ID or 'all'.
 * @return array Section list.
 */
function mjschool_get_class_sections( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class_section';
	if ( ! empty( $id ) ) {
		if ( $id === 'all' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%s", $id ) );
		} else {
			$id = absint( $id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d", $id ) );
		}
		return $result;
	}
	return array();
}
/**
 * Get class section name by ID.
 *
 * @since 1.0.0
 * @param int $id Section ID.
 * @return string Section name.
 */
function mjschool_get_class_sections_name( $id ) {
	global $wpdb;
	$table_name       = $wpdb->prefix . 'mjschool_class_section';
	$class_section_id = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$class_sections_name = $wpdb->get_row( $wpdb->prepare( "SELECT section_name FROM $table_name WHERE id=%d", $class_section_id ) );
	if ( ! empty( $class_sections_name ) ) {
		return $class_sections_name->section_name;
	} else {
		return ' ';
	}
}

/**
 * Get section name by ID.
 *
 * @since 1.0.0
 * @param int $id Section ID.
 * @return string Section name.
 */
function mjschool_get_section_name( $id ) {
	global $wpdb;
	$table_name       = $wpdb->prefix . 'mjschool_class_section';
	$class_section_id = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id=%d", $class_section_id ) );
	
	if ( isset( $result->section_name ) ) {
		return $result->section_name;
	} else {
		return '';
	}
}

/**
 * Delete a class section and log audit.
 *
 * @since 1.0.0
 * @param int $section_id Section ID.
 * @return int Rows affected.
 */
function mjschool_delete_class_section( $section_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class_section';
	$id         = absint( $section_id );
	
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Class Section Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id ) );
	
	return $result;
}

/**
 * Update a record in a custom table.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @param array $data New data.
 * @param array $record_id Where conditions.
 * @return int|false Rows updated or false.
 */
function mjschool_update_record( $mjschool_table_name, $data, $record_id ) {
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->update( $table_name, $data, $record_id );
	return $result;
}

/**
 * Delete a class and log audit entry.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @param int $id Class ID.
 * @return int Rows affected.
 */
function mjschool_delete_class( $mjschool_table_name, $id ) {
	global $wpdb;
	// Sanitize table name.
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$record_id           = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d", $record_id ) );
	$class = isset( $event->class_name ) ? $event->class_name : '';
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Class Deleted', 'mjschool' ) . '( ' . $class . ' )', get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE class_id = %d", $record_id ) );
	
	return $result;
}
/**
 * Delete a grade entry.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @param int $id Grade ID.
 * @return int Rows affected.
 */
function mjschool_delete_grade( $mjschool_table_name, $id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Grade Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	
	global $wpdb;
	$record_id = absint( $id );
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE grade_id = %d", $record_id ) );
	
	return $result;
}

/**
 * Delete an exam and all related records (receipt & timetable).
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @param int $id Exam ID.
 * @return int Rows affected.
 */
function mjschool_delete_exam( $mjschool_table_name, $id ) {
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$record_id           = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id=%d", $record_id ) );
	
	if ( ! empty( $event ) && isset( $event->exam_name ) ) {
		$exam = $event->exam_name;
		$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( esc_html__( 'Exam Deleted', 'mjschool' ) . '( ' . $exam . ' )', get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	}
	
	$mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	$mjschool_exam_time_table   = $wpdb->prefix . 'mjschool_exam_time_table';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE exam_id = %d", $record_id ) );
	
	if ( $result ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result_receipt_delete = $wpdb->query( $wpdb->prepare( "DELETE FROM $mjschool_exam_hall_receipt WHERE exam_id = %d", $record_id ) );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result_timetable_delete = $wpdb->query( $wpdb->prepare( "DELETE FROM $mjschool_exam_time_table WHERE exam_id = %d", $record_id ) );
	}	
	return $result;
}
/**
 * Delete user meta and remove user account completely.
 *
 * @since 1.0.0
 * @param int $id User ID.
 * @return mixed True on success or WP_Error.
 */
function mjschool_delete_usedata( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'usermeta';
	$record_id  = absint( $id );
	$user_data  = get_userdata( $record_id );
	$user = '';
	if ( $user_data ) {
		$user = mjschool_get_user_name_by_id( $user_data->ID );
	}
	
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	
	mjschool_append_audit_log( esc_html__( 'User Deleted', 'mjschool' ) . '( ' . $user . ' )', $record_id, get_current_user_id(), 'delete', $current_page );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE user_id = %d", $record_id ) );
	
	if ( ! current_user_can( 'delete_user', $record_id ) ) {
		return new WP_Error( 'permission_denied', 'You are not allowed to delete this user' );
	}
	
	$retuenval = wp_delete_user( $record_id );
	
	return $retuenval;
}

/**
 * Delete a message by ID.
 *
 * @since 1.0.0
 * @param string $tablenm Table name.
 * @param int $id Message ID.
 * @return int Rows affected.
 */
function mjschool_delete_message( $tablenm, $id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	
	mjschool_append_audit_log( esc_html__( 'Message Deleted', 'mjschool' ), null, get_current_user_id(), 'delete', $current_page );
	
	global $wpdb;
	$record_id = absint( $id );
	// Sanitize table name.
	$tablenm    = sanitize_key( $tablenm );
	$table_name = $wpdb->prefix . $tablenm;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE message_id = %d", $record_id ) );
	
	return $result;
}

/**
 * Get class name by ID.
 *
 * @since 1.0.0
 * @param int $id Class ID.
 * @return string Class name.
 */
function mjschool_get_class_name( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$cid        = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$classname = $wpdb->get_row( $wpdb->prepare( "SELECT class_name FROM $table_name WHERE class_id=%d", $cid ) );
	
	if ( ! empty( $classname ) && isset( $classname->class_name ) ) {
		return $classname->class_name;
	} else {
		return 'N/A';
	}
}

/**
 * Get fee term name by fee ID.
 *
 * @since 1.0.0
 * @param int $id Fee ID.
 * @return string Fee title.
 */
function mjschool_get_fees_term_name( $id ) {
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	$fees_id             = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$classname = $wpdb->get_row( $wpdb->prepare( "SELECT fees_title_id FROM $table_mjschool_fees WHERE fees_id=%d", $fees_id ) );
	
	if ( ! empty( $classname ) && isset( $classname->fees_title_id ) ) {
		return get_the_title( $classname->fees_title_id );
	} else {
		return ' ';
	}
}
/**
 * Determine payment status (Not Paid / Partially Paid / Fully Paid).
 *
 * @since 1.0.0
 * @param int $id Payment record ID.
 * @return string Payment status.
 */
function mjschool_get_payment_status( $id ) {
	global $wpdb;
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$fees_pay_id                 = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE fees_pay_id=%d", $fees_pay_id ) );
	
	if ( ! empty( $result ) ) {
		if ( isset( $result->total_amount ) && $result->total_amount > 0 ) {
			if ( ! isset( $result->fees_paid_amount ) || $result->fees_paid_amount === 0 ) {
				return 'Not Paid';
			} elseif ( $result->fees_paid_amount < $result->total_amount ) {
				return 'Partially Paid';
			} else {
				return 'Fully Paid';
			}
		} else {
			return 'Fully Paid';
		}
	} else {
		return '';
	}
}
/**
 * Get single fee payment record by ID.
 *
 * @since 1.0.0
 * @param int $id Payment ID.
 * @return object|null Payment row.
 */
function mjschool_get_single_fees_payment_record( $id ) {
	global $wpdb;
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$fees_pay_id                 = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees_payment WHERE fees_pay_id=%d", $fees_pay_id ) );
	return $result;
}

/**
 * Get all payment history entries for a fees_pay_id.
 *
 * @since 1.0.0
 * @param int $fees_pay_id Payment ID.
 * @return array Payment history list.
 */
function mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id ) {
	global $wpdb;
	$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
	$fees_pay_id                        = absint( $fees_pay_id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fee_payment_history WHERE fees_pay_id=%d", $fees_pay_id ) );
	
	return $result;
}
/**
 * Get name of a user by ID.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @return string Username.
 */
function mjschool_get_user_name_by_id( $user_id ) {
	$user_id   = absint( $user_id );
	$user_info = get_userdata( $user_id );
	
	if ( $user_info ) {
		return $user_info->display_name;
	} else {
		return 'N/A';
	}
}

/**
 * Get display name of a user by ID.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @return string Username.
 */
function mjschool_get_display_name( $user_id ) {
	$user_id = absint( $user_id );
	$user    = get_userdata( $user_id );
	
	if ( ! $user ) {
		return 'N/A';
	}	
	return $user->display_name;
}
/**
 * Get user email address by ID.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @return string|false Email address.
 */
function mjschool_get_email_id_by_user_id( $user_id ) {
	$user_id = absint( $user_id );
	$user    = get_userdata( $user_id );
	
	if ( ! $user ) {
		return false;
	}
	return $user->user_email;
}
/**
 * Get full name of teacher (first + middle + last).
 *
 * @since 1.0.0
 * @param int $id Teacher ID.
 * @return string Full name.
 */
function mjschool_get_teacher( $id ) {
	$id        = absint( $id );
	$user_info = get_userdata( $id );	
	if ( $user_info ) {
		$first  = isset( $user_info->first_name ) ? $user_info->first_name : '';
		$middle = isset( $user_info->middle_name ) ? $user_info->middle_name : '';
		$last   = isset( $user_info->last_name ) ? $user_info->last_name : '';
		
		return trim( $first . ' ' . $middle . ' ' . $last );
	}
	
	return '';
}

/**
 * Get all records from a custom table.
 *
 * @since 1.0.0
 * @param string $mjschool_table_name Table name.
 * @return array List of rows.
 */
function mjschool_get_all_data( $mjschool_table_name ) {
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with sanitized table name
	$retrieve_subjects = $wpdb->get_results( "SELECT * FROM {$table_name}" );
	
	return $retrieve_subjects;
}
/**
 * Retrieves all certificates owned by the logged-in student.
 *
 * @param string $mjschool_table_name Database table name (without prefix).
 * @return array List of certificate records.
 * @since 1.0.0
 */
function mjschool_get_all_certificate_owns( $mjschool_table_name ) {
	global $wpdb;
	$user_id = get_current_user_id();
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subjects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id=%d", $user_id ) );
	
	return $retrieve_subjects;
}

/**
 * Retrieves all certificates for a specific student or the logged-in user.
 *
 * @param int|null $student_id Student ID or null for current user.
 * @return array Certificate records.
 * @since 1.0.0
 */
function mjschool_get_all_certificate_parents( $student_id = null ) {
	global $wpdb;
	$user_id    = $student_id ? absint( $student_id ) : absint( get_current_user_id() );
	$table_name = $wpdb->prefix . 'mjschool_certificate';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d", $user_id )
	);
}

/**
 * Retrieves all subject data created by the current user.
 *
 * @param string $mjschool_table_name Table name without prefix.
 * @return array List of subjects.
 * @since 1.0.0
 */
function mjschool_get_all_own_subject_data( $mjschool_table_name ) {
	global $wpdb;
	$user_id = get_current_user_id();
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subjects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE created_by=%d", $user_id ) );
	
	return $retrieve_subjects;
}

/**
 * Retrieves certificate data by student ID.
 *
 * @param int $id Student ID.
 * @return array Certificate details.
 * @since 1.0.0
 */
function mjschool_get_certificate_by_student_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_certificate';
	$class_id   = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id=%d", $class_id ) );
	
	return $retrieve_subject;
}

/**
 * Retrieves subjects by class ID.
 *
 * @param int $id Class ID.
 * @return array List of subjects.
 * @since 1.0.0
 */
function mjschool_get_subject_by_class_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$class_id   = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d", $class_id ) );
	
	return $retrieve_subject;
}

/**
 * Retrieves subject details by subject ID.
 *
 * @param int $id Subject ID.
 * @return object|null Subject record.
 * @since 1.0.0
 */
function mjschool_get_subject( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$sid        = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE subid=%d", $sid ) );
	
	return $retrieve_subject;
}

/**
 * Retrieves subject name and code for a given subject ID.
 *
 * @param int $id Subject ID.
 * @return string Subject name with code.
 * @since 1.0.0
 */
function mjschool_get_single_subject_name( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$subject_id = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT sub_name, subject_code FROM $table_name WHERE subid=%d", $subject_id ) );
	
	if ( ! empty( $retrieve_subject ) && isset( $retrieve_subject->sub_name ) && isset( $retrieve_subject->subject_code ) ) {
		return $retrieve_subject->sub_name . '-' . $retrieve_subject->subject_code;
	} else {
		return '';
	}
}

/**
 * Retrieves class details using class ID.
 *
 * @param int $id Class ID.
 * @return object|null Class record.
 * @since 1.0.0
 */
function mjschool_get_class_by_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$sid        = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d", $sid ) );
	
	return $retrieve_subject;
}

/**
 * Retrieves class name by class ID.
 *
 * @param int $id Class ID.
 * @return string Class name.
 * @since 1.0.0
 */
function mjschool_get_class_name_by_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$sid        = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d", $sid ) );
	
	if ( isset( $retrieve_subject->class_name ) ) {
		return $retrieve_subject->class_name;
	}
	
	return '';
}

/**
 * Retrieves class ID based on class name.
 *
 * @param string $class_name Class name.
 * @return int Class ID.
 * @since 1.0.0
 */
function mjschool_get_class_id_by_name( $class_name ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$class_name = sanitize_text_field( $class_name );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_name = %s", $class_name ) );	
	if ( isset( $retrieve_subject->class_id ) ) {
		return absint( $retrieve_subject->class_id );
	}	
	return 0;
}

/**
 * Retrieves exam details by exam ID.
 *
 * @param int $id Exam ID.
 * @return object|null Exam record.
 * @since 1.0.0
 */
function mjschool_get_exam_by_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	$eid        = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id = %d", $eid ) );
	return $retrieve_subject;
}

/**
 * Retrieves all exams by class ID.
 *
 * @param int $id Class ID.
 * @return array Exam list.
 * @since 1.0.0
 */
function mjschool_get_all_exam_by_class_id_all( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	$class_id   = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id ) );
	return $retrieve_data;
}
/**
 * Retrieves class data for multiple class IDs.
 *
 * @param array $class_id Array of class IDs.
 * @return array List of class records.
 * @since 1.0.0
 */
function mjschool_get_all_class_data_by_class_array( $class_id ) {
	global $wpdb;
	$user_id    = absint( get_current_user_id() );
	$table_name = $wpdb->prefix . 'mjschool_class';
	// Sanitize array of class IDs
	if ( ! is_array( $class_id ) ) {
		return array();
	}
	$class_id = array_map( 'absint', $class_id );
	if ( empty( $class_id ) ) {
		return array();
	}
	$placeholders = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name WHERE class_id IN ($placeholders) OR creater_id = %d", array_merge( $class_id, array( $user_id ) ) )
	);
	
	return $retrieve_data;
}
/**
 * Retrieves all classes created by a specific user.
 *
 * @param int $id User ID.
 * @return array Class list.
 * @since 1.0.0
 */
function mjschool_get_all_class_created_by( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$user_id    = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE create_by=%d", $user_id ) );
	return $retrieve_data;
}
/**
 * Retrieves all exams for given class IDs (section = 0).
 *
 * @param array $class_id Class IDs.
 * @return array Exam list.
 * @since 1.0.0
 */
function mjschool_get_all_exam_by_class_id_array( $class_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	// Sanitize array of class IDs.
	if ( ! is_array( $class_id ) ) {
		return array();
	}
	$class_id = array_map( 'absint', $class_id );
	if ( empty( $class_id ) ) {
		return array();
	}
	$placeholders = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name WHERE class_id IN ($placeholders) AND section_id = %d", array_merge( $class_id, array( 0 ) ) )
	);
	return $retrieve_data;
}
/**
 * Retrieves exams for class + section combination.
 *
 * @param int $class_id Class ID.
 * @param int $section_id Section ID.
 * @return array Exam list.
 * @since 1.0.0
 */
function mjschool_get_all_exam_by_class_id_and_section_id_array( $class_id, $section_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	$class_id   = absint( $class_id );
	$section_id = absint( $section_id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND (section_id = %d OR section_id = 0)", $class_id, $section_id )
	);
	return $result;
}
/**
 * Converts date into WP date format.
 *
 * @param string $date Raw date.
 * @return string Formatted date.
 * @since 1.0.0
 */
function mjschool_change_dateformat( $date ) {
	$date = sanitize_text_field( $date );
	return mysql2date( get_option( 'date_format' ), $date );
}
/**
 * Retrieves exams based on class & section arrays (parent).
 *
 * @param array $class_id Class IDs.
 * @param array $section_id Section IDs.
 * @return array Exam list.
 * @since 1.0.0
 */
function mjschool_get_all_exam_by_class_id_and_section_id_array_parent( $class_id, $section_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	// Sanitize arrays.
	if ( ! is_array( $class_id ) || ! is_array( $section_id ) ) {
		return array();
	}
	$class_id   = array_map( 'absint', $class_id );
	$section_id = array_map( 'absint', $section_id );
	if ( empty( $class_id ) || empty( $section_id ) ) {
		return array();
	}
	$class_placeholders   = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
	$section_placeholders = implode( ', ', array_fill( 0, count( $section_id ), '%d' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_data = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM $table_name WHERE class_id IN ($class_placeholders) AND section_id IN ($section_placeholders)", array_merge( $class_id, $section_id ) )
	);
	return $retrieve_data;
}
/**
 * Retrieves exam name using exam ID.
 *
 * @param int $id Exam ID.
 * @return string|null Exam name.
 * @since 1.0.0
 */
function mjschool_get_exam_name_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_exam';
	$eid        = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_var( $wpdb->prepare( "SELECT exam_name FROM $table_name WHERE exam_id = %d", $eid ) );
	return $retrieve_subject;
}
/**
 * Retrieves transport details by ID.
 *
 * @param int $id Transport ID.
 * @return object|null Transport record.
 * @since 1.0.0
 */
function mjschool_get_transport_by_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_transport';
	$tid        = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE transport_id = %d", $tid ) );
	return $retrieve_subject;
}
/**
 * Retrieves hall details by ID.
 *
 * @param int $hall_id Hall ID.
 * @return object|null Hall record.
 * @since 1.0.0
 */
function mjschool_get_hall_by_id( $hall_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_hall';
	$id         = absint( $hall_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE hall_id = %d", $id ) );
	return $retrieve_subject;
}
/**
 * Retrieves holiday details by ID.
 *
 * @param int $haliday_id Holiday ID.
 * @return object|null Holiday record.
 * @since 1.0.0
 */
function mjschool_get_holiday_by_id( $haliday_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_holiday';
	$id         = absint( $haliday_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE holiday_id = %d", $id ) );
	return $retrieve_subject;
}
/**
 * Retrieves route details by ID.
 *
 * @param int $route_id Route ID.
 * @return object|null Route record.
 * @since 1.0.0
 */
function mjschool_get_route_by_id( $route_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_time_table';
	$id         = absint( $route_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE route_id = %d", $id ) );
	return $retrieve_subject;
}

/**
 * Retrieves payment record by ID.
 *
 * @param int $payment_id Payment ID.
 * @return object|null Payment record.
 * @since 1.0.0
 */
function mjschool_get_payment_by_id( $payment_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_payment';
	$id         = absint( $payment_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE payment_id = %d", $id ) );
	return $retrieve_subject;
}
/**
 * Deletes a payment record.
 *
 * @param string $mjschool_table_name Table name.
 * @param int $id Payment ID.
 * @return int Rows affected.
 * @since 1.0.0
 */
function mjschool_delete_payment( $mjschool_table_name, $id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Payment Deleted', 'mjschool' ), null, get_current_user_id(), 'delete', $current_page );
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$tid                 = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE payment_id = %d", $tid ) );
	return $result;
}
/**
 * Deletes transport data.
 *
 * @param string $mjschool_table_name Table name.
 * @param int $id Transport ID.
 * @return int Rows affected.
 * @since 1.0.0
 */
function mjschool_delete_transport( $mjschool_table_name, $id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Transport Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$tid                 = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE transport_id = %d", $tid ) );
	return $result;
}
/**
 * Deletes exam hall.
 *
 * @param string $mjschool_table_name Table name.
 * @param int $hall_id Hall ID.
 * @return int Rows affected.
 * @since 1.0.0
 */
function mjschool_delete_hall( $mjschool_table_name, $hall_id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Exam Hall Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$id                  = absint( $hall_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE hall_id = %d", $id ) );
	
	return $result;
}
/**
 * Deletes a holiday record.
 *
 * @param string $mjschool_table_name Table name.
 * @param int $holiday_id Holiday ID.
 * @return int Rows affected.
 * @since 1.0.0
 */
function mjschool_delete_holiday( $mjschool_table_name, $holiday_id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Holiday Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	global $wpdb;
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$id                  = absint( $holiday_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE holiday_id = %d", $id ) );
	return $result;
}
/**
 * Deletes a route and associated Zoom meeting (if exists).
 *
 * @param string $mjschool_table_name Table name.
 * @param int $route_id Route ID.
 * @return int Rows affected.
 * @since 1.0.0
 */
function mjschool_delete_route( $mjschool_table_name, $route_id ) {
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'Route Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
	global $wpdb;
	$obj_virtual_classroom = new mjschool_virtual_classroom();
	// Sanitize table name
	$mjschool_table_name = sanitize_key( $mjschool_table_name );
	$table_name          = $wpdb->prefix . $mjschool_table_name;
	$id                  = absint( $route_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE route_id = %d", $id ) );
	
	if ( $result ) {
		$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $id );
		if ( ! empty( $meeting_data ) && isset( $meeting_data->meeting_id ) ) {
			$obj_virtual_classroom->mjschool_delete_meeting_in_zoom( $meeting_data->meeting_id );
		}
	}
	return $result;
}
/**
 * Retrieves teachers assigned to a specific subject.
 *
 * @param int $subject_id Subject ID.
 * @return array Teacher IDs.
 * @since 1.0.0
 */
function mjschool_get_teacher_id_by_subject_id( $subject_id ) {
	global $wpdb;
	$teacher    = array();
	$table_name = $wpdb->prefix . 'mjschool_teacher_subject';
	$id         = absint( $subject_id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT teacher_id FROM $table_name WHERE subject_id = %d", $id ) );
	
	if ( ! empty( $retrieve_subject ) ) {
		foreach ( $retrieve_subject as $subject ) {
			if ( isset( $subject->teacher_id ) ) {
				$teacher[] = absint( $subject->teacher_id );
			}
		}
	}
	
	return $teacher;
}

/**
 * Retrieves classes assigned to a teacher.
 *
 * @param int $teacher_id Teacher ID.
 * @return string Comma-separated class IDs.
 * @since 1.0.0
 */
function mjschool_get_teachers_class( $teacher_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'mjschool_teacher_class';
	$id    = absint( $teacher_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE teacher_id = %d", $id ) );
	$return_r = array();
	if ( ! empty( $result ) ) {
		foreach ( $result as $retrive_data ) {
			if ( isset( $retrive_data->class_id ) ) {
				$return_r[] = absint( $retrive_data->class_id );
			}
		}
	}
	if ( ! empty( $return_r ) ) {
		$class_idlist = implode( ',', $return_r );
	} else {
		$class_idlist = '0';
	}
	return $class_idlist;
}
/**
 * Retrieves class list for user based on role access.
 *
 * @param int $user_id Optional user ID.
 * @return array Class data.
 * @since 1.0.0
 */
function mjschool_get_all_class( $user_id = 0 ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	if ( $user_id === 0 ) {
		$user_id = get_current_user_id();
	}
	$user_id = absint( $user_id );
	
	if ( is_user_logged_in() ) {
		$page_1 = 'class';
		$data   = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
		
		if ( ( isset( $data['own_data'] ) && $data['own_data'] === '1' ) && mjschool_get_roles( $user_id ) === 'teacher' ) {
			$class_id = get_user_meta( $user_id, 'class_name', true );
			
			// Ensure $class_id is an array
			if ( is_array( $class_id ) ) {
				// Sanitize array values
				$class_id = array_map( 'absint', $class_id );
				if ( empty( $class_id ) ) {
					return array();
				}
				// Use prepare with placeholders
				$placeholders = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$classdata = $wpdb->get_results(
					$wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id IN ({$placeholders})", $class_id ), ARRAY_A
				);	
				return $classdata;
			} else {
				return array();
			}
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
			$classdata = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
			
			return $classdata;
		}
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
		$classdata = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
		
		return $classdata;
	}
}
/**
 * Retrieves role(s) of a specific user.
 *
 * @param int $user_id User ID.
 * @return array Role list.
 * @since 1.0.0
 */
function mjschool_get_role( $user_id ) {
	$user_id   = absint( $user_id );
	$user_meta = get_userdata( $user_id );
	if ( ! $user_meta ) {
		return array();
	}
	return isset( $user_meta->roles ) ? $user_meta->roles : array();
}
/**
 * Checks attendance holiday status by date.
 *
 * @param string $AttDate Date to check.
 * @return array Holiday records.
 * @since 1.0.0
 */
function mjschool_get_attendace_status( $AttDate ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_holiday';
	// Sanitize date input.
	$AttDate = sanitize_text_field( $AttDate );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE %s BETWEEN date AND end_date", $AttDate )
	);
	return $result;
}

/**
 * Checks whether user has read specific type/status.
 *
 * @param int $user_id User ID.
 * @param string $type Type name.
 * @param int $type_id Type ID.
 * @return string Read/Unread status.
 * @since 1.0.0
 */
function mjschool_check_type_status( $user_id, $type, $type_id ) {
	global $wpdb;
	$tbl_mjschool_check_status = $wpdb->prefix . 'mjschool_check_status';
	
	// Sanitize all inputs
	$user_id = absint( $user_id );
	$type    = sanitize_key( $type );
	$type_id = absint( $type_id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$rowcount = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$tbl_mjschool_check_status} WHERE user_id = %d AND type = %s AND type_id = %d", $user_id, $type, $type_id
		)
	);
	if ( absint( $rowcount ) === 0 ) {
		$status = 'Unread';
	} else {
		$status = 'Read';
	}
	return $status;
}
/**
 * Retrieves all payments made by a student.
 *
 * @param int $id Student ID.
 * @return array Payment list.
 * @since 1.0.0
 */
function mjschool_get_student_payment_list( $id ) {
	global $wpdb;
	$table_payment = $wpdb->prefix . 'mjschool_payment';
	$std_id        = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_payment} WHERE student_id = %d", $std_id )
	);
	return $result;
}

/**
 * Retrieves all class records assigned to a teacher.
 *
 * @param int $id Teacher ID.
 * @return array Class list.
 * @since 1.0.0
 */
function mjschool_get_all_teacher_data( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_teacher_class';
	$teacher_id = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE teacher_id = %d", $teacher_id )
	);
	return $result;
}

/**
 * Retrieves all users based on role.
 *
 * @param string $role User role.
 * @return array User list.
 * @since 1.0.0
 */
function mjschool_get_users_data( $role ) {
	$role               = sanitize_key( $role );
	$users_of_this_role = get_users( array( 'role' => $role ) );
	
	return $users_of_this_role;
}

/**
 * Retrieves user data of current user filtered by specific role.
 *
 * @param string $role Role to filter.
 * @return array Filtered user list.
 * @since 1.0.0
 */
function mjschool_get_own_users_data( $role ) {
	$get_current_user_id = absint( get_current_user_id() );
	$role                = sanitize_key( $role );
	
	global $wpdb;
	$capabilities = $wpdb->prefix . 'capabilities';
	
	// Use LIKE with proper escaping instead of RLIKE
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$users_of_this_role = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->users} WHERE ID = ANY ( SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value LIKE %s AND user_id = %d )", $capabilities, '%' . $wpdb->esc_like( $role ) . '%', $get_current_user_id
		)
	);
	if ( ! empty( $users_of_this_role ) ) {
		return $users_of_this_role;
	}
	return array();
}

/**
 * Retrieves all users assigned to a specific role.
 *
 * @since 1.0.0
 *
 * @param string $role User role slug.
 *
 * @return array List of WP_User objects.
 */
function mjschool_get_users_by_role( $role ) {
	$role = sanitize_key( $role );
	return get_users( array( 'role' => $role ) );
}

/**
 * Retrieves a list of students grouped by their class name.
 *
 * @since 1.0.0
 *
 * @return array Nested array of students grouped by class.
 */
function mjschool_get_student_group_by_class() {
	global $wpdb;
	$user_id    = absint( get_current_user_id() );
	$role_name  = mjschool_get_user_role( $user_id );
	$school_obj = new MJSchool_Management( $user_id );
	
	if ( $role_name === 'teacher' ) {
		$class_id     = get_user_meta( $user_id, 'class_name', true );
		$student_list = $school_obj->mjschool_get_teacher_student_list( $class_id );
	} else {
		$student_list = mjschool_get_all_student_list( 'student' );
	}
	
	$students = array();
	if ( ! empty( $student_list ) ) {
		foreach ( $student_list as $student_obj ) {
			if ( ! isset( $student_obj->ID ) ) {
				continue;
			}
			
			$student_id   = absint( $student_obj->ID );
			$class_id     = get_user_meta( $student_id, 'class_name', true );
			$student      = mjschool_get_user_name_by_id( $student_id );
			$student_name = str_replace( "'", '', $student );
			$roll_id      = get_user_meta( $student_id, 'roll_id', true );
			
			if ( $class_id !== '' ) {
				$classname                     = mjschool_get_class_name( $class_id );
				$students[ $classname ][ $student_id ] = $student_name . '( ' . esc_html( $roll_id ) . ' )';
			}
		}
	}
	
	return $students;
}

/**
 * Retrieves the stored avatar/image of a user.
 *
 * @since 1.0.0
 *
 * @param int $uid User ID.
 *
 * @return string|false Image filename or false if not found.
 */
function mjschool_get_user_image( $uid ) {
	$uid       = absint( $uid );
	$usersdata = get_user_meta( $uid, 'mjschool_user_avatar', true );
	
	return $usersdata;
}

/**
 * Fetches driver image from the transport table based on transport ID.
 *
 * @since 1.0.0
 *
 * @param int $tid Transport ID.
 *
 * @return array|null Driver image data or null if not found.
 */
function mjschool_get_user_driver_image( $tid ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_transport';
	$tid        = absint( $tid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$usersdata = $wpdb->get_results(
		$wpdb->prepare( "SELECT smgt_user_avatar FROM {$table_name} WHERE transport_id = %d", $tid ), ARRAY_A
	);
	if ( ! empty( $usersdata ) ) {
		return $usersdata[0];
	}
	return null;
}
/**
 * Creates a new WordPress user with additional metadata and triggers related email notifications.
 *
 * @since 1.0.0
 *
 * @param array  $userdata     User core fields.
 * @param array  $usermetadata Custom user meta values.
 * @param string $firstname    First name.
 * @param string $middlename   Middle name.
 * @param string $lastname     Last name.
 * @param string $role         Primary role for the user.
 *
 * @return int|WP_Error Created user ID or WP_Error on failure.
 */
function mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role ) {
	$Schoolname = get_option( 'mjschool_name' );
	$MailSub    = get_option( 'mjschoool_student_assign_to_teacher_subject' );
	$MailCon    = get_option( 'mjschool_student_assign_to_teacher_content' );
	$user_id = wp_insert_user( $userdata );
	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}
	$user = new WP_User( $user_id );
	// Set the primary role (only if it's not already assigned).
	if ( ! in_array( $role, $user->roles, true ) ) {
		$user->set_role( $role );
	}
	if ( in_array( $role, array( 'student', 'parent', 'student_temp' ), true ) ) {
		if ( ! in_array( 'subscriber', $user->roles, true ) ) {
			$user->add_role( 'subscriber' );
		}
	} elseif ( in_array( $role, array( 'teacher', 'supportstaff' ), true ) ) {
		if ( ! in_array( 'author', $user->roles, true ) ) {
			$user->add_role( 'author' );
		}
	}
	$user_name = isset( $userdata['display_name'] ) ? $userdata['display_name'] : '';
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	mjschool_append_audit_log( esc_html__( 'User Added', 'mjschool' ) . '( ' . esc_html( $user_name ) . ' )', $user_id, get_current_user_id(), 'insert', $current_page );
	foreach ( $usermetadata as $key => $val ) {
		add_user_meta( $user_id, $key, $val, true );
	}
	if ( $user_id ) {
		$string                    = array();
		$string['{{user_name}}']   = $firstname . ' ' . $middlename . ' ' . $lastname;
		$string['{{school_name}}'] = get_option( 'mjschool_name' );
		$string['{{role}}']        = $role;
		$string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
		$string['{{username}}']    = isset( $userdata['user_email'] ) ? $userdata['user_email'] : '';
		$string['{{Password}}']    = isset( $userdata['user_pass'] ) ? $userdata['user_pass'] : '';
		
		$MsgContent = get_option( 'mjschool_add_user_mail_content' );
		$MsgSubject = get_option( 'mjschool_add_user_mail_subject' );
		$message    = mjschool_string_replacement( $string, $MsgContent );
		$MsgSubject = mjschool_string_replacement( $string, $MsgSubject );
		$email      = isset( $userdata['user_email'] ) ? $userdata['user_email'] : '';
		
		mjschool_send_mail( $email, $MsgSubject, $message );
		
		// Send mail when student assigned to teacher
		if ( $role === 'student' && isset( $usermetadata['class_name'] ) ) {
			$TeacherIDs = mjschool_check_class_exits_in_teacher_class( $usermetadata['class_name'] );
			
			$string['{{school_name}}']  = $Schoolname;
			$string['{{student_name}}'] = mjschool_get_display_name( $user_id );
			$subject                    = get_option( 'mjschool_student_assign_teacher_mail_subject' );
			$MessageContent             = get_option( 'mjschool_student_assign_teacher_mail_content' );
			if ( ! empty( $TeacherIDs ) ) {
				foreach ( $TeacherIDs as $teacher ) {
					$TeacherData = get_userdata( absint( $teacher ) );
					if ( $TeacherData ) {
						$string['{{teacher_name}}'] = mjschool_get_display_name( $TeacherData->ID );
						$message                    = mjschool_string_replacement( $string, $MessageContent );
						mjschool_send_mail( $TeacherData->user_email, $subject, $message );
					}
				}
			}
		}
	}
	
	update_user_meta( $user_id, 'first_name', $firstname );
	update_user_meta( $user_id, 'last_name', $lastname );
	
	if ( $role === 'parent' ) {
		$child_list = isset( $_REQUEST['chield_list'] ) && is_array( $_REQUEST['chield_list'] ) ? array_map( 'absint', $_REQUEST['chield_list'] ) : array();
		
		if ( ! empty( $child_list ) ) {
			foreach ( $child_list as $child_id ) {
				$child_id     = absint( $child_id );
				$student_data = get_user_meta( $child_id, 'parent_id', true );
				$parent_data  = get_user_meta( $user_id, 'child', true );
				
				if ( $student_data && is_array( $student_data ) ) {
					if ( ! in_array( $user_id, $student_data, true ) ) {
						$student_data[] = $user_id;
						update_user_meta( $child_id, 'parent_id', $student_data );
					}
				} else {
					update_user_meta( $child_id, 'parent_id', array( $user_id ) );
				}
				
				if ( $parent_data && is_array( $parent_data ) ) {
					if ( ! in_array( $child_id, $parent_data, true ) ) {
						$parent_data[] = $child_id;
						update_user_meta( $user_id, 'child', $parent_data );
					}
				} else {
					add_user_meta( $user_id, 'child', array( $child_id ) );
				}
			}
		}
	}
	
	if ( $role === 'teacher' && isset( $usermetadata['class_name'] ) ) {
		$Schoolname = get_option( 'mjschool_name' );
		$MailSub    = get_option( 'mjschoool_student_assign_to_teacher_subject' );
		$MailCon    = get_option( 'mjschool_student_assign_to_teacher_content' );
		if ( ! empty( $usermetadata['class_name'] ) ) {
			$std          = mjschool_get_student_by_class_id( $usermetadata['class_name'] );
			$student_name = '';
			if ( ! empty( $std ) ) {
				foreach ( $std as $student ) {
					if ( isset( $student->ID ) && isset( $student->user_email ) && 
					     isset( $userdata['user_email'] ) && 
					     $userdata['user_email'] === $student->user_email ) {
						
						$student_name                = mjschool_get_display_name( $student->ID );
						$MailArr['{{school_name}}']  = $Schoolname;
						$MailArr['{{teacher_name}}'] = mjschool_get_display_name( $user_id );
						$MailArr['{{class_name}}']   = mjschool_get_class_name( get_user_meta( $student->ID, 'class_name', true ) );
						$MailArr['{{student_name}}'] = $student_name;
						$MailSub                     = mjschool_string_replacement( $MailArr, $MailSub );
						$MailCon                     = mjschool_string_replacement( $MailArr, $MailCon );
						
						mjschool_send_mail( $student->user_email, $MailSub, $MailCon );
					}
				}
			}
		}
	}
	
	return $user_id;
}

/**
 * Handles file upload for a single document and stores it in the school assets directory.
 *
 * @since 1.0.0
 *
 * @param string $file File index key.
 * @param string $type File type key.
 * @param string $nm   Custom name part for file.
 *
 * @return string|false Uploaded filename or false on failure.
 */
function mjschool_load_documets( $file, $type, $nm ) {
	// Validate file exists and no upload error
	if ( ! isset( $_FILES[ $type ] ) || $_FILES[ $type ]['error'] !== UPLOAD_ERR_OK ) {
		return false;
	}
	
	// Sanitize all file data
	$file_name = isset( $_FILES[ $type ]['name'] ) ? sanitize_file_name( $_FILES[ $type ]['name'] ) : '';
	$file_tmp  = isset( $_FILES[ $type ]['tmp_name'] ) ? $_FILES[ $type ]['tmp_name'] : '';
	$file_size = isset( $_FILES[ $type ]['size'] ) ? absint( $_FILES[ $type ]['size'] ) : 0;
	
	// Validate file type
	$check_document = mjschool_wp_check_file_type_and_ext( $file_tmp, $file_name );
	if ( ! $check_document ) {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
	
	// Get file info securely
	$file_info = wp_check_filetype( $file_name );
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		wp_die( esc_html__( 'Invalid file type.', 'mjschool' ) );
	}
	
	// Sanitize custom name
	$nm = sanitize_file_name( $nm );
	
	// Generate secure filename
	$inventoryimagename = 'mjschool_' . time() . '-' . $nm . '-in.' . $file_info['ext'];
	
	// Validate file size (5MB max)
	$max_size = 5 * 1024 * 1024;
	if ( $file_size > $max_size ) {
		wp_die( esc_html__( 'File size exceeds maximum allowed (5MB).', 'mjschool' ) );
	}
	
	// Set secure upload path
	$document_dir = WP_CONTENT_DIR . '/uploads/school_assets/';
	if ( ! file_exists( $document_dir ) ) {
		wp_mkdir_p( $document_dir );
	}
	
	// Move file securely
	if ( is_uploaded_file( $file_tmp ) ) {
		$upload_path = $document_dir . $inventoryimagename;
		if ( move_uploaded_file( $file_tmp, $upload_path ) ) {
			// Set proper file permissions
			chmod( $upload_path, 0644 );
			return $inventoryimagename;
		}
	}
	
	return false;
}

/**
 * Uploads a single document using direct file array input.
 *
 * @since 1.0.0
 *
 * @param string $file Not used.
 * @param array  $type Uploaded file array (name, tmp_name).
 * @param string $nm   Custom name identifier.
 *
 * @return string|false Uploaded filename or false on failure.
 */
function mjschool_load_documets_new( $file, $type, $nm ) {
	// Validate array structure
	if ( ! is_array( $type ) || ! isset( $type['tmp_name'] ) || ! isset( $type['name'] ) ) {
		return false;
	}
	
	// Validate upload error
	if ( isset( $type['error'] ) && $type['error'] !== UPLOAD_ERR_OK ) {
		return false;
	}
	
	// Sanitize file data
	$file_name = sanitize_file_name( $type['name'] );
	$file_tmp  = $type['tmp_name'];
	$file_size = isset( $type['size'] ) ? absint( $type['size'] ) : 0;
	
	// Validate file type
	$check_document = mjschool_wp_check_file_type_and_ext( $file_tmp, $file_name );
	if ( ! $check_document ) {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
	
	// Get file info
	$file_info = wp_check_filetype( $file_name );
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		wp_die( esc_html__( 'Invalid file type.', 'mjschool' ) );
	}
	
	// Sanitize custom name
	$nm = sanitize_file_name( $nm );
	
	// Generate secure filename
	$inventoryimagename = 'mjschool_' . time() . '-' . $nm . '-in.' . $file_info['ext'];
	
	// Validate file size (5MB max)
	$max_size = 5 * 1024 * 1024;
	if ( $file_size > $max_size ) {
		wp_die( esc_html__( 'File size exceeds maximum allowed (5MB).', 'mjschool' ) );
	}
	
	// Set upload path
	$document_dir = WP_CONTENT_DIR . '/uploads/school_assets/';
	if ( ! file_exists( $document_dir ) ) {
		wp_mkdir_p( $document_dir );
	}
	
	// Move file
	if ( is_uploaded_file( $file_tmp ) ) {
		$upload_path = $document_dir . $inventoryimagename;
		if ( move_uploaded_file( $file_tmp, $upload_path ) ) {
			chmod( $upload_path, 0644 );
			return $inventoryimagename;
		}
	}
	
	return false;
}

/**
 * Uploads multiple documents and generates a random filename.
 *
 * @since 1.0.0
 *
 * @param string $file Not used.
 * @param array  $type Uploaded file array.
 * @param string $nm   Custom identifier.
 *
 * @return string|false Uploaded file name or false on failure.
 */
function mjschool_load_multiple_documets( $file, $type, $nm ) {
	// Validate array structure
	if ( ! is_array( $type ) || ! isset( $type['tmp_name'] ) || ! isset( $type['name'] ) ) {
		return false;
	}
	
	// Validate upload error
	if ( isset( $type['error'] ) && $type['error'] !== UPLOAD_ERR_OK ) {
		return false;
	}
	
	// Sanitize file data
	$file_name = sanitize_file_name( $type['name'] );
	$file_tmp  = $type['tmp_name'];
	$file_size = isset( $type['size'] ) ? absint( $type['size'] ) : 0;
	
	// Validate file type
	$check_document = mjschool_wp_check_file_type_and_ext( $file_tmp, $file_name );
	if ( ! $check_document ) {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
	
	// Get file info
	$file_info = wp_check_filetype( $file_name );
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		wp_die( esc_html__( 'Invalid file type.', 'mjschool' ) );
	}
	
	// Generate secure filename with random component
	$inventoryimagename = 'mjschool_' . time() . '-' . wp_rand( 1000, 9999 ) . '.' . $file_info['ext'];
	
	// Validate file size (5MB max)
	$max_size = 5 * 1024 * 1024;
	if ( $file_size > $max_size ) {
		wp_die( esc_html__( 'File size exceeds maximum allowed (5MB).', 'mjschool' ) );
	}
	
	// Set upload path
	$document_dir = WP_CONTENT_DIR . '/uploads/school_assets/';
	if ( ! file_exists( $document_dir ) ) {
		wp_mkdir_p( $document_dir );
	}
	
	// Move file
	if ( is_uploaded_file( $file_tmp ) ) {
		$upload_path = $document_dir . $inventoryimagename;
		if ( move_uploaded_file( $file_tmp, $upload_path ) ) {
			chmod( $upload_path, 0644 );
			return $inventoryimagename;
		}
	}
	
	return false;
}

/**
 * Updates WordPress user fields and metadata.
 *
 * @since 1.0.0
 *
 * @param array $userdata     User data fields.
 * @param array $usermetadata Meta fields to update.
 *
 * @return bool|int User ID on success, false on failure.
 */
function mjschool_update_user_profile( $userdata, $usermetadata ) {
	$user_id = wp_update_user( $userdata );
	
	if ( is_wp_error( $user_id ) ) {
		return false;
	}
	
	foreach ( $usermetadata as $key => $val ) {
		update_user_meta( $user_id, $key, $val );
	}
	
	return $user_id;
}

/**
 * Returns a readable label for a leave duration type.
 *
 * @since 1.0.0
 *
 * @param string $id Leave type ID.
 *
 * @return string Human-readable leave label.
 */
function mjschool_leave_duration_label( $id ) {
	$id = sanitize_key( $id );
	
	$labels = array(
		'half_day'      => 'Half Day',
		'full_day'      => 'Full Day',
		'more_then_day' => 'More Then One Day',
	);
	
	return isset( $labels[ $id ] ) ? $labels[ $id ] : '';
}

/**
 * Retrieves all plugin-specific users (student, teacher, support staff, parent).
 *
 * @since 1.0.0
 *
 * @return array List of WP_User objects.
 */
function mjschool_get_all_user_in_plugin() {
	$student      = get_users( array( 'role' => 'student' ) );
	$teacher      = get_users( array( 'role' => 'teacher' ) );
	$supportstaff = get_users( array( 'role' => 'supportstaff' ) );
	$parent       = get_users( array( 'role' => 'parent' ) );
	
	return array_merge( $student, $teacher, $supportstaff, $parent );
}

/**
 * Updates an existing WordPress user with role, metadata, and validation checks.
 *
 * @since 1.0.0
 *
 * @param array  $userdata     User core data.
 * @param array  $usermetadata Additional user meta fields.
 * @param string $firstname    First name.
 * @param string $middlename   Middle name.
 * @param string $lastname     Last name.
 * @param string $role         User role.
 *
 * @return int Updated user ID.
 */
function mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role ) {
	// Ensure the user is logged in
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'Security check failed! You are not logged in.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
	if ( ! isset( $_POST['security'] ) || 
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_nonce' ) ) {
		wp_die( esc_html__( 'Security check failed! Invalid security token.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
	
	// Get current user ID and role
	$current_user_id = get_current_user_id();
	$current_role    = mjschool_get_user_role( $current_user_id );
	
	// Prevent unauthorized role changes
	$allowed_roles = array( 'administrator', 'management', 'supportstaff', 'teacher' );
	if ( ! in_array( $current_role, $allowed_roles, true ) ) {
		wp_die( esc_html__( 'Permission denied! You do not have the required access.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
	
	// Prevent non-admins from assigning the 'administrator' role
	if ( $role === 'administrator' && $current_role !== 'administrator' ) {
		wp_die( esc_html__( 'You are not allowed to assign the administrator role.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
	}
	
	// Validate user ID
	if ( ! isset( $userdata['ID'] ) || ! is_numeric( $userdata['ID'] ) ) {
		wp_die( esc_html__( 'Invalid user ID! Please check the input.', 'mjschool' ), 'Error', array( 'response' => 400 ) );
	}
	
	$user_id = wp_update_user( $userdata );
	
	if ( ! is_wp_error( $user_id ) && isset( $userdata['user_login'] ) ) {
		global $wpdb;
		$new_email = sanitize_email( $userdata['user_login'] );
		
		// phpcs:disable
		$wpdb->update(
			$wpdb->users,
			array( 'user_login' => $new_email ),
			array( 'ID' => $user_id ),
			array( '%s' ),
			array( '%d' )
		);
		// phpcs:enable
	}
	
	$users = new WP_User( $user_id );
	
	// Set the primary role
	if ( ! in_array( $role, $users->roles, true ) ) {
		$users->set_role( $role );
	}
	
	if ( in_array( $role, array( 'student', 'parent', 'student_temp' ), true ) ) {
		if ( ! in_array( 'subscriber', $users->roles, true ) ) {
			$users->add_role( 'subscriber' );
		}
	} elseif ( in_array( $role, array( 'teacher', 'supportstaff' ), true ) ) {
		if ( ! in_array( 'author', $users->roles, true ) ) {
			$users->add_role( 'author' );
		}
	}
	
	update_user_meta( $user_id, 'first_name', $firstname );
	update_user_meta( $user_id, 'last_name', $lastname );
	
	$user = isset( $userdata['display_name'] ) ? $userdata['display_name'] : '';
	
	// Sanitize $_REQUEST['page'] with isset() check
	$current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
	
	mjschool_append_audit_log(
		esc_html__( 'User updated', 'mjschool' ) . '( ' . esc_html( $user ) . ' )',
		$user_id,
		get_current_user_id(),
		'edit',
		$current_page
	);
	
	foreach ( $usermetadata as $key => $val ) {
		update_user_meta( $user_id, $key, $val );
	}
	
	if ( $role === 'parent' ) {
		$child_list = isset( $_REQUEST['chield_list'] ) && is_array( $_REQUEST['chield_list'] )
			? array_map( 'absint', $_REQUEST['chield_list'] )
			: array();
		
		$old_child = get_user_meta( $user_id, 'child', true );
		
		if ( ! empty( $old_child ) && is_array( $old_child ) ) {
			$different_insert_child = array_diff( $child_list, $old_child );
			$different_delete_child = array_diff( $old_child, $child_list );
			
			if ( ! empty( $different_insert_child ) ) {
				foreach ( $different_insert_child as $child ) {
					$child     = absint( $child );
					$parent    = get_user_meta( $child, 'parent_id', true );
					$old_child = get_user_meta( $user_id, 'child', true );
					
					if ( is_array( $old_child ) ) {
						$old_child[] = $child;
						update_user_meta( $user_id, 'child', $old_child );
					}
					
					if ( empty( $parent ) ) {
						update_user_meta( $child, 'parent_id', array( $user_id ) );
					} elseif ( is_array( $parent ) ) {
						$parent[] = $user_id;
						update_user_meta( $child, 'parent_id', $parent );
					}
				}
			}
			
			if ( ! empty( $different_delete_child ) ) {
				$child     = get_user_meta( $user_id, 'child', true );
				$childdata = is_array( $child ) ? array_diff( $child, $different_delete_child ) : array();
				update_user_meta( $user_id, 'child', $childdata );
				
				foreach ( $different_delete_child as $del_child ) {
					$del_child = absint( $del_child );
					$parent    = get_user_meta( $del_child, 'parent_id', true );
					
					if ( ! empty( $parent ) && is_array( $parent ) ) {
						$key = array_search( $user_id, $parent, true );
						if ( $key !== false ) {
							unset( $parent[ $key ] );
							update_user_meta( $del_child, 'parent_id', $parent );
						}
					}
				}
			}
		} elseif ( ! empty( $child_list ) ) {
			foreach ( $child_list as $child_id ) {
				$child_id     = absint( $child_id );
				$student_data = get_user_meta( $child_id, 'parent_id', true );
				$parent_data  = get_user_meta( $user_id, 'child', true );
				
				if ( $student_data && is_array( $student_data ) ) {
					if ( ! in_array( $user_id, $student_data, true ) ) {
						$student_data[] = $user_id;
						update_user_meta( $child_id, 'parent_id', $student_data );
					}
				} else {
					update_user_meta( $child_id, 'parent_id', array( $user_id ) );
				}
				
				if ( $parent_data && is_array( $parent_data ) ) {
					if ( ! in_array( $child_id, $parent_data, true ) ) {
						$parent_data[] = $child_id;
						update_user_meta( $user_id, 'child', $parent_data );
					}
				} else {
					update_user_meta( $user_id, 'child', array( $child_id ) );
				}
			}
		}
	}
	
	return $user_id;
}
/**
 * Returns a list of weekdays with translated labels.
 *
 * @since 1.0.0
 *
 * @return array Weekday list with keys 1–7.
 */
function mjschool_day_list() {
	$day_list = array(
		'1' => esc_attr__( 'Monday', 'mjschool' ),
		'2' => esc_attr__( 'Tuesday', 'mjschool' ),
		'3' => esc_attr__( 'Wednesday', 'mjschool' ),
		'4' => esc_attr__( 'Thursday', 'mjschool' ),
		'5' => esc_attr__( 'Friday', 'mjschool' ),
		'6' => esc_attr__( 'Saturday', 'mjschool' ),
		'7' => esc_attr__( 'Sunday', 'mjschool' ),
	);
	return $day_list;
}
/**
 * Returns a non-translated weekday list.
 *
 * @since 1.0.0
 *
 * @return array Weekday names indexed 1–7.
 */
function mjschool_day_list_callback() {
	$day_list = array(
		'1' => 'Monday',
		'2' => 'Tuesday',
		'3' => 'Wednesday',
		'4' => 'Thursday',
		'5' => 'Friday',
		'6' => 'Saturday',
		'7' => 'Sunday',
	);
	
	return $day_list;
}
/**
 * Retrieves all exam entries from the database.
 *
 * @since 1.0.0
 *
 * @return array List of exam objects.
 */
function mjschool_get_exam_list() {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_exam';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
	$exam = $wpdb->get_results( "SELECT * FROM {$tbl_name}" );
	return $exam;
}
/**
 * Retrieves a single exam record (likely the first entry).
 *
 * @since 1.0.0
 *
 * @return object|null Exam record or null.
 */
function mjschool_get_exam_id() {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_exam';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
	$exam = $wpdb->get_row( "SELECT * FROM {$tbl_name}" );
	return $exam;
}
/**
 * Retrieves a subject name and code by subject ID.
 *
 * @since 1.0.0
 *
 * @param int $sid Subject ID.
 *
 * @return string Subject name with code or 'N/A'.
 */
function mjschool_get_subject_by_id( $sid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_subject';
	$id       = absint( $sid );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE subid = %d", $id ) );
	
	if ( ! empty( $subject ) && isset( $subject->sub_name ) && isset( $subject->subject_code ) ) {
		return esc_html( $subject->sub_name ) . '-' . esc_html( $subject->subject_code );
	} else {
		return 'N/A';
	}
}
/**
 * Retrieves students belonging to a specific class.
 *
 * @since 1.0.0
 *
 * @param int|string $id Class ID.
 *
 * @return array List of WP_User students.
 */
function mjschool_get_student_by_class_id( $id ) {
	$id = sanitize_text_field( $id );
	$student = get_users(
		array(
			'meta_key'   => 'class_name',
			'meta_value' => $id,
		)
	);
	return $student;
}
/**
 * Checks if a roll number already exists in the same class.
 *
 * @since 1.0.0
 *
 * @param string $r_no       Roll number.
 * @param string $class_id   Class ID.
 * @param int    $student_id Student ID (ignore self during update).
 *
 * @return int 1 if available, 0 if duplicate.
 */
function mjschool_check_student_roll_no_exist_or_not( $r_no, $class_id, $student_id ) {
	$r_no       = sanitize_text_field( $r_no );
	$class_id   = sanitize_text_field( $class_id );
	$student_id = absint( $student_id );
	$student = get_users(
		array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => 'class_name',
					'value' => $class_id,
				),
				array(
					'key'   => 'roll_id',
					'value' => mjschool_strip_tags_and_stripslashes( $r_no ),
				),
			),
			'role'       => 'student',
		)
	);
	if ( ! empty( $student ) && isset( $student[0]->ID ) ) {
		if ( absint( $student[0]->ID ) === $student_id ) {
			$status = 1;
		} else {
			$status = 0;
		}
	} else {
		$status = 1;
	}
	
	return $status;
}
/**
 * Retrieves a list of students who failed based on exam marks and passing criteria.
 *
 * @since 1.0.0
 *
 * @param string $current_class Current class ID.
 * @param string $next_class    Next class ID.
 * @param int    $exam_id       Exam ID.
 * @param int    $passing_marks Minimum required marks.
 *
 * @return array List of failed student IDs.
 */
function mjschool_fail_student_list( $current_class, $next_class, $exam_id, $passing_marks ) {
	global $wpdb;
	
	$table_users      = $wpdb->prefix . 'users';
	$table_usermeta   = $wpdb->prefix . 'usermeta';
	$table_marks      = $wpdb->prefix . 'mjschool_marks';
	$capabilities_key = $wpdb->prefix . 'capabilities';
	
	// Sanitize inputs
	$current_class = sanitize_text_field( $current_class );
	$next_class    = sanitize_text_field( $next_class );
	$passing_marks = absint( $passing_marks );
	$exam_id       = absint( $exam_id );
	
	$exam_obj      = new mjschool_exam();
	$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
	$contributions = isset( $exam_data->contributions ) ? $exam_data->contributions : '';
	
	$failed_students = array();
	
	if ( $contributions === 'yes' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$student_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID, u.user_login, um.meta_value AS class_name, m.class_marks 
				FROM {$table_users} AS u 
				INNER JOIN {$table_usermeta} AS um ON u.ID = um.user_id 
				INNER JOIN {$table_usermeta} AS cap ON u.ID = cap.user_id 
				INNER JOIN {$table_marks} AS m ON u.ID = m.student_id 
				WHERE um.meta_key = 'class_name' 
				AND um.meta_value = %s 
				AND cap.meta_key = %s 
				AND cap.meta_value LIKE %s 
				AND m.exam_id = %d",
				$current_class,
				$capabilities_key,
				'%' . $wpdb->esc_like( 'student' ) . '%',
				$exam_id
			)
		);
		foreach ( $student_data as $student ) {
			if ( ! isset( $student->class_marks ) || ! isset( $student->ID ) ) {
				continue;
			}
			
			$marks = json_decode( $student->class_marks, true );
			
			if ( is_array( $marks ) && json_last_error() === JSON_ERROR_NONE ) {
				$total_marks = array_sum( $marks );
				
				if ( $total_marks < $passing_marks ) {
					$failed_students[] = absint( $student->ID );
				}
			}
		}
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$failed_students = $wpdb->get_col(
			$wpdb->prepare( "SELECT u.ID FROM {$table_users} AS u INNER JOIN {$table_usermeta} AS um ON u.ID = um.user_id INNER JOIN {$table_usermeta} AS cap ON u.ID = cap.user_id INNER JOIN {$table_marks} AS m ON u.ID = m.student_id WHERE um.meta_key = 'class_name' AND um.meta_value = %s AND cap.meta_key = %s AND cap.meta_value LIKE %s AND m.marks < %d AND m.exam_id = %d", $current_class, $capabilities_key, '%' . $wpdb->esc_like( 'student' ) . '%', $passing_marks, $exam_id )
		);
		// Sanitize IDs
		$failed_students = array_map( 'absint', $failed_students );
	}
	return $failed_students;
}
/**
 * Migrate students from current class to next class based on exam result.
 *
 * @since 1.0.0
 *
 * @param string $current_class Current class name.
 * @param string $next_class    Next class to migrate students into.
 * @param int    $exam_id       Exam ID used for determining pass/fail.
 * @param array  $fail_list     Array of failed student IDs.
 * @param int    $passing_marks Passing marks of the exam.
 */
function mjschool_migration( $current_class, $next_class, $exam_id, $fail_list, $passing_marks ) {
	global $wpdb;
	
	// Sanitize inputs
	$current_class = sanitize_text_field( $current_class );
	$next_class    = sanitize_text_field( $next_class );
	$exam_id       = absint( $exam_id );
	$passing_marks = absint( $passing_marks );
	$fail_list     = is_array( $fail_list ) ? array_map( 'absint', $fail_list ) : array();
	
	$exlude_id = mjschool_approve_student_list();
	
	$studentdata = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'class_name',
			'meta_value' => $current_class,
			'exclude'    => $exlude_id,
		)
	);
	
	$table_usermeta         = $wpdb->prefix . 'usermeta';
	$mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log';
	$ip_address             = sanitize_text_field( gethostbyname( gethostname() ) );
	
	if ( ! empty( $studentdata ) ) {
		$pass_students = array();
		$fail_students = array();
		
		foreach ( $studentdata as $retrieved_data ) {
			if ( ! isset( $retrieved_data->ID ) ) {
				continue;
			}
			
			$student_id = absint( $retrieved_data->ID );
			
			if ( ! in_array( $student_id, $fail_list, true ) ) {
				$result = $wpdb->update(
					$table_usermeta,
					array( 'meta_value' => $next_class ),
					array(
						'user_id'    => $student_id,
						'meta_value' => $current_class,
						'meta_key'   => 'class_name',
					),
					array( '%s' ),
					array( '%d', '%s', '%s' )
				);
				
				$pass_students[] = array(
					'student_id' => $student_id,
					'reason'     => 'Pass',
				);
			} else {
				$fail_students[] = array(
					'student_id' => $student_id,
					'reason'     => 'Failed',
				);
			}
		}
		
		$migration_log = array(
			'ip_address'     => $ip_address,
			'created_by'     => get_current_user_id(),
			'current_class'  => $current_class,
			'next_class'     => $next_class,
			'exam_name'      => $exam_id,
			'pass_mark'      => $passing_marks,
			'created_at'     => current_time( 'Y-m-d' ),
			'date_time'      => current_time( 'Y-m-d H:i:s' ),
			'deleted_status' => 0,
			'pass_students'  => wp_json_encode( $pass_students ),
			'fail_students'  => wp_json_encode( $fail_students ),
		);
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->insert( $mjschool_migration_log, $migration_log );
	}
}

/**
 * Migrate students from current class to next class without exam evaluation.
 *
 * @since 1.0.0
 *
 * @param string $current_class Current class name.
 * @param string $next_class    Target class name.
 */
function mjschool_migration_without_exam( $current_class, $next_class ) {
	global $wpdb;
	// Sanitize inputs.
	$current_class = sanitize_text_field( $current_class );
	$next_class    = sanitize_text_field( $next_class );
	$ip_address = sanitize_text_field( gethostbyname( gethostname() ) );
	$exlude_id  = mjschool_approve_student_list();
	$studentdata = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'class_name',
			'meta_value' => $current_class,
			'exclude'    => $exlude_id,
		)
	);
	$table_usermeta         = $wpdb->prefix . 'usermeta';
	$mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log';
	if ( ! empty( $studentdata ) ) {
		$pass_students = array();
		$fail_students = array();
		foreach ( $studentdata as $retrieved_data ) {
			if ( ! isset( $retrieved_data->ID ) ) {
				continue;
			}
			$student_id = absint( $retrieved_data->ID );
			$student = $wpdb->update(
				$table_usermeta,
				array( 'meta_value' => $next_class ),
				array(
					'user_id'    => $student_id,
					'meta_value' => $current_class,
					'meta_key'   => 'class_name',
				),
				array( '%s' ),
				array( '%d', '%s', '%s' )
			);
			if ( $student !== false ) {
				$pass_students[] = array(
					'student_id' => $student_id,
					'reason'     => 'Migrated without exam',
				);
			} else {
				$fail_students[] = array(
					'student_id' => $student_id,
					'reason'     => 'Migration failed',
				);
			}
		}
		$migration_log = array(
			'ip_address'     => $ip_address,
			'created_by'     => get_current_user_id(),
			'current_class'  => $current_class,
			'next_class'     => $next_class,
			'exam_name'      => '',
			'pass_mark'      => '',
			'created_at'     => current_time( 'Y-m-d' ),
			'date_time'      => current_time( 'Y-m-d H:i:s' ),
			'deleted_status' => 0,
			'pass_students'  => wp_json_encode( $pass_students ),
			'fail_students'  => wp_json_encode( $fail_students ),
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->insert( $mjschool_migration_log, $migration_log );
	}
}
/**
 * Get inbox messages for a specific user.
 *
 * @since 1.0.0
 *
 * @param int $id User ID.
 * @return array  Inbox messages.
 */
function mjschool_count_inbox_item( $id ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_message';
	$id       = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$inbox = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE receiver = %d", $id )
	);
	return $inbox;
}

/**
 * Count unread messages for a user from both main messages and replies.
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID.
 * @return int         Total unread messages.
 */
function mjschool_count_unread_message( $user_id ) {
	global $wpdb;
	$tbl_name                 = $wpdb->prefix . 'mjschool_message';
	$mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
	$user_id                  = absint( $user_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$inbox = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE receiver = %d AND sender != %d AND status = %d", $user_id, $user_id, 0 )
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$reply_msg = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$mjschool_message_replies} WHERE receiver_id = %d AND (status = %d OR status IS NULL)", $user_id, 0 )
	);
	$count_total_message = count( $inbox ) + count( $reply_msg );
	return $count_total_message;
}
/**
 * Count unread messages for the logged-in user by post/message ID.
 *
 * @since 1.0.0
 *
 * @param int $post_id Message post ID.
 * @return int         Number of unread messages.
 */
function mjschool_count_unread_message_current_user( $post_id ) {
	global $wpdb;
	$tbl_name_message      = $wpdb->prefix . 'mjschool_message';
	$wpcrm_message_replies = $wpdb->prefix . 'mjschool_message_replies';
	$post_id               = absint( $post_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$inbox = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name_message} WHERE post_id = %d AND status = %d", $post_id, 0 )
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$reply_msg = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$wpcrm_message_replies} WHERE message_id = %d AND (status = %d OR status IS NULL)", $post_id, 0 )
	);
	$count_total_message = count( $inbox ) + count( $reply_msg );
	return $count_total_message;
}
/**
 * Retrieve inbox messages with pagination.
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID.
 * @param int $p       Offset.
 * @param int $lpm1    Limit per page.
 * @return array        Inbox message list.
 */
function mjschool_get_inbox_message( $user_id, $p = 0, $lpm1 = 10 ) {
	global $wpdb;
	
	$tbl_name                 = $wpdb->prefix . 'mjschool_message';
	$tbl_name_message_replies = $wpdb->prefix . 'mjschool_message_replies';
	
	// Sanitize all inputs
	$user_id = absint( $user_id );
	$p       = absint( $p );
	$lpm1    = absint( $lpm1 );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$inbox = $wpdb->get_results(
		$wpdb->prepare( "SELECT DISTINCT b.message_id, a.* FROM {$tbl_name} a LEFT JOIN {$tbl_name_message_replies} b ON a.post_id = b.message_id WHERE (a.receiver = %d OR b.receiver_id = %d) AND (a.receiver = %d OR a.sender = %d) ORDER BY date DESC LIMIT %d, %d", $user_id, $user_id, $user_id, $user_id, $p, $lpm1 )
	);
	
	return $inbox;
}
/**
 * Retrieve sent messages by a user.
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID.
 * @param int $max     Max messages per page.
 * @param int $offset  Offset.
 * @return array        Sent messages.
 */
function mjschool_get_send_message( $user_id, $max = 10, $offset = 0 ) {
	$user_id = absint( $user_id );
	$max     = absint( $max );
	$offset  = absint( $offset );
	$args = array(
		'post_type'      => 'message',
		'posts_per_page' => $max,
		'offset'         => $offset,
		'post_status'    => 'publish',
		'author'         => $user_id,
	);
	$q            = new WP_Query();
	$sent_message = $q->query( $args );
	return $sent_message;
}
/**
 * Count total sent messages by user.
 *
 * @since 1.0.0
 *
 * @param int $id User ID.
 * @return int     Total sent messages.
 */
function mjschool_count_send_item( $id ) {
	global $wpdb;
	$posts = $wpdb->prefix . 'posts';
	$id    = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$total = $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$posts} WHERE post_type = %s AND post_author = %d", 'message', $id )
	);
	
	return absint( $total );
}
/**
 * Generate pagination HTML for message listing.
 *
 * @since 1.0.0
 *
 * @param int $totalposts Total pages.
 * @param int $p          Current page.
 * @param int $lpm1       Limit per page.
 * @param int $prev       Previous page link.
 * @param int $next       Next page link.
 * @return string         Pagination HTML.
 */
function mjschool_pagination( $totalposts, $p, $lpm1, $prev, $next ) {
	$totalposts = absint( $totalposts );
	$p          = absint( $p );
	$lpm1       = absint( $lpm1 );
	$prev       = absint( $prev );
	$next       = absint( $next );
	$pagination = '';
	$form_id    = 1;
	$page_order = '';
	if ( isset( $_REQUEST['form_id'] ) ) {
		$form_id = absint( $_REQUEST['form_id'] );
	}
	if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
		$orderby    = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		$order      = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		$page_order = '&orderby=' . esc_attr( $orderby ) . '&order=' . esc_attr( $order );
	}
	if ( $totalposts > 1 ) {
		$pagination .= '<div class="btn-group">';
		if ( $p > 1 ) {
			$url         = esc_url( add_query_arg( array(
				'page'    => 'mjschool_message',
				'tab'     => 'sentbox',
				'form_id' => $form_id,
				'pg'      => $prev,
			) ) );
			$pagination .= '<a href="' . $url . esc_attr( $page_order ) . '" class="btn btn-default"><i class="fa fa-angle-left"></i></a> ';
		} else {
			$pagination .= '<a class="btn btn-default disabled"><i class="fa fa-angle-left"></i></a> ';
		}
		if ( $p < $totalposts ) {
			$url         = esc_url( add_query_arg( array(
				'page'    => 'mjschool_message',
				'tab'     => 'sentbox',
				'form_id' => $form_id,
				'pg'      => $next,
			) ) );
			$pagination .= ' <a href="' . $url . '" class="btn btn-default next-page"><i class="fa fa-angle-right"></i></a>';
		} else {
			$pagination .= ' <a class="btn btn-default disabled"><i class="fa fa-angle-right"></i></a>';
		}
		$pagination .= "</div>\n";
	}
	return $pagination;
}
/**
 * Generate pagination HTML for frontend sentbox.
 *
 * @since 1.0.0
 *
 * @param int $totalposts Total pages.
 * @param int $p          Current page.
 * @param int $lpm1       Limit per page.
 * @param int $prev       Previous page link.
 * @param int $next       Next page link.
 * @return string         Pagination HTML.
 */
function mjschool_frontend_sentbox_pagination( $totalposts, $p, $lpm1, $prev, $next ) {
	$totalposts = absint( $totalposts );
	$p          = absint( $p );
	$lpm1       = absint( $lpm1 );
	$prev       = absint( $prev );
	$next       = absint( $next );
	$pagination = '';
	$form_id    = 1;
	$page_order = '';
	if ( isset( $_REQUEST['form_id'] ) ) {
		$form_id = absint( $_REQUEST['form_id'] );
	}
	if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
		$orderby    = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		$order      = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		$page_order = '&orderby=' . esc_attr( $orderby ) . '&order=' . esc_attr( $order );
	}
	if ( $totalposts > 1 ) {
		$pagination .= '<div class="btn-group">';
		if ( $p > 1 ) {
			$url         = esc_url( add_query_arg( array(
				'dashboard' => 'mjschool_user',
				'page'      => 'message',
				'tab'       => 'sentbox',
				'pg'        => $prev,
			) ) );
			$pagination .= '<a href="' . $url . esc_attr( $page_order ) . '" class="btn btn-default"><i class="fa fa-angle-left"></i></a> ';
		} else {
			$pagination .= '<a class="btn btn-default disabled"><i class="fa fa-angle-left"></i></a> ';
		}
		if ( $p < $totalposts ) {
			$url         = esc_url( add_query_arg( array(
				'dashboard' => 'mjschool_user',
				'page'      => 'message',
				'tab'       => 'sentbox',
				'pg'        => $next,
			) ) );
			$pagination .= ' <a href="' . $url . '" class="btn btn-default next-page"><i class="fa fa-angle-right"></i></a>';
		} else {
			$pagination .= ' <a class="btn btn-default disabled"><i class="fa fa-angle-right"></i></a>';
		}
		$pagination .= "</div>\n";
	}
	return $pagination;
}
/**
 * Generate pagination HTML for inbox.
 *
 * @since 1.0.0
 *
 * @param int $totalposts Total pages.
 * @param int $p          Current page.
 * @param int $lpm1       Limit per page.
 * @param int $prev       Previous page link.
 * @param int $next       Next page link.
 * @return string         Pagination HTML.
 */
function mjschool_inbox_pagination( $totalposts, $p, $lpm1, $prev, $next ) {
	$totalposts = absint( $totalposts );
	$p          = absint( $p );
	$lpm1       = absint( $lpm1 );
	$prev       = absint( $prev );
	$next       = absint( $next );
	$pagination = '';
	$form_id    = 1;
	$page_order = '';
	if ( isset( $_REQUEST['form_id'] ) ) {
		$form_id = absint( $_REQUEST['form_id'] );
	}
	if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
		$orderby    = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		$order      = sanitize_text_field( wp_unslash( $_GET['order'] ) );
		$page_order = '&orderby=' . esc_attr( $orderby ) . '&order=' . esc_attr( $order );
	}
	if ( $totalposts > 1 ) {
		$pagination .= '<div class="btn-group">';
		if ( $p > 1 ) {
			$url         = esc_url( add_query_arg( array(
				'dashboard' => 'mjschool_user',
				'page'      => 'message',
				'tab'       => 'inbox',
				'pg'        => $prev,
			) ) );
			$pagination .= '<a href="' . $url . '" class="btn btn-default"><i class="fa fa-angle-left"></i></a> ';
		} else {
			$pagination .= '<a class="btn btn-default disabled"><i class="fa fa-angle-left"></i></a> ';
		}
		if ( $p < $totalposts ) {
			$url         = esc_url( add_query_arg( array(
				'dashboard' => 'mjschool_user',
				'page'      => 'message',
				'tab'       => 'inbox',
				'pg'        => $next,
			) ) );
			$pagination .= ' <a href="' . $url . '" class="btn btn-default next-page"><i class="fa fa-angle-right"></i></a>';
		} else {
			$pagination .= ' <a class="btn btn-default disabled"><i class="fa fa-angle-right"></i></a>';
		}
		$pagination .= "</div>\n";
	}
	return $pagination;
}

/**
 * Retrieve a single message by message ID.
 *
 * @since 1.0.0
 *
 * @param int $id Message ID.
 * @return object Message record.
 */
function mjschool_get_message_by_id( $id ) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'mjschool_message';
	$id         = absint( $id );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE message_id = %d", $id )
	);
	
	return $retrieve_subject;
}

/**
 * Handle login failure and redirect user with error message.
 *
 * @since 1.0.0
 *
 * @param WP_User|null $user User object or null.
 */
function mjschool_login_failed( $user ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	
	$curr_args = array(
		'page_id' => get_option( 'mjschool_login_page' ),
		'login'   => 'failed',
	);
	
	$referrer_faild = add_query_arg( $curr_args, get_permalink( get_option( 'mjschool_login_page' ) ) );
	
	if ( ! empty( $referrer ) && 
	     ! strstr( $referrer, 'wp-login' ) && 
	     ! strstr( $referrer, 'wp-admin' ) && 
	     $user !== null ) {
		
		if ( ! strstr( $referrer, '&login=failed' ) ) {
			wp_safe_redirect( $referrer_faild );
			exit;
		} else {
			wp_safe_redirect( $referrer );
			exit;
		}
	}
}

/**
 * Handle login attempts with empty fields and redirect with proper error.
 *
 * @since 1.0.0
 *
 * @param WP_User|null $user User object or null.
 */
function mjschool_custom_blank_login( $user ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	$error    = false;
	
	// Check for empty credentials with isset() checks
	$log = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
	$pwd = isset( $_POST['pwd'] ) ? sanitize_text_field( wp_unslash( $_POST['pwd'] ) ) : '';
	
	if ( $log === '' || $pwd === '' ) {
		$error = true;
	}
	
	if ( ! empty( $referrer ) && 
	     ! strstr( $referrer, 'wp-login' ) && 
	     ! strstr( $referrer, 'wp-admin' ) && 
	     $error ) {
		
		if ( ! strstr( $referrer, '&login=failed' ) ) {
			wp_safe_redirect( $referrer . '&login=failed' );
			exit;
		} else {
			wp_safe_redirect( site_url() );
			exit;
		}
	}
}

/**
 * Add custom nonce field to WordPress login form for security.
 *
 * @since 1.0.0
 *
 * @param string $form_html Form HTML content.
 * @return string           Modified form HTML.
 */
function mjschool_add_nonce_to_wp_login_form( $form_html ) {
	$nonce_field = '<input type="hidden" name="custom_login_form_nonce_field" value="' . esc_attr( wp_create_nonce( 'custom_login_form_nonce' ) ) . '" />';
	return $form_html . $nonce_field;
}
add_filter( 'login_form_middle', 'mjschool_add_nonce_to_wp_login_form' );
/**
 * Render custom styled login form and handle all login-related UI.
 *
 * @since 1.0.0
 */
function mjschool_login_link() {
	wp_enqueue_style( 'mjschool-fix-theme-css', plugins_url( '/assets/css/theme/mjschool-fix-login.css', __FILE__ ) );
	$theme_name = get_template();
	if ( $theme_name === 'Divi' ) {
		wp_enqueue_style( 'mjschool-divi-theme-css', plugins_url( '/assets/css/theme/mjschool-divi.css', __FILE__ ) );
	}
	if ( $theme_name === 'Twenty Twenty-Four' || $theme_name === 'Twenty Twenty-Five' ) {
		wp_enqueue_style( 'mjschool-theme-css', plugins_url( '/assets/css/theme/mjschool-twenty-twenty-four-five.css', __FILE__ ) );
	}
	if ( is_rtl() ) {
		wp_enqueue_style( 'mjschool-theme-rtl-css', plugins_url( '/assets/css/theme/mjschool-theme-rtl.css', __FILE__ ) );
	}
	if ( isset( $_GET['login'] ) && sanitize_text_field( wp_unslash( $_GET['login'] ) ) === 'failed' ) {
		?>
		<div id="login-error" class="mjschool_login_error">
			<p class="mjschool-para-margin"><?php esc_html_e( 'Login failed: You have entered an incorrect Username or password, please try again.', 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ( isset( $_GET['login'] ) && sanitize_text_field( wp_unslash( $_GET['login'] ) ) === 'empty' ) {
		?>
		<div id="login-error" class="login-error mjschool_login_error">
			<p class="mjschool-para-margin"><?php esc_html_e( 'Login Failed: Username and/or Password is empty, please try again.', 'mjschool' ); ?></p>
		</div>
		<?php
	}
	if ( isset( $_GET['mjschool_activate'] ) && sanitize_text_field( wp_unslash( $_GET['mjschool_activate'] ) ) === 'mjschool_activate' ) {
		?>
		<div id="login-error" class="mjschool_login_error">
			<p class="mjschool-para-margin"><?php esc_html_e( 'Login failed: Your account is inactive. Contact your administrator to activate it.', 'mjschool' ); ?></p>
		</div>
		<?php
	}
	global $mjschool_reg_errors;
	$mjschool_reg_errors = new WP_Error();
	if ( is_wp_error( $mjschool_reg_errors ) ) {
		foreach ( $mjschool_reg_errors->get_error_messages() as $error ) {
			echo '<div><strong>' . esc_html__( 'ERROR', 'mjschool' ) . '</strong>: ' . esc_html( $error ) . '</div>';
		}
	}
	// Sanitize REQUEST_URI.
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$args = array(
		'echo'           => true,
		'redirect'       => site_url( $request_uri ),
		'form_id'        => 'loginform',
		'label_username' => esc_attr__( 'Username', 'mjschool' ),
		'label_password' => esc_attr__( 'Password', 'mjschool' ),
		'label_remember' => esc_attr__( 'Remember Me', 'mjschool' ),
		'label_log_in'   => esc_attr__( 'Log In', 'mjschool' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'remember'       => true,
		'value_username' => null,
		'value_remember' => false,
	);
	if ( is_user_logged_in() ) {
		$curent_theme = wp_get_theme()->get( 'Name' );
		$style        = '';
		switch ( $curent_theme ) {
			case 'Twenty Twenty-Two':
				$style = 'position: absolute!important; top: 500px!important; left: 13%!important;';
				break;
			case 'Twenty Twenty-Four':
			case 'Twenty Twenty-Five':
				$style = 'position: absolute!important; top: 60%!important; left: 35%!important;';
				break;
			case 'Twenty Twenty-Three':
				$style = 'position: absolute!important; top: 70%!important; left: 30%!important;';
				break;
			default:
				$style = 'float: left!important; margin-left: 7%!important;';
		}
		?>
		<div style="<?php echo esc_attr( $style ); ?>">
			<a href="<?php echo esc_url( home_url( '/?dashboard=mjschool_user' ) ); ?>"><?php esc_html_e( 'Dashboard', 'mjschool' ); ?></a>
			<br />
			<a href="<?php echo esc_url( wp_logout_url() ); ?>"><?php esc_html_e( 'Logout', 'mjschool' ); ?></a>
		</div>
		<?php
	} else {
		?>
		<div class="mjschool-custom-login-form">
			<?php
			wp_login_form( $args );
			echo '<a class="mjschool-forgot-link" href="' . esc_url( wp_lostpassword_url() ) . '" title="' . esc_attr__( 'Lost Password', 'mjschool' ) . '">' . esc_html__( 'Forgot your password?', 'mjschool' ) . '</a>';
			?>
		</div>
		<?php
	}
}

/**
 * Retrieves attendance records for a student within a given date range.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date for filtering attendance (Y-m-d format).
 * @param string $end_date   End date for filtering attendance (Y-m-d format).
 * @param int    $id         Student user ID.
 *
 * @return array List of attendance records.
 */
function mjschool_view_student_attendance( $start_date, $end_date, $id ) {
	global $wpdb;
	
	$tbl_name   = $wpdb->prefix . 'mjschool_attendence';
	$user_id    = absint( $id );
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE user_id = %d AND role_name = %s AND attendence_date BETWEEN %s AND %s", $user_id, 'student', $start_date, $end_date )
	);
	
	return $result;
}

/**
 * Retrieves attendance status for a student on a specific date.
 *
 * @since 1.0.0
 *
 * @param int    $id        Student user ID.
 * @param string $curr_date Attendance date (Y-m-d format).
 *
 * @return string|null Attendance status or null if no record exists.
 */
function mjschool_get_attendence( $id, $curr_date ) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	$userid     = absint( $id );
	$curr_date  = sanitize_text_field( $curr_date );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_var(
		$wpdb->prepare( "SELECT status FROM {$table_name} WHERE attendence_date = %s AND user_id = %d", $curr_date, $userid )
	);
	
	return $result;
}

/**
 * Retrieves subject-wise attendance status for a student on a specific date.
 *
 * @since 1.0.0
 *
 * @param int    $id        Student user ID.
 * @param string $curr_date Attendance date (Y-m-d format).
 * @param int    $sid       Subject ID.
 *
 * @return string|null Attendance status or null if no record exists.
 */
function mjschool_get_sub_attendence( $id, $curr_date, $sid ) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$userid     = absint( $id );
	$sub_id     = absint( $sid );
	$curr_date  = sanitize_text_field( $curr_date );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_var(
		$wpdb->prepare( "SELECT status FROM {$table_name} WHERE attendance_date = %s AND user_id = %d AND sub_id = %d", $curr_date, $userid, $sub_id )
	);
	
	return $result;
}

/**
 * Retrieves attendance comment for a student on a specific date.
 *
 * @since 1.0.0
 *
 * @param int    $id        Student user ID.
 * @param string $curr_date Attendance date (Y-m-d format).
 *
 * @return string Comment text or empty string if not available.
 */
function mjschool_get_attendence_comment( $id, $curr_date ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	$userid     = absint( $id );
	$curr_date  = sanitize_text_field( $curr_date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT comment FROM {$table_name} WHERE attendence_date = %s AND user_id = %d", $curr_date, $userid )
	);
	if ( ! empty( $result ) && isset( $result->comment ) ) {
		return $result->comment;
	}
	return '';
}
/**
 * Retrieves subject-wise attendance comment for a student on a specific date.
 *
 * @since 1.0.0
 *
 * @param int    $id        Student user ID.
 * @param string $curr_date Attendance date (Y-m-d format).
 * @param int    $sid       Subject ID.
 *
 * @return string Comment text or empty string if not available.
 */
function mjschool_get_sub_attendence_comment( $id, $curr_date, $sid ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$userid     = absint( $id );
	$sub_id     = absint( $sid );
	$curr_date  = sanitize_text_field( $curr_date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT comment FROM {$table_name} WHERE attendance_date = %s AND user_id = %d AND sub_id = %d", $curr_date, $userid, $sub_id )
	);
	if ( ! empty( $result ) && isset( $result->comment ) ) {
		return $result->comment;
	}
	return '';
}
/**
 * Deletes a notification record by its ID.
 *
 * @since 1.0.0
 *
 * @param int $sid Notification ID.
 *
 * @return int|false Number of rows deleted on success, false on failure.
 */
function mjschool_delete_notification( $sid ) {
	global $wpdb;
	$mjschool_notification = $wpdb->prefix . 'mjschool_notification';
	$notification_id       = absint( $sid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query(
		$wpdb->prepare( "DELETE FROM {$mjschool_notification} WHERE notification_id = %d", $notification_id )
	);
	return $result;
}
/**
 * Checks if a student has any issued or submitted library books.
 *
 * @since 1.0.0
 *
 * @param int $sid Student ID.
 *
 * @return array|null List of issued/submitted books or null if none found.
 */
function mjschool_check_book_issued( $sid ) {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	$student_id      = absint( $sid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$booklist = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_issuebook} WHERE student_id = %d AND (status = %s OR status = %s)", $student_id, 'Issue', 'Submitted' )
	);
	if ( ! empty( $booklist ) ) {
		return $booklist;
	}
	return null;
}
/**
 * Retrieves all teacher IDs assigned to a specific subject.
 *
 * @since 1.0.0
 *
 * @param object $subject_id Subject object containing the property `subid`.
 *
 * @return array List of teacher IDs associated with the subject.
 */
function mjschool_teacher_by_subject( $subject_id ) {
	global $wpdb;
	$teacher_rows = array();
	if ( isset( $subject_id->subid ) ) {
		$subid                    = absint( $subject_id->subid );
		$table_mjschool_subject = $wpdb->prefix . 'mjschool_teacher_subject';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_mjschool_subject} WHERE subject_id = %d", $subid
			)
		);
		
		foreach ( $result as $tch_result ) {
			if ( isset( $tch_result->teacher_id ) ) {
				$teacher_rows[] = absint( $tch_result->teacher_id );
			}
		}
	}
	
	return $teacher_rows;
}
/**
 * Handles inventory image upload for subject syllabus files.
 *
 * @since 1.0.0
 *
 * @param string $file Existing file path to be replaced (optional).
 *
 * @return string|false Uploaded file name or false on failure.
 */
function mjschool_inventory_image_upload( $file ) {
	$type = 'subject_syllabus';
	
	// Validate file exists and no error
	if ( ! isset( $_FILES[ $type ] ) || $_FILES[ $type ]['error'] !== UPLOAD_ERR_OK ) {
		return false;
	}
	
	$file_name = isset( $_FILES[ $type ]['name'] ) ? sanitize_file_name( $_FILES[ $type ]['name'] ) : '';
	$file_tmp  = isset( $_FILES[ $type ]['tmp_name'] ) ? $_FILES[ $type ]['tmp_name'] : '';
	$file_size = isset( $_FILES[ $type ]['size'] ) ? absint( $_FILES[ $type ]['size'] ) : 0;
	
	// Validate file type
	$check_document = mjschool_wp_check_file_type_and_ext( $file_tmp, $file_name );
	if ( ! $check_document ) {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
	
	// Get file info
	$file_info = wp_check_filetype( $file_name );
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		wp_die( esc_html__( 'Invalid file type.', 'mjschool' ) );
	}
	
	// Generate secure filename
	$inventoryimagename = 'mjschool_' . time() . '-in.' . $file_info['ext'];
	
	// Validate file size (5MB max)
	$max_size = 5 * 1024 * 1024;
	if ( $file_size > $max_size ) {
		wp_die( esc_html__( 'File size exceeds maximum allowed (5MB).', 'mjschool' ) );
	}
	
	$document_dir  = WP_CONTENT_DIR . '/uploads/school_assets/';
	$document_path = $document_dir;
	
	// Remove old file if exists
	$file = sanitize_text_field( $file );
	if ( ! empty( $file ) && file_exists( WP_CONTENT_DIR . '/' . $file ) ) {
		wp_delete_file( WP_CONTENT_DIR . '/' . $file );
	}
	
	if ( ! file_exists( $document_path ) ) {
		wp_mkdir_p( $document_path );
	}
	
	if ( is_uploaded_file( $file_tmp ) ) {
		$upload_path = $document_path . $inventoryimagename;
		if ( move_uploaded_file( $file_tmp, $upload_path ) ) {
			chmod( $upload_path, 0644 );
			return $inventoryimagename;
		}
	}
	
	return false;
}
/**
 * Uploads a teacher signature image to the school assets directory.
 *
 * @since 1.0.0
 *
 * @param array $file Uploaded signature file array from $_FILES.
 *
 * @return string|false Relative path to the uploaded signature image or false on failure.
 */
function mjschool_upload_teacher_signature( $file ) {
	// Validate array structure
	if ( ! is_array( $file ) || ! isset( $file['tmp_name'] ) || ! isset( $file['name'] ) ) {
		return false;
	}
	// Validate upload error
	if ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
		return false;
	}
	$file_name = sanitize_file_name( $file['name'] );
	$file_tmp  = $file['tmp_name'];
	$file_size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
	// Validate file type
	$check_document = wp_check_filetype_and_ext( $file_tmp, $file_name );
	if ( ! $check_document || ! $check_document['ext'] ) {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
	// Get file info
	$file_info = wp_check_filetype( $file_name );
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		wp_die( esc_html__( 'Invalid file type.', 'mjschool' ) );
	}
	// Generate secure filename
	$inventoryimagename = time() . '-signature.' . $file_info['ext'];
	// Validate file size (5MB max)
	$max_size = 5 * 1024 * 1024;
	if ( $file_size > $max_size ) {
		wp_die( esc_html__( 'File size exceeds maximum allowed (5MB).', 'mjschool' ) );
	}
	$document_dir = WP_CONTENT_DIR . '/uploads/school_assets/';
	$imagepath    = $document_dir . $inventoryimagename;
	if ( ! file_exists( $document_dir ) ) {
		wp_mkdir_p( $document_dir );
	}
	if ( is_uploaded_file( $file_tmp ) ) {
		if ( move_uploaded_file( $file_tmp, $imagepath ) ) {
			chmod( $imagepath, 0644 );
			return 'uploads/school_assets/' . $inventoryimagename;
		}
	}
	return false;
}
/**
 * Registers the custom post type for internal messaging.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_register_post() {
	register_post_type(
		'message',
		array(
			'labels'    => array(
				'name'          => esc_attr__( 'Message', 'mjschool' ),
				'singular_name' => 'message',
			),
			'public'    => false,
			'rewrite'   => false,
			'query_var' => false,
		)
	);
}
/**
 * Generates the HTML structure for the student fees receipt history PDF.
 *
 * @since 1.0.0
 *
 * @param string $id      Encrypted ID of the fees payment record.
 * @param string $receipt Encrypted ID of the payment history record.
 *
 * @return void Outputs styled HTML which is then used for PDF generation.
 */
function mjschool_student_receipt_history_pdf( $id, $receipt ) {
	$mjschool_obj_feespayment = new Mjschool_Feespayment();
	$format             = get_option( 'mjschool_invoice_option' );
	$fees_pay_id        = mjschool_decrypt_id( $id );
	$fees_pay_id        = absint( $fees_pay_id );
	$invoice_number     = mjschool_generate_invoice_number( $fees_pay_id );
	$fees_detail_result = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$fee_pay_id         = intval( mjschool_decrypt_id( $receipt ) );
	$fees_history       = $mjschool_obj_feespayment->mjschool_get_single_payment_history( $fee_pay_id );
	// Validate data exists.
	if ( empty( $fees_detail_result ) || empty( $fees_history ) ) {
		return;
	}
	?>
	<?php if ( $format != 1 ) : ?>
		<?php if ( is_rtl() ) : ?>
			<h3><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h3>
			<table style="float: right;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>
						<td>
							<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url( plugins_url( '/mjschool/assets/images/listpage_icon/invoice_rtl.png' ) ); ?>" width="100%">
						</td>
					</tr>
				</tbody>
			</table>
		<?php else : ?>
			<h3><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h3>
			<table style="float: left;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>
						<td>
							<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url( plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ); ?>" width="100%">
						</td>
					</tr>
				</tbody>
			</table>
		<?php endif; ?>
	<?php endif; ?>
	<div class="mjschool-width-print" style="border: 2px solid;float:left;width:96%;margin: 0px 0px 0px 0px;padding:20px;padding-top: 4px;padding-bottom: 5px;margin-bottom: 0px !important">
		<div style="float:left;width:100%;">
			<div style="float:left;width:25%;">
				<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
					<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool_main_logo_class" alt="<?php echo esc_attr( get_option( 'mjschool_name' ) ); ?>" />
				</div>
			</div>
			<div style="float:left; width:75%;padding-top:10px;">
				<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;">
					<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
				</p>
				<p style="margin:0px;font-size:17px;text-align:center;">
					<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
				</p>
				<div style="margin:0px;width:100%;text-align:center;">
					<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;">
						<?php esc_html_e( 'E-mail', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>&nbsp;&nbsp;
						<?php esc_html_e( 'Phone', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
	<br>
	<div class="mjschool-width-print" style="border: 2px solid;margin-bottom:8px;float:left;width:96%;padding:20px;padding-top: 5px;padding-bottom: 5px;margin-bottom: 0px !important;margin-top: 0px !important">
		<div style="float:left;width:100%;">
			<?php
			$student_id = isset( $fees_detail_result->student_id ) ? absint( $fees_detail_result->student_id ) : 0;
			$patient    = get_userdata( $student_id );
			?>
			<div class="mjschool_padding_10px">
				<div style="float:left;width:65%;">
					<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b> <?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
				</div>
				<div style="float:left;width:35%;">
					<b><?php esc_html_e( 'Receipt Number', 'mjschool' ); ?>:</b> <?php echo esc_html( mjschool_generate_receipt_number( $fee_pay_id ) ); ?>
				</div>
			</div>
		</div>
		<div style="float:left; width:64%;">
			<?php if ( $patient ) : ?>
				<?php
				$address = esc_html( get_user_meta( $student_id, 'address', true ) );
				$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
				$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
				?>
				<div style="padding:5px;">
					<div>
						<b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b> <?php echo esc_html( $address ); ?>
					</div>
					<div><?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?></div>
				</div>
			<?php endif; ?>
		</div>
		<div style="float:right;width: 35.3%;">
			<?php
			$issue_date = isset( $fees_history[0]->paid_by_date ) ? $fees_history[0]->paid_by_date : '';
			$issue_date = sanitize_text_field( $issue_date );
			?>
			<div style="padding:5px 0;">
				<div class="mjschool_float_left_width_100">
					<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b> 
					<?php
					if ( ! empty( $issue_date ) ) {
						echo esc_html( mjschool_get_date_in_input_box( gmdate( 'Y-m-d', strtotime( $issue_date ) ) ) );
					}
					?>
				</div>
			</div>
			<div style="float:right;width: 100%;">
				<div style="padding:5px 0;">
					<div class="mjschool_float_left_width_100">
						<b><?php esc_html_e( 'Payment Method', 'mjschool' ); ?>:</b> <?php echo isset( $fees_history[0]->payment_method ) ? esc_html( $fees_history[0]->payment_method ) : ''; ?>
					</div>
				</div>
			</div>
			<div style="float:right;width: 100%;">
				<div style="padding:5px 0;">
					<div style="float:left;width:100%;">
						<b><?php esc_html_e( 'Invoice Reference', 'mjschool' ); ?>:</b> <?php echo esc_html( $invoice_number ); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<p style="font-size: 20px;font-weight: 700;color: black;text-align: center;">
		<?php esc_html_e( 'Payment History', 'mjschool' ); ?>
	</p>
	<div style="padding:10px 0;" class="mb-3">
		<div style="float:left;width:100%;">
			<b><?php esc_html_e( 'Transaction Id', 'mjschool' ); ?>:</b> <?php echo isset( $fees_history[0]->trasaction_id ) ? esc_html( $fees_history[0]->trasaction_id ) : ''; ?>
		</div>
	</div>
	<?php
	$custom_field_obj = new mjschool_custome_field();
	$module           = 'fee_transaction';
	$custom_field_obj->mjschool_show_inserted_customfield_receipt( $module );
	?>
	<table class="table table-bordered" width="100%" style="border-collapse: collapse; border: 1px solid black;">
		<thead style="background-color: #b8daff !important;">
			<tr>
				<th style="text-align: center; font-weight: 700; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;">
					<?php esc_html_e( 'Description', 'mjschool' ); ?>
				</th>
				<th style="text-align: center; font-weight: 700; color: black; padding: 10px; border: 1px solid black;background-color: #b8daff !important;">
					<?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $fees_history as $retrive_date ) : ?>
				<tr>
					<td style="text-align: left; font-weight: 600; color: #333333; padding: 10px 10px 130px 10px; border: 1px solid black;">
						<?php echo isset( $retrive_date->payment_note ) ? esc_html( $retrive_date->payment_note ) : ''; ?>
					</td>
					<td style="text-align: right; font-weight: 600; color: #333333; padding: 10px 10px 130px 10px; border: 1px solid black;">
						<?php echo isset( $retrive_date->amount ) ? esc_html( $retrive_date->amount ) : ''; ?>
					</td>
				</tr>
				<tr>
					<th style="border: 1px solid black; width: 70%; text-align: right;padding: 10px;">
						<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
					</th>
					<th style="border: 1px solid black; width: 30%; text-align: left;padding: 10px;">
						<?php
						if ( isset( $retrive_date->amount ) ) {
							echo esc_html( number_format( floatval( $retrive_date->amount ), 2, '.', '' ) );
						}
						?>
					</th>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<p class="mt-2" style="font-size: 16px;font-weight: 700;">
		<?php
		if ( isset( $retrive_date->amount ) ) {
			echo esc_html( ucfirst( mjschool_convert_number_to_words( $retrive_date->amount ) ) . ' Only' );
		}
		?>
	</p>
	
	<div style="border: 2px solid; width:100%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;margin-top: 4px;">
		<div style="float: right; width: 33.33%; text-align: center;">
			<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" alt="<?php esc_attr_e( 'Principal Signature', 'mjschool' ); ?>" /> </div>
			<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
			<div style="margin-top: 5px;"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
		</div>
	</div>
	<?php
}
/**
 * Retrieves the list of issued library books for a given student.
 *
 * @since 1.0.0
 *
 * @param int $id Student ID.
 *
 * @return array List of issued book records.
 */
function mjschool_get_student_library_book_list( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_library_book_issue';
	$id         = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE student_id = %d", $id )
	);
	return $results;
}
/**
 * Loads and renders the category/section popup form based on the provided model.
 *
 * This function displays dynamic forms and lists for fee types, book categories,
 * rack locations, and class sections. It also handles model-specific labels,
 * titles, and button text for the popup UI.
 *
 * @since 1.0.0
 *
 * @param string $model     Category model type (feetype|book_cat|rack_type|class_sec|period_type).
 * @param int    $class_id  Optional class ID (required for class section model).
 *
 * @return void Outputs HTML directly and terminates with wp_die().
 */
function mjschool_add_category_type( $model, $class_id ) {
	// Sanitize inputs.
	$model    = sanitize_key( $model );
	$class_id = absint( $class_id );
	$title              = 'Title here';
	$table_header_title = 'Table head';
	$button_text        = 'Button Text';
	$label_text         = 'Label Text';
	if ( $model === 'feetype' ) {
		$obj_fees           = new mjschool_fees();
		$cat_result         = $obj_fees->mjschool_get_all_feetype();
		$title              = esc_attr__( 'Fee type', 'mjschool' );
		$table_header_title = esc_attr__( 'Fee Type', 'mjschool' );
		$button_text        = esc_attr__( 'Add Fee Type', 'mjschool' );
		$label_text         = esc_attr__( 'Fee Type', 'mjschool' );
	}
	if ( $model === 'book_cat' ) {
		$obj_lib            = new mjschoollibrary();
		$cat_result         = $obj_lib->mjschool_get_bookcat();
		$title              = esc_attr__( 'Category', 'mjschool' );
		$table_header_title = esc_attr__( 'Category Name', 'mjschool' );
		$button_text        = esc_attr__( 'Add Category', 'mjschool' );
		$label_text         = esc_attr__( 'Category Name', 'mjschool' );
	}
	if ( $model === 'rack_type' ) {
		$obj_lib            = new mjschoollibrary();
		$cat_result         = $obj_lib->mjschool_get_rack_list();
		$title              = esc_attr__( 'Rack Location', 'mjschool' );
		$table_header_title = esc_attr__( 'Rack Location Name', 'mjschool' );
		$button_text        = esc_attr__( 'Add Rack Location', 'mjschool' );
		$label_text         = esc_attr__( 'Rack Location Name', 'mjschool' );
	}
	if ( $model === 'class_sec' ) {
		$title              = esc_attr__( 'Class Section', 'mjschool' );
		$table_header_title = esc_attr__( 'Section Name', 'mjschool' );
		$button_text        = esc_attr__( 'Add Section', 'mjschool' );
		$label_text         = esc_attr__( 'Section Name', 'mjschool' );
	}
	?>
	<!-- Trigger for JS. -->
	<div id="mjschool-category-popup-trigger" data-trigger="1"></div>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
		<a href="javascript:void(0);" class="mjschool-event-close-btn badge badge-success pull-right mjschool-dashboard-popup-design">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>" alt="<?php esc_attr_e( 'Close', 'mjschool' ); ?>">
		</a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( $title ); ?></h4>
	</div>
	<div class="mjschool-panel-white">
		<div class="mjschool-category-listbox">
			<form name="fee_form" action="" method="post" class="mjschool-category-popup-float mjschool-form-horizontal mjschool-admission-form-popup" id="fees_type_form">
				<!---CATEGORY_FORM----->
				<input type="hidden" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
				<div class="form-body mjschool-user-form">
					<div class="row">
						<?php if ( $model === 'period_type' ) : ?>
							<div class="col-md-8">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="fees_type_val" class="form-control text-input validate[required]" maxlength="3" type="number" value="" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" name="txtfee_type" placeholder="<?php esc_attr_e( 'Must Be Enter Number of Days', 'mjschool' ); ?>">
										<label for="userinput1" class="active"><?php esc_html_e( 'Section Name', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						<?php else : ?>
							<div class="col-md-8">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="fees_type_val" class="form-control text-input validate[required,custom[popup_category_validation]]" maxlength="50" type="text" value="" name="txtfee_type">
										<label for="userinput1"><?php esc_html_e( 'Section Name', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						<?php endif; ?>
						<div class="col-md-4">
							<input type="button" <?php if ( $model === 'class_sec' ) : ?> class_id="<?php echo esc_attr( $class_id ); ?>" <?php endif; ?> value="<?php echo esc_attr( $button_text ); ?>" name="save_category" class="btn mjschool-save-btn<?php echo esc_attr( $model ); ?> mjschool-btn-top btn-success" model="<?php echo esc_attr( $model ); ?>" id="btn-add-cat" />
						</div>
					</div>
				</div>
			</form>
			<div class="mjschool-category-listbox_new mjschool-admission-pop-up-new">
				<div class="class_detail_append col-lg-12 col-md-12 col-xs-12 col-sm-12">
					<?php $i = 1; ?>
					<div class="div_new_1">
						<?php
						if ( $model === 'class_sec' ) {
							$section_result = mjschool_get_class_sections( $class_id );
							if ( ! empty( $section_result ) ) {
								foreach ( $section_result as $retrieved_data ) {
									if ( ! isset( $retrieved_data->id ) || ! isset( $retrieved_data->section_name ) ) {
										continue;
									}
									?>
									<div class="row mjschool-new-popup-padding" id="<?php echo 'cat-' . esc_attr( $retrieved_data->id ); ?>">
										<div class="col-md-10 mjschool-width-70px">
											<?php echo esc_html( $retrieved_data->section_name ); ?>
										</div>
										<div class="row col-md-2 mjschool-padding-left-0-res mjschool-width-30px" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
											<div class="col-md-6 mjschool-width-50-res mjschool-padding-left-0">
												<a href="#" class="btn-delete-cat" model="<?php echo esc_attr( $model ); ?>" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage_icon/Delete.png' ); ?>" alt="<?php esc_attr_e( 'Delete', 'mjschool' ); ?>">
												</a>
											</div>
											<div class="col-md-6 mjschool-edit-btn-padding-left-25px-res mjschool-width-50-res mjschool-padding-right-0">
												<a class="mjschool-btn-edit-cat" model="<?php echo esc_attr( $model ); ?>" href="#" id="<?php echo esc_attr( $retrieved_data->id ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage_icon/mjschool-edit.png' ); ?>" alt="<?php esc_attr_e( 'Edit', 'mjschool' ); ?>">
												</a>
											</div>
										</div>
									</div>
									<?php
									$i++;
								}
							}
						} else {
							if ( ! empty( $cat_result ) ) {
								foreach ( $cat_result as $retrieved_data ) {
									if ( ! isset( $retrieved_data->ID ) || ! isset( $retrieved_data->post_title ) ) {
										continue;
									}
									?>
									<div class="row mjschool-new-popup-padding" id="<?php echo 'cat-' . esc_attr( $retrieved_data->ID ); ?>">
										<div class="col-md-11 mjschool-width-80px mjschool-mt-7px">
											<?php
											if ( $model === 'period_type' ) {
												echo esc_html( $retrieved_data->post_title ) . ' ' . esc_html__( 'Days', 'mjschool' );
											} else {
												echo esc_html( $retrieved_data->post_title );
											}
											?>
										</div>
										<div class="row col-md-1 mjschool-rs-popup-width-20px" id="<?php echo esc_attr( $retrieved_data->ID ); ?>">
											<div class="col-md-12">
												<a href="#" class="btn-delete-cat" model="<?php echo esc_attr( $model ); ?>" id="<?php echo esc_attr( $retrieved_data->ID ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage_icon/Delete.png' ); ?>" alt="<?php esc_attr_e( 'Delete', 'mjschool' ); ?>">
												</a>
											</div>
										</div>
									</div>
									<?php
									++$i;
								}
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	wp_die();
}
/**
 * Retrieves details of a single class section by section ID.
 *
 * @since 1.0.0
 *
 * @param int $section_id Section ID.
 *
 * @return object|null Section record object or null if not found.
 */
function mjschool_single_section( $section_id ) {
	global $wpdb;
	$mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
	$section_id             = absint( $section_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$mjschool_class_section} WHERE id = %d", $section_id )
	);
	return $result;
}

/**
 * Generates and prints the student fees payment receipt.
 *
 * This function decrypts the incoming fee payment ID, retrieves fee details,
 * payment history, student information, invoice references, custom fields,
 * and renders a fully formatted printable HTML receipt with all payment data.
 *
 * @since 1.0.0
 *
 * @param string $fees_pay_id Encrypted fees payment ID used to retrieve the receipt.
 *
 * @return void Outputs HTML and inline CSS for printing the fees receipt.
 */
function mjschool_student_fees_receipt_print( $fees_pay_id ) {
	wp_print_styles();
	
	$fees_pay_id = absint( mjschool_decrypt_id( $fees_pay_id ) );
	
	// Validate receipt_id with isset() check
	$receipt_id = isset( $_REQUEST['receipt_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['receipt_id'] ) ) : '';
	if ( empty( $receipt_id ) ) {
		return;
	}
	
	$fee_pay_id = absint( mjschool_decrypt_id( $receipt_id ) );
	
	$fees_detail_result = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$invoice_number     = mjschool_generate_invoice_number( $fees_pay_id );
	$fees_history       = mjschool_get_single_payment_history( $fee_pay_id );
	
	// Validate required data exists
	if ( empty( $fees_detail_result ) || empty( $fees_history ) ) {
		return;
	}
	?>
	<?php if ( is_rtl() ) : ?>
		<style>
			.rtl_billto {
				margin-right: -18px;
			}
			.new-rtl-padding-fix {
				padding-left: 12px !important;
			}
			.rtl_sings {
				width: 98% !important;
				margin-left: 2% !important;
			}
		</style>
	<?php endif; ?>
	<style>
		body,
		body * {
			font-family: 'Poppins' !important;
		}
		table thead {
			-webkit-print-color-adjust: exact;
		}
		.mjschool-invoice-table-grand-total {
			-webkit-print-color-adjust: exact;
			background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;
		}
		@media print {
			* {
				color-adjust: exact !important;
				-webkit-print-color-adjust: exact !important;
				print-color-adjust: exact !important;
			}
			.invoice_description {
				width: 75%;
			}
			.mjschool_invoce_notice {
				width: 100%;
				float: left;
			}
		}
	</style>
	<div id="Fees_invoice">
		<div class="modal-body mjschool-margin-top-15px-rs mjschool-invoice-model-body mjschool-float-left-width-100px mjschool-custom-padding-0_res height_1000px">
			<div id="mjschool-invoice-print" class="mjschool-main-div mjschool-float-left-width-100px mjschool-payment-invoice-popup-main-div">
				<div class="mjschool-invoice-width-100px mjschool-float-left" border="0">
					<div class="row mjschool_margin_right_0px">
						<div class="mjschool-width-print mjschool_border_print_width_98">
							<div class="mjschool_float_left_width_100">
								<div class="mjschool_float_left_width_25">
									<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
										<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" class="mjschool_main_logo_class" alt="<?php echo esc_attr( get_option( 'mjschool_name' ) ); ?>" />
									</div>
								</div>
								<div class="mjschool_float_left_width_75">
									<p class="mjschool_fees_widht_100_fonts_24px">
										<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
									</p>
									<p class="mjschool_print_invoice_line_height_30px">
										<?php echo esc_html( get_option( 'mjschool_address' ) ); ?>
									</p>
									<div class="mjschool_fees_center_margin_0px">
										<p class="mjschool_receipt_print_margin_0px">
											<?php esc_html_e( 'E-mail', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
										</p>
										<p class="mjschool_receipt_print_margin_0px">
											&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?>: <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
										</p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 col-sm-12 col-xl-12 mjschool-mozila-display-css mjschool-margin-top-10px">
						<div class="mjschool-width-print rtl_billto mjschool_print_padding_bottom_top_border_2px">
							<div class="mjschool_float_left_width_100">
								<?php
								$student_id = isset( $fees_detail_result->student_id ) ? absint( $fees_detail_result->student_id ) : 0;
								$patient    = get_userdata( $student_id );
								
								if ( $patient && isset( $patient->display_name ) ) {
									$display_name         = $patient->display_name;
									$escaped_display_name = esc_html( ucwords( $display_name ) );
									$split_display_name   = chunk_split( $escaped_display_name, 30, '<br>' );
								} else {
									$split_display_name = esc_html__( 'N/A', 'mjschool' );
								}
								?>
								<div class="mjschool_padding_10px">
									<div class="mjschool_float_left_width_65">
										<b><?php esc_html_e( 'Bill To', 'mjschool' ); ?>:</b>
										<?php echo esc_html( mjschool_student_display_name_with_roll( $student_id ) ); ?>
									</div>
									<div class="mjschool_float_right_width_35">
										<b><?php esc_html_e( 'Receipt Number', 'mjschool' ); ?>:</b>
										<?php echo esc_html( mjschool_generate_receipt_number( $fee_pay_id ) ); ?>
									</div>
								</div>
							</div>
							<div class="mjschool_float_left_width_65">
								<?php if ( $patient ) : ?>
									<?php
									$address = esc_html( get_user_meta( $student_id, 'address', true ) );
									$city    = esc_html( get_user_meta( $student_id, 'city', true ) );
									$zip     = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
									?>
									<div class="mjschool_padding_10px">
										<div>
											<b><?php esc_html_e( 'Address', 'mjschool' ); ?>:</b> <?php echo esc_html( $address ); ?>
										</div>
										<div><?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?></div>
									</div>
								<?php endif; ?>
							</div>
							<div class="mjschool_float_right_width_35">
								<?php
								$issue_date = isset( $fees_history[0]->paid_by_date ) ? $fees_history[0]->paid_by_date : '';
								$issue_date = sanitize_text_field( $issue_date );
								?>
								<div class="mjschool_padding_0_10px">
									<div class="mjschool_float_left_width_100">
										<b><?php esc_html_e( 'Issue Date', 'mjschool' ); ?>:</b>
										<?php
										if ( ! empty( $issue_date ) ) {
											echo esc_html( mjschool_get_date_in_input_box( gmdate( 'Y-m-d', strtotime( $issue_date ) ) ) );
										}
										?>
									</div>
								</div>
							</div>
							<div class="mjschool_float_right_width_35">
								<div class="mjschool_fees_padding_10px">
									<div class="mjschool_float_left_width_100">
										<b><?php esc_html_e( 'Payment Method', 'mjschool' ); ?>:</b>
										<?php echo isset( $fees_history[0]->payment_method ) ? esc_html( $fees_history[0]->payment_method ) : ''; ?>
									</div>
								</div>
							</div>
							<div class="mjschool_float_right_width_35">
								<div class="mjschool_fees_padding_10px">
									<div class="mjschool_float_left_width_100">
										<b><?php esc_html_e( 'Invoice Reference', 'mjschool' ); ?>:</b>
										<?php echo esc_html( $invoice_number ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<table class="mjschool-width-100px mjschool-margin-top-10px-res mt-2">
						<tbody>
							<tr>
								<td>
									<h3 class="display_name mjschool-res-pay-his-mt-10px mjschool_fees_center_font_24px">
										<?php esc_html_e( 'Payment Receipt', 'mjschool' ); ?>
									</h3>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="mjschool_fees_padding_10px mb-3">
						<div class="mjschool_float_left_width_100">
							<b><?php esc_html_e( 'Transaction Id', 'mjschool' ); ?>:</b>
							<?php echo isset( $fees_history[0]->trasaction_id ) ? esc_html( $fees_history[0]->trasaction_id ) : ''; ?>
						</div>
					</div>
					<?php
					$custom_field_obj = new mjschool_custome_field();
					$module           = 'fee_transaction';
					$custom_field_obj->mjschool_show_inserted_customfield_receipt( $module );
					?>
					<div class="table-responsive mjschool-rtl-padding-left-40px">
						<table class="table table-bordered mjschool-model-invoice-table mjschool_fees_collapse_width_100">
							<thead class="mjschool-entry-heading mjschool-invoice-model-entry-heading mjschool_white_black_color">
								<tr>
									<th class="mjschool-entry-table-heading mjschool-align-left mjschool_border_print_width_70">
										<?php esc_html_e( 'Description', 'mjschool' ); ?>
									</th>
									<th class="mjschool-entry-table-heading mjschool-align-left mjschool_border_print_width_30">
										<?php echo esc_html__( 'Amount', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $fees_history as $retrive_date ) {
									if ( ! isset( $retrive_date->payment_note ) || ! isset( $retrive_date->amount ) ) {
										continue;
									}
									?>
									<tr class="mjschool_height_150px">
										<td class="mjschool_print_vertical_align_70">
											<?php echo esc_html( $retrive_date->payment_note ); ?>
										</td>
										<td class="mjschool_print_vertical_align_30">
											<?php echo esc_html( number_format( floatval( $retrive_date->amount ), 2, '.', '' ) ); ?>
										</td>
									</tr>
								<?php } ?>
								<tr>
									<th class="mjschool_right_width_70">
										<?php echo esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
									</th>
									<th class="mjschool_left_width_30">
										<?php
										if ( isset( $retrive_date->amount ) ) {
											echo esc_html( number_format( floatval( $retrive_date->amount ), 2, '.', '' ) );
										}
										?>
									</th>
								</tr>
							</tbody>
						</table>
						<p class="mt-2 mjschool_width_700_font_16px">
							<?php
							if ( isset( $retrive_date->amount ) ) {
								echo esc_html( ucfirst( mjschool_convert_number_to_words( $retrive_date->amount ) ) . ' Only' );
							}
							?>
						</p>
					</div>
					<?php
					if ( is_rtl() ) {
						$align = 'left';
					} else {
						$align = 'right';
					}
					?>
					<div class="rtl_sings mjschool_print_boder_2px_margin_float_left">
						<div class="mjschool_fees_center_width_33">
							<div>
								<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" class="mjschool_width_100px" alt="<?php esc_attr_e( 'Principal Signature', 'mjschool' ); ?>" />
							</div>
							<div class="mjschool_fees_width_150px"></div>
							<div class="mjschool_margin_top_5px">
								<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
/**
 * Generate and display the PDF layout for a student invoice, income, or expense record.
 *
 * This function prepares all required data based on the invoice type,
 * formats the content layout (RTL/LTR), and renders the invoice view
 * including billing details, payment status, invoice entries, taxes,
 * discounts, and final totals.
 *
 * @since 1.0.0
 *
 * @param int    $invoice_id   Encrypted invoice ID used to fetch invoice data.
 * @param string $invoice_type Type of invoice. Accepted values: 'invoice', 'income', 'expense'.
 *
 * @return void
 */
function mjschool_student_invoice_pdf( $invoice_id, $invoice_type ) {
	$format      = get_option( 'mjschool_invoice_option' );
	$invoice_id  = intval( mjschool_decrypt_id( $invoice_id ) );
	$obj_invoice = new mjschool_invoice();
	if ( $invoice_type === 'invoice' ) {
		$invoice_data = mjschool_get_payment_by_id( $invoice_id );
	}
	if ( $invoice_type === 'income' ) {
		$income_data = $obj_invoice->mjschool_get_income_data( $invoice_id );
	}
	if ( $invoice_type === 'expense' ) {
		$expense_data = $obj_invoice->mjschool_get_income_data( $invoice_id );
	}
	?>
	<style>
		.mjschool-popup-label-heading {
			color: #818386;
			font-size: 14px !important;
			font-weight: 500;
			font-family: 'Poppins' !important;
			text-transform: capitalize;
		}
	</style>
	<?php
	if ( $format != 1 ) {
		if ( is_rtl() ) {
			?>
			<h3 ><?php echo esc_html( get_option( 'mjschool_name' ) ); ?></h3>
			<table style="float: right;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>

						<td> <img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice_rtl.png' ) ); ?>" width="100%"> </td>
					</tr>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<table style="float: left;position: absolute;vertical-align: top;background-repeat: no-repeat;">
				<tbody>
					<tr>
						<td> <img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="<?php echo esc_url(plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ); ?>" width="100%"> </td>
					</tr>
				</tbody>
			</table>
			<?php
		}
	}
	?>
	<?php if ($format === 1) { ?>
		<?php
		if (is_rtl()) {
			?>
			<div class="width_print" style="border: 2px solid;margin-bottom:8px;float:left;width:96%;padding:20px;">
			<?php } else { ?>
				<div class="width_print" style="border: 2px solid;margin-bottom:8px;float:left;width:100%;padding:20px;">
				<?php } ?>
				<div style="float:left;width:100%; ">
					<div style="float:left;width:25%;">
						<div class="asasa" style="float:letf;border-radius:50px;">
							<img src="<?php echo esc_url(get_option('mjschool_school_logo')); ?>" style="height: 130px;border-radius:50%;background-repeat:no-repeat;background-size:cover;margin-top: 3px;" />
						</div>
					</div>
					<div style="float:left; width:75%;padding-top:10px;">
						<p style="margin:0px;width:100%;font-weight:bold;color:#1B1B8D;font-size:24px;text-align:center;"> <?php echo esc_html(get_option('mjschool_school_name')); ?></p>
						<p style="margin:0px;font-size:17px;text-align:center;"> <?php echo esc_html(get_option('mjschool_school_address')); ?></p>
						<div style="margin:0px;width:100%;text-align:center;">
							<p style="margin: 0px;width: fit-content;font-size: 17px;display: inline-block;">
								<?php esc_html_e('E-mail', 'mjschool'); ?> : <?php echo esc_html(get_option('mjschool_email')); ?>&nbsp;&nbsp;<?php esc_html_e('Phone', 'mjschool'); ?> : <?php echo esc_html(get_option('mjschool_contact_number')); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<table style="float: left;width: 100%;position: absolute!important;margin-top:-170px;">
				<tbody>
					<tr>
						<td>
							<table>
								<tbody>
									<tr>
										<td width="22%">
											<img class="system_logo" src="<?php echo esc_url(get_option('mjschool_school_logo')); ?>">
										</td>
										<?php // @codingStandardsIgnoreEnd ?>
										<td width="80%" style="padding-left: 10px;">
											<label class="popup_label_heading"><?php esc_html_e( 'Address', 'mjschool' ); ?></label><br>
											<label for="" class="label_value word_break_all" style="color: #333333 !important;font-weight: 400;">
												<?php
												$school_address  = get_option( 'mjschool_school_address' );
												$escaped_address = esc_html( $school_address );
												$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 100, '<br>' ) );
												echo wp_kses_post( $split_address );
												?>
												</label><br>
											<label class="popup_label_heading"><?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
											<label for="" style="color: #333333 !important;font-weight: 400;" class="label_value word_break_all"><?php echo esc_html( get_option( 'mjschool_email' ) ), '<BR>'; ?></label><br>
											<label class="popup_label_heading"><?php esc_html_e( 'Phone', 'mjschool' ); ?> </label><br>
											<label for="" style="color: #333333 !important;font-weight: 400;" class="label_value"><?php echo esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>'; ?></label>
										</td>
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		<?php } ?>
	<br>
	<?php
	if ( $format === 1 ) {?>
			<?php
			if ( is_rtl() ) {
				?>
				<div class="width_print" style="border: 2px solid;margin-bottom:8px;float:left;width:96%;padding:20px;">
				<?php } else { ?>
					<div class="width_print" style="border: 2px solid;margin-bottom:8px;float:left;width:100%;padding:20px;">
					<?php } ?>
					<div style="float:left;width:100%;">
						<?php
						if ( ! empty( $expense_data ) ) {
							$party_name = $expense_data->supplier_name;
							$ex_name = $party_name
							? wp_kses_post( chunk_split( ucwords( $party_name ), 30, '<br>' ) )
							: 'N/A';
						} else {
							$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
							$patient    = get_userdata( $student_id );
							$in_name    = $patient ? wp_kses_post( chunk_split( ucwords( $patient->display_name ), 30, '<br>' ) ) : 'N/A';
						}
						?>
						<div class="123" style="padding:10px;">
							<div style="float:left;width:65%;"><b><?php esc_html_e( 'Bill To:', 'mjschool' ); ?></b>
								<?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ) . ' ' . esc_html( get_user_meta( $uid, 'student_id', true ) ); ?>&nbsp;
											<?php
											if ( ! empty( $expense_data ) ) {
												// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
												echo wp_kses_post( $ex_name );
												// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
											} else {
												echo esc_html( mjschool_student_display_name_with_roll( $student_id ) );
											}
											?>
										</div>

							<div style="float:left;width:35%;"><b><?php esc_html_e( 'Status:', 'mjschool' ); ?></b>
								<?php
								$payment_status = '';
								if ( ! empty( $income_data ) ) {
									$payment_status = $income_data->payment_status;
								}
								if ( ! empty( $invoice_data ) ) {
									$payment_status = $invoice_data->payment_status;
								}
								if ( ! empty( $expense_data ) ) {
									$payment_status = $expense_data->payment_status;
								}

								switch ( $payment_status ) {
									case 'Paid':
										echo '<span class="green_color">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
										break;
									case 'Part Paid':
										echo '<span class="perpal_color">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
										break;
									case 'Unpaid':
										echo '<span class="red_color">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
										break;
									default:
										esc_html_e( 'N/A', 'mjschool' );
								}
								?>
							</div>
						</div>
					</div>
					<div style="float:left; width:64%;">
						<?php
						if ( empty( $expense_data ) ) {
							$student_id = ! empty( $income_data ) ? $income_data->supplier_name : $invoice_data->student_id;
							$address    = esc_html( get_user_meta( $student_id, 'address', true ) );
							$city       = esc_html( get_user_meta( $student_id, 'city', true ) );
							$zip        = esc_html( get_user_meta( $student_id, 'zip_code', true ) );

							?>
							<div style="padding:10px;">
								<div><b><?php esc_html_e( 'Address:', 'mjschool' ); ?></b>
									<?php echo esc_html( $address ); ?></div>
								<div><?php echo esc_html( $city ) . ', ' . esc_html( $zip ); ?></div>
							</div>
						<?php } ?>
					</div>
					<div style="float:right;width: 35.3%;">
						<?php
						$issue_date = 'DD-MM-YYYY';
						if ( ! empty( $income_data ) ) {
							$issue_date = $income_data->income_create_date;
						}
						if ( ! empty( $invoice_data ) ) {
							$issue_date = $invoice_data->date;
						}
						if ( ! empty( $expense_data ) ) {
							$issue_date = $expense_data->income_create_date;
						}

						?>
						<div style="padding:10px 0;">
							<div style="float:left;width:100%;"><b><?php esc_html_e( 'Issue Date:', 'mjschool' ); ?></b>
								<?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php
	} else {
		?>
			<table>
				<tbody>
					<tr>
						<td width="70%">
							<h3 class="billed_to_lable invoice_model_heading bill_to_width_12">
							<?php esc_html_e( 'Bill To', 'mjschool' ); ?> : </h3>
						<?php
						if ( ! empty( $expense_data ) ) {
							echo esc_html( $party_name = $expense_data->supplier_name );
						} else {
							if ( ! empty( $income_data ) ) {
								$student_id = $income_data->supplier_name;
							} elseif ( ! empty( $invoice_data ) ) {
								$student_id = $invoice_data->student_id;
							}
							$patient = get_userdata( $student_id );
							if ( $patient ) {
								$display_name = esc_html( ucwords( $patient->display_name ) );
								$split_name   = str_replace( '<br>', '<BR>', chunk_split( $display_name, 100, '<br>' ) );
								echo '<h3 class="display_name invoice_width_100" >' . wp_kses_post( $split_name ) . '</h3>';
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
						}
						?>
							<div>
							<?php
							if ( ! empty( $expense_data ) ) {
								echo esc_html( $party_name = $expense_data->supplier_name );
							} else {
								if ( ! empty( $income_data ) ) {
									$student_id = $income_data->supplier_name;
								}
								$patient = get_userdata( $student_id );

								$address         = get_user_meta( $student_id, 'address', true );
								$escaped_address = esc_html( $address );
								$split_address   = str_replace( '<br>', '<BR>', chunk_split( $escaped_address, 30, '<br>' ) );
								echo wp_kses_post( $split_address );
								echo esc_html( get_user_meta( $student_id, 'city', true ) ) . ',' . '<BR>';
								echo esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . ',<BR>';
							}
							?>
							</div>
						</td>
						<td width="15%">
							<?php
							$issue_date = 'DD-MM-YYYY';
							if ( ! empty( $income_data ) ) {
								$issue_date     = $income_data->income_create_date;
								$payment_status = $income_data->payment_status;
							}
							if ( ! empty( $invoice_data ) ) {
								$d              = strtotime( $invoice_data->date );
								$issue_date     = date( 'Y-m-d', $d );
								$payment_status = $invoice_data->payment_status;
							}
							if ( ! empty( $expense_data ) ) {
								$issue_date     = $expense_data->income_create_date;
								$payment_status = $expense_data->payment_status;
							}
							?>
							<label
								style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;"><?php echo esc_html__( 'Date', 'mjschool' ); ?>
							</label>: <label class="invoice_model_value"
								style="font-weight: 600;color: #333333;font-size: 16px !important;"><?php echo esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ); ?></label><br>
							<label
								style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;"><?php echo esc_html__( 'Status', 'mjschool' ); ?>
							</label>: <label class="invoice_model_value"
								style="font-weight: 600;color: #333333;font-size: 16px !important;">
							<?php
							if ( $payment_status === 'Paid' ) {
								echo "<span style='color:green;'>" . esc_attr__( 'Fully Paid', 'mjschool' ) . '</span>';
							}
							if ( $payment_status === 'Part Paid' ) {
								echo "<span style='color:#537ab7;'>" . esc_attr__( 'Partially Paid', 'mjschool' ) . '</span>';
							}
							if ( $payment_status === 'Unpaid' ) {
								echo "<span style='color:red;'>" . esc_attr__( 'Not Paid', 'mjschool' ) . '</span>';
							}
							?>
							</label>
						</td>
					</tr>
				</tbody>
			</table>
		<?php } ?>
	<h4 style="font-size: 16px;font-weight: 600;color: #333333;"> <?php esc_html_e( 'Invoice Entry', 'mjschool' ); ?></h4>
	<?php if ( $format === 1 ) { ?>
		<table class="table" width="100%" style="border-collapse: collapse; border: 1px solid black;">
			<thead style="background-color: #b8daff !important;border: 1px solid black;">
				<tr>
					<th style="font-weight: bold; color: #333; text-align: center; padding: 10px;background-color: #b8daff !important; text-align: !important;color: black !important;border: 1px solid black;width: 15%;"> Number</th>
					<th style="font-weight: bold; color: #333; text-align: center; padding: 10px;background-color: #b8daff !important; text-align: !important;color: black !important;border: 1px solid black;width: 20%;"> <?php esc_html_e( 'Date', 'mjschool' ); ?></th>
					<th style="font-weight: bold; color: #333; text-align: center; padding: 10px;background-color: #b8daff !important; text-align: !important;color: black !important;border: 1px solid black;"> <?php esc_html_e( 'Entry', 'mjschool' ); ?></th>
					<th style="font-weight: bold; color: #333; text-align: center; padding: 10px;background-color: #b8daff !important; text-align: !important;color: black !important;border: 1px solid black;"> <?php esc_html_e( 'Issued By', 'mjschool' ); ?></th>
					<th style="font-weight: bold; color: #333; text-align: center; padding: 10px;background-color: #b8daff !important; text-align: !important;color: black !important;border: 1px solid black;width: 15%;"> <?php echo esc_html__( 'Price', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )'; ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$id           = 1;
				$total_amount = 0;
				if ( ! empty( $income_data ) || ! empty( $expense_data ) ) {
					if ( ! empty( $expense_data ) ) {
						$income_data = $expense_data;
					}
					$patient_all_income = $obj_invoice->mjschool_get_onepatient_income_data( $income_data->supplier_name );
					if ( ! empty( $patient_all_income ) ) {
						foreach ( $patient_all_income as $result_income ) {
							$income_entries = json_decode( $result_income->entry );
							foreach ( $income_entries as $each_entry ) {
								$total_amount += $each_entry->amount;
								?>
								<tr>
									<td style="text-align: center; padding: 8px; border: 1px solid black; font-weight: normal;"><?php echo esc_html( $id++ ); ?></td>
									<td style="text-align: center; padding: 8px; border: 1px solid black; font-weight: normal;"> <?php echo esc_html( $result_income->income_create_date ); ?></td>
									<td style="text-align: center; padding: 8px; border: 1px solid black; font-weight: normal;"><?php echo esc_html( $each_entry->entry ); ?></td>
									<td style="text-align: center; padding: 8px; border: 1px solid black; font-weight: normal;"> <?php echo esc_html( mjschool_get_display_name( $result_income->create_by ) ); ?></td>
									<td style="text-align: center; padding: 8px; border: 1px solid black; font-weight: normal;"> <?php echo esc_html( number_format( $each_entry->amount, 2 ) ); ?></td>
								</tr>
								<?php
							}
						}
					}
				}
				if ( ! empty( $invoice_data ) ) {
					$total_amount = $invoice_data->amount;
					?>
					<tr>
						<td style="<?php esc_attr( $cell_style ); ?>"><?php echo esc_html( $id ); ?></td>
						<td style="<?php esc_attr( $cell_style ); ?>"><?php echo esc_html( date( 'Y-m-d', strtotime( $invoice_data->date ) ) ); ?></td>
						<td style="<?php esc_attr( $cell_style ); ?>"><?php echo esc_html( $invoice_data->payment_title ); ?></td>
						<td style="<?php esc_attr( $cell_style ); ?>"><?php echo esc_html( mjschool_get_display_name( $invoice_data->payment_reciever_id ) ); ?> </td>
						<td style="<?php esc_attr( $cell_style ); ?>"><?php echo esc_html( number_format( $invoice_data->amount, 2 ) ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
		<?php
	}
	if ( ! empty( $invoice_data ) ) {
		$grand_total     = $total_amount;
		$sub_total       = $invoice_data->fees_amount;
		$tax_amount      = $invoice_data->tax_amount;
		$discount_amount = $invoice_data->discount_amount;
		if ( ! empty( $invoice_data->tax ) ) {
			$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $invoice_data->tax ) );
		} else {
			$tax_name = '';
		}
		if ( $invoice_data->discount ) {
			$discount_name = mjschool_get_discount_name( $invoice_data->discount, $invoice_data->discount_type );
		} else {
			$discount_name = '';
		}
	}
	if ( ! empty( $income_data ) ) {
		if ( ! empty( $income_data->tax ) ) {
			$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $income_data->tax ) );
		} else {
			$tax_name = '';
		}
		if ( ! empty($income_data->discount) ) {
			$discount_name = mjschool_get_discount_name( $income_data->discount, $income_data->discount_type );
		} else {
			$discount_name = '';
		}
		$sub_total = 0;
		if ( ! empty( $income_data->entry ) ) {
			$all_income_entry = json_decode( $income_data->entry );
			foreach ( $all_income_entry as $one_entry ) {
				$sub_total += $one_entry->amount;
			}
		}
		if( ! empty( $income_data->discount_amount ) )
		{
			$discount_amount = $income_data->discount_amount;
		}
		$tax_amount      = $income_data->tax_amount;
		$grand_total     = $sub_total + $tax_amount;
	}
	?>
	<?php
	if ( $format === 1 ) {
		?>
		<div class="table-responsive mjschool-rtl-padding-left-40px mjschool-rtl-float-left-width-100px">
			<table class="table table-bordered" style="margin-top: 20px; width: 100%; border-collapse: collapse;margin-bottom: 0px !important;">
				<tbody>
					<tr>
						<th style="width: 85%; text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row">
							<?php echo esc_html__( 'Sub Total', 'mjschool' ) . ' :'; ?>
						</th>
						<td style="width: 15%; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;">
							<?php echo esc_html( number_format( $sub_total, 2, '.', '' ) ); ?>
						</td>
					</tr>
					<?php if ( isset( $discount_amount ) && ( $discount_amount ) != 0 ) { ?>
						<tr>
							<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row">
								<?php echo esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :'; ?>
							</th>
							<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;">
								<?php echo '-' . esc_html( number_format( $fees_detail_result->discount_amount, 2, '.', '' ) ); ?>
							</td>
						</tr>
					<?php } ?>
					<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) : ?>
						<tr>
							<th style="text-align: <?php echo is_rtl() ? 'left' : 'right'; ?>; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black;" scope="row">
								<?php echo esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :'; ?>
							</th>
							<td style="text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-weight: 600; border: 1px solid black;">
								<?php echo '+' . esc_html( number_format( $tax_amount, 2, '.', '' ) ); ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	} else {
		?>
		<table width="100%" border="0" <?php if ( is_rtl() ) { ?> class="mjschool_direction_rtl" <?php } ?>>
			<tbody>
				<tr>
					<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?>>
						<?php echo esc_attr__( 'Sub Total', 'mjschool' ) . '  :'; ?>
					</td>
					<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value">
						<?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ); ?>
					</td>
				</tr>
				<?php if ( isset( $discount_amount ) && ! empty( $discount_amount ) ) { ?>
					<tr>
						<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?>>
							<?php echo esc_attr__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_amount ) . ' )' . '  :'; ?>
						</td>
						<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value">
							<?php echo '-' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $tax_amount, 2, '.', '' ) ) ); ?>
						</td>
					</tr>
					<?php
				}
				?>
				<?php if ( isset( $tax_amount ) && ! empty( $tax_amount ) ) { ?>
					<tr>
						<td width="85%" class="mjschool-rtl-float-left_label mjschool-padding-bottom-15px mjschool-total-heading" <?php if ( is_rtl() ) { ?> align="left" <?php } else { ?> align="right" <?php } ?>>
							<?php echo esc_attr__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )' . '  :'; ?>
						</td>
						<td align="right" class="mjschool-rtl-width-15px mjschool-padding-bottom-15px mjschool-total-value">
							<?php echo '+' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $tax_amount, 2, '.', '' ) ) ); ?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	<?php } ?>
	<table style="margin-left: 52px; margin-top: 18px;">
		<tbody>
			<tr>
				<td width="66%"></td>
				<td>
					<table style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;">
						<tbody>
							<tr>
								<td style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;padding:10px">
									<h3> <?php esc_html_e( 'Grand Total', 'mjschool' ); ?> </h3>
								</td>
								<td style="background-color: <?php echo esc_attr( get_option( 'mjschool_system_color_code' ) ); ?>;color: #fff;padding:10px">
									<h3>
										<?php
										$formatted_amount = number_format( $grand_total, 2, '.', '' );
										$currency_symbol  = mjschool_get_currency_symbol(); // Use this if your project has a function to get the symbol.
										echo esc_html( "({$currency_symbol}){$formatted_amount}" );
										?>
									</h3>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="" style="border: 2px solid; width:100%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden;margin-top: 20px;">
		<!-- Teacher Signature (Middle) -->
		<div style="float: right; width: 33.33%; text-align: center;">
			<div>
				<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px;" />
			</div>
			<div style="border-top: 1px solid #000; width: 150px; margin: 5px auto;"></div>
			<div style="margin-top: 5px;">
				<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
			</div>
		</div>
	</div>
	<?php
}
// phpcs:disable
/**
 * Initializes the invoice print process for student payments.
 *
 * Loads required stylesheets, triggers the browser print dialog,
 * and renders the invoice content when the `print` and `page` parameters match.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_print_invoice() {
	// Check with isset() for all required parameters
	if ( ! isset( $_REQUEST['print'] ) || ! isset( $_REQUEST['page'] ) ) {
		return;
	}
	
	$print = sanitize_text_field( wp_unslash( $_REQUEST['print'] ) );
	$page  = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
	
	if ( $print !== 'print' || $page !== 'mjschool_payment' ) {
		return;
	}
	
	// Enqueue styles
	if ( is_rtl() ) {
		wp_enqueue_style( 'bootstrap-rtl', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.rtl.min.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-custome-rtl', plugins_url( '/assets/css/mjschool-custome-rtl.css', __FILE__ ) );
		wp_enqueue_style( 'mjschool-newdesign-rtl', plugins_url( '/assets/css/mjschool-new-design-rtl.css', __FILE__ ) );
	}
	
	wp_enqueue_style( 'mjschool-style', plugins_url( '/assets/css/mjschool-style.css', __FILE__ ) );
	wp_enqueue_style( 'mjschool-new-design', plugins_url( '/assets/css/mjschool-smgt-new-design.css', __FILE__ ) );
	wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/third-party-css/bootstrap/bootstrap.min.css', __FILE__ ) );
	wp_enqueue_style( 'buttons-dataTables', plugins_url( '/assets/css/third-party-css/buttons.dataTables.min.css', __FILE__ ) );
	wp_enqueue_style( 'mjschool-poppins-fontfamily', plugins_url( '/assets/css/mjschool-popping-font.css', __FILE__ ) );
	
	// Trigger for JS
	echo '<div id="mjschool-print-invoice-trigger" data-print="1"></div>';
	
	// Validate invoice_id exists
	if ( ! isset( $_REQUEST['invoice_id'] ) ) {
		wp_die( esc_html__( 'Invoice ID is required.', 'mjschool' ) );
	}
	
	$invoice_id = sanitize_text_field( wp_unslash( $_REQUEST['invoice_id'] ) );
	
	mjschool_student_invoice_print( mjschool_decrypt_id( $invoice_id ) );
	exit;
}
add_action( 'init', 'mjschool_print_invoice' );

/**
 * Creates and updates all required database tables for the MJ School plugin.
 *
 * This function runs on plugin installation or update. It registers multiple
 * custom tables including attendance, exams, grades, events, holidays, marks,
 * classes, rooms, certificates, fees, payments and etc. It also performs schema
 * updates when required (e.g., adding missing columns or modifying types).
 *
 * Uses the WordPress `dbDelta()` function to safely create or update table
 * structures and performs direct schema modification queries where needed.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function mjschool_install_tables() {
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	global $wpdb;
	$table_attendence = $wpdb->prefix . 'mjschool_attendence';
	$sql              = 'CREATE TABLE IF NOT EXISTS ' . $table_attendence . ' (
	`attendence_id` int(50) NOT NULL AUTO_INCREMENT,
	`user_id` int(50) NOT NULL,
	`class_id` int(50) NOT NULL,
	`attend_by` int(11) NOT NULL,
	`attendence_date` date NOT NULL,
	`status` varchar(50) NOT NULL,
	`role_name` varchar(20) NOT NULL,
	`comment` text NOT NULL,
	PRIMARY KEY (`attendence_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_attendence = $wpdb->prefix . 'mjschool_attendence';
	$attendence_type = 'attendence_type';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $attendence_type, $wpdb->get_col( "DESC {$table_attendence}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_attendence} ADD {$attendence_type} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		
		if ( false === $result ) {
			error_log( "MJSchool: Failed to add column {$attendence_type} to table {$table_attendence}" );
		}
	}
	$table_exam = $wpdb->prefix . 'mjschool_exam';
	$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_exam . ' (
	`exam_id` int(11) NOT NULL AUTO_INCREMENT,
	`exam_name` varchar(200) NOT NULL,
	`exam_start_date` date NOT NULL,
	`exam_end_date` date NOT NULL,
	`exam_comment` text NOT NULL,
	`created_date` datetime NOT NULL,
	`modified_date` datetime NOT NULL,
	`exam_creater_id` int(11) NOT NULL,
	`contributions` varchar(10) NULL,
	`contributions_data` text NULL,
	PRIMARY KEY (`exam_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$subject_data = 'subject_data';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $subject_data, $wpdb->get_col( "DESC {$table_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_exam} ADD {$subject_data} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$daynamic_certificate = $wpdb->prefix . 'mjschool_daynamic_certificate';
	$sql                  = "CREATE TABLE IF NOT EXISTS {$daynamic_certificate} (
	id INT(11) NOT NULL AUTO_INCREMENT,
	certificate_name VARCHAR(100) NOT NULL,
	certificate_content LONGTEXT NOT NULL,
	created_date DATETIME NOT NULL,
	PRIMARY KEY (id),
	UNIQUE KEY certificate_name (certificate_name)
	) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
	dbDelta( $sql );
	$contributions      = 'contributions';
	$contributions_data = 'contributions_data';	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $contributions, $wpdb->get_col( "DESC {$table_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_exam} ADD {$contributions} varchar(10) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $contributions_data, $wpdb->get_col( "DESC {$table_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_exam} ADD {$contributions_data} text NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE {$table_exam} MODIFY {$contributions} varchar(10) NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_grade = $wpdb->prefix . 'mjschool_grade';
	$sql         = 'CREATE TABLE IF NOT EXISTS ' . $table_grade . ' (
	`grade_id` int(11) NOT NULL AUTO_INCREMENT,
	`grade_name` varchar(20) NOT NULL,
	`grade_point` float NOT NULL,
	`mark_from` tinyint(3) NOT NULL,
	`mark_upto` tinyint(3) NOT NULL,
	`grade_comment` text NOT NULL,
	`created_date` datetime NOT NULL,
	`creater_id` int(11) NOT NULL,
	PRIMARY KEY (`grade_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mark_from = 'mark_from';
	$mark_upto = 'mark_upto';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE $table_grade MODIFY $mark_from float NOT NULL" );
	$wpdb->query( "ALTER TABLE $table_grade MODIFY $mark_upto float NOT NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_event = $wpdb->prefix . 'mjschool_event'; // register grade table.
	$sql         = 'CREATE TABLE IF NOT EXISTS ' . $table_event . ' (
	`event_id` int(11) NOT NULL AUTO_INCREMENT,
	`event_title` varchar(100) NOT NULL,
	`description` text NOT NULL,
	`start_date` date NOT NULL,
	`start_time` varchar(100) NOT NULL,
	`end_date` date NOT NULL,
	`end_time` varchar(100) NOT NULL,
	`event_doc` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_date` date NOT NULL,
	PRIMARY KEY (`event_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_hall = $wpdb->prefix . 'mjschool_hall';
	$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_hall . ' (
	`hall_id` int(11) NOT NULL AUTO_INCREMENT,
	`hall_name` varchar(200) NOT NULL,
	`number_of_hall` int(11) NOT NULL,
	`hall_capacity` int(11) NOT NULL,
	`description` text NOT NULL,
	`date` datetime NOT NULL,
	PRIMARY KEY (`hall_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_exprience_letter = $wpdb->prefix . 'mjschool_certificate';
	$sql                    = 'CREATE TABLE IF NOT EXISTS ' . $table_exprience_letter . ' (
	`id` int(20) NOT NULL AUTO_INCREMENT,
	`student_id` int(20) NOT NULL,
	`certificate_type` varchar(150) NOT NULL,
	`certificate_content` longtext,
	`created_by` int(20) NOT NULL,
	`created_at` timestamp,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$certificate_id = 'certificate_id';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $certificate_id, $wpdb->get_col( "DESC {$table_exprience_letter}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_exprience_letter} ADD {$certificate_id} int(20) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_holiday = $wpdb->prefix . 'mjschool_holiday'; // register holiday table.
	$sql           = 'CREATE TABLE IF NOT EXISTS ' . $table_holiday . ' (
	`holiday_id` int(11) NOT NULL AUTO_INCREMENT,
	`holiday_title` varchar(200) NOT NULL,
	`description` text NOT NULL,
	`date` date NOT NULL,
	`end_date` date NOT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`holiday_id`)
	) DEFAULT CHARSET=utf8 ';
	dbDelta( $sql );
	$table_marks = $wpdb->prefix . 'mjschool_marks'; // register marks table.
	$sql         = 'CREATE TABLE IF NOT EXISTS ' . $table_marks . ' (
	`mark_id` bigint(20) NOT NULL AUTO_INCREMENT,
	`exam_id` int(11) NOT NULL,
	`class_id` int(11) NOT NULL,
	`subject_id` int(11) NOT NULL,
	`marks` tinyint(3) NOT NULL,
	`class_marks` text NOT NULL,
	`contributions` varchar(25) NOT NULL,
	`attendance` tinyint(4) NOT NULL,
	`grade_id` int(11) NOT NULL,
	`student_id` int(11) NOT NULL,
	`marks_comment` text NOT NULL,
	`created_date` datetime NOT NULL,
	`modified_date` datetime NOT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`mark_id`)
	) DEFAULT CHARSET=utf8 ';
	dbDelta( $sql );
	$class_marks = 'class_marks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $class_marks, $wpdb->get_col( "DESC {$table_marks}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_marks} ADD {$class_marks} text NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$contributions = 'contributions';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $contributions, $wpdb->get_col( "DESC {$table_marks}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_marks} ADD {$contributions} varchar(25) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_mjschool_class = $wpdb->prefix . 'mjschool_class'; // register smgt_class table.
	$sql              = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_class . ' (
	`class_id` int(11) NOT NULL AUTO_INCREMENT,
	`class_name` varchar(100) NOT NULL,
	`class_num_name` varchar(5) NOT NULL,
	`class_section` varchar(50) NOT NULL,
	`class_capacity` tinyint(4) NOT NULL,
	`creater_id` int(11) NOT NULL,
	`created_date` datetime NOT NULL,
	`modified_date` datetime NOT NULL,
	PRIMARY KEY (`class_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$class_description = 'class_description';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $class_description, $wpdb->get_col( "DESC {$table_mjschool_class}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_class} ADD {$class_description} TEXT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}	
	$academic_year = 'academic_year';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $academic_year, $wpdb->get_col( "DESC {$table_mjschool_class}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_class} ADD {$academic_year} VARCHAR(20) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_class_room = $wpdb->prefix . 'mjschool_class_room';
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_mjschool_class_room . " (
	`room_id` int(11) NOT NULL AUTO_INCREMENT,
	`room_name` varchar(255) NOT NULL,
	`class_id` text NOT NULL,
	`room_type` varchar(255) NOT NULL,
	`room_capacity` int(11) NULL,
	`created_date` datetime NOT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`room_id`)
	) DEFAULT CHARSET=utf8";
	dbDelta($sql);
	$subid = 'sub_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $subid, $wpdb->get_col( "DESC {$table_mjschool_class_room}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_class_room} ADD {$subid} TEXT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_custom_class = $wpdb->prefix . 'mjschool_custom_class'; //register subject table.
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_custom_class . " (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`sub_id` varchar(255) NOT NULL,
	`class_id` int(11) NOT NULL,
	`student_id` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	)  DEFAULT CHARSET=utf8";
	dbDelta($sql);
	$subject_ids = 'sub_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $subject_ids, $wpdb->get_col( "DESC {$table_custom_class}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_custom_class} ADD {$subject_ids} TEXT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees'; // register smgt_class table.
	$sql                 = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_fees . ' (
	`fees_id` int(11) NOT NULL AUTO_INCREMENT,
	`fees_title_id` bigint(20) NOT NULL,
	`class_id` int(11) NOT NULL,
	`fees_amount` float NOT NULL,
	`description` text NOT NULL,
	`created_date` datetime NOT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`fees_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$class_id = 'class_id';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees} MODIFY {$class_id} varchar(20) NOT NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_mjschool_taxes = $wpdb->prefix . 'mjschool_taxes';
	$sql              = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_taxes . ' (
	`tax_id` int(11) NOT NULL AUTO_INCREMENT,
	`tax_title` varchar(255) NOT NULL,
	`tax_value` double NOT NULL,
	`created_date` date NOT NULL,
	PRIMARY KEY (`tax_id`)
	) DEFAULT CHARSET=utf8';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$wpdb->query( $sql );
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment'; // register smgt_class table.
	$sql                         = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_fees_payment . ' (
	`fees_pay_id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id` int(11) NOT NULL,
	`student_id` bigint(20) NOT NULL,
	`fees_id` varchar(255) NOT NULL,
	`total_amount` float NOT NULL,
	`fees_paid_amount` float NOT NULL,
	`payment_status` tinyint(4) NOT NULL,
	`description` text NOT NULL,
	`start_year` varchar(20) NOT NULL,
	`end_year` varchar(20) NOT NULL,
	`paid_by_date` date NOT NULL,
	`created_date` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	PRIMARY KEY (`fees_pay_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$tax = 'tax';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$tax} varchar(100) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$tax_amount = 'tax_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax_amount, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$tax_amount} double DEFAULT 0" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$discount = 'discount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $discount, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$discount} varchar(20) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$discount_type = 'discount_type';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $discount_type, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$discount_type} varchar(10) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$fees_amount = 'fees_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $fees_amount, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$fees_amount} float" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$discount_amount = 'discount_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $discount_amount, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$discount_amount} double DEFAULT 0" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$invoice_status = 'invoice_status';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $invoice_status, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$invoice_status} VARCHAR(20) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$invoice_id = 'invoice_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $invoice_id, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$invoice_id} int(11) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$mjschool_fees_payment_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring'; // register smgt_class table.
	$sql                             = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_fees_payment_recurring . ' (
	`recurring_id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id`  int(11) NOT NULL,
	`section_id` int(11) NOT NULL,
	`student_id` text NOT NULL,
	`fees_id` text NOT NULL,
	`total_amount` float NOT NULL,
	`description` text NULL,
	`start_year` date NOT NULL,
	`end_year` date NOT NULL,
	`recurring_type` varchar(20) NOT NULL,
	`recurring_enddate` date NOT NULL,
	`status` varchar(20) NOT NULL,
	`created_date` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	PRIMARY KEY (`recurring_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$tax = 'tax';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax, $wpdb->get_col( "DESC {$mjschool_fees_payment_recurring}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE $mjschool_fees_payment_recurring ADD $tax varchar(100) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$tax_amount = 'tax_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax_amount, $wpdb->get_col( "DESC {$mjschool_fees_payment_recurring}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE $mjschool_fees_payment_recurring ADD $tax_amount double DEFAULT 0" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$fees_amount = 'fees_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $fees_amount, $wpdb->get_col( "DESC {$mjschool_fees_payment_recurring}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE $mjschool_fees_payment_recurring ADD $fees_amount float " );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history'; // register smgt_class table.
	$sql                                = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_fee_payment_history . ' (
	`payment_history_id` bigint(20) NOT NULL AUTO_INCREMENT,
	`fees_pay_id` int(11) NOT NULL,
	`amount` float NOT NULL,
	`payment_method` varchar(50) NOT NULL,
	`paid_by_date` date NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`trasaction_id` varchar(50) NOT NULL,
	`payment_note` text NULL,
	PRIMARY KEY (`payment_history_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$payment_note = 'payment_note';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $payment_note, $wpdb->get_col( "DESC {$table_mjschool_fee_payment_history}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE $table_mjschool_fee_payment_history ADD $payment_note text NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_message = $wpdb->prefix . 'mjschool_message'; // register mjschool_message table.
	$sql           = 'CREATE TABLE IF NOT EXISTS ' . $table_message . ' (
	`message_id` int(11) NOT NULL AUTO_INCREMENT,
	`sender` int(11) NOT NULL,
	`receiver` int(11) NOT NULL,
	`date` datetime NOT NULL,
	`subject` varchar(150) NOT NULL,
	`message_body` text NOT NULL,
	`status` int(11) NOT NULL,
	`post_id` int(11) NOT NULL,
	PRIMARY KEY (`message_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_payment = $wpdb->prefix . 'mjschool_payment'; // register mjschool_payment table.
	$sql                    = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_payment . ' (
	`payment_id` int(11) NOT NULL AUTO_INCREMENT,
	`student_id` int(11) NOT NULL,
	`class_id` int(11) NOT NULL,
	`payment_title` varchar(100) NOT NULL,
	`tax` varchar(100) NULL,
	`tax_amount` double DEFAULT 0,
	`fees_amount` float NOT NULL,
	`description` text NOT NULL,
	`amount` int(11) NOT NULL,
	`payment_status` varchar(10) NOT NULL,
	`date` datetime NOT NULL,
	`payment_reciever_id` int(11) NOT NULL,
	PRIMARY KEY (`payment_id`)
	) DEFAULT CHARSET=utf8 AUTO_INCREMENT=7';
	dbDelta( $sql );
	$tax = 'tax';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax, $wpdb->get_col( "DESC {$table_mjschool_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_payment} ADD {$tax} varchar(100) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$tax_amount = 'tax_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $tax_amount, $wpdb->get_col( "DESC {$table_mjschool_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_payment} ADD {$tax_amount} double DEFAULT 0" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$fees_amount = 'fees_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $fees_amount, $wpdb->get_col( "DESC {$table_mjschool_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_payment} ADD {$fees_amount} float " );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_time_table = $wpdb->prefix . 'mjschool_time_table'; // register mjschool_time_table table.
	$sql                       = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_time_table . ' (
	`route_id` int(11) NOT NULL AUTO_INCREMENT,
	`subject_id` int(11) NOT NULL,
	`teacher_id` int(11) NOT NULL,
	`class_id` int(11) NOT NULL,
	`start_time` varchar(10) NOT NULL,
	`end_time` varchar(10) NOT NULL,
	`weekday` tinyint(4) NOT NULL,
	PRIMARY KEY (`route_id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$teacher_id       = 'teacher_id';
	$multiple_teacher = 'multiple_teacher';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->query( "ALTER TABLE {$table_mjschool_time_table} MODIFY {$teacher_id} text NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $multiple_teacher, $wpdb->get_col( "DESC {$table_mjschool_time_table}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_time_table} ADD {$multiple_teacher} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$room_id = 'room_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if (!in_array($room_id, $wpdb->get_col( "DESC {$table_mjschool_time_table}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_time_table} ADD {$room_id} int(11) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_subject = $wpdb->prefix . 'mjschool_subject'; // register subject table.
	$sql           = 'CREATE TABLE IF NOT EXISTS ' . $table_subject . ' (
	`subid` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`sub_name` varchar(255) NOT NULL,
	`teacher_id` int(11) NOT NULL,
	`class_id` int(11) NOT NULL,
	`author_name` varchar(255) NOT NULL,
	`edition` varchar(255) NOT NULL,
	`syllabus` varchar(255) DEFAULT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`subid`)
	)  DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$subject_studentid = 'selected_students';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if (!in_array($subject_studentid, $wpdb->get_col( "DESC {$table_subject}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_subject} ADD {$subject_studentid} TEXT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$subject_credit = 'subject_credit';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if (!in_array($subject_credit, $wpdb->get_col( "DESC {$table_subject}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_subject} ADD {$subject_credit} varchar(255) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_transport = $wpdb->prefix . 'mjschool_transport'; // register transport table.
	$sql             = 'CREATE TABLE IF NOT EXISTS ' . $table_transport . " (
	`transport_id` int(11) NOT NULL AUTO_INCREMENT,
	`route_name` varchar(200) NOT NULL,
	`number_of_vehicle` int(11) NOT NULL,
	`vehicle_reg_num` varchar(50) NOT NULL,
	`smgt_user_avatar` varchar(5000) NOT NULL,
	`driver_name` varchar(100) NOT NULL,
	`driver_phone_num` varchar(15) NOT NULL,
	`driver_address` text NOT NULL,
	`route_description` text NOT NULL,
	`route_fare` int(11) NOT NULL,
	`status` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`transport_id`)
	) DEFAULT CHARSET=utf8";
	dbDelta( $sql );
	$table_assign_transport = $wpdb->prefix . 'mjschool_assign_transport'; // register assign transport table.
	$sql                    = 'CREATE TABLE IF NOT EXISTS ' . $table_assign_transport . ' (
	`assign_transport_id` int(11) NOT NULL AUTO_INCREMENT,
	`transport_id` int(11) NOT NULL,
	`route_name` varchar(200) NOT NULL,
	`route_user` text NOT NULL,
	`route_fare` int(11) NOT NULL,
	`created_by` int(11) NOT NULL,
	PRIMARY KEY (`assign_transport_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_income_expense = $wpdb->prefix . 'mjschool_income_expense'; // register transport table.
	$sql                       = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_income_expense . ' (
	`income_id` int(11) NOT NULL AUTO_INCREMENT,
	`invoice_type` varchar(50) NOT NULL,
	`class_id` int(11) NOT NULL,
	`supplier_name` varchar(100) NOT NULL,
	`entry` text NOT NULL,
	`payment_status` varchar(50) NOT NULL,
	`create_by` int(11) NOT NULL,
	`income_create_date` date NOT NULL,
	PRIMARY KEY (`income_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_audit_log = $wpdb->prefix . 'mjschool_audit_log'; // register transport table.
	$sql                  = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_audit_log . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`audit_action` text NOT NULL,
	`user_id` int(11) NULL,
	`action` text NOT NULL,
	`ip_address` text NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_at` date NOT NULL,
	`date_time` datetime NOT NULL,
	`deleted_status` boolean NOT NULL,
	`updated_by` 	int(11) NULL,
	`updated_date` datetime NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log'; // register transport table.
	$sql                      = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_migration_log . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`ip_address` text NOT NULL,
	`created_by` int(11) NOT NULL,
	`current_class` int(11) NOT NULL,
	`next_class` int(11) NOT NULL,
	`exam_name` int(11) NULL,
	`pass_mark` int(11) NULL,
	`created_at` date NOT NULL,
	`date_time` datetime NOT NULL,
	`deleted_status` boolean NOT NULL,
	`pass_students` text NULL,
	`fail_students` text NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_cron_reminder_log = $wpdb->prefix . 'mjschool_cron_reminder_log'; // register transport table.
	$sql                              = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_cron_reminder_log . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`student_id` text NOT NULL,
	`fees_pay_id` int(11) NOT NULL,
	`date_time` date NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_audit_log = $wpdb->prefix . 'mjschool_audit_log';
	$module               = 'module';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $module, $wpdb->get_col( "DESC {$table_mjschool_audit_log}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$table_mjschool_audit_log} ADD {$module} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_user_log = $wpdb->prefix . 'mjschool_user_log'; // register transport table.
	$sql                 = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_user_log . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_login` text NOT NULL,
	`role` text NOT NULL,
	`ip_address` text NOT NULL,
	`created_at` date NOT NULL,
	`date_time` datetime NOT NULL,
	`deleted_status` boolean NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_library_book = $wpdb->prefix . 'mjschool_library_book'; // register smgt_class table.
	$sql                         = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_library_book . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`ISBN` varchar(50) NOT NULL,
	`book_name` varchar(200) CHARACTER SET utf8 NOT NULL,
	`author_name` varchar(100) CHARACTER SET utf8 NOT NULL,
	`cat_id` int(11) NOT NULL,
	`rack_location` int(11) NOT NULL,
	`price` varchar(10) NOT NULL,
	`quentity` int(11) NOT NULL,
	`description` text CHARACTER SET utf8 NOT NULL,
	`added_by` int(11) NOT NULL,
	`added_date` varchar(20) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_library_book_issue = $wpdb->prefix . 'mjschool_library_book_issue'; // register smgt_class table.
	$sql                               = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_library_book_issue . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id` int(11) NOT NULL,
	`student_id` int(11) NOT NULL,
	`cat_id` int(11) NOT NULL,
	`book_id` int(11) NOT NULL,
	`issue_date` varchar(20) NOT NULL,
	`end_date` varchar(20) NOT NULL,
	`actual_return_date` varchar(20) NOT NULL,
	`period` int(11) NOT NULL,
	`fine` varchar(20) NOT NULL,
	`status` varchar(50) NOT NULL,
	`comment` text NULL,
	`issue_by` int(11) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mjschool_sub_attendance = $wpdb->prefix . 'mjschool_sub_attendance'; // register smgt_class table.
	$sql                     = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_sub_attendance . ' (
	`attendance_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`class_id` int(11) NOT NULL,
	`section_id` int(11) NOT NULL,
	`sub_id` int(11) NOT NULL,
	`attend_by` int(11) NOT NULL,
	`attendance_date` date NOT NULL,
	`status` varchar(50) NOT NULL,
	`role_name` varchar(50) NOT NULL,
	`categories` varchar(10) NULL,
	`comment` text NOT NULL,
	PRIMARY KEY (`attendance_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$categories = 'categories';
	$section_id = 'section_id';
	$type       = 'attendence_type';
	$sub_id     = 'sub_id';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $categories, $wpdb->get_col( "DESC {$mjschool_sub_attendance}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} ADD {$categories} varchar(10) DEFAULT( 'subject' )" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$mjschool_sub_attendance}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} ADD {$section_id} int(11) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $type, $wpdb->get_col( "DESC {$mjschool_sub_attendance}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} ADD {$type} varchar(10) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} MODIFY {$sub_id} int(11) NULL" );
	$result = $wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} MODIFY {$section_id} int(11) NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$mjschool_homework = $wpdb->prefix . 'mjschool_homework'; // homework table.
	$sql               = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_homework . ' (
	`homework_id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(250) NOT NULL,
	`class_name` int(11) NOT NULL,
	`section_id` int(11) NOT NULL,
	`subject` int(11) NOT NULL,
	`content` text NOT NULL,
	`submition_date` date NOT NULL,
	`createdby` int(11) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`homework_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_document = $wpdb->prefix . 'mjschool_document'; // document table.
	$sql           = 'CREATE TABLE IF NOT EXISTS ' . $smgt_document . ' (
	`document_id` int(11) NOT NULL AUTO_INCREMENT,
	`document_for` varchar(50) NOT NULL,
	`class_id` varchar(255) NOT NULL,
	`section_id` varchar(255) NOT NULL,
	`student_id` varchar(255) NOT NULL,
	`document_content` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`createdby` int(11) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`document_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mjschool_student_homework = $wpdb->prefix . 'mjschool_student_homework'; // Student Homework table.
	$sql                       = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_student_homework . ' (
	`stu_homework_id` int(50) NOT NULL AUTO_INCREMENT,
	`homework_id` int(11) NOT NULL,
	`student_id` int(11) NOT NULL,
	`status` tinyint(4) NOT NULL,
	`uploaded_date` datetime DEFAULT NULL,
	`file` text NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`stu_homework_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$document_for = 'document_for';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $document_for, $wpdb->get_col( "DESC {$smgt_document}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$smgt_document} ADD {$document_for} varchar(50) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$review_file = 'review_file';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $review_file, $wpdb->get_col( "DESC {$mjschool_student_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_student_homework} ADD {$review_file} text DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$obtain_marks = 'obtain_marks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $obtain_marks, $wpdb->get_col( "DESC {$mjschool_student_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_student_homework} ADD {$obtain_marks} tinyint(3) DEFAULT NULL AFTER review_file" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$evaluate_date = 'evaluate_date';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $evaluate_date, $wpdb->get_col( "DESC {$mjschool_student_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_student_homework} ADD {$evaluate_date} datetime DEFAULT NULL AFTER obtain_marks" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$student_comment = 'student_comment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $student_comment, $wpdb->get_col( "DESC {$mjschool_student_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_student_homework} ADD {$student_comment} text DEFAULT NULL AFTER evaluate_date" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$teacher_comment = 'teacher_comment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $teacher_comment, $wpdb->get_col( "DESC {$mjschool_student_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->query( "ALTER TABLE {$mjschool_student_homework} ADD {$teacher_comment} text DEFAULT NULL AFTER student_comment" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies'; // register smgt_class table.
	$sql                      = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_message_replies . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`message_id` int(11) NOT NULL,
	`sender_id` int(11) NOT NULL,
	`receiver_id` int(11) NOT NULL,
	`message_comment` text NOT NULL,
	`message_attachment` text,
	`status` int(11),
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mjschool_class_section = $wpdb->prefix . 'mjschool_class_section'; // register smgt_class table.
	$sql                    = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_class_section . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id` int(11) NOT NULL,
	`section_name` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_teacher_sub = $wpdb->prefix . 'mjschool_teacher_subject';
	$sql              = 'CREATE TABLE IF NOT EXISTS ' . $smgt_teacher_sub . ' (
	`teacher_subject_id` int(11) NOT NULL AUTO_INCREMENT,
	`teacher_id` bigint(20) NOT NULL,
	`subject_id` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	PRIMARY KEY (`teacher_subject_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mjschool_notification = $wpdb->prefix . 'mjschool_notification';
	$sql                   = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_notification . ' (
	`notification_id` int(11) NOT NULL AUTO_INCREMENT,
	`student_id` int(11) NOT NULL,
	`title` varchar(500) DEFAULT NULL,
	`message` varchar(5000) DEFAULT NULL,
	`device_token` varchar(255) DEFAULT NULL,
	`device_type` tinyint(4) NOT NULL,
	`bicon` int(11) DEFAULT NULL,
	`created_date` date DEFAULT NULL,
	`created_by` int(11) DEFAULT NULL,
	PRIMARY KEY (`notification_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_exam_time_table = $wpdb->prefix . 'mjschool_exam_time_table'; // register smgt_exam_time_table.
	$sql                        = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_exam_time_table . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id` int(11) NOT NULL,
	`exam_id` int(11) NOT NULL,
	`subject_id` int(11) NOT NULL,
	`exam_date` date NOT NULL,
	`start_time`  text NOT NULL,
	`end_time`  text NOT NULL,
	`created_date` date NOT NULL,
	`created_by`  int(11) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt'; // register mjschool_exam_hall_receipt.
	$sql                              = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_exam_hall_receipt . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`exam_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`hall_id` int(11) NOT NULL,
	`exam_hall_receipt_status` int(11) NOT NULL,
	`created_date` date NOT NULL,
	`created_by`  int(11) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel'; // register smgt_hostel.
	$sql              = 'CREATE TABLE IF NOT EXISTS ' . $smgt_mjschool_hostel . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostel_name` varchar(255) NOT NULL,
	`hostel_type` varchar(255) NOT NULL,
	`Description` text NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	`updated_by` bigint(20) NOT NULL,
	`updated_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_mjschool_room = $wpdb->prefix . 'mjschool_room'; // register smgt_room.
	$sql            = 'CREATE TABLE IF NOT EXISTS ' . $smgt_mjschool_room . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`room_unique_id` varchar(20) NOT NULL,
	`hostel_id` int(11) NOT NULL,
	`room_status` int(11) NOT NULL,
	`room_category` int(11) NOT NULL,
	`beds_capacity` int(11) NOT NULL,
	`room_description` text NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	`updated_by` bigint(20) NOT NULL,
	`updated_date` datetime NOT NULL,
	`facilities` text NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$facilities = 'facilities';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $facilities, $wpdb->get_col( "DESC {$smgt_mjschool_room}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$smgt_mjschool_room} ADD {$facilities} text DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$smgt_mjschool_beds = $wpdb->prefix . 'mjschool_beds'; // register smgt_beds.
	$sql            = 'CREATE TABLE IF NOT EXISTS ' . $smgt_mjschool_beds . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`bed_unique_id` varchar(20) NOT NULL,
	`room_id` int(11) NOT NULL,
	`bed_status` int(11) NOT NULL,
	`bed_description` text NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	`updated_by` bigint(20) NOT NULL,
	`updated_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds'; // register smgt_beds.
	$sql                   = 'CREATE TABLE IF NOT EXISTS ' . $smgt_mjschool_assign_beds . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`hostel_id` int(11) NOT NULL ,
	`room_id` int(11) NOT NULL,
	`bed_id` int(11) NOT NULL,
	`bed_unique_id` varchar(20) NOT NULL,
	`student_id` int(11) NOT NULL,
	`assign_date` datetime NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_custom_field = $wpdb->prefix . 'mjschool_custom_field';
	$sql                = 'CREATE TABLE IF NOT EXISTS ' . $table_custom_field . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`form_name` varchar(255),
	`field_type` varchar(100) NOT NULL,
	`field_label` varchar(100) NOT NULL,
	`field_visibility` int(10),
	`field_validation` varchar(100),
	`created_by` 	int(11),
	`created_at` datetime NOT NULL,
	`updated_by` 	int(11),
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`)
	)DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$show_in_table = 'show_in_table';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	if ( ! in_array( $show_in_table, $wpdb->get_col( "DESC {$table_custom_field}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_custom_field} ADD {$show_in_table} varchar(255) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_csv_log = $wpdb->prefix . 'mjschool_csv_log'; // Correct variable name.
	$sql           = "CREATE TABLE IF NOT EXISTS $table_csv_log (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`error_log` varchar(255) NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_at` datetime NOT NULL,
	`module` varchar(40) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$wpdb->query( $sql );
	$status_column = 'status';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $status_column, $wpdb->get_col( "DESC {$table_csv_log}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_csv_log} ADD {$status_column} varchar(50) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_custom_field_dropdown_metas = $wpdb->prefix . 'mjschool_custom_field_dropdown_metas';
	$sql                               = 'CREATE TABLE IF NOT EXISTS ' . $table_custom_field_dropdown_metas . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`custom_fields_id` int(11) NOT NULL,
	`option_label` varchar(255) NOT NULL,
	`created_by` 	int(11),
	`created_at` datetime NOT NULL,
	`updated_by` 	int(11),
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
	$sql                      = 'CREATE TABLE IF NOT EXISTS ' . $table_custom_field_metas . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`module` varchar(100) NOT NULL,
	`module_record_id` int(11) NOT NULL,
	`custom_fields_id` int(11) NOT NULL,
	`field_value` text,
	`created_at` datetime NOT NULL,
	`updated_at` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$mjschool_check_status = $wpdb->prefix . 'mjschool_check_status';
	$sql                   = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_check_status . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`type` varchar(50) NULL,
	`user_id` int(11) NOT NULL,
	`type_id` int(11) NOT NULL,
	`status` int(11) NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$smgt_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
	$sql               = 'CREATE TABLE IF NOT EXISTS ' . $smgt_zoom_meeting . ' (
	`meeting_id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`route_id` int(11) NOT NULL,
	`zoom_meeting_id` varchar(50) NOT NULL,
	`uuid` varchar(100) NOT NULL,
	`class_id` int(11) NOT NULL,
	`section_id` int(11) NULL,
	`subject_id` int(11) NOT NULL,
	`teacher_id` int(11) NOT NULL,
	`weekday_id` int(11) NOT NULL,
	`password` varchar(50) NULL,
	`agenda` varchar(2000) NULL,
	`start_date` date NOT NULL,
	`end_date` date NOT NULL,
	`meeting_join_link` varchar(1000) NOT NULL,
	`meeting_start_link` varchar(1000) NOT NULL,
	`created_by` 	int(11),
	`created_date` datetime NOT NULL,
	`updated_by` 	int(11),
	`updated_date` datetime NULL,
	PRIMARY KEY (`meeting_id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	$table_mjschool_reminder_zoom_meeting_mail_log = $wpdb->prefix . 'mjschool_reminder_zoom_meeting_mail_log';
	$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_mjschool_reminder_zoom_meeting_mail_log . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`user_id` int(11) NOT NULL,
	`meeting_id` int(11) NOT NULL,
	`class_id` varchar(20) NOT NULL,
	`alert_date` date NOT NULL,
	PRIMARY KEY (`id`)
	)DEFAULT CHARSET=utf8';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$wpdb->query( $sql );
	$mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class'; // register smgt_class table.
	$sql                    = 'CREATE TABLE IF NOT EXISTS ' . $mjschool_teacher_class . ' (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`teacher_id` bigint(20) NOT NULL,
	`class_id` int(11) NOT NULL,
	`created_by` bigint(20) NOT NULL,
	`created_date` datetime NOT NULL,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	// ----  create Merge_exam Setting Table. ----//
	$exam_merge_settings = $wpdb->prefix . 'mjschool_exam_merge_settings';
	$sql                 = 'CREATE TABLE IF NOT EXISTS ' . $exam_merge_settings . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`class_id` int(11) NOT NULL,
	`section_id` int(11) NULL,
	`merge_name` varchar(100) NOT NULL,
	`merge_config` text NOT NULL,
	`created_by` int(11) NOT NULL,
	`status` varchar(10) NOT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8';
	dbDelta( $sql );
	// ----  create Leave tables. ----//
	$smgt_leave = $wpdb->prefix . 'mjschool_leave';
	$sql        = 'CREATE TABLE IF NOT EXISTS ' . $smgt_leave . ' (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`student_id` int(11) NOT NULL,
	`leave_type` int(11) NOT NULL,
	`leave_duration` varchar(50) NOT NULL,
	`start_date` varchar(50) NOT NULL,
	`end_date` varchar(50) NOT NULL,
	`reason` text NOT NULL,
	`status` varchar(50) NOT NULL,
	`status_comment` text NOT NULL,
	`created_by` int(11) NOT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
	) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
	dbDelta( $sql );
	$status_comment = 'status_comment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $status_comment, $wpdb->get_col( "DESC {$smgt_leave}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$smgt_leave} ADD {$status_comment} text NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	mjschool_add_default_admission_fees_type();
	mjschool_add_default_registration_fees_type();
	mjschool_add_default_library_periods();
	// ---- End Leave tables. -----//
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$teacher_class = $wpdb->get_results( "SELECT * FROM {$mjschool_teacher_class}" );
	if ( empty( $teacher_class ) ) {
		$teacherlist = get_users( array( 'role' => 'teacher' ) );
		if ( ! empty( $teacherlist ) ) {
			foreach ( $teacherlist as $retrieve_data ) {
				$created_by   = get_current_user_id();
				$created_date = current_time( 'mysql' );
				$class_id     = get_user_meta( $retrieve_data->ID, 'class_name', true );
				
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$success = $wpdb->insert(
					$mjschool_teacher_class,
					array(
						'teacher_id'   => absint( $retrieve_data->ID ),
						'class_id'     => absint( $class_id ),
						'created_by'   => absint( $created_by ),
						'created_date' => $created_date,
					),
					array( '%d', '%d', '%d', '%s' )
				);	
				if ( false === $success ) {
					error_log( "MJSchool: Failed to insert teacher class data for teacher ID: {$retrieve_data->ID}" );
				}
			}
		}
	}
	/* Update transport*/
	$table_transport = $wpdb->prefix . 'mjschool_transport';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE {$table_transport} MODIFY number_of_vehicle int(11) NOT NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_hall     = $wpdb->prefix . 'mjschool_hall';
	$creted_by_hall = 'created_by';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $creted_by_hall, $wpdb->get_col( "DESC {$table_hall}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_hall} ADD {$creted_by_hall} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/* Update Makrs*/
	$table_marks = $wpdb->prefix . 'mjschool_marks';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE {$table_marks} MODIFY marks float" );
	$wpdb->query( "ALTER TABLE {$table_marks} MODIFY grade_id int(11) NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$table_mjschool_holiday = $wpdb->prefix . 'mjschool_holiday';
	$created_date_holiday   = 'created_date';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $created_date_holiday, $wpdb->get_col( "DESC {$table_mjschool_holiday}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_holiday} ADD {$created_date_holiday} datetime NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// ------------- alter query for holiday status. --------------//
	$table_mjschool_holiday_status = $wpdb->prefix . 'mjschool_holiday';
	$status_holiday                = 'status';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $status_holiday, $wpdb->get_col( "DESC {$table_mjschool_holiday_status}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_holiday_status} ADD {$status_holiday} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_transport = $wpdb->prefix . 'mjschool_transport';
	$creted_by                = 'created_by';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $creted_by, $wpdb->get_col( "DESC {$table_mjschool_transport}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_transport} ADD {$creted_by} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$comment_field = 'comment';

	// Sub Attendance
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $comment_field, $wpdb->get_col( "DESC {$mjschool_sub_attendance}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$mjschool_sub_attendance} ADD {$comment_field} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_attendance = $wpdb->prefix . 'mjschool_attendence';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $comment_field, $wpdb->get_col( "DESC {$table_attendance}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_attendance} ADD {$comment_field} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Add post_id to Message Table
	 */
	$new_field              = 'post_id';
	$table_mjschool_message = $wpdb->prefix . 'mjschool_message';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $new_field, $wpdb->get_col( "DESC {$table_mjschool_message}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_message} ADD {$new_field} int(11)" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * Add section_id to Multiple Tables
	 */
	$section_id = 'section_id';
	$created_by = 'created_by';

	// Subject Table
	$table_subject = $wpdb->prefix . 'mjschool_subject';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_subject}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_subject} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Fees Table
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_mjschool_fees}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_fees} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Fees Payment Table
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_mjschool_fees_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Modify fees_id column
	$fees_id = 'fees_id';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE {$table_mjschool_fees_payment} MODIFY COLUMN {$fees_id} varchar(255) NOT NULL" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	// Income/Expense Table
	$table_mjschool_income_expense = $wpdb->prefix . 'mjschool_income_expense';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_mjschool_income_expense}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_income_expense} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Add tax to Income/Expense
	$tax = 'tax';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $tax, $wpdb->get_col( "DESC {$table_mjschool_income_expense}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_income_expense} ADD {$tax} varchar(100) NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Add tax_amount to Income/Expense
	$tax_amount = 'tax_amount';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $tax_amount, $wpdb->get_col( "DESC {$table_mjschool_income_expense}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_income_expense} ADD {$tax_amount} double DEFAULT 0" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Library Book Issue Table
	$table_mjschool_library_book_issue = $wpdb->prefix . 'mjschool_library_book_issue';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_mjschool_library_book_issue}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_library_book_issue} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Add library_card_no
	$library_card_no = 'library_card_no';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $library_card_no, $wpdb->get_col( "DESC {$table_mjschool_library_book_issue}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_library_book_issue} ADD {$library_card_no} varchar(50) DEFAULT NULL AFTER student_id" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Add comment to library
	$library_comment = 'comment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $library_comment, $wpdb->get_col( "DESC {$table_mjschool_library_book_issue}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_library_book_issue} ADD {$library_comment} text DEFAULT NULL AFTER student_id" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Payment Table - section_id
	$table_mjschool_payment = $wpdb->prefix . 'mjschool_payment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_mjschool_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_payment} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $created_by, $wpdb->get_col( "DESC {$table_mjschool_payment}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_payment} ADD {$created_by} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// Time Table - section_name
	$section_name              = 'section_name';
	$table_mjschool_time_table = $wpdb->prefix . 'mjschool_time_table';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_name, $wpdb->get_col( "DESC {$table_mjschool_time_table}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_time_table} ADD {$section_name} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// Marks Table - section_id
	$table_marks = $wpdb->prefix . 'mjschool_marks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id, $wpdb->get_col( "DESC {$table_marks}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_marks} ADD {$section_id} int(11) NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$table_mjschool_class = $wpdb->prefix . 'mjschool_class';
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query( "ALTER TABLE {$table_mjschool_class} MODIFY class_capacity int" );
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	// Call section transfer function
	mjschool_transfer_section_id();

	/**
	 * Add Multiple Columns to Exam Table
	 */
	$exam_start_date       = 'exam_start_date';
	$exam_end_date         = 'exam_end_date';
	$class_id              = 'class_id';
	$section_id1           = 'section_id';
	$exam_term             = 'exam_term';
	$passing_mark          = 'passing_mark';
	$total_mark            = 'total_mark';
	$exam_syllabus         = 'exam_syllabus';
	$table_mjschool_exam   = $wpdb->prefix . 'mjschool_exam';

	// class_id
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $class_id, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$class_id} int(11) NOT NULL AFTER exam_name" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// section_id
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $section_id1, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$section_id1} int(11) NOT NULL AFTER class_id" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// exam_term
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $exam_term, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$exam_term} int(11) NOT NULL AFTER section_id" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// passing_mark
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $passing_mark, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$passing_mark} tinyint(3) NOT NULL AFTER exam_term" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// total_mark
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $total_mark, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$total_mark} tinyint(3) NOT NULL AFTER passing_mark" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// exam_start_date
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $exam_start_date, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$exam_start_date} date NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// exam_end_date
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $exam_end_date, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$exam_end_date} date NOT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	// exam_syllabus
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $exam_syllabus, $wpdb->get_col( "DESC {$table_mjschool_exam}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_mjschool_exam} ADD {$exam_syllabus} varchar(255) DEFAULT NULL AFTER exam_end_date" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$homework_document = 'homework_document';
	$mjschool_homework = $wpdb->prefix . 'mjschool_homework';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $homework_document, $wpdb->get_col( "DESC {$mjschool_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$mjschool_homework} ADD {$homework_document} varchar(255) DEFAULT NULL AFTER content" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$marks = 'marks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $marks, $wpdb->get_col( "DESC {$mjschool_homework}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$mjschool_homework} ADD {$marks} tinyint(3) DEFAULT NULL AFTER content" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Add subject_code to Subject Table
	 */
	$subject_code  = 'subject_code';
	$table_subject = $wpdb->prefix . 'mjschool_subject';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $subject_code, $wpdb->get_col( "DESC {$table_subject}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_subject} ADD {$subject_code} varchar(255) DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Add Columns to Message Replies Table
	 */
	$mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
	$message_attachment       = 'message_attachment';
	$status_reply             = 'status';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $message_attachment, $wpdb->get_col( "DESC {$mjschool_message_replies}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$mjschool_message_replies} ADD {$message_attachment} text" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $status_reply, $wpdb->get_col( "DESC {$mjschool_message_replies}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$mjschool_message_replies} ADD {$status_reply} int(11)" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Add Columns to Hostel Table
	 */
	$hostel_address = 'hostel_address';
	$hostel_intake  = 'hostel_intake';
	$table_hostel   = $wpdb->prefix . 'mjschool_hostel';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $hostel_address, $wpdb->get_col( "DESC {$table_hostel}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_hostel} ADD {$hostel_address} varchar(255) AFTER hostel_name" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $hostel_intake, $wpdb->get_col( "DESC {$table_hostel}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_hostel} ADD {$hostel_intake} int(11) NOT NULL DEFAULT 0 AFTER hostel_type" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Add bed_charge to Beds Table
	 */
	$bed_charge = 'bed_charge';
	$table_bed  = $wpdb->prefix . 'mjschool_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $bed_charge, $wpdb->get_col( "DESC {$table_bed}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_bed} ADD {$bed_charge} int(11) NOT NULL DEFAULT 0 AFTER bed_description" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	/**
	 * Add Columns to Library Book Table
	 */
	$book_number        = 'book_number';
	$table_library_book = $wpdb->prefix . 'mjschool_library_book';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $book_number, $wpdb->get_col( "DESC {$table_library_book}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_library_book} ADD {$book_number} int(11) NOT NULL DEFAULT 0 AFTER book_name" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$publisher = 'publisher';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $publisher, $wpdb->get_col( "DESC {$table_library_book}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_library_book} ADD {$publisher} varchar(100) DEFAULT NULL AFTER author_name" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	$total_quentity = 'total_quentity';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( ! in_array( $total_quentity, $wpdb->get_col( "DESC {$table_library_book}", 0 ), true ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "ALTER TABLE {$table_library_book} ADD {$total_quentity} int(11) NOT NULL DEFAULT 0 AFTER quentity" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}
	// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}
/**
 * Transfers section IDs to users based on their current class and section metadata.
 *
 * This function updates the `class_section` user meta value from section names to section IDs
 * for all students across all classes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_transfer_section_id() {

	$allclass = mjschool_get_all_data( 'mjschool_class' );
	foreach ($allclass as $class) {
		$allsections = mjschool_get_class_sections($class->class_id);
		foreach ($allsections as $section) {
			$usersdata = get_users(array(
				'meta_key' => 'class_section',
				'meta_value' => $section->section_name,
				'meta_query' => array(array( 'key' => 'class_name', 'value' => $class->class_id, 'compare' => '=' ) ),
				'role' => 'student'
			 ) );
			foreach ($usersdata as $user) {
				update_user_meta($user->ID, "class_section", $section->id);
			}
		}
	}

}
/**
 * Returns a mapping of PHP date formats to jQuery datepicker formats.
 *
 * @since 1.0.0
 *
 * @return array List of supported date format mappings.
 */
function mjschool_datepicker_date_format() {
	$date_format_array = array(
		'Y-m-d' => 'yy-mm-dd',
		'Y/m/d' => 'yy/mm/dd',
		'd-m-Y' => 'dd-mm-yy',
		'm/d/Y' => 'mm/dd/yy',
	);
	return $date_format_array;
}
/**
 * Retrieves the PHP date format equivalent for a given datepicker format.
 *
 * @since 1.0.0
 *
 * @param string $dateformat_value jQuery datepicker format.
 *
 * @return string|false Matching PHP date format, or false if not found.
 */
function mjschool_get_php_date_format( $dateformat_value ) {
	$date_format_array = mjschool_datepicker_date_format();
	$php_format        = array_search( $dateformat_value, $date_format_array );
	return $php_format;
}
/**
 * Converts a date into the format selected in plugin settings.
 *
 * @since 1.0.0
 *
 * @param string $date Raw date input.
 *
 * @return string Formatted date string.
 */
function mjschool_get_date_in_input_box( $date ) {
	return date( mjschool_get_php_date_format( get_option( 'mjschool_datepicker_format' ) ), strtotime( $date ) );
}
/**
 * Replaces placeholders within a message string using a key–value array.
 *
 * @since 1.0.0
 *
 * @param array  $arr        Replacement pairs.
 * @param string $MsgContent Message content containing placeholders.
 *
 * @return string Updated message content.
 */
function mjschool_string_replacement( $arr, $MsgContent ) {
	$data = str_replace( array_keys( $arr ), array_values( $arr ), $MsgContent );
	return $data;
}
/**
 * Sends an email using WordPress mail with a template-based HTML layout.
 *
 * @since 1.0.0
 *
 * @param string $email   Recipient email address.
 * @param string $subject Email subject.
 * @param string $message Email body content.
 *
 * @return void
 */
function mjschool_send_mail( $email, $subject, $message ) {
	$from      = get_option( 'mjschool_name' );
	$headers   = "MIME-Version: 1.0\r\n";
	$headers  .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers  .= 'From: ' . $from . ' <noreplay@gmail.com>' . "\r\n";
	// MAIL CONTEMNT WITH TEMPLATE DESIGN.
	$email_template = mjschool_get_mail_content_with_template_design( $message );
	if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
		wp_mail( $email, $subject, $email_template, $headers );
	}
}
/**
 * Returns the primary role of a WordPress user based on their user ID.
 *
 * @since 1.0.0
 *
 * @param int $id User ID.
 *
 * @return string User role slug, or an empty string if not found.
 */
function mjschool_get_user_role( $id ) {
	$id = absint( $id );
	
	if ( empty( $id ) ) {
		return '';
	}
	
	$result = get_userdata( $id );
	if ( false === $result || ! isset( $result->roles ) ) {
		return '';
	}
	
	$role_array = $result->roles;
	
	if ( in_array( 'administrator', $role_array, true ) ) {
		$role = 'administrator';
	} elseif ( in_array( 'management', $role_array, true ) ) {
		$role = 'management';
	} elseif ( in_array( 'student', $role_array, true ) ) {
		$role = 'student';
	} elseif ( in_array( 'teacher', $role_array, true ) ) {
		$role = 'teacher';
	} elseif ( in_array( 'parent', $role_array, true ) ) {
		$role = 'parent';
	} elseif ( in_array( 'supportstaff', $role_array, true ) ) {
		$role = 'supportstaff';
	} else {
		$role = '';
	}
	
	return $role;
}
/**
 * Retrieves the currency symbol for the given currency code.
 *
 * @since 1.0.0
 *
 * @param string $currency Optional. Currency code. Defaults to plugin setting.
 *
 * @return string Currency symbol, or the currency code if symbol not found.
 */
function mjschool_get_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = get_option( 'mjschool_currency_code' );
	}
	$currencies = mjschool_get_currency_list();
	foreach ( $currencies as $cur ) {
		if ( $cur['code'] === $currency ) {
			return $cur['symbol'];
		}
	}
	return $currency; // fallback to code if not found.
}
/**
 * Fetches all teachers assigned to a given class ID.
 *
 * @since 1.0.0
 *
 * @param int $class_id Class ID.
 *
 * @return array List of WP_User objects for assigned teachers.
 */
function mjschool_get_teacher_by_class_id( $class_id ) {
	$class_id = absint( $class_id );
	if ( empty( $class_id ) ) {
		return array();
	}
	$teacher_data = array();
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_teacher_class';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$teachers = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE class_id = %d", $class_id )
	);
	if ( ! empty( $teachers ) ) {
		foreach ( $teachers as $teacher ) {
			if ( ! isset( $teacher->teacher_id ) ) {
				continue;
			}
			$teachersdata = get_userdata( absint( $teacher->teacher_id ) );
			if ( ! empty( $teachersdata ) ) {
				$teacher_data[] = $teachersdata;
			}
		}
	}
	return $teacher_data;
}
/**
 * Generates HTML content for a fees invoice.
 *
 * @since 1.0.0
 *
 * @param int $fees_pay_id Fees payment record ID.
 *
 * @return string Rendered HTML invoice content.
 */
function mjschool_get_html_content( $fees_pay_id ) {
	$fees_pay_id = absint( $fees_pay_id );
	if ( empty( $fees_pay_id ) ) {
		return '';
	}
	$schooName     = get_option( 'mjschool_name' );
	$schooLogo     = get_option( 'mjschool_logo' );
	$schooAddress  = get_option( 'mjschool_address' );
	$schoolCountry = get_option( 'mjschool_contry' );
	$schoolNo      = get_option( 'mjschool_contact_number' );
	$fees_detail_result         = mjschool_get_single_fees_payment_record( $fees_pay_id );
	$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
	if ( empty( $fees_detail_result ) || ! isset( $fees_detail_result->student_id ) ) {
		return '';
	}
	$student_id = absint( $fees_detail_result->student_id );
	$abc        = '';
	if ( ! empty( $student_id ) ) {
		$patient = get_userdata( $student_id );
		if ( $patient && isset( $patient->display_name ) ) {
			$abc  = get_user_meta( $student_id, 'address', true ) . ', ';
			$abc .= get_user_meta( $student_id, 'city', true ) . ', ';
			$abc .= get_user_meta( $student_id, 'zip_code', true ) . ',<br>';
			$abc .= get_user_meta( $student_id, 'state', true ) . ', ';
			$abc .= esc_html( $schoolCountry ) . ', ';
			$abc .= get_user_meta( $student_id, 'mobile', true ) . '<br>';
		}
	}
	
	$content  = '<div style="background-color:aliceblue; padding:20px" class="modal-body">';
	$content .= '<div class="modal-header">';
	$content .= '<h4 class="modal-title">' . esc_html( $schooName ) . '</h4>';
	$content .= '</div>';
	$content .= '<div id="mjschool-invoice-print" class="print-box">';
	$content .= '<table width="100%" border="0"><tbody><tr>';
	$content .= '<td width="70%"><img style="max-height:80px;" src="' . esc_url( $schooLogo ) . '" alt="' . esc_attr( $schooName ) . '"/></td>';
	$content .= '<td align="right" width="24%"><h5>';
	$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
	$PStatus        = 'Not Paid';
	if ( $payment_status === 'Fully Paid' ) {
		$PStatus = 'Fully Paid';
	} elseif ( $payment_status === 'Partially Paid' ) {
		$PStatus = 'Partially Paid';
	}
	$issue_date = isset( $fees_detail_result->paid_by_date ) ? $fees_detail_result->paid_by_date : date( 'Y-m-d' );
	
	$content .= 'Issue Date: ' . esc_html( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $issue_date ) ) ) ) . '</h5>';
	$content .= '<h5>Status: <span class="btn btn-success btn-xs">' . esc_html( $PStatus ) . '</span></h5>';
	$content .= '</td></tr></tbody></table>';
	
	$content .= '<table width="100%" border="0"><tbody>';
	$content .= '<tr><td align="left"><h4>Payment From</h4></td>';
	$content .= '<td align="right"><h4>Bill To</h4></td></tr>';
	$content .= '<tr><td valign="top" align="left">';
	$content .= esc_html( $schooName ) . '<br>';
	$content .= esc_html( $schooAddress ) . ', ';
	$content .= esc_html( $schoolCountry ) . '<br>';
	$content .= esc_html( $schoolNo ) . '<br>';
	$content .= '</td><td valign="top" align="right">' . wp_kses_post( $abc ) . '</td></tr>';
	$content .= '</tbody></table><hr>';
	
	$content .= '<table class="table table-bordered mjschool_border_collapse" width="100%" border="1">';
	$content .= '<thead><tr>';
	$content .= '<th class="text-center">#</th>';
	$content .= '<th class="text-center">Fees Type</th>';
	$content .= '<th>Total</th>';
	$content .= '</tr></thead><tbody>';
	$content .= '<tr><td>1</td>';
	$fees_id = isset( $fees_detail_result->fees_id ) ? $fees_detail_result->fees_id : 0;
	$content .= '<td>' . esc_html( mjschool_get_fees_term_name( $fees_id ) ) . '</td>';
	$total_amount = isset( $fees_detail_result->total_amount ) ? floatval( $fees_detail_result->total_amount ) : 0;
	$paid_amount  = isset( $fees_detail_result->fees_paid_amount ) ? floatval( $fees_detail_result->fees_paid_amount ) : 0;
	
	$content .= '<td>' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total_amount, 2, '.', '' ) ) ) . '</td>';
	$content .= '</tr></tbody></table>';
	
	$Due_amount = $total_amount - $paid_amount;
	
	$content .= '<table width="100%" border="0"><tbody>';
	$content .= '<tr><td width="80%" align="right">Sub Total:</td>';
	$content .= '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total_amount, 2, '.', '' ) ) ) . '</td></tr>';
	$content .= '<tr><td width="80%" align="right">Payment Made:</td>';
	$content .= '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $paid_amount, 2, '.', '' ) ) ) . '</td></tr>';
	$content .= '<tr><td width="80%" align="right">Due Amount:</td>';
	$content .= '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ) . '</td></tr>';
	$content .= '</tbody></table></div></div>';
	
	return $content;
}
/**
 * Cleans a string by removing slashes, HTML entities and tags.
 *
 * @since 1.0.0
 *
 * @param string $post_string Input string to sanitize.
 *
 * @return string Sanitized output string.
 */
function mjschool_strip_tags_and_stripslashes( $post_string ) {
	$string = str_replace( '&nbsp;', ' ', $post_string );
	$string = str_replace( '\\', '', $post_string );
	$string = html_entity_decode( $string, ENT_HTML5, 'UTF-8' );
	$string = html_entity_decode( $string );
	$string = htmlspecialchars_decode( $string );
	$string = strip_tags( $string );
	return $string;
}
/**
 * Checks whether the current user has permission to view a dashboard page.
 *
 * @since 1.0.0
 *
 * @param string $page Page slug.
 *
 * @return int 1 if access is granted, 0 otherwise.
 */
function mjschool_page_access_role_wise_access_right_dashboard( $page ) {
	$page               = sanitize_text_field( $page );
	$school_obj = new MJSchool_Management( get_current_user_id() );
	$role       = $school_obj->role;
	$flage      = 0;
	if ( $role === 'student' ) {
		$menu = get_option( 'mjschool_access_right_student' );
	} elseif ( $role === 'parent' ) {
		$menu = get_option( 'mjschool_access_right_parent' );
	} elseif ( $role === 'supportstaff' ) {
		$menu = get_option( 'mjschool_access_right_supportstaff' );
	} elseif ( $role === 'teacher' ) {
		$menu = get_option( 'mjschool_access_right_teacher' );
	}
	foreach ( $menu as $key1 => $value1 ) {
		foreach ( $value1 as $key => $value ) {
			if ( $page === $value['page_link'] ) {
				if ( $value['view'] === '0' ) {
					$flage = 0;
				} else {
					$flage = 1;
				}
			}
		}
	}
	return $flage;
}
/**
 * Retrieves backend access rights for the current user role for a given page.
 *
 * @since 1.0.0
 *
 * @param string $mjschool_page_name Page slug.
 *
 * @return array|null Permission configuration array or null if not found.
 */
function mjschool_get_user_role_wise_filter_access_right_array( $mjschool_page_name ) {
	$role = mjschool_get_user_role( get_current_user_id() );
	if ( $role === 'student' ) {
		$menu = get_option( 'mjschool_access_right_student' );
	} elseif ( $role === 'parent' ) {
		$menu = get_option( 'mjschool_access_right_parent' );
	} elseif ( $role === 'supportstaff' ) {
		$menu = get_option( 'mjschool_access_right_supportstaff' );
	} elseif ( $role === 'teacher' ) {
		$menu = get_option( 'mjschool_access_right_teacher' );
	} elseif ( $role === 'management' ) {
		$menu = get_option( 'mjschool_access_right_management' );
	} else {
		$menu = 0;
	}
	if ( ! empty( $menu ) ) {
		foreach ( $menu as $key1 => $value1 ) {
			foreach ( $value1 as $key => $value ) {
				if ( $mjschool_page_name === $value['page_link'] ) {
					return $value;
				}
			}
		}
	}
}
/**
 * Retrieves access rights for the current user based on the active frontend page.
 *
 * @since 1.0.0
 *
 * @return array|null Access rights configuration for the user role.
 */
function mjschool_get_user_role_wise_access_right_array() {
	$school_obj = new MJSchool_Management( get_current_user_id() );
	$role       = $school_obj->role;
	$page       = '';
	if ( isset( $_REQUEST['page'] ) && ! empty( $_REQUEST['page'] ) ) {
		$page = sanitize_text_field( wp_unslash( $_REQUEST['page'] ) );
	}
	$menu = array();
	if ( $role === 'student' ) {
		$menu = get_option( 'mjschool_access_right_student' );
	} elseif ( $role === 'parent' ) {
		$menu = get_option( 'mjschool_access_right_parent' );
	} elseif ( $role === 'supportstaff' ) {
		$menu = get_option( 'mjschool_access_right_supportstaff' );
	} elseif ( $role === 'teacher' ) {
		$menu = get_option( 'mjschool_access_right_teacher' );
	}
	if ( ! is_array( $menu ) || empty( $menu ) ) {
		return null;
	}
	foreach ( $menu as $key1 => $value1 ) {
		if ( ! is_array( $value1 ) ) {
			continue;
		}
		foreach ( $value1 as $key => $value ) {
			if ( ! is_array( $value ) || ! isset( $value['page_link'] ) ) {
				continue;
			}
			
			if ( $page === $value['page_link'] ) {
				return $value;
			}
		}
	}
	return null;
}
/**
 * Retrieves access rights for management users on a given admin page.
 *
 * @since 1.0.0
 *
 * @param string $page Admin page slug.
 *
 * @return array|null Access rights configuration.
 */
function mjschool_get_management_access_right_array( $page ) {
	$page = sanitize_text_field( $page );
	
	$page_route     = 'schedule';
	$page_exam_hall = 'exam_hall';
	$page_homework  = 'homework';
	$fees_payment   = 'feepayment';
	if ( $page === 'mjschool_route' ) {
		$mjschool_page_name = $page_route;
	} elseif ( $page === 'mjschool_hall' ) {
		$mjschool_page_name = $page_exam_hall;
	} elseif ( $page === 'mjschool_student_homewrok' ) {
		$mjschool_page_name = $page_homework;
	} elseif ( $page === 'mjschool_fees_payment' ) {
		$mjschool_page_name = $fees_payment;
	} else {
		$mjschool_page_name = strtolower( str_replace( 'mjschool_', '', $page ) );
	}
	$role = mjschool_get_user_role( get_current_user_id() );
	$menu = array();
	if ( $role === 'management' ) {
		$menu = get_option( 'mjschool_access_right_management' );
	}
	if ( ! is_array( $menu ) || empty( $menu ) ) {
		return null;
	}
	foreach ( $menu as $key1 => $value1 ) {
		if ( ! is_array( $value1 ) ) {
			continue;
		}
		foreach ( $value1 as $key => $value ) {
			if ( ! is_array( $value ) || ! isset( $value['page_link'] ) ) {
				continue;
			}
			if ( $mjschool_page_name === $value['page_link'] ) {
				return $value;
			}
		}
	}
	return null;
}
/**
 * Retrieves access permissions for management users for a specific page.
 *
 * @since 1.0.0
 *
 * @param string $page Page slug.
 *
 * @return int 1 if access is allowed, 0 otherwise.
 */
function mjschool_get_user_role_wise_access_right_array_by_page( $page ) {
	$page               = sanitize_text_field( $page );
	$flage              = 0;
	$mjschool_page_name = str_replace( 'mjschool_', '', $page );
	$role               = mjschool_get_user_role( get_current_user_id() );
	$menu = array();
	if ( $role === 'management' ) {
		$menu = get_option( 'mjschool_access_right_management' );
	}
	if ( ! is_array( $menu ) || empty( $menu ) ) {
		return 0;
	}
	foreach ( $menu as $key1 => $value1 ) {
		if ( ! is_array( $value1 ) ) {
			continue;
		}
		foreach ( $value1 as $key => $value ) {
			if ( ! is_array( $value ) || ! isset( $value['page_link'] ) ) {
				continue;
			}
			if ( $mjschool_page_name === $value['page_link'] ) {
				if ( isset( $value['view'] ) && $value['view'] === '0' ) {
					$flage = 0;
				} else {
					$flage = 1;
				}
			}
		}
	}
	return $flage;
}
/**
 * Sanitizes password strings by stripping tags and decoding HTML entities.
 *
 * @since 1.0.0
 *
 * @param string $post_string Raw input string.
 *
 * @return string Sanitized password string.
 */
function mjschool_password_validation( $post_string ) {
	$string         = str_replace( '&nbsp;', ' ', $post_string );
	$string         = html_entity_decode( $string, ENT_QUOTES | ENT_COMPAT, 'UTF-8' );
	$string         = html_entity_decode( $string, ENT_HTML5, 'UTF-8' );
	$string         = html_entity_decode( $string );
	$string         = htmlspecialchars_decode( $string );
	$replase_string = strip_tags( $string );
	return $replase_string;
}
/**
 * Converts a datetime string to the user's local timezone and format.
 *
 * @since 1.0.0
 *
 * @param string $date_time Raw datetime value.
 *
 * @return string Formatted localized datetime string.
 */
function mjschool_convert_date_time( $date_time ) {
	$format = get_option( 'mjschool_datepicker_format' );
	if ( $format === 'yy-mm-dd' ) {
		$change_formate = 'Y-m-d';
	} elseif ( $format === 'yy/mm/dd' ) {
		$change_formate = 'Y/m/d';
	} elseif ( $format === 'dd-mm-yy' ) {
		$change_formate = 'd-m-Y';
	} elseif ( $format === 'mm/dd/yy' ) {
		$change_formate = 'm/d/Y';
	} else {
		$change_formate = 'Y-m-d';
	}
	$timestamp       = strtotime( $date_time ); // Converting time to Unix timestamp.
	$offset          = get_option( 'gmt_offset' ) * 60 * 60; // Time offset in seconds.
	$local_timestamp = $timestamp + $offset;
	$local_time      = date_i18n( $change_formate . ' H:i:s', $local_timestamp );
	return $local_time;
}
/**
 * Generates a list of all holiday dates including date ranges.
 *
 * @since 1.0.0
 *
 * @return array List of all dates marked as holidays.
 */
function mjschool_get_all_date_of_holidays() {
	global $wpdb;
	$tbl_holiday = $wpdb->prefix . 'mjschool_holiday';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$HolidayData = $wpdb->get_results( "SELECT * FROM {$tbl_holiday}" );
	$holidaydates = array();
	if ( empty( $HolidayData ) ) {
		return $holidaydates;
	}
	foreach ( $HolidayData as $holiday ) {
		if ( ! isset( $holiday->date ) || ! isset( $holiday->end_date ) ) {
			continue;
		}
		$holidaydates[] = $holiday->date;
		$holidaydates[] = $holiday->end_date;
		$start_date = strtotime( $holiday->date );
		$end_date   = strtotime( $holiday->end_date );
		if ( false === $start_date || false === $end_date ) {
			continue;
		}
		if ( $holiday->date !== $holiday->end_date ) {
			for ( $i = $start_date; $i < $end_date; $i += 86400 ) {
				$holidaydates[] = gmdate( 'Y-m-d', $i );
			}
		}
	}
	$holidaydates = array_unique( $holidaydates );
	return $holidaydates;
}
/**
 * Generates a new admission number using the configured prefix.
 *
 * @since 1.0.0
 *
 * @return string Generated admission number.
 */
function mjschool_generate_admission_number() {
	global $wpdb;
	$prefix = get_option( 'mjschool_prefix', 'SMGT' ); // e.g., 'ST'.
	$userdata = get_users();
	if ( empty( $userdata ) ) {
		$admission_no = 1;
	} else {
		$admission_no = count( $userdata ) + 1;
	}
	return $prefix . $admission_no; // e.g., ST6.
}
/**
 * Adds a new dynamic category or post type entry.
 *
 * @since 1.0.0
 *
 * @param array $data Category data including type and name.
 *
 * @return int Inserted post ID.
 */
function mjschool_add_categorytype( $data ) {
	if ( ! is_array( $data ) || ! isset( $data['category_type'] ) || ! isset( $data['category_name'] ) ) {
		return 0;
	}
	$category_type = sanitize_key( $data['category_type'] );
	$category_name = sanitize_text_field( $data['category_name'] );
	if ( empty( $category_type ) || empty( $category_name ) ) {
		return 0;
	}
	global $wpdb;
	if ( $category_type === 'period_type' ) {
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => 'mjschool_bookperiod',
				'post_title'  => $category_name,
			)
		);
	} else {
		$result = wp_insert_post(
			array(
				'post_status' => 'publish',
				'post_type'   => $category_type,
				'post_title'  => $category_name,
			)
		);
	}
	if ( is_wp_error( $result ) ) {
		return 0;
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$id = $wpdb->insert_id;
	return absint( $id );
}
/**
 * Retrieves all category posts for a given post type.
 *
 * @since 1.0.0
 *
 * @param string $model Post type slug.
 *
 * @return array List of post objects.
 */
function mjschool_get_all_category( $model ) {
	$args       = array(
		'post_type'      => $model,
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'Asc',
	);
	$cat_result = get_posts( $args );
	return $cat_result;
}
add_action( 'wp_ajax_mjschool_datatable_homework_data_ajax_to_load', 'mjschool_datatable_homework_data_ajax_to_load' );
add_action( 'wp_ajax_mjschool_leave_approve', 'mjschool_leave_approve' );
add_action( 'wp_ajax_mjschool_leave_reject', 'mjschool_leave_reject' );
add_action( 'wp_ajax_mjschool_load_students_homework', 'mjschool_load_students_homework' );
add_action( 'wp_ajax_nopriv_mjschool_load_students_homework', 'mjschool_load_students_homework' );
add_action( 'wp_ajax_mjschool_load_sections_students_homework', 'mjschool_load_sections_students_homework' );
add_action( 'wp_ajax_nopriv_mjschool_load_sections_students_homework', 'mjschool_load_sections_students_homework' );
/**
 * Handles AJAX request to load homework data for DataTables.
 *
 * Validates nonce, ensures the user is logged in, fetches homework records,
 * formats them, and returns JSON for DataTables.
 *
 * @since 1.0.0
 * @return void Outputs JSON and terminates execution.
 */
function mjschool_datatable_homework_data_ajax_to_load() {
    // 1. CHECK THE NONCE FIRST
    if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
        wp_send_json_error( 'Security check failed.' );
        wp_die();
    }

    // 2. CHECK IF USER IS LOGGED IN
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( 'You must be logged in.' );
        wp_die();
    }
    
    global $wpdb;
    $sTable = $wpdb->prefix . 'mjschool_homework';
    $iStart  = isset( $_REQUEST['iDisplayStart'] ) ? absint( $_REQUEST['iDisplayStart'] ) : 0;
    $iLength = isset( $_REQUEST['iDisplayLength'] ) ? absint( $_REQUEST['iDisplayLength'] ) : 10;
    
    if ( $iLength === 0 || $iLength > 100 ) {
        $iLength = 10; // Prevent abuse.
    }
    
    $sLimit = "LIMIT {$iStart}, {$iLength}";
    $ssearch = isset( $_REQUEST['sSearch'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sSearch'] ) ) : '';
    
    if ( ! empty( $ssearch ) ) {
        $search_term = '%' . $wpdb->esc_like( $ssearch ) . '%';
        $sQuery = $wpdb->prepare(
            "SELECT * FROM {$sTable} WHERE title LIKE %s OR to_date LIKE %s {$sLimit}",
            $search_term,
            $search_term
        );
    } else {
        $sQuery = "SELECT * FROM {$sTable} {$sLimit}";
    }
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $rResult = $wpdb->get_results( $sQuery, ARRAY_A );
    
    // Get total counts
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->get_results( "SELECT * FROM {$sTable}" );
    $iTotal = $wpdb->num_rows;
    
    $output = array(
        'sEcho'                => isset( $_REQUEST['sEcho'] ) ? absint( $_REQUEST['sEcho'] ) : 0,
        'iTotalRecords'        => $iTotal,
        'iTotalDisplayRecords' => $iTotal,
        'aaData'               => array(),
    );
    
    if ( ! empty( $rResult ) ) {
        foreach ( $rResult as $aRow ) {
            if ( ! isset( $aRow['homework_id'] ) ) {
                continue;
            }
            $homework_id = absint( $aRow['homework_id'] );
            $class_id    = isset( $aRow['class_id'] ) ? absint( $aRow['class_id'] ) : 0;
            $section_id  = isset( $aRow['section_id'] ) ? absint( $aRow['section_id'] ) : 0;
            $subject_id  = isset( $aRow['subject_id'] ) ? absint( $aRow['subject_id'] ) : 0;
            $section_name = ( $section_id !== 0 ) ? mjschool_get_section_name( $section_id ) : esc_html__( 'No Section', 'mjschool' );
            $row    = array();
            $row[0] = '<input type="checkbox" class="select-checkbox" name="id[]" value="' . esc_attr( $homework_id ) . '">';
            $row[1] = isset( $aRow['title'] ) ? esc_html( $aRow['title'] ) : '';
            $row[2] = esc_html( mjschool_get_class_name( $class_id ) );
            $row[3] = esc_html( $section_name );
            $row[4] = esc_html( mjschool_get_single_subject_name( $subject_id ) );
            $row[5] = isset( $aRow['to_date'] ) ? esc_html( $aRow['to_date'] ) : '';
            $row[6]  = '<a href="?page=mjschool_Homework&amp;tab=addhomework&amp;action=edit&amp;homework_id=' . esc_attr( $homework_id ) . '" class="btn btn-info">';
            $row[6] .= '<i class="fas fa-edit"></i>&nbsp; ' . esc_html__( 'Edit', 'mjschool' ) . '</a>&nbsp;&nbsp;';
            $row[6] .= '<a href="?page=mjschool_Homework&amp;tab=homeworklist&amp;action=delete&amp;del_homework_id=' . esc_attr( $homework_id ) . '" class="btn btn-danger delete_selected" onclick="ConfirmDelete()">';
            $row[6] .= '<i class="fas fa-times"></i>&nbsp; ' . esc_html__( 'Delete', 'mjschool' ) . '</a>&nbsp;&nbsp;';
            $row[6] .= '<a href="?page=mjschool_Homework&amp;tab=submission&amp;homework_id=' . esc_attr( $homework_id ) . '" class="btn btn-default">';
            $row[6] .= '<i class="fas fa-eye"></i>&nbsp; ' . esc_html__( 'View Submissions', 'mjschool' ) . '</a>';
            $output['aaData'][] = $row;
        }
    }
    wp_send_json( $output );
    wp_die();
}
/**
 * Loads the modal HTML for leave approval form via AJAX.
 *
 * Validates nonce and login status, then prints the approve leave
 * form markup including validation script.
 *
 * @since 1.0.0
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_leave_approve() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header mjschool-margin-bottom-20px" >
		<a href="javascript:void(0);" class="close-btn-cat badge badge-success pull-right"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php esc_html_e( 'Leave Approve', 'mjschool' ); ?></h4>
	</div>
	<div class="mjschool-panel-white mjschool-padding-20px">
		<form name="leave_form" action="" method="post" class="mjschool-form-horizontal" id="leave_form">
			<input type="hidden" name="leave_id" value="<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['leave_id'])) ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-9">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea name="comment" cols="50" rows="2" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="250"></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active"><?php esc_html_e( 'Comment', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<input type="submit" value="<?php esc_attr_e( 'Submit', 'mjschool' ); ?>" name="approve_comment" class="btn btn-success mjschool-save-btn" id="btn-add-cat" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
/**
 * Loads the modal HTML for leave rejection form via AJAX.
 *
 * Validates nonce and login status, then prints the reject leave
 * form markup including validation script.
 *
 * @since 1.0.0
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_leave_reject() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	?>
	<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header mjschool-margin-bottom-20px">

		<a href="javascript:void(0);" class="close-btn-cat badge badge-success pull-right"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
		<h4 id="myLargeModalLabel" class="modal-title"><?php esc_html_e( 'Leave Reject', 'mjschool' ); ?></h4>

	</div>
	<div class="mjschool-panel-white mjschool-padding-20px">
		<form name="leave_form" action="" method="post" class="mjschool-form-horizontal" id="leave_form">
			<input type="hidden" name="leave_id" value="<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['leave_id'])) ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-9">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea name="comment" cols="50" rows="2" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="250"></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active"><?php esc_html_e( 'Comment', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3">
						<input type="submit" value="<?php esc_attr_e( 'Submit', 'mjschool' ); ?>" name="reject_leave" class="btn btn-success mjschool-save-btn" id="btn-add-cat" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	wp_die();
}
/**
 * Loads students, sections, and subjects based on class selection via AJAX.
 *
 * Used when creating homework. Returns three dropdown HTML lists:
 * students, sections, and subjects.
 *
 * @since 1.0.0
 * @return void Outputs JSON response and terminates execution.
 */
function mjschool_load_students_homework() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	$class_id = sanitize_text_field(wp_unslash($_POST['class_list']));
	global $wpdb;
	$exlude_id = mjschool_approve_student_list();

	$retrieve_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );

	$resoinse = array();
	$student  = '';
	$sections = '';
	$subjects = '';
	foreach ( $retrieve_data as $users ) {
		$student .= '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( $users->first_name ) . ' ' . esc_html( $users->last_name ) . '</option>';
	}
	$resoinse[0] = $student;
	/*---------SECTION.-------------*/
	$retrieve_data = mjschool_get_class_sections( $class_id );
	$defaultmsg    = esc_attr__( 'All Section', 'mjschool' );
	$sections      = "<option value=''>" . esc_attr( $defaultmsg ) . '</option>';
	foreach ( $retrieve_data as $section ) {
		$teacher_access      = get_option( 'mjschool_access_right_teacher' );
		$teacher_access_data = $teacher_access['teacher'];
		foreach ( $teacher_access_data as $key => $value ) {
			if ( $key === 'student' ) {
				$data = $value;
			}
		}
		if ( $data['own_data'] === '1' && mjschool_get_roles( get_current_user_id() ) === 'teacher' ) {
			$section = smgt_get_section( $section );
		}
		$sections .= "<option value='" . esc_attr( $section->id ) . "'>" . esc_html( $section->section_name ) . '</option>';
	}
	$resoinse[1] = $sections;
	/*----------subjects.--------------*/
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$user_id    = get_current_user_id();
	// ------------------------TEACHER ACCESS.---------------------------------//
	$teacher_access      = get_option( 'mjschool_access_right_teacher' );
	$teacher_access_data = $teacher_access['teacher'];
	foreach ( $teacher_access_data as $key => $value ) {
		if ( $key === 'subject' ) {
			$data = $value;
		}
	}
	if ( mjschool_get_roles( $user_id ) === 'teacher' && $data['own_data'] === '1' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( "SELECT * FROM $table_name where  teacher_id=$user_id and class_id=" . $class_id );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( "SELECT * FROM $table_name WHERE class_id=" . $class_id );
	}
	$defaultmsg = esc_attr__( 'Select subject', 'mjschool' );
	$subjects   = "<option value=''>" . $defaultmsg . '</option>';
	if ( ! empty( $retrieve_subject ) ) {
		foreach ( $retrieve_subject as $retrieved_data ) {
			$subjects .= '<option value=' . esc_attr( $retrieved_data->subid ) . '> ' . esc_html( $retrieved_data->sub_name ) . '</option>';
		}
	}
	$resoinse[2] = $subjects;
	echo json_encode( $resoinse );
	die();
}
/**
 * Loads students and subjects for a selected section via AJAX.
 *
 * Returns two dropdown HTML lists: filtered students and subjects.
 *
 * @since 1.0.0
 * @return void Outputs JSON response and terminates execution.
 */
function mjschool_load_sections_students_homework() {
	// 1. CHECK THE NONCE FIRST - Proof of intent from a valid form.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_die( 'Security check failed.' ); // Stop if the nonce is invalid.
	}

	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_die( 'You must be logged in.' );
	}
	global $wpdb;
	$resoinse   = array();
	$student    = '';
	$subjects   = '';
	$section_id = isset($_POST['section_id']) ? sanitize_text_field(wp_unslash($_POST['section_id'])) : '';
	$exlude_id  = mjschool_approve_student_list();

	$retrieve_data = get_users(array( 'meta_key' => 'class_section', 'meta_value' => $section_id, 'role' => 'student', 'exclude' => $exlude_id ) );

	if ( ! empty( $retrieve_data ) ) {
		foreach ( $retrieve_data as $users ) {
			$student .= '<option value=' . esc_attr( $users->ID ) . '>' . esc_html( $users->first_name ) . ' ' . esc_html( $users->last_name ) . '</option>';
		}
	}
	$resoinse[0] = $student;
	/*----------subjects.--------------*/
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$user_id    = get_current_user_id();
	// ------------------------TEACHER ACCESS.---------------------------------//
	$teacher_access      = get_option( 'mjschool_access_right_teacher' );
	$teacher_access_data = $teacher_access['teacher'];
	foreach ( $teacher_access_data as $key => $value ) {
		if ( $key === 'subject' ) {
			$data = $value;
		}
	}
	if ( mjschool_get_roles( $user_id ) === 'teacher' && $data['own_data'] === '1' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id=%d AND class_id=%d", $user_id, $class_id ) );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE section_id=%d", $section_id ) );
	}
	$defaultmsg = esc_attr__( 'Select subject', 'mjschool' );
	$subjects   = "<option value=''>" . esc_attr( $defaultmsg ) . '</option>';
	foreach ( $retrieve_subject as $retrieved_data ) {
		$subjects .= '<option value=' . esc_attr( $retrieved_data->subid ) . '> ' . esc_html( $retrieved_data->sub_name ) . '</option>';
	}
	$resoinse[1] = $subjects;
	echo json_encode( $resoinse );
	die();
}
/**
 * Inserts a new exam hall receipt entry for a student.
 *
 * @since 1.0.0
 *
 * @param int $user_id   Student ID.
 * @param int $exam_hall Exam hall ID.
 * @param int $exam_id   Exam ID.
 *
 * @return int User ID on successful insert.
 */
function mjschool_insert_exam_reciept( $user_id, $exam_hall, $exam_id ) {
	$current_user = get_current_user_id();
	$created_date = date( 'Y-m-d' );
	$status       = 1;
	$mjschool_table_name    = 'mjschool_exam_hall_receipt';
	$hall_data    = array(
		'exam_id'                  => sanitize_text_field( $exam_id ),
		'user_id'                  => sanitize_text_field( $user_id ),
		'hall_id'                  => sanitize_text_field( $exam_hall ),
		'exam_hall_receipt_status' => $status,
		'created_date'             => $created_date,
		'created_by'               => $current_user,
	);
	global $wpdb;
	$table_name = $wpdb->prefix . $mjschool_table_name;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_name, $hall_data );
	return $user_id;
}
add_action( 'wp_ajax_mjschool_load_exam_hall_receipt_div', 'mjschool_load_exam_hall_receipt_div' );
add_action( 'wp_ajax_nopriv_mjschool_load_exam_hall_receipt_div', 'mjschool_load_exam_hall_receipt_div' );
/**
 * Loads the exam hall receipt assignment UI via AJAX.
 *
 * Validates nonce and login, retrieves exam details, unassigned students,
 * available halls, and builds the full HTML form layout.
 *
 * @since 1.0.0
 * @return void Outputs HTML and terminates execution.
 */
function mjschool_load_exam_hall_receipt_div() {
	// 1. CHECK THE NONCE FIRST.
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_send_json_error( 'Security check failed.' );
		wp_die();
	}
	// 2. CHECK IF USER IS LOGGED IN.
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'You must be logged in.' );
		wp_die();
	}
	global $wpdb;
	$exam_id = isset( $_REQUEST['exam_id'] ) ? absint( $_REQUEST['exam_id'] ) : 0;
	if ( empty( $exam_id ) ) {
		wp_send_json_error( 'Invalid exam ID' );
		wp_die();
	}
	$exam_data = mjschool_get_exam_by_id( $exam_id );
	if ( empty( $exam_data ) ) {
		wp_send_json_error( 'Exam not found' );
		wp_die();
	}
	$start_date = isset( $exam_data->exam_start_date ) ? $exam_data->exam_start_date : '';
	$end_date   = isset( $exam_data->exam_end_date ) ? $exam_data->exam_end_date : '';
	$class_id   = isset( $exam_data->class_id ) ? absint( $exam_data->class_id ) : 0;
	$section_id = isset( $exam_data->section_id ) ? absint( $exam_data->section_id ) : 0;
	if ( empty( $class_id ) ) {
		wp_send_json_error( 'Invalid class ID' );
		wp_die();
	}
	// Get excluded student list.
	$exlude_id = mjschool_approve_student_list();
	
	// Get student data based on class and section.
	if ( ! empty( $class_id ) && ! empty( $section_id ) ) {
		$student_data = get_users(
			array(
				'role'       => 'student',
				'exclude'    => $exlude_id,
				'meta_query' => array(
					array(
						'key'     => 'class_name',
						'value'   => $class_id,
						'compare' => '==',
					),
					array(
						'key'     => 'class_section',
						'value'   => $section_id,
						'compare' => '==',
					),
				),
			)
		);
	} else {
		$student_data = get_users(
			array(
				'meta_key'   => 'class_name',
				'meta_value' => $class_id,
				'role'       => 'student',
				'exclude'    => $exlude_id,
			)
		);
	}
	$student_id  = array();
	$student_id1 = array();
	
	// Build student ID array
	if ( ! empty( $student_data ) ) {
		foreach ( $student_data as $s_id ) {
			if ( isset( $s_id->ID ) ) {
				$student_id[] = absint( $s_id->ID );
			}
		}
	}
	
	// Get assigned students
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$student_data_asigned = $wpdb->get_results(
		$wpdb->prepare( "SELECT user_id FROM {$table_name_mjschool_exam_hall_receipt} WHERE exam_id = %d", $exam_id )
	);
	
	// Build assigned student ID array
	if ( ! empty( $student_data_asigned ) ) {
		foreach ( $student_data_asigned as $s_id1 ) {
			if ( isset( $s_id1->user_id ) ) {
				$student_id1[] = absint( $s_id1->user_id );
			}
		}
	}
	
	// Calculate unassigned students
	if ( empty( $student_data_asigned ) ) {
		$student_show_data = $student_id;
	} else {
		$student_show_data = array_diff( $student_id, $student_id1 );
	}
	
	// Build HTML response
	$array_var  = '<div class="exam_hall_receipt_main_div">';
	$array_var .= '<form name="receipt_form" action="" method="post" class="mjschool-form-horizontal" id="receipt_form">';
	$array_var .= '<input type="hidden" name="exam_id" value="' . esc_attr( $exam_id ) . '">';
	$array_var .= '<div class="form-group row">';
	$array_var .= '<div class="table-responsive rtl_mjschool-padding-15px">';
	$array_var .= '<table class="table exam_hall_table mjschool_examhall_border_1px_center" id="exam_hall_table">';
	$array_var .= '<thead><tr>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading_medium">' . esc_html__( 'Exam', 'mjschool' ) . '</th>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table">' . esc_html__( 'Class', 'mjschool' ) . '</th>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table">' . esc_html__( 'Section', 'mjschool' ) . '</th>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table">' . esc_html__( 'Term', 'mjschool' ) . '</th>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_library_table">' . esc_html__( 'Start Date', 'mjschool' ) . '</th>';
	$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head">' . esc_html__( 'End Date', 'mjschool' ) . '</th>';
	$array_var .= '</tr></thead>';
	$array_var .= '<tfoot></tfoot>';
	$array_var .= '<tbody>';
	$exam_name    = isset( $exam_data->exam_name ) ? esc_html( $exam_data->exam_name ) : '';
	$class_name   = mjschool_get_class_name( $class_id );
	$exam_term_id = isset( $exam_data->exam_term ) ? absint( $exam_data->exam_term ) : 0;
	
	$array_var .= '<tr>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px">' . $exam_name . '</td>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px">' . esc_html( $class_name ) . '</td>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px">';
	
	if ( ! empty( $section_id ) ) {
		$array_var .= esc_html( mjschool_get_section_name( $section_id ) );
	} else {
		$array_var .= esc_html__( 'No Section', 'mjschool' );
	}
	
	$array_var .= '</td>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px">' . esc_html( get_the_title( $exam_term_id ) ) . '</td>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_border_right_1px">' . esc_html( mjschool_get_date_in_input_box( $start_date ) ) . '</td>';
	$array_var .= '<td class="mjschool-exam-hall-receipt-table-value">' . esc_html( mjschool_get_date_in_input_box( $end_date ) ) . '</td>';
	$array_var .= '</tr>';
	$array_var .= '</tbody></table></div></div>';
	
	// Exam hall dropdown
	$array_var .= '<div class="form-body mjschool-user-form mjschool-margin-top-20px mjschool-padding-top-25px-res">';
	$array_var .= '<div class="row"><div class="col-md-6 col-sm-6 col-xs-12">';
	
	$table_name = $wpdb->prefix . 'mjschool_hall';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$retrieve_subject = $wpdb->get_results( "SELECT * FROM {$table_name}" );
	
	$array_var .= '<select name="exam_hall" class="mjschool-line-height-30px form-control validate[required]" id="exam_hall">';
	$array_var .= '<option value="">' . esc_html__( 'Select Exam Hall', 'mjschool' ) . '</option>';
	
	if ( ! empty( $retrieve_subject ) ) {
		foreach ( $retrieve_subject as $retrieved_data ) {
			if ( ! isset( $retrieved_data->hall_id ) || ! isset( $retrieved_data->hall_name ) ) {
				continue;
			}
			
			$hall_id       = absint( $retrieved_data->hall_id );
			$hall_name     = esc_html( stripslashes( $retrieved_data->hall_name ) );
			$hall_capacity = isset( $retrieved_data->hall_capacity ) ? absint( $retrieved_data->hall_capacity ) : 0;
			
			$array_var .= '<option id="exam_hall_capacity_' . esc_attr( $hall_id ) . '" ';
			$array_var .= 'hall_capacity="' . esc_attr( $hall_capacity ) . '" ';
			$array_var .= 'value="' . esc_attr( $hall_id ) . '">';
			$array_var .= $hall_name;
			$array_var .= '</option>';
		}
	}
	$array_var .= '</select></div></div></div>';
	// Student lists section.
	$array_var .= '<div class="form-group row mjschool-margin-top-20px mjschool-padding-top-25px-res">';
	$array_var .= '<div class="col-md-12"><div class="row">';
	if ( ! empty( $student_show_data ) || ! empty( $student_data_asigned ) ) {
		// Unassigned students table.
		$array_var .= '<div class="col-md-6 col-sm-6 col-xs-12">';
		$array_var .= '<h4 class="exam_hall_lable">' . esc_html__( 'Not Assigned Exam Hall Student List', 'mjschool' ) . '</h4>';
		if ( isset( $student_show_data ) && ! empty( $student_show_data ) ) {
			$array_var .= '<table id="not_approve_table" class="display exam_timelist mjschool_examhall_border_1px_center" cellspacing="0" width="100%">';
			$array_var .= '<thead><tr>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_names">';
			$array_var .= '<input name="select_all[]" value="all" class="hall_receipt_checkbox my_all_check" id="checkbox-select-all" type="checkbox" />';
			$array_var .= '</th>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading">' . esc_html__( 'Student Name', 'mjschool' ) . '</th>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head">' . esc_html__( 'Student Roll No', 'mjschool' ) . '</th>';
			$array_var .= '</tr></thead><tbody>';
			$has_students = false;
			foreach ( $student_show_data as $retrieve_data ) {
				$userdata = get_userdata( absint( $retrieve_data ) );
				if ( empty( $userdata ) || ! isset( $userdata->display_name ) ) {
					continue;
				}
				$has_students = true;
				$user_id      = absint( $retrieve_data );
				$display_name = esc_html( $userdata->display_name );
				$roll_id      = esc_html( get_user_meta( $user_id, 'roll_id', true ) );
				$array_var .= '<tr id="' . esc_attr( $user_id ) . '" class="mjschool_border_1px_white">';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">';
				$array_var .= '<input type="checkbox" class="hall_receipt_checkbox select-checkbox my_check" ';
				$array_var .= 'name="id[]" dataid="' . esc_attr( $user_id ) . '" value="' . esc_attr( $user_id ) . '">';
				$array_var .= '</td>';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $display_name . '</td>';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $roll_id . '</td>';
				$array_var .= '</tr>';
			}
			if ( ! $has_students ) {
				$array_var .= '<tr><td class="no_data_td_remove" style="text-align:center;" colspan="3">';
				$array_var .= esc_html__( 'No Student Available', 'mjschool' );
				$array_var .= '</td></tr>';
			}
			$array_var .= '</tbody></table>';
			$array_var .= '<tr><td>';
			$array_var .= '<button type="button" class="mt-2 btn btn-success mjschool-save-btn mjschool-assign-exam-hall" ';
			$array_var .= 'name="assign_exam_hall" id="assign_exam_hall">';
			$array_var .= esc_html__( 'Assign Exam Hall', 'mjschool' );
			$array_var .= '</button>';
			$array_var .= '</td></tr>';
		}
		$array_var .= '</div>';
		// Assigned students table.
		$array_var .= '<div class="col-md-6 col-sm-6 col-xs-12">';
		$array_var .= '<h4 class="exam_hall_lable">' . esc_html__( 'Assigned Exam Hall Student List', 'mjschool' ) . '</h4>';
		if ( isset( $student_data_asigned ) && ! empty( $student_data_asigned ) ) {
			$array_var .= '<table id="approve_table" class="display exam_timelist mjschool_examhall_border_1px_center" cellspacing="0" width="100%">';
			$array_var .= '<thead><tr>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_names"></th>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjschool_examhall_heading">' . esc_html__( 'Student Name', 'mjschool' ) . '</th>';
			$array_var .= '<th class="mjschool-exam-hall-receipt-table-heading mjchool_receipt_table_head">' . esc_html__( 'Student Roll No', 'mjschool' ) . '</th>';
			$array_var .= '</tr></thead><tbody>';
			$has_assigned = false;
			foreach ( $student_data_asigned as $retrieve_data1 ) {
				if ( ! isset( $retrieve_data1->user_id ) ) {
					continue;
				}
				$userdata = get_userdata( absint( $retrieve_data1->user_id ) );
				if ( empty( $userdata ) || ! isset( $userdata->display_name ) ) {
					continue;
				}
				$has_assigned     = true;
				$user_id          = absint( $retrieve_data1->user_id );
				$display_name     = esc_html( $userdata->display_name );
				$roll_id          = esc_html( get_user_meta( $user_id, 'roll_id', true ) );
				$dlt_image_icon   = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/Delete.png' );
				$array_var .= '<tr class="assign_student_exam_lis mjschool_border_1px_white" id="' . esc_attr( $user_id ) . '">';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">';
				$array_var .= '<a class="delete_receipt_record" href="#" dataid="' . esc_attr( $user_id ) . '" id="' . esc_attr( $user_id ) . '">';
				$array_var .= '<img src="' . $dlt_image_icon . '" class="mjschool-massage-image" alt="' . esc_attr__( 'Delete', 'mjschool' ) . '">';
				$array_var .= '</a></td>';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $display_name . '</td>';
				$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $roll_id . '</td>';
				$array_var .= '</tr>';
			}
			if ( ! $has_assigned ) {
				$array_var .= '<tr><td colspan="3" style="text-align:center;">' . esc_html__( 'No students assigned yet', 'mjschool' ) . '</td></tr>';
			}
			$array_var .= '</tbody></table>';
			$array_var .= '<tr><td>';
			$array_var .= '<button type="submit" class="mt-2 btn mjschool-save-btn btn-success" ';
			$array_var .= 'name="send_mail_exam_receipt" id="send_mail_exam_receipt">';
			$array_var .= esc_html__( 'Send Mail', 'mjschool' );
			$array_var .= '</button>';
			$array_var .= '</td></tr>';
		}
		$array_var .= '</div>';
	} else {
		$array_var .= '<div><h4>' . esc_html__( 'No Student Available', 'mjschool' ) . '</h4></div>';
	}
	$array_var .= '</div></div></div></form></div>';
	wp_send_json_success( array( $array_var ) );
	wp_die();
}
add_action( 'wp_ajax_mjschool_delete_receipt_record', 'mjschool_delete_receipt_record' );
add_action( 'wp_ajax_nopriv_mjschool_delete_receipt_record', 'mjschool_delete_receipt_record' );
/**
 * Deletes a student's exam hall receipt record via AJAX and returns the updated HTML row.
 *
 * Performs nonce verification and login checks, deletes the record from the
 * `mjschool_exam_hall_receipt` table, and returns the updated table row markup.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON encoded HTML row.
 */
function mjschool_delete_receipt_record() {
	// 1. CHECK THE NONCE FIRST
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_send_json_error( 'Security check failed.' );
		wp_die();
	}

	// 2. CHECK IF USER IS LOGGED IN
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'You must be logged in.' );
		wp_die();
	}
	$id      = isset( $_POST['record_id'] ) ? absint( $_POST['record_id'] ) : 0;
	$exam_id = isset( $_POST['exam_id'] ) ? absint( $_POST['exam_id'] ) : 0;
	
	if ( empty( $id ) || empty( $exam_id ) ) {
		wp_send_json_error( 'Invalid parameters' );
		wp_die();
	}
	
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$table_name_mjschool_exam_hall_receipt} WHERE exam_id = %d AND user_id = %d",
			$exam_id,
			$id
		)
	);
	
	if ( $deleted ) {
		$userdata = get_userdata( $id );
		
		if ( empty( $userdata ) || ! isset( $userdata->display_name ) ) {
			wp_send_json_error( 'User not found' );
			wp_die();
		}
		
		$display_name = esc_html( $userdata->display_name );
		$roll_id      = esc_html( get_user_meta( $id, 'roll_id', true ) );
		
		// Build HTML row for unassigned list
		$array_var  = '<tr id="' . esc_attr( $id ) . '" class="mjschool_border_1px_white">';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">';
		$array_var .= '<input type="checkbox" class="select-checkbox my_check hall_receipt_checkbox" ';
		$array_var .= 'name="id[]" dataid="' . esc_attr( $id ) . '" value="' . esc_attr( $id ) . '">';
		$array_var .= '</td>';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $display_name . '</td>';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $roll_id . '</td>';
		$array_var .= '</tr>';
		
		wp_send_json_success( array( $array_var ) );
	} else {
		wp_send_json_error( 'Delete failed' );
	}
	
	wp_die();
}
add_action( 'wp_ajax_mjschool_add_receipt_record', 'mjschool_add_receipt_record' );
add_action( 'wp_ajax_nopriv_mjschool_add_receipt_record', 'mjschool_add_receipt_record' );
/**
 * Adds exam hall receipt records for selected students via AJAX.
 *
 * Validates the AJAX request, inserts receipt records for each user,
 * and returns updated HTML table rows for display in the admin panel.
 *
 * @since 1.0.0
 *
 * @return void Outputs JSON encoded HTML rows.
 */
function mjschool_add_receipt_record() {
	// 1. CHECK THE NONCE FIRST
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_send_json_error( 'Security check failed.' );
		wp_die();
	}

	// 2. CHECK IF USER IS LOGGED IN
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'You must be logged in.' );
		wp_die();
	}
	$user_id_array = isset( $_POST['id_array'] ) && is_array( $_POST['id_array'] ) ? array_map( 'absint', $_POST['id_array'] ) : array();
	$exam_hall = isset( $_POST['exam_hall'] ) ? absint( $_POST['exam_hall'] ) : 0;
	$exam_id   = isset( $_POST['exam_id'] ) ? absint( $_POST['exam_id'] ) : 0;
	
	if ( empty( $user_id_array ) || empty( $exam_hall ) || empty( $exam_id ) ) {
		wp_send_json_error( 'Invalid parameters' );
		wp_die();
	}
	
	$array_var = '';
	$dlt_image_icon = esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/Delete.png' );
	
	foreach ( $user_id_array as $id ) {
		$id = absint( $id );
		
		if ( empty( $id ) ) {
			continue;
		}
		
		// Insert the receipt record
		$user_id = mjschool_insert_exam_reciept( $id, $exam_hall, $exam_id );
		
		if ( empty( $user_id ) ) {
			continue;
		}
		$userdata = get_userdata( $user_id );
		
		if ( empty( $userdata ) || ! isset( $userdata->display_name ) ) {
			continue;
		}
		
		$display_name = esc_html( $userdata->display_name );
		$roll_id      = esc_html( get_user_meta( $user_id, 'roll_id', true ) );
		
		// Build HTML row for assigned list
		$array_var .= '<tr id="' . esc_attr( $user_id ) . '" class="mjschool_border_1px_white">';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">';
		$array_var .= '<a class="delete_receipt_record" href="#" dataid="' . esc_attr( $user_id ) . '" id="' . esc_attr( $user_id ) . '">';
		$array_var .= '<img src="' . $dlt_image_icon . '" class="mjschool-massage-image" alt="' . esc_attr__( 'Delete', 'mjschool' ) . '">';
		$array_var .= '</a></td>';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $display_name . '</td>';
		$array_var .= '<td class="mjschool-exam-hall-receipt-table-value mjschool_text_align_center">' . $roll_id . '</td>';
		$array_var .= '</tr>';
	}
	
	wp_send_json_success( array( $array_var ) );
	wp_die();
}
/**
 * Retrieves the exam hall receipt records for a given student.
 *
 * Queries the `mjschool_exam_hall_receipt` table and returns stored
 * receipt entries associated with the specified student ID.
 *
 * @since 1.0.0
 *
 * @param int $id Student user ID.
 *
 * @return array List of exam hall receipt records.
 */
function mjschool_student_exam_receipt_check( $id ) {
	$student_id = absint( $id );
	if ( empty( $student_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name_mjschool_exam_hall_receipt} WHERE user_id = %d", $student_id )
	);
	return is_array( $result ) ? $result : array();
}
/**
 * Retrieves exam hall receipt details for a specific student and exam.
 *
 * @since 1.0.0
 *
 * @param int $id  Student ID.
 * @param int $eid Exam ID.
 *
 * @return object|null Database row containing exam hall receipt details, or null if not found.
 */
function mjschool_get_exam_hall_name( $id, $eid ) {
	$student_id = absint( $id );
	$exam_id    = absint( $eid );
	if ( empty( $student_id ) || empty( $exam_id ) ) {
		return null;
	}
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table_name_mjschool_exam_hall_receipt} WHERE exam_id = %d AND user_id = %d", $exam_id, $student_id )
	);
	return $result;
}
/**
 * Retrieves the hall name based on hall ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hall ID.
 *
 * @return string|null Hall name if available, otherwise null.
 */
function mjschool_get_hall_name( $eid ) {
	$hall_id = absint( $eid );
	
	if ( empty( $hall_id ) ) {
		return '';
	}
	global $wpdb;
	$table_name_hall = $wpdb->prefix . 'mjschool_hall';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table_name_hall} WHERE hall_id = %d", $hall_id )
	);
	if ( empty( $result ) || ! isset( $result->hall_name ) ) {
		return '';
	}
	return $result->hall_name;
}
/**
 * Retrieves hall capacity for a given hall ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hall ID.
 *
 * @return int|null Hall capacity if found, otherwise null.
 */
function mjschool_get_hall_capacity( $eid ) {
	$hall_id = absint( $eid );
	if ( empty( $hall_id ) ) {
		return 0;
	}
	global $wpdb;
	$table_name_hall = $wpdb->prefix . 'mjschool_hall';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT hall_capacity FROM {$table_name_hall} WHERE hall_id = %d", $hall_id ) );
	if ( empty( $result ) || ! isset( $result->hall_capacity ) ) {
		return 0;
	}
	return absint( $result->hall_capacity );
}
/**
 * Generates a unique room code based on the last inserted room ID.
 *
 * @since 1.0.0
 *
 * @return string Generated room code (e.g., RM001).
 */
function mjschool_generate_room_code() {
	global $wpdb;
	$smgt_room = $wpdb->prefix . 'mjschool_room';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$last = $wpdb->get_var( "SELECT MAX(id) FROM {$smgt_room}" );
	$lastid = ( $last ) ? absint( $last ) + 1 : 1;
	$code = 'RM' . str_pad( $lastid, 3, '0', STR_PAD_LEFT );
	return $code;
}
/**
 * Generates a unique bed code based on the last inserted bed ID.
 *
 * @since 1.0.0
 *
 * @return string Generated bed code (e.g., BD001).
 */
function mjschool_generate_bed_code() {
	global $wpdb;
	$smgt_beds = $wpdb->prefix . 'mjschool_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$last = $wpdb->get_var( "SELECT MAX(id) FROM {$smgt_beds}" );
	$lastid = ( $last ) ? absint( $last ) + 1 : 1;
	$code = 'BD' . str_pad( $lastid, 3, '0', STR_PAD_LEFT );
	return $code;
}
/**
 * Retrieves the hostel name using its ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hostel ID.
 *
 * @return string Hostel name or 'N/A' if not found.
 */
function mjschool_get_hostel_name_by_id( $eid ) {
	$id = absint( $eid );
	if ( empty( $id ) ) {
		return 'N/A';
	}
	global $wpdb;
	$smgt_hostel = $wpdb->prefix . 'mjschool_hostel';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$smgt_hostel} WHERE id = %d", $id )
	);
	if ( empty( $result ) || ! isset( $result->hostel_name ) ) {
		return 'N/A';
	}
	
	return $result->hostel_name;
}
/**
 * Retrieves a room's unique ID using the room record ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return string Room unique ID or empty string if not found.
 */
function mjschool_get_room_unique_id_by_id( $eid ) {
	$id = absint( $eid );
	if ( empty( $id ) ) {
		return '';
	}
	global $wpdb;
	$smgt_room = $wpdb->prefix . 'mjschool_room';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT room_unique_id FROM {$smgt_room} WHERE id = %d", $id )
	);
	if ( empty( $result ) || ! isset( $result->room_unique_id ) ) {
		return '';
	}
	return $result->room_unique_id;
}
/**
 * Retrieves the bed capacity of a room by its ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return int Bed capacity, 0 if not found.
 */
function mjschool_get_bed_capacity_by_id( $eid ) {
	$id = absint( $eid );
	if ( empty( $id ) ) {
		return 0;
	}
	global $wpdb;
	$smgt_room = $wpdb->prefix . 'mjschool_room';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT beds_capacity FROM {$smgt_room} WHERE id = %d", $id )
	);
	if ( empty( $result ) || ! isset( $result->beds_capacity ) ) {
		return 0;
	}
	return absint( $result->beds_capacity );
}
/**
 * Counts the number of beds assigned to a room.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return int Number of beds in the room.
 */
function mjschool_hostel_room_bed_count( $eid ) {
	$id = absint( $eid );
	if ( empty( $id ) ) {
		return 0;
	}
	global $wpdb;
	$smgt_beds = $wpdb->prefix . 'mjschool_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result_bed = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$smgt_beds} WHERE room_id = %d", $id )
	);
	return is_array( $result_bed ) ? count( $result_bed ) : 0;
}
/**
 * Retrieves the count of occupied beds for a room.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return int Number of occupied beds.
 */
function mjschool_hostel_room_status_check( $eid ) {
	$room_id = absint( $eid );
	if ( empty( $room_id ) ) {
		return 0;
	}
	global $wpdb;
	$smgt_room = $wpdb->prefix . 'mjschool_room';
	$smgt_beds = $wpdb->prefix . 'mjschool_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT id, beds_capacity FROM {$smgt_room} WHERE id = %d", $room_id )
	);
	$final_cnt   = 0;
	$result_room = array();
	if ( ! empty( $result ) ) {
		foreach ( $result as $data ) {
			if ( ! isset( $data->id ) ) {
				continue;
			}
			$room_id_id = absint( $data->id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result_room = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM {$smgt_beds} WHERE room_id = %d AND bed_status = %d", $room_id_id, 1 )
			);
		}
		
		$final_cnt = is_array( $result_room ) ? count( $result_room ) : 0;
	}
	return $final_cnt;
}
/**
 * Retrieves student bed assignment data for a given bed ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Bed ID.
 *
 * @return object|null Bed assignment record or null if not found.
 */
function mjschool_student_assign_bed_data( $eid ) {
	$id = absint( $eid );
	if ( empty( $id ) ) {
		return null;
	}
	global $wpdb;
	$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table_mjschool_assign_beds} WHERE bed_id = %d", $id )
	);
	return $result;
}

/**
 * Retrieves the unique room ID using the room ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return string Room unique ID or 'N/A' if not found.
 */
function mjschool_get_room_unique_id_by_room_id( $eid ) {
	$room_id = absint( $eid );
	if ( empty( $room_id ) ) {
		return 'N/A';
	}
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT room_unique_id FROM {$table_mjschool_room} WHERE id = %d", $room_id )
	);
	if ( empty( $result ) || ! isset( $result->room_unique_id ) ) {
		return 'N/A';
	}
	return $result->room_unique_id;
}

/**
 * Retrieves the room category/type for a given room ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return string Room category or 'N/A' if not found.
 */
function mjschool_get_room_type_by_room_id( $eid ) {
	$room_id = absint( $eid );
	if ( empty( $room_id ) ) {
		return 'N/A';
	}
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT room_category FROM {$table_mjschool_room} WHERE id = %d", $room_id )
	);
	if ( empty( $result ) || ! isset( $result->room_category ) ) {
		return 'N/A';
	}
	return $result->room_category;
}
/**
 * Retrieves the hostel name using its ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hostel ID.
 *
 * @return string Hostel name or 'N/A' if not found.
 */
function mjschool_hostel_name_by_id( $eid ) {
	$hostel_id = absint( $eid );
	if ( empty( $hostel_id ) ) {
		return 'N/A';
	}
	global $wpdb;
	$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT hostel_name FROM {$table_mjschool_hostel} WHERE id = %d", $hostel_id )
	);
	if ( empty( $result ) || ! isset( $result->hostel_name ) ) {
		return 'N/A';
	}
	return $result->hostel_name;
}

/**
 * Retrieves all student bed assignment records.
 *
 * @since 1.0.0
 *
 * @return array List of assigned bed records.
 */
function mjschool_all_assign_student_data() {
	global $wpdb;
	$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results( "SELECT * FROM {$table_mjschool_assign_beds}" );
	return is_array( $result ) ? $result : array();
}

/**
 * Checks whether a message was sent to a single user or multiple users.
 *
 * @since 1.0.0
 *
 * @param int $eid Post/message ID.
 *
 * @return int Number of message recipients.
 */
function mjschool_send_message_check_single_user_or_multiple( $eid ) {
	$post_id = absint( $eid );
	if ( empty( $post_id ) ) {
		return 0;
	}
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_message';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$sent_message = $wpdb->get_var(
		$wpdb->prepare( "SELECT COUNT(*) FROM {$tbl_name} WHERE post_id = %d", $post_id )
	);
	return absint( $sent_message );
}
// -------------------- VIEW PAGE POPUP. -----------------------//
add_action( 'wp_ajax_mjschool_view_details_popup', 'mjschool_view_details_popup' );
add_action( 'wp_ajax_nopriv_mjschool_view_details_popup', 'mjschool_view_details_popup' );
/**
 * Handles AJAX request to display detailed popup views for various modules.
 *
 * Displays a modal with detailed information for transport, booklist,
 * room, homework, or exam records based on the requested type.
 * Also includes security validation via nonce and login checks.
 *
 * @since 1.0.0
 *
 * @return void Outputs HTML for the popup and terminates execution.
 */
function mjschool_view_details_popup() {
	// 1. CHECK THE NONCE FIRST
	if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'mjschool_ajax_nonce' ) ) {
		wp_send_json_error( 'Security check failed.' );
		wp_die();
	}

	// 2. CHECK IF USER IS LOGGED IN
	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'You must be logged in.' );
		wp_die();
	}
	?>
	<style>
		.table td,
		.table>tbody>tr>td,
		.table>tbody>tr>th,
		.table>tfoot>tr>td,
		.table>tfoot>tr>th,
		.table>thead>tr>td,
		.table>thead>tr>th {
			padding: 15px !important;
		}
	</style>
	<?php
	$school_type = get_option( 'mjschool_custom_class' );
	$recoed_id   = isset( $_REQUEST['record_id'] ) ? absint( $_REQUEST['record_id'] ) : 0;
	$type        = isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : '';
	
	// IMPROVED: Validate inputs
	if ( empty( $recoed_id ) || empty( $type ) ) {
		wp_send_json_error( 'Invalid parameters' );
		wp_die();
	}
	
	if ( $type === 'transport_view' ) {
		$transport_data = mjschool_get_transport_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $transport_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Transport details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-transportation.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Transport Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Route Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->route_name ) ? esc_html( $transport_data->route_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Vehicle Identifier', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->number_of_vehicle ) ? esc_html( $transport_data->number_of_vehicle ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Vehicle Registration Number', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->vehicle_reg_num ) ? esc_html( $transport_data->vehicle_reg_num ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Driver Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->driver_name ) ? esc_html( $transport_data->driver_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Driver Phone Number', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->driver_phone_num ) ? esc_html( $transport_data->driver_phone_num ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Driver Address', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $transport_data->driver_address ) ? esc_html( $transport_data->driver_address ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Route Fare', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $transport_data->route_fare ) ) {
							echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $transport_data->route_fare, 2, '.', '' ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Route Description', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $transport_data->route_description ) && ! empty( $transport_data->route_description ) ) {
							echo esc_html( $transport_data->route_description );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				$custom_field_obj = new mjschool_custome_field();
				$module           = 'transport';
				$custom_field_obj->mjschool_show_inserted_custom_field_data_in_popup( $module, $recoed_id );
				?>
			</div>
		</div>
		<?php
	} elseif ( $type === 'booklist_view' ) {
		$obj_lib   = new mjschoollibrary();
		$book_data = $obj_lib->mjschool_get_single_books( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $book_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Book details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-library.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Book Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'ISBN', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $book_data->ISBN ) ? esc_html( $book_data->ISBN ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Book Number', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $book_data->book_number ) && ! empty( $book_data->book_number ) ) {
							echo esc_html( $book_data->book_number );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Book Title', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $book_data->book_name ) ? esc_html( $book_data->book_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Book Category', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $book_data->cat_id ) ) {
							echo esc_html( get_the_title( absint( $book_data->cat_id ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Author Name', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $book_data->author_name ) ? esc_html( $book_data->author_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Publisher', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $book_data->publisher ) && ! empty( $book_data->publisher ) ) {
							echo esc_html( $book_data->publisher );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Rack Location', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $book_data->rack_location ) ) {
							echo esc_html( get_the_title( absint( $book_data->rack_location ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Book Price', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $book_data->price ) ) {
							echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $book_data->price, 2, '.', '' ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Remaining Quantity', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $book_data->quentity ) && isset( $book_data->total_quentity ) ) {
							echo esc_html( $book_data->quentity ) . ' ' . esc_html__( 'Out Of', 'mjschool' ) . ' ' . esc_html( $book_data->total_quentity );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $book_data->description ) ) {
							$description = ltrim( $book_data->description, ' ' );
							if ( ! empty( $description ) ) {
								echo esc_html( $description );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( $type === 'room_view' ) {
		$obj_hostel = new mjschool_hostel();
		$room_data  = $obj_hostel->mjschool_get_room_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $room_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Room details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-room.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Room Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $room_data->room_unique_id ) ? esc_html( $room_data->room_unique_id ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Hostel Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $room_data->hostel_id ) ) {
							echo esc_html( mjschool_get_hostel_name_by_id( absint( $room_data->hostel_id ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Room Category', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $room_data->room_category ) ) {
							echo esc_html( get_the_title( absint( $room_data->room_category ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php 
				$capacity = isset( $room_data->id ) ? $obj_hostel->mjschool_remaining_bed_capacity( absint( $room_data->id ) ) : 0;
				?>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Remaining Beds Capacity', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						echo esc_html( $capacity ) . ' ';
						esc_html_e( 'Out Of', 'mjschool' );
						if ( isset( $room_data->beds_capacity ) ) {
							echo ' ' . esc_html( $room_data->beds_capacity );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Status', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $room_data->id ) && isset( $room_data->beds_capacity ) ) {
							$room_cnt     = mjschool_hostel_room_status_check( absint( $room_data->id ) );
							$bed_capacity = absint( $room_data->beds_capacity );
							if ( $room_cnt >= $bed_capacity ) {
								?>
								<label class="mjschool-label-value mjschool_red_colors" > <?php esc_html_e( 'Occupied', 'mjschool' ); ?> </label>
								<?php
							} else {
								?>
								<label class="mjschool-label-value mjschool_green_colors" > <?php esc_html_e( 'Available', 'mjschool' ); ?> </label>
								<?php
							}
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $room_data->room_description ) && ! empty( $room_data->room_description ) ) {
							echo esc_html( $room_data->room_description );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( $type === 'Homework_view' ) {
		$objj      = new mjschool_Homework();
		$classdata = mjschool_get_homework_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $classdata ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Homework details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-homework.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Homework Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Title', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $classdata->title ) ? esc_html( $classdata->title ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $classdata->subject ) ) {
							echo esc_html( mjschool_get_subject_by_id( absint( $classdata->subject ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Class', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $classdata->class_name ) && isset( $classdata->section_id ) ) {
							echo esc_html( mjschool_get_class_section_name_wise( absint( $classdata->class_name ), absint( $classdata->section_id ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Homework Date', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $classdata->created_date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $classdata->created_date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Submission Date', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $classdata->submition_date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $classdata->submition_date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Documents Title', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						// FIXED: Proper json_decode() validation
						$doc_data = isset( $classdata->homework_document ) ? json_decode( $classdata->homework_document ) : null;
						if ( is_array( $doc_data ) && ! empty( $doc_data ) && isset( $doc_data[0]->title ) && ! empty( $doc_data[0]->title ) ) {
							echo esc_html( $doc_data[0]->title );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Download File', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						// FIXED: Proper json_decode() validation
						$doc_data = isset( $classdata->homework_document ) ? json_decode( $classdata->homework_document ) : null;
						if ( is_array( $doc_data ) && ! empty( $doc_data ) && isset( $doc_data[0]->value ) && ! empty( $doc_data[0]->value ) ) {
							?>
							<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" 
							   class="btn mjschool-custom-padding-0 popup_download_btn" 
							   record_id="<?php echo esc_attr( isset( $classdata->homework_id ) ? $classdata->homework_id : $recoed_id ); ?>">
								<i class="fa fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
							</a>
							<?php
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Homework Content', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $classdata->content ) && ! empty( $classdata->content ) ) {
							echo esc_html( $classdata->content );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
			</div>
		</div>
		<?php
	} elseif ( $type === 'Exam_view' ) {
		$exam_data = mjschool_get_exam_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $exam_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Exam details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		
		// FIXED: Proper json_decode() and foreach validation
		$subject_data = isset( $exam_data->subject_data ) ? json_decode( $exam_data->subject_data ) : null;
		if ( ! is_array( $subject_data ) ) {
			$subject_data = array();
		}
		
		// FIXED: Initialize variables
		$max_mark       = '';
		$passing_marks1 = '';
		
		foreach ( $subject_data as $subject ) {
			if ( isset( $subject->max_marks ) && isset( $subject->passing_marks ) ) {
				$max_mark       = $subject->max_marks;
				$passing_marks1 = $subject->passing_marks;
				break;
			}
		}
		
		$contributions_data_array = array();
		if ( isset( $exam_data->contributions ) && $exam_data->contributions === 'yes' ) {
			$contributions_data_array = isset( $exam_data->contributions_data ) ? json_decode( $exam_data->contributions_data ) : null;
			if ( ! is_array( $contributions_data_array ) ) {
				$contributions_data_array = array();
			}
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-exam.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Exam Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Title', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $exam_data->exam_name ) ? esc_html( $exam_data->exam_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Term', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_data->exam_term ) && ! empty( get_the_title( absint( $exam_data->exam_term ) ) ) ) {
							echo esc_html( get_the_title( absint( $exam_data->exam_term ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Class', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $exam_data->class_id ) && isset( $exam_data->section_id ) ) {
							echo esc_html( mjschool_get_class_section_name_wise( absint( $exam_data->class_id ), absint( $exam_data->section_id ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading">
						<?php esc_html_e( 'Start Date', 'mjschool' ); ?>
						<?php esc_html_e( 'To', 'mjschool' ); ?>
						<?php esc_html_e( 'End Date', 'mjschool' ); ?>
					</label><br>
					<label class="mjschool-label-value">
						<?php 
						if ( isset( $exam_data->exam_start_date ) && isset( $exam_data->exam_end_date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_start_date ) );
							echo ' ';
							esc_html_e( 'To', 'mjschool' );
							echo ' ';
							echo esc_html( mjschool_get_date_in_input_box( $exam_data->exam_end_date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Total Marks', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php 
						if ( ! empty( $max_mark ) ) {
							echo esc_html( $max_mark );
						} elseif ( isset( $exam_data->total_mark ) ) {
							echo esc_html( $exam_data->total_mark );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Passing Marks', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php 
						if ( ! empty( $passing_marks1 ) ) {
							echo esc_html( $passing_marks1 );
						} elseif ( isset( $exam_data->passing_mark ) ) {
							echo esc_html( $exam_data->passing_mark );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				if ( ! empty( $contributions_data_array ) ) {
					foreach ( $contributions_data_array as $key => $value ) {
						if ( ! isset( $value->label ) || ! isset( $value->mark ) ) {
							continue;
						}
						?>
						<div class="col-md-6 mjschool-popup-padding-15px">
							<label class="mjschool-popup-label-heading"> <?php echo esc_html( $value->label ); ?> </label><br>
							<label class="mjschool-label-value"> <?php echo esc_html( $value->mark ); ?> </label>
						</div>
						<?php
					}
				}
				?>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Download File', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						// FIXED: Proper json_decode() validation
						$doc_data = isset( $exam_data->exam_syllabus ) ? json_decode( $exam_data->exam_syllabus ) : null;
						if ( is_array( $doc_data ) && ! empty( $doc_data ) && isset( $doc_data[0]->value ) && ! empty( $doc_data[0]->value ) ) {
							?>
							<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $doc_data[0]->value ) ); ?>" 
							   class="btn mjschool-custom-padding-0 popup_download_btn" 
							   record_id="<?php echo esc_attr( isset( $exam_data->exam_id ) ? $exam_data->exam_id : $recoed_id ); ?>">
								<i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
							</a>
							<?php
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Comment', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_data->exam_comment ) && ! empty( $exam_data->exam_comment ) ) {
							echo esc_html( $exam_data->exam_comment );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				$custom_field_obj = new mjschool_custome_field();
				$module           = 'exam';
				$custom_field_obj->mjschool_show_inserted_custom_field_data_in_popup( $module, $recoed_id );
				?>
			</div>
		</div>
		<?php
	}elseif ( $type === 'beds_view' ) {
		$obj_hostel = new mjschool_hostel();
		$bed_data   = $obj_hostel->mjschool_get_bed_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $bed_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Bed details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-bed.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Beds Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $bed_data->bed_unique_id ) ? esc_html( $bed_data->bed_unique_id ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<?php 
				$hostel_id = isset( $bed_data->room_id ) ? $obj_hostel->mjschool_get_hostel_id_by_room_id( absint( $bed_data->room_id ) ) : 0;
				?>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php 
						if ( isset( $bed_data->room_id ) ) {
							echo esc_html( mjschool_get_room_unique_id_by_id( absint( $bed_data->room_id ) ) );
							if ( $hostel_id ) {
								echo ' (' . esc_html( mjschool_get_hostel_name_by_id( absint( $hostel_id ) ) ) . ')';
							}
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Status', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $bed_data->bed_status ) && $bed_data->bed_status === '0' ) {
							?>
							<label class="mjschool-label-value mjschool_green_colors" > <?php esc_html_e( 'Available', 'mjschool' ); ?> </label>
							<?php
						} else {
							?>
							<label class="mjschool-label-value mjschool_red_colors" > <?php esc_html_e( 'Occupied', 'mjschool' ); ?> </label>
							<?php
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Charge', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $bed_data->bed_charge ) ) {
							echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $bed_data->bed_charge, 2, '.', '' ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $bed_data->bed_description ) && ! empty( $bed_data->bed_description ) ) {
							echo esc_html( $bed_data->bed_description );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				if ( isset( $bed_data->bed_status ) && $bed_data->bed_status != '0' && isset( $bed_data->id ) ) {
					$assign_data = $obj_hostel->mjschool_get_assign_bed_by_id( absint( $bed_data->id ) );
					?>
					<div class="mb-3">
						<label class="mjschool-popup-label-heading" style="font-size: 18px !important; font-weight:bold;"> <?php esc_html_e( 'Occupied History :', 'mjschool' ); ?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Occupied Student', 'mjschool' ); ?> </label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( $assign_data && isset( $assign_data->student_id ) ) {
								echo esc_html( mjschool_student_display_name_with_roll( absint( $assign_data->student_id ) ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Occupied Date', 'mjschool' ); ?> </label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( $assign_data && isset( $assign_data->assign_date ) ) {
								echo esc_html( mjschool_get_date_in_input_box( $assign_data->assign_date ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Created Date', 'mjschool' ); ?> </label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( $assign_data && isset( $assign_data->created_date ) ) {
								echo esc_html( mjschool_get_date_in_input_box( $assign_data->created_date ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<div class="col-md-6 mjschool-popup-padding-15px">
						<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Occupied By', 'mjschool' ); ?> </label>
						<br>
						<label class="mjschool-label-value">
							<?php
							if ( $assign_data && isset( $assign_data->created_by ) ) {
								echo esc_html( ucfirst( mjschool_get_user_name_by_id( absint( $assign_data->created_by ) ) ) );
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
							?>
						</label>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	} elseif ( $type === 'subject_view' ) {
		$subject_data = mjschool_get_subject( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $subject_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Subject details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		
		$teacher_group = array();
		$teacher_ids   = mjschool_teacher_by_subject( $subject_data );
		if ( is_array( $teacher_ids ) ) {
			foreach ( $teacher_ids as $teacher_id ) {
				$teacher_name = mjschool_get_teacher( absint( $teacher_id ) );
				if ( ! empty( $teacher_name ) ) {
					$teacher_group[] = $teacher_name;
				}
			}
		}
		$teachers = ! empty( $teacher_group ) ? implode( ', ', $teacher_group ) : '';
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-subject.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Subject Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Subject Code', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->subject_code ) && ! empty( $subject_data->subject_code ) ) {
							echo esc_html( $subject_data->subject_code );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Subject Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->sub_name ) && ! empty( $subject_data->sub_name ) ) {
							echo esc_html( $subject_data->sub_name );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->class_id ) && ! empty( $subject_data->class_id ) ) {
							echo esc_html( mjschool_get_class_section_name_wise( absint( $subject_data->class_id ), absint( $subject_data->section_id ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( ! empty( $teachers ) ) {
							echo esc_html( $teachers );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Author Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->author_name ) && ! empty( $subject_data->author_name ) ) {
							echo esc_html( $subject_data->author_name );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Edition', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->edition ) && ! empty( $subject_data->edition ) ) {
							echo esc_html( $subject_data->edition );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Syllabus', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->syllabus ) && ! empty( $subject_data->syllabus ) ) {
							$syllabus = $subject_data->syllabus;
							?>
							<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" 
							   href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $syllabus ) ); ?>" 
							   record_id="<?php echo esc_attr( $recoed_id ); ?>">
								<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
							</a>
							<?php
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Create By', 'mjschool' ); ?></label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $subject_data->created_by ) && ! empty( $subject_data->created_by ) ) {
							$author = mjschool_get_user_name_by_id( absint( $subject_data->created_by ) );
							echo esc_html( $author );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				$custom_field_obj = new mjschool_custome_field();
				$module           = 'subject';
				$custom_field_obj->mjschool_show_inserted_custom_field_data_in_popup( $module, $recoed_id );
				?>
			</div>
		</div>
		<?php
	} elseif ( $type === 'examhall_view' ) {
		$exam_hall = mjschool_get_hall_by_id( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $exam_hall ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Exam hall details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-exam.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Exam Hall Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Hall Name', 'mjschool' ); ?></label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_hall->hall_name ) && ! empty( $exam_hall->hall_name ) ) {
							echo esc_html( stripslashes( $exam_hall->hall_name ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"><?php esc_html_e( 'Hall Numeric Value', 'mjschool' ); ?></label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_hall->number_of_hall ) && ! empty( $exam_hall->number_of_hall ) ) {
							echo esc_html( $exam_hall->number_of_hall );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Hall Capacity', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_hall->hall_capacity ) && ! empty( $exam_hall->hall_capacity ) ) {
							echo esc_html( $exam_hall->hall_capacity );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Create Date', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_hall->date ) && ! empty( $exam_hall->date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $exam_hall->date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $exam_hall->description ) && ! empty( $exam_hall->description ) ) {
							echo esc_html( stripslashes( $exam_hall->description ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				$custom_field_obj = new mjschool_custome_field();
				$module           = 'examhall';
				$custom_field_obj->mjschool_show_inserted_custom_field_data_in_popup( $module, $recoed_id );
				?>
			</div>
		</div>
		<?php
	} elseif ( $type === 'event_view' ) {
		$obj_event  = new mjschool_event_Manage();
		$event_data = $obj_event->mjschool_get_single_event( $recoed_id );
		
		// ADDED: Validation
		if ( empty( $event_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Event details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-event.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Event Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Title', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $event_data->event_title ) && ! empty( $event_data->event_title ) ) {
							echo esc_html( stripslashes( $event_data->event_title ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Download File', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $event_data->event_doc ) && ! empty( $event_data->event_doc ) ) {
							?>
							<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $event_data->event_doc ) ); ?>" 
							   class="btn mjschool-custom-padding-0 popup_download_btn" 
							   record_id="<?php echo esc_attr( isset( $event_data->id ) ? $event_data->id : $recoed_id ); ?>">
								<i class="fas fa-download" id="mjschool-download-icon"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
							</a>
							<?php
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Start Date', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $event_data->start_date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $event_data->start_date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'End Date', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $event_data->end_date ) ) {
							echo esc_html( mjschool_get_date_in_input_box( $event_data->end_date ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Start Time', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $event_data->start_time ) ) {
							echo esc_html( mjschool_time_remove_colon_before_am_pm( $event_data->start_time ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'End Time', 'mjschool' ); ?> </label><br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $event_data->end_time ) ) {
							echo esc_html( mjschool_time_remove_colon_before_am_pm( $event_data->end_time ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Description', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $event_data->description ) && ! empty( $event_data->description ) ) {
							echo esc_html( stripslashes( $event_data->description ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<?php
				$custom_field_obj = new mjschool_custome_field();
				$module           = 'event';
				$custom_field_obj->mjschool_show_inserted_custom_field_data_in_popup( $module, $recoed_id );
				?>
			</div>
		</div>
		<?php
	} elseif ( $type === 'assign_transport_view' ) {
		$assign_transport_data = mjschool_get_single_assign_transport_by_id( $recoed_id );
		if ( empty( $assign_transport_data ) ) {
			?>
			<div class="modal-body">
				<p><?php esc_html_e( 'Assigned transport details not found.', 'mjschool' ); ?></p>
			</div>
			<?php
			wp_die();
		}
		$transport_data = null;
		if ( isset( $assign_transport_data->transport_id ) ) {
			$transport_data = mjschool_get_transport_by_id( absint( $assign_transport_data->transport_id ) );
		}
		?>
		<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/mjschool-transportation.png' ); ?>" class="mjschool-popup-image-before-name">
			<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></a>
			<h4 id="myLargeModalLabel" class="modal-title"> <?php esc_html_e( 'Assign Transport Details', 'mjschool' ); ?> </h4>
		</div>
		<div class="modal-body mjschool-view-details-body-assigned-bed mjschool-view-details-body">
			<div class="row">
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Route Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php echo isset( $assign_transport_data->route_name ) ? esc_html( $assign_transport_data->route_name ) : esc_html__( 'N/A', 'mjschool' ); ?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Vehicle Identifier', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( $transport_data && isset( $transport_data->number_of_vehicle ) ) {
							echo esc_html( $transport_data->number_of_vehicle );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Vehicle Registration Number', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( $transport_data && isset( $transport_data->vehicle_reg_num ) ) {
							echo esc_html( $transport_data->vehicle_reg_num );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Driver Name', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( $transport_data && isset( $transport_data->driver_name ) ) {
							echo esc_html( $transport_data->driver_name );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Driver Phone Number', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php 
						if ( $transport_data && isset( $transport_data->driver_phone_num ) ) {
							echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );
							echo ' ' . esc_html( $transport_data->driver_phone_num );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-6 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Route Fare', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value"> 
						<?php 
						if ( isset( $assign_transport_data->route_fare ) ) {
							echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $assign_transport_data->route_fare, 2, '.', '' ) ) );
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
				<div class="col-md-12 mjschool-popup-padding-15px">
					<label class="mjschool-popup-label-heading"> <?php esc_html_e( 'Assigned Student', 'mjschool' ); ?> </label>
					<br>
					<label class="mjschool-label-value">
						<?php
						if ( isset( $assign_transport_data->route_user ) ) {
							$users = json_decode( $assign_transport_data->route_user );
							if ( is_array( $users ) && ! empty( $users ) ) {
								$new_user_array = array();
								foreach ( $users as $user ) {
									$user_id    = absint( $user );
									$first_name = get_user_meta( $user_id, 'first_name', true );
									$last_name  = get_user_meta( $user_id, 'last_name', true );
									if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
										$new_user_array[] = trim( $first_name . ' ' . $last_name );
									}
								}
								if ( ! empty( $new_user_array ) ) {
									echo esc_html( implode( ', ', $new_user_array ) );
								} else {
									esc_html_e( 'N/A', 'mjschool' );
								}
							} else {
								esc_html_e( 'N/A', 'mjschool' );
							}
						} else {
							esc_html_e( 'N/A', 'mjschool' );
						}
						?>
					</label>
				</div>
			</div>
		</div>
		<?php
	}
	wp_die();
}
/**
 * Redirects users to a fallback page when JavaScript is disabled in the browser.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_browser_javascript_check() {
	$plugins_url = plugins_url( 'mjschool/showerrorpage.php' );
	?>
	<noscript>
		<meta http-equiv="refresh" content="0;URL=<?php echo esc_url( $plugins_url ); ?>">
	</noscript>
	<?php
}
/**
 * Displays an alert and redirects the user when they attempt to access a restricted page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_access_right_page_not_access_message() {
	?>
	<div id="mjschool-no-access-trigger" data-redirect-url="?dashboard=mjschool_user" data-trigger="1"></div>
	<?php
}
/**
 * Shows a no-access message and redirects users on the admin side when access is denied.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_access_right_page_not_access_message_admin_side() {
	?>
	<div id="mjschool-admin-no-access-trigger" data-redirect-url="?page=mjschool_dashboard" data-trigger="1"></div>
	<?php
}
/**
 * Retrieves all transport records created by a specific user.
 *
 * @since 1.0.0
 *
 * @param int $eid User ID.
 *
 * @return array List of transport records.
 */
function mjschool_get_all_transport_created_by( $eid ) {
	$user_id = absint( $eid );
	if ( empty( $user_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_transport';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE created_by = %d", $user_id )
	);
	return is_array( $results ) ? $results : array();
}

/**
 * Retrieves all leave entries created by a specific user.
 *
 * @since 1.0.0
 *
 * @param int $eid User ID.
 *
 * @return array List of leave records.
 */
function mjschool_get_all_leave_created_by( $eid ) {
	$user_id = absint( $eid );
	if ( empty( $user_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_leave';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE created_by = %d", $user_id )
	);
	return is_array( $results ) ? $results : array();
}

/**
 * Retrieves all leave entries for a specific student ID (used for parent/child relation).
 *
 * @since 1.0.0
 *
 * @param int $eid Student ID.
 *
 * @return array List of leave records.
 */
function mjschool_get_all_leave_parent_by_child_list( $eid ) {
	$child_id = absint( $eid );
	if ( empty( $child_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_leave';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE student_id = %d", $child_id )
	);
	return is_array( $results ) ? $results : array();
}

/**
 * Retrieves all holiday records created by a specific user.
 *
 * @since 1.0.0
 *
 * @param int $eid User ID.
 *
 * @return array List of holiday records.
 */
function mjschool_get_all_holiday_created_by( $eid ) {
	$user_id = absint( $eid );
	if ( empty( $user_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_holiday';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE created_by = %d", $user_id )
	);
	return is_array( $results ) ? $results : array();
}

/**
 * Retrieves the latest three holiday records created by a specific user (for dashboard display).
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID.
 *
 * @return array List of holiday records.
 */
function mjschool_get_all_holiday_created_by_dashboard( $user_id ) {
	$user_id = absint( $user_id );
	if ( empty( $user_id ) ) {
		return array();
	}
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_holiday';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_name} WHERE created_by = %d ORDER BY holiday_id DESC LIMIT 3", $user_id )
	);
	return is_array( $results ) ? $results : array();
}

/**
 * Retrieves user access rights based on role and specific page link.
 *
 * @since 1.0.0
 *
 * @param int    $user_id   User ID.
 * @param string $page_link Page identifier.
 *
 * @return array|null Access rights array or null if not found.
 */
function mjschool_get_user_role_wise_access_right_array_in_api( $user_id, $page_link ) {
	$user_id   = absint( $user_id );
	$page_link = sanitize_text_field( $page_link );
	if ( empty( $user_id ) || empty( $page_link ) ) {
		return null;
	}
	$school_obj = new MJSchool_Management( $user_id );
	$role       = $school_obj->role;
	$menu = array();
	if ( $role === 'student' ) {
		$menu = get_option( 'mjschool_access_right_student' );
	} elseif ( $role === 'teacher' ) { 
		$menu = get_option( 'mjschool_access_right_teacher' );
	}
	if ( ! is_array( $menu ) || empty( $menu ) ) {
		return null;
	}
	foreach ( $menu as $key1 => $value1 ) {
		if ( ! is_array( $value1 ) ) {
			continue;
		}
		foreach ( $value1 as $key => $value ) {
			if ( ! is_array( $value ) || ! isset( $value['page_link'] ) ) {
				continue;
			}
			if ( $page_link === $value['page_link'] ) {
				$menu_array1 = array(
					'view'     => isset( $value['view'] ) ? $value['view'] : '0',
					'own_data' => isset( $value['own_data'] ) ? $value['own_data'] : '0',
					'add'      => isset( $value['add'] ) ? $value['add'] : '0',
					'edit'     => isset( $value['edit'] ) ? $value['edit'] : '0',
					'delete'   => isset( $value['delete'] ) ? $value['delete'] : '0',
				);
				return $menu_array1;
			}
		}
	}
	return null;
}

/**
 * Generates an array of dates between two given dates (inclusive).
 *
 * @since 1.0.0
 *
 * @param string $start Start date (Y-m-d).
 * @param string $end   End date (Y-m-d).
 *
 * @return array List of dates in Y-m-d format.
 */
function mjschool_get_dates_from_range( $start, $end ) {
	$array    = array();
	$interval = new DateInterval( 'P1D' );
	$realEnd  = new DateTime( $end );
	$realEnd->add( $interval );
	$period = new DatePeriod( new DateTime( $start ), $interval, $realEnd );
	
	foreach ( $period as $date ) {
		$array[] = $date->format( 'Y-m-d' );
	}
	
	return $array;
}

/**
 * Validates the username and password fields before login and redirects if empty.
 *
 * @since 1.0.0
 *
 * @param string $login    Login action.
 * @param string $username Entered username.
 * @param string $password Entered password.
 *
 * @return void
 */
function mjschool_check_username_password( $login, $username, $password ) {
	$referrer = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	if ( empty( $referrer ) ) {
		return;
	}
	if ( strpos( $referrer, 'wp-login' ) !== false || strpos( $referrer, 'wp-admin' ) !== false ) {
		return;
	}
	if ( empty( $username ) || empty( $password ) ) {
		$login_page = absint( get_option( 'mjschool_login_page' ) );
		if ( ! empty( $login_page ) ) {
			wp_safe_redirect( get_permalink( $login_page ) . '?login=empty' );
			exit;
		}
	}
}
/**
 * Generate the fees payment invoice PDF and send it via email.
 *
 * This function fetches fee payment details, generates a PDF invoice using mPDF,
 * stores the file inside the uploads/invoices directory, and emails the invoice
 * to the specified recipients. It supports both invoice layout formats and
 * dynamically populates student, fee, tax, discount, and transaction details.
 *
 * @since 1.0.0
 *
 * @param array|string $emails       Recipient email address(es) to send the invoice PDF.
 * @param string       $subject      Subject of the email.
 * @param string       $message      Email body content.
 * @param int          $fees_pay_id  ID of the fees payment record used to generate the invoice.
 *
 * @return void
 */
function mjschool_send_mail_paid_invoice_pdf( $emails, $subject, $message, $fees_pay_id ) {
	// ADDED: Input validation
	if ( empty( $emails ) || empty( $fees_pay_id ) ) {
		error_log( 'Invoice PDF: Missing required parameters' );
		return false;
	}
	
	$fees_pay_id = absint( $fees_pay_id );
	if ( empty( $fees_pay_id ) ) {
		error_log( 'Invoice PDF: Invalid fees payment ID' );
		return false;
	}
	
	// Sanitize inputs
	$emails  = is_array( $emails ) ? array_map( 'sanitize_email', $emails ) : sanitize_email( $emails );
	$subject = sanitize_text_field( $subject );
	$message = wp_kses_post( $message );
	
	// ADDED: Validate data exists
	$fees_detail_result = mjschool_get_single_fees_payment_record( $fees_pay_id );
	if ( empty( $fees_detail_result ) || ! isset( $fees_detail_result->student_id ) ) {
		error_log( 'Invoice PDF: Fees payment record not found: ' . $fees_pay_id );
		return false;
	}
	
	$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
	$invoice_number             = mjschool_generate_invoice_number( $fees_pay_id );
	
	// FIXED: Changed permissions from 0777 to 0755
	$document_dir  = WP_CONTENT_DIR . '/uploads/invoices/';
	$document_path = $document_dir;
	
	if ( ! file_exists( $document_path ) ) {
		if ( ! mkdir( $document_path, 0755, true ) && ! is_dir( $document_path ) ) {
			error_log( 'Invoice PDF: Failed to create directory: ' . $document_path );
			return false;
		}
	}
	
	// ADDED: Try-catch for PDF generation
	try {
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf = new Mpdf\Mpdf();
		
		$stylesheet = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css' );
		
		$mpdf->WriteHTML( '<html>' );
		$mpdf->WriteHTML( '<head>' );
		$mpdf->WriteHTML( '<style></style>' );
		$mpdf->WriteHTML( $stylesheet, 1 );
		$mpdf->WriteHTML( '</head>' );
		$mpdf->WriteHTML( '<body>' );
		$mpdf->WriteHTML( '<div class="modal-body">' );
		
		// FIXED: Consistent strict comparison
		$format = absint( get_option( 'mjschool_invoice_option' ) );
		
		if ( 1 !== $format ) {
			$mpdf->WriteHTML( '<h3>' . esc_html( get_option( 'mjschool_name' ) ) . '</h3>' );
			$mpdf->WriteHTML( '<table style="float: left;position: absolute;vertical-align: top;background-repeat: no-repeat;">' );
			$mpdf->WriteHTML( '<tbody>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td>' );
			$mpdf->WriteHTML( '<img class="mjschool-invoice-image mjschool-float-left mjschool-invoice-image-model" src="' . esc_url( plugins_url( '/mjschool/assets/images/listpage_icon/invoice.png' ) ) . '" width="100%">' );
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		}
		
		if ( 1 === $format ) {
			$school_name    = esc_html( get_option( 'mjschool_name' ) );
			$email          = esc_html( get_option( 'mjschool_email' ) );
			$phone          = esc_html( get_option( 'mjschool_contact_number' ) );
			$school_address = esc_html( get_option( 'mjschool_address' ) );
			
			$mpdf->WriteHTML( '<table style="border: 2px solid #000; width: 100%; margin: 6px 0 0 6px; padding: 20px;">
				<tr>
					<td style="width: 25%; vertical-align: top;">
						<div style="border-radius: 50px;">
							<img src="' . esc_url( get_option( 'mjschool_logo' ) ) . '" style="height: 130px; border-radius: 50%; background-repeat: no-repeat; background-size: cover; margin-top: 3px;">
						</div>
					</td>
					<td style="width: 75%; padding-top: 10px; text-align: center;">
						<div style="font-weight: bold; color: #1B1B8D; font-size: 24px; line-height: 30px; width: 100% !important">' . $school_name . '</div>
						<div style="font-size: 17px; line-height: 26px; margin-top: 5px;">' . $school_address . '</div>
						<div style="font-size: 17px; line-height: 26px; margin-top: 5px;">
							' . esc_html__( 'E-mail', 'mjschool' ) . ': ' . $email . ' &nbsp;&nbsp;&nbsp; ' .
					esc_html__( 'Phone', 'mjschool' ) . ': ' . $phone . '
						</div>
					</td>
				</tr>
			</table>' );
		} else {
			$mpdf->WriteHTML( '<table style="float: left;width: 100%;position: absolute!important;margin-top:-160px;">' );
			$mpdf->WriteHTML( '<tbody>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="80%">' );
			$mpdf->WriteHTML( '<table>' );
			$mpdf->WriteHTML( '<tbody>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="10%">' );
			$mpdf->WriteHTML( '<img class="system_logo" src="' . esc_url( get_option( 'mjschool_logo' ) ) . '">' );
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '<td width="90%" style="padding-left: 20px;">' );
			$mpdf->WriteHTML( '<h4 class="mjschool-popup-label-heading">' . esc_html__( 'Address', 'mjschool' ) . '</h4>' );
			$mpdf->WriteHTML( '<label class="mjschool-label-value mjschool-word-break-all" style="font-size: 16px !important;color: #333333 !important;font-weight: 400;">' . esc_html( chunk_split( get_option( 'mjschool_address' ), 100, "<br>" ) ) . '</label><br>' );
			$mpdf->WriteHTML( '<h4 class="mjschool-popup-label-heading">' . esc_html__( 'Email', 'mjschool' ) . '</h4>' );
			$mpdf->WriteHTML( '<label style="font-size: 16px !important;color: #333333 !important;font-weight: 400;" class="mjschool-label-value mjschool-word-break-all">' . esc_html( get_option( 'mjschool_email' ) ) . '</label><br>' );
			$mpdf->WriteHTML( '<h4 class="mjschool-popup-label-heading">' . esc_html__( 'Phone', 'mjschool' ) . '</h4>' );
			$mpdf->WriteHTML( '<label style="font-size: 16px !important;color: #333333 !important;font-weight: 400;" class="mjschool-label-value">' . esc_html( get_option( 'mjschool_contact_number' ) ) . '</label>' );
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		}
		
		$mpdf->WriteHTML( '<br>' );
		
		if ( 1 === $format ) {
			$student_id = absint( $fees_detail_result->student_id );
			
			// FIXED: Validate get_userdata()
			$patient = get_userdata( $student_id );
			if ( empty( $patient ) || ! isset( $patient->display_name ) ) {
				error_log( 'Invoice PDF: Invalid student ID: ' . $student_id );
				return false;
			}
			
			$invoice_number = isset( $invoice_number ) ? $invoice_number : '';
			$display_name   = ucwords( $patient->display_name );
			$address        = esc_html( get_user_meta( $student_id, 'address', true ) );
			$city           = esc_html( get_user_meta( $student_id, 'city', true ) );
			$zip            = esc_html( get_user_meta( $student_id, 'zip_code', true ) );
			
			// FIXED: Removed undefined variable checks
			$issue_date = isset( $fees_detail_result->paid_by_date ) && ! empty( $fees_detail_result->paid_by_date ) ? $fees_detail_result->paid_by_date : gmdate( 'Y-m-d' );
			
			$payment_status       = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
			$payment_status_color = 'N/A';
			
			if ( 'Fully Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:green;">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
			} elseif ( 'Partially Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:#800080;">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
			} elseif ( 'Not Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:red;">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
			}
			
			$mpdf->WriteHTML( '<table style="width:100%; border: 2px solid #000; padding: 20px 20px 5px 20px; margin-bottom: 0; margin-top: 0;">
				<tr>
					<td style="width:65%; vertical-align: top;">
						<b>' . esc_html__( 'Bill To', 'mjschool' ) . ':</b> ' . esc_html( mjschool_student_display_name_with_roll( $student_id ) ) . '
					</td>
					<td style="width:35%; vertical-align: top;">
						<b>' . esc_html__( 'Invoice Number', 'mjschool' ) . ':</b> ' . esc_html( $invoice_number ) . '
					</td>
				</tr>
				<tr>
					<td style="width:64%; vertical-align: top; padding-top: 10px;">
						<b>' . esc_html__( 'Address', 'mjschool' ) . ':</b> ' . $address . '<br>' . $city . ', ' . $zip . '
					</td>
					<td style="width:35.3%; vertical-align: top; padding-top: 10px;">
						<b>' . esc_html__( 'Issue Date', 'mjschool' ) . ':</b> ' . esc_html( mjschool_get_date_in_input_box( gmdate( 'Y-m-d', strtotime( $issue_date ) ) ) ) . '<br>
						<b>' . esc_html__( 'Status', 'mjschool' ) . ':</b> ' . $payment_status_color . '
					</td>
				</tr>
			</table>' );
		} else {
			$mpdf->WriteHTML( '<table>' );
			$mpdf->WriteHTML( '<tbody>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="40%">' );
			$mpdf->WriteHTML( '<h3 class="mjschool-billed-to-lable mjschool-invoice-model-heading mjschool-bill-to-width-12px">' . esc_html__( 'Bill To', 'mjschool' ) . ': </h3>' );
			
			$student_id = absint( $fees_detail_result->student_id );
			
			// FIXED: Validate get_userdata()
			$patient = get_userdata( $student_id );
			if ( empty( $patient ) || ! isset( $patient->display_name ) ) {
				$mpdf->WriteHTML( '<h3 class="display_name mjschool-invoice-width-100px">' . esc_html__( 'N/A', 'mjschool' ) . '</h3>' );
			} else {
				$mpdf->WriteHTML( '<h3 class="display_name mjschool-invoice-width-100px">' . esc_html( chunk_split( ucwords( $patient->display_name ), 30, '<BR>' ) ) . '</h3>' );
			}
			
			// FIXED: Removed echo statements, get address data
			if ( $patient ) {
				$address = get_user_meta( $student_id, 'address', true );
				$city    = get_user_meta( $student_id, 'city', true );
				$zip     = get_user_meta( $student_id, 'zip_code', true );
				
				$mpdf->WriteHTML( '<div>' . esc_html( chunk_split( $address, 30, '<BR>' ) ) . esc_html( $city ) . ',<BR>' . esc_html( $zip ) . ',<BR></div>' );
			}
			
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '<td width="15%">' );
			$mpdf->WriteHTML( '<label style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;">' . esc_html__( 'Invoice Number', 'mjschool' ) . '</label>: <label class="mjschool-invoice-model-value" style="font-weight: 600;color: #333333;font-size: 16px !important;">' . esc_html( $invoice_number ) . '</label><br>' );
			
			// FIXED: Removed dead code for issue_date
			$issue_date = isset( $fees_detail_result->paid_by_date ) && ! empty( $fees_detail_result->paid_by_date )
				? $fees_detail_result->paid_by_date
				: gmdate( 'Y-m-d' );
			
			$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
			
			$mpdf->WriteHTML( '<label style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;">' . esc_html__( 'Date', 'mjschool' ) . '</label>: <label class="mjschool-invoice-model-value" style="font-weight: 600;color: #333333;font-size: 16px !important;">' . esc_html( mjschool_get_date_in_input_box( gmdate( 'Y-m-d', strtotime( $issue_date ) ) ) ) . '</label><br>' );
			
			$payment_status_color = '';
			if ( 'Fully Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:green;">' . esc_html__( 'Fully Paid', 'mjschool' ) . '</span>';
			} elseif ( 'Partially Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:#3895d3;">' . esc_html__( 'Partially Paid', 'mjschool' ) . '</span>';
			} elseif ( 'Not Paid' === $payment_status ) {
				$payment_status_color = '<span style="color:red;">' . esc_html__( 'Not Paid', 'mjschool' ) . '</span>';
			}
			
			$mpdf->WriteHTML( '<label style="color: #818386 !important;font-size: 14px !important;text-transform: uppercase;font-weight: 500;line-height: 0px;">' . esc_html__( 'Status', 'mjschool' ) . ' </label>: <label class="mjschool-invoice-model-value" style="font-weight: 600;color: #333333;font-size: 16px !important;">' . $payment_status_color . '</label>' );
			$mpdf->WriteHTML( '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		}
		
		$mpdf->WriteHTML( '<h4 style="font-size: 16px;font-weight: 600;color: #333333;">' . esc_html__( 'Invoice Entry', 'mjschool' ) . '</h4>' );
		
		if ( 1 === $format ) {
			// ADDED: Validate fees_id
			if ( ! isset( $fees_detail_result->fees_id ) || empty( $fees_detail_result->fees_id ) ) {
				error_log( 'Invoice PDF: Missing fees_id' );
				return false;
			}
			
			$fees_id = explode( ',', $fees_detail_result->fees_id );
			$x       = 1;
			$amounts = 0;
			
			$mpdf->WriteHTML( '<table width="100%" style="border-collapse: collapse; border: 1px solid black;">' );
			$mpdf->WriteHTML( '<thead style="background-color: #b8daff;">' );
			$mpdf->WriteHTML( '<tr>' );
			$header_style = 'text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black; background-color: #b8daff; font-size: 14px;';
			$mpdf->WriteHTML( '<th style="' . $header_style . ' width: 15%;">Number</th>' );
			$mpdf->WriteHTML( '<th style="' . $header_style . ' width: 20%;">' . esc_html__( 'Date', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th style="' . $header_style . '">' . esc_html__( 'Fees Type', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th style="' . $header_style . ' width: 15%;">' . esc_html__( 'Total', 'mjschool' ) . ' ( ' . esc_html( mjschool_get_currency_symbol() ) . ' )</th>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</thead>' );
			$mpdf->WriteHTML( '<tbody>' );
			
			foreach ( $fees_id as $id ) {
				$id = absint( $id );
				if ( empty( $id ) ) {
					continue;
				}
				
				$obj_feespayment = new mjschool_feespayment();
				$amount          = $obj_feespayment->mjschool_feetype_amount_data( $id );
				$amounts        += (float) $amount;
				
				$td_style = 'text-align: center; font-weight: 600; color: black; padding: 10px; border: 1px solid black; font-size: 14px;';
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<td style="' . $td_style . '">' . esc_html( $x ) . '</td>' );
				$mpdf->WriteHTML( '<td style="' . $td_style . '">' . esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ) . '</td>' );
				$mpdf->WriteHTML( '<td style="' . $td_style . '">' . esc_html( mjschool_get_fees_term_name( $id ) ) . '</td>' );
				$mpdf->WriteHTML( '<td style="' . $td_style . '">' . esc_html( number_format( (float) $amount, 2, '.', '' ) ) . '</td>' );
				$mpdf->WriteHTML( '</tr>' );
				$x++;
			}
			
			$sub_total = $amounts;
			
			// ADDED: Validate before use
			$tax_name = '';
			if ( isset( $fees_detail_result->tax ) && ! empty( $fees_detail_result->tax ) ) {
				$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
			}
			
			$discount_name = '';
			if ( isset( $fees_detail_result->discount ) && ! empty( $fees_detail_result->discount ) ) {
				$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
			}
			
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		} else {
			$mpdf->WriteHTML( '<table class="table table-bordered" width="100%">' );
			$mpdf->WriteHTML( '<thead style="background-color: #F2F2F2 !important;">' );
			$mpdf->WriteHTML( '<tr style="background-color: #F2F2F2 !important;">' );
			$mpdf->WriteHTML( '<th class="mjschool-align-left mjschool_border_padding_15px">#</th>' );
			$mpdf->WriteHTML( '<th class="mjschool-align-left mjschool_border_padding_15px">' . esc_html__( 'Date', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th class="mjschool-align-left mjschool_border_padding_15px">' . esc_html__( 'Fees Type', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th class="mjschool-align-left" style="color: #818386 !important;font-weight: 600;border-bottom-color: #E1E3E5 !important;padding: 15px;">' . esc_html__( 'Total', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</thead>' );
			
			// ADDED: Validate fees_id
			if ( ! isset( $fees_detail_result->fees_id ) || empty( $fees_detail_result->fees_id ) ) {
				error_log( 'Invoice PDF: Missing fees_id' );
				return false;
			}
			
			$fees_id = explode( ',', $fees_detail_result->fees_id );
			$x       = 1;
			$amounts = 0;
			
			foreach ( $fees_id as $id ) {
				$id = absint( $id );
				if ( empty( $id ) ) {
					continue;
				}
				
				$mpdf->WriteHTML( '<tbody>' );
				$mpdf->WriteHTML( '<tr style=" border-bottom: 1px solid #E1E3E5 !important;">' );
				$mpdf->WriteHTML( '<td class="align-center mjschool_tables_bottoms">' . esc_html( $x ) . '</td>' );
				$mpdf->WriteHTML( '<td class="align-center mjschool_tables_bottoms">' . esc_html( mjschool_get_date_in_input_box( $fees_detail_result->created_date ) ) . '</td>' );
				$mpdf->WriteHTML( '<td class="align-center mjschool_tables_bottoms">' . esc_html( mjschool_get_fees_term_name( $id ) ) . '</td>' );
				
				$obj_feespayment = new mjschool_feespayment();
				$amount          = $obj_feespayment->mjschool_feetype_amount_data( $id );
				$amounts        += (float) $amount;
				$T_amount        = mjschool_currency_symbol_position_language_wise( number_format( (float) $amount, 2, '.', '' ) );
				
				$mpdf->WriteHTML( '<td class="align-center mjschool_tables_bottoms">' . esc_html( $T_amount ) . '</td>' );
				$mpdf->WriteHTML( '</tr>' );
				$mpdf->WriteHTML( '</tbody>' );
				$x++;
			}
			
			$sub_total = $amounts;
			
			// ADDED: Validate before use
			$tax_name = '';
			if ( isset( $fees_detail_result->tax ) && ! empty( $fees_detail_result->tax ) ) {
				$tax_name = mjschool_tax_name_by_tax_id_array_for_invoice( esc_html( $fees_detail_result->tax ) );
			}
			
			$discount_name = '';
			if ( isset( $fees_detail_result->discount ) && ! empty( $fees_detail_result->discount ) ) {
				$discount_name = mjschool_get_discount_name( $fees_detail_result->discount, $fees_detail_result->discount_type );
			}
			
			$mpdf->WriteHTML( '</table>' );
		}
		
		// Totals section
		if ( 1 === $format ) {
			$Due_amount = isset( $fees_detail_result->total_amount ) && isset( $fees_detail_result->fees_paid_amount )
				? (float) $fees_detail_result->total_amount - (float) $fees_detail_result->fees_paid_amount
				: 0;
			
			$mpdf->WriteHTML( '<table width="100%" style="border-collapse: collapse; border: 1px solid black; margin-top:10px;">' );
			
			// Subtotal
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<th style="width: 85%; text-align: right; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;">' . esc_html__( 'Sub Total', 'mjschool' ) . ' :</th>' );
			$mpdf->WriteHTML( '<td style="width: 15%; text-align: left; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;">' . esc_html( number_format( $sub_total, 2, '.', '' ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			
			// Discount
			if ( isset( $fees_detail_result->discount_amount ) && 0 != $fees_detail_result->discount_amount ) {
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<th style="text-align: right; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;">' . esc_html__( 'Discount Amount', 'mjschool' ) . ' ( ' . esc_html( $discount_name ) . ' ) :</th>' );
				$mpdf->WriteHTML( '<td style="text-align: left; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;">-' . esc_html( number_format( (float) $fees_detail_result->discount_amount, 2, '.', '' ) ) . '</td>' );
				$mpdf->WriteHTML( '</tr>' );
			}
			
			// Tax
			if ( isset( $fees_detail_result->tax_amount ) && 0 != $fees_detail_result->tax_amount ) {
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<th style="text-align: right; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;">' . esc_html__( 'Tax Amount', 'mjschool' ) . ' ( ' . esc_html( $tax_name ) . ' ) :</th>' );
				$mpdf->WriteHTML( '<td style="text-align: left; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;">+' . esc_html( number_format( (float) $fees_detail_result->tax_amount, 2, '.', '' ) ) . '</td>' );
				$mpdf->WriteHTML( '</tr>' );
			}
			
			// Payment Made
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<th style="text-align: right; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;">' . esc_html__( 'Payment Made :', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<td style="text-align: left; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;">' . esc_html( number_format( (float) $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			
			// Due Amount
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<th style="text-align: right; font-weight: 600; background-color: #b8daff; padding: 10px; border: 1px solid black; font-size: 14px;">' . esc_html__( 'Due Amount :', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<td style="text-align: left; padding: 10px; font-weight: 600; border: 1px solid black; font-size: 14px;">' . esc_html( number_format( $Due_amount, 2, '.', '' ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</table>' );
		} else {
			$mpdf->WriteHTML( '<table width="100%" border="0">' );
			$mpdf->WriteHTML( '<tbody>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="80%" align="right" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;">' . esc_html__( 'Sub Total :', 'mjschool' ) . '</td>' );
			$mpdf->WriteHTML( '<td align="right" style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $sub_total, 2, '.', '' ) ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			
			if ( isset( $fees_detail_result->discount_amount ) && 0 != $fees_detail_result->discount_amount ) {
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<td width="80%" align="right" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;">' . esc_html__( 'Discount Amount', 'mjschool' ) . '( ' . esc_html( $discount_name ) . ' )  :</td>' );
				$mpdf->WriteHTML( '<td align="right" style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;"><span> -' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $fees_detail_result->discount_amount, 2, '.', '' ) ) ) . ' </span></td>' );
				$mpdf->WriteHTML( '</tr>' );
			}
			
			if ( isset( $fees_detail_result->tax_amount ) && 0 != $fees_detail_result->tax_amount ) {
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<td width="80%" align="right" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;">' . esc_html__( 'Tax Amount', 'mjschool' ) . '( ' . esc_html( $tax_name ) . ' )  :</td>' );
				$mpdf->WriteHTML( '<td align="right" style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;"><span> +' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $fees_detail_result->tax_amount, 2, '.', '' ) ) ) . ' </span></td>' );
				$mpdf->WriteHTML( '</tr>' );
			}
			
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="80%" align="right" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;">' . esc_html__( 'Payment Made :', 'mjschool' ) . '</td>' );
			$mpdf->WriteHTML( '<td align="right" style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $fees_detail_result->fees_paid_amount, 2, '.', '' ) ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<td width="80%" align="right" style="padding-bottom: 10px;font-size: 18px;color: #818386 !important;font-weight: 500;">' . esc_html__( 'Due Amount :', 'mjschool' ) . '</td>' );
			$Due_amount = isset( $fees_detail_result->total_amount ) && isset( $fees_detail_result->fees_paid_amount )
				? (float) $fees_detail_result->total_amount - (float) $fees_detail_result->fees_paid_amount
				: 0;
			$mpdf->WriteHTML( '<td align="right" style="padding-bottom: 10px;font-size: 18px;color: #333333 !important;font-weight: 700;">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ) . '</td>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		}
		
		// Grand Total
		$mpdf->WriteHTML( '<table style="width:100%">' );
		$mpdf->WriteHTML( '<tbody>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td width="62%" align="left"></td>' );
		$mpdf->WriteHTML( '<td align="right" style="float:right; background-color:' . esc_attr( get_option( 'mjschool_system_color_code' ) ) . ' !important;color: #fff !important;">' );
		$mpdf->WriteHTML( '<table style="background-color: ' . esc_attr( get_option( 'mjschool_system_color_code' ) ) . ' !important;color: #fff !important;">' );
		$mpdf->WriteHTML( '<tbody>' );
		$mpdf->WriteHTML( '<tr>' );
		
		$subtotal    = isset( $fees_detail_result->total_amount ) ? (float) $fees_detail_result->total_amount : 0;
		$paid_amount = isset( $fees_detail_result->fees_paid_amount ) ? (float) $fees_detail_result->fees_paid_amount : 0;
		$grand_total = $subtotal - $paid_amount;
		
		$mpdf->WriteHTML( '<td style="background-color: ' . esc_attr( get_option( 'mjschool_system_color_code' ) ) . ' !important;color: #fff !important;padding:10px">' );
		$mpdf->WriteHTML( '<h3>' . esc_html__( 'Grand Total', 'mjschool' ) . '</h3>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '<td style="background-color: ' . esc_attr( get_option( 'mjschool_system_color_code' ) ) . ' !important;color: #fff !important;padding:10px;">' );
		
		$formatted_amount = number_format( $subtotal, 2, '.', '' );
		$currency         = mjschool_get_currency_symbol();
		
		$mpdf->WriteHTML( '<h3>( ' . esc_html( $currency ) . ' )' . esc_html( $formatted_amount ) . '</h3>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		$mpdf->WriteHTML( '</div>' );
		
		// Signature
		$mpdf->WriteHTML( '<div style="border: 2px solid; width:100%; float: left; margin-bottom:12px; padding: 15px 10px; overflow: hidden; margin-top: 4px;">
			<div style="width: 100%; text-align: right;">
				<img src="' . esc_url( get_option( 'mjschool_principal_signature' ) ) . '" class="mjschool_width_100px" />
				<div style="border-top: 1px solid #000; width: 150px; margin: 5px 0 0 auto;"></div>
				<div class="mjschool_margin_top_5px">' . esc_html__( 'Principal Signature', 'mjschool' ) . '</div>
			</div>
		</div>' );
		
		$mpdf->WriteHTML( '</body>' );
		$mpdf->WriteHTML( '</html>' );
		
		$invoice_file = $document_path . 'invoice.pdf';
		$mpdf->Output( $invoice_file, 'F' );
		
		// ADDED: Verify file was created
		if ( ! file_exists( $invoice_file ) ) {
			error_log( 'Invoice PDF: Failed to create PDF file' );
			return false;
		}
		// Send email
		if ( 1 == get_option( 'mjschool_mail_notification' ) ) {
			$school  = get_option( 'mjschool_name' );
			$headers = '';
			$admin_email = get_option( 'admin_email' );
			$headers    .= 'From: ' . $school . ' <' . $admin_email . '>' . "\r\n";
			$headers    .= "MIME-Version: 1.0\r\n";
			$headers    .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$email_template = mjschool_get_mail_content_with_template_design( $message );
			$sent = wp_mail( $emails, $subject, $email_template, $headers, array( $invoice_file ) );
			if ( ! $sent ) {
				error_log( 'Invoice PDF: Failed to send email to: ' . ( is_array( $emails ) ? implode( ', ', $emails ) : $emails ) );
			}
		}
		if ( file_exists( $invoice_file ) ) {
			if ( ! unlink( $invoice_file ) ) {
				error_log( 'Invoice PDF: Failed to delete temporary file: ' . $invoice_file );
			}
		}
		return true;
	} catch ( Exception $e ) {
		error_log( 'Invoice PDF generation failed: ' . $e->getMessage() );
		return false;
	}
}
/**
 * Generates a translated invoice PDF using mPDF for a specific student fee payment.
 *
 * Creates a PDF file containing invoice details such as student information,
 * fee items, totals, payment history, and school details. The PDF is stored
 * inside `/wp-content/uploads/translate_invoice_pdf/` and the function returns
 * the URL to the generated file.
 *
 * @since 1.0.0
 *
 * @param int    $id      Fees payment ID.
 * @param string $student Student identifier (used in PDF filename).
 *
 * @return string URL of the generated PDF file.
 */
function mjschool_api_translate_invoice_pdf( $id, $student ) {
	// ADDED: Input validation
	$fees_pay_id = absint( $id );
	$student     = sanitize_file_name( $student );
	
	if ( empty( $fees_pay_id ) || empty( $student ) ) {
		error_log( 'Translate Invoice PDF: Invalid parameters' );
		return false;
	}
	
	// ADDED: Validate data exists
	$fees_detail_result = mjschool_get_single_fees_payment_record( $fees_pay_id );
	if ( empty( $fees_detail_result ) || ! isset( $fees_detail_result->student_id ) ) {
		error_log( 'Translate Invoice PDF: Fees payment record not found: ' . $fees_pay_id );
		return false;
	}
	
	$fees_history_detail_result = mjschool_get_payment_history_by_fees_pay_id( $fees_pay_id );
	
	// FIXED: Changed permissions from 0777 to 0755
	$document_dir  = WP_CONTENT_DIR . '/uploads/translate_invoice_pdf/';
	$document_path = $document_dir;
	
	if ( ! file_exists( $document_path ) ) {
		if ( ! mkdir( $document_path, 0755, true ) && ! is_dir( $document_path ) ) {
			error_log( 'Translate Invoice PDF: Failed to create directory: ' . $document_path );
			return false;
		}
	}
	
	// ADDED: Try-catch for PDF generation
	try {
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf                 = new Mpdf\Mpdf();
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		
		$stylesheet = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css' );
		
		$mpdf->WriteHTML( '<html>' );
		$mpdf->WriteHTML( '<head>' );
		$mpdf->WriteHTML( '<style></style>' );
		$mpdf->WriteHTML( $stylesheet, 1 );
		$mpdf->WriteHTML( '</head>' );
		$mpdf->WriteHTML( '<body>' );
		$mpdf->WriteHTML( '<div class="modal-body">' );
		$mpdf->WriteHTML( '<div id="mjschool-invoice-print" class="print-box" width="100%">' );
		
		$mpdf->WriteHTML( '<table width="100%" border="0">' );
		$mpdf->WriteHTML( '<tbody>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td width="70%">' );
		$mpdf->WriteHTML( '<img style="max-height:80px;" src="' . esc_url( get_option( 'mjschool_logo' ) ) . '">' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '<td align="right" width="24%">' );
		$mpdf->WriteHTML( '<h5>' );
		
		$issue_date = isset( $fees_detail_result->paid_by_date ) && ! empty( $fees_detail_result->paid_by_date )
			? $fees_detail_result->paid_by_date
			: gmdate( 'Y-m-d' );
		
		$mpdf->WriteHTML( '' . esc_html__( 'Issue Date', 'mjschool' ) . ' : ' . esc_html( mjschool_get_date_in_input_box( gmdate( 'Y-m-d', strtotime( $issue_date ) ) ) ) . '</h5>' );
		$mpdf->WriteHTML( '<br>' );
		$mpdf->WriteHTML( '<h5>' );
		
		$payment_status = mjschool_get_payment_status( $fees_detail_result->fees_pay_id );
		$mpdf->WriteHTML( '' . esc_html__( 'status', 'mjschool' ) . ' : ' . esc_html( $payment_status ) . '</h5>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		
		$mpdf->WriteHTML( '<hr class="mjschool-hr-margin-new">' );
		
		$mpdf->WriteHTML( '<table width="100%" border="0">' );
		$mpdf->WriteHTML( '<tbody>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td class="col-md-6">' );
		$mpdf->WriteHTML( '<h4>' . esc_html__( 'Payment From', 'mjschool' ) . '</h4>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '<td class="col-md-6 pull-right mjchool_text_align_right">' );
		$mpdf->WriteHTML( '<h4>' . esc_html__( 'Bill To', 'mjschool' ) . '</h4>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td valign="top" class="col-md-6">' );
		$mpdf->WriteHTML( esc_html( get_option( 'mjschool_name' ) ) . '<br>' );
		$mpdf->WriteHTML( esc_html( get_option( 'mjschool_address' ) ) . ',' );
		$mpdf->WriteHTML( esc_html( get_option( 'mjschool_contry' ) ) . '<br>' );
		$mpdf->WriteHTML( esc_html( get_option( 'mjschool_contact_number' ) ) . '<br>' );
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '<td valign="top" class="col-md-6 mjchool_text_align_right">' );
		
		$student_id = absint( $fees_detail_result->student_id );
		
		// ADDED: Validate get_userdata()
		$student_data = get_userdata( $student_id );
		if ( empty( $student_data ) || ! isset( $student_data->display_name ) ) {
			$mpdf->WriteHTML( esc_html__( 'Unknown Student', 'mjschool' ) . '<br>' );
		} else {
			$class_id   = isset( $student_data->class_name ) ? absint( $student_data->class_name ) : 0;
			$section_id = isset( $student_data->class_section ) ? absint( $student_data->class_section ) : 0;
			
			$mpdf->WriteHTML( esc_html( $student_data->display_name ) . '<br>' );
			
			if ( $class_id ) {
				$class_name = mjschool_get_class_name( $class_id );
				$mpdf->WriteHTML( 'Class Name <b>' . esc_html( $class_name ) . '</b><br>' );
			}
			
			if ( $section_id ) {
				$section_name = mjschool_get_section_name( $section_id );
				$mpdf->WriteHTML( 'Section Name <b>' . esc_html( $section_name ) . '</b><br>' );
			}
			
			$mpdf->WriteHTML( 'Student ID <b>' . esc_html( get_user_meta( $student_id, 'roll_id', true ) ) . '</b><br>' );
			$mpdf->WriteHTML( esc_html( get_user_meta( $student_id, 'address', true ) ) . ',' );
			$mpdf->WriteHTML( esc_html( get_user_meta( $student_id, 'city', true ) ) . '<br>' );
			$mpdf->WriteHTML( esc_html( get_user_meta( $student_id, 'zip_code', true ) ) . '<br>' );
			$mpdf->WriteHTML( esc_html( get_user_meta( $student_id, 'state', true ) ) . ',' );
			$mpdf->WriteHTML( esc_html( get_option( 'mjschool_contry' ) ) . ',' );
			$mpdf->WriteHTML( esc_html( get_user_meta( $student_id, 'mobile', true ) ) . '<br>' );
		}
		
		$mpdf->WriteHTML( '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		
		$mpdf->WriteHTML( '<hr class="mjschool-hr-margin-new">' );
		
		$mpdf->WriteHTML( '<div class="table-responsive">' );
		$mpdf->WriteHTML( '<table class="table table-bordered mjschool_border_collapse" width="100%" border="1">' );
		$mpdf->WriteHTML( '<thead>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<th class="text-center mjschool-padding-10px">#</th>' );
		$mpdf->WriteHTML( '<th class="text-center mjschool-padding-10px">' . esc_html__( 'Fees Type', 'mjschool' ) . '</th>' );
		$mpdf->WriteHTML( '<th class="mjschool-padding-10px">' . esc_html__( 'Total', 'mjschool' ) . '</th>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</thead>' );
		$mpdf->WriteHTML( '<tbody>' );
		
		// ADDED: Validate fees_id
		if ( ! isset( $fees_detail_result->fees_id ) || empty( $fees_detail_result->fees_id ) ) {
			$mpdf->WriteHTML( '<tr><td colspan="3">' . esc_html__( 'No fees data', 'mjschool' ) . '</td></tr>' );
		} else {
			$fees_array = explode( ',', $fees_detail_result->fees_id );
			$n          = 1;
			
			foreach ( $fees_array as $fees_id ) {
				$fees_id = absint( $fees_id );
				if ( empty( $fees_id ) ) {
					continue;
				}
				
				$obj_fees     = new Mjschool_Fees();
				$fees_details = $obj_fees->mjschool_get_fees_details( $fees_id );
				
				if ( empty( $fees_details ) ) {
					continue;
				}
				
				$mpdf->WriteHTML( '<tr>' );
				$mpdf->WriteHTML( '<td class="text-center">' . esc_html( $n ) . '</td>' );
				$mpdf->WriteHTML( '<td class="text-center">' );
				
				if ( isset( $fees_details->fees_title_id ) ) {
					$mpdf->WriteHTML( esc_html( get_the_title( absint( $fees_details->fees_title_id ) ) ) . '</td>' );
				} else {
					$mpdf->WriteHTML( esc_html__( 'N/A', 'mjschool' ) . '</td>' );
				}
				
				$mpdf->WriteHTML( '<td>' );
				
				if ( isset( $fees_details->fees_amount ) ) {
					$mpdf->WriteHTML( esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $fees_details->fees_amount, 2, '.', '' ) ) ) . '</td>' );
				} else {
					$mpdf->WriteHTML( '0.00</td>' );
				}
				
				$mpdf->WriteHTML( '</tr>' );
				++$n;
			}
		}
		
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		$mpdf->WriteHTML( '</div>' );
		
		// Totals
		$mpdf->WriteHTML( '<table width="100%" border="0">' );
		$mpdf->WriteHTML( '<tbody>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td align="right">' . esc_html__( 'Sub Total : ', 'mjschool' ) . '</td>' );
		
		$total_amount = isset( $fees_detail_result->total_amount ) ? (float) $fees_detail_result->total_amount : 0;
		$mpdf->WriteHTML( '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $total_amount, 2, '.', '' ) ) ) . '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td width="80%" align="right">' . esc_html__( 'Payment Made :', 'mjschool' ) . '</td>' );
		
		$paid_amount = isset( $fees_detail_result->fees_paid_amount ) ? (float) $fees_detail_result->fees_paid_amount : 0;
		$mpdf->WriteHTML( '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $paid_amount, 2, '.', '' ) ) ) . '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '<tr>' );
		$mpdf->WriteHTML( '<td width="80%" align="right">' . esc_html__( 'Due Amount :', 'mjschool' ) . '</td>' );
		
		$Due_amount = $total_amount - $paid_amount;
		$mpdf->WriteHTML( '<td align="right">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( $Due_amount, 2, '.', '' ) ) ) . '</td>' );
		$mpdf->WriteHTML( '</tr>' );
		$mpdf->WriteHTML( '</tbody>' );
		$mpdf->WriteHTML( '</table>' );
		
		$mpdf->WriteHTML( '<hr class="mjschool-hr-margin-new">' );
		
		// Payment History
		if ( ! empty( $fees_history_detail_result ) && is_array( $fees_history_detail_result ) ) {
			$mpdf->WriteHTML( '<h4>' . esc_html__( 'Payment History', 'mjschool' ) . '</h4>' );
			$mpdf->WriteHTML( '<table class="table table-bordered mjschool_border_collapse" width="100%" border="1">' );
			$mpdf->WriteHTML( '<thead>' );
			$mpdf->WriteHTML( '<tr>' );
			$mpdf->WriteHTML( '<th class="text-center mjschool-padding-10px">' . esc_html__( 'Date', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th class="text-center mjschool-padding-10px">' . esc_html__( 'Amount', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '<th class="mjschool-padding-10px">' . esc_html__( 'Method', 'mjschool' ) . '</th>' );
			$mpdf->WriteHTML( '</tr>' );
			$mpdf->WriteHTML( '</thead>' );
			$mpdf->WriteHTML( '<tbody>' );
			
			foreach ( $fees_history_detail_result as $retrive_date ) {
				if ( empty( $retrive_date ) ) {
					continue;
				}
				
				$mpdf->WriteHTML( '<tr>' );
				
				if ( isset( $retrive_date->paid_by_date ) ) {
					$mpdf->WriteHTML( '<td class="text-center">' . esc_html( mjschool_get_date_in_input_box( $retrive_date->paid_by_date ) ) . '</td>' );
				} else {
					$mpdf->WriteHTML( '<td class="text-center">N/A</td>' );
				}
				
				if ( isset( $retrive_date->amount ) ) {
					$mpdf->WriteHTML( '<td class="text-center">' . esc_html( mjschool_currency_symbol_position_language_wise( number_format( (float) $retrive_date->amount, 2, '.', '' ) ) ) . '</td>' );
				} else {
					$mpdf->WriteHTML( '<td class="text-center">0.00</td>' );
				}
				
				if ( isset( $retrive_date->payment_method ) ) {
					$mpdf->WriteHTML( '<td>' . esc_html( $retrive_date->payment_method ) . '</td>' );
				} else {
					$mpdf->WriteHTML( '<td>N/A</td>' );
				}
				$mpdf->WriteHTML( '</tr>' );
			}
			$mpdf->WriteHTML( '</tbody>' );
			$mpdf->WriteHTML( '</table>' );
		}
		$mpdf->WriteHTML( '</div>' );
		$mpdf->WriteHTML( '</div>' );
		$mpdf->WriteHTML( '</body>' );
		$mpdf->WriteHTML( '</html>' );
		$pdf_filename = 'invoice_' . $fees_pay_id . '_' . $student . '.pdf';
		$pdf_file     = $document_path . $pdf_filename;
		$mpdf->Output( $pdf_file, 'F' );
		// ADDED: Verify file was created
		if ( ! file_exists( $pdf_file ) ) {
			error_log( 'Translate Invoice PDF: Failed to create PDF file' );
			return false;
		}
		$result = get_site_url() . '/wp-content/uploads/translate_invoice_pdf/' . $pdf_filename;
		return $result;
	} catch ( Exception $e ) {
		error_log( 'Translate Invoice PDF generation failed: ' . $e->getMessage() );
		return false;
	}
}
/**
 * Generates a translated exam result PDF for a student.
 *
 * Builds an exam result sheet including marks, grades, GPA,
 * and student details. The output is rendered using mPDF and stored
 * under `/wp-content/uploads/translate_invoice_pdf/`.
 *
 * @since 1.0.0
 *
 * @param int $s_id Student ID.
 * @param int $e_id Exam ID.
 *
 * @return string URL of the generated exam result PDF file.
 */
function mjschool_api_translate_result_pdf( $s_id, $e_id ) {
	$s_id = absint( $s_id );
	$e_id = absint( $e_id );
	
	if ( empty( $s_id ) || empty( $e_id ) ) {
		error_log( 'Result PDF: Invalid parameters' );
		return false;
	}
	
	// FIXED: Changed permissions from 0777 to 0755
	$document_dir  = WP_CONTENT_DIR . '/uploads/translate_invoice_pdf/';
	$document_path = $document_dir;
	
	if ( ! file_exists( $document_path ) ) {
		if ( ! mkdir( $document_path, 0755, true ) && ! is_dir( $document_path ) ) {
			error_log( 'Result PDF: Failed to create directory' );
			return false;
		}
	}
	
	// ADDED: Try-catch for error handling
	try {
		ob_start();
		
		$obj_mark = new mjschool_Marks_Manage();
		$uid      = $s_id;
		
		// ADDED: Validate get_userdata()
		$user = get_userdata( $uid );
		if ( empty( $user ) ) {
			error_log( 'Result PDF: Invalid student ID: ' . $uid );
			ob_end_clean();
			return false;
		}
		
		// FIXED: Validate array access
		$user_meta = get_user_meta( $uid );
		if ( ! is_array( $user_meta ) || ! isset( $user_meta['class_name'][0] ) ) {
			error_log( 'Result PDF: Missing class_name for student: ' . $uid );
			ob_end_clean();
			return false;
		}
		
		$class_id   = absint( $user_meta['class_name'][0] );
		$section_id = get_user_meta( $uid, 'class_section', true );
		
		if ( ! empty( $section_id ) ) {
			$subject = $obj_mark->mjschool_student_subject( $class_id, absint( $section_id ) );
		} else {
			$subject = $obj_mark->mjschool_student_subject( $class_id );
		}
		
		// ADDED: Validate subject array
		if ( ! is_array( $subject ) || empty( $subject ) ) {
			error_log( 'Result PDF: No subjects found for student: ' . $uid );
			ob_end_clean();
			return false;
		}
		
		$total_subject = count( $subject );
		$exam_id       = $e_id;
		$total         = 0;
		$grade_point   = 0;
		$umetadata     = mjschool_get_user_image( $uid );
		?>
		<center>
			<div class="mjschool_float_left_width_100">
				<img src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>" style="max-height:50px;" />
				<?php echo esc_html( get_option( 'mjschool_name' ) ); ?>
			</div>

			<div style="width:100%;float:left;border-bottom:1px solid red;"></div>
			<div style="width:100%;float:left;border-bottom:1px solid yellow;padding-top:5px;"></div>
			<br>
			<div style="float:left;width:100%;padding:10px 0;">
				<div style="width:70%;float:left;text-align:left;">
					<p><?php esc_html_e( 'Surname', 'mjschool' ); ?> : <?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?></p>
					<p><?php esc_html_e( 'First Name', 'mjschool' ); ?> : <?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?></p>
					<p>
						<?php esc_html_e( 'Class', 'mjschool' ); ?> :
						<?php
						$classname = mjschool_get_class_name( $class_id );
						echo esc_html( $classname );
						?>
					</p>
					<p><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> : <?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?></p>
				</div>
			</div>
			<br>
			<table style="float:left;width:100%;border:1px solid #000;" cellpadding="0" cellspacing="0">
				<thead>
					<tr style="border-bottom: 1px solid #000;">
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'S/No', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Obtain Mark', 'mjschool' ); ?></th>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					// FIXED: Validate foreach
					foreach ( $subject as $sub ) {
						if ( ! isset( $sub->subid ) || ! isset( $sub->sub_name ) ) {
							continue;
						}
						?>
						<tr style="border-bottom: 1px solid #000;">
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $i ); ?></td>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $sub->sub_name ); ?></td>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?></td>
						</tr>
						<?php
						++$i;
						$total       += (float) $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						$grade_point += (float) $obj_mark->mjschool_get_grade_point( $exam_id, $class_id, $sub->subid, $uid );
					}
					?>
				</tbody>
			</table>
			<p class="result_total">
				<?php
				esc_html_e( 'Total Marks', 'mjschool' );
				echo ' : ' . esc_html( $total );
				?>
			</p>
			<p class="result_point">
				<?php
				esc_html_e( 'GPA(grade point average)', 'mjschool' );
				$GPA = $total_subject > 0 ? $grade_point / $total_subject : 0;
				echo ' : ' . esc_html( round( $GPA, 2 ) );
				?>
			</p>
			<hr />
		</center>
		<?php
		$out_put = ob_get_contents();
		ob_end_clean();
		
		header( 'Content-Disposition: inline; filename="result"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Accept-Ranges: bytes' );
		
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
		$mpdf                   = new Mpdf\Mpdf();
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		$mpdf->WriteHTML( $out_put );
		
		$pdf_filename = 'invoice_' . $exam_id . '_' . $uid . '.pdf';
		$pdf_file     = $document_path . $pdf_filename;
		
		$mpdf->Output( $pdf_file, 'F' );
		
		// ADDED: Verify file was created
		if ( ! file_exists( $pdf_file ) ) {
			error_log( 'Result PDF: Failed to create PDF file' );
			return false;
		}
		
		$result = get_site_url() . '/wp-content/uploads/translate_invoice_pdf/' . $pdf_filename;
		return $result;
		
	} catch ( Exception $e ) {
		error_log( 'Result PDF generation failed: ' . $e->getMessage() );
		ob_end_clean();
		return false;
	}
}
/**
 * Retrieves all rooms belonging to a specific hostel.
 *
 * Executes a direct SQL query on the `mjschool_room` table
 * to fetch room records associated with the given hostel ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hostel ID.
 *
 * @return array List of room objects.
 */
function mjschool_get_rooms_by_hostel_id( $eid ) {
	$hostel_id = absint( $eid );
	
	if ( empty( $hostel_id ) ) {
		return array();
	}
	
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_mjschool_room} WHERE hostel_id = %d", $hostel_id ) );
	
	return is_array( $result ) ? $result : array();
}
/**
 * Retrieves all beds associated with a specific room.
 *
 * Queries the `mjschool_beds` table to fetch all bed records
 * for the provided room ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 *
 * @return array List of bed objects.
 */
function mjschool_get_beds_by_room_id( $eid ) {
	$room_id = absint( $eid );
	
	if ( empty( $room_id ) ) {
		return array();
	}
	
	global $wpdb;
	$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_mjschool_beds} WHERE room_id = %d", $room_id )
	);
	
	return is_array( $result ) ? $result : array();
}

/**
 * Retrieve bed charge by bed ID.
 *
 * CORRECTED VERSION with property validation.
 *
 * @since 1.0.0
 *
 * @param int $eid Bed ID.
 * @return float|null Bed charge on success, null on failure.
 */
function mjschool_get_bed_charge_by_id( $eid ) {
	$bed_id = absint( $eid );
	
	if ( empty( $bed_id ) ) {
		return null;
	}
	
	global $wpdb;
	$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT bed_charge FROM {$table_mjschool_beds} WHERE id = %d", $bed_id )
	);
	
	// ADDED: Property validation
	if ( empty( $result ) || ! isset( $result->bed_charge ) ) {
		return null;
	}
	
	return (float) $result->bed_charge;
}

/**
 * Get all beds associated with a hostel.
 *
 * CORRECTED VERSION - FIXED SQL INJECTION!
 *
 * @since 1.0.0
 *
 * @param int $eid Hostel ID.
 * @return array List of beds found under the hostel.
 */
function mjschool_get_beds_by_hostel_id( $eid ) {
	$hostel_id = absint( $eid );
	
	if ( empty( $hostel_id ) ) {
		return array();
	}
	
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results(
		$wpdb->prepare( "SELECT id FROM {$table_mjschool_room} WHERE hostel_id = %d", $hostel_id )
	);
	
	if ( empty( $result ) || ! is_array( $result ) ) {
		return array();
	}
	
	$room_id = array();
	foreach ( $result as $data ) {
		if ( isset( $data->id ) ) {
			$room_id[] = absint( $data->id );
		}
	}
	
	if ( empty( $room_id ) ) {
		return array();
	}
	
	// FIXED: Proper prepare() with placeholders for IN clause
	$placeholders = implode( ',', array_fill( 0, count( $room_id ), '%d' ) );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result_beds = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table_mjschool_beds} WHERE room_id IN ($placeholders)", ...$room_id )
	);
	
	return is_array( $result_beds ) ? $result_beds : array();
}

/**
 * Get hostel name from room ID.
 *
 * CORRECTED VERSION - FIXED SQL CONCATENATION BUG!
 *
 * @since 1.0.0
 *
 * @param int $eid Room ID.
 * @return string Hostel name.
 */
function mjschool_get_hostel_name_by_room_id( $eid ) {
	$room_id = absint( $eid );
	
	if ( empty( $room_id ) ) {
		return '';
	}
	
	global $wpdb;
	$table_mjschool_room   = $wpdb->prefix . 'mjschool_room';
	$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT hostel_id FROM {$table_mjschool_room} WHERE id = %d", $room_id )
	);
	
	// ADDED: Property validation
	if ( empty( $result ) || ! isset( $result->hostel_id ) ) {
		return '';
	}
	
	$hostel_id = absint( $result->hostel_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result_hostel = $wpdb->get_row(
		$wpdb->prepare( "SELECT hostel_name FROM {$table_mjschool_hostel} WHERE id = %d", $hostel_id )
	);
	
	// ADDED: Property validation
	if ( empty( $result_hostel ) || ! isset( $result_hostel->hostel_name ) ) {
		return '';
	}
	
	return $result_hostel->hostel_name;
}

/**
 * Compare notifications by date.
 *
 * Used for sorting arrays by notification_date in descending order.
 *
 * @since 1.0.0
 *
 * @param array $element1 First notification array.
 * @param array $element2 Second notification array.
 * @return int Comparison result for usort().
 */
function mjschool_date_compare( $element1, $element2 ) {
	// ADDED: Validation
	if ( ! is_array( $element1 ) || ! isset( $element1['notification_date'] ) ) {
		return 0;
	}
	if ( ! is_array( $element2 ) || ! isset( $element2['notification_date'] ) ) {
		return 0;
	}
	
	$datetime1 = strtotime( $element1['notification_date'] );
	$datetime2 = strtotime( $element2['notification_date'] );
	
	return $datetime2 - $datetime1;
}

/**
 * Refresh Zoom OAuth access token.
 *
 * @since 1.0.0
 *
 * @return bool True on success, false on failure.
 */
function mjschool_refresh_token() {
	$CLIENT_ID     = get_option( 'mjschool_virtual_classroom_client_id' );
	$CLIENT_SECRET = get_option( 'mjschool_virtual_classroom_client_secret_id' );
	$arr_token     = get_option( 'mjschool_virtual_classroom_access_token' );
	
	if ( empty( $CLIENT_ID ) || empty( $CLIENT_SECRET ) || empty( $arr_token ) ) {
		error_log( 'Zoom token refresh: Missing credentials' );
		return false;
	}
	
	$token_decode = json_decode( $arr_token );
	
	if ( empty( $token_decode ) || ! isset( $token_decode->refresh_token ) ) {
		error_log( 'Zoom token refresh: Invalid token format' );
		return false;
	}
	
	$refresh_token = $token_decode->refresh_token;
	
	// ADDED: Try-catch for error handling
	try {
		require_once MJSCHOOL_PLUGIN_DIR . '/lib/vendor/autoload.php';
		
		$client   = new GuzzleHttp\Client( array( 'base_uri' => 'https://zoom.us' ) );
		$response = $client->request(
			'POST',
			'/oauth/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $CLIENT_ID . ':' . $CLIENT_SECRET ),
				),
				'query'   => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
				),
			)
		);
		
		$token = $response->getBody()->getContents();
		update_option( 'mjschool_virtual_classroom_access_token', $token );
		
		return true;
		
	} catch ( Exception $e ) {
		error_log( 'Zoom token refresh failed: ' . $e->getMessage() );
		return false;
	}
}

if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
	add_filter( 'cron_schedules', 'mjschool_isa_add_every_thirty_minutes' );
}

/**
 * Register a new cron schedule for every 30 minutes.
 *
 * @since 1.0.0
 *
 * @param array $schedules Existing schedules.
 * @return array Modified schedules array.
 */
function mjschool_isa_add_every_thirty_minutes( $schedules ) {
	if ( ! is_array( $schedules ) ) {
		$schedules = array();
	}
	
	$schedules['every_thirty_minutes'] = array(
		'interval' => 1800,
		'display'  => esc_html__( 'Every 30 Minutes', 'mjschool' ),
	);
	return $schedules;
}
if ( ! wp_next_scheduled( 'mjschool_isa_add_every_thirty_minutes' ) ) {
	wp_schedule_event( time(), 'every_thirty_minutes', 'mjschool_isa_add_every_thirty_minutes' );
}
add_action( 'mjschool_isa_add_every_thirty_minutes', 'mjschool_every_thirty_minutes_event_func' );
/**
 * Cron event callback to refresh Zoom token every 30 minutes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_every_thirty_minutes_event_func() {
	mjschool_refresh_token();
}
/**
 * Get receiver names for a specific message reply.
 *
 * CORRECTED VERSION - FIXED CRITICAL SQL INJECTION!
 *
 * @since 1.0.0
 *
 * @param int    $message_id Message ID.
 * @param int    $sender_id Sender ID.
 * @param string $created_date Message created date.
 * @param string $message_comment Message comment content.
 * @return string Comma-separated receiver names.
 */
function mjschool_get_receiver_name_array( $message_id, $sender_id, $created_date, $message_comment ) {
	// ADDED: Input validation
	$message_id      = absint( $message_id );
	$sender_id       = absint( $sender_id );
	$created_date    = sanitize_text_field( $created_date );
	$message_comment = sanitize_textarea_field( $message_comment );
	
	if ( empty( $message_id ) || empty( $sender_id ) ) {
		return '';
	}
	
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_message_replies';
	
	// FIXED: Proper prepare() with placeholders - CRITICAL SQL INJECTION FIX!
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$reply_msg = $wpdb->get_results(
		$wpdb->prepare( "SELECT receiver_id FROM {$tbl_name} WHERE message_id = %d AND sender_id = %d AND (message_comment = %s OR created_date = %s)", $message_id, $sender_id, $message_comment, $created_date )
	);
	
	if ( empty( $reply_msg ) || ! is_array( $reply_msg ) ) {
		return '';
	}
	
	$receiver_name = array();
	foreach ( $reply_msg as $receiver ) {
		if ( ! isset( $receiver->receiver_id ) ) {
			continue;
		}
		
		$name = mjschool_get_display_name( absint( $receiver->receiver_id ) );
		if ( ! empty( $name ) ) {
			$receiver_name[] = $name;
		}
	}
	
	return ! empty( $receiver_name ) ? implode( ', ', $receiver_name ) : '';
}

add_filter( 'cron_schedules', 'mjschool_isa_add_every_five_minutes' );

/**
 * Register a cron schedule for every 5 minutes.
 *
 * @since 1.0.0
 *
 * @param array $schedules Existing cron schedules.
 * @return array Modified schedules.
 */
function mjschool_isa_add_every_five_minutes( $schedules ) {
	if ( ! is_array( $schedules ) ) {
		$schedules = array();
	}
	$schedules['every_five_minutes'] = array(
		'interval' => 300,
		'display'  => esc_html__( 'Every 5 Minutes', 'mjschool' ),
	);
	return $schedules;
}

if ( ! wp_next_scheduled( 'mjschool_isa_add_every_five_minutes' ) ) {
	wp_schedule_event( time(), 'every_five_minutes', 'mjschool_isa_add_every_five_minutes' );
}
add_action( 'mjschool_isa_add_every_five_minutes', 'mjschool_every_five_minutes_event_callback' );
/**
 * Cron event callback to send virtual class reminders every 5 minutes.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_every_five_minutes_event_callback() {
	mjschool_virtual_class_mail_reminder();
}

/**
 * Process virtual classroom reminder logic.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_virtual_class_mail_reminder() {
	$obj_virtual_classroom                               = new mjschool_virtual_classroom();
	$virtual_classroom_enable                            = get_option( 'mjschool_enable_virtual_classroom' );
	$virtual_classroom_reminder_enable                   = get_option( 'mjschool_enable_virtual_classroom_reminder' );
	$virtual_classroom_reminder_time                     = get_option( 'mjschool_virtual_classroom_reminder_before_time' );
	$mjschool_enable_mjschool_virtual_classroom_reminder = get_option( 'mjschool_enable_mjschool_virtual_classroom_reminder' );
	
	if ( 'yes' !== $mjschool_enable_mjschool_virtual_classroom_reminder && 'yes' !== $virtual_classroom_enable && 'yes' !== $virtual_classroom_reminder_enable ) {
		return;
	}
	
	// FIXED: Date comparison logic
	$today_day = absint( gmdate( 'w' ) );  // 0 (Sunday) to 6 (Saturday)
	
	// Map PHP's date('w') to Zoom's day codes
	$weekday_map = array(
		0 => 1,  // Sunday
		1 => 2,  // Monday
		2 => 3,  // Tuesday
		3 => 4,  // Wednesday
		4 => 5,  // Thursday
		5 => 6,  // Friday
		6 => 7   // Saturday
	);
	
	$weekday = isset( $weekday_map[ $today_day ] ) ? $weekday_map[ $today_day ] : 1;
	
	$virtual_classroom_data = $obj_virtual_classroom->mjschool_get_meeting_data_by_day_in_zoom( $weekday );
	
	if ( empty( $virtual_classroom_data ) || ! is_array( $virtual_classroom_data ) ) {
		return;
	}
	
	foreach ( $virtual_classroom_data as $data ) {
		if ( ! isset( $data->route_id ) || ! isset( $data->meeting_id ) ) {
			continue;
		}
		
		$route_data = mjschool_get_route_by_id( absint( $data->route_id ) );
		
		if ( empty( $route_data ) || ! isset( $route_data->start_time ) ) {
			continue;
		}
		
		// Time class start convert in format
		$stime = explode( ':', $route_data->start_time );
		if ( count( $stime ) < 3 ) {
			continue;
		}
		
		$start_hour  = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
		$start_min   = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
		$start_am_pm = $stime[2];
		$start_time  = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
		
		// Class start time convert in 24 hours format
		$starttime = gmdate( 'H:i', strtotime( $start_time ) );
		
		// Get current time
		$currunt_time = current_time( 'H:i:s' );
		
		// Minus time in minutes
		$duration          = '-' . absint( $virtual_classroom_reminder_time ) . ' minutes';
		$class_time        = strtotime( $duration, strtotime( $starttime ) );
		$befour_class_time = gmdate( 'H:i:s', $class_time );
		
		// Check time condition
		if ( $currunt_time >= $befour_class_time ) {
			if ( 'yes' === $mjschool_enable_mjschool_virtual_classroom_reminder && 'yes' === $virtual_classroom_enable && 'yes' === $virtual_classroom_reminder_enable ) {
				mjschool_virtual_class_teacher_mail_reminder( absint( $data->meeting_id ) );
				mjschool_virtual_class_students_mail_reminder( absint( $data->meeting_id ) );
				mjschool_virtual_class_teacher_mjschool_reminder( absint( $data->meeting_id ) );
				mjschool_virtual_class_students_mjschool_reminder( absint( $data->meeting_id ) );
			} elseif ( 'yes' === $mjschool_enable_mjschool_virtual_classroom_reminder && 'yes' === $virtual_classroom_enable ) {
				mjschool_virtual_class_teacher_mjschool_reminder( absint( $data->meeting_id ) );
				mjschool_virtual_class_students_mjschool_reminder( absint( $data->meeting_id ) );
			} elseif ( 'yes' === $virtual_classroom_enable && 'yes' === $virtual_classroom_reminder_enable ) {
				mjschool_virtual_class_teacher_mail_reminder( absint( $data->meeting_id ) );
				mjschool_virtual_class_students_mail_reminder( absint( $data->meeting_id ) );
			}
		}
	}
}

/**
 * Send email reminder to teacher for an upcoming virtual class.
 *
 * CORRECTED VERSION with email fix.
 *
 * @since 1.0.0
 *
 * @param int $meeting_id Zoom meeting ID.
 * @return void
 */
function mjschool_virtual_class_teacher_mail_reminder( $meeting_id ) {
	$meeting_id = absint( $meeting_id );
	
	if ( empty( $meeting_id ) ) {
		return;
	}
	
	$obj_virtual_classroom = new mjschool_virtual_classroom();
	$meeting_data          = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( $meeting_id );
	
	if ( empty( $meeting_data ) || ! isset( $meeting_data->teacher_id ) ) {
		return;
	}
	
	$clasname     = mjschool_get_class_name( absint( $meeting_data->class_id ) );
	$subjectname  = mjschool_get_single_subject_name( absint( $meeting_data->subject_id ) );
	$today_date   = mjschool_get_date_in_input_box( gmdate( 'Y-m-d' ) );
	$teacher_name = mjschool_get_display_name( absint( $meeting_data->teacher_id ) );
	
	$teacher_all_data = get_userdata( absint( $meeting_data->teacher_id ) );
	if ( empty( $teacher_all_data ) || ! isset( $teacher_all_data->user_email ) ) {
		return;
	}
	
	$route_data = mjschool_get_route_by_id( absint( $meeting_data->route_id ) );
	if ( empty( $route_data ) ) {
		return;
	}
	
	// Process start and end times...
	$start_time_123 = isset( $route_data->start_time ) ? $route_data->start_time : '';
	$end_time_123   = isset( $route_data->end_time ) ? $route_data->end_time : '';
	
	if ( empty( $start_time_123 ) || empty( $end_time_123 ) ) {
		return;
	}
	
	$start_time_data = explode( ':', $start_time_123 );
	if ( count( $start_time_data ) < 3 ) {
		return;
	}
	
	$start_hour     = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
	$start_min      = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
	$start_am_pm    = $start_time_data[2];
	$start_time_new = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
	$starttime      = gmdate( 'H:i', strtotime( $start_time_new ) );
	
	$end_time_data = explode( ':', $end_time_123 );
	if ( count( $end_time_data ) < 3 ) {
		return;
	}
	
	$end_hour     = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
	$end_min      = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
	$end_am_pm    = $end_time_data[2];
	$end_time_new = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
	$edittime     = gmdate( 'H:i', strtotime( $end_time_new ) );
	
	$time = $starttime . ' TO ' . $edittime;
	
	$start_zoom_virtual_class_link = isset( $meeting_data->meeting_start_link ) 
		? '<p><a href="' . esc_url( $meeting_data->meeting_start_link ) . '" class="btn btn-primary">' . esc_html__( 'Start Virtual Class', 'mjschool' ) . '</a></p><br><br>'
		: '';
	
	$log_date               = gmdate( 'Y-m-d', strtotime( $today_date ) );
	$mail_reminder_log_data = mjschool_cheack_virtual_class_mail_reminder_log_data( 
		absint( $meeting_data->teacher_id ), 
		$meeting_id, 
		absint( $meeting_data->class_id ), 
		$log_date 
	);
	
	if ( empty( $mail_reminder_log_data ) ) {
		$string                                 = array();
		$string['{{teacher_name}}']             = '<span>' . esc_html( $teacher_name ) . '</span><br><br>';
		$string['{{class_name}}']               = '<span>' . esc_html( $clasname ) . '</span><br><br>';
		$string['{{subject_name}}']             = '<span>' . esc_html( $subjectname ) . '</span><br><br>';
		$string['{{date}}']                     = '<span>' . esc_html( $today_date ) . '</span><br><br>';
		$string['{{time}}']                     = '<span>' . esc_html( $time ) . '</span><br><br>';
		$string['{{virtual_class_id}}']         = '<span>' . esc_html( isset( $meeting_data->zoom_meeting_id ) ? $meeting_data->zoom_meeting_id : '' ) . '</span><br><br>';
		$string['{{password}}']                 = '<span>' . esc_html( isset( $meeting_data->password ) ? $meeting_data->password : '' ) . '</span><br><br>';
		$string['{{start_zoom_virtual_class}}'] = $start_zoom_virtual_class_link;
		$string['{{school_name}}']              = '<span>' . esc_html( get_option( 'mjschool_name' ) ) . '</span><br><br>';
		
		$MsgContent = get_option( 'mjschool_virtual_class_teacher_reminder_mail_content' );
		$MsgSubject = get_option( 'mjschool_virtual_class_teacher_reminder_mail_subject' );
		$message    = mjschool_string_replacement( $string, $MsgContent );
		$MsgSubject = mjschool_string_replacement( $string, $MsgSubject );
		$email      = $teacher_all_data->user_email;
		
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";
		
		$email_template = mjschool_get_mail_content_with_template_design( $message );
		
		if ( 1 == get_option( 'mjschool_mail_notification' ) ) {
			wp_mail( $email, $MsgSubject, $email_template, $headers );
		}
		
		mjschool_insert_virtual_class_mail_reminder_log( 
			absint( $meeting_data->teacher_id ), 
			$meeting_id, 
			absint( $meeting_data->class_id ), 
			$log_date 
		);
	}
}

/**
 * Send email reminders to students for an upcoming virtual class.
 *
 * CORRECTED VERSION with validation and email fix.
 *
 * @since 1.0.0
 *
 * @param int $meeting_id Zoom meeting ID.
 * @return void
 */
function mjschool_virtual_class_students_mail_reminder( $meeting_id ) {
	$meeting_id = absint( $meeting_id );
	
	if ( empty( $meeting_id ) ) {
		return;
	}
	
	$obj_virtual_classroom = new mjschool_virtual_classroom();
	$meeting_data          = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( $meeting_id );
	
	if ( empty( $meeting_data ) || ! isset( $meeting_data->class_id ) ) {
		return;
	}
	
	$sections_data = mjschool_get_class_sections( absint( $meeting_data->class_id ) );
	$student_data  = array();
	
	// FIXED: Proper validation
	if ( ! empty( $sections_data ) && is_array( $sections_data ) ) {
		foreach ( $sections_data as $data ) {
			if ( ! isset( $data->id ) || ! isset( $data->class_id ) ) {
				continue;
			}
			
			if ( isset( $meeting_data->section_id ) && $meeting_data->section_id === $data->id ) {
				$student_data = get_users(
					array(
						'meta_key'   => 'class_section',
						'meta_value' => $data->id,
						'meta_query' => array(
							array(
								'key'     => 'class_name',
								'value'   => $data->class_id,
								'compare' => '=',
							),
						),
						'role'       => 'student',
					)
				);
				break;
			}
		}
	} else {
		$student_data = mjschool_get_student_by_class_id( absint( $meeting_data->class_id ) );
	}
	
	if ( empty( $student_data ) || ! is_array( $student_data ) ) {
		return;
	}
	
	$clasname    = mjschool_get_class_name( absint( $meeting_data->class_id ) );
	$subjectname = mjschool_get_single_subject_name( absint( $meeting_data->subject_id ) );
	$today_date  = mjschool_get_date_in_input_box( gmdate( 'Y-m-d' ) );
	
	if ( ! isset( $meeting_data->teacher_id ) ) {
		return;
	}
	
	$teacher_name = mjschool_get_display_name( absint( $meeting_data->teacher_id ) );
	$route_data   = mjschool_get_route_by_id( absint( $meeting_data->route_id ) );
	
	if ( empty( $route_data ) || ! isset( $route_data->start_time ) || ! isset( $route_data->end_time ) ) {
		return;
	}
	
	// Process times (same as teacher function)
	$start_time_123 = $route_data->start_time;
	$end_time_123   = $route_data->end_time;
	
	$start_time_data = explode( ':', $start_time_123 );
	if ( count( $start_time_data ) < 3 ) {
		return;
	}
	
	$start_hour     = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
	$start_min      = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
	$start_am_pm    = $start_time_data[2];
	$start_time_new = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
	$starttime      = gmdate( 'H:i', strtotime( $start_time_new ) );
	
	$end_time_data = explode( ':', $end_time_123 );
	if ( count( $end_time_data ) < 3 ) {
		return;
	}
	
	$end_hour     = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
	$end_min      = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
	$end_am_pm    = $end_time_data[2];
	$end_time_new = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
	$edittime     = gmdate( 'H:i', strtotime( $end_time_new ) );
	
	$time = $starttime . ' TO ' . $edittime;
	
	$join_zoom_virtual_class_link = isset( $meeting_data->meeting_join_link )
		? '<p><a href="' . esc_url( $meeting_data->meeting_join_link ) . '" class="btn btn-primary">' . esc_html__( 'Join Virtual Class', 'mjschool' ) . '</a></p><br><br>'
		: '';
	
	$device_token = array();
	
	foreach ( $student_data as $data ) {
		if ( ! isset( $data->ID ) || ! isset( $data->user_email ) ) {
			continue;
		}
		
		$log_date               = gmdate( 'Y-m-d', strtotime( $today_date ) );
		$device_token[]         = get_user_meta( $data->ID, 'token_id', true );
		$mail_reminder_log_data = mjschool_cheack_virtual_class_mail_reminder_log_data( 
			absint( $data->ID ), 
			$meeting_id, 
			absint( $meeting_data->class_id ), 
			$log_date 
		);
		
		if ( empty( $mail_reminder_log_data ) ) {
			$student_name = mjschool_get_display_name( absint( $data->ID ) );
			
			$string                                = array();
			$string['{{student_name}}']            = '<span>' . esc_html( $student_name ) . '</span><br><br>';
			$string['{{class_name}}']              = '<span>' . esc_html( $clasname ) . '</span><br><br>';
			$string['{{subject_name}}']            = '<span>' . esc_html( $subjectname ) . '</span><br><br>';
			$string['{{teacher_name}}']            = '<span>' . esc_html( $teacher_name ) . '</span><br><br>';
			$string['{{date}}']                    = '<span>' . esc_html( $today_date ) . '</span><br><br>';
			$string['{{time}}']                    = '<span>' . esc_html( $time ) . '</span><br><br>';
			$string['{{virtual_class_id}}']        = '<span>' . esc_html( isset( $meeting_data->zoom_meeting_id ) ? $meeting_data->zoom_meeting_id : '' ) . '</span><br><br>';
			$string['{{password}}']                = '<span>' . esc_html( isset( $meeting_data->password ) ? $meeting_data->password : '' ) . '</span><br><br>';
			$string['{{join_zoom_virtual_class}}'] = $join_zoom_virtual_class_link;
			$string['{{school_name}}']             = '<span>' . esc_html( get_option( 'mjschool_name' ) ) . '</span><br><br>';
			
			$MsgContent = get_option( 'mjschool_virtual_class_student_reminder_mail_content' );
			$MsgSubject = get_option( 'mjschool_virtual_class_student_reminder_mail_subject' );
			$message    = mjschool_string_replacement( $string, $MsgContent );
			$MsgSubject = mjschool_string_replacement( $string, $MsgSubject );
			$email      = $data->user_email;
			
			$headers  = "MIME-Version: 1.0\r\n";
			$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
			$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <' . get_option( 'admin_email' ) . '>' . "\r\n";
			
			$email_template = mjschool_get_mail_content_with_template_design( $message );
			
			if ( 1 == get_option( 'mjschool_mail_notification' ) ) {
				wp_mail( $email, $MsgSubject, $email_template, $headers );
			}
			
			mjschool_insert_virtual_class_mail_reminder_log( 
				absint( $data->ID ), 
				$meeting_id, 
				absint( $meeting_data->class_id ), 
				$log_date 
			);
		}
	}
	
	// Send Push Notification
	if ( ! empty( $device_token ) ) {
		$title = esc_html__( 'New Notification For Virtual Classroom', 'mjschool' );
		$text  = esc_html__( 'Your virtual class just start', 'mjschool' ) . ' ' . esc_html( isset( $meeting_data->zoom_meeting_id ) ? $meeting_data->zoom_meeting_id : '' );
		
		$notification_data = array(
			'registration_ids' => $device_token,
			'notification'     => array(
				'title' => $title,
				'body'  => $text,
				'type'  => 'notification',
			),
		);
		
		$json    = wp_json_encode( $notification_data );
		$message = mjschool_send_push_notification( $json );
	}
}
/**
 * Send internal notification (SMS/app) to teacher for virtual class reminder.
 *
 * Logs reminders to prevent duplicate notifications.
 *
 * @since 1.0.0
 *
 * @param int $meeting_id Meeting ID.
 * @return void
 */
function mjschool_virtual_class_teacher_mjschool_reminder( $meeting_id ) {
	// define virtual classroom object.
	$obj_virtual_classroom = new mjschool_virtual_classroom();
	// get singal virtual classroom data by meeting id.
	$meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( $meeting_id );
	// get class name by class id.
	$clasname = mjschool_get_class_name( $meeting_data->class_id );
	// get subject name by subject id.
	$subjectname = mjschool_get_single_subject_name( $meeting_data->subject_id );
	// today date function.
	$today_date = date( get_option( 'date_format' ) );
	// teacher name.
	$teacher_name = mjschool_get_display_name( $meeting_data->teacher_id );
	// teacher all data.
	$teacher_all_data = get_userdata( $meeting_data->teacher_id );
	// get route data by rout id.
	$route_data = mjschool_get_route_by_id( $meeting_data->route_id );
	// class start time data.
	$stime           = explode( ':', $route_data->start_time );
	$start_hour      = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
	$start_min       = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
	$start_am_pm     = $stime[2];
	$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
	$start_time_data = new DateTime( $start_time );
	$starttime       = date_format( $start_time_data, 'h:i A' );
	// class end time function.
	$etime         = explode( ':', $route_data->end_time );
	$end_hour      = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
	$end_min       = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
	$end_am_pm     = $etime[2];
	$end_time      = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
	$end_time_data = new DateTime( $end_time );
	$edittime      = date_format( $end_time_data, 'h:i A' );
	// concat start time and end time.
	$time = $starttime . ' TO ' . $edittime;
	// start zoom virtual class link data.
	$start_zoom_virtual_class_link = '<p><a href=' . $meeting_data->meeting_start_link . " class='btn btn-primary'>" . esc_attr__( 'Start Virtual Class', 'mjschool' ) . '</a></p><br><br>';
	$log_date                      = date( 'Y-m-d', strtotime( $today_date ) );
	$mail_reminder_log_data        = mjschool_cheack_virtual_class_mail_reminder_log_data( $meeting_data->teacher_id, $meeting_data->meeting_id, $meeting_data->class_id, $log_date );
	if ( empty( $mail_reminder_log_data ) ) {
		$message_content = 'Your virtual class just start';
		$type            = 'Viertual Class';
		mjschool_send_mjschool_notification( $meeting_data->teacher_id, $type, $message_content );
		mjschool_insert_virtual_class_mail_reminder_log( $meeting_data->teacher_id, $meeting_data->meeting_id, $meeting_data->class_id, $log_date );
	}
}
/**
 * Send virtual class reminder notifications to students for a specific meeting.
 *
 * Retrieves meeting details, fetches students of that class/section,
 * generates reminder notifications, and logs the reminder event.
 *
 * @since 1.0.0
 *
 * @param int $meeting_id Zoom meeting ID.
 * @return void
 */
function mjschool_virtual_class_students_mjschool_reminder( $meeting_id ) {
	// define virtual classroom object.
	$obj_virtual_classroom = new mjschool_virtual_classroom();
	// get singal virtual classroom data by meeting id.
	$meeting_data  = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom( $meeting_id );
	$sections_data = mjschool_get_class_sections( $meeting_data->class_id );
	if ( ! empty( $sections_data ) ) {

		foreach ($sections_data as $data) {
			if ($meeting_data->section_id === $data->id) {
				$student_data = get_users(array( 'meta_key' => 'class_section', 'meta_value' => $data->id, 'meta_query' => array(array( 'key' => 'class_name', 'value' => $data->class_id, 'compare' => '=' ) ), 'role' => 'student' ) );
			}
		}

	} else {
		$student_data = mjschool_get_student_by_class_id( $meeting_data->class_id );
	}
	// get class name by class id.
	$clasname = mjschool_get_class_name( $meeting_data->class_id );
	// get subject name by subject id.
	$subjectname = mjschool_get_single_subject_name( $meeting_data->subject_id );
	// today date function.
	$today_date = date( get_option( 'date_format' ) );
	// teacher name.
	$teacher_name = mjschool_get_display_name( $meeting_data->teacher_id );
	// get route data by rout id.
	$route_data = mjschool_get_route_by_id( $meeting_data->route_id );
	// class start time data.
	$stime           = explode( ':', $route_data->start_time );
	$start_hour      = str_pad( $stime[0], 2, '0', STR_PAD_LEFT );
	$start_min       = str_pad( $stime[1], 2, '0', STR_PAD_LEFT );
	$start_am_pm     = $stime[2];
	$start_time      = $start_hour . ':' . $start_min . ' ' . $start_am_pm;
	$start_time_data = new DateTime( $start_time );
	$starttime       = date_format( $start_time_data, 'h:i A' );
	// class end time function.
	$etime         = explode( ':', $route_data->end_time );
	$end_hour      = str_pad( $etime[0], 2, '0', STR_PAD_LEFT );
	$end_min       = str_pad( $etime[1], 2, '0', STR_PAD_LEFT );
	$end_am_pm     = $etime[2];
	$end_time      = $end_hour . ':' . $end_min . ' ' . $end_am_pm;
	$end_time_data = new DateTime( $end_time );
	$edittime      = date_format( $end_time_data, 'h:i A' );
	// concat start time and end time.
	$time = $starttime . ' TO ' . $edittime;
	// start zoom virtual class link data.
	$join_zoom_virtual_class_link = '<p><a href="' . esc_url( $meeting_data->meeting_join_link ) . '" class="btn btn-primary">' . esc_html__( 'Join Virtual Class', 'mjschool' ) . '</a></p><br><br>';
	if ( ! empty( $student_data ) ) {
		foreach ( $student_data as $data ) {
			$message_content = 'Your virtual class just start';
			$type            = 'Virtual Class';
			mjschool_send_mjschool_notification( $data->ID, $type, $message_content );
			$log_date = date( 'Y-m-d', strtotime( $today_date ) );
			mjschool_insert_virtual_class_mail_reminder_log( $data->ID, $meeting_data->meeting_id, $meeting_data->class_id, $log_date );
		}
	}
}
/**
 * Insert a log entry for virtual class reminder notification.
 *
 * Stores user, meeting, class and date information in reminder log table.
 *
 * @since 1.0.0
 *
 * @param int    $user_id    User ID.
 * @param int    $meeting_id Meeting ID.
 * @param int    $class_id   Class ID.
 * @param string $date       Alert date (Y-m-d).
 * @return void
 */
function mjschool_insert_virtual_class_mail_reminder_log( $user_id, $meeting_id, $class_id, $date ) {
	global $wpdb;
	$table_zoom_meeting_mail_reminder_log = $wpdb->prefix . 'mjschool_reminder_zoom_meeting_mail_log';
	$meeting_log_data['user_id']          = absint( $user_id );
	$meeting_log_data['meeting_id']       = absint( $meeting_id );
	$meeting_log_data['class_id']         = absint( $class_id );
	$meeting_log_data['alert_date']       = sanitize_text_field( $date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_zoom_meeting_mail_reminder_log, $meeting_log_data );
}
/**
 * Check if a reminder notification has already been logged.
 *
 * Searches reminder log table to prevent duplicate notifications.
 *
 * @since 1.0.0
 *
 * @param int    $user_id    User ID.
 * @param int    $meeting_id Meeting ID.
 * @param int    $class_id   Class ID.
 * @param string $date       Alert date (Y-m-d).
 * @return object|null       Log row object or null.
 */
function mjschool_cheack_virtual_class_mail_reminder_log_data( $user_id, $meeting_id, $class_id, $date ) {
	global $wpdb;
	$table_zoom_meeting_mail_reminder_log = $wpdb->prefix . 'mjschool_reminder_zoom_meeting_mail_log';
	$user_id                              = absint( $user_id );
	$meeting_id                           = absint( $meeting_id );
	$class_id                             = absint( $class_id );
	$date                                 = sanitize_text_field( $date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_zoom_meeting_mail_reminder_log WHERE user_id=%d AND meeting_id=%d AND class_id=%d AND alert_date=%s", $user_id, $meeting_id, $class_id, $date ) );
	return $result;
}
/**
 * Get the calendar language code from the current WordPress locale.
 *
 * Example: 'en_US' → 'en', 'hi_IN' → 'hi'.
 *
 * @since 1.0.0
 *
 * @return string Two-letter language code.
 */
function mjschool_calender_laungage() {
	$lancode = get_locale();
	$code    = substr( $lancode, 0, 2 );
	return $code;
}
/**
 * Convert notice target key into a readable label.
 *
 * @since 1.0.0
 *
 * @param string $notice_for Notice type key (teacher|student|parent|supportstaff).
 * @return string Readable label.
 */
function mjschool_notice_for_value( $notice_for ) {
	$notice_for = sanitize_text_field( $notice_for );
	if ( $notice_for === 'teacher' ) {
		return 'Teacher';
	} elseif ( $notice_for === 'student' ) {
		return 'Student';
	} elseif ( $notice_for === 'parent' ) {
		return 'Parent';
	} elseif ( $notice_for === 'supportstaff' ) {
		return 'Support Staff';
	} else {
		return 'Support Staff';
	}
}
/**
 * Upload and save a user avatar image inside /uploads/school_assets/.
 *
 * Validates image type, creates directory if required,
 * removes old file, and uploads the new image.
 *
 * @since 1.0.0
 *
 * @param string $type File input key.
 * @return string Filename of uploaded image.
 */
function mjschool_user_avatar_image_upload( $type ) {
	$type = sanitize_key( $type );
	
	if ( ! isset( $_FILES[ $type ] ) || empty( $_FILES[ $type ]['tmp_name'] ) ) {
		wp_die( esc_html__( 'No file uploaded.', 'mjschool' ) );
	}
	
	$file_tmp_name = sanitize_text_field( wp_unslash( $_FILES[ $type ]['tmp_name'] ) );
	$file_name     = sanitize_file_name( wp_unslash( $_FILES[ $type ]['name'] ) );
	
	$check_image = mjschool_wp_check_file_type_and_ext_image( $file_tmp_name, $file_name );
	if ( $check_image ) {
		$imagepath          = '';
		$parts              = pathinfo( $file_name );
		$inventoryimagename = 'mjschool_' . time() . '-' . 'student' . '.' . sanitize_file_name( $parts['extension'] );
		$document_dir       = WP_CONTENT_DIR;
		$document_dir      .= '/uploads/school_assets/';
		$document_path      = $document_dir;
		if ( $imagepath != '' ) {
			if ( file_exists( WP_CONTENT_DIR . $imagepath ) ) {
				unlink( WP_CONTENT_DIR . $imagepath );
			}
		}
		if ( ! file_exists( $document_path ) ) {
			wp_mkdir_p( $document_path );
		}
		if ( is_uploaded_file( $file_tmp_name ) ) {
			if ( move_uploaded_file( $file_tmp_name, $document_path . $inventoryimagename ) ) {
				$imagepath = $inventoryimagename;
			}
		}
		return $imagepath;
	} else {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
}
/**
 * Get all classes created by a specific user.
 *
 * @since 1.0.0
 *
 * @param int $eid User ID.
 * @return array List of class records.
 */
function mjschool_get_all_class_created_by_user( $eid ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	$user_id    = absint( $eid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE creater_id=%d", $user_id ) );
	return $results;
}
/**
 * Get all grade entries created by the current logged-in user.
 *
 * @since 1.0.0
 *
 * @param string $mjschool_table_name Table slug (without prefix).
 * @return array List of grade rows.
 */
function mjschool_get_all_grade_data_by_user_id( $mjschool_table_name ) {
	global $wpdb;
	$user_id    = get_current_user_id();
	$table_name = $wpdb->prefix . sanitize_key( $mjschool_table_name );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $retrieve_subjects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name where creater_id=%d", $user_id ) );
}
/**
 * Get all exam hall entries created by the current logged-in user.
 *
 * @since 1.0.0
 *
 * @param string $mjschool_table_name Table slug (without prefix).
 * @return array List of exam hall rows.
 */
function mjschool_get_all_exam_hall_by_user_id( $mjschool_table_name ) {
	global $wpdb;
	$user_id    = get_current_user_id();
	$table_name = $wpdb->prefix . sanitize_key( $mjschool_table_name );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $retrieve_subjects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name where created_by=%d", $user_id ) );
}
/**
 * Get attendance report for a student or class within a date range.
 *
 * Supports filters for class, student, and attendance status.
 *
 * @since 1.0.0
 *
 * @param string     $start_date Start date (Y-m-d).
 * @param string     $end_date   End date (Y-m-d).
 * @param int|string $class_id   Class ID or 'all_class'.
 * @param int        $student_id Student ID.
 * @param string     $status     Attendance status or 'all_status'.
 * @return array Attendance entries.
 */
function mjschool_view_attendance_for_report( $start_date, $end_date, $class_id, $student_id, $status ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = sanitize_text_field( $class_id );
	$student_id = absint( $student_id );
	$status     = sanitize_text_field( $status );
	
	// Base query parts.
	$where_conditions = 'role_name = %s AND attendance_date BETWEEN %s AND %s';
	$query_params     = array( 'student', $start_date, $end_date );
	// Add class_id condition if it's not 'all_class'.
	if ( $class_id !== 'all_class' ) {
		$where_conditions .= ' AND class_id = %d';
		$query_params[]    = absint( $class_id );
	}
	if ( $student_id !== 0 ) {
		$where_conditions .= ' AND user_id = %d';
		$query_params[]    = $student_id;
	}
	// Add status condition if it's not 'all_status'.
	if ( $status !== 'all_status' ) {
		$where_conditions .= ' AND status = %s';
		$query_params[]    = $status;
	}
	// Prepare and execute the query.
	$query = "SELECT * FROM $tbl_name WHERE $where_conditions";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, ...$query_params ) );
	return $result;
}
/**
 * Get attendance report for all students between given dates.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return array Attendance rows.
 */
function mjschool_view_attendance_report_for_start_date_enddate( $start_date, $end_date ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	// Prepare the query with placeholders
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendance_date BETWEEN %s AND %s";
	// Prepare and execute the query
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $start_date, $end_date ) );
	return $result;
}
/**
 * Get teacher attendance report between two dates.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return array Attendance rows.
 */
function mjschool_view_teacher_for_report_attendance_report_for_start_date_enddate( $start_date, $end_date ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date BETWEEN %s AND %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'teacher', $start_date, $end_date ) );
	return $result;
}
/**
 * Count total present students across all classes for a specific date.
 *
 * @since 1.0.0
 *
 * @param string $daily_date Date (Y-m-d).
 * @return int Number of present records.
 */
function mjschool_daily_attendance_report_for_all_class_total_present( $daily_date ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$daily_date = sanitize_text_field( $daily_date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date = %s AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $daily_date, 'Present' ) );
	// Return the count of results
	return count( $result );
}
/**
 * Get total absent students for all classes on a specific date.
 *
 * @since 1.0.0
 *
 * @param string $daily_date Attendance date (Y-m-d).
 * @return int Total absent count.
 */
function mjschool_daily_attendance_report_for_all_class_total_absent( $daily_date ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$daily_date = sanitize_text_field( $daily_date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date = %s AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $daily_date, 'Absent' ) );
	// Return the count of results
	return count( $result );
}
/**
 * Get total present students for a class on a specific date.
 *
 * @since 1.0.0
 *
 * @param string $daily_date Attendance date.
 * @param int    $class_id   Class ID.
 * @return int Total present count.
 */
function mjschool_daily_attendance_report_for_date_total_present( $daily_date, $class_id ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	// Sanitize inputs.
	$daily_date = sanitize_text_field( $daily_date );
	$class_id   = absint( $class_id );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendance_date = %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $daily_date, $class_id, 'Present' ) );
	// Return the count of results
	return count( $result );
}
/**
 * Get total absent students for a class on a specific date.
 *
 * @since 1.0.0
 *
 * @param string $daily_date Attendance date.
 * @param int    $class_id   Class ID.
 * @return int Total absent count.
 */
function mjschool_daily_attendance_report_for_date_total_absent( $daily_date, $class_id ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	// Sanitize inputs.
	$daily_date = sanitize_text_field( $daily_date );
	$class_id   = absint( $class_id );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM {$tbl_name} WHERE role_name = %s AND attendance_date = %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $daily_date, $class_id, 'Absent' ) );
	// Return the count of results
	return count( $result );
}
/**
 * Get assigned beds by hostel ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Hostel ID.
 * @return array List of assigned beds.
 */
function mjschool_get_assign_beds_by_hostel_id( $eid ) {
	global $wpdb;
	$tbl_name  = $wpdb->prefix . 'mjschool_assign_beds';
	$hostel_id = absint( $eid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} where hostel_id = %d", $hostel_id ) );
	return $result;
}
/**
 * Get all assigned beds.
 *
 * @since 1.0.0
 *
 * @return array Assigned bed records.
 */
function mjschool_get_all_assign_beds() {
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_assign_beds';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( "SELECT * FROM $table_mjschool_room" );
	return $result;
}
/**
 * Get assigned bed details by student ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Student ID.
 * @return array Assigned bed records.
 */
function mjschool_assign_beds_student_id( $eid ) {
	global $wpdb;
	$tbl_name   = $wpdb->prefix . 'mjschool_assign_beds';
	$student_id = absint( $eid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} where student_id =%d", $student_id ) );
	return $result;
}
/**
 * Get assigned bed details by bed ID.
 *
 * @since 1.0.0
 *
 * @param int $eid Bed ID.
 * @return array Assigned bed details.
 */
function mjschool_assign_beds_bed_id( $eid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_assign_beds';
	$id       = absint( $eid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} where bed_id =%d", $id ) );
	return $result;
}
/**
 * Get attendance records for student by date range, class and status.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $class_id   Class ID.
 * @param int    $user_id    Student ID.
 * @param string $status     Attendance status.
 * @return array Attendance records.
 */
function mjschool_attendance_report_get_status_for_student_id( $start_date, $end_date, $class_id, $user_id, $status ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = absint( $class_id );
	$user_id    = absint( $user_id );
	$status     = sanitize_text_field( $status );
	
	$query    = $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE attendance_date BETWEEN %s AND %s AND class_id = %d AND user_id = %d AND status = %s AND sub_id IS NULL", $start_date, $end_date, $class_id, $user_id, $status );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Check issued books by class ID and date range.
 *
 * @since 1.0.0
 *
 * @param int    $eid        Class ID.
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return array Issued book records.
 */
function mjschool_check_book_issued_by_class_id_and_date( $eid, $start_date, $end_date ) {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	$class_id        = absint( $eid );
	$start_date      = sanitize_text_field( $start_date );
	$end_date        = sanitize_text_field( $end_date );
	
	// Fetch student and teacher IDs.

	$students = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'fields' => 'ID' ) );

	$teachers = mjschool_get_teacher_by_class_id( $class_id );
	// Merge and extract IDs.
	$user_ids = array_merge( $students, $teachers );
	if ( empty( $user_ids ) ) {
		return array(); // No users, return empty array early.
	}
	// Convert user IDs array to comma-separated values for SQL IN clause.
	$user_ids_placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
	// Construct the query.
	$query = "SELECT * FROM {$table_issuebook} WHERE issue_date BETWEEN %s AND %s AND student_id IN ($user_ids_placeholders)";
	// Prepare query with dynamic number of user IDs.
	$prepared_query = $wpdb->prepare( $query, array_merge( array( $start_date, $end_date ), $user_ids ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->get_results( $prepared_query );
}
/**
 * Check issued books by class ID, section ID and date range.
 *
 * @since 1.0.0
 *
 * @param int    $eid          Class ID.
 * @param int    $class_section Section ID.
 * @param string $start_date   Start date.
 * @param string $end_date     End date.
 * @return array Issued books list.
 */
function mjschool_check_book_issued_by_class_id_and_class_section_and_date( $eid, $class_section, $start_date, $end_date ) {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	$class_id        = absint( $eid );
	$class_section   = absint( $class_section );
	$start_date      = sanitize_text_field( $start_date );
	$end_date        = sanitize_text_field( $end_date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM {$table_issuebook} WHERE issue_date BETWEEN %s AND %s AND class_id = %d AND section_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$booklist = $wpdb->get_results( $wpdb->prepare( $query, $start_date, $end_date, $class_id, $class_section ) );
	return $booklist;
}
/**
 * Check issued books between date range.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return array Book records.
 */
function mjschool_check_book_issued_by_start_date_and_end_date( $start_date, $end_date ) {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM {$table_issuebook} WHERE issue_date BETWEEN %s AND %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$booklist = $wpdb->get_results( $wpdb->prepare( $query, $start_date, $end_date ) );
	return $booklist;
}
/**
 * View attendance status for a specific user, class and date.
 *
 * @since 1.0.0
 *
 * @param string $date     Date.
 * @param int    $cid      Class ID.
 * @param int    $id       Student ID.
 * @return array Attendance status.
 */
function mjschool_view_attendance_status_for_date( $date, $cid, $id ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	$user_id  = absint( $id );
	$class_id = absint( $cid );
	$date     = sanitize_text_field( $date );
	
	// Prepare the query with placeholders.
	$query = "SELECT status FROM {$tbl_name} WHERE user_id = %d AND class_id = %d AND attendence_date = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $class_id, $date ) );
	return $result;
}
/**
 * Get holiday details for a specific date.
 *
 * @since 1.0.0
 *
 * @param string $date Date.
 * @return array Holiday records.
 */
function mjschool_attendance_report_holiday_print_for_date( $date ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_holiday';
	
	$date = sanitize_text_field( $date );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM {$tbl_name} WHERE %s BETWEEN date AND end_date";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $date ) );
	return $result;
}
/**
 * Get holiday records by month and year.
 *
 * @since 1.0.0
 *
 * @param int $month Month.
 * @param int $year  Year.
 * @return array Holiday records.
 */
function mjschool_get_all_holiday_by_month_year( $month, $year ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_holiday';
	
	$month = absint( $month );
	$year  = absint( $year );
	
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tbl_name WHERE YEAR(date) = %d AND MONTH(date) = %d", $year, $month ) );
	return $result;
}
/**
 * Get admission list based on date range.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @return array Filtered user list.
 */
// phpcs:disable
function mjschool_get_all_admission_by_start_date_to_end_date( $start_date, $end_date ) {
    $start_date = sanitize_text_field( $start_date );
    $end_date   = sanitize_text_field( $end_date );
    
    $args = array(
        'role'    => 'student_temp',
        'orderby' => 'ID',
        'order'   => 'ASC',
        'number'  => -1,
    );
    $all_users = get_users( $args );
    $filtered  = array();
    $start_ts = strtotime( $start_date );
    $end_ts   = strtotime( $end_date );
    foreach ( $all_users as $user ) {
        $admission_date = get_user_meta( $user->ID, 'admission_date', true );
        if ( empty( $admission_date ) ) {
            continue;
        }
        // Try to normalize both formats.
        $ts = strtotime( str_replace( '/', '-', $admission_date ) );
        if ( $ts && $ts >= $start_ts && $ts <= $end_ts ) {
            $filtered[] = $user;
        }
    }
    return $filtered;
}
// phpcs:enable
/**
 * Get start & end date based on date type.
 *
 * @since 1.0.0
 *
 * @param string $date_type Keyword for date range.
 * @return string JSON encoded array of start/end date.
 */
function mjschool_all_date_type_value( $date_type ) {
	$date_type  = sanitize_text_field( $date_type );
	$start_date = '';
	$end_date   = '';
	$array_res  = array();
	if ( $date_type === 'today' ) {
		$start_date = date( 'Y-m-d' );
		$end_date   = date( 'Y-m-d' );
	} elseif ( $date_type === 'this_week' ) {
		// check the current day.
		if ( date( 'D' ) != 'Mon' ) {
			// take the last monday.
			$start_date = date( 'Y-m-d', strtotime( 'last sunday' ) );
		} else {
			$start_date = date( 'Y-m-d' );
		}
		// always next saturday.
		if ( date( 'D' ) != 'Sat' ) {
			$end_date = date( 'Y-m-d', strtotime( 'next saturday' ) );
		} else {
			$end_date = date( 'Y-m-d' );
		}
	} elseif ( $date_type === 'last_week' ) {
		$previous_week = strtotime( '-1 week +1 day' );
		$start_week    = strtotime( 'last sunday midnight', $previous_week );
		$end_week      = strtotime( 'next saturday', $start_week );
		$start_date    = date( 'Y-m-d', $start_week );
		$end_date      = date( 'Y-m-d', $end_week );
	} elseif ( $date_type === 'this_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	} elseif ( $date_type === 'last_month' ) {
		$start_date = date( 'Y-m-d', strtotime( 'first day of previous month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of previous month' ) );
	} elseif ( $date_type === 'last_3_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-2 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	} elseif ( $date_type === 'last_6_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-5 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	} elseif ( $date_type === 'last_12_month' ) {
		$month_date = date( 'Y-m-d', strtotime( '-11 month' ) );
		$start_date = date( 'Y-m-01', strtotime( $month_date ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	} elseif ( $date_type === 'this_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '0 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
	} elseif ( $date_type === 'last_year' ) {
		$start_date = date( 'Y-01-01', strtotime( '-1 year' ) );
		$end_date   = date( 'Y-12-t', strtotime( $start_date ) );
	}
	$array_res[] = $start_date;
	$array_res[] = $end_date;
	return wp_json_encode( $array_res );
}
/**
 * Get attendance status for student for a date.
 *
 * @since 1.0.0
 *
 * @param string $date     Date.
 * @param int    $class_id Class ID.
 * @param int    $user_id  User ID.
 * @return string Status letter (P/A/L/H/F).
 */
function mjschool_attendance_report_all_status_value( $date, $class_id, $user_id ) {
	// Sanitize inputs immediately.
	$date     = sanitize_text_field( $date );
	$class_id = absint( $class_id );
	$user_id  = absint( $user_id );
	
	// Replace this with your desired date.
	$current = new DateTime( $date );
	$dayName = $current->format( 'l' );
	global $wpdb;
	// HOLIDAY ATTENDANCE DATA.
	$tbl_name = $wpdb->prefix . 'mjschool_holiday';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$holiday_att_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tbl_name WHERE %s between date and end_date", $date ) );
	// ATTENDANCE DATA WITH STATUS.
	$tbl_name = $wpdb->prefix . 'mjschool_sub_attendance';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$attendance_data = $wpdb->get_row( $wpdb->prepare( "SELECT status FROM $tbl_name WHERE user_id = %d AND class_id = %d AND attendance_date = %s", $user_id, $class_id, $date ) );
	if ( ! empty( $holiday_att_data ) ) {
		$result = esc_html__( 'H', 'mjschool' );
	} elseif ( ! empty( $attendance_data ) ) {
		if ( $attendance_data->status === 'Present' ) {
			$status = esc_html__( 'P', 'mjschool' );
		} elseif ( $attendance_data->status === 'Absent' ) {
			$status = esc_html__( 'A', 'mjschool' );
		} elseif ( $attendance_data->status === 'Late' ) {
			$status = esc_html__( 'L', 'mjschool' );
		} elseif ( $attendance_data->status === 'Half Day' ) {
			$status = esc_html__( 'F', 'mjschool' );
		}
		$result = $status;
	} elseif ( $dayName === 'Sunday' ) {
		$result = esc_html__( 'H', 'mjschool' );
	} else {
		// CHECK ATTENDANCE ADDED FOR CLASS.
		$query = $wpdb->prepare( "SELECT status FROM $tbl_name WHERE class_id = %d AND attendance_date = %s AND sub_id IS NULL", $class_id, $date );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$attendance_data = $wpdb->get_row( $query );
		if ( ! empty( $attendance_data ) ) {
			$result = $status = esc_html__( 'A', 'mjschool' );
		} else {
			$result = '';
		}
	}
	return $result;
}
/**
 * Get total present count by date range and class.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $cid        Class ID.
 * @return int Present count.
 */
function mjschool_view_attendance_report_for_start_date_enddate_total_present( $start_date, $end_date, $cid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = absint( $cid );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date BETWEEN %s AND %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return count( $result );
}
/**
 * Get total absent count by date range and class.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $cid        Class ID.
 * @return int absent count.
 */
function mjschool_view_attendance_report_for_start_date_enddate_absent( $start_date, $end_date, $cid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = absint( $cid );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date BETWEEN %s AND %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $start_date, $end_date, $class_id, 'Absent' ) );
	// Return the count of results.
	return count( $result );
}
/**
 * Get total late count by date range and class.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $cid        Class ID.
 * @return int late count.
 */
function mjschool_view_attendance_report_for_start_date_enddate_Late( $start_date, $end_date, $cid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = absint( $cid );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date BETWEEN %s AND %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $start_date, $end_date, $class_id, 'Late' ) );
	// Return the count of results.
	return count( $result );
}
/**
 * Get total half-day count by date range and class.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $cid        Class ID.
 * @return int half-day count.
 */
function mjschool_view_attendance_report_for_start_date_enddate_Half_day( $start_date, $end_date, $cid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$class_id   = absint( $cid );
	
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE role_name = %s AND attendence_date BETWEEN %s AND %s AND class_id = %d AND status = %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'student', $start_date, $end_date, $class_id, 'Half Day' ) );
	// Return the count of results.
	return count( $result );
}
/**
 * Get total number of students in a class (excluding blocked/disabled).
 *
 * @since 1.0.0
 *
 * @param int $class_id Class ID.
 * @return int Total students.
 */
function mjschool_view_attendance_report_for_start_date_enddate_total( $class_id ) {
	global $wpdb;
	$class_id = absint( $class_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$exlude_id = mjschool_approve_student_list();

	$userdata = get_users(array( 'role' => 'student', 'meta_key' => 'class_name', 'meta_value' => $class_id, 'exclude' => $exlude_id ) );

	return count( $userdata );
}
/**
 * Retrieve leave records for students based on date, student ID, and status.
 *
 * This function dynamically filters leave entries depending on whether
 * all students or specific students are selected, and whether all statuses
 * or specific statuses are requested.
 *
 * @since 1.0.0
 *
 * @param string     $leave_date The leave start date to filter records.
 * @param int|string $sid        Student ID or 'all_student' for all students.
 * @param string     $status     Leave status or 'all_status' for all statuses.
 *
 * @return array                 List of leave records matching the criteria.
 */
function mjschool_view_leave_student_for_data( $leave_date, $sid, $status ) {
	global $wpdb;
	$tbl_name   = $wpdb->prefix . 'mjschool_leave';
	$leave_date = sanitize_text_field( $leave_date );
	$sid        = sanitize_text_field( $sid );
	$status     = sanitize_text_field( $status );
	$Student_id = ( $sid === 'all_student' ) ? 'all_student' : absint( $sid );
	// Prepare the query based on the conditions.
	if ( $Student_id === 'all_student' && $status === 'all_status' ) {
		$query = "SELECT * FROM $tbl_name WHERE start_date = %s";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( $query, $leave_date ) );
	} elseif ( $Student_id === 'all_student' && ! empty( $status ) && $status != 'all_status' ) {
		$query = "SELECT * FROM $tbl_name WHERE status = %s AND start_date = %s";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( $query, $status, $leave_date ) );
	} elseif ( $status === 'all_status' && ! empty( $Student_id ) && $Student_id != 'all_student' ) {
		$query = "SELECT * FROM $tbl_name WHERE student_id = %d AND start_date = %s";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( $query, $Student_id, $leave_date ) );
	} else {
		$query = "SELECT * FROM $tbl_name WHERE student_id = %d AND start_date = %s AND status = %s";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( $query, $Student_id, $leave_date, $status ) );
	}
	return $result;
}
/**
 * Get user details (ID, first name, last name).
 *
 * @since 1.0.0
 *
 * @param int $student_id User ID.
 * @return array User details.
 */
function mjschool_get_user_detail_by_id( $student_id ) {
	$user_return = array();
	$first_name  = get_user_meta( $student_id, 'first_name', true );
	$last_name   = get_user_meta( $student_id, 'last_name', true );
	$student_id  = get_user_meta( $student_id, 'patient_id', true );
	$user_return = array(
		'id'         => sanitize_text_field($student_id),
		'first_name' => sanitize_text_field($first_name),
		'last_name'  => sanitize_text_field($last_name),
	);
	return $user_return;
}
/**
 * Get the latest 5 messages for a given user.
 *
 * @since 1.0.0
 *
 * @param int $sid Student/User ID.
 *
 * @return array List of message objects.
 */
function mjschool_message_dashboard( $sid ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_message';
	$user_id  = absint( $sid );
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $tbl_name WHERE receiver = %d ORDER BY message_id DESC LIMIT 5";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
	return $result;
}
/**
 * Retrieve latest 5 holidays for dashboard.
 *
 * @since 1.0.0
 *
 * @return array Holiday records.
 */
function mjschool_holiday_dashboard() {
	global $wpdb;
	$smgt_holiday = $wpdb->prefix . 'mjschool_holiday';
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $smgt_holiday WHERE status = %d ORDER BY holiday_id DESC LIMIT 5";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 0 ) );
	return $result;
}
/**
 * Get latest 5 notifications for dashboard.
 *
 * @since 1.0.0
 * @return array Notification objects.
 */
function mjschool_notification_dashboard() {
	global $wpdb;
	$mjschool_notification = $wpdb->prefix . 'mjschool_notification';
	// Prepare the query (although no dynamic values, still using prepare for best practice).
	$query = "SELECT * FROM $mjschool_notification ORDER BY notification_id DESC LIMIT 5";
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Get latest 5 notifications of a specific student for dashboard.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Notification objects.
 */
function mjschool_user_notification_dashboard( $student_id ) {
	global $wpdb;
	$mjschool_notification = $wpdb->prefix . 'mjschool_notification';
	$id                    = absint( $student_id );
	// Prepare the query with placeholders.
	$query = "SELECT * FROM $mjschool_notification WHERE student_id = %d ORDER BY notification_id DESC LIMIT 5";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Get latest 5 classes for dashboard.
 *
 * @since 1.0.0
 * @return array Class records.
 */
function mjschool_class_dashboard() {
	global $wpdb;
	$smgt_class = $wpdb->prefix . 'mjschool_class';
	// Prepare the query (no dynamic values, but using prepare for consistency).
	$query = "SELECT * FROM $smgt_class ORDER BY class_id DESC LIMIT 5";
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Get fees payment record by ID.
 *
 * @since 1.0.0
 * @param int $student_id Fee payment ID.
 * @return object|null Fees payment record.
 */
function mjschool_get_feespayment_by_id( $student_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_fees_payment';
	$id         = absint( $student_id );
	// Prepare the query with a placeholder.
	$query = "SELECT * FROM $table_name WHERE fees_pay_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( $query, $id ) );
	return $retrieve_subject;
}
/**
 * Get 4 latest fees payment records of a student.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Fee payment records.
 */
function mjschool_feespayment_detail( $student_id ) {
	global $wpdb;
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$id                          = absint( $student_id );
	// Prepare the query with a placeholder for the student_id.
	$query = "SELECT * FROM $table_mjschool_fees_payment WHERE student_id = %d ORDER BY fees_pay_id DESC LIMIT 4";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Get all fee payment details for a student.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Fee payment records.
 */
function mjschool_get_fees_payment_detailpage( $student_id ) {
	global $wpdb;
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$id                          = absint( $student_id );
	// Prepare the query with a placeholder for the student_id.
	$query = "SELECT * FROM $table_mjschool_fees_payment WHERE student_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Get monthly attendance data for a student.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Attendance records.
 */
function mjschool_monthly_attendence( $student_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$id = absint( $student_id );
	// Get the current date, first day, and last day of the month.
	$curr_date = date( 'Y-m-d' );
	$sdate     = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$edate     = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	// Prepare the query with placeholders for the dynamic values.
	$query = "SELECT * FROM $table_name WHERE attendance_date BETWEEN %s AND %s AND user_id = %d ORDER BY attendance_date DESC";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $sdate, $edate, $id ) );
	return $result;
}
/**
 * Get monthly attendance data of all children of a parent.
 *
 * @since 1.0.0
 * @param int $id Parent user ID.
 * @return array Attendance records.
 */
function mjschool_monthly_attendence_for_parent( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	
	$id        = absint( $id );
	$date      = date( 'Y-m-d' );
	$curr_date = date( 'Y-m-d', strtotime( $date ) );
	$user_data = mjschool_get_parents_child_id( $id );
	$sdate     = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$edate     = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	$result    = array();
	
	if ( ! empty( $user_data ) ) {
		foreach ( $user_data as $student_id ) {
			$student_id = absint( $student_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE attendance_date BETWEEN %s AND %s AND user_id = %d", $sdate, $edate, $student_id ) );
		}
	}
	if ( ! empty( $result ) ) {
		$mergedArray  = array_merge( ...$result );
		$unique_array = array_unique( $mergedArray, SORT_REGULAR );
	} else {
		$unique_array = array();
	}
	
	return $unique_array;
}
/**
 * Get all halltickets for a student.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Hallticket records.
 */
function mjschool_hall_ticket_list( $student_id ) {
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	$id                                    = absint( $student_id );
	// Prepare the query with a placeholder for user_id.
	$query = "SELECT * FROM {$table_name_mjschool_exam_hall_receipt} WHERE user_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Retrieves hall ticket details for a student by exam ID.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 * @param int $exam_id    Exam ID.
 *
 * @return array List of hall ticket records.
 */
function mjschool_hall_ticket_by_exam_id( $student_id, $exam_id ) {
	global $wpdb;
	$table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	$id                                    = absint( $student_id );
	$exam_id                               = absint( $exam_id );
	// Prepare the query with a placeholder for user_id.
	$query = "SELECT * FROM $table_name_mjschool_exam_hall_receipt WHERE user_id = %d AND exam_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id, $exam_id ) );
	return $result;
}
/**
 * Checks whether a student has any marks or contributions for a specific exam.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 * @param int $exam_id    Exam ID.
 *
 * @return array Result count indicating whether data exists.
 */
function mjschool_check_result( $student_id, $exam_id ) {
	global $wpdb;
	$table_name_marks = $wpdb->prefix . 'mjschool_marks';
	$id               = absint( $student_id );
	$exam_id          = absint( $exam_id );
	$query            = "SELECT COUNT(*) FROM $table_name_marks WHERE (marks > 0 OR contributions = 'yes' ) AND student_id = %d AND exam_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id, $exam_id ) );
	return $result;
}
/**
 * Retrieves homework details assigned to a student.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 *
 * @return array List of homework records.
 */
function mjschool_student_homework_detail( $student_id ) {
	global $wpdb;
	$student_id  = absint( $student_id );
	$class_id    = get_user_meta( $student_id, 'class_name', true );
	$class_id    = absint( $class_id );
	$table_name  = $wpdb->prefix . 'mjschool_homework';
	$table_name2 = $wpdb->prefix . 'mjschool_student_homework';
	// Prepare the query with placeholders for the student_id and class_name.
	$query = "SELECT * FROM $table_name AS a LEFT JOIN $table_name2 AS b ON a.homework_id = b.homework_id WHERE b.student_id = %d AND a.class_name = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $student_id, $class_id ) );
	return $result;
}
/**
 * Retrieves issued library books for a given student.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 *
 * @return array List of issued book records.
 */
function mjschool_student_issuebook_detail( $student_id ) {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	$id              = absint( $student_id );
	// Prepare the query with a placeholder for student_id.
	$query = "SELECT * FROM $table_issuebook WHERE student_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Retrieves all messages sent to a student.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID (receiver).
 *
 * @return array List of message records.
 */
function mjschool_message_detail( $student_id ) {
	global $wpdb;
	$tbl_name_message = $wpdb->prefix . 'mjschool_message';
	$id               = absint( $student_id );
	// Prepare the query with a placeholder for receiver.
	$query = "SELECT * FROM $tbl_name_message WHERE receiver = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Converts a time value into a formatted 12-hour time string.
 *
 * @since 1.0.0
 *
 * @param string $time Time string.
 *
 * @return string Converted time in g:i:a format.
 */
function mjschool_time_convert( $time ) {
	$start_time_data   = $time;
	$starttime_convert = date( 'g:i:a', strtotime( $start_time_data ) );
	$starttime         = explode( ':', $starttime_convert );
	$start_hour        = $starttime[0];
	$start_min_convert = str_pad( $starttime[1], 2, '0', STR_PAD_LEFT );
	if ( $start_min_convert === '00' || $start_min_convert === '01' || $start_min_convert === '02' || $start_min_convert === '03' || $start_min_convert === '04' || $start_min_convert === '05' || $start_min_convert === '06' || $start_min_convert === '07' || $start_min_convert === '08' || $start_min_convert === '09' ) {
		$start_min = substr( $start_min_convert, 1 );
	} else {
		$start_min = $start_min_convert;
	}
	$start_am_pm = $starttime[2];
	$start_time  = $start_hour . ':' . $start_min . ':' . $start_am_pm;
	return $start_time;
}
/**
 * Retrieves all notifications created by a specific student.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 *
 * @return array List of notification records.
 */
function mjschool_get_all_notification_created_by( $student_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_notification';
	$user_id    = absint( $student_id );
	// Prepare the query with a placeholder for created_by.
	$query = "SELECT * FROM $table_name WHERE created_by = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
	return $results;
}
/**
 * Retrieves the latest 5 notifications created by a user for dashboard display.
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID.
 *
 * @return array List of notification records.
 */
function mjschool_get_all_notification_created_by_for_dashboard( $user_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_notification';
	$user_id = absint( $user_id );
	// Fetch the last 5 notifications created by the given user_id, ordered by the created_at column.
	$query = $wpdb->prepare( "SELECT * FROM {$table_name }WHERE created_by = %d ORDER BY notification_id DESC LIMIT 5", $user_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->get_results( $query );
}
/**
 * Retrieves all notifications for parents based on their child's student IDs.
 *
 * @since 1.0.0
 *
 * @param int $user_id Parent user ID.
 *
 * @return array List of unique notification records.
 */
function mjschool_get_all_notification_for_parent( $user_id ) {
	$user_id = absint( $user_id );
	$user_data = mjschool_get_parents_child_id( $user_id );
	if ( ! empty( $user_data ) ) {
		foreach ( $user_data as $student_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_notification';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result[] = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE student_id=" . $student_id );
		}
	}
	if ( ! empty( $result ) ) {
		$mergedArray  = array_merge( ...$result );
		$unique_array = array_unique( $mergedArray, SORT_REGULAR );
	} else {
		$unique_array = '';
	}
	return $unique_array;
}
/**
 * Retrieves the latest 5 notifications for parents for dashboard display.
 *
 * @since 1.0.0
 *
 * @param int $user_id Parent user ID.
 *
 * @return array List of recent notifications.
 */
function mjschool_get_all_notification_for_parent_for_dashboard( $user_id ) {
	$user_id = absint( $user_id );
	$user_data = mjschool_get_parents_child_id( $user_id );
	$result    = array();
	if ( ! empty( $user_data ) ) {
		foreach ( $user_data as $student_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_notification';
			// Fetch the last 5 notifications for each student_id.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$notifications = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d ORDER BY notification_id DESC LIMIT 5", $student_id ) );
			$result[]      = $notifications;
		}
	}
	if ( ! empty( $result ) ) {
		// Merge all results into one array.
		$mergedArray = array_merge( ...$result );
		// Sort the merged array by created_at in descending order.
		usort(
			$mergedArray,
			function ( $a, $b ) {
				return strcmp( $b->created_at, $a->created_at );
			}
		);
		// Get the last 5 records.
		$unique_array = array_slice( $mergedArray, 0, 5 );
	} else {
		$unique_array = array();
	}
	return $unique_array;
}
/**
 * Retrieves notifications created for a student.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 *
 * @return array List of notification records.
 */
function mjschool_get_student_own_notification_created_by( $student_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_notification';
	$user_id    = absint( $student_id );
	// Prepare the query with a placeholder for student_id.
	$query = "SELECT * FROM $table_name WHERE student_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
	return $results;
}
/**
 * Retrieves the latest 5 notifications for a student for dashboard display.
 *
 * @since 1.0.0
 *
 * @param int $student_id Student ID.
 *
 * @return array List of recent notification records.
 */
function mjschool_get_student_own_notification_created_by_for_dashboard( $student_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_notification';
	$user_id    = absint( $student_id );
	// Fetch the last 5 notifications for the given student_id, ordered by the created_at column.
	$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d ORDER BY notification_id DESC LIMIT 5", $user_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->get_results( $query );
}
/**
 * Retrieves the latest 5 transport records for dashboard display.
 *
 * @since 1.0.0
 *
 * @return array List of transport records.
 */
function mjschool_get_trasport_data_for_dashboard() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_transport';
	// Prepare the query (no dynamic data here, but it is still a good practice).
	$query = "SELECT * FROM {$table_name} ORDER BY transport_id DESC LIMIT 5";
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Retrieves transport assignment details by transport ID.
 *
 * @since 1.0.0
 *
 * @param int $transport_id Transport ID.
 *
 * @return object|null Transport assignment record.
 */
function mjschool_get_assign_transport_by_id( $transport_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_assign_transport';
	$tid        = absint( $transport_id );
	// Prepare the query with a placeholder for transport_id.
	$query = "SELECT * FROM $table_name WHERE transport_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( $query, $tid ) );
	return $retrieve_subject;
}
/**
 * Retrieves all transport assignment records.
 *
 * @since 1.0.0
 *
 * @return array List of transport assignment records.
 */
function mjschool_get_all_assign_transport() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_assign_transport';
	// Prepare the query (no dynamic data here, but it's still a good practice).
	$query = "SELECT * FROM {$table_name}";
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_results( $query );
	return $retrieve_subject;
}
/**
 * Retrieves a single transport assignment record by assignment ID.
 *
 * @since 1.0.0
 *
 * @param int $transport_id Assignment ID.
 *
 * @return object|null Transport assignment record.
 */
function mjschool_get_single_assign_transport_by_id( $transport_id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_assign_transport';
	$tid        = absint( $transport_id );
	// Prepare the query with a placeholder for assign_transport_id.
	$query = "SELECT * FROM {$table_name} WHERE assign_transport_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_row( $wpdb->prepare( $query, $tid ) );
	return $retrieve_subject;
}
/**
 * Retrieves assigned bed information for a student.
 *
 * @since 1.0.0
 *
 * @param int $studnet_id Student ID.
 *
 * @return object|null Assigned bed record.
 */
function mjschool_student_assign_bed_data_by_student_id( $studnet_id ) {
	global $wpdb;
	$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
	$id                         = absint( $studnet_id );
	// Prepare the query with a placeholder for student_id.
	$query = "SELECT * FROM {$table_mjschool_assign_beds} WHERE student_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( $query, $id ) );
	return $result;
}
/**
 * Retrieves assigned bed information for a student filtered by hostel.
 *
 * @since 1.0.0
 *
 * @param int $studnet_id Student ID.
 * @param int $hostel_id  Hostel ID.
 *
 * @return object|null Assigned bed record.
 */
function mjschool_student_assign_bed_data_by_student_and_hostel_id( $studnet_id, $hostel_id ) {
	global $wpdb;
	$table_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
	$id                         = absint( $studnet_id );
	$hostel_id                  = absint( $hostel_id );
	// Prepare the query with a placeholder for student_id.
	$query = "SELECT * FROM {$table_mjschool_assign_beds} WHERE student_id = %d AND hostel_id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( $query, $id, $hostel_id ) );
	return $result;
}
/**
 * Retrieves room details by room ID.
 *
 * @since 1.0.0
 *
 * @param int $id Room ID.
 *
 * @return object|null Room record.
 */
function mjschool_get_room__data_by_room_id( $id ) {
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	$room_id         = absint( $id );
	// Prepare the query with a placeholder for room_id.
	$query = "SELECT * FROM {$table_mjschool_room} WHERE id = %d";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $wpdb->prepare( $query, $room_id ) );
	return $result;
}
/**
 * Retrieves hostel type by hostel ID.
 *
 * @since 1.0.0
 *
 * @param int $hostel_id Hostel ID.
 *
 * @return string Hostel type or 'N/A'.
 */
function mjschool_hostel_type_by_id( $hostel_id ) {
	global $wpdb;
	$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
	$hostel_id = absint( $hostel_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( "SELECT * FROM {$table_mjschool_hostel} where id=" . $hostel_id );
	if ( ! empty( $result->hostel_type ) ) {
		return $result->hostel_type;
	} else {
		return 'N/A';
	}
}
/**
 * Returns the appropriate datatable header display class.
 *
 * @since 1.0.0
 *
 * @return string CSS class name.
 */
function mjschool_datatable_header() {
	$datatbl_heder_value = get_option( 'mjschool_heder_enable' );
	if ( $datatbl_heder_value === 'no' ) {
		$result = 'mjschool_heder_none';
	} else {
		$result = 'mjschool_heder_block';
	}
	return $result;
}
/**
 * Retrieves dashboard card access permissions based on user role.
 *
 * @since 1.0.0
 *
 * @return mixed Dashboard card access option value.
 */
function mjschool_frontend_dashboard_card_access() {
	$user_id = get_current_user_id();
	$role    = mjschool_get_roles( $user_id );
	if ( $role === 'student' ) {
		$card_access = get_option( 'mjschool_dashboard_card_for_student' );
	} elseif ( $role === 'teacher' ) {
		$card_access = get_option( 'mjschool_dashboard_card_for_teacher' );
	} elseif ( $role === 'parent' ) {
		$card_access = get_option( 'mjschool_dashboard_card_for_parent' );
	} elseif ( $role === 'supportstaff' ) {
		$card_access = get_option( 'mjschool_dashboard_card_for_support_staff' );
	}
	return $card_access;
}
/**
 * Retrieves total income records within a date range.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 *
 * @return array List of income records.
 */
function mjschool_get_total_income( $start_date, $end_date ) {
	global $wpdb;
	$table_income = $wpdb->prefix . 'mjschool_income_expense';
	$start_date = sanitize_text_field( $start_date );
	$end_date = sanitize_text_field( $end_date );
	// Prepare the query with placeholders for start_date and end_date.
	$query = "SELECT * FROM {$table_income} WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'income', $start_date, $end_date ) );
	return $result;
}
/**
 * Retrieves total expense records within a date range.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 *
 * @return array List of expense records.
 */
function mjschool_get_total_expense( $start_date, $end_date ) {
	global $wpdb;
	$table_income = $wpdb->prefix . 'mjschool_income_expense';
	$start_date = sanitize_text_field( $start_date );
	$end_date = sanitize_text_field( $end_date );
	// Prepare the query with placeholders for start_date and end_date.
	$query = "SELECT * FROM {$table_income} WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s";
	// Prepare and execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( $query, 'expense', $start_date, $end_date ) );
	return $result;
}
/**
 * Appends a record to the audit log.
 *
 * @since 1.0.0
 *
 * @param string $audit_action Action description.
 * @param int    $user_id      User ID.
 * @param int    $created_by   Creator ID.
 * @param string $action       Action type.
 * @param string $module       Module name.
 *
 * @return int|false Insert result.
 */
function mjschool_append_audit_log( $audit_action, $user_id, $created_by, $action, $module ) {
	global $wpdb;
	$table_mjschool_audit_log   = $wpdb->prefix . 'mjschool_audit_log';
	$ip_address             = getHostByName( getHostName() );
	$data['audit_action']   = sanitize_text_field( $audit_action );
	$data['user_id']        = absint( $user_id );
	$data['action']         = sanitize_text_field( $action );
	$data['ip_address']     = $ip_address;
	$data['created_by']     = sanitize_text_field( $created_by );
	$data['module']         = sanitize_text_field( $module );
	$data['created_at']     = date( 'Y-m-d' );
	$data['deleted_status'] = 0;
	$data['date_time']      = date( 'Y-m-d H:i:s' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_mjschool_audit_log, $data );
	return $result;
}
/**
 * Appends a user login activity record to the user log table.
 *
 * @since 1.0.0
 *
 * @param string $user_login Username.
 * @param string $role       User role.
 *
 * @return int|false Insert result.
 */
function mjschool_append_user_log( $user_login, $role ) {
	global $wpdb;
	$table_mjschool_user_log    = $wpdb->prefix . 'mjschool_user_log';
	$ip_address             = getHostByName( getHostName() );
	$data['user_login']     = $user_login;
	$data['role']           = $role;
	$data['ip_address']     = $ip_address;
	$data['created_at']     = date( 'Y-m-d' );
	$data['deleted_status'] = 0;
	$data['date_time']      = date( 'Y-m-d H:i:s' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->insert( $table_mjschool_user_log, $data );
	return $result;
}
/**
 * Adds default admission fees type if not already created.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_add_default_admission_fees_type() {
	global $wpdb;
	$data['category_name'] = 'Admission Fees';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s", $data['category_name'], 'smgt_feetype' ) );
	if ( empty( $post ) ) {
		$obj_fees = new mjschool_fees();
		$args     = array(
			'post_type'      => 'post', // Change this to your custom post type if necessary.
			'title'          => $data['category_name'],
			'posts_per_page' => 1,
		);
		$query    = new WP_Query( $args );
		if ( ! ( $query->have_posts() ) ) {
			$result = $obj_fees->mjschool_add_feetype( $data );
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$id                  = $wpdb->insert_id;
			$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
			// -------usersmeta table data.--------------
			$feedata['fees_title_id'] = sanitize_text_field( $id );
			$feedata['class_id']      = 0;
			$feedata['section_id']    = 0;
			$feedata['fees_amount']   = get_option( 'mjschool_admission_amount' );
			$feedata['description']   = '';
			$feedata['created_date']  = date( 'Y-m-d H:i:s' );
			$feedata['created_by']    = get_current_user_id();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_mjschool_fees, $feedata );
		}
	}
}
/**
 * Adds default library period values if not already created.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_add_default_library_periods() {
	global $wpdb;
	$cartegory_array = array( '10', '20', '30' );
	foreach ( $cartegory_array as $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s", $data, 'mjschool_bookperiod' ) );
		if ( empty( $post ) ) {
			$result = wp_insert_post(
				array(
					'post_status' => 'publish',
					'post_type'   => 'mjschool_bookperiod',
					'post_title'  => $data,
				)
			);
		}
	}
}
/**
 * Adds default registration fees type if not already created.
 *
 * @since 1.0.0
 *
 * @return void
 */
function mjschool_add_default_registration_fees_type() {
	global $wpdb;
	$data['category_name'] = 'Registration Fees';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$post = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_title = %s AND post_type = %s", $data['category_name'], 'smgt_feetype' ) );
	if ( empty( $post ) ) {
		$obj_fees = new mjschool_fees();
		$args     = array(
			'post_type'      => 'post', // Change this to your custom post type if necessary.
			'title'          => $data['category_name'],
			'posts_per_page' => 1,
		);
		$query    = new WP_Query( $args );
		if ( ! ( $query->have_posts() ) ) {
			$result = $obj_fees->mjschool_add_feetype( $data );
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$id                  = $wpdb->insert_id;
			$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
			// -------Usersmeta table data.--------------
			$feedata['fees_title_id'] = sanitize_text_field( $id );
			$feedata['class_id']      = 0;
			$feedata['section_id']    = 0;
			$feedata['fees_amount']   = get_option( 'mjschool_registration_amount' );
			$feedata['description']   = '';
			$feedata['created_date']  = date( 'Y-m-d H:i:s' );
			$feedata['created_by']    = get_current_user_id();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->insert( $table_mjschool_fees, $feedata );
		}
	}
}
/**
 * Generates and stores an admission fees invoice.
 *
 * @since 1.0.0
 *
 * @param float $admission_fees_amount Total admission fees amount.
 * @param int   $user_id               Student/User ID.
 * @param int   $admission_fees_id     Fees category ID.
 * @param int   $class_id              Class ID.
 * @param int   $section_id            Section ID.
 * @param string $description          Invoice description.
 *
 * @return int Inserted invoice ID.
 */
function mjschool_generate_admission_fees_invoice( $admission_fees_amount, $user_id, $admission_fees_id, $class_id, $section_id, $description ) {
	global $wpdb;
	$mjschool_fees_table         = $wpdb->prefix . 'mjschool_fees';
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$max_invoice_id          = $wpdb->get_var( "SELECT MAX(invoice_id) FROM {$table_mjschool_fees_payment}" );
	$next_invoice_id         = $max_invoice_id ? $max_invoice_id + 1 : 1;
	$feedata['class_id']     = $class_id;
	$feedata['section_id']   = $section_id;
	$feedata['total_amount'] = $admission_fees_amount;
	$feedata['fees_amount']  = $admission_fees_amount;
	$feedata['description']  = $description;
	$feedata['start_year']   = date( 'Y-m-d' );
	$feedata['end_year']     = date( 'Y-m-d' );
	$feedata['paid_by_date'] = date( 'Y-m-d' );
	$feedata['created_date'] = date( 'Y-m-d H:i:s' );
	$feedata['created_by']   = get_current_user_id();
	$feedata['student_id']   = $user_id;
	$feedata['invoice_id']   = $next_invoice_id;
	$feedata['fees_id']      = $admission_fees_id;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$admission_result = $wpdb->insert( $table_mjschool_fees_payment, $feedata );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$admission_result = $wpdb->insert_id;
	return $admission_result;
}
/**
 * Generates a draft version of an admission fees invoice.
 *
 * @since 1.0.0
 *
 * @param float $admission_fees_amount Total admission fees amount.
 * @param int   $user_id               Student/User ID.
 * @param int   $admission_fees_id     Fees category ID.
 * @param int   $class_id              Class ID.
 * @param int   $section_id            Section ID.
 * @param string $description          Invoice description.
 *
 * @return int Inserted draft invoice ID.
 */
function mjschool_generate_admission_fees_invoice_draft( $admission_fees_amount, $user_id, $admission_fees_id, $class_id, $section_id, $description ) {
	global $wpdb;
	$mjschool_fees_table         = $wpdb->prefix . 'mjschool_fees';
	$table_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$feedata['class_id']         = $class_id;
	$feedata['section_id']       = $section_id;
	$feedata['total_amount']     = $admission_fees_amount;
	$feedata['fees_amount']      = $admission_fees_amount;
	$feedata['description']      = $description;
	$feedata['start_year']       = date( 'Y-m-d' );
	$feedata['end_year']         = date( 'Y-m-d' );
	$feedata['paid_by_date']     = date( 'Y-m-d' );
	$feedata['created_date']     = date( 'Y-m-d H:i:s' );
	$feedata['invoice_status']   = 'draft';
	$feedata['created_by']       = get_current_user_id();
	$feedata['student_id']       = $user_id;
	$feedata['fees_id']          = $admission_fees_id;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$admission_result = $wpdb->insert( $table_mjschool_fees_payment, $feedata );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$admission_result = $wpdb->insert_id;
	return $admission_result;
}
/**
 * Deletes an audit log record by its ID.
 *
 * @since 1.0.0
 *
 * @param int $id Audit log ID.
 *
 * @return int Number of deleted rows.
 */
function mjschool_delete_audit_log( $id ) {
	global $wpdb;
	$table_mjschool_audit_log = $wpdb->prefix . 'mjschool_audit_log';
	$audit_id             = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_mjschool_audit_log} WHERE id = %d", $audit_id ) );
	return $result;
}
/**
 * Deletes a migration log record by its ID.
 *
 * @since 1.0.0
 *
 * @param int $id Migration log ID.
 *
 * @return int Number of deleted rows.
 */
function mjschool_delete_migration_log( $id ) {
	global $wpdb;
	$table_mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log';
	$audit_id                 = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_mjschool_migration_log} WHERE id = %d", $audit_id ) );
	return $result;
}
/**
 * Retrieves student attendance records between given dates.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param string $type       Role type (student/teacher).
 *
 * @return array Attendance records.
 */
function mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type ) {
	global $wpdb;
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	$type       = sanitize_text_field( $type );
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	$query      = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE role_name = %s AND attendence_date BETWEEN %s AND %s ORDER BY attendence_date DESC", $type, $start_date, $end_date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Retrieves attendance for a specific member between two dates (Admin View).
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $id         Member ID.
 *
 * @return array Attendance data.
 */
function mjschool_get_member_attendence_beetween_satrt_date_to_enddate_for_admin( $start_date, $end_date, $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	$start_date = sanitize_text_field( $start_date );
	$end_date = sanitize_text_field( $end_date );
	$member_id  = absint( $id );
	$query      = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE user_id = %d AND attendence_date BETWEEN %s AND %s ORDER BY attendence_date DESC", $member_id, $start_date, $end_date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$member_result = $wpdb->get_results( $query );
	return $member_result;
}
/**
 * Retrieves class name assigned to a teacher.
 *
 * @since 1.0.0
 *
 * @param int $id Teacher ID.
 *
 * @return object|null Database row object.
 */
function mjschool_get_class_name_by_teacher_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_teacher_class';
	$teacher_id = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$teacher = $wpdb->get_row( $wpdb->prepare( "SELECT class_id FROM {$table_name} WHERE teacher_id=%d", $teacher_id ) );
	return $teacher;
}
/**
 * Retrieves student attendance between two dates (Admin View).
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $id         Class ID.
 *
 * @return array Attendance list.
 */
function mjschool_get_student_attendence_beetween_satrt_date_to_enddate_class_wise_for_admin( $start_date, $end_date, $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	$start_date = sanitize_text_field( $start_date );
	$end_date = sanitize_text_field( $end_date );
	$class_id   = absint( $id );
	$query      = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND attendence_date BETWEEN %s AND %s ORDER BY attendence_date DESC", $class_id, $start_date, $end_date );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$member_result = $wpdb->get_results( $query );
	return $member_result;
}
/**
 * Retrieves student attendance filtered by class between dates.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param int    $id         Class ID.
 * @param string $date_type  Filter type (period, today, this_month, etc.).
 *
 * @return array Attendance list.
 */
function mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$type       = 'student';
	$query      = '';
	if ( $date_type === 'period' ) {
		$start_date = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : '';
    	$end_date = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : '';
		if ( ! empty( $class_id ) && $class_id != 'all class' ) {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date DESC", $class_id, $start_date, $end_date );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM $table_name WHERE role_name = %s AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date DESC", $type, $start_date, $end_date );
		}
	} elseif ( in_array( $date_type, array( 'today', 'this_week', 'last_week', 'this_month', 'last_month', 'last_3_month', 'last_6_month', 'last_12_month', 'this_year', 'last_year' ) ) ) {
		$result     = mjschool_all_date_type_value( $date_type );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
		if ( ! empty( $class_id ) && $class_id != 'all class' ) {
			$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date DESC", $class_id, $start_date, $end_date );
		} else {
			$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE role_name = %s AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date DESC", $type, $start_date, $end_date );
		}
	} else {
		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE role_name = %s AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date DESC", $type, $start_date, $end_date );
	}
	if ( ! empty( $query ) ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->get_results( $query );
	}
	return array();
}
/**
 * Retrieves teacher attendance reports with various filters.
 *
 * @since 1.0.0
 *
 * @param string $start_date Start date.
 * @param string $end_date   End date.
 * @param mixed  $teacher_id Specific teacher ID or 'all_teacher'.
 * @param mixed  $status     Attendance status or 'all_status'.
 *
 * @return array Attendance records.
 */
function mjschool_teacher_view_attendance_for_report( $start_date, $end_date, $teacher_id, $status ) {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_attendence';
	$start_date = sanitize_text_field( $start_date );
	$end_date = sanitize_text_field( $end_date );
	$status = sanitize_text_field( $status );
	$teacher_id = absint( $teacher_id );
	// Base query and parameters.
	$query  = "SELECT * FROM {$tbl_name} WHERE role_name = %s AND attendence_date BETWEEN %s AND %s";
	$params = array( 'teacher', $start_date, $end_date );
	// Additional filters.
	if ( $teacher_id !== 'all_teacher' ) {
		$query   .= ' AND user_id = %d';
		$params[] = $teacher_id;
	}
	if ( $status !== 'all_status' ) {
		$query   .= ' AND status = %s';
		$params[] = $status;
	}
	// Append order by clause.
	$query .= ' ORDER BY attendence_date DESC';
	// Prepare and execute the query.
	$prepared_query = $wpdb->prepare( $query, $params );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $prepared_query );
	return $result;
}
/**
 * Sends push notifications using Firebase Cloud Messaging.
 *
 * @since 1.0.0
 *
 * @param string $json JSON payload for push notification.
 *
 * @return string|false FCM API response or false if token is missing.
 */
function mjschool_send_push_notification( $json ) {
	$firebase_token = get_option( 'mjschool_notification_fcm_key' );
	if ( $firebase_token ) {
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => 'https://fcm.googleapis.com/fcm/send',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 300,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => $json,
				CURLOPT_HTTPHEADER     => array(
					'Content-Type: application/json',
					'authorization: key=' . $firebase_token,
				),
			)
		);
		$response = curl_exec( $curl );
		$err      = curl_error( $curl );
		curl_close( $curl );
		return $response;
	} else {
		return false;
	}
}
/**
 * Retrieves all classes assigned to a teacher.
 *
 * @since 1.0.0
 *
 * @param int $id Teacher ID.
 *
 * @return array List of classes.
 */
function mjschool_get_class_by_teacher_id( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_teacher_class';
	$teacher_id = absint( $id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$teacher = $wpdb->get_results( $wpdb->prepare( "SELECT class_id FROM {$table_name} WHERE teacher_id=%d", $teacher_id ) );
	return $teacher;
}
/**
 * Retrieves student attendance for a class within the current month.
 *
 * @since 1.0.0
 *
 * @param string $start_date Ignored (auto sets first day of month).
 * @param string $end_date   Ignored (auto sets last day of month).
 * @param int    $id         Class ID.
 * @param string $date_type  Unused parameter.
 *
 * @return array Monthly attendance records.
 */
function mjschool_student_attendance_by_class_id( $start_date, $end_date, $id, $date_type ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$type       = 'student';
	// Sanitize and format the dates properly.
	$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	$class_id   = absint( $id );
	// Use prepare for the query.
	$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE role_name = %s AND class_id = %d AND attendance_date BETWEEN %s AND %s", $type, $class_id, $start_date, $end_date );
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Generate student display name with class and roll number.
 *
 * Retrieves the student's display name along with their class and roll number
 * in the format: Name ( ClassName - RollNo ).
 *
 * @since 1.0.0
 * @param int $user_id WordPress user ID of the student.
 * @return string Formatted student display name.
 */
function mjschool_student_display_name_class_and_roll_wise( $user_id ) {
	$user_id      = absint( $user_id );
	$user_info    = get_userdata( $user_id );
	$user_name    = $user_info->display_name;
	$class_id     = get_user_meta( $user_id, 'class_name', true );
	$classname    = mjschool_get_class_name( $class_id );
	$roll         = get_user_meta( $user_id, 'roll_id', true );
	$student_name = $user_name . '( ' . $classname . ' - ' . $roll . ' )';
	return $student_name;
}
/**
 * Generate student display name with roll number only.
 *
 * Retrieves the student's display name and roll number
 * in the format: Name ( RollNo ).
 *
 * @since 1.0.0
 * @param int $user_id WordPress user ID of the student.
 * @return string Student name with roll number or 'N/A' if user not found.
 */
function mjschool_student_display_name_with_roll( $user_id ) {
	$user_id   = absint( $user_id );
	$user_info = get_userdata( $user_id );
	if ( ! empty( $user_info ) ) {
		$user_name     = $user_info->display_name;
		$roll          = get_user_meta( $user_id, 'roll_id', true );
		$stundent_name = $user_name . '( ' . $roll . ' )';
		return $stundent_name;
	} else {
		return 'N/A';
	}
}
/**
 * Get user display name.
 *
 * Retrieves the WordPress user's display name.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @return string Display name or 'N/A' if not found.
 */
function mjschool_user_display_name( $user_id ) {
	$user_id   = absint( $user_id );
	$user_info = get_userdata( $user_id );
	if ( ! empty( $user_info ) ) {
		$user_name = $user_info->display_name;
		return $user_name;
	} else {
		return 'N/A';
	}
}
/**
 * Delete attendance record by ID (sub-attendance table).
 *
 * @since 1.0.0
 * @param int $id Attendance record ID.
 * @return int|false Number of rows affected or false on failure.
 */
function mjschool_delete_attendance( $id ) {
	global $wpdb;
	$table_mjschool_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
	$attendance_id             = absint( $id );
	// Use prepare for the query.
	$query = $wpdb->prepare( "DELETE FROM {$table_mjschool_attendance} WHERE attendance_id = %d", $attendance_id );
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $query );
	return $result;
}
/**
 * Delete teacher attendance record.
 *
 * @since 1.0.0
 * @param int $id Attendance ID from the teacher attendance table.
 * @return int|false Number of rows affected or false on failure.
 */
function mjschool_delete_attendance_teacher( $id ) {
	global $wpdb;
	$table_mjschool_attendance = $wpdb->prefix . 'mjschool_attendence';
	$attendance_id             = absint( $id );
	// Use prepare to safely include the ID in the query.
	$query = $wpdb->prepare( "DELETE FROM {$table_mjschool_attendance} WHERE attendence_id = %d", $attendance_id );
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $query );
	return $result;
}
/**
 * Update setup wizard step status.
 *
 * Marks a specific setup wizard step as completed.
 * If all steps are marked, updates wizard status to "yes".
 *
 * @since 1.0.0
 * @param string $step Step key to update.
 * @return void
 */
function mjschool_setup_wizard_steps_updates( $step ) {
	$wizard_status = get_option( 'mjschool_setup_wizard_status' );
	if ( $wizard_status === 'no' ) {
		$setup_wizard          = get_option( 'mjschool_setup_wizard_step' );
		$setup_wizard[ $step ] = 'yes';
		$setup_wizard          = update_option( 'mjschool_setup_wizard_step', $setup_wizard );
	}
	$wizard_step = get_option( 'mjschool_setup_wizard_step' );
	if ( ! in_array( 'no', $wizard_step ) ) {
		$mjschool_setup_wizard_status = 'yes';
		$setup_wizard_status_update   = update_option( 'mjschool_setup_wizard_status', $mjschool_setup_wizard_status );
	}
}
/**
 * Retrieve exam data for parent based on child's class & section.
 *
 * @since 1.0.0
 * @param int $student_id Student ID.
 * @return array Exam data list.
 */
function mjschool_get_exam_data_for_parent( $student_id ) {
	$student_id   = absint( $student_id );
	$class_id   = get_user_meta( $student_id, 'class_name', true );
	$section_id = get_user_meta( $student_id, 'class_section', true );
	$obj_exam = new Mjschool_Exam();
	if ( isset( $class_id ) && $section_id === '' ) {
		$retrieve_class = $obj_exam->mjschool_get_all_exam_by_class_id( $class_id );
	} else {
		$retrieve_class = mjschool_get_all_exam_by_class_id_and_section_id_array( $class_id, $section_id );
	}
	return $retrieve_class;
}
/**
 * Get user document list filtered by user type.
 *
 * Supports: student, parent, teacher, supportstaff, others.
 * Fetches document visibility based on user role access.
 *
 * @since 1.0.0
 * @param int $user_id WordPress user ID.
 * @param string $user_type User role type.
 * @return array List of documents.
 */
function mjschool_get_user_document_list( $user_id, $user_type ) {
	global $wpdb;
	$obj_document = new mjschool_document();
	$table_name   = $wpdb->prefix . 'mjschool_document';
	$user_id   = absint( $user_id );
	$user_type = sanitize_text_field( $user_type );
	if ( $user_type === 'student' ) {
		$section_id = get_user_meta( $user_id, 'class_section', true );
		$class_id   = get_user_meta( $user_id, 'class_name', true );
		if ( ! empty( $section_id ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( "SELECT * FROM {$table_name} where (class_id='all class' AND section_id='all section' AND student_id='all student' ) OR (student_id= $user_id) OR (class_id= $class_id AND section_id='all section' AND student_id='all student' ) OR (class_id= $class_id AND section_id = $section_id AND student_id='all student' ) ORDER BY created_date DESC" );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( "SELECT * FROM {$table_name} where (class_id='all class' AND section_id='all section' AND student_id='all student' ) OR (student_id= $user_id) OR (class_id= $class_id AND section_id='all section' AND student_id='all student' ) ORDER BY created_date DESC" );
		}
		return $result;
	} elseif ( $user_type === 'parent' ) {
		$user_data = mjschool_get_parents_child_id( $user_id );
		foreach ( $user_data as $student_id ) {
			$section_id = get_user_meta( $student_id, 'class_section', true );
			$class_id   = get_user_meta( $student_id, 'class_name', true );
			if ( ! empty( $section_id ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result[] = $wpdb->get_results( "SELECT * FROM {$table_name} where (class_id='all class' AND section_id='all section' AND student_id='all student' ) OR (student_id= $student_id) OR (class_id= $class_id AND section_id='all section' AND student_id='all student' ) OR (class_id= $class_id AND section_id = $section_id AND student_id='all student' ) ORDER BY created_date DESC" );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result[] = $wpdb->get_results( "SELECT * FROM {$table_name} where (class_id='all class' AND section_id='all section' AND student_id='all student' ) OR (student_id= $student_id) OR (class_id= $class_id AND section_id='all section' AND student_id='all student' ) ORDER BY created_date DESC" );
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result[] = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE document_for ='parent' AND (student_id='all parent' ) OR (student_id= $user_id) ORDER BY created_date DESC" );
		}
		$mergedArray  = array_merge( ...$result );
		$unique_array = array_unique( $mergedArray, SORT_REGULAR );
		return $unique_array;
	} elseif ( $user_type === 'teacher' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE document_for ='teacher' AND (student_id='all teacher' ) OR (student_id= $user_id) OR (createdby= $user_id) ORDER BY created_date DESC" );
		return $result;
	} elseif ( $user_type === 'supportstaff' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE document_for ='supportstaff' AND (student_id='all supportstaff' ) OR (student_id= $user_id) OR (createdby= $user_id) ORDER BY created_date DESC" );
		return $result;
	} else {
		$result = $obj_document->mjschool_get_own_documents( $user_id );
		return $result;
	}
}
/**
 * Get issued book list by status.
 *
 * Fetches books where status is 'Issue' or 'Submitted'.
 *
 * @since 1.0.0
 * @return array List of issued or submitted books.
 */
function mjschool_check_book_issued_by_status() {
	global $wpdb;
	$table_issuebook = $wpdb->prefix . 'mjschool_library_book_issue';
	// Use prepare for the query.
	$query = $wpdb->prepare( "SELECT * FROM {$table_issuebook} WHERE status = %s OR status = %s", 'Issue', 'Submitted' );
	// Execute the query.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$booklist = $wpdb->get_results( $query );
	if ( ! empty( $booklist ) ) {
		return $booklist;
	}
	return array(); // Return an empty array if no records found.
}
/**
 * Get recent fee payment records for dashboard based on user role.
 *
 * @since 1.0.0
 * @param int $id User ID.
 * @param string $user_role Role: student|parent|teacher|supportstaff.
 * @return array Fee payment records.
 */
function mjschool_user_wise_fees_payment_for_dashboard( $id, $user_role ) {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'mjschool_fees_payment';
	$obj_feespayment = new mjschool_feespayment();
	$result          = array();
	$user_id         = absint( $id );
	$user_role       = sanitize_text_field( $user_role );
	// For Student.
	if ( $user_role === 'student' ) {
		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE student_id = %d ORDER BY fees_id DESC LIMIT 5", $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
	}
	// For Parent.
	elseif ( $user_role === 'parent' ) {
		$user_meta = get_user_meta( $user_id, 'child', true );
		if ( ! empty( $user_meta ) && is_array( $user_meta ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $user_meta ), '%d' ) );
			$query        = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE student_id IN ($placeholders) ORDER BY fees_id DESC LIMIT 5", $user_meta );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $query );
		}
	}
	// For Teacher.
	elseif ( $user_role === 'teacher' ) {
		$class_id = get_user_meta( get_current_user_id(), 'class_name', true );
		if ( ! empty( $class_id ) && is_array( $class_id ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $class_id ), '%d' ) );
			$query        = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id IN ($placeholders) ORDER BY fees_id DESC LIMIT 5", $class_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $query );
		}
	}
	// For Supportstaff.
	elseif ( $user_role === 'supportstaff' ) {
		$result = $obj_feespayment->mjschool_get_five_fees();
	}
	return $result;
}
/**
 * Get teacher count for dashboard card based on user role and access rights.
 *
 * @since 1.0.0
 * @param int $user_id User ID.
 * @param string $role User role.
 * @return array List of teacher users.
 */
function mjschool_teacher_count_for_dashboard_card( $user_id, $role ) {
	$page          = 'teacher';
	$user_access   = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data      = $user_access['own_data'];
	$user_id       = absint( $user_id );
	$role          = sanitize_text_field( $role );
	$teacher_count = array();
	if ( $own_data === '1' ) {
		if ( $role === 'student' ) {
			$class_name    = get_user_meta( $user_id, 'class_name', true );
			$teacher_count = mjschool_get_teacher_by_class_id( $class_name );
		} elseif ( $role === 'parent' ) {
			$teacherdata_data = array();
			$child            = get_user_meta( $user_id, 'child', true );
			if ( ! empty( $child ) ) {
				foreach ( $child as $c_id ) {
					$class_id          = get_user_meta( $c_id, 'class_name', true );
					$teacherdata_data1 = mjschool_get_teacher_by_class_id( $class_id );
					if ( ! empty( $teacherdata_data1 ) ) {
						$teacher_data[] = $teacherdata_data1;
					}
				}
			}
			if ( ! empty( $teacher_data ) ) {
				$mergedArray   = array_merge( ...$teacher_data );
				$teacher_count = array_unique( $mergedArray, SORT_REGULAR );
			} else {
				$teacher_count = '';
			}
		} elseif ( $role === 'teacher' ) {
			$teacher_count[] = get_userdata( $user_id );
		} elseif ( $role === 'supportstaff' ) {
			
			$teacherdata_created_by = get_users(
				array(
					'role' => 'teacher',
					'meta_query' => array(
						array(
							'key' => 'created_by',
							'value' => $user_id,
							'compare' => '='
						)
					)
				)
			);

			$teacher_count = $teacherdata_created_by;
		} else {
			$teacher_count = mjschool_get_users_data( 'teacher' );
		}
	} else {
		$teacher_count = mjschool_get_users_data( 'teacher' );
	}
	return $teacher_count;
}
/**
 * Get class, section and subject name in formatted string.
 *
 * Format examples:
 *  - Class => Section => <b>Subject</b>
 *  - Class => Section
 *  - Class => <b>Subject</b>
 *
 * @since 1.0.0
 * @param int $class_id Class ID.
 * @param int $section_id Section ID.
 * @param int $subject_id Subject ID.
 * @return string Formatted name string.
 */
function mjschool_get_class_section_subject( $class_id, $section_id, $subject_id ) {
	$subject_name        = '';
	$class_sections_name = '';
	$class_name          = mjschool_get_class_name( $class_id );
	$class_id            = absint( $class_id );
	$section_id          = absint( $section_id );
	$subject_id          = absint( $subject_id );
	if ( ! empty( $section_id ) ) {
		$class_sections_name = mjschool_get_class_sections_name( $section_id );
	}
	if ( ! empty( $subject_id ) ) {
		$subject_name = mjschool_get_single_subject_name( $subject_id );
	}
	if ( ! empty( $class_id ) && ! empty( $section_id ) && ! empty( $subject_id ) ) {
		$name = $class_name . '=>' . $class_sections_name . '=><b>' . $subject_name . '</b>';
	} elseif ( ! empty( $class_id ) && ! empty( $section_id ) ) {
		$name = $class_name . '=>' . $class_sections_name;
	} elseif ( ! empty( $class_id ) && ! empty( $subject_id ) ) {
		$name = $class_name . '=><b>' . $subject_name . '</b>';
	} else {
		$name = $class_name;
	}
	return $name;
}
/**
 * Get monthly attendance records for a teacher.
 *
 * Fetches attendance between the first and last day of current month.
 *
 * @since 1.0.0
 * @param int $id Teacher user ID.
 * @return array Attendance records.
 */
function mjschool_monthly_attendence_teacher( $id ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_attendence';
	// Get the first and last day of the current month.
	$sdate   = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$edate   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	$user_id = absint( $id );
	// Use prepare to securely query the database.
	$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE attendence_date BETWEEN %s AND %s AND user_id = %d ORDER BY attendence_date DESC", $sdate, $edate, $user_id );
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Get attendance report for teacher between date range.
 *
 * Fetches student attendance for classes assigned to the teacher.
 *
 * @since 1.0.0
 * @param string $start_date Start date (Y-m-d).
 * @param string $end_date End date (Y-m-d).
 * @param int $teacher_id Teacher user ID.
 * @return array Attendance report.
 */
function mjschool_view_attendance_report_for_start_date_enddate_for_teacher( $start_date, $end_date, $teacher_id ) {
	global $wpdb;
	$tbl_name     = $wpdb->prefix . 'mjschool_sub_attendance';
	$classes      = mjschool_get_class_by_teacher_id( $teacher_id );
	$start_date   = sanitize_text_field( $start_date );
	$end_date     = sanitize_text_field( $end_date );
	$teacher_id   = absint( $teacher_id );
	$unique_array = array();
	foreach ( $classes as $class ) {
		$class_id = $class->class_id;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_2     = $wpdb->get_results( "SELECT * FROM {$tbl_name} where role_name = 'student' and class_id = '$class_id' and attendance_date between '$start_date' and '$end_date'" );
		$unique_array = array_merge( $unique_array, $report_2 );
	}
	$result = array_unique( $unique_array, SORT_REGULAR );
	return $result;
}
/**
 * Get section list by class ID.
 *
 * @since 1.0.0
 * @param int $id Class ID.
 * @return array Section records.
 */
function mjschool_get_section_by_class_id( $id ) {
	global $wpdb;
	$table_name         = $wpdb->prefix . 'mjschool_class_section';
	$class_id           = absint( $id );
	$prepared_statement = $wpdb->prepare( "SELECT section_name FROM {$table_name} WHERE class_id = %d", $class_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $prepared_statement );
	return $result;
}
/**
 * Get formatted class => section name.
 *
 * @since 1.0.0
 * @param int $class_id Class ID.
 * @param int $section_id Section ID.
 * @return string Class and section name.
 */
function mjschool_get_class_section_name_wise( $class_id, $section_id ) {
	$class_sections_name = '';
	$class_id            = absint( $class_id );
	$section_id          = absint( $section_id );
	$class_name          = mjschool_get_class_name( $class_id );
	if ( ! empty( $section_id ) ) {
		$class_sections_name = mjschool_get_class_sections_name( $section_id );
	}
	if ( ! empty( $class_id ) && ! empty( $section_id ) ) {
		$name = $class_name . '=>' . $class_sections_name;
	} else {
		$name = $class_name;
	}
	return $name;
}
/**
 * Get section ID from class ID and section name.
 *
 * @since 1.0.0
 * @param string $section_name Section name.
 * @param int $id Class ID.
 * @return array Section ID(s).
 */
function mjschool_get_section_id_by_section_name( $section_name, $id ) {
	global $wpdb;
	$table_name   = $wpdb->prefix . 'mjschool_class_section';
	$class_id     = absint( $id );
	$section_name = sanitize_text_field( $section_name );
	// Use prepare to securely include dynamic values in the query.
	$query = $wpdb->prepare( "SELECT id FROM {$table_name} WHERE class_id = %d AND section_name = %s", $class_id, $section_name );
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Get subject ID from class, section, and subject name.
 *
 * @since 1.0.0
 * @param string $subject_name Subject name.
 * @param int $id Class ID.
 * @param int $sid Section ID.
 * @return int|null Subject ID or null.
 */
function mjschool_get_subject_id_by_subject_name( $subject_name, $id, $sid ) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$class_id   = absint( $id );
	$section_id = absint( $sid );
	$subject_name = sanitize_text_field( $subject_name );
	if ( ! empty( $section_id ) ) {
		// Use prepare for the query when section_id is provided.
		$query = $wpdb->prepare( "SELECT subid FROM {$table_name} WHERE sub_name = %s AND class_id = %d AND section_id = %d", $subject_name, $class_id, $section_id );
	} else {
		// Use prepare for the query when section_id is not provided.
		$query = $wpdb->prepare( "SELECT subid FROM {$table_name} WHERE sub_name = %s AND class_id = %d", $subject_name, $class_id );
	}
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$retrieve_subject = $wpdb->get_var( $query );
	return $retrieve_subject;
}
/**
 * Get leave data based on filters (student, status, date range).
 *
 * Supports multiple date-range types (today, week, month, custom period).
 *
 * @since 1.0.0
 * @param mixed $Student_id Student ID or 'all_student'.
 * @param string $status Leave status or 'all_status'.
 * @param string $date_type Date filter type.
 * @param string $start_date Start date.
 * @param string $end_date End date.
 * @return array Filtered leave data.
 */
function mjschool_get_leave_data_filter_wise( $Student_id, $status, $date_type, $start_date, $end_date ) {
	global $wpdb;
	
	// Sanitize all inputs immediately.
	$Student_id = sanitize_text_field( $Student_id );
	$status     = sanitize_text_field( $status );
	$date_type  = sanitize_text_field( $date_type );
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	$user_id    = get_current_user_id();
	$role       = mjschool_get_user_role( get_current_user_id() );
	$tbl_name   = $wpdb->prefix . 'mjschool_leave';
	$school_obj = new MJSchool_Management( get_current_user_id() );
	
	if ( $date_type === 'period' ) {
		$start_date = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : '';
		$end_date   = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : '';
	} elseif ( $date_type === 'today' || $date_type === 'this_week' || $date_type === 'last_week' || $date_type === 'this_month' || $date_type === 'last_month' || $date_type === 'last_3_month' || $date_type === 'last_6_month' || $date_type === 'last_12_month' || $date_type === 'this_year' || $date_type === 'last_year' ) {
		$result     = mjschool_all_date_type_value( $date_type );
		$response   = json_decode( $result );
		$start_date = sanitize_text_field( $response[0] );
		$end_date   = sanitize_text_field( $response[1] );
	}
	
	$leave_data = array();
	
	if ( $role === 'teacher' ) {
		$user_id     = get_current_user_id();
		$class_id    = absint( get_user_meta( $user_id, 'class_name', true ) );
		$studentdata = $school_obj->mjschool_get_teacher_student_list( $class_id );
		if ( $Student_id === 'all_student' && $status === 'all_status' ) {
			foreach ( $studentdata as $student ) {
				$student_id = absint( $student->ID );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$leave_data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE student_id = %d AND start_date between %s and %s ORDER BY start_date DESC", $student_id, $start_date, $end_date ) );
			}
			if ( ! empty( $leave_data ) ) {
				$mergedArray = array_merge( ...$leave_data );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			} else {
				$result = array();
			}
		} elseif ( $Student_id === 'all_student' && $status != 'all_status' ) {
			foreach ( $studentdata as $student ) {
				$student_id = absint( $student->ID );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$leave_data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE student_id = %d AND status= %s AND start_date between %s and %s ORDER BY start_date DESC", $student_id, $status, $start_date, $end_date ) );
			}
			if ( ! empty( $leave_data ) ) {
				$mergedArray = array_merge( ...$leave_data );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			} else {
				$result = array();
			}
		} elseif ( $status === 'all_status' && $Student_id !== 'all_student' ) {
			$Student_id = absint( $Student_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE student_id = %d AND start_date between %s and %s ORDER BY start_date DESC", $Student_id, $start_date, $end_date ) );
		} else {
			$Student_id = absint( $Student_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE status = %s AND student_id = %d AND start_date between %s and %s ORDER BY start_date DESC", $status, $Student_id, $start_date, $end_date ) );
		}
	} elseif ( $Student_id === 'all_student' && $status === 'all_status' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( "SELECT * FROM {$tbl_name} ORDER BY start_date DESC" );
	} elseif ( $Student_id === 'all_student' && $status != 'all_status' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE status= %s AND start_date between %s and %s ORDER BY start_date DESC", $status, $start_date, $end_date ) );
	} elseif ( $status === 'all_status' && $Student_id !== 'all_student' ) {
		$Student_id = absint( $Student_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE student_id = %d AND start_date between %s and %s ORDER BY start_date DESC", $Student_id, $start_date, $end_date ) );
	} else {
		$Student_id = absint( $Student_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tbl_name} WHERE status = %s AND student_id = %d AND start_date between %s and %s ORDER BY start_date DESC", $status, $Student_id, $start_date, $end_date ) );
	}
	return $result;
}
/**
 * Get parent full name by user ID.
 *
 * @since 1.0.0
 * @param int $user_id Parent user ID.
 * @return string Full parent name or 'N/A'.
 */
function mjschool_get_parent_name_by_id( $user_id ) {
	$user_id   = absint( $user_id );
	$user_info = get_userdata( $user_id );
	if ( $user_info ) {
		return sanitize_text_field( $user_info->first_name ) . ' ' . sanitize_text_field( $user_info->middle_name ) . ' ' . sanitize_text_field( $user_info->last_name );
	} else {
		return 'N/A';
	}
}
// add_action( 'init','mjschool_attendance_migratation_for_new_table' );
/**
 * Migrate student attendance data to new sub-attendance table.
 *
 * Runs only once based on migration status option.
 *
 * @since 1.0.0
 * @return void
 */
function mjschool_attendance_migratation_for_new_table() {
	set_time_limit( 0 );
	$mjschool_attendence_migration_status = get_option( 'mjschool_attendence_migration_status' );
	if ( $mjschool_attendence_migration_status === 'no' || $mjschool_attendence_migration_status === false ) {
		global $wpdb;
		$attendence              = $wpdb->prefix . 'mjschool_attendence';
		$mjschool_sub_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
		$table_name              = $wpdb->prefix . 'mjschool_attendence';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$attendence_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE role_name=%s", 'student' ) );
		if ( ! empty( $attendence_data ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$migration_success = $wpdb->query( $wpdb->prepare( "INSERT INTO $mjschool_sub_attendance (user_id, class_id, attend_by,attendance_date,status,role_name,comment,attendence_type,categories) SELECT user_id, class_id, attend_by,attendence_date,status,role_name,comment,attendence_type,'class' FROM $attendence where role_name=%s", 'student' ) );
			if ( $migration_success ) {
				update_option( 'mjschool_attendence_migration_status', 'yes' );
			}
		}
	}
}
/**
 * Get class record by class ID.
 *
 * @since 1.0.0
 * @param int $class_id Class ID.
 * @return object|null Class record.
 */
function mjschool_get_class_data_by_class_id( $class_id ) {
	global $wpdb;
	$table_name         = $wpdb->prefix . 'mjschool_class';
	$class_id           = absint( $class_id );
	$prepared_statement = $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_row( $prepared_statement );
	return $result;
}
/**
 * Send homework email with attachment using template design.
 *
 * @since 1.0.0
 * @param string $email Recipient email.
 * @param string $subject Email subject.
 * @param string $message Email body content.
 * @param array|string $attechment Attachment file(s).
 * @return void
 */
function mjschool_send_mail_for_homework( $email, $subject, $message, $attechment ) {
	$email   = sanitize_email( $email );
	$subject = sanitize_text_field( $subject );
	$message = wp_kses_post( $message );
	
	$from      = sanitize_text_field( get_option( 'mjschool_name' ) );
	$headers   = "MIME-Version: 1.0\r\n";
	$headers  .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers  .= 'From: ' . $from . ' <noreplay@gmail.com>' . "\r\n";
	// MAIL CONTEMNT WITH TEMPLATE DESIGN.
	$email_template = mjschool_get_mail_content_with_template_design( $message );
	if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
		wp_mail( $email, $subject, $email_template, $headers, $attechment );
	}
}
/**
 * Retrieves bed data based on user access rights.
 *
 * Returns beds assigned to the user depending on role (student, parent, or staff/teacher).
 *
 * @param int    $user_id   The user ID.
 * @param int    $hostel_id The hostel ID.
 * @param string $user_role The role of the user (student, parent, teacher, etc.).
 *
 * @return array List of bed objects accessible by the user.
 * @since 1.0.0
 */
function mjschool_get_bed_data_user_access_right_wise( $user_id, $hostel_id, $user_role ) {
	global $wpdb;
	$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
	
	$user_id    = absint( $user_id );
	$hostel_id  = absint( $hostel_id );
	$user_role  = sanitize_text_field( $user_role );
	$result     = array();
	
	// GET DATA FOR STUDENT.
	if ( $user_role === 'student' ) {
		$assign_bed = mjschool_student_assign_bed_data_by_student_and_hostel_id( $user_id, $hostel_id );
		if ( ! empty( $assign_bed ) && isset( $assign_bed->bed_id ) ) {
			$bed_id = absint( $assign_bed->bed_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_beds where id=%d", $bed_id ) );
		}
	}
	// GET DATA FOR PARENT.
	elseif ( $user_role === 'parent' ) {
		$child_id = get_user_meta( $user_id, 'child', true );
		$data     = array();
		if ( ! empty( $child_id ) && is_array( $child_id ) ) {
			foreach ( $child_id as $id ) {
				$id         = absint( $id );
				$assign_bed = mjschool_student_assign_bed_data_by_student_and_hostel_id( $id, $hostel_id );
				if ( ! empty( $assign_bed ) && isset( $assign_bed->bed_id ) ) {
					$bed_id = absint( $assign_bed->bed_id );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_beds where id=%d", $bed_id ) );
				}
			}
			if ( ! empty( $data ) ) {
				$mergedArray = array_merge( ...$data );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			}
		}
	} else {
		$obj_hostel = new smgt_hostel();
		$result     = $obj_hostel->mjschool_get_bed_by_hostel_id( $hostel_id );
	}
	return $result;
}
/**
 * Retrieves room data based on user access rights.
 *
 * Returns rooms assigned to the user depending on role (student, parent, teacher, support staff).
 *
 * @param int    $user_id   The user ID.
 * @param int    $hostel_id The hostel ID.
 * @param string $user_role The role of the user.
 *
 * @return array List of room objects accessible by the user.
 * @since 1.0.0
 */
function mjschool_get_room_data_user_access_right_wise( $user_id, $hostel_id, $user_role ) {
	global $wpdb;
	$table_mjschool_room = $wpdb->prefix . 'mjschool_room';
	
	$user_id   = absint( $user_id );
	$hostel_id = absint( $hostel_id );
	$user_role = sanitize_text_field( $user_role );
	$result    = array();
	
	// GET DATA FOR STUDENT.
	if ( $user_role === 'student' ) {
		$assign_bed = mjschool_student_assign_bed_data_by_student_and_hostel_id( $user_id, $hostel_id );
		if ( ! empty( $assign_bed ) && isset( $assign_bed->room_id ) ) {
			$room_id = absint( $assign_bed->room_id );
			$query   = $wpdb->prepare( "SELECT * FROM {$table_mjschool_room} WHERE id = %d", $room_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $query );
		}
	}
	// GET DATA FOR PARENT.
	elseif ( $user_role === 'parent' ) {
		$child_id = get_user_meta( $user_id, 'child', true );
		$data     = array();
		if ( ! empty( $child_id ) && is_array( $child_id ) ) {
			foreach ( $child_id as $id ) {
				$id         = absint( $id );
				$assign_bed = mjschool_student_assign_bed_data_by_student_and_hostel_id( $id, $hostel_id );
				if ( ! empty( $assign_bed ) && isset( $assign_bed->room_id ) ) {
					$room_id = absint( $assign_bed->room_id );
					$query   = $wpdb->prepare( "SELECT * FROM {$table_mjschool_room} WHERE id = %d", $room_id );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$data[] = $wpdb->get_results( $query );
				}
			}
			if ( ! empty( $data ) ) {
				$mergedArray = array_merge( ...$data );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			}
		}
	}
	// GET DATA FOR STAFF OR TEACHER.
	elseif ( $user_role === 'supportstaff' || $user_role === 'teacher' ) {
		$query = $wpdb->prepare( "SELECT * FROM {$table_mjschool_room} WHERE created_by = %d AND hostel_id = %d", $user_id, $hostel_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
	}
	// Default case.
	else {
		$obj_hostel = new mjschool_Homework();
		$result     = $obj_hostel->mjschool_get_room_by_hostel_id( $hostel_id );
	}
	return $result;
}
/**
 * Retrieves hostel data based on user access rights.
 *
 * Returns hostels related to the user depending on role (student, parent, teacher, support staff).
 *
 * @param int    $user_id   The user ID.
 * @param string $user_role The role of the user.
 *
 * @return array List of hostel objects accessible by the user.
 * @since 1.0.0
 */
function mjschool_get_hostel_data_user_access_right_wise( $user_id, $user_role ) {
	global $wpdb;
	$table_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
	
	$user_id   = absint( $user_id );
	$user_role = sanitize_text_field( $user_role );
	$result    = array();
	
	// GET DATA FOR STUDENT.
	if ( $user_role === 'student' ) {
		$assign_bed = mjschool_student_assign_bed_data_by_student_id( $user_id );
		if ( ! empty( $assign_bed ) && isset( $assign_bed->hostel_id ) ) {
			$hostel_id = absint( $assign_bed->hostel_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_hostel where id=%d", $hostel_id ) );
		}
	}
	// GET DATA FOR PARENT.
	elseif ( $user_role === 'parent' ) {
		$child_id = get_user_meta( $user_id, 'child', true );
		$data     = array();
		if ( ! empty( $child_id ) && is_array( $child_id ) ) {
			foreach ( $child_id as $id ) {
				$id         = absint( $id );
				$assign_bed = mjschool_student_assign_bed_data_by_student_id( $id );
				if ( ! empty( $assign_bed ) && isset( $assign_bed->hostel_id ) ) {
					$hostel_id = absint( $assign_bed->hostel_id );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$data[] = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_hostel where id=%d", $hostel_id ) );
				}
			}
			if ( ! empty( $data ) ) {
				$mergedArray = array_merge( ...$data );
				$result      = array_unique( $mergedArray, SORT_REGULAR );
			}
		}
	}
	// GET DATA FOR STAFF OR TEACHER.
	elseif ( $user_role === 'supportstaff' || $user_role === 'teacher' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_hostel where created_by=%d", $user_id ) );
	} else {
		$tablename = 'mjschool_beds';
		$result    = mjschool_get_all_data( $tablename );
	}
	return $result;
}
/**
 * Deletes an inbox message by ID.
 *
 * @param int $id The ID of the message to delete.
 *
 * @return int|false Number of rows affected or false on failure.
 * @since 1.0.0
 */
function mjschool_delete_inbox_message( $id ) {
	global $wpdb;
	$message_id       = absint( $id );
	$tbl_name_message = $wpdb->prefix . 'mjschool_message';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_name_message} where message_id=%d", $message_id ) );
	return $result;
}
/**
 * Retrieves access rights for a given page based on current user role.
 *
 * @param string $page The page slug to check access rights for.
 *
 * @return array|void Access rights array for the page, or nothing if page is empty or access not found.
 * @since 1.0.0
 */
function mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page ) {
	$page       = sanitize_text_field( $page );
	$school_obj = new MJSchool_Management( get_current_user_id() );
	$role       = $school_obj->role;
	if ( ! empty( $page ) ) {
		if ( $role === 'student' ) {
			$menu = get_option( 'mjschool_access_right_student' );
		} elseif ( $role === 'parent' ) {
			$menu = get_option( 'mjschool_access_right_parent' );
		} elseif ( $role === 'supportstaff' ) {
			$menu = get_option( 'mjschool_access_right_supportstaff' );
		} elseif ( $role === 'teacher' ) {
			$menu = get_option( 'mjschool_access_right_teacher' );
		} else {
			$menu = 0;
		}
		if ( ! empty( $menu ) ) {
			foreach ( $menu as $key1 => $value1 ) {
				foreach ( $value1 as $key => $value ) {
					if ( $page === $value['page_link'] ) {
						return $value;
					}
				}
			}
		}
	}
}
/**
 * Retrieves students for dashboard card based on user role and access rights.
 *
 * @param int    $user_id   Current user ID.
 * @param string $user_role Current user role.
 *
 * @return array List of student objects.
 * @since 1.0.0
 */
function mjschool_student_count_for_dashboard_card( $user_id, $user_role ) {
	$user_id     = absint( $user_id );
	$user_role   = sanitize_text_field( $user_role );
	$page        = 'student';
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$school_obj  = new MJSchool_Management( get_current_user_id() );
	$own_data    = isset( $user_access['own_data'] ) ? $user_access['own_data'] : '0';
	$studentdata = array();
	if ( $own_data === '1' ) {
		if ( $user_role === 'student' ) {
			$studentdata[] = get_userdata( $user_id );
		} elseif ( $user_role === 'parent' ) {
			$studentdata = $school_obj->child_list;
		} elseif ( $user_role === 'teacher' ) {
			$class_id    = absint( get_user_meta( $user_id, 'class_name', true ) );
			$studentdata = $school_obj->mjschool_get_teacher_student_list( $class_id );
		} elseif ( $user_role === 'supportstaff' ) {
			
			$studentdata = get_users(
				array(
					'role' => 'student',
					'meta_query' => array(
						array(
							'key' => 'created_by',
							'value' => $user_id,
							'compare' => '='
						)
					)
				)
			);

		}
	} else {
		$studentdata = mjschool_get_users_data( 'student' );
	}
	return $studentdata;
}
/**
 * Retrieves parents for dashboard card based on user role and access rights.
 *
 * @param int    $user_id   Current user ID.
 * @param string $user_role Current user role.
 *
 * @return array List of parent objects.
 * @since 1.0.0
 */
function mjschool_parent_count_for_dashboard_card( $user_id, $user_role ) {
	$user_id     = absint( $user_id );
	$user_role   = sanitize_text_field( $user_role );
	$parentdata  = array();
	$page        = 'parent';
	$school_obj  = new MJSchool_Management( get_current_user_id() );
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data    = $user_access['own_data'];
	$parentdata  = array();
	if ( $own_data === '1' ) {
		if ( $user_role === 'student' ) {
			$parentdata1 = $school_obj->parent_list;
			if ( ! empty( $parentdata1 ) ) {
				foreach ( $parentdata1 as $pid ) {
					$parentdata[] = get_userdata( $pid );
				}
			}
		} elseif ( $user_role === 'parent' ) {
			$parentdata[] = get_userdata( $user_id );
		} elseif ( $user_role === 'teacher' ) {
			$parent = mjschool_parent_own_data_for_teacher();
			if ( ! empty( $parent ) ) {
				foreach ( $parent as $pid ) {
					$parentdata[] = get_userdata( $pid );
				}
			} else {
				$parentdata = '';
			}
		} elseif ( $user_role === 'supportstaff' ) {
			
			$parentdata = get_users(
				array(
					'role' => 'parent',
					'meta_query' => array(
						array(
							'key' => 'created_by',
							'value' => $user_id,
							'compare' => '='
						)
					)
				)
			);

		}
	} else {
		$parentdata = mjschool_get_users_data( 'parent' );
	}
	return $parentdata;
}
/**
 * Retrieves parent data accessible to a teacher based on their students.
 *
 * @return array|string List of parent IDs or empty string if none.
 * @since 1.0.0
 */
function mjschool_parent_own_data_for_teacher() {
	$user_id     = get_current_user_id();
	$school_obj  = new MJSchool_Management( get_current_user_id() );
	$class_id    = absint( get_user_meta( $user_id, 'class_name', true ) );
	$studentdata = $school_obj->mjschool_get_teacher_student_list( $class_id );
	$user_meta   = array();
	
	if ( ! empty( $studentdata ) ) {
		foreach ( $studentdata as $student ) {
			$data = get_user_meta( $student->ID, 'parent_id', true );
			if ( ! empty( $data ) ) {
				$user_meta[] = $data;
			}
		}
	}
	
	if ( ! empty( $user_meta ) ) {
		$mergedArray = array_merge( ...$user_meta );
		$result      = array_unique( $mergedArray, SORT_REGULAR );
	} else {
		$result = array();
	}
	return $result;
}
/**
 * Retrieves exam list for dashboard based on user role and access rights.
 *
 * Returns the first five upcoming exams the user can access.
 *
 * @param string $user_role User role (student, parent, teacher, supportstaff).
 *
 * @return array List of exam objects (first 5 items).
 * @since 1.0.0
 */
function mjschool_exam_list_data_with_access_for_dashboard( $user_role ) {
	$user_role       = sanitize_text_field( $user_role );
	$page            = 'exam';
	$obj_exam        = new mjschool_exam();
	$user_access     = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data        = isset( $user_access['own_data'] ) ? $user_access['own_data'] : '0';
	$user_id         = get_current_user_id();
	$retrieve_class  = array();
	
	if ( $own_data === '1' ) {
		if ( $user_role === 'student' ) {
			$class_id   = absint( get_user_meta( get_current_user_id(), 'class_name', true ) );
			$section_id = absint( get_user_meta( get_current_user_id(), 'class_section', true ) );
			if ( isset( $class_id ) && $section_id === 0 ) {
				$retrieve_class = $obj_exam->mjschool_get_all_exam_by_class_id( $class_id );
			} else {
				$retrieve_class = mjschool_get_all_exam_by_class_id_and_section_id_array( $class_id, $section_id );
			}
		} elseif ( $user_role === 'parent' ) {
			$user_meta = get_user_meta( $user_id, 'child', true );
			if ( ! empty( $user_meta ) && is_array( $user_meta ) ) {
				$result = array();
				foreach ( $user_meta as $student_id ) {
					$student_id = absint( $student_id );
					$result[]   = mjschool_get_exam_data_for_parent( $student_id );
				}
				if ( ! empty( $result ) ) {
					$mergedArray    = array_merge( ...$result );
					$retrieve_class = array_unique( $mergedArray, SORT_REGULAR );
				}
			}
		} elseif ( $user_role === 'teacher' ) {
			$class_id       = absint( get_user_meta( get_current_user_id(), 'class_name', true ) );
			$retrieve_class = $obj_exam->mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id );
		} elseif ( $user_role === 'supportstaff' ) {
			$retrieve_class = $obj_exam->mjschool_get_all_exam_created_by( $user_id );
		}
	} else {
		$tablename      = 'mjschool_exam';
		$retrieve_class = mjschool_get_all_data( $tablename );
	}
	
	if ( ! empty( $retrieve_class ) ) {
		$firstFive = array_slice( $retrieve_class, 0, 5 );
	} else {
		$firstFive = array();
	}
	return $firstFive;
}
/**
 * Generates attendance report for teacher or staff based on access rights.
 *
 * Returns attendance counts (Present/Absent) per class for the user.
 *
 * @param string $user_role Role of the current user.
 *
 * @return array Attendance summary per class.
 * @since 1.0.0
 */
function mjschool_attendance_report_for_teacher_and_staff( $user_role ) {
	global $wpdb;
	$table_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
	$table_class      = $wpdb->prefix . 'mjschool_class';
	
	$user_role   = sanitize_text_field( $user_role );
	$page        = 'attendance';
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data    = isset( $user_access['own_data'] ) ? $user_access['own_data'] : '0';
	$user_id     = get_current_user_id();
	$report_1    = array();
	
	if ( $own_data === '1' ) {
		if ( $user_role === 'teacher' ) {
			// Assuming $wpdb and $table_attendance, $table_class are defined elsewhere.
			$class  = get_user_meta( $user_id, 'class_name', true );
			$report = array();
			$result = array();
			if ( ! empty( $class ) && is_array( $class ) ) {
				foreach ( $class as $class_id ) {
					$class_id = absint( $class_id );
					$query    = $wpdb->prepare( "SELECT at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent FROM {$table_attendance} as at JOIN $table_class as cl ON at.class_id = cl.class_id WHERE at.attendance_date > DATE_SUB(NOW(), INTERVAL 1 DAY) AND at.class_id = %d GROUP BY at.class_id", $class_id );
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$result[] = $wpdb->get_results( $query );
				}
				if ( ! empty( $result ) ) {
					$mergedArray = array_merge( ...$result );
					$report_1    = array_unique( $mergedArray, SORT_REGULAR );
				}
			}
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$report_1 = $wpdb->get_results( $wpdb->prepare( "SELECT  at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from {$table_attendance} as at,$table_class as cl where at.attendance_date >  DATE_SUB(NOW(), INTERVAL 1 DAY) AND attend_by = %d GROUP BY at.class_id", $user_id ) );
		}
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_1 = $wpdb->get_results( "SELECT  at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from {$table_attendance} as at,$table_class as cl where at.attendance_date >  DATE_SUB(NOW(), INTERVAL 1 DAY) AND at.class_id = cl.class_id GROUP BY at.class_id" );
	}
	return $report_1;
}
/**
 * Retrieves notice list for dashboard based on user role and access rights.
 *
 * @param string $user_role Current user role.
 *
 * @return array List of notice objects.
 * @since 1.0.0
 */
function mjschool_notice_list_with_user_access_right( $user_role ) {
	$user_role   = sanitize_text_field( $user_role );
	$page        = 'notice';
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data    = isset( $user_access['own_data'] ) ? $user_access['own_data'] : '0';
	$user_id     = get_current_user_id();
	if ( $own_data === '1' ) {
		if ( $user_role === 'student' ) {
			$class_name    = absint( get_user_meta( get_current_user_id(), 'class_name', true ) );
			$class_section = absint( get_user_meta( get_current_user_id(), 'class_section', true ) );
			$notice_list   = mjschool_student_notice_dashboard_with_access_right( $class_name, $class_section );
		} elseif ( $user_role === 'parent' ) {
			$notice_list = mjschool_parent_notice_dashboard_with_access_right();
		} elseif ( $user_role === 'teacher' ) {
			$class_name  = absint( get_user_meta( get_current_user_id(), 'class_name', true ) );
			$notice_list = mjschool_teacher_notice_board( $class_name );
		} else {
			$notice_list = mjschool_supportstaff_notice_dashbord();
		}
	} else {
		$args['post_type']      = 'notice';
		$args['posts_per_page'] = 4;
		$args['post_status']    = 'public';
		$q                      = new WP_Query();
		$notice_list            = $q->query( $args );
	}
	return $notice_list;
}
/**
 * Retrieves upcoming homework data for dashboard.
 *
 * Returns latest 5 homework entries with submission date >= current date.
 *
 * @return array List of homework objects.
 * @since 1.0.0
 */
function mjschool_get_homework_data_for_dashboard() {
	global $wpdb;
	$current_date = date( 'Y-m-d' );
	$table_name   = $wpdb->prefix . 'mjschool_homework';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE submition_date >= %s ORDER BY homework_id DESC limit 5", $current_date ) );
	return $result;
}
/**
 * Retrieves a single homework entry by homework ID.
 *
 * @param int $tid Homework ID.
 *
 * @return object|null Homework object if found.
 * @since 1.0.0
 */
function mjschool_get_homework_by_id( $tid ) {
	global $wpdb;
	$homework_id = absint( $tid );
	$table_name  = $wpdb->prefix . 'mjschool_homework';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE homework_id =%d", $homework_id ) );
}
/**
 * Retrieves homework data for frontend dashboard based on user role and access rights.
 *
 * Returns first 5 homework items the user can access.
 *
 * @return array List of homework objects.
 * @since 1.0.0
 */
function mjschool_get_homework_data_for_frontend_dashboard() {
	$page        = 'homework';
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$own_data    = isset( $user_access['own_data'] ) ? $user_access['own_data'] : '0';
	$homewrk     = new mjschool_Homework();
	$user_id     = get_current_user_id();
	$role_name   = mjschool_get_user_role( $user_id );
	$result      = array();
	
	if ( $own_data === '1' ) {
		if ( $role_name === 'student' ) {
			$result = $homewrk->mjschool_student_view_detail_for_dashboard();
		} elseif ( $role_name === 'parent' ) {
			$child_ids = mjschool_get_parents_child_id( $user_id );
			if ( ! empty( $child_ids ) && is_array( $child_ids ) ) {
				$homework_data = implode( ',', array_map( 'absint', $child_ids ) );
				$result        = $homewrk->mjschool_parent_view_detail_for_dashboard( $homework_data );
			}
		} elseif ( $role_name === 'teacher' ) {
			$result = $homewrk->mjschool_get_all_own_upcoming_homework_list_for_teacher();
		} else {
			$result = $homewrk->mjschool_get_all_own_upcoming_homeworklist();
		}
	} else {
		$result = $homewrk->mjschool_get_all_upcoming_homework();
	}
	
	if ( ! empty( $result ) ) {
		$firstFive = array_slice( $result, 0, 5 );
	} else {
		$firstFive = array();
	}
	return $firstFive;
}
/**
 * Retrieves students by class ID and optional section ID.
 *
 * @param int    $class_id   Class ID.
 * @param string $section_id Section ID (optional).
 *
 * @return array List of student objects.
 * @since 1.0.0
 */
function mjschool_get_student_by_class_id_and_section( $class_id, $section_id ) {
	$class_id   = absint( $class_id );
	$section_id = absint( $section_id );

	if ( ! empty( $section_id ) ) {
		$results = get_users(array(
			'meta_key' => 'class_section',
			'meta_value' => $section_id,
			'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ),
			'role' => 'student'
		 ) );
	} else {
		$results = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id ) );
	}

	return $results;
}
/**
 * Retrieves fee records for a given class ID.
 *
 * @param int $cid Class ID.
 *
 * @return array List of fee objects.
 * @since 1.0.0
 */
function mjschool_get_fees_by_class_id( $cid ) {
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	$class_id            = absint( $cid );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_mjschool_fees} where class_id=%d", $class_id ) );
	return $result;
}
/**
 * Retrieves all holiday data.
 *
 * @return array List of holiday objects.
 * @since 1.0.0
 */
function mjschool_get_all_holiday_data() {
	global $wpdb;
	$table_mjschool_fees = $wpdb->prefix . 'mjschool_holiday';
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( "SELECT * FROM {$table_mjschool_fees}" );
	return $result;
}
/**
 * Retrieves a single custom field by name and module.
 *
 * @param string $custom_field_name The custom field label.
 * @param string $module            The module name.
 *
 * @return object|null Custom field object if found.
 * @since 1.0.0
 */
function mjschool_get_single_custom_field_data_by_name( $custom_field_name, $module ) {
	global $wpdb;
	$wpnc_custom_fields = $wpdb->prefix . 'mjschool_custom_field';
	$custom_field_name = sanitize_text_field( $custom_field_name );
	$module            = sanitize_text_field( $module );
	// Use prepare to securely include dynamic values in the query.
	$query = $wpdb->prepare( "SELECT * FROM {$wpnc_custom_fields} WHERE field_label = %s AND form_name = %s", $custom_field_name, $module );
	// Execute the query and return the result.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$single_custom_field_data = $wpdb->get_row( $query );
	return $single_custom_field_data;
}
/**
 * Returns the date format string for sorting based on plugin settings.
 *
 * @return string Date format string (e.g., 'YYYY-MM-DD').
 * @since 1.0.0
 */
function mjschool_return_date_format_for_shorting() {
	$format = sanitize_text_field( get_option( 'mjschool_datepicker_format' ) );
	if ( $format === 'yy-mm-dd' ) {
		$change_formate = 'YYYY-MM-DD';
	} elseif ( $format === 'yy/mm/dd' ) {
		$change_formate = 'YYYY/MM/DD';
	} elseif ( $format === 'dd-mm-yy' ) {
		$change_formate = 'DD-MM-YYYY';
	} elseif ( $format === 'mm/dd/yy' ) {
		$change_formate = 'MM/DD/YYYY';
	} else {
		$change_formate = 'YYYY-MM-DD';
	}
	return $change_formate;
}
/**
 * Retrieves payment data filtered by payment method and date range.
 *
 * @param string $method     Payment method.
 * @param string $start_date Start date (YYYY-MM-DD).
 * @param string $end_date   End date (YYYY-MM-DD).
 *
 * @return array List of payment records.
 * @since 1.0.0
 */
function mjschool_get_payment_paid_data_by_date_method( $method, $start_date, $end_date ) {
	global $wpdb;
	$table_mjschool_fees_history = $wpdb->prefix . 'mjschool_fee_payment_history';
	
	$method     = sanitize_text_field( $method );
	$start_date = sanitize_text_field( $start_date );
	$end_date   = sanitize_text_field( $end_date );
	
	$query  = $wpdb->prepare( "SELECT amount FROM {$table_mjschool_fees_history} WHERE paid_by_date BETWEEN %s AND %s AND payment_method = %s", $start_date, $end_date, $method );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $query );
	return $result;
}
/**
 * Retrieves teacher IDs assigned to a specific subject.
 *
 * @param int $subject_id Subject ID.
 *
 * @return array List of teacher IDs.
 * @since 1.0.0
 */
function mjschool_teacher_by_subject_id( $subject_id ) {
	global $wpdb;
	$teacher_rows = array();
	$subid        = absint( $subject_id );
	if ( isset( $subid ) && $subid > 0 ) {
		$table_mjschool_subject = $wpdb->prefix . 'mjschool_teacher_subject';
		// Use prepare to securely include the subject_id in the query.
		$query = $wpdb->prepare( "SELECT * FROM {$table_mjschool_subject} WHERE subject_id = %d", $subid );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $query );
		foreach ( $result as $tch_result ) {
			$teacher_rows[] = absint( $tch_result->teacher_id );
		}
	}
	return $teacher_rows;
}
/**
 * Sends a notification SMS to a user.
 *
 * @param int    $user_id         User ID to send the notification.
 * @param string $type            Type of the message (info, alert, etc.).
 * @param string $message_content The message content.
 *
 * @return bool Always true.
 * @since 1.0.0
 */
function mjschool_send_mjschool_notification( $user_id, $type, $message_content ) {
	$user_id         = absint( $user_id );
	$type            = sanitize_text_field( $type );
	$message_content = sanitize_textarea_field( $message_content );
	
	$userdata                 = get_userdata( $user_id );
	$mobile_number            = array();
	$mobile_number[]          = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) . sanitize_text_field( $userdata->mobile_number );
	$to_mobile_number         = sanitize_text_field( $userdata->mobile_number );
	$country_code             = '+' . mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
	$current_mjschool_service = get_option( 'mjschool_service' );
	if ( is_plugin_active( 'sms-pack/sms-pack.php' ) ) {
		$args                 = array();
		$args['mobile']       = $mobile_number;
		$args['message_from'] = $type;
		$args['message']      = $message_content;
		if ( $current_mjschool_service === 'telerivet' || $current_mjschool_service === 'MSG91' || $current_mjschool_service === 'bulksmsgateway.in' || $current_mjschool_service === 'textlocal.in' || $current_mjschool_service === 'bulksmsnigeria' || $current_mjschool_service === 'africastalking' || $current_mjschool_service === 'clickatell' ) {
			send_sms( $args );
		}
	} else {
		if ( $current_mjschool_service === 'clickatell' ) {
			mjschool_clickatell_send_mail_function( $to_mobile_number, $message_content, $country_code );
		}
		if ( $current_mjschool_service === 'msg91' ) {
			mjschool_msg91_send_mail_function( $to_mobile_number, $message_content, $country_code );
		}
	}
	return true;
}
/**
 * Sends SMS via Clickatell API.
 *
 * @param string $mobiles      Mobile number(s) to send the SMS.
 * @param string $message      Message content.
 * @param string $country_code Country code prefix for the number.
 *
 * @return void
 * @since 1.0.0
 */
function mjschool_clickatell_send_mail_function( $mobiles, $message, $country_code ) {
	$mobiles      = sanitize_text_field( $mobiles );
	$message      = sanitize_textarea_field( $message );
	$country_code = sanitize_text_field( $country_code );
	$clickatell = get_option( 'mjschool_clickatell_mjschool_service' );
	$api_key    = $clickatell['api_key'];
	if ( strpos( $mobiles, '+' ) !== 0 ) {
		$mobiles = $country_code . $mobiles;
	}
	$postData = json_encode(
		array(
			'messages' => array(
				array(
					'channel' => 'sms',
					'to'      => $mobiles,
					'content' => $message,
				),
			),
		)
	);
	$curl = curl_init();
	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL            => 'https://platform.clickatell.com/v1/message',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => $postData,
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: application/json',
				'Accept: application/json',
				'Authorization: ' . $api_key,
			),
		)
	);
	$response = curl_exec( $curl );
	curl_close( $curl );
}
/**
 * Sends SMS via MSG91 API.
 *
 * @param string $mobile       Mobile number to send the SMS.
 * @param string $message      Message content.
 * @param string $countary_code Country code prefix for the number.
 *
 * @return mixed API response.
 * @since 1.0.0
 */
function mjschool_msg91_send_mail_function( $mobile, $message, $countary_code ) {
	$mobile       = sanitize_text_field( $mobile );
	$message      = sanitize_textarea_field( $message );
	$countary_code = sanitize_text_field( $countary_code );
	$msg91    = get_option( 'mjschool_msg91_mjschool_service' );
	$authKey  = $msg91['mjschool_auth_key']; // Replace with your actual MSG91 Auth Key.
	$senderId = $msg91['msg91_senderID'];     // Replace with your approved Sender ID.
	$route    = '4';               // 4 = Transactional, 1 = Promotional, 2 = OTP.
	$country  = $countary_code;
	$postData = array(
		'sender'  => $senderId,
		'message' => $message,
		'route'   => $route,
		'country' => $country,
		'sms'     => array(
			array(
				'to'      => array( $mobile ),
				'message' => $message,
			),
		),
	);
	$headers = array(
		"authkey: $authKey",
		'Content-Type: application/json',
	);
	$ch = curl_init();
	curl_setopt_array(
		$ch,
		array(
			CURLOPT_URL            => 'https://api.msg91.com/api/v2/sendsms',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => json_encode( $postData ),
			CURLOPT_HTTPHEADER     => $headers,
		)
	);
	// Execute request.
	$response = curl_exec( $ch );
	// Error handling.
	if ( curl_errno( $ch ) ) {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'Curl error: ' . curl_error( $ch );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	curl_close( $ch );
	return $response; // Can be logged or processed further.
}
/**
 * Returns color code based on attendance status.
 *
 * @param string $status Attendance status (Present, Absent, Half Day, Late).
 *
 * @return string Hex color code.
 * @since 1.0.0
 */
function mjschool_attendance_status_color( $status ) {
	$status = sanitize_text_field( $status );
	$color = '';
	if ( $status === 'Present' ) {
		$color = '#28A745';
	} elseif ( $status === 'Absent' ) {
		$color = '#DC3545';
	} elseif ( $status === 'Half Day' ) {
		$color = '#FFC107';
	} elseif ( $status === 'Late' ) {
		$color = '#007BFF';
	}
	return $color;
}
/**
 * Calculates total tax amount for given amount and tax IDs.
 *
 * @param float $amount  Base amount.
 * @param array $tax_ids Array of tax IDs.
 *
 * @return float Total tax amount.
 * @since 1.0.0
 */
function mjschool_get_tax_amount( $amount, $tax_ids ) {
	if ( empty( $tax_ids ) || ! is_array( $tax_ids ) ) {
		return 0;
	}
	global $wpdb;
	$table_name  = $wpdb->prefix . 'mjschool_taxes';
	$tax_amounts = array();
	foreach ( $tax_ids as $tax_id ) {
		if ( ! is_numeric( $tax_id ) ) {
			continue;
		}
		$query = $wpdb->prepare( "SELECT tax_value FROM {$table_name} WHERE tax_id = %d", $tax_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $query );
		if ( $result ) {
			$tax_percentage = $result->tax_value;
			$tax_amounts[]  = $amount * $tax_percentage / 100;
		}
	}
	return array_sum( $tax_amounts );
}
/**
 * Calculates discount amount based on type.
 *
 * @param float  $amount   Base amount.
 * @param float  $discount Discount value.
 * @param string $type     Discount type ('amount' or 'percentage').
 *
 * @return float Calculated discount.
 * @since 1.0.0
 */
function mjschool_discount_amount( $amount, $discount, $type ) {
	if ( empty( $discount ) || $amount === 0 ) {
		return 0;
	}
	if ( $type === 'amount' ) {
		$total_amount = $discount;
		return $total_amount;
	} else {
		$total_amount = $amount * ( $discount / 100 );
		return $total_amount;
	}
}
/**
 * Returns formatted discount name based on type.
 *
 * @param float  $discount      Discount value.
 * @param string $discount_type Discount type ('amount' or 'percentage').
 *
 * @return string Formatted discount string.
 * @since 1.0.0
 */
function mjschool_get_discount_name( $discount, $discount_type ) {
	if ( $discount_type === 'amount' ) {
		$discount_name = mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) . $discount;
	} else {
		$discount_name = $discount . '%';
	}
	return $discount_name;
}
/**
 * Returns tax name with value using tax ID string.
 *
 * @param string $tax_id_string Comma-separated tax IDs.
 *
 * @return string Tax names with percentages.
 * @since 1.0.0
 */
function mjschool_tax_name_by_tax_id_array_for_invoice( $tax_id_string ) {
	$obj_tax         = new mjschool_tax_Manage();
	$tax_name        = array();
	$tax_id_array    = explode( ',', $tax_id_string );
	$tax_name_string = '';
	if ( ! empty( $tax_id_string ) ) {
		foreach ( $tax_id_array as $tax_id ) {
			$smgt_taxs = $obj_tax->mjschool_get_single_tax( $tax_id );
			if ( ! empty( $smgt_taxs ) ) {
				$tax_name[] = $smgt_taxs->tax_title . '( ' . $smgt_taxs->tax_value . '%)';
			}
		}
		$tax_name_string = implode( ',', $tax_name );
	}
	return $tax_name_string;
	die();
}
/**
 * Gets total payment amount by payment status for current user.
 *
 * @param string $status Payment status ('total' or 'paid').
 *
 * @return float Total amount.
 * @since 1.0.0
 */
function mjschool_get_payment_amout_by_payment_status( $status ) {
	global $wpdb;
	$mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$page                  = 'feepayment';
	$user_access           = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$user_id               = get_current_user_id();
	$role_name             = mjschool_get_user_role( $user_id );
	$status = sanitize_text_field( $status );
	if ( isset( $user_access['own_data'] ) && $user_access['own_data'] === '1' ) {
		// STUDENT OWN PAYMENT AMOUNT.
		if ( $role_name === 'student' ) {
			if ( $status === 'total' ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result     = $wpdb->get_results( "SELECT total_amount FROM {$mjschool_fees_payment} WHERE student_id='$user_id'" );
				$cashAmount = 0;
				foreach ( $result as $value ) {
					$cashAmount += $value->total_amount;
				}
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result     = $wpdb->get_results( "SELECT fees_paid_amount FROM {$mjschool_fees_payment} WHERE student_id='$user_id'" );
				$cashAmount = 0;
				foreach ( $result as $value ) {
					$cashAmount += $value->fees_paid_amount;
				}
			}
		}
		// PARENT OWN CHILD OWN PAYMENT AMOUNT.
		elseif ( $role_name === 'parent' ) {
			$user_data = mjschool_get_parents_child_id( $user_id );
			if ( ! empty( $user_data ) ) {
				$cashAmount = 0;
				foreach ( $user_data as $student_id ) {
					if ( $status === 'total' ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result        = $wpdb->get_results( "SELECT total_amount FROM {$mjschool_fees_payment} WHERE student_id='$student_id'" );
						$studentAmount = 0;
						foreach ( $result as $value ) {
							$studentAmount += $value->total_amount;
						}
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result        = $wpdb->get_results( "SELECT fees_paid_amount FROM {$mjschool_fees_payment} WHERE student_id='$student_id'" );
						$studentAmount = 0;
						foreach ( $result as $value ) {
							$studentAmount += $value->fees_paid_amount;
						}
					}
					$cashAmount += $studentAmount;
				}
			}
		} else {
			// OWN PAYMENT AMOUNT.
			if ( $status === 'total' ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result     = $wpdb->get_results( "SELECT total_amount FROM {$mjschool_fees_payment} WHERE created_by='$user_id'" );
				$cashAmount = 0;
				foreach ( $result as $value ) {
					$cashAmount += $value->total_amount;
				}
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result     = $wpdb->get_results( "SELECT fees_paid_amount FROM {$mjschool_fees_payment} WHERE created_by='$user_id'" );
				$cashAmount = 0;
				foreach ( $result as $value ) {
					$cashAmount += $value->fees_paid_amount;
				}
			}
		}
	} elseif ( $status === 'total' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result     = $wpdb->get_results( "SELECT total_amount FROM {$mjschool_fees_payment}" );
		$cashAmount = 0;
		foreach ( $result as $value ) {
			$cashAmount += $value->total_amount;
		}
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result     = $wpdb->get_results( "SELECT fees_paid_amount FROM {$mjschool_fees_payment}" );
		$cashAmount = 0;
		foreach ( $result as $value ) {
			$cashAmount += $value->fees_paid_amount;
		}
	}
	return $cashAmount;
}
/**
 * Counts attendance records by status for a given date range.
 *
 * @param string $start_date Start date (YYYY-MM-DD).
 * @param string $end_date   End date (YYYY-MM-DD).
 * @param string $status     Attendance status (Present, Absent, etc.).
 *
 * @return int Count of attendance records.
 * @since 1.0.0
 */
function mjschool_attendance_data_by_status( $start_date, $end_date, $status ) {
	global $wpdb;
	$page        = 'attendance';
	$user_access = mjschool_get_userrole_wise_access_right_page_wise_array_for_dashboard( $page );
	$table_name  = $wpdb->prefix . 'mjschool_sub_attendance';
	$user_id     = get_current_user_id();
	$role_name   = mjschool_get_user_role( $user_id );
	$results     = array();
	$start_date  = sanitize_text_field( $start_date );
	$end_date    = sanitize_text_field( $end_date );
	$status      = sanitize_text_field( $status );
	if ( isset( $user_access['own_data'] ) && $user_access['own_data'] === '1' ) {
		switch ( $role_name ) {
			case 'student':
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s AND user_id=%d AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $user_id, $start_date, $end_date ) );
				break;
			case 'parent':
				$user_data = mjschool_get_parents_child_id( $user_id );
				if ( ! empty( $user_data ) ) {
					foreach ( $user_data as $student_id ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$student_results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s AND user_id=%d AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $student_id, $start_date, $end_date ) );
						$results         = array_merge( $results, $student_results );
					}
				}
				break;
			case 'teacher':
				$class_ids = mjschool_get_class_by_teacher_id( $user_id );
				if ( ! empty( $class_ids ) ) {
					foreach ( $class_ids as $class ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$class_results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE status=%s AND class_id=%d AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $class->class_id, $start_date, $end_date ) );
						$results       = array_merge( $results, $class_results );
					}
				}
				break;
			case 'supportstaff':
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s AND attend_by=%d AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $user_id, $start_date, $end_date ) );
				break;
			default:
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $start_date, $end_date ) );
				break;
		}
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s AND role_name='student' AND attendance_date BETWEEN %s AND %s", $status, $start_date, $end_date ) );
	}
	// Ensure unique results.
	$results = array_unique( $results, SORT_REGULAR );
	return ! empty( $results ) ? count( $results ) : '0';
}
/**
 * Removes colon before AM/PM in a time string.
 *
 * @param string $timevalue Time string (e.g., "10:30am").
 *
 * @return string Formatted time string.
 * @since 1.0.0
 */
function mjschool_time_remove_colon_before_am_pm( $timevalue ) {
	if ( strpos( $timevalue, 'am' ) === true ) {
		$time           = str_replace( ':am', ' am', $timevalue );
		$am_translate   = esc_html__( 'am', 'mjschool' );
		$time_translate = str_replace( ' am', ' ' . $am_translate, $time );
	} elseif ( strpos( $timevalue, 'pm' ) === true ) {
		$time           = str_replace( ':pm', ' pm', $timevalue );
		$am_translate   = esc_html__( 'pm', 'mjschool' );
		$time_translate = str_replace( ' pm', ' ' . $am_translate, $time );
	} elseif ( strpos( $timevalue, 'AM' ) === true ) {
		$time           = str_replace( ':AM', ' AM', $timevalue );
		$am_translate   = esc_html__( 'AM', 'mjschool' );
		$time_translate = str_replace( ' AM', ' ' . $am_translate, $time );
	} elseif ( strpos( $timevalue, 'PM' ) === true ) {
		$time           = str_replace( ':PM', ' PM', $timevalue );
		$am_translate   = esc_html__( 'PM', 'mjschool' );
		$time_translate = str_replace( ' PM', ' ' . $am_translate, $time );
	} else {
		$time_translate = '';
	}
	return $time_translate;
}
/**
 * Returns list of temporary student users.
 *
 * @return array List of WP_User objects.
 * @since 1.0.0
 */
function mjschool_admission_student_list() {
	$args   = array(
		'role' => 'student_temp',
	);
	$result = get_users( $args );
	return $result;
}
/**
 * Returns formatted currency string with proper symbol position.
 *
 * @param float $amount Amount to format.
 *
 * @return string Formatted currency string.
 * @since 1.0.0
 */
function mjschool_currency_symbol_position_language_wise( $amount ) {
	$currency_symbol = html_entity_decode( mjschool_get_currency_symbol( get_option( 'mjschool_currency_code' ) ) );
	if ( is_rtl() ) {
		$currency = $amount . $currency_symbol;
	} else {
		$currency = $currency_symbol . $amount;
	}
	return $currency;
}
/**
 * Check file type and extension against allowed MIME types.
 *
 * @param string $tmp_name Temporary file path.
 * @param string $name     Original file name.
 * @return bool True if valid, false otherwise.
 * @since 1.0.0
 */
function mjschool_wp_check_file_type_and_ext( $tmp_name, $name ) {
	$tmp_name  = sanitize_text_field( $tmp_name );
	$name    = sanitize_text_field( $name );
	$file_info = wp_check_filetype_and_ext( $tmp_name, $name );
	// Step 2: Define custom allowed MIME types and extensions.
	$allowed_mime_types = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'gif'  => 'image/gif',
		'bmp'  => 'image/bmp',
		'webp' => 'image/webp',
		'svg'  => 'image/svg+xml',
		'pdf'  => 'application/pdf',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'ppt'  => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'xls'  => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'csv'  => 'text/csv',
	);
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		return false;
	}
	// Check if the extension and MIME type match our custom list.
	$extension = $file_info['ext'];
	$mime_type = $file_info['type'];
	if ( array_key_exists( $extension, $allowed_mime_types ) && $allowed_mime_types[ $extension ] === $mime_type ) {
		return true; // Valid file type.
	}
	return false; // Invalid file type.
}
/**
 * Check if a file is a valid image type.
 *
 * @param string $tmp_name Temporary file path.
 * @param string $name     Original file name.
 * @return bool True if valid image, false otherwise.
 * @since 1.0.0
 */
function mjschool_wp_check_file_type_and_ext_image( $tmp_name, $name ) {
	$tmp_name = sanitize_text_field( $tmp_name );
	$name = sanitize_text_field( $name );
	$file_info = wp_check_filetype_and_ext( $tmp_name, $name );
	// Step 2: Define custom allowed MIME types and extensions.
	$allowed_mime_types = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'gif'  => 'image/gif',
		'bmp'  => 'image/bmp',
		'webp' => 'image/webp',
		'svg'  => 'image/svg+xml',
	);
	if ( ! $file_info['ext'] || ! $file_info['type'] ) {
		return false;
	}
	// Check if the extension and MIME type match our custom list.
	$extension = $file_info['ext'];
	$mime_type = $file_info['type'];
	if ( array_key_exists( $extension, $allowed_mime_types ) && $allowed_mime_types[ $extension ] === $mime_type ) {
		return true; // Valid file type.
	}
	return false; // Invalid file type.
}
/**
 * Upload multiple documents for a user.
 *
 * @param array  $file           File array from $_FILES.
 * @param int    $x              Index of the file to upload.
 * @param string $document_title Document title for naming.
 * @return string Uploaded file name on success.
 * @since 1.0.0
 */
function mjschool_upload_document_user_multiple( $file, $x, $document_title ) {
	$type           = 'document_file';
	$check_document = mjschool_wp_check_file_type_and_ext( $_FILES[ $type ]['tmp_name'][ $x ], $_FILES[ $type ]['name'][ $x ] );
	if ( $check_document ) {
		$parts              = pathinfo( $_FILES[ $type ]['name'][ $x ] );
		$inventoryimagename = 'mjschool_' . mktime( time() ) . '-' . $document_title . '-' . array( $x ) . '-' . 'in' . '.' . $parts['extension'];
		$document_dir       = WP_CONTENT_DIR;
		$document_dir      .= '/uploads/school_assets/';
		$document_path      = $document_dir;
		if ( ! file_exists( $document_path ) ) {
			mkdir( $document_path, 0777, true );
		}
		$imagepath = '';
		if ( is_uploaded_file( $_FILES[ $type ]['tmp_name'][ $x ] ) ) {
			if ( move_uploaded_file( $_FILES[ $type ]['tmp_name'][ $x ], $document_path . $inventoryimagename ) ) {
				$imagepath = $inventoryimagename;
			}
		}
		return $imagepath;
	} else {
		wp_die( esc_html__( 'File type is not allowed.', 'mjschool' ) );
	}
}
/**
 * Return the document target audience.
 *
 * @param string $document_for Target audience.
 * @return string Default is 'Student'.
 * @since 1.0.0
 */
function mjschool_show_document_for( $document_for ) {
	$document_for = sanitize_text_field( $document_for );
	if ( $document_for !== '' ) {
		return $document_for;
	} else {
		return 'Student';
	}
}
/**
 * Display document user name or user group.
 *
 * @param string|int $student_id Student ID or user group string.
 * @return void Echoes the name or group.
 * @since 1.0.0
 */
function mjschool_show_document_user( $student_id ) {
	$student_id = sanitize_text_field( $student_id );
	if ( $student_id === 'all student' || $student_id === '' ) {
		esc_html_e( 'All Student', 'mjschool' );
	} elseif ( $student_id === 'all teacher' ) {
		esc_html_e( 'All Teacher', 'mjschool' );
	} elseif ( $student_id === 'all parent' ) {
		esc_html_e( 'All Parent', 'mjschool' );
	} elseif ( $student_id === 'all supportstaff' ) {
		esc_html_e( 'All Support Staff', 'mjschool' );
	} else {
		echo esc_html( mjschool_user_display_name( $student_id ) );
	}
}
/**
 * Get formatted pass/fail string for students from JSON.
 *
 * @param string $jsonData JSON string containing student data.
 * @return string HTML formatted string with student names and reasons.
 * @since 1.0.0
 */
function mjschool_get_pass_failed_string( $jsonData ) {
	// Decode JSON data into an associative array.
	$studentsArray = json_decode( $jsonData, true );
	// Check if json_decode() worked properly.
	if ( $studentsArray === null && json_last_error() !== JSON_ERROR_NONE ) {
		wp_die( 'Invalid JSON data' );
	}
	// Initialize a counter for numbering.
	$counter = 1;
	// Initialize an array to hold the formatted strings.
	$formattedStrings = array();
	// Iterate through the students array.
	foreach ( $studentsArray as $student ) {
		$id     = $student['student_id'];
		$reason = $student['reason'];
		$name   = mjschool_get_display_name( $id );
		if ( $name ) {
			// Append the formatted string to the array.
			$formattedStrings[] = "<b>{$counter}</b>. {$name}: {$reason}";
		} else {
			// Handle the case where the student ID is not found.
			$formattedStrings[] = "{$counter}. Student ID {$id}: {$reason}";
		}
		++$counter;
	}
	// Combine all formatted strings into a single string separated by new lines.
	$resultString = implode( PHP_EOL, $formattedStrings );
	// Output the result.
	return nl2br( $resultString );
}
/**
 * Generate JSON for contribution data.
 *
 * @param array $data Contribution labels and marks.
 * @return string JSON encoded contribution data.
 * @since 1.0.0
 */
function mjschool_get_costribution_data_jason( $data ) {
	$result = array();
	if ( ! empty( $data['contributions_mark'] ) ) {
		$marks_count = count( $data['contributions_mark'] );
		for ( $a = 0; $a < $marks_count; $a++ ) {
			$custribution_label = $data['contributions_label'][ $a ];
			$contributions_mark = $data['contributions_mark'][ $a ];
			$result[]           = array(
				'id'    => $a + 1,
				'label' => $custribution_label,
				'mark'  => $contributions_mark,
			);
		}
	}
	if ( ! empty( $result ) ) {
		$cunstribution_data = json_encode( $result );
	} else {
		$cunstribution_data = '';
	}
	return $cunstribution_data;
}
/**
 * Check if a reminder was already sent for a student's fees.
 *
 * @param int $student_id Student ID.
 * @param int $fees_id    Fees ID.
 * @return array Query result.
 * @since 1.0.0
 */
function mjschool_check_reminder_send_or_not( $student_id, $fees_id ) {
	$student_id = absint( $student_id );
	$fees_id    = absint( $fees_id );
	$date = date( 'Y-m-d' );
	global $wpdb;
	$mjschool_cron_reminder_log = $wpdb->prefix . 'mjschool_cron_reminder_log';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$mjschool_cron_reminder_log} WHERE student_id=%d AND fees_pay_id=%d AND date_time=%s", $student_id, $fees_id, $date ) );
	return $result;
}
/**
 * Get default options for the plugin.
 *
 * @return array Associative array of default options.
 * @since 1.0.0
 */
function mjschool_update_option() {
	/* Setup Wizard Status. */
	$options = array(
		'mjschool_name'                           	      => esc_attr__( 'School Management System', 'mjschool' ),
		'mjschool_staring_year'                           => '2025',
		'mjschool_address'                         		  => '',
		'mjschool_contact_number'                         => '',
		'mjschool_contry'                                 => 'United States',
		'mjschool_city'                                   => 'Los Angeles',
		"mjschool_custom_class" 					 	  => "school",
		"mjschool_custom_class_display" 			 	  => 0,
		'mjschool_past_pay'                               => 'no',
		'mjschool_prefix'                                 => 'S-',
		'mjschool_invoice_option'                         => 1,
		'mjschool_combine'                                => 0,
		'mjschool_email'                                  => 'admin@gmail.com',
		'mjschool_datepicker_format'                      => 'yy/mm/dd',
		'mjschool_app_logo'                        		  => plugins_url( 'mjschool/assets/images/mjschool-final-logo.png' ),
		'mjschool_logo'                            		  => plugins_url( 'mjschool/assets/images/mjschool-final-logo.png' ),
		'mjschool_system_logo'                            => plugins_url( 'mjschool/assets/images/mjschool-logo-white.png' ),
		'mjschool_background_image'                		  => plugins_url( 'mjschool/assets/images/school_life.jpg' ),
		'mjschool_student_thumb'                          => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
		'mjschool_mjschool-no-data-img'                            => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-plus-icon.png' ),
		'mjschool_parent_thumb'                           => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
		'mjschool_teacher_thumb'                          => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
		'mjschool_supportstaff_thumb'                     => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
		'mjschool_driver_thumb'                           => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
		'mjschool_principal_signature'                    => plugins_url( 'mjschool/assets/images/mjschool-signature-stamp.png' ),
		'mjschool_student_thumb_new'                      => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
		'mjschool_parent_thumb_new'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
		'mjschool_teacher_thumb_new'                      => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
		'mjschool_supportstaff_thumb_new'                 => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
		'mjschool_driver_thumb_new'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
		'mjschool_footer_description'                     => 'Copyright ©' . date( 'Y' ) . ' Mojoomla. All rights reserved.',
		'mjschool_service'                       		  => 'msg91',
		// PAY MASTER OPTION.//
		'mjschool_paymaster_pack'                         => 'no',
		'mjschool_mail_notification'                      => 1,
		'mjschool_notification_fcm_key'                   => '',
		'mjschool_service_enable'                		  => 0,
		'mjschool_student_approval'                       => 1,
		'mjschool_sms_template'                               => 'Hello [mjschool_USER_NAME] ',
		'mjschool_clickatell_mjschool_service'            => array(),
		'mjschool_twillo_mjschool_service'                => array(),
		'mjschool_parent_send_message'                    => 1,
		'mjschool_enable_total_student'                   => 1,
		'mjschool_enable_total_teacher'                   => 1,
		'mjschool_enable_total_parent'                    => 1,
		'mjschool_enable_homework_mail'                   => 0,
		'mjschool_enable_total_attendance'                => 1,
		'mjschool_enable_sandbox'                         => 'yes',
		'mjschool_virtual_classroom_account_id'           => '',
		'mjschool_virtual_classroom_client_id'            => '',
		'mjschool_virtual_classroom_client_secret_id'     => '',
		'mjschool_virtual_classroom_access_token'         => '',
		'mjschool_enable_virtual_classroom'               => 'no',
		'mjschool_return_option'                          => 'yes',
		'mjschool_return_period'                          => 3,
		'mjschool_system_payment_reminder_day'            => 3,
		'mjschool_system_payment_reminder_enable'         => 'no',
		'mjschool_paypal_email'                           => '',
		'razorpay__key'                                   => '',
		'razorpay_secret_mid'                             => '',
		'mjschool_currency_code'                          => 'USD',
		'mjschool_teacher_manage_allsubjects_marks'       => 'yes',
		'mjschool_enable_video_popup_show'                => 'yes',
		'mjschool_registration_title'                     => 'Student Registration',
		'mjschool_student_activation_title'               => 'Student Approved',
		'mjschool_fee_payment_title'                      => 'Fees Alert',
		'mjschool_fee_payment_title_for_parent'           => 'Fees Alert',
		'mjschool_teacher_show_access'                    => 'own_class',
		'mjschool_admissiion_title'                       => 'Request For Admission',
		'mjschool_exam_receipt_subject'                   => 'Exam Receipt Generate',
		'mjschool_bed_subject'                            => 'Hostel Bed Assigned',
		'mjschool_add_approve_admisson_mail_subject'      => 'Admission Approved',
		'mjschool_student_assign_teacher_mail_subject'    => 'New Student has been assigned to you.',
		'mjschool_enable_virtual_classroom_reminder'      => 'yes',
		'mjschool_enable_mjschool_virtual_classroom_reminder' => 'yes',
		'mjschool_virtual_classroom_reminder_before_time' => '30',
		'mjschool_heder_enable'                           => 'yes',
		'mjschool_admission_fees'                         => 'no',
		'mjschool_enable_recurring_invoices'              => 'no',
		'mjschool_admission_amount'                       => '',
		'mjschool_system_color_code'                      => '#5840bb',
		'mjschool_registration_fees'                      => 'no',
		'mjschool_registration_amount'                    => '',
		'mjschool_invoice_notice'                         => 'If You Paid Your Payment than Invoice are automatically Generated.',
		'mjschool_attendence_migration_status'            => 'no',
		'mjschool_add_leave_emails'                       => '',
		'mjschool_leave_approveemails'                    => '',
		'mjschool_add_leave_subject'                      => 'Request For Leave',
		'mjschool_add_leave_subject_of_admin'             => 'Request For Leave',
		'mjschool_add_leave_subject_for_student'          => 'Request For Leave',
		'mjschool_add_leave_subject_for_parent'           => 'Request For Leave',
		'mjschool_leave_approve_subject'                  => 'leave_approve_subject',
		'mjschool_leave_reject_subject'                   => 'leave_reject_subject',
		'mjschool_add_exam_mail_title'                    => 'add_exam_mail_title',
		'mjschool_upload_document_type'                   => 'mjschool_upload_document_type',
		'mjschool_upload_profile_extention'               => 'mjschool_upload_profile_extention',
		'mjschool_upload_document_size'                   => 'mjschool_upload_document_size',
		'mjschool_upload_profile_size'                    => 'mjschool_upload_profile_size',
	);
	return $options;
}
/**
 * Get student and teacher users for library selection.
 *
 * @return void Echoes <option> HTML elements.
 * @since 1.0.0
 */
function mjschool_get_student_and_teacher_for_library() {
	$school_obj = new MJSchool_Management( get_current_user_id() );
	$exlude_id  = mjschool_approve_student_list();
	$student    = get_users(
		array(
			'role'    => 'student',
			'exclude' => $exlude_id,
		)
	);
	$teacher    = get_users( array( 'role' => 'teacher' ) );
	$all_user   = array(
		'Student' => $student,
		'Teacher' => $teacher,
	);
	foreach ( $all_user as $key => $value ) {
		if ( ! empty( $value ) ) {
			echo '<optgroup label="' . esc_attr( $key ) . '" style = "text-transform: capitalize;">';
			foreach ( $value as $user ) {
				if ( $key === 'student' ) {
					foreach ( $user as $student ) {
						echo '<option value="' . esc_attr( $student->ID ) . '">' . esc_html( $student->display_name ) . '</option>';
					}
				} else {
					echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . '</option>';
				}
			}
		}
	}
}
/**
 * Get teacher users list for selection.
 *
 * @return void Echoes <option> HTML elements.
 * @since 1.0.0
 */
function mjschool_get_teacher_list() {
	$teacher  = get_users( array( 'role' => 'teacher' ) );
	$all_user = array(
		'Teacher' => $teacher,
	);
	foreach ( $all_user as $key => $value ) {
		if ( ! empty( $value ) ) {
			echo '<optgroup label="' . esc_attr( $key ) . '" style = "text-transform: capitalize;">';
			foreach ( $value as $user ) {
				echo '<option value="' . esc_attr( $user->ID ) . '">' . esc_html( $user->display_name ) . '</option>';
			}
		}
	}
}
/**
 * Display room facility labels from JSON data.
 *
 * @param string $data JSON string of room facilities.
 * @return string HTML formatted facility labels.
 * @since 1.0.0
 */
function mjschool_room_facility_show( $data ) {
	$facility_label = '';
	if ( ! empty( $data ) ) {
		$facility         = json_decode( $data, true ); // Convert to associative array.
		$facility_strings = array();
		foreach ( $facility as $category => $items ) {
			$facility_strings[] = $category . ' => ' . implode( ', ', $items );
		}
		$facility_label = implode( '<br>', $facility_strings ); // Display each category on a new line.
	}
	return $facility_label;
}
/**
 * Convert date from user format to database format.
 *
 * @param string $date Date in user-selected format.
 * @return string Date in 'Y-m-d' format.
 * @since 1.0.0
 */
function mjschool_get_format_for_db( $date ) {
	if ( ! empty( $date ) ) {
		$date     = trim( $date );
		$new_date = DateTime::createFromFormat( mjschool_get_php_date_format( get_option( 'mjschool_datepicker_format' ) ), $date );
		$new_date = $new_date->format( 'Y-m-d' );
		return $new_date;
	} else {
		$new_date = '';
		return $new_date;
	}
}
/**
 * Get failed student report data.
 *
 * @param int    $exam_id     Exam ID.
 * @param int    $class_id    Class ID.
 * @param int    $section_id  Section ID (optional).
 * @param int    $passing_mark Passing mark threshold.
 * @return array Array of student and marks data.
 * @since 1.0.0
 */
function mjschool_get_failed_student_report_data( $exam_id, $class_id, $section_id, $passing_mark ) {
	global $wpdb;
	$exam_id       = absint( $exam_id );
	$class_id      = absint( $class_id );
	$section_id    = absint( $section_id );
	$table_marks   = $wpdb->prefix . 'mjschool_marks';
	$table_users   = $wpdb->prefix . 'users';
	$where_section = $section_id != '' ? 'AND m.section_id = %d' : '';
	$sql           = "SELECT m.*, u.* FROM {$table_marks} AS m JOIN $table_users AS u ON m.student_id = u.ID WHERE m.exam_id = %d AND m.class_id = %d $where_section AND m.marks < %d";
	// Prepare query with proper bindings.
	if ( $section_id != '' ) {
		$query = $wpdb->prepare( $sql, $exam_id, $class_id, $section_id, $passing_mark );
	} else {
		$query = $wpdb->prepare( $sql, $exam_id, $class_id, $passing_mark );
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->get_results( $query );
}
/**
 * Encrypt a given ID using AES-256-CBC.
 *
 * @param string|int $id ID to encrypt.
 * @return string Encrypted and base64-encoded ID.
 * @since 1.0.0
 */
function mjschool_encrypt_id( $id ) {
	$key       = 'mjschool'; // Change this to a secure key.
	$iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
	$encrypted = openssl_encrypt( $id, 'aes-256-cbc', $key, 0, $iv );
	$encoded   = base64_encode( $iv . $encrypted );
	return strtr( $encoded, '+/=', '-_,' ); // Replace + with -, / with _, and = with , .
}
/**
 * Decrypt a previously encrypted ID.
 *
 * @param string $encrypted_id Encrypted ID string.
 * @return string Decrypted ID.
 * @since 1.0.0
 */
function mjschool_decrypt_id( $encrypted_id ) {
	$key        = 'mjschool';
	$decoded_id = strtr( $encrypted_id, '-_,', '+/=' ); // Convert back to original base64 characters.
	$data       = base64_decode( $decoded_id );
	$iv_length  = openssl_cipher_iv_length( 'aes-256-cbc' );
	$iv         = substr( $data, 0, $iv_length );
	$encrypted  = substr( $data, $iv_length );
	return openssl_decrypt( $encrypted, 'aes-256-cbc', $key, 0, $iv );
}
/**
 * Generate a nonce for a given action.
 *
 * @param string $mjschool_action Action name.
 * @return string Nonce value.
 * @since 1.0.0
 */
function mjschool_get_nonce( $mjschool_action ) {
	if ( $mjschool_action ) {
		return wp_create_nonce( $mjschool_action );
	} else {
		return '';
	}
}
/**
 * Generate fees payment PDF for mobile app.
 *
 * @param string $payment_id Payment ID.
 * @return \Mpdf\Mpdf Generated PDF object.
 * @since 1.0.0
 */
function mjschool_fees_payment_pdf_for_mobile_app( $payment_id ) {
	ob_start();
	error_reporting( 0 );
	mjschool_student_payment_history_pdf( $payment_id );
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
	$invoice_dir  = WP_CONTENT_DIR . '/uploads/invoice_pdf/fees_payment';
	$invoice_path = $invoice_dir;
	mkdir( $invoice_path, 0777, true );
	$mpdf->Output( WP_CONTENT_DIR . '/uploads/invoice_pdf/fees_payment/' . mjschool_decrypt_id( $payment_id ) . '.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	ob_end_flush();
	return $mpdf;
}
/**
 * Generate receipt PDF for mobile app.
 *
 * @param string $payment_id Payment ID.
 * @param string $receipt    Receipt identifier.
 * @return \Mpdf\Mpdf Generated PDF object.
 * @since 1.0.0
 */
function mjschool_fees_receipt_pdf_for_mobile_app( $payment_id, $receipt ) {
	ob_start();
	error_reporting( 0 );
	mjschool_student_receipt_history_pdf( $payment_id, $receipt );
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
	$invoice_dir  = WP_CONTENT_DIR . '/uploads/invoice_pdf/receipt_payment';
	$invoice_path = $invoice_dir;
	mkdir( $invoice_path, 0777, true );
	$mpdf->Output( WP_CONTENT_DIR . '/uploads/invoice_pdf/receipt_payment/' . mjschool_decrypt_id( $payment_id ) . '.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	ob_end_flush();
	return $mpdf;
}
/**
 * Generate income PDF for mobile app.
 *
 * @param string $invoice_id   Invoice ID.
 * @param string $invoice_type Invoice type.
 * @return \Mpdf\Mpdf Generated PDF object.
 * @since 1.0.0
 */
function mjschool_fees_income_pdf_for_mobile_app( $invoice_id, $invoice_type ) {
	ob_start();
	error_reporting( 0 );
	mjschool_student_invoice_pdf( $invoice_id, $invoice_type );
	$out_put = ob_get_contents();
	ob_clean();
	header( 'Content-type: application/pdf' );
	header( 'Content-Disposition: inline; filename="feepaymenthistory"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Accept-Ranges: bytes' );
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$mpdf = new Mpdf\Mpdf();
	$mpdf->SetTitle( 'Income Payment' );
	$mpdf->autoScriptToLang = true;
	$mpdf->autoLangToFont   = true;
	if ( is_rtl() ) {
		$mpdf->autoScriptToLang = true;
		$mpdf->autoLangToFont   = true;
		$mpdf->SetDirectionality( 'rtl' );
	}
	$mpdf->WriteHTML( $out_put );
	$invoice_dir  = WP_CONTENT_DIR . '/uploads/invoice_pdf/income';
	$invoice_path = $invoice_dir;
	mkdir( $invoice_path, 0777, true );
	$mpdf->Output( WP_CONTENT_DIR . '/uploads/invoice_pdf/income/' . mjschool_decrypt_id( $invoice_id ) . '.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	ob_end_flush();
	return $mpdf;
}
/**
 * Generate exam receipt PDF for mobile app.
 *
 * @param int    $student_id Student ID.
 * @param int    $exam_id    Exam ID.style="float:right;width:50%;"
 * @param string $name       File name for the PDF.
 * @return \Mpdf\Mpdf Generated PDF object.
 * @since 1.0.0
 */
function mjschool_generate_exam_receipt_mobile_app( $student_id, $exam_id, $name ) {
	ob_start();
	error_reporting( 0 );
	mjschool_student_exam_receipt_pdf( $student_id, $exam_id );
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
	$invoice_dir  = WP_CONTENT_DIR . '/uploads/exam_receipt';
	$invoice_path = $invoice_dir;
	mkdir( $invoice_path, 0777, true );
	$mpdf->Output( WP_CONTENT_DIR . '/uploads/exam_receipt/' . $name . '.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	ob_end_flush();
	return $mpdf;
}
/**
 * Generates and outputs a student's exam result as a PDF for mobile app use.
 *
 * Fetches marks, grades, and exam details, formats into HTML, and generates PDF using mPDF.
 * Supports RTL and LTR layouts and saves the PDF in the WordPress uploads directory under 'result'.
 *
 * @since 1.0.0
 *
 * @param int    $student_id  The ID of the student.
 * @param int    $exam_id     The ID of the exam.
 * @param string $name        The desired filename for the PDF (without extension).
 * @param int    $class_id    The ID of the class.
 * @param int    $section_id  The ID of the section.
 *
 * @return \Mpdf\Mpdf Returns the mPDF object used to generate the PDF.
 */
function mjschool_generate_result_for_mobile_app( $student_id, $exam_id, $name, $class_id, $section_id ) {
	ob_start();
	error_reporting( 0 );
	$uid      = absint( $student_id );
	$exam_id  = absint( $exam_id );
	$obj_mark = new mjschool_Marks_Manage();
	$exam_obj = new mjschool_exam();
	$user          = get_userdata( $uid );
	$user_meta     = get_user_meta( $uid );
	$subject       = $obj_mark->mjschool_student_subject_list( $class_id, $section_id );
	$total_subject = count( $subject );
	$total       = 0;
	$grade_point = 0;
	$umetadata   = mjschool_get_user_image( $uid );
	error_reporting( 1 );
	if ( is_rtl() ) {
		?>
		<div class="container" class="mjschool_margin_bottom_8px">
			<div style="border: 2px solid;">
				<div style="padding:20px;">
					<div style="float:right;width:100%;">
						<div style="float:right;width:25%;">
							<div class="mjschool-custom-logo-class" style="float:right;border-radius:50px;">
								<div style="width: 150px;background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;">
								</div>
							</div>
						</div>
						<div style="float:right; width:75%;padding-top:30px;">
							<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </p>
							<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?> </p>
							<div class="mjschool_fees_center_margin_0px">
								<p class="mjschool_fees_width_fit_content_inline"> <?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?> </p>
								<p class="mjschool_fees_width_fit_content_inline"> &nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?> </p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="border: 2px solid;margin-bottom:8px;">
			<div style="float:right;width:100%;">
				<div  class="mjschool_padding_10px">
					<div style="float:right;width:50%;">
						<?php esc_html_e( 'Student Name', 'mjschool' ); ?>: <b><?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?>
					</div>
					<div style="float:right;width:50%;">
						<?php esc_html_e( 'Roll Number', 'mjschool' ); ?>: <b><?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?> </b>
					</div>
				</div>
			</div>
			<div style="float:right;width:100%;">
				<div  class="mjschool_padding_10px">
					<div style="float:right;width:50%;">
						<?php esc_html_e( 'Class', 'mjschool' ); ?>: <b> <?php echo esc_html( mjschool_get_class_name( $class_id ) ); ?> </b>
					</div>
					<div style="float:right;width:50%;"><?php esc_html_e( 'Section', 'mjschool' ); ?>:
						<b>
							<?php
							if ( $section_name != '' ) {
								echo esc_html( mjschool_get_section_name( $section_name ) );
							} else {
								esc_html_e( 'No Section', 'mjschool' );
							}
							?>
						</b>
					</div>
					<div style="float:right;width:33%;"><?php esc_html_e( 'Exam Name', 'mjschool' ); ?>:
						<b><?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?> </b>
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
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $sub->sub_name ); ?></td> <?php
						$obtain_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $sub->subid, $uid );
						if ( $contributions === 'yes' ) {
							$subject_total = 0;
							foreach ( $contributions_data_array as $con_id => $con_value ) {
								$mark_value     = is_array( $obtain_marks ) ? ( $obtain_marks[ $con_id ] ?? 0 ) : $obtain_marks;
								$subject_total += $mark_value;
								?>
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $mark_value ); ?> </td> <?php
							}
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $subject_total ); ?> </td> <?php
						} else {
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $obtain_marks ); ?> </td> <?php
						}
						?>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $obj_mark->mjschool_get_grade( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
						<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $obj_mark->mjschool_get_grade_comment( $exam_id, $class_id, $sub->subid, $uid ) ); ?> </td>
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
		<div style="border: 2px solid;width:100%;float: right;margin-bottom:8px;">
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
			<hr>
			<div  style="direction: rtl;margin-right: 20px;">
				<br>
				<div style="float:right;margin-right:0px;margin-right: auto;">

					<div> <img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" /> </div>

					<div style="border: 1px solid  !important;width: 150px;margin-top: 5px;"></div>
					<div style="margin-right:10px;margin-bottom:10px;"> <?php esc_html_e( 'Principal Signature', 'mjschool' ); ?> </div>
				</div>
			</div>
		</div>
		<?php
	} else {
		?>
		<div class="container mjschool_margin_bottom_8px">
			<div style="border: 2px solid;">
				<div style="padding:20px;">
					<div class="mjschool_float_left_width_100">
						<div class="mjschool_float_left_width_25">
							<div class="mjschool-custom-logo-class mjschool_left_border_redius_50">
								<div style="width: 150px;background-image: url( '<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>' );height: 150px;border-radius: 50%;background-repeat:no-repeat;background-size:cover;">
								</div>
							</div>
						</div>
						<div class="mjschool_float_left_padding_width_75">
							<p class="mjschool_fees_widht_100_fonts_24px"> <?php echo esc_html( get_option( 'mjschool_name' ) ); ?></p>
							<p class="mjschool_fees_center_fonts_17px"> <?php echo esc_html( get_option( 'mjschool_address' ) ); ?></p>
							<div class="mjschool_fees_center_margin_0px">
								<p class="mjschool_fees_width_fit_content_inline">
									<?php esc_html_e( 'E-mail', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_email' ) ); ?>
								</p>
								<p class="mjschool_fees_width_fit_content_inline">
									&nbsp;&nbsp;<?php esc_html_e( 'Phone', 'mjschool' ); ?> : <?php echo esc_html( get_option( 'mjschool_contact_number' ) ); ?>
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="border: 2px solid;margin-bottom:8px;">
			<div class="mjschool_float_left_width_100">
				<div  class="mjschool_padding_10px">
					<div class="mjschool_float_width_css" >
						<?php esc_html_e( 'Student Name', 'mjschool' ); ?>: <b><?php echo esc_html( get_user_meta( $uid, 'first_name', true ) ); ?>&nbsp;<?php echo esc_html( get_user_meta( $uid, 'last_name', true ) ); ?>
					</div>
					<div class="mjschool_float_width_css" >
						<?php esc_html_e( 'Roll Number', 'mjschool' ); ?>: <b><?php echo esc_html( get_user_meta( $uid, 'roll_id', true ) ); ?> </b>
					</div>
				</div>
			</div>
			<div class="mjschool_float_left_width_100">
				<div  class="mjschool_padding_10px">
					<div class="mjschool_float_width_css" ><?php esc_html_e( 'Class', 'mjschool' ); ?>:
						<b><?php echo esc_html( mjschool_get_class_name( $class_id ) ); ?></b>
					</div>
					<div class="mjschool_float_width_css" ><?php esc_html_e( 'Section', 'mjschool' ); ?>:
						<b>
							<?php
							if ( $section_name != '' ) {
								echo esc_html( mjschool_get_section_name( $section_name ) );
							} else {
								esc_html_e( 'No Section', 'mjschool' );
							}
							?>
						</b>
					</div>
					<div style="float:left;width:33%;"><?php esc_html_e( 'Exam Name', 'mjschool' ); ?>:
						<b><?php echo esc_html( mjschool_get_exam_name_id( $exam_id ) ); ?> </b>
					</div>
				</div>
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
						?>
						<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Total', 'mjschool' ) . ' ( ' . esc_html( $exam_marks ) . ' )'; ?></th>
						<?php
					}
					?>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Grade', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Remarks', 'mjschool' ); ?></th>
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
								<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $mark_value ); ?></td>
								<?php
							}
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $subject_total ); ?></td>
							<?php
						} else {
							?>
							<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"><?php echo esc_html( $obtain_marks ); ?></td>
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
		</table>
		<table style="float:left;width:100%;border:1px solid #000;margin-bottom:8px;" cellpadding="10" cellspacing="0">
			<thead>
				<tr style="border-bottom: 1px solid #000;background-color:#b8daff;">
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtainable', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Percentage(%)', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'GPA', 'mjschool' ); ?></th>
					<th style="border-bottom: 1px solid #000;text-align:left;border-right: 1px solid #000;"><?php esc_html_e( 'Result', 'mjschool' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr style="border-bottom: 1px solid #000;">
					<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $total_max_mark ); ?></td>
					<td style="border-bottom: 1px solid #000;border-right: 1px solid #000;"> <?php echo esc_html( $total ); ?></td>
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
		<div style="border: 2px solid;width:100%;float: left;margin-bottom:8px;">
			<div  style="direction: rtl;margin-right: 20px;">
				<br>
				<div style="float:right;margin-right:0px;margin-left: auto;">
					<div>
						<img src="<?php echo esc_url( get_option( 'mjschool_principal_signature' ) ); ?>" style="width:100px; margin-right:15px;" />
					</div>
					<div style="border: 1px solid  !important;width: 150px;margin-top: 5px;"></div>
					<div style="margin-right:10px;margin-bottom:10px;">
						<?php esc_html_e( 'Principal Signature', 'mjschool' ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	$out_put = ob_get_contents();
	ob_clean();
	header( 'Content-type: application/pdf' );
	header( 'Content-Disposition: inline; filename="result"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Accept-Ranges: bytes' );
	require_once MJSCHOOL_PLUGIN_DIR . '/lib/mpdf/vendor/autoload.php';
	$stylesheet1 = file_get_contents( MJSCHOOL_PLUGIN_DIR . '/assets/css/mjschool-style.css' ); // Get css content.
	$mpdf        = new Mpdf\Mpdf(
		array(
			'mode'   => 'utf-8',
			'format' => array( 250, 236 ),
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
	$invoice_dir  = WP_CONTENT_DIR . '/uploads/result';
	$invoice_path = $invoice_dir;
	mkdir( $invoice_path, 0777, true );
	$mpdf->Output( WP_CONTENT_DIR . '/uploads/result/' . $name . '.pdf', 'F' );
	unset( $out_put );
	unset( $mpdf );
	ob_end_flush();
	return $mpdf;
}
/**
 * Get a module name as per page found.
 *
 * @since 1.0.0
 *
 * @param string $page string Page name.
 *
 * @return string $module string contain module name.
 */
function mjschool_get_module_name_for_custom_field($page){
	$module = '';
	$page = sanitize_text_field( $page );
	$sanitize_tab = isset($_REQUEST['tab']) ? sanitize_text_field(wp_unslash($_REQUEST['tab'])) : '';
	if(isset($page) && ($page === 'mjschool_library' || $page === 'library')) {
		$module = 'library';
	}
	if(isset($page) && ($page === 'mjschool_hostel' || $page === 'hostel')) {
		$module = 'hostel';
	}
	if(isset($page) && ($page === 'mjschool_transport' || $page === 'transport')) {
		$module = 'transport';
	}
	if(isset($page) && ($page === 'mjschool_message' || $page === 'message')) {
		$module = 'message';
	}
	if(isset($page) && ($page === 'mjschool_notice' || $page === 'notice')) {
		$module = 'notice';
	}
	if(isset($page) && $page === 'mjschool_notification') {
		$module = 'notification';
	}
	if(isset($page) && ($page === 'mjschool_event' || $page === 'event')) {
		$module = 'event';
	}
	if(isset($page) && ($page === 'mjschool_exam' || $page === 'exam')) {
		$module = 'exam';
	}
	if(isset($page) && $page === 'mjschool_admission') {
		$module = 'admission';
	}
	if(isset($page) && ($page === 'mjschool_hall' || $page === 'exam_hall' )) {
		$module = 'examhall';
	}
	if(isset($page) && ($page === 'mjschool_grade' || $page === 'grade')) {
		$module = 'grade';
	}
	if(isset($page) && ($page === 'mjschool_teacher' || $page === 'teacher')) {
		$module = 'teacher';
	}
	if(isset($page) && $page === 'mjschool_supportstaff') {
		$module = 'supportstaff';
	}
	if(isset($page) && ($page === 'mjschool_parent' || $page === 'parent')) {
		$module = 'parent';
	}
	if(isset($page) && ($page === 'mjschool_Subject' || $page === 'subject')) {
		$module = 'subject';
	}
	if(isset($page) && ($page === 'mjschool_student_homewrok' || $page === 'homework')) {
		$module = 'homework';
	}
	if(isset($page) && ($page === 'mjschool_leave' || $page === 'leave')) {
		$module = 'leave';
	}
	if(isset($page) && ($page === 'mjschool_tax' || $page === 'tax')) {
		$module = 'tax';
	}
	if(isset($page) && (($page === 'payment' || $page === 'mjschool_payment') && $sanitize_tab === 'expenselist')) {
		$module = 'expense';
	}
	if(isset($page) && (($page === 'payment' || $page === 'mjschool_payment') && $sanitize_tab === 'incomelist')) {
		$module = 'income';
	}
	if(isset($page) && ($page === 'feepayment'))
	{
		$module = 'fee_transaction';
	}
	if(isset($page) && ($page === 'admission'))
	{
		$module = 'admission';
	}
	if(isset($page) && ($page === 'student'))
	{
		$module = 'student';
	}
	if(isset($page) && ($page === 'document' || $page === 'mjschool_document'))
	{
		$module = 'document';
	}
	if(isset($page) && ($page === 'class' || $page === 'mjschool_document'))
	{
		$module = 'class';
	}
	if(isset($page) && ($page === 'mjschool_fees_payment' && $sanitize_tab === 'feeslist')) {
		$module = 'fee_pay';
	}
	if(isset($page) && ($page === 'mjschool_fees_payment' && $sanitize_tab === 'feespaymentlist')) {
		$module = 'fee_list';
	}
	if(isset($page) && ($page === 'mjschool_fees_payment' && $sanitize_tab === 'view_fessreceipt')) {
		$module = 'fee_transaction';
	}
	
	if(!empty($module)) {
		return $module;
	}
}
/**
 * Prints formatted weightage data for a given merge configuration.
 *
 * @since 1.0.0
 *
 * @param string $merge_config JSON encoded merge configuration.
 *
 * @return void
 */
function mjschool_print_weightage_data( $merge_config ) {
	$merge_config_data = json_decode( $merge_config );
	$dataString        = ''; // Initialize variable.
	foreach ( $merge_config_data as $item ) {
		$exam_name   = mjschool_get_exam_name_id( $item->exam_id );
		$line        = sprintf(
			'📖 %s: %s | ⚖️ %s: %s%%',
			esc_html__( 'Exam', 'mjschool' ),
			esc_html( $exam_name ),
			esc_html__( 'Weightage', 'mjschool' ),
			esc_html( $item->weightage )
		);
		$dataString .= $line . '<br>';
	}
	echo wp_kses_post( $dataString );
}
/**
 * Returns formatted weightage data as a string for PDF generation.
 *
 * @since 1.0.0
 *
 * @param string $merge_config JSON encoded merge configuration.
 *
 * @return string Formatted string containing exam names and their weightages.
 */
function mjschool_print_weightage_data_pdf( $merge_config ) {
	$merge_config_data = json_decode( $merge_config );
	$dataArray         = array(); // Initialize array to store formatted strings.
	foreach ( $merge_config_data as $item ) {
		$exam_name   = mjschool_get_exam_name_id( $item->exam_id );
		$dataArray[] = $exam_name . '( ' . $item->weightage . ' )';
	}
	// Join array elements with ' + ' separator.
	return implode( ' + ', $dataArray );
}
/**
 * Checks whether an exam has contributions enabled.
 *
 * @since 1.0.0
 *
 * @param int $exam_id The ID of the exam.
 *
 * @return string 'yes' if contributions are enabled, otherwise returns other value.
 */
function mjschool_check_contribution( $exam_id ) {
	$exam_id         = absint( $exam_id );
	$exam_obj      = new mjschool_exam();
	$exam_data     = $exam_obj->mjschool_exam_data( $exam_id );
	$contributions = $exam_data->contributions;
	return $contributions;
}
/**
 * Gets all exams for a class (with optional section) including merged exam data.
 *
 * @since 1.0.0
 *
 * @param int $class_id   The ID of the class.
 * @param int $section_id Optional section ID (default 0).
 *
 * @return array List of exams and merged exam data.
 */
function mjschool_get_all_exam_by_class_id_array_with_merge_data( $class_id, $section_id = 0 ) {
	global $wpdb;
	// Ensure $class_id is a positive integer.
	$class_id = absint( $class_id );
	// Define table names.
	$exam_table                = $wpdb->prefix . 'mjschool_exam';
	$exam_merge_settings_table = $wpdb->prefix . 'mjschool_exam_merge_settings';
	// Prepare and execute the query for the 'exam' table.
	$exam_query = $wpdb->prepare( "SELECT *, 'exam' AS source_table FROM `$exam_table` WHERE class_id = %d", $class_id, $section_id );
	// Prepare and execute the query for the 'exam_merge_settings' table.
	$exam_merge_settings_query = $wpdb->prepare( "SELECT *, 'exam_merge_settings' AS source_table FROM `$exam_merge_settings_table` WHERE class_id = %d AND section_id = %d AND status = %s", $class_id, $section_id, 'enable' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$exam_results = $wpdb->get_results( $exam_query );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$exam_merge_settings_results = $wpdb->get_results( $exam_merge_settings_query );
	// Merge the results into a single array.
	$combined_results = array_merge( $exam_results, $exam_merge_settings_results );
	return $combined_results;
}
/**
 * Gets all classes from the database.
 *
 * @since 1.0.0
 *
 * @return array List of class names.
 */
function mjschool_get_all_class_array() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_class';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT class_name FROM {$table_name} limit 1" ) );
	return $results;
}
/**
 * Generates an attendance report for the current month.
 *
 * @since 1.0.0
 *
 * @return array List of students with attendance stats (total working days, present, absent, percentage).
 */
function mjschool_attedance_advance_report() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_sub_attendance';
	$start_date = date( 'Y-m-01' );
	$end_date   = date( 'Y-m-t' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, class_id, section_id, COUNT(DISTINCT attendance_date) AS total_working_days, SUM(CASE WHEN daily_status = 'Present' THEN 1 ELSE 0 END) AS total_present, SUM(CASE WHEN daily_status = 'Absent' THEN 1 ELSE 0 END) AS total_absent, ROUND( (SUM(CASE WHEN daily_status = 'Present' THEN 1 ELSE 0 END) / NULLIF(COUNT(DISTINCT attendance_date), 0 ) ) * 100, 2 ) AS attendance_percentage FROM ( SELECT user_id, class_id, section_id, attendance_date, CASE WHEN SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) > 0 THEN 'Present' ELSE 'Absent' END AS daily_status FROM {$table_name} WHERE role_name = 'student' AND attendance_date BETWEEN %s AND %s GROUP BY user_id, attendance_date ) AS daily_attendance GROUP BY user_id, class_id, section_id", $start_date, $end_date ) );
	return $results;
}
/**
 * Generates teacher performance report based on marks for each class and subject.
 *
 * @since 1.0.0
 *
 * @return array List of teachers with highest, lowest, and average marks per subject.
 */
function mjschool_get_teacher_perfomance_report() {
	global $wpdb;
	$marks_table           = $wpdb->prefix . 'mjschool_marks';
	$subject_table         = $wpdb->prefix . 'mjschool_subject';
	$teacher_class_table   = $wpdb->prefix . 'mjschool_teacher_class';
	$teacher_subject_table = $wpdb->prefix . 'mjschool_teacher_subject';
	$class_table           = $wpdb->prefix . 'mjschool_class';
	$wp_users_table        = $wpdb->prefix . 'users';
	// Get raw data.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$raw_results = $wpdb->get_results( "SELECT m.marks, m.contributions, m.class_marks, m.subject_id, m.class_id, s.sub_name AS subject_name, ts.teacher_id, u.display_name AS teacher_name, c.class_name FROM {$marks_table} m INNER JOIN {$subject_table} s ON s.subid = m.subject_id INNER JOIN {$teacher_subject_table} ts ON ts.subject_id = s.subid INNER JOIN {$teacher_class_table} tc ON tc.teacher_id = ts.teacher_id AND tc.class_id = m.class_id INNER JOIN {$wp_users_table} u ON u.ID = ts.teacher_id INNER JOIN {$class_table} c ON c.class_id = m.class_id");
	// Processed results.
	$final_data = array();
	foreach ( $raw_results as $row ) {
		// Calculate final mark based on contribution.
		if ( strtolower( $row->contributions ) === 'yes' && ! empty( $row->class_marks ) ) {
			$class_marks_array = json_decode( $row->class_marks, true );
			if ( is_array( $class_marks_array ) && count( $class_marks_array ) > 0 ) {
				$total = array_sum( array_map( 'floatval', $class_marks_array ) );
				$average = floatval( $total ); // fallback.
			} else {
				$average = floatval( $row->marks ); // fallback.
			}
		} else {
			$average = floatval( $row->marks );
		}
		// Grouping key.
		$key = $row->teacher_name . '|' . $row->class_name . '|' . $row->subject_name;
		// Collect data.
		if ( ! isset( $final_data[ $key ] ) ) {
			$final_data[ $key ] = array(
				'teacher_id'   => $row->teacher_id,
				'teacher_name' => $row->teacher_name,
				'class_name'   => $row->class_name,
				'subject_name' => $row->subject_name,
				'marks'        => array(),
			);
		}
		$final_data[ $key ]['marks'][] = $average;
	}
	// Final stats.
	$report = array();
	foreach ( $final_data as $entry ) {
		$marks    = $entry['marks'];
		$report[] = array(
			'teacher_id'   => $entry['teacher_id'],
			'teacher_name' => $entry['teacher_name'],
			'class_name'   => $entry['class_name'],
			'subject_name' => $entry['subject_name'],
			'highest_mark' => max( $marks ),
			'lowest_mark'  => min( $marks ),
			'average_mark' => round( array_sum( $marks ) / count( $marks ), 2 ),
		);
	}
	// Sort by average_mark descending.
	usort(
		$report,
		function ( $a, $b ) {
			return $b['average_mark'] <=> $a['average_mark'];
		}
	);
	return $report;
}
/**
 * Retrieves all leave data ordered by start date descending.
 *
 * @since 1.0.0
 *
 * @return array List of leave records.
 */
function mjschool_get_leave_data_advance_report() {
	global $wpdb;
	$tbl_name = $wpdb->prefix . 'mjschool_leave';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( "SELECT * FROM {$tbl_name} ORDER BY start_date DESC" );
	return $result;
}
/**
 * Retrieves all fee payment records.
 *
 * @since 1.0.0
 *
 * @return array List of fee payments.
 */
function mjschool_get_payment_report_front_all_advance() {
	global $wpdb;
	$mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	$sql                   = "SELECT * FROM {$mjschool_fees_payment}  ";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $sql );
	return $result;
}
/**
 * Replaces placeholders in a message string with corresponding values from an array.
 *
 * @since 1.0.0
 *
 * @param array  $arr     Associative array of replacements (key => value).
 * @param string $message The message string containing placeholders.
 *
 * @return string The message with replacements applied.
 */
function mjschool_string_replacemnet( $arr, $message ) {
	$data = str_replace( array_keys( $arr ), array_values( $arr ), $message );
	return $data;
}
/**
 * Deletes a letter/certificate from the database by ID.
 *
 * @since 1.0.0
 *
 * @param int $id The ID of the letter/certificate to delete.
 *
 * @return int|false Number of rows affected, or false on error.
 */
function mjschool_delete_letter_table_by_id( $id ) {
	global $wpdb;
	$id    = intval( $id );
	$table = $wpdb->prefix . 'mjschool_certificate';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id = %d", $id ) );
	return $result;
}
/**
 * Clones a certificate template to create a new certificate.
 *
 * @since 1.0.0
 *
 * @param string $base_option_name  Option name of the base certificate.
 * @param string $new_option_prefix Prefix for the new certificate name.
 *
 * @return array|\WP_Error Success message array or WP_Error if base certificate not found.
 */
function mjschool_clone_certificate_template( $base_option_name, $new_option_prefix ) {
	global $wpdb;
	$table    = $wpdb->prefix . 'mjschool_daynamic_certificate';
	$template = get_option( $base_option_name . '_template' );
	$title    = get_option( $base_option_name . '_title' );
	if ( $template === false || $title === false ) {
		return new WP_Error( 'not_found', 'Base certificate not found.' );
	}
	// Generate a unique name like sample_certificate_1, _2, .
	$i = 1;
	do {
		$cert_name = $new_option_prefix . "_{$i}";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$exists = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE certificate_name = %s", $cert_name ) );
		++$i;
	} while ( $exists > 0 );
	// Now insert into custom table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$wpdb->insert($table,array( 'certificate_name' => $cert_name,'certificate_content' => $template,'created_date' => current_time( 'mysql' ) ) );
	return array(
		'message' => "New certificate created in table: {$cert_name}",
	);
}
/**
 * Outputs an HTML <select> list of teachers with an optional pre-selected teacher.
 *
 * Fetches all users with the 'teacher' role and groups them under an optgroup labeled "Teacher".
 * If a `$selected_id` is provided, that teacher will be marked as selected in the dropdown.
 *
 * @since 1.0.0
 *
 * @param int|string $selected_id Optional. The user ID of the teacher to pre-select. Default empty.
 *
 * @return void Outputs the <option> elements directly; does not return a value.
 */
function mjschool_get_teacher_list_selected( $selected_id = '' ) {
	$teacher  = get_users( array( 'role' => 'teacher' ) );
	$all_user = array(
		'Teacher' => $teacher,
	);
	foreach ( $all_user as $key => $value ) {
		if ( ! empty( $value ) ) {
			echo '<optgroup label="' . esc_attr( $key ) . '" style="text-transform: capitalize;">';
			foreach ( $value as $user ) {
				$selected = ( $user->ID === $selected_id ) ? 'selected' : '';
				echo '<option value="' . esc_attr( $user->ID ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $user->display_name ) . '</option>';
			}
			echo '</optgroup>';
		}
	}
}
/**
 * Retrieves a Zoom access token using the Account Credentials OAuth method.
 *
 * This function sends a request to Zoom's OAuth endpoint to obtain an
 * access token for the configured Zoom account. It uses the client ID,
 * client secret, and account ID stored in WordPress options.
 *
 * @since 1.0.0
 *
 * @return string|false Returns the access token string if successful, or false on failure.
 */
function mjschool_get_zoom_access_token() {
	$url           = 'https://zoom.us/oauth/token';
	$CLIENT_ID     = get_option( 'mjschool_virtual_classroom_client_id' );
	$CLIENT_SECRET = get_option( 'mjschool_virtual_classroom_client_secret_id' );
	$ACCOUNT_ID    = get_option( 'mjschool_virtual_classroom_account_id' );
	if ( ! empty( $CLIENT_ID ) && ! empty( $CLIENT_SECRET ) && ! empty( $ACCOUNT_ID ) ) {
		$headers  = array(
			'Authorization' => 'Basic ' . base64_encode( $CLIENT_ID . ':' . $CLIENT_SECRET ),
			'Content-Type'  => 'application/x-www-form-urlencoded',
		);
		$body     = http_build_query(
			array(
				'grant_type' => 'account_credentials',
				'account_id' => $ACCOUNT_ID,
			)
		);
		$response = wp_remote_post(
			$url,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);
		if ( is_wp_error( $response ) ) {
			return false;
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return $body['access_token'] ?? false;
	}
}
/**
 * Generates a formatted invoice number for a given fee payment ID.
 *
 * Retrieves the `invoice_id` from the `mjschool_fees_payment` table
 * and formats it as a 5-digit invoice number prefixed with '#'.
 * If no invoice ID is found, returns 'N/A'.
 *
 * @since 1.0.0
 *
 * @param int $fees_pay_id The fee payment ID.
 *
 * @return string Formatted invoice number (e.g., '#00001') or 'N/A' if not found.
 */
function mjschool_generate_invoice_number( $fees_pay_id ) {
	global $wpdb;
	$table = $wpdb->prefix . 'mjschool_fees_payment';
	// Get the invoice_id from the table.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$invoice_id = $wpdb->get_var( $wpdb->prepare( "SELECT invoice_id FROM {$table} WHERE fees_pay_id = %d", intval( $fees_pay_id ) ) );
	// If it's empty or NULL, return 'N/A'.
	if ( empty( $invoice_id ) ) {
		return 'N/A';
	}
	// Format invoice number with 5 digits (e.g., #00001).
	$formatted_invoice_number = str_pad( $invoice_id, 5, '0', STR_PAD_LEFT );
	return '#' . $formatted_invoice_number;
}
/**
 * Generates a formatted receipt number for a given fee payment ID.
 *
 * Converts the provided ID into a 5-digit number string, prefixed with '#'.
 *
 * @since 1.0.0
 *
 * @param int $fees_pay_id The fee payment ID.
 *
 * @return string Formatted receipt number (e.g., '#00001').
 */
function mjschool_generate_receipt_number( $fees_pay_id ) {
	// Ensure it's an integer.
	$id = intval( $fees_pay_id );
	// Pad with leading zeros to make it 5 digits.
	$formatted_invoice_number = str_pad( $id, 5, '0', STR_PAD_LEFT );
	return '#' . $formatted_invoice_number;
}
/**
 * Appends a log entry to the MJSchool CSV log table.
 *
 * Records the provided error message, the creator's ID, module name,
 * status, and the current timestamp into the `mjschool_csv_log` table.
 *
 * @since 1.0.0
 *
 * @param string $log       The error or log message to store.
 * @param int    $created_by The user ID who created the log entry.
 * @param string $module    The module name associated with the log entry.
 * @param string $status    The status of the log entry (e.g., 'success', 'error').
 *
 * @return int|false Number of rows inserted on success, false on failure.
 */
function mjschool_append_csv_log( $log, $created_by, $module, $status ) {
	global $wpdb;
	$log        = sanitize_text_field( $log );
	$created_by = sanitize_text_field( $created_by );
	$module     = sanitize_text_field( $module );
	$status     = sanitize_text_field( $status );
	$mjschool_csv_log = $wpdb->prefix . 'mjschool_csv_log';
	$data               = array(
		'error_log'  => $log,
		'created_by' => intval( $created_by ),
		'created_at' => current_time( 'mysql' ),
		'module'     => $module,
		'status'     => $status,
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	return $wpdb->insert( $mjschool_csv_log, $data );
}
/**
 * Converts a scientific notation number to a plain number string.
 *
 * Detects numbers in scientific notation (e.g., 1.23e+5) and converts
 * them to a standard numeric string without decimals.
 *
 * @since 1.0.0
 *
 * @param string|float $value The value to convert.
 *
 * @return string|float Converted number as a string or original value if not in scientific notation.
 */
function mjschool_convert_scientific_to_number( $value ) {
	if ( preg_match( '/^[\d\.]+e[+\-]?\d+$/i', $value ) ) {
		return number_format( $value, 0, '', '' );
	}
	return $value;
}
/**
 * Converts a numeric value into its English words representation.
 *
 * Supports negative numbers and decimals. For example, 123.45 becomes
 * "One Hundred and Twenty-Three point Four Five".
 *
 * @since 1.0.0
 *
 * @param float|int|string $number The numeric value to convert.
 *
 * @return string|false The number in words, or false if the input is invalid or out of range.
 */
function mjschool_convert_number_to_words( $number ) {
	$hyphen      = '-';
	$conjunction = ' and ';
	$separator   = ', ';
	$negative    = 'negative ';
	$decimal     = ' point ';
	$dictionary  = array(
		0          => 'Zero',
		1          => 'One',
		2          => 'Two',
		3          => 'Three',
		4          => 'Four',
		5          => 'Five',
		6          => 'Six',
		7          => 'Seven',
		8          => 'Eight',
		9          => 'Nine',
		10         => 'Ten',
		11         => 'Eleven',
		12         => 'Twelve',
		13         => 'Thirteen',
		14         => 'Fourteen',
		15         => 'Fifteen',
		16         => 'Sixteen',
		17         => 'Seventeen',
		18         => 'Eighteen',
		19         => 'Nineteen',
		20         => 'Twenty',
		30         => 'Thirty',
		40         => 'Forty',
		50         => 'Fifty',
		60         => 'Sixty',
		70         => 'Seventy',
		80         => 'Eighty',
		90         => 'Ninety',
		100        => 'Hundred',
		1000       => 'Thousand',
		1000000    => 'Million',
		1000000000 => 'Billion',
	);
	if ( ! is_numeric( $number ) ) {
		return false;
	}
	if ( ( $number >= 0 && (int) $number < 0 ) || (int) $number < 0 - PHP_INT_MAX ) {
		// overflow.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		trigger_error( 'mjschool_convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		return false;
	}
	if ( $number < 0 ) {
		return $negative . mjschool_convert_number_to_words( abs( $number ) );
	}
	$string = $fraction = null;
	if ( strpos( $number, '.' ) !== false ) {
		list($number, $fraction) = explode( '.', $number );
	}
	switch ( true ) {
		case $number < 21:
			$string = $dictionary[ $number ];
			break;
		case $number < 100:
			$tens   = ( (int) ( $number / 10 ) ) * 10;
			$units  = $number % 10;
			$string = $dictionary[ $tens ];
			if ( $units ) {
				$string .= $hyphen . $dictionary[ $units ];
			}
			break;
		case $number < 1000:
			$hundreds  = (int) ( $number / 100 );
			$remainder = $number % 100;
			$string    = $dictionary[ $hundreds ] . ' ' . $dictionary[100];
			if ( $remainder ) {
				$string .= $conjunction . mjschool_convert_number_to_words( $remainder );
			}
			break;
		default:
			$baseUnit     = pow( 1000, floor( log( $number, 1000 ) ) );
			$numBaseUnits = (int) ( $number / $baseUnit );
			$remainder    = $number % $baseUnit;
			$string       = mjschool_convert_number_to_words( $numBaseUnits ) . ' ' . $dictionary[ $baseUnit ];
			if ( $remainder ) {
				$string .= $remainder < 100 ? $conjunction : $separator;
				$string .= mjschool_convert_number_to_words( $remainder );
			}
			break;
	}
	if ( null !== $fraction && is_numeric( $fraction ) ) {
		$string .= $decimal;
		$words   = array();
		foreach ( str_split( (string) $fraction ) as $number ) {
			$words[] = $dictionary[ $number ];
		}
		$string .= implode( ' ', $words );
	}
	return $string;
}
/**
 * Retrieves a list of currencies from the XML file included with the plugin.
 *
 * Each currency entry contains the code, name, and symbol.
 *
 * @since 1.0.0
 *
 * @return array An array of currencies. Each element is an associative array with keys 'code', 'name', and 'symbol'.
 */
function mjschool_get_currency_list() {
	
	$xml_file = plugin_dir_path( __FILE__ ) . 'assets/xml/mjschool-currencies.xml';
	
	if ( ! file_exists( $xml_file ) ) {
		return array();
	}
	$xml        = simplexml_load_file( $xml_file );
	$currencies = array();
	foreach ( $xml->currency as $currency ) {
		
		$currencies[] = array(
			'code'   => (string) $currency->code,
			'name'   => (string) $currency->name,
			'symbol' => (string) $currency->symbol,
		);
	}
	return $currencies;
}
/**
 * Generates a unique transaction ID.
 *
 * Combines a base36 encoded timestamp and a 5-character random string
 * to ensure uniqueness.
 *
 * @since 1.0.0
 *
 * @return string A unique transaction ID.
 */
function mjschool_generate_transaction_id() {
    $timestamp = base_convert(time(), 10, 36); // Encode to base36.
    $random_str = strtoupper(substr(uniqid(), -5 ) );
    return $timestamp . '-' . $random_str;
}
/**
 * Checks if a given database table exists.
 *
 * Uses the WordPress `$wpdb` object to verify if the specified table name exists.
 *
 * @since 1.0.0
 *
 * @param string $table_name The full table name including prefix.
 *
 * @return string|null Returns the table name if it exists, null otherwise.
 */
function mjschool_check_table_exist( $table_name ) {
	global $wpdb;
	// phpcs:ignore
	$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
	return $table_exists;
}
/**
 * Deletes a classroom record from the specified table.
 *
 * Also appends an audit log entry for the deletion action.
 *
 * @since 1.0.0
 *
 * @param string $tablenm Table name (without prefix) where the classroom exists.
 * @param int    $id      The classroom ID to delete.
 *
 * @return int|false Number of rows deleted or false on failure.
 */
function mjschool_delete_class_room($tablenm, $id) {
	global $wpdb;
	$tablenm = sanitize_text_field( $tablenm );
	$table_name = $wpdb->prefix . $tablenm;
	$record_id = absint($id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$event = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$table_name} where room_id=%d", $record_id ) );
	$room_name = $event->room_name;
	mjschool_append_audit_log( '' . esc_html__( 'Class Room Deleted', 'mjschool' ) . '( ' . $room_name . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_text_field(wp_unslash($_REQUEST['page'])));
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	return $result = $wpdb->query($wpdb->prepare( "DELETE FROM {$table_name} WHERE room_id= %d", $record_id ) );
}
/**
 * Retrieves a classroom record by its ID.
 *
 * @since 1.0.0
 *
 * @param int $id The classroom ID.
 *
 * @return object|null Classroom row object if found, otherwise null.
 */
function mjschool_get_class_room_by_id($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mjschool_class_room";
	$sid = absint($id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$retrieve_room = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$table_name} WHERE room_id=%d", $sid ) );
	return $retrieve_room;
}
/**
 * Retrieves the name of a classroom by its ID.
 *
 * @since 1.0.0
 *
 * @param int $id The classroom ID.
 *
 * @return object|null Object containing the room name if found, otherwise null.
 */
function mjschool_get_class_room_name($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mjschool_class_room";
	$sid = absint($id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$retrieve_room = $wpdb->get_row($wpdb->prepare( "SELECT room_name FROM {$table_name} WHERE room_id=%d", $sid ) );
	return $retrieve_room;
}
/**
 * Retrieves the class name, optionally considering its associated event categories.
 *
 * If the class has associated events, the function can consider categories.
 *
 * @since 1.0.0
 *
 * @param int $class_id The class ID.
 *
 * @return string The class name, possibly combined with categories.
 */
function mjschool_get_class_name_category_wise($class_id) {
	$class_id   = absint( $class_id );
	$class_data = mjschool_get_class_by_id($class_id);
	$event_categories = array();
	$class_cat_name = '';
	if( ! empty( $class_data ) )
	{
		if( ! empty( $class_data->event_id ) )
		{
			$event_id = $class_data->event_id;
			$terms = get_the_terms($event_id, 'tribe_events_cat' );
			if ( ! empty( $terms) && !is_wp_error($terms ) ) {
				foreach ($terms as $term) {
					$event_categories[] = $term->name; // Store categories per event.
				}
			}
		}
		if( ! empty( $event_categories ) )
		{
			$categories = implode( ', ', $event_categories);

			$class_cat_name = $class_data->class_name;
		}
		else
		{
			$class_cat_name = $class_data->class_name;
		}
	}
	return $class_cat_name;
}
/**
 * Retrieves classrooms assigned to a specific class ID.
 *
 * Uses JSON_CONTAINS to check the class_id array in the database.
 *
 * @since 1.0.0
 *
 * @param int $class_id The class ID.
 *
 * @return array List of classroom objects assigned to the class.
 */
function mjschool_get_assign_class_room_for_single_class($class_id) {
	global $wpdb;
	$class_id   = absint( $class_id );
	$table_name = $wpdb->prefix . "mjschool_class_room";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE JSON_CONTAINS(class_id, %s, '$' )", json_encode($class_id) ) );
	return $results;
}
/**
 * Retrieves subjects for the current user assigned to a specific class.
 *
 * @since 1.0.0
 *
 * @param int $id The class ID.
 *
 * @return array List of subject objects.
 */
function mjschool_subject_list_univercity($id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$class_id = absint($id);
	$current_user_id = get_current_user_id();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND FIND_IN_SET(%d, selected_students)", $class_id, $current_user_id ) );
	return $result;
}
/**
 * Retrieves subjects for a specific child assigned to a class.
 *
 * @since 1.0.0
 *
 * @param int $id       The class ID.
 * @param int $child_id The child/student ID.
 *
 * @return array List of subject objects.
 */
function mjschool_subject_list_univercity_parents($id,$child_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$class_id = absint($id);
	$current_user_id = absint($child_id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND FIND_IN_SET(%d, selected_students)", $class_id, $current_user_id ) );
	return $result;
}
/**
 * Retrieves subjects assigned to a specific child in a specific class.
 *
 * @since 1.0.0
 *
 * @param int $class_id   The class ID.
 * @param int $student_id The student ID.
 *
 * @return array List of subject objects.
 */
function mjschool_subject_list_univercity_for_child($class_id, $student_id) {
	global $wpdb;
	$table_name = $wpdb->prefix . 'mjschool_subject';
	$class_id = absint($class_id);
	$student_id = absint($student_id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND FIND_IN_SET(%d, selected_students)", $class_id, $student_id ) );
	return $result;
}
/**
 * Retrieves all users (students) assigned to a specific class.
 *
 * @since 1.0.0
 *
 * @param int $class_id The class ID.
 *
 * @return array List of WP_User objects for students in the class.
 */
function mjschool_get_users_by_class_id($class_id) {
	$class_id = absint($class_id);
    $args = array(
        'role' => 'student',
        'meta_key' => 'class_name',
        'meta_value' => $class_id,
        'number' => -1
    );

    return get_users($args);
}
/**
 * Retrieves students assigned to a specific subject.
 *
 * @since 1.0.0
 *
 * @param int $subid The subject ID.
 *
 * @return array List of WP_User objects for students assigned to the subject.
 */
function mjschool_get_students_assigned_to_subject($subid) {
	global $wpdb;
	$subid = absint($subid);
	$table_name = $wpdb->prefix . 'mjschool_subject';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$subject = $wpdb->get_row( $wpdb->prepare( "SELECT selected_students FROM $table_name WHERE subid = %d", $subid) );
	if (!$subject || empty($subject->selected_students ) ) {
		return array();
	}
	$student_ids = explode( ',', $subject->selected_students);
	$args = array(
		'include' => $student_ids,
		'role'    => 'student',
		'orderby' => 'display_name',
		'order'   => 'ASC'
	);
	return get_users($args);
}
/**
 * Retrieves all classroom records matching an array of class IDs or created by the current user.
 *
 * @since 1.0.0
 *
 * @param array $class_id_array Array of class IDs.
 *
 * @return array List of classroom objects.
 */
function mjschool_get_all_class_data_by_class_room_array($class_id_array) {
	global $wpdb;
	$user_id = get_current_user_id();
	$table_name = $wpdb->prefix . "mjschool_class_room";

	$like_conditions = array();

	foreach ((array) $class_id_array as $class_id) {
		$class_id = esc_sql($class_id);
		$like_conditions[] = "class_id LIKE '%\"$class_id\"%'";
	}

	$like_sql = implode( " OR ", $like_conditions);

	// Build full SQL manually with user ID inserted.
	$query = "SELECT * FROM {$table_name} WHERE ($like_sql) OR created_by = $user_id";
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	return $wpdb->get_results($query);
}
/**
 * Retrieves all rows from a specified table created by the current user.
 *
 * This function fetches records from a whitelisted table in the database
 * where the `created_by` column matches the currently logged-in user.
 * Only certain tables are allowed to be queried for security reasons.
 *
 * @since 1.0.0
 *
 * @param string $tablename Name of the table (without prefix) to query.
 *
 * @return array Returns an array of row objects created by the current user.
 */
function mjschool_get_user_own_data($tablename)
{
	global $wpdb;
	$user_id = get_current_user_id();
	$tablename = sanitize_text_field( $tablename );

	// Whitelist of allowed tables (without prefix).
	$allowed_tables = ['subjects', 'classes', 'students', 'mjschool_class_room']; // Add your actual table names.

	if (!in_array($tablename, $allowed_tables ) ) {
		return []; // Or return WP_Error or false.
	}

	$table_name = $wpdb->prefix . $tablename;

	// Safe query using prepare.
	$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE created_by = %d", $user_id);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	return $wpdb->get_results($query);
}
// add_action( 'init', 'mjschool_migrate_options' );
/**
 * Migrates old SMGT plugin database tables and options to the new MJSchool format.
 *
 * This function performs the following tasks:
 * 1. Renames existing database tables with the SMGT prefix to MJSchool tables.
 * 2. Migrates old WordPress options from SMGT keys to MJSchool keys.
 * 3. Skips migration for fresh installs where old SMGT options do not exist.
 *
 * It checks if each old table exists before renaming and ensures that option values
 * are safely copied from old keys to new keys without overwriting existing MJSchool options.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database global object.
 *
 * @return void This function does not return any value.
 */
function mjschool_migrate_options() {
	global $wpdb;
	// 1) assign_transport.
	$old_assign_transport = $wpdb->prefix . 'assign_transport';
	$new_assign_transport = $wpdb->prefix . 'mjschool_assign_transport';
	// Check if the old table exists.
	$table_assign_transport_exists = mjschool_check_table_exist( $old_assign_transport );
	if ( $table_assign_transport_exists === $old_assign_transport ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_assign_transport` TO `$new_assign_transport`");
	}
	// 2 ) attendence.
	$old_attendence = $wpdb->prefix . 'attendence';
	$new_attendence = $wpdb->prefix . 'mjschool_attendence';
	// Check if the old table exists.
	$table_old_attendence_exists = mjschool_check_table_exist( $old_attendence );
	if ( $table_old_attendence_exists === $old_attendence ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_attendence` TO `$new_attendence`");
	}
	// 3) certificate.
	$old_certificate = $wpdb->prefix . 'certificate';
	$new_certificate = $wpdb->prefix . 'mjschool_certificate';
	// Check if the old table exists.
	$table_old_certificate_exists = mjschool_check_table_exist( $old_certificate );
	if ( $table_old_certificate_exists === $old_certificate ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_certificate` TO `$new_certificate`");
	}
	// 4) custom_field.
	$old_custom_field = $wpdb->prefix . 'custom_field';
	$new_custom_field = $wpdb->prefix . 'mjschool_custom_field';
	// Check if the old table exists.
	$table_old_custom_field_exists = mjschool_check_table_exist( $old_custom_field );
	if ( $table_old_custom_field_exists === $old_custom_field ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_custom_field` TO `$new_custom_field`");
	}
	// 5) custom_field_dropdown_metas.
	$old_custom_field_dropdown_metas = $wpdb->prefix . 'custom_field_dropdown_metas';
	$new_custom_field_dropdown_metas = $wpdb->prefix . 'mjschool_custom_field_dropdown_metas';
	// Check if the old table exists.
	$table_old_custom_field_dropdown_metas_exists = mjschool_check_table_exist( $old_custom_field_dropdown_metas );
	if ( $table_old_custom_field_dropdown_metas_exists === $old_custom_field_dropdown_metas ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_custom_field_dropdown_metas` TO `$new_custom_field_dropdown_metas`");
	}
	// 6) custom_field_metas.
	$old_custom_field_metas = $wpdb->prefix . 'custom_field_metas';
	$new_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
	// Check if the old table exists.
	$table_old_custom_field_metas_exists = mjschool_check_table_exist( $old_custom_field_metas );
	if ( $table_old_custom_field_metas_exists === $old_custom_field_metas ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_custom_field_metas` TO `$new_custom_field_metas`");
	}
	// 8) daynamic_certificate.
	$old_daynamic_certificate = $wpdb->prefix . 'daynamic_certificate';
	$new_daynamic_certificate = $wpdb->prefix . 'mjschool_daynamic_certificate';
	// Check if the old table exists.
	$table_old_daynamic_certificate_exists = mjschool_check_table_exist( $old_daynamic_certificate );
	if ( $table_old_daynamic_certificate_exists === $old_daynamic_certificate ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_daynamic_certificate` TO `$new_daynamic_certificate`");
	}
	// 9) event.
	$old_event = $wpdb->prefix . 'event';
	$new_event = $wpdb->prefix . 'mjschool_event';
	// Check if the old table exists.
	$table_old_event_exists = mjschool_check_table_exist( $old_event );
	if ( $table_old_event_exists === $old_event ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_event` TO `$new_event`");
	}
	// 10) exam.
	$old_exam = $wpdb->prefix . 'exam';
	$new_exam = $wpdb->prefix . 'mjschool_exam';
	// Check if the old table exists.
	$table_old_exam_exists = mjschool_check_table_exist( $old_exam );
	if ( $table_old_exam_exists === $old_exam ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_exam` TO `$new_exam`");
	}
	// 11) exam_merge_settings.
	$old_exam_merge_settings = $wpdb->prefix . 'exam_merge_settings';
	$new_exam_merge_settings = $wpdb->prefix . 'mjschool_exam_merge_settings';
	// Check if the old table exists.
	$table_old_exam_merge_settings_exists = mjschool_check_table_exist( $old_exam_merge_settings );
	if ( $table_old_exam_merge_settings_exists === $old_exam_merge_settings ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_exam_merge_settings` TO `$new_exam_merge_settings`");
	}
	// 12 ) grade.
	$old_grade = $wpdb->prefix . 'grade';
	$new_grade = $wpdb->prefix . 'mjschool_grade';
	// Check if the old table exists.
	$table_old_grade_exists = mjschool_check_table_exist( $old_grade );
	if ( $table_old_grade_exists === $old_grade ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_grade` TO `$new_grade`");
	}
	// 13) hall.
	$old_hall = $wpdb->prefix . 'hall';
	$new_hall = $wpdb->prefix . 'mjschool_hall';
	// Check if the old table exists.
	$table_old_hall_exists = mjschool_check_table_exist( $old_hall );
	if ( $table_old_hall_exists === $old_hall ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_hall` TO `$new_hall`");
	}
	// 14) holiday.
	$old_holiday = $wpdb->prefix . 'holiday';
	$new_holiday = $wpdb->prefix . 'mjschool_holiday';
	// Check if the old table exists.
	$table_old_holiday_exists = mjschool_check_table_exist( $old_holiday );
	if ( $table_old_holiday_exists === $old_holiday ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_holiday` TO `$new_holiday`");
	}
	// 15) marks.
	$old_marks = $wpdb->prefix . 'marks';
	$new_marks = $wpdb->prefix . 'mjschool_marks';
	// Check if the old table exists.
	$table_old_marks_exists = mjschool_check_table_exist( $old_marks );
	if ( $table_old_marks_exists === $old_marks ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_marks` TO `$new_marks`");
	}
	// 16) smgt_homework.
	$old_mjschool_homework = $wpdb->prefix . 'mj_smgt_homework';
	$new_mjschool_homework = $wpdb->prefix . 'mjschool_homework';
	// Check if the old table exists.
	$table_old_mjschool_homework_exists = mjschool_check_table_exist( $old_mjschool_homework );
	if ( $table_old_mjschool_homework_exists === $old_mjschool_homework ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_homework` TO `$new_mjschool_homework`");
	}

	// 17) smgt_student_homework.
	$old_mjschool_student_homework = $wpdb->prefix . 'mj_smgt_student_homework';
	$new_mjschool_student_homework = $wpdb->prefix . 'mjschool_student_homework';
	// Check if the old table exists.
	$table_old_mjschool_student_homework_exists = mjschool_check_table_exist( $old_mjschool_student_homework );
	if ( $table_old_mjschool_student_homework_exists === $old_mjschool_student_homework ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_student_homework` TO `$new_mjschool_student_homework`");
	}
	// 18) smgt_taxes.
	$old_mjschool_taxes = $wpdb->prefix . 'mj_smgt_taxes';
	$new_mjschool_taxes = $wpdb->prefix . 'mjschool_taxes';
	// Check if the old table exists.
	$table_old_mjschool_taxes_exists = mjschool_check_table_exist( $old_mjschool_taxes );
	if ( $table_old_mjschool_taxes_exists === $old_mjschool_taxes ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_taxes` TO `$new_mjschool_taxes`");
	}
	// 19) smgt_assign_beds.
	$old_mjschool_assign_beds = $wpdb->prefix . 'smgt_assign_beds';
	$new_mjschool_assign_beds = $wpdb->prefix . 'mjschool_assign_beds';
	// Check if the old table exists.
	$table_old_mjschool_assign_beds_exists = mjschool_check_table_exist( $old_mjschool_assign_beds );
	if ( $table_old_mjschool_assign_beds_exists === $old_mjschool_assign_beds ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_assign_beds` TO `$new_mjschool_assign_beds`");
	}
	// 20) smgt_audit_log.
	$old_mjschool_audit_log = $wpdb->prefix . 'smgt_audit_log';
	$new_mjschool_audit_log = $wpdb->prefix . 'mjschool_audit_log';
	// Check if the old table exists.
	$table_old_mjschool_audit_log_exists = mjschool_check_table_exist( $old_mjschool_audit_log );
	if ( $table_old_mjschool_audit_log_exists === $old_mjschool_audit_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_audit_log` TO `$new_mjschool_audit_log`");
	}
	// 21) smgt_beds.
	$old_mjschool_beds = $wpdb->prefix . 'smgt_beds';
	$new_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
	// Check if the old table exists.
	$table_old_mjschool_beds_exists = mjschool_check_table_exist( $old_mjschool_beds );
	if ( $table_old_mjschool_beds_exists === $old_mjschool_beds ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_beds` TO `$new_mjschool_beds`");
	}
	// 22 ) smgt_check_status.
	$old_mjschool_check_status = $wpdb->prefix . 'smgt_check_status';
	$new_mjschool_check_status = $wpdb->prefix . 'mjschool_check_status';
	// Check if the old table exists.
	$table_old_mjschool_check_status_exists = mjschool_check_table_exist( $old_mjschool_check_status );
	if ( $table_old_mjschool_check_status_exists === $old_mjschool_check_status ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_check_status` TO `$new_mjschool_check_status`");
	}
	// 22 ) smgt_class.
	$old_mjschool_class = $wpdb->prefix . 'smgt_class';
	$new_mjschool_class = $wpdb->prefix . 'mjschool_class';
	// Check if the old table exists.
	$table_old_mjschool_class_exists = mjschool_check_table_exist( $old_mjschool_class );
	if ( $table_old_mjschool_class_exists === $old_mjschool_class ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_class` TO `$new_mjschool_class`");
	}
	// 23) smgt_class_section.
	$old_mjschool_class_section = $wpdb->prefix . 'smgt_class_section';
	$new_mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
	// Check if the old table exists.
	$table_old_mjschool_class_section_exists = mjschool_check_table_exist( $old_mjschool_class_section );
	if ( $table_old_mjschool_class_section_exists === $old_mjschool_class_section ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_class_section` TO `$new_mjschool_class_section`");
	}
	// 24) smgt_cron_reminder_log.
	$old_mjschool_cron_reminder_log = $wpdb->prefix . 'smgt_cron_reminder_log';
	$new_mjschool_cron_reminder_log = $wpdb->prefix . 'mjschool_cron_reminder_log';
	// Check if the old table exists.
	$table_old_mjschool_cron_reminder_log_exists = mjschool_check_table_exist( $old_mjschool_cron_reminder_log );
	if ( $table_old_mjschool_cron_reminder_log_exists === $old_mjschool_cron_reminder_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_cron_reminder_log` TO `$new_mjschool_cron_reminder_log`");
	}
	// 25) smgt_csv_log.
	$old_mjschool_csv_log = $wpdb->prefix . 'smgt_csv_log';
	$new_mjschool_csv_log = $wpdb->prefix . 'mjschool_csv_log';
	// Check if the old table exists.
	$table_old_mjschool_csv_log_exists = mjschool_check_table_exist( $old_mjschool_csv_log );
	if ( $table_old_mjschool_csv_log_exists === $old_mjschool_csv_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_csv_log` TO `$new_mjschool_csv_log`");
	}
	// 26) smgt_document.
	$old_mjschool_document = $wpdb->prefix . 'smgt_document';
	$new_mjschool_document = $wpdb->prefix . 'mjschool_document';
	// Check if the old table exists.
	$table_old_mjschool_document_exists = mjschool_check_table_exist( $old_mjschool_document );
	if ( $table_old_mjschool_document_exists === $old_mjschool_document ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_document` TO `$new_mjschool_document`");
	}
	// 27) smgt_exam_hall_receipt.
	$old_mjschool_exam_hall_receipt = $wpdb->prefix . 'smgt_exam_hall_receipt';
	$new_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
	// Check if the old table exists.
	$table_old_mjschool_exam_hall_receipt_exists = mjschool_check_table_exist( $old_mjschool_exam_hall_receipt );
	if ( $table_old_mjschool_exam_hall_receipt_exists === $old_mjschool_exam_hall_receipt ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_exam_hall_receipt` TO `$new_mjschool_exam_hall_receipt`");
	}
	// 28) smgt_exam_time_table.
	$old_mjschool_exam_time_table = $wpdb->prefix . 'smgt_exam_time_table';
	$new_mjschool_exam_time_table = $wpdb->prefix . 'mjschool_exam_time_table';
	// Check if the old table exists.
	$table_old_mjschool_exam_time_table_exists = mjschool_check_table_exist( $old_mjschool_exam_time_table );
	if ( $table_old_mjschool_exam_time_table_exists === $old_mjschool_exam_time_table ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_exam_time_table` TO `$new_mjschool_exam_time_table`");
	}
	// 28) smgt_fees.
	$old_mjschool_fees = $wpdb->prefix . 'smgt_fees';
	$new_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
	// Check if the old table exists.
	$table_old_mjschool_fees_exists = mjschool_check_table_exist( $old_mjschool_fees );
	if ( $table_old_mjschool_fees_exists === $old_mjschool_fees ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_fees` TO `$new_mjschool_fees`");
	}
	// 29) smgt_fees_payment.
	$old_mjschool_fees_payment = $wpdb->prefix . 'smgt_fees_payment';
	$new_mjschool_fees_payment = $wpdb->prefix . 'mjschool_fees_payment';
	// Check if the old table exists.
	$table_old_mjschool_fees_payment_exists = mjschool_check_table_exist( $old_mjschool_fees_payment );
	if ( $table_old_mjschool_fees_payment_exists === $old_mjschool_fees_payment ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_fees_payment` TO `$new_mjschool_fees_payment`");
	}
	// 30) smgt_fees_payment_recurring.
	$old_mjschool_fees_payment_recurring = $wpdb->prefix . 'smgt_fees_payment_recurring';
	$new_mjschool_fees_payment_recurring = $wpdb->prefix . 'mjschool_fees_payment_recurring';
	// Check if the old table exists.
	$table_old_mjschool_fees_payment_recurring_exists = mjschool_check_table_exist( $old_mjschool_fees_payment_recurring );
	if ( $table_old_mjschool_fees_payment_recurring_exists === $old_mjschool_fees_payment_recurring ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_fees_payment_recurring` TO `$new_mjschool_fees_payment_recurring`");
	}
	// 31) smgt_fee_payment_history.
	$old_mjschool_fee_payment_history = $wpdb->prefix . 'smgt_fee_payment_history';
	$new_mjschool_fee_payment_history = $wpdb->prefix . 'mjschool_fee_payment_history';
	// Check if the old table exists.
	$table_old_mjschool_fee_payment_history_exists = mjschool_check_table_exist( $old_mjschool_fee_payment_history );
	if ( $table_old_mjschool_fee_payment_history_exists === $old_mjschool_fee_payment_history ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_fee_payment_history` TO `$new_mjschool_fee_payment_history`");
	}
	// 32 ) smgt_hostel.
	$old_mjschool_hostel = $wpdb->prefix . 'smgt_hostel';
	$new_mjschool_hostel = $wpdb->prefix . 'mjschool_hostel';
	// Check if the old table exists.
	$table_old_mjschool_hostel_exists = mjschool_check_table_exist( $old_mjschool_hostel );
	if ( $table_old_mjschool_hostel_exists === $old_mjschool_hostel ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_hostel` TO `$new_mjschool_hostel`");
	}
	// 33) smgt_income_expense.
	$old_mjschool_income_expense = $wpdb->prefix . 'smgt_income_expense';
	$new_mjschool_income_expense = $wpdb->prefix . 'mjschool_income_expense';
	// Check if the old table exists.
	$table_old_mjschool_income_expense_exists = mjschool_check_table_exist( $old_mjschool_income_expense );
	if ( $table_old_mjschool_income_expense_exists === $old_mjschool_income_expense ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_income_expense` TO `$new_mjschool_income_expense`");
	}
	// 34) smgt_leave.
	$old_mjschool_leave = $wpdb->prefix . 'smgt_leave';
	$new_mjschool_leave = $wpdb->prefix . 'mjschool_leave';
	// Check if the old table exists.
	$table_old_mjschool_leave_exists = mjschool_check_table_exist( $old_mjschool_leave );
	if ( $table_old_mjschool_leave_exists === $old_mjschool_leave ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_leave` TO `$new_mjschool_leave`");
	}
	// 35) smgt_library_book.
	$old_mjschool_library_book = $wpdb->prefix . 'smgt_library_book';
	$new_mjschool_library_book = $wpdb->prefix . 'mjschool_library_book';
	// Check if the old table exists.
	$table_old_mjschool_library_book_exists = mjschool_check_table_exist( $old_mjschool_library_book );
	if ( $table_old_mjschool_library_book_exists === $old_mjschool_library_book ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_library_book` TO `$new_mjschool_library_book`");
	}
	// 36) smgt_library_book_issue.
	$old_mjschool_library_book_issue = $wpdb->prefix . 'smgt_library_book_issue';
	$new_mjschool_library_book_issue = $wpdb->prefix . 'mjschool_library_book_issue';
	// Check if the old table exists.
	$table_old_mjschool_library_book_issue_exists = mjschool_check_table_exist( $old_mjschool_library_book_issue );
	if ( $table_old_mjschool_library_book_issue_exists === $old_mjschool_library_book_issue ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_library_book_issue` TO `$new_mjschool_library_book_issue`");
	}
	// 37) smgt_message.
	$old_mjschool_message = $wpdb->prefix . 'smgt_message';
	$new_mjschool_message = $wpdb->prefix . 'mjschool_message';
	// Check if the old table exists.
	$table_old_mjschool_message_exists = mjschool_check_table_exist( $old_mjschool_message );
	if ( $table_old_mjschool_message_exists === $old_mjschool_message ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_message` TO `$new_mjschool_message`");
	}
	// 38) smgt_message_replies.
	$old_mjschool_message_replies = $wpdb->prefix . 'smgt_message_replies';
	$new_mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
	// Check if the old table exists.
	$table_old_mjschool_message_replies_exists = mjschool_check_table_exist( $old_mjschool_message_replies );
	if ( $table_old_mjschool_message_replies_exists === $old_mjschool_message_replies ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_message_replies` TO `$new_mjschool_message_replies`");
	}
	// 39) smgt_migration_log.
	$old_mjschool_migration_log = $wpdb->prefix . 'smgt_migration_log';
	$new_mjschool_migration_log = $wpdb->prefix . 'mjschool_migration_log';
	// Check if the old table exists.
	$table_old_mjschool_migration_log_exists = mjschool_check_table_exist( $old_mjschool_migration_log );
	if ( $table_old_mjschool_migration_log_exists === $old_mjschool_migration_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_migration_log` TO `$new_mjschool_migration_log`");
	}
	// 40) smgt_notification.
	$old_mjschool_notification = $wpdb->prefix . 'smgt_notification';
	$new_mjschool_notification = $wpdb->prefix . 'mjschool_notification';
	// Check if the old table exists.
	$table_old_mjschool_notification_exists = mjschool_check_table_exist( $old_mjschool_notification );
	if ( $table_old_mjschool_notification_exists === $old_mjschool_notification ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_notification` TO `$new_mjschool_notification`");
	}
	// 41) smgt_payment.
	$old_mjschool_payment = $wpdb->prefix . 'smgt_payment';
	$new_mjschool_payment = $wpdb->prefix . 'mjschool_payment';
	// Check if the old table exists.
	$table_old_mjschool_payment_exists = mjschool_check_table_exist( $old_mjschool_payment );
	if ( $table_old_mjschool_payment_exists === $old_mjschool_payment ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_payment` TO `$new_mjschool_payment`");
	}
	// 42 ) smgt_reminder_zoom_meeting_mail_log.
	$old_mjschool_reminder_zoom_meeting_mail_log = $wpdb->prefix . 'smgt_reminder_zoom_meeting_mail_log';
	$new_mjschool_reminder_zoom_meeting_mail_log = $wpdb->prefix . 'mjschool_reminder_zoom_meeting_mail_log';
	// Check if the old table exists.
	$table_old_mjschool_reminder_zoom_meeting_mail_log_exists = mjschool_check_table_exist( $old_mjschool_reminder_zoom_meeting_mail_log );
	if ( $table_old_mjschool_reminder_zoom_meeting_mail_log_exists === $old_mjschool_reminder_zoom_meeting_mail_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_reminder_zoom_meeting_mail_log` TO `$new_mjschool_reminder_zoom_meeting_mail_log`");
	}
	// 43) smgt_room.
	$old_mjschool_room = $wpdb->prefix . 'smgt_room';
	$new_mjschool_room = $wpdb->prefix . 'mjschool_room';
	// Check if the old table exists.
	$table_old_mjschool_room_exists = mjschool_check_table_exist( $old_mjschool_room );
	if ( $table_old_mjschool_room_exists === $old_mjschool_room ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_room` TO `$new_mjschool_room`");
	}
	// 44) smgt_sub_attendance.
	$old_mjschool_sub_attendance = $wpdb->prefix . 'smgt_sub_attendance';
	$new_mjschool_sub_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
	// Check if the old table exists.
	$table_old_mjschool_sub_attendance_exists = mjschool_check_table_exist( $old_mjschool_sub_attendance );
	if ( $table_old_mjschool_sub_attendance_exists === $old_mjschool_sub_attendance ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_sub_attendance` TO `$new_mjschool_sub_attendance`");
	}
	// 45) smgt_teacher_class.
	$old_mjschool_teacher_class = $wpdb->prefix . 'smgt_teacher_class';
	$new_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
	// Check if the old table exists.
	$table_old_mjschool_teacher_class_exists = mjschool_check_table_exist( $old_mjschool_teacher_class );
	if ( $table_old_mjschool_teacher_class_exists === $old_mjschool_teacher_class ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_teacher_class` TO `$new_mjschool_teacher_class`");
	}
	// 46) smgt_time_table.
	$old_mjschool_time_table = $wpdb->prefix . 'smgt_time_table';
	$new_mjschool_time_table = $wpdb->prefix . 'mjschool_time_table';
	// Check if the old table exists.
	$table_old_mjschool_time_table_exists = mjschool_check_table_exist( $old_mjschool_time_table );
	if ( $table_old_mjschool_time_table_exists === $old_mjschool_time_table ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_time_table` TO `$new_mjschool_time_table`");
	}
	// 47) smgt_user_log.
	$old_mjschool_user_log = $wpdb->prefix . 'smgt_user_log';
	$new_mjschool_user_log = $wpdb->prefix . 'mjschool_user_log';
	// Check if the old table exists.
	$table_old_mjschool_user_log_exists = mjschool_check_table_exist( $old_mjschool_user_log );
	if ( $table_old_mjschool_user_log_exists === $old_mjschool_user_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_user_log` TO `$new_mjschool_user_log`");
	}
	// 48) smgt_zoom_meeting.
	$old_mjschool_zoom_meeting = $wpdb->prefix . 'smgt_zoom_meeting';
	$new_mjschool_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
	// Check if the old table exists.
	$table_old_mjschool_zoom_meeting_exists = mjschool_check_table_exist( $old_mjschool_zoom_meeting );
	if ( $table_old_mjschool_zoom_meeting_exists === $old_mjschool_zoom_meeting ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_mjschool_zoom_meeting` TO `$new_mjschool_zoom_meeting`");
	}
	// 49) subject.
	$old_subject = $wpdb->prefix . 'subject';
	$new_subject = $wpdb->prefix . 'mjschool_subject';
	// Check if the old table exists.
	$table_old_subject_exists = mjschool_check_table_exist( $old_subject );
	if ( $table_old_subject_exists === $old_subject ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_subject` TO `$new_subject`");
	}
	// 50) teacher_subject.
	$old_teacher_subject = $wpdb->prefix . 'teacher_subject';
	$new_teacher_subject = $wpdb->prefix . 'mjschool_teacher_subject';
	// Check if the old table exists.
	$table_old_teacher_subject_exists = mjschool_check_table_exist( $old_teacher_subject );
	if ( $table_old_teacher_subject_exists === $old_teacher_subject ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_teacher_subject` TO `$new_teacher_subject`");
	}
	// 51) transport.
	$old_transport = $wpdb->prefix . 'transport';
	$new_transport = $wpdb->prefix . 'mjschool_transport';
	// Check if the old table exists.
	$table_old_transport_exists = mjschool_check_table_exist( $old_transport );
	if ( $table_old_transport_exists === $old_transport ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_transport` TO `$new_transport`");
	}
	// 52 ) smgt_beds.
	$old_beds = $wpdb->prefix . 'smgt_beds';
	$new_beds = $wpdb->prefix . 'mjschool_beds';
	// Check if the old table exists.
	$table_old_beds_exists = mjschool_check_table_exist( $old_beds );
	if ( $table_old_beds_exists === $old_beds ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_beds` TO `$new_beds`");
	}
	// 53) smgt_hostel.
	$old_hostel = $wpdb->prefix . 'smgt_hostel';
	$new_hostel = $wpdb->prefix . 'mjschool_hostel';
	// Check if the old table exists.
	$table_old_hostel_exists = mjschool_check_table_exist( $old_hostel );
	if ( $table_old_hostel_exists === $old_hostel ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_hostel` TO `$new_hostel`");
	}
	// 54) cron_reminder_log.
	$old_cron_reminder_log = $wpdb->prefix . 'cron_reminder_log';
	$new_cron_reminder_log = $wpdb->prefix . 'mjschool_cron_reminder_log';
	// Check if the old table exists
	$table_old_cron_reminder_log_exists = mjschool_check_table_exist( $old_cron_reminder_log );
	if ( $table_old_cron_reminder_log_exists === $old_cron_reminder_log ) {
		// phpcs:ignore
		$wpdb->query( "RENAME TABLE `$old_cron_reminder_log` TO `$new_cron_reminder_log`");
	}
	$is_old_user = get_option( 'smgt_school_name' ) !== false;

    // If new user (fresh install), skip migration.
    if (!$is_old_user) {
        update_option( 'mjschool_migrate_options_check', '1' );
        return;
    }
	$smgt_to_mjschool_keys = array(
		'mjschool_name'                                   => 'smgt_school_name',
		'mjschool_login_page'                             => 'smgt_login_page',
		'mjschool_staring_year'                           => 'smgt_staring_year',
		'mjschool_address'                         		  => 'smgt_school_address',
		'mjschool_contact_number'                         => 'smgt_contact_number',
		'mjschool_combine'                                => 'smgt_combine',
		'mjschool_contry'                                 => 'smgt_contry',
		'mjschool_city'                                   => 'smgt_city',
		'mjschool_past_pay'                               => 'smgt_past_pay',
		'mjschool_prefix'                                 => 'smgt_prefix',
		'mjschool_email'                                  => 'smgt_email',
		'mjschool_datepicker_format'                      => 'smgt_datepicker_format',
		'mjschool_app_logo'                        	      => 'smgt_school_app_logo',
		'mjschool_logo'                            		  => 'smgt_school_logo',
		'mjschool_system_logo'                            => 'smgt_system_logo',
		'mjschool_background_image'                		  => 'smgt_school_background_image',
		'mjschool_student_thumb'                          => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
		'mjschool_mjschool-no-data-img'                   => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-plus-icon.png' ),
		'mjschool_parent_thumb'                           => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
		'mjschool_teacher_thumb'                          => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
		'mjschool_supportstaff_thumb'                     => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
		'mjschool_driver_thumb'                           => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
		'mjschool_principal_signature'                    => 'mjschool_principal_signature',
		'mjschool_student_thumb_new'                      => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-student.png' ),
		'mjschool_parent_thumb_new'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-parents.png' ),
		'mjschool_teacher_thumb_new'                      => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-teacher.png' ),
		'mjschool_supportstaff_thumb_new'                 => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-support-staff.png' ),
		'mjschool_driver_thumb_new'                       => plugins_url( 'mjschool/assets/images/thumb-icon/mjschool-transport.png' ),
		'mjschool_footer_description'                     => 'smgt_footer_description',
		'mjschool_access_right_student'                   => 'smgt_access_right_student',
		'mjschool_access_right_teacher'                   => 'smgt_access_right_teacher',
		'mjschool_access_right_parent'                    => 'smgt_access_right_parent',
		'mjschool_access_right_supportstaff'              => 'smgt_access_right_supportstaff',
		'mjschool_access_right_management'                => 'smgt_access_right_management',
		'mjschool_dashboard_card_for_student'             => 'smgt_dashboard_card_for_student',
		'mjschool_dashboard_card_for_teacher'             => 'smgt_dashboard_card_for_teacher',
		'mjschool_dashboard_card_for_support_staff'       => 'smgt_dashboard_card_for_support_staff',
		'mjschool_dashboard_card_for_parent'              => 'smgt_dashboard_card_for_parent',
		'mjschool_service'                       		  => 'smgt_mjschool_service',
		// PAY MASTER OPTION.//
		'mjschool_paymaster_pack'                         => 'no',
		'mjschool_invoice_option'                         => 1,
		'mjschool_mail_notification'                      => 1,
		'mjschool_notification_fcm_key'                   => '',
		'mjschool_service_enable'                         => 0,
		'mjschool_student_approval'                       => 1,
		'mjschool_sms_template'                               => 'Hello [mjschool_USER_NAME] ',
		'mjschool_clickatell_mjschool_service'            => array(),
		'mjschool_twillo_mjschool_service'                => array(),
		'mjschool_parent_send_message'                    => 1,
		'mjschool_enable_total_student'                   => 1,
		'mjschool_enable_total_teacher'                   => 1,
		'mjschool_enable_total_parent'                    => 1,
		'mjschool_enable_homework_mail'                   => 0,
		'mjschool_enable_total_attendance'                => 1,
		'mjschool_enable_sandbox'                         => 'yes',
		'mjschool_virtual_classroom_account_id'           => '',
		'mjschool_virtual_classroom_client_id'            => '',
		'mjschool_virtual_classroom_client_secret_id'     => '',
		'mjschool_virtual_classroom_access_token'         => '',
		'mjschool_enable_virtual_classroom'               => 'no',
		'mjschool_return_option'                          => 'yes',
		'mjschool_return_period'                          => 3,
		'mjschool_system_payment_reminder_day'            => 3,
		'mjschool_system_payment_reminder_enable'         => 'no',
		'mjschool_paypal_email'                           => 'smgt_paypal_email',
		'razorpay__key'                                   => 'razorpay__key',
		'razorpay_secret_mid'                             => 'razorpay_secret_mid',
		'mjschool_currency_code'                          => 'smgt_currency_code',
		'mjschool_teacher_manage_allsubjects_marks'       => 'smgt_teacher_manage_allsubjects_marks',
		'mjschool_enable_video_popup_show'                => 'smgt_enable_video_popup_show',
		'mjschool_registration_title'                     => 'registration_title',
		'mjschool_student_activation_title'               => 'student_activation_title',
		'mjschool_fee_payment_title'                      => 'fee_payment_title',
		'mjschool_fee_payment_title_for_parent'           => 'fee_payment_title_for_parent',
		'mjschool_teacher_show_access'                    => 'own_class',
		'mjschool_admissiion_title'                       => 'admissiion_title',
		'mjschool_exam_receipt_subject'                   => 'exam_receipt_subject',
		'mjschool_bed_subject'                            => 'exam_receipt_subject',
		'mjschool_add_approve_admisson_mail_subject'      => 'Admission Approved',
		'mjschool_admissiion_approve_subject_for_parent_subject' => 'admissiion_approve_subject_for_parent_subject',
		'mjschool_student_assign_teacher_mail_subject'    => 'student_assign_teacher_mail_subject',
		'mjschool_enable_virtual_classroom_reminder'      => 'yes',
		'mjschool_enable_mjschool_virtual_classroom_reminder' => 'yes',
		'mjschool_virtual_classroom_reminder_before_time' => '30',
		'mjschool_heder_enable'                           => 'yes',
		'mjschool_admission_fees'                         => 'smgt_admission_fees',
		'mjschool_enable_recurring_invoices'              => 'smgt_enable_recurring_invoices',
		'mjschool_admission_amount'                       => 'smgt_admission_amount',
		'mjschool_registration_fees'                      => 'smgt_registration_fees',
		'mjschool_registration_amount'                    => 'smgt_registration_amount',
		'mjschool_system_color_code'                      => 'smgt_system_color_code',
		'mjschool_invoice_notice'                         => 'smgt_invoice_notice',
		'mjschool_attendence_migration_status'            => 'no',
		'mjschool_add_leave_emails'                       => '',
		'mjschool_leave_approveemails'                    => '',
		'mjschool_add_leave_subject'                      => 'add_leave_subject',
		'mjschool_add_leave_subject_of_admin'             => 'add_leave_subject_of_admin',
		'mjschool_add_leave_subject_for_student'          => 'add_leave_subject_for_student',
		'mjschool_add_leave_subject_for_parent'           => 'add_leave_subject_for_parent',
		'mjschool_leave_approve_subject'                  => 'leave_approve_subject',
		'mjschool_leave_reject_subject'                   => 'leave_reject_subject',
		'mjschool_add_exam_mail_title'                    => 'add_exam_mail_title',
		'mjschool_upload_document_type'                   => 'smgt_upload_document_type',
		'mjschool_upload_profile_extention'               => 'smgt_upload_profile_extention',
		'mjschool_upload_document_size'                   => 'smgt_upload_document_size',
		'mjschool_upload_profile_size'                    => 'smgt_upload_profile_size',
		// ------------- SMS Template Start. --------------- //
		'mjschool_attendance_mjschool_content'            => 'smgt_attendance_mjschool_content',
		'mjschool_fees_payment_mjschool_content_for_student' => 'smgt_fees_payment_mjschool_content_for_student',
		'mjschool_fees_payment_mjschool_content_for_parent' => 'smgt_fees_payment_mjschool_content_for_parent',
		'mjschool_fees_payment_reminder_mjschool_content' => 'smgt_fees_payment_reminder_mjschool_content',
		'mjschool_student_approve_mjschool_content'       => 'smgt_student_approve_mjschool_content',
		'mjschool_student_admission_approve_mjschool_content' => 'smgt_student_admission_approve_mjschool_content',
		'mjschool_holiday_mjschool_content'               => 'smgt_holiday_mjschool_content',
		'mjschool_leave_student_mjschool_content'         => 'smgt_leave_student_mjschool_content',
		'mjschool_leave_parent_mjschool_content'          => 'smgt_leave_parent_mjschool_content',
		'mjschool_event_mjschool_content'                 => 'smgt_event_mjschool_content',
		'mjschool_exam_student_mjschool_content'          => 'smgt_exam_student_mjschool_content',
		'mjschool_exam_parent_mjschool_content'           => 'smgt_exam_parent_mjschool_content',
		'mjschool_homework_student_mjschool_content'      => 'smgt_homework_student_mjschool_content',
		'mjschool_homework_parent_mjschool_content'       => 'smgt_homework_parent_mjschool_content',
		'mjschool_setup_wizard_step'                      => 'smgt_setup_wizard_step',
		'mjschool_setup_wizard_status'                    => 'no',
		'mjschool_combine'                                => 'smgt_combine',
		// Add all your smgt_ options here, mapping to mjschool_ version if renamed.
	);
	// Migrate options.
	foreach ($smgt_to_mjschool_keys as $new => $old)
	{
		if (!is_array($old ) )
		{
			$value = get_option($old);
			if ($value !== false) {
				update_option($new, $value);
			}
			else{
				$value2 = get_option($new);
				if ($value === false) {
					update_option($new, $old);
				}
			}
		}
		else
		{
			update_option($new, $old);
		}
	}
	update_option( 'mjschool_migrate_options_check', '1' );
}
/**
 * Retrieves a single notification record by its ID.
 *
 * This function fetches one notification entry from the database
 * using the provided notification ID.
 *
 * @since 1.0.0
 *
 * @param int $id Notification ID to retrieve.
 *
 * @return object|null Returns the notification row object if found, otherwise null.
 */
function mjschool_get_single_notification_by_id($id)
{
	global $wpdb;
	$id = absint($id);
	$mjschool_notification=$wpdb->prefix. 'mjschool_notification';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
	$result= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $mjschool_notification WHERE notification_id= %d", $id));
	return $result;
}

/**
 * Retrieve a filtered list of student users based on class and section,
 * while excluding student IDs returned by the approval list function.
 * 
 * @since 1.0.0
 *
 * @param int $class_id   The class ID used to filter students.
 * @param int $section_id The section ID within the class.
 *
 * @return WP_User[] An array of WP_User objects matching the criteria.
 */
function mjschool_get_student_name_with_class_and_section($class_id, $section_id)
{
	// Fetch list of student IDs that should be excluded.
	$exclude_ids = mjschool_approve_student_list();
	// Ensure the exclude list is always an array.
	if (!is_array($exclude_ids)) {
		$exclude_ids = array();
	}

	// Sanitize input values for safety.
	$class_id   = absint($class_id);
	$section_id = absint($section_id);

	// Prepare query arguments for retrieving students.
	$args = array(
		'role'       => 'student',
		'exclude'    => $exclude_ids,
		'number'     => -1, // ensures all matched users are returned
		'orderby'    => 'display_name',
		'order'      => 'ASC',
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'class_name',
				'value'   => $class_id,
				'compare' => '=',
			),
			array(
				'key'     => 'class_section',
				'value'   => $section_id,
				'compare' => '=',
			),
		),
	);

	$students = get_users($args);

	return is_array($students) ? $students : array();
}

/**
 * Retrieve a list of student users filtered by class,
 * excluding a set of student IDs returned by the approval list function.
 *
 * @since 1.0.0
 *
 * @param int $class_id The class ID used to filter students.
 *
 * @return WP_User[] Array of WP_User objects for matched students.
 */
function mjschool_get_student_name_with_class($class_id)
{
	// Get list of student IDs to exclude.
	$exclude_ids = mjschool_approve_student_list();

	// Always ensure the exclude list is an array.
	if (!is_array($exclude_ids)) {
		$exclude_ids = array();
	}

	// Sanitize class ID.
	$class_id = absint($class_id);

	// Prepare query arguments.
	$args = array(
		'role'       => 'student',
		'exclude'    => $exclude_ids,
		'number'     => -1, // return all matching students
		'orderby'    => 'display_name',
		'order'      => 'ASC',
		'meta_query' => array(
			array(
				'key'     => 'class_name',
				'value'   => $class_id,
				'compare' => '=',
			),
		),
	);

	$students = get_users($args);

	return is_array($students) ? $students : array();
}

/**
 * Retrieve all certificate IDs and names from the dynamic certificate table.
 *
 * @global wpdb $wpdb WordPress database access abstraction object.
 *
 * @return array List of certificate objects. Each object includes:
 *               - int    $id
 *               - string $certificate_name
 */
function mjschool_get_certificate_id_and_name() {
    global $wpdb;
    $table = $wpdb->prefix . 'mjschool_daynamic_certificate';
    // Use prepare() even if no variable is used (for consistency & safety).
    $query = $wpdb->prepare( "SELECT id, certificate_name FROM {$table} ORDER BY id ASC", array() );
    $results = $wpdb->get_results( $query );
    // Ensure always returning an array
    $results = is_array( $results ) ? $results : array();
    return $results;
}

/**
 * Retrieve all notification records from the `mjschool_notification` table.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return array Array of result objects. Returns an empty array if no records found.
 */
function mjschool_get_notification_all_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mjschool_notification';
    $query = "SELECT * FROM {$table_name} ORDER BY id DESC";
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}

/**
 * Retrieve time table entries using class ID and section name.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $class_id     The class ID to filter time table records.
 * @param string $section_name The section name to filter time table records.
 *
 * @return array List of time table entries as objects. Returns an empty array if none found.
 */
function mjschool_get_time_table_using_class_and_section( $class_id, $section_name ) {
    global $wpdb;

    // Table name
    $table_name   = $wpdb->prefix . "mjschool_time_table";
	$class_id     = absint($class_id);
	$section_name = sanitize_text_field( $section_name );
    // Prepare and execute query (section_name is TEXT → use %s)
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND section_name = %s", $class_id, $section_name );

    $results = $wpdb->get_results( $query );

    // Ensure safe return
    return is_array( $results ) ? $results : array();
}

/**
 * Get distinct exam ID for a given student ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $uid The student/user ID.
 *
 * @return array List of distinct exam IDs. Returns an empty array if none found.
 */
function mjschool_get_manage_marks_exam_id_using_student_id( $uid ) {
    global $wpdb;
	$uid = absint($uid);
    // Table name
    $table_name = $wpdb->prefix . "mjschool_marks";
    $query = $wpdb->prepare( "SELECT DISTINCT exam_id FROM {$table_name} WHERE student_id = %d", $uid );
    // Fetch column results
    $exam_ids = $wpdb->get_col( $query );
    // Always return an array
    return is_array( $exam_ids ) ? $exam_ids : array();
}

/**
 * Get distinct class ID for a given student ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $uid The student/user ID.
 *
 * @return array List of distinct class IDs. Returns an empty array if none found.
 */
function mjschool_get_manage_marks_class_id_using_student_id( $uid ) {
    global $wpdb;
	$uid = absint($uid);
    // Table name
    $table_name = $wpdb->prefix . "mjschool_marks";
    // Corrected query: table name should not be in quotes
    $query = $wpdb->prepare( "SELECT DISTINCT class_id FROM {$table_name} WHERE student_id = %d", $uid );
    // Fetch column results
    $class_ids = $wpdb->get_col( $query );
    // Always return an array
    return is_array( $class_ids ) ? $class_ids : array();
}
/**
 * Get distinct subject IDs for a given student ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $uid The student/user ID.
 *
 * @return array List of distinct subject IDs. Returns an empty array if none found.
 */
function mjschool_get_manage_marks_subject_id_using_student_id( $uid ) {
    global $wpdb;
	$uid = absint($uid);
    // Table name
    $table_name = $wpdb->prefix . "mjschool_marks";
    // Corrected query: table name should not be in quotes
    $query = $wpdb->prepare( "SELECT DISTINCT subject_id FROM {$table_name} WHERE student_id = %d", $uid );
    // Fetch column results
    $subject_ids = $wpdb->get_col( $query );
    // Always return an array
    return is_array( $subject_ids ) ? $subject_ids : array();
}

/**
 * Retrieve exam details for a list of exam IDs.
 *
 * This function accepts an array of exam IDs and returns the matching
 * exam records from the `mjschool_exam` database table. It safely constructs
 * a dynamic SQL query using placeholders to avoid SQL injection.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $exam_ids Array of exam IDs (integers).
 *
 * @return array List of exam objects. Returns an empty array if nothing found.
 */
function mjschool_get_exam_details_by_ids( array $exam_ids ) {
    global $wpdb;

    // Return early if array is empty
    if ( empty( $exam_ids ) ) {
        return array();
    }

    // Table name
    $table_name = $wpdb->prefix . "mjschool_exam";

    // Create dynamic placeholders for IN()
    $placeholders = implode( ',', array_fill( 0, count( $exam_ids ), '%d' ) );

    // Query (add source_table field, as in your original code)
    $query = " SELECT *, 'mjschool_exam' AS source_table FROM {$table_name} WHERE exam_id IN ($placeholders) ";

    // Prepare and execute query
    $prepared_query = $wpdb->prepare( $query, ...$exam_ids );

    // Execute
    $results = $wpdb->get_results( $prepared_query );

    return is_array( $results ) ? $results : array();
}


/**
 * Get distinct class–section combinations for a given student.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $student_id The student/user ID.
 *
 * @return array List of objects containing class_id and section_id. 
 *               Returns an empty array if no matching records found.
 */
function mjschool_get_class_section_pairs_by_student( $student_id ) {
    global $wpdb;
	$student_id = absint($student_id);
    // Table name
    $table_name = $wpdb->prefix . "mjschool_marks";
    // Prepare query
    $query = $wpdb->prepare( "SELECT DISTINCT class_id, section_id FROM {$table_name} WHERE student_id = %d", $student_id );
    // Fetch results
    $results = $wpdb->get_results( $query );
    // Ensure array return
    return is_array( $results ) ? $results : array();
}

/**
 * Retrieve exam merge settings based on class ID, section ID, and status.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $class_id   The class ID to filter records.
 * @param int    $section_id The section ID to filter records.
 * @param string $status     The status value to filter (e.g., 'enable', 'disable').
 *
 * @return array List of setting objects. Returns an empty array if no matches found.
 */
function mjschool_get_exam_merge_settings( $class_id, $section_id, $status ) {
    global $wpdb;

    // Table name
    $table_name = $wpdb->prefix . "mjschool_exam_merge_settings";
	$class_id   = absint($class_id);
	$section_id = absint($section_id);
	$status     = sanitize_text_field( $status );

    // Prepare query safely
    $query = $wpdb->prepare( "SELECT *, 'mjschool_exam_merge_settings' AS source_table FROM {$table_name} WHERE class_id = %d AND section_id = %d AND status = %s", $class_id, $section_id, $status );

    // Fetch results
    $results = $wpdb->get_results( $query );

    // Always return array
    return is_array( $results ) ? $results : array();
}


/**
 * Get the class ID for a student in a specific exam.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $exam_id   The exam ID to filter records.
 * @param int $student_id The student/user ID.
 *
 * @return int|null The class ID if found, otherwise null.
 */
function mjschool_get_class_id_by_exam_and_student( $exam_id, $student_id ) {
    global $wpdb;
	$exam_id = absint($exam_id);
	$student_id = absint($student_id);
    // Table name
    $table_name = $wpdb->prefix . "mjschool_marks";

    // Prepare SQL query
    $query = $wpdb->prepare( "SELECT class_id FROM {$table_name} WHERE exam_id = %d AND student_id = %d LIMIT 1", $exam_id, $student_id );

    // Fetch single value
    $class_id = $wpdb->get_var( $query );

    // Ensure proper output (may return null)
    return $class_id !== null ? intval( $class_id ) : null;
}

/**
 * Retrieve a subject record by class ID and subject code.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $class_id     The class ID to filter subjects.
 * @param string $subject_code The subject code to identify the subject.
 *
 * @return object|null The subject row as an object, or null if not found.
 */
function mjschool_get_subject_by_class_and_code( $class_id, $subject_code ) {
    global $wpdb;
	$class_id = absint($class_id);
	$subject_code = sanitize_text_field( $subject_code );
    $table_name = $wpdb->prefix . 'mjschool_subject';
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND subject_code = %s", $class_id, $subject_code );
    $result = $wpdb->get_row( $query );
    return $result ? $result : null;
}

/**
 * Delete all class assignments for a specific teacher.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $teacher_id The teacher ID whose class assignments will be deleted.
 *
 * @return int|false Number of rows deleted on success, or false on failure.
 */
function mjschool_delete_teacher_class_assignments( $teacher_id ) {
    global $wpdb;
	$teacher_id = absint($teacher_id);
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_teacher_class';
    // Prepare delete query safely
    $query = $wpdb->prepare( "DELETE FROM {$table_name} WHERE teacher_id = %d", $teacher_id );
    // Execute delete operation
    return $wpdb->query( $query );
}

/**
 * Retrieve income or expense entries for a specific date.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $year         The year to filter records.
 * @param int    $month        The month to filter records.
 * @param int    $day          The day to filter records.
 * @param string $invoice_type The type of record to fetch ('income' or 'expense').
 *
 * @return array List of matching income/expense entries. Returns an empty array if none found.
 */
function mjschool_get_income_expense_by_date( $year, $month, $day, $invoice_type ) {
    global $wpdb;
	$year = absint($year);
	$month = absint($month);
	$day = absint($day);
	$invoice_type = sanitize_text_field( $invoice_type );
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_income_expense';
    // Prepare SQL query
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE YEAR(income_create_date) = %d AND MONTH(income_create_date) = %d AND DAY(income_create_date) = %d AND invoice_type = %s", $year, $month, $day, $invoice_type );
    // Execute and return results
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}


/**
 * Retrieve fee payment history entries for a specific date.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $year  The year to filter (e.g., 2025).
 * @param int $month The month to filter (1–12).
 * @param int $day   The day to filter (1–31).
 *
 * @return array List of payment history records. Returns an empty array if none found.
 */
function mjschool_get_fee_payment_history_by_date( $year, $month, $day ) {
    global $wpdb;
	$year = absint($year);
	$month = absint($month);
	$day = absint($day);
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_fee_payment_history';

    // Prepare SQL query
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE YEAR(paid_by_date) = %d AND MONTH(paid_by_date) = %d AND DAY(paid_by_date) = %d", $year, $month, $day );

    // Execute and return results
    $results = $wpdb->get_results( $query );

    return is_array( $results ) ? $results : array();
}


/**
 * Check if a class already exists with the same class name and class number,
 * excluding a specific class ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $class_name     The class name to check.
 * @param string $class_num_name The class number/name to check.
 * @param int    $exclude_id     The class ID to exclude from the check.
 *
 * @return object|null The matching class record, or null if no duplicate found.
 */
function mjschool_check_existing_class( $class_name, $class_num_name, $exclude_id ) {
    global $wpdb;
	$class_name     = sanitize_text_field( $class_name );
	$class_num_name = sanitize_text_field( $class_num_name );
	$exclude_id     = absint($exclude_id);
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_class';
    // Prepare SQL query
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_name = %s AND class_num_name = %s AND class_id != %d", $class_name, $class_num_name, $exclude_id );
    // Fetch result
    $result = $wpdb->get_row( $query );
    return $result ? $result : null;
}


/**
 * Check if a class already exists based on class name and class number/name.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $class_name     The class name to check.
 * @param string $class_num_name The class number/name to check.
 *
 * @return object|null The existing class record as an object, or null if no match found.
 */
function mjschool_get_existing_class( $class_name, $class_num_name ) {
    global $wpdb;
	$class_name     = sanitize_text_field( $class_name );
	$class_num_name = sanitize_text_field( $class_num_name );
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_class';
    // Prepare query
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_name = %s AND class_num_name = %s", $class_name, $class_num_name );
    // Run the query
    $result = $wpdb->get_row( $query );
    // Return result or null
    return $result ?: null;
}

/**
 * Retrieve teacher-class assignments for multiple class IDs.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $class_ids Array of class IDs to filter (must be integers).
 *
 * @return array List of matching teacher-class assignment objects.
 *               Returns an empty array if no records found.
 */
function mjschool_get_teacher_class_assignments_by_class_ids( array $class_ids ) {
    global $wpdb;
    // Return empty if no IDs provided
    if ( empty( $class_ids ) ) {
        return array();
    }
    // Table name
    $table_name = $wpdb->prefix . 'mjschool_teacher_class';
    // Build placeholders dynamically (%d for each class_id)
    $placeholders = implode( ',', array_fill( 0, count( $class_ids ), '%d' ) );
    // Prepare SQL query
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id IN ($placeholders)", ...$class_ids );
    // Fetch results
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}


/**
 * Retrieve all user IDs assigned to a specific exam hall receipt.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $exam_id The exam ID to filter assigned students.
 *
 * @return array List of objects containing user_id. Returns an empty array if none found.
 */
function mjschool_get_assigned_students_by_exam( $exam_id ) {
    global $wpdb;
	$exam_id    = absint($exam_id);
    $table_name = $wpdb->prefix . 'mjschool_exam_hall_receipt';
    $query      = $wpdb->prepare( "SELECT user_id FROM {$table_name} WHERE exam_id = %d", $exam_id );
    $results    = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}

/**
 * Retrieve fee payment records for all class IDs assigned to the current user.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int|array $class_ids Class ID or array of class IDs for filtering.
 *
 * @return array List of fee payment records. Always returns an array.
 */
function mjschool_get_fees_payment_by_class_ids( $class_ids ) {
    global $wpdb;
    if ( empty( $class_ids ) ) {
        return array();
    }
    if ( ! is_array( $class_ids ) ) {
        $class_ids = array( $class_ids ); // convert single value to array.
    }
    $table_name = $wpdb->prefix . 'mjschool_fees_payment';
    $placeholders = implode( ',', array_fill( 0, count( $class_ids ), '%d' ) );
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id IN ($placeholders)", ...$class_ids );
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}


/**
 * Retrieve all bed records in a room except the specified bed.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $room_id The room ID to filter beds.
 * @param int $exclude_bed_id The bed ID to exclude from results.
 *
 * @return array List of bed objects. Returns an empty array if none found.
 */
function mjschool_get_other_beds_in_room( $room_id, $exclude_bed_id ) 
{
    global $wpdb;
	$room_id        = absint($room_id);
	$exclude_bed_id = absint($exclude_bed_id);
    $table_name = $wpdb->prefix . 'mjschool_beds';
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE room_id = %d AND id != %d", $room_id, $exclude_bed_id );
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}

/**
 * Retrieve a single message row by post ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $post_id The post ID for which the message is to be retrieved.
 *
 * @return object|null The message object if found, otherwise null.
 */
function mjschool_get_message_by_post_id( $post_id ) {
    global $wpdb;
	$post_id = absint($post_id);
    // Table name.
    $table_name = $wpdb->prefix . 'mjschool_message';
    // Prepare SQL query.
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE post_id = %d", $post_id );
    // Execute query.
    $result = $wpdb->get_row( $query );
    return $result ? $result : null;
}


/**
 * Check whether a library book already exists based on book name and ISBN.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $book_name The name of the book.
 * @param string $isbn      The ISBN number of the book.
 *
 * @return object|null The matching book record (id only), or null if no match found.
 */
function mjschool_get_existing_library_book( $book_name, $isbn ) {
    global $wpdb;
	$book_name = sanitize_text_field( $book_name );
	$isbn      = sanitize_text_field( $isbn );
    // Table name.
    $table_name = $wpdb->prefix . 'mjschool_library_book';
    // Prepare and execute query.
    $query = $wpdb->prepare( "SELECT id FROM {$table_name} WHERE book_name = %s AND ISBN = %s", $book_name, $isbn );
    $result = $wpdb->get_row( $query );
    return $result ?: null;
}


/**
 * Retrieve subject records by an array of subject IDs (subid).
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $subject_ids Array of subject IDs (subid) as integers.
 *
 * @return array List of subject objects. Returns an empty array if none found.
 */
function mjschool_get_subjects_by_ids( array $subject_ids ) 
{
    global $wpdb;
    if ( empty( $subject_ids ) ) {
        return array();
    }
    $subject_ids = array_map( 'intval', $subject_ids );
    $table_name = $wpdb->prefix . 'mjschool_subject';
    $placeholders = implode( ',', array_fill( 0, count( $subject_ids ), '%d' ) );
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE subid IN ($placeholders)", ...$subject_ids );
    $results = $wpdb->get_results( $query );
    return is_array( $results ) ? $results : array();
}


/**
 * Retrieve all teacher user IDs created by a specific user.
 *
 * It returns only the user IDs of matching teacher accounts.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $created_by The user ID of the creator (admin or staff).
 *
 * @return array List of teacher user IDs. Returns an empty array if none found.
 */
function mjschool_get_teachers_created_by_user( $created_by ) {
    global $wpdb;
    // Sanitize user ID
    $created_by = intval( $created_by );
    // WordPress capabilities meta key
    $cap_key = $wpdb->prefix . 'capabilities';
    // LIKE parameter for searching 'teacher'
    $role_like = '%teacher%';
    // Prepare query
    $query = $wpdb->prepare( " SELECT u.ID FROM {$wpdb->users} u INNER JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'created_by' AND um1.meta_value = %d INNER JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = %s AND um2.meta_value LIKE %s ", $created_by, $cap_key, $role_like );
    // Execute
    $teacher_ids = $wpdb->get_col( $query );
    return is_array( $teacher_ids ) ? $teacher_ids : array();
}

/**
 * Retrieve subjects based on class ID and section ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $class_id        The class ID to filter subjects.
 * @param int $section_id      The section ID to filter subjects.
 *
 * @return array List of subject objects. Returns an empty array if none found.
 */
function mjschool_get_subjects_by_class_and_section( $class_id, $section_id ) {
    global $wpdb;
	$class_id   = absint($class_id);
	$section_id = absint($section_id);
    // Table name.
    $table_name = $wpdb->prefix . 'mjschool_subject';
    // Prepare query.
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d AND section_id = %d", $class_id, $section_id );
    // Execute.
    $subjects = $wpdb->get_results( $query );
    return is_array( $subjects ) ? $subjects : array();
}

/**
 * Retrieve a certificate record by certificate ID.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int $certificate_id The ID of the certificate to retrieve.
 *
 * @return object|null The certificate record as an object, or null if not found.
 */
function mjschool_get_certificate_by_id( $certificate_id ) {
    global $wpdb;
    // Table name.
	$certificate_id = absint($certificate_id);
    $table_name = $wpdb->prefix . 'mjschool_certificate';
    // Prepare query.
    $query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $certificate_id );
    // Execute query
    $result = $wpdb->get_row( $query );
    return $result ?: null;
}
?>