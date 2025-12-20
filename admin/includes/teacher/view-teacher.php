<?php
/**
 * Teacher Profile View Page (Admin Dashboard).
 *
 * This file is responsible for displaying detailed information about a teacher in the MJSchool plugin’s
 * admin dashboard. It retrieves teacher profile data including personal details, contact information,
 * class assignments, and schedules. The layout provides administrators with quick access to related
 * sections such as attendance tracking and class timetables.
 *
 * Key Features:
 * - Securely validates access using WordPress nonces (`wp_verify_nonce`) to prevent unauthorized access.
 * - Retrieves teacher data through WordPress user APIs and MJSchool model classes.
 * - Displays teacher profile image, name, contact number, address, and role information.
 * - Integrates tab-based navigation for:
 *   - General Information
 *   - Class List
 *   - Schedule
 *   - Attendance
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/teacher
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'view_action' ) ) {
	$active_tab1 = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'general';
	$teacher_obj      = new Mjschool_Teacher();
	$obj_route        = new Mjschool_Class_Routine();
	$custom_field_obj = new Mjschool_Custome_Field();
	$teacher_id_encrypted = isset( $_REQUEST['teacher_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['teacher_id'] ) ) : '';
	$teacher_id = ! empty( $teacher_id_encrypted ) ? intval( mjschool_decrypt_id( $teacher_id_encrypted ) ) : 0;
	$teacher_data     = get_userdata( $teacher_id );
	$user_access      = mjschool_get_user_role_wise_access_right_array();
	$school_obj       = new MJSchool_Management( get_current_user_id() );
	$mjschool_role             = $school_obj->role;
	?>
	<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div. -->
		<div class="content-body"><!-- Start content body div. -->
			<!-- Detail page header start. -->
			<section id="mjschool-user-information">
				<div class="mjschool-view-page-header-bg">
					<div class="row">
						<div class="col-xl-10 col-md-9 col-sm-10">
							<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
								<?php
								$umetadata = mjschool_get_user_image( $teacher_data->ID );
								?>
								<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( ! empty( $umetadata ) ? $umetadata : get_option( 'mjschool_teacher_thumb_new' ) ); ?>">
								<div class="row mjschool-profile-user-name">
									<div class="mjschool-float-left mjschool-view-top1">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<span class="mjschool-view-user-name-label"><?php echo esc_html( $teacher_data->display_name); ?></span>
											<?php
											if ($user_access_edit === '1' ) {
												?>
												<div class="mjschool-view-user-edit-btn">
													<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id='.rawurlencode( mjschool_encrypt_id($teacher_data->ID ) ) ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'edit_action' ) ) );?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
													</a>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<span><?php echo esc_html( $teacher_data->mobile_number); ?></span>
											</div>
										</div>
									</div>
								</div>
								<div class="row mjschool-view-user-teacher-label">
									<div class="col-xl-12 col-md-12 col-sm-12">
										<div class="mjschool-view-top2">
											<div class="row mjschool-view-user-teacher-label">
												<div class="col-md-12 mjschool-address-student-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;
													<span class="mjschool-address-detail-page"><?php echo esc_html( $teacher_data->address); ?></span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
							<div class="mjschool-group-thumbs">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
							</div>
							<div class="mjschool-viewpage-add-icon dropdown-menu-icon">
								<li class="mjschool-dropdown-icon-menu-div">
									<a class="mjschool-dropdown-icon-link" href="#" data-bs-toggle="dropdown" aria-expanded="false">
										<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-addmore-icon.png"); ?>" class="add_more_icon_detailpage">
									</a>
									<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
										<li class="mjschool-float-left-width-100px">
											<a href="<?php echo esc_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&tab1=teacher_attendences_list' ); ?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-plus.png"); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
										</li>
										<li class="mjschool-float-left-width-100px">
											<a href="<?php echo esc_url( 'admin.php?page=mjschool_route&tab=teacher_timetable' ); ?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-plus.png"); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Class Schedule', 'mjschool' ); ?></a>
										</li>
									</ul>
									
								</li>
							</div>
						</div>
					</div>
				</div>
			</section>
			<!-- Detail page header end. -->
			<!-- Detail page tabing start. -->
			<section id="body_area" class="teacher_view_tab body_areas">
				<div class="row">
					<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
						<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
							<li class="<?php if ( $active_tab1 === 'general' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teacher&tab1=general&teacher_id=' . rawurlencode(sanitize_text_field(wp_unslash($_REQUEST['teacher_id']))) . '&_wpnonce=' . rawurlencode(mjschool_get_nonce('view_action'))) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>">
									<?php esc_html_e( 'GENERAL', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab1 === 'mjschool-class-list' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teache&tab1=mjschool-class-list&teacher_id=' . rawurlencode(sanitize_text_field(wp_unslash($_REQUEST['teacher_id']))) . '&_wpnonce=' . rawurlencode(mjschool_get_nonce( 'view_action' ))) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'mjschool-class-list' ? 'active' : ''; ?>">
									<?php esc_html_e( 'CLass List', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab1 === 'schedule' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teache&tab1=schedule&teacher_id=' . rawurlencode(sanitize_text_field(wp_unslash($_REQUEST['teacher_id']))) . '&_wpnonce=' . rawurlencode(mjschool_get_nonce( 'view_action' )) )); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'schedule' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Class Schedule', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab1 === 'attendance' ) { ?>active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_teacher&tab=view_teacher&action=view_teache&tab1=attendance&teacher_id=' . rawurlencode(sanitize_text_field(wp_unslash($_REQUEST['teacher_id']))) . '&_wpnonce=' . rawurlencode(mjschool_get_nonce( 'view_action' ))) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendance' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Attendance', 'mjschool' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</section>
			<!-- Detail page tabing end. -->
			<!-- Detail page body content section. -->
			<section id="mjschool-body-content-area">
				<div class="mjschool-panel-body"><!-- Start panel body div.-->
					<?php
					// --- General tab start. ----//
					if ( $active_tab1 === 'general' ) {
						?>
						<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br>
								<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $teacher_data->user_email ); ?> </label>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br>
								<?php
								if ( $user_access_edit === '1' && empty( $teacher_data->mobile_number ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels">
										+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $teacher_data->mobile_number ); ?>
									</label>
								<?php } ?>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $teacher_data->gender ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $teacher_data->gender ) ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
								<?php
								$birth_date      = $teacher_data->birth_date;
								$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
								if ( $user_access_edit === '1' && $is_invalid_date ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels"> 
										<?php
										if ( ! empty( $birth_date ) && $birth_date != '1970-01-01' && $birth_date != '0000-00-00' ) {
											echo esc_html( mjschool_get_date_in_input_box( $birth_date ) );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' ); // Only shown to users without edit access.
										}
										?>
									</label>
								<?php } ?>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Position', 'mjschool' ); ?></label><br>
								<?php
								if ( $user_access_edit === '1' && empty( $teacher_data->possition ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels">
										<?php
										if ( ! empty( $teacher_data->possition ) ) {
											echo esc_html( $teacher_data->possition );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								<?php } ?>
							</div>
						</div>
						<!-- Student information div start. -->
						<div class="row mjschool-margin-top-20px">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label> <br>
												<?php
												if ( $user_access_edit === '1' && empty( $teacher_data->city ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( $teacher_data->city ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $teacher_data->state ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $teacher_data->state ) ) {
															echo esc_html( $teacher_data->state );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $teacher_data->zip_code ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( $teacher_data->zip_code ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alternate Mobile Number', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $teacher_data->alternet_mobile_number ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $teacher_data->alternet_mobile_number ) ) {
															?>
															+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
															<?php
															echo esc_html( $teacher_data->alternet_mobile_number );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Working Hour', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $teacher_data->working_hour ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $teacher_data->working_hour ) ) {
															$working_data = $teacher_data->working_hour;
															if ( $working_data === 'full_time' ) {
																esc_html_e( 'Full Time', 'mjschool' );
															} else {
																esc_html_e( 'Part Time', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?></label><br>
												<label class="mjschool-view-page-content-labels">
													<?php
													$classes   = '';
													$classes   = $teacher_obj->mjschool_get_class_by_teacher( $teacher_data->ID );
													$classname = '';
													foreach ( $classes as $class ) {
														$classname .= mjschool_get_class_name( $class['class_id'] ) . ',';
													}
													$classname_rtrim = rtrim( $classname, ', ' );
													$classname_ltrim = ltrim( $classname_rtrim, ', ' );
													echo esc_html( $classname_ltrim );
													?>
												</label>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Subject', 'mjschool' ); ?> </label><br>
												<?php
												$obj_subject = new Mjschool_Subject();
												$subjectname = $obj_subject->mjschool_get_subject_name_by_teacher( $teacher_data->ID );
												if ( $user_access_edit === '1' && empty( $subjectname ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $subjectname ) ) {
															echo esc_html( rtrim( $subjectname, ', ' ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Designation', 'mjschool' ); ?> </label><br>
												<?php
												$user_designation_id = get_user_meta( intval( $teacher_data->ID ), 'designation', true );
												if ( $user_access_edit === '1' && empty( $user_designation_id ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $user_designation_id ) ) {
															$designation_post = get_post( $user_designation_id );
															if ( $designation_post && $designation_post->post_type === 'designation' ) {
																echo esc_html( $designation_post->post_title );
															} else {
																echo esc_html__( 'Not Provided', 'mjschool' );
															}
														} else {
															echo esc_html__( 'Not Provided', 'mjschool' );
														} ?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Signature', 'mjschool' ); ?></label><br>
												<?php
												$signature_file = get_user_meta( intval( $teacher_data->ID ), 'signature', true );
												if ( $user_access_edit === '1' && empty( $signature_file ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_teacher&tab=addteacher&action=edit&teacher_id=' . esc_attr( mjschool_encrypt_id( $teacher_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $signature_file ) ) {
															$signature_url = esc_url( content_url() . '/' . ltrim( $signature_file, '/' ) );
															echo '<a class="btn btn-default" href="' . esc_url( $signature_url ) . '" target="_blank"><i class="fas fa-download"></i> ' . esc_html__( 'Download', 'mjschool' ) . '</a>';
														} else {
															echo esc_html__( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
										</div>
										<?php
										if ( ! empty( $teacher_data->user_document ) ) {
											?>
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
											<div class="row">
												<?php
												$document_array = json_decode( $teacher_data->user_document );
												foreach ( $document_array as $key => $value ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
														<label class="mjschool-label-value">
															<?php
															if ( ! empty( $value->document_file ) ) {
																?>
																<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $value->document_file ) ); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
																<?php
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															} ?>
														</label>
													</div>
													<?php
												}
												?>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
								$module = 'teacher';
								$custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
								?>
							</div>
						</div>
						<?php
					}
					// --- General tab end. ----//
					// ---  Attendance tab start. --//
					elseif ( $active_tab1 === 'attendance' ) {
						$attendance_list = mjschool_monthly_attendence_teacher( $teacher_id );
						if ( ! empty( $attendance_list ) ) {
							?>
							<div class="table-div"><!--  Start panel body div. -->
								<div class="table-responsive"><!-- Table responsive div start. -->
									<table id="mjschool-attendance-list-detail-page-for-teacher" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Teacher Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
												<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?> </th>
												<th><?php esc_html_e( 'Status', 'mjschool' ); ?> </th>
												<th><?php esc_html_e( 'Comment', 'mjschool' ); ?> </th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i    = 0;
											$srno = 1;
											if ( ! empty( $attendance_list ) ) {
												foreach ( $attendance_list as $retrieved_data ) {
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
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
															<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image">
															</p>
														</td>
														<td ><?php echo esc_html( mjschool_get_user_name_by_id( $retrieved_data->user_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Teacher Name', 'mjschool' ); ?>"></i></td>
														<td class="name"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendence_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i></td>
														<td >
															<?php
															$curremt_date = $retrieved_data->attendence_date;
															$day          = date( 'D', strtotime( $curremt_date ) );
															if ( $day === 'Mon' ) {
																esc_html_e( 'Monday', 'mjschool' );
															} elseif ( $day === 'Sun' ) {
																esc_html_e( 'Sunday', 'mjschool' );
															} elseif ( $day === 'Tue' ) {
																esc_html_e( 'Tuesday', 'mjschool' );
															} elseif ( $day === 'Wed' ) {
																esc_html_e( 'Wednesday', 'mjschool' );
															} elseif ( $day === 'Thu' ) {
																esc_html_e( 'Thursday', 'mjschool' );
															} elseif ( $day === 'Fri' ) {
																esc_html_e( 'Friday', 'mjschool' );
															} elseif ( $day === 'Sat' ) {
																esc_html_e( 'Saturday', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
														</td>
														<td class="name">
															<?php echo esc_html( mjschool_get_display_name( $retrieved_data->attend_by ) ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
														</td>
														<td class="name">
															<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
															<span style="color:<?php echo esc_attr( $status_color ); ?>;">
																<?php echo esc_html( $retrieved_data->status ); ?>
															</span>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
														</td>
														<td class="name">
															<?php
															if ( ! empty( $retrieved_data->comment ) ) {
																$comment       = $retrieved_data->comment;
																$grade_comment = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
																echo esc_html( $grade_comment );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_html( $retrieved_data->comment ); } else { esc_html_e( 'Comment', 'mjschool' ); } ?>"></i>
														</td>
													</tr>
													<?php
													++$i;
													++$srno;
												}
											}
											?>
										</tbody>
									</table>
								</div><!-- Table responsive div end. -->
							</div>
							<?php
						} else {
							?>
							<div class="mjschool-no-data-list-div">
								
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=teacher_attendance&tab1=teacher_attendences' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
								</a>
								
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						}
					}
					// ---  Attendance tab End. --//
					// ---  Class list tab start. --//
					elseif ( $active_tab1 === 'mjschool-class-list' ) {
						$classes = $teacher_obj->mjschool_get_class_by_teacher( $teacher_id );
						if ( $classes ) {
							?>
							<div class="table-div"><!-- Start panel body div. -->
								<div class="table-responsive"><!-- Table responsive div start. -->
									<table id="mjschool-class-list-detail-page" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?> </th>
												<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?> </th>
											</tr>
										</thead>
										<tbody>
											<?php
											$i = 0;
											if ( ! empty( $classes ) ) {
												foreach ( $classes as $class_id ) {
													$section_id     = mjschool_get_section_by_class_id( $class_id->class_id );
													$section_name   = '';
													$retrieved_data = mjschool_get_class_data_by_class_id( $class_id );
													if ( ! empty( $retrieved_data ) ) {
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
															<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( get_option( 'mjschool_student_thumb_new' ) ) ?>" class="img-circle" /></td>
															<td>
																<?php
																if ( $retrieved_data->class_name ) {
																	echo esc_html( $retrieved_data->class_name );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																foreach ( $section_id as $section ) {
																	$section_name .= $section->section_name . ', ';
																}
																$section_name_rtrim = rtrim( $section_name, ', ' );
																$section_name_ltrim = ltrim( $section_name_rtrim, ', ' );
																if ( ! empty( $section_name_ltrim ) ) {
																	echo esc_html( $section_name_ltrim );
																} else {
																	esc_html_e( 'No Section', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																if ( $retrieved_data->class_num_name ) {
																	echo esc_html( $retrieved_data->class_num_name );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Numeric Name', 'mjschool' ); ?>"></i>
															</td>
															<?php
															$class_id = $retrieved_data->class_id;
															
															$mjschool_user = count(get_users(array(
																'meta_key' => 'class_name',
																'meta_value' => $class_id
															 ) ) );
															
															?>
															<td>
																<?php
																echo esc_html( $mjschool_user ) . ' ';
																esc_html_e( 'Out Of', 'mjschool' );
																echo ' ' . esc_html( $retrieved_data->class_capacity );
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Capacity', 'mjschool' ); ?>"></i>
															</td>
															<td class="action">
																<div class="mjschool-user-dropdown">
																	<ul  class="mjschool_ul_style">
																		<li >
																			<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																			</a>
																			<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																				<li class="mjschool-float-left-width-100px">
																					<a class="mjschool-float-left-width-100px" href="<?php echo esc_url( 'admin.php?page=mjschool_class&tab=class_details&tab1=student_list&class_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->class_id ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'view_action' ) ) ); ?>"><i class="fas fa-list"></i><?php esc_html_e( 'Student List', 'mjschool' ); ?></a>
																				</li>
																			</ul>
																		</li>
																	</ul>
																</div>
															</td>
														</tr>
														<?php
														++$i;
													}
												}
											}
											?>
										</tbody>
									</table>
								</div><!-- Table responsive div end. -->
							</div>
							<?php
						} else {
						}
					}
					// ---  Class list tab end. --//
					// ---- Class schedule tab start. ----//
					elseif ( $active_tab1 === 'schedule' ) {
						$schedule_available = false; // Flag to check if any schedule exists.
						// Check if there is at least one schedule.
						foreach ( mjschool_day_list() as $daykey => $dayname ) {
							$period_1 = $obj_route->mjschool_get_period_by_teacher( $teacher_data->ID, $daykey );
							$period_2 = $obj_route->mjschool_get_period_by_particular_teacher( $teacher_data->ID, $daykey );
							if ( ! empty( $period_1 ) || ! empty( $period_2 ) ) {
								$schedule_available = true;
								break; // Exit loop early if a schedule is found.
							}
						}
						// If schedule is available, display table.
						if ( $schedule_available ) {
							?>
							<div id="Section1" class="mjschool_new_sections">
								<div class="row">
									<div class="col-lg-12">
										<div>
											<div class="mjschool-class-border-div card-content">
												<table class="table table-bordered mjschool-class-schedule">
													<?php foreach ( mjschool_day_list() as $daykey => $dayname ) { ?>
														<tr>
															<th><?php echo esc_html( $dayname ); ?></th>
															<td>
																<?php
																$period_1 = $obj_route->mjschool_get_period_by_teacher( $teacher_data->ID, $daykey );
																$period_2 = $obj_route->mjschool_get_period_by_particular_teacher( $teacher_data->ID, $daykey );
																if ( ! empty( $period_1 ) && ! empty( $period_2 ) ) {
																	$period = array_merge( $period_1, $period_2 );
																} elseif ( ! empty( $period_1 ) ) {
																	$period = $period_1;
																} elseif ( ! empty( $period_2 ) ) {
																	$period = $period_2;
																} else {
																	$period = array();
																}
																if ( ! empty( $period ) ) {
																	// Sorting function.
																	usort(
																		$period,
																		function ( $a, $b ) {
																			$startA = DateTime::createFromFormat( 'h:i A', trim( $a->start_time ) );
																			$startB = DateTime::createFromFormat( 'h:i A', trim( $b->start_time ) );
																			if ( $startA === $startB ) {
																				$endA = DateTime::createFromFormat( 'h:i A', trim( $a->end_time ) );
																				$endB = DateTime::createFromFormat( 'h:i A', trim( $b->end_time ) );
																				return $endA <=> $endB;
																			}
																			return $startA <=> $startB;
																		}
																	);
																	foreach ( $period as $period_data ) {
																		echo '<div class="btn-group m-b-sm">';
																		echo '<button class="btn btn-primary mjschool-class-list-button dropdown-toggle" aria-expanded="false" data-toggle="dropdown">
																		<span class="mjschool-period-box" id=' . esc_attr( $period_data->route_id ) . '>' . esc_html( mjschool_get_single_subject_name( $period_data->subject_id ) );
																		$start_time_data = explode( ':', $period_data->start_time );
																		$start_hour      = str_pad( $start_time_data[0], 2, '0', STR_PAD_LEFT );
																		$start_min       = str_pad( $start_time_data[1], 2, '0', STR_PAD_LEFT );
																		$end_time_data   = explode( ':', $period_data->end_time );
																		$end_hour        = str_pad( $end_time_data[0], 2, '0', STR_PAD_LEFT );
																		$end_min         = str_pad( $end_time_data[1], 2, '0', STR_PAD_LEFT );
																		echo '<span class="time"> ( ' . esc_html( $start_hour ) . ':' . esc_html( $start_min ) . ' - ' . esc_html( $end_hour ) . ':' . esc_html( $end_min ) . ' ) </span>';
																		echo '<span>' . esc_html( mjschool_get_class_name( $period_data->class_id ) ) . '</span>';
																		echo '</span><span class="caret"></span></button>';
																		?>
																		<ul role="menu" class="dropdown-menu">
																			<li>
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=addroute&action=edit&route_id=' . rawurlencode( $period_data->route_id ) ) ); ?>">
																					<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																				</a>
																			</li>
																			<li>
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=route_list&action=delete&route_id=' . rawurlencode( $period_data->route_id )) ); ?>">
																					<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																				</a>
																			</li>
																		</ul>
																		<?php
																		echo '</div>';
																	}
																} else {
																	echo '<span class="text-muted">' . esc_html__( 'No Schedule Available', 'mjschool' ) . '</span>';
																}
																?>
															</td>
														</tr>
													<?php } ?>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
						} else {
							 ?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_route&tab=addroute' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php 
						}
					}
					// ---- Class schedule tab end. ----//
					?>
				</div><!-- End panel body div.-->
			</section>
			<!-- Detail page body content section end. -->
		</div><!-- End content body div.-->
	</div><!-- End panel body div.-->
	<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}
?>