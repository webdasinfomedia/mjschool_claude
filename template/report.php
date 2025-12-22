<?php
/**
 * Report Generation and Display File.
 *
 * This file handles the front-end display and backend logic for various reports,
 * including exam failed reports, student attendance, and fee payment reports. It
 * features JavaScript for form validation, date range selection using datepickers,
 * and initializing DataTables for report listing. It also contains logic for
 * querying and preparing data for visual charts.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<script type="text/javascript">
	(function(jQuery){
		"use strict";
		jQuery(function(){
			/* ---------------- Validation. ---------------- */
			jQuery( '#failed_report, #student_attendance, #fee_payment_report' ).validationEngine({
				promptPosition : "bottomLeft",
				maxErrorsPerField: 1
			});
			/* ---------------- Datepickers. ---------------- */
			function initDatepicker(selector, extraOptions) {
				jQuery(selector).datepicker(jQuery.extend({
					dateFormat : "<?php echo esc_js( get_option( 'mjschool_datepicker_format' ) ); ?>",
					changeYear : true,
					changeMonth: true,
					maxDate    : 0
				}, extraOptions || {} ) );
			}
			initDatepicker( '#sdate', {
				onSelect: function(selected) {
					var dt = new Date(selected);
					jQuery( "#edate").datepicker( "option", "minDate", dt);
				},
				beforeShow: function(textbox, instance) {
					instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
				}
			});
			initDatepicker( '#edate', {
				onSelect: function(selected) {
					var dt = new Date(selected);
					jQuery( "#sdate").datepicker( "option", "maxDate", dt);
				},
				beforeShow: function(textbox, instance) {
					instance.dpDiv.css({ marginTop: (-textbox.offsetHeight) + 'px' });
				}
			});
			initDatepicker( '.sdate' );
			initDatepicker( '.edate' );
			/* ---------------- DataTables. ---------------- */
			function initDataTable(selector, orderCol, aoCols) {
				jQuery(selector).DataTable({
					order: [[ orderCol, "Desc" ]],
					dom: 'lifrtp',
					aoColumns: aoCols,
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				});
			}
			initDataTable( '#teacher_attendance_list_report', 2, [
				{ bSortable: false }, { bSortable: true }, { bSortable: true },
				{ bSortable: true },  { bSortable: true }, { bSortable: true },
				{ bSortable: true }
			]);
			initDataTable( '#tble_audit_log', 2, [
				{ bSortable: true }, { bSortable: true },
				{ bSortable: true }, { bSortable: true },
				{ bSortable: true }
			]);
			initDataTable( '#tble_login_log1', 2, [
				{ bSortable: true }, { bSortable: true },
				{ bSortable: true }, { bSortable: true },
				{ bSortable: true }
			]);
			initDataTable( '#table_income_expense', 2, [
				{ bSortable: false }, { bSortable: true },
				{ bSortable: true },  { bSortable: true }
			]);
			initDataTable( '#attendance_list_report', 2, [
				{ bSortable: false }, { bSortable: true }, { bSortable: true },
				{ bSortable: true },  { bSortable: true }, { bSortable: true },
				{ bSortable: true },  { bSortable: true }, { bSortable: true }
			]);
			// Add placeholder to search box.
			jQuery('.dataTables_filter input')
				.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
				.attr("id", "datatable_search")
				.attr("name", "datatable_search");
		});
	})(jQuery);
</script>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
$active_tab = isset( $_GET['tab'] ) 
    ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) 
    : 'report1';
$obj_marks  = new Mjschool_Marks_Manage();
if ( $active_tab === 'report1' ) {
	$chart_array   = array();
	$chart_array[] = array( esc_attr__( 'Class', 'mjschool' ), esc_attr__( 'No. of Student Fail', 'mjschool' ) );
	if ( isset( $_REQUEST['report_1'] ) ) {
		global $wpdb;
		$table_marks = $wpdb->prefix . 'mjschool_marks';
		$table_users = $wpdb->prefix . 'users';
		$exam_id  = isset( $_REQUEST['exam_id'] )
            ? intval( wp_unslash( $_REQUEST['exam_id'] ) )
            : 0;
		$class_id = isset( $_REQUEST['class_id'] )
            ? intval( wp_unslash( $_REQUEST['class_id'] ) )
            : 0;
		if ( isset( $_REQUEST['class_section'] ) && $_REQUEST['class_section'] != '' ) {
			$section_id = isset( $_REQUEST['class_section'] )
            ? intval( wp_unslash( $_REQUEST['class_section'] ) )
            : 0;
			$query      = "SELECT *, COUNT(student_id) AS count FROM $table_marks AS m JOIN $table_users AS u ON m.student_id = u.id WHERE m.marks < 40 AND m.exam_id = %d AND m.Class_id = %d AND m.section_id = %d GROUP BY subject_id";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$report_1 = $wpdb->get_results( $wpdb->prepare( $query, $exam_id, $class_id, $section_id ) );
		} else {
			$query = "SELECT *, COUNT(student_id) AS count FROM $table_marks AS m JOIN $table_users AS u ON m.student_id = u.id WHERE m.marks < 40 AND m.exam_id = %d AND m.Class_id = %d GROUP BY subject_id";
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$report_1 = $wpdb->get_results( $wpdb->prepare( $query, $exam_id, $class_id ) );
		}
		if ( ! empty( $report_1 ) ) {
			foreach ( $report_1 as $result ) {
				$subject       = mjschool_get_single_subject_name( $result->subject_id );
				$chart_array[] = array( "$subject", (int) $result->count );
			}
		}
		$options = array(
			'title'          => esc_attr__( 'Exam Failed Report', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#222',
				'fontSize' => 14,
				'bold'     => true,
				'italic'   => false,
				'fontName' => 'Poppins',
			),
			'legend'         => array(
				'position'  => 'right',
				'textStyle' => array(
					'color'    => '#222',
					'fontSize' => 14,
					'bold'     => true,
					'italic'   => false,
					'fontName' => 'Poppins',
				),
			),
			'hAxis'          => array(
				'title'          => esc_attr__( 'Subject', 'mjschool' ),
				'titleTextStyle' => array(
					'color'    => '#222',
					'fontSize' => 14,
					'bold'     => true,
					'italic'   => false,
					'fontName' => 'Poppins',
				),
				'textStyle'      => array(
					'color'    => '#222',
					'fontSize' => 10,
				),
				'maxAlternation' => 2,
			),
			'vAxis'          => array(
				'title'          => esc_attr__( 'No. of Students', 'mjschool' ),
				'minValue'       => 0,
				'maxValue'       => 5,
				'format'         => '#',
				'titleTextStyle' => array(
					'color'    => '#222',
					'fontSize' => 14,
					'bold'     => true,
					'italic'   => false,
					'fontName' => 'Poppins',
				),
				'textStyle'      => array(
					'color'    => '#222',
					'fontSize' => 12,
				),
			),
			'colors'         => array( '#22BAA0' ),
		);
	}
}
if ( $active_tab === 'report2' ) {
	$chart_array[] = array( esc_attr__( 'Class', 'mjschool' ), esc_attr__( 'Present', 'mjschool' ), esc_attr__( 'Absent', 'mjschool' ) );
	global $wpdb;
	$table_attendance = $wpdb->prefix . 'mjschool_attendence';
	$table_class      = $wpdb->prefix . 'mjschool_class';
	if ( isset( $_POST['report_2'] ) ) {
		$sdate = sanitize_text_field(wp_unslash($_POST['sdate']));
		$edate = sanitize_text_field(wp_unslash($_POST['edate']));
	} else {
		$sdate = date( 'Y-m-d', strtotime( 'first day of this month' ) );
		$edate = date( 'Y-m-d', strtotime( 'last day of this month' ) );
	}
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
			'title'          => esc_attr__( 'No. of Students', 'mjschool' ),
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
if ( $active_tab === 'report3' ) {
	$chart_array[] = array( esc_attr__( 'Teacher', 'mjschool' ), esc_attr__( 'Fail', 'mjschool' ) );
	global $wpdb;
	$table_subject         = $wpdb->prefix . 'subject';
	$table_name_mark       = $wpdb->prefix . 'marks';
	$table_name_users      = $wpdb->prefix . 'users';
	$table_teacher_subject = $wpdb->prefix . 'teacher_subject';
	$own_data              = $user_access['own_data'];
	if ( $own_data === '1' ) {
		$teachers[] = get_userdata( get_current_user_id() );
	} else {
		$teachers = get_users( array( 'role' => 'teacher' ) );
	}
	$obj_subject = new Mjschool_Subject_Manage();
	$report_3 = array();
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
			'color'    => '#222',
			'fontSize' => 14,
			'bold'     => true,
			'italic'   => false,
			'fontName' => 'Poppins',
		),
		'legend'         => array(
			'position'  => 'right',
			'textStyle' => array(
				'color'    => '#222',
				'fontSize' => 14,
				'bold'     => true,
				'italic'   => false,
				'fontName' => 'Poppins',
			),
		),
		'hAxis'          => array(
			'title'          => esc_attr__( 'Teacher Name', 'mjschool' ),
			'titleTextStyle' => array(
				'color'    => '#222',
				'fontSize' => 14,
				'bold'     => true,
				'italic'   => false,
				'fontName' => 'Poppins',
			),
			'textStyle'      => array(
				'color'    => '#222',
				'fontSize' => 10,
			),
			'maxAlternation' => 2,
		),
		'vAxis'          => array(
			'title'          => esc_attr__( 'No. of Students', 'mjschool' ),
			'minValue'       => 0,
			'maxValue'       => 5,
			'format'         => '#',
			'titleTextStyle' => array(
				'color'    => '#222',
				'fontSize' => 14,
				'bold'     => true,
				'italic'   => false,
				'fontName' => 'Poppins',
			),
			'textStyle'      => array(
				'color'    => '#222',
				'fontSize' => 12,
			),
		),
		'colors'         => array( '#22BAA0' ),
	);
}

