<?php
/**
 * Teacher Add/Edit Form Template.
 *
 * Handles teacher data entry (personal info, contact info, login credentials, profile image, and document uploads).
 * This template supports both Add and Edit operations.
 * 
 * Key functionalities:
 * - Form validation for text, email, and file input fields.
 * - AJAX-based dynamic document management (add/remove).
 * - File upload restrictions (type and size) based on admin settings.
 * - Conditional form rendering for “Add” and “Edit” modes.
 * - Integration with MjSchool custom fields.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/teacher
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_role = 'teacher';
$edit = 0;
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
if ( $action === 'edit' ) {
    $edit = 1;
    $teacher_id_encrypted = isset( $_REQUEST['teacher_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_id'] ) ) : '';
    if ( ! empty( $teacher_id_encrypted ) ) {
        $user_info = get_userdata( intval( mjschool_decrypt_id( $teacher_id_encrypted ) ) );
        $user_deligation = get_user_meta( intval( mjschool_decrypt_id( $teacher_id_encrypted ) ), 'designation', true );
    }
}
?>
<?php
$document_option    = get_option( 'mjschool_upload_document_type' );
$document_type      = explode( ', ', $document_option );
$document_type_json = $document_type;
$document_size      = get_option( 'mjschool_upload_document_size' );
?>
<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
	<!-- Add teacher form start. -->
	<form name="teacher_form" action="" method="post" class="form-horizontal" id="teacher_form" enctype='multipart/form-data'>
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
		<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="role" value="<?php echo esc_attr( $mjschool_role ); ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'PERSONAL Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!-- Mjschool-user-form. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="first_name" autocomplete="first_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) ); } ?>" name="first_name">
							<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="middle_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) ); } ?>" name="middle_name">
							<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) ); } ?>" name="last_name">
							<label  for="last_name"><?php esc_html_e( 'Last Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
					<div class="form-group">
						<div class="col-md-12 form-control">
							<div class="row mjschool-padding-radio">
								<div class="input-group">
									<span class="mjschool-custom-top-label mjschool-margin-left-0" for="gender"><?php esc_html_e( 'Gender', 'mjschool' ); ?><span class="mjschool-require-field">*</span></span>
									<div class="d-inline-block">
										<?php
										$genderval = 'male';
										if ( $edit ) {
											$genderval = $user_info->gender;
										} elseif ( isset( $_POST['gender'] ) ) {
											$genderval = sanitize_text_field(wp_unslash($_POST['gender']));
										}
										?>
										<label class="radio-inline">
											<input type="radio" value="<?php echo esc_attr('male'); ?>" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?>
										</label>
										&nbsp;&nbsp;
										<label class="radio-inline">
											<input type="radio" value="<?php echo esc_attr('female'); ?>" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); } ?>" readonly>
							<label class="date_label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-4 input mjschool-width-70px">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_designation"><?php esc_html_e( 'Designation', 'mjschool' ); ?><span class="required">*</span></label>
					<?php
					if ( $edit ) {
						$sectionval1 = $user_deligation;
					} elseif ( isset( $_POST['designation'] ) ) {
						$sectionval1 = sanitize_text_field(wp_unslash($_POST['designation']));
					} else {
						$sectionval1 = '';
					}
					?>
					<select id="mjschool_designation" class="form-control validate[required] designation mjschool-width-100px"  name="designation">
						<option value=""><?php esc_html_e( 'Select Designation', 'mjschool' ); ?></option>
						<?php
						$activity_category = mjschool_get_all_category( 'designation' );
						if ( ! empty( $activity_category ) ) {
							foreach ( $activity_category as $retrive_data ) {
								?>
								<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $sectionval1 ); ?>><?php echo esc_html( $retrive_data->post_title ); ?> </option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-md-2 col-sm-1 mjschool-res-width-30px">
					<input type="button" id="mjschool-addremove-cat" value="<?php esc_attr_e( 'ADD', 'mjschool' ); ?>" model="designation" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div><!-- Mjschool-user-form. -->
		<div class="header"><!-- Header div. -->
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!--Mjschool-user-form div.-->
			<div class="row"><!--Row div.-->
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" value="<?php if ( $edit ) { echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_textarea_field(wp_unslash($_POST['address'])) ); } ?>">
							<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); } ?>">
							<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); } ?>">
							<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="zip_code" class="form-control  validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); } ?>">
							<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
					<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
						<?php
						if ( $edit ) {
							$classval = $user_info->class_name;
						} elseif ( isset( $_POST['class_name'] ) ) {
							$classval = sanitize_text_field(wp_unslash($_POST['class_name']));
						} else {
							$classval = '';
						}
						$classes = array();
						if ( isset( $_REQUEST['teacher_id'] ) ) {
							$classes = $teacher_obj->mjschool_get_class_by_teacher( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) );
						}
						?>
						<select name="class_name[]" multiple="multiple" class="form-control" id="class_name">
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $teacher_obj->mjschool_in_array_r( $classdata['class_id'], $classes ), true ); ?>> <?php echo esc_html( $classdata['class_name'] ); ?> </option>
							<?php } ?>
						</select>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="class_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
					<div class="row">
						<div class="col-md-12 mobile_error_massage_left_margin">
							<div class="form-group input mjschool-margin-bottom-0">
								<div class="col-md-12 form-control mjschool-mobile-input">
									<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
									<input id="phonecode" name="alter_mobile_number" type="hidden" class="form-control validate[required] onlynumber_and_plussign" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" maxlength="5">
									<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control phonecode" name="phonecode">
									<input id="mobile_number" class="form-control mjschool-margin-top-10px_res validate[required,custom[user_mobile]],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); } ?>">
									<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="row">
						<div class="col-md-12">
							<div class="form-group input mjschool-margin-bottom-0">
								<div class="col-md-12 form-control mjschool-mobile-input">
									<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
									<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control phonecode" name="alter_mobile_number">
									<input id="alternet_mobile_number" class="form-control mjschool-margin-top-10px_res text-input validate[minSize[6],maxSize[15]]" type="text" name="alternet_mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->alternet_mobile_number ); } elseif ( isset( $_POST['alternet_mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['alternet_mobile_number'])) ); } ?>">
									<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
					<label class="ml-1 mjschool-custom-top-label top" for="working_hour"><?php esc_html_e( 'Working Hour', 'mjschool' ); ?></label>
					<?php
					if ( $edit ) {
						$workrval = $user_info->working_hour;
					} elseif ( isset( $_POST['working_hour'] ) ) {
						$workrval = sanitize_text_field(wp_unslash($_POST['working_hour']));
					} else {
						$workrval = '';
					}
					?>
					<select name="working_hour" class="form-control" id="working_hour">
						<option value=""><?php esc_html_e( 'Select Job Time', 'mjschool' ); ?></option>
						<option value="full_time" <?php selected( $workrval, 'full_time' ); ?>><?php esc_html_e( 'Full Time', 'mjschool' ); ?></option>
						<option value="half_day" <?php selected( $workrval, 'half_day' ); ?>><?php esc_html_e( 'Part time', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-padding-top-15px-res">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="possition" class="form-control validate[custom[address_description_validation]]" maxlength="50" type="text" name="possition" value="<?php if ( $edit ) { echo esc_attr( $user_info->possition ); } elseif ( isset( $_POST['possition'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['possition'])) ); } ?>">
							<label  for="possition"><?php esc_html_e( 'Position', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div><!-- Row div. -->
		</div><!-- Mjschool-user-form div. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!-- Mjschool-user-form div. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="email" class="mjschool-student-email-id form-control validate[required,custom[email]] text-input" maxlength="100" type="text" name="email" value="<?php if ( $edit ) { echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_email(wp_unslash($_POST['email'])) ); } ?>" autocomplete="email">
							<label  for="email"><?php esc_html_e( 'Email', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="password" class="form-control <?php if ( ! $edit ) { echo 'validate[required,minSize[8],maxSize[12]]'; } else { echo 'validate[minSize[8],maxSize[12]]'; }?>" type="password" name="password" autocomplete="current-password">
							<label  for="password"><?php esc_html_e( 'Password', 'mjschool' ); ?>
								<?php if ( ! $edit ) { ?>
									<span class="mjschool-require-field">*</span>
								<?php } ?>
							</label>
							<!-- Use class + data-target. -->
							<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
						</div>
					</div>
				</div>
			</div>
		</div><!--Mjschool-user-form div.-->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!--Mjschool-user-form div.-->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
							<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Image', 'mjschool' ); ?></span>
							<div class="col-sm-12 mjschool-display-flex">
								<input type="text" id="smgt_user_avatar_url" class="form-control mjschool-image-path-dots" name="mjschool_user_avatar" value="<?php echo esc_url( $edit ? $user_info->mjschool_user_avatar : ( isset($_POST['mjschool_user_avatar']) ? sanitize_text_field( wp_unslash( $_POST['mjschool_user_avatar'] ) ) : '' ) ); ?>" readonly />
								<input id="upload_user_avatar_button" type="button" class="button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
							
							<div id="mjschool-upload-user-avatar-preview">
								<?php if ($edit) {
									if ($user_info->mjschool_user_avatar === "") { ?>
										<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ); ?>">
										<?php
									} else {
										?>
										<img class="mjschool-image-preview-css" src="<?php echo esc_url( $edit && $user_info->mjschool_user_avatar !== '' ? $user_info->mjschool_user_avatar : get_option( 'mjschool_teacher_thumb_new' ) ); ?>">
										<?php
									}
								} else {
									?>
									<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_teacher_thumb_new' ) ); ?>">
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
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Documnt Details', 'mjschool' ); ?></h3>
		</div>
		<div class="mjschool-more-document">
			<?php
			if ($edit) {
				// Check user document exists or not.
				if ( ! empty( $user_info->user_document ) ) {
					$document_array = json_decode($user_info->user_document);
					foreach ($document_array as $key => $value) {
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="mjschool_document_title" class="form-control text-input" maxlength="50" type="text" value="<?php echo esc_attr($value->document_title); ?>" name="document_title[]">
											<label  for="mjschool_document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-5 col-10 col-sm-1">
									<div class="form-group input">
										<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
											<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
											<div class="col-sm-12 row">
												<input type="hidden" id="user_hidden_docs" class="mjschool-image-path-dots form-control" name="user_hidden_docs[]" value="<?php echo esc_attr( sanitize_file_name( $value->document_file ) ); ?>" readonly />
												<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 mt-2">
													<input name="document_file[]" type="file" class="form-control file_validation file" />
												</div>
												<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-0">
													<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url(content_url( '/uploads/school_assets/' . sanitize_file_name( $value->document_file ) ) ); ?>" record_id="<?php echo esc_attr($key); ?>"><i class="fas fa-download"></i> <?php esc_html_e( "Download", "mjschool" ); ?></a>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
								if ($key === 0) {
									?>
									<div class="col-md-1 col-2 col-sm-1 col-xs-12">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
									</div>
									<?php  
								} else {
									?>
									<div class="col-md-1 col-2 col-sm-3 col-xs-12">
										<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
									</div>
									<?php
								}
								?>
							</div>
						</div>
						<?php
					}
				} else {
					?>
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="mjschool_document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
										<label for="mjschool_document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-5 col-10 col-sm-1">
								<div class="form-group input">
									<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
										<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
										<div class="col-sm-12 mjschool-display-flex">
											<input name="document_file[]" type="file" class="form-control file_validation file" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
										</div>
									</div>
								</div>
							</div>
							<?php  
							?>
							<div class="col-md-1 col-2 col-sm-1 col-xs-12">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
							</div>
						</div>
					</div>
					<?php
				}
			} else {
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mjschool_document_title" class="form-control text-input" maxlength="50" type="text" value="" name="document_title[]">
									<label  for="mjschool_document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-5 col-10 col-sm-1">
							<div class="form-group input">
								<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px mjschool-file-height-padding">
									<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
									<div class="col-sm-12 mjschool-display-flex">
										<input name="document_file[]" type="file" class="form-control file_validation file" value="<?php esc_attr_e( 'Upload image', 'mjschool' ); ?>" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-1 col-2 col-sm-1 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
						</div>
					</div>
					
				</div>
				<?php
			}
			if ( $edit ) {
				$signature_file = get_user_meta( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['teacher_id'])) ) ), 'signature', true );
				?>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
							<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px mjschool-label-position-rtl"><?php esc_html_e( 'Signature', 'mjschool' ); ?></label>
							<div class="col-sm-12">
								<input type="file" name="signature" class='form-control' id="signature" />
								<input type="hidden" name="signaturehidden" value="<?php if ( $edit ) { echo esc_attr( $signature_file ); } else { echo '';} ?>">
							</div>
							<?php
							if ( ! empty( $signature_file ) ) {
								?>
								<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
									<a target="blank" class="mjschool-status-read btn btn-default" href="<?php echo esc_url( content_url( '/' . sanitize_file_name( $signature_file ) ) ); ?>"><i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a>
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		// --------- Get module-wise custom field data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'teacher';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form"> <!--Mjschool-user-form div.-->
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-12"><!--Save btn.-->
					<input type="submit" id="mjschool-class-for-alert" value="<?php if ( $edit ) { esc_attr_e( 'Save Teacher', 'mjschool' ); } else { esc_attr_e( 'Add Teacher', 'mjschool' );}?>" name="save_teacher" class="mjschool-save-btn mjschool-class-for-alert mjschool-rtl-margin-0px" />
				</div><!--Save btn.-->
			</div>
		</div>
	</form>
	<!------ Add teacher form end. ------>
</div>