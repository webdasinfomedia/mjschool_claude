<?php

/**
 * Hostel Report Template
 *
 * Generates the Student Hostel Report, including class filters, section filters,
 * hostel assignment details, DataTables rendering, and optional export options.
 *
 * This template is used inside the MJ School Management plugin to display
 * hostel occupancy, room/bed details, and related student information.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for hostel report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_hostel_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<?php
		$class_id      = '';
		$class_section = '';
		$gender        = '';
		?>
		<form method="post" id="student_attendance">
			<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
			<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
			<input type="hidden" name="id" value="<?php echo esc_attr( $hostel_id ); ?>" />
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php if ( $school_type === 'university' ) {?>
						<div class="col-md-6 mb-3 input">
					<?php }else{?>
						<div class="col-md-3 mb-3 input">
					<?php }?>
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
						<select name="class_id" id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
							<?php
							$class_id = '';
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = $_REQUEST['class_id'];
							}
							?>
							<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
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
						<div class="col-md-3 mb-3 input">
							<label class="ml-1 mjschool-custom-top-label top" for="class_section"><?php esc_html_e( 'Select Class Section', 'mjschool' ); ?></label>
							<?php
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
					<?php } ?>
					<div class="col-md-3 input">
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-hostel-id"><?php esc_html_e( 'Select Hostel', 'mjschool' ); ?></label>
						<?php
						$tablename       = 'mjschool_hostel';
						$retrieve_hostel = mjschool_get_all_data( $tablename );
						$id              = '';
						if ( isset( $_REQUEST['id'] ) ) {
							$id = $_REQUEST['id'];
						}
						?>
						<select id="mjschool-hostel-id" name="id" class="mjschool-line-height-30px form-control">
							<option value=""><?php esc_html_e( 'Select Hostel', 'mjschool' ); ?></option>
							<?php
							foreach ( $retrieve_hostel as $retrieved_data ) {
								?>
								<option value="<?php echo esc_attr( $retrieved_data->id ); ?>" <?php selected( $retrieved_data->id, $id ); ?>><?php echo esc_html( $retrieved_data->hostel_name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="hostel_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
	// -------------- STUDENT REPORT -DATA. ---------------//
	// die();
	$class_id      = '';
	$class_section = '';
	$hostel_id     = '';
	if ( isset( $_REQUEST['hostel_report'] ) ) {
		$class_id      = sanitize_text_field( $_POST['class_id'] );
		$class_section = sanitize_text_field( $_POST['class_section'] );
		$hostel_id     = intval( $_POST['id'] );
	}
	if ( ! empty( $hostel_id ) ) {
		$hostel_data = mjschool_get_assign_beds_by_hostel_id( $hostel_id );
	} else {
		$hostel_data = mjschool_get_all_assign_beds();
	}
	?>
	<script type="text/javascript">
		(function(jQuery) {
			"use strict";
			jQuery(document).ready(function() {
				var table = jQuery( '#student_report' ).DataTable({
					"order": [[1, "Desc"]],
					"dom": 'lifrtp',
					"buttons": [
						{
							extend: 'csv',
							text: '<?php esc_html_e( 'csv', 'mjschool' ); ?>',
							title: '<?php esc_html_e( 'Student Hostel Report', 'mjschool' ); ?>'
						},
						{
							extend: 'print',
							text: '<?php esc_html_e( 'Print', 'mjschool' ); ?>',
							title: '<?php esc_html_e( 'Student Hostel Report', 'mjschool' ); ?>'
						}
					],
					"aoColumns": [
						{"bSortable": true},{"bSortable": true},{"bSortable": true},
						{"bSortable": true},{"bSortable": true},{"bSortable": true},
						{"bSortable": true},{"bSortable": true},{"bSortable": true},{"bSortable": true}
					],
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				});
				jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>");
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			});
		})(jQuery);
	</script>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $hostel_data ) ) {
			?>
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
					<h4 class="mjschool-report-header"><?php esc_html_e( 'Student Hostel Report', 'mjschool' ); ?></h4>
				</div>
			</div>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<form id="frm_student_report" name="frm_student_report" method="post">
					<table id="student_report" class="display mjschool-student-report-tbl" cellspacing="0" width="100%">
						<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
						<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
						<input type="hidden" name="gender" value="<?php echo esc_attr( $gender ); ?>" />
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Admission No', 'mjschool' ); ?>.</th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Parents Phone', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Hostel Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Room / Bed Number', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Room Type', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Occupied Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Charge', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $hostel_data ) ) {
								foreach ( $hostel_data as $retrieved_data ) {
									$student_id            = $retrieved_data->student_id;
									$student_class_id      = get_user_meta( $student_id, 'class_name', true );
									$student_class_section = get_user_meta( $student_id, 'class_section', true );
									if ( ! empty( $class_id ) && ! empty( $class_section ) ) {
										if ( $student_class_id === $class_id && $student_class_section === $class_section ) {
											$student_data = get_userdata( $student_id );
										} else {
											$student_data = '';
										}
									} elseif ( ! empty( $class_id ) ) {
										if ( $student_class_id === $class_id ) {
											$student_data = get_userdata( $student_id );
										} else {
											$student_data = '';
										}
									} else {
										$student_data = get_userdata( $student_id );
									}
									$room          = mjschool_get_room_unique_id_by_room_id( $retrieved_data->room_id );
									$bed_unique_id = $retrieved_data->bed_unique_id;
									$room_bed_name = $room . ' -> ' . $bed_unique_id;
									if ( ! empty( $student_data ) ) {
										?>
										<tr>
											<td>
												<?php
												$class_name = mjschool_get_class_name( $student_data->class_name );
												echo esc_html( $class_name );
												if ( ! empty( $student_data->class_section ) ) {
													echo ' ( ' . esc_html( mjschool_get_section_name( $student_data->class_section ) ) . ' )';
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												$admission = get_user_meta( $student_data->ID, 'admission_no', true );
												if ( ! empty( $admission ) ) {
													echo esc_html( $admission );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission No', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( mjschool_student_display_name_with_roll( $student_data->ID ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( $student_data->mobile_number ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( $student_data->father_mobile ) {
													echo esc_html( $student_data->father_mobile );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Parents Phone', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->hostel_id ) ) {
													echo esc_html( mjschool_get_hostel_name_by_id( $retrieved_data->hostel_id ) ); 
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hostel Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php
												if ( ! empty( $retrieved_data->room_id ) ) {
													echo esc_html( $room_bed_name );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Room Name', 'mjschool' ); ?>"></i>
											</td>
											<td>
												<?php echo esc_html( get_the_title( mjschool_get_room_type_by_room_id( $retrieved_data->room_id ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Room Type', 'mjschool' ); ?>"></i>
											</td>
											<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->assign_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></td>
											<td> 
												<?php
												if ( mjschool_get_bed_charge_by_id( $retrieved_data->bed_id ) ) {
													echo esc_html( mjschool_get_currency_symbol() ) . '' . number_format( mjschool_get_bed_charge_by_id( $retrieved_data->bed_id ), 2, '.', '' ); 
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Bed Charge', 'mjschool' ); ?>"></i>
											</td>
										</tr>
										<?php
									}
								}
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
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
            </div>
            <?php  
		}
		?>
	</div>
</div>