<?php
/**
 * Parent's Dashboard - Child Management Page.
 *
 * This file serves as the main view for parents to manage and view details
 * related to their enrolled children (students) within the Mjschool system.
 * It is primarily responsible for:
 *
 * 1. **Access Control**: Validating the user's role and permissions to view the page.
 * 2. **Child List Display**: Fetching and displaying a list of all children linked to the current parent account.
 * 3. **Detailed Information**: Providing the functionality to view detailed information for each child, including:
 * - Child's profile image, name, and ID.
 * - Academic details (Class, Section, Class Teacher).
 * - Parent/Sibling contact details.
 * - **Student Tabs**: Managing content for various tabs like student profile, exam results (marks),
 * attendance records, fee history, and homework lists.
 * 4. **Parent List**: Displaying the other parent(s) linked to the child's record.
 * 5. **Data Table Integration**: Initializing the jQuery DataTables library for the student list.
 * 6. **UI Enhancements**: Implementing features like date pickers and toggling of "More/Less Details".
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 * 
 */

defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'childlist';
$obj_mark   = new Mjschool_Marks_Manage();
$access     = mjschool_page_access_role_wise_and_accessright();
if ( $access ) {
	?>
	<script type="text/javascript">
		(function(jQuery) {
			"use strict";
			jQuery(document).ready(function() {
				// Student list DataTable.
				jQuery( '#student_list' ).DataTable({
					responsive: true
				});
				// Datepickers.
				jQuery( '.sdate, .edate' ).datepicker({ dateFormat: "yy-mm-dd" });
				// Toggle More / Less Details.
				jQuery( ".view_more_details_div").on( "click", ".view_more_details", function() {
					jQuery( '.view_more_details_div' ).removeClass( "d-block").addClass( "d-none");
					jQuery( '.view_more_details_less_div' ).removeClass( "d-none").addClass( "d-block");
					jQuery( '.mjschool-user-more-details' ).removeClass( "d-none").addClass( "d-block");
				});
				jQuery( ".view_more_details_less_div").on( "click", ".view_more_details_less", function() {
					jQuery( '.view_more_details_div' ).removeClass( "d-none").addClass( "d-block");
					jQuery( '.view_more_details_less_div' ).removeClass( "d-block").addClass( "d-none");
					jQuery( '.mjschool-user-more-details' ).removeClass( "d-block").addClass( "d-none");
				});
				// Parents list DataTable.
				jQuery( '#parents_list' ).DataTable({
					order: [[0, "asc"]],
					aoColumns: [
						{ "bSortable": true },
						{ "bSortable": true },
						{ "bSortable": true },
						{ "bSortable": true },
						{ "bSortable": true }
					]
				});
			});
		})(jQuery);
	</script>
	<!-- POP-UP code. -->
	<div class="mjschool-popup-bg">
		<div class="mjschool-overlay-content">
			<div class="result"></div>
			<div class="view-parent"></div>
			<div class="view-attendance"></div>
		</div> 
	</div>
	<?php
	if ( isset( $_REQUEST['attendance'] ) && intval( $_REQUEST['attendance'] ) === 1 ) {
		?>
		<div class="mjschool-panel-body mjschool-panel-white">
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap" role="tablist">
				<li class="active">
					<a href="#child" role="tab" data-toggle="tab"> <i class="fas fa-align-justify"></i> <?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="mjschool-panel-body">
					<form name="wcwm_report" action="" method="post">
						<input type="hidden" name="attendance" value="1"> 
						<input type="hidden" name="user_id" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ); ?>">       
						<div class="form-group col-md-3">
							<label for="exam_id"><?php esc_html_e( 'Start Date', 'mjschool' ); ?></label>
							<input type="text"  class="form-control sdate" name="sdate" value="<?php if ( isset( $_REQUEST['sdate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
						</div>
						<div class="form-group col-md-3">
							<label for="exam_id"><?php esc_html_e( 'End Date', 'mjschool' ); ?></label>
							<input type="text"  class="form-control edate" name="edate" value="<?php if ( isset( $_REQUEST['edate'] ) ) { echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) ) ); } else { echo esc_attr( date( 'Y-m-d' ) ); } ?>" readonly>
						</div>
						<div class="form-group col-md-3 button-possition">
							<label for="subject_id">&nbsp;</label>
							<input type="submit" name="view_attendance" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info"/>
						</div>	
					</form>
					<div class="clearfix"></div>
					<?php
					if ( isset( $_REQUEST['view_attendance'] ) ) {
						$start_date = sanitize_text_field( wp_unslash( $_REQUEST['sdate'] ) );
						$end_date   = sanitize_text_field( wp_unslash( $_REQUEST['edate'] ) );
						$user_id    = sanitize_text_field( wp_unslash( $_REQUEST['user_id'] ) );
						$attendance = mjschool_view_student_attendance( $start_date, $end_date, $user_id );
						$curremt_date = $start_date;
						?>
						<div class="table-responsive">
							<table class="table col-md-12">
								<tr>
									<th width="200px"><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Day', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Attendance', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
								</tr>
								<?php
								while ( $end_date >= $curremt_date ) {
									echo '<tr>';
									echo '<td>';
									echo esc_html( mjschool_get_date_in_input_box( $curremt_date ) );
									echo '</td>';
									$attendance_status = mjschool_get_attendence( $user_id, $curremt_date );
									echo '<td>';
									echo esc_html( date( 'D', strtotime( $curremt_date ) ) );
									echo '</td>';
									if ( ! empty( $attendance_status ) ) {
										echo '<td>';
										echo esc_html( mjschool_get_attendence( $user_id, $curremt_date ) );
										echo '</td>';
									} else {
										echo '<td>';
										echo esc_html__( 'Absent', 'mjschool' );
										echo '</td>';
									}
									echo '<td>';
									echo esc_html( mjschool_get_attendence_comment( $user_id, $curremt_date ) );
									echo '</td>';
									echo '</tr>';
									$curremt_date = strtotime( '+1 day', strtotime( $curremt_date ) );
									$curremt_date = date( 'Y-m-d', $curremt_date );
								}
								?>
							</table>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	} else {
		?>
		<div class="mjschool-panel-body mjschool-panel-white">
			<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap" role="tablist">
				<li class="<?php if ( $active_tab === 'childlist' ) { ?> active<?php } ?>">
					<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'child', 'tab' => 'childlist' ), admin_url() ) ); ?>" class="nav-tab2">
					<i class="fas fa-align-justify"></i> <?php esc_html_e( 'Child List', 'mjschool' ); ?></a>
				</li>
				<?php
				if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'view_student' ) {
					?>
					<li class="<?php if ( $active_tab === 'view_student' ) { ?> active<?php } ?>">
						<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'child', 'tab' => 'view_student', 'action' => 'view_student', 'student_id' => sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ), admin_url() ) ); ?>" class="nav-tab2">
							<i class="fas fa-eye"></i> <?php esc_html_e( 'View Child', 'mjschool' ); ?></a>
						</a>
					</li>
					<?php
				}
				?>
			</ul>
			<?php
			if ( $active_tab === 'childlist' ) {
				?>
				<div class="tab-content">
					<div class="mjschool-panel-body">
						<form name="wcwm_report" action="" method="post">
							<div class="table-responsive">
								<table id="student_list" class="display dataTable mjschool-child-datatable" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Roll Number', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Actions', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$userRole           = mjschool_current_user_role();
										$parent_id          = get_current_user_id();
										$student_list       = array();
										$mjschool_user_meta = get_user_meta( $parent_id, 'mjschool_child_user_id', false );
										foreach ( $mjschool_user_meta as $child_id ) {
											if ( is_numeric( $child_id ) ) {
												$student_list = mjschool_get_student_by_id( $child_id );
											}
											if ( ! empty( $student_list ) ) {
												?>
												<tr>
													<td>
														<?php
														if ( $child_id ) {
															$user_id = $child_id;
															$umetadata = mjschool_get_user_image( $user_id );
														}
														if ( empty( $umetadata ) ) {
															echo '<img src="' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . '" class="img-circle" height="50px" width="50px"/>';
														} else {
															echo '<img src="' . esc_url( $umetadata ) . '" height="50px" width="50px" class="img-circle" alt=""/>';
														}
														?>
													</td>
													<td><?php echo esc_html( $student_list->first_name ) . ' ' . esc_html( $student_list->last_name ); ?></td>
													<td>
														<?php
														if ( ( $student_list->class_id ) != '' ) {
															echo esc_html( mjschool_get_class_name( $student_list->class_id ) );
														} else {
															esc_html_e( 'No Class', 'mjschool' );
														}
														?>
													</td>
													<td>
														<?php
														if ( ( $student_list->class_section ) != '' ) {
															echo esc_html( mjschool_get_section_name( $student_list->class_section ) );
														} else {
															esc_html_e( 'No Section', 'mjschool' );
														}
														?>
													</td>
													<td>
														<?php
														if ( ( $student_list->roll_no ) != '' ) {
															echo esc_html( $student_list->roll_no );
														} else {
															esc_html_e( 'No Roll Number', 'mjschool' );
														}
														?>
													</td>
													<td>
														<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'child', 'tab' => 'view_student', 'action' => 'view_student', 'student_id' => $child_id ), admin_url() ) ); ?>">
															<button type="button" class="btn mjschool-action-button action" data-id="<?php echo esc_attr( $child_id ); ?>">
																<i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?>
															</button>
														</a>
														<a href="<?php echo esc_url( add_query_arg( array( 'dashboard' => 'mjschool_user', 'page' => 'child', 'attendance' => 1, 'student_id' => $child_id ), admin_url() ) ); ?>">
															<button type="button" class="btn mjschool-action-button action" data-id="<?php echo esc_attr( $child_id ); ?>">
																<i class="fas fa-eye"></i> <?php esc_html_e( 'Attendance', 'mjschool' ); ?>
															</button>
														</a>
													</td>
												</tr>
												<?php
											}
										}
										?>
									</tbody>
								</table>
							</div>
						</form>
					</div>
				</div>
				<?php
			} else {
				$student_id             = sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) );
				$student_data           = mjschool_get_student_by_id( $student_id );
				$module                 = 'student';
				$custom_field_obj       = new Mjschool_Custome_Field();
				$user_custom_field      = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
				$mjschool_user_meta     = get_user_meta( $student_id, 'mjschool_parent_user_id', false );
				?>
				<div class="card mjschool-panel-body">
					<div class="row">
						<div class="col-xl-12 col-lg-12">
							<div class="user-profile">
								<div class="user-left">
									<?php
									if ( $student_id ) {
										$user_id = $student_id;
										$umetadata = mjschool_get_user_image( $user_id );
									}
									if ( empty( $umetadata ) ) {
										echo '<img src="' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . '" class="img-circle" />';
									} else {
										echo '<img src="' . esc_url( $umetadata ) . '" class="img-circle" alt=""/>';
									}
									?>
								</div>
								<div class="user-right">
									<h2 class="user-name"><b><?php echo esc_html( $student_data->first_name ) . ' ' . esc_html( $student_data->last_name ); ?></b></h2>
									<p>
										<span class="user-id"> <?php esc_html_e( 'Child ID', 'mjschool' ); ?> : <b><?php echo esc_html( $student_data->wp_usr_id ); ?></b></span>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xl-12 col-lg-12">
							<div class="card-head">
								<i class="fas fa-user"></i>
								<span><b><?php esc_html_e( 'Academic Information', 'mjschool' ); ?></b></span>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'Class Name', 'mjschool' ); ?></p></div>
									<div class="col-md-4">
										<p class="user-info">: 
											<?php
											if ( ( $student_data->class_id ) != '' ) {
												echo esc_html( mjschool_get_class_name( $student_data->class_id ) );
											} else {
												esc_html_e( 'No Class', 'mjschool' );
											}
											?>
										</p>
									</div>
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'Section Name', 'mjschool' ); ?></p></div>
									<div class="col-md-4">
										<p class="user-info">: 
											<?php
											if ( ( $student_data->class_section ) != '' ) {
												echo esc_html( mjschool_get_section_name( $student_data->class_section ) );
											} else {
												esc_html_e( 'No Section', 'mjschool' );
											}
											?>
										</p> 
									</div>
								</div>						
							</div>
							<div class="card-head">
								<i class="fas fa-map-marker"></i>
								<span> <b><?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </b></span>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'Address', 'mjschool' ); ?></p></div>
									<div class="col-md-4"><p class="user-info">: <?php echo esc_html( $student_data->address ); ?><br></p></div>
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'City', 'mjschool' ); ?></p></div>
									<div class="col-md-4"><p class="user-info">: <?php echo esc_html( $student_data->city ); ?></p></div>
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'State', 'mjschool' ); ?></p></div>
									<div class="col-md-4"><p class="user-info">: <?php echo esc_html( $student_data->state ); ?></p></div>
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'Zipcode', 'mjschool' ); ?></p></div>
									<div class="col-md-4"><p class="user-info">: <?php echo esc_html( $student_data->zip_code ); ?></p></div>
									<div class="col-md-2"><p class="user-lable"><?php esc_html_e( 'Phone Number', 'mjschool' ); ?></p></div>
									<div class="col-md-4"><p class="user-info">: <?php echo esc_html( $student_data->phone ); ?></p></div>
								</div>											
							</div>
							<?php
							if ( ! empty( $user_custom_field ) ) {
								?>
								<div class="card-head">
									<i class="fas fa-bars"></i>
									<span> <b><?php esc_html_e( 'Other Information', 'mjschool' ); ?> </b></span>
								</div>
								<div class="card-body">
									<div class="row">
										<?php
										foreach ( $user_custom_field as $custom_field ) {
											$custom_field_id = $custom_field->id;
											$module_record_id = sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) );
											$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
											?>
											<div class="col-xl-2 col-lg-2">
												<p class="user-lable"><?php echo esc_html( $custom_field->field_label ); ?></p>
											</div>	
											<?php
											if ( $custom_field->field_type === 'date' ) {
												?>
												<div class="col-xl-4 col-lg-4">
													<p class="user-info">: 
														<?php
														if ( ! empty( $custom_field_value ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
														} else {
															echo '-'; 
														}
														?>
													</p>
												</div>	
												<?php
											} elseif ( $custom_field->field_type === 'file' ) {
												if ( ! empty( $custom_field_value ) ) {
													?>
													<div class="col-xl-4 col-lg-4">
														<p class="user-info">
															<a target="blank" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>"><button class="btn btn-default view_document" type="button"> <i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'mjschool' ); ?></button></a>
															<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
														</p>
													</div>		
													<?php
												} else {
													echo '-';
												}
											} else {
												?>
												<div class="col-xl-4 col-lg-4">
													<p class="user-info">
														<?php
														if ( ! empty( $custom_field_value ) ) {
															echo esc_html( $custom_field_value );
														} else {
															echo '-'; 
														}
														?>
													</p>
												</div>	
												<?php
											}
										}
										?>
									</div>											
								</div>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="mjschool-panel-body">
				<div class="row">	
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#Section1"><i class="fas fa-user"></i><b><?php esc_html_e( ' Parents', 'mjschool' ); ?></b></a></li>
					</ul>
					<div class="tab-content">
						<div id="Section1" class="mjschool_new_sections tab-pane  active">
							<div class="row">
								<div class="col-lg-12">
									<div class="card">
										<div class="card-content">
											<div class="table-responsive">
												<table id="parents_list" class="table display" cellspacing="0" width="100%">
													<thead>
														<tr>
															<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Name', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Email', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Phone number', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Relation', 'mjschool' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<?php
														if ( ! empty( $mjschool_user_meta ) ) {
															foreach ( $mjschool_user_meta as $parentsdata ) {
																$parent = get_userdata( $parentsdata );
																?>
																<tr>
																	<td><?php 
																		if ( $parentsdata ) {
																			$umetadata = mjschool_get_user_image( $parentsdata );
																		}
																		if ( empty( $umetadata ) ) {
																			echo '<img src="' . esc_url( get_option( 'mjschool_parent_thumb_new' ) ) . '" height="50px" width="50px" class="img-circle" />';
																		} else {
																			echo '<img src="' . esc_url( $umetadata ) . '" height="50px" width="50px" class="img-circle"/>'; 
																		} ?>
																	</td>
																	<td><?php echo esc_html( $parent->user_email ); ?></td> 
																	<td><?php echo esc_html( $parent->phone ); ?></td>
																	<td><?php echo esc_html( $parent->first_name ) . ' ' . esc_html( $parent->last_name ); ?></td>
																	<td>
																		<?php
																		if ( $parent->relation === 'Father' ) {
																			echo esc_html__( 'Father', 'mjschool' );
																		} elseif ( $parent->relation === 'Mother' ) {
																			echo esc_html__( 'Mother', 'mjschool' );
																		}
																		?>
																	</td>
																</tr>
																<?php
															}
														}
														?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div id="Section2" class="tab-pane fade">			 
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
} else {
	wp_safe_redirect( admin_url( 'index.php' ) );
	exit;
}
?>