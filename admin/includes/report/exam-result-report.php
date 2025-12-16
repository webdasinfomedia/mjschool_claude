<?php

/**
 * Examination Report Page Template.
 *
 * Renders the examination result filter form and displays a detailed exam result
 * report including subjects, total marks, and pass/fail status for each student.
 *
 * Handles:
 * - Nonce validation for secure tab access.
 * - Class, section, exam, and student status filters.
 * - Dynamic subject and student retrieval.
 * - DataTables initialization for sortable/exportable reports.
 *
 * This file is part of the MJ School Management plugin.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;
$school_type = get_option( 'mjschool_custom_class' );

// Check nonce for examination report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_examination_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<!-- Panel body div.  -->
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<form method="post" id="fee_payment_report"> <!-- Form Start.  -->
		<div class="form-body mjschool-user-form">
			<div class="row">
				<?php if ( $school_type === 'university' ) {?>
						<div class="col-md-6 mb-3 input">
				<?php }else{?>
					<div class="col-md-3 mb-3 input">
				<?php }?>
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
					<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
						<?php
						$class_id = '';
						if ( isset( $_REQUEST['class_id'] ) ) {
							$class_id = $_REQUEST['class_id'];
						}
						?>
						<option value=""><?php esc_html_e( 'Select Class Name', 'mjschool' ); ?></option>
						<?php
						foreach ( mjschool_get_all_class() as $classdata ) {
							?>
							<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?>><?php echo esc_html( $classdata['class_name'] ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
				<?php if ( $school_type === 'school' ) {?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Section', 'mjschool' ); ?></label>
						<?php
						$class_section = '';
						if ( isset( $_REQUEST['class_section'] ) ) {
							$class_section = $_REQUEST['class_section'];
						}
						?>
						<select name="class_section" class="mjschool-line-height-30px mjschool-section-id-exam form-control" id="class_section">
							<option value=""><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
							<?php
							if ( isset( $_REQUEST['class_section'] ) ) {
								echo esc_html( $class_section = $_REQUEST['class_section'] );
								foreach ( mjschool_get_class_sections( $_REQUEST['class_id'] ) as $sectiondata ) {
									?>
									<option value="<?php echo esc_attr( $sectiondata->id ); ?>" <?php selected( $class_section, $sectiondata->id ); ?>><?php echo esc_html( $sectiondata->section_name ); ?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
				<?php } ?>
				<div class="col-md-3 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-exam-id"> <?php esc_html_e( 'Select Exam', 'mjschool' ); ?><span class="mjschool-require-field">*</span> </label>
					<?php
					global $wpdb;
					$exam_id  = '';
					if ( isset( $_REQUEST['exam_id'] ) ) {
						$exam_id = sanitize_text_field( $_REQUEST['exam_id'] );
					}
					$class_id = '';
					if ( isset( $_REQUEST['class_id'] ) ) {
						$class_id = sanitize_text_field( $_REQUEST['class_id'] );
					}
					// Fetch only exams of that class.
					$exams = array();
					if ( ! empty( $class_id ) ) {
						// phpcs:disable
						$exams = $wpdb->get_results(
							$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mjschool_exam WHERE class_id = %d", $class_id )
						);
						// phpcs:enable
					}
					?>
					<select id="mjschool-exam-id" name="exam_id" class="mjschool-line-height-30px form-control exam_list validate[required]">
						<option value=""><?php esc_html_e( 'Select Exam Name', 'mjschool' ); ?></option>
						<?php
						if ( ! empty( $exams ) ) {
							foreach ( $exams as $exam ) {
								?>
								<option value="<?php echo esc_attr( $exam->exam_id ); ?>" <?php selected( $exam->exam_id, $exam_id ); ?>> <?php echo esc_html( $exam->exam_name ); ?> </option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-md-3 mb-3 input">
					<label class="ml-1 mjschool-custom-top-label top" for="mjschool-status"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></label>
					<select id="mjschool-status" name="student_status" class="mjschool-line-height-30px form-control">
						<?php
						$status=null;
						if ( isset( $_REQUEST['student_status'] ) ) {
							$status = $_REQUEST['student_status'];
						}
						?>
						<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'mjschool' ); ?></option>
						<option value="deactive" <?php selected( $status, 'deactive' ); ?>><?php esc_html_e( 'Deactive', 'mjschool' ); ?></option>
					</select>
				</div>
			</div>
		</div>
		<div class="form-body mjschool-user-form">
			<div class="row">
				<div class="col-md-3">
					<input type="submit" name="report_5" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
				</div>
			</div>
		</div>
	</form> <!-- Form end.  -->
</div>
<div class="clearfix"> </div>
<!-- Panel body div start.  -->
<div class="clearfix mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	if ( isset( $_POST['report_5'] ) ) {
		$exam_id        = $_REQUEST['exam_id'];
		$class_id       = $_REQUEST['class_id'];
		$student_status = $_REQUEST['student_status'];
		if ( isset( $_REQUEST['class_section'] ) && $_REQUEST['class_section'] != '' ) {
			$subject_list = $obj_marks->mjschool_student_subject( intval( $_REQUEST['class_id'] ), $_REQUEST['class_section'] );
			
			if ($student_status === "active") {
				$exlude_id = mjschool_approve_student_list();
				$student = get_users(array(
					'meta_key' => 'class_section',
					'meta_value' => $_REQUEST['class_section'],
					'meta_query' => array(array( 'key' => 'class_name', 'value' => intval($_REQUEST['class_id']), 'compare' => '=' ) ),
					'role' => 'student',
					'exclude' => $exlude_id
				 ) );
			} else {
				$student = get_users(array(
					'role' => 'student',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'class_name',
							'value'   => intval($_REQUEST['class_id']),
							'compare' => '='
						),
						array(
							'key'     => 'class_section',
							'value'   => $_REQUEST['class_section'],
							'compare' => '='
						),
						array(
							'key'     => 'hash',
							'compare' => 'EXISTS'
						)
					)
				 ) );
			}
			
		} else {
			$subject_list = $obj_marks->mjschool_student_subject( intval( $_REQUEST['class_id'] ) );
			
			if ($student_status === "active") {
				$exlude_id = mjschool_approve_student_list();
				$student = get_users(array( 'meta_key' => 'class_name', 'meta_value' => intval($_REQUEST['class_id']), 'role' => 'student', 'exclude' => $exlude_id ) );
			} else {
				$student = get_users(array(
					'role' => 'student',
					'meta_query' => array(
						'relation' => 'AND',
						array(
							'key'     => 'class_name',
							'value'   => intval($_REQUEST['class_id']),
							'compare' => '='
						),
						array(
							'key'     => 'hash',
							'compare' => 'EXISTS'
						)
					)
				 ) );
			}
			
		}
		if ( ! empty( $student ) ) {
			?>
			<script type="text/javascript">
				(function(jQuery) {
					"use strict";
					jQuery(function() {
						var table = jQuery( '#advance-report-custom' ).DataTable({
							order: [[1, "desc"]],
							dom: 'lifrtp',
							buttons: [
								{
									extend: 'csv',
									text: '<?php esc_html_e( "csv", "mjschool" ); ?>',
									title: '<?php esc_html_e( "Exam Result Report", "mjschool" ); ?>'
								},
								{
									extend: 'print',
									text: '<?php esc_html_e( "Print", "mjschool" ); ?>',
									title: '<?php esc_html_e( "Exam Result Report", "mjschool" ); ?>'
								}
							],
							aoColumns: [
								{ bSortable: true },
								{ bSortable: true },
								<?php
								if ( ! empty( $subject_list ) ) {
									foreach ( $subject_list as $sub_id ) {
										echo '{ bSortable: true },';
									}
								}
								?>
								{ bSortable: true },
								{ bSortable: true }
							],
							language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
						});
						// Add placeholder to search input.
						jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>" );
						// Place export buttons.
						jQuery( '.btn-place' ).html(table.buttons().container( ) );
					});
				})(jQuery);
			</script>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<table id="advance-report-custom" class="display" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
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
							<th><?php esc_html_e( 'Result Status', 'mjschool' ); ?></th>
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
									<td><?php echo esc_html( $mjschool_user->roll_id ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( mjschool_get_user_name_by_id( $mjschool_user->ID ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
									<?php
									if ( ! empty( $subject_list ) ) {
										$result  = array();
										$result1 = array();
										foreach ( $subject_list as $sub_id ) {
											$mark_detail = $obj_marks->mjschool_subject_makrs_detail_byuser( $exam_id, $class_id, $sub_id->subid, $mjschool_user->ID );
											$marks_from  = $obj_marks->mjschool_get_pass_marks( $exam_id );
											if ( $mark_detail ) {
												$mark_id     = $mark_detail->mark_id;
												$marks_total = ( $mark_detail->contributions === 'yes' ) ? array_sum( json_decode( $mark_detail->class_marks, true ) ?? array() ) : (int) $mark_detail->marks;
												$marks       = $marks_total;
												$total      += $marks;
											} else {
												$marks         = 0;
												$attendance    = 0;
												$marks_comment = '';
												$total        += 0;
												$mark_id       = '0';
											}
											if ( $marks >= $marks_from ) {
												$result[] = 'pass';
											} else {
												$result1[] = 'fail';
											}
											if ( $marks >= $marks_from ) {
												$result[] = 'pass';
											} else {
												$result1[] = 'fail';
											}
											?>
											<td><?php echo esc_html( $marks ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_html( $sub_id->sub_name ); ?> <?php esc_html_e( 'Mark', 'mjschool' ); ?>"></i></td>
											<?php
										}
										?>
										<td><?php echo esc_html( $total ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Marks', 'mjschool' ); ?>"></i></td>
										<td>
											<?php
											if ( isset( $result ) && in_array( 'pass', $result ) && isset( $result1 ) && in_array( 'fail', $result1 ) ) {
												esc_html_e( 'Fail', 'mjschool' );
											} elseif ( isset( $result ) && in_array( 'pass', $result ) ) {
												esc_html_e( 'Pass', 'mjschool' );
											} elseif ( isset( $result1 ) && in_array( 'fail', $result1 ) ) {
												esc_html_e( 'Fail', 'mjschool' );
											} else {
												echo '-';
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Result Status', 'mjschool' ); ?>"></i>
										</td>
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
			<?php
		} else {
			?>
			<div class="mjschool-calendar-event-new">
				<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
			</div>
			<?php
		}
		?>
		<!-- End panel body div. -->
		<?php
	}
	?>
</div>