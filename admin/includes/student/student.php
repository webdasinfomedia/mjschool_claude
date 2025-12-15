<?php
/**
 * Add/Edit Student Form Template.
 *
 * This file handles the "Add Student" and "Edit Student" functionalities within the
 * MJSchool plugin’s admin panel. It provides a comprehensive form to input, edit,
 * and validate student personal details, contact information, and sibling records.
 *
 * Key Features:
 * - Supports both adding and editing student records with pre-filled data for edits.
 * - Includes dynamic AJAX-driven dropdowns for class, section, and sibling selection.
 * - Allows multiple sibling entries with automatic student list population per class.
 * - Validates uploaded documents based on allowed file types and size limits.
 * - Implements file upload, gender selection, and birth date pickers with validation.
 * - Integrates with WordPress AJAX, nonces, and security checks.
 * - Provides responsive and accessible form design for admin users.
 * - Supports WordPress internationalization for all static labels.
 * - Sanitizes and escapes all data inputs and outputs to maintain system security.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( "mjschool_custom_class");
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$mjschool_role                      = 'student';
if ( $active_tab === 'addstudent' ) {
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) { // -------- Edit Student -----//
		$edit         = 1;
		$user_info    = get_userdata( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) ) );
		$sibling_data = $user_info->sibling_information;
		$sibling      = json_decode( $sibling_data );
	}
	$document_option    = get_option( 'mjschool_upload_document_type' );
	$document_type      = explode( ', ', $document_option );
	$document_type_json = $document_type;
	$document_size      = get_option( 'mjschool_upload_document_size' );
	?>
	<div class="mjschool-panel-body"><!------ Panel body. -------->
		<!--------- Student form. ---------->
		<form name="mjschool-student-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-student-form" enctype='multipart/form-data'>
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="role" value="<?php echo esc_attr( $mjschool_role ); ?>" />
			<input type="hidden"  name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Personal Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-admin-dashboard"> <!-- Form body div. -->
				<div class="row"><!-- Row div. -->
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="admission_no" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->admission_no ); } elseif ( isset( $_POST['admission_no'] ) ) { echo esc_attr( mjschool_generate_admission_number() ); } else { echo esc_attr( mjschool_generate_admission_number() ); } ?>"  name="admission_no">
								<label for="admission_no"><?php esc_html_e( 'Student ID', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
						<label class="mjschool-custom-top-label mjschool-lable-top top" for="class_list_add_student"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						if ( $edit ) {
							$classval = $user_info->class_name;
						} elseif ( isset( $_POST['class_name'] ) ) {
							$classval = sanitize_text_field(wp_unslash($_POST['class_name']));
						} else {
							$classval = '';
						}
						?>
						<select name="class_name" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="class_list_add_student">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php
					if ( $school_type != "university") {
						?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-form-select">
							<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-class-section-add-student"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$sectionval = $user_info->class_section;
							} elseif ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="form-control mjschool-max-width-100px" id="mjschool-class-section-add-student">
								<option value=""><?php esc_html_e( 'Select Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $user_info->class_name ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
						<?php 
					} ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="roll_id" class="form-control validate[required,custom[roll_id_format]]" maxlength="10" type="text"  <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->roll_id ); } elseif ( isset( $_POST['roll_id'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['roll_id'])) ); } ?>" name="roll_id">
								<label  for="roll_id"><?php esc_html_e( 'Roll Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input"  autocomplete="first_name" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) );} ?>" name="first_name">
								<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="middle_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text"  <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) ); } ?>" name="middle_name">
								<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text"  <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) ); } ?>" name="last_name">
								<label  for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="input-group">
										<span class="mjschool-custom-top-label" for="gender"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
										<div class="d-inline-block mjschool-gender-line-height-24px">
											<?php
											$genderval = 'male';
											if ( $edit ) {
												$genderval = $user_info->gender;
											} elseif ( isset( $_POST['gender'] ) ) {
												$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
											}
											?>
											<label class="radio-inline custom_radio"><input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
											<label class="radio-inline custom_radio"><input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
											<label class="radio-inline custom_radio"><input type="radio" value="other" class="tog validate[required]" name="gender" <?php checked( 'other', $genderval ); ?> /><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date"  value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
								<label class="col-form-label date_label text-md-end col-sm-2 control-label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form"> <!-- Card body div. -->
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" autocomplete="address" type="text" name="address" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['address'])) ); } ?>">
								<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); } ?>">
								<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); } ?>">
								<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'save_teacher_admin_nonce' ); ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="zip_code" class="form-control validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); } ?>">
								<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phonecode1" name="phonecode" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+1<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[required],minSize[6],maxSize[15]]" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required red">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phonecode" name="alter_mobile_number" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="alternet_mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['alternet_mobile_number'])) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="alternet_mobile_number"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-input-height-50px">
								<div class="row mjschool-padding-radio">
									<div class="input-group mjschool-input-checkbox">
										<span class="mjschool-custom-top-label"><?php esc_html_e( 'Siblings', 'mjschool' ); ?></span>
										<div class="checkbox mjschool-checkbox-label-padding-8px">
											<label>
												<input type="checkbox" id="chkIsTeamLead" <?php if ( $edit ) { $sibling_data = $user_info->sibling_information; $sibling = json_decode( $sibling_data ); if ( ! empty( $user_info->sibling_information ) ) { foreach ( $sibling as $value ) { if ( ! empty( $value->siblingsclass ) && ! empty( $value->siblingsstudent ) ) { ?> checked  <?php } } } } ?> />&nbsp;&nbsp;<?php esc_html_e( 'In case of any sibling ? click here', 'mjschool' ); ?>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br>
			<?php
			if ( $edit ) {
				$sibling_data = $user_info->sibling_information;
				$sibling = json_decode( $sibling_data );
				if ( ! empty( $sibling ) ) {
					$count_array = count( $sibling );
				} else {
					$count_array = 0;
				}
				$i = 1;
				?>
				<div id="mjschool-sibling-div" class="mjschool-sibling-div-none mjschool-sibling-div_clss">
					<?php
					if ( ! empty( $sibling ) ) {
						foreach ( $sibling as $value ) {
							?>
							<div class="mjschool-sibling-trigger" data-id="<?php echo esc_attr($i); ?>"></div>
							<!-- <script type="text/javascript">
								jQuery(document).ready(function(jQuery) {
									"use strict";
									// When sibling class dropdown change.
									jQuery(document).on( "change", "#sibling_class_change_<?php echo esc_js($i); ?>", function() {
										var selection = jQuery(this).val();
										// Clear sibling student list.
										jQuery( '#sibling_student_list_<?php echo esc_js($i); ?>' ).empty();
										// Prepare AJAX data to load users by class.
										var curr_data = {
											action: 'mjschool_load_user',
											class_list: selection,
											nonce: mjschool.nonce,
											dataType: 'json'
										};
										// Load users in the selected class.
										jQuery.post(mjschool.ajax, curr_data, function(response) {
											jQuery( '#sibling_student_list_<?php echo esc_js($i); ?>' ).append(response);
										});
										// Clear sibling class section and show loading message.
										var sectionSelect = jQuery( '#sibling_class_section_<?php echo esc_js($i); ?>' );
										sectionSelect.empty().append( '<option value="remove">Loading..</option>' );
										// Prepare AJAX data to load class sections.
										var sectionData = {
											action: 'mjschool_load_class_section',
											class_id: selection,
											nonce: mjschool.nonce,
											dataType: 'json'
										};
										// Load sections of the selected class.
										jQuery.post(mjschool.ajax, sectionData, function(response) {
											sectionSelect.find( "option[value='remove']").remove();
											sectionSelect.append(response);
										});
									});
									// When sibling class section dropdown changes.
									jQuery(document).on( "change", "#sibling_class_section_<?php echo esc_js($i); ?>", function() {
										var selection = jQuery(this).val();
										var class_id = jQuery( "#sibling_class_change_<?php echo esc_js($i); ?>").val();
										// Clear sibling student list.
										jQuery( '#sibling_student_list_<?php echo esc_js($i); ?>' ).empty();
										// Prepare AJAX data to load users by section and class.
										var curr_data = {
											action: 'mjschool_load_section_user',
											section_id: selection,
											class_id: class_id,
											nonce: mjschool.nonce,
											dataType: 'json'
										};
										// Load users in the selected section and class.
										jQuery.post(mjschool.ajax, curr_data, function(response) {
											jQuery( '#sibling_student_list_<?php echo esc_js($i); ?>' ).append(response);
										});
									});
								});
							</script> -->
							<input type="hidden" id="admission_sibling_id" name="admission_sibling_id" value="<?php echo esc_attr( $count_array ); ?>"  />
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
										<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_change_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="sibling_class_change_<?php echo esc_attr( $i ); ?>">
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $value->siblingsclass, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
												<?php
											}
											?>
										</select>
									</div>
									<?php if ( $school_type === "school") { ?>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section_<?php echo esc_attr( $i ); ?>">
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
												<?php
												if ( $edit ) {
													foreach ( mjschool_get_class_sections( $value->siblingsclass ) as $sectiondata ) {
														?>
														<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $value->siblingssection, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
														<?php
													}
												}
												?>
											</select>
										</div>
									<?php }?>
									<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
										<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
										<select name="siblingsstudent[]" id="sibling_student_list_<?php echo esc_attr( $i ); ?>" class="form-control mjschool-max-width-100px">
											<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
											<?php
											if ( $edit ) {
												if ( mjschool_student_display_name_with_roll( $value->siblingsstudent ) != 'N/A' ) {
													echo '<option value="' . esc_attr( $value->siblingsstudent ) . '" ' . selected( $value->siblingsstudent, $value->siblingsstudent ) . '>' . esc_html( mjschool_student_display_name_with_roll( $value->siblingsstudent ) ) . '</option>';
												}
											}
											?>
										</select>
									</div>
									<input type="hidden"  class="click_value" name="" value="<?php echo esc_attr( $count_array + 1 ); ?>">
									<?php
									if ( $i === 1 ) {
										?>
										<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
										</div>
										<?php
									}
									else
									{
										?>
										<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
											<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-float-right mjschool-input-btn-height-width">
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
							$i++;
						}
					}
					else
					{
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class','mjschool' );?><span class="mjschool-require-field">*</span></label>
									<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
										<option value=""><?php esc_html_e( 'Select Class','mjschool' );?></option>
										<?php
										foreach(mjschool_get_all_class() as $classdata)
										{
											?>
											<option value="<?php echo esc_attr($classdata['class_id']);?>"><?php echo esc_html( $classdata['class_name']);?></option>
											<?php
										}
										?>
									</select>
								</div>
								<?php if ( $school_type === "school") { ?>
									<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
										<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section','mjschool' );?></label>
										<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section">
											<option value=""><?php esc_html_e( 'All Section','mjschool' );?></option>
										</select>
									</div>
								<?php }?>
								<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
									<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student','mjschool' );?><span class="mjschool-require-field">*</span></label>
									<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required]">
										<option value=""><?php esc_html_e( 'Select Student','mjschool' );?></option>
									</select>
								</div>
								<input type="hidden"  class="click_value" name="" value="1">
								<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			else
			{
				?>
				<div id="mjschool-sibling-div" class="mjschool-sibling-div_clss mjschool-sibling-div_clss">
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
								<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class','mjschool' );?><span class="mjschool-require-field">*</span></label>
								<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
									<option value=""><?php esc_html_e( 'Select Class','mjschool' );?></option>
									<?php
									foreach(mjschool_get_all_class() as $classdata)
									{
										?>
										<option value="<?php echo esc_attr($classdata['class_id']);?>"><?php echo esc_html( $classdata['class_name']);?></option>
										<?php
									}
									?>
								</select>
							</div>
							<?php if ( $school_type === "school") { ?>
								<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section','mjschool' );?></label>
									<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section">
										<option value=""><?php esc_html_e( 'All Section','mjschool' );?></option>
									</select>
								</div>
							<?php }?>
							<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
								<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student','mjschool' );?><span class="mjschool-require-field">*</span></label>
								<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required]">
									<option value=""><?php esc_html_e( 'Select Student','mjschool' );?></option>
								</select>
							</div>
							<input type="hidden"  class="click_value" name="" value="1">
							<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form"> <!-- Card body div. -->
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="email" class="form-control validate[required,custom[email]] text-input mjschool-student-email-id" maxlength="100" type="text" name="email" <?php if ( $edit ) { ?> value="<?php echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['email'])) ); } ?>" autocomplete="email">
								<label  for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="password" class="form-control  <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; } ?>" type="password" name="password" autocomplete="current-password">
								<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?>
									<?php if ( ! $edit ) { ?>
										<span class="mjschool-require-field">*</span>
									<?php } ?>
								</label>
								<!-- Use class + Data-target. -->
								<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
								<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl"><?php esc_html_e( 'Image', 'mjschool' ); ?></span>
								<div class="col-sm-12 mjschool-display-flex">
									<input type="text" id="smgt_user_avatar_url" class="mjschool-image-path-dots form-control" name="smgt_user_avatar" value="<?php if ( $edit ) { echo esc_url( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['mjschool_user_avatar'] ) ) { echo esc_url( sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar'])) ); }?>" readonly />
									<input id="upload_user_avatar_button" type="button" class="button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
								<div id="mjschool-upload-user-avatar-preview">
									<?php if ($edit) {
										if ($user_info->smgt_user_avatar === "") { ?>
											<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ) ?>">
										<?php } else {
											?>
											<img class="mjschool-image-preview-css" src="<?php if ($edit) echo esc_url($user_info->smgt_user_avatar); ?>" />
											<?php
										}
									} else {
										?>
										<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ) ?>">
										<?php
									} ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Document upload field start. -->
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Document Details', 'mjschool' ); ?></h3>
			</div>
			<div class="mjschool-more-document">
				<?php
				if ( $edit ) {
					// Check user document exists or not.
					if ( ! empty( $user_info->user_document ) ) {
						$document_array = json_decode( $user_info->user_document );
						foreach ( $document_array as $key => $value ) {
							?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="document_title_<?php echo esc_attr( $key ); ?>" class="form-control text-input" maxlength="50" type="text" value="<?php echo esc_attr( $value->document_title ); ?>" name="document_title[]">
												<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-5 col-10 col-sm-1">
										<div class="form-group input">
											<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
												<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
												<div class="col-sm-12 row">
													<input type="hidden" id="user_hidden_docs_<?php echo esc_attr( $key ); ?>" class="mjschool-image-path-dots form-control" name="user_hidden_docs[]" value="<?php echo esc_attr( $value->document_file ); ?>" readonly />
													<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 mt-1">
														<input name="document_file[]" type="file" class="form-control mjschool-file-validation file" />
													</div>
													<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-0">
														<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $value->document_file ); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
									if ( $key === 0 ) {
										?>
										<div class="col-md-1 col-2 col-sm-1 col-xs-12">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
										</div>
										<?php
									}
									else
									{
										?>
										<div class="col-md-1 col-2 col-sm-3 col-xs-12 mjschool-width-20px-res">
											<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
						}
					}
					else
					{
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
											<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-5 col-10 col-sm-1">
									<div class="form-group input">
										<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
											<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
											<div class="col-sm-12 mjschool-display-flex">
												<input name="document_file[]" type="file" class="form-control mjschool-file-validation file" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
											</div>
										</div>
									</div>
								</div>
								<div class="col-md-1 col-2 col-sm-1 col-xs-12">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
								</div>
							</div>
						</div>
						<?php
					}
				}
				else
				{
					?>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="document_title" class="form-control  text-input" maxlength="50" type="text" value="" name="document_title[]">
										<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-5 col-10 col-sm-1">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
										<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
										<div class="col-sm-12 mjschool-display-flex">
											<input name="document_file[]" type="file" class="form-control file mjschool-file-validation" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>"  />
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-1 col-2 col-sm-1 col-xs-12">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png")?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<div class="form-group row">
				<div class="col-sm-10">
					<?php echo esc_html( get_post_meta( get_the_ID() ) ); ?>
				</div>
			</div>
			<!-- Custom fields data. -->
			<?php
			// --------- Get module-wise custom field data. --------------//
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'student';
			$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
			?>
			<!------- Save student button. ---------->
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Student', 'mjschool' ); } else { esc_html_e( 'Add Student', 'mjschool' );} ?>" name="save_student" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form><!--------- End student form. ---------->
	</div><!------ Panel body. -------->
	<?php
}
?>