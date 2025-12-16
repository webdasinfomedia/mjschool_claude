<?php
/**
 * Admin: Parent Form Template.
 *
 * Handles rendering and client-side validation for the Parent form in the MJ School plugin.
 * Includes personal, contact, login, and child assignment information, along with document uploads.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/parent
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$students = mjschool_get_student_group_by_class();
$mjschool_role     = 'parent';
?>
<?php
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit      = 1;
	$user_info = get_userdata( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['parent_id'])) ) ) );
}
$assign_parent = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'assign_parent' ) {
	$assign_parent = 1;
	$student_id    = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
}
?>
<div class="mjschool-panel-body"><!-- Mjschool-panel-body. -->
	<form name="parent_form" action="" method="post" class="mjschool-form-horizontal" id="parent_form" enctype="multipart/form-data"><!--  form div -->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden"  name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_nonce' ) ); ?>">
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="role" value="<?php echo esc_attr( $mjschool_role ); ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'PERSONAL Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!-- User form. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="first_name" autocomplete="name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->first_name ); } elseif ( isset( $_POST['first_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['first_name'])) ); }?>" name="first_name">
							<label  for="first_name"><?php esc_html_e( 'First Name', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="middle_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->middle_name ); } elseif ( isset( $_POST['middle_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['middle_name'])) ); }?>" name="middle_name">
							<label  for="middle_name"><?php esc_html_e( 'Middle Name', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="last_name" class="form-control validate[required,custom[city_state_country_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $user_info->last_name ); } elseif ( isset( $_POST['last_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['last_name'])) ); }?>" name="last_name">
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
											<input type="radio" value="male" class="tog validate[required]" name="gender" <?php checked( 'male', $genderval ); ?> /><?php esc_html_e( 'Male', 'mjschool' ); ?>
										</label>
										&nbsp;&nbsp;
										<label class="radio-inline">
											<input type="radio" value="female" class="tog validate[required]" name="gender" <?php checked( 'female', $genderval ); ?> /><?php esc_html_e( 'Female', 'mjschool' ); ?>
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
							<input id="birth_date" class="form-control date_picker validate[required]" type="text" name="birth_date" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( $user_info->birth_date ) ); } elseif ( isset( $_POST['birth_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['birth_date'])) ) ); }?>" readonly>
							<label class="date_label" for="birth_date"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="relation"><?php esc_html_e( 'Relation', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( $edit ) {
						$relationval = esc_html( $user_info->relation );
					} elseif ( isset( $_POST['relation'] ) ) {
						$relationval = sanitize_text_field(wp_unslash($_POST['relation']));
					} else {
						$relationval = '';
					}
					?>
					<select name="relation" class="form-control validate[required]" id="relation">
						<option value=""><?php esc_html_e( 'Select Relation', 'mjschool' ); ?></option>
						<option value="Father" <?php selected( $relationval, 'Father' ); ?>><?php esc_html_e( 'Father', 'mjschool' ); ?></option>
						<option value="Mother" <?php selected( $relationval, 'Mother' ); ?>><?php esc_html_e( 'Mother', 'mjschool' ); ?></option>
					</select>
				</div>
			</div>
		</div><!-- User form. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Child Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!-- User form. -->
			<?php
			if ( $edit ) {
				$parent_data = get_user_meta( $user_info->ID, 'child', true );
				if ( ! empty( $parent_data ) ) {
					
					$i = 1;
					foreach ($parent_data as $id1) {
						?>
						<!-- Edit time. -->
						<div id="mjschool-parents-child" class="form-group row mb-3 mjschool-parents-child">
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<select name="chield_list[]" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
									<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
									<?php
									foreach ($students as $label => $opt) { ?>
										<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . " " . esc_attr($label); ?>">
											<?php foreach ($opt as $id => $name): ?>
												<option value="<?php echo esc_attr($id); ?>" <?php selected($id, $id1);  ?>><?php echo esc_html( $name); ?></option>
											<?php endforeach; ?>
										</optgroup>
									<?php } ?>
								</select>
							</div>
							<?php
							if ($i === 1) {
								?>
								<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
								</div>
								<?php
							} else {
								?>
								<div class="col-md-1 col-sm-3 col-xs-12 mjschool-width-20px-res">
									<input type="image" onclick="mjschool_delete_parent_elementChild(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-remove-certificate mjschool-input-btn-height-width">
								</div>
								<?php
							}
							?>
						</div>
						<?php
						$i++;
					}
				} else { ?>
					<div id="mjschool-parents-child" class="row mb-3 mjschool-parents-child">
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<select name="chield_list[]" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
								<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
								<?php
								foreach ($students as $label => $opt) {
									?>
									<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . " " . esc_attr($label); ?>">
										<?php foreach ($opt as $id => $name): ?>
											<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html( $name); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php }  ?>
							</select>
						</div>
						<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
						</div>
					</div>
					<?php
				}
			} elseif ($assign_parent) {
				?>
				<div id="mjschool-parents-child" class="row mb-3 mjschool-parents-child">
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="chield_list[]" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
							<?php
							foreach ($students as $label => $opt) {
								?>
								<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . " " . esc_attr($label); ?>">
									<?php foreach ($opt as $id => $name): ?>
										<option value="<?php echo esc_attr($id); ?>" <?php selected($id, $student_id);  ?>><?php echo esc_html( $name); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php }  ?>
						</select>
					</div>
					<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
						<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
					</div>
				</div>
				<?php
			} else { 	?>
				<!-- Add time. -->
				<div id="mjschool-parents-child" class="row mb-3 mjschool-parents-child">
					<div class="col-md-6 input mjschool-width-80px-res"> <label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Child', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="chield_list[]" id="student_list" class="form-control  validate[required] mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select Child', 'mjschool' ); ?></option>
							<?php
							foreach ($students as $label => $opt) { ?>
								<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . " " . esc_attr($label); ?>">
									<?php foreach ($opt as $id => $name): ?>
										<option value="<?php echo esc_attr($id); ?>"><?php echo esc_html( $name); ?></option>
									<?php endforeach; ?>
								</optgroup>
							<?php }  ?>
						</select>
					</div>
					<div class="col-md-1 col-sm-1 col-xs-12 mjschool-width-20px-res">
						<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_Child()" class="mjschool-rtl-margin-top-15px mjschool-add-certificate" id="add_more_sibling">
					</div>
				</div>
				<?php
			}
			
			?>
		</div><!-- User form. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Contact Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!-- User form. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="address" class="form-control validate[required,custom[address_description_validation]]" maxlength="120" type="text" autocomplete="address" name="address" value="<?php if ( $edit ) { echo esc_attr( $user_info->address ); } elseif ( isset( $_POST['address'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['address'])) ); }?>">
							<label  for="address"><?php esc_html_e( 'Address', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="city_name" class="form-control validate[required,custom[city_state_country_validation]]" maxlength="50" type="text" name="city_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->city ); } elseif ( isset( $_POST['city_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['city_name'])) ); }?>">
							<label  for="city_name"><?php esc_html_e( 'City', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="state_name" class="form-control validate[custom[city_state_country_validation]]" maxlength="50" type="text" name="state_name" value="<?php if ( $edit ) { echo esc_attr( $user_info->state ); } elseif ( isset( $_POST['state_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['state_name'])) ); }?>">
							<label  for="state_name"><?php esc_html_e( 'State', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="zip_code" class="form-control  validate[required,custom[zipcode],minSize[4],maxSize[8]]" maxlength="15" type="text" name="zip_code" value="<?php if ( $edit ) { echo esc_attr( $user_info->zip_code ); } elseif ( isset( $_POST['zip_code'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['zip_code'])) ); }?>">
							<label  for="zip_code"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_parent_admin_nonce' ); ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="row">
						<div class="col-md-12 mjschool-mobile-error-massage-left-margin">
							<div class="form-group input mjschool-margin-bottom-0">
								<div class="col-md-12 form-control mjschool-mobile-input">
									<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
									<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control country_code phonecode" name="phonecode">
									<input id="mobile_number" class="form-control mjschool-btn-top validate[required],minSize[6],maxSize[15]] text-input" type="text" name="mobile_number" value="<?php if ( $edit ) { echo esc_attr( $user_info->mobile_number ); } elseif ( isset( $_POST['mobile_number'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['mobile_number'])) ); }?>">
									<label class="mjschool-custom-control-label mjschool-custom-top-label" for="mobile_number"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-mobile-input">
							<span class="input-group-text mjschool-country-code-prefix">+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?></span>
							<input type="hidden" value="+<?php echo esc_attr( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>" class="form-control country_code phonecode" name="phonecode">
							<input id="phone" class="form-control validate[custom[phone_number],minSize[6],maxSize[15]] text-input" type="text" autocomplete="phone" name="phone" value="<?php if ( $edit ) { echo esc_attr( $user_info->phone ); } elseif ( isset( $_POST['phone'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['phone'])) ); }?>">
							<label class="mjschool-custom-control-label mjschool-custom-top-label" for="phone"><?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div><!-- User form. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Login Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!-- User form. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="email" class="mjschool-student-email-id form-control validate[required,custom[email]] text-input" maxlength="100" type="text" autocomplete="email" name="email" value="<?php if ( $edit ) { echo esc_attr( $user_info->user_email ); } elseif ( isset( $_POST['email'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['email'])) ); }?>">
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
							<!-- Use class + Data-target. -->
							<i class="fas fa-eye-slash togglePassword" data-target="#password"></i>
						</div>
					</div>
				</div>
			</div>
		</div><!-- User form. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Profile Image', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"><!-- User form. -->
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
							<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Image', 'mjschool' ); ?></span>
							<div class="col-sm-12 mjschool-display-flex">
								<input type="text" id="smgt_user_avatar_url" class="mjschool-image-path-dots form-control" name="smgt_user_avatar" value="<?php if ( $edit ) { echo esc_url( $user_info->smgt_user_avatar ); } elseif ( isset( $_POST['mjschool_user_avatar'] ) ) { echo esc_url( wp_unslash($_POST['mjschool_user_avatar']) ); }?>" readonly />
								<input id="upload_user_avatar_button" type="button" class="button mjschool-upload-image-btn mjschool_float_right"  value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
							<div id="mjschool-upload-user-avatar-preview">
								<?php 
								if ($edit) {
									if ($user_info->smgt_user_avatar === "") { ?>
										<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_parent_thumb_new' ) ) ?>">
										<?php
									} else {
										?>
										<img class="mjschool-image-preview-css" src="<?php if ($edit) echo esc_url($user_info->smgt_user_avatar); ?>" />
										<?php
									}
								} else {
									?>
									<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_parent_thumb_new' ) ) ?>">
									<?php
								}
								
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> <!-- User form. -->
		<!-- Document upload field start. -->
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Documnt Details', 'mjschool' ); ?></h3>
		</div>
		<div class="mjschool-more-document">
			<?php
			if ( $edit ) {
				// Check user document exists or not.
				if ( !empty( $user_info->user_document ) ) {
					
					$document_array = json_decode($user_info->user_document);
					foreach ($document_array as $key => $value) {
						?>
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="document_title" class="form-control text-input" maxlength="50" type="text" value="<?php echo esc_attr($value->document_title); ?>" name="document_title[]">
											<label  for="document_title"><?php esc_html_e( 'Ducument Title', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-5 col-10 col-sm-1">
									<div class="form-group input">
										<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
											<span for="photo" class="mjschool-custom-control-label mjschool-custom-top-label ml-2"><?php esc_html_e( 'Document File', 'mjschool' ); ?></span>
											<div class="col-sm-12 row">
												<input type="hidden" id="user_hidden_docs" class="mjschool-image-path-dots form-control" name="user_hidden_docs[]" value="<?php echo esc_attr($value->document_file); ?>" readonly />
												<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12 mt-2">
													<input id="upload_user_avatar_button" name="document_file[]" type="file" class="form-control mjschool-file-validation file" />
												</div>
												<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 p-0">
													<a target="blank" class="mjschool-status-read btn btn-default" href="<?php print esc_url(content_url() . '/uploads/school_assets/' . $value->document_file); ?>" record_id="<?php echo esc_attr($key); ?>"><i class="fas fa-download"></i><?php esc_html_e( "Download", "mjschool" ); ?></a>
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
										<input type="image" onclick="mjschool_delete_parent_element(this)" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" class="mjschool-rtl-margin-top-15px mjschool-float-right mjschool-remove-certificate mjschool-input-btn-height-width">
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
											<input id="upload_user_avatar_button" name="document_file[]" type="file" class="form-control mjschool-file-validation file" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
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
			} else {
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
										<input id="upload_user_avatar_button" name="document_file[]" type="file" class="form-control file mjschool-file-validation" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-1 col-sm-1 col-2 col-xs-12">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_more_document()" class="mjschool-rtl-margin-top-15px mjschool-more-attachment mjschool-add-certificate mjschool-float-right" id="add_more_sibling">
						</div>
					</div>
				</div>
				<?php 
			}
			?>
		</div>
		<?php
		// --------- Get Module-Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'parent';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form"> <!--Mjschool-user-form div.-->
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-12"><!-- Start save btn.-->
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Parent', 'mjschool' ); } else { esc_html_e( 'Add Parent', 'mjschool' ); }?>" name="save_parent" class="mjschool-save-btn" />
				</div><!-- End save btn.-->
			</div>
		</div>
	</form><!--  Form div. -->
</div><!-- Mjschool-panel-body. -->