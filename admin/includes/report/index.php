<?php 

/**
 * Reports Module Main View & Controller.
 *
 * This file renders and controls all report-related pages within the MjSchool
 * Management plugin (admin side). It handles user-role access verification,
 * initializes JavaScript components (DataTables, ValidationEngine, Datepickers),
 * loads tab-wise report templates, and prepares chart datasets for Google Charts.
 *
 * Responsibilities:
 * - Verify user access rights for the "Report" module.
 * - Initialize validation, datepickers, and DataTables using jQuery.
 * - Process Student, Teacher, Attendance, Finance, Hostel, and Examination reports.
 * - Prepare chart data arrays for attendance and performance analytics.
 * - Render nested tabs and load respective report partial files.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_view = '1';
} else {
	$user_access      = mjschool_get_user_role_wise_filter_access_right_array( 'report' );
	$user_access_view = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
	}
}
?>
<script type="text/javascript">
	(function (jQuery) {
		"use strict";
		jQuery(document).ready(function () {
			/* ---------------------------
			* Validation Engine Init.
			* --------------------------- */
			// var validationForms = [
			// 	'#failed_report',
			// 	'#student_attendance',
			// 	'#student_book_issue_report',
			// 	'#fee_payment_report',
			// 	'#student_expence_payment',
			// 	'#student_income_expence_payment',
			// 	'#student_income_payment'
			// ];
			// jQuery.each(validationForms, function (_, selector) {
			// 	var $el = jQuery(selector);
			// 	if ($el.length) {
			// 		$el.validationEngine({
			// 			promptPosition: "bottomLeft",
			// 			maxErrorsPerField: 1
			// 		});
			// 	}
			// });
			/* ---------------------------
			* Datepickers.
			* --------------------------- */
			var dateFormat = "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>";
			// jQuery( "#sdate").datepicker({
			// 	dateFormat: dateFormat,
			// 	changeYear: true,
			// 	changeMonth: true,
			// 	maxDate: 0,
			// 	onSelect: function (selected) {
			// 		var dt = new Date(selected);
			// 		jQuery( "#edate").datepicker( "option", "minDate", dt);
			// 	},
			// 	beforeShow: function (textbox, instance) {
			// 		instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
			// 	}
			// });
			// jQuery( "#edate").datepicker({
			// 	dateFormat: dateFormat,
			// 	changeYear: true,
			// 	changeMonth: true,
			// 	maxDate: 0,
			// 	onSelect: function (selected) {
			// 		var dt = new Date(selected);
			// 		jQuery( "#sdate").datepicker( "option", "maxDate", dt);
			// 	},
			// 	beforeShow: function (textbox, instance) {
			// 		instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
			// 	}
			// });
			// jQuery( ".sdate, .edate").datepicker({
			// 	dateFormat: dateFormat,
			// 	changeYear: true,
			// 	changeMonth: true
			// });
			/* ---------------------------
			* DataTable Initializer.
			* --------------------------- */
			function initDataTable(selector, orderCol, orderDir, columns, title) {
				var $el = jQuery(selector);
				if (!$el.length || !$el.DataTable) return;
				var table = $el.DataTable({
					"order": [[orderCol, orderDir.toLowerCase()]],
					"dom": 'lifrtp',
					buttons: [
						{
							extend: 'csv',
							text: '<?php esc_html_e( 'CSV', 'mjschool' ); ?>',
							title: title
						},
						{
							extend: 'print',
							text: '<?php esc_html_e( 'Print', 'mjschool' ); ?>',
							title: title
						}
					],
					"aoColumns": columns,
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				});
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			}
			// Income Expense.
			// initDataTable( '#table_income_expense', 2, 'desc', [
			// 	{ "bSortable": false }, { "bSortable": true },
			// 	{ "bSortable": true }, { "bSortable": true }
			// ], '<?php echo esc_html__( "Income Expense Report", "mjschool" ); ?>' );
			// Another Income Expense table.
			initDataTable( '#tble_income_expense', 2, 'desc', [
				{ "bSortable": false }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true }
			], '<?php echo esc_html__( "Income Expense Report", "mjschool" ); ?>' );
			// Student Attendance.
			initDataTable( '#attendance_list_report', 2, 'desc', [
				{ "bSortable": false }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true },
				{ "bSortable": true }
			], '<?php echo esc_html__( "Attendance Report", "mjschool" ); ?>' );
			// Teacher Attendance.
			initDataTable( '#teacher_attendance_list_report', 2, 'desc', [
				{ "bSortable": false }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true },
				{ "bSortable": true }, { "bSortable": true },
				{ "bSortable": true }
			], '<?php echo esc_html__( "Teacher Attendance Report", "mjschool" ); ?>' );
			/* ---------------------------
			* Global Search Placeholder.
			* --------------------------- */
			// jQuery('.dataTables_filter input')
			// 	.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
			// 	.attr("id", "datatable_search")
			// 	.attr("name", "datatable_search");
		});
	})(jQuery);
