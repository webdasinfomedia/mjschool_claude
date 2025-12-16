<?php
/**
 * School Management Exam Management Class.
 *
 * This file contains the Mjschool_Exam class, which handles
 * operations related to exam scheduling, time tables, and
 * merging exam results.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Mjschool_Exam Class.
 *
 * Manages database operations for subjects, exam time tables,
 * individual exams, and merged exam settings.
 *
 * @since 1.0.0
 */
class Mjschool_Exam {

	/**
	 * Retrieves subjects assigned to a specific class and section.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $cid Class ID.
	 * @param  int $sid Section ID.
	 * @return array|object|null An array of subject records, or null if no results are found.
	 */
	public function mjschool_get_subject_by_section_id( $cid, $sid ) {
		global $wpdb;
		$class_id   = intval( $cid );
		$section_id = intval( $sid );
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id=%d and section_id=%d", $class_id, $section_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $results;
	}
	/**
	 * Retrieves all subjects associated with a specific class ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $class_id The ID of the class.
	 * @return array|object|null An array of subject records, or null if no results are found.
	 */
	public function mjschool_get_subject_by_class_id( $class_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE  class_id=%s", $class_id ) );
	}
	/**
	 * Inserts or updates an exam time table entry for a specific subject.
	 *
	 * Handles data sanitation, time format conversion, and audit logging.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int    $class_id   The ID of the class.
	 * @param  int    $exam_id    The ID of the exam.
	 * @param  int    $subject_id The ID of the subject.
	 * @param  string $exam_date  The date of the exam.
	 * @param  string $start_time The start time of the exam.
	 * @param  string $end_time   The end time of the exam.
	 * @return int|false The number of affected rows on success, or false on error.
	 */
	public function mjschool_insert_sub_wise_time_table( $class_id, $exam_id, $subject_id, $exam_date, $start_time, $end_time ) {
		global $wpdb;
		$table_name               = $wpdb->prefix . 'mjschool_exam_time_table';
		$curr_date                = date( 'Y-m-d' );
		$curren_user              = get_current_user_id();
		$exam_date_new            = date( 'Y-m-d', strtotime( $exam_date ) );
		$start_time_24hrs_formate = mjschool_time_convert( $start_time );
		$end_time_24hrs_formate   = mjschool_time_convert( $end_time );
		$start_time_new           = $start_time_24hrs_formate;
		$end_time_new             = $end_time_24hrs_formate;
		$check_insrt_or_update    = $this->mjschool_check_subject_data( $exam_id, $subject_id );
		// Sanitize inputs.
		$class_id       = intval( $class_id ); // Ensure class_id is an integer.
		$exam_id        = intval( $exam_id ); // Ensure exam_id is an integer.
		$subject_id     = intval( $subject_id ); // Ensure subject_id is an integer.
		$exam_date_new  = sanitize_text_field( $exam_date_new ); // Sanitize date as text.
		$start_time_new = sanitize_text_field( $start_time_new ); // Sanitize start time.
		$end_time_new   = sanitize_text_field( $end_time_new ); // Sanitize end time.
		$curr_date      = current_time( 'mysql' ); // Get the current date safely.
		$curren_user    = get_current_user_id(); // Get current user ID.
		if ( empty( $check_insrt_or_update ) ) {
			// Log audit for insert.
			mjschool_append_audit_log( esc_html__( 'Exam Time Table Added', 'mjschool' ), $curren_user, $curren_user, 'insert', $_REQUEST['page'] );
			// Use prepared statement to insert data securely.
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$save_data = $wpdb->insert(
				$table_name,
				array(
					'class_id'     => $class_id,
					'exam_id'      => $exam_id,
					'subject_id'   => $subject_id,
					'exam_date'    => $exam_date_new,
					'start_time'   => $start_time_new,
					'end_time'     => $end_time_new,
					'created_date' => $curr_date,
					'created_by'   => $curren_user,
				),
				array(
					'%d', // class_id as integer.
					'%d', // exam_id as integer.
					'%d', // subject_id as integer.
					'%s', // exam_date as string.
					'%s', // start_time as string.
					'%s', // end_time as string.
					'%s', // created_date as string.
					'%d',  // created_by as integer.
				)
			);
		} else {
			// Log audit for update.
			mjschool_append_audit_log( esc_html__( 'Exam Time Table Updated', 'mjschool' ), $curren_user, $curren_user, 'edit', $_REQUEST['page'] );
			// Use prepared statement to update data securely.
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$save_data = $wpdb->update(
				$table_name,
				array(
					'exam_date'    => $exam_date_new,
					'start_time'   => $start_time_new,
					'end_time'     => $end_time_new,
					'created_date' => $curr_date,
					'created_by'   => $curren_user,
				),
				array(
					'class_id'   => $class_id,
					'exam_id'    => $exam_id,
					'subject_id' => $subject_id,
				),
				array(
					'%s', // exam_date as string.
					'%s', // start_time as string.
					'%s', // end_time as string.
					'%s', // created_date as string.
					'%d',  // created_by as integer.
				),
				array(
					'%d', // class_id as integer.
					'%d', // exam_id as integer.
					'%d',  // subject_id as integer.
				)
			);
		}
		return $save_data;
	}
	/**
	 * Checks if an exam time table entry exists for a given exam and subject.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $exam_id    The ID of the exam.
	 * @param  int $subject_id The ID of the subject.
	 * @return array|object|null An array of matching time table records, or null if none are found.
	 */
	public function mjschool_check_subject_data( $exam_id, $subject_id ) {
		global $wpdb;
		$exam_id = intval($exam_id);
		$subject_id = intval($subject_id);
		$table_name = $wpdb->prefix . 'mjschool_exam_time_table';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id= %d and subject_id= %d", $exam_id, $subject_id ) );
		return $results;
	}
	/**
	 * Retrieves a single exam time table entry by class, exam, and subject ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $class_id The ID of the class.
	 * @param  int $exam_id  The ID of the exam.
	 * @param  int $sub_id   The ID of the subject.
	 * @return object|null The matching time table record object, or null if none is found.
	 */
	public function mjschool_check_exam_time_table( $class_id, $exam_id, $sub_id ) {
		global $wpdb;
		$exam_id = intval($exam_id);
		$class_id = intval($class_id);
		$sub_id = intval($sub_id);
		$table_name = $wpdb->prefix . 'mjschool_exam_time_table';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id= %d and exam_id= %d and subject_id= %d", $class_id, $exam_id, $sub_id ) );
		return $result;
	}
	/**
	 * Retrieves all exam time table entries for a specific exam ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $exam_id The ID of the exam.
	 * @return array|object|null An array of matching time table records, or null if none are found.
	 */
	public function mjschool_get_exam_time_table_by_exam( $exam_id ) {
		global $wpdb;
		$exam_id = intval($exam_id);
		$table_name = $wpdb->prefix . 'mjschool_exam_time_table';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id= %d", $exam_id ) );
		return $results;
	}
	/**
	 * Retrieves exams assigned to a list of class IDs or created by a specific user.
	 *
	 * Note: The `class_id` parameter is expected to be an array of IDs.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $class_id Array of class IDs.
	 * @param  int   $user_id  The ID of the user (creator).
	 * @return array|object|null An array of exam records, or null if no results are found.
	 */
	public function mjschool_get_all_exam_by_class_id_created_by( $class_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_exam';

		$class_id = (array) $class_id;

		// Create placeholders for each class ID.
		$placeholders = implode(',', array_fill(0, count($class_id), '%d'));

		// Prepare the query.
		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE class_id IN ($placeholders) OR exam_creater_id = %d",
			array_merge($class_id, array($user_id))
		);

