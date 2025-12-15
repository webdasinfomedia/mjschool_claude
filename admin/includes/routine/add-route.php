<?php
/**
 * Class Routine Management Form.
 *
 * This file provides the admin interface for adding and editing class routines
 * within the MJSchool plugin. It enables administrators to define class schedules
 * including class, section, subject, teacher, time, and days, with support for
 * both school and university setups.
 *
 * Key Features:
 * - Allows adding or editing class timetable routes with class, subject, and teacher details.
 * - Dynamically loads subjects, teachers, and classroom lists based on selected class.
 * - Supports multiple weekday selection using a multiselect dropdown.
 * - Integrates start and end time pickers for defining session durations.
 * - Handles both single and multiple teacher assignments.
 * - Provides an option to create virtual classrooms with date and topic details.
 * - Uses WordPress nonces and sanitization for secure form submissions.
 * - Supports conditional field visibility based on school type (school/university).
 * - Includes frontend validation for required and formatted inputs.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/routine
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( "mjschool_custom_class");
$cust_class_room = get_option( "mjschool_class_room");
?>
<!------- Panel white. ------->
<div class="mjschool-panel-white mjschool-margin-top-20px mjschool-padding-top-25px-res">
	<?php
	$edit = 0;
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) {
		$edit       = 1;
		$route_data = mjschool_get_route_by_id( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['route_id'])) ) ) );
	}
	?>

	<div class="mjschool-panel-body"> <!------- Panel Body. ------->
		<form name="route_form" action="" method="post" class="mjschool-form-horizontal" id="rout_form">
			<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field(wp_unslash($_REQUEST['action'])) : 'insert'; ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="required">*</span></label>
						<?php
						if ( $edit ) {
							$classval = $route_data->class_id;
						} elseif ( isset( $_POST['class_id'] ) ) {
							$classval = sanitize_text_field(wp_unslash($_POST['class_id']));
						} else {
							$classval = '';
						}
						?>
						<select name="class_id" id="mjschool-class-list" class="form-control validate[required] mjschool-max-width-100px">
							<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php } ?>
						</select>
					</div>
					<?php wp_nonce_field( 'save_root_admin_nonce' ); ?>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
							<?php
							if ( $edit ) {
								$sectionval = $route_data->section_name;
							} elseif ( isset( $_POST['class_section'] ) ) {
								$sectionval = sanitize_text_field(wp_unslash($_POST['class_section']));
							} else {
								$sectionval = '';
							}
							?>
							<select name="class_section" class="form-control mjschool-max-width-100px mjschool-section-id-exam" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( $edit ) {
									foreach ( mjschool_get_class_sections( $route_data->class_id ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $sectionval, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php } ?>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Subject', 'mjschool' ); ?><span class="required">*</span></label>
						<?php
						if ( $edit ) {
							$subject_id = $route_data->subject_id;
						} elseif ( isset( $_POST['subject_id'] ) ) {
							$subject_id = sanitize_text_field(wp_unslash($_POST['subject_id']));
						} else {
							$subject_id = '';
						}
						?>
						<select name="subject_id" id="mjschool-subject-list" class="form-control mjschool-change-subject validate[required] mjschool-max-width-100px">
							<?php
							if ( $edit ) {
								$subject = mjschool_get_subject_by_class_id( $route_data->class_id );
								if ( ! empty( $subject ) ) {
									foreach ( $subject as $ubject_data ) {
										?>
										<option value="<?php echo esc_attr( $ubject_data->subid ); ?>" <?php selected( $subject_id, $ubject_data->subid ); ?>><?php echo esc_html( $ubject_data->sub_name ); ?></option>
										<?php
									}
								}
							} else {
								?>
								<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php
					if ( $school_type === 'university' )
					{
						if ( $cust_class_room === 1)
						{	?>
							<div class="col-md-6 input">
								<label class="ml-1 mjschool-custom-top-label top" for="classroom_id"><?php esc_html_e( 'Class Room', 'mjschool' ); ?><span class="required">*</span></label>
								<?php if ( $edit){ $room_id=$route_data->room_id; }elseif( isset( $_POST['room_id'] ) ){$room_id=sanitize_text_field(wp_unslash($_POST['room_id']));}else{$room_id='';}?>
								<select name="room_id" id="classroom_id" class="form-control validate[required] mjschool-max-width-100px">
									<option value=""><?php esc_html_e( 'Select class Room', 'mjschool' ); ?></option>
									<?php
									if( $edit )
									{
										$classroom = mjschool_get_assign_class_room_for_single_class($route_data->class_id);
										if( ! empty( $classroom ) )
										{
											foreach ($classroom as $room_data)
											{
											?>
												<option value="<?php echo esc_attr($room_data->room_id) ;?>" <?php selected($room_id, $room_data->room_id);  ?>><?php echo esc_html( $room_data->room_name);?></option>
											<?php 
											}
										}
									}
									?>
								</select>
							</div>
							<?php
						}
					}
					if ( $edit ) {
						$teachval = mjschool_teacher_by_subject_id( $subject_id );
						?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="subject_teacher"><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?><span class="required">*</span></label>
							<select name="subject_teacher" id="subject_teacher" class="form-control validate[required] teacher_list">
								<option value=""><?php esc_html_e( 'Select Teacher', 'mjschool' ); ?></option>
								<?php
								foreach ( $teachval as $teacher ) {
									?>
									<option value="<?php echo esc_attr( $teacher ); ?>" <?php selected( $route_data->teacher_id, $teacher ); ?>><?php echo esc_html( mjschool_get_display_name( $teacher ) ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php
					} else {
						?>
						<div class="col-md-6 mjschool-rtl-margin-top-15px mjschool-teacher-list-multiselect mjschool-margin-bottom-15px-res">
							<div class="col-sm-12 mjschool-multiselect-validation-teacher mjschool-multiple-select mjschool-rtl-padding-left-right-0px mjschool-res-rtl-width-100px">
								<select name="subject_teacher[]" multiple="multiple" id="subject_teacher" class="form-control validate[required] teacher_list"></select>
							</div>
						</div>
						<?php
					}
					?>
					<?php
					if ( $edit ) {
						$day_key = $route_data->weekday;
					} elseif ( isset( $_POST['weekday'] ) ) {
						$day_key = sanitize_text_field(wp_unslash($_POST['weekday']));
					} else {
						$day_key = '';
					}
					if ( $edit ) {
						?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="weekday"><?php esc_html_e( 'Day', 'mjschool' ); ?></label>
							<select name="weekday" class="form-control validate[required] mjschool-max-width-100px" id="weekday">
								<?php
								foreach ( mjschool_day_list() as $daykey => $dayname ) {
									echo '<option  value="' . esc_attr( $daykey ) . '" ' . selected( $day_key, $daykey ) . '>' . esc_html( $dayname ) . '</option>';
								}
								?>
							</select>
						</div>
						<?php
					} else {
						?>
						<div class="col-md-6 input mjschool-multiple-select">
							<select name="weekday[]" class="form-control validate[required] mjschool-max-width-100px mjschool-multiple-select-day" id="weekday" multiple="multiple">
								<?php
								foreach ( mjschool_day_list() as $daykey => $dayname ) {
									echo '<option  value="' . esc_attr( $daykey ) . '" ' . selected( $day_key, $daykey ) . '>' . esc_html( $dayname ) . '</option>';
								}
								?>
							</select>
						</div>
						<?php
					}
					if ( $edit ) {
						// ------------ Start time convert. --------------//
						$stime      = explode( ':', $route_data->start_time );
						$start_hour = $stime[0];
						$start_min  = $stime[1];
						$shours     = str_pad( $start_hour, 2, '0', STR_PAD_LEFT );
						$smin       = str_pad( $start_min, 2, '0', STR_PAD_LEFT );
						$start_time = $shours . ':' . $smin;
						// -------------------- End time convert. -----------------//
						$etime    = explode( ':', $route_data->end_time );
						$end_hour = $etime[0];
						$end_min  = $etime[1];
						$ehours   = str_pad( $end_hour, 2, '0', STR_PAD_LEFT );
						$emin     = str_pad( $end_min, 2, '0', STR_PAD_LEFT );
						$end_time = $ehours . ':' . $emin;
					}
					?>
					<div class="col-md-3">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" id="mjschool-start-timepicker" name="start_time" class="form-control validate[required] start_time" value="<?php if ( ! empty( $route_data->start_time ) ) { echo esc_attr( $start_time ); }?>" />
								<label for="mjschool-start-timepicker"><?php esc_html_e( 'Start Time', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" id="mjschool-end-timepicker" name="end_time" class="form-control validate[required] end_time" value="<?php if ( ! empty( $route_data->end_time ) ) { echo esc_attr( $end_time ); } ?>" />
								<label for="mjschool-end-timepicker"><?php esc_html_e( 'End Time', 'mjschool' ); ?><span class="required">*</span></label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			if ( get_option( 'mjschool_enable_virtual_classroom' ) === 'yes' ) {
				if ( ! $edit ) {
					?>
					<!-- Create virtual classroom. -->
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-6 mjschool-rtl-margin-top-15px mb-3 mjschool-rtl-margin-bottom-0px">
								<div class="form-group">
									<div class="col-md-12 form-control mjschool-input-height-50px">
										<div class="row mjschool-padding-radio">
											<div class="input-group mjschool-input-checkbox">
												<label class="mjschool-custom-top-label"><?php esc_html_e( 'Create Virtual Class', 'mjschool' ); ?></label>
												<div class="checkbox mjschool-checkbox-label-padding-8px">
													<label>
														<input type="checkbox" id="isCheck" class="mjschool-margin-right-checkbox-css create_virtual_classroom" name="create_virtual_classroom" value="1" />&nbsp;&nbsp;<?php esc_html_e( 'Enable', 'mjschool' ); ?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="form-body mjschool-user-form mjschool-create-virtual-classroom-div mjschool-create-virtual-classroom-div-none">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="start_date_new" class="form-control validate[required] text-input start_date" type="text" placeholder="<?php esc_html_e( 'Enter Start Date', 'mjschool' ); ?>" name="start_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
										<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input id="end_date_new" class="form-control validate[required] text-input end_date" type="text" placeholder="<?php esc_html_e( 'Enter End Date', 'mjschool' ); ?>" name="end_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
										<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input class="form-control validate[custom[address_description_validation]]" type="text" name="password" value="">
										<label for="userinput1"><?php esc_html_e( 'Topic', 'mjschool' ); ?></label>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 form-control">
										<input class="form-control text-input validate[required,minSize[8],maxSize[12]]" type="password" name="agenda" value="">
										<label for="userinput1"><?php esc_html_e( 'Password', 'mjschool' ); ?><span class="required">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>
			<!-- End create virtual classroom. -->
			<div class="form-body">
				<div class="row">
					<div class="col-sm-6">
						<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Route', 'mjschool' ); } else { esc_html_e( 'Add Route', 'mjschool' );} ?>" name="save_route" class="btn mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!------- Panel white. ------->
</div> <!------- Panel white. ------->