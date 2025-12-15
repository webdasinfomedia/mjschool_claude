<?php
/**
 * School Management Teacher Class.
 *
 * This file contains the Mjschool_Teacher class, which handles
 * the association between teachers and the classes they teach, including
 * functionality for adding, updating, and retrieving these associations.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to teacher-class assignments.
 *
 * @since 1.0.0
 */
class Mjschool_Teacher
{
    /**
     * Assigns multiple classes to a teacher identified by email.
     *
     * Iterates over an array of class IDs and inserts a record for each
     * association into the `mjschool_teacher_class` table.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array  $classes Array of class IDs to assign.
     * @param  string $name    The email address of the teacher.
     * @return int|false The result of the last $wpdb->insert call (1 on success), or false on invalid input.
     * @since  1.0.0
     */
    public function mjschool_add_multi_class( $classes, $name )
    {
        global $wpdb;
        $table        = $wpdb->prefix . 'mjschool_teacher_class';
        $teacher      = get_user_by('email', $name);
        $created_by   = get_current_user_id();
        $created_date = date('Y-m-d H:i:s');
        if (! empty($classes) && ! empty($teacher) ) {
            foreach ( $classes as $class ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $success = $wpdb->insert(
                    $table,
                    array(
                    'teacher_id'   => $teacher->ID,
                    'class_id'     => $class,
                    'created_by'   => $created_by,
                    'created_date' => $created_date,
                    )
                );
            }
        } else {
            return false;
        }
        return $success;
    }
    /**
     * Assigns multiple classes to a teacher during an import process.
     *
     * This method differs from `mjschool_add_multi_class` by looking up the teacher
     * by login/username and converting class names to class IDs using a helper function.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array  $classes Array of class names to assign.
     * @param  string $name    The login/username of the teacher.
     * @return int|false The result of the last $wpdb->insert call (1 on success), or false on invalid input.
     * @since  1.0.0
     */
    public function mjschool_add_multi_class_import( $classes, $name )
    {
        global $wpdb;
        $table        = $wpdb->prefix . 'mjschool_teacher_class';
        $teacher      = get_user_by('login', $name);
        $created_by   = get_current_user_id();
        $created_date = date('Y-m-d H:i:s');
        if (! empty($classes) && ! empty($teacher) ) {
            foreach ( $classes as $class ) {
                $class_id = mjschool_get_class_id_by_name($class);
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $success = $wpdb->insert(
                    $table,
                    array(
                    'teacher_id'   => $teacher->ID,
                    'class_id'     => $class_id,
                    'created_by'   => $created_by,
                    'created_date' => $created_date,
                    )
                );
            }
        } else {
            return false;
        }
        return $success;
    }
    /**
     * Retrieves an array of class IDs assigned to a specific teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $teacher_id The ID of the teacher.
     * @return array A flat array containing only the class IDs.
     * @since  1.0.0
     */
    public function mjschool_get_teacher_class( $teacher_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result   = $wpdb->get_results('SELECT * FROM ' . $table . ' where teacher_id =' . $teacher_id);
        $return_r = array();
        foreach ( $result as $retrive_data ) {
            $return_r[] = $retrive_data->class_id;
        }
        return $return_r;
    }
    /**
     * Retrieves all teacher-class assignments for a given class ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $class_id The ID of the class.
     * @return array Array of assignment records (teacher_id, class_id, etc.) as associative arrays.
     * @since  1.0.0
     */
    public function mjschool_get_class_teacher( $class_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results('SELECT * FROM ' . $table . ' where class_id =' . $class_id, ARRAY_A);
        return $result;
    }
    /**
     * Retrieves a single teacher-class assignment record for a given class ID.
     *
     * This method may only be useful if a class is intended to have only one main teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $class_id The ID of the class.
     * @return object|null The single assignment record object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_single_class_teacher( $class_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row('SELECT * FROM ' . $table . ' where class_id =' . $class_id);
        return $result;
    }
    /**
     * Updates the multi-class assignments for a teacher.
     *
     * Compares the new list of class IDs with the existing list, inserts the new ones,
     * and deletes the removed ones.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $classes    The new array of class IDs to be assigned.
     * @param  int   $teacher_id The ID of the teacher being updated.
     * @return int|false The result of the last insert operation (1 on success), or 1 if only deletions occurred, or false on error.
     * @since  1.0.0
     */
    public function mjschool_update_multi_class( $classes, $teacher_id )
    {
        global $wpdb;
        $table        = $wpdb->prefix . 'mjschool_teacher_class';
        $created_by   = get_current_user_id();
        $created_date = date('Y-m-d H:i:s');
        $post_classes = $classes;
        $old_class    = $this->mjschool_get_teacher_class($teacher_id);
        $new_insert   = array_diff($post_classes, $old_class);
        $delete_class = array_diff($old_class, $post_classes);
        $success      = 1;
        if (! empty($new_insert) ) {
            foreach ( $new_insert as $class_id ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $success = $wpdb->insert(
                    $table,
                    array(
                    'teacher_id'   => $teacher_id,
                    'class_id'     => $class_id,
                    'created_by'   => $created_by,
                    'created_date' => $created_date,
                    )
                );
            }
        }
        if (! empty($delete_class) ) {
            foreach ( $delete_class as $class_id ) {
             	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $wpdb->delete(
                    $table,
                    array(
                    'teacher_id' => $teacher_id,
                    'class_id'   => $class_id,
                    )
                );
            }
        }
        return $success;
    }
    /**
     * Retrieves an array of class IDs assigned to a specific teacher (similar to mjschool_get_teacher_class).
     *
     * Returns the data as an associative array of arrays.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $teacher_id The ID of the teacher.
     * @return array Array of class assignment records as associative arrays.
     * @since  1.0.0
     */
    public function mjschool_get_class_by_teacher( $teacher_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $data = $wpdb->get_results("SELECT class_id FROM {$table} WHERE teacher_id = {$teacher_id}", ARRAY_A);
        return $data;
    }
    /**
     * Retrieves a flat array of class IDs assigned to a teacher, specifically for notification purposes.
     *
     * This method unwraps the nested array structure returned by `get_results(..., ARRAY_A)`.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $teacher_id The ID of the teacher.
     * @return array A flat array containing only the class IDs.
     * @since  1.0.0
     */
    public function mjschool_get_class_by_teacher_notification( $teacher_id )
    {
        global $wpdb;
        $classes = array();
        $table   = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $data = $wpdb->get_results("SELECT class_id FROM {$table} WHERE teacher_id = {$teacher_id}", ARRAY_A);
        foreach ( $data as $key => $value ) {
            foreach ( $value as $class ) {
                $classes[] = $class;
            }
        }
        return $classes;
    }
    /**
     * Recursively checks if a needle exists within a haystack (array) or any of its nested arrays.
     *
     * @param  mixed $needle   The value to search for.
     * @param  array $haystack The array to search in.
     * @param  bool  $strict   If true, checks for strict equality (===).
     * @return bool True if the needle is found, false otherwise.
     * @since  1.0.0
     */
    function mjschool_in_array_r( $needle, $haystack, $strict = false )
    {
        foreach ( $haystack as $item ) {
            if (( $strict ? $item === $needle : $item == $needle ) || ( is_array($item) && $this->mjschool_in_array_r($needle, $item, $strict) ) ) {
                return true;
            }
        }
        return false;
    }
    /**
     * Retrieves the IDs of teachers assigned to a specific class.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int|null $class_id The ID of the class. If null, returns false.
     * @return array|false An array of teacher IDs as associative arrays, or false if $class_id is null.
     * @since  1.0.0
     */
    function mjschool_get_teacher_by_class( $class_id = null )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
        if ($class_id != null ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_results("SELECT teacher_id FROM {$table} WHERE class_id = {$class_id}", ARRAY_A);
            return $results;
        } else {
            return false;
        }
    }
    /**
     * Retrieves an array of subject IDs assigned to a specific teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $teacher_id The ID of the teacher.
     * @return array A flat array containing the subject IDs, or an empty array if none are found.
     * @since  1.0.0
     */
    function mjschool_get_teacher_subjects( $teacher_id )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_subject';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result   = $wpdb->get_results('SELECT * FROM ' . $table . ' where teacher_id =' . $teacher_id);
        $return_r = array();
        if (! empty($result) ) {
            foreach ( $result as $retrive_data ) {
                $subjects[] = $retrive_data->subject_id;
            }
        }
        return $subjects;
    }
}
