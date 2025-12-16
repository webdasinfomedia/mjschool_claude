<?php
/**
 * School Management Fees Management Class.
 *
 * This file contains the Mjschool_Fees class, which handles
 * CRUD operations for fee types (as custom posts) and fee structure
 * entries (in a custom database table).
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Fees Class.
 *
 * Manages database and WordPress functions related to fee types,
 * fee structures (amount per class/section), and fee records.
 *
 * @since 1.0.0
 */
class Mjschool_Fees
{
    /**
     * Retrieves all custom posts registered as fee types.
     *
     * @since 1.0.0
     *
     * @return array Array of WP_Post objects representing fee types.
     */
    public function mjschool_get_all_feetype()
    {
        $args   = array(
        'post_type'      => sanitize_key('smgt_feetype'), // Validate post type.
        'posts_per_page' => intval(-1),                  // Ensure it's an integer.
        'orderby'        => sanitize_key('post_title'),  // Validate orderby field.
        'order'          => sanitize_text_field('ASC'),  // Validate order.
        );
        $result = get_posts($args);
        return $result;
    }
    /**
     * Inserts a new fee type as a custom post.
     *
     * @since 1.0.0
     *
     * @param  array $data Array containing the fee type data, including 'category_name'.
     * @return int|WP_Error The post ID on success, or WP_Error object on failure.
     */
    public function mjschool_add_feetype( $data )
    {
        global $wpdb;
        $result = wp_insert_post(
            array(
            'post_status' => 'publish',
            'post_type'   => 'smgt_feetype',
            'post_title'  => sanitize_textarea_field($data['category_name']),
            )
        );
        return $result;
    }
    /**
     * Deletes a fee type custom post by its ID.
     *
     * @since 1.0.0
     *
     * @param  int $cat_id The ID of the fee type post to delete.
     * @return object|false True on success, False on failure.
     */
    public function mjschool_delete_fee_type( $cat_id )
    {
        $cat_id = isset($cat_id) ? intval($cat_id) : 0;
        $result = wp_delete_post($cat_id, true);
        return $result;
    }
    /**
     * Checks if a fee structure already exists for a given fee type and class.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  int $fee_type_id The post ID of the fee type.
     * @param  int $class_id    The ID of the class.
     * @return bool True if a duplicate record is found, false otherwise.
     */
    public function mjschool_is_duplicat_fees( $fee_type_id, $class_id )
    {
        global $wpdb;
        $fee_type_id = intval($fee_type_id);
        $class_id = intval($class_id);
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_mjschool_fees where fees_title_id =%d, AND class_id =%d", $fee_type_id, $class_id));
        if (! empty($result) ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Inserts a new fee structure record or updates an existing one.
     *
     * Inserts data into the custom `mjschool_fees` table and performs audit logging.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  array $data Array of fee structure data, including 'fees_title_id', 'fees_id' (for edit).
     * @return int|false The inserted ID on insert, or the number of affected rows on update, or false on error.
     */
    public function mjschool_add_fees( $data )
    {
        global $wpdb;
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
        // -------Usersmeta table data. --------------
        $feedata['fees_title_id'] = sanitize_text_field($data['fees_title_id']);
        $feedata['class_id']      = sanitize_text_field(wp_unslash($_POST['class_id']));
        $feedata['section_id']    = sanitize_text_field(wp_unslash($_POST['class_section']));
        $feedata['fees_amount']   = sanitize_text_field(wp_unslash($_POST['fees_amount']));
        $feedata['description']   = sanitize_textarea_field(wp_unslash($_POST['description']));
        $feedata['created_date']  = date('Y-m-d H:i:s');
        $feedata['created_by']    = get_current_user_id();
        if ($data['action'] == 'edit' ) {
            $fees_id['fees_id'] = intval($data['fees_id']);
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result   = $wpdb->update($table_mjschool_fees, $feedata, $fees_id);
            $fee_type = get_the_title(intval($feedata['fees_title_id']));
            mjschool_append_audit_log('' . esc_html__('Fees Type Updated', 'mjschool') . '( ' . $fee_type . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_text_field($_REQUEST['page']));
            return $result;
        } else {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_mjschool_fees, $feedata);
            if ($result !== false ) {
                $insert_id = $wpdb->insert_id;
                $fee_type  = get_the_title($feedata['fees_title_id']);
                mjschool_append_audit_log(
                    esc_html__('Fees Type Added', 'mjschool') . ' ( ' . $fee_type . ' )',
                    get_current_user_id(),
                    get_current_user_id(),
                    'insert',
                    sanitize_text_field(wp_unslash($_REQUEST['page']))
                );
                return $insert_id;
            } else {
                return false;
            }
        }
    }
    /**
     * Retrieves all fee structure records from the custom fees table.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array|object|null An array of fee records, or null if none are found.
     */
    public function mjschool_get_all_fees()
    {
        global $wpdb;
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_mjschool_fees ORDER BY created_date DESC"));
        return $result;
    }
    /**
     * Retrieves fee structure records created by a specific user.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  int $user_id The ID of the user (creator).
     * @return array|object|null An array of fee records created by the user, or null if none are found.
     */
    public function mjschool_get_own_fees( $user_id )
    {
        global $wpdb;
        $user_id = intval($user_id);
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_mjschool_fees where created_by=%d", $user_id));
        return $result;
    }
    /**
     * Retrieves a single fee structure record by its fees ID.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  int $fees_id The unique ID of the fee structure record (`fees_id`).
     * @return object|null The fee structure record object, or null otherwise.
     */
    public function mjschool_get_single_feetype_data( $fees_id )
    {
        global $wpdb;
        $fees_id = intval($fees_id);
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_mjschool_fees where fees_id =%d", $fees_id));
        return $result;
    }
    /**
     * Retrieves the fees amount from a single fee structure record.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  int $fees_id The unique ID of the fee structure record (`fees_id`).
     * @return string|null The fee amount as a string, or null if the record is not found.
     */
    public function mjschool_get_single_feetype_data_amount( $fees_id )
    {
        global $wpdb;
        $fees_id = intval($fees_id);
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT fees_amount FROM $table_mjschool_fees where fees_id =%d", $fees_id));
        if (! empty($result) ) {
            return $result->fees_amount;
        }
    }
    /**
     * Deletes a fee structure record by its ID and logs the action.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param  int $fees_id The unique ID of the fee structure record to delete.
     * @return int|false The number of rows deleted (1 on success), or false on error.
     */
    public function mjschool_delete_feetype_data( $fees_id )
    {
        global $wpdb;
        $fees_id = intval($fees_id);
        $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $fee_type = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_mjschool_fees where fees_id=%d", $fees_id));
        $fee      = get_the_title($fee_type->fees_title_id);
        mjschool_append_audit_log('' . esc_html__('Fees Type Deleted', 'mjschool') . '( ' . $fee . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_text_field($_REQUEST['page']));
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_mjschool_fees where fees_id= " . $fees_id));
        return $result;
    }
}
