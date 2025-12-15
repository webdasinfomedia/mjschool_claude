<?php
/**
 * MJSchool Management Class.
 *
 * Defines the core MJSchool_Management class, which acts as a central object for
 * managing and retrieving contextual data for a specific user (Student, Teacher, etc.).
 * The constructor initializes properties like user role, class information, subject lists,
 * and lists of related users (parents, children, students in their class) based on the
 * provided user ID.
 *
 * @package Mjschool
 * @subpackage Mjschool
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class MJSchool_Management.
 *
 * This class is the primary data model for a user in the MJSchool system. 
 * It initializes various properties (e.g., student, teacher, subject) based 
 * on the user's role and ID provided during object creation.
 * @since 1.0.0
 */
class MJSchool_Management {
	public $student;
	public $teacher;
	public $exam;
	public $result;
	public $subject;
	public $schedule;
	public $transport;
	public $notice;
	public $message;
	public $role;
	public $class_info;
	public $parent_list;
	public $child_list;
	public $payment;
	public $feepayment;
	public $class_section;
	/**
	 * Constructor for MJSchool_Management.
	 *
	 * @since 1.0.0
	 * @param int|null $user_id The ID of the user to initialize data for.
	 */
	function __construct( $user_id = null ) {
		if ( $user_id ) {
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'student' ) {
				$this->role               = 'student';
				$this->class_info         = $this->mjschool_get_user_class_id( $user_id );
				$this->class_section_info = $this->mjschool_get_user_class_id( $user_id );
				$this->subject            = $this->mjschool_subject_list( $this->class_info->class_id );
				$this->parent_list        = $this->mjschool_parents( $user_id );
				$this->student            = $this->mjschool_get_student_list( $this->class_info->class_id );
				$this->payment_list       = $this->mjschool_payment( 'student' );
				$this->notice             = $this->mjschool_notice_board( $this->mjschool_get_current_user_role() );
			}
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'teacher' ) {
				$this->role          = 'teacher';
				$teacher_access      = get_option( 'mjschool_access_right_teacher' );
				$teacher_access_data = $teacher_access['teacher'];
				foreach ( $teacher_access_data as $key => $value ) {
					if ( $key === 'student' ) {
						$data = $value;
					}
				}
				if ( $data['own_data'] === '1' ) {
					$class_id      = get_user_meta( $user_id, 'class_name', true );
					$this->student = $this->mjschool_get_teacher_student_list( $class_id );
				} else {
					$this->student = $this->mjschool_get_all_student_list();
				}
				$this->notice = $this->mjschool_notice_board( $this->mjschool_get_current_user_role() );
			}
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'supportstaff' ) {
				$this->role         = 'supportstaff';
				$this->student      = $this->mjschool_get_all_student_list();
				$this->notice       = $this->mjschool_notice_board( $this->mjschool_get_current_user_role() );
				$this->payment_list = $this->mjschool_payment( 'supportstaff' );
			}
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'parent' ) {
				$this->role         = 'parent';
				$this->child_list   = $this->mjschool_child( $user_id );
				$this->student      = $this->mjschool_get_all_student_list();
				$this->payment_list = $this->mjschool_payment( 'parent' );
				$this->notice       = $this->mjschool_notice_board( $this->mjschool_get_current_user_role() );
			}
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'administrator' ) {
				$this->role = 'administrator';
			}
			if ( $this->mjschool_get_current_user_role( $user_id ) === 'management' ) {
				$this->role = 'management';
			}
			$this->payment    = $this->mjschool_payment( $this->mjschool_get_current_user_role() );
			$this->feepayment = $this->mjschool_feepayment( $this->mjschool_get_current_user_role() );
		}
	}
	/**
	 * Wrapper function to get a user's role.
	 *
	 * @since 1.0.0
	 * @param int $user_id The ID of the user.
	 * @return string The user's role (lowercase).
	 */
	public function mjschool_get_current_user_role( $userid = 0 ) {
		if ( $userid != 0 ) {
			$current_user = get_userdata( $userid );
		} else {
			$current_user = wp_get_current_user();
		}
		if ( ! empty( $current_user->roles ) ) {
			$user_roles = $current_user->roles; // Get all roles assigned to the user.
			// Check if the user has one of the specific roles.
			$allowed_roles = array( 'student', 'parent', 'student_temp', 'teacher', 'supportstaff', 'administrator', 'management' );
			foreach ( $user_roles as $role ) {
				if ( in_array( $role, $allowed_roles ) ) {
					return $role; // Return the matched role.
				}
			}
		}
		return false; // Return false if no matching role is found.
	}
	/**
	 * Retrieves the main class information for a student.
	 *
	 * @since 1.0.0
	 * @param int $user_id The ID of the student.
	 * @return object|null The class information object or null.
	 */
	public function mjschool_get_user_class_id( $id ) {
		$user_id   = intval( $id );
		$user      = get_userdata( $user_id );
		$user_meta = get_user_meta( $user_id );
		$class_id  = $user_meta['class_name'][0];
		$class_info = mjschool_get_class_by_id($class_id);
		return $class_info;
	}
	/**
	 * Retrieves the section information for a student.
	 *
	 * @since 1.0.0
	 * @param int $user_id The ID of the student.
	 * @return object|null The section information object or null.
	 */
	public function mjschool_get_user_section_id( $id ) {
		$user_id    = intval( $id );
		$user       = get_userdata( $user_id );
		$user_meta  = get_user_meta( $user_id );
		$section_id = $user_meta['class_section'][0];
		$section_info = mjschool_get_class_sections($class_id);
		return $section_info;
	}
	/**
	 * Retrieves the list of subjects belonging to a specific class.
	 *
	 * @since 1.0.0
	 * @param int $class_id The ID of the class.
	 * @return array List of subjects.
	 */
	public function mjschool_subject_list( $id ) {
		$class_id   = intval( $id );
		$result = mjschool_get_subject_by_class_id($class_id);
		return $result;
	}
	/**
	 * Retrieves the list of subjects subjects belonging to a specific class and section.
	 *
	 * @since 1.0.0
	 * @param int $cid The ID of the class.
	 * @param int $sid The ID of the student.
	 * @return array List of subjects.
	 */
	public function mjschool_subject_list_with_calss_and_section( $cid, $sid ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$class_id   = intval( $cid );
		$section_id = intval( $sid );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND section_id = %d", $class_id, $section_id ) );
		return $result;
	}
	/**
	 * Retrieves notice board data based on user role.
	 *
	 * This method fetches notices from the database, filtered by user role,
	 * class, and section for students, or all notices for other roles.
	 *
	 * @since 1.0.0
	 * @param string $role The user role (e.g., 'student', 'teacher').
	 * @param int    $limit The number of notices to retrieve (-1 for all).
	 * @return array Array of notice post IDs or objects.
	 */
	public function mjschool_notice_board( $role, $limit = -1 ) {
		$args['post_type']      = 'notice';
		$args['posts_per_page'] = $limit;
		$args['post_status']    = 'public';
		$args['orderby']        = 'date';
		$args['order']          = 'DESC';
		$retrieve_notice_data   = array();
		if ( $role === 'student' ) {
			$class_id   = get_user_meta( get_current_user_id(), 'class_name', true );
			$section_id = get_user_meta( get_current_user_id(), 'class_section', true );
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => 'smgt_section_id',
					'value'   => get_user_meta( get_current_user_id(), 'class_section', true ),
					'compare' => '=',
				),
				array(
					'key'     => 'smgt_class_id',
					'value'   => get_user_meta( get_current_user_id(), 'class_name', true ),
					'compare' => '=',
				),
			);
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$q                          = new WP_Query();
			$retrieve_class_data_notice = $q->query( $args );
			foreach ( $retrieve_class_data_notice as $notice ) {
				$retrieve_notice_data[] = $notice->ID;
			}
			$retrieve_notice = $retrieve_notice_data;
		} else {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = array(
				'relation' => 'OR',
			);
			$q                  = new WP_Query();
			$retrieve_notice    = $q->query( $args );
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}
		return $retrieve_notice;
	}
	/**
	 * Retrieves notice board data for a specific student.
	 *
	 * This private method fetches notices relevant to a student based on their class and role.
	 *
	 * @since 1.0.0
	 * @param int    $user_id The user ID of the student.
	 * @param string $role    The user role.
	 * @return array Array of notice objects.
	 */
	private function mjschool_notice_board_student( $user_id, $role ) {
		$class_id = get_user_meta( $user_id, 'class_name', true );
		global $wpdb;
		$table_post     = $wpdb->prefix . 'posts';
		$table_postmeta = $wpdb->prefix . 'postmeta';
		$notice_limit   = '';
		if ( ! isset( $_REQUEST['page'] ) ) {
			$notice_limit = 'Limit 0,3';
		}
		$sql = " select * FROM $table_post as post,$table_postmeta as post_meta where post.post_type='notice' AND 
		 (post.ID=post_meta.post_id AND (post_meta.meta_key = 'notice_for' AND 
		 (post_meta.meta_value = '$role' OR post_meta.meta_value = 'all' ) ) OR 
		 (post_meta.meta_key = 'notice_for' AND post_meta.meta_key = 'smgt_class_id' AND
		 (post_meta.meta_value = '$class_id' OR post_meta.meta_value = 'all' ) ) ) $notice_limit";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_notice = $wpdb->get_results( $sql );
		return $retrieve_notice;
	}
	/**
	 * Retrieves notice board data for parents.
	 *
	 * This method fetches notices intended for parents or all users.
	 *
	 * @since 1.0.0
	 * @param string $role The user role (e.g., 'parent').
	 * @return array Array of notice post objects.
	 */
	public function mjschool_notice_board_parent( $role ) {
		$args['post_type']      = 'notice';
		$args['posts_per_page'] = -1;
		$args['post_status']    = 'public';
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'   => 'notice_for',
				'value' => 'all',
			),
			array(
				'key'   => 'notice_for',
				'value' => "$role",
			),
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$q               = new WP_Query();
		$retrieve_notice = $q->query( $args );
		return $retrieve_notice;
	}
	/**
	 * Retrieves notice board data for teachers.
	 *
	 * This private method fetches notices intended for teachers or all users.
	 *
	 * @since 1.0.0
	 * @param string $role The user role (e.g., 'teacher').
	 * @return array Array of notice post objects.
	 */
	private function mjschool_notice_board_teacher( $role ) {
		$args['post_type']      = 'notice';
		$args['posts_per_page'] = -1;
		$args['post_status']    = 'public';
		$class_id               = '';
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key'   => 'notice_for',
				'value' => 'all',
			),
			array(
				'key'   => 'notice_for',
				'value' => "$role",
			),
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$q               = new WP_Query();
		$retrieve_notice = $q->query( $args );
		return $retrieve_notice;
	}
	/**
	 * Retrieves payment data based on user role.
	 *
	 * This private method fetches payment records from the database,
	 * filtered by user role (student, parent, or all).
	 *
	 * @since 1.0.0
	 * @param string $user_role The user role (e.g., 'student', 'parent').
	 * @return array Array of payment objects.
	 */
	private function mjschool_payment( $user_role ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . 'mjschool_payment as p';
		$table_users = $wpdb->prefix . 'users as u';
		if ( $user_role === 'student' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d", get_current_user_id() ) );
		} elseif ( $user_role === 'parent' ) {
			$child_ids = implode( ',', array_map( 'intval', $this->child_list ) ); // Ensure all child IDs are integers
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id IN ($child_ids)" ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( "SELECT * FROM $table_name, $table_users WHERE p.student_id = u.id" );
		}
		return $result;
	}
	/**
	 * Retrieves fee payment data based on user role.
	 *
	 * This private method fetches fee payment records from the database,
	 * filtered by user role (student, parent, or all).
	 *
	 * @since 1.0.0
	 * @param string $user_role The user role (e.g., 'student', 'parent').
	 * @return array Array of fee payment objects.
	 */
	private function mjschool_feepayment( $user_role ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . 'mjschool_fees_payment as p';
		$table_users = $wpdb->prefix . 'users as u';
		if ( $user_role === 'student' ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d", get_current_user_id() ) );
		} elseif ( $user_role === 'parent' ) {
			$child_ids = implode( ',', array_map( 'intval', $this->child_list ) ); // Ensure all child IDs are integers
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id IN ($child_ids)" ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->get_results( "SELECT * FROM $table_name, $table_users WHERE p.student_id = u.id" );
		}
		return $result;
	}
	/**
	 * Retrieves a list of students for a teacher based on class.
	 *
	 * This method fetches students enrolled in a specific class, excluding approved students.
	 *
	 * @since 1.0.0
	 * @param int $class_id The class ID.
	 * @return array Array of student user objects.
	 */
	public function mjschool_get_teacher_student_list( $class_id ) {
		$exclude_id = mjschool_approve_student_list();
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$students = get_users(
			array(
				'role'       => 'student',
				'exclude'    => $exclude_id,
				'meta_query' => array(
					array(
						'key'     => 'class_name',
						'value'   => $class_id,
						'compare' => 'IN',
					),
				),
			)
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		return $students;
	}
	/**
	 * Retrieves the list of students in a specific class, excluding specified IDs.
	 *
	 * @since 1.0.0
	 * @param int $class_id   The ID of the class.
	 * @param array $exclude_id Array of user IDs to exclude from the list.
	 * @return array List of student user objects.
	 */
	public function mjschool_get_student_list( $class_id ) {
		$exclude_id          = mjschool_approve_student_list();
		$student_access      = get_option( 'mjschool_access_right_student' );
		$student_access_data = $student_access['student'];
		foreach ( $student_access_data as $key => $value ) {
			if ( $key === 'student' ) {
				$data = $value;
			}
		}
		if ( $this->role === 'student' && $data['own_data'] === '1' ) {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$students = get_users(
				array(
					'role'       => 'student',
					'exclude'    => $exclude_id,
					'meta_query' => array(
						array(
							'key'     => 'class_name',
							'value'   => $class_id,
							'compare' => '=',
						),
					),
				)
			);
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		} else {
			// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$students = get_users(
				array(
					'role'       => 'student',
					'exclude'    => $exclude_id,
					'meta_query' => array(
						array(
							'key'     => 'class_name',
							'value'   => $class_id,
							'compare' => '=',
						),
					),
				)
			);
			// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}
		return $students;
	}
	/**
	 * Retrieves a list of all students, excluding approved students.
	 *
	 * This method fetches all users with the 'student' role, excluding those in the approved list.
	 *
	 * @since 1.0.0
	 * @return array Array of student user objects.
	 */
	public function mjschool_get_all_student_list() {
		$exlude_id = mjschool_approve_student_list();
		$students  = get_users(
			array(
				'role'    => 'student',
				'exclude' => $exlude_id,
			)
		);
		return $students;
	}
	/**
	 * Retrieves the parent(s) of a given student.
	 *
	 * This private method fetches the parent IDs associated with a student from user meta.
	 *
	 * @since 1.0.0
	 * @param int $user_id The ID of the student.
	 * @return mixed The parent ID(s) from user meta.
	 */
	private function mjschool_parents( $user_id ) {
		$user_meta = get_user_meta( $user_id, 'parent_id', true );
		return $user_meta;
	}
	/**
	 * Retrieves the list of children associated with a given parent.
	 *
	 * This private method fetches the child IDs associated with a parent from user meta.
	 *
	 * @since 1.0.0
	 * @param int $user_id The ID of the parent.
	 * @return mixed The child ID(s) from user meta.
	 */
	private function mjschool_child( $user_id ) {
		$user_meta = get_user_meta( $user_id, 'child', true );
		return $user_meta;
	}
}