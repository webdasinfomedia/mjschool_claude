<?php
/**
 * Add/Edit Fee Type Form.
 *
 * Handles the display and submission of the Fee Type form in the MJSchool plugin.
 * Provides functionality to add a new fee type or edit an existing one, including:
 * - Selecting fee type and class.
 * - Optional class section selection (for school type).
 * - Entering fee amount and description.
 * - Integration with custom fields for the fee_pay module.
 *
 * The form validates required fields and uses WordPress nonces for secure submission.
 *
 *
 * @since      1.0.0
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/feespayment
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
if ( $active_tab === 'addfeetype' ) {
	$fees_id = 0;
	if ( isset( $_REQUEST['fees_id'] ) ) {
		$fees_id = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['fees_id'] ) ) );
	}
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$edit   = 1;
		$result = $mjschool_obj_fees->mjschool_get_single_feetype_data( $fees_id );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!----- Panel Body. --------->
		<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="fees_id" value="<?php echo esc_attr( $fees_id ); ?>">
			<input type="hidden" name="invoice_type" value="expense">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-4 input">
						<label class="ml-1 mjschool-custom-top-label top" for="category_data">
							<?php esc_html_e( 'Fee Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span>
						</label>
						<select class="form-control validate[required] smgt_feetype mjschool-max-width-100px" name="fees_title_id" id="category_data">
							<option value=""><?php esc_html_e( 'Select Fee Type', 'mjschool' ); ?></option>
							<?php
							$activity_category = mjschool_get_all_category( 'smgt_feetype' );
							if ( ! empty( $activity_category ) ) {
								if ( $edit ) {
									$fees_val = $result->fees_title_id;
								} else {
									$fees_val = '';
								}
								foreach ( $activity_category as $retrive_data ) {
									?>
									<option value="<?php echo esc_attr( $retrive_data->ID ); ?>" <?php selected( $retrive_data->ID, $fees_val ); ?>>
										<?php echo esc_html( $retrive_data->post_title ); ?>
									</option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="col-sm-2 mjschool-padding-bottom-15px-res">
						<button id="mjschool-addremove-cat" class="mjschool-rtl-margin-top-15px btn btn-info mjschool-add-btn" model="smgt_feetype">
							<?php esc_html_e( 'Add', 'mjschool' ); ?>
						</button>
					</div>
					<div class="col-md-6 input mjschool-error-msg-left-margin">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						$classval = 0;
						if ( $edit ) {
							$classval = $result->class_id;
						}
						?>
						<select name="class_id" class="form-control validate[required] mjschool-max-width-100px" id="mjschool-class-list">
							<option value=""><?php esc_attr_e( 'Select Class', 'mjschool' ); ?></option>
							<option value="all_class" <?php selected( $classval, 'all_class' ); ?>><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>>
									<?php echo esc_html( $classdata['class_name'] ); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<?php wp_nonce_field( 'save_fees_type_admin_nonce' ); ?>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$sectionval = $result->section_id;
							} elseif ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field( wp_unslash($_POST['class_section']));
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="form-control mjschool-max-width-100px" id="class_section">
								<option value=""><?php esc_attr_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>>
											<?php echo esc_html( $sectiondata->section_name ); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php } ?>
					<div class="col-md-6 mjschool-error-msg-left-margin">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->fees_amount ); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['fees_amount'])) ); } ?>" name="fees_amount">
								<label for="fees_amount">
									<?php esc_html_e( 'Fees Amount', 'mjschool' ); ?>( <?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span>
								</label>
							</div>
						</div>
					</div>
					<div class="col-md-6 mjschool-note-text-notice">
						<div class="form-group input">
							<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
								<div class="form-field">
									<textarea id="mjschool-description" name="description" class="mjschool-textarea-height-47px form-control" maxlength="150"><?php if ( $edit ) { echo esc_textarea( $result->description ); } elseif ( isset( $_POST['description'] ) ) { echo esc_textarea( sanitize_text_field( wp_unslash($_POST['description'])) ); } ?></textarea>
									<span class="mjschool-txt-title-label"></span>
									<label class="text-area address active" for="mjschool-description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			// --------- Get Module Wise Custom Field Data. --------------//
			$custom_field_obj = new Mjschool_Custome_Field();
			$module           = 'fee_pay';
			$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
			?>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Fee Type', 'mjschool' ); } else { esc_attr_e( 'Create Fee Type', 'mjschool' ); } ?>" name="save_feetype" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!----- Panel Body. --------->
	<?php
}
?>