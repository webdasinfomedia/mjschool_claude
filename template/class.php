<?php

/**
 * Class and Section Management Page.
 *
 * This file serves as the main view/controller for managing **Classes and their Sections**
 * within the Mjschool dashboard. It integrates front-end display logic with back-end
 * management functions.
 *
 * It is primarily responsible for:
 *
 * 1. **Configuration Checks**: Retrieving school-wide options like `mjschool_custom_class`
 * and `mjschool_class_room` to determine which features to display (e.g., custom class names,
 * class room association).
 * 2. **Access Control**: Implementing **role-based access control** by fetching the current
 * user's role and corresponding access rights (`$user_access`) to control 'view', 'add',
 * 'edit', and 'delete' operations.
 * 3. **Custom Fields Integration**: Initializing the `Mjschool_Custome_Field` object to fetch
 * and display any custom fields associated with the 'class' module.
 * 4. **Class List Display**: Rendering a list of classes in a tabular format (using jQuery
 * DataTables for features like responsiveness and sorting). The list shows class details and
 * an action dropdown for viewing student lists.
 * 5. **Data Retrieval**: Fetching all classes and iterating through them to display relevant
 * information, including:
 * - Class image.
 * - Class name.
 * - Class Teacher's name.
 * - Number of Students in the class.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */

defined('ABSPATH') || exit;
$school_type = get_option( 'mjschool_custom_class' );
$cust_class_room = get_option( 'mjschool_class_room' );
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$mjschool_role_name                 = mjschool_get_user_role( get_current_user_id() );
$user_access               = mjschool_get_user_role_wise_access_right_array();
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'class';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );

