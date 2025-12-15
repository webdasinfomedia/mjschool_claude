<?php
/**
 * Grade Management Form (Add/Edit).
 *
 * This file handles the admin-side grade creation and editing functionality for the MJSchool plugin.  
 * It provides a form interface for adding or updating grade records with fields such as grade name,  
 * grade point, mark range, and comments. The form also integrates WordPress nonce verification for  
 * security and uses client-side validation for input sanitization.
 *
 * Key Features:
 * - Supports both add and edit operations using a single form.
 * - Includes validation for numeric and text fields.
 * - Uses nonce for CSRF protection.
 * - Dynamically loads grade data during edit mode.
 * - Supports custom fields added through the MJSchool Custom Field module.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/grade
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit       = 1;
	$grade_data = mjschool_get_grade_by_id( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['grade_id']) ) ) );
}
?>
<div class="mjschool-panel-body mt-5 mjschool-padding-top-25px-res"><!-------- Panel body. -------->
	<form name="grade_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="grade_form">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="grade_name" class="form-control validate[required,custom[address_description_validation]]" type="text" value="<?php if ( $edit ) { echo esc_attr( $grade_data->grade_name ); } ?>" maxlength="50" name="grade_name">
							<label for="grade_name"><?php esc_html_e( 'Grade Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="grade_point" class="form-control validate[required,max[100]] text-input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->grade_point ); } ?>" name="grade_point" step="any">
							<label for="grade_point"><?php esc_html_e( 'Grade Point', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_grade_admin_nonce' ); ?>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="mark_from" class="form-control validate[required,max[100]] text-input mark_from_input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->mark_upto ); } ?>" name="mark_upto" step="any">
							<label for="mark_from"><?php esc_html_e( 'Mark From', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="mark_upto" class="form-control validate[required,max[100]] text-input mark_upto_input" type="number" value="<?php if ( $edit ) { echo esc_attr( $grade_data->mark_from ); } ?>" name="mark_from" step="any">
							<label for="mark_upto"><?php esc_html_e( 'Mark Upto', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="grade_comment" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150" id="grade_comment"><?php if ( $edit ) { echo esc_attr( $grade_data->grade_comment ); } ?></textarea>
								<span class="mjschool-txt-title-label"></span><label for="grade_comment" class="text-area address active"><?php esc_html_e( 'Comment', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		// --------- Get Module-Wise Custom Field Data. --------------//
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$module                    = 'grade';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Grade', 'mjschool' ); } else { esc_html_e( 'Add Grade', 'mjschool' ); } ?>" name="save_grade" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!-------- Panel body. -------->
