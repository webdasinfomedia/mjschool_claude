<?php
/**
 * Student Management Page.
 *
 * This file manages the core functionality for the Student Management module in the MJSchool plugin.
 * It handles user role access control, student activation/deactivation, attendance viewing, 
 * form validation, AJAX-based interactions, and notification processes via email and SMS.
 *
 * Key Features:
 * - Manages access rights based on user roles (Administrator, Teacher, etc.).
 * - Handles student activation, deactivation, and approval workflows.
 * - Validates input forms using jQuery Validation Engine and Datepicker.
 * - Displays student data dynamically using DataTables with export (Print, PDF, CSV) options.
 * - Sends student activation emails and teacher assignment notifications.
 * - Supports SMS notifications for approved students via configured gateways.
 * - Includes nonce verification for secure form submissions.
 * - Organizes main student-related tabs: Student List, Add Student, and View Student.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
// -------- Check Browser Javascript. ----------//
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$mjschool_obj_leave        = new Mjschool_Leave();
mjschool_browser_javascript_check();
$mjschool_role              = mjschool_get_user_role(get_current_user_id());
$module            = 'student';
$user_custom_field = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module($module);
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
?>
<?php
if ($mjschool_role === 'administrator' ) {
    $user_access_add    = '1';
    $user_access_edit   = '1';
    $user_access_delete = '1';
    $user_access_view   = '1';
} else {
    $user_access        = mjschool_get_user_role_wise_filter_access_right_array('student');
    $user_access_add    = $user_access['add'];
    $user_access_edit   = $user_access['edit'];
    $user_access_delete = $user_access['delete'];
    $user_access_view   = $user_access['view'];
    if (isset($_REQUEST['page']) ) {
        if ($user_access_view === '0' ) {
            mjschool_access_right_page_not_access_message_admin_side();
            die();
        }
        if (! empty($_REQUEST['action']) ) {
            if ('student' === $user_access['page_link'] && ( $action === 'edit' ) ) {
                if ($user_access_edit === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
            if ('student' === $user_access['page_link'] && ( $action === 'delete' ) ) {
                if ($user_access_delete === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
            if ('student' === $user_access['page_link'] && ( $action === 'insert' ) ) {
                if ($user_access_add === '0' ) {
                    mjschool_access_right_page_not_access_message_admin_side();
                    die();
                }
            }
        }
    }
}
?>
<!--------- Script start. --------->
<?php
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$obj_mark                  = new Mjschool_Marks_Manage();
$mjschool_role                      = 'student';
// --------- Save active user. ------------//
if ( isset( $_POST['active_user'] ) ) {
    // phpcs:disable
    $act_user_id = isset( $_REQUEST['act_user_id'] ) ? intval( wp_unslash( $_REQUEST['act_user_id'] ) ) : 0;
    $class      = get_user_meta( $act_user_id, 'class_name', true );
    $roll_id_in = isset( $_REQUEST['roll_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['roll_id'] ) ) : '';
    $args = array(
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key'   => 'class_name',
                'value' => $class,
            ),
            array(
                'key'   => 'roll_id',
                'value' => $roll_id_in,
            ),
        ),
        'role' => 'student',
    );
    // phpcs:enable
    $userbyroll_no = get_users( $args );
    $is_rollno     = count( $userbyroll_no );
    if ( $is_rollno ) {
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=3' ) );
        die();
    } else {
        // Update roll id - sanitize stored value
        $act_user_id_post = isset( $_POST['act_user_id'] ) ? intval( wp_unslash( $_POST['act_user_id'] ) ) : 0;
        $roll_id_post     = isset( $_POST['roll_id'] ) ? sanitize_text_field( wp_unslash( $_POST['roll_id'] ) ) : '';
        update_user_meta( $act_user_id_post, 'roll_id', $roll_id_post );
        if ( isset( $_POST['mjschool_student_mail_service_enable'] ) || isset( $_POST['mjschool_student_sms_service_enable'] ) ) {
            if ( isset( $_POST['mjschool_student_mail_service_enable'] ) ) {
                $active_user_id = $act_user_id_post;
                $class_name     = get_user_meta( $active_user_id, 'class_name', true );
                $user_info      = get_userdata( $active_user_id );
                if ( $user_info ) {
                    $to      = sanitize_email( $user_info->user_email );
                    $subject = get_option( 'mjschool_student_activation_title' );

                    $Seach                        = array();
                    $Seach['{{student_name}}']    = $user_info->display_name;
                    $Seach['{{user_name}}']       = $user_info->user_login;
                    $Seach['{{class_name}}']      = mjschool_get_class_name( $class_name );
                    $Seach['{{email}}']           = $to;
                    $Seach['{{school_name}}']     = get_option( 'mjschool_name' );
                    $MsgContent                   = mjschool_string_replacement( $Seach, get_option( 'mjschool_student_activation_mailcontent' ) );
                    mjschool_send_mail( $to, $subject, $MsgContent );
                }
                // ----------- Student assigned teacher mail. ------------//
                $TeacherIDs    = mjschool_check_class_exits_in_teacher_class( $class_name );
                $string        = array();
                $string['{{school_name}}'] = get_option( 'mjschool_name' );
                $string['{{student_name}}'] = mjschool_get_display_name( $act_user_id_post );
                $subject      = get_option( 'mjschool_student_assign_teacher_mail_subject' );
                $MessageContent = get_option( 'mjschool_student_assign_teacher_mail_content' );
                foreach ( $TeacherIDs as $teacher ) {
                    $TeacherData                = get_userdata( $teacher );
                    $string['{{teacher_name}}'] = mjschool_get_display_name( $TeacherData->ID );
                    $message                    = mjschool_string_replacement( $string, $MessageContent );
                    if ( $TeacherData && isset( $TeacherData->user_email ) ) {
                        $teacher_email = sanitize_email( $TeacherData->user_email );
                        mjschool_send_mail( $teacher_email, $subject, $message );
                    }
                }
            }
            /* Approved SMS notification. */
            if ( isset( $_POST['mjschool_studnet_sms_service_enable'] ) ) {
                $message_content = 'Your account with ' . get_option( 'mjschool_name' ) . ' is approved';
                $type            = 'Attendance';
                $sms             = mjschool_send_notification( intval( $act_user_id_post ), $type, $message_content );
            }
        }
        $active_user_id = $act_user_id_post;
        if ( get_user_meta( $active_user_id, 'hash', true ) ) {
            delete_user_meta( $active_user_id, 'hash' );
        }
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=7' ) );
        die();
    }
}
// Deactivate student flow.
if ( isset( $_REQUEST['action'] ) && $action === 'deactivate' ) {
    if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'deactive_action' ) ) {
        $student_id_raw = isset( $_REQUEST['student_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) : '';
        $student_id     = intval( mjschool_decrypt_id( $student_id_raw ) );
        $hash           = md5( rand( 0, 1000 ) );
        delete_user_meta( $student_id, 'roll_id' );
        $result = update_user_meta( $student_id, 'hash', $hash );
        if ( $result ) {
            wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=15' ) );
            die();
        }
    } else {
        wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
    }
}
// Approve comment (leave) - approve action uses POST data; pass unslashed POST to function that should sanitize internally.
if ( isset( $_POST['approve_comment'] ) ) {
    $approve_post = wp_unslash( $_POST );
    $result       = $mjschool_obj_leave->mjschool_approve_leave( $approve_post );
    $student_id_param = isset( $_REQUEST['student_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) : '';
    if ( $result ) {
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=view_student&tab1=leave_list&student_id=' .rawurlencode( $student_id_param ) . '&message=12&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) );
        die();
    } else {
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=view_student&tab1=leave_list&student_id=' . rawurlencode( $student_id_param ) . '&message=14&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) );
        die();
    }
}
// Reject leave
if ( isset( $_POST['reject_leave'] ) ) {
    $reject_post = wp_unslash( $_POST );
    $result      = $mjschool_obj_leave->mjschool_reject_leave( $reject_post );
    $student_id_param = isset( $_REQUEST['student_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) : '';
    if ( $result ) {
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=view_student&tab1=leave_list&student_id=' . rawurlencode( $student_id_param ) . '&message=13&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) );
        die();
    } else {
        wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=view_student&tab1=leave_list&student_id=' . rawurlencode( $student_id_param ) . '&message=14&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) );
        die();
    }
}

