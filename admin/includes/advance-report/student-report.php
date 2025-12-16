<?php
/**
 * The admin view for the Student Left Report (Advanced Report section).
 *
 * This file generates and displays a report of students who have left the institution
 * during the current academic year. It uses the DataTables library with SearchBuilder
 * to dynamically filter students by status (“Left”) and year, and provides CSV export
 * and print functionality for easy record keeping.
 *
 * The table includes student details such as class, roll number, contact info, gender,
 * parent name, and left date. The file applies WordPress data sanitization for secure
 * output and integrates localized language support for DataTables.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance student report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<!-- Buttons extension. -->
<div class="mjschool-panel-body clearfix  mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	$exlude_id   = mjschool_approve_student_list();
	$studentdata = get_users(
		array(
			'role'    => 'student',
			'exclude' => $exlude_id,
		)
	);
	sort( $studentdata );
	?>
	<!-- <script type="text/javascript">
		(function(jQuery) {
			"use strict";
			jQuery(document).ready(function() {
				var currentYear = new Date().getFullYear().toString(); // e.g., "2025".
				var table = jQuery( '#student_report' ).DataTable({
					"order": [[1, "desc"]],
					"dom": 'Qlfrtip',
					language: <?php echo wp_json_encode( mjschool_datatable_multi_language() ); ?>,
					searchBuilder: {
						preDefined: {
							criteria: [
								{
									data: 'Status',
									condition: '=',
									value: ['Left']
								},
								{
									data: 'Left Date',
									condition: 'contains',
									value: [currentYear]
								}
							],
							logic: 'AND'
						}
					},
					buttons: [
						{
							extend: 'csv',
							text: '<?php echo esc_attr_e( 'csv', 'mjschool' ); ?>',
							title: '<?php echo esc_attr_e( 'Student Report', 'mjschool' ); ?>',
						},
						{
							extend: 'print',
							text: '<?php echo esc_attr_e( 'Print', 'mjschool' ); ?>',
							title: '<?php echo esc_attr_e( 'Student Report', 'mjschool' ); ?>',
						}
					],
					"aoColumns": [
						{ "bSortable": true }, // Class.
						{ "bSortable": true }, // Roll No.
						{ "bSortable": true }, // Student Name & Email.
						{ "bSortable": true }, // Parent Name.
						{ "bSortable": true }, // Date of Birth.
						{ "bSortable": true }, // Gender.
						{ "bSortable": true }, // Mobile Number.
						{ "bSortable": true }, // Status.
						{ "bSortable": true }  // Left Date.
					]
				});
				jQuery('.dataTables_filter input')
					.attr("placeholder", "<?php esc_html_e( 'Search...', 'mjschool' ); ?>")
					.attr("id", "datatable_search")
					.attr("name", "datatable_search");
				jQuery( '.btn-place' ).html(table.buttons().container( ) );
			});
		})(jQuery);
	</script> -->
	<div class="mjschool-panel-body  mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $studentdata ) ) {
			?>
			<div class="admission-report my-3">
				<div class="badge-container d-inline-flex flex-wrap align-items-center">
					<span class="report-label"><?php esc_html_e( 'Students', 'mjschool' ); ?></span>
					<span class="status-text"><?php esc_html_e( 'Left', 'mjschool' ); ?></span>
					<span class="report-label"><?php esc_html_e( 'in', 'mjschool' ); ?></span>
					<span class="year-chip" id="year-chip"><?php echo esc_attr( date( 'Y' ) ); ?></span>
				</div>
			</div>
			<div class="table-responsive">
				<form id="frm_student_report" name="frm_student_report" method="post">
					<div class="btn-place"></div>
					<table id="student_report" class="display mjschool-student-report-tbl" cellspacing="0" width="100%">
						<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
						<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
						<input type="hidden" name="gender" value="<?php echo esc_attr( $gender ); ?>" />
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?>.</th>
								<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Left Date', 'mjschool' ); ?></th>
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
										<?php if ( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ) { echo esc_html( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ); }?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_attr( $retrieved_data->display_name ); ?><br>
										<span class="mjschool-list-page-email"><?php echo esc_attr( $retrieved_data->user_email ); ?></span>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Email', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $parent_id ) ) {
											$parents_name = array();
											foreach ( $parent_id as $parents_data ) {
												$parents_name[] = mjschool_get_display_name( $parents_data );
											}
											// Get unique parent names.
											$unique_parents_name = array_unique( $parents_name );
											// Get the count of unique names.
											$length = count( $unique_parents_name );
											// Loop through unique names and echo them.
											foreach ( $unique_parents_name as $index => $parent ) {
												echo esc_html( $parent );
												// Add a comma if it's not the last element.
												if ( $index < $length - 1 ) {
													echo ', ';
												}
											}
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Father Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( $student_data->gender === 'male' ) {
											echo esc_attr__( 'Male', 'mjschool' );
										} elseif ( $student_data->gender === 'female' ) {
											echo esc_attr__( 'Female', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										if ( ! empty( $student_data->mobile_number ) ) {
											echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										$hash           = get_user_meta( $retrieved_data->ID, 'hash', true );
										$student_status = get_user_meta( $retrieved_data->ID, 'student_status', true );
										$status_comment = get_user_meta( $retrieved_data->ID, 'status_comment', true );

										if ( ! empty( $hash ) ) {
											// If hash exists, show Active.
											echo '<span class="text-warning font-weight-bold">' . esc_html__( 'Pending', 'mjschool' ) . '</span>';
										} elseif ( ! empty( $student_status ) && $student_status === 'left' ) {
											echo '<span class="text-danger font-weight-bold">' . esc_html__( 'Left', 'mjschool' ) . '</span>';
										} else {
											echo '<span class="text-success">' . esc_html__( 'Approved', 'mjschool' ) . '</span>';
										}
										?>
									</td>
									<td>
										<?php
										$left_date = ! empty( $student_data->left_date ) ? mjschool_get_date_in_input_box( $student_data->left_date ) : esc_html__( 'N/A', 'mjschool' );
										echo esc_html( $left_date );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Left Date', 'mjschool' ); ?>"></i>
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