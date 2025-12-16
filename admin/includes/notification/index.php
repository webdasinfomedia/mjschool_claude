<?php
/**
 * Notification Management Page.
 *
 * This file handles the "Notification" admin page of the Mjschool plugin.
 * It provides functionality to:
 * - Add, view, and delete student notifications.
 * - Send push notifications to selected users or classes.
 * - Display notification records in a dynamic DataTable with custom fields.
 * - Enforce user access rights (add, edit, delete, view) based on roles.
 * - Include custom field data for notifications where applicable.
 *
 * @package    Mjschool
 * @subpackage Mjschool/admin/includes/notification
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
// -------- Check Browser Javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role = mjschool_get_user_role( get_current_user_id() );
if ( $mjschool_role === 'administrator' ) {
	$user_access_add    = '1';
	$user_access_edit   = '1';
	$user_access_delete = '1';
	$user_access_view   = '1';
} else {
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'notification' );
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
			if ( 'notification' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'notification' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'notification' === $user_access['page_link'] && ( sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$mjschool_custom_field_obj = new Mjschool_Custome_Field();
$module                    = 'notification';
$user_custom_field         = $mjschool_custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>
<?php
if ( isset( $_POST['save_notification'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_notice_admin_nonce' ) ) {
		global $wpdb;
		$mjschool_notification = $wpdb->prefix . 'mjschool_notification';
		$exlude_id             = mjschool_approve_student_list();
		if ( isset( $_POST['selected_users'] ) && sanitize_text_field(wp_unslash($_POST['selected_users'])) != 'All' ) {
			$title = esc_attr__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['title'] ) );
			$text  = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
			// Send Push Notification.
			$device_token      = array();
			$device_token[]    = get_user_meta( sanitize_text_field(wp_unslash($_POST['selected_users'])), 'token_id', true );
			$notification_data = array(
				'registration_ids' => $device_token,
				'data'             => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'notification',
				),
			);
			$json = json_encode( $notification_data );
			mjschool_send_push_notification( $json );
			// End Send Push Notification.
			$data['student_id']   = sanitize_text_field(wp_unslash($_POST['selected_users']));
			$data['title']        = sanitize_text_field( wp_unslash( $_POST['title'] ) );
			$data['message']      = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
			$data['created_date'] = date( 'Y-m-d' );
			$data['created_by']   = get_current_user_id();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
			$result                    = $wpdb->insert( $mjschool_notification, $data );
			$ids                       = $wpdb->insert_id;
			$mjschool_custom_field_obj = new Mjschool_Custome_Field();
			$module                    = 'notification';
			$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
		} elseif ( isset( $_POST['class_id'] ) && sanitize_text_field(wp_unslash($_POST['class_id'])) === 'All' ) {
			foreach ( mjschool_get_all_class() as $class ) {
				
				$query_data['exclude'] = $exlude_id;
				$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval($class['class_id']), 'compare' => '=' ) );
				
				$results = get_users( $query_data );
				if ( ! empty( $results ) ) {
					foreach ( $results as $retrive_data ) {
						$title = esc_attr__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['title'] ) );
						$text  = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
						// Send Push Notification.
						$device_token      = array();
						$device_token[]    = get_user_meta( strval( $retrive_data->ID ), 'token_id', true );
						$notification_data = array(
							'registration_ids' => $device_token,
							'data'             => array(
								'title' => $title,
								'body'  => $text,
								'type'  => 'notification',
							),
						);
						$json = json_encode( $notification_data );
						mjschool_send_push_notification( $json );
						// End Send Push Notification.
						$data['student_id']   = $retrive_data->ID;
						$data['title']        = sanitize_text_field( wp_unslash( $_POST['title'] ) );
						$data['message']      = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
						$data['created_date'] = date( 'Y-m-d' );
						$data['created_by']   = get_current_user_id();
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
						$result                    = $wpdb->insert( $mjschool_notification, $data );
						$ids                       = $wpdb->insert_id;
						$mjschool_custom_field_obj = new Mjschool_Custome_Field();
						$module                    = 'notification';
						$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
					}
				}
			}
		} elseif ( isset( $_POST['class_section'] ) && sanitize_text_field(wp_unslash($_POST['class_section'])) === 'All' ) {
			
			$query_data['exclude'] = $exlude_id;
			$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval(wp_unslash($_POST['class_id'])), 'compare' => '=' ) );
			
			$results = get_users( $query_data );
			if ( ! empty( $results ) ) {
				foreach ( $results as $retrive_data ) {
					$title = esc_attr__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['title'] ) );
					$text  = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
					// Send Push Notification.
					$device_token      = array();
					$device_token[]    = get_user_meta( $retrive_data->ID, 'token_id', true );
					$notification_data = array(
						'registration_ids' => $device_token,
						'data'             => array(
							'title' => $title,
							'body'  => $text,
							'type'  => 'notification',
						),
					);
					$json = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					// End Send Push Notification.
					$data['student_id']   = $retrive_data->ID;
					$data['title']        = sanitize_text_field( wp_unslash( $_POST['title'] ) );
					$data['message']      = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
					$data['created_date'] = date( 'Y-m-d' );
					$data['created_by']   = get_current_user_id();
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$result                    = $wpdb->insert( $mjschool_notification, $data );
					$ids                       = $wpdb->insert_id;
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'notification';
					$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
				}
			}
		} else {
			
			$query_data['exclude'] = $exlude_id;
			$query_data['meta_key'] = 'class_section';
			$query_data['meta_value'] = intval(wp_unslash($_POST['class_section']));
			$query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval(wp_unslash($_POST['class_id'])), 'compare' => '=' ) );
			
			$results = get_users( $query_data );
			if ( ! empty( $results ) ) {
				foreach ( $results as $retrive_data ) {
					$title = esc_attr__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['title'] ) );
					$text  = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
					// Send Push Notification.
					$device_token      = array();
					$device_token[]    = get_user_meta( $retrive_data->ID, 'token_id', true );
					$notification_data = array(
						'registration_ids' => $device_token,
						'data'             => array(
							'title' => $title,
							'body'  => $text,
							'type'  => 'notification',
						),
					);
					$json              = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					// End Send Push Notification.
					$data['student_id']   = $retrive_data->ID;
					$data['title']        = sanitize_text_field( wp_unslash( $_POST['title'] ) );
					$data['message']      = sanitize_textarea_field( wp_unslash( $_POST['message_body'] ) );
					$data['created_date'] = date( 'Y-m-d' );
					$data['created_by']   = get_current_user_id();
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
					$result                    = $wpdb->insert( $mjschool_notification, $data );
					$ids                       = $wpdb->insert_id;
					$mjschool_custom_field_obj = new Mjschool_Custome_Field();
					$module                    = 'notification';
					$insert_custom_data        = $mjschool_custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
				}
			}
		}
		if ( isset( $result ) ) {
			wp_redirect( admin_url() . 'admin.php?page=mjschool_notification&tab=notificationlist&message=1' );
			die();
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
				<p><?php echo esc_attr__( 'Please Add least one student', 'mjschool' ); ?></p>
				<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
			</div>
			<?php
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) === 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_notification( intval( mjschool_decrypt_id( sanitize_text_field(wp_unslash($_REQUEST['notification_id'])) ) ) );
		if ( $result ) {
			wp_redirect( esc_url(admin_url() . 'admin.php?page=mjschool_notification&tab=notificationlist&message=2') );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ----------- Add Multiple Delete records. ----------//
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) ) {
		foreach ( $_REQUEST['id'] as $id ) {
			$result = mjschool_delete_notification( sanitize_text_field(wp_unslash($id)) );
			wp_redirect( esc_url(admin_url() . 'admin.php?page=mjschool_notification&tab=notificationlist&message=2') );
			die();
		}
	}
	if ( $result ) {
		wp_redirect( esc_url(admin_url() . 'admin.php?page=mjschool_notification&tab=notificationlist&message=2') );
		die();
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'notificationlist';
?>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field(wp_unslash($_REQUEST['message'])) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Notification Inserted Successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Notification Deleted Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Notification', 'mjschool' );
				break;
		}
		?>
		<div class="row"><!-- Row. -->
			<?php
			if ( $message ) {
				?>
				<div id="mjschool-message" class="mjschool-message_class alert mjschool-message-disabled mjschool-below-h2 notice is-dismissible alert-dismissible">
					<p><?php echo esc_html( $message_string ); ?></p>
					<button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'mjschool' ); ?></span></button>
				</div>
				<?php
			}
			?>
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-main-list-page"><!-- Mjschool-main-list-page. -->
					<?php
					// Report 1.
					if ( $active_tab === 'notificationlist' ) {
						$result = mjschool_get_notification_all_data();
						if ( ! empty( $result ) ) { ?>
							<div class="mjschool-panel-body"><!-- Mjschool-panel-body.-->
								<div class="table-responsive"><!--Table-responsive.-->
									<form name="mjschool-common-form" action="" method="post">
										<table id="notification_list" class="display mjschool-admin-notification-datatable" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Student Name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class name', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Title', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Message', 'mjschool' ); ?></th>
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
													if ( $user_access_delete === '1' ) {
														?>
														<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
													<?php } ?>
												</tr>
											</thead>
											<tbody>
												<?php
												$i = 0;
												if ( $result ) {
													foreach ( $result as $retrieved_data ) {
														$class_id     = get_user_meta( $retrieved_data->student_id, 'class_name', true );
														$section_name = get_user_meta( $retrieved_data->student_id, 'class_section', true );
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
															<td class="mjschool-checkbox-width-10px">
																<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->notification_id ); ?>">
															</td>
															<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
																<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/icons/white-icons/mjschool-notification.png"); ?>" height="30px" width="30px" class="mjschool-massage-image">
																</p>
															</td>
															<td>
																<?php
																$sname = mjschool_student_display_name_with_roll( $retrieved_data->student_id );
																if ( $sname != '' ) {
																	echo esc_html( $sname );
																} else {
																	esc_html_e( 'N/A', 'mjschool' );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Student Name', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php echo esc_html( mjschool_get_class_section_name_wise( $class_id, $section_name ) ); ?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class Name', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php echo esc_html( $retrieved_data->title ); ?> <i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Title', 'mjschool' ); ?>"></i>
															</td>
															<td>
																<?php
																$strlength = strlen( $retrieved_data->message );
																if ( $strlength > 60 ) {
																	echo esc_html( substr( $retrieved_data->message, 0, 60 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->message );
																}
																?>
																<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top"  title="<?php if ( ! empty( $retrieved_data->message ) ) { echo esc_html( $retrieved_data->message ); } else { esc_html_e( 'Message', 'mjschool' ); } ?>"></i>
															</td>
															<?php
															// Custom Field Values.
															if ( ! empty( $user_custom_field ) ) {
																foreach ( $user_custom_field as $custom_field ) {
																	if ( $custom_field->show_in_table === '1' ) {
																		$module             = 'notification';
																		$custom_field_id    = $custom_field->id;
																		$module_record_id   = $retrieved_data->notification_id;
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
																			<a target="" href="<?php echo esc_url( content_url() . '/uploads/school_assets/' . $custom_field_value ); ?>" download="CustomFieldfile">
																				<button class="btn btn-default view_document" type="button"><i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
																			</a>
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
															if ( $user_access_delete === '1' ) {
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
																						<a href="?page=mjschool_notification&tab=notificationlist&action=delete&notification_id=<?php echo esc_attr( mjschool_encrypt_id( $retrieved_data->notification_id ) ); ?>&_wpnonce_action=<?php echo esc_attr( mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i>
																							<?php esc_html_e( 'Delete', 'mjschool' ); ?>
																						</a>
																					</li>
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
														++$i;
													}
												}
												?>
											</tbody>
										</table>
										<div class="mjschool-print-button pull-left">
											<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
												<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												 ?>
												<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
												<?php 
											}
											?>
										</div>
									</form>
								</div><!--Table-responsive.-->
							</div><!-- Mjschool-panel-body.-->
							<?php
						} elseif ( $user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url() . 'admin.php?page=mjschool_notification&tab=addnotification' ); ?>">
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
					if ( $active_tab === 'addnotification' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/notification/add-notification.php';
					}
					?>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->