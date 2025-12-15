<?php
/**
 * Admin: Add/Edit Expense Page.
 *
 * This file manages the creation and editing of expense entries within the MJSchool plugin.
 * It provides an intuitive admin interface for recording and managing expense data such as
 * supplier name, payment status, and itemized expense entries.
 *
 * Key Features:
 * - Allows administrators to add or edit expense records securely.
 * - Supports multiple expense entry fields with dynamic add/remove functionality.
 * - Implements form validation using jQuery Validation Engine.
 * - Uses WordPress datepicker with configurable date format settings.
 * - Ensures secure form submissions using nonces and data sanitization.
 * - Loads and saves existing expense data for editing, including JSON-encoded entry items.
 * - Integrates with the custom fields module for extended data storage.
 * - Provides responsive and accessible form layouts compatible with the admin interface.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$mjschool_obj_invoice = new Mjschool_Invoice(); ?>
<?php
if ( $active_tab === 'addexpense' ) {
	$expense_id = 0;
	if ( isset( $_REQUEST['expense_id'] ) ) {
		$expense_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['expense_id'])) ) );
	}
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$edit   = 1;
		$result = $mjschool_obj_invoice->mjschool_get_income_data( $expense_id );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel Body. --------->
		<form name="expense_form" action="" method="post" class="mjschool-form-horizontal" id="expense_form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="expense_id" value="<?php echo esc_attr( $expense_id ); ?>">
			<input type="hidden" name="invoice_type" value="expense">	
			<div class="form-body mjschool-user-form"><!--------- Form Body. --------->
				<div class="row"><!--------- Row Div. --------->
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="supplier_name" class="form-control validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->supplier_name ); } elseif ( isset( $_POST['supplier_name'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['supplier_name'])) ); } ?>" name="supplier_name">
								<label for="supplier_name"><?php esc_html_e( 'Supplier Name', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="payment_status"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
						<select name="payment_status" id="payment_status" class="form-control validate[required] mjschool-max-width-100px">
							<option value="Paid" <?php if ( $edit ) { selected( 'Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Paid', 'mjschool' ); ?></option>
							<option value="Part Paid" <?php if ( $edit ) { selected( 'Part Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Part Paid', 'mjschool' ); ?></option>
							<option value="Unpaid" <?php if ( $edit ) { selected( 'Unpaid', $result->payment_status );} ?> ><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="invoice_date" class="form-control validate[required]" type="text" value="<?php if ( $edit ) { echo esc_attr( $result->income_create_date ); } elseif ( isset( $_POST['invoice_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['invoice_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="invoice_date" readonly>
								<label for="invoice_date"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
				</div><!--------- Row Div. --------->
			</div><!--------- Form Body. --------->
			<hr>
			<div id="expense_entry_main">
				<?php
				if ( $edit ) {
					$all_entry = json_decode( $result->entry );
				} elseif ( isset( $_POST['income_entry'] ) ) {
					$all_data  = $mjschool_obj_invoice->mjschool_get_entry_records( wp_unslash($_POST) );
					$all_entry = json_decode( $all_data );
				}
				if ( ! empty( $all_entry ) ) {
					$i = 0;
					foreach ( $all_entry as $entry ) {
						?>
						<div id="expense_entry">
							<div class="form-body mjschool-user-form mjschool-income-feild">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="income_amount" class="form-control mjschool-btn-top amt validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php echo esc_attr( $entry->amount ); ?>" name="income_amount[]">
												<label for="income_amount"><?php esc_html_e( 'Expense Amount', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-3">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="income_entry" class="form-control entry mjschool-btn-top validate[required,custom[description_validation]] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $entry->entry ); ?>" name="income_entry[]">
												<label for="income_entry"><?php esc_html_e( 'Expense Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<?php 
									if ($i === 0) {
										?>
										<div class="col-md-2 mjschool-symptoms-dropdown-div">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
										</div>
										<?php
									} else {
										?>
										<div class="col-md-2 mjschool-symptoms-dropdown-div">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>" onclick="mjschool_delete_parent_element(this)" class="mjschool-rtl-margin-top-15px">
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php
						$i++;
					}
				} else {
					?>
					<div id="expense_entry">
						<div class="form-body mjschool-user-form mjschool-income-feild">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">
											<label for="income_amount"><?php esc_html_e( 'Expense Amount', 'mjschool' ); ?><span class="required">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[description_validation]] text-input" maxlength="50" type="text" value="" name="income_entry[]">
											<label for="income_entry"><?php esc_html_e( 'Expense Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-2 mjschool-symptoms-dropdown-div">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
								</div>
							</div>
						</div>
					</div>
					<?php 
				}
				?>
			</div>
			<?php wp_nonce_field( 'save_expense_fees_admin_nonce' ); ?>
			<?php
			// --------- Get Module-Wise Custom Field Data. --------------//
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'expense';
			$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
			?>
			<hr>
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Expense', 'mjschool' ); } else { esc_html_e( 'Create Expense Entry', 'mjschool' ); } ?>" name="save_expense" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!--------- Form Body. --------->
	<?php
}
?>
