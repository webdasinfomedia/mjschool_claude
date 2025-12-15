<?php
/**
 * School Management Subject Class.
 *
 * This file contains the Mjschool_Subject class, which handles
 * subject-related data retrieval, primarily focusing on which subjects a
 * specific teacher is assigned to.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Manages all functionality related to subject data.
 *
 * @since  1.0.0
 */
class Mjschool_Subject {
	/**
	 * Retrieves the subject IDs associated with a specific teacher.
	 *
	 * It checks for subjects where the teacher is either directly assigned
	 * (`teacher_id`) or is the creator of the assignment record (`created_by`).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $teacher_id The ID of the teacher.
	 * @return array Array of objects, each containing the `subject_id`.
	 * @since 1.0.0
	 */
	public function mjschool_get_teacher_own_subject( $teacher_id ) {
		global $wpdb;
		$table_mjschool_beds = $wpdb->prefix . 'mjschool_teacher_subject';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT subject_id From $table_mjschool_beds WHERE teacher_id=%d OR created_by=%d", $teacher_id, $teacher_id ) );
		return $result;
	}
}