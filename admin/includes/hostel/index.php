<?php
/**
 * Admin Hostel Management Interface.
 *
 * This file manages the backend interface for adding, editing, viewing, and deleting
 * hostel records within the MJSchool plugin. It provides role-based access control,
 * secure data handling, and dynamic table listings for hostel management.
 *
 * Key Features:
 * - Implements CRUD operations (Create, Read, Update, Delete) for hostel entries.
 * - Provides access control based on user roles and defined permissions.
 * - Integrates nonce verification and sanitization for secure form submission.
 * - Displays hostel records with sorting, searching, and pagination using DataTables.
 * - Supports custom fields dynamically retrieved via the `Mjschool_Custome_Field` class.
 * - Includes client-side validation using the jQuery Validation Engine.
 * - Offers bulk deletion functionality with confirmation prompts.
 * - Enables video tutorials (YouTube popup) and responsive table layouts.
 * - Automatically redirects users upon successful CRUD actions with messages.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/hostel
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// Check Browser Javascript.
mjschool_browser_javascript_check();
$mjschool_role       = mjschool_get_user_role( get_current_user_id() );
$obj_hostel = new Mjschool_Hostel();
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'hostel' );
	$user_access_add    = $user_access['add'];
	$user_access_edit   = $user_access['edit'];
	$user_access_delete = $user_access['delete'];
	$user_access_view   = $user_access['view'];
	if ( isset( $_REQUEST ['page'] ) ) {
		if ( $user_access_view === '0' ) {
			mjschool_access_right_page_not_access_message_admin_side();
			die();
		}
		if ( ! empty( $_REQUEST['action'] ) ) {
			if ( 'hostel' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'hostel' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'hostel' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'hostel';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
$obj_hostel = new Mjschool_Hostel();
$tablename  = 'mjschool_hostel';
// Data insert and update.
if ( isset( $_POST['save_hostel'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_hostel_admin_nonce' ) ) {
		if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'edit' ) {
			$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
			if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
				$book_id             = sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) );
				$result              = $obj_hostel->mjschool_insert_hostel( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'hostel';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $book_id );
				wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list&message=2' ) ) );
				die();
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result             = $obj_hostel->mjschool_insert_hostel( array_map( 'sanitize_text_field', wp_unslash( $_POST ) ) );
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'hostel';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list&message=1' ) ) );
				die();
			}
		}
	}
}
// Delete record.
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
	if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
		$result = $obj_hostel->mjschool_delete_hostel( intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['hostel_id'] ) ) ) ) );
		if ( $result ) {
			wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list&message=3' ) ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = $obj_hostel->mjschool_delete_hostel( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( esc_url_raw( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list&message=3' ) ) );
		die();
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'hostel_list';
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Hostel Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Hostel Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Hostel Deleted Successfully.', 'mjschool' );
				break;
		}
		if ( $message ) {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible mjschool_margin_7px_10px" >
				<p><?php echo esc_html( $message_string ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
		?>
		<div class="row"><!-- row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<div class="mjschool-main-list-page"><!-- mjschool-main-list-page. -->
					<?php
					if ( $active_tab === 'hostel_list' ) {
						?>
						<div class="mjschool-popup-bg">
							<div class="mjschool-overlay-content mjschool-admission-popup">
								<div class="modal-content">
									<div class="mjschool-category-list">
									</div>
								</div>
							</div>
						</div>
						<?php
						if ( get_option( 'mjschool_enable_video_popup_show' ) == 'yes' ) {
							?>
							<a href="#" class="mjschool-view-video-popup youtube-icon" link="<?php echo esc_url( 'https://www.youtube.com/embed/CZQzPhCPIr4?si=Hg16bHUL2gzi9xLA' ); ?>" title="Hostel Module">
								<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-youtube-icon.png' ); ?>" alt="<?php esc_attr_e( 'YouTube', 'mjschool' ); ?>">
							</a>
							<?php
						}
						$retrieve_class_data = mjschool_get_all_data( $tablename );
						if ( ! empty( $retrieve_class_data ) ) {
							?>
							<div class="mjschool-panel-body">
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<table id="hostel_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" id="select_all"></th>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Hostel Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Hostel Type', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Hostel Address', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Intake/Capacity', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Description', 'mjschool' ); ?></th>
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
													<?php
													if ( $user_access_edit === '1' || $user_access_delete === '1' ) {
														?>
														<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													<?php } ?>
												</tr>
											</thead>
											<tbody>
												<?php
												$i = 0;
												foreach ( $retrieve_class_data as $retrieved_data ) {
													?>
													<tr>
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->id ) ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/thumb-icon/mjschool-hostel.png' ); ?>" class="img-circle" /></td>
														<td>
															<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>">
																<?php echo esc_html( $retrieved_data->hostel_name ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Hostel Name', 'mjschool' ); ?>"></i>
															</a>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->hostel_type ) ) {
																echo esc_html( $retrieved_data->hostel_type );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Hostel Type', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->hostel_address ) ) {
																$strlength = strlen( $retrieved_data->hostel_address );
																if ( $strlength > 25 ) {
																	echo esc_html( substr( $retrieved_data->hostel_address, 0, 25 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->hostel_address );
																}
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->hostel_address ) ) { echo esc_attr( $retrieved_data->hostel_address ); } else { esc_attr_e( 'Hostel Address', 'mjschool' );} ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->hostel_intake ) ) {
																echo esc_html( $retrieved_data->hostel_intake );
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Intake/Capacity', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->Description ) ) {
																$strlength = strlen( $retrieved_data->Description );
																if ( $strlength > 40 ) {
																	echo esc_html( substr( $retrieved_data->Description, 0, 40 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->Description );
																}
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->Description ) ) { echo esc_attr( $retrieved_data->Description ); } else { esc_attr_e( 'Description', 'mjschool' );} ?>"></i>
														</td>
														<?php
														if ( $user_access_edit === '1' || $user_access_delete === '1' ) {
															// Custom Field Values.
															if ( ! empty( $user_custom_field ) ) {
																foreach ( $user_custom_field as $custom_field ) {
																	if ( $custom_field->show_in_table === '1' ) {
																		$module             = 'hostel';
																		$custom_field_id    = $custom_field->id;
																		$module_record_id   = $retrieved_data->id;
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
																					<a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . $custom_field_value ) ); ?>" download="CustomFieldfile"><button class="btn btn-default view_document" type="button">
																						<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																				<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																			</a>
																			<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_details&hostel_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-eye"></i><?php esc_html_e( 'View', 'mjschool' ); ?></a>
																				</li>
																				<?php
																				if ( $user_access_edit === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=add_hostel&action=edit&hostel_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				if ( $user_access_delete === '1' ) {
																					?>
																					<li class="mjschool-float-left-width-100px">
																						<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=hostel_list&action=delete&hostel_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->id ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color"  onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
																					</li>
																					<?php
																				}
																				?>
																			</ul>
																		</li>
																	</ul>
																</div>
															</td>
														<?php } ?>
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
											<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected','mjschool' );?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
										</div>
									</form>
								</div>
							</div>
							<?php
						} elseif ( $user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_hostel&tab=add_hostel' ) ); ?>">
									<img class="col-md-12 mjschool-no-img-width-100px" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ); ?>">
								</a>
								<div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
									<label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
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
					}
					if ( $active_tab === 'hostel_details' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/hostel/hostel-details.php';
					}
					if ( $active_tab === 'add_hostel' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/hostel/add-hostel.php';
					}
					?>
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->