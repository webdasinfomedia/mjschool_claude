<?php
/**
 * Support Staff View Page (Admin Dashboard).
 *
 * Displays detailed profile information for support staff within the MJSchool plugin.
 * Provides administrators and authorized users with a structured view of staff data including
 * personal details, contact information, position, and uploaded documents.
 *
 * Key Features:
 * - Validates access using WordPress nonces for secure data viewing.
 * - Dynamically retrieves and displays user profile details (name, contact, address, position, etc.).
 * - Integrates with the MJSchool Custom Field system to show additional user-defined data.
 * - Supports profile image handling with fallback to default images when unavailable.
 * - Provides conditional “Edit” buttons for users with proper access rights.
 * - Displays uploaded staff documents with secure download links.
 * - Handles missing data gracefully by showing “Not Provided” placeholders.
 * - Implements full WordPress escaping and sanitization for secure output rendering.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/supportstaff
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'view_action' ) ) {
	$custom_field_obj = new Mjschool_Custome_Field();
	$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
	$staff_data       = get_userdata( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) ) );
	?>
	<div class="mjschool-panel-body mjschool-support-view-page mjschool-view-page-main"><!-- START PANEL BODY DIV-->
		<div class="content-body">
			<!-- Detail page header start. -->
			<section id="mjschool-user-information" class="mjschool-view-page-header-bg">
				<div class="mjschool-view-page-header-bg">
					<div class="row">
						<div class="col-xl-10 col-md-9 col-sm-10">
							<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
								<?php
								$umetadata = mjschool_get_user_image($staff_data->ID);
								?>
								<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $umetadata ) ) { echo esc_url($umetadata); } else { echo esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) ); } ?>">
								<div class="row mjschool-profile-user-name">
									<div class="mjschool-float-left mjschool-view-top1">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<label class="mjschool-view-user-name-label"><?php echo esc_html( $staff_data->display_name); ?></label>
											<?php
											if ($user_access_edit === '1' ) {
												?>
												<div class="mjschool-view-user-edit-btn">
													<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( mjschool_encrypt_id( $staff_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png" )?>">
													</a>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<label><?php echo esc_html( $staff_data->mobile_number); ?></label>
											</div>
										</div>
									</div>
								</div>
								<div class="row mjschool-padding-top-15px-res mjschool-support-staff-address-row">
									<div class="col-xl-12 col-md-12 col-sm-12">
										<div class="mjschool-view-top2">
											<div class="row mjschool-view-user-doctor-label mjschool-support-staff-address-row">
												<div class="col-md-12 mjschool-address-student-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_attr($staff_data->address); ?></label>
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
			<!-- Detail page header end. -->
			<!-- Detail page body content section. -->
			<section id="body_area" class="body_areas">
				<div class="mjschool-panel-body"><!-- Start panel body div. -->
					<?php
					// General tab start.
					if ( $active_tab1 === 'general' ) {
						?>
						<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Email ID', 'mjschool' ); ?></label><br>
								<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php echo esc_html( $staff_data->user_email ); ?> </label>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></label><br>
								<?php
								if ( $user_access_edit === '1' && empty( $staff_data->mobile_number ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_id'] ) ) . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $staff_data->mobile_number ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Gender', 'mjschool' ); ?></label><br>
								<?php
								if ( $user_access_edit === '1' && empty( $staff_data->gender ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . sanitize_text_field( wp_unslash( $_REQUEST['supportstaff_id'] ) ) . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-word-break mjschool-view-page-content-labels"> 
										<?php
										if ( $staff_data->gender === 'male' ) {
											echo esc_attr__( 'Male', 'mjschool' );
										} elseif ( $staff_data->gender === 'female' ) {
											echo esc_attr__( 'Female', 'mjschool' );
										}
										?>
									</label> 
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label><br>
								<?php
								$birth_date      = $staff_data->birth_date;
								$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
								if ( $user_access_edit === '1' && $is_invalid_date ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
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
						</div>
						<!-- Student Information div start. -->
						<div class="row mjschool-margin-top-20px">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'City', 'mjschool' ); ?></label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $staff_data->city ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->city ) ) {
															echo esc_html( $staff_data->city );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' ); 
														} ?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $staff_data->state ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->state ) ) {
															echo esc_html( $staff_data->state );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zipcode', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $staff_data->zip_code ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->zip_code ) ) {
															echo esc_html( $staff_data->zip_code );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $staff_data->alternet_mobile_number ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-word-break mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->alternet_mobile_number ) ) {
															?>
															+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
															<?php
															echo esc_html( $staff_data->alternet_mobile_number );
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
												if ( $user_access_edit === '1' && empty( $staff_data->working_hour ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->working_hour ) ) {
															$working_data = $staff_data->working_hour;
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
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php esc_html_e( 'Position', 'mjschool' ); ?></label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $staff_data->possition ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( sanitize_text_field(wp_unslash($_REQUEST['supportstaff_id'])) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $staff_data->possition ) ) {
															echo esc_html( $staff_data->possition );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
										</div>
										<?php
										if ( ! empty( $staff_data->user_document ) ) {
											?>
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
											<div class="row">
												<?php
												$document_array = json_decode( $staff_data->user_document );
												foreach ( $document_array as $key => $value ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"><?php echo esc_html( $value->document_title ); ?></label><br>
														<label class="mjschool-label-value">
															<?php
															if ( ! empty( $value->document_file ) ) {
																?>
																<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . sanitize_file_name( $value->document_file ) ); ?>" record_id="<?php echo esc_attr( $key ); ?>"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
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
								$module = 'supportstaff';
								$custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
								?>
							</div>
						</div>
						<?php
					}
					?>
				</div><!-- End panel body div. -->
			</section>
			<!-- Detail page body content section end. -->
		</div><!-- Start content body div. -->
	</div><!-- End panel body div. -->
	<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}
?>