// Delete leave
if ( isset( $_REQUEST['action'] ) ) {
    $action_req = sanitize_key( wp_unslash( $_REQUEST['action'] ) );
} else {
    $action_req = '';
}
if ( $action_req === 'delete' && isset( $_REQUEST['leave_id'] ) ) {
    if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'delete_action' ) ) {
        $leave_id_raw = sanitize_text_field( wp_unslash( $_REQUEST['leave_id'] ) );
        $leave_id     = intval( mjschool_decrypt_id( $leave_id_raw ) );
        $result       = $mjschool_obj_leave->mjschool_delete_leave( $leave_id );
        $student_id_action = isset( $_REQUEST['student_id_action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['student_id_action'] ) ) : '';
        if ( $result ) {
            wp_safe_redirect( admin_url( 'admin.php?page=mjschool_student&tab=view_student&tab1=leave_list&student_id=' . rawurlencode( $student_id_action ) . '&message=11&_wpnonce=' . rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) );
            die();
        }
    } else {
        wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
    }
}

// Export student CSV file code.
if ( isset( $_REQUEST['exportstudentin_csv'] ) ) {
    // sanitize inputs used in export
    $post_class_name    = isset( $_POST['class_name'] ) ? sanitize_text_field( wp_unslash( $_POST['class_name'] ) ) : '';
    $post_class_section = isset( $_POST['class_section'] ) ? sanitize_text_field( wp_unslash( $_POST['class_section'] ) ) : '';

    if ( $post_class_name !== '' && $post_class_section === '' ) {

        $student_list = get_users(
            array(
                'meta_key'   => 'class_name',
                'meta_value' => $post_class_name,
                'role'       => 'student',
            )
        );

    } elseif ( $post_class_section !== '' ) {

        $args = array(
            'role'       => 'student',
            'meta_query' => array(
                array(
                    'key'   => 'class_name',
                    'value' => $post_class_name,
                ),
                array(
                    'key'   => 'class_section',
                    'value' => $post_class_section,
                ),
            ),
        );

        $student_list = get_users( $args );

    } else {
        $student_list = get_users( array( 'role' => 'student' ) );
    }
    // ----- CSV file add custom export csv file. ------//
    foreach ( $student_list as $retrive_data ) {
        $student_array[] = $retrive_data->ID;
        $student_id      = implode(',', $student_array);
    }
    $module                    = 'student';
    $module_record_id          = $student_id;
    $mjschool_custom_field_obj = new Mjschool_Custome_Field();
    $all_custom_field_name     = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module($module);
    if (! empty($student_list) ) {
        $header   = array();
        $header[] = 'Username';
        $header[] = 'Email';
        $header[] = 'Password';
        $header[] = 'Roll No';
        $header[] = 'Class Name';
        $header[] = 'Class Section';
        $header[] = 'First Name';
        $header[] = 'Middle Name';
        $header[] = 'Last Name';
        $header[] = 'Gender';
        $header[] = 'Birth Date';
        $header[] = 'Address';
        $header[] = 'City Name';
        $header[] = 'State Name';
        $header[] = 'Zip Code';
        $header[] = 'Mobile Number';
        $header[] = 'Alternate Mobile Number';
        $header[] = 'user_profile';
        // -----Add custom filed label in excel sheet.  ----//
        foreach ( $all_custom_field_name as $custom_field ) {
            $header[] = $custom_field->field_label;
        }
        $filename = 'export/mjschool-export-student.csv';
        $fh       = fopen(MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w') or wp_die("can't open file");
        fputcsv($fh, $header);
        foreach ( $student_list as $retrive_data ) {
            $uid                 = $retrive_data->ID;
            $umetadata           = mjschool_get_user_image($uid);
            $row                 = array();
            $user_info           = get_userdata($retrive_data->ID);
            $row[]               = $user_info->user_login;
            $row[]               = $user_info->user_email;
            $row[]               = '';
            $row[]               = get_user_meta($retrive_data->ID, 'roll_id', true);
            $class_id            = get_user_meta($retrive_data->ID, 'class_name', true);
            $classname           = mjschool_get_class_name($class_id);
            $row[]               = $classname;
            $class_section_id    = get_user_meta($retrive_data->ID, 'class_section', true);
            $class_sections_name = mjschool_get_class_sections_name($class_section_id);
            $row[]               = $class_sections_name;
            $row[]               = get_user_meta($retrive_data->ID, 'first_name', true);
            $row[]               = get_user_meta($retrive_data->ID, 'middle_name', true);
            $row[]               = get_user_meta($retrive_data->ID, 'last_name', true);
            $row[]               = get_user_meta($retrive_data->ID, 'gender', true);
            $row[]               = get_user_meta($retrive_data->ID, 'birth_date', true);
            $row[]               = get_user_meta($retrive_data->ID, 'address', true);
            $row[]               = get_user_meta($retrive_data->ID, 'city', true);
            $row[]               = get_user_meta($retrive_data->ID, 'state', true);
            $row[]               = get_user_meta($retrive_data->ID, 'zip_code', true);
            $row[]               = get_user_meta($retrive_data->ID, 'mobile_number', true);
            $row[]               = get_user_meta($retrive_data->ID, 'alternet_mobile_number', true);
            $uid                 = $retrive_data->ID;
            $umetadata           = mjschool_get_user_image($uid);
            if (! empty($umetadata) ) {
                $row[] = $umetadata;
            } else {
                $row[] = '';
            }
            // -----Add custom filed value in excel sheet.  ----//
            foreach ( $all_custom_field_name as $custom_field ) {
                $all_custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_field_value_meta_value_by_filed_and_student_id($retrive_data->ID, $custom_field->id);
                if ($custom_field->field_type === 'file' ) {
                    $row[] = content_url() . '/uploads/school_assets/' . $all_custom_field_value;
                } else {
                    $row[] = $all_custom_field_value;
                }
            }
            fputcsv($fh, $row);
        }
        fclose($fh);
        // Download csv file.
        ob_clean();
        $file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-student.csv'; // File location.
        $mime = 'text/plain';
        header('Content-Type:application/force-download');
        header('Pragma: public');       // Required.
        header('Expires: 0');           // No cache.
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Last-Modified: ' . date('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        readfile($file);
        die();
    } else {
        wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&message=10' ) );
        die();
    }
}
// --------- Save student. -----------//
if ( isset( $_POST['save_student'] ) ) {

    // DO NOT sanitize nonce.
    $nonce = $_POST['_wpnonce'];
    if ( wp_verify_nonce( $nonce, 'save_teacher_admin_nonce' ) ) {

        // ---- Basic sanitized fields. ---- //
        $firstname  = sanitize_text_field( wp_unslash( $_POST['first_name'] ) );
        $lastname   = sanitize_text_field( wp_unslash( $_POST['last_name'] ) );
        $middlename = sanitize_text_field( wp_unslash( $_POST['middle_name'] ) );

        // ---- Prepare userdata. ---- //
        $userdata = array(
            'user_login'    => sanitize_email( wp_unslash( $_POST['email'] ) ),
            'user_nicename' => null,
            'user_email'    => sanitize_email( wp_unslash( $_POST['email'] ) ),
            'user_url'      => null,
            'display_name'  => $firstname . ' ' . $middlename . ' ' . $lastname,
        );

        // ---- Password (NO sanitizing). ---- //
        if ( isset( $_POST['password'] ) && $_POST['password'] !== '' ) {
            $userdata['user_pass'] = mjschool_password_validation( $_POST['password'] );
        }

        // ---- User Profile Photo. ---- //
        $photo = isset( $_POST['mjschool_user_avatar'] ) ? sanitize_text_field( wp_unslash( $_POST['mjschool_user_avatar'] ) ) : '';

        // ---- Zoom Classroom Option. ---- //
        $zoom_add_status = ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) ? 'yes' : 'no';

        // ---- Phone sanitizing. ---- //
        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $document_content = array();
        if ( ! empty( $_FILES['document_file']['name'] ) ) {
            $count_array = count( $_FILES['document_file']['name'] );
            for ( $a = 0; $a < $count_array; $a++ ) {
                if ( $_FILES['document_file']['size'][ $a ] > 0 && ! empty( $_POST['document_title'][ $a ] ) ) {
                    $document_title = sanitize_text_field( wp_unslash( $_POST['document_title'][ $a ] ) );
                    $document_file = mjschool_upload_document_user_multiple( $_FILES['document_file'], $a, $document_title );
                } elseif ( ! empty( $_POST['user_hidden_docs'][ $a ] ) && ! empty( $_POST['document_title'][ $a ] ) ) {
                    $document_title = sanitize_text_field( wp_unslash( $_POST['document_title'][ $a ] ) );
                    $document_file  = sanitize_text_field( wp_unslash( $_POST['user_hidden_docs'][ $a ] ) );
                }
                if ( ! empty( $document_file ) && ! empty( $document_title ) ) {
                    $document_content[] = array(
                        'document_title' => $document_title,
                        'document_file'  => $document_file,
                    );
                }
            }
        }
        $final_document = ! empty( $document_content ) ? wp_json_encode( $document_content ) : '';
        $sibling_value = array();
        if ( ! empty( $_POST['siblingsclass'] ) ) {
            foreach ( $_POST['siblingsclass'] as $key => $value ) {
                $sibling_value[] = array(
                    'siblingsclass'   => sanitize_text_field( wp_unslash( $value ) ),
                    'siblingssection' => sanitize_text_field( wp_unslash( $_POST['siblingssection'][ $key ] ) ),
                    'siblingsstudent' => sanitize_text_field( wp_unslash( $_POST['siblingsstudent'][ $key ] ) ),
                );
            }
        }
        $usermetadata = array(
            'admission_no'           => sanitize_text_field( wp_unslash( $_POST['admission_no'] ) ),
            'roll_id'                => sanitize_textarea_field( wp_unslash( $_POST['roll_id'] ) ),
            'middle_name'            => $middlename,
            'gender'                 => sanitize_text_field( wp_unslash( $_POST['gender'] ) ),
            'birth_date'             => sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ),
            'address'                => sanitize_textarea_field( wp_unslash( $_POST['address'] ) ),
            'city'                   => sanitize_text_field( wp_unslash( $_POST['city_name'] ) ),
            'state'                  => sanitize_text_field( wp_unslash( $_POST['state_name'] ) ),
            'zip_code'               => sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ),
            'class_name'             => sanitize_text_field( wp_unslash( $_POST['class_name'] ) ),
            'class_section'          => sanitize_text_field( wp_unslash( $_POST['class_section'] ) ),
            'phone'                  => $phone,
            'mobile_number'          => sanitize_text_field( wp_unslash( $_POST['mobile_number'] ) ),
            'user_document'          => $final_document,
            'sibling_information'    => wp_json_encode( $sibling_value ),
            'alternet_mobile_number' => sanitize_text_field( wp_unslash( $_POST['alternet_mobile_number'] ) ),
            'mjschool_user_avatar'   => $photo,
            'zoom_add_status'        => $zoom_add_status,
            'created_by'             => get_current_user_id(),
        );
        $class_name_for_check = sanitize_text_field( wp_unslash( $_POST['class_name'] ) );
        $roll_id_for_check    = sanitize_text_field( wp_unslash( $_POST['roll_id'] ) );
        $userbyroll_no = get_users(
            array(
                'meta_query' => array(
                    'relation' => 'AND',
                    array( 'key' => 'class_name', 'value' => $class_name_for_check ),
                    array( 'key' => 'roll_id', 'value' => $roll_id_for_check ),
                ),
                'role' => 'student'
            )
        );
        $is_rollno = count( $userbyroll_no );
        if ( $action === 'edit' ) {
            $student_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) );   
            $admission_no    = sanitize_text_field( wp_unslash( $_POST['admission_no'] ) ); 
            $args = array(
             'meta_key'   => 'admission_no',
             'meta_value' => $admission_no,
             'number'     => 1,
             'fields'     => 'ID' // Only get user ID.
            );
            
            $admission_user_id = '';
            $user_query        = new WP_User_Query($args);
            if (! empty($user_query->get_results()) ) {
                $admission_user_id = $user_query->get_results()[0];
            }
            if (! empty($admission_user_id) && $admission_user_id != $student_id ) {
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=16' ) );
                die();
            }
            if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'edit_action') ) {
                $userdata['ID'] = $student_id;
                $roll_no_cheack = mjschool_check_student_roll_no_exist_or_not(sanitize_text_field(wp_unslash($_POST['roll_id'])), sanitize_text_field(wp_unslash($_POST['class_name'])), $student_id);
                if ($roll_no_cheack === 1 ) {
                    $result = mjschool_update_user($userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role);
                    if (! empty($sibling_value) ) {
                        foreach ( $sibling_value as $sibling ) {
                            $sibling_student_id = intval($sibling['siblingsstudent']);
                            if ($sibling_student_id > 0 && $sibling_student_id != $student_id ) {
                                $existing_siblings = get_user_meta($sibling_student_id, 'sibling_information', true);
                                if (! empty($existing_siblings) ) {
                                    $existing_siblings = json_decode($existing_siblings, true);
                                } else {
                                    $existing_siblings = array();
                                }
                                // Check if already added to avoid duplicates.
                                $already_exists = false;
                                foreach ( $existing_siblings as $sibling_info ) {
                                    if (isset($sibling_info['siblingsstudent']) && $sibling_info['siblingsstudent'] === $student_id ) {
                                        $already_exists = true;
                                        break;
                                    }
                                }
                                if (! $already_exists ) {
                                    $existing_siblings[] = array(
                                        'siblingsclass'   => sanitize_text_field(wp_unslash($_POST['class_name'])),
                                        'siblingssection' => sanitize_text_field(wp_unslash($_POST['class_section'])),
                                        'siblingsstudent' => $student_id,
                                    );
                                    update_user_meta($sibling_student_id, 'sibling_information', json_encode($existing_siblings));
                                }
                            }
                        }
                    }
                    $student = $userdata['display_name'];
                    mjschool_append_audit_log('' . esc_html__('Student Updated', 'mjschool') . '( ' . $student . ' )' . '', $student_id, get_current_user_id(), 'edit', sanitize_text_field(wp_unslash($_REQUEST['page'])));
                    // Custom field file update. //
                    $module              = 'student';
                    $custom_field_update = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise($module, $result);
                    wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=2' ) );
                    die();
                } else {
                    wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=3' ) );
                    die();
                }
            } else {
                wp_die(esc_html__('Security check failed!', 'mjschool'));
            }
        } else // ---------- Insert student. -----------//
        {
            /*Setup wizard. */
            mjschool_setup_wizard_steps_updates('step6_student');
            $admission_no    = sanitize_text_field( wp_unslash( $_POST['admission_no'] ) ); 
            $args = array(
            'meta_key'   => 'admission_no',
            'meta_value' => $admission_no,
            'number'     => 1,
            'fields'     => 'ID' // Only get user ID.
            );
            
            $admission_user_id = '';
            $user_query        = new WP_User_Query($args);
            if (! empty($user_query->get_results()) ) {
                $admission_user_id = $user_query->get_results()[0];
            }
            if (! empty($admission_user_id) ) {
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=16' ) );
                die();
            }
            if (! email_exists($_POST['email']) ) {
                if ($is_rollno ) {
                    wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=3' ) );
                    die();
                } else {
                    $result     = mjschool_add_new_user($userdata, $usermetadata, $firstname, $middlename, $lastname, $mjschool_role);
                    $student_id = $result;
                    if (! empty($sibling_value) ) {
                        foreach ( $sibling_value as $sibling ) {
                               $sibling_student_id = intval($sibling['siblingsstudent']);
                            if ($sibling_student_id > 0 && $sibling_student_id != $student_id ) {
                                $existing_siblings = get_user_meta($sibling_student_id, 'sibling_information', true);
                                if (! empty($existing_siblings) ) {
                                    $existing_siblings = json_decode($existing_siblings, true);
                                } else {
                                    $existing_siblings = array();
                                }
                                // Check if already added to avoid duplicates.
                                $already_exists = false;
                                foreach ( $existing_siblings as $sibling_info ) {
                                    if (isset($sibling_info['siblingsstudent']) && $sibling_info['siblingsstudent'] === $student_id ) {
                                        $already_exists = true;
                                        break;
                                    }
                                }
                                if (! $already_exists ) {
                                    $existing_siblings[] = array(
                                        'siblingsclass'   => sanitize_text_field(wp_unslash($_POST['class_name'])),
                                        'siblingssection' => sanitize_text_field(wp_unslash($_POST['class_section'])),
                                        'siblingsstudent' => $student_id,
                                    );
                                    update_user_meta($sibling_student_id, 'sibling_information', json_encode($existing_siblings));
                                }
                            }
                        }
                    }
                    $student = $userdata['display_name'];
                    mjschool_append_audit_log('' . esc_html__('Student Added', 'mjschool') . '( ' . $student . ' )' . '', $result, get_current_user_id(), 'insert', sanitize_text_field(wp_unslash($_REQUEST['page'])));
                    // Custom field file insert. //
                    $module             = 'student';
                    $insert_custom_data = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise($module, $result);
                    if ($result ) {
                        wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=1' ) );
                        die();
                    }
                }
            } else {
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=4' ) );
                die();
            }
        }
    }
}
// ----------- Delete student code. -------- //
if ($action === 'delete' ) {
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_action') ) {
        $student_id = intval(mjschool_decrypt_id(sanitize_text_field(wp_unslash($_REQUEST['student_id']))));
        $childs     = get_user_meta(intval($student_id), 'parent_id', true);
        if (! empty($childs) ) {
            foreach ( $childs as $key => $childvalue ) {
                $parents = get_user_meta($childvalue, 'child', true);
                if (! empty($parents) ) {
                    if (( $key = array_search(intval($student_id), $parents) ) !== false ) {
                        unset($parents[ $key ]);
                        update_user_meta($childvalue, 'child', $parents);
                    }
                }
            }
        }
        $result = mjschool_delete_usedata(intval($student_id));
        if ($result ) {
            wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=5' ) );
            die();
        }
    } else {
        wp_die(esc_html__('Security check failed!', 'mjschool'));
    }
}
// ------- Multiple student record delete code. -------//
if (isset($_REQUEST['delete_selected']) ) {
    if (! empty($_REQUEST['id']) ) {
        foreach ( $_REQUEST['id'] as $id ) {
            $childs = get_user_meta($id, 'parent_id', true);
            if (! empty($childs) ) {
                foreach ( $childs as $key => $childvalue ) {
                    $parents = get_user_meta($childvalue, 'child', true);
                    if (! empty($parents) ) {
                        if (( $key = array_search($id, $parents) ) !== false ) {
                            unset($parents[ $key ]);
                            update_user_meta($childvalue, 'child', $parents);
                        }
                    }
                }
            }
            $result = mjschool_delete_usedata($id);
        }
    }
    if ($result ) {
        wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=5' ));
        die();
    }
}
// ---------- Student print. ------------//
if (isset($_REQUEST['print']) && sanitize_text_field(wp_unslash($_REQUEST['print'])) === 'pdf' ) {
    $sudent_id = intval(mjschool_decrypt_id(sanitize_text_field(wp_unslash($_REQUEST['student']))));
    mjschool_download_result_pdf($sudent_id);
}
// ------ Upload CSV Code. -----------//
if (isset($_REQUEST['upload_csv_file']) ) {
    $post_class_id   = ! empty($_POST['class_name']) ? (int) $_POST['class_name'] : 0;
    $post_section_id = ! empty($_POST['class_section']) ? (int) $_POST['class_section'] : 0;
    $nonce           = $_POST['_wpnonce'];
    if (wp_verify_nonce($nonce, 'upload_teacher_admin_nonce') ) {
        if (isset($_FILES['csv_file']) ) {
            $errors     = array();
            $file_name  = sanitize_file_name($_FILES['csv_file']['name']);
            $file_size  = $_FILES['csv_file']['size'];
            $file_tmp   = $_FILES['csv_file']['tmp_name'];
            $file_type  = $_FILES['csv_file']['type'];
            $value      = explode('.', $_FILES['csv_file']['name']);
            $file_ext   = strtolower(array_pop($value));
            $extensions = array( 'csv' );
            $upload_dir = wp_upload_dir();
            if (in_array($file_ext, $extensions) === false ) {
                $module      = 'student';
                $status      = 'file type error';
                $log_message = 'Student import fail due to invalid file type';
                mjschool_append_csv_log($log_message, get_current_user_id(), $module, $status);
                $err      = esc_attr__('This file not allowed, please choose a CSV file.', 'mjschool');
                $errors[] = $err;
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=uploadstudent&message=8' ) );
                die();
            }
            // ------------ Check file size. ------------//
            if ($file_size > 2097152 ) {
                $errors[] = 'File size limit 2 MB';
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=uploadstudent&message=9' ) );
                die();
            }
            if (empty($errors) === true ) {
                $rows         = array_map('str_getcsv', file($file_tmp));
                $header       = array_map('trim', array_map('strtolower', array_shift($rows)));
                $arraycheck   = array( 'username', 'email', 'password', 'roll no', 'class name', 'class section', 'first name', 'middle name', 'last name', 'gender', 'birth date', 'address', 'city name', 'state name', 'zip code', 'mobile number', 'alternate mobile number', 'user_profile' );
                $fields       = array_diff($header, $arraycheck);
                $csv          = array();
                $user_created = false;
                foreach ( $rows as $row ) {
                    global $wpdb;
                    $csv         = array_combine($header, $row);
                    $class_names = sanitize_text_field($csv['class name']);
                    if ($post_class_id === 0 ) {
                        $class_name = sanitize_text_field($csv['class name']);
                        $class_id = mjschool_get_class_id_by_name($class_names);
                    } else {
                        $class_id = $post_class_id;
                    }
                    if ($post_section_id === 0 ) {
                        $class_section = sanitize_text_field($csv['class section']);
                        $section_id = mjschool_get_section_id_by_section_name($class_id, $class_section);
                    } else {
                        $section_id = $post_section_id;
                    }
                    $custom_fields = array();
                    $username      = sanitize_user($csv['username'], true);
                    $email         = sanitize_email($csv['email']);
                    $user_id       = 0;
                    if (isset($csv['password']) ) {
                        $password = $csv['password'];
                    } else {
                        $password = rand();
                    }
                    $problematic_row = false;
                    $studentExists   = true;
                    if (username_exists($username) ) { // If user exists, we take his ID by login.
                        $user_object = get_user_by('login', $username);
                        $user_id     = $user_object->ID;
                        $mjschool_role_name   = mjschool_get_user_role($user_id);
                        if ($mjschool_role_name != 'administrator' ) {
                            if (! empty($password) ) {
                                wp_set_password($password, $user_id);
                            }
                        }
                    } elseif (email_exists($email) ) { // If the email is registered, we take the user from this.
                        $user_object     = get_user_by('email', $email);
                        $user_id         = $user_object->ID;
                        $problematic_row = true;
                        $mjschool_role_name       = mjschool_get_user_role($user_id);
                        if ($mjschool_role_name != 'administrator' ) {
                            if (! empty($password) ) {
                                wp_set_password($password, $user_id);
                            }
                        }
                    } else {
                        $studentExists    = false;
                        $admission_number = mjschool_generate_admission_number();
                        if (empty($password) ) { // If user not exist and password is empty but the column is set, it will be generated.
                            $password = wp_generate_password();
                        }
                        $user_id = wp_create_user($username, $password, $email);
                        if ($user_id ) {
                            $user_created = true;
                        }
                    }
                    if (is_wp_error($user_id) ) { // In case the user is generating errors after this checks.
                        $module      = 'student';
                        $emails      = $email;
                        $status      = 'Fail';
                        $log_message = "Student import fail for: $emails";
                        mjschool_append_csv_log($log_message, get_current_user_id(), $module, $status);
                        echo '<input type="hidden" id="mjschool_import_error" value="' . esc_attr( $username ) . '">';
                        continue;
                    }
                    if ($mjschool_role_name != 'administrator' ) {
                        $studentClass = get_user_meta($user_id, 'class_name', true);
                        wp_update_user(
                            array(
                            'ID'   => $user_id,
                            'role' => 'student',
                            )
                        );
                        $mjschool_user = new WP_User($user_id);
                        $mjschool_user->add_role('subscriber');
                    }
                    update_user_meta($user_id, 'active', true);
                    update_user_meta($user_id, 'class_name', $class_id);
                    $class_id_sanitized = sanitize_text_field($class_id);
                    $roll_no_sanitized  = sanitize_text_field($csv['roll no']);
                    $cache_key = 'students_by_class_' . $class_id_sanitized . '_roll_' . $roll_no_sanitized;
                    $user_ids  = wp_cache_get($cache_key);
                    if ($user_ids === false ) {
                     // phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                        $args       = array(
                        'meta_query' => array(
                        'relation' => 'AND',
                        array(
                        'key'     => 'class_name',
                        'value'   => $class_id_sanitized,
                        'compare' => '=',
                        ),
                        array(
                        'key'     => 'roll_id',
                        'value'   => $roll_no_sanitized,
                        'compare' => '=',
                        ),
                        array(
                        'key'     => $wpdb->prefix . 'capabilities',
                        'value'   => 'student',
                        'compare' => 'LIKE',
                        ),
                        ),
                        'fields'     => 'ID',
                        );
                        $user_query = new WP_User_Query($args);
                        $user_ids   = $user_query->get_results();
                     // phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                        wp_cache_set($cache_key, $user_ids, 'user_lookup', 300); // Cache for 5 minutes.
                    }
                    $userbyroll_no = array_map('get_userdata', $user_ids);
                    
                    $rollReset = true;
                    if ($studentExists ) {
                        $rollReset = false;
                    } else {
                        // Student not exists.
                        if (count($userbyroll_no) || $csv['roll no'] === '' ) {  // Roll exists.
                            $rollReset = true;
                        } else { // Roll not exists.
                            $rollReset = false;
                        }
                    }
                    if ($rollReset ) {
                        $roll = '';
                        add_user_meta($user_id, 'hash', rand());
                    } else {
                        $roll = $csv['roll no'];
                    }
                    // ---------- Student Record Insert. ----------//
                    $user_id1 = wp_update_user(
                        array(
                        'ID'           => $user_id,
                        'display_name' => $csv['first name'] . ' ' . $csv['middle name'] . ' ' . $csv['last name'],
                        )
                    );
                    if (isset($_POST['class_section']) ) {
                        update_user_meta($user_id, 'class_section', sanitize_text_field($section_id));
                    }
                    if (isset($csv['roll no']) ) {
                        update_user_meta($user_id, 'roll_id', $roll);
                    }
                    if (isset($csv['first name']) ) {
                        update_user_meta($user_id, 'first_name', sanitize_text_field($csv['first name']));
                    }
                    if (isset($csv['last name']) ) {
                        update_user_meta($user_id, 'last_name', sanitize_text_field($csv['last name']));
                    }
                    if (isset($csv['middle name']) ) {
                        update_user_meta($user_id, 'middle_name', sanitize_text_field($csv['middle name']));
                    }
                    if (isset($csv['gender']) ) {
                        $gender = strtolower(trim($csv['gender']));
                        // Optionally validate allowed values.
                        if (in_array($gender, array( 'male', 'female' )) ) {
                            update_user_meta($user_id, 'gender', $gender);
                        } else {
                            update_user_meta($user_id, 'gender', ''); // Or skip, or set default.
                        }
                    }
                    if (isset($csv['birth date']) ) {
                        update_user_meta($user_id, 'birth_date', sanitize_text_field($csv['birth date']));
                    }
                    if (isset($csv['address']) ) {
                        update_user_meta($user_id, 'address', sanitize_textarea_field($csv['address']));
                    }
                    if (isset($csv['city name']) ) {
                        update_user_meta($user_id, 'city', sanitize_text_field($csv['city name']));
                    }
                    if (isset($csv['state name']) ) {
                        update_user_meta($user_id, 'state', sanitize_text_field($csv['state name']));
                    }
                    if (isset($csv['zip code']) ) {
                        update_user_meta($user_id, 'zip_code', sanitize_text_field($csv['zip code']));
                    }
                    if (isset($csv['mobile number']) ) {
                        update_user_meta($user_id, 'mobile_number', sanitize_text_field($csv['mobile number']));
                    }
                    if (! empty($csv['user_profile']) ) {
                        $upload_dir = wp_upload_dir();
                        $photo      = $upload_dir['baseurl'] . '/' . $csv['user_profile'];
                        update_user_meta($user_id, 'mjschool_user_avatar', $photo);
                    }
                    if (isset($csv['alternate mobile number']) ) {
                        update_user_meta($user_id, 'alternet_mobile_number', sanitize_text_field($csv['alternate mobile number']));
                    }
                    if (isset($csv['phone number']) ) {
                        update_user_meta($user_id, 'phone', sanitize_text_field($csv['phone number']));
                    }
                    foreach ( $fields as $field_name ) {
                        global $wpdb;
                        $wpnc_custom_field_metas = $wpdb->prefix . 'mjschool_custom_field_metas';
                        $custom_field            = mjschool_get_single_custom_field_data_by_name($field_name, 'student');
                        if ($custom_field->field_type === 'date' ) {
                            $field_value = date('Y-m-d', strtotime($csv[ $field_name ]));
                        } elseif ($custom_field->field_type === 'file' ) {
                            if (! empty($csv[ $field_name ]) ) {
                                $upload_dir  = wp_upload_dir();
                                $field_value = $csv[ $field_name ];
                            } else {
                                $field_value = '';
                            }
                        } else {
                            $field_value = $csv[ $field_name ];
                        }
                        $custom_meta_data['module']           = 'student';
                        $custom_meta_data['module_record_id'] = $user_id;
                        $custom_meta_data['custom_fields_id'] = $custom_field->id;
                        $custom_meta_data['field_value']      = $field_value;
                        $custom_meta_data['created_at']       = date('Y-m-d H:i:s');
                        $custom_meta_data['updated_at']       = date('Y-m-d H:i:s');
                     // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
                        $insert_custom_meta_data = $wpdb->insert($wpnc_custom_field_metas, $custom_meta_data);
                    }
                    if ($user_created ) {
                        update_user_meta($user_id, 'admission_no', $admission_number);
                        if ( isset($_REQUEST['mjschool_import_student_mail']) && sanitize_text_field(wp_unslash($_REQUEST['mjschool_import_student_mail'])) === '1' ) {
                            if ($user_id ) {
                                $userdata                  = get_userdata($user_id);
                                $string                    = array();
                                $string['{{user_name}}']   = $userdata->display_name;
                                $string['{{school_name}}'] = get_option('mjschool_name');
                                $string['{{role}}']        = 'student';
                                $string['{{login_link}}']  = site_url() . '/index.php/mjschool-login-page';
                                $string['{{username}}']    = $userdata->user_email;
                                $string['{{Password}}']    = $password;
                                $MsgContent                = get_option('mjschool_add_user_mail_content');
                                $MsgSubject                = get_option('mjschool_add_user_mail_subject');
                                $message                   = mjschool_string_replacement($string, $MsgContent);
                                $MsgSubject                = mjschool_string_replacement($string, $MsgSubject);
                                $email                     = $userdata->user_email;
                                mjschool_send_mail($email, $MsgSubject, $message);
                            }
                        }
                        $module      = 'student';
                        $emails      = isset($email) ? $email : ''; // or collect all emails
                        $status      = 'Success';
                        $log_message = "Import CSV Successful: {$emails}";
                        mjschool_append_csv_log($log_message, get_current_user_id(), $module, $status);
                    }
                }
                $success = 1;
            } else {
                foreach ( $errors as &$error ) {
                    echo esc_html($error);
                }
            }
            if (isset($success) ) {
                wp_safe_redirect(admin_url( 'admin.php?page=mjschool_student&tab=studentlist&message=6' ) );
                die();
            }
        }
    } else {
        wp_die(esc_html__('Security check failed!', 'mjschool'));
    }
}
$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'studentlist';
?>
<div class="mjschool-page-inner"><!--------- Page inner. -------->
    <div class="mjschool-main-list-margin-15px"><!----- List page padding. --------->
        <?php
        // ---------- Student Messages. ---------//
        $message = isset($_REQUEST['message']) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
        switch ( $message ) {
        case '1':
            $message_string = esc_html__('Student Added Successfully.', 'mjschool');
            break;
        case '2':
            $message_string = esc_html__('Student Updated Successfully.', 'mjschool');
            break;
        case '3':
            $message_string = esc_html__('Student Roll No. Already Exist.', 'mjschool');
            break;
        case '4':
            $message_string = esc_html__("Student's Username Or Email-id Already Exist.", 'mjschool');
            break;
        case '5':
            $message_string = esc_html__('Student Deleted Successfully.', 'mjschool');
            break;
        case '6':
            $message_string = esc_html__('Student CSV Uploaded Successfully.', 'mjschool');
            break;
        case '7':
            $message_string = esc_html__('Student Activated Successfully.', 'mjschool');
            break;
        case '8':
            $message_string = esc_html__('This file not allowed, please choose a CSV file.', 'mjschool');
            break;
        case '9':
            $message_string = esc_html__('File size limit 2 MB.', 'mjschool');
            break;
        case '10':
            $message_string = esc_html__('Records not found.', 'mjschool');
            break;
        case '11':
            $message_string = esc_html__('Leave Deleted Successfully', 'mjschool');
            break;
        case '12':
            $message_string = esc_html__('Leave Approved Successfully', 'mjschool');
            break;
        case '13':
            $message_string = esc_html__('Leave Rejected Successfully', 'mjschool');
            break;
        case '14':
            $message_string = esc_html__('Oops, Something went wrong.', 'mjschool');
            break;
        case '15':
            $message_string = esc_html__('Student Deactivated Successfully.', 'mjschool');
            break;
        case '16':
            $message_string = esc_html__('Student Admission No. Already Exist.', 'mjschool');
            break;
        }
        if ($message ) {
            ?>
            <div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjchool_student_margins_7px">
                <p><?php echo esc_html($message_string); ?></p>
                <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'mjschool'); ?></span></button>
            </div>
            <?php
        }
        ?>
        <div class="row"> <!------- Row Div. --------->
            <div class="col-md-12 mjschool-custom-padding-0">
                <div class="mjschool-main-list-page">
                    <?php
                    // ---------- Student list tab. --------//
                    if ($active_tab === 'studentlist' ) {
                        include_once MJSCHOOL_ADMIN_DIR . '/student/student-list.php';
                    }
                    if ($active_tab === 'addstudent' ) {
                        include_once MJSCHOOL_ADMIN_DIR . '/student/student.php';
                    }
                    if ($active_tab === 'view_student' ) {
                        include_once MJSCHOOL_ADMIN_DIR . '/student/view-student.php';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div><!------- Row Div. --------->
</div><!----- List page padding. --------->
</div> <!--------- Page inner. -------->
