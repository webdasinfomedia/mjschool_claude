<?php

/**
 * Student Book Issue Report.
 *
 * This file handles the display and filtering of the Library Book Issue
 * Report. Administrators can filter by class and date range (presets or custom
 * period). The resulting list displays issued books, student details, issue/
 * return dates, and applicable fines.
 *
 * Features:
 * - Class-based and date-type-based filtering.
 * - Custom date range selection using jQuery UI datepicker.
 * - Retrieves book-issue records via plugin helper functions.
 * - DataTables used for sorting, export, and search.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for library report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_library_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<?php
		$class_id = '';
		?>
		<form method="post" id="student_book_issue_report">
			<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-6 mb-3 input mjschool-rtl-margin-bottom-0px">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="form-control validate[required]">
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = $_REQUEST['class_id'];
							}
							?>
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
					<?php $selected_date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
					<div class="col-md-6 mb-3 input">
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
				</div>
				<div class="col-md-6 mb-2">
					<input type="submit" name="library_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
				</div>
			</div>
		</form>
	</div>
</div>
<?php
// -------------- ADMISSION REPORT - DATA. ---------------//
$class_id      = '';
$class_section = '';
$date_type     = '';
if ( isset( $_REQUEST['library_report'] ) ) {
	$date_type = sanitize_text_field( $_POST['date_type'] );
	$class_id  = sanitize_text_field( $_POST['class_id'] );
	if ( $date_type === 'period' ) {
		$start_date = sanitize_text_field( $_REQUEST['start_date'] );
		$end_date   = sanitize_text_field( $_REQUEST['end_date'] );
	} else {
		$result     = mjschool_all_date_type_value( $date_type );
		$response   = json_decode( $result );
		$start_date = $response[0];
		$end_date   = $response[1];
	}
	$book_issue_data = mjschool_check_book_issued_by_class_id_and_date( $class_id, $start_date, $end_date );
} else {
	$start_date      = date( 'Y-m-d' );
	$end_date        = date( 'Y-m-d' );
	$book_issue_data = mjschool_check_book_issued_by_start_date_and_end_date( $start_date, $end_date );
}
?>
<script type="text/javascript">
	(function (jQuery) {
		"use strict";
		jQuery(document).ready(function () {
			var table = jQuery( '#mjschool-book-issue-list-report' ).DataTable({
				order: [[2, "desc"]],
				dom: 'lifrtp',
				buttons: [
					{
						extend: 'csv',
						text: '<?php echo esc_html__( 'csv', 'mjschool' ); ?>',
						title: '<?php echo esc_html__( 'Book Issue Report', 'mjschool' ); ?>'
					},
					{
						extend: 'print',
						text: '<?php echo esc_html__( 'Print', 'mjschool' ); ?>',
						title: '<?php echo esc_html__( 'Book Issue Report', 'mjschool' ); ?>'
					}
				],
				aoColumns: [
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true },
					{ "bSortable": true }
				],
				language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
			});
			jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>" );
			jQuery( '.btn-place' ).html(table.buttons().container( ) );
		});
	})(jQuery);
</script>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
	<?php
	if ( ! empty( $book_issue_data ) ) {
		?>
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
				<h4 class="mjschool-report-header"><?php esc_html_e( 'Book Issue Report', 'mjschool' ); ?></h4>
			</div>
		</div>
		<div class="table-responsive">
			<div class="btn-place"></div>
			<form id="mjschool-form-admisssion" name="mjschool-form-admisssion" method="post">
				<table id="mjschool-book-issue-list-report" class="display mjschool-admission-report-tbl" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Book Number', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'ISBN', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Admission No', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Return Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Accept Return Date', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Fine', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $book_issue_data as $retrieved_data ) {
							?>
							<tr>
								<td><?php echo esc_html( mjschool_get_book_name( $retrieved_data->book_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
								<td><?php echo esc_html( mjschool_get_book_number( $retrieved_data->book_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Number', 'mjschool' ); ?>"></i></td>
								<td><?php echo esc_html( mjschool_get_ISBN( $retrieved_data->book_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'ISBN', 'mjschool' ); ?>"></i></td>
								<td> <?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i> </td>
								<td>
									<?php
									$admission_no = get_user_meta( $retrieved_data->student_id, 'admission_no', true );
									if ( ! empty( $admission_no ) ) {
										echo esc_html( get_user_meta( $retrieved_data->student_id, 'admission_no', true ) );
									}
									?>
									<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission No', 'mjschool' ); ?>"></i>
								</td>
								<td> <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i> </td>
								<td> <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Return Date', 'mjschool' ); ?>"></i> </td>
								<td> <?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->actual_return_date ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Return Date', 'mjschool' ); ?>"></i> </td>
								<td>
									<?php echo esc_html( mjschool_get_currency_symbol() ) . '' . number_format( $retrieved_data->fine, 2, '.', '' ); ?>
									<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fine', 'mjschool' ); ?>"></i>
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
            <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
        </div>
        <?php  
	}
	?>
</div>