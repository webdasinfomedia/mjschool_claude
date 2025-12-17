<?php
/**
 * MJ School Notice Management Page
 *
 * Handles add, edit, delete, and list operations for notices in the MJ School plugin.
 * Includes access control, validation, notifications (email/SMS/push), and custom field integration.
 *
 * @package    MJSchool
 * @subpackage MJSchool/admin/includes/notice
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
	$user_access        = mjschool_get_user_role_wise_filter_access_right_array( 'notice' );
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
			$action_value = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
			if ( 'notice' === $user_access['page_link'] && ( $action_value === 'edit' ) ) {
				if ( $user_access_edit === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'notice' === $user_access['page_link'] && ( $action_value === 'delete' ) ) {
				if ( $user_access_delete === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
			if ( 'notice' === $user_access['page_link'] && ( $action_value === 'insert' ) ) {
				if ( $user_access_add === '0' ) {
					mjschool_access_right_page_not_access_message_admin_side();
					die();
				}
			}
		}
	}
}
$custom_field_obj  = new Mjschool_Custome_Field();
$module            = 'notice';
$user_custom_field = $custom_field_obj->mjschool_get_custom_field_by_module( $module );
?>

<?php
if ( isset( $_POST['save_notice'] ) ) {
	$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
	if ( wp_verify_nonce( $nonce, 'save_notice_admin_nonce' ) ) {
		$start_date_raw = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : '';
		$end_date_raw   = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : '';
		$start_date     = gmdate( 'Y-m-d', strtotime( $start_date_raw ) );
		$end_date       = gmdate( 'Y-m-d', strtotime( $end_date_raw ) );

		if ( $start_date > $end_date ) {
			?>
			<div class="mjschool-date-error-trigger" data-error="1"></div>
			<?php
		} else {
			$class_id = '';
			if ( isset( $_POST['class_id'] ) ) {
				$class_id = sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) );
			}
			$action_value = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
			if ( $action_value === 'edit' ) {
				$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
				if ( wp_verify_nonce( $nonce_action, 'edit_action' ) ) {
					$notice_id = isset( $_REQUEST['notice_id'] ) ? intval( wp_unslash( $_REQUEST['notice_id'] ) ) : 0;
					$args      = array(
						'ID'           => $notice_id,
						'post_title'   => isset( $_REQUEST['notice_title'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_title'] ) ) : '',
						'post_content' => isset( $_REQUEST['notice_content'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['notice_content'] ) ) : '',
					);
					$result1 = wp_update_post( $args );
					// Update Custom Field Data.
					$custom_field_obj    = new Mjschool_Custome_Field();
					$module              = 'notice';
					$custom_field_update = $custom_field_obj->mjschool_update_custom_field_data_module_wise( $module, $notice_id );
					$notivce             = $args['post_title'];
					$page_name           = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
					mjschool_append_audit_log( '' . esc_html__( 'Notice Updated', 'mjschool' ) . '( ' . $notivce . ' )' . '', get_current_user_id(), get_current_user_id(), 'edit', $page_name );
					$notice_for = isset( $_REQUEST['notice_for'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_for'] ) ) : '';
					$result2 = update_post_meta( $notice_id, 'notice_for', $notice_for );
					$result3 = update_post_meta( $notice_id, 'start_date', $start_date_raw );
					$result4 = update_post_meta( $notice_id, 'end_date', $end_date_raw );
					if ( isset( $_POST['class_id'] ) ) {
						$result5 = update_post_meta( $notice_id, 'smgt_class_id', sanitize_text_field( wp_unslash( $_REQUEST['class_id'] ) ) );
					}
					if ( isset( $_POST['class_section'] ) ) {
						$result6 = update_post_meta( $notice_id, 'smgt_section_id', sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) );
					}
					$mjschool_role               = isset( $_POST['notice_for'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_for'] ) ) : '';
					$mjschool_sms_service_enable = 0;
					$current_sms_service_active  = get_option( 'mjschool_sms_service' );
					if ( isset( $_POST['mjschool_sms_service_enable'] ) ) {
						$mjschool_sms_service_enable = intval( wp_unslash( $_POST['mjschool_sms_service_enable'] ) );
					}
					// Send SMS Notification.
					if ( $mjschool_sms_service_enable ) {
						$current_sms_service = get_option( 'mjschool_sms_service' );
						if ( ! empty( $current_sms_service ) ) {
							$class_section = isset( $_REQUEST['class_section'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) : '';
							$userdata      = mjschool_get_user_notice( $mjschool_role, $class_id, $class_section );
							if ( ! empty( $userdata ) ) {
								$mail_id = array();
								$i       = 0;
								foreach ( $userdata as $mjschool_user ) {
									if ( $mjschool_role === 'parent' && $class_id !== 'all' ) {
										$mail_id[] = $mjschool_user['ID'];
									} else {
										$mail_id[] = $mjschool_user->ID;
									}
									++$i;
								}
								$parent_number = array();
								foreach ( $mail_id as $mjschool_user ) {
									$message_content = isset( $_POST['sms_template'] ) ? sanitize_text_field( wp_unslash( $_POST['sms_template'] ) ) : '';
									$type            = 'notice';
									mjschool_send_notification( $mjschool_user, $type, $message_content );
								}
							}
						}
					}
					if ( $result1 || $result2 || $result3 || $result4 || isset( $result5 ) ) {
						wp_safe_redirect( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist&message=2' ) );
						die();
					}
				} else {
					wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
				}
			} else {
				$current_sms_service = get_option( 'mjschool_sms_service' );
				$notice_title        = isset( $_REQUEST['notice_title'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['notice_title'] ) ) : '';
				$notice_content      = isset( $_REQUEST['notice_content'] ) ? sanitize_textarea_field( wp_unslash( $_REQUEST['notice_content'] ) ) : '';
				$post_id             = wp_insert_post(
					array(
						'post_status'  => 'publish',
						'post_type'    => 'notice',
						'post_title'   => $notice_title,
						'post_content' => $notice_content,
					)
				);
				$custom_field_obj   = new Mjschool_Custome_Field();
				$module             = 'notice';
				$insert_custom_data = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
				$page_name          = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				mjschool_append_audit_log( '' . esc_html__( 'Notice Added', 'mjschool' ) . '( ' . $notice_title . ' )' . '', get_current_user_id(), get_current_user_id(), 'insert', $page_name );
				if ( ! empty( $_POST['notice_for'] ) ) {
					// Send Push Notification.
					$notice_for_value = sanitize_text_field( wp_unslash( $_POST['notice_for'] ) );
					if ( $notice_for_value === 'supportstaff' || $notice_for_value === 'parent' ) {
						$user_list_array = get_users(
							array(
								'role__in' => array( $notice_for_value ),
								'fields'   => array( 'ID' ),
							)
						);
					} elseif ( $notice_for_value === 'all' ) {
						$user_list_array = get_users(
							array(
								'role__in' => array( 'supportstaff', 'parent', 'teacher', 'student' ),
								'fields'   => array( 'ID' ),
							)
						);
					} elseif ( $notice_for_value === 'teacher' ) {
						$class_list = isset( $_POST['class_id'] ) ? sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) : '';
						if ( $class_list === 'all' ) {
							$user_list_array = get_users(
								array(
									'role__in' => array( $notice_for_value ),
									'fields'   => array( 'ID' ),
								)
							);
						} else {
							global $wpdb;
							$table_mjschool_teacher_class = $wpdb->prefix . 'mjschool_teacher_class';
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Safe direct query, caching not required in this context
							$teacher_list = $wpdb->get_results(
								$wpdb->prepare( "SELECT * FROM $table_mjschool_teacher_class WHERE class_id = %d", intval( $class_list ) )
							);
							if ( $teacher_list ) {
								foreach ( $teacher_list as $teacher ) {
									$user_list_array[] = $teacher->teacher_id;
								}
							}
						}
					} elseif ( $notice_for_value === 'student' ) {
						$user_list                 = array();
						$exlude_id                 = mjschool_approve_student_list();
						$query_data['exclude']     = $exlude_id;
						$class_list                = isset( $_POST['class_id'] ) ? sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) : '0';
						$query_data['role']        = $notice_for_value;
						$query_data['fields']      = array( 'ID' );
						$class_section             = isset( $_REQUEST['class_section'] ) ? intval( wp_unslash( $_REQUEST['class_section'] ) ) : 0;
						if ( $class_section ) {
							$query_data['meta_key']   = 'class_section';
							$query_data['meta_value'] = $class_section;
							$query_data['meta_query'] = array( array( 'key' => 'class_name', 'value' => $class_list, 'compare' => '=' ) );
							$user_list_array          = get_users( $query_data );
						} elseif ( $class_list === 'all' ) {
							$user_list_array = get_users(
								array(
									'role__in' => array( $notice_for_value ),
									'fields'   => array( 'ID' ),
								)
							);
						} elseif ( empty( $class_section ) && ! empty( $class_list ) ) {
							$query_data['meta_key']   = 'class_name';
							$query_data['meta_value'] = $class_list;
							$user_list_array          = get_users( $query_data );
						}
					}
					if ( ! empty( $user_list_array ) ) {
						$device_token = array();
						foreach ( $user_list_array as $retrive_data ) {
							$user_id        = is_object( $retrive_data ) ? $retrive_data->ID : intval( $retrive_data );
							$device_token[] = get_user_meta( $user_id, 'token_id', true );
						}
						$title             = esc_html__( 'You have a New Notice', 'mjschool' ) . ' ' . sanitize_text_field( wp_unslash( $_POST['notice_title'] ) );
						$text              = sanitize_textarea_field( wp_unslash( $_POST['notice_content'] ) );
						$notification_data = array(
							'registration_ids' => $device_token,
							'data'             => array(
								'title' => $title,
								'body'  => $text,
								'type'  => 'notice',
							),
						);
						$json = wp_json_encode( $notification_data );
						mjschool_send_push_notification( $json );
						// End Send Push Notification.
					}
					delete_post_meta( $post_id, 'notice_for' );
					$result = add_post_meta( $post_id, 'notice_for', $notice_for_value );
					$result = add_post_meta( $post_id, 'start_date', $start_date_raw );
					$result = add_post_meta( $post_id, 'end_date', $end_date_raw );
					$result = add_post_meta( $post_id, 'notice_status', 0 );
					if ( isset( $_POST['class_id'] ) ) {
						$result = add_post_meta( $post_id, 'smgt_class_id', sanitize_text_field( wp_unslash( $_POST['class_id'] ) ) );
					}
					if ( isset( $_POST['class_section'] ) ) {
						$result6 = add_post_meta( $post_id, 'smgt_section_id', sanitize_text_field( wp_unslash( $_REQUEST['class_section'] ) ) );
					}
					$custom_field_obj            = new Mjschool_Custome_Field();
					$module                      = 'notice';
					$insert_custom_data          = $custom_field_obj->mjschool_insert_custom_field_data_module_wise( $module, $post_id );
					$mjschool_role               = isset( $_POST['notice_for'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_for'] ) ) : '';
					$mjschool_sms_service_enable = 0;
					$mjschool_mail_service_enable = 0;
					$current_sms_service_active  = get_option( 'mjschool_sms_service' );
					$userdata                    = mjschool_get_user_notice( $mjschool_role, $class_id );
					if ( ! empty( $userdata ) ) {
						if ( isset( $_POST['mjschool_mail_service_enable'] ) ) {
							$mjschool_mail_service_enable = intval( wp_unslash( $_POST['mjschool_mail_service_enable'] ) );
						}
						if ( $mjschool_mail_service_enable ) {
							$mail_id   = array();
							$i         = 0;
							$startdate = strtotime( $start_date_raw );
							$enddate   = strtotime( $end_date_raw );
							if ( $startdate === $enddate ) {
								$date = mjschool_get_date_in_input_box( $start_date_raw );
							} else {
								$date = mjschool_get_date_in_input_box( $start_date_raw ) . ' To ' . mjschool_get_date_in_input_box( $end_date_raw );
							}
							$search['{{notice_title}}']   = $notice_title;
							$search['{{notice_date}}']    = $date;
							$search['{{notice_comment}}'] = $notice_content;
							$search['{{school_name}}']    = get_option( 'mjschool_name' );
							$message                      = mjschool_string_replacement( $search, get_option( 'mjschool_notice_mailcontent' ) );
							$headers  = '';
							$headers .= 'From: ' . get_option( 'mjschool_name' ) . ' <noreplay@gmail.com>' . "\r\n";
							$headers .= "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/plain; charset=iso-8859-1\r\n";
							foreach ( $userdata as $mjschool_user ) {
								$userinfo = get_user_by( 'id', $mjschool_user->ID );
								if ( get_option( 'mjschool_mail_notification' ) === 1 ) {
									wp_mail( $userinfo->user_email, get_option( 'mjschool_notice_mailsubject' ), $message, $headers );
								}
								if ( $mjschool_role === 'parent' && $class_id !== 'all' ) {
									$mail_id[] = $mjschool_user['ID'];
								} else {
									$mail_id[] = $mjschool_user->ID;
								}
								++$i;
							}
						}
						if ( isset( $_POST['mjschool_sms_service_enable'] ) ) {
							$mjschool_sms_service_enable = intval( wp_unslash( $_POST['mjschool_sms_service_enable'] ) );
						}
						if ( $mjschool_sms_service_enable ) {
							if ( ! empty( $current_sms_service_active ) ) {
								$parent_number = array();
								foreach ( $userdata as $mjschool_user ) {
									$message_content = isset( $_POST['sms_template'] ) ? sanitize_text_field( wp_unslash( $_POST['sms_template'] ) ) : '';
									$type            = 'notice';
									mjschool_send_notification( $mjschool_user->ID, $type, $message_content );
								}
							}
						}
					}
				}
				if ( $result ) {
					wp_safe_redirect( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist&message=1' ) );
					die();
				}
			}
		}
	}
}
if ( isset( $_REQUEST['delete_selected'] ) ) {
	if ( ! empty( $_REQUEST['id'] ) && is_array( $_REQUEST['id'] ) ) {
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Notice Deleted', 'mjschool' ) . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		$ids = array_map( 'intval', wp_unslash( $_REQUEST['id'] ) );
		foreach ( $ids as $id ) {
			$result = wp_delete_post( $id );
			if ( $result ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist&message=3' ) );
				die();
			}
		}
	}
}
if ( isset( $_REQUEST['action'] ) && sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) === 'delete' ) {
	$nonce_action = isset( $_GET['_wpnonce_action'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce_action'] ) ) : '';
	if ( wp_verify_nonce( $nonce_action, 'delete_action' ) ) {
		$notice_id = isset( $_REQUEST['notice_id'] ) ? intval( mjschool_decrypt_id( sanitize_text_field( wp_unslash( $_REQUEST['notice_id'] ) ) ) ) : 0;
		$notice    = get_post( $notice_id );
		$page_name = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		mjschool_append_audit_log( '' . esc_html__( 'Notice Deleted', 'mjschool' ) . '( ' . $notice->post_title . ' )' . '', get_current_user_id(), get_current_user_id(), 'delete', $page_name );
		$result = wp_delete_post( $notice_id );
		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist&message=3' ) );
			die();
		}
	} else {
		wp_die( esc_html__( 'Security check failed!', 'mjschool' ) );
	}
}
$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'noticelist';
?>
<!-- View POP-UP Code. -->
<div class="mjschool-popup-bg">
	<div class="mjschool-overlay-content">
		<div class="mjschool-notice-content"></div>
	</div>
</div>
<div class="mjschool-page-inner"><!-- Mjschool-page-inner. -->
	<div class="mjschool-main-list-margin-15px"><!-- Mjschool-main-list-margin-15px. -->
		<?php
		$message = isset( $_REQUEST['message'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['message'] ) ) : '0';
		switch ( $message ) {
			case '1':
				$message_string = esc_html__( 'Notice Added successfully.', 'mjschool' );
				break;
			case '2':
				$message_string = esc_html__( 'Notice Updated Successfully.', 'mjschool' );
				break;
			case '3':
				$message_string = esc_html__( 'Notice Deleted Successfully.', 'mjschool' );
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
		<div class="row"><!-- Row. -->
			<div class="col-md-12 mjschool-custom-padding-0"><!-- Col-md-12. -->
				<div class="mjschool-main-list-page"><!-- Smgt_main_listpage. -->
					<?php
					if ( $active_tab === 'noticelist' ) {
						$args['post_type']      = 'notice';
						$args['posts_per_page'] = -1;
						$args['post_status']    = 'public';
						$q                      = new WP_Query();
						$retrieve_class_data    = $q->query( $args );
						$format                 = get_option( 'date_format' );
						if ( ! empty( $retrieve_class_data ) ) {
							?>
							<div class="mjschool-panel-body"><!-- mjschool-panel-body -->
								<div class="table-responsive">
									<form id="mjschool-common-form" name="mjschool-common-form" method="post"><!-- mjschool-panel-body -->
										<table id="notice_list" class="display" cellspacing="0" width="100%">
											<thead class="<?php echo esc_attr( mjschool_datatable_header() ); ?>">
												<tr>
													<th class="mjschool-custom-padding-0"><input type="checkbox" class="select_all" name="select_all"></th>
													<th><?php esc_html_e( 'Image', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Notice Title', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Notice Comment', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Notice Start Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Notice End Date', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Notice For', 'mjschool' ); ?></th>
													<th><?php esc_html_e( 'Class', 'mjschool' ); ?></th>
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
														<td class="mjschool-checkbox-width-10px">
															<input type="checkbox" class="mjschool-sub-chk select-checkbox" name="id[]" value="<?php echo esc_attr( intval( $retrieved_data->ID ) ); ?>">
														</td>
														<td class="mjschool-user-image mjschool-width-50px-td mjschool-profile-image-prescription mjschool-padding-left-0">
															<a class="view-notice" id="<?php echo esc_attr( intval( $retrieved_data->ID ) ); ?>" href="#">
																<p class="mjschool-prescription-tag mjschool-padding-15px mjschool-margin-bottom-0px <?php echo esc_attr( $color_class_css ); ?>">
																	<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/dashboard-icon/icons/white-icons/mjschool-notice.png' ); ?>" height="30px" width="30px" class="mjschool-massage-image">
																</p>
															</a>
														</td>
														<td>
															<a class="mjschool-color-black view-notice" id="<?php echo esc_attr( intval( $retrieved_data->ID ) ); ?>" href="#"><?php echo esc_html( $retrieved_data->post_title ); ?></a> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice Title', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( ! empty( $retrieved_data->post_content ) ) {
																$strlength = strlen( $retrieved_data->post_content );
																if ( $strlength > 50 ) {
																	echo esc_html( substr( $retrieved_data->post_content, 0, 50 ) ) . '...';
																} else {
																	echo esc_html( $retrieved_data->post_content );
																}
															} else {
																esc_html_e( 'N/A', 'mjschool' );
															}
															?>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php if ( ! empty( $retrieved_data->post_content ) ) { echo esc_attr( $retrieved_data->post_content ); } else { esc_attr_e( 'Notice Comment', 'mjschool' ); } ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'start_date', true ) ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice Start Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( mjschool_get_date_in_input_box( get_post_meta( $retrieved_data->ID, 'end_date', true ) ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice End Date', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php echo esc_html( ucfirst( get_post_meta( $retrieved_data->ID, 'notice_for', true ) ) ); ?> <i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Notice For', 'mjschool' ); ?>"></i>
														</td>
														<td>
															<?php
															if ( get_post_meta( $retrieved_data->ID, 'notice_for', true ) === 'all' ) {
																esc_html_e( 'N/A', 'mjschool' );
															} elseif ( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) !== '' && get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) === 'all' ) {
																esc_html_e( 'All', 'mjschool' );
															} elseif ( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) !== '' ) {
																echo esc_html( mjschool_get_class_name( get_post_meta( $retrieved_data->ID, 'smgt_class_id', true ) ) );
															}
															?>
															<i class="fa fa-info-circle mjschool-fa-information-bg" data-toggle="tooltip" data-placement="top" title="<?php esc_attr_e( 'Class', 'mjschool' ); ?>"></i>
														</td>
														<?php
														// Custom Field Values.
														if ( ! empty( $user_custom_field ) ) {
															foreach ( $user_custom_field as $custom_field ) {
																if ( $custom_field->show_in_table === '1' ) {
																	$module             = 'notice';
																	$custom_field_id    = $custom_field->id;
																	$module_record_id   = $retrieved_data->ID;
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
																					<i class="fa fa-download"></i> <?php esc_html_e( 'Download', 'mjschool' ); ?></button>
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
																<ul class="mjschool_ul_style">
																	<li >
																		<a  href="#" data-bs-toggle="dropdown" aria-expanded="false">
																			<img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-more.png' ); ?>">
																		</a>
																		<ul class="dropdown-menu mjschool-header-dropdown-menu mjschool-action-dropdawn" aria-labelledby="dropdownMenuLink">
																			<li class="mjschool-float-left-width-100px">
																				<a href="#" class="mjschool-float-left-width-100px view-notice" id="<?php echo esc_attr( intval( $retrieved_data->ID ) ); ?>"><i class="fa fa-eye"> </i><?php esc_html_e( 'View Notice Detail', 'mjschool' ); ?></a>
																			</li>
																			<?php
																			if ( $user_access_edit === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px mjschool-border-bottom-item">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=addnotice&action=edit&notice_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->ID ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'edit_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px"><i class="fa fa-edit"> </i><?php esc_html_e( 'Edit', 'mjschool' ); ?></a>
																				</li>
																				<?php
																			}
																			?>
																			<?php
																			if ( $user_access_delete === '1' ) {
																				?>
																				<li class="mjschool-float-left-width-100px">
																					<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=noticelist&action=delete&notice_id=' . rawurlencode( mjschool_encrypt_id( $retrieved_data->ID ) ) . '&_wpnonce_action=' . rawurlencode( mjschool_get_nonce( 'delete_action' ) ) ) ); ?>" class="mjschool-float-left-width-100px mjschool_orange_color" onclick="return confirm( '<?php esc_html_e( 'Are you sure you want to delete this record?', 'mjschool' ); ?>' );"><i class="fa fa-trash"></i> <?php esc_html_e( 'Delete', 'mjschool' ); ?></a>
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
											<button class="btn btn-success mjschool-btn-sms-color mjschool-button-reload" type="button">
												<input type="checkbox" id="select_all" name="" class="mjschool-sub-chk select_all mjchool_margin_top_0px" value="">
												<label for="select_all" class="mjschool-margin-right-5px"><?php esc_html_e( 'Select All', 'mjschool' ); ?></label>
											</button>
											<?php
											if ( $user_access_delete === '1' ) {
												?>
												<button id="delete_selected" data-toggle="tooltip" title="<?php esc_attr_e( 'Delete Selected', 'mjschool' ); ?>" name="delete_selected" class="delete_selected"><img src="<?php echo esc_url( MJSCHOOL_PLUGIN_URL . '/assets/images/listpage-icon/mjschool-delete.png' ); ?>"></button>
												<?php
											}
											?>
										</div>
									</form>
								</div>
							</div><!-- Mjschool-panel-body. -->
							<?php
						} elseif ( $user_access_add === '1' ) {
							?>
							<div class="mjschool-no-data-list-div">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mjschool_notice&tab=addnotice' ) ); ?>">
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
					if ( $active_tab === 'addnotice' ) {
						require_once MJSCHOOL_ADMIN_DIR . '/notice/add-notice.php';
					}
					?>
				</div><!-- Mjschool-main-list-page. -->
			</div><!-- Col-md-12. -->
		</div><!-- Row. -->
	</div><!-- Mjschool-main-list-margin-15px. -->
</div><!-- Mjschool-page-inner. -->