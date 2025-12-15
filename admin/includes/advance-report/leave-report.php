<?php
/**
 * The admin view for the Leave Report (Advanced Report section).
 *
 * This file displays the detailed leave records of students, including
 * leave type, duration, date range, status, and reason. It integrates
 * with DataTables to support advanced filtering, search builder,
 * CSV/Print export, and multilingual support.
 *
 * It uses dynamic JavaScript filters based on class names to simplify
 * analysis and applies WordPress data sanitization for secure rendering.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance leave report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_attendance_report_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	$mjschool_obj_leave = new Mjschool_Leave();
	$leave_data         = mjschool_get_leave_data_advance_report();
	$class_name         = mjschool_get_all_class_array();
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $leave_data ) ) {
			?>
			<div class="table-responsive"><!-- table-responsive. -->
				<div class="btn-place"></div>
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="leave_list_advance_report" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Reason', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							foreach ( $leave_data as $retrieved_data ) {
								$leave_id = mjschool_encrypt_id( $retrieved_data->id );
								?>
								<tr>
									<td class="mjschool-user-image mjschool-width-50px-td">
										<a href="?smgt_student&tab=view_student&action=view_student&student_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->student_id ) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>">
											<?php
											$umetadata = mjschool_get_user_image( $retrieved_data->student_id );
											if ( empty( $umetadata ) ) {
                                                
                                                echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
											} else {
                                                echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
                                                
											}
											?>
										</a>
									</td>
									<td>
										<?php
										$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
										if ( $sname !== '' ) {
											echo esc_html( $sname );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
									</td>
									<td class="name">
										<?php
										$section_id = 0;
										$class_id   = get_user_meta( $retrieved_data->student_id, 'class_name', true );
										$classname = mjschool_get_class_section_name_wise( $class_id, $section_id );
										if ( ! empty( $classname ) ) {
											echo esc_html( $classname );
										} else {
											esc_html_e( 'N/A', 'mjschool' );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
									</td>
									<td><?php echo esc_html( get_the_title( $retrieved_data->leave_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Type', 'mjschool' ); ?>"></i></td>
									<td>
										<?php $duration = mjschool_leave_duration_label( $retrieved_data->leave_duration ); echo esc_html( $duration ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Duration', 'mjschool' ); ?>"></i>
									</td>
									<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Start Date', 'mjschool' ); ?>"></i></td>
									<td>
										<?php if ( ! empty( $retrieved_data->end_date ) ) { echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave End Date', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php
										$status = $retrieved_data->status;
										if ( $status === 'Approved' ) {
											echo "<span class='mjschool-green-color'> " . esc_html( $status ) . ' </span>';
										} else {
											echo "<span class='mjschool-red-color'> " . esc_html( $status ) . ' </span>';
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->status_comment ) ) { echo esc_html( $retrieved_data->status_comment ); } else { esc_html_e( 'Status', 'mjschool' ); } ?>"></i>
									</td>
									<td>
										<?php
										$comment = $retrieved_data->reason;
										$reason  = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
										echo esc_html( $reason );
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_html_e( 'Reason', 'mjschool' ); } ?>"></i>
									</td>
								</tr>
								<?php
								++$i;
							}
							?>
						</tbody>
					</table>
				</form>
			</div><!--------- Table Responsive. ------->
			<?php
		}
		?>
	</div>
</div>