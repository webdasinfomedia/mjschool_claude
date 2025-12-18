<?php
/**
 * School Management Class Routine/Timetable Class.
 *
 * This file contains the Mjschool_Class_Routine class, which handles
 * the creation, saving, updating, and retrieval of class routines (timetables).
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Class_Routine class.
 *
 * Manages database operations for class routine.
 *
 * @since 1.0.0
 */
class Mjschool_Class_Routine
{

    public $route_id;
    public $subject_id;
    public $teacher_id;
    public $class_id;
    public $week_day;
    public $start_time;
    public $end_time;
    public $table_name = 'mjschool_time_table';
    public $day_list   = array(
    '1' => 'Monday',
    '2' => 'Tuesday',
    '3' => 'Wednesday',
    '4' => 'Thursday',
    '5' => 'Friday',
    '6' => 'Saturday',
    '7' => 'Sunday',
    );
    /**
     * Saves a single class routine entry to the database.
     *
     * @param  array $route_data Array of data for the route record.
     * @return int|false The number of rows inserted, or false on error. (Assumed return from mjschool_insert_record)
     * @since  1.0.0
     */
    public function mjschool_save_route( $route_data )
    {
        $table_name = 'mjschool_time_table';
        mjschool_insert_record($table_name, $route_data);
    }
    /**
     * Saves multiple class routine entries, typically for a virtual class setup.
     *
     * Logs each insertion to the audit log.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $route_data An array of arrays, where each inner array is a route record.
     * @return array An array of the inserted IDs.
     * @since  1.0.0
     */
    public function mjschool_save_route_with_virtual_class( $route_data )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_time_table';
        $lastid = array();
        foreach ( $route_data as $route ) {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_name, $route);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $lastid[] = $wpdb->insert_id;
            mjschool_append_audit_log('' . esc_html__('Route Added', 'mjschool') . '', get_current_user_id(), get_current_user_id(), 'insert', isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '');
        }
        return $lastid;
    }
    /**
     * Updates an existing class routine entry in the database.
     *
     * Logs the update action to the audit log.
     *
     * @param  array $route_data The updated data for the route record.
     * @param  int   $route_id   The primary key ID of the route to update.
     * @return int|false The number of rows updated, or false on error. (Assumed return from mjschool_update_record)
     * @since  1.0.0
     */
    public function mjschool_update_route( $route_data, $route_id )
    {
        $table_name = 'mjschool_time_table';
        mjschool_update_record($table_name, $route_data, $route_id);
        mjschool_append_audit_log('' . esc_html__('Route Updated', 'mjschool') . '', get_current_user_id(), get_current_user_id(), 'edit', isset($_REQUEST['page']) ? sanitize_text_field(wp_unslash($_REQUEST['page'])) : '');
    }
    /**
     * Checks if a class routine period already exists, either as a duplicate entry
     * for the class/subject, or as a conflict for the assigned teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $route_data Array containing subject_id, teacher_id, class_id, weekday, start_time, and end_time.
     * @return string 'success' if no conflict, 'duplicate' if a class/subject conflict, or 'teacher_duplicate' if a teacher conflict.
     * @since  1.0.0
     */
    public function mjschool_is_route_exist( $route_data )
    {
        $subject_id = $route_data['subject_id'];
        $teacher_id = $route_data['teacher_id'];
        $class_id   = $route_data['class_id'];
        $weekday    = $route_data['weekday'];
        $start_time = $route_data['start_time'];
        $end_time   = $route_data['end_time'];
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $route = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE subject_id = %d AND teacher_id = %d AND class_id = %d AND start_time = %s AND end_time = %s AND weekday = %d",
                $route_data['subject_id'],
                $route_data['teacher_id'],
                $route_data['class_id'],
                $route_data['start_time'],
                $route_data['end_time'],
                $route_data['weekday']
            )
        );
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE teacher_id = %d AND start_time = %s AND end_time = %s AND weekday = %d",
            $route_data['teacher_id'],
            $route_data['start_time'],
            $route_data['end_time'],
            $route_data['weekday']
        );
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context		
        $route2 = $wpdb->get_row($query);
        if (empty($route) && empty($route2) ) {
            return 'success';
        } else {
            if ($route ) {
                return 'duplicate';
            }
            if ($route2 ) {
                return 'teacher_duplicate';
            }
        }
    }
    /**
     * Retrieves the time table periods for a specific class, section, and day of the week.
     *
     * It fetches periods with and without the 'multiple_teacher' flag and merges them,
     * then sorts the combined result by start time.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int    $class_id ID of the class.
     * @param  string $section  Name of the class section.
     * @param  string $week_day Day of the week (numeric or string).
     * @return array Array of class routine data objects, sorted by start time.
     * @since  1.0.0
     */
    public function mjschool_get_period( $class_id, $section, $week_day )
    {
        global $wpdb;
        $class_id = intval($class_id);
        $table_name    = $wpdb->prefix . $this->table_name;
        $table_subject = $wpdb->prefix . 'mjschool_subject';
        // Use prepared statements to prevent SQL injection.
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $route_data_1 = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE class_id = %d AND section_name = %s AND weekday = %s AND multiple_teacher = 'yes'", $class_id, $section, $week_day));
        // Use INNER JOIN for better readability.
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $route_data_2 = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name AS route
				INNER JOIN $table_subject AS sb ON route.subject_id = sb.subid
				WHERE route.class_id = %d AND route.section_name = %s AND route.weekday = %s AND route.multiple_teacher IS NULL
				GROUP BY route.class_id, route.subject_id, route.weekday, route.start_time, route.end_time, route.section_name
				ORDER BY route.route_id ASC",
                $class_id,
                $section,
                $week_day
            )
        );
        // Merge the arrays.
        $route = array_merge($route_data_1, $route_data_2);
        usort(
            $route,
            function ( $a, $b ) {
                return strtotime($a->start_time) - strtotime($b->start_time);
            }
        );
        return $route;
    }
    /**
     * Retrieves the time table periods assigned to a specific teacher for a given day.
     *
     * It first identifies the classes and subjects the teacher is associated with
     * and then fetches the routine entries for those classes/subjects.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int    $teacher_id ID of the teacher.
     * @param  string $week_day   Day of the week.
     * @return array Array of class routine data objects.
     * @since  1.0.0
     */
    public function mjschool_get_period_by_teacher( $teacher_id, $week_day )
    {
        global $wpdb;
        $t1 = $wpdb->prefix . $this->table_name; /*mjschool_time_table.*/
        global $wpdb;
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result  = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table . ' where teacher_id =%d', intval($teacher_id)));
        $classes = array();
        if (! empty($result) ) {
            foreach ( $result as $retrive_data ) {
                $classes[] = intval($retrive_data->class_id);
            }
        }
        $table = $wpdb->prefix . 'mjschool_teacher_class';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result_sub = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $table . ' where teacher_id =%d', intval($teacher_id)));
        $subjects   = array();
        if (! empty($result_sub) ) {
            foreach ( $result_sub as $sub_retrive_data ) {
                if (isset($sub_retrive_data->subject_id) ) {
                    $subjects[] = intval($sub_retrive_data->subject_id);
                }
            }
        }
        $classes = implode(',', array_map('intval', $classes));
        if (! empty($subjects) ) {
            $subjects = implode(',', array_map('intval', $subjects));
            $tbl      = $wpdb->prefix . $this->table_name;
            // Assuming $week_day, $classes, $subjects are properly validated and sanitized.
            $query = $wpdb->prepare(
                "SELECT * FROM $t1 
				WHERE weekday = %s
				AND class_id IN ($classes)
				AND subject_id IN ($subjects)
				AND multiple_teacher IS NULL",
                $week_day
            );
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $route = $wpdb->get_results($query);
        } else {
            $route = '';
        }
        return $route;
    }
    /**
     * Retrieves routine periods where the teacher is explicitly listed as a "particular teacher"
     * in a multiple-teacher class.
     *
     * It uses the teacher's assigned classes from user meta (rather than the mjschool_teacher_class table).
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int    $teacher_id ID of the teacher.
     * @param  string $week_day   Day of the week.
     * @return array Array of class routine data objects.
     * @since  1.0.0
     */
    public function mjschool_get_period_by_particular_teacher( $teacher_id, $week_day )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        $class_id   = get_user_meta($teacher_id, 'class_name', true);
        $route_data = array();
        if (! empty($class_id) ) {
            $classes = implode(',', array_map('intval', $class_id));
            $query   = $wpdb->prepare("SELECT * FROM $table_name WHERE weekday = %s AND class_id IN ($classes) AND teacher_id LIKE %s AND multiple_teacher = 'yes'", $week_day, intval($teacher_id));
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $route_data = $wpdb->get_results($query);
        }
        return $route_data;
    }
}