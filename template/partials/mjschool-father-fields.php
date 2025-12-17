<?php
/**
 * Father Information Fields Partial Template
 *
 * @package MJSchool
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get phone code if not already available.
if ( ! isset( $phone_code ) && function_exists( 'mjschool_get_country_phonecode' ) ) {
    $phone_code = mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
}
?>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 father_div">
    
    <!-- Section Header -->
    <div class="header" id="fatid" style="margin-left:10px;">
        <h3 class="mjschool-first-header"><?php esc_html_e( 'Father Information', 'mjschool' ); ?></h3>
    </div>

    <!-- Salutation -->
    <div id="fatid1" class="col-md-12 mb-3">
        <div class="form-group input">
            <label class="mjschool-custom-top-label top" for="fathersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
            <select class="mjschool-line-height-29px-registration-from form-control validate[required]" name="fathersalutation" id="fathersalutation">
                <option value="Mr"><?php esc_html_e( 'Mr', 'mjschool' ); ?></option>
                <option value="Dr"><?php esc_html_e( 'Dr', 'mjschool' ); ?></option>
                <option value="Prof"><?php esc_html_e( 'Prof', 'mjschool' ); ?></option>
            </select>
        </div>
    </div>

    <!-- First Name -->
    <div id="fatid2" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_first_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_first_name"autocomplete="off">
                <label for="father_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Middle Name -->
    <div id="fatid3" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_middle_name"autocomplete="off">
                <label for="father_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Last Name -->
    <div id="fatid4" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_last_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_last_name"autocomplete="off">
                <label for="father_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Gender -->
    <div id="fatid13" class="col-md-12 mb-3">
        <div class="form-group mjschool-radio-button-bottom-margin-rs mjschool-margin-top-15px_child_theme">
            <div class="col-md-12 form-control">
                <div class="row mjschool-padding-radio mjschool-line-height-29px-registration-from">
                    <div class="input-group">
                        <label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="left: 0px; top: -11px !important;"><?php esc_html_e( 'Gender', 'mjschool' ); ?></label>
                        <div class="d-inline-block">
                            <input type="radio" value="male" class="tog" name="fathe_gender" id="father_gender_male"checked>
                            <label class="mjschool-custom-control-label mjschool-margin-right-20px" for="father_gender_male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>&nbsp;&nbsp;
                            <input type="radio" value="female" class="tog" name="fathe_gender"id="father_gender_female">
                            <label class="mjschool-custom-control-label" for="father_gender_female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>&nbsp;&nbsp;
                            <input type="radio" value="other" class="tog" name="fathe_gender"id="father_gender_other">
                            <label class="mjschool-custom-control-label" for="father_gender_other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date of Birth -->
    <div id="fatid14" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_birth_date" class="mjschool-line-height-29px-registration-from form-control birth_date" type="text" name="father_birth_date" readonly autocomplete="off">
                <label for="father_birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div id="fatid15" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_address" class="mjschool-line-height-29px-registration-from form-control validate[custom[address_description_validation]]" maxlength="150" type="text" name="father_address" autocomplete="off">
                <label for="father_address"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- State -->
    <div id="fatid16" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_state_name"autocomplete="off">
                <label for="father_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- City -->
    <div id="fatid17" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_city_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_city_name"autocomplete="off">
                <label for="father_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Zip Code -->
    <div id="fatid18" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_zip_code" class="mjschool-line-height-29px-registration-from form-control validate[custom[zipcode]]" maxlength="15" type="text" name="father_zip_code"autocomplete="off">
                <label for="father_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Email -->
    <div id="fatid5" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_email" data-email-type="father_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control validate[custom[email]] text-input father_email" maxlength="100" type="email" name="father_email"autocomplete="off">
                <label for="father_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Mobile Number -->
    <div id="fatid6" class="col-md-12 mb-3">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group input mjschool-margin-bottom-0">
                    <div class="col-md-12 form-control mjschool-mobile-input">
                        <span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( $phone_code ); ?></span>
                        <input id="father_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]] mjschool-line-height-29px-registration-from" type="text" name="father_mobile"autocomplete="off">
                        <label for="father_mobile" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- School Name -->
    <div id="fatid7" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_school" class="form-control validate[custom[onlyLetter_specialcharacter]] text-input mjschool-line-height-29px-registration-from" maxlength="50" type="text" name="father_school"autocomplete="off">
                <label for="father_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Medium of Instruction -->
    <div id="fatid8" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_medium" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_medium"autocomplete="off">
                <label for="father_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Educational Qualification -->
    <div id="fatid9" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_education" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_education"autocomplete="off">
                <label for="father_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Annual Income -->
    <div id="fatid10" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="fathe_income" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="fathe_income"autocomplete="off">
                <label for="fathe_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Occupation -->
    <div id="fatid11" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_occuption" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="father_occuption"autocomplete="off">
                <label for="father_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Document Name -->
    <div id="fatid_doc_name" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="father_document_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="100" type="text" name="father_document_name"autocomplete="off">
                <label for="father_document_name"><?php esc_html_e( 'Document Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Proof of Qualification (File Upload) -->
    <div class="col-md-12 mb-3" id="mjschool-fatid12">
        <div class="form-group input mjschool-margin-top-15px_child_theme">
            <div class="col-md-12 form-control">
                <label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" style="left: 20px; top: 9px !important;"for="father_doc"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label>
                <div class="col-sm-12">
                    <input type="file" name="father_doc" id="father_doc"class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file mjschool-father_doc">
                </div>
            </div>
        </div>
    </div>

</div>