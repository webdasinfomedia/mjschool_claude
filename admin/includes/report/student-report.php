<?php

/**
 * Student Attendance Report – Filter Form, Processing, and Report Table.
 *
 * This file renders the student attendance report interface, including:
 * - Class, section, and status filter form
 * - Secure nonce validation for student information tab
 * - Dynamic retrieval of active/deactive students
 * - DataTables initialization for the student report table
 * - Display of class, student, parent, DOB, gender, and mobile details
 *
 * Loaded within the Admin Reports section (mjschool_report)
 * under the Student Attendance Report tab.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/report
 * @since      1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Check nonce for student report tab.
if ( isset( $_GET['tab'] ) ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'mjschool_student_infomation_tab' ) ) {
		wp_die( esc_html__( 'Security check failed. Please reload the page.', 'mjschool' ) );
	}
}

$school_type = get_option( 'mjschool_custom_class' );
?>
<div class="mjschool-panel-body clearfix mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
	<div class="mjschool-panel-body clearfix">
		<form method="post" id="student_attendance">  
			<?php
			$class_id      = '';
			$class_section = '';
			$gender        = '';
			?>
			<input type="hidden" name="class_id" value="<?php echo esc_attr( $class_id ); ?>" />
			<input type="hidden" name="class_section" value="<?php echo esc_attr( $class_section ); ?>" />
			<input type="hidden" name="gender" value="<?php echo esc_attr( $gender ); ?>" />
			<div class="form-body mjschool-user-form">
				<div class="row">
					<?php if ( $school_type === 'school' ) {?>
						<div class="col-md-3 mb-3 input">
					<?php }elseif ( $school_type === 'university' ){?>
						<div class="col-md-6 mb-3 input">
					<?php }?>
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-class-list"><?php esc_html_e( 'Select Class', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>			
						<select name="class_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control validate[required]">
							<?php
							if ( isset( $_REQUEST['class_id'] ) ) {
								$class_id = $_REQUEST['class_id'];
							} else {
								$class_id = '';
							} ?>
							<option value=""><?php esc_html_e( 'Select class Name', 'mjschool' ); ?></option>
							<?php
							foreach ( mjschool_get_all_class() as $classdata ) {
								?>
								<option  value="<?php echo esc_attr( $classdata['class_id'] ); ?>" <?php selected( $classdata['class_id'], $class_id ); ?> ><?php echo esc_html( $classdata['class_name'] ); ?></option>
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
						<label class="ml-1 mjschool-custom-top-label top" for="mjschool-status"><?php esc_html_e( 'Student Status', 'mjschool' ); ?></label>
						<?php
						$status = ''; // Default to empty.
						if ( isset( $_REQUEST['status'] ) ) {
							$status = sanitize_text_field( $_REQUEST['status'] );
						}
						?>
						<select id="mjschool-status" name="status" class="mjschool-line-height-30px form-control">
							<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'mjschool' ); ?></option>
							<option value="deactive" <?php selected( $status, 'deactive' ); ?>><?php esc_html_e( 'Deactive', 'mjschool' ); ?></option>
						</select>     
					</div>
					<div class="col-md-3 mb-2">
						<input type="submit" name="student_report" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
					</div>
				</div>
			</div>
		</form> 
	</div>	
	<?php
	// -------------- STUDENT REPORT -DATA. ---------------//
	if ( isset( $_REQUEST['student_report'] ) ) {
		$class_id      = sanitize_text_field( $_POST['class_id'] );
		$class_section = sanitize_text_field( $_POST['class_section'] );
		$status        = sanitize_text_field( $_POST['status'] );
		if ( $class_section === '' ) {
            
            if ( $status ==="active")
            {
                $exlude_id = mjschool_approve_student_list();
                $studentdata = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id,'role'=>'student','exclude'=>$exlude_id ) );
            }
            else
            {
                $studentdata = get_users(array(
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
            }
		} else {
            if ( $status ==="active")
            {
                $exlude_id = mjschool_approve_student_list();
                $studentdata = 	get_users(array( 'meta_key' => 'class_section', 'meta_value' =>$class_section,'meta_query'=> array(array( 'key' => 'class_name','value' => $class_id ) ),'role'=>'student','exclude'=>$exlude_id ) );
            }
            else
            {
                $studentdata = get_users(array(
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
            }
            
		}
	} else {
		$exlude_id   = mjschool_approve_student_list();
		$studentdata = get_users(
			array(
				'role'    => 'student',
				'exclude' => $exlude_id,
			)
		);
		sort( $studentdata );
	}
	?>
	<div class="mjschool-panel-body mjschool-margin-top-20px mjschool-rtl-margin-0px mjschool-padding-top-15px-res">
		<?php
		if ( ! empty( $studentdata ) ) {
			?>
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12 col-xs-12">
					<h4 class="mjschool-report-header"><?php esc_html_e( 'Student Report', 'mjschool' ); ?></h4>
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
								<th><?php esc_html_e( 'Student Id', 'mjschool' ); ?>.</th>
								<th><?php esc_html_e( 'Student Name & Roll No.', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Parent Name', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
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
										<?php
										if ( get_user_meta( $retrieved_data->ID, 'admission_no', true ) ) {
											echo esc_html( get_user_meta( $retrieved_data->ID, 'admission_no', true ) );
										}
										?>
										<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Admission Number', 'mjschool' ); ?>"></i>
									</td>
									<td>  
										<?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->ID ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name & Roll No', 'mjschool' ); ?>"></i>
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
										<?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
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
										<?php echo esc_html( $student_data->mobile_number ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
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
                <img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG)?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
            </div>	
            <?php 
		}
		?>
	</div>
</div>	