</script>
<?php
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'report1';
$obj_marks  = new Mjschool_Marks_Manage();
if ( $active_tab === 'report2' ) {
	$chart_array[] = array( esc_attr__( 'Class', 'mjschool' ), esc_attr__( 'Present', 'mjschool' ), esc_attr__( 'Absent', 'mjschool' ) );
	if ( isset( $_REQUEST['report_2'] ) ) {
		global $wpdb;
		$table_attendance = $wpdb->prefix . 'amjschool_ttendence';
		$table_class      = $wpdb->prefix . 'mjschool_class';
		$sdate            = $_REQUEST['sdate'];
		$edate            = $_REQUEST['edate'];
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
		$report_2 = $wpdb->get_results( "SELECT  at.class_id, SUM(case when `status` ='Present' then 1 else 0 end) as Present, SUM(case when `status` ='Absent' then 1 else 0 end) as Absent from $table_attendance as at,$table_class as cl where `attendence_date` BETWEEN '$sdate' AND '$edate' AND at.class_id = cl.class_id AND at.role_name = 'student' GROUP BY at.class_id" );
		if ( ! empty( $report_2 ) ) {
			foreach ( $report_2 as $result ) {
				$class_id      = mjschool_get_class_name( $result->class_id );
				$chart_array[] = array( "$class_id", (int) $result->Present, (int) $result->Absent );
			}
		}
		$options = array(
			'title'          => esc_attr__( 'Attendance Report', 'mjschool' ),
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
				'title'          => esc_attr__( 'No. of Student', 'mjschool' ),
				'minValue'       => 0,
				'maxValue'       => 5,
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
			'colors'         => array( '#22BAA0', '#f25656' ),
		);
	}
}
if ( $active_tab === 'report3' ) {
	$chart_array[] = array( esc_attr__( 'Teacher', 'mjschool' ), esc_attr__( 'fail', 'mjschool' ) );
	global $wpdb;
	$table_subject         = $wpdb->prefix . 'mjschool_subject';
	$table_name_mark       = $wpdb->prefix . 'mjschool_marks';
	$table_name_users      = $wpdb->prefix . 'users';
	$table_teacher_subject = $wpdb->prefix . 'mjschool_teacher_subject';
	$teachers              = get_users( array( 'role' => 'teacher' ) );
	$report_3              = array();
	$obj_subject           = new Mjschool_Subject();
	if ( ! empty( $teachers ) ) {
		foreach ( $teachers as $teacher ) {
			$report_3[ $teacher->ID ] = $obj_subject->mjschool_get_subject_id_by_teacher( $teacher->ID );
		}
	}
	if ( ! empty( $report_3 ) ) {
		foreach ( $report_3 as $teacher_id => $subject ) {
			if ( ! empty( $subject ) ) {
				$sub_str = implode( ',', $subject );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
				$count      = $wpdb->get_results( "SELECT COUNT(*) as count FROM {$table_name_mark} WHERE marks < 40 AND subject_id in ({$sub_str}) GROUP by subject_id", ARRAY_A );
				$total_fail = array_sum( mjschool_array_column( $count, 'count' ) );
			} else {
				$total_fail = 0;
			}
			$teacher_name  = mjschool_get_display_name( $teacher_id );
			$chart_array[] = array( $teacher_name, $total_fail );
		}
	}
	$options = array(
		'title'          => esc_attr__( 'Teacher Perfomance Report', 'mjschool' ),
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
			'title'          => esc_attr__( 'Teacher Name', 'mjschool' ),
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
			'title'          => esc_attr__( 'No. of Student', 'mjschool' ),
			'minValue'       => 0,
			'maxValue'       => 5,
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
		'colors'         => array( '#5840bb' ),
	);
}

