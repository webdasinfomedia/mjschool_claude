<?php
/**
 * School Management Grade Management Class.
 *
 * This file contains the Mjschool_Grade class, which handles
 * the creation, retrieval, updating, and deletion of Grade records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to grade records.
 *
 * @since 1.0.0
 */
class Mjschool_Grade
{
    /**
     * Delete a grade entry.
     *
     * @since 1.0.0
     * @param string $mjschool_table_name Table name.
     * @param int $id Grade ID.
     * @return int Rows affected.
     */
    function mjschool_delete_grade( $mjschool_table_name, $id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Grade Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        
        global $wpdb;
        $record_id = absint( $id );
        // Sanitize table name
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $inserrt_table_name          = $wpdb->prefix . $mjschool_table_name;
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $inserrt_table_name WHERE grade_id = %d", $record_id ) );
        
        return $result;
    }
}