<?php

/**
 * Monthly Attendance Report Template.
 *
 * Displays the monthly attendance filtering form and generates
 * the student-wise attendance report table. Also handles CSV export
 * functionality for monthly attendance data.
 *
 * Functionality:
 * - Filters by class, section, student status, student, year, and month.
 * - Generates day-wise attendance records (P/L/A/H/F).
 * - Exports monthly attendance as CSV.
 * - Uses DataTables for sortable and printable reports.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for monthly attendance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<?php
		$class_id = '';
		?>
		<form method="post" id="student_attendance">
			<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
			<div class="form-body mjschool-user-form">
				<div class="row">
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
							<option value=""><?php esc_html_e( 'Select Class', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = $_REQUEST['class_id'];
							}
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-3 mb-3 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></label>
							<?php
							$class_section = '';
							if ( isset( $_REQUEST['class_section'] ) ) {
								$class_section = $_REQUEST['class_section'];
							}
							?>
							<select name="class_section" class="mjschool-line-height-30px form-control" id="class_section">
								<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
								<?php
								if ( isset( $_REQUEST['class_section'] ) ) {
									$class_section = $_REQUEST['class_section'];
									foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
										?>
										<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</div>
					<?php }?>
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="student_status"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></label>
						<select name="status" id="student_status" class="mjschool-line-height-30px form-control">
							<?php
							$status = ''; // Default to empty.
							if ( isset( $_REQUEST['status'] ) ) {
								$status = $_REQUEST['status'];
							}
							?>
							<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'mjschool' ); ?></option>
							<option value="deactive" <?php selected( $status, 'deactive' ); ?>><?php esc_html_e( 'Deactive', 'mjschool' ); ?></option>
						</select>
					</div>
					<div class="col-sm-3 col-md-3 col-lg-3 col-xl-3 input mjschool-class-section-hide">
						<label class="ml-1 mjschool-custom-top-label top" for="student_list"><?php esc_html_e( 'Select Student', 'mjschool' ); ?></label>
						<select name="student_id" id="student_list" class="form-control mjschool-max-width-100px mjschool_heights_47px" >
							<option value=""><?php esc_html_e( 'All Student', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['student_id'] ) ) {
								$class_section = $_REQUEST['class_section'];
								$class_id      = $_REQUEST['class_id'];
								$exlude_id     = mjschool_approve_student_list();
								if ( ! empty( $class_section ) ) {
                                    
                                    $student_data =     get_users(array( 'meta_key' => 'class_section', 'meta_value' => $class_section, 'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id ) ), 'role' => 'student', 'exclude' => $exlude_id ) );
                                } else {
                                    $student_data = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
                                    
								}
								foreach ( $student_data as $studentdata ) {
									?>
									<option value="<?php echo esc_attr( $studentdata->ID ); ?>" <?php selected( $_REQUEST['student_id'], $studentdata->ID ); ?>><?php echo esc_html( mjschool_student_display_name_with_roll( $studentdata->ID ) ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-year"><?php esc_html_e( 'Year', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select id="mjschool-year" name="year" class="mjschool-line-height-30px form-control validate[required]">
							<option value=""><?php esc_html_e( 'Select year', 'mjschool' ); ?></option>
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
					<div class="col-md-3 mb-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="month"><?php esc_html_e( 'Month', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select id="month" name="month" class="mjschool-line-height-30px validate[required] form-control class_id_exam">
							<option value=""><?php esc_html_e( 'Select Month', 'mjschool' ); ?></option>
							<?php
							$selected_month = date( 'm' ); // Current month.
							for ( $i_month = 1; $i_month <= 12; $i_month++ ) {
								$selected = ( $selected_month === $i_month ? ' selected' : '' );
								$data     = date( 'F', mktime( 0, 0, 0, $i_month ) );
								echo '<option value="' . esc_attr( $i_month ) . '"' . esc_attr( $selected ) . '>' . esc_html( $data, 'mjschool' ) . '</option>' . "\n";
							}
							?>
						</select>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	// --- Download Monthly Attendance CSV file --start. --//
	if ( isset( $_POST['monthly_attendance_csv_download'] ) ) {
		$class_id      = $_POST['class_id'];
		$class_section = $_POST['class_section'];
		$year          = $_POST['year'];
		$month         = $_POST['month'];
		$student_id    = $_POST['student_id'];
		// Fetch day and date by year,Month.
		$list = array();
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
		if ( $student_id === '' ) {
			$exlude_id = mjschool_approve_student_list();
			if ( $class_id === 'all class' && $class_section === '' ) {
				$student = get_users(
					array(
						'role'    => 'student',
						'exclude' => $exlude_id,
					)
				);
				sort( $student );
			} elseif ( $class_section === '' ) {
                
                $student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
                
				sort( $student );
			} else {
                
                $student =     get_users(array( 'meta_key' => 'class_section', 'meta_value' => $class_section, 'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id ) ), 'role' => 'student', 'exclude' => $exlude_id ) );
                
				sort( $student );
			}
		} else {
			$student[] = get_userdata( $student_id );
		}
		$header   = array();
		$header[] = 'Student';
		$header[] = 'Present';
		$header[] = 'Late';
		$header[] = 'Absent';
		$header[] = 'Half Day';
		$header[] = 'Holiday';
		foreach ( $day_date as $data ) {
			$header[] = $data;
		}
		$filename = 'export/mjschool-monthly-attendance.csv';
		$fh       = fopen( MJSCHOOL_PLUGIN_DIR . '/sample-csv/' . $filename, 'w' ) or wp_die( "can't open file" );
		fputcsv( $fh, $header );
		foreach ( $student as $mjschool_user ) {
			$row                     = array();
			$class_id                = get_user_meta( $mjschool_user->ID, 'class_name', true );
			$student_name            = mjschool_get_display_name( $mjschool_user->ID );
			$Present                 = 'Present';
			$total_present           = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Present );
			$total_present_count     = count( $total_present );
			$Late                    = 'Late';
			$total_late              = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Late );
			$total_late_count        = count( $total_late );
			$absent                  = 'Absent';
			$total_absent            = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $absent );
			$total_absent_count      = count( $total_absent );
			$Half_Day                = 'Half Day';
			$total_Half_day          = mjschool_attendance_report_get_status_for_student_id( $month_first_date, $month_last_date, $class_id, $mjschool_user->ID, $Half_Day );
			$total_Half_day_count    = count( $total_Half_day );
			$total_Holiday_day       = mjschool_get_all_holiday_by_month_year( $month, $year );
			$total_Holiday_day_count = count( $total_Holiday_day );
			$row[]                   = $student_name;
			$row[]                   = $total_present_count;
			$row[]                   = $total_late_count;
			$row[]                   = $total_absent_count;
			$row[]                   = $total_Half_day_count;
			$row[]                   = $total_Holiday_day_count;
			foreach ( $date_list as $date ) {
				$status = mjschool_attendance_report_all_status_value( $date, $class_id, $mjschool_user->ID );
				$row[]  = $status;
			}
			fputcsv( $fh, $row );
		}
		fclose( $fh );
		// Download csv file.
		ob_clean();
		$file = MJSCHOOL_PLUGIN_DIR . '/sample-csv/export/mjschool-monthly-attendance.csv'; // File location.
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
		header( 'Connection: close' );
		readfile( $file );
		die();
	}
	// --- Download Monthly Attendance CSV file -- End. --//
	// -------------- MONTHLY ATTENDANCE Report. ---------------//
	if ( isset( $_REQUEST['view_attendance'] ) ) {
		$class_id      = sanitize_text_field( $_POST['class_id'] );
		if( isset( $_POST['class_section'] ) ){
			$class_section = sanitize_text_field( $_POST['class_section'] );
		}
		else{
			$class_section = '';
		}
		$year          = sanitize_text_field( $_POST['year'] );
		$month         = sanitize_text_field( $_POST['month'] );
		$status        = $_POST['status'];
		$student_id    = isset($_POST['student_id'])? sanitize_text_field( $_POST['student_id'] ):'';
	} else {
		$class_id   = '';
		$student_id = '';
		$status     = 'active';
		$year       = date( 'Y' );
		$month      = date( 'm' );
	}
	// Fetch day and date by year,Month.
	$list = array();
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
	if ( $class_id === '' ) {
		$student = '';
	} elseif ( $student_id === '' ) {
		if ( $status === 'active' ) {
			$exlude_id = mjschool_approve_student_list();
			if ( $class_id === 'all class' && $class_section === '' ) {
				$student = get_users(
					array(
						'role'    => 'student',
						'exclude' => $exlude_id,
					)
				);
				sort( $student );
			} elseif ( $class_section === '' ) {
                
                $student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'role' => 'student', 'exclude' => $exlude_id ) );
                
				sort( $student );
			} else {
                
                $student =     get_users(array( 'meta_key' => 'class_section', 'meta_value' => $class_section, 'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id ) ), 'role' => 'student', 'exclude' => $exlude_id ) );
                
				sort( $student );
			}
		} else {
            
            if ($class_id === "all class" && $class_section === "") {
                $student = get_users(array(
                    'role'       => 'student',
                    'meta_query' => array(
                        array(
                            'key'     => 'hash',
                            'compare' => 'EXISTS'
                        )
                    )
                ) );
                sort($student);
            } elseif ($class_section === "") {
                $student = get_users(array(
                    'role' => 'student',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'class_name',
                            'value'   => $class_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'hash',
                            'compare' => 'EXISTS'
                        )
                    )
                ) );
                sort($student);
            } else {
                $student = get_users(array(
                    'role' => 'student',
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'class_name',
                            'value'   => $class_id,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'class_section',
                            'value'   => $class_section,
                            'compare' => '='
                        ),
                        array(
                            'key'     => 'hash',
                            'compare' => 'EXISTS'
                        )
                    )
                ) );
                sort($student);
            }
            
		}
	} else {
		$student   = array();
		$student[] = get_userdata( $student_id );
	}
	?>
	<script type="text/javascript">
		(function (jQuery) {
			"use strict";
			jQuery(document).ready(function () {
				var table = jQuery( '#mjschool-class-attendance-list-report' ).DataTable({
					responsive: false,
					order: [[2, "desc"]],
					dom: 'Bflrtip',
					buttons: [
						{
							extend: 'csv',
							text: '<?php echo esc_html__( 'csv', 'mjschool' ); ?>',
							title: '<?php echo esc_html__( 'Student Attendance Report', 'mjschool' ); ?>'
						},
						{
							extend: 'print',
							text: '<?php echo esc_html__( 'Print', 'mjschool' ); ?>',
							title: '<?php echo esc_html__( 'Student Attendance Report', 'mjschool' ); ?>'
						}
					],
					aoColumns: [
						{ "bSortable": true },
						{ "bSortable": false },
						{ "bSortable": false },
						{ "bSortable": false },
						{ "bSortable": false },
						// Dynamically add day columns.
						<?php foreach ( $day_date as $data ) : ?>
							{ "bSortable": false },
						<?php endforeach; ?>
						{ "bSortable": false }
					],
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				});
				jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>");
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			});
		})(jQuery);
	</script>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $student ) ) {
			?>
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
					<div class="btn-place"></div>
					<table id="mjschool-class-attendance-list-report" class="display mjschool-class-att-repost-tbl" cellspacing="0" width="100%">
						<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
						<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
						<input type="hidden" name="year" value="<?php echo esc_attr( $year ); ?>" />
						<input type="hidden" name="month" value="<?php echo esc_attr( $month ); ?>" />
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
								$class_id = get_user_meta( $mjschool_user->ID, 'class_name', true );
								foreach ( $date_list as $date ) {
									$count[] = mjschool_attendance_report_all_status_value( $date, $class_id, $mjschool_user->ID );
								}
								?>
								<tr>
									<td class='monthly_atttendance_report'>
										<?php echo esc_html( mjschool_student_display_name_with_roll( $mjschool_user->ID ) ); ?>
									</td>
									<td class='monthly_atttendance_report'>
										<?php
										$countP = 0;
										foreach ( $count as $value ) {
											if ( $value === 'P' ) {
												++$countP;
											}
										}
										echo esc_html( $countP );
										?>
									</td>
									<td class='monthly_atttendance_report'>
										<?php
										$countL = 0;
										foreach ( $count as $value ) {
											if ( $value === 'L' ) {
												++$countL;
											}
										}
										echo esc_html( $countL );
										?>
									</td>
									<td>
										<?php
										$countA = 0;
										foreach ( $count as $value ) {
											if ( $value === 'A' ) {
												++$countA;
											}
										}
										echo esc_html( $countA );
										?>
									</td>
									<td>
										<?php
										$countF = 0;
										foreach ( $count as $value ) {
											if ( $value === 'F' ) {
												++$countF;
											}
										}
										echo esc_html( $countF );
										?>
									</td>
									<td>
										<?php
										$countH = 0;
										foreach ( $count as $value ) {
											if ( $value === 'H' ) {
												++$countH;
											}
										}
										echo esc_html( $countH );
										?>
									</td>
									<?php
									$count = array();
									foreach ( $date_list as $date ) {
										?>
										<td class="mjschool-att-status-color">
											<span class="<?php echo esc_attr( mjschool_attendance_report_all_status_value( $date, $class_id, $mjschool_user->ID ) ); ?>">
												<?php echo esc_html( mjschool_attendance_report_all_status_value( $date, $class_id, $mjschool_user->ID ) ); ?>
											</span>
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
					<div class="mjschool-print-button pull-left">
						<button data-toggle="tooltip" title="<?php esc_attr_e( 'Download CSV', 'mjschool' ); ?>" name="monthly_attendance_csv_download" class="mjschool-attr-download-csv-btn mjschool-custom-padding-0"><?php esc_html_e( 'Download Report in CSV', 'mjschool' ); ?></button>
					</div>
				</form>
			</div>
			<?php
		} else {
			 ?>
            <div class="mjschool-calendar-event-new">
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
            </div>
            <?php  
		}
		?>
	</div>
</div>