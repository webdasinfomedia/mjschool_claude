<?php
/**
 * Notification Management File.
 *
 * This file handles the display, creation, editing, and deletion of notifications
 * within the school management system. It includes user access checks, form
 * processing with nonce verification, and logic for sending notifications to
 * selected users, classes, or sections, including push notification functionality.
 *
 * @package    Mjschool
 * @subpackage Mjschool/templates
 * @since      1.0.0
 */
defined( 'ABSPATH' ) || exit;
$school_type = get_option( "mjschool_custom_class");
// -------- Check browser javascript. ----------//
mjschool_browser_javascript_check();
$mjschool_role_name = mjschool_get_user_role( get_current_user_id() );
// --------------- Access-wise role. -----------//
$user_access = mjschool_get_user_role_wise_access_right_array();
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
$custom_field_obj  = new Mjschool_Custome_Field();
$mjschool_obj_notification  = new Mjschool_notification();
$module            = 'notification';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
// --------------- Save notification. ---------------//
if ( isset( $_POST['save_notification'] ) ) {
	$nonce = sanitize_text_field(wp_unslash($_POST['_wpnonce']));
	if ( wp_verify_nonce( $nonce, 'save_notice_admin_nonce' ) ) {
		global $wpdb;
		$exlude_id             = mjschool_approve_student_list();
		if ( isset( $_POST['selected_users'] ) && sanitize_text_field(wp_unslash($_POST['selected_users'])) != 'All' ) {
			/* Send Push Notification. */
			$device_token      = array();
			$device_token[]    = get_user_meta( sanitize_text_field(wp_unslash($_POST['selected_users'])), 'token_id', true );
			$title             = esc_html__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_textarea_field( stripslashes( $_POST['title'] ) );
			$text              = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
			$notification_data = array(
				'registration_ids' => $device_token,
				'notification'     => array(
					'title' => $title,
					'body'  => $text,
					'type'  => 'notification',
				),
			);
			$json              = json_encode( $notification_data );
			$message           = mjschool_send_push_notification( $json );
			/* Send Push Notification. */
			$data['student_id']   = sanitize_text_field(wp_unslash($_POST['selected_users']));
			$data['title']        = sanitize_textarea_field( stripslashes( $_POST['title'] ) );
			$data['message']      = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
			$data['created_date'] = date( 'Y-m-d' );
			$data['created_by']   = get_current_user_id();
			$result             = $mjschool_obj_notification->mjschool_insert_notification($data);
			$ids                = $wpdb->insert_id;
			$custom_field_obj   = new Mjschool_Custome_Field();
			$module             = 'notification';
			$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
		} elseif ( isset( $_POST['class_id'] ) && sanitize_text_field(wp_unslash($_POST['class_id'])) === 'All' ) {
			foreach ( mjschool_get_all_class() as $class ) {
                
                $query_data['exclude'] = $exlude_id;
                $query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval($class['class_id']), 'compare' => '=' ) );
                
				$results = get_users( $query_data );
				if ( ! empty( $results ) ) {
					foreach ( $results as $retrive_data ) {
						/* Send Push Notification. */
						$device_token      = array();
						$device_token[]    = get_user_meta( $retrive_data->ID, 'token_id', true );
						$title             = esc_html__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_textarea_field( stripslashes( $_POST['title'] ) );
						$text              = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
						$notification_data = array(
							'registration_ids' => $device_token,
							'notification'     => array(
								'title' => $title,
								'body'  => $text,
								'type'  => 'notification',
							),
						);
						$json              = json_encode( $notification_data );
						mjschool_send_push_notification( $json );
						/* Send Push Notification. */
						$data['student_id']   = $retrive_data->ID;
						$data['title']        = sanitize_textarea_field( stripslashes( $_POST['title'] ) );
						$data['message']      = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
						$data['created_date'] = date( 'Y-m-d' );
						$data['created_by']   = get_current_user_id();
                        $result             = $mjschool_obj_notification->mjschool_insert_notification($data);
						$ids                = $wpdb->insert_id;
						$custom_field_obj   = new Mjschool_Custome_Field();
						$module             = 'notification';
						$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
					}
				}
			}
		} elseif ( isset( $_POST['class_section'] ) && sanitize_text_field(wp_unslash($_POST['class_section'])) == 'All' ) {
            $query_data['exclude'] = $exlude_id;
            $query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval(wp_unslash($_POST['class_id'])), 'compare' => '=' ) );
            
			$results = get_users( $query_data );
			if ( ! empty( $results ) ) {
				foreach ( $results as $retrive_data ) {
					/* Send Push Notification. */
					$device_token      = array();
					$device_token[]    = get_user_meta( $retrive_data->ID, 'token_id', true );
					$title             = esc_html__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_textarea_field( stripslashes( $_POST['title'] ) );
					$text              = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
					$notification_data = array(
						'registration_ids' => $device_token,
						'notification'     => array(
							'title' => $title,
							'body'  => $text,
							'type'  => 'notification',
						),
					);
					$json              = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					/* Send Push Notification. */
					$data['student_id']   = $retrive_data->ID;
					$data['title']        = sanitize_textarea_field( stripslashes( $_POST['title'] ) );
					$data['message']      = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
					$data['created_date'] = date( 'Y-m-d' );
					$data['created_by']   = get_current_user_id();
                  	$result             = $mjschool_obj_notification->mjschool_insert_notification($data);
					$ids                = $wpdb->insert_id;
					$custom_field_obj   = new Mjschool_Custome_Field();
					$module             = 'notification';
					$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
				}
			}
		} else {
            
            $query_data['exclude'] = $exlude_id;
            $query_data['meta_key'] = 'class_section';
            $query_data['meta_value'] = sanitize_text_field(wp_unslash($_POST['class_section']));
            $query_data['meta_query'] = array(array( 'key' => 'class_name', 'value' => intval(wp_unslash($_POST['class_id'])), 'compare' => '=' ) );
            $results = get_users($query_data);
            
			if ( ! empty( $results ) ) {
				foreach ( $results as $retrive_data ) {
					/* Send Push Notification. */
					$device_token      = array();
					$device_token[]    = get_user_meta( $retrive_data->ID, 'token_id', true );
					$title             = esc_html__( 'You have a New Notification', 'mjschool' ) . ' ' . sanitize_textarea_field( stripslashes( $_POST['title'] ) );
					$text              = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
					$notification_data = array(
						'registration_ids' => $device_token,
						'notification'     => array(
							'title' => $title,
							'body'  => $text,
							'type'  => 'notification',
						),
					);
					$json              = json_encode( $notification_data );
					mjschool_send_push_notification( $json );
					/* Send Push Notification. */
					$data['student_id']   = $retrive_data->ID;
					$data['title']        = sanitize_textarea_field( stripslashes( $_POST['title'] ) );
					$data['message']      = sanitize_textarea_field( stripslashes( $_POST['message_body'] ) );
					$data['created_date'] = date( 'Y-m-d' );
					$data['created_by']   = get_current_user_id();
                    $result             = $mjschool_obj_notification->mjschool_insert_notification($data);
					$ids                = $wpdb->insert_id;
					$custom_field_obj   = new Mjschool_Custome_Field();
					$module             = 'notification';
					$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $ids );
				}
			}
		}
		if ( $result ) {
			wp_safe_redirect( home_url( '?dashboard=mjschool_user&page=notification&tab=notificationlist&message=1' ) );
			die();
		} else {
			?>
			<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
                <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span></button>
				<?php echo esc_html__( 'Please Add least one student', 'mjschool' ); ?>
			</div>
			<?php
		}
	}
}
// ----------- DELETE NOTIFICATION. ----------------//
if ( isset( $_REQUEST['action'] ) && sanitize_text_field(wp_unslash($_REQUEST['action'])) == 'delete' ) {
	if ( isset( $_GET['_wpnonce_action'] ) && wp_verify_nonce( sanitize_text_field(wp_unslash($_GET['_wpnonce_action'])), 'delete_action' ) ) {
		$result = mjschool_delete_notification( intval( mjschool_decrypt_id( wp_unslash($_REQUEST['notification_id']) ) ) );
		if ( $result ) {
			wp_safe_redirect( home_url() . '?dashboard=mjschool_user&page=notification&tab=notificationlist&message=2' );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
// ----------- Add Multiple Delete record. ----------//
if ( isset( $_POST['delete_selected'] ) ) {
	// Verify nonce
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'bulk_delete_parents' ) ) {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
	
	if ( ! empty( $_POST['id'] ) ) {
		foreach ( $_POST['id'] as $id ) {
			$result = mjschool_delete_notification( intval( $id ) );
		}
		wp_safe_redirect( home_url() . '?dashboard=mjschool_user&page=notification&tab=notificationlist&message=2' );
		exit;
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'notificationlist';
?>
<div class="mjschool-panel-body mjschool-panel-white mjschool-frontend-list-margin-30px-res"><!--------------- Panel body. --------------------->
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
			$message_string = '';
			break;
	}
	if ( $message ) {
		?>
		<div id="mjschool-message" class="mjschool-message_class mjschool-alert-msg alert alert-success alert-dismissible " role="alert">
            
            <button type="button" class="btn-default notice-dismiss" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/dashboard-icon/mjschool-close.png"); ?>"></span> </button>
			
			<?php echo esc_html( $message_string ); ?>
		</div>
		<?php
	}
	if ( $active_tab === 'notificationlist' ) {
		$user_id = get_current_user_id();
		// ------- NOTIFICATION DATA FOR STUDENT. ---------//
		if ( $school_obj->role === 'student' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$result = mjschool_get_student_own_notification_created_by( $user_id );
			} else {
				$result = $mjschool_obj_notification->mjschool_get_all_notifications();
			}
		}
		// ------- NOTIFICATION DATA FOR TEACHER. ---------//
		elseif ( $school_obj->role === 'teacher' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$result = mjschool_get_all_notification_created_by( $user_id );
			} else {
				$result = $mjschool_obj_notification->mjschool_get_all_notifications();
			}
		}
		// ------- NOTIFICATION DATA FOR PARENT. ---------//
		elseif ( $school_obj->role === 'parent' ) {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$result = mjschool_get_all_notification_for_parent( $user_id );
			} else {
				$result = $mjschool_obj_notification->mjschool_get_all_notifications();
			}
		}
		// ------- NOTIFICATION DATA FOR SUPPORT STAFF. ---------//
		else {
			$own_data = $user_access['own_data'];
			if ( $own_data === '1' ) {
				$result = mjschool_get_all_notification_created_by( $user_id );
			} else {
				$result = $mjschool_obj_notification->mjschool_get_all_notifications();
			}
		}
		if ( ! empty( $result ) ) {
			?>
			<div class="mjschool-panel-body"><!-- Mjschool-panel-body.-->
				<div class="table-responsive"><!--Table-responsive.-->
					<form name="mjschool-common-form" action="" method="post">
						<?php wp_nonce_field( 'bulk_delete_parents' ); ?>
						<table id="frontend_notification_list" class="display mjschool-admin-notification-datatable" cellspacing="0" width="100%">
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
									if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
										?>
										<th class="mjschool-text-align-end"><?php esc_html_e( 'Action', 'mjschool' ); ?></th>
										<?php
									}
									?>
								</tr>
							</thead>
							<tbody>
								<?php
								$i = 0;
								if ( $result ) {
									foreach ( $result as $retrieved_data ) {
										$class_id = get_user_meta( $retrieved_data->student_id, 'class_name', true );
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
											<?php
											if ( $mjschool_role_name === 'supportstaff' ) {
												?>
												<td class="mjschool-checkbox-width-10px">
													<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( $retrieved_data->notification_id ); ?>">
												</td>
												<?php
											}
											?>
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
												<?php $strlength = strlen( $retrieved_data->message ); ?>
												<?php
												if ( $strlength > 40 ) {
													echo esc_html( substr( $retrieved_data->message, 0, 40 ) ) . '...';
												} else {
													echo esc_html( $retrieved_data->message );
												}
												?>
												<i class="fa-solid fa-circle-info mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->message ) ) { echo esc_attr( $retrieved_data->message ); } else { esc_attr_e( 'Message', 'mjschool' ); } ?>"></i>
											</td>
											<?php
											// Custom Field Values.
											if ( ! empty( $user_custom_field ) ) {
												foreach ( $user_custom_field as $custom_field ) {
													if ( $custom_field->show_in_table === '1' ) {
														$module             = 'notification';
														$custom_field_id    = $custom_field->id;
														$module_record_id   = $retrieved_data->notification_id;
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
											if ( $user_access['edit'] === '1' || $user_access['delete'] === '1' ) {
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
                                                                    if ($user_access['delete'] === '1' ) {
                                                                    	?>
                                                                        <li class="mjschool-float-left-width-100px">
                                                                            <a href="<?php echo esc_url('?dashboard=mjschool_user&page=notification&tab=notificationlist&action=delete&notification_id=' . mjschool_encrypt_id( $retrieved_data->notification_id ) . '&_wpnonce_action=' . mjschool_get_nonce( 'delete_action' ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fas fa-trash"></i>
                                                                                <?php esc_html_e( 'Delete', 'mjschool' ); ?>
                                                                            </a>
                                                                        </li>
                                                                    	<?php
                                                                    } ?>
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
                                } ?>
                            </tbody>
                        </table>
                        <?php
                        if ($mjschool_role_name === "supportstaff") {
                        	?>
                            <div class="mjschool-print-button pull-left">
                                <button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload">
                                    <input type="checkbox" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
                                    <label for="checkbox" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
                                </button>
                                <?php
                                if ($user_access['delete'] === '1' ) { ?>
                                    <button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . "/assets/images/listpage-icon/mjschool-delete.png"); ?>"></button>
                               		<?php
                                } ?>
                            </div>
                        	<?php
                        }
                        ?>
                    </form>
                </div><!--Table-responsive.-->
            </div><!-- Mjschool-panel-body. -->
            <?php
        } else {
            if ($user_access['add'] === '1' ) {
            	?>
                <div class="mjschool-no-data-list-div">
                    <a href="<?php echo esc_url(home_url() . '?dashboard=mjschool_user&page=notification&tab=addnotification' ); ?>">
                        <img class="col-md-12 mjschool-no-img-width-100px notification_img" src="<?php echo esc_url( get_option( 'mjschool_mjschool-no-data-img' ) ) ?>">
                    </a>
                    <div class="col-md-12 mjschool-dashboard-btn mjschool-margin-top-20px">
                        <label class="mjschool-no-data-list-label"><?php esc_html_e( 'Tap on above icon to add your first Record.', 'mjschool' ); ?> </label>
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
	if ( $active_tab === 'addnotification' ) {
		?>
		<div class="mjschool-panel-body overflow-hidden"><!-- Mjschool-panel-body. -->
			<form name="class_form" action="" method="post" class="mjschool-form-horizontal" id="notification_form" enctype="multipart/form-data">
				<div class="header">
					<h3 class="mjschool-first-header"><?php esc_html_e( 'Notification Information', 'mjschool' ); ?></h3>
				</div>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-class-list-id"><?php esc_html_e( 'Select Class', 'mjschool' ); ?></label>
							<select name="class_id" id="mjschool-notification-class-list-id" class="mjschool-line-height-30px form-control mjschool-max-width-100px">
								<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
								<?php
								foreach ( mjschool_get_all_class() as $classdata ) {
									?>
									<option value="<?php echo esc_attr( $classdata['class_id'] ); ?>"><?php echo esc_html( $classdata['class_name'] ); ?></option>
									<?php
								}
								?>
							</select>
						</div>
						<?php if ( $school_type === 'school' ){ ?>
							<div class="col-md-6 input mjschool-notification-class-section-id">
								<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-class-section-id"><?php esc_html_e( 'Class Section', 'mjschool' ); ?></label>
								<select name="class_section" class="mjschool-line-height-30px form-control mjschool-max-width-100px" id="mjschool-notification-class-section-id">
									<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
								</select>
							</div>
						<?php }?>
						<div class="col-md-6 input">
							<label class="ml-1 mjschool-custom-top-label top" for="mjschool-notification-selected-users"><?php esc_html_e( 'Select Users', 'mjschool' ); ?></label>
							<span class="mjschool-notification-user-display-block">
								<select name="selected_users" id="mjschool-notification-selected-users" class="mjschool-line-height-30px form-control mjschool-max-width-100px">
									<option value="All"><?php esc_html_e( 'All', 'mjschool' ); ?></option>
								</select>
							</span>
						</div>
						<?php wp_nonce_field( 'save_notice_admin_nonce' ); ?>
						<div class="col-sm-6 col-md-6 col-lg-6 col-xl-6 mjschool-error-msg-left-margin">
							<div class="form-group input">
								<div class="col-md-12 form-control">
									<input id="title" class="form-control validate[required,custom[description_validation]] text-input" type="text" maxlength="100" name="title">
									<label  for="title"><?php esc_html_e( 'Title', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
								</div>
							</div>
						</div>
						<div class="col-md-6 mjschool-note-text-notice">
							<div class="form-group input">
								<div class="col-md-12 mjschool-note-border mjschool-margin-bottom-15px-res">
									<div class="form-field">
										<textarea name="message_body" id="message_body" maxlength="1000" class="mjschool-textarea-height-60px form-control validate[required,custom[description_validation]] text-input"></textarea>
										<span class="mjschool-txt-title-label"></span>
										<label class="text-area address active" for="message_body"><?php esc_html_e( 'Message', 'mjschool' ); ?><span class="mjschool-require-field">*</span></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php
				// --------- Get module-wise custom field data. --------------//
				$custom_field_obj = new Mjschool_Custome_Field();
				$module           = 'notification';
				$custom_field     = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
				?>
				<div class="form-body mjschool-user-form">
					<div class="row">
						<div class="col-sm-6">
							<input type="submit" value="<?php esc_html_e( 'Add Notification', 'mjschool' ); ?>" name="save_notification" class="btn btn-success mjschool-save-btn" />
						</div>
					</div>
				</div>
			</form>
		</div><!-- Mjschool-panel-body. -->
		<?php
	}
	?>
</div><!--------------- Panel body. --------------------->