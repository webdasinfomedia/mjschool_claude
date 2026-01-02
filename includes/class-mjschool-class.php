<?php
/**
 * School Management Class Management Class.
 *
 * This file contains the Mjschool_Class class, which handles
 * the creation, retrieval, updating, and deletion of class records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to class records.
 *
 * @since 1.0.0
 */
class Mjschool_Class
{
    /**
     * Get all sections under a class.
     *
     * @since 1.0.0
     * @param int|string $id Class ID or 'all'.
     * @return array Section list.
     */
    public function mjschool_get_class_sections( $id ) {
        global $wpdb;
        $table_mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
        if ( ! empty( $id ) ) {
            if ( $id === 'all' ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_class_section WHERE class_id=%s", $id ) );
            } else {
                $id = absint( $id );
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_class_section WHERE class_id=%d", $id ) );
            }
            return $result;
        }
        return array();
    }

    /**
     * Get class section name by ID.
     *
     * @since 1.0.0
     * @param int $id Section ID.
     * @return string Section name.
     */
    public function mjschool_get_class_sections_name( $id ) {
        global $wpdb;
        $table_mjschool_class_section      = $wpdb->prefix . 'mjschool_class_section';
        $class_section_id = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $class_sections_name = $wpdb->get_row( $wpdb->prepare( "SELECT section_name FROM $table_mjschool_class_section WHERE id=%d", $class_section_id ) );
        if ( ! empty( $class_sections_name ) ) {
            return $class_sections_name->section_name;
        } else {
            return ' ';
        }
    }

    /**
     * Get section name by ID.
     *
     * @since 1.0.0
     * @param int $id Section ID.
     * @return string Section name.
     */
    public function mjschool_get_section_name( $id ) {
        global $wpdb;
        $table_mjschool_class_section      = $wpdb->prefix . 'mjschool_class_section';
        $class_section_id = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_class_section WHERE id=%d", $class_section_id ) );
        
        if ( isset( $result->section_name ) ) {
            return $result->section_name;
        } else {
            return '';
        }
    }

    /**
     * Delete a class section and log audit.
     *
     * @since 1.0.0
     * @param int $section_id Section ID.
     * @return int Rows affected.
     */
    public function mjschool_delete_class_section( $section_id ) {
        global $wpdb;
        $table_mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
        $id         = absint( $section_id );
        
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Class Section Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_mjschool_class_section WHERE id = %d", $id ) );
        
        return $result;
    }

    /**
     * Retrieves class details using class ID.
     *
     * @param int $id Class ID.
     * @return object|null Class record.
     * @since 1.0.0
     */
    public function mjschool_get_class_by_id( $id ) {
        global $wpdb;
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        $sid        = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_class WHERE class_id=%d", $sid ) );
        
        return $retrieve_subject;
    }

    /**
     * Retrieves class name by class ID.
     *
     * @param int $id Class ID.
     * @return string Class name.
     * @since 1.0.0
     */
    public function mjschool_get_class_name_by_id( $id ) {
        global $wpdb;
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        $sid        = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_class WHERE class_id=%d", $sid ) );
        
        if ( isset( $retrieve_subject->class_name ) ) {
            return $retrieve_subject->class_name;
        }
        
        return '';
    }

    /**
     * Retrieves class ID based on class name.
     *
     * @param string $class_name Class name.
     * @return int Class ID.
     * @since 1.0.0
     */
    public function mjschool_get_class_id_by_name( $class_name ) {
        global $wpdb;
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        $class_name = sanitize_text_field( $class_name );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_class WHERE class_name = %s", $class_name ) );	
        if ( isset( $retrieve_subject->class_id ) ) {
            return absint( $retrieve_subject->class_id );
        }	
        return 0;
    }

    /**
     * Get class name by ID.
     *
     * @since 1.0.0
     * @param int $id Class ID.
     * @return string Class name.
     */
    public function mjschool_get_class_name( $id ) {
        global $wpdb;
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        $cid        = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $classname = $wpdb->get_row( $wpdb->prepare( "SELECT class_name FROM $table_mjschool_class WHERE class_id=%d", $cid ) );
        
        if ( ! empty( $classname ) && isset( $classname->class_name ) ) {
            return $classname->class_name;
        } else {
            return 'N/A';
        }
    }

