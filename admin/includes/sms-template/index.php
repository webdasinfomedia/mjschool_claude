<?php
/**
 * SMS Template Management Page.
 *
 * This file handles the administrative interface for managing SMS templates
 * used throughout the MJSchool plugin. It allows administrators to customize
 * and save message templates for various student-related notifications such as
 * admissions, approvals, exams, homework, attendance, holidays, events, fees and all.
 *
 * Key Features:
 * - Provides editable text areas for different SMS templates.
 * - Supports templates for both students and parents across multiple modules.
 * - Uses dynamic accordion sections to organize templates for easier navigation.
 * - Implements user access control (Add, Edit, Delete, View) based on role permissions.
 * - Saves templates securely in WordPress options using `update_option()`.
 * - Displays success messages after updates using the admin notice system.
 * - Supports custom placeholder variables like `{{student_name}}`, `{{school_name}}`, etc.
 * - Uses WordPress internationalization functions for translatable UI strings.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/sms-template
 * @since      1.0.0
 */
defined('ABSPATH') || exit;
$mjschool_role = mjschool_get_user_role(get_current_user_id());
if ($mjschool_role === 'administrator' ) {
    $user_access_add    = '1';
    $user_access_edit   = '1';
    $user_access_delete = '1';
    $user_access_view   = '1';
} else {
    $user_access        = mjschool_get_user_role_wise_filter_access_right_array('mjschool_template');
    $user_access_add    = $user_access['add'];
    $user_access_edit   = $user_access['edit'];
    $user_access_delete = $user_access['delete'];
    $user_access_view   = $user_access['view'];
}
$changed = 0;
if (isset($_REQUEST['save_attendance_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_attendance_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option( 'mjschool_attendance_mjschool_content', mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_attendance_mjschool_content'])) );
    $changed = 1;
}
if (isset($_REQUEST['save_add_fees_mjschool_template_for_student'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_student_fees_payment_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option( 'mjschool_fees_payment_mjschool_content_for_student', mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_fees_payment_mjschool_content_for_student'])) );
    $changed = 1;
}
if (isset($_REQUEST['save_add_fees_mjschool_template_for_parent'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_parent_fees_payment_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option( 'mjschool_fees_payment_mjschool_content_for_parent', mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_fees_payment_mjschool_content_for_parent'])) );
    $changed = 1;
}
if (isset($_REQUEST['save_add_fees_reminder_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_fees_payment_reminder_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option( 'mjschool_fees_payment_reminder_mjschool_content', mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_fees_payment_reminder_mjschool_content'])) );
    $changed = 1;
}
if (isset($_REQUEST['save_student_approve_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_student_approve_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_student_approve_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_student_approve_mjschool_content']))
    );
    $changed = 1;
}
if (isset($_REQUEST['save_student_admission_approve_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_admission_approve_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_student_admission_approve_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_student_admission_approve_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_holiday_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_holiday_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_holiday_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_holiday_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_event_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_event_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_event_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_event_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_leave_student_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_student_leave_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_leave_student_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_leave_student_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_leave_parent_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_parent_leave_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_leave_parent_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_leave_parent_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_exam_student_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_exam_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_exam_student_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_exam_student_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_exam_parent_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_parent_exam_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_exam_parent_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_exam_parent_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_homework_student_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_student_homework_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_homework_student_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_homework_student_mjschool_content']))
    );
    $changed = 1;
}

if (isset($_REQUEST['save_homework_parent_mjschool_template'])) {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'mjschool_parent_homework_template_sms_nonce')) {
        wp_die(esc_html__('Security check failed.', 'mjschool'));
    }
    update_option(
        'mjschool_homework_parent_mjschool_content',
        mjschool_strip_tags_and_stripslashes(wp_unslash($_REQUEST['mjschool_homework_parent_mjschool_content']))
    );
    $changed = 1;
}

if ($changed) {
    wp_redirect(admin_url() . 'admin.php?page=mjschool_sms_template&message=1');
    die();
}

