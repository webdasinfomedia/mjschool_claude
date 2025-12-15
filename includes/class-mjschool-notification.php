<?php
/**
 * School Management Notification Class.
 *
 * This file contains the Mjschool_notification class, which handles
 * CRUD operations for notification and fee structure
 * entries (in a custom database table).
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_notification Class.
 *
 * Manages database and WordPress functions related to notification,
 *
 * @since 1.0.0
 */
class Mjschool_notification
{
    /**
     * Insert a new notification record into the database.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array $data     Associative array of column => value for insertion.
     *
     * @return int|false Inserted row ID on success, or false on failure.
     */
    public function mjschool_insert_notification($data) {
        global $wpdb;

        // Table name
        $table_name = $wpdb->prefix . 'mjschool_notification';
        // Perform insert
        $inserted = $wpdb->insert($table_name, $data);

        // Return inserted ID if successful
        return $inserted ? $wpdb->insert_id : false;
    }
    
    /**
     * Retrieve all notification records.
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array List of notification objects. Returns an empty array if no records found.
     */
    public function mjschool_get_all_notifications() {
        global $wpdb;

        // Table name
        $table_name = $wpdb->prefix . 'mjschool_notification';

        // Safe direct query (no placeholders needed)
        $query  = "SELECT * FROM {$table_name}";
        $result = $wpdb->get_results( $query );

        return is_array( $result ) ? $result : array();
    }

}
