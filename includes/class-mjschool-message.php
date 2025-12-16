<?php
/**
 * School Management Message Management Class.
 *
 * This file contains the Mjschool_Message class, which handles
 * CRUD operations using custom database tables.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Message Class
 *
 * Handles all leave-related operations for the mjschool plugin,
 * including adding, editing, fetching, approving, rejecting, and deleting leave records.
 * It also manages email, SMS, and push notifications for these actions.
 *
 * @since 1.0.0
 */
class Mjschool_Message
{
    /**
     * Retrieves all replies for a given message thread.
     *
     * @since 1.0.0
     *
     * @param int $tid Message ID.
     *
     * @return array List of reply records.
     */
    public function mjschool_get_all_replies( $tid ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_message_replies';
        $user_id    = intval( $tid );
        $query      = $wpdb->prepare( "SELECT * FROM $table_name WHERE message_id = %d GROUP BY message_id, sender_id, message_comment ORDER BY id ASC", $user_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $wpdb->get_results( $query );
    }

    /**
     * Retrieve all message replies for a given message ID (frontend use).
     *
     * @since 1.0.0
     * @param int $id Message ID.
     * @return array List of reply objects.
     */
    public function mjschool_get_all_replies_frontend( $id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_message_replies';
        $user_id    = intval( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM $table_name where message_id = %d", $user_id ) );
    }

    /**
     * Delete a single reply record from the replies table.
     *
     * @since 1.0.0
     * @param int $id Reply ID.
     * @return int|false Number of rows deleted or false on failure.
     */
    public function mjschool_delete_reply( $id ) {
        global $wpdb;
        $table_name     = $wpdb->prefix . 'mjschool_message_replies';
        $reply_id['id'] = $id;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        return $result = $wpdb->delete( $table_name, $reply_id );
    }

    /**
     * Count total unread messages and replies for the current user.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @return int Total unread message count.
     */
    public function mjschool_count_reply_item( $user_id ) {
        global $wpdb;
        $tbl_name                 = $wpdb->prefix . 'mjschool_message';
        $mjschool_message_replies = $wpdb->prefix . 'mjschool_message_replies';
        $user_id                  = get_current_user_id();
        $id                       = intval( $user_id );
        // Query for inbox/sent box messages.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $inbox_sent_box = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tbl_name WHERE (receiver = %d AND sender != %d) AND post_id = %d AND status = 0", $user_id, $user_id, $id ) );
        // Query for reply messages.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $reply_msg = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $mjschool_message_replies WHERE receiver_id = %d AND message_id = %d AND (status = 0 OR status IS NULL)", $user_id, $id ) );
        // Count total messages.
        $count_total_message = count( $inbox_sent_box ) + count( $reply_msg );
        return $count_total_message;
    }
}
