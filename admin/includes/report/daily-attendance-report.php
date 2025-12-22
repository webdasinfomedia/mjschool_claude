<?php
/**
 * Daily Attendance Report Template.
 *
 * Renders the daily attendance reporting interface, including:
 * - Date selection form
 * - Attendance summary table (present, absent, percentages)
 * - CSV export functionality
 *
 * Functionality:
 * - Reads attendance for all classes for a selected date.
 * - Calculates present/absent percentages.
 * - Allows CSV download of the generated report.
 * - Integrates with DataTables for search, sorting, exporting.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Check nonce for daily attendance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<form method="post">
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group input">
							<div class="col-md-12 form-control">
								<input type="text" id="sdate" class="form-control" name="date" value="<?php if ( isset( $_REQUEST['date'] ) ) { echo esc_attr( mjschool_get_date_in_input_box( $_REQUEST['date'] ) ); } else { echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); } ?>" readonly>
								<label for="sdate"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
							</div>
						</div>
					</div>
					<div class="col-md-2">
						<input type="submit" name="daily_attendance" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	// ----Download Daily attendance Report in CSV -- start. ---/
	if ( isset( $_POST['download_daily_attendance'] ) ) {
		$daily_date = $_POST['daily_date'];
		$header     = array();
		$header[]   = 'Class Name';
		$header[]   = 'Present Student';
		$header[]   = 'Absent Student';
		$header[]   = 'Present %';
		$header[]   = 'Absent %';
		$header[]   = 'Total Student';
		$filename   = 'export/mjschool-export-attendance.csv';
		$fh         = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
		fputcsv( $fh, $header );
		foreach ( mjschool_get_all_class() as $classdata ) {
			$row           = array();
			$class_id      = $classdata['class_id'];
			$classname     = mjschool_get_class_name( $class_id );
			$total         = mjschool_view_attendance_report_for_start_date_enddate_total( $class_id );
			$total_present = mjschool_daily_attendance_report_for_date_total_present( $daily_date, $class_id );
			$total_absent  = mjschool_daily_attendance_report_for_date_total_absent( $daily_date, $class_id );
			$total_pre_abs = $total_present + $total_absent;
			if ( $total_present === '0' && $total_absent === '0' ) {
				$present_per = 0;
				$absent_per  = 0;
			} else {
				$present_per = ( $total_present * 100 ) / $total_pre_abs;
				$absent_per  = ( $total_absent * 100 ) / $total_pre_abs;
			}
			$row[] = $classname;
			$row[] = $total_present;
			$row[] = $total_absent;
			$row[] = $present_per;
			$row[] = $absent_per;
			$row[] = $total;
			fputcsv( $fh, $row );
		}
		fclose( $fh );
		// Download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-export-attendance.csv'; // File location.
		$mime = 'text/plain';
		header( 'Content-Type:application/force-download' );
		header( 'Pragma: public' );       // Required.
		header( 'Expires: 0' );           // No cache.
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Last-Modified: ' . date( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
		header( 'Cache-Control: private', false );
		header( 'Content-Type: ' . $mime );
		header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		// header( 'Content-Length: '.filesize($file_name ) );      // Provide file size.
		header( 'Connection: close' );
		readfile( $file );
		die();
	}
	// ---- Download Daily attendance Report in CSV -- End. ---/
	if ( isset( $_REQUEST['daily_attendance'] ) ) {
		$daily_date = $_POST['date'];
	} else {
		$daily_date = date( 'Y-m-d' );
	}
	?>
	<script type="text/javascript">
		(function(jQuery) {
			"use strict";
			jQuery(function() {
				var table = jQuery( '#mjschool-daily-attendance-list-report' ).DataTable({
					initComplete: function(settings, json) {
						jQuery( ".mjschool-print-button" ).css({
							"margin-top": "-55px"
						});
					},
					order: [[2, "desc"]],
					dom: 'lifrtp',
					buttons: [
						{
							extend: 'csv',
							text: '<?php esc_html_e( "csv", "mjschool" ); ?>',
							title: '<?php esc_html_e( "Student Attendance Report", "mjschool" ); ?>'
						},
						{
							extend: 'print',
							text: '<?php esc_html_e( "Print", "mjschool" ); ?>',
							title: '<?php esc_html_e( "Student Attendance Report", "mjschool" ); ?>'
						}
					],
					aoColumns: [
						{ bSortable: true },
						{ bSortable: true },
						{ bSortable: true },
						{ bSortable: true },
						{ bSortable: true },
						{ bSortable: true }
					],
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				});
				// Add placeholder text.
				jQuery('.dataTables_filter input')
					.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
					.attr("id", "datatable_search")
					.attr("name", "datatable_search");
				// Place export buttons.
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			});
		})(jQuery);
	</script>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
				<h4 class="mjschool-report-header"><?php esc_html_e( 'Daily Attendance Report', 'mjschool' ); ?></h4>
			</div>
		</div>
		<div class="table-responsive">
			<div class="btn-place"></div>
			<form id="frm-daily-attendance" name="frm-daily-attendance" method="post">
				<table id="mjschool-daily-attendance-list-report" class="display" cellspacing="0" width="100%">
					<input type="hidden" name="daily_date" value="<?php echo esc_attr( $daily_date ); ?>" />
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Total Present', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Total Absent', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Present', 'mjschool' ); ?><?php esc_html_e( ' %', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Absent', 'mjschool' ); ?><?php esc_html_e( ' %', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Total Student', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							$class_id      = $classdata['class_id'];
							$total         = mjschool_view_attendance_report_for_start_date_enddate_total( $class_id );
							$total_present = mjschool_daily_attendance_report_for_date_total_present( $daily_date, $class_id );
							$total_absent  = mjschool_daily_attendance_report_for_date_total_absent( $daily_date, $class_id );
							$total_pre_abs = $total_present + $total_absent;
							if ( $total_present === '0' && $total_absent === '0' ) {
								$present_per = 0;
								$absent_per  = 0;
							} else {
								$present_per = ( $total_present * 100 ) / $total_pre_abs;
								$absent_per  = ( $total_absent * 100 ) / $total_pre_abs;
							}
							?>
							<tr>
								<td><?php echo esc_html( mjschool_get_class_name( $class_id ) ); ?> </td>
								<td><?php echo esc_html( round( $total_present ) ); ?></td>
								<td><?php echo esc_html( round( $total_absent ) ); ?></td>
								<td><?php echo esc_html( round( $present_per ) ); ?>%</td>
								<td><?php echo esc_html( round( $absent_per ) ); ?>%</td>
								<td><?php echo esc_html( $total ); ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<div class="mjschool-print-button pull-left">
					<button data-toggle="tooltip" title="<?php esc_attr_e( 'Download Report in CSV', 'mjschool' ); ?>" name="download_daily_attendance" class="mjschool-attr-download-csv-btn mjschool-custom-padding-0"><?php esc_html_e( 'Download CSV', 'mjschool' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>