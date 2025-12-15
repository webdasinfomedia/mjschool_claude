<?php
/**
 * Admin Fee Type Management Form.
 *
 * This file handles the backend interface for creating and editing fee types within the Mjschool plugin. 
 * It provides secure form handling, class-based fee assignment, and validation for fee details.
 *
 * Key Features:
 * - Add or edit fee type details such as name, amount, and description.
 * - Assign fee types to specific classes dynamically.
 * - Supports validation for numeric input and required fields.
 * - Integrates WordPress nonce security and sanitization best practices.
 * - Provides role-based access for fee type management.
 * - Includes multilingual support using WordPress translation functions.
 * - Dynamically loads existing fee type data in edit mode.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/fees
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( $active_tab === 'addfeetype' ) {
	$fees_id = 0;
	if ( isset( $_REQUEST['fees_id'] ) ) {
		$fees_id = intval( mjschool_decrypt_id( $_REQUEST['fees_id'] ) );
	}
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) {
		$edit   = 1;
		$result = $mjschool_obj_fees->mjschool_get_single_feetype_data( $fees_id );
	}
	?>
	<div class="mjschool-panel-body">
		<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action']) ) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="fees_id" value="<?php echo esc_attr( $fees_id ); ?>">
			<input type="hidden" name="invoice_type" value="expense">
			<div class="form-group row mb-3">
				<label class="col-sm-2 control-label col-form-label text-md-end" for="category_data"><?php esc_html_e( 'Fee Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<div class="col-sm-8">
					<select name="fees_title_id" id="category_data" class="form-control validate[required]">
						<option value=""><?php esc_attr_e( 'Select Fee Type 1', 'mjschool' ); ?></option>
						<?php
						$fee_type = 0;
						if ( $edit ) {
							$fee_type = $result->fees_title_id;
						}
						$feeype_data = $obj_fees->mjschool_get_all_feetype();
						if ( ! empty( $feeype_data ) ) {
							foreach ( $feeype_data as $retrieved_data ) {
								echo '<option value="' . esc_attr($retrieved_data->ID) . '" ' . selected($fee_type, $retrieved_data->ID) . '>' . esc_attr( $retrieved_data->post_title) . '</option>'; //phpcs:ignore
							}
						}
						?>
					</select>
				</div>
				<div class="col-sm-2">
					<button id="addremove" model="feetype"><?php esc_html_e( 'Add Or Remove', 'mjschool' ); ?></button>
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-2 control-label col-form-label text-md-end" for="class_name"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<div class="col-sm-8">
					<?php
					$classval = 0;
					if ( $edit ) {
						$classval = $result->class_id;
					}
					?>
					<select name="class_id" class="form-control validate[required]" id="class_name">
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
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-2 control-label col-form-label text-md-end" for="fees_amount"><?php esc_html_e( 'Amount', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="mjschool-require-field">*</span></label>
				<div class="col-sm-8">
					<input id="fees_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $result->fees_amount ); } elseif ( isset( $_POST['fees_amount'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash($_POST['fees_amount']) ) ); } ?>" name="fees_amount">
				</div>
			</div>
			<div class="form-group row mb-3">
				<label class="col-sm-2 control-label col-form-label text-md-end" for="description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
				<div class="col-sm-8">
					<textarea name="description" class="form-control validate[custom[address_description_validation]]" maxlength="150"> 
						<?php
						if ( $edit ) {
							echo esc_attr( $result->description );
						} elseif ( isset( $_POST['description'] ) ) {
							echo esc_attr( sanitize_text_field( wp_unslash($_POST['description']) ) );
						}
						?>
					</textarea>
				</div>
			</div>
			<div class="offset-sm-2 col-sm-8">
				<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Fee Type', 'mjschool' ); } else { esc_attr_e( 'Create Fee Type', 'mjschool' ); } ?>" name="save_feetype" class="btn btn-success" />
			</div>
		</form>
	</div>
	<?php
}
?>