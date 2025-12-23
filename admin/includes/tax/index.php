<?php
/**
 * MJSchool Tax Management (Admin Module)
 *
 * This file handles the entire tax management functionality within the MJSchool plugin’s
 * admin panel. It allows administrators and authorized users to add, edit, view, and delete
 * tax entries that are used across the system for billing, invoices, and financial reports.
 *
 * Key Responsibilities:
 * - Displays the tax listing table with search, sorting, and pagination features.
 * - Integrates DataTables for enhanced data presentation and usability.
 * - Handles CRUD operations: Add, Edit, and Delete tax records with nonce security.
 * - Enforces user role–based access control for tax-related actions.
 * - Supports multi-language DataTable configurations.
 * - Connects with the custom field module to display user-defined tax metadata.
 * - Displays contextual success or error messages after each operation.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/tax
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'tax';
$obj_tax    = new Mjschool_Tax_Manage();
// -------- Check Browser Javascript. ----------//
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
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'class' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'tax';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
// ------------------ Save tax. --------------------//
if ( isset( $_POST['save_tax'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_tax_admin_nonce' ) ) {
		$post_action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		if ( $post_action === 'edit' ) {
			if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'edit_action' ) ) {
				$tax_id              = isset( $_REQUEST['tax_id'] ) ? intval( wp_unslash( $_REQUEST['tax_id'] ) ) : 0;
				$result              = $obj_tax->mjschool_insert_tax( wp_unslash($_POST) );
				$custom_field_obj    = new Mjschool_Custome_Field();
				$module              = 'tax';
				$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $tax_id );
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_tax&tab=tax&message=2' ) );
					die();
				}
			} else {
				wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
			}
		} else {
			$result             = $obj_tax->mjschool_insert_tax( wp_unslash($_POST) );
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'tax';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $result );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_tax&tab=tax&message=1' ) );
				die();
			}
		}
	}
}
// ------------------ Delete tax. --------------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( $_GET['_wpnonce_action'], 'delete_action' ) ) {
		$result = $obj_tax->mjschool_delete_tax( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['tax_id'])) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_tax&tab=tax&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ------------------ Delete multiple tax. --------------------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = $obj_tax->mjschool_delete_tax( $id );
		}
	}
	if ( $result ) {
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_tax&tab=tax&message=3' ) );
		die();
	}
}
?>

<div class="mjschool-list-padding-5px"> <!--------- List page padding. ---------->
	<div class="mjschool-class-list"> <!--------- List page main wrapper. ---------->
		<div class="mjschool-panel-white"> <!------ Panel white. -------->
			<?php
			$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
			switch ( $message ) {
				case '1':
					$message_string = esc_html__( 'Tax Added successfully.', 'mjschool' );
					break;
				case '2':
					$message_string = esc_html__( 'Tax Updated Successfully.', 'mjschool' );
					break;
				case '3':
					$message_string = esc_html__( 'Tax Deleted Successfully.', 'mjschool' );
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
			<div class="mjschool-panel-body">
				<?php
				if ($active_tab === 'tax' ) {
					$retrieve_tax = $obj_tax->mjschool_get_all_tax();
					if ( ! empty( $retrieve_tax ) ) {
						?>
						<div class="mjschool-panel-body">
							<div class="table-responsive">
								<form id="mjschool-common-form" name="mjschool-common-form" method="post">
									<table id="tax_list" class="display" cellspacing="0" width="100%">
										<thead class="<?php echo esc_attr( mjschool_datatable_header( ) ); ?>">
											<tr>
												<th class="mjschool-checkbox-width-10px text-end"><input type="checkbox" class="select_all" name="select_all"></th>
												<th><?php esc_html_e( 'Photo', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Tax Title', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Tax Value(%)', 'mjschool' ); ?></th>
												<th><?php esc_html_e( 'Created Date', 'mjschool' ); ?></th>
												<?php
												if ( ! empty( $user_custom_field ) ) {
													foreach ($user_custom_field as $custom_field) {
														if ($custom_field->show_in_table === '1' ) {
															?>
															<th><?php echo esc_html( $custom_field->field_label); ?></th>
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
											foreach ($retrieve_tax as $retrieved_data) {
												$tax_id = mjschool_encrypt_id($retrieved_data->tax_id);
												$color_class_css = mjschool_table_list_background_color( $i );
												?>
												<tr>
													<td class="mjschool-checkbox-width-10px"><input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr($retrieved_data->tax_id); ?>"></td>
													<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription">
														<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr($color_class_css); ?>">
															<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-tax.png"); ?>" class="mjschool-massage-image mjschool-image-icon-height-25px mjschool-margin-top-3px">
														</p>
													</td>
													<td>
														<?php 
														if ($retrieved_data->tax_title) {
															echo esc_html( $retrieved_data->tax_title);
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														} ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Tax Title', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php 
														if ($retrieved_data->tax_value) {
															echo esc_html( $retrieved_data->tax_value);
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														} ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Tax Value(%)', 'mjschool' ); ?>"></i>
													</td>
													<td>
														<?php 
														if ($retrieved_data->created_date) {
															echo esc_html( mjschool_get_date_in_input_box($retrieved_data->created_date ) );
														} else {
															esc_html_e( 'N/A', 'mjschool' );
														} ?>
														<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Created Date', 'mjschool' ); ?>"></i>
													</td>
													<?php
													// Custom field values.
													if ( ! empty( $user_custom_field ) ) {
														foreach ($user_custom_field as $custom_field) {
															if ($custom_field->show_in_table === "1") {
																$module = 'tax';
																$custom_field_id = $custom_field->id;
																$module_record_id = $retrieved_data->tax_id;
																$custom_field_value = $custom_field_obj->mjschool_get_single_custom_field_meta_value($module, $module_record_id, $custom_field_id);
																if ($custom_field->field_type === 'date' ) {
																	?>
																	<td>
																		<?php 
																		if ( ! empty( $custom_field_value ) ) {
																			echo esc_html( mjschool_get_date_in_input_box($custom_field_value ) );
																		} else {
																			esc_html_e( 'N/A', 'mjschool' );
																		} ?>
																	</td>
																	<?php
																} elseif ($custom_field->field_type === 'file' ) {
																	?>
																	<td>
																		<?php
																		if ( ! empty( $custom_field_value ) ) {
																			?>
																			<a target="" href="<?php echo esc_url(content_url( '/uploads/school_assets/' . sanitize_file_name( $custom_field_value ))); ?>" download="CustomFieldfile"><a target="" href="<?php echo esc_url( content_url( '/uploads/school_assets/' . sanitize_file_name( $custom_field_value ) ) ); ?>" download="CustomFieldfile">
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
																			echo esc_html( $custom_field_value);
																		} else {
																			esc_html_e( 'N/A', 'mjschool' );
																		} ?>
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
																		<?php
																		if ($user_access_edit === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px mjschool-border-bottom-menu">
																				<a href="<?php echo esc_url( "?page=mjschool_tax&tab=add_tax&action=edit&tax_id=" . esc_attr( $tax_id ) . "&_wpnonce_action=" . esc_attr( mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fas fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																			</li>
																			<?php
																		}
																		if ($user_access_delete === '1' ) {
																			?>
																			<li class="mjschool-float-left-width-100px">
																				<a href="<?php echo esc_url( "?page=mjschool_tax&tab=tax&action=delete&tax_id=" . esc_attr( $tax_id ) . "&_wpnonce_action=" . esc_attr( mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );">
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
												++$i;
											}
											?>
										</tbody>
									</table>
									<div class="mjschool-print-button pull-left">
										<button class="mjschool-btn-sms-color mjschool-button-reload">
											<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
											<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
										</button>
										<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected">
											<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>">
										</button>
									</div>
								</form>
							</div>
						</div>
						<?php
					} else {
						if ($user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_tax&tab=add_tax' ) ); ?>">
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
				if ($active_tab === 'add_tax' ) {
					require_once MJSCHOOL_ADMIN_DIR . '/tax/add-tax.php';
				}
				?>
			</div>
		</div>
	</div>
</div>