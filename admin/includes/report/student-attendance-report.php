<?php

/**
 * Attendance Report Tabs Controller.
 *
 * Handles the tab navigation and routing of different attendance report views
 * including Monthly Report, Daily Report, Datatable Report, and Graph Report.
 *
 * This file:
 * - Determines the active attendance report tab.
 * - Generates the tab navigation UI.
 * - Loads the corresponding report template based on the active tab.
 * - Implements nonce validation for secure tab switching.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 * 
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'monthly_attendance_report';
?>
<?php $nonce = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
	<li class="<?php if ( $active_tab === 'monthly_attendance_report' ) { ?> active<?php } ?>">
		<a href="?page=mjschool_report&tab=attendance_report&tab1=student_attendance_report&tab2=monthly_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'monthly_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Monthly Attendance Report', 'mjschool' ); ?></a> 
	</li>
	<li class="<?php if ( $active_tab === 'daily_attendance_report' ) { ?> active<?php } ?>">
		<a href="?page=mjschool_report&tab=attendance_report&tab1=student_attendance_report&tab2=daily_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'daily_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Daily Attendance Report', 'mjschool' ); ?></a> 
	</li>
	<li class="<?php if ( $active_tab === 'attendance_report_datatable' ) { ?> active<?php } ?>">
		<a href="?page=mjschool_report&tab=attendance_report&tab1=student_attendance_report&tab2=attendance_report_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'attendance_report_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report In Datatable', 'mjschool' ); ?></a> 
	</li> 
	<li class="<?php if ( $active_tab === 'attendance_report_graph' ) { ?> active<?php } ?>">
		<a href="?page=mjschool_report&tab=attendance_report&tab1=student_attendance_report&tab2=attendance_report_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'attendance_report_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report In Graph', 'mjschool' ); ?></a> 
	</li>
</ul>
<?php
if ( $active_tab === 'monthly_attendance_report' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/monthly-attendence-report.php';
}
if ( $active_tab === 'daily_attendance_report' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/daily-attendance-report.php';
}
if ( $active_tab === 'attendance_report_datatable' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/attendance-report-datatable.php';
}
if ( $active_tab === 'attendance_report_graph' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/attendance-report-graph.php';
}
?>