<?php

/**
 * Teacher Attendance Report - Graph View
 *
 * Displays the teacher attendance report in a graphical (Google Charts)
 * format based on selected date ranges such as today, week, month, and year.
 * This file handles nonce validation, fetches attendance data from the
 * database, prepares chart arrays, initializes Google Charts, and renders
 * the output on the admin page.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// Check nonce for teacher attendance report graph tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="row">
		<div class="col-md-3 input">
			<select class="mjschool-line-height-30px form-control teacher_graph date_type validate[required]" name="date_type" autocomplete="off">
				<option value="today"><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
				<option value="this_week"><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
				<option value="last_week"><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
				<option value="this_month" 	selected><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
				<option value="last_month"><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
				<option value="last_3_month"><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
				<option value="last_6_month"><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
				<option value="last_12_month"><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
				<option value="this_year"><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
				<option value="last_year"><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
			</select>
		</div>
	</div>
	<div class="events1" id="teacher_graph_id">
		<?php
		global $wpdb;
		$table_attendance = $wpdb->prefix . 'mjschool_attendence';
		if ( isset( $_REQUEST['view_attendance'] ) ) {
			$sdate = sanitize_text_field( $_REQUEST['sdate'] );
			$edate = sanitize_text_field( $_REQUEST['edate'] );
		} else {
			$sdate = date( 'Y-m-d', strtotime( 'first day of this month' ) );
			$edate = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_2      = $wpdb->get_results(
			"SELECT  at.user_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from $table_attendance as at where `attendence_date` BETWEEN '$sdate' AND '$edate' AND at.user_id AND at.role_name = 'teacher' GROUP BY at.user_id"
		);
		$chart_array   = array();
		$chart_array[] = array( esc_attr__( 'teacher', 'mjschool' ), esc_attr__( 'Present', 'mjschool' ), esc_attr__( 'Absent', 'mjschool' ) );
		if ( ! empty( $report_2 ) ) {
			foreach ( $report_2 as $result ) {
				$class_id      = mjschool_get_user_name_by_id( $result->user_id );
				$chart_array[] = array( "$class_id", (int) $result->Present, (int) $result->Absent );
			}
		}
		$options = array(
			'title'          => esc_attr__( 'This Month Attendance Report', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#4e5e6a',
				'fontSize' => 16,
				'bold'     => false,
				'italic'   => false,
				'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
			),
			'legend'         => array(
				'position'  => 'right',
				'textStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
				),
			),
			'hAxis'          => array(
				'title'          => esc_attr__( 'Teacher', 'mjschool' ),
				'titleTextStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 16,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
				),
				'textStyle'      => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
				),
				'maxAlternation' => 2,
			),
			'vAxis'          => array(
				'title'          => esc_attr__( 'No. of Days', 'mjschool' ),
				'minValue'       => 0,
				'maxValue'       => 4,
				'format'         => '#',
				'titleTextStyle' => array(
					'color'    => '#4e5e6a',
					'fontSize' => 16,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
				),
				'textStyle'      => array(
					'color'    => '#4e5e6a',
					'fontSize' => 13,
					'bold'     => false,
					'italic'   => false,
					'fontName' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;',
				),
			),
			'colors'         => array( '#5840bb', '#f25656' ),
		);
		
		$GoogleCharts = new GoogleCharts();
		if ( ! empty( $report_2 ) ) {
			$chart = $GoogleCharts->load( 'column', 'mjschool-chart-div-last-month' )->get( $chart_array, $options );
		} else {
			 ?>
			<div class="mjschool-calendar-event-new"> 
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
			</div>
			<?php 
		}
		if ( isset( $report_2 ) && count( $report_2 ) > 0 ) {
			?>
			<div id="mjschool-chart-div-last-month" class="w-100 h-500-px"></div>
			<!-- Javascript. -->
			<script type="text/javascript">
				"use strict";
				<?php echo wp_kses_post( $chart ); ?>
			</script>
			<?php
		}
		?>
	</div>
</div>