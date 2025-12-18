<?php
/**
 * School Management Tax Management Class.
 *
 * This file contains the Mjschool_Tax_Manage class, which handles
 * the creation, retrieval, updating, and deletion of tax records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to tax records (e.g., GST rates).
 *
 * @since 1.0.0
 */
class Mjschool_Tax_Manage
{
    /**
     * Inserts a new tax record or updates an existing one.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $data Array of tax data, including 'tax_title', 'tax_value', and optionally 'action' and 'tax_id'.
     * @return int|false The ID of the newly inserted record, or the result of the update query (1 on success), or false on error.
     * @since  1.0.0
     */
    public function mjschool_insert_tax( $data )
    {
        global $wpdb;
        $table_name              = $wpdb->prefix . 'mjschool_taxes';
        $taxdata['tax_title']    = sanitize_text_field($data['tax_title']);
        $taxdata['tax_value']    = sanitize_text_field($data['tax_value']);
        $taxdata['created_date'] = date('Y-m-d');
        if (isset($data['action']) && $data['action'] == 'edit' ) {
            $whereid['tax_id'] = intval($data['tax_id']);
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->update($table_name, $taxdata, $whereid);
            $result = 1;
            return $result;
        } else {
         // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_name, $taxdata);
            $ids    = $wpdb->insert_id;
            return $ids;
        }
    }
    /**
     * Retrieves all tax records, ordered by creation date descending.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of tax data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_tax()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_taxes';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY created_date DESC"));
        return $result;
    }
    /**
     * Retrieves a single tax record by its ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $tax_id The ID of the tax record to retrieve.
     * @return object|null The tax data object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_single_tax( $tax_id )
    {
        global $wpdb;
        $tax_id = intval($tax_id);
        $table_name = $wpdb->prefix . 'mjschool_taxes';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name where tax_id=%d", $tax_id));
        return $result;
    }
    /**
     * Deletes a tax record by its ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $id The ID of the tax record to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since  1.0.0
     */
    public function mjschool_delete_tax( $id )
    {
        global $wpdb;
        $id = intval($id);
        $table_name = $wpdb->prefix . 'mjschool_taxes';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_name where tax_id=%d", $id));
        return $result;
    }
}