<?php
/**
 * Student Admission Form
 *
 * Handles student admission form rendering, validation, and dynamic features such as
 * datepickers, file upload validation, and sibling management.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/access-rights
 * @since      1.0.0
 */
if (!defined( 'ABSPATH' ) ) {
	die();
}
$role_temp = 'student_temp';
$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<?php
if ( $active_tab === 'mjschool-admission-form' ) {
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$student_id   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'] ))) );
		$edit         = 1;
		$student_data = get_userdata( $student_id );
		$key          = 'status';
		$single       = true;
		$user_status  = get_user_meta( $student_id, $key, $single );
		$sibling_data = $student_data->sibling_information;
		$sibling      = json_decode( $sibling_data );
	}
	?>
	<div class="mjschool-panel-body"><!-------- Panel body. -------->
		<form name="mjschool-admission-form" action="" method="post" class="mjschool-form-horizontal mjschool-admission-form" enctype="multipart/form-data" id="mjschool-admission-form"><!------ Form End ----->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
		<input type="hidden" name="role" value="<?php echo esc_attr( $role_temp ); ?>" />
		<input type="hidden" name="user_id" value="<?php if ( $edit ) { echo esc_attr( $student_id ); } ?>" />
		<input type="hidden" name="status" value="<?php if ( $edit ) { echo esc_attr( $user_status ); } ?>" />
		<!--- Hidden User and password. --------->
		<input id="username" type="hidden" name="username">
		<input id="password" type="hidden" name="password">
		<div class="header">
			<h3 class="mjschool-first-header">
				<?php esc_html_e( 'Admission Information', 'mjschool' ); ?>
			</h3>
		</div>
		<div class="form-body mjschool-user-form"> <!------  Form Body. -------->
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="admission_no" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $student_data->admission_no ); } elseif ( isset( $_POST['admission_no'] ) ) { echo esc_attr( mjschool_generate_admission_number() ); } else { echo esc_attr( mjschool_generate_admission_number() ); } ?>" name="admission_no">
								<label for="admission_no"><?php esc_html_e( 'Admission Number', 'mjschool' ); ?><span class="required">*</span> </label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="admission_date" class="form-control date_picker validate[required]" type="text" name="admission_date" readonly value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $student_data->admission_date ) ); } elseif ( isset( $_POST['admission_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['admission_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
								<label for="admission_date" class="date_label"><?php esc_html_e( 'Admission Date', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<?php
					if (get_option('mjschool_admission_fees') === 'yes')
					{
						$fees_id  = get_option( 'mjschool_admission_amount' );
						$obj_fees = new Mjschool_Fees();
						$amount   = $obj_fees->mjschool_get_single_feetype_data_amount( $fees_id );
						if ( ! empty( $amount ) ) 
						{
							?>
							<div class="col-md-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="admission_fees" name="admission_fees" disabled class="form-control" type="text" readonly value="<?php echo esc_attr( mjschool_get_currency_symbol() . ' ' . $amount ); ?>">
										<label for="admission_fees" class="active"><?php esc_html_e( 'Admission Fees', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
							<input id="admission_fees" class="form-control" type="hidden" name="admission_fees_id" value="<?php echo esc_attr( $fees_id ); ?>">
							<input class="form-control" type="hidden" name="admission_fees_amount" value="<?php echo esc_attr( $amount ); ?>">
							<?php
						}
					}
					?>
				</div>
			</div> <!------  Form Body. -------->
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Student Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="first_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="first_name" autocomplete="first_name" value="<?php if( $edit ) { echo esc_attr( $student_data->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) ); } ?>">
								<label for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="middle_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="middle_name" value="<?php if( $edit ) { echo esc_attr( $student_data->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['middle_name'] ) ) ); }?>">
								<label for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="last_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" name="last_name" value="<?php if( $edit ) { echo esc_attr( $student_data->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) ); } ?>">
								<label for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="birth_date" class="form-control date_picker validate[required] birth_date" type="text" name="birth_date" readonly value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $student_data->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field( wp_unslash( $_POST['birth_date'] ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>">
								<label for="birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-margin-bottom-15px-res mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="input-group">
										<sapn class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="required">*</span></sapn>
										<div class="d-inline-block">
											<?php
											$genderval = 'male';
											if ( $edit ) {
												$genderval = $student_data->gender;
											} elseif ( isset( $_POST['gender'] ) ) {
												$genderval = isset( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '';
											}
											?>
											<input type="radio" value="male" name="gender" class="mjschool-custom-control-input" <?php checked( 'male', $genderval ); ?> id="male">
											<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
											&nbsp;&nbsp;<input type="radio" value="female" name="gender" <?php checked( 'female', $genderval ); ?> class="mjschool-custom-control-input" id="female">
											<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
											&nbsp;&nbsp;<input type="radio" value="other" name="gender" <?php checked( 'other', $genderval ); ?> class="mjschool-custom-control-input" id="other">
											<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phonecode1" name="phonecode" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="mobile_number" class="form-control validate[required,custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if( $edit ) { echo esc_attr( $student_data->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="required red">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="alter_mobile_number" name="alter_mobile_number" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="alternet_mobile_number" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $student_data->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['alternet_mobile_number'] ) ) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="alternet_mobile_number"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?><span class="required red">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="email" email_tpye="student_email" class="form-control validate[required,custom[email]] text-input email addmission_email_id" autocomplete="email" maxlength="100" type="text" name="email" value="<?php if( $edit ) { echo esc_attr( $student_data->user_email ); } elseif ( isset( $_POST['user_email'] ) ) { echo esc_attr( sanitize_email( wp_unslash( $_POST['user_email'] ) ) ); } ?>">
								<label for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="preschool_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="preschool_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->preschool_name ); } elseif ( isset( $_POST['preschool_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['preschool_name'] ) ) ); } ?>">
								<label for="preschool_name"><?php esc_html_e( 'Previous School', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Address Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="address" class="form-control student_address validate[required,custom[address_description_validation]]" autocomplete="address" maxlength="120" type="text" name="address" value="<?php if ( $edit ) { echo esc_attr( $student_data->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['address'] ) ) ); } ?>">
								<label for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="city_name" class="form-control student_city validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['city_name'] ) ) ); }?>">
								<label for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="state_name" class="form-control student_state validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['state_name'] ) ) ); }?>">
								<label for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="zip_code" class="form-control student_zip validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ) ); }?>">
								<label for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mb-3 mjschool-margin-15px-rtl">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label" for="mjschool_enable_exam_mail"><?php esc_html_e( 'Parent Address Same as Student Address', 'mjschool' ); ?></label>
										<input id="mjschool_enable_exam_mail" class="same_as_address" type="checkbox" name="same_as_address" value="1">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'save_mjschool-admission-form' ); ?>
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
										<sapn class="mjschool-custom-top-label"><?php esc_html_e( 'Siblings', 'mjschool' ); ?></sapn>
										<div class="checkbox mjschool-checkbox-label-padding-8px">
											<label>
												<input type="checkbox" id="chkIsTeamLead" <?php if ( $edit ) { $sibling_data = $student_data->sibling_information; $sibling      = json_decode( $sibling_data ); if ( ! empty( $student_data->sibling_information ) ) { foreach ( $sibling as $value ) { if ( ! empty( $value->siblingsclass ) && ! empty( $value->siblingsstudent ) ) { ?> checked  <?php } } } } ?> />&nbsp;&nbsp;<?php esc_html_e( 'In case of any sibling ? click here', 'mjschool' ); ?>
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
			if ( $edit ) 
			{
				if ( ! empty( $student_data->sibling_information ) ) 
				{
					$sibling_data = $student_data->sibling_information;
					$sibling      = json_decode( $sibling_data );
					if ( ! empty( $sibling ) ) 
					{
						$count_array = count( $sibling );
					} 
					else 
					{
						$count_array = 0;
					}
					$i = 1;
					?>
					<div id="mjschool-sibling-div" class="mjschool-sibling-div-none mjschool-sibling-div_clss" data-sibling-index="<?php echo esc_attr($i); ?>">
						<?php
						if ( ! empty( $sibling ) ) 
						{
							foreach ( $sibling as $value ) 
							{
								?>
								<input type="hidden" id="admission_sibling_id" name="admission_sibling_id" value="<?php echo esc_attr( $count_array ); ?>" />
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_change_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
											<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="sibling_class_change_<?php echo esc_attr( $i ); ?>">
												<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
												<?php
												foreach ( mjschool_get_all_class() as $classdata ) 
												{
													?>
													<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $value->siblingsclass, $classdata['class_id'] ); ?>>
														<?php echo esc_html( $classdata['class_name'] ); ?>
													</option>
													<?php
												}
												?>
											</select>
										</div>
										<?php if ( $school_type === 'school' ) {?>
											<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
												<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
												<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section_<?php echo esc_attr( $i ); ?>">
													<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
													<?php
													if ( $edit ) 
													{
														foreach ( mjschool_get_class_sections( $value->siblingsclass ) as $sectiondata ) 
														{
															?>
															<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $value->siblingssection, $sectiondata->id ); ?>>
																<?php echo esc_html( $sectiondata->section_name ); ?>
															</option>
															<?php
														}
													}
													?>
												</select>
											</div>
										<?php } ?>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
											<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list_<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Student', 'mjschool' ); ?></label>
											<select name="siblingsstudent[]" id="sibling_student_list_<?php echo esc_attr( $i ); ?>" class="form-control mjschool-max-width-100px">
												<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
												<?php
												if ( $edit )
												{
													if ( mjschool_student_display_name_with_roll( $value->siblingsstudent ) !== 'N/A' )
													{
														echo '<option value="' . esc_attr( $value->siblingsstudent ) . '" ' . selected( $value->siblingsstudent, $value->siblingsstudent ) . '>' . esc_html( mjschool_student_display_name_with_roll( $value->siblingsstudent ) ) . '</option>';
													}
												}
												?>
											</select>
										</div>
										<input type="hidden" class="click_value" name="" value="<?php echo esc_attr( $count_array + 1 ); ?>">
										<?php
										if ( $i === 1 )
										{
											?>
											<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
												
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
												
											</div>
											<?php
										} else {
											?>
											<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
												<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-float-right mjschool-input-btn-height-width">
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
								++$i;
							}
						} 
						else
						{
							?>
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
										<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
											<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata )
											{
												?>
												<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>">
													<?php echo esc_html( $classdata['class_name'] ); ?>
												</option>
												<?php
											}
											?>
										</select>
									</div>
									<?php if ( $school_type === 'school' ) {?>
										<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
											<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
											<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section">
												<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
											</select>
										</div>
									<?php }?>
									<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
										<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required]">
											<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
										</select>
									</div>
									<input type="hidden" class="click_value" name="" value="1">
									<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
										
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
										
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
			} 
			else 
			{
				?>
				<div id="mjschool-sibling-div" class="mjschool-sibling-div_clss mjschool-sibling-div_clss">
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-form-select">
								<label class="mjschool-custom-top-label mjschool-lable-top top" for="mjschool-sibling-class-change"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<select name="siblingsclass[]" class="form-control validate[required] mjschool-class-in-student mjschool-max-width-100px" id="mjschool-sibling-class-change">
									<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
									<?php
									foreach ( mjschool_get_all_class() as $classdata )
									{
										?>
										<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>">
											<?php echo esc_html( $classdata['class_name'] ); ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
							<?php if ( $school_type === 'school' ) {?>
								<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-form-select">
									<label class="mjschool-custom-top-label mjschool-lable-top top" for="sibling_class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<select name="siblingssection[]" class="form-control mjschool-max-width-100px" id="sibling_class_section">
										<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									</select>
								</div>
							<?php }?>
							<div class="col-sm-4 col-md-4 col-lg-4 col-xl-4 input mjschool-class-section-hide">
								<label class="ml-1 mjschool-custom-top-label top" for="sibling_student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<select name="siblingsstudent[]" id="sibling_student_list" class="form-control mjschool-max-width-100px validate[required]">
									<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
								</select>
							</div>
							<input type="hidden" class="click_value" name="" value="1">
							<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
								
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_siblings()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			?>
			<div class="header">
				<h3 class="mjschool-first-header"><?php esc_html_e( 'Family Information', 'mjschool' ); ?></h3>
			</div>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 mjschool-margin-bottom-20px mjschool-rtl-margin-top-15px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="input-group">
										<sapn class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Parental Status', 'mjschool' ); ?></sapn>
										<div class="d-inline-block mjschool-family-information">
											<?php
											$pstatus = 'Both';
											if ( $edit ) {
												$pstatus = $student_data->parent_status;
												if ( $pstatus === '' )
												{
													$pstatus = 'Both';
												}
											} elseif ( isset( $_POST['pstatus'] ) ) {
												$pstatus = sanitize_text_field(wp_unslash($_POST['pstatus']));
											}
											?>
											<input type="radio" name="pstatus" class="tog" value="Father" id="sinfather" <?php checked( 'Father', $pstatus ); ?>>
											<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="sinfather"><?php esc_html_e( 'Father', 'mjschool' ); ?></label>
											&nbsp;&nbsp; <input type="radio" name="pstatus" id="sinmother" class="tog" value="Mother" <?php checked( 'Mother', $pstatus ); ?>>
											<label class="mjschool-custom-control-label" for="sinmother"><?php esc_html_e( 'Mother', 'mjschool' ); ?></label>
											&nbsp;&nbsp; <input type="radio" name="pstatus" id="boths" class="tog" value="Both" <?php checked( 'Both', $pstatus ); ?>>
											<label class="mjschool-custom-control-label" for="boths"><?php esc_html_e( 'Both', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				$m_display_none = '';
				$f_display_none = '';
				if ( $edit ) {
					$pstatus = $student_data->parent_status;
					if ( $pstatus === 'Father' ) {
						$m_display_none = 'family_display_none';
					} elseif ( $pstatus === 'Mother' ) {
						$f_display_none = 'family_display_none';
					}
				}
				?>
				<!-- Father Information. -->
				<div class="row father_div <?php echo esc_attr( $f_display_none ); ?>">
					<div class="header" id="fatid">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Father Information', 'mjschool' ); ?></h3>
					</div>
					<div id="fatid1" class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="fathersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
						<select class="form-control validate[required]" name="fathersalutation" id="fathersalutation">
							<option value="Mr"><?php esc_html_e( 'Mr', 'mjschool' ); ?></option>
						</select>
					</div>
					<div id="fatid2" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_first_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_first_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_first_name ); } elseif ( isset( $_POST['father_first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_first_name'])) ); } ?>">
								<label for="father_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid3" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_middle_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_middle_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_middle_name ); } elseif ( isset( $_POST['father_middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_middle_name'])) ); } ?>">
								<label for="father_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid4" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_last_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_last_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_last_name ); } elseif ( isset( $_POST['father_last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_last_name'])) ); } ?>">
								<label for="father_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid13" class="col-md-6 mjschool-rtl-margin-top-15px">
						<div class="form-group mjschool-radio-button-bottom-margin-rs">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="input-group">
										<sapn class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?></sapn>
										<div class="d-inline-block">
											<?php
											$father_gender = 'male';
											if ( $edit ) {
												$father_gender = $student_data->fathe_gender;
											} elseif ( isset( $_POST['fathe_gender'] ) ) {
												$father_gender = sanitize_text_field(wp_unslash($_POST['fathe_gender']));
											}
											?>
											<input type="radio" value="male" class="tog" name="fathe_gender" <?php checked( 'male', $father_gender ); ?> />
											<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
											<input type="radio" value="female" class="tog" name="fathe_gender" <?php checked( 'female', $father_gender ); ?> />
											<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
											<input type="radio" value="other" class="tog" name="fathe_gender" <?php checked( 'other', $father_gender ); ?> />
											<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="fatid14" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_birth_date" class="form-control date_picker birth_date" type="text" name="father_birth_date" value="<?php if ( $edit ) { if ( $student_data->father_birth_date === '' ) { echo ''; } else { echo esc_attr( mjschool_get_date_in_input_box( $student_data->father_birth_date ) ); } } elseif ( isset( $_POST['father_birth_date'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_birth_date'])) ); } ?>" readonly>
								<label for="father_birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid15" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_address" class="form-control parent_address validate[custom[address_description_validation]]" maxlength="120" type="text" name="father_address" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_address ); } elseif ( isset( $_POST['father_address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_address'])) ); } ?>">
								<label for="father_address"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid17" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_city_name" class="form-control parent_city validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_city_name ); } elseif ( isset( $_POST['father_city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_city_name'])) ); }?>">
								<label for="father_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid16" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_state_name" class="form-control parent_state validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="father_state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_state_name ); } elseif ( isset( $_POST['father_state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_state_name'])) ); } ?>">
								<label for="father_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid18" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_zip_code" class="form-control parent_zip validate[custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="father_zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_zip_code ); } elseif ( isset( $_POST['father_zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_zip_code'])) ); } ?>">
								<label for="father_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid5" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input email_tpye="father_email" id="father_email" class="addmission_email_id form-control validate[custom[email]] text-input father_email" maxlength="100" type="text" name="father_email" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_email ); } elseif ( isset( $_POST['father_email'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['father_email'])) ); } ?>">
								<label for="father_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid6" class="col-md-6">
						<div class="row">
							<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phone_code" name="phone_code" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="father_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="father_mobile" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_mobile ); } elseif ( isset( $_POST['father_mobile'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_mobile'])) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="father_mobile"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="fatid7" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_school" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_school" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_school ); } elseif ( isset( $_POST['father_school'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_school'])) ); } ?>">
								<label for="father_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid8" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_medium" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_medium" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_medium ); } elseif ( isset( $_POST['father_medium'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_medium'])) ); } ?>">
								<label for="father_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid9" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_education" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_education" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_education ); } elseif ( isset( $_POST['father_education'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_education'])) ); } ?>">
								<label for="father_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid10" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="fathe_income" class="form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" maxlength="50" type="text" name="fathe_income" value="<?php if ( $edit ) { echo esc_attr( $student_data->fathe_income ); } elseif ( isset( $_POST['fathe_income'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['fathe_income'])) ); } ?>">
								<label for="fathe_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="fatid9" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="father_occuption" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="father_occuption" value="<?php if ( $edit ) { echo esc_attr( $student_data->father_occuption ); } elseif ( isset( $_POST['father_occuption'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_occuption'])) ); } ?>">
								<label for="father_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<?php
					if ( $edit ) 
					{
						$father_doc      = str_replace( '"[', '[', $student_data->father_doc );
						$father_doc1     = str_replace( ']"', ']', $father_doc );
						$father_doc_info = json_decode( $father_doc1 );
						?>
						<div class="col-md-6" id="mjschool-fatid12">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<sapn class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></sapn>
									<div class="col-sm-12">
										<input type="file" name="father_doc" class="col-md-12 form-control file mjschool-file-validation">
										<input type="hidden" name="father_doc_hidden" value="<?php if ( $edit ) { if ( ! empty( $father_doc_info[0]->value ) ) { echo esc_attr( $father_doc_info[0]->value ); } else { echo ''; } } else { echo ''; } ?>">
									</div>
									<?php
									if ( ! empty( $father_doc_info[0]->value ) )
									{
										$safe_filename = sanitize_file_name( $father_doc_info[0]->value );
										?>
										<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
											<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $safe_filename ); ?>"><i class="fa fa-download"></i><?php esc_html_e( 'Download', 'mjschool' ); ?></a>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
					} 
					else
					{
						?>
						<div class="col-md-6" id="mjschool-fatid12">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<sapn class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></sapn>
									<div class="col-sm-12">
										<input type="file" name="father_doc" class="col-md-12 form-control file mjschool-file-validation input-file" value="<?php if ( $edit ) { echo esc_attr( $father_doc_info[0]->value ); } elseif ( isset( $_POST['father_doc'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['father_doc'])) ); } ?>">
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<!-- Mother Information. -->
				<div class="row mother_div <?php echo esc_attr( $m_display_none ); ?>">
					<div class="header" id="motid">
						<h3 class="mjschool-first-header"><?php esc_html_e( 'Mother Information', 'mjschool' ); ?></h3>
					</div>
					<div id="motid1" class="col-md-6 input mother_info">
						<label class="ml-1 mjschool-custom-top-label top" for="mothersalutation"><?php esc_html_e( 'Salutation', 'mjschool' ); ?></label>
						<select class="form-control validate[required]" name="mothersalutation" id="mothersalutation">
							<option value="Ms"><?php esc_html_e( 'Ms', 'mjschool' ); ?></option>
							<option value="Mrs"><?php esc_html_e( 'Mrs', 'mjschool' ); ?></option>
							<option value="Miss"><?php esc_html_e( 'Miss', 'mjschool' ); ?></option>
						</select>
					</div>
					<div id="motid2" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_first_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_first_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_first_name ); } elseif ( isset( $_POST['mother_first_name'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_first_name'])) ); } ?>">
								<label for="mother_first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid3" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_middle_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_middle_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_middle_name ); } elseif ( isset( $_POST['mother_middle_name'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_middle_name'])) ); } ?>">
								<label for="mother_middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid4" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_last_name" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_last_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_last_name ); } elseif ( isset( $_POST['mother_last_name'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_last_name'])) ); } ?>">
								<label for="mother_last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid13" class="col-md-6 mjschool-rtl-margin-top-15px">
						<?php
						$mother_gender = 'female';
						if ( $edit ) {
							$mother_gender = $student_data->mother_gender;
						} elseif ( isset( $_POST['mother_gender'] ) ) {
							$mother_gender = sanitize_text_field(wp_unslash($_POST['mother_gender']));
						}
						?>
						<div class="form-group mjschool-radio-button-bottom-margin-rs">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio">
									<div class="input-group">
										<sapn class="mjschool-custom-top-label mjschool-margin-left-0"><?php esc_html_e( 'Gender', 'mjschool' ); ?></sapn>
										<div class="d-inline-block">
											<?php
											$father_gender = 'male';
											if ( $edit ) {
												$father_gender = $student_data->fathe_gender;
											} elseif ( isset( $_POST['fathe_gender'] ) ) {
												$father_gender = sanitize_text_field(wp_unslash($_POST['fathe_gender']));
											}
											?>
											<input type="radio" value="male" class="tog" name="mother_gender" <?php checked( 'male', $mother_gender ); ?> />
											<label class="mjschool-custom-control-label mjschool-margin-right-20px" for="male"><?php esc_html_e( 'Male', 'mjschool' ); ?></label>
											<input type="radio" value="female" class="tog" name="mother_gender" <?php checked( 'female', $mother_gender ); ?> />
											<label class="mjschool-custom-control-label" for="female"><?php esc_html_e( 'Female', 'mjschool' ); ?></label>
											<input type="radio" value="other" class="tog" name="mother_gender" <?php checked( 'other', $mother_gender ); ?> />
											<label class="mjschool-custom-control-label" for="other"><?php esc_html_e( 'Other', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="motid14" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_birth_date" class="form-control date_picker birth_date" type="text" name="mother_birth_date" value="<?php if ( $edit ) { if ( $student_data->mother_birth_date === '' ) { echo ''; } else { echo esc_attr( mjschool_get_date_in_input_box( $student_data->mother_birth_date ) ); } } elseif ( isset( $_POST['mother_birth_date'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_birth_date'])) ); } ?>" readonly>
								<label for="mother_birth_date" class="date_label"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid15" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_address" class="form-control parent_address validate[custom[address_description_validation]]" maxlength="120" type="text" name="mother_address" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_address ); } elseif ( isset( $_POST['mother_address'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_address'])) ); } ?>">
								<label for="mother_address"><?php esc_html_e( 'Address', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid17" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_city_name" class="form-control parent_city validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_city_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_city_name ); } elseif ( isset( $_POST['mother_city_name'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_city_name'])) ); } ?>">
								<label for="mother_city_name"><?php esc_html_e( 'City', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid16" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_state_name" class="form-control parent_state validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="mother_state_name" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_state_name ); } elseif ( isset( $_POST['mother_state_name'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_state_name'])) ); } ?>">
								<label for="mother_state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid18" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_zip_code" class="form-control parent_zip validate[custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="mother_zip_code" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_zip_code ); } elseif ( isset( $_POST['mother_zip_code'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_zip_code'])) ); } ?>">
								<label for="mother_zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid5" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_email" email_tpye="mother_email" class="addmission_email_id form-control  validate[custom[email]]  text-input mother_email" maxlength="100" type="text" name="mother_email" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_email ); } elseif ( isset( $_POST['mother_email'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mother_email'])) ); } ?>">
								<label for="mother_email"><?php esc_html_e( 'Email', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid6" class="col-md-6">
						<div class="row">
							<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
								<div class="form-group input mjschool-margin-bottom-0">
									<div class="col-md-12 form-control mjschool-mobile-input">
										<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
										<input id="phone_code" name="phone_code" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
										<input id="mother_mobile" class="form-control text-input validate[custom[phone_number],minSize[6],maxSize[15]]" type="text" name="mother_mobile" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_mobile ); } elseif ( isset( $_POST['mother_mobile'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_mobile'])) ); } ?>">
										<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mother_mobile"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="motid7" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_school" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_school" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_school ); } elseif ( isset( $_POST['mother_school'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_school'])) ); } ?>">
								<label for="mother_school"><?php esc_html_e( 'School Name', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid8" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_medium" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_medium" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_medium ); } elseif ( isset( $_POST['mother_medium'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_medium'])) ); } ?>">
								<label for="mother_medium"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid9" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_education" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_education" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_education ); } elseif ( isset( $_POST['mother_education'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_education'])) ); } ?>">
								<label for="mother_education"><?php esc_html_e( 'Educational Qualification', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid10" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_income" class="form-control validate[custom[onlyNumberSp],maxSize[8],min[0]] text-input" type="text" name="mother_income" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_income ); } elseif ( isset( $_POST['mother_income'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_income'])) ); } ?>">
								<label for="mother_income"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div id="motid9" class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="mother_occuption" class="form-control validate[custom[city_state_country_validation]] text-input" maxlength="50" type="text" name="mother_occuption" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_occuption ); } elseif ( isset( $_POST['mother_occuption'] ) ) { esc_attr( sanitize_text_field(wp_unslash($_POST['mother_occuption'])) ); } ?>">
								<label for="mother_occuption"><?php esc_html_e( 'Occupation', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<?php
					if ( $edit ) {
						$mother_doc      = str_replace( '"[', '[', $student_data->mother_doc );
						$mother_doc1     = str_replace( ']"', ']', $mother_doc );
						$mother_doc_info = json_decode( $mother_doc1 );
						?>
						<div id="mjschool-motid12" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<sapn class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></sapn>
									<div class="col-sm-12">
										<input type="file" name="mother_doc" class="col-md-12 form-control file mjschool-file-validation input-file">
										<input type="hidden" name="mother_doc_hidden" value="<?php if ( $edit ) { if ( ! empty( $mother_doc_info[0]->value ) ) { echo esc_attr( $mother_doc_info[0]->value ); } else { echo ''; } } else { echo ''; } ?>">
									</div>
									<?php
									if ( ! empty( $mother_doc_info[0]->value ) ) {
										$safe_filename = sanitize_file_name( $mother_doc_info[0]->value );
										?>
										<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
											<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $safe_filename ); ?>"><i class="fa fa-download"></i><?php esc_html_e( 'Download', 'mjschool' ); ?></a>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
					} else {
						?>
						<div id="mjschool-motid12" class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
									<sapn class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl mjschool-margin-left-30px"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></sapn>
									<div class="col-sm-12">
										<input type="file" name="mother_doc" class="col-md-12 form-control file mjschool-file-validation input-file" value="<?php if ( $edit ) { echo esc_attr( $student_data->mother_doc ); } elseif ( isset( $_POST['mother_doc'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mother_doc'])) ); } ?>">
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
				// --------- Get Module Wise Custom Field Data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'admission';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
				?>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-xs-12">
						<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Admission', 'mjschool' ); } else { esc_attr_e( 'New Admission', 'mjschool' ); } ?>" name="student_admission" class="mjschool-save-btn" />
					</div>
				</div>
			</div>
	</div>
	</form> <!------ Form End. ----->
</div><!-------- Panel body. -------->
	<?php
}
?>