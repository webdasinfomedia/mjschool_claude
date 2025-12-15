<?php
/**
 * The admin report page for displaying student admission data.
 *
 * This file generates the Admission Report table in the admin area,
 * showing a list of students with their details such as admission number,
 * name, email, gender, mobile number, and status. It also initializes
 * a searchable and exportable DataTable with predefined filters for
 * rejected students in the current year.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/advance-report
 */
defined( 'ABSPATH' ) || exit;

// Check nonce for advance admission report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_advance_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

?>
<div class="mjschool-panel-body clearfix  mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<?php
	// -------------- ADMISSION REPORT - DATA. ---------------//
	$admission = mjschool_admission_student_list();
	?>
	<div class="mjschool-panel-body  mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $admission ) ) {
			?>
			<div class="admission-report my-3">
				<div class="badge-container d-inline-flex flex-wrap align-items-center">
					<span class="report-label"><?php esc_html_e( 'Students', 'mjschool' ); ?></span>
					<span class="status-text"><?php esc_html_e( 'Rejected', 'mjschool' ); ?></span>
					<span class="report-label"><?php esc_html_e( 'in', 'mjschool' ); ?></span>
					<span class="year-chip" id="year-chip"><?php echo esc_html( date( 'Y' ) ); ?></span>
				</div>
			</div>
			<div class="table-responsive">
				<div  class="btn-place"></div>
				<form id="mjschool-form-admisssion" name="mjschool-form-admisssion" method="post">
					<table id="mjschool-admission-list-report" class="display mjschool-admission-report-tbl" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Admission No', 'mjschool' ); ?>.</th>
								<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Email Id', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
								<!-- <th><?php //esc_html_e( 'Date of Status', 'mjschool' ); ?></th> -->
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ( $admission as $retrieved_data ) {
								$student_data = get_userdata( $retrieved_data->ID );
								?>
								<tr>
									<td>
										<?php
										if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) {
											echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Number', 'mjschool' ); ?>"></i>
									</td>
									<td>  
										<?php echo esc_html( $student_data->display_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
									</td>
									<td>  
										<?php echo esc_html( $retrieved_data->user_email ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Email ID', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->admission_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Date', 'mjschool' ); ?>"></i> 
									</td>
									<td>
										<?php if ( $student_data->gender === 'male' ) { echo esc_attr__( 'Male', 'mjschool' ); } elseif ( $student_data->gender === 'female' ) { echo esc_attr__( 'Female', 'mjschool' ); } ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
									</td>
									<td>
										<?php if ( ! empty( $student_data->mobile_number ) ) { echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number );} ?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
									</td>
									<td> 
										<?php 
											$status = trim( $student_data->status ?? '' );
											if (empty($status ) ) {
												echo 'Not Approved';
											} elseif ($status !== 'Approved' ) {
												echo 'Approved';
											}
										?>
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
                
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
                
			</div>	
			<?php
		}
		?>
	</div>
</div>