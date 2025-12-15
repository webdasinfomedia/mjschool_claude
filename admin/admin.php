<?php
/**
 * Admin Menu Registration for MJ School Management Plugin.
 *
 * This file registers all the admin menu and submenu pages for the MJ School plugin,
 * defining access based on user roles (administrator, management).
 * It dynamically loads the required pages and manages access rights for each section.
 *
 * @package    MjSchool
 * @subpackage MjSchool/admin/includes
 * @since      1.0.0
 */
if (! defined('ABSPATH') ) {
    exit;
}
/**
 * Registers the main admin menu and all submenus for the MJ School plugin.
 *
 * This function creates the primary admin menu for MJ School and attaches
 * various submenu pages under it. The available menu items depend on
 * the current user's role and assigned access rights.
 *
 * - Administrators have access to all system menus.
 * - Management users have role-based restricted access to menus defined by
 *   `mjschool_get_user_role_wise_access_right_array_by_page()`.
 *
 * The menu pages link to a single dashboard handler function (`mjschool_dashboard()`),
 * which dynamically loads content based on the selected menu.
 *
 * @since  1.0.0
 * @return void
 */
add_action('admin_menu', 'mjschool_admin_menu');
function mjschool_admin_menu()
{
    if (function_exists('mjschool_admin_menu') ) {
        $mjschool_user      = new WP_User(get_current_user_id());
        $mjschool_user_role = $mjschool_user->roles[0];
        if ($mjschool_user_role === 'administrator' ) {
            add_menu_page('MJ School', esc_attr__('MJ School', 'mjschool'), 'manage_options', 'mjschool', 'mjschool_dashboard', plugins_url('mjschool/assets/images/mjschool-management-system.png'), 7);
            add_submenu_page('mjschool', 'Licence Settings', esc_attr__('Licence Settings', 'mjschool'), 'manage_options', 'mjschool_setup', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Dashboard', 'mjschool'), esc_attr__('Dashboard', 'mjschool'), 'administrator', 'mjschool', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Admission', 'mjschool'), esc_attr__('Admission', 'mjschool'), 'administrator', 'mjschool_admission', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Student', 'mjschool'), esc_attr__('Student', 'mjschool'), 'administrator', 'mjschool_student', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Teacher', 'mjschool'), esc_attr__('Teacher', 'mjschool'), 'administrator', 'mjschool_teacher', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Support Staff', 'mjschool'), esc_attr__('Support Staff', 'mjschool'), 'administrator', 'mjschool_supportstaff', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Parent', 'mjschool'), esc_attr__('Parent', 'mjschool'), 'administrator', 'mjschool_parent', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Subject', 'mjschool'), esc_attr__('Subject', 'mjschool'), 'administrator', 'mjschool_Subject', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Class', 'mjschool'), esc_attr__('Class', 'mjschool'), 'administrator', 'mjschool_class', 'mjschool_dashboard');
            if (get_option('mjschool_enable_virtual_classroom') === 'yes' ) {
                add_submenu_page('mjschool', esc_attr__('Virtual Classroom', 'mjschool'), esc_attr__('Virtual Classroom', 'mjschool'), 'administrator', 'mjschool_virtual_classroom', 'mjschool_dashboard');
            }
            add_submenu_page('mjschool', esc_attr__('Class Routine', 'mjschool'), esc_attr__('Class Routine', 'mjschool'), 'administrator', 'mjschool_route', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Custom Room', 'mjschool'), esc_attr__('Custom Room', 'mjschool'), 'administrator', 'mjschool_class_room', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Custom Class', 'mjschool'), esc_attr__('Custom Class', 'mjschool'), 'administrator', 'mjschool_custom_class', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__(' Attendance', 'mjschool'), esc_attr__(' Attendance', 'mjschool'), 'administrator', 'mjschool_attendence', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Exam', 'mjschool'), esc_attr__('Exam', 'mjschool'), 'administrator', 'mjschool_exam', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Exam Hall', 'mjschool'), esc_attr__('Exam Hall', 'mjschool'), 'administrator', 'mjschool_hall', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Grade', 'mjschool'), esc_attr__('Grade', 'mjschool'), 'administrator', 'mjschool_grade', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Manage Marks', 'mjschool'), esc_attr__('Manage Marks', 'mjschool'), 'administrator', 'mjschool_result', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Homework', 'mjschool'), esc_attr__('Homework', 'mjschool'), 'administrator', 'mjschool_student_homewrok', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Hostel', 'mjschool'), esc_attr__('Hostel', 'mjschool'), 'administrator', 'mjschool_hostel', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Certificate', 'mjschool'), esc_attr__('Certificate', 'mjschool'), 'administrator', 'mjschool_certificate', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Leave', 'mjschool'), esc_attr__('Leave', 'mjschool'), 'administrator', 'mjschool_leave', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Documents', 'mjschool'), esc_attr__('Documents', 'mjschool'), 'administrator', 'mjschool_document', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Transport', 'mjschool'), esc_attr__('Transport', 'mjschool'), 'administrator', 'mjschool_transport', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Notice', 'mjschool'), esc_attr__('Notice', 'mjschool'), 'administrator', 'mjschool_notice', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Event', 'mjschool'), esc_attr__('Event', 'mjschool'), 'administrator', 'mjschool_event', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Message', 'mjschool'), esc_attr__('Message', 'mjschool'), 'administrator', 'mjschool_message', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Notification', 'mjschool'), esc_attr__('Notification', 'mjschool'), 'administrator', 'mjschool_notification', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Tax', 'mjschool'), esc_attr__('Tax', 'mjschool'), 'administrator', 'mjschool_tax', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Advance Reports', 'mjschool'), esc_attr__('Advance Reports', 'mjschool'), 'administrator', 'mjschool_advance_report', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Fees Payment', 'mjschool'), esc_attr__('Fees Payment', 'mjschool'), 'administrator', 'mjschool_fees_payment', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Payment', 'mjschool'), esc_attr__('Payment', 'mjschool'), 'administrator', 'mjschool_payment', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Holiday', 'mjschool'), esc_attr__('Holiday', 'mjschool'), 'administrator', 'mjschool_holiday', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Library', 'mjschool'), esc_attr__('Library', 'mjschool'), 'administrator', 'mjschool_library', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Custom Fields', 'mjschool'), esc_attr__('Custom Fields', 'mjschool'), 'administrator', 'mjschool_custom_field', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Report', 'mjschool'), esc_attr__('Report', 'mjschool'), 'administrator', 'mjschool_report', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Migration', 'mjschool'), esc_attr__('Migration', 'mjschool'), 'administrator', 'mjschool_Migration', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('SMS Setting', 'mjschool'), esc_attr__('SMS Setting', 'mjschool'), 'administrator', 'mjschool_sms_setting', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Email Template', 'mjschool'), esc_attr__('Email Template', 'mjschool'), 'administrator', 'mjschool_email_template', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('SMS Template', 'mjschool'), esc_attr__('SMS Template', 'mjschool'), 'administrator', 'mjschool_sms_template', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('Access Right', 'mjschool'), esc_attr__('Access Right', 'mjschool'), 'administrator', 'mjschool_access_right', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('How To Videos', 'mjschool'), esc_attr__('How To Videos', 'mjschool'), 'administrator', 'mjschool_system_videos', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('System Addon', 'mjschool'), esc_attr__('System Addon', 'mjschool'), 'administrator', 'mjschool_system_addon', 'mjschool_dashboard');
            add_submenu_page('mjschool', esc_attr__('General Settings', 'mjschool'), esc_attr__('General Settings', 'mjschool'), 'administrator', 'mjschool_general_settings', 'mjschool_dashboard');
        } elseif ($mjschool_user_role === 'management' ) {
            $admission         = mjschool_get_user_role_wise_access_right_array_by_page('admission');
            $supportstaff      = mjschool_get_user_role_wise_access_right_array_by_page('supportstaff');
            $exam_hall         = mjschool_get_user_role_wise_access_right_array_by_page('exam_hall');
            $grade             = mjschool_get_user_role_wise_access_right_array_by_page('grade');
            $notification      = mjschool_get_user_role_wise_access_right_array_by_page('notification');
            $custom_field      = mjschool_get_user_role_wise_access_right_array_by_page('custom_field');
            $migration         = mjschool_get_user_role_wise_access_right_array_by_page('migration');
            $mjschool_setting  = mjschool_get_user_role_wise_access_right_array_by_page('sms_setting');
            $email_template    = mjschool_get_user_role_wise_access_right_array_by_page('email_template');
            $mjschool_template = mjschool_get_user_role_wise_access_right_array_by_page('sms_setting');
            $access_right      = mjschool_get_user_role_wise_access_right_array_by_page('access_right');
            $general_settings  = mjschool_get_user_role_wise_access_right_array_by_page('general_settings');
            $teacher           = mjschool_get_user_role_wise_access_right_array_by_page('teacher');
            $student           = mjschool_get_user_role_wise_access_right_array_by_page('student');
            $parent            = mjschool_get_user_role_wise_access_right_array_by_page('parent');
            $subject           = mjschool_get_user_role_wise_access_right_array_by_page('subject');
            $class             = mjschool_get_user_role_wise_access_right_array_by_page('class');
            $virtual_classroom = mjschool_get_user_role_wise_access_right_array_by_page('virtual_classroom');
            $schedule          = mjschool_get_user_role_wise_access_right_array_by_page('schedule');
            $attendance        = mjschool_get_user_role_wise_access_right_array_by_page('attendance');
            $exam              = mjschool_get_user_role_wise_access_right_array_by_page('exam');
            $hostel            = mjschool_get_user_role_wise_access_right_array_by_page('hostel');
            $certificate       = mjschool_get_user_role_wise_access_right_array_by_page('certificate');
            $leave             = mjschool_get_user_role_wise_access_right_array_by_page('leave');
            $documents         = mjschool_get_user_role_wise_access_right_array_by_page('document');
            $homework          = mjschool_get_user_role_wise_access_right_array_by_page('homework');
            $manage_marks      = mjschool_get_user_role_wise_access_right_array_by_page('manage_marks');
            $feepayment        = mjschool_get_user_role_wise_access_right_array_by_page('feepayment');
            $tax               = mjschool_get_user_role_wise_access_right_array_by_page('feepayment');
            $payment           = mjschool_get_user_role_wise_access_right_array_by_page('payment');
            $transport         = mjschool_get_user_role_wise_access_right_array_by_page('transport');
            $notice            = mjschool_get_user_role_wise_access_right_array_by_page('notice');
            $event             = mjschool_get_user_role_wise_access_right_array_by_page('event');
            $message           = mjschool_get_user_role_wise_access_right_array_by_page('message');
            $holiday           = mjschool_get_user_role_wise_access_right_array_by_page('holiday');
            $library           = mjschool_get_user_role_wise_access_right_array_by_page('library');
            $report            = mjschool_get_user_role_wise_access_right_array_by_page('report');
            add_menu_page('School Management', esc_attr__('School Management', 'mjschool'), 'management', 'mjschool', 'mjschool_dashboard', plugins_url('mjschool/assets/images/mjschool-system-1.png'), 7);
            if (empty( $_SESSION['mjschool_verify'] ) ) {
                add_submenu_page('mjschool', 'Licence Settings', esc_attr__('Licence Settings', 'mjschool'), 'management', 'mjschool_setup', 'mjschool_dashboard');
            }
            add_submenu_page('mjschool', esc_attr__('Dashboard', 'mjschool'), esc_attr__('Dashboard', 'mjschool'), 'management', 'mjschool_dashboard', 'mjschool_dashboard');
            if ($admission === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Admission', 'mjschool'), esc_attr__('Admission', 'mjschool'), 'management', 'mjschool_admission', 'mjschool_dashboard');
            }
            if ($student === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Student', 'mjschool'), esc_attr__('Student', 'mjschool'), 'management', 'mjschool_student', 'mjschool_dashboard');
            }
            if ($teacher === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Teacher', 'mjschool'), esc_attr__('Teacher', 'mjschool'), 'management', 'mjschool_teacher', 'mjschool_dashboard');
            }
            if ($supportstaff === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Support Staff', 'mjschool'), esc_attr__('Support Staff', 'mjschool'), 'management', 'mjschool_supportstaff', 'mjschool_dashboard');
            }
            if ($parent === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Parent', 'mjschool'), esc_attr__('Parent', 'mjschool'), 'management', 'mjschool_parent', 'mjschool_dashboard');
            }
            if ($subject === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Subject', 'mjschool'), esc_attr__('Subject', 'mjschool'), 'management', 'mjschool_Subject', 'mjschool_dashboard');
            }
            if ($class === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Class', 'mjschool'), esc_attr__('Class', 'mjschool'), 'management', 'mjschool_class', 'mjschool_dashboard');
            }
            if ($virtual_classroom === 1 ) {
                if (get_option('mjschool_enable_virtual_classroom') === 'yes' ) {
                    add_submenu_page('mjschool', esc_attr__('Virtual Classroom', 'mjschool'), esc_attr__('Virtual Classroom', 'mjschool'), 'management', 'mjschool_virtual_classroom', 'mjschool_dashboard');
                }
            }
            if ($schedule === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Class Routine', 'mjschool'), esc_attr__('Class Routine', 'mjschool'), 'management', 'mjschool_route', 'mjschool_dashboard');
            }
            if ($attendance === 1 ) {
                add_submenu_page('mjschool', esc_attr__(' Attendance', 'mjschool'), esc_attr__(' Attendance', 'mjschool'), 'management', 'mjschool_attendence', 'mjschool_dashboard');
            }
            if ($exam === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Exam', 'mjschool'), esc_attr__('Exam', 'mjschool'), 'management', 'mjschool_exam', 'mjschool_dashboard');
            }
            if ($exam_hall === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Exam Hall', 'mjschool'), esc_attr__('Exam Hall', 'mjschool'), 'management', 'mjschool_hall', 'mjschool_dashboard');
            }
            if ($grade === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Grade', 'mjschool'), esc_attr__('Grade', 'mjschool'), 'management', 'mjschool_grade', 'mjschool_dashboard');
            }
            if ($manage_marks === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Manage Marks', 'mjschool'), esc_attr__('Manage Marks', 'mjschool'), 'management', 'mjschool_result', 'mjschool_dashboard');
            }
            if ($homework === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Homework', 'mjschool'), esc_attr__('Homework', 'mjschool'), 'management', 'mjschool_student_homewrok', 'mjschool_dashboard');
            }
            if ($hostel === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Hostel', 'mjschool'), esc_attr__('Hostel', 'mjschool'), 'management', 'mjschool_hostel', 'mjschool_dashboard');
            }
            if ($leave === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Leave', 'mjschool'), esc_attr__('Leave', 'mjschool'), 'management', 'mjschool_leave', 'mjschool_dashboard');
            }
            if ($documents === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Documents', 'mjschool'), esc_attr__('Documents', 'mjschool'), 'management', 'mjschool_document', 'mjschool_dashboard');
            }
            if ($certificate === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Certificate', 'mjschool'), esc_attr__('Certificate', 'mjschool'), 'management', 'mjschool_certificate', 'mjschool_dashboard');
            }
            if ($transport === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Transport', 'mjschool'), esc_attr__('Transport', 'mjschool'), 'management', 'mjschool_transport', 'mjschool_dashboard');
            }
            if ($notice === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Notice', 'mjschool'), esc_attr__('Notice', 'mjschool'), 'management', 'mjschool_notice', 'mjschool_dashboard');
            }
            if ($event === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Event', 'mjschool'), esc_attr__('Event', 'mjschool'), 'management', 'mjschool_event', 'mjschool_dashboard');
            }
            if ($message === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Message', 'mjschool'), esc_attr__('Message', 'mjschool'), 'management', 'mjschool_message', 'mjschool_dashboard');
            }
            if ($notification === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Notification', 'mjschool'), esc_attr__('Notification', 'mjschool'), 'management', 'mjschool_notification', 'mjschool_dashboard');
            }
            if ($tax === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Tax', 'mjschool'), esc_attr__('Tax', 'mjschool'), 'management', 'mjschool_tax', 'mjschool_dashboard');
            }
            if ($feepayment === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Fees Payment', 'mjschool'), esc_attr__('Fees Payment', 'mjschool'), 'management', 'mjschool_fees_payment', 'mjschool_dashboard');
            }
            if ($payment === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Payment', 'mjschool'), esc_attr__('Payment', 'mjschool'), 'management', 'mjschool_payment', 'mjschool_dashboard');
            }
            if ($holiday === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Holiday', 'mjschool'), esc_attr__('Holiday', 'mjschool'), 'management', 'mjschool_holiday', 'mjschool_dashboard');
            }
            if ($library === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Library', 'mjschool'), esc_attr__('Library', 'mjschool'), 'management', 'mjschool_library', 'mjschool_dashboard');
            }
            if ($custom_field === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Custom Fields', 'mjschool'), esc_attr__('Custom Fields', 'mjschool'), 'management', 'custom_field', 'mjschool_dashboard');
            }
            if ($report === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Report', 'mjschool'), esc_attr__('Report', 'mjschool'), 'management', 'mjschool_report', 'mjschool_dashboard');
            }
            if ($migration === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Migration', 'mjschool'), esc_attr__('Migration', 'mjschool'), 'management', 'mjschool_Migration', 'mjschool_dashboard');
            }
            if ($mjschool_setting === 1 ) {
                add_submenu_page('mjschool', esc_attr__('SMS Setting', 'mjschool'), esc_attr__('SMS Setting', 'mjschool'), 'management', 'mjschool_sms_setting', 'mjschool_dashboard');
            }
            if ($email_template === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Email Template', 'mjschool'), esc_attr__('Email Template', 'mjschool'), 'management', 'mjschool_email_template', 'mjschool_dashboard');
            }
            if ($mjschool_template === 1 ) {
                add_submenu_page('mjschool', esc_attr__('SMS Template', 'mjschool'), esc_attr__('SMS Template', 'mjschool'), 'management', 'mjschool_sms_template', 'mjschool_dashboard');
            }
            if ($access_right === 1 ) {
                add_submenu_page('mjschool', esc_attr__('Access Right', 'mjschool'), esc_attr__('Access Right', 'mjschool'), 'management', 'mjschool_access_right', 'mjschool_dashboard');
            }
            if ($general_settings === 1 ) {
                add_submenu_page('mjschool', esc_attr__('General Settings', 'mjschool'), esc_attr__('General Settings', 'mjschool'), 'management', 'mjschool_general_settings', 'mjschool_dashboard');
            }
        }
    } else {
        die();
    }
}
/**
 * Loads the main MJ School admin dashboard view.
 *
 * This function serves as the callback for all admin menu pages,
 * including submenus. It dynamically includes the dashboard content file.
 *
 * @since  1.0.0
 * @return void
 */
function mjschool_dashboard()
{
    include_once MJSCHOOL_ADMIN_DIR . '/dashboard.php';
}
