<?php
/**
 * Transport Management - Admin Dashboard.
 *
 * Handles the creation, editing, deletion, and assignment of transport routes 
 * within the MJSchool plugin. Provides an intuitive admin interface for managing 
 * school transportation data including routes, vehicles, and driver information.
 *
 * Key Features:
 * - Implements role-based access control for transport operations (add, edit, delete, view).
 * - Enables route management with driver details, vehicle identifiers, and fare information.
 * - Supports bulk deletion of transport records with security and confirmation checks.
 * - Integrates DataTables for dynamic searching, sorting, and responsive layout.
 * - Provides route assignment functionality to link users with transport routes.
 * - Uses WordPress nonces for secure form submissions and actions.
 * - Includes jQuery validation for form inputs and AJAX-based pop-up views.
 * - Displays success and error messages using WordPress admin notices.
 * - Ensures compliance with WordPress coding standards and data sanitization best practices.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/transport
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'transport' );
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
			if ( 'transport' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'transport' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'transport' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'transport';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>

<!-- POP-UP code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="modal-content">
			<div class="view_popup"></div>
			<div class="assign_route"></div>
		</div>
	</div>
</div>
<!-- End POP-UP code. -->
<?php
// This is class at admin side.
// ---------- Add-update record. ---------------------//
$tablename = 'mjschool_transport';
if ( isset( $_POST['save_transport'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_transpoat_admin_nonce' ) ) {
		if ( isset( $_POST['mjschool_user_avatar'] ) && $_POST['mjschool_user_avatar'] != '' ) {
			$photo = sanitize_text_field(wp_unslash($_POST['mjschool_user_avatar']));
		} else {
			$photo = '';
		}
		$route_data = array(
			'route_name'        => sanitize_textarea_field( wp_unslash( $_POST['route_name'] ) ),
			'number_of_vehicle' => sanitize_text_field( wp_unslash($_POST['number_of_vehicle']) ),
			'vehicle_reg_num'   => sanitize_textarea_field( wp_unslash( $_POST['vehicle_reg_num'] ) ),
			'smgt_user_avatar'  => $photo,
			'driver_name'       => sanitize_text_field( wp_unslash($_POST['driver_name']) ),
			'driver_phone_num'  => sanitize_text_field( wp_unslash($_POST['driver_phone_num']) ),
			'driver_address'    => sanitize_textarea_field( wp_unslash( $_POST['driver_address'] ) ),
			'route_description' => sanitize_textarea_field( wp_unslash( $_POST['route_description'] ) ),
			'route_fare'        => sanitize_textarea_field( wp_unslash($_POST['route_fare']) ),
			'created_by'        => get_current_user_id(),
		);
		// Table name without prefix.
		$tablename = 'mjschool_transport';
		if ( $_REQUEST['action'] === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'edit_action' ) ) {
				$transport_id        = intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['transport_id'])) ) );
				$result              = mjschool_update_record( $tablename, $route_data, array( 'transport_id' => $transport_id ) );
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=transport&message=2' ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result             = mjschool_insert_record( $tablename, $route_data );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=transport&message=1' ) );
				die();
			}
		}
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_transport( $tablename, $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=transport&message=3' ) );
		die();
	}
}
// ---------------- Assign route. -----------------//
if ( isset( $_REQUEST['save_assign_route'] ) ) {
	$nonce = $_POST['_wpnonce'];
	if ( wp_verify_nonce( $nonce, 'save_assign_transpoat_admin_nonce' ) ) {
		$assign_route_table_name = 'mjschool_assign_transport';
		$assign_transport_data   = mjschool_get_assign_transport_by_id( sanitize_text_field(wp_unslash($_POST['transport_id'])) );
		$transport_data          = mjschool_get_transport_by_id( sanitize_text_field(wp_unslash($_POST['transport_id'])) );
		$assign_route_data       = array(
			'transport_id' => sanitize_text_field(wp_unslash($_POST['transport_id'])),
			'route_name'   => sanitize_textarea_field( $transport_data->route_name ),
			'route_fare'   => sanitize_textarea_field( $transport_data->route_fare ),
			'route_user'   => json_encode( $_POST['selected_users'] ),
			'created_by'   => get_current_user_id(),
		);
		if ( ! empty( $assign_transport_data ) ) {
			$transport_id = array( 'transport_id' => intval( wp_unslash($_REQUEST['transport_id']) ) );
			$result       = mjschool_update_record( $assign_route_table_name, $assign_route_data, $transport_id );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=assign_transport_list&message=4' ) );
				die();
			}
		} else {
			$result = mjschool_insert_record( $assign_route_table_name, $assign_route_data );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=assign_transport_list&message=5' ) );
				die();
			}
		}
	}
}
// ---------- Delete record. ---------------------------
$tablename = 'mjschool_transport';
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'delete_action' ) ) {
		$result = mjschool_delete_transport( $tablename, intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['transport_id'])) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_transport&tab=transport&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'transport';
?>
<div class="mjschool-page-inner"><!--------- Page inner. ------->
	<div class="mjschool-transport-list mjschool-main-list-margin-5px">
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Transport Added successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Transport Updated successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Transport Deleted Successfully.', 'mjschool' );
				break;
			case '4':
				$message_string = esc_html__( 'Assign Transport Route Updated Successfully.', 'mjschool' );
				break;
			case '5':
				$message_string = esc_html__( 'Assign Transport Route Inserted Successfully.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="mjschool-panel-white"><!--------- Panel white. ------->
			<div class="mjschool-panel-body"> <!--------- Panel body. ------->
				<?php
				if ( $active_tab === 'transport' ) {
					$retrieve_class_data = mjschool_get_all_data( $tablename );
					if ( ! empty( $retrieve_class_data ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="transport_list" class="display mjschool-admin-transport-datatable" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Route Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Vehicle Identifier', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Vehicle Reg. No.', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Driver Name', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Mobile No.', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Route Fare', 'mjschool' ); ?>(<?php echo esc_html( mjschool_get_currency_symbol() ); ?>)</th>
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
											$i = 0;
											foreach ( $retrieve_class_data as $retrieved_data ) {
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
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->transport_id ); ?>"></td>
													<td class="mjschool-user-image">
														<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr($retrieved_data->transport_id); ?>" type="transport_view">
															<?php
															$tid = $retrieved_data->transport_id;
															$umetadata = mjschool_get_user_driver_image($tid);
															if (empty($umetadata) || $umetadata['smgt_user_avatar'] === "") {
																echo '<img src="' . esc_url( get_option( 'mjschool_driver_thumb_new' ) ) . '" height="50px" width="50px" class="img-circle" />';
															} else
																echo '<img src=' . esc_url( $umetadata['smgt_user_avatar'] ) . ' height="50px" width="50px" class="img-circle" />';
															?>
														</a>
													</td>
													<td>
														<a href="#" class="mjschool-view-details-popup" id="<?php echo esc_attr( $retrieved_data->transport_id ); ?>" type="transport_view">
															<?php echo esc_html( $retrieved_data->route_name ); ?>
														</a> 
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Route Name', 'mjschool' ); ?>"></i>
													</td>
													<td><?php echo esc_html( $retrieved_data->number_of_vehicle ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Vehicle Identifier', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( $retrieved_data->vehicle_reg_num ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Vehicle Reg. No.', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( $retrieved_data->driver_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Driver Name', 'mjschool' ); ?>"></i></td>
													<td><?php echo '+' . esc_html( mjschool_get_country_phonecode( get_option( 'mjschool_contry' ) ) ); ?> <?php echo esc_html( $retrieved_data->driver_phone_num ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Mobile No.', 'mjschool' ); ?>"></i></td>
													<td><?php echo esc_html( mjschool_get_currency_symbol() ); ?> <?php echo number_format( $retrieved_data->route_fare, 2, '.', '' ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" title="<?php esc_attr_e( 'Route Fare', 'mjschool' ); ?>"></i></td>
													<?php
													// Custom field values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ( $user_custom_field as $custom_field ) {
															if ( $custom_field->show_in_table === '1' ) {
																$module             = 'transport';
																$custom_field_id    = $custom_field->id;
																$module_record_id   = $retrieved_data->transport_id;
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
																			<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
																				<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																			</a>
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
														<div class="mjschool-user-dropdown">
															<ul  class="mjschool_ul_style">
																<li >
																	<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																		<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																	</a>
																	<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																		<li class="mjschool-float-left-width-100px">
																			<a href="#" class="mjschool-float-left-width-100px mjschool-view-details-popup" id="<?php echo esc_attr($retrieved_data->transport_id); ?>" type="transport_view"><i class="fas fa-eye" aria-hidden="true"></i><?php esc_html_e( 'View Transport Detail', 'mjschool' ); ?></a>
																		</li>
																		<?php
																		if ($user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="?page=mjschool_transport&tab=addtransport&action=edit&transport_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->transport_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'edit_action' ) );?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ($user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="?page=mjschool_transport&tab=transport&action=delete&transport_id=<?php echo esc_attr( mjschool_encrypt_id($retrieved_data->transport_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) );?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<?php if ($user_access_delete === '1' ) {
											?>
											<button data-toggle="tooltip" id="delete_selected" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
											<?php
										}
										?>
									</div>
								</form>
							</div>
						</div>
						<?php
					} else {
						if ($user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_transport&tab=addtransport' ) ); ?>">
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
				if ( $active_tab === 'addtransport' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/transport/add-transport.php';
				}
				?>
			</div><!--------- Panel body. ------->
		</div><!--------- Panel white. ------->
	</div>
</div><!--------- Page inner. ------->