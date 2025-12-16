<?php
/**
 * Attendance Report Graph Template.
 *
 * Renders the attendance graph filter UI and generates a Google Column Chart
 * displaying student attendance (Present/Absent) for a selected date range.
 * The chart is generated based on the logged-in user's role (teacher/admin)
 * and attendance records retrieved from the database.
 *
 * Features:
 * - Validates nonce for security.
 * - Allows selecting predefined or custom date range.
 * - Loads attendance data for each class.
 * - Generates a Google Charts column graph.
 * - Displays a “No Data” image when records are unavailable.
 *
 * @global object $school_obj Logged-in user object with role information.
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Check nonce for attendance report graph tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="row">
		<div class="col-md-3 input">
			<?php $date_type = isset( $_POST['date_type'] ) ? $_POST['date_type'] : ''; ?>
			<select class="mjschool-line-height-30px form-control student_graph date_type validate[required]" name="date_type" autocomplete="off">
				<option value="today" <?php selected( $date_type, 'today' ); ?>><?php esc_html_e( 'Today', 'mjschool' ); ?></option>
				<option value="this_week" <?php selected( $date_type, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'mjschool' ); ?></option>
				<option value="last_week" <?php selected( $date_type, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'mjschool' ); ?></option>
				<option value="this_month" <?php selected( $date_type, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'mjschool' ); ?></option>
				<option value="last_month" <?php selected( $date_type, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'mjschool' ); ?></option>
				<option value="last_3_month" <?php selected( $date_type, 'last_3_month' ); ?>><?php esc_html_e( 'Last 3 Months', 'mjschool' ); ?></option>
				<option value="last_6_month" <?php selected( $date_type, 'last_6_month' ); ?>><?php esc_html_e( 'Last 6 Months', 'mjschool' ); ?></option>
				<option value="last_12_month" <?php selected( $date_type, 'last_12_month' ); ?>><?php esc_html_e( 'Last 12 Months', 'mjschool' ); ?></option>
				<option value="this_year" <?php selected( $date_type, 'this_year' ); ?>><?php esc_html_e( 'This Year', 'mjschool' ); ?></option>
				<option value="last_year" <?php selected( $date_type, 'last_year' ); ?>><?php esc_html_e( 'Last Year', 'mjschool' ); ?></option>
				<option value="period" <?php selected( $date_type, 'period' ); ?>><?php esc_html_e( 'Period', 'mjschool' ); ?></option>
			</select>
		</div>
		<div id="date_type_div" class="col-md-6 <?php echo ( $date_type === 'period' ) ? '' : 'date_type_div_none'; ?>">
			<?php
			if ( $date_type === 'period' ) {
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
								dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
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
								dateFormat: "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
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
	<div class="events1" id="student_graph_id">
		<?php
		global $wpdb;
		$table_attendance = $wpdb->prefix . 'mjschool_sub_attendance';
		$table_class      = $wpdb->prefix . 'mjschool_class';
		if ( isset( $_REQUEST['view_attendance'] ) ) {
			$sdate = $_REQUEST['sdate'];
			$edate = $_REQUEST['edate'];
		} else {
			$sdate = date( 'Y-m-d', strtotime( 'first day of this month' ) );
			$edate = date( 'Y-m-d', strtotime( 'last day of this month' ) );
		}
		if ( $school_obj->role === 'teacher' ) {
			$teacher_id   = get_current_user_id();
			$classes      = mjschool_get_class_by_teacher_id( $teacher_id );
			$unique_array = array();
			foreach ( $classes as $class ) {
				$class_id = $class->class_id;
				$query    = "SELECT at.class_id, SUM(CASE WHEN `status` ='Present' THEN 1 ELSE 0 END) AS Present, SUM(CASE WHEN `status` ='Absent' THEN 1 ELSE 0 END) AS Absent FROM $table_attendance AS at JOIN $table_class AS cl ON at.class_id = cl.class_id WHERE `attendance_date` BETWEEN '$sdate' AND '$edate' AND at.class_id = $class_id AND at.role_name = 'student' GROUP BY at.class_id";
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$result       = $wpdb->get_results( $query );
				$unique_array = array_merge( $unique_array, $result );
			}
			$report_2 = array_unique( $unique_array, SORT_REGULAR );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$report_2 = $wpdb->get_results( "SELECT  at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from $table_attendance as at,$table_class as cl where `attendance_date` BETWEEN '$sdate' AND '$edate' AND at.class_id = cl.class_id AND at.role_name = 'student' GROUP BY at.class_id"
			);
		}
		$chart_array   = array();
		$chart_array[] = array( esc_attr__( 'Class', 'mjschool' ), esc_attr__( 'Present', 'mjschool' ), esc_attr__( 'Absent', 'mjschool' ) );
		if ( ! empty( $report_2 ) ) {
			foreach ( $report_2 as $result ) {
				$class_id      = mjschool_get_class_name( $result->class_id );
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
				'title'          => esc_attr__( 'Class', 'mjschool' ),
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
				'title'          => esc_attr__( 'No. of Students', 'mjschool' ),
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
				
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
				
			</div>
			<?php
		}
		if ( isset( $report_2 ) && count( $report_2 ) > 0 ) {
			?>
			<div id="mjschool-chart-div-last-month" class="w-100 h-500-px"></div>
			<!-- Javascript. -->
			<script type="text/javascript">
				(function(jQuery) {
					"use strict";
					jQuery(function() {
						<?php echo wp_kses_post( $chart ); ?>
					});
				})(jQuery);
			</script>
			<?php
		}
		?>
	</div>
</div>