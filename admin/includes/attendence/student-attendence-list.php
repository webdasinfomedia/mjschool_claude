<?php
/**
 * Admin Student Attendance List Page
 *
 * This file displays and manages the student attendance list in the admin dashboard.
 * It provides date-based filters, class filters, and supports CRUD operations,
 * including viewing, exporting, importing, and deleting attendance records.
 *
 * Additionally, it integrates jQuery DataTables for enhanced UI features such as
 * sorting, searching, and bulk actions, while maintaining WordPress security standards.
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

// Check nonce for student attendence list tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'mjschool_student_attendance_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content mjschool-max-height-overflow">
		<div class="modal-content">
			<div class="mjschool-category-list">
			</div>
		</div>
	</div>
</div>
<form method="post" id="attendance_list" class="attendance_list">
	<div class="form-body mjschool-user-form mjschool-margin-top-15px">
		<div class="row">
			<div class="col-md-3 mb-3 input">
				<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
				<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
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
				<label class="ml-1 mjschool-custom-top-label top" for="mjschool-attendance-class-list-id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
				<?php
				if ( isset( $_POST['class_id'] ) ) {
					$classval = sanitize_text_field(wp_unslash($_POST['class_id']));
				} else {
					$classval = '';
				}
				?>
				<select name="class_id" id="mjschool-attendance-class-list-id" class="form-control mjschool-max-width-100px">
					<option value="all class"><?php esc_html_e( 'All Class', 'mjschool' ); ?></option>
					<?php
					foreach ( mjschool_get_all_class() as $classdata ) {
						?>
						<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classval, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
						<?php
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
			<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_attendance_list_nonce' ) ); ?>">
			<div class="col-md-3 mb-2">
				<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
			</div>
		</div>
	</div>
</form>
<div class="clearfix"></div>
<?php
if ( isset( $_REQUEST['view_attendance'] ) ) {
	if (! isset($_POST['security']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['security'])), 'mjschool_attendance_list_nonce')) {
		wp_die(esc_html__('Security check failed.', 'mjschool'));
	}
	$date_type       = sanitize_text_field(wp_unslash($_POST['date_type']));
	$class_id        = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
	$start_date      = '';
	$end_date        = '';
	$attendence_data = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
} else {
	$class_id        = '';
	$date_type       = '';
	$start_date      = date( 'Y-m-d', strtotime( 'first day of this month' ) );
	$end_date        = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	$attendence_data = mjschool_get_student_attendence_beetween_satrt_date_to_enddate( $start_date, $end_date, $class_id, $date_type );
}
if ( $start_date > $end_date ) {
	echo '<script type="text/javascript">alert( "' . esc_html__( 'End Date should be greater than the Start Date', 'mjschool' ) . '");</script>';
}	
if ( ! empty( $attendence_data ) ) {
	if ( isset( $_REQUEST['delete_selected_attendance'] ) ) {
		$nonce = wp_create_nonce( 'mjschool_teacher_attendance_tab' );
		if ( ! empty( $_REQUEST['id'] ) ) {
			foreach ( $_REQUEST['id'] as $id ) {
				$result = mjschool_delete_attendance( intval( $id ) );
			}
		}
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&_wpnonce='. $nonce .'&message=2' ) );
			die();
		}
	}
	?>
	<div class="table-div"><!--  Start panel body div. -->
		<div class="table-responsive"><!-- TABLE RESPONSIVE DIV START. -->
			<div class="btn-place"></div>
			<form id="mjschool-common-form" name="mjschool-common-form" method="post">
				<table id="attend_list" class="display" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class=" multiple_select select_all" name="select_all"></th>
							<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance Status', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Attendance With QR Code', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 0;
						foreach ( $attendence_data as $retrieved_data ) {
							if ( isset( $retrieved_data->class_id ) && $retrieved_data->class_id ) {
								$class_section_sub_name = mjschool_get_class_section_subject( $retrieved_data->class_id, $retrieved_data->section_id, $retrieved_data->sub_id );
								$member_data            = get_userdata( $retrieved_data->user_id );
								$created_by             = get_userdata( $retrieved_data->attend_by );
								if ( ! empty( $member_data->parent_id ) ) {
									$parent_data = get_userdata( $member_data->parent_id );
								}
								?>
								<tr>
									<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->attendance_id ); ?>"></td>
									<td class="mjschool-user-image mjschool-width-50px-td">
										<a href="<?php echo esc_url( '?page=mjschool_student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $member_data->ID ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ); ?>">
											<?php
											$umetadata = mjschool_get_user_image( $member_data->ID );
                                             
                                            if (empty($umetadata ) ) {
                                                echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
                                            } else {
                                                echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
                                            }
                                            
											?>
										</a>
									</td>
									<td class="name">
										<a href="<?php echo esc_url( '?page=mjschool_student&tab=view_student&action=view_student&student_id=' . mjschool_encrypt_id( $member_data->ID ) . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ); ?>">
											<?php
											if ( ! empty( $member_data->ID ) ) {
												echo esc_html( mjschool_student_display_name_with_roll( $member_data->ID ) );
											} else {
												esc_html_e( 'Not Provided', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
										</a>
									</td>
									<td class="name">
										<?php
										$allowed_tags = array(
											'b' => array(),
										);
										echo wp_kses( $class_section_sub_name, $allowed_tags );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendance_date ) ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php
										$day = date( 'l', strtotime( $retrieved_data->attendance_date ) );
										echo esc_html( $day );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
										<span style="color:<?php echo esc_attr( $status_color ); ?>;"> <?php echo esc_html( $retrieved_data->status ); ?> </span>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php echo esc_html( $created_by->display_name ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php
										if ( $retrieved_data->attendence_type === 'QR' ) {
											esc_html_e( 'Yes', 'mjschool' );
										} else {
											esc_html_e( 'No', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance With QR Code', 'mjschool' ); ?>"></i>
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
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html( 'Comment', 'mjschool' ); } ?>"></i>
									</td>
								</tr>
								<?php
							}
							++$i;
						}
						?>
					</tbody>
				</table>
				<div class="mjschool-print-button pull-left">
					<button class="mjschool-btn-sms-color mjschool-button-reload">
						<input type="checkbox" name="" class="mjschool-sub-chk select-checkbox select_all mjchool_margin_top_0px" value="" >
						<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
					</button>
                    
                    <button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected_attendance" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
					<input type="hidden" name="filtered_date_type" value="<?php echo esc_attr( $date_type ); ?>" />
					<input type="hidden" name="filtered_class_id" value="<?php echo esc_attr( $class_id ); ?>" />
                    <button data-toggle="tooltip" title="<?php esc_attr_e( 'Export Attendance', 'mjschool' ); ?>" name="export_attendance_in_csv" class="mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-export-csv.png"); ?>"></button>
                    <button data-toggle="tooltip" title="<?php esc_attr_e( 'Import Attendance', 'mjschool' ); ?>" type="button" class="import_attendance_popup mjschool-export-import-csv-btn mjschool-custom-padding-0"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-import-csv.png"); ?>"></button>
                    
				</div>
			</form>
		</div><!-- TABLE RESPONSIVE DIV END. -->
	</div>
	<?php
} else {
	?>
	<div class="mjschool-no-data-list-div row">
		<div class="offset-md-2 col-md-4">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&tab1=subject_attendence' ) ); ?>">
                
                <img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
                
			</a>
			<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
				<span class="mjschool-no-data-list-label">
					<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
				</span>
			</div>
		</div>
		<div class="col-md-4">
			<a data-toggle="tooltip" name="import_csv" type="button" class="import_attendance_popup">
                
                <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-Import-list.png"); ?>">
                
			</a>
			<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
				<span class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to import CSV.', 'mjschool' ); ?></span>
			</div>
		</div>
	</div>
	<?php
}
?>