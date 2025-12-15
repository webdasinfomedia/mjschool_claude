<?php
/**
 * Admin Class Room Management Form.
 *
 * This file handles the form interface for creating and editing class rooms within the Mjschool plugin.
 * It provides support for multi-select class and subject assignments, form validation, and nonce-based
 * security during save operations.
 *
 * Key Features:
 * - Add or edit classroom details such as name, type, and capacity.
 * - Assign multiple classes and subjects to a single room.
 * - Uses Bootstrap multiselect with translation-ready labels.
 * - Includes client-side validation and WordPress nonce security.
 * - Dynamically populates data during edit mode.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/class_room
 * @since      1.0.0
 */
$edit = 0;
if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) 
{
	$edit = 1;
	$classroomdata = mjschool_get_class_room_by_id( intval( wp_unslash($_REQUEST['class_room_id']) ) );
}
?>
<div class="mjschool-panel-body"><!-------- Panel body. -------->
	<form name="mjschool-class-room-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-class-room-form"><!------- form Start --------->
		<?php $action = isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash($_REQUEST['action'])) : 'insert'; ?>
		<input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
		<div class="header">
			<h3 class="mjschool-first-header"><?php esc_html_e( 'Class Room Information', 'mjschool' ); ?></h3>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-rtl-margin-top-15px">
					<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
						<?php
						$classes = array();
						if ($edit) 
						{
							$classes = json_decode($classroomdata->class_id, true); // Ensure associative array.
							//var_dump($classes);
						} 
						elseif ( isset( $_POST['class_name'] ) ) {
							$classes = sanitize_text_field( wp_unslash($_POST['class_name']));
						} else {
							$classes = array(); // Initialize as empty array.
						}
						?>
						<select name="class_name[]" multiple="multiple" class="form-control" id="class_name">
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
                                // $selected = in_array($classdata['class_id'], $classes) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($classdata['class_id']); ?>" <?php selected( in_array($classdata['class_id'], $classes), true ); ?>>
									<?php echo esc_html( $classdata['class_name']); ?>
								</option>
							<?php } ?>
						</select>
						<span class="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="room_name" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ($edit) { echo esc_attr($classroomdata->room_name);} ?>" name="room_name">
							<label for="userinput1" class=""><?php esc_html_e( 'Room Name', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6 rtl_mjschool-margin-top-15px mb-3 mjschool-teacher-list-multiselect">
					<div class="col-sm-12 mjschool-multiselect-validation-class mjschool-multiple-select mjschool-rtl-padding-left-right-0px">
						<?php
						$selected_subjects = array();
						if ($edit && !empty($classroomdata->sub_id ) ) {
							$selected_subjects = json_decode($classroomdata->sub_id, true);
						}
						$all_subjects = mjschool_get_all_subject(); // You need to have this function or replace with your subject fetch logic.
						?>
						<select name="mjschool-subject-list[]" multiple="multiple" id="mjschool-subject-list" class="form-control validate[required] teacher_list">
							<?php foreach ($all_subjects as $subject) { ?>
								<option value="<?php echo esc_attr($subject->subid); ?>" <?php echo in_array($subject->subid, $selected_subjects) ? 'selected' : ''; ?>>
									<?php echo esc_html( $subject->sub_name . " - " . $subject->subject_code); ?>
								</option>
							<?php } ?>
						</select>
						<span class ="mjschool-multiselect-label">
							<label class="ml-1 mjschool-custom-top-label top" for="staff_name"><?php esc_html_e( 'Select Subject','mjschool' );?><span class="required">*</span></label>
						</span>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="room_type" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ($edit) { echo esc_attr($classroomdata->room_type);} ?>" name="room_type">
							<label for="userinput1" class=""><?php esc_html_e( 'Room Type', 'mjschool' ); ?><span class="required">*</span></label>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group input">
						<div class="col-md-12 form-control">
							<input id="room_capacity" oninput="this.value = Math.abs(this.value)" class="form-control validate[min[0],maxSize[4]]" type="number" value="<?php if ($edit) { echo esc_attr($classroomdata->room_capacity); } ?>" name="room_capacity">
							<label for="userinput1" class=""><?php esc_html_e( 'Room Capacity', 'mjschool' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php wp_nonce_field( 'save_class_room_admin_nonce' ); ?>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
					<input type="submit" value="<?php if ($edit) { esc_attr_e( 'Save Class Room', 'mjschool' );} else { esc_attr_e( 'Add Class Room', 'mjschool' );} ?>" name="save_classroom" class="mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form> <!------- form end. --------->
</div><!-------- Panel body. -------->