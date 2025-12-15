<?php
/**
 * School Management Event Management Class.
 *
 * This file contains the Mjschool_Event_Manage class, which handles
 * CRUD operations and notifications for school events.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Mjschool_Event_Manage Class.
 *
 * Handles all database operations and logic for creating, retrieving,
 * updating, and deleting school events, including email, SMS, and push
 * notifications upon insertion.
 *
 * @since 1.0.0
 */
class Mjschool_Event_Manage
{
    /**
     * Inserts a new event or updates an existing one and sends notifications.
     *
     * Handles data sanitization, database insertion/update, audit logging,
     * and sends email, SMS, and push notifications to relevant user roles.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param array  $data      Array of event details from the form submission.
     * @param string $file_name The file name of the event document (if uploaded).
     *
     * @return int|false The inserted event ID on insert, the number of affected rows on update, or false on error.
     */
    public function mjschool_insert_event( $data, $file_name )
    {
        global $wpdb;
        $table_name                = $wpdb->prefix . 'mjschool_event';
        $eventdata['event_title']  = sanitize_text_field(stripslashes($data['event_title']));
        $eventdata['description']  = sanitize_textarea_field(stripslashes($data['description']));
        $eventdata['start_date']   = date('Y-m-d', strtotime($data['start_date']));
        $eventdata['start_time']   = $data['start_time'];
        $eventdata['end_date']     = date('Y-m-d', strtotime($data['end_date']));
        $eventdata['end_time']     = $data['end_time'];
        $eventdata['event_doc']    = $file_name;
        $eventdata['created_date'] = date('Y-m-d');
        $eventdata['created_by']   = get_current_user_id();
        if ($data['action'] == 'edit' ) {
            $whereid['event_id'] = $data['event_id'];
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->update($table_name, $eventdata, $whereid);
            $event  = $eventdata['event_title'];
            mjschool_append_audit_log('' . esc_html__('Event Updated', 'mjschool') . '( ' . $event . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            return $result;
        } else {
         	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
            $result = $wpdb->insert($table_name, $eventdata);
            $ids    = $wpdb->insert_id;
            $event  = $eventdata['event_title'];
            mjschool_append_audit_log('' . esc_html__('Event Added', 'mjschool') . '( ' . $event . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
            if ($result ) {
                $user_list_array = get_users(
                    array(
                    'role__in' => array( 'supportstaff', 'parent', 'teacher', 'student' ),
                    'fields'   => array( 'ID' ),
                    )
                );
                if (! empty($user_list_array) ) {
                       $device_token = array();
                    foreach ( $user_list_array as $retrive_data ) {
                        $user_info = get_userdata($retrive_data->ID);
                        // Email Notification.
                        if (isset($data['mjschool_enable_event_mail']) == '1' ) {
                            $Search['{{user_name}}']   = $user_info->display_name;
                            $Search['{{event_title}}'] = sanitize_text_field(stripslashes($data['event_title']));
                            $Search['{{event_date}}']  = date('Y-m-d', strtotime($data['start_date'])) . ' To ' . date('Y-m-d', strtotime($data['end_date']));
                            $Search['{{event_time}}']  = $data['start_time'] . ' To ' . $data['end_time'];
                            $Search['{{description}}'] = sanitize_textarea_field(stripslashes($data['description']));
                            $Search['{{school_name}}'] = get_option('mjschool_name');
                            $message                   = mjschool_string_replacement($Search, get_option('mjschool_event_mailcontent'));
                            $sub['{{school_name}}']    = get_option('mjschool_name');
                            $subject                   = mjschool_string_replacement($sub, get_option('mjschool_event_mailsubject'));
                            mjschool_send_mail($user_info->user_email, $subject, $message);
                        }
                        // SMS Notification.
                        if (isset($data['mjschool_enable_event_sms']) == '1' ) {
                            $SMSCon                     = get_option('mjschool_event_mjschool_content');
                            $SMSArr['{{student_name}}'] = $user_info->display_name;
                            $SMSArr['{{event_title}}']  = mjschool_strip_tags_and_stripslashes(sanitize_text_field(wp_unslash($_POST['event_title'])));
                            $SMSArr['{{school_name}}']  = get_option('mjschool_name');
                            $message_content            = mjschool_string_replacement($SMSArr, $SMSCon);
                            $type                       = 'Event';
                            mjschool_send_mjschool_notification($retrive_data->ID, $type, $message_content);
                        }
                        $device_token[] = get_user_meta($retrive_data->id, 'token_id', true);
                    }
                       $title             = esc_attr__('You have a New Event', 'mjschool') . ' ' . sanitize_textarea_field(stripslashes($data['event_title']));
                       $text              = sanitize_textarea_field(stripslashes($data['description']));
                       $notification_data = array(
                        'registration_ids' => $device_token,
                        'data'             => array(
                         'title' => $title,
                         'body'  => $text,
                         'type'  => 'event',
                        ),
                       );
                       $json = json_encode($notification_data);
                       mjschool_send_push_notification($json);
                       // End send push Notification.//
                }
            }
            return $ids;
        }
    }
    /**
     * Retrieve a single event record by its ID.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $id The unique ID of the event (`event_id`).
     *
     * @return object|null The event record object if found, or null otherwise.
     */
    public function mjschool_get_single_event( $id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_event';
        $event_id   = intval($id);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE event_id = %d", $event_id));
        return $result;
    }
    /**
     * Retrieve all event records, ordered by start date descending.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array|object|null An array of all event objects, or null if no results are found.
     */
    public function mjschool_get_all_event()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_event';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY start_date DESC"));
        return $result;
    }
    /**
     * Delete an event record by its ID.
     *
     * Fetches the event title for audit logging before deletion.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $event_id The unique ID of the event to delete.
     *
     * @return int|false The number of rows deleted, or false on error.
     */
    public function mjschool_delete_event( $event_id )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_event';
        $id         = intval($event_id);
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $event = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name where event_id=%d", $id));
        mjschool_append_audit_log('' . esc_html__('Event Deleted', 'mjschool') . '( ' . $event->event_title . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', 'Event');
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query($wpdb->prepare("DELETE FROM $table_name where event_id=%d", $id));
        return $result;
    }
    /**
     * Retrieve the latest 5 event records for dashboard display.
     *
     * Results are ordered by event ID descending and limited to 5.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @return array|object|null An array of the latest 5 event objects, or null if fewer are found.
     */
    public function mjschool_get_all_event_for_dashboard()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mjschool_event';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY event_id DESC LIMIT %d", 5));
        return $result;
    }
    /**
     * Retrieve all events created by a specific user.
     *
     * @since 1.0.0
     *
     * @global wpdb $wpdb WordPress database abstraction object.
     *
     * @param int $event_id ID of the user who created the events (`created_by`).
     *
     * @return array|object|null An array of event objects created by the user, or null if none are found.
     */
    public function mjschool_get_own_event_list( $event_id )
    {
        global $wpdb;
        $id         = intval($event_id);
        $table_name = $wpdb->prefix . 'mjschool_event';
     	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name where created_by=%d", $id));
        return $result;
    }
}