?>
<?php
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$active_tab = isset( $_REQUEST['tab'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'classlist';
// --------------- Access-wise role. -----------//
if ( isset( $_REQUEST['page'] ) ) {
	if ( $user_access['view'] === 0 ) {
		mjschool_access_right_page_not_access_message();
		die();
	}
	if ( ! empty( $_REQUEST['action'] ) ) {
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
			if ( $user_access['edit'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
			if ( $user_access['delete'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
		if ( isset( $_REQUEST['page'] ) && sanitize_text_field(wp_unslash($_REQUEST['page'])) === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
			if ( $user_access['add'] === 0 ) {
				mjschool_access_right_page_not_access_message();
				die();
			}
		}
	}
}
// ------------ Save class form. --------------//
if ( isset( $_POST['save_class'] ) ) {

	$nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';

	if ( wp_verify_nonce( $nonce, 'save_class_admin_nonce' ) ) {

		$class_name       = isset($_POST['class_name']) ? sanitize_textarea_field( wp_unslash( $_POST['class_name'] ) ) : '';
		$class_num_name   = isset($_POST['class_num_name']) ? sanitize_text_field( wp_unslash( $_POST['class_num_name'] ) ) : '';
		$class_capacity   = isset($_POST['class_capacity']) ? sanitize_text_field( wp_unslash( $_POST['class_capacity'] ) ) : '';
		$class_description = isset($_POST['class_description']) ? sanitize_text_field( wp_unslash( $_POST['class_description'] ) ) : '';
		$academic_year    = isset($_POST['academic_year']) ? sanitize_text_field( wp_unslash( $_POST['academic_year'] ) ) : '';
		$created_date     = date( 'Y-m-d H:i:s' );

		$classdata = array(
			'class_name'        => $class_name,
			'class_num_name'    => $class_num_name,
			'class_capacity'    => $class_capacity,
			'class_description' => $class_description,
			'academic_year'     => $academic_year,
			'creater_id'        => get_current_user_id(),
			'created_date'      => $created_date
		);

		$tablename = 'mjschool_class';

		if ( isset($_REQUEST['action']) && sanitize_text_field( wp_unslash($_REQUEST['action']) ) === 'edit' ) {

			$edit_nonce = isset($_GET['_wpnonce_action']) ? $_GET['_wpnonce_action'] : '';
			if ( wp_verify_nonce( $edit_nonce, 'edit_action' ) ) {

				$class_id = isset($_REQUEST['class_id']) ? intval( mjschool_decrypt_id( $_REQUEST['class_id'] ) ) : 0;
				$classid  = array( 'class_id' => $class_id );
				$existing_class = mjschool_check_existing_class($class_name, $class_num_name, $class_id);
				if ( $existing_class ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&&tab=addclass&message=4' ) );
					exit;
				}

				$result = mjschool_update_record( $tablename, $classdata, $classid );

				// Update custom field data
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module = 'class';
				$mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $class_id );

				if ( $result ) {
					wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class&tab=classlist&message=2' ) );
					exit;
				}

			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}

		} else {
			// Add new class
			$existing_class = mjschool_get_existing_class($class_name, $class_num_name);
			if ( $existing_class ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&&tab=addclass&message=4' ) );
				exit;
			}

			$result = mjschool_insert_record( $tablename, $classdata );
			$last_insert_id = $wpdb->insert_id;

			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module = 'class';
			$mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );

			if ( $result ) {
				wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class&tab=classlist&message=1' ) );
				exit;
			}
		}

	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}

// -------------- Delete selected class. -----------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$tablename = 'mjschool_class';
			$result    = mjschool_delete_class( $tablename, $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class&tab=classlist&message=3' ) );
		die();
	}
}
// ------------ Delete class. ----------------//
if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'delete_action' ) ) {
		$tablename = 'mjschool_class';
		$result    = mjschool_delete_class( $tablename, mjschool_decrypt_id( wp_unslash($_REQUEST['class_id'] ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=class&tab=classlist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_GET['message'] ) && sanitize_text_field( wp_unslash( $_GET['message'] ) ) === 1 ) {
	 ?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
			<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
		</button>
		<?php esc_html_e( 'Class Added Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message'] ) && sanitize_text_field( wp_unslash( $_GET['message'] ) ) === 2 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
			<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
		</button>
		<?php esc_html_e( 'Class Updated Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
if ( isset( $_GET['message'] ) && sanitize_text_field( wp_unslash( $_GET['message'] ) ) === 3 ) {
	?>
	<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
		<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close">
			<span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/mjschool-close.png' ); ?>"></span>
		</button>
		
		<?php esc_html_e( 'Class Deleted Successfully.', 'mjschool' ); ?>
	</div>
	<?php
}
?>
<!-- Nav tabs. -->
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res">
	<?php
	// ------------- Active tab class list. -------------//
	if ( $active_tab === 'classlist' ) {
		$tablename = 'mjschool_class';
		$user_id   = get_current_user_id();
		$own_data  = $user_access['own_data'];
		// ------- Exam data for teacher.. ---------//
		if ( $school_obj->role === 'teacher' ) {
			if ( $own_data === '1' ) {
				$class_id       = get_user_meta( get_current_user_id(), 'class_name', true );
				$retrieve_class_data = mjschool_get_all_class_data_by_class_array( $class_id );
			} else {
				$retrieve_class_data = mjschool_get_all_data( $tablename );
			}
		}
		// ------- Exam data for support staff.. ---------//
		elseif ( $own_data === '1' ) {
			$retrieve_class_data = mjschool_get_all_class_created_by_user( $user_id );
		} else {
			$retrieve_class_data = mjschool_get_all_data( $tablename );
		}
		if ( ! empty( $retrieve_class_data ) ) {
			?>
			<div class="mjschool-panel-body"><!--------------- Panel body. ------------->
				<div class="table-responsive"><!--------------- Table responsive. ----------->
					<!----------- Class list form start. ---------->
					<form id="mjschool-common-form" name="mjschool-common-form" method="post">
						<table id="mjschool-class-list-frontend" class="display dataTable mjschool-exam-datatable" cellspacing="0" width="100%">
							<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
								<tr>
									<?php
									if ( $mjschool_role_name === 'supportstaff' ) {
										?>
										<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
										<?php
									}
									?>
									<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
									<?php if ( $school_type === 'school' ) {?>
										<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
									<?php } ?>
									<?php if ( $school_type === 'university' ) {?>
										<th><?php esc_html_e( 'Academy year', 'mjschool' ); ?></th>
									<?php } ?>
									<th><?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?></th>
									<th><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?></th>
									<?php if ( $cust_class_room === 1) {?>
										<th><?php esc_html_e( 'Assign Room', 'mjschool' ); ?></th>
									<?php } ?>
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
									?>
									<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $retrieve_class_data as $retrieved_data ) {
									$class_id     = $retrieved_data->class_id;
									$section_id   = mjschool_get_section_by_class_id( $class_id );
									$section_name = '';
									?>
									<tr>
										<?php
										if ( $mjschool_role_name === 'supportstaff' ) {
											?>
											<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->class_id ); ?>"></td>
											<?php
										}
										?>
										<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-class.png"); ?>" class="img-circle" /></td>
										<td>
											<a href="?dashboard=mjschool_user&page=class&tab=class_details&class_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->class_id ) ); ?>">
												<?php
												if ( $retrieved_data->class_name ) {
													echo esc_html( $retrieved_data->class_name );
												} else {
													esc_html_e( 'Not Provided', 'mjschool' );
												}
												?>
											</a>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
										</td>
										<?php if ( $school_type === 'school' ) { ?>
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
										<?php }
										if ( $school_type === "university"){ ?>
											<td>
												<?php if ($retrieved_data->academic_year) {
													echo esc_html( $retrieved_data->academic_year);
												} else {
													esc_html_e( 'N/A', 'mjschool' );
												} ?> 
												<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Academy Year', 'mjschool' ); ?>"></i>
											</td>
										<?php } ?>
										<td>
											<?php
											if ( $retrieved_data->class_num_name ) {
												echo esc_html( $retrieved_data->class_num_name );
											} else {
												esc_html_e( 'N/A', 'mjschool' );
											}
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Numeric Value', 'mjschool' ); ?>"></i>
										</td>
										<?php
										
										$mjschool_user = count(get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id ) ) );
										
										?>
										<td>
											<?php
											echo esc_html( $mjschool_user ) . ' ';
											esc_html_e( 'Out Of', 'mjschool' );
											echo ' ' . esc_html( $retrieved_data->class_capacity );
											?>
											<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Capacity', 'mjschool' ); ?>"></i>
										</td>
										<?php
										if ($cust_class_room === 1) 
										{ ?>
											<td>
												<?php 
													$class_room = mjschool_get_assign_class_room_for_single_class($class_id);
													if ( $class_room) 
													{
														$roomname = "";
														foreach ($class_room as $roomdata) 
														{
															$roomname .= $roomdata->room_name . ", ";
														}
														$roomname_rtrim = rtrim($roomname, ", ");
														$roomname_ltrim = ltrim($roomname_rtrim, ", ");
														if ( ! empty( $roomname_ltrim ) ) 
														{
															echo wp_kses_post($roomname_ltrim);
														} 
														else 
														{
															esc_html_e( 'N/A', 'mjschool' );
														}
													} 
													else 
													{
														esc_html_e( 'N/A', 'mjschool' );
													} 
												?>
												<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Assign Room', 'mjschool' ); ?>"></i>
											</td>
											<?php 
										}
										// Custom Field Values.
										if ( ! empty( $user_custom_field ) ) {
											foreach ( $user_custom_field as $custom_field ) {
												if ( $custom_field->show_in_table === '1' ) {
													$module             = 'class';
													$custom_field_id    = $custom_field->id;
													$module_record_id   = $retrieved_data->class_id;
													$custom_field_value = $mjschool_custom_field_obj->mjschool_get_single_custom_field_meta_value( $module, $module_record_id, $custom_field_id );
													if ( $custom_field->field_type === 'date' ) {
														?>
														<td>
															<?php
															if ( ! empty( $custom_field_value ) ) {
																echo esc_html( mjschool_get_date_in_input_box( $custom_field_value ) );
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
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
																<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value )); ?>" download="CustomFieldfile">
																	<button class="btn btn-default view_document" type="button"> <i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																</a>
																<?php
															} else {
																esc_html_e( 'Not Provided', 'mjschool' );
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
																esc_html_e( 'Not Provided', 'mjschool' );
															}
															?>
														</td>
														<?php
													}
												}
											}
										}
										?>
										<td class="action">
											<div class="mjschool-user-dropdown">
												<ul class="mjschool_ul_style">
													<li>
														<a href="#" data-bs-toggle="dropdown" aria-expanded="false">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
														</a>
														<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
															<li class="mjschool-float-left-width-100px">
																<a class="mjschool-float-left-width-100px" href="?dashboard=mjschool_user&page=class&tab=class_details&class_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->class_id ) ); ?>"><i class="fa fa-eye"> </i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
															</li>
															<?php
															if ( $school_type === 'school' ) {
																if ($user_access['add'] === '1' ) {
																	?>
																	<li class="mjschool-float-left-width-100px">
																		<a class="mjschool-float-left-width-100px" href="?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->class_id ) ); ?>"><i class="fa fa-plus"> </i><?php esc_html_e( 'Add Section', 'mjschool' ); ?></a>
																	</li>
																	<?php
																}
															}
															if ($user_access['edit'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																	<a href="?dashboard=mjschool_user&page=class&tab=addclass&action=edit&class_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->class_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																</li>
																<?php
															}
															if ($user_access['delete'] === '1' ) {
																?>
																<li class="mjschool-float-left-width-100px">
																	<a href="?dashboard=mjschool_user&page=class&tab=classlist&action=delete&class_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->class_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?> </a>
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
								}
								?>
							</tbody>
						</table>
						<?php
						if ($mjschool_role_name === "supportstaff") { ?>
							<div class="mjschool-print-button pull-left">
								<button class="mjschool-btn-sms-color mjschool-button-reload">
									<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjschool_width_0px mjchool_margin_top_0px" value="" >
									<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
								</button>
								<?php
								if ($user_access['delete'] === '1' ) {
									?>
									<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									<?php
								}
								?>
							</div>
							<?php
						} ?>
					</form>
				</div><!------------- Table responsive. ------------------>
			</div><!------------- Panel body. ----------------->
			<?php
		} else {
			if ($user_access['add'] === '1' ) { ?>
				<div class="mjschool-no-data-list-div mjschool-no-data-img-mt-30px">
					<a href="<?php echo esc_url(home_url( '?dashboard=mjschool_user&page=class&tab=addclass') ); ?>">
						<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
					</a>
					<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
						<label class="mjschool-no-data-list-label">
							<?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?>
						</label>
					</div>
				</div>
				<?php
			} else { ?>
				<div class="mjschool-calendar-event-new">
					<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
				</div>
				<?php 
			}
		}
	}
	// ------------- Active tab add class form. -------------//
	if ( $active_tab === 'addclass' ) {
		$edit = 0;
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$edit      = 1;
			$classdata = mjschool_get_class_by_id( mjschool_decrypt_id( wp_unslash($_REQUEST['class_id'] ) ) );
		}
		?>
		<div class="mjschool-panel-body"><!-------- Panel body. -------->
			<form name="class_form" action="" method="post" class="mjschool-form-horizontal" enctype="multipart/form-data" id="class_form"><!------- form Start --------->
				<?php $mjschool_action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'insert'; ?>
				<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
				<div class="header">
					<h3 class="mjschool-first-header">
						<?php esc_html_e( 'Class Information', 'mjschool' ); ?>
					</h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="mjschool_class_name" class="form-control validate[required,custom[popup_category_validation]]" maxlength="50" type="text" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_name ); } ?>" name="class_name">
									<label for="mjschool_class_name"> <?php esc_html_e( 'Class Name', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php if ( $school_type === 'university' ) {?>
							<div class="col-md-6">
								<div class="form-group input">
									<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
										<div class="form-field">
											<textarea name="class_description" class="mjschool-textarea-height-47px form-control" maxlength="150"><?php if ( $edit){ echo esc_attr($classdata->class_description);}?></textarea>
											<span class="mjschool-txt-title-label"></span>
											<label class="text-area address active"><?php esc_html_e( 'Description', 'mjschool' ); ?></label>
										</div>
									</div>
								</div>
							</div>
						<?php } ?>
						<div class="col-md-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_num_name" class="form-control validate[required,min[1],maxSize[4]] text-input" oninput="this.value = Math.abs(this.value)" type="number" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_num_name ); } ?>" name="class_num_name">
									<label for="class_num_name"> <?php esc_html_e( 'Class Numeric Name', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php wp_nonce_field( 'save_class_admin_nonce' ); ?>
						<div class="col-md-6">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="class_capacity" oninput="this.value = Math.abs(this.value)" class="form-control validate[required, min[1],maxSize[4]]" type="number" value="<?php if ( $edit ) { echo esc_attr( $classdata->class_capacity ); } ?>" name="class_capacity">
									<label for="class_capacity"> <?php esc_html_e( 'Student Capacity', 'mjschool' ); ?><span class="required">*</span> </label>
								</div>
							</div>
						</div>
						<?php
						if ( $school_type === "university"){ 
							$current_year = date( "Y");
							$selected_academic_year = $edit ? $classdata->academic_year : '';
							?>
							<div class="col-md-6 input">
								<label for="academic_year" class="mjschool-custom-top-label mjschool-lable-top top"><?php esc_html_e( 'Academic Year', 'mjschool' ); ?><span class="required">*</span></label>
								<select name="academic_year" id="academic_year" class="form-control validate[required]">
									<option value=""><?php esc_attr_e( 'Select Academic Year', 'mjschool' ); ?></option>
									<?php for ($i = 0; $i <= 5; $i++) {
										$start_year = $current_year + $i;
										$end_year = $start_year + 1;
										$year_range = $start_year . ' - ' . $end_year;
										?>
										<option value="<?php echo esc_attr($year_range); ?>" <?php selected($selected_academic_year, $year_range); ?>>
											<?php echo esc_html( $year_range); ?>
										</option>
									<?php } ?>
								</select>
							</div>
						<?php } ?>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'class';
				$custom_field              = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module_callback( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
							<input type="submit" value="<?php if ( $edit ) { esc_html_e( 'Save Class', 'mjschool' ); } else { esc_html_e( 'Add Class', 'mjschool' ); } ?>" name="save_class" class="mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form> <!------- Form end. --------->
		</div><!-------- Panel body. -------->
		<?php
	}
	// ------------- Class details tabs. ------------------//
	if ( $active_tab === 'class_details' ) {
		$class_id                  = intval( mjschool_decrypt_id( wp_unslash($_REQUEST['class_id'] ) ) );
		$classdata                 = mjschool_get_class_by_id( $class_id );
		$mjschool_custom_field_obj = new Mjschool_Custome_Field();
		$active_tab1               = isset( $_REQUEST['tab1'] ) ? $_REQUEST['tab1'] : 'general';
		?>
		<div class="mjschool-panel-body mjschool-view-page-main"><!--  Start panel body div.-->
			<div class="content-body"><!-- Start content body div.-->
				<!-- Detail page header start. -->
				<section id="mjschool-user-information">
					<div class="mjschool-view-page-header-bg">
						<div class="row">
							<div class="col-xl-10 col-md-9 col-sm-10">
								<div class="mjschool-user-profile-header-left mjschool-float-left-width-100px">
									<img class="mjschool-user-view-profile-image" src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/thumb-icon/mjschool-class.png"); ?>">
									<div class="row mjschool-profile-user-name">
										<div class="mjschool-float-left mjschool-view-top1">
											<div class="col-xl-12 col-md-12 col-sm-12 mjschool-float-left-width-100px">
												<span class="mjschool-view-user-name-label"><?php echo esc_html( $classdata->class_name); ?></span>
												<?php
												if ($user_access['edit'] === '1' ) {
													?>
													<div class="mjschool-view-user-edit-btn">
														<a class="mjschool-color-white mjschool-margin-left-2px" href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=addclass&action=edit&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-edit.png"); ?>">
														</a>
													</div>
													<?php
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-2 col-lg-3 col-md-3 col-sm-2 mjschool-add-btn_possition_teacher_res">
								<div class="mjschool-group-thumbs">
									<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-group.png"); ?>">
								</div>
							</div>
							
						</div>
					</div>
				</section>
				<section id="body_area" class="teacher_view_tab body_areas">
					<div class="row">
						<div class="col-xl-12 col-md-12 col-sm-12 mjschool-rs-width">
							<ul class="nav nav-tabs mjschool-panel-tabs mjschool-flex-nowrap mjschool-margin-left-1per" role="tablist">
								<li class="<?php if ( $active_tab1 === 'general' ) { ?> active<?php } ?>">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=general&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) ) ); ?>"
 class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'general' ? 'active' : ''; ?>"> <?php esc_html_e( 'GENERAL', 'mjschool' ); ?></a>
								</li>
								<?php if ( $school_type != 'university' ) {?>
									<li class="<?php if ( $active_tab1 === 'section_list' ) { ?> active<?php } ?>">
										<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) ) ); ?>" class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'section_list' ? 'active' : ''; ?>"> <?php esc_html_e( 'Section', 'mjschool' ); ?></a>
									</li>
								<?php }?>
								<li class="<?php if ( $active_tab1 === 'student_list' ) { ?> active<?php } ?>">
									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=student_list&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) ) ); ?>"
 class="mjschool-padding-left-0 tab <?php echo esc_attr( $active_tab1  ) === 'student_list' ? 'active' : ''; ?>"> <?php esc_html_e( 'Student', 'mjschool' ); ?></a>
								</li>
							</ul>
						</div>
					</div>
				</section>
				<section id="mjschool-body-content-area">
					<div class="mjschool-panel-body"><!--  Start panel body div.-->
						<?php
						$section_success = isset( $_REQUEST['section_success'] ) ? $_REQUEST['section_success'] : '0';
						switch ( $section_success ) {
							case 'insert_success':
								$message_string = esc_html__( 'Section Added Successfully.', 'mjschool' );
								break;
							case 'edit_success':
								$message_string = esc_html__( 'Section Updated Successfully.', 'mjschool' );
								break;
							case 'delete_success':
								$message_string = esc_html__( 'Section Deleted Successfully.', 'mjschool' );
								break;
							case 'exist':
								$message_string = esc_html__( 'This Section is already exist in this Class.', 'mjschool' );
								break;
						}
						
						if ($section_success) { ?>
							<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
								<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
								<?php echo esc_html( $message_string); ?>
							</div>
							<?php
						}
						//--- General tab start. ----//
						if ($active_tab1 === "general") {
							?>
							<div class="row">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
										<div class="mjschool-guardian-div">
											<label class="mjschool-view-page-label-heading"> <?php esc_html_e( 'Class Information', 'mjschool' ); ?> </label>
											<div class="row">
												<div class="row mjschool-margin-top-15px mjschool-margin-left-3">
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels">
															<?php esc_html_e( 'Class Name', 'mjschool' ); ?>
														</label><br />
														<?php if ($user_access['edit'] === '1' && empty($classdata->class_name ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id($classdata->class_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url($edit_url) . '">Add</a>';
														} else { ?>
															<label class="mjschool-view-page-content-labels">
																<?php echo esc_html( ucfirst($classdata->class_name ) ); ?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels">
															<?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?>
														</label><br />
														<?php if ($user_access['edit'] === '1' && empty($classdata->class_num_name ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id($classdata->class_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url($edit_url) . '">Add</a>';
														} else { ?>
															<label class="mjschool-view-page-content-labels"><?php echo esc_html( $classdata->class_num_name); ?></label>
														<?php } ?>
													</div>
													<?php
													$class_id = $classdata->class_id;
													$mjschool_user = count(get_users(array(
														'meta_key' => 'class_name',
														'meta_value' => $class_id
													 ) ) );
													?>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels">
															<?php esc_html_e( 'Student Capacity', 'mjschool' ); ?>
														</label><br />
														<?php if ($user_access['edit'] === '1' && empty($classdata->class_capacity ) ) {
															$edit_url = home_url( '?dashboard=mjschool_user&page=class&tab=addclass&action=edit&class_id=' . esc_attr( mjschool_encrypt_id($classdata->class_id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) );
															echo '<a class="btn btn-primary mjschool-view-add-buttons btn-sm" href="' . esc_url($edit_url) . '">Add</a>';
														} else { ?>
															<label class="mjschool-view-page-content-labels">
																<?php echo esc_html( $mjschool_user) . ' '; esc_html_e( 'Out Of', 'mjschool' ); echo ' ' . esc_html( $classdata->class_capacity); ?>
															</label>
														<?php } ?>
													</div>
													<div class="col-xl-4 col-md-4 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels">
															<?php esc_html_e( 'Class Section', 'mjschool' ); ?>
														</label><br />
														<label class="mjschool-view-page-content-labels">
															<?php
															$section_id = mjschool_get_section_by_class_id($class_id);
															$section_name = "";
															foreach ($section_id as $section) {
																$section_name .= $section->section_name . ", ";
															}
															$section_name_rtrim = rtrim($section_name, ", ");
															$section_name_ltrim = ltrim($section_name_rtrim, ", ");
															if ( ! empty( $section_name_ltrim ) ) {
																echo esc_html( $section_name_ltrim);
															} else {
																esc_html_e( 'No Section', 'mjschool' );
															}
															?>
														</label>
													</div>
													<div class="col-xl-8 col-md-8 col-sm-12 mjschool-margin-bottom-10-res">
														<label class="mjschool-view-page-header-labels">
															<?php esc_html_e( 'Class Teachers', 'mjschool' ); ?>
														</label><br />
														<label class="mjschool-view-page-content-labels">
															<?php
															$teachers = mjschool_get_teacher_by_class_id($class_id);
															$teacher_name = "";
															foreach ($teachers as $teacher_data) {
																$teacher_name .= ucfirst($teacher_data->display_name) . ", ";
															}
															$teacher_name_rtrim = rtrim($teacher_name, ", ");
															$teacher_name_ltrim = ltrim($teacher_name_rtrim, ", ");
															if ( ! empty( $teacher_name_ltrim ) ) {
																echo esc_html( $teacher_name_ltrim);
															} else {
																esc_html_e( 'No Teachers', 'mjschool' );
															}
															?>
														</label>
													</div>
												</div>
											</div>
										</div>
									</div>
									<?php
									$module = 'class';
									$mjschool_custom_field_obj->mjschool_show_inserted_customfield_data_in_datail_page($module);
									?>
								</div>
							</div>
							<?php
						}
						// Student list tab start.
						if ($active_tab1 === "section_list") {
							?>
							<div class="row">
								<div class="col-xl-12 col-md-12 col-sm-12">
									<?php
									// Insert section data.
									if ( isset( $_POST['save_class_section'] ) ) {
										if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_class_section_nonce' ) ) {
											wp_die(esc_html( 'Invalid request. Please try again.', 'mjschool' ) );
										}
										$section = sanitize_text_field( sanitize_text_field( wp_unslash( $_POST['section_name'] ) ) );
										$section_id = isset($_POST['section_id']) ? intval( sanitize_text_field( wp_unslash($_POST['section_id'] ) ) ) : null;
										$mjschool_action = isset($_REQUEST['action']) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : null;
										$existing_section = mjschool_get_section_id_by_section_name($section, $class_id);
										$sectiondata = [
											'class_id' => $class_id,
											'section_name' => $section,
										];
										if ($mjschool_action === 'edit_section' ) {
											if ( isset( $_GET['_wpnonce_action']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ), 'edit_action' ) ) {
												// Update section code.
												if (empty($existing_section) || $existing_section->id === $section_id) {
													// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
													$result = $wpdb->update($class_section_table, $sectiondata, ['id' => $section_id]);
													wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=' . mjschool_encrypt_id($class_id) . '&section_success=edit_success' ) );
													die();
												} else {
													wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&action=edit_section&class_id=' . mjschool_encrypt_id($class_id) . '&section_id=' . wp_unslash($_POST['section_id']) . '&section_success=exist' ) );
													die();
												}
											} else {
												wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
											}
										} else {
											// Add new section code.
											if (empty($existing_section ) ) {
												// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
												$result = $wpdb->insert($class_section_table, $sectiondata);
												if ($result) {
													wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=' . mjschool_encrypt_id($class_id) . '&section_success=insert_success' ) );
													die();
												}
											} else {
												wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=' . mjschool_encrypt_id($class_id) . '&section_success=exist' ) );
												die();
											}
										}
									}
									// Class section delete code.
									if ( isset( $_REQUEST['action']) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === "delete_section") {
										if ( isset( $_GET['_wpnonce_action']) && wp_verify_nonce($_GET['_wpnonce_action'], 'delete_action' ) ) {
											$section_id = intval(mjschool_decrypt_id( wp_unslash( $_REQUEST['section_id'] ) ) );
											$result = mjschool_delete_class_section($section_id);
											if ($result) {
												wp_safe_redirect(home_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&class_id=' . mjschool_encrypt_id($class_id) . '&section_success=delete_success' ) );
												die();
											}
										} else {
											wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
										}
									}
									if (($user_access['add'] === '1' ) || ($user_access['edit'] === '1' && isset($_REQUEST['action']) && ( sanitize_text_field( wp_unslash($_REQUEST['action']) ) === "edit_section" ) ) ) {
										?>
										<div class="col-xl-12 col-md-12 col-sm-12 mjschool-margin-top-20px mjschool-margin-top-15px-rs">
											<div class="mjschool-guardian-div">
												<?php
												$edit = 0;
												if ( isset( $_REQUEST['action']) && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === "edit_section" ) ) {
													$edit = 1;
													$id = intval(mjschool_decrypt_id( wp_unslash( $_REQUEST['section_id'] ) ) );
													$section = mjschool_single_section($id);
												}
												?>
												<form name="class_Section_form" action="" method="post" class="mjschool-form-horizontal" id="class_Section_form">
													<!------- Form Start. --------->
													<?php $mjschool_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'insert'; ?>
													<input type="hidden" name="action" value="<?php echo esc_attr( $mjschool_action ); ?>">
													<input type="hidden" name="section_id" value="<?php if ($edit) { echo esc_attr($id); } ?>" />
													<div class="header">
														<h3 class="mjschool-first-header"> <?php esc_html_e( 'Add Class Section', 'mjschool' ); ?> </h3>
													</div>
													<div class="form-body mjschool-user-form">
														<div class="row">
															<div class="col-md-6">
																<div class="form-group input">
																	<div class="col-md-12 form-control">
																		<input id="section_name" class="form-control validate[required,custom[popup_category_validation,required]" maxlength="50" type="text" value="<?php if ($edit) { echo esc_attr($section->section_name); } ?>" name="section_name">
																		<label for="section_name"> <?php esc_html_e( 'Section Name', 'mjschool' ); ?><span class="required">*</span> </label>
																	</div>
																</div>
															</div>
															<?php wp_nonce_field( 'save_class_section_nonce' ); ?>
															<div class="col-sm-3 col-md-3 col-lg-3 col-xs-12">
																<input type="submit" value="<?php esc_html_e( 'Add Section', 'mjschool' ); ?>" name="save_class_section" class="mjschool-save-btn" />
															</div>
														</div>
													</div>
												</form>
											</div>
										</div>
										<?php
									}
									?>
									<div class="header mt-4">
										<h3 class="mjschool-first-header"> <?php esc_html_e( 'Class Section List', 'mjschool' ); ?> </h3>
									</div>
									<?php
									$retrieve_class_data = mjschool_get_section_by_class_id($class_id);
									if ( ! empty( $retrieve_class_data ) ) {
										?>
										<div class="mjschool-panel-body">
											<div class="table-responsive">
												<form id="mjschool-common-form" name="mjschool-common-form" method="post">
													<table id="frontend_section_list" class="display" cellspacing="0" width="100%">
														<thead class="<?php echo esc_attr( mjschool_datatable_header( ) ) ?>">
															<tr>
																<th><?php esc_html_e( 'Section Name', 'mjschool' ); ?></th>
																<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
																<?php
																if ($user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
																	?>
																	<th class="mjschool-text-align-end"> <?php esc_html_e( 'Action', 'mjschool' ); ?> </th>
																	<?php
																}
																?>
															</tr>
														</thead>
														<tbody>
															<?php
															foreach ($retrieve_class_data as $retrieved_data) {
																?>
																<tr>
																	<td>
																		<?php echo esc_html( $retrieved_data->section_name); ?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section Name', 'mjschool' ); ?>"></i>
																	</td>
																	<td>
																		<?php if ( ! empty( $retrieved_data->class_id ) ) {
																			echo esc_html( mjschool_get_class_name_by_id($retrieved_data->class_id ) );
																		} else {
																			esc_html_e( 'Not Provided', 'mjschool' );
																		} ?>
																		<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
																	</td>
																	<?php
																	if ($user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
																		?>
																		<td class="action">
																			<div class="mjschool-user-dropdown">
																				<ul  class="mjschool_ul_style">
																					<li >
																						<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																							<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																						</a>
																						<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																							<?php
																							if ($user_access['edit'] === '1' ) {
																								?>
																								<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&action=edit_section&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) . '&section_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i>
																										<?php esc_html_e( 'Edit', 'mjschool' ); ?>
																									</a>
																								</li>
																								<?php
																							}
																							if ($user_access['delete'] === '1' ) {
																								?>
																								<li class="mjschool-float-left-width-100px">
																									<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=class&tab=class_details&tab1=section_list&action=delete_section&class_id=' . esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) . '&section_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i>
																										<?php esc_html_e( 'Delete', 'mjschool' ); ?>
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
																		<?php
																	}
																	?>
																</tr>
																<?php
																$i++;
															}
															?>
														</tbody>
													</table>
												</form>
											</div>
										</div>
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
							<?php
						}
						// Student list tab start.
						if ( $active_tab1 === 'student_list' ) {
							if ( $school_type === "school")
							{
								?>
								<form method="post">
									<input type="hidden" name="security" id="mjschool_nonce" value="<?php echo esc_attr( wp_create_nonce( 'mjschool_student_filter_nonce' ) ); ?>">
									<div class="form-body mjschool-user-form">
										<div class="row">
											<div class="col-md-4 input">
												<label class="ml-1 mjschool-custom-top-label top" for="filter_section_id"> <?php esc_html_e( 'Select Section', 'mjschool' ); ?> </label>
												<select id="filter_section_id" name="filter_section_id" class="mjschool-line-height-30px form-control class_id_exam validate[required]">
													<option value="all_section"><?php esc_html_e( 'All Section', 'mjschool' ); ?></option>
													<?php
													$section    = mjschool_get_class_sections( $class_id );
													$section_id = '';
													if ( isset( $_REQUEST['filter_section_id'] ) ) {
														$section_id = sanitize_text_field( wp_unslash( $_REQUEST['filter_section_id'] ) );
													}
													foreach ( $section as $section_data ) {
														?>
														<option value="<?php echo esc_attr( $section_data->id ); ?>" <?php selected( $section_data->id, $section_id ); ?> >
															<?php echo esc_html( $section_data->section_name ); ?>
														</option>
														<?php
													}
													?>
												</select>
											</div>
											<div class="col-md-3">
												<input type="submit" name="view_student_list" Value="<?php esc_html_e( 'Go', 'mjschool' ); ?>" class="btn btn-info mjschool-save-btn" />
											</div>
										</div>
									</div>
								</form>
								<?php
							}
							
							if ( isset( $_POST['view_student_list'] ) ) {
								if (! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'mjschool_student_filter_nonce')) {
									wp_die(esc_html__('Security check failed.', 'mjschool'));
								}
								$exlude_id = mjschool_approve_student_list();
								if ( sanitize_text_field( wp_unslash( $_POST['filter_section_id'] ) ) === "all_section") {
									$student_list = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'exclude' => $exlude_id ) );
								} else {
									$student_list = get_users(array( 'meta_key' => 'class_section', 'meta_value' => wp_unslash( $_POST['filter_section_id']), 'meta_query' => array(array( 'key' => 'class_name', 'value' => $class_id, 'compare' => '=' ) ), 'role' => 'student', 'exclude' => $exlude_id ) );
								}
							} else {
								$exlude_id = mjschool_approve_student_list();
								$student_list = get_users(array( 'meta_key' => 'class_name', 'meta_value' => $class_id, 'exclude' => $exlude_id ) );
							}
							
							if ( ! empty( $student_list ) ) {
								?>
								<div class="mjschool-panel-body">
									<div class="table-responsive">
										<form id="mjschool-common-form" name="mjschool-common-form" method="post">
											<table id="front_class_wise_student_list" class="display" cellspacing="0" width="100%">
												<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
													<tr>
														<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Student Name & Email', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Roll No.', 'mjschool' ); ?></th>
														<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
														<?php if($school_type !== 'university'){?>
															<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
														<?php }?>
														<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php
													foreach ( $student_list as $retrieved_data ) {
														?>
														<tr>
															<td class="mjschool-user-image mjschool-width-50px-td">
																<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ) ); ?>">
																	<?php
																	$uid       = $retrieved_data->ID;
																	$umetadata = mjschool_get_user_image( $uid );
																	if (empty($umetadata ) ) {
																		echo '<img src=' . esc_url( get_option( 'mjschool_student_thumb_new' ) ) . ' class="img-circle" />';
																	} else {
																		echo '<img src=' . esc_url($umetadata) . ' class="img-circle" />';
																	}
																	?>
																</a>
															</td>
															<td class="name">
																<a class="mjschool-color-black" href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ) ); ?>">
																	<?php echo esc_html( $retrieved_data->display_name ); ?>
																</a>
																<br>
																<span class="mjschool-list-page-email"> <?php echo esc_html( $retrieved_data->user_email ); ?> </span>
															</td>
															<td class="roll_no">
																<?php
																if ( get_user_meta( $retrieved_data->ID, 'roll_id', true ) ) {
																	echo esc_html( get_user_meta( $retrieved_data->ID, 'roll_id', true ) );
																} else {
																	esc_html_e( 'Not Provided', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Roll No.', 'mjschool' ); ?>"></i>
															</td>
															<td class="name">
																<?php
																$class_id  = get_user_meta( $retrieved_data->ID, 'class_name', true );
																$classname = mjschool_get_class_name( $class_id );
																if ( $classname === ' ' ) {
																	esc_html_e( 'Not Provided', 'mjschool' );
																} else {
																	echo esc_html( $classname );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
															</td>
															<?php if($school_type !== 'university') {?>
																<td class="name">
																	<?php
																	$section_name = get_user_meta( $retrieved_data->ID, 'class_section', true );
																	if ( $section_name != '' ) {
																		echo esc_html( mjschool_get_section_name( $section_name ) );
																	} else {
																		esc_html_e( 'No Section', 'mjschool' );
																	}
																	?>
																	<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section', 'mjschool' ); ?>"></i>
																</td>
															<?php } ?>
															<td class="action">
																<div class="mjschool-user-dropdown">
																	<ul  class="mjschool_ul_style">
																		<li >
																			<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																			</a>
																			<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( '?dashboard=mjschool_user&page=student&tab=view_student&action=view_student&student_id=' . esc_attr( mjschool_encrypt_id( $retrieved_data->ID ) ) ); ?>" class="mjschool-float-left-width-100px">
																						<i class="fa fa-eye"> </i> <?php esc_html_e( 'View', 'mjschool' ); ?>
																					</a>
																				</li>
																			</ul>
																		</li>
																	</ul>
																</div>
															</td>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										</form>
									</div>
								</div>
								<?php
							} else {
								?>
								<div class="mjschool-calendar-event-new">
									<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
								</div>
								<?php 
							}
						}
						?>
					</div>
				</section>
			</div>
		</div>
		<?php
	}
	?>
</div>