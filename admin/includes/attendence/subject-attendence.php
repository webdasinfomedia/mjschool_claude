<?php
/**
 * Admin Subject-wise Attendance Management Page
 *
 * This file handles the subject-level attendance feature within the admin dashboard.
 * It provides functionality for selecting classes, sections, and subjects, and
 * allows teachers or admins to take, update, and save attendance for each student.
 *
 * It also supports:
 * - Preventing attendance on holidays
 * - Displaying student lists dynamically based on filters
 * - Optional email/SMS notifications to parents for absentees
 * - University/school type differentiation
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );
if ( $active_tab1 === 'subject_attendence' ) {

	// Check nonce for subject attendence tab.
	if ( isset( $_GET['tab'] ) ) {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_student_attendance_tab' ) ) {
			wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
		}
	}

	?>
	<div class="mjschool-panel-body"> <!-- mjschool-panel-body. -->
		<form method="post" id="subject_attendance">
			<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_take_nonce' ) ); ?>">
			<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input id="curr_date_subject" class="form-control date_picker curr_date" type="text" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" name="curr_date" readonly>
								<label class="date_label" for="curr_date_subject"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php
						if ( isset( $_REQUEST['class_id'] ) ) {
							$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
						}
						?>
						<select name="class_id" id="mjschool-class-list" class="form-control validate[required]">
							<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php if ( $school_type === 'school' ) { ?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
							<?php
							$class_section = '';
							if ( isset( $_REQUEST['class_section'] ) ) {
								$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
							}
							?>
							<select name="class_section" class="form-control mjschool-class-section-subject" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = sanitize_text_field(wp_unslash($_REQUEST['class_section']));
									foreach ( mjschool_get_class_sections( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php } ?>
					<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 input mjschool-error-msg-left-margin">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-subject-list"><?php esc_html_e( 'Select Subject', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
						<select name="sub_id" id="mjschool-subject-list" class="form-control">
							<option value=""><?php esc_html_e( 'Select Subject', 'mjschool' ); ?></option>
							<?php
							$sub_id = 0;
							if ( isset( $_POST['sub_id'] ) ) {
								$sub_id = sanitize_text_field(wp_unslash($_POST['sub_id']));
								?>
								<?php
								$allsubjects = mjschool_get_subject_by_class_id( sanitize_text_field(wp_unslash($_POST['class_id'])) );
								foreach ( $allsubjects as $subjectdata ) {
									?>
									<option value="<?php echo esc_attr( $subjectdata->subid ); ?>" <?php selected( $subjectdata->subid, $sub_id ); ?>><?php echo esc_html( $subjectdata->sub_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				</div>
			</div>
			
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6">
						<input type="submit" value="<?php esc_html_e( 'Take Attendance', 'mjschool' ); ?>" name="attendence" class="mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div><!-- mjschool-panel-body. -->
	<div class="clearfix"> </div>
	<?php
	if ( isset( $_REQUEST['attendence'] )) {
		if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_attendance_take_nonce')) {
			wp_die(esc_html__('Security check failed.', 'mjschool'));
		}
		$attendanace_date = sanitize_text_field(wp_unslash($_REQUEST['curr_date']));
		$holiday_dates    = mjschool_get_all_date_of_holidays();
		if ( in_array( $attendanace_date, $holiday_dates ) ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_20px">
				<p><?php esc_html_e( 'This day is holiday you are not able to take attendance', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		} else {
			if ( isset( $_REQUEST['class_id'] ) && sanitize_text_field(wp_unslash($_REQUEST['class_id'])) !== ' ' ) {
				$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
			} else {
				$class_id = 0;
			}
			if ( $class_id === 0 ) {
				?>
				<div class="mjschool-panel-heading">
					<h4 class="mjschool-panel-title"><?php esc_html_e( 'Please Select Class', 'mjschool' ); ?></h4>
				</div>
				<?php
			} else {
					
				if ( isset( $_REQUEST['class_section']) && sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) !== "") {
					$student = mjschool_get_student_name_with_class_and_section($class_id, sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ));
					sort($student);
				} else {
					if ( $school_type === 'university' )
					{
						if ( isset( $_REQUEST['sub_id']) && !empty( sanitize_text_field( wp_unslash( $_REQUEST['sub_id'] ) ) ) ) {
							$student = mjschool_get_students_assigned_to_subject(sanitize_text_field(wp_unslash($_REQUEST['sub_id'])));
						} else {
							$student = array(); // fallback if no subject selected.
						}
					}
					else
					{	
						$student = mjschool_get_student_name_with_class($class_id);
						sort( $student );
					}
				}
				
				if ( ! empty( $student ) ) {
					?>
					<div class="mjschool-panel-body"> <!-- mjschool-panel-body. -->
						<form method="post" class="mjschool-form-horizontal">
							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
							<input type="hidden" name="sub_id" value="<?php echo esc_attr( $sub_id ); ?>" />
							<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_subject_attendance_nonce' ) ); ?>">
							<input type="hidden" name="class_section" value="<?php if( isset( $_REQUEST['class_section'] ) ) { echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['class_section'])) ); }?>" />
							<input type="hidden" name="curr_date" value="<?php if ( isset( $_POST['curr_date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" />
							<div class="mjschool-panel-heading mjschool-margin-top-20px mjschool-margin-top-15px-rs">
								<h4 class="mjschool-panel-title"> <?php esc_html_e( 'Class', 'mjschool' ); ?> : <?php echo esc_attr( mjschool_get_class_name( $class_id ) ); ?> , <?php esc_html_e( 'Date', 'mjschool' ); ?> : <?php echo esc_attr( mjschool_get_date_in_input_box( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) ); ?> </h4>
							</div>
							<div class="col-md-12 mjschool-padding-payment mjschool_att_tbl_list">
								<div class="table-responsive padding_top_0px">
									<table class="table">
										<tr>
											<th class="mjschool_width_0px"><?php esc_html_e( 'Srno', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th class="mjschool_widht_250px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
										</tr>
										<?php
										$date = sanitize_text_field(wp_unslash($_POST['curr_date']));
										$i    = 1;
										foreach ( $student as $mjschool_user ) {
											$umetadata = mjschool_get_user_image( $mjschool_user->ID );
											if ( empty( $umetadata ) ) {
												$profile_path = get_option( 'mjschool_student_thumb_new' );
											} else {
												$profile_path = $umetadata;
											}
											$date             = date( 'Y-m-d', strtotime( sanitize_text_field(wp_unslash($_POST['curr_date'])) ) );
											if ( $school_type === 'school' ){
												$check_attendance = $obj_attend->mjschool_check_has_subject_attendace( $mjschool_user->ID, $class_id, $date, sanitize_text_field(wp_unslash($_POST['sub_id'])), sanitize_text_field(wp_unslash($_POST['class_section'])) );
											}
											if ( ! empty( $check_attendance ) ) {
												$attendanc_status = $check_attendance->status;
											} else {
												$attendanc_status = 'Present';
											}
											echo '<tr>';
											echo '<td>' . esc_html( $i ) . '</td>';
											
											echo '<td class="mjschool_padding_left_0px"><img src=' . esc_url($profile_path) . ' class="img-circle" /><span class="ms-2">' . esc_html( mjschool_student_display_name_with_roll($mjschool_user->ID ) ) . '</span></td>';
											
											?>
											<td class="mjschool_padding_left_0px">
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Present" <?php checked( $attendanc_status, 'Present' ); ?>> <?php esc_html_e( 'Present', 'mjschool' ); ?></label>
												<label class="radio-inline"> <input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Absent" <?php checked( $attendanc_status, 'Absent' ); ?>> <?php esc_html_e( 'Absent', 'mjschool' ); ?></label><br>
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Late" <?php checked( $attendanc_status, 'Late' ); ?>> <?php esc_html_e( 'Late', 'mjschool' ); ?></label>
												<label class="radio-inline"><input type="radio" name="attendanace_<?php echo esc_attr( $mjschool_user->ID ); ?>" value="Half Day" <?php checked( $attendanc_status, 'Half Day' ); ?>> <?php esc_html_e( 'Half Day', 'mjschool' ); ?></label>
											</td>
											<td class="padding_left_right_0">
												<div class="form-group input mjschool-margin-bottom-0px">
													<div class="col-md-12 form-control">
														<input type="text" name="attendanace_comment_<?php echo esc_attr( $mjschool_user->ID ); ?>" class="form-control" value="<?php if ( ! empty( $check_attendance ) ) { echo esc_attr( $check_attendance->comment );} ?>">
													</div>
												</div>
											</td>
											<?php
											echo '</tr>';
											++$i;
										}
										?>
									</table>
								</div>
								<div class="d-flex mt-2">
									<div class="form-group row mb-3">
										<span class="col-sm-8 control-label " for="enable"><?php esc_html_e( 'If student absent then Send Email to his/her parents', 'mjschool' ); ?></span>
										<div class="col-sm-2 ps-0">
											<div class="mjschool-checkbox">
												<span>
													<input class="mjschool_check_box" type="checkbox"  <?php $mjschool_subject_mail_service_enable = 0; if ( $mjschool_subject_mail_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_subject_mail_service_enable">
												</span>
											</div>
										</div>
									</div>
									<div class="form-group row mb-3">
										<span class="col-sm-10 control-label" for="enable"><?php esc_html_e( 'If student absent then Send SMS to his/her parents', 'mjschool' ); ?></span>
										<div class="col-sm-2 ps-0">
											<div class="mjschool-checkbox">
												<span>
													<input class="mjschool_check_box" type="checkbox"  <?php $mjschool_service_enable = 0; if ( $mjschool_service_enable ) { echo 'checked'; } ?> value="1" name="mjschool_service_enable">
												</span>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-12 mjschool-rtl-res-att-save">
								<input type="submit" value="<?php esc_html_e( 'Save Attendance', 'mjschool' ); ?>" name="save_sub_attendence" id="mjschool-res-rtl-width-100px mjschool-res-rtl-width-100px" class="col-sm-6 mjschool-save-attr-btn" />
							</div>
						</form>
					</div><!-- mjschool-panel-body. -->
					<?php
				} else {
					?>
					<div class=" mt-2">
						<h4 class="mjschool-panel-title"><?php esc_html_e( 'No Any Student In This Class', 'mjschool' ); ?></h4>
					</div>
					<?php
				}
			}
		}
	}
}
?>