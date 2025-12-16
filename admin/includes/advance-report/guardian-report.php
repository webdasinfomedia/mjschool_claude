<?php
/**
 * The admin interface for displaying the Advanced Student (Guardian) Report.
 *
 * This file renders the Student Report within the Advanced Reports module.
 * It lists student information such as class, admission number, guardian relation,
 * and parent contact details using dynamic DataTables with search, filter, and export (CSV/Print) features.
 * The report helps administrators quickly review student and guardian records in a structured format.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-panel-body clearfix mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	// -------------- STUDENT REPORT -DATA. ---------------//
	$studentdata = get_users( array( 'role' => 'student' ) );
	?>
	<!-- <script type="text/javascript">
		(function(jQuery) {
			"use strict";
			jQuery(document).ready(function() {
				// var table = jQuery( '#student_report' ).DataTable({
				// 	"order": [[1, "Desc"]],
				// 	"dom": 'Qlfrtip',
				// 	buttons: [
				// 		{
				// 			extend: 'csv',
				// 			text: '<?php esc_html_e( 'csv', 'mjschool' ); ?>',
				// 			title: '<?php esc_html_e( 'Guardian Report', 'mjschool' ); ?>',
				// 		},
				// 		{
				// 			extend: 'print',
				// 			text: '<?php esc_html_e( 'Print', 'mjschool' ); ?>',
				// 			title: '<?php esc_html_e( 'Guardian Report', 'mjschool' ); ?>',
				// 		}
				// 	],
				// 	"aoColumns": [
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true },
				// 		{ "bSortable": true }
				// 	],
				// 	language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>
				// });
				jQuery( '.dataTables_filter input' ).attr( "placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>");
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			});
		})(jQuery);
	</script> -->
	<div class="mjschool-panel-body mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $studentdata ) ) {
			?>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<form id="frm_student_report" name="frm_student_report" method="post">
					<table id="guardian_report" class="display mjschool-student-report-tbl" cellspacing="0" width="100%">
						<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
						<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
						<input type="hidden" name="gender" value="<?php echo esc_attr( $gender ); ?>" />
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Admission No', 'mjschool' ); ?>.</th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Guardian Relation', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Father Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Father Phone', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mother Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mother Phone', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $studentdata as $retrieved_data ) {
								$student_data = get_userdata( $retrieved_data->ID );
								$parent_id    = get_user_meta( $retrieved_data->ID, 'parent_id', true );
								?>
								<tr>
									<td>
										<?php
										$class_name = mjschool_get_class_section_name_wise( $student_data->class_name, $student_data->class_section );
										echo esc_html( $class_name );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) { echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ); } ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Number', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_student_display_name_with_roll( $student_data->ID ) ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php if ( ! empty( $student_data->mobile_number ) ) { echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number ); } ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											$relation_name = array();
											foreach ( $parent_id as $parents_data ) {
												$relation        = get_user_meta( $parents_data, 'relation', true );
												$relation_name[] = get_user_meta( $parents_data, 'relation', true );
											}
											if ( ! empty( $relation_name ) ) {
												echo esc_html( implode( ' / ', $relation_name ) );
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Guardian Relation', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											foreach ( $parent_id as $parents_data ) {
												$relation = get_user_meta( $parents_data, 'relation', true );
												if ( $relation === 'Father' ) {
													$parents = get_userdata( $parents_data );
													echo esc_html( $parents->first_name ) . ' ' . esc_html( $parents->last_name ) . '<br>';
												}
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Father Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											foreach ( $parent_id as $parents_data ) {
												$relation = get_user_meta( $parents_data, 'relation', true );
												if ( $relation === 'Father' ) {
													$parents = get_userdata( $parents_data );
													if ( ! empty( get_user_meta( $parents_data, 'mobile_number', true ) ) ) {
														echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $parents_data, 'mobile_number', true ) );
													}
												}
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Father Phone', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											foreach ( $parent_id as $parents_data ) {
												$relation = get_user_meta( $parents_data, 'relation', true );
												if ( $relation === 'Mother' ) {
													$parents = get_userdata( $parents_data );
													echo esc_html( $parents->first_name ) . ' ' . esc_html( $parents->last_name ) . '<br>';
												}
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mother Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											foreach ( $parent_id as $parents_data ) {
												$relation = get_user_meta( $parents_data, 'relation', true );
												if ( $relation === 'Mother' ) {
													$parents = get_userdata( $parents_data );
													if ( ! empty( get_user_meta( $parents_data, 'mobile_number', true ) ) ) {
														echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $parents_data, 'mobile_number', true ) );
													}
												}
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mother Phone', 'mjschool' ); ?>"></i>
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
                
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
                
			</div>
			<?php
		}
		?>
	</div>
</div>