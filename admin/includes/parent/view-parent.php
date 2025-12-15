<?php
/**
 * Parent Detail View Page Template.
 *
 * This file displays the detailed view of a parent user, including general information, 
 * contact details, documents, and the list of children associated with the parent.
 * The content is dynamically loaded based on the selected tab (General / Child List).
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/parent
 * @since      1.0.0
 *
 */
defined( 'ABSPATH' ) || exit;
if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'view_action' ) ) {
	$parent_id                 = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['parent_id'])) ) );
	$active_tab1               = isset( $_REQUEST['tab1'] ) ? sanitize_text_field(wp_unslash($_REQUEST['tab1'])) : 'general';
	$parent_data               = get_userdata( $parent_id );
	$mjschool_custom_field_obj = new Mjschool_Custome_Field();
	$user_meta                 = get_user_meta( $parent_id, 'child', true );
	?>
	<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start panel body div.-->
		<div class="content-body"><!-- Start content body div. -->
			<!-- Detail page header start. -->
			<section id="mjschool-user-information">
				<div class="mjschool-view-page-header-bg">
					<div class="row">
						<div class="col-xl-10 col-md-9 col-sm-10">
							<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
								<?php 
								$umetadata = mjschool_get_user_image($parent_data->ID);
								?>
								<img class="mjschool-user-view-profile-image" src="<?php if ( ! empty( $umetadata ) ) { echo esc_url($umetadata); } else { echo esc_url( get_option( 'mjschool_parent_thumb_new' ) ); } ?>">
								<div class="row mjschool-profile-user-name">
									<div class="mjschool-float-left mjschool-view-top1">
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<span class="mjschool-view-user-name-label"><?php echo esc_html( $parent_data->display_name); ?></span>
											<?php
											if ($user_access_edit === '1' ) {
												?>
												<div class="mjschool-view-user-edit-btn">
													<a class="mjschool-color-white mjschool-margin-left-2px" href="?page=mjschool_parent&tab=addparent&action=edit&parent_id=<?php echo esc_attr( mjschool_encrypt_id($parent_data->ID ) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>">
														<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
													</a>
												</div>
												<?php
											}
											?>
										</div>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
											<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-phone.png"); ?>">&nbsp;+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<span><?php echo esc_html( $parent_data->mobile_number); ?></span>
											</div>
										</div>
									</div>
								</div>
								<div class="row mjschool-view-user-teacher-label">
									<div class="col-xl-12 col-md-12 col-sm-12">
										<div class="mjschool-view-top2">
											<div class="row mjschool-view-user-teacher-label">
												<div class="col-md-12 mjschool-address-student-div">
													<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-location.png"); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $parent_data->address); ?></label>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2">
							<div class="mjschool-group-thumbs">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
							</div>
						</div>
						
					</div>
				</div>
			</section>
			<!-- Detail page header end. -->
			<!-- Detail page tabing start. -->
			<section id="body_area" class="body_areas">
				<div class="row">
					<div class="col-xl-12 col-md-12 col-sm-12">
						<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
							<li class="<?php if ( $active_tab1 === 'general' ) { ?>active<?php } ?>">
								<a href="admin.php?page=mjschool_parent&tab=view_parent&action=view_parent&tab1=general&parent_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['parent_id'])) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>">
									<?php esc_html_e( 'GENERAL', 'mjschool' ); ?>
								</a>
							</li>
							<li class="<?php if ( $active_tab1 === 'Child' ) { ?>active<?php } ?>">
								<a href="admin.php?page=mjschool_parent&tab=view_parent&action=view_parent&tab1=Child&parent_id=<?php echo esc_attr( sanitize_text_field(wp_unslash($_REQUEST['parent_id'])) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'Child' ? 'active' : ''; ?>">
									<?php esc_html_e( 'Child List', 'mjschool' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</section>
			<!-- Detail page tabing end. -->
			<!-- Detail page body content section.  -->
			<section id="mjschool-body-content-area">
				<div class="mjschool-panel-body"><!-- Start panel body div..-->
					<?php
					// General tab start.
					if ( $active_tab1 === 'general' ) {
						?>
						<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Email ID', 'mjschool' ); ?> </label><br />
								<label class="mjschool-view-page-content-labels"> <?php echo esc_html( $parent_data->user_email ); ?> </label>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $parent_data->mobile_number ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels">
										+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $parent_data->mobile_number ); ?>
									</label>
								<?php } ?>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Date of Birth', 'mjschool' ); ?></label><br />
								<?php
								$birth_date      = $parent_data->birth_date;
								$is_invalid_date = empty( $birth_date ) || $birth_date === '1970-01-01' || $birth_date === '0000-00-00';
								if ( $user_access_edit === '1' && $is_invalid_date ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels"> 
										<?php
										if ( ! empty( $parent_data->birth_date ) ) {
											echo esc_html( mjschool_get_date_in_input_box( $parent_data->birth_date ) );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								<?php } ?>
							</div>
							<div class="col-xl-2 col-md-2 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Gender', 'mjschool' ); ?> </label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $parent_data->gender ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels"> <?php echo esc_html( ucfirst( $parent_data->gender ) ); ?></label>
								<?php } ?>
							</div>
							<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-bottom-10-res">
								<label class="mjschool-view-page-header-labels"> <?php esc_html_e( 'Relation', 'mjschool' ); ?> </label><br />
								<?php
								if ( $user_access_edit === '1' && empty( $parent_data->relation ) ) {
									$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
									echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
								} else {
									?>
									<label class="mjschool-view-page-content-labels">
										<?php
										$relation = $parent_data->relation;
										if ( ! empty( $relation ) ) {
											echo esc_html( $parent_data->relation );
										} else {
											esc_html_e( 'Not Provided', 'mjschool' );
										}
										?>
									</label>
								<?php } ?>
							</div>
						</div>
						<!-- Student information div start.  -->
						<div class="row mjschool-margin-top-20px">
							<div class="col-xl-12 col-md-12 col-sm-12">
								<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
									<div class="mjschool-guardian-div">
										<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Contact Information', 'mjschool' ); ?> </label>
										<div class="row">
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'City', 'mjschool' ); ?> </label> <br>
												<?php
												if ( $user_access_edit === '1' && empty( $parent_data->city ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $parent_data->city ) ) {
															echo esc_html( $parent_data->city );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'State', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $parent_data->state ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-text-style-capitalization mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $parent_data->state ) ) {
															echo esc_html( $parent_data->state );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Zip Code', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $parent_data->zip_code ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels"><?php echo esc_html( $parent_data->zip_code ); ?></label>
												<?php } ?>
											</div>
											<div class="col-xl-3 col-md-3 col-sm-12 mjschool-margin-top-15px">
												<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php esc_html_e( 'Alt. Mobile Number', 'mjschool' ); ?> </label><br>
												<?php
												if ( $user_access_edit === '1' && empty( $parent_data->phone ) ) {
													$edit_url = admin_url( 'admin.php?page=mjschool_parent&tab=addparent&action=edit&parent_id=' . esc_attr( mjschool_encrypt_id( $parent_data->ID ) ) . '&_wpnonce=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
													echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url( $edit_url ) . '">Add</a>';
												} else {
													?>
													<label class="mjschool-view-page-content-labels">
														<?php
														if ( ! empty( $parent_data->phone ) ) {
															?>
															+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;
															<?php
															echo esc_html( $parent_data->phone );
														} else {
															esc_html_e( 'Not Provided', 'mjschool' );
														}
														?>
													</label>
												<?php } ?>
											</div>
										</div>
										<?php
										if ( ! empty( $parent_data->user_document ) ) {
											?>
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Document Information', 'mjschool' ); ?> </label>
											<div class="row">
												<?php
												$document_array = json_decode( $parent_data->user_document );
												foreach ( $document_array as $key => $value ) {
													?>
													<div class="col-xl-3 col-md-3 col-sm-12 mjschool-address-rs-css mjschool-margin-top-15px">
														<label class="mjschool-guardian-labels mjschool-view-page-header-labels"> <?php echo esc_html( $value->document_title ); ?> </label><br>
														<label class="mjschool-label-value">
															<?php
															if ( ! empty( $value->document_file ) ) {
																?>
																<a target="blank" class="mjschool-status-read btn btn-default mjschool-download-btn-syllebus" href="<?php print esc_url( content_url() . '/uploads/school_assets/' . $value->document_file ); ?>" record_id="<?php echo esc_attr( $key ); ?>">
																	<i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?>
																</a> 
																<?php
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
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
								$module = 'parent';
								$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page( $module );
								?>
							</div>
						</div>
						<?php
					}
					// Attendance tab start.
					elseif ( $active_tab1 === 'Child' ) {
						?>
						<div>
							<div id="Section1" class="mjschool_new_sections">
								<div class="row">
									<div class="col-lg-12">
										<div>
											<div class="card-content">
												<div class="table-responsive">
													<?php
													if ( ! empty( $user_meta ) ) {
														?>
														<table id="mjschool-child-list-for-parent" class="display table" cellspacing="0" width="100%">
															<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
																<tr>
																	<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Mobile Number', 'mjschool' ); ?> </th>
																	<th><?php esc_html_e( 'Class & Section', 'mjschool' ); ?></th>
																	<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
																</tr>
															</thead>
															<tbody>
																<?php
																foreach ( $user_meta as $childsdata ) {
																	$child = get_userdata( $childsdata );
																	if ( ! empty( $child ) ) {
																		?>
																		<tr>
																			<td class="mjschool-width-50px-td">
																				<?php
																				if ( $childsdata)
																				{
																					$umetadata=mjschool_get_user_image($childsdata);
																				}
																				if(empty($umetadata ) )
																				{
																					echo '<img src='.esc_url( get_option( 'mjschool_student_thumb_new' ) ).' height="50px" width="50px" class="img-circle" />';
																				}
																				else
																					echo '<img src='.esc_url($umetadata).' height="50px" width="50px" class="img-circle"/>';
																				?>
																			</td>
																			<td class="name">
																				<a class="mjschool-color-black" href="admin.php?page=mjschool_student&tab=view_student&action=view_student&student_id=<?php echo esc_attr( mjschool_encrypt_id( $child->ID ) ); ?>&_wpnonce=<?php echo esc_attr( mjschool_get_nonce( 'view_action' ) ); ?>"><?php echo esc_html( $child->first_name ) . ' ' . esc_html( $child->middle_name ) . ' ' . esc_html( $child->last_name ); ?></a>
																				<br>
																				<label class="mjschool-list-page-email"><?php echo esc_html( $child->user_email ); ?></label>
																			</td>
																			<td>+<?php echo esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?>&nbsp;&nbsp;<?php echo esc_html( $child->mobile_number ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Mobile Number', 'mjschool' ); ?>"></i></td>
																			<td class="name">
																				<?php
																				$class_id   = get_user_meta( $child->ID, 'class_name', true );
																				$section_id = get_user_meta( $child->ID, 'class_section', true );
																				$classname  = mjschool_get_class_section_name_wise( $class_id, $section_id );
																				if ( ! empty( $classname ) ) {
																					echo esc_html( $classname );
																				} else {
																					esc_html_e( 'Not Provided', 'mjschool' );
																				}
																				?>
																				<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class & Section', 'mjschool' ); ?>"></i>
																			</td>
																			<td>
																				<?php echo esc_html( get_user_meta( $child->ID, 'roll_id', true ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
																			</td>
																		</tr>
																		<?php
																	}
																}
																?>
															</tbody>
														</table>
														<?php
													} else {
														?>
														<div class="mjschool-calendar-event-new">
															<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
														</div>
														<?php
													}
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div><!-- End panel body div.-->
			</section>
			<!-- Detail page body content section end. -->
		</div><!-- End content body div. -->
	</div><!-- End panel body div. -->
	<?php
} else {
	wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
}
?>