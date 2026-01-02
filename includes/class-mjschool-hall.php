<?php
/**
 * School Management hall Management Class.
 *
 * This file contains the Mjschool_Hall class, which handles
 * the creation, retrieval, updating, and deletion of hall records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to hall records.
 *
 * @since 1.0.0
 */
class Mjschool_Hall
{
    /**
     * Retrieves hall details by ID.
     *
     * @param int $id Hall ID.
     * @return object|null Hall record.
     * @since 1.0.0
     */
    public function mjschool_get_hall_by_id( $id ) {
        global $wpdb;
        $table_mjschool_hall = $wpdb->prefix . 'mjschool_hall';
        $hid        = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_hall = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_hall WHERE hall_id = %d", $hid ) );
        return $retrieve_hall;
    }
    
    /**
     * Deletes exam hall.
     *
     * @param string $mjschool_table_name Table name.
     * @param int $hall_id Hall ID.
     * @return int Rows affected.
     * @since 1.0.0
     */
    public function mjschool_delete_hall( $mjschool_table_name, $hall_id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Exam Hall Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        global $wpdb;
        // Sanitize table name
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $insert_table_name          = $wpdb->prefix . $mjschool_table_name;
        $id                  = absint( $hall_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $insert_table_name WHERE hall_id = %d", $id ) );

        return $result;
    }
    /**
     * Retrieves exam hall receipt details for a specific student and exam.
     *
     * @since 1.0.0
     *
     * @param int $id  Student ID.
     * @param int $eid Exam ID.
     *
     * @return object|null Database row containing exam hall receipt details, or null if not found.
     */
    public function mjschool_get_exam_hall_name( $id, $eid ) {
        $student_id = absint( $id );
        $exam_id    = absint( $eid );
        if ( empty( $student_id ) || empty( $exam_id ) ) {
            return null;
        }
        global $wpdb;
        $table_name_mjschool_exam_hall_receipt = $wpdb->prefix . 'mjschool_exam_hall_receipt';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name_mjschool_exam_hall_receipt} WHERE exam_id = %d AND user_id = %d", $exam_id, $student_id )
        );
        return $result;
    }
    /**
     * Retrieves the hall name based on hall ID.
     *
     * @since 1.0.0
     *
     * @param int $eid Hall ID.
     *
     * @return string|null Hall name if available, otherwise null.
     */
    public function mjschool_get_hall_name( $eid ) {
        $hall_id = absint( $eid );
        
        if ( empty( $hall_id ) ) {
            return '';
        }
        global $wpdb;
        $table_name_hall = $wpdb->prefix . 'mjschool_hall';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table_name_hall} WHERE hall_id = %d", $hall_id )
        );
        if ( empty( $result ) || ! isset( $result->hall_name ) ) {
            return '';
        }
        return $result->hall_name;
    }

}