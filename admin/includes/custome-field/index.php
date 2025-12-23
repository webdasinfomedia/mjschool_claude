<?php
/**
 * The admin functionality for managing custom fields.
 *
 * This file handles adding, editing, viewing, and deleting custom fields in the admin dashboard.
 * It includes role-based access control, nonce verification, and DataTable integration for listing records.
 *
 * @since      1.0.0
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/custom-field
 */
defined( 'ABSPATH' ) || exit;
// -------- Check browser javascript.. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'custom_field' );
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
			if ( 'custom_field' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'custom_field' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'custom_field' === $user_access['page_link'] && ( sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_obj_custome_field = new Mjschool_Custome_Field();
// Save custom field data.
if ( isset( $_POST['add_custom_field'] ) ) {
	if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'insert' ) {
		// Add Custom Field data.
		$result = $mjschool_obj_custome_field->mjschool_add_custom_field( wp_unslash($_POST) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?&page=mjschool_custom_field&tab=custome_field_list&message=1' ) );
			die();
		}
	} elseif ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'edit_action' ) ) {
		// Update Custom Field data.
		$result = $mjschool_obj_custome_field->mjschool_add_custom_field( wp_unslash($_POST) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?&page=mjschool_custom_field&tab=custome_field_list&message=2' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = $mjschool_obj_custome_field->mjschool_delete_custome_field( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) ) );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_custom_field&tab=custome_field_list&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
if ( isset( $_POST['custome_delete_selected'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_custom_field' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( isset( $_POST['selected_id'] ) ) {
		foreach ( $_POST['selected_id'] as $custome_id ) {
			$record_id = intval( $custome_id );
			$result    = $mjschool_obj_custome_field->mjschool_delete_selected_custome_field( $record_id );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=mjschool_custom_field&tab=custome_field_list&message=3' ) );
		exit;
	} else {
		?>
		<div class="mjschool-alert-msg alert alert-warning alert-dismissible " role="alert">
			<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
			<?php esc_html_e( 'Please Select At least One Record.', 'mjschool' ); ?>
		</div>
		<?php
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'custome_field_list';
?>
<div class="mjschool-page-inner"><!-- mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Custom Field Added Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Custom Field  Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Custom Field Deleted Successfully.', 'mjschool' );
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
		<div class="row"><!-- row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- col-md-12. -->
				<div class="mjschool-main-list-page"><!-- mjschool-main-list-page. -->
					<?php
					if ( $active_tab === 'custome_field_list' ) {
						$retrieve_class_data = $mjschool_obj_custome_field->mjschool_get_all_custom_field_data();
						if ( ! empty( $retrieve_class_data ) ) {
							?>
							<div class="mjschool-panel-body"><!-- mjschool-panel-body. -->
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post">
										<?php wp_nonce_field( 'bulk_delete_custom_field' ); ?>
										<table id="custome_field_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Form Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Lable', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Type', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Custom Field Id', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Validation', 'mjschool' ); ?></th>
													<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												$i = 0;
												foreach ( $retrieve_class_data as $retrieved_data ) {
													$color_class_css = mjschool_table_list_background_color( $i );
													?>
													<tr>
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" name="selected_id[]" class="mjschool-sub-chk sub_chk" value="<?php echo esc_attr( $retrieved_data->id ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
															<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																
																<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-custome-field.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
																
															</p>
														</td>
														<td class="added">
															<?php echo esc_html( $retrieved_data->form_name ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Form Name', 'mjschool' ); ?>"></i>
														</td>
														<td class="added">
															<?php echo esc_html( $retrieved_data->field_label ); ?> 
															<i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Lable', 'mjschool' ); ?>"></i>
														</td>
														<td class="added">
															<?php echo esc_html( $retrieved_data->field_type ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Type', 'mjschool' ); ?>"></i>
														</td>
														<td class="added">
															<?php echo esc_html( $retrieved_data->id ); ?> 
															<i class="fa fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Custom Field Id', 'mjschool' ); ?>"></i>
														</td>
														<td class="added">
															<?php echo esc_html( $retrieved_data->field_validation ); ?>
															<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Validation', 'mjschool' ); ?>"></i>
														</td>
														<td class="action">
															<div class="mjschool-user-dropdown">
																<ul  class="mjschool_ul_style">
																	<li >
																		<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																			
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-more.png"); ?>">
																			
																		</a>
																		<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																			<?php
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field&tab=add_custome_field&action=edit&id='. mjschool_encrypt_id( $retrieved_data->id ) .'&_wpnonce_action='. mjschool_get_nonce( 'edit_action' ) ) ); ?>" class="mjschool-float-left-width-100px">
																						<i class="fa fa-edit"></i><?php esc_html_e( 'Edit', 'mjschool' ); ?>
																					</a>
																				</li>
																				<?php
																			}
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field&tab=custome_field_list&action=delete&id='. mjschool_encrypt_id( $retrieved_data->id ) .'&_wpnonce_action='. mjschool_get_nonce( 'delete_action' ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i><?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
												<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php if ( $user_access_delete === '1' ) {
												 ?>
												<button id="custome_delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="custome_delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php 
											}
											?>
										</div>
									</form>
								</div>
							</div><!-- mjschool-panel-body. -->
							<?php
						} else {
							
							if ($user_access_add === '1' ) {
								?>
								<div class="mjschool-no-data-list-div">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_custom_field&tab=add_custome_field' ) ); ?>">
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
									<img class="mjschool-no-data-img" src="<?php echo esc_url(MJSCHOOL_NODATA_IMG); ?>" alt="<?php esc_attr_e( 'No data', 'mjschool' ); ?>">
								</div>
								<?php
							}
							
						}
					}
					if ( $active_tab === 'add_custome_field' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/custome-field/add-custome-field.php';
					}
					?>
				</div><!-- mjschool-main-list-page. -->
			</div><!-- col-md-12. -->
		</div><!-- row. -->
	</div><!-- mjschool-main-list-margin-15px. -->
</div><!-- mjschool-page-inner. -->