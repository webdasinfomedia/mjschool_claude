<?php
/**
 * Manage Transport Form (Admin Page).
 *
 * This file handles the creation and editing of transport records within the MJSchool plugin's
 * admin dashboard. Administrators can add, update, and manage transport details including route
 * information, driver details, fare, and associated vehicle data. It also supports image upload
 * functionality and integrates custom fields for module-specific extensions.
 *
 * Key Features:
 * - Supports both Add and Edit operations for transport records.
 * - Collects and validates key transport details such as route name, vehicle identifier,
 *   registration number, driver name, phone number, address, and fare.
 * - Includes secure file upload functionality for driver/vehicle images.
 * - Implements dynamic image preview with default thumbnail fallback.
 * - Utilizes WordPress nonces (`wp_nonce_field`) for CSRF protection.
 * - Integrates the MJSchool Custom Field system for module-level extensibility.
 * - Ensures all form inputs are validated using client-side validation rules.
 * - Designed with Bootstrap responsive classes for a clean and mobile-friendly layout.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/transport
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="add_transport"><!--------- Add transport div. ------->
	<?php
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$edit           = 1;
		$transport_data = mjschool_get_transport_by_id( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['transport_id'])) ) ) );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel body. ------->
		<form name="transport_form" action="" method="post" class="mjschool-form-horizontal" id="transport_form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="route_name" class="form-control validate[required,custom[description_validation]]" type="text" maxlength="50" value="<?php if ( $edit ) { echo esc_attr( $transport_data->route_name ); }?>" name="route_name">
								<label for="route_name"><?php esc_html_e( 'Route Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="number_of_vehicle" class="form-control validate[required,custom[onlyNumberSp]]" maxlength="15" type="text" value="<?php if ( $edit ) { echo esc_attr( $transport_data->number_of_vehicle ); }?>" name="number_of_vehicle">
								<label for="number_of_vehicle"><?php esc_html_e( 'Vehicle Identifier', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="vehicle_reg_num" class="form-control validate[required,custom[address_description_validation]] " maxlength="15" type="text" value="<?php if ( $edit ) { echo esc_attr( $transport_data->vehicle_reg_num ); }?>" name="vehicle_reg_num">
								<label for="vehicle_reg_num"><?php esc_html_e( 'Vehicle Registration Number', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<?php wp_nonce_field( 'save_transpoat_admin_nonce' ); ?>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="driver_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $transport_data->driver_name ); }?>" name="driver_name">
								<label for="driver_name"><?php esc_html_e( 'Driver Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-margin-bottom-15px-res">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="driver_phone_num" class="form-control validate[required,custom[phone_number],minSize[6],maxSize[15]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $transport_data->driver_phone_num ); }?>" name="driver_phone_num">
								<label for="driver_phone_num"><?php esc_html_e( 'Driver Phone Number', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-note-text-notice mjschool-margin-bottom-15px-res mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea id="driver_address" name="driver_address" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="150" id="driver_address"> <?php if ( $edit ) { echo esc_textarea( $transport_data->driver_address ); } ?> </textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active" for="driver_address"><?php esc_html_e( 'Driver Address', 'mjschool' ); ?><span class="required">*</span></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-note-text-notice">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea id="route_description" name="route_description" class="mjschool-textarea-height-47px form-control" maxlength="120" id="route_description"><?php if ( $edit ) { echo esc_textarea( $transport_data->route_description ); } ?></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active" for="route_description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="route_fare" class="form-control validate[required,custom[onlyNumberSp],min[0],maxSize[10]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $transport_data->route_fare ); }?>" name="route_fare">
								<label for="route_fare"><?php esc_html_e( 'Route Fare', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control mjschool-upload-profile-image-patient mjschool-res-rtl-height-50px">
								<span class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-label-position-rtl" for="photo"><?php esc_html_e( 'Image Upload', 'mjschool' ); ?></span>
								<div class="col-sm-12 mjschool-display-flex">
									<input type="text" id="smgt_user_avatar_url" name="smgt_user_avatar" class="mjschool-image-path-dots" value="<?php if ( $edit ) { echo esc_url( $transport_data->smgt_user_avatar );}?>" readonly />
									<input id="upload_user_avatar_button" type="button" class="button mjschool-upload-image-btn mjschool-btn-top" value="<?php esc_html_e( 'Upload image', 'mjschool' ); ?>" />
								</div>
							</div>
							<div class="clearfix"></div>
							<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
								<div id="mjschool-upload-user-avatar-preview">
									<?php
									if ($edit) {
										if ($transport_data->smgt_user_avatar === "") {?>
											<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_driver_thumb_new' ) ) ?>">
										<?php } else { ?>
											<img class="mjschool-image-preview-css" src="<?php if ($edit) echo esc_url( $transport_data->smgt_user_avatar ); ?>" />
										<?php }
									} else {
										?>
										<img class="mjschool-image-preview-css" src="<?php echo esc_url( get_option( 'mjschool_driver_thumb_new' ) ) ?>">
										<?php
									}?>
								</div>
								
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			// --------- Get module-wise custom field data. --------------//
			$custom_field_obj = new Mjschool_Custome_Field();
			$module           = 'transport';
			$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Transport', 'mjschool' ); } else { esc_html_e( 'Add Transport', 'mjschool' ); } ?>" name="save_transport" class="btn btn-success mjschool-rtl-margin-0px mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!--------- Panel body. ------->
</div><!--------- Add transport div. ------->