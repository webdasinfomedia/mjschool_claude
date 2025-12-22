<?php
/**
 * The admin view for the Teacher Performance Report (Advanced Report section).
 *
 * This file generates a detailed report of teacher performance, including class name,
 * subject, and performance metrics such as highest, lowest, and average marks.
 * It leverages the DataTables library with SearchBuilder to filter and visualize
 * high-performing classes (average marks > 80) across all classes.
 *
 * The table provides CSV export and print options, integrates multilingual
 * DataTable support, and ensures secure WordPress-based data rendering using
 * proper sanitization and escaping.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance teacher perfomance report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	$mjschool_obj_leave     = new Mjschool_Leave();
	$perfomance_report_data = mjschool_get_teacher_perfomance_report();
	$class_name             = mjschool_get_all_class_array();
	$class_name_list        = array_map(
		function ( $s ) {
			return trim( $s->class_name ); // Trim each class name.
		},
		$class_name
	);
	?>
	
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $perfomance_report_data ) ) {
			?>
			<div class="table-responsive"><!-- table-responsive. -->
				<div class="btn-place"></div>
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="teacher_advance_report" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Highest Mark', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Lowest Mark', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Average Mark', 'mjschool' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $perfomance_report_data as $retrieved_data ) {
								?>
								<tr>
									<td class="mjschool-user-image mjschool-width-50px-td">
										<a href="?smgt_teacher&tab=view_teacher&action=view_teacher&teacher_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data['teacher_id'] ) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>">
											<?php
											$uid       = $retrieved_data['teacher_id'];
											$umetadata = mjschool_get_user_image( $uid );
											if ( empty( $umetadata ) ) {
                                                
                                                echo '<img src=' . esc_url( get_option( 'mjschool_teacher_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
											} else {
                                                echo '<img src=' . esc_url($umetadata . ' height="50px" width="50px" class="img-circle"/>' );
                                                
											}
											?>
										</a>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['teacher_name'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['class_name'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['subject_name'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['highest_mark'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Highest Mark', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['lowest_mark'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Lowest Mark', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( $retrieved_data['average_mark'] ); ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Average Mark', 'mjschool' ); ?>"></i>
									</td>
								</tr>
								<?php
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