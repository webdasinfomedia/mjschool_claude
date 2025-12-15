<?php
/**
 * Class Mjschool_Attendence_Manage
 *
 * Handles all attendance-related operations for the MJSchool plugin.
 * Supports student, teacher, and subject-wise attendance tracking,
 * insertion, update, and retrieval using WordPress database APIs.
 *
 * Key Features:
 * - Student and teacher attendance management.
 * - Subject-wise attendance tracking with sections.
 * - Insert or update attendance dynamically.
 * - Secure database operations with sanitization and prepared statements.
 * - Compatible with WordPress standards and PHPCS.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
class Mjschool_Attendence_Manage
{

    public $class_id;
    public $status;
    public $attendance;
    public $student_id;
    public $attend_by;
    public $attendence_date;
    public $curr_date;
    public $table_name;
    public $result;
    public $role;
    public $savedata = 0;
	/**
     * Constructor.
     *
     * Initializes the attendance table reference.
	 * 
	 * @since 1.0.0
     *
     * @param mixed|null $marks Optional marks data.
     */
    public function __construct( $marks = null )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
    }
    /**
     * Insert or update student attendance.
	 * 
	 * @since 1.0.0
     *
     * @param string $curr_date        Attendance date.
     * @param int    $class_id         Class ID.
     * @param int    $user_id          Student user ID.
     * @param int    $attend_by        Attendance marked by user ID.
     * @param string $status           Attendance status (Present/Absent).
     * @param string $comment          Attendance note or remark.
     * @param string $attendence_type  Type of attendance (e.g., Manual/Automatic).
     *
     * @return int|false Number of rows affected or false on failure.
     */
    public function mjschool_insert_student_attendance( $curr_date, $class_id, $user_id, $attend_by, $status, $comment, $attendence_type )
    {
        global $wpdb;
        $table_name            = $wpdb->prefix . 'mjschool_attendence';
        $curr_date             = date('Y-m-d', strtotime($curr_date));
        $check_insrt_or_update = $this->mjschool_check_has_attendace($user_id, $class_id, $curr_date);
        if (empty($check_insrt_or_update) ) {
            // Sanitize inputs.
            $attend_by       = sanitize_text_field($attend_by);
            $status          = sanitize_text_field($status);
            $comment         = sanitize_textarea_field($comment);
            $attendence_type = sanitize_text_field($attendence_type);
            // Insert sanitized data.
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->insert(
                $table_name,
                array(
                'attendence_date' => sanitize_text_field($curr_date),
                'attend_by'       => intval($attend_by),
                'class_id'        => intval($class_id),
                'user_id'         => intval($user_id),
                'status'          => $status,
                'role_name'       => 'student',
                'comment'         => $comment,
                'attendence_type' => $attendence_type,
                ),
                array( '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s' ) // Define types
            );
        } else {
            // Sanitize inputs.
            $attend_by       = sanitize_text_field($attend_by);
            $status          = sanitize_text_field($status);
            $comment         = sanitize_textarea_field($comment);
            $attendence_type = sanitize_text_field($attendence_type);
            // Update sanitized data.
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->update(
                $table_name,
                array(
                'attend_by'       => $attend_by,
                'status'          => $status,
                'comment'         => $comment,
                'attendence_type' => $attendence_type,
                ),
                array(
                'attendence_date' => sanitize_text_field($curr_date),
                'class_id'        => intval($class_id),
                'user_id'         => intval($user_id),
                ),
                array( '%d', '%s', '%s', '%s' ), // Define types for `set` values.
                array( '%s', '%d', '%d' )       // Define types for `where` values.
            );
        }
        return $savedata;
    }
     /**
     * Insert or update subject-wise attendance.
	 * 
	 * @since 1.0.0
     *
     * @param string $curr_date        Attendance date.
     * @param int    $class_id         Class ID.
     * @param int    $user_id          Student user ID.
     * @param int    $attend_by        Attendance marked by user ID.
     * @param string $status           Attendance status (Present/Absent).
     * @param int    $sub_id           Subject ID.
     * @param string $comment          Optional comment or remark.
     * @param string $attendence_type  Attendance type.
     * @param int    $section_id       Section ID.
     *
     * @return int|false Number of rows affected or false on failure.
     */
    public function mjschool_insert_subject_wise_attendance( $curr_date, $class_id, $user_id, $attend_by, $status, $sub_id, $comment, $attendence_type, $section_id )
    {
        if (empty($sub_id) ) {
            $categories = 'class';
        } else {
            $categories = 'subject';
        }
        if (empty($section_id) ) {
            $section_id = null;
        }
        if (empty($sub_id) ) {
            $sub_id = null;
        }
        global $wpdb;
        $table_name            = $wpdb->prefix . 'mjschool_sub_attendance';
        $curr_date             = date('Y-m-d', strtotime($curr_date));
        $check_insrt_or_update = $this->mjschool_check_has_subject_attendace($user_id, $class_id, $curr_date, $sub_id, $section_id);
        if (empty($check_insrt_or_update) ) {
            // Sanitize inputs.
            $curr_date       = sanitize_text_field($curr_date);
            $attend_by       = sanitize_text_field($attend_by);
            $class_id        = intval($class_id);
            $sub_id          = intval($sub_id);
            $user_id         = intval($user_id);
            $status          = sanitize_text_field($status);
            $comment         = sanitize_textarea_field($comment);
            $attendence_type = sanitize_text_field($attendence_type);
            $categories      = sanitize_text_field($categories);
            $section_id      = intval($section_id);
            // Insert sanitized data.
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->insert(
                $table_name,
                array(
                'attendance_date' => $curr_date,
                'attend_by'       => $attend_by,
                'class_id'        => $class_id,
                'sub_id'          => $sub_id,
                'user_id'         => $user_id,
                'status'          => $status,
                'role_name'       => 'student',
                'comment'         => $comment,
                'attendence_type' => $attendence_type,
                'categories'      => $categories,
                'section_id'      => $section_id,
                ),
                array( '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d' ) // Specify data types
            );
        } else {
            // Sanitize inputs.
            $curr_date       = sanitize_text_field($curr_date);
            $attend_by       = intval($attend_by);
            $class_id        = intval($class_id);
            $sub_id          = intval($sub_id);
            $user_id         = intval($user_id);
            $status          = sanitize_text_field($status);
            $comment         = sanitize_textarea_field($comment);
            $attendence_type = sanitize_text_field($attendence_type);
            $categories      = sanitize_text_field($categories);
            $section_id      = intval($section_id);
            // Prepare data for update.
            $attendace_data = array(
            'attend_by'       => $attend_by,
            'status'          => $status,
            'comment'         => $comment,
            'role_name'       => 'student',
            'attendence_type' => $attendence_type,
            'categories'      => $categories,
            'section_id'      => $section_id,
            'sub_id'          => $sub_id,
            'attendance_date' => $curr_date,
            'class_id'        => $class_id,
            'user_id'         => $user_id,
            );
            // Prepare where clause.
            $where_id = array( 'attendance_id' => intval($check_insrt_or_update->attendance_id) );
            // Update sanitized data.
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->update(
                $table_name,
                $attendace_data,
                $where_id,
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d' ), // Data types for update
                array( '%d' ) // Data type for where clause
            );
        }
        return $savedata;
    }
	/**
     * Check if a student's attendance record already exists.
	 * 
	 * @since 1.0.0
     *
     * @param int    $user_id        User ID.
     * @param int    $class_id       Class ID.
     * @param string $attendace_date Attendance date.
     *
     * @return object|null Attendance record or null if not found.
     */
    public function mjschool_check_has_attendace( $user_id, $class_id, $attendace_date )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $results = $wpdb->get_row("SELECT * FROM $table_name WHERE attendence_date='$attendace_date' and class_id=$class_id and user_id =" . $user_id);
    }
	 /**
     * Check if a subject-wise attendance record exists for a user.
	 * 
	 * @since 1.0.0
     *
     * @param int    $user_id        User ID.
     * @param int    $class_id       Class ID.
     * @param string $attendace_date Attendance date.
     * @param int    $sub_id         Subject ID.
     * @param int    $section_id     Section ID.
     *
     * @return object|null Record object or null if not found.
     */
    public function mjschool_check_has_subject_attendace( $user_id, $class_id, $attendace_date, $sub_id, $section_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_sub_attendance';
        if (! empty($class_id) && ! empty($sub_id) && ! empty($section_id) ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $table_name WHERE attendance_date = %s AND class_id = %d AND sub_id = %d AND section_id = %d AND user_id = %d", $attendace_date, $class_id, $sub_id, $section_id, $user_id )
            );
        } elseif (! empty($class_id) && empty($sub_id) && ! empty($section_id) ) {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $table_name WHERE attendance_date = %s AND class_id = %d AND section_id = %d AND user_id = %d AND sub_id =%d", $attendace_date, $class_id, $section_id, $user_id, $sub_id, )
            );
        } elseif (! empty($class_id) && ! empty($sub_id) && empty($section_id) ) {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $table_name WHERE attendance_date = %s AND class_id = %d AND sub_id = %d AND user_id = %d AND section_id =%d", $attendace_date, $class_id, $sub_id, $user_id, $section_id, )
            );
        } else {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT * FROM $table_name WHERE attendance_date = %s AND class_id = %d AND user_id = %d AND section_id =%d AND sub_id =%d", $attendace_date, $class_id, $user_id, $section_id, $sub_id )
            );
        }
        return $results;
    }
    /**
	 * Insert or update a teacher's attendance record.
	 * 
	 * @since 1.0.0
	 *
	 * Checks if an attendance record already exists for the given date, and either
	 * inserts a new record or updates the existing one.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $curr_date Date of the attendance record (e.g., 'Y-m-d').
	 * @param int    $user_id   ID of the teacher user.
	 * @param int    $attend_by ID of the user who recorded the attendance.
	 * @param string $status    Attendance status (e.g., 'Present', 'Absent', 'Late').
	 * @param string $comment   Optional comment for the attendance.
	 *
	 * @return int|false The number of rows inserted/updated, or false on error.
	 */
    public function mjschool_insert_teacher_attendance( $curr_date, $user_id, $attend_by, $status, $comment )
    {
        $class_id = 0;
        global $wpdb;
        $table_name            = $wpdb->prefix . 'mjschool_attendence';
        $check_insrt_or_update = $this->mjschool_check_has_attendace($user_id, $class_id, $curr_date);
        $curr_date             = date('Y-m-d', strtotime($curr_date));
        if (empty($check_insrt_or_update) ) {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->insert(
                $table_name,
                array(
                'attendence_date' => $curr_date,
                'attend_by'       => $attend_by,
                'class_id'        => $class_id,
                'user_id'         => $user_id,
                'status'          => $status,
                'role_name'       => 'teacher',
                'comment'         => $comment,
                )
            );
        } else {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $savedata = $wpdb->update(
                $table_name,
                array(
                'attend_by' => $attend_by,
                'status'    => $status,
                'comment'   => $comment,
                ),
                array(
                'attendence_date' => $curr_date,
                'class_id'        => $class_id,
                'user_id'         => $user_id,
                )
            );
        }
    }
	/**
	 * Save or update daily student attendance for a specific class.
	 * 
	 * @since 1.0.0
	 *
	 * Retrieves the list of students for the given class, checks if attendance
	 * has already been recorded for today, and either updates existing records
	 * or inserts new records for all students based on the provided attendance array.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $curr_date  The current date for the attendance record. Expected format 'Y-m-d'.
	 * @param int    $class_id   The ID of the class whose attendance is being recorded.
	 * @param array  $attendence Array of user IDs (int) of students who have the main status (e.g., 'Present').
	 * @param int    $attend_by  ID of the user who recorded the attendance (e.g., a teacher).
	 * @param string $status     The main attendance status being recorded (e.g., 'Present'). Students not in $attendence will receive the opposite status.
	 *
	 * @return int|false The result of the final database operation (number of rows inserted/updated, or false on error).
	 */
    public function mjschool_save_attendence( $curr_date, $class_id, $attendence, $attend_by, $status )
    {
        global $wpdb;
        $role       = 'student';
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $exlude_id  = mjschool_approve_student_list();
        $students = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ));
        if ($status == 'Present' ) {
            $new_status = 'Absent';
        } else {
            $new_status = 'Present';
        }
        $record_status          = '';
        $check_today_attendence = $this->mjschool_show_today_attendence($class_id, $role);
        $record_status          = '';
        $curr_date              = date('Y-m-d');
        foreach ( $check_today_attendence as $today_data ) {
            if ($today_data['class_id'] == $class_id && $today_data['attendence_date'] == $curr_date ) {
                $record_status = 'update';
            }
        }
        if ($record_status == 'update' ) {
            return $savedata = $this->mjschool_update_attendence($students, $curr_date, $class_id, $attendence, $attend_by, $status, $table_name);
        } else {
            foreach ( $students as $stud ) {
                if (in_array($stud->ID, $attendence) ) {
                    // Sanitize and insert data.
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $savedata = $wpdb->insert(
                        $table_name,
                        array(
                        'attendence_date' => sanitize_text_field($curr_date),
                        'attend_by'       => intval($attend_by),
                        'class_id'        => intval($class_id),
                        'user_id'         => intval($stud->ID),
                        'status'          => sanitize_text_field($status),
                        'role_name'       => sanitize_text_field($role),
                        ),
                        array( '%s', '%d', '%d', '%d', '%s', '%s' ) // Define data types
                    );
                } else {
                    // Sanitize and insert data.
                 	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $savedata = $wpdb->insert(
                        $table_name,
                        array(
                        'attendence_date' => sanitize_text_field($curr_date),
                        'attend_by'       => sanitize_text_field($attend_by),
                        'class_id'        => intval($class_id),
                        'user_id'         => intval($stud->ID),
                        'status'          => sanitize_text_field($new_status),
                        'role_name'       => sanitize_text_field($role),
                        ),
                        array( '%s', '%d', '%d', '%d', '%s', '%s' ) // Define data types
                    );
                }
            }
            if ($savedata ) {
                return $savedata;
            }
        }
    }
	/**
	 * Update existing student attendance records for a class on a specific date.
	 *
	 * Iterates through a list of students for a class and updates their attendance
	 * status based on whether their ID is present in the provided attendance array.
	 * 
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array  $students   Array of student user objects to update.
	 * @param string $curr_date  The attendance date (e.g., 'Y-m-d').
	 * @param int    $class_id   The ID of the class.
	 * @param array  $attendence Array of user IDs (int) of students who are marked with the main status ($status).
	 * @param int    $attend_by  ID of the user who recorded the attendance (e.g., a teacher).
	 * @param string $status     The main attendance status (e.g., 'Present'). Students not in $attendence get the opposite status.
	 * @param string $table_name The name of the database table to update.
	 *
	 * @return int|false The number of rows affected by the last update query, or false on error.
	 */
    public function mjschool_update_attendence( $students, $curr_date, $class_id, $attendence, $attend_by, $status, $table_name )
    {
        global $wpdb;
        if ($status == 'Present' ) {
            $new_status = 'Absent';
        } else {
            $new_status = 'Present';
        }
        foreach ( $students as $stud ) {
            if (in_array($stud->ID, $attendence) ) {
                // Sanitize and update data.
             	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->update(
                    $table_name,
                    array(
                    'attend_by' => intval($attend_by),
                    'status'    => sanitize_text_field($status),
                    ),
                    array(
                    'attendence_date' => sanitize_text_field($curr_date),
                    'class_id'        => intval($class_id),
                    'user_id'         => intval($stud->ID),
                    ),
                    array( '%d', '%s' ), // Define data types for update values.
                    array( '%s', '%d', '%d' ) // Define data types for where clause.
                );
            } else {
                // Sanitize and update data.
             	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->update(
                    $table_name,
                    array(
                    'attend_by' => sanitize_text_field($attend_by),
                    'status'    => sanitize_text_field($new_status),
                    ),
                    array(
                    'attendence_date' => sanitize_text_field($curr_date),
                    'class_id'        => intval($class_id),
                    'user_id'         => intval($stud->ID),
                    ),
                    array( '%d', '%s' ), // Define data types for update values.
                    array( '%s', '%d', '%d' ) // Define data types for where clause.
                );
            }
        }
        return $result;
    }
	/**
	 * Save or update daily attendance records for all teachers.
	 *
	 * Checks if attendance has already been recorded for teachers on the given date,
	 * and either updates existing records or inserts new records for all teachers
	 * based on the provided attendance array.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param string $curr_date  The attendance date (e.g., 'Y-m-d').
	 * @param array  $attendence Array of user IDs (int) of teachers who are marked with the main status ($status).
	 * @param int    $attend_by  ID of the user who recorded the attendance.
	 * @param string $status     The main attendance status being recorded (e.g., 'Present'). Teachers not in $attendence get the opposite status.
	 *
	 * @return int|false The result of the final database operation (number of rows inserted/updated, or false on error).
	 */
    public function mjschool_save_teacher_attendence( $curr_date, $attendence, $attend_by, $status )
    {
        $role = 'teacher';
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        if ($status == 'Present' ) {
            $new_status = 'Absent';
        } else {
            $new_status = 'Present';
        }
        $record_status          = '';
        $check_today_attendence = $this->mjschool_show_today_teacher_attendence($role);
        $record_status          = '';
        $curr_date              = $curr_date;
        foreach ( $check_today_attendence as $today_data ) {
            if ($today_data['attendence_date'] == $curr_date ) {
                $record_status = 'update';
            }
        }
        if ($record_status == 'update' ) {
            return $savedata = $this->mjschool_update_teacher_attendence($curr_date, $attendence, $attend_by, $status, $table_name);
        } else {
            foreach ( mjschool_get_users_data('teacher') as $stud ) {
                if (in_array($stud->ID, $attendence) ) {
                    $class_id = get_user_meta($stud->ID, 'class_name', true);
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $result = $wpdb->insert(
                        $table_name,
                        array(
                        'attendence_date' => $curr_date,
                        'attend_by'       => $attend_by,
                        'user_id'         => $stud->ID,
                        'status'          => $status,
                        'role_name'       => $role,
                        'class_id'        => $class_id,
                        )
                    );
                } else {
                 	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $result = $wpdb->insert(
                        $table_name,
                        array(
                        'attendence_date' => $curr_date,
                        'attend_by'       => $attend_by,
                        'user_id'         => $stud->ID,
                        'status'          => $new_status,
                        'role_name'       => $role,
                        'class_id'        => $class_id,
                        )
                    );
                }
            }
            return $result;
        }
    }
	/**
	 * Update existing attendance records for all teachers on a specific date.
	 *
	 * Iterates through all teacher users and updates their attendance status in the database
	 * based on whether their ID is present in the provided attendance array.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param string $curr_date  The attendance date (e.g., 'Y-m-d').
	 * @param array  $attendence Array of user IDs (int) of teachers marked with the main status ($status).
	 * @param int    $attend_by  ID of the user who recorded the attendance.
	 * @param string $status     The main attendance status (e.g., 'Present'). Teachers not in $attendence get the opposite status.
	 * @param string $table_name The name of the database table to update.
	 *
	 * @return int|false The number of rows affected by the last update query, or false on error.
	 */
    public function mjschool_update_teacher_attendence( $curr_date, $attendence, $attend_by, $status, $table_name )
    {
        global $wpdb;
        if ($status == 'Present' ) {
            $new_status = 'Absent';
        } else {
            $new_status = 'Present';
        }
        foreach ( mjschool_get_users_data('teacher') as $stud ) {
            // Sanitize inputs.
            $attend_by  = intval($attend_by);
            $status     = sanitize_text_field($status);
            $new_status = sanitize_text_field($new_status);
            $curr_date  = sanitize_text_field($curr_date);
            $user_id    = intval($stud->ID);  // Ensure ID is an integer.
            // Prepare and execute the update query with placeholders.
            if (in_array($user_id, $attendance) ) {
             	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->update(
                    $table_name,
                    array(
                    'attend_by' => $attend_by,
                    'status'    => $status,
                    ),
                    array(
                    'attendence_date' => $curr_date,
                    'user_id'         => $user_id,
                    ),
                    array( '%d', '%s' ), // Format for the values.
                    array( '%s', '%d' )  // Format for the conditions.
                );
            } else {
             // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->update(
                    $table_name,
                    array(
                    'attend_by' => $attend_by,
                    'status'    => $new_status,
                    ),
                    array(
                    'attendence_date' => $curr_date,
                    'user_id'         => $user_id,
                    ),
                    array( '%d', '%s' ), // Format for the values.
                    array( '%s', '%d' )  // Format for the conditions.
                );
            }
        }
        return $result;
    }
    public function mjschool_show_today_attendence( $class_id, $role )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = date('Y-m-d');
        $curr_date  = sanitize_text_field($curr_date); // Sanitize input.
        $class_id   = intval($class_id); // Ensure class ID is an integer.
        $role       = sanitize_text_field($role); // Sanitize input.
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE attendence_date = %s AND class_id = %d AND role_name = %s", $curr_date, $class_id, $role );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $results = $wpdb->get_results($query, ARRAY_A);
        return $results;
    }
	/**
	 * Retrieve today's attendance records for a specific class and user role.
	 *
	 * Queries the database for all attendance entries matching the current date,
	 * a specified class ID, and a user role (e.g., 'student').
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param string $role     The role of the users (e.g., 'student') whose attendance is being checked.
	 *
	 * @return array|null An array of attendance records (associative arrays) for today, or null if no records are found.
	 */
    public function mjschool_show_today_teacher_attendence( $role )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = date('Y-m-d');
        $curr_date  = sanitize_text_field($curr_date); // Sanitize input.
        $role       = sanitize_text_field($role); // Sanitize input.
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE attendence_date = %s AND role_name = %s", $curr_date, $role );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $results = $wpdb->get_results($query, ARRAY_A);
        return $results;
    }
	/**
	 * Check if a user has a 'Present' attendance record for a specific class and date.
	 *
	 * Queries the attendance table to determine if an entry exists for the given user,
	 * class, and date, specifically checking for a 'Present' status.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int    $userid   ID of the user (student or teacher) to check attendance for.
	 * @param int    $class_id ID of the class associated with the attendance record.
	 * @param string $date     The date of the attendance record (expected format 'Y-m-d').
	 *
	 * @return bool True if a 'Present' attendance record is found, false otherwise.
	 */
    public function mjschool_get_attendence( $userid, $class_id, $date )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = $date;
        $curr_date  = sanitize_text_field($curr_date); // Sanitize input.
        $class_id   = intval($class_id); // Ensure class ID is an integer.
        $userid     = intval($userid); // Ensure user ID is an integer.
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare( "SELECT * FROM $table_name WHERE attendence_date = %s AND class_id = %d AND user_id = %d AND status = %s", $curr_date, $class_id, $userid, 'Present' );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_var($query);
        if ($result ) {
            return true;
        } else {
            return false;
        }
    }
	/**
	 * Retrieve all records from the attendance table.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @return array|object|null An array of objects/rows from the attendance table, or null if no results are found.
	 */
    public function mjschool_get_all_attendence()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name"));
        return $result;
    }
	/**
	 * Retrieve all records from the subject-wise attendance table.
	 *
	 * Queries the `mjschool_sub_attendance` table to fetch all entries.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @return array|object|null An array of objects/rows from the subject attendance table, or null if no results are found.
	 */
    public function mjschool_get_all_attendence_with_subject()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_sub_attendance';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name"));
        return $result;
    }
	/**
	 * Retrieve the attendance status records for a specific student.
	 *
	 * Queries the subject attendance table to fetch all status entries for a given
	 * user ID with the role 'student'.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int $uid ID of the student user.
	 *
	 * @return array|object|null An array of objects/rows containing only the 'status' column, or null if no results are found.
	 */
    public function mjschool_get_students( $uid )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_sub_attendance';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT status FROM $table_name where user_id=%d and role_name=%s", intval($uid), 'student'));
        return $result;
    }
	/**
	 * Check for a daily attendance record for a user in a specific class on a given date.
	 *
	 * Queries the main attendance table to fetch a single record that matches
	 * the user ID, class ID, and date.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int    $userid   ID of the user (student or teacher).
	 * @param int    $class_id ID of the class.
	 * @param string $date     The date of the attendance record (e.g., 'Y-m-d').
	 *
	 * @return object|null The full record object if found, or null if no record exists.
	 */
    public function mjschool_check_attendence( $userid, $class_id, $date )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = date('Y-m-d', strtotime($date));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE attendence_date=%s and class_id=%d and user_id=%d", $date, $class_id, $userid));
        return $result;
    }
	/**
	 * Check for a subject-wise attendance record for a user.
	 *
	 * Queries the subject attendance table to fetch a single record that matches
	 * the user ID, class ID, date, and subject ID.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int    $userid   ID of the user.
	 * @param int    $class_id ID of the class.
	 * @param string $date     The date of the attendance record (e.g., 'Y-m-d').
	 * @param int    $sub_id   ID of the subject.
	 *
	 * @return object|null The full record object if found, or null if no record exists.
	 */
    public function mjschool_check_sub_attendence( $userid, $class_id, $date, $sub_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_sub_attendance';
        $curr_date  = date('Y-m-d', strtotime($date));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE attendance_date = %s AND class_id = %d AND sub_id = %d AND user_id = %d", $curr_date, $class_id, $sub_id, $userid));
        return $result;
    }
	/**
	 * Check if a teacher has a 'Present' attendance record for a specific date.
	 *
	 * Queries the main attendance table to check for a 'Present' status for the
	 * given teacher ID and date.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int    $userid ID of the teacher user.
	 * @param string $date   The date of the attendance record (expected format 'Y-m-d').
	 *
	 * @return bool True if a 'Present' attendance record is found, false otherwise.
	 */
    public function mjschool_get_teacher_attendence( $userid, $date )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = $date;
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_var($wpdb->prepare("SELECT * FROM $table_name WHERE attendence_date = %s AND user_id = %d AND status = %s", $curr_date, $userid, 'Present'));
        if ($result ) {
            return true;
        } else {
            return false;
        }
    }
	/**
	 * Count the total number of 'Present' attendance records for the current date.
	 *
	 * Queries the main attendance table to count all users (students and teachers)
	 * who have a status of 'Present' for today's date.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @return int The total number of 'Present' records for today.
	 */
    public function mjschool_today_presents()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
        $curr_date  = date('Y-m-d');
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE attendence_date = %s AND status = %s", $curr_date, 'Present'));
    }
	/**
	 * Retrieve all attendance records for a specific user (used primarily for teachers).
	 *
	 * Queries the main attendance table to fetch every record associated with the given user ID.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * 
	 * @since 1.0.0
	 *
	 * @param int $user_id ID of the user (e.g., a teacher) to retrieve all attendance records for.
	 *
	 * @return array|object|null An array of objects/rows containing all attendance records for the user, or null if no results are found.
	 */
    public function mjschool_get_all_user_teacher_attendence( $user_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_attendence';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name where user_id= %d ", $user_id));
        return $result;
    }
}
