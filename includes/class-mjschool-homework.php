<?php
/**
 * Homework Management Class
 *
 * This class handles all database operations and business logic related to
 * managing homework assignments, submissions, and status for various user roles
 * (students, parents, teachers) in the mjschool system.
 *
 * @package    Mjschool
 * @subpackage Mjschool/includes
 */
defined('ABSPATH') || exit;
/**
 * Core class for Mjschool Homework.
 *
 * Defines all methods for homework CRUD operations, validation, and retrieval
 * based on user roles and submission status.
 *
 * @since      1.0.0
 * @package    Mjschool
 * @subpackage Mjschool/includes
 */
class Mjschool_Homework
{
    /**
     * Checks if a filename has a valid image extension.
     *
     * Returns:
     * 2 if filename is empty.
     * 0 if extension is invalid.
     * 1 if extension is valid.
     *
     * @param  string $filename The name of the file to check.
     * @return int 2, 0, or 1 indicating the validity status.
     * @since  1.0.0
     */
    public function mjschool_check_valid_extension( $filename )
    {
        $flag = 2;
        if ($filename != '' ) {
            $flag            = 0;
            $ext             = pathinfo($filename, PATHINFO_EXTENSION);
            $valid_extension = array( 'gif', 'png', 'jpg', 'jpeg' );
            if (in_array($ext, $valid_extension) ) {
                $flag = 1;
            }
        }
        return $flag;
    }
    /**
     * Deletes records from a specified database table based on homework ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  string $mjschool_table_name The name of the table to delete from (excluding prefix).
     * @param  int    $record_id           The ID of the homework record to delete.
     * @return int|bool The number of rows deleted on success, or false on error.
     * @since  1.0.0
     */
    function mjschool_get_delete_records( $mjschool_table_name, $record_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $mjschool_table_name;
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE homework_id= %d", $record_id));
    }
    /**
     * Checks if a file has been uploaded for a specific student homework submission.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $assign_id The ID of the student homework submission (`stu_homework_id`).
     * @return string|bool The filename if a file exists, or false otherwise.
     * @since  1.0.0
     */
    public function mjschool_check_uploaded( $assign_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_student_homework';
        $query = $wpdb->prepare("SELECT file FROM {$table} WHERE stu_homework_id = %d", $assign_id);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($query, ARRAY_A);
        if ($result['file'] != '' ) {
            return $result['file'];
        } else {
            return false;
        }
    }
    /**
     * Retrieves a single student homework submission record by its ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $stu_homework_id The ID of the student homework submission.
     * @return object|null Database row object or null if no result is found.
     * @since  1.0.0
     */
    public function mjschool_get_student_submitted_homework( $stu_homework_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_student_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE stu_homework_id =%d", $stu_homework_id));
        return $result;
    }
    /**
     * Retrieves all homework assignments created in the system.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of homework objects, or an empty array on failure.
     * @since  1.0.0
     */
    function mjschool_get_class_homework()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_results("SELECT * FROM $table_name");
    }
    /**
     * Retrieves a list of student submissions for a specific homework assignment.
     *
     * Performs a LEFT JOIN between the homework and student homework tables.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $data The ID of the homework assignment.
     * @return array Array of combined homework and submission objects.
     * @since  1.0.0
     */
    function mjschool_view_submission( $data )
    {
        global $wpdb;
        $table_name  = $wpdb->prefix . 'mjschool_homework';
        $table_name2 = $wpdb->prefix . 'mjschool_student_homework';
        $query       = $wpdb->prepare("SELECT * FROM {$table_name} AS a LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id WHERE a.homework_id = %d", $data);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves a detailed view of current/upcoming homework for the logged-in parent's children.
     *
     * Fetches assignments whose submission date is greater than or equal to the current date.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current user (parent).
     * @return array Array of unique homework objects for all children.
     * @since  1.0.0
     */
    function mjschool_parent_view_detail()
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        global $user_ID;
        $child = mjschool_get_parents_child_id($user_ID);
        foreach ( $child as $student_id ) {
            // Use prepared statement to securely query the database.
            $class_id = intval(get_user_meta($student_id, 'class_name', true));
            $query    = $wpdb->prepare(
                "SELECT * FROM {$table_name} AS a 
				LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
				WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date >= %s",
                $student_id,
                $class_id,
                $current_date
            );
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result[] = $wpdb->get_results($query);
        }
        if (! empty($result) ) {
            $mergedArray   = array_merge(...$result);
            $homework_data = array_unique($mergedArray, SORT_REGULAR);
        } else {
            $homework_data = array();
        }
        return $homework_data;
    }
    /**
     * Retrieves upcoming homework for the logged-in parent's children (identical to mjschool_parent_view_detail).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current user (parent).
     * @return array Array of unique upcoming homework objects for all children.
     * @since  1.0.0
     */
    function mjschool_parent_upcoming_homework()
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        global $user_ID;
        $child = mjschool_get_parents_child_id($user_ID);
        foreach ( $child as $student_id ) {
            // Use prepared statement to securely query the database.
            $class_id = intval(get_user_meta($student_id, 'class_name', true));
            $query    = $wpdb->prepare(
                "SELECT * FROM {$table_name} AS a 
				LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
				WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date >= %s",
                $student_id,
                $class_id,
                $current_date
            );
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result[] = $wpdb->get_results($query);
        }
        if (! empty($result) ) {
            $mergedArray   = array_merge(...$result);
            $homework_data = array_unique($mergedArray, SORT_REGULAR);
        } else {
            $homework_data = array();
        }
        return $homework_data;
    }
    /**
     * Retrieves past (closed) homework for the logged-in parent's children.
     *
     * Fetches assignments whose submission date is less than the current date.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current user (parent).
     * @return array Array of unique closed homework objects for all children.
     * @since  1.0.0
     */
    function mjschool_parent_closed_homework()
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        global $user_ID;
        $child = mjschool_get_parents_child_id($user_ID);
        foreach ( $child as $student_id ) {
            // Use prepared statement to securely query the database.
            $class_id = intval(get_user_meta($student_id, 'class_name', true));
            $query    = $wpdb->prepare(
                "SELECT * FROM {$table_name} AS a 
				LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
				WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date < %s",
                $student_id,
                $class_id,
                $current_date
            );
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result[] = $wpdb->get_results($query);
        }
        if (! empty($result) ) {
            $mergedArray   = array_merge(...$result);
            $homework_data = array_unique($mergedArray, SORT_REGULAR);
        } else {
            $homework_data = array();
        }
        return $homework_data;
    }
    /**
     * Retrieves a limited list of current/upcoming homework for the parent's dashboard view.
     *
     * Fetches the 5 most recent upcoming assignments.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current user (parent).
     * @param  array $child_ids This parameter is passed but not used inside the function, relying on `mjschool_get_parents_child_id`.
     * @return array Array of unique, recent upcoming homework objects for all children.
     * @since  1.0.0
     */
    function mjschool_parent_view_detail_for_dashboard( $child_ids )
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        global $user_ID;
        $child = mjschool_get_parents_child_id($user_ID);
        foreach ( $child as $student_id ) {
            $class_id   = intval(get_user_meta($student_id, 'class_name', true));
            $student_id = intval($student_id); // Ensure student ID is an integer.
            // Use prepared statement to securely query the database.
            $query = $wpdb->prepare(
                "SELECT * FROM {$table_name} AS a 
				LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
				WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date >= %s
				ORDER BY a.homework_id DESC 
				LIMIT 5",
                $student_id,
                $class_id,
                $current_date
            );
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result[] = $wpdb->get_results($query);
        }
        if (! empty($result) ) {
            $mergedArray   = array_merge(...$result);
            $homework_data = array_unique($mergedArray, SORT_REGULAR);
        } else {
            $homework_data = array();
        }
        return $homework_data;
    }
    /**
     * Retrieves all homework assignments for the logged-in student.
     *
     * Joins the main homework table with the student submission table for a comprehensive list.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current student user.
     * @return array Array of homework objects relevant to the student.
     * @since  1.0.0
     */
    function mjschool_student_view_detail()
    {
        global $wpdb;
        global $user_ID;
        $current_date = date('Y-m-d');
        $class_id     = intval(get_user_meta($user_ID, 'class_name', true));
        $user_ID      = intval($user_ID); // Ensure user_ID is an integer.
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} AS a 
			LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
			WHERE b.student_id = %d AND a.class_name = %d",
            $user_ID,
            $class_id
        );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves upcoming homework for the logged-in student.
     *
     * Filters assignments where the submission date is on or after the current date.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current student user.
     * @return array Array of upcoming homework objects for the student.
     * @since  1.0.0
     */
    function mjschool_student_view_upcoming_homework()
    {
        global $wpdb;
        global $user_ID;
        $current_date = date('Y-m-d');
        $class_id     = intval(get_user_meta($user_ID, 'class_name', true));
        $user_ID      = intval($user_ID); // Ensure user_ID is an integer.
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} AS a 
			LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
			WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date >= %s",
            $user_ID,
            $class_id,
            $current_date
        );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves closed (past submission date) homework for the logged-in student.
     *
     * Filters assignments where the submission date is before the current date.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current student user.
     * @return array Array of closed homework objects for the student.
     * @since  1.0.0
     */
    function mjschool_student_view_closed_homework()
    {
        global $wpdb;
        global $user_ID;
        $current_date = date('Y-m-d');
        $class_id     = intval(get_user_meta($user_ID, 'class_name', true));
        $user_ID      = intval($user_ID); // Ensure user_ID is an integer.
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} AS a 
			LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
			WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date < %s",
            $user_ID,
            $class_id,
            $current_date
        );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves a limited list (last 5 upcoming) of homework for the student's dashboard view.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @global int $user_ID The ID of the current student user.
     * @return array Array of unique, recent upcoming homework objects for the student.
     * @since  1.0.0
     */
    function mjschool_student_view_detail_for_dashboard()
    {
        global $wpdb;
        global $user_ID;
        $current_date = date('Y-m-d');
        $user_ID      = intval($user_ID);
        $class_id     = intval(get_user_meta($user_ID, 'class_name', true));
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $table_name2  = $wpdb->prefix . 'mjschool_student_homework';
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} AS a 
			LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id
			WHERE b.student_id = %d AND a.class_name = %d AND a.submition_date >= %s
			ORDER BY a.homework_id DESC 
			LIMIT 5",
            $user_ID,
            $class_id,
            $current_date
        );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves details of a specific homework assignment and the submission status for a particular student (used by parent).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $data       The homework ID.
     * @param  int $student_id The ID of the student.
     * @return array Array of homework and submission details.
     * @since  1.0.0
     */
    function mjschool_parent_update_detail( $data, $student_id )
    {
        global $wpdb;
        global $user_ID;
        $table_name  = $wpdb->prefix . 'mjschool_homework';
        $table_name2 = $wpdb->prefix . 'mjschool_student_homework';
        // Use prepared statement to securely query the database.
        $query = $wpdb->prepare("SELECT * FROM {$table_name} AS a LEFT JOIN {$table_name2} AS b ON a.homework_id = b.homework_id WHERE a.homework_id = %d AND b.student_id = %d", $data, $student_id);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($query);
        return $result;
    }
    /**
     * Retrieves a single student's homework submission record by homework and student ID (likely for API use).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $data       The homework ID.
     * @param  int $student_id The ID of the student.
     * @return object|null Database row object of the submission, or null.
     * @since  1.0.0
     */
    function mjschool_parent_update_detail_api( $data, $student_id )
    {
        global $wpdb;
        global $user_ID;
        $table_name2 = $wpdb->prefix . 'mjschool_student_homework';
        $query       = $wpdb->prepare("SELECT * FROM {$table_name2} WHERE student_id = %d AND homework_id = %d", $student_id, $data);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($query);
        return $result;
    }
    /**
     * Handles the creation or update of a homework assignment and its distribution to students.
     *
     * Also handles audit logging and sending email/SMS/Push notifications.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $data          The homework data from the form submission.
     * @param  array $document_data Array of uploaded document details.
     * @return int|bool The last inserted ID on success, or the result of the update query (int 1 or 0), or false on error.
     * @since  1.0.0
     */
    function mjschool_add_homework( $data, $document_data )
    {
        global $wpdb;
        // $user                           = $current_user->user_login;
        $table_name                     = $wpdb->prefix . 'mjschool_homework';
        $table_name2                    = $wpdb->prefix . 'mjschool_student_homework';
        $homeworkdata['title']          = sanitize_text_field(stripslashes($data['title']));
        $homeworkdata['class_name']     = sanitize_text_field($data['class_name']);
        $homeworkdata['section_id']     = sanitize_text_field($data['class_section']);
        $homeworkdata['subject']        = sanitize_text_field($data['subject_id']);
        $homeworkdata['content']        = stripslashes(sanitize_textarea_field($data['content']));
        $homeworkdata['marks']          = sanitize_text_field($data['homework_marks']);
        $homeworkdata['created_date']   = date('Y-m-d H:i:s');
        $homeworkdata['submition_date'] = date('Y-m-d', strtotime($data['sdate']));
        $homeworkdata['createdby']      = get_current_user_id();
        $subject_name                   = mjschool_get_single_subject_name($data['subject_id']);
        if (! empty($_REQUEST['homework_id']) ) {
            $homework_id['homework_id']        = intval(mjschool_decrypt_id(sanitize_textarea_field(wp_unslash($_REQUEST['homework_id']))));
            $homeworkdata['homework_document'] = json_encode($document_data);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->update($table_name, $homeworkdata, $homework_id);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $last_homework_id = $wpdb->insert_id;
            $homework         = $homeworkdata['title'];
            mjschool_append_audit_log('' . esc_html__('Homework Updated', 'mjschool') . '( ' . $homework . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
            if ($result ) {
                $exlude_id = mjschool_approve_student_list();
                
                if (! empty($data['class_section']) ) {
                    $class_id = $data['class_name'];
                    $studentdata = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ));
                } else {
                    $studentdata = get_users(
                        array(
                        'meta_key' => 'class_section',
                        'meta_value' => $data['class_section'],
                        'meta_query' => array(array( 'key' => 'class_name', 'value' => $data['class_name'], 'compare' => '=' ) ),
                        'role' => 'student',
                        'exclude' => $exlude_id
                        ) 
                    );
                }
                
                $homeworstud['homework_id'] = $last_homework_id;
                foreach ( $studentdata as $student ) {
                    $homeworstud['student_id'] = $student->ID;
                 	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $result = $wpdb->insert($table_name2, $homeworstud);
                }
            }
            $device_token[] = get_user_meta($user_id, 'token_id', true);
            return $result;
        } else {
            $homeworkdata['homework_document'] = json_encode($document_data);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_name, $homeworkdata);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $last_homework_id = $wpdb->insert_id;
            $homework         = $homeworkdata['title'];
            mjschool_append_audit_log('' . esc_html__('Homework Added', 'mjschool') . '( ' . $homework . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
            if ($result ) {
                $exlude_id = mjschool_approve_student_list();
                
                if (empty($data['class_section']) ) {
                    $class_id = $data['class_name'];
                    $studentdata = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ));
                } else {
                    $studentdata = get_users(
                        array(
                        'meta_key' => 'class_section',
                        'meta_value' => $data['class_section'],
                        'meta_query' => array(array( 'key' => 'class_name', 'value' => $data['class_name'], 'compare' => '=' ) ),
                        'role' => 'student',
                        'exclude' => $exlude_id
                        ) 
                    );
                }
                
                if (! empty($studentdata) ) {
                    $homeworstud['homework_id']  = $last_homework_id;
                    $homeworstud['status']       = '0';
                    $homeworstud['created_by']   = get_current_user_id();
                    $homeworstud['created_date'] = date('Y-m-d H:i:s');
                    $device_token                = array();
                    foreach ( $studentdata as $student ) {
                        $homeworstud['student_id'] = $student->ID;
                     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                        $insert         = $wpdb->insert($table_name2, $homeworstud);
                        $device_token[] = get_user_meta($student->ID, 'token_id', true);
                    }
                    /* Start Send Push Notification. */
                    $title             = esc_attr__('New Notification For Homework', 'mjschool');
                    $text              = esc_attr__('New homework has been assign to you', 'mjschool');
                    $notification_data = array(
                    'registration_ids' => $device_token,
                    'data'             => array(
                    'title' => $title,
                    'body'  => $text,
                    'type'  => 'Message',
                    ),
                    );
                    $json              = json_encode($notification_data);
                    $push_notification = mjschool_send_push_notification($json);
                    /* End send push notification. */
                    if ($insert ) {
                        if (isset($data['mjschool_enable_homework_mail']) == '1' || isset($data['mjschool_enable_homework_mjschool_student']) == '1' || isset($data['mjschool_enable_homework_mjschool_parent']) == '1' ) {
                            foreach ( $studentdata as $userdata ) {
                                $student_id    = $userdata->ID;
                                $student_name  = $userdata->display_name;
                                $student_email = $userdata->user_email;
                                $parent        = get_user_meta($student_id, 'parent_id', true);
                                // Send mail notification for parent. //
                                if (isset($data['mjschool_enable_homework_mail']) == '1' ) {
                                    if (! empty($parent) ) {
                                        foreach ( $parent as $p ) {
                                            $user_info                             = get_userdata($p);
                                            $email_to                              = $user_info->user_email;
                                            $searchArr                             = array();
                                            $mjschool_parent_homework_mail_content = get_option('mjschool_parent_homework_mail_content');
                                            $mjschool_parent_homework_mail_subject = get_option('mjschool_parent_homework_mail_subject');
                                            $parerntdata                           = get_user_by('email', $email_to);
                                            $searchArr['{{parent_name}}']          = $parerntdata->display_name;
                                            $searchArr['{{student_name}}']         = $student_name;
                                            $searchArr['{{title}}']                = sanitize_textarea_field($data['title']);
                                            $searchArr['{{submition_date}}']       = mjschool_get_date_in_input_box($data['sdate']);
                                            $searchArr['{{homework_date}}']        = mjschool_get_date_in_input_box(date('Y-m-d H:i:s'));
                                            $searchArr['{{subject}}']              = $subject_name;
                                            $searchArr['{{school_name}}']          = get_option('mjschool_name');
                                            $message                               = mjschool_string_replacement($searchArr, $mjschool_parent_homework_mail_content);
                                            if (! empty($document_data[0]) ) {
                                                $attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
                                            } else {
                                                $attechment = '';
                                            }
                                            mjschool_send_mail_for_homework($email_to, $mjschool_parent_homework_mail_subject, $message, $attechment);
                                        }
                                    }
                                    // Send mail notification for student. //
                                    $string                       = array();
                                    $string['{{student_name}}']   = $student_name;
                                    $string['{{title}}']          = sanitize_textarea_field($data['title']);
                                    $string['{{submition_date}}'] = mjschool_get_date_in_input_box($data['sdate']);
                                    $string['{{homework_date}}']  = mjschool_get_date_in_input_box(date('Y-m-d H:i:s'));
                                    $string['{{subject}}']        = $subject_name;
                                    $string['{{school_name}}']    = get_option('mjschool_name');
                                    $msgcontent                   = get_option('mjschool_homework_mailcontent');
                                    $msgsubject                   = get_option('mjschool_homework_title');
                                    $student_message              = mjschool_string_replacement($string, $msgcontent);
                                    if (! empty($document_data[0]) ) {
                                        $attechment = WP_CONTENT_DIR . '/uploads/school_assets/' . $document_data[0]['value'];
                                    } else {
                                        $attechment = '';
                                    }
                                    $mail = mjschool_send_mail_for_homework($student_email, $msgsubject, $student_message, $attechment);
                                }
                                // Send SMS notification for student. //
                                if (isset($data['mjschool_enable_homework_mjschool_student']) == '1' ) {
                                    $SMSArr                     = array();
                                    $SMSCon                     = get_option('mjschool_homework_student_mjschool_content');
                                    $SMSArr['{{student_name}}'] = $student_name;
                                    $SMSArr['{{title}}']        = sanitize_textarea_field($data['title']);
                                    $SMSArr['{{date}}']         = mjschool_get_date_in_input_box($data['sdate']);
                                    $SMSArr['{{school_name}}']  = get_option('mjschool_name');
                                    $message_content            = mjschool_string_replacement($SMSArr, $SMSCon);
                                    $type                       = 'Homework';
                                    mjschool_send_mjschool_notification($userdata->ID, $type, $message_content);
                                }
                                // Send SMS notification for parent. //
                                if (isset($data['mjschool_enable_homework_mjschool_parent']) == '1' ) {
                                    if (! empty($parent) ) {
                                        foreach ( $parent as $p ) {
                                            $SMSArr                    = array();
                                            $user_info                 = get_userdata($p);
                                            $email_to                  = $user_info->user_email;
                                            $parerntdata               = get_user_by('email', $email_to);
                                            $SMSCon                    = get_option('mjschool_homework_parent_mjschool_content');
                                            $SMSArr['{{parent_name}}'] = $parerntdata->display_name;
                                            $SMSArr['{{title}}']       = sanitize_textarea_field($data['title']);
                                            $SMSArr['{{school_name}}'] = get_option('mjschool_name');
                                            $message_content           = mjschool_string_replacement($SMSArr, $SMSCon);
                                            $type                      = 'Homework';
                                            mjschool_send_mjschool_notification($p->ID, $type, $message_content);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $last_homework_id;
        }
    }
    /**
     * Retrieves all homework assignments in the system, ordered by submission date descending.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of all homework objects.
     * @since  1.0.0
     */
    function mjschool_get_all_homework_list()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name ORDER BY submition_date DESC"));
    }
    /**
     * Retrieves all upcoming homework assignments (submission date is on or after today).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of upcoming homework objects.
     * @since  1.0.0
     */
    function mjschool_get_all_upcoming_homework()
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $query        = $wpdb->prepare("SELECT * FROM $table_name WHERE submition_date >= %s", $current_date);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $results = $wpdb->get_results($query);
        return $results;
    }
    /**
     * Retrieves all closed homework assignments (submission date is before today).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of closed homework objects.
     * @since  1.0.0
     */
    function mjschool_get_all_closed_homework()
    {
        global $wpdb;
        $current_date = date('Y-m-d');
        $table_name   = $wpdb->prefix . 'mjschool_homework';
        $query        = $wpdb->prepare("SELECT * FROM $table_name WHERE submition_date < %s", $current_date);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $results = $wpdb->get_results($query);
        return $results;
    }
    /**
     * Retrieves all homework assignments created by the currently logged-in user.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of homework objects created by the current user.
     * @since  1.0.0
     */
    function mjschool_get_all_own_homeworklist()
    {
        global $wpdb;
        $get_current_user_id = get_current_user_id();
        $table_name          = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where createdby =%d ORDER BY submition_date DESC", $get_current_user_id));
    }
    /**
     * Retrieves upcoming homework assignments created by the currently logged-in user.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of upcoming homework objects created by the current user.
     * @since  1.0.0
     */
    function mjschool_get_all_own_upcoming_homeworklist()
    {
        global $wpdb;
        $current_date        = date('Y-m-d');
        $get_current_user_id = get_current_user_id();
        $table_name          = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where createdby =%d AND submition_date >= %s ORDER BY submition_date DESC", $get_current_user_id, $current_date));
    }
    /**
     * Retrieves closed homework assignments created by the currently logged-in user.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of closed homework objects created by the current user.
     * @since  1.0.0
     */
    function mjschool_get_all_own_closed_homeworklist()
    {
        global $wpdb;
        $current_date        = date('Y-m-d');
        $get_current_user_id = get_current_user_id();
        $table_name          = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where createdby =%d AND submition_date < %s ORDER BY submition_date DESC", $get_current_user_id, $current_date));
    }
    /**
     * Retrieves all homework for the classes managed by the current teacher.
     *
     * Note: The query uses `implode` directly on un-prepared data inside the SQL string,
     * which is a **potential SQL Injection risk** if the class name metadata is not strictly sanitized/integer-enforced.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of homework objects assigned to the teacher's classes.
     * @since  1.0.0
     */
    function mjschool_get_teacher_homeworklist()
    {
        global $wpdb;
        $class_name = array();
        $table_name = $wpdb->prefix . 'mjschool_homework';
        $class_name = get_user_meta(get_current_user_id(), 'class_name', true);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_results("SELECT * from $table_name where class_name IN( " . implode(',', $class_name) . ' )');
    }
    /**
     * Retrieves a single homework record for editing purposes.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $homework_id The ID of the homework assignment.
     * @return object|null Database row object or null.
     * @since  1.0.0
     */
    function mjschool_get_edit_record( $homework_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->get_row($wpdb->prepare("SELECT * from $table_name where homework_id=%d", $homework_id));
    }
    /**
     * Deletes a single homework record and logs the action to the audit log.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $homework_id The ID of the homework assignment to delete.
     * @return int|bool The number of rows deleted (1 on success), or false on error.
     * @since  1.0.0
     */
    function mjschool_get_delete_record( $homework_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_homework';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $home     = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name where homework_id= %d ", $homework_id));
        $homework = isset($home->title) ? $home->title : esc_html__('Unknown Homework', 'mjschool');
        mjschool_append_audit_log('' . esc_html__('Homework Deleted', 'mjschool') . '( ' . $homework . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $rows = $wpdb->query($wpdb->prepare("Delete from $table_name where homework_id= %d ", $homework_id));
    }
    /**
     * Retrieves all homework assignments assigned to the classes taught by the current teacher.
     *
     * Iterates over the teacher's class IDs to fetch relevant homework.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of unique homework objects for the teacher's classes.
     * @since  1.0.0
     */
    function mjschool_get_all_own_homework_list_for_teacher()
    {
        global $wpdb;
        $get_current_user_id = get_current_user_id();
        $current_date        = date('Y-m-d');
        $table_name          = $wpdb->prefix . 'mjschool_homework';
        $class               = get_user_meta($get_current_user_id, 'class_name', true);
        foreach ( $class as $class_id ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $rows[] = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where class_name = %d ORDER BY submition_date DESC", $class_id));
        }
        $mergedArray    = array_merge(...$rows);
        $retrieve_class_data = array_unique($mergedArray, SORT_REGULAR);
        return $retrieve_class_data;
    }
    /**
     * Retrieves upcoming homework for the classes taught by the current teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of unique upcoming homework objects for the teacher's classes.
     * @since  1.0.0
     */
    function mjschool_get_all_own_upcoming_homework_list_for_teacher()
    {
        global $wpdb;
        $get_current_user_id = get_current_user_id();
        $current_date        = date('Y-m-d');
        $table_name          = $wpdb->prefix . 'mjschool_homework';
        $class               = get_user_meta($get_current_user_id, 'class_name', true);
        foreach ( $class as $class_id ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $rows[] = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where class_name = %d AND submition_date >= %s ORDER BY submition_date DESC", $class_id, $current_date));
        }
        $mergedArray    = array_merge(...$rows);
        $retrieve_class_data = array_unique($mergedArray, SORT_REGULAR);
        return $retrieve_class_data;
    }
    /**
     * Retrieves closed homework for the classes taught by the current teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of unique closed homework objects for the teacher's classes.
     * @since  1.0.0
     */
    function mjschool_get_all_own_closed_homework_list_for_teacher()
    {
        global $wpdb;
        $get_current_user_id = get_current_user_id();
        $current_date        = date('Y-m-d');
        $table_name          = $wpdb->prefix . 'mjschool_homework';
        $class               = get_user_meta($get_current_user_id, 'class_name', true);
        foreach ( $class as $class_id ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $rows[] = $wpdb->get_results($wpdb->prepare("SELECT * from $table_name where class_name = %d AND submition_date < %s ORDER BY submition_date DESC", $class_id, $current_date));
        }
        $mergedArray    = array_merge(...$rows);
        $retrieve_class_data = array_unique($mergedArray, SORT_REGULAR);
        return $retrieve_class_data;
    }

    /**
     * Update homework evaluation details for a specific student homework entry.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int    $stud_homework_id The homework record ID to update.
     * @param string $file_name        The uploaded review file name.
     * @param string $obtain_marks     The marks obtained by the student.
     * @param string $teacher_comment  Teacher's evaluation comment.
     * @param string $evaluate_date    Evaluation date in proper format.
     * @param int    $status           Homework evaluation status (0/1 or similar).
     *
     * @return int|false Number of rows updated on success, or false on failure.
     */
    function mjschool_update_student_homework(
        $stud_homework_id,
        $file_name,
        $obtain_marks,
        $teacher_comment,
        $evaluate_date,
        $status
    ) {
        global $wpdb;

        // Table name
        $table_name = $wpdb->prefix . 'mjschool_student_homework';

        // Update data
        $data = array(
            'review_file'     => $file_name,
            'obtain_marks'    => $obtain_marks,
            'teacher_comment' => $teacher_comment,
            'evaluate_date'   => $evaluate_date,
            'status'          => $status,
        );

        // Data formats
        $format = array( '%s', '%s', '%s', '%s', '%d' );

        // WHERE clause
        $where = array( 'stu_homework_id' => $stud_homework_id );
        $where_format = array( '%d' );

        // Execute update
        return $wpdb->update( $table_name, $data, $where, $format, $where_format );
    }

}