$GoogleCharts = new GoogleCharts();
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data"></div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-page-inner"><!-- Panel Inner. --->
	<div class=" mjschool-transport-list mjschool-main-list-margin-5px">
		<div class="mjschool-panel-white"> <!-- Panel White. --->
			<div class="mjschool-panel-body"> <!-- Panel body. --->
				<!--  Student Information Report - start.-->
				<?php
				if ( $active_tab === 'student_information_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'student_report';
					?>
					<div class="clearfix"> </div>
					<!-- Tabbing start.  -->
					<?php $nonce = wp_create_nonce( 'mjschool_student_infomation_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'student_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=student_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'class_section_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=class_section_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'class_section_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Class & Section Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'guardian_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=guardian_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'guardian_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Guardian Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'admission_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=admission_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'admission_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Admission Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'sibling_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=sibling_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'sibling_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Sibling Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'student_failed' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=student_failed&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_failed' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Failed', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'teacher_performance_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=student_information_report&tab1=teacher_performance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_performance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher Performance', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( $active_tab === 'student_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/student-report.php';
						}
						if ( $active_tab === 'class_section_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/class-section-report.php';
						}
						if ( $active_tab === 'guardian_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/guardian-report.php';
						}
						if ( $active_tab === 'admission_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/admission-report.php';
						}
						if ( $active_tab === 'sibling_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/sibling-report.php';
						}
						if ( $active_tab === 'student_failed' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/student-failed-report.php';
						}
						if ( $active_tab === 'teacher_performance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-performance-report.php';
						}
						?>
					</div>
					<?php
				}
				// --- Student Information Report - End. --//
				// --- Attendance Report - start.----//
				if ( $active_tab === 'attendance_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'student_attendance_report';
					?>
					<!-- Tabbing start. -->
					<?php $nonce = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'student_attendance_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=attendance_report&tab1=student_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Attendance Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'teacher_attendance_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=attendance_report&tab1=teacher_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher Attendance Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( $active_tab === 'student_attendance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/student-attendance-report.php';
						}
						if ( $active_tab === 'teacher_attendance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-attendance-report.php';
						}
						?>
					</div>
					<div class="clearfix"> </div>
					<?php
				}
				// --- Attendance Report - End.----//
				// --- Hostel Report - start.----//
				if ( $active_tab === 'hostel_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'student_hostel_report';
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_hostel_report_tab' ); ?>
					<!-- tabing start  -->
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'student_hostel_report' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=hostel_report&tab1=student_hostel_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_hostel_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Hostel Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( $active_tab === 'student_hostel_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/student-hostel-report.php';
						}
						?>
					</div>
					<div class="clearfix"> </div>
					<?php
				}
				// --- Hostel Report - End.----//
				// Fianance / Payment Report.
				if ( $active_tab === 'finance_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'fees_payment';
					?>
					<!-- tabing start  -->
					 <?php $nonce = wp_create_nonce( 'mjschool_finance_report_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'fees_payment' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=finance_report&tab1=fees_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'fees_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'income_payment' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=finance_report&tab1=income_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'expense_payment' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=finance_report&tab1=expense_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'expense_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Expense Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'income_expense_payment' ) { ?> active<?php } ?>">
							<a href="?page=mjschool_report&tab=finance_report&tab1=income_expense_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_expense_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income-Expense Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<!-- Tabbing end.  -->
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( $active_tab === 'fees_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/fees-payment.php';
						}
						if ( $active_tab === 'income_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/income-payment.php';
						}
						if ( $active_tab === 'expense_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/expense-payment.php';
						}
						if ( $active_tab === 'income_expense_payment' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/income-expense.php';
						}
						?>
					</div>
					<?php
				}
				?>
				<div id="chart_div" class="chart_div">
					<?php
					// Fees Payment Report.
					// Examinations Report.
					if ( $active_tab === 'examinations_report' ) {
						$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'exam_result_report';
						?>
						<!-- Tabbing start.  -->
						<?php $nonce = wp_create_nonce( 'mjschool_examination_report_tab' ); ?>
						<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
							<li class="<?php if ( $active_tab === 'exam_result_report' ) { ?> active<?php } ?>">
								<a href="?page=mjschool_report&tab=examinations_report&tab1=exam_result_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_result_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Result', 'mjschool' ); ?></a>
							</li>
						</ul>
						<!-- Tabbing end.  -->
						<div class="clearfix mjschool-panel-body">
							<?php
							if ( $active_tab === 'exam_result_report' ) {
								require_once MJSCHOOL_ADMIN_DIR . '/report/exam-result-report.php';
							}
							?>
						</div>
						<?php
					}
					?>
					<div id="chart_div" class="chart_div">
						<?php
						// Library_report Report.
						if ( $active_tab === 'library_report' ) {
							$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'student_book_issue_report';
							?>
							<?php $nonce = wp_create_nonce( 'mjschool_library_report_tab' ); ?>
							<!-- Tabbing start.  -->
							<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
								<li class="<?php if ( $active_tab === 'student_book_issue_report' ) { ?> active<?php } ?>">
									<a href="?page=mjschool_report&tab=library_report&tab1=student_book_issue_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_book_issue_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'About Issue Book', 'mjschool' ); ?></a>
								</li>
							</ul>
							<div class="clearfix mjschool-panel-body">
								<?php
								if ( $active_tab === 'student_book_issue_report' ) {
									require_once MJSCHOOL_ADMIN_DIR . '/report/student-book-issue-report.php';
								}
								?>
							</div>
							<?php
						}
						if ( $active_tab === 'audit_log_report' ) {
							?>
							<div class="clearfix mjschool-panel-body">
								<?php
								require_once MJSCHOOL_ADMIN_DIR . '/report/audit-log.php';
								?>
							</div>
							<?php
						}
						if ( $active_tab === 'migration_report' ) {
							?>
							<div class="clearfix mjschool-panel-body">
								<?php
								require_once MJSCHOOL_ADMIN_DIR . '/report/migration-log.php';
								?>
							</div>
							<?php
						}
						if ( $active_tab === 'user_log_report' ) {
							?>
							<div class="clearfix mjschool-panel-body">
								<?php
								require_once MJSCHOOL_ADMIN_DIR . '/report/user-log.php';
								?>
							</div>
							<?php
						}
						?>
					</div><!-- Panel body. --->
				</div><!-- Panel white. --->
			</div>
		</div><!-- Panel Inner. --->
	</div>
</div>