<?php
/**
 * View Homework Submissions List
 *
 * This file displays a table listing all submitted homework by students
 * for a specific assignment, primarily used by the teacher role. It provides
 * actions to view the submission details and download the submitted file.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="mjschool-panel-body"><!-- Mjschool-panel-body.--> 	
	<div class="table-responsive"><!-- Table-responsive. --> 	
		<form id="mjschool-common-form" name="mjschool-common-form" method="post">
			<table id="submission_list" class="display" cellspacing="0" width="100%">
				<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
					<tr>
						<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
						<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
						<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 0;
					foreach ( $retrieve_class_data as $retrieved_data ) {
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
						?>
						<tr>
							<td class="mjschool-padding-left-0 mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">	
								<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png")?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
								</p>
							</td>
							<td><?php echo esc_html( $retrieved_data->title ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i></td>
							<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i></td>
							<td>
								<a  href="<?php echo esc_url( '?page=mjschool_student&tab=view_student&action=view_student&student_id=' . $retrieved_data->student_id ); ?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
							</td>
							<td><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i></td>
							<?php
							if ( $retrieved_data->status === 1 ) {
								if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
									?>
									<td><label class="mjschool-green-color"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
									<?php
								} else {
									?>
									<td><label class="mjschool-green-color"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
									<?php
								}
							} else {
								?>
								<td><label class="color-red"><?php esc_html_e( 'Pending', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
								<?php
							}
							?>
							<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i></td>
							<?php
							if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
								?>
								<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo 'N/A '; ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
								<?php
							} else {
								?>
								<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
								<?php
							}
							?>
							<td class="action">
								<div class="mjschool-user-dropdown">
									<ul  class="mjschool_ul_style">
										<li >
											<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL."/assets/images/listpage-icon/mjschool-more.png")?>">
											</a>
											<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
												<?php
												if ( $retrieved_data->status === 1 && $school_obj->role === 'teacher' ) {
													?>
													<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
														<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=homework&tab=view_student_homework&stud_homework_id=' . $retrieved_data->stu_homework_id ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'Submitted Homework', 'mjschool' ); ?></a>
													</li>
													<?php
												}
												if ( $retrieved_data->status === 1 ) {
													?>
													<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
														<a download href="<?php print esc_url( content_url( '/uploads/homework_file/' . $retrieved_data->file) ); ?>" class="mjschool-float-left-width-100px" record_id="<?php echo esc_attr( $retrieved_data->stu_homework_id ); ?>"><i class="fas fa-eye"> </i><?php esc_html_e( ' Uploaded Homework', 'mjschool' ); ?></a></td>
													</li>
													<?php
												}
												?>
											</ul>
										</li>
									</ul>
								</div>
							</td>
						</tr>
						<?php
						++$i;
					}
					?>
				</tbody>
			</table>
		</form>
	</div><!-- Table-responsive. --> 
</div><!-- Mjschool-panel-body.-->