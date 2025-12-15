<?php
/**
 * Payment Form Template.
 *
 * This file handles the "Add/Edit Payment" form in the admin area of the MJSchool plugin.
 * It allows administrators to record, update, and manage student payments, including
 * class-wise or section-wise fee assignments and tax configurations.
 *
 * Key Features:
 * - Supports both "Add Payment" and "Edit Payment" operations.
 * - Dynamically loads class, section, and student data via WordPress functions.
 * - Enables selection of multiple tax charges through a multiselect dropdown.
 * - Validates inputs using jQuery Validation Engine with prompt positioning and error control.
 * - Integrates nonce verification for secure form submissions.
 * - Fetches and displays related student data, including names and roll numbers.
 * - Provides structured UI with responsive layout and form grouping.
 * 
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/payment
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit         = 1;
	$payment_data = mjschool_get_payment_by_id( sanitize_text_field(wp_unslash($_REQUEST['payment_id'])) );
}
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel Body. --------->
	<form name="payment_form" action="" method="post" class="mjschool-form-horizontal" id="payment_form">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="payment_id" value="<?php if ( $edit ) { echo esc_attr( $payment_data->payment_id );} ?>" />
		<div class="form-body mjschool-user-form"><!--------- Form Body. --------->
			<div class="row"><!--------- Row Div. --------->
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="payment_title" class="form-control validate[required,custom[popup_category_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $payment_data->payment_title ); } ?>" name="payment_title" />
							<label for="userinput1"><?php esc_html_e( 'Title', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( $edit ) {
						$classval = $payment_data->class_id;
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
				<div class="col-md-6 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
					<?php
					if ( $edit ) {
						$sectionval = $payment_data->section_id;
					} elseif ( isset( $_POST['class_section'] ) ) {
						$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
					} else {
						$sectionval = '';
					}
					?>
					<select name="class_section" class="form-control mjschool-max-width-100px" id="class_section">
						<option value=""><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></option>
						<?php
						if ( $edit ) {
							foreach ( mjschool_get_class_sections( $payment_data->class_id ) as $sectiondata ) {
								?>
								<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-md-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Student', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<?php
					if ( $edit ) {
						$classval = $payment_data->class_id;
					} else {
						$classval = '';
					}
					?>
					<select name="student_id" id="student_list" class="form-control validate[required] mjschool-max-width-100px">
						<?php
						if ( isset( $payment_data->student_id ) ) {
							$student = get_userdata( $payment_data->student_id );
							?>
							<option value="<?php echo esc_attr( $payment_data->student_id ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $payment_data->student_id ) ); ?></option>
						<?php } else { ?>
							<option value=""><?php esc_html_e( 'Select student', 'mjschool' ); ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="amount" class="form-control validate[required,min[0],maxSize[12]]" type="number" step="0.01" value="<?php if ( $edit ) { echo esc_attr( $payment_data->fees_amount ); } ?>" name="amount">
							<label for="userinput1"><?php esc_html_e( 'Amount', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)<span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="mjschool-rtl-margin-top-15px col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-bottom-0px mb-3 mjschool-multiselect-validation-member mjschool-multiple-select">
					<select class="form-control tax_charge" id="tax_id" name="tax[]" multiple="multiple">
						<?php
						if ( $edit ) {
							if ( $payment_data->tax !== null ) {
								$tax_id = explode( ',', $payment_data->tax );
							} else {
								$tax_id[] = '';
							}
						} else {
							$tax_id[] = '';
						}
						$obj_tax   = new Mjschool_Tax_Manage();
						$smgt_taxs = $obj_tax->mjschool_get_all_tax();
						if ( ! empty( $smgt_taxs ) ) {
							foreach ( $smgt_taxs as $data ) {
								$selected = '';
								if ( in_array( $data->tax_id, $tax_id ) ) {
									$selected = 'selected';
								}
								?>
								<option value="<?php echo esc_attr( $data->tax_id ); ?>" <?php echo esc_html( $selected ); ?>>
									<?php echo esc_html( $data->tax_title ); ?> - <?php echo esc_html( $data->tax_value ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<?php wp_nonce_field( 'save_payment_admin_nonce' ); ?>
				<div class="col-md-6 input mjschool-error-msg-left-margin">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
					<select name="payment_status" id="payment_status" class="form-control mjschool-max-width-100px">
						<option value="Paid" <?php if ( $edit ) { selected( 'Paid', $payment_data->payment_status );} ?> class="validate[required]"><?php esc_html_e( 'Paid', 'mjschool' ); ?></option>
						<option value="Part Paid" <?php if ( $edit ) { selected( 'Part Paid', $payment_data->payment_status );} ?> class="validate[required]"><?php esc_html_e( 'Part Paid', 'mjschool' ); ?></option>
						<option value="Unpaid" <?php if ( $edit ) { selected( 'Unpaid', $payment_data->payment_status );} ?> class="validate[required]"><?php esc_html_e( 'Unpaid', 'mjschool' ); ?></option>
					</select>
				</div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea name="description" id="description" class="mjschool-textarea-height-47px form-control validate[custom[address_description_validation]]" maxlength="150"><?php if ( $edit ) { echo esc_attr( $payment_data->description ); } ?></textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
				</div>
			</div><!--------- Row Div. --------->
		</div><!--------- Form Body. --------->
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Payment', 'mjschool' ); } else { esc_html_e( 'Add Payment', 'mjschool' ); } ?>" name="save_payment" class="btn btn-success mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!--------- Table-responsive. --------->