<?php
/**
 * Student Profile View Template.
 *
 * This file displays the detailed student profile page within the MJSchool plugin.
 * It allows administrators to view, manage, and navigate between various sections
 * of a student's information, including personal details, class information,
 * attendance, exam results, fee payments, and more.
 *
 * Key Features:
 * - Displays a complete student profile with personal and academic details.
 * - Supports tab-based navigation for sections such as General, Parents, Attendance, etc.
 * - Includes a dynamically generated QR code for student identification.
 * - Integrates edit options for authorized users with proper nonce verification.
 * - Retrieves data securely using WordPress functions and custom plugin helpers.
 * - Implements conditional rendering to handle missing or optional data.
 * - Uses WordPress escaping and sanitization functions for secure output.
 * - Supports internationalization for all labels and messages.
 * - Ensures responsive design and accessibility across admin interfaces.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/student
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'view_action' ) ) {
	$active_tab1                = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
	$student_id                 = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
	$student_data               = get_userdata( $student_id );
	$user_meta                  = get_user_meta( $student_id, 'parent_id', true );
	$sibling_information_value  = str_replace( '"[', '[', $student_data->sibling_information );
	$sibling_information_value1 = str_replace( ']"', ']', $sibling_information_value );
	$sibling_information        = json_decode( $sibling_information_value1 );
	$parent_list                = mjschool_get_student_parent_id( $student_id );
	$mjschool_custom_field_obj  = new Mjschool_Custome_Field();
	$mjschool_page_name         = sanitize_text_field( wp_unslash($_REQUEST['page']) );
	$school_obj                 = new MJSchool_Management( get_current_user_id() );
	$mjschool_role                       = $school_obj->role;
	$class_id                   = get_user_meta( $student_id, 'class_name', true );
	$section_name               = get_user_meta( $student_id, 'class_section', true );
	?>
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="mjschool-category-list"></div>
		</div>
	</div>
</div>
<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div. -->
	<div class="content-body">
		<!-- Detail page header start. -->
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<?php
							$userimage = mjschool_get_user_image( $student_data->ID );
							?>
							<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url( $userimage ); } else { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } ?>">
							<div class="row mjschool-profile-user-name">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<span class="mjschool-view-user-name-label"><?php echo esc_html( $student_data->display_name ); ?></span>
										<?php
										if ( $user_access_edit === '1' ) {
											?>
											<div class="mjschool-view-user-edit-btn">
												<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id='.rawurlencode( mjschool_encrypt_id( $student_data->ID ) ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
												</a>
											</div>
											<?php
										}
										?>
									</div>
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-phone.png' ); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<span class="mjschool-color-white-rs"><?php echo esc_html( $student_data->mobile_number ); ?></span>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="mjschool-view-top2">
										<div class="row mjschool-view-user-doctor-label">
											<div class="col-md-12 mjschool-address-student-div">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-location.png' ); ?>">&nbsp;&nbsp;<span class="mjschool-address-detail-page"><?php echo esc_html( $student_data->address ); ?></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn-possition-res">
						<div class="mjschool-group-thumbs">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-group.png' ); ?>">
						</div>
						<div class="mjschool-viewpage-add-icon dropdown-menu-icon">
							<li class="mjschool-dropdown-icon-menu-div">
								<a class="mjschool-dropdown-icon-link" href="#" data-bs-toggle="dropdown" aria-expanded="false"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-addmore-icon.png' ); ?>" class="add_more_icon_detailpage"></a>
								<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
									<li class="mjschool-float-left-width-100px">
										<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_result'));?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-plus.png' ); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Manage Marks', 'mjschool' ); ?></a>
									</li>
									<li class="mjschool-float-left-width-100px">
										<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_attendence&tab=student_attendance&tab1=subject_attendence'));?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-plus.png' ); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Attendance', 'mjschool' ); ?></a>
									</li>
									<li class="mjschool-float-left-width-100px">
										<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_fees_payment&tab=addpaymentfee'));?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-plus.png' ); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Fees Payment', 'mjschool' ); ?></a>
									</li>
									<li class="mjschool-float-left-width-100px">
										<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_message&tab=compose'));?>" class="mjschool-float-left-width-100px"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-plus.png' ); ?>" class="image_margin_right_10px"><?php esc_html_e( 'Message', 'mjschool' ); ?></a>
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
		<section id="body_area" class="mjschool-student-view-tab body_areas">
			<div class="row">
				<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<?php 
						$student_id_safe = sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ?? '' ) );
						?>
						<li class="<?php if ( $active_tab1 === 'general' ) {?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=general&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>">
								<?php esc_html_e( 'GENERAL', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'parent' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=parent&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'parent' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Parent List', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'hallticket' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=hallticket&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'hallticket' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Hall Ticket', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'exam_result' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=exam_result&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'exam_result' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Exam Results', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'homework' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=homework&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'homework' ? 'active' : ''; ?>">
								<?php esc_html_e( 'HomeWork', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'attendance' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=attendance&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'attendance' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Attendance', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'leave_list' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=leave_list&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'leave_list' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Leave', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'feespayment' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=feespayment&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'feespayment' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Fees Payment', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'issuebook' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=issuebook&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'issuebook' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Issue Book', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'message' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url('admin.php?page=mjschool_student&tab=view_student&action=view_student&tab1=message&student_id='.rawurlencode( $student_id_safe ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'message' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Messages', 'mjschool' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</section>
		<!-- Detail page tabing end. -->
		<!-- Detail page body content section. -->
		<section id="mjschool-body-content-area">
			<div class="mjschool-panel-body"><!-- Start panel body div. -->
				<?php
				// General tab start.
				if ( $active_tab1 === 'general' ) {
					?>
					<div class="mjschool-popup-bg">
						<div class="mjschool-overlay-content mjschool-admission-popup">
							<div class="modal-content">
								<div class="mjschool-category-list">
								</div>
							</div>
						</div>
					</div>
					<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
						<div class="col-xl-4 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br>
							<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $student_data->user_email ); ?> </label>
						</div>
						<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Student ID', 'mjschool' ); ?> </label><br>
							<?php
							if ( $user_access_edit === '1' && empty( $student_data->admission_no ) ) {
								$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
								echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
							} else {
								?>
								<label class="mjschool-view-page-content-labels">
									<?php
									if ( ! empty( $student_data->admission_no ) ) {
										echo esc_html( $student_data->admission_no );
									} else {
										esc_html_e( 'Not Provided', 'mjschool' );
									}
									?>
								</label>
							<?php } ?>
						</div>
						<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Roll Number', 'mjschool' ); ?> </label><br>
							<?php
							if ( $user_access_edit === '1' && empty( $student_data->roll_id ) ) {
								$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
								echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
							} else {
								?>
								<label class="mjschool-view-page-content-labels">
									<?php
									if ( ! empty( $student_data->roll_id ) ) {
										echo esc_html( $student_data->roll_id );
									} else {
										esc_html_e( 'Not Provided', 'mjschool' );
									}
									?>
								</label>
							<?php } ?>
						</div>
						<div class="col-xl-2 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
							<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?> </label><br>
							<?php
							if ( $user_access_edit === '1' && empty( $student_data->class_name ) ) {
								$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
								echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
							} else {
								?>
								<label class="mjschool-view-page-content-labels">
									<?php
									$class_name = mjschool_get_class_name( $student_data->class_name );
									if ( $class_name === ' ' ) {
										esc_html_e( 'Not Provided', 'mjschool' );
									} else {
										echo esc_html( $class_name );
									}
									?>
								</label>
							<?php } ?>
						</div>
						<?php if ( $school_type === 'school' ) {?>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Section Name', 'mjschool' ); ?> </label><br>
								<?php
								if ( $user_access_edit === '1' && empty( $student_data->class_section ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels">
										<?php
										if ( ! empty( $student_data->class_section ) ) {
											echo esc_html( mjschool_get_section_name( $student_data->class_section ) );
										} else {
											esc_html_e( 'No Section', 'mjschool' );
										}
										?>
									</label>
								<?php } ?>
							</div>
						<?php }?>
					</div>
					<!-- Student information div start.  -->
					<div class="row mjschool-margin-top-20px">
						<div class="col-xl-8 col-md-8 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-rtl-custom-padding-0px">
								<div class="mjschool-guardian-div">
									<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Student Information', 'mjschool' ); ?> </label>
									<div class="row">
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Full Name', 'mjschool' ); ?> </label> <br>
											<?php
											if ( $user_access_edit === '1' && empty( $student_data->display_name ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->display_name ); ?></label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $student_data->alternet_mobile_number ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $student_data->alternet_mobile_number ) ) {
														?>
														+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
														<?php
														echo esc_html( $student_data->alternet_mobile_number );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $student_data->gender ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( $student_data->gender === 'male' ) {
														echo esc_attr__( 'Male', 'mjschool' );
													} elseif ( $student_data->gender === 'female' ) {
														echo esc_attr__( 'Female', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
											<?php
											$birth_date      = $student_data->birth_date;
											$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
											if ( $user_access_edit === '1' && $is_invalid_date ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $student_data->birth_date ) ) {
														echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $student_data->city ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->city ); ?></label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
											<?php
											if ( $user_access_edit === '1' && empty( $student_data->state ) ) {
												$edit_url = admin_url( 'admin.php?page=mjschool_student&tab=addstudent&action=edit&student_id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
												echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
											} else {
												?>
												<label class="mjschool-view-page-content-labels">
													<?php
													if ( ! empty( $student_data->state ) ) {
														echo esc_html( $student_data->state );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
												</label>
											<?php } ?>
										</div>
										<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
											<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zipcode', 'mjschool' ); ?> </label><br>
											<label class="mjschool-view-page-content-labels"><?php echo esc_html( $student_data->zip_code ); ?></label>
										</div>
									</div>
									<?php
									if ( ! empty( $student_data->user_document ) ) {
										?>
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
										<div class="row">
											<?php
											$document_array = json_decode( $student_data->user_document );
											foreach ( $document_array as $key => $value ) {
												?>
												<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
													<label class="mjschool-label-value">
														<?php
														if ( ! empty( $value->document_file ) ) {
															?>
															<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php print esc_url( content_url( '/uploads/school_assets/' . $value->document_file ) ); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
															<?php
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );}
														?>
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
							$has_sibling = false;
							// First, check if there's at least one valid sibling.
							foreach ( $sibling_information as $sibling ) {
								if ( ! empty( $sibling->siblingsstudent ) ) {
									$has_sibling = true;
									break;
								}
							}
							if ( $has_sibling ) {
								?>
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Siblings Information', 'mjschool' ); ?></label>
										<?php
										foreach ( $sibling_information as $value ) {
											if ( empty( $value->siblingsstudent ) ) {
												continue;
											}
											$sibling_data = get_userdata( $value->siblingsstudent );
											if ( ! empty( $sibling_data ) ) {
												?>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Name', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_student_display_name_with_roll( $sibling_data->ID ) ); ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Email', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $sibling_data->user_email ); ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Class', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $value->siblingsclass, $value->siblingssection ) ); ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $sibling_data->mobile_number ) ) {
																echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $sibling_data->mobile_number );
															} else {
																echo esc_html__( 'Not Provided', 'mjschool' );
															}
															?>
														</label>
													</div>
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
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
								<div class="mjschool-guardian-div mjschool-parent-information-div-overflow">
									<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Parent Information', 'mjschool' ); ?> </label>
									<?php
									if ( ! empty( $user_meta ) ) {
										foreach ( $user_meta as $parentsdata ) {
											$parent = get_userdata( $parentsdata );
											if ( ! empty( $parent ) ) {
												?>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Name', 'mjschool' ); ?></p>
														<p class="mjschool-view-page-content-labels"><a class="mjschool-color-black" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent&tab=view_parent&action=view_parent&parent_id='.rawurlencode( $parent->ID ) ) ); ?>"><?php echo esc_attr( mjschool_get_parent_name_by_id( $parent->ID ) ); ?></a></p>
													</div>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
														<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Email ID', 'mjschool' ); ?></p>
														<p class="mjschool-view-page-content-labels"><?php echo esc_html( $parent->user_email ); ?></p>
													</div>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
														<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></p>
														<p class="mjschool-view-page-content-labels">
															<?php if ( $parent->mobile_number ) : ?>
																+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent->mobile_number ); ?>
															<?php else : ?>
																Not Provided
															<?php endif; ?>
														</p>
													</div>
													<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-top-15px">
														<p class="mjschool-view-page-header-labels"><?php esc_html_e( 'Relation', 'mjschool' ); ?></p>
														<p class="mjschool-view-page-content-labels">
															<?php
															if ( $parent->relation === 'Father' ) {
																echo esc_attr__( 'Father', 'mjschool' );
															} elseif ( $parent->relation === 'Mother' ) {
																echo esc_attr__( 'Mother', 'mjschool' );
															}
															?>
														</p>
													</div>
												</div>
												<?php
											}
										}
									} else {
										?>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px-rtl mjschool-margin-top-15px mjschool_text_align_center" >
											<p class="mjschool-view-page-content-labels"><?php echo esc_attr__( 'No Any Parent.', 'mjschool' ); ?></p>
										</div>
										<?php
									}
									?>
								</div>
							</div>
							<?php
							$hostel_data = mjschool_student_assign_bed_data_by_student_id( $student_id );
							$room_data   = '';
							if ( ! empty( $hostel_data ) ) {
								$room_data = mjschool_get_room__data_by_room_id( $hostel_data->room_id );
							}
							if ( ! empty( $hostel_data ) ) {
								$h_name = mjschool_hostel_name_by_id( $hostel_data->hostel_id );
								?>
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-rtl-custom-padding-0px">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Hostel Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="col-xl-4 col-md-4 col-sm-12">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Hostel Name', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $hostel_data ) ) {
															if ( $h_name ) {
																echo esc_html( $h_name );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $room_data ) ) {
															if ( $room_data->room_unique_id ) {
																echo esc_html( $room_data->room_unique_id );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?> </label><br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $hostel_data ) ) {
															if ( $hostel_data->bed_unique_id ) {
																echo esc_html( $hostel_data->bed_unique_id );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Charge', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $hostel_data ) ) {
															if ( $hostel_data->bed_id ) {
																echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( mjschool_get_bed_charge_by_id( $hostel_data->bed_id ), 2, '.', '' ) ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Bed Assign Date', 'mjschool' ); ?> </label> <br>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $hostel_data ) ) {
															if ( $hostel_data->assign_date ) {
																echo esc_html( mjschool_get_date_in_input_box( $hostel_data->assign_date ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
															}
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							$module = 'student';
							$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
							?>
						</div>
						<!-- Other information div start. -->
						<!-- Student I-card & QR code div start.  -->
						<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool_fix_card_rtl">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rtl-custom-padding-0px">
								<div class="mjschool-id-page-card mjschool-card-margin-bottom">
									<img class="mjschool-icard-logo" src="<?php echo esc_url( get_option( 'mjschool_logo' ) ); ?>">
									<div class="mjschool-card-heading mjschool-card-title-position mjschool_70px">
										<label class="mjschool-id-card-label"><?php echo esc_html( get_option( 'mjschool_name' ) ); ?> </label>
									</div>
									<div class="mjschool-id-card-body">
										<div class="row">
											<div class="col-md-3 col-3 mjschool-id-margin">
												<p class="mjschool-id-card-image">
													<img class="mjschool-id-card-user-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($userimage); } else { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } ?>">
												</p>
												<p class="mjschool-id-card-image mjschool-card-code">
													<img class="mjschool-id-card-barcode" id='barcode' src=''>
												</p>
											</div>
											<div class="col-md-9 col-9  mjschool-id-card-info row">
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<h5 class="mjschool-student-info"><?php esc_html_e( 'Student Name', 'mjschool' ); ?></h5>
												</div>
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<p class="mjschool-icard-dotes">:&nbsp;</p>
													<h5 class="mjschool-user-info"><?php echo esc_html( $student_data->display_name); ?></h5>
												</div>
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<h5 class="mjschool-student-info"><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></h5>
												</div>
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<p class="mjschool-icard-dotes">:&nbsp;</p>
													<h5 class="mjschool-user-info">
														<?php if ( ! empty( $student_data->roll_id ) ) {
															echo esc_html( $student_data->roll_id );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														} ?>
													</h5>
												</div>
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<h5 class="mjschool-student-info"><?php esc_html_e( 'Contact No', 'mjschool' ); ?>.</h5>
												</div>
												<div class="p-0 col-md-6 col-6 mjschool-card-user-name">
													<p class="mjschool-icard-dotes">:&nbsp;</p>
													<h5 class="mjschool-user-info">
														<label ><?php echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $student_data->mobile_number ); ?>
													</h5>
												</div>
												<div class="p-0 col-md-6 col-6">
													<h5 class="mjschool-student-info"><?php esc_html_e( 'Class', 'mjschool' ); ?></h5>
												</div>
												<div class="p-0 col-md-6 col-6">
													<p class="mjschool-icard-dotes">:&nbsp;</p>
													<h5 class="mjschool-user-info">
														<?php
														$class_name = mjschool_get_class_section_name_wise( $student_data->class_name, $student_data->class_section);
														if ($class_name === " ") {
															esc_html_e( 'Not Provided', 'mjschool' );
														} else {
															echo esc_html( $class_name );
														}
														?>
													</h5>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="mjschool-qr-code-card">
									<div class="mjschool-qr-main-div">
										<h3><?php esc_html_e( 'Scan Below QR For Attendance', 'mjschool' ); ?></h3>
										<div class="mjschool-qr-image-div"><img class="mjschool-id-card-barcode qr_width" id='barcode' src=''></div>
									</div>
									
								</div>
							</div>
						</div>
					</div>
					<!-- Student I-card & QR code end. -->
					<?php
				}
				// Parents tab start.
				elseif ( $active_tab1 === 'parent' ) {
					if ( ! empty( $user_meta ) ) {
						?>
						<div>
							<div id="Section1" class="mjschool_new_sections">
								<div class="row">
									<div class="col-lg-12">
										<div>
											<div class="card-content">
												<div class="table-responsive">
													<table id="mjschool-parents-list-detail-page" class="display table" cellspacing="0" width="100%">
														<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
															<tr>
																<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Parent Name & Email', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Relation', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Address', 'mjschool' ); ?></th>
															</tr>
														</thead>
														<tbody>
															<?php
															if ( ! empty( $user_meta ) ) {
																foreach ( $user_meta as $parentsdata ) {
																	if ( ! empty( $parentsdata->errors ) ) {
																		$parent = '';
																	} else {
																		$parent = get_userdata( $parentsdata );
																	}
																	if ( ! empty( $parent ) ) {
																		?>
																		<tr>
																			<td class="mjschool-width-50px-td">
																				<?php
																				if ($parentsdata) {
																					$umetadata = mjschool_get_user_image($parentsdata);
																				}
																				if (empty($umetadata ) ) {
																					echo '<img src=' . esc_url( get_option( 'mjschool_parent_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
																				} else
																					echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>'; ?>
																			</td>
																			<td class="name">
																				<a class="mjschool-color-black" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent&tab=view_parent&action=view_parent&parent_id='.rawurlencode( mjschool_encrypt_id( $parent->ID ) ).'&_wpnonce='.rawurlencode( mjschool_get_nonce( 'view_action' ) ) ) ); ?>">
																					<?php echo esc_html( mjschool_get_parent_name_by_id( $parent->ID ) ); ?>
																				</a>
																				<br>
																				<label class="mjschool-list-page-email"><?php echo esc_html( $parent->user_email ); ?></label>
																			</td>
																			<td>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent->mobile_number ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i></td>
																			<td>
																				<?php
																				if ( ! empty( $parent->phone ) ) {
																					echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) );
																					?>
																					&nbsp;&nbsp;
																					<?php
																					echo esc_html( $parent->phone );
																				} else {
																					esc_html_e( 'Not Provided', 'mjschool' );
																				}
																				?>
																				<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Alt. Mobile Number', 'mjschool' ); ?>"></i>
																			</td>
																			<td>
																				<?php
																				if ( $parent->relation === 'Father' ) {
																					echo esc_attr__( 'Father', 'mjschool' );
																				} elseif ( $parent->relation === 'Mother' ) {
																					echo esc_attr__( 'Mother', 'mjschool' );
																				}
																				?>
																				<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Relation', 'mjschool' ); ?>"></i>
																			</td>
																			<td>
																				<?php
																				$task_subject = esc_html( $parent->address );
																				$max_length   = 25; // Adjust this value to your desired maximum length.
																				if ( $parent->address ) {
																					if ( strlen( $task_subject ) > $max_length ) {
																						echo esc_html( substr( $task_subject, 0, $max_length ) ) . '...';
																					} else {
																						echo esc_html( $task_subject );
																					}
																				} else {
																					esc_html_e( 'Not Provided', 'mjschool' );
																				}
																				?>
																				<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( $parent->address ) { echo esc_attr( $parent->address ); } else { echo esc_attr__( 'Address', 'mjschool' ); } ?>"></i>
																			</td>
																		</tr>
																		<?php
																	}
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
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-no-data-list-div">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=assign_parent&student_id=' . rawurlencode( $student_id ) ) ); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
							</a>
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
							</div>
						</div>
						<?php
					}
				}
				// Feespayment tab start.
				elseif ( $active_tab1 === 'feespayment' ) {
					$fees_payment = mjschool_get_fees_payment_detailpage( $student_id );
					if ( ! empty( $fees_payment ) ) {
						?>
						<div class="mjschool-popup-bg">
							<div class="mjschool-overlay-content">
								<div class="modal-content">
									<div class=" invoice_data"></div>
									<div class="mjschool-category-list">
									</div>
								</div>
							</div>
						</div>

						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-feespayment-list-detailpage" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Fees Type', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Total Amount', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Paid Amount', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Due Amount', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Payment Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Start Year To End Year', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $fees_payment ) ) {
											foreach ( $fees_payment as $retrieved_data ) {
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
												<?php
												$Due_amt    = $retrieved_data->total_amount - $retrieved_data->fees_paid_amount;
												$due_amount = number_format( $Due_amt, 2, '.', '' );
												?>
												<tr>
													<td class="mjschool-cursor-pointer mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
														<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-payment.png"); ?>" class="mjschool-massage-image">
														</p>
													</td>
													<td class="mjschool-cursor-pointer">
														<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=view_fesspayment&idtest='.rawurlencode( mjschool_encrypt_id( $retrieved_data->fees_pay_id ) ).'&view_type=view_payment'));?>">
															<?php
															$fees_id   = explode( ',', $retrieved_data->fees_id );
															$fees_type = array();
															foreach ( $fees_id as $id ) {
																$fees_type[] = mjschool_get_fees_term_name( $id );
															}
															echo esc_html( implode( ' , ', $fees_type ) );
															?>
														</a> 
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Fees Type', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
													<td class="name">
														<?php
														if ( $retrieved_data->class_id ) {
															echo esc_html( mjschool_get_class_section_name_wise( $retrieved_data->class_id, $retrieved_data->section_id ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->total_amount, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Total Amount', 'mjschool' ); ?>"></i></td>
													<td class="department"><?php echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->fees_paid_amount, 2, '.', '' ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Paid Amount', 'mjschool' ); ?>"></i></td>
													<?php
													$Due_amt    = $retrieved_data->total_amount - $retrieved_data->fees_paid_amount;
													$due_amount = number_format( $Due_amt, 2, '.', '' );
													?>
													<td><?php echo esc_html( mjschool_currency_symbol_position_language_wise( $due_amount ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Due Amount', 'mjschool' ); ?>"></i></td>
													<td>
														<?php
														$mjschool_get_payment_status = mjschool_get_payment_status( $retrieved_data->fees_pay_id );
														if ( $mjschool_get_payment_status === 'Not Paid' ) {
															echo "<span class='mjschool-red-color'>";
														} elseif ( $mjschool_get_payment_status === 'Partially Paid' ) {
															echo "<span class='mjschool-purpal-color'>";
														} else {
															echo "<span class='mjschool-green-color'>";
														}
														echo esc_html( $mjschool_get_payment_status );
														echo '</span>';
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Payment Status', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( $retrieved_data->start_year ) . '-' . esc_html( $retrieved_data->end_year ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Year To End Year', 'mjschool' ); ?>"></i></td>
												</tr>
												<?php
												++$i;
											}
										}
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
						</div>
						<?php
					} else {
						$page_1       = 'feepayment';
						$feepayment_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
						if ( $mjschool_role === 'administrator' || $feepayment_1['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_fees_payment&tab=addpaymentfee' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				} elseif ( $active_tab1 === 'attendance' ) {
					$attendance_list = mjschool_monthly_attendence( $student_id );
					if ( ! empty( $attendance_list ) ) {
						?>

						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-attendance-list-detail-page" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Attendance Date', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Day', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Attendance By', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Attendance With QR Code', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Comment', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i    = 0;
										$srno = 1;
										if ( ! empty( $attendance_list ) ) {
											foreach ( $attendance_list as $retrieved_data ) {
												$class_section_sub_name = mjschool_get_class_section_subject( $retrieved_data->class_id, $retrieved_data->section_id, $retrieved_data->sub_id );
												$created_by             = get_userdata( $retrieved_data->attend_by );
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
														<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance'));?>">
															<p class="mjschool-remainder-title-pr Bold mjschool-prescription-tag <?php echo esc_attr($color_class_css); ?>">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-attendance.png"); ?>" class="mjschool-massage-image">
															</p>
														</a>
													</td>
													<td class="department"><a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance' ));?>"><?php echo esc_html( mjschool_student_display_name_with_roll( $retrieved_data->user_id ) ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
													<td >
														<?php echo wp_kses_post( $class_section_sub_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<?php
													$curremt_date = mjschool_get_date_in_input_box( $retrieved_data->attendance_date );
													$day          = date( 'l', strtotime( $curremt_date ) );
													?>
													<td class="name"><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->attendance_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance Date', 'mjschool' ); ?>"></i></td>
													<td class="department">
														<?php echo esc_html( $day ); ?><i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Day', 'mjschool' ); ?>"></i>
													</td>
													<td class="name">
														<?php $status_color = mjschool_attendance_status_color( $retrieved_data->status ); ?>
														<span style="color:<?php echo esc_attr( $status_color ); ?>;">
															<?php echo esc_html( $retrieved_data->status ); ?>
														</span>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance Status', 'mjschool' ); ?>"></i>
													</td>
													<?php
													$comment     = $retrieved_data->comment;
													$comment_out = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
													?>
													<td class="name">
														<?php echo esc_html( $created_by->display_name ); ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Attendance By', 'mjschool' ); ?>"></i>
													</td>
													<td class="mjschool-width-20px">
														<?php
														if ( $retrieved_data->attendence_type === 'QR' ) {
															esc_html_e( 'Yes', 'mjschool' );
														} else {
															esc_html_e( 'No', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Attendance With QR Code', 'mjschool' ); ?>"></i>
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
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->comment ) ) { echo esc_attr( $retrieved_data->comment ); } else { esc_attr_e( 'Comment', 'mjschool' ); } ?>"></i>
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
						$page_1        = 'attendance';
						$fattendance_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
						if ( $mjschool_role === 'administrator' || $fattendance_1['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_attendence&tab=student_attendance&tab1=subject_attendence' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				} elseif ( $active_tab1 === 'leave_list' ) {
					$obj_leave  = new Mjschool_Leave();
					$leave_data = $obj_leave->mjschool_get_single_user_leaves( $student_id );
					if ( ! empty( $leave_data ) ) {
						?>

						<div class="table-responsive"><!-- Table responsive div start. -->
							<form id="mjschool-common-form" name="mjschool-common-form" method="post">
								<table id="leave_list" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Leave Type', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Leave Duration', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Reason', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										foreach ( $leave_data as $retrieved_data ) {
											$leave_id = mjschool_encrypt_id( $retrieved_data->id );
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
													<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-leave.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
													</p>
												</td>
												<td>
													<?php
													$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
													if ( $sname != '' ) {
														echo esc_html( $sname );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
												</td>
												<td class="name">
													<?php
													$class_id   = get_user_meta( $retrieved_data->student_id, 'class_name', true );
													$section_id = get_user_meta( $retrieved_data->student_id, 'class_section', true );
													$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
													if ( ! empty( $classname ) ) {
														echo esc_html( $classname );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
												</td>
												<td><?php echo esc_html( get_the_title( $retrieved_data->leave_type ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Type', 'mjschool' ); ?>"></i></td>
												<td>
													<?php
													$duration = mjschool_leave_duration_label( $retrieved_data->leave_duration );
													echo esc_html( $duration );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Duration', 'mjschool' ); ?>"></i>
												</td>
												<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->start_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Leave Start Date', 'mjschool' ); ?>"></i></td>
												<td>
													<?php
													if ( ! empty( $retrieved_data->end_date ) ) {
														echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->end_date ) );
													} else {
														esc_html_e( 'Not Provided', 'mjschool' );
													}
													?>
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
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $retrieved_data->status_comment ) ) { echo esc_attr( $retrieved_data->status_comment ); } else { esc_attr_e( 'Status', 'mjschool' ); } ?>"></i>
												</td>
												<td>
													<?php
													$comment = $retrieved_data->reason;
													$reason  = strlen( $comment ) > 30 ? substr( $comment, 0, 30 ) . '...' : $comment;
													echo esc_html( $reason );
													?>
													<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php if ( ! empty( $comment ) ) { echo esc_attr( $comment ); } else { esc_attr_e( 'Reason', 'mjschool' ); } ?>"></i>
												</td>
												<td class="action">
													<div class="mjschool-user-dropdown">
														<ul  class="mjschool_ul_style">
															<li >
																<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																</a>
																<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																	<?php
																	if (($retrieved_data->status != 'Approved' ) ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="#" leave_id="<?php echo esc_attr($retrieved_data->id) ?>" class="mjschool-float-left-width-100px leave-approve">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-leave-approved.png"); ?>" class="mjschool_height_17px">&nbsp;&nbsp;<?php esc_html_e( 'Approve', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	}
																	if (($retrieved_data->status != 'Rejected' ) ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="#" leave_id="<?php echo esc_attr($retrieved_data->id) ?>" class="leave-reject mjschool-float-left-width-100px">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-leave-rejected.png"); ?>" class="mjschool_height_17px">&nbsp;&nbsp;<?php esc_html_e( 'Reject', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	}
																	if ($mjschool_role === 'administrator' ) {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=add_leave&action=edit&leave_id='.rawurlencode($leave_id).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) );?>" class="mjschool-float-left-width-100px">
																				<i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																			</a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&tab=studentlist&action=delete&leave_id='.rawurlencode($leave_id).'&student_id_action='.rawurlencode($_REQUEST["student_id"]).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) );?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																				<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?>
																			</a>
																		</li>
																		<?php
																	} else {
																		?>
																		<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?dashboard=mjschool_user&page=leave&tab=add_leave&action=edit&leave_id='.rawurlencode($leave_id).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) );?>" leave_id="'.$retrieved_data->id.'" class="mjschool-float-left-width-100px leave-reject">
																				<i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																			</a>
																		</li>
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?dashboard=mjschool_user&page=leave&tab=leave_list&action=delete&leave_id='.rawurlencode($leave_id).'&_wpnonce_action='.rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) );?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
																				<i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> 
																			</a>
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
											$i++;
										}
										?>
									</tbody>
								</table>
							</form>
						</div><!--------- Table responsive div end. ------->
						<?php
					} else {
						?>
						<div class="mjschool-no-data-list-div">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_leave&tab=add_leave' ) ); ?>">
								<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
							</a>
							
							<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
								<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
							</div>
						</div>
						<?php
					}
				}
				// hallticket tab start
				elseif ( $active_tab1 === 'hallticket' ) {
					$hall_ticket = mjschool_hall_ticket_list( $student_id );
					if ( ! empty( $hall_ticket ) ) {
						?>

						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-hall-ticket-detailpage" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Hall Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Exam Term', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Exam Start To End Date', 'mjschool' ); ?> </th>
											<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $hall_ticket ) ) {
											foreach ( $hall_ticket as $retrieved_data ) {
												$exam_data  = mjschool_get_exam_by_id( $retrieved_data->exam_id );
												$start_date = $exam_data->exam_start_date;
												$end_date   = $exam_data->exam_end_date;
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
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
														</p>
													</td>
													<td><?php echo esc_html( mjschool_get_hall_name($retrieved_data->hall_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Hall Name', 'mjschool' ); ?>"></i></td>
													<td class="department"><?php echo esc_html( mjschool_student_display_name_with_roll($retrieved_data->user_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>
													<td class="name"><?php echo esc_html( mjschool_get_exam_name_id($retrieved_data->exam_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Name', 'mjschool' ); ?>"></i></td>
													<td class="department"><?php echo esc_html( get_the_title($exam_data->exam_term ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Term', 'mjschool' ); ?>"></i></td>
													<td class="department"><?php echo esc_html( mjschool_get_date_in_input_box($start_date ) ); ?><?php esc_html_e( " To ", "mjschool" ); ?><?php echo esc_html( mjschool_get_date_in_input_box($end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Exam Start To End Date', 'mjschool' ); ?>"></i></td>
													<td class="action">
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&student_exam_receipt=student_exam_receipt&student_id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->user_id ) ).'&exam_id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->exam_id ) ) ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fas fa-print"> </i><?php esc_html_e( 'Hall Ticket Print', 'mjschool' ); ?></a>
																		</li>
																		<?php
																		if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
																			$pdf_name = $retrieved_data->user_id . '_' . $retrieved_data->exam_id;
																			if ( isset( $_POST['download_app_pdf'] ) ) {
																				$file_path = content_url() . '/uploads/exam_receipt/' . $pdf_name . '.pdf';
																				if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																					unlink( $file_path ); // Delete the file.
																				}
																				$generate_pdf = mjschool_generate_exam_receipt_mobile_app( $retrieved_data->user_id, $retrieved_data->exam_id, $pdf_name );
																				wp_safe_redirect( $file_path );
																				die();
																			}
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<form name="app2_pdf" action="" method="post"  class="mjschool-float-left-width-100px">
																					<button type="submit" name="download_app_pdf" class="mjschool-float-left-width-100px mjschool-hall-ticket-pdf-button">
																						<span class="mjschool-hall-ticket-pdf-button-span"><i class="fas fa-print mjschool-hall-ticket-pdf-icon"></i> <?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?></span>
																					</button>
																				</form>
																			</li>
																			<?php
																		} else {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student&student_exam_receipt_pdf=student_exam_receipt_pdf&student_id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->user_id ) ).'&exam_id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->exam_id ) ) ) ); ?>" target="_blank" class="mjschool-float-left-width-100px"><i class="fas fa-print"> </i><?php esc_html_e( 'Hall Ticket PDF', 'mjschool' ); ?></a>
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
										}
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
						</div>
						<?php
					} else {
						$page_1      = 'exam_hall';
						$exam_hall_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
						if ( $mjschool_role === 'administrator' || $exam_hall_1['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<?php $nonce = wp_create_nonce( 'mjschool_exam_hall_tab' ); ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hall&tab=exam_hall_receipt&_wpnonce='.$nonce ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} else {
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				// Homework tab start.
				elseif ( $active_tab1 === 'homework' ) {
					?>
					<div class="mjschool-popup-bg">
						<div class="mjschool-overlay-content">
							<div class="modal-content">
								<div class="view_popup"></div>
							</div>
						</div>
					</div>
					<?php
					$student_homework = mjschool_student_homework_detail( $student_id );
					if ( ! empty( $student_homework ) ) {
						?>
						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-homework-detailpage" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Homework Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submission Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Submitted Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Evaluate Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Marks', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Marks Obtained', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Status', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $student_homework ) ) {
											foreach ( $student_homework as $retrieved_data ) {
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
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-homework.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
														</p>
													</td>
													<td class="mjschool-cursor-pointer">
														<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->homework_id ) ) ) ); ?>">
															<?php echo esc_html( $retrieved_data->title ); ?>
														</a>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Homework Title', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( mjschool_get_class_name( $retrieved_data->class_name ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_single_subject_name( $retrieved_data->subject ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject Name', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->created_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Homework Date', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->submition_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submission Date', 'mjschool' ); ?>"></i></td>
													<?php
													if ( $retrieved_data->uploaded_date === 0000 - 00 - 00 ) {
														?>
														<td><?php esc_html_e( 'Not Provided', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td> 
														<?php
													} else {
														?>
														<td><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->uploaded_date ) ); ?>  <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Submitted Date', 'mjschool' ); ?>"></i></td>
														<?php
													}
													?>
													<td>
														<?php
														if ( ! empty( $retrieved_data->evaluate_date ) ) {
															echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->evaluate_date ) );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Evaluate Date', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->marks ) ) {
															echo esc_html( $retrieved_data->marks );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php
														if ( ! empty( $retrieved_data->obtain_marks ) ) {
															echo esc_html( $retrieved_data->obtain_marks );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Marks', 'mjschool' ); ?>"></i>
													</td>
													<?php
													if ( $retrieved_data->status === 1 ) {
														if ( date( 'Y-m-d', strtotime( $retrieved_data->uploaded_date ) ) <= $retrieved_data->submition_date ) {
															?>
															<td>
																<label class="mjschool-homework-submitted"><?php esc_html_e( 'Submitted', 'mjschool' ); ?></label>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														} else {
															?>
															<td>
																<label class="mjschool-purpal-color"><?php esc_html_e( 'Late-Submitted', 'mjschool' ); ?></label>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
															</td>
															<?php
														}
													} elseif ( $retrieved_data->status === 2 ) {
														?>
														<td><label class="mjschool-homework-evaluated"><?php esc_html_e( 'Evaluated', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i></td>
														<?php
													} else {
														?>
														<td>
															<label class="mjschool-homework-pending"><?php esc_html_e( 'Pending', 'mjschool' ); ?> </label>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Status', 'mjschool' ); ?>"></i>
														</td>
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
																		<li class="mjschool-float-left-width-100px">
																			<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=view_homework&id='.rawurlencode( mjschool_encrypt_id( $retrieved_data->homework_id ) ) ) ); ?>" class="mjschool-float-left-width-100px" type="Homework_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
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
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
						</div>
						<?php
					} else {
						$page_1     = 'homework';
						$homework_1 = mjschool_get_user_role_wise_filter_access_right_array( $page_1 );
						if ( $mjschool_role === 'administrator' || $homework_1['add'] === '1' ) {
							 ?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_student_homewrok&tab=addhomework' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} 
						else 
						{
							?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				// Issuebooks tab start.
				elseif ( $active_tab1 === 'issuebook' ) {
					$mjschool_obj_lib = new Mjschool_Library();
					$student_issuebook = mjschool_student_issuebook_detail( $student_id );
					if ( ! empty( $student_issuebook ) ) {
						?>
						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-issuebook-detailpage" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Book Title', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Issue Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Expected Return Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Time Period', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $student_issuebook ) ) {
											foreach ( $student_issuebook as $retrieved_data ) {
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
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-library.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
														</p>
													</td>
													<td class="department"><?php echo esc_html( mjschool_student_display_name_with_roll($retrieved_data->student_id ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i></td>	
													<td><?php echo esc_html( stripslashes( $mjschool_obj_lib->mjschool_get_book_name($retrieved_data->book_id ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Book Title', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->issue_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Issue Date', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_date_in_input_box($retrieved_data->end_date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Expected Return Date', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( get_the_title($retrieved_data->period ) ); ?><?php echo ' ' . esc_attr__( 'Days', 'mjschool' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Time Period', 'mjschool' ); ?>"></i></td>
												</tr>
												<?php
												$i++;
											}
										}
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
						</div>
						<?php
					} else {
						$page_1 = 'mjschool_library';
						$library_1 = mjschool_get_user_role_wise_filter_access_right_array($page_1);
						if ($mjschool_role === 'administrator' || $library_1['add'] === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_library&tab=issuebook' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?></label>
								</div>
							</div>
							<?php
						} else {
							?>		
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				if ( $active_tab1 === 'exam_result' ) {
					?>
					<form method="post">
						<div class="row">
							<div class="col-md-3 input mjschool-responsive-months mjschool-dashboard-payment-report-padding">
								<label class="ml-1 mjschool-custom-top-label top" for="year"><?php esc_html_e( 'Exam Year', 'mjschool' ); ?></label>
								<select name="year" id="year" class="mjschool-line-height-30px form-control mjschool-dash-year-load">
									<?php
									$current_year  = date( 'Y' );
									$min_year      = $current_year - 10;
									$selected_year = isset( $_POST['year'] ) ? intval( $_POST['year'] ) : date( 'Y' );
									for ( $i = $current_year; $i >= $min_year; $i-- ) {
										$year_array[ $i ] = $i;
										$selected         = ( $selected_year === $i ? ' selected' : '' );
										echo '<option value="' . esc_attr( $i ) . '"' . esc_attr( $selected ) . '>' . esc_html( $i ) . '</option>' . "\n";
									}
									?>
								</select>
							</div>
							<div class="col-md-2">        	
								<input type="submit" value="<?php esc_attr_e( 'GO', 'mjschool' ); ?>" name="save_latter" class="btn btn-success mjschool-save-btn" />
							</div> 
						</div>
					</form>
					<?php
					$obj_mark    = new Mjschool_Marks_Manage();
					$uid         = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['student_id'])) ) );
					$mjschool_user        = get_userdata( $uid );
					$user_meta   = get_user_meta( $uid );
					$total       = 0;
					$grade_point = 0;
					$exam_ids = mjschool_get_manage_marks_exam_id_using_student_id($uid);
					$class_ids = mjschool_get_manage_marks_class_id_using_student_id($uid);
					$subject_ids = mjschool_get_manage_marks_subject_id_using_student_id($uid);
					// Yearly report.
					$all_exam = array();
					if ( ! empty( $exam_ids ) ) {
						$exam_results = mjschool_get_exam_details_by_ids($exam_ids);
						$merge_exam_results = array();
						$class_section_pairs = mjschool_get_class_section_pairs_by_student($uid);
						foreach ( $class_section_pairs as $pair ) {
							$class_id   = intval( $pair->class_id );
							$section_id = intval( $pair->section_id );
							$results = mjschool_get_exam_merge_settings($class_id, $section_id, 'enable');
							
							if ( ! empty( $results ) ) {
								$merge_exam_results = array_merge( $merge_exam_results, $results );
							}
						}
						// 3. Merge both.
						$all_exam = array_merge( $exam_results, $merge_exam_results );
						$all_exam = array_filter(
							$all_exam,
							function ( $exam ) use ( $selected_year ) {
								if ( $exam->source_table === 'mjschool_exam' ) {
									$exam_year = (int) date( 'Y', strtotime( $exam->exam_start_date ) );
								} else {
									$exam_year = (int) date( 'Y', strtotime( $exam->created_at ) );
								}
								return $exam_year === (int) $selected_year;
							}
						);
					}
					$all_subjects = array();
					if ( ! empty( $class_ids ) ) {
						foreach ( $class_ids as $class_id ) {
							$subjects = $obj_mark->mjschool_student_subject_by_class( $class_id );
							if ( ! empty( $subjects ) ) {
								$all_subjects = array_merge( $all_subjects, $subjects );
							}
						}
					}
					if ( ! empty( $all_exam ) ) {
						?>
						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-messages-detailpage-for-exam" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Exam Name', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Start Date', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'End Date', 'mjschool' ); ?></th>
											<th class="mjschool-exam-exam"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $all_exam ) ) {
											foreach ( $all_exam as $retrieved_data ) {
												if ( $retrieved_data->source_table === 'mjschool_exam' ) {
													$exam_id         = $retrieved_data->exam_id;
													$exam_name       = $retrieved_data->exam_name;
													$exam_start_date = mjschool_get_date_in_input_box( $retrieved_data->exam_start_date );
													$exam_end_date   = mjschool_get_date_in_input_box( $retrieved_data->exam_end_date );
													$class_id = mjschool_get_class_id_by_exam_and_student($exam_id, $uid);
													// Get subject list for this class.
													$subjects = $obj_mark->mjschool_student_subject_by_class( $class_id );
												} else {
													$exam_name       = $retrieved_data->merge_name;
													$exam_start_date = 'Not Provided';
													$exam_end_date   = 'Not Provided';
													$class_id        = $retrieved_data->class_id;
													$section_id      = $retrieved_data->section_id;

												}
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
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-exam-hall.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
														</p>
													</td>
													<td class="subject_name">
														<?php
														$max_length      = 30;
														$full_exam_name  = esc_attr( $exam_name );
														$short_exam_name = ( strlen( $exam_name ) > $max_length ) ? substr( $exam_name, 0, $max_length ) . '...' : $exam_name;
														?>
														<label  data-toggle="tooltip" title="<?php echo esc_attr( $full_exam_name ); ?>">
															<?php echo esc_html( $short_exam_name ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip"></i>
														</label>
													</td>
													<td class="department mjschool-width-15px">
														<label ><?php echo esc_attr( $exam_start_date ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Start Date', 'mjschool' ); ?>"></i></label>
													</td>
													<td class="department mjschool-width-15px">
														<label ><?php echo esc_html( $exam_end_date ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'End Date', 'mjschool' ); ?>"></i></label>
													</td>
													<td class="department">
														<?php
														if ( $retrieved_data->source_table === 'mjschool_exam' ) {
															$main_marks = array();
															foreach ( $subjects as $sub ) {
																$subject_id   = $sub->subid;
																$subject_name = $sub->sub_name;
																// Now call with single class_id, subject_id, exam_id.
																$new_marks = $obj_mark->mjschool_get_marks( $exam_id, $class_id, $subject_id, $uid );
																if ( $new_marks != '0' )
																{
																	$main_marks[] = $new_marks;
																}
															}
															if ( ! empty( $main_marks ) ) {
																?>
																<div class="col-md-12 row mjschool-padding-left-50px  mjschool-view-result">
																	<?php
																	if ( isset( $_REQUEST['web_type'] ) && sanitize_text_field(wp_unslash($_REQUEST['web_type'])) === 'wpschool_app' ) {
																		$pdf_name  = $uid . '_' . $exam_id;
																		$file_path = content_url() . '/uploads/result/' . $pdf_name . '.pdf';
																		if ( isset( $_POST['download_app_pdf'] ) ) {
																			$file_path = content_url() . '/uploads/result/' . $pdf_name . '.pdf';
																			if ( file_exists( ABSPATH . str_replace( content_url(), 'wp-content', $file_path ) ) ) {
																				unlink( $file_path ); // Delete the file.
																			}
																			$generate_pdf = mjschool_generate_result_for_mobile_app( $uid, $exam_id, $pdf_name, $class_id, $section_id );
																			wp_safe_redirect( $file_path );
																			die();
																		}
																		 ?>
																		<div class="col-md-2 mjschool-width-50px mjschool-marks-block">
																			<form name="app1_pdf" action="" method="post">
																				<button data-toggle="tooltip" name="download_app_pdf"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>"></button>
																			</form>
																		</div>
																		<?php
																	}
																	else
																	{	
																		?>
																		<div class="col-md-2 mjschool-width-50px mjschool-marks-block  mjschool_margin_right_15px">
																			<a href="#" student_id="<?php echo esc_js(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_js(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_js(mjschool_encrypt_id($section_id ) ); ?>" exam_id="<?php echo esc_js(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="pdf" class="mjschool-float-right show-popup-teacher-details" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>"></a>
																		</div>
																		<?php 
																	} 
																	?>
																	<div class="col-md-2 mjschool-width-50px mjschool-rtl-margin-left-20px">
																		<a href="#" student_id="<?php echo esc_js(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_js(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_js(mjschool_encrypt_id($section_id ) ); ?>" exam_id="<?php echo esc_js(mjschool_encrypt_id($exam_id ) ); ?>" typeformat="print" class="mjschool-float-right show-popup-teacher-details">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>">
																		</a>
																	</div>
																</div>
																<?php
															} 
															else 
															{
																esc_html_e( 'No Result Available.', 'mjschool' );
															}
														}
														else
														{
															?>
															<div class="col-md-12 row mjschool-padding-left-50px  mjschool-view-result">
																<div class="col-md-2 mjschool-width-50px mjschool-marks-block mjschool_margin_right_15px">
																	<a student_id="<?php echo esc_js(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_js(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_js(mjschool_encrypt_id($section_id ) ); ?>" merge_id="<?php echo esc_js(mjschool_encrypt_id($retrieved_data->id ) ); ?>" typeformat="pdf" href="#" class="mjschool-float-right show-popup-teacher-details-marge" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-pdf.png"); ?>"></a>
																</div>
																<div class="col-md-2 mjschool-width-50px mjschool-rtl-margin-left-20px">
																	<a student_id="<?php echo esc_js(mjschool_encrypt_id($uid ) ); ?>" class_id="<?php echo esc_js(mjschool_encrypt_id($class_id ) ); ?>" section_id="<?php echo esc_js(mjschool_encrypt_id($section_id ) ); ?>" merge_id="<?php echo esc_js(mjschool_encrypt_id($retrieved_data->id ) ); ?>" typeformat="print" href="#" class="mjschool-float-right show-popup-teacher-details-marge" target="_blank"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-print.png"); ?>"></a>
																</div>
															</div>
															<?php
														}
														?>
													</td>
												</tr>
												<?php
												$i++;
											}
										}
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
							<div class="mjschool-panel-white mjschool_table_transform_translate" id="printPopupModal">
								<div class="modal-header mjschool-model-header-padding mjschool-dashboard-model-header">
									<a href="javascript:void(0);" class="close-btn badge badge-success pull-right mjschool-dashboard-popup-design"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></a>
									<h4 id="myLargeModalLabel" class="modal-title"><?php echo esc_html( mjschool_get_user_name_by_id($uid ) ); ?>'s <?php esc_html_e( 'Result', 'mjschool' ) ?></h4>
								</div>
								<h4>Enter Teacher Comment</h4>
								<textarea id="teacherComment" rows="4" class="mjschool_width_100px"></textarea>
								<br><br>
								<div class="col-md-12 input mjschool-single-select">
									<label class="ml-1 mjschool-custom-top-label top" for="student_teacher_idid"><?php esc_html_e( 'Select Teacher','mjschool' );?></label>
									<select name="teacher_id" id="teacher_id" class="form-control mjschool-max-width-100px validate[required]">
										<option value=""><?php esc_html_e( 'Select Teacher','mjschool' ); ?></option>
										<?php mjschool_get_teacher_list_selected($selected_teacher); ?>
									</select>
								</div>
								<button onclick="submitPrint()">Print</button>
								<button onclick="closePrintPopup()">Cancel</button>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="mjschool-calendar-event-new">
							<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
						</div>
						<?php 
					}
				}
				// Message tab start.
				if ( $active_tab1 === 'message' ) {
					$student_message = mjschool_message_detail( $student_id );
					if ( ! empty( $student_message ) ) {
						?>
						<div class="table-div"><!-- Panel body div start. -->
							<div class="table-responsive"><!-- Table responsive div start. -->
								<table id="mjschool-messages-detailpage" class="display" cellspacing="0" width="100%">
									<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
										<tr>
											<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Sender', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Subject', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
											<th><?php esc_html_e( 'Date', 'mjschool' ); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 0;
										if ( ! empty( $student_message ) ) {
											foreach ( $student_message as $retrieved_data ) {
												$sender_id = $retrieved_data->sender;
												$sender    = mjschool_get_display_name( $sender_id );
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
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-message-chat.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px">
														</p>
													</td>
													<td class="subject_name">
														<label ><?php echo esc_html( $sender ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Sender', 'mjschool' ); ?>"></i></label>
													</td>
													<td class="department">
														<label ><?php echo esc_html( $retrieved_data->subject); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Subject', 'mjschool' ); ?>"></i></label>
													</td>
													<?php
													$massage = $retrieved_data->message_body;
													$massage_out = strlen( $massage ) > 30 ? substr( $massage, 0, 30 ) . "..." : $massage;
													?>
													<td class="specialization">
														<label ><?php echo esc_html( $massage_out); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php echo esc_attr( $massage ); ?>"></i></label>
													</td>
													<td class="department mjschool-width-15px">
														<label ><?php echo esc_html( mjschool_get_date_in_input_box( $retrieved_data->date ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Date', 'mjschool' ); ?>"></i></label>
													</td>
												</tr>
												<?php
												$i++;
											}
										}
										?>
									</tbody>
								</table>
							</div><!-- Table responsive div end. -->
						</div>
						<?php
					} else {
						if ($mjschool_role === 'management' || $mjschool_role === 'administrator' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_message&tab=compose' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
								</div>
							</div>
							<?php
						} else {
							 ?>
							<div class="mjschool-calendar-event-new">
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				// Message Tab End.
				?>
			</div><!-- End panel body div.-->
		</section>
		<!-- Detail page body content section end. -->
	</div>
</div>
<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}
?>