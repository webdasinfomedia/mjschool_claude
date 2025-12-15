<?php
/**
 * Leave Form Template.
 *
 * This file renders the "Add/Edit Leave" form at the admin.
 * It allows admins to create or update leave records, specify leave type, duration,
 * reason, and choose whether to notify parents/students via email or SMS.
 *
 * @package Mjschool
 * @subpackage MJSchool/admin/includes/leave
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<?php
$leave_id = 0;
if ( isset( $_REQUEST['leave_id'] ) ) {
	$leave_id = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['leave_id'])) ) );
}
$edit = 0;
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
	$edit   = 1;
	$result = $mjschool_obj_leave->mjschool_get_single_leave( $leave_id );
}
$students = mjschool_get_student_group_by_class();
?>
<!-- Start Panel body. -->
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res"><!--------- Panel body. ------->
	<!-- Start Leave form. -->
	<form name="leave_form" action="" method="post" class="mjschool-form-horizontal" id="leave_form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input id="action" type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<input type="hidden" name="leave_id" value="<?php echo esc_attr( $leave_id ); ?>" />
		<input type="hidden" name="status" value="<?php echo 'Not Approved'; ?>" />
		<input type="hidden" name="leave_id" value="<?php echo esc_attr( $leave_id ); ?>" />
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Leave Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-6 input mjschool-single-select">
					<select class="form-control add-search-single-select-js display-members max_mjschool-width-70px0" id="mjschool-student-id" name="student_id">
						<option value=""><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
						<?php
						if ( $edit ) {
							$student = $result->student_id;
						} elseif ( isset( $_REQUEST['student_id'] ) ) {
							$student = sanitize_text_field(wp_unslash($_REQUEST['student_id']));
						} else {
							$student = '';
						}
						$studentdata = mjschool_get_all_student_list( 'student' );
						foreach ( $students as $label => $opt ) {
							?>
							<optgroup label="<?php echo esc_html__( 'Class :', 'mjschool' ) . ' ' . esc_attr( $label ); ?>">
								<?php foreach ( $opt as $id => $name ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $id, $student ); ?>><?php echo esc_html( $name ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php } ?>
					</select>
					<span class="mjschool-multiselect-label">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-student-id"><?php esc_html_e( 'Select Student', 'mjschool' ); ?><span class="required">*</span></label>
					</span>
				</div>
				<div class="col-md-4 input">
					<label class="ml-1 mjschool-custom-top-label top" for="leave_type"><?php esc_html_e( 'Leave Type', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
					<select class="form-control validate[required] leave_type mjschool-width-100px" name="leave_type" id="leave_type">
						<option value=""><?php esc_html_e( 'Select Leave Type', 'mjschool' ); ?></option>
						<?php
						if ( $edit ) {
							$category = $result->leave_type;
						} elseif ( isset( $_REQUEST['leave_type'] ) ) {
							$category = sanitize_text_field(wp_unslash($_REQUEST['leave_type']));
						} else {
							$category = '';
						}
						$activity_category = mjschool_get_all_category( 'leave_type' );
						if ( ! empty( $activity_category ) ) {
							foreach ( $activity_category as $retrive_data ) {
								echo '<option value="' . esc_attr( $retrive_data->ID ) . '" ' . selected( $category, $retrive_data->ID ) . '>' . esc_html( $retrive_data->post_title ) . '</option>';
							}
						}
						?>
					</select>
				</div>
				<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-3 mjschool-rtl-margin-0px">
					<button id="mjschool-addremove-cat" class="mjschool-save-btn sibling_add_remove" model="leave_type"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>
				</div>
				<div class="col-md-6 mjschool-res-margin-bottom-20px mjschool-rtl-margin-top-15px">
					<div class="form-group mb-3">
						<div class="col-md-12 form-control mjschool_minheight_47px" >
							<div class="row mjschool-padding-radio">
								<div class="input-group">
									<label class="mjschool-custom-top-label mjschool-margin-left-0" for="reason"><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?><span class="required">*</span></label>
									<div class="d-inline-block">
										<?php
										$durationval = '';
										if ( $edit ) {
											$durationval = $result->leave_duration;
										} elseif ( isset( $_POST['duration'] ) ) {
											$durationval = sanitize_text_field(wp_unslash($_POST['duration']));
										}
										?>
										<label class="radio-inline">
											<input id="half_day" type="radio" value="half_day" class="tog duration" name="leave_duration" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" <?php checked( 'half_day', $durationval ); ?> /><?php esc_html_e( 'Half Day', 'mjschool' ); ?>
										</label>
										<label class="radio-inline">
											<?php
											if ( $edit ) {
												?>
												<input id="full_day" type="radio" value="full_day" class="tog duration" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" name="leave_duration" <?php checked( 'full_day', $durationval ); ?> /><?php esc_html_e( 'Full Day', 'mjschool' ); ?>
												<?php
											} else {
												?>
												<input id="full_day" type="radio" value="full_day" class="tog duration" idset="<?php if ( $edit ) { echo esc_attr($result->id ); } ?>" name="leave_duration" <?php checked( 'full_day', $durationval ); ?> checked /><?php esc_html_e( 'Full Day', 'mjschool' ); ?>
												<?php
											}
											?>
										</label>
										<label class="radio-inline margin_left_top">
											<input id="more_then_day" type="radio" idset="<?php if ( $edit ) { echo esc_attr( $result->id );} ?>" value="more_then_day" class="tog duration" name="leave_duration" <?php checked( 'more_then_day', $durationval ); ?> /><?php esc_html_e( 'More Than One Day', 'mjschool' ); ?>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="leave_date" class="col-sm-6 col-md-6 col-lg-6 col-xl-6"></div>
				<div class="col-md-6 mjschool-note-text-notice">
					<div class="form-group input">
						<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
							<div class="form-field">
								<textarea id="reason" maxlength="150" class="mjschool-textarea-height-47px form-control validate[required,custom[address_description_validation]]" maxlength="150" name="reason"><?php if ( $edit ) { echo esc_attr( $result->reason ); } elseif ( isset( $_POST['reason'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_POST['reason'])) );} ?> </textarea>
								<span class="mjschool-txt-title-label"></span>
								<label class="text-area address active" for="reason"><?php esc_html_e( 'Reason', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							</div>
						</div>
					</div>
				</div>
				<?php wp_nonce_field( 'save_leave_nonce' ); ?>
			</div>
		</div>
		<div class="form-body mjschool-user-form">
			<?php
			if ( ! $edit ) {
				?>
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_leave_mail"><?php esc_html_e( 'Send Mail To Parents & Students', 'mjschool' ); ?></label>
										<input id="mjschool_enable_leave_mail" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mail" value="1" /> <?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_leave_mjschool_student"><?php esc_html_e( 'Enable Send SMS To Student', 'mjschool' ); ?></label>
										<input id="mjschool_enable_leave_mjschool_student" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mjschool_student" value="1" /> <?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-3 col-lg-3 col-xl-3 mjschool-padding-top-15px-res mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
						<div class="form-group">
							<div class="col-md-12 form-control">
								<div class="row mjschool-padding-radio mjschool-rtl-relative-position">
									<div>
										<label class="mjschool-custom-top-label mjschool-label-right-position" for="mjschool_enable_leave_mjschool_parent"><?php esc_html_e( 'Enable Send SMS To Parent', 'mjschool' ); ?></label>
										<input id="mjschool_enable_leave_mjschool_parent" type="checkbox" class="mjschool-check-box-input-margin" name="smgt_enable_leave_mjschool_parent" value="1" /> <?php esc_html_e( 'Enable', 'mjschool' ); ?>
									</div>
								</div>
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
		$module                    = 'leave';
		$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
		?>
		<?php wp_nonce_field( 'save_leave_nonce' ); ?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6">
					<input type="submit"  value="<?php if ( $edit ) { esc_html_e( 'Save', 'mjschool' ); } else { esc_html_e( 'Add Leave', 'mjschool' ); } ?>" name="save_leave" class="btn btn-success mjschool-save-btn mjschool-rtl-margin-0px save_leave_validate" />
				</div>
			</div>
		</div>
	</form>
	<!-- End Leave form. -->
</div>