    /**
     * Delete a class and log audit entry.
     *
     * @since 1.0.0
     * @param string $mjschool_table_name Table name.
     * @param int $id Class ID.
     * @return int Rows affected.
     */
    public function mjschool_delete_class( $mjschool_table_name, $id ) {
        global $wpdb;
        // Sanitize table name.
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $inserrt_table_name          = $wpdb->prefix . $mjschool_table_name;
        $record_id           = absint( $id );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $event = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $inserrt_table_name WHERE class_id=%d", $record_id ) );
        $class = isset( $event->class_name ) ? $event->class_name : '';
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Class Deleted', 'mjschool' ) . '( ' . $class . ' )', get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $inserrt_table_name WHERE class_id = %d", $record_id ) );
        
        return $result;
    }

    /**
    * Retrieves all exams by class ID.
    *
    * @param int $id Class ID.
    * @return array Exam list.
    * @since 1.0.0
    */
    public function mjschool_get_all_exam_by_class_id_all( $id ) {
        global $wpdb;
        $table_mjschool_exam = $wpdb->prefix . 'mjschool_exam';
        $class_id   = absint( $id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_exam WHERE class_id = %d", $class_id ) );
        return $retrieve_data;
    }
    
    /**
     * Retrieves class data for multiple class IDs.
     *
     * @param array $class_id Array of class IDs.
     * @return array List of class records.
     * @since 1.0.0
     */
    public function mjschool_get_all_class_data_by_class_array( $class_id ) {
        global $wpdb;
        $user_id    = absint( get_current_user_id() );
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        // Sanitize array of class IDs
        if ( ! is_array( $class_id ) ) {
            return array();
        }
        $class_id = array_map( 'absint', $class_id );
        if ( empty( $class_id ) ) {
            return array();
        }
        $placeholders = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_data = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table_mjschool_class WHERE class_id IN ($placeholders) OR creater_id = %d", array_merge( $class_id, array( $user_id ) ) )
        );
        
        return $retrieve_data;
    }
    /**
     * Retrieves route details by ID.
     *
     * @param int $route_id Route ID.
     * @return object|null Route record.
     * @since 1.0.0
     */
    public function mjschool_get_route_by_id( $route_id ) {
        global $wpdb;
        $table_mjschool_time_table = $wpdb->prefix . 'mjschool_time_table';
        $id         = absint( $route_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $retrieve_subject = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_mjschool_time_table WHERE route_id = %d", $id ) );
        return $retrieve_subject;
    }

    /**
     * Deletes a route and associated Zoom meeting (if exists).
     *
     * @param string $mjschool_table_name Table name.
     * @param int $route_id Route ID.
     * @return int Rows affected.
     * @since 1.0.0
     */
    public function mjschool_delete_route( $mjschool_table_name, $route_id ) {
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'Route Deleted', 'mjschool' ), get_current_user_id(), get_current_user_id(), 'delete', $current_page );
        global $wpdb;
        $obj_virtual_classroom = new mjschool_virtual_classroom();
        // Sanitize table name
        $mjschool_table_name = sanitize_key( $mjschool_table_name );
        $insert_table_name          = $wpdb->prefix . $mjschool_table_name;
        $id                  = absint( $route_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $insert_table_name WHERE route_id = %d", $id ) );

        if ( $result ) {
            $meeting_data = $obj_virtual_classroom->mjschool_get_single_meeting_by_route_data_in_zoom( $id );
            if ( ! empty( $meeting_data ) && isset( $meeting_data->meeting_id ) ) {
                $obj_virtual_classroom->mjschool_delete_meeting_in_zoom( $meeting_data->meeting_id );
            }
        }
        return $result;
    } 
    
