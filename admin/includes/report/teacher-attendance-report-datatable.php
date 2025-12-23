<?php

/**
 * Teacher Attendance Report – Datatable View
 *
 * This file renders the Teacher Attendance Report in a datatable format.
 * Administrators can filter attendance records by teacher, status, and date
 * range (predefined ranges or custom period). Results are displayed with
 * corresponding metadata such as teacher details, class name, date, day,
 * status, and comments.
 *
 * Features:
 * - Filter by teacher, attendance status, and date type (today, week, month, etc.).
 * - Option to select a custom start–end date range.
 * - Displays attendance in a sortable, searchable DataTable.
 * - Supports tooltips, dynamic colors, and responsive layout.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for teacher attendance report datatable tab.
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
					<div class="col-md-6 input">
						<?php
						if ( isset( $_POST['teacher_name'] ) ) {
							$workrval = $_POST['teacher_name'];
						} else {
							$workrval = '';
						}
						?>
						<select id="teacher_list" class="form-control display-members mjschool_heights_47px" name="teacher_name" >
							<option value="all_teacher"><?php esc_html_e( 'All Teacher', 'mjschool' ); ?></option>
							<?php
							$teacherdata = mjschool_get_users_data( 'teacher' );
							if ( ! empty( $teacherdata ) ) {
								foreach ( $teacherdata as $teacher ) {
									?>
									<option value="<?php echo esc_attr( $teacher->ID ); ?>" <?php selected( $teacher->ID ); ?>><?php echo esc_html( $teacher->display_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="col-md-6 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-status"><?php esc_html_e( 'Status', 'mjschool' ); ?></label>
						<select id="mjschool-status" name="status" class="mjschool-line-height-30px form-control">
							<option value="all_status"><?php esc_html_e( 'All Status', 'mjschool' ); ?></option>
							<option value="Present"><?php esc_html_e( 'Present', 'mjschool' ); ?></option>
							<option value="Absent"><?php esc_html_e( 'Absent', 'mjschool' ); ?></option>
							<option value="Late"><?php esc_html_e( 'Late', 'mjschool' ); ?></option>
							<option value="Half Day"><?php esc_html_e( 'Half Day', 'mjschool' ); ?></option>
						</select>
					</div>
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-6 input">
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
							<script type="text/javascript">
								(function(jQuery) {
									"use strict";
									jQuery(document).ready(function() {
										jQuery( "#report_sdate").datepicker({
											dateFormat: "<?php echo esc_js(get_option( 'mjschool_datepicker_format' ) ); ?>",
											changeYear: true,
											changeMonth: true,
											maxDate: 0,
											onSelect: function(selected) {
												var dt = new Date(selected);
												dt.setDate(dt.getDate( ) );
												jQuery( "#report_edate").datepicker( "option", "minDate", dt);
											}
										});
										jQuery( "#report_edate").datepicker({
											dateFormat: "<?php echo esc_js(get_option( 'mjschool_datepicker_format' ) ); ?>",
											changeYear: true,
											changeMonth: true,
											maxDate: 0,
											onSelect: function(selected) {
												var dt = new Date(selected);
												dt.setDate(dt.getDate( ) );
												jQuery( "#report_sdate").datepicker( "option", "maxDate", dt);
											}
										});
									});
								})(jQuery);
							</script>
							<?php
						}
						?>
					</div>
					<div class="col-md-6">
						<input type="submit" name="view_attendance" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	if ( isset( $_REQUEST['view_attendance'] ) ) {
		$date_type = sanitize_text_field( $_POST['date_type'] );
		if ( $date_type === 'period' ) {
			$start_date = sanitize_text_field( $_REQUEST['start_date'] );
			$end_date   = sanitize_text_field( $_REQUEST['end_date'] );
			$status     = sanitize_text_field( $_POST['status'] );
			$teacher_id = sanitize_text_field( $_REQUEST['teacher_name'] );
			$attendance = mjschool_teacher_view_attendance_for_report( $start_date, $end_date, $teacher_id, $status );
		} elseif ( $date_type === 'today' || $date_type === 'this_week' || $date_type === 'last_week' || $date_type === 'this_month' || $date_type === 'last_month' || $date_type === 'last_3_month' || $date_type === 'last_6_month' || $date_type === 'last_12_month' || $date_type === 'this_year' ) {
			$result     = mjschool_all_date_type_value( $date_type );
			$response   = json_decode( $result );
			$start_date = $response[0];
			$end_date   = $response[1];
			$teacher_id = $_REQUEST['teacher_name'];
			$status     = $_POST['status'];
			$attendance = mjschool_teacher_view_attendance_for_report( $start_date, $end_date, $teacher_id, $status );
		}
	} else {
		$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		$attendance = mjschool_view_teacher_for_report_attendance_report_for_start_date_enddate( $start_date, $end_date );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $attendance ) ) {
			?>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="teacher_attendance_list_report" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $attendance ) ) {
								$i = 0;
								foreach ( $attendance as $attendance_data ) {
									$member_data = get_userdata( $attendance_data->user_id );
									$class       = mjschool_get_class_name_by_teacher_id( $member_data->data->ID );
									$color_class_css = mjschool_table_list_background_color( $i );
									 
									?>
									<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
										<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image mjschool-margin-top-3px">
										</p>
									</td>
									<td><?php echo esc_html( mjschool_get_display_name($attendance_data->user_id ) );?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name','mjschool' );?>"></i></td>
									<td><?php echo esc_html( mjschool_get_class_name($class->class_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name','mjschool' );?>"></i></td>
									<td><?php echo esc_html( mjschool_get_date_in_input_box($attendance_data->attendence_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date','mjschool' );?>"></i></td>
									<td><?php esc_html_e(date( "D", strtotime($attendance_data->attendence_date ) ),'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day','mjschool' );?>"></i></td>
									<td class="name">
										<?php $status_color =  mjschool_attendance_status_color($attendance_data->status); ?>
										<span style="color:<?php echo esc_attr($status_color); ?>;"> <?php echo esc_html( $attendance_data->status); ?> </span>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status','mjschool' );?>"></i>
									</td>
									<?php
									$comment = $attendance_data->comment;
									$description = strlen($comment) > 30 ? substr( $comment, 0, 30) . "..." : $comment;
									?>
									<td><?php if( ! empty( $description ) ){ echo esc_html( $description); }else{ esc_html_e( 'N/A', 'mjschool' ); } ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if( ! empty( $comment ) ){ echo esc_attr($comment);}else{ esc_html_e( 'Comment','mjschool' );} ?>"></i></td>              
									<?php
									echo '</tr>';
									$i++;
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