$GoogleCharts = new GoogleCharts();
?>
<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data">
			</div>
		</div>
	</div> 
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-panel-white"><!----------- Panel white. ------------->
	<div class="mjschool-panel-body mjschool-frontend-list-margin-30px-res"> <!----------- Panel body. ------------->
		<!-- Tabbing start.  -->
		<!-- Tabbing End. -->
		<?php
		if ( $active_tab === 'student_information_report' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'student_report';
			?>
			<div class="clearfix"> </div>
			<!-- Tabbing start. -->
			 <?php $nonce = wp_create_nonce( 'mjschool_student_infomation_tab' ); ?>
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
				<li class="<?php if ( $active_tab === 'student_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=student_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'class_section_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=class_section_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'class_section_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Class & Section Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'guardian_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=guardian_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'guardian_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Guardian Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'admission_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=admission_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'admission_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Admission Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'sibling_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=sibling_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'sibling_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Sibling Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'student_failed' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=student_failed&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_failed' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Failed', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'teacher_performance_report' ) { ?> active<?php } ?>">
					<a href="?dashboard=mjschool_user&page=report&tab=student_information_report&tab1=teacher_performance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_performance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher Performance', 'mjschool' ); ?></a> 
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
		// --- Attendance Report - start.----//
		if ( $active_tab === 'attendance_report' ) {
			if ( $school_obj->role === 'teacher' || $school_obj->role === 'supportstaff' ) {
				$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'monthly_attendance_report';
				?>
				<!-- Tabbing start. -->
				<?php $nonce = wp_create_nonce( 'mjschool_attendance_report_tab' ); ?>
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab === 'monthly_attendance_report' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=monthly_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'monthly_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Monthly Attendance Report', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab === 'daily_attendance_report' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=daily_attendance_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'daily_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Daily Attendance Report', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab === 'attendance_report_datatable' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=attendance_report_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'attendance_report_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report In Datatable', 'mjschool' ); ?></a> 
					</li> 
					<li class="<?php if ( $active_tab === 'attendance_report_graph' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=attendance_report_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'attendance_report_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report In Graph', 'mjschool' ); ?></a> 
					</li>
					<?php
					if ( $school_obj->role === 'supportstaff' ) {
						?>
						<li class="<?php if ( $active_tab === 'teacher_attendance_report_datatable' ) { ?> active<?php } ?>">
							<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=teacher_attendance_report_datatable&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_attendance_report_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher Attendance Report In Datatable', 'mjschool' ); ?></a> 
						</li> 
						<li class="<?php if ( $active_tab === 'teacher_attendance_report_graph' ) { ?> active<?php } ?>">			
							<a href="?dashboard=mjschool_user&page=report&tab=attendance_report&tab1=teacher_attendance_report_graph&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_attendance_report_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Teacher Attendance Report In Graph', 'mjschool' ); ?></a> 
						</li>
					<?php } ?>
				</ul>
				<div class="clearfix mjschool-panel-body">
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
					if ( $school_obj->role === 'supportstaff' ) {
						if ( $active_tab === 'teacher_attendance_report_datatable' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-attendance-report-datatable.php';
						}
						if ( $active_tab === 'teacher_attendance_report_graph' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/report/teacher-attendance-report-graph.php';
						}
					}
					?>
				</div>
				<div class="clearfix"> </div>
				<?php
			}
		}
		// --- Attendance Report - End.----//
		// --- Attendance Report - start.----//
		if ( $active_tab === 'hostel_report' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'student_hostel_report';
			?>
			<?php $nonce = wp_create_nonce( 'mjschool_hostel_report_tab' ); ?>
			<!-- Tabbing start.  -->
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
				<li class="<?php if ( $active_tab === 'student_hostel_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=hostel_report&tab1=student_hostel_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_hostel_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student Hostel Report', 'mjschool' ); ?></a> 
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
		// --- Attendance Report - End.----//
		// Fianance / Payment Report.
		if ( $active_tab === 'finance_report' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'fees_payment';
			?>
			<!-- Tabbing start.  -->
			<?php $nonce = wp_create_nonce( 'mjschool_finance_report_tab' ); ?>
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
				<li class="<?php if ( $active_tab === 'fees_payment' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=finance_report&tab1=fees_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'fees_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'income_payment' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=finance_report&tab1=income_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'expense_payment' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=finance_report&tab1=expense_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'expense_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Expense Report', 'mjschool' ); ?></a> 
				</li>
				<li class="<?php if ( $active_tab === 'income_expense_payment' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab=finance_report&tab1=income_expense_payment&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'income_expense_payment' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income-Expense Report', 'mjschool' ); ?></a> 
				</li>
			</ul>	  
			<!-- Tabbing end.  -->
			<div class="clearfix mjschool-panel-body">
				<?php
				if ( $active_tab === 'fees_payment' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/report/fees-payment.php';
				}
				if ( $active_tab === 'income_payment' ) {
					?>
					<script type="text/javascript">
						(function(jQuery){
							"use strict";
							jQuery(document).ready(function(){
								jQuery( '#student_income_payment' ).validationEngine({
									promptPosition: "bottomLeft",
									maxErrorsPerField: 1
								});
							});
						})(jQuery);
					</script>
					<?php
					require_once MJSCHOOL_ADMIN_DIR . '/report/income-payment.php';
				}
				if ( $active_tab === 'expense_payment' ) {
					?>
					<script type="text/javascript">
						(function(jQuery){
							"use strict";
							jQuery(document).ready(function(){
								jQuery( '#student_expence_payment' ).validationEngine({
									promptPosition: "bottomLeft",
									maxErrorsPerField: 1
								});
							});
						})(jQuery);
					</script>
					<?php
					require_once MJSCHOOL_ADMIN_DIR . '/report/expense-payment.php';
				}
				if ( $active_tab === 'income_expense_payment' ) {
					?>
					<script type="text/javascript">
						(function(jQuery){
							"use strict";
							jQuery(document).ready(function(){
								jQuery( '#student_income_expence_payment' ).validationEngine({
									promptPosition: "bottomLeft",
									maxErrorsPerField: 1
								});
							});
						})(jQuery);
					</script>
					<?php
					require_once MJSCHOOL_ADMIN_DIR . '/report/income-expense.php';
				}
				?>
			</div>
			<div id="chart_div" class="chart_div">
			<?php
		}
		// Fees Payment Report.
		if ( $active_tab === 'examinations_report' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'exam_result_report';
			?>
			<!-- Tabbing start. -->
			<?php $nonce = wp_create_nonce( 'mjschool_examination_report_tab' ); ?> 
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
				<li class="<?php if ( $active_tab === 'exam_result_report' ) { ?> active<?php } ?>">
					<a href="?dashboard=mjschool_user&page=report&tab=examinations_report&tab1=exam_result_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_result_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Result', 'mjschool' ); ?></a> 
				</li> 
			</ul>
			<!-- Tabbing end.  --> 
			<div class="clearfix mjschool-panel-body mt-5">
				<?php
				if ( $active_tab === 'exam_result_report' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/report/exam-result-report.php';
				}
				?>
			</div>
			<div id="chart_div" class="chart_div"></div>
			<?php
		}
		if ( $active_tab === 'audit_log_report' ) {
			?>
			<div class="clearfix mjschool-panel-body mt-5">
				<?php require_once MJSCHOOL_ADMIN_DIR . '/report/audit-log.php'; ?>
			</div>
			<?php
		}
		if ( $active_tab === 'migration_report' ) {
			?>
			<div class="clearfix mjschool-panel-body mt-5">
				<?php require_once MJSCHOOL_ADMIN_DIR . '/report/migration-log.php'; ?>
			</div>
			<?php
		}
		if ( $active_tab === 'user_log_report' ) {
			?>
			<div class="clearfix mjschool-panel-body mt-5">
				<?php require_once MJSCHOOL_ADMIN_DIR . '/report/user-log.php'; ?>
			</div>
			<?php
		}
		if ( $active_tab === 'report1' ) { 
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'student_book_issue_report';
			?>
			<?php $nonce = wp_create_nonce( 'mjschool_library_report_tab' ); ?>
			<!-- Tabbing start.  -->
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-margin-left-1per" role="tablist">
				<li class="<?php if ( $active_tab === 'student_book_issue_report' ) { ?> active<?php } ?>">			
					<a href="?dashboard=mjschool_user&page=report&tab1=student_book_issue_report&_wpnonce=<?php echo esc_attr( $nonce ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_book_issue_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'About Issue Book', 'mjschool' ); ?></a> 
				</li>
			</ul>
			<!-- Panel body div. -->
			<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
				<form method="post" id="failed_report">  
					<!-- Panel body div. -->
					<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
						<form method="post" id="failed_report">  
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-6 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<?php
										$class_id = '';
										if ( isset( $_REQUEST['class_id'] ) ) {
											$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
										}

										?>
										<select name="class_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] class_id_exam">
											<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option  value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
												<?php
											}
											?>
										</select>           
									</div>
									<div class="col-md-6 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-date-type"><?php esc_html_e( 'Date Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>			
										<?php $date_type = isset( $_POST['date_type'] ) ? sanitize_text_field(wp_unslash($_POST['date_type'])) : ''; ?>			
										<select id="mjschool-date-type" class="mjschool-line-height-30px form-control date_type validate[required]" name="date_type" autocomplete="off">
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
											<script type="text/javascript">
												(function(jQuery){
													"use strict";
													jQuery(document).ready(function() {
														var dateFormat = "<?php echo esc_js(get_option( 'mjschool_datepicker_format' ) ); ?>";
														jQuery( "#report_sdate").datepicker({
															dateFormat: dateFormat,
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
															dateFormat: dateFormat,
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
									<input type="submit" name="library_report" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
								</div>
							</div>
							</div>	
						</form>
					</div>
					<!-- Panel body div. -->	
				</form>
			</div>
			<!-- Panel body div. -->
			<?php
			$class_id      = '';
			$class_section = '';
			$date_type     = '';
			if ( isset( $_REQUEST['library_report'] ) ) {
				$date_type = sanitize_text_field(wp_unslash($_POST['date_type']));
				$class_id  = sanitize_text_field(wp_unslash($_POST['class_id']));
				if ( $date_type === 'period' ) {
					$start_date = isset( $_REQUEST['start_date'] )
						? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) )
						: '';

					$end_date = isset( $_REQUEST['end_date'] )
						? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) )
						: '';
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
				(function(jQuery){
					"use strict";
					jQuery(function(){
						const tableBookIssue = jQuery( '#mjschool-book-issue-list-report' ).DataTable({
							order: [[2, "desc"]],
							dom: 'lifrtp',
							buttons: [
								{
									extend: 'csv',
									text: '<?php echo esc_html__( 'CSV', 'mjschool' ); ?>',
									title: '<?php echo esc_html__( 'Book Issue Report', 'mjschool' ); ?>',
								},
								{
									extend: 'print',
									text: '<?php echo esc_html__( 'Print', 'mjschool' ); ?>',
									title: '<?php echo esc_html__( 'Book Issue Report', 'mjschool' ); ?>',
								}
							],
							aoColumns: [
								{ bSortable: true },
								{ bSortable: true },
								{ bSortable: true },
								{ bSortable: true },
								{ bSortable: true },
								{ bSortable: true },
								{ bSortable: true }
							],
							language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
						});
						jQuery('.dataTables_filter input')
							.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
							.attr("id", "datatable_search")
							.attr("name", "datatable_search");
						jQuery( '.btn-place' ).html(tableBookIssue.buttons().container( ) );
					});
				})(jQuery);
			</script>
			<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
				<?php
				if ( ! empty( $book_issue_data ) ) {
					$mjschool_obj_lib = new Mjschool_Library();
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
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $book_issue_data as $retrieved_data ) {
										?>
										<tr>
											<td><?php echo esc_html( $mjschool_obj_lib->mjschool_get_book_name( $retrieved_data->book_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( $mjschool_obj_lib->mjschool_get_book_number( $retrieved_data->book_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Number', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( $mjschool_obj_lib->mjschool_get_ISBN( $retrieved_data->book_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'ISBN', 'mjschool' ); ?>"></i></td>
											<td>
												<?php echo esc_html( mjschool_get_display_name( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												$admission_no = get_user_meta( $retrieved_data->student_id, 'admission_no', true );
												if ( ! empty( $admission_no ) ) {
													echo esc_html( get_user_meta( $retrieved_data->student_id, 'admission_no', true ) );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission No', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Return Date', 'mjschool' ); ?>"></i>
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
						
						<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
						
					</div>	
					<?php
				}
				?>
			</div>
			<?php
		}
		if ( $active_tab === 'report2' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'report2_graph';
			?>
			<div class="mjschool-panel-body"><!-------------- Panel body. ------------------>
				<!--------------- INCOME TABING. --------------->
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab_1 === 'report2_graph' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=report2&tab1=report2_graph" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1  ) === 'report2_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report Graph', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab_1 === 'report2_attendance_report' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=report2&tab1=report2_attendance_report" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1  ) === 'report2_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Attendance Report', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab_1 === 'report2_daily_attendance_report' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=report2&tab1=report2_daily_attendance_report" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1  ) === 'report2_daily_attendance_report' ? 'active' : ''; ?>"> <?php esc_html_e( 'Daily Attendance Report', 'mjschool' ); ?></a> 
					</li>
				</ul><!--------------- INCOME TABING. --------------->
			</div><!-------------- Panel body. ------------------>
			<?php
			// Satrt Income Datatbale Report Tab. //
			if ( $active_tab_1 === 'report2_graph' ) {
				?>
				<div class="clearfix"> </div>
				<div class="mjschool-panel-body mjschool-margin-top-10px" id="attendance_report"><!----------- Panel body. --------------->
					<form method="post">
						<div class="form-body mjschool-user-form">
							<div class="row">
								<div class="col-md-5">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text"  id="sdate" class="form-control" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d', strtotime( 'first day of this month' ) ) ); } ?>" readonly>
											<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-5">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text"  id="edate" class="form-control" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
											<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-2">
									<input type="submit" name="report_2" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
								</div>	
							</div>
						</div>	
					</form>
				</div><!----------- Panel body. --------------->
				<div class="clearfix"> </div>
				<div class="clearfix"> </div>
				<?php
				if ( ! empty( $report_2 ) ) {
					$chart = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
				} else {
					?>
					<div class="mjschool-calendar-event-new"> 
						
						<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
						
					</div>		
					<?php
				}
				?>
				<div id="chart_div" class="w-100 h-500-px"></div>
				<!-- Javascript. -->
				<script type="text/javascript">
					"use strict";
					<?php echo wp_kses_post( $chart ); ?>
				</script>
				<?php
			}
			if ( $active_tab_1 === 'report2_attendance_report' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-15px-res">
					<div class="mjschool-panel-body clearfix">
						<form method="post" id="student_attendance">  
							<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-3 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>			
										<select name="class_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
											<?php
											$class_id = '';
											if ( isset( $_REQUEST['class_id'] ) ) {
												$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
											}
											?>
											<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												?>
												<option  value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?> ><?php echo esc_html( $classdata['class_name'] ); ?></option>
												<?php
											}
											?>
										</select>   		
									</div>
									<div class="col-md-3 mb-3 input">
										<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></label>			
										<?php
										$class_section = '';
										if ( isset( $_REQUEST['class_section'] ) ) {
											$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
										}
										?>
										<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
											<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
											<?php
											if ( isset( $_REQUEST['class_section'] ) ) {
												$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
												foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
													?>
													<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
													<?php
												}
											}
											?>
										</select>
									</div>
									<div class="col-md-2 mb-2 input">
										<label class="ml-1 mjschool-custom-top-label top" for="mjschool-year"><?php esc_html_e( 'Year', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select id="mjschool-year" name="year" class="mjschool-line-height-30px form-control validate[required]">
											<option ><?php esc_html_e( 'Selecte year', 'mjschool' ); ?></option>
											<?php
											$current_year = date( 'Y' );
											$min_year     = $current_year - 10;
											for ( $i = $min_year; $i <= $current_year; $i++ ) {
												$year_array[ $i ] = $i;
												$selected         = ( $current_year === $i ? ' selected' : '' );
												echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>' . "\n";
											}
											?>
										</select>       
									</div>
									<div class="col-md-2 mb-2 input">
										<label class="ml-1 mjschool-custom-top-label top" for="month"><?php esc_html_e( 'Months', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
										<select id="month" name="month" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
											<option ><?php esc_html_e( 'Selecte Month', 'mjschool' ); ?></option>
											<?php
											$selected_month = date( 'm' ); // Current month.
											for ( $i_month = 1; $i_month <= 12; $i_month++ ) {
												$selected = ( $selected_month === $i_month ? ' selected' : '' );
												echo '<option value="' . esc_attr( $i_month ) . '"' . esc_attr( $selected ) . '>' . esc_html( date( 'F', mktime( 0, 0, 0, $i_month ) ) ) . '</option>' . "\n";
											}
											?>
										</select>       
									</div>
									<div class="col-md-2 mb-2">
										<input type="submit" name="view_attendance" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
									</div>
								</div>
							</div>
						</form> 
					</div>	
					<?php
					if ( isset( $_REQUEST['view_attendance'] ) ) {
						$class_id      = sanitize_text_field(wp_unslash($_POST['class_id']));
						$class_section = sanitize_text_field(wp_unslash($_POST['class_section']));
						$year          = sanitize_text_field(wp_unslash($_POST['year']));
						$month         = sanitize_text_field(wp_unslash($_POST['month']));
						// Fetch day and date by year,Month.
						$list  = array();
						$month = $month;
						$year  = $year;
						if ( $month === '2' ) {
							$max_d = '28';
						} elseif ( $month === '4' || $month === '6' || $month === '9' || $month === '11' ) {
							$max_d = '30';
						} else {
							$max_d = '31';
						}
						for ( $d = 1; $d <= $max_d; $d++ ) {
							$time = mktime( 12, 0, 0, $month, $d, $year );
							if ( date( 'm', $time ) === $month ) {
								$date_list[] = date( 'Y-m-d', $time );
							}
							$day_date[]       = date( 'd D', $time );
							$month_first_date = min( $date_list );
							$month_last_date  = max( $date_list );
						}
						
						if ( $class_section === "")
						{
							$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id,'role'=>'student' ) );
							sort($student);
						}
						else
						{ 
							$student = 	get_users(array( 'meta_key' => 'class_section', 'meta_value' =>$class_section,'meta_query'=> array(array( 'key' => 'class_name','value' => $class_id ) ),'role'=>'student' ) );
							sort($student);
						} 
						?>
						<script type="text/javascript">
							(function(jQuery){
								"use strict";
								jQuery(function(){
									// DataTable initialization.
									const tableClassAttendance = jQuery( '#mjschool-class-attendance-list-report' ).DataTable({
										initComplete: function(){
											jQuery( ".mjschool-print-button").addClass( "mt-minus-5"); // Use CSS class instead.
										},
										order: [[2, "desc"]],
										dom: 'lifrtp',
										aoColumns: [
											{ bSortable: true },
											{ bSortable: false },
											{ bSortable: false },
											{ bSortable: false },
											{ bSortable: false },
											<?php foreach ( $day_date as $data ) : ?>
												{ bSortable: false },
											<?php endforeach; ?>
											{ bSortable: false }
										],
										language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
									});
									// Add placeholder to search box.
									jQuery('.dataTables_filter input')
										.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
										.attr("id", "datatable_search")
										.attr("name", "datatable_search");
								});
							})(jQuery);
						</script>
						<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
							<div class="row">
								<div class="col-sm-12 col-md-4 col-lg-4 col-xs-12">
									<h4 class="mjschool-report-header"><?php esc_html_e( 'Student Attendance Report', 'mjschool' ); ?></h4>
								</div>
								<div class="col-sm-12 col-md-8 col-lg-8 col-xs-12">
									<div class="mjschool-card-head">
										<ul class="mjschool-att-repot-list mjschool-right mjschool-att-status-color">
											<!--Set attendance-status header Start. -->
											<li> <?php esc_html_e( 'Present', 'mjschool' ); ?>: <span ><?php esc_html_e( 'P', 'mjschool' ); ?></span></li>
											<li> <?php esc_html_e( 'Late', 'mjschool' ); ?>: <span ><?php esc_html_e( 'L', 'mjschool' ); ?></span></li>
											<li> <?php esc_html_e( 'Absent', 'mjschool' ); ?>: <span ><?php esc_html_e( 'A', 'mjschool' ); ?></span></li>
											<li> <?php esc_html_e( 'Holiday', 'mjschool' ); ?>: <span ><?php esc_html_e( 'H', 'mjschool' ); ?></span></li>
											<li> <?php esc_html_e( 'Half Day', 'mjschool' ); ?>: <span ><?php esc_html_e( 'F', 'mjschool' ); ?></span></li>
										</ul>
									</div>   
								</div>
							</div>
							<div id="mjschool-overflow" class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="mjschool-class-attendance-list-report" class="display mjschool-class-att-repost-tbl" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Student', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'P', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'L', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'A', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'F', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'H', 'mjschool' ); ?></th>
												<?php
												foreach ( $day_date as $data ) {
													?>
													<th class="<?php echo esc_attr( $data ); ?>"><?php echo esc_html( $data ); ?></th>
													<?php
												}
												?>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ( $student as $mjschool_user ) {
												?>
												<tr>
													<td>
														<?php echo esc_html( mjschool_get_display_name( $mjschool_user->ID ) ); ?> 
													</td>
													<td>
														<?php
														$Present       = 'Present';
														$total_present = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Present );
														echo esc_html( count( $total_present ) );
														?>
													</td>
													<td>
														<?php
														$Late       = 'Late';
														$total_late = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Late );
														echo count( $total_late );
														?>
													</td>
													<td>
														<?php
														$absent       = 'Absent';
														$total_absent = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $absent );
														echo count( $total_absent );
														?>
													</td>
													<td>
														<?php
														$Half_Day       = 'Half Day';
														$total_Half_day = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Half_Day );
														echo count( $total_Half_day );
														?>
													</td>
													<td>
														<?php
														$total_Holiday_day = mjschool_get_all_holiday_by_month_year( $month, $year );
														echo count( $total_Holiday_day );
														?>
													</td>
													<?php
													foreach ( $date_list as $date ) {
														?>
														<td class="mjschool-att-status-color">
															<?php
															echo esc_html( mjschool_attendance_report_all_staus_value( $date, $class_id, $mjschool_user->ID ) )
															?>
														</td>
														<?php
													}
													?>
												</tr>
												<?php
											}
											?>
										</tbody>        
									</table>
								</form>
							</div>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			if ( $active_tab_1 === 'report2_daily_attendance_report' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-15px-res">
					<div class="mjschool-panel-body clearfix">
						<form method="post">  
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-8">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input type="text"  id="sdate" class="form-control" name="date" value="<?php if ( isset( $_REQUEST['date'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['date'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
												<label for="userinput1"><?php esc_html_e( 'Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-4">
										<input type="submit" name="daily_attendance" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
									</div>
								</div>
							</div>
						</form>
					</div>	
					<?php
					if ( isset( $_REQUEST['daily_attendance'] ) ) {
						$daily_date = sanitize_text_field(wp_unslash($_POST['date']));
						?>
						<script type="text/javascript">
							(function(jQuery){
								"use strict";
								jQuery(function(){
									// DataTable initialization.
									const tableDailyAttendance = jQuery( '#mjschool-daily-attendance-list-report' ).DataTable({
										// stateSave: true,
										order: [[2, "desc"]],
										dom: 'lifrtp',
										aoColumns: [
											{ bSortable: true },
											{ bSortable: true },
											{ bSortable: true },
											{ bSortable: true },
											{ bSortable: true }
										],
										language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
									});
									// Add placeholder to search box.
									jQuery('.dataTables_filter input')
										.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
										.attr("id", "datatable_search")
										.attr("name", "datatable_search");
								});
							})(jQuery);
						</script>
						<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
							<div class="row">
								<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
									<h4 class="mjschool-report-header"><?php esc_html_e( 'Daily Attendance Report', 'mjschool' ); ?></h4>
								</div>
							</div>
							<div class="table-responsive">
								<form id="frm-daily-attendance" name="frm-daily-attendance" method="post">
									<table id="mjschool-daily-attendance-list-report" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Total Present', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Total Absent', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Present', 'mjschool' ); ?><?php esc_html_e( ' %', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Absent', 'mjschool' ); ?><?php esc_html_e( ' %', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											foreach ( mjschool_get_all_class() as $classdata ) {
												$class_id      = $classdata['class_id'];
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
												</tr>
												<?php
											}
											?>
										</tbody>
										<tbody>
											<?php
											$total_class_present = mjschool_daily_attendance_report_for_all_class_total_present( $daily_date );
											$total_class_absent  = mjschool_daily_attendance_report_for_all_class_total_absent( $daily_date );
											$total_class_pre_abs = $total_class_present + $total_class_absent;
											if ( $total_class_present === '0' && $total_class_absent === '0' ) {
												$present_class_per = 0;
												$absent_class_per  = 0;
											} else {
												$present_class_per = ( $total_class_present * 100 ) / $total_class_pre_abs;
												$absent_class_per  = ( $total_class_absent * 100 ) / $total_class_pre_abs;
											}
											?>
											<tr id="mjschool-daily-att-total">
												<td></td>
												<td ><?php echo esc_html( round( $total_class_present ) ); ?></td>
												<td ><?php echo esc_html( round( $total_class_absent ) ); ?></td>
												<td ><?php echo esc_html( round( $present_class_per ) ); ?>%</td>
												<td ><?php echo esc_html( round( $absent_class_per ) ); ?>%</td>
											</tr>
										</tbody>        
									</table>
								</form>
							</div>
						</div>
						<?php
					}
					?>
				</div>	
				<?php
			}
		}
		if ( $active_tab === 'report3' ) {
			?>
			<div class="clearfix"> </div>
			<?php
			if ( ! empty( $report_3 ) ) {
				$chart = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
			} else {
				?>
				<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
					
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/mjschool-close.png")?>"></span>
					
					</button>
					<?php echo esc_html__( 'Result Not Found', 'mjschool' ); ?>
				</div>
				<?php
			}
			?>
			<div id="chart_div" class="w-100 h-500-px"></div>
			<!-- Javascript. --> 
			<script type="text/javascript">
				"use strict";
				<?php echo wp_kses_post( $chart ); ?>
			</script>
			<?php
		}
		// Satrt Expense Report Tab. //
		if ( $active_tab === 'report7' ) {
			$active_tab = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'report7_datatable'
			?>
			<div class="mjschool-panel-body">
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab_1 === 'report7_datatable' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=report7&tab1=report7_datatable" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report7_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Expense Report Datatable', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab_1 === 'report7_graph' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=report7&tab1=report7_graph" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report7_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Expense Report Graph', 'mjschool' ); ?></a> 
					</li>
				</ul>
			</div>
			<?php
			// Satrt Expense Datatbale Report Tab. //
			if ( $active_tab_1 === 'report7_datatable' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-25px-res">
					<div class="mjschool-panel-body clearfix">
						<form method="post">  
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-5">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input type="text"  id="sdate" class="form-control" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d', strtotime( 'first day of this month' ) ) ); } ?>" readonly>
												<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-5">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input type="text"  id="edate" class="form-control" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
												<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<input type="submit" name="report_6" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
									</div>	
								</div>
							</div>	
						</form>
					</div>		
					<?php
					// -------- IF SREACH DATE. ---------//
					if ( isset( $_REQUEST['report_6'] ) ) {
						$start_date = sanitize_text_field(wp_unslash($_POST['sdate']));
						$end_date   = sanitize_text_field(wp_unslash($_POST['edate']));
					} else {
						$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
						$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
					}
					global $wpdb;
					$table_income = $wpdb->prefix . 'mjschool_income_expense';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$report_6 = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $table_income WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s", 'expense', $start_date, $end_date )
					);
					if ( ! empty( $report_6 ) ) {
						?>
						<div class="mjschool-panel-body"><!--------------- Panel body. --------------->
							<div class="table-responsive"><!--------------- Table responsive. --------------->
								<!--------------- Expense list form. --------------->
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="tblexpence" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Supplier Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Amount', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Create Date', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											if ( ! empty( $report_6 ) ) {
												$i = 0;
												foreach ( $report_6 as $result ) {
													$all_entry    = json_decode( $result->entry );
													$total_amount = 0;
													foreach ( $all_entry as $entry ) {
														$total_amount += $entry->amount;
														if ( $i === 10 ) {
															$i = 0;
														}
														if ( $i === 0 ) {
															$color_class_css = 'mjschool-class-color0';
														} elseif ( $i === 1 ) {
															$color_class_css = 'mjschool-class-color1';
														} elseif ( $i === 2 ) {
															$color_class_css = 'mjschool-class-color2';
														} elseif ( $i === 3 ) {
															$color_class_css = 'mjschool-class-color3';
														} elseif ( $i === 4 ) {
															$color_class_css = 'mjschool-class-color4';
														} elseif ( $i === 5 ) {
															$color_class_css = 'mjschool-class-color5';
														} elseif ( $i === 6 ) {
															$color_class_css = 'mjschool-class-color6';
														} elseif ( $i === 7 ) {
															$color_class_css = 'mjschool-class-color7';
														} elseif ( $i === 8 ) {
															$color_class_css = 'mjschool-class-color8';
														} elseif ( $i === 9 ) {
															$color_class_css = 'mjschool-class-color9';
														}
														?>
														<tr>
															<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
																<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																		
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png")?>" class="mjschool-massage-image mjschool-margin-top-3px">
																	
																</p>
															</td>
															<td class="patient_name"><?php echo esc_html( $result->supplier_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Supplier Name', 'mjschool' ); ?>"></i></td>
															<td class="income_amount"><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . esc_html( $total_amount ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
															<td class="status"><?php echo esc_html( mjschool_get_date_in_input_box( $result->income_create_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></td>
														</tr>
														<?php
														++$i;
													}
												}
											}
											?>
										</tbody>        
									</table>
								</form><!--------------- Expense list form. --------------->
							</div><!--------------- Table responsive. --------------->
						</div><!--------------- Panel body. --------------->
						<?php
					} else {
						$page    = 'payment';
						$payment = mjschool_get_user_role_wise_filter_access_right_array( $page );
						if ( $payment['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
								
								<a href="<?php echo esc_url(home_url().'?dashboard=mjschool_user&page=payment&tab=addexpense' );?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.','mjschool' ); ?> </label>
								</div> 
							</div>		
							<?php
						}
						else
						{
							?>
							<div class="mjschool-calendar-event-new"> 
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
								
							</div>	
							<?php
						}
					}
					?>
				</div>
				<?php
			}
			// End  Expense Datatbale Report Tab. //
			if ( $active_tab_1 === 'report7_graph' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-30px mjschool-padding-top-15px-res">
					<?php
					$month = array(
						'1'  => esc_html__( 'January', 'mjschool' ),
						'2'  => esc_html__( 'February', 'mjschool' ),
						'3'  => esc_html__( 'March', 'mjschool' ),
						'4'  => esc_html__( 'April', 'mjschool' ),
						'5'  => esc_html__( 'May', 'mjschool' ),
						'6'  => esc_html__( 'June', 'mjschool' ),
						'7'  => esc_html__( 'July', 'mjschool' ),
						'8'  => esc_html__( 'August', 'mjschool' ),
						'9'  => esc_html__( 'September', 'mjschool' ),
						'10' => esc_html__( 'Octomber', 'mjschool' ),
						'11' => esc_html__( 'November', 'mjschool' ),
						'12' => esc_html__( 'December', 'mjschool' ),
					);
					$year = isset( $_POST['year'] ) ? sanitize_text_field(wp_unslash($_POST['year'])) : date( 'Y' );
					global $wpdb;
					$table_name = $wpdb->prefix . 'mjschool_income_expense';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$report_6 = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $table_name WHERE invoice_type = %s", 'expense' )
					);
					foreach ( $report_6 as $result ) {
						$all_entry    = json_decode( $result->entry );
						$total_amount = 0;
						foreach ( $all_entry as $entry ) {
							$total_amount += $entry->amount;
							$q             = "SELECT EXTRACT(MONTH FROM income_create_date) as date, sum($total_amount) as count FROM " . $table_name . " WHERE invoice_type='expense' AND YEAR(income_create_date) =" . $year . ' group by month(income_create_date) ORDER BY income_create_date ASC';
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$result = $wpdb->get_results( $q );
						}
					}
					$sumArray = array();
					foreach ( $result as $value ) {
						if ( isset( $sumArray[ $value->date ] ) ) {
							$sumArray[ $value->date ] = $sumArray[ $value->date ] + (int) $value->count;
						} else {
							$sumArray[ $value->date ] = (int) $value->count;
						}
					}
					$chart_array   = array();
					$chart_array[] = array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Expenses', 'mjschool' ) );
					$i             = 1;
					foreach ( $sumArray as $month_value => $count ) {
						$chart_array[] = array( $month[ $month_value ], (int) $count );
					}
					$options = array(
						'title'          => esc_html__( 'Expenses Payment Report By Month', 'mjschool' ),
						'titleTextStyle' => array( 'color' => '#66707e' ),
						'legend'         => array(
							'position'  => 'right',
							'textStyle' => array( 'color' => '#66707e' ),
						),
						'hAxis'          => array(
							'title'          => esc_html__( 'Month', 'mjschool' ),
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'maxAlternation' => 2,
						),
						'vAxis'          => array(
							'title'          => esc_html__( 'Expenses Payment', 'mjschool' ),
							'minValue'       => 0,
							'maxValue'       => 6,
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
						),
						'colors'         => array( '#22BAA0' ),
					);
					
					$GoogleCharts = new GoogleCharts();
					$chart        = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
					?>
					<div id="chart_div" class="chart_div">
						<?php
						if ( empty( $result ) ) {
							?>
							<div class="clear col-md-12"><h3><?php esc_html_e( 'There is not enough data to generate report.', 'mjschool' ); ?> </h3></div>
							<?php
						}
						?>
					</div>
					<!-- Javascript. --> 
					<script type="text/javascript">
						"use strict";
						<?php
						if ( ! empty( $result ) ) {
							echo wp_kses_post( $chart );
						}
						?>
					</script>
				</div>
				<?php
			}
		}
		// End Expense Report Tab. //
		// Income Payment Report Tab. //
		if ( $active_tab === 'report6' ) {
			$active_tab_1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'report6_datatable';
			?>
			<div class="mjschool-panel-body"><!-------------- Panel body. ------------------>
				<!--------------- Income tabbing. --------------->
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab_1 === 'report6_datatable' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=report6&tab1=report6_datatable" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report6_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income Report Datatable', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab_1 === 'report6_graph' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=report6&tab1=report6_graph" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report6_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Income Report Graph', 'mjschool' ); ?></a> 
					</li>
				</ul><!--------------- Income tabbing. --------------->
			</div><!-------------- Panel body. ------------------>
			<?php
			// Satrt Income Datatbale Report Tab. //
			if ( $active_tab_1 === 'report6_datatable' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-25px-res">
					<div class="mjschool-panel-body clearfix"><!--------- Panel body. ---------------->
						<form method="post"><!--------- Income form. ---------------->
							<div class="form-body mjschool-user-form">
								<div class="row">
									<div class="col-md-5">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input type="text"  id="sdate" class="form-control" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d', strtotime( 'first day of this month' ) ) ); } ?>" readonly>
												<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-5">
										<div class="form-group input">
											<div class="col-md-12 form-control">
												<input type="text"  id="edate" class="form-control" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
												<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
											</div>
										</div>
									</div>
									<div class="col-md-2">
										<input type="submit" name="report_6" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
									</div>	
								</div>
							</div>	
						</form><!--------- Income form. ---------------->
					</div><!--------- Panel body. ---------------->	
					<?php
					if ( isset( $_REQUEST['report_6'] ) ) {
						$start_date = sanitize_text_field(wp_unslash($_POST['sdate']));
						$end_date   = sanitize_text_field(wp_unslash($_POST['edate']));
					} else {
						$start_date = date( 'Y-m-d', strtotime( 'first day of this month' ) );
						$end_date   = date( 'Y-m-d', strtotime( 'last day of this month' ) );
					}
					global $wpdb;
					$table_income = $wpdb->prefix . 'mjschool_income_expense';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$report_6 = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $table_income WHERE invoice_type = %s AND income_create_date BETWEEN %s AND %s", 'income', $start_date, $end_date )
					);
					if ( ! empty( $report_6 ) ) {
						?>
						<div class="mjschool-panel-body"><!------------------ Panel body. --------------->
							<div class="table-responsive"><!------------------ Table responsive. --------------->
								<!-------------- Income list form. ------------------>
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="tblincome" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Student Name & Roll No.', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php
											if ( ! empty( $report_6 ) ) {
												$i = 0;
												foreach ( $report_6 as $result ) {
													$all_entry    = json_decode( $result->entry );
													$total_amount = 0;
													foreach ( $all_entry as $entry ) {
														$total_amount += $entry->amount;
													}
													if ( $i === 10 ) {
														$i = 0;
													}
													if ( $i === 0 ) {
														$color_class_css = 'mjschool-class-color0';
													} elseif ( $i === 1 ) {
														$color_class_css = 'mjschool-class-color1';
													} elseif ( $i === 2 ) {
														$color_class_css = 'mjschool-class-color2';
													} elseif ( $i === 3 ) {
														$color_class_css = 'mjschool-class-color3';
													} elseif ( $i === 4 ) {
														$color_class_css = 'mjschool-class-color4';
													} elseif ( $i === 5 ) {
														$color_class_css = 'mjschool-class-color5';
													} elseif ( $i === 6 ) {
														$color_class_css = 'mjschool-class-color6';
													} elseif ( $i === 7 ) {
														$color_class_css = 'mjschool-class-color7';
													} elseif ( $i === 8 ) {
														$color_class_css = 'mjschool-class-color8';
													} elseif ( $i === 9 ) {
														$color_class_css = 'mjschool-class-color9';
													}
													?>
													<tr>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">	
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png")?>" class="mjschool-massage-image mjschool-margin-top-3px">
																
															</p>
														</td>
														<td class="patient_name"><?php echo esc_html( mjschool_get_user_name_by_id( $result->supplier_name ) ); ?>-<?php echo esc_html( get_user_meta( $result->supplier_name, 'roll_id', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No.', 'mjschool' ); ?>"></i></td>
														<td class="income_amount"><?php echo '<span> ' . esc_html( mjschool_get_currency_symbol() ) . ' </span>' . esc_html( $total_amount ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
														<td class="status"><?php echo esc_html( mjschool_get_date_in_input_box( $result->income_create_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></td>
													</tr>
													<?php
													++$i;
												}
											}
											?>
										</tbody>        
									</table>
								</form><!-------------- Income list form. ------------------>
							</div><!------------------ Table responsive. --------------->
						</div><!------------------ Panel body. --------------->
						<?php
					} else {
						$page    = 'payment';
						$payment = mjschool_get_user_role_wise_filter_access_right_array( $page );
						if ( $payment['add'] === '1' ) {
							 ?>
							<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px"> 
								<a href="<?php echo esc_url(home_url().'?dashboard=mjschool_user&page=payment&tab=addincome' );?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.','mjschool' ); ?> </label>
								</div> 
							</div>		
							<?php
						}
						else
						{
							?>
							<div class="mjschool-calendar-event-new"> 
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
								
							</div>	
							<?php
						}
					}
					?>
				</div>
				<?php
			}
			// End Income Datatbale Report Tab. //
			// Start Income Graph Report Tab. //
			if ( $active_tab_1 === 'report6_graph' ) {
				?>
				<div class="mjschool-panel-body clearfix mjschool-margin-top-30px mjschool-padding-top-15px-res">
					<?php
					$month = array(
						'1'  => esc_html__( 'January', 'mjschool' ),
						'2'  => esc_html__( 'February', 'mjschool' ),
						'3'  => esc_html__( 'March', 'mjschool' ),
						'4'  => esc_html__( 'April', 'mjschool' ),
						'5'  => esc_html__( 'May', 'mjschool' ),
						'6'  => esc_html__( 'June', 'mjschool' ),
						'7'  => esc_html__( 'July', 'mjschool' ),
						'8'  => esc_html__( 'August', 'mjschool' ),
						'9'  => esc_html__( 'September', 'mjschool' ),
						'10' => esc_html__( 'Octomber', 'mjschool' ),
						'11' => esc_html__( 'November', 'mjschool' ),
						'12' => esc_html__( 'December', 'mjschool' ),
					);
					$year  = isset( $_POST['year'] ) ? sanitize_text_field(wp_unslash($_POST['year'])) : date( 'Y' );
					global $wpdb;
					$table_name = $wpdb->prefix . 'mjschool_income_expense';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$report_6 = $wpdb->get_results(
						$wpdb->prepare( "SELECT * FROM $table_name WHERE invoice_type = %s", 'income' )
					);
					foreach ( $report_6 as $result ) {
						$all_entry    = json_decode( $result->entry );
						$total_amount = 0;
						foreach ( $all_entry as $entry ) {
							$total_amount += $entry->amount;
							$q             = "SELECT EXTRACT(MONTH FROM income_create_date) as date, sum($total_amount) as count FROM " . $table_name . " WHERE invoice_type='income' AND YEAR(income_create_date) =" . $year . ' group by month(income_create_date) ORDER BY income_create_date ASC';
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$result = $wpdb->get_results( $q );
						}
					}
					$sumArray = array();
					foreach ( $result as $value ) {
						if ( isset( $sumArray[ $value->date ] ) ) {
							$sumArray[ $value->date ] = $sumArray[ $value->date ] + (int) $value->count;
						} else {
							$sumArray[ $value->date ] = (int) $value->count;
						}
					}
					$chart_array   = array();
					$chart_array[] = array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Income', 'mjschool' ) );
					$i             = 1;
					foreach ( $sumArray as $month_value => $count ) {
						$chart_array[] = array( $month[ $month_value ], (int) $count );
					}
					$options = array(
						'title'          => esc_html__( 'Income Payment Report By Month', 'mjschool' ),
						'titleTextStyle' => array( 'color' => '#66707e' ),
						'legend'         => array(
							'position'  => 'right',
							'textStyle' => array( 'color' => '#66707e' ),
						),
						'hAxis'          => array(
							'title'          => esc_html__( 'Month', 'mjschool' ),
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'maxAlternation' => 2,
						),
						'vAxis'          => array(
							'title'          => esc_html__( 'Income Payment', 'mjschool' ),
							'minValue'       => 0,
							'maxValue'       => 6,
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
						),
						'colors'         => array( '#22BAA0' ),
					);
					
					$GoogleCharts = new GoogleCharts();
					$chart        = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
					?>
					<div id="chart_div" class="chart_div">
						<?php
						if ( empty( $result ) ) {
							?>
							<div class="clear col-md-12"><h3><?php esc_html_e( 'There is not enough data to generate report.', 'mjschool' ); ?> </h3></div>
							<?php
						}
						?>
					</div>
					<!-- Javascript. --> 
					<script type="text/javascript">
						"use strict";
						<?php
						if ( ! empty( $result ) ) {
							echo wp_kses_post( $chart );
						}
						?>
					</script>
				</div>
				<?php
			} //END Start Income Graph Report Tab. //
		} //End Income Payment Report Tab. //
		// Start Fees Payment Report Tab. //
		if ( $active_tab === 'report4' ) {
			$active_tab_1 = isset( $_GET['tab1'] ) ? sanitize_text_field(wp_unslash($_GET['tab1'])) : 'report4_datatable';
			?>
			<div class="mjschool-panel-body"><!-------------- Panel body. ------------------>
				<!--------------- Income tabbing. --------------->
				<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
					<li class="<?php if ( $active_tab_1 === 'report4_datatable' ) { ?> active<?php } ?>">			
						<a href="?dashboard=mjschool_user&page=report&tab=report4&tab1=report4_datatable" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report4_datatable' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment Datatable', 'mjschool' ); ?></a> 
					</li>
					<li class="<?php if ( $active_tab_1 === 'report4_graph' ) { ?> active<?php } ?>">
						<a href="?dashboard=mjschool_user&page=report&tab=report4&tab1=report4_graph" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab_1 ) === 'report4_graph' ? 'active' : ''; ?>"> <?php esc_html_e( 'Fees Payment Graph', 'mjschool' ); ?></a> 
					</li>
				</ul><!--------------- Income tabbing. --------------->
			</div><!-------------- Panel body. ------------------>
			<?php
			// Fees Payment Datatbale Report Tab. //
			if ( $active_tab_1 === 'report4_datatable' ) {
				?>
				<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-25px-res"><!-------------- Panel body. ------------------>
					<!--------------- Fees payment form. -------------------->
					<form method="post" id="fee_payment_report">  
						<div class="form-body mjschool-user-form"><!-------------- Form body. ------------------>
							<div class="row">
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select name="class_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control mjschool-load-fee-type-single validate[required]">
										<?php
										$select_class = isset( $_REQUEST['class_id'] ) ? $_REQUEST['class_id'] : '';
										?>
										<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
										<?php
										foreach ( mjschool_get_all_class() as $classdata ) {
											?>
											<option  value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php echo selected( $select_class, $classdata['class_id'] ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
											<?php
										}
										?>
									</select>       
								</div>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
									<?php
									$class_section = '';
									if ( isset( $_REQUEST['class_section'] ) ) {
										$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
									}
									?>
									<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
										<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
										<?php
										if ( isset( $_REQUEST['class_section'] ) ) {
											$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
											echo esc_html( $class_section );
											foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
												?>
												<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
												<?php
											}
										}
										?>
									</select>     
								</div>
								<div class="col-md-6 input">
									<label class="ml-1 mjschool-custom-top-label top" for="fees_data"><?php esc_html_e( 'FeesType', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select id="fees_data" class="mjschool-line-height-30px form-control validate[required]" name="fees_id">
										<option value=""><?php esc_html_e( 'Select Fee Type', 'mjschool' ); ?></option>
										<?php
										if ( isset( $_REQUEST['fees_id'] ) ) {
											echo '<option value="' . esc_attr( $_REQUEST['fees_id'] ) . '" ' . selected( $_REQUEST['fees_id'], $_REQUEST['fees_id'] ) . '>' . esc_html( mjschool_get_fees_term_name( $_REQUEST['fees_id'] ) ) . '</option>';
										}
										?>
									</select>   
								</div>
								<div class="col-md-6 input mjschool-error-msg-left-margin">
									<label class="ml-1 mjschool-custom-top-label top" for="fee_status"><?php esc_html_e( 'Payment Status', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									<select id="fee_status" class="mjschool-line-height-30px form-control validate[required]" name="fee_status">
										<?php
										$select_payment = isset( $_REQUEST['fee_status'] ) ? $_REQUEST['fee_status'] : '';
										?>
										<option value=""><?php esc_html_e( 'Select Payment Status', 'mjschool' ); ?></option>
										<option value="0" <?php echo selected( $select_payment, 0 ); ?>><?php esc_html_e( 'Not Paid', 'mjschool' ); ?></option>
										<option value="1" <?php echo selected( $select_payment, 1 ); ?>><?php esc_html_e( 'Partially Paid', 'mjschool' ); ?></option>
										<option value="2" <?php echo selected( $select_payment, 2 ); ?>><?php esc_html_e( 'Fully paid', 'mjschool' ); ?></option>
									</select>   
								</div>
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text"  id="sdate" class="form-control" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d', strtotime( 'first day of this month' ) ) ); } ?>" readonly>
											<label for="userinput1"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group input">
										<div class="col-md-12 form-control">
											<input type="text"  id="edate" class="form-control" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
											<label for="userinput1"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
								<div class="col-md-6">
									<input type="submit" name="report_4" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
								</div>
							</div>
						</div><!-------------- Form body. ------------------>
					</form><!--------------- Fees payment form. -------------------->
				</div><!-------------- Panel body. ------------------>
				<?php
				if ( isset( $_POST['report_4'] ) ) {
					if ( sanitize_text_field(wp_unslash($_POST['class_id'])) != '' && sanitize_text_field(wp_unslash($_POST['fees_id'])) != '' && sanitize_text_field(wp_unslash($_POST['sdate'])) != '' && sanitize_text_field(wp_unslash($_POST['edate'])) != '' ) {
						$class_id   = sanitize_text_field(wp_unslash($_POST['class_id']));
						$section_id = 0;
						if ( isset( $_POST['class_section'] ) ) {
							$section_id = sanitize_text_field(wp_unslash($_POST['class_section']));
						}
						$fee_term         = sanitize_text_field(wp_unslash($_POST['fees_id']));
						$payment_status   = sanitize_text_field(wp_unslash($_POST['fee_status']));
						$sdate            = sanitize_text_field(wp_unslash($_POST['sdate']));
						$edate            = sanitize_text_field(wp_unslash($_POST['edate']));
						$result_feereport = $obj_library->mjschool_get_payment_report_front( $class_id, $fee_term, $payment_status, $sdate, $edate, $section_id );
					}
					if ( ! empty( $result_feereport ) ) {
						?>
						<div class="table-responsive"><!-------------- Table responsive. ---------------->
							<table id="payment_report" class="display" cellspacing="0" width="100%">
								<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
									<tr>
										<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Fees Term', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Student Name & Roll No.', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Start To End Year', 'mjschool' ); ?></th>
										<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php
									if ( ! empty( $result_feereport ) ) {
										$i = 0;
										foreach ( $result_feereport as $retrieved_data ) {
											if ( $i === 0 ) {
												$color_class_css = 'mjschool-class-color0';
											} elseif ( $i === 1 ) {
												$color_class_css = 'mjschool-class-color1';
											} elseif ( $i === 2 ) {
												$color_class_css = 'mjschool-class-color2';
											} elseif ( $i === 3 ) {
												$color_class_css = 'mjschool-class-color3';
											} elseif ( $i === 4 ) {
												$color_class_css = 'mjschool-class-color4';
											} elseif ( $i === 5 ) {
												$color_class_css = 'mjschool-class-color5';
											} elseif ( $i === 6 ) {
												$color_class_css = 'mjschool-class-color6';
											} elseif ( $i === 7 ) {
												$color_class_css = 'mjschool-class-color7';
											} elseif ( $i === 8 ) {
												$color_class_css = 'mjschool-class-color8';
											} elseif ( $i === 9 ) {
												$color_class_css = 'mjschool-class-color9';
											}
											?>
											<tr>
												<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">	
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png")?>" class="mjschool-massage-image mjschool-margin-top-3px">
													</p>
												</td>
												<?php
												$fees_id=explode( ',',$retrieved_data->fees_id);
												$fees_type=array();
												foreach($fees_id as $id)
												{ 
													$fees_type[] = mjschool_get_fees_term_name($id);
												}
												?>
												<td><?php echo esc_html( implode( " , " ,$fees_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Term','mjschool' );?>"></i></td>
												<td><?php echo esc_html( mjschool_get_user_name_by_id($retrieved_data->student_id ) );?>-<?php echo esc_html( get_user_meta($retrieved_data->student_id, 'roll_id',true ) );?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No.','mjschool' );?>"></i></td>
												<td><?php echo esc_html( mjschool_get_class_name($retrieved_data->class_id ) );?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name','mjschool' );?>"></i></td>
												<td>
													<?php 
													$payment_status=mjschool_get_payment_status($retrieved_data->fees_pay_id);
													if ( $payment_status === 'Not Paid' )
													{
													echo "<span class='mjschool-red-color'>";
													}
													elseif ( $payment_status === 'Partially Paid' )
													{
														echo "<span class='mjschool-purpal-color'>";
													}
													else
													{
														echo "<span class='mjschool-green-color'>";
													}
													echo esc_html__( "$payment_status","mjschool" );
													echo "</span>";	
													?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status','mjschool' );?>"></i>
												</td>
												<td><?php echo esc_html( mjschool_get_currency_symbol( ) ).' '.esc_html( $retrieved_data->total_amount);?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount','mjschool' );?>"></i></td>
												<?php
												$Due_amt = $retrieved_data->total_amount-$retrieved_data->fees_paid_amount;
												?>
												<td><?php echo esc_html( mjschool_get_currency_symbol( ) ).' '.esc_html( $Due_amt);?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount','mjschool' );?>"></i></td>
												<td><?php echo esc_html( $retrieved_data->start_year).'-'.esc_html( $retrieved_data->end_year);?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start To End Year','mjschool' );?>"></i></td>
												<td class="action">  
													<div class="mjschool-user-dropdown">
														<ul  class="mjschool_ul_style">
															<li >
																<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
																</a>
																
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<li class="mjschool-float-left-width-100px">
																		<a href="?dashboard=mjschool_user&page=feepayment&tab=view_fesspayment&idtest=<?php echo esc_attr( $retrieved_data->fees_pay_id ); ?>&view_type=view_payment" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																	</li>
																</ul>
															</li>
														</ul>
													</div>	
												</td>
											</tr>
											<?php
											++$i;
										}
									}
									?>
								</tbody>
							</table>
						</div><!-------------- Table responsive. ---------------->
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new"> 
							
							<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
							
						</div>	
						<?php
					}
				}
			}
			// End Fees Payment Datatbale Report Tab. //
			// Fees Payment Graph Report Tab. //
			if ( $active_tab_1 === 'report4_graph' ) {
				?>
				<div class="mjschool-panel-body clearfix">
					<?php
					$month = array(
						'1'  => esc_html__( 'January', 'mjschool' ),
						'2'  => esc_html__( 'February', 'mjschool' ),
						'3'  => esc_html__( 'March', 'mjschool' ),
						'4'  => esc_html__( 'April', 'mjschool' ),
						'5'  => esc_html__( 'May', 'mjschool' ),
						'6'  => esc_html__( 'June', 'mjschool' ),
						'7'  => esc_html__( 'July', 'mjschool' ),
						'8'  => esc_html__( 'August', 'mjschool' ),
						'9'  => esc_html__( 'September', 'mjschool' ),
						'10' => esc_html__( 'Octomber', 'mjschool' ),
						'11' => esc_html__( 'November', 'mjschool' ),
						'12' => esc_html__( 'December', 'mjschool' ),
					);
					$year  = isset( $_POST['year'] ) ? sanitize_text_field(wp_unslash($_POST['year'])) : date( 'Y' );
					global $wpdb;
					$table_name = $wpdb->prefix . 'mjschool_fees_payment';
					$q          = 'SELECT EXTRACT(MONTH FROM paid_by_date) as date, sum(fees_paid_amount) as count FROM ' . $table_name . ' WHERE YEAR(paid_by_date) =' . $year . ' group by month(paid_by_date) ORDER BY paid_by_date ASC';
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$result = $wpdb->get_results( $q );
					$sumArray = array();
					foreach ( $result as $value ) {
						if ( isset( $sumArray[ $value->date ] ) ) {
							$sumArray[ $value->date ] = $sumArray[ $value->date ] + (int) $value->count;
						} else {
							$sumArray[ $value->date ] = (int) $value->count;
						}
					}
					$chart_array   = array();
					$chart_array[] = array( esc_html__( 'Month', 'mjschool' ), esc_html__( 'Fees Payment', 'mjschool' ) );
					$i             = 1;
					foreach ( $sumArray as $month_value => $count ) {
						$chart_array[] = array( $month[ $month_value ], (int) $count );
					}
					$options = array(
						'title'          => esc_html__( 'Fees Payment Report By Month', 'mjschool' ),
						'titleTextStyle' => array( 'color' => '#66707e' ),
						'legend'         => array(
							'position'  => 'right',
							'textStyle' => array( 'color' => '#66707e' ),
						),
						'hAxis'          => array(
							'title'          => esc_html__( 'Month', 'mjschool' ),
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'maxAlternation' => 2,
						),
						'vAxis'          => array(
							'title'          => esc_html__( 'Fees Payment', 'mjschool' ),
							'minValue'       => 0,
							'maxValue'       => 6,
							'format'         => '#',
							'titleTextStyle' => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
							'textStyle'      => array(
								'color'    => '#66707e',
								'fontSize' => 16,
								'bold'     => true,
								'italic'   => false,
								'fontName' => 'Poppins',
							),
						),
						'colors'         => array( '#22BAA0' ),
					);
					
					$GoogleCharts = new GoogleCharts();
					$chart        = $GoogleCharts->load( 'column', 'chart_div' )->get( $chart_array, $options );
					?>
					<div id="chart_div" class="chart_div">
						<?php
						if ( empty( $result ) ) {
							?>
							<div class="clear col-md-12"><h3><?php esc_html_e( 'There is not enough data to generate report.', 'mjschool' ); ?> </h3></div>
							<?php
						}
						?>
					</div>
					<!-- Javascript. --> 
					<script type="text/javascript">
						"use strict";
						<?php
						if ( ! empty( $result ) ) {
							echo wp_kses_post( $chart );
						}
						?>
					</script>
				</div>
				<?php
			}
			// End Fees Payment Graph Report Tab. //
			?>
			<div class="clearfix"> </div>
			<?php
		}
		// End Fees Payment  Report Tab. //
		// Result Report Tab. //
		if ( $active_tab === 'report5' ) {
			?>
			<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
				<form method="post" id="result_report">  
					<div class="form-body mjschool-user-form">
						<div class="row">
							<div class="col-md-3 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<select name="class_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required] class_id_exam">
									<?php
									$class_id = '';
									if ( isset( $_REQUEST['class_id'] ) ) {
										$class_id = intval( wp_unslash( $_REQUEST['class_id'] ) );
									}
									?>
									<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
									<?php
									foreach ( mjschool_get_all_class() as $classdata ) {
										?>
										<option  value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?> ><?php echo esc_html( $classdata['class_name'] ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<div class="col-md-3 input">
								<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								<?php
								$class_section = '';
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
								}
								?>
								<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
									<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
									<?php
									if ( isset( $_REQUEST['class_section'] ) ) {
										$class_section = sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) );
										echo esc_html( $class_section );
										foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
											?>
											<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="col-md-3 input">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"><?php esc_html_e( 'Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								<?php
								$tablename      = 'mjschool_exam';
								$retrieve_class_data = mjschool_get_all_data( $tablename );
								$exam_id = '';
								if ( isset( $_REQUEST['exam_id'] ) ) {
									$exam_id = intval( wp_unslash( $_REQUEST['exam_id'] ) );
								}
								?>
								<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required]">
									<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
									<?php
									foreach ( $retrieve_class_data as $retrieved_data ) {
										?>
										<option value="<?php echo esc_attr( $retrieved_data->exam_id ); ?>" <?php selected( $retrieved_data->exam_id, $exam_id ); ?>><?php echo esc_html( $retrieved_data->exam_name ); ?></option>
										<?php
									}
									?>
								</select>
							</div>
							<div class="col-md-3">
								<input type="submit" name="report_5" value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="clearfix mjschool-panel-body">
				<?php
				if ( isset( $_POST['report_5'] ) ) {
					$exam_id  = sanitize_text_field(wp_unslash($_REQUEST['exam_id']));
					$class_id = sanitize_text_field(wp_unslash($_REQUEST['class_id']));
					
					if( isset( $_REQUEST['class_section']) && sanitize_text_field(wp_unslash($_REQUEST['class_section'])) != ""){
						$subject_list = $obj_marks->mjschool_student_subject(sanitize_text_field(wp_unslash($_REQUEST['class_id'])),sanitize_text_field(wp_unslash($_REQUEST['class_section'])));
						$exlude_id = mjschool_approve_student_list();
						$student = get_users(array( 'meta_key' => 'class_section', 'meta_value' =>sanitize_text_field(wp_unslash($_REQUEST['class_section'])), 'meta_query'=> array(array( 'key' => 'class_name','value' =>sanitize_text_field(wp_unslash($_REQUEST['class_id'])),'compare' => '=' ) ),'role'=>'student','exclude'=>$exlude_id ) );	
					}
					else
					{ 
						$subject_list = $obj_marks->mjschool_student_subject(sanitize_text_field(wp_unslash($_REQUEST['class_id'])));
						$exlude_id = mjschool_approve_student_list();
						$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => sanitize_text_field(wp_unslash($_REQUEST['class_id'])),'role'=>'student','exclude'=>$exlude_id ) );
					}
					?>
					<script type="text/javascript">
						(function(jQuery){
							"use strict";
							jQuery(function(){
								// DataTable initialization.
								const tableAdvanceReport = jQuery( '#advance-report-custom' ).DataTable({
									order: [[2, "desc"]],
									dom: 'lifrtp',
									aoColumns: [
										{ bSortable: false },
										{ bSortable: true },
										{ bSortable: true },
										<?php if ( ! empty( $subject_list ) ) : ?>
											<?php foreach ( $subject_list as $sub_id ) : ?>
												{ bSortable: true },
											<?php endforeach; ?>
										<?php endif; ?>
										{ bSortable: true }
									],
									language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
								});
								// Add placeholder to search box.
								jQuery('.dataTables_filter input')
									.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
									.attr("id", "datatable_search")
									.attr("name", "datatable_search");
							});
						})(jQuery);
					</script>
					<div class="table-responsive">
						<table id="advance-report-custom" class="display" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>  
									<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
									<?php
									if ( ! empty( $subject_list ) ) {
										foreach ( $subject_list as $sub_id ) {
											echo '<th> ' . esc_html( $sub_id->sub_name ) . ' </th>';
										}
									}
									?>
									<th><?php esc_html_e( 'Total', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ! empty( $student ) ) {
									$i = 0;
									foreach ( $student as $mjschool_user ) {
										if ( $i === 10 ) {
											$i = 0;
										}
										if ( $i === 0 ) {
											$color_class_css = 'mjschool-class-color0';
										} elseif ( $i === 1 ) {
											$color_class_css = 'mjschool-class-color1';
										} elseif ( $i === 2 ) {
											$color_class_css = 'mjschool-class-color2';
										} elseif ( $i === 3 ) {
											$color_class_css = 'mjschool-class-color3';
										} elseif ( $i === 4 ) {
											$color_class_css = 'mjschool-class-color4';
										} elseif ( $i === 5 ) {
											$color_class_css = 'mjschool-class-color5';
										} elseif ( $i === 6 ) {
											$color_class_css = 'mjschool-class-color6';
										} elseif ( $i === 7 ) {
											$color_class_css = 'mjschool-class-color7';
										} elseif ( $i === 8 ) {
											$color_class_css = 'mjschool-class-color8';
										} elseif ( $i === 9 ) {
											$color_class_css = 'mjschool-class-color9';
										}
										$total = 0;
										?>
										<tr>
											<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
												<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
														
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png")?>" class="mjschool-massage-image mjschool-margin-top-3px">
													
												</p>
											</td>
											<td><?php echo esc_html( $mjschool_user->roll_id ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i></td>
											<td><?php echo esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
											<?php
											if ( ! empty( $subject_list ) ) {
												foreach ( $subject_list as $sub_id ) {
													$mark_detail = $obj_marks->mjschool_subject_makrs_detail_by_user( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
													if ( $mark_detail ) {
														$mark_id = $mark_detail->mark_id;
														$marks   = $mark_detail->marks;
														$total  += $marks;
													} else {
														$marks         = 0;
														$attendance    = 0;
														$marks_comment = '';
														$total        += 0;
														$mark_id       = '0';
													}
													?>
													<td><?php echo esc_html( $marks ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( $sub_id->sub_name ); ?> <?php esc_html_e( 'Mark', 'mjschool' ); ?>"></i></td>
													<?php
												}
												?>
												<td><?php echo esc_html( $total ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Marks', 'mjschool' ); ?>"></i></td>
												<?php
											} else {
												?>
												<td><?php echo esc_html( $total ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Marks', 'mjschool' ); ?>"></i></td>
												<?php
											}
											?>
										</tr>
										<?php
										++$i;
									}
								}
								?>
							</tbody>
						</table>
					</div>
					<!-- End panel body div. -->
					<?php
				}
				?>
			</div>
			<?php
		}
		// End Result Report Tab. //
		?>
	</div><!----------- Panel body. ------------->
</div><!----------- Panel white. ------------->