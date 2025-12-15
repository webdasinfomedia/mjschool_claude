<?php
/**
 * Manage Tax Form (Admin Page).
 *
 * This file is responsible for rendering the "Add/Edit Tax" form in the MJSchool plugin's admin dashboard.
 * It enables administrators to create or update tax records used in financial transactions, such as invoices
 * and fees. The form ensures proper validation, data sanitization, and secure submission through WordPress
 * nonce verification.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/tax
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit    = 1;
	$taxdata = $obj_tax->mjschool_get_single_tax( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['tax_id'])) ) ) );
}
?>
<div class="mjschool-panel-body"><!-------- Panel body. -------->
	<form name="tax_form" action="" method="post" class="mjschool-form-horizontal" id="tax_form" enctype="multipart/form-data"><!------- form Start --------->
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="tax_id" value="<?php if ( $edit ) {  echo esc_attr( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['tax_id'] ) ) ) );  } ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Tax Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="tax_title" class="form-control validate[required,custom[popup_category_validation]]" maxlength="30" type="text" value="<?php if ( $edit ) { echo esc_attr( $taxdata->tax_title ); } elseif ( isset( $_POST['tax_title'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_POST['tax_title'] ) ) ); } ?>" name="tax_title">
							<label for="tax_title"><?php esc_html_e( 'Tax Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="tax" class="form-control validate[required,custom[number]] text-input" onkeypress="if(this.value.length==6) return false;" step="0.01" type="number" value="<?php if ( $edit ) { echo esc_attr( $taxdata->tax_value ); } elseif ( isset( $_POST['tax_value'] ) ) { echo esc_attr( floatval( wp_unslash( $_POST['tax_value'] ) ) ); } ?>" name="tax_value" min="0" max="100">
							<label  for="tax"><?php esc_html_e( 'Tax Value(%)', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_tax_admin_nonce' ); ?>
			</div>
		</div>
		<?php
		// --------- Get module-wise custom field data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'tax';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Tax', 'mjschool' ); } else { esc_html_e( 'Add Tax', 'mjschool' ); } ?>" name="save_tax" class="mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form> <!------- Form end. --------->
</div><!-------- Panel body. -------->