<?php
/**
 * The admin interface for displaying the advanced attendance report.
 *
 * This file generates the Attendance Advance Report table in the admin area.
 * It lists students along with their class, section, attendance percentage,
 * and related details. The report also supports DataTables features such as
 * filtering, searching, and exporting to CSV or print format.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance attendance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	$attendance      = mjschool_attedance_advance_report();
	$class_name      = mjschool_get_all_class_array();
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $attendance ) ) {
			?>
			<div class="table-responsive">
				<div class="btn-place"></div>
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="advance_attendance_report" class="display" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Working Days', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Present', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Attendance %', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $attendance ) ) {
								$i = 0;
								foreach ( $attendance as $attendance_data ) {
									$class_name   = 'N/A';
									$section_name = 'N/A';
									if ( ! empty( $attendance_data->class_id ) ) {
										$class_name = mjschool_get_class_name_by_id( $attendance_data->class_id );
									}
									if ( ! empty( $attendance_data->section_id ) ) {
										$section_name = mjschool_get_section_name( $attendance_data->section_id );
									}
									?>
									<td class="mjschool-user-image mjschool-width-50px-td">
										<a href="?smgt_student&tab=view_student&action=view_student&student_id=<?php echo esc_attr( mjschool_encrypt_id( $attendance_data->user_id ) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>">
											<?php
											$umetadata = mjschool_get_user_image( $attendance_data->user_id );
                                            
                                            if (empty($umetadata ) ) {
                                                echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
                                            } else {
                                                echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
                                            }
                                            
											?>
										</a>
									</td>
									<td><?php echo esc_html( mjschool_student_display_name_with_roll( $attendance_data->user_id ) ); ?><i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( $class_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( $section_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( $attendance_data->total_working_days ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Working Days', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( $attendance_data->total_present ); ?> <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Present', 'mjschool' ); ?>"></i></td>
									<td><?php echo esc_html( $attendance_data->attendance_percentage ); ?> % <i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance %', 'mjschool' ); ?>"></i></td>
									<?php
									echo '</tr>';
									++$i;
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
                
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
                
			</div>
			<?php
		}
		?>
	</div>
</div>