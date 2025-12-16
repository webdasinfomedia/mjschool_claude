<?php
/**
 * Admin Class Management Form.
 *
 * This file handles the creation and editing of class records within the Mjschool plugin.
 * It provides the admin interface to add new classes, edit existing ones, and manage related
 * attributes such as class name, numeric value, student capacity, and (for universities) academic year.
 *
 * Key Features:
 * - Supports both insert and edit actions for class entities.
 * - Dynamically adjusts form fields based on school type (e.g., university mode).
 * - Implements client-side validation via jQuery ValidationEngine.
 * - Integrates secure nonce verification for form submissions.
 * - Utilizes WordPress escaping and sanitization functions for security.
 * - Supports custom fields via the Mjschool_Custome_Field class.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/class
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
	// Verify nonce for edit action
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_action' ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'mjschool' ) );
	}
	$edit      = 1;
	$class_id  = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) );
	$classdata = mjschool_get_class_by_id( $class_id );
}
?>
<div class="mjschool-panel-body"><!-------- Panel body. -------->
	<form name="class_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="class_form"><!------- form Start --------->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="class_name_for_class" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_name ); } ?>" name="class_name">
							<label for="class_name_for_class"><?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php if ( $school_type === 'university' ) {?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea name="class_description" class="mjschool-textarea-height-47px form-control" maxlength="150"><?php if ( $edit ) { echo esc_textarea( $classdata->class_description ); } ?></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="class_num_name" class="form-control validate[required,min[1],maxSize[4]] text-input" oninput="this.value = Math.abs(this.value)" type="number" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_num_name ); } ?>" name="class_num_name">
							<label for="class_num_name"><?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_class_admin_nonce' ); ?>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="class_capacity" oninput="this.value = Math.abs(this.value)" class="form-control validate[required,min[0],maxSize[4]]" type="number" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_capacity ); } ?>" name="class_capacity">
							<label for="class_capacity"><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php
				if ( $school_type === 'university' ) { 
					$current_year = date( 'Y' );
					$selected_academic_year = $edit ? $classdata->academic_year : '';
					?>
					<div class="col-md-6 input">
						<label for="academic_year" class="mjschool-custom-top-label mjschool-lable-top top"><?php esc_html_e( 'Academic Year', 'mjschool' ); ?><span class="required">*</span></label>
						<select name="academic_year" id="academic_year" class="form-control validate[required]">
							<option value=""><?php esc_html_e( 'Select Academic Year', 'mjschool' ); ?></option>
							<?php for ( $i = 0; $i <= 5; $i++ ) {
								$start_year = $current_year + $i;
								$end_year = $start_year + 1;
								$year_range = $start_year . ' - ' . $end_year;
								?>
								<option value="<?php echo esc_attr( $year_range ); ?>" <?php selected( $selected_academic_year, $year_range ); ?>>
									<?php echo esc_html( $year_range ); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
		// --------- Get Module Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'class';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Class', 'mjschool' ); } else { esc_attr_e( 'Add Class', 'mjschool' ); } ?>" name="save_class" class="mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form> <!------- form end. --------->
</div><!-------- Panel body. -------->