    /**
     * Retrieves a list of students grouped by their class name.
     *
     * @since 1.0.0
     *
     * @return array Nested array of students grouped by class.
     */
    public function mjschool_get_student_group_by_class() {
        global $wpdb;
        $user_id    = absint( get_current_user_id() );
        $role_name  = mjschool_get_user_role( $user_id );
        $school_obj = new MJSchool_Management( $user_id );
        
        if ( $role_name === 'teacher' ) {
            $class_id     = get_user_meta( $user_id, 'class_name', true );
            $student_list = $school_obj->mjschool_get_teacher_student_list( $class_id );
        } else {
            $student_list = mjschool_get_all_student_list( 'student' );
        }
        
        $students = array();
        if ( ! empty( $student_list ) ) {
            foreach ( $student_list as $student_obj ) {
                if ( ! isset( $student_obj->ID ) ) {
                    continue;
                }
                
                $student_id   = absint( $student_obj->ID );
                $class_id     = get_user_meta( $student_id, 'class_name', true );
                $student      = mjschool_get_display_name( $student_id );
                $student_name = str_replace( "'", '', $student );
                $roll_id      = get_user_meta( $student_id, 'roll_id', true );
                
                if ( $class_id !== '' ) {
                    $mjschool_class = new Mjschool_Class();
                    $classname                     = $mjschool_class->mjschool_get_class_name( $class_id );
                    $students[ $classname ][ $student_id ] = $student_name . '( ' . esc_html( $roll_id ) . ' )';
                }
            }
        }
        
        return $students;
    }
    /**
     * Retrieves class list for user based on role access.
     *
     * @param int $user_id Optional user ID.
     * @return array Class data.
     * @since 1.0.0
     */
    public function mjschool_get_all_class( $user_id = 0 ) {
        global $wpdb;
        $table_mjschool_class = $wpdb->prefix . 'mjschool_class';
        if ( $user_id === 0 ) {
            $user_id = get_current_user_id();
        }
        $user_id = absint( $user_id );
        
        if ( is_user_logged_in() ) {
            $page_1 = 'class';
            $data   = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
            
            if ( ( isset( $data['own_data'] ) && $data['own_data'] === '1' ) && mjschool_get_roles( $user_id ) === 'teacher' ) {
                $class_id = get_user_meta( $user_id, 'class_name', true );
                
                // Ensure $class_id is an array
                if ( is_array( $class_id ) ) {
                    // Sanitize array values
                    $class_id = array_map( 'absint', $class_id );
                    if ( empty( $class_id ) ) {
                        return array();
                    }
                    // Use prepare with placeholders
                    $placeholders = implode( ', ', array_fill( 0, count( $class_id ), '%d' ) );
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                    $classdata = $wpdb->get_results(
                        $wpdb->prepare( "SELECT * FROM {$table_mjschool_class} WHERE class_id IN ({$placeholders})", $class_id ), ARRAY_A
                    );	
                    return $classdata;
                } else {
                    return array();
                }
            } else {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
                $classdata = $wpdb->get_results( "SELECT * FROM {$table_mjschool_class}", ARRAY_A );
                
                return $classdata;
            }
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe direct query with no user input
            $classdata = $wpdb->get_results( "SELECT * FROM {$table_mjschool_class}", ARRAY_A );
            
            return $classdata;
        }
    }

    /**
     * Retrieves students belonging to a specific class.
     *
     * @since 1.0.0
     *
     * @param int|string $id Class ID.
     *
     * @return array List of WP_User students.
     */
    public function mjschool_get_student_by_class_id( $id ) {
        $id = sanitize_text_field( $id );
        $student = get_users(
            array(
                'meta_key'   => 'class_name',
                'meta_value' => $id,
            )
        );
        return $student;
    }

    /**
     * Retrieves details of a single class section by section ID.
     *
     * @since 1.0.0
     *
     * @param int $section_id Section ID.
     *
     * @return object|null Section record object or null if not found.
     */
    public function mjschool_single_section( $section_id ) {
        global $wpdb;
        $mjschool_class_section = $wpdb->prefix . 'mjschool_class_section';
        $section_id             = absint( $section_id );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$mjschool_class_section} WHERE id = %d", $section_id )
        );
        return $result;
    }

}