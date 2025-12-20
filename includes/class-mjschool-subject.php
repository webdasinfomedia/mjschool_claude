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
	 * Checks if a subject exists.
	 *
	 * @param int $id Subject ID.
	 * @return int Number of matching records.
	 * @since 1.0.0
	 */
	public function mjschool_is_subject_check( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$subject_id = absint( $id );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE subid=%d", $subject_id ) );
		
		return absint( $retrieve_subject );
	}

	/**
	 * Retrieves subject IDs assigned to a teacher.
	 *
	 * @param int $id Teacher ID.
	 * @return array List of subject IDs.
	 * @since 1.0.0
	 */
	public function mjschool_get_subject_id_by_teacher( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_teacher_subject';
		$teacher_id = absint( $id );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id=%d", $teacher_id ) );
		
		$subjects = array();
		if ( ! empty( $retrieve_subject ) ) {
			foreach ( $retrieve_subject as $retrive_data ) {
				$count = $this->mjschool_is_subject_check( $retrive_data->subject_id );
				if ( $count > 0 ) {
					$subjects[] = absint( $retrive_data->subject_id );
				}
			}
		}
		
		return $subjects;
	}

	/**
	 * Retrieves subject names assigned to a teacher.
	 *
	 * @param int $id Teacher ID.
	 * @return string Comma-separated subject names.
	 * @since 1.0.0
	 */
	public function mjschool_get_subject_name_by_teacher( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_teacher_subject';
		$teacher_id = absint( $id );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE teacher_id=%d", $teacher_id ) );
		
		$subjec = '';
		if ( ! empty( $retrieve_subject ) ) {
			foreach ( $retrieve_subject as $retrive_data ) {
				$sub_name = mjschool_get_single_subject_name( $retrive_data->subject_id );
				$subjec  .= $sub_name . ', ';
			}
		}
		
		return $subjec;
	}

	/**
	 * Retrieves only the subject code by ID.
	 *
	 * @param int $id Subject ID.
	 * @return string|null Subject code.
	 * @since 1.0.0
	 */
	public function mjschool_get_single_subject_code( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$subject_id = absint( $id );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_var( $wpdb->prepare( "SELECT subject_code FROM $table_name WHERE subid=%d", $subject_id ) );
		
		return $retrieve_subject;
	}

	/**
	 * Retrieves all subjects stored in the system.
	 *
	 * @return array List of subjects.
	 * @since 1.0.0
	 */
	public function mjschool_get_all_subject() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
		$retrive_subject = $wpdb->get_results( "SELECT * FROM {$table_name}" );
		
		return $retrive_subject;
	}

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
		$teacher_id = intval($teacher_id);
		$table_mjschool_beds = $wpdb->prefix . 'mjschool_teacher_subject';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT subject_id From $table_mjschool_beds WHERE teacher_id=%d OR created_by=%d", $teacher_id, $teacher_id ) );
		return $result;
	}
	
	/**
	 * Delete a subject and all related teacher mappings.
	 *
	 * @since 1.0.0
	 * @param string $mjschool_table_name Table name.
	 * @param int $id Subject ID.
	 * @return int Rows affected.
	 */
	public function mjschool_delete_subject( $mjschool_table_name, $id ) {
		global $wpdb;
		$table_name         = $wpdb->prefix . $mjschool_table_name;
		$teacher_table_name = $wpdb->prefix . 'mjschool_teacher_subject';
		$record_id          = intval( $id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$event   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name where subid=%d", $record_id ) );
		$subject = $event->sub_name;
		mjschool_append_audit_log( '' . esc_html__( 'Subject Deleted', 'mjschool' ) . '( ' . $subject . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$wpdb->query( $wpdb->prepare( "DELETE FROM $teacher_table_name WHERE subject_id= %d", $record_id ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_name WHERE subid= %d", $record_id ) );
	}
}