<?php
/**
 * Upload Student Attendance CSV
 *
 * This file provides the interface and logic for uploading student attendance records
 * from a CSV file into the system.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
	<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-5">
					<div class="form-group input">
						<div class="col-md-12 form-control mjschool-res-rtl-height-50px">
							<label class="mjschool-custom-control-label mjschool-custom-top-label ml-2 mjschool-margin-left-30px" for="city_name"><?php esc_html_e( 'Select CSV file', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
							<div class="col-sm-12">
								<input id="csv_file" type="file" class="col-md-12 validate[required] csvfile_width" name="csv_file">
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<input type="submit" value="<?php esc_attr__('Upload CSV File', 'mjschool'); ?>" name="upload_attendance_csv_file" class="col-sm-6 mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form>
</div><!-- mjschool-panel-body. -->