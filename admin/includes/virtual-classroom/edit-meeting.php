<?php
/**
 * Virtual Classroom Meeting Management - Admin Interface.
 *
 * Displays and manages virtual classroom meeting details (Zoom integration) 
 * within the MJSchool plugin. This file is used to view and edit meeting data 
 * such as class, subject, teacher, timing, and route information.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/virtual-classroom
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
$meeting_data    = $obj_virtual_classroom->mjschool_get_single_meeting_data_in_zoom(sanitize_text_field(wp_unslash($_REQUEST['meeting_id'])));
$route_data      = mjschool_get_route_by_id($meeting_data->route_id);
$start_time_data = explode(':', $route_data->start_time);
$end_time_data   = explode(':', $route_data->end_time);
if ($start_time_data[1] === 0 || $end_time_data[1] === 0 ) {
    $start_time_minit = '00';
    $end_time_minit   = '00';
} else {
    $start_time_minit = $start_time_data[1];
    $end_time_minit   = $end_time_data[1];
}
if (isset($start_time_data[2]) ) {
    $start_time = $start_time_data[0] . ':' . $start_time_minit . ' ' . $start_time_data[2];
} else {
    $start_time = $start_time_data[0] . ':' . $start_time_minit;
}
if (isset($end_time_data[2]) ) {
    $end_time = $end_time_data[0] . ':' . $end_time_minit . ' ' . $end_time_data[2];
} else {
    $end_time = $end_time_data[0] . ':' . $end_time_minit;
}
?>
<div class="mjschool-panel-body">
    <form name="route_form" action="" method="post" class="mjschool-form-horizontal" id="meeting_form">
        <?php $mjschool_action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($mjschool_action); ?>">
        <input type="hidden" name="meeting_id" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_REQUEST['meeting_id']))); ?>">
        <input type="hidden" name="route_id" value="<?php echo esc_attr($meeting_data->route_id); ?>">
        <input type="hidden" name="class_id" value="<?php echo esc_attr($route_data->class_id); ?>">
        <input type="hidden" name="subject_id" value="<?php echo esc_attr($route_data->subject_id); ?>">
        <input type="hidden" name="class_section_id" value="<?php echo esc_attr($route_data->section_name); ?>">
        <input type="hidden" name="duration" value="<?php echo esc_attr($meeting_data->duration); ?>">
        <input type="hidden" name="weekday" value="<?php echo esc_attr($route_data->weekday); ?>">
        <input type="hidden" name="start_time" value="<?php echo esc_attr($start_time); ?>">
        <input type="hidden" name="end_time" value="<?php echo esc_attr($end_time); ?>">
        <input type="hidden" name="teacher_id" value="<?php echo esc_attr($route_data->teacher_id); ?>">
        <input type="hidden" name="zoom_meeting_id" value="<?php echo esc_attr($meeting_data->zoom_meeting_id); ?>">
        <input type="hidden" name="uuid" value="<?php echo esc_attr($meeting_data->uuid); ?>">
        <input type="hidden" name="meeting_join_link" value="<?php echo esc_url($meeting_data->meeting_join_link); ?>">
        <input type="hidden" name="meeting_start_link" value="<?php echo esc_url($meeting_data->meeting_start_link); ?>">
        <div class="header">
            <h3 class="mjschool-first-header"><?php esc_html_e('Virtual Classroom Information', 'mjschool'); ?></h3>
        </div>
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="class_name" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr(mjschool_get_class_name($route_data->class_id)); ?>" name="class_name" disabled>
                            <label for="userinput1"><?php esc_html_e('Class Name', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="class_section" class="form-control" maxlength="50" type="text" value="<?php echo esc_attr(mjschool_get_section_name($route_data->section_name)); ?>" name="class_section" disabled>
                            <label for="userinput1"><?php esc_html_e('Class Section', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="subject" class="form-control" type="text" value="<?php echo esc_attr(mjschool_get_single_subject_name($route_data->subject_id)); ?>" name="class_section" disabled>
                            <label for="userinput1"><?php esc_html_e('Subject', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="start_time" class="form-control" type="text" value="<?php echo esc_attr($start_time); ?>" name="start_time" disabled>
                            <label for="userinput1"><?php esc_html_e('Start Time', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="end_time" class="form-control" type="text" value="<?php echo esc_attr($end_time); ?>" name="end_time" disabled>
                            <label for="userinput1"><?php esc_html_e('End Time', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="start_date" class="form-control validate[required] text-input" type="text" placeholder="<?php esc_html_e('Enter Start Date', 'mjschool'); ?>" name="start_date" value="<?php echo esc_attr(date('Y-m-d', strtotime($meeting_data->start_date))); ?>" readonly>
                            <label for="userinput1"><?php esc_html_e('Start Date', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="end_date" class="form-control validate[required] text-input" type="text" placeholder="<?php esc_html_e('Enter Exam Date', 'mjschool'); ?>" name="end_date" value="<?php echo esc_attr(date('Y-m-d', strtotime($meeting_data->end_date))); ?>" readonly>
                            <label for="userinput1"><?php esc_html_e('End Date', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mjschool-note-text-notice">
                    <div class="form-group input">
                        <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                            <div class="form-field">
                                <textarea name="agenda" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="250"><?php echo esc_attr($meeting_data->agenda); ?></textarea>
                                <span class="mjschool-txt-title-label"></span>
                                <label class="text-area address"><?php esc_html_e('Topic', 'mjschool'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="password" class="form-control validate[minSize[8],maxSize[12]]" type="password" value="<?php echo esc_attr($meeting_data->password); ?>" name="password">
                            <label for="userinput1"><?php esc_html_e('Password', 'mjschool'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php wp_nonce_field('edit_meeting_admin_nonce'); ?>
        <div class="form-body mjschool-user-form">
            <div class="row">
                <div class="col-md-6">
                    <input type="submit" value="<?php if (! empty($route_data) ) { esc_html_e('Save Meeting', 'mjschool'); } else { esc_html_e('Create Meeting', 'mjschool'); } ?>" name="edit_meeting" class="btn mjschool-save-btn btn-success" />
                </div>
            </div>
        </div>
    </form>
</div>
