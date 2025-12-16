<?php
/**
 * Admin Add Holiday Form.
 *
 * This file renders and processes the Holiday creation and editing form within the MJSchool plugin.
 * It provides administrators with the ability to add, edit, approve, or disapprove holidays,
 * as well as send email or SMS notifications to users when a new holiday is added.
 *
 * Key Features:
 * - Handles both 'Add' and 'Edit' actions for holidays.
 * - Uses WordPress nonces for form security.
 * - Implements validation and sanitization for safe user input.
 * - Supports optional email and SMS notifications on new holidays.
 * - Integrates custom field management through the Mjschool_Custome_Field class.
 * - Provides approval status options for holidays.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/holiday
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit         = 1;
	$holiday_id   = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['holiday_id']) ) );
	$holiday_data = mjschool_get_holiday_by_id( $holiday_id );
}
?>
<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
	<form name="holiday_form" action="" method="post" class="mjschool-form-horizontal" id="holiday_form" enctype="multipart/form-data"> 
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="holiday_id" value=" <?php if ( $edit ) { echo esc_attr( $holiday_id );} ?>"/>
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Holiday Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="mjschool_holiday_title" class="form-control validate[required,custom[description_validation]] text-input" maxlength="100" type="text" value="<?php if ( $edit ) { echo esc_attr( $holiday_data->holiday_title );} ?>" name="holiday_title">
							<label for="mjschool_holiday_title"><?php esc_html_e( 'Holiday Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="holiday_description" class="form-control validate[custom[description_validation]]" maxlength="1000" type="text" value="<?php if ( $edit ) { echo esc_attr( $holiday_data->description );} ?>" name="description">
							<label  for="holiday_description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="date" class="form-control date_picker validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $holiday_data->date ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="date" readonly>
							<label class="date_label" for="date"><?php esc_html_e( 'Start Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_holiday_admin_nonce' ); ?>
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="end_date_new" class="form-control date_picker validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d', strtotime( $holiday_data->end_date ) ) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="end_date" readonly>
							<label class="date_label" for="end_date_new"><?php esc_html_e( 'End Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						</div>
					</div>
				</div>
				<?php
				if ( ! $edit ) {
					?>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_enable_holiday_mail"><?php esc_html_e( 'Send Mail', 'mjschool' ); ?></label>
										<input id="mjschool_enable_holiday_mail" type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_enable_holiday_mail"  value="1" <?php echo checked( get_option( 'mjschool_enable_holiday_mail' ), 'yes' ); ?>/>&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 mjschool-rtl-margin-top-15px mb-3">
						<div class="form-group">
							<div class="col-md-12 form-control mjschool-rtl-relative-position">
								<div class="row mjschool-padding-radio">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-position-rtl" for="mjschool_enable_holiday_sms"><?php esc_html_e( 'Send SMS', 'mjschool' ); ?></label>
										<input id="mjschool_enable_holiday_sms" type="checkbox" class="mjschool-check-box-input-margin" name="mjschool_enable_holiday_sms"  value="1" <?php echo checked( get_option( 'mjschool_enable_holiday_sms' ), 'yes' ); ?>/>&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				if ( $edit ) {
					?>
					<div class="col-md-6 input mb-3">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool_template"><?php esc_html_e( 'Select Status', 'mjschool' ); ?></label>
						<?php $holiday_status = $holiday_data->status; ?>
						<select name="status"  id="status" class="form-control mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select Status', 'mjschool' ); ?></option>
							<option value="0" <?php if ( $holiday_status === '0' ) { selected( $holiday_status, 0 ); } ?> ><?php esc_html_e( 'Approve', 'mjschool' ); ?></option>
							<option value="1" <?php if ( $holiday_status === '1' ) { selected( $holiday_status, 1 ); } ?> ><?php esc_html_e( 'Not Approve', 'mjschool' ); ?></option>
						</select>
					</div>
					<?php
				} else {
					?>
					<input  type="hidden" value="0" name="status" readonly>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		// --------- Get Module-Wise Custom Field Data. --------------//
		$custom_field_obj = new Mjschool_Custome_Field();
		$module           = 'holiday';
		$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
		?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Holiday', 'mjschool' ); } else { esc_attr_e( 'Add Holiday', 'mjschool' );} ?>" name="save_holiday" class="btn btn-success mjschool-save-btn mjschool-rtl-margin-0px" />
				</div>
			</div>
		</div>
	</form>
</div><!-- mjschool-panel-body. -->