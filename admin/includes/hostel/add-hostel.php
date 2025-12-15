<?php
/**
 * Admin Hostel Management Form.
 *
 * This file provides the backend interface for adding and editing hostel details
 * within the MJSchool plugin. Administrators can manage hostel names, types, capacity,
 * addresses, and descriptions. It also supports integration with the MJSchool custom
 * field module and includes nonce-based security for form submissions.
 *
 * Key Features:
 * - Add or edit hostel information such as name, type, capacity, and address.
 * - Provides form validation for required fields.
 * - Uses WordPress nonces for secure form handling.
 * - Integrates with the Mjschool_Custome_Field class for module-specific custom fields.
 * - Supports localization and translation through WordPress functions.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hostel
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$obj_hostel = new Mjschool_Hostel();
$edit       = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit        = 1;
	$hostel_id   = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hostel_id'])) ) );
	$hostel_data = $obj_hostel->mjschool_get_hostel_by_id( $hostel_id );
}
?>
<div class="mjschool-panel-body"><!-- start mjschool-panel-body. -->
	<form name="hostel_form" action="" method="post" class="mjschool-form-horizontal" id="hostel_form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="hostel_id" value="<?php if ( $edit ) { echo esc_attr( $hostel_id );} ?>"/> 
		<div class="header">	
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Hostel Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form"> <!--Card Body div.-->   
			<div class="row"><!--Row Div.--> 
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hostel_name" class="form-control validate[required,custom[popup_category_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $hostel_data->hostel_name );} ?>" name="hostel_name">
							<label  for="hostel_name"><?php esc_html_e( 'Hostel Name', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hostel_type" class="form-control validate[required,custom[popup_category_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $hostel_data->hostel_type );} ?>" name="hostel_type">
							<label  for="hostel_type"><?php esc_html_e( 'Hostel Type', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hostel_address" class="form-control validate[custom[popup_category_validation]] text-input" maxlength="250" type="text" value="<?php if ( $edit ) { echo esc_attr( $hostel_data->hostel_address );} ?>" name="hostel_address">
							<label  for="hostel_type"><?php esc_html_e( 'Hostel Address', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hostel_intake" class="form-control validate[custom[popup_category_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $hostel_data->hostel_intake );} ?>" name="hostel_intake">
							<label  for="hostel_intake"><?php esc_html_e( 'Intake/Capacity', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_hostel_admin_nonce' ); ?>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="Description" id="Description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[description_validation]]"> <?php if ( $edit ) { echo esc_attr( $hostel_data->Description );} ?></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="Description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// Get Module-Wise Custom Field Data.
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'hostel';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Hostel', 'mjschool' ); } else { esc_attr_e( 'Add Hostel', 'mjschool' );} ?>" name="save_hostel" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!-- End mjschool-panel-body. -->