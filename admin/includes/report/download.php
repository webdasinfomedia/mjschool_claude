<?php

/**
 * Generates and downloads the attendance report CSV file.
 *
 * This script collects attendance data between a given start and end date,
 * exports it into a CSV format, saves it temporarily in the plugin's
 * reports directory, and forces the CSV download for the user.
 *
 * Data included per class:
 * - Class Name
 * - Present Students Count
 * - Absent Students Count
 * - Total Students Count
 *
 * The script supports manual date range submission via POST request.
 * If no date is provided, it defaults to the current date.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-panel-body clearfix">
	<?php
	if ( isset( $_REQUEST['download_attendance'] ) ) {
		$start_date = $_POST['sdate'];
		$end_date   = $_POST['edate'];
	} else {
		$start_date = date( 'Y-m-d' );
		$end_date   = date( 'Y-m-d' );
	}
	$header   = array();
	$header[] = 'Class Name';
	$header[] = 'Present Student';
	$header[] = 'Absent Student';
	$header[] = 'Total Student';
	$filename = 'export/mjschool-export-attendance.csv';
	$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
	fputcsv( $fh, $header );
	foreach ( mjschool_get_all_class() as $classdata ) {
		$class_id      = $classdata['class_id'];
		$row           = array();
		$total_present = mjschool_view_attendance_report_for_start_date_enddate_total_present( $start_date, $end_date, $class_id );
		$total_absent  = mjschool_view_attendance_report_for_start_date_enddate_absent( $start_date, $end_date, $class_id );
		$total         = mjschool_view_attendance_report_for_start_date_enddate_total( $class_id );
		$classname     = mjschool_get_class_name( $class_id );
		$row[]         = $classname;
		$row[]         = $total_present;
		$row[]         = $total_absent;
		$row[]         = $total;
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
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', filemtime( $file ) ) . ' GMT' );
	header( 'Cache-Control: private', false );
	header( 'Content-Type: ' . $mime );
	header( 'Content-Disposition: attachment; filename="' . basename( $file ) . '"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Connection: close' );
	readfile( $file );
	die();
	?>
</div>