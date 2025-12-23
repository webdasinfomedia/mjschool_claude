<?php
/**
 * Hostel Details and Room Management Page.
 *
 * This file handles the display and management of individual hostel details
 * within the MJSchool plugin. It provides administrators with an interactive
 * interface to view hostel information, manage rooms and beds, and assign
 * students to beds.
 *
 * Key Features:
 * - Displays hostel name, type, capacity, and address details.
 * - Provides navigation tabs for "Room List", "Bed List", and "Assign Bed".
 * - Supports adding, editing, and deleting hostel rooms and beds.
 * - Implements nonce verification for secure form submissions.
 * - Integrates with `Mjschool_Hostel` and `Mjschool_Custome_Field` classes.
 * - Uses dynamic category and facility options for room management.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hostel
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$active_tab1      = isset( $_REQUEST['tab1'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab1'] ) ) : 'roomlist';
$obj_hostel       = new Mjschool_Hostel();
$hostel_id        = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
$hostel_data      = $obj_hostel->mjschool_get_hostel_by_id( $hostel_id );
$custom_field_obj = new Mjschool_Custome_Field();
$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
?>
<div class="mjschool-panel-body mjschool-view-page-main"><!-- Start Panel Body Div.-->
	<div class="content-body"><!-- START CONTENT BODY DIV.-->
		<!-- Detail Page Header Start. -->
		<section id="mjschool-user-information">
			<div class="mjschool-view-page-header-bg">
				<div class="row">
					<div class="col-xl-10 col-md-9 col-sm-10">
						<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
							<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-hostel.png' ); ?>">
							<div class="row mjschool-profile-user-name">
								<div class="mjschool-float-left mjschool-view-top1">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
										<label class="mjschool-view-user-name-label"><?php echo esc_html( ucfirst( $hostel_data->hostel_name ) ); ?></label>
										<div class="mjschool-view-user-edit-btn">
											<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=add_hostel&action=edit&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>">
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-edit.png' ); ?>">
											</a>
										</div>
									</div>
									<div class="col-xl-6 col-md-6 col-sm-6 mjschool-float-left-width-100px">
										<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
											<label><?php esc_html_e( 'Hostel Type', 'mjschool' ); ?></label> - <label><?php echo esc_html( ucfirst( $hostel_data->hostel_type ) ); ?></label>
										</div>
									</div>
									<div class="col-xl-6 col-md-6 col-sm-6 mjschool-float-left-width-100px">
										<div class="mjschool-view-user-phone mjschool-float-left-width-100px">
											<label><?php esc_html_e( 'Capacity', 'mjschool' ); ?></label> - <label><?php if ( ! empty( $hostel_data->hostel_intake ) ) { echo esc_html( $hostel_data->hostel_intake ); } else { esc_html_e( 'N/A', 'mjschool' ); } ?></label>
										</div>
									</div>
								</div>
							</div>
							<div class="row mjschool-view-user-teacher-label">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="mjschool-view-top2">
										<div class="row mjschool-view-user-teacher-label">
											<div class="col-md-12 mjschool-address-student-div">
												
												<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-location.png' ); ?>">&nbsp;&nbsp;<label class="mjschool-address-detail-page"><?php echo esc_html( $hostel_data->hostel_address ); ?></label>
												
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
						<div class="mjschool-group-thumbs">
							
							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-group.png' ); ?>">
							
						</div>
					</div>
				</div>
			</div>
		</section>
		<section id="body_area" class="teacher_view_tab body_areas">
			<div class="row">
				<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
					<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
						<li class="<?php if ( $active_tab1 === 'roomlist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=roomlist&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'roomlist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Room List', 'mjschool' ); ?>
							</a>
						</li>
						<li class="<?php if ( $active_tab1 === 'bedlist' ) { ?>active<?php } ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) ) ); ?>"class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'bedlist' ? 'active' : ''; ?>">
								<?php esc_html_e( 'Bed List', 'mjschool' ); ?>
							</a>
						</li>
						<?php
						if ( $active_tab1 === 'assign_bed' ) {
							if ( $action === 'view_assign_room' ) {
								$room_id_param = isset( $_REQUEST['room_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) : '';
								?>
								<li class="<?php if ( $active_tab1 === 'assign_bed' ) { ?>active<?php } ?>">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=view_assign_room&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( $room_id_param ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'assign_bed' ? 'active' : ''; ?>">
										<?php esc_html_e( 'Assign Bed', 'mjschool' ); ?>
									</a>
								</li>
							<?php } elseif ( $action === 'view_assign_bed' ) { 
								$bed_id_param = isset( $_REQUEST['bed_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bed_id'] ) ) : '';
								?>
								<li class="<?php if ( $active_tab1 === 'assign_bed' ) { ?>active<?php } ?>">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=view_assign_bed&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&bed_id=' . rawurlencode( $bed_id_param ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1 ) === 'assign_bed' ? 'active' : ''; ?>">
										<?php esc_html_e( 'Assign Bed', 'mjschool' ); ?>
									</a>
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div>
			</div>
		</section>
		<section id="mjschool-body-content-area">
			<div class="mjschool-panel-body"><!-- START PANEL BODY DIV.-->
				<div class="row">
					<div class="col-xl-12 col-md-12 col-sm-12">
						<?php
						$room_message = isset( $_REQUEST['room_message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['room_message'] ) ) : '0';
						switch ( $room_message ) {
							case 'insert_success':
								$message_string = esc_html__( 'Room Added Successfully.', 'mjschool' );
								break;
							case 'edit_success':
								$message_string = esc_html__( 'Room Updated Successfully.', 'mjschool' );
								break;
							case 'delete_success':
								$message_string = esc_html__( 'Room Deleted Successfully.', 'mjschool' );
								break;
							case 'assign_success':
								$message_string = esc_html__( 'Bed Assigned Successfully', 'mjschool' );
								break;
							case 'assign_delete_success':
								$message_string = esc_html__( 'Assigned Bed Deleted Successfully.', 'mjschool' );
								break;
						}
						if ( $room_message ) {
							?>
							<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
								<p><?php echo esc_html( $message_string ); ?></p>
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
							</div>
							<?php
						}
						$bed_message = isset( $_REQUEST['bed_message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['bed_message'] ) ) : '0';
						switch ( $bed_message ) {
							case 'insert_success':
								$message_string = esc_html__( 'Bed Added Successfully.', 'mjschool' );
								break;
							case 'edit_success':
								$message_string = esc_html__( 'Bed Updated Successfully.', 'mjschool' );
								break;
							case 'delete_success':
								$message_string = esc_html__( 'Bed Deleted Successfully.', 'mjschool' );
								break;
							case 'no_capacity':
								$message_string = esc_html__( 'Room has no extra bed capacity', 'mjschool' );
								break;
						}
						if ( $bed_message ) {
							?>
							<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
								<p><?php echo esc_html( $message_string ); ?></p>
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
							</div>
							<?php
						}
						?>
					</div>
				</div>     
				<?php
				if ( $active_tab1 === 'roomlist' ) {
					?>
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
								<div class="mjschool-guardian-div">
									<?php
									if ( isset( $_POST['save_room'] ) ) {
										$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
										if ( wp_verify_nonce( $nonce, 'save_room_admin_nonce' ) ) {
											$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
											if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
												if ( $action === 'edit_room' ) {
													$result = $obj_hostel->mjschool_insert_room( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
													if ( $result ) {
														$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
														wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=edit_success' ) );
														die();
													}
												} else {
													wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
												}
											} else {
												$result = $obj_hostel->mjschool_insert_room( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
												$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
												wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=insert_success' ) );
												die();
											}
										}
									}
									if ( $action === 'delete_room' ) {
										$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
										if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
											$room_id_decrypt = isset( $_REQUEST['room_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) ) ) : 0;
											$result = $obj_hostel->mjschool_delete_room( $room_id_decrypt );
											if ( $result ) {
												$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
												wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=delete_success' ) );
												die();
											}
										} else {
											wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
										}
									}
									if ( isset( $_REQUEST['mjschool-delete-selected-room'] ) ) {
										if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
											$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
											foreach ( $ids as $id ) {
												$result = $obj_hostel->mjschool_delete_room( $id );
											}
										}
										if ( $result ) {
											$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
											wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=delete_success' ) );
											die();
										}
									}
									$edit = 0;
									if ( $action === 'edit_room' ) {
										$edit      = 1;
										$room_id_decrypt = isset( $_REQUEST['room_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) ) ) : 0;
										$room_data = $obj_hostel->mjschool_get_room_by_id( $room_id_decrypt );
									}
									?>
									<div class="mjschool-popup-bg">
										<div class="mjschool-overlay-content mjschool-admission-popup">
											<div class="modal-content">
												<div class="mjschool-category-list">
												</div>     
											</div>
										</div>     
									</div>
									<form name="room_form" action="" method="post" class="mjschool-form-horizontal" id="room_form">
										<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
										<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
										<input type="hidden" name="room_id" value="<?php if ( $edit ) { echo esc_attr( intval( $room_data->id ) ); } ?>"/>
										<input type="hidden" name="hostel_id" value="<?php echo esc_attr( intval( $hostel_data->id ) ); ?>"/> 
										<div class="header">	
											<h3 class="mjschool-first-header"><?php esc_html_e( 'Add Hostel Room', 'mjschool' ); ?></h3>
										</div>
										<div class="form-body mjschool-user-form"> <!--Card Body div.-->   
											<div class="row"><!--Row Div.--> 
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="room_unique_id" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $room_data->room_unique_id ); } else { echo esc_attr( mjschool_generate_room_code() ); } ?>" name="room_unique_id" readonly>    
															<label  for="room_unique_id"><?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>	
														</div>
													</div>
												</div>
												<div class="col-md-4 input">
													<label class="ml-1 mjschool-custom-top-label top" for="hostel_type"><?php esc_html_e( 'Room Type', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
													<select class="form-control validate[required] room_category mjschool-width-100px" name="room_category" id="room_category">
														<option value=""><?php esc_html_e( 'Select Room', 'mjschool' ); ?></option>
														<?php
														$activity_category = mjschool_get_all_category( 'room_category' );
														if ( ! empty( $activity_category ) ) {
															if ( $edit ) {
																$room_val = $room_data->room_category;
															} else {
																$room_val = '';
															}
															foreach ( $activity_category as $retrive_data ) {
																?>
																<option value="<?php echo esc_attr( intval( $retrive_data->ID ) ); ?>" <?php selected( $retrive_data->ID, $room_val ); ?>><?php echo esc_html( $retrive_data->post_title ); ?> </option>
																<?php
															}
														}
														?>
													</select>	
												</div>
												<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
													<button id="mjschool-addremove-cat" class="mjschool-save-btn sibling_add_remove" model="room_category"><?php esc_html_e( 'Add', 'mjschool' ); ?></button>		
												</div>
												<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin mjschool-padding-top-15px-res">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="beds_capacity" class="form-control validate[required,custom[onlyNumberSp],maxSize[2],min[1]] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( intval( $room_data->beds_capacity ) ); } ?>" name="beds_capacity">
															<label  for="Bed Capacity"><?php esc_html_e( 'Number Of Beds', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label> 
														</div>
													</div>
												</div>
												<?php wp_nonce_field( 'save_room_admin_nonce' ); ?>
												<div class="col-md-6 mjschool-note-text-notice">
													<div class="form-group input">
														<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
															<div class="form-field">
																<textarea name="room_description" id="room_description" maxlength="150"  class="mjschool-textarea-height-47px form-control validate[custom[description_validation]]"><?php if ( $edit ) { echo esc_textarea( $room_data->room_description ); } ?></textarea>
																<span class="mjschool-txt-title-label"></span>
																<label  class="text-area address active" for="room_description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
															</div>
														</div>
													</div>
												</div>
												<div class="col-md-12 mjschool-padding-bottom-15px-res mjschool-rtl-margin-top-15px mjschool-margin-top-15px mb-3">
													<div class="form-group">
														<div class="col-md-12 form-control mjschool-input-height-50px">
															<div class="row mjschool-padding-radio">
																<div class="input-group mjschool-input-checkbox">
																	<label class="mjschool-custom-top-label label_right_position"><?php esc_html_e( 'Select Hostel/Room Facilities', 'mjschool' ); ?></label>													
																	<div class="checkbox mjschool-checkbox-label-padding-8px">
																		<div class="mjschool_display_flex_column">
																			<?php
																			// Categorized list of amenities.
																			$hostel_facilities = array(
																				'Appliances' => array(
																					'Air Conditioner (AC)' => esc_html__( 'Air Conditioner (AC)', 'mjschool' ),
																					'Fan' => esc_html__( 'Fan', 'mjschool' ),
																					'Refrigerator' => esc_html__( 'Refrigerator', 'mjschool' ),
																					'Microwave' => esc_html__( 'Microwave', 'mjschool' ),
																					'Water Heater' => esc_html__( 'Water Heater', 'mjschool' ),
																					'Washing Machine' => esc_html__( 'Washing Machine', 'mjschool' ),
																					'TV' => esc_html__( 'TV', 'mjschool' ),
																				),
																				'Furniture' => array(
																					'Bed' => esc_html__( 'Bed', 'mjschool' ),
																					'Study Table' => esc_html__( 'Study Table', 'mjschool' ),
																					'Chair' => esc_html__( 'Chair', 'mjschool' ),
																					'Cupboard' => esc_html__( 'Cupboard', 'mjschool' ),
																					'Sofa' => esc_html__( 'Sofa', 'mjschool' ),
																				),
																				'Utilities' => array(
																					'WiFi' => esc_html__( 'WiFi', 'mjschool' ),
																					'Laundry Service' => esc_html__( 'Laundry Service', 'mjschool' ),
																					'Room Cleaning' => esc_html__( 'Room Cleaning', 'mjschool' ),
																					'Power Backup' => esc_html__( 'Power Backup', 'mjschool' ),
																				),
																				'Room Features' => array(
																					'Attached Bathroom' => esc_html__( 'Attached Bathroom', 'mjschool' ),
																					'Shared Bathroom' => esc_html__( 'Shared Bathroom', 'mjschool' ),
																					'Balcony' => esc_html__( 'Balcony', 'mjschool' ),
																					'Locker Facility' => esc_html__( 'Locker Facility', 'mjschool' ),
																				),
																			);
																			if ( $edit ) {
																				if ( ! empty( $room_data->facilities ) ) {
																					$edit_facilities = json_decode( $room_data->facilities );
																				}
																			}
																			// Get saved selections (deserialize).
																			$selected_facilities = get_option( 'mjschool_hostel_room_facilities', array() );
																			$selected_facilities = is_array( $selected_facilities ) ? $selected_facilities : array();
																			foreach ( $hostel_facilities as $category => $facilities ) {
																				echo '<div><strong>' . esc_html( $category ) . '</strong><br>';
																				echo "<div style='display: flex; flex-wrap: wrap;'>";
																				foreach ( $facilities as $key => $facility ) {
																					$checked = '';
																					if ( $edit && isset( $edit_facilities->$category ) && in_array( $key, $edit_facilities->$category ) ) {
																						$checked = 'checked';
																					} elseif ( isset( $selected_facilities[ $category ] ) && in_array( $key, $selected_facilities[ $category ] ) ) {
																						$checked = 'checked';
																					} else {
																						$checked = isset( $selected_facilities[ $category ] ) && in_array( $facility, $selected_facilities[ $category ] ) ? 'checked' : '';
																					}
																					?>
																					<label class="mjschool_white_space_margin_10px">
																						<input type="checkbox" name="mjschool_hostel_room_facilities[<?php echo esc_attr( $category ); ?>][]" value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $checked ); ?> /> 
																						<?php echo esc_html( $facility ); ?>
																					</label>
																					<?php
																				}
																				echo '</div></div><br>';
																			}
																			?>
																		</div>
																	</div>
																</div>
															</div>												
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="form-body mjschool-user-form">
											<div class="row">
												<div class="col-sm-6">
													<input type="submit" value="<?php echo $edit ? esc_attr__( 'Save Room', 'mjschool' ) : esc_attr__( 'Add Room', 'mjschool' ); ?>" name="save_room" class="btn btn-success mjschool-save-btn" />
												</div>
											</div>
										</div>
									</form>
								</div>
								<div class="header mt-4">	
									<h3 class="mjschool-first-header"><?php esc_html_e( 'Hostel Room List', 'mjschool' ); ?></h3>
								</div>
								<?php
								$hostel_id_decrypt = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
								$retrieve_class_data = $obj_hostel->mjschool_get_room_by_hostel_id( $hostel_id_decrypt );
								if ( ! empty( $retrieve_class_data ) ) {
									?>
									<div class="mjschool-panel-body">
										<div class="table-responsive">
											<form id="mjschool-common-form" name="mjschool-common-form" method="post">
												<table id="room_list" class="display" cellspacing="0" width="100%">
													<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
														<tr>
															<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
															<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Hostel Name', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Room Type', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Remaining No Of Beds', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Availability', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Facilities', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'View & Assign Room', 'mjschool' ); ?></th>
															<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<?php
														$i = 0;
														foreach ( $retrieve_class_data as $retrieved_data ) {
															$capacity = $obj_hostel->mjschool_remaining_bed_capacity( $retrieved_data->id );
															$color_class_css = mjschool_table_list_background_color( $i );
															?>
															<tr>
																<td class="mjschool-checkbox-width-10px">
																	<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>">
																</td>
																
																<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-hostel.png' ); ?>" class="img-circle" /></td>
																
																<td>
																	<?php echo esc_html( $retrieved_data->room_unique_id ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Unique ID', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->hostel_id ) ) {
																		echo esc_html( mjschool_get_hostel_name_by_id( $retrieved_data->hostel_id ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' ); }
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Hostel Name', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php echo esc_html( get_the_title( $retrieved_data->room_category ) ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Type', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	echo esc_html( $capacity ) . ' ';
																		esc_html_e( 'Out Of', 'mjschool' );
																		echo ' ' . esc_html( $retrieved_data->beds_capacity );
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Remaining No Of Beds', 'mjschool' ); ?>"></i>
																</td>
																<?php
																$room_cnt     = mjschool_hostel_room_status_check( $retrieved_data->id );
																$bed_capacity = (int) $retrieved_data->beds_capacity;
																if ( $room_cnt >= $bed_capacity ) {
																	?>
																	<td>
																		<label class="mjschool-hostel-lbl"><?php esc_html_e( 'Occupied', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Availability', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																} else {
																	?>
																	<td>
																		<label class="mjschool-hoste-lbl2"><?php esc_html_e( 'Available', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Availability', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																}
																?>
																<td>
																	<?php
																	$facility = mjschool_room_facility_show( $retrieved_data->facilities );
																	if ( ! empty( $facility ) ) {
																		$length = strlen( $facility );
																		if ( $length > 30 ) {
																			echo esc_html( substr( $facility, 0, 30 ) ) . '...';
																		} else {
																			echo esc_html( $facility );
																		}
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $facility ) ) { echo esc_attr( $facility ); } else { esc_attr_e( 'Facilities', 'mjschool' );} ?>"></i>
																</td>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->room_description ) ) {
																		$strlength = strlen( $retrieved_data->room_description );
																		if ( $strlength > 30 ) {
																			echo esc_html( substr( $retrieved_data->room_description, 0, 30 ) ) . '...';
																		} else {
																			echo esc_html( $retrieved_data->room_description );
																		}
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->room_description ) ) { echo esc_attr( $retrieved_data->room_description ); } else { esc_attr_e( 'Description', 'mjschool' );} ?>"></i>
																</td>
																<td>
																	<?php
																	if ( $room_cnt >= $bed_capacity ) {
																		esc_html_e( 'No Bed Available In This Room.', 'mjschool' );
																	} else {
																		?>
																		<button class="btn btn-default mjschool-assign-room-btn-design"><a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=view_assign_room&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-bed"></i> <?php esc_html_e( 'Assign Bed', 'mjschool' ); ?></a></button>
																		<?php
																	}
																	?>
																</td>
																<td class="action"> 
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																					
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<?php
																					if ( $room_cnt >= $bed_capacity ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=view_assign_room&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-list"></i><?php esc_html_e( 'Occupied List', 'mjschool' ); ?></a> 
																						</li>
																						<?php
																					}
																					if ( $user_access_add === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&action=add_bed&tab1=bedlist&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-bed"></i><?php esc_html_e( 'Add Bed', 'mjschool' ); ?></a> 
																						</li>
																						<?php
																					}
																					if ( $user_access_edit === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&action=edit_room&tab1=roomlist&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a> 
																						</li>
																						<?php
																					}
																					if ( $user_access_delete === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&action=delete_room&tab1=roomlist&hostel_id=' . rawurlencode( mjschool_encrypt_id( $hostel_id ) ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color"  onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a> 
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
												<div class="mjschool-print-button pull-left">
													<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
														<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
														<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
													</button>
													<?php
													if ( $user_access_delete === '1' ) {
														 ?>
														<button id="mjschool-delete-selected-room" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="mjschool-delete-selected-room" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
														<?php  
													}
													?>
												</div>
											</form>
										</div>
									</div>
									<?php
								} else {
									?>
									<div class="mjschool-calendar-event-new"> 
										
										<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
										
									</div>		
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
				if ( $active_tab1 === 'bedlist' ) {
					// INSERT AND UPDATES BEDS.
					if ( isset( $_POST['save_bed'] ) ) {
						$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
						if ( wp_verify_nonce( $nonce, 'save_bed_admin_nonce' ) ) {
							if ( $action === 'edit_bed' ) {
								$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
								if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
									$bed_id  = isset( $_REQUEST['bed_id'] ) ? intval( wp_unslash( $_REQUEST['bed_id'] ) ) : 0;
									$room_id = isset( $_REQUEST['room_id'] ) ? intval( wp_unslash( $_REQUEST['room_id'] ) ) : 0;
									global $wpdb;
									$table_mjschool_beds = $wpdb->prefix . 'mjschool_beds';
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
									$result_bed   = $wpdb->get_results(
										$wpdb->prepare( "SELECT * FROM $table_mjschool_beds WHERE room_id = %d AND id != %d", $room_id, $bed_id )
									);
									$bed          = count( $result_bed );
									$bed_capacity = mjschool_get_bed_capacity_by_id( $room_id );
									if ( $bed < $bed_capacity ) {
										$result = $obj_hostel->mjschool_insert_bed( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
										if ( $result ) {
											$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
											wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=edit_success' ) );
											die();
										}
									} else {
										$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
										wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=no_capacity' ) );
										die();
									}
								} else {
									wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
								}
							} else {
								$room_id_post     = isset( $_POST['room_id'] ) ? intval( wp_unslash( $_POST['room_id'] ) ) : 0;
								$assign_bed       = mjschool_hostel_room_bed_count( $room_id_post );
								$bed_capacity     = mjschool_get_bed_capacity_by_id( $room_id_post );
								$bed_capacity_int = (int) $bed_capacity;
								if ( $assign_bed >= $bed_capacity_int ) {
									$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
									wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=no_capacity' ) );
									die();
								} else {
									$result = $obj_hostel->mjschool_insert_bed( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
									if ( $result ) {
										$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
										wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=insert_success' ) );
										die();
									}
								}
							}
						}
					}
					// DELETE RECORD BED.
					if ( $action === 'delete_bed' ) {
						$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
						if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
							$bed_id_decrypt = isset( $_REQUEST['bed_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['bed_id'] ) ) ) ) : 0;
							$result = $obj_hostel->mjschool_delete_bed( $bed_id_decrypt );
							if ( $result ) {
								$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
								wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=delete_success' ) );
								die();
							}
						} else {
							wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
						}
					}
					// DELETE MULTIPLE SELECTED BED.
					if ( isset( $_REQUEST['delete_selected_bed'] ) ) {
						if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
							$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
							foreach ( $ids as $id ) {
								$result = $obj_hostel->mjschool_delete_bed( $id );
							}
						}
						if ( $result ) {
							$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
							wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_message=delete_success' ) );
							die();
						}
					}
					?>
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12">
							<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-15px-rs">
								<div class="mjschool-guardian-div">
									<?php
									$edit = 0;
									if ( $action === 'edit_bed' ) {
										$edit     = 1;
										$bed_id_decrypt = isset( $_REQUEST['bed_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['bed_id'] ) ) ) ) : 0;
										$bed_data = $obj_hostel->mjschool_get_bed_by_id( $bed_id_decrypt );
									}
									?>
									<div class="mjschool-panel-body"> <!-- start mjschool-panel-body. -->
										<form name="mjschool-bed-form" action="" method="post" class="mjschool-form-horizontal" id="mjschool-bed-form">
											<?php $mjschool_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'insert'; ?>
											<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
											<input type="hidden" name="bed_id" value="<?php if ( $edit ) { echo esc_attr( intval( $bed_data->id ) ); } ?>"/> 
											<div class="header">	
												<h3 class="mjschool-first-header"><?php esc_html_e( 'Add Room Beds', 'mjschool' ); ?></h3>
											</div>
											<div class="form-body mjschool-user-form"> <!--Card Body div.-->   
												<div class="row"><!--Row Div.--> 
													<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="bed_unique_id" class="form-control validate[required] text-input" type="text" value="<?php if ( $edit ) { echo esc_attr( $bed_data->bed_unique_id ); } else { echo esc_attr( mjschool_generate_bed_code() ); } ?>"  name="bed_unique_id" readonly> 
																<label  for="bed_unique_id"><?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>	
															</div>
														</div>
													</div>
													<div class="col-md-6 input mjschool-error-msg-left-margin">
														<label class="ml-1 mjschool-custom-top-label top" for="room_id"><?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?> <span class="mjschool-require-field">*</span></label>
														<select name="room_id" class="form-control validate[required] mjschool-width-100px" id="room_id">
															<option value=""><?php esc_html_e( 'Select Room Unique ID', 'mjschool' ); ?></option>
															<?php
															$hostel_id_decrypt = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
															$room_data = $obj_hostel->mjschool_get_room_by_hostel_id( $hostel_id_decrypt );
															if ( $edit ) {
																$roomval = $bed_data->room_id;
															} elseif ( $action === 'add_bed' ) {
																$roomval = isset( $_REQUEST['room_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) ) ) : 0;
															} else {
																$roomval = '';
															}
															foreach ( $room_data as $room ) {
																?>
																<option value="<?php echo esc_attr( intval( $room->id ) ); ?>" <?php selected( $room->id, $roomval ); ?>><?php echo esc_html( $room->room_unique_id ); ?></option> 
																<?php
															}
															?>
														</select>
													</div>
													<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
														<div class="form-group input">
															<div class="col-md-12 form-control">
																<input id="bed_charge" class="form-control validate[custom[popup_category_validation]] text-input" maxlength="50" type="number" value="<?php if ( $edit ) { echo esc_attr( floatval( $bed_data->bed_charge ) ); } ?>" name="bed_charge">
																<label  for="bed_charge"><?php esc_html_e( 'Cost', 'mjschool' ); ?> (<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)</label>
															</div>
														</div>
													</div>
													<?php wp_nonce_field( 'save_bed_admin_nonce' ); ?>	
													<div class="col-md-6 mjschool-note-text-notice">
														<div class="form-group input">
															<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
																<div class="form-field">
																	<textarea name="bed_description" id="bed_description" maxlength="150" class="mjschool-textarea-height-47px form-control validate[custom[description_validation]]"><?php if ( $edit ) { echo esc_textarea( $bed_data->bed_description ); } ?></textarea>
																	<span class="mjschool-txt-title-label"></span>
																	<label class="text-area address active" for="bed_description"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
											<div class="form-body mjschool-user-form">
												<div class="row">
													<div class="col-sm-6">
														<input type="submit" value="<?php if ( $edit ) { esc_attr_e( 'Save Bed', 'mjschool' ); } else { esc_attr_e( 'Add Bed', 'mjschool' ); } ?>" name="save_bed" class="btn btn-success mjschool-save-btn" />
													</div>
												</div>
											</div>
										</form>
									</div><!-- End mjschool-panel-body. --> 
								</div>
								<div class="header mt-4">	
									<h3 class="mjschool-first-header"><?php esc_html_e( 'Hostel Bed List', 'mjschool' ); ?></h3>
								</div>
								<form method="post">  
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-md-4 input">
												<label class="ml-1 mjschool-custom-top-label top" for="mjschool_contry"><?php esc_html_e( 'Select Room', 'mjschool' ); ?></label>
												<select name="filter_room_id"  id="mjschool-class-list" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
													<option value="all_room"><?php esc_html_e( 'All Room', 'mjschool' ); ?></option>
													<?php
													$room_id = '';
													if ( isset( $_REQUEST['filter_room_id'] ) ) {
														$room_id = intval( wp_unslash( $_REQUEST['filter_room_id'] ) );
													}
													$hostel_id_decrypt = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
													$retrieve_class_data = $obj_hostel->mjschool_get_room_by_hostel_id( $hostel_id_decrypt );
													foreach ( $retrieve_class_data as $room_data ) {
														?>
														<option  value="<?php echo esc_attr( intval( $room_data->id ) ); ?>" <?php selected( $room_data->id, $room_id ); ?> ><?php echo esc_html( $room_data->room_unique_id ); ?></option>
														<?php
													}
													?>
												</select>         
											</div>
											<div class="col-md-3">
												<input type="submit" name="view_bed_list" Value="<?php esc_attr_e( 'Go', 'mjschool' ); ?>"  class="btn btn-info mjschool-save-btn"/>
											</div>
										</div>
									</div>
								</form>
								<?php
								if ( isset( $_POST['view_bed_list'] ) ) {
									$filter_room_id = isset( $_POST['filter_room_id'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_room_id'] ) ) : '';
									if ( $filter_room_id === 'all_room' ) {
										$hostel_id_decrypt = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
										$retrieve_class_data = $obj_hostel->mjschool_get_bed_by_hostel_id( $hostel_id_decrypt );
									} else {
										$filter_room_id_int = isset( $_REQUEST['filter_room_id'] ) ? intval( wp_unslash( $_REQUEST['filter_room_id'] ) ) : 0;
										$retrieve_class_data = $obj_hostel->mjschool_get_all_bed_by_room_id( $filter_room_id_int );
									}
								} else {
									$hostel_id_decrypt = isset( $_REQUEST['hostel_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) : 0;
									$retrieve_class_data = $obj_hostel->mjschool_get_bed_by_hostel_id( $hostel_id_decrypt );
								}
								if ( ! empty( $retrieve_class_data ) ) {
									?>
									<div class="mjschool-popup-bg">
										<div class="mjschool-overlay-content mjschool-admission-popup">
											<div class="modal-content">
												<div class="view_popup">
												</div>     
											</div>
										</div>     
									</div>
									<div class="mjschool-panel-body">
										<div class="table-responsive">
											<form id="mjschool-common-form" name="mjschool-common-form" method="post">
												<table id="mjschool-bed-list" class="display" cellspacing="0" width="100%">
													<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
														<tr>
															<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
															<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Room Unique ID', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Occupied Student', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Bed Cost', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Availability', 'mjschool' ); ?></th>
															<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
															<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
														</tr>
													</thead>
													<tbody>
														<?php
														$a = 0;
														foreach ( $retrieve_class_data as $retrieved_data ) {
															$student_id = $obj_hostel->mjschool_get_assign_bed_student_by_id( $retrieved_data->id );
															$hostel_id  = $obj_hostel->mjschool_get_hostel_id_by_room_id( $retrieved_data->room_id );
															$color_class_css = mjschool_table_list_background_color( $i );
															$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
															?>
															<tr>
																<td class="mjschool-checkbox-width-10px">
																	<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>">
																</td>
																
																<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-hostel.png' ); ?>" class="img-circle" /></td>
																
																<td>
																	<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" type="beds_view">
																	<?php echo esc_html( $retrieved_data->bed_unique_id ); ?></a> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Bed Unique ID', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php echo esc_html( mjschool_get_room_unique_id_by_id( $retrieved_data->room_id ) ); ?>(<?php echo esc_html( mjschool_get_hostel_name_by_id( $hostel_id ) ); ?>) <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Room Unique ID', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( $student_id ) {
																		echo esc_html( mjschool_student_display_name_with_roll( $student_id->student_id ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' ); }
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Occupied Student', 'mjschool' ); ?>"></i>
																</td>
																<td>
																	<?php
																	if ( $retrieved_data->bed_charge ) {
																		echo esc_html( mjschool_currency_symbol_position_language_wise( number_format( $retrieved_data->bed_charge, 2, '.', '' ) ) );
																	} else {
																		esc_html_e( 'N/A', 'mjschool' ); }
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Bed Cost', 'mjschool' ); ?>"></i>
																</td>
																<?php
																if ( $retrieved_data->bed_status === '0' ) {
																	?>
																	<td>
																		<label class="mjschool-hoste-lbl2"><?php esc_html_e( 'Available', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Availability', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																} else {
																	?>
																	<td>
																		<label class="mjschool-hostel-lbl"><?php esc_html_e( 'Occupied', 'mjschool' ); ?></label> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Availability', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																}
																?>
																<td>
																	<?php
																	if ( ! empty( $retrieved_data->bed_description ) ) {
																		$strlength = strlen( $retrieved_data->bed_description );
																		if ( $strlength > 40 ) {
																			echo esc_html( substr( $retrieved_data->bed_description, 0, 40 ) ) . '...';
																		} else {
																			echo esc_html( $retrieved_data->bed_description );
																		}
																	} else {
																		esc_html_e( 'N/A', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->bed_description ) ) { echo esc_attr( $retrieved_data->bed_description ); } else { esc_attr_e( 'Description', 'mjschool' );} ?>"></i>
																</td>
																
																<td class="action"> 
																	<div class="mjschool-user-dropdown">
																		<ul  class="mjschool_ul_style">
																			<li >
																				<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																					
																					<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																					
																				</a>
																				<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																					<li class="mjschool-float-left-width-100px">
																						<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>" type="beds_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																					</li>
																					<?php
																					if ( $retrieved_data->bed_status === '0' ) {
																						if ( $user_access_add === '1' ) {
																							?>
																							<li class="mjschool-float-left-width-100px">
																								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=view_assign_bed&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-bed"> </i><?php esc_html_e( 'Assign Bed', 'mjschool' ); ?></a> 
																							</li>
																							<?php
																						}
																					}
																					if ( $user_access_edit === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&action=edit_bed&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a> 
																						</li>
																						<?php
																					}
																					?>
																					<?php
																					if ( $user_access_delete === '1' ) {
																						?>
																						<li class="mjschool-float-left-width-100px">
																							<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&action=delete_bed&hostel_id=' . rawurlencode( $hostel_id_param ) . '&bed_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
															++$a;
														}
														?>
													</tbody>
												</table>
												<div class="mjschool-print-button pull-left">
													<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
														<input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
														<label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
													</button>
													<?php
													if ( $user_access_delete === '1' ) {
														 ?>
														<button id="delete_selected_bed" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="delete_selected_bed" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
														<?php 
													}
													?>
												</div>
											</form>
										</div>
									</div>
									<?php
								} else {
									?>
									<div class="mjschool-calendar-event-new"> 
										
										<img class="mjschool-no-data-img" src="<?php echo esc_url( MJSCHOOL_NODATA_IMG ); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
										
									</div>		
									<?php
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
				if ( $active_tab1 === 'assign_bed' ) {
					// ASSIGN BEDS.
					if ( isset( $_POST['assign_room'] ) ) {
						$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
						if ( wp_verify_nonce( $nonce, 'save_assign_room_admin_nonce' ) ) {
							$result = $obj_hostel->mjschool_assign_room( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
							if ( $result ) {
								$action_post = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
								$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
								if ( $action_post === 'view_assign_room' ) {
									wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=roomlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=assign_success' ) );
									die();
								} elseif ( $action_post === 'view_assign_bed' ) {
									wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=bedlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=assign_success' ) );
									die();
								}
							}
						}
					}
					// ASSIGN BED DELETE FLOW.
					if ( $action === 'delete_assign_bed' ) {
						$room_id    = isset( $_REQUEST['room_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) ) ) : 0;
						$bed_id     = isset( $_REQUEST['bed_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['bed_id'] ) ) ) ) : 0;
						$student_id = isset( $_REQUEST['student_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['student_id'] ) ) ) ) : 0;
						$result     = $obj_hostel->mjschool_delete_assigned_bed( $room_id, $bed_id, $student_id );
						if ( $result ) {
							$hostel_id_param = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
							wp_safe_redirect( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=roomlist&hostel_id=' . rawurlencode( $hostel_id_param ) . '&room_message=assign_delete_success' ) );
							die();
						}
					}
					$bed_data = array();
					if ( $action === 'view_assign_room' ) {
						$room_id  = isset( $_REQUEST['room_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['room_id'] ) ) ) ) : 0;
						$bed_data = $obj_hostel->mjschool_get_all_bed_by_room_id( $room_id );
					}
					if ( $action === 'view_assign_bed' ) {
						$bed_id     = isset( $_REQUEST['bed_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['bed_id'] ) ) ) ) : 0;
						$bed_data[] = $obj_hostel->mjschool_get_bed_by_id( $bed_id );
					}
					$hostel_id   = isset( $_REQUEST['hostel_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) : '';
					$exlude_id   = mjschool_approve_student_list();
					$student_all = get_users(
						array(
							'role'    => 'student',
							'exclude' => $exlude_id,
						)
					);
					foreach ( $student_all as $aa ) {
						$student_id[] = $aa->ID;
					}
					// GET ASSIGNED STUDENT DATA.
					$assign_data = mjschool_all_assign_student_data();
					if ( ! empty( $assign_data ) ) {
						foreach ( $assign_data as $bb ) {
							$student_new_id[] = $bb->student_id;
						}
						$Student_result = array_diff( $student_id, $student_new_id );
					} else {
						$Student_result = $student_id;
					}
					?>
					<div class="mjschool-panel-body"><!-- start mjschool-panel-body. -->
						<?php
						$i = 0;
						if ( ! empty( $bed_data ) ) {
							foreach ( $bed_data as $data ) 
							{
								$student_data = mjschool_student_assign_bed_data( $data->id );
								?>
								<form name="mjschool-bed-form" action="" method="post" class="mjschool-form-horizontal" id="bed_form_new">
									<input type="hidden" name="room_id_new[]" value="<?php echo esc_attr( intval( $data->room_id ) ); ?>">
									<input type="hidden" name="bed_id[]" value="<?php echo esc_attr( intval( $data->id ) ); ?>">
									<input type="hidden" name="action" value="<?php echo esc_attr( $action ); ?>">
									<input type="hidden" name="hostel_id" value="<?php echo esc_attr( $hostel_id ); ?>">
									<div class="form-body mjschool-user-form mt-2" id="mjschool-main-assign-room"> <!--Card Body div-->
										<div class="row">
											<div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
												<div class="form-group input">
													<div class="col-md-12 form-control">
														<input id="bed_unique_id_<?php echo esc_attr( intval( $i ) ); ?>" class="form-control validate[required]" type="text" value="<?php echo esc_attr( $data->bed_unique_id ); ?>" name="bed_unique_id[]" readonly>
														<label  for="bed_unique_id"><?php esc_html_e( 'Bed Unique ID', 'mjschool' ); ?><span class="mjschool-require-field"></span></label>
													</div>
												</div>
											</div>
											<?php
											if ( ! empty( $student_data ) ) {
												$new_class_var = '';
											} else {
												$new_class_var = 'new_class_var';
											}
											?>
											<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 input">
												<select name="student_id[]" id="students_list_<?php echo esc_attr( intval( $i ) ); ?>" data-index="<?php echo esc_attr( intval( $i ) ); ?>" class="form-control validate[required] select_student student_check <?php echo esc_attr( $new_class_var ); ?> students_list_<?php echo esc_attr( intval( $i ) ); ?>">
													<?php
													if ( ! empty( $student_data ) ) {
														$roll_no  = get_user_meta( $student_data->student_id, 'roll_id', true );
														$class_id = get_user_meta( $student_data->student_id, 'class_name', true );
														?>
														<option value="<?php echo esc_attr( intval( $student_data->student_id ) ); ?>"><?php echo esc_html( mjschool_get_display_name( $student_data->student_id ) ) . ' ( ' . esc_html( $roll_no ) . ' ) ( ' . esc_html( mjschool_get_class_name( $class_id ) ) . ' )'; ?></option>
														<?php
													} else {
														?>
														<option value="0"><?php esc_html_e( 'Select Student', 'mjschool' ); ?></option>
														<?php
														foreach ( $Student_result as $student ) {
															$roll_no  = get_user_meta( $student, 'roll_id', true );
															$class_id = get_user_meta( $student, 'class_name', true );
															?>
															<option value="<?php echo esc_attr( intval( $student ) ); ?>"><?php echo esc_html( mjschool_get_display_name( $student ) ) . ' ( ' . esc_html( $roll_no ) . ' ) ( ' . esc_html( mjschool_get_class_name( $class_id ) ) . ' )'; ?></option>
															<?php
														}
													}
													?>
												</select>
											</div>
											<?php
											if ( ! empty( $student_data ) ) {
												?>
												<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
													<div class="form-group input">
														<div class="col-md-12 form-control">
															<input id="assign_date_<?php echo esc_attr( intval( $i ) ); ?>"  value="<?php echo esc_attr( mjschool_get_date_in_input_box( $student_data->assign_date ) ); ?>" class="form-control" type="text" name="assign_date[]" readonly>
														</div>
													</div>
												</div>
												<?php
											} else {
												?>
												<div class="col-sm-12 col-md-2 col-lg-2 col-xl-2">
													<div class="form-group input">
														<div class="col-md-12 col-sm-12 col-xs-12 form-control assigndate_<?php echo esc_attr( intval( $i ) ); ?>" id="assigndate_<?php echo esc_attr( intval( $i ) ); ?>" name="assigndate">
															<input id="assign_date_<?php echo esc_attr( intval( $i ) ); ?>" placeholder="<?php esc_attr_e( 'Enter Date', 'mjschool' ); ?>" class="datepicker form-control text-input mjschool-placeholder-color" type="text" name="assign_date[]" autocomplete="off" value="<?php echo esc_attr( mjschool_get_date_in_input_box( date( 'Y-m-d' ) ) ); ?>">
														</div>
													</div>
												</div>
												<?php
											}
											if ( $student_data ) {
												?>
												<div class="col-md-2 col-sm-2 col-xs-12 input">
													<label class="col-md-2 col-sm-2 col-xs-12 control-label mjschool-occupied col-form-label mjschool-occupied-available-btn" for="available"><?php esc_html_e( 'Occupied', 'mjschool' ); ?></label>
												</div>
												<div class="col-md-2 col-sm-2 col-xs-12 input">
													<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&tab1=assign_bed&action=delete_assign_bed&hostel_id=' . rawurlencode( $hostel_id ) . '&room_id=' . rawurlencode( mjschool_encrypt_id( $data->room_id ) ) . '&bed_id=' . rawurlencode( mjschool_encrypt_id( $data->id ) ) . '&student_id=' . rawurlencode( mjschool_encrypt_id( $student_data->student_id ) ) ) ); ?>" class="btn btn-danger delete_btn" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to vacant this bed?', 'mjschool' ); ?>' );"><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
												</div>
												<?php
											} else {
												?>
												<div class="col-md-2 col-sm-2 col-xs-12 input">
													<label class="col-md-2 col-sm-2 col-xs-12 control-label mjschool-available col-form-label mjschool-occupied-available-btn" for="available"><?php esc_html_e( 'Available', 'mjschool' ); ?></label>
												</div>
												<?php
											}
											?>
										</div>
									</div>
									<!-- </div> -->
									 <?php
									++$i;
								}?>
								<?php wp_nonce_field( 'save_assign_room_admin_nonce' ); ?>
								<div class="form-body mjschool-user-form">
									<div class="row">
										<div class="col-sm-6">
											<input type="submit" id="Assign_bed" value="<?php esc_attr_e( 'Assign Bed', 'mjschool' ); ?>" name="assign_room" class="btn btn-success mjschool-save-btn mjschool-assign-room-for-alert" />
										</div>
									</div>
								</div>
							</form>
							<?php
						} else {
							?>
							<h4 class="mjschool-require-field mjschool-margin-top-10px" ><?php esc_html_e( 'No Bed Available', 'mjschool' ); ?></h4>
							<?php
						}
						?>
					</div><!-- End mjschool-panel-body. -->
					<?php
				}
				?>
			</div>
		</section>
	</div>
</div>