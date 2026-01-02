<?php
/**
 * School Management User Management Class.
 *
 * This file contains the Mjschool_User class, which handles
 * the creation, retrieval, updating, and deletion of user records.
 *
 * @package    MJSchool
 * @subpackage MJSchool/includes
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
/**
 * Manages all functionality related to user records.
 *
 * @since 1.0.0
 */
class Mjschool_User
{
    /**
     * Creates a new WordPress user with additional metadata and triggers related email notifications.
     *
     * @since 1.0.0
     *
     * @param array  $userdata     User core fields.
     * @param array  $usermetadata Custom user meta values.
     * @param string $firstname    First name.
     * @param string $middlename   Middle name.
     * @param string $lastname     Last name.
     * @param string $role         Primary role for the user.
     *
     * @return int|WP_Error Created user ID or WP_Error on failure.
     */
    public function mjschool_add_new_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role ) {
        $Schoolname = get_option( 'mjschool_name' );
        $MailSub    = get_option( 'mjschoool_student_assign_to_teacher_subject' );
        $MailCon    = get_option( 'mjschool_student_assign_to_teacher_content' );
        $user_id = wp_insert_user( $userdata );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        $user = new WP_User( $user_id );
        // Set the primary role (only if it's not already assigned).
        if ( ! in_array( $role, $user->roles, true ) ) {
            $user->set_role( $role );
        }
        if ( in_array( $role, array( 'student', 'parent', 'student_temp' ), true ) ) {
            if ( ! in_array( 'subscriber', $user->roles, true ) ) {
                $user->add_role( 'subscriber' );
            }
        } elseif ( in_array( $role, array( 'teacher', 'supportstaff' ), true ) ) {
            if ( ! in_array( 'author', $user->roles, true ) ) {
                $user->add_role( 'author' );
            }
        }
        $user_name = isset( $userdata['display_name'] ) ? $userdata['display_name'] : '';
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        mjschool_append_audit_log( esc_html__( 'User Added', 'mjschool' ) . '( ' . esc_html( $user_name ) . ' )', $user_id, get_current_user_id(), 'insert', $current_page );
        foreach ( $usermetadata as $key => $val ) {
            add_user_meta( $user_id, $key, $val, true );
        }
        if ( $user_id ) {
            $string                    = array();
            $string['{{user_name}}']   = $firstname . ' ' . $middlename . ' ' . $lastname;
            $string['{{school_name}}'] = get_option( 'mjschool_name' );
            $string['{{role}}']        = $role;
            $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
            $string['{{username}}']    = isset( $userdata['user_email'] ) ? $userdata['user_email'] : '';
            $string['{{Password}}']    = isset( $userdata['user_pass'] ) ? $userdata['user_pass'] : '';
            
            $MsgContent = get_option( 'mjschool_add_user_mail_content' );
            $MsgSubject = get_option( 'mjschool_add_user_mail_subject' );
            $message    = mjschool_string_replacement( $string, $MsgContent );
            $MsgSubject = mjschool_string_replacement( $string, $MsgSubject );
            $email      = isset( $userdata['user_email'] ) ? $userdata['user_email'] : '';
            
            mjschool_send_mail( $email, $MsgSubject, $message );
            
            // Send mail when student assigned to teacher
            if ( $role === 'student' && isset( $usermetadata['class_name'] ) ) {
                $teacher_obj = new Mjschool_Teacher();
                $TeacherIDs = $teacher_obj->mjschool_check_class_exits_in_teacher_class( $usermetadata['class_name'] );
                
                $string['{{school_name}}']  = $Schoolname;
                $string['{{student_name}}'] = mjschool_get_display_name( $user_id );
                $subject                    = get_option( 'mjschool_student_assign_teacher_mail_subject' );
                $MessageContent             = get_option( 'mjschool_student_assign_teacher_mail_content' );
                if ( ! empty( $TeacherIDs ) ) {
                    foreach ( $TeacherIDs as $teacher ) {
                        $TeacherData = get_userdata( absint( $teacher ) );
                        if ( $TeacherData ) {
                            $string['{{teacher_name}}'] = mjschool_get_display_name( $TeacherData->ID );
                            $message                    = mjschool_string_replacement( $string, $MessageContent );
                            mjschool_send_mail( $TeacherData->user_email, $subject, $message );
                        }
                    }
                }
            }
        }
        
        update_user_meta( $user_id, 'first_name', $firstname );
        update_user_meta( $user_id, 'last_name', $lastname );
        
        if ( $role === 'parent' ) {
            $child_list = isset( $_REQUEST['chield_list'] ) && is_array( $_REQUEST['chield_list'] ) ? array_map( 'absint', $_REQUEST['chield_list'] ) : array();
            
            if ( ! empty( $child_list ) ) {
                foreach ( $child_list as $child_id ) {
                    $child_id     = absint( $child_id );
                    $student_data = get_user_meta( $child_id, 'parent_id', true );
                    $parent_data  = get_user_meta( $user_id, 'child', true );
                    
                    if ( $student_data && is_array( $student_data ) ) {
                        if ( ! in_array( $user_id, $student_data, true ) ) {
                            $student_data[] = $user_id;
                            update_user_meta( $child_id, 'parent_id', $student_data );
                        }
                    } else {
                        update_user_meta( $child_id, 'parent_id', array( $user_id ) );
                    }
                    
                    if ( $parent_data && is_array( $parent_data ) ) {
                        if ( ! in_array( $child_id, $parent_data, true ) ) {
                            $parent_data[] = $child_id;
                            update_user_meta( $user_id, 'child', $parent_data );
                        }
                    } else {
                        add_user_meta( $user_id, 'child', array( $child_id ) );
                    }
                }
            }
        }
        
        if ( $role === 'teacher' && isset( $usermetadata['class_name'] ) ) {
            $mjschool_class = new Mjschool_Class();
            $Schoolname = get_option( 'mjschool_name' );
            $MailSub    = get_option( 'mjschoool_student_assign_to_teacher_subject' );
            $MailCon    = get_option( 'mjschool_student_assign_to_teacher_content' );
            if ( ! empty( $usermetadata['class_name'] ) ) {

                $std          = $mjschool_class->mjschool_get_student_by_class_id( $usermetadata['class_name'] );
                $student_name = '';
                if ( ! empty( $std ) ) {
                    
                    foreach ( $std as $student ) {
                        if ( isset( $student->ID ) && isset( $student->user_email ) && 
                            isset( $userdata['user_email'] ) && 
                            $userdata['user_email'] === $student->user_email ) {
                            
                            $student_name                = mjschool_get_display_name( $student->ID );
                            $MailArr['{{school_name}}']  = $Schoolname;
                            $MailArr['{{teacher_name}}'] = mjschool_get_display_name( $user_id );
                            $MailArr['{{class_name}}']   = $mjschool_class->mjschool_get_class_name( get_user_meta( $student->ID, 'class_name', true ) );
                            $MailArr['{{student_name}}'] = $student_name;
                            $MailSub                     = mjschool_string_replacement( $MailArr, $MailSub );
                            $MailCon                     = mjschool_string_replacement( $MailArr, $MailCon );
                            
                            mjschool_send_mail( $student->user_email, $MailSub, $MailCon );
                        }
                    }
                }
            }
        }
        
        return $user_id;
    }

    /**
     * Updates an existing WordPress user with role, metadata, and validation checks.
     *
     * @since 1.0.0
     *
     * @param array  $userdata     User core data.
     * @param array  $usermetadata Additional user meta fields.
     * @param string $firstname    First name.
     * @param string $middlename   Middle name.
     * @param string $lastname     Last name.
     * @param string $role         User role.
     *
     * @return int Updated user ID.
     */
    public function mjschool_update_user( $userdata, $usermetadata, $firstname, $middlename, $lastname, $role ) {
        // Ensure the user is logged in
        if ( ! is_user_logged_in() ) {
            wp_die( esc_html__( 'Security check failed! You are not logged in.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }
        if ( ! isset( $_POST['security'] ) || 
            ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'mjschool_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed! Invalid security token.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }
        
        // Get current user ID and role
        $current_user_id = get_current_user_id();
        $current_role    = mjschool_get_user_role( $current_user_id );
        
        // Prevent unauthorized role changes
        $allowed_roles = array( 'administrator', 'management', 'supportstaff', 'teacher' );
        if ( ! in_array( $current_role, $allowed_roles, true ) ) {
            wp_die( esc_html__( 'Permission denied! You do not have the required access.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }
        
        // Prevent non-admins from assigning the 'administrator' role
        if ( $role === 'administrator' && $current_role !== 'administrator' ) {
            wp_die( esc_html__( 'You are not allowed to assign the administrator role.', 'mjschool' ), 'Error', array( 'response' => 403 ) );
        }
        
        // Validate user ID
        if ( ! isset( $userdata['ID'] ) || ! is_numeric( $userdata['ID'] ) ) {
            wp_die( esc_html__( 'Invalid user ID! Please check the input.', 'mjschool' ), 'Error', array( 'response' => 400 ) );
        }
        
        $user_id = wp_update_user( $userdata );
        
        if ( ! is_wp_error( $user_id ) && isset( $userdata['user_login'] ) ) {
            global $wpdb;
            $new_email = sanitize_email( $userdata['user_login'] );
            
            // phpcs:disable
            $wpdb->update(
                $wpdb->users,
                array( 'user_login' => $new_email ),
                array( 'ID' => $user_id ),
                array( '%s' ),
                array( '%d' )
            );
            // phpcs:enable
        }
        
        $users = new WP_User( $user_id );
        
        // Set the primary role
        if ( ! in_array( $role, $users->roles, true ) ) {
            $users->set_role( $role );
        }
        
        if ( in_array( $role, array( 'student', 'parent', 'student_temp' ), true ) ) {
            if ( ! in_array( 'subscriber', $users->roles, true ) ) {
                $users->add_role( 'subscriber' );
            }
        } elseif ( in_array( $role, array( 'teacher', 'supportstaff' ), true ) ) {
            if ( ! in_array( 'author', $users->roles, true ) ) {
                $users->add_role( 'author' );
            }
        }
        
        update_user_meta( $user_id, 'first_name', $firstname );
        update_user_meta( $user_id, 'last_name', $lastname );
        
        $user = isset( $userdata['display_name'] ) ? $userdata['display_name'] : '';
        
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        
        mjschool_append_audit_log(
            esc_html__( 'User updated', 'mjschool' ) . '( ' . esc_html( $user ) . ' )',
            $user_id,
            get_current_user_id(),
            'edit',
            $current_page
        );
        
        foreach ( $usermetadata as $key => $val ) {
            update_user_meta( $user_id, $key, $val );
        }
        
        if ( $role === 'parent' ) {
            $child_list = isset( $_REQUEST['chield_list'] ) && is_array( $_REQUEST['chield_list'] )
                ? array_map( 'absint', $_REQUEST['chield_list'] )
                : array();
            
            $old_child = get_user_meta( $user_id, 'child', true );
            
            if ( ! empty( $old_child ) && is_array( $old_child ) ) {
                $different_insert_child = array_diff( $child_list, $old_child );
                $different_delete_child = array_diff( $old_child, $child_list );
                
                if ( ! empty( $different_insert_child ) ) {
                    foreach ( $different_insert_child as $child ) {
                        $child     = absint( $child );
                        $parent    = get_user_meta( $child, 'parent_id', true );
                        $old_child = get_user_meta( $user_id, 'child', true );
                        
                        if ( is_array( $old_child ) ) {
                            $old_child[] = $child;
                            update_user_meta( $user_id, 'child', $old_child );
                        }
                        
                        if ( empty( $parent ) ) {
                            update_user_meta( $child, 'parent_id', array( $user_id ) );
                        } elseif ( is_array( $parent ) ) {
                            $parent[] = $user_id;
                            update_user_meta( $child, 'parent_id', $parent );
                        }
                    }
                }
                
                if ( ! empty( $different_delete_child ) ) {
                    $child     = get_user_meta( $user_id, 'child', true );
                    $childdata = is_array( $child ) ? array_diff( $child, $different_delete_child ) : array();
                    update_user_meta( $user_id, 'child', $childdata );
                    
                    foreach ( $different_delete_child as $del_child ) {
                        $del_child = absint( $del_child );
                        $parent    = get_user_meta( $del_child, 'parent_id', true );
                        
                        if ( ! empty( $parent ) && is_array( $parent ) ) {
                            $key = array_search( $user_id, $parent, true );
                            if ( $key !== false ) {
                                unset( $parent[ $key ] );
                                update_user_meta( $del_child, 'parent_id', $parent );
                            }
                        }
                    }
                }
            } elseif ( ! empty( $child_list ) ) {
                foreach ( $child_list as $child_id ) {
                    $child_id     = absint( $child_id );
                    $student_data = get_user_meta( $child_id, 'parent_id', true );
                    $parent_data  = get_user_meta( $user_id, 'child', true );
                    
                    if ( $student_data && is_array( $student_data ) ) {
                        if ( ! in_array( $user_id, $student_data, true ) ) {
                            $student_data[] = $user_id;
                            update_user_meta( $child_id, 'parent_id', $student_data );
                        }
                    } else {
                        update_user_meta( $child_id, 'parent_id', array( $user_id ) );
                    }
                    
                    if ( $parent_data && is_array( $parent_data ) ) {
                        if ( ! in_array( $child_id, $parent_data, true ) ) {
                            $parent_data[] = $child_id;
                            update_user_meta( $user_id, 'child', $parent_data );
                        }
                    } else {
                        update_user_meta( $user_id, 'child', array( $child_id ) );
                    }
                }
            }
        }
        
        return $user_id;
    }

    /**
     * Updates WordPress user fields and metadata.
     *
     * @since 1.0.0
     *
     * @param array $userdata     User data fields.
     * @param array $usermetadata Meta fields to update.
     *
     * @return bool|int User ID on success, false on failure.
     */
    public function mjschool_update_user_profile( $userdata, $usermetadata ) {
        $user_id = wp_update_user( $userdata );
        
        if ( is_wp_error( $user_id ) ) {
            return false;
        }
        
        foreach ( $usermetadata as $key => $val ) {
            update_user_meta( $user_id, $key, $val );
        }
        
        return $user_id;
    }

    /**
     * Retrieves all plugin-specific users (student, teacher, support staff, parent).
     *
     * @since 1.0.0
     *
     * @return array List of WP_User objects.
     */
    public function mjschool_get_all_user_in_plugin() {
        $student      = get_users( array( 'role' => 'student' ) );
        $teacher      = get_users( array( 'role' => 'teacher' ) );
        $supportstaff = get_users( array( 'role' => 'supportstaff' ) );
        $parent       = get_users( array( 'role' => 'parent' ) );
        
        return array_merge( $student, $teacher, $supportstaff, $parent );
    }

    /**
     * Get user email address by ID.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @return string|false Email address.
     */
    public function mjschool_get_email_id_by_user_id( $user_id ) {
        $user_id = absint( $user_id );
        $user    = get_userdata( $user_id );
        
        if ( ! $user ) {
            return false;
        }
        return $user->user_email;
    }

    /**
     * Delete user meta and remove user account completely.
     *
     * @since 1.0.0
     * @param int $id User ID.
     * @return mixed True on success or WP_Error.
     */
    public function mjschool_delete_usedata( $id ) {
        global $wpdb;
        $table_usermeta = $wpdb->prefix . 'usermeta';
        $record_id  = absint( $id );
        $user_data  = get_userdata( $record_id );
        $user = '';
        if ( $user_data ) {
            $user = mjschool_get_display_name( $user_data->ID );
        }
        
        // Sanitize $_REQUEST['page'] with isset() check
        $current_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
        
        mjschool_append_audit_log( esc_html__( 'User Deleted', 'mjschool' ) . '( ' . $user . ' )', $record_id, get_current_user_id(), 'delete', $current_page );
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM $table_usermeta WHERE user_id = %d", $record_id ) );
        
        if ( ! current_user_can( 'delete_user', $record_id ) ) {
            return new WP_Error( 'permission_denied', 'You are not allowed to delete this user' );
        }
        
        $retuenval = wp_delete_user( $record_id );
        
        return $retuenval;
    }
    
    /**
     * Retrieves all users based on role.
     *
     * @param string $role User role.
     * @return array User list.
     * @since 1.0.0
     */
    public function mjschool_get_users_data( $role ) {
        $role               = sanitize_key( $role );
        $users_of_this_role = get_users( array( 'role' => $role ) );
        
        return $users_of_this_role;
    }

    /**
     * Retrieves the stored avatar/image of a user.
     *
     * @since 1.0.0
     *
     * @param int $uid User ID.
     *
     * @return string|false Image filename or false if not found.
     */
    public function mjschool_get_user_image( $uid ) {
        $uid       = absint( $uid );
        $usersdata = get_user_meta( $uid, 'mjschool_user_avatar', true );
        
        return $usersdata;
    }

}