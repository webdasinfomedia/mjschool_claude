<?php
/**
 * Mother Fields Partial Template
 *
 * @package MJSchool
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get phone code.
$phone_code = '';
if ( function_exists( 'mjschool_get_country_phonecode' ) ) {
    $phone_code = mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) );
}
?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mother_div">
    <!-- Section Header -->
    <div class="header" id="motid" style="margin-left:10px;">
        <h3 class="mjschool-first-header">
            <?php esc_html_e( 'Mother Information', 'mjschool' ); ?>
        </h3>
    </div>

    <!-- Salutation -->
    <div id="motid1" class="col-md-12 mb-3">
        <div class="form-group input">
            <label class="mjschool-custom-top-label top" for="mothersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
            <select class="form-control validate[required]" name="mothersalutation" id="mothersalutation">
                <option value="Ms"><?php esc_html_e( 'Ms', 'mjschool' ); ?></option>
                <option value="Mrs"><?php esc_html_e( 'Mrs', 'mjschool' ); ?></option>
                <option value="Miss"><?php esc_html_e( 'Miss', 'mjschool' ); ?></option>
                <option value="Dr"><?php esc_html_e( 'Dr', 'mjschool' ); ?></option>
                <option value="Prof"><?php esc_html_e( 'Prof', 'mjschool' ); ?></option>
            </select>
        </div>
    </div>

    <!-- First Name -->
    <div id="motid2" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_first_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_first_name">
                <label for="mother_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Middle Name -->
    <div id="motid3" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_middle_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_middle_name">
                <label for="mother_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Last Name -->
    <div id="motid4" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_last_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_last_name">
                <label for="mother_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Gender -->
    <div id="motid13" class="col-md-12 mb-3">
        <div class="form-group mjschool-radio-button-bottom-margin-rs mjschool-margin-top-15px_child_theme">
            <div class="col-md-12 form-control">
                <div class="row mjschool-padding-radio mjschool-line-height-29px-registration-from">
                    <div class="input-group">
                        <label class="mjschool-custom-top-label mjschool-margin-left-0 mjschool-gender-label-rtl" style="left: 0px; top: -11px !important;"><?php esc_html_e( 'Gender', 'mjschool' ); ?></label>
                        <div class="d-inline-block">
                            <input type="radio" value="male" class="tog" name="mother_gender" id="mother_gender_male">
                            <label class="mjschool-custom-control-label mjschool-margin-right-20px" for="mother_gender_male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>&nbsp;&nbsp;
                            <input type="radio" value="female" class="tog" name="mother_gender" id="mother_gender_female" checked>
                            <label class="mjschool-custom-control-label" for="mother_gender_female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>&nbsp;&nbsp;
                            <input type="radio" value="other" class="tog" name="mother_gender" id="mother_gender_other">
                            <label class="mjschool-custom-control-label" for="mother_gender_other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date of Birth -->
    <div id="motid14" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_birth_date" class="mjschool-line-height-29px-registration-from form-control birth_date" type="text" name="mother_birth_date" readonly>
                <label for="mother_birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Address -->
    <div id="motid15" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_address" class="mjschool-line-height-29px-registration-from form-control validate[custom[address_description_validation]]" maxlength="150" type="text" name="mother_address">
                <label for="mother_address"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- State -->
    <div id="motid16" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_state_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_state_name">
                <label for="mother_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- City -->
    <div id="motid17" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_city_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_city_name">
                <label for="mother_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Zip Code -->
    <div id="motid18" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_zip_code" class="mjschool-line-height-29px-registration-from form-control validate[custom[zipcode]]" maxlength="15" type="text" name="mother_zip_code">
                <label for="mother_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Email -->
    <div id="motid5" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_email" data-email-type="mother_email" class="addmission_email_id mjschool-line-height-29px-registration-from form-control validate[custom[email]] text-input mother_email" maxlength="100" type="email" name="mother_email">
                <label for="mother_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Mobile Number -->
    <div id="motid6" class="col-md-12 mb-3">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group input mjschool-margin-bottom-0">
                    <div class="col-md-12 form-control mjschool-mobile-input">
                        <span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( $phone_code ); ?></span>
                        <input type="hidden" value="+<?php echo esc_attr( $phone_code ); ?>" class="mjschool-line-height-29px-registration-from form-control" name="mother_phone_code">
                        <input id="mother_mobile" class="mjschool-line-height-29px-registration-from form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mother_mobile">
                        <label for="mother_mobile" class="mobile_number_rtl mjschool-custom-control-label mjschool-custom-top-label"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- School Name -->
    <div id="motid7" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_school" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_school">
                <label for="mother_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Medium of Instruction -->
    <div id="motid8" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_medium" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_medium">
                <label for="mother_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Educational Qualification -->
    <div id="motid9" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_education" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_education">
                <label for="mother_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Annual Income -->
    <div id="motid10" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_income" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="mother_income">
                <label for="mother_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Occupation -->
    <div id="motid11" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_occuption" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="mother_occuption">
                <label for="mother_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Document Name -->
    <div id="motid_doc_name" class="col-md-12 mb-3">
        <div class="form-group input">
            <div class="col-md-12 form-control">
                <input id="mother_document_name" class="mjschool-line-height-29px-registration-from form-control validate[custom[onlyLetter_specialcharacter]] text-input" maxlength="100" type="text" name="mother_document_name">
                <label for="mother_document_name"><?php esc_html_e( 'Document Name', 'mjschool' ); ?></label>
            </div>
        </div>
    </div>

    <!-- Proof of Qualification (Document Upload) -->
    <div id="mjschool-motid12" class="col-md-12 mb-3">
        <div class="form-group input mjschool-margin-top-15px_child_theme">
            <div class="col-md-12 form-control">
                <label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" style="left: 20px; top: 9px !important;" for="mother_doc"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label>
                <div class="col-sm-12">
                    <input type="file" name="mother_doc" id="mother_doc" class="col-md-2 col-sm-2 col-xs-12 form-control mjschool-file-validation input-file mjschool-mother_doc"accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.gif,.png,.jpg,.jpeg">
                </div>
            </div>
        </div>
    </div>
</div>