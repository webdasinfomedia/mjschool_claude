<?php
/**
 * Support Staff Management Page
 *
 * This file handles the listing, adding, editing, and profile viewing for
 * users assigned the 'supportstaff' role in the system dashboard.
 * It includes access control checks and handling of custom user fields.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$role_name         = mjschool_get_user_role( get_current_user_id() );
$active_tab        = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'supportstaff_list';
$obj_admission     = new Mjschool_admission();
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'supportstaff';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
}
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!------------ Panel body. ------------->
	<?php
	// ------------------ SUPPORT STAFF LIST TAB. ----------------//
	if ( $active_tab === 'supportstaff_list' ) {
		?>
		<div class="mjschool-panel-body"><!------------- Panel body. ----------->
			<div class="table-responsive"><!------------- Table responsive. ----------->
				<!----------- SUPPORT STAFF LIST FORM START. ---------->
				<form id="mjschool-common-form" name="mjschool-common-form" method="post">
					<table id="supportstaff_list_front" class="display dataTable mjschool-exam-datatable" cellspacing="0" width="100%">
						<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
							<tr>
								<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Support Staff Name & Email', 'mjschool' ); ?></th>
								<?php
								if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
									?>
									<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?></th>
									<?php
								}
								?>
								<th><?php esc_html_e( 'Gender', 'mjschool' ); ?></th>
								<th><?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></th>
								<?php
								if ( ! empty( $user_custom_field ) ) {
									foreach ( $user_custom_field as $custom_field ) {
										if ( $custom_field->show_in_table === '1' ) {
											?>
											<th><?php echo esc_html( $custom_field->field_label ); ?></th>
											<?php
										}
									}
								}
								if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
									?>
									<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
									<?php
								}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( $school_obj->role === 'supportstaff' ) {
								$own_data = $user_access['own_data'];
								if ( $own_data === '1' ) {
									$user_id        = get_current_user_id();
									$supportstaff   = array();
									$supportstaff[] = get_userdata( $user_id );
								} else {
									$supportstaff = mjschool_get_users_data( 'supportstaff' );
								}
							} else {
								$supportstaff = mjschool_get_users_data( 'supportstaff' );
							}
							if ( ! empty( $supportstaff ) ) {
								foreach ( $supportstaff as $retrieved_data ) {
									?>
									<tr>
										<td class="mjschool-user-image mjschool-width-50px-td">
											<?php
											if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
												?>
												<a  href="?dashboard=mjschool_user&page=supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
												<?php
											} else {
												?>
												<a  href="#">
													<?php
													$uid       = $retrieved_data->ID;
													$umetadata = mjschool_get_user_image( $uid );
													
													if (empty($umetadata ) ) {
														echo '<img src=' . esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) ) . ' height="50px" width="50px" class="img-circle" />';
													} else {
														echo '<img src=' . esc_url($umetadata) . ' height="50px" width="50px" class="img-circle"/>';
													}
													
													?>
												</a>
												<?php
											}
											?>
										</td>
										<td class="name">
											<?php
											if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
												?>
												<a class="mjschool-color-black" href="?dashboard=mjschool_user&page=supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>">
												<?php
											} else {
												?>
												<a  href="#"><?php
											}
											echo esc_html( $retrieved_data->display_name ); ?> </a>
											<br>
											<span class="mjschool-list-page-email"><?php echo esc_attr( $retrieved_data->user_email ); ?></span>
										</td>
										<?php
										if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
											$uid       = $retrieved_data->ID;
											?>
											<td >
												+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ) . ' ' . esc_html( get_user_meta( $uid, 'mobile_number', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i>
											</td>
											<?php
										}
										?>
										<td >
											<?php echo esc_html( ucfirst( get_user_meta( $uid, 'gender', true ) ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Gender', 'mjschool' ); ?>"></i>
										</td>
										<td >
											<?php $birthdate = get_user_meta( $uid, 'birth_date', true ); ?>
											<?php echo esc_html( mjschool_get_date_in_input_box( $birthdate ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Date of Birth', 'mjschool' ); ?>"></i>
										</td>
										<?php
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'supportstaff';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->ID;
													$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
													if ( $custom_field->field_type === 'date' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													} elseif ( $custom_field->field_type === 'file' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																?>
																<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
																<?php
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													} else {
														?>
														<td> 
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( $custom_field_value );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</td>
														<?php
													}
												}
											}
										}
										?>
										<?php
										if ( $role_name === 'supportstaff' || $role_name === 'teacher' ) {
											?>
											<td class="action">
												<div class="mjschool-user-dropdown">
													<ul  class="mjschool_ul_style">
														<li >
															<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
															</a>
															<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																<li class="mjschool-float-left-width-100px">
																	<a href="?dashboard=mjschool_user&page=supportstaff&tab=view_supportstaff&action=view_supportstaff&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																</li>
																<?php
																if ( $user_access['edit'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																		<a href="?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
																?>
																<?php
																if ( $user_access['delete'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a href="?dashboard=mjschool_user&page=supportstaff&tab=supportstaff_list&action=delete&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"> </i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
																	</li>
																	<?php
																}
																?>
															</ul>
														</li>
													</ul>
												</div>
											</td>
											<?php
										}
										?>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</form><!----------- SUPPORT STAFF LIST FORM START. ---------->
			</div><!------------- Table responsive. ----------->
		</div><!------------- Panel body. ----------->
		<?php
	}
	// ----------------- VIEW SUPPIRT STAFF TAB. -----------------//
	if ( $active_tab === 'view_supportstaff' ) {
		$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
		$custom_field_obj = new Mjschool_Custome_Field();
		$staff_data       = get_userdata( mjschool_decrypt_id( wp_unslash($_REQUEST['supportstaff_id']) ) );
		?>
		<div class="mjschool-panel-body mjschool-support-view-page mjschool-view-page-main"><!--  Start panel body div.-->
			<div class="content-body">
				<!-- Detail Page Header Start -->
				<section id="mjschool-user-information" class="mjschool-view-page-header-bg">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<?php
									$umetadata = mjschool_get_user_image( $staff_data->ID );
									?>
									
									<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $umetadata ) ) { echo esc_url($umetadata); } else { echo esc_url( get_option( 'mjschool_supportstaff_thumb_new' ) ); } ?>">
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<label class="mjschool-view-user-name-label"><?php echo esc_html( $staff_data->display_name); ?></label>
												<?php
												if ($user_access['edit'] === '1' ) {
													?>
													<div class="mjschool-view-user-edit-btn">
														<a class="mjschool-color-white mjschool-margin-left-2px" href="?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=<?php echo esc_attr( mjschool_encrypt_id($staff_data->ID ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) );?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
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
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $staff_data->address); ?></label>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-md-3 col-sm-2 mjschool-group-thumbs mjschool-rtl-width-25px">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
							</div>
						</div>
					</div>
				</section>
				<!-- Detail Page Header End. -->
				<!-- Detail Page Body Content Section.  -->
				<section id="body_area" class="body_areas">
					<div class="mjschool-panel-body"><!--  Start panel body div.-->
						<?php
						// General tab start.
						if ( $active_tab1 === 'general' ) {
							?>
							<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php echo esc_html( $staff_data->user_email ); ?> </label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels">+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $staff_data->mobile_number ); ?></label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $staff_data->gender ) ); ?></label>
								</div>
								<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
									<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?> </label><br />
									<label class="mjschool-word-break mjschool-view-page-content-labels"> 
										<?php
										if ( ! empty( $staff_data->birth_date ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $staff_data->birth_date ) );
										} else {
											esc_html_e( 'N/A', 'mjschool' );}
										?>
									</label>
								</div>
							</div>
							<!-- student Information div start.  -->
							<div class="row mjschool-margin-top-20px">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->city ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $staff_data->city ) ) {
																echo esc_html( $staff_data->city );
															} else {
																esc_html_e( 'N/A', 'mjschool' ); 
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->state ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $staff_data->state ) ) {
																echo esc_html( $staff_data->state );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zipcode', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->zip_code ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-word-break mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $staff_data->zip_code ) ) {
																echo esc_html( $staff_data->zip_code );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->alternet_mobile_number ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
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
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Working Hour', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->working_hour ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
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
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</label>
													<?php } ?>
												</div>
												<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-top-15px">
													<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Position', 'mjschool' ); ?> </label><br>
													<?php
													if ( $user_access_edit === '1' && empty( $staff_data->possition ) ) {
														$edit_url = home_url( '?dashboard=mjschool_user&page=supportstaff&tab=addsupportstaff&action=edit&supportstaff_id=' . esc_attr( wp_unslash($_REQUEST['supportstaff_id']) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
														echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
													} else {
														?>
														<label class="mjschool-view-page-content-labels">
															<?php
															if ( ! empty( $staff_data->possition ) ) {
																echo esc_html( $staff_data->possition );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
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
															<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
															<label class="mjschool-label-value">
																<?php
																if ( ! empty( $value->document_file ) ) {
																	?>
																	<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $value->document_file ); ?>" record_id="<?php echo esc_attr( $key ); ?>"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></a> 
																	<?php
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
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
									$module = 'supportstaff';
									$custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
									?>
								</div>
							</div>
							<?php
						}
						?>
					</div><!-- End panel body div.-->
				</section>
				<!-- Detail Page Body Content Section End. -->
			</div><!--  Start content body div.-->
		</div><!--  End panel body div.-->
		<?php
	}
	?>
</div> <!------------ Panel body. ------------->