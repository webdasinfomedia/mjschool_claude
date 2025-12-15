<?php
/**
 * Exam Management Form.
 *
 * This file handles adding and editing exam records in the MJSchool plugin.
 * It allows administrators or authorized users to create, update, and manage
 * exam schedules with details such as exam name, start and end dates, and comments.
 *
 * Key Features:
 * - Secure form submission using WordPress nonce and sanitization functions.
 * - Supports both adding new exams and editing existing ones.
 * - Automatically records creation and modification timestamps.
 * - Displays user feedback upon successful insertion or update.
 * - Ensures proper i18n (translation-ready) text for multilingual use.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/mark
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_POST['save_exam'] ) ) {
	$created_date = date( 'Y-m-d H:i:s' );
	$examdata     = array(
		'exam_name'       => mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_POST['exam_name'])) ),
		'exam_start_date' => sanitize_text_field(wp_unslash($_POST['exam_start_date'])),
		'exam_end_date'   => sanitize_text_field(wp_unslash($_POST['exam_end_date'])),
		'exam_comment'    => mjschool_strip_tags_and_stripslashes( sanitize_text_field(wp_unslash($_POST['exam_comment'])) ),
		'exam_creater_id' => get_current_user_id(),
		'created_date'    => $created_date,
	);
	// Table name without prefix.
	$tablename = 'mjschool_exam';
	if ( isset($_REQUEST['action']) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$grade_id                  = array( 'exam_id' => sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) );
		$modified_date_date        = date( 'Y-m-d H:i:s' );
		$examdata['modified_date'] = $modified_date_date;
		mjschool_update_record( $tablename, $examdata, $grade_id );
		$message = esc_attr__( 'Update Exam Successfully', 'mjschool' );
	} else {
		$reult   = mjschool_insert_record( $tablename, $examdata );
		$message = esc_attr__( 'Add Exam Successfully', 'mjschool' );
	}
}
?>
<div class="mjschool_add_class">
	<h2>
		<?php
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
			echo esc_html( esc_attr__( 'Edit Exam', 'mjschool' ) );
			$edit      = 1;
			$exam_data = mjschool_get_exam_by_id( sanitize_text_field(wp_unslash($_REQUEST['exam_id'])) );
		} else {
			echo esc_html( esc_attr__( 'Add New Exam', 'mjschool' ) );
		}
		?>
	</h2>
	<?php
	if ( isset( $message ) ) {
		echo '<div id="mjschool-message" class="mjschool-message_class updated mjschool-below-h2"><p>' . esc_html( $message ) . '</p></div>';
		?>
		<form name="class_form" action="" method="post" id="marks_form">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<table class="form-table">
				<tr>
					<th><label><?php esc_html_e( 'Exam Name', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label></th>
					<td>
						<input type="text" name="exam_name" maxlength="50" class="regular-text validate[required,custom[popup_category_validation]]" value="<?php if ( $edit ) { echo esc_attr( $exam_data->exam_name ); } ?>" />
					</td>
				</tr>
				<tr>
					<th><label><?php esc_html_e( 'Exam Start Date', 'mjschool' ); ?> </label></th>
					<td>
						<input type="date" name="exam_start_date" class="validate[required]" value="<?php if ( $edit ) { echo esc_attr( $exam_data->exam_start_date ); } ?>" readonly />
					</td>
				</tr>
				<tr>
					<th><label><?php esc_html_e( 'Exam End Date', 'mjschool' ); ?> </label></th>
					<td>
						<input type="date" name="exam_end_date" class="validate[required]" value="<?php if ( $edit ) { echo esc_attr( $exam_data->exam_end_date ); } ?>" readonly />
					</td>
				</tr>
				<tr>
					<th><label><?php esc_html_e( 'Exam Comment', 'mjschool' ); ?> </label></th>
					<td>
						<textarea name="exam_comment" class="validate[custom[address_description_validation]]" maxlength="150">
							<?php
							if ( $edit ) {
								echo esc_textarea( $exam_data->exam_comment );
							}
							?>
						</textarea>
					</td>
				</tr>
				<tr>
					<th></th>
					<td><input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Exam', 'mjschool' ); } else { esc_html_e( 'Add Exam', 'mjschool' ); } ?>" name="save_exam" /></td>
				</tr>
			</table>
		</form>
		<?php
	}
	?>
</div>