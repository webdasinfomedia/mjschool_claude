<?php
/**
 * Attendance DataTable Report Template.
 *
 * Displays the attendance report filter form and the results table.
 * Handles class selection, date filters, student filters, and shows
 * attendance records with status, QR info, and comments.
 *
 * This template processes form input, validates nonce, loads student data,
 * applies date-range filters, and renders the attendance report table.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for attendance report datatable tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
if ( isset( $_POST['date_type'] ) ) {
	$date_type_value = sanitize_text_field(wp_unslash($_POST['date_type']));
} else {
	$date_type_value = 'this_month';
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<form method="post">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
							<?php
							$class_id = '';
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = $_REQUEST['class_id'];
							}
							?>
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-status"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
						<select id="mjschool-status" name="status" class="mjschool-line-height-30px form-control">
							<option value="all_status"><?php esc_html_e( 'All Status', 'mjschool' ); ?></option>
							<option value="Present"><?php esc_html_e( 'Present', 'mjschool' ); ?></option>
							<option value="Absent"><?php esc_html_e( 'Absent', 'mjschool' ); ?></option>
							<option value="Late"><?php esc_html_e( 'Late', 'mjschool' ); ?></option>
							<option value="Half Day"><?php esc_html_e( 'Half Day', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="student_status"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></label>
						<select name="student_status" id="student_status" class="mjschool-line-height-30px form-control">
							<?php
							$status=null;
							if ( isset( $_REQUEST['student_status'] ) ) {
								$status = $_REQUEST['student_status'];
							}
							?>
							<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'mjschool' ); ?></option>
							<option value="deactive" <?php selected( $status, 'deactive' ); ?>><?php esc_html_e( 'Deactive', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-class-section-hide">
						<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Select Student', 'mjschool' ); ?></label>
						<select name="student_id" id="student_list" class="form-control mjschool-max-width-100px mjschool-input-height-47px" >
							<option value=""><?php esc_html_e( 'All Student', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['student_id'] ) ) {
								$class_id  = $_REQUEST['class_id'];
								$exlude_id = mjschool_approve_student_list();
								// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								$student_data = get_users(
									array(
										'meta_key'   => 'class_name',
										'meta_value' => $class_id,
										'role'       => 'student',
										'exclude'    => $exlude_id,
									)
								);
								// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
								foreach ( $student_data as $studentdata ) {
									?>
									<option value="<?php echo esc_attr( $studentdata->ID ); ?>" <?php selected( $_REQUEST['student_id'], $studentdata->ID ); ?>><?php echo esc_html( mjschool_student_display_name_with_roll( $studentdata->ID ) ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<?php mjschool_date_filter_dropdown( $date_type_value ); ?>
					</div>
					<div id="date_type_div" class="col-md-6 <?php echo ( $selected_date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
						<?php
						if ( $selected_date_type === 'period' ) {
							?>
							<div class="row">
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( $_POST['start_date'] ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6 mb-2">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( $_POST['end_date'] ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
											<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<div class="col-md-3">
						<input type="submit" name="view_attendance" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	$student_status=null;
	if ( isset( $_REQUEST['view_attendance'] ) ) {
		$date_type      = sanitize_text_field( $_POST['date_type'] );
		$student_status = sanitize_text_field( $_POST['student_status'] );
		if ( $date_type === 'period' ) {
			$start_date          = sanitize_text_field( mjschool_get_format_for_db( $_REQUEST['start_date'] ) );
			$end_date            = sanitize_text_field( mjschool_get_format_for_db( $_REQUEST['end_date'] ) );
			$class_id            = sanitize_text_field( $_POST['class_id'] );
			$status              = sanitize_text_field( $_POST['status'] );
			$student_id          = sanitize_text_field( $_POST['student_id'] );
			$filtered_attendance = mjschool_view_attendance_for_report( $start_date, $end_date, $class_id, $student_id, $status );
		} elseif ( $date_type === 'today' || $date_type === 'this_week' || $date_type === 'last_week' || $date_type === 'this_month' || $date_type === 'last_month' || $date_type === 'last_3_month' || $date_type === 'last_6_month' || $date_type === 'last_12_month' || $date_type === 'this_year' ) {
			$result              = mjschool_all_date_type_value( $date_type );
			$response            = json_decode( $result );
			$start_date          = $response[0];
			$end_date            = $response[1];
			$class_id            = sanitize_text_field( $_POST['class_id'] );
			$status              = sanitize_text_field( $_POST['status'] );
			$student_id          = sanitize_text_field( $_POST['student_id'] );
			$filtered_attendance = mjschool_view_attendance_for_report( $start_date, $end_date, $class_id, $student_id, $status );
		}
	} else {
		$filtered_attendance = '';
	}
	if ( $student_status === 'active' ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$students = get_users( array( 'role' => 'student' ) );
		$students_without_hash = array_filter(
			$students,
			function ( $mjschool_user ) {
				return get_user_meta( $mjschool_user->ID, 'hash', true ) === '';
			}
		);
		$student_ids           = wp_list_pluck( $students_without_hash, 'ID' );
	} else {
		$students = get_users( array( 'role' => 'student' ) );
		$students_without_hash = array_filter(
			$students,
			function ( $mjschool_user ) {
				return get_user_meta( $mjschool_user->ID, 'hash', true ) === '';
			}
		);
		$student_ids           = wp_list_pluck( $students_without_hash, 'ID' );
	}
	$attendance = array();
	if( ! empty( $filtered_attendance ) )
	{
		foreach ( $filtered_attendance as $attendance_data ) {
			if ( in_array( $attendance_data->user_id, $student_ids ) ) {
				$attendance[] = $attendance_data;
			}
		}
	}
	
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $attendance ) ) {
			?>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="attendance_list_report" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance With QR Code', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $attendance ) ) {
								$i = 0;
								foreach ( $attendance as $attendance_data ) {
									$class_section_sub_name = mjschool_get_class_section_subject( $attendance_data->class_id, $attendance_data->section_id, $attendance_data->sub_id );
									$created_by             = get_userdata( $attendance_data->attend_by );
									$color_class_css = mjschool_table_list_background_color( $i );
									?>
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
											
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
											
										</p>
									</td>
									<td><?php echo esc_html( mjschool_student_display_name_with_roll( $attendance_data->user_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
									<td><?php echo wp_kses( $class_section_sub_name, array( 'b' => array() ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( mjschool_get_date_in_input_box( $attendance_data->attendance_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></td>
									<td>
										<?php
										$day = date( 'l', strtotime( $attendance_data->attendance_date ) );
										echo esc_html( $day );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php $status_color = mjschool_attendance_status_color( $attendance_data->status ); ?>
										<span style="color:<?php echo esc_attr( $status_color ); ?>;"> <?php echo esc_html( $attendance_data->status ); ?> </span>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( $created_by->display_name ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
									</td>
									<?php
									$comment     = $attendance_data->comment;
									$description = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
									?>
									<td>
										<?php
										if ( $attendance_data->attendence_type === 'QR' ) {
											esc_html_e( 'Yes', 'mjschool' );
										} else {
											esc_html_e( 'No', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance With QR Code', 'mjschool' ); ?>"></i>
									</td>              
									<td class="name">
										<?php
										if ( ! empty( $attendance_data->comment ) ) {
											$comment       = $attendance_data->comment;
											$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
											echo esc_html( $grade_comment );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $attendance_data->comment ) ) { echo esc_html( $attendance_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
									</td>
									<?php
									echo '</tr>';
									++$i;
								}
							}
							?>
						</tbody>
					</table>
				</form>
			</div>
			<?php
		} else {
			?>
			<div class="mjschool-calendar-event-new">
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
			</div>
			<?php
		}
		?>
	</div>
</div>