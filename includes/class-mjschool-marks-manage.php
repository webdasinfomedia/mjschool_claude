<?php
/**
 * School Management Marks Management Class.
 *
 * This file contains the Mjschool_Marks_Manage class, which handles
 * marks submission, retrieval, and grade calculation for students and teachers
 * using custom database tables.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
/**
 * Handles all business logic and data manipulation for the Mjschool Mark module.
 *
 * This class manages CRUD operations for marks and grade.
 *
 * @since 1.0.0
 */
class Mjschool_Marks_Manage {

	public $mark_id;
	public $exam_id;
	public $class_id;
	public $subject_id;
	public $marks;
	public $attendance;
	public $student_id;
	public $marks_comment;
	public $created_date;
	/**
	 * Constructor. Initializes the object by retrieving existing marks data if an ID is provided.
	 *
	 * @param int|null $marks Optional. The ID of the mark record to load.
	 * @since 1.0.0
	 */
	public function __construct( $marks = null ) {
		if ( $marks ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'mjschool_marks';
			$marks_id   = intval( $marks );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$mark_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE mark_id = %d", $marks_id ) );
			if ( $mark_data ) {
				$this->mark_id       = $mark_data->mark_id;
				$this->exam_id       = $mark_data->exam_id;
				$this->class_id      = $mark_data->class_id;
				$this->subject_id    = $mark_data->subject_id;
				$this->marks         = $mark_data->marks;
				$this->attendance    = $mark_data->attendance;
				$this->student_id    = $mark_data->student_id;
				$this->marks_comment = $mark_data->marks_comment;
			}
		}
	}

