<?php
/**
 * Admin Class Management Controller.
 *
 * This file manages class-related functionality within the Mjschool plugin’s admin interface.
 * It handles CRUD operations (Create, Read, Update, Delete) for classes, user access rights,
 * nonce security verification, and integrates DataTables for listing.
 *
 * Key Features:
 * - Handles insert, edit, delete, and bulk delete actions for classes.
 * - Enforces role-based access control (administrator and custom roles).
 * - Implements secure nonce checks for edit/delete/view actions.
 * - Displays class list with DataTables and custom field columns.
 * - Supports conditional rendering for schools and universities.
 * - Integrates multilingual support and form validation.
 * - Uses WordPress standards for sanitization, escaping, and redirects.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/class
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type === get_option( 'mjschool_custom_class' );
// -------- Check browser javascript.. ----------//
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'class' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'class';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
// This is Class at admin side.
if ( isset( $_POST['save_class'] ) ) {
	$nonce = sanitize_text_field( wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_class_admin_nonce' ) ) {
		$academic_year = isset($_POST['academic_year']) ? sanitize_text_field( wp_unslash($_POST['academic_year'])) : '';
		$created_date = date( 'Y-m-d H:i:s' );
		$classdata    = array(
			'class_name'     => sanitize_text_field( stripslashes( $_POST['class_name'] ) ),
			'class_num_name' => sanitize_text_field( wp_unslash( $_POST['class_num_name'] )),
			'class_capacity' => sanitize_text_field( wp_unslash( $_POST['class_capacity'] )),
			'creater_id'     => get_current_user_id(),
			'created_date'   => $created_date,
			'class_description' => sanitize_text_field( wp_unslash($_POST['class_description'])),
			'academic_year' => $academic_year
		);
		$tablename    = 'mjschool_class';
		if ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'edit_action' ) ) {
				// Proceed with the action.
				$class_id       = intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) );
				$classid        = array( 'class_id' => $class_id );
				$class_name     = sanitize_text_field( stripslashes( $_POST['class_name'] ) );
				$class_num_name = sanitize_text_field( wp_unslash( $_POST['class_num_name'] ) );
				global $wpdb;
				$table_name     = $wpdb->prefix . 'mjschool_class';
				$existing_class = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM $table_name WHERE class_name = %s AND class_num_name = %s AND class_id !== %d", $class_name, $class_num_name, $class_id )
				);
				if ( $existing_class ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=addclass&message=4' ) );
					die();
				}
				$result = mjschool_update_record( $tablename, $classdata, $classid );
				// UPDATE CUSTOM FIELD DATA.
				$mjschool_custom_field_obj = new Mjschool_Custome_Field();
				$module                    = 'class';
				$custom_field_update       = $mjschool_custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $class_id );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=classlist&message=2' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$wizard = mjschool_setup_wizard_steps_updates( 'step2_class' );
			global $wpdb;
			$class_name     = sanitize_text_field( stripslashes( $_POST['class_name'] ) );
			$class_num_name = sanitize_text_field( wp_unslash( $_POST['class_num_name'] ) );
			$table_name     = $wpdb->prefix . 'mjschool_class';
			$existing_class = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM $table_name WHERE class_name = %s AND class_num_name = %s", $class_name, $class_num_name
				)
			);
			if ( $existing_class ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=addclass&message=4' ) );
				die();
			}
			$result                    = mjschool_insert_record( $tablename, $classdata );
			$last_insert_id            = $wpdb->insert_id;
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'class';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $last_insert_id );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=classlist&message=1' ) );
				die();
			}
		}
	}
}
$tablename = 'mjschool_class';
/*Delete selected Subject.*/
if ( isset( $_REQUEST['delete_selected'] ) ) {
	
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_class( $tablename, intval( $id ) );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=classlist&message=3' ) );
		die();
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce'])), 'delete_action' ) ) {
		$result = mjschool_delete_class( $tablename, mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_class&tab=classlist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'classlist';
?>
<!-- POP up code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="invoice_data">
			</div>
		</div>
	</div>
</div>
<!-- End POP-UP Code. -->
<div class="mjschool-list-padding-5px"> <!--------- list page padding. ---------->
	<div class="mjschool-class-list"> <!--------- list page main wrapper. ---------->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Class Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Class Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Class Deleted Successfully.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Same Class name and Class number already exist.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-below-h2 notice is-dismissible mjschool-message-disabled alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white">
			<div class="mjschool-panel-body">
				<?php
				// --------------- CLASS LIST TABING. ----------------//
				if ( $active_tab === 'classlist' ) {
					$retrieve_class_data = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="mjschool-class-list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
												<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Class Name', 'mjschool' ); ?></th>
												<?php if ( $school_type === 'school' ) {?>
													<th><?php esc_html_e( 'Section', 'mjschool' ); ?></th>
												<?php } ?>
												<?php if ( $school_type === 'university' ) {?>
													<th><?php esc_html_e( 'Academy Year', 'mjschool' ); ?></th>
												<?php } ?>
												<th><?php esc_html_e( 'Class Numeric Value', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Student Capacity', 'mjschool' ); ?></th>
												<?php 
												$cust_class_room = get_option( 'mjschool_class_room' ); 
												if ( $cust_class_room === 1)
												{
													?>
													<th><?php esc_html_e( 'Assign Room', 'mjschool' ); ?></th>
													<?php 
												} ?>
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
												$class_id         = $retrieved_data->class_id;
												$class_id_encrypt = mjschool_encrypt_id( $retrieved_data->class_id );
												$section_id       = mjschool_get_section_by_class_id( $class_id );
												$section_name     = '';
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px">
														<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->class_id ); ?>">
													</td>
													<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-class.png' ); ?>" class="img-circle" /></td>
													<td>
														<a href="<?php echo esc_url( '?page=mjschool_class&tab=class_details&class_id=' . $class_id_encrypt . '&_wpnonce=' . mjschool_get_nonce( 'view_action' ) ); ?>">
															<?php
															if ( $retrieved_data->class_name ) {
																echo esc_html( $retrieved_data->class_name );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
														</a>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
													</td>
													<?php if ( $school_type === 'school' ) {?>
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
																esc_attr_e( 'No Section', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Section', 'mjschool' ); ?>"></i>
														</td>
														<?php 
													}
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
													$mjschool_user = count(get_users(array(
														'meta_key' => 'class_name',
														'meta_value' => $class_id
													 ) ) );
													 ?>
													<td>
														<?php
														echo esc_html( $mjschool_user ) . ' ';
														esc_attr_e( 'Out Of', 'mjschool' );
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
																			<a target=""  href="<?php echo esc_url( content_url( '/uploads/school_assets/' . rawurlencode( sanitize_file_name( $custom_field_value ) ) ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i><?php esc_html_e( 'Download', 'mjschool' ); ?></button></a>
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
													<td class="action">
														<?php
														$class_id = mjschool_encrypt_id( $class_id );
														?>
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a class="mjschool-float-left-width-100px" href="<?php echo esc_url( '?page=mjschool_class&tab=class_details&class_id='. $class_id_encrypt .'&_wpnonce='. mjschool_get_nonce( 'view_action' ) ); ?>"><i class="fa fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																		</li>
																		<?php if ( $school_type === 'school' ) {?>
																			<li class="mjschool-float-left-width-100px">
																				<a class="mjschool-float-left-width-100px" href="<?php echo esc_url( '?page=mjschool_class&tab=class_details&tab1=section_list&class_id='. $class_id_encrypt .'&_wpnonce='. mjschool_get_nonce( 'view_action' ) ); ?>"><i class="fa fa-plus"></i><?php esc_html_e( 'Add Section', 'mjschool' ); ?></a>
																			</li>
																		<?php } ?>
																		<?php
																		if ($user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( '?page=mjschool_class&tab=addclass&action=edit&class_id=' . $class_id_encrypt . '&_wpnonce=' . mjschool_get_nonce( 'edit_action' ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ($user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( '?page=mjschool_class&tab=classlist&action=delete&class_id=' . $class_id_encrypt . '&_wpnonce=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											<?php } ?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="mjschool-sub-chk" name="" class="mjschool-sub-chk select_all mjchool_margin_none" value="">
											<label for="mjschool-sub-chk" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
									</div>
								</form>
							</div>
						</div>
						<?php
					} else {
						if ($user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_class&tab=addclass' ) ); ?>">
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
								<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_html_e( 'No data', 'mjschool' ); ?>">
							</div>
							<?php 
						}
					}
				}
				// -------------- ADD CLASS TABING. -----------------//
				if ( $active_tab === 'addclass' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/class/add-new-class.php';
				}
				// -------------- CLASS DETAILS TABING. ------------//
				if ( $active_tab === 'class_details' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/class/class-details.php';
				}
				?>
			</div>
		</div>
	</div>
</div>