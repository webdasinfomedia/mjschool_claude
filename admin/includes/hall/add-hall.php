<?php
/**
 * Admin Add Exam Hall Interface.
 *
 * Handles backend functionality for adding, editing, exam halls 
 * within the MJSchool plugin. This interface provides administrators with tools to 
 * manage hall records including capacity, numbering, and descriptions.
 *
 * Key Features:
 * - Securely adds and updates exam hall records using WordPress nonces.
 * - Implements client-side validation for numeric and text inputs.
 * - Supports dynamic editing of existing hall details.
 * - Integrates module-based custom fields for extended functionality.
 * - Sanitizes and escapes user inputs to prevent security vulnerabilities.
 * - Provides a responsive and user-friendly admin interface.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hall
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit      = 1;
	$hall_data = mjschool_get_hall_by_id( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['hall_id'])) ) ) );
}
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!-------- Panel Body -------->
	<form name="hall_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="hall_form">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="hall_id" value="<?php if ( $edit ) { echo esc_attr( $hall_data->hall_id ); } ?>" /> 
		<div class="form-body mjschool-user-form"><!-------- Form Body. -------->
			<div class="row"><!-------- Row Div. -------->
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hall_name" class="form-control validate[required,custom[popup_category_validation]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( stripslashes( $hall_data->hall_name ) ); } ?>" name="hall_name">
							<label for="hall_name"><?php esc_html_e( 'Hall Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="number_of_hall" class="form-control validate[required,custom[onlyNumberSp]]" maxlength="5" type="text" value="<?php if ( $edit ) { echo esc_attr( $hall_data->number_of_hall ); } ?>" name="number_of_hall">              
							<label for="number_of_hall"><?php esc_html_e( 'Hall Numeric Value', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_hall_admin_nonce' ); ?>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="hall_capacity" class="form-control validate[required,custom[onlyNumberSp]]" maxlength="5" type="text" value="<?php if ( $edit ) { echo esc_attr( $hall_data->hall_capacity ); } ?>" name="hall_capacity">               
							<label for="hall_capacity"><?php esc_html_e( 'Hall Capacity', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="description" id="description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]"><?php if ( $edit ) { echo esc_textarea( stripslashes( $hall_data->description ) ); } ?></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label for="description" class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div><!-------- Row Div. -------->
		</div><!-------- Form Body. -------->
		<?php
		// --------- Get Module-Wise Custom Field Data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'examhall';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">        	
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Hall', 'mjschool' ); } else { esc_html_e( 'Add Hall', 'mjschool' ); } ?>" name="save_hall" class="mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!-------- Panel Body. -------->