	/**
	 * Retrieves grade details by grade name.
	 *
	 * @param string $grade_name Grade name.
	 * @return object|null Grade record.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_by_name( $grade_name ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_grade';
		$grade_name = sanitize_text_field( $grade_name );	
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE grade_name = %s", $grade_name ) );
		return $retrieve_subject;
	}

	/**
	 * Retrieves grade details by ID.
	 *
	 * @param int $id Grade ID.
	 * @return object|null Grade record.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_by_id( $id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_grade';
		$gid        = absint( $id );
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE grade_id = %d", $gid ) );
		
		return $retrieve_subject;
	}

	/**
	 * Checks if a mark record exists for a given ID.
	 *
	 * @param int $mark_id The ID of the mark record.
	 * @return bool True if marks exist, false otherwise.
	 * @since 1.0.0
	 */
	public function mjschool_marks_exist( $mark_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$mark_id    = intval( $mark_id );
		$query      = $wpdb->prepare( "SELECT mark_id FROM $table_name WHERE mark_id = %d", $mark_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$marks = $wpdb->get_var( $query );
		if ( ! empty( $marks ) ) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Saves a new mark record.
	 *
	 * @param array $marks_data Array of mark data to be inserted.
	 * @since 1.0.0
	 */
	public function mjschool_save_marks( $marks_data ) {
		$table_name = 'mjschool_marks';
		mjschool_insert_record( $table_name, $marks_data );
	}
	/**
	 * Updates an existing mark record.
	 *
	 * @param array $marks_data Array of mark data to be updated.
	 * @param int   $mark_id The ID of the mark record to update.
	 * @return int|false The number of rows updated, or false on error.
	 * @since 1.0.0
	 */
	public function mjschool_update_marks( $marks_data, $mark_id ) {
		$table_name = 'mjschool_marks';
		$mark_id    = intval( $mark_id );
		$result     = mjschool_update_record( $table_name, $marks_data, $mark_id );
		return $result;
	}
	/**
	 * Retrieves subject marks details for a specific exam, class, subject, and student.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $userid The ID of the student.
	 * @return object|false The row object on success, or false on failure.
	 * @since 1.0.0
	 */
	public function mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $subject_id, $userid ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		$subject_id = intval( $subject_id );
		$userid     = intval( $userid );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_marks = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $userid ) );
		if ( ! empty( $retrieve_marks ) ) {
			return $retrieve_marks;
		} else {
			return false;
		}
	}
	/**
	 * Retrieves all mark records for a specific student ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $userid The ID of the student.
	 * @return array Array of mark records.
	 * @since 1.0.0
	 */
	public function mjschool_subject_makrs_by_student_id( $userid ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$userid     = intval( $userid );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_marks = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE student_id = %d", $userid ) );
		return $retrieve_marks;
	}
	/**
	 * Retrieves subjects for a class, optionally for a section.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @param int $section_id Optional. The ID of the section (defaults to 0).
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_student_subject( $class_id, $section_id = 0 ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . 'mjschool_subject';
		$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
		$user_id     = get_current_user_id();
		$class_id    = intval( $class_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id ) );
		return $retrieve_subject;
	}
	/**
	 * Retrieves subjects for a class, optionally filtered by section, primarily for export purposes.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @param int $section_id Optional. The ID of the section (defaults to 0).
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_student_subject_export( $class_id, $section_id = 0 ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . 'mjschool_subject';
		$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
		$user_id     = get_current_user_id();
		$class_id    = intval( $class_id );
		$section_id  = intval( $section_id );
		if ( $section_id === 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d", $class_id ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND section_id = %d", $class_id, $section_id ) );
		}
		return $retrieve_subject;
	}
	/**
	 * Retrieves the list of subjects based on user role and class/section.
	 *
	 * Teachers see subjects they teach; others see all subjects for the class/section.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @param int $section_id Optional. The ID of the section (defaults to 0).
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_student_subject_for_list( $class_id, $section_id = 0 ) {
		global $wpdb;
		$table_name  = $wpdb->prefix . 'mjschool_subject';
		$table_name2 = $wpdb->prefix . 'mjschool_teacher_subject';
		$user_id     = get_current_user_id();
		$section_id  = intval( $section_id ); // Ensure section_id is an integer.
		$class_id    = intval( $class_id ); // Ensure class_id is an integer.
		if ( mjschool_get_roles( $user_id ) === 'teacher' ) {
			if ( $section_id !== 0 ) {
				// Use $wpdb->prepare to secure the query.
				$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM {$table_name} p1 INNER JOIN {$table_name2} p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d AND p1.section_id = %d", $user_id, $class_id, $section_id );
			} else {
				// Use $wpdb->prepare to secure the query.
				$query = $wpdb->prepare( "SELECT p1.*, p2.* FROM {$table_name} p1 INNER JOIN {$table_name2} p2 ON (p1.subid = p2.subject_id) WHERE p2.teacher_id = %d AND p1.class_id = %d", $user_id, $class_id );
			}
			// Execute the query and get the results.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$retrieve_subject = $wpdb->get_results( $query );
		} else {
			if ( $section_id !== 0 ) {
				// Use $wpdb->prepare to secure the query.
				$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE section_id = %d AND class_id = %d", $section_id, $class_id );
			} else {
				// Use $wpdb->prepare to secure the query.
				$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d", $class_id );
			}
			// Execute the query and get the results.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$retrieve_subject = $wpdb->get_results( $query );
		}
		return $retrieve_subject;
	}
	/**
	 * Retrieves the list of subjects for a student's class (ignores section if empty).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @param int $section_id The ID of the section.
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_student_subject_list( $class_id, $section_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$section_id = ( $section_id === '' || $section_id === null ) ? 0 : intval( $section_id );
		$class_id   = intval( $class_id ); // Ensure class_id is an integer.
		// Secure the query using $wpdb->prepare.
		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d", $class_id );
		// Execute the query and return the results.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $query );
		return $retrieve_subject;
	}
	/**
	 * Retrieves the list of subjects based solely on class ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_student_subject_by_class( $class_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$class_id   = intval( $class_id ); // Ensure class_id is an integer.
		// Secure the query using $wpdb->prepare.
		$query = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE class_id = %d", $class_id );
		// Execute the query and return the results.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $query );
		return $retrieve_subject;
	}
	/**
	 * Retrieves the subjects taught by a specific teacher for a specific class.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $class_id The ID of the class.
	 * @param int $teacherid The ID of the teacher.
	 * @return array Array of subject records.
	 * @since 1.0.0
	 */
	public function mjschool_teachers_subject( $class_id, $teacherid ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_subject';
		$class_id   = intval( $class_id );
		$teacherid  = intval( $teacherid );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_subject = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE class_id = %d AND teacher_id = %d", $class_id, $teacherid ) );
		return $retrieve_subject;
	}
	/**
	 * Retrieves the calculated marks for a specific student, subject, class, and exam.
	 *
	 * Handles both single mark entry and marks with contributions (json data).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return int|array The marks (integer) or an array of marks from contributions.
	 * @since 1.0.0
	 */
	public function mjschool_get_marks( $exam_id, $class_id, $subject_id, $user_id ) {
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		$subject_id = intval( $subject_id );
		$user_id    = intval( $user_id );
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $user_id ) );
		$marks  = 0;
		if ( ! empty( $result ) ) {
			if ( $result->contributions === 'yes' ) {
				$marks = json_decode( $result->class_marks, true );
			} else {
				$marks = $result->marks;
			}
		}
		return $marks;
	}
	/**
	 * Retrieves all marks for a student in a given exam (fetches the first row, used for general checks).
	 *
	 * Handles both single mark entry and marks with contributions (json data).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $user_id The ID of the student.
	 * @return int|array The marks (integer) or an array of marks from contributions.
	 * @since 1.0.0
	 */
	public function mjschool_get_marks_all( $exam_id, $user_id ) {
		$exam_id = intval( $exam_id );
		$user_id = intval( $user_id );
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE exam_id = %d AND student_id = %d", $exam_id, $user_id ) );
		$marks  = 0;
		if ( ! empty( $result ) ) {
			if ( $result->contributions === 'yes' ) {
				$marks = json_decode( $result->class_marks, true );
			} else {
				$marks = $result->marks;
			}
		}
		return $marks;
	}
	/**
	 * Retrieves a student's mark for an exam and class, typically used for pass/fail calculation.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $user_id The ID of the student.
	 * @return string|null The mark value or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_marks_pass_fail( $exam_id, $class_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		$user_id    = intval( $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT marks FROM $table_name WHERE exam_id = %d AND class_id = %d AND student_id = %d", $exam_id, $class_id, $user_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the maximum marks for an exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @return string|null The total mark value or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_max_marks( $exam_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_exam';
		$exam_id    = intval( $exam_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT total_mark FROM $table_name WHERE exam_id = %d", $exam_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the passing marks for an exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @return string|null The passing mark value or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_pass_marks( $exam_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_exam';
		$exam_id    = intval( $exam_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT passing_mark FROM $table_name WHERE exam_id = %d", $exam_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the exam term for a given exam ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @return string|null The exam term or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_exam_term( $exam_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_exam';
		$exam_id    = intval( $exam_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT exam_term FROM $table_name WHERE exam_id = %d", $exam_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the marks comment for a student in a specific subject and exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return string|null The marks comment or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_marks_comment( $exam_id, $class_id, $subject_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		$subject_id = intval( $subject_id );
		$user_id    = intval( $user_id );
		$query      = "SELECT marks_comment FROM $table_name WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d";
		// Use $wpdb->prepare to safely insert the variables into the query
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( $query, $exam_id, $class_id, $subject_id, $user_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the comment associated with a specific grade name.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param string $grade_name The name of the grade.
	 * @return string|null The grade comment or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_marks_comment( $grade_name ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_grade';
		$grade_name = sanitize_text_field( $grade_name );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT grade_comment FROM $table_name WHERE grade_name = %s", $grade_name ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the attendance status for a student in a specific subject and exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return string|null The attendance status or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_attendance( $exam_id, $class_id, $subject_id, $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		$subject_id = intval( $subject_id );
		$user_id    = intval( $user_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT attendance FROM $table_name WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $user_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the name of a grade given its ID.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $grade_id The ID of the grade.
	 * @return string|null The grade name or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_name( $grade_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_grade';
		$grade_id   = intval( $grade_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT grade_name FROM $table_name WHERE grade_id = %d", $grade_id ) );
		return $retrieve_result;
	}
	/**
	 * Calculates the grade name based on a total mark value (grand total).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $total_marks The total marks.
	 * @return string The grade name, or an empty string if no grade is found.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_base_on_grand_total( $total_marks ) {
		global $wpdb;
		$table_grade = $wpdb->prefix . 'mjschool_grade';
		$total_marks = intval( $total_marks );
		// Fetch grade.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$grade = $wpdb->get_var( $wpdb->prepare( "SELECT grade_name FROM $table_grade WHERE %d BETWEEN mark_upto AND mark_from", $total_marks ) );
		return $grade ? $grade : '';
	}
	/**
	 * Retrieves the grade name for a student's mark in a specific subject and exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return string The grade name, or an empty string if no grade is found.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade( $exam_id, $class_id, $subject_id, $user_id ) {
		global $wpdb;
		// Sanitize input parameters.
		$exam_id     = intval( $exam_id );
		$class_id    = intval( $class_id );
		$subject_id  = intval( $subject_id );
		$user_id     = intval( $user_id );
		$table_marks = $wpdb->prefix . 'mjschool_marks';
		$table_grade = $wpdb->prefix . 'mjschool_grade';
		// Fetch student marks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT marks, contributions, class_marks FROM $table_marks WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $user_id ) );
		if ( ! $result ) {
			return null; // No result found.
		}
		// Calculate total marks.
		$total_marks = 0;
		if ( $result->contributions === 'yes' ) {
			$marks_array = json_decode( $result->class_marks, true );
			if ( is_array( $marks_array ) ) {
				$total_marks = array_sum( $marks_array );
			}
		} else {
			$total_marks = intval( $result->marks );
		}
		// Fetch grade.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$grade = $wpdb->get_var( $wpdb->prepare( "SELECT grade_name FROM $table_grade WHERE %d BETWEEN mark_upto AND mark_from", $total_marks ) );
		return $grade ? $grade : '';
	}
	/**
	 * Retrieves the grade comment for a student's mark in a specific subject and exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return string The grade comment, or an empty string if no grade is found.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_comment( $exam_id, $class_id, $subject_id, $user_id ) {
		global $wpdb;
		// Sanitize input parameters.
		$exam_id     = intval( $exam_id );
		$class_id    = intval( $class_id );
		$subject_id  = intval( $subject_id );
		$user_id     = intval( $user_id );
		$table_marks = $wpdb->prefix . 'mjschool_marks';
		$table_grade = $wpdb->prefix . 'mjschool_grade';
		// Fetch student marks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT marks, contributions, class_marks FROM $table_marks WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $user_id ) );
		if ( ! $result ) {
			return null; // No result found.
		}
		// Calculate total marks.
		$total_marks = 0;
		if ( $result->contributions === 'yes' ) {
			$marks_array = json_decode( $result->class_marks, true );
			if ( is_array( $marks_array ) ) {
				$total_marks = array_sum( $marks_array );
			}
		} else {
			$total_marks = intval( $result->marks );
		}
		// Fetch grade.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$grade = $wpdb->get_var( $wpdb->prepare( "SELECT grade_comment FROM $table_grade WHERE %d BETWEEN mark_upto AND mark_from", $total_marks ) );
		return $grade ? $grade : '';
	}
	/**
	 * Retrieves the grade point for a student's mark in a specific subject and exam.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $subject_id The ID of the subject.
	 * @param int $user_id The ID of the student.
	 * @return float The grade point, or 0 if no grade is found.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_point( $exam_id, $class_id, $subject_id, $user_id ) {
		global $wpdb;
		// Sanitize input parameters.
		$exam_id     = intval( $exam_id );
		$class_id    = intval( $class_id );
		$subject_id  = intval( $subject_id );
		$user_id     = intval( $user_id );
		$table_marks = $wpdb->prefix . 'mjschool_marks';
		$table_grade = $wpdb->prefix . 'mjschool_grade';
		// Fetch student marks.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT marks, contributions, class_marks FROM $table_marks WHERE exam_id = %d AND class_id = %d AND subject_id = %d AND student_id = %d", $exam_id, $class_id, $subject_id, $user_id ) );
		if ( ! $result ) {
			return null; // No result found.
		}
		// Calculate total marks.
		$total_marks = 0;
		if ( $result->contributions === 'yes' ) {
			$marks_array = json_decode( $result->class_marks, true );
			if ( is_array( $marks_array ) ) {
				$total_marks = array_sum( $marks_array );
			}
		} else {
			$total_marks = intval( $result->marks );
		}
		// Fetch grade.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$grade = $wpdb->get_var( $wpdb->prepare( "SELECT grade_point FROM $table_grade WHERE %d BETWEEN mark_upto AND mark_from", $total_marks ) );
		return $grade ? floatval( $grade ) : 0;
	}
	/**
	 * Retrieves the grade ID based on a mark value.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $marks The mark value.
	 * @return string|null The grade ID or null.
	 * @since 1.0.0
	 */
	public function mjschool_get_grade_id( $marks ) {
		global $wpdb;
		$tbl_grade = $wpdb->prefix . 'mjschool_grade';
		$marks     = intval( $marks );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT grade_id FROM $tbl_grade WHERE %d BETWEEN mark_upto AND mark_from", $marks ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the marks comment for export purposes (currently only fetches one comment based on exam and class).
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @return string|null The marks comment or null.
	 * @since 1.0.0
	 */
	public function mjschool_export_marks( $exam_id, $class_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		$exam_id    = intval( $exam_id );
		$class_id   = intval( $class_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_var( $wpdb->prepare( "SELECT marks_comment FROM $table_name WHERE exam_id = %d AND class_id = %d", $exam_id, $class_id ) );
		return $retrieve_result;
	}
	/**
	 * Retrieves the mark for a student in a specific subject, class, and exam, used primarily for export.
	 *
	 * @global wpdb $wpdb WordPress database access abstraction object.
	 * @param int $exam_id The ID of the exam.
	 * @param int $class_id The ID of the class.
	 * @param int $sutdent_id The ID of the student.
	 * @param int $subject_id The ID of the subject.
	 * @return int The mark value, or 0 if not found.
	 * @since 1.0.0
	 */
	public function mjschool_export_get_subject_mark( $exam_id, $class_id, $sutdent_id, $subject_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'mjschool_marks';
		// Sanitize the input values
		$exam_id    = intval( $exam_id ); // Ensure exam_id is an integer.
		$class_id   = intval( $class_id ); // Ensure class_id is an integer.
		$sutdent_id = intval( $sutdent_id ); // Ensure student_id is an integer.
		$subject_id = intval( $subject_id ); // Ensure subject_id is an integer.
		$query      = $wpdb->prepare( "SELECT marks FROM {$table_name} WHERE exam_id = %d AND class_id = %d AND student_id = %d AND subject_id = %d", $exam_id, $class_id, $sutdent_id, $subject_id );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$retrieve_result = $wpdb->get_row( $query );
		if ( ! empty( $retrieve_result ) ) {
			return $retrieve_result->marks;
		} else {
			return 0;
		}
	}
}