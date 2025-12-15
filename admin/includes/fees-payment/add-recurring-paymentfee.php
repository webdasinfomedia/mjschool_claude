<?php
/**
 * Add/Edit Recurring Payment Fee Form.
 *
 * Handles the display and submission of recurring payment fees in the MJSchool plugin.
 * Provides functionality to create or edit a recurring payment invoice, including:
 * - Selecting class, section, and students.
 * - Choosing fee types and applying taxes.
 * - Entering amount, description, start and end dates.
 * - Setting recurrence type (weekly, monthly, quarterly, half-yearly).
 * - Enabling or disabling the recurring fee status.
 * - Integration with WordPress nonces for secure form submission.
 *
 * The form validates required fields and uses multiselects for users and taxes.
 *
 *
 * @since      1.0.0
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 */
defined( 'ABSPATH' ) || exit;
$obj_feespayment = new Mjschool_Feespayment();
if ($active_tab === 'addrecurringpayment' ) {
    $recurring_fees_id = 0;
    if ( isset( $_REQUEST['recurring_fees_id'] ) ) {
        $recurring_fees_id = intval(mjschool_decrypt_id( wp_unslash($_REQUEST['recurring_fees_id']) ) );
    }
    $edit = 0;
    if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
        $edit   = 1;
        $result = $mjschool_obj_feespayment->mjschool_get_single_recurring_fees($recurring_fees_id);
    }
	?>
    <div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
        <!----- Panel Body. --------->
        <form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
            <?php $mjschool_action = isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
            <input type="hidden" name="action" value="<?php echo esc_attr($mjschool_action); ?>">
            <input type="hidden" name="recurring_fees_id" value="<?php echo esc_attr($recurring_fees_id); ?>">
            <input type="hidden" name="last_recurrence_date" value="<?php echo esc_attr($result->recurring_enddate); ?>">
            <div class="form-body mjschool-user-form">
                <div class="row">
                    <?php
                    if ($edit) {
                    	?>
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox">
                            <div class="form-group">
                                <div class="col-md-12 form-control">
                                    <div class="row mjschool-padding-radio">
                                        <div class="input-group">
                                            <label class="mjschool-custom-top-label" for="classis_limit">
                                                <?php esc_html_e( 'Recurrence Type', 'mjschool' ); ?>
                                            </label>
                                            <div class="d-inline-block mjschool-gender-line-height-24px">
                                                <?php
                                                $recurrence_type = 'one_time';
                                                if ($edit) {
                                                    $recurrence_type = $result->recurring_type;
                                                } elseif ( isset( $_POST['recurrence_type'] ) ) {
                                                    $recurrence_type = sanitize_text_field( wp_unslash($_POST['recurrence_type']));
                                                }
                                                ?>
                                                <label class="radio-inline">
                                                    <input type="radio" value="weekly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'weekly', $recurrence_type ); ?> />
                                                    <?php esc_html_e( 'Weekly', 'mjschool' ); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" value="monthly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'monthly', $recurrence_type ); ?> />
                                                    <?php esc_html_e( 'Monthly', 'mjschool' ); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" value="quarterly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'quarterly', $recurrence_type ); ?> />
                                                    <?php esc_html_e( 'Quarterly', 'mjschool' ); ?>
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" value="half_yearly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'half_yearly', $recurrence_type ); ?> />
                                                    <?php esc_html_e( 'Half- Yearly', 'mjschool' ); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 input">
                        </div>
                    	<?php
                    }
                    if ($edit) {
                    	?>
                        <div class="col-md-6 input">
                            <label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry">
                                <?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
                            </label>
                            <?php
                            $classval = ($edit && !empty($result->class_id ) ) ? $result->class_id : '';
                            ?>
                            <select name="class_id" id="fees_class_list_id" class="form-control validate[required] load_fees_drop mjschool-max-width-100px">
                                <option value="all class" <?php selected($classval, '' ); ?>>
                                    <?php esc_html_e( 'All Class', 'mjschool' ); ?>
                                </option>
                                <?php if ( ! empty( $classval ) ) : ?>
                                    <option value="<?php echo esc_attr($classval); ?>" selected>
                                        <?php echo esc_html( mjschool_get_class_name_by_id($classval ) ); ?>
                                    </option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 input mjschool-class-section-hide">
                            <label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry">
                                <?php esc_html_e( 'Class Section', 'mjschool' ); ?>
                            </label>
                            <?php
                            if ($edit) {
                                $sectionval = $result->section_id;
                            } elseif ( isset( $_POST['class_section'] ) ) {
                                $sectionval = sanitize_text_field( wp_unslash($_POST['class_section']));
                            } else {
                                $sectionval = '';
                            }
                            ?>
                            <select name="class_section" class="form-control mjschool-max-width-100px" id="fees_class_section_id">
                                <option value="">
                                    <?php esc_html_e( 'All Section', 'mjschool' ); ?>
                                </option>
                                <?php
                                if ($edit) {
                                    foreach (mjschool_get_class_sections($result->class_id) as $sectiondata) {
                                		?>
                                        <option value="<?php echo esc_attr($sectiondata->id); ?>" <?php selected($sectionval, $sectiondata->id); ?>>
                                            <?php echo esc_html( $sectiondata->section_name); ?>
                                        </option>
                                		<?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        $class_id = !empty($result->class_id) ? $result->class_id : null;
                        ?>
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
                            <div id="messahe_test"></div>
                            <div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
                                <span class="user_display_block" id="user_display_block">
                                    <select name="selected_users[]" id="selected_users" class="form-control mjschool-min-width-250px validate[required]" multiple="multiple">
                                        <?php
                                        $class_id = !empty($result->class_id) ? $result->class_id : null;
                                        if ($class_id === '' ) {
                                            $student_list = get_users(
                                                array(
                                                    'role' => 'student',
                                                )
                                            );
                                        } else {
                                            $student_list = mjschool_get_student_by_class_id_and_section($result->class_id, $result->section_id);
                                        }
                                        if ( ! empty( $student_list ) ) {
                                            $student_data = explode( ',', $result->student_id);
                                            foreach ($student_list as $student_id) {
                                                $selected = '';
                                                if (in_array($student_id->ID, $student_data ) ) {
                                                    $selected = 'selected';
                                                }
                                        		?>
                                                <option value="<?php echo esc_attr($student_id->ID); ?>" <?php echo esc_attr($selected); ?>>
                                                    <?php echo esc_html( mjschool_student_display_name_with_roll($student_id->ID ) ); ?>
                                                </option>
                                        		<?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </span>
                                <span class="mjschool-multiselect-label">
                                    <label class="ml-1 mjschool-custom-top-label top" for="staff_name">
                                        <?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span>
                                    </label>
                                </span>
                            </div>
                        </div>
                    	<?php
                    }
                    ?>
                    <?php wp_nonce_field( 'save_payment_fees_admin_nonce' ); ?>
                    <div class="col-md-6 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px">
                        <div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
                            <select name="fees_id[]" multiple="multiple" id="fees_data" class="form-control validate[required] mjschool-max-width-100px">
                                <?php
                                if ($edit) {
                                    $fees_data = mjschool_get_fees_by_class_id($result->class_id);
                                    if ( ! empty( $fees_data ) ) {
                                        $fees_id = explode( ',', $result->fees_id);
                                        foreach ($fees_data as $id) {
                                            if (mjschool_get_fees_term_name($id->fees_id) !== ' ' ) {
                                                $selected = '';
                                                if (in_array($id->fees_id, $fees_id ) ) {
                                                    $selected = 'selected';
                                                }
                                				?>
                                                <option value="<?php echo esc_attr($id->fees_id); ?>" <?php echo esc_attr($selected); ?>>
                                                    <?php echo esc_html( mjschool_get_fees_term_name($id->fees_id ) ); ?>
                                                </option>
                                				<?php
                                            }
                                        }
                                    }
                                }
                                ?>
                            </select>
                            <span class="mjschool-multiselect-label">
                                <label class="ml-1 mjschool-custom-top-label top" for="staff_name">
                                    <?php esc_html_e( 'Select Fees Type', 'mjschool' ); ?><span class="required">*</span>
                                </label>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input">
                            <div class="col-md-12 form-control">
                                <input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="text" value="<?php if ($edit) { echo esc_attr($result->fees_amount); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr(sanitize_text_field( wp_unslash($_POST['fees_amount']))); } else { echo '0'; } ?>" name="fees_amount" readonly>
                                <label for="userinput1">
                                    <?php esc_html_e( 'Amount', 'mjschool' ); ?>(
                                    <?php echo esc_html( mjschool_get_currency_symbol( ) ); ?>)<span class="required">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-multiselect-validation-member mjschool-multiple-select mjschool-rtl-margin-bottom-0px">
                        <select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
                            <?php
                            if ($edit) {
                                if ($result->tax !== null) {
                                    $tax_id = explode( ',', $result->tax);
                                } else {
                                    $tax_id[] = '';
                                }
                            } else {
                                $tax_id[] = '';
                            }
                            $obj_tax   = new Mjschool_Tax_Manage();
                            $smgt_taxs = $obj_tax->mjschool_get_all_tax();
                            if ( ! empty( $smgt_taxs ) ) {
                                foreach ($smgt_taxs as $data) {
                                    $selected = '';
                                    if (in_array($data->tax_id, $tax_id ) ) {
                                        $selected = 'selected';
                                    }
                            		?>
                                    <option value="<?php echo esc_attr($data->tax_id); ?>" <?php echo esc_attr( $selected); ?>>
                                        <?php echo esc_html( $data->tax_title); ?> -
                                        <?php echo esc_html( $data->tax_value); ?>
                                    </option>
                            		<?php
                                }
                            }
                            ?>
                        </select>
                        <span class="mjschool-multiselect-label">
                            <label class="ml-1 mjschool-custom-top-label top" for="staff_name">
                                <?php esc_html_e( 'Select Tax', 'mjschool' ); ?>
                            </label>
                        </span>
                    </div>
                    <div class="col-md-6 mjschool-note-text-notice">
                        <div class="form-group input">
                            <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                                <div class="form-field">
                                    <textarea name="description" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"><?php if ($edit) { echo esc_textarea($result->description); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea(sanitize_text_field( wp_unslash($_POST['description']))); } ?></textarea>
                                    <span class="mjschool-txt-title-label"></span>
                                    <label class="text-area address active">
                                        <?php esc_html_e( 'Description', 'mjschool' ); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 input mjschool-para-margin" >
                        <div class="form-group input mjschool-rtl-margin-0px">
                            <div class="col-md-12 form-control">
                                <input id="start_date_event" class="form-control date_picker validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_year" value="<?php if ($edit) { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d', strtotime($result->start_year ) ) ) ); } elseif ( isset( $_POST['start_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box(sanitize_text_field( wp_unslash($_POST['start_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d' ) ) ); } ?>">
                                <label class="active date_label" for="start_date_event">
                                    <?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 input mjschool-para-margin">
                        <div class="form-group input mjschool-rtl-margin-0px">
                            <div class="col-md-12 form-control">
                                <input id="end_date_event" class="form-control date_picker validate[required] start_date datepicker2" type="text" name="end_year" autocomplete="off" value="<?php if ($edit) { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d', strtotime($result->end_year ) ) ) ); } elseif ( isset( $_POST['end_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box(sanitize_text_field( wp_unslash($_POST['end_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d' ) ) ); } ?>">
                                <label class="date_label" for="end">
                                    <?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox mjschool-margin-15px-rtl mjschool-para-margin" >
                        <div class="form-group">
                            <div class="col-md-12 form-control">
                                <div class="row mjschool-padding-radio">
                                    <div class="input-group">
                                        <label class="mjschool-custom-top-label" for="classis_limit">
                                            <?php esc_html_e( 'Status', 'mjschool' ); ?>
                                        </label>
                                        <div class="d-inline-block mjschool-gender-line-height-24px">
                                            <?php
                                            $status = 'no';
                                            if ($edit) {
                                                $recurrence_type = $result->status;
                                            } elseif ( isset( $_POST['status'] ) ) {
                                                $recurrence_type = sanitize_text_field($_POST['status']);
                                            }
                                            ?>
                                            <label class="radio-inline">
                                                <input type="radio" value="yes" class="recurrence_type validate[required]" name="status" <?php checked( 'yes', esc_html( $recurrence_type ) ); ?> />
                                                <?php esc_html_e( 'Yes', 'mjschool' ); ?>
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" value="no" class="recurrence_type validate[required]" name="status" <?php checked( 'no', esc_html( $recurrence_type ) ); ?> />
                                                <?php esc_html_e( 'No', 'mjschool' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-body mjschool-user-form mjschool-padding-top-15px-res">
                <div class="row">
                    <div class="col-sm-6">
                        <input type="submit" value="<?php if ($edit) { esc_attr_e( 'Save Recurring Invoice', 'mjschool' ); } else { esc_attr_e( 'Create Invoice', 'mjschool' ); } ?>" name="save_recurring_feetype_payment" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to edit this record? This data change in next recurring invoice details.', 'mjschool' ); ?>' );" class="btn btn-success mjschool-save-btn" />
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!----- Panel Body. --------->
	<?php
}
?>