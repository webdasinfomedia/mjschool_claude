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
        $teacher      = get_user_by('email', sanitize_email($name));
        $created_by   = get_current_user_id();
        $created_date = current_time( 'mysql' );
        if (! empty($classes) && ! empty($teacher) ) {
            foreach ( $classes as $class ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $success = $wpdb->insert(
                    $table,
                    array(
                    'teacher_id'   => $teacher->ID,
                    'class_id'     => intval($class),
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
        $teacher      = get_user_by('login', sanitize_user($name));
        $created_by   = get_current_user_id();
        $created_date = current_time( 'mysql' );
        
        if (! empty($classes) && ! empty($teacher) ) {
            foreach ( $classes as $class ) {
                $class_id = $this->mjschool_get_class_id_by_name(sanitize_text_field($class));
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $success = $wpdb->insert(
                    $table,
                    array(
                    'teacher_id'   => $teacher->ID,
                    'class_id'     => intval($class_id),
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
        $result   = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table . ' where teacher_id =%d', intval($teacher_id)));
        $return_r = array();
        foreach ( $result as $retrive_data ) {
            $return_r[] = intval($retrive_data->class_id);
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
        $result = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table . ' where class_id =%d', intval($class_id)), ARRAY_A);
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
        $result = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $table . ' where class_id =%d', intval($class_id)));
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
        $created_date = current_time( 'mysql' );
        $post_classes = array_map('intval', $classes);
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
                    'teacher_id'   => intval($teacher_id),
                    'class_id'     => intval($class_id),
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
                    'teacher_id' => intval($teacher_id),
                    'class_id'   => intval($class_id),
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
        $data = $wpdb->get_results($wpdb->prepare("SELECT class_id FROM {$table} WHERE teacher_id = %d", intval($teacher_id)), ARRAY_A);
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
        $data = $wpdb->get_results($wpdb->prepare("SELECT class_id FROM {$table} WHERE teacher_id = %d", intval($teacher_id)), ARRAY_A);
        foreach ( $data as $key => $value ) {
            foreach ( $value as $class ) {
                $classes[] = intval($class);
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
    public function mjschool_in_array_r( $needle, $haystack, $strict = false )
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
    public function mjschool_get_teacher_by_class( $class_id = null )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
        if ($class_id != null ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $results = $wpdb->get_results($wpdb->prepare("SELECT teacher_id FROM {$table} WHERE class_id = %d", intval($class_id)), ARRAY_A);
            return $results;
        } else {
            return false;
        }
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
    public function mjschool_check_class_exits_in_teacher_class( $id ) {
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
     * Get full name of teacher (first + middle + last).
     *
     * @since 1.0.0
     * @param int $id Teacher ID.
     * @return string Full name.
     */
    public function mjschool_get_teacher( $id ) {
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
     * Retrieves all class records assigned to a teacher.
     *
     * @param int $id Teacher ID.
     * @return array Class list.
     * @since 1.0.0
     */
    public function mjschool_get_all_teacher_data( $id ) {
        global $wpdb;
        $table_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
        $teacher_id = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$table_mjschool_teacher_class} WHERE teacher_id = %d", $teacher_id )
        );
        return $result;
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
    public function mjschool_upload_teacher_signature( $file ) {
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
     * Fetches all teachers assigned to a given class ID.
     *
     * @since 1.0.0
     *
     * @param int $class_id Class ID.
     *
     * @return array List of WP_User objects for assigned teachers.
     */
    public function mjschool_get_teacher_by_class_id( $class_id ) {
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

}