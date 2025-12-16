<?php
/**
 * Add/Edit Payment Fee Form.
 *
 * Handles the display and submission of the Payment Fee form in the MJSchool plugin.
 * Provides functionality to create a new payment fee or edit an existing one, including:
 * - Selecting class, section, and students.
 * - Choosing fee types and applying taxes.
 * - Entering amount, discount, start and end dates.
 * - Setting recurrence options for recurring fees.
 * - Sending notifications via email/SMS to students and parents.
 * - Integration with custom fields for the fee_list module.
 *
 * The form validates required fields and uses WordPress nonces for secure submission.
 *
 *
 * @since      1.0.0
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
if ($active_tab === 'addpaymentfee' ) {
    $fees_pay_id = 0;
    if ( isset( $_REQUEST['fees_pay_id'] ) ) {
        $fees_pay_id = intval(mjschool_decrypt_id(wp_unslash($_REQUEST['fees_pay_id']) ) );
    }
    $edit = 0;
    if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
        $edit   = 1;
        $result = $mjschool_obj_feespayment->mjschool_get_single_fee_mjschool_payment($fees_pay_id);
    }
	?>
    <div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
        <!----- Panel Body. --------->
        <form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
            <?php $mjschool_action = isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
            <input type="hidden" name="action" value="<?php echo esc_attr($mjschool_action); ?>">
            <input type="hidden" name="fees_pay_id" value="<?php echo esc_attr($fees_pay_id); ?>">
            <input type="hidden" name="invoice_type" value="expense">
            <input type="hidden" name="recurrence_type" value="one_time">
            <div class="form-body mjschool-user-form">
                <div class="row">
                    <?php
                    if (!$edit) {
                        $recurring_option = get_option( 'mjschool_enable_recurring_invoices' );
                        if ($recurring_option === 'yes' ) {
                    		?>
                            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-recurring-option-checkbox">
                                <div class="form-group">
                                    <div class="col-md-12 form-control">
                                        <div class="row mjschool-padding-radio">
                                            <div class="input-group">
                                                <label class="mjschool-custom-top-label" for="classis_limit"><?php esc_html_e( 'Recurrence Type', 'mjschool' ); ?></label>
                                                <div class="d-inline-block mjschool-gender-line-height-24px">
                                                    <?php
                                                    $recurrence_type = 'one_time';
                                                    if ($edit) {
                                                        $recurrence_type = $result->recurrence_type;
                                                    } elseif ( isset( $_POST['recurrence_type'] ) ) {
                                                        $recurrence_type = sanitize_text_field(sanitize_text_field( wp_unslash($_POST['recurrence_type'])));
                                                    }
                                                    ?>
                                                    <label class="radio-inline">
                                                        <input type="radio" value="one_time" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'one_time', $recurrence_type ); ?> /><?php esc_html_e( 'One Time', 'mjschool' ); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" value="weekly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'weekly', $recurrence_type ); ?> /><?php esc_html_e( 'Weekly', 'mjschool' ); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" value="monthly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'monthly', $recurrence_type ); ?> /><?php esc_html_e( 'Monthly', 'mjschool' ); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" value="quarterly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'quarterly', $recurrence_type ); ?> /><?php esc_html_e( 'Quarterly', 'mjschool' ); ?>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" value="half_yearly" class="recurrence_type validate[required]" name="recurrence_type" <?php checked( 'half_yearly', $recurrence_type ); ?> /><?php esc_html_e( 'Half- Yearly', 'mjschool' ); ?>
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
                    }
                    if ($edit) {
                        $feestype = $result->description;
                        if ($feestype !== 'Admission Fees' ) {
                        	?>
                            <div class="col-md-6 input">
                                <label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                                <?php
                                if ($edit) {
                                    $classval = $result->class_id;
                                } else {
                                    $classval = '';
                                }
                                ?>
                                <select name="class_id" id="mjschool-class-list" class="form-control validate[required] load_fees_drop mjschool-max-width-100px">
                                    <?php
                                    if ($addparent) {
                                        $classdata = mjschool_get_class_by_id($student->class_name);
                                    	?>
                                        <option value="<?php echo esc_attr($student->class_name); ?>"><?php echo esc_html( $classdata->class_name); ?></option>
                                   		<?php
                                    }
                                    ?>
                                    <option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
                                    <?php
                                    foreach (mjschool_get_all_class() as $classdata) {
                                    	?>
                                        <option value="<?php echo esc_attr($classdata['class_id']); ?>" <?php selected($classval, $classdata['class_id']); ?>><?php echo esc_html( $classdata['class_name']); ?>
                                        </option>
                                    	<?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if ( $school_type === 'school' ) {?>
                                <div class="col-md-6 input mjschool-class-section-hide">
                                    <label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
                                    <?php
                                    if ($edit) {
                                        $sectionval = $result->section_id;
                                    } elseif ( isset( $_POST['class_section'] ) ) {
                                        $sectionval = sanitize_text_field( wp_unslash($_POST['class_section']));
                                    } else {
                                        $sectionval = '';
                                    }
                                    ?>
                                    <select name="class_section" class="form-control mjschool-max-width-100px" id="class_section">
                                        <option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
                                        <?php
                                        if ($edit) {
                                            foreach (mjschool_get_class_sections($result->class_id) as $sectiondata) {
                                                ?>
                                                <option value="<?php echo esc_attr($sectiondata->id); ?>" <?php selected($sectionval, $sectiondata->id); ?>> <?php echo esc_html( $sectiondata->section_name); ?></option>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php
                            }
                        }
                        ?>
                        <div class="col-md-6 input mjschool-class-section-hide">
                            <label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
                            <?php
                            if ($edit) {
                                $classval = $result->class_id;
                            } else {
                                $classval = '';
                            }
                            ?>
                            <select name="student_id" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
                                <option value=""><?php esc_html_e( 'Select student', 'mjschool' ); ?></option>
                                <?php
                                if ($edit) {
                                    echo '<option value="' . esc_attr($result->student_id) . '" ' . selected($result->student_id, $result->student_id) . '>' . esc_attr( mjschool_student_display_name_with_roll($result->student_id ) ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    	<?php
                    } else {
                    	?>
                        <div id="mjschool-smgt-select-class" class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-single-class-div mjschool-rtl-margin-0px">
                            <label class="ml-1 mjschool-custom-top-label top" for="fees_class_list_id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                            <select name="class_id" id="fees_class_list_id" class="form-control load_fees mjschool-min-width-100px validate[required]">
                                <option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
                                <option value="all_class"><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
                                <?php
                                foreach (mjschool_get_all_class() as $classdata) {
                                	?>
                                    <option value="<?php echo esc_attr($classdata['class_id']); ?>"><?php echo esc_html( $classdata['class_name']); ?></option>
                                	<?php 
								} ?>
                            </select>
                        </div>
                        <?php if ( $school_type === 'school' ) {?>
                            <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input class_section_id mjschool-rtl-margin-0px">
                                <label class="ml-1 mjschool-custom-top-label top" for="fees_class_section_id"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
                                <?php if ( isset( $_POST['class_section'] ) ) { $sectionval = sanitize_text_field( wp_unslash($_POST['class_section'])); } else { $sectionval = ''; } ?>
                                <select name="class_section" class="form-control mjschool-min-width-100px" id="fees_class_section_id">
                                    <option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
                                    <?php
                                    if ($edit) {
                                        foreach (mjschool_get_class_sections($user_info->class_name) as $sectiondata) {
                                            ?>
                                            <option value="<?php echo esc_attr($sectiondata->id); ?>" <?php selected($sectionval, $sectiondata->id); ?>><?php echo esc_html( $sectiondata->section_name); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php } ?>
                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-single-class-div mjschool-support-staff-user-div input">
                            <div id="messahe_test"></div>
                            <div class="col-sm-12 mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
                                <span class="user_display_block">
                                    <select name="selected_users[]" id="selected_users" class="form-control mjschool-min-width-250px validate[required]" multiple="multiple">
                                    </select>
                                </span>
                                <span class="mjschool-multiselect-label">
                                    <label class="ml-1 mjschool-custom-top-label top mjschool_margin_left_5px"  for="selected_users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?><span class="required">*</span></label>
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
                                    global $wpdb;
                                    $table_mjschool_fees = $wpdb->prefix . 'mjschool_fees';
                                    $fees_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_mjschool_fees WHERE class_id = %s", $result->class_id ) ); //phpcs:ignore
                                    $fees_id = explode( ',', $result->fees_id);
                                    if ( ! empty( $fees_data ) ) {
                                        foreach ( $fees_data as $retrive_data ) {
                                            $selected = "";
                                            if (in_array($retrive_data->fees_id, $fees_id ) )
                                                $selected = "selected";
                                            echo '<option value="' . esc_attr( $retrive_data->fees_id ) . '"'.esc_attr( $selected).'>' . esc_html( get_the_title( $retrive_data->fees_title_id ) ) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                            <span class="mjschool-multiselect-label">
                                <label class="ml-1 mjschool-custom-top-label top" for="fees_data"><?php esc_html_e( 'Select Fees Type', 'mjschool' ); ?><span class="required">*</span></label>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group input">
                            <div class="col-md-12 form-control">
                                <input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="text" value="<?php if ($edit) { echo esc_attr($result->fees_amount); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr(sanitize_text_field( wp_unslash($_POST['fees_amount']))); } else { echo '0'; } ?>" name="fees_amount" readonly>
                                <label for="fees_amount"><?php esc_html_e( 'Amount', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol( ) ); ?>)<span class="required">*</span></label>
                            </div>
                        </div>
                    </div>
                    <div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
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
                                        <?php echo esc_html( $data->tax_title); ?> - <?php echo esc_html( $data->tax_value); ?>
									</option>
                            		<?php
                                }
                            }
                            ?>
                        </select>
                        <span class="mjschool-multiselect-label">
                            <label class="ml-1 mjschool-custom-top-label top" for="tax_id"><?php esc_html_e( 'Select Tax', 'mjschool' ); ?></label>
                        </span>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
                        <div class="form-group input">
                            <div class="col-md-12 form-control">
                                <input id="discount" class="form-control text-input" type="number" min="0" onkeypress="if(this.value.length==8) return false;" step="0.01" value="<?php if ($edit) { echo esc_attr($result->discount); } ?>" name="discount" placeholder="">
                                <label  for="discount"><?php esc_html_e( 'Discount', 'mjschool' ); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
                        <select class="form-control mjschool-max-width-100px" name="discount_type" id="discount_type">
                            <option value="%" <?php if ($edit) { if ( isset( $result->discount_type ) ) { selected(esc_attr($result->discount_type), '%' ); } } ?>>%</option>
                            <option value="amount" <?php if ($edit) { if ( isset( $result->discount_type ) ) { selected(esc_html( $result->discount_type), 'amount' ); } } ?>><?php echo esc_html__( 'Amount', 'mjschool' ) . '( ' . esc_html( mjschool_get_currency_symbol(get_option( 'mjschool_currency_code' ) ) ) . ' )'; ?></option>
                        </select>
                    </div>
                    <div class="col-md-3 input mjschool-rtl-margin-0px mjschool-para-margin">
                        <div class="form-group input mjschool-rtl-margin-0px">
                            <div class="col-md-12 form-control">
                                <input id="start_date_event" class="form-control date_picker validate[required] start_date datepicker1" autocomplete="off" type="text" name="start_year" value="<?php if ($edit) { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d', strtotime($result->start_year ) ) ) ); } elseif ( isset( $_POST['start_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_box(sanitize_text_field( wp_unslash($_POST['start_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d' ) ) ); } ?>">
                                <label class="active date_label" for="start_date_event"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 input mjschool-rtl-margin-0px mjschool-para-margin" >
                        <div class="form-group input">
                            <div class="col-md-12 form-control">
                                <input id="end_date_event" class="form-control date_picker validate[required] start_date datepicker2" type="text" name="end_year" autocomplete="off" value="<?php if ($edit) { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d', strtotime($result->end_year ) ) ) ); } elseif ( isset( $_POST['end_year'] ) ) { echo esc_attr( mjschool_get_date_in_input_boxsanitize_text_field( wp_unslash(($_POST['end_year'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box(date( 'Y-m-d' ) ) ); } ?>">
                                <label class="date_label" for="end_date_event"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mjschool-note-text-notice">
                        <div class="form-group input mjschool-rtl-margin-0px">
                            <div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
                                <div class="form-field">
                                    <textarea id="mjschool-description" name="description" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"><?php if ($edit) { echo esc_textarea($result->description); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea(sanitize_text_field( wp_unslash($_POST['description']))); } ?></textarea>
                                    <span class="mjschool-txt-title-label"></span>
                                    <label class="text-area address active" for="mjschool-description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px mjschool-margin-top-15px">
                        <div class="form-group">
                            <div class="col-md-12 form-control mjschool-input-height-50px">
                                <div class="row mjschool-padding-radio">
                                    <div class="input-group mjschool-input-checkbox">
                                        <label for="mjschool_enable_feesalert_mail" class="mjschool-custom-top-label label_right_position"><?php esc_html_e( 'Send Email To Students & Parents', 'mjschool' ); ?></label>
                                        <div class="checkbox mjschool-checkbox-label-padding-8px">
                                            <label>
                                                <input id="mjschool_enable_feesalert_mail" type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mail" value="1" <?php echo checked(get_option( 'mjschool_enable_feesalert_mail' ), 'yes' ); ?> />&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mjschool-rtl-margin-top-15px mjschool-margin-top-15px mb-3">
                        <div class="form-group">
                            <div class="col-md-12 form-control mjschool-input-height-50px">
                                <div class="row mjschool-padding-radio">
                                    <div class="input-group mjschool-input-checkbox">
                                        <label for="mjschool_enable_feesalert_mjschool_student" class="mjschool-custom-top-label label_right_position"><?php esc_html_e( 'Send SMS To Student', 'mjschool' ); ?></label>
                                        <div class="checkbox mjschool-checkbox-label-padding-8px">
                                            <label>
                                                <input id="mjschool_enable_feesalert_mjschool_student" type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mjschool_student" value="1" <?php echo checked(get_option( 'mjschool_enable_feesalert_sms' ), 'yes' ); ?> />&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mjschool-rtl-margin-top-15px mjschool-margin-top-15px">
                        <div class="form-group">
                            <div class="col-md-12 form-control mjschool-input-height-50px">
                                <div class="row mjschool-padding-radio">
                                    <div class="input-group mjschool-input-checkbox">
                                        <label for="mjschool_enable_feesalert_mjschool_parent" class="mjschool-custom-top-label label_right_position"><?php esc_html_e( 'Send SMS To Parents', 'mjschool' ); ?></label>
                                        <div class="checkbox mjschool-checkbox-label-padding-8px">
                                            <label>
                                                <input id="mjschool_enable_feesalert_mjschool_parent" type="checkbox" class="margin_right_checkbox mjschool-margin-right-5px_checkbox mjschool-margin-right-checkbox-css" name="smgt_enable_feesalert_mjschool_parent" value="1" <?php echo checked(get_option( 'mjschool_enable_feesalert_sms' ), 'yes' ); ?> />&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            // --------- Get Module Wise Custom Field Data. --------------//
            $mjschool_custom_field_obj = new Mjschool_Custome_Field();
            $module                    = 'fee_list';
            $custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback($module);
            ?>
            <div class="form-body mjschool-user-form mjschool-padding-top-15px-res">
                <div class="row">
                    <div class="col-sm-6">
                        <input type="submit" value="<?php if ($edit) { esc_attr_e( 'Save Invoice', 'mjschool' ); } else { esc_attr_e( 'Create Invoice', 'mjschool' ); } ?>" name="save_feetype_payment" class="btn btn-success mjschool-rtl-margin-0px mjschool-save-btn" />
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!----- Panel Body. --------->
    <?php
}
?>