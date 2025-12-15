<?php
/**
 * Admin Teacher Attendance Report List
 *
 * This file displays and manages the teacher attendance records in the admin panel.  
 * It allows administrators to:
 * - Filter attendance records by predefined date ranges (Today, This Week, Month, Year, or Custom Period)
 * - View attendance records of all or individual teachers
 * - Export teacher attendance data to CSV
 * - Delete selected attendance entries
 * - Display teacher details including name, date, day, attendance status, and comments
 *
 * Integrated Features:
 * - AJAX-powered DataTables for sorting, filtering, and searching
 * - jQuery datepickers for custom date range selection
 * - Bulk actions (select all, delete selected)
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/attendance
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_POST['date_type'] ) ) {
	$date_type_value = sanitize_text_field(wp_unslash($_POST['date_type']));
} else {
	$date_type_value = 'this_month';
}
// Check nonce for teacher attendence list tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_teacher_attendance_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<form method="post" id="attendance_list" class="attendance_list">
	<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_teacher_attendance_list_nonce' ) ); ?>">
	<div class="form-body mjschool-user-form mjschool-margin-top-15px">
		<div class="row">
			<div class="col-md-3 mb-3 input">
				<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<select id="date_type" class="mjschool-line-height-30px form-control date_type validate[required]"  name="date_type" autocomplete="off">
					<option <?php selected( $date_type_value, 'today' ); ?> value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
					<option value="this_week" <?php selected( $date_type_value, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
					<option <?php selected( $date_type_value, 'last_week' ); ?> value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
					<option value="this_month" <?php selected( $date_type_value, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
					<option value="last_month" <?php selected( $date_type_value, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
					<option value="last_3_month" <?php selected( $date_type_value, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
					<option value="last_6_month" <?php selected( $date_type_value, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
					<option value="last_12_month" <?php selected( $date_type_value, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
					<option value="this_year" <?php selected( $date_type_value, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
					<option value="last_year" <?php selected( $date_type_value, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
					<option value="period" <?php selected( $date_type_value, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
				</select>
			</div>
			<div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 input">
				<?php
				if ( isset( $_POST['teacher_name'] ) ) {
					$workrval = sanitize_text_field(wp_unslash($_POST['teacher_name']));
				} else {
					$workrval = '';
				}
				?>
				<select id="teacher_list" class="form-control display-members" name="teacher_name">
					<option value="all_teacher"><?php esc_html_e( 'All Teacher', 'mjschool' ); ?></option>
					<?php
					$teacherdata = mjschool_get_users_data( 'teacher' );
					if ( ! empty( $teacherdata ) ) {
						foreach ( $teacherdata as $teacher ) {
							?>
							<option value="<?php echo esc_attr( $teacher->ID ); ?>" <?php selected( $workrval, $teacher->ID ); ?>><?php echo esc_html( $teacher->display_name ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</div>
			<div id="date_type_div" class="col-md-6 <?php echo esc_attr( ( $date_type_value === 'period' ) ? '' : 'date_type_div_none' ); ?>">
				<?php
				if ( $date_type_value === 'period' ) {
					?>
					<div class="row">
						<div class="col-md-6 mb-2">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="text" id="report_sdate" class="form-control" name="start_date" value="<?php echo isset( $_POST['start_date'] ) ? esc_attr( sanitize_text_field(wp_unslash($_POST['start_date'])) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
									<label for="report_sdate" class="active"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mb-2">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input type="text" id="report_edate" class="form-control" name="end_date" value="<?php echo isset( $_POST['end_date'] ) ? esc_attr( sanitize_text_field(wp_unslash($_POST['end_date'])) ) : esc_attr( date( 'Y-m-d' ) ); ?>" readonly>
									<label for="report_edate" class="active"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
								</div>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
			<div class="col-md-3 mb-2">
				<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
			</div>
		</div>
	</div>
</form>
<div class="clearfix"></div>
<?php
if ( isset( $_REQUEST['view_attendance'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_teacher_attendance_list_nonce')) 
	{
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$date_type = sanitize_text_field(wp_unslash($_POST['date_type']));
	if ( $date_type === 'period' ) {
		$start_date      = sanitize_text_field(wp_unslash($_REQUEST['start_date']));
		$end_date        = sanitize_text_field(wp_unslash($_REQUEST['end_date']));
		$type            = 'teacher';
		$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
	} else {
		$result     = mjschool_all_date_type_value( $date_type );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
		if ( ! empty( $_REQUEST['teacher_name'] ) && sanitize_text_field(wp_unslash($_REQUEST['teacher_name'])) !== 'all_teacher' ) {
			$member_id       = sanitize_text_field(wp_unslash($_REQUEST['teacher_name']));
			$attendence_data = mjschool_get_member_attendence_beetween_satrt_date_to_enddate_for_admin( $start_date, $end_date, $member_id );
		} else {
			$member_id       = sanitize_text_field(wp_unslash($_REQUEST['teacher_name']));
			$type            = 'teacher';
			$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
		}
	}
} else {
	$start_date      = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$end_date        = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	$date_type       = '';
	$member_id       = '';
	$type            = 'teacher';
	$attendence_data = mjschool_get_all_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $type );
}
if ( $start_date > $end_date ) {
	echo '<script type="text/javascript">alert( "' . esc_html__( 'End Date should be greater than the Start Date', 'mjschool' ) . '");</script>';
}
if ( ! empty( $attendence_data ) ) {
	?>
	<?php
	if ( isset( $_REQUEST['delete_selected_attendance_teacher'] ) ) {
		if ( ! empty( $_REQUEST['id'] ) ) {
			foreach ( $_REQUEST['id'] as $id ) {
				$result = mjschool_delete_attendance_teacher( intval( $id ) );
			}
		}
		if ( $result ) {
			$nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&_wpnonce=' . esc_attr( $nonce ) . '&message=2' ) );
			exit;
		}
	}
	?>
	<div class="table-div"><!-- PANEL BODY DIV START. -->
		<div class="table-responsive"><!-- TABLE RESPONSIVE DIV START. -->
			<div class="btn-place"></div>
			<form id="mjschool-common-form" name="mjschool-common-form" method="post">
				<table id="teacher_attendance_list" class="display" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class=" multiple_select select_all" name="select_all"></th>
							<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance Status', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ( $attendence_data as $retrieved_data ) {
							$member_data = get_userdata( $retrieved_data->user_id );
							$class       = mjschool_get_class_name_by_teacher_id( $member_data->data->ID );
							if ( ! empty( $member_data->parent_id ) ) {
								$parent_data = get_userdata( $member_data->parent_id );
							}
							if ( $i === 10 ) {
								$i = 0;
							}
							if ( $i === 0 ) {
								$color_class_css = 'mjschool-class-color0';
							} elseif ( $i === 1 ) {
								$color_class_css = 'mjschool-class-color1';
							} elseif ( $i === 2 ) {
								$color_class_css = 'mjschool-class-color2';
							} elseif ( $i === 3 ) {
								$color_class_css = 'mjschool-class-color3';
							} elseif ( $i === 4 ) {
								$color_class_css = 'mjschool-class-color4';
							} elseif ( $i === 5 ) {
								$color_class_css = 'mjschool-class-color5';
							} elseif ( $i === 6 ) {
								$color_class_css = 'mjschool-class-color6';
							} elseif ( $i === 7 ) {
								$color_class_css = 'mjschool-class-color7';
							} elseif ( $i === 8 ) {
								$color_class_css = 'mjschool-class-color8';
							} elseif ( $i === 9 ) {
								$color_class_css = 'mjschool-class-color9';
							}
							$uid = $retrieved_data->user_id;
							?>
							<tr>
								<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->attendence_id ); ?>"></td>
								<td class="mjschool-user-image mjschool-width-50px-td mjschool-cursor-pointer">
									<a href="<?php echo esc_url( '?page=mjschool_teacher&tab=view_teacher&action=view_teacher&teacher_id=' . mjschool_encrypt_id( $uid ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ); ?>">
										<?php
										$umetadata = mjschool_get_user_image( $uid );
                                        
                                        if (empty($umetadata ) ) {
                                            echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
                                        } else {
                                            echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
                                        }
                                        
										?>
									</a>
								</td>
								<td class="name">
									<?php
									if ( $member_data->roles[0] === 'student' ) {
										echo esc_html( $member_data->display_name );
									} else {
										echo esc_html( $member_data->display_name );
									}
									?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr( 'Teacher Name', 'mjschool' ); ?>"></i>
								</td>
								<td class="name">
									<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendence_date ) ); ?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr( 'Date', 'mjschool' ); ?>"></i>
								</td>
								<td class="name">
									<?php
									$day = date( 'l', strtotime( $retrieved_data->attendence_date ) );
									echo esc_html( $day, 'mjschool' );
									?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
								</td>
								<td class="name">
									<?php echo esc_html( mjschool_get_display_name( $retrieved_data->attend_by ) ); ?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
								</td>
								<td class="name">
									<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
									<span style="color:<?php echo esc_attr( $status_color ); ?>;"><?php echo esc_html( $retrieved_data->status ); ?></span>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
								</td>
								<td class="name">
									<?php
									if ( ! empty( $retrieved_data->comment ) ) {
										$comment       = $retrieved_data->comment;
										$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
										echo esc_html( $grade_comment );
									} else {
										esc_html_e( 'Not Provided', 'mjschool' );
									}
									?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
								</td>
							</tr>
							<?php
							++$i;
						}
						?>
					</tbody>
				</table>
				<div class="mjschool-print-button pull-left">
					<button class="mjschool-btn-sms-color mjschool-button-reload">
						<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select-checkbox select_all mjchool_margin_top_0px" value="">
						<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
					</button>
                    
                    <button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_attendance_teacher" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
					<input type="hidden" name="filtered_date_type" value="<?php echo esc_attr( $date_type ); ?>" />
					<input type="hidden" name="filtered_member_id" value="<?php echo esc_attr( $member_id ); ?>" />
                    <button data-toggle="tooltip" title="<?php esc_attr_e( 'Export Attendance', 'mjschool' ); ?>" name="export_teacher_attendance_in_csv" class="mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
                    
				</div>
			</form>
		</div><!-- TABLE RESPONSIVE DIV END. -->
	</div>
	<?php
} else {
	?>
	<div class="mjschool-no-data-list-div row">
		<div class="offset-md-4 col-md-4">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&tab1=teacher_attendences' ) ); ?>">
                
                <img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
                
			</a>
			<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
				<span class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}
?>