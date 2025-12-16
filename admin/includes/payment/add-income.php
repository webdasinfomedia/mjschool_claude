<?php
/**
 * Income Form Template.
 *
 * This file renders the "Add/Edit Income" form in the admin area of the MJSchool plugin.
 * It enables administrators to create and manage income records, assign them to students or classes,
 * specify payment details, and apply tax configurations.
 *
 * Key Features:
 * - Allows administrators to create or edit income entries securely.
 * - Supports linking income to specific students, classes, and sections.
 * - Implements client-side form validation with jQuery Validation Engine.
 * - Integrates a datepicker for selecting the invoice date with the configured format.
 * - Includes dynamic add/remove functionality for multiple income entries.
 * - Supports tax assignment through a multiselect dropdown.
 * - Handles both "Add Income" and "Edit Payment" actions based on the request.
 * - Ensures security using WordPress nonces and proper sanitization.
 * - Fetches module-specific custom fields for extended data input.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
$mjschool_obj_invoice = new Mjschool_Invoice();
if ( $active_tab === 'addincome' ) {
	$income_id = 0;
	if ( isset( $_REQUEST['income_id'] ) ) {
		$income_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['income_id'])) ) );
	}
	$edit = 0;
	$action = isset($_REQUEST['action']) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : '';
	if ( $action === 'edit' ) {
		$edit   = 1;
		$result = $mjschool_obj_invoice->mjschool_get_income_data( $income_id );
	} elseif ( $action === 'edit_payment' ) {
		$edit   = 1;
		$result = mjschool_get_payment_by_id( $income_id );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel Body. --------->
		<form name="income_form" action="" method="post" class="mjschool-form-horizontal" id="income_form" enctype="multipart/form-data">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<input type="hidden" name="income_id" value="<?php echo esc_attr( $income_id ); ?>">
			<input type="hidden" name="invoice_type" value="income">
			<div class="form-body mjschool-user-form"><!--------- Form Body. --------->
				<div class="row"><!--------- Row Div. --------->
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						if ( $edit ) {
							$classval = $result->class_id;
						} else {
							$classval = '';
						}
						?>
						<select name="class_id" id="mjschool-class-list" class="form-control validate[required] mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$sectionval = $result->section_id;
							} elseif ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="form-control mjschool-max-width-100px" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $result->class_id ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php }?>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						if ( $edit ) {
							$classval = $result->class_id;
						} else {
							$classval = '';
						}
						?>
						<select name="supplier_name" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
							<?php
							if ( isset( $result->supplier_name ) ) {
								$student = get_userdata( $result->supplier_name );
								?>
								<option value="<?php echo esc_attr( $result->supplier_name ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $result->supplier_name ) ); ?></option>
								<?php
							} elseif ( isset( $result->student_id ) ) {
								$student = get_userdata( $result->student_id );
								?>
								<option value="<?php echo esc_attr( $result->student_id ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $result->student_id ) ); ?></option>
								<?php
							} else {
								?>
								<option value=""><?php esc_html_e( 'Select student', 'mjschool' ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="payment_status"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
						<select name="payment_status" id="payment_status" class="form-control validate[required] mjschool-max-width-100px">
							<option value="Paid" <?php if ( $edit ) { selected( 'Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Paid', 'mjschool' ); ?></option>
							<option value="Part Paid" <?php if ( $edit ) { selected( 'Part Paid', $result->payment_status );} ?> ><?php esc_html_e( 'Part Paid', 'mjschool' ); ?></option>
							<option value="Unpaid" <?php if ( $edit ) { selected( 'Unpaid', $result->payment_status );} ?> ><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></option>
						</select>
					</div>
					<?php wp_nonce_field( 'save_income_fees_admin_nonce' ); ?>
					<div class="col-md-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="invoice_date" class="form-control " type="text" value="<?php if ( $edit ) { if ( isset( $result->income_create_date ) ) { echo esc_attr( mjschool_get_date_in_input_box( $result->income_create_date ) ); } elseif ( isset( $result->date ) ) { echo esc_attr( mjschool_get_date_in_input_box( $result->date ) ); } } elseif ( isset( $_POST['invoice_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['invoice_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); }?>" name="invoice_date" readonly>
								<label for="invoice_date"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-bottom-0px mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
						<select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
							<?php
							if ( $edit ) {
								if ( $result->tax !== null ) {
									$tax_id = explode( ',', $result->tax );
								} else {
									$tax_id[] = '';
								}
							} else {
								$tax_id[] = '';
							}
							$mjschool_obj_tax = new Mjschool_Tax_Manage();
							$smgt_taxs        = $mjschool_obj_tax->mjschool_get_all_tax();
							if ( ! empty( $smgt_taxs ) ) {
								foreach ( $smgt_taxs as $data ) {
									$selected = '';
									if ( in_array( $data->tax_id, $tax_id ) ) {
										$selected = 'selected';
									}
									?>
									<option value="<?php echo esc_attr( $data->tax_id ); ?>" <?php echo esc_html( $selected ); ?>><?php echo esc_html( $data->tax_title ); ?> - <?php echo esc_html( $data->tax_value ); ?></option>
									<?php
								}
							}
							?>
						</select>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="tax_id"><?php esc_html_e( 'Select Tax', 'mjschool' ); ?></label>
						</span>
					</div>
				</div><!--------- Row Div. --------->
			</div><!--------- Form Body. --------->
			<hr>
			<div class="header">
				<h3 class="mjschool-first-header mjschool-margin-top-0px-image"><?php esc_html_e( 'Income Entry', 'mjschool' ); ?></h3>
			</div>
			<div id="income_entry_main">
				<?php
				if ( $edit ) {
					if ( isset( $result->entry ) ) {
						$all_entry = json_decode( $result->entry );
					} else {
						$payment_title          = $result->payment_title;
						$payment_amount         = $result->fees_amount;
						$payment_object         = new stdClass();
						$payment_object->entry  = $payment_title;
						$payment_object->amount = $payment_amount;
						$all_entry              = array();
						$all_entry[]            = $payment_object;
					}
				} elseif ( isset( $_POST['income_entry'] ) ) {
					$all_data  = $mjschool_obj_invoice->mjschool_get_entry_records( wp_unslash($_POST) );
					$all_entry = json_decode( $all_data );
				}
				if ( ! empty( $all_entry ) ) {
					$i = 0;
					foreach ( $all_entry as $entry ) {
						?>
						<div id="income_entry">
							<div class="form-body mjschool-user-form mjschool-income-feild">
								<div class="row">
									<div class="col-md-3">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="income_amount" class="form-control validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="<?php echo esc_attr( $entry->amount ); ?>" name="income_amount[]">
												<label for="income_amount"><?php esc_html_e( 'Income Amount', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<div class="col-md-3 col-9">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="<?php echo esc_attr( $entry->entry ); ?>" name="income_entry[]">
												<label for="income_entry"><?php esc_html_e( 'Income Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
											</div>
										</div>
									</div>
									<?php
									
									if ($i === 0) {
										?>
										<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
										</div>
										<?php
									} else {
										?>
										<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
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
				} else { ?>
					<div id="income_entry">
						<div class="form-body mjschool-user-form mjschool-income-feild">
							<div class="row">
								<div class="col-md-3">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="income_amount" class="form-control mjschool-btn-top validate[required,min[0],maxSize[8]] text-input" type="number" step="0.01" value="" name="income_amount[]">
											<label for="income_amount"><?php esc_html_e( 'Income Amount', 'mjschool' ); ?><span class="required">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-3 col-9">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input id="income_entry" class="form-control mjschool-btn-top validate[required,custom[onlyLetter_specialcharacter]] text-input" maxlength="50" type="text" value="" name="income_entry[]">
											<label for="income_entry"><?php esc_html_e( 'Income Entry Label', 'mjschool' ); ?><span class="required">*</span></label>
										</div>
									</div>
								</div>
								<div class="col-md-2 col-3 mjschool-symptoms-dropdown-div">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-add-new-plus-btn.png"); ?>" onclick="mjschool_add_entry()" name="add_new_entry" class="mjschool-rtl-margin-top-15px mjschool-daye-name-onclick" id="add_new_entry">
								</div>
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
			$module                    = 'income';
			$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
			?>
			<hr>
			<div class="form-body mjschool-user-form mjschool-income-feild">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Income', 'mjschool' ); } else { esc_html_e( 'Create Income Entry', 'mjschool' ); }?>" name="save_income" class="btn btn-success mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!--------- Panel Body. --------->
	<?php
}
?>