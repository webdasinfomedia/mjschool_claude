<?php
/**
 * The admin interface controller for the Advanced Reports module.
 *
 * This file manages the main tabbed navigation and dynamic loading of
 * individual report views, including Student, Admission, Exam Result,
 * Teacher Performance, Finance, and Attendance Reports.
 *
 * It verifies user permissions, ensures access rights, and loads
 * corresponding report templates based on the selected tab and sub-tab.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */

defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript.. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_view = '1';
} else {
	$user_access      = mjschool_get_user_role_wise_filter_access_right_array( 'report' );
	$user_access_view = $user_access['view'];
	if ( isset( $_REQUEST ['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
	}
}
$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'student_information_report';
?>
<div class="mjschool-page-inner"><!-- Panel Inner. --->
	<div class=" mjschool-transport-list mjschool-main-list-margin-5px">
		<div class="mjschool-panel-white"> <!-- Panel White. --->
			<div class="mjschool-panel-body"> <!-- Panel-body. --->
				<?php
				if ( $active_tab === 'student_information_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'student_report';
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_advance_student_infomation_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'student_report' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_information_report&tab1=student_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'student_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Student Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'admission_report' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_information_report&tab1=admission_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'admission_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Admission Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'exam_result_report' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_information_report&tab1=exam_result_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'exam_result_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Exam Result Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'teacher_perfomance_report' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_information_report&tab1=teacher_perfomance_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'teacher_perfomance_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Teacher Perfomance Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( isset( $active_tab ) && $active_tab === 'teacher_perfomance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/teacher-perfomance-report.php';
						}
						if ( isset( $active_tab ) && $active_tab === 'student_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/student-report.php';
						}
						if ( isset( $active_tab ) && $active_tab === 'exam_result_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/exam-result-report.php';
						}
						if ( isset( $active_tab ) && $active_tab === 'admission_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/admission-report.php';
						}
						?>
					</div>
					<?php
				}
				if ( $active_tab === 'finance_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'fees_payment_datatable';
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_advance_finance_report_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'fees_payment_datatable' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=finance_report&tab1=fees_payment_datatable&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'fees_payment_datatable' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Fees Payment Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( isset( $active_tab ) && $active_tab === 'fees_payment_datatable' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/fees-payment.php';
						}
						?>
					</div>
					<?php
				}
				if ( $active_tab === 'student_attendance_report' ) {
					$active_tab = isset( $_GET['tab1'] ) ? $_GET['tab1'] : 'attendance_report';
					?>
					<?php $nonce = wp_create_nonce( 'mjschool_advance_attendance_report_tab' ); ?>
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab === 'attendance_report' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_attendance_report&tab1=attendance_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'attendance_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Attendance Report', 'mjschool' ); ?></a>
						</li>
						<li class="<?php if ( $active_tab === 'leave_report' ) {?>active<?php } ?>"><a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_advance_report&tab=student_attendance_report&tab1=leave_report&_wpnonce='.rawurlencode( $nonce ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab  ) === 'leave_report' ? 'active' : ''; ?>">
							<?php esc_html_e( 'Leave Report', 'mjschool' ); ?></a>
						</li>
					</ul>
					<div class="clearfix mjschool-panel-body">
						<?php
						if ( isset( $active_tab ) && $active_tab === 'attendance_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/attendance-report-datatable.php';
						}
						if ( isset( $active_tab ) && $active_tab === 'leave_report' ) {
							require_once MJSCHOOL_ADMIN_DIR . '/advance-report/leave-report.php';
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>