<?php

/**
 * Class & Section Report Page Template.
 *
 * Displays a detailed report of all classes, their sections, and the
 * total number of students mapped to each class/section. Also includes
 * DataTables integration with CSV/Print export options and multi-language
 * support.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for class section report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$tablename      = 'mjschool_class';
$retrieve_class_data = mjschool_get_all_data( $tablename );
?>
<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	if ( ! empty( $retrieve_class_data ) ) {
		?>
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
				<h4 class="mjschool-report-header"><?php esc_html_e( 'Class & Section Report', 'mjschool' ); ?></h4>
			</div>
		</div>
		<div class="table-responsive">
			<div class="btn-place"></div>
			<form id="frm_student_report" name="frm_student_report" method="post">
				<table id="class_section_report" class="display class_section_report_tbl" cellspacing="0" width="100%">
					<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
						<tr>
							<th><?php esc_html_e( 'Sr. No.', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
							<th><?php esc_html_e( 'Students', 'mjschool' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$i = 1;
						foreach ( $retrieve_class_data as $retrieved_data ) {
							$section_list = mjschool_get_class_sections( $retrieved_data->class_id );
							$class_name   = $retrieved_data->class_name;
							$class_id     = $retrieved_data->class_id;
							?>
							<tr>
								<td><?php echo esc_attr( $i ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Sr. No.', 'mjschool' ); ?>"></i></td>
								<td><?php echo esc_html( $retrieved_data->class_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i> </td>
								<td>
									<?php
									$exlude_id = mjschool_approve_student_list();
									
									$studentdata = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $retrieved_data->class_id, 'role' => 'student','exclude'=>$exlude_id ) );
									
									echo count( $studentdata );
									?>
									<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student', 'mjschool' ); ?>"></i>
								</td>
							</tr>
							<?php
							++$i;
							// Check if there are sections and print the count.
							if ( ! empty( $section_list ) ) {
								foreach ( $section_list as $section_id ) {
									?>
									<tr>
										<td><?php echo esc_attr( $i ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Sr. No.', 'mjschool' ); ?>"></i></td>
										<td>
											<?php echo esc_html( $retrieved_data->class_name ); ?> -> <?php echo esc_html( $section_id->section_name ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
										</td>
										<td>
											<?php 
											$studentdata = get_users(array( 'meta_key' => 'class_section', 'meta_value' =>$section_id->id, 'meta_query'=> array(array( 'key' => 'class_name','value' => $class_id,'compare' => '=' ) ),'role'=>'student','exclude'=>$exlude_id ) );
											
											echo count( $studentdata );
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student', 'mjschool' ); ?>"></i>
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
            <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
		</div>	
		<?php
	}
	?>
</div>