?>
</script>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
    <div class="mjschool-main-list-margin-15px mt-2"><!-- Mjschool-main-list-margin-15px. -->
        <div class="row"><!-- Row. -->
            <?php
            $message = isset($_REQUEST['message']) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
            switch ( $message ) {
            case '1':
                $message_string = esc_html__('SMS Template Updated Successfully.', 'mjschool');
                break;
            }
            if ($message ) {
                ?>
                <div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
                    <p><?php echo esc_html($message_string); ?></p>
                    <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'mjschool'); ?></span></button>
                </div>
                <?php
            }
            $i = 1;
            ?>
            <div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
                <div class="mjschool-main-list-page"><!-- Mjschool-main-list-page. -->
                    <div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
                        
                        <div class="mjschool-main-email-template"><!--Mjschool-main-email-template. -->
                            <?php ++$i; ?>
                            <div id="mjschool-accordion" class="mjschool-accordion panel-group accordion accordion-flush mjschool-padding-top-15px-res" id="mjschool-accordion-flush" aria-multiselectable="false" role="tablist"><!-- Start accordion. -->
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Student Admission Approve SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_admission_approve_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_student_admission_approve_mjschool_content" name="mjschool_student_admission_approve_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_student_admission_approve_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_student_admission_approve_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_student_admission_approve_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Student Approve SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_approve_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_student_approve_mjschool_content" name="mjschool_student_approve_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_student_approve_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_student_approve_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_student_approve_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Exam SMS Template For Student', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_exam_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_exam_student_mjschool_content" name="mjschool_exam_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_exam_student_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_exam_student_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{exam_name}} - </strong><?php esc_html_e('Exam Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{date}} - </strong><?php esc_html_e('Exam Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_exam_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Exam SMS Template For Parent', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_exam_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_exam_parent_mjschool_content" name="mjschool_exam_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0 mjschool_70px" ><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_exam_parent_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_exam_parent_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{exam_name}} - </strong><?php esc_html_e('Exam Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{date}} - </strong><?php esc_html_e('Exam Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_exam_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Homework SMS Template For Student', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_homework_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_homework_student_mjschool_content" name="mjschool_homework_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_homework_student_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_homework_student_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Title', 'mjschool'); ?></span><br>
                                                                <span><strong>{{title}} - </strong><?php esc_html_e('Homework Title', 'mjschool'); ?></span><br>
                                                                <span><strong>{{date}} - </strong><?php esc_html_e('Submission Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_homework_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Homework SMS Template For Parent', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_homework_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_homework_parent_mjschool_content" name="mjschool_homework_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_homework_parent_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_homework_parent_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{parent_name}} - </strong><?php esc_html_e('Parent Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{title}} - </strong><?php esc_html_e('Homework Title', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_homework_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Attendance SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_attendance_mjschool_content" name="mjschool_attendance_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_attendance_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_attendance_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{current_date}} - </strong><?php esc_html_e('Today Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_attendance_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Leave SMS Template For Student', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_leave_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_leave_student_mjschool_content" name="mjschool_leave_student_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_leave_student_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_leave_student_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{date}} - </strong><?php esc_html_e('Leave Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_leave_student_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Leave SMS Template For Parent', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_leave_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_leave_parent_mjschool_content" name="mjschool_leave_parent_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_leave_parent_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_leave_parent_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{parent_name}} - </strong><?php esc_html_e('Parent Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{date}} - </strong><?php esc_html_e('Leave Date', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_leave_parent_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Fees Payment SMS Template For Student', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_fees_payment_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_fees_payment_mjschool_content_for_student" name="mjschool_fees_payment_mjschool_content_for_student" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_fees_payment_mjschool_content_for_student'))); ?></textarea>
                                                                <label for="mjschool_fees_payment_mjschool_content_for_student" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_add_fees_mjschool_template_for_student" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Fees Payment SMS Template For Parent', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_parent_fees_payment_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_fees_payment_mjschool_content_for_parent" name="mjschool_fees_payment_mjschool_content_for_parent" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_fees_payment_mjschool_content_for_parent'))); ?></textarea>
                                                                <label for="mjschool_fees_payment_mjschool_content_for_parent" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{parent_name}} - </strong><?php esc_html_e('Parent Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_add_fees_mjschool_template_for_parent" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Fees Payment Reminder SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_fees_payment_reminder_template_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_fees_payment_reminder_mjschool_content" name="mjschool_fees_payment_reminder_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_fees_payment_reminder_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_fees_payment_reminder_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{parent_name}} - </strong><?php esc_html_e('Parent Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_add_fees_reminder_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Event SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_event_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_event_mjschool_content" name="mjschool_event_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_event_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_event_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{student_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{event_title}} - </strong><?php esc_html_e('Event Title', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_event_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                                <div class="mt-1 accordion-item">
                                    <h4 class="accordion-header" id="flush-heading<?php echo esc_attr($i); ?>">
                                        <button class="accordion-button collapsed bg-gray" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse_collapse_<?php echo esc_attr($i); ?>" aria-controls="flush-heading<?php echo esc_attr($i); ?>">
                                            <?php esc_html_e('Holiday SMS Template', 'mjschool'); ?>
                                        </button>
                                    </h4>
                                    <div id="flush-collapse_collapse_<?php echo esc_attr($i); ?>" class="accordion-collapse mjschool-email-temp-rtl collapse " aria-labelledby="flush-heading<?php echo esc_attr($i); ?>" role="tabpanel" data-bs-parent="#mjschool-accordion">
                                        <div class="m-auto mjschool-panel-body mjschool-margin-20px">
                                            <form id="mjschool-email-template-form" class="mjschool-form-horizontal" method="post" action="" name="parent_form">
                                                <input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_holiday_sms_nonce' ) ); ?>">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12 form-control mjschool-texarea-padding-15px">
                                                                <textarea id="mjschool_holiday_mjschool_content" name="mjschool_holiday_mjschool_content" class="form-control validate[required] mjschool-texarea-custom-padding-0"><?php echo esc_textarea(mjschool_strip_tags_and_stripslashes(get_option('mjschool_holiday_mjschool_content'))); ?></textarea>
                                                                <label for="mjschool_holiday_mjschool_content" class="mjschool-textarea-label"><?php esc_html_e('Message Content', 'mjschool'); ?><span class="mjschool-require-field">*</span></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group input">
                                                            <div class="col-md-12">
                                                                <span><?php esc_html_e('You can use following variables in the SMS template:', 'mjschool'); ?></span><br>
                                                                <span><strong>{{user_name}} - </strong><?php esc_html_e('Student Name', 'mjschool'); ?></span><br>
                                                                <span><strong>{{title}} - </strong><?php esc_html_e('Holiday Title', 'mjschool'); ?></span><br>
                                                                <span><strong>{{school_name}} - </strong><?php esc_html_e('School Name', 'mjschool'); ?></span><br>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                                if ($user_access_add === '1' || $user_access_edit === '1' ) {
                                                    ?>
                                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                        <input type="submit" value="<?php esc_html_e('Save', 'mjschool'); ?>" name="save_holiday_mjschool_template" class="btn btn-success mjschool-save-btn" />
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php ++$i; ?>
                            </div><!--End accordion. -->
                        </div><!--Mjschool-main-email-template. -->
                    </div><!-- Mjschool-panel-body. -->
                </div><!-- Mjschool-main-list-page. -->
            </div><!-- Col-md-12. -->
        </div><!-- Row. -->
    </div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->
