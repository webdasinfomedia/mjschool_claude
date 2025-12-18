<?php
/**
 * School Management Virtual Classroom Class.
 *
 * This file contains the Mjschool_Virtual_Classroom class, which handles
 * the integration with an external virtual meeting platform (Zoom), allowing
 * for the creation, updating, retrieval, and deletion of recurring meetings
 * and the viewing of past participant lists.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
require_once MJSCHOOL_PLUGIN_DIR . '/lib/vendor/autoload.php';
/**
 * Manages all functionality related to Virtual Classrooms (Zoom meetings).
 *
 * @since 1.0.0
 */
class Mjschool_Virtual_Classroom
{

    /**
     * Creates a new Zoom meeting or updates an existing one.
     *
     * It handles API communication with Zoom and stores/updates the meeting
     * details in the local database.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  array $data Array of meeting data, including 'teacher_id', 'start_date', 'end_date', 'start_time',
     *                     'end_time', 'class_id', 'agenda', 'weekday', 'action', and optionally 'zoom_meeting_id', etc.
     * @return int|false The result of the database operation (number of affected rows or false).
     * @since  1.0.0
     */
    public function mjschool_create_meeting_in_zoom( $data )
    {
        global $wpdb;
        $teacher_all_data = get_userdata($data['teacher_id']);
        if (empty($data['password']) ) {
            $password = wp_generate_password(10, true, true);
        } else {
            $password = $data['password'];
        }
        $start_time_raw = $data['start_time'];
        $start_date     = $data['start_date'] . 'T' . $data['start_time'] . ':' . '00';
        $end_time_raw   = $data['end_time'];
        $end_date       = $data['end_date'] . 'T' . $data['end_time'] . ':' . '00Z';
        // Fix invalid format like "3:00:pm" to "3:00 pm".
        $start_time_raw = str_replace(':am', ' am', strtolower(trim($start_time_raw)));
        $start_time_raw = str_replace(':pm', ' pm', $start_time_raw);
        $end_time_raw   = str_replace(':am', ' am', strtolower(trim($end_time_raw)));
        $end_time_raw   = str_replace(':pm', ' pm', $end_time_raw);
        // Convert to timestamps.
        $start = strtotime($start_time_raw);
        $end   = strtotime($end_time_raw);
        // Calculate difference in minutes.
        $diff_minutes = ( $end - $start ) / 60;
        $clasname     = mjschool_get_class_name($data['class_id']);
        $client       = new GuzzleHttp\Client(array( 'base_uri' => 'https://api.zoom.us' ));
        $accessToken  = mjschool_get_zoom_access_token();
        $topic        = $data['agenda'];
        $timezone     = get_option('timezone_string');
        if (! $timezone ) {
            // Fallback if timezone_string is not set.
            $offset   = get_option('gmt_offset');
            $timezone = timezone_name_from_abbr('', $offset * 3600, 0);
        }
        try {
            if ($data['action'] == 'edit' ) {
                $meetingId = $data['zoom_meeting_id'];
                $url       = "https://api.zoom.us/v2/meetings/{$meetingId}";
                $headers   = array(
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
                );
                $body      = json_encode(
                    array(
                    'topic'      => $clasname,
                    'start_time' => $start_date,
                    'duration'   => $diff_minutes ?? 30,
                    'timezone'   => $timezone,
                    'recurrence' => array(
                    'type'            => 2, // Weekly.
                    'repeat_interval' => 1, // Every week.
                    'weekly_days'     => (int) $data['weekday'],
                    'end_date_time'   => $end_date,
                    ),
                    'settings'   => array(
                            'join_before_host' => true,
                            'mute_upon_entry'  => true,
                            'approval_type'    => 0,
                    ),
                    )
                );
                $response  = wp_remote_request(
                    $url,
                    array(
                    'method'  => 'PATCH',
                    'headers' => $headers,
                    'body'    => $body,
                    )
                );
            } else {
                $url              = 'https://api.zoom.us/v2/users/me/meetings';
                $headers          = array(
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
                );
                $body = json_encode(
                    array(
                    'topic'      => $clasname,
                    'agenda'     => $topic,
                    'password'   => $password,
                    'type'       => 8, // Recurring with fixed time.
                    'start_time' => $start_date,
                    'duration'   => $diff_minutes ?? 30,
                    'timezone'   => $timezone,
                    'recurrence' => array(
                            'type'            => 2, // Weekly.
                            'repeat_interval' => 1, // Every week.
                            'weekly_days'     => (int) $data['weekday'],
                            'end_date_time'   => $end_date,
                    ),
                    'settings'   => array(
                    'join_before_host' => true,
                    'mute_upon_entry'  => true,
                    'approval_type'    => 0,
                    ),
                    )
                );
                $response = wp_remote_post(
                    $url,
                    array(
                    'headers' => $headers,
                    'body'    => $body,
                    )
                );
                $meeting_response = json_decode(wp_remote_retrieve_body($response));
            }
            $table_zoom_meeting         = $wpdb->prefix . 'mjschool_zoom_meeting';
            $meeting_data['title']      = $clasname;
            $meeting_data['route_id']   = (int) $data['route_id'];
            $meeting_data['class_id']   = (int) $data['class_id'];
            $meeting_data['section_id'] = (int) $data['class_section_id'];
            $meeting_data['subject_id'] = (int) $data['subject_id'];
            $meeting_data['teacher_id'] = (int) $data['teacher_id'];
            $meeting_data['agenda']     = $data['agenda'];
            $meeting_data['start_date'] = $data['start_date'];
            $meeting_data['end_date']   = $data['end_date'];
            $meeting_data['weekday_id'] = (int) $data['weekday'];
            $meeting_data['password']   = $password;
            if ($data['action'] == 'edit' ) {
                $meeting_data['zoom_meeting_id']    = $data['zoom_meeting_id'];
                $meeting_data['uuid']               = $data['uuid'];
                $meeting_data['meeting_join_link']  = $data['meeting_join_link'];
                $meeting_data['meeting_start_link'] = $data['meeting_start_link'];
                $meetingid['meeting_id']            = sanitize_text_field($data['meeting_id']);
                $meeting_data['updated_date']       = date('Y-m-d h:i:sa');
                $meeting_data['updated_by']         = get_current_user_id(); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result                             = $wpdb->update($table_zoom_meeting, $meeting_data, $meetingid);
                mjschool_append_audit_log('' . esc_html__('Virtual Classroom Updated', 'mjschool') . '', get_current_user_id(), get_current_user_id(), 'edit', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
            } elseif ($meeting_response ) {
                $meeting_data['zoom_meeting_id']    = $meeting_response->id;
                $meeting_data['uuid']               = $meeting_response->uuid;
                $meeting_data['meeting_join_link']  = $meeting_response->join_url;
                $meeting_data['meeting_start_link'] = $meeting_response->start_url;
                $meeting_data['created_by']         = get_current_user_id();
                $meeting_data['created_date']       = date('Y-m-d h:i:sa'); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result                             = $wpdb->insert($table_zoom_meeting, $meeting_data);
                mjschool_append_audit_log('' . esc_html__('Virtual Classroom Added', 'mjschool') . '', get_current_user_id(), get_current_user_id(), 'insert', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
            }
            return $result;
        } catch ( Exception $e ) {
            wp_redirect(admin_url() . 'admin.php?page=mjschool_virtual_classroom&tab=meeting_list&message=5');
            die();
        }
    }
    /**
     * Retrieves all Zoom meeting records from the local database.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @return array Array of meeting data objects.
     * @since  1.0.0
     */
    public function mjschool_get_all_meeting_data_in_zoom()
    {
        global $wpdb;
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results("SELECT * FROM $table_zoom_meeting");
        return $result;
    }
    /**
     * Retrieves all Zoom meeting records assigned to a specific teacher.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $teacher_id The ID of the teacher.
     * @return array Array of meeting data objects.
     * @since  1.0.0
     */
    public function mjschool_get_meeting_by_teacher_id_data_in_zoom( $teacher_id )
    {
        global $wpdb;
        $teacher_id = intval($teacher_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE teacher_id=%d", $teacher_id));
        return $result;
    }
    /**
     * Retrieves all Zoom meeting records assigned to a specific class.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $class_id The ID of the class.
     * @return array Array of meeting data objects.
     * @since  1.0.0
     */
    public function mjschool_get_meeting_by_class_id_data_in_zoom( $class_id )
    {
        global $wpdb;
        $class_id = intval($class_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE class_id=%d", $class_id));
        return $result;
    }
    /**
     * Retrieves all Zoom meeting records assigned to a specific class and section.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $class_id   The ID of the class.
     * @param  int $section_id The ID of the section.
     * @return array Array of meeting data objects.
     * @since  1.0.0
     */
    public function mjschool_get_meeting_by_class_id_and_section_id_data_in_zoom( $class_id, $section_id )
    {
        global $wpdb;
        $class_id = intval($class_id);
        $section_id = intval($section_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE class_id=%d AND section_id=%d", $class_id, $section_id));
        return $result;
    }
    /**
     * Retrieves a single Zoom meeting record by its local database ID.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $meeting_id The local database ID of the meeting.
     * @return object|null The meeting data object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_single_meeting_data_in_zoom( $meeting_id )
    {
        global $wpdb;
        $meeting_id = intval($meeting_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE meeting_id=%d", $meeting_id));
        return $result;
    }
    /**
     * Retrieves a single Zoom meeting record by its route ID.
     *
     * The purpose of 'route_id' is not fully clear but is assumed to be a unique identifier
     * for a specific schedule/route entity.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $route_id The ID of the route.
     * @return object|null The meeting data object or null if not found.
     * @since  1.0.0
     */
    public function mjschool_get_single_meeting_by_route_data_in_zoom( $route_id )
    {
        global $wpdb;
        $route_id = intval($route_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE route_id=%d", $route_id));
        return $result;
    }
    /**
     * Retrieves all Zoom meeting records scheduled for a specific day of the week.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $day_id The ID representing the day of the week.
     * @return array Array of meeting data objects.
     * @since  1.0.0
     */
    public function mjschool_get_meeting_data_by_day_in_zoom( $day_id )
    {
        global $wpdb;
        $day_id = intval($day_id);
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting';
     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_zoom_meeting WHERE weekday_id=%d", $day_id));
        return $result;
    }
    /**
     * Deletes a Zoom meeting record from the local database.
     *
     * Note: This method only deletes the record locally and does not call the Zoom API
     * to delete the meeting from Zoom's servers.
     *
     * @global wpdb $wpdb WordPress database access abstraction object.
     * @param  int $meeting_id The local database ID of the meeting to delete.
     * @return int|false The number of rows deleted, or false on error.
     * @since  1.0.0
     */
    public function mjschool_delete_meeting_in_zoom( $meeting_id )
    {
        $meeting_id = intval($meeting_id);
        mjschool_append_audit_log('' . esc_html__('Virtual Classroom Deleted', 'mjschool') . '', get_current_user_id(), get_current_user_id(), 'delete', sanitize_textarea_field(wp_unslash($_REQUEST['page'])));
        global $wpdb;
        $table_zoom_meeting = $wpdb->prefix . 'mjschool_zoom_meeting'; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result             = $wpdb->query($wpdb->prepare("DELETE FROM $table_zoom_meeting WHERE meeting_id=%d", $meeting_id));
        return $result;
    }
    /**
     * Retrieves the list of participants for a past Zoom meeting.
     *
     * @param  string $meeting_uuid The UUID of the past meeting from Zoom.
     * @return object|string The decoded JSON response object from the Zoom API, or an empty string on error.
     * @since  1.0.0
     */
    public function mjschool_view_past_participle_list_in_zoom( $meeting_uuid )
    {
        try {
            $token        = mjschool_get_zoom_access_token();
            $encoded_uuid = urlencode($meeting_uuid);
            $url          = "https://api.zoom.us/v2/past_meetings/{$encoded_uuid}/participants";
            $headers      = array(
            'Authorization' => "Bearer {$token}",
            'Content-Type'  => 'application/json',
            );
            $response     = wp_remote_get(
                $url,
                array(
                'headers' => $headers,
                )
            );
            $result       = json_decode(wp_remote_retrieve_body($response));
        } catch ( Exception $e ) {
            $result = '';
        }
        return $result;
    }
}
