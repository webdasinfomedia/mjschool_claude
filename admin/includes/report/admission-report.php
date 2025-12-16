<?php

/**
 * Displays and processes the Admission Report page in the MJ School Management plugin.
 *
 * This template handles:
 * - Validation of the security nonce for the admission report tab.
 * - Rendering of the date filter form (today, this week, last week, custom period, etc.).
 * - Initializing datepickers for custom date range selection.
 * - Fetching admission records based on the selected date range.
 * - Rendering the Admission Report table with DataTables (search, CSV export, print).
 * - Displaying student details such as admission number, name, email, DOB, admission date, gender, and mobile number.
 * - Showing a fallback "No Data" image when no admission records are found.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for admission report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<form method="post" id="student_attendance">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="date_type"><?php esc_html_e( 'Date Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select class="mjschool-line-height-30px form-control date_type validate[required]" id="date_type" name="date_type" autocomplete="off">
							<option value=""><?php esc_html_e( 'Select', 'mjschool' ); ?></option>
							<option value="today" <?php selected( $selected_date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
							<option value="this_week" <?php selected( $selected_date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
							<option value="last_week" <?php selected( $selected_date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
							<option value="this_month" <?php selected( $selected_date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
							<option value="last_month" <?php selected( $selected_date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
							<option value="last_3_month" <?php selected( $selected_date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
							<option value="last_6_month" <?php selected( $selected_date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
							<option value="last_12_month" <?php selected( $selected_date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
							<option value="this_year" <?php selected( $selected_date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
							<option value="last_year" <?php selected( $selected_date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
							<option value="period" <?php selected( $selected_date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
						</select>
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
					<div class="col-md-3 mb-2">
						<input type="submit" name="admission_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<?php
// -------------- ADMISSION REPORT - DATA. ---------------//
if ( isset( $_REQUEST['admission_report'] ) ) {
	$date_type = sanitize_text_field( $_POST['date_type'] );
	if ( $date_type === 'period' ) {
		$start_date = sanitize_text_field( $_REQUEST['start_date'] );
		$end_date   = sanitize_text_field( $_REQUEST['end_date'] );
	} else {
		$result     = mjschool_all_date_type_value( $date_type );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
	}
	$admission = mjschool_get_all_admission_by_start_date_to_end_date( $start_date, $end_date );
} else {
	$start_date = date( 'Y-m-d' );
	$end_date   = date( 'Y-m-d' );
	$admission  = mjschool_get_all_admission_by_start_date_to_end_date( $start_date, $end_date );
}
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	if ( ! empty( $admission ) ) {
		?>
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
				<h4 class="mjschool-report-header"><?php esc_html_e( 'Admission Report', 'mjschool' ); ?></h4>
			</div>
		</div>
		<div class="table-responsive">
			<div class="btn-place"></div>
			<form id="mjschool-form-admisssion" name="mjschool-form-admisssion" method="post">
				<table id="mjschool-admission-list-report" class="display mjschool-admission-report-tbl" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th><?php esc_html_e( 'Admission No', 'mjschool' ); ?>.</th>
							<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Email Id', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $admission as $retrieved_data ) {
							$student_data = get_userdata( $retrieved_data->ID );
							?>
							<tr>
								<td>
									<?php
									if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) {
										echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) );
									}
									?>
									<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Number', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php echo esc_html( $student_data->display_name ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php echo esc_html( $retrieved_data->user_email ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Email ID', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php echo esc_html( $student_data->admission_date ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Date', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php
									if ( $student_data->gender === 'male' ) {
										echo esc_attr__( 'Male', 'mjschool' );
									} elseif ( $student_data->gender === 'female' ) {
										echo esc_attr__( 'Female', 'mjschool' );
									}
									?>
									<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
								</td>
								<td>
									<?php echo esc_html( $student_data->mobile_number ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
								</td>
							</tr>
							<?php
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