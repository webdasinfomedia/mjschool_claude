<?php
/**
 * Admin Event Management Form.
 *
 * This file handles the admin-side interface for creating and editing events within the Mjschool plugin.
 * It includes client-side validation, date and time pickers, file upload validation, and options to send
 * notifications via email or SMS. The form dynamically populates fields when editing an existing event.
 *
 * Key Features:
 * - Add or edit event details such as title, description, start/end dates, and times.
 * - Upload related event documents with validation for file type and size.
 * - Includes jQuery datepicker and timepicker integrations.
 * - Provides email and SMS notification toggles.
 * - Implements client-side validation and WordPress nonce verification for secure form submission.
 * - Supports custom fields dynamically fetched by module.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/event
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$event_id = 0;
if ( isset( $_REQUEST['event_id'] ) ) {
    $edit     = 0;
    $event_id = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['event_id'] ) ) ) );
} else {
    $edit = 0;
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
    $edit   = 1;
    $result = $mjschool_obj_event->mjschool_get_single_event( $event_id );
}
?>
<div class="mjschool-panel-body mjschool-custom-padding-0"><!--PANEL BODY.-->
    <form name="event_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="event_form"><!--ADD EVENT FORM-->
        <?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
        <input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
        <input type="hidden" name="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
        <div class="header">
            <h3 class="mjschool-first-header"><?php esc_html_e( 'Event Information', 'mjschool' ); ?></h3>
        </div>
        <div class="form-body mjschool-user-form"> <!-- mjschool-user-form start.-->
            <div class="row"><!--Row Div start.-->
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="event_title" maxlength="100" class="form-control text-input validate[required,custom[description_validation]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->event_title ); } elseif ( isset( $_POST['event_title'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['event_title'] ) ) ); } ?>" name="event_title">
                            <label  for="event_title"><?php esc_html_e( 'Event Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-note-text-notice">
                    <div class="form-group input">
                        <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                            <div class="form-field">
                                <textarea name="description" id="description" maxlength="1000" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"><?php if ( $edit ) { echo esc_textarea( sanitize_textarea_field( wp_unslash( $result->description ) ) ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) ); } ?></textarea>
                                <span class="mjschool-txt-title-label"></span>
                                <label class="text-area address active" for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                            </div>
                        </div>
                    </div>
                </div>
             
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="start_date_event" class="form-control date_picker validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->start_date ) ) ) ); } elseif ( isset( $_POST['start_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
                            <label class="active date_label" for="start_date_event"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input placeholder="<?php esc_attr_e( 'Start Time', 'mjschool' ); ?>" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->start_time ); } elseif ( isset( $_POST['start_time'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) ); } ?>" class="form-control timepicker event_start_time validate[required]" name="start_time" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input id="end_date_event" class="form-control date_picker validate[required] start_date datepicker2" type="text" name="end_date" autocomplete="off" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $result->end_date ) ) ) ); } elseif ( isset( $_POST['end_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
                            <label class="date_label" for="end_date_event"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                    <div class="form-group input">
                        <div class="col-md-12 form-control">
                            <input placeholder="<?php esc_attr_e( 'End Time', 'mjschool' ); ?>" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->end_time ); } elseif ( isset( $_POST['end_time'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) ); } ?>" class="form-control timepicker event_end_time validate[required]" name="end_time" />
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
                    <div class="form-group input">
                        <div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
                            <span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="Document"><?php esc_html_e( 'Document', 'mjschool' ); ?></span>
                            <div class="col-sm-12 mjschool-display-flex">
                                <input type="hidden" name="hidden_upload_file" value="<?php if ( $edit ) { echo esc_attr( $result->event_doc ); } elseif ( isset( $_POST['upload_file'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['upload_file'] ) ) ); } ?>">
                                <input id="upload_file" class="mjschool-file-validation" name="upload_file" type="file"  />
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ( ! $edit ) { ?>
                    <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
                        <div class="form-group">
                            <div class="col-md-12 form-control mjschool-rtl-relative-position mjschool_minheight_47px" >
                                <div class="row mjschool-padding-radio">
                                    <div>
                                        <label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_enable_event_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
                                        <input id="mjschool_enable_event_mail" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_event_mail" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
                        <div class="form-group">
                            <div class="col-md-12 form-control mjschool-rtl-relative-position mjschool_minheight_47px" >
                                <div class="row mjschool-padding-radio">
                                    <div>
                                        <label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_enable_event_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
                                        <input id="mjschool_enable_event_sms" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_event_sms" value="1" />&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
        // --------- Get Module-Wise Custom Field Data. --------------//
        $mjschool_custom_field_obj = new Mjschool_Custome_Field();
        $module                    = 'event';
        $custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
        ?>
        <!----------  save btn. 	-------------->
        <div class="form-body mjschool-user-form"> <!-- mjschool-user-form start.-->
            <div class="row"><!--Row Div start.-->
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <?php wp_nonce_field( 'save_event_nonce' ); ?>
                    <input id="save_event_btn" type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Event', 'mjschool' ); } else { esc_attr_e( 'Add Event', 'mjschool' ); } ?>" name="save_event" class="btn mjschool-save-btn event_time_validation" />
                </div>
            </div><!--Row Div End.-->
        </div><!-- mjschool-user-form End.-->
    </form><!--END ADD EVENT FORM.-->
</div><!--END PANEL BODY.-->