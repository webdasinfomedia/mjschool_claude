<?php
/**
 * School Management holiday Management Class.
 *
 * This file contains the Mjschool_Holiday class, which handles
 * the creation, retrieval, updating, and deletion of holiday records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to holiday records.
 *
 * @since 1.0.0
 */
class Mjschool_Holiday
{
    /**
     * Retrieves holiday details by ID.
     *
     * @param int $id Holiday ID.
     * @return object|null Holiday record.
     * @since 1.0.0
     */
    public function mjschool_get_holiday_by_id( $id ) {
        global $wpdb;
        $table_mjschool_holiday = $wpdb->prefix . 'mjschool_holiday';
        $hid        = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_holiday = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_holiday WHERE holiday_id = %d", $hid ) );
        return $retrieve_holiday;
    }

    /**
     * Deletes a holiday record.
     *
     * @param string $mjschool_table_name Table name.
     * @param int $holiday_id Holiday ID.
     * @return int Rows affected.
     * @since 1.0.0
     */
    public function mjschool_delete_holiday( $mjschool_table_name, $holiday_id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Holiday Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        global $wpdb;
        // Sanitize table name
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $insert_table_name          = $wpdb->prefix . $mjschool_table_name;
        $id                  = absint( $holiday_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $insert_table_name WHERE holiday_id = %d", $id ) );
        return $result;
    }
    
    /**
     * Generates a list of all holiday dates including date ranges.
     *
     * @since 1.0.0
     *
     * @return array List of all dates marked as holidays.
     */
    public function mjschool_get_all_date_of_holidays() {
        global $wpdb;
        $tbl_holiday = $wpdb->prefix . 'mjschool_holiday';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $HolidayData = $wpdb->get_results( "SELECT * FROM {$tbl_holiday}" );
        $holidaydates = array();
        if ( empty( $HolidayData ) ) {
            return $holidaydates;
        }
        foreach ( $HolidayData as $holiday ) {
            if ( ! isset( $holiday->date ) || ! isset( $holiday->end_date ) ) {
                continue;
            }
            $holidaydates[] = $holiday->date;
            $holidaydates[] = $holiday->end_date;
            $start_date = strtotime( $holiday->date );
            $end_date   = strtotime( $holiday->end_date );
            if ( false === $start_date || false === $end_date ) {
                continue;
            }
            if ( $holiday->date !== $holiday->end_date ) {
                for ( $i = $start_date; $i < $end_date; $i += 86400 ) {
                    $holidaydates[] = wp_date( 'Y-m-d', $i );
                }
            }
        }
        $holidaydates = array_unique( $holidaydates );
        return $holidaydates;
    }
}