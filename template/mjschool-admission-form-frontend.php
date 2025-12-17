<?php
/**
 * Admission Form Template
 *
 * @package MJSchool
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Variables available: $theme_name, $role, $form_action, $phone_code, $admission_no, $current_date, $currency_symbol
?>
<div class="<?php echo esc_attr( $theme_name ); ?>">
    <form id="mjschool-admission-form" class="mjschool-admission-form" action="<?php echo esc_url( $form_action ); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="student_admission">
        <input type="hidden" name="role" value="<?php echo esc_attr( $role ); ?>">
        <?php wp_nonce_field( 'mjschool_nonce', 'security' ); ?>
        <input id="username" type="hidden" name="username">
        <input id="password" type="hidden" name="password">
        <div class="accordion admission_label" id="myAccordion">
            <!-- Student Information Section -->
            <div class="accordion-item mjschool-class-border-div">
                <h2 class="accordion-header mjschool-accordion-header-custom-css" id="headingOne">
                    <button type="button" class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne" style="font-weight:800;">
                        <?php esc_html_e( 'Student Information', 'mjschool' ); ?>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse mjschool-theme-page-addmission-form-padding show" data-bs-parent="#myAccordion">
                    <div class="card-body_1">
                        <div class="form-body mjschool-user-form mjschool-padding-20px-child-theme mjschool-margin-top-15px">
                            <div class="row">
                                <!-- Admission Number -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="admission_no" class="mjschool-line-height-29px-registration-from form-control validate[required] text-input" type="text" value="<?php echo esc_attr( $admission_no ); ?>" name="admission_no" readonly>
                                            <label for="admission_no"><?php esc_html_e( 'Admission Number', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Class Name (Conditional) -->
                                <?php if ( '1' === get_option( 'mjschool_combine' ) ) : ?>
                                    <div class="col-md-12 input mjschool-error-msg-left-margin mjschool-responsive-bottom-15">
                                        <div class="form-group input">
                                            <label class="ml-1 mjschool-custom-top-label top" for="class_name"><?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span></label>
                                            <select name="class_name" class="mjschool-line-height-27px-registration-form form-control validate[required]" id="class_name">
                                                <option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
                                                <?php
                                                if ( function_exists( 'mjschool_get_all_data' ) ) {
                                                    $class_data = mjschool_get_all_data( 'mjschool_class' );
                                                    foreach ( $class_data as $class ) :
                                                        ?>
                                                        <option value="<?php echo esc_attr( $class->class_id ); ?>">
                                                            <?php echo esc_html( $class->class_name ); ?>
                                                        </option>
                                                        <?php
                                                    endforeach;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <!-- Admission Date -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="admission_date" class="mjschool-line-height-29px-registration-from form-control validate[required]" type="text" name="admission_date" value="<?php echo esc_attr( $current_date ); ?>" readonly>
                                            <label for="admission_date"><?php esc_html_e( 'Admission Date', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fees Display -->
                                <?php
                                $fees = 0;
                                $fees_id = '';
                                $fee_label = '';
                                if ( class_exists( 'Mjschool_Fees' ) ) {
                                    $obj_fees = new Mjschool_Fees();
                                    if ( '1' === get_option( 'mjschool_combine' ) && 'yes' === get_option( 'mjschool_registration_fees' ) ) {
                                        $fees_id = get_option( 'mjschool_registration_amount' );
                                        $fee_label = __( 'Registration Fees', 'mjschool' );
                                        $fees = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id ) ?: 0;
                                        ?>
                                        <div class="col-md-12 mjschool-error-msg-left-margin mb-3">
                                            <div class="form-group input">
                                                <div class="col-md-12 form-control">
                                                    <input id="registration_fees" class="form-control" type="text" readonly value="<?php echo esc_attr( $currency_symbol . ' ' . $fees ); ?>">
                                                    <label for="registration_fees"><?php echo esc_html( $fee_label ); ?><span class="required">*</span></label>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="registration_fees" value="<?php echo esc_attr( $fees_id ); ?>">
                                        <?php
                                    } elseif ( 'yes' === get_option( 'mjschool_admission_fees' ) ) {
                                        $fees_id = get_option( 'mjschool_admission_amount' );
                                        $fee_label = __( 'Admission Fees', 'mjschool' );
                                        $fees = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id ) ?: 0;
                                        ?>
                                        <div class="col-md-12 mjschool-error-msg-left-margin mb-3">
                                            <div class="form-group input">
                                                <div class="col-md-12 form-control">
                                                    <input id="admission_fees" class="form-control" type="text" readonly value="<?php echo esc_attr( $currency_symbol . ' ' . $fees ); ?>">
                                                    <label for="admission_fees"><?php echo esc_html( $fee_label ); ?><span class="required">*</span></label>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="admission_fees" value="<?php echo esc_attr( $fees_id ); ?>">
                                        <?php
                                    }
                                }
                                ?>
                                <!-- First Name -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="first_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="first_name">
                                            <label for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Middle Name -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" name="middle_name">
                                            <label for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Last Name -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="last_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="last_name">
                                            <label for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Date of Birth -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="birth_date" class="mjschool-line-height-29px-registration-from form-control validate[required] birth_date" type="text" value="<?php echo esc_attr( $current_date ); ?>" name="birth_date" readonly>
                                            <label for="birth_date"><?php esc_html_e( 'Date Of Birth', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gender -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group">
                                        <div class="col-md-12 form-control">
                                            <div class="row mjschool-padding-radio">
                                                <div class="input-group">
                                                    <label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="top:-7px !important;"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span></label>
                                                    <div class="d-inline-block mjschool-line-height-29px-registration-from">
                                                        <input type="radio" value="male" class="tog validate[required]" name="gender" checked>
                                                        <label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>&nbsp;&nbsp;
                                                        <input type="radio" value="female" class="tog validate[required]" name="gender">
                                                        <label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>&nbsp;&nbsp;
                                                        <input type="radio" value="other" class="tog validate[required]" name="gender">
                                                        <label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="address" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[address_description_validation]]" maxlength="150" type="text" name="address">
                                            <label for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- State -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name">
                                            <label for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- City -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="city_name" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name">
                                            <label for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Zip Code -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="zip_code" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[zipcode]]" maxlength="15" type="text" name="zip_code">
                                            <label for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mobile Number -->
                                <div class="col-md-12 mb-3 mjschool-mobile-error-massage-left-margin">
                                    <div class="form-group input mjschool-margin-bottom-0">
                                        <div class="col-md-12 form-control mjschool-mobile-input">
                                            <span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( $phone_code ); ?></span>
                                            <input type="hidden" value="+<?php echo esc_attr( $phone_code ); ?>" class="mjschool-line-height-29px-registration-from form-control" name="phone_code">
                                            <input id="phone" class="mjschool-line-height-29px-registration-from form-control validate[required,custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number">
                                            <label for="phone" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Email -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="email" data-email-type="student_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control validate[required,custom[email]] text-input email" maxlength="100" type="email" name="email">
                                            <label for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
                                        </div>
                                    </div>
                                </div>
                                <!-- Previous School -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-group input">
                                        <div class="col-md-12 form-control">
                                            <input id="preschool_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="preschool_name">
                                            <label for="preschool_name">
                                                <?php esc_html_e( 'Previous School', 'mjschool' ); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Siblings Information Section -->
            <div class="accordion-item mjschool-class-border-div">
                <h2 class="accordion-header" id="headingTwo">
                    <button type="button" class="accordion-button collapsed" style="font-weight:800;" data-bs-toggle="collapse" data-bs-target="#collapseTwo"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#myAccordion">
                    <div class="card-body_1">
                        <div class="mjschool-panel-body mjschool-padding-20px-child-theme">
                            <div class="form-group">
                                <div class="col-md-12 col-sm-12 col-xs-12" style="display: inline-flex;" id="relationid">
                                    <input type="checkbox" id="chkIsTeamLead" style="margin-top:4px;">&nbsp;&nbsp;
                                    <h4 class="admintion_page_checkbox_span front">
                                        <?php esc_html_e( 'In case of any sibling? Click here', 'mjschool' ); ?>
                                    </h4>
                                </div>
                            </div>
                            <div id="mjschool-sibling-div" class="mjschool-sibling-div-none mjschool-sibling-div_clss">
                                <div class="form-body mjschool-user-form">
                                    <div class="row">
                                        <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select mb-3">
                                            <label class="mjschool-custom-top-label mjschool-lable-top top" for="siblingsclass"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                                            <select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px mjschool_45px" id="mjschool-sibling-class-change">
                                                <option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
                                                <?php
                                                if ( function_exists( 'mjschool_get_all_data' ) ) {
                                                    $class_data = mjschool_get_all_data( 'mjschool_class' );
                                                    foreach ( $class_data as $class ) :
                                                        ?>
                                                        <option value="<?php echo esc_attr( $class->class_id ); ?>">
                                                            <?php echo esc_html( $class->class_name ); ?>
                                                        </option>
                                                        <?php
                                                    endforeach;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select mb-3">
                                            <label class="mjschool-custom-top-label mjschool-lable-top top" for="siblingssection"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
                                            <select name="siblingssection[]" class="form-control mjschool-max-width-100px mjschool_45px" id="sibling_class_section">
                                                <option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide mb-3">
                                            <label class="ml-1 mjschool-custom-top-label top" for="siblingsstudent"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
                                            <select name="siblingsstudent[]" id="sibling_student_list" class="mjschool_45px form-control mjschool-max-width-100px validate[required1]">
                                                <option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
                                            </select>
                                        </div>
                                        <input type="hidden" class="click_value" name="" value="1">
                                        <div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
                                            <?php if ( defined( 'MJSCHOOL_PLUGIN_URL' ) ) : ?>
                                                <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png' ); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling" alt="<?php esc_attr_e( 'Add Sibling', 'mjschool' ); ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Family Information Section -->
            <div class="accordion-item mjschool-class-border-div">
                <h2 class="accordion-header" id="headingThree">
                    <button type="button" class="accordion-button collapsed" style="font-weight:800;" data-bs-toggle="collapse" data-bs-target="#collapseThree"><?php esc_html_e( 'Family Information', 'mjschool' ); ?></button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse mjschool-margin-top-10pxpx" data-bs-parent="#myAccordion">
                    <div class="card-body_1 admission_parent_information_div">
                        <!-- Parental Status -->
                        <div class="form-body mjschool-user-form mjschool-padding-20px-child-theme">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="col-md-12 form-control">
                                            <div class="row mjschool-padding-radio">
                                                <div class="input-group">
                                                    <label class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Parental Status', 'mjschool' ); ?></label>
                                                    <div class="d-inline-block mjschool-family-information">
                                                        <input type="radio" name="pstatus" class="tog" value="Father" id="sinfather">
                                                        <label class="mjschool-custom-control-label mjschool-margin-right-20px" for="sinfather"><?php esc_html_e( 'Father', 'mjschool' ); ?></label>&nbsp;&nbsp;
                                                        <input type="radio" name="pstatus" class="tog" id="sinmother" value="Mother">
                                                        <label class="mjschool-custom-control-label" for="sinmother"><?php esc_html_e( 'Mother', 'mjschool' ); ?></label>&nbsp;&nbsp;
                                                        <input type="radio" name="pstatus" class="tog" id="boths" value="Both" checked>
                                                        <label class="mjschool-custom-control-label" for="boths"><?php esc_html_e( 'Both', 'mjschool' ); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mjschool-panel-body">
                            
                            <!-- Father Information -->
                            <?php include __DIR__ . '/partials/mjschool-father-fields.php'; ?>
                            
                            <!-- Mother Information -->
                            <?php include __DIR__ . '/partials/mjschool-mother-fields.php'; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Fields Section -->
            <?php
            if ( class_exists( 'Mjschool_Custome_Field' ) ) {
                $custom_field_obj = new Mjschool_Custome_Field();
                $custom_fields = $custom_field_obj->mjschool_get_custom_field_by_module( 'admission' );

                if ( ! empty( $custom_fields ) ) :
                    ?>
                    <div class="accordion-item mjschool-class-border-div">
                        <h2 id="headingFour" class="accordion-header">
                            <button type="button" class="accordion-button collapsed mjschool_weight_800" data-bs-toggle="collapse" data-bs-target="#collapseFour"><?php esc_html_e( 'Custom Field Information', 'mjschool' ); ?></button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse admission_custom collapse mjschool-margin-top-10pxpx" data-bs-parent="#myAccordion">
                            <div class="card-body_1 admission_parent_information_div">
                                <?php// Custom fields are rendered by the class method ?>
                            </div>
                        </div>
                    </div>
                    <?php
                endif;
            }
            ?>
        </div>
        <?php wp_nonce_field( 'save_student_frontend_admission_nonce' ); ?>
        <div class="col-sm-6 mjschool-admission-button mjschool_width_100px">
            <input type="submit" value="<?php esc_attr_e( 'New Admission', 'mjschool' ); ?>" name="save_student_front_admission" class="btn btn-success btn_style mjschool-save-btn">
        </div>
    </form>
</div>