		// Execute.
		$results = $wpdb->get_results($sql);
		return $results;
	}
	/**
	 * Retrieves all exams created by a specific user.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $user_id The ID of the user (creator).
	 * @return array|object|null An array of exam records, or null if no results are found.
	 */
	public function mjschool_get_all_exam_created_by( $user_id ) {
		global $wpdb;
		$user_id = intval($user_id);
		$table_name = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_creater_id=%d", $user_id ) );
		return $results;
	}
	/**
	 * Retrieves the latest 3 exams for a class with no specific section for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $class_id The ID of the class.
	 * @return array|object|null An array of the latest 3 exam records, or null if fewer are found.
	 */
	function mjschool_get_all_exam_by_class_id_dashboard( $class_id ) {
		global $wpdb;
		$class_id = intval($class_id);
		$table_name = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d and section_id='0' ORDER BY exam_id DESC limit 3", $class_id ) );
	}
	/**
	 * Retrieves the latest 3 exams for a specific class and section for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $class_id   The ID of the class.
	 * @param  int $section_id The ID of the section.
	 * @return array|object|null An array of the latest 3 exam records, or null if fewer are found.
	 */
	function mjschool_get_all_exam_by_class_id_and_section_id_array_dashboard( $class_id, $section_id ) {
		global $wpdb;
		$class_id = intval($class_id);
		$table_name = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id= %d and section_id= %d ORDER BY exam_id DESC limit 3", $class_id, $section_id ) );
	}
	/**
	 * Retrieves the latest 3 exams for a list of classes for the dashboard.
	 *
	 * Searches for exams assigned to the list of classes (`class_id`) and no specific section (`section_id = 0`).
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $class_id Array of class IDs.
	 * @param  int   $user_id  The ID of the user (creator).
	 * @return array|object|null An array of the latest 3 exam records, or null if fewer are found.
	 */
	public function mjschool_get_all_exam_by_class_id_created_by_dashboard( $class_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_exam';
		// Sanitize the class IDs.
		$class_ids = array_map( 'intval', $class_id );
		// Prepare the SQL query using placeholders.
		$query = "SELECT * FROM $table_name WHERE class_id IN (%s) AND section_id = %d ORDER BY exam_id DESC LIMIT 3";
		// Implode the class IDs into a comma-separated list.
		$class_ids_imploded = implode( ',', $class_ids );
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( $query, $class_ids_imploded, 0 ) );  // section_id is '0'
		return $results;
	}
	/**
	 * Retrieves the latest 3 exams for a list of classes with no specific section for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $class_id Array of class IDs.
	 * @return array|object|null An array of the latest 3 exam records, or null if fewer are found.
	 */
	function mjschool_get_all_exam_by_class_id_array_dashboard( $class_id ) {
		global $wpdb;
		$class_id = intval($class_id);
		$table_name = $wpdb->prefix . 'mjschool_exam';
		global $wpdb;
		// Ensure $class_id is an array and properly sanitized.
		$class_ids = array_map( 'intval', $class_id );  // Convert each ID to an integer
		// Prepare the SQL query using placeholders.
		$query = "SELECT * FROM $table_name WHERE class_id IN (%s) AND section_id = %d ORDER BY exam_id DESC LIMIT 3";
		// Implode the class IDs into a comma-separated list.
		$class_ids_imploded = implode( ',', $class_ids );
		// Prepare the query with the secure values.
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( $query, $class_ids_imploded, 0 ) );  // section_id is '0'
		return $results;
	}
	/**
	 * Retrieves the latest 3 exams created by a specific user for the dashboard.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $user_id The ID of the user (creator).
	 * @return array|object|null An array of the latest 3 exam records, or null if fewer are found.
	 */
	public function mjschool_get_all_exam_created_by_dashboard( $user_id ) {
		global $wpdb;
		$user_id    = intval( $user_id );
		$table_name = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_creater_id= %d ORDER BY exam_id DESC limit 3", $user_id ) );
		return $results;
	}
	/**
	 * Retrieves the latest 5 exam records for a general dashboard view.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array|object|null An array of the latest 5 exam records, or null if fewer are found.
	 */
	public function mjschool_exam_list_for_dashboard() {
		global $wpdb;
		$smgt_exam = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $smgt_exam ORDER BY exam_id DESC limit 5" ) );
		return $result;
	}
	/**
	 * Retrieves a single exam record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $exam_id The unique ID of the exam.
	 * @return object|null The exam record object, or null otherwise.
	 */
	public function mjschool_exam_data( $exam_id ) {
		global $wpdb;
		$exam_id = intval($exam_id);
		$smgt_exam = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $smgt_exam WHERE exam_id=%d", $exam_id ) );
		return $result;
	}
	/**
	 * Retrieves the name of an exam by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $exam_id The unique ID of the exam.
	 * @return object|null The exam name record object, or null otherwise.
	 */
	public function mjschool_exam_name_by_id( $exam_id ) {
		global $wpdb;
		$exam_id = intval($exam_id);
		$smgt_exam = $wpdb->prefix . 'mjschool_exam';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT exam_name FROM $smgt_exam WHERE exam_id=%d", $exam_id ) );
		return $result;
	}
	/**
	 * Inserts or updates settings for merging multiple exams into a single result.
	 *
	 * Handles data sanitation, JSON encoding of merge configuration, and audit logging.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  array $data Array of merge setting details from the form submission.
	 * @return int|false The number of affected rows on success, or false on error.
	 */
	public function mjschool_save_merge_exam_setting( $data ) {
		global $wpdb;
		$table_exam_merge_settings = $wpdb->prefix . 'mjschool_exam_merge_settings';
		$exam_config               = array();
		if ( ! empty( $data['exam_id'] ) && is_array( $data['exam_id'] ) ) {
			foreach ( $data['exam_id'] as $index => $exam_id ) {
				$weightage     = isset( $data['weightage'][ $index ] ) ? floatval( $data['weightage'][ $index ] ) : 0;
				$exam_config[] = array(
					'exam_id'   => intval( $exam_id ),
					'weightage' => $weightage,
				);
			}
		}
		$json_config     = ! empty( $exam_config ) ? json_encode( $exam_config, JSON_UNESCAPED_UNICODE ) : json_encode( array( 'exams' => array() ) );
		$merge_exam_data = array(
			'class_id'     => intval( $data['class_id'] ),
			'section_id'   => isset( $data['section_id'] ) ? $data['section_id'] : 0,
			'merge_name'   => sanitize_text_field( wp_unslash( $data['merge_name'] ) ),
			'merge_config' => $json_config,
			'status'       => 'enable',
			'created_by'   => get_current_user_id(),
		);
		if ( $data['action'] == 'edit_merge' ) {
			// Secure update using prepare statement.
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $table_exam_merge_settings 
					SET class_id = %d, section_id = %d, merge_name = %s, merge_config = %s, status = %s, created_by = %d 
					WHERE id = %d",
					$merge_exam_data['class_id'],
					$merge_exam_data['section_id'],
					$merge_exam_data['merge_name'],
					$merge_exam_data['merge_config'],
					$merge_exam_data['status'],
					$merge_exam_data['created_by'],
					$data['merge_id'] // Assuming 'id' is the primary key.
				)
			);
			mjschool_append_audit_log( '' . esc_html__( 'Group Exam Merge Settings Updated', 'mjschool' ) . '( ' . $data['merge_name'] . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_text_field( wp_unslash($_REQUEST['page']) ) );
			return $result;
		} else {
			// Secure insert using prepare statement.
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result = $wpdb->query(
				$wpdb->prepare(
					"INSERT INTO $table_exam_merge_settings (class_id, section_id, merge_name, merge_config, status, created_by) 
					VALUES (%d, %d, %s, %s, %s, %d)",
					$merge_exam_data['class_id'],
					$merge_exam_data['section_id'],
					$merge_exam_data['merge_name'],
					$merge_exam_data['merge_config'],
					$merge_exam_data['status'],
					$merge_exam_data['created_by']
				)
			);
			mjschool_append_audit_log(
				esc_html__( 'Group Exam Merge Setting Added', 'mjschool' ) . ' ( ' . esc_html( $merge_exam_data['merge_name'] ) . ' )',
				get_current_user_id(),
				get_current_user_id(),
				'insert',
				isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''
			);
		}
		return $result;
	}
	/**
	 * Retrieves all configured exam merge settings.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return array|object|null An array of merge setting records, or null if none are found.
	 */
	public function mjschool_get_all_merge_exam_setting() {
		global $wpdb;
		$exam_merge_settings = esc_sql( $wpdb->prefix . 'mjschool_exam_merge_settings' ); // Escaping table name.
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$exam_merge_settings_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$exam_merge_settings} WHERE 1 = %d", 1 ) );
		return $exam_merge_settings_data;
	}
	/**
	 * Deletes a single exam merge setting record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the merge setting to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function mjschool_delete_exam_setting( $id ) {
		global $wpdb;
		$id = intval($id);
		$exam_merge_settings = esc_sql( $wpdb->prefix . 'mjschool_exam_merge_settings' ); // Escaping table name.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM $exam_merge_settings where id=%d", $id ) );
		return $result;
	}
	/**
	 * Retrieves a single exam merge setting record by its ID.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param  int $id The unique ID of the merge setting.
	 * @return object|null The merge setting record object, or null otherwise.
	 */
	public function mjschool_get_single_merge_exam_setting( $id ) {
		global $wpdb;
		$id = intval($id);
		$exam_merge_settings = esc_sql( $wpdb->prefix . 'mjschool_exam_merge_settings' ); // Escaping table name.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$exam_merge_settings} WHERE id = %d", $id ) );
		return $result;
	}
}
