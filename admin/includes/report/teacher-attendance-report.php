<?php

/**
 * Displays navigation tabs for Teacher Attendance Reports and loads
 * the appropriate report file (datatable or graph) depending on
 * the selected tab.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab2'] ) ? $_GET['tab2'] : 'teacher_attendance_report_datatable';
?>
<?php $nonce = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
	<li class="<?php if ( $active_tab === 'teacher_attendance_report_datatable' ) { ?>active<?php } ?>">
		<a href="?page=mjschool_report&tab=attendance_report&tab1=teacher_attendance_report&tab2=teacher_attendance_report_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_attendance_report_datatable' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Attendance Report In Datatable', 'mjschool' ); ?>
		</a> 
	</li> 
	<li class="<?php if ( $active_tab === 'teacher_attendance_report_graph' ) {?>active<?php } ?>">			
		<a href="?page=mjschool_report&tab=attendance_report&tab1=teacher_attendance_report&tab2=teacher_attendance_report_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_attendance_report_graph' ? 'active' : ''; ?>">
			<?php esc_html_e( 'Attendance Report In Graph', 'mjschool' ); ?>
		</a> 
	</li>
</ul>
<?php
if ( $active_tab === 'teacher_attendance_report_datatable' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-attendance-report-datatable.php';
}
if ( $active_tab === 'teacher_attendance_report_graph' ) {
	require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-attendance-report-graph.php';
}
?>