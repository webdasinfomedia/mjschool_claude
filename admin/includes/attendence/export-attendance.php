<?php
/**
 * Export Student Attendance
 *
 * This file provides functionality to export student attendance records in CSV format.
 * It defines the export form and handles user input submission.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
	<form name="mjschool-upload-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-upload-form" enctype="multipart/form-data">
		<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
		<div class="col-sm-12">
			<input type="submit" value="<?php echo esc_attr__( 'Export Student Attendance', 'mjschool' ); ?>" name="export_attendance_in_csv" class="col-sm-6 mjschool-save-attr-btn" />
		</div>
	</form>
</div><!-- mjschool-panel-body. -->