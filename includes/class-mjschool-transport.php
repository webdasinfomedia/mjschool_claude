<?php
/**
 * School Management Transport Management Class.
 *
 * This file contains the Mjschool_Transport class, which handles
 * the creation, retrieval, updating, and deletion of transport records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to transport records.
 *
 * @since 1.0.0
 */
class Mjschool_Transport
{
    /**
     * Retrieves transport details by ID.
     *
     * @param int $id Transport ID.
     * @return object|null Transport record.
     * @since 1.0.0
     */
    public function mjschool_get_transport_by_id( $id ) {
        global $wpdb;
        $table_mjschool_transport = $wpdb->prefix . 'mjschool_transport';
        $tid        = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_transport WHERE transport_id = %d", $tid ) );
        return $retrieve_subject;
    }

    /**
     * Deletes transport data.
     *
     * @param string $mjschool_table_name Table name.
     * @param int $id Transport ID.
     * @return int Rows affected.
     * @since 1.0.0
     */
    public function mjschool_delete_transport( $mjschool_table_name, $id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Transport Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        global $wpdb;
        // Sanitize table name
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $insert_table_name          = $wpdb->prefix . $mjschool_table_name;
        $tid                 = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $insert_table_name WHERE transport_id = %d", $tid ) );
        return $result;
    }

    /**
     * Fetches driver image from the transport table based on transport ID.
     *
     * @since 1.0.0
     *
     * @param int $tid Transport ID.
     *
     * @return array|null Driver image data or null if not found.
     */
    public function mjschool_get_user_driver_image( $tid ) {
        global $wpdb;
        $table_mjschool_transport = $wpdb->prefix . 'mjschool_transport';
        $tid        = absint( $tid );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $usersdata = $wpdb->get_results(
            $wpdb->prepare( "SELECT smgt_user_avatar FROM {$table_mjschool_transport} WHERE transport_id = %d", $tid ), ARRAY_A
        );
        if ( ! empty( $usersdata ) ) {
            return $usersdata[0];
        }
        return null;
    } 
}