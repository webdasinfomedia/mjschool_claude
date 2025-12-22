<?php 
/**
 * The admin view page for displaying detailed student admission information.
 *
 * This file handles the display of student details, including profile image, contact
 * information, address, sibling information, and edit/approval actions for the
 * Mjschool Admission module.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/admission
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'view_action' ) ) {
	$student_id                 = intval(mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['id'] ))));
	$active_tab1                = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
	$student_data               = get_userdata( $student_id );
	$user_meta                  = get_user_meta( $student_id, 'parent_id', true );
	$mjschool_custom_field_obj  = new Mjschool_Custome_Field();
	$sibling_information_value  = str_replace( '"[', '[', $student_data->sibling_information );
	$sibling_information_value1 = str_replace( ']"', ']', $sibling_information_value );
	$sibling_information        = json_decode( $sibling_information_value1 );
	?>
	<div class="mjschool-panel-body mjschool-view-page-main"><!-- START PANEL BODY DIV.-->
		<div class="content-body">
			<!-- Detail Page Header Start. -->
			<section id="mjschool-user-information" class="mjschool-view-page-header-bg">
				<div class="mjschool-view-page-header-bg">
					<div class="row">
						<div class="col-xl-10 col-md-9 col-sm-10">
							<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
								<?php
								$umetadata = mjschool_get_user_image( $student_data->ID );
								?>
								<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $userimage ) ) { echo esc_url($umetadata); } else { echo esc_url( get_option( 'mjschool_student_thumb_new' ) ); } ?>">
								
								<div class="row mjschool-profile-user-name">
									<div class="mjschool-float-left mjschool-view-top1">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<label class="mjschool-view-user-name-label"><?php echo esc_html( $student_data->display_name ); ?></label>
											<?php
											if ( $user_access_edit === '1' ) {
												$admission_id = mjschool_encrypt_id( $student_data->ID );
												?>
												<div class="mjschool-view-user-edit-btn">
													<a class="mjschool-color-white mjschool-margin-left-2px"  href="<?php echo esc_url( '?page=mjschool_admission' . '&tab=mjschool-admission-form' . '&action=edit' . '&id=' . $admission_id . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) ); ?>">					
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
													</a>
												</div>
												<?php
											}
											?>
											<div class="mjschool-view-user-edit-btn">
												<a class="mjschool-color-white mjschool-margin-left-2px show-admission-popup" href="<?php echo esc_url( '?page=mjschool_admission&tab=admission_list&action=approve&id=' . $student_data->ID ); ?>" student_id="<?php echo esc_attr( $student_data->ID ); ?>"> <img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-approve.png"); ?>"></a>
											</div>
										</div>
										<div class=" col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;
												<label> <?php echo esc_html( $student_data->mobile_number); ?></label>
											</div>
										</div>
									</div>
								</div>
								<div id="mjschool-res-add-width" class="row">
									<div class="col-xl-12 col-md-12 col-sm-12">
										<div class="mjschool-view-top2">
											<div class="row mjschool-view-user-doctor-label">
												<div class="col-md-12 mjschool-address-student-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page">
													<?php echo esc_html( $student_data->address); ?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-md-3 col-sm-2 mjschool-group-thumbs">
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
						</div>
					</div>
				</div>
			</section>
			<!-- Detail Page Header End. -->
			<!-- Detail Page Body Content Section.  -->
			<section id="body_area" class="body_areas">
				<div class="mjschool-panel-body"><!-- START PANEL BODY DIV.-->
					<?php
					// general tab start.
					if ( $active_tab1 === 'general' ) {
						?>
						<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?></label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $student_data->user_email ) ) {
									$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->user_email ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Admission Number', 'mjschool' ); ?></label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $student_data->admission_no ) ) {
									$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->admission_no ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Admission Date', 'mjschool' ); ?></label><br />
								<?php
								$birth_date      = $student_data->admission_date;
								$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
								if ( $user_access_edit === '1' && $is_invalid_date ) {
									$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->admission_date ) ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Previous School', 'mjschool' ); ?></label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $student_data->preschool_name ) ) {
									$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->preschool_name ) ) { echo esc_html( $student_data->preschool_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
								<?php } ?>
							</div>
						</div>
						<!-- student Information div start. -->
						<div class="row mjschool-margin-top-20px">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs mjschool-rtl-custom-padding-0px">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Student Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Full Name', 'mjschool' ); ?> </label> <br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->display_name ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->display_name ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?></label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->alternet_mobile_number ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->alternet_mobile_number ) ) { ?> +<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp; <?php echo esc_html( $student_data->alternet_mobile_number ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->gender ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels"><?php if ( $student_data->gender === 'male' ) { echo esc_html__( 'Male', 'mjschool' ); } elseif ( $student_data->gender === 'female' ) { echo esc_html__( 'Female', 'mjschool' ); } elseif ( $student_data->gender === 'other' ) { echo esc_html__( 'Other', 'mjschool' ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
												<?php
												$birth_date      = $student_data->birth_date;
												$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
												if ( $user_access_edit === '1' && $is_invalid_date ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_date_in_input_box( $student_data->birth_date ) ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->city ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->city ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->state ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->state ) ) { echo esc_html( $student_data->state ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Zipcode', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $student_data->zip_code ) ) {
													$mjschool_edit_url = admin_url( 'admin.php?page=mjschool_admission&tab=mjschool-admission-form&action=edit&id=' . esc_attr( mjschool_encrypt_id( $student_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $mjschool_edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->zip_code ); ?></label>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
								<?php
								$module = 'admission';
								$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
								?>
								<!-- Sibling Information. -->
								<?php if ( ! empty( $sibling_information[0]->siblingsstudent ) ) { ?>
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Siblings Information', 'mjschool' ); ?> </label>
											<?php
											foreach ( $sibling_information as $value ) {
												$sibling_data = get_userdata( $value->siblingsstudent );
												if ( ! empty( $sibling_data ) ) {
													?>
													<div class="row">
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Name', 'mjschool' ); ?> </label> <br>
															<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( mjschool_student_display_name_with_roll( $sibling_data->ID ) ); ?></label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Sibling Email', 'mjschool' ); ?> </label> <br>
															<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $sibling_data->user_email ); ?></label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Class', 'mjschool' ); ?> </label><br>
															<label class="mjschool-word-break mjschool-text-style-capitalization mjschool-view-page-content-labels"><?php echo esc_html( mjschool_get_class_section_name_wise( $value->siblingsclass, $value->siblingssection ) ); ?></label>
														</div>
														<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br>
															<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $sibling_data->mobile_number ) ) { echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( $sibling_data->mobile_number ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
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
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<?php
									if ( $student_data->parent_status === 'Father' || $student_data->parent_status === 'Both' ) {
										if ( ! empty( $student_data->father_first_name ) ) {
											?>
											<div class="mjschool-guardian-div">
												<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Father Information', 'mjschool' ); ?> </label>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Name', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->fathersalutation ) . ' ' . esc_html( $student_data->father_first_name ) . ' ' . esc_html( $student_data->father_middle_name ) . ' ' . esc_html( $student_data->father_last_name ); ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_email ) ) { echo esc_html( $student_data->father_email ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels font_transfer_capitalize"> <?php if ( ! empty( $student_data->fathe_gender ) ) { if ( $student_data->fathe_gender === 'male' ) { echo esc_html__( 'Male', 'mjschool' ); } elseif ( $student_data->fathe_gender === 'female' ) { echo esc_html__( 'Female', 'mjschool' ); } } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ( $student_data->father_birth_date === '' || $student_data->father_birth_date === '01/01/1970' ) ) { esc_html_e( 'Not Provided', 'mjschool' ); } else { echo esc_html( mjschool_get_date_in_input_box( $student_data->father_birth_date ) ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_address ) ) { echo esc_html( $student_data->father_address ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_state_name ) ) { echo esc_html( $student_data->father_state_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_city_name ) ) { echo esc_html( $student_data->father_city_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_zip_code ) ) { echo esc_html( $student_data->father_zip_code ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_mobile ) ) { echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;<?php echo esc_html( $student_data->father_mobile ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'School Name', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_school ) ) { echo esc_html( $student_data->father_school ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_medium ) ) { echo esc_html( $student_data->father_medium ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Qualification', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->father_education ) ) { echo esc_html( $student_data->father_education ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->fathe_income ) ) { echo esc_html( mjschool_get_currency_symbol() ) . '' . esc_html( $student_data->fathe_income ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Occupation', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->father_occuption ) ) { echo esc_html( $student_data->father_occuption ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															$father_doc      = str_replace( '"[', '[', $student_data->father_doc );
															$father_doc1     = str_replace( ']"', ']', $father_doc );
															$father_doc_info = json_decode( $father_doc1 );
															?>
															<p class="user-info mjschool-view-page-content-labels">
																<?php
																if ( ! empty( $father_doc_info[0]->value ) ) {
																	?>
																	<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $father_doc_info[0]->value ) ); ?>" class="mjschool-status-read btn btn-default"><i class="fa fa-download"></i> <?php if ( ! empty( $father_doc_info[0]->title ) ) { echo esc_html( $father_doc_info[0]->title ); } else { esc_html_e( ' Download', 'mjschool' ); } ?> </a>
																	<?php
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</p>
														</label>
													</div>
												</div>
											</div>
											<br>
											<?php
										}
									}
									if ( $student_data->parent_status === 'Mother' || $student_data->parent_status === 'Both' ) {
										if ( ! empty( $student_data->mother_first_name ) ) {
											?>
											<div class="mjschool-guardian-div">
												<label class="mjschool-view-page-label-heading"><?php esc_html_e( 'Mother Information', 'mjschool' ); ?> </label>
												<div class="row">
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Name', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php echo esc_html( $student_data->mothersalutation ) . ' ' . esc_html( $student_data->mother_first_name ) . ' ' . esc_html( $student_data->mother_middle_name ) . ' ' . esc_html( $student_data->mother_last_name ); ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Email', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_email ) ) { echo esc_html( $student_data->mother_email ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels font_transfer_capitalize"> <?php if ( ! empty( $student_data->mother_gender ) ) { if ( $student_data->mother_gender === 'male' ) { echo esc_html__( 'Male', 'mjschool' ); } elseif ( $student_data->mother_gender === 'female' ) { echo esc_html__( 'Female', 'mjschool' ); } } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?>
														</label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->mother_birth_date ) ) { echo esc_html( mjschool_get_date_in_input_box( $student_data->mother_birth_date ) ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?></label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Address', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_address ) ) { echo esc_html( $student_data->mother_address ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_state_name ) ) { echo esc_html( $student_data->mother_state_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_city_name ) ) { echo esc_html( $student_data->mother_city_name ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_zip_code ) ) { echo esc_html( $student_data->mother_zip_code ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_mobile ) ) { echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;<?php echo esc_html( $student_data->mother_mobile ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'School Name', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->mother_school ) ) { echo esc_html( $student_data->mother_school ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Medium of Instruction', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_medium ) ) { echo esc_html( $student_data->mother_medium ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Qualification', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->mother_education ) ) { echo esc_html( $student_data->mother_education ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Annual Income', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"><?php if ( ! empty( $student_data->mother_income ) ) { echo esc_html( mjschool_get_currency_symbol() ) . '' . esc_html( $student_data->mother_income ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Occupation', 'mjschool' ); ?> </label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php if ( ! empty( $student_data->mother_occuption ) ) { echo esc_html( $student_data->mother_occuption ); } else { esc_html_e( 'Not Provided', 'mjschool' ); } ?> </label>
													</div>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Proof of Qualification', 'mjschool' ); ?></label><br>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															$mother_doc      = str_replace( '"[', '[', $student_data->mother_doc );
															$mother_doc1     = str_replace( ']"', ']', $mother_doc );
															$mother_doc_info = json_decode( $mother_doc1 );
															?>
															<p class="user-info">
																<?php
																if ( ! empty( $mother_doc_info[0]->value ) ) {
																	?>
																	<a download href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $mother_doc_info[0]->value ) ); ?>" class=" btn btn-default"  <?php if ( empty( $mother_doc_info[0] ) ) { ?> disabled <?php } ?>><i class="fas fa-download"></i> <?php if ( ! empty( $mother_doc_info[0]->title ) ) { echo esc_html( $mother_doc_info[0]->title ); } else { esc_html_e( ' Download', 'mjschool' ); } ?></a>
																	<?php
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
															</p>
														</label>
													</div>
												</div>
											</div>
											<?php
										}
									}
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div><!-- END PANEL BODY DIV.-->
			</section>
			<!-- Detail Page Body Content Section End. -->
		</div>
	</div>
	<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}
?>