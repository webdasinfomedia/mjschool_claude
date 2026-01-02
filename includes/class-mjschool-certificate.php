<?php
/**
 * School Management Certificate Management Class.
 *
 * This file contains the Mjschool_Certificate class, which handles
 * the creation, retrieval, updating, and deletion of certificate records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to certificate records.
 *
 * @since 1.0.0
 */
class Mjschool_Certificate
{
    public function mjschool_get_all_certificate_owns( $mjschool_table_name ) {
        global $wpdb;
        $mjschool_table_name = sanitize_text_field($mjschool_table_name);
        $user_id    = get_current_user_id();
        $school_obj = new MJSchool_Management( $user_id );
        $table_name = $wpdb->prefix . $mjschool_table_name;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subjects = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name where student_id=%d", $user_id ) );
        return $retrieve_subjects;
    }
}